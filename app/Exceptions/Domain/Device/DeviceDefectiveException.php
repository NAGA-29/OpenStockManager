<?php

namespace App\Exceptions\Domain\Device;

use App\Exceptions\Domain\DeviceException;

class DeviceDefectiveException extends DeviceException
{
    public static function forDevice(string $deviceId): self
    {
        return new self(
            __('messages.device_defective', ['device_id' => $deviceId]),
            ['device_id' => $deviceId]
        );
    }
}
