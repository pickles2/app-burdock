<?php
require_once(__DIR__.'/../vendor/autoload.php');
$realpath_dotenv = __DIR__.'/../.env';
$realpath_dotenv_template = __DIR__.'/../.env.example';
$fs = new tomk79\filesystem();

$envKeys = array(
	'APP_NAME',
	'APP_ENV',
	'APP_KEY',
	'APP_DEBUG',

	// デフォルト。APP_DEBUGに応じて決まる
	'DEBUGBAR_ENABLED',

	// アプリサーバーのURLを必ず設定する
	'APP_URL',

	'LOG_CHANNEL',

	// データベースに関する設定（初期設定はSQLite）
	'DB_CONNECTION',
	'DB_HOST',
	'DB_PORT',
	'DB_DATABASE',
	'DB_USERNAME',
	'DB_PASSWORD',
	'DB_PREFIX',

	'BROADCAST_DRIVER',
	'CACHE_DRIVER',
	'QUEUE_CONNECTION',
	'SESSION_DRIVER',
	'SESSION_LIFETIME',

	'REDIS_HOST',
	'REDIS_PASSWORD',
	'REDIS_PORT',

	// メール送信サーバーを必ず設定する
	'MAIL_DRIVER',
	'MAIL_HOST',
	'MAIL_PORT',
	'MAIL_USERNAME',
	'MAIL_PASSWORD',
	'MAIL_ENCRYPTION',
	'MAIL_FROM_ADDRESS',
	'MAIL_FROM_NAME',

	'PUSHER_APP_ID',
	'PUSHER_APP_KEY',
	'PUSHER_APP_SECRET',
	'PUSHER_APP_CLUSTER',

	'MIX_PUSHER_APP_KEY',
	'MIX_PUSHER_APP_CLUSTER',



	# --------------------------------------
	# Burdock 固有設定

	# コマンドのパス
	'BD_COMMAND_PHP',
	'BD_COMMAND_PHP_INI',
	'BD_COMMAND_PHP_EXTENSION_DIR',
	'BD_COMMAND_GIT',

	# プロジェクトを作成するディレクトリパス (必須)
	'BD_DATA_DIR',

	# プレビュー環境のドメイン名 (必須)
	# ポート番号を含む場合は、合わせて設定してください。 (例: preview.example.com:8080)
	# 実際は、この前にサブドメインを1つ追加して使用されます。 (例: proj-code---master.preview.example.com)
	'BD_PREVIEW_DOMAIN',

	# ステージング環境のドメイン名 (必須)
	# ポート番号を含む場合は、合わせて設定してください。 (例: staging.example.com:8080)
	# 実際は、この前にサブドメインを1つ追加して使用されます。 (例: proj-code---stg1.staging.example.com)
	'BD_STAGING_DOMAIN',
);


if( array_key_exists('action', $_REQUEST) && $_REQUEST['action'] == 'write' ){
	$src_dotenv = '';
	foreach($envKeys as $envKey){
		$inputvalue = $_REQUEST['env_'.$envKey];
		switch(strtolower($inputvalue)){
			case 'null':
			case 'true':
			case 'false':
				$inputvalue = json_decode($inputvalue);
				break;
		}
		$src_dotenv .= $envKey.'='.json_encode($inputvalue, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)."\n";
	}
	$fs->save_file($realpath_dotenv, $src_dotenv);
}




if(is_file($realpath_dotenv)){
	header('Location: /');
	exit;
}



$dotenv_basename = basename($realpath_dotenv);
if(!is_file($realpath_dotenv)){
	$dotenv_basename = basename($realpath_dotenv_template);
}


$dotenv = new Dotenv\Dotenv(__DIR__.'/../', $dotenv_basename);
$dotenv->load();



?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<title>Initialize .env</title>
		<link rel="stylesheet" href="./vendor/bootstrap/css/bootstrap.css" />
		<script src="./vendor/bootstrap/js/bootstrap.js"></script>
	</head>
	<body>
		<div class="container">
			<h1>Initialize <code>.env</code></h1>

			<form action="" method="post">
				<input type="hidden" name="action" value="write" />

<?php
$inputValues = array();
foreach($envKeys as $envKey){
	$inputValues[$envKey] = getenv($envKey);
}
if( $inputValues['DB_CONNECTION'] == 'sqlite' ){
	$inputValues['DB_DATABASE'] = $fs->get_realpath($inputValues['DB_DATABASE'], __DIR__);
}
$inputValues['BD_DATA_DIR'] = $fs->get_realpath($inputValues['BD_DATA_DIR'], __DIR__);
foreach($envKeys as $envKey){
	echo '<p>'."\n";
	echo '<label>'.htmlspecialchars($envKey).'</label>:'."\n";
	$value = $inputValues[$envKey];
	echo '<input type="text" name="env_'.htmlspecialchars($envKey).'" value="'.htmlspecialchars($value).'" class="form-control" />'."\n";
	echo '</p>'."\n";
}
?>

				<p>
					<button class="btn btn-primary">Save</button>
				</p>
			</form>
		</div>
	</body>
</html>
