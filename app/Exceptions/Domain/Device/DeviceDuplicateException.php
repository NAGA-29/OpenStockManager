<?php

namespace App\Exceptions\Domain\Device;

use App\Exceptions\Domain\DeviceException;

class DeviceDuplicateException extends DeviceException
{
    public static function forDevice(string $deviceId): self
    {
        return new self(
            __('messages.device_duplicate', ['device_id' => $deviceId]),
            ['device_id' => $deviceId]
        );
    }
}
