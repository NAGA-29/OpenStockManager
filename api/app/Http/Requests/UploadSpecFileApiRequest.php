<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * 端末スペックファイルアップロード（API）のバリデーション。
 *
 * 旧 `UploadSpecFileRequest` を踏襲しつつ、失敗時は JSON（422）で返す。
 */
class UploadSpecFileApiRequest extends FormRequest
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
            'spec_file' => ['required', 'file', 'mimes:xlsx,xls,csv,pdf', 'max:10240'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'spec_file.required' => 'ファイルを選択してください。',
            'spec_file.file'     => '有効なファイルをアップロードしてください。',
            'spec_file.mimes'    => 'ファイル形式はxlsx, xls, csv, pdfのみアップロード可能です。',
            'spec_file.max'      => 'ファイルサイズは10MB以内でアップロードしてください。',
        ];
    }
}
