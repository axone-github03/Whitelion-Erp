<?php

namespace App\Http\Controllers\API\Dashboard;

use DB;
use Mail;
use Config;
use Carbon\Carbon;
use App\Models\Lead;
use App\Models\User;
use App\Models\Order;
use App\Models\Invoice;
use App\Models\Architect;
use App\Models\Electrician;
use App\Models\GiftProductOrder;
use App\Models\Wlmst_target;
use App\Models\Wltrn_Quotation;
use App\Models\Wltrn_QuotItemdetail;
use Illuminate\Http\Request;
use App\Models\Wlmst_targetdetail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\CRMSettingStageOfSite;
use Illuminate\Support\Facades\Validator;

class DashboardReportController extends Controller
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

    function dashboardReport(Request $request)
    {
        $isSalePerson = isSalePerson();
        $dataIds = explode(',', $request->data_ids);
        $dataType = $request->data_type;
		$recordsTotal = 0;
		$recordsFiltered = 0;

		$DealBillingAmount = 0;
        $DealTotalAmount = 0;
		
		$data = array();
		$req_startDate = date('Y-m-d', strtotime($request->start_date));

        if ((count($dataIds) != 0 || $dataType != '') && ($dataType == "ORDER" || $dataType == "PLACED" || $dataType == "DISPATCHED")) {
            $searchColumns = [
                0 => 'orders.id',
                1 => 'users.first_name',
                2 => 'users.last_name',
                3 => 'channel_partner.firm_name',
            ];

            $sortingColumns = [
                0 => 'orders.id',
                1 => 'orders.user_id',
                2 => 'orders.channel_partner_user_id',
                3 => 'orders.sale_persons',
                4 => 'orders.payment_mode',
                5 => 'orders.status',
            ];

            $selectColumns = [
                0 => 'orders.id',
                1 => 'orders.user_id',
                2 => 'orders.channel_partner_user_id',
                3 => 'orders.sale_persons',
                4 => 'orders.payment_mode',
                5 => 'orders.status',
                6 => 'orders.created_at',
                7 => 'users.first_name as first_name',
                8 => 'users.last_name as last_name',
                9 => 'channel_partner.firm_name',
                10 => 'orders.payment_mode',
                11 => 'orders.total_mrp_minus_disocunt',
                12 => 'orders.total_payable',
                13 => 'orders.pending_total_payable',
                14 => 'orders.sub_status',
                15 => 'orders.invoice',
                16 => 'channel_partner.type as channel_partner_type',
                17 => 'channel_partner_user.first_name as channel_partner_user_first_name',
                18 => 'channel_partner_user.last_name as channel_partner_user_last_name',
                19 => 'channel_partner_user.phone_number as channel_partner_user_phone_number',
            ];

            $recordsTotal = Order::query();
            $recordsTotal->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
            $recordsTotal->whereIn('orders.id', $dataIds);
            $recordsTotal = $recordsTotal->count();

            $recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.
            $query = Order::query();
            $query->leftJoin('users', 'users.id', '=', 'orders.user_id');
            $query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
            $query->leftJoin('users as channel_partner_user', 'channel_partner_user.id', '=', 'orders.channel_partner_user_id');
            $query->whereIn('orders.id', $dataIds);
            $query->select($selectColumns);
            // $query->limit($request->length);
            // $query->offset($request->start);
            // $query->orderBy($sortingColumns[$request['order'][0]['column']], $request['order'][0]['dir']);

            $isFilterApply = 0;

            if (isset($request['search']['value'])) {
                $isFilterApply = 1;
                $search_value = $request['search']['value'];
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

            $data = $query->get();

            $data = json_decode(json_encode($data), true);
            if ($isFilterApply == 1) {
                $recordsFiltered = count($data);
            }

            $channelPartner = getChannelPartners();

            foreach ($data as $key => $value) {
                $data[$key]['created_time'] = convertOrderDateTime($value['created_at'], 'time');
                $data[$key]['created_date'] = convertOrderDateTime($value['created_at'], 'date');
                $data[$key]['channel_partner_type'] = $channelPartner[$value['channel_partner_type']]['short_name'];

                $sale_persons = explode(',', $value['sale_persons']);
                $sale_persons_list = User::select('users.first_name', 'users.last_name', 'users.type', 'users.phone_number', 'sales_hierarchy.code as sales_hierarchy_code')
                    ->leftJoin('sale_person', 'sale_person.id', '=', 'users.reference_id')
                    ->leftJoin('sales_hierarchy', 'sales_hierarchy.id', '=', 'sale_person.type')
                    ->whereIn('users.id', $sale_persons)
                    ->get();
                $data[$key]['sales_person'] = $sale_persons_list;

                $data[$key]['total_mrp_minus_disocunt'] = priceLable($value['total_mrp_minus_disocunt']);
                $data[$key]['total_payable'] = priceLable($value['total_payable']);

				$data[$key]['order_status'] = getOrderStatus($value['status']);
				
            }
        } else if ((count($dataIds) != 0 || $dataType != '') && $dataType == "ARCHITECT") {

			$searchColumns = array(
				0 => 'users.id',
				1 => 'users.first_name',
				2 => 'users.last_name',
				3 => 'users.email',
				4 => 'users.phone_number',
				5 => 'sale_person.first_name',
				6 => 'sale_person.last_name',
				7 => "CONCAT(users.first_name,' ',users.last_name)",
				8 => "CONCAT(sale_person.first_name,' ',sale_person.last_name)",
	
			);
	
			$sortingColumns = array(
				0 => 'users.id',
				1 => 'users.first_name',
				2 => 'users.email',
				3 => 'architect.sale_person_id',
				4 => 'architect.total_point_current',
				5 => 'architect.total_point',
				6 => 'users.status',
				7 => 'architect.category_id',
				8 => 'architect.principal_architect_name',
				9 => 'users.created_by',
	
			);
	
			$selectColumns = array(
				'users.id',
				'users.type',
				'users.first_name',
				'users.last_name',
				'users.email',
				'users.phone_number',
				'architect.sale_person_id',
				'users.status',
				'architect.total_point_current',
				'sale_person.first_name as sale_person_first_name',
				'sale_person.last_name  as sale_person_last_name',
				'users.created_at',
				'architect.category_id',
				'architect.principal_architect_name',
				'architect.tele_verified',
				'architect.tele_not_verified',
				'architect.instagram_link',
				'architect.data_verified',
				'architect.data_not_verified',
				'architect.missing_data',
				'architect.total_point',
				'created_by_user.first_name as created_by_user_first_name',
				'created_by_user.last_name as created_by_user_last_name',
	
			);
	
			$query = Architect::query();
			$query->leftJoin('users', 'users.id', '=', 'architect.user_id');
	
			$query->leftJoin('users as sale_person', 'architect.sale_person_id', '=', 'sale_person.id');
			$query->whereIn('architect.type', [201, 202]);
			$query->whereIn('architect.id', $dataIds);
			$recordsTotal = $query->count();
			$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.
	
			$query = Architect::query();
			$query->leftJoin('users', 'users.id', '=', 'architect.user_id');
			$query->leftJoin('users as sale_person', 'architect.sale_person_id', '=', 'sale_person.id');
			$query->whereIn('architect.type', [201, 202]);
			$query->whereIn('architect.id', $dataIds);
			$query->select('architect.id');
			// $query->limit($request->length);
			// $query->offset($request->start);
	
			$isFilterApply = 0;
	
			if (isset($request['search']['value'])) {
				$isFilterApply = 1;
				$search_value = $request['search']['value'];
				$query->where(function ($query) use ($search_value, $searchColumns) {
	
					for ($i = 0; $i < count($searchColumns); $i++) {
	
						if ($i == 0) {
							$query->whereRaw($searchColumns[$i] . ' like ? ', [$search_value]);
							$query->orWhereRaw($searchColumns[$i] . ' like ? ', ["%" . $search_value . "%"]);
						} else {
	
							$query->orWhereRaw($searchColumns[$i] . ' like ? ', [$search_value]);
							$query->orWhereRaw($searchColumns[$i] . ' like ? ', ["%" . $search_value . "%"]);
						}
					}
				});
			}
	
			$recordsFiltered = $query->count();
	
			$query = Architect::query();
			$query->leftJoin('users', 'users.id', '=', 'architect.user_id');
			$query->leftJoin('users as created_by_user', 'users.created_by', '=', 'created_by_user.id');
			$query->leftJoin('users as sale_person', 'architect.sale_person_id', '=', 'sale_person.id');
			$query->whereIn('architect.type', [201, 202]);
			$query->whereIn('architect.id', $dataIds);
			$query->select($selectColumns);
			// $query->limit($request->length);
			// $query->offset($request->start);
				// $query->orderBy($sortingColumns[$request['order'][0]['column']], $request['order'][0]['dir']);
			$isFilterApply = 0;
	
			if (isset($request['search']['value'])) {
				$isFilterApply = 1;
				$search_value = $request['search']['value'];
				$query->where(function ($query) use ($search_value, $searchColumns) {
	
					for ($i = 0; $i < count($searchColumns); $i++) {
	
						if ($i == 0) {
							$query->whereRaw($searchColumns[$i] . ' like ? ', [$search_value]);
							$query->orWhereRaw($searchColumns[$i] . ' like ? ', ["%" . $search_value . "%"]);
						} else {
	
							$query->orWhereRaw($searchColumns[$i] . ' like ? ', [$search_value]);
							$query->orWhereRaw($searchColumns[$i] . ' like ? ', ["%" . $search_value . "%"]);
						}
					}
				});
			}
			$data = $query->get();
			$data = json_decode(json_encode($data), true);
	
			foreach ($data as $key => $value) {
				$data[$key]['user_type'] =  getUserTypeName($value['type']);
				$data[$key]['user_status'] = getArchitectsStatus()[$value['status']]['header_code'];

			}
	

		} else if ((count($dataIds) != 0 || $dataType != '') && $dataType == "ELECTRICIAN") {
			$searchColumns = array(
				'users.id',
				'users.first_name',
				'users.last_name',
				'users.email',
				'users.phone_number',
				'CONCAT(sale_person.first_name," ",sale_person.last_name)',
				'CONCAT(users.first_name," ",users.last_name)',
	
			);
	
			$sortingColumns = array(
				0 => 'users.id',
				1 => 'users.first_name',
				2 => 'users.phone_number',
				3 => 'electrician.sale_person_id',
				4 => 'users.status',
				5 => 'users.created_by',

			);
	
			$selectColumns = array(
				'users.id',
				'users.type',
				'users.first_name',
				'users.last_name',
				'users.email',
				'users.phone_number',
				'electrician.sale_person_id',
				'users.status',
				'users.created_at',
				'electrician.total_point_current',
				'electrician.total_point',
				'created_by_user.first_name as created_by_user_first_name',
				'created_by_user.last_name as created_by_user_last_name',
				'sale_person.first_name as sale_person_first_name',
				'sale_person.last_name  as sale_person_last_name',
	
			);
	
			$query = Electrician::query();
			$query->leftJoin('users', 'users.id', '=', 'electrician.user_id');
			$query->whereIn('electrician.type', [301, 302]);
			$query->whereIn('electrician.id', $dataIds);
			$recordsTotal = $query->count();
	
			$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.
	
			$query = Electrician::query();
			$query->leftJoin('users', 'users.id', '=', 'electrician.user_id');
			$query->leftJoin('users as sale_person', 'electrician.sale_person_id', '=', 'sale_person.id');
			$query->whereIn('electrician.type', [301, 302]);
			$query->whereIn('electrician.id', $dataIds);
			$query->select('electrician.id');
			// $query->orderBy($sortingColumns[$request['order'][0]['column']], $request['order'][0]['dir']);
	
			$isFilterApply = 0;
	
			if (isset($request['search']['value'])) {
				$isFilterApply = 1;
				$search_value = $request['search']['value'];
				$query->where(function ($query) use ($search_value, $searchColumns) {
	
					for ($i = 0; $i < count($searchColumns); $i++) {
	
						if ($i == 0) {
							$query->whereRaw($searchColumns[$i] . ' like ? ', [$search_value]);
							$query->orWhereRaw($searchColumns[$i] . ' like ? ', ["%" . $search_value . "%"]);
						} else {
	
							$query->orWhereRaw($searchColumns[$i] . ' like ? ', [$search_value]);
							$query->orWhereRaw($searchColumns[$i] . ' like ? ', ["%" . $search_value . "%"]);
						}
					}
				});
			}
	
			$recordsFiltered = $query->count();
	
			$query = Electrician::query();
			$query->leftJoin('users', 'users.id', '=', 'electrician.user_id');
			$query->leftJoin('users as created_by_user', 'users.created_by', '=', 'created_by_user.id');
			$query->leftJoin('users as sale_person', 'electrician.sale_person_id', '=', 'sale_person.id');
			$query->whereIn('electrician.type', [301, 302]);
			$query->whereIn('electrician.id', $dataIds);
			$query->select($selectColumns);
			// $query->limit($request->length);
			// $query->offset($request->start);
				// $query->orderBy($sortingColumns[$request['order'][0]['column']], $request['order'][0]['dir']);
	
			$isFilterApply = 0;
	
			if (isset($request['search']['value'])) {
				$isFilterApply = 1;
				$search_value = $request['search']['value'];
				$query->where(function ($query) use ($search_value, $searchColumns) {
	
					for ($i = 0; $i < count($searchColumns); $i++) {
	
						if ($i == 0) {
							$query->whereRaw($searchColumns[$i] . ' like ? ', [$search_value]);
							$query->orWhereRaw($searchColumns[$i] . ' like ? ', ["%" . $search_value . "%"]);
						} else {
	
							$query->orWhereRaw($searchColumns[$i] . ' like ? ', [$search_value]);
							$query->orWhereRaw($searchColumns[$i] . ' like ? ', ["%" . $search_value . "%"]);
						}
					}
				});
			}
	
			$data = $query->get();
			$data = json_decode(json_encode($data), true);
	
			foreach ($data as $key => $value) {
	
				$data[$key]['user_type'] =  getUserTypeName($value['type']);
				$data[$key]['user_status'] = getElectricianStatus()[$value['status']]['header_code'];
	
			}
	
		} else if ((count($dataIds) != 0 || $dataType != '') && ($dataType == "LEAD" || $dataType == "WON" || $dataType == "LOST" || $dataType == "COLD" || $dataType == "RUNNING" || $dataType == "DEALCONVERSION" || $dataType == "PRIDICTION" || $dataType == "OFFLINELEAD" || $dataType == "MARKETINGLEAD" || $dataType == "DEMOMEETINGDONELEAD")) {

        	$Leadamount = Lead::query();
        	$Leadamount->selectRaw('SUM(wltrn_quotation.quot_whitelion_amount) AS whitelion_amount');
        	$Leadamount->selectRaw('SUM(wltrn_quotation.quot_billing_amount) AS billing_amount');
        	$Leadamount->selectRaw('SUM(wltrn_quotation.quot_other_amount) AS other_amount');
        	$Leadamount->selectRaw('SUM(wltrn_quotation.quot_total_amount) AS total_amount');
        	$Leadamount->leftJoin('wltrn_quotation', 'wltrn_quotation.inquiry_id', '=', 'leads.id');
        	$Leadamount->whereIn('leads.id', $dataIds);
        	$Leadamount->where('wltrn_quotation.isfinal', 1);
        	$Leadamount = $Leadamount->first();

        	if($Leadamount){
        	    // $total_whitelion_amount = $Leadamount->whitelion_amount;
        	    $DealBillingAmount = $Leadamount->billing_amount;
        	    // $total_other_amount = $Leadamount->other_amount;
        	    $DealTotalAmount = $Leadamount->total_amount;
        	}

			$searchColumns = array(
				0 => 'leads.id',
				1 => 'leads.first_name',
				2 => 'leads.last_name',
				3 => 'leads.email',
				4 => 'leads.phone_number',
				5 => 'leads.inquiry_id',
	
			);
	
			$sortingColumns = array(
				'leads.id',
				'leads.first_name',
				'leads.phone_number',
				'leads.status',
				'leads.site_stage',
				'leads.closing_date_time',
				'leads.assigned_to',
				'leads.user_id',
	
			);
	
			$selectColumns = array(
				'leads.id',
				'leads.first_name',
				'leads.phone_number',
				'leads.status',
				'leads.site_stage',
				'leads.is_deal',
				'leads.closing_date_time',
				'leads.assigned_to',
				'leads.user_id',
				'leads.last_name',
				'lead_owner.first_name as lead_owner_first_name',
				'lead_owner.last_name  as lead_owner_last_name',
				'created_by.first_name as created_by_first_name',
				'created_by.last_name  as created_by_last_name',
				'leads.source_type',
				'leads.inquiry_id',
	
			);
	
	
			$query = Lead::query();
			$query->whereIn('leads.id', $dataIds);
			$recordsTotal = $query->count();
			$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.
	
			$query = Lead::query();
			$query->leftJoin('users as lead_owner', 'lead_owner.id', '=', 'leads.assigned_to');
			$query->leftJoin('users as created_by', 'created_by.id', '=', 'leads.user_id');
			$query->select($selectColumns);
			$query->whereIn('leads.id', $dataIds);
			// $query->orderBy($sortingColumns[$request['order'][0]['column']], $request['order'][0]['dir']);
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
	
			$recordsFiltered = $query->count();
	
			$query = Lead::query();
			$query->leftJoin('users as lead_owner', 'lead_owner.id', '=', 'leads.assigned_to');
			$query->leftJoin('users as created_by', 'created_by.id', '=', 'leads.user_id');
			$query->whereIn('leads.id', $dataIds);
			$query->select($selectColumns);
			// $query->limit($request->length);
			// $query->offset($request->start);
			// $query->orderBy($sortingColumns[$request['order'][0]['column']], $request['order'][0]['dir']);
	
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
	
			$LeadStatus = getLeadStatus();
			foreach ($data as $key => $value) {
	
				if ($value['is_deal'] == 0) {
					$data[$key]['type'] = 'Lead';
				} else {
					$data[$key]['type'] = 'Deal';
				}
	
				if ($value['is_deal'] == 0) {
					$prifix = 'L';
				} else if ($value['is_deal'] == 1) {
					$prifix = 'D';
				}
	
				$data[$key]['id_lable'] = "#" . $prifix . $value['id'];

				
				$CRMSettingStageOfSite = CRMSettingStageOfSite::find($value['site_stage']);
            	if ($CRMSettingStageOfSite) {
					$site_stage = $CRMSettingStageOfSite->name;
            	}
				$data[$key]['sites_tage_name'] = $site_stage;

				$closing_date_time = $value['closing_date_time'];
            	if (($closing_date_time != '') || ($closing_date_time != null)) {

            	    $closing_date_time = date('Y-m-d', strtotime($closing_date_time));
            	} else {
            	    $closing_date_time = "-";

            	}
				$data[$key]['closing_date_time'] = $closing_date_time;

				if ($value['status'] != 0) {
					$data[$key]['lead_status_lable'] = $LeadStatus[$value['status']]['name'];
				} else {
					$data[$key]['lead_status_lable'] = "not define";
				}

			}

		} else if ((count($dataIds) != 0 || $dataType != '') && $dataType == "TARGET") {
			$searchColumns = array(
				0 => 'users.first_name',
				1 => 'users.last_name',
			);
	
			$columns = array(
				'wlmst_target.id',
				'wlmst_target.employeee_id',
				'wlmst_target.finyear_id',
				'wlmst_target.minachivement',
				'wlmst_target.total_target',
				'wlmst_target.distribute_type',
				'wlmst_target.created_at',
				'wlmst_target.updated_at',
				'wlmst_financialyear.name as financial_year',
				'users.first_name',
				'users.last_name',
				'users.status as user_status'
			);
	
			 // when there is no search parameter then total number rows = total number filtered rows.
	
			$chiledSalePersonId = getChildSalePersonsIds(Auth::user()->id);
			$query = Wlmst_target::query();
			$query->select($columns);
			$query->selectRaw("CONCAT(users.first_name,' ', users.last_name) AS sales_person_name");
			$query->leftJoin('users', 'users.id', '=', 'wlmst_target.employeee_id');
			$query->leftJoin('wlmst_financialyear', 'wlmst_financialyear.id', '=', 'wlmst_target.finyear_id');
			$query->where('users.status', 1);
			$query->whereIn('wlmst_target.id', $dataIds);
			// $query->limit($request->length);
			// $query->offset($request->start);
			// $query->orderBy($columns[$request['order'][0]['column']], $request['order'][0]['dir']);
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
	
			if(Auth::user()->id == 2)
			{
				$recordsTotal = count($data);
			}else
			{
				$recordsTotal = Wlmst_target::query();
				$recordsTotal->whereIn('wlmst_target.id', $dataIds);
				$recordsTotal = $recordsTotal->count();
			}
			$recordsFiltered = $recordsTotal;
	
			// echo "<pre>";
			// print_r(DB::getQueryLog());
			// die;
	
			$data = json_decode(json_encode($data), true);
	
			if ($isFilterApply == 1) {
				$recordsFiltered = count($data);
			}
	
			foreach ($data as $key => $value) {
				$user_status = $value['user_status'];
				$user_status_lable = getUserStatusLable($value['user_status']);
	
				$total_target = 0;
				$montly_target = Wlmst_targetdetail::query();
				$montly_target->select('target_amount');
				$montly_target->where('wlmst_targetdetail.target_id', $value['id']);
				$montly_target->where('wlmst_targetdetail.month_number', Carbon::parse($req_startDate)->month);
				$montly_target = $montly_target->first();
				
				if($montly_target){
					$total_target = (int)$montly_target->target_amount;
					$data[$key]['target_amount'] = (int)$montly_target->target_amount;
				} else {
					$data[$key]['target_amount'] = 0;
				}
	
	
				if ($request->view_type == 0 || $request->view_type == '') {
					$start_year = explode("-", $value['financial_year'])[0];
					$end_year = explode("-", $value['financial_year'])[1];
					$start_date = '01-04-' . $start_year;
					$end_date = '31-03-' . $end_year;
	
					$startDate = date('Y-m-d 00:00:00', strtotime($start_date));
	
					$endDate = date('Y-m-d 00:00:00', strtotime($end_date));
				} else {
	
					$startDate = getDatesFromMonth($request->view_type, $value['financial_year'])['start'];
	
					$endDate = getDatesFromMonth($request->view_type, $value['financial_year'])['end'];
				}
	
	
	
				$childSalePersonsIds = getChildSalePersonsIds($value['employeee_id']);
	
				$allSalesUserIds = array_unique($childSalePersonsIds);
				$allSalesUserIds = array_values($allSalesUserIds);
	
				$orderAmount = Invoice::query();
				$orderAmount->selectRaw('SUM(invoice.total_mrp_minus_disocunt) as amount');
				$orderAmount->leftJoin('orders', 'orders.id', '=', 'invoice.order_id');
				$orderAmount->whereIn('invoice.status', array(2, 3));
				$orderAmount->where('orders.created_at', '>=', $startDate);
				$orderAmount->where('orders.created_at', '<=', $endDate);
				$orderAmount->whereIn('orders.user_id', $allSalesUserIds);
				$orderAmount = $orderAmount->first();
	
	
				if ($orderAmount != null) {
					$achieved_amt = ($orderAmount->amount == '') ? '00.00' : $orderAmount->amount;
					$achieved_per = getpercentage($total_target, $achieved_amt);
				} else {
					$achieved_amt = 0;
					$achieved_per = 0;
				}
	
				$data[$key]['achieved_amt'] = (int)$achieved_amt;
				$data[$key]['achieved_per'] = (int)$achieved_per;				
			}
		} else if ((count($dataIds) != 0 || $dataType != '') && $dataType == "REWARD_ARCHITECT" || $dataType == "REWARD_ELECTRICIAN") {

			$searchColumns = array(
				'gift_product_orders.id',
				'gift_product_orders.total_point_value',
				'reporting_sale.first_name',
				'reporting_sale.last_name',
				'users.first_name',
				'users.last_name',
			);
	
			$sortingColumns = array(
				0 => 'gift_product_orders.id',
				1 => 'gift_product_orders.created_at',
				2 => 'gift_product_orders.user_id',
				3 => 'gift_product_orders.total_point_value',
				5 => 'gift_product_orders.dispatch_detail',
				6 => 'gift_product_orders.total_cashback',
				7 => 'gift_product_orders.total_cash',
				8 => 'gift_product_orders.status',
	
			);
	
			$selectColumns = array(
				'gift_product_orders.id',
				'gift_product_orders.created_at',
				'gift_product_orders.created_at',
				'gift_product_orders.total_point_value',
				'gift_product_orders.cash_point_value',
				'gift_product_orders.status',
				'gift_product_orders.cash_status',
				'gift_product_orders.cashback_status',
				'gift_product_orders.track_id',
				'gift_product_orders.dispatch_detail',
				'users.first_name',
				'users.last_name',
				'users.type',
				'users.id as user_id',
				'gift_product_orders.total_cashback',
				'gift_product_orders.total_cash',
				'reporting_sale.first_name as reporting_sale_first_name',
				'reporting_sale.last_name as reporting_sale_last_name',
			);
			if ($dataType == "REWARD_ARCHITECT") {
				$selectColumns[] = 'architect.sale_person_id';
			} else if ($dataType == "REWARD_ELECTRICIAN") {
				$selectColumns[] = 'electrician.sale_person_id';
			}
	
			$query = GiftProductOrder::query();
			$query->leftJoin('users', 'users.id', '=', 'gift_product_orders.user_id');
			$query->whereIn('gift_product_orders.id', $dataIds);
			//$query->where('gift_product_orders.user_id', Auth::user()->id);
			$recordsTotal = $query->count();
			$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.
			$query = GiftProductOrder::query();
			$query->leftJoin('users', 'users.id', '=', 'gift_product_orders.user_id');
			$query->whereIn('gift_product_orders.id', $dataIds);
	
			if ($dataType == "REWARD_ARCHITECT") {
				$query->leftJoin('architect', 'architect.user_id', '=', 'gift_product_orders.user_id');
				$query->leftJoin('users as reporting_sale', 'reporting_sale.id', '=', 'architect.sale_person_id');
			} else if ($dataType == "REWARD_ELECTRICIAN") {
				$query->leftJoin('electrician', 'electrician.user_id', '=', 'gift_product_orders.user_id');
				$query->leftJoin('users as reporting_sale', 'reporting_sale.id', '=', 'electrician.sale_person_id');
			}
	
			$query->select($selectColumns);
			// $query->limit($request->length);
			// $query->offset($request->start);
			// $query->orderBy($sortingColumns[$request['order'][0]['column']], $request['order'][0]['dir']);
	
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
				$data[$key]['Date_Time'] = convertDateTime($value['created_at']);
				$data[$key]['total_point'] = (int) $value['total_point_value'];
				$data[$key]['total_amount'] = (int) $value['total_cashback'] + (int) $value['total_cash'];
			}
	
		}


        $jsonData = successRes("Success");
        $jsonData['data'] = $data;
		$jsonData['report_total_amount'] = $DealTotalAmount;
        $jsonData['report_billing_amount'] = $DealBillingAmount;
        return $jsonData;
    }
}
