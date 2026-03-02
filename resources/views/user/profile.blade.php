@extends('layouts.app')

@section('content')

@endsection

@section('main_contents')
    {{-- <div class="col-xl-12 shadow-sm pt-0 pr-0 pb-0 pl-0 mt-2 mb-2"> --}}
    <div class="row">
        <div class="col-xl-12">
            <div class="card card-shadow mb-3 mt-3 bg-dark">
                <div class="card-body">
                    <div class="row">
                        <div class="col-xl-3 col-md-6 col-sm-6">
                            <div class="device-bar rounded bg-dark text-white">
                                <div class="device-name h3 m-0">
                                    {{ __('マイページ') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div>
        <div class="card">
            <div class="card-body">
                <p>※ メールアドレス以外の変更を希望する場合は管理者に問い合わせてください</p>
                <div class="profile-item p-2">
                    <label>名前:</label>
                    <span class="value">{{ $user->name }}</span>
                </div>
                <div class="profile-item p-2">
                    <label>メールアドレス:</label>
                    <span class="value">{{ $user->email }}</span>
                    <button type="button" class="btn btn-outline-dark btn-sm" data-bs-toggle="modal"
                        data-bs-target="#EmailChangeModal">
                        変更
                    </button>
                </div>
                <div class="profile-item p-2">
                    <label>パスワード:</label>
                    <span class="value">********</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Email modal --}}
    <div class="modal fade" id="EmailChangeModal" tabindex="-1" role="dialog" aria-labelledby="ModalLongTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    @include('component.modal.email_change')
                </div>
            </div>
        </div>
    </div>
    {{-- Modal End --}}
@endsection

@section('js')
@endsection
