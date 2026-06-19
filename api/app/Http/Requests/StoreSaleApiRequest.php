<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * 販売登録（API）のバリデーション。
 */
class StoreSaleApiRequest extends FormRequest
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
            'device_ids'          => ['required', 'array', 'min:1'],
            'device_ids.*'        => ['string', 'exists:devices,device_id'],
            'client_id'           => ['required', 'exists:clients,client_id'],
            'contact_id'          => ['required', 'exists:contacts,id'],
            'sale_date_at'        => ['required', 'date'],
            'note'                => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'device_ids.required'           => '端末を選択してください。',
            'device_ids.min'                => '端末を選択してください。',
            'device_ids.*.exists'           => '指定された端末が見つかりません。',
            'client_id.required'            => 'クライアントを選択してください。',
            'client_id.exists'              => '指定されたクライアントが見つかりません。',
            'contact_id.required'           => '担当者を選択してください。',
            'contact_id.exists'             => '指定された担当者が見つかりません。',
            'sale_date_at.required'         => '販売日を入力してください。',
            'sale_date_at.date'             => '販売日は有効な日付を入力してください。',
        ];
    }
}
