<?php
class JSONResponseUtility {
	
	public static function Redirect($url) {
		return Response::json(['redirect'=>$url]);
	}
	
	public static function ValidationError($arr) {
		return Response::json(['error'=>$arr], 400);
	}
	
}