<meta name="csrf-token" content="{{ csrf_token() }}">

<p class="mb-3">クライアントを検索・選択してください</p>
<p class="mb-4 text-muted">※お探しの企業が見つからない場合は、CRMを確認し、存在しない場合は新規登録を行なってください。</p>

<div class="form-group row align-items-center">
    <label for="word" class="col-md-4 col-form-label text-md-right">{{ __('貸出先会社名') }}</label>
    <div class="col-md-6">
        <input id="word" type="text" class="form-control @error('client') is-invalid @enderror" name="client"
            required autofocus>
    </div>
    <div class="col-md-2">
        <button type="button" id="search_client" class="btn btn-outline-dark w-100">{{ __('検索') }}</button>
    </div>
</div>

<div id="result" class="mt-3"></div>

<div class="table-responsive mt-3">
    <table class="table table-hover">
        <thead class="table-dark">
            <tr>
                <th scope="col"></th>
                <th scope="col">企業名</th>
                <th scope="col">URL</th>
                <th scope="col">Tel</th>
                <th scope="col">住所</th>
            </tr>
        </thead>
        <tbody id="search_table"></tbody>
    </table>
</div>

<div class="text-center mt-4">
    <button type="submit" id="client_select_btn" class="btn btn-outline-dark me-3"
        data-bs-dismiss="modal">{{ __('選択') }}</button>
    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('閉じる') }}</button>
</div>
