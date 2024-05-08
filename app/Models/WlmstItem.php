<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\WlmstItemCategory;


class WlmstItem extends Model
{
    protected $table = 'wlmst_items';

    function category()
    {
        return $this->belongsTo(WlmstItemCategory::class, 'itemcategory_id', 'id');
    }
}
