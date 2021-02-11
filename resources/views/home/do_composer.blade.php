@php
	$title = $project->project_name;
@endphp
@extends('layouts.default')
@section('content')
<div class="container">
	<h1 id="project-title" style="margin-bottom: 50px;">Project "{{ $title }}"</h1>
	<div class="contents">
		<div class="row">
			<div class="col-sm-12">
                <p>Composer ライブラリを更新してください。</p>
                <p><a href="{{ url('composer'.'/'.urlencode($project->project_code).'/'.urlencode($branch_name)) }}">Compsoer 操作</a></p>
			</div>
		</div><!-- /.row -->
	</div>
</div>
@endsection
