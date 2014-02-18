<?php

use Httpful\Request;

class GoogleFusionTable {
	
	private static function getGFusionOAuthAccessToken() {
		Redis::connection();
		Redis::auth('78d257f1df9e8afc9d503e4b523ccbab');
		$access_token = Redis::get('gfusion_access_token');
		return $access_token;
		//return 'ya29.1.AADtN_XKOVOHEnoaN7Vzw5nX7XibHSa1IrsMVjFuRH_XjfhE7uQHQRStc3ug8BuOqKnx2w';
	}

	private static function setGFusionOAuthAccessToken($val) {
		Redis::connection();
		Redis::auth('78d257f1df9e8afc9d503e4b523ccbab');
		Redis::set('gfusion_access_token', $val);
	}
	
	private static function refreshGFusionOAuthAccessToken() {
		$refresh_req_data = http_build_query(['client_id' => '152751724162-quq2loao55dns7j693ce23tq683uajf7',
				'client_secret' => 'mVK5992bvMNlwA1w8o1Q6y1N',
				'refresh_token' => self::getGFusionOAuthRefreshToken(),
				'grant_type' => 'refresh_token']);
		$response = Request::post('https://accounts.google.com/o/oauth2/token')
					->sends('application/x-www-form-urlencoded')
					->body($refresh_req_data)
					->send();
		$access_token = $response->body->access_token;
		self::setGFusionOAuthAccessToken($access_token);
		
		return $access_token;
	}
	
	private static function getGFusionOAuthRefreshToken() {
		/*
		Redis::connection();
		Redis::auth('78d257f1df9e8afc9d503e4b523ccbab');
		$refresh_token = Redis::get('gfusion_refresh_token');
		return $refresh_token;*/
		return '1/zuRSQC5Q12yBoJ1idPljEw4xlolOWXrp4hyoKSC1C2o';
	}
	
	public static function create($name, $column_list) {	
		$data = ['name'=>$name, 'columns'=>$column_list, 'isExportable'=>true];
		$response = Request::post('https://www.googleapis.com/fusiontables/v1/tables')
					->sendsJson()
					->addHeaders(['Authorization'=>'Bearer '.self::getGFusionOAuthAccessToken()])
					->body(json_encode($data))
					->send();
		
		if ($response->code == 401) {
			$access_token = self::refreshGFusionOAuthAccessToken();
			// Reupload data
			$response = Request::post('https://www.googleapis.com/fusiontables/v1/tables')
						->sendsJson()
						->addHeaders(['Authorization'=>'Bearer '.$access_token])
						->body(json_encode($data))
						->send();
		}
		
		if ($response->code == 200) {
			GoogleDrive::setPublicPermissionForFusionTable(GoogleDrive::getFileIDForFusionTable($name));
			return $response->body->tableId;
		}
		
		return false;
	}
	
	public static function retrieve($gf_table_id) {
		$access_token = self::getGFusionOAuthAccessToken();
		
		/*= Get general info =*/
		$returnArr = [];
		$response1 = Request::get('https://www.googleapis.com/fusiontables/v1/tables/'.$gf_table_id)
							->addHeaders(['Authorization'=>'Bearer '.$access_token])
							->send();
		
		if ($response1->code == 200)
			$returnArr['fusionProps'] = $response1->body;
		else if ($response1->code == 401) {
			$access_token = self::refreshGFusionOAuthAccessToken();
			$response1 = Request::get('https://www.googleapis.com/fusiontables/v1/tables/'.$gf_table_id)
							->addHeaders(['Authorization'=>'Bearer '.$access_token])
							->send();
			$returnArr['fusionProps'] = $response1->body;
		} else return false;
		
		/*= Get data body =*/
		$sql = urlencode('SELECT * FROM '.$gf_table_id);
		$response2 = Request::get('https://www.googleapis.com/fusiontables/v1/query?sql='.$sql)
							->addHeaders(['Authorization'=>'Bearer '.$access_token])
							->send();
		
		if ($response2->code == 200)
			$returnArr['fusionData'] = $response2->body;
		else if ($response1->code == 401) {
			$access_token = self::refreshGFusionOAuthAccessToken();
			$response2 = Request::get('https://www.googleapis.com/fusiontables/v1/query?sql='.$sql)
							->addHeaders(['Authorization'=>'Bearer '.$access_token])
							->send();
			$returnArr['fusionData'] = $response2->body;
		} else return false;
		
		return $returnArr;
	}
}