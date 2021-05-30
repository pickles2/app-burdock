<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
	<head>
		<meta charset="UTF-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge" />
		<meta name="viewport" content="width=1366" />

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

		<style>
			body{
				width: 100%;
				height: 100%;
				display: flex;
				flex-direction: column;
			}
			.theme-wrap {
				flex-grow: 100;
				max-height: 100%;
				height: 100%;

				display: flex;
				flex-direction: column;

			}
			.theme-wrap .contents{
				flex-grow: 100;
				height: 100%;
				overflow: auto;
			}
			.theme-wrap footer.theme-footer{
				flex-grow: 1;
			}

			/* via: styles.css */
			/*
			.contents {
				top: 0;
				left: 0;
				width: 100%;
				min-height: 150px;
				z-index: 50;
			}
			.contents iframe {
				width: 100%;
				max-height: 100%;
				border: none;
				margin: 0;
				padding: 0;
			}
			*/
		</style>

		@yield('head')
	</head>
	<body>
		<div class="theme-wrap">
			{{-- フラッシュ・メッセージ --}}
			@if (session('bd_flash_message'))
				@component('components.flash_message')
				@endcomponent
			@endif
			<div class="contents">
				@yield('content')
			</div>
		</div>

		{{-- JavaScript --}}
		{{-- <script src="{{ asset('js/app.js') }}"></script> --}}
		{{-- <script src="{{ asset('js/custom.js') }}"></script> --}}

		<!-- Pickles 2 Style -->
		<script src="/common/px2style/dist/px2style.js" charset="utf-8"></script>

		@yield('foot')
	</body>
</html>
