<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Redirect;

class UploadSaleFileRequest extends FormRequest
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
            'client_id'    => ['required', 'string', 'exists:clients,client_id'],
            'contact'    => ['required', 'string', 'exists:contacts,id'],
            'sale_date_at' => ['required', 'date'],
            'note'         => ['nullable', 'string'],
            'csv_file'     => ['required', 'file', 'mimes:csv,txt'],
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
            'client_id.required'    => 'クライアントを選択してください。',
            'client_id.exists'      => '存在しないクライアントです。',
            'contact.required'    => '担当者を選択してください。',
            'contact.exists'      => '存在しない担当者です。',
            'sale_date_at.required' => '販売日を入力してください。',
            'sale_date_at.date'     => '販売日は日付を入力してください。',
            'csv_file.required'     => 'CSVファイルを選択してください。',
            'csv_file.file'         => '有効なファイルをアップロードしてください。',
            'csv_file.mimes'        => 'ファイル形式はcsvのみアップロード可能です。',
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
