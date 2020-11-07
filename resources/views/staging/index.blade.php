@php
	$title = __('Staging');
@endphp
@extends('layouts.px2_project')

@section('content')
<div class="container">
	<h1>ステージング管理</h1>
	<div class="contents">


<div id="cont-plum-area"></div>

	</div>
</div>
@endsection

@section('stylesheet')
<!-- plum -->
<link rel="stylesheet" href="/common/lib-plum/dist/plum.css" />
@endsection
@section('script')
<!-- plum -->
<script src="/common/lib-plum/dist/plum.js"></script>

<script>
var method = 'post';
var apiUrl = "/staging/{{ $project->project_code }}/{{ $branch_name }}/gpi";

var plum = new Plum(
	document.getElementById('cont-plum-area'),
	{
		'gpiBridge': function(data, callback){
			$.ajax({
				'type' : method,
				'url' : apiUrl,
				'headers': {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				'dataType': 'json',
				'data': {
					'data': data
				},
				'success': function(result){
					callback(result);
				}
			});
		}
	}
);
plum.init();
</script>


@endsection
