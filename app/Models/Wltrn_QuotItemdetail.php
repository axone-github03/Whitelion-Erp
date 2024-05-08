<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wltrn_QuotItemdetail extends Model
{
    protected $table = 'wltrn_quot_itemdetails';
    use HasFactory;

    protected $fillable = [
        'id',
        'quotchart_id',
        'quot_id',
        'quotgroup_id',
        'srno',
        'ampier',
        'company_id',
        'itemgroup_id',
        'itemsubgroup_id',
        'itemcategory_id',
        'item_id',
        'itemcode',
        'itemdescription',
        'hsn_code',
        'qty',
        'rate',
        'discper',
        'discamount',
        'grossamount',
        'addamount',
        'lessamount',
        'taxableamount',
        'igst_per',
        'igst_amount',
        'cgst_per',
        'cgst_amount',
        'sgst_per',
        'sgst_amount',
        'net_amount',
        'created_at',
        'entryby',
        'entryip',
        'updated_at',
        'updateby',
        'updateip',
        'floor_no',
        'floor_name',
        'room_no',
        'room_name',
        'board_no',
        'board_name',
        'isactiveroom',
        'isactiveboard',
        'copyfromroom_no',
        'copyfromboard_no',
        'item_price_id',
        'board_size',
        'board_item_id',
        'board_item_price_id',
        'item_type',
        'room_range',
        'board_range',
    ];

    // public function getImageAttribute($board_image) {
	// 	return getSpaceFilePath($board_image);
	// }
}
