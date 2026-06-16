<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Ramsey\Uuid\Uuid;

class StoreRentalFileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
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
            'lend_id'               => ['required', 'string', 'max:255', 'unique:rental_hists,lend_id',],
            'client_id'             => ['required', 'string', 'max:255', 'exists:App\Models\Client,client_id'],
            'contact'               => ['required', 'string', 'max:255', 'exists:App\Models\Contacts,id'],
            'checkout_at'           => ['required', 'date', 'before_or_equal:schedule_return_at'],
            'schedule_return_at'    => ['required', 'date'],
            'note'                  => ['nullable', 'string', 'max:500',],
            'csv_file'              => ['required', 'file', 'mimes:csv,txt',],
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
            'client_id.required'                => 'クライアントが指定されていません',
            'client_id.string'                  => 'クライアントが不正です',
            'client_id.max'                     => 'クライアントが不正です',
            'client_id.exists'                  => '指定されたクライアントが存在しません',
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
            'lend_id'    => (string) Uuid::uuid7(), // CHANGE: Add 2024-10-01
        ]);
    }

}
