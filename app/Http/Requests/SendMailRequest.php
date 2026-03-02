<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendMailRequest extends FormRequest
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
            'to_email' => ['required', 'email'],
            'subject'  => ['required', 'string'],
            'body'     => ['required', 'string'],
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
            'to_email.required' => '送信先メールアドレスを入力してください。',
            'to_email.email'    => '有効なメールアドレスを入力してください。',
            'subject.required'  => '件名を入力してください。',
            'body.required'     => '本文を入力してください。',
        ];
    }
}
