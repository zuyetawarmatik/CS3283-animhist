<?php

class UserController extends \BaseController {
	
	public function showLogin() {
		if (Input::get('ajax'))
			return View::make('login', array('title'=>'Login', 'has_back'=>Input::get('back'), 'has_minimize_right'=>false));
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
		if (Input::get('ajax')) {
			return View::make('register', array('title'=>'Register', 'has_back'=>Input::get('back'), 'has_minimize_right'=>false));
		} else
			return View::make('layouts.base', array('main_panel_iframe_url'=>URL::route('user.showCreate').'?ajax=1'));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		$rules = array(
				'username'			=> 'required|unique:users|alpha_dash',
				'display-name'		=> 'required',
				'email'      		=> 'required|unique:users|email',
				'password'     		=> 'required|min:5',
				'password-retype' 	=> 'required|same:password'
		);
		$validator = Validator::make(Input::all(), $rules);
		if ($validator->fails())
			return Response::json($validator->getMessageBag()->toArray(), 400);
		
		$user = User::create(['avatar' => Input::file('avatar')]);
		$user->username = Input::get('username');
		$user->display_name = Input::get('display-name');
		$user->email = Input::get('email');
		$user->password = Hash::make(Input::get('password'));
		$user->description = Input::get('description');
		$user->save();
		
		// Redirect
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