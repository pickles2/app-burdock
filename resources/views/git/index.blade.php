@php
	$title = __('Git');
@endphp
@extends('layouts.default')

@section('content')
<div class="cont-git"></div>
@endsection

@section('stylesheet')
<link rel="stylesheet" href="/common/gitui79/dist/gitui79.min.css">
@endsection

@section('foot')
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
		$cont = $('.cont-git').html('');

		var $elm = document.querySelector('.cont-git');
		var gitUi79 = new GitUi79( $elm, function( cmdAry, callback ){
			var method = 'post';
			var result = [];
			var apiUrl = "/git/{{ $project->project_code }}/{{ $branch_name }}/git";

			if( cmdAry.length == 2 && cmdAry[0] == 'checkout' ){
				// `git checkout branchname` のフェイク
				window.location.href = "/git/{{ $project->project_code }}/"+cmdAry[1];
				return;
			}

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
					console.log('complete', result);
					if( cmdAry[0] == 'checkout' && cmdAry[1] == '-b' && cmdAry.length >= 3 && cmdAry.length <= 4 ){
						// `git checkout -b branchname` のフェイク および
						// `git checkout -b localBranchname remoteBranch` のフェイク
						if( result[0].return ){
							alert('Error: ' + result[0].stderr);
						}else{
							window.location.href = "/git/{{ $project->project_code }}/"+cmdAry[2];
							return;
						}
					}

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
