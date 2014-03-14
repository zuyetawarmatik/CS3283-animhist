@extends('layouts.main3sections')

@section('css')
	{{ HTML::style('js/vex-2.0.1/css/vex.css'); }}
	{{ HTML::style('js/vex-2.0.1/css/vex-theme-wireframe.css'); }}
	{{ HTML::style('js/slickgrid/css/smoothness/jquery-ui-1.8.16.custom.css'); }}
	{{ HTML::style('js/slickgrid/slick.grid.css'); }}
	<link rel="stylesheet/less" type="text/css" href="/css/create-visualization-step-2.less.css" />
@stop

@section('js')
	<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false"></script>
	{{ HTML::script('js/attrchange/attrchange.js'); }}
	{{ HTML::script('js/vex-2.0.1/js/vex.combined.min.js'); }}
	{{ HTML::script('js/jquery.event.drag-2.2/jquery.event.drag-2.2.js'); }}
	{{ HTML::script('js/jquery.event.drag-2.2/jquery.event.drag.live-2.2.js'); }}
	{{ HTML::script('js/jquery.event.drop-2.2/jquery.event.drop-2.2.js'); }}
	{{ HTML::script('js/jquery.event.drop-2.2/jquery.event.drop.live-2.2.js'); }}
	{{ HTML::script('js/slickgrid/slick.core.js'); }}
	{{ HTML::script('js/slickgrid/slick.formatters.js'); }}
	{{ HTML::script('js/slickgrid/slick.editors.js'); }}
	{{ HTML::script('js/slickgrid/slick.dataview.js'); }}
	{{ HTML::script('js/slickgrid/slick.grid.js'); }}
	{{ HTML::script('js/slickgrid/plugins/slick.rowselectionmodel.js'); }}
	{{ HTML::script('js/slickgrid/plugins/slick.checkboxselectcolumn.js'); }}
	{{ HTML::script('js/moment.min.js'); }}
	{{ HTML::script('js/project/modify-visualization-shared.js'); }}
	{{ HTML::script('js/project/page-create-visualization-step-2.js'); }}
	{{ HTML::script('js/project/page-create-visualization-step-2-column.js'); }}
	{{ HTML::script('js/project/page-create-visualization-step-2-table.js'); }}
	{{ HTML::script('js/project/page-create-visualization-step-2-visualization.js'); }}
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
		<div id="map" data-fusion-table="{{ $visualization->fusion_table_id }}">
		</div>
		<div id="seekbar">	
			<button id="play-btn" title="Play"><i>&#57610;</i></button>
			<ul id="timeline-list">
			</ul>
		</div>
	</div>
	<div id="edit-area-table" class="current">
		<div id="toolbar">	
			<button id="delete-row-btn" title="Delete Row(s)" disabled><i>&#57597;</i></button>
			<ul id="filter-list">
			</ul>
		</div>
		<div id="table"></div>
	</div>
	<div id="edit-area-style">
	</div>
</div>
@stop

@section('right-area')
	<div id="description-area">
		<h1 class="editable">{{ $visualization->display_name }}</h1>
		<p><br><span class="h2">Author: </span>{{ $visualization->user->display_name }}</p>
		<p><span class="h2">Created at: </span>{{ $visualization->getFormattedCreatedDate() }}</p>
		<p><span class="h2">Type: </span>{{ ucfirst($visualization->type) }}</p>
		<p class="editable"><span class="h2">Category: </span>{{ $visualization->category }}</p>
		<p class="editable"><br><span class="h2">Brief Description:</span></p>
		<p>
			@if ($visualization->description)
				{{ $visualization->description }}
			@else
				(The visualization does not have any description yet.)
			@endif
		</p>
	</div>
	<div id="column-list-area" class="current">
		<h1>{{ $visualization->display_name }}</h1>
		<span class="h2">List of Columns</span>
		<ul id="column-list">
		</ul>
	</div>
@stop