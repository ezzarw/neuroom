<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('user.{userId}.pomodoro', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
