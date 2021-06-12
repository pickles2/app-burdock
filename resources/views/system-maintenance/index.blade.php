@extends('layouts.default')
@section('title', 'システムメンテナンス')
@section('content')

<h2>メンテナンスメニュー</h2>
<div class="px2-p">
	<ul class="px2-vertical-list">
		<li><a href="/system-maintenance/project-dirs" class="px2-a">プロジェクトディレクトリ一覧</a></li>
		<li><a href="/system-maintenance/healthcheck" class="px2-a">インストール状態のチェック</a></li>
		<li><a href="/system-maintenance/basicauth-default-htpasswd" class="px2-a">バーチャルホストのデフォルトの基本認証パスワード設定</a></li>
		<li><a href="/system-maintenance/generate_vhosts" class="px2-a">バーチャルホストの再生成</a></li>
	</ul>
</div>

<h2>実行環境</h2>
<div class="px2-p">
	<table class="px2-table" style="width: 100%;">
		<tr>
			<th>Platform</th>
			<td><code><?= htmlspecialchars( php_uname() ); ?></code></td>
		</tr>
		<tr>
			<th>PHP Version</th>
			<td><pre><code><?= htmlspecialchars( phpversion() ); ?></code></pre></td>
		</tr>
		<tr>
			<th>phpinfo</th>
			<td>
				<p><a href="/system-maintenance/phpinfo" class="px2-a" target="_blank">新しいウィンドウで表示</a></p>
			</td>
		</tr>
		<tr>
			<th>UserName</th>
			<?php
			$userName = '';
			if( is_callable('posix_getpwuid') && is_callable('posix_geteuid') ){
				$userName = posix_getpwuid(posix_geteuid());
				$userName = $userName['name'];
			}
			?>
			<td><pre><code><?= htmlspecialchars( ($userName ? $userName : '---') ); ?></code></pre></td>
		</tr>
	</table>
</div>

<h2>コマンド</h2>

<div class="px2-p">
	<table class="px2-table" style="width: 100%;">
		<tr>
			<th>PHP</th>
			<td>
				<div class="cont-checkcommand-php">
					<pre><code></code></pre>
					<p><code><?= config('burdock.command_path.php') ?></code></p>
					<p><code><?= config('burdock.command_path.php_ini') ?></code></p>
					<p><code><?= config('burdock.command_path.php_extension_dir') ?></code></p>
				</div>
			</td>
		</tr>
		<tr>
			<th>Composer</th>
			<td>
				<div class="cont-checkcommand-composer">
					<pre><code></code></pre>
				</div>
			</td>
		</tr>
		<tr>
			<th>Git</th>
			<td>
				<div class="cont-checkcommand-git">
					<pre><code></code></pre>
					<p><code><?= config('burdock.command_path.git') ?></code></p>
				</div>
			</td>
		</tr>
	</table>
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
