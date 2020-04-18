@php
	$title = $project->project_name;
@endphp
@extends('layouts.px2_project')
@section('content')
<div class="container">
	<h1 id="project-title" style="margin-bottom: 50px;">Project "{{ $title }}"</h1>
	<div class="contents">
		<div class="cont_info"></div>
		<div class="cont_maintask_ui">

			<h2>基本的な手順</h2>
			<div class="row" style="margin-bottom: 100px;">
				<div class="col-sm-3">
					<button type="button" onclick="window.location.href='{{ url('/sitemaps'.'/'.urlencode($project->project_code).'/'.urlencode($branch_name)) }}';" class="px2-btn cont_mainmenu">{{ __('Edit Sitemap')}}</button>
				</div>
				<div class="col-sm-3">
					<button type="button" onclick="window.location.href='{{ url('themes/'.urlencode($project->project_code).'/'.urlencode($branch_name)) }}';" class="px2-btn cont_mainmenu">{{ __('Edit Themes')}}</button>
				</div>
				<div class="col-sm-3">
					<button type="button" onclick="window.location.href='{{ url('pages/'.urlencode($project->project_code).'/'.urlencode($branch_name)) }}';" class="px2-btn cont_mainmenu">{{ __('Edit Contents')}}</button>
				</div>
				<div class="col-sm-3">
					<button type="button" onclick="window.location.href='{{ url('/publish'.'/'.urlencode($project->project_code).'/'.urlencode($branch_name)) }}';" class="px2-btn cont_mainmenu">{{ __('To Publish')}}</button>
				</div>
			</div><!-- / .row -->
		</div>
		<div class="alert alert-info">Hint! : <span class="cont_hint">Burdock は Pickles2をベースにしたWebアプリケーションです。</span></div>
		<p>
			<div>
				@component('components.btn-del-project')
					@slot('controller', 'projects')
					@slot('id', $project->id)
					@slot('code', $project->project_code)
					@slot('name', $project->project_name)
					@slot('branch', get_git_remote_default_branch_name())
				@endcomponent
			</div>
		</p>
		<hr>
		<address class="center">(C)Pickles 2 Project.</address>
	</div>
</div>
@endsection
