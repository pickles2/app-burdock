<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Project;
use App\Http\Requests\StoreSitemap;

class SearchController extends Controller
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

		$project_path = get_project_workingtree_dir($project->project_code, $branch_name);

		$searcher = new \tomk79\searchInDirectory\main(
			array(
				// 検索対象とするディレクトリを列挙する
				$project_path,
			),
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

		$filter = array();
		$ignore = array();
		array_push($ignore, '/\.git/');

		// 検索する
		$matched = array();
		$unmatched = array();
		$total = 0;
		$done = 0;
		$result = $searcher->start(
			$request->keyword, // 検索キーワード
			array(
				'filter' => $filter ,
				'ignore' => $ignore ,
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

}
