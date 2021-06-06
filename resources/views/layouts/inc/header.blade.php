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
							@if( $global->project_status->isPxStandby )
							@foreach($global->main_menu as $main_menu_id=>$main_menu_info)
							<li><a href="{{ url($main_menu_info->href) }}" data-name="{{ $main_menu_info->app }}">{{ $main_menu_info->label }}</a></li>
							@endforeach
							@else
							<li><a href="{{ url('home/'.urlencode($project->project_code).'/'.urlencode($branch_name).'/') }}" data-name="home">ホーム</a></li>
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
					@if( isset($project) && ! Request::is('*mypage*') && ! Request::is('/') && ! Request::is('setup/*'))
						@if( $global->project_status->isPxStandby )
							<li><a href="{{ '//'.\App\Helpers\utils::preview_host_name( $project->project_code, $branch_name ).\App\Helpers\utils::get_path_controot() }}" target="_blank">新規ウィンドウでプレビュー</a></li>
						@endif
						<li>
							<a href="javascript:void(0)">設定</a>
							<ul>
								<li><a href="{{ url('projects/'.urlencode($project->project_code).'/edit') }}" data-name="projects">プロジェクト環境設定</a></li>
							</ul>
						</li>
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
										@if ( !$cce_info )
											@continue
										@endif
										<li><a href="{{ url($cce_info->href) }}" data-name="{{ $cce_info->app }}">{{ $cce_info->label }}</a></li>
										@endforeach
									</ul>
								</li>
							@endif

							<li><a href="{{ url('staging/'.urlencode($project->project_code).'/'.urlencode($branch_name).'/') }}" data-name="staging">ステージング管理</a></li>
							<li><a href="{{ url('delivery/'.urlencode($project->project_code).'/'.urlencode($branch_name).'/') }}" data-name="delivery">配信管理</a></li>
							<li><a href="{{ url('clearcache/'.urlencode($project->project_code).'/'.urlencode($branch_name).'/') }}" data-name="clearcache">キャッシュを消去する</a></li>




							@foreach($global->shoulder_menu as $shoulder_menu_id=>$shoulder_menu_info)
							<li><a href="{{ url($shoulder_menu_info->href) }}" data-name="{{ $shoulder_menu_info->app }}">{{ $shoulder_menu_info->label }}</a></li>
							@endforeach




						@endif
						@if( $global->project_status->pathExists )
							<li><a href="{{ url('files-and-folders/'.urlencode($project->project_code).'/'.urlencode($branch_name).'/') }}" data-name="files-and-folders">ファイルとフォルダ</a></li>
						@endif
					@endif
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
					<li><a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">{{ __('Logout') }}</a>
						<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
							@csrf
						</form></li>
				@endguest
			</ul>
		</div>
	</div>
</header>
