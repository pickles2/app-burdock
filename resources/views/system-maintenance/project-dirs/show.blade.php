@php
	$title = 'プロジェクトディレクトリ詳細';
@endphp
@extends('layouts.px2_project')
@section('title', $title)
@section('content')
<div class="container">
	<h1>プロジェクトディレクトリ詳細</h1>
	<div class="contents">

		<h2>ディレクトリ情報</h2>
		<table class="px2-table">
			<tbody>
				<tr>
					<th>Project Code</th>
					<td>{{ $project_code }}</td>
				</tr>
			</tbody>
		</table>

		<h2>データベース情報</h2>
		@if ($project_obj)
		<table class="px2-table">
			<tbody>
				<tr>
					<th>Project ID</th>
					<td>{{ $project_obj->id }}</td>
				</tr>
				<tr>
					<th>Project Name</th>
					<td>{{ $project_obj->project_name }}</td>
				</tr>
				<tr>
					<th>Project Code</th>
					<td>{{ $project_obj->project_code }}</td>
				</tr>
			</tbody>
		</table>
		@else
		<p>データベースに該当レコードはありません。</p>
		@endif

		<hr />

		@if ($project_obj)
			<ul class="px2-horizontal-list">
				<li><a href="{{ url( '/home/'.$project_code ) }}" class="px2-btn px2-btn--primary">プロジェクトのホーム画面を開く</a></li>
			</ul>
		@else
			<ul class="px2-horizontal-list">
				<li><a href="{{ url( '/system-maintenance/project-dirs/'.$project_code.'/store' ) }}" class="px2-btn">データベースに登録する</a></li>
				<!-- <li><a href="{{ url( '/system-maintenance/project-dirs/'.$project_code.'/delete' ) }}" class="px2-btn px2-btn--danger">削除する</a></li> -->
			</ul>
		@endif

		<hr />

		<p>
			<a href="{{ url('/system-maintenance/project-dirs') }}" class="px2-btn">戻る</a>
		</p>
	</div>
</div>
@endsection
