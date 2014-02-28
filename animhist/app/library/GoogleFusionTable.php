<?php

use Httpful\Request;

class GoogleFusionTable {
	
	protected $gf_table_id;
	
	public function __construct($gf_table_id) {
		$this->gf_table_id = $gf_table_id;
	}
	
	public function retrieveGFusionAll() {
		$returnArr = [];
	
		$gfusionProps = $this->retrieveGFusionProperties();
		if (!$gfusionProps) return false;
		$returnArr["gfusionProps"] = $gfusionProps;
	
		$gfusionData = $this->retrieveGFusionData();
		if (!$gfusionData) return false;
		$returnArr["gfusionData"] = $gfusionData;
	
		$gfusionRowsID = $this->retrieveGFusionRowsID();
		if (!$gfusionRowsID) return false;
		$returnArr["gfusionRowsID"] = $gfusionRowsID;
	
		return $returnArr;
	}
	
	public function retrieveGFusionProperties() {
		$access_token = self::getGFusionOAuthAccessToken();
	
		$response = Request::get('https://www.googleapis.com/fusiontables/v1/tables/'.$this->gf_table_id)
		->addHeaders(['Authorization'=>'Bearer '.$access_token])
		->send();
	
		if ($response->code == 401) {
			$access_token = self::refreshGFusionOAuthAccessToken();
			$response = Request::get('https://www.googleapis.com/fusiontables/v1/tables/'.$this->gf_table_id)
			->addHeaders(['Authorization'=>'Bearer '.$access_token])
			->send();
		}
	
		if ($response->code == 200)
			return $response->body;
	
		return false;
	}
	
	public function retrieveGFusionData() {
		$sql = 'SELECT * FROM '.$this->gf_table_id." ORDER BY 'CreatedAt'";
		return self::sendSQLToGFusion($sql, 'get');
	}
	
	public function retrieveGFusionRowsID() {
		$sql = "SELECT ROWID, 'CreatedAt' FROM ".$this->gf_table_id." ORDER BY 'CreatedAt'";
		return self::sendSQLToGFusion($sql, 'get');
	}
	
	public function retrieveGFusionTimeline() {
		$sql = "SELECT MilestoneRep, Count() FROM ".$this->gf_table_id." GROUP BY MilestoneRep";
		return self::sendSQLToGFusion($sql, 'get');
	}
	
	public function updateRow($row_id, $arr) {
		if (count($arr) == 0) return true;
	
		$setStr = ' SET ';
		foreach ($arr as $key=>$val) {
			$str = "'".$key."' = '".$val."',";
			$setStr .= $str;
		}
		$setStr = substr($setStr, 0, -1);
	
		$sql = 'UPDATE '.$this->gf_table_id . $setStr." WHERE ROWID = '".$row_id."'";
		return self::sendSQLToGFusion($sql, 'post');
	}
	
	public function insertRow($arr) {
		if (count($arr) == 0) return true;
	
		$colStr = '('; $valStr = '(';
		foreach ($arr as $key=>$val) {
			$colStr .= "'".$key."',";
			$valStr .= "'".$val."',";
		}
		$colStr = substr($colStr, 0, -1); $valStr = substr($valStr, 0, -1);
		$colStr .= ')'; $valStr .= ')';
	
		$sql = 'INSERT INTO '.$this->gf_table_id.' '.$colStr.' VALUES '.$valStr;
		return self::sendSQLToGFusion($sql, 'post');
	}
	
	public function deleteRows($rows_arr) {
		if (count($rows_arr) == 0) return true;
	
		$rowsID = $this->retrieveGFusionRowsID();
		$rowsCount = count($rowsID->rows);
		if (count($rows_arr) == $rowsCount) {
			$sql = "DELETE FROM ".$this->gf_table_id;
			self::sendSQLToGFusion($sql, 'post');
		} else {
			foreach ($rows_arr as $row_id) {
				$sql = "DELETE FROM ".$this->gf_table_id." WHERE ROWID = '".$row_id."';";
				self::sendSQLToGFusion($sql, 'post');
			}
		}
		return true;
	}
	
	public function updateAllRowsMilestoneRep($datetime_format_str) {
		$rowsIDObj = $this->retrieveGFusionRowsID();
		$rowsID = $rowsIDObj->rows;
	
		$rowsDataObj = $this->retrieveGFusionData();
		$rowsData = $rowsDataObj->rows;
	
		$milestone_col_id = 2;
	
		for ($i = 0; $i < count($rowsID); $i++) {
			$milestone = $rowsData[$i][$milestone_col_id];
			$datetime = new DateTime(VisualizationController::prepareProperDateTime($milestone));
			$new_milestone_rep = $datetime->format($datetime_format_str);
				
			$this->updateRow($rowsID[$i][0], ['MilestoneRep'=>$new_milestone_rep]);
		}
		return true;
	}
	
	public function insertColumn($col_name, $col_type) {
		if (empty($col_name)) return true;
		if (empty($col_type)) $col_type = 'STRING';
	
		$access_token = self::getGFusionOAuthAccessToken();
	
		$data = ['name'=>$col_name, 'type'=>$col_type];
	
		$response = Request::post('https://www.googleapis.com/fusiontables/v1/tables/'.$this->gf_table_id.'/columns')
		->sendsJson()
		->addHeaders(['Authorization'=>'Bearer '.$access_token])
		->body(json_encode($data))
		->send();
	
		if ($response->code == 401) {
			$access_token = self::refreshGFusionOAuthAccessToken();
			$response = Request::post('https://www.googleapis.com/fusiontables/v1/tables/'.$this->gf_table_id.'/columns')
			->sendsJson()
			->addHeaders(['Authorization'=>'Bearer '.$access_token])
			->body(json_encode($data))
			->send();
		}
	
		if ($response->code == 200)
			return true;
	
		return false;
	}
	
	public function updateColumn($col_id, $col_name, $col_type) {
		if (empty($col_name)) return true;
		if (empty($col_type)) $col_type = 'STRING';
	
		$access_token = self::getGFusionOAuthAccessToken();
	
		$data = ['name'=>$col_name, 'type'=>$col_type];
	
		$response = Request::put('https://www.googleapis.com/fusiontables/v1/tables/'.$this->gf_table_id.'/columns/'.$col_id)
		->sendsJson()
		->addHeaders(['Authorization'=>'Bearer '.$access_token])
		->body(json_encode($data))
		->send();
	
		if ($response->code == 401) {
			$access_token = self::refreshGFusionOAuthAccessToken();
			$response = Request::put('https://www.googleapis.com/fusiontables/v1/tables/'.$this->gf_table_id.'/columns/'.$col_id)
			->sendsJson()
			->addHeaders(['Authorization'=>'Bearer '.$access_token])
			->body(json_encode($data))
			->send();
		}
	
		if ($response->code == 200)
			return true;
	
		return false;
	}
	
	public function deleteColumn($col_id) {
		$access_token = self::getGFusionOAuthAccessToken();
	
		$response = Request::delete('https://www.googleapis.com/fusiontables/v1/tables/'.$this->gf_table_id.'/columns/'.$col_id)
		->addHeaders(['Authorization'=>'Bearer '.$access_token])
		->send();
	
		if ($response->code == 401) {
			$access_token = self::refreshGFusionOAuthAccessToken();
			$response = Request::delete('https://www.googleapis.com/fusiontables/v1/tables/'.$this->gf_table_id.'/columns/'.$col_id)
			->addHeaders(['Authorization'=>'Bearer '.$access_token])
			->send();
		}
	
		if ($response->code == 204)
			return true;
	
		return false;
	}
	
	private static function sendSQLToGFusion($sql, $method) {
		$access_token = self::getGFusionOAuthAccessToken();
		$encoded_sql = urlencode($sql);
	
		$response = Request::$method('https://www.googleapis.com/fusiontables/v1/query?sql='.$encoded_sql)
		->addHeaders(['Authorization'=>'Bearer '.$access_token])
		->send();
	
		if ($response->code == 401) {
			$access_token = self::refreshGFusionOAuthAccessToken();
			$response = Request::$method('https://www.googleapis.com/fusiontables/v1/query?sql='.$encoded_sql)
			->addHeaders(['Authorization'=>'Bearer '.$access_token])
			->send();
		}
	
		if ($response->code >= 200 && $response->code < 300)
			return $response->body;
	
		return false;
	}
	
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
}