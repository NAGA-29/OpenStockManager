<?php

namespace Tests\Feature\Exceptions;

use App\Exceptions\Domain\Device\DeviceNotFoundException;
use App\Exceptions\Infrastructure\ImageProcessingException;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 例外ハンドリング設定のレンダリング動作を検証するFeatureテスト
 *
 * テスト用ルートをその場で登録し、例外を throw させることで
 * `bootstrap/app.php` に定義した例外レスポンス変換を検証する。
 */
class HandlerTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // DeviceException handling
    // =========================================================================

    /**
     * DeviceException が throw された場合、直前のページへリダイレクトし
     * セッションに error_message がセットされること
     */
    public function test_DeviceException_redirects_back_with_error_message(): void
    {
        $user = User::factory()->create();

        $this->app['router']->get('/test/device-exception', function () {
            throw DeviceNotFoundException::forDevice('DEV-001');
        })->middleware(['web', 'auth']);

        $response = $this->actingAs($user)
            ->withSession(['_previous' => ['url' => '/dashboard']])
            ->get('/test/device-exception');

        $response->assertRedirect();
        $response->assertSessionHas('error_message');
    }

    /**
     * DeviceException が throw された場合、セッションの error_message が空でないこと
     */
    public function test_DeviceException_error_message_in_session_is_not_empty(): void
    {
        $user = User::factory()->create();

        $this->app['router']->get('/test/device-exception-msg', function () {
            throw DeviceNotFoundException::forDevice('DEV-999');
        })->middleware(['web', 'auth']);

        $response = $this->actingAs($user)
            ->withSession(['_previous' => ['url' => '/dashboard']])
            ->get('/test/device-exception-msg');

        $this->assertNotEmpty($response->getSession()->get('error_message'));
    }

    // =========================================================================
    // ImageProcessingException handling
    // =========================================================================

    /**
     * ImageProcessingException が throw された場合、直前のページへリダイレクトし
     * セッションに error_message がセットされること
     */
    public function test_ImageProcessingException_redirects_back_with_error_message(): void
    {
        $user = User::factory()->create();

        $this->app['router']->get('/test/image-exception', function () {
            throw ImageProcessingException::analysisFailure('/uploads/photo.png');
        })->middleware(['web', 'auth']);

        $response = $this->actingAs($user)
            ->withSession(['_previous' => ['url' => '/dashboard']])
            ->get('/test/image-exception');

        $response->assertRedirect();
        $response->assertSessionHas('error_message');
    }

    // =========================================================================
    // TokenMismatchException handling
    // =========================================================================

    /**
     * TokenMismatchException (CSRFトークン不正) が throw された場合、
     * ログインページにリダイレクトされること
     */
    public function test_TokenMismatchException_redirects_to_login(): void
    {
        $this->app['router']->get('/test/csrf-exception', function () {
            throw new \Illuminate\Session\TokenMismatchException('CSRF token mismatch');
        })->middleware('web');

        $response = $this->get('/test/csrf-exception');

        $response->assertRedirect('/login');
    }

    // =========================================================================
    // API (JSON) handling — リダイレクトでなく JSON を返すこと
    // =========================================================================

    /**
     * api/* ルートで DeviceException が throw された場合、
     * リダイレクトせず 422 JSON（message/context）を返すこと
     */
    public function test_DeviceException_on_api_returns_json(): void
    {
        $this->app['router']->get('/api/test/device-exception', function () {
            throw DeviceNotFoundException::forDevice('DEV-001');
        });

        $response = $this->getJson('/api/test/device-exception');

        $response->assertStatus(422)
            ->assertJsonStructure(['message', 'context']);
    }

    /**
     * api/* ルートで ImageProcessingException が throw された場合、
     * リダイレクトせず 422 JSON を返すこと
     */
    public function test_ImageProcessingException_on_api_returns_json(): void
    {
        $this->app['router']->get('/api/test/image-exception', function () {
            throw ImageProcessingException::analysisFailure('/uploads/photo.png');
        });

        $response = $this->getJson('/api/test/image-exception');

        $response->assertStatus(422)
            ->assertJsonStructure(['message', 'context']);
    }
}
