@extends('layouts.main3sections')

@section('css')
	<link rel="stylesheet/less" type="text/css" href="/css/create-visualization-step-1.less.css" />
@stop

@section('js')
	{{ HTML::script('js/project/page-create-visualization-step-1.js'); }}
@stop

@section('left-area')
	{{ Form::open(array('name'=>'create-visualization-form', 'url'=>URL::route('visualization.store', Auth::user()->username))) }}
		<table>
			<tr>
				<td>{{ Form::label('display-name', 'Name:') }}</td>
				<td>{{ Form::text('display-name') }}</td>
			</tr>
			<tr>
				<td>{{ Form::label('description', 'Brief Description:') }}</td>
				<td>{{ Form::textarea('description') }}</td>
			</tr>
			<tr>
				<td>{{ Form::label('type', 'Type:') }}</td>
				<td><div class="styled-radio">
						{{ Form::radio('type', 'point', true) }}
						<label><span><span></span></span>Point</label>
					</div>
					<div class="styled-radio">
    					{{ Form::radio('type', 'polygon') }}
    					<label><span><span></span></span>Polygon</label>
    				</div>
    			</td>
			</tr>
			<tr>
				<td>{{ Form::label('category', 'Category:') }}</td>
				<td><div class="styled-select">{{ Form::select('category', array('Social Science' => 'Social Science', 'Science' => 'Science', 'Miscellaneous' => 'Miscellaneous')) }}</div></td>
			</tr>
			<tr>
				<td colspan="2">
					<div class="styled-radio">
						{{ Form::radio('option', 'manual', true) }}
						<label><span><span></span></span>I would like to use Web Interface to input data items manually, provided that my table will have these columns as followed:</label>
					</div>					
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<div class="styled-radio">
						{{ Form::radio('option', 'upload') }}
						<label><span><span></span></span>I would like to upload my data (Make sure your data has Milestone and Position columns, <a>download our template Excel file here</a>)</label>
					</div>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<button type="submit" name="submit-btn"><i>&#57614;</i>Next Step</button>
				</td>
			</tr>
		</table>
	{{ Form::close() }}
@stop

@section('right-area')
	<article id="description-area">
		<h1>Create a new visualization</h1>
		<p><br>A visualization represents data temporally using milestone slider and spatially using maps. The data items can be added manually using the Web Interface, or can be uploaded provided that the data table supplied has 2 compulsory columns: <span style="color:#11fdcb;">Position in KML or (Latitude, Longitude) or String format</span> and <span style="color:#11fdcb;">Milestone in Date/Time format</span>. In addition, you can indicate whether your uploaded data table needs the system to add into itself <span style="color:#11fdcb;">a HTMLData column</span> and <span style="color:#11fdcb;">a number variable column</span>.</p>
	</article>
@stop