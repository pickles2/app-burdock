@php
	$title = __('Contents').' - '.$current->page_info->title;
@endphp
@extends('layouts.px2_project')
@section('content')
<div class="container">
	<h1>コンテンツ</h1>
</div>
<div class="contents">
	<div class="container-fluid">
		<div style="float:right;">
			<a href="javascript:;" data-placement="bottom" title="コンテンツは、サイトマップに記述されたページ1つにつき1つ編集します。特別な場合を除き、コンテンツはヘッダー、フッターなどの共通部分(=テーマ領域)を含まない、コンテンツエリアのみのHTMLコードとして管理されています。一覧からページを選択し、コンテンツを編集してください。">
				<span class="glyphicon glyphicon-question-sign"></span> ヒント
			</a>
		</div>
		<div class="cont_breadcrumb">
			<ul>
				@if($current->navigation_info->breadcrumb_info !== false)
				@foreach($current->navigation_info->breadcrumb_info as $breadcrumb_info)
				<li><a href="{{ url('/pages/'.$project->project_code.'/'.$branch_name.'?page_path='.$breadcrumb_info->path) }}">{{ $breadcrumb_info->title }}</a></li>
				@endforeach
				@endif
				<li><strong>{{ $current->page_info->title }}</strong></li>
			</ul>
		</div>
	</div>
	<div class="container-fluid">
		<div class="row">
			<div class="col-xs-9">
				<div class="cont_page_info clearfix">
					<div>
						<div class="cont_page_info-prop">
							<span class="selectable">{{ $current->page_info->title }} ({{ $current->page_info->path }})</span>
							<span class="px2-editor-type px2-editor-type--@if ($editor_type === 'html'){{ 'html' }}@elseif ($editor_type === 'html.gui'){{ 'html-gui' }}@elseif ($editor_type === 'md'){{ 'md' }}@else{{ 'not-exists' }}@endif"></span>
						</div>
						<div class="cont_page_info-btn">
							<div class="btn-group">
								<a href="{{ url('/contentsEditor/'.urlencode($project->project_code).'/'.urlencode($branch_name).'?page_path='.$page_path) }}" class="btn px2-btn px2-btn--primary px2-btn--lg btn--edit" style="padding-left: 5em; padding-right: 5em; font: inherit;" target="_blank">{{ __('Edit')}}</a>
								<a href="{{ url('https://'.urlencode($project->project_code).'---'.urlencode($branch_name).'.'.env('BD_PREVIEW_DOMAIN').$page_path) }}" class="btn px2-btn px2-btn--lg btn--preview" target="_blank" style="font: inherit;">ブラウザでプレビュー</a>
								<!-- <button type="button" class="btn px2-btn px2-btn--lg btn--resources">リソース</button> -->
								<button type="button" class="btn px2-btn px2-btn--lg dropdown-toggle" data-toggle="dropdown">
									<span class="caret"></span>
									<span class="sr-only">Toggle Dropdown</span>
								</button>
								<ul class="dropdown-menu cont_page-dropdown-menu">
									{{-- <li style="max-width: 476px; overflow: hidden;">
										<a data-content="/index.html" href="javascript:;">フォルダを開く</a>
									</li>
									<li style="max-width: 476px; overflow: hidden;">
										<a data-path="/index.html" href="javascript:;">外部テキストエディタで編集</a>
									</li>
									<li style="max-width: 476px; overflow: hidden;">
										<a data-path="/index.html">リソースフォルダを開く</a>
									</li>
									<li style="max-width: 476px; overflow: hidden;">
										<a data-path="/index.html" href="javascript:;">コンテンツのソースコードを表示</a>
									</li>
									<li style="max-width: 476px; overflow: hidden;">
										<a data-path="/index.html" data-page-info="">ページ情報を表示</a>
									</li>
									<li class="divider" style="max-width: 476px; overflow: hidden;"></li>
									<li style="max-width: 476px; overflow: hidden;">
										<a data-path="/index.html" href="javascript:;">埋め込みコメントを表示する</a>
									</li>
									<li style="max-width: 476px; overflow: hidden;">
										<a class="menu-materials" data-path="/index.html" href="javascript:;">素材フォルダを開く (0)</a>
									</li>
									<li style="max-width: 476px; overflow: hidden;">
										<a data-path="/index.html" href="javascript:;">コンテンツコメントを編集</a>
									</li>
									<li class="divider" style="max-width: 476px; overflow: hidden;"></li>
									<li style="max-width: 476px; overflow: hidden;">
										<a data-path="/index.html" data-proc_type="html" href="javascript:;">他のページから複製して取り込む</a>
									</li>
									<li style="max-width: 476px; overflow: hidden;">
										<a data-path="/index.html" data-proc_type="html" href="javascript:;">編集方法を変更</a>
									</li> --}}
									<li style="max-width: 476px; overflow: hidden;">
										<a data-param="{{ $page_path }}" onClick="publishSingle(this)">このページを単体でパブリッシュ</a>
									</li>
									<script>
									function publishSingle(e) {
										// 受信したイベントデータをajaxでコントローラーに送信
										var path_region = e.dataset.param;
										$.ajax({
											url: "/publish/{{ $project->project_code }}/{{ $branch_name }}/publishSingleAjax",
											type: 'post',
											data : {
												"path_region" : path_region,
												_token : '{{ csrf_token() }}'
											},
										}).done(function(data){
											// ajaxで取得してきたデータをアラートで表示
											var flashAlert = document.getElementById("flash_alert");
											var flashAlertInner = document.getElementById("flash_alert_inner");
											flashAlertInner.innerHTML = '「{{ $current->page_info->title }}」'+data.info;
											flashAlert.style.display = "block";
											setTimeout(function() {
												$('#flash_alert').fadeOut(500);
											}, 2000);
										});
									};
									</script>
									{{-- <li style="max-width: 476px; overflow: hidden;">
										<a data-path="/index.html" href="javascript:;">コンテンツをコミット</a>
									</li>
									<li style="max-width: 476px; overflow: hidden;">
										<a data-path="/index.html" href="javascript:;">コンテンツのコミットログ</a>
									</li>
									<li style="max-width: 476px; overflow: hidden;">
										<a data-path="/index.html" href="javascript:;">ページをリロード</a>
									</li> --}}
								</ul>
							</div>
							<!-- /btn-group -->
						</div>
					</div>
				</div>
				<div class="preview_window_frame cont_preview" style="height: 70vh;">
					<div class="preview_window_frame--inner">
						<script>
						// .envよりプレビューサーバーのURLを取得
						var preview_url = '{{ 'https://'.$branch_name.'.'.$project->project_code.'.'.env('BD_PREVIEW_DOMAIN') }}';
						// 外部サイトに送るAPP_URLとスクリプトをbase64でエンコード
						var jsBase64 = '{{ base64_encode("var parent_url = '".env('APP_URL')."';".file_get_contents('../resources/views/pages/js/script.js')) }}';

						// windowロードイベント
						window.onload = function() {
							// iframeのwindowオブジェクトを取得
							var ifrm = document.getElementById('ifrm').contentWindow;
							// 外部サイトにメッセージを投げる
							ifrm.postMessage({'scriptUrl':'data:text/javascript;base64,'+encodeURIComponent(jsBase64)}, preview_url);
						};
						// メッセージ受信イベント
						window.addEventListener('message', receiveMessage, false);
						function receiveMessage(event) {
							// オリジンがpreview_urlではなかった場合終了
							if (event.origin !== preview_url) {
								return;
							};
							// 受信したイベントデータをajaxでコントローラーに送信
							var decodeEventData = decodeURIComponent(escape(atob(event.data)));
							$.ajax({
								url: "/pages/{{ $project->project_code }}/{{ $branch_name }}/ajax?page_path={{ $page_path }}",
								type: 'post',
								data : {
									"path_path" : JSON.stringify(decodeEventData),
									_token : '{{ csrf_token() }}'
								},
							}).done(function(data){
								// ajaxで取得してきたパスとIDでページ遷移
								window.location.href = 'index.html?page_path='+data.path+'&page_id='+data.id;
							});
						};
						</script>
						<iframe id="ifrm" src="{{ url('https://'.urlencode($project->project_code).'---'.urlencode($branch_name).'.'.env('BD_PREVIEW_DOMAIN').$page_path) }}"></iframe>
					</div>
				</div>
			</div>
			<div class="col-xs-3">
				<div class="cont_workspace_search">
					<div class="input-group input-group-sm">
						{{-- Vueコンポーネント --}}
						<div id="app">
							<cont-search-component project-code="{{ $project->project_code}}" branch-name="{{ $branch_name }}" page-id="{{ $page_id }}"></cont-search-component>
						</div>
					</div>
				</div>
				<!-- /.cont_workspace_search -->
				<div class="cont_workspace_container" style="height: 100vh; margin-top: 10px;">
					<div class="cont_sitemap_parent">
						@if($current->navigation_info->parent_info !== false)
						<ul class="listview">
							<li><a href="{{ url('/pages/'.$project->project_code.'/'.$branch_name.'?page_path='.$current->navigation_info->parent_info->path.'&page_id='.$current->navigation_info->parent_info->id) }}"><span class="glyphicon glyphicon-level-up"></span><span>{{ $current->navigation_info->parent_info->title }}</span></a></li>
						</ul>
						@endif
					</div>
					<div class="cont_sitemap_broslist">
						<ul class="listview">
						@if($current->navigation_info->bros_info !== false)
						@foreach($current->navigation_info->bros_info as $bros_info)
							<li><a href="{{ url('/pages/'.$project->project_code.'/'.$branch_name.'?page_path='.$bros_info->path.'&page_id='.$bros_info->id) }}" @if ($page_path == $bros_info->path) class="current" @endif>{{ $bros_info->title }}</a>
							@if($current->navigation_info->children_info !== false && $page_path === $bros_info->path)
								<ul>
								@foreach($current->navigation_info->children_info as $children_info)
									<li><a href="{{ url('/pages/'.$project->project_code.'/'.$branch_name.'?page_path='.$children_info->path.'&page_id='.$children_info->id) }}" style="font-size: 80%;">{{ $children_info->title }}</a></li>
								@endforeach
								</ul>
							@endif
							</li>
						@endforeach
						@endif
						</ul>
					</div>
				</div>
				<!-- /.cont_workspace_container -->
				<div class="cont_comment_view" data-path="/index.html" style="display: none;">no comment.</div>
				<!-- /.cont_comment_view -->
			</div>
		</div>
	</div>
</div>
@endsection
@section('script')
	<script src="{{ asset('/js/app.js') }}"></script>
@endsection
