<?php

namespace Tests\Unit\Models;

use App\Models\Content;
use PHPUnit\Framework\TestCase;

class ContentTest extends TestCase
{
    private Content $content;

    protected function setUp(): void
    {
        parent::setUp();
        $this->content = new Content();
    }

    public function test_primary_key_is_not_incrementing(): void
    {
        $this->assertFalse($this->content->getIncrementing());
    }

    public function test_fillable_contains_all_expected_fields(): void
    {
        $expected = [
            'id', 'filename', 'extension', 'hash', 'path',
            'height', 'width', 'size', 'thumbnail', 'device_id',
        ];

        $fillable = $this->content->getFillable();
        foreach ($expected as $field) {
            $this->assertContains($field, $fillable, "Fillable should contain '{$field}'");
        }
    }

    public function test_casts_contain_datetime_fields(): void
    {
        $casts = $this->content->getCasts();

        $this->assertArrayHasKey('created_at', $casts);
        $this->assertArrayHasKey('modified_at', $casts);
        $this->assertEquals('datetime', $casts['created_at']);
        $this->assertEquals('datetime', $casts['modified_at']);
    }
}
