<?php

namespace Tests\Unit\Models;

use App\Models\DeviceCategory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Models\DeviceCategory
 */
class DeviceCategoryTest extends TestCase
{
    private DeviceCategory $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->category = new DeviceCategory();
    }

    // =========================================================================
    // Fillable
    // =========================================================================

    /**
     * fillable に期待するフィールドがすべて含まれること
     */
    public function test_fillable_contains_all_expected_fields(): void
    {
        $expected = ['code', 'name', 'icon', 'sort_order', 'is_active'];

        foreach ($expected as $field) {
            $this->assertContains($field, $this->category->getFillable(), "Fillable should contain '{$field}'");
        }
    }

    // =========================================================================
    // Casts
    // =========================================================================

    /**
     * is_active が boolean にキャストされること
     */
    public function test_is_active_is_cast_to_boolean(): void
    {
        $casts = $this->category->getCasts();

        $this->assertEquals('boolean', $casts['is_active']);
    }

    /**
     * sort_order が integer にキャストされること
     */
    public function test_sort_order_is_cast_to_integer(): void
    {
        $casts = $this->category->getCasts();

        $this->assertEquals('integer', $casts['sort_order']);
    }
}
