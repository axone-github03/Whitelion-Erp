<?php

namespace App\Http\Controllers\API\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Architect;
use App\Models\Electrician;
use App\Models\LeadStatusUpdate;
use App\Models\Order;
use App\Models\LeadTimeline;
use App\Models\Wlmst_target;
use App\Models\GiftProductOrder;
use App\Models\Lead;
use App\Models\Invoice;
use Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Mail;
use DB;

class DashboardChartController extends Controller
{
	public function __construct()
	{

		$this->middleware(function ($request, $next) {

			$tabCanAccessBy = array(0, 1, 2,101, 102, 103, 104, 105);

			if (!in_array(Auth::user()->type, $tabCanAccessBy)) {

				$response = errorRes("Invalid access", 401);
				return response()->json($response, $response['status_code'])->header('Content-Type', 'application/json');
			}
			$MyPrivilege = getMyPrivilege('dashboard');
			if ($MyPrivilege == 0) {
				$response = errorRes("Invalid access", 402);
				return response()->json($response, $response['status_code'])->header('Content-Type', 'application/json');
			}

			return $next($request);
		});
	}

	function barChartCount(Request $request)
    {
        $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
        $isSalePerson = isSalePerson();
        if ($isSalePerson == 1) {
            $childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
        }
        $isChannelPartner = isChannelPartner(Auth::user()->type);

        $req_startdate = $request->start_date;
		$req_enddate = $request->end_date;
		$req_dataType = $request->data_type;
		$req_filter_type = $request->filter_type;
		$req_user_id = $request->user_id;
		$req_channel_partner_user_id = $request->channel_partner_user_id;
		$req_user_id_ex = explode(',',$request->user_id);
		$req_channel_partner_user_id_ex = explode(',',$request->channel_partner_user_id);
		$req_channel_partner_type = $request->channel_partner_type;

        $hasFilter = 0;
        if (isset($req_channel_partner_user_id) && is_array($req_channel_partner_user_id_ex)) {
            $hasFilter = 1;
        }
        if (isset($req_user_id) && is_array($req_user_id_ex)) {
            $hasFilter = 1;
        }
        if ($req_channel_partner_type != 0 && $req_channel_partner_type == 101) {
            $hasFilter = 1;
        }

        if ($isAdminOrCompanyAdmin == 1 || $isSalePerson == 1 || $isChannelPartner != 0) {
            if ($req_dataType == 'TARGET') {
                if($req_filter_type == "" || $req_filter_type == "MONTH"){
                    $Target = Wlmst_target::query();
                    $Target->selectRaw('CONCAT(UPPER(SUBSTRING(wlmst_targetdetail.month_name, 1, 1)), LOWER(SUBSTRING(wlmst_targetdetail.month_name, 2,2))) as month_name');
                    $Target->selectRaw('SUM(wlmst_targetdetail.target_amount) as target_amount');
                    $Target->selectRaw('CASE WHEN month_number IN (4,5,6) THEN "Q1" WHEN month_number IN (7,8,9) THEN "Q2" WHEN month_number IN (10,11,12) THEN "Q3" WHEN month_number IN (1,2,3) THEN "Q4" END as quater');
                    $Target->leftJoin('wlmst_targetdetail', 'wlmst_targetdetail.target_id', '=', 'wlmst_target.id');
                    $Target->leftJoin('users', 'users.id', '=', 'wlmst_target.employeee_id');
                    $Target->leftJoin('wlmst_financialyear', 'wlmst_financialyear.id', '=', 'wlmst_target.finyear_id');
                    $Target->where('users.status', 1);
                    if (date('m', strtotime($req_startdate)) > 3) {
                        $financialyear = date('Y', strtotime($req_startdate)) . "-" . (date('Y', strtotime($req_startdate)) + 1);
                    } else {
                        $financialyear = (date('Y', strtotime($req_startdate)) - 1) . "-" . date('Y', strtotime($req_startdate));
                    }
                    $Target->where('wlmst_financialyear.name', $financialyear);
                    if ($isAdminOrCompanyAdmin == 1) {
                        if (isset($req_user_id) && $req_user_id != "") {
                            $Target->whereIn('wlmst_target.employeee_id', $req_user_id_ex);
                        }
                    } else if ($isSalePerson == 1) {
                        if (isset($req_user_id) && $req_user_id != "" ) {
                            $Target->whereIn('wlmst_target.employeee_id', $req_user_id_ex);
                        } else {
                            $Target->where('wlmst_target.employeee_id', Auth::user()->id);
                        }
                    }
                    $Target->groupBy('month_name','month_number','quater' );
                    $Target->orderBy('quater', 'ASC');
                    $Target->orderBy('month_number', 'ASC');
                    $Target = $Target->get();
                    // $Target->transform(function ($Target) {
                    //     $Target->month_name = ucfirst($Target->month_name);
                    //     return $Target;
                    // });
                    $MonthArr = $Target->pluck('month_name');
                    $CountArr = $Target->pluck('target_amount');
                    if(isset($req_channel_partner_user_id) && count($req_channel_partner_user_id_ex) > 0) {
                        $response = successRes();
                        $response['MonthArr'] = array();
                        $response['CountArr'] = array();
                    } else {
                        $response = successRes();
                        $response['MonthArr'] = $MonthArr;
                        $response['CountArr'] = $CountArr;
                    }
                    $response['title'] = $req_dataType;
                    $response['chart_title'] = "Target's";
                    $response['type'] = 1;
                } else if($req_filter_type == "YEAR") {
                    $Target = Wlmst_target::query();
                    $Target->selectRaw('wlmst_financialyear.name as month');
                    $Target->selectRaw('SUM(wlmst_target.total_target) as target_amount');
                    $Target->leftJoin('wlmst_targetdetail', 'wlmst_targetdetail.target_id', '=', 'wlmst_target.id');
                    $Target->leftJoin('users', 'users.id', '=', 'wlmst_target.employeee_id');
                    $Target->leftJoin('wlmst_financialyear', 'wlmst_financialyear.id', '=', 'wlmst_target.finyear_id');
                    $Target->where('users.status', 1);
				    if ($isAdminOrCompanyAdmin == 1) {
                        if (isset($req_user_id) && $req_user_id != "") {
                            $Target->whereIn('wlmst_target.employeee_id', $req_user_id_ex);
                        }
                    } else if ($isSalePerson == 1) {
                        if (isset($req_user_id) && $req_user_id != "" ) {
                            $Target->whereIn('wlmst_target.employeee_id', $req_user_id_ex);
                        } else {
                            $Target->where('wlmst_target.employeee_id', Auth::user()->id);
                        }
                    }
                    $Target->groupBy('wlmst_financialyear.name');
                    $Target->orderBy('wlmst_financialyear.name', 'ASC');
                    $Target = $Target->get();
                    $MonthArr = $Target->pluck('month');
                    $CountArr = $Target->pluck('target_amount');
                    $response = successRes();
                    if(isset($req_channel_partner_user_id) && count($req_channel_partner_user_id_ex) > 0) {
                        $response['MonthArr'] = array();
                        $response['CountArr'] = array();
                    } else {
                        $response['MonthArr'] = $MonthArr;
                        $response['CountArr'] = $CountArr;
                    }
                    $response['title'] = $req_dataType;
                    $response['chart_title'] = "Target's";
                    $response['type'] = 1;
                } else if($req_filter_type == "QUARTER") {
                    $Target = Wlmst_target::select('wlmst_targetdetail.quater as month', DB::raw('SUM(wlmst_targetdetail.target_amount) as target_amount'));
                    $Target->leftJoin('wlmst_targetdetail', 'wlmst_targetdetail.target_id', '=', 'wlmst_target.id');
                    $Target->leftJoin('users', 'users.id', '=', 'wlmst_target.employeee_id');
                    $Target->leftJoin('wlmst_financialyear', 'wlmst_financialyear.id', '=', 'wlmst_target.finyear_id');
                    $Target->where('users.status', 1);
                    if (date('m', strtotime($req_startdate)) > 3) {
                        $financialyear = date('Y', strtotime($req_startdate)) . "-" . (date('Y', strtotime($req_startdate)) + 1);
                    } else {
                        $financialyear = (date('Y', strtotime($req_startdate)) - 1) . "-" . date('Y', strtotime($req_startdate));
                    }
                    $Target->where('wlmst_financialyear.name', $financialyear);
				    if ($isAdminOrCompanyAdmin == 1) {
                        if (isset($req_user_id) && $req_user_id != "") {
                            $Target->whereIn('wlmst_target.employeee_id', $req_user_id_ex);
                        }
                    } else if ($isSalePerson == 1) {
                        if (isset($req_user_id) && $req_user_id != "" ) {
                            $Target->whereIn('wlmst_target.employeee_id', $req_user_id_ex);
                        } else {
                            $Target->where('wlmst_target.employeee_id', Auth::user()->id);
                        }
                    }
				    // if ($isSalePerson == 1) {
				    // 	$Target->whereIn('wlmst_target.employeee_id', $childSalePersonsIds);
                    // }
                    $Target->groupBy('wlmst_targetdetail.quater');
                    $Target->orderBy('wlmst_targetdetail.quater', 'ASC');
                    $Target = $Target->get();
                    $CountArr = $Target->pluck('target_amount');
                    $MonthArrs = $Target->pluck('month')->all();
                    $MonthArr = array_map(function ($item) {
                        return 'q'.$item;
                    }, $MonthArrs);

                    $response = successRes();
                    if(isset($req_channel_partner_user_id) && count($req_channel_partner_user_id_ex) > 0) {
                        $response['MonthArr'] = array();
                        $response['CountArr'] = array();
                    } else {
                        $response['MonthArr'] = $MonthArr;
                        $response['CountArr'] = $CountArr;
                    }
                    $response['title'] = $req_dataType;
                    $response['chart_title'] = "Target's";
                    $response['type'] = 1;
                }
            } elseif ($req_dataType == 'ORDER') {
                if($req_filter_type == "" || $req_filter_type == "MONTH"){
                    $orderTotal = Order::select(DB::raw("DATE_FORMAT(orders.created_at, '%b') as month"), DB::raw('COUNT(orders.id) as count'));
                    $orderTotal->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
                    $orderTotal->where('orders.status', '!=', 4);
                    $orderTotal->where('channel_partner.type', '!=', 104);
                    $orderTotal->where('channel_partner.type', '!=', 105);
				    if ($isAdminOrCompanyAdmin == 1) {
				    	$orderTotal->where('channel_partner.reporting_company_id', Auth::user()->company_id);
				    	$orderTotal->where('channel_partner.reporting_manager_id', 0);
				    } else if ($isSalePerson == 1) {
                        $orderTotal->whereIn('orders.user_id', $childSalePersonsIds);
                    }else if($isChannelPartner != 0){
                        $orderTotal->where('orders.channel_partner_user_id', Auth::user()->id);
                    }
                    $orderTotal->whereYear('orders.created_at', date('Y'));
                    $orderTotal->groupBy(DB::raw("DATE_FORMAT(orders.created_at, '%b')"), DB::raw('MONTH(orders.created_at)'));
                    $orderTotal->orderBy(DB::raw('MONTH(orders.created_at)'), 'ASC');
                    $orderTotal = $orderTotal->get();
                    $MonthArr = $orderTotal->pluck('month');
                    $CountArr = $orderTotal->pluck('count');

                    $response = successRes();
                    $response['MonthArr'] = $MonthArr;
                    $response['CountArr'] = $CountArr;
                    $response['title'] = $req_dataType;
                    $response['chart_title'] = "Order's";
                    $response['type'] = 1;
                } else if($req_filter_type == "YEAR") {
                    $orderTotal = Order::query();
                    $orderTotal->selectRaw("DATE_FORMAT(orders.created_at, '%Y') as month");
                    $orderTotal->selectRaw('COUNT(orders.id) as count');
                    $orderTotal->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
                    $orderTotal->where('orders.status', '!=', 4);
                    $orderTotal->where('channel_partner.type', '!=', 104);
                    $orderTotal->where('channel_partner.type', '!=', 105);
				    if ($isAdminOrCompanyAdmin == 1) {
				    	$orderTotal->where('channel_partner.reporting_company_id', Auth::user()->company_id);
				    	$orderTotal->where('channel_partner.reporting_manager_id', 0);
				    } else if ($isSalePerson == 1) {
                        $orderTotal->whereIn('orders.user_id', $childSalePersonsIds);
                    }else if($isChannelPartner != 0){
                        $orderTotal->where('orders.channel_partner_user_id', Auth::user()->id);
                    }
                    $orderTotal->groupBy(DB::raw("DATE_FORMAT(orders.created_at, '%Y')"), DB::raw('YEAR(orders.created_at)'));
                    $orderTotal->orderBy(DB::raw('YEAR(orders.created_at)'), 'ASC');
                    $orderTotal = $orderTotal->get();
                    $CountArr = $orderTotal->pluck('count');
                    $MonthArrs = $orderTotal->pluck('month')->all();
                    $MonthArr = array_map(function ($item) {
                        return $item === null ? 'No date' : $item;
                    }, $MonthArrs);

                    $response = successRes();
                    $response['MonthArr'] = $MonthArr;
                    $response['CountArr'] = $CountArr;
                    $response['title'] = $req_dataType;
                    $response['chart_title'] = "Order's";
                    $response['type'] = 1;

                } else if($req_filter_type == "QUARTER") {
                    $orderTotal = Order::query();
                    $orderTotal->selectRaw("DATE_FORMAT(orders.created_at, '%q') as month");
                    $orderTotal->selectRaw('COUNT(orders.id) as count');
                    $orderTotal->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
                    $orderTotal->where('orders.status', '!=', 4);
                    $orderTotal->where('channel_partner.type', '!=', 104);
                    $orderTotal->where('channel_partner.type', '!=', 105);
				    if ($isAdminOrCompanyAdmin == 1) {
				    	$orderTotal->where('channel_partner.reporting_company_id', Auth::user()->company_id);
				    	$orderTotal->where('channel_partner.reporting_manager_id', 0);
				    } else if ($isSalePerson == 1) {
                        $orderTotal->whereIn('orders.user_id', $childSalePersonsIds);
                    }else if($isChannelPartner != 0){
                        $orderTotal->where('orders.channel_partner_user_id', Auth::user()->id);
                    }
                    $orderTotal->groupBy(DB::raw("DATE_FORMAT(orders.created_at, '%q')"), DB::raw('QUARTER(orders.created_at)'));
                    $orderTotal->orderBy(DB::raw('QUARTER(orders.created_at)'), 'ASC');
                    $orderTotal = $orderTotal->get();
                    $CountArr = $orderTotal->pluck('count');
                    $MonthArrs = $orderTotal->pluck('month')->all();
                    $counter = 1; 
                    $MonthArr = array_map(function ($item) use (&$counter) {
                        if ($item === 'q') {
                            $replacement = $counter;
                            $counter++; 
                            return 'Q'.$replacement;
                        }
                        return $item;
                    }, $MonthArrs);

                    $response = successRes();
                    $response['MonthArr'] = $MonthArr;
                    $response['CountArr'] = $CountArr;
                    $response['title'] = $req_dataType;
                    $response['chart_title'] = "Order's";
                    $response['type'] = 1;

                }

            } elseif ($req_dataType == 'PLACED') {
                if($req_filter_type == "" || $req_filter_type == "MONTH"){
                    $OrderPlaceAmount = Order::select(DB::raw("DATE_FORMAT(orders.created_at, '%b') as month"), DB::raw('SUM(orders.total_mrp_minus_disocunt) as PlacedAmount'));
                    $OrderPlaceAmount->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
                    $OrderPlaceAmount->whereYear('orders.created_at', date('Y'));
                    $OrderPlaceAmount->where('orders.status', '!=', 4);
                    $OrderPlaceAmount->where('channel_partner.type', '!=', 104);
                    $OrderPlaceAmount->where('channel_partner.type', '!=', 105);
                    if ($req_channel_partner_type != 0) {
                        $OrderPlaceAmount->where('channel_partner.type', $req_channel_partner_type);
                    }else{
                        $OrderPlaceAmount->where('channel_partner.type', '!=',104);
                        $OrderPlaceAmount->where('channel_partner.type', '!=',105);
                    }
                    if ($isAdminOrCompanyAdmin == 1) {
                        if ($hasFilter == 0) {
                            $OrderPlaceAmount->where('channel_partner.reporting_manager_id', 0);
                            $OrderPlaceAmount->where('channel_partner.reporting_company_id', Auth::user()->company_id);
                        }
                    } else if ($isSalePerson == 1) {
                        if ($hasFilter == 0) {
                            $OrderPlaceAmount->whereIn('orders.user_id', $childSalePersonsIds);
                        }
                    }else if($isChannelPartner != 0){
                        $OrderPlaceAmount->where('orders.channel_partner_user_id', Auth::user()->id);
                    }
        
                    if (isset($req_channel_partner_user_id) && is_array($req_channel_partner_user_id_ex)) {
        
                        $OrderPlaceAmount->whereIn('orders.channel_partner_user_id', $req_channel_partner_user_id_ex);
        
                    }
                    if (isset($req_user_id) && is_array($req_user_id_ex)) {
        
                        $salesUserIds = $req_user_id_ex;
                        $allSalesUserIds = [];
        
                        foreach ($salesUserIds as $key => $value) {
        
                            $childSalePersonsIds1 = getChildSalePersonsIds($value);
        
                            $allSalesUserIds = array_merge($allSalesUserIds, $childSalePersonsIds1);
                        }
                        $allSalesUserIds = array_unique($allSalesUserIds);
                        $allSalesUserIds = array_values($allSalesUserIds);
        
                        $OrderPlaceAmount->whereIn('orders.user_id', $allSalesUserIds);
        
                    }
                    $OrderPlaceAmount->groupBy(DB::raw("DATE_FORMAT(orders.created_at, '%b')"), DB::raw('MONTH(orders.created_at)'));
                    $OrderPlaceAmount->orderBy(DB::raw('MONTH(orders.created_at)'), 'ASC');
                    $OrderPlaceAmount = $OrderPlaceAmount->get();
                    $MonthArr = $OrderPlaceAmount->pluck('month');
                    $CountArr = $OrderPlaceAmount->pluck('PlacedAmount');

                    $response = successRes();
                    $response['MonthArr'] = $MonthArr;
                    $response['CountArr'] = $CountArr;
                    $response['title'] = $req_dataType;
                    $response['chart_title'] = "Place Order's";
                    $response['type'] = 1;
                } else if($req_filter_type == "YEAR") {
                    $OrderPlaceAmount = Order::query();
                    $OrderPlaceAmount->selectRaw("DATE_FORMAT(orders.created_at, '%Y') as month");
                    $OrderPlaceAmount->selectRaw('SUM(orders.total_mrp_minus_disocunt) as PlacedAmount');
                    $OrderPlaceAmount->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
                    $OrderPlaceAmount->where('orders.status', '!=', 4);
                    $OrderPlaceAmount->where('channel_partner.type', '!=', 104);
                    $OrderPlaceAmount->where('channel_partner.type', '!=', 105);
                    if ($req_channel_partner_type != 0) {
                        $OrderPlaceAmount->where('channel_partner.type', $req_channel_partner_type);
                    }else{
                        $OrderPlaceAmount->where('channel_partner.type', '!=',104);
                        $OrderPlaceAmount->where('channel_partner.type', '!=',105);
                    }
                    if ($isAdminOrCompanyAdmin == 1) {
                        if ($hasFilter == 0) {
                            $OrderPlaceAmount->where('channel_partner.reporting_manager_id', 0);
                            $OrderPlaceAmount->where('channel_partner.reporting_company_id', Auth::user()->company_id);
                        }
                    } else if ($isSalePerson == 1) {
                        if ($hasFilter == 0) {
                            $OrderPlaceAmount->whereIn('orders.user_id', $childSalePersonsIds);
                        }
                    }else if($isChannelPartner != 0){
                        $OrderPlaceAmount->where('orders.channel_partner_user_id', Auth::user()->id);
                    }
        
                    if (isset($req_channel_partner_user_id) && is_array($req_channel_partner_user_id_ex)) {
        
                        $OrderPlaceAmount->whereIn('orders.channel_partner_user_id', $req_channel_partner_user_id_ex);
        
                    }
                    if (isset($req_user_id) && is_array($req_user_id_ex)) {
        
                        $salesUserIds = $req_user_id_ex;
                        $allSalesUserIds = [];
        
                        foreach ($salesUserIds as $key => $value) {
        
                            $childSalePersonsIds1 = getChildSalePersonsIds($value);
        
                            $allSalesUserIds = array_merge($allSalesUserIds, $childSalePersonsIds1);
                        }
                        $allSalesUserIds = array_unique($allSalesUserIds);
                        $allSalesUserIds = array_values($allSalesUserIds);
        
                        $OrderPlaceAmount->whereIn('orders.user_id', $allSalesUserIds);
        
                    }
                    $OrderPlaceAmount->groupBy(DB::raw("DATE_FORMAT(orders.created_at, '%Y')"), DB::raw('YEAR(orders.created_at)'));
                    $OrderPlaceAmount->orderBy(DB::raw('YEAR(orders.created_at)'), 'ASC');
                    $OrderPlaceAmount = $OrderPlaceAmount->get();
                    $CountArr = $OrderPlaceAmount->pluck('PlacedAmount');
                    $MonthArrs = $OrderPlaceAmount->pluck('month')->all();
                    $MonthArr = array_map(function ($item) {
                        return $item === null ? 'No date' : $item;
                    }, $MonthArrs);

                    $response = successRes();
                    $response['MonthArr'] = $MonthArr;
                    $response['CountArr'] = $CountArr;
                    $response['title'] = $req_dataType;
                    $response['chart_title'] = "Place Order's";
                    $response['type'] = 1;
                } else if($req_filter_type == "QUARTER") {
                    $OrderPlaceAmount = Order::query();
                    $OrderPlaceAmount->selectRaw("DATE_FORMAT(orders.created_at, '%q') as month");
                    $OrderPlaceAmount->selectRaw('SUM(orders.total_mrp_minus_disocunt) as PlacedAmount');
                    $OrderPlaceAmount->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
                    $OrderPlaceAmount->where('orders.status', '!=', 4);
                    $OrderPlaceAmount->where('channel_partner.type', '!=', 104);
                    $OrderPlaceAmount->where('channel_partner.type', '!=', 105);
				    if ($req_channel_partner_type != 0) {
                        $OrderPlaceAmount->where('channel_partner.type', $req_channel_partner_type);
                    }else{
                        $OrderPlaceAmount->where('channel_partner.type', '!=',104);
                        $OrderPlaceAmount->where('channel_partner.type', '!=',105);
                    }
                    if ($isAdminOrCompanyAdmin == 1) {
                        if ($hasFilter == 0) {
                            $OrderPlaceAmount->where('channel_partner.reporting_manager_id', 0);
                            $OrderPlaceAmount->where('channel_partner.reporting_company_id', Auth::user()->company_id);
                        }
                    } else if ($isSalePerson == 1) {
                        if ($hasFilter == 0) {
                            $OrderPlaceAmount->whereIn('orders.user_id', $childSalePersonsIds);
                        }
                    }else if($isChannelPartner != 0){
                        $OrderPlaceAmount->where('orders.channel_partner_user_id', Auth::user()->id);
                    }
        
                    if (isset($req_channel_partner_user_id) && is_array($req_channel_partner_user_id_ex)) {
        
                        $OrderPlaceAmount->whereIn('orders.channel_partner_user_id', $req_channel_partner_user_id_ex);
        
                    }
                    if (isset($req_user_id) && is_array($req_user_id_ex)) {
        
                        $salesUserIds = $req_user_id_ex;
                        $allSalesUserIds = [];
        
                        foreach ($salesUserIds as $key => $value) {
        
                            $childSalePersonsIds1 = getChildSalePersonsIds($value);
        
                            $allSalesUserIds = array_merge($allSalesUserIds, $childSalePersonsIds1);
                        }
                        $allSalesUserIds = array_unique($allSalesUserIds);
                        $allSalesUserIds = array_values($allSalesUserIds);
        
                        $OrderPlaceAmount->whereIn('orders.user_id', $allSalesUserIds);
        
                    }
                    $OrderPlaceAmount->groupBy(DB::raw("DATE_FORMAT(orders.created_at, '%q')"), DB::raw('QUARTER(orders.created_at)'));
                    $OrderPlaceAmount->orderBy(DB::raw('QUARTER(orders.created_at)'), 'ASC');
                    $OrderPlaceAmount = $OrderPlaceAmount->get();
                    $CountArr = $OrderPlaceAmount->pluck('PlacedAmount');
                    $MonthArrs = $OrderPlaceAmount->pluck('month')->all();
                    $counter = 1; 
                    $MonthArr = array_map(function ($item) use (&$counter) {
                        if ($item === 'q') {
                            $replacement = $counter;
                            $counter++; 
                            return 'Q'.$replacement;
                        }
                        return $item;
                    }, $MonthArrs);

                    $response = successRes();
                    $response['MonthArr'] = $MonthArr;
                    $response['CountArr'] = $CountArr;
                    $response['title'] = $req_dataType;
                    $response['chart_title'] = "Place Order's";
                    $response['type'] = 1;
                }
            } elseif ($req_dataType == 'DISPATCHED') {
                if($req_filter_type == "" || $req_filter_type == "MONTH"){
                    $OrderDispatchedAmount = Invoice::query();
                    $OrderDispatchedAmount->selectRaw("DATE_FORMAT(orders.created_at, '%b') as month");
                    $OrderDispatchedAmount->selectRaw('SUM(invoice.total_mrp_minus_disocunt) as DispatchedAmount');
                    $OrderDispatchedAmount->leftJoin('orders', 'orders.id', '=', 'invoice.order_id');
                    $OrderDispatchedAmount->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
                    $OrderDispatchedAmount->whereYear('orders.created_at', date('Y'));
                    $OrderDispatchedAmount->whereIn('invoice.status', [2, 3]);
                    $OrderDispatchedAmount->where('channel_partner.type', '!=', 104);
                    $OrderDispatchedAmount->where('channel_partner.type', '!=', 105);
                    if ($req_channel_partner_type != 0) {
                        $OrderDispatchedAmount->where('channel_partner.type', $req_channel_partner_type);
                    }else{
                        $OrderDispatchedAmount->where('channel_partner.type', '!=',104);
                        $OrderDispatchedAmount->where('channel_partner.type', '!=',105);
                    }
                    if ($isAdminOrCompanyAdmin == 1) {
        
                        if ($hasFilter == 0) {
        
                            $OrderDispatchedAmount->where('channel_partner.reporting_manager_id', 0);
                            $OrderDispatchedAmount->where('channel_partner.reporting_company_id', Auth::user()->company_id);
                        }
                    } else if ($isSalePerson == 1) {
        
                        if ($hasFilter == 0) {
        
                            $OrderDispatchedAmount->whereIn('orders.user_id', $childSalePersonsIds);
                        }
                    }else if($isChannelPartner != 0){
                        $OrderDispatchedAmount->where('orders.channel_partner_user_id', Auth::user()->id);
                    }
                    if (isset($req_channel_partner_user_id) && is_array($req_channel_partner_user_id_ex)) {
        
                        $OrderDispatchedAmount->whereIn('orders.channel_partner_user_id', $req_channel_partner_user_id_ex);
                    }
                    if (isset($req_user_id) && is_array($req_user_id_ex)) {
        
                        $salesUserIds = $req_user_id_ex;
                        $allSalesUserIds = [];
        
                        foreach ($salesUserIds as $key => $value) {
        
                            $childSalePersonsIds1 = getChildSalePersonsIds($value);
        
                            $allSalesUserIds = array_merge($allSalesUserIds, $childSalePersonsIds1);
                        }
                        $allSalesUserIds = array_unique($allSalesUserIds);
                        $allSalesUserIds = array_values($allSalesUserIds);
        
                        $OrderDispatchedAmount->whereIn('orders.user_id', $allSalesUserIds);
        
                    }
                    $OrderDispatchedAmount->groupBy(DB::raw("DATE_FORMAT(orders.created_at, '%b')"), DB::raw('MONTH(orders.created_at)'));
                    $OrderDispatchedAmount->orderBy(DB::raw('MONTH(orders.created_at)'), 'ASC');
                    $OrderDispatchedAmount = $OrderDispatchedAmount->get();
                    $MonthArr = $OrderDispatchedAmount->pluck('month');
                    $CountArr = $OrderDispatchedAmount->pluck('DispatchedAmount');

                    $response = successRes();
                    $response['MonthArr'] = $MonthArr;
                    $response['CountArr'] = $CountArr;
                    $response['title'] = $req_dataType;
                    $response['chart_title'] = "Dispatched Order's";
                    $response['type'] = 1;
                } else if($req_filter_type == "YEAR") {
                    $OrderDispatchedAmount = Invoice::query();
                    $OrderDispatchedAmount->selectRaw("DATE_FORMAT(orders.created_at, '%Y') as month");
                    $OrderDispatchedAmount->selectRaw('SUM(invoice.total_mrp_minus_disocunt) as DispatchedAmount');
                    $OrderDispatchedAmount->leftJoin('orders', 'orders.id', '=', 'invoice.order_id');
                    $OrderDispatchedAmount->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
                    $OrderDispatchedAmount->whereIn('invoice.status', [2, 3]);
                    $OrderDispatchedAmount->where('channel_partner.type', '!=', 104);
                    $OrderDispatchedAmount->where('channel_partner.type', '!=', 105);
                    if ($req_channel_partner_type != 0) {
                        $OrderDispatchedAmount->where('channel_partner.type', $req_channel_partner_type);
                    }else{
                        $OrderDispatchedAmount->where('channel_partner.type', '!=',104);
                        $OrderDispatchedAmount->where('channel_partner.type', '!=',105);
                    }
                    if ($isAdminOrCompanyAdmin == 1) {
        
                        if ($hasFilter == 0) {
        
                            $OrderDispatchedAmount->where('channel_partner.reporting_manager_id', 0);
                            $OrderDispatchedAmount->where('channel_partner.reporting_company_id', Auth::user()->company_id);
                        }
                    } else if ($isSalePerson == 1) {
        
                        if ($hasFilter == 0) {
        
                            $OrderDispatchedAmount->whereIn('orders.user_id', $childSalePersonsIds);
                        }
                    }else if($isChannelPartner != 0){
                        $OrderDispatchedAmount->where('orders.channel_partner_user_id', Auth::user()->id);
                    }
                    if (isset($req_channel_partner_user_id) && is_array($req_channel_partner_user_id_ex)) {
        
                        $OrderDispatchedAmount->whereIn('orders.channel_partner_user_id', $req_channel_partner_user_id_ex);
                    }
                    if (isset($req_user_id) && is_array($req_user_id_ex)) {
        
                        $salesUserIds = $req_user_id_ex;
                        $allSalesUserIds = [];
        
                        foreach ($salesUserIds as $key => $value) {
        
                            $childSalePersonsIds1 = getChildSalePersonsIds($value);
        
                            $allSalesUserIds = array_merge($allSalesUserIds, $childSalePersonsIds1);
                        }
                        $allSalesUserIds = array_unique($allSalesUserIds);
                        $allSalesUserIds = array_values($allSalesUserIds);
        
                        $OrderDispatchedAmount->whereIn('orders.user_id', $allSalesUserIds);
        
                    }
                    $OrderDispatchedAmount->groupBy(DB::raw("DATE_FORMAT(orders.created_at, '%Y')"), DB::raw('YEAR(orders.created_at)'));
                    $OrderDispatchedAmount->orderBy(DB::raw('YEAR(orders.created_at)'), 'ASC');
                    $OrderDispatchedAmount = $OrderDispatchedAmount->get();
                    $CountArr = $OrderDispatchedAmount->pluck('DispatchedAmount');
                    $MonthArrs = $OrderDispatchedAmount->pluck('month')->all();
                    $MonthArr = array_map(function ($item) {
                        return $item === null ? 'No date' : $item;
                    }, $MonthArrs);

                    $response = successRes();
                    $response['MonthArr'] = $MonthArr;
                    $response['CountArr'] = $CountArr;
                    $response['title'] = $req_dataType;
                    $response['chart_title'] = "Dispatched Order's";
                    $response['type'] = 1;
                } else if($req_filter_type == "QUARTER") {
                    $OrderDispatchedAmount = Invoice::query();
                    $OrderDispatchedAmount->selectRaw("DATE_FORMAT(orders.created_at, '%q') as month");
                    $OrderDispatchedAmount->selectRaw('SUM(invoice.total_mrp_minus_disocunt) as DispatchedAmount');
                    $OrderDispatchedAmount->leftJoin('orders', 'orders.id', '=', 'invoice.order_id');
                    $OrderDispatchedAmount->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
                    $OrderDispatchedAmount->whereIn('invoice.status', [2, 3]);
                    $OrderDispatchedAmount->where('channel_partner.type', '!=', 104);
                    $OrderDispatchedAmount->where('channel_partner.type', '!=', 105);
                    if ($req_channel_partner_type != 0) {
                        $OrderDispatchedAmount->where('channel_partner.type', $req_channel_partner_type);
                    }else{
                        $OrderDispatchedAmount->where('channel_partner.type', '!=',104);
                        $OrderDispatchedAmount->where('channel_partner.type', '!=',105);
                    }
                    if ($isAdminOrCompanyAdmin == 1) {
        
                        if ($hasFilter == 0) {
        
                            $OrderDispatchedAmount->where('channel_partner.reporting_manager_id', 0);
                            $OrderDispatchedAmount->where('channel_partner.reporting_company_id', Auth::user()->company_id);
                        }
                    } else if ($isSalePerson == 1) {
        
                        if ($hasFilter == 0) {
        
                            $OrderDispatchedAmount->whereIn('orders.user_id', $childSalePersonsIds);
                        }
                    }else if($isChannelPartner != 0){
                        $OrderDispatchedAmount->where('orders.channel_partner_user_id', Auth::user()->id);
                    }
                    if (isset($req_channel_partner_user_id) && is_array($req_channel_partner_user_id_ex)) {
        
                        $OrderDispatchedAmount->whereIn('orders.channel_partner_user_id', $req_channel_partner_user_id_ex);
                    }
                    if (isset($req_user_id) && is_array($req_user_id_ex)) {
        
                        $salesUserIds = $req_user_id_ex;
                        $allSalesUserIds = [];
        
                        foreach ($salesUserIds as $key => $value) {
        
                            $childSalePersonsIds1 = getChildSalePersonsIds($value);
        
                            $allSalesUserIds = array_merge($allSalesUserIds, $childSalePersonsIds1);
                        }
                        $allSalesUserIds = array_unique($allSalesUserIds);
                        $allSalesUserIds = array_values($allSalesUserIds);
        
                        $OrderDispatchedAmount->whereIn('orders.user_id', $allSalesUserIds);
        
                    }
                    $OrderDispatchedAmount->groupBy(DB::raw("DATE_FORMAT(orders.created_at, '%q')"), DB::raw('QUARTER(orders.created_at)'));
                    $OrderDispatchedAmount->orderBy(DB::raw('QUARTER(orders.created_at)'), 'ASC');
                    $OrderDispatchedAmount = $OrderDispatchedAmount->get();
                    $CountArr = $OrderDispatchedAmount->pluck('DispatchedAmount');
                    $MonthArrs = $OrderDispatchedAmount->pluck('month')->all();
                    $counter = 1; 
                    $MonthArr = array_map(function ($item) use (&$counter) {
                        if ($item === 'q') {
                            $replacement = $counter;
                            $counter++; 
                            return 'Q'.$replacement;
                        }
                        return $item;
                    }, $MonthArrs);

                    $response = successRes();
                    $response['MonthArr'] = $MonthArr;
                    $response['CountArr'] = $CountArr;
                    $response['title'] = $req_dataType;
                    $response['chart_title'] = "Dispatched Order's";
                    $response['type'] = 1;
                }
            } elseif ($req_dataType == 'ARCHITECT') {
                if($req_filter_type == "" || $req_filter_type == "MONTH"){
                    $Architect = Architect::query();
                    $Architect->selectRaw("DATE_FORMAT(created_at, '%b') as month");
                    $Architect->selectRaw('COUNT(id) as count');
                    $Architect->whereIn('type', [201, 202]);
                    $Architect->whereYear('created_at', date('Y'));
                    $Architect->groupBy(DB::raw("DATE_FORMAT(created_at, '%b')"), DB::raw('MONTH(created_at)'));
                    $Architect->orderBy(DB::raw('MONTH(created_at)'), 'ASC');
                    if ($isAdminOrCompanyAdmin == 1) {
                        if (isset($req_user_id) && $req_user_id != "") {
                            $Architect->whereIn('sale_person_id', $req_user_id_ex);
                        }
                    } else if ($isSalePerson == 1) {
                        if (isset($req_user_id) && $req_user_id != "") {
                            $Architect->whereIn('sale_person_id', $req_user_id_ex);
                        } else {
                            $Architect->whereIn('sale_person_id', $childSalePersonsIds);
                        }
                    } else if ($isChannelPartner != 0) {
                        $Architect->where('added_by', Auth::user()->id);
                    } 
                    // if ($isSalePerson == 1) {
                    //     $Architect->whereIn('sale_person_id', $childSalePersonsIds);
                    // }
                    $Architect = $Architect->get();
                    $CountArr = $Architect->pluck('count');
                    $MonthArr = $Architect->pluck('month');

                    $response = successRes();
                    if(isset($req_channel_partner_user_id) && count($req_channel_partner_user_id_ex) > 0) {
                        $response['MonthArr'] = array();
                        $response['CountArr'] = array();
                    } else {
                        $response['MonthArr'] = $MonthArr;
                        $response['CountArr'] = $CountArr;
                    }
                    $response['title'] = $req_dataType;
                    $response['chart_title'] = 'New Architect';
                    $response['type'] = 1;
                } else if($req_filter_type == "YEAR") {
                    $Architect = Architect::query();
                    $Architect->selectRaw("DATE_FORMAT(created_at, '%Y') as month");
                    $Architect->selectRaw('COUNT(id) as count');
                    $Architect->whereIn('type', [201, 202]);
                    $Architect->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y')"), DB::raw('YEAR(created_at)'));
                    $Architect->orderBy(DB::raw('YEAR(created_at)'), 'ASC');
                    if ($isAdminOrCompanyAdmin == 1) {
                        if (isset($req_user_id) && $req_user_id != "") {
                            $Architect->whereIn('sale_person_id', $req_user_id_ex);
                        }
                    } else if ($isSalePerson == 1) {
                        if (isset($req_user_id) && $req_user_id != "") {
                            $Architect->whereIn('sale_person_id', $req_user_id_ex);
                        } else {
                            $Architect->whereIn('sale_person_id', $childSalePersonsIds);
                        }
                    } else if ($isChannelPartner != 0) {
                        $Architect->where('added_by', Auth::user()->id);
                    } 
                    // if ($isSalePerson == 1) {
                    //     $Architect->whereIn('sale_person_id', $childSalePersonsIds);
                    // }
                    $Architect = $Architect->get();
                    $CountArr = $Architect->pluck('count');
                    $MonthArrs = $Architect->pluck('month')->all();
                    $MonthArr = array_map(function ($item) {
                        return $item === null ? 'No date' : $item;
                    }, $MonthArrs);

                    $response = successRes();
                    if(isset($req_channel_partner_user_id) && count($req_channel_partner_user_id_ex) > 0) {
                        $response['MonthArr'] = array();
                        $response['CountArr'] = array();
                    } else {
                        $response['MonthArr'] = $MonthArr;
                        $response['CountArr'] = $CountArr;
                    }
                    $response['title'] = $req_dataType;
                    $response['chart_title'] = 'New Architect';
                    $response['type'] = 1;
                } else if($req_filter_type == "QUARTER") {
                    $Architect = Architect::query();
                    $Architect->selectRaw("DATE_FORMAT(created_at, '%q') as month");
                    $Architect->selectRaw('COUNT(id) as count');
                    $Architect->whereIn('type', [201, 202]);
                    $Architect->whereYear('created_at', date('Y'));
                    $Architect->groupBy(DB::raw("DATE_FORMAT(created_at, '%q')"), DB::raw('QUARTER(created_at)'));
                    $Architect->orderBy(DB::raw('QUARTER(created_at)'), 'ASC');
                    if ($isAdminOrCompanyAdmin == 1) {
                        if (isset($req_user_id) && $req_user_id != "") {
                            $Architect->whereIn('sale_person_id', $req_user_id_ex);
                        }
                    } else if ($isSalePerson == 1) {
                        if (isset($req_user_id) && $req_user_id != "") {
                            $Architect->whereIn('sale_person_id', $req_user_id_ex);
                        } else {
                            $Architect->whereIn('sale_person_id', $childSalePersonsIds);
                        }
                    } else if ($isChannelPartner != 0) {
                        $Architect->where('added_by', Auth::user()->id);
                    } 
                    // if ($isSalePerson == 1) {
                    //     $Architect->whereIn('sale_person_id', $childSalePersonsIds);
                    // }
                    $Architect = $Architect->get();
                    $CountArr = $Architect->pluck('count');
                    $MonthArrs = $Architect->pluck('month')->all();
                    $counter = 1; 
                    $MonthArr = array_map(function ($item) use (&$counter) {
                        if ($item === 'q') {
                            $replacement = $counter;
                            $counter++; 
                            return 'Q'.$replacement;
                        }
                        return $item;
                    }, $MonthArrs);
                    $response = successRes();
                    if(isset($req_channel_partner_user_id) && count($req_channel_partner_user_id_ex) > 0) {
                        $response['MonthArr'] = array();
                        $response['CountArr'] = array();
                    } else {
                        $response['MonthArr'] = $MonthArr;
                        $response['CountArr'] = $CountArr;
                    }
                    
                    $response['title'] = $req_dataType;
                    $response['chart_title'] = 'New Architect';
                    $response['type'] = 1;
                }
            } elseif ($req_dataType == 'ELECTRICIAN') {
                if($req_filter_type == "" || $req_filter_type == "MONTH"){
                    $Electrician = Electrician::query();
                    $Electrician->selectRaw("DATE_FORMAT(created_at, '%b') as month");
                    $Electrician->selectRaw('COUNT(id) as count');
                    $Electrician->whereYear('created_at', date('Y'));
                    $Electrician->whereIn('type', [301, 302]);
                    $Electrician->groupBy(DB::raw("DATE_FORMAT(created_at, '%b')"), DB::raw('MONTH(created_at)'));
                    $Electrician->orderBy(DB::raw('MONTH(created_at)'), 'ASC');
                    if ($isAdminOrCompanyAdmin == 1) {
                        if (isset($req_user_id) && $req_user_id != "") {
                            $Electrician->whereIn('sale_person_id', $req_user_id_ex);
                        }
                    } else if ($isSalePerson == 1) {
                        if (isset($req_user_id) && $req_user_id != "") {
                            $Electrician->whereIn('sale_person_id', $req_user_id_ex);
                        } else {
                            $Electrician->whereIn('sale_person_id', $childSalePersonsIds);
                        }
                    } else if ($isChannelPartner != 0) {
                        $Electrician->where('added_by', Auth::user()->id);
                    } 
                    // if ($isSalePerson == 1) {
                    //     $Electrician->whereIn('sale_person_id', $childSalePersonsIds);
                    // }
                    $Electrician = $Electrician->get();
                    $MonthArr = $Electrician->pluck('month');
                    $CountArr = $Electrician->pluck('count');

                    $response = successRes();
                    if(isset($req_channel_partner_user_id) && count($req_channel_partner_user_id_ex) > 0) {
                        $response['MonthArr'] = array();
                        $response['CountArr'] = array();
                    } else {
                        $response['MonthArr'] = $MonthArr;
                        $response['CountArr'] = $CountArr;
                    }
                    $response['title'] = $req_dataType;
                    $response['chart_title'] = 'New Electrician';
                    $response['type'] = 1;
                } else if($req_filter_type == "YEAR") {
                    $Electrician = Electrician::query();
                    $Electrician->selectRaw("DATE_FORMAT(created_at, '%Y') as month");
                    $Electrician->selectRaw('COUNT(id) as count');
                    $Electrician->whereIn('type', [301, 302]);
                    $Electrician->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y')"), DB::raw('YEAR(created_at)'));
                    $Electrician->orderBy(DB::raw('YEAR(created_at)'), 'ASC');
                    if ($isAdminOrCompanyAdmin == 1) {
                        if (isset($req_user_id) && $req_user_id != "") {
                            $Electrician->whereIn('sale_person_id', $req_user_id_ex);
                        }
                    } else if ($isSalePerson == 1) {
                        if (isset($req_user_id) && $req_user_id != "") {
                            $Electrician->whereIn('sale_person_id', $req_user_id_ex);
                        } else {
                            $Electrician->whereIn('sale_person_id', $childSalePersonsIds);
                        }
                    } else if ($isChannelPartner != 0) {
                        $Electrician->where('added_by', Auth::user()->id);
                    } 

                    // if ($isSalePerson == 1) {
                    //     $Electrician->whereIn('sale_person_id', $childSalePersonsIds);
                    // }
                    $Electrician = $Electrician->get();
                    $CountArr = $Electrician->pluck('count');
                    $MonthArrs = $Electrician->pluck('month')->all();
                    $MonthArr = array_map(function ($item) {
                        return $item === null ? 'No date' : $item;
                    }, $MonthArrs);

                    $response = successRes();
                    if(isset($req_channel_partner_user_id) && count($req_channel_partner_user_id_ex) > 0) {
                        $response['MonthArr'] = array();
                        $response['CountArr'] = array();
                    } else {
                        $response['MonthArr'] = $MonthArr;
                        $response['CountArr'] = $CountArr;
                    }
                    $response['title'] = $req_dataType;
                    $response['chart_title'] = 'New Electrician';
                    $response['type'] = 1;

                } else if($req_filter_type == "QUARTER") {
                    $Electrician = Electrician::query();
                    $Electrician->selectRaw("DATE_FORMAT(created_at, '%q') as month");
                    $Electrician->selectRaw('COUNT(id) as count');
                    $Electrician->whereIn('type', [301, 302]);
                    $Electrician->groupBy(DB::raw("DATE_FORMAT(created_at, '%q')"), DB::raw('QUARTER(created_at)'));
                    $Electrician->orderBy(DB::raw('QUARTER(created_at)'), 'ASC');

                    if ($isAdminOrCompanyAdmin == 1) {
                        if (isset($req_user_id) && $req_user_id != "") {
                            $Electrician->whereIn('sale_person_id', $req_user_id_ex);
                        }
                    } else if ($isSalePerson == 1) {
                        if (isset($req_user_id) && $req_user_id != "") {
                            $Electrician->whereIn('sale_person_id', $req_user_id_ex);
                        } else {
                            $Electrician->whereIn('sale_person_id', $childSalePersonsIds);
                        }
                    } else if ($isChannelPartner != 0) {
                        $Electrician->where('added_by', Auth::user()->id);
                    } 

                    // if ($isSalePerson == 1) {
                    //     $Electrician->whereIn('sale_person_id', $childSalePersonsIds);
                    // }
                    $Electrician = $Electrician->get();
                    $CountArr = $Electrician->pluck('count');
                    $MonthArrs = $Electrician->pluck('month')->all();
                    $counter = 1; 
                    $MonthArr = array_map(function ($item) use (&$counter) {
                        if ($item === 'q') {
                            $replacement = $counter;
                            $counter++; 
                            return 'Q'.$replacement;
                        }
                        return $item;
                    }, $MonthArrs);

                    $response = successRes();
                    if(isset($req_channel_partner_user_id) && count($req_channel_partner_user_id_ex) > 0) {
                        $response['MonthArr'] = array();
                        $response['CountArr'] = array();
                    } else {
                        $response['MonthArr'] = $MonthArr;
                        $response['CountArr'] = $CountArr;
                    }
                    $response['title'] = $req_dataType;
                    $response['chart_title'] = 'New Electrician';
                    $response['type'] = 1;
                }
            } elseif ($req_dataType == 'PRIDICTION') {
                if($req_filter_type == "" || $req_filter_type == "MONTH"){
                    $PridictionDeal = Lead::query();
                    $PridictionDeal->selectRaw("DATE_FORMAT(leads.closing_date_time, '%b') as month");
                    $PridictionDeal->selectRaw('COUNT(leads.id) as deal_count');
                    $PridictionDeal->whereYear('leads.closing_date_time', date('Y'));
                    $PridictionDeal->where('leads.closing_date_time','<>', 0);
                    $PridictionDeal->where('leads.is_deal', 1);
			        $PridictionDeal->whereIn('leads.status', array(100,101,102));
                    $PridictionDeal->groupBy(DB::raw("DATE_FORMAT(leads.closing_date_time, '%b')"), DB::raw('MONTH(leads.closing_date_time)'));
                    $PridictionDeal->orderBy(DB::raw('MONTH(leads.closing_date_time)'), 'ASC');
                    if ($isAdminOrCompanyAdmin == 1) {
                        if (isset($req_user_id) && $req_user_id != "") {
                            $PridictionDeal->whereIn('leads.assigned_to', $req_user_id_ex);
                        }
                    } else if ($isSalePerson == 1) {
                        if (isset($req_user_id) && $req_user_id != "") {
                            $PridictionDeal->whereIn('leads.assigned_to', $req_user_id_ex);
                        } else {
                            $PridictionDeal->whereIn('leads.assigned_to', $childSalePersonsIds);
                        }
                    } elseif($isChannelPartner != 0){
                        $PridictionDeal->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $PridictionDeal->where('lead_sources.source', Auth::user()->id);
                    }

                    if (isset($req_channel_partner_user_id) && is_array($req_channel_partner_user_id_ex)) {
                        $PridictionDeal->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $PridictionDeal->whereIn('lead_sources.source', $req_channel_partner_user_id_ex);
                    }
                    // if ($isSalePerson == 1) {
                    //     $PridictionDeal->whereIn('leads.assigned_to', $childSalePersonsIds);
                    // }
                    $PridictionDeal = $PridictionDeal->get();
                    $MonthArr = $PridictionDeal->pluck('month');
                    $PridictionDealArr = $PridictionDeal->pluck('deal_count');
                    $response = successRes();
                    $response['MonthArr'] = $MonthArr;
                    $response['CountArr'] = $PridictionDealArr;
                    $response['title'] = $req_dataType;
                    $response['chart_title'] = "Pridiction Deal's";
                    $response['type'] = 1;
                } else if($req_filter_type == "YEAR") {
                    $PridictionDeal = Lead::query();
                    $PridictionDeal->selectRaw("DATE_FORMAT(leads.closing_date_time, '%Y') as month");
                    $PridictionDeal->selectRaw('COUNT(leads.id) as deal_count');
                    $PridictionDeal->where('leads.is_deal', '=', 1);
			        $PridictionDeal->whereIn('leads.status', array(100,101,102));
                    $PridictionDeal->where('leads.closing_date_time','<>', 0);
                    $PridictionDeal->groupBy(DB::raw("DATE_FORMAT(leads.closing_date_time, '%Y')"), DB::raw('YEAR(leads.closing_date_time)'));
                    $PridictionDeal->orderBy(DB::raw('YEAR(leads.closing_date_time)'), 'ASC');
                    if ($isAdminOrCompanyAdmin == 1) {
                        if (isset($req_user_id) && $req_user_id != "") {
                            $PridictionDeal->whereIn('leads.assigned_to', $req_user_id_ex);
                        }
                    } else if ($isSalePerson == 1) {
                        if (isset($req_user_id) && $req_user_id != "") {
                            $PridictionDeal->whereIn('leads.assigned_to', $req_user_id_ex);
                        } else {
                            $PridictionDeal->whereIn('leads.assigned_to', $childSalePersonsIds);
                        }
                    } elseif($isChannelPartner != 0){
                        $PridictionDeal->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $PridictionDeal->where('lead_sources.source', Auth::user()->id);
                    }
                    if (isset($req_channel_partner_user_id) && is_array($req_channel_partner_user_id_ex)) {
                        $PridictionDeal->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $PridictionDeal->whereIn('lead_sources.source', $req_channel_partner_user_id_ex);
                    }
                    // if ($isSalePerson == 1) {
                    //     $PridictionDeal->whereIn('leads.assigned_to', $childSalePersonsIds);
                    // }
                    $PridictionDeal = $PridictionDeal->get();
                    $PridictionDealArr = $PridictionDeal->pluck('deal_count');
                    $MonthArrs = $PridictionDeal->pluck('month')->all();
                    $MonthArr = array_map(function ($item) {
                        return $item === null ? 'No date' : $item;
                    }, $MonthArrs);

                    $response = successRes();
                    $response['MonthArr'] = $MonthArr;
                    $response['CountArr'] = $PridictionDealArr;
                    $response['title'] = $req_dataType;
                    $response['chart_title'] = "Pridiction Deal's";
                    $response['type'] = 1;
                }else if($req_filter_type == "QUARTER") {
                    $PridictionDeal = Lead::query();
                    $PridictionDeal->selectRaw("DATE_FORMAT(leads.closing_date_time, '%q') as month");
                    $PridictionDeal->selectRaw('COUNT(leads.id) as deal_count');
                    $PridictionDeal->where('leads.is_deal', '=', 1);
			        $PridictionDeal->whereIn('leads.status', array(100,101,102));
                    $PridictionDeal->where('leads.closing_date_time','<>', 0);
                    $PridictionDeal->groupBy(DB::raw("DATE_FORMAT(leads.closing_date_time, '%q')"), DB::raw('QUARTER(leads.closing_date_time)'));
                    $PridictionDeal->orderBy(DB::raw('QUARTER(leads.closing_date_time)'), 'ASC');
                    if ($isAdminOrCompanyAdmin == 1) {
                        if (isset($req_user_id) && $req_user_id != "") {
                            $PridictionDeal->whereIn('leads.assigned_to', $req_user_id_ex);
                        }
                    } else if ($isSalePerson == 1) {
                        if (isset($req_user_id) && $req_user_id != "") {
                            $PridictionDeal->whereIn('leads.assigned_to', $req_user_id_ex);
                        } else {
                            $PridictionDeal->whereIn('leads.assigned_to', $childSalePersonsIds);
                        }
                    } elseif($isChannelPartner != 0){
                        $PridictionDeal->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $PridictionDeal->where('lead_sources.source', Auth::user()->id);
                    }

                    if (isset($req_channel_partner_user_id) && is_array($req_channel_partner_user_id_ex)) {
                        $PridictionDeal->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $PridictionDeal->whereIn('lead_sources.source', $req_channel_partner_user_id_ex);
                    }
                    // if ($isSalePerson == 1) {
                    //     $PridictionDeal->whereIn('leads.assigned_to', $childSalePersonsIds);
                    // }
                    $PridictionDeal = $PridictionDeal->get();
                    $PridictionDealArr = $PridictionDeal->pluck('deal_count');
                    $MonthArrs = $PridictionDeal->pluck('month')->all();
                    $counter = 1; 
                    $MonthArr = array_map(function ($item) use (&$counter) {
                        if ($item === 'q') {
                            $replacement = $counter;
                            $counter++; 
                            return 'Q'.$replacement;
                        }
                        return $item;
                    }, $MonthArrs);

                    $response = successRes();
                    $response['MonthArr'] = $MonthArr;
                    $response['CountArr'] = $PridictionDealArr;
                    $response['title'] = $req_dataType;
                    $response['chart_title'] = "Pridiction Deal's";
                    $response['type'] = 1;
                }
            } elseif ($req_dataType == 'LEAD') {
                if($req_filter_type == "" || $req_filter_type == "MONTH"){
                    $LeadCount = Lead::query();
                    $LeadCount->selectRaw("DATE_FORMAT(leads.created_at, '%b') as month");
                    $LeadCount->selectRaw("SUM(CASE WHEN is_deal = 1 THEN 1 ELSE 0 END) as deal_count");
                    $LeadCount->selectRaw('SUM(CASE WHEN is_deal = 0 THEN 1 ELSE 0 END) as lead_count');
                    $LeadCount->whereYear('leads.created_at', date('Y'));
                    $LeadCount->groupBy(DB::raw("DATE_FORMAT(leads.created_at, '%b')"), DB::raw('MONTH(leads.created_at)'));
                    $LeadCount->orderBy(DB::raw('MONTH(leads.created_at)'), 'ASC');
                    if ($isAdminOrCompanyAdmin == 1) {
                        if (isset($req_user_id) && $req_user_id != "") {
                            $LeadCount->whereIn('leads.assigned_to', $req_user_id_ex);
                        }
                    } else if ($isSalePerson == 1) {
                        if (isset($req_user_id) && $req_user_id != "") {
                            $LeadCount->whereIn('leads.assigned_to', $req_user_id_ex);
                        } else {
                            $LeadCount->whereIn('leads.assigned_to', $childSalePersonsIds);
                        }
                    } elseif($isChannelPartner != 0){
                        $LeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $LeadCount->where('lead_sources.source', Auth::user()->id);
                    }
                    if (isset($req_channel_partner_user_id) && is_array($req_channel_partner_user_id_ex)) {
                        $LeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $LeadCount->whereIn('lead_sources.source', $req_channel_partner_user_id_ex);
                    }
                    // if ($isSalePerson == 1) {
                    //     $LeadCount->whereIn('leads.assigned_to', $childSalePersonsIds);
                    // }
                    $LeadCount = $LeadCount->get();
                    $MonthArr = $LeadCount->pluck('month');
                    $LeadCountArr = $LeadCount->pluck('lead_count');
                    $DealCountArr = $LeadCount->pluck('deal_count');
                } else if($req_filter_type == "YEAR") { 
                    $LeadCount = Lead::query();
                    $LeadCount->selectRaw("DATE_FORMAT(leads.created_at, '%Y') as month");
                    $LeadCount->selectRaw("SUM(CASE WHEN is_deal = 1 THEN 1 ELSE 0 END) as deal_count");
                    $LeadCount->selectRaw('SUM(CASE WHEN is_deal = 0 THEN 1 ELSE 0 END) as lead_count');
                    $LeadCount->groupBy(DB::raw("DATE_FORMAT(leads.created_at, '%Y')"), DB::raw('YEAR(leads.created_at)'));
                    $LeadCount->orderBy(DB::raw('YEAR(leads.created_at)'), 'ASC');
                    if ($isAdminOrCompanyAdmin == 1) {
                        if (isset($req_user_id) && $req_user_id != "") {
                            $LeadCount->whereIn('leads.assigned_to', $req_user_id_ex);
                        }
                    } else if ($isSalePerson == 1) {
                        if (isset($req_user_id) && $req_user_id != "") {
                            $LeadCount->whereIn('leads.assigned_to', $req_user_id_ex);
                        } else {
                            $LeadCount->whereIn('leads.assigned_to', $childSalePersonsIds);
                        }
                    } elseif($isChannelPartner != 0){
                        $LeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $LeadCount->where('lead_sources.source', Auth::user()->id);
                    }
                    if (isset($req_channel_partner_user_id) && is_array($req_channel_partner_user_id_ex)) {
                        $LeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $LeadCount->whereIn('lead_sources.source', $req_channel_partner_user_id_ex);
                    }
                    // if ($isSalePerson == 1) {
                    //     $LeadCount->whereIn('leads.assigned_to', $childSalePersonsIds);
                    // }
                    $LeadCount = $LeadCount->get();
                    $MonthArrs = $LeadCount->pluck('month')->all();
                    $LeadCountArr = $LeadCount->pluck('lead_count');
                    $DealCountArr = $LeadCount->pluck('deal_count');
                    $MonthArr = array_map(function ($item) {
                        return $item === null ? 'No date' : $item;
                    }, $MonthArrs);
                } else if($req_filter_type == "QUARTER") {
                    $LeadCount = Lead::query();
                    $LeadCount->selectRaw("DATE_FORMAT(leads.created_at, '%q') as month");
                    $LeadCount->selectRaw("SUM(CASE WHEN is_deal = 1 THEN 1 ELSE 0 END) as deal_count");
                    $LeadCount->selectRaw('SUM(CASE WHEN is_deal = 0 THEN 1 ELSE 0 END) as lead_count');
                    $LeadCount->whereYear('leads.created_at', date('Y'));
                    $LeadCount->groupBy(DB::raw("DATE_FORMAT(leads.created_at, '%q')"), DB::raw('QUARTER(leads.created_at)'));
                    $LeadCount->orderBy(DB::raw('QUARTER(leads.created_at)'), 'ASC');
                    if ($isAdminOrCompanyAdmin == 1) {
                        if (isset($req_user_id) && $req_user_id != "") {
                            $LeadCount->whereIn('leads.assigned_to', $req_user_id_ex);
                        }
                    } else if ($isSalePerson == 1) {
                        if (isset($req_user_id) && $req_user_id != "") {
                            $LeadCount->whereIn('leads.assigned_to', $req_user_id_ex);
                        } else {
                            $LeadCount->whereIn('leads.assigned_to', $childSalePersonsIds);
                        }
                    } elseif($isChannelPartner != 0){
                        $LeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $LeadCount->where('lead_sources.source', Auth::user()->id);
                    }
                    if (isset($req_channel_partner_user_id) && is_array($req_channel_partner_user_id_ex)) {
                        $LeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $LeadCount->whereIn('lead_sources.source', $req_channel_partner_user_id_ex);
                    }
                    // if ($isSalePerson == 1) {
                    //     $LeadCount->whereIn('leads.assigned_to', $childSalePersonsIds);
                    // }
                    $LeadCount = $LeadCount->get();
                    $MonthArrs = $LeadCount->pluck('month')->all();
                    $LeadCountArr = $LeadCount->pluck('lead_count');
                    $DealCountArr = $LeadCount->pluck('deal_count');
                    $counter = 1; 
                    $MonthArr = array_map(function ($item) use (&$counter) {
                        if ($item === 'q') {
                            $replacement = $counter;
                            $counter++; 
                            return 'Q'.$replacement;
                        }
                        return $item;
                    }, $MonthArrs);
                }

                $arrCount = [];
                $arrLeadCount = [];
                $arrLeadCount['name'] = 'Lead';
                $arrLeadCount['data'] = $LeadCountArr;
                array_push($arrCount, $arrLeadCount);

                $arrDealCount = [];
                $arrDealCount['name'] = 'Deal';
                $arrDealCount['data'] = $DealCountArr;
                array_push($arrCount, $arrDealCount);

                $response = successRes();
                $response['MonthArr'] = $MonthArr;
                $response['CountArr'] = $arrCount;
                $response['title'] = $req_dataType;
                $response['chart_title'] = "Lead's";

                $response['type'] = 2;
            } elseif ($req_dataType == 'DEALCONVERSION') {
                if($req_filter_type == "" || $req_filter_type == "MONTH"){ 
                    $LeadCount = LeadTimeline::query();
                    $LeadCount->selectRaw("DATE_FORMAT(lead_timeline.created_at, '%b') as month");
                    $LeadCount->selectRaw('COUNT(lead_timeline.lead_id) as lead_count');
                    $LeadCount->leftJoin('leads', 'leads.id', '=', 'lead_timeline.lead_id');
                    $LeadCount->where('lead_timeline.type', '=', 'convert-to-deal');
                    $LeadCount->whereYear('lead_timeline.created_at', date('Y'));
                    $LeadCount->groupBy(DB::raw("DATE_FORMAT(lead_timeline.created_at, '%b')"), DB::raw('MONTH(lead_timeline.created_at)'));
                    $LeadCount->orderBy(DB::raw('MONTH(lead_timeline.created_at)'), 'ASC');
                    if ($isAdminOrCompanyAdmin == 1) {
                        if (isset($req_user_id) && $req_user_id != "") {
                            $LeadCount->whereIn('leads.assigned_to', $req_user_id_ex);
                        }
                    } else if ($isSalePerson == 1) {
                        if (isset($req_user_id) && $req_user_id != "") {
                            $LeadCount->whereIn('leads.assigned_to', $req_user_id_ex);
                        } else {
                            $LeadCount->whereIn('leads.assigned_to', $childSalePersonsIds);
                        }
                    } else if($isChannelPartner != 0){
                        $LeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $LeadCount->where('lead_sources.source', Auth::user()->id);
                    }

                    if (isset($req_channel_partner_user_id) && is_array($req_channel_partner_user_id_ex)) {
                        $LeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $LeadCount->whereIn('lead_sources.source', $req_channel_partner_user_id_ex);
                    }
                    // if ($isSalePerson == 1) {
                    //     $LeadCount->whereIn('leads.assigned_to', $childSalePersonsIds);
                    // }
                    $LeadCount = $LeadCount->get();
                    $MonthArr = $LeadCount->pluck('month');
                    $LeadCountArr = $LeadCount->pluck('lead_count');
                } else if($req_filter_type == "YEAR") { 
                    $LeadCount = LeadTimeline::query();
                    $LeadCount->selectRaw("DATE_FORMAT(lead_timeline.created_at, '%Y') as month");
                    $LeadCount->selectRaw('COUNT(lead_timeline.lead_id) as lead_count');
                    $LeadCount->leftJoin('leads', 'leads.id', '=', 'lead_timeline.lead_id');
                    $LeadCount->where('lead_timeline.type', '=', 'convert-to-deal');
                    $LeadCount->groupBy(DB::raw("DATE_FORMAT(lead_timeline.created_at, '%Y')"), DB::raw('YEAR(lead_timeline.created_at)'));
                    $LeadCount->orderBy(DB::raw('YEAR(lead_timeline.created_at)'), 'ASC');

                    if ($isAdminOrCompanyAdmin == 1) {
                        if (isset($req_user_id) && $req_user_id != "") {
                            $LeadCount->whereIn('leads.assigned_to', $req_user_id_ex);
                        }
                    } else if ($isSalePerson == 1) {
                        if (isset($req_user_id) && $req_user_id != "") {
                            $LeadCount->whereIn('leads.assigned_to', $req_user_id_ex);
                        } else {
                            $LeadCount->whereIn('leads.assigned_to', $childSalePersonsIds);
                        }
                    } else if($isChannelPartner != 0){
                        $LeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $LeadCount->where('lead_sources.source', Auth::user()->id);
                    }

                    if (isset($req_channel_partner_user_id) && is_array($req_channel_partner_user_id_ex)) {
                        $LeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $LeadCount->whereIn('lead_sources.source', $req_channel_partner_user_id_ex);
                    }

                    // if ($isSalePerson == 1) {
                    //     $LeadCount->whereIn('leads.assigned_to', $childSalePersonsIds);
                    // }
                    $LeadCount = $LeadCount->get();
                    $MonthArrs = $LeadCount->pluck('month')->all();
                    $LeadCountArr = $LeadCount->pluck('lead_count');

                    $MonthArr = array_map(function ($item) {
                        return $item === null ? 'No date' : $item;
                    }, $MonthArrs);

                } else if($req_filter_type == "QUARTER") { 
                    $LeadCount = LeadTimeline::query();
                    $LeadCount->selectRaw("DATE_FORMAT(lead_timeline.created_at, '%q') as month");
                    $LeadCount->selectRaw('COUNT(lead_timeline.lead_id) as lead_count');
                    $LeadCount->leftJoin('leads', 'leads.id', '=', 'lead_timeline.lead_id');
                    $LeadCount->where('lead_timeline.type', '=', 'convert-to-deal');
                    $LeadCount->whereYear('lead_timeline.created_at', date('Y'));
                    $LeadCount->groupBy(DB::raw("DATE_FORMAT(lead_timeline.created_at, '%q')"), DB::raw('QUARTER(lead_timeline.created_at)'));
                    $LeadCount->orderBy(DB::raw('QUARTER(lead_timeline.created_at)'), 'ASC');
                    if ($isAdminOrCompanyAdmin == 1) {
                        if (isset($req_user_id) && $req_user_id != "") {
                            $LeadCount->whereIn('leads.assigned_to', $req_user_id_ex);
                        }
                    } else if ($isSalePerson == 1) {
                        if (isset($req_user_id) && $req_user_id != "") {
                            $LeadCount->whereIn('leads.assigned_to', $req_user_id_ex);
                        } else {
                            $LeadCount->whereIn('leads.assigned_to', $childSalePersonsIds);
                        }
                    } else if($isChannelPartner != 0){
                        $LeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $LeadCount->where('lead_sources.source', Auth::user()->id);
                    }

                    if (isset($req_channel_partner_user_id) && is_array($req_channel_partner_user_id_ex)) {
                        $LeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $LeadCount->whereIn('lead_sources.source', $req_channel_partner_user_id_ex);
                    }

                    // if ($isSalePerson == 1) {
                    //     $LeadCount->whereIn('leads.assigned_to', $childSalePersonsIds);
                    // }
                    $LeadCount = $LeadCount->get();
                    $MonthArrs = $LeadCount->pluck('month')->all();
                    $LeadCountArr = $LeadCount->pluck('lead_count');
                    $counter = 1; 
                    $MonthArr = array_map(function ($item) use (&$counter) {
                        if ($item === 'q') {
                            $replacement = $counter;
                            $counter++; 
                            return 'Q'.$replacement;
                        }
                        return $item;
                    }, $MonthArrs);
                }

                $response = successRes();
                $response['MonthArr'] = $MonthArr;

                $arrCount = [];
                $arrLeadCount = [];
                $arrLeadCount['name'] = 'Lead';
                $arrLeadCount['data'] = $LeadCountArr;
                array_push($arrCount, $arrLeadCount);

                

                $response['CountArr'] = $LeadCountArr;
                $response['title'] = $req_dataType;
                $response['chart_title'] = "Deal Conversion's";
                $response['type'] = 1;
            } elseif ($req_dataType == 'RUNNING') {
                if($req_filter_type == "" || $req_filter_type == "MONTH"){ 
                    $LeadCount = Lead::query();
                    $LeadCount->selectRaw("DATE_FORMAT(leads.created_at, '%b') as month");
                    $LeadCount->selectRaw('SUM(CASE WHEN is_deal = 1 THEN 1 ELSE 0 END) as deal_count');
                    $LeadCount->selectRaw('SUM(CASE WHEN is_deal = 0 THEN 1 ELSE 0 END) as lead_count');
                    $LeadCount->whereYear('leads.created_at', date('Y'));
                    $LeadCount->whereIn('status', [2, 3, 4, 100, 101, 102]);
                    $LeadCount->groupBy(DB::raw("DATE_FORMAT(leads.created_at, '%b')"), DB::raw('MONTH(leads.created_at)'));
                    $LeadCount->orderBy(DB::raw('MONTH(leads.created_at)'), 'ASC');
                    if ($isAdminOrCompanyAdmin == 1) {
                        if (isset($req_user_id) && $req_user_id != "") {
                            $LeadCount->whereIn('leads.assigned_to', $req_user_id_ex);
                        }
                    } else if ($isSalePerson == 1) {
                        if (isset($req_user_id) && $req_user_id != "") {
                            $LeadCount->whereIn('leads.assigned_to', $req_user_id_ex);
                        } else {
                            $LeadCount->whereIn('leads.assigned_to', $childSalePersonsIds);
                        }
                    } else if($isChannelPartner != 0){
                        $LeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $LeadCount->where('lead_sources.source', Auth::user()->id);
                    }

                    if (isset($req_channel_partner_user_id) && is_array($req_channel_partner_user_id_ex)) {
                        $LeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $LeadCount->whereIn('lead_sources.source', $req_channel_partner_user_id_ex);
                    }
                    // if ($isSalePerson == 1) {
                    //     $LeadCount->whereIn('leads.assigned_to', $childSalePersonsIds);
                    // }
                    $LeadCount = $LeadCount->get();
                    $MonthArr = $LeadCount->pluck('month');
                    $LeadCountArr = $LeadCount->pluck('lead_count');
                    $DealCountArr = $LeadCount->pluck('deal_count');
                } else if($req_filter_type == "YEAR") { 
                    $LeadCount = Lead::query();
                    $LeadCount->selectRaw("DATE_FORMAT(leads.created_at, '%Y') as month");
                    $LeadCount->selectRaw('SUM(CASE WHEN is_deal = 1 THEN 1 ELSE 0 END) as deal_count');
                    $LeadCount->selectRaw('SUM(CASE WHEN is_deal = 0 THEN 1 ELSE 0 END) as lead_count');
                    $LeadCount->whereIn('status', [2, 3, 4, 100, 101, 102]);
                    $LeadCount->groupBy(DB::raw("DATE_FORMAT(leads.created_at, '%Y')"), DB::raw('YEAR(leads.created_at)'));
                    $LeadCount->orderBy(DB::raw('YEAR(leads.created_at)'), 'ASC');
                    if ($isAdminOrCompanyAdmin == 1) {
                        if (isset($req_user_id) && $req_user_id != "") {
                            $LeadCount->whereIn('leads.assigned_to', $req_user_id_ex);
                        }
                    } else if ($isSalePerson == 1) {
                        if (isset($req_user_id) && $req_user_id != "") {
                            $LeadCount->whereIn('leads.assigned_to', $req_user_id_ex);
                        } else {
                            $LeadCount->whereIn('leads.assigned_to', $childSalePersonsIds);
                        }
                    } else if($isChannelPartner != 0){
                        $LeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $LeadCount->where('lead_sources.source', Auth::user()->id);
                    }

                    if (isset($req_channel_partner_user_id) && is_array($req_channel_partner_user_id_ex)) {
                        $LeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $LeadCount->whereIn('lead_sources.source', $req_channel_partner_user_id_ex);
                    }
                    // if ($isSalePerson == 1) {
                    //     $LeadCount->whereIn('leads.assigned_to', $childSalePersonsIds);
                    // }
                    $LeadCount = $LeadCount->get();
                    $MonthArrs = $LeadCount->pluck('month')->all();
                    $LeadCountArr = $LeadCount->pluck('lead_count');
                    $DealCountArr = $LeadCount->pluck('deal_count');

                    $MonthArr = array_map(function ($item) {
                        return $item === null ? 'No date' : $item;
                    }, $MonthArrs);
                } else if($req_filter_type == "QUARTER") { 
                    $LeadCount = Lead::query();
                    $LeadCount->selectRaw("DATE_FORMAT(leads.created_at, '%q') as month");
                    $LeadCount->selectRaw('SUM(CASE WHEN is_deal = 1 THEN 1 ELSE 0 END) as deal_count');
                    $LeadCount->selectRaw('SUM(CASE WHEN is_deal = 0 THEN 1 ELSE 0 END) as lead_count');
                    $LeadCount->whereYear('leads.created_at', date('Y'));
                    $LeadCount->whereIn('status', [2, 3, 4, 100, 101, 102]);
                    $LeadCount->groupBy(DB::raw("DATE_FORMAT(leads.created_at, '%q')"), DB::raw('QUARTER(leads.created_at)'));
                    $LeadCount->orderBy(DB::raw('QUARTER(leads.created_at)'), 'ASC');
                    if ($isAdminOrCompanyAdmin == 1) {
                        if (isset($req_user_id) && $req_user_id != "") {
                            $LeadCount->whereIn('leads.assigned_to', $req_user_id_ex);
                        }
                    } else if ($isSalePerson == 1) {
                        if (isset($req_user_id) && $req_user_id != "") {
                            $LeadCount->whereIn('leads.assigned_to', $req_user_id_ex);
                        } else {
                            $LeadCount->whereIn('leads.assigned_to', $childSalePersonsIds);
                        }
                    } else if($isChannelPartner != 0){
                        $LeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $LeadCount->where('lead_sources.source', Auth::user()->id);
                    }

                    if (isset($req_channel_partner_user_id) && is_array($req_channel_partner_user_id_ex)) {
                        $LeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $LeadCount->whereIn('lead_sources.source', $req_channel_partner_user_id_ex);
                    }
                    // if ($isSalePerson == 1) {
                    //     $LeadCount->whereIn('leads.assigned_to', $childSalePersonsIds);
                    // }
                    $LeadCount = $LeadCount->get();
                    $MonthArrs = $LeadCount->pluck('month')->all();
                    $LeadCountArr = $LeadCount->pluck('lead_count');
                    $DealCountArr = $LeadCount->pluck('deal_count');
                    $counter = 1; 
                    $MonthArr = array_map(function ($item) use (&$counter) {
                        if ($item === 'q') {
                            $replacement = $counter;
                            $counter++; 
                            return 'Q'.$replacement;
                        }
                        return $item;
                    }, $MonthArrs);
                }

                $response = successRes();
                $response['MonthArr'] = $MonthArr;

                $arrCount = [];
                $arrLeadCount = [];
                $arrLeadCount['name'] = 'Lead';
                $arrLeadCount['data'] = $LeadCountArr;
                array_push($arrCount, $arrLeadCount);

                $arrDealCount = [];
                $arrDealCount['name'] = 'Deal';
                $arrDealCount['data'] = $DealCountArr;
                array_push($arrCount, $arrDealCount);

                $response['CountArr'] = $arrCount;
                $response['title'] = $req_dataType;
                $response['chart_title'] = "Running Lead's & Deal's";
                $response['type'] = 2;
            } elseif ($req_dataType == 'WON') {

                if($req_filter_type == "" || $req_filter_type == "MONTH"){ 
                    $LeadCount = Lead::query();
                    $LeadCount->selectRaw("DATE_FORMAT(lead_status_detail.created_at, '%b') as month");
                    $LeadCount->selectRaw('SUM(CASE WHEN leads.is_deal = 1 THEN 1 ELSE 0 END) as deal_count');
                    $LeadCount->selectRaw('SUM(CASE WHEN leads.is_deal = 0 THEN 1 ELSE 0 END) as lead_count');
                    $LeadCount->whereYear('lead_status_detail.created_at', date('Y'));
                    $LeadCount->leftJoin('lead_status_updates as lead_status_detail', function ($join) {
                        $join->select('lead_status_detail.new_status');
                        $join->on('lead_status_detail.lead_id', '=', 'leads.id');
                        $join->where('lead_status_detail.new_status', 103);
                        $join->orderBy('lead_status_detail.created_at', 'DESC');
                        $join->limit(1);
                    });
                    $LeadCount->groupBy(DB::raw("DATE_FORMAT(lead_status_detail.created_at, '%b')"), DB::raw('MONTH(lead_status_detail.created_at)'));
                    $LeadCount->orderBy(DB::raw('MONTH(lead_status_detail.created_at)'), 'ASC');
                    if ($isAdminOrCompanyAdmin == 1) {
                        if (isset($req_user_id) && $req_user_id != "") {
                            $LeadCount->whereIn('leads.assigned_to', $req_user_id_ex);
                        }
                    } else if ($isSalePerson == 1) {
                        if (isset($req_user_id) && $req_user_id != "") {
                            $LeadCount->whereIn('leads.assigned_to', $req_user_id_ex);
                        } else {
                            $LeadCount->whereIn('leads.assigned_to', $childSalePersonsIds);
                        }
                    } else if($isChannelPartner != 0){
                        $LeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $LeadCount->where('lead_sources.source', Auth::user()->id);
                    }

                    if (isset($req_channel_partner_user_id) && is_array($req_channel_partner_user_id_ex)) {
                        $LeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $LeadCount->whereIn('lead_sources.source', $req_channel_partner_user_id_ex);
                    }
                    // if ($isSalePerson == 1) {
                    //     $LeadCount->whereIn('leads.assigned_to', $childSalePersonsIds);
                    // }
                    $LeadCount = $LeadCount->get();
                    $MonthArr = $LeadCount->pluck('month');
                    $LeadCountArr = $LeadCount->pluck('lead_count');
                    $DealCountArr = $LeadCount->pluck('deal_count');
                } else if($req_filter_type == "YEAR") { 
                    $LeadCount = Lead::query();
                    $LeadCount->selectRaw("DATE_FORMAT(lead_status_detail.created_at, '%Y') as month");
                    $LeadCount->selectRaw('SUM(CASE WHEN leads.is_deal = 1 THEN 1 ELSE 0 END) as deal_count');
                    $LeadCount->selectRaw('SUM(CASE WHEN leads.is_deal = 0 THEN 1 ELSE 0 END) as lead_count');
                    $LeadCount->leftJoin('lead_status_updates as lead_status_detail', function ($join) {
                        $join->select('lead_status_detail.new_status');
                        $join->on('lead_status_detail.lead_id', '=', 'leads.id');
                        $join->where('lead_status_detail.new_status', 103);
                        $join->orderBy('lead_status_detail.created_at', 'DESC');
                        $join->limit(1);
                    });
                    $LeadCount->groupBy(DB::raw("DATE_FORMAT(lead_status_detail.created_at, '%Y')"), DB::raw('YEAR(lead_status_detail.created_at)'));
                    $LeadCount->orderBy(DB::raw('YEAR(lead_status_detail.created_at)'), 'ASC');
                    if ($isAdminOrCompanyAdmin == 1) {
                        if (isset($req_user_id) && $req_user_id != "") {
                            $LeadCount->whereIn('leads.assigned_to', $req_user_id_ex);
                        }
                    } else if ($isSalePerson == 1) {
                        if (isset($req_user_id) && $req_user_id != "") {
                            $LeadCount->whereIn('leads.assigned_to', $req_user_id_ex);
                        } else {
                            $LeadCount->whereIn('leads.assigned_to', $childSalePersonsIds);
                        }
                    } else if($isChannelPartner != 0){
                        $LeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $LeadCount->where('lead_sources.source', Auth::user()->id);
                    }

                    if (isset($req_channel_partner_user_id) && is_array($req_channel_partner_user_id_ex)) {
                        $LeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $LeadCount->whereIn('lead_sources.source', $req_channel_partner_user_id_ex);
                    }
                    // if ($isSalePerson == 1) {
                    //     $LeadCount->whereIn('leads.assigned_to', $childSalePersonsIds);
                    // }
                    $LeadCount = $LeadCount->get();
                    $MonthArrs = $LeadCount->pluck('month')->all();
                    $LeadCountArr = $LeadCount->pluck('lead_count');
                    $DealCountArr = $LeadCount->pluck('deal_count');

                    $MonthArr = array_map(function ($item) {
                        return $item === null ? 'No date' : $item;
                    }, $MonthArrs);
                } else if($req_filter_type == "QUARTER") { 
                    $LeadCount = Lead::query();
                    $LeadCount->selectRaw("DATE_FORMAT(lead_status_detail.created_at, '%q') as month");
                    $LeadCount->selectRaw('SUM(CASE WHEN leads.is_deal = 1 THEN 1 ELSE 0 END) as deal_count');
                    $LeadCount->selectRaw('SUM(CASE WHEN leads.is_deal = 0 THEN 1 ELSE 0 END) as lead_count');
                    $LeadCount->whereYear('lead_status_detail.created_at', date('Y'));
                    $LeadCount->leftJoin('lead_status_updates as lead_status_detail', function ($join) {
                        $join->select('lead_status_detail.new_status');
                        $join->on('lead_status_detail.lead_id', '=', 'leads.id');
                        $join->where('lead_status_detail.new_status', 103);
                        $join->orderBy('lead_status_detail.created_at', 'DESC');
                        $join->limit(1);
                    });
                    $LeadCount->groupBy(DB::raw("DATE_FORMAT(lead_status_detail.created_at, '%q')"), DB::raw('QUARTER(lead_status_detail.created_at)'));
                    $LeadCount->orderBy(DB::raw('QUARTER(lead_status_detail.created_at)'), 'ASC');
                    if ($isAdminOrCompanyAdmin == 1) {
                        if (isset($req_user_id) && $req_user_id != "") {
                            $LeadCount->whereIn('leads.assigned_to', $req_user_id_ex);
                        }
                    } else if ($isSalePerson == 1) {
                        if (isset($req_user_id) && $req_user_id != "") {
                            $LeadCount->whereIn('leads.assigned_to', $req_user_id_ex);
                        } else {
                            $LeadCount->whereIn('leads.assigned_to', $childSalePersonsIds);
                        }
                    } else if($isChannelPartner != 0){
                        $LeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $LeadCount->where('lead_sources.source', Auth::user()->id);
                    }

                    if (isset($req_channel_partner_user_id) && is_array($req_channel_partner_user_id_ex)) {
                        $LeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $LeadCount->whereIn('lead_sources.source', $req_channel_partner_user_id_ex);
                    }
                    // if ($isSalePerson == 1) {
                    //     $LeadCount->whereIn('leads.assigned_to', $childSalePersonsIds);
                    // }
                    $LeadCount = $LeadCount->get();
                    $MonthArrs = $LeadCount->pluck('month')->all();
                    $LeadCountArr = $LeadCount->pluck('lead_count');
                    $DealCountArr = $LeadCount->pluck('deal_count');
                    $counter = 1; 
                    $MonthArr = array_map(function ($item) use (&$counter) {
                        if ($item === 'q') {
                            $replacement = $counter;
                            $counter++; 
                            return 'Q'.$replacement;
                        }
                        return $item;
                    }, $MonthArrs);
                }

                $response = successRes();
                $response['MonthArr'] = $MonthArr;

                $arrCount = [];
                $arrLeadCount = [];
                $arrLeadCount['name'] = 'Lead';
                $arrLeadCount['data'] = $LeadCountArr;
                array_push($arrCount, $arrLeadCount);

                $arrDealCount = [];
                $arrDealCount['name'] = 'Deal';
                $arrDealCount['data'] = $DealCountArr;
                array_push($arrCount, $arrDealCount);

                $response['CountArr'] = $arrCount;
                $response['title'] = $req_dataType;
                $response['chart_title'] = "Won Deal's";
                $response['type'] = 2;
            } elseif ($req_dataType == 'LOST') {

                if($req_filter_type == "" || $req_filter_type == "MONTH"){ 
                    $LeadCount = LeadStatusUpdate::query();
                    $LeadCount->selectRaw("DATE_FORMAT(lead_status_updates.created_at, '%b') as month");
                    $LeadCount->selectRaw('SUM(CASE WHEN leads.is_deal = 1 THEN 1 ELSE 0 END) as deal_count');
                    $LeadCount->selectRaw('SUM(CASE WHEN leads.is_deal = 0 THEN 1 ELSE 0 END) as lead_count');
                    $LeadCount->whereYear('lead_status_updates.created_at', date('Y'));
                    $LeadCount->leftJoin('leads', 'leads.id', '=', 'lead_status_updates.lead_id');
                    $LeadCount->whereIn('lead_status_updates.new_status', [5, 104]);
                    $LeadCount->groupBy(DB::raw("DATE_FORMAT(lead_status_updates.created_at, '%b')"), DB::raw('MONTH(lead_status_updates.created_at)'));
                    $LeadCount->orderBy(DB::raw('MONTH(lead_status_updates.created_at)'), 'ASC');
                    if ($isAdminOrCompanyAdmin == 1) {
                        if (isset($req_user_id) && $req_user_id != "") {
                            $LeadCount->whereIn('leads.assigned_to', $req_user_id_ex);
                        }
                    } else if ($isSalePerson == 1) {
                        if (isset($req_user_id) && $req_user_id != "") {
                            $LeadCount->whereIn('leads.assigned_to', $req_user_id_ex);
                        } else {
                            $LeadCount->whereIn('leads.assigned_to', $childSalePersonsIds);
                        }
                    } else if($isChannelPartner != 0){
                        $LeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $LeadCount->where('lead_sources.source', Auth::user()->id);
                    }

                    if (isset($req_channel_partner_user_id) && is_array($req_channel_partner_user_id_ex)) {
                        $LeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $LeadCount->whereIn('lead_sources.source', $req_channel_partner_user_id_ex);
                    }
                    // if ($isSalePerson == 1) {
                    //     $LeadCount->whereIn('leads.assigned_to', $childSalePersonsIds);
                    // }
                    $LeadCount = $LeadCount->get();
                    $MonthArr = $LeadCount->pluck('month');
                    $LeadCountArr = $LeadCount->pluck('lead_count');
                    $DealCountArr = $LeadCount->pluck('deal_count');
                } else if($req_filter_type == "YEAR") { 
                    $LeadCount = LeadStatusUpdate::query();
                    $LeadCount->selectRaw("DATE_FORMAT(lead_status_updates.created_at, '%Y') as month");
                    $LeadCount->selectRaw('SUM(CASE WHEN leads.is_deal = 1 THEN 1 ELSE 0 END) as deal_count');
                    $LeadCount->selectRaw('SUM(CASE WHEN leads.is_deal = 0 THEN 1 ELSE 0 END) as lead_count');
                    $LeadCount->leftJoin('leads', 'leads.id', '=', 'lead_status_updates.lead_id');
                    $LeadCount->whereIn('lead_status_updates.new_status', [5, 104]);
                    $LeadCount->groupBy(DB::raw("DATE_FORMAT(lead_status_updates.created_at, '%Y')"), DB::raw('YEAR(lead_status_updates.created_at)'));
                    $LeadCount->orderBy(DB::raw('YEAR(lead_status_updates.created_at)'), 'ASC');
                    if ($isAdminOrCompanyAdmin == 1) {
                        if (isset($req_user_id) && $req_user_id != "") {
                            $LeadCount->whereIn('leads.assigned_to', $req_user_id_ex);
                        }
                    } else if ($isSalePerson == 1) {
                        if (isset($req_user_id) && $req_user_id != "") {
                            $LeadCount->whereIn('leads.assigned_to', $req_user_id_ex);
                        } else {
                            $LeadCount->whereIn('leads.assigned_to', $childSalePersonsIds);
                        }
                    } else if($isChannelPartner != 0){
                        $LeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $LeadCount->where('lead_sources.source', Auth::user()->id);
                    }

                    if (isset($req_channel_partner_user_id) && is_array($req_channel_partner_user_id_ex)) {
                        $LeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $LeadCount->whereIn('lead_sources.source', $req_channel_partner_user_id_ex);
                    }
                    // if ($isSalePerson == 1) {
                    //     $LeadCount->whereIn('leads.assigned_to', $childSalePersonsIds);
                    // }
                    $LeadCount = $LeadCount->get();
                    $MonthArrs = $LeadCount->pluck('month')->all();
                    $LeadCountArr = $LeadCount->pluck('lead_count');
                    $DealCountArr = $LeadCount->pluck('deal_count');

                    $MonthArr = array_map(function ($item) {
                        return $item === null ? 'No date' : $item;
                    }, $MonthArrs);
                } else if($req_filter_type == "QUARTER") { 
                    $LeadCount = LeadStatusUpdate::query();
                    $LeadCount->selectRaw("DATE_FORMAT(lead_status_updates.created_at, '%q') as month");
                    $LeadCount->selectRaw('SUM(CASE WHEN leads.is_deal = 1 THEN 1 ELSE 0 END) as deal_count');
                    $LeadCount->selectRaw('SUM(CASE WHEN leads.is_deal = 0 THEN 1 ELSE 0 END) as lead_count');
                    $LeadCount->whereYear('lead_status_updates.created_at', date('Y'));
                    $LeadCount->leftJoin('leads', 'leads.id', '=', 'lead_status_updates.lead_id');
                    $LeadCount->whereIn('lead_status_updates.new_status', [5, 104]);
                    $LeadCount->groupBy(DB::raw("DATE_FORMAT(lead_status_updates.created_at, '%q')"), DB::raw('QUARTER(lead_status_updates.created_at)'));
                    $LeadCount->orderBy(DB::raw('QUARTER(lead_status_updates.created_at)'), 'ASC');
                    if ($isAdminOrCompanyAdmin == 1) {
                        if (isset($req_user_id) && $req_user_id != "") {
                            $LeadCount->whereIn('leads.assigned_to', $req_user_id_ex);
                        }
                    } else if ($isSalePerson == 1) {
                        if (isset($req_user_id) && $req_user_id != "") {
                            $LeadCount->whereIn('leads.assigned_to', $req_user_id_ex);
                        } else {
                            $LeadCount->whereIn('leads.assigned_to', $childSalePersonsIds);
                        }
                    } else if($isChannelPartner != 0){
                        $LeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $LeadCount->where('lead_sources.source', Auth::user()->id);
                    }

                    if (isset($req_channel_partner_user_id) && is_array($req_channel_partner_user_id_ex)) {
                        $LeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $LeadCount->whereIn('lead_sources.source', $req_channel_partner_user_id_ex);
                    }
                    // if ($isSalePerson == 1) {
                    //     $LeadCount->whereIn('leads.assigned_to', $childSalePersonsIds);
                    // }
                    $LeadCount = $LeadCount->get();
                    $MonthArrs = $LeadCount->pluck('month')->all();
                    $LeadCountArr = $LeadCount->pluck('lead_count');
                    $DealCountArr = $LeadCount->pluck('deal_count');
                    $counter = 1; 
                    $MonthArr = array_map(function ($item) use (&$counter) {
                        if ($item === 'q') {
                            $replacement = $counter;
                            $counter++; 
                            return 'Q'.$replacement;
                        }
                        return $item;
                    }, $MonthArrs);
                }

                $response = successRes();
                $response['MonthArr'] = $MonthArr;

                $arrCount = [];
                $arrLeadCount = [];
                $arrLeadCount['name'] = 'Lead';
                $arrLeadCount['data'] = $LeadCountArr;
                array_push($arrCount, $arrLeadCount);
                $arrDealCount = [];
                $arrDealCount['name'] = 'Deal';
                $arrDealCount['data'] = $DealCountArr;
                array_push($arrCount, $arrDealCount);

                $response['CountArr'] = $arrCount;
                $response['title'] = $req_dataType;
                $response['chart_title'] = "Lost Lead's & Deal's";
                $response['type'] = 2;
            } elseif ($req_dataType == 'COLD') {

                if($req_filter_type == "" || $req_filter_type == "MONTH"){ 
                    $LeadCount = LeadStatusUpdate::query();
                    $LeadCount->selectRaw("DATE_FORMAT(lead_status_updates.created_at, '%b') as month");
                    $LeadCount->selectRaw('SUM(CASE WHEN leads.is_deal = 1 THEN 1 ELSE 0 END) as deal_count');
                    $LeadCount->selectRaw('SUM(CASE WHEN leads.is_deal = 0 THEN 1 ELSE 0 END) as lead_count');
                    $LeadCount->whereYear('lead_status_updates.created_at', date('Y'));
                    $LeadCount->leftJoin('leads', 'leads.id', '=', 'lead_status_updates.lead_id');
                    $LeadCount->whereIn('lead_status_updates.new_status', [6, 105]);
                    $LeadCount->groupBy(DB::raw("DATE_FORMAT(lead_status_updates.created_at, '%b')"), DB::raw('MONTH(lead_status_updates.created_at)'));
                    $LeadCount->orderBy(DB::raw('MONTH(lead_status_updates.created_at)'), 'ASC');
                    if ($isAdminOrCompanyAdmin == 1) {
                        if (isset($req_user_id) && $req_user_id != "") {
                            $LeadCount->whereIn('leads.assigned_to', $req_user_id_ex);
                        }
                    } else if ($isSalePerson == 1) {
                        if (isset($req_user_id) && $req_user_id != "") {
                            $LeadCount->whereIn('leads.assigned_to', $req_user_id_ex);
                        } else {
                            $LeadCount->whereIn('leads.assigned_to', $childSalePersonsIds);
                        }
                    } else if($isChannelPartner != 0){
                        $LeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $LeadCount->where('lead_sources.source', Auth::user()->id);
                    }

                    if (isset($req_channel_partner_user_id) && is_array($req_channel_partner_user_id_ex)) {
                        $LeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $LeadCount->whereIn('lead_sources.source', $req_channel_partner_user_id_ex);
                    }
                    // if ($isSalePerson == 1) {
                    //     $LeadCount->whereIn('leads.assigned_to', $childSalePersonsIds);
                    // }
                    $LeadCount = $LeadCount->get();
                    $MonthArr = $LeadCount->pluck('month');
                    $LeadCountArr = $LeadCount->pluck('lead_count')->all();
                    $DealCountArr = $LeadCount->pluck('deal_count');
                } else if($req_filter_type == "YEAR") { 
                    $LeadCount = LeadStatusUpdate::query();
                    $LeadCount->selectRaw("DATE_FORMAT(lead_status_updates.created_at, '%Y') as month");
                    $LeadCount->selectRaw('SUM(CASE WHEN leads.is_deal = 1 THEN 1 ELSE 0 END) as deal_count');
                    $LeadCount->selectRaw('SUM(CASE WHEN leads.is_deal = 0 THEN 1 ELSE 0 END) as lead_count');
                    $LeadCount->leftJoin('leads', 'leads.id', '=', 'lead_status_updates.lead_id');
                    $LeadCount->whereIn('lead_status_updates.new_status', [6, 105]);
                    $LeadCount->groupBy(DB::raw("DATE_FORMAT(lead_status_updates.created_at, '%Y')"), DB::raw('YEAR(lead_status_updates.created_at)'));
                    $LeadCount->orderBy(DB::raw('YEAR(lead_status_updates.created_at)'), 'ASC');
                    if ($isAdminOrCompanyAdmin == 1) {
                        if (isset($req_user_id) && $req_user_id != "") {
                            $LeadCount->whereIn('leads.assigned_to', $req_user_id_ex);
                        }
                    } else if ($isSalePerson == 1) {
                        if (isset($req_user_id) && $req_user_id != "") {
                            $LeadCount->whereIn('leads.assigned_to', $req_user_id_ex);
                        } else {
                            $LeadCount->whereIn('leads.assigned_to', $childSalePersonsIds);
                        }
                    } else if($isChannelPartner != 0){
                        $LeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $LeadCount->where('lead_sources.source', Auth::user()->id);
                    }

                    if (isset($req_channel_partner_user_id) && is_array($req_channel_partner_user_id_ex)) {
                        $LeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $LeadCount->whereIn('lead_sources.source', $req_channel_partner_user_id_ex);
                    }
                    // if ($isSalePerson == 1) {
                    //     $LeadCount->whereIn('leads.assigned_to', $childSalePersonsIds);
                    // }
                    $LeadCount = $LeadCount->get();
                    $MonthArrs = $LeadCount->pluck('month')->all();
                    $LeadCountArr = $LeadCount->pluck('lead_count');
                    $DealCountArr = $LeadCount->pluck('deal_count');
    
                    $MonthArr = array_map(function ($item) {
                        return $item === null ? 'No date' : $item;
                    }, $MonthArrs);
                } else if($req_filter_type == "QUARTER") { 
                    $LeadCount = LeadStatusUpdate::query();
                    $LeadCount->selectRaw("DATE_FORMAT(lead_status_updates.created_at, '%q') as month");
                    $LeadCount->selectRaw('SUM(CASE WHEN leads.is_deal = 1 THEN 1 ELSE 0 END) as deal_count');
                    $LeadCount->selectRaw('SUM(CASE WHEN leads.is_deal = 0 THEN 1 ELSE 0 END) as lead_count');
                    $LeadCount->whereYear('lead_status_updates.created_at', date('Y'));
                    $LeadCount->leftJoin('leads', 'leads.id', '=', 'lead_status_updates.lead_id');
                    $LeadCount->whereIn('lead_status_updates.new_status', [6, 105]);
                    $LeadCount->groupBy(DB::raw("DATE_FORMAT(lead_status_updates.created_at, '%q')"), DB::raw('QUARTER(lead_status_updates.created_at)'));
                    $LeadCount->orderBy(DB::raw('QUARTER(lead_status_updates.created_at)'), 'ASC');
                    if ($isAdminOrCompanyAdmin == 1) {
                        if (isset($req_user_id) && $req_user_id != "") {
                            $LeadCount->whereIn('leads.assigned_to', $req_user_id_ex);
                        }
                    } else if ($isSalePerson == 1) {
                        if (isset($req_user_id) && $req_user_id != "") {
                            $LeadCount->whereIn('leads.assigned_to', $req_user_id_ex);
                        } else {
                            $LeadCount->whereIn('leads.assigned_to', $childSalePersonsIds);
                        }
                    } else if($isChannelPartner != 0){
                        $LeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $LeadCount->where('lead_sources.source', Auth::user()->id);
                    }

                    if (isset($req_channel_partner_user_id) && is_array($req_channel_partner_user_id_ex)) {
                        $LeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $LeadCount->whereIn('lead_sources.source', $req_channel_partner_user_id_ex);
                    }
                    // if ($isSalePerson == 1) {
                    //     $LeadCount->whereIn('leads.assigned_to', $childSalePersonsIds);
                    // }
                    $LeadCount = $LeadCount->get();
                    $MonthArrs = $LeadCount->pluck('month')->all();
                    $LeadCountArr = $LeadCount->pluck('lead_count');
                    $DealCountArr = $LeadCount->pluck('deal_count');
                    $counter = 1; 
                    $MonthArr = array_map(function ($item) use (&$counter) {
                        if ($item === 'q') {
                            $replacement = $counter;
                            $counter++; 
                            return 'Q'.$replacement;
                        }
                        return $item;
                    }, $MonthArrs);
                }

                $response = successRes();
                $response['MonthArr'] = $MonthArr;

                $arrCount = [];
                $arrLeadCount = [];
                $arrLeadCount['name'] = 'Lead';
                $arrLeadCount['data'] = $LeadCountArr;
                array_push($arrCount, $arrLeadCount);
                $arrDealCount = [];
                $arrDealCount['name'] = 'Deal';
                $arrDealCount['data'] = $DealCountArr;
                array_push($arrCount, $arrDealCount);

                $response['CountArr'] = $arrCount;
                $response['title'] = $req_dataType;
                $response['chart_title'] = "Cold Lead's & Deal's";
                $response['type'] = 2;
            } elseif ($req_dataType == 'REWARD_ARCHITECT') {
                if($req_filter_type == "" || $req_filter_type == "MONTH"){
                    $RewardArchitect = GiftProductOrder::query();
			        $RewardArchitect->selectRaw("DATE_FORMAT(gift_product_orders.created_at, '%b') as month");
			        $RewardArchitect->selectRaw("COUNT(gift_product_orders.id) as count");
			        $RewardArchitect->leftJoin('users', 'users.id', '=', 'gift_product_orders.user_id');
			        if ($isSalePerson == 1) {
			        	$RewardArchitect->leftJoin('architect', 'architect.user_id', '=', 'gift_product_orders.user_id');
			        	$RewardArchitect->whereIn('architect.sale_person_id', $childSalePersonsIds);
			        }
			        $RewardArchitect->where('users.type', 202);
			        $RewardArchitect->whereYear('gift_product_orders.created_at', date('Y'));
                    $RewardArchitect->groupBy(DB::raw("DATE_FORMAT(gift_product_orders.created_at, '%b')"), DB::raw('MONTH(gift_product_orders.created_at)'));
                    $RewardArchitect->orderBy(DB::raw('MONTH(gift_product_orders.created_at)'), 'ASC');
                    $RewardArchitectOrder = $RewardArchitect->get();
                    $MonthArr = $RewardArchitectOrder->pluck('month');
                    $CountArr = $RewardArchitectOrder->pluck('count');

                    $response = successRes();
                    $response['MonthArr'] = $MonthArr;
                    $response['CountArr'] = $CountArr;
                    $response['title'] = $req_dataType;
                    $response['chart_title'] = "Architect Reward Order's";
                    $response['type'] = 1;
                } else if($req_filter_type == "YEAR") {
                    $RewardArchitect = GiftProductOrder::query();
			        $RewardArchitect->selectRaw("DATE_FORMAT(gift_product_orders.created_at, '%Y') as month");
			        $RewardArchitect->selectRaw("COUNT(gift_product_orders.id) as count");
			        $RewardArchitect->leftJoin('users', 'users.id', '=', 'gift_product_orders.user_id');
			        if ($isSalePerson == 1) {
			        	$RewardArchitect->leftJoin('architect', 'architect.user_id', '=', 'gift_product_orders.user_id');
			        	$RewardArchitect->whereIn('architect.sale_person_id', $childSalePersonsIds);
			        }
			        $RewardArchitect->where('users.type', 202);
                    $RewardArchitect->groupBy(DB::raw("DATE_FORMAT(gift_product_orders.created_at, '%Y')"), DB::raw('YEAR(gift_product_orders.created_at)'));
                    $RewardArchitect->orderBy(DB::raw('YEAR(gift_product_orders.created_at)'), 'ASC');
                    $RewardArchitectOrder = $RewardArchitect->get();
                    $CountArr = $RewardArchitectOrder->pluck('count');

                    $MonthArrs = $RewardArchitectOrder->pluck('month')->all();
                    $MonthArr = array_map(function ($item) {
                        return $item === null ? 'No date' : $item;
                    }, $MonthArrs);

                    $response = successRes();
                    $response['MonthArr'] = $MonthArr;
                    $response['CountArr'] = $CountArr;
                    $response['title'] = $req_dataType;
                    $response['chart_title'] = "Architect Reward Order's";
                    $response['type'] = 1;

                } else if($req_filter_type == "QUARTER") {
                    $RewardArchitect = GiftProductOrder::query();
			        $RewardArchitect->selectRaw("DATE_FORMAT(gift_product_orders.created_at, '%q') as month");
			        $RewardArchitect->selectRaw("COUNT(gift_product_orders.id) as count");
			        $RewardArchitect->leftJoin('users', 'users.id', '=', 'gift_product_orders.user_id');
			        if ($isSalePerson == 1) {
			        	$RewardArchitect->leftJoin('architect', 'architect.user_id', '=', 'gift_product_orders.user_id');
			        	$RewardArchitect->whereIn('architect.sale_person_id', $childSalePersonsIds);
			        }
			        $RewardArchitect->where('users.type', 202);
			        $RewardArchitect->whereYear('gift_product_orders.created_at', date('Y'));
                    $RewardArchitect->groupBy(DB::raw("DATE_FORMAT(gift_product_orders.created_at, '%q')"), DB::raw('QUARTER(gift_product_orders.created_at)'));
                    $RewardArchitect->orderBy(DB::raw('QUARTER(gift_product_orders.created_at)'), 'ASC');
                    $RewardArchitectOrder = $RewardArchitect->get();
                    $CountArr = $RewardArchitectOrder->pluck('count');
                    $MonthArrs = $RewardArchitectOrder->pluck('month')->all();
                    $counter = 1; 
                    $MonthArr = array_map(function ($item) use (&$counter) {
                        if ($item === 'q') {
                            $replacement = $counter;
                            $counter++; 
                            return 'Q'.$replacement;
                        }
                        return $item;
                    }, $MonthArrs);

                    $response = successRes();
                    $response['MonthArr'] = $MonthArr;
                    $response['CountArr'] = $CountArr;
                    $response['title'] = $req_dataType;
                    $response['chart_title'] = "Architect Reward Order's";
                    $response['type'] = 1;

                }

            } elseif ($req_dataType == 'REWARD_ELECTRICIAN') {
                if($req_filter_type == "" || $req_filter_type == "MONTH"){
                    $RewardElectrician = GiftProductOrder::query();
			        $RewardElectrician->selectRaw("DATE_FORMAT(gift_product_orders.created_at, '%b') as month");
			        $RewardElectrician->selectRaw("COUNT(gift_product_orders.id) as count");
			        $RewardElectrician->leftJoin('users', 'users.id', '=', 'gift_product_orders.user_id');
			        if ($isSalePerson == 1) {
			        	$RewardElectrician->leftJoin('architect', 'architect.user_id', '=', 'gift_product_orders.user_id');
			        	$RewardElectrician->whereIn('architect.sale_person_id', $childSalePersonsIds);
			        }
			        $RewardElectrician->where('users.type', 302);
			        $RewardElectrician->whereYear('gift_product_orders.created_at', date('Y'));
                    $RewardElectrician->groupBy(DB::raw("DATE_FORMAT(gift_product_orders.created_at, '%b')"), DB::raw('MONTH(gift_product_orders.created_at)'));
                    $RewardElectrician->orderBy(DB::raw('MONTH(gift_product_orders.created_at)'), 'ASC');
                    $RewardElectricianOrder = $RewardElectrician->get();
                    $MonthArr = $RewardElectricianOrder->pluck('month');
                    $CountArr = $RewardElectricianOrder->pluck('count');

                    $response = successRes();
                    $response['MonthArr'] = $MonthArr;
                    $response['CountArr'] = $CountArr;
                    $response['title'] = $req_dataType;
                    $response['chart_title'] = "Electrician Reward Order's";
                    $response['type'] = 1;
                } else if($req_filter_type == "YEAR") {
                    $RewardElectrician = GiftProductOrder::query();
			        $RewardElectrician->selectRaw("DATE_FORMAT(gift_product_orders.created_at, '%Y') as month");
			        $RewardElectrician->selectRaw("COUNT(gift_product_orders.id) as count");
			        $RewardElectrician->leftJoin('users', 'users.id', '=', 'gift_product_orders.user_id');
			        if ($isSalePerson == 1) {
			        	$RewardElectrician->leftJoin('architect', 'architect.user_id', '=', 'gift_product_orders.user_id');
			        	$RewardElectrician->whereIn('architect.sale_person_id', $childSalePersonsIds);
			        }
			        $RewardElectrician->where('users.type', 302);
                    $RewardElectrician->groupBy(DB::raw("DATE_FORMAT(gift_product_orders.created_at, '%Y')"), DB::raw('YEAR(gift_product_orders.created_at)'));
                    $RewardElectrician->orderBy(DB::raw('YEAR(gift_product_orders.created_at)'), 'ASC');
                    $RewardElectricianOrder = $RewardElectrician->get();
                    $CountArr = $RewardElectricianOrder->pluck('count');

                    $MonthArrs = $RewardElectricianOrder->pluck('month')->all();
                    $MonthArr = array_map(function ($item) {
                        return $item === null ? 'No date' : $item;
                    }, $MonthArrs);

                    $response = successRes();
                    $response['MonthArr'] = $MonthArr;
                    $response['CountArr'] = $CountArr;
                    $response['title'] = $req_dataType;
                    $response['chart_title'] = "Electrician Reward Order's";
                    $response['type'] = 1;

                } else if($req_filter_type == "QUARTER") {
                    $RewardElectrician = GiftProductOrder::query();
			        $RewardElectrician->selectRaw("DATE_FORMAT(gift_product_orders.created_at, '%q') as month");
			        $RewardElectrician->selectRaw("COUNT(gift_product_orders.id) as count");
			        $RewardElectrician->leftJoin('users', 'users.id', '=', 'gift_product_orders.user_id');
			        if ($isSalePerson == 1) {
			        	$RewardElectrician->leftJoin('architect', 'architect.user_id', '=', 'gift_product_orders.user_id');
			        	$RewardElectrician->whereIn('architect.sale_person_id', $childSalePersonsIds);
			        }
			        $RewardElectrician->where('users.type', 302);
			        $RewardElectrician->whereYear('gift_product_orders.created_at', date('Y'));
                    $RewardElectrician->groupBy(DB::raw("DATE_FORMAT(gift_product_orders.created_at, '%q')"), DB::raw('QUARTER(gift_product_orders.created_at)'));
                    $RewardElectrician->orderBy(DB::raw('QUARTER(gift_product_orders.created_at)'), 'ASC');
                    $RewardElectricianOrder = $RewardElectrician->get();
                    $CountArr = $RewardElectricianOrder->pluck('count');
                    $MonthArrs = $RewardElectricianOrder->pluck('month')->all();
                    $counter = 1; 
                    $MonthArr = array_map(function ($item) use (&$counter) {
                        if ($item === 'q') {
                            $replacement = $counter;
                            $counter++; 
                            return 'Q'.$replacement;
                        }
                        return $item;
                    }, $MonthArrs);

                    $response = successRes();
                    $response['MonthArr'] = $MonthArr;
                    $response['CountArr'] = $CountArr;
                    $response['title'] = $req_dataType;
                    $response['chart_title'] = "Electrician Reward Order's";
                    $response['type'] = 1;

                }

            } elseif ($req_dataType == 'OFFLINELEAD') {
                if ($req_filter_type == '' || $req_filter_type == 'MONTH') {
                    $LeadCount = Lead::query();
                    $LeadCount->whereNotIn('leads.source_type', ['user-4', 'textnotrequired-1', 'textnotrequired-11', 'textnotrequired-12']);
                    $LeadCount->selectRaw("DATE_FORMAT(leads.created_at, '%b') as month");
                    $LeadCount->selectRaw('SUM(CASE WHEN is_deal = 1 THEN 1 ELSE 0 END) as deal_count');
                    $LeadCount->selectRaw('SUM(CASE WHEN is_deal = 0 THEN 1 ELSE 0 END) as lead_count');
                    $LeadCount->whereYear('leads.created_at', date('Y'));
                    $LeadCount->groupBy(DB::raw("DATE_FORMAT(leads.created_at, '%b')"), DB::raw('MONTH(leads.created_at)'));
                    $LeadCount->orderBy(DB::raw('MONTH(leads.created_at)'), 'ASC');
                    if ($isAdminOrCompanyAdmin == 1) {
                        if (isset($req_user_id) && $req_user_id != '') {
                            $LeadCount->whereIn('leads.assigned_to', $req_user_id);
                        }
                    } elseif ($isSalePerson == 1) {
                        if (isset($req_user_id) && $req_user_id != '') {
                            $LeadCount->whereIn('leads.assigned_to', $req_user_id);
                        } else {
                            $LeadCount->whereIn('leads.assigned_to', $childSalePersonsIds);
                        }
                    } elseif ($isChannelPartner != 0) {
                        $LeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $LeadCount->where('lead_sources.source', Auth::user()->id);
                    }
                    if (isset($req_channel_partner_user_id) && is_array($req_channel_partner_user_id)) {
                        $LeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $LeadCount->whereIn('lead_sources.source', $req_channel_partner_user_id);
                    }
                    // if ($isSalePerson == 1) {
                    //     $LeadCount->whereIn('leads.assigned_to', $childSalePersonsIds);
                    // }
                    $LeadCount = $LeadCount->get();
                    $MonthArr = $LeadCount->pluck('month');
                    $LeadCountArr = $LeadCount->pluck('lead_count');
                    $DealCountArr = $LeadCount->pluck('deal_count');
                } elseif ($req_filter_type == 'YEAR') {
                    $LeadCount = Lead::query();
                    $LeadCount->whereNotIn('leads.source_type', ['user-4', 'textnotrequired-1', 'textnotrequired-11', 'textnotrequired-12']);
                    $LeadCount->selectRaw("DATE_FORMAT(created_at, '%Y') as month");
                    $LeadCount->selectRaw('SUM(CASE WHEN is_deal = 1 THEN 1 ELSE 0 END) as deal_count');
                    $LeadCount->selectRaw('SUM(CASE WHEN is_deal = 0 THEN 1 ELSE 0 END) as lead_count');
                    $LeadCount->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y')"), DB::raw('YEAR(created_at)'));
                    $LeadCount->orderBy(DB::raw('YEAR(created_at)'), 'ASC');
                    if ($isAdminOrCompanyAdmin == 1) {
                        if (isset($req_user_id) && $req_user_id != '') {
                            $LeadCount->whereIn('leads.assigned_to', $req_user_id);
                        }
                    } elseif ($isSalePerson == 1) {
                        if (isset($req_user_id) && $req_user_id != '') {
                            $LeadCount->whereIn('leads.assigned_to', $req_user_id);
                        } else {
                            $LeadCount->whereIn('leads.assigned_to', $childSalePersonsIds);
                        }
                    } elseif ($isChannelPartner != 0) {
                        $LeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $LeadCount->where('lead_sources.source', Auth::user()->id);
                    }
                    if (isset($req_channel_partner_user_id) && is_array($req_channel_partner_user_id)) {
                        $LeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $LeadCount->whereIn('lead_sources.source', $req_channel_partner_user_id);
                    }
                    // if ($isSalePerson == 1) {
                    //     $LeadCount->whereIn('leads.assigned_to', $childSalePersonsIds);
                    // }
                    $LeadCount = $LeadCount->get();
                    $MonthArrs = $LeadCount->pluck('month')->all();
                    $LeadCountArr = $LeadCount->pluck('lead_count');
                    $DealCountArr = $LeadCount->pluck('deal_count');
                    $MonthArr = array_map(function ($item) {
                        return $item === null ? 'No date' : $item;
                    }, $MonthArrs);
                } elseif ($req_filter_type == 'QUARTER') {
                    $LeadCount = Lead::query();
                    $LeadCount->whereNotIn('leads.source_type', ['user-4', 'textnotrequired-1', 'textnotrequired-11', 'textnotrequired-12']);
                    $LeadCount->selectRaw("DATE_FORMAT(created_at, '%q') as month");
                    $LeadCount->selectRaw('SUM(CASE WHEN is_deal = 1 THEN 1 ELSE 0 END) as deal_count');
                    $LeadCount->selectRaw('SUM(CASE WHEN is_deal = 0 THEN 1 ELSE 0 END) as lead_count');
                    $LeadCount->whereYear('created_at', date('Y'));
                    $LeadCount->groupBy(DB::raw("DATE_FORMAT(created_at, '%q')"), DB::raw('QUARTER(created_at)'));
                    $LeadCount->orderBy(DB::raw('QUARTER(created_at)'), 'ASC');
                    if ($isAdminOrCompanyAdmin == 1) {
                        if (isset($req_user_id) && $req_user_id != '') {
                            $LeadCount->whereIn('leads.assigned_to', $req_user_id);
                        }
                    } elseif ($isSalePerson == 1) {
                        if (isset($req_user_id) && $req_user_id != '') {
                            $LeadCount->whereIn('leads.assigned_to', $req_user_id);
                        } else {
                            $LeadCount->whereIn('leads.assigned_to', $childSalePersonsIds);
                        }
                    } elseif ($isChannelPartner != 0) {
                        $LeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $LeadCount->where('lead_sources.source', Auth::user()->id);
                    }
                    if (isset($req_channel_partner_user_id) && is_array($req_channel_partner_user_id)) {
                        $LeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $LeadCount->whereIn('lead_sources.source', $req_channel_partner_user_id);
                    }
                    // if ($isSalePerson == 1) {
                    //     $LeadCount->whereIn('leads.assigned_to', $childSalePersonsIds);
                    // }
                    $LeadCount = $LeadCount->get();
                    $MonthArrs = $LeadCount->pluck('month')->all();
                    $LeadCountArr = $LeadCount->pluck('lead_count');
                    $DealCountArr = $LeadCount->pluck('deal_count');
                    $counter = 1;
                    $MonthArr = array_map(function ($item) use (&$counter) {
                        if ($item === 'q') {
                            $replacement = $counter;
                            $counter++;
                            return 'Q' . $replacement;
                        }
                        return $item;
                    }, $MonthArrs);
                } elseif ($req_filter_type == 'WEEK') {
                    $LeadCount = Lead::query();
                    $LeadCount->whereNotIn('leads.source_type', ['user-4', 'textnotrequired-1', 'textnotrequired-11', 'textnotrequired-12']);
                    $LeadCount->selectRaw("CONCAT(DATE_FORMAT(leads.created_at, '%b'),'-', WEEK(leads.created_at)) AS month");
                    $LeadCount->selectRaw('SUM(CASE WHEN is_deal = 1 THEN 1 ELSE 0 END) as deal_count');
                    $LeadCount->selectRaw('SUM(CASE WHEN is_deal = 0 THEN 1 ELSE 0 END) as lead_count');
                    $LeadCount->whereYear('leads.created_at', date('Y'));
                    $LeadCount->groupBy(DB::raw("CONCAT(DATE_FORMAT(leads.created_at, '%b'),'-', WEEK(leads.created_at))"), DB::raw("DATE_FORMAT(leads.created_at, '%b')"), DB::raw('MONTH(leads.created_at)'), DB::raw('WEEK(leads.created_at)'));
                    $LeadCount->orderBy(DB::raw('MONTH(leads.created_at)'), 'ASC');
                    $LeadCount->orderBy(DB::raw('WEEK(leads.created_at)'), 'ASC');
                    if ($isAdminOrCompanyAdmin == 1) {
                        if (isset($req_user_id) && $req_user_id != '') {
                            $LeadCount->whereIn('leads.assigned_to', $req_user_id);
                        }
                    } elseif ($isSalePerson == 1) {
                        if (isset($req_user_id) && $req_user_id != '') {
                            $LeadCount->whereIn('leads.assigned_to', $req_user_id);
                        } else {
                            $LeadCount->whereIn('leads.assigned_to', $childSalePersonsIds);
                        }
                    } elseif ($isChannelPartner != 0) {
                        $LeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $LeadCount->where('lead_sources.source', Auth::user()->id);
                    }
                    if (isset($req_channel_partner_user_id) && is_array($req_channel_partner_user_id)) {
                        $LeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $LeadCount->whereIn('lead_sources.source', $req_channel_partner_user_id);
                    }
                    // if ($isSalePerson == 1) {
                    //     $LeadCount->whereIn('leads.assigned_to', $childSalePersonsIds);
                    // }
                    $LeadCount = $LeadCount->get();
                    $MonthArr = $LeadCount->pluck('month');
                    $LeadCountArr = $LeadCount->pluck('lead_count');
                    $DealCountArr = $LeadCount->pluck('deal_count');
                }

                $arrCount = [];
                $arrLeadCount = [];
                $arrLeadCount['name'] = 'Lead';
                $arrLeadCount['data'] = $LeadCountArr;
                array_push($arrCount, $arrLeadCount);

                $arrDealCount = [];
                $arrDealCount['name'] = 'Deal';
                $arrDealCount['data'] = $DealCountArr;
                array_push($arrCount, $arrDealCount);

                $response = successRes();
                $response['MonthArr'] = $MonthArr;
                $response['CountArr'] = $arrCount;
                $response['title'] = $req_dataType;
                $response['chart_title'] = "New Offline Lead's";

                $response['type'] = 2;
            } elseif ($req_dataType == 'MARKETINGLEAD') {
                if ($req_filter_type == '' || $req_filter_type == 'MONTH') {
                    $LeadCount = Lead::query();
                    $LeadCount->selectRaw("DATE_FORMAT(leads.created_at, '%b') as month");
                    $LeadCount->selectRaw('SUM(CASE WHEN is_deal = 1 THEN 1 ELSE 0 END) as deal_count');
                    $LeadCount->selectRaw('SUM(CASE WHEN is_deal = 0 THEN 1 ELSE 0 END) as lead_count');
                    $LeadCount->whereYear('leads.created_at', date('Y'));
                    $LeadCount->groupBy(DB::raw("DATE_FORMAT(leads.created_at, '%b')"), DB::raw('MONTH(leads.created_at)'));
                    $LeadCount->orderBy(DB::raw('MONTH(leads.created_at)'), 'ASC');
                    $LeadCount->where(function ($query) {
                        $query->where('leads.source_type', 'user-4')
                              ->orWhere('leads.source_type', 'textnotrequired-1')
                              ->orWhere('leads.source_type', 'textnotrequired-11')
                              ->orWhere('leads.source_type', 'textnotrequired-12');
                    });
                    if ($isAdminOrCompanyAdmin == 1) {
                        if (isset($req_user_id) && $req_user_id != '') {
                            $LeadCount->whereIn('leads.assigned_to', $req_user_id);
                        }
                    } elseif ($isSalePerson == 1) {
                        if (isset($req_user_id) && $req_user_id != '') {
                            $LeadCount->whereIn('leads.assigned_to', $req_user_id);
                        } else {
                            $LeadCount->whereIn('leads.assigned_to', $childSalePersonsIds);
                        }
                    } elseif ($isChannelPartner != 0) {
                        $LeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $LeadCount->where('lead_sources.source', Auth::user()->id);
                    }
                    if (isset($req_channel_partner_user_id) && is_array($req_channel_partner_user_id)) {
                        $LeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $LeadCount->whereIn('lead_sources.source', $req_channel_partner_user_id);
                    }
                    // if ($isSalePerson == 1) {
                    //     $LeadCount->whereIn('leads.assigned_to', $childSalePersonsIds);
                    // }
                    $LeadCount = $LeadCount->get();
                    $MonthArr = $LeadCount->pluck('month');
                    $LeadCountArr = $LeadCount->pluck('lead_count');
                    $DealCountArr = $LeadCount->pluck('deal_count');
                } elseif ($req_filter_type == 'YEAR') {
                    $LeadCount = Lead::query();
                    $LeadCount->selectRaw("DATE_FORMAT(created_at, '%Y') as month");
                    $LeadCount->selectRaw('SUM(CASE WHEN is_deal = 1 THEN 1 ELSE 0 END) as deal_count');
                    $LeadCount->selectRaw('SUM(CASE WHEN is_deal = 0 THEN 1 ELSE 0 END) as lead_count');
                    $LeadCount->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y')"), DB::raw('YEAR(created_at)'));
                    $LeadCount->orderBy(DB::raw('YEAR(created_at)'), 'ASC');
                    $LeadCount->where(function ($query) {
                        $query->where('leads.source_type', 'user-4')
                              ->orWhere('leads.source_type', 'textnotrequired-1')
                              ->orWhere('leads.source_type', 'textnotrequired-11')
                              ->orWhere('leads.source_type', 'textnotrequired-12');
                    });

                    if ($isAdminOrCompanyAdmin == 1) {
                        if (isset($req_user_id) && $req_user_id != '') {
                            $LeadCount->whereIn('leads.assigned_to', $req_user_id);
                        }
                    } elseif ($isSalePerson == 1) {
                        if (isset($req_user_id) && $req_user_id != '') {
                            $LeadCount->whereIn('leads.assigned_to', $req_user_id);
                        } else {
                            $LeadCount->whereIn('leads.assigned_to', $childSalePersonsIds);
                        }
                    } elseif ($isChannelPartner != 0) {
                        $LeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $LeadCount->where('lead_sources.source', Auth::user()->id);
                    }
                    if (isset($req_channel_partner_user_id) && is_array($req_channel_partner_user_id)) {
                        $LeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $LeadCount->whereIn('lead_sources.source', $req_channel_partner_user_id);
                    }
                    // if ($isSalePerson == 1) {
                    //     $LeadCount->whereIn('leads.assigned_to', $childSalePersonsIds);
                    // }
                    $LeadCount = $LeadCount->get();
                    $MonthArrs = $LeadCount->pluck('month')->all();
                    $LeadCountArr = $LeadCount->pluck('lead_count');
                    $DealCountArr = $LeadCount->pluck('deal_count');
                    $MonthArr = array_map(function ($item) {
                        return $item === null ? 'No date' : $item;
                    }, $MonthArrs);
                } elseif ($req_filter_type == 'QUARTER') {
                    $LeadCount = Lead::query();
                    $LeadCount->selectRaw("DATE_FORMAT(created_at, '%q') as month");
                    $LeadCount->selectRaw('SUM(CASE WHEN is_deal = 1 THEN 1 ELSE 0 END) as deal_count');
                    $LeadCount->selectRaw('SUM(CASE WHEN is_deal = 0 THEN 1 ELSE 0 END) as lead_count');
                    $LeadCount->whereYear('created_at', date('Y'));
                    $LeadCount->groupBy(DB::raw("DATE_FORMAT(created_at, '%q')"), DB::raw('QUARTER(created_at)'));
                    $LeadCount->orderBy(DB::raw('QUARTER(created_at)'), 'ASC');
                    $LeadCount->where(function ($query) {
                        $query->where('leads.source_type', 'user-4')
                              ->orWhere('leads.source_type', 'textnotrequired-1')
                              ->orWhere('leads.source_type', 'textnotrequired-11')
                              ->orWhere('leads.source_type', 'textnotrequired-12');
                    });

                    if ($isAdminOrCompanyAdmin == 1) {
                        if (isset($req_user_id) && $req_user_id != '') {
                            $LeadCount->whereIn('leads.assigned_to', $req_user_id);
                        }
                    } elseif ($isSalePerson == 1) {
                        if (isset($req_user_id) && $req_user_id != '') {
                            $LeadCount->whereIn('leads.assigned_to', $req_user_id);
                        } else {
                            $LeadCount->whereIn('leads.assigned_to', $childSalePersonsIds);
                        }
                    } elseif ($isChannelPartner != 0) {
                        $LeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $LeadCount->where('lead_sources.source', Auth::user()->id);
                    }
                    if (isset($req_channel_partner_user_id) && is_array($req_channel_partner_user_id)) {
                        $LeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $LeadCount->whereIn('lead_sources.source', $req_channel_partner_user_id);
                    }
                    // if ($isSalePerson == 1) {
                    //     $LeadCount->whereIn('leads.assigned_to', $childSalePersonsIds);
                    // }
                    $LeadCount = $LeadCount->get();
                    $MonthArrs = $LeadCount->pluck('month')->all();
                    $LeadCountArr = $LeadCount->pluck('lead_count');
                    $DealCountArr = $LeadCount->pluck('deal_count');
                    $counter = 1;
                    $MonthArr = array_map(function ($item) use (&$counter) {
                        if ($item === 'q') {
                            $replacement = $counter;
                            $counter++;
                            return 'Q' . $replacement;
                        }
                        return $item;
                    }, $MonthArrs);
                } elseif ($req_filter_type == 'WEEK') {
                    $LeadCount = Lead::query();
                    $LeadCount->selectRaw("CONCAT(DATE_FORMAT(leads.created_at, '%b'),'-', WEEK(leads.created_at)) AS month");
                    $LeadCount->selectRaw('SUM(CASE WHEN is_deal = 1 THEN 1 ELSE 0 END) as deal_count');
                    $LeadCount->selectRaw('SUM(CASE WHEN is_deal = 0 THEN 1 ELSE 0 END) as lead_count');
                    $LeadCount->whereYear('leads.created_at', date('Y'));
                    $LeadCount->groupBy(DB::raw("CONCAT(DATE_FORMAT(leads.created_at, '%b'),'-', WEEK(leads.created_at))"), DB::raw("DATE_FORMAT(leads.created_at, '%b')"), DB::raw('MONTH(leads.created_at)'), DB::raw('WEEK(leads.created_at)'));
                    $LeadCount->orderBy(DB::raw('MONTH(leads.created_at)'), 'ASC');
                    $LeadCount->orderBy(DB::raw('WEEK(leads.created_at)'), 'ASC');
                    $LeadCount->where(function ($query) {
                        $query->where('leads.source_type', 'user-4')
                              ->orWhere('leads.source_type', 'textnotrequired-1')
                              ->orWhere('leads.source_type', 'textnotrequired-11')
                              ->orWhere('leads.source_type', 'textnotrequired-12');
                    });

                    if ($isAdminOrCompanyAdmin == 1) {
                        if (isset($req_user_id) && $req_user_id != '') {
                            $LeadCount->whereIn('leads.assigned_to', $req_user_id);
                        }
                    } elseif ($isSalePerson == 1) {
                        if (isset($req_user_id) && $req_user_id != '') {
                            $LeadCount->whereIn('leads.assigned_to', $req_user_id);
                        } else {
                            $LeadCount->whereIn('leads.assigned_to', $childSalePersonsIds);
                        }
                    } elseif ($isChannelPartner != 0) {
                        $LeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $LeadCount->where('lead_sources.source', Auth::user()->id);
                    }
                    if (isset($req_channel_partner_user_id) && is_array($req_channel_partner_user_id)) {
                        $LeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $LeadCount->whereIn('lead_sources.source', $req_channel_partner_user_id);
                    }
                    // if ($isSalePerson == 1) {
                    //     $LeadCount->whereIn('leads.assigned_to', $childSalePersonsIds);
                    // }
                    $LeadCount = $LeadCount->get();
                    $MonthArr = $LeadCount->pluck('month');
                    $LeadCountArr = $LeadCount->pluck('lead_count');
                    $DealCountArr = $LeadCount->pluck('deal_count');
                }

                $arrCount = [];
                $arrLeadCount = [];
                $arrLeadCount['name'] = 'Lead';
                $arrLeadCount['data'] = $LeadCountArr;
                array_push($arrCount, $arrLeadCount);

                $arrDealCount = [];
                $arrDealCount['name'] = 'Deal';
                $arrDealCount['data'] = $DealCountArr;
                array_push($arrCount, $arrDealCount);

                $response = successRes();
                $response['MonthArr'] = $MonthArr;
                $response['CountArr'] = $arrCount;
                $response['title'] = $req_dataType;
                $response['chart_title'] = "New Marketing Lead's";

                $response['type'] = 2;
            } elseif ($req_dataType == 'DEMOMEETINGDONELEAD') {
                if ($req_filter_type == '' || $req_filter_type == 'MONTH') {
                    $LeadCount = Lead::query();
                    $LeadCount->where('leads.status', [4]);
                    $LeadCount->selectRaw("DATE_FORMAT(leads.created_at, '%b') as month");
                    $LeadCount->selectRaw('SUM(CASE WHEN is_deal = 1 THEN 1 ELSE 0 END) as deal_count');
                    $LeadCount->selectRaw('SUM(CASE WHEN is_deal = 0 THEN 1 ELSE 0 END) as lead_count');
                    $LeadCount->whereYear('leads.created_at', date('Y'));
                    $LeadCount->groupBy(DB::raw("DATE_FORMAT(leads.created_at, '%b')"), DB::raw('MONTH(leads.created_at)'));
                    $LeadCount->orderBy(DB::raw('MONTH(leads.created_at)'), 'ASC');
                    if ($isAdminOrCompanyAdmin == 1) {
                        if (isset($req_user_id) && $req_user_id != '') {
                            $LeadCount->whereIn('leads.assigned_to', $req_user_id);
                        }
                    } elseif ($isSalePerson == 1) {
                        if (isset($req_user_id) && $req_user_id != '') {
                            $LeadCount->whereIn('leads.assigned_to', $req_user_id);
                        } else {
                            $LeadCount->whereIn('leads.assigned_to', $childSalePersonsIds);
                        }
                    } elseif ($isChannelPartner != 0) {
                        $LeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $LeadCount->where('lead_sources.source', Auth::user()->id);
                    }
                    if (isset($req_channel_partner_user_id) && is_array($req_channel_partner_user_id)) {
                        $LeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $LeadCount->whereIn('lead_sources.source', $req_channel_partner_user_id);
                    }
                    // if ($isSalePerson == 1) {
                    //     $LeadCount->whereIn('leads.assigned_to', $childSalePersonsIds);
                    // }
                    $LeadCount = $LeadCount->get();
                    $MonthArr = $LeadCount->pluck('month');
                    $LeadCountArr = $LeadCount->pluck('lead_count');
                    $DealCountArr = $LeadCount->pluck('deal_count');
                } elseif ($req_filter_type == 'YEAR') {
                    $LeadCount = Lead::query();
                    $LeadCount->where('leads.status', [4]);
                    $LeadCount->selectRaw("DATE_FORMAT(created_at, '%Y') as month");
                    $LeadCount->selectRaw('SUM(CASE WHEN is_deal = 1 THEN 1 ELSE 0 END) as deal_count');
                    $LeadCount->selectRaw('SUM(CASE WHEN is_deal = 0 THEN 1 ELSE 0 END) as lead_count');
                    $LeadCount->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y')"), DB::raw('YEAR(created_at)'));
                    $LeadCount->orderBy(DB::raw('YEAR(created_at)'), 'ASC');
                    if ($isAdminOrCompanyAdmin == 1) {
                        if (isset($req_user_id) && $req_user_id != '') {
                            $LeadCount->whereIn('leads.assigned_to', $req_user_id);
                        }
                    } elseif ($isSalePerson == 1) {
                        if (isset($req_user_id) && $req_user_id != '') {
                            $LeadCount->whereIn('leads.assigned_to', $req_user_id);
                        } else {
                            $LeadCount->whereIn('leads.assigned_to', $childSalePersonsIds);
                        }
                    } elseif ($isChannelPartner != 0) {
                        $LeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $LeadCount->where('lead_sources.source', Auth::user()->id);
                    }
                    if (isset($req_channel_partner_user_id) && is_array($req_channel_partner_user_id)) {
                        $LeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $LeadCount->whereIn('lead_sources.source', $req_channel_partner_user_id);
                    }
                    // if ($isSalePerson == 1) {
                    //     $LeadCount->whereIn('leads.assigned_to', $childSalePersonsIds);
                    // }
                    $LeadCount = $LeadCount->get();
                    $MonthArrs = $LeadCount->pluck('month')->all();
                    $LeadCountArr = $LeadCount->pluck('lead_count');
                    $DealCountArr = $LeadCount->pluck('deal_count');
                    $MonthArr = array_map(function ($item) {
                        return $item === null ? 'No date' : $item;
                    }, $MonthArrs);
                } elseif ($req_filter_type == 'QUARTER') {
                    $LeadCount = Lead::query();
                    $LeadCount->where('leads.status', [4]);
                    $LeadCount->selectRaw("DATE_FORMAT(created_at, '%q') as month");
                    $LeadCount->selectRaw('SUM(CASE WHEN is_deal = 1 THEN 1 ELSE 0 END) as deal_count');
                    $LeadCount->selectRaw('SUM(CASE WHEN is_deal = 0 THEN 1 ELSE 0 END) as lead_count');
                    $LeadCount->whereYear('created_at', date('Y'));
                    $LeadCount->groupBy(DB::raw("DATE_FORMAT(created_at, '%q')"), DB::raw('QUARTER(created_at)'));
                    $LeadCount->orderBy(DB::raw('QUARTER(created_at)'), 'ASC');
                    if ($isAdminOrCompanyAdmin == 1) {
                        if (isset($req_user_id) && $req_user_id != '') {
                            $LeadCount->whereIn('leads.assigned_to', $req_user_id);
                        }
                    } elseif ($isSalePerson == 1) {
                        if (isset($req_user_id) && $req_user_id != '') {
                            $LeadCount->whereIn('leads.assigned_to', $req_user_id);
                        } else {
                            $LeadCount->whereIn('leads.assigned_to', $childSalePersonsIds);
                        }
                    } elseif ($isChannelPartner != 0) {
                        $LeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $LeadCount->where('lead_sources.source', Auth::user()->id);
                    }
                    if (isset($req_channel_partner_user_id) && is_array($req_channel_partner_user_id)) {
                        $LeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $LeadCount->whereIn('lead_sources.source', $req_channel_partner_user_id);
                    }
                    // if ($isSalePerson == 1) {
                    //     $LeadCount->whereIn('leads.assigned_to', $childSalePersonsIds);
                    // }
                    $LeadCount = $LeadCount->get();
                    $MonthArrs = $LeadCount->pluck('month')->all();
                    $LeadCountArr = $LeadCount->pluck('lead_count');
                    $DealCountArr = $LeadCount->pluck('deal_count');
                    $counter = 1;
                    $MonthArr = array_map(function ($item) use (&$counter) {
                        if ($item === 'q') {
                            $replacement = $counter;
                            $counter++;
                            return 'Q' . $replacement;
                        }
                        return $item;
                    }, $MonthArrs);
                } elseif ($req_filter_type == 'WEEK') {
                    $LeadCount = Lead::query();
                    $LeadCount->where('leads.status', [4]);
                    $LeadCount->selectRaw("CONCAT(DATE_FORMAT(leads.created_at, '%b'),'-', WEEK(leads.created_at)) AS month");
                    $LeadCount->selectRaw('SUM(CASE WHEN is_deal = 1 THEN 1 ELSE 0 END) as deal_count');
                    $LeadCount->selectRaw('SUM(CASE WHEN is_deal = 0 THEN 1 ELSE 0 END) as lead_count');
                    $LeadCount->whereYear('leads.created_at', date('Y'));
                    $LeadCount->groupBy(DB::raw("CONCAT(DATE_FORMAT(leads.created_at, '%b'),'-', WEEK(leads.created_at))"), DB::raw("DATE_FORMAT(leads.created_at, '%b')"), DB::raw('MONTH(leads.created_at)'), DB::raw('WEEK(leads.created_at)'));
                    $LeadCount->orderBy(DB::raw('MONTH(leads.created_at)'), 'ASC');
                    $LeadCount->orderBy(DB::raw('WEEK(leads.created_at)'), 'ASC');
                    if ($isAdminOrCompanyAdmin == 1) {
                        if (isset($req_user_id) && $req_user_id != '') {
                            $LeadCount->whereIn('leads.assigned_to', $req_user_id);
                        }
                    } elseif ($isSalePerson == 1) {
                        if (isset($req_user_id) && $req_user_id != '') {
                            $LeadCount->whereIn('leads.assigned_to', $req_user_id);
                        } else {
                            $LeadCount->whereIn('leads.assigned_to', $childSalePersonsIds);
                        }
                    } elseif ($isChannelPartner != 0) {
                        $LeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $LeadCount->where('lead_sources.source', Auth::user()->id);
                    }
                    if (isset($req_channel_partner_user_id) && is_array($req_channel_partner_user_id)) {
                        $LeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $LeadCount->whereIn('lead_sources.source', $req_channel_partner_user_id);
                    }
                    // if ($isSalePerson == 1) {
                    //     $LeadCount->whereIn('leads.assigned_to', $childSalePersonsIds);
                    // }
                    $LeadCount = $LeadCount->get();
                    $MonthArr = $LeadCount->pluck('month');
                    $LeadCountArr = $LeadCount->pluck('lead_count');
                    $DealCountArr = $LeadCount->pluck('deal_count');
                }

                $arrCount = [];
                $arrLeadCount = [];
                $arrLeadCount['name'] = 'Lead';
                $arrLeadCount['data'] = $LeadCountArr;
                array_push($arrCount, $arrLeadCount);

                $arrDealCount = [];
                $arrDealCount['name'] = 'Deal';
                $arrDealCount['data'] = $DealCountArr;
                array_push($arrCount, $arrDealCount);

                $response = successRes();
                $response['MonthArr'] = $MonthArr;
                $response['CountArr'] = $arrCount;
                $response['title'] = $req_dataType;
                $response['chart_title'] = "Demo Meeting Done Lead's";

                $response['type'] = 2;
            } else {
                $response = errorRes("please pass valid perameater");
            }

            return response()
                ->json($response)
                ->header('Content-Type', 'application/json');
        }
    }

}
