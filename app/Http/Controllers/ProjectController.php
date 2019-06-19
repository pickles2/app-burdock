<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Project;
use App\Http\Requests\StoreProject;

class ProjectController extends Controller
{
	/**
	 * 各アクションの前に実行させるミドルウェア
	 */
	public function __construct()
	{
		// ログイン・登録完了してなくても閲覧だけはできるようにexcept()で指定します。
		$this->middleware('auth');
		$this->middleware('verified');
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		//
		// 1. 新しい順に取得できない
		// $projects = Project::all();

		// 2. 記述が長くなる
		// $projects = Project::orderByDesc('created_at')->get();

		// 3. latestメソッドがおすすめ
		// ページネーション（1ページに5件表示）
		$projects = Project::latest()->paginate(5);
		// Debugbarを使ってみる
		\Debugbar::info($projects);
		return view('projects.index', ['projects' => $projects]);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		//
		return view('projects.create');
	}

	/**
	 * Store a newly created resource in storage.
	 * 新しい記事を保存する
	 * @param  \App\Http\Requests\StoreProject $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(StoreProject $request)
	{
		//
		$bd_data_dir = env('BD_DATA_DIR');
		$branch_name = 'master';

		$git_url = $request->git_url;
		$git_username = $request->git_username;
		$git_password = $request->git_password;

		$path_current_dir = realpath('.'); // 元のカレントディレクトリを記憶
		$path_branches_dir = $bd_data_dir.'/projects/'.urlencode($request->project_code).'/branches/'.urlencode($branch_name).'/';

		\File::makeDirectory($path_branches_dir, 0777, true, true);

		$path_composer = realpath(__DIR__.'/../../common/composer/composer.phar');
		chdir($path_branches_dir);
		shell_exec($path_composer . ' create-project pickles2/preset-get-start-pickles2 ./');
		chdir($path_current_dir);
		clearstatcache();

		$project_path = get_project_workingtree_dir($request->project_code, $branch_name);

		// 記事作成時に著者のIDを保存する
		$project = new Project;
		$project->project_code = $request->project_code;
		$project->project_name = $project->project_name;
		$project->user_id = $request->user()->id;
		$project->git_url = $git_url;
		$project->git_username = \Crypt::encryptString($git_username);
		$project->git_password = \Crypt::encryptString($git_password);
		$project->save();

		// .px_execute.phpの存在確認
		if(\File::exists($project_path.'/.px_execute.php')) {
			// ここから configのmaster_formatをtimestampに変更してconfig.phpに上書き保存
			$files = null;
			$file = file($project_path.'/px-files/config.php');
			for($i = 0; $i < count($file); $i++) {
				if(strpos($file[$i], "'master_format'=>'xlsx'") !== false) {
					$files .= str_replace('xlsx', 'timestamp', $file[$i]);
				} else {
					$files .= $file[$i];
				}
			}
			file_put_contents($project_path.'/px-files/config.php', $files);

			// ここまで configのmaster_formatをtimestampに変更してconfig.phpに上書き保存
			clearstatcache();
			chdir($path_branches_dir);
			$git_url_plus_auth = $git_url;
			if( strlen($git_username) ){
				$parsed_git_url = parse_url($git_url_plus_auth);
				$git_url_plus_auth = '';
				$git_url_plus_auth .= $parsed_git_url['scheme'].'://';
				$git_url_plus_auth .= urlencode($git_username);
				$git_url_plus_auth .= ':'.urlencode($git_password);
				$git_url_plus_auth .= '@';
				$git_url_plus_auth .= $parsed_git_url['host'];
				if( array_key_exists('port', $parsed_git_url) && strlen($parsed_git_url['port']) ){
					$git_url_plus_auth .= ':'.$parsed_git_url['port'];
				}
				$git_url_plus_auth .= $parsed_git_url['path'];
				if( array_key_exists('query', $parsed_git_url) && strlen($parsed_git_url['query']) ){
					$git_url_plus_auth .= '?'.$parsed_git_url['query'];
				}
			}

			shell_exec('git remote set-url origin '.escapeshellarg($git_url));
			shell_exec('git init');
			shell_exec('git add *');
			shell_exec('git commit -m "Create project"');
			if( strlen($git_url) ){
				shell_exec('git remote add origin '.escapeshellarg($git_url));
			}

			// push するときは認証情報が必要なので、
			// 認証情報付きのURLで実行する
			$result = shell_exec('git push -u '.escapeshellarg($git_url_plus_auth).' master:master');
			chdir($path_current_dir);

			// git pushの結果によって処理わけ
			if($result === null) {
				chdir($path_current_dir); // 元いたディレクトリへ戻る
				\File::deleteDirectory(env('BD_DATA_DIR').'/projects/'.$project->project_name);
				$message = 'Gitをプッシュできませんでした。URL/Username/Passwordが正しいか確認し、もう一度やり直してください。';
				$redirect = '/';
			} else {
				chdir($path_current_dir); // 元いたディレクトリへ戻る
				$message = __('Created new Project.');
				$redirect = 'projects/'.urlencode($project->project_name).'/'.urlencode($branch_name);
			}
		} else {
			chdir($path_current_dir); // 元いたディレクトリへ戻る
			\File::deleteDirectory(env('BD_DATA_DIR').'/projects/'.$project->project_name);
			$message = 'プロジェクトを作成できませんでした。もう一度やり直してください。';
			$redirect = '/';
		}

		return redirect($redirect)->with('my_status', __($message));
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show(Project $project, $branch_name)
	{
		$project_path = get_project_workingtree_dir($project->project_code, $branch_name);

		$path_current_dir = realpath('.'); // 元のカレントディレクトリを記憶
		chdir($project_path);
		$bd_json = shell_exec('php .px_execute.php /?PX=px2dthelper.get.all');
		$bd_object = json_decode($bd_json);
		chdir($path_current_dir); // 元いたディレクトリへ戻る

		return view('projects.show', ['project' => $project, 'branch_name' => $branch_name], compact('bd_object'));
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit(Project $project, $branch_name)
	{
		//
		// update, destroyでも同様に
		$this->authorize('edit', $project);
		return view('projects.edit', ['project' => $project, 'branch_name' => $branch_name]);
	}

	/**
	 * Update the specified resource in storage.
	 * 記事の更新を保存する
	 * @param  \App\Http\Requests\StoreProject $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(StoreProject $request, Project $project, $branch_name)
	{
		//
		$this->authorize('edit', $project);

		$bd_data_dir = env('BD_DATA_DIR');
		$path_current_dir = realpath('.'); // 元のカレントディレクトリを記憶

		chdir($bd_data_dir . '/projects/');
		rename($project->project_code, $request->project_code);

		$project->project_code = $request->project_code;
		$project->project_name = $request->project_name;
		$project->git_url = $request->git_url;
		$project->git_username = \Crypt::encryptString($request->git_username);
		$project->git_password = \Crypt::encryptString($request->git_password);
		$project->save();

		chdir($path_current_dir); // 元いたディレクトリへ戻る

		return redirect('projects/' . $project->project_code . '/' . $branch_name)->with('my_status', __('Updated a Project.'));
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy(Request $request, Project $project, $branch_name)
	{
		//
		$page_param = $request->page_path;
		$page_id = $request->page_id;

		$project_code = $project->project_code;
		$project_path = get_project_workingtree_dir($project_code, $branch_name);

		$path_current_dir = realpath('.'); // 元のカレントディレクトリを記憶
		chdir($project_path);
		$data_json = shell_exec('php .px_execute.php /?PX=px2dthelper.get.all\&filter=false\&path='.$page_id);
		$current = json_decode($data_json);
		chdir($path_current_dir); // 元いたディレクトリへ戻る

		$project->delete();
		\File::deleteDirectory(env('BD_DATA_DIR').'/projects/'.$project_code);

		if(\File::exists(env('BD_DATA_DIR').'/projects/'.$project_code) === false) {
			$message = 'Deleted a Project.';
		} else {
			$message = 'プロジェクトを削除できませんでした。';
		}

		return redirect('/')->with('my_status', __($message));
	}
}
