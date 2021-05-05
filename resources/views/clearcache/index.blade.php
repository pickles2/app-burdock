@php
	$title = __('キャッシュを消去');
@endphp
@extends('layouts.default')

@section('content')

<div class="cont-clearcache">
	<div class="btn-group" role="group">
		<button class="px2-btn px2-btn--primary cont-btn-clearcache">Pickles 2 のキャッシュをクリアする</button>
	</div>
	<pre><code></code></pre>
</div>

@endsection

@section('foot')
<script>
var $preview = document.querySelector('.cont-clearcache pre code');

function contClearCache(params){
	var _this = this;
	var btns = document.querySelectorAll('.cont-clearcache button.px2-btn');
	btns.forEach(function(btn){
		btn.setAttribute('disabled', 'disabled');
	});
	$preview.innerHTML = '';
	var method = 'post';
	$.ajax({
		type : method,
		url : "/clearcache/{{ $project->project_code }}/{{ $branch_name }}/clearcache",
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		},
		contentType: 'application/json',
		dataType: 'json',
		data: JSON.stringify(params || {}),
		success: function(data){
			console.log('clearcache request accepted:', data);
			// $preview.innerHTML += data;
		},
		complete: function(){
			btns.forEach(function(btn){
				btn.removeAttribute('disabled');
			});
		}
	});
}

window.addEventListener('load', function(e){
	document.querySelector('.cont-btn-clearcache').addEventListener('click', function(){
		contClearCache();
	});
});

window.Echo.channel('{{ $project->project_code }}---{{ $branch_name }}___pxcmd-clearcache.{{ Auth::id() }}').listen('AsyncGeneralProgressEvent', (message) => {
	console.log(message);
	if(message.stdout){
		$preview.innerHTML += message.stdout;
	}
	if(message.stderr){
		$preview.innerHTML += message.stderr;
	}
});

</script>
@endsection
