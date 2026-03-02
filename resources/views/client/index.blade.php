@extends('layouts.app')

@section('css')
@endsection

@section('main_contents')
    <div class="col-md-12 shadow-sm p-0 mt-2 mb-2">
        <div class="device-bar d-flex align-items-center justify-content-between rounded bg-dark text-white p-3">
            <div class="device-name h3 m-0">
                {{ __('登録クライアント一覧') }}
            </div>
            <div>
                <button type="button" id="synchronize-button" class="btn btn-outline-dark">
                    最新情報取得 <i class="fas fa-redo"></i>
                </button>
            </div>
        </div>
    </div>

    <div>
        <div class="row device-toolbar">
            <div class="col-lg-6 col-md-6 device-toolbar-right">
                <form action="{{ route('client.list') }}" method="GET" class="col-xl-12 device-toolbar-form">
                    <div class="input-group">
                        <input type="text" name="word" class="form-control" placeholder="クライアント名" value="{{ $word ?? '' }}">
                        <button type="submit" class="btn btn-outline-dark">
                            <i class="fas fa-search"></i> 検索
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div class="row device-pagination">
            <div class="col-md-12 col-xl-6">
                {{ $clients->links() }}
            </div>
        </div>
    </div>

    <div class=" bg-white rounded shadow table-responsive text-nowrap">
        <table class="table table-hover">
            <thead class="table-dark">
                <tr>
                    <th scope="col">クライアント名</th>
                    <th scope="col">URL</th>
                    <th scope="col">住所</th>
                    <th scope="col">ノート</th>
                    <th scope="col"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($clients as $client)
                    <tr>
                        <td>
                            <a href="{{ route('client.details', ['client_id' => $client->client_id]) }}">
                                {{ $client->company }}
                            </a>
                        </td>
                        <td>{{ $client->url }}</td>
                        <td>{{ $client->street_address }}</td>
                        <td class="text-truncate" style="max-width:200px;">{{ $client->note }}</td>
                        <td>
                            <button type="button" class="btn btn-outline-dark"
                                onclick="location.href='{{ url('/client/id', $client->client_id) }}'">
                                <i class="far fa-edit"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

@section('js')
    @vite('resources/js/ui/button/synchronize-button.ts')
@endsection
