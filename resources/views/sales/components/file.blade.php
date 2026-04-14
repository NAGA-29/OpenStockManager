<div class="card">
    <div class="card-body m-5">
        <form enctype="multipart/form-data" action={{ route('device.multi_sales_csv_upload') }} method='POST'>
            @csrf
            <div class='device-volume mb-5'>
                <span>登録用CSVファイル : </span>
                <a href="{{ route('device.multi_csv_download') }}" class="btn btn-outline-dark"
                    type="button">ダウンロード</a>
            </div>
            <table class="table table-hover">
                <tr>
                    <td>{{ __('販売先企業') }}</td>
                    <td>
                        <div class="form-group row">
                            <div class="col-md-10">
                                <p class="search_result">選択されていません</p>
                                <input id="client" class="client" type="hidden"
                                    class="form-control @error('client_id') is-invalid @enderror" name="client_id" required
                                    autofocus value={{ old('client_id') }}>
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
                    <td>{{ __('販売先担当者') }}</td>
                    <td>
                        <div class="form-group row">
                            <div class="col-md-10">
                                <p class="select_contact" name='select_contact'>貸出先企業を先に選択してください</p>
                            </div>
                        </div>
                    </td>
                </tr> 
                <tr>
                    <td>{{ __('販売日') }}</td>
                    <td>
                        <div class="form-group row">
                            <div class="col-md-10">
                                <input id="sale_date_at" type="date"
                                    class="form-control @error('sale_date_at') is-invalid @enderror" name="sale_date_at"
                                    value="{{ old('sale_date_at') }}"
                                    required>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>{{ __('販売ノート') }}</td>
                    <td>
                        <div class="form-group row">
                            <div class="col-md-12">
                                <textarea id="note" type="textarea" rows="5" class="form-control @error('note') is-invalid @enderror"
                                    name="note" value="{{ old('note') }}"></textarea>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>

            {{-- csvアップロード --}}
            <div class="custom-file form-group">
                <label class="custom-file-label" for="csv_file" data-browse="参照">添付ファイル:</label>
                <input type="file" class="form-control" name="csv_file" id="csv_file" accept="text/csv">
            </div>

            <div class='text-center m-3'>
                <button type="submit" class='btn btn-outline-dark mr-3'>アップロード</button>
                <button type="reset" class='btn btn-outline-danger mr-3'>リセット</button>
            </div>
        </form>
    </div>
</div>
