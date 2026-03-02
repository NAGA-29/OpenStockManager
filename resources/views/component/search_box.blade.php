    <meta name="csrf-token" content="{{ csrf_token() }}">

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- 貸出先会社名 --}}
    <div class="form-group row">
        <label for="client" class="col-md-4 col-form-label text-md-right">{{ __('貸出先会社名') }}</label>
        <div class="col-md-6">
            <input id="word" type="text" class="form-control @error('client') is-invalid @enderror" name="client" required autofocus>
        </div>
        <button type="button" id='search_client' class="btn btn-secondary">{{ __('検索') }}</button>
    </div>

    <div id="result"></div>
    <table class="table table-hover">
        <thead class="table-dark">
            <tr>
                <th scope="col"></th>
                <th scope="col">企業名</th>
                <th scope="col">URL</th>
                <th scope="col">TEl</th>
                <th scope="col">住所</th>
                <th scope="col">メモ</th>
            </tr>
        </thead>
        <tbody  id="search_table"></tbody>
    </table>

    {{-- ボタン群 --}}
    <div class="form-group row mb-0">
        <div class="col-md-6 offset-md-4">
            <button type="button" id='client_select_btn' class="btn btn-primary">{{ __('選択') }}</button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('閉じる') }}</button>
        </div>
    </div>
{{-- </form> --}}
    <script src="{{ asset('/js/search_for_personnel.js') }}"></script>

