<?php

use Illuminate\Contracts\Console\Kernel;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware(['boot'])
->group(function () {

	// --------------------------------------
	// スタートページ/ダッシュボード
	Route::get('/', 'StartpageController@startpage');


	Auth::routes(['verify' => true]);


	// --------------------------------------
	// プロフィール
	Route::get('mypage', 'MypageController@show');
	Route::get('mypage/edit', 'MypageController@edit');
	Route::put('mypage', 'MypageController@update');
	Route::get('mypage/edit_email', 'MypageController@edit_email');
	Route::post('mypage/edit_email', 'MypageController@update_email');
	Route::get('mypage/edit_email_mailsent', 'MypageController@update_email_mailsent');
	Route::get('mypage/edit_email_update', 'MypageController@update_email_update');
	Route::delete('mypage', 'MypageController@destroy');


	// --------------------------------------
	// スペース管理
	Route::get('space', 'Space\IndexController@index');
	Route::get('space/bd_data_dir', 'Space\BdDataDirController@index');
	Route::post('space/bd_data_dir/gpi', 'Space\BdDataDirController@remoteFinderGPI');
	Route::get('space/bd_data_dir/common-file-editor', 'Space\BdDataDirController@commonFileEditor');
	Route::post('space/bd_data_dir/common-file-editor/gpi', 'Space\BdDataDirController@commonFileEditorGPI');
	Route::get('space/members', 'Space\MembersController@index');
	Route::get('space/project', 'Space\ProjectController@index');
	Route::get('space/event-logs', 'Space\EventLogController@index');


	// --------------------------------------
	// システムメンテナンス画面 System Maintenance
	Route::middleware(['isDebugMode'])
	->group(function(){
		Route::get('system-maintenance', 'SystemMaintenance\IndexController@index');
		Route::get('system-maintenance/phpinfo', 'SystemMaintenance\IndexController@phpinfo');
		Route::get('system-maintenance/generate_vhosts', 'SystemMaintenance\GenerateVhostsController@index');
		Route::post('system-maintenance/generate_vhosts/ajax_generate_vhosts', 'SystemMaintenance\GenerateVhostsController@ajaxGenerateVhosts');
		Route::get('system-maintenance/ajax/checkCommand', 'SystemMaintenance\IndexController@ajaxCheckCommand');
		Route::get('system-maintenance/project-dirs', 'SystemMaintenance\ProjectDirsController@index');
		Route::get('system-maintenance/project-dirs/{project}', 'SystemMaintenance\ProjectDirsController@show');
		Route::get('system-maintenance/project-dirs/{project}/store', 'SystemMaintenance\ProjectDirsController@store');
		Route::get('system-maintenance/basicauth-default-htpasswd', 'SystemMaintenance\BasicauthDefaultHtpasswdController@index');
		Route::post('system-maintenance/basicauth-default-htpasswd/ajax', 'SystemMaintenance\BasicauthDefaultHtpasswdController@ajax');
		Route::get('system-maintenance/healthcheck', 'SystemMaintenance\HealthCheckController@index');
		Route::post('system-maintenance/healthcheck/ajax', 'SystemMaintenance\HealthCheckController@ajax');
	});


	Route::middleware(['project'])
	->group(function(){

		// --------------------------------------
		// プロジェクト Home
		Route::get('home/{project}/{branch_name?}', 'HomeController@index');
		Route::post('home/{project}/{branch_name}/setupAjax', 'HomeController@setupAjax');
		Route::post('home/{project}/{branch_name}/setupOptionAjax', 'HomeController@setupOptionAjax');


		// --------------------------------------
		// プロジェクト管理
		Route::post('projects', 'ProjectController@store');
		Route::get('projects/{project}/edit', 'ProjectController@edit');
		Route::put('projects/{project}/edit', 'ProjectController@update');
		Route::delete('projects/{project}', 'ProjectController@destroy');


		// --------------------------------------
		// サイトマップ
		Route::get('sitemaps/{project}/{branch_name}', 'SitemapController@index');
		Route::post('sitemaps/{project}/{branch_name}/uploadAjax', 'Ajax\SitemapController@uploadAjax');
		Route::post('sitemaps/{project}/{branch_name}/upload', 'SitemapController@upload');
		Route::post('sitemaps/{project}/{branch_name}/download', 'SitemapController@download');
		Route::post('sitemaps/{project}/{branch_name}/destroy', 'SitemapController@destroy');

		// --------------------------------------
		// テーマ
		Route::get('themes/{project}/{branch_name}', 'ThemeController@index');
		Route::post('themes/{project}/{branch_name}/ajax', 'ThemeController@ajax');
		Route::post('themes/{project}/{branch_name}/px2teGpi', 'ThemeController@px2teGpi');

		// --------------------------------------
		// コンテンツ
		Route::get('contents/{project}/{branch_name}', 'ContentController@index');
		Route::post('contents/{project}/{branch_name}/gpi', 'ContentController@gpi');
		Route::post('contents/{project}/{branch_name}/editAjax', 'Ajax\ContentController@editAjax');
		Route::post('contents/{project}/{branch_name}/searchAjax', 'Ajax\ContentController@searchAjax');

		// --------------------------------------
		// パブリッシュ
		Route::get('publish/{project}/{branch_name}', 'PublishController@index');
		Route::get('publish/{project}/{branch_name}/publish_run', 'PublishController@publish');
		Route::get('publish/{project}/{branch_name}/deleteApplock', 'PublishController@deleteApplock');
		Route::post('publish/{project}/{branch_name}/publishAjax', 'Ajax\PublishController@publishAjax');
		Route::post('publish/{project}/{branch_name}/readCsvAjax', 'Ajax\PublishController@readCsvAjax');
		Route::post('publish/{project}/{branch_name}/publishCancelAjax', 'Ajax\PublishController@publishCancelAjax');
		Route::post('publish/{project}/{branch_name}/publishSingleAjax', 'Ajax\PublishController@publishSingleAjax');
		Route::get('publish/{project}/{branch_name}/publishFileDownload', 'PublishController@publishFileDownload');
		Route::get('publish/{project}/{branch_name}/publishReportDownload', 'PublishController@publishReportDownload');

		// --------------------------------------
		// Custom Console Extensions
		Route::get('custom_console_extensions/{cce_id}/{project}/{branch_name}', 'CustomConsoleExtensionsController@index');
		Route::post('custom_console_extensions/{cce_id}/{project}/{branch_name}/ajax', 'CustomConsoleExtensionsController@ajax');
		Route::post('custom_console_extensions/{cce_id}/{project}/{branch_name}/gpi', 'CustomConsoleExtensionsController@gpi');

		// --------------------------------------
		// Git
		Route::get('git/{project}/{branch_name}', 'GitController@index');
		Route::post('git/{project}/{branch_name}/git', 'GitController@gitCommand');

		// --------------------------------------
		// Composer
		Route::get('composer/{project}/{branch_name}', 'ComposerController@index');
		Route::post('composer/{project}/{branch_name}/composer-install', 'ComposerController@install');
		Route::post('composer/{project}/{branch_name}/composer-update', 'ComposerController@update');

		// --------------------------------------
		// キャッシュを消去する
		Route::get('clearcache/{project}/{branch_name}', 'ClearCacheController@index');
		Route::post('clearcache/{project}/{branch_name}/clearcache', 'ClearCacheController@clearcache');

		// --------------------------------------
		// 検索
		Route::get('search/{project}/{branch_name}', 'SearchController@index');
		Route::post('search/{project}/{branch_name}/search', 'SearchController@search');

		// --------------------------------------
		// ステージング切り替え (Plum)
		Route::match(['get', 'post'], 'staging/{project}/{branch_name}', 'StagingController@index');
		Route::match(['get', 'post'], 'staging/{project}/{branch_name}/gpi', 'StagingController@gpi');

		// --------------------------------------
		// 配信 (Indigo)
		Route::match(['get', 'post'], 'delivery/{project}/{branch_name}', 'DeliveryController@index');
		Route::match(['get', 'post'], 'delivery/{project}/{branch_name}/indigoAjaxAPI', 'DeliveryController@indigoAjaxAPI');

		// --------------------------------------
		// ファイルとフォルダ (remote-finder)
		Route::get('files-and-folders/{project}/{branch_name}', 'FilesAndFoldersController@index');
		Route::get('files-and-folders/{project}/{branch_name}/api/parsePx2FilePath', 'FilesAndFoldersController@apiParsePx2FilePath');
		Route::post('files-and-folders/{project}/{branch_name}/gpi', 'FilesAndFoldersController@remoteFinderGPI');
		Route::get('files-and-folders/{project}/{branch_name}/common-file-editor', 'FilesAndFoldersController@commonFileEditor');
		Route::post('files-and-folders/{project}/{branch_name}/common-file-editor/gpi', 'FilesAndFoldersController@commonFileEditorGPI');

		// --------------------------------------
		// コンテンツエディタ
		Route::get('contentsEditor/{project}/{branch_name}', 'ContentsEditorController@index');
		Route::post('contentsEditor/{project}/{branch_name}/px2ceGpi', 'ContentsEditorController@px2ceGpi');
	});

	// --------------------------------------

});
