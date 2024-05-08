<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Architect;
use App\Models\Electrician;
use App\Models\Wlmst_targetdetail;
use App\Models\Wlmst_target;
use App\Models\Order;
use App\Models\Wltrn_Quotation;
use App\Models\Wltrn_QuotItemdetail;
use App\Models\User;
use App\Models\StateList;
use App\Models\CityList;
use App\Models\ChannelPartner;
use App\Models\GiftProductOrder;
use App\Models\CRMSettingStageOfSite;
use App\Models\LeadSource;
use App\Models\Exhibition;
use App\Models\Lead;
use App\Models\Invoice;
use Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Mail;
use Carbon\Carbon;
use DB;

class DashboardReportController extends Controller
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
	
    function dashboardOrderReport(Request $request)
    {
        $isSalePerson = isSalePerson();
        $dataIds = explode(',', $request->data_ids);
        $dataType = $request->data_type;
		$req_startDate = date('Y-m-d', strtotime($request->start_date));
		$req_endDate = date('Y-m-d', strtotime($request->end_date));
		$recordsTotal = 0;
		$recordsFiltered = 0;

		$DealBillingAmount = 0;
        $DealTotalAmount = 0;

		$data = array();
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
				6 => 'orders.status',
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
            $query->limit($request->length);
            $query->offset($request->start);
            $query->orderBy($sortingColumns[$request['order'][0]['column']], $request['order'][0]['dir']);

            $isFilterApply = 0;
			$search_value = '';
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
                $data[$key]['col_1'] =
                    '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">#' .
                    highlightString($value['id'],$search_value) .
                    '</a></h5>
                	<p class="text-muted mb-0" data-bs-toggle="tooltip"  data-bs-original-title="' .
					highlightString(convertOrderDateTime($value['created_at'], 'time'),$search_value) .
                    '" >' .
                    highlightString(convertOrderDateTime($value['created_at'], 'date'),$search_value) .
                    '</p>';

                $paymentMode = '';

                $paymentMode = getPaymentLable($value['payment_mode']);
                $channelPartnerType = '<span class="badge rounded-pill badge-soft-dark font-size-11">' . $channelPartner[$value['channel_partner_type']]['short_name'] . '</span>';
                $data[$key]['col_2'] = '<p class="text-muted mb-0" data-bs-toggle="tooltip" title="' . $value['first_name'] . '  ' . $value['last_name'] . '">' . highlightString(displayStringLenth($value['first_name'] . '  ' . $value['last_name'], 10),$search_value) . '</p>';
                $data[$key]['col_3'] = '<p class="text-muted mb-0 text-center" data-bs-toggle="tooltip" title="' .$value['channel_partner_user_first_name'] .' ' .$value['channel_partner_user_last_name'] .'&#013;&#013; PHONE:' .$value['channel_partner_user_phone_number'] .'" >' .highlightString(displayStringLenth($value['firm_name'], 15),$search_value) .'</p><p class="text-muted mb-0 text-center">' .$channelPartnerType .' ' .$paymentMode .'</p>';
                $sale_persons = explode(',', $value['sale_persons']);

                $Users = User::select('users.first_name', 'users.last_name', 'users.type', 'users.phone_number', 'sales_hierarchy.code as sales_hierarchy_code')
                    ->leftJoin('sale_person', 'sale_person.id', '=', 'users.reference_id')
                    ->leftJoin('sales_hierarchy', 'sales_hierarchy.id', '=', 'sale_person.type')
                    ->whereIn('users.id', $sale_persons)
                    ->get();

                $uiSalePerson = '<ul class="list-inline font-size-20 contact-links mb-0">';
                foreach ($Users as $kU => $vU) {
                    $uiSalePerson .= '<li class="list-inline-item px-2">';
                    $uiSalePerson .= '<a  data-bs-toggle="tooltip" title="' . $vU['first_name'] . ' ' . $vU['last_name'] . '&#013;' . $vU['sales_hierarchy_code'] . '&#013; PHONE:' . $vU['phone_number'] . '" href="javascript: void(0);" ><i class="bx bx-user"></i></a>';
                    $uiSalePerson .= '</li>';
                }

                $uiSalePerson .= '</ul>';

                $data[$key]['col_4'] = $uiSalePerson;

                $data[$key]['col_5'] =
                    '<p class="text-muted mb-0">EXGST&nbsp&nbsp;&nbsp;&nbsp;: <i class="fas fa-rupee-sign"></i> <span class="price-lable-font">' .
                    highlightString(priceLable($value['total_mrp_minus_disocunt']),$search_value) .
                    '</span></p><p class="text-muted mb-0 ">TOTAL&nbsp;&nbsp;&nbsp;&nbsp: <i class="fas fa-rupee-sign"></i> <span class="price-lable-font">' .
                    highlightString(priceLable($value['total_payable']),$search_value) .
                    '</span></p>';

                // $data[$key]['col_6'] = '';

                // if ($value['status'] == 1 || $value['status'] == 2) {
                //     $data[$key]['col_6'] = getInvoiceLable($value['sub_status']);
                // }
				
                // $data[$key]['status'] = getOrderLable($value['status']);
                // if ($data[$key]['status'] != '') {
				// 	$data[$key]['col_6'] = $data[$key]['status'] . '-' . $data[$key]['col_6'];
                // }
				
				$data[$key]['col_7'] = '';
				$data[$key]['col_6'] = getOrderLable($value['status']);
				
            }
        } else if ((count($dataIds) != 0 || $dataType != '') && ($dataType == "ARCHITECT" || $dataType == "NEWARCHITECT" || $dataType == "ACTIVEARCHITECT")) {

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
			$search_value = '';

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
			$query->limit($request->length);
			$query->offset($request->start);
			$query->orderBy($sortingColumns[$request['order'][0]['column']], $request['order'][0]['dir']);
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
	
				$data[$key] = array();
				$data[$key]['col_1'] = highlightString($value['id'],$search_value);
				
					$data[$key]['col_2'] = '<a href="javascript: void(0);" class="">' . highlightString($value['first_name'] . " " . $value['last_name'],$search_value) . '</a>
					<p class="text-muted mb-0">' . highlightString(getUserTypeName($value['type']),$search_value) . '</p>';
				
	
				if ($value['type'] == 202) {
	
					$data[$key]['col_3'] = '<p class="text-muted mb-0">' . highlightString($value['email'],$search_value) . ' <span class="badge rounded-pill bg-success">PRIME</span></p>
				 <p class="text-muted mb-0">' . highlightString($value['phone_number'],$search_value) . '</p>';
				} else {
	
					$data[$key]['col_3'] = '
				 <p class="text-muted mb-0">' . highlightString($value['phone_number'],$search_value) . '</p>';
				}

				$data[$key]['col_4'] = '<p class="text-muted mb-0">' . highlightString($value['sale_person_first_name'] . ' ' . $value['sale_person_last_name'],$search_value) . '</p>';

				$data[$key]['col_5'] = 'Currunt : ' . highlightString($value['total_point_current'],$search_value) . '</br>Lifetime : ' . highlightString($value['total_point'],$search_value);
				$data[$key]['col_7'] = '';
				$data[$key]['col_6'] = getArchitectsStatusLable($value['status']);
	
			}
		} else if ((count($dataIds) != 0 || $dataType != '') && ($dataType == "ELECTRICIAN" || $dataType == "NEWELECTRICIAN" || $dataType == "ACTIVEELECTRICIAN")) {
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
			$query->orderBy($sortingColumns[$request['order'][0]['column']], $request['order'][0]['dir']);
	
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
			$query->limit($request->length);
			$query->offset($request->start);
				$query->orderBy($sortingColumns[$request['order'][0]['column']], $request['order'][0]['dir']);
	
			$isFilterApply = 0;
			$search_value = '';
	
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
	
				$data[$key] = array();
				$data[$key]['col_1'] = highlightString($value['id'],$search_value);
				
				if ($value['type'] == 301) {
					
					$data[$key]['col_2'] = '<a onclick="inquiryLogs(' . $value['id'] . ')" href="javascript: void(0);" class="">' . highlightString($value['first_name'] . " " . $value['last_name'],$search_value) . '</a>
					<p class="text-muted mb-0">' . highlightString(getUserTypeName($value['type']),$search_value) . '</p>';
				} else if ($value['type'] == 302) {
					
					$data[$key]['col_2'] = '<a onclick="inquiryLogs(' . $value['id'] . ')" href="javascript: void(0);" class="">' . highlightString($value['first_name'] . " " . $value['last_name'],$search_value) . ' </a> <span class="badge rounded-pill bg-success">PRIME</span>
					<p class="text-muted mb-0">' . highlightString(getUserTypeName($value['type']),$search_value) . '</p>';
				}
				
				$data[$key]['col_3'] = '<p class="text-muted mb-0">' . highlightString($value['phone_number'],$search_value) . '</p>';
	
	
	
				$data[$key]['col_4'] = "";
	
				$salePerson = User::query();
				$salePerson->select('users.id as id', DB::raw('CONCAT(first_name," ", last_name) AS text'));
				$salePerson = $salePerson->find($value['sale_person_id']);
				if ($salePerson) {
	
					$data[$key]['col_4'] = '<p class="text-muted mb-0">' . highlightString($salePerson->text,$search_value) . '</p>';
				}

				$data[$key]['col_5'] = 'Currunt : ' . highlightString($value['total_point_current'],$search_value) . '</br>Lifetime : ' . highlightString($value['total_point'],$search_value);
				$data[$key]['col_7'] = '';
				$data[$key]['col_6'] = getElectricianStatusStatusLable($value['status']);
	
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
        	    $DealBillingAmount = numCommaFormat($Leadamount->billing_amount);
        	    // $total_other_amount = $Leadamount->other_amount;
        	    $DealTotalAmount = numCommaFormat($Leadamount->total_amount);
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
				'leads.is_deal',
				'leads.site_stage',
				'leads.closing_date_time',
				'leads.assigned_to',
				'leads.user_id',
				'leads.last_name',
				'lead_owner.first_name as lead_owner_first_name',
				'lead_owner.last_name  as lead_owner_last_name',
				'created_by.first_name as created_by_first_name',
				'created_by.last_name  as created_by_last_name',
				'leads.source_type',
				'leads.source',
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
			$query->orderBy($sortingColumns[$request['order'][0]['column']], $request['order'][0]['dir']);
			$search_value = '';
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
			$query->limit($request->length);
			$query->offset($request->start);
			$query->orderBy($sortingColumns[$request['order'][0]['column']], $request['order'][0]['dir']);
	
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
	
					$routeLead = route('crm.lead') . "?id=" . $value['id'];
				} else {
	
					$routeLead = route('crm.deal') . "?id=" . $value['id'];
				}
	
				$data[$key] = array();
				if ($value['inquiry_id'] != 0) {
					$inquiry_id = " - " . $value['inquiry_id'];
				} else {
					$inquiry_id = '';
				}
	
				if ($value['is_deal'] == 0) {
					$prifix = 'L';
				} else if ($value['is_deal'] == 1) {
					$prifix = 'D';
				}
	
				$data[$key]['col_1'] = "<a href='" . $routeLead . "' > " . "#" . highlightString($prifix . $value['id'],$search_value) . highlightString($inquiry_id,$search_value) . "</a>";

				$data[$key]['col_2'] = '<p class="text-muted mb-0">' . highlightString($value['first_name'] . " " . $value['last_name'],$search_value) . '</p>
				<p class="text-muted mb-0">' . highlightString($value['phone_number'],$search_value) . '</p>';
	
				$CRMSettingStageOfSite = CRMSettingStageOfSite::find($value['site_stage']);
				$site_stage = "";
            	if ($CRMSettingStageOfSite) {
					$site_stage = $CRMSettingStageOfSite->name;
            	}
				$data[$key]['col_3'] = highlightString($site_stage,$search_value);

				$closing_date_time = $value['closing_date_time'];
            	if (($closing_date_time != '') || ($closing_date_time != null)) {

            	    $closing_date_time = date('Y-m-d', strtotime($closing_date_time));
            	} else {
            	    $closing_date_time = "-";

            	}

				if ($value['is_deal'] == 0) {
					$data[$key]['col_4'] = "-";
				} else if ($value['is_deal'] == 1) {
					$data[$key]['col_4'] = highlightString($closing_date_time,$search_value);
				}


				$source_type = explode("-", $value['source_type']);

				$sourceType = '';
				foreach (getLeadSourceTypes() as $skey => $svalue) {
					if ($svalue['type'] == $source_type[0] && $svalue['id'] == $source_type[1]) {
						$sourceType = $svalue['lable'];
					}
				}

				$source = '';
				$source .= '<span>'. highlightString($value['lead_owner_first_name'] . ' ' . $value['lead_owner_last_name'],$search_value).'</span>';
				$source .= '<div class="border my-1"></div>';

				if($source_type[0] == 'user') {
					if(in_array($source_type[1], array(101, 102, 103, 104, 105))) {
						$sourceUser = ChannelPartner::select('firm_name')->where('user_id', $value['source'])->first();
						if($sourceUser) {
							$source .= '<span>'.$sourceUser['firm_name'] .'</span> - <span class="badge badge-pill badge-soft-success">' .$sourceType. '</span>';
						} else {
							$source .= '';
						}
					} else {
						$sourceUser = User::find($value['source']);
						if($sourceUser) {
							$source .= '<span>'.$sourceUser['first_name'] .''. $sourceUser['last_name'] .'</span> - <span class="badge badge-pill badge-soft-success">' .$sourceType. '</span>';
						} else {
							$source .= '';
						}
					}
				} else if($source_type[0] == 'exhibition') {
					$sourceUser = Exhibition::find($value['source']);
					if($sourceUser) {
						$source .= '<span>'.highlightString($sourceUser['name'],$search_value) . '</span> - <span class="badge badge-pill badge-soft-success">' .$sourceType. '</span>';
					} else {
						$source .= '';
					}
				} else {
					$source .= '<span>'. highlightString($value['source'],$search_value) .'</span> - <span class="badge badge-pill badge-soft-success">' .$sourceType. '</span>';
				}
				$source .= '';


				$data[$key]['col_5'] =  $source;
				if ($value['status'] != 0) {
					$data[$key]['col_6'] = highlightString($LeadStatus[$value['status']]['name'],$search_value);
				} else {
					$data[$key]['col_6'] = "not define";
				}

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
        		$Leadamount->where('leads.id', $value['id']);
        		$Leadamount->where('wltrn_quotation.isfinal', 1);
        		$Leadamount = $Leadamount->first();

        		if($Leadamount){
        		    $total_whitelion_amount = numCommaFormat($Leadamount->whitelion_amount);
        		    // $total_billing_amount = numCommaFormat($Leadamount->billing_amount);
        		    $total_other_amount = numCommaFormat($Leadamount->other_amount);
        		    $total_amount = numCommaFormat($Leadamount->total_amount);
        		}
			

				$data[$key]['col_7'] = '<p class="text-muted mb-0">Whitelion : ' . $total_whitelion_amount . '</p>
				<p class="text-muted mb-0">Other : ' . $total_other_amount . '</p>
				<p class="text-muted mb-0">Total : ' . $total_amount . '</p>';

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
			$query->limit($request->length);
			$query->offset($request->start);
			$query->orderBy($columns[$request['order'][0]['column']], $request['order'][0]['dir']);
			$isFilterApply = 0;
	
			$search_value = '';
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
	
				$data[$key]['col_1'] = ($key+1);
				$data[$key]['col_2'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" onclick="targetView(\'' . $value['id'] . '\',\'' . $value['employeee_id'] . '\',\'' . $value['finyear_id'] . '\',\'' . $value['financial_year'] . '\')" >' . highlightString($data[$key]['sales_person_name'],$search_value) . '</span></a> ' . $user_status_lable . '</h5>
				<p class="text-muted mb-0">' . $data[$key]['financial_year'] . '</p>';
	
				$total_target = 0;
				$montly_target = Wlmst_targetdetail::query();
				$montly_target->select('target_amount');
				$montly_target->where('wlmst_targetdetail.target_id', $value['id']);
				$montly_target->where('wlmst_targetdetail.month_number', Carbon::parse($req_startDate)->month);
				$montly_target = $montly_target->first();
				
				if($montly_target){
					$total_target = (int)$montly_target->target_amount;
					$data[$key]['col_3'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">RS. ' . (int)$montly_target->target_amount . '</a></h5>';
				} else {
					$data[$key]['col_3'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">RS. 0</a></h5>';
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
	
				if ($achieved_per < $value['minachivement']) {
					$achievecolour = 'text-danger';
				} elseif ($achieved_per < 100.00 && $achieved_per >= $value['minachivement']) {
					$achievecolour = 'text-primary';
				} elseif ($achieved_per >= 100) {
					$achievecolour = 'text-success';
				} else {
					$achievecolour = 'text-dark';
				}
	
				$data[$key]['col_4'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="' . $achievecolour . '">RS. ' . (int)$achieved_amt . '</a></h5>';
				$data[$key]['col_5'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="' . $achievecolour . '">' . (int)$achieved_per . '%</a></h5>';
	
				$data[$key]['col_7'] = '';
				$data[$key]['col_6'] = '';
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
			$query->limit($request->length);
			$query->offset($request->start);
			$query->orderBy($sortingColumns[$request['order'][0]['column']], $request['order'][0]['dir']);
	
			$isFilterApply = 0;
			$search_value = '';

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
				$data[$key]['col_1'] = "<a href='javascript:void(0)' onclick='getGiftOrderLog(" . $value['id'] . ")' > #" . highlightString($value['id'],$search_value) . "</a>";
				$data[$key]['col_2'] = highlightString(convertDateTime($value['created_at']),$search_value);
				if ($value['type'] == 201 || $value['type'] == 202) {
					$routeArchitects = route('new.architects.index') . '?id=' . $value['user_id'];
					$data[$key]['col_3'] = "<a target='_blank' href='" . $routeArchitects . "' >" . highlightString($value['first_name'] . " " . $value['last_name'],$search_value) . "</a>";
				} else if ($value['type'] == 301 || $value['type'] == 302) {
					$routeElectrician = route('new.electricians.index') . "?id=" . $value['user_id'];
					$data[$key]['col_3'] = "<a target='_blank' href='" . $routeElectrician . "' >" . highlightString($value['first_name'] . " " . $value['last_name'],$search_value) . "</a>";
				}
				$data[$key]['col_4'] = highlightString($value['reporting_sale_first_name'] . " " . $value['reporting_sale_last_name'],$search_value);
				$data[$key]['col_5'] = "" . (int) $value['total_point_value'];
				$data[$key]['col_7'] = '';
				$data[$key]['col_6'] = (int) $value['total_cashback'] + (int) $value['total_cash'];
			}
	
		} else if ((count($dataIds) != 0 || $dataType != '') && ($dataType == "EXECUTIVES" || $dataType == "NEWEXECUTIVES" || $dataType == "ACTIVEEXECUTIVES")) {
			$searchColumns = array(
				0 => 'users.id',
				1 => 'users.first_name',
				2 => 'users.last_name',
				3 => 'users.email',
				4 => 'users.phone_number',
				5 => "CONCAT(users.first_name,' ',users.last_name)",	
			);
	
			$sortingColumns = array(
				0 => 'users.id',
				1 => 'users.first_name',
				2 => 'users.email',
				3 => 'users.status',
			);
	
			$selectColumns = array(
				'users.id',
				'users.first_name',
				'users.last_name',
				'users.email',
				'users.phone_number',
				'users.status',
			);
	
			$query = User::query();
			$query->where('type', 2);
			$query->whereIn('users.id', $dataIds);
			$recordsTotal = $query->count();
			$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.
	
			$query = User::query();
			$query->where('type', 2);
			$query->whereIn('users.id', $dataIds);
			$query->select('users.id');
			$isFilterApply = 0;
			$search_value = '';

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
	
			$query = User::query();
			$query->where('type', 2);
			$query->whereIn('id', $dataIds);
			$query->select($selectColumns);
			$query->limit($request->length);
			$query->offset($request->start);
			$query->orderBy($sortingColumns[$request['order'][0]['column']], $request['order'][0]['dir']);
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
	
				$data[$key] = array();
				$data[$key]['col_1'] = highlightString($value['id'],$search_value);
				
				$data[$key]['col_2'] = '<a href="javascript: void(0);" class="">' . highlightString($value['first_name'] . " " . $value['last_name'],$search_value) . '</a>';
				
				$data[$key]['col_3'] = '<p class="text-muted mb-0">' . highlightString($value['phone_number'],$search_value) . '</p>';

				$data[$key]['col_4'] = '<p class="text-muted mb-0">' . highlightString($value['email'],$search_value) . ' </p>';

				$data[$key]['col_5'] = getUserStatusLable($value['status']);
				$data[$key]['col_7'] = '';
				$data[$key]['col_6'] = '';
	
			}
		} else if ((count($dataIds) != 0 || $dataType != '') && ($dataType == "ADM" || $dataType == "NEWADM" || $dataType == "ACTIVEADM")) {
			$searchColumns = array(
				0 => 'users.id',
				1 => 'users.first_name',
				2 => 'users.last_name',
				3 => 'users.email',
				4 => 'users.phone_number',
				5 => "CONCAT(users.first_name,' ',users.last_name)",	
				6 => 'channel_partner.firm_name',
				7 => 'sale_person.first_name',
				8 => 'sale_person.last_name',
			);
	
			$sortingColumns = array(
				0 => 'users.id',
				1 => 'users.first_name',
				2 => 'users.email',
				3 => 'users.status',
			);
	
			$selectColumns = array(
				'users.id',
				'users.first_name',
				'users.last_name',
				'users.email',
				'users.phone_number',
				'users.status',
				'channel_partner.firm_name',
				'channel_partner.sale_persons',
				'sale_person.first_name as sale_person_first_name',
				'sale_person.last_name  as sale_person_last_name',
			);
	
			$query = User::query();
			$query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
			$query->leftJoin('users as sale_person', 'channel_partner.sale_persons', '=', 'sale_person.id');
			$query->where('users.type', 102);
			$query->whereIn('users.id', $dataIds);
			$recordsTotal = $query->count();
			$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.
	
			$query = User::query();
			$query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
			$query->leftJoin('users as sale_person', 'channel_partner.sale_persons', '=', 'sale_person.id');
			$query->where('users.type', 102);
			$query->whereIn('users.id', $dataIds);
			$query->select('users.id');
			$isFilterApply = 0;
			$search_value = '';

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

			$query = User::query();
			$query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
			$query->leftJoin('users as sale_person', 'channel_partner.sale_persons', '=', 'sale_person.id');
			$query->where('users.type', 102);
			$query->whereIn('users.id', $dataIds);
			$query->select($selectColumns);
			$query->limit($request->length);
			$query->offset($request->start);
			$query->orderBy($sortingColumns[$request['order'][0]['column']], $request['order'][0]['dir']);
			$isFilterApply = 0;
			$search_value = '';
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
				$data[$key] = array();
				$data[$key]['col_1'] = highlightString($value['id'],$search_value);
				
				$data[$key]['col_2'] = '<a href="javascript: void(0);">' . highlightString($value['first_name'] . " " . $value['last_name'],$search_value) . '</a>';
				
				$data[$key]['col_4'] = '<p class="text-muted mb-0">' . highlightString($value['firm_name'],$search_value) . ' </p>';
				$data[$key]['col_3'] = '<p class="text-muted mb-0">' . highlightString($value['phone_number'],$search_value) . '</p><p class="text-muted mb-0">' . highlightString($value['email'],$search_value) . ' </p>';


				$data[$key]['col_5'] = '<p class="text-muted mb-0">' . highlightString($value['sale_person_first_name'] . ' ' . $value['sale_person_last_name'],$search_value) . ' </p>';
				$data[$key]['col_6'] = getUserStatusLable($value['status']);
				$data[$key]['col_7'] = '';
			}
		} else if ((count($dataIds) != 0 || $dataType != '') && ($dataType == "AD" || $dataType == "NEWAD" || $dataType == "ACTIVEAD")) {
			$searchColumns = array(
				0 => 'users.id',
				1 => 'users.first_name',
				2 => 'users.last_name',
				3 => 'users.email',
				4 => 'users.phone_number',
				5 => "CONCAT(users.first_name,' ',users.last_name)",	
				6 => 'channel_partner.firm_name',
				7 => 'sale_person.first_name',
				8 => 'sale_person.last_name',
			);
	
			$sortingColumns = array(
				0 => 'users.id',
				1 => 'users.first_name',
				2 => 'users.email',
				3 => 'users.status',
			);
	
			$selectColumns = array(
				'users.id',
				'users.first_name',
				'users.last_name',
				'users.email',
				'users.phone_number',
				'users.status',
				'channel_partner.firm_name',
				'channel_partner.sale_persons',
				'sale_person.first_name as sale_person_first_name',
				'sale_person.last_name  as sale_person_last_name',
			);
	
			$query = User::query();
			$query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
			$query->leftJoin('users as sale_person', 'channel_partner.sale_persons', '=', 'sale_person.id');
			$query->where('users.type', 104);
			$query->whereIn('users.id', $dataIds);
			$recordsTotal = $query->count();
			$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.
	
			$query = User::query();
			$query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
			$query->leftJoin('users as sale_person', 'channel_partner.sale_persons', '=', 'sale_person.id');
			$query->where('users.type', 104);
			$query->whereIn('users.id', $dataIds);
			$query->select('users.id');
			$isFilterApply = 0;
			$search_value = '';

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

			$query = User::query();
			$query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
			$query->leftJoin('users as sale_person', 'channel_partner.sale_persons', '=', 'sale_person.id');
			$query->where('users.type', 104);
			$query->whereIn('users.id', $dataIds);
			$query->select($selectColumns);
			$query->limit($request->length);
			$query->offset($request->start);
			$query->orderBy($sortingColumns[$request['order'][0]['column']], $request['order'][0]['dir']);
			$isFilterApply = 0;
			$search_value = '';
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
				$data[$key] = array();
				$data[$key]['col_1'] = highlightString($value['id'],$search_value);
				
				$data[$key]['col_2'] = '<a href="javascript: void(0);">' . highlightString($value['first_name'] . " " . $value['last_name'],$search_value) . '</a>';
				
				$data[$key]['col_4'] = '<p class="text-muted mb-0">' . highlightString($value['firm_name'],$search_value) . ' </p>';
				$data[$key]['col_3'] = '<p class="text-muted mb-0">' . highlightString($value['phone_number'],$search_value) . '</p><p class="text-muted mb-0">' . highlightString($value['email'],$search_value) . ' </p>';


				$data[$key]['col_5'] = '<p class="text-muted mb-0">' . highlightString($value['sale_person_first_name'] . ' ' . $value['sale_person_last_name'],$search_value) . ' </p>';
				$data[$key]['col_6'] = getUserStatusLable($value['status']);
				$data[$key]['col_7'] = '';
			}
		}

        $jsonData = [
            'draw' => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            'recordsTotal' => intval($recordsTotal), // total number of records
            'recordsFiltered' => intval($recordsFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
            'data' => $data, // total data array
            'report_total_amount' => $DealTotalAmount, // total data array
            'report_billing_amount' => $DealBillingAmount, // total data array
        ];
        return $jsonData;
    }

	function dashboardSaleExecutiveReport(Request $request)
	{
		$isSalePerson = isSalePerson();
		$dataIds = explode(',', $request->data_ids);
		$dataType = $request->data_type;
		$State = $request->state_id;
		$City = $request->city_id;
		$req_startDate = date('Y-m-d', strtotime($request->start_date));
		$req_endDate = date('Y-m-d', strtotime($request->end_date));
		$recordsTotal = 0;
		$recordsFiltered = 0;

		$DealBillingAmount = 0;
		$DealTotalAmount = 0;

		$data = array();


		if($dataType == "TARGET") {
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
				'users.status as user_status'
			);
	
			// when there is no search parameter then total number rows = total number filtered rows.
	
			$chiledSalePersonId = getChildSalePersonsIds(Auth::user()->id);
			$query = Wlmst_target::query();
			$query->select($columns);
			$query->selectRaw("CONCAT(users.first_name,' ', users.last_name) AS sale_person_name");
			$query->leftJoin('users', 'users.id', '=', 'wlmst_target.employeee_id');
			$query->leftJoin('wlmst_financialyear', 'wlmst_financialyear.id', '=', 'wlmst_target.finyear_id');
			$query->where('users.status', 1);
			$query->limit(6);
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
	
			if (Auth::user()->id == 2) {
				$recordsTotal = count($data);
			} else {
				$recordsTotal = Wlmst_target::query();
				$recordsTotal->leftJoin('users', 'users.id', '=', 'wlmst_target.employeee_id');
				$recordsTotal->where('users.status', 1);
				$recordsTotal->limit(6);
				$recordsTotal = $recordsTotal->count();
			}
			$recordsFiltered = $recordsTotal;
	
			$data = json_decode(json_encode($data), true);
	
			if ($isFilterApply == 1) {
				$recordsFiltered = count($data);
			}
	
			foreach ($data as $key => $value) {
				$user_status = $value['user_status'];
				$user_status_lable = getUserStatusLable($value['user_status']);
	
				$data[$key]['col_1'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" onclick="targetView(\'' . $value['id'] . '\',\'' . $value['employeee_id'] . '\',\'' . $value['finyear_id'] . '\',\'' . $value['financial_year'] . '\')" >' . $data[$key]['sale_person_name'] . '</span></a> ' . $user_status_lable . '</h5>
				<p class="text-muted mb-0"></p>';
	
				$total_target = 0;
				$monthlyTarget = Wlmst_targetdetail::query()->select('target_amount')->where('wlmst_targetdetail.target_id', $value['id'])->where('wlmst_targetdetail.month_number', Carbon::parse($req_startDate)->month)->first();
	
				if ($monthlyTarget) {
					$totalTarget = (int)$monthlyTarget->target_amount;
	
					// $startDate = ($request->view_type == 0 || $request->view_type == '') ? date('Y-m-d 00:00:00', strtotime('01-04-' . explode("-", $value['financial_year'])[0])) : getDatesFromMonth($request->view_type, $value['financial_year'])['start'];
	
					// $endDate = ($request->view_type == 0 || $request->view_type == '') ? date('Y-m-d 00:00:00', strtotime('31-03-' . explode("-", $value['financial_year'])[1])) : getDatesFromMonth($request->view_type, $value['financial_year'])['end'];
	
					$childSalePersonsIds = getChildSalePersonsIds($value['employeee_id']);
					$allSalesUserIds = array_unique($childSalePersonsIds);
					$allSalesUserIds = array_values($allSalesUserIds);
	
					$orderAmount = Invoice::query();
					$orderAmount->selectRaw('SUM(invoice.total_mrp_minus_disocunt) as amount');
					$orderAmount->leftJoin('orders', 'orders.id', '=', 'invoice.order_id');
					$orderAmount->whereIn('invoice.status', [2, 3]);
					$orderAmount->where('orders.created_at', '>=', $req_startDate);
					$orderAmount->where('orders.created_at', '<=', $req_endDate);
					$orderAmount->whereIn('orders.user_id', $allSalesUserIds);
					$orderAmount = $orderAmount->first();
	
					$achievedAmt = ($orderAmount && $orderAmount->amount != '') ? $orderAmount->amount : 0;
					$achievedPer = getpercentage($totalTarget, $achievedAmt);
					
					$proccessBar = "";
					if($achievedPer == 0) {
						$proccessBar .= '<div class="progress progress-xl justify-content-center" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="bottom" title="Total Target : '.$totalTarget.'<br>Achieved Target : '.$achievedAmt.'<br>Achieved Per : '.$achievedPer.'%">';
						$proccessBar .= '0.00%';
						$proccessBar .= '</div>';
					} else {
						$proccessBar .= '<div class="progress progress-xl" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="bottom" title="Total Target : '.$totalTarget.'<br>Achieved Target : '.$achievedAmt.'<br>Achieved Per : '.$achievedPer.'%">';
						$proccessBar .= '<div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: '.$achievedPer.'%;" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" id="progressbar_721" data-id="2">'.$achievedPer.'%</div>';
						$proccessBar .= '</div>';
					}
				} else {
					$proccessBar = "";
					$proccessBar .= '<div class="progress progress-xl" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="bottom" title="0.00%">0.00%';
					$proccessBar .= '<div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 0%;" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" id="progressbar_721" data-id="2">0%</div>';
					$proccessBar .= '</div>';
				}
	
				$data[$key]['col_2'] = $proccessBar;
				$data[$key]['col_4'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">' . (int)$achievedAmt . ' </a></h5>';
	
				$LastStartDate = Carbon::parse($req_startDate);
				$lastDayPreviousMonth = $LastStartDate->subMonthNoOverflow()->endOfMonth();
	
				$LastMonthOrderCounts = Invoice::query();
				$LastMonthOrderCounts->selectRaw('orders.user_id, COUNT(DISTINCT orders.id) as order_count, SUM(invoice.total_mrp_minus_disocunt) as amount');
				$LastMonthOrderCounts->leftJoin('orders', 'orders.id', '=', 'invoice.order_id');
				$LastMonthOrderCounts->whereIn('invoice.status', [2, 3]);
				$LastMonthOrderCounts->whereMonth('orders.created_at', '=', Carbon::parse($lastDayPreviousMonth)->month);
				$LastMonthOrderCounts->whereYear('orders.created_at', '=', Carbon::parse($lastDayPreviousMonth)->year);
				$LastMonthOrderCounts->whereIn('orders.user_id', $allSalesUserIds);
				$LastMonthOrderCounts->groupBy('orders.user_id');
				$LastMonthOrderCounts = $LastMonthOrderCounts->get();
	
				$achievedPer = getpercentage($totalTarget, $achievedAmt);
	
				$LastMonthOrderCount = 0;
				$LastMonthOrderAmount = 0;
				foreach ($LastMonthOrderCounts as $orderLastCount) {
					$LastMonthOrderCount += $orderLastCount->order_count;
					$LastMonthOrderAmount += $orderLastCount->amount;
				}
				$LastMonthOrderAmountPer = getpercentage($totalTarget, $LastMonthOrderAmount);
	
				$thisMonthOrderCounts = Invoice::query();
				$thisMonthOrderCounts->selectRaw('orders.user_id, COUNT(DISTINCT orders.id) as order_count, SUM(invoice.total_mrp_minus_disocunt) as amount');
				$thisMonthOrderCounts->leftJoin('orders', 'orders.id', '=', 'invoice.order_id');
				$thisMonthOrderCounts->whereIn('invoice.status', [2, 3]);
				$thisMonthOrderCounts->where('orders.created_at', '>=', $req_startDate);
				$thisMonthOrderCounts->where('orders.created_at', '<=', $req_endDate);
				$thisMonthOrderCounts->whereIn('orders.user_id', $allSalesUserIds);
				$thisMonthOrderCounts->groupBy('orders.user_id');
				$thisMonthOrderCounts = $thisMonthOrderCounts->get();
	
				$ThisMonthOrderCount = 0;
				$ThisMonthOrderAmount = 0;
				foreach ($thisMonthOrderCounts as $orderCount) {
					$ThisMonthOrderCount += $orderCount->order_count;
					$ThisMonthOrderAmount += $orderCount->amount;
				}
				
				if($LastMonthOrderCount != 0 && $ThisMonthOrderCount != 0) {
					$diffrentOrderCount = ($ThisMonthOrderCount) - ($LastMonthOrderCount);
				} else {
					$diffrentOrderCount = 0;
				}
	
				if($diffrentOrderCount > 0) {
					$diffrentOrderPer = (($LastMonthOrderCount * 100) / $ThisMonthOrderCount);
					$data[$key]['col_3'] = '<div class="compare_badge" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="bottom" title=""><i class="bx bx-caret-down bx-flip-vertical" style="color:#077322"></i>'.number_format($diffrentOrderPer, 2, '.', ',').'% (+'.$diffrentOrderCount.')</div>';
				} else if($diffrentOrderCount < 0) {
					$diffrentOrderPer = (($ThisMonthOrderCount * 100) / $LastMonthOrderCount);
					$data[$key]['col_3'] = '<div class="compare_badge1" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="bottom" title=""><i class="bx bx-caret-down" style="color:#ff0000"></i>'.number_format($diffrentOrderPer, 2, '.', ',').'% ('.$diffrentOrderCount.')</div>';
				} else {
					$data[$key]['col_3'] = '<div class="compare_badge1" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="bottom" title=""><i class="bx bx-caret-down" style="color:#ff0000"></i>0% ('.$diffrentOrderCount.')</div>';
				}
			}
		} elseif($dataType == "EXECUTIVES" || $dataType == "NEWEXECUTIVES" || $dataType == "ACTIVEEXECUTIVES") {
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
				'users.status as user_status'
			);
	
			// when there is no search parameter then total number rows = total number filtered rows.
			$chiledSalePersonId = getChildSalePersonsIds(Auth::user()->id);
			$query = Wlmst_target::query();
			$query->select($columns);
			$query->selectRaw("CONCAT(users.first_name,' ', users.last_name) AS sale_person_name");
			$query->leftJoin('users', 'users.id', '=', 'wlmst_target.employeee_id');
			$query->leftJoin('wlmst_financialyear', 'wlmst_financialyear.id', '=', 'wlmst_target.finyear_id');
			$query->where('users.status', 1);
			$query->limit(6);
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
	
			if (Auth::user()->id == 2) {
				$recordsTotal = count($data);
			} else {
				$recordsTotal = Wlmst_target::query();
				$recordsTotal->leftJoin('users', 'users.id', '=', 'wlmst_target.employeee_id');
				$recordsTotal->where('users.status', 1);
				$recordsTotal->limit(6);
				$recordsTotal = $recordsTotal->count();
			}
			$recordsFiltered = $recordsTotal;
	
			$data = json_decode(json_encode($data), true);
	
			if ($isFilterApply == 1) {
				$recordsFiltered = count($data);
			}
	
			foreach ($data as $key => $value) {
				$user_status = $value['user_status'];
				$user_status_lable = getUserStatusLable($value['user_status']);
	
				$data[$key]['col_1'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" onclick="targetView(\'' . $value['id'] . '\',\'' . $value['employeee_id'] . '\',\'' . $value['finyear_id'] . '\',\'' . $value['financial_year'] . '\')" >' . $data[$key]['sale_person_name'] . '</span></a> ' . $user_status_lable . '</h5>
				<p class="text-muted mb-0"></p>';
	
				$total_target = 0;
				$monthlyTarget = Wlmst_targetdetail::query()->select('target_amount')->where('wlmst_targetdetail.target_id', $value['id'])->where('wlmst_targetdetail.month_number', Carbon::parse($req_startDate)->month)->first();
	
				if ($monthlyTarget) {
					$totalTarget = (int)$monthlyTarget->target_amount;
	
					// $startDate = ($request->view_type == 0 || $request->view_type == '') ? date('Y-m-d 00:00:00', strtotime('01-04-' . explode("-", $value['financial_year'])[0])) : getDatesFromMonth($request->view_type, $value['financial_year'])['start'];
	
					// $endDate = ($request->view_type == 0 || $request->view_type == '') ? date('Y-m-d 00:00:00', strtotime('31-03-' . explode("-", $value['financial_year'])[1])) : getDatesFromMonth($request->view_type, $value['financial_year'])['end'];
	
					$childSalePersonsIds = getChildSalePersonsIds($value['employeee_id']);
					$allSalesUserIds = array_unique($childSalePersonsIds);
					$allSalesUserIds = array_values($allSalesUserIds);
	
					$orderAmount = Invoice::query();
					$orderAmount->selectRaw('SUM(invoice.total_mrp_minus_disocunt) as amount');
					$orderAmount->leftJoin('orders', 'orders.id', '=', 'invoice.order_id');
					$orderAmount->whereIn('invoice.status', [2, 3]);
					$orderAmount->where('orders.created_at', '>=', $req_startDate);
					$orderAmount->where('orders.created_at', '<=', $req_endDate);
					$orderAmount->whereIn('orders.user_id', $allSalesUserIds);
					$orderAmount = $orderAmount->first();
	
					$achievedAmt = ($orderAmount && $orderAmount->amount != '') ? $orderAmount->amount : 0;
					$achievedPer = getpercentage($totalTarget, $achievedAmt);
					
					$proccessBar = "";
					if($achievedPer == 0) {
						$proccessBar .= '<div class="progress progress-xl justify-content-center" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="bottom" title="Total Target : '.$totalTarget.'<br>Achieved Target : '.$achievedAmt.'<br>Achieved Per : '.$achievedPer.'%">';
						$proccessBar .= '0.00%';
						$proccessBar .= '</div>';
					} else {
						$proccessBar .= '<div class="progress progress-xl" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="bottom" title="Total Target : '.$totalTarget.'<br>Achieved Target : '.$achievedAmt.'<br>Achieved Per : '.$achievedPer.'%">';
						$proccessBar .= '<div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: '.$achievedPer.'%;" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" id="progressbar_721" data-id="2">'.$achievedPer.'%</div>';
						$proccessBar .= '</div>';
					}
				} else {
					$proccessBar = "";
					$proccessBar .= '<div class="progress progress-xl" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="bottom" title="0.00%">0.00%';
					$proccessBar .= '<div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 0%;" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" id="progressbar_721" data-id="2">0%</div>';
					$proccessBar .= '</div>';
				}
	
				$data[$key]['col_2'] = $proccessBar;
				$data[$key]['col_4'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">' . (int)$achievedAmt . ' </a></h5>';
	
				$LastStartDate = Carbon::parse($req_startDate);
				$lastDayPreviousMonth = $LastStartDate->subMonthNoOverflow()->endOfMonth();
	
				$LastMonthOrderCounts = Invoice::query();
				$LastMonthOrderCounts->selectRaw('orders.user_id, COUNT(DISTINCT orders.id) as order_count, SUM(invoice.total_mrp_minus_disocunt) as amount');
				$LastMonthOrderCounts->leftJoin('orders', 'orders.id', '=', 'invoice.order_id');
				$LastMonthOrderCounts->whereIn('invoice.status', [2, 3]);
				$LastMonthOrderCounts->whereMonth('orders.created_at', '=', Carbon::parse($lastDayPreviousMonth)->month);
				$LastMonthOrderCounts->whereYear('orders.created_at', '=', Carbon::parse($lastDayPreviousMonth)->year);
				$LastMonthOrderCounts->whereIn('orders.user_id', $allSalesUserIds);
				$LastMonthOrderCounts->groupBy('orders.user_id');
				$LastMonthOrderCounts = $LastMonthOrderCounts->get();
	
				$achievedPer = getpercentage($totalTarget, $achievedAmt);
	
				$LastMonthOrderCount = 0;
				$LastMonthOrderAmount = 0;
				foreach ($LastMonthOrderCounts as $orderLastCount) {
					$LastMonthOrderCount += $orderLastCount->order_count;
					$LastMonthOrderAmount += $orderLastCount->amount;
				}
				$LastMonthOrderAmountPer = getpercentage($totalTarget, $LastMonthOrderAmount);
	
				$thisMonthOrderCounts = Invoice::query();
				$thisMonthOrderCounts->selectRaw('orders.user_id, COUNT(DISTINCT orders.id) as order_count, SUM(invoice.total_mrp_minus_disocunt) as amount');
				$thisMonthOrderCounts->leftJoin('orders', 'orders.id', '=', 'invoice.order_id');
				$thisMonthOrderCounts->whereIn('invoice.status', [2, 3]);
				$thisMonthOrderCounts->where('orders.created_at', '>=', $req_startDate);
				$thisMonthOrderCounts->where('orders.created_at', '<=', $req_endDate);
				$thisMonthOrderCounts->whereIn('orders.user_id', $allSalesUserIds);
				$thisMonthOrderCounts->groupBy('orders.user_id');
				$thisMonthOrderCounts = $thisMonthOrderCounts->get();
	
				$ThisMonthOrderCount = 0;
				$ThisMonthOrderAmount = 0;
				foreach ($thisMonthOrderCounts as $orderCount) {
					$ThisMonthOrderCount += $orderCount->order_count;
					$ThisMonthOrderAmount += $orderCount->amount;
				}
				
				if($LastMonthOrderCount != 0 && $ThisMonthOrderCount != 0) {
					$diffrentOrderCount = ($ThisMonthOrderCount) - ($LastMonthOrderCount);
				} else {
					$diffrentOrderCount = 0;
				}
	
				if($diffrentOrderCount > 0) {
					$diffrentOrderPer = (($LastMonthOrderCount * 100) / $ThisMonthOrderCount);
					$data[$key]['col_3'] = '<div class="compare_badge" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="bottom" title="This Month Count : '.$ThisMonthOrderCount.'<br>Last Month Count : '.$LastMonthOrderCount.'"><i class="bx bx-caret-down bx-flip-vertical" style="color:#077322"></i>'.number_format($diffrentOrderPer, 2, '.', ',').'% (+'.$diffrentOrderCount.')</div>';
				} else if($diffrentOrderCount < 0) {
					$diffrentOrderPer = (($ThisMonthOrderCount * 100) / $LastMonthOrderCount);
					$data[$key]['col_3'] = '<div class="compare_badge1" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="bottom" title="This Month Count : '.$ThisMonthOrderCount.'<br>Last Month Count : '.$LastMonthOrderCount.'"><i class="bx bx-caret-down" style="color:#ff0000"></i>'.number_format($diffrentOrderPer, 2, '.', ',').'% ('.$diffrentOrderCount.')</div>';
				} else {
					$data[$key]['col_3'] = '<div class="compare_badge1" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="bottom" title=""><i class="bx bx-caret-down" style="color:#ff0000"></i>0% ('.$diffrentOrderCount.')</div>';
				}
			}
		} elseif($dataType == "ADM" || $dataType == "NEWADM" || $dataType == "ACTIVEADM" || $dataType == "AD" || $dataType == "NEWAD" || $dataType == "ACTIVEAD") {
			$searchColumns = array(
				0 => 'users.first_name',
				1 => 'users.last_name',
			);
	
			$columns = array(
				'users.id',
				'users.first_name',
				'users.last_name',
				'channel_partner.firm_name',
				'users.status as user_status'
			);
	
			// when there is no search parameter then total number rows = total number filtered rows.
			$chiledSalePersonId = getChildSalePersonsIds(Auth::user()->id);
			$query = User::query();
			$query->select($columns);
			$query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
			// $query->where('users.status', 1);
			$query->whereIn('users.id', $dataIds);
			$query->limit(6);
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
	
			if (Auth::user()->id == 2) {
				$recordsTotal = count($data);
			} else {
				$recordsTotal = User::query();
				$recordsTotal->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
				// $recordsTotal->where('users.status', 1);
				$recordsTotal->whereIn('users.id', $dataIds);
				$recordsTotal->limit(6);
				$recordsTotal = $recordsTotal->count();
			}
			$recordsFiltered = $recordsTotal;
	
			$data = json_decode(json_encode($data), true);
	
			if ($isFilterApply == 1) {
				$recordsFiltered = count($data);
			}
	
			foreach ($data as $key => $value) {
				$user_status = $value['user_status'];
				$user_status_lable = getUserStatusLable($value['user_status']);
	
				$data[$key]['col_1'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" onclick="" >' . $data[$key]['firm_name'] . '</span></a>'.$user_status_lable.'</h5><p class="text-muted mb-0"></p>';
				
				$orderAmount = Invoice::query();
				$orderAmount->selectRaw('SUM(invoice.total_mrp_minus_disocunt) as amount');
				$orderAmount->leftJoin('orders', 'orders.id', '=', 'invoice.order_id');
				$orderAmount->whereIn('invoice.status', [2, 3]);
				$orderAmount->where('orders.created_at', '>=', $req_startDate);
				$orderAmount->where('orders.created_at', '<=', $req_endDate);
				$orderAmount->where('orders.user_id', $value['id']);
				$orderAmount = $orderAmount->first();

				$achievedAmt = ($orderAmount && $orderAmount->amount != '') ? $orderAmount->amount : 0;
	
				$data[$key]['col_2'] = "";
				$data[$key]['col_4'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">' . (int)$achievedAmt . ' </a></h5>';
	
				$LastStartDate = Carbon::parse($req_startDate);
				$lastDayPreviousMonth = $LastStartDate->subMonthNoOverflow()->endOfMonth();
	
				$LastMonthOrderCounts = Invoice::query();
				$LastMonthOrderCounts->selectRaw('orders.user_id, COUNT(DISTINCT orders.id) as order_count, SUM(invoice.total_mrp_minus_disocunt) as amount');
				$LastMonthOrderCounts->leftJoin('orders', 'orders.id', '=', 'invoice.order_id');
				$LastMonthOrderCounts->whereIn('invoice.status', [2, 3]);
				$LastMonthOrderCounts->whereMonth('orders.created_at', '=', Carbon::parse($lastDayPreviousMonth)->month);
				$LastMonthOrderCounts->whereYear('orders.created_at', '=', Carbon::parse($lastDayPreviousMonth)->year);
				$LastMonthOrderCounts->where('orders.user_id', $value['id']);
				$LastMonthOrderCounts->groupBy('orders.user_id');
				$LastMonthOrderCounts = $LastMonthOrderCounts->get();
	
	
				$LastMonthOrderCount = 0;
				$LastMonthOrderAmount = 0;
				foreach ($LastMonthOrderCounts as $orderLastCount) {
					$LastMonthOrderCount += $orderLastCount->order_count;
					$LastMonthOrderAmount += $orderLastCount->amount;
				}
	
				$thisMonthOrderCounts = Invoice::query();
				$thisMonthOrderCounts->selectRaw('orders.user_id, COUNT(DISTINCT orders.id) as order_count, SUM(invoice.total_mrp_minus_disocunt) as amount');
				$thisMonthOrderCounts->leftJoin('orders', 'orders.id', '=', 'invoice.order_id');
				$thisMonthOrderCounts->whereIn('invoice.status', [2, 3]);
				$thisMonthOrderCounts->where('orders.created_at', '>=', $req_startDate);
				$thisMonthOrderCounts->where('orders.created_at', '<=', $req_endDate);
				$thisMonthOrderCounts->where('orders.user_id', $value['id']);
				$thisMonthOrderCounts->groupBy('orders.user_id');
				$thisMonthOrderCounts = $thisMonthOrderCounts->get();
	
				$ThisMonthOrderCount = 0;
				$ThisMonthOrderAmount = 0;
				foreach ($thisMonthOrderCounts as $orderCount) {
					$ThisMonthOrderCount += $orderCount->order_count;
					$ThisMonthOrderAmount += $orderCount->amount;
				}
				
				if($LastMonthOrderCount != 0 && $ThisMonthOrderCount != 0) {
					$diffrentOrderCount = ($ThisMonthOrderCount) - ($LastMonthOrderCount);
				} else {
					$diffrentOrderCount = 0;
				}
	
				if($diffrentOrderCount > 0) {
					$diffrentOrderPer = (($LastMonthOrderCount * 100) / $ThisMonthOrderCount);
					$data[$key]['col_3'] = '<div class="compare_badge" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="bottom" title="This Month Count : '.$ThisMonthOrderCount.'<br>Last Month Count : '.$LastMonthOrderCount.'"><i class="bx bx-caret-down bx-flip-vertical" style="color:#077322"></i>'.number_format($diffrentOrderPer, 2, '.', ',').'% (+'.$diffrentOrderCount.')</div>';
				} else if($diffrentOrderCount < 0) {
					$diffrentOrderPer = (($ThisMonthOrderCount * 100) / $LastMonthOrderCount);
					$data[$key]['col_3'] = '<div class="compare_badge1" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="bottom" title="This Month Count : '.$ThisMonthOrderCount.'<br>Last Month Count : '.$LastMonthOrderCount.'"><i class="bx bx-caret-down" style="color:#ff0000"></i>'.number_format($diffrentOrderPer, 2, '.', ',').'% ('.$diffrentOrderCount.')</div>';
				} else {
					$data[$key]['col_3'] = '<div class="compare_badge1" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="bottom" title=""><i class="bx bx-caret-down" style="color:#ff0000"></i>0% ('.$diffrentOrderCount.')</div>';
				}
			}
		}
		

		$jsonData = [
			'draw' => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
			'recordsTotal' => intval($recordsTotal), // total number of records
			'recordsFiltered' => intval($recordsFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
			'data' => $data, // total data array
		];
		return $jsonData;
	}
	
	function dashboardSalesOverviewPerEntity(Request $request) {
		$diffrentExecutiveOrderCount = 0;
		$req_startDate = date('Y-m-d', strtotime($request->start_date));
		$req_endDate = date('Y-m-d', strtotime($request->end_date));
		$LastStartDate = Carbon::parse($req_startDate);
		$lastDayPreviousMonth = $LastStartDate->subMonthNoOverflow()->endOfMonth();

		$SalePersonIds = User::select('id')->where('type', 2)->distinct()->pluck('id');

		$ManagerIds = User::select('users.id');
		$ManagerIds->leftJoin('sale_person', 'sale_person.user_id', '=', 'users.id');
		$ManagerIds->whereIn('sale_person.type', [1, 2, 3, 4, 5]);
		$ManagerIds = $ManagerIds->distinct()->pluck('users.id');

		$ChannelPartnerIds = User::select('id')->whereIn('type', [101, 102, 103, 104, 105])->distinct()->pluck('id');

		$ElectricianLeadIds = LeadSource::select('lead_id');
		$ElectricianLeadIds->whereIn('source_type', ['user-301', 'user-302']);
		$ElectricianLeadIds = $ElectricianLeadIds->distinct()->pluck('lead_id');

		$ArchitectLeadIds = LeadSource::select('lead_id');
		$ArchitectLeadIds->whereIn('source_type', ['user-201', 'user-202']);
		$ArchitectLeadIds = $ArchitectLeadIds->distinct()->pluck('lead_id');

		$viewData = array();

		// EXECUTIVE START
			$Executive = Invoice::query();
			$Executive->selectRaw('SUM(invoice.total_mrp_minus_disocunt) as amount');
			$Executive->leftJoin('orders', 'orders.id', '=', 'invoice.order_id');
			$Executive->whereIn('invoice.status', [2, 3]);
			$Executive->where('orders.created_at', '>=', $req_startDate);
			$Executive->where('orders.created_at', '<=', $req_endDate);
			$Executive->whereIn('orders.user_id', $SalePersonIds);
			$Executive = $Executive->first();

			$LastMonthExecutiveOrderCounts = Invoice::query();
			$LastMonthExecutiveOrderCounts->selectRaw('orders.user_id, COUNT(DISTINCT orders.id) as order_count, SUM(invoice.total_mrp_minus_disocunt) as amount');
			$LastMonthExecutiveOrderCounts->leftJoin('orders', 'orders.id', '=', 'invoice.order_id');
			$LastMonthExecutiveOrderCounts->whereIn('invoice.status', [2, 3]);
			$LastMonthExecutiveOrderCounts->whereMonth('orders.created_at', '=', Carbon::parse($lastDayPreviousMonth)->month);
			$LastMonthExecutiveOrderCounts->whereYear('orders.created_at', '=', Carbon::parse($lastDayPreviousMonth)->year);
			$LastMonthExecutiveOrderCounts->whereIn('orders.user_id', $SalePersonIds);
			$LastMonthExecutiveOrderCounts->groupBy('orders.user_id');
			$LastMonthExecutiveOrderCounts = $LastMonthExecutiveOrderCounts->get();

			$LastMonthExecutiveOrderCount = 0;
			$LastMonthExecutiveOrderAmount = 0;
			foreach ($LastMonthExecutiveOrderCounts as $ExecutiveorderLastCount) {
				$LastMonthExecutiveOrderCount += $ExecutiveorderLastCount->order_count;
				$LastMonthExecutiveOrderAmount += $ExecutiveorderLastCount->order_count;
			}

			$ThisMonthExecutiveOrderCounts = Invoice::query();
			$ThisMonthExecutiveOrderCounts->selectRaw('orders.user_id, COUNT(DISTINCT orders.id) as order_count, SUM(invoice.total_mrp_minus_disocunt) as amount');
			$ThisMonthExecutiveOrderCounts->leftJoin('orders', 'orders.id', '=', 'invoice.order_id');
			$ThisMonthExecutiveOrderCounts->whereIn('invoice.status', [2, 3]);
			$ThisMonthExecutiveOrderCounts->where('orders.created_at', '>=', $req_startDate);
			$ThisMonthExecutiveOrderCounts->where('orders.created_at', '<=', $req_endDate);
			$ThisMonthExecutiveOrderCounts->whereIn('orders.user_id', $SalePersonIds);
			$ThisMonthExecutiveOrderCounts->groupBy('orders.user_id');
			$ThisMonthExecutiveOrderCounts = $ThisMonthExecutiveOrderCounts->get();

			$ThisMonthCustomerOrderIds = ORDER::query();
			$ThisMonthCustomerOrderIds->where('orders.created_at', '>=', $req_startDate);
			$ThisMonthCustomerOrderIds->where('orders.created_at', '<=', $req_endDate);
			$ThisMonthCustomerOrderIds->whereIn('orders.user_id', $SalePersonIds);
			$CustomerIds = $ThisMonthCustomerOrderIds->distinct()->pluck('id')->all();

			$ThisMonthExecutiveOrderCount = 0;
			$ThisMonthExecutiveOrderAmount = 0;
			foreach ($ThisMonthExecutiveOrderCounts as $ExecutiveorderThisCount) {
				$ThisMonthExecutiveOrderCount += $ExecutiveorderThisCount->order_count;
				$ThisMonthExecutiveOrderAmount += $ExecutiveorderThisCount->amount;
			}

			if($LastMonthExecutiveOrderCount != 0 && $ThisMonthExecutiveOrderCount != 0) {
				$diffrentExecutiveOrderCount = ($ThisMonthExecutiveOrderCount) - ($LastMonthExecutiveOrderCount);
			} else {
				$diffrentExecutiveOrderCount = 0;
			}

			
			
			$viewData[0]['col_1'] = "Executive";
			
			if($diffrentExecutiveOrderCount > 0) {
				$diffrentExecutiveOrderPer = (($LastMonthExecutiveOrderCount * 100) / $ThisMonthExecutiveOrderCount);
				$viewData[0]['col_2'] = '<div class="compare_badge" onclick="ViewSales(\'' . implode(',', $CustomerIds) . '\', \'ORDER\', \'Executive\')" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="bottom" title=""><i class="bx bx-caret-down bx-flip-vertical" style="color:#077322"></i>'.number_format($diffrentExecutiveOrderPer, 2, '.', ',').'% (+'.$diffrentExecutiveOrderCount.')</div>';
			} else if($diffrentExecutiveOrderCount < 0) {
				$diffrentExecutiveOrderPer = (($ThisMonthExecutiveOrderCount * 100) / $LastMonthExecutiveOrderCount);
				$viewData[0]['col_2'] = '<div class="compare_badge1" onclick="ViewSales(\'' . implode(',', $CustomerIds) . '\', \'ORDER\',  \'Executive\')" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="bottom" title=""><i class="bx bx-caret-down" style="color:#ff0000"></i>'.number_format($diffrentExecutiveOrderPer, 2, '.', ',').'% ('.$diffrentExecutiveOrderCount.')</div>';
			} else {
				$viewData[0]['col_2'] = '<div class="compare_badge" onclick="ViewSales(\'' . implode(',', $CustomerIds) . '\', \'ORDER\',  \'Executive\')" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="bottom" title=""><i class="bx bx-caret-down bx-flip-vertical" style="color:#077322"></i>0% (+0)</div>';
			}

			$viewData[0]['col_3'] = $Executive->amount;
		// EXECUTIVE END

		// DISTRIBUTOR START
			$Distributor = Invoice::query();
			$Distributor->selectRaw('SUM(invoice.total_mrp_minus_disocunt) as amount');
			$Distributor->leftJoin('orders', 'orders.id', '=', 'invoice.order_id');
			$Distributor->whereIn('invoice.status', [2, 3]);
			$Distributor->where('orders.created_at', '>=', $req_startDate);
			$Distributor->where('orders.created_at', '<=', $req_endDate);
			$Distributor->whereIn('orders.user_id', $ChannelPartnerIds);
			$Distributor = $Distributor->first();

			$LastMonthDistributorOrderCounts = Invoice::query();
			$LastMonthDistributorOrderCounts->selectRaw('orders.user_id, COUNT(DISTINCT orders.id) as order_count');
			$LastMonthDistributorOrderCounts->leftJoin('orders', 'orders.id', '=', 'invoice.order_id');
			$LastMonthDistributorOrderCounts->whereIn('invoice.status', [2, 3]);
			$LastMonthDistributorOrderCounts->whereMonth('orders.created_at', '=', Carbon::parse($lastDayPreviousMonth)->month);
			$LastMonthDistributorOrderCounts->whereYear('orders.created_at', '=', Carbon::parse($lastDayPreviousMonth)->year);
			$LastMonthDistributorOrderCounts->whereIn('orders.user_id', $ChannelPartnerIds);
			$LastMonthDistributorOrderCounts->groupBy('orders.user_id');
			$LastMonthDistributorOrderCounts = $LastMonthDistributorOrderCounts->get();

			$LastMonthDistributorOrderCount = 0;
			foreach ($LastMonthDistributorOrderCounts as $DistributororderLastCount) {
				$LastMonthDistributorOrderCount += $DistributororderLastCount->order_count;
			}

			$ThisMonthDistributorOrderCounts = Invoice::query();
			$ThisMonthDistributorOrderCounts->selectRaw('orders.user_id, COUNT(DISTINCT orders.id) as order_count');
			$ThisMonthDistributorOrderCounts->leftJoin('orders', 'orders.id', '=', 'invoice.order_id');
			$ThisMonthDistributorOrderCounts->whereIn('invoice.status', [2, 3]);
			$ThisMonthDistributorOrderCounts->where('orders.created_at', '>=', $req_startDate);
			$ThisMonthDistributorOrderCounts->where('orders.created_at', '<=', $req_endDate);
			$ThisMonthDistributorOrderCounts->whereIn('orders.user_id', $ChannelPartnerIds);
			$ThisMonthDistributorOrderCounts->groupBy('orders.user_id');
			$ThisMonthDistributorOrderCounts = $ThisMonthDistributorOrderCounts->get();

			$ThisMonthCustomerOrderIds = ORDER::query();
			$ThisMonthCustomerOrderIds->where('orders.created_at', '>=', $req_startDate);
			$ThisMonthCustomerOrderIds->where('orders.created_at', '<=', $req_endDate);
			$ThisMonthCustomerOrderIds->whereIn('orders.user_id', $ChannelPartnerIds);
			$CustomerIds = $ThisMonthCustomerOrderIds->distinct()->pluck('id')->all();

			$ThisMonthDistributorOrderCount = 0;
			foreach ($ThisMonthDistributorOrderCounts as $DistributororderThisCount) {
				$ThisMonthDistributorOrderCount += $DistributororderThisCount->order_count;
			}

			if($LastMonthDistributorOrderCount != 0 && $ThisMonthDistributorOrderCount != 0) {
				$diffrentDistributorOrderCount = ($ThisMonthDistributorOrderCount) - ($LastMonthDistributorOrderCount);
			} else {
				$diffrentDistributorOrderCount = 0;
			}

			
			$viewData[1]['col_1'] = "Distributor";
			if($diffrentDistributorOrderCount > 0) {
				$diffrentDistributorOrderPer = (($LastMonthDistributorOrderCount * 100) / $ThisMonthDistributorOrderCount);
				$viewData[1]['col_2'] = '<div class="compare_badge" onclick="ViewSales(\'' . implode(',', $CustomerIds) . '\', \'ORDER\',  \'Distributor\')" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="bottom" title=""><i class="bx bx-caret-down bx-flip-vertical" style="color:#077322"></i>'.number_format($diffrentDistributorOrderPer, 2, '.', ',').'% (+'.$diffrentDistributorOrderCount.')</div>';
			} else if($diffrentDistributorOrderCount < 0) {
				$diffrentDistributorOrderPer = (($ThisMonthDistributorOrderCount * 100) / $LastMonthDistributorOrderCount);
				$viewData[1]['col_2'] = '<div class="compare_badge1" onclick="ViewSales(\'' . implode(',', $CustomerIds) . '\', \'ORDER\',  \'Distributor\')" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="bottom" title=""><i class="bx bx-caret-down" style="color:#ff0000"></i>'.number_format($diffrentDistributorOrderPer, 2, '.', ',').'% ('.$diffrentDistributorOrderCount.')</div>';
			} else {
				$viewData[1]['col_2'] = '<div class="compare_badge" onclick="ViewSales(\'' . implode(',', $CustomerIds) . '\', \'ORDER\',  \'Distributor\')" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="bottom" title=""><i class="bx bx-caret-down bx-flip-vertical" style="color:#077322"></i>0% (+0)</div>';
			}
			$viewData[1]['col_3'] = $Distributor->amount;
		// DISTRIBUTOR END

		// MANAGER START
			$Manager = Invoice::query();
			$Manager->selectRaw('SUM(invoice.total_mrp_minus_disocunt) as amount');
			$Manager->leftJoin('orders', 'orders.id', '=', 'invoice.order_id');
			$Manager->whereIn('invoice.status', [2, 3]);
			$Manager->where('orders.created_at', '>=', $req_startDate);
			$Manager->where('orders.created_at', '<=', $req_endDate);
			$Manager->whereIn('orders.user_id', $ManagerIds);
			$Manager = $Manager->first();

			$LastMonthManagerOrderCounts = Invoice::query();
			$LastMonthManagerOrderCounts->selectRaw('orders.user_id, COUNT(DISTINCT orders.id) as order_count');
			$LastMonthManagerOrderCounts->leftJoin('orders', 'orders.id', '=', 'invoice.order_id');
			$LastMonthManagerOrderCounts->whereIn('invoice.status', [2, 3]);
			$LastMonthManagerOrderCounts->whereMonth('orders.created_at', '=', Carbon::parse($lastDayPreviousMonth)->month);
			$LastMonthManagerOrderCounts->whereYear('orders.created_at', '=', Carbon::parse($lastDayPreviousMonth)->year);
			$LastMonthManagerOrderCounts->whereIn('orders.user_id', $ManagerIds);
			$LastMonthManagerOrderCounts->groupBy('orders.user_id');
			$LastMonthManagerOrderCounts = $LastMonthManagerOrderCounts->get();

			$LastMonthManagerOrderCount = 0;
			foreach ($LastMonthManagerOrderCounts as $ManagerorderLastCount) {
				$LastMonthManagerOrderCount += $ManagerorderLastCount->order_count;
			}

			$ThisMonthManagerOrderCounts = Invoice::query();
			$ThisMonthManagerOrderCounts->selectRaw('orders.user_id, COUNT(DISTINCT orders.id) as order_count');
			$ThisMonthManagerOrderCounts->leftJoin('orders', 'orders.id', '=', 'invoice.order_id');
			$ThisMonthManagerOrderCounts->whereIn('invoice.status', [2, 3]);
			$ThisMonthManagerOrderCounts->where('orders.created_at', '>=', $req_startDate);
			$ThisMonthManagerOrderCounts->where('orders.created_at', '<=', $req_endDate);
			$ThisMonthManagerOrderCounts->whereIn('orders.user_id', $ManagerIds);
			$ThisMonthManagerOrderCounts->groupBy('orders.user_id');
			$ThisMonthManagerOrderCounts = $ThisMonthManagerOrderCounts->get();

			$ThisMonthCustomerOrderIds = ORDER::query();
			$ThisMonthCustomerOrderIds->where('orders.created_at', '>=', $req_startDate);
			$ThisMonthCustomerOrderIds->where('orders.created_at', '<=', $req_endDate);
			$ThisMonthCustomerOrderIds->whereIn('orders.user_id', $ManagerIds);
			$CustomerIds = $ThisMonthCustomerOrderIds->distinct()->pluck('id')->all();

			$ThisMonthManagerOrderCount = 0;
			foreach ($ThisMonthManagerOrderCounts as $ManagerorderThisCount) {
				$ThisMonthManagerOrderCount += $ManagerorderThisCount->order_count;
			}

			if($LastMonthManagerOrderCount != 0 && $ThisMonthManagerOrderCount != 0) {
				$diffrentManagerOrderCount = ($ThisMonthManagerOrderCount) - ($LastMonthManagerOrderCount);
			} else {
				$diffrentManagerOrderCount = 0;
			}

			
			$viewData[2]['col_1'] = "Manager";
			if($diffrentManagerOrderCount > 0) {
				$diffrentManagerOrderPer = (($LastMonthManagerOrderCount * 100) / $ThisMonthManagerOrderCount);
				$viewData[2]['col_2'] = '<div class="compare_badge" onclick="ViewSales(\'' . implode(',', $CustomerIds) . '\', \'ORDER\',  \'Manager\')" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="bottom" title=""><i class="bx bx-caret-down bx-flip-vertical" style="color:#077322"></i>'.number_format($diffrentManagerOrderPer, 2, '.', ',').'% (+'.$diffrentManagerOrderCount.')</div>';
			} else if($diffrentManagerOrderCount < 0) {
				$diffrentManagerOrderPer = (($ThisMonthManagerOrderCount * 100) / $LastMonthManagerOrderCount);
				$viewData[2]['col_2'] = '<div class="compare_badge1" onclick="ViewSales(\'' . implode(',', $CustomerIds) . '\', \'ORDER\',  \'Manager\')" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="bottom" title=""><i class="bx bx-caret-down" style="color:#ff0000"></i>'.number_format($diffrentManagerOrderPer, 2, '.', ',').'% ('.$diffrentManagerOrderCount.')</div>';
			} else {
				$viewData[2]['col_2'] = '<div class="compare_badge" onclick="ViewSales(\'' . implode(',', $CustomerIds) . '\', \'ORDER\',  \'Manager\')" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="bottom" title=""><i class="bx bx-caret-down bx-flip-vertical" style="color:#077322"></i>0% (+0)</div>';
			}
			$viewData[2]['col_3'] = $Manager->amount;
		// MANAGER END

		// ELECTRICIAN START
			$Electrician = Lead::query();
			$Electrician->selectRaw('SUM(wltrn_quotation.quot_whitelion_amount) AS whitelion_amount');
			$Electrician->selectRaw('SUM(wltrn_quotation.quot_billing_amount) AS billing_amount');
			$Electrician->selectRaw('SUM(wltrn_quotation.quot_other_amount) AS other_amount');
			$Electrician->selectRaw('SUM(wltrn_quotation.quot_total_amount) AS total_amount');
			$Electrician->leftJoin('wltrn_quotation', 'wltrn_quotation.inquiry_id', '=', 'leads.id');
			$Electrician->where('leads.created_at', '>=', $req_startDate);
			$Electrician->where('leads.created_at', '<=', $req_endDate);
			$Electrician->whereIn('leads.id', $ElectricianLeadIds);
			$Electrician->where('wltrn_quotation.isfinal', 1);
			$Electrician = $Electrician->first();

			$LastMonthElectricianLeadCounts = Lead::query();
			$LastMonthElectricianLeadCounts->selectRaw('COUNT(DISTINCT leads.id) as lead_count');
			$LastMonthElectricianLeadCounts->whereMonth('leads.created_at', '=', Carbon::parse($lastDayPreviousMonth)->month);
			$LastMonthElectricianLeadCounts->whereYear('leads.created_at', '=', Carbon::parse($lastDayPreviousMonth)->year);
			$LastMonthElectricianLeadCounts->whereIn('leads.id', $ElectricianLeadIds);
			$LastMonthElectricianLeadCounts->groupBy('leads.id');
			$LastMonthElectricianLeadCounts = $LastMonthElectricianLeadCounts->get();

			$LastMonthElectricianLeadCount = 0;
			foreach ($LastMonthElectricianLeadCounts as $ElectricianLeadLastCount) {
				$LastMonthElectricianLeadCount += $ElectricianLeadLastCount->lead_count;
			}

			$ThisMonthElectricianLeadCounts = Lead::query();
			$ThisMonthElectricianLeadCounts->selectRaw('COUNT(DISTINCT leads.id) as lead_count');
			$ThisMonthElectricianLeadCounts->where('leads.created_at', '>=', $req_startDate);
			$ThisMonthElectricianLeadCounts->where('leads.created_at', '<=', $req_endDate);
			$ThisMonthElectricianLeadCounts->whereIn('leads.id', $ElectricianLeadIds);
			$ThisMonthElectricianLeadCounts->groupBy('leads.id');
			$ThisMonthElectricianLeadCounts = $ThisMonthElectricianLeadCounts->get();

			$ThisMonthCustomerLeadIds = Lead::query();
			$ThisMonthCustomerLeadIds->where('leads.created_at', '>=', $req_startDate);
			$ThisMonthCustomerLeadIds->where('leads.created_at', '<=', $req_endDate);
			$ThisMonthCustomerLeadIds->whereIn('leads.id', $ElectricianLeadIds);
			$CustomerIds = $ThisMonthCustomerLeadIds->distinct()->pluck('id')->all();

			$ThisMonthElectricianLeadCount = 0;
			foreach ($ThisMonthElectricianLeadCounts as $ElectricianLeadThisCount) {
				$ThisMonthElectricianLeadCount += $ElectricianLeadThisCount->lead_count;
			}

			if($LastMonthElectricianLeadCount != 0 && $ThisMonthElectricianLeadCount != 0) {
				$diffrentElectricianLeadCount = ($ThisMonthElectricianLeadCount) - ($LastMonthElectricianLeadCount);
			} else {
				$diffrentElectricianLeadCount = 0;
			}

			
			$viewData[3]['col_1'] = "Electrician";
			if($diffrentElectricianLeadCount > 0) {
				$diffrentElectricianOrderPer = (($LastMonthElectricianLeadCount * 100) / $ThisMonthElectricianLeadCount);
				$viewData[3]['col_2'] = '<div class="compare_badge" onclick="ViewSales(\'' . implode(',', $CustomerIds) . '\', \'LEAD\',  \'Electrician\')" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="bottom" title=""><i class="bx bx-caret-down bx-flip-vertical" style="color:#077322"></i>'.number_format($diffrentElectricianOrderPer, 2, '.', ',').'% (+'.$diffrentElectricianLeadCount.')</div>';
			} else if($diffrentElectricianLeadCount < 0) {
				$diffrentElectricianOrderPer = (($ThisMonthElectricianLeadCount * 100) / $LastMonthElectricianLeadCount);
				$viewData[3]['col_2'] = '<div class="compare_badge1" onclick="ViewSales(\'' . implode(',', $CustomerIds) . '\', \'LEAD\',  \'Electrician\')" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="bottom" title=""><i class="bx bx-caret-down" style="color:#ff0000"></i>'.number_format($diffrentElectricianOrderPer, 2, '.', ',').'% ('.$diffrentElectricianLeadCount.')</div>';
			} else {
				$viewData[3]['col_2'] = '<div class="compare_badge" onclick="ViewSales(\'' . implode(',', $CustomerIds) . '\', \'LEAD\',  \'Electrician\')" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="bottom" title=""><i class="bx bx-caret-down bx-flip-vertical" style="color:#077322"></i>0% (+0)</div>';
			}
			if($Electrician->billing_amount != null) {
				$viewData[3]['col_3'] = $Electrician->billing_amount;
			} else {
				$viewData[3]['col_3'] = 0.00;
			}
		// ELECTRICIAN END

		// ARCHITECT START
			$Architect = Lead::query();
			$Architect->selectRaw('SUM(wltrn_quotation.quot_whitelion_amount) AS whitelion_amount');
			$Architect->selectRaw('SUM(wltrn_quotation.quot_billing_amount) AS billing_amount');
			$Architect->selectRaw('SUM(wltrn_quotation.quot_other_amount) AS other_amount');
			$Architect->selectRaw('SUM(wltrn_quotation.quot_total_amount) AS total_amount');
			$Architect->leftJoin('wltrn_quotation', 'wltrn_quotation.inquiry_id', '=', 'leads.id');
			$Architect->where('leads.created_at', '>=', $req_startDate);
			$Architect->where('leads.created_at', '<=', $req_endDate);
			$Architect->whereIn('leads.id', $ArchitectLeadIds);
			$Architect->where('wltrn_quotation.isfinal', 1);
			$Architect = $Architect->first();

			$LastMonthArchitectLeadCounts = Lead::query();
			$LastMonthArchitectLeadCounts->selectRaw('COUNT(DISTINCT leads.id) as lead_count');
			$LastMonthArchitectLeadCounts->whereMonth('leads.created_at', '=', Carbon::parse($lastDayPreviousMonth)->month);
			$LastMonthArchitectLeadCounts->whereYear('leads.created_at', '=', Carbon::parse($lastDayPreviousMonth)->year);
			$LastMonthArchitectLeadCounts->whereIn('leads.id', $ArchitectLeadIds);
			$LastMonthArchitectLeadCounts->groupBy('leads.id');
			$LastMonthArchitectLeadCounts = $LastMonthArchitectLeadCounts->get();

			$LastMonthArchitectLeadCount = 0;
			foreach ($LastMonthArchitectLeadCounts as $ArchitectLeadLastCount) {
				$LastMonthArchitectLeadCount += $ArchitectLeadLastCount->lead_count;
			}

			$ThisMonthArchitectLeadCounts = Lead::query();
			$ThisMonthArchitectLeadCounts->selectRaw('COUNT(DISTINCT leads.id) as lead_count');
			$ThisMonthArchitectLeadCounts->where('leads.created_at', '>=', $req_startDate);
			$ThisMonthArchitectLeadCounts->where('leads.created_at', '<=', $req_endDate);
			$ThisMonthArchitectLeadCounts->whereIn('leads.id', $ArchitectLeadIds);
			$ThisMonthArchitectLeadCounts->groupBy('leads.id');
			$ThisMonthArchitectLeadCounts = $ThisMonthArchitectLeadCounts->get();

			$ThisMonthCustomerLeadIds = Lead::query();
			$ThisMonthCustomerLeadIds->where('leads.created_at', '>=', $req_startDate);
			$ThisMonthCustomerLeadIds->where('leads.created_at', '<=', $req_endDate);
			$ThisMonthCustomerLeadIds->whereIn('leads.id', $ArchitectLeadIds);
			$CustomerIds = $ThisMonthCustomerLeadIds->distinct()->pluck('id')->all();

			$ThisMonthArchitectLeadCount = 0;
			foreach ($ThisMonthArchitectLeadCounts as $ArchitectLeadThisCount) {
				$ThisMonthArchitectLeadCount += $ArchitectLeadThisCount->lead_count;
			}

			if($LastMonthArchitectLeadCount != 0 && $ThisMonthArchitectLeadCount != 0) {
				$diffrentArchitectLeadCount = ($ThisMonthArchitectLeadCount) - ($LastMonthArchitectLeadCount);
			} else {
				$diffrentArchitectLeadCount = 0;
			}

			$viewData[4]['col_1'] = "Architect";
			if($diffrentArchitectLeadCount > 0) {
				$diffrentArchitectOrderPer = (($LastMonthArchitectLeadCount * 100) / $ThisMonthArchitectLeadCount);
				$viewData[4]['col_2'] = '<div class="compare_badge" onclick="ViewSales(\'' . implode(',', $CustomerIds) . '\', \'LEAD\',  \'Architect\')" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="bottom" title=""><i class="bx bx-caret-down bx-flip-vertical" style="color:#077322"></i>'.number_format($diffrentArchitectOrderPer, 2, '.', ',').'% (+'.$diffrentArchitectLeadCount.')</div>';
			} else if($diffrentArchitectLeadCount < 0) {
				$diffrentArchitectOrderPer = (($ThisMonthArchitectLeadCount * 100) / $LastMonthArchitectLeadCount);
				$viewData[4]['col_2'] = '<div class="compare_badge1" onclick="ViewSales(\'' . implode(',', $CustomerIds) . '\', \'LEAD\',  \'Architect\')" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="bottom" title=""><i class="bx bx-caret-down" style="color:#ff0000"></i>'.number_format($diffrentArchitectOrderPer, 2, '.', ',').'% ('.$diffrentArchitectLeadCount.')</div>';
			} else {
				$viewData[4]['col_2'] = '<div class="compare_badge" onclick="ViewSales(\'' . implode(',', $CustomerIds) . '\', \'LEAD\',  \'Architect\')" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="bottom" title=""><i class="bx bx-caret-down bx-flip-vertical" style="color:#077322"></i>0% (+0)</div>';
			}

			if($Architect->billing_amount != null) {
				$viewData[4]['col_3'] = $Architect->billing_amount;
			} else {
				$viewData[4]['col_3'] = 0.00;
			}
		// ARCHITECT END

		// CUSTOMER START
			$Customer = Lead::query();
			$Customer->selectRaw('SUM(wltrn_quotation.quot_whitelion_amount) AS whitelion_amount');
			$Customer->selectRaw('SUM(wltrn_quotation.quot_billing_amount) AS billing_amount');
			$Customer->selectRaw('SUM(wltrn_quotation.quot_other_amount) AS other_amount');
			$Customer->selectRaw('SUM(wltrn_quotation.quot_total_amount) AS total_amount');
			$Customer->leftJoin('wltrn_quotation', 'wltrn_quotation.inquiry_id', '=', 'leads.id');
			$Customer->where('leads.created_at', '>=', $req_startDate);
			$Customer->where('leads.created_at', '<=', $req_endDate);
			$Customer->where('wltrn_quotation.isfinal', 1);
			$Customer = $Customer->first();

			$LastMonthCustomerLeadCounts = Lead::query();
			$LastMonthCustomerLeadCounts->selectRaw('COUNT(DISTINCT leads.id) as lead_count');
			$LastMonthCustomerLeadCounts->whereMonth('leads.created_at', '=', Carbon::parse($lastDayPreviousMonth)->month);
			$LastMonthCustomerLeadCounts->whereYear('leads.created_at', '=', Carbon::parse($lastDayPreviousMonth)->year);
			$LastMonthCustomerLeadCounts = $LastMonthCustomerLeadCounts->get();

			$LastMonthCustomerLeadCount = 0;
			foreach ($LastMonthCustomerLeadCounts as $CustomerLeadLastCount) {
				$LastMonthCustomerLeadCount += $CustomerLeadLastCount->lead_count;
			}

			$ThisMonthCustomerLeadCounts = Lead::query();
			$ThisMonthCustomerLeadCounts->selectRaw('COUNT(DISTINCT leads.id) as lead_count');
			$ThisMonthCustomerLeadCounts->where('leads.created_at', '>=', $req_startDate);
			$ThisMonthCustomerLeadCounts->where('leads.created_at', '<=', $req_endDate);
			$ThisMonthCustomerLeadCounts = $ThisMonthCustomerLeadCounts->get();

			$ThisMonthCustomerLeadIds = Lead::query();
			$ThisMonthCustomerLeadIds->where('leads.created_at', '>=', $req_startDate);
			$ThisMonthCustomerLeadIds->where('leads.created_at', '<=', $req_endDate);
			$CustomerIds = $ThisMonthCustomerLeadIds->distinct()->pluck('id')->all();

			$ThisMonthCustomerLeadCount = 0;
			foreach ($ThisMonthCustomerLeadCounts as $CustomerLeadThisCount) {
				$ThisMonthCustomerLeadCount += $CustomerLeadThisCount->lead_count;
			}

			if($LastMonthCustomerLeadCount != 0 && $ThisMonthCustomerLeadCount != 0) {
				$diffrentCustomerLeadCount = ($ThisMonthCustomerLeadCount) - ($LastMonthCustomerLeadCount);
			} else {
				$diffrentCustomerLeadCount = 0;
			}

			$viewData[5]['col_1'] = "Customer";
			if($diffrentCustomerLeadCount > 0) {
				$diffrentCustomerOrderPer = (($LastMonthCustomerLeadCount * 100) / $ThisMonthCustomerLeadCount);
				$viewData[5]['col_2'] = '<div class="compare_badge" onclick="ViewSales(\'' . implode(',', $CustomerIds) . '\', \'LEAD\',  \'Customer\')" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="bottom" title=""><i class="bx bx-caret-down bx-flip-vertical" style="color:#077322"></i>'.number_format($diffrentCustomerOrderPer, 2, '.', ',').'% (+'.$diffrentCustomerLeadCount.')</div>';
			} else if($diffrentCustomerLeadCount < 0) {
				$diffrentCustomerOrderPer = (($ThisMonthCustomerLeadCount * 100) / $LastMonthCustomerLeadCount);
				$viewData[5]['col_2'] = '<div class="compare_badge1" onclick="ViewSales(\'' . implode(',', $CustomerIds) . '\', \'LEAD\',  \'Customer\')" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="bottom" title=""><i class="bx bx-caret-down" style="color:#ff0000"></i>'.number_format($diffrentCustomerOrderPer, 2, '.', ',').'% ('.$diffrentCustomerLeadCount.')</div>';
			} else {
				$viewData[5]['col_2'] = '<div class="compare_badge" onclick="ViewSales(\'' . implode(',', $CustomerIds) . '\', \'LEAD\',  \'Customer\')" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="bottom" title=""><i class="bx bx-caret-down bx-flip-vertical" style="color:#077322"></i>0% (+0)</div>';
			}
			$viewData[5]['col_3'] = $Customer->billing_amount;
		// CUSTOMER END

		$jsonData = [
			'draw' => intval($request['draw']),
			'recordsTotal' => 0,
			'recordsFiltered' => 0,
			'data' => $viewData,
		];
		return $jsonData;
	}

	public function searchState(Request $request)
	{

		$StateList = array();
		$StateList = StateList::select('id', 'name as text');
		$StateList->where('name', 'like', "%" . $request->q . "%");

		$StateList->limit(5);
		$StateList = $StateList->get();

		$response = array();
		$response['results'] = $StateList;
		$response['pagination']['more'] = false;
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function searchCity(Request $request)
	{

		$CityList = array();
		$CityList = CityList::select('id', 'name as text');
		$CityList->where('state_id', $request->state_id);
		$CityList->where('name', 'like', "%" . $request->q . "%");
		$CityList->where('status', 1);
		$CityList->limit(5);
		$CityList = $CityList->get();

		$response = array();
		$response['results'] = $CityList;
		$response['pagination']['more'] = false;
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function dashboardSalesOverviewajax(Request $request)
	{
		$isSalePerson = isSalePerson();
		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		$isCreUser = isCreUser();
		$dataIds = explode(',', $request->ids);
        $dataType = $request->type;
        $dataTitle = $request->title;

		$recordsTotal = 0;
		$recordsFiltered = 0;

		$DealBillingAmount = 0;
		$DealTotalAmount = 0;

		$data = array();
		if($dataType == "ORDER") {

			$searchColumns = array(
				0 => 'orders.id',
				1 => 'users.first_name',
				2 => 'users.last_name',
				3 => 'channel_partner.firm_name',
			);

			$sortingColumns = array(
				0 => 'orders.id',
				1 => 'orders.user_id',
				2 => 'orders.channel_partner_user_id',
				3 => 'orders.sale_persons',
				4 => 'orders.payment_mode',
				5 => 'orders.status',
				6 => 'orders.status',
			);

			$selectColumns = array(
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
				20 => 'orders.is_cancelled',
			);

			$recordsTotal = Order::query();
			$recordsTotal->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
			$recordsTotal->whereIn('orders.id', $dataIds);
			if ($isSalePerson == 1) {
				$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
				$recordsTotal->where(function ($query) use ($childSalePersonsIds) {
					foreach ($childSalePersonsIds as $key => $value) {
						if ($key == 0) {
							$query->whereRaw('FIND_IN_SET("' . $value . '",orders.sale_persons)>0');
						} else {
							$query->orWhereRaw('FIND_IN_SET("' . $value . '",orders.sale_persons)>0');
						}
					}
				});
				
				$recordsTotal->where('channel_partner.reporting_company_id', Auth::user()->company_id);
			} else if (isChannelPartner(Auth::user()->type) != 0) {

				$recordsTotal->where('orders.channel_partner_user_id', Auth::user()->id);
			}
			$recordsTotal = $recordsTotal->count();
			//$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.

			$query = Order::query();
			$query->leftJoin('users', 'users.id', '=', 'orders.user_id');
			$query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
			$query->leftJoin('users as channel_partner_user', 'channel_partner_user.id', '=', 'orders.channel_partner_user_id');
			$query->whereIn('orders.id', $dataIds);

			if ($isSalePerson == 1) {

				$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
				$query->where(function ($query2) use ($childSalePersonsIds) {
					
					foreach ($childSalePersonsIds as $key => $value) {
						if ($key == 0) {
							$query2->whereRaw('FIND_IN_SET("' . $value . '",orders.sale_persons)>0');
						} else {
							$query2->orWhereRaw('FIND_IN_SET("' . $value . '",orders.sale_persons)>0');
						}
					}
				});

				$query->where('channel_partner.reporting_company_id', Auth::user()->company_id);
			} else if (isChannelPartner(Auth::user()->type) != 0) {
				$query->where('orders.channel_partner_user_id', Auth::user()->id);
			}
			$query->select('orders.id');
			// $query->limit($request->length);
			// $query->offset($request->start);
			$query->orderBy($sortingColumns[$request['order'][0]['column']], $request['order'][0]['dir']);

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

			$recordsFiltered = $query->count();

			$query = Order::query();
			$query->leftJoin('users', 'users.id', '=', 'orders.user_id');
			$query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
			$query->leftJoin('users as channel_partner_user', 'channel_partner_user.id', '=', 'orders.channel_partner_user_id');
			$query->whereIn('orders.id', $dataIds);

			if ($isSalePerson == 1) {

				$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
				$query->where(function ($query2) use ($childSalePersonsIds) {

					foreach ($childSalePersonsIds as $key => $value) {
						if ($key == 0) {
							$query2->whereRaw('FIND_IN_SET("' . $value . '",orders.sale_persons)>0');
						} else {
							$query2->orWhereRaw('FIND_IN_SET("' . $value . '",orders.sale_persons)>0');
						}
					}
				});

				$query->where('channel_partner.reporting_company_id', Auth::user()->company_id);
			} else if (isChannelPartner(Auth::user()->type) != 0) {
				$query->where('orders.channel_partner_user_id', Auth::user()->id);
			}
			
			$query->select($selectColumns);
			$query->limit($request->length);
			$query->offset($request->start);
			$query->orderBy($sortingColumns[$request['order'][0]['column']], $request['order'][0]['dir']);

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

			$channelPartner = getChannelPartners();
			if (isset($request['order_id'])) {
				$order_id = $request['order_id'];
		
				// Add a condition to the query to filter by the specified order_id
				$query->where('orders.id', $order_id);
			}

			foreach ($data as $key => $value) {

				if (Auth::user()->type == 0) {

					$data[$key]['col_11'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">#' . $value['id'] . '</a></h5>
					<p class="text-muted mb-0" data-bs-toggle="tooltip"  data-bs-original-title="' . convertOrderDateTime($value['created_at'], "time") . '" ><a href="javascript:void(0)"  onclick="changeOrderDate(\'' . $value['id'] . '\')" >' . convertOrderDateTime($value['created_at'], "date") . '</a></p>';
				} else {

					$data[$key]['col_11'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">#' . $value['id'] . '</a></h5>
					<p class="text-muted mb-0" data-bs-toggle="tooltip"  data-bs-original-title="' . convertOrderDateTime($value['created_at'], "time") . '" >' . convertOrderDateTime($value['created_at'], "date") . '</p>';
				}

				$paymentMode = "";

				$paymentMode = getPaymentLable($value['payment_mode']);
				$channelPartnerType = '<span class="badge rounded-pill badge-soft-dark font-size-11">' . $channelPartner[$value['channel_partner_type']]['short_name'] . '</span>';
			
				$data[$key]['col_12'] = '<p class="text-muted mb-0" data-bs-toggle="tooltip" title="' . $value['first_name'] . '  ' . $value['last_name'] . '">' . displayStringLenth($value['first_name'] . '  ' . $value['last_name'], 10) . '</p>';

				$data[$key]['col_13'] = '<p class="text-muted mb-0 text-center"
				data-bs-toggle="tooltip" title="' . $value['channel_partner_user_first_name'] . ' ' . $value['channel_partner_user_last_name'] . '&#013;&#013; PHONE:' . $value['channel_partner_user_phone_number'] . '" >' . displayStringLenth($value['firm_name'], 15) . '</p><p class="text-muted mb-0 text-center">' . $channelPartnerType . " " . $paymentMode . '</p>';

				$sale_persons = explode(",", $value['sale_persons']);
				$Users = User::select('users.first_name', 'users.last_name', 'users.type', 'users.phone_number', 'sales_hierarchy.code as sales_hierarchy_code')->leftJoin('sale_person', 'sale_person.id', '=', 'users.reference_id')->leftJoin('sales_hierarchy', 'sales_hierarchy.id', '=', 'sale_person.type')->whereIn('users.id', $sale_persons)->get();

				$uiSalePerson = '';
				foreach ($Users as $kU => $vU) {
					$uiSalePerson .= '<span class="text-muted mb-0">' . $vU['first_name'] . ' ' . $vU['last_name'] . '<br>' . '&#013;' . $vU['sales_hierarchy_code'] . '<br>' . '&#013; PHONE:' . $vU['phone_number'] . '</span>';
				}

				$data[$key]['col_14'] = $uiSalePerson;

				$data[$key]['col_15'] = '<p class="text-muted mb-0">EXGST&nbsp&nbsp;&nbsp;&nbsp;: <i class="fas fa-rupee-sign"></i> <span class="price-lable-font">' . priceLable($value['total_mrp_minus_disocunt']) . '</span></p>

				<p class="text-muted mb-0 ">TOTAL&nbsp;&nbsp;&nbsp;&nbsp: <i class="fas fa-rupee-sign"></i> <span class="price-lable-font">' . priceLable($value['total_payable']) . '</span></p>
				';

				$data[$key]['sub_status'] = "";

				$data[$key]['col_17'] = '';
				$data[$key]['col_16'] = getOrderLable($value['status']);


				if ($value['status'] != 4 && $value['is_cancelled'] == 1) {
					$data[$key]['status'] = $data[$key]['status'] . "-" . '<span class="badge badge-pill badge badge-soft-danger font-size-11">PARTIALLY CANCELLED</span>';
				} else {

					if ($value['status'] == 1 || $value['status'] == 2) {
						$data[$key]['sub_status'] = getInvoiceLable($value['sub_status']);
					}


					if ($data[$key]['sub_status'] != "") {
						$data[$key]['col_16'] = $data[$key]['col_16'] . "-" . $data[$key]['sub_status'];
					}
				}

			}
		}else if($dataType == "LEAD"){

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
        	    $DealBillingAmount = numCommaFormat($Leadamount->billing_amount);
        	    // $total_other_amount = $Leadamount->other_amount;
        	    $DealTotalAmount = numCommaFormat($Leadamount->total_amount);
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
				'leads.is_deal',
				'leads.site_stage',
				'leads.closing_date_time',
				'leads.assigned_to',
				'leads.user_id',
				'leads.last_name',
				'lead_owner.first_name as lead_owner_first_name',
				'lead_owner.last_name  as lead_owner_last_name',
				'created_by.first_name as created_by_first_name',
				'created_by.last_name  as created_by_last_name',
				'leads.source_type',
				'leads.source',
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
			$query->orderBy($sortingColumns[$request['order'][0]['column']], $request['order'][0]['dir']);
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
			$query->limit($request->length);
			$query->offset($request->start);
			$query->orderBy($sortingColumns[$request['order'][0]['column']], $request['order'][0]['dir']);
	
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
	
					$routeLead = route('crm.lead') . "?id=" . $value['id'];
				} else {
	
					$routeLead = route('crm.deal') . "?id=" . $value['id'];
				}
	
				$data[$key] = array();
				if ($value['inquiry_id'] != 0) {
					$inquiry_id = " - " . $value['inquiry_id'];
				} else {
					$inquiry_id = '';
				}
	
				if ($value['is_deal'] == 0) {
					$prifix = 'L';
				} else if ($value['is_deal'] == 1) {
					$prifix = 'D';
				}
	
				$data[$key]['col_11'] = "<a href='" . $routeLead . "' > " . "#" . $prifix . $value['id'] . $inquiry_id . "</a>";

				$data[$key]['col_12'] = '<p class="text-muted mb-0">' . $value['first_name'] . " " . $value['last_name'] . '</p>
				<p class="text-muted mb-0">' . $value['phone_number'] . '</p>';
	
				$CRMSettingStageOfSite = CRMSettingStageOfSite::find($value['site_stage']);
				$site_stage = "";
            	if ($CRMSettingStageOfSite) {
					$site_stage = $CRMSettingStageOfSite->name;
            	}
				$data[$key]['col_13'] = $site_stage;

				$closing_date_time = $value['closing_date_time'];
            	if (($closing_date_time != '') || ($closing_date_time != null)) {

            	    $closing_date_time = date('Y-m-d', strtotime($closing_date_time));
            	} else {
            	    $closing_date_time = "-";

            	}

				if ($value['is_deal'] == 0) {
					$data[$key]['col_14'] = "-";
				} else if ($value['is_deal'] == 1) {
					$data[$key]['col_14'] = $closing_date_time;
				}


				$source_type = explode("-", $value['source_type']);

				$sourceType = '';
				foreach (getLeadSourceTypes() as $skey => $svalue) {
					if ($svalue['type'] == $source_type[0] && $svalue['id'] == $source_type[1]) {
						$sourceType = $svalue['lable'];
					}
				}

				$source = '';
				$source .= '<span>'. $value['lead_owner_first_name'] . ' ' . $value['lead_owner_last_name'].'</span>';
				$source .= '<div class="border my-1"></div>';

				if($source_type[0] == 'user') {
					if(in_array($source_type[1], array(101, 102, 103, 104, 105))) {
						$sourceUser = ChannelPartner::select('firm_name')->where('user_id', $value['source'])->first();
						if($sourceUser) {
							$source .= '<span>'.$sourceUser['firm_name'] .'</span> - <span class="badge badge-pill badge-soft-success">' .$sourceType. '</span>';
						} else {
							$source .= '';
						}
					} else {
						$sourceUser = User::find($value['source']);
						if($sourceUser) {
							$source .= '<span>'.$sourceUser['first_name'] .''. $sourceUser['last_name'] .'</span> - <span class="badge badge-pill badge-soft-success">' .$sourceType. '</span>';
						} else {
							$source .= '';
						}
					}
				} else if($source_type[0] == 'exhibition') {
					$sourceUser = Exhibition::find($value['source']);
					if($sourceUser) {
						$source .= '<span>'.$sourceUser['name'] . '</span> - <span class="badge badge-pill badge-soft-success">' .$sourceType. '</span>';
					} else {
						$source .= '';
					}
				} else {
					$source .= '<span>'. $value['source'] .'</span> - <span class="badge badge-pill badge-soft-success">' .$sourceType. '</span>';
				}
				$source .= '';


				$data[$key]['col_15'] =  $source;
				if ($value['status'] != 0) {
					$data[$key]['col_16'] = $LeadStatus[$value['status']]['name'];
				} else {
					$data[$key]['col_16'] = "not define";
				}

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
        		$Leadamount->where('leads.id', $value['id']);
        		$Leadamount->where('wltrn_quotation.isfinal', 1);
        		$Leadamount = $Leadamount->first();
        		if($Leadamount){
					$total_whitelion_amount = numCommaFormat($Leadamount->whitelion_amount);
        		    // $total_billing_amount = numCommaFormat($Leadamount->billing_amount);
        		    $total_other_amount = numCommaFormat($Leadamount->other_amount);
        		    $total_amount = numCommaFormat($Leadamount->total_amount);
        		}
			
				$data[$key]['col_17'] = '<p class="text-muted mb-0">Whitelion : ' . $total_whitelion_amount . '</p>
				<p class="text-muted mb-0">Other : ' . $total_other_amount . '</p>
				<p class="text-muted mb-0">Total : ' . $total_amount . '</p>';

			}
		}

		$jsonData = array(
			"draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
			"recordsTotal" => intval($recordsTotal), // total number of records
			"recordsFiltered" => intval($recordsFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
			"data" => $data, // total data array
			'report_total_amount' => $DealTotalAmount, // total data array
            'report_billing_amount' => $DealBillingAmount,
            'title' => $dataTitle,
		);
		return $jsonData;
	}
}
