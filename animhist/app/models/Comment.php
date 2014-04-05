<?php

class Comment extends Eloquent {
	
	protected $guarded = [];
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'comments';
	
	public function getFormattedCreatedDate() {
		if ($this->created_at->diffInDays() > 30) {
			return $this->created_at->toFormattedDateString();
		} else {
			return $this->created_at->diffForHumans();
		}
	}
	
	public function visualization()
	{
		return $this->belongsTo('Visualization');
	}
	
	public function user()
	{
		return $this->belongsTo('User');
	}
}