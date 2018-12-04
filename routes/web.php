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

Route::get('/', 'HomeController@index');

// Route::get('users', 'UserController@index');
// Route::get('users/create', 'UserController@create');
// Route::post('users', 'UserController@store');
// Route::get('users/{user}', 'UserController@show');
// Route::get('users/{user}/edit', 'UserController@edit');
// Route::put('users/{user}', 'UserController@update');
// Route::delete('users/{user}', 'UserController@destroy');

// Route::resource('users', 'UserController');

Route::get('profile', 'ProfileController@show');
Route::get('profile/edit', 'ProfileController@edit');
Route::put('profile', 'ProfileController@update');
Route::delete('profile', 'ProfileController@destroy');

// Route::get('projects', 'ProjectController@index'); //プロジェクト一覧は封印
Route::get('projects/create', 'ProjectController@create');
Route::post('projects', 'ProjectController@store');
Route::get('projects/{project}/{branch_name}', 'ProjectController@show');
Route::get('projects/{project}/{branch_name}/edit', 'ProjectController@edit');
Route::put('projects/{project}/{branch_name}', 'ProjectController@update');
Route::delete('projects/{project}/{branch_name}', 'ProjectController@destroy');

// Route::resource('projects', 'ProjectController');

Route::post('upload/{project}/{branch_name}', 'SitemapController@upload');
Route::post('download/{project}/{branch_name}', 'SitemapController@download');
Route::post('publish/{project}/{branch_name}', 'PublishController@publish');

Route::get('pages/{project}/{branch_name}/index.html', 'PageController@index');
Route::get('pages/{project}/{branch_name}', 'PageController@show');
Route::post('pages/{project}/{branch_name}', 'PageController@gpi');
