<?php

namespace App\Http\Controllers\SystemMaintenance;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\EventLog;

class EventLogController extends \App\Http\Controllers\Controller
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

		$eventLogs = EventLog::paginate(10);

		return view(
			'system-maintenance.event-logs.index',
			array(
				'eventLogs' => $eventLogs,
			)
		);
	}

}
