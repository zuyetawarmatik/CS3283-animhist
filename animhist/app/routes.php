<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

// Testing Routes

Route::get('/', function()
{
});

Route::get('play', function()
{
	return View::make('view-visualization', array('title'=>'California Electricity Consumption <span class="h6">by</span> <a href="#">Richard Koe</a>', 'like_info'=>'56 Likes', 'has_back'=>true));
});

Route::get('search', function()
{
	return View::make('show-visualizations-search', array('title'=>'Search', 'has_back'=>false, 'has_minimize_right'=>true));
});

Route::get('settings', function()
{
	return View::make('settings', array('title'=>'Settings', 'has_back'=>false, 'has_minimize_right'=>false));
});

// ==========================================================

Route::group(['before' => 'guest', 'prefix' => '/'], function(){
	// /login
	Route::get('login', function(){return Redirect::route('user.showLogin');});
	
	// /user
	Route::get('user', function(){return Redirect::route('user.showLogin');});
	Route::post('user', ['as' => 'user.store', 'uses' => 'UserController@store']);
});

Route::group(['before' => 'guest', 'prefix' => 'user'], function(){	
	// /user/create
	Route::get('create', ['as' => 'user.showCreate', 'uses' => 'UserController@showCreate']);
	
	// /user/login
	Route::get('login', ['as' => 'user.showLogin', 'uses' => 'UserController@showLogin']);
	Route::post('login', ['before' => 'csrf', 'as' => 'user.login', 'uses' => 'UserController@login']);
});

Route::group(['before' => 'auth', 'prefix' => 'user'], function(){
	// /user/logout
	Route::post('logout', ['as' => 'user.logout', 'uses' => 'UserController@logout']);
});

// /{username}
Route::group(['prefix' => '{username}'], function(){
	Route::get('/', ['as' => 'user.show', 'uses' => 'UserController@show'])->where('username', '[a-zA-Z0-9-_]+');
});

Route::group(['before' => 'auth.truncateURLToUsername', 'prefix' => '{username}'], function(){
	// /{username}/visualization/create
	Route::get('visualization/create', ['as'=> 'visualization.showCreate', 'uses' => 'VisualizationController@showCreate']);
	Route::post('visualization/create', ['as'=> 'visualization.store', 'uses' => 'VisualizationController@store']);
});
