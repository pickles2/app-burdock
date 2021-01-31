@php
	$title = __('Error');
@endphp
@extends('layouts.px2_project')
@section('content')
<div class="container">
	<h1>エラー</h1>
</div>
<div class="contents">
	<div class="container">
		<div class="row">
			<div class="col-xs-12">
{{ $error_message }}
            </div>
		</div>
	</div>
</div>
@endsection
@section('script')
@endsection
