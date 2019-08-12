<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- CSRF トークン --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@if (! Request::is('/')){{ $title }} | @endif{{ env('APP_NAME') }}</title>

    {{-- CSS --}}
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
    @yield('stylesheet')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
    @yield('javascript')
</head>
<body>
    <div id="app">

        {{-- フラッシュ・メッセージ --}}
        @if (session('my_status'))
			@component('components.flash_message')
			@endcomponent
        @endif

        <main class="py-4">
            @yield('content')
        </main>
    </div>

    @yield('script')
</body>
</html>
