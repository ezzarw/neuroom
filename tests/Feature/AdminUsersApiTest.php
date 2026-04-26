<?php

namespace Tests\Feature;

use App\Models\Auth;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUsersApiTest extends TestCase
{
    use RefreshDatabase;

    protected function createAdmin(): Auth
    {
        $admin = Auth::create([
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => password_hash('password123', PASSWORD_BCRYPT),
            'is_admin' => 1,
        ]);

        User::create([
            'username' => 'admin',
            'display_name' => 'Administrator',
            'email' => 'admin@example.com',
            'profile_picture' => null,
        ]);

        return $admin;
    }

    public function test_admin_can_crud_users_via_api(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin, 'web');

        $create = $this->postJson('/api/v1/admin/users', [
            'username' => 'member baru',
            'email' => 'member@example.com',
            'password' => 'password123',
        ]);

        $create->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.email', 'member@example.com');

        $userId = $create->json('data.user.id');

        $index = $this->getJson('/api/v1/admin/users');
        $index->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data.users');

        $update = $this->putJson("/api/v1/admin/users/{$userId}", [
            'display_name' => 'Member Update',
            'email' => 'member-update@example.com',
            'is_admin' => 0,
        ]);

        $update->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.display_name', 'Member Update')
            ->assertJsonPath('data.user.email', 'member-update@example.com');

        $delete = $this->deleteJson("/api/v1/admin/users/{$userId}");
        $delete->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('auths', [
            'id' => $userId,
        ]);
    }
}
