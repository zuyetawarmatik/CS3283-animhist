@extends('layouts.main3sections')

@section('css')
	<link rel="stylesheet/less" type="text/css" href="/css/login.less.css" />
@stop

@section('js')
	{{ HTML::script('js/project/page-login.js'); }}
@stop

@section('left-area')
	{{ Form::open(array('name'=>'login-form', 'url'=>URL::route('user.login'))) }}
		<table>
			<tr>
				<td>{{ Form::label('username', 'Username or Email:') }}</td>
				<td>{{ Form::text('username') }}</td>
			</tr>
			<tr>
				<td>{{ Form::label('password', 'Password:') }}</td>
				<td>{{ Form::password('password') }}</td>
			</tr>
			<tr>
				<td colspan="2">
					{{ Form::submit('Login', array('name'=>'login-btn')) }}
					{{ Form::button('Forgot Your Password?', array('name'=>'forget-btn', 'class'=>'grey-btn')) }}
				</td>
			</tr>
		</table>
	{{ Form::close() }}
@stop

@section('right-area')
	<article id="description-area">
		<h1>Haven't had an account yet?</h1>
		<p><br>Sign up now to completely benefit from Animhist! Create and play historical visualization seamlessly in the fastest way. Share it around the world. Be a geek historian.</p>
		<button id="register-btn" data-url="{{URL::route('user.showCreate')}}">Create New Account</button>
	</article>
@stop