<?php

namespace App\Http\Controllers\Quotation;

use App\Models\DebugLog;
use App\Models\WlmstItem;
use Illuminate\Http\Request;
use App\Models\Wlmst_ItemPrice;
use App\Models\Wltrn_Quotation;
use App\Models\WlmstItemSubgroup;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Wltrn_QuotItemdetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;

class QuotationConvertationController extends Controller
{
    public function index(Request $request)
    {
        $data = array();
        $data['title'] = "Quotation Convertation";
        $data['quot_id'] = $request->quotno;
        $data['quotgroup_id'] = $request->quotgroup_id;
        return  $data['view'] = view('quotation/quotation_convertation/index', compact('data'))->render();
    }

    public function PostQuotItemList(Request $request)
    {
        DB::enableQueryLog();
        $searchColumns = [
            0 => 'wlmst_items.itemname',
            1 => 'wlmst_item_categories.itemcategoryname',
        ];

        $columns = [
            'wlmst_item_prices.id as priceid',
            'wlmst_items.id as itemid',
            'wlmst_items.itemname',
            'wlmst_items.app_display_name',
            'wlmst_items.module',
            'wlmst_items.max_module',
            'wlmst_items.is_special',
            'wlmst_items.additional_remark',
            'wlmst_items.remark',
            'wlmst_items.itemcategory_id',
            'wlmst_item_categories.itemcategoryname',
            'wlmst_item_prices.mrp',
            'wlmst_item_prices.discount',
            'wlmst_item_prices.company_id',
            'wlmst_item_prices.item_type',
            'wlmst_companies.companyname',
            'wlmst_item_prices.itemgroup_id',
            'wlmst_item_groups.itemgroupname',
            'wlmst_item_prices.itemsubgroup_id',
            'wlmst_item_subgroups.itemsubgroupname',
            'wlmst_item_prices.image',
            'wlmst_item_prices.code',
            'wlmst_item_prices.code AS product_code',
            'wlmst_items.itemname AS product_code_name',
        ];

        $response = [];
        $status = 0;
        $status_code = http_response_code();
        $msg = 'api';
        $data = null;

        try {
            $response = [];
            $status = 1;
            $msg = 'Quotation Item List Success';

            // $request
            $query = WlmstItem::query();
            $query->select($columns);

            $query->leftJoin('wlmst_item_categories', 'wlmst_items.itemcategory_id', '=', 'wlmst_item_categories.id');
            $query->leftJoin('wlmst_item_prices', 'wlmst_items.id', '=', 'wlmst_item_prices.item_id');
            $query->leftJoin('wlmst_companies', 'wlmst_companies.id', '=', 'wlmst_item_prices.company_id');
            $query->leftJoin('wlmst_item_groups', 'wlmst_item_groups.id', '=', 'wlmst_item_prices.itemgroup_id');
            $query->leftJoin('wlmst_item_subgroups', 'wlmst_item_subgroups.id', '=', 'wlmst_item_prices.itemsubgroup_id');

            $query->where('wlmst_items.isactive', 1);
            $query->where('wlmst_item_prices.isactive', 1);
            $query->orderBy('wlmst_items.app_sequence', 'ASC');

            if (isset($request->quot_id) || isset($request->quotgroup_id) || isset($request->room_no) || isset($request->board_no)) {
                $Quot_Id = $request->quot_id;
                $Quotgroup_Id = $request->quotgroup_id;
                $Room_No = $request->room_no;
                $Board_No = $request->board_no;

                $query->selectRaw('case when wltrn_quot_itemdetails.qty is null then 0 else wltrn_quot_itemdetails.qty end as qty');
                $query->selectRaw('case when wltrn_quot_itemdetails.qty is null then wlmst_item_prices.mrp else wltrn_quot_itemdetails.rate end as mrp');
                $query->selectRaw('case when wltrn_quot_itemdetails.qty is null then 0 else wltrn_quot_itemdetails.net_amount end as net_amount');

                $query->leftJoin('wltrn_quot_itemdetails', function ($query) use ($Quot_Id, $Quotgroup_Id, $Room_No, $Board_No) {
                    $query->where('wltrn_quot_itemdetails.quot_id', '=', $Quot_Id);
                    $query->where('wltrn_quot_itemdetails.quotgroup_id', '=', $Quotgroup_Id);
                    $query->where('wltrn_quot_itemdetails.room_no', '=', $Room_No);
                    $query->where('wltrn_quot_itemdetails.board_no', '=', $Board_No);
                    $query->on('wltrn_quot_itemdetails.item_id', '=', 'wlmst_item_prices.item_id');
                });
            } else {
                $query->addSelect(DB::raw("'0' as qty"));
            }

            if (isset($request->range_subgroup)) {
                $Range_Subgroup = explode(',', $request->range_subgroup);
                $query->where(function ($query) use ($Range_Subgroup) {
                    for ($i = 0; $i < count($Range_Subgroup); $i++) {
                        $range_group_id = WlmstItemSubgroup::find($Range_Subgroup[$i])->itemgroup_id;
                        $range_subgroup_id = $Range_Subgroup[$i];
                        if ($i == 0) {
                            $query->where(function ($query) use ($range_group_id, $range_subgroup_id) {
                                $query->whereIn('wlmst_item_prices.itemgroup_id', [$range_group_id])->whereIn('wlmst_item_prices.itemsubgroup_id', [$range_subgroup_id]);
                            });
                        } else {
                            $query->orWhere(function ($query) use ($range_group_id, $range_subgroup_id) {
                                $query->whereIn('wlmst_item_prices.itemgroup_id', [$range_group_id])->whereIn('wlmst_item_prices.itemsubgroup_id', [$range_subgroup_id]);
                            });
                        }
                    }
                });
            }

            if (isset($request->company_id)) {
                $query->where('wlmst_item_prices.company_id', $request->company_id);
            }

            if (isset($request->itemgroup_id)) {
                $query->where('wlmst_item_prices.itemgroup_id', $request->itemgroup_id);
            }

            if (isset($request->itemsubgroup_id)) {
                $query->whereIn('wlmst_item_prices.itemsubgroup_id', explode(',', $request->itemsubgroup_id));
            }

            if (isset($request->itemcategory_id)) {
                $query->whereRaw('find_in_set(' . $request->itemcategory_id . ',wlmst_items.itemcategory_id)');
            }

            if (isset($request->type)) {
                $query->whereRaw("find_in_set('" . $request->type . "',wlmst_item_prices.item_type)");
            }

            if (isset($request->q)) {
                $search_value = $request->q;
                $query->where(function ($query) use ($search_value, $searchColumns) {
                    for ($i = 0; $i < count($searchColumns); $i++) {
                        if ($i == 0) {
                            $query->where($searchColumns[$i], 'like', '%' . $search_value . '%');
                        } else {
                            $query->orWhere($searchColumns[$i], 'like', '%' . $search_value . '%');
                        }
                    }
                });
            }

            if (isset($request->limit)) {
                $data = $query->limit($request->limit);
            }

            if (isset($request->pagination)) {
                $data = $query->get();
            } else {
                $data = $query->paginate(20);
            }

            foreach ($data as $key => $value) {
                if ($value->image == null) {
                    $data[$key]['image'] = 'http://axoneerp.whitelion.in/assets/images/logo.svg';
                } else {
                    $data[$key]['image'] = getSpaceFilePath($value->image);
                }
            }
        } catch (QueryException $ex) {
            $response = [];
            $status = 0;
            $msg = $query;
            $data = $ex;
        }

        $response['status'] = $status;
        $response['status_code'] = $status_code;
        $response['msg'] = $msg;
        $response['data'] = $data;

        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function PostQuotRoomList(Request $request)
    {
        $searchColumns = [
            0 => 'wltrn_quot_itemdetails.room_name',
        ];

        $room_columns = [
            'wltrn_quot_itemdetails.room_no', 
            'wltrn_quot_itemdetails.room_name', 
            'wltrn_quot_itemdetails.room_range', 
            'wltrn_quot_itemdetails.quot_id', 
            'wltrn_quot_itemdetails.quotgroup_id', 
            'wltrn_quot_itemdetails.srno', 
            'wltrn_quot_itemdetails.isactiveroom'
        ];

        $response = [];
        $status = 0;
        $status_code = http_response_code();
        $msg = 'api';
        $data = null;

        try {
            $response = [];
            $status = 1;
            $msg = 'Quotation Room List Success';

            $room_query = Wltrn_QuotItemdetail::query();
            $room_query->select($room_columns);
            $room_query->where([['wltrn_quot_itemdetails.quot_id', $request->quot_id], ['wltrn_quot_itemdetails.quotgroup_id', $request->quotgroup_id]]);
            $room_query->groupBy($room_columns);
            $room_query->orderBy('wltrn_quot_itemdetails.room_no', 'ASC');
            if (isset($request->q)) {
                $search_value = $request->q;
                $room_query->where(function ($query) use ($search_value, $searchColumns) {
                    for ($i = 0; $i < count($searchColumns); $i++) {
                        if ($i == 0) {
                            $query->where($searchColumns[$i], 'like', '%' . $search_value . '%');
                        } else {
                            $query->orWhere($searchColumns[$i], 'like', '%' . $search_value . '%');
                        }
                    }
                });
            }

            $roomlist = $room_query->get();
            $quot_array = [];
            foreach ($roomlist as $key => $value) {
                $quot_f_array = [];

                if ($value['room_range'] != '' || $value['room_range'] != null) {
                    $Range_Subgroup = explode(',', $value['room_range']);
                    $range_group = '';
                    $range_company = '';
                    for ($i = 0; $i < count($Range_Subgroup); $i++) {
                        $range_group .= WlmstItemSubgroup::find($Range_Subgroup[$i])->itemgroup_id . ',';
                        $range_company .= WlmstItemSubgroup::find($Range_Subgroup[$i])->company_id . ',';
                    }
                    $range_group_f = substr($range_group, 0, -1);
                    $range_company_f = explode(',', $range_company)[0];
                } else {
                    $range_group_f = '';
                    $range_company_f = '';
                }

                $room_addon_query = WlmstItem::query();
                $room_addon_query->select('wlmst_items.id');
                $room_addon_query->leftJoin('wlmst_item_categories', 'wlmst_items.itemcategory_id', '=', 'wlmst_item_categories.id');
                $room_addon_query->leftJoin('wlmst_item_prices', 'wlmst_items.id', '=', 'wlmst_item_prices.item_id');
                $room_addon_query->leftJoin('wlmst_companies', 'wlmst_companies.id', '=', 'wlmst_item_prices.company_id');
                $room_addon_query->leftJoin('wlmst_item_groups', 'wlmst_item_groups.id', '=', 'wlmst_item_prices.itemgroup_id');
                $room_addon_query->leftJoin('wlmst_item_subgroups', 'wlmst_item_subgroups.id', '=', 'wlmst_item_prices.itemsubgroup_id');
                $room_addon_query->whereRaw("find_in_set('4',wlmst_items.itemcategory_id)");
                $data_room_addon = $room_addon_query->get();

                $room_addon_qry = Wltrn_QuotItemdetail::query();
                $room_addon_qry->select('wlmst_item_prices.id as priceid', 'wlmst_items.id as itemid', 'wlmst_items.itemname', 'wlmst_items.app_display_name', 'wlmst_items.module', 'wlmst_items.max_module', 'wlmst_items.is_special', 'wlmst_items.additional_remark', 'wlmst_items.remark', 'wlmst_items.itemcategory_id', 'wlmst_item_categories.itemcategoryname', 'wlmst_item_prices.mrp', 'wlmst_item_prices.discount', 'wlmst_item_prices.company_id', 'wlmst_item_prices.item_type', 'wlmst_companies.companyname', 'wlmst_item_prices.itemgroup_id', 'wlmst_item_groups.itemgroupname', 'wlmst_item_prices.itemsubgroup_id', 'wlmst_item_subgroups.itemsubgroupname', 'wlmst_items.image', 'wlmst_item_prices.code', 'wlmst_item_prices.code AS product_code', 'wlmst_items.itemname AS product_code_name', DB::raw('CONCAT(wlmst_item_prices.code," - ", wltrn_quot_itemdetails.qty) AS name'));
                $room_addon_qry->leftJoin('wlmst_items', 'wltrn_quot_itemdetails.item_id', '=', 'wlmst_items.id');
                $room_addon_qry->leftJoin('wlmst_item_prices', 'wltrn_quot_itemdetails.item_price_id', '=', 'wlmst_item_prices.id');
                $room_addon_qry->leftJoin('wlmst_item_categories', 'wltrn_quot_itemdetails.itemcategory_id', '=', 'wlmst_item_categories.id');
                $room_addon_qry->leftJoin('wlmst_companies', 'wltrn_quot_itemdetails.company_id', '=', 'wlmst_companies.id');
                $room_addon_qry->leftJoin('wlmst_item_groups', 'wltrn_quot_itemdetails.itemgroup_id', '=', 'wlmst_item_groups.id');
                $room_addon_qry->leftJoin('wlmst_item_subgroups', 'wltrn_quot_itemdetails.itemsubgroup_id', '=', 'wlmst_item_subgroups.id');
                $room_addon_qry->whereIn('wltrn_quot_itemdetails.item_id', $data_room_addon);
                $room_addon_qry->where([['wltrn_quot_itemdetails.quot_id', $request->quot_id], ['wltrn_quot_itemdetails.quotgroup_id', $request->quotgroup_id], ['wltrn_quot_itemdetails.room_no', $value['room_no']], ['wltrn_quot_itemdetails.board_no', 0]]);

                $quot_f_array = $value;
                $quot_f_array['range_group'] = $range_group_f;
                $quot_f_array['range_company'] = $range_company_f;
                $quot_f_array['room_addon'] = $room_addon_qry->get();
                array_push($quot_array, $quot_f_array);
            }

            $data = $quot_array;
        } catch (QueryException $ex) {
            $response = [];
            $status = 0;
            $msg = 'Please Contact To Admin';
            $data = $ex;
        }

        $view = view('quotation/quotation_convertation/room_detail', compact('data'))->render();

        $response['status'] = $status;
        $response['status_code'] = $status_code;
        $response['msg'] = $msg;
        $response['view'] = $view;
        $response['data'] = $data;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function PostQuotRoomWiseBoardList(Request $request)
    {
        DB::enableQueryLog();
        $columns = [
            'wltrn_quot_itemdetails.isactiveboard', 
            'wltrn_quot_itemdetails.board_image', 
            'wltrn_quot_itemdetails.quot_id', 
            'wltrn_quot_itemdetails.quotgroup_id', 
            'wltrn_quot_itemdetails.srno', 
            'wltrn_quot_itemdetails.isactiveboard', 
            'wltrn_quot_itemdetails.room_no', 
            'wltrn_quot_itemdetails.board_item_id', 
            'wltrn_quot_itemdetails.item_type', 
            'wltrn_quot_itemdetails.board_no', 
            'wltrn_quot_itemdetails.board_name'
        ];

        $response = [];
        $status = 0;
        $status_code = http_response_code();
        $msg = 'api';
        $data = null;

        try {
            $response = [];
            $status = 1;
            $msg = 'Quotation Room List Success';

            $board_query = Wltrn_QuotItemdetail::query();
            $board_query->select($columns);
            $board_query->leftJoin('wlmst_items', 'wltrn_quot_itemdetails.board_item_id', '=', 'wlmst_items.id');
            $board_query->where('wltrn_quot_itemdetails.board_no', '!=', '0');
            $board_query->where([['wltrn_quot_itemdetails.quot_id', $request->quot_id], ['wltrn_quot_itemdetails.quotgroup_id', $request->quotgroup_id], ['wltrn_quot_itemdetails.room_no', $request->room_id]]);
            $board_query->orderBy('wltrn_quot_itemdetails.board_no', 'asc');
            $board_query->groupBy($columns);

            $boardlist = $board_query->get();
            $quot_array = [];
            foreach ($boardlist as $key => $board_value) {
                $quot_f_array = [];

                $board_range_query = Wltrn_QuotItemdetail::query();
                $board_range_query->select('wltrn_quot_itemdetails.board_range');
                $board_range_query->where([['wltrn_quot_itemdetails.quot_id', $request->quot_id], ['wltrn_quot_itemdetails.quotgroup_id', $request->quotgroup_id], ['wltrn_quot_itemdetails.room_no', $request->room_id], ['wltrn_quot_itemdetails.board_no', $board_value->board_no]]);
                $board_range_query = $board_range_query->first();

                if ($board_range_query->board_range != '' || $board_range_query->board_range != null) {
                    $Range_Subgroup = explode(',', $board_range_query->board_range);
                    $range_group = '';
                    $range_company = '';
                    for ($i = 0; $i < count($Range_Subgroup); $i++) {
                        $range_group .= WlmstItemSubgroup::find($Range_Subgroup[$i])->itemgroup_id . ',';
                        $range_company .= WlmstItemSubgroup::find($Range_Subgroup[$i])->company_id . ',';
                    }
                    $range_group_f = substr($range_group, 0, -1);
                    $range_company_f = explode(',', $range_company)[0];
                } else {
                    $range_group_f = ' ';
                    $range_company_f = ' ';
                }

                $quot_f_array = $board_value;
                if ($board_value->board_no == 0) {
                    $Board_Name = 'Room Addon';
                } else {
                    $Board_Name = $board_value->board_name;
                }
                $quot_f_array['board_name'] = $Board_Name; //Live
                $quot_f_array['image'] = getSpaceFilePath($board_value->board_image); //Live
                $quot_f_array['range_group'] = $range_group_f;
                $quot_f_array['board_range'] = $board_range_query->board_range;
                $quot_f_array['range_company'] = $range_company_f;

                $board_items_column = ['wlmst_item_prices.id as priceid', 'wlmst_items.id as itemid', 'wlmst_items.itemname', 'wlmst_items.app_display_name', 'wlmst_item_prices.company_id', 'wlmst_companies.companyname', 'wlmst_items.itemcategory_id', 'wlmst_item_categories.itemcategoryname', 'wlmst_item_prices.itemgroup_id', 'wlmst_item_groups.itemgroupname', 'wlmst_item_prices.itemsubgroup_id', 'wlmst_item_subgroups.itemsubgroupname', 'wlmst_items.module', 'wltrn_quot_itemdetails.qty', 'wlmst_items.max_module', 'wlmst_items.is_special', 'wlmst_items.additional_remark', 'wltrn_quot_itemdetails.discper as discount', 'wlmst_items.remark', 'wlmst_item_prices.mrp', 'wlmst_item_prices.image', 'wlmst_item_prices.code', 'wlmst_item_prices.code AS product_code_name'];
                $board_items_qry = Wltrn_QuotItemdetail::query();
                $board_items_qry->select($board_items_column);
                $board_items_qry->leftJoin('wlmst_items', 'wltrn_quot_itemdetails.item_id', '=', 'wlmst_items.id');
                $board_items_qry->leftJoin('wlmst_item_prices', 'wltrn_quot_itemdetails.item_price_id', '=', 'wlmst_item_prices.id');
                $board_items_qry->leftJoin('wlmst_item_categories', 'wltrn_quot_itemdetails.itemcategory_id', '=', 'wlmst_item_categories.id');
                $board_items_qry->leftJoin('wlmst_companies', 'wltrn_quot_itemdetails.company_id', '=', 'wlmst_companies.id');
                $board_items_qry->leftJoin('wlmst_item_groups', 'wltrn_quot_itemdetails.itemgroup_id', '=', 'wlmst_item_groups.id');
                $board_items_qry->leftJoin('wlmst_item_subgroups', 'wltrn_quot_itemdetails.itemsubgroup_id', '=', 'wlmst_item_subgroups.id');
                $board_items_qry->where('wltrn_quot_itemdetails.item_id', '<>', $board_value->board_item_id);
                $board_items_qry->where([['wltrn_quot_itemdetails.quot_id', $request->quot_id], ['wltrn_quot_itemdetails.quotgroup_id', $request->quotgroup_id], ['wltrn_quot_itemdetails.room_no', $request->room_id], ['wltrn_quot_itemdetails.board_no', $board_value->board_no]]);
                $range_group_new = [];
                $range_group = [];
                $item_name = '';
                foreach ($board_items_qry->get() as $key => $value) {
                    if ($value->image == null) {
                        $value['image'] = 'http://axoneerp.whitelion.in/assets/images/logo.svg';
                    } else {
                        $value['image'] = getSpaceFilePath($value->image);
                    }
                    $value['is_addons'] = $value->itemcategory_id == 6 ? 1 : 0;
                    if ($value->itemcategory_id == 6) {
                        $item_name .= $value->itemname . ',';
                    }
                    $range_group_new[$value->priceid] = $value;
                    $range_group = [];
                    array_push($range_group, $range_group_new);
                }
                $range_group_new = [];

                $board_addon_query = WlmstItem::query();
                $board_addon_query->select('wlmst_items.id');
                $board_addon_query->leftJoin('wlmst_item_categories', 'wlmst_items.itemcategory_id', '=', 'wlmst_item_categories.id');
                $board_addon_query->leftJoin('wlmst_item_prices', 'wlmst_items.id', '=', 'wlmst_item_prices.item_id');
                $board_addon_query->leftJoin('wlmst_companies', 'wlmst_companies.id', '=', 'wlmst_item_prices.company_id');
                $board_addon_query->leftJoin('wlmst_item_groups', 'wlmst_item_groups.id', '=', 'wlmst_item_prices.itemgroup_id');
                $board_addon_query->leftJoin('wlmst_item_subgroups', 'wlmst_item_subgroups.id', '=', 'wlmst_item_prices.itemsubgroup_id');
                if ($board_value->board_no == 0) {
                    $board_addon_query->whereRaw("find_in_set('4',wlmst_items.itemcategory_id)");
                } else {
                    $board_addon_query->whereRaw("find_in_set('6',wlmst_items.itemcategory_id)");
                }
                $data_board_addon = $board_addon_query->get();

                $board_addon_qry = Wltrn_QuotItemdetail::query();
                $board_addon_qry->select(DB::raw('CONCAT(wlmst_item_prices.code," - ", wltrn_quot_itemdetails.qty) AS name'));
                $board_addon_qry->leftJoin('wlmst_items', 'wltrn_quot_itemdetails.item_id', '=', 'wlmst_items.id');
                $board_addon_qry->leftJoin('wlmst_item_prices', 'wltrn_quot_itemdetails.item_price_id', '=', 'wlmst_item_prices.id');
                $board_addon_qry->leftJoin('wlmst_item_categories', 'wltrn_quot_itemdetails.itemcategory_id', '=', 'wlmst_item_categories.id');
                $board_addon_qry->leftJoin('wlmst_companies', 'wltrn_quot_itemdetails.company_id', '=', 'wlmst_companies.id');
                $board_addon_qry->leftJoin('wlmst_item_groups', 'wltrn_quot_itemdetails.itemgroup_id', '=', 'wlmst_item_groups.id');
                $board_addon_qry->leftJoin('wlmst_item_subgroups', 'wltrn_quot_itemdetails.itemsubgroup_id', '=', 'wlmst_item_subgroups.id');
                $board_addon_qry->whereIn('wltrn_quot_itemdetails.item_id', $data_board_addon);
                $board_addon_qry->where([['wltrn_quot_itemdetails.quot_id', $request->quot_id], ['wltrn_quot_itemdetails.quotgroup_id', $request->quotgroup_id], ['wltrn_quot_itemdetails.room_no', $request->room_id], ['wltrn_quot_itemdetails.board_no', $board_value->board_no]]);

                $quot_f_array['itemname'] = rtrim($item_name, ',');
                if (!empty($range_group)) {
                    $quot_f_array['board_item'] = $range_group[0];
                } else {
                    $quot_f_array['board_item'] = 'null';
                }
                $quot_f_array['board_addon'] = $board_addon_qry->get();

                array_push($quot_array, $quot_f_array);
            }

            $data = $quot_array;
        } catch (QueryException $ex) {
            $response = [];
            $status = 0;
            $msg = 'Please Contact To Admin';
            $data = $ex;
        }

        $view = view('quotation/quotation_convertation/board_detail', compact('data'))->render();

        $response['status'] = $status;
        $response['status_code'] = $status_code;

        $response['msg'] = $msg;
        $response['view'] = $view;
        $response['data'] = $data;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function searchPlate(Request $request) {
        $searchKeyword = isset($request->q) ? $request->q : "";

        $perameater_request = new Request();
        $perameater_request['quot_id'] = $request->quot_id;
        $perameater_request['quotgroup_id'] = $request->quotgroup_id;
        $perameater_request['itemgroup_id'] = 4;
        if($request->type == "POSH") {
            $perameater_request['item_type'] = "QUARTZ";
        } else {
            $perameater_request['range_subgroup'] = $request->range_subgroup;
            $perameater_request['item_type'] = "POSH";
        }
        $perameater_request['q'] = $searchKeyword;
        $perameater_request['pagination'] = "";
        $perameater_request['limit'] = 5;
        $PlateList = $this->PostQuotItemList($perameater_request);

        $data = array();
        foreach ($PlateList->original['data'] as $key => $value) {
            $data[$key]['id'] = $value['priceid'];
            $data[$key]['text'] = $value['itemname'];
        }
        // $PlateList = Wlmst_ItemPrice::select('wlmst_item_prices.item_id as id', 'wlmst_items.itemname as text');
        // $PlateList->leftjoin('wlmst_items', 'wlmst_items.id', '=', 'wlmst_item_prices.item_id');
        // $PlateList->where('wlmst_item_prices.itemgroup_id', 4);
        // $PlateList->where('wlmst_items.itemname', 'like', "%" . $searchKeyword . "%");
        // $PlateList->groupBy('wlmst_item_prices.item_id', 'wlmst_items.itemname');
        // $PlateList->limit(5);
        // $PlateList = $PlateList->get();

        $response = array();
        $response['results'] = $data;
        $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function searchAccessories(Request $request) {
        $searchKeyword = isset($request->q) ? $request->q : "";

        $perameater_request = new Request();
        $perameater_request['quot_id'] = $request->quot_id;
        $perameater_request['quotgroup_id'] = $request->quotgroup_id;
        $perameater_request['itemgroup_id'] = 2;
        if($request->type == "POSH") {
            $perameater_request['item_type'] = "QUARTZ";
        } else {
            $perameater_request['range_subgroup'] = $request->range_subgroup;
            $perameater_request['item_type'] = "POSH";
        }
        $perameater_request['q'] = $searchKeyword;
        $perameater_request['pagination'] = "";
        $perameater_request['limit'] = 5;
        $AccessoriesList = $this->PostQuotItemList($perameater_request);

        $data = array();
        foreach ($AccessoriesList->original['data'] as $key => $value) {
            $data[$key]['id'] = $value['priceid'];
            $data[$key]['text'] = $value['itemname'];
        }

        $response = array();
        $response['results'] = $data;
        $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function searchWhitelionModel(Request $request) {
        $searchKeyword = isset($request->q) ? $request->q : "";

        $perameater_request = new Request();
        $perameater_request['quot_id'] = $request->quot_id;
        $perameater_request['quotgroup_id'] = $request->quotgroup_id;
        $perameater_request['itemgroup_id'] = 1;
        if($request->type == "POSH") {
            $perameater_request['itemsubgroup_id'] = $request->sub_group;
            $perameater_request['item_type'] = "QUARTZ";
        } else {
            $perameater_request['range_subgroup'] = $request->range_subgroup;
            $perameater_request['item_type'] = "POSH";
        }
        $perameater_request['q'] = $searchKeyword;
        $perameater_request['pagination'] = "";
        $perameater_request['limit'] = 5;
        $WhitelionModel = $this->PostQuotItemList($perameater_request);

        $data = array();
        foreach ($WhitelionModel->original['data'] as $key => $value) {
            $data[$key]['id'] = $value['priceid'];
            $data[$key]['text'] = $value['itemname'];
        }

        $response = array();
        $response['results'] = $data;
        $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function searchAddon(Request $request) {
        $searchKeyword = isset($request->q) ? $request->q : "";

        $perameater_request = new Request();
        $perameater_request['quot_id'] = $request->quot_id;
        $perameater_request['quotgroup_id'] = $request->quotgroup_id;
        $perameater_request['itemgroup_id'] = 3;
        if($request->type == "POSH") {
            $perameater_request['item_type'] = "QUARTZ";
        } else {
            $perameater_request['item_type'] = "POSH";
        }
        $perameater_request['q'] = $searchKeyword;
        $perameater_request['pagination'] = "";
        $perameater_request['limit'] = 5;
        $AddonList = $this->PostQuotItemList($perameater_request);

        $data = array();
        foreach ($AddonList->original['data'] as $key => $value) {
            $data[$key]['id'] = $value['priceid'];
            $data[$key]['text'] = $value['itemname'];
        }

        $response = array();
        $response['results'] = $data;
        $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function PostQuotRoomNBoardSave(Request $request)
    {
        $request_data = $request->input();
        $validator = Validator::make($request->input(), [
            'room' => ['required'],
        ]);

        if(isset($request_data['item'])) {
            if (count($request_data['item']) >= 1) {
                foreach ($request_data['item'] as $Valid_value) {
                    if($Valid_value['item_price_id'] == null || $Valid_value['item_price_id'] == "") {
                        $response = errorRes('Please Select Proper Item');
                        return response()->json($response)->header('Content-Type', 'application/json');
                    }

                    if ($Valid_value['qty'] == null || $Valid_value['qty'] == "" || $Valid_value['qty'] == 0) {
                        $response = errorRes('Please Enter Proper Qty');
                        return response()->json($response)->header('Content-Type', 'application/json');
                    }
                }
            }
        }
        if ($validator->fails()) {
            $response = successRes($validator->errors());
            return response()->json($response)->header('Content-Type', 'application/json');
        } else {
            $quot_id = $request['room'][0]['quot_id'];
            $quotgroup_id = $request['room'][0]['quotgroup_id'];
            $room_no = $request['room'][0]['room_no'];
            $board_no = $request['room'][0]['board_no'];

            $ItemDetails = Wltrn_QuotItemdetail::query();
            $ItemDetails->where('wltrn_quot_itemdetails.quot_id', $quot_id);
            $ItemDetails->where('wltrn_quot_itemdetails.quotgroup_id', $quotgroup_id);
            $ItemDetails->where('wltrn_quot_itemdetails.room_no', $room_no);
            $ItemDetails->where('wltrn_quot_itemdetails.board_no', $board_no);
            $ItemDetails = $ItemDetails->first();

            if($ItemDetails) {

                $Quotation = Wltrn_Quotation::find($quot_id);
                $room_adon = $ItemDetails;
                $room_name = $ItemDetails->room_name;
                $board_name = $ItemDetails->board_name;
                $board_size = $ItemDetails->board_size;
                $board_item_id = $ItemDetails->board_item_id;
                $board_item_price_id = $ItemDetails->board_item_price_id;
                $itemdescription = $request->notes;
                $default_range = $Quotation->default_range;
                $room_range = $ItemDetails->room_range;
                $board_range = $ItemDetails->board_range;
                $board_image = $ItemDetails->board_image;

                /* ------------------- END ------------------- */
                // $QuotMaster = Wltrn_Quotation::find($quot_id);
                // $QuotMaster->default_range = $default_range;
                // $QuotMaster->save();

                $DeleteOldEntry = Wltrn_QuotItemdetail::where([['wltrn_quot_itemdetails.quot_id', $quot_id], ['wltrn_quot_itemdetails.quotgroup_id', $quotgroup_id], ['wltrn_quot_itemdetails.room_no', $room_no], ['wltrn_quot_itemdetails.board_no', $board_no]])->delete();

                if($DeleteOldEntry) {

                    if ($board_image != '') {
                        $folderPathofFile = '/quotation/board';
                        $fileObject1 = base64_decode($board_image);
                        $extension = '.png';
                        $fileName1 = uniqid() . '_' . $quot_id . '_' . $quotgroup_id . '_' . $room_no . '_' . $board_no . $extension;
                        $destinationPath = public_path($folderPathofFile);

                        if (!File::exists($destinationPath)) {
                            File::makeDirectory($destinationPath);
                        }

                        file_put_contents($destinationPath . '/' . $fileName1, $fileObject1);

                        if (File::exists(public_path($folderPathofFile . '/' . $fileName1))) {
                            $Image_Path = $folderPathofFile . '/' . $fileName1;
                            //START UPLOAD FILE ON SPACES
                            $spaceUploadResponse = uploadFileOnSpaces(public_path($Image_Path), $Image_Path); //Live

                            if ($spaceUploadResponse != 1) {
                                $Image_Path = '';
                            } else {
                                unlink(public_path($Image_Path));
                            }
                            //END UPLOAD FILE ON SPACES
                        } else {
                            $Image_Path = '/assets/images/logo.png';
                        }
                    } else {
                        $Image_Path = '/assets/images/logo.png';
                    }

                    $BoardSaveLog = [];
                    $BoardSaveLog['quot_id'] = $quot_id;
                    $BoardSaveLog['quotgroup_id'] = $quotgroup_id;
                    $BoardSaveLog['room_no'] = $room_no;
                    $BoardSaveLog['board_no'] = $board_no;
                    $BoardSaveLog['description'] = '';
                    $BoardSaveLog['source'] = "WEB";
                    $BoardSaveLog['entryip'] = $request->ip();
                    saveBoardSaveLog($BoardSaveLog);

                    if(isset($request_data['item'])) {
                        if (count($request_data['item']) >= 1) {
                            foreach ($request_data['item'] as $value) {
                                if (intval($value['qty']) != 0) {
                                    $QuotationMaster = Wltrn_Quotation::find($quot_id);
                                    $ItemPriceMaster = Wlmst_ItemPrice::find($value['item_price_id']);
                                    $ItemMaster = WlmstItem::find($ItemPriceMaster['item_id']);

                                    $SubTotal = floatval($ItemPriceMaster['mrp']) * floatval($value['qty']);
                                    $Discount_Amount = (floatval($SubTotal) * floatval($ItemPriceMaster['discount'])) / 100;
                                    $GrossAmount = floatval($SubTotal) - floatval($Discount_Amount);
                                    if ($QuotationMaster->site_state_id == '9' /*IS GUJARAT*/) {
                                        /* CGST CALCULATION */
                                        $CGST_Per = $ItemMaster->cgst_per;
                                        $CGST_Amount = (floatval($GrossAmount) * floatval($ItemMaster->cgst_per)) / 100;
                                        /* SGST CALCULATION */
                                        $SGST_Per = $ItemMaster->sgst_per;
                                        $SGST_Amount = (floatval($GrossAmount) * floatval($ItemMaster->sgst_per)) / 100;
                                        /* IGST CALCULATION */
                                        $IGST_Per = '0.00';
                                        $IGST_Amount = '0.00';

                                        /* NET AMOUNT CALCULATION */
                                        $NetTotalAmount = floatval($GrossAmount) + floatval($CGST_Amount) + floatval($SGST_Amount);
                                        /* ROUND_UP AMOUNT CALCULATION */
                                        $RoundUpAmount = floatval($NetTotalAmount) - floatval(round($NetTotalAmount));
                                        /* NET FINAL AMOUNT CALCULATION */
                                        $NetAmount = round($NetTotalAmount);
                                    } else {
                                        /* CGST CALCULATION */
                                        $CGST_Per = '0';
                                        $CGST_Amount = '0.00';
                                        /* SGST CALCULATION */
                                        $SGST_Per = '0';
                                        $SGST_Amount = '0.00';
                                        /* IGST CALCULATION */
                                        $IGST_Per = $ItemMaster->igst_per;
                                        $IGST_Amount = (floatval($GrossAmount) * floatval($ItemMaster->igst_per)) / 100;

                                        /* NET AMOUNT CALCULATION */
                                        $NetTotalAmount = floatval($GrossAmount) + floatval($IGST_Amount);
                                        /* ROUND_UP AMOUNT CALCULATION */
                                        $RoundUpAmount = floatval($NetTotalAmount) - floatval(round($NetTotalAmount));
                                        /* NET FINAL AMOUNT CALCULATION */
                                        $NetAmount = round($NetTotalAmount);
                                    }

                                    $qry_add_quot_item = new Wltrn_QuotItemdetail();
                                    $qry_add_quot_item->quot_id = $quot_id;
                                    $qry_add_quot_item->quotgroup_id = $quotgroup_id;
                                    $qry_add_quot_item->room_no = $room_no;
                                    $qry_add_quot_item->room_name = $room_name;
                                    $qry_add_quot_item->board_no = $board_no;
                                    $qry_add_quot_item->board_name = $board_name;
                                    $qry_add_quot_item->board_size = $board_size;
                                    $qry_add_quot_item->board_item_id = $board_item_id;
                                    $qry_add_quot_item->board_item_price_id = $board_item_price_id;
                                    if ($Image_Path != '') {
                                        $qry_add_quot_item->board_image = $Image_Path;
                                    }
                                    $qry_add_quot_item->itemdescription = $itemdescription;
                                    $qry_add_quot_item->item_id = $ItemPriceMaster['item_id'];
                                    $qry_add_quot_item->item_price_id = $value['item_price_id'];
                                    $qry_add_quot_item->company_id = $ItemPriceMaster['company_id'];
                                    $qry_add_quot_item->itemgroup_id = $ItemPriceMaster['itemgroup_id'];
                                    $qry_add_quot_item->itemsubgroup_id = $ItemPriceMaster['itemsubgroup_id'];
                                    $qry_add_quot_item->itemcategory_id = $ItemMaster['itemcategory_id'];
                                    $qry_add_quot_item->itemcode = $ItemPriceMaster['code'];
                                    $qry_add_quot_item->qty = $value['qty'];
                                    $qry_add_quot_item->rate = $ItemPriceMaster['mrp'];

                                    // $qry_add_quot_item->sequence_no = $value['sequence_no'];

                                    $qry_add_quot_item->discper = $ItemPriceMaster['discount'];
                                    $qry_add_quot_item->discamount = $Discount_Amount;

                                    $qry_add_quot_item->grossamount = $GrossAmount;
                                    $qry_add_quot_item->taxableamount = $GrossAmount;
                                    $qry_add_quot_item->igst_per = $IGST_Per;
                                    $qry_add_quot_item->igst_amount = $IGST_Amount;
                                    $qry_add_quot_item->cgst_per = $CGST_Per;
                                    $qry_add_quot_item->cgst_amount = $CGST_Amount;
                                    $qry_add_quot_item->sgst_per = $SGST_Per;
                                    $qry_add_quot_item->sgst_amount = $SGST_Amount;
                                    $qry_add_quot_item->roundup_amount = $RoundUpAmount;
                                    $qry_add_quot_item->net_amount = $NetAmount;
                                    $qry_add_quot_item->item_type = $request['room'][0]['item_type'];
                                    $qry_add_quot_item->room_range = $room_range;
                                    $qry_add_quot_item->board_range = $board_range;
                                    $qry_add_quot_item->entryby = Auth::user()->id; //Live
                                    $qry_add_quot_item->entryip = $request->ip();
                                    $qry_add_quot_item->save();
                                }
                            }
                        }
                    }
                }
            }

            $DebugLog = new DebugLog();
            $DebugLog->user_id = 1;
            $DebugLog->name = 'quot-quot-master-basicdetail-edit';
            $DebugLog->description = "Quotation Item Detail has been Updated (#Quote ID = " . $quot_id . ") (#Quote Group ID = " . $quotgroup_id . ") (#Room No = " . $room_no . ") (#Board No = " . $board_no . ')';
            $DebugLog->save();

            $response = successRes();
            // $response['data'] = $res_data;

            return response()->json($response)->header('Content-Type', 'application/json');
        }
    }
}
