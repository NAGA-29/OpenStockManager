{{-- {{ $user_data }} --}}
<form class="profile-group" action="{{Route('device.return')}}" method="POST" novalidate='novalidate'>
    {{-- {{var_dump(Auth::user())}} --}}
    @csrf

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- レンタルID --}}
    <div class="form-group row">
        <label for="device_id" class="col-md-4 col-form-label text-md-right">{{ __('レンタルID') }}</label>
        <div class="col-md-6">
            <div>{{$device_info_collection->lending_now}}</div>
            <input id="lend_id" type="hidden" class="form-control @error('lend_id') is-invalid @enderror" name="lend_id" value="{{$device_info_collection->lending_now}}">
        </div>
    </div>

    {{-- 端末ID --}}
    <div class="form-group row">
        <label for="device_id" class="col-md-4 col-form-label text-md-right">{{ __('端末ID') }}</label>
        <div class="col-md-6">
            <div>{{$device_info_collection->device_id}}</div>
            <input id="device_id" type="hidden" class="form-control @error('device_id') is-invalid @enderror" name="device_id" value="{{$device_info_collection->device_id}}">
        </div>
    </div>

    {{-- 使用ID --}}
    {{-- <div class="form-group row">
        <label for="using_user_id" class="col-md-4 col-form-label text-md-right">{{ __('使用ID') }}</label>
        <div class="col-md-6">
            <div>{{$device_info_collection->using_user_id}}</div>
            <input id="using_user_id" type="hidden" class="form-control @error('using_user_id') is-invalid @enderror" name="using_user_id" value="{{$device_info_collection->using_user_id}}">
        </div>
    </div> --}}

    {{-- 貸出先担当者 --}}
    {{-- <div class="form-group row">
        <label for="contact" class="col-md-4 col-form-label text-md-right">{{ __('貸出先担当者') }}</label>
        <div class="col-md-6">
            <div></div>
            <input id="contact" type="text" class="form-control @error('contact') is-invalid @enderror" name="contact" required >
        </div>
    </div> --}}

    {{-- 貸出日 --}}
    {{-- <div class="form-group row">
        <label for="checkout_at" class="col-md-4 col-form-label text-md-right">{{ __('貸し出し日') }}</label>
        <div class="col-md-6">
            <input id="checkout_at" type="date" class="form-control @error('checkout_at') is-invalid @enderror" name="checkout_at"  required>
        </div>
    </div> --}}

    {{-- 返却予定日 --}}
    {{-- <div class="form-group row">
        <label for="schedule_return_at" class="col-md-4 col-form-label text-md-right">{{ __('返却予定日') }}</label>
        <div class="col-md-6">
            <input id="schedule_return_at" type="date" class="form-control @error('schedule_return_at') is-invalid @enderror" name="schedule_return_at">
        </div>
    </div> --}}

    {{-- 返却日 --}}
    <div class="form-group row">
        <label for="return_at" class="col-md-4 col-form-label text-md-right">{{ __('返却日') }}</label>
        <div class="col-md-6">
            <input id="return_at" type="date" class="form-control @error('return_at') is-invalid @enderror" name="return_at">
        </div>
    </div>

    {{-- 不具合 --}}
    <div class="form-group row">
        <label for="defective" class="col-md-4 col-form-label text-md-right">{{ __('不具合') }}</label>
        <div class="col-md-6">
            <input id="defective" type="checkbox" class="form-control @error('defective') is-invalid @enderror" name="defective"
                value=1 required autocomplete="defective" autofocus {{$device_info_collection->defective == 1 ? 'checked':''}}>
        </div>
    </div>

    {{-- 販売不可 --}}
    <div class="form-group row">
        <label for="not_for_sale" class="col-md-4 col-form-label text-md-right">{{ __('販売不可') }}</label>
        <div class="col-md-6">
            <input id="not_for_sale" type="checkbox" class="form-control @error('not_for_sale') is-invalid @enderror" name="not_for_sale"
                value=1 required autocomplete="not_for_sale" autofocus {{$device_info_collection->not_for_sale == 1 ? 'checked':''}}>
        </div>
    </div>

    {{-- ノート --}}
    <div class="form-group row">
        <label for="note" class="col-md-4 col-form-label text-md-right">{{ __('ノート') }}</label>
        <div class="col-md-6">
            <textarea id="note" type="text" class="form-control @error('note') is-invalid @enderror" name="note">{{$device_info_collection->note}}</textarea>
        </div>
    </div>

    {{-- ボタン類 --}}
    <div class="form-group row mb-0">
        <div class="col-md-6 offset-md-4">
            <button type="submit" class="btn btn-primary">{{ __('保存') }}</button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('閉じる') }}</button>
        </div>
    </div>

    {{-- <a href="{{ route('edit.user') }}">編集</a> --}}
</form>
