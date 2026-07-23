<?php

namespace App\Http\Requests;

use App\Enums\DeviceEnum;
use App\Models\DeviceCategory;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;

class StoreDeviceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'device_type'           => ['required', 'string', 'max:16', Rule::in(DeviceCategory::activeCodes())],
            'device_name'           => ['required', 'max:255'],
            'device_serial'         => ['required', 'string', 'max:255'],
            'custom_fields'         => ['nullable', 'array'],
            'custom_fields.*'       => ['nullable'],
            'first_work_date_at'    => ['nullable', 'date'],
            'purchase_date_at'      => ['nullable', 'date'],
            'client'                => ['nullable', 'string', 'max:32'],
            'condition'             => ['integer', Rule::in(array_keys(DeviceEnum::CONDITIONS))],
            'defective'             => ['nullable', 'between:0,1'],
            'not_for_sale'          => ['nullable', 'between:0,1'],
            'note'                  => ['nullable', 'string', 'max:255'],
            'device_image'          => ['nullable', 'mimes:jpeg,png,jpg', 'max:3072'],
        ];
    }

    public function messages()
    {
        return [
            'device_type.required'      => '端末種別を選択してください',
            'device_type.in'            => '端末種別が不正です',
            'device_name.required'      => '端末名を入力してください',
            'device_serial.required'    => 'シリアル番号を入力してください',
            'first_work_date_at.date'   => '初回稼働は日付を入力してください',
            'purchase_date_at.date'     => '購入日は日付を入力してください',
            'condition.integer'         => 'コンディション値が不正です',
            'defective.between'         => '不良チェックが不正です',
            'not_for_sale.between'      => '販売不可チェックが不正です',
            'device_image.mimes'        => '画像はjpeg, png, jpg形式でアップロードしてください',
            'device_image.max'          => '画像サイズは3MB以内でアップロードしてください',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $response = Redirect::back()
            ->with('error_message', '登録内容に誤りがあります')
            ->withInput()
            ->withErrors($validator->errors(), $this->errorBag);

        throw new HttpResponseException($response);
    }
}
