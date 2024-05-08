<?php

namespace App\Models;

use Config;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable {
	use HasApiTokens, HasFactory, Notifiable;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'name',
		'username',
		'password',
	];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [
		'password',
		'remember_token',
	];

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array
	 */
	protected $casts = [
		'email_verified_at' => 'datetime',
	];

	public function getAvatarAttribute($avatar) {
		return Config::get('app.url') . "/assets/images/users/" . $avatar;
	}

	function city() {
		return $this->belongsTo(CityList::class, 'city_id', 'id');
	}
	function state() {
		return $this->belongsTo(StateList::class, 'state_id', 'id');
	}
	function country() {
		return $this->belongsTo(CountryList::class, 'country_id', 'id');
	}
	function company() {
		return $this->belongsTo(Company::class, 'company_id', 'id');
	}
}
