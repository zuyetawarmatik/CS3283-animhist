@extends('layouts.mainbase')

@section('body')
	<div id="top-bar">
		@section('top-bar')
			@if ($has_back)
				<div id="back-btn" data-url="{{ $back_url }}">&#57446;</div>
				<div id="title">{{ $title }}</div>
			@else
				<div id="title" class="no-back">{{ $title }}</div>
			@endif
		@show
	</div>
	<div id="main-area">
		<div id="left-area" class="main3sections">
			@yield('left-area')
		</div>
		<div id="right-area" class="main3sections">
			@if ($has_minimize_right)
				<div id="right-area-showhide-btn">&#57477;</div>
			@endif
			@yield('right-area')
		</div>
	</div>
@stop