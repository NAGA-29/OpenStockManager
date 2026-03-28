<?php

namespace Tests\Unit\Exceptions;

use App\Exceptions\Infrastructure\ImageProcessingException;
use RuntimeException;
use Tests\TestCase;

/**
 * @covers \App\Exceptions\Infrastructure\ImageProcessingException
 */
class ImageProcessingExceptionTest extends TestCase
{
    /**
     * analysisFailure() が ImageProcessingException のインスタンスを返すこと
     */
    public function test_analysisFailure_returns_ImageProcessingException_instance(): void
    {
        $e = ImageProcessingException::analysisFailure('/path/to/image.jpg');

        $this->assertInstanceOf(ImageProcessingException::class, $e);
    }

    /**
     * analysisFailure() の context に file_path が設定されること
     */
    public function test_analysisFailure_sets_file_path_in_context(): void
    {
        $filePath = '/storage/uploads/photo.png';
        $e = ImageProcessingException::analysisFailure($filePath);

        $this->assertEquals($filePath, $e->getContext()['file_path']);
    }

    /**
     * $previous 例外を渡した場合、getPrevious() で取得できること
     */
    public function test_analysisFailure_preserves_previous_exception(): void
    {
        $previous = new RuntimeException('GD library error');
        $e = ImageProcessingException::analysisFailure('/path/to/image.jpg', $previous);

        $this->assertSame($previous, $e->getPrevious());
    }

    /**
     * $previous に null を渡した場合、getPrevious() が null を返すこと
     */
    public function test_analysisFailure_accepts_null_previous(): void
    {
        $e = ImageProcessingException::analysisFailure('/path/to/image.jpg', null);

        $this->assertNull($e->getPrevious());
    }

    /**
     * context に file_path キーが含まれること
     */
    public function test_analysisFailure_context_has_file_path_key(): void
    {
        $e = ImageProcessingException::analysisFailure('/some/file.mp4');

        $this->assertArrayHasKey('file_path', $e->getContext());
    }
}
