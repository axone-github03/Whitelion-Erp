<?php

namespace App\Http\Controllers;

use App\Models\GiftProductOrder;
use App\Models\MarketingChallan;
use App\Models\MarketingOrder;
use App\Models\Order;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
	//
	public function getBadge(Request $request)
	{

		$pendingUserRequest = 0;

		$tabCanAccessBy = array(0);
		$hasRequest = 0;
		if (in_array(Auth::user()->type, $tabCanAccessBy)) {
			$pendingUserRequest = User::where('status', 2)->whereIn('type', [101,102,103,104,105])->count();
			$hasRequest = 1;
		}

		$unreadCount = UserNotification::where('user_id', Auth::user()->id)->where('is_read', 0)->count();
		$response = successRes("");
		$response['unread'] = $unreadCount;
		$response['pending_request'] = $pendingUserRequest;
		$response['has_request'] = $hasRequest;

		$isSalePerson = isSalePerson();
		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();

		if ($isSalePerson == 1) {
			$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
		}

		///MARKETING
		$query = MarketingOrder::query();
		$query->where('marketing_orders.status', 0);
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
		}
		$noOfMarketingRequestPending = $query->count();
		$response['no_of_marketing_pending'] = $noOfMarketingRequestPending;

		$query = MarketingChallan::query();

		if ($isAdminOrCompanyAdmin == 1) {
		} else if ($isSalePerson == 1) {

			$query->leftJoin('marketing_orders', 'marketing_orders_challan.order_id', '=', 'marketing_orders.id');
			$query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'marketing_orders.channel_partner_user_id');

			$query->where(function ($query) use ($childSalePersonsIds) {

				foreach ($childSalePersonsIds as $key => $value) {
					if ($key == 0) {
						$query->whereRaw('FIND_IN_SET("' . $value . '",marketing_orders.sale_persons)>0');
					} else {
						$query->orWhereRaw('FIND_IN_SET("' . $value . '",marketing_orders.sale_persons)>0');
					}
				}
			});
		}


		$query->where('marketing_orders_challan.invoice_number', '');
		$query->where('marketing_orders_challan.status', 0);
		$noOfChallanWithoutInvoice = $query->count();
		$response['no_of_marketing_challan_pending'] = $noOfChallanWithoutInvoice;

		$query = MarketingChallan::query();

		if ($isAdminOrCompanyAdmin == 1) {
		} else if ($isSalePerson == 1) {

			$query->leftJoin('marketing_orders', 'marketing_orders_challan.order_id', '=', 'marketing_orders.id');
			$query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'marketing_orders.channel_partner_user_id');

			$query->where(function ($query) use ($childSalePersonsIds) {

				foreach ($childSalePersonsIds as $key => $value) {
					if ($key == 0) {
						$query->whereRaw('FIND_IN_SET("' . $value . '",marketing_orders.sale_persons)>0');
					} else {
						$query->orWhereRaw('FIND_IN_SET("' . $value . '",marketing_orders.sale_persons)>0');
					}
				}
			});
		}
		$query->where('marketing_orders_challan.invoice_number', '!=', '');
		$query->where('marketing_orders_challan.status', 0);
		$noOfChallanRaised = $query->count();
		$response['no_of_marketing_challan_raised'] = $noOfChallanRaised;

		$query = MarketingChallan::query();

		if ($isAdminOrCompanyAdmin == 1) {
		} else if ($isSalePerson == 1) {

			$query->leftJoin('marketing_orders', 'marketing_orders_challan.order_id', '=', 'marketing_orders.id');
			$query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'marketing_orders.channel_partner_user_id');

			$query->where(function ($query) use ($childSalePersonsIds) {

				foreach ($childSalePersonsIds as $key => $value) {
					if ($key == 0) {
						$query->whereRaw('FIND_IN_SET("' . $value . '",marketing_orders.sale_persons)>0');
					} else {
						$query->orWhereRaw('FIND_IN_SET("' . $value . '",marketing_orders.sale_persons)>0');
					}
				}
			});
		}
		$query->where('marketing_orders_challan.status', 1);
		$noOfChallanPacked = $query->count();
		$response['no_of_marketing_challan_packed'] = $noOfChallanPacked;

		$Gift_query = GiftProductOrder::query();
		$Cash_query = GiftProductOrder::query();
		$Cashback_query = GiftProductOrder::query();

		if ($isAdminOrCompanyAdmin == 1) {
			$Gift_query->selectRaw('COUNT(gift_product_order_items.id) as item_count');
			$Gift_query->leftJoin('gift_product_order_items', 'gift_product_order_items.gift_product_order_id', '=', 'gift_product_orders.id');
		} else if ($isSalePerson == 1) {
			$Gift_query->selectRaw('COUNT(gift_product_order_items.id) as item_count');
			$Gift_query->leftJoin('architect', 'architect.user_id', '=', 'gift_product_orders.user_id');
			$Gift_query->leftJoin('electrician', 'electrician.user_id', '=', 'gift_product_orders.user_id');
			$Gift_query->leftJoin('gift_product_order_items', 'gift_product_order_items.gift_product_order_id', '=', 'gift_product_orders.id');
			$Gift_query->where(function ($Gift_query2) use ($childSalePersonsIds) {
				$Gift_query2->whereIn('architect.sale_person_id', $childSalePersonsIds);
				$Gift_query2->orwhereIn('electrician.sale_person_id', $childSalePersonsIds);
			});
			$Cash_query->leftJoin('architect', 'architect.user_id', '=', 'gift_product_orders.user_id');
			$Cash_query->leftJoin('electrician', 'electrician.user_id', '=', 'gift_product_orders.user_id');
			$Cash_query->where(function ($Cash_query2) use ($childSalePersonsIds) {
				$Cash_query2->whereIn('architect.sale_person_id', $childSalePersonsIds);
				$Cash_query2->orwhereIn('electrician.sale_person_id', $childSalePersonsIds);
			});
			$Cashback_query->leftJoin('architect', 'architect.user_id', '=', 'gift_product_orders.user_id');
			$Cashback_query->leftJoin('electrician', 'electrician.user_id', '=', 'gift_product_orders.user_id');
			$Cashback_query->where(function ($Cashback_query2) use ($childSalePersonsIds) {
				$Cashback_query2->whereIn('architect.sale_person_id', $childSalePersonsIds);
				$Cashback_query2->orwhereIn('electrician.sale_person_id', $childSalePersonsIds);
			});
		}


		$Gift_query->where('gift_product_orders.status', 0);

		$Cash_query->where('gift_product_orders.cash_status', 0);
		$Cash_query->where('gift_product_orders.total_cash', '!=', 0);

		$Cashback_query->where('gift_product_orders.cashback_status', 0);
		$Cashback_query->where('gift_product_orders.total_cashback', '!=', 0);

		$noOfGifOrders = (int)$Gift_query->first()->item_count + (int)$Cash_query->count() + (int)$Cashback_query->count();

		$response['count'] = (int)$Gift_query->first()->item_count ." - ". (int)$Cash_query->count() ." - ". (int)$Cashback_query->count();
		$response['no_of_gift_order'] = $noOfGifOrders;
		$noOfGifOrders = 0;


		if ($isAdminOrCompanyAdmin == 1) {

			$query = Order::query();
			$query->whereIn('status', array(0, 2));
			$noOfGifOrders = $query->count();
			$response['no_of_order'] = $noOfGifOrders;
		}

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function getContent(Request $request)
	{

		$response = successRes("");
		$userNotificationTypes = getUserNotificationTypes();

		if ($request->activetab == "notification-all") {

			$UserNotification = UserNotification::query();
			$UserNotification->select('user_notifications.id', 'user_notifications.title', 'user_notifications.description', 'user_notifications.created_at', 'user_notifications.is_read', 'user_notifications.is_favourite', 'user_notifications.type', 'user_notifications.inquiry_id', 'user_notifications.from_user_id', 'users.first_name as from_first_name', 'users.last_name as from_last_name');
			$UserNotification->leftJoin('users', 'users.id', '=', 'user_notifications.from_user_id');
			$UserNotification->where('user_notifications.user_id', Auth::user()->id);

			if (isset($request->get_history) && $request->get_history == 1 && $request->notification_id != "") {
				$UserNotification->where('user_notifications.id', '<', $request->notification_id);
			}

			$UserNotification->orderBy('user_notifications.id', 'desc');

			$UserNotification->limit(4);
			$UserNotification = $UserNotification->get();
		} else if ($request->activetab == "notification-unread") {
			$UserNotification = UserNotification::query();
			$UserNotification->select('user_notifications.id', 'user_notifications.title', 'user_notifications.description', 'user_notifications.created_at', 'user_notifications.is_read', 'user_notifications.is_favourite', 'user_notifications.type', 'user_notifications.inquiry_id', 'user_notifications.from_user_id', 'users.first_name as from_first_name', 'users.last_name as from_last_name');
			$UserNotification->leftJoin('users', 'users.id', '=', 'user_notifications.from_user_id');
			$UserNotification->where('user_id', Auth::user()->id);
			$UserNotification->where('is_read', 0);

			if (isset($request->get_history) && $request->get_history == 1 && $request->notification_id != "") {
				$UserNotification->where('user_notifications.id', '<', $request->notification_id);
			}

			$UserNotification->orderBy('user_notifications.id', 'desc');
			$UserNotification->limit(4);
			$UserNotification = $UserNotification->get();
		} else if ($request->activetab == "notification-mentioned") {

			$mentionedNotificationTypes = array();

			foreach ($userNotificationTypes as $key => $value) {
				if ($value['mentioned'] == 1) {
					$mentionedNotificationTypes[] = $value['id'];
				}
			}

			$UserNotification = UserNotification::query();
			$UserNotification->select('user_notifications.id', 'user_notifications.title', 'user_notifications.description', 'user_notifications.created_at', 'user_notifications.is_read', 'user_notifications.is_favourite', 'user_notifications.type', 'user_notifications.inquiry_id', 'user_notifications.from_user_id', 'users.first_name as from_first_name', 'users.last_name as from_last_name');
			$UserNotification->leftJoin('users', 'users.id', '=', 'user_notifications.from_user_id');
			$UserNotification->where('user_notifications.user_id', Auth::user()->id);

			$UserNotification->whereIn('user_notifications.type', $mentionedNotificationTypes);

			if (isset($request->get_history) && $request->get_history == 1 && $request->notification_id != "") {
				$UserNotification->where('user_notifications.id', '<', $request->notification_id);
			}
			$UserNotification->orderBy('user_notifications.id', 'desc');
			$UserNotification->limit(4);
			$UserNotification = $UserNotification->get();
		} else if ($request->activetab == "notification-assigned") {

			$assignedNotificationTypes = array();

			foreach ($userNotificationTypes as $key => $value) {
				if ($value['assigned'] == 1) {
					$assignedNotificationTypes[] = $value['id'];
				}
			}

			$UserNotification = UserNotification::query();
			$UserNotification->select('user_notifications.id', 'user_notifications.title', 'user_notifications.description', 'user_notifications.created_at', 'user_notifications.is_read', 'user_notifications.is_favourite', 'user_notifications.type', 'user_notifications.inquiry_id', 'user_notifications.from_user_id', 'users.first_name as from_first_name', 'users.last_name as from_last_name');
			$UserNotification->leftJoin('users', 'users.id', '=', 'user_notifications.from_user_id');
			$UserNotification->where('user_notifications.user_id', Auth::user()->id);

			$UserNotification->whereIn('user_notifications.type', $assignedNotificationTypes);
			if (isset($request->get_history) && $request->get_history == 1 && $request->notification_id != "") {
				$UserNotification->where('user_notifications.id', '<', $request->notification_id);
			}
			$UserNotification->orderBy('user_notifications.id', 'desc');
			$UserNotification->limit(4);
			$UserNotification = $UserNotification->get();
		} else if ($request->activetab == "notification-favourite") {

			$UserNotification = UserNotification::query();
			$UserNotification->select('user_notifications.id', 'user_notifications.title', 'user_notifications.description', 'user_notifications.created_at', 'user_notifications.is_read', 'user_notifications.is_favourite', 'user_notifications.type', 'user_notifications.inquiry_id', 'user_notifications.from_user_id', 'users.first_name as from_first_name', 'users.last_name as from_last_name');
			$UserNotification->leftJoin('users', 'users.id', '=', 'user_notifications.from_user_id');
			$UserNotification->where('user_notifications.user_id', Auth::user()->id);
			$UserNotification->where('user_notifications.is_favourite', 1);

			if (isset($request->get_history) && $request->get_history == 1 && $request->notification_id != "") {
				$UserNotification->where('user_notifications.id', '<', $request->notification_id);
			}

			$UserNotification->orderBy('user_notifications.id', 'desc');
			$UserNotification->limit(4);
			$UserNotification = $UserNotification->get();
		}

		foreach ($UserNotification as $key => $value) {

			$UserNotification[$key]['A'] = strtoupper(substr($value->from_first_name, 0, 1));
			$UserNotification[$key]['B'] = strtoupper(substr($value->from_last_name, 0, 1));
		}

		$data = array();
		$data['notification'] = $UserNotification;
		$data['tab'] = $request->activetab;
		$response['view'] = view('dashboard/notification', compact('data'))->render();

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function read(Request $request)
	{

		$rules = array();
		$rules['notification_id'] = 'required';

		$customMessage = array();
		$customMessage['notification_id.required'] = "Invalid parameters";

		$validator = Validator::make($request->all(), $rules, $customMessage);

		if ($validator->fails()) {

			$response = array();
			$response['status'] = 0;
			$response['msg'] = "The request could not be understood by the server due to malformed syntax";
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();
		} else {
			$UserNotification = UserNotification::find($request->notification_id);
			if ($UserNotification && $UserNotification->user_id == Auth::user()->id) {

				$UserNotification->is_read = 1;
				$UserNotification->save();
				$response = successRes("");
			} else {
				$response = errorRes("");
			}
		}

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function unread(Request $request)
	{

		$rules = array();
		$rules['notification_id'] = 'required';

		$customMessage = array();
		$customMessage['notification_id.required'] = "Invalid parameters";

		$validator = Validator::make($request->all(), $rules, $customMessage);

		if ($validator->fails()) {

			$response = array();
			$response['status'] = 0;
			$response['msg'] = "The request could not be understood by the server due to malformed syntax";
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();
		} else {
			$UserNotification = UserNotification::find($request->notification_id);
			if ($UserNotification && $UserNotification->user_id == Auth::user()->id) {

				$UserNotification->is_read = 0;
				$UserNotification->save();
				$response = successRes("");
			} else {
				$response = errorRes("");
			}
		}

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function favourite(Request $request)
	{

		$rules = array();
		$rules['notification_id'] = 'required';

		$customMessage = array();
		$customMessage['notification_id.required'] = "Invalid parameters";

		$validator = Validator::make($request->all(), $rules, $customMessage);

		if ($validator->fails()) {

			$response = array();
			$response['status'] = 0;
			$response['msg'] = "The request could not be understood by the server due to malformed syntax";
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();
		} else {
			$UserNotification = UserNotification::find($request->notification_id);
			if ($UserNotification && $UserNotification->user_id == Auth::user()->id) {

				$UserNotification->is_favourite = 1;
				$UserNotification->save();
				$response = successRes("");
			} else {
				$response = errorRes("");
			}
		}

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function removeFromFavourite(Request $request)
	{

		$rules = array();
		$rules['notification_id'] = 'required';

		$customMessage = array();
		$customMessage['notification_id.required'] = "Invalid parameters";

		$validator = Validator::make($request->all(), $rules, $customMessage);

		if ($validator->fails()) {

			$response = array();
			$response['status'] = 0;
			$response['msg'] = "The request could not be understood by the server due to malformed syntax";
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();
		} else {
			$UserNotification = UserNotification::find($request->notification_id);
			if ($UserNotification && $UserNotification->user_id == Auth::user()->id) {

				$UserNotification->is_favourite = 0;
				$UserNotification->save();
				$response = successRes("");
			} else {
				$response = errorRes("");
			}
		}

		return response()->json($response)->header('Content-Type', 'application/json');
	}
}
