@php
	$title = __('Git');
@endphp
@extends('layouts.px2_project')

@section('content')
<div class="container">
	<h1>Git</h1>
	<div class="contents cont-git">
		<div class="btn-group" role="group">
			<button class="btn px2-btn cont-btn-git-status">status</button>
			<button class="btn px2-btn cont-btn-git-pull">pull</button>
			<button class="btn px2-btn cont-btn-git-commit">commit</button>
			<button class="btn px2-btn cont-btn-git-push">push</button>
		</div>
		<pre><code></code></pre>
	</div>
</div>
@endsection

@section('script')
<script>
var $preview = document.querySelector('.cont-git pre code');

function gitCmd(command_name, params){
	var _this = this;
	var btns = document.querySelectorAll('.cont-git button.px2-btn');
	btns.forEach(function(btn){
		btn.setAttribute('disabled', 'disabled');
	});
	$preview.innerHTML = '';
	var method = 'post';
	switch(command_name){
		case 'status':
			method = 'get';
			break;
	}
	$.ajax({
		type : method,
		url : "/git/{{ $project->project_code }}/{{ $branch_name }}/git-"+command_name,
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		},
		contentType: 'application/json',
		dataType: 'json',
		data: JSON.stringify(params || {}),
		success: function(data){
			// console.log(data);
			for( var idx in data ){
				$preview.innerHTML += data[idx].stdout;
				$preview.innerHTML += data[idx].stderr;
			}
		},
		complete: function(){
			btns.forEach(function(btn){
				btn.removeAttribute('disabled');
			});
		}
	});
}

window.addEventListener('load', function(e){
	document.querySelector('.cont-btn-git-status').addEventListener('click', function(){
		gitCmd('status');
	});
	document.querySelector('.cont-btn-git-pull').addEventListener('click', function(){
		gitCmd('pull');
	});
	document.querySelector('.cont-btn-git-commit').addEventListener('click', function(){
		var commitMsg = prompt('commit message?');
		if(!commitMsg){
			return;
		}
		gitCmd('commit', {
			'commit_message': commitMsg
		});
	});
	document.querySelector('.cont-btn-git-push').addEventListener('click', function(){
		gitCmd('push');
	});
});
</script>
@endsection
