@php
	$title = __('Edit') . ': ' . $project->project_name;
@endphp
@extends('layouts.default')
@section('content')

<form action="{{ url('projects/'.urlencode($project->project_code).'/edit') }}" method="post" autocomplete="off">
	@csrf
	@method('PUT')
	<input type="hidden" name="id" value="{{ $project->id }}">

	<div class="px2-form-input-list">
		<ul class="px2-form-input-list__ul">
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="project_name">{{ __('Project Name') }}</label></div>
				<div class="px2-form-input-list__input">
					<input id="project_name" type="text" class="px2-input px2-input--block @if ($errors->has('project_name')) is-invalid @endif" name="project_name" value="{{ old('project_name', $project->project_name) }}">
					@if ($errors->has('project_name'))
						<span class="invalid-feedback" role="alert">
							{{ $errors->first('project_name') }}
						</span>
					@endif
				</div>
			</li>

			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="project_code">{{ __('Project Code') }}</label></div>
				<div class="px2-form-input-list__input">
					<input id="project_code" type="text" class="px2-input px2-input--block @if ($errors->has('project_code')) is-invalid @endif" name="project_code" value="{{ old('project_code', $project->project_code) }}">
						@if ($errors->has('project_code'))
							<span class="invalid-feedback" role="alert">
								{{ $errors->first('project_code') }}
							</span>
						@endif
				</div>
			</li>
		</ul>
	</div>



	<h3>Gitリモート設定</h3>
	<div class="px2-form-input-list">
		<ul class="px2-form-input-list__ul">
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="git_url">{{ __('Git URL') }}</label></div>
				<div class="px2-form-input-list__input">
					<input id="git_url" type="text" class="px2-input px2-input--block @if ($errors->has('git_url')) is-invalid @endif" name="git_url" value="{{ old('git_url', $project->git_url) }}">
						@if ($errors->has('git_url'))
							<span class="invalid-feedback" role="alert">
								{{ $errors->first('git_url') }}
							</span>
						@endif
				</div>
			</li>

			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="git_username">{{ __('Git Username') }}</label></div>
				<div class="px2-form-input-list__input">
					<input id="git_username" type="text" class="px2-input px2-input--block @if ($errors->has('git_username')) is-invalid @endif" name="git_username" @if(isset($project->git_username))value="{{ old('git_username', \Crypt::decryptString($project->git_username)) }}"@else value="{{ old('git_username') }}"@endif />
						@if ($errors->has('git_username'))
							<span class="invalid-feedback" role="alert">
								{{ $errors->first('git_username') }}
							</span>
						@endif
				</div>
			</li>

			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="git_password">{{ __('Git Password') }}</label></div>
				<div class="px2-form-input-list__input">
					<input id="git_password" type="password" class="px2-input px2-input--block @if ($errors->has('git_password')) is-invalid @endif" name="git_password" value="" />
						@if ($errors->has('git_url'))
							<span class="invalid-feedback" role="alert">
								{{ $errors->first('git_password') }}
							</span>
						@endif
					<ul class="px2-note-list">
						<li>変更する場合のみ入力してください。</li>
					</ul>
					{{-- パスワードをクライアントへ送出しないようにする(セキュリティホールとなる危険性があるため) --}}
				</div>
			</li>

			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="git_main_branch_name">{{ __('Git Main Branch Name') }}</label></div>
				<div class="px2-form-input-list__input">
					<input id="git_main_branch_name" type="text" class="px2-input px2-input--block @if ($errors->has('git_main_branch_name')) is-invalid @endif" name="git_main_branch_name" value="{{ old('git_main_branch_name', $project->git_main_branch_name) }}">
						@if ($errors->has('git_main_branch_name'))
							<span class="invalid-feedback" role="alert">
								{{ $errors->first('git_main_branch_name') }}
							</span>
						@endif
				</div>
			</li>

		</ul>
	</div>


	<h3>認証設定</h3>
	<div class="px2-form-input-list">
		<ul class="px2-form-input-list__ul">
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="basicauth_user_name">ユーザー名</label></div>
				<div class="px2-form-input-list__input">
					<input id="basicauth_user_name" type="text" class="px2-input px2-input--block @if ($errors->has('basicauth_user_name')) is-invalid @endif" name="basicauth_user_name" value="{{ old('basicauth_user_name', $basicauth_user_name) }}" />
						@if ($errors->has('basicauth_user_name'))
							<span class="invalid-feedback" role="alert">
								{{ $errors->first('basicauth_user_name') }}
							</span>
						@endif
					<ul class="px2-note-list">
						<li>ユーザー名 を空白にすると、認証が解除されます。</li>
					</ul>
				</div>
			</li>
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="basicauth_password">パスワード</label></div>
				<div class="px2-form-input-list__input">
					<input id="basicauth_password" type="password" class="px2-input px2-input--block @if ($errors->has('basicauth_password')) is-invalid @endif" name="basicauth_password" value="" />
						@if ($errors->has('basicauth_password'))
							<span class="invalid-feedback" role="alert">
								{{ $errors->first('basicauth_password') }}
							</span>
						@endif
					<ul class="px2-note-list">
						<li>変更する場合のみ入力してください。</li>
					</ul>
				</div>
			</li>
		</ul>
	</div>




	<div class="px2-form-submit-area">
		<div class="px2-form-submit-area__btns">
			<button type="submit" class="px2-btn px2-btn--primary">保存する</button>
		</div>
		<div class="px2-form-submit-area__backward-btns">
			<a href="{{ url('home/'.urlencode($project->project_code)) }}" class="px2-btn">キャンセル</a>
		</div>
	</div>

@endsection
