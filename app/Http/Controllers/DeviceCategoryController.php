<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\DeviceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class DeviceCategoryController extends Controller
{
    /**
     * 機材カテゴリ管理ページ表示
     */
    public function index()
    {
        $categories = DeviceCategory::ordered()->get();

        // 各カテゴリのデバイス数を取得
        $deviceCounts = Device::select('device_type', DB::raw('count(*) as count'))
            ->groupBy('device_type')
            ->pluck('count', 'device_type');

        return view('device_categories.index', compact('categories', 'deviceCounts'));
    }

    /**
     * 新規カテゴリ追加
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:16', 'unique:device_categories,code', 'regex:/^[A-Z0-9_]+$/'],
            'name' => ['required', 'string', 'max:64'],
            'icon' => ['nullable', 'string', 'max:64'],
        ], [
            'code.required' => 'カテゴリコードを入力してください',
            'code.unique' => 'このコードは既に使用されています',
            'code.regex' => 'コードは半角英大文字・数字・アンダースコアのみ使用できます',
            'code.max' => 'コードは16文字以内で入力してください',
            'name.required' => 'カテゴリ名を入力してください',
            'name.max' => 'カテゴリ名は64文字以内で入力してください',
        ]);

        try {
            $maxOrder = DeviceCategory::max('sort_order') ?? 0;
            DeviceCategory::create([
                'code' => $validated['code'],
                'name' => $validated['name'],
                'icon' => $validated['icon'] ?: 'fa-cube',
                'sort_order' => $maxOrder + 1,
                'is_active' => true,
            ]);

            return redirect()
                ->route('device_categories.index')
                ->with('success_message', 'カテゴリを追加しました');
        } catch (\Exception $err) {
            Log::channel('error')->error('device_category.store.failed', [
                'error_message' => $err->getMessage(),
            ]);
            return redirect()
                ->back()
                ->withInput()
                ->with('error_message', 'カテゴリの追加に失敗しました');
        }
    }

    /**
     * カテゴリ更新
     */
    public function update(Request $request, int $id)
    {
        $category = DeviceCategory::findOrFail($id);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:16', Rule::unique('device_categories', 'code')->ignore($id), 'regex:/^[A-Z0-9_]+$/'],
            'name' => ['required', 'string', 'max:64'],
            'icon' => ['nullable', 'string', 'max:64'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'code.required' => 'カテゴリコードを入力してください',
            'code.unique' => 'このコードは既に使用されています',
            'code.regex' => 'コードは半角英大文字・数字・アンダースコアのみ使用できます',
            'name.required' => 'カテゴリ名を入力してください',
        ]);

        try {
            DB::beginTransaction();

            $oldCode = $category->code;
            $newCode = $validated['code'];

            // コード変更時はdevicesテーブルも更新
            if ($oldCode !== $newCode) {
                Device::where('device_type', $oldCode)->update(['device_type' => $newCode]);
            }

            $category->update([
                'code' => $newCode,
                'name' => $validated['name'],
                'icon' => $validated['icon'] ?: 'fa-cube',
                'is_active' => $request->has('is_active'),
            ]);

            DB::commit();

            return redirect()
                ->route('device_categories.index')
                ->with('success_message', 'カテゴリを更新しました');
        } catch (\Exception $err) {
            DB::rollBack();
            Log::channel('error')->error('device_category.update.failed', [
                'id' => $id,
                'error_message' => $err->getMessage(),
            ]);
            return redirect()
                ->back()
                ->with('error_message', 'カテゴリの更新に失敗しました');
        }
    }

    /**
     * カテゴリ削除
     */
    public function destroy(int $id)
    {
        $category = DeviceCategory::findOrFail($id);

        // このカテゴリに属するデバイスがある場合は削除を拒否
        $deviceCount = Device::where('device_type', $category->code)->count();
        if ($deviceCount > 0) {
            return redirect()
                ->route('device_categories.index')
                ->with('error_message', "「{$category->name}」には{$deviceCount}台の機材が登録されているため削除できません。先に機材を移動してください。");
        }

        try {
            $category->delete();
            return redirect()
                ->route('device_categories.index')
                ->with('success_message', 'カテゴリを削除しました');
        } catch (\Exception $err) {
            Log::channel('error')->error('device_category.destroy.failed', [
                'id' => $id,
                'error_message' => $err->getMessage(),
            ]);
            return redirect()
                ->route('device_categories.index')
                ->with('error_message', 'カテゴリの削除に失敗しました');
        }
    }

    /**
     * カテゴリ並び替え（Ajax）
     */
    public function reorder(Request $request)
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

            return response()->json(['success' => true]);
        } catch (\Exception $err) {
            DB::rollBack();
            Log::channel('error')->error('device_category.reorder.failed', [
                'error_message' => $err->getMessage(),
            ]);
            return response()->json(['success' => false, 'message' => '並び替えに失敗しました'], 500);
        }
    }
}
