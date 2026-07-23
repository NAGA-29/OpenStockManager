<?php

namespace App\Exceptions\Infrastructure;

use App\Exceptions\AppException;

class ImageProcessingException extends AppException
{
    public static function analysisFailure(string $filePath, ?\Throwable $previous = null): self
    {
        return new self(
            __('messages.image_analysis_failed'),
            ['file_path' => $filePath],
            $previous
        );
    }
}
