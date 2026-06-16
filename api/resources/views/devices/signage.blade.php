@extends('layouts.app')

@section('content')
@endsection

@section('main_contents')
    <div>

        @include('component.summary_cards', ['title' => __('サイネージ')])

        <div>
            <div class="row device-toolbar">
                <div class="col-lg-6 col-md-6 device-toolbar-left">
                    @include('devices.components.status_legend')
                </div>
                @include('component.search_form', ['hiddenType' => 'SIGN'])
            </div>
            <div class="row device-pagination">
                <div class="col-md-12 col-xl-6">
                    {{ $signage_info_collection->links() }}
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
                        <th scope="col">ノート</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>
                    {{-- {{$device_info_collection }} --}}
                    @foreach ($signage_info_collection as $device_info)
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
                        <td class="text-truncate" style="max-width:200px;">{{ $device_info->note }}</td>
                        <td><button type="button" class="btn btn-outline-dark"
                                onclick="location.href='{{ url('/devices', $device_info->device_id) }}'">
                                <i class="far fa-edit"></i></button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('js')
@endsection
