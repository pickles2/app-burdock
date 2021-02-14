@php
	$title = __('Search');
@endphp
@extends('layouts.default')

@section('content')

<div id="cont-pickles2-code-search"></div>

@endsection

@section('head')
<link rel="stylesheet" href="/common/pickles2-code-search/dist/pickles2-code-search.css" />
@endsection
@section('foot')
<script>
window.contRemoteFinderGpiEndpoint = "/files-and-folders/{{ $project->project_code }}/{{ $branch_name }}/gpi";
window.contCommonFileEditorEndpoint = '/files-and-folders/{{ $project->project_code }}/{{ $branch_name }}/common-file-editor';
window.contCommonFileEditorGpiEndpoint = '/files-and-folders/{{ $project->project_code }}/{{ $branch_name }}/common-file-editor/gpi';
window.contContentsEditorEndpoint = '/contentsEditor/{{ $project->project_code }}/{{ $branch_name }}';
window.contApiParsePx2FilePathEndpoint = '/files-and-folders/{{ $project->project_code }}/{{ $branch_name }}/api/parsePx2FilePath';
</script>
<script src="/common/pickles2-code-search/dist/pickles2-code-search.js"></script>
<script>

window.contApp = new (function($){
	var _this = this;

	var pickles2CodeSearch;
	var hitCount = 0;
	var targetCount = 0;

	/**
	 * 初期化
	 */
	function init(){
		pickles2CodeSearch = new Pickles2CodeSearch(
			document.getElementById('cont-pickles2-code-search')
		);

		window.Echo.channel('search-event')
			.listen('SearchEvent', (message) => {
				// console.log(message);
				if( message.data.command == 'finished' ){
					pickles2CodeSearch.finished();
				}else{
					pickles2CodeSearch.update({
						'total': message.data.total,
						'done': message.data.done,
						'new': message.data.new
					});
				}

			})
		;

		pickles2CodeSearch.init(
			{
				'start': function(keyword, searchOptions, callback){
					console.log('----- start', searchOptions);

					$.ajax({
						type : 'POST',
						url : "/search/{{ $project->project_code }}/{{ $branch_name }}/search",
						headers: {
							'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
						},
						contentType: 'application/json',
						dataType: 'json',
						data: JSON.stringify({
							'command': 'start',
							'keyword': keyword,
							'options': searchOptions,
						}),
						error: function(data){
							console.error('error', data);
						},
						success: function(data){
							console.log(data);
						},
						complete: function(){
							console.log('complete');
							callback();
						}
					});
					return;
				},
				'abort': function(callback){
					console.log('abort -----');

					$.ajax({
						type : 'POST',
						url : "/search/{{ $project->project_code }}/{{ $branch_name }}/search",
						headers: {
							'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
						},
						contentType: 'application/json',
						dataType: 'json',
						data: JSON.stringify({
							'command': 'cancel',
						}),
						error: function(data){
							console.error('abort: error', data);
						},
						success: function(data){
							console.log(data);
						},
						complete: function(){
							console.log('abort: complete');
							callback();
						}
					});
					return;
				},
				'tools': [
					{
						'label': 'エディタで開く',
						'open': function(path){

							px2style.loading();
							var ext = path.replace(/^[\s\S]*?\.([a-zA-Z0-9\-\_]+)$/g, '$1');

							switch( ext ){
								case 'html':
								case 'htm':
									parsePx2FilePathEndpoint(path, function(pxExternalPath, pathFiles, pathType){
										console.log(pxExternalPath, pathType);
										var url = 'about:blank';
										if(pathType == 'contents'){
											url = window.contContentsEditorEndpoint + '?page_path='+encodeURIComponent(pxExternalPath);
										}else{
											url = window.contCommonFileEditorEndpoint + '?filename='+encodeURIComponent(path);
										}
										window.open(url);
									});
									break;
								default:
									var url = window.contCommonFileEditorEndpoint + '?filename='+encodeURIComponent(path);
									window.open(url);
									break;
							}
							px2style.closeLoading();

						}
					},
					// {
					// 	'label': 'フォルダを開く',
					// 	'open': function(path){
					// 		alert('フォルダを開く: ' + path);
					// 	}
					// },
				]
			},
			function(){
				console.log('ready.');
			}
		);


	}


	function parsePx2FilePathEndpoint( filepath, callback ){
		callback = callback || function(){};
		$.ajax({
			type : 'get',
			url : window.contApiParsePx2FilePathEndpoint,
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			contentType: 'application/json',
			dataType: 'json',
			data: {
				'path': filepath
			},
			success: function(data){
				// console.log(data);
				callback(data.pxExternalPath, data.pathFiles, data.pathType);
			}
		});
		return;
	}

	/**
	 * onload
	 */
	$(window).on('load', function(){
		init();
	});

})($);



</script>
@endsection
