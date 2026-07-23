<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * 販売 CSV ファイルアップロード（API）のバリデーション。
 */
class UploadSaleMultiApiRequest extends FormRequest
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
            'sale_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'sale_file.required' => 'CSVファイルを選択してください。',
            'sale_file.file'     => 'ファイルが破損しています。',
            'sale_file.mimes'    => 'CSVまたはテキストファイルをアップロードしてください。',
            'sale_file.max'      => 'ファイルサイズは5MB以下にしてください。',
        ];
    }
}
