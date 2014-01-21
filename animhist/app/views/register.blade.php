@extends('layouts.main3sections')

@section('css')
	<link rel="stylesheet/less" type="text/css" href="/css/register.less.css" />
@stop

@section('js')
	{{ HTML::script('js/project/page-register.js'); }}
@stop

@section('left-area')
	{{ Form::open(['name'=>'register-form', 'url'=>URL::route('user.store'), 'files'=>true]) }}
		<table>
			<tr>
				<td>{{ Form::label('username', 'Username:') }}</td>
				<td>{{ Form::text('username') }}</td>
			</tr>
			<tr>
				<td>{{ Form::label('display-name', 'Display Name:') }}</td>
				<td>{{ Form::text('display-name') }}</td>
			</tr>
			<tr>
				<td>{{ Form::label('email', 'Email:') }}</td>
				<td>{{ Form::text('email') }}</td>
			</tr>
			<tr>
				<td>{{ Form::label('password', 'Password:') }}</td>
				<td>{{ Form::password('password') }}</td>
			</tr>
			<tr>
				<td>{{ Form::label('password-retype', 'Retype Password:') }}</td>
				<td>{{ Form::password('password-retype') }}</td>
			</tr>
			<tr>
				<td>{{ Form::label('description', 'Self Description:') }}</td>
				<td>{{ Form::textarea('description') }}</td>
			</tr>
			<tr>
				<td>{{ Form::label('avatar', 'Upload Avatar:') }}</td>
				<td>{{ Form::file('avatar') }}</td>
			</tr>
			<tr>
				<td colspan="2">
					{{ Form::submit('Register', array('name'=>'register-btn')) }}
				</td>
			</tr>
		</table>
	{{ Form::close() }}
@stop

@section('right-area')
	<article id="description-area">
		<h1>Welcome to Animhist!</h1>
		<p><br>Create and play historical visualization seamlessly in the fastest way. Share it around the world. Be a geek historian.</p>
	</article>
@stop