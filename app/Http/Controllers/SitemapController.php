<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Project;
use App\Http\Requests\StoreSitemap;

class SitemapController extends Controller
{
    //
    public function index(Project $project, $branch_name)
    {
        //
        return view('sitemaps.index', ['project' => $project, 'branch_name' => $branch_name]);
    }

    public function upload(StoreSitemap $request, Project $project, $branch_name)
    {
        //
        $project_name = $project->project_name;
        $file_name = $request->file;

        $old_file = $file_name;
        $new_file = get_project_workingtree_dir($project_name, $branch_name).'/px-files/sitemaps/sitemap.xlsx';

        if (!copy($old_file, $new_file)) {
            echo "failed to copy $file...\n";
        }

        return redirect('sitemaps/' . $project_name . '/' . $branch_name)->with('my_status', __('Updated a Sitemap.'));
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
