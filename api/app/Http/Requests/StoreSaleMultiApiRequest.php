<?php

namespace App\Http\Requests;

use App\Traits\ChecksSaleableDevices;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * 一括販売保存（API）のバリデーション。
 */
class StoreSaleMultiApiRequest extends FormRequest
{
    use ChecksSaleableDevices;

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
            'client_id'           => ['required', 'exists:clients,client_id'],
            'contact_id'          => ['required', 'exists:contacts,id'],
            'sale_date_at'        => ['required', 'date'],
            'sales'               => ['required', 'array', 'min:1'],
            'sales.*.device_id'   => ['required', 'string', 'exists:devices,device_id'],
            'note'                => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'client_id.required'           => 'クライアントを選択してください。',
            'client_id.exists'             => '指定されたクライアントが見つかりません。',
            'contact_id.required'          => '担当者を選択してください。',
            'contact_id.exists'            => '指定された担当者が見つかりません。',
            'sale_date_at.required'        => '販売日を入力してください。',
            'sale_date_at.date'            => '販売日は有効な日付を入力してください。',
            'sales.required'               => '販売端末を指定してください。',
            'sales.min'                    => '最低1つの端末を指定してください。',
            'sales.*.device_id.required'   => '端末IDが未指定です。',
            'sales.*.device_id.exists'     => '指定された端末が見つかりません。',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $deviceIds = collect($this->input('sales', []))
                ->pluck('device_id')
                ->filter()
                ->values()
                ->all();
            $this->validateSaleableDevices($validator, $deviceIds, 'sales');
        });
    }
}
