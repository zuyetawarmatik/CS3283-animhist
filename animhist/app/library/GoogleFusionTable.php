<?php

use Httpful\Request;

class GoogleFusionTable {
	
	private static function getGFusionOAuthAccessToken() {
		if (Session::has('gfusion_access_token'))
			return Session::get('gfusion_access_token');
		else {
			Redis::connection();
			Redis::auth('78d257f1df9e8afc9d503e4b523ccbab');
			$access_token = Redis::get('gfusion_access_token');
			Session::set('gfusion_access_token', $access_token);
			return $access_token;
		}
		//return 'ya29.1.AADtN_XKOVOHEnoaN7Vzw5nX7XibHSa1IrsMVjFuRH_XjfhE7uQHQRStc3ug8BuOqKnx2w';
	}

	private static function setGFusionOAuthAccessToken($val) {
		Redis::connection();
		Redis::auth('78d257f1df9e8afc9d503e4b523ccbab');
		Redis::set('gfusion_access_token', $val);
		Session::set('gfusion_access_token', $val);
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
	
	public static function retrieveGFusionAll($gf_table_id) {
		$returnArr = [];

		$gfusionProps = self::retrieveGFusionProperties($gf_table_id);
		if (!$gfusionProps) return false;		
		$returnArr["gfusionProps"] = $gfusionProps;
		
		$gfusionData = self::retrieveGFusionData($gf_table_id);
		if (!$gfusionData) return false;
		$returnArr["gfusionData"] = $gfusionData;
		
		$gfusionRowsID = self::retrieveGFusionRowsID($gf_table_id);
		if (!$gfusionRowsID) return false;
		$returnArr["gfusionRowsID"] = $gfusionRowsID;
		
		return $returnArr;
	}
	
	public static function retrieveGFusionProperties($gf_table_id) {
		$access_token = self::getGFusionOAuthAccessToken();
		
		$response = Request::get('https://www.googleapis.com/fusiontables/v1/tables/'.$gf_table_id)
					->addHeaders(['Authorization'=>'Bearer '.$access_token])
					->send();
		
		if ($response->code == 200)
			return $response->body;
		else if ($response->code == 401) {
			$access_token = self::refreshGFusionOAuthAccessToken();
			$response = Request::get('https://www.googleapis.com/fusiontables/v1/tables/'.$gf_table_id)
							->addHeaders(['Authorization'=>'Bearer '.$access_token])
							->send();
			return $response->body;
		} else return false;
	}
	
	public static function retrieveGFusionData($gf_table_id) {
		$sql = 'SELECT * FROM '.$gf_table_id;
		return self::sendSQLToGFusion($sql, 'get');
	}
	
	public static function retrieveGFusionRowsID($gf_table_id) {
		$sql = 'SELECT ROWID FROM '.$gf_table_id;
		return self::sendSQLToGFusion($sql, 'get');
	}

	public static function updateRow($gf_table_id, $row_id, $arr) {
		if (count($arr) == 0) return true;

		$setStr = ' SET ';
		foreach ($arr as $key=>$val) {
			$str = "'".$key."' = '".$val."',";
			$setStr .= $str;
		}
		$setStr = substr($setStr, 0, -1);

		$sql = 'UPDATE '.$gf_table_id . $setStr." WHERE ROWID = '".$row_id."'";
		return self::sendSQLToGFusion($sql, 'post');
	}
	
	public static function insertRow($gf_table_id, $arr) {
		if (count($arr) == 0) return true;
		
		$colStr = '('; $valStr = '(';
		foreach ($arr as $key=>$val) {
			$colStr .= "'".$key."',";
			$valStr .= "'".$val."',";
		}
		$colStr = substr($colStr, 0, -1); $valStr = substr($valStr, 0, -1);
		$colStr .= ')'; $valStr .= ')';
		
		$sql = 'INSERT INTO '.$gf_table_id.' '.$colStr.' VALUES '.$valStr;
		return self::sendSQLToGFusion($sql, 'post');
	}
	
	private static function sendSQLToGFusion($sql, $method) {
		$access_token = self::getGFusionOAuthAccessToken();
		$encoded_sql = urlencode($sql);
		
		$response = Request::$method('https://www.googleapis.com/fusiontables/v1/query?sql='.$encoded_sql)
						->addHeaders(['Authorization'=>'Bearer '.$access_token])
						->send();
		
		if ($response->code == 200)
			return $response->body;
		else if ($response->code == 401) {
			$access_token = self::refreshGFusionOAuthAccessToken();
			$response = Request::$method('https://www.googleapis.com/fusiontables/v1/query?sql='.$encoded_sql)
							->addHeaders(['Authorization'=>'Bearer '.$access_token])
							->send();
			return $response->body;
		} else return false;
	}
}