<?php

namespace App\Http\Controllers;

use App\Services\PomodoroService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PomodoroController extends Controller
{
    public function start(Request $request, PomodoroService $service): JsonResponse
    {
        return $this->pomodoroResponse(
            $service->start(
                user: $request->user(),
                duration: $request->integer('duration', 1500),
                type: 'focus',
                autoStartBreak: $request->boolean('auto_start_break', true),
                autoStartFocus: $request->boolean('auto_start_focus', false),
            )
        );
    }

    public function startBreak(Request $request, PomodoroService $service): JsonResponse
    {
        return $this->pomodoroResponse(
            $service->startBreak(
                user: $request->user(),
                duration: $request->integer('duration', 300),
                autoStartBreak: $request->boolean('auto_start_break', true),
                autoStartFocus: $request->boolean('auto_start_focus', false),
            )
        );
    }

    public function pause(Request $request, PomodoroService $service): JsonResponse
    {
        return $this->pomodoroResponse($service->pause($request->user()));
    }

    public function resume(Request $request, PomodoroService $service): JsonResponse
    {
        return $this->pomodoroResponse($service->resume($request->user()));
    }

    public function stop(Request $request, PomodoroService $service): JsonResponse
    {
        return $this->pomodoroResponse($service->stop($request->user()));
    }

    public function finish(Request $request, PomodoroService $service): JsonResponse
    {
        return $this->pomodoroResponse($service->finish($request->user()));
    }

    public function current(Request $request, PomodoroService $service): JsonResponse
    {
        return $this->pomodoroResponse($service->current($request->user()));
    }

    public function show(Request $request, PomodoroService $service): JsonResponse
    {
        $result = $service->history($request->user());

        return $this->apiSuccess('Riwayat pomodoro berhasil diambil.', [
            'records' => $result['records'],
            'total' => $result['total'],
        ]);
    }

    private function pomodoroResponse(array $result): JsonResponse
    {
        $reason = $result['message'] ?? match ($result['status'] ?? '') {
            'idle' => 'Tidak ada pomodoro aktif.',
            'running' => 'Pomodoro berjalan.',
            'paused' => 'Pomodoro dijeda.',
            'stopped' => 'Pomodoro dihentikan.',
            'finished' => 'Pomodoro selesai.',
            'error' => $result['message'] ?? 'Terjadi kesalahan.',
            default => 'Berhasil.',
        };

        unset($result['message']);

        if (($result['status'] ?? '') === 'error') {
            return $this->apiError($reason, 422);
        }

        return $this->apiSuccess($reason, $result, 200);
    }
}
