<?php

namespace App\Services;

use App\Events\PomodoroStateChanged;
use App\Models\PomodoroHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class PomodoroService
{
    public function history(object $user): array
    {
        $records = PomodoroHistory::where('user_id', $user->id)
            ->latest()
            ->limit(50)
            ->get()
            ->toArray();

        return [
            'status' => 'history',
            'records' => $records,
            'total' => count($records),
            'server_now' => now()->toISOString(),
        ];
    }

    private function saveFocusHistory(
        object $user,
        array $state,
        string $status,
        int $actualSeconds,
        int $remaining,
        $endedAt
    ): void {
        if (($state['type'] ?? null) !== 'focus') {
            return;
        }

        PomodoroHistory::create([
            'pomodoro_uid' => $state['id'],
            'user_id' => $user->id,

            'status' => $status,

            'duration_seconds' => (int) $state['duration'],
            'actual_seconds' => $actualSeconds,
            'remaining_seconds' => $remaining,

            'started_at' => $state['started_at'] ?? null,
            'finished_at' => $status === 'finished' ? $endedAt : null,
            'stopped_at' => $status === 'stopped' ? $endedAt : null,
        ]);
    }

    private int $bufferSeconds = 3600;

    private int $pausedTtlSeconds = 86400; // 24 jam

    public function current(object $user): array
    {
        $state = $this->getState($user);

        if (! $state) {
            return $this->idlePayload();
        }

        return array_merge($state, [
            'server_now' => now()->toISOString(),
        ]);
    }

    public function start(
        object $user,
        int $duration,
        string $type = 'focus',
        bool $autoStartBreak = true,
        bool $autoStartFocus = false,
    ): array {
        $key = $this->key($user);

        $currentState = $this->getState($user);

        if ($currentState) {
            return array_merge($currentState, [
                'message' => 'Masih ada pomodoro aktif.',
                'server_now' => now()->toISOString(),
            ]);
        }

        return $this->startNewSession(
            user: $user,
            duration: $duration,
            type: $type,
            autoStartBreak: $autoStartBreak,
            autoStartFocus: $autoStartFocus
        );
    }

    public function startBreak(
        object $user,
        int $duration = 300,
        bool $autoStartBreak = true,
        bool $autoStartFocus = false,
    ): array {
        return $this->start(
            user: $user,
            duration: $duration,
            type: 'short_break',
            autoStartBreak: $autoStartBreak,
            autoStartFocus: $autoStartFocus
        );
    }

    public function startLongBreak(
        object $user,
        int $duration = 900,
        bool $autoStartBreak = true,
        bool $autoStartFocus = false,
    ): array {
        return $this->start(
            user: $user,
            duration: $duration,
            type: 'long_break',
            autoStartBreak: $autoStartBreak,
            autoStartFocus: $autoStartFocus
        );
    }

    public function pause(object $user): array
    {
        $key = $this->key($user);
        $state = $this->getState($user);

        if (! $state) {
            return $this->idlePayload();
        }

        $now = now();

        if ($state['status'] !== 'running') {
            return array_merge($state, [
                'message' => 'Pomodoro tidak sedang running.',
                'server_now' => $now->toISOString(),
            ]);
        }

        $remaining = $this->calculateRemainingFromEndsAt($state['ends_at'], $now);

        $payload = array_merge($this->baseCarryPayload($state), [
            'status' => 'paused',
            'remaining' => $remaining,
            'paused_at' => $now->toISOString(),
            'ends_at' => null,
            'server_now' => $now->toISOString(),
        ]);

        Redis::setex(
            $key,
            $this->pausedTtlSeconds,
            json_encode($payload)
        );

        event(new PomodoroStateChanged($user->id, $payload));

        return $payload;
    }

    public function resume(object $user): array
    {
        $key = $this->key($user);
        $state = $this->getState($user);

        if (! $state) {
            return $this->idlePayload();
        }

        $now = now();

        if ($state['status'] !== 'paused') {
            return array_merge($state, [
                'message' => 'Pomodoro tidak sedang paused.',
                'server_now' => $now->toISOString(),
            ]);
        }

        $remaining = (int) $state['remaining'];

        if ($remaining <= 0) {
            return $this->finish($user);
        }

        $endsAt = $now->copy()->addSeconds($remaining);

        $payload = array_merge($this->baseCarryPayload($state), [
            'status' => 'running',
            'remaining' => $remaining,
            'resumed_at' => $now->toISOString(),
            'ends_at' => $endsAt->toISOString(),
            'server_now' => $now->toISOString(),
        ]);

        Redis::setex(
            $key,
            $remaining + $this->bufferSeconds,
            json_encode($payload)
        );

        event(new PomodoroStateChanged($user->id, $payload));

        return $payload;
    }

    public function stop(object $user): array
    {
        $key = $this->key($user);
        $state = $this->getState($user);

        if (! $state) {
            return $this->idlePayload();
        }

        $now = now();

        if ($state['status'] === 'running') {
            $remaining = $this->calculateRemainingFromEndsAt($state['ends_at'], $now);
        } elseif ($state['status'] === 'paused') {
            $remaining = (int) $state['remaining'];
        } else {
            $remaining = 0;
        }

        $duration = (int) $state['duration'];
        $actualSeconds = max(0, $duration - $remaining);

        $payload = array_merge($this->baseCarryPayload($state), [
            'status' => 'stopped',
            'remaining' => $remaining,
            'actual_seconds' => $actualSeconds,
            'stopped_at' => $now->toISOString(),
            'server_now' => $now->toISOString(),
        ]);

        $this->saveFocusHistory(
            user: $user,
            state: $state,
            status: 'stopped',
            actualSeconds: $actualSeconds,
            remaining: $remaining,
            endedAt: $now
        );

        Redis::del($key);

        event(new PomodoroStateChanged($user->id, $payload));

        return $payload;
    }

    public function finish(object $user): array
    {
        $key = $this->key($user);
        $state = $this->getState($user);

        if (! $state) {
            return $this->idlePayload();
        }

        $now = now();

        if ($state['status'] !== 'running') {
            return array_merge($state, [
                'message' => 'Pomodoro tidak sedang running.',
                'server_now' => $now->toISOString(),
            ]);
        }

        $endsAt = Carbon::parse($state['ends_at']);

        if ($now->lt($endsAt)) {
            return array_merge($state, [
                'message' => 'Pomodoro belum selesai.',
                'server_now' => $now->toISOString(),
            ]);
        }

        $duration = (int) $state['duration'];

        $next = $this->nextSessionInfo($state);

        $payload = array_merge($this->baseCarryPayload($state), [
            'status' => 'finished',
            'remaining' => 0,
            'actual_seconds' => $duration,
            'finished_at' => $now->toISOString(),

            'next_type' => $next['type'],
            'next_duration' => $next['duration'],
            'next_action' => $next['action'],

            'server_now' => $now->toISOString(),
        ]);

        $this->saveFocusHistory(
            user: $user,
            state: $state,
            status: 'finished',
            actualSeconds: $duration,
            remaining: 0,
            endedAt: $now
        );

        Redis::del($key);

        event(new PomodoroStateChanged($user->id, $payload));

        if ($next['should_auto_start']) {
            return $this->startNewSession(
                user: $user,
                duration: $next['duration'],
                type: $next['type'],
                autoStartBreak: (bool) $state['auto_start_break'],
                autoStartFocus: (bool) $state['auto_start_focus'],
                previousSessionId: $state['id']
            );
        }

        return $payload;
    }

    public function show(object $user)
    {
        $pomodoro = PomodoroHistory::query()->where('user_id', $user->id)->get();

        return $pomodoro;
    }

    private function startNewSession(
        object $user,
        int $duration,
        string $type,
        bool $autoStartBreak,
        bool $autoStartFocus,
        ?string $previousSessionId = null,
    ): array {
        if (! in_array($type, ['focus', 'short_break', 'long_break'], true)) {
            return [
                'status' => 'error',
                'message' => 'Type pomodoro tidak valid.',
                'server_now' => now()->toISOString(),
            ];
        }

        if ($duration <= 0) {
            return [
                'status' => 'error',
                'message' => 'Duration harus lebih dari 0.',
                'server_now' => now()->toISOString(),
            ];
        }

        $now = now();
        $endsAt = $now->copy()->addSeconds($duration);
        $pomodoroId = (string) Str::uuid();

        $payload = [
            'id' => $pomodoroId,
            'previous_session_id' => $previousSessionId,
            'type' => $type,
            'status' => 'running',

            'duration' => $duration,
            'remaining' => $duration,

            'focus_duration' => $type === 'focus' ? $duration : 1500,
            'short_break_duration' => 300,
            'long_break_duration' => 900,

            'auto_start_break' => $autoStartBreak,
            'auto_start_focus' => $autoStartFocus,

            'started_at' => $now->toISOString(),
            'ends_at' => $endsAt->toISOString(),

            'server_now' => $now->toISOString(),
        ];

        Redis::setex(
            $this->key($user),
            $duration + $this->bufferSeconds,
            json_encode($payload)
        );

        event(new PomodoroStateChanged($user->id, $payload));

        return $payload;
    }

    private function nextSessionInfo(array $state): array
    {
        if ($state['type'] === 'focus') {
            $nextType = 'short_break';
            $nextDuration = (int) ($state['short_break_duration'] ?? 300);

            return [
                'type' => $nextType,
                'duration' => $nextDuration,
                'action' => (bool) $state['auto_start_break']
                    ? 'auto_start_break'
                    : 'wait_start_break',
                'should_auto_start' => (bool) $state['auto_start_break'],
            ];
        }

        $nextDuration = (int) ($state['focus_duration'] ?? 1500);

        return [
            'type' => 'focus',
            'duration' => $nextDuration,
            'action' => (bool) $state['auto_start_focus']
                ? 'auto_start_focus'
                : 'wait_start_focus',
            'should_auto_start' => (bool) $state['auto_start_focus'],
        ];
    }

    private function baseCarryPayload(array $state): array
    {
        return [
            'id' => $state['id'],
            'previous_session_id' => $state['previous_session_id'] ?? null,
            'type' => $state['type'],

            'duration' => (int) $state['duration'],

            'focus_duration' => (int) ($state['focus_duration'] ?? 1500),
            'short_break_duration' => (int) ($state['short_break_duration'] ?? 300),
            'long_break_duration' => (int) ($state['long_break_duration'] ?? 900),

            'auto_start_break' => (bool) ($state['auto_start_break'] ?? true),
            'auto_start_focus' => (bool) ($state['auto_start_focus'] ?? false),

            'started_at' => $state['started_at'] ?? null,
        ];
    }

    private function key(object $user): string
    {
        return "user:{$user->id}:pomodoro:current";
    }

    private function getState(object $user): ?array
    {
        $rawState = Redis::get($this->key($user));

        if (! $rawState) {
            return null;
        }

        $state = json_decode($rawState, true);

        if (! is_array($state)) {
            return null;
        }

        return $state;
    }

    private function idlePayload(): array
    {
        return [
            'status' => 'idle',
            'message' => 'Tidak ada pomodoro yang sedang berjalan.',
            'server_now' => now()->toISOString(),
        ];
    }

    private function calculateRemainingFromEndsAt(string $endsAt, $now): int
    {
        return max(
            0,
            (int) $now->diffInSeconds(Carbon::parse($endsAt), false)
        );
    }
}
