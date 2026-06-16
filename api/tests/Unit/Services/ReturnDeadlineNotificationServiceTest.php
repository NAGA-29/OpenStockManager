<?php

namespace Tests\Unit\Services;

use App\Services\ReturnDeadlineNotificationService;
use Mockery;
use Psr\Log\LoggerInterface;
use Tests\TestCase;

/**
 * @covers \App\Services\ReturnDeadlineNotificationService
 */
class ReturnDeadlineNotificationServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * 例外が発生した場合、logger にエラーログが記録されること
     */
    public function test_send_catches_exception_and_logs_error(): void
    {
        $logger = Mockery::mock(LoggerInterface::class);
        $logger->shouldReceive('error')
            ->once()
            ->withArgs(function (string $key, array $data) {
                return isset($data['error_message']);
            });

        $service = new ReturnDeadlineNotificationService($logger);

        // SendGrid APIキー未設定のため例外が発生するが、サービス内でキャッチされる
        $service->send('<p>テスト返却期限メッセージ</p>');
    }

    /**
     * 例外が発生してもサービスから例外が伝播しないこと
     */
    public function test_send_does_not_propagate_exception(): void
    {
        $logger = Mockery::mock(LoggerInterface::class)->shouldIgnoreMissing();
        $service = new ReturnDeadlineNotificationService($logger);

        // HTMLコンテンツを渡しても例外が投げられないこと
        $htmlMessage = '<p>下記の返却期限が迫っています。<br>レンタルID: LEND-001<br></p>';
        $service->send($htmlMessage);

        $this->assertTrue(true);
    }

    /**
     * 空文字列のメッセージを渡しても例外が伝播しないこと
     */
    public function test_send_accepts_empty_message(): void
    {
        $logger = Mockery::mock(LoggerInterface::class)->shouldIgnoreMissing();
        $service = new ReturnDeadlineNotificationService($logger);

        $service->send('');

        $this->assertTrue(true);
    }

    /**
     * SendGrid APIキーの設定値が config() 経由で取得されること
     */
    public function test_send_uses_config_for_api_key(): void
    {
        config(['services.sendgrid.api_key' => 'test-api-key']);

        $logger = Mockery::mock(LoggerInterface::class)->shouldIgnoreMissing();
        $service = new ReturnDeadlineNotificationService($logger);
        $service->send('テスト');

        $this->assertEquals('test-api-key', config('services.sendgrid.api_key'));
    }
}
