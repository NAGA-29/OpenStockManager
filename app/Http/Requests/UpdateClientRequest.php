<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Redirect;

class UpdateClientRequest extends FormRequest
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
            'client_id'      => ['required', 'string'],
            'company'        => ['required', 'max:255'],
            'url'            => ['required', 'url'],
            'tel'            => ['required', 'numeric', 'digits_between:8,11'],
            'street_address' => ['required', 'max:255'],
            'note'           => ['nullable', 'string'],
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
            'client_id.required'      => 'クライアントIDが不正です。',
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
