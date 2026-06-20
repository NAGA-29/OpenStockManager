<?php

namespace App\Http\Requests;

use App\Models\DeviceTypeField;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * カスタムフィールド更新（API）のバリデーション。
 * カテゴリ・field_key は変更不可のため対象外。
 */
class UpdateDeviceFieldApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'label'           => ['required', 'string', 'max:128'],
            'field_type'      => ['required', Rule::in(array_keys(DeviceTypeField::FIELD_TYPES))],
            'options'         => ['nullable', 'array'],
            'options.*.label' => ['required_with:options', 'string', 'max:64'],
            'options.*.value' => ['required_with:options', 'string', 'max:64'],
            'is_required'     => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'label.required'                => 'ラベルを入力してください。',
            'label.max'                     => 'ラベルは128文字以内で入力してください。',
            'field_type.required'           => 'フィールド種別を選択してください。',
            'field_type.in'                 => 'フィールド種別の指定が不正です。',
            'options.*.label.required_with' => '選択肢のラベルを入力してください。',
            'options.*.value.required_with' => '選択肢の値を入力してください。',
        ];
    }
}
