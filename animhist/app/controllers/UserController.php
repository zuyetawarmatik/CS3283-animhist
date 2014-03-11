<?php

class UserController extends \BaseController {
	
	public function showLogin() {
		if (Input::get('ajax'))
			return ViewResponseUtility::makeSubView('login', 'Login');
		else
			return ViewResponseUtility::makeBaseView(URL::route('user.showLogin'), Constant::SIDEBAR_GUEST_LOGIN);
	}
	
	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function showCreate()
	{
		if (Input::get('ajax')) {
			return ViewResponseUtility::makeSubView('register', 'Register');
		} else {
			return ViewResponseUtility::makeBaseView(URL::route('user.showCreate'), Constant::SIDEBAR_GUEST_LOGIN);
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
			return JSONResponseUtility::ValidationError($validator->getMessageBag()->toArray());
		
		$user = User::create(['avatar' => Input::file('avatar')]);
		$user->username = Input::get('username');
		$user->display_name = Input::get('display-name');
		$user->email = Input::get('email');
		$user->password = Hash::make(Input::get('password'));
		$user->description = Input::get('description');
		$user->save();
		
		Auth::login($user);
		
		/* All redirects in login, logout and register are whole-page, no ajax */
		if (Input::get('referer')) {
			return JSONResponseUtility::Redirect(URL::to(Input::get('referer')));
		} else {
			return JSONResponseUtility::Redirect(URL::route('user.show', [$user->username]));
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
				
				return ViewResponseUtility::makeSubView('show-visualizations-personal', $title, ['user'=>$user], true);
			} else {
				if (Auth::user() == $user)
					return ViewResponseUtility::makeBaseView(URL::route('user.show', ['username'=>$username]), Constant::SIDEBAR_MYVISUALIZATION);
				else {
					$highlight_id = Auth::check() ? Constant::SIDEBAR_USERVISUALIZATION : Constant::SIDEBAR_GUEST_USERVISUALIZATION;
					return ViewResponseUtility::makeBaseView(URL::route('user.show', ['username'=>$username]), $highlight_id, ['user'=>$user]);
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
	public function showEdit($username)
	{
		if (Auth::user()->username == $username) {
			if (Input::get('ajax')) {
				return ViewResponseUtility::makeSubView('settings', 'User Settings');
			} else {
				return ViewResponseUtility::makeBaseView(URL::route('user.showEdit', [$username]), Constant::SIDEBAR_SETTINGS);
			}
		} else
			return Redirect::route('user.show', [$username]);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $username
	 * @return Response
	 */
	public function update($username)
	{
		if (Auth::user()->username == $username) {
			$rules = ['display-name' => 'required'];
			$validator = Validator::make(Input::all(), $rules);
			if ($validator->fails())
				return JSONResponseUtility::ValidationError($validator->getMessageBag()->toArray());
			
			$user = Auth::user();

			$user->display_name = Input::get('display-name');
			$user->description = Input::get('description');
			
			$user->save();
		} else {
			return Response::make('', 401);
		}
	}

	public function updatePassword($username)
	{
		if (Auth::user()->username == $username) {
			$rules = ['oldpassword' => 'required', 
				  	  'newpassword' => 'required|min:5',
					  'retypepassword' => 'required|same:newpassword' ];
			$validator = Validator::make(Input::all(), $rules);
			if ($validator->fails())
				return JSONResponseUtility::ValidationError($validator->getMessageBag()->toArray());
				
			$user = Auth::user();
	
			$user->password = Input::get('newpassword');

			$user->save();
		} else {
			return Response::make('', 401);
		}
	}
	
	public function login()
	{
		$input = Input::all();
		$rules = array('username' => 'required', 'password' => 'required');
		$validator = Validator::make($input, $rules);
		if ($validator->fails())
			return JSONResponseUtility::ValidationError($validator->getMessageBag()->toArray());
		
		$user = User::where('email', $input['username'])->orWhere('username', $input['username'])->first();
		if (!$user) {
			$attempt = false;
		} else {
			$attempt = Auth::attempt(array('username' => $user->username, 'password' => $input['password']), true);
		}
		
		/* All redirects in login, logout and register are whole-page, no ajax */
		if ($attempt) {
			$url;
			if (Input::get('referer')) {
				$url = URL::to($input['referer']);
			} else {
				$url = URL::route('user.show', [$user->username]);
			}
			return JSONResponseUtility::Redirect($url);
		} else {
			return JSONResponseUtility::ValidationError(['user'=>['User does not exist or wrong password.']]);
		}
	}
	
	public function followUser($username)
	{
		$following_user = DB::table('users')->where('username', $username)->first();
		if (!$following_user) return JSONResponseUtility::ValidationError(['user'=>['User to follow does not exist.']]);
		
		$following_id = $following_user->id;
		if (Auth::user()->id == $following_id) return JSONResponseUtility::ValidationError(['user'=>['User and followed user are the same.']]);
		
		$existing_follow = DB::table('follows')->where('user_id', Auth::user()->id)->where('following_id', $following_id)->first();
		if ($existing_follow) return JSONResponseUtility::ValidationError(['follow'=>['Already following this person.']]);
		
		$follow = new Follow();
		$follow->user_id = Auth::user()->id;
		$follow->following_id = $following_id;
		$follow->save();
		
		return Response::json(['followers'=>count(User::find($following_id)->followers)]);
	}
	
	public function unfollowUser($username)
	{
		$following_user = DB::table('users')->where('username', $username)->first();
		if (!$following_user) return JSONResponseUtility::ValidationError(['user'=>['User to unfollow does not exist.']]);
		
		$following_id = $following_user->id;
		if (Auth::user()->id == $following_id) return JSONResponseUtility::ValidationError(['user'=>['User and followed user are the same.']]);
		
		$existing_follow = DB::table('follows')->where('user_id', Auth::user()->id)->where('following_id', $following_id)->first();
		if (!$existing_follow)
			return JSONResponseUtility::ValidationError(['unfollow'=>['You haven\'t followed this user yet to unfollow.']]);
		
		Follow::destroy($existing_follow->id);
		
		return Response::json(['followers'=>count(User::find($following_id)->followers)]);
	}
	
	public function logout()
	{
		/* All redirects in login, logout and register are whole-page, no ajax */
		Session::flush();
		Auth::logout();
		return JSONResponseUtility::Redirect(Input::get('referer'));
	}
	
}