<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PomodoroHistory extends Model
{
    protected $fillable = [
        'username',
        'user_id',
        'session',
        'date'
    ];
}
