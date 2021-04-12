@php
	$title = __('Edit').': '.$user->name;
@endphp
@extends('layouts.default')
@section('content')

<form action="{{ url('mypage') }}" method="post">
	@csrf
	@method('PUT')
	<div class="form-group">
		<label for="name">{{ __('Name') }}</label>
		<input id="name" type="text" class="form-control @if ($errors->has('name')) is-invalid @endif" name="name" value="{{ old('name', $user->name) }}" required autofocus>
			@if ($errors->has('name'))
				<span class="invalid-feedback" role="alert">
					{{ $errors->first('name') }}
				</span>
			@endif
	</div>
	<div class="form-group">
		<label for="email">{{ __('Email') }}</label>
		{{ $user->email }}
	</div>
	<div class="form-group">
		<label for="password">{{ __('Password') }}</label>
		<p>パスワードを変更したい場合のみ入力してください。</p>
		<input id="password" type="password" class="form-control @if ($errors->has('password')) is-invalid @endif" name="password" value="" autofocus>
			@if ($errors->has('password'))
				<span class="invalid-feedback" role="alert">
					{{ $errors->first('password') }}
				</span>
			@endif
	</div>
	<div class="form-group">
		<label for="password_confirmation">{{ __('Password (Confirmation)') }}</label>
		<p>パスワードを変更したい場合のみ、確認用にもう一度入力してください。</p>
		<input id="password_confirmation" type="password" class="form-control @if ($errors->has('password_confirmation')) is-invalid @endif" name="password_confirmation" value="" autofocus>
			@if ($errors->has('password_confirmation'))
				<span class="invalid-feedback" role="alert">
					{{ $errors->first('password_confirmation') }}
				</span>
			@endif
	</div>
	<button type="submit" name="submit" class="btn btn-primary">{{ __('Submit') }}</button>
</form>

@endsection
