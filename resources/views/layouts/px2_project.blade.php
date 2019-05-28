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
	<!-- jQuery -->
	<script src="/common/scripts/jquery-2.2.4.min.js" type="text/javascript"></script>
	<!-- Bootstrap -->
	<link rel="stylesheet" href="/common/bootstrap/css/bootstrap.min.css">
	<link rel="stylesheet" href="/common/bootstrap/css/bootstrap.css">
	<script src="/common/bootstrap/js/bootstrap.min.js"></script>
	<!-- normalize & FESS -->
	<link rel="stylesheet" href="/common/styles/contents.css" type="text/css">
	<link rel="stylesheet" href="/common/styles/fess.css" type="text/css">
	<!-- Pickles 2 Style -->
	<link rel="stylesheet" href="/common/px2style/dist/styles.css" charset="utf-8">
	<script src="/common/px2style/dist/scripts.js" charset="utf-8"></script>
	<!-- Local Resources -->
	<link rel="stylesheet" href="/common/index_files/style.css" type="text/css" data-original-title="" title="">
	<link rel="stylesheet" href="/common/index_files/styles.css" type="text/css" data-original-title="" title="">
	{{-- CSS --}}
	@yield('stylesheet')
	@yield('javascript')
</head>
<body>
	<div class="theme_wrap">

		<header class="px2-header">
			<div class="px2-header__inner">
				<div class="px2-header__px2logo">
					<a href="{{ url('/') }}"><img src="/common/images/logo.svg" alt="Pickles 2" /></a>
				</div>
				<div class="px2-header__block">
					<div class="px2-header__id">
						@guest
							<span><a class="app_name" href="{{ url('/') }}">{{ config('app.name') }}</a></span>
						@else
							@if(! Request::is('*profile*'))
								<span>{{ 'Project_'.$project->project_name }}</span>
							@else
								<span><a class="app_name" href="{{ url('/') }}">{{ config('app.name') }}</a></span>
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
								@if(! Request::is('*profile*'))
									<li><a href="{{ url('projects/'.$project->project_name.'/'.$branch_name.'/') }}" data-name="home">ホーム</a></li>
									<li><a href="{{ url('sitemaps/'.$project->project_name.'/'.$branch_name.'/') }}" data-name="sitemaps">サイトマップ</a></li>
									<li><a href="{{ url('themes/'.$project->project_name.'/'.$branch_name.'/') }}" data-name="themes">テーマ</a></li>
									<li><a href="{{ url('pages/'.$project->project_name.'/'.$branch_name.'/index.html?page_path='.'%2Findex.html')}}" data-name="pages">コンテンツ</a></li>
									<li><a href="{{ url('publish/'.$project->project_name.'/'.$branch_name) }}" data-name="publish">パブリッシュ</a></li>
								@endif

								{{-- 認証関連のリンク --}}
								{{-- 「プロフィール」と「ログアウト」のドロップダウンメニュー --}}
								<li>
									<a class="nav-link dropdown-toggle" href="#" id="dropdown-user" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{ 'ようこそ '.Auth::user()->name.' さん' }}</a>
									<ul>
										<li><a class="dropdown-item" href="{{ url('profile') }}">{{ __('Profile') }}</a></li>
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
					<button>≡</button>
					<ul>
						<li><a href="{{ url('/') }}">ダッシュボード</a></li>
						<li><a href="{{ url('staging/'.$project->project_name.'/'.$branch_name.'/') }}" data-name="staging">ステージング管理</a></li>
						<li><a href="{{ url('delivery/'.$project->project_name.'/'.$branch_name.'/') }}" data-name="delivery">配信管理</a></li>
						@guest
						@else
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
			<div class="container mt-2">
				<div class="alert alert-success">
					{{ session('my_status') }}
				</div>
			</div>
		@endif
		<div>
			@yield('content')
		</div>
		<footer class="theme-footer">
		</footer>
	</div>
	{{-- JavaScript --}}
	{{-- <script src="{{ asset('js/app.js') }}"></script>
	<script src="{{ asset('js/custom.js') }}"></script> --}}
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
			@if(! Request::is('*profile*'))
				@if (Request::is('projects/'.$project->project_name.'/'.$branch_name)) current = 'home'; @endif
				@if (Request::is('sitemaps/'.$project->project_name.'/'.$branch_name)) current = 'sitemaps'; @endif
				@if (Request::is('themes/'.$project->project_name.'/'.$branch_name)) current = 'themes'; @endif
				@if (Request::is('pages/'.$project->project_name.'/'.$branch_name.'/index.html')) current = 'pages'; @endif
				@if (Request::is('publish/'.$project->project_name.'/'.$branch_name)) current = 'publish'; @endif
				@if (Request::is('staging/'.$project->project_name.'/'.$branch_name)) current = 'staging'; @endif
				@if (Request::is('delivery/'.$project->project_name.'/'.$branch_name)) current = 'delivery'; @endif
			@endif
			px2style.header.init({'current': current});
		});
		</script>
	@endguest
	@yield('script')
</body>
</html>
