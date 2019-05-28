<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Project;
use App\Http\Requests\StoreSitemap;

class StagingController extends Controller
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
	public function index(Request $request, Project $project, $branch_name){

		$page_param = $request->page_path;
		$page_id = $request->page_id;

		$project_name = $project->project_name;
		$project_path = get_project_workingtree_dir($project_name, $branch_name);

		$path_current_dir = realpath('.'); // 元のカレントディレクトリを記憶
		chdir($project_path);
		$data_json = shell_exec('php .px_execute.php /?PX=px2dthelper.get.all\&filter=false\&path='.$page_id);
		$current = json_decode($data_json);
		chdir($path_current_dir); // 元いたディレクトリへ戻る

		$sitemap_files = \File::files($current->realpath_homedir.'sitemaps/');
		foreach($sitemap_files as $file) {
			if($file->getExtension() === 'xlsx') {
				$get_files[] = $file;
			} else {
				$destroy_files[] = $file;
			}
		}
		return view('staging.index', ['project' => $project, 'branch_name' => $branch_name, 'page_param' => $page_param], compact('current', 'get_files'));
	}
}
