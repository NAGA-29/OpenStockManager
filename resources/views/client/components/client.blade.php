<div class="col-md-12 shadow-sm pt-0 pr-0 pb-0 pl-0 mt-2 mb-2">
    <div class="device-bar rounded bg-dark text-white p-3">
        <div class="device-name h3 m-0">
            {{ __('新規クライアント登録') }}
        </div>
    </div>
</div>

@if (Session::has('client_register'))
    <div class="alert alert-success" role="alert">
        {{ session('client_register') }}
    </div>
@endif
<div class=" bg-white rounded shadow table-responsive text-nowrap">
    <div class="card">
        {{-- <div class="card-header bg-dark text-white">{{ __('STB') }}</div> --}}
        <div class="card-body m-5">
            <form action={{ Route('client.register') }} method='POST' class='h6 font-weight-bold'>
                @csrf
                <div class="form-group">
                    <label for='company'>企業名</label>
                    <input class="form-control" id ='company' type='text' name='company'
                        value= "{{ old('company') }}">
                </div>
                <div class="form-group">
                    <label for='url'>会社URL</label>
                    <input class="form-control" id='url' type='text' name='url'
                        value= "{{ old('url') }}">
                </div>
                <div class="form-group">
                    <label for='tel'>電話番号</label>
                    <input class="form-control" id='tel' type='tel' name='tel'
                        value="{{ old('tel') }}">
                </div>
                <div class="form-group">
                    <label for='street_address'>住所</label>
                    <input class="form-control" id='street_address' type='text' name='street_address'
                        value = "{{ old('street_address') }}">
                </div>
                <div class="form-group">
                    <label for='note'>ノート</label>
                    <textarea class="form-control" id='note' type='text' name='note' rows="5">{{ old('note') }}</textarea>
                </div>

                <div class='text-center'>
                    <button type="submit" class='btn btn-outline-dark mr-3'>登録</button>
                    <button type="reset" class='btn btn-outline-danger mr-3'>リセット</button>
                </div>
            </form>
        </div>
    </div>
</div>
