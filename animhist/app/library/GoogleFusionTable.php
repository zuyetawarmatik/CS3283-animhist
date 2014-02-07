<?php

class GoogleFusionTable {
	
	public static function getGFusionOAuthAccessToken() {
		Redis::connection();
		Redis::auth('78d257f1df9e8afc9d503e4b523ccbab');
		$access_token = Redis::get('gfusion_access_token');
		return $access_token;
		//return 'ya29.1.AADtN_XKOVOHEnoaN7Vzw5nX7XibHSa1IrsMVjFuRH_XjfhE7uQHQRStc3ug8BuOqKnx2w';
	}

	public static function updateGFusionOAuthAccessToken($val) {
		Redis::connection();
		Redis::auth('78d257f1df9e8afc9d503e4b523ccbab');
		Redis::set('gfusion_access_token', $val);
	}
	
	public static function getGFusionOAuthRefreshToken() {
		/*
		Redis::connection();
		Redis::auth('78d257f1df9e8afc9d503e4b523ccbab');
		$refresh_token = Redis::get('gfusion_refresh_token');
		return $refresh_token;*/
		return '1/zuRSQC5Q12yBoJ1idPljEw4xlolOWXrp4hyoKSC1C2o';
	}
	
	public static function create($name, $column_list) {	
		$data = ['name'=>$name, 'columns'=>$column_list, 'isExportable'=>true];
		$response = Unirest::post('https://www.googleapis.com/fusiontables/v1/tables', ['Authorization'=>'Bearer '.self::getGFusionOAuthAccessToken(), 'Content-Type'=>'application/json'], json_encode($data));
		
		$status_code = $response->code;
		
		if ($status_code == 401) {
			// Refresh token
			$refresh_req_data = http_build_query(['client_id' => '152751724162-quq2loao55dns7j693ce23tq683uajf7',
  												'client_secret' => 'mVK5992bvMNlwA1w8o1Q6y1N',
  												'refresh_token' => self::getGFusionOAuthRefreshToken(),
  												'grant_type' => 'refresh_token']);
			$response = Unirest::post('https://accounts.google.com/o/oauth2/token', ['Content-Type'=>'application/x-www-form-urlencoded'], $refresh_req_data);
			$access_token = json_decode($response->raw_body, true)['access_token'];
			self::updateGFusionOAuthAccessToken($access_token);
			
			// Reupload data
			$response = Unirest::post('https://www.googleapis.com/fusiontables/v1/tables', ['Authorization'=>'Bearer '.$access_token, 'Content-Type'=>'application/json'], $data);
		} else if ($status_code == 400) {
			return false;
		}
		
		return json_decode($response->raw_body, true)['tableId'];
	}

}