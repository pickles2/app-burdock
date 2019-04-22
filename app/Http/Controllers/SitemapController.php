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

        $sitemap_files = \File::files($current->realpath_homedir.'sitemaps/');
        foreach($sitemap_files as $file) {
            if($file->getExtension() === 'xlsx') {
                $get_files[] = $file;
            } else {
                $destroy_files[] = $file;
            }
        }
        return view('sitemaps.index', ['project' => $project, 'branch_name' => $branch_name, 'page_param' => $page_param], compact('current', 'get_files'));
    }

    public function upload(StoreSitemap $request, Project $project, $branch_name)
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

        $upload_file = $request->file;
        $old_file = $upload_file;
        $mimetype = $upload_file->clientExtension();
        $file_name = $upload_file->getClientOriginalName();
        $new_file = $current->realpath_homedir.'sitemaps/'.$file_name;

        if (!copy($old_file, $new_file)) {
            $message = 'Could not update Sitemap.';
        } else {
            $path_current_dir = realpath('.'); // 元のカレントディレクトリを記憶
            chdir($project_path);
            shell_exec('git remote set-url origin https://'.urlencode(\Crypt::decryptString($project->git_username)).':'.urlencode(\Crypt::decryptString($project->git_password)).str_replace('https://', '@', $project->git_url));
            shell_exec('git fetch');
            $check = shell_exec('git diff origin/master');

            if($check === null) {
                $result = false;
            } else {
                shell_exec('php .px_execute.php /?PX=px2dthelper.get.all\&filter=false\&path='.$page_id);
                shell_exec('git add *');
                shell_exec('git commit -m "Edit Sitemap"');
                shell_exec('git push origin master:master');
            }
            chdir($path_current_dir); // 元いたディレクトリへ戻る

            $message = 'Updated a Sitemap.';
        }

        return redirect('sitemaps/'.$project_name.'/'.$branch_name)->with('my_status', __($message));
    }

    public function download(Request $request, Project $project, $branch_name)
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

        if($request->file === 'csv') {
            // CSVファイルのダウンロード
            $file_name = $request->file_name;
            $csv_file_name = str_replace('xlsx', 'csv', $file_name);
            $pathToFile = $current->realpath_homedir.'sitemaps/'.$csv_file_name;
            $name = $csv_file_name;
            return response()->download($pathToFile, $name);
        } elseif($request->file === 'xlsx') {
            // XLSXファイルのダウンロード
            $xlsx_file_name = $request->file_name;
            $pathToFile = $current->realpath_homedir.'sitemaps/'.$xlsx_file_name;
            $name = $xlsx_file_name;
            return response()->download($pathToFile, $name);
        }
    }

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

        $xlsx_file_name = $request->file_name;
        $csv_file_name = str_replace('xlsx', 'csv', $xlsx_file_name);
        $xlsx_file_path = $current->realpath_homedir.'sitemaps/'.$xlsx_file_name;
        $csv_file_path = $current->realpath_homedir.'sitemaps/'.$csv_file_name;

        \File::delete($xlsx_file_path, $csv_file_path);

        if(\File::exists($xlsx_file_path) === false && \File::exists($csv_file_path) === false) {
            $message = $xlsx_file_name.'と'.$csv_file_name.'を削除しました。';
        } else {
            $message = 'サイトマップを削除できませんでした。';
        }

        return redirect('sitemaps/'.$project_name.'/'.$branch_name)->with('my_status', __($message));
    }
}
