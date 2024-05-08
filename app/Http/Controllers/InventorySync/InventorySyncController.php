<?php

namespace App\Http\Controllers\InventorySync;

use App\Http\Controllers\Controller;
use FontLib\Table\Type\name;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Hash;
use App\Models\ProdWlmst_ItemPrice;
use App\Models\Wlmst_ItemPrice;
use App\Models\ProductInventory;
use App\Models\InventorySync;
use App\Models\InventorySyncLog;
use App\Models\StateList;
use App\Models\CountryList;
use App\Models\CityList;
use App\Models\ProdMstJobWorker;


class InventorySyncController extends Controller
{

    public function index(Request $request)
    {
        
        $data = array();
        $data['title'] = "Inventory Sync";
        return view('inventory_sync/index', compact('data'));
    }

    public function ajax(Request $request) {
        $searchColumns = array(
            0 => 'prod_wlmst_item_prices.id',
            1 => 'prod_wlmst_item_prices.code',
            2 => 'prod_wlmst_items.itemname',
        );
        
        $columns = array(
            0 => 'prod_wlmst_item_prices.id',
            1 => 'prod_wlmst_item_prices.code',
            2 => 'prod_wlmst_items.itemname',
        );

        $recordsTotal = ProdWlmst_ItemPrice::count();
        $recordsFiltered = $recordsTotal;

        $query = ProdWlmst_ItemPrice::query();
        $query->select($columns);
        $query->leftJoin('prod_wlmst_items', 'prod_wlmst_items.id', '=', 'prod_wlmst_item_prices.item_id');
        $query->limit($request->length);
        $query->offset($request->start);
        $query->orderBy($columns[$request['order'][0]['column']], $request['order'][0]['dir']);
        $isFilterApply = 0;

        if (isset($request['search']['value'])) {
            $isFilterApply = 1;
            $search_value = $request['search']['value'];
            $query->where(function ($query) use ($search_value, $searchColumns) {
                for ($i = 0; $i < count($searchColumns); $i++) {
                    if ($i == 0) {
                        $query->where($searchColumns[$i], 'like', "%" . $search_value . "%");
                    } else {
                        $query->orWhere($searchColumns[$i], 'like', "%" . $search_value . "%");
                    }
                }
            });
        }

        $data = $query->get();
        $data = json_decode(json_encode($data), true);

        if ($isFilterApply == 1) {
            $recordsFiltered = count($data);
        }

        foreach ($data as $key => $value) {
            $data[$key]['prod_itemname'] = "<span>" . $data[$key]['itemname'] . "</span> - <span class='text-success'>" . $data[$key]['code'] . "</span>";

            $data[$key]['quot_itemname'] = '
                <div class="col-md-12">
                    <div class="ajax-select">
                        <select class="form-control select2-ajax item_change_select2" id="quot_item_id_' . $key . '" name="quot_item_id_' . $key . '" required> ';
                                $QuotItemId = InventorySync::select('*')->where('prod_composite_item_id', $data[$key]['id'])->first();
                                if(isset($QuotItemId) && $QuotItemId->quot_item_price_id != 0) {
                                    $QuotItemList = Wlmst_ItemPrice::select('wlmst_item_prices.id', DB::raw("CONCAT(wlmst_items.itemname,' ',wlmst_item_prices.code) AS text"));
                                    $QuotItemList->leftJoin('wlmst_items', 'wlmst_items.id', '=', 'wlmst_item_prices.item_id');
                                    $QuotItemList->where('wlmst_item_prices.id', '=', $QuotItemId->quot_item_price_id);
                                    $QuotItemList = $QuotItemList->first();

                                    $data[$key]['quot_itemname'] .= '<option value="' . $QuotItemList->id . '" ' . ($QuotItemList->text ? 'selected' : '') . '>' . $QuotItemList->text . '</option>';
                                }

                            $data[$key]['quot_itemname'] .= '
                            </select>
                            <div class="invalid-feedback">
                                Select Quotation Item.
                            </div>
                            <script type="text/javascript">
                                $(document).ready(function() {
                                    $("#quot_item_id_' . $key . '").select2({
                                        ajax: {
                                            url: "' . route("inventory.sync.search.quot.item") . '",
                                            dataType: "json",
                                            delay: 0,
                                            data: function(params) {
                                                return {
                                                    q: params.term,
                                                    page: params.page
                                                };
                                            },
                                            processResults: function(data, params) {
                                                params.page = params.page || 1;
                    
                                                return {
                                                    results: data.results,
                                                    pagination: {
                                                        more: (params.page * 30) < data.total_count
                                                    }
                                                };
                                            },
                                            cache: false
                                        },
                                        placeholder: "Select Quotation Item",
                                        dropdownParent: $("#datatable"),
                                    });
                                });
                            </script>
                        </div>
                    </div>';

            $data[$key]['so_itemname'] = '
                <div class="col-md-12">
                    <div class="ajax-select">
                        <select class="form-control select2-ajax item_change_select2" id="so_item_id_' . $key . '" name="so_item_id_' . $key . '" required> ';
                                $SoItemId = InventorySync::select('*')->where('prod_composite_item_id', $data[$key]['id'])->first();
                                if(isset($SoItemId) && $SoItemId->so_item_id != 0) {
                                    $SoItemList = ProductInventory::select('product_inventory.id', DB::raw("CONCAT(product_brand.name, ' ', product_code.name) AS text"));
                                    $SoItemList->leftJoin('data_master as product_brand', 'product_brand.id', '=', 'product_inventory.product_brand_id');
                                    $SoItemList->leftJoin('data_master as product_code', 'product_code.id', '=', 'product_inventory.product_code_id');
                                    $SoItemList->where('product_inventory.id', '=', $SoItemId->so_item_id);
                                    $SoItemList = $SoItemList->first();

                                    $data[$key]['so_itemname'] .= '<option value="' . $SoItemList->id . '" ' . ($SoItemList->text ? 'selected' : '') . '>' . $SoItemList->text . '</option>';
                                }
                            $data[$key]['so_itemname'] .= '
                            </select>
                            <div class="invalid-feedback">
                                Select Quotation Item.
                            </div>
                            <script type="text/javascript">
                                $(document).ready(function() {
                                    $("#so_item_id_' . $key . '").select2({
                                        ajax: {
                                            url: "' . route("inventory.sync.search.so.item") . '",
                                            dataType: "json",
                                            delay: 0,
                                            data: function(params) {
                                                return {
                                                    q: params.term,
                                                    page: params.page
                                                };
                                            },
                                            processResults: function(data, params) {
                                                params.page = params.page || 1;
                    
                                                return {
                                                    results: data.results,
                                                    pagination: {
                                                        more: (params.page * 30) < data.total_count
                                                    }
                                                };
                                            },
                                            cache: false
                                        },
                                        placeholder: "Select Sales Item",
                                        dropdownParent: $("#datatable"),
                                    });
                                });
                            </script>
                        </div>
                    </div>';
            $data[$key]['action'] = '<div class="text-center"><button class="btn btn-primary" onclick="saveInventorySync('.$data[$key]['id'].', '.$key.')">Save</button><div>';
        }

        $jsonData = array(
            "draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($recordsTotal), // total number of records
            "recordsFiltered" => intval($recordsFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $data,
        );

        return $jsonData;
    }

    public function save(Request $request) {
        $validator = Validator::make($request->all(), [
            'prod_composite_item_id' => ['required'],
            'quot_item_price_id' => ['required'],
            'so_item_id' => ['required'],
        ]);

        if ($validator->fails()) {
            $response = array();
            $response['status'] = 0;
            $response['msg'] = $validator->errors()->first();
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors();
            return response()->json($response)->header('Content-Type', 'application/json');
        } else {

            $OldValue = InventorySync::select('*');
            $OldValue->where('prod_composite_item_id', $request->prod_composite_item_id);
            $OldValue = $OldValue->first();
            if($OldValue) {
                $oldProdCompositeItemId = $OldValue->prod_composite_item_id;
                $oldQuotItemPriceId = $OldValue->quot_item_price_id;
                $oldSoItemId = $OldValue->so_item_id;
                
                $NewValue = InventorySync::select('*');
                $NewValue->where('prod_composite_item_id', $request->prod_composite_item_id);
                $NewValue = $NewValue->first();
    
                $NewValue->quot_item_price_id = $request->quot_item_price_id;
                $NewValue->so_item_id = $request->so_item_id;
                $NewValue->entryby = Auth::user()->id;
                $NewValue->entryip = $request->ip();
                $NewValue->updateby = Auth::user()->id;
                $NewValue->updateip = $request->ip();
                $NewValue->save();

                if($NewValue) {
                    $isChange = 0;
                    if($NewValue->quot_item_price_id != $oldQuotItemPriceId) {
                        $isChange = 1;
                    } else if($NewValue->so_item_id != $oldSoItemId) {
                        $isChange = 1;
                    }

                    if($isChange == 1) {
                        $InventoryLog = new InventorySyncLog();
                        $InventoryLog->inventory_sync_id = $NewValue->id;
                        $InventoryLog->old_prod_composite_item_id = $oldProdCompositeItemId;
                        $InventoryLog->old_quot_item_price_id = $oldQuotItemPriceId;
                        $InventoryLog->old_so_item_id = $oldSoItemId;
                        $InventoryLog->new_prod_composite_item_id = $NewValue->prod_composite_item_id;
                        $InventoryLog->new_quot_item_price_id = $NewValue->quot_item_price_id;
                        $InventoryLog->new_so_item_id = $NewValue->so_item_id;
                        $InventoryLog->entryby = Auth::user()->id;
                        $InventoryLog->entryip = $request->ip();
                        $InventoryLog->save();
                    }
                    $response = successRes();
                } else {
                    $response = errorRes();
                }
            } else {
                $response = errorRes();
            }

            return response()->json($response)->header('Content-Type', 'application/json');
        }
    }
    
    public function searchQuotItem(Request $request) {
        $SearchValue = isset($request->q) ? $request->q : '';

        $QuotItemList = [];
        $QuotItemList = Wlmst_ItemPrice::select('wlmst_item_prices.id', DB::raw("CONCAT(wlmst_items.itemname,' ',wlmst_item_prices.code) AS text"));
        $QuotItemList->leftJoin('wlmst_items', 'wlmst_items.id', '=', 'wlmst_item_prices.item_id');
        $QuotItemList->where(function ($query) use ($SearchValue) {
            $query->where('wlmst_items.itemname', 'like', '%' . $SearchValue . '%');
            $query->orWhere('wlmst_item_prices.code', 'like', '%' . $SearchValue . '%');
        });
        $QuotItemList->limit(5);
        $QuotItemList = $QuotItemList->get();

        $response = [];
        $response['results'] = $QuotItemList;
        $response['pagination']['more'] = false;
        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }

    public function searchSoItem(Request $request) {
        $SearchValue = isset($request->q) ? $request->q : '';

        $SoItemList = [];
        $SoItemList = ProductInventory::select('product_inventory.id', DB::raw("CONCAT(product_brand.name, ' ', product_code.name) AS text"));
        $SoItemList->leftJoin('data_master as product_brand', 'product_brand.id', '=', 'product_inventory.product_brand_id');
		$SoItemList->leftJoin('data_master as product_code', 'product_code.id', '=', 'product_inventory.product_code_id');
        $SoItemList->where(function ($query) use ($SearchValue) {
            $query->where('product_brand.name', 'like', '%' . $SearchValue . '%');
            $query->orWhere('product_code.name', 'like', '%' . $SearchValue . '%');
        });
        $SoItemList->limit(5);
        $SoItemList = $SoItemList->get();

        $response = [];
        $response['results'] = $SoItemList;
        $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');
    }
}