<?php

namespace App\Http\Controllers\SystemMaintenance;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IndexController extends \App\Http\Controllers\Controller
{
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('auth');
		$this->middleware('verified');
	}

	/**
	 * Show the application dashboard.
	 */
	public function index()
	{
		return view('system-maintenance.index');
	}


	/**
	 * phpinfo() を表示する
	 */
	public function phpinfo(){
		phpinfo();
		return '';
	}

	/**
	 * コマンドの状態をチェックする
	 */
	public function ajaxCheckCommand(Request $request)
	{
		$rtn = array(
			'result' => null,
		);
		$cmd = $request->cmd;

		$path_php = config('burdock.command_path.php');
		if( !strlen($path_php) ){ $path_php = 'php'; }
		$path_git = config('burdock.command_path.git');
		if( !strlen($path_git) ){ $path_git = 'git'; }

		switch( $cmd ){
			case 'php':
				$rtn['result'] = true;
				$rtn['which'] = shell_exec('which '.$path_php);
				$rtn['version'] = shell_exec($path_php.' -v');
				break;
			case 'composer':
				$path_composer = realpath(__DIR__.'/../../../common/composer/composer.phar');
				$rtn['result'] = true;
				$rtn['which'] = shell_exec('which '.$path_composer);
				$rtn['version'] = shell_exec($path_php.' '.$path_composer.' --version');
				break;
			case 'node':
				$rtn['result'] = true;
				$rtn['which'] = shell_exec('which node');
				$rtn['version'] = shell_exec('node -v');
				break;
			case 'npm':
				$rtn['result'] = true;
				$rtn['which'] = shell_exec('which npm');
				$rtn['version'] = shell_exec('npm -v');
				break;
			case 'git':
				$rtn['result'] = true;
				$rtn['which'] = shell_exec('which '.$path_git);
				$rtn['version'] = shell_exec($path_git.' --version');
				break;
		}
		return $rtn;
	}

}
