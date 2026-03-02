@extends('layouts.app')

@section('content')
@endsection

@section('main_contents')
    @if (session('device_cart') === 'RESET')
        <script>
            // sessionStorageにカート情報をリセット
            sessionStorage.removeItem("DeviceManagerCart");
        </script>
    @endif

    <div class="col-md-12 shadow-sm pt-0 pr-0 pb-0 pl-0 mt-2 mb-2">
        <div class="device-bar rounded bg-dark text-white p-3">
            <div class="device-name h3 m-0">
                {{ __('レンタル履歴一覧') }}
            </div>
        </div>
    </div>

    <div class="row device-toolbar">
        <div class="device-toolbar-left"></div>
        <div class="device-toolbar-right">
            <form action="{{ route('rental.history') }}" method="GET" class="col-xl-12 device-toolbar-form">
                <div class="input-group">
                    <input type="text" name="word" class="form-control" placeholder="貸出先 or ノート" value="{{ request('word') }}">
                    <button type="submit" class="btn btn-outline-dark">
                        <i class="fas fa-search"></i> 検索
                    </button>
                </div>
            </form>
        </div>
    </div>
    <div class="row device-pagination">
        <div class="col-md-12 col-xl-6">
            {{ $histories->links() }}
        </div>
    </div>

    <div class=" bg-white rounded shadow table-responsive text-nowrap">
        <table class="table table-hover">
            <thead class="table-dark">
                <tr>
                    <th scope="col">貸出 / 返却</th>
                    <th scope="col">貸出先</th>
                    <th scope="col">貸出日</th>
                    <th scope="col">返却予定日</th>
                    <th scope="col">返却日</th>
                    <th scope="col">ノート</th>
                    <th scope="col"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($histories as $his)
                    {{-- 貸出中判定 --}}
                    @if ($his->all_returned == 0)
                        <tr class='table-info'>
                            <td>
                                <i class="fas fa-dove fa-2x" aria-hidden="true"></i><span class="sr-only">貸出中</span>
                            </td>
                        @else
                        <tr>
                            <td>返却済</td>
                    @endif
                    <td>{{ $his->clients->company }}</td>
                    <td>{{ $his->checkout_at ? $his->checkout_at->format('Y-m-d') : null }}</td>
                    <td>{{ $his->schedule_return_at ? $his->schedule_return_at->format('Y-m-d') : null }}</td>
                    <td>{{ $his->return_at ? $his->return_at->format('Y-m-d') : null }}</td>
                    <td class="text-truncate" style="max-width:200px;">{{ $his->note }}</td>
                    <td><button type="button" class="btn btn-outline-dark"
                            onclick="location.href='{{ route('rental.rental_detail', ['id' => $his->lend_id]) }}'">
                            <i class="far fa-edit"></i></button>
                    </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="row device-pagination mt-3">
        <div class="col-md-12 col-xl-6">
            {{ $histories->links() }}
        </div>
    </div>
@endsection
