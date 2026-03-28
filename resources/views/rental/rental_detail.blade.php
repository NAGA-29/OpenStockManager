@extends('layouts.app')

@section('content')
@endsection

@section('main_contents')
    <div class="col-md-12 shadow-sm pt-0 pr-0 pb-0 pl-0 mt-2 mb-2">
        <div class="device-bar rounded bg-dark text-white p-3">
            <div class="device-name h3 m-0">
                {{ __('レンタル履歴詳細') }}
            </div>
        </div>
    </div>
    {{-- card end --}}
    <div class=" bg-white rounded shadow table-responsive text-nowrap">
        <div class="card">
            <div class="card-header bg-white text-black">{{ __('下記の記入欄を入力してください') }}</div>
            <div class="card-body m-5">
                {{-- 貸出中/返却済 --}}
                @if ($rental_details->all_returned)
                    <h2><span class="badge badge-pill badge-secondary">{{ __('返却済') }}</span></h2>
                @else
                    <h2><span class="badge badge-pill badge-success">{{ __('貸出中') }}</span></h2>
                @endif
                <table class="table table-hover">
                    {{-- レンタルID --}}
                    <tr>
                        <td>{{ __('レンタルID') }}</td>
                        <td>{{ $rental_details->lend_id }}</td>
                    </tr>
                    {{-- 貸出先企業名 --}}
                    <tr>
                        <td>{{ __('貸出先企業名') }}</td>
                        <td>
                            {{ $rental_details->clients->company }}
                            <a class="far fa-arrow-alt-circle-right"
                                href="{{ route('client.details', ['client_id' => $rental_details->clients->client_id]) }}"></a>
                        </td>
                    </tr>
                    {{-- 担当者 --}}
                    <tr>
                        <td>{{ __('担当者') }}</td>
                        <td>
                            {{ $rental_details->personnels->name }}
                            <a class="far fa-arrow-alt-circle-right"
                                href="{{ route('personnel.detail', ['contact_id' => $rental_details->personnels->id]) }}"></a>
                        </td>
                    </tr>
                    {{-- 対応スタッフ --}}
                    <tr>
                        <td>{{ __('対応スタッフ') }}</td>
                        <td>{{ $rental_details->user->name }}</td>
                    </tr>
                    {{-- レンタル端末 --}}
                    <tr>
                        <td>{{ __('端末ID/端末区分/ユーザーID/個別返却日時') }}</td>
                        <td>
                            @foreach ($rental_details->devices as $device)
                                {{ $device->device_id . ' / ' . $device->device_type . ' / ' . $device->using_user_id . ' / ' . $device->pivot->return_at }}
                                <a class="far fa-arrow-alt-circle-right"
                                    href="{{ route('device.individual', ['device_id' => $device->device_id]) }}"></a><br>
                            @endforeach

                            @if ($rental_details->all_returned == 0)
                                <a class="btn btn-outline-danger"
                                    href="{{ route('device.multi_return_device_confirm', ['lend_id' => $rental_details->lend_id]) }}">一斉返却</a>
                            @endif
                        </td>
                    </tr>
                    {{-- 貸出日 --}}
                    <tr>
                        <td>{{ __('貸出日') }}</td>
                        <td>{{ $rental_details->checkout_at->format('Y-m-d') }}</td>
                    </tr>
                    {{-- 返却予定日 --}}
                    <tr>
                        <td>{{ __('返却予定日') }}</td>
                        @if ($rental_details->schedule_return_at == null)
                            <td>{{ __('未設定') }}</td>
                        @else
                            <td>{{ $rental_details->schedule_return_at->format('Y-m-d') }}</td>
                        @endif
                    </tr>
                    {{-- 返却日 --}}
                    <tr>
                        <td>{{ __('返却日') }}</td>
                        <td>{{ $rental_details->return_at }}</td>
                    </tr>
                    {{-- ノート --}}
                    <tr>
                        <td>{{ __('ノート') }}</td>
                        <td>{{ $rental_details->note }}</td>
                    </tr>
                    {{-- 編集日 --}}
                    <tr>
                        <td>{{ __('編集日') }}</td>
                        <td>{{ $rental_details->modified_at }}</td>
                    </tr>
                </table>
                <div>
                    <button type="submit" class='btn btn-outline-dark mr-3' data-bs-toggle="modal"
                        data-bs-target="#rantalHistoryEditModal">編集</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Lending Modal -->
    <div class="modal fade" id="rantalHistoryEditModal" tabindex="-1" role="dialog" aria-labelledby="ModalLongTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ModalLongTitle">レンタル履歴編集</h5>
                    <!-- data-dismiss -> data-bs-dismissに変更 -->
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @include('component.modal.edit_rental_history')
                </div>
            </div>
        </div>
    </div>
    <!-- Modal End-->
@endsection
