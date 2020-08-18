<?php

namespace App\Http\Controllers\SystemMaintenance;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Project;
use App\Http\Requests\StoreUser;

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
		switch( $cmd ){
			case 'php':
				$rtn['result'] = true;
				$rtn['which'] = shell_exec('which php');
				$rtn['version'] = shell_exec('php -v');
				break;
			case 'composer':
				$path_composer = realpath(__DIR__.'/../../../common/composer/composer.phar');
				$rtn['result'] = true;
				$rtn['which'] = shell_exec('which '.$path_composer);
				$rtn['version'] = shell_exec($path_composer.' --version');
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
				$rtn['which'] = shell_exec('which git');
				$rtn['version'] = shell_exec('git --version');
				break;
		}
		return $rtn;
	}

}
