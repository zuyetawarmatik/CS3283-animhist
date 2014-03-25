@extends('layouts.main4sections')

@section('css')
	<link rel="stylesheet/less" type="text/css" href="/css/view-visualization.less.css" />
@stop

@section('js')
	<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false"></script>
	{{ HTML::script('js/attrchange/attrchange.js'); }}
	{{ HTML::script('js/project/page-view-visualization.js'); }}
@stop

@section('left-area-main')
	<div id="visualization-area" data-user-id="{{ $user->username }}" data-vi-id="{{ $visualization->id }}">
		<div id="map">
		</div>
		<div id="seekbar">	
			<button id="play-btn" title="Play" data-is-playing="false"><i>&#57610;</i></button>
			<ul id="timeline-list"></ul>
		</div>
	</div>
	@if (true)
		<div id="comment-area">
			<div id="comment-area-title">
				12 comments
			</div>
			<ul id="comment-list">
				<li class="comment-item">
					<div class="avatar-wrapper">
						<a href="#"><img class="avatar" src="images/cavatar1.jpg" width="80" height="80" /></a>
					</div>
					<div class="comment-main">
						<p class="comment-info"><a href="#" class="username">Richard Tan</a> - <span class="time">3:30, 12 Dec 2013</span></p>
						<p class="comment-content">I must say this is the most awesome I’ve ever seen! I must say this is the most awesome I’ve ever seen!</p>
					</div>
				</li>
				<li class="comment-item">
					<div class="avatar-wrapper">
						<a href="#"><img class="avatar" src="images/cavatar2.jpg" width="80" height="80" /></a>
					</div>
					<div class="comment-main">
						<p class="comment-info"><a href="#" class="username">Mr Dhane</a> - <span class="time">3:30, 12 Dec 2013</span></p>
						<p class="comment-content">Aenean sollicitudin, lorem quis bibendum auctor, nisi elit consequat ipsum, nec sagittis sem nibh id elit. Duis sed odio sit amet.</p>
					</div>
				</li>
				<li class="comment-item">
					<div class="avatar-wrapper">
						<a href="#"><img class="avatar" src="images/cavatar3.jpg" width="80" height="80" /></a>
					</div>
					<div class="comment-main">
						<p class="comment-info"><a href="#" class="username">Walla Jones</a> - <span class="time">3:30, 12 Dec 2013</span></p>
						<p class="comment-content">Obviously better than textbook :)</p>
					</div>
				</li>
			</ul>
		</div>
	@endif
@stop

@section('action-bar')
	@if (true)
		<div id="like-btn">&#57556;</div>
	@endif
	<div id="like-info">
		
	</div>
@stop

@section('right-area-main')
	@if (true)
		{{ Form::open(array('name'=>'comment-form', 'url'=>'')) }}
			{{ Form::textarea('comment-box', 'What are you having in mind?') }}
			{{ Form::submit('Post', array('name'=>'submit-btn')) }}
		{{ Form::close() }}
	@endif
	<article id="description-area">
		<h1>{{ $visualization->display_name }}</h1>
		<p><br><span class="h2">Author: </span>{{ $visualization->user->display_name }}</p>
		<p><span class="h2">Created at: </span>{{ $visualization->getFormattedCreatedDate() }}</p>
		<p><span class="h2">Last Updated at: </span>{{ $visualization->getFormattedUpdatedDate() }}</p>
		<p><br><span class="h2">Type: </span>{{ ucfirst($visualization->type) }}</p>
		<p id="zoom"><span class="h2">Zoom: </span>{{ number_format($visualization->zoom) }}</p>
		<p id="center"><span class="h2">Center: </span>{{ number_format($visualization->center_latitude, 2) }}, {{ number_format($visualization->center_longitude, 2) }}</p>
		<p><span class="h2">Category: </span>{{ $visualization->category }}</p>
		<p><br><span class="h2">Brief Description:</span></p>
		<p>
			@if ($visualization->description)
				{{ $visualization->description }}
			@else
				(The visualization does not have any description yet.)
			@endif
		</p>
	</article>
	<div id="button-area">
	{{ Form::open(array('name'=>'hidden-form', 'url'=>'#')) }}
	{{ Form::close() }}
	@if (!$user->isAuthUser())
		@if (Auth::check() && DB::table('follows')->where('user_id', Auth::user()->id)->where('following_id', $user->id)->first())
		<button id="follow-btn" data-url="{{ URL::route('user.unfollow', $user->username) }}"><i>&#57551;</i>Unfollow The Author</button>
		@else
		<button id="follow-btn" data-url="{{ URL::route('user.follow', $user->username) }}"><i>&#57552;</i>Follow The Author</button>
		@endif
	@else
		<button id="edit-visualization-btn" data-url="{{}}"><i>&#57350;</i>Edit This Visualization</button>
	@endif
	</div>
@stop