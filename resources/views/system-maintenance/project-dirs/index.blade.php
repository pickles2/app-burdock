@extends('layouts.default')
@section('title', 'プロジェクトディレクトリ一覧')
@section('content')

<div class="px2-p">
	<table class="px2-table">
		<thead>
			<tr>
				<th>Project Code</th>
				<th>Home</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
		@foreach ($projectDirs as $tmp_project)
			<tr>
				<th>{{ $tmp_project["project_code"] }}</th>
				<td>
				@if ($tmp_project["exists_on_db"])
					<a href="{{ url( '/home/'.$tmp_project['project_code'] ) }}" class="px2-a">プロジェクトのホーム画面を開く</a>
				@else
					<div>データベースに定義されていません</div>
				@endif
				</td>
				<td><a href="{{ url( '/system-maintenance/project-dirs/'.$tmp_project['project_code'] ) }}" class="px2-btn">詳細</a></td>
			</tr>
		@endforeach
		</tbody>
	</table>
</div>


<p>
	<a href="{{ url('/system-maintenance') }}" class="px2-btn">戻る</a>
</p>

@endsection
