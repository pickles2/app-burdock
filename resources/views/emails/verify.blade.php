@php
	$title = 'ユーザー登録を完了する';
@endphp
@extends('emails.layouts.default')


@section('content')


<p>
    {{ __('Please click the link below to verify your email address.') }}<br>
    {{ __('If you did not create an account, no further action is required.') }}
</p>
<p>
    <a href="{{ $actionUrl }}">{{ $actionText }}</a>
</p>



@endsection
