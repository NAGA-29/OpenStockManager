@extends('layouts.app')

@section('content')

@endsection

@section('main_contents')
    <div class="col-md-12 shadow-sm pt-0 pr-0 pb-0 pl-0 mt-2 mb-2">
        <div class="device-bar rounded bg-dark text-white p-3">
            <div class="device-name h3 m-0">
                {{ __('複数 / 端末販売手続き < 確認 >') }}
            </div>
        </div>
    </div>

    <div class=" bg-white rounded shadow table-responsive text-nowrap">
        <div class="form-group">
            <p>以下の登録内容で登録します。よろしいですか？</p>
            <p>※処理に時間がかかる場合があります。</p>
        </div>
            <p>貸出先企業名:{{ $request_data[0]->client['company'] }}
                <a class="far fa-arrow-alt-circle-right" href="#"></a>
                {{-- <a class="far fa-arrow-alt-circle-right" href="{{ route('contact.detail', ['contact_id'=> $rental_details->contacts->contact_id]) }}"></a> --}}
            </p>
            <p>担当者名:{{ $request_data[0]['name'] }}
                <a class="far fa-arrow-alt-circle-right" href="#"></a></p>
            <p>販売日:{{ $request_data['sale_date_at'] }}</p>
            <p>ノート:{{ $request_data['note'] }}</p>
            {{-- {{ $stb_info_collection->links() }} --}}
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th scope="col" >端末ID</th>
                        <th scope="col" >端末区分</th>
                        <th scope="col" >端末名</th>
                        <th scope="col" >シリアル番号</th>
                        <th scope="col" >OS</th>
                        <th scope="col" >OS Ver</th>
                        <th scope="col" >ノート</th>
                        <th scope="col" >使用ID</th>
                        {{-- <th scope="col" >更新日</th> --}}
                    </tr>
                </thead>
                <tbody>
                    @foreach($lists as $list)
                    <tr>
                        <td >{{ $list[0]->device_id }}</td>
                        <td >{{ $list[0]->device_type }}</td>
                        <td >{{ $list[0]->device_name }}</td>
                        <td >{{ $list[0]->device_serial }}</td>
                        <td >{{ $list[0]->os }}</td>
                        <td >{{ $list[0]->os_ver }}</td>
                        <td >{{ $list[0]->note }}</td>

                        <td >{{ $list[1]['user_id'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        <form method="POST" action="{{ route('device.multi_sales_csv_store') }}">
            @csrf
            {{-- ボタン群 --}}
            <div class="col-md-6 offset-md-4">
                <button type="submit" name='back' value='back' class="btn btn-outline-secondary">{{ __('キャンセル') }}</button>
                <button type="submit" class="btn btn-outline-dark">{{ __('販売登録') }}</button>
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
