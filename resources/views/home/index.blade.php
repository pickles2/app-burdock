@php
	$gitUtil = new \App\Helpers\git($project);
	$title = 'Project "'.$project->project_name.'"';
@endphp
@extends('layouts.default')


@section('head')
<link href="{{ asset('/cont/home/style.css') }}" rel="stylesheet" />
@endsection

@section('content')

<div class="cont_maintask_ui">

	<h2>基本的な手順</h2>
	<div class="row">
		<div class="col-sm-3">
			<button type="button" onclick="window.location.href='{{ url('sitemaps'.'/'.urlencode($project->project_code).'/'.urlencode($branch_name)) }}';" class="px2-btn cont_mainmenu">{{ __('Edit Sitemap')}}</button>
		</div>
		<div class="col-sm-3">
			<button type="button" onclick="window.location.href='{{ url('themes/'.urlencode($project->project_code).'/'.urlencode($branch_name)) }}';" class="px2-btn cont_mainmenu">{{ __('Edit Themes')}}</button>
		</div>
		<div class="col-sm-3">
			<button type="button" onclick="window.location.href='{{ url('contents/'.urlencode($project->project_code).'/'.urlencode($branch_name)) }}';" class="px2-btn cont_mainmenu">{{ __('Edit Contents')}}</button>
		</div>
		<div class="col-sm-3">
			<button type="button" onclick="window.location.href='{{ url('publish'.'/'.urlencode($project->project_code).'/'.urlencode($branch_name)) }}';" class="px2-btn cont_mainmenu">{{ __('To Publish')}}</button>
		</div>
	</div><!-- / .row -->

	<ul class="px2-horizontal-list px2-horizontal-list--right">
		<li><a href="{{ url('files-and-folders/'.urlencode($project->project_code).'/'.urlencode($branch_name).'/') }}" class="px2-link px2-link--burette">フォルダを開く</a></li>
		<li><a href="{{ '//'.\App\Helpers\utils::preview_host_name( $project->project_code, $branch_name ) }}" target="_blank" class="px2-link px2-link--burette">新規ウィンドウでプレビュー</a></li>
		<!-- <li><a href="javascript:main.openAppInBrowser();" class="px2-link px2-link--burette">新規ウィンドウでパブリッシュプレビュー</a></li> -->
		<li><a href="{{ url('git/'.urlencode($project->project_code).'/'.urlencode($branch_name).'/') }}" class="px2-link px2-link--burette">Git を操作する</a></li>
		<!-- <li><a href="javascript:main.openInTextEditor( contApp.pj.get('path') );" class="px2-link px2-link--burette">外部テキストエディタで開く</a></li> -->
		<!-- <li><a href="javascript:main.openInGitClient( contApp.pj.get('path') );" class="px2-link px2-link--burette">外部Gitクライアントで開く</a></li> -->
		<!-- <li><a href="javascript:main.openInTerminal( contApp.pj.get('path') );" class="px2-link px2-link--burette">コマンドラインで開く</a></li> -->
		<li><a href="{{ url('composer/'.urlencode($project->project_code).'/'.urlencode($branch_name).'/') }}" class="px2-link px2-link--burette">Composer を操作する</a></li>
	</ul>
</div>

<div class="px2-p" style="margin-top: 100px;">
	<div class="alert alert-info">Hint! : <span class="cont_hint">Burdock は Pickles 2 をウェブ上で操作するWebアプリケーションです。</span></div>
</div>

<h2>Project Information</h2>
<div class="px2-responsive">
	<table class="px2-table" style="width:100%; table-layout: fixed;">
		<colgroup><col width="30%"><col width="70%"></colgroup>
		<tbody>
			<tr>
				<th>Project Name</th>
				<td class="tpl_name">
				@if (is_object($project_branch_info) && is_object($project_branch_info->packages) && is_object($project_branch_info->packages->package_list) && is_array($project_branch_info->packages->package_list->projects) && count($project_branch_info->packages->package_list->projects))
					{{ $project_branch_info->packages->package_list->projects[0]->name }}
				@else
					---
				@endif
				</td>
			</tr>
			<tr>
				<th>Project Code</th>
				<td class="tpl_code">{{ $project->project_code }}</td>
			</tr>
			<tr>
				<th>Git URL</th>
				<td class="tpl_code">{{ $project->git_url }}</td>
			</tr>
		</tbody>
	</table>
</div>

<div class="px2-p px2-text-align-center">
	<button class="px2-btn px2-btn--danger" onclick="window.bdApp.modalDeleteProject('{{ $project->project_code }}');">このプロジェクトを削除</button>
</div>
<hr>
<address class="px2-text-align-center">&copy;  {{ config('burdock.app_copyright') }}.</address>

@endsection
