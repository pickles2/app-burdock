@php
    $title = __('Edit') . ': ' . $project->project_name;
@endphp
@extends('layouts.px2_project')
@section('content')
<div class="container">
    <h1>{{ $title }}</h1>
    <form action="{{ url('projects/'.$project->project_name.'/'.$branch_name) }}" method="post">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="project_name">{{ __('Project Name') }}</label>
            <input id="project_name" type="text" class="form-control @if ($errors->has('project_name')) is-invalid @endif" name="project_name" value="{{ old('project_name', $project->project_name) }}" required autofocus>
                @if ($errors->has('project_name'))
                    <span class="invalid-feedback" role="alert">
                        {{ $errors->first('project_name') }}
                    </span>
                @endif
        </div>
        <div class="form-group">
            <label for="git_url">{{ __('Git URL') }}</label>
            <input id="git_url" type="text" class="form-control @if ($errors->has('git_url')) is-invalid @endif" name="git_url" value="{{ old('git_url', $project->git_url) }}">
                @if ($errors->has('git_url'))
                    <span class="invalid-feedback" role="alert">
                        {{ $errors->first('git_url') }}
                    </span>
                @endif
        </div>
		<div class="form-group">
            <label for="git_username">{{ __('Git Username') }}</label>
            <input id="git_username" type="text" class="form-control @if ($errors->has('git_username')) is-invalid @endif" name="git_username" value="{{ old('git_username', \Crypt::decryptString($project->git_username)) }}">
                @if ($errors->has('git_username'))
                    <span class="invalid-feedback" role="alert">
                        {{ $errors->first('git_username') }}
                    </span>
                @endif
        </div>
		<div class="form-group">
            <label for="git_password">{{ __('Git Password') }}</label>
            <input id="git_password" type="text" class="form-control @if ($errors->has('git_password')) is-invalid @endif" name="git_password" value="{{ old('git_password', Crypt::decryptString($project->git_password)) }}">
                @if ($errors->has('git_url'))
                    <span class="invalid-feedback" role="alert">
                        {{ $errors->first('git_password') }}
                    </span>
                @endif
        </div>
        <button type="submit" name="submit" class="btn btn-primary">{{ __('Submit') }}</button>
    </form>
</div>
@endsection
