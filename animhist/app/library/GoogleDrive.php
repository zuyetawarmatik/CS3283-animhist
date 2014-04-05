<?php

use Httpful\Request;

class GoogleDrive {
	
	private static function getGDriveOAuthAccessToken() {
		if (Session::get('gdrive_access_token'))
			return Session::get('gdrive_access_token');
		else {
			Redis::connection();
			Redis::auth('78d257f1df9e8afc9d503e4b523ccbab');
			$access_token = Redis::get('gdrive_access_token');
			Session::set('gdrive_access_token', $access_token);
			return $access_token;
		}
		//return 'ya29.1.AADtN_XE5XDCH3CaHAnA38J0KlDahzNxaV7RfMffxAdw0Lzvslu0l9w-rW5eGzy5Gj0VOg';
	}

	private static function setGDriveOAuthAccessToken($val) {
		Redis::connection();
		Redis::auth('78d257f1df9e8afc9d503e4b523ccbab');
		Redis::set('gdrive_access_token', $val);
		Session::set('gdrive_access_token', $val);
	}
	
	private static function getGDriveOAuthRefreshToken() {
		/*
		Redis::connection();
		Redis::auth('78d257f1df9e8afc9d503e4b523ccbab');
		$refresh_token = Redis::get('gdrive_refresh_token');
		return $refresh_token;*/
		return '1/2tvTDU2asdIwf4z3N2vzJdIXT0LnEfVnpwixVCKhi-Q';
	}
	
	private static function refreshGDriveOAuthAccessToken() {
		$refresh_req_data = http_build_query(['client_id' => '152751724162-quq2loao55dns7j693ce23tq683uajf7',
				'client_secret' => 'mVK5992bvMNlwA1w8o1Q6y1N',
				'refresh_token' => self::getGDriveOAuthRefreshToken(),
				'grant_type' => 'refresh_token']);
		$response = Request::post('https://accounts.google.com/o/oauth2/token')
					->sends('application/x-www-form-urlencoded')
					->body($refresh_req_data)
					->send();
		$access_token = $response->body->access_token;
		self::setGDriveOAuthAccessToken($access_token);
	
		return $access_token;
	}
	
	public static function getFileIDForFusionTable($name) {
		$query = urlencode("title='".$name."' and trashed = false");

		$response = Request::get('https://www.googleapis.com/drive/v2/files?q='.$query)
							->addHeaders(['Authorization'=>'Bearer '.self::getGDriveOAuthAccessToken()])
							->send();
		if ($response->code == Constant::STATUS_UNAUTHORIZED) {
			$access_token = self::refreshGDriveOAuthAccessToken();
			$response = Request::get('https://www.googleapis.com/drive/v2/files?q='.$query)
						->addHeaders(['Authorization'=>'Bearer '.$access_token])
						->send();
		}
		
		if ($response->code == Constant::STATUS_SUCCESS) {		
			if (count($response->body->items) == 0) return false;
			return $response->body->items[0]->id;
		}
		
		return false;
	}
	
	public static function setPublicPermissionForFusionTable($file_id) {
		$data = ['type' => 'anyone', 'role' => 'reader'];
		
		$url = 'https://www.googleapis.com/drive/v2/files/'.$file_id.'/permissions';
		$response = Request::post($url)
					->sendsJson()
					->addHeaders(['Authorization'=>'Bearer '.self::getGDriveOAuthAccessToken()])
					->body(json_encode($data))
					->send();
		
		if ($response->code == Constant::STATUS_UNAUTHORIZED) {
			$access_token = self::refreshGDriveOAuthAccessToken();
			$response = Request::post($url)
						->sendsJson()
						->addHeaders(['Authorization'=>'Bearer '.$access_token])
						->body(json_encode($data))
						->send();
		}
		
		if ($response->code == Constant::STATUS_SUCCESS)
			return true;
		
		return false;
	}
}