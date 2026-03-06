<?php

namespace App\Exceptions\Infrastructure;

use App\Exceptions\AppException;

class CsvImportException extends AppException
{
    public static function forRow(int $rowNumber, string $reason, array $rowData = []): self
    {
        return new self($reason, ['row_number' => $rowNumber, 'row_data' => $rowData]);
    }
}
