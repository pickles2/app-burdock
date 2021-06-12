<?php

namespace App\Http\Controllers\Space;

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
	public function index(Request $request)
	{

		$eventLogs = EventLog::paginate(100);

		if( !isset( $request->page ) ){
			// 最後のページ(=最新のログ)をデフォルトにする
			return redirect('/space/event-logs?page='.urlencode($eventLogs->lastPage()));
		}


		return view(
			'space.event-logs.index',
			array(
				'eventLogs' => $eventLogs,
			)
		);
	}

}
