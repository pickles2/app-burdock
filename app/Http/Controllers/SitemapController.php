<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Project;
use App\Http\Requests\StoreSitemap;

class SitemapController extends Controller
{
    //
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

        return view('sitemaps.index', ['project' => $project, 'branch_name' => $branch_name, 'page_param' => $page_param], compact('current'));
    }

    public function uploadAjax(Request $request, Project $project, $branch_name)
    {
        $status = 0;
        if(isset($request->str)) {
            if(!($request->str === 'text/csv' || $request->str === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')) {
                $error = 'ファイルがcsvまたはxlsxではありません。';
            } else {
                $error = '';
                $status = 1;
            }
        }

        $data = array(
            "error" => $error,
            "status" => $status,
        );
        return $data;
    }

    public function upload(StoreSitemap $request, Project $project, $branch_name)
    {
        //
        $project_name = $project->project_name;
        $file_name = $request->file;
        $old_file = $file_name;
        $mimetype = $file_name->clientExtension();

        if($mimetype === 'csv') {
            $new_file = get_project_workingtree_dir($project_name, $branch_name).'/px-files/sitemaps/sitemap.csv';
        } elseif($mimetype === 'xlsx') {
            $new_file = get_project_workingtree_dir($project_name, $branch_name).'/px-files/sitemaps/sitemap.xlsx';
        }

        if (!copy($old_file, $new_file)) {
            $message = 'Could not update Sitemap.';
        } else {
            $message = 'Updated a Sitemap.';
        }

        return redirect('sitemaps/' . $project_name . '/' . $branch_name)->with('my_status', __($message));
    }

    public function download(Request $request, Project $project, $branch_name)
    {
        //
        $project_name = $project->project_name;

        if($request->file === 'csv') {
            // CSVファイルのダウンロード
            $pathToFile = get_project_workingtree_dir($project_name, $branch_name).'/px-files/sitemaps/sitemap.csv';
            $name = 'sitemap.csv';
            return response()->download($pathToFile, $name);
        } elseif($request->file === 'xlsx') {
            // XLSXファイルのダウンロード
            $pathToFile = get_project_workingtree_dir($project_name, $branch_name).'/px-files/sitemaps/sitemap.xlsx';
            $name = 'sitemap.xlsx';
            return response()->download($pathToFile, $name);
        }
    }
}
