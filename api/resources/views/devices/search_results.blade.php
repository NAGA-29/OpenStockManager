@extends('layouts.app')

@section('content')
@endsection

@section('main_contents')
    <div class="col-md-12 shadow-sm pt-0 pr-0 pb-0 pl-0 mt-2 mb-2">
        <div class="row">
            <div class="col-xl-12">
                <div class="card card-shadow mb-3 mt-3 bg-dark">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-xl-3 col-md-6 col-sm-6">
                                <div class="device-bar rounded bg-dark text-white">
                                    <div class="device-name h3 m-0">
                                        {{ __('検索結果') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- </div> --}}

        <div>
            <div class="row device-toolbar">
                <div class="col-lg-6 col-md-6 device-toolbar-left">
                    <div class="device-status-group">
                        <div class="status-icon"><i class="fas fa-dove fa-lg"></i> : レンタル中</div>
                        <div class="status-icon"><i class="fas fa-yen-sign"></i> : 販売済み</div>
                        <div class="status-icon"><i class="fas fa-images"></i> : 写真アリ</div>
                    </div>
                </div>
                @include('component.search_form', ["searchKeyword" => $search_keywords])
            </div>
            <div class="row device-pagination">
                <div class="col-md-12 col-xl-6">
                    {{ $device_info_collection->appends(request()->query())->links() }}
                </div>
            </div>
        </div>

        <div class=" bg-white rounded shadow table-responsive text-nowrap">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th scope="col"></th>
                        <th scope="col">ステータス</th>
                        <th scope="col">端末ID</th>
                        <th scope="col">端末名</th>
                        <th scope="col">OS</th>
                        <th scope="col">オプション</th>
                        <th scope="col">コンディション</th>
                        <th scope="col">不具合</th>
                        <th scope="col">販売不可</th>
                        <th scope="col">ノート</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($device_info_collection as $device_info)
                        @if ($device_info->not_for_sale === 1 || $device_info->defective === 1)
                            <tr class='table-danger'>
                            @elseif($device_info->sale_id || $device_info->lending_now)
                            <tr class='table-secondary'>
                            @else
                            <tr>
                        @endif

                        <td>
                            <input type="checkbox" class="product-checkbox" id="product-{{ $device_info->device_id }}"
                                aria-label="{{ $device_info->device_id }}を選択"
                                device-name="{{ $device_info->device_name ? $device_info->device_name : 'UnKnown' }}"
                                device-type="{{ $device_info->device_type }}" value="{{ $device_info->device_id }}">
                        </td>

                        {{-- 貸出中判定 --}}
                        <td>
                            @if ($device_info->lending_now)
                                <i class="fas fa-dove fa-lg" aria-hidden="true"></i><span class="sr-only">貸出中</span>
                            @elseif ($device_info->sale_id)
                                <i class="fas fa-yen-sign" aria-hidden="true"></i><span class="sr-only">販売済</span>
                            @endif

                            @if ($device_info->contents->count())
                                <i class="fas fa-images" aria-hidden="true"></i><span class="sr-only">画像あり</span>
                            @endif
                            @if ($device_info->defective === 1)
                                <span class="badge bg-danger">不具合</span>
                            @endif
                            @if ($device_info->not_for_sale === 1)
                                <span class="badge bg-danger">販売不可</span>
                            @endif
                        </td>

                        <td>
                            <a href="{{ route('device.individual', ['device_id' => $device_info->device_id]) }}">
                                {{ $device_info->device_id }}</a>
                        </td>

                        <td>{{ $device_info->device_name }}</td>
                        <td>{{ $device_info->os }}</td>
                        <td>{{ $device_info->option }}</td>
                        <td>{{ $device_info->condition->condition }}</td>
                        {{-- 不具合有無判定 --}}
                        @if ($device_info->defective == 1)
                            <td><i class="fas fa-check-circle fa-lg danger-icon" aria-hidden="true"></i><span class="sr-only">該当</span></td>
                        @else
                            <td></td>
                        @endif
                        {{-- 販売不可判定 --}}
                        @if ($device_info->not_for_sale == 1)
                            <td><i class="fas fa-check-circle fa-lg danger-icon" aria-hidden="true"></i><span class="sr-only">該当</span></td>
                        @else
                            <td></td>
                        @endif
                        <td class="text-truncate" style="max-width:200px;">{{ $device_info->note }}</td>
                        <td><button type="button" class="btn btn-outline-dark"
                                onclick="location.href='{{ url('/devices', $device_info->device_id) }}'">
                                <i class="far fa-edit"></i></button>
                        </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    {{-- @include('layouts.footer') --}}
@endsection
