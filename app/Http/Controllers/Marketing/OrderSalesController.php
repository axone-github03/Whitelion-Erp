<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\ChannelPartner;
use App\Models\CityList;
use App\Models\CountryList;
use App\Models\InvoiceItem;
use App\Models\MarketingChallan;
use App\Models\MarketingChallanItem;
use App\Models\MarketingOrder;
use App\Models\MarketingOrderItem;
use App\Models\MarketingProductInventory;
use App\Models\StateList;
use App\Models\User;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

use function GuzzleHttp\json_encode;

//use Session;

class OrderSalesController extends Controller
{

	public function __construct()
	{

		$this->middleware(function ($request, $next) {

			$tabCanAccessBy = array(0, 1, 3, 6, 13);

			if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
				return redirect()->route('dashboard');
			}

			return $next($request);
		});
	}

	public function index()
	{
		$data = array();
		$data['title'] = "Material Request List";
		return view('marketing/orders/sales', compact('data'));
	}

	public function index2()
	{
		$data = array();
		$data['title'] = "Sales Approve Request";
		return view('marketing/orders/sales2', compact('data'));
	}

	public function index3()
	{
		$data = array();
		$data['title'] = "Sales Rejected Request";
		return view('marketing/orders/sales3', compact('data'));
	}


	function ajax(Request $request)
	{

		// $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		// $isMarketingUser = isMarketingUser();

		$searchColumns = array(
			0 => 'marketing_orders.id',
			1 => 'users.first_name',
			2 => 'users.last_name',
			3 => 'channel_partner.firm_name',

		);

		$sortingColumns = array(
			0 => 'marketing_orders.id',
			1 => 'marketing_orders.user_id',
			2 => 'marketing_orders.channel_partner_user_id',
			3 => 'marketing_orders.sale_persons',
			4 => 'marketing_orders.payment_mode',
			5 => 'marketing_orders.status',

		);

		$selectColumns = array(
			0 => 'marketing_orders.id',
			1 => 'marketing_orders.user_id',
			2 => 'marketing_orders.channel_partner_user_id',
			3 => 'marketing_orders.sale_persons',
			4 => 'marketing_orders.payment_mode',
			5 => 'marketing_orders.status',
			6 => 'marketing_orders.created_at',
			7 => 'users.first_name as first_name',
			8 => 'users.last_name as last_name',
			9 => 'channel_partner.firm_name',
			10 => 'marketing_orders.payment_mode',
			11 => 'marketing_orders.total_mrp_minus_disocunt',
			12 => 'marketing_orders.total_payable',
			13 => 'marketing_orders.pending_total_payable',
			14 => 'marketing_orders.sub_status',
			15 => 'channel_partner.type as channel_partner_type',
			16 => 'channel_partner_user.first_name as channel_partner_user_first_name',
			17 => 'channel_partner_user.last_name as channel_partner_user_last_name',
			18 => 'channel_partner_user.phone_number as channel_partner_user_phone_number',
			19 => 'marketing_orders.challan',
			20 => 'marketing_orders.is_self',
			21 => 'users.phone_number'

		);

		$query = MarketingOrder::query();
		$query->select('marketing_orders.id');
		$query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'marketing_orders.channel_partner_user_id');
		$query->whereIn('marketing_orders.status', array(0, 1, 2, 3));
		//	$query->whereIn('marketing_orders.status', array(0));

		// if ($isAccountUser == 1 || $isAdminOrCompanyAdmin == 1) {

		// 	if (Auth::user()->parent_id != 0) {

		// 		$parent = ChannelPartner::where('user_id', Auth::user()->parent_id)->first();

		// 		$query->where('channel_partner.reporting_manager_id', $parent->user_id);

		// 	} else {

		// 		$query->where('channel_partner.reporting_company_id', Auth::user()->company_id);
		// 		$query->where('channel_partner.reporting_manager_id', 0);

		// 	}

		// } else if (isChannelPartner(Auth::user()->type) != 0) {

		// 	$query->where('channel_partner.reporting_manager_id', Auth::user()->id);

		// }

		$recordsTotal = $query->count();

		$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.
		$query = MarketingOrder::query();
		$query->leftJoin('users', 'users.id', '=', 'marketing_orders.user_id');
		$query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'marketing_orders.channel_partner_user_id');
		$query->leftJoin('users as channel_partner_user', 'channel_partner_user.id', '=', 'marketing_orders.channel_partner_user_id');

		// if ($isAccountUser == 1 || $isAdminOrCompanyAdmin == 1) {
		// 	if (Auth::user()->parent_id != 0) {
		// 		$parent = ChannelPartner::where('user_id', Auth::user()->parent_id)->first();
		// 		$query->where('channel_partner.reporting_manager_id', $parent->user_id);

		// 	} else {

		// 		$query->where('channel_partner.reporting_company_id', Auth::user()->company_id);
		// 		$query->where('channel_partner.reporting_manager_id', 0);

		// 	}

		// } else if (isChannelPartner(Auth::user()->type) != 0) {

		// 	$query->where('channel_partner.reporting_manager_id', Auth::user()->id);

		// }

		$query->whereIn('marketing_orders.status', array(0, 1, 2, 3));
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
		$channelPartner = getChannelPartners();

		foreach ($data as $key => $value) {

			$data[$key]['detail'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">#MR' . highlightString($value['id'],$search_value) . '</a></h5>
                <p class="text-muted mb-0" data-bs-toggle="tooltip"  data-bs-original-title="' . convertOrderDateTime($value['created_at'], "time") . '" >' . highlightString(convertOrderDateTime($value['created_at'], "date"),$search_value) . '</p>';

			$paymentMode = "";
			$channelPartnerType = "";

			//$paymentMode = getPaymentLable($value['payment_mode']);

			if ($value['is_self'] == 0) {
				$channelPartnerType = '<span class="badge rounded-pill badge-soft-dark font-size-11">' . $channelPartner[$value['channel_partner_type']]['short_name'] . '</span>';
			} else {
				$channelPartnerType = '<span class="badge rounded-pill badge-soft-dark font-size-11">SELF</p>';
			}


			$data[$key]['order_by'] = '<p class="text-muted mb-0" data-bs-toggle="tooltip" title="' . $value['first_name'] . '  ' . $value['last_name'] . '">' .  highlightString(displayStringLenth($value['first_name'] . '  ' . $value['last_name'], 10),$search_value) . '</p>';
			$data[$key]['channel_partner'] = "";
			if ($value['is_self'] == 0) {
				$data[$key]['channel_partner'] = '<p class="text-muted mb-0 text-center"
                data-bs-toggle="tooltip" title="' . $value['channel_partner_user_first_name'] . ' ' . $value['channel_partner_user_last_name'] . '&#013;&#013; PHONE:' . $value['channel_partner_user_phone_number'] . '" >' .  highlightString(displayStringLenth($value['firm_name'], 15),$search_value) . '</p><p class="text-muted mb-0 text-center">' . $channelPartnerType . " " . $paymentMode . '</p>';
			} else {

				$data[$key]['channel_partner'] = '<p class="text-muted mb-0 text-center"
                data-bs-toggle="tooltip" title="' . $value['first_name'] . ' ' . $value['last_name'] . '&#013;&#013; PHONE:' . $value['phone_number'] . '" >' .  highlightString(displayStringLenth($value['first_name'] . ' ' . $value['last_name'], 15),$search_value) . '</p><p class="text-muted mb-0 text-center">' . $channelPartnerType . " " . $paymentMode . '</p>';
			}
			$sale_persons = explode(",", $value['sale_persons']);

			$Users = User::select('users.first_name', 'users.last_name', 'users.type', 'users.phone_number', 'sales_hierarchy.code as sales_hierarchy_code')->leftJoin('sale_person', 'sale_person.id', '=', 'users.reference_id')->leftJoin('sales_hierarchy', 'sales_hierarchy.id', '=', 'sale_person.type')->whereIn('users.id', $sale_persons)->get();

			$uiSalePerson = '<ul class="list-inline font-size-20 contact-links mb-0">';
			foreach ($Users as $kU => $vU) {
				$uiSalePerson .= '<li class="list-inline-item px-2">';
				$uiSalePerson .= '<a  data-bs-toggle="tooltip" title="' . $vU['first_name'] . ' ' . $vU['last_name'] . '&#013;' . $vU['sales_hierarchy_code'] . '&#013; PHONE:' . $vU['phone_number'] . '" href="javascript: void(0);" ><i class="bx bx-user"></i></a>';
				$uiSalePerson .= '</li>';
			}

			$uiSalePerson .= '</ul>';

			$data[$key]['sale_persons'] = $uiSalePerson;

			// $data[$key]['payment_detail'] = '<p class="text-muted mb-0">EXGST&nbsp&nbsp;&nbsp;&nbsp;: <i class="fas fa-rupee-sign"></i> <span class="price-lable-font">' . priceLable($value['total_mrp_minus_disocunt']) . '</span></p>

			// <p class="text-muted mb-0 ">TOTAL&nbsp;&nbsp;&nbsp;&nbsp: <i class="fas fa-rupee-sign"></i> <span class="price-lable-font">' . priceLable($value['total_payable']) . '</span></p>

			// ';

			$data[$key]['sub_status'] = "";

			$data[$key]['status'] = getMarketingRequestStatus($value['status']);
			$statusClass = "";

			if ($value['status'] == 0) {
				$statusClass = 'badge-soft-warning ';
			} else if ($value['status'] == 1) {
				$statusClass = 'badge-soft-success ';
			} else if ($value['status'] == 2) {
				$statusClass = 'badge-soft-success ';
			} else if ($value['status'] == 3) {
				$statusClass = 'badge-soft-danger ';
			} else {
				$statusClass = 'badge-soft-warning ';
			}

			$data[$key]['status'] = '<span class="badge ' . $statusClass . ' badge-pill badgefont-size-11">' . $data[$key]['status'] . '</span>';

			$uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';

			$uiAction .= '<li class="list-inline-item px-2">';
			$uiAction .= '<a onclick="ViewOrder(\'' . $value['id'] . '\')" href="javascript: void(0);" title="View"><i class="mdi mdi-eye"></i></a>';
			$uiAction .= '</li>';
			$status2 = "";

			$MarketingChallanstatus = 0;
			if ($value['challan'] != "") {

				$MarketingChallan = MarketingChallan::where('id', $value['challan'])->where('invoice_number', '!=', "")->first();
				if ($MarketingChallan) {

					$status2 = getMarketingRequestDelieveryChallanStatus($MarketingChallan->status);

					$statusClass = "";

					$MarketingChallanstatus = $MarketingChallan->status;
					if ($MarketingChallan->status == 0) {
						$statusClass = 'badge-soft-warning ';
					} else if ($MarketingChallan->status == 1) {
						$statusClass = 'badge-soft-primary ';
					} else if ($MarketingChallan->status == 2) {
						$statusClass = 'badge-soft-success ';
					}
					if($MarketingChallanstatus == 2 && $value['status'] == 2){
						$status2 = '<span class="badge badge-soft-success badge-pill badgefont-size-11"> PARTIALLY DISPATCHED </span>';
					}else{
						$status2 = '<span class="badge ' . $statusClass . ' badge-pill badgefont-size-11">' . $status2 . '</span>';

					}
					
					$uiAction .= '<li class="list-inline-item px-2">';
					$uiAction .= '<a href="' . getSpaceFilePath($MarketingChallan->invoice_file) . '" target="_blank" title="Challan"><i class="bx bxs-file-pdf">' . $MarketingChallan->invoice_number . '</i></a>';
					$uiAction .= '</li>';
				}
			}
			
			$data[$key]['status'] = $data[$key]['status'] . " " . $status2;

			// print_r($challan);
			// die;

			// if ($value['invoice'] != "") {

			// 	$routeInvoice = route('marketing_orders.sales.invoice.list') . "?order_id=" . $value['id'];

			// 	$uiAction .= '<li class="list-inline-item px-2">';
			// 	$uiAction .= '<a target="_blank"  href="' . $routeInvoice . '" title="Invoice"><i class="bx bx-receipt"></i></a>';
			// 	$uiAction .= '</li>';

			// }

			$uiAction .= '</ul>';
			$data[$key]['action'] = $uiAction;
		}

		$jsonData = array(
			"draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
			"recordsTotal" => intval($recordsTotal), // total number of records
			"recordsFiltered" => intval($recordsFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
			"data" => $data, // total data array

		);
		return $jsonData;
	}

	function ajax2(Request $request)
	{

		$isSalePerson = isSalePerson();
		if ($isSalePerson == 1) {
			$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
		}
		$isMarketingUser = isMarketingUser();
		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();

		$searchColumns = array(
			0 => 'marketing_orders_challan.id',
			1 => 'marketing_orders_challan.status',
			2 => 'users.first_name',
			3 => 'users.last_name',
			4 => 'channel_partner.firm_name',
			5 => 'marketing_orders_challan.order_id',
			6 => 'marketing_orders_challan.invoice_number',
			7 => 'marketing_orders.status',
			8 => 'channel_partner.type',
			9 => 'channel_partner_user.first_name',
			10 => 'channel_partner_user.last_name',

		);

		$sortingColumns = array(
			0 => 'marketing_orders_challan.id',
			1 => 'marketing_orders.user_id',
			2 => 'marketing_orders.channel_partner_user_id',
			3 => 'marketing_orders.sale_persons',
			4 => 'marketing_orders.payment_mode',
			5 => 'marketing_orders_challan.status',

		);

		$selectColumns = array(
			0 => 'marketing_orders_challan.id',
			1 => 'marketing_orders.user_id',
			2 => 'marketing_orders.channel_partner_user_id',
			3 => 'marketing_orders.sale_persons',
			4 => 'marketing_orders.payment_mode',
			5 => 'marketing_orders_challan.status',
			6 => 'marketing_orders_challan.created_at',
			7 => 'users.first_name as first_name',
			8 => 'users.last_name as last_name',
			9 => 'channel_partner.firm_name',
			10 => 'marketing_orders.payment_mode',
			11 => 'marketing_orders_challan.gst_tax',
			12 => 'marketing_orders_challan.total_payable',
			13 => 'marketing_orders_challan.order_id',
			14 => 'marketing_orders_challan.invoice_number',
			15 => 'marketing_orders.status as marketing_order_status',
			16 => 'channel_partner.type as channel_partner_type',
			17 => 'channel_partner_user.first_name as channel_partner_user_first_name',
			18 => 'channel_partner_user.last_name as channel_partner_user_last_name',
			19 => 'channel_partner_user.phone_number as channel_partner_user_phone_number',
			20 => 'marketing_orders.challan',
			21 => 'marketing_orders.is_self',
			22 => 'users.phone_number'

		);

		$query = MarketingChallan::query();
		$query->leftJoin('marketing_orders', 'marketing_orders_challan.order_id', '=', 'marketing_orders.id');
		$query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'marketing_orders.channel_partner_user_id');

		if ($isAdminOrCompanyAdmin == 1) {
		} else if ($isSalePerson == 1) {

			$query->where(function ($query) use ($childSalePersonsIds) {

				foreach ($childSalePersonsIds as $key => $value) {
					if ($key == 0) {
						$query->whereRaw('FIND_IN_SET("' . $value . '",marketing_orders.sale_persons)>0');
					} else {
						$query->orWhereRaw('FIND_IN_SET("' . $value . '",marketing_orders.sale_persons)>0');
					}
				}
			});

			$query->where('channel_partner.reporting_company_id', Auth::user()->company_id);
		} else if ($isMarketingUser == 1) {
		}

		$query->where('marketing_orders_challan.invoice_number', '');

		$recordsTotal = $query->count();
		$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.
		$query = MarketingChallan::query();
		$query->leftJoin('marketing_orders', 'marketing_orders_challan.order_id', '=', 'marketing_orders.id');
		$query->leftJoin('users', 'users.id', '=', 'marketing_orders.user_id');
		$query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'marketing_orders.channel_partner_user_id');
		$query->leftJoin('users as channel_partner_user', 'channel_partner_user.id', '=', 'marketing_orders.channel_partner_user_id');

		//$query->where('channel_partner.reporting_company_id', Auth::user()->company_id);
		//$query->where('marketing_orders_challan.order_id', $request->order_id);
		$query->where('marketing_orders_challan.invoice_number', '');

		if ($isAdminOrCompanyAdmin == 1) {
		} else if ($isSalePerson == 1) {

			$query->where(function ($query) use ($childSalePersonsIds) {

				foreach ($childSalePersonsIds as $key => $value) {
					if ($key == 0) {
						$query->whereRaw('FIND_IN_SET("' . $value . '",marketing_orders.sale_persons)>0');
					} else {
						$query->orWhereRaw('FIND_IN_SET("' . $value . '",marketing_orders.sale_persons)>0');
					}
				}
			});

			$query->where('channel_partner.reporting_company_id', Auth::user()->company_id);
		} else if ($isMarketingUser == 1) {
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
		$channelPartner = getChannelPartners();
		foreach ($data as $key => $value) {

			$data[$key]['detail'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">#' . highlightString($value['invoice_number'],$search_value) . '</a></h5>
			    <p class="text-muted mb-0" data-bs-toggle="tooltip" title="Marketing Request">#MR' . highlightString(($value['order_id']),$search_value) . '</p>
                <p class="text-muted mb-0" data-bs-toggle="tooltip"  data-bs-original-title="' . convertOrderDateTime($value['created_at'], "time") . '" >' . highlightString(convertOrderDateTime($value['created_at'], "date"),$search_value) . '</p>';

			$paymentMode = "";
			$channelPartnerType = "";

			//$paymentMode = '<span class="badge rounded-pill badge-soft-dark font-size-11">' . getPaymentModeName($value['payment_mode']) . '</span>';

			// $data[$key]['order_by'] = '<p class="text-muted mb-0">' . $value['first_name'] . '  ' . $value['last_name'] . '</p>';
			// $data[$key]['channel_partner'] = '<p class="text-muted mb-0">' . $value['firm_name'] . '</p><p class="text-muted mb-0">' . $paymentMode . '</p>';



			if ($value['is_self'] == 0) {
				$channelPartnerType = '<span class="badge rounded-pill badge-soft-dark font-size-11">' . $channelPartner[$value['channel_partner_type']]['short_name'] . '</span>';
			} else {
				$channelPartnerType = '<span class="badge rounded-pill badge-soft-dark font-size-11">SELF</p>';
			}


			$data[$key]['order_by'] = '<p class="text-muted mb-0" data-bs-toggle="tooltip" title="' . $value['first_name'] . '  ' . $value['last_name'] . '">' . highlightString(displayStringLenth($value['first_name'] . '  ' . $value['last_name'], 10),$search_value) . '</p>';
			$data[$key]['channel_partner'] = "";
			if ($value['is_self'] == 0) {
				$data[$key]['channel_partner'] = '<p class="text-muted mb-0 text-center"
                data-bs-toggle="tooltip" title="' . $value['channel_partner_user_first_name'] . ' ' . $value['channel_partner_user_last_name'] . '&#013;&#013; PHONE:' . $value['channel_partner_user_phone_number'] . '" >' . highlightString(displayStringLenth($value['firm_name'], 15),$search_value) . '</p><p class="text-muted mb-0 text-center">' . $channelPartnerType . " " . $paymentMode . '</p>';
			} else {

				$data[$key]['channel_partner'] = '<p class="text-muted mb-0 text-center"
                data-bs-toggle="tooltip" title="' . $value['first_name'] . ' ' . $value['last_name'] . '&#013;&#013; PHONE:' . $value['phone_number'] . '" >' . highlightString(displayStringLenth($value['first_name'] . ' ' . $value['last_name'], 15),$search_value) . '</p><p class="text-muted mb-0 text-center">' . $channelPartnerType . " " . $paymentMode . '</p>';
			}


			$sale_persons = explode(",", $value['sale_persons']);

			$Users = User::select('first_name', 'last_name')->whereIn('id', $sale_persons)->get();

			$uiSalePerson = '<ul class="list-inline font-size-20 contact-links mb-0">';
			foreach ($Users as $kU => $vU) {
				$uiSalePerson .= '<li class="list-inline-item px-2">';
				$uiSalePerson .= '<a  data-bs-toggle="tooltip" title="' . $vU['first_name'] . ' ' . $vU['last_name'] . '" href="javascript: void(0);" ><i class="bx bx-user"></i></a>';
				$uiSalePerson .= '</li>';
			}

			$uiSalePerson .= '</ul>';

			$data[$key]['sale_persons'] = $uiSalePerson;

			$data[$key]['payment_detail'] = '<p class="text-muted mb-0">EXGST&nbsp&nbsp;&nbsp;&nbsp;: <i class="fas fa-rupee-sign"></i> <span class="price-lable-font">' . priceLable($value['gst_tax']) . '</span></p>

			<p class="text-muted mb-0 ">TOTAL&nbsp;&nbsp;&nbsp;&nbsp;&nbsp: <i class="fas fa-rupee-sign"></i> <span class="price-lable-font">' . priceLable($value['total_payable']) . '</span></p>


			   ';

			$data[$key]['status'] = getMarketingRequestDelieveryChallanStatus($value['status']);

			$statusClass = "";

			if ($value['status'] == 0) {
				$statusClass = 'badge-soft-warning ';
			} else if ($value['status'] == 1) {
				$statusClass = 'badge-soft-primary ';
			} else if ($value['status'] == 2) {
				$statusClass = 'badge-soft-success ';
			}

			$invoceStatus = '<span class="badge ' . $statusClass . ' badge-pill badgefont-size-11">' . $data[$key]['status'] . '</span>';

			$orderStatus = getMarketingRequestStatus($value['marketing_order_status']);
			$statusClass = "";

			if ($value['marketing_order_status'] == 0) {
				$statusClass = 'badge-soft-warning ';
			} else if ($value['marketing_order_status'] == 1) {
				$statusClass = 'badge-soft-success ';
			} else if ($value['marketing_order_status'] == 2) {
				$statusClass = 'badge-soft-success ';
			} else if ($value['marketing_order_status'] == 3) {
				$statusClass = 'badge-soft-danger ';
			} else {
				$statusClass = 'badge-soft-warning ';
			}
			$data[$key]['status'] = '<span class="badge ' . $statusClass . ' badge-pill badgefont-size-11">' . $orderStatus . '</span>';

			$uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';

			$uiAction .= '<li class="list-inline-item px-2">';
			$uiAction .= '<a onclick="ViewMarketingChallan(\'' . $value['id'] . '\')" href="javascript: void(0);" title="View"><i class="mdi mdi-eye"></i></a>';
			$uiAction .= '</li>';

			// $uiAction .= '<li class="list-inline-item px-2">';
			// $uiAction .= '<a onclick="EditMarketingChallan(\'' . $value['id'] . '\')" href="javascript: void(0);" title="View"><i class="mdi mdi-square-edit-outline"></i></a>';
			// $uiAction .= '</li>';

			// $uiAction .= '<li class="list-inline-item px-2">';

			// $uiAction .= '<a target="_blank" href="' . getSpaceFilePath($value['invoice_file']) . '" title="PDF"><i class="bx bxs-file-pdf"></i></a>';
			// $uiAction .= '</li>';

			$uiAction .= '</ul>';
			$data[$key]['action'] = $uiAction;
		}

		$jsonData = array(
			"draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
			"recordsTotal" => intval($recordsTotal), // total number of records
			"recordsFiltered" => intval($recordsFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
			"data" => $data, // total data array

		);
		return $jsonData;
	}


	function ajax3(Request $request)
	{


		// $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		// $isMarketingUser = isMarketingUser();

		$searchColumns = array(
			0 => 'marketing_orders.id',
			1 => 'users.first_name',
			2 => 'users.last_name',
			3 => 'channel_partner.firm_name',

		);

		$sortingColumns = array(
			0 => 'marketing_orders.id',
			1 => 'marketing_orders.user_id',
			2 => 'marketing_orders.channel_partner_user_id',
			3 => 'marketing_orders.sale_persons',
			4 => 'marketing_orders.payment_mode',
			5 => 'marketing_orders.status',

		);

		$selectColumns = array(
			0 => 'marketing_orders.id',
			1 => 'marketing_orders.user_id',
			2 => 'marketing_orders.channel_partner_user_id',
			3 => 'marketing_orders.sale_persons',
			4 => 'marketing_orders.payment_mode',
			5 => 'marketing_orders.status',
			6 => 'marketing_orders.created_at',
			7 => 'users.first_name as first_name',
			8 => 'users.last_name as last_name',
			9 => 'channel_partner.firm_name',
			10 => 'marketing_orders.payment_mode',
			11 => 'marketing_orders.total_mrp_minus_disocunt',
			12 => 'marketing_orders.total_payable',
			13 => 'marketing_orders.pending_total_payable',
			14 => 'marketing_orders.sub_status',
			15 => 'channel_partner.type as channel_partner_type',
			16 => 'channel_partner_user.first_name as channel_partner_user_first_name',
			17 => 'channel_partner_user.last_name as channel_partner_user_last_name',
			18 => 'channel_partner_user.phone_number as channel_partner_user_phone_number',
			19 => 'marketing_orders.challan',
			20 => 'marketing_orders.is_self',
			21 => 'users.phone_number'

		);

		$query = MarketingOrder::query();
		$query->select('marketing_orders.id');
		$query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'marketing_orders.channel_partner_user_id');
		$query->whereIn('marketing_orders.status', array(3));

		// if ($isAccountUser == 1 || $isAdminOrCompanyAdmin == 1) {

		// 	if (Auth::user()->parent_id != 0) {

		// 		$parent = ChannelPartner::where('user_id', Auth::user()->parent_id)->first();

		// 		$query->where('channel_partner.reporting_manager_id', $parent->user_id);

		// 	} else {

		// 		$query->where('channel_partner.reporting_company_id', Auth::user()->company_id);
		// 		$query->where('channel_partner.reporting_manager_id', 0);

		// 	}

		// } else if (isChannelPartner(Auth::user()->type) != 0) {

		// 	$query->where('channel_partner.reporting_manager_id', Auth::user()->id);

		// }

		$recordsTotal = $query->count();

		$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.
		$query = MarketingOrder::query();
		$query->leftJoin('users', 'users.id', '=', 'marketing_orders.user_id');
		$query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'marketing_orders.channel_partner_user_id');
		$query->leftJoin('users as channel_partner_user', 'channel_partner_user.id', '=', 'marketing_orders.channel_partner_user_id');

		// if ($isAccountUser == 1 || $isAdminOrCompanyAdmin == 1) {
		// 	if (Auth::user()->parent_id != 0) {
		// 		$parent = ChannelPartner::where('user_id', Auth::user()->parent_id)->first();
		// 		$query->where('channel_partner.reporting_manager_id', $parent->user_id);

		// 	} else {

		// 		$query->where('channel_partner.reporting_company_id', Auth::user()->company_id);
		// 		$query->where('channel_partner.reporting_manager_id', 0);

		// 	}

		// } else if (isChannelPartner(Auth::user()->type) != 0) {

		// 	$query->where('channel_partner.reporting_manager_id', Auth::user()->id);

		// }

		$query->whereIn('marketing_orders.status', array(3));
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
		$channelPartner = getChannelPartners();

		foreach ($data as $key => $value) {

			$data[$key]['detail'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">#MR' . highlightString($value['id'],$search_value) . '</a></h5>
                <p class="text-muted mb-0" data-bs-toggle="tooltip"  data-bs-original-title="' . convertOrderDateTime($value['created_at'], "time") . '" >' . highlightString(convertOrderDateTime($value['created_at'], "date"),$search_value) . '</p>';

			$paymentMode = "";

			//$paymentMode = getPaymentLable($value['payment_mode']);

			if ($value['is_self'] == 0) {
				$channelPartnerType = '<span class="badge rounded-pill badge-soft-dark font-size-11">' . $channelPartner[$value['channel_partner_type']]['short_name'] . '</span>';
			} else {
				$channelPartnerType = '<span class="badge rounded-pill badge-soft-dark font-size-11">SELF</p>';
			}


			$data[$key]['order_by'] = '<p class="text-muted mb-0" data-bs-toggle="tooltip" title="' . $value['first_name'] . '  ' . $value['last_name'] . '">' . highlightString(displayStringLenth($value['first_name'] . '  ' . $value['last_name'], 10),$search_value) . '</p>';
			$data[$key]['channel_partner'] = "";
			if ($value['is_self'] == 0) {
				$data[$key]['channel_partner'] = '<p class="text-muted mb-0 text-center"
                data-bs-toggle="tooltip" title="' . $value['channel_partner_user_first_name'] . ' ' . $value['channel_partner_user_last_name'] . '&#013;&#013; PHONE:' . $value['channel_partner_user_phone_number'] . '" >' . highlightString(displayStringLenth($value['firm_name'], 15),$search_value) . '</p><p class="text-muted mb-0 text-center">' . $channelPartnerType . " " . $paymentMode . '</p>';
			} else {

				$data[$key]['channel_partner'] = '<p class="text-muted mb-0 text-center"
                data-bs-toggle="tooltip" title="' . $value['first_name'] . ' ' . $value['last_name'] . '&#013;&#013; PHONE:' . $value['phone_number'] . '" >' . highlightString(displayStringLenth($value['first_name'] . ' ' . $value['last_name'], 15),$search_value) . '</p><p class="text-muted mb-0 text-center">' . $channelPartnerType . " " . $paymentMode . '</p>';
			}

			$sale_persons = explode(",", $value['sale_persons']);

			$Users = User::select('users.first_name', 'users.last_name', 'users.type', 'users.phone_number', 'sales_hierarchy.code as sales_hierarchy_code')->leftJoin('sale_person', 'sale_person.id', '=', 'users.reference_id')->leftJoin('sales_hierarchy', 'sales_hierarchy.id', '=', 'sale_person.type')->whereIn('users.id', $sale_persons)->get();

			$uiSalePerson = '<ul class="list-inline font-size-20 contact-links mb-0">';
			foreach ($Users as $kU => $vU) {
				$uiSalePerson .= '<li class="list-inline-item px-2">';
				$uiSalePerson .= '<a  data-bs-toggle="tooltip" title="' . $vU['first_name'] . ' ' . $vU['last_name'] . '&#013;' . $vU['sales_hierarchy_code'] . '&#013; PHONE:' . $vU['phone_number'] . '" href="javascript: void(0);" ><i class="bx bx-user"></i></a>';
				$uiSalePerson .= '</li>';
			}

			$uiSalePerson .= '</ul>';

			$data[$key]['sale_persons'] = $uiSalePerson;

			// $data[$key]['payment_detail'] = '<p class="text-muted mb-0">EXGST&nbsp&nbsp;&nbsp;&nbsp;: <i class="fas fa-rupee-sign"></i> <span class="price-lable-font">' . priceLable($value['total_mrp_minus_disocunt']) . '</span></p>

			// <p class="text-muted mb-0 ">TOTAL&nbsp;&nbsp;&nbsp;&nbsp: <i class="fas fa-rupee-sign"></i> <span class="price-lable-font">' . priceLable($value['total_payable']) . '</span></p>

			// ';

			$data[$key]['sub_status'] = "";

			$data[$key]['status'] = getMarketingRequestStatus($value['status']);
			$statusClass = "";

			if ($value['status'] == 0) {
				$statusClass = 'badge-soft-warning ';
			} else if ($value['status'] == 1) {
				$statusClass = 'badge-soft-success ';
			} else if ($value['status'] == 2) {
				$statusClass = 'badge-soft-success ';
			} else if ($value['status'] == 3) {
				$statusClass = 'badge-soft-danger ';
			} else {
				$statusClass = 'badge-soft-warning ';
			}

			$data[$key]['status'] = '<span class="badge ' . $statusClass . ' badge-pill badgefont-size-11">' . $data[$key]['status'] . '</span>';

			$uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';

			$uiAction .= '<li class="list-inline-item px-2">';
			$uiAction .= '<a onclick="ViewOrder(\'' . $value['id'] . '\')" href="javascript: void(0);" title="View"><i class="mdi mdi-eye"></i></a>';
			$uiAction .= '</li>';
			$status2 = "";

			if ($value['challan'] != "") {

				$MarketingChallan = MarketingChallan::where('id', $value['challan'])->where('invoice_number', '!=', "")->first();
				if ($MarketingChallan) {

					$status2 = getMarketingRequestDelieveryChallanStatus($MarketingChallan->status);

					$statusClass = "";

					if ($MarketingChallan->status == 0) {
						$statusClass = 'badge-soft-warning ';
					} else if ($MarketingChallan->status == 1) {
						$statusClass = 'badge-soft-primary ';
					} else if ($MarketingChallan->status == 2) {
						$statusClass = 'badge-soft-success ';
					} else if ($MarketingChallan->status == 3) {
						$statusClass = 'badge-soft-danger ';
					}
					$status2 = '<span class="badge ' . $statusClass . ' badge-pill badgefont-size-11">' . $status2 . '</span>';

					$uiAction .= '<li class="list-inline-item px-2">';
					$uiAction .= '<a href="' . getSpaceFilePath($MarketingChallan->invoice_file) . '" target="_blank" title="Challan"><i class="bx bxs-file-pdf">' . $MarketingChallan->invoice_number . '</i></a>';
					$uiAction .= '</li>';
				}
			}

			$data[$key]['status'] = $data[$key]['status'] . " " . $status2;

			// print_r($challan);
			// die;

			// if ($value['invoice'] != "") {

			// 	$routeInvoice = route('marketing_orders.sales.invoice.list') . "?order_id=" . $value['id'];

			// 	$uiAction .= '<li class="list-inline-item px-2">';
			// 	$uiAction .= '<a target="_blank"  href="' . $routeInvoice . '" title="Invoice"><i class="bx bx-receipt"></i></a>';
			// 	$uiAction .= '</li>';

			// }

			$uiAction .= '</ul>';
			$data[$key]['action'] = $uiAction;
		}

		$jsonData = array(
			"draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
			"recordsTotal" => intval($recordsTotal), // total number of records
			"recordsFiltered" => intval($recordsFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
			"data" => $data, // total data array

		);
		return $jsonData;
	}

	public function detail(Request $request)
	{

		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		$isAccountUser = isAccountUser();

		$Order = MarketingOrder::query();
		$Order->select('marketing_orders.id', 'marketing_orders.is_self', 'marketing_orders.channel_partner_user_id', 'marketing_orders.created_at', 'marketing_orders.gst_number', 'marketing_orders.payment_mode', 'marketing_orders.shipping_cost', 'marketing_orders.sale_persons', 'marketing_orders.total_mrp_minus_disocunt', 'marketing_orders.total_mrp', 'marketing_orders.gst_tax', 'marketing_orders.gst_tax', 'marketing_orders.delievery_charge', 'marketing_orders.total_payable', 'marketing_orders.pending_total_payable', 'marketing_orders.bill_address_line1', 'marketing_orders.bill_address_line2', 'marketing_orders.bill_pincode', 'marketing_orders.bill_state_id', 'marketing_orders.bill_city_id', 'marketing_orders.bill_country_id', 'marketing_orders.d_address_line1', 'marketing_orders.d_address_line2', 'marketing_orders.d_pincode', 'marketing_orders.d_state_id', 'marketing_orders.d_city_id', 'marketing_orders.d_country_id', 'marketing_orders.remark', 'marketing_orders.remark', 'channel_partner_detail.email as channel_partner_email', 'channel_partner_detail.phone_number as channel_partner_phone_number', 'channel_partner_detail.dialing_code as channel_partner_dialing_code', 'channel_partner_detail.first_name as channel_partner_first_name', 'channel_partner_detail.last_name as channel_partner_last_name', 'channel_partner.firm_name as channel_partner_firm_name', 'channel_partner.type as channel_partner_type', 'channel_partner.credit_limit as channel_partner_credit_limit', 'channel_partner.credit_days as channel_partner_credit_days', 'channel_partner.pending_credit as channel_partner_pending_credit', 'channel_partner.credit_days as channel_partner_credit_days', 'marketing_orders.status');
		$Order->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'marketing_orders.channel_partner_user_id');
		$Order->leftJoin('users as channel_partner_detail', 'channel_partner_detail.id', '=', 'channel_partner.user_id');
		$Order->where('marketing_orders.id', $request->order_id);
		//$Order->where('channel_partner.reporting_company_id', Auth::user()->company_id);

		// if ($isAccountUser == 1 || $isAdminOrCompanyAdmin == 1) {
		// 	if (Auth::user()->parent_id != 0) {

		// 		$parent = ChannelPartner::where('user_id', Auth::user()->parent_id)->first();

		// 		$Order->where('channel_partner.reporting_manager_id', $parent->user_id);

		// 	} else {

		// 		$Order->where('channel_partner.reporting_company_id', Auth::user()->company_id);
		// 		$Order->where('channel_partner.reporting_manager_id', 0);

		// 	}

		// } else if (isChannelPartner(Auth::user()->type) != 0) {

		// 	$Order->where('channel_partner.reporting_manager_id', Auth::user()->id);

		// }
		$Order = $Order->first();

		if ($Order) {

			$salePersons = explode(",", $Order['sale_persons']);
			$salePersons = User::select('first_name', 'last_name')->whereIn('id', $salePersons)->get();

			$salePersonsD = array();

			foreach ($salePersons as $keyS => $valueS) {

				$salePersonsD[$keyS] = $valueS['first_name'] . " " . $valueS['last_name'];
			}

			$Order['sale_persons'] = implode(", ", $salePersonsD);

			$BCityList = CityList::find($Order['bill_city_id']);
			$Order['bill_city_name'] = "";
			if ($BCityList) {
				$Order['bill_city_name'] = $BCityList->name;
			}

			$BStateList = StateList::find($Order['bill_state_id']);
			$Order['bill_state_name'] = "";
			if ($BStateList) {
				$Order['bill_state_name'] = $BStateList->name;
			}

			$BCountryList = CountryList::find($Order['bill_country_id']);
			$Order['bill_country_name'] = "";
			if ($BCountryList) {
				$Order['bill_country_name'] = $BCountryList->name;
			}

			$DCityList = CityList::find($Order['d_city_id']);
			$Order['d_city_name'] = "";
			if ($DCityList) {
				$Order['d_city_name'] = $DCityList->name;
			}

			$DStateList = StateList::find($Order['d_state_id']);
			$Order['d_state_name'] = "";
			if ($DStateList) {
				$Order['d_state_name'] = $DStateList->name;
			}

			$DCountryList = CountryList::find($Order['d_country_id']);
			$Order['d_country_name'] = "";
			if ($DCountryList) {
				$Order['d_country_name'] = $DCountryList->name;
			}



			$Order['channel_partner_type_name'] = getUserTypeName($Order['channel_partner_type']);

			$Order['display_date_time'] = convertOrderDateTime($Order['created_at'], "date");

			if ($Order['is_self'] == 0) {
				$Order['payment_mode_lable'] = getPaymentModeName($Order['payment_mode']);
			} else {
				$SelfUser = User::find($Order['channel_partner_user_id']);
				$Order['payment_mode_lable'] = "";
				$Order['channel_partner_firm_name'] = $SelfUser['first_name'] . " " . $SelfUser['last_name'];
				$Order['channel_partner_first_name'] = $SelfUser['first_name'];
				$Order['channel_partner_last_name'] = $SelfUser['first_name'];
				$Order['channel_partner_email'] = $SelfUser['email'];
				$Order['channel_partner_phone_number'] = $SelfUser['phone_number'];
				$Order['channel_partner_dialing_code'] = $SelfUser['dialing_code'];
				$Order['channel_partner_type'] = 0;
				$Order['channel_partner_credit_limit'] = 0;
				$Order['channel_partner_credit_days'] = 0;
				$Order['channel_partner_pending_credit'] = 0;
				$Order['channel_partner_type_name'] = "SELF";
			}

			$OrderItem = MarketingOrderItem::query();
			// $OrderItem->select('marketing_order_items.id', 'marketing_order_items.qty', 'marketing_order_items.mrp', 'marketing_order_items.weight', 'marketing_order_items.total_mrp', 'marketing_order_items.pending_qty', 'marketing_order_items.marketing_product_inventory_id', 'marketing_order_items.discount_percentage', 'marketing_product_inventory.thumb as product_image', 'product_code.name as product_code_name', 'marketing_product_inventory.quantity as product_stock', 'marketing_order_items.gst_percentage', 'marketing_order_items.gst_tax', 'marketing_order_items.total_gst_tax');
			$OrderItem->select(
				'marketing_order_items.id',
				'marketing_order_items.qty',
				'marketing_order_items.mrp', 
				'marketing_order_items.width',
				'marketing_order_items.weight',
				'marketing_order_items.height',
				'marketing_order_items.gst_tax',
				'marketing_order_items.box_image',
				'marketing_order_items.total_mrp',
				'marketing_order_items.pending_qty',
				'marketing_order_items.sample_image',
				'marketing_order_items.total_gst_tax',
				'marketing_order_items.gst_percentage',
				'marketing_order_items.discount_percentage',
				'marketing_order_items.marketing_product_inventory_id',
				'marketing_product_inventory.thumb as product_image',
				'marketing_product_inventory.quantity as product_stock',
				'marketing_product_inventory.is_custome',
				'product_code.name as product_code_name'
			);

			$OrderItem->leftJoin('marketing_product_inventory', 'marketing_product_inventory.id', '=', 'marketing_order_items.marketing_product_inventory_id');
			//$OrderItem->leftJoin('data_master as product_group', 'product_group.id', '=', 'marketing_product_inventory.marketing_product_group_id');
			$OrderItem->leftJoin('data_master as product_code', 'product_code.id', '=', 'marketing_product_inventory.marketing_product_code_id');
			$OrderItem->where('marketing_order_id', $Order->id);
			$OrderItem->orderBy('id', 'desc');
			$OrderItem = $OrderItem->get();

			$Order['items'] = $OrderItem;

			$response = successRes("Order detail");
			$response['data'] = $Order;
		} else {
			$response = errorRes("Invalid order id");
		}
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function calculation(Request $request)
	{

		if ($request->expectsJson()) {
			$inputJSON = $request->all();
			$orderItems = array();

			foreach ($inputJSON['order_items'] as $key => $value) {

				$orderItems[$key]['id'] = $value['id'];
				//$orderItems[$key]['info']['product_group_name'] = $value['product_group_name'];
				$orderItems[$key]['info']['product_code_name'] = $value['product_code_name'];
				// $orderItems[$key]['info']['description'] = $value['description'];
				$orderItems[$key]['info']['product_image'] = $value['product_image'];
				$orderItems[$key]['info']['product_stock'] = $value['product_stock'];
				$orderItems[$key]['info']['pending_qty'] = $value['pending_qty'];
				$orderItems[$key]['info']['orignal_qty'] = $value['qty'];

				$orderItems[$key]['mrp'] = $value['mrp'];
				$orderItems[$key]['qty'] = $value['updated_qty'];
				$orderItems[$key]['discount_percentage'] = 0;
				$orderItems[$key]['weight'] = $value['weight'];
				$orderItems[$key]['gst_percentage'] = $value['gst_percentage'];

				if($value['is_custome'] == 1)
				{
					$orderItems[$key]['width'] = $value['width'];
					$orderItems[$key]['height'] = $value['height'];
					$orderItems[$key]['box_image'] = $value['box_image'];
					$orderItems[$key]['sample_image'] = $value['sample_image'];
					$orderItems[$key]['is_custom'] = 1;
				}
				else
				{
					$orderItems[$key]['width'] = 0;
					$orderItems[$key]['height'] = 0;
					$orderItems[$key]['box_image'] = "";
					$orderItems[$key]['sample_image'] = "";
					$orderItems[$key]['is_custom'] = 0;
				}
			}

			// $GSTPercentage = $inputJSON['gst_percentage'];
			// $shippingCost = $inputJSON['shipping_cost'];
			$orderDetail = calculationProcessOfMarketingRequest($orderItems);
			$response = successRes("Order detail");
			$response['order'] = $orderDetail;
		} else {

			$response = errorRes("Something went wrong");
		}

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function invoiceSave(Request $request)
	{
		// 'invoice_file' => ['required'],
		// 'invoice_number' => ['required'],

		$validator = Validator::make($request->all(), []);

		if ($validator->fails()) {

			$response = array();
			$response['status'] = 0;
			$response['msg'] = "The request could not be understood by the server due to malformed syntax";
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();

			return response()->json($response)->header('Content-Type', 'application/json');
		} else {

			$uploadedFile1 = "";

			// if ($request->hasFile('invoice_file')) {

			// 	$folderPathofFile = '/s/challan-file';
			// 	if (!is_dir(public_path($folderPathofFile))) {
			// 		mkdir(public_path($folderPathofFile));
			// 	}

			// 	$folderPathofFile = '/s/challan-file/' . date('Y');

			// 	if (!is_dir(public_path($folderPathofFile))) {

			// 		mkdir(public_path($folderPathofFile));
			// 	}

			// 	$folderPathofFile = '/s/challan-file/' . date('Y') . "/" . date('m');
			// 	if (!is_dir(public_path($folderPathofFile))) {
			// 		mkdir(public_path($folderPathofFile));
			// 	}

			// 	$fileObject1 = $request->file('invoice_file');
			// 	$extension = $fileObject1->getClientOriginalExtension();
			// 	$fileTypes = acceptFileTypes('marketing.challan', 'server');

			// 	if (in_array(strtolower($extension), $fileTypes)) {

			// 		$fileName1 = time() . mt_rand(10000, 99999) . '.' . $extension;

			// 		$destinationPath = public_path($folderPathofFile);

			// 		$fileObject1->move($destinationPath, $fileName1);

			// 		if (File::exists(public_path($folderPathofFile . "/" . $fileName1))) {

			// 			$uploadedFile1 = $folderPathofFile . "/" . $fileName1;
			// 			$spaceUploadResponse = uploadFileOnSpaces(public_path($uploadedFile1), $uploadedFile1);
			// 			if ($spaceUploadResponse != 1) {
			// 				$uploadedFile1 = "";
			// 			} else {
			// 				unlink(public_path($uploadedFile1));
			// 			}

			// 		}
			// 	}

			// if ($uploadedFile1 == "") {
			// 	$response = successRes("Invalid challan file");
			// 	return response()->json($response)->header('Content-Type', 'application/json');
			// }

			$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
			$isAccountUser = isAccountUser();

			$InvoiceItems = array();
			$keyCount = 0;
			$hasAnyPending = 0;

			foreach ($request->input_order_item_id as $key => $value) {

				if (isset($request->input_order_item_id[$key])) {

					$inputQty = (int) $request->input_qty[$key];
					$OrderItem = MarketingOrderItem::find($value);
					if ($OrderItem) {
						$pendingQty = floatval($OrderItem->pending_qty);

						if ($pendingQty >= $inputQty) {

							if ($inputQty > 0) {

								$productInventory = MarketingProductInventory::find($OrderItem->marketing_product_inventory_id);

								if (!$productInventory) {

									$response = errorRes("Invalid product");
									return response()->json($response)->header('Content-Type', 'application/json');
								}

								/*if ($productInventory->quantity < $inputQty) {

									$response = errorRes("Marketing Inventary #" . $productInventory->id . " (" . $productInventory->description . ") out of stock");
									return response()->json($response)->header('Content-Type', 'application/json');
								}*/ /* Meet Comment 12-05-2023*/

								$InvoiceItems[$keyCount]['id'] = $request->input_order_item_id[$key];
								$InvoiceItems[$keyCount]['qty'] = $inputQty;
								$InvoiceItems[$keyCount]['mrp'] = $OrderItem->mrp;
								$InvoiceItems[$keyCount]['discount_percentage'] = $OrderItem->discount_percentage;
								$InvoiceItems[$keyCount]['weight'] = $OrderItem->weight;
								$InvoiceItems[$keyCount]['gst_percentage'] = $OrderItem->gst_percentage;
								$InvoiceItems[$keyCount]['info']['pending_qty'] = $pendingQty - $inputQty;
								$InvoiceItems[$keyCount]['width'] = $OrderItem->width;
								$InvoiceItems[$keyCount]['height'] = $OrderItem->height;
								$InvoiceItems[$keyCount]['box_image'] = $OrderItem->box_image;
								$InvoiceItems[$keyCount]['sample_image'] = $OrderItem->sample_image;
								$hasAnyPending = $hasAnyPending + $pendingQty - $inputQty;

								$keyCount++;
							}
						} else {

							$response = errorRes("Invalid challan QTY");
							return response()->json($response)->header('Content-Type', 'application/json');
						}
					} else {

						$response = errorRes("Invalid order item");
						return response()->json($response)->header('Content-Type', 'application/json');
					}
				} else {

					$response = errorRes("Invalid QTY");
					return response()->json($response)->header('Content-Type', 'application/json');
				}
			}

			if (count($InvoiceItems) == 0) {

				$response = errorRes("Not Updated QTY");
				return response()->json($response)->header('Content-Type', 'application/json');
			}

			$Order = MarketingOrder::query();
			$Order->select('marketing_orders.*', 'channel_partner.reporting_company_id');
			$Order->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'marketing_orders.channel_partner_user_id');

			// if ($isAccountUser == 1 || $isAdminOrCompanyAdmin == 1) {
			// 	if (Auth::user()->parent_id != 0) {

			// 		$parent = ChannelPartner::where('user_id', Auth::user()->parent_id)->first();

			// 		$Order->where('channel_partner.reporting_manager_id', $parent->user_id);

			// 	} else {

			// 		$Order->where('channel_partner.reporting_company_id', Auth::user()->company_id);
			// 		$Order->where('channel_partner.reporting_manager_id', 0);

			// 	}

			// } else if (isChannelPartner(Auth::user()->type) != 0) {

			// 	$Order->where('channel_partner.reporting_manager_id', Auth::user()->id);

			// }

			$Order = $Order->where('marketing_orders.id', $request->invoice_order_id)->first();

			if ($Order && $Order->status != 3) {

				$invoiceDetail = calculationProcessOfMarketingRequest($InvoiceItems);

				if ($request->verify_payable_total != $invoiceDetail['total_payable']) {
					$response = errorRes("Something went wrong with price calculation");
					$response['verify_payable_total'] = $request->verify_payable_total;
					$response['order'] = $invoiceDetail;
					return response()->json($response)->header('Content-Type', 'application/json');
				}

				$Invoice = new MarketingChallan();
				$Invoice->user_id = Auth::user()->id;
				$Invoice->order_id = $Order->id;
				//$Invoice->invoice_date = $request->invoice_date;
				$Invoice->invoice_number = "";
				$Invoice->invoice_file = $uploadedFile1;
				$Invoice->total_mrp = $invoiceDetail['total_mrp'];
				$Invoice->total_discount = $invoiceDetail['total_discount'];
				// $Invoice->gst_percentage = $invoiceDetail['gst_percentage'];
				$Invoice->total_weight = $invoiceDetail['total_weight'];
				$Invoice->delievery_charge = $invoiceDetail['delievery_charge'];
				$Invoice->total_mrp_minus_disocunt = $invoiceDetail['total_mrp_minus_disocunt'];
				$Invoice->gst_tax = $invoiceDetail['gst_tax'];
				$Invoice->shipping_cost = $invoiceDetail['shipping_cost'];
				$Invoice->total_payable = $invoiceDetail['total_payable'];
				$Invoice->save();

				foreach ($invoiceDetail['items'] as $key => $value) {

					$InvoiceItem = new MarketingChallanItem();
					$InvoiceItem->user_id = Auth::user()->id;
					$InvoiceItem->order_item_id = $value['id'];
					$InvoiceItem->order_id = $Order->id;
					$InvoiceItem->orders_challan_id = $Invoice->id;
					$InvoiceItem->qty = $value['qty'];
					//$InvoiceItem->pending_packed_qty = $value['qty'];
					$InvoiceItem->mrp = $value['mrp'];
					$InvoiceItem->gst_percentage = $value['gst_percentage'];
					$InvoiceItem->gst_tax = $value['gst_tax'];
					$InvoiceItem->total_gst_tax = $value['total_gst_tax'];
					$InvoiceItem->total_mrp = $value['total_mrp'];
					$InvoiceItem->discount_percentage = $value['discount_percentage'];
					$InvoiceItem->discount = $value['discount'];
					$InvoiceItem->total_discount = $value['discount'];
					$InvoiceItem->mrp_minus_disocunt = $value['mrp_minus_disocunt'];
					$InvoiceItem->weight = $value['weight'];
					$InvoiceItem->total_weight = $value['total_weight'];
					$InvoiceItem->save();

					$OrderItem = MarketingOrderItem::find($value['id']);
					$OrderItem->pending_qty = $value['info']['pending_qty'];
					$OrderItem->save();

					$productInventory = MarketingProductInventory::find($OrderItem->marketing_product_inventory_id);
					if ($productInventory) {
						$productInventory->quantity = $productInventory->quantity - $InvoiceItem->qty;
						$productInventory->save();
					}
				}

				if ($hasAnyPending == 0) {

					$Order->status = 1;
					$Order->challan = $Invoice->id;
					// $Order->sub_status = 0;
					$Order->save();
				} else {
					$Order->challan = $Invoice->id;

					$Order->status = 2;
					// $Order->sub_status = 0;
					$Order->save();
				}

				$response = successRes("Successfully generated challan");
				// } else {

				// 	$response = successRes("Invalid challan file");

				// }
			}
		}
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function invoiceSave2(Request $request)
	{

		$validator = Validator::make($request->all(), [
			'invoice_file' => ['required'],
			'invoice_number' => ['required'],

		]);

		if ($validator->fails()) {

			$response = array();
			$response['status'] = 0;
			$response['msg'] = "The request could not be understood by the server due to malformed syntax";
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();

			return response()->json($response)->header('Content-Type', 'application/json');
		} else {

			$uploadedFile1 = "";

			if ($request->hasFile('invoice_file')) {

				$folderPathofFile = '/s/challan-file';
				if (!is_dir(public_path($folderPathofFile))) {
					mkdir(public_path($folderPathofFile));
				}

				$folderPathofFile = '/s/challan-file/' . date('Y');

				if (!is_dir(public_path($folderPathofFile))) {

					mkdir(public_path($folderPathofFile));
				}

				$folderPathofFile = '/s/challan-file/' . date('Y') . "/" . date('m');
				if (!is_dir(public_path($folderPathofFile))) {
					mkdir(public_path($folderPathofFile));
				}

				$fileObject1 = $request->file('invoice_file');
				$extension = $fileObject1->getClientOriginalExtension();
				$fileTypes = acceptFileTypes('marketing.challan', 'server');

				if (in_array(strtolower($extension), $fileTypes)) {

					$fileName1 = time() . mt_rand(10000, 99999) . '.' . $extension;

					$destinationPath = public_path($folderPathofFile);

					$fileObject1->move($destinationPath, $fileName1);

					if (File::exists(public_path($folderPathofFile . "/" . $fileName1))) {

						$uploadedFile1 = $folderPathofFile . "/" . $fileName1;
						$spaceUploadResponse = uploadFileOnSpaces(public_path($uploadedFile1), $uploadedFile1);
						if ($spaceUploadResponse != 1) {
							$uploadedFile1 = "";
						} else {
							unlink(public_path($uploadedFile1));
						}
					}
				}

				if ($uploadedFile1 == "") {
					$response = successRes("Invalid challan file");
					return response()->json($response)->header('Content-Type', 'application/json');
				}

				$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
				$isAccountUser = isAccountUser();

				$InvoiceItems = array();
				$keyCount = 0;
				$hasAnyPending = 0;

				$Invoice = MarketingChallan::find($request->invoice_order_id);
				//$Invoice->invoice_date = $request->invoice_date;
				$Invoice->invoice_number = $request->invoice_number;
				$Invoice->invoice_file = $uploadedFile1;

				$Invoice->save();

				$response = successRes("Successfully generated challan");
			} else {

				$response = successRes("Invalid challan file");
			}
		}
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function invoiceList(Request $request)
	{

		$Order = MarketingOrder::select('id')->find($request->order_id);
		if ($Order) {
			$data['title'] = "Invoice";
			$data['order_id'] = $Order->id;
			return view('orders/sales_invoice_list', compact('data'));
		} else {
			return redirect()->route('dashboard');
		}
	}

	public function invoiceListAjax(Request $request)
	{

		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		$isAccountUser = isAccountUser();

		$searchColumns = array(
			0 => 'invoice.id',
			1 => 'invoice.invoice_number',
			2 => 'invoice.invoice_date',

		);

		$sortingColumns = array(
			0 => 'invoice.id',
			1 => 'marketing_orders.user_id',
			2 => 'marketing_orders.channel_partner_user_id',
			3 => 'marketing_orders.sale_persons',
			4 => 'marketing_orders.payment_mode',
			5 => 'invoice.status',

		);

		$selectColumns = array(
			0 => 'invoice.id',
			1 => 'marketing_orders.user_id',
			2 => 'marketing_orders.channel_partner_user_id',
			3 => 'marketing_orders.sale_persons',
			4 => 'marketing_orders.payment_mode',
			5 => 'invoice.status',
			6 => 'invoice.created_at',
			7 => 'users.first_name as first_name',
			8 => 'users.last_name as last_name',
			9 => 'channel_partner.firm_name',
			10 => 'marketing_orders.payment_mode',
			11 => 'invoice.gst_tax',
			12 => 'invoice.total_payable',
			13 => 'invoice.invoice_file',
			14 => 'invoice.invoice_number',
			15 => 'invoice.total_mrp_minus_disocunt',

		);

		$query = MarketingChallan::query();
		$query->leftJoin('orders', 'invoice.order_id', '=', 'marketing_orders.id');
		$query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'marketing_orders.channel_partner_user_id');

		if ($isAccountUser == 1 || $isAdminOrCompanyAdmin == 1) {
			if (Auth::user()->parent_id != 0) {

				$parent = ChannelPartner::where('user_id', Auth::user()->parent_id)->first();

				$query->where('channel_partner.reporting_manager_id', $parent->user_id);
			} else {

				$query->where('channel_partner.reporting_company_id', Auth::user()->company_id);
				$query->where('channel_partner.reporting_manager_id', 0);
			}
		} else if (isChannelPartner(Auth::user()->type) != 0) {

			$query->where('channel_partner.reporting_manager_id', Auth::user()->id);
		}

		$query->where('invoice.order_id', $request->order_id);

		$recordsTotal = $query->count();
		$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.
		$query = MarketingChallan::query();
		$query->leftJoin('orders', 'invoice.order_id', '=', 'marketing_orders.id');
		$query->leftJoin('users', 'users.id', '=', 'marketing_orders.user_id');
		$query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'marketing_orders.channel_partner_user_id');
		$query->where('invoice.order_id', $request->order_id);

		if ($isAccountUser == 1 || $isAdminOrCompanyAdmin == 1) {
			if (Auth::user()->parent_id != 0) {

				$parent = ChannelPartner::where('user_id', Auth::user()->parent_id)->first();

				$query->where('channel_partner.reporting_manager_id', $parent->user_id);
			} else {

				$query->where('channel_partner.reporting_company_id', Auth::user()->company_id);
				$query->where('channel_partner.reporting_manager_id', 0);
			}
		} else if (isChannelPartner(Auth::user()->type) != 0) {

			$query->where('channel_partner.reporting_manager_id', Auth::user()->id);
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
		if ($isFilterApply == 1) {
			$recordsFiltered = count($data);
		}

		foreach ($data as $key => $value) {

			$data[$key]['detail'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">#' . $value['id'] . '</a></h5>
			    <p class="text-muted mb-0" data-bs-toggle="tooltip" title="INVOICE NO">' . ($value['invoice_number']) . '</p>
                <p class="text-muted mb-0" data-bs-toggle="tooltip"  data-bs-original-title="' . convertOrderDateTime($value['created_at'], "time") . '" >' . convertOrderDateTime($value['created_at'], "date") . '</p>';

			$paymentMode = "";

			//$paymentMode = '<span class="badge rounded-pill badge-soft-dark font-size-11">' . getPaymentModeName($value['payment_mode']) . '</span>';

			$data[$key]['order_by'] = '<p class="text-muted mb-0">' . $value['first_name'] . '  ' . $value['last_name'] . '</p>';
			$data[$key]['channel_partner'] = '<p class="text-muted mb-0">' . $value['firm_name'] . '</p><p class="text-muted mb-0">' . $paymentMode . '</p>';

			$sale_persons = explode(",", $value['sale_persons']);

			$Users = User::select('first_name', 'last_name')->whereIn('id', $sale_persons)->get();

			$uiSalePerson = '<ul class="list-inline font-size-20 contact-links mb-0">';
			foreach ($Users as $kU => $vU) {
				$uiSalePerson .= '<li class="list-inline-item px-2">';
				$uiSalePerson .= '<a  data-bs-toggle="tooltip" title="' . $vU['first_name'] . ' ' . $vU['last_name'] . '" href="javascript: void(0);" ><i class="bx bx-user"></i></a>';
				$uiSalePerson .= '</li>';
			}

			$uiSalePerson .= '</ul>';

			$data[$key]['sale_persons'] = $uiSalePerson;

			$data[$key]['payment_detail'] = '<p class="text-muted mb-0">EXGST&nbsp&nbsp;&nbsp;&nbsp;: <i class="fas fa-rupee-sign"></i> <span class="price-lable-font">' . priceLable($value['total_mrp_minus_disocunt']) . '</span></p>

			<p class="text-muted mb-0 ">TOTAL&nbsp;&nbsp;&nbsp;&nbsp: <i class="fas fa-rupee-sign"></i> <span class="price-lable-font">' . priceLable($value['total_payable']) . '</span></p>


			   ';

			$data[$key]['status'] = getInvoiceLable($value['status']);

			$uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';

			$uiAction .= '<li class="list-inline-item px-2">';
			$uiAction .= '<a onclick="ViewInvoice(\'' . $value['id'] . '\')" href="javascript: void(0);" title="View"><i class="mdi mdi-eye"></i></a>';
			$uiAction .= '</li>';

			$uiAction .= '<li class="list-inline-item px-2">';
			$uiAction .= '<a onclick="EditInvoice(\'' . $value['id'] . '\')" href="javascript: void(0);" title="View"><i class="mdi mdi-square-edit-outline"></i></a>';
			$uiAction .= '</li>';

			$uiAction .= '<li class="list-inline-item px-2">';

			$uiAction .= '<a target="_blank" href="' . getSpaceFilePath($value['invoice_file']) . '" title="PDF"><i class="bx bxs-file-pdf"></i></a>';
			$uiAction .= '</li>';

			$uiAction .= '</ul>';
			$data[$key]['action'] = $uiAction;
		}

		$jsonData = array(
			"draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
			"recordsTotal" => intval($recordsTotal), // total number of records
			"recordsFiltered" => intval($recordsFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
			"data" => $data, // total data array

		);
		return $jsonData;
	}

	public function invoiceDetail(Request $request)
	{

		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		$isAccountUser = isAccountUser();

		$Order = MarketingChallan::query();
		$Order->select('invoice.id', 'invoice.dispatch_detail', 'invoice.eway_bill', 'invoice.created_at', 'marketing_orders.gst_number', 'marketing_orders.payment_mode', 'marketing_orders.sale_persons', 'invoice.total_mrp_minus_disocunt', 'invoice.total_mrp', 'invoice.gst_tax', 'invoice.gst_percentage', 'invoice.delievery_charge', 'invoice.total_payable', 'marketing_orders.bill_address_line1', 'marketing_orders.bill_address_line2', 'marketing_orders.bill_pincode', 'marketing_orders.bill_state_id', 'marketing_orders.bill_city_id', 'marketing_orders.bill_country_id', 'marketing_orders.d_address_line1', 'marketing_orders.d_address_line2', 'marketing_orders.d_pincode', 'marketing_orders.d_state_id', 'marketing_orders.d_city_id', 'marketing_orders.d_country_id', 'channel_partner_detail.email as channel_partner_email', 'channel_partner_detail.phone_number as channel_partner_phone_number', 'channel_partner_detail.dialing_code as channel_partner_dialing_code', 'channel_partner_detail.first_name as channel_partner_first_name', 'channel_partner_detail.last_name as channel_partner_last_name', 'channel_partner.firm_name as channel_partner_firm_name', 'channel_partner.type as channel_partner_type', 'channel_partner.credit_limit as channel_partner_credit_limit', 'channel_partner.credit_days as channel_partner_credit_days', 'channel_partner.pending_credit as channel_partner_pending_credit', 'channel_partner.credit_days as channel_partner_credit_days');
		$Order->leftJoin('marketing_orders', 'invoice.order_id', '=', 'marketing_orders.id');
		$Order->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'marketing_orders.channel_partner_user_id');
		$Order->leftJoin('users as channel_partner_detail', 'channel_partner_detail.id', '=', 'channel_partner.user_id');
		$Order->where('invoice.id', $request->invoice_id);

		if ($isAccountUser == 1 || $isAdminOrCompanyAdmin == 1) {
			if (Auth::user()->parent_id != 0) {

				$parent = ChannelPartner::where('user_id', Auth::user()->parent_id)->first();

				$Order->where('channel_partner.reporting_manager_id', $parent->user_id);
			} else {

				$Order->where('channel_partner.reporting_company_id', Auth::user()->company_id);
				$Order->where('channel_partner.reporting_manager_id', 0);
			}
		} else if (isChannelPartner(Auth::user()->type) != 0) {

			$Order->where('channel_partner.reporting_manager_id', Auth::user()->id);
		}

		$Order = $Order->first();

		if ($Order) {

			$Order->dispatch_detail = explode(",", $Order->dispatch_detail);
			$salePersons = explode(",", $Order['sale_persons']);
			$salePersons = User::select('first_name', 'last_name')->whereIn('id', $salePersons)->get();
			$salePersonsD = array();

			foreach ($salePersons as $keyS => $valueS) {

				$salePersonsD[$keyS] = $valueS['first_name'] . " " . $valueS['last_name'];
			}

			$Order['sale_persons'] = implode(", ", $salePersonsD);

			$BCityList = CityList::find($Order['bill_city_id']);
			$Order['bill_city_name'] = "";
			if ($BCityList) {
				$Order['bill_city_name'] = $BCityList->name;
			}

			$BStateList = StateList::find($Order['bill_state_id']);
			$Order['bill_state_name'] = "";
			if ($BStateList) {
				$Order['bill_state_name'] = $BStateList->name;
			}

			$BCountryList = CountryList::find($Order['bill_country_id']);
			$Order['bill_country_name'] = "";
			if ($BCountryList) {
				$Order['bill_country_name'] = $BCountryList->name;
			}

			$DCityList = CityList::find($Order['d_city_id']);
			$Order['d_city_name'] = "";
			if ($DCityList) {
				$Order['d_city_name'] = $DCityList->name;
			}

			$DStateList = StateList::find($Order['d_state_id']);
			$Order['d_state_name'] = "";
			if ($DStateList) {
				$Order['d_state_name'] = $DStateList->name;
			}

			$DCountryList = CountryList::find($Order['d_country_id']);
			$Order['d_country_name'] = "";
			if ($DCountryList) {
				$Order['d_country_name'] = $DCountryList->name;
			}

			$Order['payment_mode_lable'] = getPaymentModeName($Order['payment_mode']);
			$Order['channel_partner_type_name'] = getUserTypeName($Order['channel_partner_type']);
			$Order['display_date_time'] = convertOrderDateTime($Order->created_at, "date");

			$OrderItem = InvoiceItem::query();
			$OrderItem->select('marketing_order_items.id', 'invoice_items.qty', 'invoice_items.total_mrp', 'marketing_product_inventory.image as product_image', 'product_brand.name as product_brand_name', 'product_code.name as product_code_name');
			$OrderItem->leftJoin('order_items', 'invoice_items.order_item_id', '=', 'marketing_order_items.id');
			$OrderItem->leftJoin('product_inventory', 'marketing_product_inventory.id', '=', 'marketing_order_items.marketing_product_inventory_id');
			$OrderItem->leftJoin('data_master as product_brand', 'product_brand.id', '=', 'marketing_product_inventory.product_brand_id');
			$OrderItem->leftJoin('data_master as product_code', 'product_code.id', '=', 'marketing_product_inventory.product_code_id');
			$OrderItem->where('invoice_id', $Order->id);
			$OrderItem->orderBy('id', 'desc');
			$OrderItem = $OrderItem->get();
			$Order['items'] = $OrderItem;
			$response = successRes("Invoice detail");
			$response['data'] = $Order;
		} else {
			$response = errorRes("Invalid order id");
		}
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	function updateFile(Request $request)
	{

		$validator = Validator::make($request->all(), [
			'invoice_file' => ['required'],
			'invoice_id' => ['required'],

		]);

		if ($validator->fails()) {

			$response = array();
			$response['status'] = 0;
			$response['msg'] = "The request could not be understood by the server due to malformed syntax";
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();
			return response()->json($response)->header('Content-Type', 'application/json');
		} else {

			$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
			$isAccountUser = isAccountUser();
			$uploadedFile1 = "";

			if ($request->hasFile('invoice_file')) {

				$folderPathofFile = '/s/invoice';
				if (!is_dir(public_path($folderPathofFile))) {
					mkdir(public_path($folderPathofFile));
				}

				$folderPathofFile = '/s/invoice/' . date('Y');

				if (!is_dir(public_path($folderPathofFile))) {

					mkdir(public_path($folderPathofFile));
				}

				$folderPathofFile = '/s/invoice/' . date('Y') . "/" . date('m');
				if (!is_dir(public_path($folderPathofFile))) {
					mkdir(public_path($folderPathofFile));
				}

				$fileObject1 = $request->file('invoice_file');
				$extension = $fileObject1->getClientOriginalExtension();
				$fileTypes = acceptFileTypes('order.invoice', 'server');
				if (in_array(strtolower($extension), $fileTypes)) {

					$fileName1 = time() . mt_rand(10000, 99999) . '.' . $extension;

					$destinationPath = public_path($folderPathofFile);

					$fileObject1->move($destinationPath, $fileName1);

					if (File::exists(public_path($folderPathofFile . "/" . $fileName1))) {

						$uploadedFile1 = $folderPathofFile . "/" . $fileName1;

						$spaceUploadResponse = uploadFileOnSpaces(public_path($uploadedFile1), $uploadedFile1);

						if ($spaceUploadResponse != 1) {
							$uploadedFile1 = "";
						} else {
							unlink(public_path($uploadedFile1));
						}
					}
				}
			}

			if ($uploadedFile1 != "") {

				$Invoice = MarketingChallan::query();
				$Invoice->select('invoice.*');
				$Invoice->leftJoin('orders', 'invoice.order_id', '=', 'marketing_orders.id');
				$Invoice->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'marketing_orders.channel_partner_user_id');

				if ($isAccountUser == 1 || $isAdminOrCompanyAdmin == 1) {
					if (Auth::user()->parent_id != 0) {

						$parent = ChannelPartner::where('user_id', Auth::user()->parent_id)->first();

						$Invoice->where('channel_partner.reporting_manager_id', $parent->user_id);
					} else {

						$Invoice->where('channel_partner.reporting_company_id', Auth::user()->company_id);
						$Invoice->where('channel_partner.reporting_manager_id', 0);
					}
				} else if (isChannelPartner(Auth::user()->type) != 0) {

					$Invoice->where('channel_partner.reporting_manager_id', Auth::user()->id);
				}

				$Invoice = $Invoice->find($request->invoice_id);

				if ($Invoice) {

					$Invoice->invoice_file = $uploadedFile1;
					$Invoice->save();

					$response = successRes("Successfully updated invoice");
				} else {
					$response = errorRes("Invalid access of user");
				}
			} else {

				$response = errorRes("Invalid pdf");
			}
		}
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function detail2(Request $request)
	{

		$isSalePerson = isSalePerson();
		if ($isSalePerson == 1) {
			$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
		}
		$isMarketingUser = isMarketingUser();
		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();

		$Order = MarketingChallan::query();
		$Order->select('marketing_orders_challan.id', 'marketing_orders.channel_partner_user_id', 'marketing_orders.is_self', 'marketing_orders_challan.created_at', 'marketing_orders.gst_number', 'marketing_orders.payment_mode', 'marketing_orders_challan.invoice_number', 'marketing_orders.sale_persons', 'marketing_orders_challan.total_mrp_minus_disocunt', 'marketing_orders_challan.total_mrp', 'marketing_orders_challan.gst_tax', 'marketing_orders_challan.delievery_charge', 'marketing_orders_challan.total_payable', 'marketing_orders.bill_address_line1', 'marketing_orders.bill_address_line2', 'marketing_orders.bill_pincode', 'marketing_orders.bill_state_id', 'marketing_orders.bill_city_id', 'marketing_orders.bill_country_id', 'marketing_orders.d_address_line1', 'marketing_orders.d_address_line2', 'marketing_orders.d_pincode', 'marketing_orders.d_state_id', 'marketing_orders.d_city_id', 'marketing_orders.d_country_id', 'channel_partner_detail.email as channel_partner_email', 'channel_partner_detail.phone_number as channel_partner_phone_number', 'channel_partner_detail.dialing_code as channel_partner_dialing_code', 'channel_partner_detail.first_name as channel_partner_first_name', 'channel_partner_detail.last_name as channel_partner_last_name', 'channel_partner.firm_name as channel_partner_firm_name', 'channel_partner.type as channel_partner_type', 'channel_partner.credit_limit as channel_partner_credit_limit', 'channel_partner.credit_days as channel_partner_credit_days', 'channel_partner.pending_credit as channel_partner_pending_credit', 'channel_partner.credit_days as channel_partner_credit_days', 'marketing_orders.remark');
		$Order->leftJoin('marketing_orders', 'marketing_orders_challan.order_id', '=', 'marketing_orders.id');
		$Order->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'marketing_orders.channel_partner_user_id');
		$Order->leftJoin('users as channel_partner_detail', 'channel_partner_detail.id', '=', 'channel_partner.user_id');
		$Order->where('marketing_orders_challan.id', $request->invoice_id);

		if ($isAdminOrCompanyAdmin == 1) {
		} else if ($isSalePerson == 1) {

			$Order->where(function ($query) use ($childSalePersonsIds) {

				foreach ($childSalePersonsIds as $key => $value) {
					if ($key == 0) {
						$query->whereRaw('FIND_IN_SET("' . $value . '",marketing_orders.sale_persons)>0');
					} else {
						$query->orWhereRaw('FIND_IN_SET("' . $value . '",marketing_orders.sale_persons)>0');
					}
				}
			});

			$Order->where('channel_partner.reporting_company_id', Auth::user()->company_id);
		} else if ($isMarketingUser == 1) {
		}

		$Order = $Order->first();

		if ($Order) {

			$salePersons = explode(",", $Order['sale_persons']);
			$salePersons = User::select('first_name', 'last_name')->whereIn('id', $salePersons)->get();

			$salePersonsD = array();

			foreach ($salePersons as $keyS => $valueS) {

				$salePersonsD[$keyS] = $valueS['first_name'] . " " . $valueS['last_name'];
			}

			$Order['sale_persons'] = implode(", ", $salePersonsD);

			$BCityList = CityList::find($Order['bill_city_id']);
			$Order['bill_city_name'] = "";
			if ($BCityList) {
				$Order['bill_city_name'] = $BCityList->name;
			}

			$BStateList = StateList::find($Order['bill_state_id']);
			$Order['bill_state_name'] = "";
			if ($BStateList) {
				$Order['bill_state_name'] = $BStateList->name;
			}

			$BCountryList = CountryList::find($Order['bill_country_id']);
			$Order['bill_country_name'] = "";
			if ($BCountryList) {
				$Order['bill_country_name'] = $BCountryList->name;
			}

			$DCityList = CityList::find($Order['d_city_id']);
			$Order['d_city_name'] = "";
			if ($DCityList) {
				$Order['d_city_name'] = $DCityList->name;
			}

			$DStateList = StateList::find($Order['d_state_id']);
			$Order['d_state_name'] = "";
			if ($DStateList) {
				$Order['d_state_name'] = $DStateList->name;
			}

			$DCountryList = CountryList::find($Order['d_country_id']);
			$Order['d_country_name'] = "";
			if ($DCountryList) {
				$Order['d_country_name'] = $DCountryList->name;
			}

			$Order['payment_mode_lable'] = getPaymentModeName($Order['payment_mode']);
			$Order['channel_partner_type_name'] = getUserTypeName($Order['channel_partner_type']);
			$Order['display_date_time'] = convertOrderDateTime($Order->created_at, "date");

			if ($Order['is_self'] == 0) {
				$Order['payment_mode_lable'] = getPaymentModeName($Order['payment_mode']);
			} else {

				$SelfUser = User::find($Order['channel_partner_user_id']);
				$Order['payment_mode_lable'] = "";
				$Order['channel_partner_firm_name'] = $SelfUser['first_name'] . " " . $SelfUser['last_name'];
				$Order['channel_partner_first_name'] = $SelfUser['first_name'];
				$Order['channel_partner_last_name'] = $SelfUser['first_name'];
				$Order['channel_partner_email'] = $SelfUser['email'];
				$Order['channel_partner_phone_number'] = $SelfUser['phone_number'];
				$Order['channel_partner_dialing_code'] = $SelfUser['dialing_code'];
				$Order['channel_partner_type'] = 0;
				$Order['channel_partner_credit_limit'] = 0;
				$Order['channel_partner_credit_days'] = 0;
				$Order['channel_partner_pending_credit'] = 0;
				$Order['channel_partner_type_name'] = "SELF";
			}

			$OrderItem = array();

			$OrderItem = MarketingChallanItem::query();
			// $OrderItem->select('marketing_orders_challan_items.id', 'marketing_orders_challan_items.qty', 'marketing_orders_challan_items.total_mrp', 'marketing_product_inventory.image as product_image', 'product_code.name as product_code_name');
			$OrderItem->select('marketing_orders_challan_items.id', 'marketing_orders_challan_items.qty','marketing_order_items.box_image','marketing_order_items.sample_image', 'marketing_orders_challan_items.total_mrp', 'marketing_product_inventory.image as product_image', 'product_code.name as product_code_name', 'marketing_product_inventory.is_custome');
			$OrderItem->leftJoin('marketing_order_items', 'marketing_orders_challan_items.order_item_id', '=', 'marketing_order_items.id');
			$OrderItem->leftJoin('marketing_product_inventory', 'marketing_product_inventory.id', '=', 'marketing_order_items.marketing_product_inventory_id');
			//$OrderItem->leftJoin('data_master as product_group', 'product_group.id', '=', 'marketing_product_inventory.marketing_product_group_id');
			$OrderItem->leftJoin('data_master as product_code', 'product_code.id', '=', 'marketing_product_inventory.marketing_product_code_id');
			$OrderItem->where('orders_challan_id', $Order->id);
			$OrderItem->orderBy('marketing_orders_challan_items.id', 'desc');
			$OrderItem = $OrderItem->get();
			$Order['items'] = $OrderItem;

			$response = successRes("Order detail");
			$response['data'] = $Order;
		} else {
			$response = errorRes("Invalid challan id");
		}

		$response = json_decode(json_encode($response), true);

		// echo '<pre>';
		// print_r($response);
		// die;
		return response()->json($response)->header('Content-Type', 'application/json');
	}
}
