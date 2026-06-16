@extends('layouts.error')

@section('title', 'OpenStockManager | 503 Service Unavailable')

@section('css')
    {{-- <link rel="stylesheet" href="{{ mix('css/icons_all.css') }}"> --}}
@endsection

@section('content')
    <div id="user_print" class="login-after print_target">
        {{-- header --}}
        @include('layouts.error-navigation')

        <div class="content print_content print_target mgb-set3">
            <h3>503 : システムに問題が発生しました。</h3>
            <br>
            <p>アクセスが集中しているため、しばらく時間を置いてからやり直してください。</p>
            <br>
            <p><a href="{{ url('/') }}">トップページへ</a></p>
        </div>
    </div>
@endsection

@section('js')
@stop
