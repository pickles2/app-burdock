@php
	$title = __('Edit').': '.$user->name;
@endphp
@extends('layouts.default')
@section('content')

<form action="{{ url('mypage') }}" method="post">
	@csrf
	@method('PUT')

	<div class="px2-form-input-list">
		<ul class="px2-form-input-list__ul">
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="name">{{ __('Name') }}</label></div>
				<div class="px2-form-input-list__input">
					<input id="name" type="text" class="px2-input px2-input--block @if ($errors->has('name')) is-invalid @endif" name="name" value="{{ old('name', $user->name) }}" required autofocus>
						@if ($errors->has('name'))
							<span class="invalid-feedback" role="alert">
								{{ $errors->first('name') }}
							</span>
						@endif
				</div>
			</li>
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="email">{{ __('Email') }}</label></div>
				<div class="px2-form-input-list__input">{{ $user->email }}</div>
			</li>
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="password">{{ __('Password') }}</label></div>
				<div class="px2-form-input-list__input">
					<p>パスワードを変更したい場合のみ入力してください。</p>
					<input id="password" type="password" class="px2-input px2-input--block @if ($errors->has('password')) is-invalid @endif" name="password" value="" autofocus>
						@if ($errors->has('password'))
							<span class="invalid-feedback" role="alert">
								{{ $errors->first('password') }}
							</span>
						@endif
				</div>
			</li>
			<li class="px2-form-input-list__li">
				<div class="px2-form-input-list__label"><label for="password_confirmation">{{ __('Password (Confirmation)') }}</label></div>
				<div class="px2-form-input-list__input">
					<p>パスワードを変更したい場合のみ、確認用にもう一度入力してください。</p>
					<input id="password_confirmation" type="password" class="px2-input px2-input--block @if ($errors->has('password_confirmation')) is-invalid @endif" name="password_confirmation" value="" autofocus>
						@if ($errors->has('password_confirmation'))
							<span class="invalid-feedback" role="alert">
								{{ $errors->first('password_confirmation') }}
							</span>
						@endif
				</div>
			</li>
		</ul>
	</div>


	<div class="px2-form-submit-area">
		<div class="px2-form-submit-area__btns">
			<button type="submit" name="submit" class="px2-btn px2-btn--primary">保存する</button>
		</div>
		<div class="px2-form-submit-area__backward-btns">
			<a href="{{ url('mypage') }}" class="px2-btn">キャンセル</a>
		</div>
	</div>

</form>

@endsection
