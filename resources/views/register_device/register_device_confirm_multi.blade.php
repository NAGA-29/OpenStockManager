@extends('layouts.app')

@section('content')

@endsection

@section('main_contents')
    <div class="col-md-12 shadow-sm pt-0 pr-0 pb-0 pl-0 mt-2 mb-2">
        <div class="device-bar rounded bg-dark text-white p-3">
            <div class="device-name h3 m-0">
                {{ __('新規デバイス登録 < 確認 >') }}
            </div>
        </div>
    </div>

    <div class=" bg-white rounded shadow table-responsive text-nowrap">
        <div class="form-group">
            <p>以下の登録内容で登録します。よろしいですか？</p>
            <p>※処理に時間がかかる場合があります。</p>
        </div>
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th scope="col" >端末ID (自動生成)</th>
                        <th scope="col" >端末区分</th>
                        <th scope="col" >端末名</th>
                        <th scope="col" >シリアル番号</th>
                        <th scope="col" >OS</th>
                        <th scope="col" >OS Ver</th>
                        <th scope="col" >初回稼働日時</th>
                        <th scope="col" >購入日</th>
                        <th scope="col" >オプション</th>
                        <th scope="col" >不具合</th>
                        <th scope="col" >販売不可</th>
                        <th scope="col" >ノート</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($devices as $device)
                    <tr>
                        <td >{{ $device['device_id'] }}</td>
                        <td >{{ $device['device_type'] }}</td>
                        <td >{{ $device['device_name'] }}</td>
                        <td >{{ $device['device_serial'] }}</td>
                        <td >{{ $device['os'] }}</td>
                        <td >{{ $device['os_ver'] }}</td>
                        <td >{{ $device['first_work_date_at'] }}</td>
                        <td >{{ $device['purchase_date_at'] }}</td>
                        <td >{{ $device['option'] }}</td>
                        {{-- 不具合判定 --}}
                        @if($device['defective'] == 1)
                            <td ><i class="fas fa-check-circle fa-lg danger-icon" aria-hidden="true"></i><span class="sr-only">該当</span></td>
                        @else
                            <td ></td>
                        @endif
                        {{-- 販売不可判定 --}}
                        @if($device['not_for_sale'] == 1)
                            <td ><i class="fas fa-check-circle fa-lg danger-icon" aria-hidden="true"></i><span class="sr-only">該当</span></td>
                        @else
                            <td ></td>
                        @endif
                        <td >{{ $device['note'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        <form method="POST" action="{{ route('device.store_multi') }}">
            @csrf
            {{-- ボタン群 --}}
            <div class='text-center'>
                <button type="submit" class='btn btn-outline-dark mr-3'>登録</button>
                <button type="reset" class='btn btn-outline-danger mr-3'>キャンセル</button>
            </div>
        </form>
    </div>
@endsection

@section('script_area')
@endsection
