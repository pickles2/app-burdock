<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\DeliveryController;
use App\Project;
use App\Events\AsyncGeneralProgressEvent;

class AsyncSetupCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'bd:setup {path_json}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Pickles 2 のセットアップを非同期で実行する。';

	/** $fs */
	private $fs;

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{

		$this->info('================================================================');
		$this->info('  Start '.$this->signature);
		$this->info('    - Local Time: '.date('Y-m-d H:i:s'));
		$this->info('    - GMT: '.gmdate('Y-m-d H:i:s'));
		$this->info('----------------------------------------------------------------');
		$this->line( '' );

		$path_json = $this->argument('path_json');
		$json = null;
		if( is_file($path_json) ){
			$json = json_decode( file_get_contents($path_json) );
		}
		if( !$json ){
			$this->line(' Nothing to do.');
			$this->line( '' );
			$this->line('Local Time: '.date('Y-m-d H:i:s'));
			$this->line('GMT: '.gmdate('Y-m-d H:i:s'));
			$this->comment('------------ '.$this->signature.' successful ------------');
			$this->line( '' );

			return 0; // 終了コード
		}

		$user_id = $json->user_id;
		$project_code = $json->project_code;
		$branch_name = $json->branch_name;
		$params = $json->params;

		$project = \App\Project::where('project_code', $project_code)->first();

		// $project_path = \get_project_workingtree_dir($project_code, $branch_name);


		// --------------------------------------


		$burdockProjectManager = new \tomk79\picklesFramework2\burdock\projectManager\main( env('BD_DATA_DIR') );
		$pjManager = $burdockProjectManager->project($project->project_code);

		$gitUtil = new \App\Helpers\git();

		$checked_option = $params->checked_option;
		$checked_init = $params->checked_init;
		$repository = $params->clone_repository;
		$user_name = $params->clone_user_name;
		$password = $params->clone_password;
		$setup_status = $params->setup_status;
		$checked_repository = '';
		$vendor_name = '';
		$project_name = '';

		$project_code = $project->project_code;
		$project_data_path = get_project_dir($project_code);
		$project_workingtree_path = get_project_workingtree_dir($project_code, $branch_name);
		$path_composer = realpath(__DIR__.'/../../common/composer/composer.phar');
		$path_composer_home = realpath(dirname($path_composer).'/home');
		$path_current_dir = realpath('.'); // 元のカレントディレクトリを記憶

		// 再セットアップ時はディレクトリ内を削除してから処理に入る
		if($params->restart === 1) {
			\File::deleteDirectory($project_workingtree_path);
		}
		\File::makeDirectory($project_data_path, 0777, true, true);
		\File::makeDirectory($project_workingtree_path, 0777, true, true);

		$pjManager->save_initializing_request(array(
			'initializing_method' => ($checked_option === 'pickles2' ? 'create' : 'fork'),
				// create = 規定の雛形(pickles2/preset-get-start-pickles2) から新規プロジェクトを作成
				// fork = 新規の別のプロジェクトをベースに新規プロジェクトを作成
			'initializing_with' => array(
				'type' => ($checked_option === 'pickles2' ? 'composer-packagist' : 'git-remote'),
				'origin' => ($checked_option === 'pickles2' ? 'pickles2/preset-get-start-pickles2' : $repository),
			),
			'git_remote' => null,
			'git_user_name' => null,
			'composer_vendor_name' => null,
			'composer_project_name' => null,
		));


		// --------------------------------------
		// プロジェクトを配置する
		if($checked_option === 'pickles2') {
			// composer-packagist から
			$cmd = 'export COMPOSER_HOME='.$path_composer_home.'; php '.escapeshellarg($path_composer).' create-project pickles2/preset-get-start-pickles2 ./';
		} else {
			// 任意の gitリポジトリから
			$git_url_plus_auth = $repository;
			if( strlen($user_name) ){
				$git_url_plus_auth = $gitUtil->url_bind_confidentials($git_url_plus_auth, $user_name, $password);
			}

			$cmd = '';
			if(\File::cleanDirectory($project_workingtree_path)) {
				// shell_exec('rm .DS_Store');
				// clone するときは認証情報が必要なので、
				// 認証情報付きのURLで実行する
				$cmd = 'git clone --progress '.escapeshellarg($git_url_plus_auth).' .';
			}
		}

		chdir($project_workingtree_path);
		$desc = array(
		    1 => array('pipe', 'w'),
		    2 => array('pipe', 'w'),
		);
		$proc = proc_open($cmd, $desc, $pipes);
		stream_set_blocking($pipes[1], 0);
		stream_set_blocking($pipes[2], 0);

		while (feof($pipes[1]) === false || feof($pipes[2]) === false) {
			$stdout = $stderr = $std_parse = $std_array = $numerator = $denominator = $rate = '';
			$read = array($pipes[1], $pipes[2]);
			$write = null;
			$except = null;
			$timeout = 60000;
		    $ret = stream_select($read, $write, $except, $timeout);

		    if ($ret === false) {
		        echo "error\n";
		        break;
		    } else if ($ret === 0) {
		        echo "timeout\n";
		        continue;
		    } else {
		        foreach ($read as $sock) {
		            if ($sock === $pipes[2]) {
						$stdout = fgets($sock);
						// 標準出力をスペース区切りで配列に代入
						$std_array = explode(' ', $stdout);
						if($std_array[0] === 'Receiving' && $std_array[1] === 'objects:') {
							for($i = 0; $i < count($std_array); $i++) {
								if(preg_match('/\(.*?\)/', $std_array[$i])) {
									$std_parse = trim($std_array[$i], '()');
									$std_parse = explode('/', $std_parse);
									// 分子変数に数値型の標準出力配列[0]を代入
									$numerator = intval($std_parse[0]);
									// 分母変数に数値型の標準出力配列[1]を代入
									$denominator = intval($std_parse[1]);
									// 分子/分母の値を小数点切り捨てかつ文字列型に変換して$parseに代入
									$rate = strval(floor($numerator/$denominator*100));
									break;
								}
							}
						}
					} else {
						$stderr = fgets($sock);
					}
				}
			}
			$process = proc_get_status($proc);
			// ブロードキャストイベントに標準出力、標準エラー出力、パース結果を渡す、判定変数、キュー数、アラート配列、経過時間配列、パブリッシュファイルを渡す
			broadcast(new \App\Events\SetupEvent($stdout, $stderr, $path_composer, $std_parse, $std_array, $numerator, $denominator, $rate, $checked_option));
		}

		fclose($pipes[1]);
		fclose($pipes[2]);
		proc_close($proc);


		// --------------------------------------
		// composer install
		if( $checked_option !== 'pickles2' ){
			// gitリポジトリからロードした場合、
			// composer のセットアップ処理が追加で必要。
			// Packagist からセットアップした場合は同時に処理されるので不要。
			shell_exec('export COMPOSER_HOME='.$path_composer_home.'; php '.escapeshellarg($path_composer).' install');
		}


		// --------------------------------------
		// .px_execute.php の存在確認
		$is_entry_script_exists = false;
		if(\File::exists($project_workingtree_path.'/'.get_px_execute_path($project_code, $branch_name))) {
			$is_entry_script_exists = true;
		} else {
			$is_entry_script_exists = false;
		}

		// $initializing_request = $pjManager->get_initializing_request();
		// $pjManager->save_initializing_request($initializing_request);

		chdir($path_current_dir); // 元いたディレクトリへ戻る

		clearstatcache();

		$data = array(
			"is_entry_script_exists" => $is_entry_script_exists,
			"checked_option" => $checked_option,
		);
		// return $data;




		// / --------------------------------------

		$this->line(' finished!');
		$this->line( '' );
		$this->line('Local Time: '.date('Y-m-d H:i:s'));
		$this->line('GMT: '.gmdate('Y-m-d H:i:s'));
		$this->comment('------------ '.$this->signature.' successful ------------');
		$this->line( '' );

		return 0; // 終了コード
	}

}
