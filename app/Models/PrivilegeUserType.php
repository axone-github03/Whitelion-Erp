<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrivilegeUserType extends Model {
	//
	protected $table = 'privilege_user_type';

	function privilege() {
		return $this->belongsTo(Privilege::class, 'privilege_id', 'id');
	}
}
