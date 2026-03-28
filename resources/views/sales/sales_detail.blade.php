@extends('layouts.app')

@section('content')

@endsection

@section('main_contents')
    <div class="col-md-12 shadow-sm pt-0 pr-0 pb-0 pl-0 mt-2 mb-2">
        <div class="device-bar rounded bg-dark text-white p-3">
            <div class="device-name h3 m-0">
                {{ __('販売履歴詳細') }}
            </div>
        </div>
    </div>

    <div class=" bg-white rounded shadow table-responsive text-nowrap">
        <div class="card">
            <div class="card-header bg-white text-black">{{ __('下記の記入欄を入力してください') }}</div>
            <div class="card-body m-5">
                {{-- 貸出中/返却済 --}}
                <h2><span class="badge badge-pill badge-secondary">{{ __('販売済') }}</span></h2>
                <table class="table table-hover">
                    <tr>
                        <td>{{ __('販売ID') }}</td>
                        <td>{{ $sales_details->sale_id }}</td>
                    </tr>
                    <tr>
                        <td>{{ __('販売先企業名') }}</td>
                        <td>
                            {{ $sales_details->clients->company }}
                            <a class="far fa-arrow-alt-circle-right"
                                href="{{ route('client.details', ['client_id' => $sales_details->clients->client_id]) }}"></a>
                        </td>
                    </tr>
                    <tr>
                        <td>{{ __('担当者') }}</td>
                        <td>
                            {{ $sales_details->personnels->name }}
                            <a class="far fa-arrow-alt-circle-right"
                                href="{{ route('personnel.detail', ['contact_id' => $sales_details->personnels->id]) }}"></a>
                        </td>
                    </tr>
                    <tr>
                        <td>{{ __('対応スタッフ') }}</td>
                        <td>{{ $sales_details->user->name }}</td>
                    </tr>
                    <tr>
                        <td>{{ __('端末ID/端末区分') }}</td>
                        <td>
                            @foreach ($collection as $device)
                                {{ $device['device_id'] . ' / ' . $device['device_type'] }}
                                <a class="far fa-arrow-alt-circle-right"
                                    href="{{ route('device.individual', ['device_id' => $device['device_id']]) }}"></a><br>
                            @endforeach
                        </td>
                    </tr>
                    <tr>
                        <td>{{ __('販売日') }}</td>
                        <td>{{ $sales_details->sale_date_at }}</td>
                    </tr>
                    <tr>
                        <td>{{ __('ノート') }}</td>
                        <td>{{ $sales_details->note }}</td>
                    </tr>
                    <tr>
                        <td>{{ __('編集日') }}</td>
                        <td>{{ $sales_details->modified_at }}</td>
                    </tr>
                </table>
                <div>
                    <button type="submit" class='btn btn-outline-dark mr-3' data-bs-toggle="modal"
                        data-bs-target="#SaleHistoryEditModal">編集</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Lending Modal -->
    <div class="modal fade" id="SaleHistoryEditModal" tabindex="-1" role="dialog" aria-labelledby="ModalLongTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <!--  販売履歴編集 -->
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ModalLongTitle">端末販売履歴編集</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @include('component.modal.edit_sale_history')
                </div>
            </div>
        </div>
    </div>
    <!-- Modal End-->
@endsection
