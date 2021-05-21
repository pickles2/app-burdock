<?php
namespace App\Helpers\GitHelpers;
class GitBranch
{

	/**
	 * `git branch` のフェイク処理
	 */
	public static function execute($gitUtil, $git_command_array){
		$stdout = '';
		$fs = new \tomk79\filesystem();

		$realpath_pj_git_root = \get_project_workingtree_dir($gitUtil->get_project_code(), $gitUtil->get_branch_name());


		// ローカルのブランチの取得方法をフェイクする
		$filelist = $fs->ls($realpath_pj_git_root.'../');
		foreach( $filelist as $filename ){
			if( is_dir( $realpath_pj_git_root.'../'.$filename ) ){
				if(preg_match('/^(.*?)\-\-\-(.*)$/', $filename, $matched)){
					$tmp_project_code = $matched[1];
					$tmp_branch_name = $matched[2];
					if($tmp_project_code == $gitUtil->get_project_code()){
						$stdout .= ( $gitUtil->get_branch_name() == $tmp_branch_name ? '*' : ' ' ).' '.$tmp_branch_name."\n";
					}
				}
			}
		}


		// リモートのブランチを取得して補完
		$gitUtil->git(array('fetch'));
		$result = $gitUtil->git(array('branch', '-r'));
		$lines = preg_split('/\r\n|\r|\n/', $result['stdout']);
		$remote_branches = array();
		foreach($lines as $line){
			$line = trim($line);
			if(!strlen($line)){
				continue;
			}
			$line = preg_replace('/^(\s*)/', '$1remotes/', $line);
			$stdout .= $line."\n";
		}


		// 返す
		$cmd_result = array(
			'stdout' => trim($stdout),
			'stderr' => '',
			'return' => 0,
		);
		return $cmd_result;
	}
}
