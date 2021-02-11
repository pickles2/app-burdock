@php
    $title = __('Internal Server Error');
@endphp
@extends('layouts.default')
@section('content')

<p><strong>{{ __('Error') }}: <span class="error-code">500</span></strong></p>
<p>{{ __('The server was unable to complete your request.') }}</p>

@endsection
