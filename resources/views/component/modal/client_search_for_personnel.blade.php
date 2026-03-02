{{-- {{ $user_data }} --}}
{{-- <form class="profile-group" action="{{Route('device.search.client')}}" method="POST" novalidate='novalidate'> --}}
    {{-- {{var_dump(Auth::user())}} --}}
    {{-- @csrf --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <p>企業を選択してください</p>
    <p>※お探しの企業が見つからない場合は、CRMを確認し、存在しない場合は新規登録を行なってください。</p>

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

    {{-- <table>
        <tr>
            <th></th>
            <th>企業名</th>
            <th>URL</th>
            <th>TEL</th>
            <th>住所</th>
            <th>ノート</th>
        </tr>
        <tbody>
            @foreach ($clients as $client)
            <tr>
                <td><input type="radio" value={{$client->client_id .'@'. $client->company}} name='client_id' id={{$client->client_id}} /></td>
                <td><label for={{$client->client_id}}>{{$client->company}}</label></td>
                <td><label for={{$client->client_id}}>{{$client->url}}</label></td>
                <td><label for={{$client->client_id}}>{{$client->tel}}</label></td>
                <td><label for={{$client->client_id}}>{{$client->street_address}}</label></td>
                <td><label for={{$client->client_id}}>{{$client->note}}</label></td>
            </tr>
            @endforeach
        </tbody>
    </table> --}}


    {{-- ボタン群 --}}
    <div class="form-group row mb-0">
        <div class="col-md-6 offset-md-4">
            <button type="button" id='client_select_btn' class="btn btn-primary">{{ __('選択') }}</button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('閉じる') }}</button>
        </div>
    </div>
{{-- </form> --}}
    <script src="{{ asset('/js/search_for_personnel.js') }}"></script>
    {{-- <a href="{{ route('edit.user') }}">編集</a> --}}

