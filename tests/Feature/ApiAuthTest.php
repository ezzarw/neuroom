<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class ApiAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_me_and_logout_via_stateful_api(): void
    {
        $user = User::create([
            'username' => 'tester',
            'display_name' => 'Tester',
            'email' => 'tester@example.com',
            'is_admin' => 0,
            'password' => Hash::make('password123'),
            'profile_picture' => null,
        ]);

        $login = $this
            ->withSession(['_token' => 'test-csrf-token'])
            ->withHeader('Referer', 'http://localhost')
            ->withHeader('X-CSRF-TOKEN', 'test-csrf-token')
            ->postJson('/api/v1/auth/login', [
                'email' => 'tester@example.com',
                'password' => 'password123',
            ]);

        $login->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.username', 'tester');

        $me = $this
            ->withHeader('Referer', 'http://localhost')
            ->getJson('/api/v1/auth/me');

        $me->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.display_name', 'Tester');

        Redis::shouldReceive('keys')
            ->once()
            ->with("user:{$user->id}:*")
            ->andReturn([]);

        $logout = $this
            ->withSession(['_token' => 'test-csrf-token'])
            ->withHeader('Referer', 'http://localhost')
            ->withHeader('X-CSRF-TOKEN', 'test-csrf-token')
            ->postJson('/api/v1/auth/logout');

        $logout->assertOk()
            ->assertJsonPath('success', true);
    }
}
