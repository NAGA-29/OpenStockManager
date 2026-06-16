@extends('layouts.app')

@section('content')
@endsection
{{-- @include('layouts.sidebar') --}}

<body class="antialiased">
    <div class="main-content">
        <div class="container" style="max-width: 1280px;">
            <div class='row'>
                <div class="col-lg-2 p-0">
                    <div class="sidebar-container">
                        <ul class="sidebar-navigation">
                            <li class="header"><i class="fa fa-history"></i> 履歴</li>
                            <li>
                                <a href="#">
                                    <i aria-hidden="true"></i> 販売履歴
                                </a>
                            </li>
                            <li>
                                <a href="#">
                                    <i aria-hidden="true"></i> 貸出履歴
                                </a>
                            </li>
                            <li>
                                <a href="#">
                                    <i aria-hidden="true"></i> 全体履歴
                                </a>
                            </li>
                            <li class="header"><i class="fa fa-tablet-alt" ></i>機材</li>
                            <li>
                                <a href={{route('device.stb')}}>
                                    <i aria-hidden="true"></i> STB
                                </a>
                            </li>
                            <li>
                                <a href={{route('device.tablet')}}>
                                    <i aria-hidden="true"></i> タブレット
                                </a>
                            </li>
                            <li>
                                <a href={{route('device.camera')}}>
                                    <i aria-hidden="true"></i> カメラ
                                </a>
                            </li>
                            <li>
                                <a href={{route('device.signage')}}>
                                    <i aria-hidden="true"></i> サイネージ
                                </a>
                            </li>
                            <li>
                                <a href={{route('device.other')}}>
                                    <i aria-hidden="true"></i> その他機材
                                </a>
                            </li>
                            <li class="header"><i class="fa fa-file-signature" ></i>登録</li>
                            <li>
                                <a href={{route('device.register')}}>
                                    <i aria-hidden="true"></i> 機材登録 (単数)
                                </a>
                            </li>
                            <li>
                                <a href={{route('device.register_multi')}}>
                                    <i aria-hidden="true"></i> 機材登録 (複数)
                                </a>
                            </li>
                            <li>
                                <a href="#">
                                    <i aria-hidden="true"></i> 貸出手続き
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>


            <div class="col-lg-10 float-right pt-0 pr-3 pb-0 pl-3">

                <div class="col-md-12 shadow-sm pt-0 pr-0 pb-0 pl-0 mt-2 mb-2">
                    <div class="device-bar rounded bg-dark text-white p-3">
                        <div class="device-name h3 m-0">
                            {{ __('STB') }}
                        </div>
                        <div class='device-volume'>
                            <span class='m-0'>不具合:2　</span>
                            <span class='m-0'>貸出中:20　</span>
                            <span class='m-0'>合計:100　</span>
                        </div>
                    </div>
                    {{-- <div class="card">
                        <div class="card-header bg-dark text-white">{{ __('STB') }}</div> --}}
                        {{-- <div class="card-body">
                            @if (session('status'))
                                <div class="alert alert-success" role="alert">
                                    {{ session('status') }}
                                </div>
                            @endif

                            {{ __('You are logged in!') }}
                        </div> --}}
                    {{-- </div> --}}
                </div>

                <div class=" bg-white rounded shadow table-responsive text-nowrap">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col" >端末ID</th>
                                <th scope="col" >端末区分</th>
                                <th scope="col" >端末名</th>
                                <th scope="col" >シリアル番号</th>
                                <th scope="col" >OS</th>
                                <th scope="col" >OS Ver</th>
                                <th scope="col" >初稼働日</th>
                                <th scope="col" >購入日</th>
                                <th scope="col" >オプション</th>
                                <th scope="col" >不具合</th>
                                <th scope="col" >販売不可</th>
                                <th scope="col" >販売先</th>
                                <th scope="col" >販売日</th>
                                <th scope="col" >ノート</th>
                                <th scope="col" >貸し出し中</th>
                                <th scope="col" >使用ID</th>
                                <th scope="col" >更新日</th>
                                <th scope="col" ></th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- {{$device_info_collection }} --}}
                            @foreach($tablet_info_collection as $device_info)
                                <tr>
                                    <td >{{ $device_info->device_id }}</td>
                                    <td >{{ $device_info->device_type }}</td>
                                    <td >{{ $device_info->device_name }}</td>
                                    <td >{{ $device_info->device_serial }}</td>
                                    <td >{{ $device_info->os }}</td>
                                    <td >{{ $device_info->os_ver }}</td>
                                    <td >{{ $device_info->first_work_date_at }}</td>
                                    <td >{{ $device_info->purchase_date_at }}</td>
                                    <td >{{ $device_info->option }}</td>
                                    {{-- 不具合有無判定 --}}
                                    @if($device_info->defective == 1)
                                        <td ><i class="fas fa-check-circle fa-lg danger-icon" aria-hidden="true"></i><span class="sr-only">該当</span></td>
                                    @else
                                        <td ></td>
                                    @endif
                                    {{-- 販売不可判定 --}}
                                    @if($device_info->not_for_sale == 1)
                                    <td ><i class="fas fa-check-circle fa-lg danger-icon" aria-hidden="true"></i><span class="sr-only">該当</span></td>
                                    @else
                                    <td ></td>
                                    @endif
                                    <td >{{ $device_info->client }}</td>
                                    <td >{{ $device_info->sale_date_at }}</td>
                                    <td >{{ $device_info->note }}</td>
                                    {{-- 貸出中判定 --}}
                                    @if($device_info->lending_now == 1)
                                    <td ><i class="fas fa-check-circle fa-lg success-icon" aria-hidden="true"></i><span class="sr-only">該当</span></td>
                                    @else
                                    <td ></td>
                                    @endif
                                    <td >{{ $device_info->using_user_id }}</td>
                                    <td >{{ $device_info->modified_at }}</td>
                                    <td><button type="button" class="btn btn-outline-dark">詳細 <i class="far fa-edit"></i></button></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            </div>
        </div>
    </div>

@endsection
