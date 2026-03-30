<?php

namespace Tests\Unit\Models;

use App\Models\SaleHist;
use PHPUnit\Framework\TestCase;

class SaleHistTest extends TestCase
{
    private SaleHist $saleHist;

    protected function setUp(): void
    {
        parent::setUp();
        $this->saleHist = new SaleHist();
    }

    public function test_primary_key_is_sale_id(): void
    {
        $this->assertEquals('sale_id', $this->saleHist->getKeyName());
    }

    public function test_primary_key_is_not_incrementing(): void
    {
        $this->assertFalse($this->saleHist->getIncrementing());
    }

    public function test_updated_at_column_is_modified_at(): void
    {
        $this->assertEquals('modified_at', SaleHist::UPDATED_AT);
    }

    public function test_fillable_contains_all_expected_fields(): void
    {
        $expected = [
            'sale_id', 'client', 'contact', 'staff',
            'sale_date_at', 'note', 'created_at', 'modified_at',
            'soft_deleted_at',
        ];

        $fillable = $this->saleHist->getFillable();
        foreach ($expected as $field) {
            $this->assertContains($field, $fillable, "Fillable should contain '{$field}'");
        }
    }

    public function test_sale_date_at_is_cast_to_datetime(): void
    {
        $casts = $this->saleHist->getCasts();

        $this->assertArrayHasKey('sale_date_at', $casts);
        $this->assertEquals('datetime', $casts['sale_date_at']);
    }
}
