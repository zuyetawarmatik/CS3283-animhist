<?php

class VisualizationController extends \BaseController {
	
	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function showCreate($username)
	{
		if (Auth::user()->username == $username) {
			if (Input::get('step') == 2) {
				
			} else {
				if (Input::get('ajax')) {
					return ViewResponseUtility::makeSubView('create-visualization-step-1', 'New Visualization: <span style="font-weight:300">Step 1 (Start A New Visualization)</span>');
				} else {
					return ViewResponseUtility::makeBaseView(URL::route('visualization.showCreate', [$username]), Constant::SIDEBAR_MYVISUALIZATION);
				}	
			}
		} else {
			// When requesting full page - no ajax
			return Redirect::route('user.show', [$username]);
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
	public function show($id)
	{
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function showEdit($id)
	{
		
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		
	}
}