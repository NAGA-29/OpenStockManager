<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class DeviceEnum extends Enum
{
    public const DEVICE_TYPES = [
        'STB', // STB
        'TAB', // タブレット
        'CAM', // カメラ
        'SIGN', // サイネージ筐体
        'OTH', // その他
    ];

    public const DEVICE_OS = [
        'None',
        'Windows',
        'Android',
        'Linux-Ubuntu',
        'Linux-Debian',
        'iOS',
        'MacOS',
        'RaspberryPi',
    ];

    public const CONDITIONS = [
        1 => '新品',
        2 => '新古品',
        3 => '中古',
        4 => 'ジャンク',
        5 => '不明',
    ];
}
