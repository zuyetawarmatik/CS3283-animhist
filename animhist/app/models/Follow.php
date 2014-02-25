<?php

class Follow extends Eloquent {
	
	protected $guarded = array();
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'follows';
}