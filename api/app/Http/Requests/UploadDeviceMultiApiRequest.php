<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * 複数端末 CSV アップロード（API）のバリデーション。
 */
class UploadDeviceMultiApiRequest extends FormRequest
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
            'device_register_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'device_register_file.required' => 'ファイルを選択してください。',
            'device_register_file.file'     => '有効なファイルをアップロードしてください。',
            'device_register_file.mimes'    => 'ファイル形式はCSVのみアップロード可能です。',
            'device_register_file.max'      => 'ファイルサイズは5MB以内でアップロードしてください。',
        ];
    }
}
