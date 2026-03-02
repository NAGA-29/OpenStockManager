<?php

namespace Tests\Unit\Models;

use App\Models\Client;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = new Client();
    }

    public function test_primary_key_is_client_id(): void
    {
        $this->assertEquals('client_id', $this->client->getKeyName());
    }

    public function test_primary_key_is_not_incrementing(): void
    {
        $this->assertFalse($this->client->getIncrementing());
    }

    public function test_updated_at_column_is_modified_at(): void
    {
        $this->assertEquals('modified_at', Client::UPDATED_AT);
    }

    public function test_fillable_contains_all_expected_fields(): void
    {
        $expected = [
            'client_id', 'company', 'url', 'tel',
            'post_code', 'street_address', 'note',
            'created_at', 'modified_at', 'soft_deleted_at',
        ];

        $fillable = $this->client->getFillable();
        foreach ($expected as $field) {
            $this->assertContains($field, $fillable, "Fillable should contain '{$field}'");
        }
    }
}
