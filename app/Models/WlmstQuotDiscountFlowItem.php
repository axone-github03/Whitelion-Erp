<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WlmstQuotDiscountFlowItem extends Model
{
    use HasFactory;

    protected $table = 'wlmst_quot_discount_flow_items';

    function usertype()
    {
        return $this->belongsTo(SalesHierarchy::class, 'user_type', 'id')->withDefault([
            'id' => 9,
            'name' => 'Channel Partner',
        ]);
    }
}
