<?php

use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableInterface;
use Codesleeve\Stapler\ORM\StaplerableInterface;
use Codesleeve\Stapler\ORM\EloquentTrait;

class User extends Eloquent implements UserInterface, RemindableInterface, StaplerableInterface {
	use EloquentTrait;
	
	protected $guarded = [];
	
	public function __construct(array $attributes = []) {
		$this->hasAttachedFile('avatar', [
					'styles' => [
						'medium' => '400x400#',
						'thumb' => '60x60#'
					],
					'default_url' => '/system/:class/:attachment/:style/missing.jpg'
				]);
	
		parent::__construct($attributes);
	}

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'users';

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = ['password'];

	/**
	 * Get the unique identifier for the user.
	 *
	 * @return mixed
	 */
	public function getAuthIdentifier()
	{
		return $this->getKey();
	}

	/**
	 * Get the password for the user.
	 *
	 * @return string
	 */
	public function getAuthPassword()
	{
		return $this->password;
	}

	/**
	 * Get the e-mail address where password reminders are sent.
	 *
	 * @return string
	 */
	public function getReminderEmail()
	{
		return $this->email;
	}
	
	public function getFormattedCreatedDate() {
		if ($this->created_at->diffInDays() > 30) {
			return $this->created_at->toFormattedDateString();
		} else {
			return $this->created_at->diffForHumans();
		}
	}
	
	public function publishedVisualizations()
	{
		return $this->hasMany('Visualization')->where('published', true)->orderBy('created_at', 'desc');
	}
	
	public function visualizations()
	{
		return $this->hasMany('Visualization')->orderBy('created_at', 'desc');
	}

	public function followings()
	{
		return $this->belongsToMany('User', 'follows', 'user_id', 'following_id');
	}
	
	public function followers()
	{
		return $this->belongsToMany('User', 'follows', 'following_id', 'user_id');
	}
	
	public function isAuthUser() {
		return (Auth::check() && Auth::user()->id == $this->id);
	}
}