<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProdWlmstCompany;
use App\Models\ProdWlmst_ItemGroup;
use App\Models\WlmstItemSubgroup;
use App\Models\ProdWlmstItem;

class ProdWlmst_ItemPrice extends Model
{
    protected $table = 'prod_wlmst_item_prices';

    function company()
    {
        return $this->belongsTo(ProdWlmstCompany::class, 'company_id', 'id');
    }

    function itemgroup()
    {
        return $this->belongsTo(ProdWlmst_ItemGroup::class, 'itemgroup_id', 'id');
    }

    function itemsubgroup()
    {
        return $this->belongsTo(WlmstItemSubgroup::class, 'itemsubgroup_id', 'id');
    }

    function item()
    {
        return $this->belongsTo(ProdWlmstItem::class, 'item_id', 'id');
    }
}
