@php
	$title = __('Files And Folders');
@endphp
@extends('layouts.px2_project')

@section('content')
<div class="container">
	<h1>{{ __('Files And Folders'); }}</h1>
	<div class="contents">
		<div id="cont-finder"></div>
	</div>
</div>
@endsection

@section('stylesheet')
<link rel="stylesheet" href="/common/remote-finder/dist/remote-finder.css">
@endsection

@section('script')
<script src="/common/remote-finder/dist/scripts/remote-finder.js"></script>

<script>
	$(function() {
		var remoteFinder = window.remoteFinder = new RemoteFinder(
			document.getElementById('cont-finder'),
			{
				"gpiBridge": function(input, callback){ // required
					fetch("/files-and-folders/{{$project}}/{{$branch_name}}/gpi", {
						method: "post",
						headers: {
							'content-type': 'application/json'
						},
						body: JSON.stringify(input)
					}).then(function (response) {
						var contentType = response.headers.get('content-type').toLowerCase();
						if(contentType.indexOf('application/json') === 0 || contentType.indexOf('text/json') === 0) {
							response.json().then(function(json){
								callback(json);
							});
						} else {
							response.text().then(function(text){
								callback(text);
							});
						}
					}).catch(function (response) {
						console.log(response);
						callback(response);
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
