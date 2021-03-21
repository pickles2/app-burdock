@php
	$gitUtil = new \App\Helpers\git($project);
	$bootstrap = 3;
    $title = $project->project_name;
@endphp
@extends('layouts.default')


@section('head')
<link href="{{ asset('/cont/home/style.css') }}" rel="stylesheet" />
@endsection

@section('content')
<div class="container">

	<h1>Project "{{ $title }}"</h1>

	{{-- Vueコンポーネント --}}
	<div id="app">
		<setup-component
			project-code="{{ $project->project_code}}"
			branch-name="{{ $branch_name }}"
			initializing-method="{{ $initializing_request->initializing_method }}"
			git-remote="{{ $initializing_request->git_remote }}"
			git-user-name="{{ $initializing_request->git_user_name }}"
			composer-vendor-name="{{ $initializing_request->composer_vendor_name }}"
			composer-project-name="{{ $initializing_request->composer_project_name }}"></setup-component>
	</div>
	<div class="contents">
		<p>
			<button class="px2-btn px2-btn--danger px2-btn--block" onclick="window.bdApp.modalDeleteProject('{{ $project->project_code }}');">このプロジェクトを削除</button>
		</p>
		<address class="px2-text-align-center">(C)Pickles 2 Project.</address>
	</div>

</div>
@endsection
