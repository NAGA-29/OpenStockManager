@extends('layouts.app')

@section('content')
@endsection

{{-- @include('layouts.sidebar') --}}
{{-- <body class="antialiased"> --}}
<!-- フラッシュメッセージ -->
{{-- @if (Session::has('register_message'))
        <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="label1" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body text-center">
                            {{ Session::get('register_message') }}
                        </div>
                        <div class="modal-footer text-center">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif --}}
{{-- フラッシュメッセージ END --}}
{{-- <div class="main-content">
        <div class="container" style="max-width: 1280px;">
            <div class='row'> --}}
@section('main_contents')
    <div class="col-md-12 shadow-sm pt-0 pr-0 pb-0 pl-0 mt-2 mb-2">
        <div class="device-bar rounded bg-dark text-white p-3">
            <div class="device-name h3 m-0">
                {{ __('新規担当者登録') }}
            </div>
        </div>
    </div>

    @if (Session::has('contact_register'))
        <div class="alert alert-success" role="alert">
            {{ session('contact_register') }}
        </div>
    @endif

    <div class=" bg-white rounded shadow table-responsive text-nowrap">
        <div class="card">
            <div class="card-body m-5">
                <form action={{ Route('contact.register') }} method='POST' class='h6 font-weight-bold'>
                    @csrf
                    <div class="form-group row">
                        <div class="col-md-10">
                            <label for='ciient_id'>企業名</label>
                            <p class="search_result">選択されていません</p>
                            <input id="client_id" type="hidden"
                                class="form-control @error('client_id') is-invalid @enderror" name="client_id" required
                                autofocus>
                        </div>
                        <button type="button" class="btn btn-outline-dark" data-toggle="modal"
                            data-target="#ClientSearchModal">
                            検索
                        </button>
                    </div>
                    <div class="form-group">
                        <label for='name'>担当者名</label>
                        <input class="form-control" id='name' type='text' name='name'
                            value="{{ old('name') }}">
                    </div>
                    <div class="form-group">
                        <label for='tel'>電話番号</label>
                        <input class="form-control" id='tel' type='text' name='tel'
                            value="{{ old('tel') }}">
                    </div>
                    <div class="form-group">
                        <label for='email'>Email</label>
                        <input class="form-control" id='email' type='text' name='email'
                            value="{{ old('email') }}">
                    </div>
                    <div class="form-group">
                        <label for='note'>ノート</label>
                        <textarea class="form-control" type='text' name='note' rows="5">{{ old('note') }}</textarea>
                    </div>

                    <div class='text-center'>
                        <button type="submit" class='btn btn-outline-dark mr-3'>登録</button>
                        <button type="reset" class='btn btn-outline-danger mr-3'>リセット</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Search Client Modal -->
    <div class="modal fade" id="ClientSearchModal" tabindex="-1" role="dialog" aria-labelledby="clientSearchcontactModalTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="clientSearchcontactModalTitle">担当者所属企業の検索</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @include('component.modal.client_search_for_contact')
                </div>
            </div>
        </div>
    </div>
    <!-- Modal End-->
@endsection
