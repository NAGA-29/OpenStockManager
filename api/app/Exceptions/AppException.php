<?php

namespace App\Exceptions;

use RuntimeException;

abstract class AppException extends RuntimeException
{
    public function __construct(string $message, private readonly array $context = [], ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }

    public function context(): array
    {
        return $this->context;
    }

    public function getContext(): array
    {
        return $this->context();
    }
}
