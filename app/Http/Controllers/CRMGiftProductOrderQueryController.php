<?php

namespace App\Http\Controllers;

use App\Models\GiftProductOrder;
use App\Models\GiftProductOrderQuery;
use App\Models\GiftProductOrderQueryConversion;
use App\Models\User;
use Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Mail;

class CRMGiftProductOrderQueryController extends Controller {

	public function __construct() {
		$this->middleware(function ($request, $next) {
			$tabCanAccessBy = array(0, 1, 6);
			if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
				return redirect()->route('dashboard');
			}
			return $next($request);
		});
	}

	public function index(Request $request) {

		$data = array();
		$data['title'] = "Orders Query";

		return view('crm/gift_product_orders_query', compact('data'));
	}

	public function ajax(Request $request) {

		$searchColumns = array(
			'gift_product_orders_query.id',
			'gift_product_orders_query.gift_product_order_id',

		);

		$sortingColumns = array(
			0 => 'gift_product_orders_query.id',
			1 => 'gift_product_orders_query.created_at',
			2 => 'gift_product_orders_query.gift_product_order_id',
			3 => 'gift_product_orders_query.status',
			4 => 'gift_product_orders_query.status',
			5 => 'gift_product_orders_query.id',

		);

		$selectColumns = array(
			'gift_product_orders_query.id',
			'gift_product_orders_query.created_at',
			'gift_product_orders_query.gift_product_order_id',
			'gift_product_orders_query.status',
			'gift_product_orders_query.message_from_crm_user',

		);

		$query = GiftProductOrderQuery::query();

		$recordsTotal = $query->count();
		$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.
		$query = GiftProductOrderQuery::query();

		//$query->where('gift_product_orders_query.user_id', Auth::user()->id);
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

		$viewData = array();

		foreach ($data as $key => $value) {

			$viewData[$key] = array();
			$viewData[$key]['id'] = "#" . highlightString($value['id'],$search_value);
			$viewData[$key]['created_at'] = highlightString(convertDateTime($value['created_at']),$search_value);
			$viewData[$key]['gift_product_order_id'] = "#" . highlightString($value['gift_product_order_id'],$search_value);

			if ($value['status'] == 0) {
				$viewData[$key]['status'] = '<span class="badge badge-pill badge-soft-warning font-size-11">OPEN</span>';
			} else {
				$viewData[$key]['status'] = '<span class="badge badge-pill badge-soft-success font-size-11">CLOSED</span>';

			}

			if ($value['status'] == 0) {

				if ($value['message_from_crm_user'] == 0) {

					$viewData[$key]['need_to_respond'] = '<span class="badge badge-pill badge-soft-success font-size-11">No</span>';

				} else {

					$viewData[$key]['need_to_respond'] = '<span class="badge badge-pill badge-soft-warning font-size-11">Yes</span>';

				}
			} else {
				$viewData[$key]['need_to_respond'] = '<span class="badge badge-pill badge-soft-success font-size-11">No</span>';

			}

			$redirectURL = route('gift.product.orders.query.detail');

			$uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';
			$uiAction .= '<li class="list-inline-item px-2">';
			$uiAction .= '<a href="' . $redirectURL . '?id=' . $value['id'] . '" title="View"><i class="mdi mdi-eye"></i></a>';
			$uiAction .= '</li>';

			$viewData[$key]['action'] = $uiAction;
		}

		$jsonData = array(
			"draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
			"recordsTotal" => intval($recordsTotal), // total number of records
			"recordsFiltered" => intval($recordsFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
			"data" => $viewData, // total data array

		);
		return $jsonData;

	}

	function detail(Request $request) {

		$GiftProductOrderQuery = GiftProductOrderQuery::find($request->id);

		if ($GiftProductOrderQuery) {

			$GiftProductOrder = GiftProductOrder::find($GiftProductOrderQuery->gift_product_order_id);
			if ($GiftProductOrder) {

				$GiftProductOrderQueryConversion = GiftProductOrderQueryConversion::query();
				$GiftProductOrderQueryConversion->select('gift_product_orders_query_conversion.*', 'users.first_name', 'users.last_name');
				$GiftProductOrderQueryConversion->leftJoin('users', 'users.id', '=', 'gift_product_orders_query_conversion.from_user_id');
				$GiftProductOrderQueryConversion->where('gift_product_orders_query_conversion.gift_product_order_query_id', $GiftProductOrderQuery->id)->orderBy('gift_product_orders_query_conversion.id', 'asc');
				$GiftProductOrderQueryConversion = $GiftProductOrderQueryConversion->get();

				$GiftProductOrderQueryConversion = json_decode(json_encode($GiftProductOrderQueryConversion), true);

				foreach ($GiftProductOrderQueryConversion as $key => $value) {

					$GiftProductOrderQueryConversion[$key]['created_at'] = convertDateTime($value['created_at']);

				}

				$data = array();
				$data['title'] = "Order Query Detail";
				$data['gift_product_order'] = $GiftProductOrder;
				$data['gift_product_order_query'] = $GiftProductOrderQuery;
				$data['gift_product_orders_query_conversion'] = $GiftProductOrderQueryConversion;

				return view('crm/gift_order_query_detail', compact('data'));

			} else {
				return redirect()->back();
			}

		} else {

			return redirect()->back();

		}

	}

	function save(Request $request) {

		$rules = array();
		$rules['conversion_query_id'] = 'required';
		$rules['message'] = 'required';
		$customMessage = array();
		$customMessage['conversion_query_id.required'] = "Please enter query";
		$customMessage['message.required'] = "Please enter message";
		$validator = Validator::make($request->all(), $rules, $customMessage);
		if ($validator->fails()) {

			$response = array();
			$response['status'] = 0;
			$response['msg'] = "The request could not be understood by the server due to malformed syntax";
			$response['statuscode'] = 400;
			return redirect()->back();

		} else {

			$GiftProductOrderQuery = GiftProductOrderQuery::find($request->conversion_query_id);

			if ($GiftProductOrderQuery) {

				$GiftProductOrder = GiftProductOrder::find($GiftProductOrderQuery->gift_product_order_id);
				if ($GiftProductOrder) {

					$GiftProductOrderQueryConversion = new GiftProductOrderQueryConversion();
					$GiftProductOrderQueryConversion->gift_product_order_query_id = $GiftProductOrderQuery->id;
					$GiftProductOrderQueryConversion->from_user_id = Auth::user()->id;
					$GiftProductOrderQueryConversion->message = $request->message;
					$GiftProductOrderQueryConversion->save();

					$GiftProductOrderQuery->message_from_management = $GiftProductOrderQuery->message_from_management + 1;
					$GiftProductOrderQuery->message_from_crm_user = 0;
					$GiftProductOrderQuery->save();

					//

					$User = User::find($GiftProductOrder->user_id);
					if ($User) {

						$configrationForNotify = configrationForNotify();

						$params = array();
						$params['from_name'] = $configrationForNotify['from_name'];
						$params['from_email'] = $configrationForNotify['from_email'];
						$params['to_email'] = $User->email;
						$params['to_name'] = $configrationForNotify['to_name'];
						$params['first_name'] = $User->first_name;
						$params['last_name'] = $User->last_name;
						$params['subject'] = "Reply ticket #" . $GiftProductOrderQuery->id;
						$params['message'] = $request->message;

						if (Config::get('app.env') == "local") {

							$params['to_email'] = $configrationForNotify['test_email'];
							//$params['bcc_email'] = $configrationForNotify['test_email_bcc'];

						}

						Mail::send('emails.gift_order_query_reply', ['params' => $params], function ($m) use ($params) {
							{}$m->from($params['from_email'], $params['from_name']);
							$m->to($params['to_email'], $params['to_name'])->subject($params['subject']);
						});

					}
					//

					return redirect()->back()->with('success', "Successfully sent message to user");

				} else {
					return redirect()->back();
				}

			} else {

				return redirect()->back();

			}

		}

	}

	function close(Request $request) {

		$GiftProductOrderQuery = GiftProductOrderQuery::find($request->id);
		if ($GiftProductOrderQuery) {
			$GiftProductOrderQuery->status = 1;
			$GiftProductOrderQuery->save();
			return redirect()->back()->with('success', "Successfully updated status as closed");

		}

		return redirect()->back();

	}
}