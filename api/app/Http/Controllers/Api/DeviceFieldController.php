<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDeviceFieldApiRequest;
use App\Http\Requests\UpdateDeviceFieldApiRequest;
use App\Models\DeviceTypeField;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * カスタムフィールド管理 API（admin ミドルウェアで保護）。
 */
class DeviceFieldController extends Controller
{
    /**
     * フィールド一覧。`category` でカテゴリコード絞り込み可。
     */
    public function index(Request $request): JsonResponse
    {
        $query = DeviceTypeField::query()
            ->orderBy('device_category_code')
            ->orderBy('sort_order')
            ->orderBy('id');

        if ($request->filled('category')) {
            $query->where('device_category_code', $request->query('category'));
        }

        $fields = $query->get()->map(fn (DeviceTypeField $field) => $this->resource($field));

        return response()->json([
            'data' => $fields,
            'field_types' => DeviceTypeField::FIELD_TYPES,
        ]);
    }

    /**
     * フィールド登録。field_key はラベルから自動採番、sort_order はカテゴリ内 max+1。
     */
    public function store(StoreDeviceFieldApiRequest $request): JsonResponse
    {
        try {
            $safe = $request->safe()->all();
            $categoryCode = $safe['device_category_code'];
            $fieldType = $safe['field_type'];

            $fieldKey = DeviceTypeField::generateFieldKey($safe['label'], $categoryCode);
            $maxOrder = DeviceTypeField::where('device_category_code', $categoryCode)->max('sort_order') ?? -1;

            $field = DeviceTypeField::create([
                'device_category_code' => $categoryCode,
                'field_key'            => $fieldKey,
                'label'                => $safe['label'],
                'field_type'           => $fieldType,
                'options'              => $fieldType === 'select' ? ($safe['options'] ?? []) : null,
                'is_required'          => $safe['is_required'] ?? false,
                'sort_order'           => $maxOrder + 1,
            ]);

            return response()->json([
                'data' => $this->resource($field),
                'message' => 'フィールドを追加しました。',
            ], 201);
        } catch (\Exception $err) {
            Log::channel('error')->error('device_field.store.failed', [
                'error_message' => $err->getMessage(),
            ]);

            return response()->json([
                'message' => 'フィールドの追加に失敗しました。',
                'error' => $err->getMessage(),
            ], 500);
        }
    }

    /**
     * フィールド更新。カテゴリ・field_key は変更しない。
     */
    public function update(UpdateDeviceFieldApiRequest $request, int $id): JsonResponse
    {
        $field = DeviceTypeField::find($id);

        if (!$field) {
            return response()->json(['message' => '指定されたフィールドが見つかりません。'], 404);
        }

        try {
            $safe = $request->safe()->all();
            $fieldType = $safe['field_type'];

            $field->update([
                'label'       => $safe['label'],
                'field_type'  => $fieldType,
                'options'     => $fieldType === 'select' ? ($safe['options'] ?? []) : null,
                'is_required' => $safe['is_required'] ?? false,
            ]);

            return response()->json([
                'data' => $this->resource($field->fresh()),
                'message' => 'フィールドを更新しました。',
            ]);
        } catch (\Exception $err) {
            Log::channel('error')->error('device_field.update.failed', [
                'id' => $id,
                'error_message' => $err->getMessage(),
            ]);

            return response()->json([
                'message' => 'フィールドの更新に失敗しました。',
                'error' => $err->getMessage(),
            ], 500);
        }
    }

    /**
     * フィールド削除（端末側の JSON 値はそのまま残す）。
     */
    public function destroy(int $id): JsonResponse
    {
        $field = DeviceTypeField::find($id);

        if (!$field) {
            return response()->json(['message' => '指定されたフィールドが見つかりません。'], 404);
        }

        try {
            $field->delete();

            return response()->json(['message' => 'フィールドを削除しました。']);
        } catch (\Exception $err) {
            Log::channel('error')->error('device_field.destroy.failed', [
                'id' => $id,
                'error_message' => $err->getMessage(),
            ]);

            return response()->json([
                'message' => 'フィールドの削除に失敗しました。',
                'error' => $err->getMessage(),
            ], 500);
        }
    }

    /**
     * 並び替え。`order` に id を希望順で並べて渡す（同一カテゴリ内での利用を想定）。
     */
    public function reorder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['required', 'integer', 'exists:device_type_fields,id'],
        ]);

        try {
            DB::beginTransaction();
            foreach ($validated['order'] as $index => $id) {
                DeviceTypeField::where('id', $id)->update(['sort_order' => $index + 1]);
            }
            DB::commit();

            return response()->json(['message' => '並び順を更新しました。']);
        } catch (\Exception $err) {
            DB::rollBack();
            Log::channel('error')->error('device_field.reorder.failed', [
                'error_message' => $err->getMessage(),
            ]);

            return response()->json([
                'message' => '並び替えに失敗しました。',
                'error' => $err->getMessage(),
            ], 500);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function resource(DeviceTypeField $field): array
    {
        return [
            'id' => $field->id,
            'device_category_code' => $field->device_category_code,
            'field_key' => $field->field_key,
            'label' => $field->label,
            'field_type' => $field->field_type,
            'options' => $field->options,
            'is_required' => $field->is_required,
            'sort_order' => $field->sort_order,
        ];
    }
}
