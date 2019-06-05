@php
    $title = 'OAuth Callback';
@endphp
@extends('layouts.px2_project')
@section('content')
<div class="container">
    <h1>OAuth Callback</h1>

    @if(isset($error) && $error)
        <p>{{ $error->message }}</p>
    @else
        <p>認証に成功しました。</p>
    @endif

    <div>
        <a href="{{ url('/') }}" class="btn btn-primary">ダッシュボードへ戻る</a>
    </div>
</div>
@endsection
