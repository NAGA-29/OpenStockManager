@extends('layouts.auth')

@section('hideHeader', true)

@section('content')
    <div class="min-vh-100 d-flex align-items-center bg-gradient-to-br from-slate-100 via-white to-slate-200 py-4">
        <div class="container w-100">
            <div class="row justify-content-center">
                <div class="col-md-10 col-xl-6">
                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl">
                        <div class="row -mx-0">
                            <div class="col-md-6 px-0">
                                <div class="h-full bg-gradient-to-br from-slate-800 to-slate-950 p-8 text-white">
                                    <p
                                        class="mb-4 text-xl inline-flex items-center rounded-full bg-white/10 px-4 py-1 font-semibold uppercase tracking-wider">
                                        Device Manager
                                    </p>
                                    <h2 class="mb-4 text-3xl font-bold leading-tight">Welcome</h2>
                                    <p class="mb-6 text-sm text-slate-200">
                                        管理者から発行されたアカウント情報でログインしてください。
                                    </p>
                                    <ul class="space-y-3 text-sm text-slate-200">
                                        <li class="flex items-center gap-2"><i
                                                class="fas fa-tablet-alt"></i><span>デバイス管理・ステータス確認を一元化</span></li>
                                        <li class="flex items-center gap-2"><i
                                                class="fas fa-history"></i><span>履歴情報を安全にトラッキング</span></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-md-6 px-0">
                                <div class="p-8 md:p-10">
                                    <div class="mb-6 text-center">
                                        <h3 class="mb-2 text-2xl font-bold text-slate-800">ログイン</h3>
                                        <p class="text-sm text-slate-500">メールアドレスとパスワードを入力してください</p>
                                    </div>

                                    <form method="POST" action="{{ route('login') }}">
                                        @csrf

                                        <div class="form-group mb-4">
                                            <label for="email" class="mb-2 block text-sm font-semibold text-slate-700">
                                                {{ __('E-Mail Address') }}
                                            </label>
                                            <div class="relative">
                                                <i
                                                    class="fas fa-envelope pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                                <input id="email" type="email"
                                                    class="form-control @error('email') is-invalid @enderror pl-10"
                                                    name="email" value="{{ old('email') }}" required autocomplete="email"
                                                    autofocus>
                                            </div>
                                            @error('email')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <div class="form-group mb-4">
                                            <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">
                                                {{ __('Password') }}
                                            </label>
                                            <div class="relative">
                                                <i
                                                    class="fas fa-lock pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                                <input id="password" type="password"
                                                    class="form-control @error('password') is-invalid @enderror pl-10"
                                                    name="password" required autocomplete="current-password">
                                            </div>
                                            @error('password')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <div class="mb-6 flex items-center justify-between">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="remember"
                                                    id="remember" {{ old('remember') ? 'checked' : '' }}>
                                                <label class="form-check-label text-slate-600" for="remember">
                                                    {{ __('Remember Me') }}
                                                </label>
                                            </div>
                                        </div>

                                        <button type="submit"
                                            class="w-full rounded-lg bg-slate-800 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-900">
                                            <i class="fas fa-sign-in-alt mr-2"></i>{{ __('Login') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
