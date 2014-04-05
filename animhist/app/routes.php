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
	//$gft = new GoogleFusionTable('1yy9FoPRlCVk5ncS5rfbekDNnyUYpemOPcv5CF0Wj', 'polygon');
	//return json_encode($gft->retrieveGFusionData());
});


// ==========================================================

// /search
Route::get('search', ['as' => 'visualization.showSearch', 'uses' => 'VisualizationController@showSearch']);
Route::post('search', ['as' => 'visualization.search', 'uses' => 'VisualizationController@search']);

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

Route::group(['before' => 'auth.truncateURLToUsername', 'prefix' => '{username}'], function(){
	// /{username}/visualization/create
	Route::get('visualization/create', ['as'=> 'visualization.showCreate', 'uses' => 'VisualizationController@showCreate']);

	// /{username}/visualization/{id}/edit
	Route::get('visualization/{id}/edit', ['as'=> 'visualization.showEdit', 'uses' => 'VisualizationController@showEdit']);
	
	// /{username}/settings
	Route::get('settings', ['as' => 'user.showEdit', 'uses' => 'UserController@showEdit']);
});

Route::group(['before' => 'auth', 'prefix' => '{username}'], function(){
	// /{username}
	Route::put('/', ['before' => 'csrf', 'as' => 'user.update', 'uses' => 'UserController@update']);
	
	// /{username}/updatepassword
	Route::post('/updatepassword', ['before' => 'csrf', 'as' => 'user.updatePassword', 'uses' => 'UserController@updatePassword']);
	
	// /{username}/follow
	Route::post('follow', ['before' => 'csrf', 'as' => 'user.follow', 'uses' => 'UserController@followUser']);
	
	// /{username}/unfollow
	Route::post('unfollow', ['before' => 'csrf', 'as' => 'user.unfollow', 'uses' => 'UserController@unfollowUser']);
	
	// /{username}/visualization/create
	Route::post('visualization/create', ['before' => 'csrf', 'as'=> 'visualization.store', 'uses' => 'VisualizationController@store']);
	
	// /{username}/visualization/{id}
	Route::delete('visualization/{id}', ['before' => 'csrf', 'as'=> 'visualization.destroy', 'uses' => 'VisualizationController@destroy']);
	
	// /{username}/visualization/{id}/updatetable
	Route::post('visualization/{id}/updatetable', ['before' => 'csrf', 'as'=> 'visualization.updateTable', 'uses' => 'VisualizationController@updateTable']);
	
	// /{username}/visualization/{id}/updateproperty
	Route::post('visualization/{id}/updateproperty', ['before' => 'csrf', 'as'=> 'visualization.updateProperty', 'uses' => 'VisualizationController@updateProperty']);
	
	// /{username}/visualization/{id}/updatestyle
	Route::post('visualization/{id}/updatestyle', ['before' => 'csrf', 'as'=> 'visualization.updateStyle', 'uses' => 'VisualizationController@updateStyle']);
	
	// /{username}/visualization/{id}/comment
	Route::post('visualization/{id}/comment', ['before' => 'csrf', 'as'=> 'visualization.comment', 'uses' => 'VisualizationController@comment']);
	
});

Route::group(['prefix' => '{username}'], function(){
	// /{username}
	Route::get('/', ['as' => 'user.show', 'uses' => 'UserController@show']);

	// /{username}/visualization/{id}
	Route::get('visualization/{id}', ['as'=> 'visualization.show', 'uses' => 'VisualizationController@show']);

	// /{username}/visualization/{id}/info
	Route::get('visualization/{id}/info', ['as'=> 'visualization.info', 'uses' => 'VisualizationController@info']);
});