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
        $project = new Project;
        $project->project_name = $request->project_name;
        $project->git_url = $request->git_url;

        $bd_data_dir = env('BD_DATA_DIR');
        $projects_name = 'projects';
        $project_name = $project->project_name;
        $branchs_name = 'branches';
        $branch_name = 'master';

        $git_url = $project->git_url;

        $path_current_dir = realpath('.'); // 元のカレントディレクトリを記憶

        if (!is_dir($bd_data_dir)) {
            mkdir($bd_data_dir);
        }
        chdir($bd_data_dir);

        if (!is_dir($projects_name)) {
            mkdir($projects_name);
        }
        chdir($projects_name);

        if (!is_dir("project_" . $project_name)) {
            mkdir("project_" . $project_name);
        }
        chdir("project_" . $project_name);

        if (!is_dir($branchs_name)) {
            mkdir($branchs_name);
        }
        chdir($branchs_name);

        $path_composer = realpath(__DIR__.'/../../common/composer/composer.phar');
        shell_exec($path_composer . ' create-project pickles2/preset-get-start-pickles2 ./' . $branch_name);
        chdir($branch_name);

        $project_path = get_project_workingtree_dir($project_name, $branch_name);

        // 記事作成時に著者のIDを保存する
        $project->user_id = $request->user()->id;
        $project->save();

        shell_exec('git init');
        shell_exec('git add -A');
        shell_exec('git commit -m "Create project"');
        if( strlen($git_url) ){
            shell_exec('git remote add origin ' . escapeshellarg($git_url));
        }
        shell_exec('git push origin master');

        chdir($path_current_dir); // 元いたディレクトリへ戻る

        return redirect('projects/' . urlencode($project->project_name) . '/' . urlencode($branch_name))->with('my_status', __('Created new Project.'));
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
        rename('project_'. $project->project_name, 'project_'. $request->project_name);

        $project->project_name = $request->project_name;
        $project->git_url = $request->git_url;
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
    public function destroy(Project $project, $branch_name)
    {
        //
        $this->authorize('edit', $project);
        $project->delete();
        return redirect('/')->with('my_status', __('Deleted a Project.'));
    }
}
