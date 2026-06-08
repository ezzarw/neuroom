<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;

class AuthService
{
    public function clearUserRedisKeys(int $userId): void
    {
        $redisKeys = Redis::keys("user:$userId:*");
        
        if (!empty($redisKeys)) {
            $parsedKeys = [];
            foreach ($redisKeys as $key) {
                $parts = explode(':', $key);
                $parts[0] = 'user';
                $parsedKeys[] = implode(':', $parts);
            }
            Redis::del($parsedKeys);
        }
    }
}
