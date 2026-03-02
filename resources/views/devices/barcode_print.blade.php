<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('バーコード印刷') }} - {{ $device->device_id }}</title>
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('/images/favicon.svg') }}">
    @vite('resources/css/barcode-print.css')
</head>
<body>
    <div class="actions">
        <button type="button" class="btn-print" id="barcode-print-btn">
            <i class="fas fa-print"></i> {{ __('印刷') }}
        </button>
        <a href="{{ route('device.individual', ['device_id' => $device->device_id]) }}" class="btn-back">
            {{ __('端末詳細に戻る') }}
        </a>
    </div>

    <div class="barcode-label">
        <div class="device-info">
            <div class="device-name">{{ $device->device_name }}</div>
            <div class="device-detail">
                {{ $device->device_type }} | S/N: {{ $device->device_serial }}
            </div>
        </div>
        <div data-barcode="{{ $device->device_id }}"></div>
    </div>

    @vite('resources/js/components/barcode-print.ts')
</body>
</html>
