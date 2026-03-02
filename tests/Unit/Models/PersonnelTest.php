<?php

namespace Tests\Unit\Models;

use App\Models\Personnel;
use PHPUnit\Framework\TestCase;

class PersonnelTest extends TestCase
{
    private Personnel $personnel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->personnel = new Personnel();
    }

    public function test_primary_key_is_personnel_id(): void
    {
        $this->assertEquals('personnel_id', $this->personnel->getKeyName());
    }

    public function test_primary_key_is_not_incrementing(): void
    {
        $this->assertFalse($this->personnel->getIncrementing());
    }

    public function test_updated_at_column_is_modified_at(): void
    {
        $this->assertEquals('modified_at', Personnel::UPDATED_AT);
    }

    public function test_fillable_contains_all_expected_fields(): void
    {
        $expected = [
            'personnel_id', 'client_id', 'name', 'tel',
            'email', 'note', 'created_at', 'modified_at',
            'soft_deleted_at',
        ];

        $fillable = $this->personnel->getFillable();
        foreach ($expected as $field) {
            $this->assertContains($field, $fillable, "Fillable should contain '{$field}'");
        }
    }
}
