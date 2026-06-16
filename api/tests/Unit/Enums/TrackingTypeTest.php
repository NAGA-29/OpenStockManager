<?php

namespace Tests\Unit\Enums;

use App\Enums\TrackingType;
use PHPUnit\Framework\TestCase;

class TrackingTypeTest extends TestCase
{
    // =========================================================================
    // Cases
    // =========================================================================

    public function test_has_individual_case(): void
    {
        $this->assertSame('individual', TrackingType::Individual->value);
    }

    public function test_has_quantity_case(): void
    {
        $this->assertSame('quantity', TrackingType::Quantity->value);
    }

    public function test_case_count_is_two(): void
    {
        $this->assertCount(2, TrackingType::cases());
    }

    // =========================================================================
    // label()
    // =========================================================================

    /**
     * @dataProvider labelProvider
     */
    public function test_label_returns_expected_japanese_text(TrackingType $type, string $expected): void
    {
        $this->assertSame($expected, $type->label());
    }

    public static function labelProvider(): array
    {
        return [
            '個別管理' => [TrackingType::Individual, '個別管理'],
            '数量管理' => [TrackingType::Quantity, '数量管理'],
        ];
    }

    // =========================================================================
    // from() / tryFrom()
    // =========================================================================

    public function test_from_returns_correct_case_for_individual(): void
    {
        $this->assertSame(TrackingType::Individual, TrackingType::from('individual'));
    }

    public function test_from_returns_correct_case_for_quantity(): void
    {
        $this->assertSame(TrackingType::Quantity, TrackingType::from('quantity'));
    }

    public function test_try_from_returns_null_for_unknown_value(): void
    {
        $this->assertNull(TrackingType::tryFrom('unknown'));
    }
}
