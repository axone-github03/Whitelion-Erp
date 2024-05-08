<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ChannelPartner;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\User;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SalesOrderController extends Controller
{
	//
	public function __construct()
	{

		$this->middleware(function ($request, $next) {

			$tabCanAccessBy = array(0,1,2, 101, 102, 103, 104, 105);

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

	public function searchChannelPartner(Request $request)
	{

		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		$isSalePerson = isSalePerson();
		$isChannelPartner = isChannelPartner(Auth::user()->type);
		if ($isAdminOrCompanyAdmin == 1 || $isSalePerson == 1) {

			$ChannelPartner = array();
			$ChannelPartner = ChannelPartner::select('channel_partner.user_id as id', DB::raw('CONCAT(channel_partner.firm_name) as text'));
			$ChannelPartner->leftJoin('users', 'users.id', '=', 'channel_partner.user_id');
			$ChannelPartner->whereIn('channel_partner.type', array(101, 102, 103, 104, 105));
			$ChannelPartner->where('users.status', 1);
			if ($request->type != 0) {

				$ChannelPartner->where('channel_partner.type', $request->type);
			}

			$q = $request->q;

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
			}

			$ChannelPartner->where(function ($query) use ($q) {
				$query->where('channel_partner.firm_name', 'like', '%' . $q . '%');
			});

			$ChannelPartner->limit(10);
			$ChannelPartner = $ChannelPartner->get();

			$response = array();
			$response['results'] = $ChannelPartner;
			$response['pagination']['more'] = false;
			return response()->json($response)->header('Content-Type', 'application/json');
		}
	}

	public function searchUser(Request $request)
	{

		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		$isSalePerson = isSalePerson();
		$isChannelPartner = isChannelPartner(Auth::user()->type);
		if ($isAdminOrCompanyAdmin == 1 || $isSalePerson == 1) {

			if ($isSalePerson == 1) {
				$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
			}

			$User = $UserResponse = array();
			$q = $request->q;
			$User = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
			$User->where('users.type', 2);
			$User->where('users.status', 1);
			if ($isSalePerson == 1) {
				$User->whereIn('id', $childSalePersonsIds);
			}
			$User->where(function ($query) use ($q) {
				$query->where('users.first_name', 'like', '%' . $q . '%');
				$query->orWhere('users.last_name', 'like', '%' . $q . '%');
			});
			$User->limit(5);
			$User = $User->get();

			if (count($User) > 0) {
				foreach ($User as $User_key => $User_value) {
					$UserResponse[$User_key]['id'] = $User_value['id'];
					$UserResponse[$User_key]['text'] = $User_value['full_name'];
				}
			}
			$response = array();
			$response['results'] = $UserResponse;
			$response['pagination']['more'] = false;
			return response()->json($response)->header('Content-Type', 'application/json');
		}
	}

	public function saleOrdercount(Request $request)
	{

		$startDate = date('Y-m-d', strtotime($request->start_date));

		$endDate = date('Y-m-d', strtotime($request->end_date));

		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		$isSalePerson = isSalePerson();
		$isChannelPartner = isChannelPartner(Auth::user()->type);

		if ($isAdminOrCompanyAdmin == 1 || $isSalePerson == 1) {

			if ($isSalePerson == 1) {
				$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
			}

			$hasFilter = 0;
			if (isset($request->channel_partner_user_id) && is_array($request->channel_partner_user_id)) {
				$hasFilter = 1;
			}
			if (isset($request->sales_user_id) && is_array($request->sales_user_id)) {
				$hasFilter = 1;
			}
			if ($request->channel_partner_type != 0 && $request->channel_partner_type == 101) {
				$hasFilter = 1;
			}

			DB::enableQueryLog();

			$orderTotal = Order::query();
			$orderTotal->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
			$orderTotal->where('orders.status', '!=', 4);
			$orderTotal->whereDate('orders.created_at', '>=', $startDate);
			$orderTotal->whereDate('orders.created_at', '<=', $endDate);
			$orderTotal->orderBy('orders.id', 'desc');
			if ($request->channel_partner_type != 0) {

				$orderTotal->where('channel_partner.type', $request->channel_partner_type);
			}else{
				$orderTotal->where('channel_partner.type', '!=',104);
				$orderTotal->where('channel_partner.type', '!=',105);
			}

			if ($isAdminOrCompanyAdmin == 1) {

				if ($hasFilter == 0) {

					$orderTotal->where('channel_partner.reporting_manager_id', 0);
					$orderTotal->where('channel_partner.reporting_company_id', Auth::user()->company_id);
				}

			} else if ($isSalePerson == 1) {

				if ($hasFilter == 0) {

					$orderTotal->where(function ($query) use ($childSalePersonsIds) {

						foreach ($childSalePersonsIds as $key => $value) {
							if ($key == 0) {
								$query->whereRaw('FIND_IN_SET("' . $value . '",orders.sale_persons)>0');
							} else {
								$query->orWhereRaw('FIND_IN_SET("' . $value . '",orders.sale_persons)>0');
							}
						}
					});
				}
			}

			if (isset($request->channel_partner_user_id) && is_array($request->channel_partner_user_id)) {



				$orderTotal->whereIn('orders.channel_partner_user_id', $request->channel_partner_user_id);
			}

			if (isset($request->sales_user_id) && is_array($request->sales_user_id)) {

				$salesUserIds = $request->sales_user_id;
				$allSalesUserIds = [];

				foreach ($salesUserIds as $key => $value) {

					$childSalePersonsIds1 = getChildSalePersonsIds($value);

					$allSalesUserIds = array_merge($allSalesUserIds, $childSalePersonsIds1);
				}
				$allSalesUserIds = array_unique($allSalesUserIds);
				$allSalesUserIds = array_values($allSalesUserIds);

				$orderTotal->whereIn('orders.user_id', $allSalesUserIds);

			}

			$orderids = $orderTotal->pluck('orders.id');
			$orderTotal = $orderTotal->count();

			$orderTotalAmount = Order::query();
			$orderTotalAmount->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
			$orderTotalAmount->where('orders.status', '!=', 4);
			$orderTotalAmount->whereDate('orders.created_at', '>=', $startDate);
			$orderTotalAmount->whereDate('orders.created_at', '<=', $endDate);
			$orderTotalAmount->orderBy('orders.id', 'desc');
			if ($request->channel_partner_type != 0) {
				$orderTotalAmount->where('channel_partner.type', $request->channel_partner_type);
			}else{
				$orderTotalAmount->where('channel_partner.type', '!=',104);
				$orderTotalAmount->where('channel_partner.type', '!=',105);
			}
			if ($isAdminOrCompanyAdmin == 1) {

				if ($hasFilter == 0) {

					$orderTotalAmount->where('channel_partner.reporting_manager_id', 0);
					$orderTotalAmount->where('channel_partner.reporting_company_id', Auth::user()->company_id);
				}


			} else if ($isSalePerson == 1) {

				if ($hasFilter == 0) {

					$orderTotalAmount->whereIn('orders.user_id', $childSalePersonsIds);
				}

			}
			if (isset($request->channel_partner_user_id) && is_array($request->channel_partner_user_id)) {

				$orderTotalAmount->whereIn('orders.channel_partner_user_id', $request->channel_partner_user_id);

			}
			if (isset($request->sales_user_id) && is_array($request->sales_user_id)) {

				$salesUserIds = $request->sales_user_id;
				$allSalesUserIds = [];

				foreach ($salesUserIds as $key => $value) {

					$childSalePersonsIds1 = getChildSalePersonsIds($value);

					$allSalesUserIds = array_merge($allSalesUserIds, $childSalePersonsIds1);
				}
				$allSalesUserIds = array_unique($allSalesUserIds);
				$allSalesUserIds = array_values($allSalesUserIds);

				$orderTotalAmount->whereIn('orders.user_id', $allSalesUserIds);

			}
			$orderTotalAmount = $orderTotalAmount->sum('total_mrp_minus_disocunt');

			$orderTotalAmountDispatched = Invoice::query();
			$orderTotalAmountDispatched->leftJoin('orders', 'orders.id', '=', 'invoice.order_id');
			$orderTotalAmountDispatched->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
			$orderTotalAmountDispatched->whereIn('invoice.status', array(2, 3));

			$orderTotalAmountDispatched->whereDate('orders.created_at', '>=', $startDate);
			$orderTotalAmountDispatched->whereDate('orders.created_at', '<=', $endDate);
			$orderTotalAmountDispatched->orderBy('orders.id', 'desc');

			if ($request->channel_partner_type != 0) {
				$orderTotalAmountDispatched->where('channel_partner.type', $request->channel_partner_type);
			}else{
				$orderTotalAmountDispatched->where('channel_partner.type', '!=',104);
				$orderTotalAmountDispatched->where('channel_partner.type', '!=',105);
			}

			if ($isAdminOrCompanyAdmin == 1) {

				if ($hasFilter == 0) {

					$orderTotalAmountDispatched->where('channel_partner.reporting_manager_id', 0);
					$orderTotalAmountDispatched->where('channel_partner.reporting_company_id', Auth::user()->company_id);
				}
			} else if ($isSalePerson == 1) {

				if ($hasFilter == 0) {

					$orderTotalAmountDispatched->whereIn('orders.user_id', $childSalePersonsIds);
				}
			}

			if (isset($request->channel_partner_user_id) && is_array($request->channel_partner_user_id)) {

				$orderTotalAmountDispatched->whereIn('orders.channel_partner_user_id', $request->channel_partner_user_id);
			}
			if (isset($request->sales_user_id) && is_array($request->sales_user_id)) {

				$salesUserIds = $request->sales_user_id;
				$allSalesUserIds = [];

				foreach ($salesUserIds as $key => $value) {

					$childSalePersonsIds1 = getChildSalePersonsIds($value);

					$allSalesUserIds = array_merge($allSalesUserIds, $childSalePersonsIds1);
				}
				$allSalesUserIds = array_unique($allSalesUserIds);
				$allSalesUserIds = array_values($allSalesUserIds);

				$orderTotalAmountDispatched->whereIn('orders.user_id', $allSalesUserIds);

			}

			$orderTotalAmountDispatched = $orderTotalAmountDispatched->sum('invoice.total_mrp_minus_disocunt');

			$response = successRes("Get Sales Order Count");
			$response['order_count_ids'] = $orderids;
			$response['order_count'] = ($orderTotal);
			$response['order_total_amount'] = priceLable($orderTotalAmount);
			$response['order_dispateched_amount'] = priceLable($orderTotalAmountDispatched);
			return response()->json($response)->header('Content-Type', 'application/json');
		} else if ($isChannelPartner != 0) {

			$orderTotal = Order::query();
			$orderTotal->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
			$orderTotal->where('orders.status', '!=', 4);
			$orderTotal->whereDate('orders.created_at', '>=', $startDate);
			$orderTotal->whereDate('orders.created_at', '<=', $endDate);
			$orderTotal->where('orders.channel_partner_user_id', Auth::user()->id);
			if ($request->channel_partner_type != 0) {

				$orderTotal->where('channel_partner.type', $request->channel_partner_type);
			}else{
				$orderTotal->where('channel_partner.type', '!=',104);
			}
			$orderTotal = $orderTotal->count();

			$orderTotalAmount = Order::query();
			$orderTotalAmount->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
			$orderTotalAmount->where('orders.status', '!=', 4);
			$orderTotalAmount->whereDate('orders.created_at', '>=', $startDate);
			$orderTotalAmount->whereDate('orders.created_at', '<=', $endDate);
			$orderTotalAmount->where('orders.channel_partner_user_id', Auth::user()->id);
			if ($request->channel_partner_type != 0) {

				$orderTotalAmount->where('channel_partner.type', $request->channel_partner_type);
			}else{
				$orderTotalAmount->where('channel_partner.type', '!=',104);
			}

			$orderTotalAmount = $orderTotalAmount->sum('actual_total_mrp_minus_disocunt');

			$orderTotalAmountDispatched = Invoice::query();

			$orderTotalAmountDispatched->leftJoin('orders', 'orders.id', '=', 'invoice.order_id');
			$orderTotalAmountDispatched->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
			$orderTotalAmountDispatched->whereIn('invoice.status', array(2, 3));
			$orderTotalAmountDispatched->whereDate('orders.created_at', '>=', $startDate);
			$orderTotalAmountDispatched->whereDate('orders.created_at', '<=', $endDate);
			$orderTotalAmountDispatched->where('orders.channel_partner_user_id', Auth::user()->id);

			if ($request->channel_partner_type != 0) {

				$orderTotalAmountDispatched->where('channel_partner.type', $request->channel_partner_type);
			}else{
				$orderTotalAmountDispatched->where('channel_partner.type', '!=',104);
			}
			$orderTotalAmountDispatched = $orderTotalAmountDispatched->sum('invoice.total_mrp_minus_disocunt');

			$response = successRes("Get Sales Order Count");
			$response['order_count'] = ($orderTotal);
			$response['order_total_amount'] = priceLable($orderTotalAmount);
			$response['order_dispateched_amount'] = priceLable($orderTotalAmountDispatched);
			return response()->json($response)->header('Content-Type', 'application/json');
		}
	}

	
}
