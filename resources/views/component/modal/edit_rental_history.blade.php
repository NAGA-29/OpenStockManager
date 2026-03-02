<form class="profile-group" action="{{ route('rental.edit') }}" method="POST" novalidate='novalidate'>
    @csrf
    <input name="lend_id" type="hidden" value="{{ $rental_details->lend_id }}">

    @if ($errors->any())
        <div class="alert alert-danger mb-4">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="gap-3">
        {{-- 貸出日 --}}
        <div class="row mb-3">
            <label for="checkout_at" class="col-md-4 col-form-label text-md-end">{{ __('貸出日') }}</label>
            <div class="col-md-6">
                <input id="checkout_at" type="date" class="form-control @error('checkout_at') is-invalid @enderror"
                    name="checkout_at" value="{{ $rental_details->checkout_at->format('Y-m-d') }}" required>
            </div>
        </div>

        {{-- 返却予定日 --}}
        <div class="row mb-3">
            <label for="schedule_return_at" class="col-md-4 col-form-label text-md-end">{{ __('返却予定日') }}</label>
            <div class="col-md-6">
                <input id="schedule_return_at" type="date"
                    class="form-control @error('schedule_return_at') is-invalid @enderror" name="schedule_return_at"
                    value="{{ $rental_details->schedule_return_at ? $rental_details->schedule_return_at->format('Y-m-d') : '' }}">
            </div>
        </div>

        {{-- ノート --}}
        <div class="row mb-3">
            <label for="note" class="col-md-4 col-form-label text-md-end">{{ __('ノート') }}</label>
            <div class="col-md-6">
                <textarea id="note" rows="8" class="form-control @error('note') is-invalid @enderror" name="note">{{ $rental_details->note }}</textarea>
            </div>
        </div>

        {{-- ボタン類 --}}
        <div class="row mb-3">
            <div class="col-md-6 offset-md-4">
                <button type="submit" class="btn btn-outline-dark">{{ __('保存') }}</button>
                <button type="button" class="btn btn-outline-secondary"
                    data-bs-dismiss="modal">{{ __('閉じる') }}</button>
            </div>
        </div>
    </div>
</form>
