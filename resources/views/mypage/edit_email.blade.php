@extends('layouts.default')
@section('title', 'メールアドレスを変更する')

@section('content')
<div class="container">

	<form action="{{ url('mypage/edit_email') }}" method="post">
		@csrf
		@method('POST')

		<div class="px2-form-input-list">
			<ul class="px2-form-input-list__ul">
				<li class="px2-form-input-list__li">
					<div class="px2-form-input-list__label"><label for="email">新しいメールアドレス</label></div>
					<div class="px2-form-input-list__input">
						<input id="email" type="text" class="px2-input px2-input--block @if ($errors->has('email')) is-invalid @endif" name="email" value="{{ old('email', $profile->email) }}" required autofocus>
							@if ($errors->has('email'))
								<span class="invalid-feedback" role="alert">
									{{ $errors->first('email') }}
								</span>
							@endif
					</div>
				</li>
			</ul>
		</div>

		<!--
		<ul>
			<li><label><input type="radio" name="method" value="" checked /> 古いメールアドレスを上書きし、新しいメールアドレスをログインに使う</label></li>
			<li><label><input type="radio" name="method" value="backup_and_update" /> 古いメールアドレスも残したまま、新しいメールアドレスをログインに使う</label></li>
			<li><label><input type="radio" name="method" value="add_new" /> ログインに使うメールアドレスはそのままにして、新しいメールアドレスを追加する</label></li>
		</ul>
		-->
		<input type="hidden" name="method" value="" />

		<div class="px2-form-submit-area">
			<div class="px2-form-submit-area__btns">
				<button type="submit" name="submit" class="px2-btn px2-btn--primary">変更する</button>
			</div>
			<div class="px2-form-submit-area__backward-btns">
				<a href="{{ url('/mypage/') }}" class="px2-btn">キャンセル</a>
			</div>
		</div>
	</form>

</div>
@endsection
