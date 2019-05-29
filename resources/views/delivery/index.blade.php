@php
	$title = __('Delivery');
@endphp
@extends('layouts.px2_project')

@section('content')
<div class="container">
	<h1>配信管理</h1>
	<div class="contents">
		{!! $indigo_std_out !!}
	</div>
</div>
@endsection

@section('stylesheet')
<link rel="stylesheet" href="/common/lib-indigo/res/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="/common/lib-indigo/res/styles/common.css">

<!-- datepicker -->
<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css" />
@endsection
@section('script')
<script src="/common/lib-indigo/res/bootstrap/js/bootstrap.min.js"></script>
<script src="/common/lib-indigo/res/scripts/common.js"></script>

<!-- datepicker -->
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1/i18n/jquery.ui.datepicker-ja.min.js"></script>

<script>
	$(function() {
		
		var dateFormat = 'yy-mm-dd';
		
		$.datepicker.setDefaults($.datepicker.regional["ja"]);
		
		$("#datepicker").datepicker({
			   dateFormat: dateFormat
		});
	});
</script>
@endsection
