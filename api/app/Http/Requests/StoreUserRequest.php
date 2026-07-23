<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rules\Password;
use Ramsey\Uuid\Uuid;

class StoreUserRequest extends FormRequest
{
    protected $vendor_id;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        # ログインユーザーは全員通す
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'id'                    => ['required', 'string', 'max:255', 'unique:users,id'],
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => ['required', 'string', 'email', 'max:255', 'unique:users,email', ],
            'password'              => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers(), 'max:64'],
        ];
    }

    /**
     * エラーメッセージ
     * オーバーライド
     * @return string[]
     */
    public function messages()
    {
        return [
            'id.required'                  => 'IDが指定されていません',
            'id.string'                    => 'IDが不正です',
            'id.max'                       => 'IDは:max文字以内で指定してください',
            'id.unique'                    => 'IDが重複しています',
            'name.required'                => '名前が指定されていません',
            'name.string'                  => '名前が不正です',
            'name.max'                     => '名前は:max文字以内で指定してください',
            'email.required'               => 'メールアドレスが指定されていません',
            'email.string'                 => 'メールアドレスが不正です',
            'email.email'                  => 'メールアドレスが不正です',
            'email.max'                    => 'メールアドレスは:max文字以内で指定してください',
            'email.unique'                 => 'メールアドレスがすでに登録されています',
            'password.required'            => 'パスワードが指定されていません',
            'password.confirmed'           => 'パスワードが一致しません',
            'password.min'                 => 'パスワードは8文字以上で、大文字・小文字・数字を含めてください',
            'password.max'                 => 'パスワードは64文字以内で指定してください',
        ];
    }

    /**
     * バリデーション前に実行する処理
     *  - プロジェクトIDを生成する
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'id'    => (string) Uuid::uuid7(), // CHANGE: Add 2024-09-19
        ]);
    }

    /**
     * バリデーション後に実行する処理
     * Override
     * @access protected
     * @param Validator $validator
     */
    protected function failedValidation(Validator $validator)
    {
        $response = Redirect::back()
            ->with('error_message', '登録内容に誤りがあります')
            ->withInput()
            ->withErrors($validator->errors(), $this->errorBag);
        throw new HttpResponseException($response);
    }
}
