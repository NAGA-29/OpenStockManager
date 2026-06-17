<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * クライアント企業登録（API）のバリデーション。
 *
 * 旧 `StoreClientRequest` を踏襲しつつ、失敗時は Blade へのリダイレクトでなく
 * FormRequest 既定の `ValidationException`（API では 422 JSON）で返す。
 */
class StoreClientApiRequest extends FormRequest
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
            'company'        => ['required', 'max:255'],
            'url'            => ['required', 'url'],
            'tel'            => ['required', 'numeric', 'digits_between:8,11'],
            'street_address' => ['required', 'max:255'],
            'note'           => ['nullable'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'company.required'        => '会社名を入力してください。',
            'company.max'             => '会社名は255文字以内で入力してください。',
            'url.required'            => 'URLを入力してください。',
            'url.url'                 => '有効なURLを入力してください。',
            'tel.required'            => '電話番号を入力してください。',
            'tel.numeric'             => '電話番号は数値で入力してください。',
            'tel.digits_between'      => '電話番号は8〜11桁で入力してください。',
            'street_address.required' => '住所を入力してください。',
            'street_address.max'      => '住所は255文字以内で入力してください。',
        ];
    }
}
