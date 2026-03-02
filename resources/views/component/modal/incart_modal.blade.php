<meta name="csrf-token" content="{{ csrf_token() }}">

<p>選択内容に問題がない場合は、レンタル手続きまたは販売手続きに進んでください。</p>

<div class="text-center m-3">
    <a type="button" href="{{ route('device.rental') }}" class='btn btn-outline-dark mr-3'>{{ __('レンタル手続き') }}</a>
    <a type="button" href="{{ route('device.sale') }}" class='btn btn-outline-dark mr-3'>{{ __('販売手続き') }}</a>
</div>

<div id="result"></div>
<div class="table-scroll">
<table class="table table-hover">
    <thead class="table-dark">
        <tr>
            <th scope="col"></th>
            <th scope="col">{{__("端末ID")}}</th>
            <th scope="col">{{__("端末名")}}</th>
            <th scope="col">{{__("カテゴリー")}}</th>
        </tr>
    </thead>
    <tbody id="in-cart-devices"></tbody>
</table>
</div>

<div class='text-center m-3'>
  <button type="button" id='all-check-btn' class='btn btn-outline-dark mr-3'>{{ __('全て選択') }}</button>
  <button type="button" id='all-remove-btn' class='btn btn-outline-secondary mr-3'>{{ __('全て外す') }}</button>
  <button type="button" id='update-cart-btn' class='btn btn-outline-dark mr-3' data-dismiss="modal">{{ __('更新') }}</button>
</div>
