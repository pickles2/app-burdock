@php
	$title = __('Publish');
@endphp
@extends('layouts.default')

@section('head')
	<link rel="stylesheet" href="{{ asset('/cont/publish/style.css') }}" type="text/css">
@endsection

@section('content')

	<div id="app">
		<publish-component project-code="{{ $project->project_code}}" branch-name="{{ $branch_name }}" exists-publish-log="{{ $exists_publish_log }}" exists-alert-log="{{ $exists_alert_log }}" exists-applock="{{ $exists_applock }}" publish-files="{{ $publish_files }}" alert-files="{{ $alert_files }}" diff-seconds="{{ $diff_seconds }}" session-my-status="{{ session('bd_flash_message') }}" publish-patterns="{{ json_encode($publish_patterns) }}"></publish-component>
	</div>

@endsection
