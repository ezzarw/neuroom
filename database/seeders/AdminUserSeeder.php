<?php

namespace Database\Seeders;

use App\Models\Auth;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $auth = Auth::updateOrCreate(
            ['username' => 'admin'],
            [
                'email' => 'admin@neuroom.local',
                'password' => 'admin12345',
                'is_admin' => 1,
            ]
        );

        User::updateOrCreate(
            ['auth_id' => $auth->id],
            [
                'auth_id' => $auth->id,
                'display_name' => 'Administrator',
            ]
        );
    }
}
