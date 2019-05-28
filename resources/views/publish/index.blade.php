@php
	$title = __('Publish');
@endphp
@extends('layouts.px2_project')

@section('content')
<div class="container">
	<h1>パブリッシュ</h1>
	<div class="contents">
		<p><a class="px2-btn px2-btn--primary" href="{{ url('/publish/'.$project->project_name.'/'.$branch_name.'/publish_run') }}">フルパブリッシュ</a></p>
	</div>
</div>
@endsection
