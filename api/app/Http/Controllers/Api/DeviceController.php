<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\DeviceCategory;
use App\Models\DeviceTypeField;
use Illuminate\Http\JsonResponse;

class DeviceController extends Controller
{
    /**
     * カテゴリコードごとの端末一覧（個別管理）を JSON で返す。
     *
     * 旧 `devices/device_list.blade.php` 相当。タブ用カテゴリ一覧・サマリー件数・
     * 一覧行（ステータスアイコン判定に必要なフラグ込み）をまとめて返す。
     */
    public function byCategory(string $code): JsonResponse
    {
        $categories = DeviceCategory::active()->ordered()->get();
        $current    = $categories->firstWhere('code', $code);

        if (! $current) {
            abort(404, __('messages.device_not_found'));
        }

        $devices = Device::with('condition')
            ->withCount('contents')
            ->where('device_type', $code)
            ->whereNull('soft_deleted_at')
            ->orderBy('device_id', 'desc')
            ->get()
            ->map(fn (Device $device) => array_merge($this->resource($device), [
                'has_images' => $device->contents_count > 0,
            ]));

        $countBase = fn () => Device::where('device_type', $code)->whereNull('soft_deleted_at');

        return response()->json([
            'categories' => $categories->map(fn (DeviceCategory $cat) => [
                'code' => $cat->code,
                'name' => $cat->name,
                'icon' => $cat->icon,
            ])->values(),
            'current' => [
                'code' => $current->code,
                'name' => $current->name,
                'icon' => $current->icon,
            ],
            'counts' => [
                'all'       => $countBase()->count(),
                'lending'   => $countBase()->where('lending_now', '<>', '')->whereNotNull('lending_now')->count(),
                'defective' => $countBase()->where('defective', 1)->count(),
            ],
            // 旧レスポンスとの後方互換（カテゴリ名）。
            'category' => $current->name,
            'data'     => $devices,
        ]);
    }

    /**
     * 端末個別詳細を JSON で返す。
     *
     * 旧 `devices/show.blade.php` 相当（読み取り表示分）。カスタムフィールドは
     * 定義（ラベル・型・select 表示名）を解決した一覧として返す。
     */
    public function show(string $deviceId): JsonResponse
    {
        $device = Device::with([
            'condition',
            'contents',
            'rental_hists.clients',
            'sale_hists.clients',
        ])
            ->where('device_id', $deviceId)
            ->firstOrFail();

        $fieldDefs = DeviceTypeField::where('device_category_code', $device->device_type)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $customFields = $fieldDefs->map(function (DeviceTypeField $def) use ($device) {
            $raw     = $device->custom_fields[$def->field_key] ?? null;
            $display = $raw;

            if ($def->field_type === 'select' && $def->options) {
                $display = collect($def->options)->firstWhere('value', $raw)['label'] ?? $raw;
            }

            return [
                'key'     => $def->field_key,
                'label'   => $def->label,
                'type'    => $def->field_type,
                'value'   => $raw,
                'display' => $display,
            ];
        })->values();

        $rentalHists = $device->rental_hists
            ->sortByDesc('checkout_at')
            ->map(fn ($rental) => [
                'lend_id'     => $rental->lend_id,
                'company'     => $rental->clients->company ?? null,
                'checkout_at' => optional($rental->checkout_at)->format('Y-m-d'),
            ])->values();

        $saleHists = $device->sale_hists
            ->map(fn ($sale) => [
                'sale_id'      => $sale->sale_id,
                'company'      => $sale->clients->company ?? null,
                'sale_date_at' => optional($sale->sale_date_at)->format('Y-m-d'),
            ])->values();

        return response()->json([
            'data' => array_merge($this->resource($device), [
                'option'             => $device->option,
                'using_user_id'      => $device->using_user_id,
                'first_work_date_at' => optional($device->first_work_date_at)->format('Y-m-d'),
                'purchase_date_at'   => optional($device->purchase_date_at)->format('Y-m-d'),
                'modified_at'        => optional($device->modified_at)->format('Y-m-d H:i:s'),
                'custom_fields'      => $customFields,
                'images'             => $device->contents->map(fn ($content) => [
                    'path'     => $content->path,
                    'filename' => $content->filename ?? null,
                ])->values(),
                'rental_hists' => $rentalHists,
                'sale_hists'   => $saleHists,
            ]),
        ]);
    }

    /**
     * 一覧・詳細で共通の端末基本情報。
     *
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
            'sale_id'       => $device->sale_id,
            'defective'     => (bool) $device->defective,
            'not_for_sale'  => (bool) $device->not_for_sale,
            'note'          => $device->note,
            'condition'     => $device->condition->condition ?? null,
        ];
    }
}
