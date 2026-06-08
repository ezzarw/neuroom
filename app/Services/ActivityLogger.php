<?php

namespace App\Services;

use App\Models\ActivityLog;

class ActivityLogger
{
    public static function log(?int $userId, string $action, string $description = null): void
    {
        try {
            ActivityLog::create([
                'user_id' => $userId,
                'action' => $action,
                'description' => $description,
            ]);
        } catch (\Throwable $th) {
            // Ignore error so it doesn't break main flow
        }
    }
}