<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\WlmstCompany;
use App\Models\Wlmst_ItemGroup;
use App\Models\WlmstItemSubgroup;
use App\Models\WlmstItem;

class Wlmst_ItemPrice extends Model
{
    protected $table = 'wlmst_item_prices';

    function company()
    {
        return $this->belongsTo(WlmstCompany::class, 'company_id', 'id');
    }

    function itemgroup()
    {
        return $this->belongsTo(Wlmst_ItemGroup::class, 'itemgroup_id', 'id');
    }

    function itemsubgroup()
    {
        return $this->belongsTo(WlmstItemSubgroup::class, 'itemsubgroup_id', 'id');
    }

    function item()
    {
        return $this->belongsTo(WlmstItem::class, 'item_id', 'id');
    }
}
