<?php

namespace Tests\Feature\Middleware;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * AdminMiddleware によるロールベースアクセス制御の Feature テスト。
 * Blade UI 撤去後は API の管理者専用ルート（`/api/users`）で検証する。
 *
 * @covers \App\Http\Middleware\AdminMiddleware
 */
class AdminMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    /**
     * admin ロールを持つユーザーが管理者専用 API に 200 でアクセスできること
     */
    public function test_admin_user_can_access_admin_route(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $response = $this->actingAs($admin, 'sanctum')->getJson('/api/users');

        $response->assertStatus(200);
    }

    /**
     * admin ロールを持たないユーザーが管理者専用 API にアクセスすると 403 が返ること
     */
    public function test_non_admin_user_gets_403_on_admin_route(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_USER]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/users');

        $response->assertStatus(403);
    }

    /**
     * 未認証ユーザーが管理者専用 API にアクセスすると 401 が返ること
     */
    public function test_unauthenticated_user_gets_401_on_admin_route(): void
    {
        $response = $this->getJson('/api/users');

        $response->assertStatus(401);
    }
}
