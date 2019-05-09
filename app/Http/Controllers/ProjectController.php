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
        $projects_name = 'projects';
        $project_name = $request->project_name;
        $branchs_name = 'branches';
        $branch_name = 'master';

        $git_url = $request->git_url;
        $git_username = $request->git_username;
        $git_password = $request->git_password;

        $path_current_dir = realpath('.'); // 元のカレントディレクトリを記憶
        if (!is_dir($bd_data_dir)) {
            mkdir($bd_data_dir);
        }
        chdir($bd_data_dir);

        if (!is_dir($projects_name)) {
            mkdir($projects_name);
        }
        chdir($projects_name);

        if (!is_dir($project_name)) {
            mkdir($project_name);
        }
        chdir($project_name);

        if (!is_dir($branchs_name)) {
            mkdir($branchs_name);
        }
        chdir($branchs_name);
        $path_composer = realpath(__DIR__.'/../../common/composer/composer.phar');
        shell_exec($path_composer . ' create-project pickles2/preset-get-start-pickles2 ./' . $branch_name);
        chdir($branch_name);
        $project_path = get_project_workingtree_dir($project_name, $branch_name);
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
            shell_exec('git remote set-url origin https://'.urlencode($git_username).':'.urlencode($git_password).str_replace('https://', '@', urlencode($git_url)));
            shell_exec('git init');
            shell_exec('git add *');
            shell_exec('git commit -m "Create project"');
            if( strlen($git_url) ){
                shell_exec('git remote add origin '.escapeshellarg($git_url));
            }
            $result = shell_exec('git push -u origin master');

            // git pushの結果によって処理わけ
            if($result === null) {
                chdir($path_current_dir); // 元いたディレクトリへ戻る
                \File::deleteDirectory(env('BD_DATA_DIR').'/projects/'.$project_name);
                $message = 'Gitをプッシュできませんでした。URL/Username/Passwordが正しいか確認し、もう一度やり直してください。';
                $redirect = '/';
            } else {
                chdir($path_current_dir); // 元いたディレクトリへ戻る
                // 記事作成時に著者のIDを保存する
                $project = new Project;
                $project->project_name = $project_name;
                $project->user_id = $request->user()->id;
                $project->git_url = $git_url;
                $project->git_username = \Crypt::encryptString($git_username);
                $project->git_password = \Crypt::encryptString($git_password);
                $project->save();
                $message = __('Created new Project.');
                $redirect = 'projects/'.urlencode($project_name).'/'.urlencode($branch_name);
            }
        } else {
            chdir($path_current_dir); // 元いたディレクトリへ戻る
            \File::deleteDirectory(env('BD_DATA_DIR').'/projects/'.$project_name);
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
        //
        $project_name = $project->project_name;
        $project_path = get_project_workingtree_dir($project_name, $branch_name);

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
        rename($project->project_name, $request->project_name);

        $project->project_name = $request->project_name;
        $project->git_url = $request->git_url;
		$project->git_username = \Crypt::encryptString($request->git_username);
		$project->git_password = \Crypt::encryptString($request->git_password);
        $project->save();

        chdir($path_current_dir); // 元いたディレクトリへ戻る

        return redirect('projects/' . $project->project_name . '/' . $branch_name)->with('my_status', __('Updated a Project.'));
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

        $project_name = $project->project_name;
        $project_path = get_project_workingtree_dir($project_name, $branch_name);

        $path_current_dir = realpath('.'); // 元のカレントディレクトリを記憶
        chdir($project_path);
        $data_json = shell_exec('php .px_execute.php /?PX=px2dthelper.get.all\&filter=false\&path='.$page_id);
        $current = json_decode($data_json);
        chdir($path_current_dir); // 元いたディレクトリへ戻る

        $project->delete();
        \File::deleteDirectory(env('BD_DATA_DIR').'/projects/'.$project_name);

        if(\File::exists(env('BD_DATA_DIR').'/projects/'.$project_name) === false) {
            $message = 'Deleted a Project.';
        } else {
            $message = 'プロジェクトを削除できませんでした。';
        }

        return redirect('/')->with('my_status', __($message));
    }
}
