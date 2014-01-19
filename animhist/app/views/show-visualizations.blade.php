@extends('layouts.main3sections')

@section('css')
	<link rel="stylesheet/less" type="text/css" href="/css/show-visualizations.less.css" />
@stop

@section('left-area')
	<ul id="visualization-list">
		<li class="visualization-item">
			<a href="{{URL::to('play')}}" class="visualization-link"><img class="visualization-img" src="images/visualization.png" width="340" height="200"/></a>
			<div class="avatar-wrapper">
				<a href="#"><img class="avatar" src="images/cavatar2.jpg" width="60" height="60" /></a>
			</div>
			<div class="visualization-main">
				<p class="visualization-title">California Electrical Consumption</p>
				<p class="visualization-author"><a href="#" class="username">Mr Dhane</a></p>
			</div>
		</li>
	</ul>
@stop

@section('right-area')
	<div id="category-area">
		<h1>Categories</h1>
		<ul id="category-list">
			<li class="category-item"><span class="category-caption">All</span></li>
			<li class="category-item"><span class="category-caption">Arts</span></li>
			<li class="category-item selected"><span class="category-caption">Science</span></li>
			<li class="category-item"><span class="category-caption">Social Science</span></li>
			<li class="category-item"><span class="category-caption">Technology</span></li>
		</ul>
	</div>
@stop