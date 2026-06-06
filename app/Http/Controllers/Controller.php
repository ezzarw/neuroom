<?php

namespace App\Http\Controllers;

use App\Models\User;
use DateTimeInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

abstract class Controller
{
    protected function apiSuccess(string $reason, array $data = [], int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'reason' => $reason,
            'data' => $data,
        ], $status);
    }

    protected function apiError(string $reason, int $status = 400, array $errors = []): JsonResponse
    {
        return response()->json([
            'success' => false,
            'reason' => $reason,
            'errors' => (object) $errors,
        ], $status);
    }

    protected function generateUniqueUsername(string $naturalUsername): string
    {
        $base = Str::slug($naturalUsername, '');
        $base = $base === '' ? Str::lower(Str::random(8)) : $base;
        $uniqueUsername = $base;
 
        if (! User::where('username', $uniqueUsername)->exists()) {
            return $uniqueUsername;
        }

        $taken = User::where('username', 'like', $base.'%')
            ->pluck('username')
            ->all();

        $max = 1;

        foreach ($taken as $username) {
            if ($username === $base) {
                continue;
            }

            if (preg_match('/^'.preg_quote($base, '/').'(\d+)$/', $username, $matches)) {
                $max = max($max, (int) $matches[1]);
            }
        }

        return $base.($max + 1);
    }

    protected function formatDuration(int $totalSeconds): string
    {
        $hours = intdiv($totalSeconds, 3600);
        $minutes = intdiv($totalSeconds % 3600, 60);
        $seconds = $totalSeconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

    protected function formatDateTime(?DateTimeInterface $value): ?string
    {
        return $value?->format('Y-m-d H:i:s');
    }
}
