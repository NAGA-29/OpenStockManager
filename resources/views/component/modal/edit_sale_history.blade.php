<form class="profile-group" action="{{ route('sales.history.edit') }}" method="POST" novalidate='novalidate'>
    @csrf
    <input name="sale_id" type="hidden" value="{{ $sales_details->sale_id }}">

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="gap-3">
        {{--　販売日 --}}
        <div class="row mb-3">
            <label for="sale_date_at" class="col-md-4 col-form-label text-md-right">{{ __('販売日') }}</label>
            <div class="col-md-6">
                <input id="sale_date_at" type="date" class="form-control @error('sale_date_at') is-invalid @enderror"
                    name="sale_date_at" value="{{ $sales_details->sale_date_at->format('Y-m-d') }}" required
                    autocomplete="sale_date_at">
            </div>
        </div>

        {{-- ノート --}}
        <div class="row mb-3">
            <label for="note" class="col-md-4 col-form-label text-md-right">{{ __('ノート') }}</label>
            <div class="col-md-6">
                <textarea id="note" rows=8 class="form-control @error('note') is-invalid @enderror" name="note"
                    utocomplete="note">{{ $sales_details->note }}</textarea>
            </div>
        </div>

        {{-- ボタン類 --}}
        <div class="row mb-3">
            <div class="col-md-6 offset-md-4">
                {{-- <a type="button" class="btn btn-outline-secondary">{{ __('返品') }}</a> --}}
                <button type="submit" class="btn btn-outline-dark">{{ __('保存') }}</button>
                <button type="button" class="btn btn-outline-secondary"
                    data-bs-dismiss="modal">{{ __('閉じる') }}</button>
            </div>
        </div>
    </div>
</form>
