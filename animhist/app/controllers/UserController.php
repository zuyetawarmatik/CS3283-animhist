<?php

class UserController extends \BaseController {
	
	public function showLogin() {
		if (Input::get('ajax'))
			return View::make('login', array('title'=>'Login', 'has_back'=>Input::get('back'), 'has_minimize_right'=>false));
		else
			return View::make('layouts.base', array('main_panel_iframe_url'=>URL::route('user.showLogin').'?ajax=1', 'highlight_id'=>1));
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
		} else {
			return View::make('layouts.base', array('main_panel_iframe_url'=>URL::route('user.showCreate').'?ajax=1', 'highlight_id'=>1));
		}
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		$rules = array(
				'username'			=> 'required|unique:users|alpha_dash|not_in:user,featured,search,visualization',
				'display-name'		=> 'required',
				'email'      		=> 'required|unique:users|email',
				'password'     		=> 'required|min:5',
				'password-retype' 	=> 'required|same:password'
		);
		$validator = Validator::make(Input::all(), $rules);
		if ($validator->fails())
			return Response::json(['error'=>$validator->getMessageBag()->toArray()], 400);
		
		$user = User::create(['avatar' => Input::file('avatar')]);
		$user->username = Input::get('username');
		$user->display_name = Input::get('display-name');
		$user->email = Input::get('email');
		$user->password = Hash::make(Input::get('password'));
		$user->description = Input::get('description');
		$user->save();
		
		Auth::login($user);
		
		/* All redirects in login and register are whole-page, no ajax */
		if (Input::get('referer')) {
			return Response::json(['redirect'=>URL::to(Input::get('referer'))]);
		} else {
			return Response::json(['redirect'=>URL::route('user.show', [$user->username])]);
		}
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  $username
	 * @return Response
	 */
	public function show($username)
	{
		$user = User::where('username', '=', $username)->first();
		if ($user) {
			if (Input::get('ajax')) {
				$title = '';
				if (Auth::user() == $user) $title = 'My Visualizations';
				else $title = $user->display_name.'&#39;s Visualizations';
					
				return View::make('show-visualizations-personal', array('title'=>$title, 'has_back'=>Input::get('back'), 'has_minimize_right'=>true, 'user'=>$user));
			} else {
				if (Auth::user() == $user)
					return View::make('layouts.base', array('main_panel_iframe_url'=>URL::route('user.show', ['username'=>$username]).'?ajax=1', 'highlight_id'=>1));
				else {
					$highlight_id = Auth::check() ? 6 : 4;
					return View::make('layouts.base', array('main_panel_iframe_url'=>URL::route('user.show', ['username'=>$username]).'?ajax=1', 'highlight_id'=>$highlight_id, 'user'=>$user));
				}
			}
		} else {
			App::abort(404);
		}
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

	public function login()
	{
		$input = Input::all();
		$rules = array('username' => 'required', 'password' => 'required');
		$validator = Validator::make($input, $rules);
		if ($validator->fails())
			return Response::json(['error'=>$validator->getMessageBag()->toArray()], 400);
		
		$user = User::where('email', $input['username'])->orWhere('username', $input['username'])->first();
		if (!$user) {
			$attempt = false;
		} else {
			$attempt = Auth::attempt(array('username' => $user->username, 'password' => $input['password']), true);
		}
		
		/* All redirects in login and register are whole-page, no ajax */
		if ($attempt) {
			if (Input::get('referer')) {
				return Response::json(['redirect'=>URL::to($input['referer'])]);
			} else {
				return Response::json(['redirect'=>URL::route('user.show', [$user->username])]);
			}
		} else {
			return Response::json(['error'=>['user'=>['User does not exist or wrong password.']]], 400);
		}
	}
	
	public function logout()
	{
		Session::flush();
		Auth::logout();
		return Response::json(['redirect'=>URL::route('user.showLogin')]); // Should check referer
	}
}