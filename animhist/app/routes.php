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
	$gft = new GoogleFusionTable('1bvHTXTlPgCNfmuK_rXnWqWSM5g8TpcS5ZE1LTRI');
	//return $gft->createColumnDefaultStyle('point', 'New Valuable');
	return Response::JSON($gft->getColumnStyle('point', 'New Valuable'));
	//return Response::JSON($gft->retrieveGFusionStyles());
});

Route::get('play', function()
{
	return View::make('view-visualization', array('title'=>'California Electricity Consumption <span class="h6">by</span> <a href="#">Richard Koe</a>', 'like_info'=>'56 Likes', 'has_back'=>true));
});

Route::get('search', function()
{
	return View::make('show-visualizations-search', array('title'=>'Search', 'has_back'=>false, 'has_minimize_right'=>true));
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
	
	// /{username}/settings
	Route::get('settings', ['as' => 'user.showEdit', 'uses' => 'UserController@showEdit']);
});

Route::group(['before' => 'auth', 'prefix' => '{username}'], function(){
	// /{username}
	Route::put('/', ['before' => 'csrf', 'as' => 'user.update', 'uses' => 'UserController@update']);
	
	// /{username}/follow
	Route::post('follow', ['before' => 'csrf', 'as' => 'user.follow', 'uses' => 'UserController@followUser']);
	
	// /{username}/unfollow
	Route::post('unfollow', ['before' => 'csrf', 'as' => 'user.unfollow', 'uses' => 'UserController@unfollowUser']);
	
	// /{username}/visualization/create
	Route::post('visualization/create', ['before' => 'csrf', 'as'=> 'visualization.store', 'uses' => 'VisualizationController@store']);
	
	// /{username}/visualization/{id}/info
	Route::get('visualization/{id}/info', ['before' => 'csrf', 'as'=> 'visualization.info', 'uses' => 'VisualizationController@info']);
	
	// /{username}/visualization/{id}/updatetable
	Route::post('visualization/{id}/updatetable', ['before' => 'csrf', 'as'=> 'visualization.updateTable', 'uses' => 'VisualizationController@updateTable']);
	
});