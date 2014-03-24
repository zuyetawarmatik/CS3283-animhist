@extends('layouts.main4sections')

@section('css')
	<link rel="stylesheet/less" type="text/css" href="/css/view-visualization.less.css" />
@stop

@section('js')
	{{ HTML::script('js/project/page-view-visualization.js'); }}
@stop

@section('left-area-main')
	<div id="visualization-area">
		<div id="map" data-fusion-table="{{ $visualization->fusion_table_id }}">
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
		{{ $like_info }}
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
		<h1>California Electricity Consumption</h1>
		<p><br><span class="h2">Author: </span>Richard Koe</p>
		<p><span class="h2">Created at: </span>1:56, 1 Dec 2013</p>
		<p><span class="h2">Last Updated at: </span>2:38, 1 Dec 2013</p>
		<p><br><span class="h2">Brief Description:</span></p>
		<p>This is Photoshop's version of Lorem Ipsum. Proin gravida nibh vel velit auctor aliquet. Aenean sollicitudin, lorem quis bibendum auctor, nisi elit consequat ipsum, nec sagittis sem nibh id elit. Duis sed odio sit amet nibh vulputate cursus a sit amet mauris. Morbi accumsan ipsum velit. Nam nec tellus a odio tincidunt auctor a ornare odio. Sed non  mauris vitae erat consequat auctor eu in elit. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Mauris in erat justo. Nullam ac urna eu felis dapibus condimentum sit amet a augue. Sed non neque elit. Sed ut imperdiet nisi. Proincondimentum fermentum nunc. Etiam pharetra, erat sed fermentum feugiat, velit mauris egestas quam, ut aliquam massa nisl quis neque. Suspendisse in orci enim.</p>
	</article>
	@if (true)
		<div id="button-area">
			<button id="follow-btn"><i>&#57552;</i>Follow The Author</button>
		</div>
	@endif
@stop