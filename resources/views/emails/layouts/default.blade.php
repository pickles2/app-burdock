<?php
if( !isset($branch_name) || !strlen($branch_name) ){
	$branch_name = 'master';
}
?>
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
	<head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1">

		@hasSection('title')
		<title>@yield('title') | {{ config('app.name') }}</title>
		@else
		<title>@if (! Request::is('/')){{ $title }} | @endif{{ config('app.name') }}</title>
		@endif

		<style>
			html, body {
				background-color: #f3f3f3;
				color: #333;
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
				background-color: #333;
				color: #fff;
				text-align: center;
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
		</style>

@yield('head')

	</head>
	<body>
		<div class="bd-theme-outline">
			<div class="bd-theme-outline__inner">

				<header class="bd-theme-header">
					<h1><a href="{{ config('app.url') }}">{{ config('app.name') }}</a></h1>
				</header>

				<div class="bd-theme-content">
					<div class="contents">
						@yield('content')
					</div>
				</div>

				<footer class="bd-theme-footer">
					<p>{{ config('burdock.app_copyright') }}</p>
				</footer>

			</div>
		</div>

@yield('foot')

	</body>
</html>
