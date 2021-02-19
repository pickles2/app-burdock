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

		@hasSection('title')
		<title>@yield('title') | {{ env('APP_NAME') }}</title>
		@else
		<title>@if (! Request::is('/')){{ $title }} | @endif{{ env('APP_NAME') }}</title>
		@endif

		@include("layouts.inc.head")

		@yield('head')
	</head>
	<body>

		@include("layouts.inc.header")


		@hasSection('first-view')
		@yield('first-view')
		@else
		<div class="theme-h1-container">
			<div class="theme-h1-container__heading">
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

				@hasSection('title')
				{{-- TODO: `@yield` する↓こちらのほうが正しい？ --}}
				<h1>@yield('title')</h1>
				@endif
			</div>
		</div>
		@endif

		<div class="theme-main-container">
			<div class="theme-main-container__header-info">
				{{-- フラッシュ・メッセージ --}}
				@if (session('bd_flash_message'))
					@component('components.flash_message')
					@endcomponent
				@endif
				{{-- Ajax用のフラッシュ・メッセージ --}}
				@component('components.ajax_flash_message')
				@endcomponent

				@if (session('flash_message'))
				<div class="alert alert-success" role="alert">
					{{ session('flash_message') }}
				</div>
				@endif
			</div>

			<div class="contents">
				@yield('content')
			</div>
		</div>

		@include("layouts.inc.footer")

		@yield('foot')
	</body>
</html>
