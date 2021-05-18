<?php
namespace App\Helpers;

use App\Project;

class plumHelper{
	private $project;
	private $user_id;
	private $fs;

	/**
	 * Constructor
	 */
	public function __construct( $project = null, $user_id = null ){
		$this->project = $project;
		$this->user_id = $user_id;
		$this->fs = new \tomk79\filesystem();
	}

	/**
	 * Plumを生成する
	 */
	public function create_plum(){
		$realpath_pj_git_root = config('burdock.data_dir').'/projects/'.urlencode($this->project->project_code).'/plum_data_dir/';
		$this->fs->mkdir_r($realpath_pj_git_root);
		$this->fs->mkdir_r(config('burdock.data_dir').'/stagings/');

		$staging_server = array();
		for( $i = 1; $i <= 10; $i ++ ){
			array_push($staging_server, array(
				'name' => 'Staging No.'.$i.'',
				'path' => config('burdock.data_dir').'/stagings/'.urlencode($this->project->project_code).'---stg'.$i.'/',
				'url' => '//'.urlencode($this->project->project_code).'---stg'.$i.'.'.config('burdock.staging_domain').'/',
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
		$plum->set_async_callbacks(array(
			'async' => function( $params ){
				$asyncHelper = new \App\Helpers\async( $this->project );
				$asyncHelper->artisan('bd:plum:async', array(), $params);
			},
			'broadcast' => function( $message ){

				// ブロードキャスト
				broadcast(
					new \App\Events\AsyncPlumEvent(
						$this->user_id,
						$this->project->project_code,
						$message
					)
				);
			}
		));

		return $plum;
	}

}
