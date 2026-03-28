@extends('layouts.app')

@section('content')
@endsection

@section('css')
    @vite('resources/css/slideshow.css')
@endsection

@section('main_contents')
    <div class="col-md-12 shadow-sm pt-0 pr-0 pb-0 pl-0 mt-2 mb-2">
        <div class="device-bar rounded bg-dark text-white p-3">
            <div class="device-name h3 m-0">
                {{ __('端末詳細情報') }}
            </div>
        </div>
    </div>

    <div class="flex justify-end gap-2 mb-2">
        <a class="btn btn-outline-dark"
            href="{{ route('device.barcode', ['device_id' => $device_info_collection->device_id]) }}" target="_blank">
            <i class="fas fa-barcode"></i> バーコード印刷
        </a>
        <button type="button" class="btn btn-outline-dark" data-bs-toggle="modal" data-bs-target="#EditModal">
            編集
        </button>
        {{-- sale値あり --}}
        @if ($device_info_collection->sale_id)
            {{-- pass --}}
            {{-- sale値 & rental値 なし --}}
        @elseif (!$device_info_collection->sale_id && !$device_info_collection->lending_now)
            <a class="btn btn-outline-dark" href="{{ url('/device/rental', $device_info_collection->device_id) }}"
                role="button">貸出</a>
            <a class="btn btn-outline-dark" href="{{ url('/device/sales', $device_info_collection->device_id) }}"
                role="button">販売</a>
            {{-- sale値なし & rental値あり --}}
        @elseif (!$device_info_collection->sale_id && $device_info_collection->lending_now)
            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#ReturnModal">
                返却
            </button>
        @endif
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body m-5">
                    <div class="row pb-5">
                        @if ($device_info_collection->contents->count() > 0)
                            <div class="slideshow-container">
                                @foreach ($device_info_collection->contents as $device_image)
                                    <div class="slide">
                                        <img src="{{ asset($device_image->path) }}" class="img-fluid" style="width:200px;"
                                            alt="{{ $device_image->original_name }}">
                                    </div>
                                @endforeach

                                <a class="prev" onclick="changeSlide(-1)">&#10094;</a>
                                <a class="next" onclick="changeSlide(1)">&#10095;</a>
                            </div>
                        @else
                            <div class="col-md-12 text-center">
                                <p>No Image</p>
                                <img src="{{ asset('images/no_image.png') }}" class="img-fluid" alt="No Image">
                            </div>
                        @endif
                    </div>

                    @if ($device_info_collection->sale_id)
                        <h2><span class="badge rounded-pill text-bg-danger text-white">販売済</span></h2>
                    @elseif ($device_info_collection->lending_now)
                        <h2><span class="badge rounded-pill text-bg-success text-white">貸出中</span></h2>
                    @endif
                    <table class="table table-hover">
                        <tr>
                            <td>端末ID</td>
                            <td>{{ $device_info_collection->device_id }}</td>
                        </tr>
                        <tr>
                            <td>端末区分</td>
                            <td>{{ $device_info_collection->device_type }}</td>
                        </tr>
                        <tr>
                            <td>端末名</td>
                            <td>{{ $device_info_collection->device_name }}</td>
                        </tr>
                        <tr>
                            <td>端末シリアル</td>
                            <td>{{ $device_info_collection->device_serial }}</td>
                        </tr>
                        {{-- カスタムフィールド表示 --}}
                        @foreach ($customFieldDefs as $fieldDef)
                            <tr>
                                <td>{{ $fieldDef->label }}</td>
                                <td>
                                    @php
                                        $cfVal = $device_info_collection->custom_fields[$fieldDef->field_key] ?? null;
                                    @endphp
                                    @if ($fieldDef->field_type === 'boolean')
                                        @if ($cfVal)
                                            <i class="fas fa-check text-success"></i>
                                        @else
                                            —
                                        @endif
                                    @elseif ($fieldDef->field_type === 'select' && $fieldDef->options)
                                        @php
                                            $optLabel =
                                                collect($fieldDef->options)->firstWhere('value', $cfVal)['label'] ??
                                                $cfVal;
                                        @endphp
                                        {{ $optLabel }}
                                    @else
                                        {{ $cfVal }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        @if ($device_info_collection->first_work_date_at)
                            <tr>
                                <td>初稼働日</td>
                                <td>{{ $device_info_collection->first_work_date_at->format('Y-m-d') }}</td>
                            </tr>
                        @else
                            <tr>
                                <td>初稼働日</td>
                                <td></td>
                            </tr>
                        @endif

                        @if ($device_info_collection->purchase_date_at)
                            <tr>
                                <td>購入日</td>
                                <td>{{ $device_info_collection->purchase_date_at->format('Y-m-d') }}</td>
                            </tr>
                        @else
                            <tr>
                                <td>購入日</td>
                                <td></td>
                            </tr>
                        @endif

                        <tr>
                            <td>オプション</td>
                            <td>{{ $device_info_collection->option }}</td>
                        </tr>
                        <tr>
                            <td>使用ID</td>
                            <td>{{ $device_info_collection->using_user_id }}</td>
                        </tr>

                        <tr>
                            <td>コンディション</td>
                            <td>{{ $device_info_collection->condition->condition }}</td>
                        </tr>

                        {{-- 不具合有無判定 --}}
                        @if ($device_info_collection->defective == 1)
                            <tr class='table-danger'>
                                <td>不具合</td>
                                <td><i class="fas fa-check-circle fa-lg danger-icon" aria-hidden="true"></i><span
                                        class="sr-only">該当</span></td>
                            @else
                            <tr>
                                <td>不具合</td>
                                <td></td>
                        @endif
                        </tr>
                        {{-- 販売不可判定 --}}
                        @if ($device_info_collection->not_for_sale == 1)
                            <tr class='table-danger'>
                                <td>販売不可</td>
                                <td><i class="fas fa-check-circle fa-lg danger-icon" aria-hidden="true"></i><span
                                        class="sr-only">該当</span></td>
                            @else
                            <tr>
                                <td>販売不可</td>
                                <td></td>
                        @endif
                        </tr>
                        {{-- 貸出中判定 --}}
                        @if ($device_info_collection->lending_now !== '')
                            <tr>
                                <td>貸出中</td>
                                <td><i class="fas fa-check-circle fa-lg success-icon" aria-hidden="true"></i><span
                                        class="sr-only">該当</span></td>
                            @else
                            <tr>
                                <td>貸出中</td>
                                <td></td>
                        @endif
                        </tr>
                        <tr>
                            <td>ノート</td>
                            <td>{{ $device_info_collection->note }}</td>
                        </tr>
                        <tr>
                            <td>更新日</td>
                            <td>{{ $device_info_collection->modified_at }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="col-md-12 shadow-sm pt-0 pr-0 pb-0 pl-0 mt-2 mb-2">
                <div class="device-bar rounded bg-dark text-white p-3">
                    <div class="device-name h3 m-0">
                        {{ __('貸出履歴') }}
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body m-5">
                    <table class="table table-hover">
                        <tr>
                            <th>レンタルID</th>
                            <th>貸出先</th>
                            <th>貸出日</th>
                            <th></th>
                        </tr>
                        @foreach ($device_info_collection->rental_hists as $rHis)
                            <tr>
                                <td>{{ $rHis->lend_id }}</td>
                                <td>{{ $rHis->clients->company }}</td>
                                <td>{{ $rHis->checkout_at->format('Y-m-d') }}</td>
                                <td>
                                    <button type="button" class="btn btn-outline-dark"
                                        onclick="location.href='{{ route('rental.rental_detail', ['id' => $rHis->lend_id]) }}'">詳細</button>
                                </td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>

            <div class="col-md-12 shadow-sm pt-0 pr-0 pb-0 pl-0 mt-2 mb-2">
                <div class="device-bar rounded bg-dark text-white p-3">
                    <div class="device-name h3 m-0">
                        {{ __('販売履歴') }}
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body m-5">
                    <table class="table table-hover">
                        <tr>
                            <th>セールID</th>
                            <th>販売先</th>
                            <th>販売日</th>
                            <th></th>
                        </tr>
                        @foreach ($device_info_collection->sale_hists as $sHis)
                            <tr>
                                <td>{{ $sHis->sale_id }}</td>
                                <td>{{ $sHis->clients->company }}</td>
                                <td>{{ $sHis->sale_date_at->format('Y-m-d') }}</td>
                                <td>
                                    <button type="button" class="btn btn-outline-dark"
                                        onclick="location.href='{{ route('sales.sales_detail', ['id' => $sHis->sale_id]) }}'">詳細</button>
                                </td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="EditModal" tabindex="-1" role="dialog" aria-labelledby="editDeviceModalTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editDeviceModalTitle">デバイス情報の編集</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @include('component.modal.edit_device_info')
                </div>
            </div>
        </div>
    </div>
    <!-- Modal End-->

    <!-- checkout Modal -->
    <div class="modal fade" id="CheckoutModal" tabindex="-1" role="dialog" aria-labelledby="checkoutDeviceModalTitle"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="checkoutDeviceModalTitle">端末の貸出</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @include('component.modal.checkout')
                </div>
            </div>
        </div>
    </div>
    <!-- Modal End-->

    <!-- return Modal -->
    <div class="modal fade" id="ReturnModal" tabindex="-1" role="dialog" aria-labelledby="returnDeviceModalTitle"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="returnDeviceModalTitle">端末の返却</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @include('component.modal.return_device')
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    @vite(['resources/js/components/slideshow.ts', 'resources/js/components/gallery.ts'])
@endsection
