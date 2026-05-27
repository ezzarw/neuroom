<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminUsersApiTest extends TestCase
{
    use RefreshDatabase;

    protected function createAdmin(): User
    {
        return User::create([
            'username' => 'admin',
            'display_name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => password_hash('password123', PASSWORD_BCRYPT),
            'is_admin' => 1,
            'profile_picture' => null,
        ]);
    }

    public function test_admin_can_crud_users_via_api(): void
    {
        $admin = $this->createAdmin();
        Sanctum::actingAs($admin);

        $create = $this->postJson('/api/v1/admin/users', [
            'username' => 'member baru',
            'email' => 'member@example.com',
            'password' => 'password123',
        ]);

        $create->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.email', 'member@example.com');

        $userId = $create->json('data.id');

        $index = $this->getJson('/api/v1/admin/users');
        $index->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data');

        $update = $this->putJson("/api/v1/admin/users/{$userId}", [
            'display_name' => 'Member Update',
            'email' => 'member-update@example.com',
            'is_admin' => 0,
        ]);

        $update->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.display_name', 'Member Update')
            ->assertJsonPath('data.email', 'member-update@example.com');

        $delete = $this->deleteJson("/api/v1/admin/users/{$userId}");
        $delete->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('users', [
            'id' => $userId,
        ]);
    }
}
