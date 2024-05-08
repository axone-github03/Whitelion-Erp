<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Architect;
use App\Models\ChannelPartner;
use App\Models\Electrician;
use App\Models\Wltrn_QuotItemdetail;
use App\Models\Order;
use App\Models\Wlmst_target;
use App\Models\GiftProductOrder;
use App\Models\User;
use App\Models\Lead;
use App\Models\LeadTimeline;
use App\Models\Invoice;
use App\Models\SalePerson as ModelsSalePerson;
use App\Models\Wltrn_Quotation;
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
use SalePerson;

class DashboardCountController extends Controller
{

	public function __construct()
	{
		$this->middleware(function ($request, $next) {

			$tabCanAccessBy = array(0, 1, 2, 3, 101, 102, 103, 104, 105);

			if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
				return redirect()->route('dashboard');
			}

			$MyPrivilege = getMyPrivilege('dashboard');
			if ($MyPrivilege == 0) {
				return redirect()->route('dashboard');
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
			$req_channel_partner_type = $request->channel_partner_type;
			$req_is_first_load = $request->is_first_load;
			$req_state_id = $request->state_id;
			$req_city_id = $request->city_id;

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
			if($isChannelPartner != 0 || $isAccountUser == 1){
				$TargetAmount = 0;
			} else{
				$Target = Wlmst_target::query();
				$Target->leftJoin('wlmst_targetdetail', 'wlmst_targetdetail.target_id', '=', 'wlmst_target.id');
				$Target->leftJoin('users', 'users.id', '=', 'wlmst_target.employeee_id');
				$Target->leftJoin('wlmst_financialyear', 'wlmst_financialyear.id', '=', 'wlmst_target.finyear_id');
				$Target->where('users.status', 1);
				// $Target->where('wlmst_targetdetail.quater', getQuaterFromMonth(date('m', strtotime($request->start_date))));
				if (date('m', strtotime($req_startdate)) > 3) {
					$financialyear = date('Y', strtotime($req_startdate)) . "-" . (date('Y', strtotime($req_startdate)) + 1);
				} else {
					$financialyear = (date('Y', strtotime($req_startdate)) - 1) . "-" . date('Y', strtotime($req_startdate));
				}
				$Target->where('wlmst_financialyear.name', $financialyear);
				$Target->whereIn('wlmst_targetdetail.month_number', $monthNumbers);
				if ($isAdminOrCompanyAdmin == 1) {
					if (isset($req_user_id) && $req_user_id != "") {
						$Target->whereIn('wlmst_target.employeee_id', $req_user_id);
					}
				} else if ($isSalePerson == 1) {
					if (isset($req_user_id) && $req_user_id != "" ) {
						$Target->whereIn('wlmst_target.employeee_id', $req_user_id);
					} else {
						$Target->where('wlmst_target.employeee_id', Auth::user()->id);
					}
				}

				if(isset($req_state_id) && $req_state_id != "") {
					$Target->where('users.state_id', $req_state_id);
				}

				if(isset($req_city_id) && $req_city_id != "") {
					$Target->where('users.city_id', $req_city_id);
				}

				$TargetAmount = $Target->sum('wlmst_targetdetail.target_amount');
				$TargetIds = $Target->distinct()->pluck('wlmst_target.id')->all();
			}
			// $Target->whereYear('wlmst_target.created_at', date('Y'));
			// TARGET AMOUNT END

			// PRIDICTION COUNT START
			$PridictionDealIds = array();
			if($isChannelPartner != 0 || $isAccountUser == 1){
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
						$PridictionDeal->whereIn('leads.assigned_to', $req_user_id);
					}
				} else if ($isSalePerson == 1) {
					if (isset($req_user_id) && $req_user_id != "") {
						$PridictionDeal->whereIn('leads.assigned_to', $req_user_id);
					} else {
						$PridictionDeal->whereIn('leads.assigned_to', $childSalePersonsIds);
					}
				}
				if (isset($req_channel_partner_user_id) && is_array($req_channel_partner_user_id)) {
					$PridictionDeal->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
					$PridictionDeal->whereIn('lead_sources.source', $req_channel_partner_user_id);
				}

				if(isset($req_state_id) && $req_state_id != "") {
					$PridictionDeal->leftJoin('city_list', 'city_list.id', '=', 'leads.city_id');
					$PridictionDeal->where('city_list.state_id', $req_state_id);
				}

				if(isset($req_city_id) && $req_city_id != "") {
					$PridictionDeal->where('leads.city_id', $req_city_id);
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
			if($isAccountUser == 1){
				$LeadTotalCount = 0;
			} else {
				$Lead = Lead::query();
				// $Lead->where('leads.is_deal', '=', 0);
				$Lead->whereDate('leads.created_at', '>=', $startDate);
				$Lead->whereDate('leads.created_at', '<=', $endDate);
				if ($isAdminOrCompanyAdmin == 1) {

					if (isset($req_user_id) && $req_user_id != "") {
						$Lead->whereIn('leads.assigned_to', $req_user_id);
					}

				} else if ($isSalePerson == 1) {

					if (isset($req_user_id) && $req_user_id != "") {
						$Lead->whereIn('leads.assigned_to', $req_user_id);
					} else {
						$Lead->whereIn('leads.assigned_to', $childSalePersonsIds);
					}

				} else if($isChannelPartner != 0){
					$Lead->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
					$Lead->where('lead_sources.source', Auth::user()->id);
				}
				if (isset($req_channel_partner_user_id) && is_array($req_channel_partner_user_id)) {
					$Lead->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
					$Lead->whereIn('lead_sources.source', $req_channel_partner_user_id);
				}

				if(isset($req_state_id) && $req_state_id != "") {
					$Lead->leftJoin('city_list', 'city_list.id', '=', 'leads.city_id');
					$Lead->where('city_list.state_id', $req_state_id);
				}

				if(isset($req_city_id) && $req_city_id != "") {
					$Lead->where('leads.city_id', $req_city_id);
				}

				$LeadTotalCount = $Lead->distinct()->pluck('leads.id')->count();
				$LeadTotalIds = $Lead->distinct()->pluck('leads.id')->all();
			}
			

			$DealConvertIds = array();
			if($isAccountUser == 1){
				$DealConvertCount = 0;
			} else {
				$Lead = LeadTimeline::query();
				$Lead->leftJoin('leads', 'leads.id', '=', 'lead_timeline.lead_id');
				$Lead->where('lead_timeline.type', '=', 'convert-to-deal');
				$Lead->whereDate('lead_timeline.created_at', '>=', $startDate);
				$Lead->whereDate('lead_timeline.created_at', '<=', $endDate);
				if ($isAdminOrCompanyAdmin == 1) {

					if (isset($req_user_id) && $req_user_id != "") {
						$Lead->whereIn('leads.assigned_to', $req_user_id);
					}

				} else if ($isSalePerson == 1) {

					if (isset($req_user_id) && $req_user_id != "") {
						$Lead->whereIn('leads.assigned_to', $req_user_id);
					} else {
						$Lead->whereIn('leads.assigned_to', $childSalePersonsIds);
					}


				} else if($isChannelPartner != 0){
					$Lead->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
					$Lead->where('lead_sources.source', Auth::user()->id);
				}
				if (isset($req_channel_partner_user_id) && is_array($req_channel_partner_user_id)) {
					$Lead->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'lead_timeline.lead_id');
					$Lead->whereIn('lead_sources.source', $req_channel_partner_user_id);
				}

				if(isset($req_state_id) && $req_state_id != "") {
					$Lead->leftJoin('city_list', 'city_list.id', '=', 'leads.city_id');
					$Lead->where('city_list.state_id', $req_state_id);
				}

				if(isset($req_city_id) && $req_city_id != "") {
					$Lead->where('leads.city_id', $req_city_id);
				}

				$DealConvertCount = $Lead->distinct()->pluck('lead_timeline.lead_id')->count();
				$DealConvertIds = $Lead->distinct()->pluck('lead_timeline.lead_id')->all();
			}


			$LeadWonIds = array();
			if($isAccountUser == 1){
				$LeadWonCount = 0;
			} else {
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
						$Lead->whereIn('leads.assigned_to', $req_user_id);
					}
				} else if ($isSalePerson == 1) {

					if (isset($req_user_id) && $req_user_id != "") {
						$Lead->whereIn('leads.assigned_to', $req_user_id);
					} else {
						$Lead->whereIn('leads.assigned_to', $childSalePersonsIds);
					}
				}else if($isChannelPartner != 0){
					$Lead->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
					$Lead->where('lead_sources.source', Auth::user()->id);
				}
				if (isset($req_channel_partner_user_id) && is_array($req_channel_partner_user_id)) {
					$Lead->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
					$Lead->whereIn('lead_sources.source', $req_channel_partner_user_id);
				}

				if(isset($req_state_id) && $req_state_id != "") {
					$Lead->leftJoin('city_list', 'city_list.id', '=', 'leads.city_id');
					$Lead->where('city_list.state_id', $req_state_id);
				}

				if(isset($req_city_id) && $req_city_id != "") {
					$Lead->where('leads.city_id', $req_city_id);
				}

				$LeadWonCount = $Lead->distinct()->pluck('leads.id')->count();
				$LeadWonIds = $Lead->distinct()->pluck('leads.id')->all();
			}

			$LeadLostIds = array();
			if($isAccountUser == 1){
				$LeadLostCount = 0;
			} else {
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
						$Lead->whereIn('leads.assigned_to', $req_user_id);
					}

				} else if ($isSalePerson == 1) {

					if (isset($req_user_id) && $req_user_id != "") {
						$Lead->whereIn('leads.assigned_to', $req_user_id);
					} else {
						$Lead->whereIn('leads.assigned_to', $childSalePersonsIds);
					}
				}else if($isChannelPartner != 0){
					$Lead->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
					$Lead->where('lead_sources.source', Auth::user()->id);
				}
				if (isset($req_channel_partner_user_id) && is_array($req_channel_partner_user_id)) {
					$Lead->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
					$Lead->whereIn('lead_sources.source', $req_channel_partner_user_id);
				}
				if(isset($req_state_id) && $req_state_id != "") {
					$Lead->leftJoin('city_list', 'city_list.id', '=', 'leads.city_id');
					$Lead->where('city_list.state_id', $req_state_id);
				}

				if(isset($req_city_id) && $req_city_id != "") {
					$Lead->where('leads.city_id', $req_city_id);
				}

				$LeadLostCount = $Lead->distinct()->pluck('leads.id')->count();
				$LeadLostIds = $Lead->distinct()->pluck('leads.id')->all();
			}

			$LeadColdIds = array();
			if($isAccountUser == 1){
				$LeadColdCount = 0;
			} else {
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
						$Lead->whereIn('leads.assigned_to', $req_user_id);
					}
				} else if ($isSalePerson == 1) {
					if (isset($req_user_id) && $req_user_id != "") {
						$Lead->whereIn('leads.assigned_to', $req_user_id);
					} else {
						$Lead->whereIn('leads.assigned_to', $childSalePersonsIds);
					}
				}else if($isChannelPartner != 0){
					$Lead->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
					$Lead->where('lead_sources.source', Auth::user()->id);
				}
				if (isset($req_channel_partner_user_id) && is_array($req_channel_partner_user_id)) {
					$Lead->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
					$Lead->whereIn('lead_sources.source', $req_channel_partner_user_id);
				}
				if(isset($req_state_id) && $req_state_id != "") {
					$Lead->leftJoin('city_list', 'city_list.id', '=', 'leads.city_id');
					$Lead->where('city_list.state_id', $req_state_id);
				}

				if(isset($req_city_id) && $req_city_id != "") {
					$Lead->where('leads.city_id', $req_city_id);
				}
				$LeadColdCount = $Lead->distinct()->pluck('leads.id')->count();
				$LeadColdIds = $Lead->distinct()->pluck('leads.id')->all();
			}

			$LeadRunningIds = array();
			if($isAccountUser == 1){
				$LeadRunningCount = 0;
			} else {
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
					if (isset($req_user_id) && is_array($req_user_id)) {
						$RunningLead->whereIn('leads.assigned_to', $req_user_id);
					}
					if (isset($req_channel_partner_user_id) && is_array($req_channel_partner_user_id)) {
						$RunningLead->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
						$RunningLead->whereIn('lead_sources.source', $req_channel_partner_user_id);
					}
				} elseif ($isSalePerson == 1) {
					if (isset($req_user_id) && is_array($req_user_id)) {
						$RunningLead->whereIn('leads.assigned_to', $req_user_id);
					} else {
						$RunningLead->whereIn('leads.assigned_to', $childSalePersonsIds);
					}
					if (isset($req_channel_partner_user_id) && is_array($req_channel_partner_user_id)) {
						$RunningLead->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
						$RunningLead->whereIn('lead_sources.source', $req_channel_partner_user_id);
					}
				} elseif($isChannelPartner != 0){
					$RunningLead->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
					$RunningLead->where('lead_sources.source', Auth::user()->id);
				}
				if(isset($req_state_id) && $req_state_id != "") {
					$Lead->leftJoin('city_list', 'city_list.id', '=', 'leads.city_id');
					$Lead->where('city_list.state_id', $req_state_id);
				}

				if(isset($req_city_id) && $req_city_id != "") {
					$Lead->where('leads.city_id', $req_city_id);
				}
				$LeadRunningCount = $RunningLead->distinct()->pluck('leads.id')->count();
				$LeadRunningIds = $RunningLead->distinct()->pluck('leads.id')->all();
			}
			// LEAD & DEAL COUNT END

			// ARCHITECT COUNT START
			$ArchitectIds = array();
			if($isAccountUser == 1){
				$ArchitectCount = 0;
			} else {
				$Architect = Architect::query();
				$Architect->whereIn('architect.type', [201, 202]);
				if ($isAdminOrCompanyAdmin == 1) {

					if (isset($req_user_id) && $req_user_id != "") {
						$Architect->whereIn('architect.sale_person_id', $req_user_id);
					}
				} else if ($isSalePerson == 1) {

					if (isset($req_user_id) && $req_user_id != "") {
						$Architect->whereIn('architect.sale_person_id', $req_user_id);
					} else {
						$Architect->whereIn('architect.sale_person_id', $childSalePersonsIds);
					}
				} else if ($isChannelPartner != 0) {
					$Architect->where('architect.added_by', Auth::user()->id);
				} 

				if(isset($req_state_id) && $req_state_id != "") {
					$Architect->leftJoin('users', 'users.id', '=', 'architect.user_id');
					$Architect->where('users.state_id', $req_state_id);
				}

				if(isset($req_city_id) && $req_city_id != "") {
					// $Architect->leftJoin('users', 'users.id', '=', 'architect.user_id');
					$Architect->where('users.city_id', $req_city_id);
				}

				$ArchitectCount = $Architect->distinct()->pluck('architect.id')->count();
				$ArchitectIds = $Architect->distinct()->pluck('architect.id')->all();
			}

			$NewArchitectIds = array();
			if($isAccountUser == 1){
				$NewArchitectCount = 0;
			} else {
				$NewArchitect = Architect::query();
				// $NewArchitect->whereYear('architect.created_at', date('Y'));
    			// $NewArchitect->whereMonth('architect.created_at', date('m'));
				$NewArchitect->whereDate('architect.created_at', '>=', $startDate);
				$NewArchitect->whereDate('architect.created_at', '<=', $endDate);
				$NewArchitect->whereIn('architect.type', [201, 202]);
				if ($isAdminOrCompanyAdmin == 1) {
					if (isset($req_user_id) && $req_user_id != "") {
						$NewArchitect->whereIn('architect.sale_person_id', $req_user_id);
					}
				} else if ($isSalePerson == 1) {
					if (isset($req_user_id) && $req_user_id != "") {
						$NewArchitect->whereIn('architect.sale_person_id', $req_user_id);
					} else {
						$NewArchitect->whereIn('architect.sale_person_id', $childSalePersonsIds);
					}
				} else if ($isChannelPartner != 0) {
					$NewArchitect->where('architect.added_by', Auth::user()->id);
				} 
				if(isset($req_state_id) && $req_state_id != "") {
					$NewArchitect->leftJoin('users', 'users.id', '=', 'architect.user_id');
					$NewArchitect->where('users.state_id', $req_state_id);
				}

				if(isset($req_city_id) && $req_city_id != "") {
					// $NewArchitect->leftJoin('users', 'users.id', '=', 'architect.user_id');
					$NewArchitect->where('users.city_id', $req_city_id);
				}
				$NewArchitectCount = $NewArchitect->distinct()->pluck('architect.id')->count();
				$NewArchitectIds = $NewArchitect->distinct()->pluck('architect.id')->all();
			}

			$ActiveArchitectIds = array();
			if($isAccountUser == 1){
				$ActiveArchitectCount = 0;
			} else {
				$ActiveArchitect = Architect::query();
				$ActiveArchitect->leftJoin('users', 'users.id', '=', 'architect.id');
				$ActiveArchitect->where('users.status', 1);
				$ActiveArchitect->whereIn('architect.type', [201, 202]);
				if ($isAdminOrCompanyAdmin == 1) {
					if (isset($req_user_id) && $req_user_id != "") {
						$ActiveArchitect->whereIn('architect.sale_person_id', $req_user_id);
					}
				} else if ($isSalePerson == 1) {

					if (isset($req_user_id) && $req_user_id != "") {
						$ActiveArchitect->whereIn('architect.sale_person_id', $req_user_id);
					} else {
						$ActiveArchitect->whereIn('architect.sale_person_id', $childSalePersonsIds);
					}
				} else if ($isChannelPartner != 0) {
					$ActiveArchitect->where('architect.added_by', Auth::user()->id);
				} 
				if(isset($req_state_id) && $req_state_id != "") {
					$ActiveArchitect->where('users.state_id', $req_state_id);
				}

				if(isset($req_city_id) && $req_city_id != "") {
					$ActiveArchitect->where('users.city_id', $req_city_id);
				}
				$ActiveArchitectCount = $ActiveArchitect->distinct()->pluck('architect.id')->count();
				$ActiveArchitectIds = $ActiveArchitect->distinct()->pluck('architect.id')->all();
			}
			// ARCHITECT COUNT END

			// ELECTRICIAN COUNT START
			$ElectricianIds = array();
			if($isAccountUser == 1){
				$ElectricianCount = 0;
			} else {
				$Electrician = Electrician::query();
				$Electrician->whereIn('electrician.type', [301, 302]);
				if ($isAdminOrCompanyAdmin == 1) {

					if (isset($req_user_id) && $req_user_id != "") {
						$Electrician->whereIn('electrician.sale_person_id', $req_user_id);
					}
				} else if ($isSalePerson == 1) {

					if (isset($req_user_id) && $req_user_id != "") {
						$Electrician->whereIn('electrician.sale_person_id', $req_user_id);
					} else {
						$Electrician->whereIn('electrician.sale_person_id', $childSalePersonsIds);
					}
				} else if ($isChannelPartner != 0) {
					$Electrician->where('electrician.added_by', Auth::user()->id);
				} 
				if(isset($req_state_id) && $req_state_id != "") {
					$Electrician->leftJoin('users', 'users.id', '=', 'electrician.user_id');
					$Electrician->where('users.state_id', $req_state_id);
				}

				if(isset($req_city_id) && $req_city_id != "") {
					// $Electrician->leftJoin('users', 'users.id', '=', 'electrician.user_id');
					$Electrician->where('users.city_id', $req_city_id);
				}

				$ElectricianCount = $Electrician->distinct()->pluck('electrician.id')->count();
				$ElectricianIds = $Electrician->distinct()->pluck('electrician.id')->all();
			}

			$NewElectricianIds = array();
			if($isAccountUser == 1){
				$NewElectricianCount = 0;
			} else {
				$NewElectrician = Electrician::query();
				// $NewElectrician->whereYear('electrician.created_at', date('Y'));
    			// $NewElectrician->whereMonth('electrician.created_at', date('m'));
				$NewElectrician->whereDate('electrician.created_at', '>=', $startDate);
				$NewElectrician->whereDate('electrician.created_at', '<=', $endDate);
				$NewElectrician->whereIn('electrician.type', [301, 302]);
				if ($isAdminOrCompanyAdmin == 1) {

					if (isset($req_user_id) && $req_user_id != "") {
						$NewElectrician->whereIn('electrician.sale_person_id', $req_user_id);
					}
				} else if ($isSalePerson == 1) {

					if (isset($req_user_id) && $req_user_id != "") {
						$NewElectrician->whereIn('electrician.sale_person_id', $req_user_id);
					} else {
						$NewElectrician->whereIn('electrician.sale_person_id', $childSalePersonsIds);
					}
				} else if ($isChannelPartner != 0) {
					$NewElectrician->where('electrician.added_by', Auth::user()->id);
				} 
				if(isset($req_state_id) && $req_state_id != "") {
					$NewElectrician->leftJoin('users', 'users.id', '=', 'electrician.user_id');
					$NewElectrician->where('users.state_id', $req_state_id);
				}

				if(isset($req_city_id) && $req_city_id != "") {
					// $NewElectrician->leftJoin('users', 'users.id', '=', 'electrician.user_id');
					$NewElectrician->where('users.city_id', $req_city_id);
				}
				
				$NewElectricianCount = $NewElectrician->distinct()->pluck('electrician.id')->count();
				$NewElectricianIds = $NewElectrician->distinct()->pluck('electrician.id')->all();
			}

			$ActiveElectricianIds = array();
			if($isAccountUser == 1){
				$ActiveElectricianCount = 0;
			} else {
				$ActiveElectrician = Electrician::query();
				$ActiveElectrician->leftJoin('users', 'users.id', '=', 'electrician.id');
				$ActiveElectrician->where('users.status', 1);
				$ActiveElectrician->whereIn('electrician.type', [301, 302]);
				if ($isAdminOrCompanyAdmin == 1) {

					if (isset($req_user_id) && $req_user_id != "") {
						$ActiveElectrician->whereIn('electrician.sale_person_id', $req_user_id);
					}
				} else if ($isSalePerson == 1) {

					if (isset($req_user_id) && $req_user_id != "") {
						$ActiveElectrician->whereIn('electrician.sale_person_id', $req_user_id);
					} else {
						$ActiveElectrician->whereIn('electrician.sale_person_id', $childSalePersonsIds);
					}
				} else if ($isChannelPartner != 0) {
					$ActiveElectrician->where('electrician.added_by', Auth::user()->id);
				} 
				if(isset($req_state_id) && $req_state_id != "") {
					$ActiveElectrician->where('users.state_id', $req_state_id);
				}

				if(isset($req_city_id) && $req_city_id != "") {
					$ActiveElectrician->where('users.city_id', $req_city_id);
				}
				$ActiveElectricianCount = $ActiveElectrician->distinct()->pluck('electrician.id')->count();
				$ActiveElectricianIds = $ActiveElectrician->distinct()->pluck('electrician.id')->all();
			}
			// ELECTRICIAN COUNT START

			// SALES ORDER COUNT START
			$OrderTotalCount = 0;
			$OrderTotalIds = array();

			$OrderPlaceAmount = 0;
			$OrderPlaceIds = array();

			$OrderDispatchedAmount = 0;
			$OrderDispatchedIds = array();

			if ($isAdminOrCompanyAdmin == 1 || $isSalePerson == 1 || $isAccountUser == 1) {

				$hasFilter = 0;
				if (isset($req_channel_partner_user_id) && is_array($req_channel_partner_user_id)) {
					$hasFilter = 1;
				}
				if (isset($req_user_id) && is_array($req_user_id)) {
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

				if(isset($req_state_id) && $req_state_id != "") {
					$orderTotal->leftJoin('users', 'users.id', '=', 'channel_partner.user_id');
					$orderTotal->where('users.state_id', $req_state_id);
				}

				if(isset($req_city_id) && $req_city_id != "") {
					$orderTotal->where('users.city_id', $req_city_id);
				}

				if (isset($req_channel_partner_user_id) && is_array($req_channel_partner_user_id)) {
					$orderTotal->whereIn('orders.channel_partner_user_id', $req_channel_partner_user_id);
				}

				if (isset($req_user_id) && is_array($req_user_id)) {
					$salesUserIds = $req_user_id;
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
					$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
					$orderTotalAmount->where(function ($query) use ($childSalePersonsIds) {

						foreach ($childSalePersonsIds as $key => $value) {
							if ($key == 0) {
								$query->whereRaw('FIND_IN_SET("' . $value . '",orders.sale_persons)>0');
							} else {
								$query->orWhereRaw('FIND_IN_SET("' . $value . '",orders.sale_persons)>0');
							}
						}
					});

					$orderTotalAmount->where('channel_partner.reporting_company_id', Auth::user()->company_id);
					
				}
				
				if(isset($req_state_id) && $req_state_id != "") {
					$orderTotalAmount->leftJoin('users', 'users.id', '=', 'channel_partner.user_id');
					$orderTotalAmount->where('users.state_id', $req_state_id);
				}

				if(isset($req_city_id) && $req_city_id != "") {
					$orderTotalAmount->where('users.city_id', $req_city_id);
				}

				// if(Auth::user()->id == 3) {
				// 	$AsmChannelPartnerIds = User::select('id')->where('type', 101)->distinct()->pluck('id');
				// 	$orderTotalAmount->whereNotIn('orders.channel_partner_user_id', $AsmChannelPartnerIds);
				// }

				if (isset($req_channel_partner_user_id) && is_array($req_channel_partner_user_id)) {
					$orderTotalAmount->whereIn('orders.channel_partner_user_id', $req_channel_partner_user_id);
				}

				if (isset($req_user_id) && is_array($req_user_id)) {
					$salesUserIds = $req_user_id;
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

				if(isset($req_state_id) && $req_state_id != "") {
					$orderTotalAmountDispatched->leftJoin('users', 'users.id', '=', 'channel_partner.user_id');
					$orderTotalAmountDispatched->where('users.state_id', $req_state_id);
				}

				if(isset($req_city_id) && $req_city_id != "") {
					$orderTotalAmountDispatched->where('users.city_id', $req_city_id);
				}

				// if(Auth::user()->id == 3) {
				// 	$AsmChannelPartnerIds = User::select('id')->where('type', 101)->distinct()->pluck('id');
				// 	$orderTotalAmountDispatched->whereNotIn('orders.channel_partner_user_id', $AsmChannelPartnerIds);
				// }


				if (isset($req_channel_partner_user_id) && is_array($req_channel_partner_user_id)) {
	
					$orderTotalAmountDispatched->whereIn('orders.channel_partner_user_id', $req_channel_partner_user_id);
				}
				if (isset($req_user_id) && is_array($req_user_id)) {
	
					$salesUserIds = $req_user_id;
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
				// if ($req_channel_partner_type != 0) {
	
				// 	$orderTotal->where('channel_partner.type', $req_channel_partner_type);
				// }else{
					$orderTotal->where('channel_partner.type', '!=',104);
				// }
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
			
			$RewardArchitectIds = array();
			if($isAccountUser == 1){
				$RewardArchitectCount = 0;
			} else {
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
			}

			$RewardElectricianIds = array();
			if($isAccountUser == 1){
				$RewardElectricianCount = 0;
			} else {
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
			}

			// EXECUTIVE COUNT START
			$ExecutiveIds = array();
			if($isAccountUser == 1){
				$ExecutiveCount = 0;
			} else {
				$Executive = ModelsSalePerson::query();
				$Executive->leftJoin('users', 'users.id', '=', 'sale_person.user_id');
				$Executive->where('users.type', 2);
				
				if ($isAdminOrCompanyAdmin == 1) {
					if (isset($req_user_id) && $req_user_id != "") {
						$salePersonArr = "";
						foreach ($req_user_id as $Skey => $Svalue) {
							$salePersonArr .= implode(',',  getChildSalePersonsIds($Svalue));	
						}
						$Executive->whereIn('users.id', explode(',', $salePersonArr));
					}
				} else if ($isSalePerson == 1) {
					if (isset($req_user_id) && $req_user_id != "") {
						$salePersonArr = "";
						foreach ($req_user_id as $Skey => $Svalue) {
							$salePersonArr .= implode(',',  getChildSalePersonsIds($Svalue));	
						}
						$Executive->whereIn('users.id', explode(',', $salePersonArr));
					} else {
						$Executive->whereIn('users.id', $childSalePersonsIds);
					}
				} else if ($isChannelPartner != 0) {
					// $Executive->where('sale_person.user_id', Auth::user()->id);
				} 

				if(isset($req_state_id) && $req_state_id != "") {
					$Executive->where('users.state_id', $req_state_id);
				}

				if(isset($req_city_id) && $req_city_id != "") {
					$Executive->where('users.city_id', $req_city_id);
				}
				$ExecutiveCount = $Executive->distinct()->pluck('users.id')->count();
				$ExecutiveIds = $Executive->distinct()->pluck('users.id')->all();
			}

			$NewExecutiveIds = array();
			if($isAccountUser == 1){
				$NewExecutiveCount = 0;
			} else {
				$NewExecutive = ModelsSalePerson::query();
				$NewExecutive->leftJoin('users', 'users.id', '=', 'sale_person.user_id');
				$NewExecutive->where('users.type', 2);
				$NewExecutive->whereYear('users.created_at', date('Y'));
    			$NewExecutive->whereMonth('users.created_at', date('m'));

				if ($isAdminOrCompanyAdmin == 1) {
					if (isset($req_user_id) && $req_user_id != "") {
						$salePersonArr = "";
						foreach ($req_user_id as $Skey => $Svalue) {
							$salePersonArr .= implode(',',  getChildSalePersonsIds($Svalue));	
						}
						$NewExecutive->whereIn('users.id', explode(',', $salePersonArr));
					}
				} else if ($isSalePerson == 1) {

					if (isset($req_user_id) && $req_user_id != "") {
						$salePersonArr = "";
						foreach ($req_user_id as $Skey => $Svalue) {
							$salePersonArr .= implode(',',  getChildSalePersonsIds($Svalue));	
						}
						$NewExecutive->whereIn('users.id', explode(',', $salePersonArr));
					} else {
						$NewExecutive->whereIn('users.id', $childSalePersonsIds);
					}
				} else if ($isChannelPartner != 0) {
					// $NewExecutive->where('architect.added_by', Auth::user()->id);
				} 
				if(isset($req_state_id) && $req_state_id != "") {
					$NewExecutive->where('users.state_id', $req_state_id);
				}

				if(isset($req_city_id) && $req_city_id != "") {
					$NewExecutive->where('users.city_id', $req_city_id);
				}
				$NewExecutiveCount = $NewExecutive->distinct()->pluck('users.id')->count();
				$NewExecutiveIds = $NewExecutive->distinct()->pluck('users.id')->all();
			}

			$ActiveExecutiveIds = array();
			if($isAccountUser == 1){
				$ActiveExecutiveCount = 0;
			} else {
				$ActiveExecutive = ModelsSalePerson::query();
				$ActiveExecutive->leftJoin('users', 'users.id', '=', 'sale_person.user_id');
				$ActiveExecutive->where('users.status', 1);
				$ActiveExecutive->where('users.type', 2);
				
				if ($isAdminOrCompanyAdmin == 1) {
					if (isset($req_user_id) && $req_user_id != "") {
						$salePersonArr = "";
						foreach ($req_user_id as $Skey => $Svalue) {
							$salePersonArr .= implode(',',  getChildSalePersonsIds($Svalue));	
						}
						$ActiveExecutive->whereIn('users.id', explode(',', $salePersonArr));
					}
				} else if ($isSalePerson == 1) {

					if (isset($req_user_id) && $req_user_id != "") {
						$salePersonArr = "";
						foreach ($req_user_id as $Skey => $Svalue) {
							$salePersonArr .= implode(',',  getChildSalePersonsIds($Svalue));	
						}
						$ActiveExecutive->whereIn('users.id', explode(',', $salePersonArr));
					} else {
						$ActiveExecutive->whereIn('users.id', $childSalePersonsIds);
					}
				} else if ($isChannelPartner != 0) {
					// $ActiveExecutive->where('architect.added_by', Auth::user()->id);
				} 
				if(isset($req_state_id) && $req_state_id != "") {
					$ActiveExecutive->where('users.state_id', $req_state_id);
				}

				if(isset($req_city_id) && $req_city_id != "") {
					$ActiveExecutive->where('users.city_id', $req_city_id);
				}
				$ActiveExecutiveCount = $ActiveExecutive->distinct()->pluck('users.id')->count();
				$ActiveExecutiveIds = $ActiveExecutive->distinct()->pluck('users.id')->all();
			}
			// EXECUTIVE COUNT END 

			// ADM COUNT START 
			$AdmIds = array();
			if($isAccountUser == 1){
				$AdmCount = 0;
			} else {
				$Adm = ChannelPartner::query();
				$Adm->leftJoin('users', 'users.id', '=', 'channel_partner.user_id');
				$Adm->where('users.type', 102);
				if ($isAdminOrCompanyAdmin == 1) {
					if (isset($req_user_id) && $req_user_id != "") {
						$Adm->whereIn('channel_partner.sale_persons', $req_user_id);
					}
				} else if ($isSalePerson == 1) {

					if (isset($req_user_id) && $req_user_id != "") {
						$Adm->whereIn('channel_partner.sale_persons', $req_user_id);
					} else {
						$Adm->whereIn('channel_partner.sale_persons', $childSalePersonsIds);
					}
				} else if ($isChannelPartner != 0) {
					// $Adm->where('architect.added_by', Auth::user()->id);
				} 

				if(isset($req_state_id) && $req_state_id != "") {
					$Adm->where('users.state_id', $req_state_id);
				}

				if(isset($req_city_id) && $req_city_id != "") {
					$Adm->where('users.city_id', $req_city_id);
				}
				$AdmCount = $Adm->distinct()->pluck('users.id')->count();
				$AdmIds = $Adm->distinct()->pluck('users.id')->all();
			}

			$NewAdmIds = array();
			if($isAccountUser == 1){
				$NewAdmCount = 0;
			} else {
				$NewAdm = ChannelPartner::query();
				$NewAdm->leftJoin('users', 'users.id', '=', 'channel_partner.user_id');
				$NewAdm->where('users.type', 102);
				$NewAdm->whereYear('users.created_at', date('Y'));
    			$NewAdm->whereMonth('users.created_at', date('m'));
				if ($isAdminOrCompanyAdmin == 1) {
					if (isset($req_user_id) && $req_user_id != "") {
						$NewAdm->whereIn('channel_partner.sale_persons', $req_user_id);
					}
				} else if ($isSalePerson == 1) {

					if (isset($req_user_id) && $req_user_id != "") {
						$NewAdm->whereIn('channel_partner.sale_persons', $req_user_id);
					} else {
						$NewAdm->whereIn('channel_partner.sale_persons', $childSalePersonsIds);
					}
				} else if ($isChannelPartner != 0) {
					// $NewAdm->where('architect.added_by', Auth::user()->id);
				} 

				if(isset($req_state_id) && $req_state_id != "") {
					$NewAdm->where('users.state_id', $req_state_id);
				}

				if(isset($req_city_id) && $req_city_id != "") {
					$NewAdm->where('users.city_id', $req_city_id);
				}
				$NewAdmCount = $NewAdm->distinct()->pluck('users.id')->count();
				$NewAdmIds = $NewAdm->distinct()->pluck('users.id')->all();
			}

			$ActiveAdmIds = array();
			if($isAccountUser == 1){
				$ActiveAdmCount = 0;
			} else {
				$ActiveAdm = ChannelPartner::query();
				$ActiveAdm->leftJoin('users', 'users.id', '=', 'channel_partner.user_id');
				$ActiveAdm->where('users.status', 1);
				$ActiveAdm->where('users.type', 102);
				if ($isAdminOrCompanyAdmin == 1) {
					if (isset($req_user_id) && $req_user_id != "") {
						$ActiveAdm->whereIn('channel_partner.sale_persons', $req_user_id);
					}
				} else if ($isSalePerson == 1) {

					if (isset($req_user_id) && $req_user_id != "") {
						$ActiveAdm->whereIn('channel_partner.sale_persons', $req_user_id);
					} else {
						$ActiveAdm->whereIn('channel_partner.sale_persons', $childSalePersonsIds);
					}
				} else if ($isChannelPartner != 0) {
					// $ActiveAdm->where('architect.added_by', Auth::user()->id);
				} 
				
				if(isset($req_state_id) && $req_state_id != "") {
					$ActiveAdm->where('users.state_id', $req_state_id);
				}

				if(isset($req_city_id) && $req_city_id != "") {
					$ActiveAdm->where('users.city_id', $req_city_id);
				}
				$ActiveAdmCount = $ActiveAdm->distinct()->pluck('users.id')->count();
				$ActiveAdmIds = $ActiveAdm->distinct()->pluck('users.id')->all();
			}
			// ADM COUNT END 


			// AD COUNT START 
			$AdIds = array();
			if($isAccountUser == 1){
				$AdCount = 0;
			} else {
				$Ad = ChannelPartner::query();
				$Ad->leftJoin('users', 'users.id', '=', 'channel_partner.user_id');
				$Ad->where('users.type', 104);
				if ($isAdminOrCompanyAdmin == 1) {
					if (isset($req_user_id) && $req_user_id != "") {
						$Ad->whereIn('channel_partner.sale_persons', $req_user_id);
					}
				} else if ($isSalePerson == 1) {

					if (isset($req_user_id) && $req_user_id != "") {
						$Ad->whereIn('channel_partner.sale_persons', $req_user_id);
					} else {
						$Ad->whereIn('channel_partner.sale_persons', $childSalePersonsIds);
					}
				} else if ($isChannelPartner != 0) {
					// $Ad->where('architect.added_by', Auth::user()->id);
				} 
				if(isset($req_state_id) && $req_state_id != "") {
					$Ad->where('users.state_id', $req_state_id);
				}

				if(isset($req_city_id) && $req_city_id != "") {
					$Ad->where('users.city_id', $req_city_id);
				}
				$AdCount = $Ad->distinct()->pluck('users.id')->count();
				$AdIds = $Ad->distinct()->pluck('users.id')->all();
			}

			$NewAdIds = array();
			if($isAccountUser == 1){
				$NewAdCount = 0;
			} else {		
				$NewAd = ChannelPartner::query();
				$NewAd->leftJoin('users', 'users.id', '=', 'channel_partner.user_id');
				$NewAd->where('users.type', 104);
				$NewAd->whereYear('users.created_at', date('Y'));
    			$NewAd->whereMonth('users.created_at', date('m'));
				if ($isAdminOrCompanyAdmin == 1) {
					if (isset($req_user_id) && $req_user_id != "") {
						$NewAd->whereIn('channel_partner.sale_persons', $req_user_id);
					}
				} else if ($isSalePerson == 1) {

					if (isset($req_user_id) && $req_user_id != "") {
						$NewAd->whereIn('channel_partner.sale_persons', $req_user_id);
					} else {
						$NewAd->whereIn('channel_partner.sale_persons', $childSalePersonsIds);
					}
				} else if ($isChannelPartner != 0) {
					// $NewAd->where('architect.added_by', Auth::user()->id);
				}
				if(isset($req_state_id) && $req_state_id != "") {
					$NewAd->where('users.state_id', $req_state_id);
				}

				if(isset($req_city_id) && $req_city_id != "") {
					$NewAd->where('users.city_id', $req_city_id);
				} 
				$NewAdCount = $NewAd->distinct()->pluck('users.id')->count();
				$NewAdIds = $NewAd->distinct()->pluck('users.id')->all();
			}

			$ActiveAdIds = array();
			if($isAccountUser == 1){
				$ActiveAdCount = 0;
			} else {
				$ActiveAd = ChannelPartner::query();
				$ActiveAd->leftJoin('users', 'users.id', '=', 'channel_partner.user_id');
				$ActiveAd->where('users.status', 1);
				$ActiveAd->where('users.type', 104);
				if ($isAdminOrCompanyAdmin == 1) {
					if (isset($req_user_id) && $req_user_id != "") {
						$ActiveAd->whereIn('channel_partner.sale_persons', $req_user_id);
					}
				} else if ($isSalePerson == 1) {

					if (isset($req_user_id) && $req_user_id != "") {
						$ActiveAd->whereIn('channel_partner.sale_persons', $req_user_id);
					} else {
						$ActiveAd->whereIn('channel_partner.sale_persons', $childSalePersonsIds);
					}
				} else if ($isChannelPartner != 0) {
					// $ActiveAd->where('architect.added_by', Auth::user()->id);
				} 
				if(isset($req_state_id) && $req_state_id != "") {
					$ActiveAd->where('users.state_id', $req_state_id);
				}

				if(isset($req_city_id) && $req_city_id != "") {
					$ActiveAd->where('users.city_id', $req_city_id);
				}
				$ActiveAdCount = $ActiveAd->distinct()->pluck('users.id')->count();
				$ActiveAdIds = $ActiveAd->distinct()->pluck('users.id')->all();
			}
			// AD COUNT END 

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
				if (isset($req_channel_partner_user_id) && is_array($req_channel_partner_user_id)) {
					$LeadOff->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
					$LeadOff->whereIn('lead_sources.source', $req_channel_partner_user_id);
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
				if (isset($req_channel_partner_user_id) && is_array($req_channel_partner_user_id)) {
					$LeadMar->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
					$LeadMar->whereIn('lead_sources.source', $req_channel_partner_user_id);
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
				if (isset($req_channel_partner_user_id) && is_array($req_channel_partner_user_id)) {
					$LeadDemo->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
					$LeadDemo->whereIn('lead_sources.source', $req_channel_partner_user_id);
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
			$response['pridiction_ids'] = implode(',', $PridictionDealIds);
			$response['pridiction_count'] = $PridictionDealCount;
			$response['pridiction_total_amount'] = $PridictionDealTotalAmount;
			$response['pridiction_wl_amount'] = $PridictionDealWlAmount;
			$response['pridiction_billing_amount'] = $PridictionDealBillingAmount;

			$response['lead_total_ids'] = implode(',', $LeadTotalIds);
			$response['lead_total_count'] = $LeadTotalCount;

			$response['deal_convert_ids'] = implode(',', $DealConvertIds);
			$response['deal_convert_count'] = $DealConvertCount;

			$response['lead_won_ids'] = implode(',', $LeadWonIds);
			$response['lead_won_count'] = $LeadWonCount;

			$response['lead_lost_ids'] = implode(',', $LeadLostIds);
			$response['lead_lost_count'] = $LeadLostCount;

			$response['lead_cold_ids'] = implode(',', $LeadColdIds);
			$response['lead_cold_count'] = $LeadColdCount;

			$response['lead_runing_ids'] = implode(',', $LeadRunningIds);
			// $response['lead_runing_ids'] = explode(',', $LeadRunningIds);
			$response['lead_runing_count'] = $LeadRunningCount;

			$response['executives_ids'] = $ExecutiveIds;
			$response['executives_count'] = $ExecutiveCount;

			$response['new_executives_ids'] = $NewExecutiveIds;
			$response['new_executives_count'] = $NewExecutiveCount;

			$response['active_executives_ids'] = $ActiveExecutiveIds;
			$response['active_executives_count'] = $ActiveExecutiveCount;

			$response['adm_ids'] = $AdmIds;
			$response['adm_count'] = $AdmCount;

			$response['new_adm_ids'] = $NewAdmIds;
			$response['new_adm_count'] = $NewAdmCount;

			$response['active_adm_ids'] = $ActiveAdmIds;
			$response['active_adm_count'] = $ActiveAdmCount;

			$response['ad_ids'] = $AdIds;
			$response['ad_count'] = $AdCount;

			$response['new_ad_ids'] = $NewAdIds;
			$response['new_ad_count'] = $NewAdCount;

			$response['active_ad_ids'] = $ActiveAdIds;
			$response['active_ad_count'] = $ActiveAdCount;

			$response['lead_offline_ids'] = $LeadOfflineIds;
			$response['lead_offline_count'] = $LeadOfflineCount;

			$response['lead_marketing_ids'] = $LeadMarketingIds;
			$response['lead_marketing_count'] = $LeadMarketingCount;

			$response['lead_demo_meeting_done_ids'] = $LeadDemoMeetingDoneIds;
			$response['lead_demo_meeting_done_count'] = $LeadDemoMeetingDoneCount;

			$LeadConversionRatio = 0.00;
			$totalLeadDealCount = $LeadWonCount + $LeadLostCount + $LeadColdCount;
			if ($totalLeadDealCount != 0) {
				$LeadConversionRatio = round((($LeadWonCount * 100) / ($totalLeadDealCount)), 2);
			}
			$response['conversion_ratio'] = $LeadConversionRatio;

			$response['order_ids'] = implode(',', $OrderTotalIds);
			$response['order_count'] = $OrderTotalCount;

			$response['order_place_ids'] = implode(',', $OrderPlaceIds);
			$response['order_place_amount'] = numCommaFormat(ceil($OrderPlaceAmount));

			$response['order_dispateched_ids'] = implode(',', $OrderDispatchedIds);
			$response['order_dispateched_amount'] = numCommaFormat(ceil($OrderDispatchedAmount));

			// $response['months'] = $monthNumbersString;
			
			if(isset($req_channel_partner_user_id) && count($req_channel_partner_user_id) > 0) {
				$response['architects_ids'] = [];
				$response['architects_count'] = "-";

				$response['new_architects_ids'] = [];
				$response['new_architects_count'] = "-";

				$response['active_architects_ids'] = [];
				$response['active_architects_count'] = "-";
				
				$response['electricians_ids'] = [];
				$response['electricians_count'] = "-";

				$response['new_electricians_ids'] = [];
				$response['new_electricians_count'] = "-";

				$response['active_electricians_ids'] = [];
				$response['active_electricians_count'] = "-";

				$response['architects_reward_ids'] = [];
				$response['architects_reward_count'] = "-";
				
				$response['electricians_reward_ids'] = [];
				$response['electricians_reward_count'] = "-";

				$response['target_ids'] = [];
				$response['target_amount'] = "-";
			} else {
				$response['architects_ids'] = implode(',', $ArchitectIds);
				$response['architects_count'] = $ArchitectCount;

				$response['new_architects_ids'] = implode(',', $NewArchitectIds);
				$response['new_architects_count'] = $NewArchitectCount;

				$response['active_architects_ids'] = implode(',', $ActiveArchitectIds);
				$response['active_architects_count'] = $ActiveArchitectCount;

				$response['electricians_ids'] = implode(',', $ElectricianIds);
				$response['electricians_count'] = $ElectricianCount;

				$response['new_electricians_ids'] = implode(',', $NewElectricianIds);
				$response['new_electricians_count'] = $NewElectricianCount;

				$response['active_electricians_ids'] = implode(',', $ActiveElectricianIds);
				$response['active_electricians_count'] = $ActiveElectricianCount;

				$response['architects_reward_ids'] = implode(',', $RewardArchitectIds);
				$response['architects_reward_count'] = $RewardArchitectCount;
				
				$response['electricians_reward_ids'] = implode(',', $RewardElectricianIds);
				$response['electricians_reward_count'] = $RewardElectricianCount;

				$response['target_ids'] = implode(',', $TargetIds);
				$response['target_amount'] = numCommaFormat(ceil($TargetAmount));
			}

			


			if ($isSalePerson == 1) {
				$response['chield'] = implode(',', $childSalePersonsIds);
			}
		}
		return response()->json($response)->header('Content-Type', 'application/json');
	}
}
