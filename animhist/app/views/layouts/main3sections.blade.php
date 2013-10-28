@extends('layouts.mainbase')

@section('body')
	<div id="top-bar">
		@if ($has_back)
			<div id="back-btn">&#57446;</div>
			<div id="title">{{ $title }}</div>
		@else
			<div id="title" class="no-back">{{ $title }}</div>
		@endif
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