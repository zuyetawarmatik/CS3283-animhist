<?php

class Visualization extends Eloquent {
	use Codesleeve\Stapler\Stapler;
	
	protected $guarded = [];
	
	public function __construct(array $attributes = []) {
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
	
	public function getFormattedUpdatedDate() {
		if ($this->updated_at->diffInDays() > 30) {
			return $this->updated_at->toFormattedDateString();
		} else {
			return $this->updated_at->diffForHumans();
		}
	}
	
	public function getMilestoneFormatString() {
		$format_str = 'd M Y';
		switch (strtolower($this->milestone_format)) {
			case 'day':
				$format_str = 'd M Y';
				break;
			case 'month':
				$format_str = 'M Y';
				break;
			case 'year':
				$format_str = 'Y';
				break;
			default:
				break;
		}
		return $format_str;
	}
	
	public function user() {
		return $this->belongsTo('User');
	}
	
	public function comments()
	{
		return $this->hasMany('Comment')->orderBy('created_at', 'desc');
	}
	
	public function likes()
	{
		return $this->hasMany('Like');
	}
}