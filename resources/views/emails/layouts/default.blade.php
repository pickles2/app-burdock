<?php
if( !isset($branch_name) || !strlen($branch_name) ){
	$branch_name = 'master';
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />

		@hasSection('title')
		<title>@yield('title') | {{ config('app.name') }}</title>
		@else
		<title>@if (! Request::is('/')){{ $title }} | @endif{{ config('app.name') }}</title>
		@endif

		<style>
			html, body {
				background-color: #e9e9e9;
				color: #333;
				font-size: 16px;
			}
			.bd-theme-outline {
				width: calc(100% - 20px);
				max-width: 800px;
				margin: 1em auto;
				background-color: #f9f9f9;
			}

			/* Header */
			.bd-theme-header {
				padding: 1em;
				background-color: #00a0e6;
				color: #fff;
				text-align: center;
			}
			.bd-theme-header__app-name {
				font-size: 36px;
				font-weight: bold;
			}
			.bd-theme-header a {
				color: #fff;
				text-decoration: none;
			}

			/* Contents Area */
			.bd-theme-content {
				padding: 1em;
			}

			/* Footer */
			.bd-theme-footer {
				padding: 1em;
				background-color: #eee;
				text-align: center;
			}

			/* Modules */
			h1 {
				font-size: 24px;
				font-weight: bold;
				margin: 1em 0 0.5em 0;
			}
			h2 {
				font-size: 20px;
				font-weight: bold;
				margin: 1em 0 0.5em 0;
			}
			h3 {
				font-size: 18px;
				font-weight: bold;
				margin: 0.5em 0 0.5em 0;
			}
			h4,
			h5,
			h6 {
				font-size: 16px;
				font-weight: bold;
				margin: 0.5em 0 0.5em 0;
			}
		</style>

@yield('head')

	</head>
	<body>
		<div class="bd-theme-outline">
			<div class="bd-theme-outline__inner">

				<header class="bd-theme-header">
					<p class="bd-theme-header__app-name"><a href="{{ config('app.url') }}">{{ config('app.name') }}</a></p>
				</header>

				<div class="bd-theme-content">

					@hasSection('title')
					<h1>@yield('title')</h1>
					@else
					<h1>{{ $title }}</h1>
					@endif

					<div class="contents">
						@yield('content')
					</div>
				</div>

				<footer class="bd-theme-footer">
					<p>&copy; {{ config('burdock.app_copyright') }}</p>
				</footer>

			</div>
		</div>

@yield('foot')

	</body>
</html>
