<?php

namespace App\Http\Controllers\Space;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Project;
use App\Http\Requests\StoreSitemap;

class BdDataDirController extends Controller
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
	public function index(Request $request){
		return view(
			'space.bd_data_dir.index',
			[
				'filename' => $request->filename
			]
		);
	}

	/**
	 * Show the application dashboard.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function remoteFinderGPI(Request $request){
		$realpath_basedir = config('burdock.data_dir');

		$remoteFinder = new \tomk79\remoteFinder\main(array(
			'default' => realpath($realpath_basedir).'/',
		), array(
			'paths_invisible' => array(
			),
			'paths_readonly' => array(
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
	public function commonFileEditor(Request $request){
		$filename = $request->filename;

		return view(
			'space.bd_data_dir.common_file_editor',
			[
				'filename' => $filename
			]
		);
	}

	/**
	 * Common File Editor: API: read, write
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function commonFileEditorGPI(Request $request){
		$fs = new \tomk79\filesystem();
		$realpath_basedir = config('burdock.data_dir');
		$rtn = array();
		if( !strlen($request->filename) ){
			return json_encode(false);
		}
		$filename = $fs->normalize_path( $fs->get_realpath('/'.$request->filename) );
		if( !strlen($filename) || $filename == '/' ){
			return json_encode(false);
		}
		$realpath_filename = $fs->normalize_path( $fs->get_realpath( $realpath_basedir.$filename) );


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
			$fs->chmod_r( $realpath_filename, 0777 );
			$rtn['result'] = $fs->rm( $realpath_filename );

		}elseif( $request->method == 'px_command' ){
			$rtn['result'] = px2query(
				$project->project_code,
				$branch_name,
				( strlen($filename) ? $filename : '/' ).'?PX='.urlencode($request->px_command)
			);
			$rtn['result'] = json_decode($rtn['result']);

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

}
