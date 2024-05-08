<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChannelPartner extends Model {
	//
	protected $table = 'channel_partner';
	function d_city() {
		return $this->belongsTo(CityList::class, 'd_city_id', 'id');
	}
	function d_state() {
		return $this->belongsTo(StateList::class, 'd_state_id', 'id');
	}
	function d_country() {
		return $this->belongsTo(CountryList::class, 'd_country_id', 'id');
	}
}
