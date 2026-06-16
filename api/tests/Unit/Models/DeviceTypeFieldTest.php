<?php

namespace Tests\Unit\Models;

use App\Models\DeviceTypeField;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Models\DeviceTypeField
 */
class DeviceTypeFieldTest extends TestCase
{
    private DeviceTypeField $field;

    protected function setUp(): void
    {
        parent::setUp();
        $this->field = new DeviceTypeField();
    }

    // =========================================================================
    // FIELD_TYPES constant
    // =========================================================================

    /**
     * FIELD_TYPES 定数に 'text' キーが含まれること
     */
    public function test_FIELD_TYPES_contains_text(): void
    {
        $this->assertArrayHasKey('text', DeviceTypeField::FIELD_TYPES);
    }

    /**
     * FIELD_TYPES 定数に 'number' キーが含まれること
     */
    public function test_FIELD_TYPES_contains_number(): void
    {
        $this->assertArrayHasKey('number', DeviceTypeField::FIELD_TYPES);
    }

    /**
     * FIELD_TYPES 定数に 'select' キーが含まれること
     */
    public function test_FIELD_TYPES_contains_select(): void
    {
        $this->assertArrayHasKey('select', DeviceTypeField::FIELD_TYPES);
    }

    /**
     * FIELD_TYPES 定数に 'boolean' キーが含まれること
     */
    public function test_FIELD_TYPES_contains_boolean(): void
    {
        $this->assertArrayHasKey('boolean', DeviceTypeField::FIELD_TYPES);
    }

    /**
     * FIELD_TYPES 定数のエントリ数がちょうど 4 であること
     */
    public function test_FIELD_TYPES_has_exactly_four_entries(): void
    {
        $this->assertCount(4, DeviceTypeField::FIELD_TYPES);
    }

    // =========================================================================
    // Casts
    // =========================================================================

    /**
     * options が array にキャストされること
     */
    public function test_options_is_cast_to_array(): void
    {
        $casts = $this->field->getCasts();

        $this->assertEquals('array', $casts['options']);
    }

    /**
     * is_required が boolean にキャストされること
     */
    public function test_is_required_is_cast_to_boolean(): void
    {
        $casts = $this->field->getCasts();

        $this->assertEquals('boolean', $casts['is_required']);
    }

    /**
     * sort_order が integer にキャストされること
     */
    public function test_sort_order_is_cast_to_integer(): void
    {
        $casts = $this->field->getCasts();

        $this->assertEquals('integer', $casts['sort_order']);
    }
}
