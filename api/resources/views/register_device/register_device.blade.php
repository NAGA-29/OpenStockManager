@extends('layouts.app')

@section('css')
    @vite('resources/css/slideshow.css')
@endsection

@section('content')
@endsection

@section('main_contents')
    @if ($errors->any())
        <div class="alert alert-danger m-3">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <ul class="nav nav-tabs nav-pills">
        <li class="nav-item">
            <a href="#contents1" class="nav-link active" data-bs-toggle="tab">単体</a>
        </li>
        <li class="nav-item">
            <a href="#contents2" class="nav-link" data-bs-toggle="tab">複数</a>
        </li>
        <li class="nav-item">
            <a href="#contents3" class="nav-link" data-bs-toggle="tab"><i class="fas fa-wrench"></i></a>
        </li>
    </ul>

    @if (Session::has('client_register'))
        <div class="alert alert-success" role="alert">
            {{ session('client_register') }}
        </div>
    @endif

    <div class="tab-content">
        <div id="contents1" class="tab-pane active">
            @include('devices.components.register_device')
        </div>
        <div id="contents2" class="tab-pane">
            @include('devices.components.register_device_multi')
        </div>
    </div>
@endsection
