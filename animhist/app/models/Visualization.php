<?php

class Visualization extends Eloquent {
	use Codesleeve\Stapler\Stapler;
	
	protected $guarded = array();
	
	public function __construct(array $attributes = array()) {
		$this->hasAttachedFile('thumb', [
					'styles' => [
						'thumb' => '340x200#'
					],
					'default_url' => '/system/:class/:attachment/:style/missing.png'
				]);
	
		parent::__construct($attributes);
	}

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'visualizations';
	
	public function getFormattedCreatedDate() {
		if ($this->created_at->diffInDays() > 30) {
			return $this->created_at->toFormattedDateString();
		} else {
			return $this->created_at->diffForHumans();
		}
	}

}