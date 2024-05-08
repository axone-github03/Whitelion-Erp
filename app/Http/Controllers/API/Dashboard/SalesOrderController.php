<?php

namespace App\Http\Controllers\API\Dashboard;

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

			$tabCanAccessBy = array(0, 1, 2, 101, 102, 103, 104);

			if (!in_array(Auth::user()->type, $tabCanAccessBy)) {

				$response = errorRes("Invalid access", 401);
				return response()->json($response, $response['status_code'])->header('Content-Type', 'application/json');
			}
			$MyPrivilege = getMyPrivilege('dashboard');
			if ($MyPrivilege == 0) {
				$response = errorRes("Invalid access", 402);
				return response()->json($response, $response['status_code'])->header('Content-Type', 'application/json');
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
			$ChannelPartner->whereIn('channel_partner.type', array(101, 102, 103, 104));
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

			$response = successRes("Channel Partner");
			$response['data'] = $ChannelPartner;
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
			$response = successRes("Sales Person");
			$response['data'] = $UserResponse;
			return response()->json($response)->header('Content-Type', 'application/json');
		}
	}

	public function saleOrdercount(Request $request)
	{

		$startDate = date('Y-m-d', strtotime($request->start_date));
		// $startDate = date('Y-m-d H:i:s', strtotime($startDate . " -5 hours"));
		// $startDate = date('Y-m-d H:i:s', strtotime($startDate . " -30 minutes"));

		$endDate = date('Y-m-d', strtotime($request->end_date));
		// $endDate = date('Y-m-d H:i:s', strtotime($endDate . " -5 hours"));
		// $endDate = date('Y-m-d H:i:s', strtotime($endDate . " -30 minutes"));

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
			//$orderTotal->leftJoin('users', 'users.id', '=', 'orders.user_id');
			$orderTotal->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
			$orderTotal->where('orders.status', '!=', 4);
			$orderTotal->whereDate('orders.created_at', '>=', $startDate);
			$orderTotal->whereDate('orders.created_at', '<=', $endDate);
			if ($request->channel_partner_type != 0) {

				$orderTotal->where('channel_partner.type', $request->channel_partner_type);
			}

			if ($isAdminOrCompanyAdmin == 1) {

				if ($hasFilter == 0) {

					$orderTotal->where('channel_partner.reporting_manager_id', 0);
					$orderTotal->where('channel_partner.reporting_company_id', Auth::user()->company_id);
				}

				// $orderTotal->where('channel_partner.reporting_manager_id', 0);
				// $orderTotal->where('channel_partner.reporting_company_id', Auth::user()->company_id);

			} else if ($isSalePerson == 1) {

				if ($hasFilter == 0) {

					$orderTotal->whereIn('orders.user_id', $childSalePersonsIds);
				}

				//$orderTotal->whereIn('orders.user_id', $childSalePersonsIds);

				// $orderTotal->where(function ($query) use ($childSalePersonsIds) {

				// 	foreach ($childSalePersonsIds as $key => $value) {
				// 		if ($key == 0) {
				// 			$query->whereRaw('FIND_IN_SET("' . $value . '",orders.sale_persons)>0');
				// 		} else {
				// 			$query->orWhereRaw('FIND_IN_SET("' . $value . '",orders.sale_persons)>0');
				// 		}

				// 	}
				// });

				// $orderTotal->where('channel_partner.reporting_company_id', Auth::user()->company_id);
			}

			if (isset($request->channel_partner_user_id) && is_array($request->channel_partner_user_id)) {

				// print_r($request->channel_partner_user_id);
				// die;

				$orderTotal->whereIn('orders.channel_partner_user_id', $request->channel_partner_user_id);

				// $orderTotal->whereIn('orders.user_id', $request->channel_partner_user_id);

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
				// $orderTotal->where(function ($query) use ($salesUserIds) {

				// 	foreach ($salesUserIds as $keyS => $valueS) {

				// 		if ($keyS == 0) {
				// 			$query->WhereRaw('FIND_IN_SET("' . $valueS . '",orders.sale_persons)>0');

				// 		} else {
				// 			$query->orWhereRaw('FIND_IN_SET("' . $valueS . '",orders.sale_persons)>0');

				// 		}

				// 	}

				// });

			}

			$orderTotal = $orderTotal->count();

			//dd(DB::getQueryLog());

			////
			$orderTotalAmount = Order::query();
			$orderTotalAmount->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
			// $orderTotalAmount->leftJoin('users', 'users.id', '=', 'orders.user_id');
			$orderTotalAmount->where('orders.status', '!=', 4);
			$orderTotalAmount->whereDate('orders.created_at', '>=', $startDate);
			$orderTotalAmount->whereDate('orders.created_at', '<=', $endDate);

			if ($request->channel_partner_type != 0) {
				$orderTotalAmount->where('channel_partner.type', $request->channel_partner_type);
			}

			if ($isAdminOrCompanyAdmin == 1) {

				if ($hasFilter == 0) {

					$orderTotalAmount->where('channel_partner.reporting_manager_id', 0);
					$orderTotalAmount->where('channel_partner.reporting_company_id', Auth::user()->company_id);
				}

				// $orderTotalAmount->where('channel_partner.reporting_manager_id', 0);
				// $orderTotalAmount->where('channel_partner.reporting_company_id', Auth::user()->company_id);

			} else if ($isSalePerson == 1) {

				if ($hasFilter == 0) {

					$orderTotalAmount->whereIn('orders.user_id', $childSalePersonsIds);
				}

				//$orderTotalAmount->whereIn('orders.user_id', $childSalePersonsIds);

				// $orderTotalAmount->where(function ($query) use ($childSalePersonsIds) {

				// 	foreach ($childSalePersonsIds as $key => $value) {
				// 		if ($key == 0) {
				// 			$query->whereRaw('FIND_IN_SET("' . $value . '",orders.sale_persons)>0');
				// 		} else {
				// 			$query->orWhereRaw('FIND_IN_SET("' . $value . '",orders.sale_persons)>0');
				// 		}

				// 	}
				// });
				// $orderTotalAmount->where('channel_partner.reporting_company_id', Auth::user()->company_id);
			}

			if (isset($request->channel_partner_user_id) && is_array($request->channel_partner_user_id)) {

				$orderTotalAmount->whereIn('orders.channel_partner_user_id', $request->channel_partner_user_id);

				// $orderTotalAmount->whereIn('orders.user_id', $request->channel_partner_user_id);

			}
			if (isset($request->sales_user_id) && is_array($request->sales_user_id)) {

				//if ($isSalePerson == 1) {

				$salesUserIds = $request->sales_user_id;
				$allSalesUserIds = [];

				foreach ($salesUserIds as $key => $value) {

					$childSalePersonsIds1 = getChildSalePersonsIds($value);

					$allSalesUserIds = array_merge($allSalesUserIds, $childSalePersonsIds1);
				}
				$allSalesUserIds = array_unique($allSalesUserIds);
				$allSalesUserIds = array_values($allSalesUserIds);

				$orderTotalAmount->whereIn('orders.user_id', $allSalesUserIds);

				// $orderTotalAmount->where(function ($query) use ($salesUserIds, $childSalePersonsIds) {

				// 	foreach ($salesUserIds as $keyS => $valueS) {

				// 		if ($keyS == 0) {

				// 			$query->WhereRaw('FIND_IN_SET("' . $valueS . '",orders.sale_persons)>0');

				// 		} else {

				// 			$query->orWhereRaw('FIND_IN_SET("' . $valueS . '",orders.sale_persons)>0');

				// 		}

				// 	}

				// });

				//} else {

				//$salesUserIds = $request->sales_user_id;

				// $orderTotalAmount->where(function ($query) use ($salesUserIds) {

				// 	foreach ($salesUserIds as $keyS => $valueS) {

				// 		if ($keyS == 0) {

				// 			$query->WhereRaw('FIND_IN_SET("' . $valueS . '",orders.sale_persons)>0');

				// 		} else {

				// 			$query->orWhereRaw('FIND_IN_SET("' . $valueS . '",orders.sale_persons)>0');

				// 		}

				// 	}

				// });

				//}

			}

			$orderTotalAmount = $orderTotalAmount->sum('actual_total_mrp_minus_disocunt');

			////
			$orderTotalAmountDispatched = Invoice::query();
			// $orderTotalAmountDispatched->leftJoin('users', 'users.id', '=', 'orders.user_id');
			$orderTotalAmountDispatched->leftJoin('orders', 'orders.id', '=', 'invoice.order_id');
			$orderTotalAmountDispatched->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
			$orderTotalAmountDispatched->whereIn('invoice.status', array(2, 3));
			$orderTotalAmountDispatched->whereDate('orders.created_at', '>=', $startDate);
			$orderTotalAmountDispatched->whereDate('orders.created_at', '<=', $endDate);

			if ($request->channel_partner_type != 0) {
				$orderTotalAmountDispatched->where('channel_partner.type', $request->channel_partner_type);
			}

			if ($isAdminOrCompanyAdmin == 1) {

				if ($hasFilter == 0) {

					$orderTotalAmountDispatched->where('channel_partner.reporting_manager_id', 0);
					$orderTotalAmountDispatched->where('channel_partner.reporting_company_id', Auth::user()->company_id);
				}

				// $orderTotalAmountDispatched->where('channel_partner.reporting_manager_id', 0);
				// $orderTotalAmountDispatched->where('channel_partner.reporting_company_id', Auth::user()->company_id);

			} else if ($isSalePerson == 1) {

				if ($hasFilter == 0) {

					$orderTotalAmountDispatched->whereIn('orders.user_id', $childSalePersonsIds);
				}

				//$orderTotalAmountDispatched->whereIn('orders.user_id', $childSalePersonsIds);

				// $orderTotalAmountDispatched->where(function ($query) use ($childSalePersonsIds) {

				// 	foreach ($childSalePersonsIds as $key => $value) {
				// 		if ($key == 0) {
				// 			$query->whereRaw('FIND_IN_SET("' . $value . '",orders.sale_persons)>0');
				// 		} else {
				// 			$query->orWhereRaw('FIND_IN_SET("' . $value . '",orders.sale_persons)>0');
				// 		}

				// 	}
				// });

				// $orderTotalAmountDispatched->where('channel_partner.reporting_company_id', Auth::user()->company_id);
			}

			if (isset($request->channel_partner_user_id) && is_array($request->channel_partner_user_id)) {

				$orderTotalAmountDispatched->whereIn('orders.channel_partner_user_id', $request->channel_partner_user_id);

				// $orderTotalAmountDispatched->whereIn('orders.user_id', $request->channel_partner_user_id);

			}
			if (isset($request->sales_user_id) && is_array($request->sales_user_id)) {

				//if ($isSalePerson == 1) {

				$salesUserIds = $request->sales_user_id;
				$allSalesUserIds = [];

				foreach ($salesUserIds as $key => $value) {

					$childSalePersonsIds1 = getChildSalePersonsIds($value);

					$allSalesUserIds = array_merge($allSalesUserIds, $childSalePersonsIds1);
				}
				$allSalesUserIds = array_unique($allSalesUserIds);
				$allSalesUserIds = array_values($allSalesUserIds);

				$orderTotalAmountDispatched->whereIn('orders.user_id', $allSalesUserIds);

				// $orderTotalAmountDispatched->where(function ($query) use ($salesUserIds, $childSalePersonsIds) {

				// 	foreach ($salesUserIds as $keyS => $valueS) {

				// 		if ($keyS == 0) {

				// 			$query->WhereRaw('FIND_IN_SET("' . $valueS . '",orders.sale_persons)>0');

				// 		} else {

				// 			$query->orWhereRaw('FIND_IN_SET("' . $valueS . '",orders.sale_persons)>0');

				// 		}

				// 	}

				// });

				// if (isset($request->sales_user_id) && $request->user_id != "" && in_array($request->sales_user_id, $childSalePersonsIds)) {

				// 	$orderTotalAmountDispatched->WhereRaw('FIND_IN_SET("' . $request->sales_user_id . '",orders.sale_persons)>0');
				// }
				// } else {

				// 	$salesUserIds = $request->sales_user_id;
				// 	$orderTotalAmountDispatched->whereIn('orders.user_id', $salesUserIds);

				// $orderTotalAmountDispatched->where(function ($query) use ($salesUserIds) {

				// 	foreach ($salesUserIds as $keyS => $valueS) {

				// 		if ($keyS == 0) {

				// 			$query->WhereRaw('FIND_IN_SET("' . $valueS . '",orders.sale_persons)>0');

				// 		} else {

				// 			$query->orWhereRaw('FIND_IN_SET("' . $valueS . '",orders.sale_persons)>0');

				// 		}

				// 	}

				// });
				// $orderTotalAmountDispatched->WhereRaw('FIND_IN_SET("' . $request->sales_user_id . '",orders.sale_persons)>0');

				//}

			}

			$orderTotalAmountDispatched = $orderTotalAmountDispatched->sum('invoice.total_mrp_minus_disocunt');

			$response = successRes("Get Sales Order Count");
			$response['order_count'] = "" . ($orderTotal);
			$response['order_total_amount'] = "" . priceLable($orderTotalAmount);
			$response['order_dispateched_amount'] = "" . priceLable($orderTotalAmountDispatched);
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
			}
			$orderTotalAmountDispatched = $orderTotalAmountDispatched->sum('invoice.total_mrp_minus_disocunt');

			$response = successRes("Get Sales Order Count");
			$response['order_count'] = "" . ($orderTotal);
			$response['order_total_amount'] = "" . priceLable($orderTotalAmount);
			$response['order_dispateched_amount'] = "" . priceLable($orderTotalAmountDispatched);
			return response()->json($response)->header('Content-Type', 'application/json');
		}
	}
}
