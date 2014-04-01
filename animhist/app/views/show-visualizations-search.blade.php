@extends('show-visualizations')

@section('css')
	<link rel="stylesheet/less" type="text/css" href="/css/show-visualizations-search.less.css" />
@stop

@section('js')
	@parent
	{{ HTML::script('js/project/page-show-visualizations-search.js'); }}
@stop

@section('top-bar')
	@parent
	{{ Form::open(array('name'=>'search-form', 'url'=>URL::Route('visualization.search'))) }}
		<i>&#57471;</i>
		{{ Form::text('search-box', 'Type your search here') }}
	{{ Form::close() }}
@stop