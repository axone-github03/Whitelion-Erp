<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProdWlmstItemCategory;


class ProdWlmstItem extends Model
{
    protected $table = 'prod_wlmst_items';

    function category()
    {
        return $this->belongsTo(ProdWlmstItemCategory::class, 'itemcategory_id', 'id');
    }
}
