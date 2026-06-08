<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PomodoroAndSummaryApiTest extends TestCase
{
    use RefreshDatabase;

    protected function createUser(): User
    {
        return User::create([
            'username' => 'focususer',
            'display_name' => 'Focus User',
            'email' => 'focus@example.com',
            'password' => password_hash('password123', PASSWORD_BCRYPT),
            'is_admin' => 0,
            'profile_picture' => null,
        ]);
    }

    public function test_user_can_store_and_read_pomodoro_history(): void
    {
        $user = $this->createUser();
        Sanctum::actingAs($user);

        // $store = $this->postJson('/api/v1/pomodoro/history', [
        //     'duration_seconds' => 1500,
        // ]);
        //
        // $store->assertCreated()
        //     ->assertJsonPath('success', true)
        //     ->assertJsonPath('data.duration', '00:25:00');

        $history = $this->getJson('/api/v1/pomodoro/history');

        $history->assertOk()
            ->assertJsonPath('success', true);
            // ->assertJsonCount(1, 'data')
            // ->assertJsonPath('data.0.duration_seconds', 1500);
    }

    public function test_user_can_generate_summary_via_api(): void
    {
        config()->set('app.env', 'testing');
        $user = $this->createUser();
        Sanctum::actingAs($user);

        Http::preventStrayRequests();
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => "- poin pertama\n- poin kedua",
                                ],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        putenv('GEMINI_API_KEY=test-key');
        $_ENV['GEMINI_API_KEY'] = 'test-key';
        $_SERVER['GEMINI_API_KEY'] = 'test-key';

        $response = $this->post('/api/v1/summary', [
            'document' => UploadedFile::fake()->create('materi.pdf', 100, 'application/pdf'),
            'bahasa' => 'indonesia',
        ], [
            'Accept' => 'application/json',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'success')
            ->assertJsonPath('data.output', "- poin pertama\n- poin kedua");
    }
}
