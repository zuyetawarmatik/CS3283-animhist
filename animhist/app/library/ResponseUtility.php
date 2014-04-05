<?php
class ResponseUtility {
	
	public static function badRequest() {
		return Response::make('', Constant::STATUS_BAD_REQUEST);
	}
	
	public static function unauthorized() {
		return Response::make('', Constant::STATUS_UNAUTHORIZED);
	}
	
	public static function success() {
		return Response::make('', Constant::STATUS_SUCCESS);
	}
	
}