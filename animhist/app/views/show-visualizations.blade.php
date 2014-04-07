@extends('layouts.main3sections')

@section('css')
	<link rel="stylesheet/less" type="text/css" href="/css/show-visualizations.less.css" />
@stop

@section('js')
	{{ HTML::script('js/project/page-show-visualizations.js'); }}
@stop

@section('left-area')
	<div class="visualizations-info">
		@yield('info')
	</div>
	<ul id="visualization-list">
		@section('visualizations')
			@foreach ($visualizations as $visualization)
				<li class="visualization-item" data-vi-category="{{$visualization->category}}">
					<div class="overlay" style="display:none" data-url="{{URL::route('visualization.show', [$visualization->user->username, $visualization->id])}}">
						<p><span class="h2">Created at: </span>{{ $visualization->getFormattedCreatedDate() }}</p>
						<p><span class="h2">Last Updated at: </span>{{ $visualization->getFormattedUpdatedDate() }}</p>
						<p style="font-weight:700">{{ count($visualization->likes) }} like(s)</p>
						<div class="visualization-buttons">
							<a href="{{ URL::route('visualization.show', [$visualization->user->username, $visualization->id]) }}">&#57542;</a>
						</div>
					</div>
					<div class="visualization-img"><img src="http://maps.googleapis.com/maps/api/staticmap?maptype=terrain&key=AIzaSyBTVe9qjhnOgr7dNZJGjpQkyuViCn3wKDU&center={{$visualization->center_latitude}},{{$visualization->center_longitude}}&zoom={{max(number_format($visualization->zoom)-1,1)}}&size=340x200&sensor=false"/></div>
					<div class="avatar-wrapper">
						<a href="{{ URL::route('user.show', $visualization->user->username) }}"><img class="avatar" src="{{ $visualization->user->avatar->url('thumb') }}" /></a>
					</div>
					<div class="visualization-main">
						<p class="visualization-title">{{$visualization->display_name}}</p>
						<p class="visualization-author"><a href="{{ URL::route('user.show', $visualization->user->username) }}" class="username">{{$visualization->user->display_name}}</a></p>
					</div>
				</li>
			@endforeach
		@show
	</ul>
@stop

@section('right-area')
	<div id="category-area">
		<h1>Categories</h1>
		<ul id="category-list">
			<li class="category-item selected"><span class="category-caption">All</span></li>		
			@foreach ($categories as $category)
				<li class="category-item"><span class="category-caption">{{$category}}</span></li>
			@endforeach
		</ul>
	</div>
@stop