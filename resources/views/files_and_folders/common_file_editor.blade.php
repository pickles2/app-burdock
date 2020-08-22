@php
	$title = __('Files And Folders: File Editor');
@endphp
@extends('layouts.px2_plain')

@section('content')
<div class="container">
	<h1>{{ __('File Editor') }}</h1>
	<div class="contents">
		<div id="cont-editor"></div>
	</div>
</div>
@endsection

@section('stylesheet')
<link rel="stylesheet" href="/common/common-file-editor/dist/common-file-editor.css">
@endsection

@section('script')
<script src="/common/common-file-editor/dist/common-file-editor.js"></script>

<script>
	$(window).on('load', function(){
		var commonFileEditor = window.commonFileEditor = new CommonFileEditor(
			document.getElementById('cont-editor'),
			{
				"read": function(filename, callback){ // required
					$.ajax({
						type : 'post',
						url : "/files-and-folders/{{ $project->project_code }}/{{ $branch_name }}/common-file-editor/gpi",
						headers: {
							'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
						},
						contentType: 'application/json',
						dataType: 'json',
						data: JSON.stringify({
 							'method': 'read',
 							'filename': filename
						}),
						success: function(data){
							callback(data);
						}
					});
				},
				"write": function(filename, base64, callback){ // required
					$.ajax({
						type : 'post',
						url : "/files-and-folders/{{ $project->project_code }}/{{ $branch_name }}/common-file-editor/gpi",
						headers: {
							'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
						},
						contentType: 'application/json',
						dataType: 'json',
						data: JSON.stringify({
 							'method': 'write',
 							'filename': filename,
 							'base64': base64
						}),
						success: function(data){
							callback(data);
						}
					});
				},
				"onemptytab": function(){
					window.close();
				}
			}
		);

		commonFileEditor.init(function(){
			console.log('ready.');
            commonFileEditor.preview( '{{ $filename }}' );
		});

	});
</script>
@endsection
