<?php
namespace App\Helpers;

use App\Project;

class plumHelper{
	private $project;
    private $fs;

	/**
	 * Constructor
	 */
	public function __construct( $project = null ){
        $this->project = $project;
		$this->fs = new \tomk79\filesystem();
	}

    public function create_plum(){
		$realpath_pj_git_root = env('BD_DATA_DIR').'/projects/'.urlencode($this->project->project_code).'/plum_data_dir/';
		$this->fs->mkdir_r($realpath_pj_git_root);
		$this->fs->mkdir_r(env('BD_DATA_DIR').'/stagings/');

		$staging_server = array();
		for( $i = 1; $i <= 10; $i ++ ){
			array_push($staging_server, array(
				'name' => 'Staging No.'.$i.'',
				'path' => env('BD_DATA_DIR').'/stagings/'.urlencode($this->project->project_code).'---stg'.$i.'/',
				'url' => 'http'.($_SERVER["HTTPS"] ? 's' : '').'://'.urlencode($this->project->project_code).'---stg'.$i.'.'.env('BD_PLUM_STAGING_DOMAIN').'/',
			));
		}

		$git_username = null;
		if( strlen($this->project->git_username) ){
			$git_username = \Crypt::decryptString( $this->project->git_username );
		}
		$git_password = null;
		if( strlen($this->project->git_password) ){
			$git_password = \Crypt::decryptString( $this->project->git_password );
		}

		$plum = new \hk\plum\main(
			array(
				'data_dir' => $realpath_pj_git_root,
				'staging_server' => $staging_server,
				'git' => array(
					'url' => $this->project->git_url,
					'username' => $git_username,
					'password' => $git_password,
				)
			)
		);

        return $plum;
    }

}
