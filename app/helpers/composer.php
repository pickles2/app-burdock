<?php
namespace pickles2\burdock;

use App\Project;

class composer{
	private $project;
	private $project_id;
	private $branch_name;

	/**
	 * Constructor
	 */
	public function __construct( $project = null, $branch_name = null ){
		if(is_null($project)){
			// Project情報に関連付けないで利用する場合
			return;
		}else if(is_object($project)){
			// Projectモデル を受け取った場合
			$this->project = $project;
			$this->project_id = $project->id;
		}else{
			// Project ID を受け取った場合
			$this->project_id = $project;
			$this->project = Project::find($project);
		}

		if( !strlen($branch_name) ){
			$gitUtil = new \pickles2\burdock\git($this->project);
			$branch_name = $gitUtil->get_branch_name();
		}
		$this->branch_name = $branch_name;
	}

	/**
	 * Composerコマンドを実行する
	 */
	public function composer( $composer_sub_command ){
		$cmd = $composer_sub_command;
		if( is_array($composer_sub_command) ){
			$cmd = implode(' ', $composer_sub_command);
		}

		$realpath_pj_git_root = \get_project_workingtree_dir($this->project->project_code, $this->branch_name);

		$cd = realpath('.');
		chdir($realpath_pj_git_root);

		$path_php = env('BD_COMMAND_PHP');
		if(!strlen($path_php)){
			$path_php = 'php';
		}

		ob_start();
		$proc = proc_open($path_php.' '.__DIR__.'/../common/composer/composer.phar '.$cmd, array(
			0 => array('pipe','r'),
			1 => array('pipe','w'),
			2 => array('pipe','w'),
		), $pipes);
		$io = array();
		foreach($pipes as $idx=>$pipe){
			if($idx){
				$io[$idx] = stream_get_contents($pipe);
			}
			fclose($pipe);
		}
		$return_var = proc_close($proc);
		ob_get_clean();

		chdir($cd);

		return array(
			'stdout' => $io[1],
			'stderr' => $io[2],
			'return' => $return_var,
		);
	}

}
