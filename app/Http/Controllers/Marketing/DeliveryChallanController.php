<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\CityList;
use App\Models\CountryList;
use App\Models\MarketingChallan;
use App\Models\MarketingChallanItem;
use App\Models\StateList;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

//use Session;

class DeliveryChallanController extends Controller
{

	public function __construct()
	{

		$this->middleware(function ($request, $next) {

			$tabCanAccessBy = array(0, 1, 2, 6);

			if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
				return redirect()->route('dashboard');
			}

			return $next($request);
		});
	}

	public function index(Request $request)
	{

		$data = array();
		$data['title'] = "Marketing Request Delivery Challan";
		return view('marketing/orders/delivery_challan', compact('data'));
	}

	function ajax(Request $request)
	{

		$isSalePerson = isSalePerson();
		if ($isSalePerson == 1) {
			$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
		}
		$isMarketingUser = isMarketingUser();
		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();

		$searchColumns = array(
			0 => 'marketing_orders_challan.id',
			1 => 'marketing_orders_challan.id',
			2 => 'marketing_orders_challan.id',

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

		$recordsTotal = $query->count();
		$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.
		$query = MarketingChallan::query();
		$query->leftJoin('marketing_orders', 'marketing_orders_challan.order_id', '=', 'marketing_orders.id');
		$query->leftJoin('users', 'users.id', '=', 'marketing_orders.user_id');
		$query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'marketing_orders.channel_partner_user_id');
		//$query->where('channel_partner.reporting_company_id', Auth::user()->company_id);
		//$query->where('marketing_orders_challan.order_id', $request->order_id);

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

			$data[$key]['detail'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">#' . $value['invoice_number'] . '</a></h5>
			    <p class="text-muted mb-0" data-bs-toggle="tooltip" title="Marketing Request">#MR' . ($value['order_id']) . '</p>
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
			$data[$key]['status'] = '<span class="badge ' . $statusClass . ' badge-pill badgefont-size-11">' . $data[$key]['status'] . '</span>';

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

	public function detail(Request $request)
	{

		$isSalePerson = isSalePerson();
		if ($isSalePerson == 1) {
			$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
		}
		$isMarketingUser = isMarketingUser();
		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();

		$Order = MarketingChallan::query();
		$Order->select('marketing_orders_challan.id', 'marketing_orders_challan.created_at', 'marketing_orders.gst_number', 'marketing_orders.payment_mode', 'marketing_orders_challan.invoice_number', 'marketing_orders.sale_persons', 'marketing_orders_challan.total_mrp_minus_disocunt', 'marketing_orders_challan.total_mrp', 'marketing_orders_challan.gst_tax', 'marketing_orders_challan.delievery_charge', 'marketing_orders_challan.total_payable', 'marketing_orders.bill_address_line1', 'marketing_orders.bill_address_line2', 'marketing_orders.bill_pincode', 'marketing_orders.bill_state_id', 'marketing_orders.bill_city_id', 'marketing_orders.bill_country_id', 'marketing_orders.d_address_line1', 'marketing_orders.d_address_line2', 'marketing_orders.d_pincode', 'marketing_orders.d_state_id', 'marketing_orders.d_city_id', 'marketing_orders.d_country_id', 'channel_partner_detail.email as channel_partner_email', 'channel_partner_detail.phone_number as channel_partner_phone_number', 'channel_partner_detail.dialing_code as channel_partner_dialing_code', 'channel_partner_detail.first_name as channel_partner_first_name', 'channel_partner_detail.last_name as channel_partner_last_name', 'channel_partner.firm_name as channel_partner_firm_name', 'channel_partner.type as channel_partner_type', 'channel_partner.credit_limit as channel_partner_credit_limit', 'channel_partner.credit_days as channel_partner_credit_days', 'channel_partner.pending_credit as channel_partner_pending_credit', 'channel_partner.credit_days as channel_partner_credit_days');
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
			$OrderItem = array();

			$OrderItem = MarketingChallanItem::query();
			$OrderItem->select('marketing_orders_challan_items.id', 'marketing_orders_challan_items.qty', 'marketing_orders_challan_items.total_mrp', 'marketing_product_inventory.image as product_image', 'product_code.name as product_code_name');
			$OrderItem->leftJoin('marketing_order_items', 'marketing_orders_challan_items.order_item_id', '=', 'marketing_order_items.id');
			$OrderItem->leftJoin('marketing_product_inventory', 'marketing_product_inventory.id', '=', 'marketing_order_items.marketing_product_inventory_id');
			$OrderItem->leftJoin('data_master as product_group', 'product_group.id', '=', 'marketing_product_inventory.marketing_product_group_id');
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
		return response()->json($response)->header('Content-Type', 'application/json');
	}
}
