<?php

namespace Tests\Feature;

use App\Models\Auth;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_me_and_logout_via_stateful_api(): void
    {
        $auth = Auth::create([
            'username' => 'tester',
            'email' => 'tester@example.com',
            'password' => password_hash('password123', PASSWORD_BCRYPT),
            'is_admin' => 0,
        ]);

        User::create([
            'username' => 'tester',
            'display_name' => 'Tester',
            'email' => 'tester@example.com',
            'profile_picture' => null,
        ]);

        $login = $this->postJson('/api/v1/auth/login', [
            'email' => 'tester@example.com',
            'password' => 'password123',
        ]);

        $login->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.username', 'tester')
            ->assertJsonPath('meta.redirect_to', route('utama'));

        $me = $this->getJson('/api/v1/me');

        $me->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.id', $auth->id)
            ->assertJsonPath('data.user.display_name', 'Tester');

        $logout = $this->postJson('/api/v1/auth/logout');

        $logout->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.redirect_to', route('landing'));
    }
}
