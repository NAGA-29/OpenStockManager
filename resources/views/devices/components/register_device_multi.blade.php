<div class="col-md-12 shadow-sm p-0 mt-2 mb-2">
  <div class="device-bar d-flex align-items-center justify-content-between rounded bg-dark text-white p-3">
      <div class="device-name h3 m-0">
          {{ __('新規デバイス登録 (複数)') }}
      </div>
      <div>
          <a type="button" class="btn btn-outline-dark" href="{{ route('device.file.register.download') }}">
            一括登録用CSVファイル
          </a>
      </div>
  </div>
</div>

<div class="bg-white rounded shadow table-responsive text-nowrap">
    <div class="card">
        <div class="card-body m-5">
            <form enctype="multipart/form-data" action="{{ route('device.confirm_multi') }}" method='POST' class='h6 font-weight-bold'>
                @csrf
                <div class="mb-3">
                    <p>登録希望の端末を記載したファイルをアップロードしてください。</p>
                    <p>ファイルの解析・登録には時間が掛かります。アップロード後はしばらくお待ちください。</p>
                    <label for="device_register_file" class="form-label">添付ファイル:</label>
                    <input type="file" class="form-control" name="device_register_file" id="device_register_file" accept="text/csv">
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-outline-dark mr-3">アップロード</button>
                    <button type="reset" class="btn btn-outline-danger mr-3">リセット</button>
                </div>
            </form>
        </div>
    </div>
</div>

@section('script_area')
    <script src="https://cdn.jsdelivr.net/npm/bs-custom-file-input/dist/bs-custom-file-input.js"></script>
    <script>
        bsCustomFileInput.init();
    </script>
@endsection
