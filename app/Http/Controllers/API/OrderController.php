<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ChannelPartner;
use App\Models\CityList;
use App\Models\CountryList;
use App\Models\CreditTranscationLog;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\MainMaster;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductInventory;
use App\Models\StateList;
use App\Models\User;
use App\Models\UserDiscount;
use Config;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mail;
use PDF;

class OrderController extends Controller
{

	public function __construct()
	{

		$this->middleware(function ($request, $next) {

			$tabCanAccessBy = array(0, 1, 2, 101, 102, 103, 104);

			if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
				$response = errorRes("Invalid access", 401);
				return response()->json($response, $response['status_code'])->header('Content-Type', 'application/json');
			}

			return $next($request);
		});
	}

	//
	function savePDF($orderID, $fileName)
	{

		$Order = Order::query();
		$Order->select('orders.id', 'orders.created_at', 'orders.gst_number', 'orders.payment_mode', 'orders.sale_persons', 'orders.total_mrp', 'orders.total_mrp_minus_disocunt', 'orders.total_mrp', 'orders.gst_percentage', 'orders.gst_tax', 'orders.delievery_charge', 'orders.total_payable', 'orders.pending_total_payable', 'orders.bill_address_line1', 'orders.bill_address_line2', 'orders.bill_pincode', 'orders.bill_state_id', 'orders.bill_city_id', 'orders.bill_country_id', 'orders.d_address_line1', 'orders.d_address_line2', 'orders.d_pincode', 'orders.d_state_id', 'orders.d_city_id', 'orders.d_country_id', 'channel_partner_detail.email as channel_partner_email', 'channel_partner_detail.phone_number as channel_partner_phone_number', 'channel_partner_detail.dialing_code as channel_partner_dialing_code', 'channel_partner_detail.first_name as channel_partner_first_name', 'channel_partner_detail.last_name as channel_partner_last_name', 'channel_partner.firm_name as channel_partner_firm_name', 'channel_partner.type as channel_partner_type', 'channel_partner.credit_limit as channel_partner_credit_limit', 'channel_partner.credit_days as channel_partner_credit_days', 'channel_partner.pending_credit as channel_partner_pending_credit', 'channel_partner.credit_days as channel_partner_credit_days');

		$Order->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
		$Order->leftJoin('users as channel_partner_detail', 'channel_partner_detail.id', '=', 'channel_partner.user_id');
		$Order->where('orders.id', $orderID);
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
			$Order['display_date_time'] = convertOrderDateTime($Order->created_at, 'date');

			$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();

			$OrderItem = OrderItem::query();

			// if ($isAdminOrCompanyAdmin == 1) {

			// 	$OrderItem->select('order_items.id', 'order_items.qty', 'order_items.total_mrp', 'order_items.pending_qty', 'order_items.product_inventory_id', 'product_inventory.image as product_image', 'product_brand.name as product_brand_name', 'product_code.name as product_code_name', 'product_inventory.quantity as product_stock');
			// } else {
			$OrderItem->select('order_items.id', 'order_items.qty', 'order_items.total_mrp', 'order_items.mrp', 'order_items.discount_percentage', 'order_items.mrp_minus_disocunt', 'order_items.pending_qty', 'order_items.product_inventory_id', 'product_inventory.thumb as product_image', 'product_brand.name as product_brand_name', 'product_code.name as product_code_name');

			// }

			$OrderItem->leftJoin('product_inventory', 'product_inventory.id', '=', 'order_items.product_inventory_id');
			$OrderItem->leftJoin('data_master as product_brand', 'product_brand.id', '=', 'product_inventory.product_brand_id');
			$OrderItem->leftJoin('data_master as product_code', 'product_code.id', '=', 'product_inventory.product_code_id');

			$OrderItem->where('order_id', $Order->id);
			$OrderItem->orderBy('id', 'desc');
			$OrderItem = $OrderItem->get();

			$Order['items'] = $OrderItem;

			$data = $Order;

			// echo '<pre>';
			// print_r($data);
			// die;
			$pdf = Pdf::loadView('orders.pdf', compact('data'));
			$pdf->save($fileName);
			return "";
		} else {
			$response = errorRes("Invalid order id");
		}
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	function ajax(Request $request)
	{

		$isSalePerson = isSalePerson();

		$searchColumns = array(
			0 => 'orders.id',
			1 => 'users.first_name',
			2 => 'users.last_name',
			3 => 'channel_partner.firm_name',

		);

		$selectColumns = array(
			0 => 'orders.id',
			1 => 'orders.user_id',
			2 => 'orders.channel_partner_user_id',
			3 => 'orders.sale_persons',
			4 => 'orders.payment_mode',
			5 => 'orders.status',
			6 => 'orders.created_at as created_at1',
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
		);

		$query = Order::query();
		$query->leftJoin('users', 'users.id', '=', 'orders.user_id');
		$query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
		$query->leftJoin('users as channel_partner_user', 'channel_partner_user.id', '=', 'orders.channel_partner_user_id');

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
		
		if($request->sort_by == 1){
			$query->orderBy('orders.id', 'asc');
		}else{
			$query->orderBy('orders.id', 'desc');
		}

		if(isset($request->fromdate)){
			$startDate = date('Y-m-d', strtotime($request->fromdate));
			$query->whereDate('orders.created_at', '>=', $startDate);
		}

		if(isset($request->todate)){
			$endDate = date('Y-m-d', strtotime($request->todate));
			$query->whereDate('orders.created_at', '<=', $endDate);
		}

		if(isset($request->status)){
			$filtered_status = explode(',',$request->status);
			$query->whereIn('orders.status', $filtered_status);
		}

		$query->select($selectColumns);

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

		$data = $query->paginate(10);

		foreach ($data as $key => $value) {

			$data[$key]->status_lable = getOrderStatus($value->status);
			$data[$key]->sub_status_lable = "";
			if ($value->status == 1 || $value->status == 2) {
				$data[$key]->sub_status_lable = getInvoiceStatus($value->sub_status);
			}
			// $data[$key]->created_at = $value->created_at;
			$data[$key]->created_at1 = convertDateTime($value->created_at1);
		}

		$data = json_decode(json_encode($data), true);
		if (isset($data['data'])) {
			foreach ($data['data'] as $k => $v) {
				$data['data'][$k]['created_at'] = $v['created_at1'];
				unset($data['data'][$k]['created_at1']);
			}
		}

		$response = successRes("Orders ");
		$response['data'] = $data;
		$response['status_list'] = getOrderStatusList();
		return response()->json($response, $response['status_code'])->header('Content-Type', 'application/json');
	}

	public function detail(Request $request)
	{

		$Order = Order::query();
		$Order->select('orders.id', 'orders.created_at', 'orders.gst_number', 'orders.payment_mode', 'orders.sale_persons', 'orders.total_mrp', 'orders.total_mrp_minus_disocunt', 'orders.total_mrp', 'orders.gst_percentage', 'orders.gst_tax', 'orders.delievery_charge', 'orders.total_payable', 'orders.pending_total_payable', 'orders.bill_address_line1', 'orders.bill_address_line2', 'orders.bill_pincode', 'orders.bill_state_id', 'orders.bill_city_id', 'orders.bill_country_id', 'orders.d_address_line1', 'orders.d_address_line2', 'orders.d_pincode', 'orders.d_state_id', 'orders.d_city_id', 'orders.d_country_id', 'orders.remark', 'channel_partner_detail.email as channel_partner_email', 'channel_partner_detail.phone_number as channel_partner_phone_number', 'channel_partner_detail.dialing_code as channel_partner_dialing_code', 'channel_partner_detail.first_name as channel_partner_first_name', 'channel_partner_detail.last_name as channel_partner_last_name', 'channel_partner.firm_name as channel_partner_firm_name', 'channel_partner.type as channel_partner_type', 'channel_partner.credit_limit as channel_partner_credit_limit', 'channel_partner.credit_days as channel_partner_credit_days', 'channel_partner.pending_credit as channel_partner_pending_credit', 'channel_partner.credit_days as channel_partner_credit_days');

		$Order->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
		$Order->leftJoin('users as channel_partner_detail', 'channel_partner_detail.id', '=', 'channel_partner.user_id');
		$Order->where('orders.id', $request->order_id);
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
			$Order['display_date_time'] = convertOrderDateTime($Order->created_at, 'date');

			$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();

			$OrderItem = OrderItem::query();

			if ($isAdminOrCompanyAdmin == 1) {

				$OrderItem->select('order_items.id', 'order_items.qty', 'order_items.total_mrp', 'order_items.pending_qty', 'order_items.product_inventory_id', 'product_inventory.thumb as product_image', 'product_brand.name as product_brand_name', 'product_code.name as product_code_name', 'product_inventory.quantity as product_stock');
			} else {
				$OrderItem->select('order_items.id', 'order_items.qty', 'order_items.total_mrp', 'order_items.pending_qty', 'order_items.product_inventory_id', 'product_inventory.thumb as product_image', 'product_brand.name as product_brand_name', 'product_code.name as product_code_name');
			}

			$OrderItem->leftJoin('product_inventory', 'product_inventory.id', '=', 'order_items.product_inventory_id');
			$OrderItem->leftJoin('data_master as product_brand', 'product_brand.id', '=', 'product_inventory.product_brand_id');
			$OrderItem->leftJoin('data_master as product_code', 'product_code.id', '=', 'product_inventory.product_code_id');

			$OrderItem->where('order_id', $Order->id);
			$OrderItem->orderBy('id', 'desc');
			$OrderItem = $OrderItem->get();

			// foreach ($OrderItem as $OK => $OV) {

			// 	$OrderItem->product_image

			// }

			$Order['items'] = $OrderItem;
			// foreach ($Order['items'] as $it => $vit) {

			// 	$path = getSpaceFilePath($Order['items'][$it]['product_image']);
			// 	$type = pathinfo($path, PATHINFO_EXTENSION);
			// 	$data = file_get_contents($path);
			// 	$base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
			// 	$Order['items'][$it]['product_image'] = $base64;

			// }

			$response = successRes("Order detail");

			$response['data'] = $Order;
		} else {
			$response = errorRes("Invalid order id");
		}
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function cancel(Request $request)
	{

		if (Auth::user()->type == 0) {

			$Order = Order::find($request->id);
			if ($Order->status == 0) {
				$Order->status = 4;
				$Order->save();
				$response = successRes("Successfully mark as cancelled");
			} else {
				$response = errorRes("Only Placed order can cancel");
			}
		} else {
			$response = errorRes("Invalid access");
		}
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function add()
	{
		$data = array();
		$data['type'] = array();
		if (Auth::user()->type == 0 || Auth::user()->type == 1) {

			$data['type'] = getChannelPartners();
		} else if (Auth::user()->type == 2) {
			$data['type'] = getChannelPartners();
		} else {
			$data['type'][] = getChannelPartners()[Auth::user()->type];
		}
		$data['title'] = "Add Order ";
		return view('orders/add', compact('data'));
	}

	// public function searchCity(Request $request) {

	// 	$CityList = array();
	// 	$CityList = ChannelPartner::select('city_list.id', 'city_list.name as text');
	// 	$CityList->leftJoin('city_list', 'city_list.id', '=', 'channel_partner.d_city_id');
	// 	$CityList->where('channel_partner.type', $request->channel_partner_type);
	// 	//$CityList->where('state_id', $request->state_id);
	// 	$CityList->where('city_list.name', 'like', "%" . $request->q . "%");
	// 	$CityList->where('city_list.status', 1);
	// 	if (Auth::user()->type == 2) {

	// 		$CityList->whereRaw('FIND_IN_SET("' . Auth::user()->id . '",channel_partner.sale_persons)>0');

	// 	} else if (Auth::user()->type == 101 || Auth::user()->type == 102 || Auth::user()->type == 103 || Auth::user()->type == 104) {
	// 		$CityList->where('channel_partner.user_id', Auth::user()->id);

	// 	}
	// 	$CityList->distinct();
	// 	$CityList->limit(5);
	// 	$CityList = $CityList->get();

	// 	$response = array();
	// 	$response['results'] = $CityList;
	// 	$response['pagination']['more'] = false;
	// 	return response()->json($response)->header('Content-Type', 'application/json');

	// }

	public function searchChannelPartner(Request $request)
	{

		$ChannelPartner = array();
		$ChannelPartner = ChannelPartner::select('channel_partner.user_id as id', DB::raw('CONCAT(channel_partner.firm_name," - ", city_list.name) AS text'));
		if (isset($request->channel_partner_type)) {
			$ChannelPartner->where('channel_partner.type', $request->channel_partner_type);
		}

		$ChannelPartner->leftJoin('city_list', 'city_list.id', '=', 'channel_partner.d_city_id');
		$ChannelPartner->leftJoin('users', 'users.id', '=', 'channel_partner.user_id');
		$ChannelPartner->where('users.status', 1);

		$q = $request->q;

		$isSalePerson = isSalePerson();
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
		} else if (isChannelPartner(Auth::user()->type) != 0) {

			$ChannelPartner->where('channel_partner.user_id', Auth::user()->id);
		}

		$ChannelPartner->where(function ($query) use ($q) {

			$query->where('channel_partner.firm_name', 'like', $q . "%");
			$query->orWhere('city_list.name', 'like', $q . "%");
		});
		$ChannelPartner->limit(9);
		$ChannelPartner = $ChannelPartner->get();

		$response = array();
		$response = successRes("Chanel Partner");
		$response['data'] = $ChannelPartner;
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function channelPartnerDetail(Request $request)
	{

		$ChannelPartner = ChannelPartner::query();
		$ChannelPartner->where('user_id', $request->channel_partner_user_id);
		$ChannelPartner->leftJoin('users', 'users.id', '=', 'channel_partner.user_id');
		$isSalePerson = isSalePerson();

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
		} else if (isChannelPartner(Auth::user()->type) != 0) {

			$ChannelPartner->where('channel_partner.user_id', Auth::user()->id);
		}

		$ChannelPartner->with(array('d_country' => function ($query) {
			$query->select('id', 'name');
		}, 'd_state' => function ($query) {
			$query->select('id', 'name');
		}, 'd_city' => function ($query) {
			$query->select('id', 'name');
		}));

		$ChannelPartner = $ChannelPartner->first();

		$response = array();
		if ($ChannelPartner) {

			$CountryList = CountryList::select('id', 'name')->find($ChannelPartner->country_id);

			if ($CountryList) {
				$ChannelPartner->country = $CountryList;
			}

			$StateList = StateList::select('id', 'name')->find($ChannelPartner->state_id);

			if ($StateList) {
				$ChannelPartner->state = $StateList;
			}

			$CityList = CityList::select('id', 'name')->find($ChannelPartner->city_id);

			if ($CityList) {
				$ChannelPartner->city = $CityList;
			}

			$salePersons = User::select('users.id', 'users.first_name', 'users.last_name')->whereIn('users.id', explode(",", $ChannelPartner->sale_persons))->get();

			$ChannelPartner->sale_persons = json_decode(json_encode($salePersons), true);

			$response = successRes("");
			$response['data'] = $ChannelPartner;
		} else {
			$response = errorRes("Invalid Channel Partner");
		}

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	function searchProduct(Request $request)
	{

		$DataMaster = array();
		$PRODUCT_CODE = MainMaster::select('id')->where('code', 'PRODUCT_CODE')->first();
		$PRODUCT_BRAND = MainMaster::select('id')->where('code', 'PRODUCT_BRAND')->first();

		$DataMaster = ProductInventory::select('product_inventory.id', DB::raw('CONCAT(product_brand.name," ",product_code.name," (",product_inventory.description,")" )  as text'));
		$DataMaster->leftJoin('data_master as product_brand', 'product_brand.id', '=', 'product_inventory.product_brand_id');
		$DataMaster->leftJoin('data_master as product_code', 'product_code.id', '=', 'product_inventory.product_code_id');

		$searchValue = $request->q;
		$DataMaster->where('product_inventory.status', 1);
		$DataMaster->where('product_brand.main_master_id', $PRODUCT_BRAND->id);
		$DataMaster->where('product_code.main_master_id', $PRODUCT_CODE->id);

		$searchValuePieces = explode(" ", $searchValue);

		if (count($searchValuePieces) > 1) {

			$DataMaster->where(function ($query) use ($searchValuePieces) {
				$query->where('product_brand.name', 'like', $searchValuePieces[0] . "%");
				$query->Where('product_code.name', 'like', $searchValuePieces[1] . "%");
			});
		} else {

			$DataMaster->where(function ($query) use ($searchValue) {
				$query->where('product_brand.name', 'like', $searchValue . "%");
				// $query->orWhere('product_code.name', 'like', "%" . $searchValue . "%");
			});
		}

		$DataMaster->limit(15);
		$DataMaster = $DataMaster->get();

		$response = array();
		$response = successRes("Product");
		$response['data'] = $DataMaster;
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function productDetail(Request $request)
	{
		$ProductInventory = ProductInventory::with(array('product_brand' => function ($query) {
			$query->select('id', 'name');
		}, 'product_code' => function ($query) {

			$query->select('id', 'name');
		}))->where('id', $request->product_inventory_id)->first();
		$response = array();
		if ($ProductInventory) {
			$UserDiscount = UserDiscount::where('product_inventory_id', $request->product_inventory_id)->where('user_id', $request->channel_partner_user_id)->first();
			$discount_percentage = 0;
			if ($UserDiscount) {

				$discount_percentage = floatval($UserDiscount->discount_percentage);
			}

			$response = successRes("");
			$response['data'] = $ProductInventory;
			$response['data']['discount_percentage'] = $discount_percentage;
		} else {
			$response = errorRes("Invalid Product");
		}

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function calculation(Request $request)
	{

		//	if ($request->expectsJson()) {

		$inputJSON = $request->all();

		$orderItems = array();

		foreach ($inputJSON['order_items'] as $key => $value) {

			$orderItems[$key]['id'] = $value['id'];
			$orderItems[$key]['info']['product_brand'] = $value['product_brand'];
			$orderItems[$key]['info']['product_code'] = $value['product_code'];
			$orderItems[$key]['info']['description'] = $value['description'];
			$orderItems[$key]['info']['image'] = $value['image'];
			$orderItems[$key]['info']['thumb'] = $value['thumb'];

			$orderItems[$key]['mrp'] = $value['price'];
			$orderItems[$key]['qty'] = $value['order_qty'];
			$orderItems[$key]['discount_percentage'] = $value['discount_percentage'];
			$orderItems[$key]['weight'] = $value['weight'];
		}

		$GSTPercentage = GSTPercentage();
		$shippingCost = $inputJSON['shipping_cost'];
		$orderDetail = calculationProcessOfOrder($orderItems, $GSTPercentage, $shippingCost);
		$response = successRes("Order detail");
		$response['order'] = $orderDetail;

		$query = User::query();
		$query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
		$ChannelPartner = $query->find($inputJSON['channel_partner_user_id']);
		$salePersons = User::select('users.first_name', 'users.last_name')->whereIn('users.id', explode(",", $ChannelPartner->sale_persons))->get();
		$data = $response;
		$data['channel_partner'] = json_decode(json_encode($ChannelPartner), true);
		$data['salePerson'] = json_decode(json_encode($salePersons), true);
		$data['d_country'] = $inputJSON['d_country'];
		$data['d_state'] = $inputJSON['d_state'];
		$data['d_city'] = $inputJSON['d_city'];
		$data['d_address_line1'] = $inputJSON['d_address_line1'];
		$data['d_address_line2'] = $inputJSON['d_address_line2'];
		$data['d_pincode'] = $inputJSON['d_pincode'];

		//$response['data'] = $data;
		//$response['preview'] = view('orders/preview', compact('data'))->render();

		// } else {

		// 	$response = errorRes("Something went wrong");

		// }

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function save(Request $request)
	{

		//$response = errorRes("Working...");
		//return response()->json($response)->header('Content-Type', 'application/json');

		$orderItems = array();

		foreach ($request->input_product_id as $key => $value) {

			$productInventory = ProductInventory::find($value);

			if (!$productInventory) {
				$response = errorRes("Invalid Product");
				return response()->json($response)->header('Content-Type', 'application/json');
			} else if ($productInventory->status != 1) {

				$response = errorRes("Deactivated Product " . $productInventory->description);
				return response()->json($response)->header('Content-Type', 'application/json');
			}

			if (!isset($request->input_qty[$key])) {
				$response = errorRes("Invalid QTY");
				return response()->json($response)->header('Content-Type', 'application/json');
			}

			$UserDiscount = UserDiscount::where('product_inventory_id', $value)->where('user_id', $request->channel_partner_user_id)->first();
			$discountPercentage = 0;
			if ($UserDiscount) {
				$discountPercentage = $UserDiscount->discount_percentage;
			}

			$orderItems[$key]['id'] = $productInventory->id;
			$orderItems[$key]['info'] = "";
			$orderItems[$key]['mrp'] = $productInventory->price;
			$orderItems[$key]['qty'] = $request->input_qty[$key];
			$orderItems[$key]['discount_percentage'] = $discountPercentage;
			$orderItems[$key]['weight'] = $productInventory->weight;
		}

		$isSalePerson = isSalePerson();

		$ChannelPartner = ChannelPartner::query();
		$ChannelPartner->where('user_id', $request->channel_partner_user_id);
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
		} else if (isChannelPartner(Auth::user()->type) != 0) {

			$ChannelPartner->where('channel_partner.user_id', Auth::user()->id);
		}
		$ChannelPartner = $ChannelPartner->first();
		$User = User::find($request->channel_partner_user_id);
		if ($ChannelPartner && $User) {

			$shippingCost = floatval($ChannelPartner->shipping_cost);
		} else {
			$response = errorRes("Invalid Channel Partner");
			return response()->json($response)->header('Content-Type', 'application/json');
		}

		$GSTPercentage = GSTPercentage();
		$orderDetail = calculationProcessOfOrder($orderItems, $GSTPercentage, $shippingCost);

		if ($request->verify_payable_total != $orderDetail['total_payable']) {
			$response = errorRes("Something went wrong with price calculation");
			$response['verify_payable_total'] = $request->verify_payable_total;
			$response['order'] = $orderDetail;
			return response()->json($response)->header('Content-Type', 'application/json');
		}

		if ($ChannelPartner->payment_mode == 2) {

			$pendingCredit = $ChannelPartner->pending_credit;
			if ($pendingCredit >= $orderDetail['total_payable']) {

				$transcationCredit = CreditTranscationLog::where('user_id', $ChannelPartner->user_id)->orderBy('id', 'desc')->first();
				if ($transcationCredit) {
					$transcationCreditValue = $transcationCredit->amount;

					if ($transcationCreditValue != $pendingCredit) {

						// $response = errorRes("Channel Partner credit mismatch");
						// return response()->json($response)->header('Content-Type', 'application/json');

					}
				} else {

					// $response = errorRes("Channel Partner has no credit transcation");
					// return response()->json($response)->header('Content-Type', 'application/json');

				}
			} else {

				// $response = errorRes("Channel Partner has not enough credit to generate order");
				// return response()->json($response)->header('Content-Type', 'application/json');

			}

			$remainCredit = $pendingCredit - $orderDetail['total_payable'];
			if ($remainCredit < 0) {

				// $response = errorRes("Something went wrong with credit calculation");
				// return response()->json($response)->header('Content-Type', 'application/json');

			}
		}

		if ($ChannelPartner->shipping_limit < $orderDetail['delievery_charge']) {

			$response = errorRes("Channel Partner has not shipping limit");
			return response()->json($response)->header('Content-Type', 'application/json');
		}

		$Order = new Order();
		$Order->user_id = Auth::user()->id;
		$Order->channel_partner_user_id = $ChannelPartner->user_id;
		$Order->sale_persons = $ChannelPartner->sale_persons;
		$Order->payment_mode = $ChannelPartner->payment_mode;
		$Order->gst_number = $ChannelPartner->gst_number;

		$Order->d_address_line1 = $request->d_address_line1;
		$Order->d_address_line2 =  isset($request->d_address_line2) ? $request->d_address_line2 : '';
		$Order->d_pincode = $request->d_pincode;
		$Order->d_country_id = $request->d_country_id;
		$Order->d_state_id = $request->d_state_id;
		$Order->d_city_id = $request->d_city_id;

		$Order->bill_address_line1 = $User->address_line1;
		$Order->bill_address_line2 = $User->address_line2;
		$Order->bill_pincode = $User->pincode;
		$Order->bill_country_id = $User->country_id;
		$Order->bill_state_id = $User->state_id;
		$Order->bill_city_id = $User->city_id;

		$Order->status = 0;
		$Order->sub_status = 0;

		$Order->total_qty = $orderDetail['total_qty'];
		$Order->total_mrp = $orderDetail['total_mrp'];
		$Order->total_discount = $orderDetail['total_discount'];
		$Order->total_mrp_minus_disocunt = $orderDetail['total_mrp_minus_disocunt'];
		$Order->actual_total_mrp_minus_disocunt = $orderDetail['total_mrp_minus_disocunt'];
		$Order->gst_percentage = $orderDetail['gst_percentage'];
		$Order->gst_tax = $orderDetail['gst_tax'];
		$Order->total_weight = $orderDetail['total_weight'];
		$Order->shipping_cost = $orderDetail['shipping_cost'];
		$Order->delievery_charge = $orderDetail['delievery_charge'];

		$Order->total_payable = $orderDetail['total_payable'];
		$Order->pending_total_payable = $orderDetail['total_payable'];
		$Order->remark = isset($request->remark) ? $request->remark : '';
		$Order->save();

		foreach ($orderDetail['items'] as $key => $value) {
			$OrderItem = new OrderItem();
			$OrderItem->user_id = $Order->user_id;
			$OrderItem->channel_partner_user_id = $Order->channel_partner_user_id;
			$OrderItem->order_id = $Order->id;
			$OrderItem->product_inventory_id = $value['id'];
			$OrderItem->qty = $value['qty'];
			$OrderItem->pending_qty = $value['qty'];
			$OrderItem->mrp = $value['mrp'];
			$OrderItem->total_mrp = $value['total_mrp'];
			$OrderItem->discount_percentage = $value['discount_percentage'];
			$OrderItem->discount = $value['discount'];
			$OrderItem->total_discount = $value['total_discount'];
			$OrderItem->mrp_minus_disocunt = $value['mrp_minus_disocunt'];
			$OrderItem->weight = $value['weight'];
			$OrderItem->total_weight = $value['total_weight'];
			$OrderItem->save();
		}

		if ($Order->id != "" && $Order->payment_mode == 2) {

			$CreditTranscationLog = new CreditTranscationLog();
			$CreditTranscationLog->user_id = $Order->channel_partner_user_id;
			$CreditTranscationLog->type = 0;
			$CreditTranscationLog->amount = $remainCredit;
			$CreditTranscationLog->request_amount = $orderDetail['total_payable'];
			$CreditTranscationLog->description = "order #" . $Order->id;
			$CreditTranscationLog->save();
			//

			$ChannelPartner->pending_credit = $remainCredit;
			$ChannelPartner->save();
		}

		// echo '<pre>';
		// print_r($orderDetail['items']);
		// d

		$ChannelPartnerUser = User::find($ChannelPartner->user_id);

		if ($ChannelPartnerUser) {

			$salesPersonString = array();
			$salePersons = User::select('users.first_name', 'users.last_name', 'users.email')->whereIn('users.id', explode(",", $ChannelPartner->sale_persons))->get();

			foreach ($salePersons as $keyS => $valueS) {

				$salesPersonString[] = $valueS->first_name . " " . $valueS->last_name;
			}
			$salesPersonString = implode(" , ", $salesPersonString);

			foreach ($orderDetail['items'] as $key => $value) {

				$productInventory = ProductInventory::find($value['id']);

				if ($productInventory->notify_when_order == 1) {

					//$orderDetail['items'][$key]['notify_emails'] = array();

					if ($productInventory->notify_emails != "") {

						$notify_emails = explode(",", $productInventory->notify_emails);
						foreach ($notify_emails as $keyNE => $valNE) {

							if ($valNE != "") {

								if (filter_var($valNE, FILTER_VALIDATE_EMAIL)) {
									$configrationForNotify = configrationForNotify();

									$ProductInventory = ProductInventory::select('product_inventory.id', DB::raw('CONCAT(product_brand.name," ",product_code.name," (",product_inventory.description,")" )  as text'));
									$ProductInventory->leftJoin('data_master as product_brand', 'product_brand.id', '=', 'product_inventory.product_brand_id');
									$ProductInventory->leftJoin('data_master as product_code', 'product_code.id', '=', 'product_inventory.product_code_id');
									$ProductInventory = $ProductInventory->find($value['id']);

									$params = array();
									$params['from_email'] = $configrationForNotify['from_email'];
									$params['from_name'] = $configrationForNotify['from_name'];
									$params['to_email'] = $valNE;
									$params['to_name'] = $configrationForNotify['to_name'];
									$params['subject'] = "New Order Placed - #" . $Order->id;

									$params['order'] = array();
									$params['order']['id'] = $Order->id;
									$params['order']['company_name'] = $ChannelPartner->firm_name;
									$params['order']['phone_number'] = $ChannelPartnerUser->phone_number;
									$params['order']['first_name'] = $ChannelPartnerUser->first_name;
									$params['order']['last_name'] = $ChannelPartnerUser->last_name;
									$params['order']['address_line1'] = $ChannelPartnerUser->address_line1;
									$params['order']['address_line2'] = $ChannelPartnerUser->address_line2;
									$params['order']['pincode'] = $ChannelPartnerUser->pincode;
									$params['order']['gst_number'] = $ChannelPartner->gst_number;
									$params['order']['city_id'] = $ChannelPartnerUser->city_id;
									$params['order']['state_id'] = $ChannelPartnerUser->state_id;
									$params['order']['country_id'] = $ChannelPartnerUser->country_id;
									$params['order']['sale_persons'] = $salesPersonString;
									$params['order']['type'] = getAllUserTypes()[$ChannelPartner->type]['short_name'];
									$params['order']['gst_number'] = $ChannelPartner->gst_number;
									$params['order']['qty'] = $value['qty'];
									$params['order']['item_name'] = $ProductInventory->product_brand . " " . $ProductInventory->code . " " . $ProductInventory->text . "";

									if (Config::get('app.env') == "local") {
										$params['to_email'] = $configrationForNotify['test_email'];
										//$params['bcc_email'] = $configrationForNotify['test_email_bcc'];
									}
									

									Mail::send('emails.notify_when_order', ['params' => $params], function ($m) use ($params) {
										$m->from($params['from_email'], $params['from_name']);
										$m->to($params['to_email'], $params['to_name'])->subject($params['subject']);
									});
								}
							}
						}
					}
				}
			}

			$fileName = 'order-' . $Order->id . '.pdf';
			$filePath = 's/order/' . $fileName;
			$this->savePDF($Order->id, $filePath);

			$params = array();

			$configrationForNotify = configrationForNotify();
			$params['from_email'] = $configrationForNotify['from_email'];
			$params['from_name'] = $configrationForNotify['from_name'];
			$params['to_email'] = $ChannelPartnerUser->email;
			$params['user_name'] = $ChannelPartnerUser->first_name . ' '  . $ChannelPartnerUser->last_name;
			$params['firm_name'] = $ChannelPartner->firm_name;
			$params['to_name'] = $configrationForNotify['to_name'];
			$params['subject'] = "New Order Placed - #" . $Order->id;
			$params['file_path'] = $filePath;
			$params['file_name'] = $fileName;
			$params['id'] = $Order->id;
			$params['order_by'] = Auth::user()->first_name . " " . Auth::user()->last_name;
			$params['order_date'] = convertDateTime($Order->created_at);
			$params['order_amount'] = $Order->total_mrp_minus_disocunt;

			if (Config::get('app.env') == "local") {
				$params['to_email'] = $configrationForNotify['test_email'];
				//$params['bcc_email'] = $configrationForNotify['test_email_bcc'];
			}

			Mail::send('emails.order_channel_partner', ['params' => $params], function ($m) use ($params) {
				$m->from($params['from_email'], $params['from_name']);
				$m->to($params['to_email'], $params['to_name'])->subject($params['subject']);
				$m->attach(public_path($params['file_path']), array(
					'as' => $params['file_name']
				));
			});

			// if ($ChannelPartnerUser->fcm_token != "") {

			$salesPersonsList = explode(",", $ChannelPartner->sale_persons);

			$totalUsers = array();

			foreach ($salesPersonsList as $ks => $vs) {

				if ($vs != "") {

					$mobileNotificationTitle = "New Order Place";
					$mobileNotificationMessage = "New Order Places " . $Order->id . " By " . $params['order_by'];
					$notificationUserids = getParentSalePersonsIds($vs);
					$notificationUserids[] = $vs;
					$totalUsers = array_merge($totalUsers, $notificationUserids);
				}
			}

			$totalUsers[] = $ChannelPartner->user_id;
			if (count($totalUsers) > 0) {

				$totalUsers = array_unique($totalUsers);
				$totalUsers = array_values($totalUsers);
				$UsersNotificationTokens = UsersNotificationTokens($notificationUserids);
				$mobileNotificationTitle = "New Order Place";
				//$mobileNotificationMessage = "New Order Places " . $Order->id . " By " . $params['order_by'];
				$mobileNotificationMessage = "New Order Places #" . $Order->id . " " . $ChannelPartner->firm_name . "  By " . $params['order_by'];;
				sendNotificationTOAndroid($mobileNotificationTitle, $mobileNotificationMessage, $UsersNotificationTokens,'Order',$Order);

				$parent_SalesUser = User::select('first_name', 'last_name', 'email')->whereIn('id', $notificationUserids)->orWhere('id', 1)->get();
				foreach ($parent_SalesUser as $keyS => $valueS) {

					$params['to_email'] = $valueS->email;
					$params['to_name'] = $valueS->first_name . ' ' .$valueS->first_name;
					$params['subject'] = "New Order Placed";
					$params['id'] = $Order->id;
	
					// TEMPLATE 15
					Mail::send('emails.order_sales_person', ['params' => $params], function ($m) use ($params) {
						$m->from($params['from_email'], $params['from_name']);
						$m->to($params['to_email'], $params['to_name'])->subject($params['subject']);
						$m->attach(public_path($params['file_path']), array(
							'as' => $params['file_name']));
					});
	
				}
			}


			if (is_file($filePath)) {
				unlink($filePath);
			}
		}

		$response = successRes("Successfully generated order #" . $Order->id);
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function invoiceList(Request $request)
	{

		$Order = Order::select('id')->find($request->order_id);
		if ($Order) {
			$data['title'] = "Invoice";
			$data['order_id'] = $Order->id;
			return view('orders/invoice', compact('data'));
		} else {
			return redirect()->route('dashboard');
		}
	}

	function invoiceListAjax(Request $request)
	{

		$searchColumns = array(
			0 => 'invoice.id',
			1 => 'invoice.invoice_number',
			2 => 'invoice.invoice_date',

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

		$isSalePerson = isSalePerson();

		
		$query = Invoice::query();
		$query->leftJoin('orders', 'invoice.order_id', '=', 'orders.id');
		$query->leftJoin('users', 'users.id', '=', 'orders.user_id');
		$query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
		$query->where('invoice.order_id', $request->order_id);
		$query->select($selectColumns);
		$isFilterApply = 0;
		if (isset($request->q)) {
			$isFilterApply = 1;
			$search_value = $request->q;
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

		foreach ($data as $key => $value) {

			$data[$key]['invoice_date'] = convertOrderDateTime($value['created_at'], "date");
			$data[$key]['invoice_time'] = convertOrderDateTime($value['created_at'], "time");
			$data[$key]['payment_mode_label'] = getPaymentModeName($value['payment_mode']);
			$data[$key]['order_by'] = $value['first_name'] . ' ' . $value['last_name'];
			$data[$key]['channel_partner'] = $value['firm_name'];

			$sale_persons = explode(",", $value['sale_persons']);
			$Users = User::select('first_name', 'last_name')->whereIn('id', $sale_persons)->get();
			$uiSalePerson = '';
			foreach ($Users as $kU => $vU) {
				$uiSalePerson .= $vU['first_name'] . ' ' . $vU['last_name'] . ',';
			}
			$data[$key]['sale_persons'] = $uiSalePerson;

			$data[$key]['gst_tax'] =  priceLable($value['gst_tax']);
			$data[$key]['total_payable'] =  priceLable($value['total_payable']);
			$data[$key]['status'] = getInvoiceStatus($value['status']);
			$data[$key]['invoice_file_link'] = getSpaceFilePath($value['invoice_file']);

		}

		$response = successRes();
		$response['data'] = $data;
		return response()->json($response, $response['status_code'])->header('Content-Type', 'application/json');
	}

	public function invoiceDetail(Request $request)
	{

		$isSalePerson = isSalePerson();

		$Order = Invoice::query();
		$Order->select('invoice.id', 'invoice.dispatch_detail', 'invoice.eway_bill', 'invoice.created_at', 'orders.gst_number', 'orders.payment_mode', 'orders.sale_persons', 'invoice.total_mrp_minus_disocunt', 'invoice.total_mrp', 'invoice.gst_tax', 'invoice.gst_percentage', 'invoice.delievery_charge', 'invoice.total_payable', 'orders.bill_address_line1', 'orders.bill_address_line2', 'orders.bill_pincode', 'orders.bill_state_id', 'orders.bill_city_id', 'orders.bill_country_id', 'orders.d_address_line1', 'orders.d_address_line2', 'orders.d_pincode', 'orders.d_state_id', 'orders.d_city_id', 'orders.d_country_id', 'channel_partner_detail.email as channel_partner_email', 'channel_partner_detail.phone_number as channel_partner_phone_number', 'channel_partner_detail.dialing_code as channel_partner_dialing_code', 'channel_partner_detail.first_name as channel_partner_first_name', 'channel_partner_detail.last_name as channel_partner_last_name', 'channel_partner.firm_name as channel_partner_firm_name', 'channel_partner.type as channel_partner_type', 'channel_partner.credit_limit as channel_partner_credit_limit', 'channel_partner.credit_days as channel_partner_credit_days', 'channel_partner.pending_credit as channel_partner_pending_credit', 'channel_partner.credit_days as channel_partner_credit_days');
		$Order->leftJoin('orders', 'invoice.order_id', '=', 'orders.id');
		$Order->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
		$Order->leftJoin('users as channel_partner_detail', 'channel_partner_detail.id', '=', 'channel_partner.user_id');
		$Order->where('invoice.id', $request->invoice_id);

		if ($isSalePerson == 1) {

			$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);

			$Order->where(function ($query) use ($childSalePersonsIds) {

				foreach ($childSalePersonsIds as $key => $value) {
					if ($key == 0) {
						$query->whereRaw('FIND_IN_SET("' . $value . '",orders.sale_persons)>0');
					} else {
						$query->orWhereRaw('FIND_IN_SET("' . $value . '",orders.sale_persons)>0');
					}
				}
			});

			// foreach ($childSalePersonsIds as $key => $value) {
			// 	if ($key == 0) {
			// 		$Order->whereRaw('FIND_IN_SET("' . $value . '",orders.sale_persons)>0');
			// 	} else {
			// 		$Order->orWhereRaw('FIND_IN_SET("' . $value . '",orders.sale_persons)>0');
			// 	}

			// }

		} else if (isChannelPartner(Auth::user()->type) != 0) {

			$Order->where('orders.channel_partner_user_id', Auth::user()->id);
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

	function export(Request $request)
	{

		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		$isAccountUser = isAccountUser();
		$isSalePerson = isSalePerson();
		$isChannelPartner = isChannelPartner(Auth::user()->type);

		$startDate = date('Y-m-d 00:00:00', strtotime($request->start_date));
		$startDate = date('Y-m-d H:i:s', strtotime($startDate . " -5 hours"));
		$startDate = date('Y-m-d H:i:s', strtotime($startDate . " -30 minutes"));

		$endDate = date('Y-m-d 23:59:59', strtotime($request->end_date));
		$endDate = date('Y-m-d H:i:s', strtotime($endDate . " -5 hours"));
		$endDate = date('Y-m-d H:i:s', strtotime($endDate . " -30 minutes"));

		if ($request->export_type == 0 || $request->export_type == 1) {

			$Order = Order::query();
			$Order->select('orders.id', 'channel_partner.firm_name', 'orders.created_at');
			$Order->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
			$Order->orderBy('orders.id', 'desc');
			$Order->where('orders.created_at', '>=', $startDate);
			$Order->where('orders.created_at', '<=', $endDate);

			if (isset($request->channel_partner_user_id) && is_array($request->channel_partner_user_id)) {

				$Order->whereIn('orders.channel_partner_user_id', $request->channel_partner_user_id);
			}

			if ($request->filter_type == 1) {

				if ($isSalePerson == 1) {

					$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
					$Order->where(function ($query) use ($childSalePersonsIds) {

						foreach ($childSalePersonsIds as $key => $value) {
							if ($key == 0) {
								$query->whereRaw('FIND_IN_SET("' . $value . '",orders.sale_persons)>0');
							} else {
								$query->orWhereRaw('FIND_IN_SET("' . $value . '",orders.sale_persons)>0');
							}
						}
					});

					$Order->where('channel_partner.reporting_company_id', Auth::user()->company_id);
				} else if ($isChannelPartner != 0) {

					$Order->where('orders.channel_partner_user_id', Auth::user()->id);
				}

				// ORDERS
				//$query->whereIn('orders.status', array(0, 1, 2));
			} else if ($request->filter_type == 2) {
				//SALES ORDERS
				$Order->whereIn('orders.status', array(0, 1, 2, 3));

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
			}

			if ($request->export_type == 0) {
				$Order->whereIn('orders.status', [0, 1, 2, 3]);
			} else if ($request->export_type == 1) {
				$Order->whereIn('orders.status', [0, 1, 2]);
			}

			$Order = $Order->get();

			$orderIds = array(0);

			foreach ($Order as $key => $value) {

				$orderIds[] = $value->id;
			}

			$orderItemsPendingQTY = array();

			$OrderItem = OrderItem::query();
			$OrderItem->select('order_items.pending_qty', 'order_items.order_id', 'order_items.product_inventory_id', 'order_items.order_id', 'order_items.qty');
			$OrderItem->orderBy('order_items.id', 'desc');
			$OrderItem->whereIn('order_items.order_id', $orderIds);

			if (isset($request->product_inventory_id) && is_array($request->product_inventory_id)) {

				$OrderItem->whereIn('order_items.product_inventory_id', $request->product_inventory_id);
			}
			if ($request->export_type != 0) {
				$OrderItem->where('order_items.pending_qty', '>', 0);
			}
			$OrderItems = $OrderItem->get();

			$productIds = array(0);

			$orderIds = array(0);

			foreach ($OrderItems as $key => $value) {
				$productIds[] = $value->product_inventory_id;

				if ($request->export_type == 0) {
					$orderItemsPendingQTY[$value->order_id . "_" . $value->product_inventory_id] = $value->qty;
				} else {
					$orderItemsPendingQTY[$value->order_id . "_" . $value->product_inventory_id] = $value->pending_qty;
				}

				$orderIds[] = $value->order_id;
			}

			if (isset($request->product_inventory_id) && is_array($request->product_inventory_id)) {

				$Order = Order::query();
				$Order->select('orders.id', 'channel_partner.firm_name', 'orders.created_at');
				$Order->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
				$Order->orderBy('orders.id', 'desc');
				$Order->whereIn('orders.id', $orderIds);
				$Order = $Order->get();
			}

			$productIds = array_unique($productIds);
			$productIds = array_values($productIds);

			$Products = ProductInventory::select('product_inventory.id', DB::raw('CONCAT(product_brand.name," ",product_code.name )  as text'));
			$Products->leftJoin('data_master as product_brand', 'product_brand.id', '=', 'product_inventory.product_brand_id');
			$Products->leftJoin('data_master as product_code', 'product_code.id', '=', 'product_inventory.product_code_id');
			$Products->whereIn('product_inventory.id', $productIds);
			$Products = $Products->get();

			$productIdText = array("");

			foreach ($productIds as $key => $value) {

				if ($key != 0) {

					foreach ($Products as $keyP => $valueP) {

						if ($value == $valueP->id) {
							$productIdText[] = $valueP->text;
							break;
						}
					}
				}
			}
			// echo '<pre>';
			// print_r($productIds);
			// print_r($productIdText);
			// die;

			$headers = array("Channel Partner/Products", "#orderId", "orderDate");
			foreach ($productIdText as $key => $value) {
				if ($key != 0) {
					$headers[] = $value;
				}
			}

			header('Content-Type: text/csv');
			header('Content-Disposition: attachment; filename="reports.csv"');
			$fp = fopen('php://output', 'wb');

			fputcsv($fp, $headers);

			foreach ($Order as $key => $value) {

				$created_at = convertOrderDateTime($value->created_at, "date");

				$lineVal = array(
					$value->firm_name,
					$value->id,
					$created_at,

				);

				foreach ($productIds as $keyP => $valeP) {

					if ($keyP != 0) {

						if (isset($orderItemsPendingQTY[$value->id . "_" . $valeP])) {

							$lineVal[] = $orderItemsPendingQTY[$value->id . "_" . $valeP];
						} else {
							$lineVal[] = "";
						}
					}
				}

				fputcsv($fp, $lineVal, ",");
			}

			fclose($fp);
		} else if ($request->export_type == 2) {

			$Order = Order::query();
			$Order->select('orders.id', 'channel_partner.firm_name', 'orders.created_at', 'users.first_name', 'users.last_name', 'orders.sale_persons', 'orders.total_mrp_minus_disocunt', 'orders.total_payable', 'orders.status', 'orders.sub_status');
			$Order->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
			$Order->leftJoin('users', 'users.id', '=', 'orders.user_id');
			$Order->orderBy('orders.id', 'desc');
			$Order->where('orders.created_at', '>=', $startDate);
			$Order->where('orders.created_at', '<=', $endDate);

			if (isset($request->channel_partner_user_id) && is_array($request->channel_partner_user_id)) {

				$Order->whereIn('orders.channel_partner_user_id', $request->channel_partner_user_id);
			}

			if ($request->filter_type == 1) {
				// ORDERS
				if ($isSalePerson == 1) {

					$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
					$Order->where(function ($query) use ($childSalePersonsIds) {

						foreach ($childSalePersonsIds as $key => $value) {
							if ($key == 0) {
								$query->whereRaw('FIND_IN_SET("' . $value . '",orders.sale_persons)>0');
							} else {
								$query->orWhereRaw('FIND_IN_SET("' . $value . '",orders.sale_persons)>0');
							}
						}
					});

					$Order->where('channel_partner.reporting_company_id', Auth::user()->company_id);
				} else if ($isChannelPartner != 0) {

					$Order->where('orders.channel_partner_user_id', Auth::user()->id);
				}
			} else if ($request->filter_type == 2) {
				//SALES ORDERS
				$Order->whereIn('orders.status', array(0, 1, 2, 3));

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
			}

			$Order = $Order->get();

			$headers = array("DATE", "ORDER ID", "ORDER BY", "CHANNEL PARTNER", "EXGST", "STATUS");

			header('Content-Type: text/csv');
			header('Content-Disposition: attachment; filename="reports.csv"');
			$fp = fopen('php://output', 'wb');
			fputcsv($fp, $headers);
			foreach ($Order as $key => $value) {

				//$sale_persons = explode(",", $value['sale_persons']);

				// $Users = User::select('users.first_name', 'users.last_name', 'users.type', 'users.phone_number', 'sales_hierarchy.code as sales_hierarchy_code')->leftJoin('sale_person', 'sale_person.id', '=', 'users.reference_id')->leftJoin('sales_hierarchy', 'sales_hierarchy.id', '=', 'sale_person.type')->whereIn('users.id', $sale_persons)->get();

				// $uiSalePerson = '';
				// foreach ($Users as $kU => $vU) {
				// 	$uiSalePerson .= $vU['first_name'] . ' ' . $vU['last_name'] . '|' . $vU['sales_hierarchy_code'] . '|PHONE:' . $vU['phone_number'] . ',';
				// }

				$paymentDetailEXGST = priceLable($value['total_mrp_minus_disocunt']);
				// $paymentDetailTotalPayable = priceLable($value['total_payable']);

				$subStatus = "";

				if ($value['status'] == 1 || $value['status'] == 2) {
					$subStatus = getInvoiceLable($value['sub_status']);
				}

				$status = getOrderLable($value['status']);
				if ($subStatus != "") {
					$status = $status . "-" . $subStatus;
				}

				$status = str_replace('<span class="badge badge-pill badge badge-soft-warning font-size-11">', "", $status);
				$status = str_replace('<span class="badge badge-pill badge badge-soft-info font-size-11">', "", $status);
				$status = str_replace('<span class="badge badge-pill badge badge-soft-orange font-size-11">', "", $status);
				$status = str_replace('<span class="badge badge-pill badge badge-soft-success font-size-11">', "", $status);
				$status = str_replace('<span class="badge badge-pill badge badge-soft-danger font-size-11">', "", $status);
				$status = str_replace('<span class="badge badge-pill badge bg-primary font-size-11">', "", $status);
				$status = str_replace('<span class="badge badge-pill badge-soft-info font-size-11">', "", $status);
				$status = str_replace('<span class="badge badge-pill badge-soft-success font-size-11">', "", $status);
				$status = str_replace('<span class="badge badge-pill badge-soft-dark font-size-11">', "", $status);
				$status = str_replace('</span>', "", $status);

				$lineVal = array(

					convertOrderDateTime($value['created_at'], "date"),
					$value->id,
					$value['first_name'] . '  ' . $value['last_name'],
					$value['firm_name'],
					$paymentDetailEXGST,
					$status,

				);

				fputcsv($fp, $lineVal, ",");
			}

			fclose($fp);
		}
	}
}
