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
	return View::make('layouts.base', array('username'=>'Richard Francesc', 'main_panel_iframe_url'=>URL::to('play')));
	//return View::make('layouts.base', array('username'=>'Richard Francesc', 'main_panel_iframe_url'=>URL::to('featured')));
});

Route::get('play', function()
{
	return View::make('view-visualization', array('title'=>'California Electricity Consumption <span class="h6">by</span> <a href="#">Richard Koe</a>', 'like_info'=>'56 Likes', 'has_back'=>true));
});

Route::get('featured', function()
{
	return View::make('show-visualizations-featured', array('title'=>'Featured', 'has_back'=>false));
});