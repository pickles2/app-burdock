@php
    $title = __('Forbidden');
@endphp
@extends('layouts.default')
@section('content')

<p><strong>{{ __('Error') }}: <span class="error-code">403</span></strong></p>
<p>{{ __('You do not have permission to access this page.') }}</p>

@endsection
