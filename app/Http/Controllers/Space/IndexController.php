<?php

namespace App\Http\Controllers\Space;

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
		return view('space.index');
	}

}
