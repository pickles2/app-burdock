<?php

namespace App\Http\Controllers\Ajax;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreSitemap;
use App\Http\Controllers\Controller;
use App\Project;

class PageController extends Controller
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

    public function editAjax(Request $request, Project $project, $branch_name)
    {
        //
        $project_path = get_project_workingtree_dir($project->project_code, $branch_name);

        $path_current_dir = realpath('.'); // 元のカレントディレクトリを記憶
        chdir($project_path);
        shell_exec('git remote set-url origin https://'.urlencode(\Crypt::decryptString($project->git_username)).':'.urlencode(\Crypt::decryptString($project->git_password)).str_replace('https://', '@', $project->git_url));
        shell_exec('git fetch');
        $check = shell_exec('git diff origin/master');

        if($check === null) {
            $result = false;
        } else {
            shell_exec('git add -A');
            shell_exec('git commit -m "'.$request->str.'"');
            shell_exec('git push origin master:master');
            shell_exec('git fetch');
            $result = shell_exec('git diff origin/master');
        }
        chdir($path_current_dir); // 元いたディレクトリへ戻る

        if($result === null) {
            $message = 'プッシュしました。';
        } elseif($result === false) {
            $message = 'プッシュできる差分がありませんでした。';
        } else {
            $message = 'プッシュできませんでした。';
        }

        $data = array(
            "message" => $message,
        );
        return $data;
    }

	public function searchAjax(Request $request, Project $project, $branch_name)
	{
		//
		$str = $request->str;
		$option = ' /?PX=px2dthelper.search_sitemap\&keyword='.urlencode($str);
		$info = get_px_execute($project->project_code, $branch_name, $option);

		$data = array(
			"info" => $info,
		);

		return $data;
	}
}
