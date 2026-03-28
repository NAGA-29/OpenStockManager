<?php

namespace Tests\Feature\Exceptions;

use App\Exceptions\Domain\Device\DeviceNotFoundException;
use App\Exceptions\Infrastructure\ImageProcessingException;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Handler による例外レンダリング動作のFeatureテスト
 *
 * テスト用ルートをその場で登録し、例外を throw させることで
 * Handler::render() の振る舞いを検証する。
 *
 * @covers \App\Exceptions\Handler
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
}
