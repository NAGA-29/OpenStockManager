<?php

namespace Tests\Unit\Models;

use App\Enums\TrackingType;
use App\Models\Item;
use PHPUnit\Framework\TestCase;

class ItemTest extends TestCase
{
    private Item $item;

    protected function setUp(): void
    {
        parent::setUp();
        $this->item = new Item();
    }

    // =========================================================================
    // Fillable
    // =========================================================================

    public function test_fillable_contains_all_expected_fields(): void
    {
        $expected = [
            'name',
            'description',
            'category_code',
            'tracking_type',
            'unit',
            'custom_fields',
            'is_active',
        ];

        $fillable = $this->item->getFillable();
        foreach ($expected as $field) {
            $this->assertContains($field, $fillable, "Fillable should contain '{$field}'");
        }
    }

    // =========================================================================
    // Casts
    // =========================================================================

    public function test_tracking_type_is_cast_to_enum(): void
    {
        $casts = $this->item->getCasts();

        $this->assertArrayHasKey('tracking_type', $casts);
        $this->assertSame(TrackingType::class, $casts['tracking_type']);
    }

    public function test_custom_fields_is_cast_to_array(): void
    {
        $casts = $this->item->getCasts();

        $this->assertArrayHasKey('custom_fields', $casts);
        $this->assertSame('array', $casts['custom_fields']);
    }

    public function test_is_active_is_cast_to_boolean(): void
    {
        $casts = $this->item->getCasts();

        $this->assertArrayHasKey('is_active', $casts);
        $this->assertSame('boolean', $casts['is_active']);
    }

    // =========================================================================
    // isIndividual() / isQuantity()
    // =========================================================================

    public function test_is_individual_returns_true_when_tracking_type_is_individual(): void
    {
        $this->item->tracking_type = TrackingType::Individual;

        $this->assertTrue($this->item->isIndividual());
    }

    public function test_is_individual_returns_false_when_tracking_type_is_quantity(): void
    {
        $this->item->tracking_type = TrackingType::Quantity;

        $this->assertFalse($this->item->isIndividual());
    }

    public function test_is_quantity_returns_true_when_tracking_type_is_quantity(): void
    {
        $this->item->tracking_type = TrackingType::Quantity;

        $this->assertTrue($this->item->isQuantity());
    }

    public function test_is_quantity_returns_false_when_tracking_type_is_individual(): void
    {
        $this->item->tracking_type = TrackingType::Individual;

        $this->assertFalse($this->item->isQuantity());
    }

    // =========================================================================
    // Relationships (return type verification via reflection)
    // =========================================================================

    /**
     * @dataProvider relationReturnTypeProvider
     */
    public function test_relation_method_has_correct_return_type(string $method, string $expectedType): void
    {
        $reflection = new \ReflectionMethod(Item::class, $method);
        $returnType = $reflection->getReturnType();

        $this->assertNotNull($returnType, "Method '{$method}' should have a return type declaration");
        $this->assertSame($expectedType, $returnType->getName());
    }

    public static function relationReturnTypeProvider(): array
    {
        return [
            'inventoryUnits'    => ['inventoryUnits',    \Illuminate\Database\Eloquent\Relations\HasMany::class],
            'inventoryStocks'   => ['inventoryStocks',   \Illuminate\Database\Eloquent\Relations\HasMany::class],
            'stockTransactions' => ['stockTransactions', \Illuminate\Database\Eloquent\Relations\HasMany::class],
            'category'          => ['category',          \Illuminate\Database\Eloquent\Relations\BelongsTo::class],
        ];
    }
}
