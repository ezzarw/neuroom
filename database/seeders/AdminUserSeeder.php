<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed admin user for authentications and users tables.
     */
    public function run(): void
    {
        $now = now();

        DB::transaction(function () use ($now): void {
            $adminUsername = 'admin';
            $adminEmail = 'admin@neuroom.local';

            DB::table('authentications')->updateOrInsert(
                ['username' => $adminUsername],
                [
                    'email' => $adminEmail,
                    'password' => Hash::make('admin12345'),
                    'is_admin' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            DB::table('users')->updateOrInsert(
                ['username' => $adminUsername],
                [
                    'display_name' => 'Administrator',
                    'email' => $adminEmail,
                    'profile_picture' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        });
    }
}
