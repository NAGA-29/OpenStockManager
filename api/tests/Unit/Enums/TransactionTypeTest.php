<?php

namespace Tests\Unit\Enums;

use App\Enums\TransactionType;
use PHPUnit\Framework\TestCase;

class TransactionTypeTest extends TestCase
{
    // =========================================================================
    // Cases
    // =========================================================================

    public function test_has_in_case(): void
    {
        $this->assertSame('in', TransactionType::In->value);
    }

    public function test_has_out_case(): void
    {
        $this->assertSame('out', TransactionType::Out->value);
    }

    public function test_has_adjust_case(): void
    {
        $this->assertSame('adjust', TransactionType::Adjust->value);
    }

    public function test_case_count_is_three(): void
    {
        $this->assertCount(3, TransactionType::cases());
    }

    // =========================================================================
    // label()
    // =========================================================================

    /**
     * @dataProvider labelProvider
     */
    public function test_label_returns_expected_japanese_text(TransactionType $type, string $expected): void
    {
        $this->assertSame($expected, $type->label());
    }

    public static function labelProvider(): array
    {
        return [
            '入庫' => [TransactionType::In,     '入庫'],
            '出庫' => [TransactionType::Out,    '出庫'],
            '調整' => [TransactionType::Adjust, '調整'],
        ];
    }

    // =========================================================================
    // from() / tryFrom()
    // =========================================================================

    public function test_from_returns_correct_case_for_in(): void
    {
        $this->assertSame(TransactionType::In, TransactionType::from('in'));
    }

    public function test_from_returns_correct_case_for_out(): void
    {
        $this->assertSame(TransactionType::Out, TransactionType::from('out'));
    }

    public function test_from_returns_correct_case_for_adjust(): void
    {
        $this->assertSame(TransactionType::Adjust, TransactionType::from('adjust'));
    }

    public function test_try_from_returns_null_for_unknown_value(): void
    {
        $this->assertNull(TransactionType::tryFrom('unknown'));
    }
}
