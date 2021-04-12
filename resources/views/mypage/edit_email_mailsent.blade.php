@extends('layouts.default')
@section('title', 'メールアドレスを変更する')

@section('content')
<div class="container">

	<p>メールアドレスの変更はまだ完了していません。</p>
	<p>新しいメールアドレス宛にメールをお送りしました。</p>
	<p>メールに記載されているリンクへアクセスして、メールアドレス変更を完了してください。</p>
	<hr />
	<p>
		<a href="{{ url('/mypage/') }}" class="btn btn-primary">戻る</a>
	</p>

</div>
@endsection
