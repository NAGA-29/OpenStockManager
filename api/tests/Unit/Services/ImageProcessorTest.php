<?php

namespace Tests\Unit\Services;

use App\Services\Image\ImageProcessor;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\TestCase;

class ImageProcessorTest extends TestCase
{
    private ImageProcessor $processor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->processor = new ImageProcessor();
    }

    public function test_process_returns_array_with_expected_keys(): void
    {
        $tempFile = $this->createTempImage(100, 80);

        $file = $this->createMock(UploadedFile::class);
        $file->method('getPathname')->willReturn($tempFile);
        $file->method('getClientOriginalName')->willReturn('test_image.png');
        $file->method('getClientOriginalExtension')->willReturn('png');
        $file->method('getSize')->willReturn(filesize($tempFile));
        $file->method('getMimeType')->willReturn('image/png');

        $result = $this->processor->process($file);

        $this->assertArrayHasKey('temp_path', $result);
        $this->assertArrayHasKey('original_name', $result);
        $this->assertArrayHasKey('extension', $result);
        $this->assertArrayHasKey('dimensions', $result);
        $this->assertArrayHasKey('size', $result);
        $this->assertArrayHasKey('hash', $result);

        unlink($tempFile);
    }

    public function test_process_returns_correct_original_name(): void
    {
        $tempFile = $this->createTempImage(50, 50);

        $file = $this->createMock(UploadedFile::class);
        $file->method('getPathname')->willReturn($tempFile);
        $file->method('getClientOriginalName')->willReturn('my_photo.jpg');
        $file->method('getClientOriginalExtension')->willReturn('jpg');
        $file->method('getSize')->willReturn(filesize($tempFile));
        $file->method('getMimeType')->willReturn('image/png');

        $result = $this->processor->process($file);

        $this->assertEquals('my_photo.jpg', $result['original_name']);

        unlink($tempFile);
    }

    public function test_process_returns_correct_extension(): void
    {
        $tempFile = $this->createTempImage(50, 50);

        $file = $this->createMock(UploadedFile::class);
        $file->method('getPathname')->willReturn($tempFile);
        $file->method('getClientOriginalName')->willReturn('test.png');
        $file->method('getClientOriginalExtension')->willReturn('png');
        $file->method('getSize')->willReturn(filesize($tempFile));
        $file->method('getMimeType')->willReturn('image/png');

        $result = $this->processor->process($file);

        $this->assertEquals('png', $result['extension']);

        unlink($tempFile);
    }

    public function test_process_extracts_image_dimensions(): void
    {
        $width = 200;
        $height = 150;
        $tempFile = $this->createTempImage($width, $height);

        $file = $this->createMock(UploadedFile::class);
        $file->method('getPathname')->willReturn($tempFile);
        $file->method('getClientOriginalName')->willReturn('test.png');
        $file->method('getClientOriginalExtension')->willReturn('png');
        $file->method('getSize')->willReturn(filesize($tempFile));
        $file->method('getMimeType')->willReturn('image/png');

        $result = $this->processor->process($file);

        $this->assertEquals($width, $result['dimensions']['width']);
        $this->assertEquals($height, $result['dimensions']['height']);

        unlink($tempFile);
    }

    public function test_process_calculates_sha256_hash(): void
    {
        $tempFile = $this->createTempImage(10, 10);

        $file = $this->createMock(UploadedFile::class);
        $file->method('getPathname')->willReturn($tempFile);
        $file->method('getClientOriginalName')->willReturn('test.png');
        $file->method('getClientOriginalExtension')->willReturn('png');
        $file->method('getSize')->willReturn(filesize($tempFile));
        $file->method('getMimeType')->willReturn('image/png');

        $result = $this->processor->process($file);

        // SHA-256 hash is 64 hex characters
        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $result['hash']);

        // Verify the hash matches the file
        $expectedHash = hash_file('sha256', $tempFile);
        $this->assertEquals($expectedHash, $result['hash']);

        unlink($tempFile);
    }

    public function test_process_returns_correct_file_size(): void
    {
        $tempFile = $this->createTempImage(100, 100);
        $expectedSize = filesize($tempFile);

        $file = $this->createMock(UploadedFile::class);
        $file->method('getPathname')->willReturn($tempFile);
        $file->method('getClientOriginalName')->willReturn('test.png');
        $file->method('getClientOriginalExtension')->willReturn('png');
        $file->method('getSize')->willReturn($expectedSize);
        $file->method('getMimeType')->willReturn('image/png');

        $result = $this->processor->process($file);

        $this->assertEquals($expectedSize, $result['size']);

        unlink($tempFile);
    }

    public function test_process_returns_null_dimensions_for_non_media_file(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($tempFile, 'plain text content');

        $file = $this->createMock(UploadedFile::class);
        $file->method('getPathname')->willReturn($tempFile);
        $file->method('getClientOriginalName')->willReturn('document.txt');
        $file->method('getClientOriginalExtension')->willReturn('txt');
        $file->method('getSize')->willReturn(filesize($tempFile));
        $file->method('getMimeType')->willReturn('text/plain');

        $result = $this->processor->process($file);

        $this->assertNull($result['dimensions']['width']);
        $this->assertNull($result['dimensions']['height']);

        unlink($tempFile);
    }

    public function test_process_returns_temp_path(): void
    {
        $tempFile = $this->createTempImage(10, 10);

        $file = $this->createMock(UploadedFile::class);
        $file->method('getPathname')->willReturn($tempFile);
        $file->method('getClientOriginalName')->willReturn('test.png');
        $file->method('getClientOriginalExtension')->willReturn('png');
        $file->method('getSize')->willReturn(filesize($tempFile));
        $file->method('getMimeType')->willReturn('image/png');

        $result = $this->processor->process($file);

        $this->assertEquals($tempFile, $result['temp_path']);

        unlink($tempFile);
    }

    /**
     * Helper: create a temporary PNG image file
     */
    private function createTempImage(int $width, int $height): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'img_test_') . '.png';
        $image = imagecreatetruecolor($width, $height);
        imagepng($image, $tempFile);
        imagedestroy($image);
        return $tempFile;
    }
}
