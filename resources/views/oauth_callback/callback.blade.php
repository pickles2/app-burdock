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
        <p>{{ $request->code }}</p>

        <p>{{ $request->error }}</p>

        <p>{{ $request->state }}</p>

        <p>{{ $data['access_token'] }}</p>

        <p>{{ var_dump($data['user_info']) }}</p>
    @endif

</div>
@endsection
