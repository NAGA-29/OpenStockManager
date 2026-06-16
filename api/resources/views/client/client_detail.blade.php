@extends('layouts.app')

{{-- @include('layouts.sidebar') --}}

@section('content')
@endsection


@section('main_contents')
    <div class="col-md-12 shadow-sm pt-0 pr-0 pb-0 pl-0 mt-2 mb-2">
        <div class="device-bar rounded bg-dark text-white p-3">
            <div class="device-name h3 m-0">
                {{ __('クライアント詳細情報') }}
            </div>
        </div>
    </div>

    @if (session('success'))
        更新に成功しました。
    @endif
    <div class="card">
        <div class="card-body m-5">
            <div>
                <p>※ クライアント情報の変更は専門CRMで行ってください</p>
            </div>
            <table class="table table-hover">
                <tr>
                    <td>会社名</td>
                    <td>{{ $client->company }}</td>
                </tr>
                <tr>
                    <td>URL</td>
                    <td>{{ $client->url }}</td>
                </tr>
                <tr>
                    <td>電話番号</td>
                    <td>{{ $client->tel }}</td>
                </tr>
                <tr>
                    <td>住所</td>
                    <td>{{ $client->street_address }}</td>
                </tr>
                <tr>
                    <td>ノート</td>
                    <td>{{ $client->note }}</td>
                </tr>
                <tr>
                    <td>更新日</td>
                    <td>{{ $client->modified_at }}</td>
                </tr>
            </table>
            {{-- <button type="button" class="btn btn-outline-dark" data-toggle="modal" data-target="#EditModal">
            編集する
        </button> --}}
        </div>
    </div>

    <div class="col-md-12 shadow-sm pt-0 pr-0 pb-0 pl-0 mt-2 mb-2">
        <div class="device-bar rounded bg-dark text-white p-3">
            <div class="device-name h3 m-0">
                {{ __('担当者一覧') }}
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body m-5">
            <div class='table-responsive text-nowrap'>
                <table class="table table-hover">
                    <tr>
                        <th>名前</th>
                        <th>電話番号</th>
                        <th>Email</th>
                        <th>ノート</th>
                        <th>更新日</th>
                        <th></th>
                    </tr>
                    @foreach ($client->contacts as $p)
                        <tr>
                            <td class='flex-nowrap'>{{ $p->name }}</td>
                            <td>{{ $p->tel }}</td>
                            <td>{{ $p->email }}</td>
                            <td class="text-wrap" style="max-width: 450px; ">{{ $p->note }}</td>
                            <td>{{ $p->modified_at }}</td>
                            <td>
                                <a href="{{ route('contact.detail', ['contact_id' => $p->id]) }}">
                                    {{-- <button type="button" class="btn btn-outline-dark"　data-toggle="modal" data-target="#client_Edit_Modal">詳細</button> --}}
                                    <button type="button" class="btn btn-outline-dark">詳細</button>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>
    </div>
    <!-- client Edit Modal -->
    {{-- <div class="modal fade" id="EditModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">企業情報の編集</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
        </div>
        <div class="modal-body">
        @include('modal.edit_client_info')
        </div>
    </div>
    </div>
</div> --}}
    <!-- Modal End-->
@endsection
