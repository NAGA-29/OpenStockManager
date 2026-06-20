<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $member;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_ADMIN,
        ]);

        $this->member = User::create([
            'name' => 'Normal User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_USER,
        ]);
    }

    public function test_index_requires_authentication(): void
    {
        $this->getJson('/api/users')->assertStatus(401);
    }

    public function test_index_forbidden_for_non_admin(): void
    {
        $this->actingAs($this->member, 'sanctum')
            ->getJson('/api/users')
            ->assertStatus(403);
    }

    public function test_index_returns_users_for_admin(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/users');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [['id', 'name', 'email', 'role', 'is_admin', 'created_at']],
            'meta' => ['current_page', 'last_page', 'per_page', 'total'],
        ]);
        $this->assertSame(2, $response->json('meta.total'));
    }

    public function test_index_searches_by_keyword(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/users?word=Normal');

        $response->assertStatus(200);
        $this->assertSame(1, $response->json('meta.total'));
        $this->assertSame('Normal User', $response->json('data.0.name'));
    }

    public function test_store_forbidden_for_non_admin(): void
    {
        $this->actingAs($this->member, 'sanctum')
            ->postJson('/api/users', [
                'name' => 'New',
                'email' => 'new@example.com',
                'password' => 'Password1',
                'password_confirmation' => 'Password1',
                'role' => 'user',
            ])
            ->assertStatus(403);
    }

    public function test_store_creates_user(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/users', [
                'name' => 'New User',
                'email' => 'new@example.com',
                'password' => 'Password1',
                'password_confirmation' => 'Password1',
                'role' => 'user',
            ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.email', 'new@example.com');
        $this->assertDatabaseHas('users', [
            'email' => 'new@example.com',
            'role' => 'user',
        ]);
    }

    public function test_store_validates_unique_email(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/users', [
                'name' => 'Dup',
                'email' => 'user@example.com',
                'password' => 'Password1',
                'password_confirmation' => 'Password1',
                'role' => 'user',
            ]);

        $response->assertStatus(422);
        $response->assertJsonPath('errors.email.0', 'このメールアドレスは既に登録されています。');
    }

    public function test_store_validates_password_confirmation(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/users', [
                'name' => 'Mismatch',
                'email' => 'mismatch@example.com',
                'password' => 'Password1',
                'password_confirmation' => 'Password2',
                'role' => 'user',
            ]);

        $response->assertStatus(422);
        $response->assertJsonPath('errors.password.0', 'パスワードが一致しません。');
    }

    public function test_update_changes_role_and_name(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/users/{$this->member->id}", [
                'name' => 'Promoted User',
                'email' => 'user@example.com',
                'role' => 'admin',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'id' => $this->member->id,
            'name' => 'Promoted User',
            'role' => 'admin',
        ]);
    }

    public function test_update_allows_keeping_own_email(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/users/{$this->member->id}", [
                'name' => 'Normal User',
                'email' => 'user@example.com',
                'role' => 'user',
            ]);

        $response->assertStatus(200);
    }

    public function test_update_returns_404_for_missing_user(): void
    {
        $this->actingAs($this->admin, 'sanctum')
            ->putJson('/api/users/999999', [
                'name' => 'Ghost',
                'email' => 'ghost@example.com',
                'role' => 'user',
            ])
            ->assertStatus(404);
    }

    public function test_update_forbidden_for_non_admin(): void
    {
        $this->actingAs($this->member, 'sanctum')
            ->putJson("/api/users/{$this->member->id}", [
                'name' => 'Self',
                'email' => 'user@example.com',
                'role' => 'admin',
            ])
            ->assertStatus(403);
    }
}
