<?php

class GoogleFusionTable {
	
	public static function getOAuthAccessToken() {
		Redis::connection();
		Redis::auth('78d257f1df9e8afc9d503e4b523ccbab');
		$access_token = Redis::get('access_token');
		return $access_token;
		//return 'ya29.1.AADtN_XKOVOHEnoaN7Vzw5nX7XibHSa1IrsMVjFuRH_XjfhE7uQHQRStc3ug8BuOqKnx2w';
	}

	public static function updateOAuthAccessToken($val) {
		Redis::connection();
		Redis::auth('78d257f1df9e8afc9d503e4b523ccbab');
		Redis::set('access_token', $val);
	}
	
	public static function getOAuthRefreshToken() {
		/*
		Redis::connection();
		Redis::auth('78d257f1df9e8afc9d503e4b523ccbab');
		$refresh_token = Redis::get('refresh_token');
		return $refresh_token;*/
		return '1/zuRSQC5Q12yBoJ1idPljEw4xlolOWXrp4hyoKSC1C2o';
	}
	
	public static function create() {	
		$data = ["name"=>"Insects",
 					"columns"=>[
						  [
						   "name"=>"Species",
						   "type"=>"STRING"
						  ],
						  [
						   "name"=>"Elevation",
						   "type"=>"NUMBER"
						  ],
						  [
						   "name"=>"Year",
						   "type"=>"DATETIME"
						  ]
					],
					"description"=>"Insect Tracking Information",
					"isExportable"=>true
				];
		$response = Unirest::post('https://www.googleapis.com/fusiontables/v1/tables', ['Authorization'=>'Bearer '.self::getOAuthAccessToken(), 'Content-Type'=>'application/json'], json_encode($data));
		
		$status_code = $response->code;
		
		if ($status_code == 401) {
			// Refresh token
			$refresh_req_data = http_build_query(['client_id' => '152751724162-quq2loao55dns7j693ce23tq683uajf7',
  												'client_secret' => 'mVK5992bvMNlwA1w8o1Q6y1N',
  												'refresh_token' => self::getOAuthRefreshToken(),
  												'grant_type' => 'refresh_token']);
			$response = Unirest::post('https://accounts.google.com/o/oauth2/token', ['Content-Type'=>'application/x-www-form-urlencoded'], $refresh_req_data);
			$access_token = json_decode($response->raw_body, true)['access_token'];
			self::updateOAuthAccessToken($access_token);
			
			// Reupload data
			$response = Unirest::post('https://www.googleapis.com/fusiontables/v1/tables', ['Authorization'=>'Bearer '.$access_token, 'Content-Type'=>'application/json'], $data);
			
			return $response->raw_body;
		} else if ($status_code == 200) {
			return $response->raw_body;
		} else if ($status_code == 400) {
			return $response->raw_body;
		}
	}

}