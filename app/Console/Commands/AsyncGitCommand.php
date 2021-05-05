<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Project;
use App\Events\AsyncGeneralProgressEvent;

class AsyncGitCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'bd:git {path_json}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Gitコマンドを非同期で実行する。';

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
		$channel_name = $json->channel_name;

		$git_command_array = $json->ary_command;
		$options = $json->options;

		$project = \App\Project::where('project_code', $project_code)->first();

		$project_path = realpath('.');
		if( strlen($project_code) && strlen($branch_name) ){
			$project_path = \get_project_workingtree_dir($project_code, $branch_name);
		}
		$path_current_dir = realpath('.'); // 元のカレントディレクトリを記憶

		set_time_limit(60);

		chdir($project_path);




		// --------------------------------------



		$gitUtil = new \App\Helpers\git($project, $branch_name);
		$gitUtil->set_remote_origin();

		if( count($git_command_array) == 2 && $git_command_array[0] == 'branch' && $git_command_array[1] == '-a' ){
			// `git branch -a` のフェイク
			// ブランチの一覧を取得する
			$rtn = \App\Helpers\GitHelpers\GitBranch::execute($gitUtil, $git_command_array);
		}elseif( count($git_command_array) == 3 && $git_command_array[0] == 'checkout' && $git_command_array[1] == '-b' ){
			// `git checkout -b branchname` のフェイク
			// カレントブランチから新しいブランチを作成する
			$rtn = \App\Helpers\GitHelpers\GitCheckoutNewBranch::execute($gitUtil, $git_command_array);
		}elseif( count($git_command_array) == 4 && $git_command_array[0] == 'checkout' && $git_command_array[1] == '-b' ){
			// `git checkout -b localBranchname remoteBranch` のフェイク
			// リモートブランチをチェックアウトする
			$rtn = \App\Helpers\GitHelpers\GitCheckoutRemoteBranch::execute($gitUtil, $git_command_array);
		}elseif( count($git_command_array) == 2 && $git_command_array[0] == 'merge' && !preg_match('/^remotes\//', $git_command_array[1]) ){
			// `git merge branchname` のフェイク
			// マージする
			// ただし、ここを通過するのはマージ元がローカルブランチの場合のみ。リモートブランチからのマージする場合はフェイクは要らない。
			$rtn = \App\Helpers\GitHelpers\GitMerge::execute($gitUtil, $git_command_array);
		}elseif( count($git_command_array) == 3 && $git_command_array[0] == 'branch' && $git_command_array[1] == '--delete' ){
			// `git branch --delete branchname` のフェイク
			// ブランチを削除する
			$rtn = \App\Helpers\GitHelpers\GitBranchDelete::execute($gitUtil, $git_command_array);
		}else{
			$rtn = $gitUtil->git( $git_command_array );
		}

		$gitUtil->clear_remote_origin();


		// / --------------------------------------


		broadcast(
			new AsyncGeneralProgressEvent(
				$user_id,
				$project_code,
				$branch_name,
				'exit',
				$rtn['return'],
				($rtn['stdout']!==false ? $rtn['stdout'] : ''),
				($rtn['stderr']!==false ? $rtn['stderr'] : ''),
				$channel_name
			)
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
