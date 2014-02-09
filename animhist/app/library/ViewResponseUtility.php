<?php
class ViewResponseUtility {
	
	public static function makeSubView($name, $title, $arr = [], $minimize_right = false) {
		return View::make($name, array_merge($arr, ['title'=>$title, 'has_back'=>Input::get('back'), 'back_url'=>Input::get('referer'), 'has_minimize_right'=>$minimize_right]));
	}
	
	public static function makeBaseView($iframe_src, $sidebar_highlight_id, $arr = [], $other_input = []) {
		$iframe_src = $iframe_src.'?ajax=1';
		foreach ($other_input as $key=>$value) {
			$iframe_src .= '&'.urlencode($key).'='.urlencode($value);
		}
		return View::make('layouts.base', array_merge($arr, ['main_panel_iframe_url'=>$iframe_src, 'highlight_id'=>$sidebar_highlight_id]));
	}
}