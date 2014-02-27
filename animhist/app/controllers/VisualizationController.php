<?php

class VisualizationController extends \BaseController {
	
	public function showCreate($username)
	{
		if (Auth::user()->username == $username) {
			if (Input::get('step') == 2) {
				if (Input::has('vi_id')) {
					$vi_id = Input::get('vi_id');
					$visualization = Visualization::find($vi_id);
					if (!$visualization || $visualization->user != Auth::user() || $visualization->published) goto fail;
					if (Input::get('ajax')) {
						return ViewResponseUtility::makeSubView('create-visualization-step-2', 'New Visualization: <span style="font-weight:300">Step 2 (Edit And Publish The Visualization)</span>', ['visualization'=>$visualization]);
					} else {
						return ViewResponseUtility::makeBaseView(URL::route('visualization.showCreate', [$username]), Constant::SIDEBAR_MYVISUALIZATION, [], ['step'=>2, 'vi_id'=>$vi_id]);
					}
				} else goto fail;
			} else {
				if (Input::get('ajax')) {
					return ViewResponseUtility::makeSubView('create-visualization-step-1', 'New Visualization: <span style="font-weight:300">Step 1 (Start A New Visualization)</span>');
				} else {
					return ViewResponseUtility::makeBaseView(URL::route('visualization.showCreate', [$username]), Constant::SIDEBAR_MYVISUALIZATION);
				}	
			}
		}
		
		fail: return Redirect::route('user.show', [$username]);
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
			
			$visualization->zoom = 1.0;
			$visualization->center_latitude = 0.0;
			$visualization->center_longitude = 0.0;
			
			$visualization_name = $username.'_'.$visualization->display_name;
			$fusion_table_id = GoogleFusionTable::create($visualization_name, self::prepareColumnListSentToGFusion($column_list, Input::get('type')));
			
			if ($fusion_table_id) {
				$visualization->fusion_table_id = $fusion_table_id;
				$visualization->save();
				
				return JSONResponseUtility::Redirect(URL::route('visualization.showCreate', [$username]).'?step=2&vi_id='.$visualization->id, false);
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
		
	}
	
	/* Require JSON request */
	public function updateTable($username, $id)
	{
		if (Auth::user()->username == $username) {
			if (!Input::isJson()) goto fail;
			
			$visualization = Visualization::find($id);
			if (!$visualization || $visualization->user != Auth::user()) goto fail;
			$gf_table_id = $visualization->fusion_table_id;
			
			$row_id = Input::json('row');
			$col_id = Input::json('col');
			$col_name = Input::json('colName');
			$col_type = Input::json('colType');
			$col_val_pairs = Input::json('colvalPairs');
			
			$result;
			switch (Input::json('type')) {
				case 'row-update':
					$result = GoogleFusionTable::updateRow($gf_table_id, $row_id, $col_val_pairs);
					break;
				case 'row-insert':
					$col_val_pairs['Created at'] = date("Y-m-d H:i:s");
					$result = GoogleFusionTable::insertRow($gf_table_id, $col_val_pairs);
					break;
				case 'row-delete':
					$result = GoogleFusionTable::deleteRows($gf_table_id, $row_id);
					break;
				case 'column-update':
					if ($col_id == 1 || $col_name == 'Milestone') {
						$visualization->milestone_format = $col_type;
						$visualization->save();
						$result = true;
					} else {
						$result = GoogleFusionTable::updateColumn($gf_table_id, $col_id, $col_name, $col_type);
					}
					break;
				case 'column-insert':
					$result = GoogleFusionTable::insertColumn($gf_table_id, $col_name, $col_type);
					break;
				case 'column-delete':
					$result = GoogleFusionTable::deleteColumn($gf_table_id, $col_id);
					break;
			}
			if (!$result) goto fail;
			return Response::make('', 200);
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
			if (!$visualization || $visualization->user != Auth::user()) goto fail;
			
			if (Input::get('request') == 'data') {
				$ret = GoogleFusionTable::retrieveGFusionAll($visualization->fusion_table_id);
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
						"zoom" => $visualization->zoom,
						"centerLatitude" => $visualization->center_latitude,
						"centerLongitude" => $visualization->center_longitude];
				$gfusion_props = GoogleFusionTable::retrieveGFusionProperties($visualization->fusion_table_id);
				$json["columnList"] = self::prepareColumnListSentToClient($gfusion_props->columns, $visualization->milestone_format);
				return Response::json($json);
			} else
				goto fail;
		} else {
			return Response::make('', 401);
		}
		
		fail: return Response::make('', 400);
	}
	
	private static function prepareColumnListSentToGFusion($input_column_list, $input_visualization_type) {
		$column_list = [['name'=>'Created at', 'type'=>'DATETIME'], ['name'=>'Milestone', 'type'=>'DATETIME'], ['name'=>'Position', 'type'=>'LOCATION']];
		
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
		$result[] = ['caption'=>'Milestone', 'type-caption'=>ucfirst($milestone_format), 'editable'=>true, 'column-id'=>1];
		$result[] = ['caption'=>'Position', 'type-caption'=>'Location: KML or Lat/Long or String', 'column-id'=>2];
		
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