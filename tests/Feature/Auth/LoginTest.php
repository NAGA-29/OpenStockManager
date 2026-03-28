<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 認証（ログイン・ログアウト）フローのFeatureテスト
 *
 * @covers \App\Http\Controllers\Auth\LoginController
 */
class LoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ログインページが 200 で表示されること
     */
    public function test_login_page_is_accessible(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    /**
     * 認証済みユーザーがログインページにアクセスするとリダイレクトされること
     */
    public function test_authenticated_user_is_redirected_from_login_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/login');

        $response->assertRedirect();
    }

    /**
     * 正しいCredentialでログインするとホーム画面にリダイレクトされ、認証状態になること
     */
    public function test_login_with_valid_credentials_redirects_to_dashboard(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email'    => $user->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect('/device/stb');
        $this->assertAuthenticatedAs($user);
    }

    /**
     * 誤ったパスワードでログインするとバリデーションエラーが返り、未認証のままであること
     */
    public function test_login_with_invalid_password_returns_error(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('correct-password'),
        ]);

        $response = $this->post('/login', [
            'email'    => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * 存在しないメールアドレスでログインするとバリデーションエラーが返ること
     */
    public function test_login_with_nonexistent_email_returns_error(): void
    {
        $response = $this->post('/login', [
            'email'    => 'notexists@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * ログアウト後に未認証状態になること
     */
    public function test_logout_clears_authenticated_session(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/logout');

        $this->assertGuest();
    }
}
