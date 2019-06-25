@php
	$title = __('Files And Folders');
@endphp
@extends('layouts.px2_project')

@section('content')
<div class="container">
	<h1>{{ __('Files And Folders') }}</h1>
	<div class="contents">
		<div id="cont-finder"></div>
	</div>
</div>
@endsection

@section('stylesheet')
<link rel="stylesheet" href="/common/remote-finder/dist/remote-finder.css">
@endsection

@section('script')
<script src="/common/remote-finder/dist/remote-finder.js"></script>

<script>
	$(window).on('load', function(){
		var remoteFinder = window.remoteFinder = new RemoteFinder(
			document.getElementById('cont-finder'),
			{
				"gpiBridge": function(input, callback){ // required
					$.ajax({
						type : 'post',
						url : "/files-and-folders/{{ $project->project_code }}/{{ $branch_name }}/gpi",
						headers: {
							'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
						},
						contentType: 'application/json',
						dataType: 'json',
						data: JSON.stringify({
 							'data': JSON.stringify(input)
						}),
						success: function(data){
							callback(data);
						}
					});
				},
				"open": function(fileinfo, callback){
					alert('ファイル ' + fileinfo.path + ' を開きました。');
					callback(true);
				},
				"mkdir": function(current_dir, callback){
					var foldername = prompt('Folder name:');
					if( !foldername ){ return; }
					callback( foldername );
					return;
				},
				"mkfile": function(current_dir, callback){
					var filename = prompt('File name:');
					if( !filename ){ return; }
					callback( filename );
					return;
				},
				"rename": function(renameFrom, callback){
					var renameTo = prompt('Rename from '+renameFrom+' to:', renameFrom);
					callback( renameFrom, renameTo );
					return;
				},
				"remove": function(path_target, callback){
					if( !confirm('Really?') ){
						return;
					}
					callback();
					return;
				},
				"mkdir": function(current_dir, callback){
					var foldername = prompt('Folder name:');
					if( !foldername ){ return; }
					callback( foldername );
					return;
				},
				"mkdir": function(current_dir, callback){
					var foldername = prompt('Folder name:');
					if( !foldername ){ return; }
					callback( foldername );
					return;
				}
			}
		);
		// console.log(remoteFinder);
		remoteFinder.init('/', {}, function(){
			console.log('ready.');
		});

	});
</script>
@endsection
