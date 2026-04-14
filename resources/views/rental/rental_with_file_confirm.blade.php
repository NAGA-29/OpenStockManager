@extends('layouts.app')

@section('content')

@endsection

@section('main_contents')
    <div class="col-md-12 shadow-sm pt-0 pr-0 pb-0 pl-0 mt-2 mb-2">
        <div class="device-bar rounded bg-dark text-white p-3">
            <div class="device-name h3 m-0">
                {{ __('複数レンタル手続き < 確認 >') }}
            </div>
        </div>
    </div>

    <div class=" bg-white rounded shadow table-responsive text-nowrap">
        <div class="form-group">
            <p>以下の登録内容で登録します。よろしいですか？</p>
            <p>※処理に時間がかかる場合があります。</p>
        </div>
            <p>貸出先企業名:{{ $safe[0]->client['company'] }}
                <a class="far fa-arrow-alt-circle-right" href="#"></a>
                {{-- <a class="far fa-arrow-alt-circle-right" href="{{ route('contact.detail', ['contact_id'=> $rental_details->contacts->contact_id]) }}"></a> --}}
            </p>
            <p>担当者名:{{ $safe[0]['name'] }}
                <a class="far fa-arrow-alt-circle-right" href="#"></a></p>
            <p>貸出日:{{ $safe['checkout_at'] }}</p>
            <p>返却予定日:{{ $safe['schedule_return_at'] }}</p>
            <p>ノート:{{ $safe['note'] }}</p>
            {{-- {{ $stb_info_collection->links() }} --}}
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th scope="col" >端末ID</th>
                        <th scope="col" >端末区分</th>
                        <th scope="col" >端末名</th>
                        <th scope="col" >OS</th>
                        <th scope="col" >OS Ver</th>
                        <th scope="col" >ノート</th>
                        <th scope="col" >更新日</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lists as $list)
                    <tr>
                        <td >{{ $list[0]['device_id'] }}</td>
                        <td >{{ $list[0]['device_type'] }}</td>
                        <td >{{ $list[0]['device_name'] }}</td>
                        <td >{{ $list[0]['os'] }}</td>
                        <td >{{ $list[0]['os_ver'] }}</td>
                        <td >{{ $list[1]['note'] }}</td>
                        <td >{{ $list[0]['modified_at'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        <form method="POST" action="{{ route('device.multi_csv_store') }}">
            @csrf
            {{-- ボタン群 --}}
            <div class="col-md-6 offset-md-4">
                <button type="submit" name='back' value='back' class="btn btn-secondary">{{ __('キャンセル') }}</button>
                <button type="submit" class="btn btn-outline-dark">{{ __('貸出し登録する') }}</button>
            </div>
        </form>
    </div>
@endsection

@section('script_area')
@endsection
