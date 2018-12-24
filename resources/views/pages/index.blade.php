@php
	$title = __('Contents').' - '.$current->page_info->title;
@endphp
@extends('layouts.px2_project')

@section('content')

	<div class="contents" style="margin: 0px; padding: 0px; left: 0px; top: 0px; right: 0px; height: 100vh;">
		<div class="container" data-original-title="" title="">
			<h1 data-original-title="" title="">コンテンツ</h1>
		</div>
		<div class="contents" data-original-title="" title="">
			<div class="container-fluid" data-original-title="" title="">
				<div style="float:right;" data-original-title="" title="">
					<a href="javascript:;" data-placement="bottom" title="" data-original-title="コンテンツは、サイトマップに記述されたページ1つにつき1つ編集します。特別な場合を除き、コンテンツはヘッダー、フッターなどの共通部分(=テーマ領域)を含まない、コンテンツエリアのみのHTMLコードとして管理されています。一覧からページを選択し、コンテンツを編集してください。">
						<span class="glyphicon glyphicon-question-sign" data-original-title="" title=""></span> ヒント
					</a>
				</div>
				<div class="cont_breadcrumb" data-original-title="" title="">
					<ul>
						@if($current->navigation_info->breadcrumb_info !== false)
						@foreach($current->navigation_info->breadcrumb_info as $breadcrumb_info)
						<li><a href="{{ url('/pages/'.$project->project_name.'/'.$branch_name.'/index.html?page_path='.$breadcrumb_info->path) }}">{{ $breadcrumb_info->title }}</a></li>
						@endforeach
						@endif
						<li><strong>{{ $current->page_info->title }}</strong></li>
					</ul>
				</div>
			</div>
			<div class="container-fluid" data-original-title="" title="">
				<div class="row" data-original-title="" title="">
					<div class="col-xs-9" data-original-title="" title="">
						<div class="cont_page_info clearfix" data-original-title="" title="">
							<div>
								<div class="cont_page_info-prop">
									<span class="selectable">{{ $current->page_info->title }} ({{ $current->page_info->path }})</span>
									<span class="px2-editor-type px2-editor-type--@if ($editor_type === 'html'){{ 'html' }}@elseif ($editor_type === 'html.gui'){{ 'html-gui' }}@elseif ($editor_type === 'md'){{ 'md' }}@else{{ 'not-exists' }}@endif"></span>
								</div>
								<div class="cont_page_info-btn">
									<div class="btn-group">
										<a href="{{ url('/pages/'.$project->project_name.'/'.$branch_name.'?page_path='.$page_param) }}" class="btn px2-btn px2-btn--primary px2-btn--lg btn--edit" style="padding-left: 5em; padding-right: 5em; font: inherit;" target="_blank">{{ __('Edit')}}</a>
										<a href="{{ url('https://prev1.app-burdock.localhost'.$page_param) }}" class="btn px2-btn px2-btn--lg btn--preview" target="_blank" style="font: inherit;">ブラウザでプレビュー</a>
										<!-- <button type="button" class="btn px2-btn px2-btn--lg btn--resources">リソース</button> -->
										<button type="button" class="btn px2-btn px2-btn--lg dropdown-toggle" data-toggle="dropdown">
											<span class="caret"></span>
											<span class="sr-only">Toggle Dropdown</span>
										</button>
										<ul class="dropdown-menu cont_page-dropdown-menu">
											<li style="max-width: 476px; overflow: hidden;">
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
											</li>
											<li style="max-width: 476px; overflow: hidden;">
												<a data-path="/index.html" data-proc_type="html" href="javascript:;">このページを単体でパブリッシュ</a>
											</li>
											<li style="max-width: 476px; overflow: hidden;">
												<a data-path="/index.html" href="javascript:;">コンテンツをコミット</a>
											</li>
											<li style="max-width: 476px; overflow: hidden;">
												<a data-path="/index.html" href="javascript:;">コンテンツのコミットログ</a>
											</li>
											<li style="max-width: 476px; overflow: hidden;">
												<a data-path="/index.html" href="javascript:;">ページをリロード</a>
											</li>
										</ul>
									</div>
									<!-- /btn-group -->
								</div>
							</div>
						</div>
						<div class="preview_window_frame cont_preview" data-original-title="" title="" style="height: 70vh;">
							<div class="preview_window_frame--inner" data-original-title="" title="">
								<script>
								var jsBase64 = '{{ base64_encode(file_get_contents('../resources/views/pages/js/script.js')) }}';

								// windowロードイベント
								window.onload = function() {
									// iframeのwindowオブジェクトを取得
									var ifrm = document.getElementById('ifrm').contentWindow;
									// 外部サイトにメッセージを投げる
									ifrm.postMessage({'scriptUrl':'data:text/javascript;base64,'+encodeURIComponent(jsBase64)}, 'https://prev1.app-burdock.localhost');
								};
								// メッセージ受信イベント
								window.addEventListener('message', receiveMessage, false);
								function receiveMessage(event) {
									// オリジンがhttps://prev1.app-burdock.localhostではなかった場合終了
									if (event.origin !== "https://prev1.app-burdock.localhost") {
										return;
									};
									// 受信したイベントデータをajaxでコントローラーに送信
									var decodeEventData = decodeURIComponent(escape(atob(event.data)));
									$.ajax({
										url: "/pages/{{ $project->project_name }}/{{ $branch_name }}/ajax?page_path={{ $page_param }}",
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
								<iframe id="ifrm" data-original-title="" title="" src="{{ url('https://prev1.app-burdock.localhost'.$page_param) }}"></iframe>
							</div>
						</div>
					</div>
					<div class="col-xs-3" data-original-title="" title="">
						<div class="cont_workspace_search" data-original-title="" title="">
							<div class="input-group input-group-sm" data-original-title="" title="">
								<form action="javascript;;" id="cont_search_form" data-original-title="" title="">
									<div class="input-group" data-original-title="" title="">
										<input type="text" class="form-control" placeholder="Search..." data-original-title="" title="">
										<span class="input-group-btn" data-original-title="" title="">
											<button class="px2-btn px2-btn--primary" type="submit" data-original-title="" title="">検索</button>
										</span>
									</div>
									<!-- /input-group -->
									<div class="btn-group btn-group-justified" data-toggle="buttons" data-original-title="" title="">
										<label class="btn px2-btn active" data-original-title="" title="">
											<input type="radio" name="list-label" value="title" checked="checked" data-original-title="" title="">title
										</label>
										<label class="btn px2-btn" data-original-title="" title="">
											<input type="radio" name="list-label" value="path" data-original-title="" title="">path
										</label>
									</div>
								</form>
							</div>
						</div>
						<!-- /.cont_workspace_search -->
						<div class="cont_workspace_container" data-original-title="" title="" style="height: 100vh; margin-top: 10px;">
							<div class="cont_sitemap_parent" data-original-title="" title="">
								@if($current->navigation_info->parent_info !== false)
								<ul class="listview">
							        <li><a href="{{ url('/pages/'.$project->project_name.'/'.$branch_name.'/index.html?page_path='.$current->navigation_info->parent_info->path.'&page_id='.$current->navigation_info->parent_info->id) }}"><span class="glyphicon glyphicon-level-up"></span><span>{{ $current->navigation_info->parent_info->title }}</span></a></li>
							    </ul>
								@endif
							</div>
							<div class="cont_sitemap_broslist" data-original-title="" title="">
								<ul class="listview">
								@if($current->navigation_info->bros_info !== false)
								@foreach($current->navigation_info->bros_info as $bros_info)
									<li><a href="{{ url('/pages/'.$project->project_name.'/'.$branch_name.'/index.html?page_path='.$bros_info->path.'&page_id='.$bros_info->id) }}" @if ($page_param == $bros_info->path) class="current" @endif>{{ $bros_info->title }}</a>
									@if($current->navigation_info->children_info !== false && $page_param === $bros_info->path)
										<ul>
										@foreach($current->navigation_info->children_info as $children_info)
											<li><a href="{{ url('/pages/'.$project->project_name.'/'.$branch_name.'/index.html?page_path='.$children_info->path.'&page_id='.$children_info->id) }}" style="font-size: 80%;">{{ $children_info->title }}</a></li>
										@endforeach
										</ul>
									@endif
									</li>
								@endforeach
								@endif
								</ul>
							</div>
							<div class="cont_sitemap_search" data-original-title="" title="" style="display: none;"></div>
						</div>
						<!-- /.cont_workspace_container -->
						<div class="cont_comment_view" data-original-title="" title="" data-path="/index.html" style="display: none;">no comment.</div>
						<!-- /.cont_comment_view -->
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection
