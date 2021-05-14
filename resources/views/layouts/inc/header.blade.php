<header class="px2-header">
	<div class="px2-header__inner">
		<div class="px2-header__px2logo">
			<a href="{{ url('/') }}"><img src="/common/images/logo.svg" alt="{{ config('app.name') }}" /></a>
		</div>
		<div class="px2-header__block">
			<div class="px2-header__id">
				@guest
					<span>{{ config('app.name') }}</span>
				@else
					@if( isset($project) && ! Request::is('*mypage*') && ! Request::is('/') && ! Request::is('setup/*'))
						<span>{{ $project->project_name }}</span>
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
					@else
						@if( isset($project) && ! Request::is('*mypage*') && ! Request::is('/') && ! Request::is('setup/*'))
							<li><a href="{{ url('home/'.urlencode($project->project_code).'/'.urlencode($branch_name).'/') }}" data-name="home">ホーム</a></li>
							@if( $global->project_status->isPxStandby )
							<li><a href="{{ url('sitemaps/'.urlencode($project->project_code).'/'.urlencode($branch_name).'/') }}" data-name="sitemaps">サイトマップ</a></li>
							<li><a href="{{ url('themes/'.urlencode($project->project_code).'/'.urlencode($branch_name).'/') }}" data-name="themes">テーマ</a></li>
							<li><a href="{{ url('contents/'.urlencode($project->project_code).'/'.urlencode($branch_name).'/') }}" data-name="contents">コンテンツ</a></li>
							<li><a href="{{ url('publish/'.urlencode($project->project_code).'/'.urlencode($branch_name).'/') }}" data-name="publish">パブリッシュ</a></li>
							@endif
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

					@endguest
				</ul>
			</div>
		</div>
		<div class="px2-header__shoulder-menu">
			<button><span class="px2-header__hamburger"></span></button>
			<ul>
				@guest
				<li><a href="{{ route('login') }}" data-name="login">ログイン</a></li>
				<li><a href="{{ route('register') }}" data-name="register">新規ユーザー登録</a></li>
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
				@else
					<li><a href="{{ url('/') }}">ダッシュボード</a></li>
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
					@if( isset($project) && ! Request::is('*mypage*') && ! Request::is('/') && ! Request::is('setup/*'))
						<li><a href="{{ url('projects/'.urlencode($project->project_code).'/edit') }}" data-name="projects">プロジェクト概要設定</a></li>
						@if( $global->project_status->composerJsonExists )
							<li><a href="{{ url('composer/'.urlencode($project->project_code).'/'.urlencode($branch_name).'/') }}" data-name="composer">Composerを操作する</a></li>
						@endif
						@if( $global->project_status->pathExists )
							<li><a href="{{ url('git/'.urlencode($project->project_code).'/'.urlencode($branch_name).'/') }}" data-name="git">Gitを操作する</a></li>
						@endif
						@if( $global->project_status->isPxStandby )
							<li><a href="javascript:;">ツール</a>
								<ul>
									<li><a href="{{ url('search/'.urlencode($project->project_code).'/'.urlencode($branch_name).'/') }}" data-name="search">検索</a></li>
								</ul>
							</li>
							@if( isset($global->cce) && (is_object($global->cce) || is_array($global->cce)) )
								<li><a href="javascript:;">拡張機能</a>
									<ul>
										@foreach($global->cce as $cce_id=>$cce_info)
										<li><a href="{{ url('custom_console_extensions/'.urlencode($cce_id).'/'.urlencode($project->project_code).'/'.urlencode($branch_name).'/') }}" data-name="custom_console_extensions.{{ $cce_id }}">{{ $cce_info->label }}</a></li>
										@endforeach
									</ul>
								</li>
							@endif
							<li><a href="{{ url('staging/'.urlencode($project->project_code).'/'.urlencode($branch_name).'/') }}" data-name="staging">ステージング管理</a></li>
							<li><a href="{{ url('delivery/'.urlencode($project->project_code).'/'.urlencode($branch_name).'/') }}" data-name="delivery">配信管理</a></li>
							<li><a href="{{ url('clearcache/'.urlencode($project->project_code).'/'.urlencode($branch_name).'/') }}" data-name="clearcache">キャッシュを消去する</a></li>
						@endif
						@if( $global->project_status->pathExists )
							<li><a href="{{ url('files-and-folders/'.urlencode($project->project_code).'/'.urlencode($branch_name).'/') }}" data-name="files-and-folders">ファイルとフォルダ</a></li>
						@endif
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
