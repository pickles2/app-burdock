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
			<publish-component project-code="{{ $project->project_code}}" branch-name="{{ $branch_name }}" exists-publish-log="{{ $exists_publish_log }}" exists-alert-log="{{ $exists_alert_log }}" exists-applock="{{ $exists_applock }}" publish-files="{{ $publish_files }}" alert-files="{{ $alert_files }}" diff-seconds="{{ $diff_seconds }}" session-my-status="{{ session('my_status') }}"></publish-component>
		</div>
	@else
		<div class="contents">
			<p><a class="px2-btn px2-btn--primary" href="{{ url('/publish/'.$project->project_code.'/'.$branch_name.'/publish_run') }}">フルパブリッシュ</a></p>
		</div>
	@endif
</div>
@endsection
@section('script')
	<script src="{{ asset('/js/app.js') }}"></script>
@endsection
