<?php

namespace App\Http\Requests;

use App\Traits\ChecksRentableDevices;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * 一括レンタル保存（API）のバリデーション。
 */
class StoreRentalMultiApiRequest extends FormRequest
{
    use ChecksRentableDevices;

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
            'checkout_at'         => ['required', 'date'],
            'schedule_return_at'  => ['required', 'date'],
            'rentals'             => ['required', 'array', 'min:1'],
            'rentals.*.device_id' => ['required', 'string', 'exists:devices,device_id'],
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
            'checkout_at.required'         => '貸出日を入力してください。',
            'checkout_at.date'             => '貸出日は有効な日付を入力してください。',
            'schedule_return_at.required'  => '返却予定日を入力してください。',
            'schedule_return_at.date'      => '返却予定日は有効な日付を入力してください。',
            'rentals.required'             => 'レンタル端末を指定してください。',
            'rentals.min'                  => '最低1つの端末を指定してください。',
            'rentals.*.device_id.required' => '端末IDが未指定です。',
            'rentals.*.device_id.exists'   => '指定された端末が見つかりません。',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $deviceIds = collect($this->input('rentals', []))
                ->pluck('device_id')
                ->filter()
                ->values()
                ->all();
            $this->validateRentableDevices($validator, $deviceIds, 'rentals');
        });
    }
}
