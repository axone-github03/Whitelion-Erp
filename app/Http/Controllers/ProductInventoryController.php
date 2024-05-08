<?php

namespace App\Http\Controllers;

use App\Models\ChannelPartner;
use App\Models\DataMaster;
use App\Models\MainMaster;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductGroup;
use App\Models\ProductInventory;
use App\Models\ProductInventoryFeature;
use App\Models\User;
use App\Models\UserDiscount;
use DB;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Config;
use Mail;

//use Session;

class ProductInventoryController extends Controller
{

	public function __construct()
	{

		$this->middleware(function ($request, $next) {

			$tabCanAccessBy = array(0, 1);

			if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
				return redirect()->route('dashboard');
			}

			return $next($request);
		});
	}

	public function index()
	{

		$data = array();
		$data['title'] = "Product Inventory ";
		return view('product/inventory', compact('data'));
	}

	function ajax(Request $request)
	{
		//DB::enableQueryLog();

		$searchColumns = array(
			0 => 'product_inventory.id',
			1 => 'product_inventory.description',
			2 => 'product_inventory.price',
			3 => 'product_inventory.weight',
			4 => 'product_inventory.quantity',
			5 => 'product_brand.name',
			6 => 'product_code.name',

		);

		$sortingColumns = array(
			0 => 'product_inventory.id',
			1 => 'product_brand.name',
			2 => 'product_code.name',
			3 => 'product_inventory.description',
			4 => 'product_inventory.price',
			5 => 'product_inventory.weight',
			6 => 'product_inventory.quantity',

		);

		$selectColumns = array(
			0 => 'product_inventory.id',
			1 => 'product_brand.name as product_brand_name',
			2 => 'product_code.name as product_code_name',
			3 => 'product_inventory.description',
			4 => 'product_inventory.price',
			5 => 'product_inventory.weight',
			6 => 'product_inventory.quantity',
			7 => 'product_inventory.status',

		);

		$recordsTotal = ProductInventory::count();
		$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.
		$query = ProductInventory::query();
		$query->leftJoin('data_master as product_brand', 'product_brand.id', '=', 'product_inventory.product_brand_id');
		$query->leftJoin('data_master as product_code', 'product_code.id', '=', 'product_inventory.product_code_id');
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

			$data[$key]['id'] = highlightString($data[$key]['id'], $search_value);
			$data[$key]['product_brand'] = "<p>" . highlightString($value['product_brand_name'], $search_value) . "</p>";
			$data[$key]['product_code'] = "<p>" . highlightString($value['product_code_name'], $search_value) . "</p>";
			if ($value['status'] == 0) {

				$data[$key]['description'] = '<span class="badge badge-pill badge-soft-danger font-size-11">' . $data[$key]['description'] . '</span>';
			} else if ($value['status'] == 1) {
				$data[$key]['description'] = '<span class="badge badge-pill badge-soft-success font-size-11">' . $data[$key]['description'] . '</span>';
			}

			$data[$key]['price'] = '<i class="fas fa-rupee-sign"></i>' . highlightString(priceLable($value['price']), $search_value);
			$data[$key]['weight'] = highlightString($value['weight'] . " gm", $search_value);

			$uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';

			$uiAction .= '<li class="list-inline-item px-2">';
			$uiAction .= '<a onclick="editDiscount(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Discount"><i class="bx bxs-discount"></i></a>';
			$uiAction .= '</li>';

			$uiAction .= '<li class="list-inline-item px-2">';
			$uiAction .= '<a onclick="editView(\'' . $value['id'] . '\',\'plus\')" href="javascript: void(0);" title="Edit"><i class="bx bx-plus-circle"></i></a>';
			$uiAction .= '</li>';

			$uiAction .= '<li class="list-inline-item px-2">';
			$uiAction .= '<a onclick="editView(\'' . $value['id'] . '\',\'minus\')" href="javascript: void(0);" title="Edit"><i class="bx bx-minus-circle"></i></a>';
			$uiAction .= '</li>';

			$uiAction .= '<li class="list-inline-item px-2">';
			$uiAction .= '<a onclick="editView(\'' . $value['id'] . '\',\'all\')" href="javascript: void(0);" title="Edit"><i class="bx bx-edit-alt"></i></a>';
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

	public function discountAjax(Request $request)
	{

		$searchColumns = array(
			0 => 'users.id',
			1 => 'users.first_name',
			2 => 'users.last_name',
			3 => 'users.email',
			4 => 'users.phone_number',
			5 => 'channel_partner.firm_name',

		);

		$sortingColumns = array(
			0 => 'users.id',
			1 => 'channel_partner.firm_name',
			2 => 'users.email',

		);

		$selectColumns = array(
			0 => 'users.id',
			1 => 'users.first_name',
			2 => 'users.last_name',
			3 => 'users.email',
			4 => 'users.phone_number',
			5 => 'user_discounts.discount_percentage',
			6 => 'user_discounts.id as user_discount_id',
			7 => 'users.type',
			8 => 'channel_partner.firm_name',

		);

		if ($request->isLoadDiscountTable != 0) {

			$recordsTotal = User::where('type', $request->user_type)->count();
			$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows
			//DB::enableQueryLog();
			$productInventoryId = $request->product_inventory_id;
			$query = User::query();
			$query->where('users.type', $request->user_type);
			$query->leftJoin('user_discounts', function ($join) use ($productInventoryId) {

				$join->on('user_discounts.user_id', '=', 'users.id');
				$join->on('user_discounts.product_inventory_id', '=', DB::raw($productInventoryId));
			});
			$query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');

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
			// print_r(DB::getQueryLog());
			// die;

			$data = json_decode(json_encode($data), true);
			if ($isFilterApply == 1) {
				$recordsFiltered = count($data);
			}

			foreach ($data as $key => $value) {

				$data[$key]['name'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">' . $value['firm_name'] . '</a></h5>
                <p class="text-muted mb-0">' . getUserTypeName($value['type']) . '</p>';

				$data[$key]['email'] = '<p class="text-muted mb-0">' . $value['email'] . '</p>
             <p class="text-muted mb-0">' . $value['phone_number'] . '</p>';

				if (!isset($value['user_discount_id']) || $value['user_discount_id'] == null) {
					$data[$key]['discount_percentage'] = 0;
					$value['user_discount_id'] = 0;
				} else {
					$data[$key]['discount_percentage'] = $value['discount_percentage'];
				}

				$data[$key]['new_discount_percentage'] = "<input type='number'   min='0' max='100' step='1' class='form-control new-discount-cls valid-discount' id='" . $request->product_inventory_id . "-" . $value['id'] . "-" . $value['user_discount_id'] . "' value='" . $data[$key]['discount_percentage'] . "'  />";
			}
		} else {

			$data = array();
			$recordsTotal = 0;
			$recordsFiltered = 0;
		}

		$jsonData = array(
			"draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
			"recordsTotal" => intval($recordsTotal), // total number of records
			"recordsFiltered" => intval($recordsFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
			"data" => $data, // total data array

		);
		return $jsonData;
	}

	public function discountSave(Request $request)
	{

		$validator = Validator::make($request->all(), [
			'discount_percentage' => ['required'],
			'id' => ['required'],

		]);

		if ($validator->fails()) {

			$response = array();
			$response['status'] = 0;
			$response['msg'] = "The request could not be understood by the server due to malformed syntax";
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();
		} else {

			$IdString = explode("-", $request->id);
			$ProductInventoryId = $IdString[0];
			$UserID = $IdString[1];
			$UserDiscountId = $IdString[2];
			if ($UserDiscountId != 0) {

				$UserDiscount = UserDiscount::find($UserDiscountId);
				$UserDiscount->discount_percentage = $request->discount_percentage;
				$UserDiscount->save();

				$User = User::select('id', 'first_name', 'last_name')->find($UserID);
				$ProductInventory = ProductInventory::select('id', 'description')->find($ProductInventoryId);

				$debugLog = array();
				$debugLog['name'] = "discount-updated";
				$debugLog['description'] = "#" . $User->id . "(" . $User->first_name . " " . $User->last_name . ") for product #" . $ProductInventory->id . "(" . $ProductInventory->description . ")  has been updated to " . $request->discount_percentage;
				saveDebugLog($debugLog);
			} else {

				$UserDiscount = new UserDiscount();
				$UserDiscount->product_inventory_id = $ProductInventoryId;
				$UserDiscount->user_id = $UserID;
				$UserDiscount->discount_percentage = $request->discount_percentage;
				$UserDiscount->save();

				$User = User::select('id', 'first_name', 'last_name')->find($UserID);
				$ProductInventory = ProductInventory::select('id', 'description')->find($ProductInventoryId);

				$debugLog = array();
				$debugLog['name'] = "discount-updated";
				$debugLog['description'] = "#" . $User->id . "(" . $User->first_name . " " . $User->last_name . ") for product #" . $ProductInventory->id . "(" . $ProductInventory->description . ")  has been updated to " . $request->discount_percentage;
				saveDebugLog($debugLog);
			}
			$response = successRes();

			return response()->json($response)->header('Content-Type', 'application/json');
		}
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function discountSaveAll(Request $request)
	{

		$validator = Validator::make($request->all(), [
			'discount_percentage' => ['required'],
			'user_type' => ['required'],
			'product_inventory_id' => ['required'],

		]);

		if ($validator->fails()) {

			$response = array();
			$response['status'] = 0;
			$response['msg'] = "The request could not be understood by the server due to malformed syntax";
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();
		} else {

			$query = User::select('id');
			$query->where('users.type', $request->user_type);
			$Users = $query->get();
			foreach ($Users as $keyU => $valueU) {

				$UserDiscount = UserDiscount::where('user_id', $valueU['id'])->where('product_inventory_id', $request->product_inventory_id)->first();
				if ($UserDiscount) {
					$UserDiscount->discount_percentage = $request->discount_percentage;
					$UserDiscount->save();
				} else {

					$UserDiscount = new UserDiscount();
					$UserDiscount->discount_percentage = $request->discount_percentage;
					$UserDiscount->product_inventory_id = $request->product_inventory_id;
					$UserDiscount->user_id = $valueU['id'];
					$UserDiscount->save();
				}

				$User = User::select('id', 'first_name', 'last_name')->find($valueU['id']);
				$ProductInventory = ProductInventory::select('id', 'description')->find($request->product_inventory_id);

				$debugLog = array();
				$debugLog['name'] = "discount-updated";
				$debugLog['description'] = "#" . $User->id . "(" . $User->first_name . " " . $User->last_name . ") for product #" . $ProductInventory->id . "(" . $ProductInventory->description . ")  has been updated to " . $request->discount_percentage;
				saveDebugLog($debugLog);
			}

			$response = successRes();

			return response()->json($response)->header('Content-Type', 'application/json');
		}
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function save(Request $request)
	{

		$validator = Validator::make($request->all(), [
			'product_inventory_id' => ['required'],

		]);

		if ($validator->fails()) {

			$response = array();
			$response['status'] = 0;
			$response['msg'] = "The request could not be understood by the server due to malformed syntax";
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();

			return response()->json($response)->header('Content-Type', 'application/json');
		} else {

			if ($request->product_inventory_id == 0) {

				//

				$hasWarning = isset($request->product_inventory_has_warning) ? $request->product_inventory_has_warning : "off";
				$notifyWhenOrder = isset($request->product_inventory_notify_when_order) ? $request->product_inventory_notify_when_order : "off";

				$rules = array();
				$rules['product_inventory_id'] = 'required';
				$rules['product_brand_id'] = 'required';
				$rules['product_code_id'] = 'required';
				$rules['product_inventory_description'] = 'required';
				$rules['product_inventory_quantity'] = 'required';
				$rules['product_inventory_price'] = 'required';
				$rules['product_inventory_weight'] = 'required';
				$rules['product_inventory_status'] = 'required';
				if ($hasWarning == "on") {
					$hasWarning = 1;
					$rules['product_inventory_warning'] = 'required';
				} else {
					$hasWarning = 0;
				}

				if ($notifyWhenOrder == "on") {
					$notifyWhenOrder = 1;
					$rules['product_inventory_notify_emails'] = 'required';
				} else {
					$notifyWhenOrder = 0;
				}

				// $validator = Validator::make($request->all(), [
				// 	'product_inventory_id' => ['required'],
				// 	'product_brand_id' => ['required'],
				// 	'product_code_id' => ['required'],
				// 	'product_inventory_description' => ['required'],
				// 	'product_inventory_quantity' => ['required', 'integer', 'max:100000000', 'min:0'],
				// 	'product_inventory_price' => ['required', 'integer', 'max:100000000'],
				// 	'product_inventory_weight' => ['required', 'integer', 'max:100000000'],
				// 	'product_inventory_status' => ['required'],

				// ]);

				$customMessage = array();

				$validator = Validator::make($request->all(), $rules, $customMessage);

				if ($validator->fails()) {

					$response = array();
					$response['status'] = 0;
					$response['msg'] = "The request could not be understood by the server due to malformed syntax";
					$response['statuscode'] = 400;
					$response['data'] = $validator->errors();

					return response()->json($response)->header('Content-Type', 'application/json');
				} else {

					$uploadedFile1 = "/s/product/default.png";
					$uploadedFile2 = "/s/product/default.png";

					if ($request->hasFile('product_inventory_image')) {

						$folderPathImage = '/s/product';
						$fileObject1 = $request->file('product_inventory_image');

						$extension = $fileObject1->getClientOriginalExtension();
						$fileName1 = time() . mt_rand(10000, 99999) . '.' . $extension;

						$destinationPath = public_path($folderPathImage);

						$fileObject1->move($destinationPath, $fileName1);

						if (File::exists(public_path($folderPathImage . "/" . $fileName1))) {

							createThumbs(public_path($folderPathImage . "/" . $fileName1), public_path($folderPathImage . "/thumb-" . $fileName1), 200);

							$uploadedFile1 = $folderPathImage . "/" . $fileName1;

							$spaceUploadResponse = uploadFileOnSpaces(public_path($uploadedFile1), $uploadedFile1);
							if ($spaceUploadResponse != 1) {
								$uploadedFile1 = "";
							} else {
								unlink(public_path($uploadedFile1));
							}

							$uploadedFile2 = $folderPathImage . "/thumb-" . $fileName1;

							$spaceUploadResponse = uploadFileOnSpaces(public_path($uploadedFile2), $uploadedFile2);
							if ($spaceUploadResponse != 1) {
								$uploadedFile1 = "";
							} else {
								unlink(public_path($uploadedFile2));
							}
						}
					}

					$warning = isset($request->product_inventory_warning) ? $request->product_inventory_warning : '';
					$notify_emails = isset($request->product_inventory_notify_emails) ? $request->product_inventory_notify_emails : '';

					$alreadyName = ProductInventory::query();
					$alreadyName->where('product_brand_id', $request->product_brand_id);
					$alreadyName->where('product_code_id', $request->product_code_id);
					$alreadyName = $alreadyName->first();

					if ($alreadyName) {

						$response = errorRes("already product exits, Try with another product brand or product code");
					} else {

						$ProductInventory = new ProductInventory();
						$ProductInventory->product_brand_id = $request->product_brand_id;
						$ProductInventory->product_code_id = $request->product_code_id;
						$ProductInventory->description = $request->product_inventory_description;
						$ProductInventory->image = $uploadedFile1;
						$ProductInventory->thumb = $uploadedFile2;
						$ProductInventory->quantity = $request->product_inventory_quantity;
						$ProductInventory->price = $request->product_inventory_price;
						$ProductInventory->weight = $request->product_inventory_weight;

						$ProductInventory->status = $request->product_inventory_status;
						$ProductInventory->has_warning = $hasWarning;
						$ProductInventory->warning = $warning;
						$ProductInventory->notify_when_order = $notifyWhenOrder;
						$ProductInventory->notify_emails = $notify_emails;
						$ProductInventory->save();
						if ($ProductInventory) {

							if (isset($request->featute) && is_array($request->featute)) {
								foreach ($request->featute as $keyF => $valueF) {

									$ProductInventoryFeature = new ProductInventoryFeature;
									$ProductInventoryFeature->product_inventory_id = $ProductInventory->id;
									$ProductInventoryFeature->code = $keyF;
									$ProductInventoryFeature->feature_value = $valueF;
									$ProductInventoryFeature->save();
								}
							}


							$debugLog = array();
							$debugLog['name'] = "product-new";
							$debugLog['product_inventory_id'] = $ProductInventory->id;
							$debugLog['request_quantity'] = $ProductInventory->quantity;
							$debugLog['quantity'] = $ProductInventory->quantity;

							$debugLog['description'] = "New Product";
							saveProductLog($debugLog);


							$response = successRes("Successfully added product inventory");
						} else {

							$response = errorRes("Something went to wrong");
						}
					}
				}
			} else {

				$product_inventory_type_process = isset($request->product_inventory_type_process) ? $request->product_inventory_type_process : '';

				if ($product_inventory_type_process == "plus") {

					$validator = Validator::make($request->all(), [
						'product_inventory_id' => ['required'],
						'product_inventory_purpose' => ['required'],
						'product_inventory_status' => ['required'],
						'product_inventory_quantity_plus' => ['required', 'integer', 'max:100000000', 'min:0'],

					]);
				} else if ($product_inventory_type_process == "minus") {

					$validator = Validator::make($request->all(), [
						'product_inventory_id' => ['required'],
						'product_inventory_purpose' => ['required'],
						'product_inventory_status' => ['required'],
						'product_inventory_quantity_minus' => ['required', 'integer', 'max:100000000', 'min:0'],

					]);
				} else if ($product_inventory_type_process == "all") {

					$hasWarning = isset($request->product_inventory_has_warning) ? $request->product_inventory_has_warning : "off";
					$notifyWhenOrder = isset($request->product_inventory_notify_when_order) ? $request->product_inventory_notify_when_order : "off";

					// $validator = Validator::make($request->all(), [
					// 	'product_inventory_id' => ['required'],
					// 	'product_brand_id' => ['required'],
					// 	'product_code_id' => ['required'],
					// 	'product_inventory_description' => ['required'],
					// 	'product_inventory_price' => ['required', 'integer', 'max:100000000'],
					// 	'product_inventory_weight' => ['required', 'integer', 'max:100000000'],
					// 	'product_inventory_status' => ['required'],

					// ]);
					$rules = array();
					$rules['product_inventory_id'] = 'required';
					$rules['product_brand_id'] = 'required';
					$rules['product_code_id'] = 'required';
					$rules['product_inventory_description'] = 'required';
					//$rules['product_inventory_quantity'] = 'required';
					$rules['product_inventory_price'] = 'required';
					$rules['product_inventory_weight'] = 'required';
					$rules['product_inventory_status'] = 'required';
					if ($hasWarning == "on") {
						$hasWarning = 1;
						$rules['product_inventory_warning'] = 'required';
					} else {
						$hasWarning = 0;
					}

					if ($notifyWhenOrder == "on") {
						$notifyWhenOrder = 1;
						$rules['product_inventory_notify_emails'] = 'required';
					} else {
						$notifyWhenOrder = 0;
					}
					$customMessage = array();

					$validator = Validator::make($request->all(), $rules, $customMessage);
					$warning = isset($request->product_inventory_warning) ? $request->product_inventory_warning : '';
					$notify_emails = isset($request->product_inventory_notify_emails) ? $request->product_inventory_notify_emails : '';
				}

				if ($validator->fails()) {

					$response = array();
					$response['status'] = 0;
					$response['msg'] = "The request could not be understood by the server due to malformed syntax";
					$response['statuscode'] = 400;
					$response['data'] = $validator->errors();

					return response()->json($response)->header('Content-Type', 'application/json');
				} else {

					$ProductInventory = ProductInventory::find($request->product_inventory_id);
					$ProductInventoryStatus = $ProductInventory['status'];







					if ($ProductInventory && $product_inventory_type_process == "plus") {
						$ProductInventory->status = $request->product_inventory_status;

						$ProductInventory->quantity = $ProductInventory->quantity + $request->product_inventory_quantity_plus;
						$ProductInventory->save();

						$response = successRes("Successfully update product inventory quantity");
						$debugLog = array();
						$debugLog['name'] = "product-quantity-plus";
						$debugLog['product_inventory_id'] = $ProductInventory->id;
						$debugLog['request_quantity'] = $request->product_inventory_quantity_plus;
						$debugLog['quantity'] = $ProductInventory->quantity;
						$debugLog['description'] = $request->product_inventory_purpose;

						saveProductLog($debugLog);
					} else if ($ProductInventory && $product_inventory_type_process == "minus" && ($ProductInventory->quantity - $request->product_inventory_quantity_minus) >= 0) {

						$ProductInventory->status = $request->product_inventory_status;

						$ProductInventory->quantity = $ProductInventory->quantity - $request->product_inventory_quantity_minus;
						$ProductInventory->save();
						$response = successRes("Successfully update product inventory quantity");

						$debugLog = array();
						$debugLog['name'] = "product-quantity-minus";
						$debugLog['product_inventory_id'] = $ProductInventory->id;
						$debugLog['request_quantity'] = $request->product_inventory_quantity_minus;
						$debugLog['quantity'] = $ProductInventory->quantity;
						$debugLog['description'] = $request->product_inventory_purpose;
						saveProductLog($debugLog);
					} else if ($ProductInventory && $product_inventory_type_process == "all") {

						$uploadedFile1 = "";

						if ($request->hasFile('product_inventory_image')) {

							$folderPathImage = '/s/product';
							$fileObject1 = $request->file('product_inventory_image');

							$extension = $fileObject1->getClientOriginalExtension();
							$fileName1 = time() . mt_rand(10000, 99999) . '.' . $extension;

							$destinationPath = public_path($folderPathImage);

							$fileObject1->move($destinationPath, $fileName1);

							if (File::exists(public_path($folderPathImage . "/" . $fileName1))) {

								createThumbs(public_path($folderPathImage . "/" . $fileName1), public_path($folderPathImage . "/thumb-" . $fileName1), 200);

								// $uploadedFile1 = $fileName1;
								$uploadedFile1 = $folderPathImage . "/" . $fileName1;

								$spaceUploadResponse = uploadFileOnSpaces(public_path($uploadedFile1), $uploadedFile1);
								if ($spaceUploadResponse != 1) {
									$uploadedFile1 = "";
								} else {
									unlink(public_path($uploadedFile1));
								}

								$uploadedFile2 = $folderPathImage . "/thumb-" . $fileName1;

								$spaceUploadResponse = uploadFileOnSpaces(public_path($uploadedFile2), $uploadedFile2);
								if ($spaceUploadResponse != 1) {
									$uploadedFile1 = "";
								} else {
									unlink(public_path($uploadedFile2));
								}
							}
						}

						if ($uploadedFile1 != "") {
							$ProductInventory->image = $uploadedFile1;
							$ProductInventory->thumb = $uploadedFile2;
						}

						$ProductInventory->product_brand_id = $request->product_brand_id;
						$ProductInventory->product_code_id = $request->product_code_id;
						$ProductInventory->description = $request->product_inventory_description;
						$ProductInventory->price = $request->product_inventory_price;
						$ProductInventory->weight = $request->product_inventory_weight;
						$ProductInventory->status = $request->product_inventory_status;
						$ProductInventory->has_warning = $hasWarning;
						$ProductInventory->warning = $warning;
						$ProductInventory->notify_when_order = $notifyWhenOrder;
						$ProductInventory->notify_emails = $notify_emails;
						$ProductInventory->save();


						$debugLog = array();
						$debugLog['name'] = "product-update";
						$debugLog['product_inventory_id'] = $ProductInventory->id;
						$debugLog['request_quantity'] = 0;
						$debugLog['quantity'] = $ProductInventory->quantity;
						$debugLog['description'] = "Product Detail Update";
						saveProductLog($debugLog);

						if ($ProductInventoryStatus != $request->product_inventory_status) {

							$selesPerson = User::select('id', 'first_name', 'last_name', 'email')->where('type', 2)->get();
							$ProductInventory = ProductInventory::with(array('product_brand' => function ($query) {
								$query->select('id', 'name');
							}, 'product_code' => function ($query) {
								$query->select('id', 'name');
							}))->find($ProductInventory->id);


							$configrationForNotify = configrationForNotify();

							// foreach ($selesPerson as $User) {
							// 	$params = [];
							// 	$params['from_name'] = $configrationForNotify['from_name'];
							// 	$params['from_email'] = $configrationForNotify['from_email'];
							// 	$params['to_email'] = $User->email;
							// 	$params['to_name'] = $configrationForNotify['to_name'];
							// 	$params['bcc_email'] = "sales@whitelion.in";
							// 	$params['subject'] = "Product Status | Whitelion";
							// 	$params['user_name'] = $User->first_name . " " . $User->last_name;
							// 	$params['first_name'] = $User->first_name;
							// 	$params['last_name'] = $User->last_name;
							// 	$params['reset_password_token'] = $User->reset_password_token;
							// 	$params['product_name'] = $ProductInventory['product_brand']['name'];
							// 	$params['product_code'] = $ProductInventory['product_code']['name'];
							// 	$params['description'] = $ProductInventory['description'];
							// 	if ($request->product_inventory_status == 1) {
							// 		$params['status'] = 'Active';
							// 	} else {
							// 		$params['status'] = 'InActive';
							// 	}

							// 	if (Config::get('app.env') == "local") {
							// 		$params['to_email'] = $configrationForNotify['test_email'];
							// 		$params['bcc_email'] = $configrationForNotify['test_email_bcc'];
							// 	}

							// 	Mail::send('emails.product_status_change', ['params' => $params], function ($m) use ($params) {
							// 		$m->from($params['from_email'], $params['from_name']);
							// 		$m->to($params['to_email'], $params['user_name'])->subject($params['subject']);
							// 	});
							// }
							// foreach ($selesPerson as $User) {
							$params = [];
							$params['from_name'] = $configrationForNotify['from_name'];
							$params['from_email'] = $configrationForNotify['from_email'];
							// $params['to_email'] = $User->email;
							$params['to_email'] = $configrationForNotify['test_email'];
							$params['to_name'] = $configrationForNotify['to_name'];
							$params['bcc_email'] = "sales@whitelion.in";
							$params['subject'] = "Product Status | Whitelion";
							// $params['user_name'] = $User->first_name . " " . $User->last_name;
							$params['user_name'] = 'jay';
							// $params['first_name'] = $User->first_name;
							// $params['last_name'] = $User->last_name;
							// $params['reset_password_token'] = $User->reset_password_token;
							$params['product_name'] = $ProductInventory['product_brand']['name'];
							$params['product_code'] = $ProductInventory['product_code']['name'];
							$params['description'] = $ProductInventory['description'];
							if ($request->product_inventory_status == 1) {
								$params['status'] = 'Active';
							} else {
								$params['status'] = 'Inactive';
							}

							if (Config::get('app.env') == "local") {
								$params['to_email'] = $configrationForNotify['test_email'];
								$params['bcc_email'] = $configrationForNotify['test_email_bcc'];
							}

							Mail::send('emails.product_status_change', ['params' => $params], function ($m) use ($params) {
								$m->from($params['from_email'], $params['from_name']);
								$m->to($params['to_email'], $params['user_name'])->subject($params['subject']);
							});
							// }
						}
						$response = successRes("Successfully update product inventory quantity");
					} else {

						$response = errorRes("Invalid Product");
					}
				}
			}
		}

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function detail(Request $request)
	{

		$ProductInventory = ProductInventory::with(array('product_brand' => function ($query) {
			$query->select('id', 'name');
		}, 'product_code' => function ($query) {
			$query->select('id', 'name');
		}))->find($request->id);
		if ($ProductInventory) {

			$response = successRes("Successfully get product inventory");
			$response['data'] = $ProductInventory;
		} else {
			$response = errorRes("Invalid id");
		}
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function searchBrand(Request $request)
	{

		$DataMaster = array();

		$MainMaster = MainMaster::select('id')->where('code', 'PRODUCT_BRAND')->first();
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

	public function searchCode(Request $request)
	{

		$DataMaster = array();

		$MainMaster = MainMaster::select('id')->where('code', 'PRODUCT_CODE')->first();
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

	public function searchChannelPartner(Request $request)
	{

		$q = $request->q;

		$ChannelPartner = array();
		$ChannelPartner = ChannelPartner::select('channel_partner.user_id as id', DB::raw('CONCAT(channel_partner.firm_name) AS text'));
		$ChannelPartner->leftJoin('users', 'users.id', '=', 'channel_partner.user_id');
		$ChannelPartner->where('users.status', 1);
		$ChannelPartner->where('channel_partner.reporting_manager_id', 0);

		$ChannelPartner->where(function ($query) use ($q) {

			$query->where('channel_partner.firm_name', 'like', $q . "%");
		});
		$ChannelPartner->limit(9);
		$ChannelPartner = $ChannelPartner->get();

		$response = array();
		$response['results'] = $ChannelPartner;
		$response['pagination']['more'] = false;
		return response()->json($response)->header('Content-Type', 'application/json');
	}
	function searchProductGroup(Request $request)
	{

		$ProductGroup = ProductGroup::select('id', 'name as text');
		$q = $request->q;
		$ProductGroup->where(function ($query) use ($q) {
			$query->where('name', 'like', '%' . $q . '%');
		});
		$ProductGroup->limit(5);
		$data = $ProductGroup->get();
		$data = json_decode(json_encode($data), true);

		$response = array();
		$response['results'] = $data;
		$response['pagination']['more'] = false;
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	function searchProductInventory(Request $request)
	{

		$DataMaster = array();
		$PRODUCT_CODE = MainMaster::select('id')->where('code', 'PRODUCT_CODE')->first();
		$PRODUCT_BRAND = MainMaster::select('id')->where('code', 'PRODUCT_BRAND')->first();

		$DataMaster = ProductInventory::select('product_inventory.id', DB::raw('CONCAT(product_brand.name," ",product_code.name," (",product_inventory.description,")" )  as text'));
		$DataMaster->leftJoin('data_master as product_brand', 'product_brand.id', '=', 'product_inventory.product_brand_id');
		$DataMaster->leftJoin('data_master as product_code', 'product_code.id', '=', 'product_inventory.product_code_id');

		$searchValue = $request->q;
		$DataMaster->where('product_inventory.status', 1);
		$DataMaster->where('product_brand.main_master_id', $PRODUCT_BRAND->id);
		$DataMaster->where('product_code.main_master_id', $PRODUCT_CODE->id);

		$searchValuePieces = explode(" ", $searchValue);

		if (count($searchValuePieces) > 1) {

			$DataMaster->where(function ($query) use ($searchValuePieces) {
				$query->where('product_brand.name', 'like', $searchValuePieces[0] . "%");
				$query->Where('product_code.name', 'like', $searchValuePieces[1] . "%");
			});
		} else {

			$DataMaster->where(function ($query) use ($searchValue) {
				$query->where('product_brand.name', 'like', $searchValue . "%");
				// $query->orWhere('product_code.name', 'like', "%" . $searchValue . "%");
			});
		}

		$DataMaster->limit(15);
		$DataMaster = $DataMaster->get();

		$response = array();
		$response['results'] = $DataMaster;
		$response['pagination']['more'] = false;
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function reportGenerate(Request $request)
	{

		$startDate = date('Y-m-d 00:00:00', strtotime($request->report_start_date));
		$startDate = date('Y-m-d H:i:s', strtotime($startDate . " -5 hours"));
		$startDate = date('Y-m-d H:i:s', strtotime($startDate . " -30 minutes"));
		$endDate = date('Y-m-d 23:59:59', strtotime($request->report_end_date));
		$endDate = date('Y-m-d H:i:s', strtotime($endDate . " -5 hours"));
		$endDate = date('Y-m-d H:i:s', strtotime($endDate . " -30 minutes"));

		$productGroupProductIds = array();

		if (isset($request->report_product_group_id) && is_array($request->report_product_group_id)) {

			$ProductGroup = ProductGroup::query();
			$ProductGroup->select('product_group.product_brand');
			$ProductGroup->orderBy('product_group.id', 'desc');
			$ProductGroup = $ProductGroup->get();
			$productBrandIds = array();

			foreach ($ProductGroup as $PKey => $PVal) {

				$tempBrandIds = explode(",", $PVal->product_brand);

				$productBrandIds = array_merge($productBrandIds, $tempBrandIds);
			}

			$productBrandIds = array_unique($productBrandIds);
			$productBrandIds = array_values($productBrandIds);

			if (count($productBrandIds) > 0) {

				$ProductInventory = ProductInventory::select('product_inventory.id');
				$ProductInventory->whereIn('product_inventory.product_brand_id', $productBrandIds);
				$ProductInventory = $ProductInventory->get();

				foreach ($ProductInventory as $key => $value) {

					$productGroupProductIds[] = $value->id;
				}
			}
		}

		if ($request->report_status == 0) {

			$Order = Order::query();
			$Order->select('orders.id', 'channel_partner.firm_name', 'orders.created_at');
			$Order->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
			$Order->orderBy('orders.id', 'desc');
			$Order->where('orders.created_at', '>=', $startDate);
			$Order->where('orders.created_at', '<=', $endDate);
			$Order->where('channel_partner.reporting_manager_id', 0);

			if (isset($request->report_channel_partner_user_id) && is_array($request->channel_partner_user_id)) {

				$Order->whereIn('orders.channel_partner_user_id', $request->report_channel_partner_user_id);
			}

			$Order->whereIn('orders.status', [0, 1, 2, 3]);
			$Order = $Order->get();

			$orderIds = array(0);

			foreach ($Order as $key => $value) {

				$orderIds[] = $value->id;
			}

			$orderItemsPendingQTY = array();

			$OrderItem = OrderItem::query();
			$OrderItem->select('order_items.pending_qty', 'order_items.order_id', 'order_items.product_inventory_id', 'order_items.order_id', 'order_items.qty');
			$OrderItem->orderBy('order_items.id', 'desc');
			$OrderItem->whereIn('order_items.order_id', $orderIds);

			if (isset($request->report_product_inventory_id) && is_array($request->report_product_inventory_id)) {

				$OrderItem->whereIn('order_items.product_inventory_id', $request->report_product_inventory_id);
			}

			if (count($productGroupProductIds) > 0) {

				$OrderItem->whereIn('order_items.product_inventory_id', $productGroupProductIds);
			}

			$OrderItems = $OrderItem->get();

			$pendingQty = array();

			foreach ($OrderItems as $key => $value) {
				$productIds[] = $value->product_inventory_id;

				if (isset($pendingQty[$value->product_inventory_id])) {
					$pendingQty[$value->product_inventory_id] = $pendingQty[$value->product_inventory_id] + $value->qty;
				} else {

					$pendingQty[$value->product_inventory_id] = $value->qty;
				}
			}

			$productIds = array_unique($productIds);
			$productIds = array_unique($productIds);

			$Products = ProductInventory::select('product_inventory.id', DB::raw('CONCAT(product_brand.name," ",product_code.name )  as text'), 'product_inventory.quantity');
			$Products->leftJoin('data_master as product_brand', 'product_brand.id', '=', 'product_inventory.product_brand_id');
			$Products->leftJoin('data_master as product_code', 'product_code.id', '=', 'product_inventory.product_code_id');
			$Products->whereIn('product_inventory.id', $productIds);
			$Products = $Products->get();

			$headers = array("Product Name", "Order Placed", "In Stock", "Deficlt");

			$tableData = array();
			$tableHeaderData = array();

			if ($request->report_export_type == 0) {
				$tableHeaderData[] = $headers;
			} else if ($request->report_export_type == 1) {

				header('Content-Type: text/csv');
				header('Content-Disposition: attachment; filename="reports.csv"');
				$fp = fopen('php://output', 'wb');
				fputcsv($fp, $headers);
			}

			foreach ($Products as $key => $value) {

				$lineVal = array(
					$value->text,
					$pendingQty[$value->id],
					$value->quantity,
					$pendingQty[$value->id] - $value->quantity,

				);

				if ($request->report_export_type == 0) {
					$tableData[] = $lineVal;
				} else {
					fputcsv($fp, $lineVal, ",");
				}
			}
			if ($request->report_export_type == 0) {

				$data = array();
				$data['title'] = "Report - Product Inventory ";
				$data['table_header_data'] = $tableHeaderData;
				$data['table_data'] = $tableData;

				return view('product/report', compact('data'));
			} else {
				fclose($fp);
			}
		} else if ($request->report_status == 1) {

			$Products = ProductInventory::select('product_inventory.id', DB::raw('CONCAT(product_brand.name," ",product_code.name )  as text'), 'product_inventory.quantity');
			$Products->leftJoin('data_master as product_brand', 'product_brand.id', '=', 'product_inventory.product_brand_id');
			$Products->leftJoin('data_master as product_code', 'product_code.id', '=', 'product_inventory.product_code_id');
			$Products = $Products->get();

			$headers = array("Product Name", "Stock");

			$tableData = array();
			$tableHeaderData = array();

			if ($request->report_export_type == 0) {
				$tableHeaderData[] = $headers;
			} else if ($request->report_export_type == 1) {

				header('Content-Type: text/csv');
				header('Content-Disposition: attachment; filename="reports.csv"');
				$fp = fopen('php://output', 'wb');
				fputcsv($fp, $headers);
			}

			foreach ($Products as $keyP => $valueP) {

				$lineVal = array(
					$valueP->text,
					$valueP->quantity,
				);

				if ($request->report_export_type == 0) {
					$tableData[] = $lineVal;
				} else {
					fputcsv($fp, $lineVal, ",");
				}
			}

			if ($request->report_export_type == 0) {

				$data = array();
				$data['title'] = "Report - Product Inventory ";
				$data['table_header_data'] = $tableHeaderData;
				$data['table_data'] = $tableData;

				return view('product/report', compact('data'));
			} else {
				fclose($fp);
			}
		} else if ($request->report_status == 2) {

			$Order = Order::query();
			$Order->select('orders.id', 'channel_partner.firm_name', 'orders.created_at');
			$Order->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
			$Order->orderBy('orders.id', 'desc');
			$Order->where('channel_partner.reporting_manager_id', 0);
			$Order->where('orders.created_at', '>=', $startDate);
			$Order->where('orders.created_at', '<=', $endDate);
			if (isset($request->report_channel_partner_user_id) && is_array($request->channel_partner_user_id)) {

				$Order->whereIn('orders.channel_partner_user_id', $request->report_channel_partner_user_id);
			}

			$Order->whereIn('orders.status', [0, 1, 2, 3]);
			$Order = $Order->get();

			$orderIds = array(0);

			foreach ($Order as $key => $value) {

				$orderIds[] = $value->id;
			}

			$orderItemsPendingQTY = array();

			$OrderItem = OrderItem::query();
			$OrderItem->select('order_items.pending_qty', 'order_items.order_id', 'order_items.product_inventory_id', 'order_items.order_id', 'order_items.qty');
			$OrderItem->orderBy('order_items.id', 'desc');
			$OrderItem->whereIn('order_items.order_id', $orderIds);

			if (isset($request->report_product_inventory_id) && is_array($request->report_product_inventory_id)) {

				$OrderItem->whereIn('order_items.product_inventory_id', $request->report_product_inventory_id);
			}
			if ($request->export_type != 0) {
				$OrderItem->where('order_items.pending_qty', '>', 0);
			}
			$OrderItems = $OrderItem->get();

			$productIds = array(0);

			$orderIds = array(0);

			foreach ($OrderItems as $key => $value) {
				$productIds[] = $value->product_inventory_id;

				if ($request->export_type == 0) {
					$orderItemsPendingQTY[$value->order_id . "_" . $value->product_inventory_id] = $value->qty;
				} else {
					$orderItemsPendingQTY[$value->order_id . "_" . $value->product_inventory_id] = $value->pending_qty;
				}

				$orderIds[] = $value->order_id;
			}

			if (isset($request->product_inventory_id) && is_array($request->product_inventory_id)) {

				$Order = Order::query();
				$Order->select('orders.id', 'channel_partner.firm_name', 'orders.created_at');
				$Order->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
				$Order->orderBy('orders.id', 'desc');
				$Order->whereIn('orders.id', $orderIds);
				$Order = $Order->get();
			}

			$productIds = array_unique($productIds);
			$productIds = array_values($productIds);

			$Products = ProductInventory::select('product_inventory.id', DB::raw('CONCAT(product_brand.name," ",product_code.name )  as text'));
			$Products->leftJoin('data_master as product_brand', 'product_brand.id', '=', 'product_inventory.product_brand_id');
			$Products->leftJoin('data_master as product_code', 'product_code.id', '=', 'product_inventory.product_code_id');
			$Products->whereIn('product_inventory.id', $productIds);
			$Products = $Products->get();

			$productIdText = array("");

			foreach ($productIds as $key => $value) {

				if ($key != 0) {

					foreach ($Products as $keyP => $valueP) {

						if ($value == $valueP->id) {
							$productIdText[] = $valueP->text;
							break;
						}
					}
				}
			}

			$headers = array("Channel Partner/Products", "#orderId", "orderDate");
			foreach ($productIdText as $key => $value) {
				if ($key != 0) {
					$headers[] = $value;
				}
			}

			$tableData = array();
			$tableHeaderData = array();

			if ($request->report_export_type == 0) {
				$tableHeaderData[] = $headers;
			} else if ($request->report_export_type == 1) {

				header('Content-Type: text/csv');
				header('Content-Disposition: attachment; filename="reports.csv"');
				$fp = fopen('php://output', 'wb');
				fputcsv($fp, $headers);
			}

			$totalProducts = array();

			foreach ($Order as $key => $value) {

				$created_at = convertOrderDateTime($value->created_at, "date");

				$lineVal = array(
					$value->firm_name,
					$value->id,
					$created_at,

				);

				foreach ($productIds as $keyP => $valeP) {

					if ($keyP != 0) {

						if (isset($orderItemsPendingQTY[$value->id . "_" . $valeP])) {

							$countOfProduct = $orderItemsPendingQTY[$value->id . "_" . $valeP];

							$lineVal[] = $countOfProduct;
							if (isset($totalProducts[$keyP])) {

								$totalProducts[$keyP] = $totalProducts[$keyP] + $countOfProduct;
							} else {

								$totalProducts[$keyP] = $countOfProduct;
							}
						} else {
							$lineVal[] = "";
						}
					}
				}

				if ($request->report_export_type == 0) {
					$tableData[] = $lineVal;
				} else {
					fputcsv($fp, $lineVal, ",");
				}
			}

			$lineVal = array(
				"Total",
				"-",
				"-",

			);

			foreach ($totalProducts as $key => $value) {

				$lineVal[] = $value;
			}

			if ($request->report_export_type == 0) {
				$tableData[] = $lineVal;
			} else {
				fputcsv($fp, $lineVal, ",");
			}

			if ($request->report_export_type == 0) {

				$data = array();
				$data['title'] = "Report - Product Inventory ";
				$data['table_header_data'] = $tableHeaderData;
				$data['table_data'] = $tableData;

				return view('product/report', compact('data'));
			} else {
				fclose($fp);
			}
		} else if ($request->report_status == 0) {

			$Order = Order::query();
			$Order->select('orders.id', 'channel_partner.firm_name', 'orders.created_at');
			$Order->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
			$Order->where('channel_partner.reporting_manager_id', 0);
			$Order->orderBy('orders.id', 'desc');
			$Order->where('orders.created_at', '>=', $startDate);
			$Order->where('orders.created_at', '<=', $endDate);
			if (isset($request->report_channel_partner_user_id) && is_array($request->channel_partner_user_id)) {

				$Order->whereIn('orders.channel_partner_user_id', $request->report_channel_partner_user_id);
			}

			$Order->whereIn('orders.status', [0, 1, 2, 3]);
			$Order = $Order->get();

			$orderIds = array(0);

			foreach ($Order as $key => $value) {

				$orderIds[] = $value->id;
			}

			$orderItemsPendingQTY = array();

			$OrderItem = OrderItem::query();
			$OrderItem->select('order_items.pending_qty', 'order_items.order_id', 'order_items.product_inventory_id', 'order_items.order_id', 'order_items.qty');
			$OrderItem->orderBy('order_items.id', 'desc');
			$OrderItem->whereIn('order_items.order_id', $orderIds);

			if (isset($request->report_product_inventory_id) && is_array($request->report_product_inventory_id)) {

				$OrderItem->whereIn('order_items.product_inventory_id', $request->report_product_inventory_id);
			}

			if (count($productGroupProductIds) > 0) {

				$OrderItem->whereIn('order_items.product_inventory_id', $productGroupProductIds);
			}

			$OrderItems = $OrderItem->get();

			$pendingQty = array();

			foreach ($OrderItems as $key => $value) {
				$productIds[] = $value->product_inventory_id;

				if (isset($pendingQty[$value->product_inventory_id])) {
					$pendingQty[$value->product_inventory_id] = $pendingQty[$value->product_inventory_id] + $value->qty;
				} else {

					$pendingQty[$value->product_inventory_id] = $value->qty;
				}
			}

			$productIds = array_unique($productIds);
			$productIds = array_unique($productIds);

			$Products = ProductInventory::select('product_inventory.id', DB::raw('CONCAT(product_brand.name," ",product_code.name )  as text'), 'product_inventory.quantity');
			$Products->leftJoin('data_master as product_brand', 'product_brand.id', '=', 'product_inventory.product_brand_id');
			$Products->leftJoin('data_master as product_code', 'product_code.id', '=', 'product_inventory.product_code_id');
			$Products->whereIn('product_inventory.id', $productIds);
			$Products = $Products->get();

			$headers = array("Product Name", "Order Placed", "In Stock", "Deficlt");

			$tableData = array();
			$tableHeaderData = array();

			if ($request->report_export_type == 0) {
				$tableHeaderData[] = $headers;
			} else if ($request->report_export_type == 1) {

				header('Content-Type: text/csv');
				header('Content-Disposition: attachment; filename="reports.csv"');
				$fp = fopen('php://output', 'wb');
				fputcsv($fp, $headers);
			}

			foreach ($Products as $key => $value) {

				$lineVal = array(
					$value->text,
					$pendingQty[$value->id],
					$value->quantity,
					$pendingQty[$value->id] - $value->quantity,

				);

				if ($request->report_export_type == 0) {
					$tableData[] = $lineVal;
				} else {
					fputcsv($fp, $lineVal, ",");
				}
			}
			if ($request->report_export_type == 0) {

				$data = array();
				$data['title'] = "Report - Product Inventory ";
				$data['table_header_data'] = $tableHeaderData;
				$data['table_data'] = $tableData;

				return view('product/report', compact('data'));
			} else {
				fclose($fp);
			}
		} else if ($request->report_status == 3) {

			$Order = Order::query();
			$Order->select('orders.id', 'channel_partner.firm_name', 'orders.created_at');
			$Order->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
			$Order->orderBy('orders.id', 'desc');
			$Order->where('channel_partner.reporting_manager_id', 0);
			$Order->where('orders.created_at', '>=', $startDate);
			$Order->where('orders.created_at', '<=', $endDate);
			if (isset($request->report_channel_partner_user_id) && is_array($request->channel_partner_user_id)) {

				$Order->whereIn('orders.channel_partner_user_id', $request->report_channel_partner_user_id);
			}

			$Order->whereIn('orders.status', [0, 1, 2, 3]);
			$Order = $Order->get();

			$orderIds = array(0);

			foreach ($Order as $key => $value) {

				$orderIds[] = $value->id;
			}

			$orderItemsPendingQTY = array();

			$OrderItem = OrderItem::query();
			$OrderItem->select('order_items.pending_qty', 'order_items.order_id', 'order_items.product_inventory_id', 'order_items.order_id', 'order_items.qty');
			$OrderItem->orderBy('order_items.id', 'desc');
			$OrderItem->whereIn('order_items.order_id', $orderIds);

			if (isset($request->report_product_inventory_id) && is_array($request->report_product_inventory_id)) {

				$OrderItem->whereIn('order_items.product_inventory_id', $request->report_product_inventory_id);
			}

			if (count($productGroupProductIds) > 0) {

				$OrderItem->whereIn('order_items.product_inventory_id', $productGroupProductIds);
			}

			$OrderItems = $OrderItem->get();

			$pendingQty = array();

			foreach ($OrderItems as $key => $value) {
				$productIds[] = $value->product_inventory_id;

				if (isset($pendingQty[$value->product_inventory_id])) {
					$pendingQty[$value->product_inventory_id] = $pendingQty[$value->product_inventory_id] + $value->qty;
				} else {

					$pendingQty[$value->product_inventory_id] = $value->qty;
				}
			}

			$productIds = array_unique($productIds);
			$productIds = array_unique($productIds);

			$Products = ProductInventory::select('product_inventory.id', DB::raw('CONCAT(product_brand.name," ",product_code.name )  as text'), 'product_inventory.quantity');
			$Products->leftJoin('data_master as product_brand', 'product_brand.id', '=', 'product_inventory.product_brand_id');
			$Products->leftJoin('data_master as product_code', 'product_code.id', '=', 'product_inventory.product_code_id');
			$Products->whereIn('product_inventory.id', $productIds);
			$Products = $Products->get();

			$headers = array("Product Name", "Deficlt");

			$tableData = array();
			$tableHeaderData = array();

			if ($request->report_export_type == 0) {
				$tableHeaderData[] = $headers;
			} else if ($request->report_export_type == 1) {

				header('Content-Type: text/csv');
				header('Content-Disposition: attachment; filename="reports.csv"');
				$fp = fopen('php://output', 'wb');
				fputcsv($fp, $headers);
			}

			foreach ($Products as $key => $value) {

				$lineVal = array(
					$value->text,
					$pendingQty[$value->id] - $value->quantity,

				);

				if ($request->report_export_type == 0) {
					$tableData[] = $lineVal;
				} else {
					fputcsv($fp, $lineVal, ",");
				}
			}
			if ($request->report_export_type == 0) {

				$data = array();
				$data['title'] = "Report - Product Inventory ";
				$data['table_header_data'] = $tableHeaderData;
				$data['table_data'] = $tableData;

				return view('product/report', compact('data'));
			} else {
				fclose($fp);
			}
		} else if ($request->report_status == 4) {

			$Order = Order::query();
			$Order->select('orders.id', 'channel_partner.firm_name', 'orders.created_at');
			$Order->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
			$Order->orderBy('orders.id', 'desc');
			$Order->where('channel_partner.reporting_manager_id', 0);
			$Order->where('orders.created_at', '>=', $startDate);
			$Order->where('orders.created_at', '<=', $endDate);
			if (isset($request->report_channel_partner_user_id) && is_array($request->channel_partner_user_id)) {

				$Order->whereIn('orders.channel_partner_user_id', $request->report_channel_partner_user_id);
			}

			$Order->whereIn('orders.status', [0, 1, 2, 3]);
			$Order = $Order->get();

			$orderIds = array(0);

			foreach ($Order as $key => $value) {

				$orderIds[] = $value->id;
			}

			$orderItemsPendingQTY = array();

			$OrderItem = OrderItem::query();
			$OrderItem->select('order_items.pending_qty', 'order_items.order_id', 'order_items.product_inventory_id', 'order_items.order_id', 'order_items.qty');
			$OrderItem->orderBy('order_items.id', 'desc');
			$OrderItem->whereIn('order_items.order_id', $orderIds);

			if (isset($request->report_product_inventory_id) && is_array($request->report_product_inventory_id)) {

				$OrderItem->whereIn('order_items.product_inventory_id', $request->report_product_inventory_id);
			}

			if (count($productGroupProductIds) > 0) {

				$OrderItem->whereIn('order_items.product_inventory_id', $productGroupProductIds);
			}

			$OrderItems = $OrderItem->get();

			$pendingQty = array();

			foreach ($OrderItems as $key => $value) {
				$productIds[] = $value->product_inventory_id;

				if (isset($pendingQty[$value->product_inventory_id])) {
					$pendingQty[$value->product_inventory_id] = $pendingQty[$value->product_inventory_id] + $value->qty;
				} else {

					$pendingQty[$value->product_inventory_id] = $value->qty;
				}
			}

			$productIds = array_unique($productIds);
			$productIds = array_unique($productIds);

			$Products = ProductInventory::select('product_inventory.id', DB::raw('CONCAT(product_brand.name," ",product_code.name )  as text'), 'product_inventory.quantity');
			$Products->leftJoin('data_master as product_brand', 'product_brand.id', '=', 'product_inventory.product_brand_id');
			$Products->leftJoin('data_master as product_code', 'product_code.id', '=', 'product_inventory.product_code_id');
			$Products->whereIn('product_inventory.id', $productIds);
			$Products = $Products->get();

			$headers = array("Product Name", "Deficlt");

			$tableData = array();
			$tableHeaderData = array();

			if ($request->report_export_type == 0) {
				$tableHeaderData[] = $headers;
			} else if ($request->report_export_type == 1) {

				header('Content-Type: text/csv');
				header('Content-Disposition: attachment; filename="reports.csv"');
				$fp = fopen('php://output', 'wb');
				fputcsv($fp, $headers);
			}

			foreach ($Products as $key => $value) {

				if (($pendingQty[$value->id] - $value->quantity) > 0) {

					$lineVal = array(
						$value->text,
						$pendingQty[$value->id] - $value->quantity,

					);

					if ($request->report_export_type == 0) {
						$tableData[] = $lineVal;
					} else {
						fputcsv($fp, $lineVal, ",");
					}
				}
			}
			if ($request->report_export_type == 0) {

				$data = array();
				$data['title'] = "Report - Product Inventory ";
				$data['table_header_data'] = $tableHeaderData;
				$data['table_data'] = $tableData;

				return view('product/report', compact('data'));
			} else {
				fclose($fp);
			}
		} else if ($request->report_status == 5) {

			$Order = Order::query();
			$Order->select('orders.id', 'channel_partner.firm_name', 'orders.created_at');
			$Order->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
			$Order->orderBy('orders.id', 'desc');
			$Order->where('channel_partner.reporting_manager_id', 0);
			$Order->where('orders.created_at', '>=', $startDate);
			$Order->where('orders.created_at', '<=', $endDate);
			if (isset($request->report_channel_partner_user_id) && is_array($request->channel_partner_user_id)) {

				$Order->whereIn('orders.channel_partner_user_id', $request->report_channel_partner_user_id);
			}

			$Order->whereIn('orders.status', [0, 1, 2, 3]);
			$Order = $Order->get();

			$orderIds = array(0);

			foreach ($Order as $key => $value) {

				$orderIds[] = $value->id;
			}

			$orderItemsPendingQTY = array();

			$OrderItem = OrderItem::query();
			$OrderItem->select('order_items.pending_qty', 'order_items.order_id', 'order_items.product_inventory_id', 'order_items.order_id', 'order_items.qty');
			$OrderItem->orderBy('order_items.id', 'desc');
			$OrderItem->whereIn('order_items.order_id', $orderIds);

			if (isset($request->report_product_inventory_id) && is_array($request->report_product_inventory_id)) {

				$OrderItem->whereIn('order_items.product_inventory_id', $request->report_product_inventory_id);
			}

			if (count($productGroupProductIds) > 0) {

				$OrderItem->whereIn('order_items.product_inventory_id', $productGroupProductIds);
			}

			$OrderItems = $OrderItem->get();

			$pendingQty = array();

			foreach ($OrderItems as $key => $value) {
				$productIds[] = $value->product_inventory_id;

				if (isset($pendingQty[$value->product_inventory_id])) {
					$pendingQty[$value->product_inventory_id] = $pendingQty[$value->product_inventory_id] + $value->qty;
				} else {

					$pendingQty[$value->product_inventory_id] = $value->qty;
				}
			}

			$productIds = array_unique($productIds);
			$productIds = array_unique($productIds);

			$Products = ProductInventory::select('product_inventory.id', DB::raw('CONCAT(product_brand.name," ",product_code.name )  as text'), 'product_inventory.quantity');
			$Products->leftJoin('data_master as product_brand', 'product_brand.id', '=', 'product_inventory.product_brand_id');
			$Products->leftJoin('data_master as product_code', 'product_code.id', '=', 'product_inventory.product_code_id');
			$Products->whereIn('product_inventory.id', $productIds);
			$Products = $Products->get();

			$headers = array("Product Name", "Deficlt");

			$tableData = array();
			$tableHeaderData = array();

			if ($request->report_export_type == 0) {
				$tableHeaderData[] = $headers;
			} else if ($request->report_export_type == 1) {

				header('Content-Type: text/csv');
				header('Content-Disposition: attachment; filename="reports.csv"');
				$fp = fopen('php://output', 'wb');
				fputcsv($fp, $headers);
			}

			foreach ($Products as $key => $value) {

				if (($pendingQty[$value->id] - $value->quantity) < 0) {

					$lineVal = array(
						$value->text,
						$pendingQty[$value->id] - $value->quantity,

					);

					if ($request->report_export_type == 0) {
						$tableData[] = $lineVal;
					} else {
						fputcsv($fp, $lineVal, ",");
					}
				}
			}
			if ($request->report_export_type == 0) {

				$data = array();
				$data['title'] = "Report - Product Inventory ";
				$data['table_header_data'] = $tableHeaderData;
				$data['table_data'] = $tableData;

				return view('product/report', compact('data'));
			} else {
				fclose($fp);
			}
		}
	}

	public function reportGenerate2(Request $request)
	{

		$startDate = date('Y-m-d 00:00:00', strtotime($request->report_start_date));
		$startDate = date('Y-m-d H:i:s', strtotime($startDate . " -5 hours"));
		$startDate = date('Y-m-d H:i:s', strtotime($startDate . " -30 minutes"));
		$endDate = date('Y-m-d 23:59:59', strtotime($request->report_end_date));
		$endDate = date('Y-m-d H:i:s', strtotime($endDate . " -5 hours"));
		$endDate = date('Y-m-d H:i:s', strtotime($endDate . " -30 minutes"));

		if ($request->report_status == 0) {

			$Order = Order::query();
			$Order->select('orders.id', 'channel_partner.firm_name', 'orders.created_at');
			$Order->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
			$Order->orderBy('orders.id', 'desc');
			$Order->where('orders.created_at', '>=', $startDate);
			$Order->where('orders.created_at', '<=', $endDate);
			$Order->where('channel_partner.reporting_manager_id', 0);
			if (isset($request->report_channel_partner_user_id) && is_array($request->channel_partner_user_id)) {

				$Order->whereIn('orders.channel_partner_user_id', $request->report_channel_partner_user_id);
			}

			$Order->whereIn('orders.status', [0, 1, 2, 3]);
			$Order = $Order->get();

			$orderIds = array(0);

			foreach ($Order as $key => $value) {

				$orderIds[] = $value->id;
			}

			$orderItemsPendingQTY = array();

			$OrderItem = OrderItem::query();
			$OrderItem->select('order_items.pending_qty', 'order_items.order_id', 'order_items.product_inventory_id', 'order_items.order_id', 'order_items.qty');
			$OrderItem->orderBy('order_items.id', 'desc');
			$OrderItem->whereIn('order_items.order_id', $orderIds);

			if (isset($request->product_inventory_id) && is_array($request->product_inventory_id)) {

				$OrderItem->whereIn('order_items.product_inventory_id', $request->product_inventory_id);
			}
			if ($request->export_type != 0) {
				$OrderItem->where('order_items.pending_qty', '>', 0);
			}
			$OrderItems = $OrderItem->get();

			$productIds = array(0);

			$orderIds = array(0);

			foreach ($OrderItems as $key => $value) {
				$productIds[] = $value->product_inventory_id;

				if ($request->export_type == 0) {
					$orderItemsPendingQTY[$value->order_id . "_" . $value->product_inventory_id] = $value->qty;
				} else {
					$orderItemsPendingQTY[$value->order_id . "_" . $value->product_inventory_id] = $value->pending_qty;
				}

				$orderIds[] = $value->order_id;
			}

			if (isset($request->product_inventory_id) && is_array($request->product_inventory_id)) {

				$Order = Order::query();
				$Order->select('orders.id', 'channel_partner.firm_name', 'orders.created_at');
				$Order->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
				$Order->where('channel_partner.reporting_manager_id', 0);
				$Order->orderBy('orders.id', 'desc');
				$Order->whereIn('orders.id', $orderIds);
				$Order = $Order->get();
			}

			$productIds = array_unique($productIds);
			$productIds = array_values($productIds);

			$Products = ProductInventory::select('product_inventory.id', DB::raw('CONCAT(product_brand.name," ",product_code.name )  as text'), 'product_inventory.quantity');
			$Products->leftJoin('data_master as product_brand', 'product_brand.id', '=', 'product_inventory.product_brand_id');
			$Products->leftJoin('data_master as product_code', 'product_code.id', '=', 'product_inventory.product_code_id');
			$Products->whereIn('product_inventory.id', $productIds);
			$Products = $Products->get();

			$productIdText = array("");
			$currentStock = array("");

			foreach ($productIds as $key => $value) {

				if ($key != 0) {

					foreach ($Products as $keyP => $valueP) {

						if ($value == $valueP->id) {
							$productIdText[] = $valueP->text;
							$currentStock[] = $valueP->quantity;
							break;
						}
					}
				}
			}

			$headers = array("Channel Partner/Products", "#orderId", "orderDate");
			// foreach ($productIdText as $key => $value) {
			// 	if ($key != 0) {
			// 		$headers[] = "Placed - " . $value;
			// 		$headers[] = "Deficit - " . $value;
			// 		$headers[] = "Stock - " . $value;
			// 	}

			// }

			$tableData = array();
			$tableHeaderData = array();

			if ($request->report_export_type == 0) {
				$tableHeaderData[] = $headers;
			} else if ($request->report_export_type == 1) {

				header('Content-Type: text/csv');
				header('Content-Disposition: attachment; filename="reports.csv"');
				$fp = fopen('php://output', 'wb');
				fputcsv($fp, $headers);
			}

			foreach ($Order as $key => $value) {

				$created_at = convertOrderDateTime($value->created_at, "date");

				$lineVal = array(
					$value->firm_name,
					$value->id,
					$created_at,

				);

				foreach ($productIds as $keyP => $valeP) {

					if ($keyP != 0) {

						if (isset($orderItemsPendingQTY[$value->id . "_" . $valeP])) {

							$lineVal[] = $orderItemsPendingQTY[$value->id . "_" . $valeP];
							$lineVal[] = $orderItemsPendingQTY[$value->id . "_" . $valeP] - $currentStock[$keyP];
							$lineVal[] = $currentStock[$keyP];
						} else {
							$lineVal[] = "";
							$lineVal[] = "";
							$lineVal[] = "";
						}
					}
				}

				if ($request->report_export_type == 0) {
					$tableData[] = $lineVal;
				} else {
					fputcsv($fp, $lineVal, ",");
				}
			}
			if ($request->report_export_type == 0) {

				$data = array();
				$data['title'] = "Report - Product Inventory ";
				$data['table_header_data'] = $tableHeaderData;
				$data['table_data'] = $tableData;

				return view('product/report', compact('data'));
			} else {
				fclose($fp);
			}
		} else if ($request->report_status == 1) {

			$Products = ProductInventory::select('product_inventory.id', DB::raw('CONCAT(product_brand.name," ",product_code.name )  as text'), 'product_inventory.quantity');
			$Products->leftJoin('data_master as product_brand', 'product_brand.id', '=', 'product_inventory.product_brand_id');
			$Products->leftJoin('data_master as product_code', 'product_code.id', '=', 'product_inventory.product_code_id');
			$Products = $Products->get();

			$headers = array("Product Name", "Stock");

			$tableData = array();
			$tableHeaderData = array();

			if ($request->report_export_type == 0) {
				$tableHeaderData[] = $headers;
			} else if ($request->report_export_type == 1) {

				header('Content-Type: text/csv');
				header('Content-Disposition: attachment; filename="reports.csv"');
				$fp = fopen('php://output', 'wb');
				fputcsv($fp, $headers);
			}

			foreach ($Products as $keyP => $valueP) {

				$lineVal = array(
					$valueP->text,
					$valueP->quantity,
				);

				if ($request->report_export_type == 0) {
					$tableData[] = $lineVal;
				} else {
					fputcsv($fp, $lineVal, ",");
				}
			}

			if ($request->report_export_type == 0) {

				$data = array();
				$data['title'] = "Report - Product Inventory ";
				$data['table_header_data'] = $tableHeaderData;
				$data['table_data'] = $tableData;

				return view('product/report', compact('data'));
			} else {
				fclose($fp);
			}
		} else if ($request->report_status == 2) {

			$Order = Order::query();
			$Order->select('orders.id', 'channel_partner.firm_name', 'orders.created_at');
			$Order->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
			$Order->orderBy('orders.id', 'desc');
			$Order->where('channel_partner.reporting_manager_id', 0);
			$Order->where('orders.created_at', '>=', $startDate);
			$Order->where('orders.created_at', '<=', $endDate);
			if (isset($request->report_channel_partner_user_id) && is_array($request->channel_partner_user_id)) {

				$Order->whereIn('orders.channel_partner_user_id', $request->report_channel_partner_user_id);
			}

			$Order->whereIn('orders.status', [0, 1, 2, 3]);
			$Order = $Order->get();

			$orderIds = array(0);

			foreach ($Order as $key => $value) {

				$orderIds[] = $value->id;
			}

			$orderItemsPendingQTY = array();

			$OrderItem = OrderItem::query();
			$OrderItem->select('order_items.pending_qty', 'order_items.order_id', 'order_items.product_inventory_id', 'order_items.order_id', 'order_items.qty');
			$OrderItem->orderBy('order_items.id', 'desc');
			$OrderItem->whereIn('order_items.order_id', $orderIds);

			if (isset($request->product_inventory_id) && is_array($request->product_inventory_id)) {

				$OrderItem->whereIn('order_items.product_inventory_id', $request->product_inventory_id);
			}
			if ($request->export_type != 0) {
				$OrderItem->where('order_items.pending_qty', '>', 0);
			}
			$OrderItems = $OrderItem->get();

			$productIds = array(0);

			$orderIds = array(0);

			foreach ($OrderItems as $key => $value) {
				$productIds[] = $value->product_inventory_id;

				if ($request->export_type == 0) {
					$orderItemsPendingQTY[$value->order_id . "_" . $value->product_inventory_id] = $value->qty;
				} else {
					$orderItemsPendingQTY[$value->order_id . "_" . $value->product_inventory_id] = $value->pending_qty;
				}

				$orderIds[] = $value->order_id;
			}

			if (isset($request->product_inventory_id) && is_array($request->product_inventory_id)) {

				$Order = Order::query();
				$Order->select('orders.id', 'channel_partner.firm_name', 'orders.created_at');
				$Order->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
				$Order->orderBy('orders.id', 'desc');
				$Order->whereIn('orders.id', $orderIds);
				$Order = $Order->get();
			}

			$productIds = array_unique($productIds);
			$productIds = array_values($productIds);

			$Products = ProductInventory::select('product_inventory.id', DB::raw('CONCAT(product_brand.name," ",product_code.name )  as text'));
			$Products->leftJoin('data_master as product_brand', 'product_brand.id', '=', 'product_inventory.product_brand_id');
			$Products->leftJoin('data_master as product_code', 'product_code.id', '=', 'product_inventory.product_code_id');
			$Products->whereIn('product_inventory.id', $productIds);
			$Products = $Products->get();

			$productIdText = array("");

			foreach ($productIds as $key => $value) {

				if ($key != 0) {

					foreach ($Products as $keyP => $valueP) {

						if ($value == $valueP->id) {
							$productIdText[] = $valueP->text;
							break;
						}
					}
				}
			}

			$headers = array("Channel Partner/Products", "#orderId", "orderDate");
			foreach ($productIdText as $key => $value) {
				if ($key != 0) {
					$headers[] = $value;
				}
			}

			$tableData = array();
			$tableHeaderData = array();

			if ($request->report_export_type == 0) {
				$tableHeaderData[] = $headers;
			} else if ($request->report_export_type == 1) {

				header('Content-Type: text/csv');
				header('Content-Disposition: attachment; filename="reports.csv"');
				$fp = fopen('php://output', 'wb');
				fputcsv($fp, $headers);
			}

			foreach ($Order as $key => $value) {

				$created_at = convertOrderDateTime($value->created_at, "date");

				$lineVal = array(
					$value->firm_name,
					$value->id,
					$created_at,

				);

				foreach ($productIds as $keyP => $valeP) {

					if ($keyP != 0) {

						if (isset($orderItemsPendingQTY[$value->id . "_" . $valeP])) {

							$lineVal[] = $orderItemsPendingQTY[$value->id . "_" . $valeP];
						} else {
							$lineVal[] = "";
						}
					}
				}

				if ($request->report_export_type == 0) {
					$tableData[] = $lineVal;
				} else {
					fputcsv($fp, $lineVal, ",");
				}
			}

			if ($request->report_export_type == 0) {

				$data = array();
				$data['title'] = "Report - Product Inventory ";
				$data['table_header_data'] = $tableHeaderData;
				$data['table_data'] = $tableData;

				return view('product/report', compact('data'));
			} else {
				fclose($fp);
			}
		} else if ($request->report_status == 3) {

			$Order = Order::query();
			$Order->select('orders.id', 'channel_partner.firm_name', 'orders.created_at');
			$Order->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
			$Order->orderBy('orders.id', 'desc');
			$Order->where('channel_partner.reporting_manager_id', 0);
			$Order->where('orders.created_at', '>=', $startDate);
			$Order->where('orders.created_at', '<=', $endDate);
			if (isset($request->report_channel_partner_user_id) && is_array($request->channel_partner_user_id)) {

				$Order->whereIn('orders.channel_partner_user_id', $request->report_channel_partner_user_id);
			}

			$Order->whereIn('orders.status', [0, 1, 2, 3]);
			$Order = $Order->get();

			$orderIds = array(0);

			foreach ($Order as $key => $value) {

				$orderIds[] = $value->id;
			}

			$orderItemsPendingQTY = array();

			$OrderItem = OrderItem::query();
			$OrderItem->select('order_items.pending_qty', 'order_items.order_id', 'order_items.product_inventory_id', 'order_items.order_id', 'order_items.qty');
			$OrderItem->orderBy('order_items.id', 'desc');
			$OrderItem->whereIn('order_items.order_id', $orderIds);

			if (isset($request->product_inventory_id) && is_array($request->product_inventory_id)) {

				$OrderItem->whereIn('order_items.product_inventory_id', $request->product_inventory_id);
			}
			if ($request->export_type != 0) {
				$OrderItem->where('order_items.pending_qty', '>', 0);
			}
			$OrderItems = $OrderItem->get();

			$productIds = array(0);

			$orderIds = array(0);

			foreach ($OrderItems as $key => $value) {
				$productIds[] = $value->product_inventory_id;

				if ($request->export_type == 0) {
					$orderItemsPendingQTY[$value->order_id . "_" . $value->product_inventory_id] = $value->qty;
				} else {
					$orderItemsPendingQTY[$value->order_id . "_" . $value->product_inventory_id] = $value->pending_qty;
				}

				$orderIds[] = $value->order_id;
			}

			if (isset($request->product_inventory_id) && is_array($request->product_inventory_id)) {

				$Order = Order::query();
				$Order->select('orders.id', 'channel_partner.firm_name', 'orders.created_at');
				$Order->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'orders.channel_partner_user_id');
				$Order->orderBy('orders.id', 'desc');
				$Order->whereIn('orders.id', $orderIds);
				$Order = $Order->get();
			}

			$productIds = array_unique($productIds);
			$productIds = array_values($productIds);

			$Products = ProductInventory::select('product_inventory.id', DB::raw('CONCAT(product_brand.name," ",product_code.name )  as text'), 'product_inventory.quantity');
			$Products->leftJoin('data_master as product_brand', 'product_brand.id', '=', 'product_inventory.product_brand_id');
			$Products->leftJoin('data_master as product_code', 'product_code.id', '=', 'product_inventory.product_code_id');
			$Products->whereIn('product_inventory.id', $productIds);
			$Products = $Products->get();

			$productIdText = array("");
			$currentStock = array("");

			foreach ($productIds as $key => $value) {

				if ($key != 0) {

					foreach ($Products as $keyP => $valueP) {

						if ($value == $valueP->id) {
							$productIdText[] = $valueP->text;
							$currentStock[] = $valueP->quantity;
							break;
						}
					}
				}
			}

			$headers = array("Channel Partner/Products", "#orderId", "orderDate");
			foreach ($productIdText as $key => $value) {
				if ($key != 0) {
					$headers[] = $value;
				}
			}

			$tableData = array();
			$tableHeaderData = array();

			if ($request->report_export_type == 0) {
				$tableHeaderData[] = $headers;
			} else if ($request->report_export_type == 1) {

				header('Content-Type: text/csv');
				header('Content-Disposition: attachment; filename="reports.csv"');
				$fp = fopen('php://output', 'wb');
				fputcsv($fp, $headers);
			}

			foreach ($Order as $key => $value) {

				$created_at = convertOrderDateTime($value->created_at, "date");

				$lineVal = array(
					$value->firm_name,
					$value->id,
					$created_at,

				);

				foreach ($productIds as $keyP => $valeP) {

					if ($keyP != 0) {

						if (isset($orderItemsPendingQTY[$value->id . "_" . $valeP])) {

							$lineVal[] = $orderItemsPendingQTY[$value->id . "_" . $valeP] - $currentStock[$keyP];
						} else {
							$lineVal[] = "";
						}
					}
				}

				if ($request->report_export_type == 0) {
					$tableData[] = $lineVal;
				} else {
					fputcsv($fp, $lineVal, ",");
				}
			}

			if ($request->report_export_type == 0) {

				$data = array();
				$data['title'] = "Report - Product Inventory ";
				$data['table_header_data'] = $tableHeaderData;
				$data['table_data'] = $tableData;

				return view('product/report', compact('data'));
			} else {
				fclose($fp);
			}
		}
	}
}
