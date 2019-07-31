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
                    <a href="{{ url('/sitemaps'.'/'.urlencode($project->project_code).'/'.urlencode($branch_name)) }}" class="px2-btn cont_mainmenu">{{ __('Edit Sitemap')}}</a>
                </div>
                <div class="col-sm-3">
                    <a href="{{ url('pages/'.urlencode($project->project_code).'/'.urlencode($branch_name).'/index.html?page_path='.urlencode('/index.html'))}}" class="px2-btn cont_mainmenu">{{ __('Edit Themes')}}</a>
                </div>
                <div class="col-sm-3">
                    <a href="{{ url('pages/'.urlencode($project->project_code).'/'.urlencode($branch_name).'/index.html?page_path='.urlencode('/index.html'))}}" class="px2-btn cont_mainmenu">{{ __('Edit Contents')}}</a>
                </div>
                <div class="col-sm-3">
                    <a href="{{ url('/publish'.'/'.urlencode($project->project_code).'/'.urlencode($branch_name)) }}" class="px2-btn cont_mainmenu">{{ __('To Publish')}}</a>
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
                                <td class="tpl_name selectable">{{ $bd_object->packages->package_list->projects[0]->name }}</td>
                            </tr>
                            <tr>
                                <th>Path</th>
                                <td class="tpl_path selectable">{{ $bd_object->realpath_docroot }}</td>
                            </tr>
                            <tr>
                                <th>Home Directory</th>
                                <td class="tpl_home_dir selectable">{{ $bd_object->packages->package_list->projects[0]->path_homedir }}</td>
                            </tr>
                            <tr>
                                <th>Entry Script</th>
                                <td class="tpl_entry_script selectable">{{ $bd_object->packages->package_list->projects[0]->path }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div><!-- /.row -->
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
