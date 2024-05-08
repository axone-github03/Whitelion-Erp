<?php

namespace App\Http\Controllers\Quotation;

use Dompdf\Options;

use App\Models\DebugLog;
use App\Models\WlmstItem;
// use DB;
use Illuminate\Http\Request;
use App\Models\Wlmst_ItemPrice;
use App\Models\Wltrn_Quotation;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\WlmstItemSubgroup;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Wlmst_QuotationError;
use App\Models\Wltrn_QuotItemdetail;
use App\Models\QuotRequest;
use App\Models\LeadSource;
use App\Models\CityList;
use App\Models\Lead;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Response;

class QuotationMasterController extends Controller
{

    public function __construct()
    {
        $this->middleware(function ($request, $next) {

            $tabCanAccessBy = array(0, 1, 2, 9, 101, 102, 103, 104, 105, 13);

            if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
                return redirect()->route('dashboard');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $data = array();
        $data['title'] = "Quotation  Master ";
        return view('quotation/master/quotation/quotation', compact('data'));
    }

    public function ajax(Request $request)
    {
        // DB::enableQueryLog();

        $searchColumns = array(
            'wltrn_quotation.id',
            'wltrn_quotation.quotgroup_id',
            'wltrn_quotation.inquiry_id',
            'wltrn_quotation.customer_name',
            'CONCAT(wltrn_quotation.quotno)',
            'CONCAT(users.first_name," ",users.last_name)',
            'CONCAT(leads.first_name," ",leads.last_name)',
        );

        $columns = array(
            'wltrn_quotation.id',
            'wltrn_quotation.quotgroup_id',
            'wltrn_quotation.quotno',
            'wltrn_quotation.yy',
            'wltrn_quotation.mm',
            'wltrn_quotation.quot_no_str',
            'wltrn_quotation.inquiry_id',
            'wltrn_quotation.customer_name',
            'wltrn_quotation.entryby',
            'wltrn_quotation.status',
            'wltrn_quotation.created_at',
            'wltrn_quotation.updated_at',
            'users.first_name',
            'users.last_name',
            'leads.id as lead_id',
            'leads.first_name as lead_first_name',
            'leads.last_name as lead_last_name',
            'leads.is_deal as lead_is_deal',
        );

        $recordsTotal = Wltrn_Quotation::query()->where('wltrn_quotation.quottype_id', '!=', '4');
        if ($request->status != 'ALL') {
            $recordsTotal->where('wltrn_quotation.status', $request->status);
        }
        $recordsFiltered = count(json_decode(json_encode($recordsTotal->get()), true)); // when there is no search parameter then total number rows = total number filtered rows.

        $query = Wltrn_Quotation::query();
        $query->select($columns);
        $query->leftJoin('users', 'users.id', '=', 'wltrn_quotation.entryby');
        $query->leftJoin('leads', 'leads.id', '=', 'wltrn_quotation.inquiry_id');
        $query->where('wltrn_quotation.quottype_id', '!=', '4');
        $query->limit($request->length);
        $query->offset($request->start);
        $query->orderBy($columns[$request['order'][0]['column']], $request['order'][0]['dir']);
        $isFilterApply = 0;
        $search_value = '';

        if ($request->status != 'ALL') {
            $query->where('wltrn_quotation.status', $request->status);
        }

        if (isset($request['search']['value'])) {
            $isFilterApply = 1;
            $search_value = $request['search']['value'];
            $query->where(function ($query) use ($search_value, $searchColumns) {
                for ($i = 0; $i < count($searchColumns); $i++) {
                    if ($i == 0) {
                        // $query->where($searchColumns[$i], 'like', "%" . $search_value . "%");
                        $query->whereRaw($searchColumns[$i] . ' like ?', [$search_value]);
                    } else {
                        // $query->orWhere($searchColumns[$i], 'like', "%" . $search_value . "%");
                        $query->orWhereRaw($searchColumns[$i] . ' like ?', ["%" . $search_value . "%"]);
                    }
                }
            });
        }

        $data = $query->get();
        // echo "<pre>";
        // print_r(DB::getQueryLog());
        // die;

        $data = json_decode(json_encode($data), true);

        if ($isFilterApply == 1) {
            $recordsFiltered = count($data);
        }

        $data_list = array();
        foreach ($data as $key => $value) {

            $data_new['id'] =  highlightString($value['id'],$search_value);
            $data_new['quot_no'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">Q' .  highlightString($value['quotno'],$search_value) . '</a></h5>';

            if ($value['inquiry_id'] != null && $value['inquiry_id'] != 0 && $value['inquiry_id'] != '') {
                $prifix = '';
                $url = '';
                if ($value['lead_is_deal'] == 0) {
                    $prifix = 'L';
                    $url = route('crm.lead') . "?id=" . $value['lead_id'];
                } elseif ($value['lead_is_deal'] == 1) {
                    $prifix = 'D';
                    $url = route('crm.deal') . "?id=" . $value['lead_id'];
                }
                $data_new['partyname'] = '<a href="' . $url . '" target="_blank" class="font-size-14">' .  highlightString($value['lead_first_name'] . ' ' . $value['lead_last_name'],$search_value) . '</br>#' . $prifix .  highlightString($value['lead_id'],$search_value) . '</a>';
            } else {
                $data_new['partyname'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">' .  highlightString($value['customer_name'],$search_value) . '</a></h5>';
            }

            $provision_query = Wltrn_Quotation::query();
            $provision_query->where('wltrn_quotation.quotgroup_id', $value['quotgroup_id']);
            $provision_query->where('wltrn_quotation.quotno', $value['quotno']);
            $provision_query->get();
            $data_new['noofprovision'] = "<p>" . highlightString($provision_query->count(),$search_value) . "</p>";

            $data_new['entryby'] = "<p>" .  highlightString($value['first_name'] . ' ' . $value['last_name'],$search_value) . '</p>';

            $data_new['status'] = getQuotationMasterStatusLable($value['status']);

            $uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';

            $uiAction .= '<li class="list-inline-item px-2">';
            $uiAction .= '<a data-bs-toggle="tooltip" href="javascript: void(0);" title="Created Date & Time : ' . convertDateTime($value['updated_at']) . '"><i class="bx bx-calendar"></i></a>';

            if ($provision_query->count() > 1) {
                $uiAction .= '<li class="list-inline-item px-2">';
                $uiAction .= '<a onclick="ShowHistory(\'' . $value['id'] . '\',\'' . $value['quotgroup_id'] . '\')" href="javascript: void(0);" title="Quotation History Detail"><i class="bx bxs-show"></i></a>';
                $uiAction .= '</li>';
            } else {
                // if(isCreUser() == 0) {
                    $uiAction .= '<li class="list-inline-item px-2">';
                    $uiAction .= '<a href="' . route('quot.itemquotedetail') . '?quotno=' . $value['id'] . '" title="Quotation Item Detail"><i class="bx bx-edit"></i></a>';
                    $uiAction .= '</li>';
    
                    $uiAction .= '<li class="list-inline-item px-2">';
                    $uiAction .= '<a onclick="ItemWisePrint(\'' . $value['id'] . '\',\'' . $value['quotgroup_id'] . '\')" href="javascript: void(0);" title="Item Wise Print"><i class="bx bxs-file-pdf"></i></a>';
                    $uiAction .= '</li>';
                // }

                // $uiAction .= '<li class="list-inline-item px-2">';
                // $uiAction .= '<a onclick="RoomWisePrint(\'' . $value['id'] . '\',\'' . $value['quotgroup_id'] . '\')" href="javascript: void(0);" title="Room Wise Print"><i class="bx bx-receipt"></i></a>';
                // $uiAction .= '</li>';
            }

            // $uiAction .= '<li class="list-inline-item px-2">';
            // $uiAction .= '<a onclick="deleteWarning(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Delete"><i class="bx bx-trash-alt"></i></a>';
            // $uiAction .= '</li>';

            $uiAction .= '</ul>';

            $data_new['action'] = $uiAction;
            array_push($data_list, $data_new);
        }

        $jsonData = array(
            "draw" => intval($request['draw']),
            // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => $recordsTotal,
            // total number of records
            "recordsFiltered" => intval($recordsFiltered),
            // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $data_list, // total data array
        );

        return $jsonData;
    }
    public function QuotHistoryDataajax(Request $request)
    {
        // DB::enableQueryLog();

        $searchColumns = array(
            'wltrn_quotation.quot_no_str',
        );

        $columns = array(
            'wltrn_quotation.id',
            'wltrn_quotation.quotgroup_id',
            'wltrn_quotation.quotno',
            'wltrn_quotation.yy',
            'wltrn_quotation.mm',
            'wltrn_quotation.inquiry_id',
            'wltrn_quotation.quot_no_str',
            'wltrn_quotation.customer_name',
            'wltrn_quotation.entryby',
            'wltrn_quotation.status',
            'wltrn_quotation.created_at',
            'users.first_name',
            'users.last_name',
            'leads.id as lead_id',
            'leads.first_name as lead_first_name',
            'leads.last_name as lead_last_name',
            'leads.is_deal as lead_is_deal',
        );

        $recordsTotal = Wltrn_Quotation::query();
        $recordsTotal->where('wltrn_quotation.quotgroup_id', $request->quotgroup_id);
        $recordsTotal->where('wltrn_quotation.quottype_id', '!=', '4');
        $recordsFiltered = count(json_decode(json_encode($recordsTotal->get()), true)); // when there is no search parameter then total number rows = total number filtered rows.

        $query = Wltrn_Quotation::query();
        $query->select($columns);
        $query->leftJoin('users', 'users.id', '=', 'wltrn_quotation.entryby');
        $query->leftJoin('leads', 'leads.id', '=', 'wltrn_quotation.inquiry_id');
        $query->limit($request->length);
        $query->offset($request->start);
        $query->orderBy($columns[$request['order'][0]['column']], $request['order'][0]['dir']);
        $isFilterApply = 0;
        $query->where('wltrn_quotation.quotgroup_id', $request->quotgroup_id);
        $query->where('wltrn_quotation.quottype_id', '!=', '4');

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
        // echo "<pre>";
        // print_r(DB::getQueryLog());
        // die;

        $data = json_decode(json_encode($data), true);

        if ($isFilterApply == 1) {
            $recordsFiltered = count($data);
        }
        $data_list = array();
        foreach ($data as $key => $value) {
            $data_new['id'] = $value['id'];
            $data_new['quot_no'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">Q' . $value['quotno'] . '</a></h5>';

            if ($value['inquiry_id'] != null && $value['inquiry_id'] != 0 && $value['inquiry_id'] != '') {
                $prifix = '';
                $url = '';
                if ($value['lead_is_deal'] == 0) {
                    $prifix = 'L';
                    $url = route('crm.lead') . "?id=" . $value['lead_id'];
                } elseif ($value['lead_is_deal'] == 1) {
                    $prifix = 'D';
                    $url = route('crm.deal') . "?id=" . $value['lead_id'];
                }
                $data_new['partyname'] = '<a href="' . $url . '" target="_blank" class="font-size-14">' . $value['lead_first_name'] . ' ' . $value['lead_last_name'] . '</br>#' . $prifix . $value['lead_id'] . '</a>';
            } else {
                $data_new['partyname'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">' . $value['customer_name'] . '</a></h5>';
            }
            $data_new['version'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">' . $value['quot_no_str'] . '</a></h5>';


            $data_new['entryby'] = "<p>" . $value['first_name'] . ' ' . $value['last_name'] . '</p>';

            $data_new['status'] = getQuotationMasterStatusLable($value['status']);

            $uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';

            $uiAction .= '<li class="list-inline-item px-2">';
            $uiAction .= '<a data-bs-toggle="tooltip" href="javascript: void(0);" title="Created Date & Time : ' . $value['created_at'] . '"><i class="bx bx-calendar"></i></a>';

            // if(isCreUser() == 0) {
                $uiAction .= '<li class="list-inline-item px-2">';
                $uiAction .= '<a href="' . route('quot.itemquotedetail') . '?quotno=' . $value['id'] . '" title="Quotation Item Detail"><i class="bx bx-edit"></i></a>';
                $uiAction .= '</li>';

                $uiAction .= '<li class="list-inline-item px-2">';
                $uiAction .= '<a onclick="ItemWisePrint(\'' . $value['id'] . '\',\'' . $value['quotgroup_id'] . '\')" href="javascript: void(0);" title="Item Wise Print"><i class="bx bxs-file-pdf"></i></a>';
                $uiAction .= '</li>';
            // }

            // $uiAction .= '<li class="list-inline-item px-2">';
            // $uiAction .= '<a onclick="ItemWisePrint(\'' . $value['id'] . '\',\'' . $value['quotgroup_id'] . '\')" href="javascript: void(0);" title="Item Wise Print"><i class="bx bx-receipt"></i></a>';
            // // $uiAction .= '<a onclick="itemwise(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Item Wise Print"><i class="bx bx-receipt"></i></a>';
            // // $value['quotgroup_id']
            // $uiAction .= '</li>';

            // $uiAction .= '<li class="list-inline-item px-2">';
            // $uiAction .= '<a onclick="RoomWisePrint(\'' . $value['id'] . '\',\'' . $value['quotgroup_id'] . '\')" href="javascript: void(0);" title="Room Wise Print"><i class="bx bx-receipt"></i></a>';
            // $uiAction .= '</li>';

            // $uiAction .= '<li class="list-inline-item px-2">';
            // $uiAction .= '<a onclick="deleteWarning(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Delete"><i class="bx bx-trash-alt"></i></a>';
            // $uiAction .= '</li>';

            $uiAction .= '</ul>';

            $data_new['action'] = $uiAction;
            array_push($data_list, $data_new);
        }

        $jsonData = array(
            "draw" => intval($request['draw']),
            // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => $recordsTotal,
            // total number of records
            "recordsFiltered" => intval($recordsFiltered),
            // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $data_list, // total data array
        );

        return $jsonData;
    }

    // //NEW UPDATE START

    public function PostItemWiseDownloadPrint(Request $request)
    {
        $pdf_filter_array = json_decode($request->visible_array, true);
        $visible_array = $pdf_filter_array[0];


        $old_QuotItemdetail = Wltrn_QuotItemdetail::where([
            ['wltrn_quot_itemdetails.quot_id', $request->quot_id],
            ['wltrn_quot_itemdetails.quotgroup_id', $request->quotgroup_id],
            ['wltrn_quot_itemdetails.isactiveroom', 1],
            ['wltrn_quot_itemdetails.isactiveboard', 1],
        ]);
        $old_QuotItemdetail->update(['is_appendix' => 0]);

        $appendix_columns = array(
            'wlmst_items.additional_info',
            'wltrn_quot_itemdetails.item_id'
        );

        $appendix_query = Wltrn_QuotItemdetail::query();
        $appendix_query->select($appendix_columns);
        $appendix_query->leftJoin('wlmst_items', 'wlmst_items.id', '=', 'wltrn_quot_itemdetails.item_id');
        $appendix_query->groupBy($appendix_columns);
        $appendix_query->where([
            ['wltrn_quot_itemdetails.quot_id', $request->quot_id],
            ['wltrn_quot_itemdetails.quotgroup_id', $request->quotgroup_id],
            ['wltrn_quot_itemdetails.isactiveroom', 1],
            ['wltrn_quot_itemdetails.isactiveboard', 1],
        ]);
        $appendix_query = $appendix_query->get();

        foreach ($appendix_query as $key => $appendix_value) {

            if ($appendix_value->additional_info != null) {
                $appendix_count = Wltrn_QuotItemdetail::where([
                    ['wltrn_quot_itemdetails.quot_id', $request->quot_id],
                    ['wltrn_quot_itemdetails.quotgroup_id', $request->quotgroup_id],
                    ['wltrn_quot_itemdetails.isactiveroom', 1],
                    ['wltrn_quot_itemdetails.isactiveboard', 1],
                ])->max('is_appendix') + 1;

                $QuotItemdetail = Wltrn_QuotItemdetail::where([
                    ['wltrn_quot_itemdetails.quot_id', $request->quot_id],
                    ['wltrn_quot_itemdetails.quotgroup_id', $request->quotgroup_id],
                    ['wltrn_quot_itemdetails.item_id', $appendix_value->item_id],
                    ['wltrn_quot_itemdetails.isactiveroom', 1],
                    ['wltrn_quot_itemdetails.isactiveboard', 1],
                ]);
                $QuotItemdetail->update(['is_appendix' => $appendix_count]);
            }
        }

        $lst_appendix_columns = array(
            'wlmst_items.additional_info',
            'wltrn_quot_itemdetails.item_id',
            'wltrn_quot_itemdetails.is_appendix'
        );

        $lst_appendix_query = Wltrn_QuotItemdetail::query();
        $lst_appendix_query->select($lst_appendix_columns);
        $lst_appendix_query->leftJoin('wlmst_items', 'wlmst_items.id', '=', 'wltrn_quot_itemdetails.item_id');
        $lst_appendix_query->groupBy($lst_appendix_columns);
        $lst_appendix_query->where([
            ['wltrn_quot_itemdetails.quot_id', $request->quot_id],
            ['wltrn_quot_itemdetails.quotgroup_id', $request->quotgroup_id],
            ['wltrn_quot_itemdetails.is_appendix', '!=', '0'],
            ['wltrn_quot_itemdetails.isactiveroom', 1],
            ['wltrn_quot_itemdetails.isactiveboard', 1],

        ]);
        $lst_appendix_query = $lst_appendix_query->get();
        $data['lstappendix'] = $lst_appendix_query;
        $data['appendix_count'] = (int) count($lst_appendix_query);

        $columns = array(
            'wltrn_quotation.id',
            'wltrn_quotation.site_name',
            // 'wltrn_quotation.customer_name',
            // 'wltrn_quotation.customer_contact_no',
            // 'wltrn_quotation.siteaddress',
            DB::raw("CONCAT(leads.first_name, ' ', leads.last_name) as customer_name"),
            'leads.phone_number as customer_contact_no',
            DB::raw("CONCAT(leads.house_no, ' ', leads.addressline1, ' ', leads.addressline2, ' ', leads.area) as siteaddress"),
            'leads.city_id as site_city_id',
            'leads.pincode as site_pincode',
            'city_list.name AS city_name',
            'state_list.name AS state_name',
            'wltrn_quotation.yy',
            'wltrn_quotation.mm',
            'wltrn_quotation.quotno',
            'wltrn_quotation.quot_no_str',
            'wltrn_quotation.quottype_id',
            'quot_type.name AS quot_type',
            'wltrn_quotation.customer_id',
            'wlmst_client.email',
            'wltrn_quotation.inquiry_id',
            'wltrn_quotation.architech_id',
            'wltrn_quotation.electrician_id',
            'wltrn_quotation.salesexecutive_id',
            'wltrn_quotation.channelpartner_id',
            'channel_partner.first_name as channel_partner_first_name',
            'channel_partner.last_name as channel_partner_last_name',
            'channel_partner.email as channel_partner_email',
            'channel_partner.phone_number as channel_partner_mobile_number',
            'consultant.first_name as consultant_first_name',
            'consultant.last_name as consultant_last_name',
            'consultant.phone_number as consultant_phone_number',
            'consultant.email as consultant_email',
        );

        $QuotationBasic = Wltrn_Quotation::query();
        $QuotationBasic->select($columns);
        $QuotationBasic->selectRaw('DATE_FORMAT(wltrn_quotation.quot_date,"%d-%m-%Y") as quot_date');
        $QuotationBasic->leftJoin('leads', 'leads.id', '=', 'wltrn_quotation.inquiry_id');
        $QuotationBasic->leftJoin('city_list', 'city_list.id', '=', 'leads.city_id');
        $QuotationBasic->leftJoin('state_list', 'state_list.id', '=', 'city_list.state_id');
        $QuotationBasic->leftJoin('wlmst_quotation_type as quot_type', 'quot_type.id', '=', 'wltrn_quotation.quottype_id');
        $QuotationBasic->leftJoin('wlmst_client as wlmst_client', 'wlmst_client.id', '=', 'wltrn_quotation.customer_id');
        $QuotationBasic->leftJoin('users as channel_partner', 'channel_partner.id', '=', 'wltrn_quotation.channelpartner_id');
        $QuotationBasic->leftJoin('users as consultant', 'consultant.id', '=', 'wltrn_quotation.entryby');
        $QuotationBasic->where('wltrn_quotation.id', $request->quot_id);

        $Quot_Basic_Detail = $QuotationBasic->first();



        if ($Quot_Basic_Detail) {
            $LeadDetail = Lead::find($Quot_Basic_Detail['inquiry_id']);

            

            $SiteAddress = '';
            $CustomerName = '';
            if ($LeadDetail) {
                $ch_type_arr = ['user-101', 'user-102', 'user-103', 'user-104', 'user-105'];
                $Lead_source = LeadSource::select('users.first_name','users.last_name','users.email','users.phone_number');
                $Lead_source->leftJoin('users', 'users.id', '=', 'lead_sources.source');
                $Lead_source->where('lead_sources.lead_id', $LeadDetail->id);
                $Lead_source->where('lead_sources.source','!=', '');
                $Lead_source->whereIn('lead_sources.source_type', $ch_type_arr);
                $Lead_source = $Lead_source->orderBy('lead_sources.id', 'DESC')->first();
                if($Lead_source){
                    $Quot_Basic_Detail['channel_partner_first_name'] = $Lead_source->first_name;
                    $Quot_Basic_Detail['channel_partner_last_name'] = $Lead_source->last_name;
                    $Quot_Basic_Detail['channel_partner_email'] = $Lead_source->email;
                    $Quot_Basic_Detail['channel_partner_mobile_number'] = $Lead_source->phone_number;
                }
            
                $CustomerName = $LeadDetail->first_name . ' ' . $LeadDetail->last_name;
                if ($LeadDetail->house_no != '' || $LeadDetail->house_no != null) {
                    $SiteAddress .= $LeadDetail->house_no;
                }
                if ($LeadDetail->addressline1 != '' || $LeadDetail->addressline1 != null) {
                    $SiteAddress .= ", " . $LeadDetail->addressline1;
                }
                if ($LeadDetail->addressline2 != '' || $LeadDetail->addressline2 != null) {
                    $SiteAddress .= ", " . $LeadDetail->addressline2;
                }
                if ($LeadDetail->area != '' || $LeadDetail->area != null) {
                    $SiteAddress .= ", " . $LeadDetail->area;
                }
                if ($LeadDetail->city_id != '' || $LeadDetail->city_id != null || $LeadDetail->city_id != 0) {
                    $CityName = CityList::select('city_list.id', 'city_list.name as city_list_name', 'state_list.name as state_list_name');
                    $CityName->leftJoin('state_list', 'state_list.id', '=', 'city_list.state_id');
                    $CityName->where('city_list.id', $LeadDetail->city_id);
                    $CityName = $CityName->first();
                    if ($CityName) {
                        $SiteAddress .= ", " . $CityName->city_list_name . ", " . $CityName->state_list_name;
                    }
                }
                if ($LeadDetail->pincode != '' || $LeadDetail->pincode != null || $LeadDetail->pincode != null) {
                    $SiteAddress .= ", " . $LeadDetail->pincode;
                }
            } else {
                $CustomerName = $Quot_Basic_Detail['customer_name'];
                if ($Quot_Basic_Detail['siteaddress'] != '' || $Quot_Basic_Detail['siteaddress'] != null) {
                    $SiteAddress .= $Quot_Basic_Detail['siteaddress'];
                }
                if ($Quot_Basic_Detail['city_name'] != '' || $Quot_Basic_Detail['city_name'] != null) {
                    $SiteAddress .= ", " . $Quot_Basic_Detail['city_name'];
                }
                if ($Quot_Basic_Detail['state_name'] != '' || $Quot_Basic_Detail['state_name'] != null) {
                    $SiteAddress .= ", " . $Quot_Basic_Detail['state_name'];
                }
                if ($Quot_Basic_Detail['site_pincode'] != '' || $Quot_Basic_Detail['site_pincode'] != null) {
                    $SiteAddress .= ", " . $Quot_Basic_Detail['site_pincode'];
                }
            }

            if ($Quot_Basic_Detail['quottype_id'] == 1) {
                $SiteVisitWith = "Client";
                if ($Quot_Basic_Detail['architech_id'] != "0") {
                    $SiteVisitWith .= ", Architect";
                }

                if ($Quot_Basic_Detail['electrician_id'] != "0") {
                    $SiteVisitWith .= ",</br>Electrician";
                }
            } else {
                $SiteVisitWith = '-';
            }
            // BOARD WISE ROOM LIST START

            $room_column = array(
                'wltrn_quot_itemdetails.room_no',
                'wltrn_quot_itemdetails.room_name'
            );
            $room_query = Wltrn_QuotItemdetail::query();
            $room_query->select($room_column);
            $room_query->selectRaw('SUM(wltrn_quot_itemdetails.net_amount) as room_amount');
            $room_query->where([
                ['wltrn_quot_itemdetails.quot_id', $request->quot_id],
                ['wltrn_quot_itemdetails.quotgroup_id', $request->quotgroup_id],
                ['wltrn_quot_itemdetails.isactiveroom', 1],
                ['wltrn_quot_itemdetails.isactiveboard', 1],
                // ['wltrn_quot_itemdetails.itemsubgroup_id', '<>', '64']
            ]);
            $room_query->groupBy($room_column);
            $room_data = $room_query->get();

            $product_detailed_summary_visible = $visible_array['product_detailed_summary_visible'];
            $product_detailed_gst_visible = $visible_array['product_detailed_gst_visible'];
            $product_detailed_discount_visible = $visible_array['product_detailed_discount_visible'];
            $product_detailed_rate_total_visible = $visible_array['product_detailed_rate_total_visible'];

            if ($product_detailed_summary_visible == 1) {

                $arr_room = array();
                foreach ($room_data as $key => $room_value) {
                    $room_detail['room_name'] = $room_value->room_name;
                    $room_detail['room_amount'] = round($room_value->room_amount);
                    // $room_detail['room_amount'] = number_format(round($room_value->room_amount), 2, '.', '');
                    $board_column = array(
                        'wltrn_quot_itemdetails.quot_id',
                        'wltrn_quot_itemdetails.quotgroup_id',
                        'wltrn_quot_itemdetails.room_no',
                        'wltrn_quot_itemdetails.board_no',
                        'wltrn_quot_itemdetails.board_name',
                        'wltrn_quot_itemdetails.board_item_id',
                        'wltrn_quot_itemdetails.board_image',
                    );

                    $board_query = Wltrn_QuotItemdetail::query();
                    $board_query->select($board_column);
                    $board_query->selectRaw('SUM(wltrn_quot_itemdetails.net_amount) as board_net_amount');
                    $board_query->where([
                        ['wltrn_quot_itemdetails.quot_id', $request->quot_id],
                        ['wltrn_quot_itemdetails.quotgroup_id', $request->quotgroup_id],
                        ['wltrn_quot_itemdetails.room_no', $room_value->room_no],
                        ['wltrn_quot_itemdetails.isactiveroom', 1],
                        ['wltrn_quot_itemdetails.isactiveboard', 1],
                        // ['wltrn_quot_itemdetails.itemsubgroup_id', '<>', '64']
                    ]);
                    // $board_query->groupBy(['wltrn_quot_itemdetails.board_no']);
                    $board_query->groupBy($board_column);
                    $board_data = $board_query->get();
                    $arr_board = array();
                    foreach ($board_data as $key => $board_value) {
                        // $arr_board_detail['board_image'] = strval($board_value->board_image);
                        $arr_board_detail['board_image'] = strval(getSpaceFilePath($board_value->board_image));
                        $arr_board_detail['board_name'] = $board_value->board_name;
                        $arr_board_detail['board_price'] = round($board_value->board_net_amount);
                        // $arr_board_detail['board_price'] = number_format(round($board_value->board_net_amount), 2, '.', '');
                        $board_item_column = array(
                            'wltrn_quot_itemdetails.qty',
                            'wltrn_quot_itemdetails.rate',
                            'wltrn_quot_itemdetails.discper',
                            'wltrn_quot_itemdetails.grossamount as taxableamount',
                            // 'wltrn_quot_itemdetails.taxableamount',
                            'wltrn_quot_itemdetails.net_amount',
                            'wltrn_quot_itemdetails.is_appendix',
                            'wlmst_items.itemname',
                            'wlmst_items.igst_per',
                            'wlmst_item_groups.sequence',
                            'wlmst_item_prices.code',
                            'wlmst_item_prices.image as addons_image',
                        );

                        $board_item_query = Wltrn_QuotItemdetail::query();
                        $board_item_query->select($board_item_column);
                        $board_item_query->leftJoin('wlmst_items', 'wlmst_items.id', '=', 'wltrn_quot_itemdetails.item_id');
                        $board_item_query->leftJoin('wlmst_item_groups', 'wlmst_item_groups.id', '=', 'wltrn_quot_itemdetails.itemgroup_id');
                        $board_item_query->leftJoin('wlmst_item_prices', 'wlmst_item_prices.id', '=', 'wltrn_quot_itemdetails.item_price_id');

                        $board_item_query->where([
                            ['wltrn_quot_itemdetails.quot_id', $request->quot_id],
                            ['wltrn_quot_itemdetails.quotgroup_id', $request->quotgroup_id],
                            ['wltrn_quot_itemdetails.room_no', $board_value->room_no],
                            ['wltrn_quot_itemdetails.board_no', $board_value->board_no],
                            ['wltrn_quot_itemdetails.isactiveroom', 1],
                            ['wltrn_quot_itemdetails.isactiveboard', 1],
                        ]);
                        // $board_item_query->orderBy('wltrn_quot_itemdetails.itemsubgroup_id', 'ASC');
                        $board_item_query->orderBy('wlmst_item_groups.sequence', 'ASC');

                        $arr_board_detail['board_item_count'] = count($board_item_query->get());
                        $arr_board_detail['board_item'] = $board_item_query->get();
                        array_push($arr_board, $arr_board_detail);
                    }

                    $room_detail['board'] = $arr_board;
                    array_push($arr_room, $room_detail);
                }
            } else {
                $arr_room = 0;
            }
            $data['basic_detail'] = $Quot_Basic_Detail;
            $data['basic_detail']['site_visit_with'] = $SiteVisitWith;
            $data['basic_detail']['customer_name'] = $CustomerName;
            $data['basic_detail']['final_site_address'] = $SiteAddress;
            $data['room'] = $arr_room;
            // BOARD WISE ROOM LIST END

            ///////////////////////////////   START ROOM AND AREA WISE SUMMARY  ////////////////////////////////////////////
            $area_page_visible = $visible_array['area_page_visible'];
            $area_summary_visible = $visible_array['area_summary_visible'];
            $product_summary_visible = $visible_array['product_summary_visible'];

            // ROOM WISE SUMMARY START
            if ($area_page_visible == 1) {
                $columns_area_summary = array(
                    0 => 'wltrn_quot_itemdetails.room_no',
                    1 => 'wltrn_quot_itemdetails.room_name'
                );

                $QuotationAreaSummary = Wltrn_QuotItemdetail::query();
                $QuotationAreaSummary->select($columns_area_summary);
                $QuotationAreaSummary->selectRaw('SUM(wltrn_quot_itemdetails.net_amount) as room_net_amount');
                $QuotationAreaSummary->where([
                    ['wltrn_quot_itemdetails.quot_id', $request->quot_id],
                    ['wltrn_quot_itemdetails.quotgroup_id', $request->quotgroup_id],
                    ['wltrn_quot_itemdetails.isactiveroom', 1],
                    ['wltrn_quot_itemdetails.isactiveboard', 1],
                ]);
                $QuotationAreaSummary->groupBy($columns_area_summary);

                $listAreaSummary = array();
                $area_summary_total_other_amount = 0;
                $area_summary_total_whitelion_amount = 0;
                $area_summary_total_final_amount = 0;
                foreach ($QuotationAreaSummary->get() as $key => $area_summary) {
                    $listAreaSm['room_no'] = $area_summary->room_no;
                    $listAreaSm['room_name'] = $area_summary->room_name;

                    $whitelion_net_amount = Wltrn_QuotItemdetail::where('room_no', $area_summary->room_no);
                    $whitelion_net_amount = $whitelion_net_amount->where('quot_id', $request->quot_id);
                    $whitelion_net_amount = $whitelion_net_amount->where('quotgroup_id', $request->quotgroup_id);
                    $whitelion_net_amount = $whitelion_net_amount->where('isactiveroom', 1);
                    $whitelion_net_amount = $whitelion_net_amount->where('isactiveboard', 1);
                    $whitelion_net_amount = $whitelion_net_amount->whereIn('itemgroup_id', [1, 3])->sum('net_amount');

                    $other_net_amount = Wltrn_QuotItemdetail::where('room_no', $area_summary->room_no);
                    $other_net_amount = $other_net_amount->where('quot_id', $request->quot_id);
                    $other_net_amount = $other_net_amount->where('quotgroup_id', $request->quotgroup_id);
                    $other_net_amount = $other_net_amount->where('isactiveroom', 1);
                    $other_net_amount = $other_net_amount->where('isactiveboard', 1);
                    $other_net_amount = $other_net_amount->whereIn('itemgroup_id', [2, 4])->sum('net_amount');

                    $total_net_amount = $area_summary->room_net_amount;
                    $area_summary_total_other_amount += $other_net_amount;
                    $area_summary_total_whitelion_amount += $whitelion_net_amount;
                    $area_summary_total_final_amount += $total_net_amount;
                    $listAreaSm['room_total_whitelion_net_amount'] = round($whitelion_net_amount);
                    $listAreaSm['room_total_other_net_amount'] = round($other_net_amount);
                    $listAreaSm['room_total_net_amount'] = round($total_net_amount);
                    array_push($listAreaSummary, $listAreaSm);
                }
            } else {
                $listAreaSummary = 0;
                $area_summary_total_other_amount = 0;
                $area_summary_total_whitelion_amount = 0;
                $area_summary_total_final_amount = 0;
            }
            $data['area_summary']['area_list'] = $listAreaSummary;
            $data['area_summary']['area_summary_total_other_amount'] = round($area_summary_total_other_amount);
            $data['area_summary']['area_summary_total_whitelion_amount'] = round($area_summary_total_whitelion_amount);
            $data['area_summary']['area_summary_total_final_amount'] = round($area_summary_total_final_amount);
            // ROOM WISE SUMMARY END


            // START PRODUCT WISE EXCEL SUMMARY
            if ($product_summary_visible == 1) {
                $columns_area_summary = array(
                    0 => 'wltrn_quot_itemdetails.room_no',
                    1 => 'wltrn_quot_itemdetails.room_name'
                );

                $QuotationAreaSummary = Wltrn_QuotItemdetail::query();
                $QuotationAreaSummary->select($columns_area_summary);
                $QuotationAreaSummary->selectRaw('SUM(wlmst_item_details.touch_on_off * wltrn_quot_itemdetails.qty) as touch_on_off');
                $QuotationAreaSummary->selectRaw('SUM(wlmst_item_details.touch_fan_regulator * wltrn_quot_itemdetails.qty) as touch_fan_regulator');
                $QuotationAreaSummary->selectRaw('SUM(wlmst_item_details.wl_plug * wltrn_quot_itemdetails.qty) as wl_plug');
                $QuotationAreaSummary->selectRaw('SUM(wlmst_item_details.special * wltrn_quot_itemdetails.qty) as special');
                $QuotationAreaSummary->selectRaw('SUM(wlmst_item_details.wl_accessories * wltrn_quot_itemdetails.qty) as wl_accessories');
                // $QuotationAreaSummary->selectRaw('SUM(wlmst_item_details.rc2 * wltrn_quot_itemdetails.qty) as rc2');
                $QuotationAreaSummary->selectRaw('SUM(wlmst_item_details.normal_switch * wltrn_quot_itemdetails.qty) as normal_switch');
                $QuotationAreaSummary->selectRaw('SUM(wlmst_item_details.normal_fan_regulator * wltrn_quot_itemdetails.qty) as normal_fan_regulator');
                $QuotationAreaSummary->selectRaw('SUM(wlmst_item_details.other_plug * wltrn_quot_itemdetails.qty) as other_plug');
                $QuotationAreaSummary->selectRaw('SUM(wlmst_item_details.other * wltrn_quot_itemdetails.qty) as other');
                $QuotationAreaSummary->leftJoin('wlmst_item_details', 'wlmst_item_details.item_id', '=', 'wltrn_quot_itemdetails.item_id');
                $QuotationAreaSummary->where([
                    ['wltrn_quot_itemdetails.quot_id', $request->quot_id],
                    ['wltrn_quot_itemdetails.quotgroup_id', $request->quotgroup_id],
                    ['wltrn_quot_itemdetails.isactiveroom', 1],
                    ['wltrn_quot_itemdetails.isactiveboard', 1],
                ]);
                $QuotationAreaSummary->groupBy($columns_area_summary);
                $listProductExcelSummary = $QuotationAreaSummary->get();
            } else {
                $listProductExcelSummary = 0;
            }

            $data['area_summary']['item_excel_summary'] = $listProductExcelSummary;
            // END PRODUCT WISE EXCEL SUMMARY

            ///////////////////////////////   END ROOM AND AREA WISE SUMMARY  ////////////////////////////////////////////

            ///////////////////////////////   START ROOM AND AREA WISE DETAILED SUMMARY  ////////////////////////////////////////////


            // PREFIX = rds
            $area_detailed_summary_visible = $visible_array['area_detailed_summary_visible'];
            $area_detailed_gst_visible = $visible_array['area_detailed_gst_visible'];
            $area_detailed_discount_visible = $visible_array['area_detailed_discount_visible'];
            $area_detailed_rate_total_visible = $visible_array['area_detailed_rate_total_visible'];
            if ($area_detailed_summary_visible == 1) {
                $rds_room_column = array(
                    'wltrn_quot_itemdetails.room_no',
                    'wltrn_quot_itemdetails.room_name'
                );
                $rds_room_query = Wltrn_QuotItemdetail::query();
                $rds_room_query->select($rds_room_column);
                $rds_room_query->selectRaw('SUM(wltrn_quot_itemdetails.rate) as rds_total_rate');
                $rds_room_query->selectRaw('SUM(wltrn_quot_itemdetails.grossamount) as rds_total_grossamount');
                // $rds_room_query->selectRaw('SUM(wltrn_quot_itemdetails.taxableamount) as rds_total_grossamount');
                $rds_room_query->selectRaw('SUM(wltrn_quot_itemdetails.net_amount) as room_amount');
                $rds_room_query->selectRaw('SUM(wltrn_quot_itemdetails.cgst_amount) as rds_total_cgst_amount');
                $rds_room_query->selectRaw('SUM(wltrn_quot_itemdetails.sgst_amount) as rds_total_sgst_amount');

                $rds_room_query->where([
                    ['wltrn_quot_itemdetails.quot_id', $request->quot_id],
                    ['wltrn_quot_itemdetails.quotgroup_id', $request->quotgroup_id],
                    ['wltrn_quot_itemdetails.isactiveroom', 1],
                    ['wltrn_quot_itemdetails.isactiveboard', 1],
                    // ['wltrn_quot_itemdetails.itemsubgroup_id', '<>', '64']
                ]);
                $rds_room_query->groupBy($rds_room_column);
                $rds_room_data = $rds_room_query->get();

                $rds_room_arr = array();
                foreach ($rds_room_data as $key => $rds_room_summary) {
                    $rds_room_detail['rds_room_name'] = $rds_room_summary->room_name;
                    $rds_room_detail['rds_room_total_rate'] = round($rds_room_summary->rds_total_rate);
                    $rds_room_detail['rds_room_total_grossamount'] = round($rds_room_summary->rds_total_grossamount);
                    $rds_room_detail['rds_room_total_gst'] = round($rds_room_summary->rds_total_cgst_amount + $rds_room_summary->rds_total_sgst_amount);
                    $rds_room_detail['rds_room_total_netamount'] = round($rds_room_summary->room_amount);
                    $rds_item_column = array(
                        'wltrn_quot_itemdetails.discper',
                        'wltrn_quot_itemdetails.rate',
                        'wltrn_quot_itemdetails.is_appendix',
                        'wlmst_items.itemname',
                        'wlmst_items.igst_per',
                        'wltrn_quot_itemdetails.item_price_id',
                        'wlmst_item_groups.sequence',
                        'wlmst_item_prices.code',
                        'wlmst_item_subgroups.itemsubgroupname'
                    );

                    $rds_item_query = Wltrn_QuotItemdetail::query();
                    $rds_item_query->select($rds_item_column);
                    $rds_item_query->selectRaw('SUM(wltrn_quot_itemdetails.qty) as qty');
                    $rds_item_query->selectRaw('SUM(wltrn_quot_itemdetails.discamount) as discamount');
                    $rds_item_query->selectRaw('SUM(wltrn_quot_itemdetails.grossamount) as grossamount');
                    // $rds_item_query->selectRaw('SUM(wltrn_quot_itemdetails.taxableamount) as grossamount');
                    $rds_item_query->selectRaw('SUM(wltrn_quot_itemdetails.net_amount) as net_amount');
                    $rds_item_query->leftJoin('wlmst_items', 'wlmst_items.id', '=', 'wltrn_quot_itemdetails.item_id');
                    $rds_item_query->leftJoin('wlmst_item_groups', 'wlmst_item_groups.id', '=', 'wltrn_quot_itemdetails.itemgroup_id');
                    $rds_item_query->leftJoin('wlmst_item_prices', 'wlmst_item_prices.id', '=', 'wltrn_quot_itemdetails.item_price_id');
                    $rds_item_query->leftJoin('wlmst_item_subgroups', 'wlmst_item_subgroups.id', '=', 'wltrn_quot_itemdetails.itemsubgroup_id');

                    $rds_item_query->where([
                        ['wltrn_quot_itemdetails.quot_id', $request->quot_id],
                        ['wltrn_quot_itemdetails.quotgroup_id', $request->quotgroup_id],
                        ['wltrn_quot_itemdetails.room_no', $rds_room_summary->room_no],
                        ['wltrn_quot_itemdetails.isactiveroom', 1],
                        ['wltrn_quot_itemdetails.isactiveboard', 1],
                    ]);
                    $rds_item_query->groupby($rds_item_column);
                    $rds_item_query->orderBy('wlmst_item_groups.sequence', 'ASC');
                    $rds_room_detail['rds_room_item'] = $rds_item_query->get();
                    array_push($rds_room_arr, $rds_room_detail);
                }
            } else {
                $rds_room_arr = 0;
            }
            $data['rds_room_summary'] = $rds_room_arr;

            ///////////////////////////////   END ROOM AND AREA WISE DETAILED SUMMARY  ////////////////////////////////////////////


            ///////////////////////////////   START WHITELION AND OTHERS PRODUCT WISE DETAILED SUMMARY  ////////////////////////////////////////////


            // PREFIX = whitelion
            $wlt_and_others_detailed_summary_visible = $visible_array['wlt_and_others_detailed_summary_visible'];
            $wlt_and_others_detailed_gst_visible = $visible_array['wlt_and_others_detailed_gst_visible'];
            $wlt_and_others_detailed_discount_visible = $visible_array['wlt_and_others_detailed_discount_visible'];
            $wlt_and_others_detailed_rate_total_visible = $visible_array['wlt_and_others_detailed_rate_total_visible'];
            if ($wlt_and_others_detailed_summary_visible == 1) {
                $whitelion_company_column = array(
                    'wltrn_quot_itemdetails.discper',
                    'wltrn_quot_itemdetails.rate',
                    'wltrn_quot_itemdetails.is_appendix',
                    'wlmst_items.itemname',
                    'wlmst_items.igst_per',
                    'wltrn_quot_itemdetails.item_price_id',
                    'wlmst_item_groups.sequence',
                    'wlmst_item_prices.code',
                    'wlmst_item_subgroups.itemsubgroupname'
                );

                $whitelion_company_query = Wltrn_QuotItemdetail::query();
                $whitelion_company_query->select($whitelion_company_column);
                $whitelion_company_query->selectRaw('SUM(wltrn_quot_itemdetails.qty) as whitelion_qty');
                $whitelion_company_query->selectRaw('SUM(wltrn_quot_itemdetails.discamount) as whitelion_discount_amount');
                // $whitelion_company_query->selectRaw('SUM(wltrn_quot_itemdetails.taxableamount) as whitelion_grossamount');
                $whitelion_company_query->selectRaw('SUM(wltrn_quot_itemdetails.grossamount) as whitelion_grossamount');
                $whitelion_company_query->selectRaw('SUM(wltrn_quot_itemdetails.cgst_amount) as whitelion_cgst_amount');
                $whitelion_company_query->selectRaw('SUM(wltrn_quot_itemdetails.sgst_amount) as whitelion_sgst_amount');
                $whitelion_company_query->selectRaw('SUM(wltrn_quot_itemdetails.net_amount) as whitelion_net_amount');
                $whitelion_company_query->leftJoin('wlmst_items', 'wlmst_items.id', '=', 'wltrn_quot_itemdetails.item_id');
                $whitelion_company_query->leftJoin('wlmst_item_groups', 'wlmst_item_groups.id', '=', 'wltrn_quot_itemdetails.itemgroup_id');
                $whitelion_company_query->leftJoin('wlmst_item_prices', 'wlmst_item_prices.id', '=', 'wltrn_quot_itemdetails.item_price_id');
                $whitelion_company_query->leftJoin('wlmst_item_subgroups', 'wlmst_item_subgroups.id', '=', 'wltrn_quot_itemdetails.itemsubgroup_id');

                $whitelion_company_query->where([
                    ['wltrn_quot_itemdetails.quot_id', $request->quot_id],
                    ['wltrn_quot_itemdetails.quotgroup_id', $request->quotgroup_id],
                    ['wltrn_quot_itemdetails.isactiveroom', 1],
                    ['wltrn_quot_itemdetails.isactiveboard', 1],
                ]);
                $whitelion_company_query->whereIn('wltrn_quot_itemdetails.itemgroup_id', [1, 3]);
                $whitelion_company_query->groupby($whitelion_company_column);
                $whitelion_company_query->orderBy('wlmst_item_groups.sequence', 'ASC');
                $whitelion_company_data = $whitelion_company_query->get();


                $whitelion_company_total_cgst = 0;
                $whitelion_company_total_sgst = 0;
                $whitelion_company_total_grossamount = 0;
                $whitelion_company_total_netamount = 0;

                $whitelion_company_arr = array();
                $new_whitelion_subgroup = '';
                $old_whitelion_subgroup = '';
                foreach ($whitelion_company_data as $key => $whitelion_company_summary) {
                    $item_price = Wlmst_ItemPrice::find($whitelion_company_summary->item_price_id);
                    $item_subgroup = WlmstItemSubgroup::find($item_price->itemsubgroup_id);
                    $new_whitelion_subgroup = $item_subgroup->itemsubgroupname;
                    $whitelion_company_summary['subgroupname'] = '';
                    if ($new_whitelion_subgroup != $old_whitelion_subgroup) {
                        $whitelion_company_summary['subgroupname'] = $new_whitelion_subgroup;
                    }

                    $whitelion_company_total_cgst += $whitelion_company_summary->whitelion_cgst_amount;
                    $whitelion_company_total_sgst += $whitelion_company_summary->whitelion_sgst_amount;
                    $whitelion_company_total_grossamount += $whitelion_company_summary->whitelion_grossamount;
                    $whitelion_company_total_netamount += $whitelion_company_summary->whitelion_net_amount;
                    array_push($whitelion_company_arr, $whitelion_company_summary);

                    $old_whitelion_subgroup = $new_whitelion_subgroup;
                }


                // PREFIX = others
                $others_company_column = array(
                    'wltrn_quot_itemdetails.discper',
                    'wltrn_quot_itemdetails.rate',
                    'wltrn_quot_itemdetails.is_appendix',
                    'wlmst_items.itemname',
                    'wlmst_items.igst_per',
                    'wltrn_quot_itemdetails.item_price_id',
                    'wlmst_item_groups.sequence',
                    'wltrn_quot_itemdetails.itemgroup_id',
                    'wlmst_item_prices.code',
                    'wlmst_item_subgroups.itemsubgroupname'
                );
                $others_company_query = Wltrn_QuotItemdetail::query();
                $others_company_query->select($others_company_column);
                $others_company_query->selectRaw('SUM(wltrn_quot_itemdetails.qty) as others_qty');
                $others_company_query->selectRaw('SUM(wltrn_quot_itemdetails.discamount) as others_discount_amount');
                // $others_company_query->selectRaw('SUM(wltrn_quot_itemdetails.taxableamount) as others_grossamount');
                $others_company_query->selectRaw('SUM(wltrn_quot_itemdetails.grossamount) as others_grossamount');
                $others_company_query->selectRaw('SUM(wltrn_quot_itemdetails.cgst_amount) as others_cgst_amount');
                $others_company_query->selectRaw('SUM(wltrn_quot_itemdetails.sgst_amount) as others_sgst_amount');
                $others_company_query->selectRaw('SUM(wltrn_quot_itemdetails.net_amount) as others_net_amount');
                $others_company_query->leftJoin('wlmst_items', 'wlmst_items.id', '=', 'wltrn_quot_itemdetails.item_id');
                $others_company_query->leftJoin('wlmst_item_groups', 'wlmst_item_groups.id', '=', 'wltrn_quot_itemdetails.itemgroup_id');
                $others_company_query->leftJoin('wlmst_item_prices', 'wlmst_item_prices.id', '=', 'wltrn_quot_itemdetails.item_price_id');
                $others_company_query->leftJoin('wlmst_item_subgroups', 'wlmst_item_subgroups.id', '=', 'wltrn_quot_itemdetails.itemsubgroup_id');

                $others_company_query->where([
                    ['wltrn_quot_itemdetails.quot_id', $request->quot_id],
                    ['wltrn_quot_itemdetails.quotgroup_id', $request->quotgroup_id],
                    ['wltrn_quot_itemdetails.isactiveroom', 1],
                    ['wltrn_quot_itemdetails.isactiveboard', 1],
                ]);
                $others_company_query->whereIn('wltrn_quot_itemdetails.itemgroup_id', [2, 4]);
                $others_company_query->groupby($others_company_column);
                $others_company_query->orderBy('wlmst_item_groups.sequence', 'ASC');

                $others_company_data = $others_company_query->get();

                $others_company_total_cgst = 0;
                $others_company_total_sgst = 0;
                $others_company_total_grossamount = 0;
                $others_company_total_netamount = 0;
                $others_company_arr = array();
                $new_other_subgroup = '';
                $old_other_subgroup = '';
                foreach ($others_company_data as $key => $others_company_summary) {
                    $item_price = Wlmst_ItemPrice::find($others_company_summary->item_price_id);
                    $item_subgroup = WlmstItemSubgroup::find($item_price->itemsubgroup_id);
                    $new_other_subgroup = $item_subgroup->itemsubgroupname;
                    $others_company_summary['subgroupname'] = '';
                    if ($new_other_subgroup != $old_other_subgroup) {
                        $others_company_summary['subgroupname'] = $new_other_subgroup;
                    }

                    $others_company_total_cgst += $others_company_summary->others_cgst_amount;
                    $others_company_total_sgst += $others_company_summary->others_sgst_amount;
                    $others_company_total_grossamount += $others_company_summary->others_grossamount;
                    $others_company_total_netamount += $others_company_summary->others_net_amount;
                    array_push($others_company_arr, $others_company_summary);

                    $old_other_subgroup = $new_other_subgroup;
                }
            } else {

                // whitelion
                $whitelion_company_total_cgst = 0;
                $whitelion_company_total_sgst = 0;
                $whitelion_company_total_grossamount = 0;
                $whitelion_company_total_netamount = 0;
                $whitelion_company_arr = 0;

                // others
                $others_company_total_cgst = 0;
                $others_company_total_sgst = 0;
                $others_company_total_grossamount = 0;
                $others_company_total_netamount = 0;
                $others_company_arr = 0;
            }

            $data['whitelion_product_summary']['whitelion_company_total_cgst'] = $whitelion_company_total_cgst;
            $data['whitelion_product_summary']['whitelion_company_total_sgst'] = $whitelion_company_total_sgst;
            $data['whitelion_product_summary']['whitelion_company_total_grossamount'] = $whitelion_company_total_grossamount;
            $data['whitelion_product_summary']['whitelion_company_total_netamount'] = $whitelion_company_total_netamount;
            $data['whitelion_product_summary']['whitelion_items'] = $whitelion_company_arr;


            $data['others_product_summary']['others_company_total_cgst'] = $others_company_total_cgst;
            $data['others_product_summary']['others_company_total_sgst'] = $others_company_total_sgst;
            $data['others_product_summary']['others_company_total_grossamount'] = $others_company_total_grossamount;
            $data['others_product_summary']['others_company_total_netamount'] = $others_company_total_netamount;
            $data['others_product_summary']['others_company_item'] = $others_company_arr;

            ///////////////////////////////   END WHITELION AND OTHERS PRODUCT WISE DETAILED SUMMARY  ////////////////////////////////////////////
            $data['pdf_permission'] = $visible_array;

            // $response = $data;
            // $pdf = Pdf::loadView('quotation/master/quotation/quotpdf', compact('data'));
            // $pdf->setPaper('a4', 'portrait');

            if(isset($request->is_helper) && $request->is_helper == 1){
                $view = view('quotation/master/quotation/quotpdf', compact('data'))->render();
                $pdf = app('dompdf.wrapper');
                $pdf->getDomPDF()->set_option('isHtml5ParserEnabled', true);
                $pdf->getDomPDF()->set_option('isRemoteEnabled', true);
                $pdf->getDomPDF()->set_option('enable_php', true);
                $pdf->loadHTML($view);

                $folderPathofFile = '/s/quotation_pdf/';
                if (!is_dir(public_path($folderPathofFile))) {
                    mkdir(public_path($folderPathofFile));
                }
                $filePath = public_path('s/quotation_pdf/'.preg_replace('/\s+/', '', $CustomerName).'.pdf');
                $pdf = $pdf->save($filePath);

                if ($pdf) {
                    $folderPathofFile = '/s/quotation_pdf/';
                    if (!is_dir(public_path($folderPathofFile))) {
                        mkdir(public_path($folderPathofFile));
                    }

                    $fileName1 = preg_replace('/\s+/', '', $CustomerName).'.pdf';

                    if (File::exists(public_path($folderPathofFile . "/" . $fileName1))) {

                        $fileName1 = $folderPathofFile . "/" . $fileName1;

                        $fileSize = filesize(public_path($fileName1));

                        $spaceUploadResponse = uploadFileOnSpaces(public_path($fileName1), $fileName1);
                        if ($spaceUploadResponse != 1) {
                            $fileName1 = "";
                        } else {
                            unlink(public_path($fileName1));
                        }
                    }

                    return '/s/quotation_pdf/'.preg_replace('/\s+/', '', $CustomerName).'.pdf';
                }

            } else {
                $view = view('quotation/master/quotation/quotpdf', compact('data'))->render();
                $pdf = app('dompdf.wrapper');
                $pdf->getDomPDF()->set_option("isHtml5ParserEnabled", true);
                // $pdf->getDomPDF()->set_option('dpi', 300);
                $pdf->getDomPDF()->set_option("isRemoteEnabled", true);
                $pdf->getDomPDF()->set_option("enable_php", true);
                $pdf->loadHTML($view);
                return $pdf->download(preg_replace('/\s+/', '', $CustomerName).'_quotation.pdf');
            }

            


            // $view = view('quotation/master/quotation/quotpdf', compact('data'));
            // return response($view, Response::HTTP_OK)
            //     ->header('Content-Type', 'text/html');
        } else {
            $response = errorRes("Invalid Quotation Number");
        }
        // $data['data'] = $visible_array;
        // $response = $data;

        return response()->json($response)->header('Content-Type', 'application/json');
    }

    // //NEW UPDATE END 


    public function searchItemSubGroupPlate(Request $request)
    {
        $GroupList = array();
        $GroupList = WlmstItemSubgroup::select('id', 'itemsubgroupname as text');
        $GroupList->where('wlmst_item_subgroups.itemgroup_id', '4');
        $GroupList->where('itemsubgroupname', 'like', "%" . $request->q . "%");
        $GroupList->limit(5);
        $GroupList = $GroupList->get();

        $response = array();
        $response['results'] = $GroupList;
        $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function searchItemSubGroupAccessories(Request $request)
    {
        $companyid = WlmstItemSubgroup::find($request->plate_subgroup)->company_id;
        $GroupList = array();
        $GroupList = WlmstItemSubgroup::select('id', 'itemsubgroupname as text');
        $GroupList->where('wlmst_item_subgroups.company_id', $companyid);
        $GroupList->where('wlmst_item_subgroups.itemgroup_id', '2');
        $GroupList->where('itemsubgroupname', 'like', "%" . $request->q . "%");
        $GroupList->limit(5);
        $GroupList = $GroupList->get();

        $response = array();
        $response['results'] = $GroupList;
        $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function searchItemForDiscount(Request $request)
    {
        $brand_query = Wltrn_QuotItemdetail::query();
        $brand_query->select('wltrn_quot_itemdetails.item_id as id', 'wlmst_items.itemname as text');
        $brand_query->leftJoin('wlmst_items', 'wlmst_items.id', '=', 'wltrn_quot_itemdetails.item_id');
        $brand_query->where('wltrn_quot_itemdetails.quot_id', $request->quot_id);
        $brand_query->where('wltrn_quot_itemdetails.quotgroup_id', $request->quot_groupid);
        $brand_query->where('wlmst_items.itemname', 'like', "%" . $request->q . "%");
        $brand_query->groupBy(['wltrn_quot_itemdetails.item_id', 'wlmst_items.itemname']);
        $brand_query->limit(5);
        $brand_query_data = $brand_query->get();

        $response = array();
        $response['results'] = $brand_query_data;
        $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');
    }
    public function searchItemBrandForDiscount(Request $request)
    {
        $brand_query = Wltrn_QuotItemdetail::query();
        $brand_query->select('wltrn_quot_itemdetails.itemsubgroup_id as id', 'wlmst_item_subgroups.itemsubgroupname as text');
        $brand_query->leftJoin('wlmst_item_subgroups', 'wlmst_item_subgroups.id', '=', 'wltrn_quot_itemdetails.itemsubgroup_id');
        $brand_query->where('wltrn_quot_itemdetails.quot_id', $request->quot_id);
        $brand_query->where('wltrn_quot_itemdetails.quotgroup_id', $request->quot_groupid);
        $brand_query->where('wlmst_item_subgroups.itemsubgroupname', 'like', "%" . $request->q . "%");
        $brand_query->groupBy(['wltrn_quot_itemdetails.itemsubgroup_id', 'wlmst_item_subgroups.itemsubgroupname']);
        $brand_query->limit(5);
        $brand_query_data = $brand_query->get();

        $response = array();
        $response['results'] = $brand_query_data;
        $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function searchItemSubGroupWhitelion(Request $request)
    {
        $companyid = WlmstItemSubgroup::find($request->plate_subgroup)->company_id;
        $GroupList = array();
        $GroupList = WlmstItemSubgroup::select('id', 'itemsubgroupname as text');
        // $GroupList->where('wlmst_item_subgroups.company_id', $companyid);
        $GroupList->whereRaw("find_in_set(" . $companyid . ",wlmst_item_subgroups.company_id)");
        $GroupList->where('wlmst_item_subgroups.itemgroup_id', '1');
        $GroupList->where('itemsubgroupname', 'like', "%" . $request->q . "%");
        $GroupList->limit(5);
        $GroupList = $GroupList->get();

        $response = array();
        $response['results'] = $GroupList;
        $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    // NEW UPDATE START
    public function searchBoardAddons(Request $request)
    {
        $GroupList = array();

        $columns = array(
            'wlmst_item_prices.id as id',
            'wlmst_items.itemname as text',
        );

        $GroupList = Wlmst_ItemPrice::query();
        $GroupList->select($columns);
        $GroupList->leftJoin('wlmst_items', 'wlmst_items.id', '=', 'wlmst_item_prices.item_id');
        $GroupList->where('wlmst_items.isactive', 1);
        $GroupList->whereRaw("find_in_set(6,wlmst_items.itemcategory_id)");
        $GroupList->whereRaw("find_in_set('POSH',wlmst_item_prices.item_type)");
        // $GroupList->addSelect(DB::raw("'1' as qty"));

        $GroupList->where('wlmst_items.itemname', 'like', "%" . $request->q . "%");
        $GroupList->limit(10);

        $response = array();
        $response['results'] = $GroupList->get();
        $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function getItemPriceOnChange(Request $request)
    {
        $GroupList = array();

        $columns = array(
            'wlmst_item_prices.id',
            'wlmst_item_prices.mrp as mrp',
        );
        $GroupList = Wlmst_ItemPrice::query();
        $GroupList->select($columns);
        $GroupList->where('wlmst_item_prices.id', $request->item_price_id);

        $response = array();
        $response['results'] = $GroupList->first();
        $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function BoardAddonsSave(Request $request)
    {
        if ($request->item_price_id == '') {
            $res_status = 0;
            $res_msg = 'Please Select Item';
            $res_statuscode = '400';
        } else if ($request->item_qty == ' ') {
            $res_status = 0;
            $res_msg = 'Please Enter Item Qty';
            $res_statuscode = '400';
        } else if ($request->item_price == ' ') {
            $res_status = 0;
            $res_msg = 'Please Select Item Again';
            $res_statuscode = '400';
        } else {
            $QuotationMaster = Wltrn_Quotation::find($request->quot_id);
            $QuotationItemDetailMaster = Wltrn_QuotItemdetail::query()->where('wltrn_quot_itemdetails.quot_id', $request->quot_id)->where('wltrn_quot_itemdetails.quotgroup_id', $request->quot_groupid)->where('wltrn_quot_itemdetails.room_no', $request->room_no)->where('wltrn_quot_itemdetails.board_no', $request->board_no)->first();
            $Old_QuotationItemDetailMaster = Wltrn_QuotItemdetail::query()->where('wltrn_quot_itemdetails.quot_id', $request->quot_id)->where('wltrn_quot_itemdetails.quotgroup_id', $request->quot_groupid)->where('wltrn_quot_itemdetails.room_no', $request->room_no)->where('wltrn_quot_itemdetails.board_no', $request->board_no)->where('wltrn_quot_itemdetails.item_price_id', $request->item_price_id)->first();
            $PriceMaster = Wlmst_ItemPrice::find($request->item_price_id);
            $ItemMaster = WlmstItem::find($PriceMaster->item_id);

            if ($Old_QuotationItemDetailMaster) {
                $new_qty = ($Old_QuotationItemDetailMaster->qty + $request->item_qty);
                $SubTotal = ($request->item_price * $new_qty);
            } else {
                $new_qty = $request->item_qty;
                $SubTotal = floatval($request->item_price) * floatval($request->item_qty);
            }
            $Discount_Amount = floatval($SubTotal) * floatval($PriceMaster->discount) / 100;
            $GrossAmount = floatval($SubTotal) - floatval($Discount_Amount);
            if ($QuotationMaster->site_state_id == '9' /*IS GUJARAT*/) {
                /* CGST CALCULATION */
                $CGST_Per = $ItemMaster->cgst_per;
                $CGST_Amount = floatval($GrossAmount) * floatval($ItemMaster->cgst_per) / 100;
                /* SGST CALCULATION */
                $SGST_Per = $ItemMaster->sgst_per;
                $SGST_Amount = floatval($GrossAmount) * floatval($ItemMaster->sgst_per) / 100;
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
                $CGST_Per = "0";
                $CGST_Amount = "0.00";
                /* SGST CALCULATION */
                $SGST_Per = "0";
                $SGST_Amount = "0.00";
                /* IGST CALCULATION */
                $IGST_Per = $ItemMaster->igst_per;
                $IGST_Amount = floatval($GrossAmount) * floatval($ItemMaster->igst_per) / 100;

                /* NET AMOUNT CALCULATION */
                $NetTotalAmount = floatval($GrossAmount) + floatval($IGST_Amount);
                /* ROUND_UP AMOUNT CALCULATION */
                $RoundUpAmount = floatval($NetTotalAmount) - floatval(round($NetTotalAmount));
                /* NET FINAL AMOUNT CALCULATION */
                $NetAmount = round($NetTotalAmount);
            }

            if ($Old_QuotationItemDetailMaster) {
                $qry_add_quot_item = Wltrn_QuotItemdetail::find($Old_QuotationItemDetailMaster->id);
                $qry_add_quot_item->updateby = Auth::user()->id; //Live
                $qry_add_quot_item->updateip = $request->ip();
            } else {
                $qry_add_quot_item = new Wltrn_QuotItemdetail();
                $qry_add_quot_item->entryby = Auth::user()->id; //Live
                $qry_add_quot_item->entryip = $request->ip();
            }

            $qry_add_quot_item->quot_id = $request->quot_id;
            $qry_add_quot_item->quotgroup_id = $request->quot_groupid;
            $qry_add_quot_item->room_no = $request->room_no;
            $qry_add_quot_item->room_name = $QuotationItemDetailMaster->room_name;
            $qry_add_quot_item->board_no = $request->board_no;
            $qry_add_quot_item->board_name = $QuotationItemDetailMaster->board_name;
            $qry_add_quot_item->board_size = $QuotationItemDetailMaster->board_size;
            $qry_add_quot_item->board_item_id = $QuotationItemDetailMaster->board_item_id;
            $qry_add_quot_item->board_item_price_id = $QuotationItemDetailMaster->board_item_price_id;
            $qry_add_quot_item->board_image = $QuotationItemDetailMaster->board_image;
            $qry_add_quot_item->itemdescription = $QuotationItemDetailMaster->itemdescription;
            $qry_add_quot_item->item_id = $PriceMaster->item_id;
            $qry_add_quot_item->item_price_id = $request->item_price_id;
            $qry_add_quot_item->company_id = $PriceMaster->company_id;
            $qry_add_quot_item->itemgroup_id = $PriceMaster->itemgroup_id;
            $qry_add_quot_item->itemsubgroup_id = $PriceMaster->itemsubgroup_id;
            $qry_add_quot_item->itemcategory_id = $ItemMaster->itemcategory_id;
            $qry_add_quot_item->itemcode = $PriceMaster->code;
            $qry_add_quot_item->qty = $new_qty;
            $qry_add_quot_item->rate = $request->item_price;


            $qry_add_quot_item->discper = $PriceMaster->discount;

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

            $qry_add_quot_item->item_type = $PriceMaster->item_type;
            $qry_add_quot_item->room_range = $QuotationItemDetailMaster->room_range;
            $qry_add_quot_item->board_range = $QuotationItemDetailMaster->board_range;

            $qry_add_quot_item->save();
            $res_status = 1;
            $res_msg = 'Success';
            $res_statuscode = '200';
        }
        ;

        $response = array();
        $response['status'] = $res_status;
        $response['msg'] = $res_msg;
        $response['statuscode'] = $res_statuscode;
        return response()->json($response)->header('Content-Type', 'application/json');
    }
    // NEW UPDATE END
    public function quot_board_detail(Request $request)
    {
        DB::enableQueryLog();
        $searchColumns = array(
            0 => 'wlmst_items.itemname',
            1 => 'wlmst_item_subgroups.itemsubgroupname'
        );

        $filterColumns = array(
            0 => 'wlmst_items.itemname',
            1 => 'wlmst_item_subgroups.itemsubgroupname'
        );

        $board_item_columns = array(
            'wltrn_quot_itemdetails.id',
            'wltrn_quot_itemdetails.room_name',
            'wltrn_quot_itemdetails.board_name',
            'wltrn_quot_itemdetails.qty',
            'wltrn_quot_itemdetails.rate',
            'wltrn_quot_itemdetails.discper',
            'wltrn_quot_itemdetails.grossamount',
            'wltrn_quot_itemdetails.igst_per',
            'wltrn_quot_itemdetails.igst_amount',
            'wltrn_quot_itemdetails.cgst_per',
            'wltrn_quot_itemdetails.cgst_amount',
            'wltrn_quot_itemdetails.sgst_per',
            'wltrn_quot_itemdetails.sgst_amount',
            'wltrn_quot_itemdetails.net_amount',
            'wltrn_quot_itemdetails.item_id',
            'wltrn_quot_itemdetails.item_price_id',
            'wltrn_quot_itemdetails.itemsubgroup_id',
            'wlmst_items.itemname',
            'wlmst_items.module',
            'wltrn_quot_itemdetails.item_type',
            'wlmst_companies.companyname',
            'wltrn_quot_itemdetails.itemgroup_id',
            'wlmst_item_groups.itemgroupname',
            'wlmst_item_subgroups.itemsubgroupname',
            'wlmst_item_categories.itemcategoryname',
            'wltrn_quot_itemdetails.board_range',
        );

        $recordsTotal = Wltrn_QuotItemdetail::query();
        $recordsTotal->select($board_item_columns);
        $recordsTotal->leftJoin('wlmst_items', 'wlmst_items.id', '=', 'wltrn_quot_itemdetails.item_id');
        $recordsTotal->leftJoin('wlmst_companies', 'wlmst_companies.id', '=', 'wltrn_quot_itemdetails.company_id');
        $recordsTotal->leftJoin('wlmst_item_groups', 'wlmst_item_groups.id', '=', 'wltrn_quot_itemdetails.itemgroup_id');
        $recordsTotal->leftJoin('wlmst_item_subgroups', 'wlmst_item_subgroups.id', '=', 'wltrn_quot_itemdetails.itemsubgroup_id');
        $recordsTotal->leftJoin('wlmst_item_categories', 'wlmst_item_categories.id', '=', 'wltrn_quot_itemdetails.itemcategory_id');
        $recordsTotal->where('wltrn_quot_itemdetails.quot_id', $request->quot_id);
        $recordsTotal->where('wltrn_quot_itemdetails.quotgroup_id', $request->quot_groupid);
        $recordsTotal->where('wltrn_quot_itemdetails.room_no', $request->quot_rommno);
        $recordsTotal->where('wltrn_quot_itemdetails.board_no', $request->quot_boardno);
        $recordsTotal = json_decode(json_encode($recordsTotal->get()), true);
        $recordsTotal = count($recordsTotal);
        $recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.

        $board_query = Wltrn_QuotItemdetail::query();
        $board_query->select($board_item_columns);
        $board_query->leftJoin('wlmst_items', 'wlmst_items.id', '=', 'wltrn_quot_itemdetails.item_id');
        $board_query->leftJoin('wlmst_companies', 'wlmst_companies.id', '=', 'wltrn_quot_itemdetails.company_id');
        $board_query->leftJoin('wlmst_item_groups', 'wlmst_item_groups.id', '=', 'wltrn_quot_itemdetails.itemgroup_id');
        $board_query->leftJoin('wlmst_item_subgroups', 'wlmst_item_subgroups.id', '=', 'wltrn_quot_itemdetails.itemsubgroup_id');
        $board_query->leftJoin('wlmst_item_categories', 'wlmst_item_categories.id', '=', 'wltrn_quot_itemdetails.itemcategory_id');
        $board_query->where('wltrn_quot_itemdetails.quot_id', $request->quot_id);
        $board_query->where('wltrn_quot_itemdetails.quotgroup_id', $request->quot_groupid);
        $board_query->where('wltrn_quot_itemdetails.room_no', $request->quot_rommno);
        $board_query->where('wltrn_quot_itemdetails.board_no', $request->quot_boardno);
        $board_query->orderBy('wlmst_item_groups.sequence', 'ASC');

        // $board_query->where('wltrn_quot_itemdetails.srno', $request->quot_strno);
        
        $board_query->limit($request->length);
        $board_query->offset($request->start);
        $board_query->orderBy($filterColumns[$request['order'][0]['column']], $request['order'][0]['dir']);
        $isFilterApply = 0;

        if (isset($request->q)) {
            $isFilterApply = 1;
            $search_value = $request->q;
            $board_query->where(function ($query) use ($search_value, $searchColumns) {
                for ($i = 0; $i < count($searchColumns); $i++) {
                    if ($i == 0) {
                        $query->where($searchColumns[$i], 'like', "%" . $search_value . "%");
                    } else {
                        $query->orWhere($searchColumns[$i], 'like', "%" . $search_value . "%");
                    }
                }
            });
        }

        $data = $board_query->get();
        // echo "<pre>";
        // print_r(DB::getQueryLog());
        // die;

        $data = json_decode(json_encode($data), true);

        if ($isFilterApply == 1) {
            $recordsFiltered = count($data);
        }

        foreach ($data as $key => $value) {
            $brand = ($value['itemsubgroupname'] == '') ? $value['itemgroupname'] : $value['itemsubgroupname'];

            $data[$key]['id'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">' . ($key + 1) . '</a></h5>';

            // $data[$key]['item'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">' . $value['itemname'] . '</a></h5>';
            $data[$key]['item'] = '
            <style>
            .select2-container .select2-selection--single .select2-selection__rendered {
                line-height: 36px;
                padding-left: 0.75rem;
                color: #1c2024;
                font-size: small;
            }
            </style>
            <div class="col-md-12">
                <div class="ajax-select">
                    <select tabindex="' . ($key + 1) . '" class="form-control select2-ajax item_change_select2"
                        data-range="' . $value['board_range'] . '" 
                        data-groupid="' . $value['itemgroup_id'] . '" 
                        id="' . $value['item_id'] . '-' . $value['item_price_id'] . '-' . $value['itemsubgroup_id'] . '" 
                        name="' . $value['item_id'] . '-' . $value['item_price_id'] . '-' . $value['itemsubgroup_id'] . '" required>
                    </select>
                    <div class="invalid-feedback">
                        Select New Item.
                    </div>
                    <script type="text/javascript">
                    var newOption = new Option("' . $value['itemname'] . '", "' . $value['item_price_id'] . '", false, false);
                    $("#' . $value['item_id'] . '-' . $value['item_price_id'] . '-' . $value['itemsubgroup_id'] . '").append(newOption).trigger("change");
                    $("#' . $value['item_id'] . '-' . $value['item_price_id'] . '-' . $value['itemsubgroup_id'] . '").select2({
                        ajax: {
                            url: "' . route("quot.search.boarditem.ajax") . '",
                            dataType: "json",
                            delay: 0,
                            data: function(params) {
                                return {
                                    "range_subgroup": "' . $value['board_range'] . '",
                                    "type": "' . $value['item_type'] . '",
                                    // "itemgroup_id": groupid,
                                    q: params.term, // search term
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
                        placeholder: "Select New Item",
                        dropdownParent: $("#modalQuotBoardDetail"),
                    });
                    </script>
                </div>
            </div>';

            $data[$key]['brand'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">' . $brand . '</a></h5>';
            $data[$key]['module'] = '<h5 class="font-size-14 text-center mb-1"><a href="javascript: void(0);" class="text-dark">' . $value['module'] . '</a></h5>';
            // $data[$key]['qty'] = '<input type="number" size="4" min="0" max="100" step="1" tabindex="' . ($key + 1) . '" class="form-control newqtytext" onchange="changeqty(id)"  name="input_qty_text" id="' . $value['id'] . '" value="' .   $value['qty'] . '"  />';
            $data[$key]['qty'] = '<input type="number" size="4" min="0" max="100" step="1" tabindex="' . ($key + 2) . '" class="form-control newqtytext" onchange="changeqty(' . $value['id'] . ')" data-discount="' . $value['discper'] . '" data-igstper="' . $value['igst_per'] . '" data-cgstper="' . $value['cgst_per'] . '" data-sgstper="' . $value['sgst_per'] . '" data-select2id="' . $value['item_id'] . '-' . $value['item_price_id'] . '-' . $value['itemsubgroup_id'] . '" name="input_qty_text" id="' . $value['id'] . '" value="' . $value['qty'] . '"  />';
            $data[$key]['rate'] = '<h5 class="font-size-13 text-end mb-1"><a href="javascript: void(0);" class="text-dark rate">' . $value['rate'] . '</a></h5>';
            $data[$key]['grossamount'] = '<h5 class="font-size-13 text-end mb-1"><a href="javascript: void(0);" class="text-dark grossamount">' . number_format(round($value['grossamount']), 2, '.', '') . '</a></h5>';
            $data[$key]['gst'] = '<h5 class="font-size-13 text-end mb-1"><a href="javascript: void(0);" class="text-dark gst">' . number_format(round($value['igst_amount'] + $value['cgst_amount'] + $value['sgst_amount']), 2, '.', '') . '</a></h5>';
            $data[$key]['net_amount'] = '<h5 class="font-size-13 text-end mb-1"><a href="javascript: void(0);" class="text-dark netamount">' . number_format(round($value['net_amount']), 2, '.', '') . '</a></h5>';
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

        return $jsonData;
    }

    public function quot_board_error_detail(Request $request)
    {
        DB::enableQueryLog();
        $searchColumns = array(
            0 => 'wlmst_items.itemname',
            1 => 'company.companyname'
        );

        $filterColumns = array(
            0 => 'wlmst_items.itemname',
            1 => 'company.companyname'
        );

        $board_item_columns = array(
            'wlmst_quotation_errors.id',
            'wlmst_items.itemname',
            'company.companyname',
            'group.itemgroupname',
            'subgroup.itemsubgroupname',
            'wlmst_items.module',
            'wlmst_item_prices.mrp',
            'new_company.companyname AS new_company',
            'new_group.itemgroupname AS new_group',
            'new_subgroup.itemsubgroupname AS new_subgroup',
        );

        $recordsTotal = Wlmst_QuotationError::query();
        $recordsTotal->select($board_item_columns);
        $recordsTotal->leftJoin('wlmst_items', 'wlmst_items.id', '=', 'wlmst_quotation_errors.old_item_id');
        $recordsTotal->leftJoin('wlmst_item_prices', 'wlmst_item_prices.id', '=', 'wlmst_quotation_errors.old_item_price_id');
        $recordsTotal->leftJoin('wlmst_companies as company', 'company.id', '=', 'wlmst_quotation_errors.old_company_id');
        $recordsTotal->leftJoin('wlmst_item_groups as group', 'group.id', '=', 'wlmst_quotation_errors.old_itemgroup_id');
        $recordsTotal->leftJoin('wlmst_item_subgroups as subgroup', 'subgroup.id', '=', 'wlmst_quotation_errors.old_itemsubgroup_id');
        $recordsTotal->leftJoin('wlmst_companies as new_company', 'new_company.id', '=', 'wlmst_quotation_errors.old_company_id');
        $recordsTotal->leftJoin('wlmst_item_groups as new_group', 'new_group.id', '=', 'wlmst_quotation_errors.old_itemgroup_id');
        $recordsTotal->leftJoin('wlmst_item_subgroups as new_subgroup', 'new_subgroup.id', '=', 'wlmst_quotation_errors.old_itemsubgroup_id');
        $recordsTotal->where('wlmst_quotation_errors.quot_id', $request->quot_id);
        $recordsTotal->where('wlmst_quotation_errors.quotgroup_id', $request->quot_groupid);
        $recordsTotal->where('wlmst_quotation_errors.roomno', $request->quot_rommno);
        $recordsTotal->where('wlmst_quotation_errors.boardno', $request->quot_boardno);
        $recordsTotal = json_decode(json_encode($recordsTotal->get()), true);
        $recordsTotal = count($recordsTotal);
        $recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.

        $board_query = Wlmst_QuotationError::query();
        $board_query->select($board_item_columns);
        $board_query->leftJoin('wlmst_items', 'wlmst_items.id', '=', 'wlmst_quotation_errors.old_item_id');
        $board_query->leftJoin('wlmst_item_prices', 'wlmst_item_prices.id', '=', 'wlmst_quotation_errors.old_item_price_id');
        $board_query->leftJoin('wlmst_companies as company', 'company.id', '=', 'wlmst_quotation_errors.old_company_id');
        $board_query->leftJoin('wlmst_item_groups as group', 'group.id', '=', 'wlmst_quotation_errors.old_itemgroup_id');
        $board_query->leftJoin('wlmst_item_subgroups as subgroup', 'subgroup.id', '=', 'wlmst_quotation_errors.old_itemsubgroup_id');
        $board_query->leftJoin('wlmst_companies as new_company', 'new_company.id', '=', 'wlmst_quotation_errors.old_company_id');
        $board_query->leftJoin('wlmst_item_groups as new_group', 'new_group.id', '=', 'wlmst_quotation_errors.old_itemgroup_id');
        $board_query->leftJoin('wlmst_item_subgroups as new_subgroup', 'new_subgroup.id', '=', 'wlmst_quotation_errors.old_itemsubgroup_id');
        $board_query->where('wlmst_quotation_errors.quot_id', $request->quot_id);
        $board_query->where('wlmst_quotation_errors.quotgroup_id', $request->quot_groupid);
        $board_query->where('wlmst_quotation_errors.roomno', $request->quot_rommno);
        $board_query->where('wlmst_quotation_errors.boardno', $request->quot_boardno);

        $board_query->limit($request->length);
        $board_query->offset($request->start);
        $board_query->orderBy($filterColumns[$request['order'][0]['column']], $request['order'][0]['dir']);
        $isFilterApply = 0;

        if (isset($request->q)) {
            $isFilterApply = 1;
            $search_value = $request->q;
            $board_query->where(function ($query) use ($search_value, $searchColumns) {
                for ($i = 0; $i < count($searchColumns); $i++) {
                    if ($i == 0) {
                        $query->where($searchColumns[$i], 'like', "%" . $search_value . "%");
                    } else {
                        $query->orWhere($searchColumns[$i], 'like', "%" . $search_value . "%");
                    }
                }
            });
        }

        $data = $board_query->get();
        // echo "<pre>";
        // print_r(DB::getQueryLog());
        // die;

        $data = json_decode(json_encode($data), true);

        if ($isFilterApply == 1) {
            $recordsFiltered = count($data);
        }

        foreach ($data as $key => $value) {
            $data[$key]['id'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">' . ($key + 1) . '</a></h5>';
            $data[$key]['item'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">' . $value['itemname'] . '</a></h5>';
            $data[$key]['brand'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">' . $value['companyname'] . ' <br>' . $value['itemgroupname'] . '. <br>' . $value['itemsubgroupname'] . '</a></h5>';
            $data[$key]['module'] = '<h5 class="font-size-14 mb-1 text-center"><a href="javascript: void(0);" class="text-dark">' . $value['module'] . '</a></h5>';
            $data[$key]['rate'] = '<h5 class="font-size-14 mb-1 text-center"><a href="javascript: void(0);" class="text-dark">' . $value['mrp'] . '</a></h5>';
            $data[$key]['new_brand'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">' . $value['new_company'] . ' <br>' . $value['new_group'] . '. <br>' . $value['new_subgroup'] . '</a></h5>';

            $uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';

            $uiAction .= '<li class="list-inline-item px-2">';
            $uiAction .= '<a onclick="delete_board_error_Warning(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Delete"><i class="bx bx-trash-alt"></i></a>';
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

        return $jsonData;
    }
    public function change_board_satus(Request $request)
    {
        try {
            if($request->type == "BOARD"){
                $ItemCompany = Wltrn_QuotItemdetail::where([
                    ['wltrn_quot_itemdetails.quot_id', $request->quot_id],
                    ['wltrn_quot_itemdetails.quotgroup_id', $request->quotgroup_id],
                    ['wltrn_quot_itemdetails.room_no', $request->room_no],
                    ['wltrn_quot_itemdetails.board_no', $request->board_no]
                ]);
                $ItemCompany->update(['isactiveboard' => $request->status]);
            }elseif($request->type == "ROOM"){
                $ItemCompany = Wltrn_QuotItemdetail::where([
                    ['wltrn_quot_itemdetails.quot_id', $request->quot_id],
                    ['wltrn_quot_itemdetails.quotgroup_id', $request->quotgroup_id],
                    ['wltrn_quot_itemdetails.room_no', $request->room_no]
                ]);
                $ItemCompany->update(['isactiveroom' => $request->status]);
            }
            $response = successRes("Board Status Updated Successfully");
        } catch (QueryException $ex) {
            $response = errorRes("Status Not Updtaed Please Contact To Admin !");
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }
    public function delete_board_error(Request $request)
    {
        $ItemCompany = Wlmst_QuotationError::find($request->id);
        if ($ItemCompany) {
            $ItemCompany->delete();
        }
        $response = successRes("Successfully delete Board Error");
        return response()->json($response)->header('Content-Type', 'application/json');
    }
    public function delete_quot_board(Request $request)
    {
        $quot_Board = Wltrn_QuotItemdetail::where([
            ['wltrn_quot_itemdetails.quot_id', $request->quot_id],
            ['wltrn_quot_itemdetails.quotgroup_id', $request->quotgroup_id],
            ['wltrn_quot_itemdetails.room_no', $request->room_no],
            ['wltrn_quot_itemdetails.board_no', $request->board_no]
        ]);
        $quot_Board->delete();
        $response = successRes("Successfully delete Board");
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function show_selected_range(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'range_type' => ['required'],
        ]);

        $board_detail = '';
        if ($validator->fails()) {
            $status = 0;
            $message = "Please Enter Valid Perameater";
            $data = $validator->errors();
        } else {
            try {
                if ($request->range_type == 'QUOTATIONRANGE') {
                    $range = explode(',', $request->range);
                    foreach ($range as $key => $value) {
                        $option[$key]['id'] = $value;
                        $option[$key]['text'] = WlmstItemSubgroup::find($value)->itemsubgroupname;
                    }
                    $status = 1;
                    $message = "Successs";
                    $data = $option;
                    $board_detail = '';
                } elseif ($request->range_type == 'BOARDRANGE') {

                    $option = array();
                    if ($request->range != null || $request->range != '') {

                        $range = explode(',', $request->range);
                        foreach ($range as $key => $value) {
                            $option[$key]['id'] = $value;
                            $option[$key]['text'] = WlmstItemSubgroup::find($value)->itemsubgroupname;
                        }
                    }

                    $board_detail_columns = array(
                        'wltrn_quot_itemdetails.room_no',
                        'wltrn_quot_itemdetails.room_name',
                        'wltrn_quot_itemdetails.board_no',
                        'wltrn_quot_itemdetails.board_name',
                        'wltrn_quot_itemdetails.board_image',
                    );

                    $board_query = Wltrn_QuotItemdetail::query();
                    $board_query->select($board_detail_columns);
                    $board_query->selectRaw('SUM(wltrn_quot_itemdetails.grossamount) as gross_amount');
                    $board_query->selectRaw('SUM(wltrn_quot_itemdetails.igst_amount) as igst_amount');
                    $board_query->selectRaw('SUM(wltrn_quot_itemdetails.cgst_amount) as cgst_amount');
                    $board_query->selectRaw('SUM(wltrn_quot_itemdetails.sgst_amount) as sgst_amount');
                    $board_query->selectRaw('SUM(wltrn_quot_itemdetails.net_amount) as net_amount');
                    $board_query->where('wltrn_quot_itemdetails.quot_id', $request->quot_id);
                    $board_query->where('wltrn_quot_itemdetails.quotgroup_id', $request->quot_groupid);
                    $board_query->where('wltrn_quot_itemdetails.room_no', $request->quot_rommno);
                    $board_query->where('wltrn_quot_itemdetails.board_no', $request->quot_boardno);
                    // $board_query->groupBy('wltrn_quot_itemdetails.board_no');
                    $board_query->groupBy($board_detail_columns);
                    foreach ($board_query->get() as $key => $value) {
                        $board_detail = $value;
                        $board_detail['board_image'] = getSpaceFilePath($value->board_image);
                    }
                    $status = 1;
                    $message = "Successs";
                    $data = $option;
                    // $board_detail = $board_query->get();
                }
            } catch (QueryException $ex) {
                $status = 0;
                $message = "Contact To Admin";
                $data = $ex->getMessage();
                $board_detail = '';
            }
        }
        $response['status'] = $status;
        $response['msg'] = $message;
        $response['data'] = $data;
        $response['board_detail'] = [$board_detail];

        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function quot_range_change(Request $request)
    {
        $res_status = "";
        $res_message = "";
        $res_data = "";
        $response = array();

        $validator = Validator::make($request->all(), [
            'quot_id' => ['required'],
            'quotgroup_id' => ['required'],
            // 'old_range' => ['required'],
            'range' => ['required'],
            'room_no' => ['required'],
            'board_no' => ['required'],
            'type' => ['required'],
        ]);

        if ($validator->fails()) {
            $res_status = 0;
            $res_message = "Please Check Perameater And Value";
            $res_data = $validator->errors();
        } else {

            // try {
            $old_range = explode(',', $request->old_range);
            $new_range = explode(',', $request->range);
            foreach ($old_range as $rage_key => $old_range_value) {
                $old_range_group = WlmstItemSubgroup::find($old_range_value)->itemgroup_id;
                $old_range_subgroup = $old_range_value;
                $old_range_company = WlmstItemSubgroup::find($old_range_value)->company_id;

                $new_range_group = WlmstItemSubgroup::find($new_range[$rage_key])->itemgroup_id;
                $new_range_subgroup = WlmstItemSubgroup::find($new_range[$rage_key])->id;
                $new_range_company = WlmstItemSubgroup::find($new_range[$rage_key])->company_id;

                if ($request->type == 'BOARD') {
                    /* CHNAGE BOARD RANGE */
                    $board_detail_query = Wltrn_QuotItemdetail::where([
                        ['wltrn_quot_itemdetails.quot_id', $request->quot_id],
                        ['wltrn_quot_itemdetails.quotgroup_id', $request->quotgroup_id],
                        ['wltrn_quot_itemdetails.room_no', $request->room_no],
                        ['wltrn_quot_itemdetails.board_no', $request->board_no],
                        ['wltrn_quot_itemdetails.itemgroup_id', $old_range_group],
                        ['wltrn_quot_itemdetails.itemsubgroup_id', '<>', '64']
                    ])->get();
                } else if ($request->type == 'FULL') {

                    $query_quot_default_range_update = Wltrn_Quotation::find($request->quot_id);
                    $query_quot_default_range_update->default_range = $request->range;
                    $query_quot_default_range_update->updateby = '1';
                    $query_quot_default_range_update->updateip = $request->ip();
                    $query_quot_default_range_update->save();

                    $board_detail_query = Wltrn_QuotItemdetail::where([
                        ['wltrn_quot_itemdetails.quot_id', $request->quot_id],
                        ['wltrn_quot_itemdetails.quotgroup_id', $request->quotgroup_id],
                        ['wltrn_quot_itemdetails.itemgroup_id', $old_range_group],
                        ['wltrn_quot_itemdetails.itemsubgroup_id', '<>', '64']
                    ])->get();
                }

                foreach ($board_detail_query as $key => $value) {

                    $item_price_detail = Wlmst_ItemPrice::where([
                        ['wlmst_item_prices.company_id', $new_range_company],
                        ['wlmst_item_prices.itemgroup_id', $new_range_group],
                        ['wlmst_item_prices.itemsubgroup_id', $new_range_subgroup],
                        ['wlmst_item_prices.item_id', $value->item_id]
                    ])->first();

                    if ($item_price_detail) {

                        $QuotationMaster = Wltrn_Quotation::find($request->quot_id);
                        $BoardItemMaster = WlmstItem::find($value->board_item_id);
                        $ItemMaster = WlmstItem::find($item_price_detail->item_id);

                        $SubTotal = floatval($item_price_detail->mrp) * floatval($value->qty);
                        $Discount_Amount = floatval($SubTotal) * floatval($item_price_detail->discount) / 100;
                        $GrossAmount = floatval($SubTotal) - floatval($Discount_Amount);

                        if ($QuotationMaster->site_state_id == '9' /*IS GUJARAT*/) {
                            /* CGST CALCULATION */
                            $CGST_Per = $ItemMaster->cgst_per;
                            $CGST_Amount = floatval($GrossAmount) * floatval($ItemMaster->cgst_per) / 100;
                            /* SGST CALCULATION */
                            $SGST_Per = $ItemMaster->sgst_per;
                            $SGST_Amount = floatval($GrossAmount) * floatval($ItemMaster->sgst_per) / 100;
                            /* IGST CALCULATION */
                            $IGST_Per = '0.00';
                            $IGST_Amount = '0.00';

                            /* NET AMOUNT CALCULATION */
                            $NetTotalAmount = floatval($GrossAmount) + floatval($CGST_Amount) + floatval($SGST_Amount);
                            /* ROUND_UP AMOUNT CALCULATION */
                            $RoundUpAmount = floatval($NetTotalAmount) - floatval(round($NetTotalAmount));
                            /* NET FINAL AMOUNT CALCULATION */
                            $NeteAmount = round($NetTotalAmount);
                        } else {
                            /* CGST CALCULATION */
                            $CGST_Per = "0";
                            $CGST_Amount = "0.00";
                            /* SGST CALCULATION */
                            $SGST_Per = "0";
                            $SGST_Amount = "0.00";
                            /* IGST CALCULATION */
                            $IGST_Per = $ItemMaster->igst_per;
                            $IGST_Amount = floatval($GrossAmount) * floatval($ItemMaster->igst_per) / 100;

                            /* NET AMOUNT CALCULATION */
                            $NetTotalAmount = floatval($GrossAmount) + floatval($IGST_Amount);
                            /* ROUND_UP AMOUNT CALCULATION */
                            $RoundUpAmount = floatval($NetTotalAmount) - floatval(round($NetTotalAmount));
                            /* NET FINAL AMOUNT CALCULATION */
                            $NeteAmount = round($NetTotalAmount);
                        }

                        $board_item_change = Wltrn_QuotItemdetail::find($value->id);
                        $board_item_change->company_id = $item_price_detail->company_id;
                        $board_item_change->itemgroup_id = $item_price_detail->itemgroup_id;
                        $board_item_change->itemsubgroup_id = $item_price_detail->itemsubgroup_id;
                        $board_item_change->itemcategory_id = $ItemMaster->itemcategory_id;
                        $board_item_change->item_id = $item_price_detail->item_id;
                        $board_item_change->itemcode = $item_price_detail->code;
                        $board_item_change->rate = $item_price_detail->mrp;

                        // $board_item_change->discper = $value->discper;
                        // $board_item_change->discamount = $value->discamount;
                        $board_item_change->discper = $item_price_detail->discount;
                        $board_item_change->discamount = $Discount_Amount;

                        $board_item_change->grossamount = $GrossAmount;
                        // $board_item_change->addamount = $value->addamount;
                        // $board_item_change->lessamount = $value->lessamount;
                        $board_item_change->taxableamount = $GrossAmount;

                        $board_item_change->igst_per = $IGST_Per;
                        $board_item_change->igst_amount = $IGST_Amount;

                        $board_item_change->cgst_per = $CGST_Per;
                        $board_item_change->cgst_amount = $CGST_Amount;

                        $board_item_change->sgst_per = $SGST_Per;
                        $board_item_change->sgst_amount = $SGST_Amount;

                        $board_item_change->roundup_amount = $RoundUpAmount;
                        $board_item_change->net_amount = $NeteAmount;

                        $board_item_change->item_price_id = $item_price_detail->id;
                        if ($request->type == 'BOARD') {

                            $board_item_change->board_range = $request->range;
                        } else if ($request->type == 'FULL') {
                            $board_item_change->board_range = $request->range;
                            $board_item_change->room_range = $request->range;
                        }
                        if ($item_price_detail->itemgroup_id == '4') {

                            $QuotBoardItemUpdate = Wltrn_QuotItemdetail::where([
                                ['wltrn_quot_itemdetails.quot_id', $board_item_change->quot_id],
                                ['wltrn_quot_itemdetails.quotgroup_id', $board_item_change->quotgroup_id],
                                ['wltrn_quot_itemdetails.room_no', $board_item_change->room_no],
                                ['wltrn_quot_itemdetails.board_no', $board_item_change->board_no],
                            ]);
                            $QuotBoardItemUpdate->update(['board_item_id' => $item_price_detail->item_id, 'board_item_price_id' => $item_price_detail->id, 'board_size' => $ItemMaster->module]);

                            $board_item_change->board_item_id = $item_price_detail->item_id;
                            $board_item_change->board_item_price_id = $item_price_detail->id;
                        }

                        // $board_item_change->board_size = $BoardItemMaster->module;
                        // $board_item_change->board_item_price_id = $item_board_price_detail->id;

                        $board_item_change->updateby = '1';
                        $board_item_change->updateip = $request->ip();
                        $board_item_change->save();
                    } else {
                        $error_query = new Wlmst_QuotationError();
                        $error_query->quot_id = $request->quot_id;
                        $error_query->quotgroup_id = $request->quotgroup_id;
                        $error_query->quotitemdetail_id = $value->id;
                        $error_query->srno = $value->srno;
                        $error_query->floorno = $value->floor_no;
                        $error_query->roomno = $value->room_no;
                        $error_query->boardno = $value->board_no;

                        $error_query->old_company_id = $value->company_id;
                        $error_query->old_itemgroup_id = $value->itemgroup_id;
                        $error_query->old_itemsubgroup_id = $value->itemsubgroup_id;
                        $error_query->old_itemcategory_id = $value->itemcategory_id;
                        $error_query->old_item_id = $value->item_id;
                        $error_query->old_itemcode = $value->itemcode;
                        $error_query->old_item_price_id = $value->item_price_id;
                        $error_query->old_range = $request->old_range;

                        $error_query->new_range = $request->range;
                        // $error_query->new_company_id = $new_range_company;
                        $error_query->new_itemgroup_id = $new_range_group;
                        $error_query->new_itemsubgroup_id = $new_range_subgroup;
                        $error_query->new_itemcategory_id = '0';
                        $error_query->new_item_id = '0';
                        $error_query->new_itemcode = '0';
                        $error_query->new_item_price_id = '0';
                        $error_query->description = 'In This Range Some Product Mismatch';
                        $error_query->status = '400';
                        $error_query->entryby = '1';
                        $error_query->entryip = $request->ip();
                        $error_query->save();

                        $error_board_item = Wltrn_QuotItemdetail::find($value->id);
                        $error_board_item->company_id = '0';
                        $error_board_item->itemgroup_id = '0';
                        $error_board_item->itemsubgroup_id = '0';
                        $error_board_item->itemcategory_id = '0';
                        $error_board_item->itemcode = '0';
                        $error_board_item->rate = '0';

                        $error_board_item->discper = '0';
                        $error_board_item->discamount = '0';
                        $error_board_item->grossamount = '0';
                        // $error_board_item->addamount = '0';
                        // $error_board_item->lessamount = '0';
                        $error_board_item->taxableamount = '0';

                        $error_board_item->igst_per = '0';
                        $error_board_item->igst_amount = '0';

                        $error_board_item->cgst_per = '0';
                        $error_board_item->cgst_amount = '0';

                        $error_board_item->sgst_per = '0';
                        $error_board_item->sgst_amount = '0';

                        $error_board_item->net_amount = '0';

                        $error_board_item->item_price_id = '0';
                        $error_board_item->board_range = $request->range;

                        $error_board_item->board_item_price_id = '0';
                        $error_board_item->status = '404';

                        $error_board_item->updateby = '1';
                        $error_board_item->updateip = $request->ip();
                        $error_board_item->save();
                    }

                    $update_board_range = Wltrn_QuotItemdetail::find($value->id);
                    if ($request->type == 'BOARD') {
                        $update_board_range->board_range = $request->range;
                    } else if ($request->type == 'FULL') {
                        $update_board_range->board_range = $request->range;
                        $update_board_range->room_range = $request->range;
                    }
                    $update_board_range->save();
                    // $count .= $key;
                }
            }

            $chk_error_columns = array(
                'wlmst_quotation_errors.old_company_id',
                'wlmst_quotation_errors.old_itemgroup_id',
                'wlmst_quotation_errors.old_itemsubgroup_id',
                'wlmst_quotation_errors.old_itemcategory_id',
                'wlmst_quotation_errors.old_item_id',
                'wlmst_quotation_errors.old_item_price_id',
                'wlmst_quotation_errors.new_company_id',
                'wlmst_quotation_errors.new_itemgroup_id',
                'wlmst_quotation_errors.new_itemsubgroup_id',
                'wlmst_quotation_errors.new_itemcategory_id',
                'wlmst_quotation_errors.description',
                'wlmst_quotation_errors.status',
            );

            $error_data_query = Wlmst_QuotationError::query();
            $error_data_query->select($chk_error_columns);
            if ($request->type == 'BOARD') {
                $error_data_query->where([
                    ['wlmst_quotation_errors.quot_id', $request->quot_id],
                    ['wlmst_quotation_errors.quotgroup_id', $request->quotgroup_id],
                    ['wlmst_quotation_errors.roomno', $request->room_no],
                    ['wlmst_quotation_errors.boardno', $request->board_no],
                    ['wlmst_quotation_errors.status', '400']
                ]);
            } else if ($request->type == 'FULL') {
                $error_data_query->where([
                    ['wlmst_quotation_errors.quot_id', $request->quot_id],
                    ['wlmst_quotation_errors.quotgroup_id', $request->quotgroup_id],
                    ['wlmst_quotation_errors.status', '400']
                ]);
            }
            $error_data = $error_data_query->get();
            if (count($error_data) >= 1) {
                $res_status = 1;
                $res_message = "In This Range Change Time Some Product Mismatch";
            } else {
                $res_status = 1;
                $res_message = "Range Change successfully ";
            }

            $res_data = $error_data;
            // } catch (QueryException $ex) {
            //     $response = array();
            //     $res_status = 0;
            //     $res_message = "Please Contact To Admin";
            //     $res_data = $ex;
            // }
        }

        $response['status'] = $res_status;
        $response['msg'] = $res_message;
        $response['data'] = $res_data;

        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function quot_Search_BoardItem(Request $request)
    {
        $GroupList = array();

        // $GroupList = WlmstItemSubgroup::select('id', 'itemsubgroupname as text');
        // $GroupList->where('wlmst_item_subgroups.itemgroup_id', '5');
        // $GroupList->where('itemsubgroupname', 'like', "%" . $request->q . "%");
        // $GroupList->limit(5);
        $columns = array(
            'wlmst_item_prices.id as id',
        );

        $GroupList = WlmstItem::query();
        $GroupList->select($columns);
        $GroupList->selectRaw('CONCAT(wlmst_items.itemname," - ",wlmst_item_prices.code) as text');
        $GroupList->leftJoin('wlmst_item_categories', 'wlmst_items.itemcategory_id', '=', 'wlmst_item_categories.id');
        $GroupList->leftJoin('wlmst_item_prices', 'wlmst_items.id', '=', 'wlmst_item_prices.item_id');
        $GroupList->leftJoin('wlmst_companies', 'wlmst_companies.id', '=', 'wlmst_item_prices.company_id');
        $GroupList->leftJoin('wlmst_item_groups', 'wlmst_item_groups.id', '=', 'wlmst_item_prices.itemgroup_id');
        $GroupList->leftJoin('wlmst_item_subgroups', 'wlmst_item_subgroups.id', '=', 'wlmst_item_prices.itemsubgroup_id');
        // $GroupList->addSelect(DB::raw("'1' as qty"));

        $Range_Subgroup = explode(',', $request->range_subgroup);
        // $Range_group = explode(',', $request->range_group);
        if($request->type == 'POSH' || $request->type == ''){
            $GroupList->where(function ($GroupList) use ($Range_Subgroup) {
                for ($i = 0; $i < count($Range_Subgroup); $i++) {
                    $range_group_id = WlmstItemSubgroup::find($Range_Subgroup[$i])->itemgroup_id;
                    // $range_group_id = $Range_group[$i];
                    $range_subgroup_id = $Range_Subgroup[$i];
                    if ($i == 0) {
                        $GroupList->where(function ($GroupList) use ($range_group_id, $range_subgroup_id) {
                            $GroupList->whereIn('wlmst_item_prices.itemgroup_id', [$range_group_id])
                                ->whereIn('wlmst_item_prices.itemsubgroup_id', [$range_subgroup_id]);
                        });
                    } else {
                        $GroupList->orWhere(function ($GroupList) use ($range_group_id, $range_subgroup_id) {
                            $GroupList->whereIn('wlmst_item_prices.itemgroup_id', [$range_group_id])
                                ->whereIn('wlmst_item_prices.itemsubgroup_id', [$range_subgroup_id]);
                        });
                    }
                }
            });
        }

        if (isset($request->company_id)) {
            $GroupList->where('wlmst_item_prices.company_id', $request->company_id);
        }

        if (isset($request->itemgroup_id)) {
            $GroupList->where('wlmst_item_prices.itemgroup_id', $request->itemgroup_id);
        }

        if (isset($request->itemsubgroup_id)) {
            $GroupList->whereIn('wlmst_item_prices.itemsubgroup_id', explode(",", $request->itemsubgroup_id));
        }

        if (isset($request->itemcategory_id)) {
            // $GroupList->where('wlmst_items.itemcategory_id', $request->itemcategory_id);
            $GroupList->whereRaw("find_in_set(" . $request->itemcategory_id . ",wlmst_items.itemcategory_id)");
        }
        if (isset($request->type) && $request->type == 'QUARTZ') {
            $GroupList->whereRaw("find_in_set('" . $request->type . "',wlmst_item_prices.item_type)");
        }
        $GroupList->where('wlmst_items.itemname', 'like', "%" . $request->q . "%");
        $GroupList->limit(5);

        $response = array();
        $response['results'] = $GroupList->get();
        $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function add_discount_model(Request $request)
    {
        DB::enableQueryLog();
        $searchColumns = array(
            0 => 'wlmst_items.itemname',
            1 => 'wlmst_item_subgroups.itemsubgroupname'
        );

        $filterColumns = array(
            0 => 'wlmst_items.itemname',
            1 => 'wlmst_item_subgroups.itemsubgroupname'
        );

        $board_item_columns = array(
            'wltrn_quot_itemdetails.rate',
            'wltrn_quot_itemdetails.discper',
            'wltrn_quot_itemdetails.igst_per',
            'wltrn_quot_itemdetails.cgst_per',
            'wltrn_quot_itemdetails.sgst_per',

            'wltrn_quot_itemdetails.item_id',
            'wltrn_quot_itemdetails.item_price_id',
            'wlmst_items.itemname',

            'wlmst_items.module',

            'wltrn_quot_itemdetails.company_id',
            'wlmst_companies.companyname',

            'wltrn_quot_itemdetails.itemgroup_id',
            'wlmst_item_groups.itemgroupname',

            'wltrn_quot_itemdetails.itemsubgroup_id',
            'wlmst_item_subgroups.itemsubgroupname',

            // 'wltrn_quot_itemdetails.itemcategory_id',
        );

        $recordsTotal = Wltrn_QuotItemdetail::query();
        $recordsTotal->select($board_item_columns);
        $recordsTotal->selectRaw('SUM(wltrn_quot_itemdetails.qty) as qty');
        $recordsTotal->selectRaw('SUM(wltrn_quot_itemdetails.grossamount) as grossamount');
        $recordsTotal->selectRaw('SUM(wltrn_quot_itemdetails.igst_amount) as igst_amount');
        $recordsTotal->selectRaw('SUM(wltrn_quot_itemdetails.cgst_amount) as cgst_amount');
        $recordsTotal->selectRaw('SUM(wltrn_quot_itemdetails.sgst_amount) as sgst_amount');
        $recordsTotal->selectRaw('SUM(wltrn_quot_itemdetails.net_amount) as net_amount');
        $recordsTotal->leftJoin('wlmst_items', 'wlmst_items.id', '=', 'wltrn_quot_itemdetails.item_id');
        $recordsTotal->leftJoin('wlmst_companies', 'wlmst_companies.id', '=', 'wltrn_quot_itemdetails.company_id');
        $recordsTotal->leftJoin('wlmst_item_groups', 'wlmst_item_groups.id', '=', 'wltrn_quot_itemdetails.itemgroup_id');
        $recordsTotal->leftJoin('wlmst_item_subgroups', 'wlmst_item_subgroups.id', '=', 'wltrn_quot_itemdetails.itemsubgroup_id');
        // $recordsTotal->leftJoin('wlmst_item_categories', 'wlmst_item_categories.id', '=', 'wltrn_quot_itemdetails.itemcategory_id');
        $recordsTotal->where('wltrn_quot_itemdetails.quot_id', $request->quot_id);
        if ($request->discount_type == 'ITEMWISE') {
            $recordsTotal->where('wltrn_quot_itemdetails.item_id', $request->list_filter);
        } elseif ($request->discount_type == 'BRANDWISE') {
            $recordsTotal->where('wltrn_quot_itemdetails.itemsubgroup_id', $request->list_filter);
        }
        $recordsTotal->orderBy('wlmst_item_groups.sequence', 'ASC');
        $recordsTotal->groupBy($board_item_columns);
        $recordsTotal = json_decode(json_encode($recordsTotal->get()), true);
        $recordsTotal = count($recordsTotal);
        $recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.

        $board_query = Wltrn_QuotItemdetail::query();
        $board_query->select($board_item_columns);
        $board_query->selectRaw('SUM(wltrn_quot_itemdetails.qty) as qty');
        $board_query->selectRaw('SUM(wltrn_quot_itemdetails.grossamount) as grossamount');
        $board_query->selectRaw('SUM(wltrn_quot_itemdetails.igst_amount) as igst_amount');
        $board_query->selectRaw('SUM(wltrn_quot_itemdetails.cgst_amount) as cgst_amount');
        $board_query->selectRaw('SUM(wltrn_quot_itemdetails.sgst_amount) as sgst_amount');
        $board_query->selectRaw('SUM(wltrn_quot_itemdetails.net_amount) as net_amount');
        $board_query->leftJoin('wlmst_items', 'wlmst_items.id', '=', 'wltrn_quot_itemdetails.item_id');
        $board_query->leftJoin('wlmst_companies', 'wlmst_companies.id', '=', 'wltrn_quot_itemdetails.company_id');
        $board_query->leftJoin('wlmst_item_groups', 'wlmst_item_groups.id', '=', 'wltrn_quot_itemdetails.itemgroup_id');
        $board_query->leftJoin('wlmst_item_subgroups', 'wlmst_item_subgroups.id', '=', 'wltrn_quot_itemdetails.itemsubgroup_id');
        // $board_query->leftJoin('wlmst_item_categories', 'wlmst_item_categories.id', '=', 'wltrn_quot_itemdetails.itemcategory_id');
        $board_query->where('wltrn_quot_itemdetails.quot_id', $request->quot_id);

        if ($request->discount_type == 'ITEMWISE') {
            $board_query->where('wltrn_quot_itemdetails.item_id', $request->list_filter);
        } elseif ($request->discount_type == 'BRANDWISE') {
            $board_query->where('wltrn_quot_itemdetails.itemsubgroup_id', $request->list_filter);
        }
        $board_query->orderBy('wlmst_item_groups.sequence', 'ASC');
        $board_query->groupBy($board_item_columns);
        // $board_query->where('wltrn_quot_itemdetails.quotgroup_id', $request->quot_groupid);
        // $board_query->where('wltrn_quot_itemdetails.srno', $request->quot_strno);
        // $board_query->where('wltrn_quot_itemdetails.room_no', $request->quot_rommno);
        // $board_query->where('wltrn_quot_itemdetails.board_no', $request->quot_boardno);

        $board_query->limit($request->length);
        $board_query->offset($request->start);
        $board_query->orderBy($filterColumns[$request['order'][0]['column']], $request['order'][0]['dir']);
        $isFilterApply = 0;

        if (isset($request->q)) {
            $isFilterApply = 1;
            $search_value = $request->q;
            $board_query->where(function ($board_query) use ($search_value, $searchColumns) {
                for ($i = 0; $i < count($searchColumns); $i++) {
                    if ($i == 0) {
                        $board_query->where($searchColumns[$i], 'like', "%" . $search_value . "%");
                    } else {
                        $board_query->orWhere($searchColumns[$i], 'like', "%" . $search_value . "%");
                    }
                }
            });
        }

        $data = $board_query->get();
        // echo "<pre>";
        // print_r(DB::getQueryLog());
        // die;

        $data = json_decode(json_encode($data), true);

        if ($isFilterApply == 1) {
            $recordsFiltered = count($data);
        }

        foreach ($data as $key => $value) {
            // $brand = ($value['itemsubgroupname'] == '') ? $value['itemgroupname'] : $value['itemsubgroupname'];

            $data[$key]['id'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">' . ($key + 1) . '</a></h5>';

            $data[$key]['item'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">' . $value['itemname'] . '</a></h5>
            <p class="text-muted mb-0 font-size-14">' . $value['companyname'] . '</p>';


            $data[$key]['brand'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">' . $value['itemsubgroupname'] . '</a></h5>
            <p class="text-muted mb-0 font-size-14  ">' . $value['itemgroupname'] . '</p>';

            $data[$key]['module'] = '<h5 class="font-size-14 text-center mb-1"><a href="javascript: void(0);" class="text-dark">' . $value['module'] . '</a></h5>';
            $data[$key]['qty'] = '<h5 class="font-size-14 text-center mb-1"><a href="javascript: void(0);" class="text-dark qty">' . $value['qty'] . '</a></h5>';
            $data[$key]['rate'] = '<h5 class="font-size-13 text-end mb-1"><a href="javascript: void(0);" class="text-dark rate">' . $value['rate'] . '</a></h5>';
            // $data[$key]['discount'] = '<input type="number" tabindex="' . ($key + 1) . '"  class="form-control newdiscounttext" onchange="changediscount(id)" name="input_discount_text" id="' . $value['id'] . '" value="' .   $value['discper'] . '"  />';
            $data[$key]['discount'] = '<input type="number" tabindex="' . ($key + 1) . '"  class="form-control newdiscounttext" 
            data-company="' . $value['company_id'] . '" 
            data-group="' . $value['itemgroup_id'] . '" 
            data-subgroup="' . $value['itemsubgroup_id'] . '"
            data-item_id="' . $value['item_id'] . '"
            data-item_price_id="' . $value['item_price_id'] . '"

            data-igstper="' . $value['igst_per'] . '" 
            data-cgstper="' . $value['cgst_per'] . '" 
            data-sgstper="' . $value['sgst_per'] . '" 
            onchange="changediscount(id)" 
            id="' . ($key + 1) . '" 
            name="input_discount_text" 
            value="' . $value['discper'] . '"  />';
            $data[$key]['grossamount'] = '<h5 class="font-size-13 text-end mb-1" ><a href="javascript: void(0);" class="text-dark grossamount">' . number_format(round($value['grossamount']), 2, '.', '') . '</a></h5>';
            $data[$key]['gst'] = '<h5 class="font-size-13 text-end mb-1"><a href="javascript: void(0);" class="text-dark gst" >' . number_format(round($value['cgst_amount'] + $value['sgst_amount']), 2, '.', '') . '</a></h5>';
            $data[$key]['netamount'] = '<h5 class="font-size-13 text-end mb-1"><a href="javascript: void(0);" class="text-dark netamount">' . number_format(round($value['net_amount']), 2, '.', '') . '</a></h5>';
        }

        $jsonData = array(
            "draw" => intval($request['draw']),
            // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($recordsTotal),
            // total number of records
            "recordsFiltered" => intval($recordsFiltered),
            // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $data,
            // total data array
            "data_query" => DB::getQueryLog(), // total data array
        );

        return $jsonData;
    }
    public function add_discount_modelold(Request $request)
    {
        DB::enableQueryLog();
        $searchColumns = array(
            0 => 'wlmst_items.itemname',
            1 => 'wlmst_item_subgroups.itemsubgroupname'
        );

        $filterColumns = array(
            0 => 'wlmst_items.itemname',
            1 => 'wlmst_item_subgroups.itemsubgroupname'
        );

        $board_item_columns = array(
            'wltrn_quot_itemdetails.id',
            'wltrn_quot_itemdetails.room_name',
            'wltrn_quot_itemdetails.board_name',
            'wltrn_quot_itemdetails.qty',
            'wltrn_quot_itemdetails.rate',
            'wltrn_quot_itemdetails.grossamount',
            'wltrn_quot_itemdetails.discper',
            'wltrn_quot_itemdetails.igst_per',
            'wltrn_quot_itemdetails.igst_amount',
            'wltrn_quot_itemdetails.cgst_per',
            'wltrn_quot_itemdetails.cgst_amount',
            'wltrn_quot_itemdetails.sgst_per',
            'wltrn_quot_itemdetails.sgst_amount',
            'wltrn_quot_itemdetails.net_amount',
            'wltrn_quot_itemdetails.item_id',
            'wltrn_quot_itemdetails.item_price_id',
            'wltrn_quot_itemdetails.itemsubgroup_id',
            'wlmst_items.itemname',
            'wlmst_items.module',
            'wlmst_companies.companyname',
            'wltrn_quot_itemdetails.itemgroup_id',
            'wlmst_item_groups.itemgroupname',
            'wlmst_item_subgroups.itemsubgroupname',
            'wlmst_item_categories.itemcategoryname',
            'wltrn_quot_itemdetails.board_range',
        );

        $recordsTotal = Wltrn_QuotItemdetail::query();
        $recordsTotal->select($board_item_columns);
        $recordsTotal->leftJoin('wlmst_items', 'wlmst_items.id', '=', 'wltrn_quot_itemdetails.item_id');
        $recordsTotal->leftJoin('wlmst_companies', 'wlmst_companies.id', '=', 'wltrn_quot_itemdetails.company_id');
        $recordsTotal->leftJoin('wlmst_item_groups', 'wlmst_item_groups.id', '=', 'wltrn_quot_itemdetails.itemgroup_id');
        $recordsTotal->leftJoin('wlmst_item_subgroups', 'wlmst_item_subgroups.id', '=', 'wltrn_quot_itemdetails.itemsubgroup_id');
        $recordsTotal->leftJoin('wlmst_item_categories', 'wlmst_item_categories.id', '=', 'wltrn_quot_itemdetails.itemcategory_id');
        $recordsTotal->where('wltrn_quot_itemdetails.quot_id', $request->quot_id);
        if ($request->discount_type == 'ITEMWISE') {
            $recordsTotal->where('wltrn_quot_itemdetails.item_id', $request->list_filter);
        } elseif ($request->discount_type == 'BRANDWISE') {
            $recordsTotal->where('wltrn_quot_itemdetails.itemsubgroup_id', $request->list_filter);
        }
        $recordsTotal->orderBy('wlmst_item_groups.sequence', 'ASC');
        $recordsTotal = json_decode(json_encode($recordsTotal->get()), true);
        $recordsTotal = count($recordsTotal);
        $recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.

        $board_query = Wltrn_QuotItemdetail::query();
        $board_query->select($board_item_columns);
        $board_query->leftJoin('wlmst_items', 'wlmst_items.id', '=', 'wltrn_quot_itemdetails.item_id');
        $board_query->leftJoin('wlmst_companies', 'wlmst_companies.id', '=', 'wltrn_quot_itemdetails.company_id');
        $board_query->leftJoin('wlmst_item_groups', 'wlmst_item_groups.id', '=', 'wltrn_quot_itemdetails.itemgroup_id');
        $board_query->leftJoin('wlmst_item_subgroups', 'wlmst_item_subgroups.id', '=', 'wltrn_quot_itemdetails.itemsubgroup_id');
        $board_query->leftJoin('wlmst_item_categories', 'wlmst_item_categories.id', '=', 'wltrn_quot_itemdetails.itemcategory_id');
        $board_query->where('wltrn_quot_itemdetails.quot_id', $request->quot_id);

        if ($request->discount_type == 'ITEMWISE') {
            $board_query->where('wltrn_quot_itemdetails.item_id', $request->list_filter);
        } elseif ($request->discount_type == 'BRANDWISE') {
            $board_query->where('wltrn_quot_itemdetails.itemsubgroup_id', $request->list_filter);
        }
        $board_query->orderBy('wlmst_item_groups.sequence', 'ASC');
        // $board_query->where('wltrn_quot_itemdetails.quotgroup_id', $request->quot_groupid);
        // $board_query->where('wltrn_quot_itemdetails.srno', $request->quot_strno);
        // $board_query->where('wltrn_quot_itemdetails.room_no', $request->quot_rommno);
        // $board_query->where('wltrn_quot_itemdetails.board_no', $request->quot_boardno);

        $board_query->limit($request->length);
        $board_query->offset($request->start);
        $board_query->orderBy($filterColumns[$request['order'][0]['column']], $request['order'][0]['dir']);
        $isFilterApply = 0;

        if (isset($request->q)) {
            $isFilterApply = 1;
            $search_value = $request->q;
            $board_query->where(function ($board_query) use ($search_value, $searchColumns) {
                for ($i = 0; $i < count($searchColumns); $i++) {
                    if ($i == 0) {
                        $board_query->where($searchColumns[$i], 'like', "%" . $search_value . "%");
                    } else {
                        $board_query->orWhere($searchColumns[$i], 'like', "%" . $search_value . "%");
                    }
                }
            });
        }

        $data = $board_query->get();
        // echo "<pre>";
        // print_r(DB::getQueryLog());
        // die;

        $data = json_decode(json_encode($data), true);

        if ($isFilterApply == 1) {
            $recordsFiltered = count($data);
        }

        foreach ($data as $key => $value) {
            $brand = ($value['itemsubgroupname'] == '') ? $value['itemgroupname'] : $value['itemsubgroupname'];

            $data[$key]['id'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">' . ($key + 1) . '</a></h5>';

            $data[$key]['item'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">' . $value['itemname'] . '</a></h5>';

            $data[$key]['brand'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">' . $brand . '</a></h5>';
            $data[$key]['module'] = '<h5 class="font-size-14 text-center mb-1"><a href="javascript: void(0);" class="text-dark">' . $value['module'] . '</a></h5>';
            $data[$key]['qty'] = '<h5 class="font-size-14 text-center mb-1"><a href="javascript: void(0);" class="text-dark qty">' . $value['qty'] . '</a></h5>';
            $data[$key]['rate'] = '<h5 class="font-size-13 text-end mb-1"><a href="javascript: void(0);" class="text-dark rate">' . $value['rate'] . '</a></h5>';
            // $data[$key]['discount'] = '<input type="number" tabindex="' . ($key + 1) . '"  class="form-control newdiscounttext" onchange="changediscount(id)" name="input_discount_text" id="' . $value['id'] . '" value="' .   $value['discper'] . '"  />';
            $data[$key]['discount'] = '<input type="number" tabindex="' . ($key + 1) . '"  class="form-control newdiscounttext" onchange="changediscount(' . $value['id'] . ')" data-igstper="' . $value['igst_per'] . '" data-cgstper="' . $value['cgst_per'] . '" data-sgstper="' . $value['sgst_per'] . '" name="input_discount_text" id="' . $value['id'] . '" value="' . $value['discper'] . '"  />';
            $data[$key]['grossamount'] = '<h5 class="font-size-13 text-end mb-1" ><a href="javascript: void(0);" class="text-dark grossamount">' . number_format(round($value['grossamount']), 2, '.', '') . '</a></h5>';
            $data[$key]['gst'] = '<h5 class="font-size-13 text-end mb-1"><a href="javascript: void(0);" class="text-dark gst" >' . number_format(round($value['cgst_amount'] + $value['sgst_amount']), 2, '.', '') . '</a></h5>';
            $data[$key]['netamount'] = '<h5 class="font-size-13 text-end mb-1"><a href="javascript: void(0);" class="text-dark netamount">' . number_format(round($value['net_amount']), 2, '.', '') . '</a></h5>';
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

        return $jsonData;
    }

    public function searchSubGroupForUpdateDiscount(Request $request)
    {
        $GroupList = array();
        // $GroupList = Wltrn_QuotItemdetail::select('id', 'itemsubgroupname as text');
        $GroupList = Wltrn_QuotItemdetail::query();
        $GroupList->where('wltrn_quot_itemdetails.quot_id', $request->quot_id);
        $GroupList->where('wltrn_quot_itemdetails.quotgroup_id', $request->quot_group_id);
        $GroupList->where('itemsubgroupname', 'like', "%" . $request->q . "%");
        $GroupList->limit(5);
        $GroupList = $GroupList->get();

        $response = array();
        $response['results'] = $GroupList;
        $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function discountBrandWiseSave(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'quot_id' => ['required'],
            'quot_group_id' => ['required'],
            'discount_type' => ['required'],
            'item' => ['required'],
            'brand' => ['required'],
            'discount' => ['required'],
        ]);

        if ($validator->fails()) {

            $response = array();
            $response['status'] = 0;
            $response['msg'] = "The request could not be understood by the server due to malformed syntax";
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors();
        } else {
            $discount_type = $request->discount_type;
            $quot_id = $request->quot_id;
            $quot_group_id = $request->quot_group_id;
            $dis_item = $request->item;
            $dis_brand = $request->brand;
            $discount = $request->discount;

            try {
                if ($discount_type == 'ALL') {
                    $QuotItemDetailArr = Wltrn_QuotItemdetail::select('*')->where([
                        ['wltrn_quot_itemdetails.quot_id', $quot_id],
                        ['wltrn_quot_itemdetails.quotgroup_id', $quot_group_id]
                    ]);
                } else if ($discount_type == 'ITEMWISE') {
                    $QuotItemDetailArr = Wltrn_QuotItemdetail::select('*')->where([
                        ['wltrn_quot_itemdetails.quot_id', $quot_id],
                        ['wltrn_quot_itemdetails.quotgroup_id', $quot_group_id],
                        ['wltrn_quot_itemdetails.item_id', $dis_item]
                    ]);
                } else if ($discount_type == 'BRANDWISE') {
                    $QuotItemDetailArr = Wltrn_QuotItemdetail::select('*')->where([
                        ['wltrn_quot_itemdetails.quot_id', $quot_id],
                        ['wltrn_quot_itemdetails.quotgroup_id', $quot_group_id],
                        ['wltrn_quot_itemdetails.itemsubgroup_id', $dis_brand]
                    ]);
                }

                foreach ($QuotItemDetailArr->get() as $key => $value) {
                    $QuotItemDetail = Wltrn_QuotItemdetail::find($value['id']);

                    $totalamt = floatval($QuotItemDetail->qty) * floatval($QuotItemDetail->rate);
                    $dis_amt = floatval($totalamt) * floatval($discount) / 100;
                    $new_grossamount = floatval($totalamt) - floatval($dis_amt);
                    $new_taxableamount = floatval($totalamt) - floatval($dis_amt);

                    $new_igst_amount = floatval($new_taxableamount) * floatval($QuotItemDetail->igst_per) / 100;
                    $new_cgst_amount = floatval($new_taxableamount) * floatval($QuotItemDetail->cgst_per) / 100;
                    $new_sgst_amount = floatval($new_taxableamount) * floatval($QuotItemDetail->sgst_per) / 100;

                    /* NET AMOUNT CALCULATION */
                    $NetTotalAmount = floatval($new_taxableamount) + floatval($new_igst_amount) + floatval($new_cgst_amount) + floatval($new_sgst_amount);
                    /* ROUND_UP AMOUNT CALCULATION */
                    $RoundUpAmount = floatval($NetTotalAmount) - floatval(round($NetTotalAmount));
                    /* NET FINAL AMOUNT CALCULATION */
                    $new_net_amount = round($NetTotalAmount);


                    $QuotItemDetail->discper = $discount;
                    $QuotItemDetail->discamount = $dis_amt;
                    $QuotItemDetail->grossamount = $new_grossamount;
                    $QuotItemDetail->taxableamount = $new_taxableamount;
                    $QuotItemDetail->igst_amount = $new_igst_amount;
                    $QuotItemDetail->cgst_amount = $new_cgst_amount;
                    $QuotItemDetail->sgst_amount = $new_sgst_amount;
                    $QuotItemDetail->roundup_amount = $RoundUpAmount;
                    $QuotItemDetail->net_amount = $new_net_amount;

                    $QuotItemDetail->save();
                }
                $response = successRes("Discount Updated ✅");
            } catch (QueryException $ex) {
                $response = errorRes("Please Contact To Admin");
            }
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }
    public function discountitemSave(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'quot_id' => ['required'],
            'quot_group_id' => ['required'],
            'change_type' => ['required'],
            'discount' => ['required'],
            'id' => ['required'],
        ]);

        if ($validator->fails()) {

            $response = array();
            $response['status'] = 0;
            $response['msg'] = "The request could not be understood by the server due to malformed syntax";
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors();
        } else {
            $quot_id = $request->quot_id;
            $quot_group_id = $request->quot_group_id;

            $quot_itemdetail_id = $request->id;
            $new_discount = $request->discount;
            $discount_change_type = $request->change_type;

            try {
                if ($discount_change_type == 'SAVEALL') {


                    foreach ($new_discount as $key => $dis_value) {
                        $QuotItemDetailArr = Wltrn_QuotItemdetail::select('*')->where([
                            ['wltrn_quot_itemdetails.quot_id', $quot_id],
                            ['wltrn_quot_itemdetails.quotgroup_id', $quot_group_id],
                            ['wltrn_quot_itemdetails.company_id', $dis_value['company']],
                            ['wltrn_quot_itemdetails.itemgroup_id', $dis_value['group']],
                            ['wltrn_quot_itemdetails.itemsubgroup_id', $dis_value['subgroup']],
                            ['wltrn_quot_itemdetails.item_id', $dis_value['item_id']],
                            ['wltrn_quot_itemdetails.item_price_id', $dis_value['item_price_id']],
                        ]);

                        foreach ($QuotItemDetailArr->get() as $key => $value) {

                            $discount_qry = Wltrn_QuotItemdetail::find($value['id']);

                            $totalamt = floatval($discount_qry->qty) * floatval($discount_qry->rate);
                            $dis_amt = floatval($totalamt) * floatval($dis_value['val']) / 100;
                            $new_grossamount = floatval($totalamt) - floatval($dis_amt);
                            $new_taxableamount = floatval($totalamt) - floatval($dis_amt);

                            $new_igst_amount = floatval($new_taxableamount) * floatval($discount_qry->igst_per) / 100;
                            $new_cgst_amount = floatval($new_taxableamount) * floatval($discount_qry->cgst_per) / 100;
                            $new_sgst_amount = floatval($new_taxableamount) * floatval($discount_qry->sgst_per) / 100;

                            /* NET AMOUNT CALCULATION */
                            $NetTotalAmount = floatval($new_taxableamount) + floatval($new_igst_amount) + floatval($new_cgst_amount) + floatval($new_sgst_amount);
                            /* ROUND_UP AMOUNT CALCULATION */
                            $RoundUpAmount = floatval($NetTotalAmount) - floatval(round($NetTotalAmount));
                            /* NET FINAL AMOUNT CALCULATION */
                            $new_net_amount = round($NetTotalAmount);

                            $discount_qry->discper = $dis_value['val'];
                            $discount_qry->discamount = $dis_amt;
                            $discount_qry->grossamount = $new_grossamount;
                            $discount_qry->taxableamount = $new_taxableamount;
                            $discount_qry->igst_amount = $new_igst_amount;
                            $discount_qry->cgst_amount = $new_cgst_amount;
                            $discount_qry->sgst_amount = $new_sgst_amount;
                            $discount_qry->roundup_amount = $RoundUpAmount;
                            $discount_qry->net_amount = $new_net_amount;

                            $discount_qry->save();
                        }
                    }
                    $response = successRes("Discount Updated ✅");
                } elseif ($discount_change_type == 'SAVEROWWISE') {
                    $discount_qry = Wltrn_QuotItemdetail::find($quot_itemdetail_id);

                    $totalamt = floatval($discount_qry->qty) * floatval($discount_qry->rate);
                    $dis_amt = floatval($totalamt) * floatval($new_discount) / 100;
                    $new_grossamount = floatval($totalamt) - floatval($dis_amt);
                    $new_taxableamount = floatval($totalamt) - floatval($dis_amt);

                    $new_igst_amount = floatval($new_taxableamount) * floatval($discount_qry->igst_per) / 100;
                    $new_cgst_amount = floatval($new_taxableamount) * floatval($discount_qry->cgst_per) / 100;
                    $new_sgst_amount = floatval($new_taxableamount) * floatval($discount_qry->sgst_per) / 100;

                    /* NET AMOUNT CALCULATION */
                    $NetTotalAmount = floatval($new_taxableamount) + floatval($new_igst_amount) + floatval($new_cgst_amount) + floatval($new_sgst_amount);
                    /* ROUND_UP AMOUNT CALCULATION */
                    $RoundUpAmount = floatval($NetTotalAmount) - floatval(round($NetTotalAmount));
                    /* NET FINAL AMOUNT CALCULATION */
                    $new_net_amount = round($NetTotalAmount);

                    $discount_qry->discper = $new_discount;
                    $discount_qry->discamount = $dis_amt;
                    $discount_qry->grossamount = $new_grossamount;
                    $discount_qry->taxableamount = $new_taxableamount;
                    $discount_qry->igst_amount = $new_igst_amount;
                    $discount_qry->cgst_amount = $new_cgst_amount;
                    $discount_qry->sgst_amount = $new_sgst_amount;
                    $discount_qry->roundup_amount = $RoundUpAmount;
                    $discount_qry->net_amount = $new_net_amount;

                    $discount_qry->save();
                }
                $response = successRes("Discount Updated ✅");
            } catch (QueryException $ex) {
                $response = errorRes("PLease Contact To Admin");
            }


            return response()->json($response)->header('Content-Type', 'application/json');
        }
    }

    public function newqtySave(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'arry_qty' => ['required']
        ]);

        if ($validator->fails()) {

            $response = array();
            $response['status'] = 0;
            $response['msg'] = "The request could not be understood by the server due to malformed syntax";
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors();
        } else {

            $arry_qty = $request->arry_qty;

            try {
                foreach ($arry_qty as $key => $qty_value) {
                    $newQuotItemDetail_qry = Wltrn_QuotItemdetail::find($qty_value['id']);
                    $newQuotDetail_qry = Wltrn_Quotation::find($newQuotItemDetail_qry->quot_id);
                    $itemPriceDetail = Wlmst_ItemPrice::find($qty_value['itemprice_id']);
                    if ($itemPriceDetail) {

                        $itemDetail = WlmstItem::find($itemPriceDetail->item_id);

                        $totalamt = floatval($qty_value['val']) * floatval($itemPriceDetail->mrp);
                        $dis_amt = floatval($totalamt) * floatval($newQuotItemDetail_qry->discper) / 100;
                        $new_grossamount = floatval($totalamt) - floatval($dis_amt);
                        $new_taxableamount = floatval($totalamt) - floatval($dis_amt);

                        if ($newQuotDetail_qry->site_state_id == '9' /*IS GUJARAT*/) {
                            /* CGST CALCULATION */
                            $CGST_Per = $itemDetail->cgst_per;
                            $CGST_Amount = floatval($new_grossamount) * floatval($itemDetail->cgst_per) / 100;
                            /* SGST CALCULATION */
                            $SGST_Per = $itemDetail->sgst_per;
                            $SGST_Amount = floatval($new_grossamount) * floatval($itemDetail->sgst_per) / 100;
                            /* IGST CALCULATION */
                            $IGST_Per = '0.00';
                            $IGST_Amount = '0.00';

                            /* NET AMOUNT CALCULATION */
                            $NetTotalAmount = floatval($new_grossamount) + floatval($CGST_Amount) + floatval($SGST_Amount);
                            /* ROUND_UP AMOUNT CALCULATION */
                            $RoundUpAmount = floatval($NetTotalAmount) - floatval(round($NetTotalAmount));
                            /* NET FINAL AMOUNT CALCULATION */
                            $NeteAmount = round($NetTotalAmount);
                        } else {
                            /* CGST CALCULATION */
                            $CGST_Per = "0";
                            $CGST_Amount = "0.00";
                            /* SGST CALCULATION */
                            $SGST_Per = "0";
                            $SGST_Amount = "0.00";
                            /* IGST CALCULATION */
                            $IGST_Per = $itemDetail->igst_per;
                            $IGST_Amount = floatval($new_grossamount) * floatval($itemDetail->igst_per) / 100;

                            /* NET AMOUNT CALCULATION */
                            $NetTotalAmount = floatval($new_grossamount) + floatval($IGST_Amount);
                            /* ROUND_UP AMOUNT CALCULATION */
                            $RoundUpAmount = floatval($NetTotalAmount) - floatval(round($NetTotalAmount));
                            /* NET FINAL AMOUNT CALCULATION */
                            $NeteAmount = round($NetTotalAmount);
                        }

                        // $new_igst_amount = floatval($new_taxableamount) * floatval($newQuotItemDetail_qry->igst_per) / 100;
                        // $new_cgst_amount = floatval($new_taxableamount) * floatval($newQuotItemDetail_qry->cgst_per) / 100;
                        // $new_sgst_amount = floatval($new_taxableamount) * floatval($newQuotItemDetail_qry->sgst_per) / 100;

                        /* NET AMOUNT CALCULATION */
                        // $NetTotalAmount = floatval($new_taxableamount) + floatval($new_igst_amount) + floatval($new_cgst_amount) + floatval($new_sgst_amount);
                        /* ROUND_UP AMOUNT CALCULATION */
                        // $RoundUpAmount = floatval($NetTotalAmount) - floatval(round($NetTotalAmount));
                        /* NET FINAL AMOUNT CALCULATION */
                        // $new_net_amount = round($NetTotalAmount);

                        $newQuotItemDetail_qry->company_id = $itemPriceDetail->company_id;
                        $newQuotItemDetail_qry->itemgroup_id = $itemPriceDetail->itemgroup_id;
                        $newQuotItemDetail_qry->itemsubgroup_id = $itemPriceDetail->itemsubgroup_id;
                        $newQuotItemDetail_qry->itemcategory_id = $itemDetail->itemcategory_id;
                        $newQuotItemDetail_qry->item_id = $itemPriceDetail->item_id;
                        $newQuotItemDetail_qry->item_price_id = $itemPriceDetail->id;
                        $newQuotItemDetail_qry->itemcode = $itemPriceDetail->code;
                        $newQuotItemDetail_qry->rate = $itemPriceDetail->mrp;

                        if ($itemPriceDetail->itemgroup_id == '4') {
                            $QuotBoardItemUpdate = Wltrn_QuotItemdetail::where([
                                ['wltrn_quot_itemdetails.quot_id', $newQuotItemDetail_qry->quot_id],
                                ['wltrn_quot_itemdetails.quotgroup_id', $newQuotItemDetail_qry->quotgroup_id],
                                ['wltrn_quot_itemdetails.room_no', $newQuotItemDetail_qry->room_no],
                                ['wltrn_quot_itemdetails.board_no', $newQuotItemDetail_qry->board_no],
                            ]);
                            $QuotBoardItemUpdate->update(['board_item_id' => $itemPriceDetail->item_id,'board_item_price_id' => $itemPriceDetail->id,'board_size' => $itemDetail->module]);

                            $newQuotItemDetail_qry->board_item_id = $itemPriceDetail->item_id;
                            $newQuotItemDetail_qry->board_item_price_id = $itemPriceDetail->id;

                            // $newQuotItemDetail_qry->board_item_id = $itemPriceDetail->item_id;
                            // $newQuotItemDetail_qry->board_item_price_id = $itemPriceDetail->id;
                        }

                        $newQuotItemDetail_qry->qty = $qty_value['val'];
                        $newQuotItemDetail_qry->discamount = $dis_amt;
                        $newQuotItemDetail_qry->grossamount = $new_grossamount;
                        $newQuotItemDetail_qry->taxableamount = $new_taxableamount;

                        $newQuotItemDetail_qry->igst_per = $IGST_Per;
                        $newQuotItemDetail_qry->igst_amount = $IGST_Amount;

                        $newQuotItemDetail_qry->cgst_per = $CGST_Per;
                        $newQuotItemDetail_qry->cgst_amount = $CGST_Amount;

                        $newQuotItemDetail_qry->sgst_per = $SGST_Per;
                        $newQuotItemDetail_qry->sgst_amount = $SGST_Amount;

                        $newQuotItemDetail_qry->roundup_amount = $RoundUpAmount;
                        $newQuotItemDetail_qry->net_amount = $NeteAmount;

                        // $newQuotItemDetail_qry->igst_amount = $new_igst_amount;
                        // $newQuotItemDetail_qry->cgst_amount = $new_cgst_amount;
                        // $newQuotItemDetail_qry->sgst_amount = $new_sgst_amount;
                        // $newQuotItemDetail_qry->roundup_amount = $RoundUpAmount;
                        // $newQuotItemDetail_qry->net_amount = $new_net_amount;

                        $newQuotItemDetail_qry->updateby = Auth::user()->id; //Live
                        $newQuotItemDetail_qry->updateip = $request->ip();

                        $newQuotItemDetail_qry->save();
                    }

                }
                $response = successRes("Qty Updated ✅");
            } catch (QueryException $ex) {
                $response = errorRes("PLease Contact To Admin");
            }

            return response()->json($response)->header('Content-Type', 'application/json');
        }
    }

    public function newBoardItemSave(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'itempriceid' => ['required'],
            'quotitemdetailid' => ['required']
        ]);

        if ($validator->fails()) {

            $response = array();
            $response['status'] = 0;
            $response['msg'] = "The request could not be understood by the server due to malformed syntax";
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors();
        } else {
            $new_itempriceid = $request->itempriceid;
            // $new_discount =  $request->discount;
            $quotitemdetailid = $request->quotitemdetailid;

            $new_item_price_detail = Wlmst_ItemPrice::find($new_itempriceid);
            $new_item_detail = WlmstItem::find($new_item_price_detail->item_id);
            $newqty_qry = Wltrn_QuotItemdetail::find($quotitemdetailid);
            $QuotationMaster = Wltrn_Quotation::find($newqty_qry->quot_id);

            $totalamt = floatval($newqty_qry->qty) * floatval($new_item_price_detail->mrp);
            $dis_amt = floatval($totalamt) * floatval($new_item_price_detail->discount) / 100;
            $new_grossamount = floatval($totalamt) - floatval($dis_amt);
            $new_taxableamount = floatval($totalamt) - floatval($dis_amt);

            if ($QuotationMaster->site_state_id == '9' /*IS GUJARAT*/) {
                /* CGST CALCULATION */
                $CGST_Per = $new_item_detail->cgst_per;
                $new_cgst_amount = floatval($new_taxableamount) * floatval($new_item_detail->cgst_per) / 100;
                /* SGST CALCULATION */
                $SGST_Per = $new_item_detail->sgst_per;
                $new_sgst_amount = floatval($new_taxableamount) * floatval($new_item_detail->sgst_per) / 100;
                /* IGST CALCULATION */
                $IGST_Per = '0.00';
                $new_igst_amount = '0.00';

                /* NET AMOUNT CALCULATION */
                $NetTotalAmount = floatval($new_taxableamount) + floatval($new_cgst_amount) + floatval($new_sgst_amount);
                /* ROUND_UP AMOUNT CALCULATION */
                $RoundUpAmount = floatval($NetTotalAmount) - floatval(round($NetTotalAmount));
                /* NET FINAL AMOUNT CALCULATION */
                $new_net_amount = round($NetTotalAmount);
            } else {
                /* CGST CALCULATION */
                $CGST_Per = "0";
                $new_cgst_amount = "0.00";
                /* SGST CALCULATION */
                $SGST_Per = "0";
                $new_sgst_amount = "0.00";
                /* IGST CALCULATION */
                $IGST_Per = $new_item_detail->igst_per;
                $new_igst_amount = floatval($new_taxableamount) * floatval($new_item_detail->igst_per) / 100;

                /* NET AMOUNT CALCULATION */
                $NetTotalAmount = floatval($new_taxableamount) + floatval($new_igst_amount);
                /* ROUND_UP AMOUNT CALCULATION */
                $RoundUpAmount = floatval($NetTotalAmount) - floatval(round($NetTotalAmount));
                /* NET FINAL AMOUNT CALCULATION */
                $new_net_amount = round($NetTotalAmount);
            }


            $newqty_qry->company_id = $new_item_price_detail->company_id;
            $newqty_qry->itemgroup_id = $new_item_price_detail->itemgroup_id;
            $newqty_qry->itemsubgroup_id = $new_item_price_detail->itemsubgroup_id;
            $newqty_qry->itemcategory_id = $new_item_detail->itemcategory_id;
            $newqty_qry->item_id = $new_item_price_detail->item_id;
            $newqty_qry->item_price_id = $new_itempriceid;
            $newqty_qry->itemcode = $new_item_price_detail->code;

            $newqty_qry->discamount = $dis_amt;
            $newqty_qry->grossamount = $new_grossamount;
            $newqty_qry->taxableamount = $new_taxableamount;
            $newqty_qry->igst_per = $new_igst_amount;
            $newqty_qry->igst_amount = $new_igst_amount;
            $newqty_qry->cgst_per = $CGST_Per;
            $newqty_qry->cgst_amount = $new_cgst_amount;
            $newqty_qry->sgst_per = $new_sgst_amount;
            $newqty_qry->sgst_amount = $new_sgst_amount;
            $newqty_qry->roundup_amount = $RoundUpAmount;
            $newqty_qry->net_amount = $new_net_amount;

            if ($new_item_price_detail->itemgroup_id == 5) {
                $newqty_qry->board_size = $new_item_price_detail->module;
                $newqty_qry->board_item_id = $new_item_price_detail->item_id;
                $newqty_qry->board_item_price_id = $new_itempriceid;
            }

            $newqty_qry->updateby = Auth::user()->id;
            $newqty_qry->updateip = $request->ip();

            $newqty_qry->save();

            $debugLog = array();
            $debugLog['name'] = "quot-itemdetail-item-edit";
            $debugLog['description'] = "quotation item id #" . $quotitemdetailid . "( New Item Price id  :" . $new_itempriceid . ") qty has been Changed ";
            saveDebugLog($debugLog);

            $response = successRes("Item Updated ✅");

            return response()->json($response)->header('Content-Type', 'application/json');
        }
    }

    public function changeQuotationStatus(Request $request)
    {

        $ClientMaster = Wltrn_Quotation::find($request->quot_id);
        $ClientMaster->updateby = Auth::user()->id;
        $ClientMaster->updateip = $request->ip();
        $ClientMaster->status = $request->status;
        $ClientMaster->save();

        if ($ClientMaster) {
            $response = successRes("Status Updated ✅");
            
            $QuotMaster = Wltrn_Quotation::find($ClientMaster->id);
            $QuotMaster->updateby = Auth::user()->id;
            $QuotMaster->updateip = $request->ip();
            if ($ClientMaster->status == 3 || $ClientMaster->status == '3') {
                // Wltrn_Quotation::where('quotgroup_id', $ClientMaster->quotgroup_id)->update(['isfinal' => 0]);
                Wltrn_Quotation::where('inquiry_id', $ClientMaster->inquiry_id)->update(['isfinal' => 0]);
                $QuotMaster->isfinal = 1;
            }else{
                $QuotMaster->isfinal = 0;
            }
            $QuotMaster->save();

            $DebugLog = new DebugLog();
            $DebugLog->user_id = 1;
            $DebugLog->name = "quotation-status-change";
            $DebugLog->description = "Quotation #" . $ClientMaster->id . "(" . $ClientMaster->quotgroup_id . ")" . " status has been change Successfully";
            $DebugLog->save();
        } else {
            $response = errorRes("Status Not Updated lease Contact To Admin");
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function GetQuotationRequestDetail(Request $request) 
    {
        $validator = Validator::make($request->all(), [
            'group_id' => ['required']
        ]);

        if ($validator->fails()) {
            $response = errorRes($validator->errors()->first());
            $response['data'] = $validator->errors();
        } else {
            $data = QuotRequest::select('quotation_request.*', 'wlmst_item_subgroups.itemsubgroupname');
            $data->leftJoin('wlmst_item_subgroups', 'wlmst_item_subgroups.id', '=', 'quotation_request.subgroup_id');
            $data->where('quotation_request.group_id', $request->group_id);
            $data->where('quotation_request.status', 0);
            $data->where('quotation_request.type', 'DISCOUNT');
            $data = $data->get();
            $response = successRes();
            $response['data'] = $data;
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function GetBrandList(Request $request) 
    {
        $data = QuotRequest::select('quotation_request.*', 'users.type as user_type', 'wlmst_item_subgroups.itemsubgroupname', 'wlmst_item_subgroups.manager_maxdisc', 'wlmst_item_subgroups.channel_partner_maxdisc', 'wlmst_item_subgroups.company_admin_maxdisc');
        $data->leftJoin('wlmst_item_subgroups', 'wlmst_item_subgroups.id', '=', 'quotation_request.subgroup_id');
        $data->leftJoin('users', 'users.id', '=', 'quotation_request.assign_to');
        $data->where('quotation_request.group_id', $request->group_id);
        $data->where('quotation_request.assign_to', Auth::user()->id);
        $data->where('quotation_request.status', 0);
        $data->where('quotation_request.type', 'DISCOUNT');
        $data = $data->get();
        if($data){
            $data = json_decode(json_encode($data), true);

            $Brand_list = "";
            foreach ($data as $key => $value) {
                $user_type_lable = getUserTypeMainLabel($value['user_type']);
                $max_dis = $value['discount'];
                if($user_type_lable == 'SALES'){
                    $max_dis = $value['manager_maxdisc'];
                }elseif (isChannelPartner($value['user_type']) != 0) {
                    $max_dis = $value['channel_partner_maxdisc'];
                }elseif ($user_type_lable == "ADMIN" || $user_type_lable == "COMPANY ADMIN") {
                    $max_dis = 100;
                }

                $Brand_list .= '<tr>';
                $Brand_list .= '<td>'.$value['itemsubgroupname'].'</td>';
                // $Brand_list .= '<td>'.$value['discount'].'%</td>';
                $inputId = $value['id'].'_'.$value['group_id'].'_discount';
                $Brand_list .= '<td><input type="number" onkeyup="changeDiscount(\'' . $inputId . '\', \'' . $value['discount'] . '\', \''.$max_dis.'\');" class="form-control" id="'.$inputId.'" name="'.$inputId.'" placeholder="Discount" value="'.$value['discount'].'" max="'.$max_dis.'" required></td>';
                $Brand_list .= '<td>';
                $Brand_list .= '<button type="button" class="btn btn-success p-1 me-2" onclick="ApprovedAndRejectDiscount(\'' . $inputId . '\',\'' . $value['id'] . '\', \'' . $value['group_id'] . '\', \'APPROVED\')">Approved</button>';
                $Brand_list .= '<button type="button" class="btn btn-danger p-1" onclick="ApprovedAndRejectDiscount(\'' . $inputId . '\',\'' . $value['id'] . '\', \'' . $value['group_id'] . '\', \'REJECT\')">Reject</button>';
                $Brand_list .= '</td>';
                $Brand_list .= '</tr>';
            }
        }

        $response = successRes();
        $response['data'] = $Brand_list;

        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function SaveDiscountApprovedOrReject(Request $request){
        if($request->type == "APPROVED"){
            $QuotReq = QuotRequest::find($request->quot_req_line_id);
            if($QuotReq){

                $objQuotRequest = QuotRequest::select('quotation_request.*', 'users.type as user_type', 'wlmst_item_subgroups.itemsubgroupname', 'wlmst_item_subgroups.manager_maxdisc', 'wlmst_item_subgroups.channel_partner_maxdisc', 'wlmst_item_subgroups.company_admin_maxdisc');
                $objQuotRequest->leftJoin('wlmst_item_subgroups', 'wlmst_item_subgroups.id', '=', 'quotation_request.subgroup_id');
                $objQuotRequest->leftJoin('users', 'users.id', '=', 'quotation_request.assign_to');
                $objQuotRequest->where('quotation_request.id', $request->quot_req_line_id);
                $objQuotRequest->where('quotation_request.type', 'DISCOUNT');
                $objQuotRequest = $objQuotRequest->first();

                if($objQuotRequest){
                    $user_type_lable = getUserTypeMainLabel($objQuotRequest['user_type']);
                    $max_dis = $objQuotRequest['discount'];
                    if($user_type_lable == 'SALES'){
                        $max_dis = $objQuotRequest['manager_maxdisc'];
                    }elseif (isChannelPartner($objQuotRequest['user_type']) != 0) {
                        $max_dis = $objQuotRequest['channel_partner_maxdisc'];
                    }elseif ($user_type_lable == "ADMIN" || $user_type_lable == "COMPANY ADMIN") {
                        $max_dis = 100;
                    }
                    
                    if((float)$request->discount > (float)$max_dis){
                        $response = errorRes("⚠️ \n Sorry, you cannot apply a discount of more than ".$max_dis."%.");
                        return response()->json($response)->header('Content-Type', 'application/json');
                    }
                }

                $QuotReq->status = 1;
                $QuotReq->updateby = Auth::user()->id;
                $QuotReq->updateip = $request->ip();
                $QuotReq->save();
                $QuotItemDetailArr = Wltrn_QuotItemdetail::select('*')->where([['wltrn_quot_itemdetails.quot_id', $QuotReq->quot_id], ['wltrn_quot_itemdetails.quotgroup_id', $QuotReq->quotgroup_id], ['wltrn_quot_itemdetails.itemsubgroup_id', $QuotReq->subgroup_id]]);
                foreach ($QuotItemDetailArr->get() as $key => $value) {
                    $QuotItemDetail = Wltrn_QuotItemdetail::find($value['id']);

                    $totalamt = floatval($QuotItemDetail->qty) * floatval($QuotItemDetail->rate);
                    $dis_amt = (floatval($totalamt) * floatval($request->discount)) / 100;
                    $new_grossamount = floatval($totalamt) - floatval($dis_amt);
                    $new_taxableamount = floatval($totalamt) - floatval($dis_amt);

                    $new_igst_amount = (floatval($new_taxableamount) * floatval($QuotItemDetail->igst_per)) / 100;
                    $new_cgst_amount = (floatval($new_taxableamount) * floatval($QuotItemDetail->cgst_per)) / 100;
                    $new_sgst_amount = (floatval($new_taxableamount) * floatval($QuotItemDetail->sgst_per)) / 100;

                    /* NET AMOUNT CALCULATION */
                    $NetTotalAmount = floatval($new_taxableamount) + floatval($new_igst_amount) + floatval($new_cgst_amount) + floatval($new_sgst_amount);
                    /* ROUND_UP AMOUNT CALCULATION */
                    $RoundUpAmount = floatval($NetTotalAmount) - floatval(round($NetTotalAmount));
                    /* NET FINAL AMOUNT CALCULATION */
                    $new_net_amount = round($NetTotalAmount);

                    $QuotItemDetail->discper = $request->discount;
                    $QuotItemDetail->discamount = $dis_amt;
                    $QuotItemDetail->grossamount = $new_grossamount;
                    $QuotItemDetail->taxableamount = $new_taxableamount;
                    $QuotItemDetail->igst_amount = $new_igst_amount;
                    $QuotItemDetail->cgst_amount = $new_cgst_amount;
                    $QuotItemDetail->sgst_amount = $new_sgst_amount;
                    $QuotItemDetail->roundup_amount = $RoundUpAmount;
                    $QuotItemDetail->net_amount = $new_net_amount;

                    $QuotItemDetail->save();
                }
                $response = successRes("Your Discount Is Approved");

                $QuotMaster = Wltrn_Quotation::find($QuotReq->quot_id);
                if($QuotMaster){
                    $Lead = Lead::find($QuotMaster->inquiry_id);
                    $notificationUserids = getParentSalePersonsIds($Lead->assigned_to);
                    $notificationUserids[] = $Lead->assigned_to;
                    $UsersNotificationTokens = UsersNotificationTokens($notificationUserids);
                    sendNotificationTOAndroid("Discount Approved", "Discount request has been approved for deal no #".$Lead->id, $UsersNotificationTokens, "LEAD", $Lead, '');
                }

                $DebugLog = new DebugLog();
                $DebugLog->user_id = Auth::user()->id;
                $DebugLog->name = "quotation-discount";
                $DebugLog->description = "Quotation Discount Approved By #" . Auth::user()->id . " (Quot id : " . $QuotReq->quot_id . " Discount : ".$request->discount.")";
                $DebugLog->save();

                $checkReqCount = QuotRequest::where('quot_id',$QuotReq->quot_id)->where('quotation_request.type', 'DISCOUNT')->whereNotIn('status',[1,2])->count();
                if($checkReqCount == 0){
                    $QuotMaster = Wltrn_Quotation::find($QuotReq->quot_id);
                    $QuotMaster->updateby = Auth::user()->id;
                    $QuotMaster->updateip = $request->ip();
                    // Wltrn_Quotation::where('quotgroup_id', $QuotReq->quotgroup_id)->update(['isfinal' => 0]);
                    Wltrn_Quotation::where('inquiry_id', $QuotMaster->inquiry_id)->update(['isfinal' => 0]);
                    $QuotMaster->status = 3;
                    $QuotMaster->isfinal = 1;
                    $QuotMaster->save();
                    if ($QuotMaster) {
                        $Lead = Lead::find($QuotMaster->inquiry_id);
                        $Lead->is_deal = 1;
                        $Lead->save();
                    }
                }

            } else {
                $response = errorRes("Please Valid Data Pass");
                $response['data'] = $QuotReq;
            }
        } else if($request->type == "REJECT") {
            $QuotReq = QuotRequest::find($request->quot_req_line_id);
            $QuotReq->status = 2;
            $QuotReq->updateby = Auth::user()->id;
            $QuotReq->updateip = $request->ip();
            $QuotReq->save();

            $checkReqCount = QuotRequest::where('quot_id',$QuotReq->quot_id)->where('quotation_request.type', 'DISCOUNT')->whereNotIn('status',[1,2])->count();
            if($checkReqCount == 0){
                $QuotMaster = Wltrn_Quotation::find($QuotReq->quot_id);
                $QuotMaster->updateby = Auth::user()->id;
                $QuotMaster->updateip = $request->ip();

                // Wltrn_Quotation::where('quotgroup_id', $QuotReq->quotgroup_id)->update(['isfinal' => 0]);

                Wltrn_Quotation::where('inquiry_id', $QuotMaster->inquiry_id)->update(['isfinal' => 0]);
                
                $QuotMaster->status = 3;
                $QuotMaster->isfinal = 1;
                $QuotMaster->save();
                if ($QuotMaster) {
                    $Lead = Lead::find($QuotMaster->inquiry_id);
                    $Lead->is_deal = 1;
                    $Lead->save();
                }
            }

            $response = successRes("Your Discount Is Reject");
            $QuotMaster = Wltrn_Quotation::find($QuotReq->quot_id);
            if($QuotMaster){
                $Lead = Lead::find($QuotMaster->inquiry_id);
                $notificationUserids = getParentSalePersonsIds($Lead->assigned_to);
                $notificationUserids[] = $Lead->assigned_to;
                $UsersNotificationTokens = UsersNotificationTokens($notificationUserids);
                sendNotificationTOAndroid("Discount Rejected", "Discount request has been rejected for deal no #".$Lead->id, $UsersNotificationTokens, "LEAD", $Lead, '');
            }

            $DebugLog = new DebugLog();
            $DebugLog->user_id = Auth::user()->id;
            $DebugLog->name = "quotation-discount";
            $DebugLog->description = "Quotation Discount Rejected By #" . Auth::user()->id . " (Quot id : " . $QuotReq->quot_id . " Discount : ".$request->discount.")";
            $DebugLog->save();
        } else {
            $response = errorRes("Please Valid Type Select");
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }
}