<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\DataMaster;
use App\Models\MainMaster;
use App\Models\MarketingProductInventory;
use App\Models\User;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProductInventoryController extends Controller
{
	//

	public function __construct()
	{

		$this->middleware(function ($request, $next) {

			$tabCanAccessBy = array(0, 1, 6);

			if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
				return redirect()->route('dashboard');
			}

			return $next($request);
		});
	}

	public function index()
	{

		$data = array();
		$isViewMode = 0;

		$isMarketingUser = isMarketingUser();
		if ($isMarketingUser == 1) {
			$isViewMode = 0;
		}

		$data['title'] = "Marketing Product Inventory ";
		$data['viewMode'] = $isViewMode;
		return view('marketing/product/inventory', compact('data'));
	}

	function ajax(Request $request)
	{
		//DB::enableQueryLog();

		$isViewMode = 0;

		$isMarketingUser = isMarketingUser();
		if ($isMarketingUser == 1) {
			$isViewMode = 0;
		}

		$searchColumns = array(
			0 => 'marketing_product_inventory.id',
			1 => 'marketing_product_inventory.description',
			2 => 'marketing_product_inventory.purchase_price',
			3 => 'marketing_product_inventory.sale_price',
			4 => 'marketing_product_inventory.weight',
			5 => 'marketing_product_inventory.quantity',
			6 => 'marketing_product_code.name',
			7 => 'CONCAT(marketing_product_code.name," (",marketing_product_inventory.description,")" )',

		);

		$sortingColumns = array(
			0 => 'marketing_product_inventory.id',
			1 => 'marketing_product_code.name',
			2 => 'marketing_product_inventory.description',
			3 => 'marketing_product_inventory.purchase_price',
			4 => 'marketing_product_inventory.sale_price',
			5 => 'marketing_product_inventory.weight',
			6 => 'marketing_product_inventory.quantity',

		);

		$selectColumns = array(
			0 => 'marketing_product_inventory.id',
			1 => 'marketing_product_code.name as marketing_product_code_name',
			2 => 'marketing_product_inventory.description',
			3 => 'marketing_product_inventory.purchase_price',
			4 => 'marketing_product_inventory.sale_price',
			5 => 'marketing_product_inventory.weight',
			6 => 'marketing_product_inventory.quantity',
			7 => 'marketing_product_inventory.status',

		);

		$recordsTotal = MarketingProductInventory::count();
		$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.
		$query = MarketingProductInventory::query();
		//$query->leftJoin('data_master as marketing_product_group', 'marketing_product_group.id', '=', 'marketing_product_inventory.marketing_product_group_id');
		$query->leftJoin('data_master as marketing_product_code', 'marketing_product_code.id', '=', 'marketing_product_inventory.marketing_product_code_id');
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
						// $query->where($searchColumns[$i], 'like', "%" . $search_value . "%");
						$query->whereRaw($searchColumns[$i] . ' like ? ', ["%" . $search_value . "%"]);
					} else {
						$query->orwhereRaw($searchColumns[$i] . ' like ? ', ["%" . $search_value . "%"]);
						// $query->orWhere($searchColumns[$i], 'like', "%" . $search_value . "%");
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

			//$data[$key]['marketing_product_group'] = "<p>" . $value['marketing_product_group_name'] . "</p>";
			$data[$key]['id'] = highlightString($data[$key]['id'],$search_value);
			$data[$key]['marketing_product_code'] = "<p>" . highlightString($value['marketing_product_code_name'],$search_value) . "</p>";
			if ($value['status'] == 0) {

				$data[$key]['description'] = '<span class="badge badge-pill badge-soft-danger font-size-11">' . $data[$key]['description'] . '</span>';
			} else if ($value['status'] == 1) {
				$data[$key]['description'] = '<span class="badge badge-pill badge-soft-success font-size-11">' . $data[$key]['description'] . '</span>';
			}

			$data[$key]['purchase_price'] = '<i class="fas fa-rupee-sign"></i>' . highlightString(priceLable($value['purchase_price']),$search_value);
			$data[$key]['sale_price'] = '<i class="fas fa-rupee-sign"></i>' . highlightString(priceLable($value['sale_price']),$search_value);
			$data[$key]['weight'] = highlightString($value['weight'] . " gm",$search_value);

			$uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';

			if ($isViewMode == 1) {

				$uiAction .= '<li class="list-inline-item px-2">';
				$uiAction .= '<a onclick="editView(\'' . $value['id'] . '\',\'all\')" href="javascript: void(0);" title="Edit"><i class="mdi mdi-eye"></i></a>';
				$uiAction .= '</li>';
			} else {

				$uiAction .= '<li class="list-inline-item px-2">';
				$uiAction .= '<a onclick="editView(\'' . $value['id'] . '\',\'plus\')" href="javascript: void(0);" title="Edit"><i class="bx bx-plus-circle"></i></a>';
				$uiAction .= '</li>';

				$uiAction .= '<li class="list-inline-item px-2">';
				$uiAction .= '<a onclick="editView(\'' . $value['id'] . '\',\'minus\')" href="javascript: void(0);" title="Edit"><i class="bx bx-minus-circle"></i></a>';
				$uiAction .= '</li>';

				$uiAction .= '<li class="list-inline-item px-2">';
				$uiAction .= '<a onclick="editView(\'' . $value['id'] . '\',\'all\')" href="javascript: void(0);" title="Edit"><i class="bx bx-edit-alt"></i></a>';
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

	public function searchGroup(Request $request)
	{

		$DataMaster = array();

		$MainMaster = MainMaster::select('id')->where('code', 'MARKETING_PRODUCT_GROUP')->first();
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

		$MainMaster = MainMaster::select('id')->where('code', 'MARKETING_PRODUCT_CODE')->first();
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

	public function save(Request $request)
	{

		$validator = Validator::make($request->all(), [
			'marketing_product_inventory_id' => ['required'],

		]);

		if ($validator->fails()) {

			$response = array();
			$response['status'] = 0;
			$response['msg'] = "The request could not be understood by the server due to malformed syntax";
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();

			return response()->json($response)->header('Content-Type', 'application/json');
		} else {

			if ($request->marketing_product_inventory_id == 0) {

				//

				$hasWarning = isset($request->marketing_product_inventory_has_warning) ? $request->marketing_product_inventory_has_warning : "off";
				$notifyWhenOrder = isset($request->product_inventory_notify_when_order) ? $request->product_inventory_notify_when_order : "off";
				$hasSpecificCode = isset($request->marketing_product_inventory_has_specific_code) ? $request->marketing_product_inventory_has_specific_code : "off";
				$hasSpecialItem = isset($request->marketing_product_inventory_has_special_item) ? $request->marketing_product_inventory_has_special_item : "off";

				$rules = array();
				$rules['marketing_product_inventory_id'] = 'required';
				// $rules['marketing_product_group_id'] = 'required';
				$rules['marketing_product_code_id'] = 'required';
				$rules['marketing_product_inventory_hsn'] = 'required';
				$rules['marketing_product_inventory_description'] = 'required';
				$rules['marketing_product_inventory_quantity'] = 'required';
				$rules['marketing_product_inventory_purchase_price'] = 'required';
				$rules['marketing_product_inventory_sale_price'] = 'required';
				$rules['marketing_product_inventory_weight'] = 'required';
				$rules['marketing_product_inventory_status'] = 'required';
				$rules['marketing_product_inventory_gst_percentage'] = 'required';

				if ($hasWarning == "on") {
					$hasWarning = 1;
					$rules['marketing_product_inventory_warning'] = 'required';
				} else {
					$hasWarning = 0;
				}

				if ($notifyWhenOrder == "on") {
					$notifyWhenOrder = 1;
					$rules['marketing_product_inventory_notify_emails'] = 'required';
				} else {
					$notifyWhenOrder = 0;
				}

				if ($hasSpecificCode == "on") {
					$hasSpecificCode = 1;
				} else {
					$hasSpecificCode = 0;
				}

				if ($hasSpecialItem == "on") {
					$hasSpecialItem = 1;
				} else {
					$hasSpecialItem = 0;
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

					$uploadedFile1 = "/s/marketing-product/default.png";
					$uploadedFile2 = "/s/marketing-product/default.png";

					if ($request->hasFile('marketing_product_inventory_image')) {

						$folderPathImage = '/s/marketing-product';
						$fileObject1 = $request->file('marketing_product_inventory_image');

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

					$warning = isset($request->marketing_product_inventory_warning) ? $request->marketing_product_inventory_warning : '';
					$notify_emails = isset($request->marketing_product_inventory_notify_emails) ? $request->marketing_product_inventory_notify_emails : '';

					$alreadyName = MarketingProductInventory::query();
					//$alreadyName->where('marketing_product_group_id', $request->marketing_product_group_id);
					$alreadyName->where('marketing_product_code_id', $request->marketing_product_code_id);
					$alreadyName = $alreadyName->first();

					if ($alreadyName) {

						$response = errorRes("already product exits, Try with another marketing product code");
					} else {

						$MarketingProductInventory = new MarketingProductInventory();
						//$MarketingProductInventory->marketing_product_group_id = $request->marketing_product_group_id;
						$MarketingProductInventory->marketing_product_code_id = $request->marketing_product_code_id;
						$MarketingProductInventory->description = $request->marketing_product_inventory_description;
						$MarketingProductInventory->hsn = $request->marketing_product_inventory_hsn;
						$MarketingProductInventory->image = $uploadedFile1;
						$MarketingProductInventory->thumb = $uploadedFile2;
						$MarketingProductInventory->quantity = $request->marketing_product_inventory_quantity;
						$MarketingProductInventory->purchase_price = $request->marketing_product_inventory_purchase_price;
						$MarketingProductInventory->sale_price = $request->marketing_product_inventory_sale_price;
						$MarketingProductInventory->weight = $request->marketing_product_inventory_weight;

						$MarketingProductInventory->status = $request->marketing_product_inventory_status;
						$MarketingProductInventory->has_warning = $hasWarning;
						$MarketingProductInventory->has_specific_code = $hasSpecificCode;
						$MarketingProductInventory->is_custome = $hasSpecialItem;
						$MarketingProductInventory->warning = $warning;
						$MarketingProductInventory->notify_when_order = $notifyWhenOrder;
						$MarketingProductInventory->notify_emails = $notify_emails;
						$MarketingProductInventory->gst_percentage = $request->marketing_product_inventory_gst_percentage;

						$MarketingProductInventory->save();
						if ($MarketingProductInventory) {

							$response = successRes("Successfully added marketing product inventory");

							$debugLog = array();
							$debugLog['name'] = "product-new";
							$debugLog['marketing_product_inventory_id'] = $MarketingProductInventory->id;
							$debugLog['request_quantity'] = $MarketingProductInventory->quantity;
							$debugLog['quantity'] = $MarketingProductInventory->quantity;
							$debugLog['description'] = "New Product";
							saveMarketingProductLog($debugLog);
						} else {

							$response = errorRes("Something went to wrong");
						}
					}
				}
			} else {

				$product_inventory_type_process = isset($request->marketing_product_inventory_type_process) ? $request->marketing_product_inventory_type_process : '';

				if ($product_inventory_type_process == "plus") {

					$validator = Validator::make($request->all(), [
						'marketing_product_inventory_id' => ['required'],
						'marketing_product_inventory_purpose' => ['required'],
						'marketing_product_inventory_status' => ['required'],
						'marketing_product_inventory_quantity_plus' => ['required', 'integer', 'max:100000000', 'min:0'],

					]);
				} else if ($product_inventory_type_process == "minus") {

					$validator = Validator::make($request->all(), [
						'marketing_product_inventory_id' => ['required'],
						'marketing_product_inventory_purpose' => ['required'],
						'marketing_product_inventory_status' => ['required'],
						'marketing_product_inventory_quantity_minus' => ['required', 'integer', 'max:100000000', 'min:0'],

					]);
				} else if ($product_inventory_type_process == "all") {

					$hasWarning = isset($request->marketing_product_inventory_has_warning) ? $request->marketing_product_inventory_has_warning : "off";
					$notifyWhenOrder = isset($request->marketing_product_inventory_notify_when_order) ? $request->marketing_product_inventory_notify_when_order : "off";
					$hasSpecificCode = isset($request->marketing_product_inventory_has_specific_code) ? $request->marketing_product_inventory_has_specific_code : "off";
					$hasSpecialItem = isset($request->marketing_product_inventory_has_special_item) ? $request->marketing_product_inventory_has_special_item : "off";
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
					$rules['marketing_product_inventory_id'] = 'required';
					// $rules['marketing_product_group_id'] = 'required';
					$rules['marketing_product_code_id'] = 'required';
					$rules['marketing_product_inventory_description'] = 'required';
					$rules['marketing_product_inventory_hsn'] = 'required';

					//$rules['product_inventory_quantity'] = 'required';
					$rules['marketing_product_inventory_purchase_price'] = 'required';
					$rules['marketing_product_inventory_sale_price'] = 'required';
					$rules['marketing_product_inventory_weight'] = 'required';
					$rules['marketing_product_inventory_status'] = 'required';
					$rules['marketing_product_inventory_gst_percentage'] = 'required';
					if ($hasWarning == "on") {
						$hasWarning = 1;
						$rules['marketing_product_inventory_warning'] = 'required';
					} else {
						$hasWarning = 0;
					}

					if ($notifyWhenOrder == "on") {
						$notifyWhenOrder = 1;
						$rules['marketing_product_inventory_notify_emails'] = 'required';
					} else {
						$notifyWhenOrder = 0;
					}

					if ($hasSpecificCode == "on") {
						$hasSpecificCode = 1;
					} else {
						$hasSpecificCode = 0;
					}

					if ($hasSpecialItem == "on") {
						$hasSpecialItem = 1;
					} else {
						$hasSpecialItem = 0;
					}
					$customMessage = array();

					$validator = Validator::make($request->all(), $rules, $customMessage);
					$warning = isset($request->marketing_product_inventory_warning) ? $request->marketing_product_inventory_warning : '';
					$notify_emails = isset($request->marketing_product_inventory_notify_emails) ? $request->marketing_product_inventory_notify_emails : '';
				}

				if ($validator->fails()) {

					$response = array();
					$response['status'] = 0;
					$response['msg'] = "The request could not be understood by the server due to malformed syntax";
					$response['statuscode'] = 400;
					$response['data'] = $validator->errors();

					return response()->json($response)->header('Content-Type', 'application/json');
				} else {

					$MarketingProductInventory = MarketingProductInventory::find($request->marketing_product_inventory_id);

					if ($MarketingProductInventory && $product_inventory_type_process == "plus") {
						$MarketingProductInventory->status = $request->marketing_product_inventory_status;

						$MarketingProductInventory->quantity = $MarketingProductInventory->quantity + $request->marketing_product_inventory_quantity_plus;
						$MarketingProductInventory->save();

						$response = successRes("Successfully update product inventory quantity");
						$debugLog = array();
						$debugLog['name'] = "product-quantity-plus";
						$debugLog['marketing_product_inventory_id'] = $MarketingProductInventory->id;
						$debugLog['request_quantity'] = $request->marketing_product_inventory_quantity_plus;
						$debugLog['quantity'] = $MarketingProductInventory->quantity;
						$debugLog['description'] = $request->marketing_product_inventory_purpose;

						saveMarketingProductLog($debugLog);
					} else if ($MarketingProductInventory && $product_inventory_type_process == "minus" && ($MarketingProductInventory->quantity - $request->product_inventory_quantity_minus) >= 0) {

						$MarketingProductInventory->status = $request->marketing_product_inventory_status;

						$MarketingProductInventory->quantity = $MarketingProductInventory->quantity - $request->marketing_product_inventory_quantity_minus;
						$MarketingProductInventory->save();
						$response = successRes("Successfully update product inventory quantity");

						$debugLog = array();
						$debugLog['name'] = "product-quantity-minus";
						$debugLog['marketing_product_inventory_id'] = $MarketingProductInventory->id;
						$debugLog['request_quantity'] = $request->marketing_product_inventory_quantity_minus;
						$debugLog['quantity'] = $MarketingProductInventory->quantity;
						$debugLog['description'] = $request->marketing_product_inventory_purpose;
						saveMarketingProductLog($debugLog);
					} else if ($MarketingProductInventory && $product_inventory_type_process == "all") {


						$alreadyName = MarketingProductInventory::query();
						//$alreadyName->where('marketing_product_group_id', $request->marketing_product_group_id);
						$alreadyName->where('marketing_product_code_id', $request->marketing_product_code_id);
						$alreadyName->where('id', '!=', $request->marketing_product_inventory_id);
						$alreadyName = $alreadyName->first();

						if ($alreadyName) {

							$response = errorRes("already product exits, Try with another marketing product code");
						} else {

							$uploadedFile1 = "";

							if ($request->hasFile('marketing_product_inventory_image')) {

								$folderPathImage = '/s/marketing-product';
								$fileObject1 = $request->file('marketing_product_inventory_image');

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
								$MarketingProductInventory->image = $uploadedFile1;
								$MarketingProductInventory->thumb = $uploadedFile2;
							}

							//$MarketingProductInventory->marketing_product_group_id = $request->marketing_product_group_id;
							$MarketingProductInventory->marketing_product_code_id = $request->marketing_product_code_id;

							$MarketingProductInventory->description = $request->marketing_product_inventory_description;
							$MarketingProductInventory->hsn = $request->marketing_product_inventory_hsn;

							//$MarketingProductInventory->quantity = $request->marketing_product_inventory_quantity;
							$MarketingProductInventory->purchase_price = $request->marketing_product_inventory_purchase_price;
							$MarketingProductInventory->sale_price = $request->marketing_product_inventory_sale_price;
							$MarketingProductInventory->weight = $request->marketing_product_inventory_weight;

							$MarketingProductInventory->status = $request->marketing_product_inventory_status;
							$MarketingProductInventory->has_warning = $hasWarning;
							$MarketingProductInventory->has_specific_code = $hasSpecificCode;
							$MarketingProductInventory->warning = $warning;
							$MarketingProductInventory->is_custome = $hasSpecialItem;
							$MarketingProductInventory->notify_when_order = $notifyWhenOrder;
							$MarketingProductInventory->notify_emails = $notify_emails;
							$MarketingProductInventory->gst_percentage = $request->marketing_product_inventory_gst_percentage;
							$MarketingProductInventory->save();

							$response = successRes("Successfully update product inventory quantity");

							$debugLog = array();
							$debugLog['name'] = "product-update";
							$debugLog['marketing_product_inventory_id'] = $MarketingProductInventory->id;
							$debugLog['request_quantity'] = 0;
							$debugLog['quantity'] = $MarketingProductInventory->quantity;
							$debugLog['description'] = "Product Detail Update";
							saveMarketingProductLog($debugLog);
						}
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

		$ProductInventory = MarketingProductInventory::with(array('product_group' => function ($query) {
			$query->select('id', 'name');
		}, 'product_code' => function ($query) {
			$query->select('id', 'name');
		}))->find($request->id);
		if ($ProductInventory) {

			$response = successRes("Successfully get marketing product inventory");
			$response['data'] = $ProductInventory;
		} else {
			$response = errorRes("Invalid id");
		}
		return response()->json($response)->header('Content-Type', 'application/json');
	}
}
