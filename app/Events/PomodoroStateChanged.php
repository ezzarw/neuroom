<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class PomodoroStateChanged implements ShouldBroadcastNow
{
    public function __construct(
        public int $userId,
        public array $payload
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->userId . '.pomodoro'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'pomodoro.state.changed';
    }

    public function broadcastWith(): array
    {
        return $this->payload;
    }
}