<?php

namespace App\Http\Controllers;

use App\Models\GiftProductOrder;
use App\Models\GiftProductOrderQuery;
use App\Models\GiftProductOrderQueryConversion;
use App\Models\Parameter;
use App\Models\User;
use Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Mail;

class CRMUserRaiseQueryController extends Controller {

	public function __construct() {
		$this->middleware(function ($request, $next) {
			$tabCanAccessBy = array(202, 302);
			if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
				return redirect()->route('dashboard');
			}
			return $next($request);
		});
	}

	public function send(Request $request) {

		$rules = array();
		$rules['gift_product_order_query_title'] = 'required';
		$rules['gift_product_order_query_description'] = 'required';
		$rules['gift_product_order_query_order_id'] = 'required';
		$customMessage = array();
		$customMessage['gift_product_order_query_title.required'] = "Please enter title";
		$customMessage['gift_product_order_query_description.required'] = "Please enter description";
		$validator = Validator::make($request->all(), $rules, $customMessage);
		if ($validator->fails()) {

			$response = array();
			$response['status'] = 0;
			$response['msg'] = "The request could not be understood by the server due to malformed syntax";
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();

		} else {

			$GiftProductOrder = GiftProductOrder::find($request->gift_product_order_query_order_id);
			if ($GiftProductOrder->user_id != Auth::user()->id) {

				$response = errorRes("Invalid gift order id #" . $GiftProductOrder->id);
				return response()->json($response)->header('Content-Type', 'application/json');

			}

			$GiftProductOrderQuery = GiftProductOrderQuery::where('gift_product_order_id', $request->gift_product_order_query_order_id)->where('status', 0)->first();
			if ($GiftProductOrderQuery) {

				$response = errorRes("Gift Product Order Has already query generated ticket id #" . $GiftProductOrderQuery->id);

			} else {

				$GiftProductOrderQuery = new GiftProductOrderQuery();
				$GiftProductOrderQuery->gift_product_order_id = $request->gift_product_order_query_order_id;
				$GiftProductOrderQuery->status = 0;
				$GiftProductOrderQuery->title = $request->gift_product_order_query_title;
				$GiftProductOrderQuery->description = $request->gift_product_order_query_description;
				$GiftProductOrderQuery->message_from_crm_user = 1;
				$GiftProductOrderQuery->save();

				if ($GiftProductOrderQuery) {
					$GiftProductOrder = GiftProductOrder::find($GiftProductOrderQuery->gift_product_order_id);
					if ($GiftProductOrder) {

						$GiftProductOrder->gift_product_order_query_id = $GiftProductOrderQuery->id;
						$GiftProductOrder->save();
					}
				}
				$response = successRes("Successfully sent query to Whitelion Systems.");

				$Parameter = Parameter::where('code', 'crm-query-email')->first();
				$toEmail = array();

				if ($Parameter) {
					$toEmail = $Parameter->name_value;

					$toEmail = explode(",", $toEmail);
				}

				if (count($toEmail) > 0) {

					$configrationForNotify = configrationForNotify();
					$params = array();
					$params['from_email'] = $configrationForNotify['from_email'];
					$params['from_name'] = $configrationForNotify['from_name'];
					$params['to_name'] = $configrationForNotify['to_name'];
					$params['to_email'] = $toEmail;
					$params['query'] = $GiftProductOrderQuery;
					$params['subject'] = "New Ticket #" . $GiftProductOrderQuery->id . " on reward";

					if (Config::get('app.env') == "local") {
						$params['to_email'] = $configrationForNotify['test_email'];
					}

					Mail::send('emails.gift_order_query', ['params' => $params], function ($m) use ($params) {

						$m->from($params['from_email'], $params['from_name'])->subject($params['subject']);
						foreach ($params['to_email'] as $keyT => $valueT) {
							if ($valueT != "") {
								$m->to(trim($valueT), $params['to_name']);
							}

						}
					});

				}

			}

		}

		return response()->json($response)->header('Content-Type', 'application/json');

	}

	function detail(Request $request) {

		$GiftProductOrderQuery = GiftProductOrderQuery::find($request->id);

		if ($GiftProductOrderQuery) {

			$GiftProductOrder = GiftProductOrder::find($GiftProductOrderQuery->gift_product_order_id);
			if ($GiftProductOrder && $GiftProductOrder->user_id == Auth::user()->id) {

				$GiftProductOrderQueryConversion = GiftProductOrderQueryConversion::where('gift_product_order_query_id', $GiftProductOrderQuery->id)->orderBy('id', 'asc')->get();

				$GiftProductOrderQueryConversion = json_decode(json_encode($GiftProductOrderQueryConversion), true);

				foreach ($GiftProductOrderQueryConversion as $key => $value) {

					$GiftProductOrderQueryConversion[$key]['created_at'] = convertDateTime($value['created_at']);

				}

				$data = array();
				$data['title'] = "Order Query Detail";
				$data['gift_product_order'] = $GiftProductOrder;
				$data['gift_product_order_query'] = $GiftProductOrderQuery;
				$data['gift_product_orders_query_conversion'] = $GiftProductOrderQueryConversion;

				return view('crm/architect/gift_order_query_detail', compact('data'));

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
				if ($GiftProductOrder && $GiftProductOrder->user_id == Auth::user()->id) {

					$GiftProductOrderQueryConversion = new GiftProductOrderQueryConversion();
					$GiftProductOrderQueryConversion->gift_product_order_query_id = $GiftProductOrderQuery->id;
					$GiftProductOrderQueryConversion->from_user_id = Auth::user()->id;
					$GiftProductOrderQueryConversion->message = $request->message;
					$GiftProductOrderQueryConversion->save();

					$GiftProductOrderQuery->message_from_crm_user = $GiftProductOrderQuery->message_from_crm_user + 1;
					$GiftProductOrderQuery->message_from_management = 0;
					$GiftProductOrderQuery->save();

					return redirect()->back()->with('success', "Successfully sent message to Whitelion Systems");

				} else {
					return redirect()->back();
				}

			} else {

				return redirect()->back();

			}

		}

	}

}