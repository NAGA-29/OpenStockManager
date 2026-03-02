<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Redirect;

class UpdateSaleHistoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'sale_id'               => ['required', 'exists:sale_hists,sale_id', 'max:255'],
            'sale_date_at'          => ['nullable', 'date'],
            'note'                  => ['nullable', 'string', 'max:255'],
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
            'sale_id.required'      => '入力内容が不正です。',
            'sale_id.exists'        => '存在しない販売履歴です。',
            'sale_date_at.date'     => '日付を入力してください。',
            'note.string'           => '文字列を入力してください。',
            'note.max'              => '255文字以内で入力してください。',
        ];
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
