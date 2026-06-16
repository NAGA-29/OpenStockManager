<?php

namespace App\Enums;

enum TransactionType: string
{
    case In     = 'in';     // 入庫
    case Out    = 'out';    // 出庫
    case Adjust = 'adjust'; // 調整（棚卸など）

    public function label(): string
    {
        return match($this) {
            self::In     => '入庫',
            self::Out    => '出庫',
            self::Adjust => '調整',
        };
    }
}
