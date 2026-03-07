<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Device Manager'))</title>

    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('/images/favicon.svg') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @yield('css')
</head>

<body class="antialiased layout-root">
    @yield('content')
    @yield('js')
</body>

</html>