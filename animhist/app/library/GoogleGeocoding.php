<?php

use Httpful\Request;

class GoogleGeocoding {
	public static function getGeocodeForString($str) {
		$response = Request::get('https://maps.googleapis.com/maps/api/geocode/json?address='.urlencode($str).'&sensor=false&key=AIzaSyAttNeBptvoIgxbXmj0yxIXjdMQosxk90s')
					->send();
		return $response->body;
	}
	
	public static function getLatLongForString($str) {
		$res = GoogleGeocoding::getGeocodeForString($str);
		if ($res->status == 'OK') {
			$lat = $res->results[0]->geometry->location->lat;
			$long = $res->results[0]->geometry->location->lng;
			return $lat.' '.$long;
		}
		return false;
	}
}