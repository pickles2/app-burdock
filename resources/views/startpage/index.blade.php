@extends('layouts.default')
@section('title') {{ config('app.name') }} @endsection

@section('content')

<div class="container">
	<div class="row justify-content-center">
		<div class="col-md-8">

			<form method="POST" action="{{ route('login') }}">
				@csrf

				<div class="form-group row">
					<label for="email">メールアドレス</label>
					<div class="col-12">
						<input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" required autofocus />

						@if ($errors->has('email'))
							<span class="invalid-feedback" role="alert">
								<strong>{{ $errors->first('email') }}</strong>
							</span>
						@endif
					</div>
				</div>

				<div class="form-group row">
					<label for="email">パスワード</label>
					<div class="col-12">
						<input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required />

						@if ($errors->has('password'))
							<span class="invalid-feedback" role="alert">
								<strong>{{ $errors->first('password') }}</strong>
							</span>
						@endif
					</div>
				</div>

				<div class="form-group row">
					<div class="col-12">
						<div class="form-check">
							<input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>

							<label class="form-check-label" for="remember">ログイン情報を記憶する</label>
						</div>
					</div>
				</div>

				<div class="form-group row justify-content-center">
					<div class="col-6"><button type="submit" class="btn btn-primary btn-block">ログイン</button></div>
				</div>
				<div class="form-group row justify-content-center">
					<div><a href="{{ url('register') }}" class="btn btn-link btn-block">新規会員登録</a></div>
				</div>
				@if (Route::has('password.request'))
				<div class="form-group row justify-content-center">
					<div><a class="btn btn-link" href="{{ route('password.request') }}">パスワードを忘れた方はこちら</a></div>
				</div>
				@endif
			</form>

		</div>
	</div>
</div>

@endsection
