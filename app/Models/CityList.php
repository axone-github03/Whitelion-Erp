<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CityList extends Model {
	//
	protected $table = 'city_list';

	function state() {
		return $this->belongsTo(StateList::class, 'state_id', 'id');
	}
	function country() {
		return $this->belongsTo(CountryList::class, 'country_id', 'id');
	}
}
