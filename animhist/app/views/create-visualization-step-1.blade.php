@extends('layouts.main3sections')

@section('css')
	<link rel="stylesheet/less" type="text/css" href="/css/create-visualization-step-1.less.css" />
@stop

@section('js')
	{{ HTML::script('js/project/page-create-visualization-step-1.js'); }}
@stop

@section('left-area')
	{{ Form::open(array('name'=>'create-visualization-form')) }}
		<table>
			<tr>
				<td>{{ Form::label('display_name', 'Name:') }}</td>
				<td>{{ Form::text('display_name') }}</td>
			</tr>
			<tr>
				<td>{{ Form::label('description', 'Brief Description:') }}</td>
				<td>{{ Form::textarea('description') }}</td>
			</tr>
			<tr>
				<td colspan="2">
					{{ Form::submit('Next Step', array('name'=>'submit-btn')) }}
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