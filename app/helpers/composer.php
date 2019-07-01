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
	public function __construct( $project_id, $branch_name ){
		$this->project_id = $project_id;
		$this->project = Project::find($project_id);

		if( !strlen($branch_name) ){
			$branch_name = \get_git_remote_default_branch_name($project->git_url);
		}
		$this->branch_name = $branch_name;
	}

	public function composer( $composer_sub_command ){
		$cmd = $composer_sub_command;
		if( is_array($composer_sub_command) ){
			$cmd = implode(' ', $composer_sub_command);
		}

		$realpath_pj_git_root = \get_project_workingtree_dir($this->project->project_code, $this->branch_name);

		$cd = realpath('.');
		chdir($realpath_pj_git_root);

		ob_start();
		$proc = proc_open(__DIR__.'/../common/composer/composer.phar '.$cmd, array(
			0 => array('pipe','r'),
			1 => array('pipe','w'),
			2 => array('pipe','w'),
		), $pipes);
		$io = array();
		foreach($pipes as $idx=>$pipe){
			$io[$idx] = stream_get_contents($pipe);
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
