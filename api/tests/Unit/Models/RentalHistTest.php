<?php

namespace Tests\Unit\Models;

use App\Models\RentalHist;
use PHPUnit\Framework\TestCase;

class RentalHistTest extends TestCase
{
    private RentalHist $rentalHist;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rentalHist = new RentalHist();
    }

    // =========================================================================
    // Model Configuration
    // =========================================================================

    public function test_primary_key_is_lend_id(): void
    {
        $this->assertEquals('lend_id', $this->rentalHist->getKeyName());
    }

    public function test_primary_key_is_not_incrementing(): void
    {
        $this->assertFalse($this->rentalHist->getIncrementing());
    }

    public function test_updated_at_column_is_modified_at(): void
    {
        $this->assertEquals('modified_at', RentalHist::UPDATED_AT);
    }

    // =========================================================================
    // Fillable
    // =========================================================================

    public function test_fillable_contains_all_expected_fields(): void
    {
        $expected = [
            'lend_id', 'client', 'contact', 'staff',
            'all_returned', 'checkout_at', 'schedule_return_at',
            'return_at', 'note', 'created_at', 'modified_at',
            'soft_deleted_at',
        ];

        $fillable = $this->rentalHist->getFillable();
        foreach ($expected as $field) {
            $this->assertContains($field, $fillable, "Fillable should contain '{$field}'");
        }
    }

    // =========================================================================
    // Casts
    // =========================================================================

    public function test_datetime_fields_are_properly_cast(): void
    {
        $casts = $this->rentalHist->getCasts();

        $this->assertEquals('datetime', $casts['checkout_at']);
        $this->assertEquals('datetime', $casts['schedule_return_at']);
        $this->assertEquals('datetime', $casts['return_at']);
    }

    // =========================================================================
    // makeDeadLineMessage (via reflection to test private method)
    // =========================================================================

    public function test_makeDeadLineMessage_returns_empty_string_for_empty_collection(): void
    {
        $collection = new \Illuminate\Database\Eloquent\Collection([]);

        $method = new \ReflectionMethod(RentalHist::class, 'makeDeadLineMessage');
        $method->setAccessible(true);

        $result = $method->invoke($this->rentalHist, $collection);

        $this->assertEquals('', $result);
    }

    public function test_makeDeadLineMessage_returns_formatted_message_for_single_deadline(): void
    {
        $client = new \stdClass();
        $client->company = 'テスト株式会社';

        $deadline = new \stdClass();
        $deadline->lend_id = 'LEND-001';
        $deadline->clients = $client;

        $collection = new \Illuminate\Database\Eloquent\Collection([$deadline]);

        $method = new \ReflectionMethod(RentalHist::class, 'makeDeadLineMessage');
        $method->setAccessible(true);

        $result = $method->invoke($this->rentalHist, $collection);

        $this->assertStringContainsString('下記の返却期限が迫っています。', $result);
        $this->assertStringContainsString('==================================', $result);
        $this->assertStringContainsString('レンタルID: LEND-001', $result);
        $this->assertStringContainsString('クライアント名: テスト株式会社様', $result);
    }

    public function test_makeDeadLineMessage_returns_formatted_message_for_multiple_deadlines(): void
    {
        $client1 = new \stdClass();
        $client1->company = '会社A';

        $client2 = new \stdClass();
        $client2->company = '会社B';

        $deadline1 = new \stdClass();
        $deadline1->lend_id = 'LEND-001';
        $deadline1->clients = $client1;

        $deadline2 = new \stdClass();
        $deadline2->lend_id = 'LEND-002';
        $deadline2->clients = $client2;

        $collection = new \Illuminate\Database\Eloquent\Collection([$deadline1, $deadline2]);

        $method = new \ReflectionMethod(RentalHist::class, 'makeDeadLineMessage');
        $method->setAccessible(true);

        $result = $method->invoke($this->rentalHist, $collection);

        $this->assertStringContainsString('レンタルID: LEND-001', $result);
        $this->assertStringContainsString('クライアント名: 会社A様', $result);
        $this->assertStringContainsString('レンタルID: LEND-002', $result);
        $this->assertStringContainsString('クライアント名: 会社B様', $result);

        // 3 separators: header + 1 per deadline
        $this->assertEquals(3, substr_count($result, '=================================='));
    }

    public function test_makeDeadLineMessage_includes_html_br_tags(): void
    {
        $client = new \stdClass();
        $client->company = 'テスト会社';

        $deadline = new \stdClass();
        $deadline->lend_id = 'LEND-001';
        $deadline->clients = $client;

        $collection = new \Illuminate\Database\Eloquent\Collection([$deadline]);

        $method = new \ReflectionMethod(RentalHist::class, 'makeDeadLineMessage');
        $method->setAccessible(true);

        $result = $method->invoke($this->rentalHist, $collection);

        // Message should use HTML <br> tags for line breaks
        $this->assertStringContainsString('<br>', $result);
    }
}
