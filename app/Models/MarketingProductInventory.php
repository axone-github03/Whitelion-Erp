<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketingProductInventory extends Model {
	//
	protected $table = 'marketing_product_inventory';

	function product_group() {
		return $this->belongsTo(DataMaster::class, 'marketing_product_group_id', 'id');
	}
	function product_code() {
		return $this->belongsTo(DataMaster::class, 'marketing_product_code_id', 'id');
	}
	public function getImageAttribute($image) {
		return getSpaceFilePath($image);
	}

	public function getThumbAttribute($thumb) {
		return getSpaceFilePath($thumb);
	}
}
