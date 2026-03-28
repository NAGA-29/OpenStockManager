@extends('layouts.app')

{{-- @include('layouts.sidebar') --}}

@section('content')
@endsection

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
            {{ __('担当者一覧') }}
        </div>
    </div>
</div>
{{ $personnel->links() }}
<div class=" bg-white rounded shadow table-responsive text-nowrap">
    <table class="table table-hover">
        <thead class="table-dark">
            <tr>
                <th scope="col" >名前</th>
                <th scope="col" >所属</th>
                <th scope="col" >TEL</th>
                <th scope="col" >Mail</th>
                <th scope="col" >ノート</th>
                <th scope="col" ></th>
            </tr>
        </thead>
        <tbody>
            @foreach($personnel as $person)
                <tr>
                    <td>{{ $person->name }}</td>
                    <td>{{ $person->client->company }}</td>
                    <td>{{ $person->tel }}</td>
                    <td>{{ $person->email }}</td>
                    <td>{{ $person->note }}</td>
                    <td>
                        <a href='{{ url('/personnel/detail', $person->personnel_id) }}'>
                            <button type="button" class="btn btn-outline-dark">詳細</button>
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
