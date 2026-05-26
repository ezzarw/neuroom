<?php

namespace App\Http\Controllers;

use App\Models\PomodoroHistory;
use Illuminate\Http\Request;

class PomodoroController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'duration_seconds' => ['required', 'integer', 'min:1'],
        ]);

        $user = $request->user();

        if ($user === null) {
            return $this->apiError('User tidak ditemukan.', 404);
        }

        $session = $user->pomodoroHistories()
            ->whereDate('created_at', now()->toDateString())
            ->count() + 1;

        $history = $user->pomodoroHistories()->create([
            'session' => $session,
            'duration_seconds' => $validated['duration_seconds'],
        ]);

        return $this->apiSuccess(
            'Data pomodoro berhasil ditambahkan.',
            [
                'id' => $history->id,
                'session' => $history->session,
                'date' => $history->created_at?->toDateString(),
                'duration_seconds' => (int) $history->duration_seconds,
                'duration' => $this->formatDuration((int) $history->duration_seconds),
                'created_at' => $this->formatDateTime($history->created_at),
            ],
            201
        );
    }

    public function history(Request $request)
    {
        $user = $request->user();

        if ($user === null) {
            return $this->apiError('User tidak ditemukan.', 404);
        }

        $sessions = $user->pomodoroHistories()
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

        return $this->apiSuccess(
            'Riwayat pomodoro berhasil diambil.',
            $sessions->all()
        );
    }
}
