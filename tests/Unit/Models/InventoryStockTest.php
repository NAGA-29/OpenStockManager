<?php

namespace Tests\Unit\Models;

use App\Models\InventoryStock;
use PHPUnit\Framework\TestCase;

class InventoryStockTest extends TestCase
{
    private InventoryStock $inventoryStock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->inventoryStock = new InventoryStock();
    }

    // =========================================================================
    // Fillable
    // =========================================================================

    public function test_fillable_contains_all_expected_fields(): void
    {
        $expected = [
            'item_id',
            'location',
            'quantity',
            'min_stock',
        ];

        $fillable = $this->inventoryStock->getFillable();
        foreach ($expected as $field) {
            $this->assertContains($field, $fillable, "Fillable should contain '{$field}'");
        }
    }

    // =========================================================================
    // Casts
    // =========================================================================

    public function test_quantity_is_cast_to_integer(): void
    {
        $casts = $this->inventoryStock->getCasts();

        $this->assertArrayHasKey('quantity', $casts);
        $this->assertSame('integer', $casts['quantity']);
    }

    public function test_min_stock_is_cast_to_integer(): void
    {
        $casts = $this->inventoryStock->getCasts();

        $this->assertArrayHasKey('min_stock', $casts);
        $this->assertSame('integer', $casts['min_stock']);
    }

    // =========================================================================
    // isBelowMinStock() – 境界条件
    // =========================================================================

    public function test_is_below_min_stock_returns_true_when_quantity_is_less_than_min_stock(): void
    {
        $this->inventoryStock->quantity  = 3;
        $this->inventoryStock->min_stock = 5;

        $this->assertTrue($this->inventoryStock->isBelowMinStock());
    }

    public function test_is_below_min_stock_returns_false_when_quantity_equals_min_stock(): void
    {
        $this->inventoryStock->quantity  = 5;
        $this->inventoryStock->min_stock = 5;

        $this->assertFalse($this->inventoryStock->isBelowMinStock());
    }

    public function test_is_below_min_stock_returns_false_when_quantity_exceeds_min_stock(): void
    {
        $this->inventoryStock->quantity  = 10;
        $this->inventoryStock->min_stock = 5;

        $this->assertFalse($this->inventoryStock->isBelowMinStock());
    }

    public function test_is_below_min_stock_returns_false_when_min_stock_is_null(): void
    {
        $this->inventoryStock->quantity  = 0;
        $this->inventoryStock->min_stock = null;

        $this->assertFalse($this->inventoryStock->isBelowMinStock());
    }

    public function test_is_below_min_stock_returns_true_when_quantity_is_zero_and_min_stock_is_positive(): void
    {
        $this->inventoryStock->quantity  = 0;
        $this->inventoryStock->min_stock = 1;

        $this->assertTrue($this->inventoryStock->isBelowMinStock());
    }

    // =========================================================================
    // Relationships (return type verification via reflection)
    // =========================================================================

    /**
     * @dataProvider relationReturnTypeProvider
     */
    public function test_relation_method_has_correct_return_type(string $method, string $expectedType): void
    {
        $reflection = new \ReflectionMethod(InventoryStock::class, $method);
        $returnType = $reflection->getReturnType();

        $this->assertNotNull($returnType, "Method '{$method}' should have a return type declaration");
        $this->assertSame($expectedType, $returnType->getName());
    }

    public static function relationReturnTypeProvider(): array
    {
        return [
            'item'              => ['item',              \Illuminate\Database\Eloquent\Relations\BelongsTo::class],
            'stockTransactions' => ['stockTransactions', \Illuminate\Database\Eloquent\Relations\HasMany::class],
        ];
    }
}
