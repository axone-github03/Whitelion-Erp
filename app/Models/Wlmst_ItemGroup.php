<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\WlmstCompany;

class Wlmst_ItemGroup extends Model
{
  protected $table = 'wlmst_item_groups';

  function company()
  {
    return $this->belongsTo(WlmstCompany::class, 'company_id', 'id');
  }
}
