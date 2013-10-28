@extends('layouts.mainbase')

@section('body')
	<div id="left-area" class="main4sections">
		<div id="top-bar">
			@if ($has_back)
				<div id="back-btn">&#57446;</div>
				<div id="title">{{ $title }}</div>
			@else
				<div id="title" class="no-back">{{ $title }}</div>
			@endif
		</div>
		<div id="left-area-main">
			@yield('left-area-main')
		</div>
	</div>
	<div id="right-area" class="main4sections">
		<div id="action-bar">
			@yield('action-bar')
			<div id="right-area-showhide-btn">&#57477;</div>
		</div>
		<div id="right-area-main">
			@yield('right-area-main')
		</div>
	</div>
@stop