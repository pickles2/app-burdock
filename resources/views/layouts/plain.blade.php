<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
	<head>
		<meta charset="UTF-8 /">
		<meta http-equiv="X-UA-Compatible" content="IE=edge" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />

		{{-- CSRF トークン --}}
		<meta name="csrf-token" content="{{ csrf_token() }}" />

		{{-- ログインユーザーID --}}
		<meta name="login-user-id" content="{{ Auth::id() }}" />

		@hasSection('title')
		<title>@yield('title') | {{ config('app.name') }}</title>
		@else
		<title>@if (! Request::is('/')){{ $title }} | @endif{{ config('app.name') }}</title>
		@endif

		@include("layouts.inc.head")

		@yield('head')

	</head>
	<body>

		{{-- フラッシュ・メッセージ --}}
		@if (session('bd_flash_message'))
			@component('components.flash_message')
			@endcomponent
		@endif
		{{-- Ajax用のフラッシュ・メッセージ --}}
		@component('components.ajax_flash_message')
		@endcomponent

		@yield('content')


		{{-- JavaScript --}}
		<script src="{{ asset('js/app.js') }}"></script>
		{{-- <script src="{{ asset('js/custom.js') }}"></script> --}}

		@yield('foot')
	</body>
</html>
