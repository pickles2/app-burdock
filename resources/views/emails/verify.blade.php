@php
	$title = 'ユーザー登録を完了してください';
@endphp
@extends('emails.layouts.default')


@section('content')



<h2>リンクをクリックして、ユーザー登録を完了します</h2>
<p>ユーザー登録はまだ完了していません。</p>
<p><a href="{{ $actionUrl }}">ここをクリック</a> するか、次のURLをコピーしてブラウザでアクセスし、ユーザー登録を完了してください。</p>

<p><a href="{{ $actionUrl }}">{{ $actionUrl }}</a></p>


@endsection
