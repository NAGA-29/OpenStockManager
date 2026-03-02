<?php

namespace App\Http\Requests;

use App\Models\Device;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Redirect;
// Model
use Ramsey\Uuid\Uuid;

class StoreSaleCartRequest extends FormRequest
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
            'sale_id'               => ['required', 'string', 'max:255', 'unique:sale_hists,sale_id',],
            'deviceIds[]'           => ['array'],
            'deviceIds.*'           => ['required', 'string', 'max:255', 'exists:App\Models\Device,device_id', 'distinct'], // distinct->重複を検証
            'client_id'                => ['required', 'string', 'max:255', 'exists:App\Models\Client,client_id'],
            'personnel'             => ['required', 'string', 'max:255', 'exists:App\Models\Personnel,personnel_id'],
            'sale_date_at'          => ['required', 'date'],
            'note'                  => ['nullable', 'string', 'max:500'],
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
            'sale_id.required'                  => 'IDが指定されていません',
            'sale_id.string'                    => 'IDが不正です',
            'sale_id.max'                       => 'IDは:max文字以内で指定してください',
            'sale_id.unique'                    => 'IDが重複しています',
            'sale_id.distinct'                  => 'IDに重複があります',
            'deviceIds[].array'                 => 'デバイスIDが不正です',
            'deviceIds.*.string'                => 'デバイスIDが不正です',
            'deviceIds.*.max'                   => 'デバイスIDが不正です',
            'deviceIds.*.exists'                => '指定されたデバイスIDが存在しません',
            'client_id.required'                   => 'クライアントが指定されていません',
            'client_id.string'                     => 'クライアントが不正です',
            'client_id.max'                        => 'クライアントが不正です',
            'client_id.exists'                     => '指定されたクライアントが存在しません',
            'personnel.required'                => '担当者が指定されていません',
            'personnel.string'                  => '担当者が不正です',
            'personnel.max'                     => '担当者が不正です',
            'personnel.exists'                  => '指定された担当者が存在しません',
            'sale_date_at.required'             => '販売日が指定されていません',
            'sale_date_at.date'                 => '販売日が不正です',
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
            'sale_id'    => (string) Uuid::uuid7(), // CHANGE: Add 2024-09-19
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
            foreach ($this->input('deviceIds') as $device_id) {
                $device = Device::where('device_id', $device_id)->first();
                if ($device->sale_id != '') {
                    $validator->errors()->add('deviceIds', 'すでに販売されているデバイスが含まれています');
                }
                if ($device->lending_now != '') {
                    $validator->errors()->add('deviceIds', '現在貸出されているデバイスが含まれています');
                }
                if ($device->defective == 1) {
                    $validator->errors()->add('deviceIds', '不良品が含まれています');
                }
                if ($device->not_for_sale == 1) {
                    $validator->errors()->add('deviceIds', '販売対象外のデバイスが含まれています');
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
