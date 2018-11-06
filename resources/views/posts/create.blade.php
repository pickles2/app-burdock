@php
    $title = __('Create Project');
@endphp
@extends('layouts.my')
@section('content')
<div class="container">
    <h1>{{ $title }}</h1>
    <form action="{{ url('posts') }}" method="post">
        @csrf
        @method('POST')
        <div class="form-group">
            <label for="title">{{ __('Project Name') }}</label>
            <input id="title" type="text" class="form-control @if ($errors->has('title')) is-invalid @endif" name="title" value="{{ old('title') }}" required autofocus>
                @if ($errors->has('title'))
                    <span class="invalid-feedback" role="alert">
                        {{ $errors->first('title') }}
                    </span>
                @endif
        </div>
        <div class="form-group">
            <label for="body">{{ __('Git URL') }}</label>
            <input id="body" type="text" class="form-control @if ($errors->has('body')) is-invalid @endif" name="body"  value="{{ old('body') }}">
                @if ($errors->has('body'))
                    <span class="invalid-feedback" role="alert">
                        {{ $errors->first('body') }}
                    </span>
                @endif
        </div>
        <button type="submit" name="submit" class="btn btn-primary">{{ __('Submit') }}</button>
    </form>
</div>
@endsection
