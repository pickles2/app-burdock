<?php

namespace App\Http\Controllers\SystemMaintenance;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Project;

class GenerateVhostsController extends \App\Http\Controllers\Controller
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
	 * Show project list.
	 */
	public function index()
	{
		return view(
			'system-maintenance.generate_vhosts.index',
			array(
			)
		);
	}

	/**
	 * bd:generate_vhosts を実行する
	 */
	public function ajaxGenerateVhosts()
	{
		$bdAsync = new \App\Helpers\async();
		$bdAsync->set_channel_name( 'system-mentenance___generate_vhosts' );
		$bdAsync->artisan(
			'bd:generate_vhosts'
		);

		return array(
			'result' => true,
			'message' => 'OK',
		);
	}

}
