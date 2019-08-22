@php
	$title = __('Git');
@endphp
@extends('layouts.px2_project')

@section('content')
<div class="container">
	<h1>Git</h1>
	<div class="contents"></div>
</div>
@endsection

@section('stylesheet')
<link rel="stylesheet" href="/common/gitui79/dist/gitui79.min.css">
@endsection

@section('script')
<script src="/common/gitparse79/dist/gitParse79.min.js"></script>
<script src="/common/gitui79/dist/gitui79.min.js"></script>

<script>
window.contApp = new (function(){
	var _this = this;
	var $cont;

	/**
	 * initialize
	 */
	function init(){
		$cont = $('.contents').html('');

		var $elm = document.querySelector('.contents');
		var gitUi79 = new GitUi79( $elm, function( cmdAry, callback ){
			var method = 'post';
			var result = [];
			var apiUrl = "/git/{{ $project->project_code }}/{{ $branch_name }}/git";

			$.ajax({
				type : method,
				url : apiUrl,
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				contentType: 'application/json',
				dataType: 'json',
				data: JSON.stringify({
					command_ary: cmdAry
				}),
				error: function(data){
					result = result.concat(data);
					console.error('error', data);
				},
				success: function(data){
					result = result.concat(data);
				},
				complete: function(){
					// console.log('complete', result);
					try{
						callback(result[0].return, result[0].stdout+result[0].stderr);
					}catch(e){
						console.error(e);
						alert('Failed');
					}
				}
			});

		}, {
			committer: {
				name: "{{ $user->name }}",
				email: "{{ $user->email }}"
			}
		} );
		gitUi79.init(function(){
			console.log('gitUi79: Standby.');
		});

	}

	/**
	 * イベント
	 */
	window.addEventListener('load', function(e){
		init();
	});

})();

</script>
@endsection
