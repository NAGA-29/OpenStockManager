@extends('layouts.app')

@section('content')
@endsection

@section('main_contents')
    @if ($errors->any())
        <div class="alert alert-danger m-3">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="col-md-12 shadow-sm pt-0 pr-0 pb-0 pl-0 mt-2 mb-2">
        <div class="device-bar rounded bg-dark text-white p-3">
            <div class="device-name h3 m-0">
                {{ __('管理者 - 新規登録') }}
            </div>
        </div>
    </div>

    <div class="bg-white rounded shadow table-responsive text-nowrap">
        <div class="card">
            <div class="card-body m-5">
                <form action="{{ route('user.store') }}" method='POST'>
                    @csrf
                    <table class="table table-hover">
                        <tr>
                            <td>{{ __('ユーザー名') }}</td>
                            <td>
                                <div class="form-group row">
                                    <div class="col-md-10">
                                        <input id="name" type="text"
                                            class="form-control @error('name') is-invalid @enderror" name="name"
                                            value="{{ old('name') }}" required autofocus>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>{{ __('Email') }}</td>
                            <td>
                                <div class="form-group row">
                                    <div class="col-md-10">
                                        <input id="email" type="email"
                                            class="form-control @error('email') is-invalid @enderror" name="email"
                                            value="{{ old('email') }}" required>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>{{ __('パスワード') }}</td>
                            <td>
                                <div class="form-group row">
                                    <div class="col-md-10">
                                        <input id="password" type="password"
                                            class="form-control @error('password') is-invalid @enderror" name="password"
                                            required>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>{{ __('パスワード確認') }}</td>
                            <td>
                                <div class="form-group row">
                                    <div class="col-md-10">
                                        <input id="password-confirm" type="password" class="form-control"
                                            name="password_confirmation" required>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </table>

                    <div class='text-center m-3'>
                        <button type="submit" class='btn btn-outline-dark mr-3'>登録</button>
                        <button type="reset" class='btn btn-outline-danger mr-3'>リセット</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script_area')
@endsection
