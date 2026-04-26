<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PomodoroHistory extends Model
{
    protected $fillable = [
        'user_id',
        'session',
        'duration_seconds',
    ];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getUsers(): BelongsTo
    {
        return $this->user();
    }
}
