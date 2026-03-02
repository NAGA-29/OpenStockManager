<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class AppEnum extends Enum
{
    public const APP_TYPES = [
        'None',
        'Product-A',
        'Product-B',
        'Product-C',
    ];
}
