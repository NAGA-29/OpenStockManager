<?php

namespace Tests\Unit\Enums;

use App\Enums\ContentEnum;
use PHPUnit\Framework\TestCase;

class ContentEnumTest extends TestCase
{
    public function test_image_dir_has_expected_value(): void
    {
        $this->assertEquals('storage/device_img/', ContentEnum::IMAGE_DIR);
    }

    public function test_image_dir_ends_with_slash(): void
    {
        $this->assertStringEndsWith('/', ContentEnum::IMAGE_DIR);
    }
}
