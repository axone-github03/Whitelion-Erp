<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchasePerson extends Model
{
    //
    protected $table = 'purchase_person';

    function type()
    {
        return $this->belongsTo(PurchaseHierarchy::class, 'type', 'id');
    }
    function reporting_manager()
    {
        return $this->belongsTo(User::class, 'reporting_manager_id', 'id');
    }
    function reporting_company()
    {
        return $this->belongsTo(Company::class, 'reporting_company_id', 'id');
    }
}
