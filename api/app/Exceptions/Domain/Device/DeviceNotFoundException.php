<?php

namespace App\Exceptions\Domain\Device;

use App\Exceptions\Domain\DeviceException;

class DeviceNotFoundException extends DeviceException
{
    public static function forDevice(string $deviceId): self
    {
        return new self(
            __('messages.device_not_exists', ['device_id' => $deviceId]),
            ['device_id' => $deviceId]
        );
    }
}
