@php
    $title = env('APP_NAME');
@endphp

@extends('layouts.px2_project')
@section('title', 'Burdock')
@section('content')
<div class="theme_wrap">
	<div class="contents" style="margin: 0px; padding: 0px; left: 0px; top: 33px; right: 0px; height: 892px;">
    	<div class="cont_top_jumbotron">
    		<p class="center">
                <img src="common/images/logo_2017.svg" style="width:50%; max-height:120px;" alt="Pickles 2">
            </p>
    		<p class="center">Web Tool</p>
    	</div>
    	<div class="container">
    		<div class="row">
    			<div class="col-sm-6">
    				<h1>プロジェクトを選択してください</h1>
    				<div class="cont_project_list unit">
                        <div class="list-group">
                            @foreach($user->projects as $project)
                                <a class="list-group-item" href="{{ url('projects/'.$project->project_name.'/'.get_git_remote_default_branch_name()) }}">{{ $project->project_name }}</a>
                            @endforeach
                        </div>
                    </div>
    			</div>
    			<div class="col-sm-6">
    				<h2>新規プロジェクト</h2>
    				<div class="cont_project_form unit">
    					<form action="{{ url('projects') }}" method="post">
                            @csrf
    						<table class="form_elements" style="width:100%;">
    							<colgroup>
                                    <col width="30%">
                                    <col width="70%">
                                </colgroup>
    							<tbody>
                                    <tr>
    								<th>Project Name <span class="must">Required</span></th>
    								<td>
    									<p>他のプロジェクトと区別できる表示名を入力してください。日本語やその他のマルチバイト文字も使えます。</p>
    									<div class="overflow:hidden;">
                                            <input type="text" name="project_name" class="form-control @if($errors->has('project_name'))is-invalid @endif" value="{{ old('project_name') }}" placeholder="Your Project Name">
                                            @if ($errors->has('project_name'))
                                                <span class="invalid-feedback" role="alert">
                                                    {{ $errors->first('project_name') }}
                                                </span>
                                            @endif
                                        </div>
    								</td>
    							</tr>
    							<tr>
    								<th>Git URL <span class="must">Required</span></th>
    								<td>
    									<p>プロジェクトをコミットするGit URL（HTTPS）を入力してください。</p>
    									<p>例：<code>https://github.com/pickles2/app-burdock.git</code></p>
    									<div class="overflow:hidden;">
                                            <input type="text" name="git_url" class="form-control @if($errors->has('git_url'))is-invalid @endif" value="{{ old('git_url') }}" placeholder="Your Git URL">
                                            @if ($errors->has('git_url'))
                                                <span class="invalid-feedback" role="alert">
                                                    {{ $errors->first('git_url') }}
                                                </span>
                                            @endif
                                        </div>
    								</td>
    							</tr>
                                <tr>
    								<th>Git Username <span class="must">Required</span></th>
    								<td>
    									<div class="overflow:hidden;">
                                            <input type="text" name="git_username" class="form-control @if($errors->has('git_username'))is-invalid @endif" value="{{ old('git_username') }}" placeholder="Your Git Username">
                                            @if ($errors->has('git_username'))
                                                <span class="invalid-feedback" role="alert">
                                                    {{ $errors->first('git_username') }}
                                                </span>
                                            @endif
                                        </div>
    								</td>
    							</tr>
                                <tr>
    								<th>Git Password <span class="must">Required</span></th>
    								<td>
    									<div class="overflow:hidden;">
                                            <input type="password" name="git_password" class="form-control @if($errors->has('git_password'))is-invalid @endif" value="{{ old('git_password') }}" placeholder="Your Git Password">
                                            @if ($errors->has('git_password'))
                                                <span class="invalid-feedback" role="alert">
                                                    {{ $errors->first('git_password') }}
                                                </span>
                                            @endif
                                        </div>
    								</td>
    							</tr>
    						</tbody>
                        </table>
    					<p class="center">
                            <button type="submit" name="submit" class="px2-btn px2-btn--primary px2-btn--block" onclick="uploadSitemap(event);">新規プロジェクト作成</button>
                        </p>
    				</form>
    			</div>
    		</div>
    	</div>
       </div>
	<div class="cont_top_footer">
		<p>(C)Pickles 2 Project.</p>
	</div>
</div>
@endsection
