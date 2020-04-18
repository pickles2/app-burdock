<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Project;
use App\Http\Requests\StoreUser;

class HomeController extends Controller
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
	public function index(Project $project, $branch_name)
	{
		$bd_object = px2query(
			$project->project_code,
			$branch_name,
			'/?PX=px2dthelper.get.all'
		);
		$bd_object = json_decode($bd_object);
		if($bd_object) {
			return view('home', [
				'project' => $project,
				'branch_name' => $branch_name
			], compact('bd_object'));
		} elseif(session('my_status')) {
			$message = session('my_status');
			return redirect('setup/'.$project->project_code.'/'.$branch_name)->with('my_status', __($message));
		} else {
			return redirect('setup/'.$project->project_code.'/'.$branch_name);
		}
	}
}
