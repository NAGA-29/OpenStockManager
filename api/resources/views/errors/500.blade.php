@extends('layouts.error')

@section('title', 'OpenStockManager | 500 Internal Server Error')

@section('css')
    {{-- <link rel="stylesheet" href="{{ mix('css/icons_all.css') }}"> --}}
@endsection

@section('content')
    <div id="user_print" class="login-after print_target">
        {{-- header --}}
        @include('layouts.error-navigation')

        <div class="content print_content print_target mgb-set3">
            <h3>500 : システムに問題が発生しました。</h3>
            <br>
            <p>お手数ですが、しばらく時間を置いてからやり直すか、<br>
                このページが表示された下記の情報<br>
                と併せて管理者に問い合わせしてください</p>
            <br>
            Error : 500 <br>
            @php echo date('Y年m月d日 H時i分s秒', time()); @endphp <br>
            <br>
            <br>
            <br>
            <p><a href="{{ url('/') }}">トップページへ</a></p>
        </div>
    </div>
@endsection

@section('js')
@stop
