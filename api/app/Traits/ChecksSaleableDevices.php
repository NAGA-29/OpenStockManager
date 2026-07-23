<?php

namespace App\Traits;

use App\Models\Device;
use Illuminate\Validation\Validator;

/**
 * 販売登録時の端末状態チェック（旧 StoreSaleCartRequest::withValidator 相当）。
 * 販売済み・貸出中・不良・販売対象外の端末を弾く。
 */
trait ChecksSaleableDevices
{
    /**
     * @param array<int, string> $deviceIds 検証対象の端末 ID 一覧
     * @param string $errorKey エラーを追加するフィールド名
     */
    protected function validateSaleableDevices(Validator $validator, array $deviceIds, string $errorKey): void
    {
        foreach ($deviceIds as $deviceId) {
            if (!$deviceId) {
                continue;
            }

            $device = Device::where('device_id', $deviceId)->first();
            if (!$device) {
                // 存在チェックは exists ルールが担当するためここではスキップ。
                continue;
            }

            if (!empty($device->sale_id)) {
                $validator->errors()->add($errorKey, "すでに販売されている端末が含まれています（{$deviceId}）。");
            } elseif (!empty($device->lending_now)) {
                $validator->errors()->add($errorKey, "現在貸出中の端末が含まれています（{$deviceId}）。");
            } elseif ($device->defective) {
                $validator->errors()->add($errorKey, "不良品の端末が含まれています（{$deviceId}）。");
            } elseif ($device->not_for_sale) {
                $validator->errors()->add($errorKey, "販売対象外の端末が含まれています（{$deviceId}）。");
            }
        }
    }
}
