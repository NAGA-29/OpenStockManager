<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * ベンチマークファイルアップロード（API）のバリデーション。
 *
 * 旧 `UploadBenchmarkFileRequest` を踏襲しつつ、失敗時は JSON（422）で返す。
 */
class UploadBenchmarkFileApiRequest extends FormRequest
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
            'benchmark_file' => ['required', 'file', 'mimes:xlsx,xls,csv,pdf', 'max:10240'],
        ];
    }

    /**
     * @return array<string, string>
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
}
