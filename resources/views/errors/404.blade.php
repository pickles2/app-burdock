@php
    $title = __('Not Found');
@endphp
@extends('layouts.default')
@section('content')

<p><strong>{{ __('Error') }}: <span class="error-code">404</span></strong></p>
<p>{{ __('The requested page does not exist.') }}</p>

@endsection
