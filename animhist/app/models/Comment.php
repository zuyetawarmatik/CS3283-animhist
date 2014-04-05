<?php

class Comment extends Eloquent {
	
	protected $guarded = [];
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'comments';
	
	public function user()
	{
		return $this->belongsTo('User');
	}
}