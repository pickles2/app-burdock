@php
    $title = __('Projects');
@endphp
@extends('layouts.my')
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
                        <a href="{{ url('projects/' . $project->project_name . '/' . getBranchName() . '/') }}">{{ $project->project_name }}</a>
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
