<?php

namespace App\Http\Controllers\SystemMaintenance;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HealthCheckController extends \App\Http\Controllers\Controller
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

		$laravelEchoJson = json_decode(file_get_contents(__DIR__.'/../../../../laravel-echo-server.json'));
		$broadcast_endpoint_url = $laravelEchoJson->authHost.':'.$laravelEchoJson->port;

		return view(
			'system-maintenance.healthcheck.index',
			array(
				'broadcast_endpoint_url' => $broadcast_endpoint_url,
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
			case 'broadcast':
				// ブロードキャストメッセージを送信する
				$broadcastData = array(
					'result' => true,
					'message' => '[SUCCESS] Broadcast is working!',
				);
				broadcast(new \App\Events\SystemMaintenanceHealthCheckEvent($broadcastData));

				$rtn['result'] = true;
				$rtn['message'] = 'Broadcast request recieved.';
				break;

			case 'async':
				// 非同期処理をテストする
				$bdAsync = new \App\Helpers\async();
				$bdAsync->set_channel_name( 'system-mentenance___async.broadcast' );
				$bdAsync->cmd(
					array('ls', '-la')
				);

				$rtn['result'] = true;
				$rtn['message'] = 'Async request recieved.';
				break;

			case 'queue':
				// テストキューを発行する
				\App\Jobs\SystemMaintenanceHealthCheckJob::dispatch();

				$rtn['result'] = true;
				$rtn['message'] = 'Queue request recieved.';
				break;
		}

		return $rtn;
	}

}
