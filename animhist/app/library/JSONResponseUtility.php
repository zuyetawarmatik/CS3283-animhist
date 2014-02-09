<?php
class JSONResponseUtility {
	
	public static function Redirect($url, $whole_page = true) {
		return Response::json(['redirect'=>$url, 'wholePage'=>$whole_page]);
	}
	
	public static function ValidationError($arr) {
		return Response::json(['error'=>$arr], 400);
	}
	
}