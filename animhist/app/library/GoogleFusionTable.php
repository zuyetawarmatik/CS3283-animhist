<?php

use Httpful\Request;

class GoogleFusionTable {
	
	protected $gf_table_id, $visualization_type;
	
	public function __construct($gf_table_id, $visualization_type) {
		$this->gf_table_id = $gf_table_id;
		$this->visualization_type = $visualization_type;
	}
	
	public function retrieveGFusionAllData() {
		$return_arr = [];
	
		$gfusion_props = $this->retrieveGFusionProperties();
		if (!$gfusion_props) return false;
		$return_arr["gfusionProps"] = $gfusion_props;
	
		$gfusion_data = $this->retrieveGFusionData();
		if (!$gfusion_data) return false;
		$return_arr["gfusionData"] = $gfusion_data;
	
		$gfusion_rows_ID = $this->retrieveGFusionRowsID();
		if (!$gfusion_rows_ID) return false;
		$return_arr["gfusionRowsID"] = $gfusion_rows_ID;
	
		return $return_arr;
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
	
	public function retrieveGFusionColumns() {
		return $this->retrieveGFusionProperties()->columns;
	}
	
	public function retrieveGFusionData() {
		$sql = 'SELECT * FROM '.$this->gf_table_id." ORDER BY CreatedAt";
		return self::sendSQLToGFusion($sql, 'get');
	}
	
	public function retrieveGFusionRowsID() {
		$sql = "SELECT ROWID, CreatedAt FROM ".$this->gf_table_id." ORDER BY CreatedAt";
		return self::sendSQLToGFusion($sql, 'get');
	}
	
	public function retrieveGFusionTimeline() {
		$sql = "SELECT MilestoneRep, Count() FROM ".$this->gf_table_id." GROUP BY MilestoneRep";
		$ret_gf = self::sendSQLToGFusion($sql, 'get');
		if (!$ret_gf) return false;
		
		$ret = [];
		if (property_exists($ret_gf, 'rows'))	
			foreach ($ret_gf->rows as $row) {
				$ret[] = $row[0];
			}
		
		return $ret;
	}
	
	public function getRow($row_id) {
		$sql = 'SELECT * FROM '.$this->gf_table_id." WHERE ROWID = '".$row_id."'";
		return self::sendSQLToGFusion($sql, 'get');
	}
	
	public function updateRow($row_id, $arr) {
		if (count($arr) == 0) return true;
	
		$set_str = ' SET ';
		foreach ($arr as $key=>$val) {
			$str = "'".$key."' = '".$val."',";
			$set_str .= $str;
		}
		$set_str = substr($set_str, 0, -1);
	
		$sql = 'UPDATE '.$this->gf_table_id . $set_str." WHERE ROWID = '".$row_id."'";
		return self::sendSQLToGFusion($sql, 'post');
	}
	
	public function insertRow($arr) {
		if (count($arr) == 0) return true;
	
		$col_str = '('; $val_str = '(';
		foreach ($arr as $key=>$val) {
			$col_str .= "'".$key."',";
			$val_str .= "'".$val."',";
		}
		$col_str = substr($col_str, 0, -1); $val_str = substr($val_str, 0, -1);
		$col_str .= ')'; $val_str .= ')';
	
		$sql = 'INSERT INTO '.$this->gf_table_id.' '.$col_str.' VALUES '.$val_str;
		return self::sendSQLToGFusion($sql, 'post');
	}
	
	public function deleteRows($rows_arr) {
		if (count($rows_arr) == 0) return true;
	
		$rows_ID = $this->retrieveGFusionRowsID();
		$rows_count = count($rows_ID->rows);
		if (count($rows_arr) == $rows_count) {
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
		$rows_ID_obj = $this->retrieveGFusionRowsID();
		$rows_ID = $rows_ID_obj->rows;
	
		$rows_data_obj = $this->retrieveGFusionData();
		$rows_data = $rows_data_obj->rows;
	
		for ($i = 0; $i < count($rows_ID); $i++) {
			$milestone = $rows_data[$i][Constant::COL_ID_MILESTONE];
			$datetime = new DateTime($milestone);
			$new_milestone_rep = $datetime->format($datetime_format_str);
			
			$this->updateRow($rows_ID[$i][0], ['MilestoneRep'=>$new_milestone_rep]);
		}
		return true;
	}
	
	public function getColumn($col_id) {
		$columns = $this->retrieveGFusionProperties()->columns;
		foreach ($columns as $column) {
			if ($column->columnId == $col_id) {
				return $column;
			}
		}	
		return false;
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
	
		if ($response->code == 200) {
			if ($col_type == 'NUMBER')
				$this->createColumnDefaultStyle($col_name);
			return true;
		}
	
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
	
		if ($response->code == 200) {
			if ($col_type == 'STRING') {				
				$this->deleteColumnStyle($col_name);
			} else { // Changed to NUMBER type
				$style = $this->getColumnStyle($col_name);
				if (!$style)
					$this->createColumnDefaultStyle($col_name);
			}
			return true;
		}
		
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
		
		if ($response->code == 204) {
			$this->deleteColumnStyle('#INVALID_COLUMN');
			return true;
		}
	
		return false;
	}
	
	// TODO : private
	public function retrieveGFusionStyles() {
		$access_token = self::getGFusionOAuthAccessToken();
		
		$response = Request::get('https://www.googleapis.com/fusiontables/v1/tables/'.$this->gf_table_id.'/styles')
		->addHeaders(['Authorization'=>'Bearer '.$access_token])
		->send();
		
		if ($response->code == 401) {
			$access_token = self::refreshGFusionOAuthAccessToken();
			$response = Request::get('https://www.googleapis.com/fusiontables/v1/tables/'.$this->gf_table_id.'/styles')
			->addHeaders(['Authorization'=>'Bearer '.$access_token])
			->send();
		}
		
		if ($response->code == 200)
			return $response->body;
	
		return false;
	}
	
	public function getColumnStyle($col_name) {
		$styles = $this->retrieveGFusionStyles();
		
		for ($i = 0; $i < $styles->totalItems; $i++) {
			$style_item = $styles->items[$i]; 
			
			if ($this->visualization_type == 'point') {
				if (property_exists($style_item, 'markerOptions'))
					if (property_exists($style_item->markerOptions, 'iconStyler') && $style_item->markerOptions->iconStyler->columnName == $col_name)
						return $style_item;
			} else if ($this->visualization_type == 'polygon') {
				if (property_exists($style_item, 'polygonOptions'))
					if (property_exists($style_item->polygonOptions, 'fillColorStyler') && $style_item->polygonOptions->fillColorStyler->columnName == $col_name ||
						property_exists($style_item->polygonOptions, 'strokeColorStyler') && $style_item->polygonOptions->strokeColorStyler->columnName == $col_name ||
						property_exists($style_item->polygonOptions, 'strokeWeightStyler') && $style_item->polygonOptions->strokeWeightStyler->columnName == $col_name)
						return $style_item;
			}
		}
		
		return false;
	}

	// Only for NUMBER column
	// Only use bucket styling
	public function createColumnDefaultStyle($col_name) {
		$style = [];
		
		$colors = ['#75d6ff', '#008abd', '#0a719c', '#004a69', '#001721'];
		
		if ($this->visualization_type == 'point') {
			$style['markerOptions']['iconStyler']['columnName'] = $col_name;
			
			$icons = ['measle_brown', 'small_red', 'small_purple', 'small_yellow', 'small_green'];
			
			$style['markerOptions']['iconStyler']['buckets'] = [];
			for ($i = 0; $i < 5; $i++) {
				$style['markerOptions']['iconStyler']['buckets'][$i]['icon'] = $icons[$i];
				//$style['markerOptions']['iconStyler']['buckets'][$i]['color'] = $colors[$i];
				$style['markerOptions']['iconStyler']['buckets'][$i]['min'] = $i * 10;
				$style['markerOptions']['iconStyler']['buckets'][$i]['max'] = ($i + 1) * 10;
			}
			
		} else if ($this->visualization_type == 'polygon') {
			$style['polygonOptions']['fillColorStyler']['columnName'] = $col_name;
			
			$style['polygonOptions']['fillColorStyler']['buckets'] = [];
			for ($i = 0; $i < 5; $i++) {
				$style['polygonOptions']['fillColorStyler']['buckets'][$i]['color'] = $colors[$i];
				$style['polygonOptions']['fillColorStyler']['buckets'][$i]['min'] = $i * 10;
				$style['polygonOptions']['fillColorStyler']['buckets'][$i]['max'] = ($i + 1) * 10;
				$style['polygonOptions']['fillColorStyler']['buckets'][$i]['opacity'] = 0.5;
			}
		}
		
		$access_token = self::getGFusionOAuthAccessToken();
		
		$response = Request::post('https://www.googleapis.com/fusiontables/v1/tables/'.$this->gf_table_id.'/styles')
		->addHeaders(['Authorization'=>'Bearer '.$access_token])
		->sendsJson()
		->body(json_encode($style))
		->send();

		if ($response->code == 401) {
			$access_token = self::refreshGFusionOAuthAccessToken();
			$response = Request::post('https://www.googleapis.com/fusiontables/v1/tables/'.$this->gf_table_id.'/styles')
			->addHeaders(['Authorization'=>'Bearer '.$access_token])
			->sendsJson()
			->body(json_encode($style))
			->send();
		}
		
		if ($response->code == 200)
			return $response->body;
		
		return false;
	}
	
	public function updateColumnStyle($col_name, $style) {
		$old_style = $this->getColumnStyle($col_name);
		
		if ($old_style) {
			$style_id = $old_style->styleId;
			
			$access_token = self::getGFusionOAuthAccessToken();
			
			$response = Request::put('https://www.googleapis.com/fusiontables/v1/tables/'.$this->gf_table_id.'/styles/'.$style_id)
			->addHeaders(['Authorization'=>'Bearer '.$access_token])
			->sendsJson()
			->body(json_encode($style))
			->send();
			
			if ($response->code == 401) {
				$access_token = self::refreshGFusionOAuthAccessToken();
				$response = Request::put('https://www.googleapis.com/fusiontables/v1/tables/'.$this->gf_table_id.'/styles/'.$style_id)
				->addHeaders(['Authorization'=>'Bearer '.$access_token])
				->sendsJson()
				->body(json_encode($style))
				->send();
			}
			
			if ($response->code == 200)
				return $response->body;
		}
		
		return false;
	}
	
	public function deleteColumnStyle($col_name) {
		$style = $this->getColumnStyle($col_name);
		if ($style) {
			$style_id = $style->styleId;
			
			$access_token = self::getGFusionOAuthAccessToken();
			
			$response = Request::delete('https://www.googleapis.com/fusiontables/v1/tables/'.$this->gf_table_id.'/styles/'.$style_id)
			->addHeaders(['Authorization'=>'Bearer '.$access_token])
			->send();
			
			if ($response->code == 401) {
				$access_token = self::refreshGFusionOAuthAccessToken();
				$response = Request::delete('https://www.googleapis.com/fusiontables/v1/tables/'.$this->gf_table_id.'/styles/'.$style_id)
				->addHeaders(['Authorization'=>'Bearer '.$access_token])
				->send();
			}
			
			if ($response->code == 204)
				return true;
		} 
		
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
	
	public static function create($name, $type, $column_list) {	
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
			
			// Create default style for every NUMBER column
			$gft = new GoogleFusionTable($response->body->tableId, $type);
			foreach ($column_list as $column) {
				if ($column['type'] == 'NUMBER')
					$gft->createColumnDefaultStyle($column['name']);
			}
			
			return $response->body->tableId;
		}
		
		return false;
	}
}