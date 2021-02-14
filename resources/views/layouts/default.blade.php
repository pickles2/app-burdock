<?php
if( !isset($branch_name) || !strlen($branch_name) ){
	$branch_name = 'master';
}
?>
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
	<head>
		<meta charset="UTF-8"><meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">

		{{-- CSRF トークン --}}
		<meta name="csrf-token" content="{{ csrf_token() }}">

		{{-- ログインユーザーID --}}
		<meta name="login-user-id" content="{{ Auth::id() }}">

		<title>@if (! Request::is('/')){{ $title }} | @endif{{ env('APP_NAME') }}</title>

		@include("layouts.inc.head")

		@yield('head')
	</head>
	<body>

		@include("layouts.inc.header")


		{{-- main block --}}
		<div class="theme-main-block">
			<div class="theme-main-block__inner" id="app">


				{{-- フラッシュ・メッセージ --}}
				@if (session('bd_flash_message'))
					@component('components.flash_message')
					@endcomponent
				@endif
				{{-- Ajax用のフラッシュ・メッセージ --}}
				@component('components.ajax_flash_message')
				@endcomponent

				<main class="theme-main">
					@if (isset($title))
					@guest
					<h1>{{ $title }}</h1>
					@else
					@if ( Request::is('') )
					@else
					<h1>{{ $title }}</h1>
					@endif
					@endguest
					@endif

					@if (session('flash_message'))
						<div class="alert alert-success" role="alert">
							{{ session('flash_message') }}
						</div>
					@endif

					<div class="contents">
						@yield('content')
					</div>
				</main>

			</div>
		</div>

		@include("layouts.inc.footer")

		@yield('foot')
	</body>
</html>
