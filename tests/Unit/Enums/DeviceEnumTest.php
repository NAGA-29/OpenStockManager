<?php

namespace Tests\Unit\Enums;

use App\Enums\DeviceEnum;
use PHPUnit\Framework\TestCase;

class DeviceEnumTest extends TestCase
{
    // =========================================================================
    // DEVICE_TYPES
    // =========================================================================

    public function test_device_types_contains_expected_values(): void
    {
        $expected = ['STB', 'TAB', 'CAM', 'SIGN', 'OTH'];
        $this->assertEquals($expected, DeviceEnum::DEVICE_TYPES);
    }

    public function test_device_types_has_five_entries(): void
    {
        $this->assertCount(5, DeviceEnum::DEVICE_TYPES);
    }

    /**
     * @dataProvider deviceTypeProvider
     */
    public function test_device_types_contains_each_type(string $type): void
    {
        $this->assertContains($type, DeviceEnum::DEVICE_TYPES);
    }

    public static function deviceTypeProvider(): array
    {
        return [
            'STB' => ['STB'],
            'タブレット' => ['TAB'],
            'カメラ' => ['CAM'],
            'サイネージ' => ['SIGN'],
            'その他' => ['OTH'],
        ];
    }

    // =========================================================================
    // DEVICE_OS
    // =========================================================================

    public function test_device_os_contains_expected_values(): void
    {
        $expected = [
            'None', 'Windows', 'Android', 'Linux-Ubuntu',
            'Linux-Debian', 'iOS', 'MacOS', 'RaspberryPi',
        ];
        $this->assertEquals($expected, DeviceEnum::DEVICE_OS);
    }

    public function test_device_os_has_eight_entries(): void
    {
        $this->assertCount(8, DeviceEnum::DEVICE_OS);
    }

    public function test_device_os_first_entry_is_none(): void
    {
        $this->assertEquals('None', DeviceEnum::DEVICE_OS[0]);
    }

    // =========================================================================
    // CONDITIONS
    // =========================================================================

    public function test_conditions_contains_expected_values(): void
    {
        $expected = [
            1 => '新品',
            2 => '新古品',
            3 => '中古',
            4 => 'ジャンク',
            5 => '不明',
        ];
        $this->assertEquals($expected, DeviceEnum::CONDITIONS);
    }

    public function test_conditions_has_five_entries(): void
    {
        $this->assertCount(5, DeviceEnum::CONDITIONS);
    }

    public function test_conditions_keys_are_1_indexed(): void
    {
        $keys = array_keys(DeviceEnum::CONDITIONS);
        $this->assertEquals([1, 2, 3, 4, 5], $keys);
    }

    /**
     * @dataProvider conditionProvider
     */
    public function test_conditions_contains_each_condition(int $key, string $label): void
    {
        $this->assertArrayHasKey($key, DeviceEnum::CONDITIONS);
        $this->assertEquals($label, DeviceEnum::CONDITIONS[$key]);
    }

    public static function conditionProvider(): array
    {
        return [
            '新品' => [1, '新品'],
            '新古品' => [2, '新古品'],
            '中古' => [3, '中古'],
            'ジャンク' => [4, 'ジャンク'],
            '不明' => [5, '不明'],
        ];
    }
}
