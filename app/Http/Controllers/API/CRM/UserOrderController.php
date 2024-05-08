<?php

namespace App\Http\Controllers\API\CRM;

use App\Http\Controllers\Controller;
use App\Models\Architect;
use App\Models\Electrician;
use App\Models\GiftCategory;
use App\Models\GiftProduct;
use App\Models\GiftProductOrder;
use App\Models\GiftProductOrderItem;
use App\Models\GiftProductOrderQuery;
use App\Models\Parameter;
use App\Models\User;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Session;

class UserOrderController extends Controller
{

	public function __construct()
	{
		$this->middleware(function ($request, $next) {
			$tabCanAccessBy = array(202, 302);
			if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
				$response = errorRes("Invalid access", 401);
				return response()->json($response, $response['status_code'])->header('Content-Type', 'application/json');
			}
			return $next($request);
		});
	}

	public function index(Request $request)
	{

		$data = array();
		$data['title'] = "Orders";
		return view('crm/architect/orders', compact('data'));
	}

	public function ajax(Request $request)
	{

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
			'gift_product_orders.gift_product_order_query_id',

		);

		// $query = GiftProductOrder::query();
		// $query->where('gift_product_orders.user_id', Auth::user()->id);
		// $recordsTotal = $query->count();
		// $recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.
		$query = GiftProductOrder::query();
		$query->where('gift_product_orders.user_id', Auth::user()->id);
		$query->select($selectColumns);
		if (isset($request->isticket)) {
			if($request->isticket == 1){
				$query->where('gift_product_orders.gift_product_order_query_id','!=',0);
			}else{
				$query->where('gift_product_orders.gift_product_order_query_id',0);
			}
		}
		// $query->limit($request->length);
		// $query->offset($request->start);
		//$query->orderBy($sortingColumns[$request['order'][0]['column']], $request['order'][0]['dir']);
		$query->orderBy('gift_product_orders.id', 'desc');

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

		$data = $query->paginate();
		foreach ($data as $key => $value) {

			if ($value->gift_product_order_query_id != 0) {

				$GiftProductOrderQuery = GiftProductOrderQuery::find($value->gift_product_order_query_id);
				if ($GiftProductOrderQuery) {

					$data[$key]['gift_product_order_query_status'] = $GiftProductOrderQuery->status;
				}
			}
		}
		$data = json_decode(json_encode($data), true);

		$response = successRes("Order List");
		$response['data'] = $data;
		return response()->json($response, $response['status_code'])->header('Content-Type', 'application/json');

	}

	function removeFromCart(Request $request)
	{

		$cartIterms = Session::get('cart_items');
		if (!isset($cartIterms)) {
			$cartIterms = array();
		}

		foreach ($cartIterms as $key => $value) {

			$fourChar = substr($request->id, 0, 4);

			if ($fourChar != "cash" && isset($value['id']) && $value['id'] != "" && $value['id'] == $request->id) {

				unset($cartIterms[$key]);
				break;
			} else if ($fourChar == "cash") {
				unset($cartIterms[$key]);
				break;
			}
		}

		$cartIterms = array_values($cartIterms);
		Session::put('cart_items', $cartIterms);
		$response = successRes("Item successfully removed to your cart");
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	function setCart(Request $request)
	{

		$cartIterms = Session::get('cart_items');
		if (!isset($cartIterms)) {
			$cartIterms = array();
		}

		$fourChar = substr($request->id, 0, 4);
		if ($fourChar == "cash") {
			$alreadyCash = 0;
			if ($request->id == "cash-0") {

				$response = errorRes("Invalid cash");
				return response()->json($response)->header('Content-Type', 'application/json');
			}

			foreach ($cartIterms as $cA => $cV) {

				$fourChar = substr($cV['id'], 0, 4);
				if ($fourChar == "cash") {
					$alreadyCash = 1;
					$cartIterms[$cA]['id'] = $request->id;
				}
			}

			if ($alreadyCash == 0) {
				$cartIterms[count($cartIterms)]['id'] = $request->id;
			}
		} else {
			$cartIterms[count($cartIterms)]['id'] = $request->id;
		}

		Session::put('cart_items', $cartIterms);
		$response = successRes("Item successfully added to your cart");
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	function getCartCount()
	{
		$cartItemCount = 0;
		$cartIterms = Session::get('cart_items');
		if (isset($cartIterms) && is_array($cartIterms)) {
			$cartItemCount = count($cartIterms);
		}
		$response = successRes("Successfully get cart item count");
		$response['data'] = $cartItemCount;
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	function cart()
	{

		if (Auth::user()->type == 202) {

			$Architect = Architect::where('user_id', Auth::user()->id)->first();
			$point_data = $Architect;
		} else if (Auth::user()->type == 302) {

			$Electrician = Electrician::where('user_id', Auth::user()->id)->first();
			$point_data = $Electrician;
		}

		$data = array();
		$data['title'] = "Cart - Gift Product ";
		$data['point_data'] = $point_data;
		return view('crm/architect/cart', compact('data'));
	}

	function calculationProcessOfOrder($orderItems)
	{

		$isArchitect = isArchitect();
		$isElectrician = isElectrician();

		if ($isArchitect == 1) {
			$Parameter = Parameter::where('code', 'point-value-architect')->first();
		} else if ($isElectrician == 1) {
			$Parameter = Parameter::where('code', 'point-value-electrician')->first();
		}

		$pointValue = $Parameter->name_value;

		$order = array();
		$order['id'] = "orderID";
		$order['total_product_pv'] = 0;
		$order['total_cash_value'] = 0;
		$order['total_cashback_value'] = 0;
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
			$order['total_product_pv'] = $order['total_product_pv'] + $OrderItemsMRP;

			$orderItems[$key]['total_cash_value'] = 0;
			$orderItems[$key]['cash'] = 0;

			// if ($value['is_cash'] == 1) {
			// 	$orderItems[$key]['cash'] = $value['cash'];
			// 	$order['total_cash_value'] = $order['total_cash_value'] + ($orderItemQTY * $value['cash']);
			// 	$orderItems[$key]['total_cash_value'] = ($orderItemQTY * $value['cash']);
			// }

			$orderItems[$key]['total_cashback_value'] = 0;
			$orderItems[$key]['cashback'] = 0;
			if ($value['has_cashback'] == 1) {
				$orderItems[$key]['cashback'] = $value['cashback'];
				$order['total_cashback_value'] = $order['total_cashback_value'] + ($orderItemQTY * $value['cashback']);
				$orderItems[$key]['total_cashback_value'] = $orderItemQTY * $value['cashback'];
			}
		}

		$order['items'] = $orderItems;
		return $order;
	}

	function cartDetail()
	{
		$cartIterms = Session::get('cart_items');
		if (!isset($cartIterms)) {
			$cartIterms = array();
		}
		$isElectrician = isElectrician();
		$isArchitect = isArchitect();

		$products = array();
		$totalPV = 0;
		$totalCash = 0;
		$totalCashBack = 0;
		$totalCashPV = 0;

		foreach ($cartIterms as $key => $value) {

			$giftProduct = GiftProduct::find($value['id']);

			if ($giftProduct) {
				$GiftCategory = GiftCategory::find($giftProduct->gift_category_id);
				$giftProduct['category'] = $GiftCategory;
				$products[] = $giftProduct;
				$totalPV = $totalPV + $giftProduct->point_value;

				// if ($giftProduct->is_cash == 1 && $giftProduct->cash != 0) {
				// 	$totalCash = $totalCash + $giftProduct->cash;
				// }

				if ($giftProduct->has_cashback == 1 && $giftProduct->cashback != 0) {
					$totalCashBack = $totalCashBack + $giftProduct->cashback;
				}
			} else {

				$fourChar = substr($value['id'], 0, 4);
				if ($fourChar != "cash") {
					unset($cartIterms[$key]);
				} else {

					$piecesOfId = explode("-", $value['id']);
					$totalCashPV = $piecesOfId[1];
				}
			}
		}

		if ($isArchitect == 1) {
			$Parameter = Parameter::where('code', 'point-value-architect')->first();
		} else if ($isElectrician == 1) {
			$Parameter = Parameter::where('code', 'point-value-electrician')->first();
		}

		$pointValue = $Parameter->name_value;

		$totalCash = $totalCashPV * $pointValue;

		$totalProductPV = $totalPV;
		$totalPV = $totalProductPV + $totalCashPV;

		$cartIterms = array_values($cartIterms);
		Session::put('cart_items', $cartIterms);

		$isArchitect = isArchitect();
		$isElectrician = isElectrician();
		$UserCurrentPoint = 0;
		if ($isArchitect == 1) {

			$Architect = Architect::where('user_id', Auth::user()->id)->first();
			$checkOutBtnVisible = 0;

			if (count($cartIterms) > 0 && $Architect->total_point_current >= $totalPV) {
				$checkOutBtnVisible = 1;
			}
			$UserCurrentPoint = $Architect->total_point_current;
		} else if ($isElectrician == 1) {

			$Electrician = Electrician::where('user_id', Auth::user()->id)->first();
			$checkOutBtnVisible = 0;

			if (count($cartIterms) > 0 && $Electrician->total_point_current >= $totalPV) {
				$checkOutBtnVisible = 1;
			}
			$UserCurrentPoint = $Electrician->total_point_current;
		}

		$data = array();
		$data['products'] = $products;
		$data['total_pv'] = $totalPV;
		$data['total_product_pv'] = $totalProductPV;
		$data['user_total_pv'] = $UserCurrentPoint;
		$data['user_total_cashback'] = $totalCashBack;
		$data['user_total_cash'] = $totalCash;
		$data['user_total_cash_pv'] = $totalCashPV;
		$data['checkout_btn_visible'] = $checkOutBtnVisible;
		$response = successRes("Cart detail View");
		$response['cart_html'] = view('crm/architect/cart_detail', compact('data'))->render();
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	function previewOrder(Request $request)
	{

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

			$isArchitect = isArchitect();
			$isElectrician = isElectrician();
			$paymentMode = array();
			$paymentMode['bank_detail_account'] = "";
			$paymentMode['bank_detail_ifsc'] = "";
			$paymentMode['bank_detail_upi'] = "";

			if ($isArchitect == 1) {

				$Architect = Architect::select('aadhar_card', 'bank_detail_account', 'bank_detail_ifsc', 'bank_detail_upi')->where('user_id', Auth::user()->id)->first();
				$paymentMode['bank_detail_account'] = $Architect->bank_detail_account;
				$paymentMode['bank_detail_ifsc'] = $Architect->bank_detail_ifsc;
				$paymentMode['bank_detail_upi'] = $Architect->bank_detail_upi;

				$hasAadharCard = 0;
				if ($Architect && $Architect->aadhar_card != "") {
					$hasAadharCard = 1;
				}
			} else if ($isElectrician == 1) {
				$hasAadharCard = 1;
				$Electrician = Electrician::select('bank_detail_account', 'bank_detail_ifsc', 'bank_detail_upi')->where('user_id', Auth::user()->id)->first();

				$paymentMode['bank_detail_account'] = $Electrician->bank_detail_account;
				$paymentMode['bank_detail_ifsc'] = $Electrician->bank_detail_ifsc;
				$paymentMode['bank_detail_upi'] = $Electrician->bank_detail_upi;
			}

			$response = array();
			$cartIterms = Session::get('cart_items');
			$idWithKey = array();
			$finalCartItems = array();
			$totalCashPV = 0;
			foreach ($cartIterms as $key => $value) {

				$fourChar = substr($value['id'], 0, 4);
				if ($fourChar == "cash") {

					$piecesOfId = explode("-", $value['id']);
					$totalCashPV = $piecesOfId[1];

					continue;
				}

				if (!in_array($value['id'], array_keys($idWithKey))) {

					$cfinalCartItems = count($finalCartItems);
					$idWithKey[$value['id']] = $cfinalCartItems;
					$finalCartItems[$cfinalCartItems]['id'] = $value['id'];
					$finalCartItems[$cfinalCartItems]['qty'] = 1;
					$GiftProduct = GiftProduct::find($value['id']);
					$finalCartItems[$cfinalCartItems]['info'] = array();
					$finalCartItems[$cfinalCartItems]['info']['name'] = $GiftProduct->name;
					$finalCartItems[$cfinalCartItems]['info']['image'] = $GiftProduct->image;
					$finalCartItems[$cfinalCartItems]['point_value'] = $GiftProduct->point_value;
					$finalCartItems[$cfinalCartItems]['is_cash'] = $GiftProduct->is_cash;
					$finalCartItems[$cfinalCartItems]['cash'] = $GiftProduct->cash;
					$finalCartItems[$cfinalCartItems]['has_cashback'] = $GiftProduct->has_cashback;
					$finalCartItems[$cfinalCartItems]['cashback'] = $GiftProduct->cashback;
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

			if ($isArchitect == 1) {
				$Parameter = Parameter::where('code', 'point-value-architect')->first();
			} else if ($isElectrician == 1) {
				$Parameter = Parameter::where('code', 'point-value-electrician')->first();
			}

			$pointValue = $Parameter->name_value;

			$totalCash = $totalCashPV * $pointValue;

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
			$data['payment_mode'] = $paymentMode;
			$data['total_cash'] = $totalCash;
			$data['total_cash_pv'] = $totalCashPV;
			$response = successRes("Order Preview");
			$response['preview'] = view('crm/architect/orders_preview', compact('data'))->render();
		}
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	function placeOrder(Request $request)
	{

		$isArchitect = isArchitect();
		$isElectrician = isElectrician();

		if ($isArchitect == 1) {

			$Architect = Architect::select('id', 'aadhar_card')->where('user_id', Auth::user()->id)->first();

			$hasAadharCard = 0;
			if ($Architect && $Architect->aadhar_card != "") {
				$hasAadharCard = 1;
			}
		} else if ($isElectrician == 1) {
			$hasAadharCard = 1;
		}

		// if ($request->expectsJson()) {

		$rules = array();
		$rules['request_data'] = ['required'];
		$rules['request_data_items'] = ['required'];

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

			if ($isArchitect == 1) {

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
						$spaceUploadResponse = uploadFileOnSpaces(public_path($uploadedFile2), $uploadedFile2);
						if ($spaceUploadResponse != 1) {
							$uploadedFile2 = "";
						} else {
							unlink(public_path($uploadedFile2));
						}
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
			}

			$inputJSON = $request->request_data;
			$inputJSON = json_decode($inputJSON, true);

			$cartIterms = json_decode($request->request_data_items, true);
			$idWithKey = array();
			$finalCartItems = array();
			$totalCashPV = 0;
			foreach ($cartIterms as $key => $value) {

				$fourChar = substr($value['id'], 0, 4);
				if ($fourChar == "cash") {

					$piecesOfId = explode("-", $value['id']);
					$totalCashPV = $piecesOfId[1];

					continue;
				}

				if (!in_array($value['id'], array_keys($idWithKey))) {

					$GiftProduct = GiftProduct::find($value['id']);
					if ($GiftProduct->status == 1) {

						$cfinalCartItems = count($finalCartItems);
						$idWithKey[$value['id']] = $cfinalCartItems;
						$finalCartItems[$cfinalCartItems]['id'] = $value['id'];
						$finalCartItems[$cfinalCartItems]['qty'] = 1;
						$finalCartItems[$cfinalCartItems]['info'] = array();
						$finalCartItems[$cfinalCartItems]['info']['name'] = $GiftProduct->name;
						$finalCartItems[$cfinalCartItems]['info']['image'] = getSpaceFilePath($GiftProduct->image);
						$finalCartItems[$cfinalCartItems]['point_value'] = $GiftProduct->point_value;
						// $finalCartItems[$cfinalCartItems]['is_cash'] = $GiftProduct->is_cash;
						// $finalCartItems[$cfinalCartItems]['cash'] = $GiftProduct->cash;
						$finalCartItems[$cfinalCartItems]['has_cashback'] = $GiftProduct->has_cashback;
						$finalCartItems[$cfinalCartItems]['cashback'] = $GiftProduct->cashback;
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
			if ($isArchitect == 1) {
				$Architect = Architect::where('user_id', Auth::user()->id)->first();
				$userTotalPoint = (int) $Architect->total_point_current;
				$Parameter = Parameter::where('code', 'point-value-architect')->first();
			} else if ($isElectrician == 1) {
				$Electrician = Electrician::where('user_id', Auth::user()->id)->first();
				$Parameter = Parameter::where('code', 'point-value-electrician')->first();
				$userTotalPoint = (int) $Electrician->total_point_current;
			}

			$pointValue = $Parameter->name_value;
			$totalCash = $totalCashPV * $pointValue;

			if (count($orderDetail['items']) > 0 || $totalCashPV > 0) {
				if ($userTotalPoint >= ($orderDetail['total_product_pv'] + $totalCashPV)) {

					$paymentMode = 0;
					$paymentMode1['bank_detail_account'] = "";
					$paymentMode1['bank_detail_ifsc'] = "";
					$paymentMode1['bank_detail_upi'] = "";

					if (($orderDetail['total_cashback_value'] + $totalCash) > 0) {

						$hasAnyOrder = GiftProductOrder::where('user_id', Auth::user()->id)->first();
						if (!$hasAnyOrder) {

							if ($totalCashPV > 0 && $totalCashPV <= 100) {

								$response = array();
								$response['status'] = 0;
								$response['msg'] = "You can't withdraw cash first time less or 100";
								$response['statuscode'] = 400;
								return response()->json($response)->header('Content-Type', 'application/json');
							}
						}

						if (!isset($inputJSON['payment_mode'])) {

							$response = array();
							$response['status'] = 0;
							$response['msg'] = "Please select payment method";
							$response['statuscode'] = 400;
							return response()->json($response)->header('Content-Type', 'application/json');
						} else {
							$paymentMode = $inputJSON['payment_mode'];

							if ($paymentMode == 1) {

								if (!isset($inputJSON['bank_detail_account']) || trim($inputJSON['bank_detail_account']) == "") {

									$response = array();
									$response['status'] = 0;
									$response['msg'] = "Please enter bank account";
									$response['statuscode'] = 400;
									return response()->json($response)->header('Content-Type', 'application/json');
								}

								if (!isset($inputJSON['bank_detail_ifsc']) || trim($inputJSON['bank_detail_ifsc']) == "") {

									$response = array();
									$response['status'] = 0;
									$response['msg'] = "Please enter bank ifsc code";
									$response['statuscode'] = 400;
									return response()->json($response)->header('Content-Type', 'application/json');
								}
							} else {

								if (!isset($inputJSON['bank_detail_upi']) || trim($inputJSON['bank_detail_upi']) == "") {

									$response = array();
									$response['status'] = 0;
									$response['msg'] = "Please enter bank upi";
									$response['statuscode'] = 400;
									return response()->json($response)->header('Content-Type', 'application/json');
								}
							}

							$paymentMode1['bank_detail_account'] = $inputJSON['bank_detail_account'];
							$paymentMode1['bank_detail_ifsc'] = $inputJSON['bank_detail_ifsc'];
							$paymentMode1['bank_detail_upi'] = $inputJSON['bank_detail_upi'];

							if ($isArchitect == 1) {

								$Architect->bank_detail_account = $paymentMode1['bank_detail_account'];
								$Architect->bank_detail_ifsc = $paymentMode1['bank_detail_ifsc'];
								$Architect->bank_detail_upi = $paymentMode1['bank_detail_upi'];
								$Architect->save();
							}

							if ($isElectrician == 1) {

								$Electrician->bank_detail_account = $paymentMode1['bank_detail_account'];
								$Electrician->bank_detail_ifsc = $paymentMode1['bank_detail_ifsc'];
								$Electrician->bank_detail_upi = $paymentMode1['bank_detail_upi'];
								$Electrician->save();
							}
						}
					}

					$totalPV = $orderDetail['total_product_pv'] + $totalCashPV;

					$GiftProductOrder = new GiftProductOrder();
					$GiftProductOrder->user_id = Auth::user()->id;
					$GiftProductOrder->total_point_value = $totalPV;
					$GiftProductOrder->product_point_value = $orderDetail['total_product_pv'];
					$GiftProductOrder->cash_point_value = $totalCashPV;

					$GiftProductOrder->d_address_line1 = $inputJSON['d_address_line1'];
					$GiftProductOrder->d_address_line2 = isset($inputJSON['d_address_line2']) ? $inputJSON['d_address_line2'] : '';
					$GiftProductOrder->d_city_id = $inputJSON['d_city_id'];
					$GiftProductOrder->d_country_id = $inputJSON['d_country_id'];
					$GiftProductOrder->d_pincode = $inputJSON['d_pincode'];
					$GiftProductOrder->d_state_id = $inputJSON['d_state_id'];
					$GiftProductOrder->total_cashback = $orderDetail['total_cashback_value'];
					$GiftProductOrder->total_cash = $totalCash;
					$GiftProductOrder->payment_mode = $paymentMode;
					$GiftProductOrder->bank_detail_account = $paymentMode1['bank_detail_account'];
					$GiftProductOrder->bank_detail_ifsc = $paymentMode1['bank_detail_ifsc'];
					$GiftProductOrder->bank_detail_upi = $paymentMode1['bank_detail_upi'];
					$GiftProductOrder->save();

					foreach ($orderDetail['items'] as $key => $value) {
						$GiftProductOrderItem = new GiftProductOrderItem();
						$GiftProductOrderItem->user_id = $GiftProductOrder->user_id;
						$GiftProductOrderItem->gift_product_order_id = $GiftProductOrder->id;
						$GiftProductOrderItem->gift_product_id = $value['id'];
						$GiftProductOrderItem->qty = $value['qty'];
						$GiftProductOrderItem->point_value = $value['point_value'];
						$GiftProductOrderItem->total_point_value = $value['total_point_value'];
						$GiftProductOrderItem->cashback = $value['cashback'];
						$GiftProductOrderItem->total_cashback = $value['total_cashback_value'];
						// $GiftProductOrderItem->cash = $value['cash'];
						// $GiftProductOrderItem->total_cash = $value['total_cash_value'];
						$GiftProductOrderItem->save();
					}

					if ($isArchitect == 1) {

						$Architect->total_point_used = $Architect->total_point_used + $GiftProductOrder->total_point_value;
						$Architect->total_point_current = $Architect->total_point_current - $GiftProductOrder->total_point_value;
						$Architect->save();
						$salePersionId = $Architect->sale_person_id;
					} else if ($isElectrician == 1) {

						$Electrician->total_point_used = $Electrician->total_point_used + $GiftProductOrder->total_point_value;
						$Electrician->total_point_current = $Electrician->total_point_current - $GiftProductOrder->total_point_value;
						$Electrician->save();
						$salePersionId = $Electrician->sale_person_id;
					}

					$debugLog = array();
					$debugLog['for_user_id'] = Auth::user()->id;
					$debugLog['name'] = "point-redeem";
					$debugLog['points'] = $GiftProductOrder->total_point_value;
					$debugLog['order_id'] = $GiftProductOrder->id;
					$debugLog['description'] = $GiftProductOrder->total_point_value . " Point redeem from order #" . $GiftProductOrder->id;
					$debugLog['type'] = '';
					saveCRMUserLog($debugLog);
					$response = successRes("Successfully generated order #" . $GiftProductOrder->id);
					Session::put('cart_items', array());

					$mobileNotificationTitle = "New Order Place";
					$mobileNotificationMessage = "New Order Places #" . $GiftProductOrder->id . " By " . Auth::user()->first_name . " " . Auth::user()->last_name;

					if($isArchitect == 1){
						$mobileNotificationTitle = "New Reward Order(Arc) Place";
						$mobileNotificationMessage = "New Reward Order(Arc) Places #" . $GiftProductOrder->id . " By " . Auth::user()->first_name . " " . Auth::user()->last_name;

					}else if($isElectrician == 1){
						$mobileNotificationTitle = "New Reward Order(Ele) Place";
						$mobileNotificationMessage = "New Reward Order(Ele) Places #" . $GiftProductOrder->id . " By " . Auth::user()->first_name . " " . Auth::user()->last_name;

					}else{
						$mobileNotificationTitle = "New Reward Order Place";
						$mobileNotificationMessage = "New Reward Order Places #" . $GiftProductOrder->id . " By " . Auth::user()->first_name . " " . Auth::user()->last_name;

					}
					$notificationUserids = getParentSalePersonsIds($salePersionId);
					$UsersNotificationTokens = UsersNotificationTokens($notificationUserids);
					sendNotificationTOAndroid($mobileNotificationTitle, $mobileNotificationMessage, $UsersNotificationTokens,'Gift Order',$GiftProductOrder);
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

	function detail(Request $request)
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
					$GiftProductOrderItem[$key]['info']['image'] = getSpaceFilePath($value['image']);
				}
				$GiftProductOrder['items'] = $GiftProductOrderItem;
				$response = successRes("Order detail");
				$data = array();
				// $data['preview'] = 0;
				$data['order'] = json_decode(json_encode($GiftProductOrder), true);
				//$data['order']['total_cashback_value'] = $data['order']['total_cashback'];

				//$data['total_cash_value'] = $data['order']['total_cash'];
				//$data['total_cash_pv'] = $data['order']['cash_point_value'];
				//$data['order']['total_product_pv'] = $data['order']['product_point_value'];
				//$data['total_cash'] = $data['order']['total_cash'];

				$data['name'] = Auth::user()->first_name . " " . Auth::user()->last_name;
				$data['email'] = Auth::user()->email;
				$data['phone_number'] = Auth::user()->phone_number;

				$data['d_country'] = getCountryName($data['order']['d_country_id']);
				$data['d_state'] = getStateName($data['order']['d_state_id']);
				$data['d_city'] = getCityName($data['order']['d_city_id']);
				$data['d_pincode'] = $data['order']['d_pincode'];
				$data['d_address_line1'] = $data['order']['d_address_line1'];
				$data['d_address_line2'] = $data['order']['d_address_line2'];
				$response = successRes("Order Preview");
				$response['data'] = $data;
			} else {
				$response = errorRes("Invalid order id");
			}
		}

		return response()->json($response)->header('Content-Type', 'application/json');
	}
}
