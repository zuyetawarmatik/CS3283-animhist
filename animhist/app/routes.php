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
	return View::make('layouts.base', array('username'=>'Richard Francesc'))->nest('main_panel', 'play', array('title'=>'USA Population in 20th Century <span class="h6">by</span> <span class="h2">Richard Koe</span>', 'like_info'=>'56 Likes'));
});