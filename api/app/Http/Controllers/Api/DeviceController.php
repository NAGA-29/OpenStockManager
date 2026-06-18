<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDeviceApiRequest;
use App\Http\Requests\UploadSpecFileApiRequest;
use App\Http\Requests\UploadBenchmarkFileApiRequest;
use App\Http\Requests\UploadDeviceMultiApiRequest;
use App\Http\Requests\StoreDeviceMultiApiRequest;
use App\Models\Condition;
use App\Models\Device;
use App\Models\DeviceCategory;
use App\Models\DeviceTypeField;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use League\Csv\Reader;
use League\Csv\Statement;
use App\Utilities\GeneralUtil;

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
     * 端末登録フォームの選択肢を返す。
     *
     * 旧 `register_device` の動的フォーム相当。カテゴリ（種別ごとのカスタム
     * フィールド定義込み）とコンディション一覧を返し、フロントは選択カテゴリに
     * 応じてカスタムフィールドを描画する。
     */
    public function formOptions(): JsonResponse
    {
        $categories = DeviceCategory::active()
            ->ordered()
            ->with('fields')
            ->get()
            ->map(fn (DeviceCategory $cat) => [
                'code'   => $cat->code,
                'name'   => $cat->name,
                'fields' => $cat->fields->map(fn (DeviceTypeField $field) => [
                    'field_key'   => $field->field_key,
                    'label'       => $field->label,
                    'field_type'  => $field->field_type,
                    'is_required' => (bool) $field->is_required,
                    'options'     => $field->options,
                ])->values(),
            ])->values();

        $conditions = Condition::orderBy('id')
            ->get()
            ->map(fn (Condition $condition) => [
                'id'    => $condition->id,
                'label' => $condition->condition,
            ])->values();

        return response()->json([
            'categories' => $categories,
            'conditions' => $conditions,
        ]);
    }

    /**
     * 端末を単体登録する。
     *
     * 旧 `DevicesController::storeDevice` の保存ロジックを踏襲（device_id 自動採番）。
     * 画像アップロードは未対応（後続フェーズ）。
     */
    public function store(StoreDeviceApiRequest $request): JsonResponse
    {
        $safe = $request->validated();

        $deviceId = Device::generateDeviceId($safe['device_type'], $safe['device_name']);

        $device = Device::create([
            'device_id'          => $deviceId,
            'device_type'        => $safe['device_type'],
            'device_name'        => $safe['device_name'],
            'device_serial'      => $safe['device_serial'],
            'custom_fields'      => $safe['custom_fields'] ?? null,
            'first_work_date_at' => $safe['first_work_date_at'] ?? null,
            'purchase_date_at'   => $safe['purchase_date_at'] ?? null,
            'client'             => $safe['client'] ?? null,
            'condition_id'       => $safe['condition'],
            'defective'          => ! empty($safe['defective']),
            'not_for_sale'       => ! empty($safe['not_for_sale']),
            'note'               => $safe['note'] ?? null,
        ]);

        return response()->json([
            'data' => [
                'device_id' => $device->device_id,
            ],
        ], 201);
    }

    /**
     * 端末スペックファイル情報を返す。
     *
     * ファイルが存在すればファイル名・サイズ・更新日時を返し、
     * 存在しなければ空オブジェクトを返す。
     */
    public function getSpecFile(): JsonResponse
    {
        $files = Storage::disk('public')->files('spec/');

        if (empty($files)) {
            return response()->json(['data' => null]);
        }

        $filePath = $files[0];
        $fileInfo = [
            'filename'   => basename($filePath),
            'size'       => Storage::disk('public')->size($filePath),
            'updated_at' => date('Y-m-d H:i:s', Storage::disk('public')->lastModified($filePath)),
        ];

        return response()->json(['data' => $fileInfo]);
    }

    /**
     * 端末スペックファイルをアップロードする。
     *
     * 既存ファイルがあれば削除してから新規保存。成功時は 200 ファイル情報を返す。
     */
    public function uploadSpecFile(UploadSpecFileApiRequest $request): JsonResponse
    {
        $file = $request->file('spec_file');
        $fileName = $file->getClientOriginalName();

        if (! Storage::disk('public')->exists('spec')) {
            Storage::disk('public')->makeDirectory('spec');
        }

        // 既存ファイルを削除
        $oldFiles = Storage::disk('public')->files('spec/');
        Storage::disk('public')->delete($oldFiles);

        // 新規ファイルをアップロード
        $file->storeAs('spec', $fileName, 'public');

        $filePath = 'spec/' . $fileName;
        $fileInfo = [
            'filename'   => basename($filePath),
            'size'       => Storage::disk('public')->size($filePath),
            'updated_at' => date('Y-m-d H:i:s', Storage::disk('public')->lastModified($filePath)),
        ];

        return response()->json(['data' => $fileInfo], 200);
    }

    /**
     * ベンチマークファイル情報を返す。
     */
    public function getBenchmarkFile(): JsonResponse
    {
        $files = Storage::disk('public')->files('benchmark/');

        if (empty($files)) {
            return response()->json(['data' => null]);
        }

        $filePath = $files[0];
        $fileInfo = [
            'filename'   => basename($filePath),
            'size'       => Storage::disk('public')->size($filePath),
            'updated_at' => date('Y-m-d H:i:s', Storage::disk('public')->lastModified($filePath)),
        ];

        return response()->json(['data' => $fileInfo]);
    }

    /**
     * ベンチマークファイルをアップロードする。
     */
    public function uploadBenchmarkFile(UploadBenchmarkFileApiRequest $request): JsonResponse
    {
        $file = $request->file('benchmark_file');
        $fileName = $file->getClientOriginalName();

        if (! Storage::disk('public')->exists('benchmark')) {
            Storage::disk('public')->makeDirectory('benchmark');
        }

        // 既存ファイルを削除
        $oldFiles = Storage::disk('public')->files('benchmark/');
        Storage::disk('public')->delete($oldFiles);

        // 新規ファイルをアップロード
        $file->storeAs('benchmark', $fileName, 'public');

        $filePath = 'benchmark/' . $fileName;
        $fileInfo = [
            'filename'   => basename($filePath),
            'size'       => Storage::disk('public')->size($filePath),
            'updated_at' => date('Y-m-d H:i:s', Storage::disk('public')->lastModified($filePath)),
        ];

        return response()->json(['data' => $fileInfo], 200);
    }

    /**
     * 複数端末 CSV ファイルをアップロード・解析する。
     *
     * 旧 `confirmMulti` を API 化。CSV を解析・各行検証し、
     * プレビューデータを返す（保存はしない）。
     */
    public function uploadDeviceMulti(UploadDeviceMultiApiRequest $request): JsonResponse
    {
        try {
            $file = $request->file('device_register_file');
            $filePath = $file->getPathname();

            // CSV 解析
            $csv = Reader::createFromPath($filePath, 'r')->setHeaderOffset(0);
            $stmt = Statement::create();
            $records = $stmt->process($csv);

            $devices = [];
            foreach ($records as $record) {
                $record = GeneralUtil::sanitizeCsvRecord($record);

                // 行単位のバリデーション
                \Validator::make($record, [
                    'device_type'           => ['required', 'string', 'max:16', Rule::in(DeviceCategory::activeCodes())],
                    'device_name'           => ['required', 'string', 'max:255'],
                    'device_serial'         => ['required', 'string', 'max:32'],
                    'first_work_date_at'    => ['nullable', 'date'],
                    'purchase_date_at'      => ['nullable', 'date'],
                    'option'                => ['nullable', 'string'],
                    'defective'             => ['nullable', 'integer'],
                    'not_for_sale'          => ['nullable', 'integer'],
                    'note'                  => ['nullable', 'string'],
                ])->validate();

                // device_id を自動生成
                $samePrefix = collect($devices)->filter(function ($d) use ($record) {
                    return str_starts_with($d['device_id'], "{$record['device_type']}_{$record['device_name']}_");
                })->count();
                $baseId = Device::generateDeviceId($record['device_type'], $record['device_name']);
                if ($samePrefix > 0) {
                    $prefix = "{$record['device_type']}_{$record['device_name']}_";
                    $baseNum = (int) substr($baseId, strlen($prefix));
                    $record['device_id'] = $prefix . str_pad($baseNum + $samePrefix, 6, '0', STR_PAD_LEFT);
                } else {
                    $record['device_id'] = $baseId;
                }

                $devices[] = $record;
            }

            return response()->json(['data' => $devices], 200);
        } catch (\Exception $err) {
            Log::channel('error')->error('device.upload_multi.failed', [
                'action'         => 'device_csv_upload',
                'error_message'  => $err->getMessage(),
                'error_class'    => get_class($err),
            ]);

            return response()->json([
                'message' => 'CSV 解析に失敗しました。形式を確認してください。',
            ], 422);
        }
    }

    /**
     * 複数端末を一括保存する。
     *
     * 旧 `storeDeviceMulti` を API 化。アップロード時に検証済みの
     * デバイスデータの配列を受け取り、トランザクション内で一括保存。
     */
    public function storeDeviceMulti(StoreDeviceMultiApiRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $devices = $request->validated()['devices'];

            foreach ($devices as $device_data) {
                Device::create([
                    'device_id'             => $device_data['device_id'],
                    'device_type'           => $device_data['device_type'],
                    'device_name'           => $device_data['device_name'],
                    'device_serial'         => $device_data['device_serial'],
                    'first_work_date_at'    => $device_data['first_work_date_at'] ?? null,
                    'purchase_date_at'      => $device_data['purchase_date_at'] ?? null,
                    'option'                => $device_data['option'] ?? null,
                    'condition_id'          => $device_data['condition'] ?? null,
                    'defective'             => ! empty($device_data['defective']),
                    'not_for_sale'          => ! empty($device_data['not_for_sale']),
                    'note'                  => $device_data['note'] ?? null,
                ]);
            }

            DB::commit();

            return response()->json([
                'data' => [
                    'count'   => count($devices),
                    'message' => count($devices) . '台の端末を登録しました。',
                ],
            ], 201);
        } catch (\Exception $err) {
            DB::rollBack();
            Log::channel('error')->error('device.store_multi.failed', [
                'action'         => 'device_multi_registration',
                'error_message'  => $err->getMessage(),
                'error_class'    => get_class($err),
            ]);

            return response()->json([
                'message' => '端末の登録に失敗しました。',
            ], 500);
        }
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
