<?php

namespace App\Http\Controllers;

use App\Models\DeviceCategory;
use App\Models\DeviceTypeField;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DeviceTypeFieldController extends Controller
{
    /**
     * 管理画面：カスタムフィールド一覧
     */
    public function index()
    {
        $categories = DeviceCategory::ordered()->with('fields')->get();

        return view('device_fields.index', compact('categories'));
    }

    /**
     * カスタムフィールド作成
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'device_category_code' => ['required', 'string', 'exists:device_categories,code'],
            'label'                => ['required', 'string', 'max:128'],
            'field_type'           => ['required', Rule::in(array_keys(DeviceTypeField::FIELD_TYPES))],
            'options'              => ['nullable', 'array'],
            'options.*.label'      => ['required_with:options', 'string', 'max:64'],
            'options.*.value'      => ['required_with:options', 'string', 'max:64'],
            'is_required'          => ['boolean'],
        ]);

        $categoryCode = $validated['device_category_code'];
        $fieldKey = DeviceTypeField::generateFieldKey($validated['label'], $categoryCode);

        $maxOrder = DeviceTypeField::where('device_category_code', $categoryCode)->max('sort_order') ?? -1;

        DeviceTypeField::create([
            'device_category_code' => $categoryCode,
            'field_key'            => $fieldKey,
            'label'                => $validated['label'],
            'field_type'           => $validated['field_type'],
            'options'              => ($validated['field_type'] === 'select') ? ($validated['options'] ?? []) : null,
            'is_required'          => $validated['is_required'] ?? false,
            'sort_order'           => $maxOrder + 1,
        ]);

        return redirect()->route('device_fields.index')
                         ->with('success', 'フィールドを追加しました');
    }

    /**
     * カスタムフィールド更新
     */
    public function update(Request $request, int $id)
    {
        $field = DeviceTypeField::findOrFail($id);

        $validated = $request->validate([
            'label'           => ['required', 'string', 'max:128'],
            'field_type'      => ['required', Rule::in(array_keys(DeviceTypeField::FIELD_TYPES))],
            'options'         => ['nullable', 'array'],
            'options.*.label' => ['required_with:options', 'string', 'max:64'],
            'options.*.value' => ['required_with:options', 'string', 'max:64'],
            'is_required'     => ['boolean'],
        ]);

        $field->update([
            'label'       => $validated['label'],
            'field_type'  => $validated['field_type'],
            'options'     => ($validated['field_type'] === 'select') ? ($validated['options'] ?? []) : null,
            'is_required' => $validated['is_required'] ?? false,
        ]);

        return redirect()->route('device_fields.index')
                         ->with('success', 'フィールドを更新しました');
    }

    /**
     * カスタムフィールド削除（DBのfieldレコードのみ削除。デバイスのJSONデータはそのまま）
     */
    public function destroy(int $id)
    {
        DeviceTypeField::findOrFail($id)->delete();

        return redirect()->route('device_fields.index')
                         ->with('success', 'フィールドを削除しました');
    }

    /**
     * カスタムフィールドの並び替え（AJAX）
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'ids'   => ['required', 'array'],
            'ids.*' => ['integer'],
        ]);

        foreach ($request->ids as $order => $id) {
            DeviceTypeField::where('id', $id)->update(['sort_order' => $order]);
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * デバイス種別のカスタムフィールド定義を取得（AJAX）
     */
    public function getByCategory(string $code)
    {
        $category = DeviceCategory::where('code', $code)->firstOrFail();
        $fields   = $category->fields;

        return response()->json($fields);
    }
}
