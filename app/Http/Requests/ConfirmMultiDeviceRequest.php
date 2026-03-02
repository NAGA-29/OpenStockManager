<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Redirect;

class ConfirmMultiDeviceRequest extends FormRequest
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
            'device_register_file' => ['required', 'file', 'mimes:csv,txt'],
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
            'device_register_file.required' => 'ファイルを選択してください。',
            'device_register_file.file'     => '有効なファイルをアップロードしてください。',
            'device_register_file.mimes'    => 'ファイル形式はcsvのみアップロード可能です。',
        ];
    }

    /**
     * @access protected
     * @param Validator $validator
     */
    protected function failedValidation(Validator $validator)
    {
        $response = Redirect::back()
            ->with('register_message', 'ファイルを選択してください')
            ->withInput()
            ->withErrors($validator->errors(), $this->errorBag);

        throw new HttpResponseException($response);
    }
}
