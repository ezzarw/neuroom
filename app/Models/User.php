<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use Notifiable;

    protected $fillable = [
        'username',
        'display_name',
        'profile_picture',
        'email',
        'password',
        'is_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function pomodoroHistories(): HasMany
    {
        return $this->hasMany(PomodoroHistory::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(\App\Models\Note::class);
    }
}
