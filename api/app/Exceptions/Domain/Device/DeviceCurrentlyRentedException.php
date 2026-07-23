<?php

namespace App\Exceptions\Domain\Device;

use App\Exceptions\Domain\DeviceException;

class DeviceCurrentlyRentedException extends DeviceException
{
    public static function forDevice(string $deviceId): self
    {
        return new self(
            __('messages.device_currently_rented', ['device_id' => $deviceId]),
            ['device_id' => $deviceId]
        );
    }
}
