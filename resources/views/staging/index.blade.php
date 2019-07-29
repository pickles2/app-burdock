@php
	$title = __('Staging');
@endphp
@extends('layouts.px2_project')

@section('content')
<div class="container">
	<h1>ステージング管理</h1>
	<div class="contents">
		{!! $plum_std_out !!}
	</div>
</div>
@endsection

@section('stylesheet')
<!-- plum -->
<link rel="stylesheet" href="/common/lib-plum/res/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="/common/lib-plum/res/styles/common.css">
@endsection
@section('script')
<!-- jquery -->
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>

<!-- plum -->
<script src="/common/lib-plum/res/bootstrap/js/bootstrap.min.js"></script>
<script src="/common/lib-plum/res/scripts/common.js"></script>
@endsection
