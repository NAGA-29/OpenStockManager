<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Device Manager') }}</title>

    {{-- Favicon --}}
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('/images/favicon.svg') }}">

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.1/css/all.css"
        integrity="sha384-vp86vTRFVJgpjF9jiIGPEEqYqlDwgyBgEF109VFjmqGmIY/Y4HV4d3Gp2irVfcrp" crossorigin="anonymous">

    <!-- Scripts and Styles -->
    @vite(['resources/css/app.css', 'resources/css/page_loading.css', 'resources/css/sidebar.css', 'resources/css/table.css', 'resources/js/app.js'])
    @yield('css')
    @yield('content')
</head>

<body class="antialiased layout-root">
    {{-- ローディング --}}
    <div class="loader" id="loader">
        <span class="circle"></span>
    </div>

    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-dark shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ route('dashboard') }}">
                    <span class="h2 text-blue-500">Open</span><span class="h2 text-white">StockManager</span>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                    aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item me-3">
                                <!-- Cart Button -->
                                <button type="button" class="btn text-white" data-bs-toggle="modal"
                                    data-bs-target="#InCartModal" id="dm-cart-btn">
                                    <i class="fas fa-shopping-cart pr-3"></i>
                                    <span class="badge bg-secondary" id="dm-cart">0</span>
                                </button>
                            </li>

                            <li class="nav-item dropdown">
                                <!-- User Dropdown -->
                                <a id="navbarDropdown" class="text-light nav-link dropdown-toggle" href="#"
                                    role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                    v-pre>
                                    {{ Auth::user()->name }}
                                </a>
                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('profile') }}">
                                        {{ __('マイページ') }}
                                    </a>
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                        onclick="event.preventDefault();
                                                  document.getElementById('logout-form').submit();">
                                        {{ __('ログアウト') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>


        <div class="main-content">
            <div class="container main-container" style="max-width: 1920px;">
                <div class='row'>
                    {{-- サイドメニュー --}}
                    <div class="col-lg-2 p-0">
                        @include('layouts.sidebar')
                    </div>
                    {{-- コンテンツ --}}
                    <div class="col-lg-10 float-right pt-0 pr-3 pb-0 pl-3 main-column">
                        @yield('main_contents')
                        @include('layouts.footer')
                    </div>
                </div>
            </div>
        </div>

        <!-- Cart Modal -->
        <div class="modal fade" id="InCartModal" tabindex="-1" role="dialog" aria-labelledby="inCartModalTitle"
            aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="inCartModalTitle">カートの内容</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @include('component.modal.incart_modal')
                    </div>
                </div>
            </div>
        </div>
        <!-- Cart Modal End-->

        {{-- script --}}
        @include('component.alert')
        @section('script_area')
        @show

        @yield('js')
        @vite(['resources/js/ui/loading/loading.ts', 'resources/js/components/cart.ts'])
</body>

</html>
