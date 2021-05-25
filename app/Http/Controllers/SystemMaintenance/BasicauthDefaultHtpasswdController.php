<?php

namespace App\Http\Controllers\SystemMaintenance;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BasicauthDefaultHtpasswdController extends \App\Http\Controllers\Controller
{

	/** BD_DATA_DIR */
	private $realpath_vhosts_dir;

	/** 基本認証のデフォルトパスワードファイル */
	private $realpath_basicauth_default_htpasswd;

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('auth');
		$this->middleware('verified');

		$this->realpath_vhosts_dir = config('burdock.data_dir').'/vhosts/';
		$this->realpath_basicauth_default_htpasswd = $this->realpath_vhosts_dir.'default.htpasswd';
	}

	/**
	 * Show the application dashboard.
	 */
	public function index()
	{

		$basicauth_id = null;
		if( is_file($this->realpath_basicauth_default_htpasswd) ){
			$bin = file_get_contents($this->realpath_basicauth_default_htpasswd);
			$bin_ary = preg_split('/\:/', $bin);
			$basicauth_id = $bin_ary[0];
		}

		return view(
			'system-maintenance.basicauth-default-htpasswd.index',
			array(
				'basicauth_id' => $basicauth_id,
			)
		);
	}

	/**
	 * Ajax Endpoint
	 */
	public function ajax(Request $request)
	{
		$rtn = array(
			'result' => null,
			'message' => null,
		);
		$cmd = $request->cmd;

		switch( $cmd ){
			case 'update':
				if( strlen( $request->basicauth_id ) ){
					// 更新する

					$file_content = '';
					$file_content .= $request->basicauth_id.":";


					// パスワードハッシュを生成する
					if( !strlen($request->basicauth_pw) && is_file($this->realpath_basicauth_default_htpasswd) ){
						// パスワードが指定されなかった場合: 現状維持
						$bin = file_get_contents($this->realpath_basicauth_default_htpasswd);
						$bin_ary = preg_split('/\:/', $bin);
						$hashed_passwd = $bin_ary[1];
						$file_content .= $hashed_passwd;

					}else{
						// パスワードが指定された場合: ハッシュ化して保存

						$basicauth_password = $request->basicauth_pw;
						$hashed_passwd = $basicauth_password;
						$hash_algorithm = config('burdock.htpasswd_hash_algorithm');
						switch( $hash_algorithm ){
							case 'bcrypt':
								$hashed_passwd = password_hash($basicauth_password, PASSWORD_BCRYPT);
								break;

							case 'md5':
								$hashed_passwd = md5($basicauth_password);
								break;

							case 'sha1':
								$hashed_passwd = sha1($basicauth_password);
								break;

							case 'plain':
								$hashed_passwd = $basicauth_password;
								break;

							case 'crypt':
							default:
								$hashed_passwd = crypt($basicauth_password, substr(trim($request->basicauth_user_name), -2));
								break;

						}

						$file_content .= $hashed_passwd;
					}

					file_put_contents( $this->realpath_basicauth_default_htpasswd, $file_content );

				}elseif( is_file($this->realpath_basicauth_default_htpasswd) ){
					// 削除する
					unlink($this->realpath_basicauth_default_htpasswd);
				}

				// --------------------------------------
				// vhosts.conf を更新する
				$bdAsync = new \App\Helpers\async();
				$bdAsync->set_channel_name( 'system-mentenance___generate_vhosts' );
				$bdAsync->artisan(
					'bd:generate_vhosts'
				);

				$rtn['result'] = true;
				$rtn['message'] = 'Default Basic Auth Password is Updated.';
				break;
		}

		return $rtn;
	}

}
