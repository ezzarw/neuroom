<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PomodoroHistory extends Model
{
    protected $fillable = [
        'user_id',
        'pomodoro_uid',
        'status',
        'duration_seconds',
        'actual_seconds',
        'remaining_seconds',
        'started_at',
        'finished_at',
        'stopped_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'stopped_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getUsers(): BelongsTo
    {
        return $this->user();
    }
}
