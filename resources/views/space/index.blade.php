@extends('layouts.default')
@section('title', 'スペースの管理')
@section('content')

<p>スペースを管理します。</p>

<div class="px2-p">
	<ul class="px2-vertical-list">
		<li><a href="/space/members" class="px2-a">登録ユーザーの一覧</a></li>
		<li><a href="/space/project" class="px2-a">プロジェクトの一覧</a></li>
		<li><a href="/space/event-logs" class="px2-a">イベントログ</a></li>
		<li><a href="/space/bd_data_dir" class="px2-a">データディレクトリの管理</a></li>
	</ul>
</div>


@endsection
