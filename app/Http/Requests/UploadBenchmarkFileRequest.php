<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Redirect;

class UploadBenchmarkFileRequest extends FormRequest
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
            'benchmark_file' => ['required', 'file', 'mimes:xlsx,xls,csv,pdf', 'max:10240'],
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
            'benchmark_file.required' => 'ファイルを選択してください。',
            'benchmark_file.file'     => '有効なファイルをアップロードしてください。',
            'benchmark_file.mimes'    => 'ファイル形式はxlsx, xls, csv, pdfのみアップロード可能です。',
            'benchmark_file.max'      => 'ファイルサイズは10MB以内でアップロードしてください。',
        ];
    }

    /**
     * @access protected
     * @param Validator $validator
     */
    protected function failedValidation(Validator $validator)
    {
        $response = Redirect::back()
            ->with('error_message', 'アップロード内容に誤りがあります')
            ->withInput()
            ->withErrors($validator->errors(), $this->errorBag);

        throw new HttpResponseException($response);
    }
}
