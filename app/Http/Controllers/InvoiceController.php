<?php

namespace App\Http\Controllers;
use App\Models\ChannelPartner;
use App\Models\CityList;
use App\Models\CountryList;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\StateList;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

//use Session;

class InvoiceController extends Controller {

	public function __construct() {

		$this->middleware(function ($request, $next) {

			$tabCanAccessBy = array(3);

			if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
				return redirect()->route('dashboard');

			}

			return $next($request);

		});

	}

	public function index(Request $request) {

		$data = array();
		$data['title'] = "Invoice all";
		return view('invoice/index', compact('data'));

	}

	function ajax(Request $request) {

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

		$query = Invoice::query();
		$query->leftJoin('orders', 'invoice.order_id', '=', 'orders.id');
		$query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');

		if (Auth::user()->parent_id != 0) {

			$parent = ChannelPartner::where('user_id', Auth::user()->parent_id)->first();

			$query->where('channel_partner.reporting_manager_id', $parent->user_id);

		} else {

			$query->where('channel_partner.reporting_company_id', Auth::user()->company_id);
			$query->where('channel_partner.reporting_manager_id', 0);

		}

		$recordsTotal = $query->count();
		$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.
		$query = Invoice::query();
		$query->leftJoin('orders', 'invoice.order_id', '=', 'orders.id');
		$query->leftJoin('users', 'users.id', '=', 'orders.user_id');
		$query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
		//$query->where('channel_partner.reporting_company_id', Auth::user()->company_id);
		//$query->where('invoice.order_id', $request->order_id);

		if (Auth::user()->parent_id != 0) {

			$parent = ChannelPartner::where('user_id', Auth::user()->parent_id)->first();

			$query->where('channel_partner.reporting_manager_id', $parent->user_id);

		} else {

			$query->where('channel_partner.reporting_company_id', Auth::user()->company_id);
			$query->where('channel_partner.reporting_manager_id', 0);

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

			$data[$key]['payment_detail'] = '<p class="text-muted mb-0">EXGST&nbsp&nbsp;&nbsp;&nbsp;: <i class="fas fa-rupee-sign"></i> <span class="price-lable-font">' . priceLable($value['gst_tax']) . '</span></p>

			<p class="text-muted mb-0 ">TOTAL&nbsp;&nbsp;&nbsp;&nbsp;&nbsp: <i class="fas fa-rupee-sign"></i> <span class="price-lable-font">' . priceLable($value['total_payable']) . '</span></p>


			   ';

			$data[$key]['status'] = getInvoiceLable($value['status']);

			$uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';

			$uiAction .= '<li class="list-inline-item px-2">';
			$uiAction .= '<a onclick="ViewInvoice(\'' . $value['id'] . '\')" href="javascript: void(0);" title="View"><i class="mdi mdi-eye"></i></a>';
			$uiAction .= '</li>';

			// $uiAction .= '<li class="list-inline-item px-2">';
			// $uiAction .= '<a onclick="EditInvoice(\'' . $value['id'] . '\')" href="javascript: void(0);" title="View"><i class="mdi mdi-square-edit-outline"></i></a>';
			// $uiAction .= '</li>';

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

	public function detail(Request $request) {

		$Order = Invoice::query();
		$Order->select('invoice.id', 'invoice.created_at', 'orders.gst_number', 'orders.payment_mode', 'orders.sale_persons', 'invoice.total_mrp_minus_disocunt', 'invoice.total_mrp', 'invoice.gst_tax', 'invoice.gst_percentage', 'invoice.delievery_charge', 'invoice.total_payable', 'orders.bill_address_line1', 'orders.bill_address_line2', 'orders.bill_pincode', 'orders.bill_state_id', 'orders.bill_city_id', 'orders.bill_country_id', 'orders.d_address_line1', 'orders.d_address_line2', 'orders.d_pincode', 'orders.d_state_id', 'orders.d_city_id', 'orders.d_country_id', 'channel_partner_detail.email as channel_partner_email', 'channel_partner_detail.phone_number as channel_partner_phone_number', 'channel_partner_detail.dialing_code as channel_partner_dialing_code', 'channel_partner_detail.first_name as channel_partner_first_name', 'channel_partner_detail.last_name as channel_partner_last_name', 'channel_partner.firm_name as channel_partner_firm_name', 'channel_partner.type as channel_partner_type', 'channel_partner.credit_limit as channel_partner_credit_limit', 'channel_partner.credit_days as channel_partner_credit_days', 'channel_partner.pending_credit as channel_partner_pending_credit', 'channel_partner.credit_days as channel_partner_credit_days');
		$Order->leftJoin('orders', 'invoice.order_id', '=', 'orders.id');
		$Order->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
		$Order->leftJoin('users as channel_partner_detail', 'channel_partner_detail.id', '=', 'channel_partner.user_id');
		$Order->where('invoice.id', $request->invoice_id);

		if (Auth::user()->parent_id != 0) {

			$parent = ChannelPartner::where('user_id', Auth::user()->parent_id)->first();

			$Order->where('channel_partner.reporting_manager_id', $parent->user_id);

		} else {

			$Order->where('channel_partner.reporting_company_id', Auth::user()->company_id);
			$Order->where('channel_partner.reporting_manager_id', 0);

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

			$response = successRes("Order detail");
			$response['data'] = $Order;

		} else {
			$response = errorRes("Invalid order id");
		}
		return response()->json($response)->header('Content-Type', 'application/json');

	}

}