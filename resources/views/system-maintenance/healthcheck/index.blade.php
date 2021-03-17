@extends('layouts.default')
@section('title', 'インストール状態のチェック')

@section('content')

<h2>WebSocketのテスト</h2>
<div class="cont-btn-broadcast">
	<p>
		<button class="px2-btn cont-btn-broadcast-test" type="button">WebSocketブロードキャストをテスト</button>
		<button class="px2-btn cont-btn-broadcast-open-endpoint" type="button">WebSocketエンドポイントを開く</button>
	</p>
</div>

<h2>Queueのテスト</h2>
<div class="cont-btn-queue">
	<p>
		<button class="px2-btn cont-btn-queue-test" type="button">Queueをテスト</button>
	</p>
</div>

<hr />
<div class="cont-console">
	<pre style="max-height: 200px;"><code></code></pre>
</div>
<hr />
<p>
	<a href="{{ url('/system-maintenance') }}" class="px2-btn">戻る</a>
</p>

@endsection

@section('foot')
<script>
$(window).on('load', function(){
	$('.cont-btn-broadcast-test').on('click', function(){
		let $code = $('.cont-console pre code');
		$code.append('Sending broadcast request...'+"\n");
		$.ajax({
			"url": '/system-maintenance/healthcheck/ajax',
			"headers": {
				'X-CSRF-TOKEN': '{{ csrf_token() }}'
			},
			"type": 'post',
			"data": {
				"cmd": "broadcast",
			},
			"success": function(data){
				console.log('--- broadcast request response:', data);
				$code.append(data.message+"\n");
			},
			"error": function(error){
				console.error('*** broadcast request ERROR:', error);
				$code.append('Error:'+error+"\n");
			},
			"complete": function(){
			}
		});
	});
	$('.cont-btn-broadcast-open-endpoint').on('click', function(){
		let broadcast_endpoint_url = '{{ $broadcast_endpoint_url }}';
		console.log(broadcast_endpoint_url);
		window.open( broadcast_endpoint_url );
	});

	$('.cont-btn-queue-test').on('click', function(){
		let $code = $('.cont-console pre code');
		$code.append('Sending queue request...'+"\n");
		$.ajax({
			"url": '/system-maintenance/healthcheck/ajax',
			"headers": {
				'X-CSRF-TOKEN': '{{ csrf_token() }}'
			},
			"type": 'post',
			"data": {
				"cmd": "queue",
			},
			"success": function(data){
				console.log('--- queue request response:', data);
				$code.append(data.message+"\n");
			},
			"error": function(error){
				console.error('*** queue request ERROR:', error);
				$code.append('Error:'+error+"\n");
			},
			"complete": function(){
			}
		});
	});

	window.Echo.channel('system-mentenance___broadcast').listen('SystemMaintenanceHealthCheckEvent', (e) => {
		console.info(e);
		let $code = $('.cont-console pre code');
		$code.append(e.data.message + "\n");
	});

})
</script>
@endsection
