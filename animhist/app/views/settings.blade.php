@extends('layouts.main3sections')

@section('css')
	{{ HTML::style('js/vex-2.0.1/css/vex.css'); }}
	{{ HTML::style('js/vex-2.0.1/css/vex-theme-wireframe.css'); }}
	<link rel="stylesheet/less" type="text/css" href="/css/settings.less.css" />
@stop

@section('js')
	{{ HTML::script('js/vex-2.0.1/js/vex.combined.min.js'); }}
	{{ HTML::script('js/project/page-settings.js'); }}
	<script>vex.defaultOptions.className = 'vex-theme-wireframe';</script>
@stop

@section('left-area')
	{{ Form::open(['name'=>'settings-form', 'url'=>URL::route('user.update', Auth::user()->username), 'method'=>'put', 'data-user-id'=>Auth::user()->username]) }}
		<table>
			<tr>
				<td>{{ Form::label('display-name', 'Display Name:') }}</td>
				<td>{{ Form::text('display-name', Auth::user()->display_name) }}</td>
			</tr>
			<tr>
				<td>{{ Form::label('description', 'Brief Description:') }}</td>
				<td>{{ Form::textarea('description', Auth::user()->description) }}</td>
			</tr>
			<tr>
				<td>{{ Form::label('password', 'Password:') }}</td>
				<td>{{ Form::button('Change Password...', ['name'=>'changepwd-btn', 'type'=>'button']) }}</td>
			</tr>
			<tr>
				<td colspan="2">
					<button name="submit-btn"><i>&#57598;</i>Save Changes</button>
				</td>
			</tr>
		</table>
	{{ Form::close() }}
@stop