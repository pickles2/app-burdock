@php
	$title = 'パスワードを再設定する';
@endphp
@extends('emails.layouts.default')


@section('content')


<h2>リンクをクリックして、パスワード再設定を完了します</h2>
<p>パスワードの再設定はまだ完了していません。</p>
<p><a href="{{ $actionUrl }}">ここをクリック</a> するか、次のURLをコピーしてブラウザでアクセスし、パスワード再設定を完了してください。</p>

<p><a href="{{ $actionUrl }}">{{ $actionUrl }}</a></p>


<h2>このメールに心当たりがない場合</h2>
<p>このメールに心当たりがない場合は、<strong>リンクをクリックせずにこのメールを削除してください</strong>。</p>


@endsection
