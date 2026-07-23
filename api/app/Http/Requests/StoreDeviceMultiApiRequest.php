<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * 複数端末一括保存（API）のバリデーション。
 *
 * アップロード時に検証済みのデバイスデータの配列を受け取る。
 */
class StoreDeviceMultiApiRequest extends FormRequest
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
            'devices'            => ['required', 'array', 'min:1'],
            'devices.*.device_type'        => ['required', 'string'],
            'devices.*.device_name'        => ['required', 'string'],
            'devices.*.device_serial'      => ['required', 'string'],
            'devices.*.device_id'          => ['required', 'string'],
            'devices.*.first_work_date_at' => ['nullable', 'date'],
            'devices.*.purchase_date_at'   => ['nullable', 'date'],
            'devices.*.option'             => ['nullable', 'string'],
            'devices.*.condition'          => ['nullable', 'integer'],
            'devices.*.defective'          => ['nullable', 'boolean'],
            'devices.*.not_for_sale'       => ['nullable', 'boolean'],
            'devices.*.note'               => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'devices.required' => '登録するデバイスがありません。',
            'devices.min'      => '最低1つ以上のデバイスを登録してください。',
        ];
    }
}
