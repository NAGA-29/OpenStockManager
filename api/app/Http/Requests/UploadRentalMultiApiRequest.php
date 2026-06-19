<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * レンタル CSV ファイルアップロード（API）のバリデーション。
 */
class UploadRentalMultiApiRequest extends FormRequest
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
            'rental_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'rental_file.required' => 'CSVファイルを選択してください。',
            'rental_file.file'     => 'ファイルが破損しています。',
            'rental_file.mimes'    => 'CSVまたはテキストファイルをアップロードしてください。',
            'rental_file.max'      => 'ファイルサイズは5MB以下にしてください。',
        ];
    }
}
