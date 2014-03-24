@extends('layouts.main3sections')

@section('css')
	{{ HTML::style('js/spectrum/spectrum.css'); }}
	{{ HTML::style('js/vex-2.0.1/css/vex.css'); }}
	{{ HTML::style('js/vex-2.0.1/css/vex-theme-wireframe.css'); }}
	{{ HTML::style('js/slickgrid/css/smoothness/jquery-ui-1.8.16.custom.css'); }}
	{{ HTML::style('js/slickgrid/slick.grid.css'); }}
	<link rel="stylesheet/less" type="text/css" href="/css/create-visualization-step-2.less.css" />
@stop

@section('js')
	<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false"></script>
	{{ HTML::script('js/attrchange/attrchange.js'); }}
	{{ HTML::script('js/spectrum/spectrum.js'); }}
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
	{{ HTML::script('js/project/page-create-visualization-step-2-property.js'); }}
	{{ HTML::script('js/project/page-create-visualization-step-2-column.js'); }}
	{{ HTML::script('js/project/page-create-visualization-step-2-table.js'); }}
	{{ HTML::script('js/project/page-create-visualization-step-2-style.js'); }}
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
			<ul id="timeline-list"></ul>
		</div>
	</div>
	<div id="edit-area-table" class="current">
		<div id="toolbar">	
			<button id="delete-row-btn" title="Delete Row(s)" disabled><i>&#57597;</i></button>
			<ul id="filter-list"></ul>
		</div>
		<div id="table"></div>
	</div>
	<div id="edit-area-style">
		<div id="toolbar">
			<button id="save-btn" class="blue-btn" title="Save Style"><i>&#57440;</i></button>
			<button id="delete-row-btn" title="Delete Style Row(s)" disabled><i>&#57597;</i></button>
			<span class="styled-select">
				<select id="style-column-select">
				</select>
			</span>
		</div>
		<div id="table"></div>
	</div>
</div>
@stop

@section('right-area')
	<div id="description-area">
		<h1 class="editable" id="displayname"><span class="content">{{ $visualization->display_name }}</span></h1>
		<p><br><span class="h2">Author: </span>{{ $visualization->user->display_name }}</p>
		<p><span class="h2">Created at: </span>{{ $visualization->getFormattedCreatedDate() }}</p>
		<p><span class="h2">Type: </span>{{ ucfirst($visualization->type) }}</p>
		<p class="editable" id="zoom"><span class="h2">Zoom: </span><span class="content">{{ number_format($visualization->zoom) }}</span></p>
		<p class="editable" id="center"><span class="h2">Center: </span><span class="content">{{ number_format($visualization->center_latitude, 2) }}, {{ number_format($visualization->center_longitude, 2) }}</span></p>
		<p class="editable" id="category"><span class="h2">Category: </span><span class="content">{{ $visualization->category }}</span></p>
		<p class="editable" id="description"><br><span class="h2">Brief Description:</span></p>
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
		<span class="h2">Default Column</span>
		<span class="styled-select">
			<select id="default-column-select">
			</select>
		</span>
		<span class="h2">List of Columns</span>
		<ul id="column-list">
		</ul>
	</div>
	<div id="description-area">
		<h1>Modify a style</h1>
		<p><br>Style for a specific column is divided into buckets by levels. Each level (the minimum value of a range) has its own color and opacity if the visualization is of polygon type, and icon if the visualization is of point type.</p>
	</div>
	<div id="button-area">
		<button id="publish-btn"><i>&#57534;</i>Publish The Visualization</button>
		<button id="delete-btn" class="red-btn"><i>&#57512;</i>Discard The Visualization</button>
	</div>
@stop