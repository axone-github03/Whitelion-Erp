<?php

namespace App\Http\Controllers;

use App\Models\CityList;
use App\Models\CountryList;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\StateList;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

//use Session;

class OrderSubController extends Controller
{

	public function __construct()
	{

		$this->middleware(function ($request, $next) {

			$tabCanAccessBy = array(0, 1, 101, 102, 103);

			if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
				return redirect()->route('dashboard');
			}

			return $next($request);
		});
	}

	public function searchChannelPartenerType(Request $request)
    {
        $CompanyList = array();
        $CompanyList[0]['id'] = 0;
        $CompanyList[0]['text'] = 'All';
        $CompanyList[1]['id'] = 101;
        $CompanyList[1]['text'] = 'ASM';
        $CompanyList[2]['id'] = 102;
        $CompanyList[2]['text'] = 'ADM';
        $CompanyList[3]['id'] = 103;
        $CompanyList[3]['text'] = 'APM';
        $CompanyList[4]['id'] = 104;
        $CompanyList[4]['text'] = 'AD';
        $CompanyList[5]['id'] = 105;
        $CompanyList[5]['text'] = 'Retailer';

        $response = array();
        $response['results'] = $CompanyList;
        $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');
    }
	public function all()
	{

		$data = array();
		$data['title'] = "Sub Orders";
		$data['type'] = 0;
		return view('orders/sub_order', compact('data'));
	}

	public function asm()
	{

		$data = array();
		$data['title'] = "Sub Orders";
		$data['type'] = 101;
		return view('orders/sub_order', compact('data'));
	}

	public function adm()
	{

		$data = array();
		$data['title'] = "Sub Orders";
		$data['type'] = 102;
		return view('orders/sub_order', compact('data'));
	}
	public function apm()
	{

		$data = array();
		$data['title'] = "Sub Orders";
		$data['type'] = 103;
		return view('orders/sub_order', compact('data'));
	}

	public function ad()
	{

		$data = array();
		$data['title'] = "Sub Orders";
		$data['type'] = 104;
		return view('orders/sub_order', compact('data'));
	}

	public function retailer()
	{

		$data = array();
		$data['title'] = "Sub Orders";
		$data['type'] = 105;
		return view('orders/sub_order', compact('data'));
	}

	public function afm()
	{

		$data = array();
		$data['title'] = "Sub Orders";
		$data['type'] = 106;
		return view('orders/sub_order', compact('data'));
	}

	function ajax(Request $request)
	{

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
			14 => 'orders.sub_status',
			15 => 'orders.invoice',
			16 => 'channel_partner.type as channel_partner_type',
			17 => 'channel_partner_user.first_name as channel_partner_user_first_name',
			18 => 'channel_partner_user.last_name as channel_partner_user_last_name',
			19 => 'channel_partner_user.phone_number as channel_partner_user_phone_number',
			20 => 'orders.is_cancelled'
		);

		$recordsTotal = Order::query();
		$recordsTotal->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');

		if (Auth::user()->type == 0 || Auth::user()->type == 1) {

			if ($request->type != 0) {

				$recordsTotal->where('channel_partner.type', $request->type);
			}
		} else if (isChannelPartner(Auth::user()->type) != 0) {

			$subchannelParnters = getChildChannelPartners(Auth::user()->id, $request->type);

			if (count($subchannelParnters) > 0) {

				$recordsTotal->whereIn('orders.channel_partner_user_id', $subchannelParnters);
			} else {
				$recordsTotal->where('orders.channel_partner_user_id', 0);
			}
			if ($request->type != 0) {
				$recordsTotal->where('channel_partner.type', $request->type);
			}
		}
		$recordsTotal = $recordsTotal->count();

		$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.
		$query = Order::query();
		$query->leftJoin('users', 'users.id', '=', 'orders.user_id');
		$query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
		$query->leftJoin('users as channel_partner_user', 'channel_partner_user.id', '=', 'orders.channel_partner_user_id');

		if (Auth::user()->type == 0 || Auth::user()->type == 1) {
			if ($request->type != 0) {
				$query->where('channel_partner.type', $request->type);
			}
		} else if (isChannelPartner(Auth::user()->type) != 0) {

			if (count($subchannelParnters) > 0) {

				$query->whereIn('orders.channel_partner_user_id', $subchannelParnters);
			} else {
				$query->where('orders.channel_partner_user_id', 0);
			}

			if ($request->type != 0) {
				$query->where('channel_partner.type', $request->type);
			}
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

			$data[$key]['payment_detail'] = '<p class="text-muted mb-0">EXGST&nbsp;&nbsp&nbsp;&nbsp;&nbsp;: <i class="fas fa-rupee-sign"></i> <span class="price-lable-font">' . highlightString(priceLable($value['total_mrp_minus_disocunt']),$search_value) . '</span></p>

			<p class="text-muted mb-0 ">TOTAL&nbsp;&nbsp;&nbsp;&nbsp;&nbsp: <i class="fas fa-rupee-sign"></i> <span class="price-lable-font">' . highlightString(priceLable($value['total_payable']),$search_value) . '</span></p>
			   ';

			$data[$key]['sub_status'] = "";


			$data[$key]['status'] = getOrderLable($value['status']);


			if ($value['status'] != 4 && $value['is_cancelled'] == 1) {
				$data[$key]['status'] = $data[$key]['status'] . "-" . '<span class="badge badge-pill badge badge-soft-danger font-size-11">PARTIALLY CANCELLED</span>';
			} else {

				if ($value['status'] == 1 || $value['status'] == 2) {
					$data[$key]['sub_status'] = getInvoiceLable($value['sub_status']);
				}


				if ($data[$key]['sub_status'] != "") {
					$data[$key]['status'] = $data[$key]['status'] . "-" . $data[$key]['sub_status'];
				}
			}

			// if ($value['status'] == 1 || $value['status'] == 2) {
			// 	$data[$key]['sub_status'] = getInvoiceLable($value['sub_status']);
			// }

			// $data[$key]['status'] = getOrderLable($value['status']);
			// if ($data[$key]['sub_status'] != "") {
			// 	$data[$key]['status'] = $data[$key]['status'] . "-" . $data[$key]['sub_status'];
			// }

			// if ($value['status'] != 4 && $value['is_cancelled'] == 1) {
			// 	$data[$key]['status'] = $data[$key]['status'] . "-" . '<span class="badge badge-pill badge badge-soft-danger font-size-11">PARTIALLY CANCELLED</span>';
			// } else {

			// 	if ($value['status'] == 1 || $value['status'] == 2) {
			// 		$data[$key]['sub_status'] = getInvoiceLable($value['sub_status']);
			// 	}
			// }

			$uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';

			if (Auth::user()->type == 0 && $value['is_cancelled'] == 0 && $value['status'] != 3) {

				$uiAction .= '<li class="list-inline-item px-2">';
				$uiAction .= '<a onclick="CancelOrder(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Cancel"><i class="mdi mdi-close-circle-outline"></i></a>';
				$uiAction .= '</li>';
			}

			$uiAction .= '<li class="list-inline-item px-2">';
			$uiAction .= '<a onclick="ViewOrder(\'' . $value['id'] . '\')" href="javascript: void(0);" title="View"><i class="mdi mdi-eye"></i></a>';
			$uiAction .= '</li>';

			if ($value['invoice'] != "") {

				$routeInvoice = route('order.sub.invoice') . "?order_id=" . $value['id'];

				$uiAction .= '<li class="list-inline-item px-2">';
				$uiAction .= '<a target="_blank"  href="' . $routeInvoice . '" title="Invoice"><i class="bx bx-receipt"></i></a>';
				$uiAction .= '</li>';
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

		$Order = Order::query();
		$Order->select('orders.id', 'orders.created_at', 'orders.gst_number', 'orders.payment_mode', 'orders.sale_persons', 'orders.total_mrp', 'orders.total_mrp_minus_disocunt', 'orders.total_mrp', 'orders.gst_percentage', 'orders.gst_tax', 'orders.delievery_charge', 'orders.total_payable', 'orders.pending_total_payable', 'orders.bill_address_line1', 'orders.bill_address_line2', 'orders.bill_pincode', 'orders.bill_state_id', 'orders.bill_city_id', 'orders.bill_country_id', 'orders.d_address_line1', 'orders.d_address_line2', 'orders.d_pincode', 'orders.d_state_id', 'orders.d_city_id', 'orders.d_country_id', 'orders.remark', 'channel_partner_detail.email as channel_partner_email', 'channel_partner_detail.phone_number as channel_partner_phone_number', 'channel_partner_detail.dialing_code as channel_partner_dialing_code', 'channel_partner_detail.first_name as channel_partner_first_name', 'channel_partner_detail.last_name as channel_partner_last_name', 'channel_partner.firm_name as channel_partner_firm_name', 'channel_partner.type as channel_partner_type', 'channel_partner.credit_limit as channel_partner_credit_limit', 'channel_partner.credit_days as channel_partner_credit_days', 'channel_partner.pending_credit as channel_partner_pending_credit', 'channel_partner.credit_days as channel_partner_credit_days');

		$Order->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
		$Order->leftJoin('users as channel_partner_detail', 'channel_partner_detail.id', '=', 'channel_partner.user_id');
		$Order->where('orders.id', $request->order_id);

		if (isChannelPartner(Auth::user()->type) != 0) {

			$subchannelParnters = getChildChannelPartners(Auth::user()->id, 0);

			if (count($subchannelParnters) > 0) {

				$Order->whereIn('orders.channel_partner_user_id', $subchannelParnters);
			} else {
				$Order->where('orders.channel_partner_user_id', 0);
			}
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

			$OrderItem = OrderItem::query();
			$OrderItem->select('order_items.id', 'order_items.qty', 'order_items.total_mrp', 'order_items.pending_qty', 'order_items.product_inventory_id', 'product_inventory.thumb as product_image', 'product_brand.name as product_brand_name', 'product_code.name as product_code_name', 'product_inventory.quantity as product_stock');
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

	public function invoiceList(Request $request)
	{

		$Order = Order::select('id')->find($request->order_id);
		if ($Order) {
			$data['title'] = "Invoice";
			$data['order_id'] = $Order->id;
			return view('orders/sub_order_invoice', compact('data'));
		} else {
			return redirect()->route('dashboard');
		}
	}

	public function invoiceListAjax(Request $request)
	{

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

		);

		$recordsTotal = Invoice::query();
		$recordsTotal->leftJoin('orders', 'invoice.order_id', '=', 'orders.id');
		$recordsTotal->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
		$recordsTotal->where('channel_partner.reporting_company_id', Auth::user()->company_id);
		$recordsTotal->where('invoice.order_id', $request->order_id);

		if (isChannelPartner(Auth::user()->type) != 0) {
			$subchannelParnters = getChildChannelPartners(Auth::user()->id, 0);
			if (count($subchannelParnters) > 0) {
				$recordsTotal->whereIn('orders.channel_partner_user_id', $subchannelParnters);
			} else {
				$recordsTotal->where('orders.channel_partner_user_id', 0);
			}
		}

		$recordsTotal = $recordsTotal->count();

		$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.
		$query = Invoice::query();
		$query->leftJoin('orders', 'invoice.order_id', '=', 'orders.id');
		$query->leftJoin('users', 'users.id', '=', 'orders.user_id');
		$query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
		$query->where('invoice.order_id', $request->order_id);
		if (isChannelPartner(Auth::user()->type) != 0) {
			$subchannelParnters = getChildChannelPartners(Auth::user()->id, 0);
			if (count($subchannelParnters) > 0) {
				$query->whereIn('orders.channel_partner_user_id', $subchannelParnters);
			} else {
				$query->where('orders.channel_partner_user_id', 0);
			}
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

			$data[$key]['payment_detail'] = '<p class="text-muted mb-0">GST&nbsp;&nbsp;&nbsp;&nbsp;&nbsp&nbsp;&nbsp;&nbsp;: <i class="fas fa-rupee-sign"></i> <span class="price-lable-font">' . priceLable($value['gst_tax']) . '</span></p>

			<p class="text-muted mb-0 ">Total &nbsp;&nbsp;&nbsp;&nbsp;&nbsp: <i class="fas fa-rupee-sign"></i> <span class="price-lable-font">' . priceLable($value['total_payable']) . '</span></p>


			   ';

			$data[$key]['status'] = getInvoiceLable($value['status']);

			$uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';

			$uiAction .= '<li class="list-inline-item px-2">';
			$uiAction .= '<a onclick="ViewInvoice(\'' . $value['id'] . '\')" href="javascript: void(0);" title="View"><i class="mdi mdi-eye"></i></a>';
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

		$Order = Invoice::query();
		$Order->select('invoice.id', 'invoice.dispatch_detail', 'invoice.eway_bill', 'invoice.created_at', 'orders.gst_number', 'orders.payment_mode', 'orders.sale_persons', 'invoice.total_mrp_minus_disocunt', 'invoice.total_mrp', 'invoice.gst_tax', 'invoice.gst_percentage', 'invoice.delievery_charge', 'invoice.total_payable', 'orders.bill_address_line1', 'orders.bill_address_line2', 'orders.bill_pincode', 'orders.bill_state_id', 'orders.bill_city_id', 'orders.bill_country_id', 'orders.d_address_line1', 'orders.d_address_line2', 'orders.d_pincode', 'orders.d_state_id', 'orders.d_city_id', 'orders.d_country_id', 'channel_partner_detail.email as channel_partner_email', 'channel_partner_detail.phone_number as channel_partner_phone_number', 'channel_partner_detail.dialing_code as channel_partner_dialing_code', 'channel_partner_detail.first_name as channel_partner_first_name', 'channel_partner_detail.last_name as channel_partner_last_name', 'channel_partner.firm_name as channel_partner_firm_name', 'channel_partner.type as channel_partner_type', 'channel_partner.credit_limit as channel_partner_credit_limit', 'channel_partner.credit_days as channel_partner_credit_days', 'channel_partner.pending_credit as channel_partner_pending_credit', 'channel_partner.credit_days as channel_partner_credit_days');
		$Order->leftJoin('orders', 'invoice.order_id', '=', 'orders.id');
		$Order->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
		$Order->leftJoin('users as channel_partner_detail', 'channel_partner_detail.id', '=', 'channel_partner.user_id');
		$Order->where('invoice.id', $request->invoice_id);
		if (isChannelPartner(Auth::user()->type) != 0) {
			$subchannelParnters = getChildChannelPartners(Auth::user()->id, 0);
			if (count($subchannelParnters) > 0) {
				$Order->whereIn('orders.channel_partner_user_id', $subchannelParnters);
			} else {
				$Order->where('orders.channel_partner_user_id', 0);
			}
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
			$OrderItem->select('order_items.id', 'invoice_items.qty', 'invoice_items.total_mrp', 'product_inventory.thumb as product_image', 'product_brand.name as product_brand_name', 'product_code.name as product_code_name');
			$OrderItem->leftJoin('order_items', 'invoice_items.order_item_id', '=', 'order_items.id');
			$OrderItem->leftJoin('product_inventory', 'product_inventory.id', '=', 'order_items.product_inventory_id');
			$OrderItem->leftJoin('data_master as product_brand', 'product_brand.id', '=', 'product_inventory.product_brand_id');
			$OrderItem->leftJoin('data_master as product_code', 'product_code.id', '=', 'product_inventory.product_code_id');
			$OrderItem->where('invoice_id', $Order->id);
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
}
