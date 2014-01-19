<?php

class UserController extends \BaseController {
	
	public function showLogin() {
		if (Input::get('ajax') == 1)
			return View::make('login', array('title'=>'Login', 'has_back'=>false, 'has_minimize_right'=>false));
		else
			return View::make('layouts.base', array('main_panel_iframe_url'=>URL::route('user.showLogin').'?ajax=1'));
	}
	
	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function showCreate()
	{
		if (Input::get('ajax') == 1)
			return View::make('register', array('title'=>'Register', 'has_back'=>false, 'has_minimize_right'=>false));
		else
			return View::make('layouts.base', array('main_panel_iframe_url'=>URL::route('user.showCreate').'?ajax=1'));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		//
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function showEdit($id)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		//
	}

}