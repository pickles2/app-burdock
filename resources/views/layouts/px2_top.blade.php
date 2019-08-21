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

    @if (isset($bootstrap) && $bootstrap == 4)
	<!-- Bootstrap4 -->
    <link rel="stylesheet" href="/common/bootstrap4/css/bootstrap.css">
	<script src="/common/bootstrap4/js/bootstrap.min.js"></script>
    @else
	<!-- Bootstrap -->
    <link rel="stylesheet" href="/common/bootstrap/css/bootstrap.css">
	<script src="/common/bootstrap/js/bootstrap.min.js"></script>
    @endif

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
		<header class="theme-header" style="border-bottom-color: rgb(0, 160, 230); color: rgb(0, 160, 230);">
			<div class="theme-header__inner clearfix">
				<div class="theme-header__px2logo" style="width: 45px; height: 45px;">
					<img src="/common/images/logo.svg">
				</div>
				<div class="theme-header__id" style="opacity: 1;">
					<div><a class="app_name" href="{{ url('/') }}">{{ config('app.name') }}</a></div>
                    <div></div>
                </div>
				<div class="theme-header__gmenu" id="navbarLeftContent">
                    {{-- Navbarの右側 --}}
                    <ul class="">
						{{-- プロジェクト作成ボタン --}}
                        <li class="">
                            <a href="{{ url('projects/create') }}" id="new-project" class="btn-success">
                                {{ __('Create Project') }}
                            </a>
                        </li>
                        {{-- 認証関連のリンク --}}
                        @guest
                            {{-- 「ログイン」と「ユーザー登録」へのリンク --}}
                            <li class="">
                                <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                            </li>
                            <li class="">
                                <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                            </li>
                        @else
                            {{-- 「プロフィール」と「ログアウト」のドロップダウンメニュー --}}
                            <li class="">
                                <a class="nav-link dropdown-toggle" href="#" id="dropdown-user" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    {{ 'ようこそ '.Auth::user()->name.' さん' }}
                                </a>
                                <div class="dropdown-menu" aria-labelledby="dropdown-user">
                                    <a class="dropdown-item" href="{{ url('mypage') }}">
                                        {{ __('Mypage') }}
                                    </a>
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                        onclick="event.preventDefault();
                                                 document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                        <li class="">
                            <a class="nav-link dropdown-toggle" href="javascript:void(0)" id="dropdown-lang" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                {{ __('locale.'.App::getLocale()) }}
                            </a>
                            <div class="dropdown-menu" aria-labelledby="dropdown-lang">
                                @if (!App::isLocale('en'))
                                    <a class="dropdown-item" href="{{ locale_url('en') }}">
                                        {{ __('locale.en') }}
                                    </a>
                                @endif
                                @if (!App::isLocale('ja'))
                                    <a class="dropdown-item" href="{{ locale_url('ja') }}">
                                        {{ __('locale.ja') }}
                                    </a>
                                @endif
                            </div>
                        </li>
                    </ul>
                </div>
				{{-- <div class="theme-header__shoulder-menu" style="width: 50px; height: 30px;">
					<button><span class="px2-header__hamburger"></span></button>
					<ul style="display: none; top: 30px; height: 892px;">
                        <li><a href="">ダッシュボード</a></li>
                        <li><a href="">フォルダを開く</a></li>
                        <li><a href="">ブラウザで開く</a></li>
                        <li><a href="">テキストエディタで開く</a></li>
                        <li><a href="">Gitクライアントで開く</a></li>
                        <li><a href="">コマンドラインで開く</a></li>
                        <li><a href="">プロジェクト共有設定(config.php)</a></li>
                        <li><a href="">composer</a></li>
                        <li><a href="">git</a></li>
                        <li><a href="">プレビュー</a></li>
                        <li><a href="">検索</a></li>
                        <li><a href="">モジュール</a></li>
                        <li><a href="">スタイルガイド生成</a></li>
                        <li><a href="">コンテンツを一括加工</a></li>
                        <li><a href="">コンテンツを移動する</a></li>
                        <li><a href="">GUI編集コンテンツを一括再構成</a></li>
                        <li><a href="">キャッシュを消去</a></li>
                        <li><a href="">システム情報</a></li>
                        <li><a href="">Pickles 2 アプリケーション設定</a></li>
                        <li><a href="">ヘルプ</a></li>
                        <li><a href="">デペロッパツール</a></li>
                        <li><a href="">終了</a></li>
                    </ul>
				</div> --}}
			</div>
		</header>
		{{-- フラッシュ・メッセージ --}}
        @if (session('my_status'))
			@component('components.flash_message')
			@endcomponent
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
    @yield('script')
</body>
</html>
