@extends('layouts.app')

@section('content')
@endsection

@section('main_contents')
    @if ($errors->any())
        <div class="alert alert-danger m-3">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="col-md-12 shadow-sm pt-0 pr-0 pb-0 pl-0 mt-2 mb-2">
        <div class="device-bar rounded bg-dark text-white p-3">
            <div class="device-name h3 m-0">
                {{ __('端末レンタル手続き') }}
            </div>
        </div>
    </div>

    <div class="bg-white rounded shadow table-responsive text-nowrap">
        {{-- tab --}}
        <ul class="nav nav-tabs nav-pills">
            <li class="nav-item">
                <a href="#contents1" class="nav-link active" data-bs-toggle="tab">カート式</a>
            </li>
            <li class="nav-item">
                <a href="#contents2" class="nav-link" data-bs-toggle="tab">ファイル式</a>
            </li>
        </ul>
        {{-- tab-end --}}
        <div class="tab-content">
            <div id="contents1" class="tab-pane active">
                @include('rental.components.cart')
            </div>
            <div id="contents2" class="tab-pane">
                @include('rental.components.file')
            </div>
        </div>
    </div>

    <!-- Lending Modal -->
    <div class="modal fade" id="ClientSearchModal" tabindex="-1" role="dialog" aria-labelledby="clientSearchRentalModalTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <!--  貸出対応判定 -->
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="clientSearchRentalModalTitle">貸出先企業の検索</h5>
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
    <script src="https://cdn.jsdelivr.net/npm/bs-custom-file-input/dist/bs-custom-file-input.js"></script>
    <script>
        bsCustomFileInput.init();
    </script>
    @vite('resources/js/search.js')
@endsection
