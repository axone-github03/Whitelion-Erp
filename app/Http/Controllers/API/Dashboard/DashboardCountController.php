<?php

namespace App\Http\Controllers\API\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Architect;
use App\Models\Electrician;
use App\Models\LeadStatusUpdate;
use App\Models\Wltrn_Quotation;
use App\Models\Wltrn_QuotItemdetail;
use App\Models\Order;
use App\Models\Wlmst_target;
use App\Models\User;
use App\Models\Lead;
use App\Models\Invoice;
use App\Models\LeadTimeline;
use App\Models\ChannelPartner;
use App\Models\GiftProductOrder;
use Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use DateTime;
use Mail;
use DB;

class DashboardCountController extends Controller
{

	public function __construct()
	{

		$this->middleware(function ($request, $next) {

			$tabCanAccessBy = array(0, 1, 2, 101, 102, 103, 104, 105);

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

	function dashboardCount(Request $request)
	{
		$isSalePerson = isSalePerson();
		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		$isChannelPartner = isChannelPartner(Auth::user()->type);
		$isAccountUser = isAccountUser();

		$rules = array();
		$rules['start_date'] = 'required';
		$rules['end_date'] = 'required';
		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			$response = array();
			$response['status'] = 0;
			$response['msg'] = "The request could not be understood by the server due to malformed syntax";
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();
		} else {

			if ($isSalePerson == 1) {
				$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
			}
			$req_startdate = $request->start_date;
			$req_enddate = $request->end_date;
			$req_user_id = $request->user_id;
			$req_channel_partner_user_id = $request->channel_partner_user_id;
			$req_user_id_ex = explode(',',$request->user_id) ;
			$req_channel_partner_user_id_ex = explode(',',$request->channel_partner_user_id);
			$req_channel_partner_type = $request->channel_partner_type;
			$req_is_first_load = isset($request->is_first_load) ? $request->is_first_load : 0;

			$startDate = date('Y-m-d', strtotime($req_startdate));
			$endDate = date('Y-m-d', strtotime($req_enddate));

			$startDate = new DateTime($startDate);
			$endDate = new DateTime($endDate);
			$interval = new DateInterval('P1M'); // 1 month interval
			$period = new DatePeriod($startDate, $interval, $endDate);
			$months = [];
			$monthNumbers = [];
			foreach ($period as $date) {
			    $month = $date->format('n'); // Get the month number (1-12)
			    $months[] = $date->format('F'); // Get the month name (e.g., September)
			    $monthNumbers[] = $month; // Store the month number
			}
			// To get the month numbers as a comma-separated string
			// $monthNumbersString = implode(',', $monthNumbers);

			// TARGET AMOUNT START
			$TargetIds = array();
			if($isChannelPartner != 0){
				$TargetAmount = 0;
			}else{
				$Target = Wlmst_target::query();
				$Target->leftJoin('wlmst_targetdetail', 'wlmst_targetdetail.target_id', '=', 'wlmst_target.id');
				$Target->leftJoin('users', 'users.id', '=', 'wlmst_target.employeee_id');
				$Target->leftJoin('wlmst_financialyear', 'wlmst_financialyear.id', '=', 'wlmst_target.finyear_id');
				$Target->where('users.status', 1);
				// $Target->where('wlmst_targetdetail.quater', getQuaterFromMonth(date('m', strtotime($req_startdate))));
				if (date('m', strtotime($req_startdate)) > 3) {
					$financialyear = date('Y', strtotime($req_startdate)) . "-" . (date('Y', strtotime($req_startdate)) + 1);
				} else {
					$financialyear = (date('Y', strtotime($req_startdate)) - 1) . "-" . date('Y', strtotime($req_startdate));
				}
				$Target->where('wlmst_financialyear.name', $financialyear);
				$Target->whereIn('wlmst_targetdetail.month_number', $monthNumbers);
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
				$TargetAmount = $Target->sum('wlmst_targetdetail.target_amount');
				$TargetIds = $Target->distinct()->pluck('wlmst_target.id')->all();
			}
			// $Target->whereYear('wlmst_target.created_at', date('Y'));
			// TARGET AMOUNT END

			// PRIDICTION COUNT START
			$PridictionDealIds = array();
			if($isChannelPartner != 0){
				$PridictionDealCount = 0;
				$PridictionDealTotalAmount = 0;
				$PridictionDealWlAmount = 0;
        		$PridictionDealBillingAmount = 0;
			}else{
				$PridictionDeal = Lead::query();
				$PridictionDeal->where('leads.is_deal', '=', 1);
				$PridictionDeal->whereIn('leads.status', array(100,101,102));
				// $PridictionDeal->whereDate('leads.closing_date_time', '>=', $startDate);
				$PridictionDeal->whereDate('leads.closing_date_time', '<=', $endDate);
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

				}
				if (isset($req_channel_partner_user_id) && $req_channel_partner_user_id != "") {
					$PridictionDeal->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
					$PridictionDeal->whereIn('lead_sources.source', $req_channel_partner_user_id_ex);
				}
				$PridictionDealCount = $PridictionDeal->distinct()->pluck('leads.id')->count();
				$PridictionDealIds = $PridictionDeal->distinct()->pluck('leads.id')->all();

				$total_billing_amount = 0;
        		$total_whitelion_amount = 0;
        		$total_other_amount = 0;
        		$total_amount = 0;

        		$Leadamount = Lead::query();
        		$Leadamount->selectRaw('SUM(wltrn_quotation.quot_whitelion_amount) AS whitelion_amount');
        		$Leadamount->selectRaw('SUM(wltrn_quotation.quot_billing_amount) AS billing_amount');
        		$Leadamount->selectRaw('SUM(wltrn_quotation.quot_other_amount) AS other_amount');
        		$Leadamount->selectRaw('SUM(wltrn_quotation.quot_total_amount) AS total_amount');
        		$Leadamount->leftJoin('wltrn_quotation', 'wltrn_quotation.inquiry_id', '=', 'leads.id');
        		$Leadamount->whereIn('leads.id', $PridictionDealIds);
        		$Leadamount->where('wltrn_quotation.isfinal', 1);
        		$Leadamount = $Leadamount->first();

        		if($Leadamount){
        		    $total_whitelion_amount = $Leadamount->whitelion_amount;
        		    $total_billing_amount = $Leadamount->billing_amount;
        		    $total_other_amount = $Leadamount->other_amount;
        		    $total_amount = $Leadamount->total_amount;
        		}
				$PridictionDealWlAmount = numCommaFormat($total_whitelion_amount);
        		$PridictionDealTotalAmount = numCommaFormat($total_amount);
        		$PridictionDealBillingAmount = numCommaFormat($total_billing_amount);
			}
			// LEAD & DEAL COUNT START
			$LeadTotalIds = array();
			$Lead = Lead::query();
			// $Lead->where('leads.is_deal', '=', 0);
			$Lead->whereDate('leads.created_at', '>=', $startDate);
			$Lead->whereDate('leads.created_at', '<=', $endDate);
			if ($isAdminOrCompanyAdmin == 1) {

				if (isset($req_user_id) && $req_user_id != "") {
					$Lead->whereIn('leads.assigned_to', $req_user_id_ex);
				}

			} else if ($isSalePerson == 1) {

				if (isset($req_user_id) && $req_user_id != "") {
					$Lead->whereIn('leads.assigned_to', $req_user_id_ex);
				} else {
					$Lead->whereIn('leads.assigned_to', $childSalePersonsIds);
				}

			}else if($isChannelPartner != 0){
				$Lead->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
				$Lead->where('lead_sources.source', Auth::user()->id);
			}
			if (isset($req_channel_partner_user_id) && $req_channel_partner_user_id != "") {
				$Lead->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
				$Lead->whereIn('lead_sources.source', $req_channel_partner_user_id_ex);
			}
			$LeadTotalCount = $Lead->distinct()->pluck('leads.id')->count();
			$LeadTotalIds = $Lead->distinct()->pluck('leads.id')->all();

			$DealConvertIds = array();
			$Lead = LeadTimeline::query();
			$Lead->leftJoin('leads', 'leads.id', '=', 'lead_timeline.lead_id');
			$Lead->where('lead_timeline.type', '=', 'convert-to-deal');
			$Lead->whereDate('lead_timeline.created_at', '>=', $startDate);
			$Lead->whereDate('lead_timeline.created_at', '<=', $endDate);
			if ($isAdminOrCompanyAdmin == 1) {

				if (isset($req_user_id) && $req_user_id != "") {
					$Lead->whereIn('leads.assigned_to', $req_user_id_ex);
				}

			} else if ($isSalePerson == 1) {

				if (isset($req_user_id) && $req_user_id != "") {
					$Lead->whereIn('leads.assigned_to', $req_user_id_ex);
				} else {
					$Lead->whereIn('leads.assigned_to', $childSalePersonsIds);
				}


			}else if($isChannelPartner != 0){
				$Lead->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
				$Lead->where('lead_sources.source', Auth::user()->id);
			}
			if (isset($req_channel_partner_user_id) && $req_channel_partner_user_id != "") {
				$Lead->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'lead_timeline.lead_id');
				$Lead->whereIn('lead_sources.source', $req_channel_partner_user_id_ex);
			}
			$DealConvertCount = $Lead->distinct()->pluck('lead_timeline.lead_id')->count();
			$DealConvertIds = $Lead->distinct()->pluck('lead_timeline.lead_id')->all();


			$LeadWonIds = array();
			$Lead = Lead::query();
			$Lead->leftJoin('lead_status_updates as lead_status_detail', function ($join) {
				$join->select('lead_status_detail.new_status');
				$join->on('lead_status_detail.lead_id', '=', 'leads.id');
				$join->where('lead_status_detail.new_status', 103);
				$join->orderBy('lead_status_detail.created_at', 'DESC');
				$join->limit(1);
			});
			$Lead->whereDate('lead_status_detail.created_at', '>=', $startDate);
			$Lead->whereDate('lead_status_detail.created_at', '<=', $endDate);
			if ($isAdminOrCompanyAdmin == 1) {

				if (isset($req_user_id) && $req_user_id != "") {
					$Lead->whereIn('leads.assigned_to', $req_user_id_ex);
				}
			} else if ($isSalePerson == 1) {

				if (isset($req_user_id) && $req_user_id != "") {
					$Lead->whereIn('leads.assigned_to', $req_user_id_ex);
				} else {
					$Lead->whereIn('leads.assigned_to', $childSalePersonsIds);
				}
			}else if($isChannelPartner != 0){
				$Lead->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
				$Lead->where('lead_sources.source', Auth::user()->id);
			}

			if (isset($req_channel_partner_user_id) && $req_channel_partner_user_id != "") {
				$Lead->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
				$Lead->whereIn('lead_sources.source', $req_channel_partner_user_id_ex);
			}
			$LeadWonCount = $Lead->distinct()->pluck('leads.id')->count();
			$LeadWonIds = $Lead->distinct()->pluck('leads.id')->all();
			
			$LeadLostIds = array();
			$Lead = Lead::query();
			$Lead->leftJoin('lead_status_updates as lead_status_detail', function ($join) {
				$join->select('lead_status_detail.new_status', 'lead_status_detail.created_at');
				$join->on('lead_status_detail.lead_id', '=', 'leads.id');
				$join->whereIn('lead_status_detail.new_status', array(5, 104));
				$join->orderBy('lead_status_detail.created_at', 'DESC');
				$join->limit(1);
			});
			$Lead->whereDate('lead_status_detail.created_at', '>=', $startDate);
			$Lead->whereDate('lead_status_detail.created_at', '<=', $endDate);
			if ($isAdminOrCompanyAdmin == 1) {

				if (isset($req_user_id) && $req_user_id != "") {
					$Lead->whereIn('leads.assigned_to', $req_user_id_ex);
				}

			} else if ($isSalePerson == 1) {

				if (isset($req_user_id) && $req_user_id != "") {
					$Lead->whereIn('leads.assigned_to', $req_user_id_ex);
				} else {
					$Lead->whereIn('leads.assigned_to', $childSalePersonsIds);
				}
			}else if($isChannelPartner != 0){
				$Lead->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
				$Lead->where('lead_sources.source', Auth::user()->id);
			}

			if (isset($req_channel_partner_user_id) && $req_channel_partner_user_id != "") {
				$Lead->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
				$Lead->whereIn('lead_sources.source', $req_channel_partner_user_id_ex);
			}
			$LeadLostCount = $Lead->distinct()->pluck('leads.id')->count();
			$LeadLostIds = $Lead->distinct()->pluck('leads.id')->all();

			$LeadColdIds = array();
			$Lead = Lead::query();
			$Lead->leftJoin('lead_status_updates as lead_status_detail', function ($join) {
				$join->select('lead_status_detail.new_status', 'lead_status_detail.created_at');
				$join->on('lead_status_detail.lead_id', '=', 'leads.id');
				$join->whereIn('lead_status_detail.new_status', array(6, 105));
				$join->orderBy('lead_status_detail.created_at', 'DESC');
				$join->limit(1);
			});
			$Lead->whereDate('lead_status_detail.created_at', '>=', $startDate);
			$Lead->whereDate('lead_status_detail.created_at', '<=', $endDate);
			if ($isAdminOrCompanyAdmin == 1) {

				if (isset($req_user_id) && $req_user_id != "") {
					$Lead->whereIn('leads.assigned_to', $req_user_id_ex);
				}
			} else if ($isSalePerson == 1) {

				if (isset($req_user_id) && $req_user_id != "") {
					$Lead->whereIn('leads.assigned_to', $req_user_id_ex);
				} else {
					$Lead->whereIn('leads.assigned_to', $childSalePersonsIds);
				}
			} else if($isChannelPartner != 0){
				$Lead->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
				$Lead->where('lead_sources.source', Auth::user()->id);
			}

			if (isset($req_channel_partner_user_id) && $req_channel_partner_user_id != "") {
				$Lead->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
				$Lead->whereIn('lead_sources.source', $req_channel_partner_user_id_ex);
			}
			$LeadColdCount = $Lead->distinct()->pluck('leads.id')->count();
			$LeadColdIds = $Lead->distinct()->pluck('leads.id')->all();

			$LeadRunningIds = array();
			$RunningLead = Lead::query();

			// if($req_is_first_load == '1' || $req_is_first_load == 1){
			// $RunningLead->whereDate('leads.created_at', '>=', $startDate);
			// $RunningLead->whereDate('leads.created_at', '<=', $endDate);
				$RunningLead->whereIn('status', array(1,2,3,4,100,101,102));
			// }else{
			// 	$RunningLead->leftJoin('lead_status_updates as lead_status_detail', function ($join) {
			// 		$join->select('lead_status_detail.new_status', 'lead_status_detail.created_at');
			// 		$join->on('lead_status_detail.lead_id', '=', 'leads.id');
			// 		$join->whereIn('lead_status_detail.new_status', array(2,3,4,100,101,102));
			// 		$join->orderBy('lead_status_detail.created_at', 'DESC');
			// 		$join->limit(1);
			// 	});
			// 	$RunningLead->whereDate('lead_status_detail.created_at', '>=', $startDate);
			// 	$RunningLead->whereDate('lead_status_detail.created_at', '<=', $endDate);
			// }
			
			if ($isAdminOrCompanyAdmin == 1) {
				if (isset($req_user_id) && $req_user_id != "") {
					$RunningLead->whereIn('leads.assigned_to', $req_user_id_ex);
				}
				if (isset($req_channel_partner_user_id) && $req_channel_partner_user_id != "") {
					$RunningLead->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
					$RunningLead->whereIn('lead_sources.source', $req_channel_partner_user_id_ex);
				}
			} elseif ($isSalePerson == 1) {
				if (isset($req_user_id) && $req_user_id != "") {
					$RunningLead->whereIn('leads.assigned_to', $req_user_id_ex);
				} else {
					$RunningLead->whereIn('leads.assigned_to', $childSalePersonsIds);
				}
				if (isset($req_channel_partner_user_id) && $req_channel_partner_user_id != "") {
					$RunningLead->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
					$RunningLead->whereIn('lead_sources.source', $req_channel_partner_user_id_ex);
				}
			} elseif($isChannelPartner != 0){
				$RunningLead->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
				$RunningLead->where('lead_sources.source', Auth::user()->id);
			}
			
			$LeadRunningCount = $RunningLead->distinct()->pluck('leads.id')->count();
			$LeadRunningIds = $RunningLead->distinct()->pluck('leads.id')->all();

			$ArchitectIds = array();
			$Architect = Architect::query();
			$Architect->whereDate('created_at', '>=', $startDate);
			$Architect->whereDate('created_at', '<=', $endDate);
			$Architect->whereIn('type', [201, 202]);
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
				$Architect->where('architect.added_by', Auth::user()->id);
			} 
			$ArchitectCount = $Architect->distinct()->pluck('id')->count();
			$ArchitectIds = $Architect->distinct()->pluck('id')->all();

			$ElectricianIds = array();
			$Electrician = Electrician::query();
			$Electrician->whereDate('created_at', '>=', $startDate);
			$Electrician->whereDate('created_at', '<=', $endDate);
			$Electrician->whereIn('type', [301, 302]);
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
				$Electrician->where('electrician.added_by', Auth::user()->id);
			} 
			$ElectricianCount = $Electrician->distinct()->pluck('id')->count();
			$ElectricianIds = $Electrician->distinct()->pluck('id')->all();
			
			// LEAD & DEAL COUNT END

			// SALES ORDER COUNT START
			$OrderTotalCount = 0;
			$OrderTotalIds = array();
			$OrderPlaceAmount = 0;
			$OrderPlaceIds = array();
			$OrderDispatchedAmount = 0;
			$OrderDispatchedIds = array();

			if ($isAdminOrCompanyAdmin == 1 || $isSalePerson == 1) {

				$hasFilter = 0;
				if (isset($req_channel_partner_user_id) && $req_channel_partner_user_id != "") {
					$hasFilter = 1;
				}
				if (isset($req_user_id) && $req_user_id != "") {
					$hasFilter = 1;
				}
				if ($req_channel_partner_type != 0 && $req_channel_partner_type == 101) {
					$hasFilter = 1;
				}
	
	
				$orderTotal = Order::query();
				$orderTotal->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
				$orderTotal->where('orders.status', '!=', 4);
				$orderTotal->whereDate('orders.created_at', '>=', $startDate);
				$orderTotal->whereDate('orders.created_at', '<=', $endDate);
				$orderTotal->orderBy('orders.id', 'desc');
				if ($req_channel_partner_type != 0) {
	
					$orderTotal->where('channel_partner.type', $req_channel_partner_type);
				}else{
					$orderTotal->where('channel_partner.type', '!=',104);
					$orderTotal->where('channel_partner.type', '!=',105);
				}
				if ($isAdminOrCompanyAdmin == 1) {
	
					if ($hasFilter == 0) {
	
						$orderTotal->where('channel_partner.reporting_manager_id', 0);
						$orderTotal->where('channel_partner.reporting_company_id', Auth::user()->company_id);
					}
	
				} else if ($isSalePerson == 1) {
	
					if ($hasFilter == 0) {
	
						$orderTotal->where(function ($query) use ($childSalePersonsIds) {
	
							foreach ($childSalePersonsIds as $key => $value) {
								if ($key == 0) {
									$query->whereRaw('FIND_IN_SET("' . $value . '",orders.sale_persons)>0');
								} else {
									$query->orWhereRaw('FIND_IN_SET("' . $value . '",orders.sale_persons)>0');
								}
							}
						});
					}
				}
				if (isset($req_channel_partner_user_id) && $req_channel_partner_user_id != "") {
	
					$orderTotal->whereIn('orders.channel_partner_user_id', $req_channel_partner_user_id_ex);
				}
				if (isset($req_user_id) && $req_user_id != "") {
	
					$salesUserIds = $req_user_id_ex;
					$allSalesUserIds = [];
	
					foreach ($salesUserIds as $key => $value) {
	
						$childSalePersonsIds1 = getChildSalePersonsIds($value);
	
						$allSalesUserIds = array_merge($allSalesUserIds, $childSalePersonsIds1);
					}
					$allSalesUserIds = array_unique($allSalesUserIds);
					$allSalesUserIds = array_values($allSalesUserIds);
	
					$orderTotal->whereIn('orders.sale_persons', $allSalesUserIds);
	
				}
				$OrderTotalCount = $orderTotal->distinct()->pluck('orders.id')->count();
				$OrderTotalIds = $orderTotal->distinct()->pluck('orders.id')->all();
	
				$orderTotalAmount = Order::query();
				$orderTotalAmount->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
				$orderTotalAmount->where('orders.status', '!=', 4);
				$orderTotalAmount->whereDate('orders.created_at', '>=', $startDate);
				$orderTotalAmount->whereDate('orders.created_at', '<=', $endDate);
				$orderTotalAmount->orderBy('orders.id', 'desc');
				if ($req_channel_partner_type != 0) {
					$orderTotalAmount->where('channel_partner.type', $req_channel_partner_type);
				}else{
					$orderTotalAmount->where('channel_partner.type', '!=',104);
					$orderTotalAmount->where('channel_partner.type', '!=',105);
				}
				if ($isAdminOrCompanyAdmin == 1) {
	
					if ($hasFilter == 0) {
	
						$orderTotalAmount->where('channel_partner.reporting_manager_id', 0);
						$orderTotalAmount->where('channel_partner.reporting_company_id', Auth::user()->company_id);
					}
	
	
				} else if ($isSalePerson == 1) {
	
					if ($hasFilter == 0) {
	
						$orderTotalAmount->whereIn('orders.sale_persons', $childSalePersonsIds);
					}
	
				}
	
				if (isset($req_channel_partner_user_id) && $req_channel_partner_user_id != "") {
	
					$orderTotalAmount->whereIn('orders.channel_partner_user_id', $req_channel_partner_user_id_ex);
	
				}
				
				if (isset($req_user_id) && $req_user_id != "") {
	
					$salesUserIds = $req_user_id_ex;
					$allSalesUserIds = [];
	
					foreach ($salesUserIds as $key => $value) {
	
						$childSalePersonsIds1 = getChildSalePersonsIds($value);
	
						$allSalesUserIds = array_merge($allSalesUserIds, $childSalePersonsIds1);
					}
					$allSalesUserIds = array_unique($allSalesUserIds);
					$allSalesUserIds = array_values($allSalesUserIds);
	
					$orderTotalAmount->whereIn('orders.sale_persons', $allSalesUserIds);
	
				}
				$OrderPlaceAmount = $orderTotalAmount->sum('total_mrp_minus_disocunt');
				$OrderPlaceIds = $orderTotalAmount->distinct()->pluck('orders.id')->all();
	
				$orderTotalAmountDispatched = Invoice::query();
				$orderTotalAmountDispatched->leftJoin('orders', 'orders.id', '=', 'invoice.order_id');
				$orderTotalAmountDispatched->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
				$orderTotalAmountDispatched->whereIn('invoice.status', array(2, 3));
				$orderTotalAmountDispatched->whereDate('orders.created_at', '>=', $startDate);
				$orderTotalAmountDispatched->whereDate('orders.created_at', '<=', $endDate);
				$orderTotalAmountDispatched->orderBy('orders.id', 'desc');
				if ($req_channel_partner_type != 0) {
					$orderTotalAmountDispatched->where('channel_partner.type', $req_channel_partner_type);
				}else{
					$orderTotalAmountDispatched->where('channel_partner.type', '!=',104);
					$orderTotalAmountDispatched->where('channel_partner.type', '!=',105);
				}
				if ($isAdminOrCompanyAdmin == 1) {
	
					if ($hasFilter == 0) {
	
						$orderTotalAmountDispatched->where('channel_partner.reporting_manager_id', 0);
						$orderTotalAmountDispatched->where('channel_partner.reporting_company_id', Auth::user()->company_id);
					}
				} else if ($isSalePerson == 1) {
	
					if ($hasFilter == 0) {
	
						$orderTotalAmountDispatched->whereIn('orders.sale_persons', $childSalePersonsIds);
					}
				}
				if (isset($req_channel_partner_user_id) && $req_channel_partner_user_id != "") {
	
					$orderTotalAmountDispatched->whereIn('orders.channel_partner_user_id', $req_channel_partner_user_id_ex);
				}
				if (isset($req_user_id) && $req_user_id != "") {
	
					$salesUserIds = $req_user_id_ex;
					$allSalesUserIds = [];
	
					foreach ($salesUserIds as $key => $value) {
	
						$childSalePersonsIds1 = getChildSalePersonsIds($value);
	
						$allSalesUserIds = array_merge($allSalesUserIds, $childSalePersonsIds1);
					}
					$allSalesUserIds = array_unique($allSalesUserIds);
					$allSalesUserIds = array_values($allSalesUserIds);
	
					$orderTotalAmountDispatched->whereIn('orders.sale_persons', $allSalesUserIds);
	
				}
				$OrderDispatchedAmount = $orderTotalAmountDispatched->sum('invoice.total_mrp_minus_disocunt');
				$OrderDispatchedIds = $orderTotalAmountDispatched->distinct()->pluck('orders.id')->all();
	
			} else if ($isChannelPartner != 0) {
	
				$orderTotal = Order::query();
				$orderTotal->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
				$orderTotal->where('orders.status', '!=', 4);
				$orderTotal->whereDate('orders.created_at', '>=', $startDate);
				$orderTotal->whereDate('orders.created_at', '<=', $endDate);
				$orderTotal->where('orders.channel_partner_user_id', Auth::user()->id);
				if ($req_channel_partner_type != 0) {
	
					$orderTotal->where('channel_partner.type', $req_channel_partner_type);
				}else{
					$orderTotal->where('channel_partner.type', '!=',104);
				}
				$OrderTotalCount = $orderTotal->distinct()->pluck('orders.id')->count();
				$OrderTotalIds = $orderTotal->distinct()->pluck('orders.id')->all();
	
				$orderTotalAmount = Order::query();
				$orderTotalAmount->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
				$orderTotalAmount->where('orders.status', '!=', 4);
				$orderTotalAmount->whereDate('orders.created_at', '>=', $startDate);
				$orderTotalAmount->whereDate('orders.created_at', '<=', $endDate);
				$orderTotalAmount->where('orders.channel_partner_user_id', Auth::user()->id);
				if ($req_channel_partner_type != 0) {
	
					$orderTotalAmount->where('channel_partner.type', $req_channel_partner_type);
				}else{
					$orderTotalAmount->where('channel_partner.type', '!=',104);
				}
				$OrderPlaceAmount = $orderTotalAmount->sum('actual_total_mrp_minus_disocunt');
				$OrderPlaceIds = $orderTotalAmount->distinct()->pluck('orders.id')->all();
	
				$orderTotalAmountDispatched = Invoice::query();
				$orderTotalAmountDispatched->leftJoin('orders', 'orders.id', '=', 'invoice.order_id');
				$orderTotalAmountDispatched->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
				$orderTotalAmountDispatched->whereIn('invoice.status', array(2, 3));
				$orderTotalAmountDispatched->whereDate('orders.created_at', '>=', $startDate);
				$orderTotalAmountDispatched->whereDate('orders.created_at', '<=', $endDate);
				$orderTotalAmountDispatched->where('orders.channel_partner_user_id', Auth::user()->id);
				if ($req_channel_partner_type != 0) {
	
					$orderTotalAmountDispatched->where('channel_partner.type', $req_channel_partner_type);
				}else{
					$orderTotalAmountDispatched->where('channel_partner.type', '!=',104);
				}
				$OrderDispatchedAmount = $orderTotalAmountDispatched->sum('invoice.total_mrp_minus_disocunt');
				$OrderDispatchedIds = $orderTotalAmountDispatched->distinct()->pluck('orders.id')->all();
			}

			$RewardArchitect = GiftProductOrder::query();
			$RewardArchitect->leftJoin('users', 'users.id', '=', 'gift_product_orders.user_id');
			$RewardArchitect->whereDate('gift_product_orders.created_at', '>=', $startDate);
			$RewardArchitect->whereDate('gift_product_orders.created_at', '<=', $endDate);
			$RewardArchitect->where('gift_product_orders.cash_status', 1);
			if ($isSalePerson == 1) {
				$RewardArchitect->leftJoin('architect', 'architect.user_id', '=', 'gift_product_orders.user_id');
				$RewardArchitect->whereIn('architect.sale_person_id', $childSalePersonsIds);
			}
			$RewardArchitect->where('users.type', 202);
			$RewardArchitectCount = $RewardArchitect->distinct()->pluck('gift_product_orders.id')->count();
			$RewardArchitectIds = $RewardArchitect->distinct()->pluck('gift_product_orders.id')->all();
			
			$RewardElectrician = GiftProductOrder::query();
			$RewardElectrician->leftJoin('users', 'users.id', '=', 'gift_product_orders.user_id');
			$RewardElectrician->whereDate('gift_product_orders.created_at', '>=', $startDate);
			$RewardElectrician->whereDate('gift_product_orders.created_at', '<=', $endDate);
			$RewardElectrician->where('gift_product_orders.cash_status', 1);
			if ($isSalePerson == 1) {
				$RewardElectrician->leftJoin('electrician', 'electrician.user_id', '=', 'gift_product_orders.user_id');
				$RewardElectrician->whereIn('electrician.sale_person_id', $childSalePersonsIds);
			}
			$RewardElectrician->where('users.type', 302);
			$RewardElectricianCount = $RewardElectrician->distinct()->pluck('gift_product_orders.id')->count();
			$RewardElectricianIds = $RewardElectrician->distinct()->pluck('gift_product_orders.id')->all();

			// NEW OFFLINE LEAD COUNT START
			$LeadOfflineIds = array();
			if($isAccountUser == 1){
				$LeadOfflineCount = 0;
			} else {
				$LeadOff = Lead::query();
				$LeadOff->whereNotIn('leads.source_type', ['user-4', 'textnotrequired-1', 'textnotrequired-11', 'textnotrequired-12']);

				$LeadOff->whereDate('leads.created_at', '>=', $startDate);
				$LeadOff->whereDate('leads.created_at', '<=', $endDate);
				if ($isAdminOrCompanyAdmin == 1) {

					if (isset($req_user_id) && $req_user_id != "") {
						$LeadOff->whereIn('leads.assigned_to', $req_user_id);
					}

				} else if ($isSalePerson == 1) {

					if (isset($req_user_id) && $req_user_id != "") {
						$LeadOff->whereIn('leads.assigned_to', $req_user_id);
					} else {
						$LeadOff->whereIn('leads.assigned_to', $childSalePersonsIds);
					}

				} else if($isChannelPartner != 0){
					$LeadOff->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
					$LeadOff->where('lead_sources.source', Auth::user()->id);
				}
				if (isset($req_channel_partner_user_id) && $req_channel_partner_user_id != "") {
					$LeadOff->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
					$LeadOff->whereIn('lead_sources.source', $req_channel_partner_user_id_ex);
				}

				if(isset($req_state_id) && $req_state_id != "") {
					$LeadOff->leftJoin('city_list', 'city_list.id', '=', 'leads.city_id');
					$LeadOff->where('city_list.state_id', $req_state_id);
				}

				if(isset($req_city_id) && $req_city_id != "") {
					$LeadOff->where('leads.city_id', $req_city_id);
				}

				// Get the count and ids
				$LeadOfflineCount = $LeadOff->distinct()->pluck('leads.id')->count();
				$LeadOfflineIds = $LeadOff->distinct()->pluck('leads.id')->all();
			}		
			// NEW OFFLINE COUNT END

			// NEW MARKETING LEAD COUNT START
			$LeadMarketingIds = array();
			if($isAccountUser == 1){
				$LeadMarketingCount = 0;
			} else {
				$LeadMar = Lead::query();
				$LeadOff->whereIn('leads.source_type', ['user-4', 'textnotrequired-1', 'textnotrequired-11', 'textnotrequired-12']);
				$LeadMar->whereDate('leads.created_at', '>=', $startDate);
				$LeadMar->whereDate('leads.created_at', '<=', $endDate);
				if ($isAdminOrCompanyAdmin == 1) {

					if (isset($req_user_id) && $req_user_id != "") {
						$LeadMar->whereIn('leads.assigned_to', $req_user_id);
					}

				} else if ($isSalePerson == 1) {

					if (isset($req_user_id) && $req_user_id != "") {
						$LeadMar->whereIn('leads.assigned_to', $req_user_id);
					} else {
						$LeadMar->whereIn('leads.assigned_to', $childSalePersonsIds);
					}

				} else if($isChannelPartner != 0){
					$LeadMar->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
					$LeadMar->where('lead_sources.source', Auth::user()->id);
				}
				if (isset($req_channel_partner_user_id) && $req_channel_partner_user_id != "") {
					$LeadMar->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
					$LeadMar->whereIn('lead_sources.source', $req_channel_partner_user_id_ex);
				}

				if(isset($req_state_id) && $req_state_id != "") {
					$LeadMar->leftJoin('city_list', 'city_list.id', '=', 'leads.city_id');
					$LeadMar->where('city_list.state_id', $req_state_id);
				}

				if(isset($req_city_id) && $req_city_id != "") {
					$LeadMar->where('leads.city_id', $req_city_id);
				}

				$LeadMarketingCount = $LeadMar->distinct()->pluck('leads.id')->count();
				$LeadMarketingIds = $LeadMar->distinct()->pluck('leads.id')->all();
			}
			// NEW MARKETING LEAD COUNT END

			// DEMO MEETING DONE COUNT START
			$LeadDemoMeetingDoneIds = array();
			if($isAccountUser == 1){
				$LeadDemoMeetingDoneCount = 0;
			} else {
				$LeadDemo = Lead::query();
				$LeadDemo->where('leads.status', 4);
				$LeadDemo->whereDate('leads.created_at', '>=', $startDate);
				$LeadDemo->whereDate('leads.created_at', '<=', $endDate);
				if ($isAdminOrCompanyAdmin == 1) {

					if (isset($req_user_id) && $req_user_id != "") {
						$LeadDemo->whereIn('leads.assigned_to', $req_user_id);
					}

				} else if ($isSalePerson == 1) {

					if (isset($req_user_id) && $req_user_id != "") {
						$LeadDemo->whereIn('leads.assigned_to', $req_user_id);
					} else {
						$LeadDemo->whereIn('leads.assigned_to', $childSalePersonsIds);
					}

				} else if($isChannelPartner != 0){
					$LeadDemo->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
					$LeadDemo->where('lead_sources.source', Auth::user()->id);
				}
				if (isset($req_channel_partner_user_id) && $req_channel_partner_user_id != "") {
					$LeadDemo->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
					$LeadDemo->whereIn('lead_sources.source', $req_channel_partner_user_id_ex);
				}

				if(isset($req_state_id) && $req_state_id != "") {
					$LeadDemo->leftJoin('city_list', 'city_list.id', '=', 'leads.city_id');
					$LeadDemo->where('city_list.state_id', $req_state_id);
				}

				if(isset($req_city_id) && $req_city_id != "") {
					$LeadDemo->where('leads.city_id', $req_city_id);
				}
				// $Lead->where('leads.is_deal', '=', 0);
				$LeadDemoMeetingDoneCount = $LeadDemo->distinct()->pluck('leads.id')->count();
				$LeadDemoMeetingDoneIds = $LeadDemo->distinct()->pluck('leads.id')->all();
			}
			// DEMO MEETING DONE COUNT END


			$response = successRes("Success");
			$response['pridiction']['ids'] = implode(',', $PridictionDealIds);
			$response['pridiction']['value'] = $PridictionDealCount;
			$response['pridiction']['wl_amount'] = $PridictionDealWlAmount;
			$response['pridiction']['total_amount'] = $PridictionDealTotalAmount;
			$response['pridiction']['billing_amount'] = $PridictionDealBillingAmount;
			$response['pridiction']['data_type'] = 'PRIDICTION';
			
			$response['total_lead']['ids'] = implode(',', $LeadTotalIds);
			$response['total_lead']['value'] = $LeadTotalCount;
			$response['total_lead']['data_type'] = 'LEAD';

			$response['deal_conversition']['ids'] = implode(',', $DealConvertIds);
			$response['deal_conversition']['value'] = $DealConvertCount;
			$response['deal_conversition']['data_type'] = 'DEALCONVERSION';

			$response['won_lead']['ids'] = implode(',', $LeadWonIds);
			$response['won_lead']['value'] = $LeadWonCount;
			$response['won_lead']['data_type'] = 'WON';

			$response['lost_lead']['ids'] = implode(',', $LeadLostIds);
			$response['lost_lead']['value'] = $LeadLostCount;
			$response['lost_lead']['data_type'] = 'LOST';

			$response['cold_lead']['ids'] = implode(',', $LeadColdIds);
			$response['cold_lead']['value'] = $LeadColdCount;
			$response['cold_lead']['data_type'] = 'COLD';

			$response['runing_lead']['ids'] = implode(',', $LeadRunningIds);
			$response['runing_lead']['value'] = $LeadRunningCount;
			$response['runing_lead']['data_type'] = 'RUNNING';

			if(isset($req_channel_partner_user_id) && count($req_channel_partner_user_id_ex) > 0) {
				$response['architects']['ids'] = '';
				$response['architects']['value'] = '-';
				$response['architects']['data_type'] = 'ARCHITECT';

				$response['electricians']['ids'] = '';
				$response['electricians']['value'] = '-';
				$response['electricians']['data_type'] = 'ELECTRICIAN';

				$response['architects_reward']['ids'] = '';
				$response['architects_reward']['value'] = '-';
				$response['architects_reward']['data_type'] = 'REWARD_ARCHITECT';
				
				$response['electricians_reward']['ids'] = '';
				$response['electricians_reward']['value'] = '-';
				$response['electricians_reward']['data_type'] = 'REWARD_ELECTRICIAN';

				$response['target']['ids'] = '';
				$response['target']['value'] = '-';
				$response['target']['data_type'] = 'TARGET';
			} else {
				$response['architects']['ids'] = implode(',', $ArchitectIds);
				$response['architects']['value'] = $ArchitectCount;
				$response['architects']['data_type'] = 'ARCHITECT';
				
				$response['electricians']['ids'] = implode(',', $ElectricianIds);
				$response['electricians']['value'] = $ElectricianCount;
				$response['electricians']['data_type'] = 'ELECTRICIAN';
				
				$response['architects_reward']['ids'] = implode(',', $RewardArchitectIds);
				$response['architects_reward']['value'] = $RewardArchitectCount;
				$response['architects_reward']['data_type'] = 'REWARD_ARCHITECT';
				
				$response['electricians_reward']['ids'] = implode(',', $RewardElectricianIds);
				$response['electricians_reward']['value'] = $RewardElectricianCount;
				$response['electricians_reward']['data_type'] = 'REWARD_ELECTRICIAN';

				$response['target']['ids'] = implode(',', $TargetIds);
				$response['target']['value'] = numCommaFormat(ceil($TargetAmount));
				$response['target']['data_type'] = 'TARGET';
			}

			$LeadConversionRatio = 0.00;
			$totalLeadDealCount = $LeadWonCount + $LeadLostCount + $LeadColdCount;
			if ($totalLeadDealCount != 0) {
				$LeadConversionRatio = round((($LeadWonCount * 100) / ($totalLeadDealCount)), 2);
			}
			$response['conversion_ratio'] = $LeadConversionRatio;

			$response['order']['ids'] = implode(',', $OrderTotalIds);
			$response['order']['value'] = $OrderTotalCount;
			$response['order']['data_type'] = 'ORDER';

			$response['order_place']['ids'] = implode(',', $OrderPlaceIds);
			$response['order_place']['value'] = numCommaFormat(ceil($OrderPlaceAmount));
			$response['order_place']['data_type'] = 'PLACED';

			$response['order_dispateched']['ids'] = implode(',', $OrderDispatchedIds);
			$response['order_dispateched']['value'] = numCommaFormat(ceil($OrderDispatchedAmount));
			$response['order_dispateched']['data_type'] = 'DISPATCHED';

			$response['New_Offline_Lead']['ids'] = implode(',', $LeadOfflineIds);
			$response['New_Offline_Lead']['value'] = $LeadOfflineCount;
			$response['New_Offline_Lead']['data_type'] = 'OFFLINELEAD';

			$response['New_Marketing_Lead']['ids'] = implode(',', $LeadMarketingIds);
			$response['New_Marketing_Lead']['value'] = $LeadMarketingCount;
			$response['New_Marketing_Lead']['data_type'] = 'MARKETINGLEAD';

			$response['demo_meeting_done']['ids'] = implode(',', $LeadDemoMeetingDoneIds);
			$response['demo_meeting_done']['value'] = $LeadDemoMeetingDoneCount;
			$response['demo_meeting_done']['data_type'] = 'DEMOMEETINGDONELEAD';
		}
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function searchChannelPartner(Request $request)
	{

		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		$isSalePerson = isSalePerson();
		$isChannelPartner = isChannelPartner(Auth::user()->type);
		if ($isAdminOrCompanyAdmin == 1 || $isSalePerson == 1) {

			$ChannelPartner = array();
			$ChannelPartner = ChannelPartner::select('channel_partner.user_id as id', DB::raw('CONCAT(channel_partner.firm_name) as text'));
			$ChannelPartner->leftJoin('users', 'users.id', '=', 'channel_partner.user_id');
			$ChannelPartner->whereIn('channel_partner.type', array(101, 102, 103, 104, 105));
			$ChannelPartner->where('users.status', 1);
			if ($request->type != 0) {

				$ChannelPartner->where('channel_partner.type', $request->type);
			}

			$q = $request->q;

			if ($isSalePerson == 1) {

				$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);

				$ChannelPartner->where(function ($query) use ($childSalePersonsIds) {

					foreach ($childSalePersonsIds as $key => $value) {
						if ($key == 0) {
							$query->whereRaw('FIND_IN_SET("' . $value . '",channel_partner.sale_persons)>0');
						} else {
							$query->orWhereRaw('FIND_IN_SET("' . $value . '",channel_partner.sale_persons)>0');
						}
					}
				});
			}

			$ChannelPartner->where(function ($query) use ($q) {
				$query->where('channel_partner.firm_name', 'like', '%' . $q . '%');
			});

			$ChannelPartner->limit(10);
			$ChannelPartner = $ChannelPartner->get();

			$response = successRes("Channel Partner");
			$response['data'] = $ChannelPartner;
			return response()->json($response)->header('Content-Type', 'application/json');
		}
	}

	public function searchUser(Request $request)
	{

		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		$isSalePerson = isSalePerson();
		$isChannelPartner = isChannelPartner(Auth::user()->type);
		if ($isAdminOrCompanyAdmin == 1 || $isSalePerson == 1) {

			if ($isSalePerson == 1) {
				$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
			}

			$User = $UserResponse = array();
			$q = $request->q;
			$User = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
			$User->where('users.type', 2);
			$User->where('users.status', 1);
			if ($isSalePerson == 1) {
				$User->whereIn('id', $childSalePersonsIds);
			}
			$User->where(function ($query) use ($q) {
				$query->where('users.first_name', 'like', '%' . $q . '%');
				$query->orWhere('users.last_name', 'like', '%' . $q . '%');
			});
			$User->limit(5);
			$User = $User->get();

			if (count($User) > 0) {
				foreach ($User as $User_key => $User_value) {
					$UserResponse[$User_key]['id'] = $User_value['id'];
					$UserResponse[$User_key]['text'] = $User_value['full_name'];
				}
			}
			$response = successRes("Channel Partner");
			$response['data'] = $UserResponse;
			return response()->json($response)->header('Content-Type', 'application/json');
		}
	}


	public function searchChannelPartnerTypes()
	{

		$data = array();
		$newdata['id'] = 0;
		$newdata['text'] = 'All';
		array_push($data, $newdata);
		foreach (getChannelPartners() as $key => $value) {
			$newdata['id'] = $value['id'];
			$newdata['text'] = $value['short_name'];
			array_push($data, $newdata);
		}
		$response = successRes("Get Channel Partner Type");
		$response['data'] = $data;
		return response()->json($response, $response['status_code'])->header('Content-Type', 'application/json');
	}

}
