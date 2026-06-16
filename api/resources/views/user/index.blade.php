@extends('layouts.app')

@section('css')
    @vite('resources/css/edit-user-dialog.css')
@endsection

@section('content')
@endsection

@section('main_contents')
    <div>
        @if ($errors->any())
            <div class="alert alert-danger m-3">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="col-md-12 shadow-sm p-0 mt-2 mb-2">
            <div class="device-bar d-flex align-items-center justify-content-between rounded bg-dark text-white p-3">
                <div class="device-name h3 m-0">
                    {{ __('管理者一覧') }}
                </div>
                <div>
                    <a href="{{ route('user.register') }}" type="button" class="btn btn-outline-dark">
                        {{ __('新規登録') }}
                    </a>
                </div>
            </div>
        </div>

        <div>
            <div class="row device-toolbar">
                @include('component.search_form')
            </div>
            <div class="row device-pagination">
                <div class="col-md-12 col-xl-6">
                    {{-- {{ $users->links() }} --}}
                </div>
            </div>
        </div>

        <div class=" bg-white rounded shadow table-responsive text-nowrap">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">{{ __('名前') }}</th>
                        <th scope="col">{{ __('メールアドレス') }}</th>
                        <th scope="col">{{ __('権限') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr class="edit-user-row" style="cursor: pointer;"
                            data-id="{{ $user->id }}"
                            data-name="{{ $user->name }}"
                            data-email="{{ $user->email }}"
                            data-role="{{ $user->role }}">
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->role === 'admin' ? __('管理者') : __('一般ユーザー') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @include('component.modal.edit_user')
@endsection

@section('js')
    @vite('resources/js/user/edit-user-modal.js')
@endsection
