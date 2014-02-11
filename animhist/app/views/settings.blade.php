@extends('layouts.main3sections')

@section('css')
	<link rel="stylesheet/less" type="text/css" href="/css/settings.less.css" />
@stop

@section('js')
	{{ HTML::script('js/project/page-settings.js'); }}
@stop

@section('left-area')
	{{ Form::open(array('name'=>'settings-form', 'url'=>URL::route('user.update', Auth::user()->username), 'method'=>'put')) }}
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
				<td>{{ Form::button('Change Password...', array('name'=>'changepwd-btn', 'type'=>'button')) }}</td>
			</tr>
			<tr>
				<td colspan="2">
					{{ Form::submit('Save Changes', array('name'=>'submit-btn')) }}
				</td>
			</tr>
		</table>
	{{ Form::close() }}
@stop