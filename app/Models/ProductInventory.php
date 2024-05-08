<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductInventory extends Model {
	//
	protected $table = 'product_inventory';

	function product_brand() {
		return $this->belongsTo(DataMaster::class, 'product_brand_id', 'id');
	}
	function product_code() {
		return $this->belongsTo(DataMaster::class, 'product_code_id', 'id');
	}
	public function getImageAttribute($image) {
		return getSpaceFilePath($image);
	}

	public function getThumbAttribute($thumb) {
		return getSpaceFilePath($thumb);
	}
}
