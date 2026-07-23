<?php

namespace Tests\Unit\Models;

use App\Enums\TrackingType;
use App\Enums\TransactionType;
use App\Models\StockTransaction;
use PHPUnit\Framework\TestCase;

class StockTransactionTest extends TestCase
{
    private StockTransaction $stockTransaction;

    protected function setUp(): void
    {
        parent::setUp();
        $this->stockTransaction = new StockTransaction();
    }

    // =========================================================================
    // Fillable
    // =========================================================================

    public function test_fillable_contains_all_expected_fields(): void
    {
        $expected = [
            'tracking_type',
            'transaction_type',
            'item_id',
            'inventory_unit_id',
            'inventory_stock_id',
            'quantity_change',
            'reason',
            'note',
            'staff_id',
            'transacted_at',
        ];

        $fillable = $this->stockTransaction->getFillable();
        foreach ($expected as $field) {
            $this->assertContains($field, $fillable, "Fillable should contain '{$field}'");
        }
    }

    // =========================================================================
    // Casts
    // =========================================================================

    public function test_tracking_type_is_cast_to_enum(): void
    {
        $casts = $this->stockTransaction->getCasts();

        $this->assertArrayHasKey('tracking_type', $casts);
        $this->assertSame(TrackingType::class, $casts['tracking_type']);
    }

    public function test_transaction_type_is_cast_to_enum(): void
    {
        $casts = $this->stockTransaction->getCasts();

        $this->assertArrayHasKey('transaction_type', $casts);
        $this->assertSame(TransactionType::class, $casts['transaction_type']);
    }

    public function test_quantity_change_is_cast_to_integer(): void
    {
        $casts = $this->stockTransaction->getCasts();

        $this->assertArrayHasKey('quantity_change', $casts);
        $this->assertSame('integer', $casts['quantity_change']);
    }

    public function test_transacted_at_is_cast_to_datetime(): void
    {
        $casts = $this->stockTransaction->getCasts();

        $this->assertArrayHasKey('transacted_at', $casts);
        $this->assertSame('datetime', $casts['transacted_at']);
    }

    // =========================================================================
    // Relationships (return type verification via reflection)
    // =========================================================================

    /**
     * @dataProvider relationReturnTypeProvider
     */
    public function test_relation_method_has_correct_return_type(string $method, string $expectedType): void
    {
        $reflection = new \ReflectionMethod(StockTransaction::class, $method);
        $returnType = $reflection->getReturnType();

        $this->assertNotNull($returnType, "Method '{$method}' should have a return type declaration");
        $this->assertSame($expectedType, $returnType->getName());
    }

    public static function relationReturnTypeProvider(): array
    {
        return [
            'item'           => ['item',           \Illuminate\Database\Eloquent\Relations\BelongsTo::class],
            'inventoryUnit'  => ['inventoryUnit',  \Illuminate\Database\Eloquent\Relations\BelongsTo::class],
            'inventoryStock' => ['inventoryStock', \Illuminate\Database\Eloquent\Relations\BelongsTo::class],
            'staff'          => ['staff',          \Illuminate\Database\Eloquent\Relations\BelongsTo::class],
        ];
    }
}
