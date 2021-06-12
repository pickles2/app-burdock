@php
	$title = __('Project List');
@endphp
@extends('layouts.default')

@section('content')

<div>
	<p>登録されているプロジェクトの一覧を表示します。</p>
</div>

<h2>登録プロジェクト</h2>

@if( count($activeProjects) )
<div class="px2-p">
<table class="px2-table" style="width:100%;">
	<thead>
	<tr>
		<th>プロジェクト名</th>
		<th>プロジェクトコード</th>
		<th>登録日</th>
	</tr>
	</thead>
	<tbody>
@foreach($activeProjects as $project)
	<tr>
		<td>{{ $project->project_name }}</td>
		<td>{{ $project->project_code }}</td>
		<td>{{ $datetime_to_display($project->created_at) }}</td>
	</tr>
@endforeach
	</tbody>
</table>
</div>

@else
<p>登録プロジェクトはありません。</p>
@endif

<h2>削除プロジェクト</h2>
<p>
	次にリストされたプロジェクトは、削除手続きを行っており、現在は無効です。<br />
	これらのデータは、削除してからおよそ <strong>{{ $softdelete_retention_period }}</strong> 後に、完全に消去されます。<br />
</p>

@if( count($softDeletedProjects) )
<div class="px2-p">
<table class="px2-table" style="width:100%;">
	<thead>
	<tr>
		<th>プロジェクト名</th>
		<th>プロジェクトコード</th>
		<th>登録日</th>
		<th>退会日</th>
	</tr>
	</thead>
	<tbody>
@foreach($softDeletedProjects as $project)
	<tr>
		<td>{{ $project->project_name }}</td>
		<td>{{ $project->project_code }}</td>
		<td>{{ $datetime_to_display($project->created_at) }}</td>
		<td>{{ $datetime_to_display($project->deleted_at) }}</td>
	</tr>
@endforeach
	</tbody>
</table>
</div>

@else
<p>削除済みのプロジェクトはありません。</p>
@endif


@endsection
