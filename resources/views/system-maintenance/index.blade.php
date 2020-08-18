@php
	$title = env('APP_NAME');
@endphp
@extends('layouts.px2_project')
@section('title', env('APP_NAME'))
@section('content')
<div class="container">
	<h1>システムメンテナンス</h1>
	<div class="contents">

		<h2>実行環境</h2>
		<dl>
			<dt>Platform</dt>
				<dd><?= htmlspecialchars( php_uname() ); ?></dd>
			<dt>PHP Version</dt>
				<dd><?= htmlspecialchars( phpversion() ); ?></dd>
			<dt>UserName</dt>
				<?php
				$userName = posix_getpwuid(posix_geteuid());
				?>
				<dd><?= htmlspecialchars( $userName['name'] ); ?></dd>
		</dl>

		<h2>コマンド</h2>
		<dl>
			<dt>PHP</dt>
				<dd>
					<div class="cont-checkcommand-php">
						<pre><code></code></pre>
					</div>
				</dd>
			<dt>Composer</dt>
				<dd>
					<div class="cont-checkcommand-composer">
						<pre><code></code></pre>
					</div>
				</dd>
			<dt>Git</dt>
				<dd>
					<div class="cont-checkcommand-git">
						<pre><code></code></pre>
					</div>
				</dd>
		</dl>
	</div>
</div>
<script>
function contCheckingCommands(param){
	// CHecking PHP
	$.ajax({
		'url': '/system-maintenance/ajax/checkCommand',
		'method': 'get',
		'data': {
			'cmd': param
		},
		'success': function(data){
			console.log(data);
			$('.cont-checkcommand-'+param+' pre code').text(data.version);

		},
		'complete': function(){
			console.log('done');
		}
	});
}
$(document).ready(function(){
	contCheckingCommands('php');
	contCheckingCommands('composer');
	contCheckingCommands('git');
});
</script>
@endsection
