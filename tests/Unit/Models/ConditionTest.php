<?php

namespace Tests\Unit\Models;

use App\Models\Condition;
use PHPUnit\Framework\TestCase;

class ConditionTest extends TestCase
{
    private Condition $condition;

    protected function setUp(): void
    {
        parent::setUp();
        $this->condition = new Condition();
    }

    public function test_fillable_contains_condition(): void
    {
        $fillable = $this->condition->getFillable();
        $this->assertContains('condition', $fillable);
    }

    public function test_primary_key_is_id(): void
    {
        $this->assertEquals('id', $this->condition->getKeyName());
    }

    public function test_primary_key_is_incrementing(): void
    {
        $this->assertTrue($this->condition->getIncrementing());
    }
}
