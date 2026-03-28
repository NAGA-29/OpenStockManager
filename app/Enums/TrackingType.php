<?php

namespace App\Enums;

enum TrackingType: string
{
    case Individual = 'individual'; // 個別管理（シリアル番号・個体追跡）
    case Quantity   = 'quantity';   // 数量管理（ロケーション×品目の総数）

    public function label(): string
    {
        return match($this) {
            self::Individual => '個別管理',
            self::Quantity   => '数量管理',
        };
    }
}
