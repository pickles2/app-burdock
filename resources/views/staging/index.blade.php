@php
	$title = __('Staging');
@endphp
@extends('layouts.default')

@section('content')

<div id="cont-plum-area"></div>

@endsection

@section('head')
<!-- plum -->
<link rel="stylesheet" href="/common/lib-plum/dist/plum.css" />
@endsection

@section('foot')
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
