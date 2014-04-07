<?php

class Like extends Eloquent {
	
	protected $guarded = [];
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'likes';
	
	public function visualization()
	{
		return $this->belongsTo('Visualization');
	}
	
	public function user()
	{
		return $this->belongsTo('User');
	}
}