@php
	$title = 'パスワードをリセットする';
@endphp
@extends('emails.layouts.default')


@section('content')

<p>
    {{ __('Click link below and reset password.') }}<br>
    {{ __('If you did not request a password reset, no further action is required.') }}
</p>

<p>
    {{ $actionText }}: <a href="{{ $actionUrl }}">{{ $actionUrl }}</a>
</p>


@endsection
