<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalePerson extends Model {
	//
	protected $table = 'sale_person';

	function type() {
		return $this->belongsTo(SalesHierarchy::class, 'type', 'id');
	}
	function reporting_manager() {
		return $this->belongsTo(User::class, 'reporting_manager_id', 'id');
	}
	function reporting_company() {
		return $this->belongsTo(Company::class, 'reporting_company_id', 'id');
	}
}
