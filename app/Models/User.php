<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model
{
        protected $fillable = [
            'auth_id',
            'display_name',
            'profile_picture'
        ];


        public function auth(): BelongsTo {
            return $this->belongsTo(Auth::class, 'auth_id');
        }

        public function pomodoroHistories(): HasMany {
            return $this->hasMany(PomodoroHistory::class);
        }
        
}
