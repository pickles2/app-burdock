@php
    $title = __('Edit').': '.$user->name;
@endphp
@extends('layouts.px2_project')
@section('content')
<div class="container">
    <h1>{{ $title }}</h1>
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
            <label for="password">{{ __('Password') }}</label>
            <input id="password" type="password" class="form-control @if ($errors->has('password')) is-invalid @endif" name="password" value="" autofocus>
                @if ($errors->has('password'))
                    <span class="invalid-feedback" role="alert">
                        {{ $errors->first('password') }}
                    </span>
                @endif
        </div>
        <div class="form-group">
            <label for="password_confirmation">{{ __('Password (Confirmation)') }}</label>
            <input id="password_confirmation" type="password" class="form-control @if ($errors->has('password_confirmation')) is-invalid @endif" name="password_confirmation" value="" autofocus>
                @if ($errors->has('password_confirmation'))
                    <span class="invalid-feedback" role="alert">
                        {{ $errors->first('password_confirmation') }}
                    </span>
                @endif
        </div>
        <button type="submit" name="submit" class="btn btn-primary">{{ __('Submit') }}</button>
    </form>
</div>
@endsection
