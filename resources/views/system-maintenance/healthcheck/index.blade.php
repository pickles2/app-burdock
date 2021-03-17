@extends('layouts.default')
@section('title', 'インストール状態のチェック')

@section('content')

<h2>WebSocketのテスト</h2>
<div class="cont-btn-broadcast">
	<p>
		<button class="px2-btn cont-btn-broadcast-test" type="button">WebSocketブロードキャストをテスト</button>
		<button class="px2-btn cont-btn-broadcast-open-endpoint" type="button">WebSocketエンドポイントを開く</button>
	</p>
	<div>
		<pre><code></code></pre>
	</div>
</div>

<p>
	<a href="{{ url('/system-maintenance') }}" class="px2-btn">戻る</a>
</p>

@endsection

@section('foot')
<script>
$(window).on('load', function(){
	$('.cont-btn-broadcast-test').on('click', function(){
		let $code = $('.cont-btn-broadcast pre code');
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

	window.Echo.channel('system-mentenance___broadcast').listen('SystemMaintenanceEvent', (e) => {
		console.info(e);
		let $code = $('.cont-btn-broadcast pre code');
		$code.append(e.data.message + "\n");
	});

})
</script>
@endsection
