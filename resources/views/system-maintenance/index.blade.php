@php
	$title = 'システムメンテナンス';
@endphp
@extends('layouts.px2_project')
@section('title', $title)
@section('content')
<div class="container">
	<h1>システムメンテナンス</h1>
	<div class="contents">

		<h2>メンテナンスメニュー</h2>
		<ul class="px2-vertical-list">
			<li><a href="/system-maintenance/project-dirs" class="px2-a">プロジェクトディレクトリ</a></li>
		</ul>

		<h2>実行環境</h2>
		<dl>
			<dt>Platform</dt>
				<dd><pre><code><?= htmlspecialchars( php_uname() ); ?></code></pre></dd>
			<dt>PHP Version</dt>
				<dd><pre><code><?= htmlspecialchars( phpversion() ); ?></code></pre></dd>
			<dt>phpinfo</dt>
				<dd>
					<p><a href="/system-maintenance/phpinfo" class="px2-a" target="_blank">新しいウィンドウで表示</a></p>
				</dd>
			<dt>UserName</dt>
				<?php
				$userName = '';
				if( is_callable('posix_getpwuid') && is_callable('posix_geteuid') ){
					$userName = posix_getpwuid(posix_geteuid());
					$userName = $userName['name'];
				}
				?>
				<dd><pre><code><?= htmlspecialchars( ($userName ? $userName : '---') ); ?></code></pre></dd>
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
			// console.log(data);
			$('.cont-checkcommand-'+param+' pre code').text(data.version);
		},
		'complete': function(){
			// console.log('done');
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
