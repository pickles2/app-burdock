@php
	$title = __('Search');
@endphp
@extends('layouts.px2_project')

@section('content')
<div class="container">
	<h1>検索</h1>
	<div class="contents">
		<div id="cont-pickles2-code-search"></div>
	</div>
</div>
@endsection

@section('stylesheet')
<link rel="stylesheet" href="/common/pickles2-code-search/dist/pickles2-code-search.css" />
@endsection
@section('script')
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

		pickles2CodeSearch.init(
			{
				'start': function(keyword, searchOptions, callback){
					console.log('----- start', searchOptions);

					$.ajax({
						type : 'POST',
						url : "/search/{{ $project->project_code }}/{{ $branch_name }}/api",
						headers: {
							'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
						},
						contentType: 'application/json',
						dataType: 'json',
						data: JSON.stringify({
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
							pickles2CodeSearch.finished();
						}
					});
					return;
				},
				'abort': function(callback){
					console.log('abort -----');
					callback();
					return;
				},
				'tools': [
					{
						'label': 'テキストエディタで開く',
						'open': function(path){
							alert('テキストエディタで開く: ' + path);
						}
					},
					{
						'label': 'フォルダを開く',
						'open': function(path){
							alert('フォルダを開く: ' + path);
						}
					}
				]
			},
			function(){
				console.log('ready.');
			}
		);


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
