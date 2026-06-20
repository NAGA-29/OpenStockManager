<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDeviceCategoryApiRequest;
use App\Http\Requests\UpdateDeviceCategoryApiRequest;
use App\Models\Device;
use App\Models\DeviceCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * 機材カテゴリ管理 API（admin ミドルウェアで保護）。
 */
class DeviceCategoryController extends Controller
{
    /**
     * カテゴリ一覧（並び順・各カテゴリの機材数つき）。
     */
    public function index(): JsonResponse
    {
        $categories = DeviceCategory::ordered()->get();

        $deviceCounts = Device::select('device_type', DB::raw('count(*) as count'))
            ->groupBy('device_type')
            ->pluck('count', 'device_type');

        $data = $categories->map(fn (DeviceCategory $category) => $this->resource(
            $category,
            (int) ($deviceCounts[$category->code] ?? 0),
        ));

        return response()->json(['data' => $data]);
    }

    /**
     * カテゴリ登録。
     */
    public function store(StoreDeviceCategoryApiRequest $request): JsonResponse
    {
        try {
            $safe = $request->safe()->all();
            $maxOrder = DeviceCategory::max('sort_order') ?? 0;

            $category = DeviceCategory::create([
                'code' => $safe['code'],
                'name' => $safe['name'],
                'icon' => ($safe['icon'] ?? null) ?: 'fa-cube',
                'sort_order' => $maxOrder + 1,
                'is_active' => true,
            ]);

            return response()->json([
                'data' => $this->resource($category, 0),
                'message' => 'カテゴリを追加しました。',
            ], 201);
        } catch (\Exception $err) {
            Log::channel('error')->error('device_category.store.failed', [
                'error_message' => $err->getMessage(),
            ]);

            return response()->json([
                'message' => 'カテゴリの追加に失敗しました。',
                'error' => $err->getMessage(),
            ], 500);
        }
    }

    /**
     * カテゴリ更新。コード変更時は devices.device_type も追従更新する。
     */
    public function update(UpdateDeviceCategoryApiRequest $request, int $id): JsonResponse
    {
        $category = DeviceCategory::find($id);

        if (!$category) {
            return response()->json(['message' => '指定されたカテゴリが見つかりません。'], 404);
        }

        try {
            DB::beginTransaction();

            $safe = $request->safe()->all();
            $oldCode = $category->code;
            $newCode = $safe['code'];

            if ($oldCode !== $newCode) {
                Device::where('device_type', $oldCode)->update(['device_type' => $newCode]);
            }

            $category->update([
                'code' => $newCode,
                'name' => $safe['name'],
                'icon' => ($safe['icon'] ?? null) ?: 'fa-cube',
                'is_active' => $request->boolean('is_active'),
            ]);

            DB::commit();

            $count = Device::where('device_type', $newCode)->count();

            return response()->json([
                'data' => $this->resource($category->fresh(), $count),
                'message' => 'カテゴリを更新しました。',
            ]);
        } catch (\Exception $err) {
            DB::rollBack();
            Log::channel('error')->error('device_category.update.failed', [
                'id' => $id,
                'error_message' => $err->getMessage(),
            ]);

            return response()->json([
                'message' => 'カテゴリの更新に失敗しました。',
                'error' => $err->getMessage(),
            ], 500);
        }
    }

    /**
     * カテゴリ削除。機材が紐づく場合は拒否（422）。
     */
    public function destroy(int $id): JsonResponse
    {
        $category = DeviceCategory::find($id);

        if (!$category) {
            return response()->json(['message' => '指定されたカテゴリが見つかりません。'], 404);
        }

        $deviceCount = Device::where('device_type', $category->code)->count();
        if ($deviceCount > 0) {
            return response()->json([
                'message' => "「{$category->name}」には{$deviceCount}台の機材が登録されているため削除できません。先に機材を移動してください。",
            ], 422);
        }

        try {
            $category->delete();

            return response()->json(['message' => 'カテゴリを削除しました。']);
        } catch (\Exception $err) {
            Log::channel('error')->error('device_category.destroy.failed', [
                'id' => $id,
                'error_message' => $err->getMessage(),
            ]);

            return response()->json([
                'message' => 'カテゴリの削除に失敗しました。',
                'error' => $err->getMessage(),
            ], 500);
        }
    }

    /**
     * 並び替え。`order` に id を希望順で並べて渡す。
     */
    public function reorder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['required', 'integer', 'exists:device_categories,id'],
        ]);

        try {
            DB::beginTransaction();
            foreach ($validated['order'] as $index => $id) {
                DeviceCategory::where('id', $id)->update(['sort_order' => $index + 1]);
            }
            DB::commit();

            return response()->json(['message' => '並び順を更新しました。']);
        } catch (\Exception $err) {
            DB::rollBack();
            Log::channel('error')->error('device_category.reorder.failed', [
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
    private function resource(DeviceCategory $category, int $deviceCount): array
    {
        return [
            'id' => $category->id,
            'code' => $category->code,
            'name' => $category->name,
            'icon' => $category->icon,
            'sort_order' => $category->sort_order,
            'is_active' => $category->is_active,
            'device_count' => $deviceCount,
        ];
    }
}
