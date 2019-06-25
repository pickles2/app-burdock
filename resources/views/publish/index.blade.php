@php
	$title = __('Publish');
@endphp
@extends('layouts.px2_project')

@section('content')
<div class="container">
	<h1>パブリッシュ</h1>
	@if(env('BROADCAST_DRIVER') === 'redis')
	{{-- Vueコンポーネント --}}
		<div id="app">
			<publish-component project-code="{{ $project->project_code}}" branch-name="{{ $branch_name }}"></publish-component>
		</div>
	@else
		<div class="contents">
			<p><a class="px2-btn px2-btn--primary" href="{{ url('/publish/'.$project->project_code.'/'.$branch_name.'/publish_run') }}">フルパブリッシュ</a></p>
		</div>
	@endif
</div>
@endsection
