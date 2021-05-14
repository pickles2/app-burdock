@extends('layouts.default')
@section('title', config('app.name'))

@section('first-view')
<div class="cont-top-jumbotron">
	<h1><img src="common/images/logo_2017.svg" style="width:50%; max-height:120px;" alt="Pickles 2" /></h1>
	<p>Web Tool</p>
</div>
@endsection

@section('content')
<div class="row">
	<div class="col-sm-6">
		<h2>プロジェクトを選択してください</h2>
		<div class="cont_project_list unit">
			<div class="list-group">
				{{-- 全プロジェクトが見える用に一時的に変更した箇所 --}}
				{{-- @foreach($user->projects as $project)
					<a class="list-group-item" href="{{ url('home/'.$project->project_code) }}">{{ $project->project_name }}</a>
				@endforeach --}}
				@foreach($projects as $project)
					<a class="list-group-item" href="{{ url('home/'.$project->project_code) }}">{{ $project->project_name }}</a>
				@endforeach
			</div>
		</div>
	</div>
	<div class="col-sm-6">
		<h2>新規プロジェクト</h2>
		<div class="cont_project_form px2-p">
			<form action="{{ url('projects') }}" method="post">
				@csrf
				<div class="px2-p">
				<table class="px2-table" style="width:100%;">
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
										<span class="invalid-feedback text-danger" role="alert">
											{{ $errors->first('project_name') }}
										</span>
									@endif
								</div>
							</td>
						</tr>
						<tr>
							<th>Project Code <span class="must">Required</span></th>
							<td>
								<p>システムが内部で使用する名前を入力してください。この文字列はプロジェクトのURLの一部にも使われます。半角英数字と <code>-</code>(ハイフン)、 <code>_</code>(アンダースコア) が使えます。</p>
								<div class="overflow:hidden;">
									<input type="text" name="project_code" class="form-control @if($errors->has('project_code'))is-invalid @endif" value="{{ old('project_code') }}" placeholder="Your Project Name">
									@if ($errors->has('project_code'))
										<span class="invalid-feedback text-danger" role="alert">
											{{ $errors->first('project_code') }}
										</span>
									@endif
								</div>
							</td>
						</tr>
					</tbody>
				</table>
				</div>
				<p class="px2-text-align-center">
					<button type="submit" name="submit" class="px2-btn px2-btn--primary px2-btn--block">新規プロジェクト作成</button>
				</p>
			</form>
		</div>
	</div>
</div>


<div class="cont-top-footer">
	@if ( config('app.debug') )
	<ul class="px2-horizontal-list px2-horizontal-list--center">
		<li><a href="/system-maintenance" class="px2-a">システムメンテナンス</a></li>
	</ul>
	@endif
	<p>(C)Pickles 2 Project.</p>
</div>

@endsection

@section('head')
<link href="{{ asset('/cont/dashboard/style.css') }}" rel="stylesheet" />
@endsection

@section('foot')
<script src="{{ asset('/cont/dashboard/script.js') }}"></script>
@endsection
