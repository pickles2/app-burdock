<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Project;
use App\Http\Requests\StoreSitemap;

class FilesAndFoldersController extends Controller
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
		return view(
			'files_and_folders.index',
			[
				'bootstrap' => 4,
				'project' => $project,
				'branch_name' => $branch_name
			]
		);
	}

	/**
	 * Show the application dashboard.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function remoteFinderGPI(Request $request, Project $project, $branch_name){
		$realpath_basedir = get_project_workingtree_dir($project->project_code, $branch_name);

		$remoteFinder = new \tomk79\remoteFinder\main(array(
			'default' => realpath($realpath_basedir).'/',
		), array(
			'paths_invisible' => array(
			),
			'paths_readonly' => array(
				'/.git/*',
				'/vendor/*',
				'/node_modules/*',
			),
		));

		$value = $remoteFinder->gpi( json_decode( $request->data ) );
		return json_encode($value);
	}


	/**
	 * Common File Editor
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function commonFileEditor(Request $request, Project $project, $branch_name){
		$filename = $request->filename;

		return view(
			'files_and_folders.common_file_editor',
			[
				'bootstrap' => 4,
				'project' => $project,
				'branch_name' => $branch_name,
				'filename' => $filename
			]
		);
	}

	/**
	 * Common File Editor: API: read, write
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function commonFileEditorGPI(Request $request, Project $project, $branch_name){
		$fs = new \tomk79\filesystem();
		$realpath_basedir = get_project_workingtree_dir($project->project_code, $branch_name);
		$rtn = array();
		$filename = $fs->get_realpath('/'.$request->filename);
		if( !strlen($filename) ){
			return json_encode(false);
		}
		$realpath_filename = $realpath_basedir.$filename;


		if( $request->method == 'read' ){
			$bin = \File::get( $realpath_filename );
			$rtn['base64'] = base64_encode($bin);

		}elseif( $request->method == 'write' ){
			$bin = '';
			if( strlen($request->base64) ){
				$bin = base64_decode( $request->base64 );
			}elseif( strlen($request->bin) ){
				$bin = $request->bin;
			}
			$rtn['result'] = \File::put( $realpath_filename, $bin );

		}elseif( $request->method == 'copy' ){
			$realpath_copyto = $realpath_basedir.$fs->get_realpath('/'.$request->to);
			$rtn['result'] = $fs->copy_r( $realpath_filename, $realpath_copyto );

		}elseif( $request->method == 'rename' ){
			$realpath_copyto = $realpath_basedir.$fs->get_realpath('/'.$request->to);
			$rtn['result'] = $fs->rename_f( $realpath_filename, $realpath_copyto );

		}elseif( $request->method == 'is_file' ){
			$rtn['result'] = is_file( $realpath_filename );

		}elseif( $request->method == 'is_dir' ){
			$rtn['result'] = is_dir( $realpath_filename );

		}elseif( $request->method == 'exists' ){
			$rtn['result'] = file_exists( $realpath_filename );

		}elseif( $request->method == 'remove' ){
			$rtn['result'] = $fs->rm( $realpath_filename );

		}elseif( $request->method == 'px_command' ){
			$rtn['result'] = px2query(
				$project->project_code,
				$branch_name,
				( strlen($filename) ? $filename : '/' ).'?PX='.urlencode($request->px_command)
			);

		}elseif( $request->method == 'initialize_data_dir' ){
			$json = px2query(
				$project->project_code,
				$branch_name,
				( strlen($filename) ? $filename : '/' ).'?PX=px2dthelper.get.all'
			);
			$json = json_decode($json);

			$rtn['result'] = false;
			if( $fs->mkdir_r( $json->realpath_data_dir ) ){
				if( $fs->save_file( $json->realpath_data_dir.'data.json', '{}' ) ){
					$rtn['result'] = true;
				}
			}

		}

		return json_encode($rtn);
	}

	/**
	 * ファイルのパスを、Pickles 2 の外部パス(path)に変換する。
	 *
	 * Pickles 2 のパスは、 document_root と cont_root を含まないが、
	 * ファイルのパスはこれを一部含んでいる可能性がある。
	 * これを確認し、必要に応じて除いたパスを返却する。
	 */
	public function apiParsePx2FilePath(Request $request, Project $project, $branch_name){
		$fs = new \tomk79\filesystem();
		$rtn = array();
		$pxExternalPath = $request->get('path');
		$pxExternalPath = preg_replace( '/^\/*/', '', $pxExternalPath );
		$realpath_basedir = get_project_workingtree_dir($project->project_code, $branch_name);
		$realpath_file = $fs->normalize_path($fs->get_realpath($realpath_basedir.$pxExternalPath));

		$is_file = is_file($realpath_file);

		$burdockProjectManager = new \tomk79\picklesFramework2\burdock\projectManager\main( env('BD_DATA_DIR') );
		$project_branch = $burdockProjectManager->project($project->project_code)->branch($branch_name, 'preview');

		$pageInfoAll = $project_branch->query(
			'/?PX=px2dthelper.get.all',
			array(
				'output' => 'json'
			)
		);
		$rtn['pageInfoAll'] = $pageInfoAll;


		// --------------------------------------
		// 外部パスを求める
		if( preg_match( '/^'.preg_quote($pageInfoAll->realpath_docroot, '/').'/', $realpath_file) ){
			$pxExternalPath = preg_replace('/^'.preg_quote($pageInfoAll->realpath_docroot, '/').'/', '/', $realpath_file);
		}
		if( preg_match( '/^'.preg_quote($pageInfoAll->path_controot, '/').'/', $pxExternalPath) ){
			$pxExternalPath = preg_replace('/^'.preg_quote($pageInfoAll->path_controot, '/').'/', '/', $pxExternalPath);
		}
		$rtn['pxExternalPath'] = $pxExternalPath;


		// --------------------------------------
		// パスの種類を求める
		// theme_collection, home_dir, or contents
		$path_type = 'contents';
		$realpath_target = $fs->normalize_path($realpath_file);
		$realpath_homedir = $fs->normalize_path($pageInfoAll->realpath_homedir);
		$realpath_theme_collection_dir = $fs->normalize_path($pageInfoAll->realpath_theme_collection_dir);
		if( preg_match('/^'.preg_quote($realpath_theme_collection_dir, '/').'/', $realpath_target) ){
			$path_type = 'theme_collection';
		}elseif( preg_match('/^'.preg_quote($realpath_homedir, '/').'/', $realpath_target) ){
			$path_type = 'home_dir';
		}
		$rtn['path_type'] = $path_type;

		return json_encode($rtn);
	}

}
