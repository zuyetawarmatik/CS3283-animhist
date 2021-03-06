@extends('show-visualizations')

@section('css')
	<link rel="stylesheet/less" type="text/css" href="/css/show-visualizations-personal.less.css" />
@stop

@section('js')
	@parent
	{{ HTML::script('js/project/page-show-visualizations-personal.js'); }}
@stop

@section('top-bar')
	@parent
	@if ($user->isAuthUser())
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
		<p style="font-style:italic"><span id="num-followers">{{ count($user->followers) }}</span> followers</p>
		@if (!$user->isAuthUser())	<img style="float:left; margin-top:2.5rem; margin-right:1rem; background:#666" width="60" height="60" src="{{ $user->avatar->url('thumb') }}" /> @endif
		<p><br><span class="h2">Username: </span>{{ '@'.$user->username }}</p>
		<p><span class="h2">Joined from: </span>{{ $user->getFormattedCreatedDate() }}</p>
		@if ($user->isAuthUser())
		<p id="num-visualizations"><span class="h2">Visualizations: </span><span class="content">{{ count($user->visualizations) }}</span></p>
		@endif
		<p id="num-pub-visualizations"><span class="h2">Published Visualizations: </span><span class="content">{{ count($user->publishedVisualizations) }}</span></p>
		<p><br><span class="h2">Brief Description:</span></p>
		<p>
			@if ($user->description)
				{{ $user->description }}
			@else
				@if ($user->isAuthUser())
					(You do 
				@else
					(The user does 
				@endif
				not have any description yet.)
			@endif
		</p>
	</article>
	<div id="button-area">
	@if (!$user->isAuthUser())
		@if (Auth::check() && Follow::where('user_id', Auth::user()->id)->where('following_id', $user->id)->first())
		<button id="follow-btn" data-url="{{ URL::route('user.unfollow', $user->username) }}"><i>&#57555;</i>Unfollow The Author</button>
		@else
		<button id="follow-btn" data-url="{{ URL::route('user.follow', $user->username) }}"><i>&#57553;</i>Follow The Author</button>
		@endif
	@else
		<button id="edit-profile-btn" data-url="{{ URL::route('user.showEdit', $user->username) }}"><i>&#57350;</i>Edit My Profile</button>
	@endif
	</div>
@stop

@section('info')
	<p data-username="{{$user->display_name}}">{{$user->display_name}}'s all visualizations:</p> 
@stop

@section('visualizations')
	{{ Form::open(['name'=>'hidden-form', 'url'=>'#']) }}
	{{ Form::close() }}
	@foreach ($visualizations as $visualization)
		@if ($user->isAuthUser() || (!$user->isAuthUser() && $visualization->published))
			<li class="visualization-item @if(!$visualization->published) unpublished @endif" data-vi-category="{{$visualization->category}}">
				@if ($visualization->published)
				<div class="overlay" style="display:none" data-url="{{URL::route('visualization.show', [$visualization->user->username, $visualization->id])}}">
				@else
				<div class="overlay" style="display:none" data-url="{{URL::route('visualization.showCreate', $visualization->user->username).'?step=2&vi_id='.$visualization->id}}">
				@endif
					<p><span class="h2">Created at: </span>{{ $visualization->getFormattedCreatedDate() }}</p>
					<p><span class="h2">Last Updated at: </span>{{ $visualization->getFormattedUpdatedDate() }}</p>
					<p style="font-weight:700">{{ count($visualization->likes) }} like(s)</p>
					<div class="visualization-buttons">
						@if ($visualization->published)
							<a href="{{ URL::route('visualization.show', [$visualization->user->username, $visualization->id]) }}">&#57542;</a>
						@endif
						@if ($visualization->user->isAuthUser())
							@if ($visualization->published)
								<a href="{{ URL::route('visualization.showEdit', [$visualization->user->username, $visualization->id]) }}">&#57350;</a>
							@else
								<a href="{{ URL::route('visualization.showCreate', $visualization->user->username).'?step=2&vi_id='.$visualization->id }}">&#57350;</a>
							@endif
							<a class="del" data-url="{{ URL::route('visualization.destroy', [$visualization->user->username, $visualization->id]) }}">&#57512;</a>
						@endif
					</div>
				</div>
				<div class="visualization-img"><img src="http://maps.googleapis.com/maps/api/staticmap?maptype=terrain&key=AIzaSyBTVe9qjhnOgr7dNZJGjpQkyuViCn3wKDU&center={{$visualization->center_latitude}},{{$visualization->center_longitude}}&zoom={{max(number_format($visualization->zoom)-1,1)}}&size=340x200&sensor=false"/></div>
				<div class="avatar-wrapper">
					<a href="{{ URL::route('user.show', $user->username) }}"><img class="avatar" src="{{ $user->avatar->url('thumb') }}" /></a>
				</div>
				<div class="visualization-main">
					<p class="visualization-title">{{$visualization->display_name}}</p>
					<p class="visualization-author"><a href="{{ URL::route('user.show', $user->username) }}" class="username">{{$user->display_name}}</a></p>
				</div>
			</li>
		@endif
	@endforeach
@stop
