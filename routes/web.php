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
Route::get('/', 'HomeController@index');

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
Route::get('profile', 'ProfileController@show');
Route::get('profile/edit', 'ProfileController@edit');
Route::put('profile', 'ProfileController@update');
Route::delete('profile', 'ProfileController@destroy');

// --------------------------------------
// プロジェクト Home
// Route::get('projects', 'ProjectController@index'); //プロジェクト一覧は封印
Route::get('projects/create', 'ProjectController@create');
Route::post('projects', 'ProjectController@store');
Route::get('projects/{project}/{branch_name}', 'ProjectController@show');
Route::get('projects/{project}/{branch_name}/edit', 'ProjectController@edit');
Route::put('projects/{project}/{branch_name}', 'ProjectController@update');
Route::delete('projects/{project}/{branch_name}', 'ProjectController@destroy');

// Route::resource('projects', 'ProjectController');

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
Route::get('pages/{project}/{branch_name}/index.html', 'PageController@index');
Route::post('pages/{project}/{branch_name}/ajax', 'PageController@ajax');
Route::get('pages/{project}/{branch_name}', 'PageController@show');
Route::post('pages/{project}/{branch_name}', 'PageController@gpi');
Route::post('pages/{project}/{branch_name}/editAjax', 'Ajax\PageController@editAjax');
Route::post('pages/{project}/{branch_name}/searchAjax', 'Ajax\PageController@searchAjax');

// --------------------------------------
// パブリッシュ
Route::get('publish/{project}/{branch_name}', 'PublishController@index');
Route::get('publish/{project}/{branch_name}/publish_run', 'PublishController@publish');

// --------------------------------------
// ステージング切り替え (Plum)
Route::get('staging/{project}/{branch_name}', 'StagingController@index');

// --------------------------------------
// 配信 (Indigo)
Route::get('delivery/{project}/{branch_name}', 'DeliveryController@index');
Route::get('delivery/{project}/{branch_name}/indigoAjaxAPI', 'DeliveryController@indigoAjaxAPI');

// --------------------------------------
// ファイルとフォルダ (remote-finder)
Route::get('files-and-folders/{project}/{branch_name}', 'FilesAndFoldersController@index');
Route::post('files-and-folders/{project}/{branch_name}/gpi', 'FilesAndFoldersController@remoteFinderGPI');
