<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Redirect;

class StorecontactRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'client_id' => ['required', 'alpha_num'],
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email'],
            'tel'       => ['required', 'numeric', 'digits_between:8,11'],
            'note'      => ['nullable', 'string'],
        ];
    }

    /**
     * エラーメッセージ
     *
     * @return string[]
     */
    public function messages(): array
    {
        return [
            'client_id.required' => 'クライアントを選択してください。',
            'client_id.alpha_num' => 'クライアントIDが不正です。',
            'name.required'      => '担当者名を入力してください。',
            'name.max'           => '担当者名は255文字以内で入力してください。',
            'email.required'     => 'メールアドレスを入力してください。',
            'email.email'        => '有効なメールアドレスを入力してください。',
            'tel.required'       => '電話番号を入力してください。',
            'tel.numeric'        => '電話番号は数値で入力してください。',
            'tel.digits_between' => '電話番号は8〜11桁で入力してください。',
        ];
    }

    /**
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
