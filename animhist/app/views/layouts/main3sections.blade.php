@extends('layouts.mainbase')

@section('body')
	<div id="top-bar">
		@if (true)
			<div id="back-btn">&#57446;</div>
		@endif
		<div id="title">
			{{ $title }}
		</div>
	</div>
	<div id="main-area">
		<div id="left-area" class="main3sections">
			@yield('left-area')
		</div>
		<div id="right-area" class="main3sections">
			<div id="right-area-showhide-btn">&#57477;</div>
			@yield('right-area')
		</div>
	</div>
@stop