@php
	$title = __('Staging');
@endphp
@extends('layouts.default')

@section('content')

@if ( $error )
<div>
	<p>{{ $error_message }}</p>
</div>
@else
<div id="cont-plum-area"></div>
@endif

@endsection



@section('head')
@if ( $error )
@else
<!-- plum -->
<link rel="stylesheet" href="/common/lib-plum/dist/plum.css" />
@endif
@endsection



@section('foot')

@if ( $error )
@else
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

window.Echo.channel('{{ $project->project_code }}___plum-message.{{ Auth::id() }}').listen('AsyncPlumEvent', (message) => {
	console.log('------ Broadcast message received:', message);
	plum.broadcastMessage(message.message);
});

</script>


@endif
@endsection
