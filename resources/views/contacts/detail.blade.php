@extends('layouts.app')

@section('content')
@endsection

@section('main_contents')
    <div class="col-md-12 shadow-sm pt-0 pr-0 pb-0 pl-0 mt-2 mb-2">
        <div class="device-bar rounded bg-dark text-white p-3">
            <div class="device-name h3 m-0">
                {{ __('担当者情報') }}
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body m-5">
            <div>
                <p>※ クライアント情報の変更は専用CRMで行ってください</p>
            </div>
            <table class="table table-hover">
                <tr>
                    <td>所属企業名</td>
                    <td>{{ $contact->client->company }}</td>
                </tr>
                <tr>
                    <td>名前</td>
                    <td>{{ $contact->name }}</td>
                </tr>
                <tr>
                    <td>電話番号</td>
                    <td>{{ $contact->tel }}</td>
                </tr>
                <tr>
                    <td>Email</td>
                    <td>{{ $contact->email }}</td>
                </tr>
                <tr>
                    <td>ノート</td>
                    <td>{{ $contact->note }}</td>
                </tr>
            </table>
        </div>
    </div>
@endsection
