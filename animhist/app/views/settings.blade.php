@extends('layouts.main3sections')

@section('css')
	<link rel="stylesheet/less" type="text/css" href="/css/settings.less.css" />
@stop

@section('left-area')
	{{ Form::open(array('name'=>'settings-form', 'url'=>'')) }}
		<table>
			<tr>
				<td>{{ Form::label('display-name', 'Display Name:') }}</td>
				<td>{{ Form::text('display-name', 'Francesc') }}</td>
			</tr>
			<tr>
				<td>{{ Form::label('description', 'Brief Description:') }}</td>
				<td>{{ Form::textarea('description', 'Lorem ipsum dolor sit amet') }}</td>
			</tr>
			<tr>
				<td>{{ Form::label('password', 'Password:') }}</td>
				<td>{{ Form::button('Change Password...', array('name'=>'changepwd-btn')) }}</td>
			</tr>
		</table>
		<!-- {{ Form::submit('Post', array('name'=>'submit-btn')) }} -->
	{{ Form::close() }}
@stop