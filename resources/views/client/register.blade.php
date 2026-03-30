@extends('layouts.app')

{{-- @include('layouts.sidebar') --}}

@section('content')

@endsection


@section('main_contents')
    @if (Session::has('client_register'))
        <div class="alert alert-success" role="alert">
            {{ session('client_register') }}
        </div>
    @endif

    @include('client.components.client')
    @include('client.components.contact')
@endsection
