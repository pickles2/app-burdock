@php
	$title = __('Composer');
@endphp
@extends('layouts.default')

@section('content')

<div class="cont-composer">
	<div class="btn-group" role="group">
		<button class="btn px2-btn cont-btn-composer-install">install</button>
		<button class="btn px2-btn cont-btn-composer-update">update</button>
	</div>
	<pre><code></code></pre>
</div>

@endsection

@section('foot')
<script>
(function(){
	var $preview = document.querySelector('.cont-composer pre code');
	var btns = document.querySelectorAll('.cont-composer button.px2-btn');

	function composerCmd(command_name, params){
		var _this = this;
		btns.forEach(function(btn){
			btn.setAttribute('disabled', true);
		});
		$preview.innerHTML = '';
		var method = 'post';
		$.ajax({
			type : method,
			url : "/composer/{{ $project->project_code }}/{{ $branch_name }}/composer-"+command_name,
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			contentType: 'application/json',
			dataType: 'json',
			data: JSON.stringify(params || {}),
			success: function(data){
				console.log('=-=-=-=-=', data);
			},
			complete: function(){
			}
		});
	}

	window.addEventListener('load', function(e){
		document.querySelector('.cont-btn-composer-install').addEventListener('click', function(){
			composerCmd('install');
		});
		document.querySelector('.cont-btn-composer-update').addEventListener('click', function(){
			composerCmd('update');
		});

		window.Echo.channel('{{ $project->project_code }}----{{ $branch_name }}___composer.{{ Auth::id() }}').listen('AsyncGeneralProgressEvent', (message) => {
			if( message.status == 'exit' ){
				console.log(message);
				btns.forEach(function(btn){
					btn.removeAttribute('disabled');
				});
				return;
			}

			// console.log(message);
			if(message.stdout){
				$preview.innerHTML += message.stdout;
			}
			if(message.stderr){
				$preview.innerHTML += message.stderr;
			}
		});
	});
})();
</script>
@endsection
