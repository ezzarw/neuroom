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
            ['username' => env('ADMIN_USERNAME', 'admin')],
            [
                'email' => env('ADMIN_EMAIL', 'admin@neuroom.test'),
                'password' => Hash::make(env('ADMIN_PASSWORD', 'password')),
                'is_admin' => 1,
                'display_name' => 'Administrator',
                'profile_picture' => null,
            ]
        );
    }
}
