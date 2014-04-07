<?php

class VisualizationController extends \BaseController {
	
	public function showFeatured()
	{
		if (Input::get('ajax')) {
			$vis_sort_by_likes = DB::table('likes')->select(DB::raw('visualization_id, count(*) as cnt'))
								->groupBy('visualization_id')->orderBy('cnt', 'desc')->take(10)->get();
			
			$vis_ids = [];
			foreach ($vis_sort_by_likes as $item)
				$vis_ids[] = $item->visualization_id;
			
			$vis = []; $categories = [];
			if (!empty($vis_ids)) {
				$query = Visualization::select('id', 'category')
						->whereIn('id', $vis_ids)->where('published', 1);
				$vis = $query->get();
				$categories = $query->groupBy('category')->lists('category');
			}
			
			$ret_vis = [];
			foreach ($vis as $vi)
				$ret_vis[] = Visualization::find($vi->id);
			
			return ViewResponseUtility::makeSubView('show-visualizations-featured', 'Featured', ['visualizations'=>$ret_vis, 'categories'=>$categories]);
		} else {
			if (Auth::check())
				return ViewResponseUtility::makeBaseView(URL::route('visualization.showFeatured'), Constant::SIDEBAR_FEATURED);
			else
				return ViewResponseUtility::makeBaseView(URL::route('visualization.showFeatured'), Constant::SIDEBAR_GUEST_FEATURED);
		}
	}
	
	public function showFollowing()
	{
		if (Input::get('ajax')) {
			$following_ids = Auth::user()->followings->lists('id');
			
			$vis = []; $categories = [];
			if (!empty($following_ids)) {
				$query = Visualization::select('id', 'user_id', 'category')
								->whereIn('user_id', $following_ids)->where('published', 1)->orderBy('created_at', 'desc');
				$vis = $query->get();
				$categories = $query->groupBy('category')->lists('category');
			}
			
			$ret_vis = [];
			foreach ($vis as $vi)
				$ret_vis[] = Visualization::find($vi->id);
			
			return ViewResponseUtility::makeSubView('show-visualizations-following', 'Following', ['visualizations'=>$ret_vis, 'categories'=>$categories]);
		} else {
			if (Auth::check())
				return ViewResponseUtility::makeBaseView(URL::route('visualization.showFollowing'), Constant::SIDEBAR_FOLLOWING);
			else
				return ViewResponseUtility::makeBaseView(URL::route('visualization.showFollowing'), Constant::SIDEBAR_GUEST_FOLLOWING);
		}
	}
	
	public function showSearch()
	{
		if (Input::get('ajax'))
			return ViewResponseUtility::makeSubView('show-visualizations-search', 'Search', ['visualizations'=>[], 'categories'=>[]]);
		else {
			if (Auth::check())
				return ViewResponseUtility::makeBaseView(URL::route('visualization.showSearch'), Constant::SIDEBAR_SEARCH);
			else
				return ViewResponseUtility::makeBaseView(URL::route('visualization.showSearch'), Constant::SIDEBAR_GUEST_SEARCH);
		}
	}
	
	public function search()
	{	
		if (!Input::has('q')) return;
		$q = Input::get('q');
		$query = Visualization::select('id', 'category')
							->where('display_name', 'LIKE', '%'.$q.'%')->where('published', 1)->orderBy('created_at', 'desc');
		$vis = $query->get();
		$categories = $query->groupBy('category')->lists('category');
		
		$ret_vis = [];
		foreach ($vis as $vi) {
			$visualization = Visualization::find($vi->id);
			$ret_vi = [];
			$user = User::find($visualization->user_id);
			$ret_vi['category'] = $visualization->category;
			$ret_vi['displayName'] = $visualization->display_name;
			$ret_vi['createdAt'] = $visualization->getFormattedCreatedDate();
			$ret_vi['updatedAt'] = $visualization->getFormattedUpdatedDate();
			$ret_vi['userDisplayName'] = $user->display_name;
			$ret_vi['userAvatarURL'] = $user->avatar->url('thumb');
			$ret_vi['userURL'] = URL::route('user.show', $user->username);
			$ret_vi['viewURL'] = URL::route('visualization.show', [$user->username, $visualization->id]);
			$ret_vi['imgURL'] = 'http://maps.googleapis.com/maps/api/staticmap?maptype=terrain&key=AIzaSyBTVe9qjhnOgr7dNZJGjpQkyuViCn3wKDU&center='.$visualization->center_latitude.','.$visualization->center_longitude.'&zoom='.max(number_format($visualization->zoom)-1,1).'&size=340x200&sensor=false';
			$ret_vis[] = $ret_vi;
		}
		
		return Response::json(['visualizations'=>$ret_vis, 'categories'=>$categories]);
	}
	
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
			$rules = [
					'display-name'		=> 'required|unique:visualizations,display_name,NULL,id,user_id,'.Auth::user()->id,
					'type'      		=> 'required',
					'upload'			=> 'required_if:option,upload'
					];
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
			
			$visualization->zoom = 3;
			$visualization->center_latitude = 0.00;
			$visualization->center_longitude = 0.00;

			$visualization_name = $username.'_'.$visualization->display_name;
			$gf_column_list; $table_info;
			$fusion_table_id = false;
			if (Input::get('option') == 'manual') {
				$column_list = json_decode(Input::get('column-list'), true);
					
				foreach ($column_list as $column) {
					if ($column['caption'] == 'Milestone') {
						$visualization->milestone_format = $column['type-caption'];
						break;
					}
				}
				
				$gf_column_list = self::prepareColumnListSentToGFusion($column_list, Input::get('type'));
				$fusion_table_id = GoogleFusionTable::create($visualization_name, Input::get('type'), $gf_column_list);
			} else if (Input::get('option') == 'upload') {
				// Default is milestone type year
				$visualization->milestone_format = 'year';
				
				$uploaded_file = Input::file('upload');
				if ($uploaded_file->getMimeType() != 'text/plain' || $uploaded_file->getClientOriginalExtension() != 'csv')
					return JSONResponseUtility::ValidationError(['upload'=>['Wrong uploaded file type.']]);
				
				$path = public_path().'/uploads/'.basename($uploaded_file->getRealPath());
				$filename = $uploaded_file->getClientOriginalName();
				$uploaded_file->move($path, $filename);
				
				$table_info = self::prepareCSVSentToGFusion([$path, $filename], Input::get('type'));
				$fusion_table_id = GoogleFusionTable::createWithFile($visualization_name, Input::get('type'), $table_info);
				
				File::deleteDirectory($path);
			}
			
			if ($fusion_table_id) {
				$visualization->fusion_table_id = $fusion_table_id;
				$ref_column_list = Input::get('option') == 'manual' ? $gf_column_list : $table_info['columns'];
				
				foreach ($ref_column_list as $gf_column) {
					if ($gf_column['type'] == 'NUMBER') {
						$visualization->default_column = $gf_column['name'];
						break;
					}
				}
				
				$visualization->save();
				
				return JSONResponseUtility::Redirect(URL::route('visualization.showCreate', $username).'?step=2&vi_id='.$visualization->id, false);
			} else
				return ResponseUtility::badRequest();
		} else {
			return ResponseUtility::unauthorized();
		}
	}

	public function show($username, $id)
	{
		$user = User::where('username', $username)->first();
		
		$visualization = Visualization::find($id);
		if (!$visualization || $visualization->user->username != $username || !$visualization->published)
			return Redirect::route('user.show', $username);
		
		if (Input::get('ajax')) {
			$title = $visualization->display_name.' by <a href="'.URL::route('user.show', $username).'">'.$user->display_name.'</a>';
			return ViewResponseUtility::makeSubView('view-visualization', $title, ['visualization'=>$visualization]);
		} else {
			if ($user->isAuthUser())
				return ViewResponseUtility::makeBaseView(URL::route('visualization.show', [$username, $id]), Constant::SIDEBAR_MYVISUALIZATION);
			else {
				$highlight_id = Auth::check() ? Constant::SIDEBAR_USERVISUALIZATION : Constant::SIDEBAR_GUEST_USERVISUALIZATION;
				return ViewResponseUtility::makeBaseView(URL::route('visualization.show', [$username, $id]), $highlight_id, ['user'=>$user]);
			}
		}
	}

	public function showEdit($username, $id)
	{
		if (Auth::user()->username == $username) {
			$visualization = Visualization::find($id);
			if (!$visualization || !$visualization->user->isAuthUser() || !$visualization->published) goto fail;
			if (Input::get('ajax')) {
				return ViewResponseUtility::makeSubView('create-visualization-step-2', 'Edit Visualization: <span style="font-weight:300">'.$visualization->display_name.'</span>', ['visualization'=>$visualization]);
			} else {
				return ViewResponseUtility::makeBaseView(URL::route('visualization.showEdit', [$username, $id]), Constant::SIDEBAR_MYVISUALIZATION);
			}
		}
		
		fail: return Redirect::route('user.show', $username);
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
				$visualization->center_latitude = number_format(Input::get('center-latitude'), 3);
			if (Input::has('center-longitude'))
				$visualization->center_longitude = number_format(Input::get('center-longitude'), 3);
			if (Input::has('published'))
				$visualization->published = Input::get('published') == 'true' ? true : false;
			
			$visualization->save();
			
			$json = [];
			
			if (Input::has('description'))
				$json['description'] = $visualization->description;
			if (Input::has('default-column')) {
				$gft = new GoogleFusionTable($visualization->fusion_table_id, $visualization->type);
				$json['defaultColumn'] = $visualization->default_column;
				$json['defaultStyleId'] = $gft->getColumnStyle($visualization->default_column)->styleId;
			} 
			if (Input::has('display-name'))
				$json['displayName'] = $visualization->display_name;
			if (Input::has('category'))
				$json['category'] = $visualization->category;
			if (Input::has('zoom'))
				$json['zoom'] = number_format($visualization->zoom);
			if (Input::has('center-latitude'))
				$json['centerLatitude'] = number_format($visualization->center_latitude, 3);
			if (Input::has('center-longitude'))
				$json['centerLongitude'] = number_format($visualization->center_longitude, 3);
			if (Input::has('published')) {
				// Return to view visualization page
				if ($visualization->published)
					return JSONResponseUtility::Redirect(URL::route('visualization.show', [$username, $visualization->id]), true);
			}
			
			return Response::JSON($json);
		} else {
			return ResponseUtility::unauthorized();
		}
		
		fail: return ResponseUtility::badRequest();
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
							if (preg_match('/^(\-?\d+(\.\d+)?)\s*(\-?\d+(\.\d+)?)$/', $col_val_pairs['Position']))
								$col_val_pairs['Geocode'] = $col_val_pairs['Position'];
							else {
								$geocode = GoogleGeocoding::getLatLongForString($col_val_pairs['Position']);
								if ($geocode) $col_val_pairs['Geocode'] = $geocode;
							}
						} else
							$col_val_pairs['Geocode'] = $col_val_pairs['Position'];
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
							if (preg_match('/^(\-?\d+(\.\d+)?)\s*(\-?\d+(\.\d+)?)$/', $col_val_pairs['Position']))
								$col_val_pairs['Geocode'] = $col_val_pairs['Position'];
							else {
								$geocode = GoogleGeocoding::getLatLongForString($col_val_pairs['Position']);
								if ($geocode) $col_val_pairs['Geocode'] = $geocode;
							}
						} else
							$col_val_pairs['Geocode'] = $col_val_pairs['Position'];
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
					return ResponseUtility::success();
				case 'column-insert':
					// If default column is null and this new column is NUMBER
					if (empty($visualization->default_column) && $col_type == 'NUMBER') {
						$visualization->default_column = $col_name;
						$visualization->save();
					}
					return ResponseUtility::success();
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
					return ResponseUtility::success();
				default:
					return ResponseUtility::success();
			}
		} else {
			return ResponseUtility::unauthorized();
		}
		
		fail: return ResponseUtility::badRequest();
	}
	
	public function updateStyle($username, $id)
	{
		if (Auth::user()->username == $username) {
			if (!Input::isJson()) goto fail;
				
			$visualization = Visualization::find($id);
			if (!$visualization || !$visualization->user->isAuthUser()) goto fail;
			$gft = new GoogleFusionTable($visualization->fusion_table_id, $visualization->type);
			
			$col_name = Input::json('colName');
			if (empty($col_name)) goto fail;
			
			$retrieved_style = Input::json('style');
			$ret = $gft->updateColumnStyle($col_name, self::prepareStyleSentToGFusion($col_name, $retrieved_style, $visualization->type));
			if ($ret) return Response::JSON($gft->getColumnStyle($col_name));
		} else {
			return ResponseUtility::unauthorized();
		}
		
		fail: return ResponseUtility::badRequest();
	}
	
	public function comment($username, $id) {
		$visualization = Visualization::find($id);
		if (!$visualization || $visualization->user->username != $username) goto fail;
		
		if (Input::has('content')) {
			$user = Auth::user();
			
			$comment = new Comment();
			$comment->user_id = $user->id;
			$comment->visualization_id = $id;
			$comment->content = Input::get('content');
			$comment->save();
			
			$ret = [];
			$ret['createdAt'] = $comment->getFormattedCreatedDate();
			$ret['userDisplayName'] = $user->display_name;
			$ret['userAvatarURL'] = $user->avatar->url('thumb');
			$ret['userURL'] = URL::route('user.show', $user->username);
			$ret['numComments'] = count($visualization->comments);
			$ret['content'] = $comment->content;
			
			return Response::json($ret);
		} else {
			return Response::json([]);
		}
		
		fail: return ResponseUtility::badRequest();
	}
	
	public function info($username, $id) {
		$visualization = Visualization::find($id);
		if (!$visualization || $visualization->user->username != $username) goto fail;
		$gft = new GoogleFusionTable($visualization->fusion_table_id, $visualization->type);
		
		if (Input::get('request') == 'data') {
			$ret = $gft->retrieveGFusionAllData();
			if (!$ret) goto fail;
			return Response::json($ret);
		} else if (Input::get('request') == 'property') {
			$json = ["gfusionTableID" => $visualization->fusion_table_id,
					"displayName" => $visualization->display_name,
					"username" => $visualization->user->username,
					"type" => $visualization->type,
					"description" => $visualization->description,
					"category" => $visualization->category,
					"published" => $visualization->published,
					"milestoneFormat" => $visualization->milestone_format,
					"milestones" => $visualization->milestones,
					"defaultColumn" => $visualization->default_column,
					"zoom" => number_format($visualization->zoom),
					"centerLatitude" => number_format($visualization->center_latitude, 3),
					"centerLongitude" => number_format($visualization->center_longitude, 3)];
			if ($visualization->default_column != null)
				$json["defaultStyleId"] = $gft->getColumnStyle($visualization->default_column)->styleId;
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
		}
		
		fail: return ResponseUtility::badRequest();
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
			return ResponseUtility::unauthorized();
		}
		
		fail: return ResponseUtility::badRequest();
	}
	
	public function like($username, $id)
	{
		$visualization = Visualization::find($id);
		if (!$visualization || $visualization->user->username != $username) goto fail;
		
		$user_id = Auth::user()->id;
		$existing_like = Like::where('visualization_id', $id)->where('user_id', $user_id)->first();
		if ($existing_like) goto fail;
		
		$like = new Like();
		$like->visualization_id = $id;
		$like->user_id = $user_id;
		$like->save();
		return Response::json(['numLikes'=>count($visualization->likes)]);
		
		fail: return ResponseUtility::badRequest();
	}
	
	public function unlike($username, $id)
	{
		$visualization = Visualization::find($id);
		if (!$visualization || $visualization->user->username != $username) goto fail;
		
		$user_id = Auth::user()->id;
		$existing_like = Like::where('visualization_id', $id)->where('user_id', $user_id)->first();
		if (!$existing_like) goto fail;
		
		$existing_like->delete();
		return Response::json(['numLikes'=>count($visualization->likes)]);
		
		fail: return ResponseUtility::badRequest();
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
	
	private static function prepareStyleSentToGFusion($col_name, $retrieved_style, $visualization_type) {
		$style = [];
		if ($visualization_type == 'point') {
			$style['markerOptions']['iconStyler']['columnName'] = $col_name;
			$style['markerOptions']['iconStyler']['buckets'] = [];
			for ($i = 0; $i < count($retrieved_style); $i++) {
				$item = $retrieved_style[$i];
				$style['markerOptions']['iconStyler']['buckets'][$i]['icon'] = $item['Icon'];
				$style['markerOptions']['iconStyler']['buckets'][$i]['min'] = $item['Level'];
				if ($i == count($retrieved_style) - 1)
					$style['markerOptions']['iconStyler']['buckets'][$i]['max'] = Constant::NUMBER_POSITIVE_INF;
				else
					$style['markerOptions']['iconStyler']['buckets'][$i]['max'] = $retrieved_style[$i + 1]['Level'];
			}
		} else if ($visualization_type == 'polygon') {
			$style['polygonOptions']['fillColorStyler']['columnName'] = $col_name;
			$style['polygonOptions']['fillColorStyler']['buckets'] = [];
			for ($i = 0; $i < count($retrieved_style); $i++) {
				$item = $retrieved_style[$i];
				$style['polygonOptions']['fillColorStyler']['buckets'][$i]['color'] = $item['Color'];
				$style['polygonOptions']['fillColorStyler']['buckets'][$i]['opacity'] = $item['Opacity'];
				$style['polygonOptions']['fillColorStyler']['buckets'][$i]['min'] = $item['Level'];
				if ($i == count($retrieved_style) - 1)
					$style['polygonOptions']['fillColorStyler']['buckets'][$i]['max'] = Constant::NUMBER_POSITIVE_INF;
				else
					$style['polygonOptions']['fillColorStyler']['buckets'][$i]['max'] = $retrieved_style[$i + 1]['Level'];
			}
		}
		return $style;
	}
	
	private static function readCSV($file) {
		$arr = [];
		$fp = fopen($file, 'r');
		while (!feof($fp)) {
			$new_row = (array) fgetcsv($fp);
			
			$is_empty = true;
			foreach ($new_row as $cell) {
				if ($cell != '') {
					$is_empty = false;
					break;
				}
			}
			
			if (!$is_empty) $arr[] = $new_row;
		}
		fclose($fp);
		return $arr;
	}
	
	private static function prepareCSVSentToGFusion($file_vars, $visualization_type) {
		$path = $file_vars[0];
		$filename = $file_vars[1];
		
		$arr = self::readCSV($path.'/'.$filename);
		
		$headers; $specs;
		if (count($arr) > 1) {
			$headers = $arr[0];
			$specs = $arr[1];
		} else return false;
		
		$reduced_headers = array_unique($headers);
		$rows = array_slice($arr, 2);
		
		$milestone_col = array_search('milestone', array_map('strtolower', $headers));
		$position_col = array_search('position', array_map('strtolower', $headers));

		if ($milestone_col === false || $position_col === false) return false;
		
		$exported_headers = [];
		$exported_headers[Constant::COL_ID_CREATEDAT] = 'CreatedAt';
		$exported_headers[Constant::COL_ID_MILESTONEREP] = 'MilestoneRep';
		$exported_headers[Constant::COL_ID_GEOCODE] = 'Geocode';
		$exported_headers[Constant::COL_ID_MILESTONE] = 'Milestone';
		$exported_headers[Constant::COL_ID_POSITION] = 'Position';
		foreach ($reduced_headers as $header) {
			if (array_search(strtolower($header), array_map('strtolower', $exported_headers)) == false && $header != '')
				$exported_headers[] = $header;
		}
		
		$exported_rows = [];
		for ($i = 0; $i < count($rows); $i++) {
			$row = $rows[$i];
			
			$exported_row = array_fill(0, count($exported_headers), '');
			
			$exported_row[Constant::COL_ID_CREATEDAT] = date('Y/m/d H:i:s');
			
			if ($c_milestone = self::prepareProperDateTime($row[$milestone_col], 'm/d/Y'))
				$exported_row[Constant::COL_ID_MILESTONE] = $c_milestone;
			
			if ($c_milestone = self::prepareProperDateTime($row[$milestone_col], 'Y'))
				$exported_row[Constant::COL_ID_MILESTONEREP] = $c_milestone; // Default is Year milestone type
				
			$exported_row[Constant::COL_ID_POSITION] = $row[$position_col];
 			
 			if (strtolower($visualization_type) == 'point') {
 				$geocode = GoogleGeocoding::getLatLongForString($row[$position_col]);
 				if ($geocode) $exported_row[Constant::COL_ID_GEOCODE] = $geocode;
 			} else {
 				$exported_row[Constant::COL_ID_GEOCODE] = $exported_row[Constant::COL_ID_POSITION];
 			}
 			
 			for ($j = 5; $j < count($exported_headers); $j++) {
 				$header = $exported_headers[$j];
 				$exported_row[$j] = $row[array_search($header, $headers)];
 			}
 			
 			$exported_rows[] = $exported_row;
		}
		
		/* Prepare column list */
		$column_list = [['name'=>'CreatedAt', 'type'=>'DATETIME'], ['name'=>'MilestoneRep', 'type'=>'DATETIME'], ['name'=>'Geocode', 'type'=>'LOCATION'], ['name'=>'Milestone', 'type'=>'DATETIME'], ['name'=>'Position', 'type'=>'LOCATION']];
		for ($i = 5; $i < count($exported_headers); $i++) {
			$header = $exported_headers[$i];
			$column = [];
			$column['name'] = $header;
			$column_type = $specs[array_search($header, $headers)];
			$column['type'] = strtolower($column_type) == 'number' ? 'NUMBER' : 'STRING';
			
			$column_list[] = $column; 
		}
		
		/* Prepare CSV to upload to Google */
		$re_path = $path.'/e_'.$filename;
		$fp = fopen($re_path, 'w');
		foreach ($exported_rows as $exported_row)
			fputcsv($fp, $exported_row,',','"');
		fclose($fp);
		
		return ['path'=>$re_path, 'columns'=>$column_list];
	}
	
	private static function prepareProperDateTime($dt, $format_str) {
		$ret = $dt;
		$splitted = explode('/', $dt);
		
		if (count($splitted) == 1)
			$ret = '1/1/'.$dt;
		else if (count($splitted) == 2)
			$ret = $splitted[0].'/1/'.$splitted[1];
		
		$datetime = new DateTime($ret);
		$ret = $datetime->format($format_str);
		
		return $ret;
	}
}