<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Page;
use App\Project;

class PageController extends Controller
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

    public function index(Request $request, Project $project, $branch_name)
    {
        $page_param = $request->page_path;
        $page_id = $request->page_id;

        $project_name = $project->project_name;
        $project_path = get_project_workingtree_dir($project_name, $branch_name);

        $path_current_dir = realpath('.'); // 元のカレントディレクトリを記憶
        chdir($project_path);
        $data_json = shell_exec('php .px_execute.php /?PX=px2dthelper.get.all\&filter=false\&path='.$page_id);
        $current = json_decode($data_json);
        $data_json = shell_exec('php .px_execute.php /?PX=px2dthelper.check_editor_mode\&path='.$page_param);
        $editor_type = json_decode($data_json);
        chdir($path_current_dir); // 元いたディレクトリへ戻る

        return view('pages.index', ['project' => $project, 'branch_name' => $branch_name, 'page_param' => $page_param], compact('current', 'editor_type'));
    }


    public function ajax(Request $request, Project $project, $branch_name)
    {
        $page_path = $request->path_path;
        $project_name = $project->project_name;
        $project_path = get_project_workingtree_dir($project_name, $branch_name);

        $path_current_dir = realpath('.'); // 元のカレントディレクトリを記憶
        chdir($project_path);
        $result = shell_exec('php .px_execute.php '.$page_path.'?PX=px2dthelper.get.all');
        chdir($path_current_dir); // 元いたディレクトリへ戻る

        $info = json_decode($result, true);
        $path = $info['page_info']['path'];
        $id = $info['page_info']['id'];

        $data = array(
            "path" => $path,
            "id" => $id,
        );
        return $data;
    }


    public function show(Request $request, Project $project, $branch_name)
    {
        //
        $page_param = $request->page_path;
        $client_resources_dist = realpath(__DIR__.'/../../../public/assets/px2ce_resources');

        $project_name = $project->project_name;
        $project_path = get_project_workingtree_dir($project_name, $branch_name);
        $path_current_dir = realpath('.'); // 元のカレントディレクトリを記憶
        chdir($project_path);
        $result = shell_exec('php .px_execute.php /sample_pages/?PX=px2dthelper.px2ce.client_resources\&dist='.$client_resources_dist);
        $px2ce_client_resources = json_decode($result, true);
        chdir($path_current_dir); // 元いたディレクトリへ戻る

        return view('pages.show', ['project' => $project, 'branch_name' => $branch_name, 'page_param' => $page_param], compact('px2ce_client_resources'));
    }


    public function gpi(Request $request, Project $project, $branch_name)
    {
        //
        $page_param = $request->page_path;
        $project_name = $project->project_name;
        $project_path = get_project_workingtree_dir($project_name, $branch_name);

        $path_current_dir = realpath('.'); // 元のカレントディレクトリを記憶
        chdir($project_path);

        $data_json = shell_exec('php .px_execute.php /?PX=px2dthelper.get.all');
        $current = json_decode($data_json);

        // ミリ秒を含むUnixタイムスタンプを数値（Float）で取得
        $timestamp = microtime(true);

        // ミリ秒とそうでない部分を分割
        $timeInfo = explode('.', $timestamp);

        // ミリ秒でない時間の部分を指定のフォーマットに変換し、その末尾にミリ秒を追加
        $timeWithMillisec = date('YmdHis', $timeInfo[0]).$timeInfo[1];
        // 一時ファイル名を作成
        $tmpFileName = '__tmp_'.md5($timeWithMillisec).'_data.json';
        // 一時ファイルを保存
        $file = $current->realpath_homedir.'_sys/ram/data/'.$tmpFileName;
        file_put_contents($file, $request->data);

        $result = shell_exec('php .px_execute.php '.$page_param.'?PX=px2dthelper.px2ce.gpi\&data_filename='.$tmpFileName);

        header('Content-type: text/json');
        echo $result;
        // 作成した一時ファイルを削除
        unlink($file);

        chdir($path_current_dir); // 元いたディレクトリへ戻る
        exit;
    }
}
