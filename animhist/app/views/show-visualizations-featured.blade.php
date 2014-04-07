@extends('show-visualizations')

@section('info')
	<p data-username="{{Auth::user()->display_name}}">{{Auth::user()->display_name}}'s followed author's visualizations:</p> 
@stop