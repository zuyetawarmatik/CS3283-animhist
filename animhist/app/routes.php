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

Route::get('/', function()
{
	return View::make('layouts.base', array('username'=>'Richard Francesc', 'loggedin'=>false, 'main_panel_iframe_url'=>URL::to('login')));
});

Route::get('play', function()
{
	return View::make('view-visualization', array('title'=>'California Electricity Consumption <span class="h6">by</span> <a href="#">Richard Koe</a>', 'like_info'=>'56 Likes', 'has_back'=>true));
});

Route::get('featured', function()
{
	return View::make('show-visualizations-featured', array('title'=>'Featured Visualizations', 'has_back'=>false, 'has_minimize_right'=>true));
});

Route::get('search', function()
{
	return View::make('show-visualizations-search', array('title'=>'Search', 'has_back'=>false, 'has_minimize_right'=>true));
});

Route::get('settings', function()
{
	return View::make('settings', array('title'=>'Settings', 'has_back'=>false, 'has_minimize_right'=>false));
});

Route::get('login', function()
{
	return View::make('login', array('title'=>'Login', 'has_back'=>false, 'has_minimize_right'=>false));
});
