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

Auth::routes(['verify' => true]);

// --------------------------------------
// ダッシュボード
Route::get('/', 'DashboardController@index');

// Route::get('users', 'UserController@index');
// Route::get('users/create', 'UserController@create');
// Route::post('users', 'UserController@store');
// Route::get('users/{user}', 'UserController@show');
// Route::get('users/{user}/edit', 'UserController@edit');
// Route::put('users/{user}', 'UserController@update');
// Route::delete('users/{user}', 'UserController@destroy');

// Route::resource('users', 'UserController');

// --------------------------------------
// プロフィール
Route::get('mypage', 'MypageController@show');
Route::get('mypage/edit', 'MypageController@edit');
Route::put('mypage', 'MypageController@update');
Route::delete('mypage', 'MypageController@destroy');


// --------------------------------------
// システムメンテナンス画面 System Maintenance
Route::get('system-maintenance', 'SystemMaintenanceController@index');


// --------------------------------------
// プロジェクト Home
Route::get('home/{project}/{branch_name}', 'HomeController@index');

// --------------------------------------
// プロジェクト管理
// Route::get('projects', 'ProjectController@index'); //プロジェクト一覧は封印
Route::get('projects/create', 'ProjectController@create');
Route::post('projects', 'ProjectController@store');
Route::get('projects/{project}/{branch_name}', 'ProjectController@show');
Route::get('projects/{project}/{branch_name}/edit', 'ProjectController@edit');
Route::put('projects/{project}/{branch_name}', 'ProjectController@update');
Route::delete('projects/{project}/{branch_name}', 'ProjectController@destroy');

// Route::resource('projects', 'ProjectController');

// --------------------------------------
// セットアップ
Route::get('setup/{project}/{branch_name}', 'SetupController@index');
Route::post('setup/{project}/{branch_name}/setupAjax', 'Ajax\SetupController@setupAjax');
Route::post('setup/{project}/{branch_name}/setupOptionAjax', 'Ajax\SetupController@setupOptionAjax');

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

// --------------------------------------
// コンテンツ
Route::get('contents/{project}/{branch_name}', 'ContentController@index');
Route::post('contents/{project}/{branch_name}/ajax', 'ContentController@ajax');
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
// Git
Route::get('git/{project}/{branch_name}', 'GitController@index');
Route::post('git/{project}/{branch_name}/git', 'GitController@gitCommand');

// --------------------------------------
// Composer
Route::get('composer/{project}/{branch_name}', 'ComposerController@index');
Route::post('composer/{project}/{branch_name}/composer-install', 'ComposerController@install');
Route::post('composer/{project}/{branch_name}/composer-update', 'ComposerController@update');

// --------------------------------------
// ステージング切り替え (Plum)
Route::match(['get', 'post'], 'staging/{project}/{branch_name}', 'StagingController@index');

// --------------------------------------
// 配信 (Indigo)
Route::match(['get', 'post'], 'delivery/{project}/{branch_name}', 'DeliveryController@index');
Route::match(['get', 'post'], 'delivery/{project}/{branch_name}/indigoAjaxAPI', 'DeliveryController@indigoAjaxAPI');

// --------------------------------------
// ファイルとフォルダ (remote-finder)
Route::get('files-and-folders/{project}/{branch_name}', 'FilesAndFoldersController@index');
Route::post('files-and-folders/{project}/{branch_name}/gpi', 'FilesAndFoldersController@remoteFinderGPI');
Route::get('files-and-folders/{project}/{branch_name}/common-file-editor', 'FilesAndFoldersController@commonFileEditor');
Route::post('files-and-folders/{project}/{branch_name}/common-file-editor/gpi', 'FilesAndFoldersController@commonFileEditorGPI');

// --------------------------------------
// コンテンツエディタ
Route::get('contentsEditor/{project}/{branch_name}', 'ContentsEditorController@index');
Route::post('contentsEditor/{project}/{branch_name}/px2ceGpi', 'ContentsEditorController@px2ceGpi');

// --------------------------------------
