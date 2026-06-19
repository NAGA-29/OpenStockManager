<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * 担当者登録（API）のバリデーション。
 *
 * 旧 `StoreContactRequest` を踏襲しつつ、失敗時は Blade へのリダイレクトでなく
 * FormRequest 既定の `ValidationException`（API では 422 JSON）で返す。
 */
class StoreContactApiRequest extends FormRequest
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
            'client_id' => ['required', 'exists:clients,client_id'],
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email'],
            'tel'       => ['required', 'numeric', 'digits_between:8,11'],
            'note'      => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'client_id.required' => 'クライアントを選択してください。',
            'client_id.exists'   => '指定されたクライアントが見つかりません。',
            'name.required'      => '担当者名を入力してください。',
            'name.max'           => '担当者名は255文字以内で入力してください。',
            'email.required'     => 'メールアドレスを入力してください。',
            'email.email'        => '有効なメールアドレスを入力してください。',
            'tel.required'       => '電話番号を入力してください。',
            'tel.numeric'        => '電話番号は数値で入力してください。',
            'tel.digits_between' => '電話番号は8〜11桁で入力してください。',
        ];
    }
}
