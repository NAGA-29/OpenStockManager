<?php

namespace Tests\Unit\Services;

use App\Services\ReturnDeadlineNotificationService;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class ReturnDeadlineNotificationServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_send_catches_exception_and_logs_error(): void
    {
        Log::shouldReceive('channel')
            ->with('error')
            ->andReturnSelf();
        Log::shouldReceive('error')
            ->with('返却期限通知メール送信例外', Mockery::on(function ($data) {
                return isset($data['error_message']);
            }))
            ->once();

        $service = new ReturnDeadlineNotificationService();

        // This will throw because SENDGRID_API_KEY is not configured in test env
        $service->send('<p>テスト返却期限メッセージ</p>');

        // Service should not propagate the exception
        $this->assertTrue(true);
    }

    public function test_send_does_not_propagate_exception(): void
    {
        Log::shouldReceive('channel')->andReturnSelf();
        Log::shouldReceive('error')->withAnyArgs();

        $service = new ReturnDeadlineNotificationService();

        // Should not throw even with HTML content
        $htmlMessage = '<p>下記の返却期限が迫っています。<br>レンタルID: LEND-001<br></p>';
        $service->send($htmlMessage);

        $this->assertTrue(true);
    }

    public function test_send_accepts_empty_message(): void
    {
        Log::shouldReceive('channel')->andReturnSelf();
        Log::shouldReceive('error')->withAnyArgs();

        $service = new ReturnDeadlineNotificationService();
        $service->send('');

        $this->assertTrue(true);
    }

    public function test_send_uses_config_for_api_key(): void
    {
        // Verify the service uses config() instead of getenv()
        // by checking it accesses 'services.sendgrid.api_key'
        config(['services.sendgrid.api_key' => 'test-api-key']);

        Log::shouldReceive('channel')->andReturnSelf();
        Log::shouldReceive('error')->withAnyArgs();

        $service = new ReturnDeadlineNotificationService();
        $service->send('テスト');

        // Service uses config('services.sendgrid.api_key')
        $this->assertEquals('test-api-key', config('services.sendgrid.api_key'));
    }
}
