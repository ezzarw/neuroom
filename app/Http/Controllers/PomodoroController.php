<?php

namespace App\Http\Controllers;

use App\Models\PomodoroHistory;
use App\Models\User;
use Illuminate\Http\Request;

class PomodoroController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'duration_seconds' => ['required', 'integer', 'min:1'],
        ]);

        $profile = User::query()
            ->where('auth_id', $request->user()->id)
            ->firstOrFail();

        $session = PomodoroHistory::query()
            ->where('user_id', $profile->id)
            ->whereDate('created_at', now()->toDateString())
            ->count() + 1;

        $history = PomodoroHistory::create([
            'user_id' => $profile->id,
            'session' => $session,
            'duration_seconds' => $validated['duration_seconds'],
        ]);

        return $this->apiSuccess('Data pomodoro berhasil ditambahkan.', [
            'session' => [
                'id' => $history->id,
                'session' => $history->session,
                'date' => $history->created_at?->toDateString(),
                'duration_seconds' => (int) $history->duration_seconds,
                'duration' => $this->formatDuration((int) $history->duration_seconds),
                'created_at' => $this->formatDateTime($history->created_at),
            ],
        ], 201);
    }

    public function history(Request $request)
    {
        $profile = User::query()
            ->where('auth_id', $request->user()->id)
            ->firstOrFail();

        $sessions = PomodoroHistory::query()
            ->where('user_id', $profile->id)
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn (PomodoroHistory $history) => [
                'id' => $history->id,
                'session' => $history->session,
                'date' => $history->created_at?->toDateString(),
                'duration_seconds' => (int) ($history->duration_seconds ?? 0),
                'duration' => $this->formatDuration((int) ($history->duration_seconds ?? 0)),
                'created_at' => $this->formatDateTime($history->created_at),
            ])
            ->values();

        return $this->apiSuccess('Riwayat pomodoro berhasil diambil.', [
            'sessions' => $sessions,
        ]);
    }
}
