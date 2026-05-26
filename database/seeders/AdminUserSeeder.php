<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'email' => 'admin@neuroom.local',
                'password' => Hash::make('Admin12345'),
                'is_admin' => 1,
                'display_name' => 'Administrator',
                'profile_picture' => null,
            ]
        );
    }
}
