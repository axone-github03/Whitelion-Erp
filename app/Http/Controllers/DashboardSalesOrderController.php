<?php

namespace App\Http\Controllers;
use App\Models\Architect;
use App\Models\Inquiry;
use App\Models\Order;
use App\Models\User;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DashboardSalesOrderController extends Controller {

	public function __construct() {

		$this->middleware(function ($request, $next) {

			$tabCanAccessBy = array(0, 1, 2);

			if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
				return redirect()->route('dashboard');

			}

			return $next($request);

		});

	}

	public function saleOrdercount(Request $request) {

		$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
		$timeInteval = explode("|", $request->time_interval);

		$orderTotal = Order::query();
		$orderTotal->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
		$orderTotal->where('orders.status', '!=', 4);
		$orderTotal->where('orders.created_at', '>=', $timeInteval[0]);
		$orderTotal->where('orders.created_at', '<=', $timeInteval[1]);
		$orderTotal->where(function ($query) use ($childSalePersonsIds) {

			foreach ($childSalePersonsIds as $key => $value) {
				if ($key == 0) {
					$query->whereRaw('FIND_IN_SET("' . $value . '",orders.sale_persons)>0');
				} else {
					$query->orWhereRaw('FIND_IN_SET("' . $value . '",orders.sale_persons)>0');
				}

			}
		});

		$orderTotal->where('channel_partner.reporting_company_id', Auth::user()->company_id);

		$orderTotal = $orderTotal->count();
		////
		$orderTotalAmount = Order::query();
		$orderTotalAmount->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
		$orderTotalAmount->where('orders.status', '!=', 4);
		$orderTotalAmount->where('orders.created_at', '>=', $timeInteval[0]);
		$orderTotalAmount->where('orders.created_at', '<=', $timeInteval[1]);
		$orderTotalAmount->where(function ($query) use ($childSalePersonsIds) {

			foreach ($childSalePersonsIds as $key => $value) {
				if ($key == 0) {
					$query->whereRaw('FIND_IN_SET("' . $value . '",orders.sale_persons)>0');
				} else {
					$query->orWhereRaw('FIND_IN_SET("' . $value . '",orders.sale_persons)>0');
				}

			}
		});

		$orderTotalAmount->where('channel_partner.reporting_company_id', Auth::user()->company_id);

		$orderTotalAmount = $orderTotalAmount->sum('total_payable');
		////
		$orderTotalAmountDispatched = Order::query();
		$orderTotalAmountDispatched->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
		$orderTotalAmountDispatched->where('orders.status', 3);
		$orderTotalAmountDispatched->where('orders.created_at', '>=', $timeInteval[0]);
		$orderTotalAmountDispatched->where('orders.created_at', '<=', $timeInteval[1]);
		$orderTotalAmountDispatched->where(function ($query) use ($childSalePersonsIds) {

			foreach ($childSalePersonsIds as $key => $value) {
				if ($key == 0) {
					$query->whereRaw('FIND_IN_SET("' . $value . '",orders.sale_persons)>0');
				} else {
					$query->orWhereRaw('FIND_IN_SET("' . $value . '",orders.sale_persons)>0');
				}

			}
		});

		$orderTotalAmountDispatched->where('channel_partner.reporting_company_id', Auth::user()->company_id);
		$orderTotalAmountDispatched = $orderTotalAmountDispatched->sum('total_payable');

		$response = successRes("Get Sales Order Count");
		$response['order_count'] = ($orderTotal);
		$response['order_total_amount'] = priceLable($orderTotalAmount);
		$response['order_dispateched_amount'] = priceLable($orderTotalAmountDispatched);
		return response()->json($response)->header('Content-Type', 'application/json');

	}

	function inquiryCount(Request $request) {
		$isSalePerson = isSalePerson();

		$rules = array();
		$rules['start_date'] = 'required';
		$rules['end_date'] = 'required';
		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			$response = array();
			$response['status'] = 0;
			$response['msg'] = "The request could not be understood by the server due to malformed syntax";
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();

		} else {

			$startDate = date('Y-m-d 00:00:00', strtotime($request->start_date));
			$startDate = date('Y-m-d H:i:s', strtotime($startDate . " +5 hours"));
			$startDate = date('Y-m-d H:i:s', strtotime($startDate . " +30 minutes"));

			$endDate = date('Y-m-d 00:00:00', strtotime($request->end_date));
			$endDate = date('Y-m-d H:i:s', strtotime($endDate . " +5 hours"));
			$endDate = date('Y-m-d H:i:s', strtotime($endDate . " +30 minutes"));

			$Inquiry = Inquiry::query();
			$Inquiry->where('created_at', '>=', $startDate);
			$Inquiry->where('created_at', '<=', $endDate);

			if (isset($request->user_id) && $request->user_id != "") {

				if ($isSalePerson == 1) {

					$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
					if (in_array($request->user_id, $childSalePersonsIds)) {
						$Inquiry->where('user_id', $request->user_id);
					} else {
						$Inquiry->where('user_id', 0);
					}

				} else {
					$Inquiry->where('user_id', $request->user_id);
				}

			} else {
				$Inquiry->where('user_id', Auth::user()->id);
			}

			$InquiryCount = $Inquiry->count();

			$Architect = Architect::query();
			$Architect->where('created_at', '>=', $startDate);
			$Architect->where('created_at', '<=', $endDate);
			$Architect->where('type', 202);

			if (isset($request->user_id) && $request->user_id != "") {

				if ($isSalePerson == 1) {

					$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
					if (in_array($request->user_id, $childSalePersonsIds)) {
						$Architect->where('sale_person_id', $request->user_id);
					} else {
						$Architect->where('sale_person_id', 0);
					}

				} else {
					$Architect->where('sale_person_id', $request->user_id);
				}

			} else {
				$Architect->where('sale_person_id', Auth::user()->id);
			}

			$ArchitectNonPrimeCount = $Architect->count();

			$Architect = Architect::query();
			$Architect->where('created_at', '>=', $startDate);
			$Architect->where('created_at', '<=', $endDate);
			$Architect->where('type', 201);

			if (isset($request->user_id) && $request->user_id != "") {

				if ($isSalePerson == 1) {

					$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
					if (in_array($request->user_id, $childSalePersonsIds)) {
						$Architect->where('sale_person_id', $request->user_id);
					} else {
						$Architect->where('sale_person_id', 0);
					}

				} else {
					$Architect->where('sale_person_id', $request->user_id);
				}

			} else {
				$Architect->where('sale_person_id', Auth::user()->id);
			}

			$ArchitectPrimeCount = $Architect->count();

			$response = successRes("Get inquiry count");
			$response['inquiry_count'] = $InquiryCount;
			$response['non_prime_architects_count'] = $ArchitectNonPrimeCount;
			$response['prime_architects_count'] = $ArchitectPrimeCount;

		}
		return response()->json($response)->header('Content-Type', 'application/json');

	}

	public function searchUser(Request $request) {

		$User = $UserResponse = array();
		$q = $request->q;
		$User = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));

		if (Auth::user()->type != 0 && Auth::user()->type != 1) {
			$User->where('users.type', 2);

			$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
			$User->whereIn('id', $childSalePersonsIds);

		} else {
			$User->whereIn('users.type', array(0, 1, 2));
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