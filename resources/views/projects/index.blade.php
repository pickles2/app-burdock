@php
    $title = __('Projects');
@endphp
@extends('layouts.px2')
@section('content')
<div class="container">
    <h1>{{ $title }}</h1>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>{{ __('Author') }}</th>
                    <th>{{ __('Project Name') }}</th>
                    <th>{{ __('Git URL') }}</th>
                    <th>{{ __('Created') }}</th>
                    <th>{{ __('Updated') }}</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($projects as $project)
                <tr>
                    <td>
                        <a href="{{ url('users/' . $project->user->id) }}">
                            {{ $project->user->name }}
                        </a>
                    </td>
                    <td>
                        <a href="{{ url('projects/'.urlencode($project->project_code).'/'.urlencode(get_git_remote_default_branch_name()) . '/') }}">{{ $project->project_name }}</a>
                    </td>
                    <td>{{ $project->git_url }}</td>
                    <td>{{ $project->created_at }}</td>
                    <td>{{ $project->updated_at }}</td>
                 </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    {{-- ページネーション --}}
    {{ $projects->links() }}
</div>
@endsection
