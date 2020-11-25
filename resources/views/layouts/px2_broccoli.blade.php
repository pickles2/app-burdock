<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
	<meta charset="UTF-8"><meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	{{-- CSRF トークン --}}
	<meta name="csrf-token" content="{{ csrf_token() }}">

	<title>@if (! Request::is('/')){{ $title }} | @endif{{ env('APP_NAME') }}</title>

	<meta name="keywords" content="">
	<meta name="description" content="">

	@if (isset($bootstrap) && $bootstrap == 4)
	<!-- jQuery -->
	<script src="/common/scripts/jquery-3.5.1.min.js" type="text/javascript"></script>
	<!-- Bootstrap4 -->
	<link rel="stylesheet" href="/common/bootstrap4/css/bootstrap.css">
	<script src="/common/bootstrap4/js/bootstrap.min.js"></script>
	@else
	<!-- jQuery -->
	<script src="/common/scripts/jquery-2.2.4.min.js" type="text/javascript"></script>
	<!-- Bootstrap -->
	<link rel="stylesheet" href="/common/bootstrap/css/bootstrap.css">
	<script src="/common/bootstrap/js/bootstrap.min.js"></script>
	@endif

	<!-- normalize -->
	<link rel="stylesheet" href="/common/styles/contents.css" type="text/css">
	<!-- Pickles 2 Style -->
	<link rel="stylesheet" href="/common/px2style/dist/styles.css" charset="utf-8">
	<script src="/common/px2style/dist/scripts.js" charset="utf-8"></script>
	<!-- Common Resources -->
	<link rel="stylesheet" href="/common/styles/common.css" type="text/css" />
	<script src="/common/scripts/common.js" charset="utf-8"></script>
	<!-- Local Resources -->
	<link rel="stylesheet" href="/common/index_files/style.css" type="text/css" data-original-title="" title="">
	<link rel="stylesheet" href="/common/index_files/styles.css" type="text/css" data-original-title="" title="">

	<!-- App Resources -->
	{{-- <link rel="stylesheet" href="{{ asset('/css/app.css') }}" type="text/css" /> --}}

	{{-- CSS --}}
	@yield('stylesheet')
</head>
<body>
	<div class="theme-wrap">
		{{-- フラッシュ・メッセージ --}}
		@if (session('bd_flash_message'))
			@component('components.flash_message')
			@endcomponent
		@endif
		<div class="theme-main">
			@yield('content')
		</div>
	</div>
	{{-- JavaScript --}}
	{{-- <script src="{{ asset('js/app.js') }}"></script> --}}
	{{-- <script src="{{ asset('js/custom.js') }}"></script> --}}
	@yield('script')
</body>
</html>
