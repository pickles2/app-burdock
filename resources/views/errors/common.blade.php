@php
	$title = __('Error');
@endphp
@extends('layouts.default')
@section('content')

<div>
{{ $error_message }}
</div>

@endsection

@section('foot')
@endsection
