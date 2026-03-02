@extends('layouts.app')

@section('content')

@endsection

{{-- @include('layouts.sidebar') --}}
@section('main_contents')
    <div class="col-md-12 shadow-sm pt-0 pr-0 pb-0 pl-0 mt-2 mb-2">
        <div class="device-bar rounded bg-dark text-white p-3">
            <div class="device-name h3 m-0">
                {{ __('端末貸出手続き') }}
            </div>
        </div>
    </div>

    <div class=" bg-white rounded shadow table-responsive text-nowrap">
        <div class="card">
            <div class="card-header bg-white text-black">{{ __('下記の記入欄を入力してください') }}</div>
            <div class="card-body m-5">
                <form action="{{ route('device.rental.store') }}" method='POST'>
                    @csrf
                    @foreach ($device_info_collection as $device_info)
                        <table class="table table-hover">
                            <tr>
                                <td>{{ __('端末ID') }}</td>
                                <td>{{ $device_info->device_id }}
                                    <input id="deviceIds" type="hidden"
                                        class="form-control @error('deviceIds') is-invalid @enderror" name="deviceIds[]"
                                        value="{{ $device_info->device_id }}" required>
                                </td>
                            </tr>
                            <tr>
                                <td>{{ __('端末区分') }}</td>
                                <td>{{ $device_info->device_type }}
                                </td>
                            </tr>
                            <tr>
                                <td>{{ __('端末名') }}</td>
                                <td>{{ $device_info->device_name }}</td>
                            </tr>
                            <tr>
                                <td>{{ __('端末シリアル') }}</td>
                                <td>{{ $device_info->device_serial }}</td>
                            </tr>
                            <tr>
                                <td>{{ __('OS') }}</td>
                                <td>{{ $device_info->os }}</td>
                            </tr>
                            <tr>
                                <td>{{ __('OS Ver.') }}</td>
                                <td>{{ $device_info->os_ver }}</td>
                            </tr>
                            {{-- <tr>
                                <td>{{ __('顧客コード+連番') }}</td>
                                <td>
                                    <div class="form-group row">
                                        <div class="col-md-10" id="in_use_id">
                                            <input id="in_use_id" type="text"
                                                class="form-control @error('in_use_id') is-invalid @enderror"
                                                name="in_use_id">
                                        </div>
                                    </div>
                                </td>
                            </tr> --}}
                            <tr>
                                <td>{{ __('貸出先企業') }}</td>
                                <td>
                                    <div class="form-group row">
                                        <div class="col-md-10">
                                            <p class="search_result">選択されていません</p>
                                            <input id="client" type="hidden" class="client"
                                                class="form-control @error('client') is-invalid @enderror" name="client"
                                                required autofocus>
                                        </div>
                                    </div>
                                    {{-- 検索モーダル --}}
                                    <button type="button" class="btn btn-outline-dark" data-bs-toggle="modal"
                                        data-bs-target="#ClientSearchModal">
                                        検索
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>{{ __('貸出先担当者') }}</td>
                                <td>
                                    <div class="form-group row">
                                        <div class="col-md-10">
                                            <p class="select_personnel" name='select_personnel'>貸出先企業を先に選択してください</p>
                                            {{-- <input id="personnel" type="text" class="form-control @error('personnel') is-invalid @enderror" name="personnel" required > --}}
                                            {{-- <select id='personnel' name='personnel' >
                                        </select> --}}
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>{{ __('貸出日') }}</td>
                                <td>
                                    <div class="form-group row">
                                        <div class="col-md-10">
                                            <input id="checkout_at" type="date"
                                                class="form-control @error('checkout_at') is-invalid @enderror"
                                                name="checkout_at" required>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>{{ __('返却予定日') }}</td>
                                <td>
                                    <div class="form-group row">
                                        <div class="col-md-10">
                                            <input id="schedule_return_at" type="date"
                                                class="form-control @error('schedule_return_at') is-invalid @enderror"
                                                name="schedule_return_at">
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>{{ __('ノート') }}</td>
                                <td>
                                    <div class="form-group row">
                                        <div class="col-md-12">
                                            <textarea id="note" type="textarea" rows="5" class="form-control @error('note') is-invalid @enderror"
                                                name="note"></textarea>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    @endforeach
                    <div class='text-center m-3'>
                        <button type="submit" class='btn btn-outline-dark mr-3'>登録</button>
                        <button type="reset" class='btn btn-outline-danger mr-3'>リセット</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Lending Modal -->
    <div class="modal fade" id="ClientSearchModal" tabindex="-1" role="dialog" aria-labelledby="clientSearchRentalDetailModalTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <!--  貸出対応判定 -->
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="clientSearchRentalDetailModalTitle">貸出先企業の検索</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @include('component.modal.client_search')
                </div>
            </div>
        </div>
    </div>
    <!-- Modal End-->
@endsection

@section('script_area')
    @vite('resources/js/search.js')
@endsection
