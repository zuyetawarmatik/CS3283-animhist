@extends('layouts.main3sections')

@section('css')
	{{ HTML::style('js/vex-2.0.1/css/vex.css'); }}
	{{ HTML::style('js/vex-2.0.1/css/vex-theme-wireframe.css'); }}
	{{ HTML::style('js/slickgrid/css/smoothness/jquery-ui-1.8.16.custom.css'); }}
	{{ HTML::style('js/slickgrid/slick.grid.css'); }}
	<link rel="stylesheet/less" type="text/css" href="/css/create-visualization-step-2.less.css" />
@stop

@section('js')
	{{ HTML::script('js/vex-2.0.1/js/vex.combined.min.js'); }}
	{{ HTML::script('js/jquery.event.drag-2.2/jquery.event.drag-2.2.js'); }}
	{{ HTML::script('js/jquery.event.drag-2.2/jquery.event.drag.live-2.2.js'); }}
	{{ HTML::script('js/jquery.event.drop-2.2/jquery.event.drop-2.2.js'); }}
	{{ HTML::script('js/jquery.event.drop-2.2/jquery.event.drop.live-2.2.js'); }}
	{{ HTML::script('js/slickgrid/slick.core.js'); }}
	{{ HTML::script('js/slickgrid/slick.formatters.js'); }}
	{{ HTML::script('js/slickgrid/slick.editors.js'); }}
	{{ HTML::script('js/slickgrid/slick.grid.js'); }}
	{{ HTML::script('js/slickgrid/plugins/slick.rowselectionmodel.js'); }}
	{{ HTML::script('js/slickgrid/plugins/slick.checkboxselectcolumn.js'); }}
	{{ HTML::script('js/date-format.js'); }}
	{{ HTML::script('js/project/page-create-visualization-step-2.js'); }}
	{{ HTML::script('js/project/page-create-visualization-step-2-property.js'); }}
	{{ HTML::script('js/project/page-create-visualization-step-2-table.js'); }}
	<script>vex.defaultOptions.className = 'vex-theme-wireframe';</script>
@stop

@section('left-area')
<div id="edit-area" data-user-id="{{ Auth::user()->username }}" data-vi-id="{{ $visualization->id }}">
	{{ Form::open(array('name'=>'hidden-form', 'url'=>'#')) }}
	{{ Form::close() }}
	<ul id="tab">
		<li><a href="#">Visualization</a></li>
		<li><a href="#" class="current">Table</a></li>
		<li><a href="#">Style</a></li>
	</ul>
	<div id="edit-area-visualization">
	</div>
	<div id="edit-area-table" class="current">
		<div id="toolbar">	
			<button id="delete-row-btn" title="Delete Row(s)" disabled><i>&#57597;</i></button>
		</div>
		<div id="table"></div>
	</div>
	<div id="edit-area-style">
	</div>
</div>
@stop

@section('right-area')
	<div id="column-list-area">
		<h1>{{ $visualization->display_name }}</h1>
		<span class="h2">List of Columns</span>
		<ul id="column-list">
		</ul>
	</div>
@stop