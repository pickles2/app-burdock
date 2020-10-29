<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Project;
use App\Http\Requests\StoreSitemap;

class SearchController extends Controller
{

	private $fs;
	private $project_path;
	private $pageInfoAll;

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('auth');
		$this->middleware('verified');
		$this->fs = new \tomk79\filesystem();
	}

	/**
	 * Show the application dashboard.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request, Project $project, $branch_name){

		return view(
			'search.index',
			[
				'project' => $project,
				'branch_name' => $branch_name,
			]
		);
	}


	/**
	 * Search API
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function search(Request $request, Project $project, $branch_name){
		$rtn = new \stdClass();
		$rtn->result = true;

		$project_path = \get_project_workingtree_dir($project->project_code, $branch_name);
		$this->project_path = $project_path;

		$burdockProjectManager = new \tomk79\picklesFramework2\burdock\projectManager\main( env('BD_DATA_DIR') );
		$project_branch = $burdockProjectManager->project($project->project_code)->branch($branch_name, 'preview');
		$pageInfoAll = $project_branch->query(
			'/?PX=px2dthelper.get.all',
			array(
				'output' => 'json'
			)
		);
		$this->pageInfoAll = $pageInfoAll;

		$searchOptions = $this->decideTargets( $request );

		$searcher = new \tomk79\searchInDirectory\main(
			// 検索対象とするディレクトリを列挙する
			$searchOptions['target'],
			array(
				'progress' => function( $_done, $_total ) use ( &$total, &$done ){
					// 進行状況を受けるコールバック
					// var_dump($_done.'/'.$_total);
					$total = $_total;
					$done = $_done;
					$data = array();
					$data['total'] = $total;
					$data['done'] = $done;
					broadcast(new \App\Events\SearchEvent($data));
				},
				'match' => function( $file, $result ) use ( &$matched ){
					// 検索にマッチしたファイルの情報を受けるコールバック
					// var_dump('Matched! '.$file);
					array_push($matched, $file);
					$data = array();
					$data['new'] = array();
					array_push($data['new'], array(
						'path' => $file,
					));
					broadcast(new \App\Events\SearchEvent($data));
				},
				'unmatch' => function( $file, $result ) use ( &$unmatched ){
					// 検索にマッチしなかったファイルの情報を受けるコールバック
					// var_dump('Unmatched! '.$file);
					array_push($unmatched, $file);
				},
				'error' => function( $file, $error ){
					// 検索エラー情報を受けるコールバック
					// var_dump($file);
					// var_dump($error);
				},
			)
		);


		// 検索する
		$matched = array();
		$unmatched = array();
		$total = 0;
		$done = 0;

		$result = $searcher->start(
			$request->keyword, // 検索キーワード
			array(
				'filter' => $searchOptions['filter'] ,
				'ignore' => $searchOptions['ignore'] ,
				'allowRegExp' => $request->options['allowRegExp'], // true = 検索キーワード中に正規表現を使えるようにする
				'ignoreCase' => !$request->options['caseSensitive'], // true = 大文字・小文字を区別しない
				'matchFileName' => $request->options['matchFileName'], // true = ファイル名にもマッチさせる
			)
		);
		// var_dump($matched);
		// var_dump($done.'/'.$total);

		header('Content-type: application/json');
		return json_encode($rtn);
	}


	/**
	 */
	private function decideTargets( $request ){
		$project_path = $this->project_path;
		$pageInfoAll = $this->pageInfoAll;

		$rtn = array(
			'target' => array(),
			'filter' => array(),
			'ignore' => array(),
			'allowRegExp' => $request->options['allowRegExp'],
			'ignoreCase' => !$request->options['caseSensitive'],
			'matchFileName' => $request->options['matchFileName'],
		);

		$publicCacheDir = ($pageInfoAll->config->public_cache_dir ? $pageInfoAll->config->public_cache_dir : '/caches/');


		$targetDir = $request->target;
		switch($targetDir){
			case 'home_dir':
				array_push($rtn['target'], $this->fs->get_realpath($pageInfoAll->realpath_homedir));
				break;
			case 'contents_comment':
				array_push($rtn['target'], $this->fs->get_realpath($project_path));
				array_push($rtn['filter'], '/'.preg_quote('/comments.ignore/comment.', '/').'/' );
				break;
			case 'sitemaps':
				array_push($rtn['target'], $this->fs->get_realpath($pageInfoAll->realpath_homedir.'/sitemaps/'));
				break;
			case 'sys-caches':
				array_push($rtn['target'], $this->fs->get_realpath($project_path.'/'.$publicCacheDir)).push();
				array_push($rtn['target'], $this->fs->get_realpath($pageInfoAll->realpath_homedir.'/_sys/'));
				break;
			case 'packages':
				if($project_path){
					$rtn['target'].push($this->fs->get_realpath($project_path.'vendor/'));
					$rtn['target'].push($this->fs->get_realpath($project_path.'composer.json'));
					$rtn['target'].push($this->fs->get_realpath($project_path.'composer.lock'));
				}
				if($project_path){
					$rtn['target'].push($this->fs->get_realpath($project_path.'node_modules/'));
					$rtn['target'].push($this->fs->get_realpath($project_path.'package.json'));
				}
				break;
			case 'all':
			default:
				array_push( $rtn['target'], $this->fs->get_realpath($project_path) );
				break;
		}

		if( array_search('contents-comment', $request->options['ignore']) !== false ){
			array_push( $rtn['ignore'], '/'.preg_quote('/comments.ignore/comment.', '/').'/' );
		}

		$this->setIgnore( 'sitemap', $pageInfoAll->realpath_homedir.'sitemaps/', $request->options['ignore'], $rtn );
		$this->setIgnore( 'px-files', $pageInfoAll->realpath_homedir , $request->options['ignore'], $rtn );
		$this->setIgnore( 'sys-caches', $project_path.'/'.$publicCacheDir, $request->options['ignore'], $rtn );
		$this->setIgnore( 'sys-caches', $pageInfoAll->realpath_homedir.'_sys/', $request->options['ignore'], $rtn );

		if($project_path){
			$this->setIgnore( 'packages', $project_path.'vendor/', $request->options['ignore'], $rtn );
			$this->setIgnore( 'packages', $project_path.'composer.json', $request->options['ignore'], $rtn );
			$this->setIgnore( 'packages', $project_path.'composer.lock', $request->options['ignore'], $rtn );
		}
		if($project_path){
			$this->setIgnore( 'packages', $project_path.'node_modules/', $request->options['ignore'], $rtn );
			$this->setIgnore( 'packages', $project_path.'package.json', $request->options['ignore'], $rtn );
		}
		array_push($rtn['ignore'], '/\.git/');

		return $rtn;
	}


	/**
	 */
	private function setIgnore( $itemName, $realpath, $ignore, &$rtn ){
		if( !is_dir($realpath) && !is_file($realpath) ){
			return;
		}
		$path = $this->getPath( $this->fs->get_realpath($realpath) );
		$preg_pattern = '/^'.preg_quote( $path, '/' ).'/';
		if( array_search($itemName, $ignore) !== false ){
			array_push( $rtn['ignore'], $preg_pattern );
		}
		return;
	}

	/**
	 */
	private function getPath($realpath_file){
		$project_path = $this->fs->get_realpath($this->project_path.'/');
		$realpath_file = preg_replace( '/^'.preg_quote($project_path, '/').'/', '/', $realpath_file );
		return $realpath_file;
	}

}
