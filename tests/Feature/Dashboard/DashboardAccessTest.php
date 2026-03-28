<?php

namespace Tests\Feature\Dashboard;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * ダッシュボードへのアクセス制御のFeatureテスト
 *
 * @covers \App\Http\Controllers\DashboardController
 */
class DashboardAccessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 認証済みユーザーがダッシュボードに 200 でアクセスできること
     */
    public function test_authenticated_user_can_access_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
    }

    /**
     * 未認証ユーザーがダッシュボードにアクセスするとログインページにリダイレクトされること
     */
    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    /**
     * ルート (/) へのアクセスがダッシュボードにリダイレクトされること
     */
    public function test_root_redirects_to_dashboard_when_authenticated(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/');

        $response->assertRedirect('/dashboard');
    }
}
