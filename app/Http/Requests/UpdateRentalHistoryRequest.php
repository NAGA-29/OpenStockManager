<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Redirect;

class UpdateRentalHistoryRequest extends FormRequest
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
     * @return array
     */
    public function rules(): array
    {
        return [
            'lend_id'            => ['required', 'string', 'exists:rental_hists,lend_id'],
            'checkout_at'        => ['required', 'date'],
            'schedule_return_at' => ['required', 'date', 'after_or_equal:checkout_at'],
            'note'               => ['nullable', 'string'],
        ];
    }

    /**
     * エラーメッセージ
     *
     * @return string[]
     */
    public function messages(): array
    {
        return [
            'lend_id.required'              => '貸出IDが不正です。',
            'lend_id.exists'                => '存在しない貸出履歴です。',
            'checkout_at.required'          => '貸出日を入力してください。',
            'checkout_at.date'              => '貸出日は日付を入力してください。',
            'schedule_return_at.required'   => '返却予定日を入力してください。',
            'schedule_return_at.date'       => '返却予定日は日付を入力してください。',
            'schedule_return_at.after_or_equal' => '返却予定日は貸出日以降の日付を入力してください。',
        ];
    }

    /**
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
    }
}
