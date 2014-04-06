@extends('show-visualizations')

@section('info')
	<p data-username="{{Auth::user()->display_name}}">{{Auth::user()->display_name}}'s followed author's visualizations:</p> 
@stop

@section('visualizations')
	@foreach ($visualizations as $visualization)
		<li class="visualization-item" data-vi-category="{{$visualization->category}}">
			<div class="overlay" style="display:none" data-url="{{URL::route('visualization.show', [$visualization->user->username, $visualization->id])}}">
				<p><span class="h2">Created at: </span>{{ $visualization->getFormattedCreatedDate() }}</p>
				<p><span class="h2">Last Updated at: </span>{{ $visualization->getFormattedUpdatedDate() }}</p>
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
@stop

@section('categories')
	@foreach ($categories as $category)
		<li class="category-item"><span class="category-caption">{{$category}}</span></li>
	@endforeach
@stop