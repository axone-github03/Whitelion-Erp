<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\ChannelPartner;
use App\Models\CityList;
use App\Models\Company;
use App\Models\CountryList;
use App\Models\DataMaster;
use App\Models\MainMaster;
use App\Models\MarketingChallan;
use App\Models\MarketingChallanItem;
use App\Models\MarketingOrder;
use App\Models\StateList;
use App\Models\User;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use PDF;

//use Session;

class ChallanManagementController extends Controller
{

	public function __construct()
	{

		$this->middleware(function ($request, $next) {

			$tabCanAccessBy = array(0, 1, 6, 7);

			if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
				return redirect()->route('dashboard');
			}

			return $next($request);
		});
	}

	public function raised(Request $request)
	{

		$data = array();
		$data['title'] = "Challan Raised";
		return view('marketing/invoice/raised', compact('data'));
	}

	public function packed(Request $request)
	{

		$data = array();
		$data['title'] = "MarketingChallan Packed";
		return view('marketing/invoice/packed', compact('data'));
	}

	public function dispatched(Request $request)
	{
		$data = array();

		$data['title'] = "MarketingChallan Dispatched";

		return view('marketing/invoice/dispatched', compact('data'));
	}

	function raisedAjax(Request $request)
	{

		// $isDispatcherUser = isMarketingDispatcherUser();
		// $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		// $isMarketingUser = isMarketingUser();

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
			14 => 'marketing_orders.created_at as order_date_time',
			15 => 'marketing_orders_challan.invoice_number',
			16 => 'marketing_orders.is_self',
			17 => 'users.phone_number',
			18 => 'channel_partner.type as channel_partner_type',
			19 => 'channel_partner_user.first_name as channel_partner_user_first_name',
			20 => 'channel_partner_user.last_name as channel_partner_user_last_name',
			21 => 'channel_partner_user.phone_number as channel_partner_user_phone_number',
			22 => 'marketing_orders_challan.invoice_file'

		);

		$query = MarketingChallan::query();
		$query->leftJoin('marketing_orders', 'marketing_orders_challan.order_id', '=', 'marketing_orders.id');
		$query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'marketing_orders.channel_partner_user_id');
		$query->leftJoin('users', 'users.id', '=', 'marketing_orders.user_id');
		$query->leftJoin('users as channel_partner_user', 'channel_partner_user.id', '=', 'marketing_orders.channel_partner_user_id');

		$query->where('marketing_orders_challan.status', 0);
		$query->where('marketing_orders_challan.invoice_number', '!=', "");

		// if ($isDispatcherUser == 1 || $isAdminOrCompanyAdmin == 1) {
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
		$query = MarketingChallan::query();
		$query->leftJoin('marketing_orders', 'marketing_orders_challan.order_id', '=', 'marketing_orders.id');
		$query->leftJoin('users', 'users.id', '=', 'marketing_orders.user_id');
		$query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'marketing_orders.channel_partner_user_id');
		$query->leftJoin('users as channel_partner_user', 'channel_partner_user.id', '=', 'marketing_orders.channel_partner_user_id');

		// if ($isDispatcherUser == 1 || $isAdminOrCompanyAdmin == 1) {
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
		$query->where('marketing_orders_challan.invoice_number', '!=', "");
		$query->where('marketing_orders_challan.status', 0);
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

		$channelPartner = getChannelPartners();

		foreach ($data as $key => $value) {

			$data[$key]['packed_sticker'] = "";

			// $MarketingChallanPacked = MarketingChallanPacked::select('sticker_box_no', 'sticker_pdf')->where('invoice_id', $value['id'])->orderBy('id', 'asc')->get();

			// $packedStickerUI = '<ul class="list-inline font-size-20 contact-links mb-0">';
			// foreach ($MarketingChallanPacked as $kU => $vU) {

			// 	$PackedPDF = getSpaceFilePath($vU['sticker_pdf']);
			// 	$packedStickerUI .= '<li class="list-inline-item px-0">';
			// 	$packedStickerUI .= '<a target="_blank" class="btn btn-outline-dark waves-effect waves-light" data-bs-toggle="tooltip" title="' . $vU['sticker_box_no'] . '" href="' . $PackedPDF . '" >' . $vU['sticker_box_no'] . '</a>';
			// 	$packedStickerUI .= '</li>';
			// }

			// $packedStickerUI .= '</ul>';
			// $data[$key]['packed_sticker'] = $packedStickerUI;

			$data[$key]['detail'] = '<h5 class="font-size-14 mb-1" data-bs-toggle="tooltip" title="' . convertOrderDateTime($value['order_date_time'], "date") . ' ' . convertOrderDateTime($value['order_date_time'], "time") . '"><a href="javascript: void(0);" class="text-dark">#MR' . $value['order_id'] . '</a></h5>
			    <p class="text-muted mb-0" data-bs-toggle="tooltip" title="CHALLAN NO">#' . ($value['invoice_number']) . '</p>
                <p class="text-muted mb-0" data-bs-toggle="tooltip"  data-bs-original-title="' . convertOrderDateTime($value['created_at'], "time") . '" >' . convertOrderDateTime($value['created_at'], "date") . '</p>';

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


			$data[$key]['order_by'] = '<p class="text-muted mb-0" data-bs-toggle="tooltip" title="' . $value['first_name'] . '  ' . $value['last_name'] . '">' . displayStringLenth($value['first_name'] . '  ' . $value['last_name'], 10) . '</p>';


			$data[$key]['channel_partner'] = "";
			if ($value['is_self'] == 0) {
				$data[$key]['channel_partner'] = '<p class="text-muted mb-0 text-center"
                data-bs-toggle="tooltip" title="' . $value['channel_partner_user_first_name'] . ' ' . $value['channel_partner_user_last_name'] . '&#013;&#013; PHONE:' . $value['channel_partner_user_phone_number'] . '" >' . displayStringLenth($value['firm_name'], 15) . '</p><p class="text-muted mb-0 text-center">' . $channelPartnerType . " " . $paymentMode . '</p>';
			} else {

				$data[$key]['channel_partner'] = '<p class="text-muted mb-0 text-center"
                data-bs-toggle="tooltip" title="' . $value['first_name'] . ' ' . $value['last_name'] . '&#013;&#013; PHONE:' . $value['phone_number'] . '" >' . displayStringLenth($value['first_name'] . ' ' . $value['last_name'], 15) . '</p><p class="text-muted mb-0 text-center">' . $channelPartnerType . " " . $paymentMode . '</p>';
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

			$data[$key]['payment_detail'] = '<p class="text-muted mb-0">GST&nbsp;&nbsp;&nbsp;&nbsp;&nbsp&nbsp;&nbsp;&nbsp;: <i class="fas fa-rupee-sign"></i> <span class="price-lable-font">' . priceLable($value['gst_tax']) . '</span></p>

			<p class="text-muted mb-0 ">Total &nbsp;&nbsp;&nbsp;&nbsp;&nbsp: <i class="fas fa-rupee-sign"></i> <span class="price-lable-font">' . priceLable($value['total_payable']) . '</span></p>
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

			$data[$key]['action_mark_packed'] = '<a onclick="doMarkAsPacked(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Mark As Packed"><span class="badge badge-pill badge-soft-info font-size-11"> MARK AS PACKED</span></a>';

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
			"draw" => intval($request['draw']),
			// for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
			"recordsTotal" => intval($recordsTotal),
			// total number of records
			"recordsFiltered" => intval($recordsFiltered),
			// total number of records after searching, if there is no searching then totalFiltered = totalData
			"data" => $data, // total data array

		);
		return $jsonData;
	}

	function generatePackedSticker($packedID, $fileName)
	{

		$MarketingChallanPacked = MarketingChallanPacked::find($packedID);

		$MarketingOrder = MarketingChallan::query();
		$MarketingOrder->select('marketing_orders_challan.id', 'marketing_orders_challan.dispatch_detail', 'marketing_orders_challan.eway_bill', 'marketing_orders_challan.created_at', 'marketing_orders.gst_number', 'marketing_orders.payment_mode', 'marketing_orders.sale_persons', 'marketing_orders_challan.total_mrp_minus_disocunt', 'marketing_orders_challan.total_mrp', 'marketing_orders_challan.gst_percentage', 'marketing_orders_challan.gst_tax', 'marketing_orders_challan.delievery_charge', 'marketing_orders_challan.total_payable', 'marketing_orders.bill_address_line1', 'marketing_orders.bill_address_line2', 'marketing_orders.bill_pincode', 'marketing_orders.bill_state_id', 'marketing_orders.bill_city_id', 'marketing_orders.bill_country_id', 'marketing_orders.d_address_line1', 'marketing_orders.d_address_line2', 'marketing_orders.d_pincode', 'marketing_orders.d_state_id', 'marketing_orders.d_city_id', 'marketing_orders.d_country_id', 'channel_partner_detail.email as channel_partner_email', 'channel_partner_detail.phone_number as channel_partner_phone_number', 'channel_partner_detail.dialing_code as channel_partner_dialing_code', 'channel_partner_detail.first_name as channel_partner_first_name', 'channel_partner_detail.last_name as channel_partner_last_name', 'channel_partner.firm_name as channel_partner_firm_name', 'channel_partner.type as channel_partner_type', 'channel_partner.credit_limit as channel_partner_credit_limit', 'channel_partner.credit_days as channel_partner_credit_days', 'channel_partner.pending_credit as channel_partner_pending_credit', 'channel_partner.credit_days as channel_partner_credit_days', 'marketing_orders.channel_partner_user_id');
		$MarketingOrder->leftJoin('orders', 'marketing_orders_challan.order_id', '=', 'marketing_orders.id');
		$MarketingOrder->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'marketing_orders.channel_partner_user_id');
		$MarketingOrder->leftJoin('users as channel_partner_detail', 'channel_partner_detail.id', '=', 'channel_partner.user_id');
		$MarketingOrder->where('marketing_orders_challan.id', $MarketingChallanPacked->invoice_id);
		$MarketingOrder = $MarketingOrder->first();

		if ($MarketingOrder) {

			$BCityList = CityList::find($MarketingOrder['bill_city_id']);
			$MarketingOrder['bill_city_name'] = "";
			if ($BCityList) {
				$MarketingOrder['bill_city_name'] = $BCityList->name;
			}

			$BStateList = StateList::find($MarketingOrder['bill_state_id']);
			$MarketingOrder['bill_state_name'] = "";
			if ($BStateList) {
				$MarketingOrder['bill_state_name'] = $BStateList->name;
			}

			$BCountryList = CountryList::find($MarketingOrder['bill_country_id']);
			$MarketingOrder['bill_country_name'] = "";
			if ($BCountryList) {
				$MarketingOrder['bill_country_name'] = $BCountryList->name;
			}

			$DCityList = CityList::find($MarketingOrder['d_city_id']);
			$MarketingOrder['d_city_name'] = "";
			if ($DCityList) {
				$MarketingOrder['d_city_name'] = $DCityList->name;
			}

			$DStateList = StateList::find($MarketingOrder['d_state_id']);
			$MarketingOrder['d_state_name'] = "";
			if ($DStateList) {
				$MarketingOrder['d_state_name'] = $DStateList->name;
			}

			$DCountryList = CountryList::find($MarketingOrder['d_country_id']);
			$MarketingOrder['d_country_name'] = "";
			if ($DCountryList) {
				$MarketingOrder['d_country_name'] = $DCountryList->name;
			}

			$MarketingOrder['payment_mode_lable'] = getPaymentModeName($MarketingOrder['payment_mode']);

			$channelPartner = ChannelPartner::select('reporting_manager_id', 'reporting_company_id')->where('user_id', $MarketingOrder['channel_partner_user_id'])->first();
			if ($channelPartner->reporting_manager_id != 0) {
				$channelPartnerDetail = ChannelPartner::select('channel_partner.firm_name', 'channel_partner_detail.dialing_code', 'channel_partner_detail.phone_number', 'channel_partner_detail.address_line1', 'channel_partner_detail.address_line2', 'channel_partner_detail.pincode', 'channel_partner_detail.country_id', 'channel_partner_detail.state_id', 'channel_partner_detail.city_id')->leftJoin('users as channel_partner_detail', 'channel_partner_detail.id', '=', 'channel_partner.user_id')->where('channel_partner.user_id', $channelPartner->reporting_manager_id)->first();

				$MarketingOrder['from_compnay_name'] = $channelPartnerDetail->firm_name;
				$MarketingOrder['from_phone_number'] = $channelPartnerDetail->dialing_code . " " . $channelPartnerDetail->phone_number;
				$MarketingOrder['from_address_line1'] = $channelPartnerDetail->address_line1;
				$MarketingOrder['from_address_line2'] = $channelPartnerDetail->address_line2;
				$MarketingOrder['from_pincode'] = $channelPartnerDetail->pincode;

				$MarketingOrder['from_country_name'] = "";
				$CountryList = CountryList::find($channelPartnerDetail->country_id);
				if ($CountryList) {
					$MarketingOrder['from_country_name'] = $CountryList->name;
				}

				$CityList = CityList::find($channelPartnerDetail->city_id);
				$MarketingOrder['from_city_name'] = "";
				if ($CityList) {
					$MarketingOrder['from_city_name'] = $CityList->name;
				}

				$StateList = StateList::find($channelPartnerDetail->state_id);
				$MarketingOrder['from_state_name'] = "";
				if ($StateList) {
					$MarketingOrder['from_state_name'] = $StateList->name;
				}
			} else {
				$Company = Company::find($channelPartner->reporting_company_id);

				$MarketingOrder['from_pincode'] = $Company->pincode;

				$MarketingOrder['from_compnay_name'] = $Company->name;
				$MarketingOrder['from_phone_number'] = $Company->phone_number;
				$MarketingOrder['from_address_line1'] = $Company->address_line1;
				$MarketingOrder['from_address_line2'] = $Company->address_line2;

				$MarketingOrder['from_country_name'] = "";
				$CountryList = CountryList::find($Company->country_id);
				if ($CountryList) {
					$MarketingOrder['from_country_name'] = $CountryList->name;
				}

				$CityList = CityList::find($Company->city_id);
				$MarketingOrder['from_city_name'] = "";
				if ($CityList) {
					$MarketingOrder['from_city_name'] = $CityList->name;
				}

				$StateList = StateList::find($Company->state_id);
				$MarketingOrder['from_state_name'] = "";
				if ($StateList) {
					$MarketingOrder['from_state_name'] = $StateList->name;
				}
			}

			$MarketingChallanItem = MarketingChallanPackedItem::query();
			$MarketingChallanItem->select('invoice_packed_items.id', 'invoice_packed_items.qty', 'marketing_product_inventory.image as product_image', 'product_brand.name as product_brand_name', 'product_code.name as product_code_name');
			$MarketingChallanItem->leftJoin('order_items', 'invoice_packed_items.order_item_id', '=', 'marketing_order_items.id');
			$MarketingChallanItem->leftJoin('marketing_product_inventory', 'marketing_product_inventory.id', '=', 'marketing_order_items.marketing_product_inventory_id');
			$MarketingChallanItem->leftJoin('data_master as product_brand', 'product_brand.id', '=', 'marketing_product_inventory.product_brand_id');
			$MarketingChallanItem->leftJoin('data_master as product_code', 'product_code.id', '=', 'marketing_product_inventory.product_code_id');
			$MarketingChallanItem->where('invoice_packed_items.invoice_packed_id', $packedID);
			$MarketingChallanItem->orderBy('id', 'desc');
			$MarketingChallanItem = $MarketingChallanItem->get();

			$data = $MarketingOrder;
			if ($MarketingChallanPacked->total_weight == 0.00) {
				$totalWeight = "_________";
			} else {
				$totalWeight = $MarketingChallanPacked->total_weight;
			}

			$data['invoice_packed_items'] = $MarketingChallanItem;

			$data['sticker_box_no'] = $MarketingChallanPacked->sticker_box_no;
			$data['packed_date'] = $MarketingChallanPacked->packed_date;
			$data['total_weight'] = $totalWeight;
			$data['department_name'] = $MarketingChallanPacked->department_name == "" ? "______________" : $MarketingChallanPacked->department_name;
			$customPaper = array(0, 0, 200, 75);
			$PDF = PDF::loadView('invoice/packed_pdf', compact('data'))->setPaper($customPaper);
			//$PDF->stream();
			return $PDF->save($fileName);
		}
	}

	public function markAsPacked(Request $request)
	{

		$validator = Validator::make($request->all(), [
			'id' => ['required'],

		]);

		if ($validator->fails()) {

			$response = array();
			$response['status'] = 0;
			$response['msg'] = "The request could not be understood by the server due to malformed syntax";
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();

			return response()->json($response)->header('Content-Type', 'application/json');
		} else {

			$MarketingChallan = MarketingChallan::find($request->id);
			if ($MarketingChallan) {

				$isDispatcherUser = isMarketingDispatcherUser();
				$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
				$MarketingOrder = MarketingOrder::query();
				$MarketingOrder->select('marketing_orders.*', 'channel_partner.reporting_company_id');
				$MarketingOrder->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'marketing_orders.channel_partner_user_id');

				// if ($isDispatcherUser == 1 || $isAdminOrCompanyAdmin == 1) {
				// 	if (Auth::user()->parent_id != 0) {

				// 		$parent = ChannelPartner::where('user_id', Auth::user()->parent_id)->first();

				// 		$MarketingOrder->where('channel_partner.reporting_manager_id', $parent->user_id);

				// 	} else {

				// 		$MarketingOrder->where('channel_partner.reporting_company_id', Auth::user()->company_id);
				// 		$MarketingOrder->where('channel_partner.reporting_manager_id', 0);

				// 	}

				// } else if (isChannelPartner(Auth::user()->type) != 0) {

				// 	$MarketingOrder->where('channel_partner.reporting_manager_id', Auth::user()->id);

				// }

				$MarketingOrder = $MarketingOrder->where('marketing_orders.id', $MarketingChallan->order_id)->first();
				if ($MarketingOrder) {

					$MarketingChallan->status = 1;
					$MarketingChallan->save();

					$lastMarketingChallan = MarketingChallan::select('id')->where('order_id', $MarketingOrder->id)->orderBy('id', 'desc')->first();
					if ($lastMarketingChallan && $lastMarketingChallan->id == $MarketingChallan->id) {

						$MarketingOrder->sub_status = 1;
						$MarketingOrder->save();
					}

					$response = successRes("Successfully invoice status");
				} else {
					$response = errorRes("Invalid marketing order");
				}
			} else {
				$response = errorRes("Invalid marketing challan");
			}
		}
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function packedAjax(Request $request)
	{

		$isDispatcherUser = isMarketingDispatcherUser();
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
			14 => 'marketing_orders.created_at as order_date_time',
			15 => 'marketing_orders_challan.invoice_number',
			16 => 'marketing_orders.is_self',
			17 => 'users.phone_number',
			18 => 'channel_partner.type as channel_partner_type',
			19 => 'channel_partner_user.first_name as channel_partner_user_first_name',
			20 => 'channel_partner_user.last_name as channel_partner_user_last_name',
			21 => 'channel_partner_user.phone_number as channel_partner_user_phone_number',

		);

		$query = MarketingChallan::query();
		$query->leftJoin('marketing_orders', 'marketing_orders_challan.order_id', '=', 'marketing_orders.id');
		$query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'marketing_orders.channel_partner_user_id');
		$query->leftJoin('users', 'users.id', '=', 'marketing_orders.user_id');
		$query->leftJoin('users as channel_partner_user', 'channel_partner_user.id', '=', 'marketing_orders.channel_partner_user_id');
		$query->where('marketing_orders_challan.status', 1);

		// if ($isDispatcherUser == 1 || $isAdminOrCompanyAdmin == 1) {
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
		$query = MarketingChallan::query();
		$query->leftJoin('marketing_orders', 'marketing_orders_challan.order_id', '=', 'marketing_orders.id');
		$query->leftJoin('users', 'users.id', '=', 'marketing_orders.user_id');
		$query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'marketing_orders.channel_partner_user_id');
		$query->leftJoin('users as channel_partner_user', 'channel_partner_user.id', '=', 'marketing_orders.channel_partner_user_id');

		$query->where('marketing_orders_challan.status', 1);
		// if ($isDispatcherUser == 1 || $isAdminOrCompanyAdmin == 1) {
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

		$channelPartner = getChannelPartners();

		foreach ($data as $key => $value) {

			$data[$key]['packed_sticker'] = "";

			// $MarketingChallanPacked = MarketingChallanPacked::select('sticker_box_no', 'sticker_pdf')->where('invoice_id', $value['id'])->orderBy('id', 'asc')->get();

			// $packedStickerUI = '<ul class="list-inline font-size-20 contact-links mb-0">';
			// foreach ($MarketingChallanPacked as $kU => $vU) {

			// 	$PackedPDF = getSpaceFilePath($vU['sticker_pdf']);
			// 	$packedStickerUI .= '<li class="list-inline-item px-0">';
			// 	$packedStickerUI .= '<a target="_blank" class="btn btn-outline-dark waves-effect waves-light" data-bs-toggle="tooltip" title="' . $vU['sticker_box_no'] . '" href="' . $PackedPDF . '" >' . $vU['sticker_box_no'] . '</a>';
			// 	$packedStickerUI .= '</li>';
			// }

			// $packedStickerUI .= '</ul>';
			// $data[$key]['packed_sticker'] = $packedStickerUI;
			// $noOFBox = count($MarketingChallanPacked);

			$data[$key]['detail'] = '<h5 class="font-size-14 mb-1" data-bs-toggle="tooltip" title="' . convertOrderDateTime($value['order_date_time'], "date") . ' ' . convertOrderDateTime($value['order_date_time'], "time") . '"><a href="javascript: void(0);" class="text-dark">#MR' . $value['order_id'] . '</a></h5>
			    <p class="text-muted mb-0" data-bs-toggle="tooltip" title="CHALLAN NO">#' . ($value['invoice_number']) . '</p>
                <p class="text-muted mb-0" data-bs-toggle="tooltip"  data-bs-original-title="' . convertOrderDateTime($value['created_at'], "time") . '" >' . convertOrderDateTime($value['created_at'], "date") . '</p>';



			// $paymentMode = "";

			// $paymentMode = '<span class="badge rounded-pill badge-soft-dark font-size-11">' . getPaymentModeName($value['payment_mode']) . '</span>';
			$paymentMode = "";
			$channelPartnerType = "";

			//$paymentMode = '<span class="badge rounded-pill badge-soft-dark font-size-11">' . getPaymentModeName($value['payment_mode']) . '</span>';

			// $data[$key]['order_by'] = '<p class="text-muted mb-0">' . $value['first_name'] . '  ' . $value['last_name'] . '</p>';
			// $data[$key]['channel_partner'] = '<p class="text-muted mb-0">' . $value['firm_name'] . '</p><p class="text-muted mb-0">' . $paymentMode . '</p>';



			$paymentMode = "";
			$channelPartnerType = "";

			//$paymentMode = getPaymentLable($value['payment_mode']);

			if ($value['is_self'] == 0) {
				$channelPartnerType = '<span class="badge rounded-pill badge-soft-dark font-size-11">' . $channelPartner[$value['channel_partner_type']]['short_name'] . '</span>';
			} else {
				$channelPartnerType = '<span class="badge rounded-pill badge-soft-dark font-size-11">SELF</p>';
			}


			$data[$key]['order_by'] = '<p class="text-muted mb-0" data-bs-toggle="tooltip" title="' . $value['first_name'] . '  ' . $value['last_name'] . '">' . displayStringLenth($value['first_name'] . '  ' . $value['last_name'], 10) . '</p>';
			$data[$key]['channel_partner'] = "";
			if ($value['is_self'] == 0) {
				$data[$key]['channel_partner'] = '<p class="text-muted mb-0 text-center"
                data-bs-toggle="tooltip" title="' . $value['channel_partner_user_first_name'] . ' ' . $value['channel_partner_user_last_name'] . '&#013;&#013; PHONE:' . $value['channel_partner_user_phone_number'] . '" >' . displayStringLenth($value['firm_name'], 15) . '</p><p class="text-muted mb-0 text-center">' . $channelPartnerType . " " . $paymentMode . '</p>';
			} else {

				$data[$key]['channel_partner'] = '<p class="text-muted mb-0 text-center"
                data-bs-toggle="tooltip" title="' . $value['first_name'] . ' ' . $value['last_name'] . '&#013;&#013; PHONE:' . $value['phone_number'] . '" >' . displayStringLenth($value['first_name'] . ' ' . $value['last_name'], 15) . '</p><p class="text-muted mb-0 text-center">' . $channelPartnerType . " " . $paymentMode . '</p>';
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

			$data[$key]['payment_detail'] = '<p class="text-muted mb-0">GST&nbsp;&nbsp;&nbsp;&nbsp;&nbsp&nbsp;&nbsp;&nbsp;: <i class="fas fa-rupee-sign"></i> <span class="price-lable-font">' . priceLable($value['gst_tax']) . '</span></p>

			<p class="text-muted mb-0 ">Total &nbsp;&nbsp;&nbsp;&nbsp;&nbsp: <i class="fas fa-rupee-sign"></i> <span class="price-lable-font">' . priceLable($value['total_payable']) . '</span></p>
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

			$data[$key]['action_mark_dispatch'] = '<a onclick="doMarkAsDispatch(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Mark As Packed"><span class="badge badge-pill badge-soft-success font-size-11"> MARK AS DISPATCH</span></a>';

			$uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';

			$uiAction .= '<li class="list-inline-item px-2">';
			$uiAction .= '<a onclick="ViewInvoice(\'' . $value['id'] . '\')" href="javascript: void(0);" title="View"><i class="mdi mdi-eye"></i></a>';
			$uiAction .= '</li>';

			// $uiAction .= '<li class="list-inline-item px-2">';
			// $uiAction .= '<a target="_blank" href="' . getSpaceFilePath($value['invoice_file']) . '" title="PDF"><i class="bx bxs-file-pdf"></i></a>';
			// $uiAction .= '</li>';

			$uiAction .= '</ul>';
			$data[$key]['action'] = $uiAction;
		}

		$jsonData = array(
			"draw" => intval($request['draw']),
			// for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
			"recordsTotal" => intval($recordsTotal),
			// total number of records
			"recordsFiltered" => intval($recordsFiltered),
			// total number of records after searching, if there is no searching then totalFiltered = totalData
			"data" => $data, // total data array

		);
		return $jsonData;
	}

	public function searchCourier(Request $request)
	{

		$DataMaster = array();

		$MainMaster = MainMaster::select('id')->where('code', 'COURIER_SERVICE')->first();
		if ($MainMaster) {

			$DataMaster = array();
			$DataMaster = DataMaster::select('id', 'name as text');
			$DataMaster->where('main_master_id', $MainMaster->id);
			$DataMaster->where('name', 'like', "%" . $request->q . "%");
			$DataMaster->limit(5);
			$DataMaster = $DataMaster->get();
		}

		$response = array();
		$response['results'] = $DataMaster;
		$response['pagination']['more'] = false;
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function markAsDispatch(Request $request)
	{

		$validator = Validator::make($request->all(), [
			'invoice_id' => ['required'],
			'invoice_track_id' => ['required'],
			'invoice_box_number' => ['required'],
			'invoice_courier_service_id' => ['required'],

		]);

		if ($validator->fails()) {

			$response = array();
			$response['status'] = 0;
			$response['msg'] = "The request could not be understood by the server due to malformed syntax";
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();

			return response()->json($response)->header('Content-Type', 'application/json');
		} else {

			$MarketingChallan = MarketingChallan::find($request->invoice_id);

			if ($MarketingChallan) {

				$isDispatcherUser = isMarketingDispatcherUser();
				$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();

				// $MarketingOrder = MarketingOrder::query();
				// $MarketingOrder->select('marketing_orders.*', 'channel_partner.reporting_company_id');
				// $MarketingOrder->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'marketing_orders.channel_partner_user_id');
				$MarketingOrder = MarketingOrder::query();
				$MarketingOrder->select('marketing_orders.*', 'channel_partner.reporting_company_id');
				$MarketingOrder->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'marketing_orders.channel_partner_user_id');

				/*
							
							// if ($isDispatcherUser == 1 || $isAdminOrCompanyAdmin == 1) {
							// 	if (Auth::user()->parent_id != 0) {

							// 		$parent = ChannelPartner::where('user_id', Auth::user()->parent_id)->first();

							// 		$MarketingOrder->where('channel_partner.reporting_manager_id', $parent->user_id);

							// 	} else {

							// 		$MarketingOrder->where('channel_partner.reporting_company_id', Auth::user()->company_id);
							// 		$MarketingOrder->where('channel_partner.reporting_manager_id', 0);

							// 	}

							// } else if (isChannelPartner(Auth::user()->type) != 0) {

							// 	$MarketingOrder->where('channel_partner.reporting_manager_id', Auth::user()->id);

							// }
							*/

				$MarketingOrder = $MarketingOrder->where('marketing_orders.id', $MarketingChallan->order_id)->first();

				if ($MarketingOrder) {

					$MarketingOrderItem = MarketingChallanItem::query();
					$MarketingOrderItem->select('marketing_orders_challan_items.id', 'marketing_orders_challan_items.qty', 'marketing_orders_challan_items.total_mrp', 'marketing_product_inventory.image as product_image', 'product_code.name as product_code_name', 'marketing_order_items.mrp', 'marketing_order_items.discount_percentage', 'marketing_order_items.weight', 'marketing_product_inventory.has_specific_code', 'marketing_product_inventory.quantity as availablestock', 'marketing_product_inventory.id as productid', 'marketing_product_inventory.description as productdescription');
					$MarketingOrderItem->leftJoin('marketing_order_items', 'marketing_orders_challan_items.order_item_id', '=', 'marketing_order_items.id');
					$MarketingOrderItem->leftJoin('marketing_product_inventory', 'marketing_product_inventory.id', '=', 'marketing_order_items.marketing_product_inventory_id');
					//$MarketingOrderItem->leftJoin('data_master as product_group', 'product_group.id', '=', 'marketing_product_inventory.marketing_product_group_id');
					$MarketingOrderItem->leftJoin('data_master as product_code', 'product_code.id', '=', 'marketing_product_inventory.marketing_product_code_id');
					$MarketingOrderItem->where('orders_challan_id', $MarketingChallan->id);
					$MarketingOrderItem->orderBy('id', 'desc');
					$MarketingOrderItem = $MarketingOrderItem->get();

					$shouldBeSpecificode = array();

					foreach ($MarketingOrderItem as $key => $value) {

						if ($value->qty > $value->availablestock) {
							$response = errorRes("Marketing Inventary #" . $value->productid . " (" . $value->productdescription . ") out of stock");
							return response()->json($response)->header('Content-Type', 'application/json');
						} else {
							if ($value->has_specific_code == 1) {

								$shouldBeSpecificode[] = $value->id;
							}
						}
					}


					if (count($shouldBeSpecificode) > 0) {

						foreach ($shouldBeSpecificode as $key => $value) {

							if (!isset($request['has_specific_code'][$value]) || $request['has_specific_code'][$value] == "") {

								$response = errorRes("Has no specific code");
								return $response;
							}

							$MarketingChallanItem = MarketingChallanItem::find($value);
							if ($MarketingChallanItem) {

								$MarketingChallanItem->specific_code = $request['has_specific_code'][$value];
								$MarketingChallanItem->save();
							} else {

								$response = errorRes("Has no specific code");
								return $response;
							}
						}
					}

					$uploadedFile1 = [];
					$uploadedFile2 = "";

					if ($request->hasFile('invoice_dispatch_detail')) {

						$folderPathofFile = '/s/marketing-dispatch-detail';
						if (!is_dir(public_path($folderPathofFile))) {
							mkdir(public_path($folderPathofFile));
						}

						$folderPathofFile = '/s/marketing-dispatch-detail/' . date('Y');

						if (!is_dir(public_path($folderPathofFile))) {

							mkdir(public_path($folderPathofFile));
						}

						$folderPathofFile = '/s/marketing-dispatch-detail/' . date('Y') . "/" . date('m');
						if (!is_dir(public_path($folderPathofFile))) {
							mkdir(public_path($folderPathofFile));
						}

						foreach ($request->file('invoice_dispatch_detail') as $keyF => $valueF) {

							$fileObject1 = $valueF;
							$extension = $fileObject1->getClientOriginalExtension();
							$fileTypes = acceptFileTypes('order.dispatch.detail', 'server');
							if (in_array(strtolower($extension), $fileTypes)) {

								$fileName1 = time() . mt_rand(10000, 99999) . '.' . $extension;

								$destinationPath = public_path($folderPathofFile);
								$fileObject1->move($destinationPath, $fileName1);

								if (File::exists(public_path($folderPathofFile . "/" . $fileName1))) {

									$uploadedFileTemp = $folderPathofFile . "/" . $fileName1;
									$spaceUploadResponse = uploadFileOnSpaces(public_path($uploadedFileTemp), $uploadedFileTemp);
									if ($spaceUploadResponse != 1) {
										$uploadedFileTemp = "";
									} else {
										unlink(public_path($uploadedFileTemp));
										$uploadedFile1[] = $uploadedFileTemp;
									}
								}
							}
						}
					}

					if ($request->hasFile('invoice_eway_bill')) {

						$folderPathofFile = '/s/marketing-eway-bill';
						if (!is_dir(public_path($folderPathofFile))) {
							mkdir(public_path($folderPathofFile));
						}

						$folderPathofFile = '/s/marketing-eway-bill/' . date('Y');

						if (!is_dir(public_path($folderPathofFile))) {

							mkdir(public_path($folderPathofFile));
						}

						$folderPathofFile = '/s/marketing-eway-bill/' . date('Y') . "/" . date('m');
						if (!is_dir(public_path($folderPathofFile))) {
							mkdir(public_path($folderPathofFile));
						}

						$fileObject1 = $request->file('invoice_eway_bill');
						$extension = $fileObject1->getClientOriginalExtension();
						$fileTypes = acceptFileTypes('order.eway.bill', 'server');
						if (in_array(strtolower($extension), $fileTypes)) {

							$fileName1 = time() . mt_rand(10000, 99999) . '.' . $extension;

							$destinationPath = public_path($folderPathofFile);

							$fileObject1->move($destinationPath, $fileName1);

							if (File::exists(public_path($folderPathofFile . "/" . $fileName1))) {

								$uploadedFile2 = $folderPathofFile . "/" . $fileName1;

								$spaceUploadResponse = uploadFileOnSpaces(public_path($uploadedFile2), $uploadedFile2);
								if ($spaceUploadResponse != 1) {
									$uploadedFile2 = "";
								} else {
									unlink(public_path($uploadedFile2));
								}
							}
						}
					}
					// if (count($uploadedFile1) > 0) {

					$MarketingChallan->eway_bill = $uploadedFile2;
					$MarketingChallan->dispatch_detail = implode(",", $uploadedFile1);
					$MarketingChallan->track_id = $request->invoice_track_id;
					$MarketingChallan->box_number = $request->invoice_box_number;
					$MarketingChallan->courier_service_id = $request->invoice_courier_service_id;
					$MarketingChallan->status = 2;

					$MarketingChallan->save();

					/*
								   // $lastMarketingChallan = MarketingChallan::select('id')->where('order_id', $MarketingOrder->id)->orderBy('id', 'desc')->first();
								   // if ($lastMarketingChallan && $lastMarketingChallan->id == $MarketingChallan->id) {

								   // 	$hasMarketingOrderItemPending = MarketingOrderItem::where('order_id', $MarketingOrder->id)->where('pending_qty', '!=', 0)->first();
								   // 	if (!$hasMarketingOrderItemPending) {

								   // 		$MarketingOrder->status = 3;

								   // 	} else {

								   // 		$MarketingOrder->status = 2;

								   // 	}

								   // 	$MarketingOrder->sub_status = 2;
								   // 	$MarketingOrder->save();

								   // }
								   */
					$response = successRes("Successfully dispatched invoice");
					$response['dispatch_pdf'] = "";
					/*
								   // $fileName = 'dispatch-' . time() . "-" . $MarketingChallan->id . '.pdf';

								   // $folderPath = '/s/dispatch-pdf/' . date('Y') . "/" . date('m');

								   // if (!is_dir("s/dispatch-pdf")) {
								   // 	mkdir("s/dispatch-pdf");
								   // }

								   // if (!is_dir("s/dispatch-pdf/" . date('Y') . "/" . date('m'))) {
								   // 	mkdir("s/dispatch-pdf/" . date('Y'));
								   // }

								   // if (!is_dir("s/dispatch-pdf/" . date('Y') . "/" . date('m'))) {
								   // 	mkdir("s/dispatch-pdf/" . date('Y') . "/" . date('m'));
								   // }

								   // $filePath = $folderPath . "/" . $fileName;

								   // $this->generateDispatchPDF($MarketingChallan->id, public_path($filePath));

								   // $spaceUploadResponse = uploadFileOnSpaces(public_path($filePath), $filePath);
								   // if ($spaceUploadResponse == 1) {

								   // 	$MarketingChallan->dispatch_pdf = $filePath;
								   // 	$MarketingChallan->save();
								   // 	unlink(public_path($filePath));
								   // 	$response['dispatch_pdf'] = getSpaceFilePath($filePath);

								   // }

								   // } else {

								   // 	$response = errorRes("Invalid dispatch detail");

								   // }
								   */
				} else {
					$response = errorRes("Invalid order");
				}
			} else {
				$response = errorRes("Invalid invoice");
			}
		}
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	function testPDF()
	{

		$MarketingChallanList = MarketingChallanPacked::get();

		foreach ($MarketingChallanList as $IP => $VP) {

			$packedID = $VP->id;

			$MarketingChallan = MarketingChallanPacked::find($packedID);

			$fileName = 'box-' . time() . "-" . $MarketingChallan->id . '.pdf';

			$folderPath = '/s/box-pdf/' . date('Y') . "/" . date('m');

			if (!is_dir("s/box-pdf")) {
				mkdir("s/box-pdf");
			}

			if (!is_dir("s/box-pdf/" . date('Y') . "/" . date('m'))) {
				mkdir("s/box-pdf/" . date('Y'));
			}

			if (!is_dir("s/box-pdf/" . date('Y') . "/" . date('m'))) {
				mkdir("s/box-pdf/" . date('Y') . "/" . date('m'));
			}

			$filePath = $folderPath . "/" . $fileName;

			$this->generatePackedSticker($MarketingChallan->id, public_path($filePath));

			$spaceUploadResponse = uploadFileOnSpaces(public_path($filePath), $filePath);
			if ($spaceUploadResponse == 1) {

				$MarketingChallan->sticker_pdf = $filePath;
				$MarketingChallan->save();
				unlink(public_path($filePath));
				$response['sticker_pdf'] = getSpaceFilePath($filePath);
			}
		}
	}

	function generateDispatchPDF($invoiceID, $fileName)
	{

		$data = array();

		// $invoiceID = 876;

		$MarketingChallan = MarketingChallan::query();
		$MarketingChallan->select('marketing_orders_challan.id', 'marketing_orders_challan.dispatch_detail', 'marketing_orders_challan.eway_bill', 'marketing_orders_challan.created_at', 'marketing_orders.gst_number', 'marketing_orders.payment_mode', 'marketing_orders.sale_persons', 'marketing_orders_challan.total_mrp_minus_disocunt', 'marketing_orders_challan.total_mrp', 'marketing_orders_challan.gst_percentage', 'marketing_orders_challan.gst_tax', 'marketing_orders_challan.delievery_charge', 'marketing_orders_challan.total_payable', 'marketing_orders.bill_address_line1', 'marketing_orders.bill_address_line2', 'marketing_orders.bill_pincode', 'marketing_orders.bill_state_id', 'marketing_orders.bill_city_id', 'marketing_orders.bill_country_id', 'marketing_orders.d_address_line1', 'marketing_orders.d_address_line2', 'marketing_orders.d_pincode', 'marketing_orders.d_state_id', 'marketing_orders.d_city_id', 'marketing_orders.d_country_id', 'channel_partner_detail.email as channel_partner_email', 'channel_partner_detail.phone_number as channel_partner_phone_number', 'channel_partner_detail.dialing_code as channel_partner_dialing_code', 'channel_partner_detail.first_name as channel_partner_first_name', 'channel_partner_detail.last_name as channel_partner_last_name', 'channel_partner.firm_name as channel_partner_firm_name', 'channel_partner.type as channel_partner_type', 'channel_partner.credit_limit as channel_partner_credit_limit', 'channel_partner.credit_days as channel_partner_credit_days', 'channel_partner.pending_credit as channel_partner_pending_credit', 'channel_partner.credit_days as channel_partner_credit_days', 'channel_partner.reporting_manager_id as channel_partner_reporting_manager_id', 'channel_partner.reporting_company_id as channel_partner_reporting_company_id', 'courier_service_id', 'track_id');
		$MarketingChallan->leftJoin('orders', 'marketing_orders_challan.order_id', '=', 'marketing_orders.id');
		$MarketingChallan->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'marketing_orders.channel_partner_user_id');
		$MarketingChallan->leftJoin('users as channel_partner_detail', 'channel_partner_detail.id', '=', 'channel_partner.user_id');
		$MarketingChallan->where('marketing_orders_challan.id', $invoiceID);
		$MarketingChallan = $MarketingChallan->first();

		if ($MarketingChallan) {

			$billFrom = "";
			$billFromAddressline1 = "";
			$billFromAddressline2 = "";
			$billPincode = "";
			$billCountryName = "";
			$billStateName = "";
			$billCityName = "";

			if ($MarketingChallan->channel_partner_reporting_manager_id != 0) {

				$ChannelPartner = ChannelPartner::select('firm_name', 'type', 'user_id');
				$ChannelPartner->where('user_id', $value->reporting_manager_id);
				$ChannelPartner = $ChannelPartner->first();
				if ($ChannelPartner) {

					$billFrom = $ChannelPartner->firm_name;
					$User = User::find($ChannelPartner->user_id);
					$billFromAddressline1 = $User->address_line1;
					$billFromAddressline2 = $User->address_line2;
					$billPincode = $User->pincode;
					$billCountryName = getCountryName($User->country_id);
					$billStateName = getStateName($User->state_id);
					$billCityName = getCityName($User->city_id);
					$parentID = $ChannelPartner->user_id;
				}
			} else {

				$Company = array();
				$Company = Company::select('*');
				$Company->where('id', $MarketingChallan->channel_partner_reporting_company_id);
				$Company = $Company->first();

				if ($Company) {
					$billFrom = $Company->name;
					$billFromAddressline1 = $Company->address_line1;
					$billFromAddressline2 = $Company->address_line2;
					$billPincode = $Company->pincode;
					$billCountryName = getCountryName($Company->country_id);
					$billStateName = getStateName($Company->state_id);
					$billCityName = getCityName($Company->city_id);
					$parentID = 0;
				}
			}

			$Dispater = User::where('type', 0)->where('parent_id', $parentID)->first();
			$DispaterMobileNo = "";
			if ($Dispater) {

				$DispaterMobileNo = $Dispater->dialing_code . " " . $Dispater->phone_number;
			}

			$courierService = "";

			$DataMaster = DataMaster::select('name')->find($MarketingChallan->courier_service_id);

			if ($DataMaster) {

				$courierService = $DataMaster->name;
			}

			$MarketingChallanItem = MarketingChallanItem::query();
			$MarketingChallanItem->select('marketing_order_items.id', 'marketing_orders_challan_items.qty', 'marketing_orders_challan_items.total_mrp', 'marketing_product_inventory.image as product_image', 'product_brand.name as product_brand_name', 'product_code.name as product_code_name');
			$MarketingChallanItem->leftJoin('order_items', 'marketing_orders_challan_items.order_item_id', '=', 'marketing_order_items.id');
			$MarketingChallanItem->leftJoin('marketing_product_inventory', 'marketing_product_inventory.id', '=', 'marketing_order_items.marketing_product_inventory_id');
			$MarketingChallanItem->leftJoin('data_master as product_brand', 'product_brand.id', '=', 'marketing_product_inventory.product_brand_id');
			$MarketingChallanItem->leftJoin('data_master as product_code', 'product_code.id', '=', 'marketing_product_inventory.product_code_id');
			$MarketingChallanItem->where('invoice_id', $invoiceID);
			$MarketingChallanItem->orderBy('id', 'desc');
			$MarketingChallanItem = $MarketingChallanItem->get();

			$data = array();
			$data['invoice'] = $MarketingChallan;
			$data['dispater_mobile_no'] = $DispaterMobileNo;
			$data['courier_service'] = $courierService;
			$data['track_id'] = $MarketingChallan->track_id;
			$data['billFrom'] = $billFrom;
			$data['billFromAddressline1'] = $billFromAddressline1;
			$data['billFromAddressline2'] = $billFromAddressline2;
			$data['billPincode'] = $billPincode;
			$data['billCountryName'] = $billCountryName;
			$data['billStateName'] = $billStateName;
			$data['billCityName'] = $billCityName;
			$data['invoice'] = $MarketingChallan;
			$data['invoice_items'] = $MarketingChallanItem;
		}

		$PDF = PDF::loadView('invoice/dispatched_pdf', compact('data'));
		//$PDF->stream();
		$PDF->save($fileName);
	}

	function dispatchedAjax(Request $request)
	{

		$isDispatcherUser = isMarketingDispatcherUser();
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
			14 => 'marketing_orders.created_at as order_date_time',
			15 => 'marketing_orders_challan.invoice_number',
			16 => 'marketing_orders.is_self',
			17 => 'users.phone_number',
			18 => 'channel_partner.type as channel_partner_type',
			19 => 'channel_partner_user.first_name as channel_partner_user_first_name',
			20 => 'channel_partner_user.last_name as channel_partner_user_last_name',
			21 => 'channel_partner_user.phone_number as channel_partner_user_phone_number',
			// 15 => 'marketing_orders_challan.dispatch_pdf',

		);

		$query = MarketingChallan::query();
		$query->leftJoin('marketing_orders', 'marketing_orders_challan.order_id', '=', 'marketing_orders.id');
		$query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'marketing_orders.channel_partner_user_id');
		$query->leftJoin('users as channel_partner_user', 'channel_partner_user.id', '=', 'marketing_orders.channel_partner_user_id');
		$query->leftJoin('users', 'users.id', '=', 'marketing_orders.user_id');
		$query->where('marketing_orders_challan.status', 2);

		// if ($isDispatcherUser == 1 || $isAdminOrCompanyAdmin == 1) {
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
		$query = MarketingChallan::query();
		$query->leftJoin('marketing_orders', 'marketing_orders_challan.order_id', '=', 'marketing_orders.id');
		$query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'marketing_orders.channel_partner_user_id');
		$query->leftJoin('users as channel_partner_user', 'channel_partner_user.id', '=', 'marketing_orders.channel_partner_user_id');
		$query->leftJoin('users', 'users.id', '=', 'marketing_orders.user_id');
		$query->where('marketing_orders_challan.status', 2);

		// if ($isDispatcherUser == 1 || $isAdminOrCompanyAdmin == 1) {

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
		//$query->where('channel_partner.reporting_company_id', Auth::user()->company_id);

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

		$channelPartner = getChannelPartners();

		foreach ($data as $key => $value) {

			$data[$key]['detail'] = '<h5 class="font-size-14 mb-1" data-bs-toggle="tooltip" title="' . convertOrderDateTime($value['order_date_time'], "date") . ' ' . convertOrderDateTime($value['order_date_time'], "time") . '"><a href="javascript: void(0);" class="text-dark">#MR' . $value['order_id'] . '</a></h5>
			    <p class="text-muted mb-0" data-bs-toggle="tooltip" title="CHALLAN NO">#' . ($value['invoice_number']) . '</p>
                <p class="text-muted mb-0" data-bs-toggle="tooltip"  data-bs-original-title="' . convertOrderDateTime($value['created_at'], "time") . '" >' . convertOrderDateTime($value['created_at'], "date") . '</p>';

			//

			$paymentMode = "";
			$channelPartnerType = "";

			//$paymentMode = getPaymentLable($value['payment_mode']);

			if ($value['is_self'] == 0) {
				$channelPartnerType = '<span class="badge rounded-pill badge-soft-dark font-size-11">' . $channelPartner[$value['channel_partner_type']]['short_name'] . '</span>';
			} else {
				$channelPartnerType = '<span class="badge rounded-pill badge-soft-dark font-size-11">SELF</p>';
			}


			$data[$key]['order_by'] = '<p class="text-muted mb-0" data-bs-toggle="tooltip" title="' . $value['first_name'] . '  ' . $value['last_name'] . '">' . displayStringLenth($value['first_name'] . '  ' . $value['last_name'], 10) . '</p>';
			$data[$key]['channel_partner'] = "";
			if ($value['is_self'] == 0) {
				$data[$key]['channel_partner'] = '<p class="text-muted mb-0 text-center"
                data-bs-toggle="tooltip" title="' . $value['channel_partner_user_first_name'] . ' ' . $value['channel_partner_user_last_name'] . '&#013;&#013; PHONE:' . $value['channel_partner_user_phone_number'] . '" >' . displayStringLenth($value['firm_name'], 15) . '</p><p class="text-muted mb-0 text-center">' . $channelPartnerType . " " . $paymentMode . '</p>';
			} else {

				$data[$key]['channel_partner'] = '<p class="text-muted mb-0 text-center"
                data-bs-toggle="tooltip" title="' . $value['first_name'] . ' ' . $value['last_name'] . '&#013;&#013; PHONE:' . $value['phone_number'] . '" >' . displayStringLenth($value['first_name'] . ' ' . $value['last_name'], 15) . '</p><p class="text-muted mb-0 text-center">' . $channelPartnerType . " " . $paymentMode . '</p>';
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

			$data[$key]['payment_detail'] = '<p class="text-muted mb-0">GST&nbsp;&nbsp;&nbsp;&nbsp;&nbsp&nbsp;&nbsp;&nbsp;: <i class="fas fa-rupee-sign"></i> <span class="price-lable-font">' . priceLable($value['gst_tax']) . '</span></p>

			<p class="text-muted mb-0 ">Total &nbsp;&nbsp;&nbsp;&nbsp;&nbsp: <i class="fas fa-rupee-sign"></i> <span class="price-lable-font">' . priceLable($value['total_payable']) . '</span></p>
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

			$data[$key]['action_mark_recieved'] = '<a onclick="doMarkAsRecieved(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Mark As Packed"><span class="badge badge-pill badge-soft-dark font-size-11"> MARK AS RECIEVED</span></a>';

			$uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';

			$uiAction .= '<li class="list-inline-item px-2">';
			$uiAction .= '<a onclick="ViewInvoice(\'' . $value['id'] . '\')" href="javascript: void(0);" title="View"><i class="mdi mdi-eye"></i></a>';
			$uiAction .= '</li>';

			// $uiAction .= '<li class="list-inline-item px-2">';
			// $uiAction .= '<a target="_blank" href="' . getSpaceFilePath($value['invoice_file']) . '" title="PDF"><i class="bx bxs-file-pdf"></i></a>';
			// $uiAction .= '</li>';

			//	if ($value['dispatch_pdf'] != "") {

			// $uiAction .= '<li class="list-inline-item px-2">';
			// $uiAction .= '<a target="_blank" href="' . getSpaceFilePath($value['dispatch_pdf']) . '" title="PDF"><i class="bx bxs-file-pdf"></i></a>';
			// $uiAction .= '</li>';

			//	}

			$uiAction .= '</ul>';
			$data[$key]['action'] = $uiAction;
		}

		$jsonData = array(
			"draw" => intval($request['draw']),
			// for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
			"recordsTotal" => intval($recordsTotal),
			// total number of records
			"recordsFiltered" => intval($recordsFiltered),
			// total number of records after searching, if there is no searching then totalFiltered = totalData
			"data" => $data, // total data array

		);
		return $jsonData;
	}

	public function markAsRecieved(Request $request)
	{

		$validator = Validator::make($request->all(), [
			'id' => ['required'],

		]);

		if ($validator->fails()) {

			$response = array();
			$response['status'] = 0;
			$response['msg'] = "The request could not be understood by the server due to malformed syntax";
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();

			return response()->json($response)->header('Content-Type', 'application/json');
		} else {

			$MarketingChallan = MarketingChallan::find($request->id);
			if ($MarketingChallan) {

				$isDispatcherUser = isMarketingDispatcherUser();
				$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();

				$MarketingOrder = MarketingOrder::query();
				$MarketingOrder->select('marketing_orders.*', 'channel_partner.reporting_company_id');
				$MarketingOrder->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'marketing_orders.channel_partner_user_id');

				if ($isDispatcherUser == 1 || $isAdminOrCompanyAdmin == 1) {
					if (Auth::user()->parent_id != 0) {

						$parent = ChannelPartner::where('user_id', Auth::user()->parent_id)->first();

						$MarketingOrder->where('channel_partner.reporting_manager_id', $parent->user_id);
					} else {

						$MarketingOrder->where('channel_partner.reporting_company_id', Auth::user()->company_id);
						$MarketingOrder->where('channel_partner.reporting_manager_id', 0);
					}
				} else if (isChannelPartner(Auth::user()->type) != 0) {

					$MarketingOrder->where('channel_partner.reporting_manager_id', Auth::user()->id);
				}

				$MarketingOrder = $MarketingOrder->where('marketing_orders.id', $MarketingChallan->order_id)->first();
				if ($MarketingOrder) {

					$MarketingChallan->status = 3;
					$MarketingChallan->save();
					$lastMarketingChallan = MarketingChallan::select('id')->where('order_id', $MarketingOrder->id)->orderBy('id', 'desc')->first();
					if ($lastMarketingChallan && $lastMarketingChallan->id == $MarketingChallan->id) {

						$MarketingOrder->sub_status = 3;
						$MarketingOrder->save();
					}
					$response = successRes("Successfully recieved invoice");
				} else {
					$response = errorRes("Invalid order");
				}
			} else {
				$response = errorRes("Invalid invoice");
			}
		}
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function detail(Request $request)
	{

		$isSalePerson = isSalePerson();
		if ($isSalePerson == 1) {
			$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
		}
		$isMarketingUser = isMarketingUser();
		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();

		$MarketingOrder = MarketingChallan::query();
		$MarketingOrder->select('marketing_orders_challan.id', 'marketing_orders.is_self', 'marketing_orders.channel_partner_user_id', 'marketing_orders_challan.dispatch_detail', 'marketing_orders_challan.eway_bill', 'marketing_orders_challan.created_at', 'marketing_orders.gst_number', 'marketing_orders.payment_mode', 'marketing_orders_challan.invoice_number', 'marketing_orders.sale_persons', 'marketing_orders_challan.total_mrp_minus_disocunt', 'marketing_orders_challan.total_mrp', 'marketing_orders_challan.gst_tax', 'marketing_orders_challan.delievery_charge', 'marketing_orders_challan.total_payable', 'marketing_orders.bill_address_line1', 'marketing_orders.bill_address_line2', 'marketing_orders.bill_pincode', 'marketing_orders.bill_state_id', 'marketing_orders.bill_city_id', 'marketing_orders.bill_country_id', 'marketing_orders.d_address_line1', 'marketing_orders.d_address_line2', 'marketing_orders.d_pincode', 'marketing_orders.d_state_id', 'marketing_orders.d_city_id', 'marketing_orders.d_country_id', 'channel_partner_detail.email as channel_partner_email', 'channel_partner_detail.phone_number as channel_partner_phone_number', 'channel_partner_detail.dialing_code as channel_partner_dialing_code', 'channel_partner_detail.first_name as channel_partner_first_name', 'channel_partner_detail.last_name as channel_partner_last_name', 'channel_partner.firm_name as channel_partner_firm_name', 'channel_partner.type as channel_partner_type', 'channel_partner.credit_limit as channel_partner_credit_limit', 'channel_partner.credit_days as channel_partner_credit_days', 'channel_partner.pending_credit as channel_partner_pending_credit', 'channel_partner.credit_days as channel_partner_credit_days', 'marketing_orders.remark');
		$MarketingOrder->leftJoin('marketing_orders', 'marketing_orders_challan.order_id', '=', 'marketing_orders.id');
		$MarketingOrder->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'marketing_orders.channel_partner_user_id');
		$MarketingOrder->leftJoin('users as channel_partner_detail', 'channel_partner_detail.id', '=', 'channel_partner.user_id');
		$MarketingOrder->where('marketing_orders_challan.id', $request->invoice_id);

		if ($isAdminOrCompanyAdmin == 1) {
		} else if ($isSalePerson == 1) {

			$MarketingOrder->where(function ($query) use ($childSalePersonsIds) {

				foreach ($childSalePersonsIds as $key => $value) {
					if ($key == 0) {
						$query->whereRaw('FIND_IN_SET("' . $value . '",marketing_orders.sale_persons)>0');
					} else {
						$query->orWhereRaw('FIND_IN_SET("' . $value . '",marketing_orders.sale_persons)>0');
					}
				}
			});

			$MarketingOrder->where('channel_partner.reporting_company_id', Auth::user()->company_id);
		} else if ($isMarketingUser == 1) {
		}

		$MarketingOrder = $MarketingOrder->first();

		if ($MarketingOrder) {

			$MarketingOrder->dispatch_detail = explode(",", $MarketingOrder->dispatch_detail);

			$salePersons = explode(",", $MarketingOrder['sale_persons']);
			$salePersons = User::select('first_name', 'last_name')->whereIn('id', $salePersons)->get();

			$salePersonsD = array();

			foreach ($salePersons as $keyS => $valueS) {

				$salePersonsD[$keyS] = $valueS['first_name'] . " " . $valueS['last_name'];
			}

			$MarketingOrder['sale_persons'] = implode(", ", $salePersonsD);

			$BCityList = CityList::find($MarketingOrder['bill_city_id']);
			$MarketingOrder['bill_city_name'] = "";
			if ($BCityList) {
				$MarketingOrder['bill_city_name'] = $BCityList->name;
			}

			$BStateList = StateList::find($MarketingOrder['bill_state_id']);
			$MarketingOrder['bill_state_name'] = "";
			if ($BStateList) {
				$MarketingOrder['bill_state_name'] = $BStateList->name;
			}

			$BCountryList = CountryList::find($MarketingOrder['bill_country_id']);
			$MarketingOrder['bill_country_name'] = "";
			if ($BCountryList) {
				$MarketingOrder['bill_country_name'] = $BCountryList->name;
			}

			$DCityList = CityList::find($MarketingOrder['d_city_id']);
			$MarketingOrder['d_city_name'] = "";
			if ($DCityList) {
				$MarketingOrder['d_city_name'] = $DCityList->name;
			}

			$DStateList = StateList::find($MarketingOrder['d_state_id']);
			$MarketingOrder['d_state_name'] = "";
			if ($DStateList) {
				$MarketingOrder['d_state_name'] = $DStateList->name;
			}

			$DCountryList = CountryList::find($MarketingOrder['d_country_id']);
			$MarketingOrder['d_country_name'] = "";
			if ($DCountryList) {
				$MarketingOrder['d_country_name'] = $DCountryList->name;
			}



			$MarketingOrder['channel_partner_type_name'] = getUserTypeName($MarketingOrder['channel_partner_type']);
			$MarketingOrder['display_date_time'] = convertOrderDateTime($MarketingOrder->created_at, "date");





			if ($MarketingOrder['is_self'] == 0) {
				$MarketingOrder['payment_mode_lable'] = getPaymentModeName($MarketingOrder['payment_mode']);
			} else {
				$SelfUser = User::find($MarketingOrder['channel_partner_user_id']);
				$MarketingOrder['payment_mode_lable'] = "";
				$MarketingOrder['channel_partner_firm_name'] = $SelfUser['first_name'] . " " . $SelfUser['last_name'];
				$MarketingOrder['channel_partner_first_name'] = $SelfUser['first_name'];
				$MarketingOrder['channel_partner_last_name'] = $SelfUser['first_name'];
				$MarketingOrder['channel_partner_email'] = $SelfUser['email'];
				$MarketingOrder['channel_partner_phone_number'] = $SelfUser['phone_number'];
				$MarketingOrder['channel_partner_dialing_code'] = $SelfUser['dialing_code'];
				$MarketingOrder['channel_partner_type'] = 0;
				$MarketingOrder['channel_partner_credit_limit'] = 0;
				$MarketingOrder['channel_partner_credit_days'] = 0;
				$MarketingOrder['channel_partner_pending_credit'] = 0;
				$MarketingOrder['channel_partner_type_name'] = "SELF";
			}

			$MarketingOrderItem = MarketingChallanItem::query();
			$MarketingOrderItem->select('marketing_orders_challan_items.id', 'marketing_orders_challan_items.qty', 'marketing_orders_challan_items.total_mrp', 'marketing_product_inventory.image as product_image', 'product_code.name as product_code_name', 'marketing_order_items.mrp', 'marketing_order_items.discount_percentage', 'marketing_order_items.weight', 'marketing_product_inventory.has_specific_code');
			$MarketingOrderItem->leftJoin('marketing_order_items', 'marketing_orders_challan_items.order_item_id', '=', 'marketing_order_items.id');
			$MarketingOrderItem->leftJoin('marketing_product_inventory', 'marketing_product_inventory.id', '=', 'marketing_order_items.marketing_product_inventory_id');
			//$MarketingOrderItem->leftJoin('data_master as product_group', 'product_group.id', '=', 'marketing_product_inventory.marketing_product_group_id');
			$MarketingOrderItem->leftJoin('data_master as product_code', 'product_code.id', '=', 'marketing_product_inventory.marketing_product_code_id');
			$MarketingOrderItem->where('orders_challan_id', $MarketingOrder->id);
			$MarketingOrderItem->orderBy('id', 'desc');
			$MarketingOrderItem = $MarketingOrderItem->get();

			$MarketingOrder['items'] = $MarketingOrderItem;

			$response = successRes("MarketingOrder detail");
			$response['data'] = $MarketingOrder;
		} else {
			$response = errorRes("Invalid order id");
		}
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function isAvailableStock(Request $request)
	{
		$MarketingChallan = MarketingChallan::find($request->invoice_id);
		if ($MarketingChallan) {
			$MarketingOrder = MarketingOrder::query();
			$MarketingOrder->select('marketing_orders.*', 'channel_partner.reporting_company_id');
			$MarketingOrder->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'marketing_orders.channel_partner_user_id');

			$MarketingOrder = $MarketingOrder->where('marketing_orders.id', $MarketingChallan->order_id)->first();

			if ($MarketingOrder) {
				$MarketingOrderItem = MarketingChallanItem::query();
				$MarketingOrderItem->select('marketing_orders_challan_items.id', 'marketing_orders_challan_items.qty', 'marketing_orders_challan_items.total_mrp', 'marketing_product_inventory.image as product_image', 'product_code.name as product_code_name', 'marketing_order_items.mrp', 'marketing_order_items.discount_percentage', 'marketing_order_items.weight', 'marketing_product_inventory.has_specific_code', 'marketing_product_inventory.quantity as availablestock', 'marketing_product_inventory.id as productid', 'marketing_product_inventory.description as productdescription');
				$MarketingOrderItem->leftJoin('marketing_order_items', 'marketing_orders_challan_items.order_item_id', '=', 'marketing_order_items.id');
				$MarketingOrderItem->leftJoin('marketing_product_inventory', 'marketing_product_inventory.id', '=', 'marketing_order_items.marketing_product_inventory_id');
				//$MarketingOrderItem->leftJoin('data_master as product_group', 'product_group.id', '=', 'marketing_product_inventory.marketing_product_group_id');
				$MarketingOrderItem->leftJoin('data_master as product_code', 'product_code.id', '=', 'marketing_product_inventory.marketing_product_code_id');
				$MarketingOrderItem->where('orders_challan_id', $MarketingChallan->id);
				$MarketingOrderItem->orderBy('id', 'desc');
				$MarketingOrderItem = $MarketingOrderItem->get();

				$shouldBeSpecificode = array();
				foreach ($MarketingOrderItem as $key => $value) {

					if ($value->qty > $value->availablestock) {
						$response = errorRes("Marketing Inventary #" . $value->productid . " (" . $value->productdescription . ") out of stock");
						$response['data'] = 0;
						return response()->json($response)->header('Content-Type', 'application/json');
					} else {
						$response = successRes("Success");
						$response['data'] = 1;
					}
				}

			}
		}
		return response()->json($response)->header('Content-Type', 'application/json');
	}
}