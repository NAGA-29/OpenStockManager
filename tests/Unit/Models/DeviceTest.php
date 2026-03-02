<?php

namespace Tests\Unit\Models;

use App\Models\Device;
use PHPUnit\Framework\TestCase;

class DeviceTest extends TestCase
{
    private Device $device;

    protected function setUp(): void
    {
        parent::setUp();
        $this->device = new Device();
    }

    // =========================================================================
    // Model Configuration
    // =========================================================================

    public function test_primary_key_is_device_id(): void
    {
        $this->assertEquals('device_id', $this->device->getKeyName());
    }

    public function test_primary_key_is_not_incrementing(): void
    {
        $this->assertFalse($this->device->getIncrementing());
    }

    public function test_updated_at_column_is_modified_at(): void
    {
        $this->assertEquals('modified_at', Device::UPDATED_AT);
    }

    // =========================================================================
    // Fillable
    // =========================================================================

    public function test_fillable_contains_all_expected_fields(): void
    {
        $expected = [
            'device_id', 'device_type', 'device_name', 'device_serial',
            'os', 'os_ver', 'first_work_date_at',
            'purchase_date_at', 'client', 'sale_date_at', 'option',
            'condition_id', 'defective', 'not_for_sale', 'note',
            'lending_now', 'using_user_id', 'created_at', 'modified_at',
            'soft_deleted_at',
        ];

        $fillable = $this->device->getFillable();
        foreach ($expected as $field) {
            $this->assertContains($field, $fillable, "Fillable should contain '{$field}'");
        }
    }

    // =========================================================================
    // Casts
    // =========================================================================

    public function test_date_fields_are_properly_cast(): void
    {
        $casts = $this->device->getCasts();

        $this->assertEquals('date:Y-m-d', $casts['first_work_date_at']);
        $this->assertEquals('date:Y-m-d', $casts['purchase_date_at']);
        $this->assertEquals('date:Y-m-d', $casts['sale_date_at']);
        $this->assertEquals('date:Y-m-d', $casts['soft_deleted_at']);
    }

    // =========================================================================
    // Accessor
    // =========================================================================

    public function test_device_serial_name_accessor_capitalizes_first_letter(): void
    {
        $result = $this->device->getDeviceSerialNameAttribute('abc-123');
        $this->assertEquals('Abc-123', $result);
    }

    public function test_device_serial_name_accessor_with_already_capitalized(): void
    {
        $result = $this->device->getDeviceSerialNameAttribute('ABC-123');
        $this->assertEquals('ABC-123', $result);
    }

    public function test_device_serial_name_accessor_with_empty_string(): void
    {
        $result = $this->device->getDeviceSerialNameAttribute('');
        $this->assertEquals('', $result);
    }

    public function test_device_serial_name_accessor_with_numeric_start(): void
    {
        $result = $this->device->getDeviceSerialNameAttribute('123abc');
        $this->assertEquals('123abc', $result);
    }
}
