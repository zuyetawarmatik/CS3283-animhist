@extends('show-visualizations')

@section('css')
	<link rel="stylesheet/less" type="text/css" href="/css/show-visualizations-personal.less.css" />
@stop

@section('js')
	{{ HTML::script('js/project/page-show-visualizations-personal.js'); }}
@stop

@section('top-bar')
	@parent
	@if (Auth::user() == $user)
		<button id="create-visualization-btn" class="blue-btn" data-url="{{URL::route('visualization.showCreate', $user->username)}}"><i>&#57380;</i>Create New Visualization</button>
	@endif
@stop

@section('right-area')
	@parent
	<ul id="right-area-tab">
		<li id="right-area-tab-info" class="selected">&#57379;</li>
		<li id="right-area-tab-category">&#57525;</li>
	</ul>
	<article id="description-area">
		<h1>{{ $user->display_name.'&#39;s Profile' }}</h1>
		<p style="font-style:italic">0 people following</p>
		@if (Auth::user() != $user)	<img style="float:left; margin-top:25px; margin-right:10px; background:#666" width="60" height="60" src="{{ $user->avatar->url('thumb') }}" /> @endif
		<p><br><span class="h2">Username: </span>{{ '@'.$user->username }}</p>
		<p><span class="h2">Joined from: </span>{{ $user->getFormattedCreatedDate() }}</p>
		<p><span class="h2">Visualizations: </span></p>
		<p><br><span class="h2">Brief Description:</span></p>
		<p>
			@if ($user->description)
				{{ $user->description }}
			@else
				@if (Auth::user() == $user)
					(You do 
				@else
					(The user does 
				@endif
				not have any description yet.)
			@endif
		</p>
	</article>
	<div id="button-area">
	@if (Auth::user() != $user)
		@if (DB::table('follows')->where('user_id', Auth::user()->id)->where('following_id', $user->id)->first())
		<button id="follow-btn" data-url="{{ URL::route('user.unfollow', $user->username) }}"><i>&#57552;</i>Unfollow The Author</button>
		@else
		<button id="follow-btn" data-url="{{ URL::route('user.follow', $user->username) }}"><i>&#57552;</i>Follow The Author</button>
		@endif
	@else
		<button id="edit-profile-btn"><i>&#57350;</i>Edit My Profile</button>
	@endif
	</div>
@stop