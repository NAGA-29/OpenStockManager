{{-- {{ $user_data }} --}}
<form class="profile-group" action="{{ Route('device.rental') }}" method="POST" novalidate='novalidate'>
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

    <div>下記の記入欄を入力してください</div>

    {{-- 端末ID --}}
    <div class="form-group row">
        <label for="device_id" class="col-md-4 col-form-label text-md-right">{{ __('端末ID') }}</label>
        <div class="col-md-6">
            {{ $device_info_collection->device_id }}
            <input id="device_id" type="hidden" class="form-control @error('device_id') is-invalid @enderror"
                name="device_id" value="{{ $device_info_collection->device_id }}" required autofocus>
        </div>
    </div>

    {{-- 端末区分 --}}
    <div class="form-group row">
        <label for="device_type" class="col-md-4 col-form-label text-md-right">{{ __('端末区分') }}</label>
        <div class="col-md-6">
            {{ $device_info_collection->device_type }}
            {{-- <select class="form-control" name='device_type' id="device_type">
                <option value='STB' {{$device_info_collection->device_type == 'STB' ? 'selected':''}}>STB</option>
                <option value='TAB' {{$device_info_collection->device_type == 'TAB' ? 'selected':''}}>タブレット</option>
                <option value='CAM' {{$device_info_collection->device_type == 'CAM' ? 'selected':''}}>カメラ</option>
                <option value='SIGN' {{$device_info_collection->device_type == 'SIGN' ? 'selected':''}}>サイネージ</option>
                <option value='OTH' {{$device_info_collection->device_type == 'OTH' ? 'selected':''}}>その他の機材</option>
            </select> --}}
        </div>
    </div>

    {{-- 端末名 --}}
    <div class="form-group row">
        <label for="device_name" class="col-md-4 col-form-label text-md-right">{{ __('端末名') }}</label>
        <div class="col-md-6">
            {{ $device_info_collection->device_name }}
            {{-- <input id="device_name" type="text" class="form-control" name="device_name" value="{{$device_info_collection->device_name}}" required autocomplete="email"> --}}
        </div>
    </div>

    {{-- 端末シリアル --}}
    <div class="form-group row">
        <label for="device_serial" class="col-md-4 col-form-label text-md-right">{{ __('端末シリアル') }}</label>
        <div class="col-md-6">
            {{ $device_info_collection->device_serial }}
            {{-- <input id="device_serial" type="text" class="form-control" name="device_serial" value="{{$device_info_collection->device_serial}}" required autocomplete="name" autofocus> --}}
        </div>
    </div>

    {{-- OS --}}
    <div class="form-group row">
        <label for="os" class="col-md-4 col-form-label text-md-right">{{ __('OS') }}</label>
        <div class="col-md-6">
            {{ $device_info_collection->os }}
            {{-- <input id="os" type="text"  class="form-control" name="os" value="{{$device_info_collection->os}}" required autocomplete="name" autofocus> --}}
            {{-- <select class="form-control" name='os' id="os">
                <option value='Android' {{$device_info_collection->os == 'Android' ? 'selected':''}}>Android</option>
                <option value='Windows' {{$device_info_collection->os == 'Windows' ? 'selected':''}}>Windows</option>
                <option value='Linux' {{$device_info_collection->os == 'Linux' ? 'selected':''}}>Linux</option>
                <option value='MacOS' {{$device_info_collection->os == 'MacOS' ? 'selected':''}}>MacOS</option>
            </select> --}}
        </div>
    </div>

    {{-- OS Ver. --}}
    <div class="form-group row">
        <label for="os_ver" class="col-md-4 col-form-label text-md-right">{{ __('OS Ver.') }}</label>
        <div class="col-md-6">
            {{ $device_info_collection->os_ver }}
            {{-- <input id="os_ver" type="text" class="form-control @error('os_ver') is-invalid @enderror" name="os_ver" value="{{$device_info_collection->os_ver}}" required autocomplete="name" autofocus> --}}
        </div>
    </div>

    {{-- 初稼働日 --}}
    {{-- <div class="form-group row">
        <label for="first_work_date_at" class="col-md-4 col-form-label text-md-right">{{ __('初稼働日') }}</label>
        <div class="col-md-6">
            <input id="first_work_date_at" type="date" class="form-control @error('first_work_date_at') is-invalid @enderror" name="first_work_date_at" value="{{$date_list['first_work_date_at']}}" required autocomplete="name" autofocus>
        </div>
    </div> --}}

    {{-- 購入日 --}}
    {{-- <div class="form-group row">
        <label for="purchase_date_at" class="col-md-4 col-form-label text-md-right">{{ __('購入日') }}</label>
        <div class="col-md-6">
            <input id="purchase_date_at" type="date" class="form-control @error('purchase_date_at') is-invalid @enderror" name="purchase_date_at" value="{{$date_list['purchase_date_at']}}" required autocomplete="name" autofocus>
        </div>
    </div> --}}

    {{-- オプション --}}
    {{-- <div class="form-group row">
        <label for="option" class="col-md-4 col-form-label text-md-right">{{ __('オプション') }}</label>
        <div class="col-md-6">
            <input id="option" type="text" class="form-control @error('option') is-invalid @enderror" name="option" value="{{$device_info_collection->option}}" required autocomplete="name" autofocus>
        </div>
    </div> --}}

    {{-- 販売先 --}}
    {{-- <div class="form-group row">
        <label for="client" class="col-md-4 col-form-label text-md-right">{{ __('販売先') }}</label>
        <div class="col-md-6">
            <input id="client" type="text" class="form-control @error('client') is-invalid @enderror" name="client" value="{{$device_info_collection->client}}" required autocomplete="name" autofocus>
        </div>
    </div> --}}

    {{-- 販売日 --}}
    {{-- <div class="form-group row">
        <label for="sale_date_at" class="col-md-4 col-form-label text-md-right">{{ __('販売日') }}</label>
        <div class="col-md-6">
            <input id="sale_date_at" type="date" class="form-control @error('sale_date_at') is-invalid @enderror" name="sale_date_at" value="{{$date_list['sale_date_at']}}" required autocomplete="name" autofocus>
        </div>
    </div> --}}

    {{-- 使用ID --}}
    <div class="form-group row">
        <label for="in_use_id" class="col-md-4 col-form-label text-md-right">{{ __('使用ID') }}</label>
        <div class="col-md-6" id="in_use_id">
            {{-- <div>{{$device_info_collection->using_user_id}}</div> --}}
            <input id="in_use_id" type="text" class="form-control @error('in_use_id') is-invalid @enderror"
                name="in_use_id">
        </div>
    </div>

    {{-- 不具合 --}}
    {{-- <div class="form-group row">
        <label for="defective" class="col-md-4 col-form-label text-md-right">{{ __('不具合') }}</label>
        <div class="col-md-6">
            <input id="defective" type="checkbox" class="form-control @error('defective') is-invalid @enderror" name="defective"
                value=1 required autocomplete="defective" autofocus {{$device_info_collection->defective == 1 ? 'checked':''}}>
        </div>
    </div> --}}

    {{-- 販売不可 --}}
    {{-- <div class="form-group row">
        <label for="not_for_sale" class="col-md-4 col-form-label text-md-right">{{ __('販売不可') }}</label>
        <div class="col-md-6">
            <input id="not_for_sale" type="checkbox" class="form-control @error('not_for_sale') is-invalid @enderror" name="not_for_sale"
                value=1 required autocomplete="not_for_sale" autofocus {{$device_info_collection->not_for_sale == 1 ? 'checked':''}}>
        </div>
    </div> --}}

    {{-- 貸出先会社名 --}}
    <div class="form-group row">
        <label for="client" class="col-md-4 col-form-label text-md-right">{{ __('貸出先会社名') }}</label>
        <div class="col-md-6">
            <input id="client" type="text" class="form-control @error('client') is-invalid @enderror"
                name="client" required autofocus>
        </div>
    </div>

    {{-- 貸出先担当者 --}}
    <div class="form-group row">
        <label for="contact" class="col-md-4 col-form-label text-md-right">{{ __('貸出先担当者') }}</label>
        <div class="col-md-6">
            <input id="contact" type="text" class="form-control @error('contact') is-invalid @enderror"
                name="contact" required>
        </div>
    </div>

    {{-- 貸出日 --}}
    <div class="form-group row">
        <label for="checkout_at" class="col-md-4 col-form-label text-md-right">{{ __('貸し出し日') }}</label>
        <div class="col-md-6">
            <input id="checkout_at" type="date" class="form-control @error('checkout_at') is-invalid @enderror"
                name="checkout_at" required>
        </div>
    </div>

    {{-- 返却予定日 --}}
    <div class="form-group row">
        <label for="schedule_return_at" class="col-md-4 col-form-label text-md-right">{{ __('返却予定日') }}</label>
        <div class="col-md-6">
            <input id="schedule_return_at" type="date"
                class="form-control @error('schedule_return_at') is-invalid @enderror" name="schedule_return_at">
        </div>
    </div>

    {{-- ノート --}}
    <div class="form-group row">
        <label for="note" class="col-md-4 col-form-label text-md-right">{{ __('ノート') }}</label>
        <div class="col-md-6">
            <textarea id="note" rows=8 class="form-control @error('note') is-invalid @enderror" name="note"></textarea>
        </div>
    </div>

    {{-- ボタン群 --}}
    <div class="form-group row mb-0">
        <div class="col-md-6 offset-md-4">
            <button type="submit" class="btn btn-primary">{{ __('保存') }}</button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('閉じる') }}</button>
        </div>
    </div>

</form>
