@extends('layouts.main3sections')

@section('css')
	<link rel="stylesheet/less" type="text/css" href="css/show-visualizations.less.css" />
@stop

@section('left-area')
	
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