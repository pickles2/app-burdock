<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Project;

class AsyncSetupOptionsCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'bd:setup_options {path_json}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Pickles 2 セットアップ後のオプション反映処理を非同期で実行する。';

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



		$burdockProjectManager = new \tomk79\picklesFramework2\burdock\projectManager\main( config('burdock.data_dir') );
		$pjManager = $burdockProjectManager->project($project->project_code);

		$gitUtil = new \App\Helpers\git();

		$initializing_request = $pjManager->get_initializing_request();

		$checked_option = $params->checked_option;
		$checked_init = $params->checked_init;
		$setup_status = $params->setup_status;
		$checked_repository = $params->checked_repository;
		$vendor_name = $params->vendor_name;
		$project_name = $params->project_name;

		if($checked_option === 'pickles2') {
			if($checked_init === true) {
				$repository = $params->repository;
				$user_name = $params->user_name;
				$password = $params->password;
			} else {
				$repository = '';
				$user_name = '';
				$password = '';
			}
		} elseif($checked_option === 'git') {
			if($checked_repository === 'original') {
				$repository = $params->clone_repository;
				$user_name = $params->clone_user_name;
				$password = $params->clone_password;
			} elseif($checked_repository === 'new') {
				$repository = $params->clone_new_repository;
				$user_name = $params->clone_new_user_name;
				$password = $params->clone_new_password;
			} else {
				$repository = '';
				$user_name = '';
				$password = '';
			}
		}
		$git_url_plus_auth = $repository;
		$project_code = $project->project_code;
		$project_data_path = get_project_dir($project_code);
		$project_workingtree_path = get_project_workingtree_dir($project_code, $branch_name);
		$path_current_dir = realpath('.'); // 元のカレントディレクトリを記憶

		chdir($project_workingtree_path);

		$initializing_request = $pjManager->get_initializing_request();
		if($checked_option === 'git' && $checked_repository === 'original'){
			$initializing_request->initializing_method = 'clone'; // clone = 既存リポジトリに参加
		}
		$initializing_request->git_remote = $repository;
		$initializing_request->git_user_name = $user_name;
		$initializing_request->composer_vendor_name = $vendor_name;
		$initializing_request->composer_project_name = $project_name;
		$pjManager->save_initializing_request($initializing_request);


		// --------------------------------------
		// composer.json の name を変更して上書き保存
		if( \File::exists($project_workingtree_path.'/composer.json') ){
			$name_property = null;
			if( strlen($vendor_name) && strlen($project_name) ){
				$name_property = $vendor_name.'/'.$project_name;
			}
			$files = null;
			$file = file($project_workingtree_path.'/composer.json');
			for($i = 0; $i < count($file); $i++) {
				if(strpos($file[$i], '"name": "pickles2/preset-get-start-pickles2"') !== false) {
					if( strlen($name_property) ){
						$files .= str_replace('pickles2/preset-get-start-pickles2', $name_property, $file[$i]);
					}
				} else {
					$files .= $file[$i];
				}
			}
			file_put_contents($project_workingtree_path.'/composer.json', $files);
		}

		// --------------------------------------
		// Git初期化操作
		if( $initializing_request->initializing_method !== 'clone' ){

			if($checked_option === 'pickles2' && $checked_init === true || $checked_option === 'git' && $checked_repository === 'new') {
				// .gitフォルダがあったら削除する
				if(\File::exists($project_workingtree_path.'/.git')) {
					\File::deleteDirectory($project_workingtree_path.'/.git');
				}
				// ユーザー名とパスワードを含むGitURLを生成
				if( strlen($user_name) ){
					$git_url_plus_auth = $gitUtil->url_bind_confidentials($git_url_plus_auth, $user_name, $password);
				}

				shell_exec('git init');
				shell_exec('git add .');
				shell_exec('git commit -m "Create project"');
				shell_exec('git remote add origin '.escapeshellarg($git_url_plus_auth));

			} elseif($checked_option === 'git' && $checked_repository === 'original') {
				// ユーザー名とパスワードを含むGitURLを生成
				$git_url_plus_auth = $repository;
				if( strlen($user_name) ){
					$git_url_plus_auth = $gitUtil->url_bind_confidentials($git_url_plus_auth, $user_name, $password);
				}

				shell_exec('git add .');
				shell_exec('git commit -m "After clone first commit"');
				shell_exec('git remote set-url origin '.escapeshellarg($git_url_plus_auth));
			} elseif($checked_option === 'git' && $checked_repository === 'none') {
				// .gitフォルダがあったら削除する
				if(\File::exists($project_workingtree_path.'/.git')) {
					\File::deleteDirectory($project_workingtree_path.'/.git');
				}
			}
			// git push時のブロードキャスト
			if($checked_option === 'pickles2' && $checked_init === true || $checked_option === 'git' && $checked_repository === 'original' || $checked_option === 'git' && $checked_repository === 'new') {
				$cmd = 'git push --progress origin master';
				$desc = array(
					1 => array('pipe', 'w'),
					2 => array('pipe', 'w'),
				);
				$proc = proc_open($cmd, $desc, $pipes);
				stream_set_blocking($pipes[1], 0);
				stream_set_blocking($pipes[2], 0);

				while (feof($pipes[1]) === false || feof($pipes[2]) === false) {
					$stdout = $stderr = '';
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
							if ($sock === $pipes[1]) {
								$stdout = fgets($sock);
							} else if ($sock === $pipes[2]) {
								$stderr = fgets($sock);
								// 標準出力をスペース区切りで配列に代入
								$std_array = explode(' ', $stderr);
								if($std_array[0] === 'Writing' && $std_array[1] === 'objects:') {
									for($i = 0; $i < count($std_array); $i++) {
										if(preg_match('/\(.*?\)/', $std_array[$i])) {
											$std_parse = trim($std_array[$i], '()');
											$std_parse = explode('/', $std_parse);
											// 分子変数に数値型の標準出力配列[0]を代入
											$numerator = intval($std_parse[0]);
											// 分母変数に数値型の標準出力配列[1]を代入
											$denominator = intval($std_parse[1]);
											// 分子/分母の値を小数点切り捨てかつ文字列型に変換して$rateに代入
											$rate = strval(floor($numerator/$denominator*100));
											break;
										} else {
											$std_parse = '';
											$numerator = '';
											$denominator = '';
											$rate = '';
										}
									}
								} else {
									$std_parse = '';
									$numerator = '';
									$denominator = '';
									$rate = '';
								}
							}
						}
					}
					$process = proc_get_status($proc);

					// ブロードキャストイベントに標準出力、標準エラー出力、パース結果、アラート配列、分子変数、分母変数、レート変数を渡す
					broadcast(new \App\Events\SetupOptionEvent(
						'progress',
						null,
						null,
						$stdout,
						$stderr,
						$std_parse,
						$std_array,
						$numerator,
						$denominator,
						$rate,
						$checked_option
					));
				}
				fclose($pipes[1]);
				fclose($pipes[2]);
				proc_close($proc);
			}
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


		// --------------------------------------
		// Gitリモート接続情報をDBに保存
		$project->git_url = $repository;
		$project->git_username = \Crypt::encryptString($user_name);
		$project->git_password = \Crypt::encryptString($password);
		$project->save();


		chdir($path_current_dir); // 元いたディレクトリへ戻る


		$data = array(
			"is_entry_script_exists" => $is_entry_script_exists,
			"checked_option" => $checked_option,
			"checked_init" => $checked_init,
			"checked_repository" => $checked_repository,
			"vendor_name" => $vendor_name,
			"project_name" => $project_name,
			"repository" => $repository,
			"user_name" => $user_name,
		);

		// --------------------------------------
		// 処理の完了をブラウザに伝える
		broadcast(new \App\Events\SetupOptionEvent(
			'exit',
			0,
			$data,
			null,
			null,
			null,
			null,
			null,
			null,
			null,
			null
		));



		// --------------------------------------
		// vhosts.conf を更新する
		$bdAsync = new \App\Helpers\async();
		$bdAsync->set_channel_name( 'system-mentenance___generate_vhosts' );
		$bdAsync->artisan(
			'bd:generate_vhosts'
		);



		$this->line(' finished!');
		$this->line( '' );
		$this->line('Local Time: '.date('Y-m-d H:i:s'));
		$this->line('GMT: '.gmdate('Y-m-d H:i:s'));
		$this->comment('------------ '.$this->signature.' successful ------------');
		$this->line( '' );

		return 0; // 終了コード
	}

}
