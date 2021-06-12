<?php

namespace App\Http\Controllers\Space;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Http\Requests\StoreSitemap;

class MembersController extends Controller
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
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request){

		$activeUsers = User::orderBy('name', 'asc')->get();
		$softDeletedUsers = User::onlyTrashed()->orderBy('name', 'asc')->get();

		$utils = new \App\Helpers\utils();
		$sec_softdelete_retention_period = $utils->resolve_period_config( config('burdock.softdelete_retention_period') );
		$softdelete_retention_period = $sec_softdelete_retention_period;
		$denomination = '秒';
		if( $softdelete_retention_period > 60 ){
			$softdelete_retention_period = $softdelete_retention_period / 60; // 分
			$denomination = '分';
			if( $softdelete_retention_period > 60 ){
				$softdelete_retention_period = $softdelete_retention_period / 60; // 時間
				$denomination = '時間';
				if( $softdelete_retention_period > 24 ){
					$softdelete_retention_period = $softdelete_retention_period / 24; // 日
					$denomination = '日';
				}
			}
		}

		return view(
			'space.members.index',
			[
				'activeUsers' => $activeUsers,
				'softDeletedUsers' => $softDeletedUsers,
				'sec_softdelete_retention_period' => $sec_softdelete_retention_period,
				'softdelete_retention_period' => intval($softdelete_retention_period).$denomination,
				'datetime_to_display' => function($datetime){
					return $datetime;
				}
			]
		);
	}

}
