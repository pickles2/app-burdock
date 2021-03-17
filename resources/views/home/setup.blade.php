@php
	$gitUtil = new \App\Helpers\git($project);
    $title = $project->project_name;
@endphp
@extends('layouts.default')
@section('content')
<div class="container">
	<h1>Project "{{ $title }}"</h1>
	@if(env('BROADCAST_DRIVER') === 'redis')
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
				<div>
					@component('components.btn-del-project')
						@slot('controller', 'projects')
						@slot('id', $project->id)
						@slot('code', $project->project_code)
						@slot('name', $project->project_name)
						@slot('branch', $gitUtil->get_remote_default_branch_name())
					@endcomponent
				</div>
			</p>
			<address class="px2-text-align-center">(C)Pickles 2 Project.</address>
		</div>
	@else
		<div class="contents">
			<div class="cont_info"></div>
			<div class="cont_maintask_ui">
				<form class="inline">
					<div class="px2-text-align-center">
						<h2>プロジェクトに Pickles 2 をセットアップします</h2>
						<div class="cont_setup_options px2-text-align-left">
							<h3>セットアップオプション</h3>
							<ul>
								<li>
									<label><input type="radio" name="setup_method" value="pickles2" checked="checked"> Packagist から Pickles 2 プロジェクトテンプレート をセットアップ</label>
								</li>
								<li>
									<label><input type="radio" name="setup_method" value="git"> Gitリポジトリ から クローン</label>
									<div>Repository URL: <input type="text" name="git_url_repository" value="" style="max-width:100%;"></div>
								</li>
							</ul>
						</div>
						<div class="cont_setup_description">
							<p>
								<img src="/common/images/install_image_clip.png" alt="Composer ☓ Packagist ☓ Pickles 2">
							</p>
							<p>
								Pickles 2 の プロジェクトテンプレート を Packagest から自動的に取得し、セットアップを完了します。<br>
							</p>
						</div>
						<p>
							次のボタンを押して、セットアップを続けてください。<br>
						</p>
						<p>
							<button class="px2-btn px2-btn--primary">プロジェクトをセットアップする</button>
						</p>
					</div>
				</form>
			</div>
			<address class="px2-text-align-center">(C)Pickles 2 Project.</address>
		</div>
		<div class="contents" tabindex="-1" style="position: fixed; left: 0px; top: 0px; width: 100%; height: 100%; z-index: 10000;">
			<div style="position: fixed; left: 0px; top: 0px; width: 100%; height: 100%; overflow: hidden; background: rgb(0, 0, 0); opacity: 0.5;"></div>
			<div style="position: absolute; left: 0px; top: 0px; padding-top: 4em; overflow: auto; width: 100%; height: 100%;">
				<div class="dialog_box" style="width: 80%; margin: 3em auto;">
					<h1>Pickles 2 プロジェクトのセットアップ</h1>
					<div>Pickles 2 プロジェクトをセットアップしています。この処理はしばらく時間がかかります。</div>
						<pre style="height: 12em; overflow: auto;">
							<div class="selectable">実行中...</div>
						</pre>
					</div>
				</div>
				<div class="dialog-buttons px2-text-align-center">
					<button disabled="disabled" class="px2-btn">セットアップしています...</button>
				</div>
			</div>
		</div>
	@endif
</div>
@endsection
