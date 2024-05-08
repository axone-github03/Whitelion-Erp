<?php

namespace App\Http\Controllers;

use App\Models\ChannelPartner;
use App\Models\CityList;
use App\Models\Company;
use App\Models\CountryList;
use App\Models\DataMaster;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoicePacked;
use App\Models\InvoicePackedItem;
use App\Models\MainMaster;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\StateList;
use App\Models\User;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use PDF;
use Mail;

//use Session;

class InvoiceManagementController extends Controller
{

	public function __construct()
	{

		$this->middleware(function ($request, $next) {

			$tabCanAccessBy = array(0, 1, 4, 101, 102, 103, 104, 105);

			if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
				return redirect()->route('dashboard');
			}

			return $next($request);
		});
	}

	public function test()
	{

		$Invoice = InvoicePacked::find(356);
		$fileName = 'box-' . time() . "-" . $Invoice->id . '.pdf';

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

		// $filePath = $folderPath . "/" . $fileName;
		// print_R($filePath);
		// die;

		//$this->generatePackedSticker($Invoice->id, public_path($filePath));
		$packedID = 356;

		$InvoicePacked = InvoicePacked::find($packedID);

		$Order = Invoice::query();
		$Order->select('invoice.id', 'invoice.dispatch_detail', 'invoice.eway_bill', 'invoice.created_at', 'orders.gst_number', 'orders.payment_mode', 'orders.sale_persons', 'invoice.total_mrp_minus_disocunt', 'invoice.total_mrp', 'invoice.gst_percentage', 'invoice.gst_tax', 'invoice.delievery_charge', 'invoice.total_payable', 'orders.bill_address_line1', 'orders.bill_address_line2', 'orders.bill_pincode', 'orders.bill_state_id', 'orders.bill_city_id', 'orders.bill_country_id', 'orders.d_address_line1', 'orders.d_address_line2', 'orders.d_pincode', 'orders.d_state_id', 'orders.d_city_id', 'orders.d_country_id', 'channel_partner_detail.email as channel_partner_email', 'channel_partner_detail.phone_number as channel_partner_phone_number', 'channel_partner_detail.dialing_code as channel_partner_dialing_code', 'channel_partner_detail.first_name as channel_partner_first_name', 'channel_partner_detail.last_name as channel_partner_last_name', 'channel_partner.firm_name as channel_partner_firm_name', 'channel_partner.type as channel_partner_type', 'channel_partner.credit_limit as channel_partner_credit_limit', 'channel_partner.credit_days as channel_partner_credit_days', 'channel_partner.pending_credit as channel_partner_pending_credit', 'channel_partner.credit_days as channel_partner_credit_days', 'orders.channel_partner_user_id');
		$Order->leftJoin('orders', 'invoice.order_id', '=', 'orders.id');
		$Order->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
		$Order->leftJoin('users as channel_partner_detail', 'channel_partner_detail.id', '=', 'channel_partner.user_id');
		$Order->where('invoice.id', $InvoicePacked->invoice_id);
		$Order = $Order->first();

		if ($Order) {

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

			$channelPartner = ChannelPartner::select('reporting_manager_id', 'reporting_company_id')->where('user_id', $Order['channel_partner_user_id'])->first();
			if ($channelPartner->reporting_manager_id != 0) {
				$channelPartnerDetail = ChannelPartner::select('channel_partner.firm_name', 'channel_partner_detail.dialing_code', 'channel_partner_detail.phone_number', 'channel_partner_detail.address_line1', 'channel_partner_detail.address_line2', 'channel_partner_detail.pincode', 'channel_partner_detail.country_id', 'channel_partner_detail.state_id', 'channel_partner_detail.city_id')->leftJoin('users as channel_partner_detail', 'channel_partner_detail.id', '=', 'channel_partner.user_id')->where('channel_partner.user_id', $channelPartner->reporting_manager_id)->first();

				$Order['from_compnay_name'] = $channelPartnerDetail->firm_name;
				$Order['from_phone_number'] = $channelPartnerDetail->dialing_code . " " . $channelPartnerDetail->phone_number;
				$Order['from_address_line1'] = $channelPartnerDetail->address_line1;
				$Order['from_address_line2'] = $channelPartnerDetail->address_line2;
				$Order['from_pincode'] = $channelPartnerDetail->pincode;

				$Order['from_country_name'] = "";
				$CountryList = CountryList::find($channelPartnerDetail->country_id);
				if ($CountryList) {
					$Order['from_country_name'] = $CountryList->name;
				}

				$CityList = CityList::find($channelPartnerDetail->city_id);
				$Order['from_city_name'] = "";
				if ($CityList) {
					$Order['from_city_name'] = $CityList->name;
				}

				$StateList = StateList::find($channelPartnerDetail->state_id);
				$Order['from_state_name'] = "";
				if ($StateList) {
					$Order['from_state_name'] = $StateList->name;
				}
			} else {
				$Company = Company::find($channelPartner->reporting_company_id);

				$Order['from_pincode'] = $Company->pincode;

				$Order['from_compnay_name'] = $Company->name;
				$Order['from_phone_number'] = $Company->phone_number;
				$Order['from_address_line1'] = $Company->address_line1;
				$Order['from_address_line2'] = $Company->address_line2;

				$Order['from_country_name'] = "";
				$CountryList = CountryList::find($Company->country_id);
				if ($CountryList) {
					$Order['from_country_name'] = $CountryList->name;
				}

				$CityList = CityList::find($Company->city_id);
				$Order['from_city_name'] = "";
				if ($CityList) {
					$Order['from_city_name'] = $CityList->name;
				}

				$StateList = StateList::find($Company->state_id);
				$Order['from_state_name'] = "";
				if ($StateList) {
					$Order['from_state_name'] = $StateList->name;
				}
			}

			$InvoiceItem = InvoicePackedItem::query();
			$InvoiceItem->select('invoice_packed_items.id', 'invoice_packed_items.qty', 'product_inventory.image as product_image', 'product_brand.name as product_brand_name', 'product_code.name as product_code_name');
			$InvoiceItem->leftJoin('order_items', 'invoice_packed_items.order_item_id', '=', 'order_items.id');
			$InvoiceItem->leftJoin('product_inventory', 'product_inventory.id', '=', 'order_items.product_inventory_id');
			$InvoiceItem->leftJoin('data_master as product_brand', 'product_brand.id', '=', 'product_inventory.product_brand_id');
			$InvoiceItem->leftJoin('data_master as product_code', 'product_code.id', '=', 'product_inventory.product_code_id');
			$InvoiceItem->where('invoice_packed_items.invoice_packed_id', $packedID);
			$InvoiceItem->orderBy('id', 'desc');
			$InvoiceItem = $InvoiceItem->get();

			$data = $Order;
			if ($InvoicePacked->total_weight == 0.00) {
				$totalWeight = "_________";
			} else {
				$totalWeight = $InvoicePacked->total_weight;
			}

			$data['invoice_packed_items'] = $InvoiceItem;

			$data['sticker_box_no'] = $InvoicePacked->sticker_box_no;
			$data['packed_date'] = $InvoicePacked->packed_date;
			$data['total_weight'] = $totalWeight;
			$data['department_name'] = $InvoicePacked->department_name == "" ? "______________" : $InvoicePacked->department_name;
			$customPaper = array(0, 0, 200, 75);

			$PDF = PDF::loadView('invoice/packed_pdf', compact('data'))->setPaper($customPaper);
			return $PDF->stream();
			//return $PDF->save($fileName);
		}
	}

	public function raised(Request $request)
	{

		// $this->generateDispatchPDF(1430, "/Applications/XAMPP/xamppfiles/htdocs/whitelion-erp-laravel/public/s/dispatch-pdf/2022/12/dispatch-1671611934-1430.pdf");
		// die;

		// $packedID = 8;

		// $InvoicePacked = InvoicePacked::find($packedID);

		// $Order = Invoice::query();
		// $Order->select('invoice.id', 'invoice.dispatch_detail', 'invoice.eway_bill', 'invoice.created_at', 'orders.gst_number', 'orders.payment_mode', 'orders.sale_persons', 'invoice.total_mrp_minus_disocunt', 'invoice.total_mrp', 'invoice.gst_percentage', 'invoice.gst_tax', 'invoice.delievery_charge', 'invoice.total_payable', 'orders.bill_address_line1', 'orders.bill_address_line2', 'orders.bill_pincode', 'orders.bill_state_id', 'orders.bill_city_id', 'orders.bill_country_id', 'orders.d_address_line1', 'orders.d_address_line2', 'orders.d_pincode', 'orders.d_state_id', 'orders.d_city_id', 'orders.d_country_id', 'channel_partner_detail.email as channel_partner_email', 'channel_partner_detail.phone_number as channel_partner_phone_number', 'channel_partner_detail.dialing_code as channel_partner_dialing_code', 'channel_partner_detail.first_name as channel_partner_first_name', 'channel_partner_detail.last_name as channel_partner_last_name', 'channel_partner.firm_name as channel_partner_firm_name', 'channel_partner.type as channel_partner_type', 'channel_partner.credit_limit as channel_partner_credit_limit', 'channel_partner.credit_days as channel_partner_credit_days', 'channel_partner.pending_credit as channel_partner_pending_credit', 'channel_partner.credit_days as channel_partner_credit_days', 'orders.channel_partner_user_id');
		// $Order->leftJoin('orders', 'invoice.order_id', '=', 'orders.id');
		// $Order->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
		// $Order->leftJoin('users as channel_partner_detail', 'channel_partner_detail.id', '=', 'channel_partner.user_id');
		// $Order->where('invoice.id', $InvoicePacked->invoice_id);
		// $Order = $Order->first();

		// if ($Order) {

		// 	$BCityList = CityList::find($Order['bill_city_id']);
		// 	$Order['bill_city_name'] = "";
		// 	if ($BCityList) {
		// 		$Order['bill_city_name'] = $BCityList->name;
		// 	}

		// 	$BStateList = StateList::find($Order['bill_state_id']);
		// 	$Order['bill_state_name'] = "";
		// 	if ($BStateList) {
		// 		$Order['bill_state_name'] = $BStateList->name;
		// 	}

		// 	$BCountryList = CountryList::find($Order['bill_country_id']);
		// 	$Order['bill_country_name'] = "";
		// 	if ($BCountryList) {
		// 		$Order['bill_country_name'] = $BCountryList->name;
		// 	}

		// 	$DCityList = CityList::find($Order['d_city_id']);
		// 	$Order['d_city_name'] = "";
		// 	if ($DCityList) {
		// 		$Order['d_city_name'] = $DCityList->name;
		// 	}

		// 	$DStateList = StateList::find($Order['d_state_id']);
		// 	$Order['d_state_name'] = "";
		// 	if ($DStateList) {
		// 		$Order['d_state_name'] = $DStateList->name;
		// 	}

		// 	$DCountryList = CountryList::find($Order['d_country_id']);
		// 	$Order['d_country_name'] = "";
		// 	if ($DCountryList) {
		// 		$Order['d_country_name'] = $DCountryList->name;
		// 	}

		// 	$Order['payment_mode_lable'] = getPaymentModeName($Order['payment_mode']);

		// 	$channelPartner = ChannelPartner::select('reporting_manager_id', 'reporting_company_id')->where('user_id', $Order['channel_partner_user_id'])->first();
		// 	if ($channelPartner->reporting_manager_id != 0) {
		// 		$channelPartnerDetail = ChannelPartner::select('channel_partner.firm_name', 'channel_partner_detail.dialing_code', 'channel_partner_detail.phone_number', 'channel_partner_detail.address_line1', 'channel_partner_detail.address_line2', 'channel_partner_detail.pincode', 'channel_partner_detail.country_id', 'channel_partner_detail.state_id', 'channel_partner_detail.city_id')->leftJoin('users as channel_partner_detail', 'channel_partner_detail.id', '=', 'channel_partner.user_id')->where('channel_partner.user_id', $channelPartner->reporting_manager_id)->first();

		// 		$Order['from_compnay_name'] = $channelPartnerDetail->firm_name;
		// 		$Order['from_phone_number'] = $channelPartnerDetail->dialing_code . " " . $channelPartnerDetail->phone_number;
		// 		$Order['from_address_line1'] = $channelPartnerDetail->address_line1;
		// 		$Order['from_address_line2'] = $channelPartnerDetail->address_line2;
		// 		$Order['from_pincode'] = $channelPartnerDetail->pincode;

		// 		$Order['from_country_name'] = "";
		// 		$CountryList = CountryList::find($channelPartnerDetail->country_id);
		// 		if ($CountryList) {
		// 			$Order['from_country_name'] = $CountryList->name;
		// 		}

		// 		$CityList = CityList::find($channelPartnerDetail->city_id);
		// 		$Order['from_city_name'] = "";
		// 		if ($CityList) {
		// 			$Order['from_city_name'] = $CityList->name;
		// 		}

		// 		$StateList = StateList::find($channelPartnerDetail->state_id);
		// 		$Order['from_state_name'] = "";
		// 		if ($StateList) {
		// 			$Order['from_state_name'] = $StateList->name;
		// 		}

		// 	} else {
		// 		$Company = Company::find($channelPartner->reporting_company_id);

		// 		$Order['from_pincode'] = $Company->pincode;

		// 		$Order['from_compnay_name'] = $Company->name;
		// 		$Order['from_phone_number'] = $Company->phone_number;
		// 		$Order['from_address_line1'] = $Company->address_line1;
		// 		$Order['from_address_line2'] = $Company->address_line2;

		// 		$Order['from_country_name'] = "";
		// 		$CountryList = CountryList::find($Company->country_id);
		// 		if ($CountryList) {
		// 			$Order['from_country_name'] = $CountryList->name;
		// 		}

		// 		$CityList = CityList::find($Company->city_id);
		// 		$Order['from_city_name'] = "";
		// 		if ($CityList) {
		// 			$Order['from_city_name'] = $CityList->name;
		// 		}

		// 		$StateList = StateList::find($Company->state_id);
		// 		$Order['from_state_name'] = "";
		// 		if ($StateList) {
		// 			$Order['from_state_name'] = $StateList->name;
		// 		}

		// 	}

		// 	$InvoiceItem = InvoicePackedItem::query();
		// 	$InvoiceItem->select('invoice_packed_items.id', 'invoice_packed_items.qty', 'product_inventory.image as product_image', 'product_brand.name as product_brand_name', 'product_code.name as product_code_name');
		// 	$InvoiceItem->leftJoin('order_items', 'invoice_packed_items.order_item_id', '=', 'order_items.id');
		// 	$InvoiceItem->leftJoin('product_inventory', 'product_inventory.id', '=', 'order_items.product_inventory_id');
		// 	$InvoiceItem->leftJoin('data_master as product_brand', 'product_brand.id', '=', 'product_inventory.product_brand_id');
		// 	$InvoiceItem->leftJoin('data_master as product_code', 'product_code.id', '=', 'product_inventory.product_code_id');
		// 	$InvoiceItem->where('invoice_packed_items.invoice_packed_id', $packedID);
		// 	$InvoiceItem->orderBy('id', 'desc');
		// 	$InvoiceItem = $InvoiceItem->get();

		// 	$data = $Order;
		// 	$data['invoice_packed_items'] = $InvoiceItem;

		// 	$data['sticker_box_no'] = $InvoicePacked->sticker_box_no;
		// 	$data['packed_date'] = $InvoicePacked->packed_date;
		// 	$data['total_weight'] = $InvoicePacked->total_weight == 0 ? "_________" : $InvoicePacked->total_weight;
		// 	$data['department_name'] = $InvoicePacked->department_name == "" ? "______________" : $InvoicePacked->department_name;
		// 	$customPaper = array(0, 0, 200, 75);
		// 	$PDF = PDF::loadView('invoice/packed_pdf', compact('data'))->setPaper($customPaper);
		// 	return $PDF->stream();
		// 	//return $PDF->save($fileName);
		// }

		$data = array();

		$data['title'] = "Invoice Raised";
		return view('invoice/raised', compact('data'));
	}

	public function packed(Request $request)
	{

		$data = array();
		$data['title'] = "Invoice Packed";
		return view('invoice/packed', compact('data'));
	}

	public function dispatched(Request $request)
	{
		$data = array();

		$data['title'] = "Invoice Dispatched";

		return view('invoice/dispatched', compact('data'));
	}

	public function recieved(Request $request)
	{

		$data = array();

		$data['title'] = "Invoice Recieved";

		return view('invoice/recieved', compact('data'));
	}

	public function cancelled(Request $request)
	{

		$data = array();

		$data['title'] = "Invoice Cancelled";

		return view('invoice/cancelled', compact('data'));
	}

	function raisedAjax(Request $request)
	{

		$isDispatcherUser = isDispatcherUser();
		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();

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
			15 => 'invoice.order_id',
			16 => 'orders.created_at as order_date_time',

		);

		$query = Invoice::query();
		$query->leftJoin('orders', 'invoice.order_id', '=', 'orders.id');
		$query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
		$query->where('invoice.status', 0);
		$query->where('invoice.is_cancelled', 0);

		if ($isDispatcherUser == 1 || $isAdminOrCompanyAdmin == 1) {
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

		$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.
		$query = Invoice::query();
		$query->leftJoin('orders', 'invoice.order_id', '=', 'orders.id');
		$query->leftJoin('users', 'users.id', '=', 'orders.user_id');
		$query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');

		if ($isDispatcherUser == 1 || $isAdminOrCompanyAdmin == 1) {
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

		$query->where('invoice.status', 0);
		$query->where('invoice.is_cancelled', 0);
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

			$data[$key]['packed_sticker'] = "";

			$InvoicePacked = InvoicePacked::select('sticker_box_no', 'sticker_pdf')->where('invoice_id', $value['id'])->orderBy('id', 'asc')->get();

			$packedStickerUI = '<ul class="list-inline font-size-20 contact-links mb-0">';
			foreach ($InvoicePacked as $kU => $vU) {

				$PackedPDF = getSpaceFilePath($vU['sticker_pdf']);
				$packedStickerUI .= '<li class="list-inline-item px-0">';
				$packedStickerUI .= '<a target="_blank" class="btn btn-outline-dark waves-effect waves-light" data-bs-toggle="tooltip" title="' . $vU['sticker_box_no'] . '" href="' . $PackedPDF . '" >' . $vU['sticker_box_no'] . '</a>';
				$packedStickerUI .= '</li>';
			}

			$packedStickerUI .= '</ul>';
			$data[$key]['packed_sticker'] = $packedStickerUI;

			$data[$key]['detail'] = '<h5 class="font-size-14 mb-1" data-bs-toggle="tooltip" title="' . convertOrderDateTime($value['order_date_time'], "date") . ' ' . convertOrderDateTime($value['order_date_time'], "time") . '"><a href="javascript: void(0);" class="text-dark">#' . highlightString($value['order_id'],$search_value) . '</a></h5>
			    <p class="text-muted mb-0" data-bs-toggle="tooltip" title="INVOICE NO">' . (highlightString($value['invoice_number'],$search_value)) . '</p>
                <p class="text-muted mb-0" data-bs-toggle="tooltip"  data-bs-original-title="' . convertOrderDateTime($value['created_at'], "time") . '" >' . highlightString(convertOrderDateTime($value['created_at'], "date"),$search_value) . '</p>';

			$paymentMode = "";

			$paymentMode = '<span class="badge rounded-pill badge-soft-dark font-size-11">' . getPaymentModeName($value['payment_mode']) . '</span>';

			$data[$key]['order_by'] = '<p class="text-muted mb-0">' . highlightString($value['first_name'] . '  ' . $value['last_name'],$search_value) . '</p>';
			$data[$key]['channel_partner'] = '<p class="text-muted mb-0">' . highlightString($value['firm_name'],$search_value) . '</p><p class="text-muted mb-0">' . $paymentMode . '</p>';

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
			"draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
			"recordsTotal" => intval($recordsTotal), // total number of records
			"recordsFiltered" => intval($recordsFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
			"data" => $data, // total data array

		);
		return $jsonData;
	}
	public function markAsPacked(Request $request)
	{
		//		'invoice_courier_service_id' => ['required'],
		$validator = Validator::make($request->all(), [
			'invoice_packed_invoice_id' => ['required'],
			'invoice_packed_packed_date' => ['required'],
			'invoice_track_id' => ['required'],

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
			$isDispatcherUser = isDispatcherUser();

			$InvoiceItems = array();
			$keyCount = 0;

			foreach ($request->input_order_item_id as $key => $value) {

				if (isset($request->input_order_item_id[$key])) {

					$inputQty = (int) $request->input_qty[$key];
					$OrderItem = InvoiceItem::find($value);
					if ($OrderItem) {
						$pendingQty = floatval($OrderItem->pending_packed_qty);

						if ($pendingQty >= $inputQty) {

							if ($inputQty > 0) {

								$InvoiceItems[$keyCount]['id'] = $request->input_order_item_id[$key];
								$InvoiceItems[$keyCount]['qty'] = $inputQty;
								$InvoiceItems[$keyCount]['mrp'] = 0;
								$InvoiceItems[$keyCount]['discount_percentage'] = 0;
								$InvoiceItems[$keyCount]['weight'] = 0;
								$InvoiceItems[$keyCount]['info']['pending_qty'] = $pendingQty - $inputQty;

								$keyCount++;
							}
						} else {

							$response = errorRes("Invalid Packed QTY");
							return response()->json($response)->header('Content-Type', 'application/json');
						}
					} else {

						$response = errorRes("Invalid Invoice Item" . $value);
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

			$Order = Invoice::query();
			$Order->select('invoice.id', 'invoice.is_cancelled', 'invoice.dispatch_detail', 'invoice.eway_bill', 'invoice.created_at', 'orders.gst_number', 'orders.payment_mode', 'orders.sale_persons', 'invoice.total_mrp_minus_disocunt', 'invoice.total_mrp', 'invoice.gst_percentage', 'invoice.gst_tax', 'invoice.delievery_charge', 'invoice.total_payable', 'orders.bill_address_line1', 'orders.bill_address_line2', 'orders.bill_pincode', 'orders.bill_state_id', 'orders.bill_city_id', 'orders.bill_country_id', 'orders.d_address_line1', 'orders.d_address_line2', 'orders.d_pincode', 'orders.d_state_id', 'orders.d_city_id', 'orders.d_country_id', 'channel_partner_detail.email as channel_partner_email', 'channel_partner_detail.phone_number as channel_partner_phone_number', 'channel_partner_detail.dialing_code as channel_partner_dialing_code', 'channel_partner_detail.first_name as channel_partner_first_name', 'channel_partner_detail.last_name as channel_partner_last_name', 'channel_partner.firm_name as channel_partner_firm_name', 'channel_partner.type as channel_partner_type', 'channel_partner.credit_limit as channel_partner_credit_limit', 'channel_partner.credit_days as channel_partner_credit_days', 'channel_partner.pending_credit as channel_partner_pending_credit', 'channel_partner.credit_days as channel_partner_credit_days', 'orders.id as order_id');
			$Order->leftJoin('orders', 'invoice.order_id', '=', 'orders.id');
			$Order->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
			$Order->leftJoin('users as channel_partner_detail', 'channel_partner_detail.id', '=', 'channel_partner.user_id');
			$Order->where('invoice.id', $request->invoice_packed_invoice_id);

			if ($isDispatcherUser == 1 || $isAdminOrCompanyAdmin == 1) {
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

			if ($Order && $Order->status == 0 && $Order->is_cancelled == 0) {

				$InvoicePacked = InvoicePacked::where('invoice_id', $Order->id)->count();
				$InvoicePacked = $InvoicePacked + 1;

				$totalWeight = isset($request->invoice_packed_total_weight) ? $request->invoice_packed_total_weight : 0;
				$GSTPercentage = 0;
				$shippingCost = 0;
				$invoiceDetail = calculationProcessOfOrder($InvoiceItems, $GSTPercentage, $shippingCost);
				$invoice_packed_department_name = isset($request->invoice_packed_department_name) ? $request->invoice_packed_department_name : '';

				$Invoice = new InvoicePacked();
				$Invoice->user_id = Auth::user()->id;
				$Invoice->invoice_id = $Order->id;
				$Invoice->order_id = $Order->order_id;
				$Invoice->department_name = $invoice_packed_department_name;
				$Invoice->total_weight = $totalWeight;
				$Invoice->sticker_box_no = $InvoicePacked;
				$Invoice->packed_date = $request->invoice_packed_packed_date;
				$Invoice->save();

				foreach ($invoiceDetail['items'] as $key => $value) {

					$InvoiceItemObject = InvoiceItem::select('order_item_id')->find($value['id']);

					$InvoiceItem = new InvoicePackedItem();
					$InvoiceItem->user_id = Auth::user()->id;
					$InvoiceItem->order_id = $Order->order_id;
					$InvoiceItem->invoice_item_id = $value['id'];
					$InvoiceItem->order_item_id = $InvoiceItemObject->order_item_id;
					$InvoiceItem->invoice_id = $Order->id;
					$InvoiceItem->invoice_packed_id = $Invoice->id;
					$InvoiceItem->qty = $value['qty'];
					$InvoiceItem->save();

					$OrderItem = InvoiceItem::find($value['id']);
					$OrderItem->pending_packed_qty = $value['info']['pending_qty'];
					$OrderItem->save();
				}

				$hasPendingQty = InvoiceItem::where('invoice_id', $Order->id)->where('pending_packed_qty', '!=', 0)->first();

				if (!$hasPendingQty) {

					$Order->status = 1;
					$Order->save();

					$lastInvoice = Invoice::select('id')->where('order_id', $Order->order_id)->orderBy('id', 'desc')->first();
					if ($lastInvoice && $lastInvoice->id == $Order->id) {

						$OrderObject = Order::find($Order->order_id);
						if ($OrderObject) {
							$OrderObject->sub_status = 1;
							$OrderObject->save();
						}
					}
				}

				$response = successRes("Successfully packed item");

				$response['sticker_pdf'] = "";

				$fileName = 'box-' . time() . "-" . $Invoice->id . '.pdf';

				$folderPath = '/s/box-pdf/' . date('Y') . "/" . date('m');

				if (!is_dir("s/box-pdf")) {
					mkdir("s/box-pdf");
				}

				if (!is_dir("s/box-pdf/" . date('Y'))) {
					mkdir("s/box-pdf/" . date('Y'));
				}

				if (!is_dir("s/box-pdf/" . date('Y') . "/" . date('m'))) {
					mkdir("s/box-pdf/" . date('Y') . "/" . date('m'));
				}

				if (!is_dir("s/box-pdf/" . date('Y') . "/" . date('m'))) {
					mkdir("s/box-pdf/" . date('Y') . "/" . date('m'));
				}

				$filePath = $folderPath . "/" . $fileName;

				$InvoicePackedId = $Invoice->id;

				$Invoice = Invoice::find($request->invoice_packed_invoice_id);

				///

				$uploadedFile1 = [];
				$uploadedFile2 = "";

				if ($request->hasFile('invoice_dispatch_detail')) {

					$folderPathofFile = '/s/dispatch-detail';
					if (!is_dir(public_path($folderPathofFile))) {
						mkdir(public_path($folderPathofFile));
					}

					$folderPathofFile = '/s/dispatch-detail/' . date('Y');

					if (!is_dir(public_path($folderPathofFile))) {

						mkdir(public_path($folderPathofFile));
					}

					$folderPathofFile = '/s/dispatch-detail/' . date('Y') . "/" . date('m');
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

					$folderPathofFile = '/s/eway-bill';
					if (!is_dir(public_path($folderPathofFile))) {
						mkdir(public_path($folderPathofFile));
					}

					$folderPathofFile = '/s/eway-bill/' . date('Y');

					if (!is_dir(public_path($folderPathofFile))) {

						mkdir(public_path($folderPathofFile));
					}

					$folderPathofFile = '/s/eway-bill/' . date('Y') . "/" . date('m');
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

				if ($uploadedFile2 != "") {
					$Invoice->eway_bill = $uploadedFile2;
				}

				if (count($uploadedFile1) > 0) {
					$Invoice->dispatch_detail = implode(",", $uploadedFile1);
				}

				$Invoice->track_id = $request->invoice_track_id;
				//$Invoice->box_number = $request->invoice_box_number;
				if (isset($request->invoice_courier_service_id)) {
					$Invoice->courier_service_id = $request->invoice_courier_service_id;
				}

				//$Invoice->status = 2;
				$Invoice->save();

				$InvoicePacked = InvoicePacked::find($InvoicePackedId);

				$this->generatePackedSticker($InvoicePacked->id, public_path($filePath));
				$spaceUploadResponse = uploadFileOnSpaces(public_path($filePath), $filePath);
				if ($spaceUploadResponse == 1) {

					$InvoicePacked->sticker_pdf = $filePath;
					$InvoicePacked->save();
					unlink(public_path($filePath));
					$response['sticker_pdf'] = getSpaceFilePath($filePath);
				}

				//

			} else {

				$response = errorRes("Invalid order");
			}
		}

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	function generatePackedSticker($packedID, $fileName)
	{

		$InvoicePacked = InvoicePacked::find($packedID);

		$Order = Invoice::query();
		$Order->select('invoice.id', 'invoice.dispatch_detail', 'invoice.eway_bill', 'invoice.created_at', 'orders.gst_number', 'orders.payment_mode', 'orders.sale_persons', 'invoice.total_mrp_minus_disocunt', 'invoice.total_mrp', 'invoice.gst_percentage', 'invoice.gst_tax', 'invoice.delievery_charge', 'invoice.total_payable', 'orders.bill_address_line1', 'orders.bill_address_line2', 'orders.bill_pincode', 'orders.bill_state_id', 'orders.bill_city_id', 'orders.bill_country_id', 'orders.d_address_line1', 'orders.d_address_line2', 'orders.d_pincode', 'orders.d_state_id', 'orders.d_city_id', 'orders.d_country_id', 'channel_partner_detail.email as channel_partner_email', 'channel_partner_detail.phone_number as channel_partner_phone_number', 'channel_partner_detail.dialing_code as channel_partner_dialing_code', 'channel_partner_detail.first_name as channel_partner_first_name', 'channel_partner_detail.last_name as channel_partner_last_name', 'channel_partner.firm_name as channel_partner_firm_name', 'channel_partner.type as channel_partner_type', 'channel_partner.credit_limit as channel_partner_credit_limit', 'channel_partner.credit_days as channel_partner_credit_days', 'channel_partner.pending_credit as channel_partner_pending_credit', 'channel_partner.credit_days as channel_partner_credit_days', 'orders.channel_partner_user_id', 'invoice.track_id', 'invoice.courier_service_id');
		$Order->leftJoin('orders', 'invoice.order_id', '=', 'orders.id');
		$Order->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
		$Order->leftJoin('users as channel_partner_detail', 'channel_partner_detail.id', '=', 'channel_partner.user_id');
		$Order->where('invoice.id', $InvoicePacked->invoice_id);
		$Order = $Order->first();

		if ($Order) {

			$courierService = "";

			$DataMaster = DataMaster::select('name')->find($Order->courier_service_id);

			if ($DataMaster) {

				$courierService = $DataMaster->name;
			}

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

			$channelPartner = ChannelPartner::select('reporting_manager_id', 'reporting_company_id')->where('user_id', $Order['channel_partner_user_id'])->first();
			if ($channelPartner->reporting_manager_id != 0) {
				$channelPartnerDetail = ChannelPartner::select('channel_partner.firm_name', 'channel_partner_detail.dialing_code', 'channel_partner_detail.phone_number', 'channel_partner_detail.address_line1', 'channel_partner_detail.address_line2', 'channel_partner_detail.pincode', 'channel_partner_detail.country_id', 'channel_partner_detail.state_id', 'channel_partner_detail.city_id')->leftJoin('users as channel_partner_detail', 'channel_partner_detail.id', '=', 'channel_partner.user_id')->where('channel_partner.user_id', $channelPartner->reporting_manager_id)->first();

				$Order['from_compnay_name'] = $channelPartnerDetail->firm_name;
				$Order['from_phone_number'] = $channelPartnerDetail->dialing_code . " " . $channelPartnerDetail->phone_number;
				$Order['from_address_line1'] = $channelPartnerDetail->address_line1;
				$Order['from_address_line2'] = $channelPartnerDetail->address_line2;
				$Order['from_pincode'] = $channelPartnerDetail->pincode;

				$Order['from_country_name'] = "";
				$CountryList = CountryList::find($channelPartnerDetail->country_id);
				if ($CountryList) {
					$Order['from_country_name'] = $CountryList->name;
				}

				$CityList = CityList::find($channelPartnerDetail->city_id);
				$Order['from_city_name'] = "";
				if ($CityList) {
					$Order['from_city_name'] = $CityList->name;
				}

				$StateList = StateList::find($channelPartnerDetail->state_id);
				$Order['from_state_name'] = "";
				if ($StateList) {
					$Order['from_state_name'] = $StateList->name;
				}
			} else {
				$Company = Company::find($channelPartner->reporting_company_id);

				$Order['from_pincode'] = $Company->pincode;

				$Order['from_compnay_name'] = $Company->name;
				$Order['from_phone_number'] = $Company->phone_number;
				$Order['from_address_line1'] = $Company->address_line1;
				$Order['from_address_line2'] = $Company->address_line2;

				$Order['from_country_name'] = "";
				$CountryList = CountryList::find($Company->country_id);
				if ($CountryList) {
					$Order['from_country_name'] = $CountryList->name;
				}

				$CityList = CityList::find($Company->city_id);
				$Order['from_city_name'] = "";
				if ($CityList) {
					$Order['from_city_name'] = $CityList->name;
				}

				$StateList = StateList::find($Company->state_id);
				$Order['from_state_name'] = "";
				if ($StateList) {
					$Order['from_state_name'] = $StateList->name;
				}
			}

			$InvoiceItem = InvoicePackedItem::query();
			$InvoiceItem->select('invoice_packed_items.id', 'invoice_packed_items.qty', 'product_inventory.image as product_image', 'product_brand.name as product_brand_name', 'product_code.name as product_code_name');
			$InvoiceItem->leftJoin('order_items', 'invoice_packed_items.order_item_id', '=', 'order_items.id');
			$InvoiceItem->leftJoin('product_inventory', 'product_inventory.id', '=', 'order_items.product_inventory_id');
			$InvoiceItem->leftJoin('data_master as product_brand', 'product_brand.id', '=', 'product_inventory.product_brand_id');
			$InvoiceItem->leftJoin('data_master as product_code', 'product_code.id', '=', 'product_inventory.product_code_id');
			$InvoiceItem->where('invoice_packed_items.invoice_packed_id', $packedID);
			$InvoiceItem->orderBy('id', 'desc');
			$InvoiceItem = $InvoiceItem->get();

			$data = $Order;
			if ($InvoicePacked->total_weight == 0.00) {
				$totalWeight = "_________";
			} else {
				$totalWeight = $InvoicePacked->total_weight;
			}

			$data['invoice_packed_items'] = $InvoiceItem;

			$data['sticker_box_no'] = $InvoicePacked->sticker_box_no;
			$data['packed_date'] = $InvoicePacked->packed_date;
			$data['total_weight'] = $totalWeight;
			$data['department_name'] = $InvoicePacked->department_name == "" ? "______________" : $InvoicePacked->department_name;

			$data['courier_service'] = $courierService;
			$data['track_id'] = $Order['track_id'];
			$data['no_of_box'] = $InvoicePacked->sticker_box_no;
			$customPaper = array(0, 0, 75, 200);

			$PDF = PDF::loadView('invoice/packed_pdf', compact('data'))->setPaper($customPaper);
			//$PDF->stream();
			return $PDF->save($fileName);
		}
	}

	public function markAsPackedDepricated(Request $request)
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

			$Invoice = Invoice::find($request->id);
			if ($Invoice) {

				$isDispatcherUser = isDispatcherUser();
				$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();

				$Order = Order::query();
				$Order->select('orders.*', 'channel_partner.reporting_company_id');
				$Order->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');

				if ($isDispatcherUser == 1 || $isAdminOrCompanyAdmin == 1) {
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

				$Order = $Order->where('orders.id', $Invoice->order_id)->first();
				if ($Order) {

					$Invoice->status = 1;
					$Invoice->save();

					$lastInvoice = Invoice::select('id')->where('order_id', $Order->id)->orderBy('id', 'desc')->first();
					if ($lastInvoice && $lastInvoice->id == $Invoice->id) {

						$Order->sub_status = 1;
						$Order->save();
					}

					$response = successRes("Successfully invoice status");
				} else {
					$response = errorRes("Invalid order");
				}
			} else {
				$response = errorRes("Invalid invoice");
			}
		}
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function packedAjax(Request $request)
	{

		$isDispatcherUser = isDispatcherUser();
		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();

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
			15 => 'invoice.order_id',
			16 => 'orders.created_at as order_date_time',

		);

		$query = Invoice::query();
		$query->leftJoin('orders', 'invoice.order_id', '=', 'orders.id');
		$query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
		$query->where('invoice.status', 1);
		$query->where('invoice.is_cancelled', 0);

		if ($isDispatcherUser == 1 || $isAdminOrCompanyAdmin == 1) {
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

		$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.
		$query = Invoice::query();
		$query->leftJoin('orders', 'invoice.order_id', '=', 'orders.id');
		$query->leftJoin('users', 'users.id', '=', 'orders.user_id');
		$query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
		$query->where('invoice.status', 1);
		$query->where('invoice.is_cancelled', 0);
		if ($isDispatcherUser == 1 || $isAdminOrCompanyAdmin == 1) {
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

			$data[$key]['packed_sticker'] = "";

			$InvoicePacked = InvoicePacked::select('sticker_box_no', 'sticker_pdf')->where('invoice_id', $value['id'])->orderBy('id', 'asc')->get();

			$packedStickerUI = '<ul class="list-inline font-size-20 contact-links mb-0">';
			foreach ($InvoicePacked as $kU => $vU) {

				$PackedPDF = getSpaceFilePath($vU['sticker_pdf']);
				$packedStickerUI .= '<li class="list-inline-item px-0">';
				$packedStickerUI .= '<a target="_blank" class="btn btn-outline-dark waves-effect waves-light" data-bs-toggle="tooltip" title="' . $vU['sticker_box_no'] . '" href="' . $PackedPDF . '" >' . $vU['sticker_box_no'] . '</a>';
				$packedStickerUI .= '</li>';
			}

			$packedStickerUI .= '</ul>';
			$data[$key]['packed_sticker'] = $packedStickerUI;
			$noOFBox = count($InvoicePacked);

			$data[$key]['detail'] = '<h5 class="font-size-14 mb-1" data-bs-toggle="tooltip" title="' . convertOrderDateTime($value['order_date_time'], "date") . ' ' . convertOrderDateTime($value['order_date_time'], "time") . '"><a href="javascript: void(0);" class="text-dark">#' . highlightString($value['order_id'],$search_value) . '</a></h5>
			    <p class="text-muted mb-0" data-bs-toggle="tooltip" title="INVOICE NO">' . (highlightString($value['invoice_number'],$search_value)) . '</p>
                <p class="text-muted mb-0" data-bs-toggle="tooltip"  data-bs-original-title="' . convertOrderDateTime($value['created_at'], "time") . '" >' . highlightString(convertOrderDateTime($value['created_at'], "date"),$search_value) . '</p>';

			$paymentMode = "";

			$paymentMode = '<span class="badge rounded-pill badge-soft-dark font-size-11">' . getPaymentModeName($value['payment_mode']) . '</span>';

			$data[$key]['order_by'] = '<p class="text-muted mb-0">' . highlightString($value['first_name'] . '  ' . $value['last_name'],$search_value) . '</p>';
			$data[$key]['channel_partner'] = '<p class="text-muted mb-0">' . highlightString($value['firm_name'],$search_value) . '</p><p class="text-muted mb-0">' . $paymentMode . '</p>';

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

			$data[$key]['action_mark_dispatch'] = '<a onclick="doMarkAsDispatch(\'' . $value['id'] . '\',\'' . $noOFBox . '\')" href="javascript: void(0);" title="Mark As Packed"><span class="badge badge-pill badge-soft-success font-size-11"> MARK AS DISPATCH</span></a>';

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

		]);
		// $validator = Validator::make($request->all(), [
		// 	'invoice_id' => ['required'],
		// 	'invoice_track_id' => ['required'],
		// 	'invoice_box_number' => ['required'],
		// 	'invoice_courier_service_id' => ['required'],

		// ]);

		if ($validator->fails()) {

			$response = array();
			$response['status'] = 0;
			$response['msg'] = "The request could not be understood by the server due to malformed syntax";
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();

			return response()->json($response)->header('Content-Type', 'application/json');
		} else {

			$Invoice = Invoice::find($request->invoice_id);
			if ($Invoice && $Invoice->status != 2 && $Invoice->is_cancelled == 0) {

				$isDispatcherUser = isDispatcherUser();
				$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();

				$Order = Order::query();
				$Order->select('orders.*', 'channel_partner.reporting_company_id');
				$Order->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');

				if ($isDispatcherUser == 1 || $isAdminOrCompanyAdmin == 1) {
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

				$Order = $Order->where('orders.id', $Invoice->order_id)->first();
				if ($Order) {

					$uploadedFile1 = [];
					$uploadedFile2 = "";

					if ($request->hasFile('invoice_dispatch_detail')) {

						$folderPathofFile = '/s/dispatch-detail';
						if (!is_dir(public_path($folderPathofFile))) {
							mkdir(public_path($folderPathofFile));
						}

						$folderPathofFile = '/s/dispatch-detail/' . date('Y');

						if (!is_dir(public_path($folderPathofFile))) {

							mkdir(public_path($folderPathofFile));
						}

						$folderPathofFile = '/s/dispatch-detail/' . date('Y') . "/" . date('m');
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

						$folderPathofFile = '/s/eway-bill';
						if (!is_dir(public_path($folderPathofFile))) {
							mkdir(public_path($folderPathofFile));
						}

						$folderPathofFile = '/s/eway-bill/' . date('Y');

						if (!is_dir(public_path($folderPathofFile))) {

							mkdir(public_path($folderPathofFile));
						}

						$folderPathofFile = '/s/eway-bill/' . date('Y') . "/" . date('m');
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

					//$Invoice->eway_bill = $uploadedFile2;
					// $Invoice->dispatch_detail = implode(",", $uploadedFile1);
					// $Invoice->track_id = $request->invoice_track_id;
					$Invoice->box_number = $request->invoice_box_number;
					//$Invoice->courier_service_id = $request->invoice_courier_service_id;
					$Invoice->status = 2;
					$Invoice->save();

					$orderPreviousStatus = $Order->status;

					$lastInvoice = Invoice::select('id')->where('order_id', $Order->id)->orderBy('id', 'desc')->first();
					if ($lastInvoice && $lastInvoice->id == $Invoice->id) {

						$hasOrderItemPending = OrderItem::where('order_id', $Order->id)->where('pending_qty', '!=', 0)->first();
						if (!$hasOrderItemPending) {
							$Order->status = 3;
						} else {
							$Order->status = 2;
						}
						$Order->sub_status = 2;
						$Order->save();
						$orderNewStatus = $Order->status;
					}
					$Order->dispatched_total_payable = $Order->dispatched_total_payable + $Invoice->total_payable;
					$Order->dispatched_total_qty = $Order->dispatched_total_qty + $Invoice->total_qty;
					$Order->save();

					$InvoiceItems = InvoiceItem::where('invoice_id', $Invoice->id)->get();
					foreach ($InvoiceItems as $key => $value) {
						$OrderItem = OrderItem::find($value->order_item_id);
						$OrderItem->dispatched_qty = $OrderItem->dispatched_qty + $value['qty'];
						$OrderItem->save();
					}




					$response = successRes("Successfully dispatched invoice");
					$response['dispatch_pdf'] = "";

					$fileName = 'dispatch-' . time() . "-" . $Invoice->id . '.pdf';

					$folderPath = '/s/dispatch-pdf/' . date('Y') . "/" . date('m');

					if (!is_dir("s/dispatch-pdf")) {
						mkdir("s/dispatch-pdf");
					}

					if (!is_dir("s/dispatch-pdf/" . date('Y'))) {
						mkdir("s/dispatch-pdf/" . date('Y'));
					}

					if (!is_dir("s/dispatch-pdf/" . date('Y') . "/" . date('m'))) {
						mkdir("s/dispatch-pdf/" . date('Y') . "/" . date('m'));
					}

					$filePath = $folderPath . "/" . $fileName;

					$this->generateDispatchPDF($Invoice->id, public_path($filePath));

					$spaceUploadResponse = uploadFileOnSpaces(public_path($filePath), $filePath);
					if ($spaceUploadResponse == 1) {

						$Invoice->dispatch_pdf = $filePath;
						$Invoice->save();
						unlink(public_path($filePath));
						$response['dispatch_pdf'] = getSpaceFilePath($filePath);
					}


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
						$fromStatus = getOrderStatus($orderPreviousStatus);
						$toStatus = getOrderStatus($orderNewStatus);
						//$mobileNotificationMessage = "Your Order " . $Order->id . " Status Update " . $fromStatus . " To " . $toStatus;
						$mobileNotificationMessage = "Your Order " . $Order->id . " " . $ChannelPartner->firm_name . " Status Update " . $fromStatus . " To " . $toStatus;
						$notificationDebug = sendNotificationTOAndroid($mobileNotificationTitle, $mobileNotificationMessage, $UsersNotificationTokens,'Order',$Order);

						$courierService = "";

						$DataMaster = DataMaster::select('name')->find($lastInvoice->courier_service_id);

						if ($DataMaster) {
							$courierService = $DataMaster->name;
						}

						$ChannelPartnerDetail = User::where('id', $Order->channel_partner_user_id)->first();
						// order_dispatch_channel_partner
						$params = array();

						$configrationForNotify = configrationForNotify();
						$params['from_email'] = $configrationForNotify['from_email'];
						$params['from_name'] = $configrationForNotify['from_name'];
						$params['bcc_email'] = array("poonam@whitelion.in");
						$params['to_email'] = $ChannelPartnerDetail->email;
						$params['to_name'] = $configrationForNotify['to_name'];
						$params['user_name'] = $ChannelPartnerDetail->first_name . ' '  . $ChannelPartnerDetail->last_name;
						$params['subject'] = "Dispatch of materials";
						$params['traking_id'] = $lastInvoice->track_id;
						$params['courier_service_name'] = $courierService;
						// $params['file_path'] = $lastInvoice->invoice_file;
						// $params['file_name'] = "ankit.pdf";
						$params['id'] = $Order->id;
						if (Config::get('app.env') == "local") {
							$params['to_email'] = $configrationForNotify['test_email'];
							//$params['bcc_email'] = $configrationForNotify['test_email_bcc'];
						}
						// TEMPLATE 16
						Mail::send('emails.order_dispatch_channel_partner', ['params' => $params], function ($m) use ($params) {
							$m->from($params['from_email'], $params['from_name']);
							$m->bcc($params['bcc_email']);
							$m->to($params['to_email'], $params['user_name'])->subject($params['subject']);
							// $m->attach(getSpaceFilePath($params['file_path']), array(
							// 	'as' => $params['file_name']
							// ));
						});
					}

					// } else {

					// 	$response = errorRes("Invalid dispatch detail");

					// }

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

		// $InvoiceList = InvoicePacked::get();

		// foreach ($InvoiceList as $IP => $VP) {

		// 	$packedID = $VP->id;

		// 	$Invoice = InvoicePacked::find($packedID);

		// 	$fileName = 'box-' . time() . "-" . $Invoice->id . '.pdf';

		// 	$folderPath = '/s/box-pdf/' . date('Y') . "/" . date('m');

		// 	if (!is_dir("s/box-pdf")) {
		// 		mkdir("s/box-pdf");
		// 	}

		// 	if (!is_dir("s/box-pdf/" . date('Y') . "/" . date('m'))) {
		// 		mkdir("s/box-pdf/" . date('Y'));
		// 	}

		// 	if (!is_dir("s/box-pdf/" . date('Y') . "/" . date('m'))) {
		// 		mkdir("s/box-pdf/" . date('Y') . "/" . date('m'));
		// 	}

		// 	$filePath = $folderPath . "/" . $fileName;

		// 	$this->generatePackedSticker($Invoice->id, public_path($filePath));

		// 	$spaceUploadResponse = uploadFileOnSpaces(public_path($filePath), $filePath);
		// 	if ($spaceUploadResponse == 1) {

		// 		$Invoice->sticker_pdf = $filePath;
		// 		$Invoice->save();
		// 		unlink(public_path($filePath));
		// 		$response['sticker_pdf'] = getSpaceFilePath($filePath);

		// 	}
		// }

	}

	function generateDispatchPDF($invoiceID, $fileName)
	{

		$data = array();

		// $invoiceID = 876;

		$Invoice = Invoice::query();
		$Invoice->select('invoice.id', 'invoice.dispatch_detail', 'invoice.eway_bill', 'invoice.created_at', 'orders.gst_number', 'orders.payment_mode', 'orders.sale_persons', 'invoice.total_mrp_minus_disocunt', 'invoice.total_mrp', 'invoice.gst_percentage', 'invoice.gst_tax', 'invoice.delievery_charge', 'invoice.total_payable', 'orders.bill_address_line1', 'orders.bill_address_line2', 'orders.bill_pincode', 'orders.bill_state_id', 'orders.bill_city_id', 'orders.bill_country_id', 'orders.d_address_line1', 'orders.d_address_line2', 'orders.d_pincode', 'orders.d_state_id', 'orders.d_city_id', 'orders.d_country_id', 'channel_partner_detail.email as channel_partner_email', 'channel_partner_detail.phone_number as channel_partner_phone_number', 'channel_partner_detail.dialing_code as channel_partner_dialing_code', 'channel_partner_detail.first_name as channel_partner_first_name', 'channel_partner_detail.last_name as channel_partner_last_name', 'channel_partner.firm_name as channel_partner_firm_name', 'channel_partner.type as channel_partner_type', 'channel_partner.credit_limit as channel_partner_credit_limit', 'channel_partner.credit_days as channel_partner_credit_days', 'channel_partner.pending_credit as channel_partner_pending_credit', 'channel_partner.credit_days as channel_partner_credit_days', 'channel_partner.reporting_manager_id as channel_partner_reporting_manager_id', 'channel_partner.reporting_company_id as channel_partner_reporting_company_id', 'courier_service_id', 'track_id');
		$Invoice->leftJoin('orders', 'invoice.order_id', '=', 'orders.id');
		$Invoice->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
		$Invoice->leftJoin('users as channel_partner_detail', 'channel_partner_detail.id', '=', 'channel_partner.user_id');
		$Invoice->where('invoice.id', $invoiceID);
		$Invoice = $Invoice->first();
		$noOfTotalBox = 0;

		if ($Invoice) {

			$billFrom = "";
			$billFromAddressline1 = "";
			$billFromAddressline2 = "";
			$billPincode = "";
			$billCountryName = "";
			$billStateName = "";
			$billCityName = "";

			$parentID = 0;

			if ($Invoice->channel_partner_reporting_manager_id != 0) {

				$ChannelPartner = ChannelPartner::select('firm_name', 'type', 'user_id');
				$ChannelPartner->where('user_id', $Invoice->channel_partner_reporting_manager_id);
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
				$Company->where('id', $Invoice->channel_partner_reporting_company_id);
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

			$DataMaster = DataMaster::select('name')->find($Invoice->courier_service_id);

			if ($DataMaster) {

				$courierService = $DataMaster->name;
			}

			$InvoicePacked = InvoicePacked::where('invoice_id', $invoiceID)->orderBy('id', 'asc')->get();

			$noOfTotalBox = count($InvoicePacked);
			$InvoicePacked = json_decode(json_encode($InvoicePacked), true);

			foreach ($InvoicePacked as $IKey => $IVal) {

				$InvoiceItem = InvoicePackedItem::query();
				$InvoiceItem->select('invoice_packed_items.id', 'invoice_packed_items.qty', 'product_inventory.image as product_image', 'product_brand.name as product_brand_name', 'product_code.name as product_code_name');
				$InvoiceItem->leftJoin('order_items', 'invoice_packed_items.order_item_id', '=', 'order_items.id');
				$InvoiceItem->leftJoin('product_inventory', 'product_inventory.id', '=', 'order_items.product_inventory_id');
				$InvoiceItem->leftJoin('data_master as product_brand', 'product_brand.id', '=', 'product_inventory.product_brand_id');
				$InvoiceItem->leftJoin('data_master as product_code', 'product_code.id', '=', 'product_inventory.product_code_id');
				$InvoiceItem->where('invoice_packed_items.invoice_packed_id', $IVal['id']);
				$InvoiceItem->orderBy('id', 'desc');
				$InvoiceItem = $InvoiceItem->get();
				$InvoiceItem = json_decode(json_encode($InvoiceItem), true);

				$someOfQty = array_column($InvoiceItem, 'qty');
				$someOfQty = array_sum($someOfQty);

				$InvoicePacked[$IKey]['items'] = $InvoiceItem;

				$InvoicePacked[$IKey]['total_items'] = $someOfQty;
			}

			$data = array();
			$data['invoice'] = $Invoice;
			$data['dispater_mobile_no'] = $DispaterMobileNo;
			$data['courier_service'] = $courierService;
			$data['track_id'] = $Invoice->track_id;
			$data['billFrom'] = $billFrom;
			$data['billFromAddressline1'] = $billFromAddressline1;
			$data['billFromAddressline2'] = $billFromAddressline2;
			$data['billPincode'] = $billPincode;
			$data['billCountryName'] = $billCountryName;
			$data['billStateName'] = $billStateName;
			$data['billCityName'] = $billCityName;
			$data['invoice'] = $Invoice;
			$data['invoice_items'] = $InvoicePacked;
			$data['no_of_total_box'] = $noOfTotalBox;
			$customPaper = array(0, 0, 75, 200);

			$PDF = PDF::loadView('invoice/dispatched_pdf', compact('data'))->setPaper($customPaper);

			//return $PDF->stream();
			$PDF->save($fileName);
		}
	}

	function dispatchedAjax(Request $request)
	{

		$isDispatcherUser = isDispatcherUser();
		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();

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
			15 => 'invoice.order_id',
			16 => 'orders.created_at as order_date_time',
			17 => 'invoice.dispatch_pdf',

		);

		$query = Invoice::query();
		$query->leftJoin('orders', 'invoice.order_id', '=', 'orders.id');
		$query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
		$query->where('invoice.status', 2);
		$query->where('invoice.is_cancelled', 0);


		if ($isDispatcherUser == 1 || $isAdminOrCompanyAdmin == 1) {
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

		$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.
		$query = Invoice::query();
		$query->leftJoin('orders', 'invoice.order_id', '=', 'orders.id');
		$query->leftJoin('users', 'users.id', '=', 'orders.user_id');
		$query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
		$query->where('invoice.status', 2);
		$query->where('invoice.is_cancelled', 0);

		if ($isDispatcherUser == 1 || $isAdminOrCompanyAdmin == 1) {

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
		//$query->where('channel_partner.reporting_company_id', Auth::user()->company_id);

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

			$data[$key]['detail'] = '<h5 class="font-size-14 mb-1" data-bs-toggle="tooltip" title="' . convertOrderDateTime($value['order_date_time'], "date") . ' ' . convertOrderDateTime($value['order_date_time'], "time") . '"><a href="javascript: void(0);" class="text-dark">#' . highlightString($value['order_id'],$search_value) . '</a></h5>
			    <p class="text-muted mb-0" data-bs-toggle="tooltip" title="INVOICE NO">' . (highlightString($value['invoice_number'],$search_value)) . '</p>
                <p class="text-muted mb-0" data-bs-toggle="tooltip"  data-bs-original-title="' . convertOrderDateTime($value['created_at'], "time") . '" >' . highlightString(convertOrderDateTime($value['created_at'], "date"),$search_value) . '</p>';

			//

			$paymentMode = "";

			$paymentMode = '<span class="badge rounded-pill badge-soft-dark font-size-11">' . getPaymentModeName($value['payment_mode']) . '</span>';

			$data[$key]['order_by'] = '<p class="text-muted mb-0">' . highlightString($value['first_name'] . '  ' . $value['last_name'],$search_value) . '</p>';
			$data[$key]['channel_partner'] = '<p class="text-muted mb-0">' . highlightString($value['firm_name'],$search_value) . '</p><p class="text-muted mb-0">' . $paymentMode . '</p>';

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

			$data[$key]['action_mark_recieved'] = '<a onclick="doMarkAsRecieved(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Mark As Packed"><span class="badge badge-pill badge-soft-dark font-size-11"> MARK AS RECIEVED</span></a>';

			$uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';

			$uiAction .= '<li class="list-inline-item px-2">';
			$uiAction .= '<a onclick="ViewInvoice(\'' . $value['id'] . '\')" href="javascript: void(0);" title="View"><i class="mdi mdi-eye"></i></a>';
			$uiAction .= '</li>';

			$uiAction .= '<li class="list-inline-item px-2">';
			$uiAction .= '<a target="_blank" href="' . getSpaceFilePath($value['invoice_file']) . '" title="PDF"><i class="bx bxs-file-pdf"></i></a>';
			$uiAction .= '</li>';

			if ($value['dispatch_pdf'] != "") {

				$uiAction .= '<li class="list-inline-item px-2">';
				$uiAction .= '<a target="_blank" href="' . getSpaceFilePath($value['dispatch_pdf']) . '" title="PDF"><i class="bx bxs-file-pdf"></i></a>';
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

			$Invoice = Invoice::find($request->id);
			if ($Invoice && $Invoice->is_cancelled == 0) {

				$isDispatcherUser = isDispatcherUser();
				$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();

				$Order = Order::query();
				$Order->select('orders.*', 'channel_partner.reporting_company_id');
				$Order->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');

				if ($isDispatcherUser == 1 || $isAdminOrCompanyAdmin == 1) {
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

				$Order = $Order->where('orders.id', $Invoice->order_id)->first();
				if ($Order) {

					$Invoice->status = 3;
					$Invoice->save();
					$lastInvoice = Invoice::select('id')->where('order_id', $Order->id)->orderBy('id', 'desc')->first();
					if ($lastInvoice && $lastInvoice->id == $Invoice->id) {

						$Order->sub_status = 3;
						$Order->save();
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

	function recievedAjax(Request $request)
	{

		$isDispatcherUser = isDispatcherUser();
		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();

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
			15 => 'invoice.order_id',
			16 => 'orders.created_at as order_date_time',

		);

		$query = Invoice::query();
		$query->leftJoin('orders', 'invoice.order_id', '=', 'orders.id');
		$query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
		$query->where('invoice.status', 3);
		$query->where('invoice.is_cancelled', 0);

		if ($isDispatcherUser == 1 || $isAdminOrCompanyAdmin == 1) {
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

		$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.
		$query = Invoice::query();
		$query->leftJoin('orders', 'invoice.order_id', '=', 'orders.id');
		$query->leftJoin('users', 'users.id', '=', 'orders.user_id');
		$query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');

		$query->where('invoice.status', 3);
		$query->where('invoice.is_cancelled', 0);

		if ($isDispatcherUser == 1 || $isAdminOrCompanyAdmin == 1) {
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

			$data[$key]['detail'] = '<h5 class="font-size-14 mb-1" data-bs-toggle="tooltip" title="' . convertOrderDateTime($value['order_date_time'], "date") . ' ' . convertOrderDateTime($value['order_date_time'], "time") . '"><a href="javascript: void(0);" class="text-dark">#' . highlightString($value['order_id'],$search_value) . '</a></h5>
			    <p class="text-muted mb-0" data-bs-toggle="tooltip" title="INVOICE NO">' . (highlightString($value['invoice_number'],$search_value)) . '</p>
                <p class="text-muted mb-0" data-bs-toggle="tooltip"  data-bs-original-title="' . convertOrderDateTime($value['created_at'], "time") . '" >' . highlightString(convertOrderDateTime($value['created_at'], "date"),$search_value) . '</p>';
			$paymentMode = "";

			$paymentMode = '<span class="badge rounded-pill badge-soft-dark font-size-11">' . getPaymentModeName($value['payment_mode']) . '</span>';

			$data[$key]['order_by'] = '<p class="text-muted mb-0">' . highlightString($value['first_name'] . '  ' . $value['last_name'],$search_value) . '</p>';
			$data[$key]['channel_partner'] = '<p class="text-muted mb-0">' . highlightString($value['firm_name'],$search_value) . '</p><p class="text-muted mb-0">' . $paymentMode . '</p>';

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

			$data[$key]['action_mark_recieved'] = '<a onclick="doMarkAsRecieved(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Mark As Packed"><span class="badge badge-pill badge-soft-dark font-size-11"> MARK AS RECIEVED</span></a>';

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


	function cancelledAjax(Request $request)
	{

		$isDispatcherUser = isDispatcherUser();
		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();

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
			15 => 'invoice.order_id',
			16 => 'orders.created_at as order_date_time',

		);

		$query = Invoice::query();
		$query->leftJoin('orders', 'invoice.order_id', '=', 'orders.id');
		$query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
		// $query->where('invoice.status', 3);
		$query->where('invoice.is_cancelled', 1);

		if ($isDispatcherUser == 1 || $isAdminOrCompanyAdmin == 1) {
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

		$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.
		$query = Invoice::query();
		$query->leftJoin('orders', 'invoice.order_id', '=', 'orders.id');
		$query->leftJoin('users', 'users.id', '=', 'orders.user_id');
		$query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');

		// $query->where('invoice.status', 3);
		$query->where('invoice.is_cancelled', 1);

		if ($isDispatcherUser == 1 || $isAdminOrCompanyAdmin == 1) {
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

			$data[$key]['detail'] = '<h5 class="font-size-14 mb-1" data-bs-toggle="tooltip" title="' . convertOrderDateTime($value['order_date_time'], "date") . ' ' . convertOrderDateTime($value['order_date_time'], "time") . '"><a href="javascript: void(0);" class="text-dark">#' . highlightString($value['order_id'],$search_value) . '</a></h5>
			    <p class="text-muted mb-0" data-bs-toggle="tooltip" title="INVOICE NO">' . (highlightString($value['invoice_number'],$search_value)) . '</p>
                <p class="text-muted mb-0" data-bs-toggle="tooltip"  data-bs-original-title="' . convertOrderDateTime($value['created_at'], "time") . '" >' . highlightString(convertOrderDateTime($value['created_at'], "date"),$search_value) . '</p>';
			$paymentMode = "";

			$paymentMode = '<span class="badge rounded-pill badge-soft-dark font-size-11">' . getPaymentModeName($value['payment_mode']) . '</span>';

			$data[$key]['order_by'] = '<p class="text-muted mb-0">' . highlightString($value['first_name'] . '  ' . $value['last_name'],$search_value) . '</p>';
			$data[$key]['channel_partner'] = '<p class="text-muted mb-0">' . highlightString($value['firm_name'],$search_value) . '</p><p class="text-muted mb-0">' . $paymentMode . '</p>';

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

			$status = getInvoiceLable(4);

			$data[$key]['status'] = $status . " - " . getInvoiceLable($value['status']);



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

	public function detail(Request $request)
	{

		$isDispatcherUser = isDispatcherUser();
		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();

		$Order = Invoice::query();
		$Order->select('invoice.id', 'invoice.dispatch_detail', 'invoice.eway_bill', 'invoice.created_at', 'orders.gst_number', 'orders.payment_mode', 'orders.sale_persons', 'invoice.total_mrp_minus_disocunt', 'invoice.total_mrp', 'invoice.gst_percentage', 'invoice.gst_tax', 'invoice.delievery_charge', 'invoice.total_payable', 'orders.bill_address_line1', 'orders.bill_address_line2', 'orders.bill_pincode', 'orders.bill_state_id', 'orders.bill_city_id', 'orders.bill_country_id', 'orders.d_address_line1', 'orders.d_address_line2', 'orders.d_pincode', 'orders.d_state_id', 'orders.d_city_id', 'orders.d_country_id', 'channel_partner_detail.email as channel_partner_email', 'channel_partner_detail.phone_number as channel_partner_phone_number', 'channel_partner_detail.dialing_code as channel_partner_dialing_code', 'channel_partner_detail.first_name as channel_partner_first_name', 'channel_partner_detail.last_name as channel_partner_last_name', 'channel_partner.firm_name as channel_partner_firm_name', 'channel_partner.type as channel_partner_type', 'channel_partner.credit_limit as channel_partner_credit_limit', 'channel_partner.credit_days as channel_partner_credit_days', 'channel_partner.pending_credit as channel_partner_pending_credit', 'channel_partner.credit_days as channel_partner_credit_days', 'invoice.track_id', 'invoice.courier_service_id');
		$Order->leftJoin('orders', 'invoice.order_id', '=', 'orders.id');
		$Order->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
		$Order->leftJoin('users as channel_partner_detail', 'channel_partner_detail.id', '=', 'channel_partner.user_id');
		$Order->where('invoice.id', $request->invoice_id);

		if ($isDispatcherUser == 1 || $isAdminOrCompanyAdmin == 1) {
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

			if ($Order->dispatch_detail != "") {
				$Order->dispatch_detail = explode(",", $Order->dispatch_detail);
			} else {
				$Order->dispatch_detail = array();
			}

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
			$OrderItem->select('invoice_items.id', 'invoice_items.qty', 'invoice_items.total_mrp', 'product_inventory.image as product_image', 'product_brand.name as product_brand_name', 'product_code.name as product_code_name', 'invoice_items.pending_packed_qty as pending_qty', 'order_items.mrp', 'order_items.discount_percentage', 'order_items.weight');
			$OrderItem->leftJoin('order_items', 'invoice_items.order_item_id', '=', 'order_items.id');
			$OrderItem->leftJoin('product_inventory', 'product_inventory.id', '=', 'order_items.product_inventory_id');
			$OrderItem->leftJoin('data_master as product_brand', 'product_brand.id', '=', 'product_inventory.product_brand_id');
			$OrderItem->leftJoin('data_master as product_code', 'product_code.id', '=', 'product_inventory.product_code_id');

			$OrderItem->where('invoice_id', $Order->id);
			$OrderItem->orderBy('id', 'desc');
			$OrderItem = $OrderItem->get();

			$Order['items'] = $OrderItem;
			$courierService = "";

			$DataMaster = DataMaster::select('name')->find($Order->courier_service_id);

			if ($DataMaster) {

				$courierService = $DataMaster->name;
			}

			$Order['courier_service'] = $courierService;

			$InvoicePacked = InvoicePacked::where('invoice_id', $Order->id)->orderBy('id', 'desc')->first();
			$departmentName = "";

			if ($InvoicePacked) {

				$departmentName = $InvoicePacked->department_name;
			}
			$Order['department_name'] = $departmentName;

			$response = successRes("Order detail");
			$response['data'] = $Order;
			$InvoicePacked = InvoicePacked::where('invoice_id', $request->invoice_id)->count();
			$response['no_of_box'] = $InvoicePacked + 1;
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
				$orderItems[$key]['info']['product_brand_name'] = $value['product_brand_name'];
				$orderItems[$key]['info']['product_code_name'] = $value['product_code_name'];
				// $orderItems[$key]['info']['description'] = $value['description'];
				$orderItems[$key]['info']['product_image'] = $value['product_image'];
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
}
