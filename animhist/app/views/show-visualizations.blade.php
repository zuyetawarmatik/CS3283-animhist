@extends('layouts.main3sections')

@section('css')
	<link rel="stylesheet/less" type="text/css" href="css/play.less.css" />
@stop

@section('js')
	<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false"></script>
	{{ HTML::script('js/project/play.js'); }}
@stop
