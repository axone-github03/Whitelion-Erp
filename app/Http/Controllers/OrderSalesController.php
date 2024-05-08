<?php

namespace App\Http\Controllers;

use App\Models\ChannelPartner;
use App\Models\CityList;
use App\Models\CountryList;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\StateList;
use App\Models\User;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

//use Session;

class OrderSalesController extends Controller
{
	public function __construct()
	{

		$this->middleware(function ($request, $next) {

			$tabCanAccessBy = array(0, 1, 3, 101, 102, 103, 104, 105, 13);

			if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
				return redirect()->route('dashboard');
			}

			return $next($request);
		});
	}
	public function index()
	{
		$data = array();
		$data['title'] = "Sales Orders";
		return view('orders/sales', compact('data'));
	}
	function ajax(Request $request)
	{

		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		$isCreUser = isCreUser();
		$isAccountUser = isAccountUser();

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
			14 => 'orders.invoice',
			15 => 'orders.sub_status',
			16 => 'channel_partner.type as channel_partner_type',
			17 => 'channel_partner_user.first_name as channel_partner_user_first_name',
			18 => 'channel_partner_user.last_name as channel_partner_user_last_name',
			19 => 'channel_partner_user.phone_number as channel_partner_user_phone_number',

		);

		$query = Order::query();
		$query->select('orders.id');
		$query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
		$query->whereIn('orders.status', array(0, 1, 2));
		$query->where('orders.is_cancelled', 0);

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

		$recordsTotal = $query->count();

		$query = Order::query();
		$query->leftJoin('users', 'users.id', '=', 'orders.user_id');
		$query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
		$query->leftJoin('users as channel_partner_user', 'channel_partner_user.id', '=', 'orders.channel_partner_user_id');

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

		$query->whereIn('orders.status', array(0, 1, 2));
		$query->where('orders.is_cancelled', 0);
		$query->select('orders.id');
		// $query->limit($request->length);
		// $query->offset($request->start);
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

		$recordsFiltered = $query->count();

		//$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.
		$query = Order::query();
		$query->leftJoin('users', 'users.id', '=', 'orders.user_id');
		$query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
		$query->leftJoin('users as channel_partner_user', 'channel_partner_user.id', '=', 'orders.channel_partner_user_id');

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

		$query->whereIn('orders.status', array(0, 1, 2));
		$query->where('orders.is_cancelled', 0);
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
		// if ($isFilterApply == 1) {
		// 	$recordsFiltered = count($data);
		// }
		$channelPartner = getChannelPartners();

		foreach ($data as $key => $value) {

			$data[$key]['detail'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">#' . highlightString($value['id'],$search_value) . '</a></h5>
                <p class="text-muted mb-0" data-bs-toggle="tooltip"  data-bs-original-title="' . convertOrderDateTime($value['created_at'], "time") . '" >' . highlightString(convertOrderDateTime($value['created_at'], "date"),$search_value) . '</p>';

			$paymentMode = "";

			$paymentMode = getPaymentLable($value['payment_mode']);

			$channelPartnerType = '<span class="badge rounded-pill badge-soft-dark font-size-11">' . $channelPartner[$value['channel_partner_type']]['short_name'] . '</span>';

			$data[$key]['order_by'] = '<p class="text-muted mb-0" data-bs-toggle="tooltip" title="' . $value['first_name'] . '  ' . $value['last_name'] . '">' . highlightString(displayStringLenth($value['first_name'] . '  ' . $value['last_name'], 10),$search_value) . '</p>';

			$data[$key]['channel_partner'] = '<p class="text-muted mb-0 text-center"data-bs-toggle="tooltip" title="' . $value['channel_partner_user_first_name'] . ' ' . $value['channel_partner_user_last_name'] . '&#013;&#013; PHONE:' . $value['channel_partner_user_phone_number'] . '"  >' . highlightString(displayStringLenth($value['firm_name'], 15),$search_value) . '</p><p class="text-muted mb-0 text-center">' . $channelPartnerType . " " . $paymentMode . '</p>';

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

			$data[$key]['payment_detail'] = '<p class="text-muted mb-0">EXGST&nbsp&nbsp;&nbsp;&nbsp;: <i class="fas fa-rupee-sign"></i> <span class="price-lable-font">' . highlightString(priceLable($value['total_mrp_minus_disocunt']),$search_value) . '</span></p>

			<p class="text-muted mb-0 ">TOTAL&nbsp;&nbsp;&nbsp;&nbsp: <i class="fas fa-rupee-sign"></i> <span class="price-lable-font">' . highlightString(priceLable($value['total_payable']),$search_value) . '</span></p>

			';

			$data[$key]['sub_status'] = "";

			if ($value['status'] == 1 || $value['status'] == 2) {
				$data[$key]['sub_status'] = getInvoiceLable($value['sub_status']);
			}

			$data[$key]['status'] = getOrderLable($value['status']);
			if ($data[$key]['sub_status'] != "") {
				$data[$key]['status'] = $data[$key]['status'] . "-" . $data[$key]['sub_status'];
			}

			$uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';

			if ($isAdminOrCompanyAdmin || $isCreUser) {
					$uiAction .= '<li class="list-inline-item px-2">';
					$uiAction .= '<a onclick="CancelOrder(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Cancel"><i class="mdi mdi-close-circle-outline"></i></a>';
					$uiAction .= '</li>';
			}



			$uiAction .= '<li class="list-inline-item px-2">';
			$uiAction .= '<a onclick="ViewOrder(\'' . $value['id'] . '\')" href="javascript: void(0);" title="View"><i class="mdi mdi-eye"></i></a>';
			$uiAction .= '</li>';

			if ($value['invoice'] != "") {

				$routeInvoice = route('orders.sales.invoice.list') . "?order_id=" . $value['id'];

				if(isCreUser() == 0) {
					$uiAction .= '<li class="list-inline-item px-2">';
					$uiAction .= '<a target="_blank"  href="' . $routeInvoice . '" title="Invoice"><i class="bx bx-receipt"></i></a>';
					$uiAction .= '</li>';
				}
			}

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

		$Order = Order::query();
		$Order->select('orders.id', 'orders.created_at', 'orders.gst_number', 'orders.payment_mode', 'orders.shipping_cost', 'orders.sale_persons', 'orders.total_mrp_minus_disocunt', 'orders.total_mrp', 'orders.gst_tax', 'orders.gst_percentage', 'orders.gst_tax', 'orders.delievery_charge', 'orders.total_payable', 'orders.pending_total_payable', 'orders.bill_address_line1', 'orders.bill_address_line2', 'orders.bill_pincode', 'orders.bill_state_id', 'orders.bill_city_id', 'orders.bill_country_id', 'orders.d_address_line1', 'orders.d_address_line2', 'orders.d_pincode', 'orders.d_state_id', 'orders.d_city_id', 'orders.d_country_id', 'orders.remark', 'orders.remark', 'channel_partner_detail.email as channel_partner_email', 'channel_partner_detail.phone_number as channel_partner_phone_number', 'channel_partner_detail.dialing_code as channel_partner_dialing_code', 'channel_partner_detail.first_name as channel_partner_first_name', 'channel_partner_detail.last_name as channel_partner_last_name', 'channel_partner.firm_name as channel_partner_firm_name', 'channel_partner.type as channel_partner_type', 'channel_partner.credit_limit as channel_partner_credit_limit', 'channel_partner.credit_days as channel_partner_credit_days', 'channel_partner.pending_credit as channel_partner_pending_credit', 'channel_partner.credit_days as channel_partner_credit_days');

		$Order->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
		$Order->leftJoin('users as channel_partner_detail', 'channel_partner_detail.id', '=', 'channel_partner.user_id');
		$Order->where('orders.id', $request->order_id);
		//$Order->where('channel_partner.reporting_company_id', Auth::user()->company_id);

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
			//$Order['display_date_time'] = convertOrderDateTime($Order->created_at);

			$OrderItem = OrderItem::query();
			$OrderItem->select('order_items.id', 'order_items.qty', 'order_items.mrp', 'order_items.weight', 'order_items.total_mrp', 'order_items.pending_qty', 'order_items.product_inventory_id', 'order_items.discount_percentage', 'product_inventory.thumb as product_image', 'product_brand.name as product_brand_name', 'product_code.name as product_code_name', 'product_inventory.quantity as product_stock');
			$OrderItem->leftJoin('product_inventory', 'product_inventory.id', '=', 'order_items.product_inventory_id');
			$OrderItem->leftJoin('data_master as product_brand', 'product_brand.id', '=', 'product_inventory.product_brand_id');
			$OrderItem->leftJoin('data_master as product_code', 'product_code.id', '=', 'product_inventory.product_code_id');

			$OrderItem->where('order_id', $Order->id);
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
	public function cancelDetail(Request $request)
	{

		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		$isAccountUser = isAccountUser();

		$Order = Order::query();
		$Order->select('orders.id', 'orders.created_at', 'orders.gst_number', 'orders.payment_mode', 'orders.shipping_cost', 'orders.sale_persons', 'orders.total_mrp_minus_disocunt', 'orders.total_mrp', 'orders.gst_tax', 'orders.gst_percentage', 'orders.gst_tax', 'orders.delievery_charge', 'orders.total_payable', 'orders.pending_total_payable', 'orders.bill_address_line1', 'orders.bill_address_line2', 'orders.bill_pincode', 'orders.bill_state_id', 'orders.bill_city_id', 'orders.bill_country_id', 'orders.d_address_line1', 'orders.d_address_line2', 'orders.d_pincode', 'orders.d_state_id', 'orders.d_city_id', 'orders.d_country_id', 'orders.remark', 'orders.remark', 'channel_partner_detail.email as channel_partner_email', 'channel_partner_detail.phone_number as channel_partner_phone_number', 'channel_partner_detail.dialing_code as channel_partner_dialing_code', 'channel_partner_detail.first_name as channel_partner_first_name', 'channel_partner_detail.last_name as channel_partner_last_name', 'channel_partner.firm_name as channel_partner_firm_name', 'channel_partner.type as channel_partner_type', 'channel_partner.credit_limit as channel_partner_credit_limit', 'channel_partner.credit_days as channel_partner_credit_days', 'channel_partner.pending_credit as channel_partner_pending_credit', 'channel_partner.credit_days as channel_partner_credit_days');
		$Order->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
		$Order->leftJoin('users as channel_partner_detail', 'channel_partner_detail.id', '=', 'channel_partner.user_id');
		$Order->where('orders.id', $request->order_id);
		//$Order->where('channel_partner.reporting_company_id', Auth::user()->company_id);

		if ($isAccountUser == 1 || $isAdminOrCompanyAdmin == 1) {
			// if (Auth::user()->parent_id != 0) {

			// 	$parent = ChannelPartner::where('user_id', Auth::user()->parent_id)->first();

			// 	$Order->where('channel_partner.reporting_manager_id', $parent->user_id);
			// } else {

			// 	$Order->where('channel_partner.reporting_company_id', Auth::user()->company_id);
			// 	$Order->where('channel_partner.reporting_manager_id', 0);
			// }
		} else if (isChannelPartner(Auth::user()->type) != 0) {

			$Order->where('channel_partner.reporting_manager_id', Auth::user()->id);
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
			//$Order['display_date_time'] = convertOrderDateTime($Order->created_at);

			$OrderItem = OrderItem::query();
			$OrderItem->select('order_items.id', 'order_items.dispatched_qty', 'order_items.qty', 'order_items.mrp', 'order_items.weight', 'order_items.total_mrp', 'order_items.pending_qty', 'order_items.product_inventory_id', 'order_items.discount_percentage', 'product_inventory.thumb as product_image', 'product_brand.name as product_brand_name', 'product_code.name as product_code_name', 'product_inventory.quantity as product_stock');
			$OrderItem->leftJoin('product_inventory', 'product_inventory.id', '=', 'order_items.product_inventory_id');
			$OrderItem->leftJoin('data_master as product_brand', 'product_brand.id', '=', 'product_inventory.product_brand_id');
			$OrderItem->leftJoin('data_master as product_code', 'product_code.id', '=', 'product_inventory.product_code_id');

			$OrderItem->where('order_id', $Order->id);
			$OrderItem->orderBy('id', 'desc');
			$OrderItem = $OrderItem->get();

			foreach ($OrderItem as $key => $value) {

				$OrderItem[$key]['cancelled_qty'] = $value['qty'] - $value['dispatched_qty'];
			}

			$Order['items'] = $OrderItem;

			$response = successRes("Order detail");
			$response['data'] = $Order;
		} else {
			$response = errorRes("Invalid order id");
		}
		return response()->json($response)->header('Content-Type', 'application/json');
	}
	public function cancel(Request $request)
	{

		if (isAdmin() == 1 || isCompanyAdmin() == 1 || isCreUser() == 1) {

			$Order = Order::find($request->id);

			if ($Order->status != 3 && $Order->status != 4) {
				// $Order->status = 4;


				$Order->is_cancelled = 1;
				$Order->cancelled_total_qty = $Order->total_qty - $Order->dispatched_total_qty;
				$Order->save();

				if ($Order->cancelled_total_qty == $Order->total_qty) {
					$Order->status = 4;
					$Order->actual_total_mrp_minus_disocunt = 0;
				}

				$Order->save();


				$OrederItems = OrderItem::where('order_id', $Order->id)->get();

				foreach ($OrederItems as $key => $value) {
					$OrderItem = OrderItem::find($value->id);
					$OrderItem->cancelled_qty = $OrderItem->qty - $OrderItem->dispatched_qty;
					$OrderItem->save();
					if($OrderItem){
						$OrderItem = OrderItem::find($value->id);
						$OrderItem->pending_qty = $OrderItem->qty - $OrderItem->dispatched_qty - $OrderItem->cancelled_qty;
						$OrderItem->save();
					}
				}
				Invoice::where('order_id', $Order->id)->whereNotIn('status', [2, 3])->update(['is_cancelled' => 1]);

				if ($Order->status != 4) {

					$Invoices = Invoice::where('order_id', $Order->id)->where('is_cancelled', 0)->get();
					$actual_total_mrp_minus_disocunt = 0;

					foreach ($Invoices as $key => $value) {
						$actual_total_mrp_minus_disocunt = $actual_total_mrp_minus_disocunt + $value['total_mrp_minus_disocunt'];
					}



					$Order->actual_total_mrp_minus_disocunt = $actual_total_mrp_minus_disocunt;
					$Order->save();
				}



				$response = successRes("Successfully mark as partially cancelled");
			} else {
				$response = errorRes("Fully dispatched order can't cancel");
			}
		} else {
			$response = errorRes("Invalid access");
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
				$orderItems[$key]['info']['product_brand_name'] = $value['product_brand_name'];
				$orderItems[$key]['info']['product_code_name'] = $value['product_code_name'];
				// $orderItems[$key]['info']['description'] = $value['description'];
				$orderItems[$key]['info']['product_image'] = $value['product_image'];
				$orderItems[$key]['info']['product_stock'] = $value['product_stock'];
				$orderItems[$key]['info']['pending_qty'] = $value['pending_qty'];
				$orderItems[$key]['info']['orignal_qty'] = $value['qty'];

				$orderItems[$key]['mrp'] = $value['mrp'];
				$orderItems[$key]['qty'] = $value['updated_qty'];
				$orderItems[$key]['discount_percentage'] = $value['discount_percentage'];
				$orderItems[$key]['weight'] = $value['weight'];
			}

			$GSTPercentage = $inputJSON['gst_percentage'];
			$shippingCost = $inputJSON['shipping_cost'];
			$orderDetail = calculationProcessOfOrder($orderItems, $GSTPercentage, $shippingCost);
			$response = successRes("Order detail");
			$response['order'] = $orderDetail;
		} else {

			$response = errorRes("Something went wrong");
		}

		return response()->json($response)->header('Content-Type', 'application/json');
	}
	public function invoiceSave(Request $request)
	{

		$validator = Validator::make($request->all(), [
			'invoice_number' => ['required'],
			'invoice_file' => ['required'],
			'invoice_date' => ['required'],

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

				$InvoiceItems = array();
				$keyCount = 0;

				foreach ($request->input_order_item_id as $key => $value) {

					if (isset($request->input_order_item_id[$key])) {

						$inputQty = (int) $request->input_qty[$key];
						$OrderItem = OrderItem::find($value);
						if ($OrderItem) {
							$pendingQty = floatval($OrderItem->pending_qty);

							if ($pendingQty >= $inputQty) {

								if ($inputQty > 0) {

									$InvoiceItems[$keyCount]['id'] = $request->input_order_item_id[$key];
									$InvoiceItems[$keyCount]['qty'] = $inputQty;
									$InvoiceItems[$keyCount]['mrp'] = $OrderItem->mrp;
									$InvoiceItems[$keyCount]['discount_percentage'] = $OrderItem->discount_percentage;
									$InvoiceItems[$keyCount]['weight'] = $OrderItem->weight;
									$InvoiceItems[$keyCount]['info']['pending_qty'] = $pendingQty - $inputQty;

									$keyCount++;
								}
							} else {

								$response = errorRes("Invalid invoice QTY");
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

				$Order = Order::query();
				$Order->select('orders.*', 'channel_partner.reporting_company_id');
				$Order->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');

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

				$Order = $Order->where('orders.id', $request->invoice_order_id)->first();

				if ($Order && $Order->status != 4) {

					$GSTPercentage = $Order->gst_percentage;
					$shippingCost = $Order->shipping_cost;
					$invoiceDetail = calculationProcessOfOrder($InvoiceItems, $GSTPercentage, $shippingCost);

					if ($request->verify_payable_total != $invoiceDetail['total_payable']) {
						$response = errorRes("Something went wrong with price calculation");
						$response['verify_payable_total'] = $request->verify_payable_total;
						$response['order'] = $invoiceDetail;
						return response()->json($response)->header('Content-Type', 'application/json');
					}

					$Invoice = new Invoice();
					$Invoice->user_id = Auth::user()->id;
					$Invoice->order_id = $Order->id;
					$Invoice->invoice_date = $request->invoice_date;
					$Invoice->invoice_number = $request->invoice_number;
					$Invoice->invoice_file = $uploadedFile1;
					$Invoice->total_mrp = $invoiceDetail['total_mrp'];
					$Invoice->total_discount = $invoiceDetail['total_discount'];
					$Invoice->gst_percentage = $invoiceDetail['gst_percentage'];
					$Invoice->total_weight = $invoiceDetail['total_weight'];
					$Invoice->delievery_charge = $invoiceDetail['delievery_charge'];
					$Invoice->total_mrp_minus_disocunt = $invoiceDetail['total_mrp_minus_disocunt'];
					$Invoice->gst_tax = $invoiceDetail['gst_tax'];
					$Invoice->shipping_cost = $invoiceDetail['shipping_cost'];
					$Invoice->total_payable = $invoiceDetail['total_payable'];
					$Invoice->total_qty = $invoiceDetail['total_qty'];
					$Invoice->save();

					foreach ($invoiceDetail['items'] as $key => $value) {

						$InvoiceItem = new InvoiceItem();
						$InvoiceItem->user_id = Auth::user()->id;
						$InvoiceItem->order_item_id = $value['id'];
						$InvoiceItem->order_id = $Order->id;
						$InvoiceItem->invoice_id = $Invoice->id;
						$InvoiceItem->qty = $value['qty'];
						$InvoiceItem->pending_packed_qty = $value['qty'];
						$InvoiceItem->mrp = $value['mrp'];
						$InvoiceItem->total_mrp = $value['total_mrp'];
						$InvoiceItem->discount_percentage = $value['discount_percentage'];
						$InvoiceItem->discount = $value['discount'];
						$InvoiceItem->total_discount = $value['discount'];
						$InvoiceItem->mrp_minus_disocunt = $value['mrp_minus_disocunt'];
						$InvoiceItem->weight = $value['weight'];
						$InvoiceItem->total_weight = $value['total_weight'];
						$InvoiceItem->save();

						$OrderItem = OrderItem::find($value['id']);
						$OrderItem->pending_qty = $value['info']['pending_qty'];
						$OrderItem->save();
					}



					$invoiceString = $Order->invoice;

					if ($invoiceString != "") {
						$invoiceString = explode(",", $invoiceString);
						$invoiceArray = array();
						foreach ($invoiceString as $keyI => $valueI) {
							if ($valueI != "") {
								$invoiceArray[] = $valueI;
							}
						}
						$invoiceArray[] = $Invoice->id;
						$invoiceString = implode(",", $invoiceArray);
						$Order->invoice = $invoiceString;
					} else {
						$Order->invoice = $Invoice->id;
					}



					$needToSendNotification = 0;
					if ($Order->status == 0) {
						$Order->status = 1;
						$needToSendNotification = 1;
					}
					$Order->sub_status = 0;
					$Order->save();

					$notificationDebug = "";
					$UsersNotificationTokens = array();

					if ($needToSendNotification == 1) {

						$ChannelPartner = ChannelPartner::where('user_id', $Order->channel_partner_user_id)->first();

						$salesPersonsList = explode(",", $ChannelPartner->sale_persons);

						$totalUsers = array();
						foreach ($salesPersonsList as $ks => $vs) {
							if ($vs != "") {
								$notificationUserids = getParentSalePersonsIds($vs);
								$notificationUserids[] = $vs;
								$totalUsers = array_merge($totalUsers, $notificationUserids);
							}
						}

						$totalUsers[] = $Order->channel_partner_user_id;
						if (count($totalUsers) > 0) {
							$totalUsers = array_unique($totalUsers);
							$totalUsers = array_values($totalUsers);
							$UsersNotificationTokens = UsersNotificationTokens($notificationUserids);
							$mobileNotificationTitle = "Order Update";
							$fromStatus = getOrderStatus(0);
							$toStatus = getOrderStatus(1);
							//$mobileNotificationMessage = "Your Order " . $Order->id . " Status Update " . $fromStatus . " To " . $toStatus;
							$mobileNotificationMessage = "Your Order " . $Order->id . " " . $ChannelPartner->firm_name . " Status Update " . $fromStatus . " To " . $toStatus;
							$notificationDebug = sendNotificationTOAndroid($mobileNotificationTitle, $mobileNotificationMessage, $UsersNotificationTokens,'Order',json_decode(json_encode($Order),true));
						}
					}

					$response = successRes("Successfully generated invoice");
					$response['debug'] = $notificationDebug;
					$response['notification_token'] = $UsersNotificationTokens;
				} else {

					$response = errorRes("Invalid order");
				}
			} else {

				$response = errorRes("Invalid pdf");
			}
		}

		return response()->json($response)->header('Content-Type', 'application/json');
	}
	public function invoiceList(Request $request)
	{

		$Order = Order::select('id')->find($request->order_id);
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
			1 => 'orders.user_id',
			2 => 'orders.channel_partner_user_id',
			3 => 'orders.sale_persons',
			4 => 'orders.payment_mode',
			5 => 'invoice.status',

		);

		$selectColumns = array(
			0 => 'invoice.id',
			1 => 'orders.user_id',
			2 => 'orders.channel_partner_user_id',
			3 => 'orders.sale_persons',
			4 => 'orders.payment_mode',
			5 => 'invoice.status',
			6 => 'invoice.created_at',
			7 => 'users.first_name as first_name',
			8 => 'users.last_name as last_name',
			9 => 'channel_partner.firm_name',
			10 => 'orders.payment_mode',
			11 => 'invoice.gst_tax',
			12 => 'invoice.total_payable',
			13 => 'invoice.invoice_file',
			14 => 'invoice.invoice_number',
			15 => 'invoice.total_mrp_minus_disocunt',

		);

		$query = Invoice::query();
		$query->leftJoin('orders', 'invoice.order_id', '=', 'orders.id');
		$query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');

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
		$query = Invoice::query();
		$query->leftJoin('orders', 'invoice.order_id', '=', 'orders.id');
		$query->leftJoin('users', 'users.id', '=', 'orders.user_id');
		$query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
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

			$paymentMode = '<span class="badge rounded-pill badge-soft-dark font-size-11">' . getPaymentModeName($value['payment_mode']) . '</span>';

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

		$Order = Invoice::query();
		$Order->select('invoice.id', 'invoice.dispatch_detail', 'invoice.eway_bill', 'invoice.created_at', 'orders.gst_number', 'orders.payment_mode', 'orders.sale_persons', 'invoice.total_mrp_minus_disocunt', 'invoice.total_mrp', 'invoice.gst_tax', 'invoice.gst_percentage', 'invoice.delievery_charge', 'invoice.total_payable', 'orders.bill_address_line1', 'orders.bill_address_line2', 'orders.bill_pincode', 'orders.bill_state_id', 'orders.bill_city_id', 'orders.bill_country_id', 'orders.d_address_line1', 'orders.d_address_line2', 'orders.d_pincode', 'orders.d_state_id', 'orders.d_city_id', 'orders.d_country_id', 'channel_partner_detail.email as channel_partner_email', 'channel_partner_detail.phone_number as channel_partner_phone_number', 'channel_partner_detail.dialing_code as channel_partner_dialing_code', 'channel_partner_detail.first_name as channel_partner_first_name', 'channel_partner_detail.last_name as channel_partner_last_name', 'channel_partner.firm_name as channel_partner_firm_name', 'channel_partner.type as channel_partner_type', 'channel_partner.credit_limit as channel_partner_credit_limit', 'channel_partner.credit_days as channel_partner_credit_days', 'channel_partner.pending_credit as channel_partner_pending_credit', 'channel_partner.credit_days as channel_partner_credit_days');
		$Order->leftJoin('orders', 'invoice.order_id', '=', 'orders.id');
		$Order->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
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
			$OrderItem->select('order_items.id', 'invoice_items.qty', 'invoice_items.total_mrp', 'product_inventory.image as product_image', 'product_brand.name as product_brand_name', 'product_code.name as product_code_name');
			$OrderItem->leftJoin('order_items', 'invoice_items.order_item_id', '=', 'order_items.id');
			$OrderItem->leftJoin('product_inventory', 'product_inventory.id', '=', 'order_items.product_inventory_id');
			$OrderItem->leftJoin('data_master as product_brand', 'product_brand.id', '=', 'product_inventory.product_brand_id');
			$OrderItem->leftJoin('data_master as product_code', 'product_code.id', '=', 'product_inventory.product_code_id');

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

				$Invoice = Invoice::query();
				$Invoice->select('invoice.*');
				$Invoice->leftJoin('orders', 'invoice.order_id', '=', 'orders.id');
				$Invoice->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');

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
}
