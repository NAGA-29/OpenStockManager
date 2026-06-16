<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * @return array
     */
    public function rules()
    {
        $userId = $this->input('id');

        return [
            'id'       => ['required', 'string', 'exists:users,id'],
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'role'     => ['required', 'string', Rule::in(['admin', 'user'])],
            'password' => ['nullable', 'confirmed', Password::min(8)->mixedCase()->numbers(), 'max:64'],
        ];
    }

    /**
     * @return string[]
     */
    public function messages()
    {
        return [
            'id.required'           => 'ユーザーIDが指定されていません',
            'id.exists'             => '指定されたユーザーが存在しません',
            'name.required'         => '名前が指定されていません',
            'name.string'           => '名前が不正です',
            'name.max'              => '名前は:max文字以内で指定してください',
            'email.required'        => 'メールアドレスが指定されていません',
            'email.string'          => 'メールアドレスが不正です',
            'email.email'           => 'メールアドレスが不正です',
            'email.max'             => 'メールアドレスは:max文字以内で指定してください',
            'email.unique'          => 'メールアドレスがすでに登録されています',
            'role.required'         => '権限が指定されていません',
            'role.in'               => '権限が不正です',
            'password.confirmed'    => 'パスワードが一致しません',
            'password.min'          => 'パスワードは8文字以上で、大文字・小文字・数字を含めてください',
            'password.max'          => 'パスワードは64文字以内で指定してください',
        ];
    }

    /**
     * @param Validator $validator
     */
    protected function failedValidation(Validator $validator)
    {
        $response = Redirect::back()
            ->with('error_message', '入力内容に誤りがあります')
            ->withInput()
            ->withErrors($validator->errors(), $this->errorBag);
        throw new HttpResponseException($response);
    }
}
