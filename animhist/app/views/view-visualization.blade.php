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
	<div id="visualization-area" data-user-id="{{ $visualization->user->username }}" data-vi-id="{{ $visualization->id }}">
		<div id="map">
		</div>
		<div id="seekbar">	
			<button id="play-btn" title="Play" data-is-playing="false"><i>&#57610;</i></button>
			<ul id="timeline-list"></ul>
		</div>
	</div>
	<div id="comment-area">
		<div id="comment-area-title">
			{{ count($visualization->comments) }} comment(s)
		</div>
		<ul id="comment-list">
		@foreach ($visualization->comments as $comment)
			<li class="comment-item">
				<div class="avatar-wrapper">
					<a href="{{ URL::route('user.show', $comment->user->username) }}"><img class="avatar" src="{{ $comment->user->avatar->url('thumb') }}" /></a>
				</div>
				<div class="comment-main">
					<p class="comment-info"><a href="{{ URL::route('user.show', $comment->user->username) }}" class="username">{{$comment->user->display_name}}</a> - <span class="time">{{$comment->getFormattedCreatedDate()}}</span></p>
					<p class="comment-content">{{ $comment->content }}</p>
				</div>
			</li>
		@endforeach
		</ul>
	</div>
@stop

@section('action-bar')
	@if (Auth::check() && Like::where('visualization_id', $visualization->id)->where('user_id', Auth::user()->id)->first())
	<div id="like-btn" data-url="{{ URL::route('visualization.unlike', [$visualization->user->username, $visualization->id]) }}">&#57557;</div>
	@else
	<div id="like-btn" data-url="{{ URL::route('visualization.like', [$visualization->user->username, $visualization->id]) }}">&#57556;</div>
	@endif
	<div id="like-info">
		{{ count($visualization->likes) }} like(s)
	</div>
@stop

@section('right-area-main')
	{{ Form::open(['name'=>'comment-form', 'url'=>URL::route('visualization.comment', [$visualization->user->username, $visualization->id])]) }}
		{{ Form::textarea('content', '', ['placeholder'=>'What are you having in mind?']) }}
		{{ Form::submit('Post', ['name'=>'submit-btn']) }}
	{{ Form::close() }}
	<article id="description-area">
		<h1>{{ $visualization->display_name }}</h1>
		<p><br><span class="h2">Author: </span>{{ $visualization->user->display_name }}</p>
		<p><span class="h2">Created at: </span>{{ $visualization->getFormattedCreatedDate() }}</p>
		<p><span class="h2">Last Updated at: </span>{{ $visualization->getFormattedUpdatedDate() }}</p>
		<p><br><span class="h2">Type: </span>{{ ucfirst($visualization->type) }}</p>
		<p id="zoom"><span class="h2">Zoom: </span>{{ number_format($visualization->zoom) }}</p>
		<p id="center"><span class="h2">Center: </span>{{ number_format($visualization->center_latitude, 3) }}, {{ number_format($visualization->center_longitude, 3) }}</p>
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
	{{ Form::open(['name'=>'hidden-form', 'url'=>'#']) }}
	{{ Form::close() }}
	@if (!$visualization->user->isAuthUser())
		@if (Auth::check() && Follow::where('user_id', Auth::user()->id)->where('following_id', $visualization->user->id)->first())
		<button id="follow-btn" data-url="{{ URL::route('user.unfollow', $visualization->user->username) }}"><i>&#57555;</i>Unfollow The Author</button>
		@else
		<button id="follow-btn" data-url="{{ URL::route('user.follow', $visualization->user->username) }}"><i>&#57553;</i>Follow The Author</button>
		@endif
	@else
		<button id="edit-visualization-btn" data-url="{{ URL::route('visualization.showEdit', [$visualization->user->username, $visualization->id]) }}"><i>&#57350;</i>Edit This Visualization</button>
	@endif
	</div>
@stop