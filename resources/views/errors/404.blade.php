@extends('layouts.error')

@section('title', 'Device Manager | 404 Not Found')

@section('css')
    {{-- <link rel="stylesheet" href="{{ mix('css/icons_all.css') }}"> --}}
@endsection

@section('content')
    <div id="user_print" class="login-after print_target">
        {{-- header --}}
        @include('layouts.error-navigation')

        <div class="content print_content print_target mgb-set3">
            <h3>お探しのページが見つかりません。</h3>
            <br>
            <p>お探しのページは削除されたか、URLが変更された可能性があります。</p>
            <p>お手数ですが、<a href="{{ url('/') }}">トップページ</a>から再度お探しください。</p>
        </div>
    </div>
@endsection

@section('js')
@stop
