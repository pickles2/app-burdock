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
	<link rel="stylesheet" href="/common/index_files/style.css" type="text/css" />
	<link rel="stylesheet" href="/common/index_files/styles.css" type="text/css" />

	<!-- App Resources -->
	<link rel="stylesheet" href="{{ asset('/css/app.css') }}" type="text/css" />

	{{-- CSS --}}
	@yield('stylesheet')
</head>
<body>
	<div class="theme-wrap">

		<header class="px2-header">
			<div class="px2-header__inner">
				<div class="px2-header__px2logo">
					<a href="{{ url('/') }}"><img src="/common/images/logo.svg" alt="{{ env('APP_NAME') }}" /></a>
				</div>
				<div class="px2-header__block">
					<div class="px2-header__id">
						@guest
							<span></span>
						@else
							@if( isset($project) && ! Request::is('*mypage*') && ! Request::is('/') && ! Request::is('setup/*'))
								<span>Project {{ $project->project_name }}</span>
							@else
								<span></span>
							@endif
						@endguest
					</div>
					<div class="px2-header__global-menu">
						<ul>
							@guest
								{{-- 認証関連のリンク --}}
								{{-- 「ログイン」と「ユーザー登録」へのリンク --}}
								<li><a href="{{ route('login') }}" data-name="login">{{ __('Login') }}</a></li>
								<li><a href="{{ route('register') }}" data-name="register">{{ __('Register') }}</a></li>
								<li><a href="javascript:void(0)">{{ __('locale.'.App::getLocale()) }}</a>
									<ul>
										@if (!App::isLocale('en'))
											<li><a class="dropdown-item" href="{{ locale_url('en') }}">{{ __('locale.en') }}</a></li>
										@endif
										@if (!App::isLocale('ja'))
											<li><a class="dropdown-item" href="{{ locale_url('ja') }}">{{ __('locale.ja') }}</a></li>
										@endif
									</ul>
								</li>
							@else
								@if( isset($project) && ! Request::is('*mypage*') && ! Request::is('/') && ! Request::is('setup/*'))
									<li><a href="{{ url('home/'.urlencode($project->project_code).'/'.urlencode($branch_name).'/') }}" data-name="home">ホーム</a></li>
									<li><a href="{{ url('sitemaps/'.urlencode($project->project_code).'/'.urlencode($branch_name).'/') }}" data-name="sitemaps">サイトマップ</a></li>
									<li><a href="{{ url('themes/'.urlencode($project->project_code).'/'.urlencode($branch_name).'/') }}" data-name="themes">テーマ</a></li>
									<li><a href="{{ url('contents/'.urlencode($project->project_code).'/'.urlencode($branch_name).'/') }}" data-name="contents">コンテンツ</a></li>
									<li><a href="{{ url('publish/'.urlencode($project->project_code).'/'.urlencode($branch_name).'/') }}" data-name="publish">パブリッシュ</a></li>
								@endif

								{{-- 認証関連のリンク --}}
								{{-- 「プロフィール」と「ログアウト」のドロップダウンメニュー --}}
								<li>
									<a class="nav-link dropdown-toggle" href="#" id="dropdown-user" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{ 'ようこそ '.Auth::user()->name.' さん' }}</a>
									<ul>
										<li><a class="dropdown-item" href="{{ url('mypage') }}">{{ __('Mypage') }}</a></li>
										<li><a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">{{ __('Logout') }}</a>
											<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
												@csrf
											</form></li>
									</ul>
								</li>

								<li>
									<a href="javascript:void(0)">{{ __('locale.'.App::getLocale()) }}</a>
									<ul>
										@if (!App::isLocale('en'))
											<li><a class="dropdown-item" href="{{ locale_url('en') }}">{{ __('locale.en') }}</a></li>
										@endif
										@if (!App::isLocale('ja'))
											<li><a class="dropdown-item" href="{{ locale_url('ja') }}">{{ __('locale.ja') }}</a></li>
										@endif
									</ul>
								</li>
							@endguest
						</ul>
					</div>
				</div>
				<div class="px2-header__shoulder-menu">
					<button><span class="px2-header__hamburger"></span></button>
					<ul>
						<li><a href="{{ url('/') }}">ダッシュボード</a></li>
						@guest
						@else
							@if( isset($project) && ! Request::is('*mypage*') && ! Request::is('/') && ! Request::is('setup/*'))
								<li><a href="{{ url('projects/'.urlencode($project->project_code).'/edit') }}" data-name="config">プロジェクト概要設定</a></li>
								<li><a href="{{ url('composer/'.urlencode($project->project_code).'/'.urlencode($branch_name).'/') }}" data-name="composer">Composer</a></li>
								<li><a href="{{ url('git/'.urlencode($project->project_code).'/'.urlencode($branch_name).'/') }}" data-name="git">Git</a></li>
								<li><a href="{{ url('staging/'.urlencode($project->project_code).'/'.urlencode($branch_name).'/') }}" data-name="staging">ステージング管理</a></li>
								<li><a href="{{ url('delivery/'.urlencode($project->project_code).'/'.urlencode($branch_name).'/') }}" data-name="delivery">配信管理</a></li>
								<li><a href="{{ url('files-and-folders/'.urlencode($project->project_code).'/'.urlencode($branch_name).'/') }}" data-name="files-and-folders">ファイルとフォルダ</a></li>
							@endif
						<li><a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">{{ __('Logout') }}</a>
							<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
								@csrf
							</form></li>
						@endguest
					</ul>
				</div>
			</div>
		</header>

		{{-- フラッシュ・メッセージ --}}
		@if (session('my_status'))
			@component('components.flash_message')
			@endcomponent
		@endif
		{{-- Ajax用のフラッシュ・メッセージ --}}
		@component('components.ajax_flash_message')
		@endcomponent
		<div class="theme-main">
			@yield('content')
		</div>
		<footer class="theme-footer">
		</footer>
	</div>
	{{-- JavaScript --}}
	{{-- <script src="{{ asset('js/custom.js') }}"></script> --}}
	@guest
		<script>
		window.addEventListener('load', function(){
			var current = '';
			px2style.header.init({'current': current});
		});
		</script>
	@else
		<script>
		window.addEventListener('load', function(){
			var current = '';
			@if (Request::is('home/*')) current = 'home'; @endif
			@if (Request::is('sitemaps/*')) current = 'sitemaps'; @endif
			@if (Request::is('themes/*')) current = 'themes'; @endif
			@if (Request::is('contents/*')) current = 'contents'; @endif
			@if (Request::is('publish/*')) current = 'publish'; @endif
			@if (Request::is('projects/*')) current = 'projects'; @endif
			@if (Request::is('composer/*')) current = 'composer'; @endif
			@if (Request::is('git/*')) current = 'git'; @endif
			@if (Request::is('staging/*')) current = 'staging'; @endif
			@if (Request::is('delivery/*')) current = 'delivery'; @endif
			@if (Request::is('files-and-folders/*')) current = 'files-and-folders'; @endif
			@if (Request::is('system-maintenance') || Request::is('system-maintenance/*')) current = 'system-maintenance'; @endif
			@if (Request::is('mypage') || Request::is('mypage/*')) current = 'mypage'; @endif
			px2style.header.init({'current': current});
		});
		</script>
	@endguest
	<script src="{{ asset('/js/app.js') }}"></script>
	@yield('script')
</body>
</html>
