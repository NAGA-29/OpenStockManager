<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * SPA(React)向け JSON API ルート登録のFeatureテスト。
 *
 * - 認証不要ルート（login）が公開されていること
 * - 保護ルートが `auth:sanctum` で 401 を返すこと
 * - Sanctum トークン認証済みで 200 が返ること
 */
class ApiRoutesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 正しい資格情報でログインするとトークンとユーザー情報が返ること。
     */
    public function test_login_issues_token(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email'    => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email', 'role', 'is_admin']]);
    }

    /**
     * 誤った資格情報では 422 バリデーションエラーになること。
     */
    public function test_login_with_invalid_credentials_fails(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email'    => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('email');
    }

    /**
     * 保護ルートは未認証だと 401 を返すこと。
     *
     * @dataProvider protectedRouteProvider
     */
    public function test_protected_routes_require_authentication(string $method, string $uri): void
    {
        $response = $this->json($method, $uri);

        $response->assertStatus(401);
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function protectedRouteProvider(): array
    {
        return [
            'me'               => ['GET', '/api/auth/me'],
            'logout'           => ['POST', '/api/auth/logout'],
            'dashboard'        => ['GET', '/api/dashboard'],
            'inventory.stocks' => ['GET', '/api/inventory/stocks'],
            'devices.category' => ['GET', '/api/devices/category/PC'],
        ];
    }

    /**
     * Sanctum トークン認証済みなら保護ルートにアクセスできること。
     */
    public function test_authenticated_user_can_access_me(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/auth/me');

        $response->assertStatus(200)
            ->assertJsonPath('user.id', $user->id);
    }

    /**
     * 認証済みで在庫数量管理の一覧が `data` 包みで返ること。
     */
    public function test_authenticated_user_can_access_inventory_stocks(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/inventory/stocks');

        $response->assertStatus(200)->assertJsonStructure(['data']);
    }

    /**
     * フロント(Vite)オリジンからの CORS プリフライトが許可されること。
     */
    public function test_cors_preflight_allows_frontend_origin(): void
    {
        config(['cors.allowed_origins' => ['http://localhost:5173']]);

        $response = $this->call('OPTIONS', '/api/auth/login', [], [], [], [
            'HTTP_ORIGIN'                        => 'http://localhost:5173',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
        ]);

        $this->assertSame('http://localhost:5173', $response->headers->get('Access-Control-Allow-Origin'));
    }
}
