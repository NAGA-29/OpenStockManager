<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * 機材カテゴリ登録（API）のバリデーション。
 */
class StoreDeviceCategoryApiRequest extends FormRequest
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
            'code' => ['required', 'string', 'max:16', 'unique:device_categories,code', 'regex:/^[A-Z0-9_]+$/'],
            'name' => ['required', 'string', 'max:64'],
            'icon' => ['nullable', 'string', 'max:64'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.required' => 'カテゴリコードを入力してください。',
            'code.unique'   => 'このコードは既に使用されています。',
            'code.regex'    => 'コードは半角英大文字・数字・アンダースコアのみ使用できます。',
            'code.max'      => 'コードは16文字以内で入力してください。',
            'name.required' => 'カテゴリ名を入力してください。',
            'name.max'      => 'カテゴリ名は64文字以内で入力してください。',
        ];
    }
}
