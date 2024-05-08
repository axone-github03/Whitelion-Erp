<?php

namespace App\Http\Controllers;

use App\Models\Architect;
use App\Models\GiftCategory;
use App\Models\GiftProduct;
use App\Models\GiftProductOrder;
use App\Models\GiftProductOrderItem;
use App\Models\User;
use Config;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Session;

class CRMArchitechOrderController extends Controller {

	public function __construct() {
		$this->middleware(function ($request, $next) {
			$tabCanAccessBy = array(202);
			if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
				return redirect()->route('dashboard');
			}
			return $next($request);
		});
	}

	public function index(Request $request) {

		$data = array();
		$data['title'] = "Orders";
		return view('crm/architect/orders', compact('data'));
	}

	public function ajax(Request $request) {

		$searchColumns = array(
			'gift_product_orders.id',
			'gift_product_orders.total_point_value',

		);

		$sortingColumns = array(
			0 => 'gift_product_orders.id',
			1 => 'gift_product_orders.created_at',
			2 => 'gift_product_orders.total_point_value',

		);

		$selectColumns = array(
			'gift_product_orders.id',
			'gift_product_orders.created_at',
			'gift_product_orders.total_point_value',
			'gift_product_orders.status',

		);

		$query = GiftProductOrder::query();
		$query->where('gift_product_orders.user_id', Auth::user()->id);
		$recordsTotal = $query->count();
		$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.
		$query = GiftProductOrder::query();
		$query->where('gift_product_orders.user_id', Auth::user()->id);
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

		$viewData = array();

		foreach ($data as $key => $value) {

			$viewData[$key] = array();
			$viewData[$key]['id'] = $value['id'];
			$viewData[$key]['total_point_value'] = "Point " . (int) $value['total_point_value'];
			$viewData[$key]['status'] = getGiftOrderLable($value['status']);
			$viewData[$key]['created_at'] = convertDateTime($value['created_at']);

			$uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';

			$uiAction .= '<li class="list-inline-item px-2">';
			$uiAction .= '<a onclick="ViewOrder(\'' . $value['id'] . '\')" href="javascript: void(0);" title="View"><i class="mdi mdi-eye"></i></a>';
			$uiAction .= '</li>';
			$uiAction .= '</ul>';
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

	function removeFromCart(Request $request) {

		$cartIterms = Session::get('cart_items');
		if (!isset($cartIterms)) {
			$cartIterms = array();
		}

		foreach ($cartIterms as $key => $value) {
			if (isset($value['id']) && $value['id'] != "" && $value['id'] == $request->id) {
				unset($cartIterms[$key]);
				break;
			}
		}

		$cartIterms = array_values($cartIterms);
		Session::put('cart_items', $cartIterms);
		$response = successRes("Item successfully removed to your cart");
		return response()->json($response)->header('Content-Type', 'application/json');

	}

	function setCart(Request $request) {

		$cartIterms = Session::get('cart_items');
		if (!isset($cartIterms)) {
			$cartIterms = array();
		}
		$cartIterms[count($cartIterms)]['id'] = $request->id;
		Session::put('cart_items', $cartIterms);
		$response = successRes("Item successfully added to your cart");
		return response()->json($response)->header('Content-Type', 'application/json');

	}

	function getCartCount() {
		$cartItemCount = 0;
		$cartIterms = Session::get('cart_items');
		if (isset($cartIterms) && is_array($cartIterms)) {
			$cartItemCount = count($cartIterms);
		}
		$response = successRes("Successfully get cart item count");
		$response['data'] = $cartItemCount;
		return response()->json($response)->header('Content-Type', 'application/json');

	}

	function cart() {

		$data = array();
		$data['title'] = "Cart - Gift Product ";
		return view('crm/architect/cart', compact('data'));

	}

	function calculationProcessOfOrder($orderItems) {

		$order = array();
		$order['id'] = "orderID";
		$order['total_point_value'] = 0;
		$order['created_at'] = date('Y-m-d H:i:s');
		foreach ($orderItems as $key => $value) {

			$orderItems[$key]['id'] = $value['id'];
			if (isset($value['info'])) {
				$orderItems[$key]['info'] = $value['info'];
			}

			$productPrice = floatval($value['point_value']);
			$orderItems[$key]['point_value'] = $productPrice;

			$orderItemQTY = intval($value['qty']);
			$OrderItemsMRP = ($orderItemQTY * $productPrice);

			$orderItems[$key]['qty'] = $orderItemQTY;
			$orderItems[$key]['total_point_value'] = $OrderItemsMRP;
			$order['total_point_value'] = $order['total_point_value'] + $OrderItemsMRP;

		}

		$order['items'] = $orderItems;
		return $order;

	}

	function cartDetail() {
		$cartIterms = Session::get('cart_items');
		if (!isset($cartIterms)) {
			$cartIterms = array();
		}

		$products = array();
		$totalPV = 0;

		foreach ($cartIterms as $key => $value) {

			$giftProduct = GiftProduct::find($value['id']);

			if ($giftProduct) {
				$GiftCategory = GiftCategory::find($giftProduct->gift_category_id);
				$giftProduct['category'] = $GiftCategory;
				$products[] = $giftProduct;
				$totalPV = $totalPV + $giftProduct->point_value;
			} else {
				unset($cartIterms[$key]);
			}

		}

		$cartIterms = array_values($cartIterms);
		Session::put('cart_items', $cartIterms);

		$Architect = Architect::where('user_id', Auth::user()->id)->first();
		$checkOutBtnVisible = 0;

		if (count($cartIterms) > 0 && $Architect->total_point_current >= $totalPV) {
			$checkOutBtnVisible = 1;

		}

		$data = array();
		$data['products'] = $products;
		$data['total_pv'] = $totalPV;
		$data['user_total_pv'] = $Architect->total_point_current;
		$data['checkout_btn_visible'] = $checkOutBtnVisible;
		$response = successRes("Cart detail View");
		$response['cart_html'] = view('crm/architect/cart_detail', compact('data'))->render();
		return response()->json($response)->header('Content-Type', 'application/json');

	}

	function previewOrder(Request $request) {

		$rules = array();
		$rules['d_country_id'] = 'required';
		$rules['d_state_id'] = 'required';
		$rules['d_city_id'] = 'required';
		$rules['d_pincode'] = 'required';
		$rules['d_address_line1'] = 'required';
		$errorMessages = array();
		$errorMessages['d_country_id.required'] = 'Please select country';
		$errorMessages['d_state_id.required'] = 'Please select state';
		$errorMessages['d_city_id.required'] = 'Please select city';
		$errorMessages['d_pincode.required'] = 'Please enter pincode';
		$errorMessages['d_address_line1.required'] = 'Please enter addressline1';
		$validator = Validator::make($request->all(), $rules, $errorMessages);
		if ($validator->fails()) {

			$response = array();
			$response['status'] = 0;
			$response['msg'] = "The request could not be understood by the server due to malformed syntax";
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();

		} else {

			$Architect = Architect::select('aadhar_card')->where('user_id', Auth::user()->id)->first();

			$hasAadharCard = 0;
			if ($Architect && $Architect->aadhar_card != "") {
				$hasAadharCard = 1;
			}

			$response = array();
			$cartIterms = Session::get('cart_items');
			$idWithKey = array();
			$finalCartItems = array();
			foreach ($cartIterms as $key => $value) {

				if (!in_array($value['id'], array_keys($idWithKey))) {

					$cfinalCartItems = count($finalCartItems);
					$idWithKey[$value['id']] = $cfinalCartItems;
					$finalCartItems[$cfinalCartItems]['id'] = $value['id'];
					$finalCartItems[$cfinalCartItems]['qty'] = 1;
					$GiftProduct = GiftProduct::find($value['id']);
					$finalCartItems[$cfinalCartItems]['info'] = array();
					$finalCartItems[$cfinalCartItems]['info']['name'] = $GiftProduct->name;
					$finalCartItems[$cfinalCartItems]['info']['image'] = Config('app.url') . "/s/gift-product/" . $GiftProduct->image;
					$finalCartItems[$cfinalCartItems]['point_value'] = $GiftProduct->point_value;

				} else {

					foreach ($idWithKey as $keyI => $valueI) {

						if ($value['id'] == $keyI) {
							$index = $valueI;
							break;
						}

					}

					$finalCartItems[$index]['qty'] = $finalCartItems[$index]['qty'] + 1;

				}

			}

			$data = array();
			$data['preview'] = 1;
			$data['name'] = Auth::user()->first_name . " " . Auth::user()->last_name;
			$data['email'] = Auth::user()->email;
			$data['phone_number'] = Auth::user()->phone_number;
			$data['order'] = $this->calculationProcessOfOrder($finalCartItems);
			$data['d_country'] = getCountryName($request->d_country_id);
			$data['d_state'] = getStateName($request->d_state_id);
			$data['d_city'] = getCityName($request->d_city_id);
			$data['d_pincode'] = $request->d_pincode;
			$data['d_address_line1'] = $request->d_address_line1;
			$data['d_address_line2'] = $request->d_address_line2;
			$data['has_aadhar_card'] = $hasAadharCard;
			$response = successRes("Order Previw");
			$response['preview'] = view('crm/architect/orders_preview', compact('data'))->render();

		}
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	function placeOrder(Request $request) {

		$Architect = Architect::select('id', 'aadhar_card')->where('user_id', Auth::user()->id)->first();

		$hasAadharCard = 0;
		if ($Architect && $Architect->aadhar_card != "") {
			$hasAadharCard = 1;
		}

		// if ($request->expectsJson()) {

		$rules = array();
		$rules['request_data'] = ['required'];
		if ($hasAadharCard == 0) {
			$rules['architect_aadhar_card'] = ['required', 'mimes:jpg,jpeg,png,pdf'];
		}

		$customMessage = array();
		$customMessage['request_data.required'] = "Invalid parameters";
		$customMessage['architect_aadhar_card.required'] = "Please attach aadhar  card";

		$validator = Validator::make($request->all(), $rules, $customMessage);

		if ($validator->fails()) {

			$response = array();
			$response['status'] = 0;
			$response['msg'] = "The request could not be understood by the server due to malformed syntax";
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();

			return response()->json($response)->header('Content-Type', 'application/json');

		} else {

			$uploadedFile2 = "";

			if ($request->hasFile('architect_aadhar_card')) {

				$folderPathofFile = '/s/architect';
				$fileObject2 = $request->file('architect_aadhar_card');
				$extension = $fileObject2->getClientOriginalExtension();

				$fileName2 = time() . mt_rand(10000, 99999) . '.' . $extension;

				$destinationPath = public_path($folderPathofFile);

				$fileObject2->move($destinationPath, $fileName2);

				if (File::exists(public_path($folderPathofFile . "/" . $fileName2))) {

					$uploadedFile2 = $folderPathofFile . "/" . $fileName2;

				}

			}

			if ($hasAadharCard == 0 && $uploadedFile2 == "") {

				$response = array();
				$response['status'] = 0;
				$response['msg'] = "Please attach aadhar  card";
				$response['statuscode'] = 400;

				return response()->json($response)->header('Content-Type', 'application/json');

			} else if ($hasAadharCard == 0 && $uploadedFile2 != "") {
				$Architect->aadhar_card = $uploadedFile2;
				$Architect->save();
			}

			$inputJSON = $request->request_data;
			$inputJSON = json_decode($inputJSON, true);

			$cartIterms = Session::get('cart_items');
			$idWithKey = array();
			$finalCartItems = array();
			foreach ($cartIterms as $key => $value) {

				if (!in_array($value['id'], array_keys($idWithKey))) {

					$GiftProduct = GiftProduct::find($value['id']);
					if ($GiftProduct->status == 1) {

						$cfinalCartItems = count($finalCartItems);
						$idWithKey[$value['id']] = $cfinalCartItems;
						$finalCartItems[$cfinalCartItems]['id'] = $value['id'];
						$finalCartItems[$cfinalCartItems]['qty'] = 1;

						$finalCartItems[$cfinalCartItems]['info'] = array();
						$finalCartItems[$cfinalCartItems]['info']['name'] = $GiftProduct->name;
						$finalCartItems[$cfinalCartItems]['info']['image'] = Config('app.url') . "/s/gift-product/" . $GiftProduct->image;
						$finalCartItems[$cfinalCartItems]['point_value'] = $GiftProduct->point_value;
					}

				} else {

					foreach ($idWithKey as $keyI => $valueI) {

						if ($value['id'] == $keyI) {
							$index = $valueI;
							break;
						}

					}

					$finalCartItems[$index]['qty'] = $finalCartItems[$index]['qty'] + 1;

				}

			}

			$orderDetail = $this->calculationProcessOfOrder($finalCartItems);
			$Architect = Architect::where('user_id', Auth::user()->id)->first();
			$userTotalPoint = (int) $Architect->total_point_current;
			if (count($orderDetail['items']) > 0) {
				if ($userTotalPoint >= $orderDetail['total_point_value']) {

					$GiftProductOrder = new GiftProductOrder();
					$GiftProductOrder->user_id = Auth::user()->id;
					$GiftProductOrder->total_point_value = $orderDetail['total_point_value'];
					$GiftProductOrder->d_address_line1 = $inputJSON['d_address_line1'];
					$GiftProductOrder->d_address_line2 = isset($inputJSON['d_address_line2']) ? $inputJSON['d_address_line2'] : '';
					$GiftProductOrder->d_city_id = $inputJSON['d_city_id'];
					$GiftProductOrder->d_country_id = $inputJSON['d_country_id'];
					$GiftProductOrder->d_pincode = $inputJSON['d_pincode'];
					$GiftProductOrder->d_state_id = $inputJSON['d_state_id'];
					$GiftProductOrder->save();

					foreach ($orderDetail['items'] as $key => $value) {
						$GiftProductOrderItem = new GiftProductOrderItem();
						$GiftProductOrderItem->user_id = $GiftProductOrder->user_id;
						$GiftProductOrderItem->gift_product_order_id = $GiftProductOrder->id;
						$GiftProductOrderItem->gift_product_id = $value['id'];
						$GiftProductOrderItem->qty = $value['qty'];
						$GiftProductOrderItem->point_value = $value['point_value'];
						$GiftProductOrderItem->total_point_value = $value['total_point_value'];
						$GiftProductOrderItem->save();

					}

					$Architect->total_point_used = $Architect->total_point_used + $GiftProductOrder->total_point_value;
					$Architect->total_point_current = $Architect->total_point_current - $GiftProductOrder->total_point_value;
					$Architect->save();

					$debugLog = array();
					$debugLog['architect_user_id'] = Auth::user()->id;
					$debugLog['name'] = "point-redeem";
					$debugLog['description'] = $GiftProductOrder->total_point_value . " Point redeem from order #" . $GiftProductOrder->id;
					saveArchitectLog($debugLog);
					$response = successRes("Successfully generated order #" . $GiftProductOrder->id);
					Session::put('cart_items', array());

				} else {
					$response = errorRes("Insufficeint points");
				}
			} else {
				$response = errorRes("Invalid items");
			}
		}

		// } else {

		// 	$response = errorRes("Something went wrong");

		// }
		return response()->json($response)->header('Content-Type', 'application/json');

	}

	function detail(Request $request) {

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

			$GiftProductOrder = GiftProductOrder::query();
			$GiftProductOrder->where('gift_product_orders.user_id', Auth::user()->id);
			$GiftProductOrder->where('gift_product_orders.id', $request->id);
			$GiftProductOrder->limit(1);
			$GiftProductOrder = $GiftProductOrder->first();
			if ($GiftProductOrder) {

				$GiftProductOrderItem = GiftProductOrderItem::query();
				$GiftProductOrderItem->select('gift_products.name', 'gift_products.image', 'gift_product_order_items.qty', 'gift_product_order_items.total_point_value', 'gift_product_order_items.point_value');
				$GiftProductOrderItem->leftJoin('gift_products', 'gift_products.id', '=', 'gift_product_order_items.gift_product_id');
				$GiftProductOrderItem->where('gift_product_order_id', $GiftProductOrder->id);
				$GiftProductOrderItem->orderBy('gift_product_order_items.id', 'desc');
				$GiftProductOrderItem = $GiftProductOrderItem->get();

				$GiftProductOrderItem = json_decode(json_encode($GiftProductOrderItem), true);
				foreach ($GiftProductOrderItem as $key => $value) {
					$GiftProductOrderItem[$key]['info'] = array();
					$GiftProductOrderItem[$key]['info']['name'] = $value['name'];
					$GiftProductOrderItem[$key]['info']['image'] = Config('app.url') . "/s/gift-product/" . $value['image'];

				}
				$GiftProductOrder['items'] = $GiftProductOrderItem;
				$response = successRes("Order detail");
				$data = array();
				$data['preview'] = 0;
				$data['order'] = json_decode(json_encode($GiftProductOrder), true);
				$data['name'] = Auth::user()->first_name . " " . Auth::user()->last_name;
				$data['email'] = Auth::user()->email;
				$data['phone_number'] = Auth::user()->phone_number;

				$data['d_country'] = getCountryName($data['order']['d_country_id']);
				$data['d_state'] = getStateName($data['order']['d_state_id']);
				$data['d_city'] = getCityName($data['order']['d_city_id']);
				$data['d_pincode'] = $data['order']['d_pincode'];
				$data['d_address_line1'] = $data['order']['d_address_line1'];
				$data['d_address_line2'] = $data['order']['d_address_line2'];

				$response = successRes("Order Previw");
				$response['preview'] = view('crm/architect/orders_preview', compact('data'))->render();

			} else {
				$response = errorRes("Invalid order id");

			}

		}

		return response()->json($response)->header('Content-Type', 'application/json');

	}
}