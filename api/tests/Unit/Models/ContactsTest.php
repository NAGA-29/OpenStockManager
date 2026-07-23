<?php

namespace Tests\Unit\Models;

use App\Models\Contacts;
use PHPUnit\Framework\TestCase;

class ContactsTest extends TestCase
{
    private Contacts $contacts;

    protected function setUp(): void
    {
        parent::setUp();
        $this->contacts = new Contacts();
    }

    public function test_primary_key_is_id(): void
    {
        $this->assertEquals('id', $this->contacts->getKeyName());
    }

    public function test_primary_key_is_incrementing(): void
    {
        $this->assertTrue($this->contacts->getIncrementing());
    }

    public function test_updated_at_column_is_modified_at(): void
    {
        $this->assertEquals('modified_at', Contacts::UPDATED_AT);
    }

    public function test_fillable_contains_all_expected_fields(): void
    {
        $expected = [
            'id', 'client_id', 'name', 'tel',
            'email', 'note', 'created_at', 'modified_at',
            'soft_deleted_at',
        ];

        $fillable = $this->contacts->getFillable();
        foreach ($expected as $field) {
            $this->assertContains($field, $fillable, "Fillable should contain '{$field}'");
        }
    }
}
