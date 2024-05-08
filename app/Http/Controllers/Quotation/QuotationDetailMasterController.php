<?php

namespace App\Http\Controllers\Quotation;

use App\Models\Lead;

use App\Models\DebugLog;
use App\Models\WlmstItem;
// use DB;
use Illuminate\Http\Request;
use App\Models\Wlmst_ItemPrice;
use App\Models\Wltrn_Quotation;
use App\Models\WlmstItemSubgroup;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Wlmst_QuotationError;
use App\Models\Wltrn_QuotItemdetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;

class QuotationDetailMasterController extends Controller
{

    public function __construct()
    {
        $this->middleware(function ($request, $next) {

            $tabCanAccessBy = array(0, 1, 13);

            if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
                return redirect()->route('dashboard');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $data = array();
        $data['title'] = "Quotation Master";
        return view('quotation/master/quotation/itemquotedetail', compact('data'));
    }

    public function ajax(Request $request)
    {

        $searchColumns = array(
            'wltrn_quot_itemdetails.id',
            'wltrn_quot_itemdetails.room_no',
            'wltrn_quot_itemdetails.room_name',
            'wltrn_quot_itemdetails.board_no',
            'wltrn_quot_itemdetails.board_name'
        );

        $columns = array(
            'wltrn_quot_itemdetails.quot_id',
            'wltrn_quot_itemdetails.quotgroup_id',
            'wltrn_quot_itemdetails.srno',
            'wltrn_quot_itemdetails.room_no',
            'wltrn_quot_itemdetails.room_name',
            'wltrn_quot_itemdetails.board_no',
            'wltrn_quot_itemdetails.board_name',
            // 'wltrn_quot_itemdetails.board_range',
            'wltrn_quot_itemdetails.isactiveboard',
            'wltrn_quot_itemdetails.isactiveroom',
            'board.module'
            // 'wlmst_quotation_errors.id',
        );

        $recordsTotal = Wltrn_QuotItemdetail::query();
        $recordsTotal->select($columns);
        $recordsTotal->leftJoin('wlmst_companies', 'wlmst_companies.id', '=', 'wltrn_quot_itemdetails.company_id');
        $recordsTotal->leftJoin('wlmst_item_groups', 'wlmst_item_groups.id', '=', 'wltrn_quot_itemdetails.itemgroup_id');
        $recordsTotal->leftJoin('wlmst_item_subgroups', 'wlmst_item_subgroups.id', '=', 'wltrn_quot_itemdetails.itemsubgroup_id');
        $recordsTotal->leftJoin('wlmst_item_categories', 'wlmst_item_categories.id', '=', 'wltrn_quot_itemdetails.itemcategory_id');
        $recordsTotal->leftJoin('wlmst_items AS board', 'board.id', '=', 'wltrn_quot_itemdetails.board_item_id');
        $recordsTotal->where('wltrn_quot_itemdetails.quot_id', $request->quotno);
        // $recordsTotal->groupBy(['wltrn_quot_itemdetails.room_no', 'wltrn_quot_itemdetails.board_no']);
        $recordsTotal->groupBy($columns);
        $recordsTotal = json_decode(json_encode($recordsTotal->get()), true);
        $recordsTotal = count($recordsTotal);
        $recordsFiltered = $recordsTotal;

        $query = Wltrn_QuotItemdetail::query();
        $query->select($columns);

        $query->selectRaw('SUM(wltrn_quot_itemdetails.rate*qty) as mrp');
        $query->selectRaw('SUM(wltrn_quot_itemdetails.discamount) as discamount');
        $query->selectRaw('SUM(wltrn_quot_itemdetails.grossamount) as grossamount');
        $query->selectRaw('SUM(wltrn_quot_itemdetails.igst_amount+wltrn_quot_itemdetails.cgst_amount+wltrn_quot_itemdetails.sgst_amount) as gst');
        $query->selectRaw('SUM(wltrn_quot_itemdetails.net_amount) as net_amount');
        $query->selectRaw('(board.module+board.module)-SUM(wlmst_items.module*wltrn_quot_itemdetails.qty) as remain_module');

        $query->leftJoin('wlmst_items', 'wlmst_items.id', '=', 'wltrn_quot_itemdetails.item_id');
        $query->leftJoin('wlmst_companies', 'wlmst_companies.id', '=', 'wltrn_quot_itemdetails.company_id');
        $query->leftJoin('wlmst_item_groups', 'wlmst_item_groups.id', '=', 'wltrn_quot_itemdetails.itemgroup_id');
        $query->leftJoin('wlmst_item_subgroups', 'wlmst_item_subgroups.id', '=', 'wltrn_quot_itemdetails.itemsubgroup_id');
        $query->leftJoin('wlmst_item_categories', 'wlmst_item_categories.id', '=', 'wltrn_quot_itemdetails.itemcategory_id');
        $query->leftJoin('wlmst_items AS board', 'board.id', '=', 'wltrn_quot_itemdetails.board_item_id');

        $query->where('wltrn_quot_itemdetails.quot_id', $request->quotno);
        // $query->groupBy(['wltrn_quot_itemdetails.room_no', 'wltrn_quot_itemdetails.board_no']);
        $query->groupBy($columns);
        $query->limit($request->length);
        $query->offset($request->start);
        $query->orderBy($columns[$request['order'][0]['column']], $request['order'][0]['dir']);
        $isFilterApply = 0;
        // quotno

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

        $room_data = $query->get();

        $room_data = json_decode(json_encode($room_data), true);

        if ($isFilterApply == 1) {
            $recordsFiltered = count($room_data);
        }

        $data = array();
        foreach ($room_data as $key => $value) {

            $board_active = ($value['isactiveboard'] == 1) ? 'checked' : '';
            $room_active = ($value['isactiveroom'] == 1) ? 'checked' : '';
            $data[$key]['id'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">' . $value['room_no'] . ' (' . $value['board_no'] . ') <br />' .
                '</br>Board<label class="switch"><input type="checkbox" onchange="quotation_board_status(this,' . $value['room_no'] . ',' . $value['board_no'] . ')" ' . $board_active . ' >
            <span class="slider round"></span>
            </label>
            </br>Room
            <label class="switch"><input type="checkbox" onchange="quotation_room_status(this,' . $value['room_no'] . ',' . $value['board_no'] . ')" ' . $room_active . ' >
            <span class="slider round"></span>
            </label>
            </a></h5>
            <br />';

            $data[$key]['room_name'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">' . $value['room_name'] . '</a></h5>
            <p class="font-size-14 text-muted mb-0">' . $room_data[$key]['board_name'] . '</p>';

            $item_columns = array(
                'wltrn_quot_itemdetails.id',
                'wltrn_quot_itemdetails.board_range',
                'wltrn_quot_itemdetails.room_name',
                'wltrn_quot_itemdetails.board_name',
                'wlmst_items.itemname',
                'wlmst_companies.companyname',
                'wlmst_item_groups.itemgroupname',
                'wlmst_item_subgroups.itemsubgroupname',
                'wlmst_item_categories.itemcategoryname',
                'wltrn_quot_itemdetails.qty',
                'wltrn_quot_itemdetails.isactiveboard',
            );

            $item_query = Wltrn_QuotItemdetail::query();
            $item_query->select($item_columns);
            $item_query->leftJoin('wlmst_items', 'wlmst_items.id', '=', 'wltrn_quot_itemdetails.item_id');
            $item_query->leftJoin('wlmst_companies', 'wlmst_companies.id', '=', 'wltrn_quot_itemdetails.company_id');
            $item_query->leftJoin('wlmst_item_groups', 'wlmst_item_groups.id', '=', 'wltrn_quot_itemdetails.itemgroup_id');
            $item_query->leftJoin('wlmst_item_subgroups', 'wlmst_item_subgroups.id', '=', 'wltrn_quot_itemdetails.itemsubgroup_id');
            $item_query->leftJoin('wlmst_item_categories', 'wlmst_item_categories.id', '=', 'wltrn_quot_itemdetails.itemcategory_id');
            $item_query->where('wltrn_quot_itemdetails.quot_id', $value['quot_id']);
            $item_query->where('wltrn_quot_itemdetails.quotgroup_id', $value['quotgroup_id']);
            $item_query->where('wltrn_quot_itemdetails.srno', $value['srno']);
            $item_query->where('wltrn_quot_itemdetails.room_no', $value['room_no']);
            $item_query->where('wltrn_quot_itemdetails.board_no', $value['board_no']);
            $item_query->orderBy('wlmst_item_groups.sequence', 'ASC');
            $itemdata = $item_query->get();
            $item_name = '';
            $item_brand = '';
            $board_range = '';
            foreach ($itemdata as $item_key => $item_value) {
                $board_range = $item_value['board_range'];
                $item_name .= '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">' . $item_value['itemname'] . ' - ' . $item_value['qty'] . ' Pcs</a></h5>';
                $item_brand .= '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">' . $item_value['itemsubgroupname'] . '</a></h5>';
            }
            $data[$key]['itemname'] = $item_name;
            $data[$key]['brand'] = $item_brand;

            $remain_modul_color = floatval($value['remain_module']) == 0 ? '#7ac68f' : '#ff0000';
            $data[$key]['remain_module'] = '<h5 class="font-size-14 mb-1 text-center"><a href="javascript: void(0);" style="color: ' . $remain_modul_color . ';">' . floatval($value['remain_module']) . ' M</a></h5>';

            $data[$key]['mrp'] = '<h5 class="font-size-14 mb-1 text-center"><a href="javascript: void(0);" class="text-dark">Rs. ' . $value['mrp'] . '</a></h5>';

            $data[$key]['dicount'] = '<h5 class="font-size-14 mb-1 text-end"><a href="javascript: void(0);" class="text-dark">Rs. ' . number_format(round($value['discamount']), 2, '.', '') . '</a></h5>
            <h5 class="font-size-14 mb-1 text-end"><a href="javascript: void(0);" class="text-dark">Rs. ' . number_format(round($value['gst']), 2, '.', '') . '</a></h5>';

            // $data[$key]['gross'] = '<h5 class="font-size-14 mb-1 text-center"><a href="javascript: void(0);" class="text-dark">Rs. ' . number_format(round($value['grossamount']), 2, '.', '') . '</a></h5>';

            // $data[$key]['gst'] = '<h5 class="font-size-14 mb-1 text-center"><a href="javascript: void(0);" class="text-dark">Rs. ' . number_format(round($value['gst']),2, '.', '') . '</a></h5>';

            $data[$key]['final'] = '<h5 class="font-size-14 mb-1 text-center"><a href="javascript: void(0);" class="text-dark">Rs. ' . number_format(round($value['net_amount']), 2, '.', '') . '</a></h5>';


            $uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';

            $item_price_detail = Wlmst_QuotationError::where([
                ['wlmst_quotation_errors.quot_id', $value['quot_id']],
                ['wlmst_quotation_errors.quotgroup_id', $value['quotgroup_id']],
                ['wlmst_quotation_errors.roomno', $value['room_no']],
                ['wlmst_quotation_errors.boardno', $value['board_no']]
            ])->first();

            if ($item_price_detail) {
                $uiAction .= '<li class="list-inline-item px-2">';
                $uiAction .= '<a onclick="quot_board_error_detail(' . $value['quot_id'] . ',' . $value['quotgroup_id'] . ',' . $value['room_no'] . ',' . $value['board_no'] . ')" href="javascript: void(0);" title="Quotation Error Detail"><i class="bx bxs-bug" style="color: red;"></i></a>';
                $uiAction .= '</li>';
            }

            $uiAction .= '<li class="list-inline-item px-2">';
            $uiAction .= '<a onclick="quot_board_detail(' . $value['quot_id'] . ',' . $value['quotgroup_id'] . ',' . $value['srno'] . ',' . $value['room_no'] . ',' . $value['board_no'] . ',\'' . $board_range . '\')" href="javascript: void(0);" title="Quotation Item Detail"><i class="bx bx-edit"></i></a>';
            $uiAction .= '</li>';

            $uiAction .= '<li class="list-inline-item px-2">';
            $uiAction .= '<a onclick="delete_board_Warning(' . $value['quot_id'] . ',' . $value['quotgroup_id'] . ',' . $value['room_no'] . ',' . $value['board_no'] . ')" href="javascript: void(0);" title="Delete"><i class="bx bx-trash-alt"></i></a>';
            $uiAction .= '</li>';

            $uiAction .= '</ul>';

            $data[$key]['action'] = $uiAction;
        }

        $jsonData = array(
            "draw" => intval($request['draw']),
            // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($recordsTotal),
            // total number of records
            "recordsFiltered" => intval($recordsFiltered),
            // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $data, // total data array
        );

        return response()->json($jsonData)->header('Content-Type', 'application/json');
        ;
    }

    public function quotationSummaryData(Request $request)
    {
        // DB::enableQueryLog();
        $validator = Validator::make($request->all(), [
            'quot_id' => ['required'],
        ]);

        if ($validator->fails()) {
            $status = 0;
            $message = "Please Enter Valid Perameater";
            $data = $validator->errors();
        } else {

            $quot_basicD_columns = array(
                'wltrn_quotation.id',
                'wltrn_quotation.yy',
                'wltrn_quotation.mm',
                'wltrn_quotation.quotno',
                'wltrn_quotation.quottype_id',
                'wlmst_quotation_type.name as type_name',
                'wltrn_quotation.quot_no_str',
                'wltrn_quotation.site_name',
                // 'wltrn_quotation.customer_name',
                // 'wltrn_quotation.customer_contact_no',
                // 'wltrn_quotation.siteaddress',
                'wltrn_quotation.quot_date',
                'wltrn_quotation.status',
                'wltrn_quotation.default_range',
                'wltrn_quotation.inquiry_id',
                DB::raw("CONCAT(leads.first_name, ' ', leads.last_name) as customer_name"),
                'leads.phone_number as customer_contact_no',
                DB::raw("CONCAT(leads.house_no, ' ', leads.addressline1, ' ', leads.addressline2, ' ', leads.area) as siteaddress")
            );

            $quot_basicD_qry = Wltrn_Quotation::query();
            $quot_basicD_qry->select($quot_basicD_columns);
            $quot_basicD_qry->leftJoin('wlmst_quotation_type', 'wlmst_quotation_type.id', '=', 'wltrn_quotation.quottype_id');
            $quot_basicD_qry->leftJoin('leads', 'leads.id', '=', 'wltrn_quotation.inquiry_id');
            $quot_basicD_qry->where('wltrn_quotation.id', $request->quot_id);
            $quot_basicD = $quot_basicD_qry->first();

            $quot_amountD_qry = Wltrn_QuotItemdetail::query();
            $quot_amountD_qry->selectRaw('SUM(wltrn_quot_itemdetails.grossamount) as gross_amount');
            $quot_amountD_qry->selectRaw('SUM(wltrn_quot_itemdetails.igst_amount) as igst_amount');
            $quot_amountD_qry->selectRaw('SUM(wltrn_quot_itemdetails.cgst_amount) as cgst_amount');
            $quot_amountD_qry->selectRaw('SUM(wltrn_quot_itemdetails.sgst_amount) as sgst_amount');
            $quot_amountD_qry->selectRaw('SUM(wltrn_quot_itemdetails.net_amount) as net_amount');
            $quot_amountD_qry->where(
                [
                    ['wltrn_quot_itemdetails.quot_id', $request->quot_id],
                    ['wltrn_quot_itemdetails.isactiveroom', 1],
                    ['wltrn_quot_itemdetails.isactiveboard', 1]
                ]
            );

            // $quot_amountD_qry->groupBy($quot_basicD_columns);

            $quot_amountD = $quot_amountD_qry->first();

            $status = 1;
            $message = "Success";
            $data = '';

            $response['quotation_detail_summary'] = $quot_basicD;
            $response['quotation_amount_summary'] = $quot_amountD;

            $range = explode(',', $quot_basicD->default_range);
            $response['quot_range_plate'] = '';
            // $itemsubgroupname0 = WlmstItemSubgroup::find($range[0]);
            // if($itemsubgroupname0){
            //     $response['quot_range_plate'] = $itemsubgroupname0->itemsubgroupname;
            // }
            $response['quot_range_acc'] = '';
            // $itemsubgroupname1 = WlmstItemSubgroup::find($range[1]);
            // if($itemsubgroupname1){
            //     $response['quot_range_acc'] = $itemsubgroupname1->itemsubgroupname;
            // }
            $response['quot_range_whitelion'] = '';
            // $itemsubgroupname2 = WlmstItemSubgroup::find($range[2]);
            // if($itemsubgroupname2){
            //     $response['quot_range_whitelion'] = $itemsubgroupname2->itemsubgroupname;
            // }
            $response['quot_status'] = getQuotationMasterStatusLable($quot_basicD->status);
            $response['quot_range'] = $quot_basicD->default_range;
            // $response['quot_range_plate'] = $range[0];
            // $response['quot_range_acc'] = $range[1];
            // $response['quot_range_whitelion'] = $range[2];
        }
        $response['status'] = $status;
        $response['msg'] = $message;
        $response['data'] = $data;

        return response()->json($response)->header('Content-Type', 'application/json');
    }
}