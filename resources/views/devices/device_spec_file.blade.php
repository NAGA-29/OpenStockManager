@extends('layouts.app')

{{-- @include('layouts.sidebar') --}}

@section('content')
@endsection

@section('main_contents')
    <div class="col-md-12 shadow-sm p-0 mt-2 mb-2">
        <div class="device-bar d-flex align-items-center justify-content-between rounded bg-dark text-white p-3">
            <div class="device-name h3 m-0">
                {{ __('端末スペック') }}
            </div>
            <div>
                <a type="button" id="synchronize-button" class="btn btn-outline-dark"
                    href="{{ route('device.file.spec.download') }}">
                    端末スペックExcelファイル
                </a>
            </div>
        </div>
    </div>
    <div class=" bg-white rounded shadow table-responsive text-nowrap">
        <div class="card">
            <div class="card-body m-5">
                <form enctype="multipart/form-data" action={{ Route('device.file.spec.upload') }} method='POST'
                    class='h6 font-weight-bold'>
                    @csrf
                    <div class="form-group">
                        <p>スペックを記載したエクセルファイルをアップロードしてください。</p>
                        <p>※以前のファイルを上書きしてしまいます。戻すことはできません。注意してください。</p>
                        <div class="custom-file">
                            <label class="custom-file-label" for="spec_file" data-browse="参照">添付ファイル:</label>
                            <input type="file" class="form-control" name="spec_file" id="spec_file"
                                accept="application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
                        </div>
                    </div>
                    <div class='text-center'>
                        <button type="submit" class='btn btn-outline-dark mr-3'>アップロード</button>
                        <button type="reset" class='btn btn-outline-danger mr-3'>リセット</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script_area')
    <script src="https://cdn.jsdelivr.net/npm/bs-custom-file-input/dist/bs-custom-file-input.js"></script>
    <script>
        bsCustomFileInput.init();
    </script>
@endsection
