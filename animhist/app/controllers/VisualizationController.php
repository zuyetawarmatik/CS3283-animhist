<?php

class VisualizationController extends \BaseController {
	
	public function showCreate($username)
	{
		if (Auth::user()->username == $username) {
			if (Input::get('step') == 2) {
				if (Input::has('vi_id')) {
					$vi_id = Input::get('vi_id');
					$visualization = Visualization::find($vi_id);
					if (!$visualization || !$visualization->user->isAuthUser() || $visualization->published) goto fail;
					if (Input::get('ajax')) {
						return ViewResponseUtility::makeSubView('create-visualization-step-2', 'New Visualization: <span style="font-weight:300">Step 2 (Edit And Publish The Visualization)</span>', ['visualization'=>$visualization]);
					} else {
						return ViewResponseUtility::makeBaseView(URL::route('visualization.showCreate', $username), Constant::SIDEBAR_MYVISUALIZATION, [], ['step'=>2, 'vi_id'=>$vi_id]);
					}
				} else goto fail;
			} else {
				if (Input::get('ajax')) {
					return ViewResponseUtility::makeSubView('create-visualization-step-1', 'New Visualization: <span style="font-weight:300">Step 1 (Start A New Visualization)</span>');
				} else {
					return ViewResponseUtility::makeBaseView(URL::route('visualization.showCreate', $username), Constant::SIDEBAR_MYVISUALIZATION);
				}	
			}
		}
		
		fail: return Redirect::route('user.show', $username);
	}

	public function store($username)
	{
		if (Auth::user()->username == $username) {
			$rules = array(
					'display-name'		=> 'required|unique:visualizations,display_name,NULL,id,user_id,'.Auth::user()->id,
					'type'      		=> 'required',
			);
			$validator = Validator::make(Input::all(), $rules);
			if ($validator->fails())
				return JSONResponseUtility::ValidationError($validator->getMessageBag()->toArray());
			
			$visualization = new Visualization();
			$visualization->display_name = Input::get('display-name');
			$visualization->user_id = Auth::user()->id;
			$visualization->type = Input::get('type');
			$visualization->description = Input::get('description');
			$visualization->category = Input::get('category');
			$visualization->published = false;
			
			$column_list = json_decode(Input::get('column-list'), true);
			
			foreach ($column_list as $column) {
				if ($column['caption'] == 'Milestone') {
					$visualization->milestone_format = $column['type-caption'];
					break;
				}
			}
			
			$visualization->zoom = 3;
			$visualization->center_latitude = 0.00;
			$visualization->center_longitude = 0.00;
			
			$visualization_name = $username.'_'.$visualization->display_name;
			$gf_column_list = self::prepareColumnListSentToGFusion($column_list, Input::get('type'));
			$fusion_table_id = GoogleFusionTable::create($visualization_name, Input::get('type'), $gf_column_list);
			
			if ($fusion_table_id) {
				$visualization->fusion_table_id = $fusion_table_id;
				foreach ($gf_column_list as $gf_column) {
					if ($gf_column['type'] == 'NUMBER') {
						$visualization->default_column = $gf_column['name'];
						break;
					}
				}
				
				$visualization->save();
				
				return JSONResponseUtility::Redirect(URL::route('visualization.showCreate', $username).'?step=2&vi_id='.$visualization->id, false);
			} else
				return Response::make('', 400);
		} else {
			return Response::make('', 401);
		}
	}

	public function show($id)
	{
		
	}

	public function showEdit($id)
	{
		
	}

	public function updateProperty($username, $id)
	{
		if (Auth::user()->username == $username) {
			$rules = [];
			if (Input::has('display-name'))
				$rules['display-name'] = 'required|unique:visualizations,display_name,NULL,id,user_id,'.Auth::user()->id;
			if (Input::has('zoom'))
				$rules['zoom'] = 'required|numeric';
			if (Input::has('center-latitude'))
				$rules['center-latitude'] = 'required|numeric';
			if (Input::has('center-longitude'))
				$rules['center-longitude'] = 'required|numeric';
			if (Input::has('category'))
				$rules['category'] = 'required';
				
			$validator = Validator::make(Input::all(), $rules);
			if ($validator->fails())
				return JSONResponseUtility::ValidationError($validator->getMessageBag()->toArray());
		
			$visualization = Visualization::find($id);
			if (!$visualization || !$visualization->user->isAuthUser()) goto fail;
			
			if (Input::has('description')) {
				$desc = Input::get('description') == 'NUL' ? null : Input::get('description'); 
				$visualization->description = $desc;
			}
			if (Input::has('default-column'))
				$visualization->default_column = Input::get('default-column');
			if (Input::has('display-name'))
				$visualization->display_name = Input::get('display-name');
			if (Input::has('category'))
				$visualization->category = Input::get('category');
			if (Input::has('zoom'))
				$visualization->zoom = number_format(Input::get('zoom'));
			if (Input::has('center-latitude'))
				$visualization->center_latitude = number_format(Input::get('center-latitude'), 2);
			if (Input::has('center-longitude'))
				$visualization->center_longitude = number_format(Input::get('center-longitude'), 2);
			if (Input::has('published'))
				$visualization->published = Input::get('published') == 'true' ? true : false;
			
			$visualization->save();
			
			$json = [];
			
			if (Input::has('description'))
				$json['description'] = $visualization->description;
			if (Input::has('default-column'))
				$json['defaultColumn'] = $visualization->default_column;
			if (Input::has('display-name'))
				$json['displayName'] = $visualization->display_name;
			if (Input::has('category'))
				$json['category'] = $visualization->category;
			if (Input::has('zoom'))
				$json['zoom'] = number_format($visualization->zoom);
			if (Input::has('center-latitude'))
				$json['centerLatitude'] = number_format($visualization->center_latitude, 2);
			if (Input::has('center-longitude'))
				$json['centerLongitude'] = number_format($visualization->center_longitude, 2);
			if (Input::has('published'))
				$json['published'] = $visualization->published;
				// TODO: return to view vis page
			
			return Response::JSON($json);
		} else {
			return Response::make('', 401);
		}
		
		fail: return Response::make('', 400);
	}
	
	/* Require JSON request */
	public function updateTable($username, $id)
	{
		if (Auth::user()->username == $username) {
			if (!Input::isJson()) goto fail;
			
			$visualization = Visualization::find($id);
			if (!$visualization || !$visualization->user->isAuthUser()) goto fail;
			$gft = new GoogleFusionTable($visualization->fusion_table_id, $visualization->type);
			
			$datetime_format_str = $visualization->getMilestoneFormatString();
			
			$row_id = Input::json('row');
			$col_id = Input::json('col');
			$col_name = Input::json('colName');
			$col_type = Input::json('colType');
			$col_val_pairs = Input::json('colvalPairs');
			
			$cur_col;
			
			$result;
			switch (Input::json('type')) {
				case 'row-update':
					/* Determine Milestone and MilestoneRep */
					if (isset($col_val_pairs['Milestone'])) {
						$datetime = new DateTime($col_val_pairs['Milestone']);
						$col_val_pairs['MilestoneRep'] = $datetime->format($datetime_format_str);
					}
					
					/* Determine Geocode */
					if (isset($col_val_pairs['Position'])) {
						if (strtolower($visualization->type) == 'point') {
							$geocode = GoogleGeocoding::getLatLongForString($col_val_pairs['Position']);
							if ($geocode) $col_val_pairs['Geocode'] = $geocode;
						}
					}
					
					$result = $gft->updateRow($row_id, $col_val_pairs);
					break;
				case 'row-insert':
					/* Determine Milestone and MilestoneRep */
					$col_val_pairs['CreatedAt'] = date('Y/m/d H:i:s');
					if (!isset($col_val_pairs['Milestone']))
						$col_val_pairs['Milestone'] = '1/1/2000';
					$datetime = new DateTime($col_val_pairs['Milestone']);
					$col_val_pairs['MilestoneRep'] = $datetime->format($datetime_format_str);
					
					/* Determine Geocode */
					if (isset($col_val_pairs['Position'])) {
						if (strtolower($visualization->type) == 'point') {
							$geocode = GoogleGeocoding::getLatLongForString($col_val_pairs['Position']);
							if ($geocode) $col_val_pairs['Geocode'] = $geocode;
						}
					}
						
					$result = $gft->insertRow($col_val_pairs);
					break;
				case 'row-delete':
					$result = $gft->deleteRows($row_id);
					break;
				case 'column-update':
					if ($col_name == 'Milestone') {
						$visualization->milestone_format = $col_type;
						$visualization->save();
						$result = $gft->updateAllRowsMilestoneRep($visualization->getMilestoneFormatString());
					} else {
						$cur_col = $gft->getColumn($col_id);
						$result = $gft->updateColumn($col_id, $col_name, $col_type);
					}
					break;
				case 'column-insert':
					$result = $gft->insertColumn($col_name, $col_type);
					break;
				case 'column-delete':
					$cur_col = $gft->getColumn($col_id);
					$result = $gft->deleteColumn($col_id);
					break;
			}
			if (!$result) goto fail;
			
			switch (Input::json('type')) {
				case 'row-update':
					return Response::json($gft->getRow($row_id));
				case 'row-insert':
					$new_row_id = $result->rows[0][0];
					$result = $gft->getRow($new_row_id);
					$result->columns[] = 'ROWID';
					$result->rows[0][] = $new_row_id;
					return Response::json($result);
				case 'column-update':
					// If default column is null and this column is NUMBER
					// If the current default column changed name but kept NUMBER type
					if ($col_type == 'NUMBER'
							&& (empty($visualization->default_column) || $visualization->default_column == $cur_col->name)) {
						$visualization->default_column = $col_name;
					}
					// If the current default column changed type to STRING
					else if ($col_type == 'STRING' && $visualization->default_column == $cur_col->name) {
						$cur_columns = $gft->retrieveGFusionColumns(); // Refresh column list
						$visualization->default_column = null;
						foreach ($cur_columns as $column) {
							if ($column->type == 'NUMBER') {
								$visualization->default_column = $column->name;
								break;
							}
						}
					}
					$visualization->save();
					return Response::make('', 200);
				case 'column-insert':
					// If default column is null and this new column is NUMBER
					if (empty($visualization->default_column) && $col_type == 'NUMBER') {
						$visualization->default_column = $col_name;
						$visualization->save();
					}
					return Response::make('', 200);
				case 'column-delete':
					// Change default_column if the current default column is deleted
					if ($visualization->default_column == $cur_col->name) {
						$cur_columns = $gft->retrieveGFusionColumns(); // Refresh column list
						$visualization->default_column = null;
						foreach ($cur_columns as $column) {
							if ($column->type == 'NUMBER') {
								$visualization->default_column = $column->name;
								break;
							}
						}
						$visualization->save();
					}
					return Response::make('', 200);
				default:
					return Response::make('', 200);
			}
		} else {
			return Response::make('', 401);
		}
		
		fail: return Response::make('', 400);
	}
	
	public function updateStyle($username, $id)
	{
	
	}
	
	public function info($username, $id) {
		if (Auth::user()->username == $username) {
			
			$visualization = Visualization::find($id);
			if (!$visualization || !$visualization->user->isAuthUser()) goto fail;
			$gft = new GoogleFusionTable($visualization->fusion_table_id, $visualization->type);
			
			if (Input::get('request') == 'data') {
				$ret = $gft->retrieveGFusionAllData();
				if (!$ret) goto fail;
				return Response::json($ret);
			} else if (Input::get('request') == 'property') {
				$json = ["displayName" => $visualization->display_name,
						"username" => $visualization->user->username,
						"type" => $visualization->type,
						"description" => $visualization->description,
						"category" => $visualization->category,
						"published" => $visualization->published,
						"milestoneFormat" => $visualization->milestone_format,
						"milestones" => $visualization->milestones,
						"defaultColumn" => $visualization->default_column,
						"zoom" => number_format($visualization->zoom),
						"centerLatitude" => number_format($visualization->center_latitude, 2),
						"centerLongitude" => number_format($visualization->center_longitude, 2)];
				$gfusion_props = $gft->retrieveGFusionProperties();
				$json["htmlData"] = false;
				foreach ($gfusion_props->columns as $column) {
					if (strtolower($column->name) == 'htmldata' && $column->type == 'STRING') {
						$json["htmlData"] = true; break;
					}
				}
				$json["columnList"] = self::prepareColumnListSentToClient($gfusion_props->columns, $visualization->milestone_format);
				return Response::json($json);
			} else if  (Input::get('request') == 'timeline') {
				$ret = $gft->retrieveGFusionTimeline();
				if (!is_array($ret)) goto fail;
				return Response::json($ret);
			} else if  (Input::get('request') == 'style') {
				$ret = $gft->getColumnStyle(Input::get('column'));
				return Response::json($ret);
			} else {
				goto fail;
			}
		} else {
			return Response::make('', 401);
		}
		
		fail: return Response::make('', 400);
	}
	
	public function destroy($username, $id) {
		if (Auth::user()->username == $username) {
			$visualization = Visualization::find($id);
			if (!$visualization || !$visualization->user->isAuthUser()) goto fail;
			
			$gft = new GoogleFusionTable($visualization->fusion_table_id, $visualization->visualization_type);
			
			if ($gft->deleteTable()) {
				$visualization->delete();
				return JSONResponseUtility::Redirect(URL::route('user.show', $username), false);
			}
		} else {
			return Response::make('', 401);
		}
		
		fail: return Response::make('', 400);
	}
	
	private static function prepareColumnListSentToGFusion($input_column_list, $input_visualization_type) {
		$column_list = [['name'=>'CreatedAt', 'type'=>'DATETIME'], ['name'=>'MilestoneRep', 'type'=>'DATETIME'], ['name'=>'Geocode', 'type'=>'LOCATION'], ['name'=>'Milestone', 'type'=>'DATETIME'], ['name'=>'Position', 'type'=>'LOCATION']];
		
		foreach ($input_column_list as $input_column) {
			if ($input_column['caption'] == 'HTMLData' && $input_column['type-caption'] == 'String') {
				$column_list[] = ['name'=>'HTMLData', 'type'=>'STRING'];
				break;
			}
		}
		
		foreach ($input_column_list as $input_column) {
			if ($input_column['type-caption'] == 'Number')
				$column_list[] = ['name'=>$input_column['caption'], 'type'=>'NUMBER'];
			else if ($input_column['caption'] != 'HTMLData' && $input_column['type-caption'] == 'String')
				$column_list[] = ['name'=>$input_column['caption'], 'type'=>'STRING'];
		}
		
		return $column_list;
	}
	
	private static function prepareColumnListSentToClient($gfusion_column_list, $milestone_format) {
		$result = [];
		$result[] = ['caption'=>'Milestone', 'type-caption'=>ucfirst($milestone_format), 'editable'=>true, 'column-id'=>Constant::COL_ID_MILESTONE];
		$result[] = ['caption'=>'Position', 'type-caption'=>'Location: KML or Lat/Long or String', 'column-id'=>Constant::COL_ID_POSITION];
		
		$has_html_data = false; $html_data_col_id;
		foreach ($gfusion_column_list as $column) {
			if ($column->name == 'HTMLData' && $column->type == 'STRING') {
				$has_html_data = true;
				$html_data_col_id = $column->columnId;
				break;
			}
		}
		$html_data_item = ['caption'=>'HTMLData', 'type-caption'=>'String', 'disable'=>true, 'disabled'=>!$has_html_data];
		if ($has_html_data) $html_data_item['column-id'] = $html_data_col_id;
		$result[] = $html_data_item;
		
		foreach ($gfusion_column_list as $column) {
			if ($column->type == 'NUMBER')
				$result[] = ['caption'=>$column->name, 'type-caption'=>'Number', 'editable'=>true, 'deletable'=>true, 'column-id'=>$column->columnId];
			else if ($column->type == 'STRING' && $column->name != 'HTMLData')
				$result[] = ['caption'=>$column->name, 'type-caption'=>'String', 'editable'=>true, 'deletable'=>true, 'column-id'=>$column->columnId];
		}
		
		return $result;
	}
}