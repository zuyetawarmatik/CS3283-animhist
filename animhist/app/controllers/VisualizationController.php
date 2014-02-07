<?php

class VisualizationController extends \BaseController {
	
	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function showCreate($username)
	{
		if (Auth::user()->username == $username) {
			if (Input::get('step') == 2) {
				
			} else {
				if (Input::get('ajax')) {
					return ViewResponseUtility::makeSubView('create-visualization-step-1', 'New Visualization: <span style="font-weight:300">Step 1 (Start A New Visualization)</span>');
				} else {
					return ViewResponseUtility::makeBaseView(URL::route('visualization.showCreate', [$username]), Constant::SIDEBAR_MYVISUALIZATION);
				}	
			}
		} else {
			// When requesting full page - no ajax
			return Redirect::route('user.show', [$username]);
		}
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
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
			
			$visualization_name = $visualization->user_id.'_'.$visualization->display_name;
			$fusion_table_id = GoogleFusionTable::create($visualization_name, self::prepareColumnList($column_list, Input::get('type')));

			if ($fusion_table_id) {
				// TODO
				$visualization->fusion_table_id = $fusion_table_id;
				$visualization->save();
				
				return $fusion_table_id;
			} else
				return Response::make('', 400);
			
		} else {
			return Response::make('', 401);
		}
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  $username
	 * @return Response
	 */
	public function show($id)
	{
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function showEdit($id)
	{
		
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		
	}
	
	private static function prepareColumnList($input_column_list, $input_visualization_type) {
		$column_list = [['name'=>'Milestone', 'type'=>'DATETIME']];
		if ($input_visualization_type == 'point')
			$column_list[] = ['name'=>'Position', 'type'=>'LOCATION'];
		else if ($input_visualization_type == 'polygon')
			$column_list[] = ['name'=>'Position', 'type'=>'KML'];
		
		foreach ($input_column_list as $input_column) {
			if ($input_column['caption'] == 'HTMLData' && $input_column['type-caption'] == 'String') {
				$column_list[] = ['name'=>'HTMLData', 'type'=>'STRING'];
				break;
			}
		}
		
		foreach ($input_column_list as $input_column) {
			if ($input_column['type-caption'] == 'Number')
				$column_list[] = ['name'=>$input_column['caption'], 'type'=>'NUMBER'];
		}
		
		return $column_list;
	}
}