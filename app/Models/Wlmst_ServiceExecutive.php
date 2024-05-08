<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wlmst_ServiceExecutive extends Model
{
    protected $table = 'wlmst_service_user';

	function type() {
		return $this->belongsTo(ServiceHierarchy::class, 'type', 'id');
	}
	function reporting_manager() {
		return $this->belongsTo(User::class, 'reporting_manager_id', 'id');
	}
	function reporting_company() {
		return $this->belongsTo(Company::class, 'reporting_company_id', 'id');
	}
}
