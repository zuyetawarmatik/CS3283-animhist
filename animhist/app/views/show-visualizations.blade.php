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
		@yield('visualizations')
	</ul>
@stop

@section('right-area')
	<div id="category-area">
		<h1>Categories</h1>
		<ul id="category-list">
			<li class="category-item selected"><span class="category-caption">All</span></li>
			@yield('categories')
		</ul>
	</div>
@stop