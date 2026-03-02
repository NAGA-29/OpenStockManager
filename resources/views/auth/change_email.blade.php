@extends('adminlte::page')
<link rel="stylesheet" href="{{ asset('css/change_email.css') }}">
<div class="change-email-wrapper">

    <div class="change-email-card">
        <div class="change-email-header">
            メールアドレス変更
        </div>
    </div>

<div class="change-email-instructions">
        {{ __('新しいメールアドレスを入力して送信ボタンをクリックしてください。') }}<br>
        {{ __('新しいメールアドレスに認証メールをお送りします。') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="change-email-status" :status="session('status')" />

    <!-- Validation Errors -->
    <x-auth-validation-errors class="change-email-errors" :errors="$errors" />

    <form method="POST" action="{{ route('users.email.change', ['user_id' => $user->id]) }}" class="change-email-form">
        @csrf

        <!-- Email Address -->
        <div class="change-email-input-group">
            <label for="email" :value="__('Email')" class="change-email-label" />
            <input id="email" class="change-email-input" type="email" name="email" :value="old('email')"
                required autofocus />
        </div>

        <div class="change-email-buttons">
            <button class='change-email-button change-email-back' type='button' onclick="history.back()">{{ __('戻る') }}</button>
            <button type='submit' class='change-email-button change-email-submit'>{{ __('送信') }}</button>
        </div>
    </form>
</div>