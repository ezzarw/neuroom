<?php

namespace Database\Seeders;

use App\Models\Note;
use App\Models\PomodoroHistory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'username' => 'budi',
                'display_name' => 'Budi Santoso',
                'email' => 'budi@neuroom.local',
                'password' => 'User12345',
            ],
            [
                'username' => 'sari',
                'display_name' => 'Sari Lestari',
                'email' => 'sari@neuroom.local',
                'password' => 'User12345',
            ],
        ];

        foreach ($users as $userData) {
            $user = User::updateOrCreate(
                ['username' => $userData['username']],
                [
                    'display_name' => $userData['display_name'],
                    'email' => $userData['email'],
                    'password' => Hash::make($userData['password']),
                    'is_admin' => 0,
                    'profile_picture' => null,
                ]
            );

            $this->seedNotes($user);
            $this->seedPomodoroHistories($user);
        }
    }

    protected function seedNotes(User $user): void
    {
        $notes = [
            [
                'title' => 'Rencana belajar hari ini',
                'content' => 'Fokus pada materi utama, kerjakan latihan, lalu review catatan sebelum selesai.',
            ],
            [
                'title' => 'Checklist tugas',
                'content' => 'Selesaikan ringkasan, cek ulang hasil pomodoro, dan rapikan prioritas besok.',
            ],
        ];

        foreach ($notes as $note) {
            Note::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'title' => $note['title'],
                ],
                [
                    'content' => $note['content'],
                ]
            );
        }
    }

    protected function seedPomodoroHistories(User $user): void
    {
        $sessions = [
            ['session' => 1, 'duration_seconds' => 1500],
            ['session' => 2, 'duration_seconds' => 1500],
            ['session' => 3, 'duration_seconds' => 900],
        ];

        foreach ($sessions as $session) {
            PomodoroHistory::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'session' => $session['session'],
                ],
                [
                    'duration_seconds' => $session['duration_seconds'],
                ]
            );
        }
    }
}
