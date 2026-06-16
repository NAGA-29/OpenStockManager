<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\DeviceCategory;
use Illuminate\Http\JsonResponse;

class DeviceController extends Controller
{
    /**
     * カテゴリコードごとの端末一覧（個別管理）を JSON で返す。
     */
    public function byCategory(string $code): JsonResponse
    {
        $devices = Device::where('device_type', $code)
            ->whereNull('soft_deleted_at')
            ->orderBy('device_id')
            ->get()
            ->map(fn (Device $device) => $this->resource($device));

        return response()->json([
            'category' => DeviceCategory::where('code', $code)->value('name') ?? $code,
            'data'     => $devices,
        ]);
    }

    /**
     * 端末個別詳細を JSON で返す。
     */
    public function show(string $deviceId): JsonResponse
    {
        $device = Device::with('condition')
            ->where('device_id', $deviceId)
            ->firstOrFail();

        return response()->json([
            'data' => array_merge($this->resource($device), [
                'custom_fields' => $device->custom_fields,
                'note'          => $device->note,
                'option'        => $device->option,
            ]),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function resource(Device $device): array
    {
        return [
            'device_id'     => $device->device_id,
            'device_type'   => $device->device_type,
            'device_name'   => $device->device_name,
            'device_serial' => $device->device_serial,
            'lending_now'   => $device->lending_now,
            'defective'     => (bool) $device->defective,
            'not_for_sale'  => (bool) $device->not_for_sale,
            'condition'     => $device->condition->name ?? null,
        ];
    }
}
