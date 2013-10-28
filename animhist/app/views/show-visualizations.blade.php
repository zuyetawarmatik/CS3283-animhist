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
			<li class="category-item">All</li>
			<li class="category-item">Arts</li>
			<li class="category-item selected">Science</li>
			<li class="category-item">Social Science</li>
			<li class="category-item">Technology</li>
		</ul>
	</div>
@stop