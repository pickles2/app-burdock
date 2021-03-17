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
				$rtn['result'] = true;
				$rtn['message'] = 'OK';
				$broadcastData = array(
					'result' => true,
					'message' => 'Broadcast Message Recieved!',
				);
				broadcast(new \App\Events\SystemMaintenanceEvent($broadcastData));
				break;
		}

		return $rtn;
	}

}
