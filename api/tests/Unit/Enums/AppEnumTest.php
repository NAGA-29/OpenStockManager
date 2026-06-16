<?php

namespace Tests\Unit\Enums;

use App\Enums\AppEnum;
use PHPUnit\Framework\TestCase;

class AppEnumTest extends TestCase
{
    public function test_app_types_contains_expected_values(): void
    {
        $expected = [
            'None',
            'Product-A',
            'Product-B',
            'Product-C',
        ];
        $this->assertEquals($expected, AppEnum::APP_TYPES);
    }

    public function test_app_types_has_seven_entries(): void
    {
        $this->assertCount(4, AppEnum::APP_TYPES);
    }

    public function test_app_types_first_entry_is_none(): void
    {
        $this->assertEquals('None', AppEnum::APP_TYPES[0]);
    }

    /**
     * @dataProvider appTypeProvider
     */
    public function test_app_types_contains_each_type(string $type): void
    {
        $this->assertContains($type, AppEnum::APP_TYPES);
    }

    public static function appTypeProvider(): array
    {
        return [
            'None' => ['None'],
            'Product-A' => ['Product-A'],
            'Product-B' => ['Product-B'],
            'Product-C' => ['Product-C'],
        ];
    }
}
