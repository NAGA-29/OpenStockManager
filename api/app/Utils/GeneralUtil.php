<?php

namespace App\Utils;

use Carbon\Carbon;
use Illuminate\Support\Str;

class GeneralUtil
{
    public static function generalID(?int $digit = null): string
    {
        if (is_int($digit)) {
            return Str::random($digit). '-'. Carbon::now()->getTimestamp() ;
        }
        return (string) Str::uuid();
    }

    public static function generalToken(?int $digit = null): string
    {
        if (is_int($digit)) {
            return Str::random($digit). '-'. Carbon::now()->getTimestamp() ;
        }
        return hash('sha256', Str::random(40));
    }

    /**
     * CSV値のインジェクション対策
     * =, +, -, @, \t, \r で始まるセル値にプレフィックスを付与
     *
     * @param string|null $value
     * @return string|null
     */
    public static function sanitizeCsvValue(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $dangerousChars = ['=', '+', '-', '@', "\t", "\r"];
        if (in_array(mb_substr($value, 0, 1), $dangerousChars, true)) {
            return "'" . $value;
        }

        return $value;
    }

    /**
     * CSVレコード全体をサニタイズ
     *
     * @param array $record
     * @return array
     */
    public static function sanitizeCsvRecord(array $record): array
    {
        return array_map(function ($value) {
            return is_string($value) ? self::sanitizeCsvValue($value) : $value;
        }, $record);
    }
}
