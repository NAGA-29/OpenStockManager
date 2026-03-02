<?php

namespace App\Http\Requests;

use App\Enums\DeviceEnum;
use App\Models\DeviceCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDeviceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'device_id'             => ['required', 'exists:devices', 'max:255'],
            'device_type'           => ['required', 'string', 'max:16', Rule::in(DeviceCategory::activeCodes())],
            'device_name'           => ['required', 'max:255'],
            'device_serial'         => ['required', 'string', 'max:32'],
            'custom_fields'         => ['nullable', 'array'],
            'custom_fields.*'       => ['nullable'],
            'first_work_date_at'    => ['nullable', 'date'],
            'purchase_date_at'      => ['nullable', 'date'],
            'option'                => ['nullable', 'string'],
            'condition'             => ['integer', Rule::in(array_keys(DeviceEnum::CONDITIONS))],
            'defective'             => ['nullable', 'integer'],
            'not_for_sale'          => ['nullable', 'integer'],
            'note'                  => ['nullable', 'string', 'max:255'],
            'imageList'             => ['nullable', 'array'],
            'imageList.*'           => ['nullable', 'string'],
            'device_image'          => ['nullable', 'mimes:jpeg,png,jpg', 'max:3072'],
        ];
    }

    public function messages()
    {
        return [
            'device_id.required'        => '端末IDを入力してください。',
            'device_id.exists'          => '端末が存在しません。',
            'device_type.required'      => '端末種別を選択してください',
            'device_type.in'            => '端末種別を選択してください',
            'device_name.required'      => '端末名を入力してください。',
            'condition.integer'         => 'コンディション値が不正です',
            'condition.in'              => 'コンディション値が不正です',
        ];
    }
}
