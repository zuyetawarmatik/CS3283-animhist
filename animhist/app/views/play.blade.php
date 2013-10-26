@extends('layouts.main4sections')

@section('css')
	<link rel="stylesheet/less" type="text/css" href="css/play.less.css" />
@stop

@section('js')
	<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false"></script>
	{{ HTML::script('js/project/play.js'); }}
@stop

@section('left-area-main')
	<div id="visualization-area">
		<div id="map">
		</div>
		<div id="seek">
		</div>
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
		<h1>USA Population in 20th Century</h1>
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