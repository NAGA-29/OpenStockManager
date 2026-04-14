@extends('layouts.app')

@section('content')
@endsection

@section('main_contents')
    <div class="col-md-12 shadow-sm pt-0 pr-0 pb-0 pl-0 mt-2 mb-2">
        <div class="device-bar rounded bg-dark text-white p-3">
            <div class="device-name h3 m-0">
                {{ __('端末返却手続き < 確認 >') }}
            </div>
        </div>
    </div>

    <div class=" bg-white rounded shadow table-responsive text-nowrap">
        <div class="form-group">
            <p>以下の端末を返却登録します。再度内容を確認してください。</p>
        </div>
        <p>貸出先企業名: {{ $request_data->clients->company }}
            <a class="far fa-arrow-alt-circle-right"
                href="{{ route('client.details', ['client_id' => $request_data->clients->client_id]) }}"></a>
        </p>
        <p>担当者名: {{ $request_data->contacts->name }}
            <a class="far fa-arrow-alt-circle-right"
                href="{{ route('contact.detail', ['contact_id' => $request_data->contacts->id]) }}"></a>
        </p>
        <p>貸出日: {{ $request_data->checkout_at }}</p>
        <p>返却予定日: {{ $request_data->schedule_return_at }}</p>
        <p>ノート: {{ $request_data['note'] }}</p>
        <table class="table table-hover">
            <thead class="table-dark">
                <tr>
                    <th scope="col">端末ID</th>
                    <th scope="col">端末区分</th>
                    <th scope="col">端末名</th>
                    <th scope="col">シリアル番号</th>
                    <th scope="col">OS</th>
                    <th scope="col">OS Ver</th>
                    <th scope="col">ノート</th>
                    <th scope="col">使用ID</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($collection as $list)
                    <tr>
                        <td>{{ $list['device_id'] }}</td>
                        <td>{{ $list['device_type'] }}</td>
                        <td>{{ $list['device_name'] }}</td>
                        <td>{{ $list['device_serial'] }}</td>
                        <td>{{ $list['os'] }}</td>
                        <td>{{ $list['os_ver'] }}</td>
                        <td>{{ $list['note'] }}</td>
                        <td>{{ $list['using_user_id'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <form method="POST"
            action="{{ route('device.multi_return_device_complete', ['lend_id' => $request_data->lend_id]) }}">
            @csrf
            <div class='text-center'>
                <button type="submit" class='btn btn-outline-danger mr-3'>返却</button>
            </div>
        </form>
    </div>
@endsection

@section('script_area')
    {{-- <script src="https://cdn.jsdelivr.net/npm/bs-custom-file-input/dist/bs-custom-file-input.js"></script> --}}
    <script>
        // bsCustomFileInput.init();
    </script>
@endsection
