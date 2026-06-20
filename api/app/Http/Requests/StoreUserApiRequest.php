<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * ユーザー登録（API）のバリデーション。
 */
class StoreUserApiRequest extends FormRequest
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
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers(), 'max:64'],
            'role'     => ['required', 'string', Rule::in(['admin', 'user'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required'      => '名前を入力してください。',
            'email.required'     => 'メールアドレスを入力してください。',
            'email.email'        => 'メールアドレスの形式が正しくありません。',
            'email.unique'       => 'このメールアドレスは既に登録されています。',
            'password.required'  => 'パスワードを入力してください。',
            'password.confirmed' => 'パスワードが一致しません。',
            'password.min'       => 'パスワードは8文字以上で、大文字・小文字・数字を含めてください。',
            'password.max'       => 'パスワードは64文字以内で指定してください。',
            'role.required'      => '権限を選択してください。',
            'role.in'            => '権限の指定が不正です。',
        ];
    }
}
