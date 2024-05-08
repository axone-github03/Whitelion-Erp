<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\WlmstCompany;
use App\Models\Wlmst_ItemGroup;

class WlmstItemSubgroup extends Model
{
    protected $table = 'wlmst_item_subgroups';

    function company()
    {
        return $this->belongsTo(WlmstCompany::class, 'company_id', 'id');
    }

    function itemgroup()
    {
        return $this->belongsTo(Wlmst_ItemGroup::class, 'itemgroup_id', 'id');
    }
}
