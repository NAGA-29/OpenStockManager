<?php

namespace Tests\Unit\Notifications;

use App\Notifications\CustomEmailChangeNotification;
use PHPUnit\Framework\TestCase;

class CustomEmailChangeNotificationTest extends TestCase
{
    public function test_constructor_sets_token_and_email(): void
    {
        $notification = new CustomEmailChangeNotification('test-token-123', 'user@example.com');

        $this->assertEquals('test-token-123', $notification->token);
        $this->assertEquals('user@example.com', $notification->email);
    }

    public function test_via_returns_mail_channel(): void
    {
        $notification = new CustomEmailChangeNotification('token', 'email@example.com');
        $channels = $notification->via(null);

        $this->assertEquals(['mail'], $channels);
    }

    public function test_toArray_returns_empty_array(): void
    {
        $notification = new CustomEmailChangeNotification('token', 'email@example.com');
        $result = $notification->toArray(null);

        $this->assertEquals([], $result);
    }

    public function test_constructor_accepts_various_token_formats(): void
    {
        // SHA-256 hash token
        $notification1 = new CustomEmailChangeNotification(
            hash('sha256', 'test'),
            'user@example.com'
        );
        $this->assertNotEmpty($notification1->token);

        // UUID-style token
        $notification2 = new CustomEmailChangeNotification(
            'a1b2c3d4-e5f6-7890-abcd-ef1234567890',
            'user@example.com'
        );
        $this->assertEquals('a1b2c3d4-e5f6-7890-abcd-ef1234567890', $notification2->token);
    }

    public function test_constructor_preserves_email_exactly(): void
    {
        $email = 'Test.User+tag@Example.COM';
        $notification = new CustomEmailChangeNotification('token', $email);

        $this->assertEquals($email, $notification->email);
    }
}
