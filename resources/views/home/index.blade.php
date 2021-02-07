@php
	$gitUtil = new \pickles2\burdock\git($project);
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
		</div>
		<div class="alert alert-info">Hint! : <span class="cont_hint">Burdock は Pickles2をベースにしたWebアプリケーションです。</span></div>
		<div class="row">
			<div class="col-sm-12">

				<h2>Project Information</h2>
				<div class="px2-responsive">
					<table class="px2-table" style="width:100%; table-layout: fixed;">
						<colgroup><col width="30%"><col width="70%"></colgroup>
						<tbody>
							<tr>
								<th>Project Name</th>
								<td class="tpl_name">
								@if (is_object($bd_object) && is_object($bd_object->packages) && is_object($bd_object->packages->package_list) && is_array($bd_object->packages->package_list->projects) && count($bd_object->packages->package_list->projects))
									{{ $bd_object->packages->package_list->projects[0]->name }}
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
							{{-- ↓不要なサーバー内部の情報は、なるべくクライアントへ送出したくない。 --}}
							{{-- <tr>
								<th>Path</th>
								<td class="tpl_path">{{ $bd_object->realpath_docroot }}</td>
							</tr>
							<tr>
								<th>Home Directory</th>
								<td class="tpl_home_dir">{{ $bd_object->packages->package_list->projects[0]->path_homedir }}</td>
							</tr>
							<tr>
								<th>Entry Script</th>
								<td class="tpl_entry_script">{{ $bd_object->packages->package_list->projects[0]->path }}</td>
							</tr> --}}
						</tbody>
					</table>
				</div>

			</div>
		</div><!-- /.row -->
		<div class="px2-p">
			@component('components.btn-del-project')
				@slot('controller', 'projects')
				@slot('id', $project->id)
				@slot('code', $project->project_code)
				@slot('name', $project->project_name)
				@slot('branch', $gitUtil->get_remote_default_branch_name())
			@endcomponent
		</div>
		<hr>
		<address class="px2-text-align-center">(C)Pickles 2 Project.</address>
	</div>
</div>
@endsection
