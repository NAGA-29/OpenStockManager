<?php

namespace App\Http\Requests;

use App\Models\Device;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Redirect;
// Model
use Ramsey\Uuid\Uuid;

class StoreRentalCartRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        # ログインユーザーは全員通す
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'lend_id'               => ['required', 'string', 'max:255', 'unique:rental_hists,lend_id'],
            'deviceIds[]'           => ['array'],
            'deviceIds.*'           => ['required', 'string', 'max:255', 'exists:App\Models\Device,device_id', 'distinct'], // distinct->重複を検証
            'client_id'             => ['required', 'string', 'max:255', 'exists:App\Models\Client,client_id'],
            'contact'               => ['required', 'string', 'max:255', 'exists:App\Models\Contacts,contact_id'],
            'checkout_at'           => ['required', 'date', 'before_or_equal:schedule_return_at'],
            'schedule_return_at'    => ['required', 'date'],
            'note'                  => ['nullable', 'string', 'max:500',],
        ];
    }

    /**
     * エラーメッセージ
     * オーバーライド
     * @return string[]
     */
    public function messages()
    {
        return [
            'lend_id.required'                  => 'IDが指定されていません',
            'lend_id.string'                    => 'IDが不正です',
            'lend_id.max'                       => 'IDは:max文字以内で指定してください',
            'lend_id.unique'                    => 'IDが重複しています',
            'deviceIds[].array'                 => 'デバイスIDが不正です',
            'deviceIds.*.string'                => 'デバイスIDが不正です',
            'deviceIds.*.max'                   => 'デバイスIDが不正です',
            'deviceIds.*.exists'                => '指定されたデバイスIDが存在しません',
            'client_id.required'                   => 'クライアントが指定されていません',
            'client_id.string'                     => 'クライアントが不正です',
            'client_id.max'                        => 'クライアントが不正です',
            'client_id.exists'                     => '指定されたクライアントが存在しません',
            'contact.required'                  => '担当者が指定されていません',
            'contact.string'                    => '担当者が不正です',
            'contact.max'                       => '担当者が不正です',
            'contact.exists'                    => '指定された担当者が存在しません',
            'checkout_at.required'              => '貸出日が指定されていません',
            'checkout_at.date'                  => '貸出日が不正です',
            'checkout_at.before_or_equal'       => '貸出日は返却予定日以前の日付を指定してください',
            'schedule_return_at.required'       => '返却予定日が指定されていません',
            'schedule_return_at.date'           => '返却予定日が不正です',
            'note.string'                       => 'ノートが不正です',
            'note.max'                          => 'ノートは:max文字以内で指定してください',
        ];
    }

    /**
     * バリデーション前に実行する処理
     *  - プロジェクトIDを生成する
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'lend_id'    => (string) Uuid::uuid7(), // CHANGE: Add 2024-09-19
        ]);
    }

    /**
     * 追加バリデーション
     * オーバーライド
     * @param \Illuminate\Contracts\Validation\Validator $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            foreach ($this->input('deviceIds', []) as $device_id) {
                $device = Device::where('device_id', $device_id)->first();
                if ($device === null) {
                    $validator->errors()->add('deviceIds', '指定されたデバイスが存在しません');
                    continue;
                }
                if ($device->sale_id != '') {
                    $validator->errors()->add('deviceIds', 'すでに販売されているデバイスが含まれています');
                }
                if ($device->lending_now != '') {
                    $validator->errors()->add('deviceIds', '現在貸出されているデバイスが含まれています');
                }
                if ($device->defective == 1) {
                    $validator->errors()->add('deviceIds', '不良品が含まれています');
                }
            }
        });
    }

    /**
     * バリデーション後に実行する処理
     * Override
     * @access protected
     * @param Validator $validator
     */
    protected function failedValidation(Validator $validator)
    {
        $response = Redirect::back()
            ->with('error_message', '登録内容に誤りがあります')
            ->withInput()
            ->withErrors($validator->errors(), $this->errorBag);
        throw new HttpResponseException($response);
        // throw new HttpResponseException(
        //     response()->json([
        //         'status'    => 'error',
        //         'message'   => '登録内容に誤りがあります',
        //         'errors'    => $validator->errors()->toArray()
        //     ], 422)
        // );
    }
}
