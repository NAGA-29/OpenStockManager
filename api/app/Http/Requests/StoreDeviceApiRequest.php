<?php

namespace App\Http\Requests;

use App\Enums\DeviceEnum;
use App\Models\DeviceCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * 端末単体登録（API）のバリデーション。
 *
 * 旧 `StoreDeviceRequest` を踏襲しつつ、失敗時は Blade へのリダイレクトでなく
 * FormRequest 既定の `ValidationException`（API では 422 JSON）で返す。
 * defective / not_for_sale は API では真偽値で受ける。画像アップロードは未対応。
 */
class StoreDeviceApiRequest extends FormRequest
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
            'device_type'        => ['required', 'string', 'max:16', Rule::in(DeviceCategory::activeCodes())],
            'device_name'        => ['required', 'string', 'max:255'],
            'device_serial'      => ['required', 'string', 'max:255', Rule::unique('devices', 'device_serial')],
            'custom_fields'      => ['nullable', 'array'],
            'custom_fields.*'    => ['nullable'],
            'first_work_date_at' => ['nullable', 'date'],
            'purchase_date_at'   => ['nullable', 'date'],
            'client'             => ['nullable', 'string', 'max:32'],
            'condition'          => ['required', 'integer', Rule::in(array_keys(DeviceEnum::CONDITIONS))],
            'defective'          => ['nullable', 'boolean'],
            'not_for_sale'       => ['nullable', 'boolean'],
            'note'               => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'device_type.required'   => '端末種別を選択してください',
            'device_type.in'         => '端末種別が不正です',
            'device_name.required'   => '端末名を入力してください',
            'device_serial.required' => 'シリアル番号を入力してください',
            'device_serial.unique'   => 'このシリアル番号は既に登録されています',
            'first_work_date_at.date' => '初回稼働は日付を入力してください',
            'purchase_date_at.date'  => '購入日は日付を入力してください',
            'condition.required'     => 'コンディションを選択してください',
            'condition.integer'      => 'コンディション値が不正です',
        ];
    }
}
