@extends('show-visualizations')

@section('css')
	<link rel="stylesheet/less" type="text/css" href="/css/show-visualizations-personal.less.css" />
@stop

@section('js')
	{{ HTML::script('js/project/page-show-visualizations-personal.js'); }}
@stop

@section('right-area')
@parent
<ul id="right-area-tab">
	<li id="right-area-tab-info" class="selected">&#57379;</li>
	<li id="right-area-tab-category">&#57525;</li>
</ul>
<article id="description-area">
	<h1>{{ $user->display_name.'&#39;s Profile' }}</h1>
	<p><br><span class="h2">Username: </span>{{ '@'.$user->username }}</p>
	<p><span class="h2">Joined from: </span>{{ $user->created_at }}</p>
	<p><span class="h2">Visualizations: </span></p>
	<p><br><span class="h2">Brief Description:</span></p>
	<p>
		@if ($user->description) $user->description
		@else (@if (Auth::user() == $user)You do @elseThe user does @endifnot have any description yet.) @endif
	</p>
</article>
@stop