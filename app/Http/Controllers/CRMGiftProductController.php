<?php

namespace App\Http\Controllers;

use App\Models\GiftCategory;
use App\Models\GiftProduct;
use App\Models\User;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CRMGiftProductController extends Controller
{

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

	public function index(Request $request)
	{

		$data = array();
		$data['title'] = "Gift Product";
		$data['type'] = isset($request->type) ? $request->type : 202;
		return view('crm/gift_product', compact('data'));
	}

	public function ajax(Request $request)
	{

		$searchColumns = array(
			0 => 'gift_products.id',
			1 => 'gift_products.name',

		);

		$sortingColumns = array(
			0 => 'gift_products.id',
			1 => 'gift_products.name',
			2 => 'gift_products.image',
			3 => 'gift_products.point_value',
			4 => 'gift_products.status',
			5 => 'gift_products.gift_category_id',

		);

		$selectColumns = array(
			'gift_products.id',
			'gift_products.name',
			'gift_products.status',
			'gift_products.point_value',
			'gift_products.image',
			'gift_categories.name as category_name',
			'gift_categories.status as category_status',
			'gift_categories.type as category_type',
			'gift_products.image2',
			'gift_products.price',

		);

		$isSalePerson = isSalePerson();

		$query = GiftProduct::query();
		$query->leftJoin('gift_categories', 'gift_categories.id', '=', 'gift_products.gift_category_id');
		$query->where('gift_categories.type', $request->type);
		$recordsTotal = $query->count();
		$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.
		$query = GiftProduct::query();
		$query->leftJoin('gift_categories', 'gift_categories.id', '=', 'gift_products.gift_category_id');
		$query->where('gift_categories.type', $request->type);
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
		$giftCategoryType = CRMUserType();
		$viewData = array();

		foreach ($data as $key => $value) {

			$viewData[$key] = array();
			$viewData[$key]['id'] = highlightString($value['id'],$search_value);

			$viewData[$key]['name'] = highlightString($value['name'],$search_value);
			$viewData[$key]['price'] = highlightString($value['price'],$search_value);

			$viewData[$key]['category_name'] = highlightString($value['category_name'],$search_value) . " - " .highlightString( $giftCategoryType[$value['category_type']]['another_name'],$search_value);

			$image = '<img class="product-img" src="' . getSpaceFilePath($value['image']) . '" />';

			$image2 = "";

			if ($value['image2'] != "") {

				$image2Pieces = explode(",", $value['image2']);
				foreach ($image2Pieces as $key2 => $value2) {

					if ($value2 != "") {
						$image2 .= '<img class="product-img" src="' . getSpaceFilePath($value2) . '" />';
					}
				}
			}

			$image .= " / " . $image2;

			$viewData[$key]['image'] = $image;
			$viewData[$key]['point_value'] = highlightString($value['point_value'],$search_value);

			$viewData[$key]['status'] = getGiftProductStatusLable($value['status']);

			$uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';
			$uiAction .= '<li class="list-inline-item px-2">';
			$uiAction .= '<a onclick="editView(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Edit"><i class="bx bx-edit-alt"></i></a>';
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

	public function save(Request $request)
	{

		$isCash = isset($request->gift_product_is_cash) ? $request->gift_product_is_cash : "off";
		$hasCashback = isset($request->gift_product_has_cashback) ? $request->gift_product_has_cashback : "off";

		$rules = array();
		// if ($isCash == "on") {
		// 	$isCash = 1;
		// 	$rules['gift_product_cash'] = 'required';
		// } else {
		// 	$isCash = 0;
		// }

		if ($hasCashback == "on") {
			$hasCashback = 1;
			$rules['gift_product_cashback'] = 'required';
		} else {
			$hasCashback = 0;
		}
		$rules['gift_product_id'] = 'required';
		$rules['gift_category_id'] = 'required';
		$rules['gift_product_name'] = 'required';
		$rules['gift_product_point_value'] = 'required';
		$rules['gift_product_price'] = 'required';


		if ($request->gift_category_id == 0) {
			$rules['gift_product_image'] = 'required';
		}
		$rules['gift_product_status'] = 'required';
		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			$response = array();
			$response['status'] = 0;
			$response['msg'] = "The request could not be understood by the server due to malformed syntax";
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();

			return redirect()->back()->with("error", "Something went wrong with validation");
		} else {

			$alreadyName = GiftProduct::query();
			if ($request->gift_category_id != 0) {
				$alreadyName->where('name', $request->gift_product_name);
				$alreadyName->where('id', '!=', $request->gift_product_id);
			} else {
				$alreadyName->where('name', $request->gift_product_name);
			}
			$alreadyName = $alreadyName->first();

			if ($alreadyName) {

				$response = errorRes("already name exits, Try with another name");
			} else {

				//$cash = isset($request->gift_product_cash) ? $request->gift_product_cash : 0;
				$cashback = isset($request->gift_product_cashback) ? $request->gift_product_cashback : 0;

				$uploadedFile1 = "";

				if ($request->hasFile('gift_product_image')) {

					$folderPathImage = '/s/gift-product';
					$fileObject1 = $request->file('gift_product_image');
					$extension = $fileObject1->getClientOriginalExtension();
					$fileName1 = time() . mt_rand(10000, 99999) . '.' . $extension;
					$destinationPath = public_path($folderPathImage);
					$fileObject1->move($destinationPath, $fileName1);

					if (File::exists(public_path($folderPathImage . "/" . $fileName1))) {

						//createThumbs(public_path($folderPathImage . "/" . $fileName1), public_path($folderPathImage . "/thumb-" . $fileName1), 200);

						$uploadedFile1 = $folderPathImage . "/" . $fileName1;

						$spaceUploadResponse = uploadFileOnSpaces(public_path($uploadedFile1), $uploadedFile1);
						if ($spaceUploadResponse != 1) {
							$uploadedFile1 = "";
						} else {
							unlink(public_path($uploadedFile1));
						}
					}
				}

				$uploadedFile2 = array();
				if ($request->hasFile('gift_product_image2')) {

					foreach ($request->file('gift_product_image2') as $fileObject) {

						$folderPathImage = '/s/gift-product';
						$fileObject1 = $fileObject;
						$extension = $fileObject1->getClientOriginalExtension();
						$fileName1 = time() . mt_rand(10000, 99999) . '.' . $extension;
						$destinationPath = public_path($folderPathImage);
						$fileObject1->move($destinationPath, $fileName1);

						if (File::exists(public_path($folderPathImage . "/" . $fileName1))) {

							$tempUploadFile = $folderPathImage . "/" . $fileName1;

							$spaceUploadResponse = uploadFileOnSpaces(public_path($tempUploadFile), $tempUploadFile);
							if ($spaceUploadResponse != 1) {
							} else {
								$uploadedFile2[] = $tempUploadFile;
								unlink(public_path($tempUploadFile));
							}
						}
					}
				}

				if ($request->gift_product_id == 0) {

					if ($uploadedFile1 == "") {
						$response = errorRes("Image not valid");
						return response()->json($response)->header('Content-Type', 'application/json');
					}

					$GiftProduct = new GiftProduct();
					$GiftProduct->image = $uploadedFile1;
				} else {

					$GiftProduct = GiftProduct::find($request->gift_product_id);
					if ($uploadedFile1 != "") {
						$GiftProduct->image = $uploadedFile1;
					}
				}

				if (count($uploadedFile2) > 0) {
					$GiftProduct->image2 = implode(",", $uploadedFile2);
				}
				$GiftProduct->gift_category_id = $request->gift_category_id;
				$GiftProduct->name = $request->gift_product_name;
				$GiftProduct->status = $request->gift_product_status;
				$GiftProduct->point_value = $request->gift_product_point_value;
				$GiftProduct->description = $request->gift_product_description;
				$GiftProduct->price = $request->gift_product_price;

				//$GiftProduct->is_cash = $isCash;
				//$GiftProduct->cash = $cash;
				$GiftProduct->has_cashback = $hasCashback;
				$GiftProduct->cashback = $cashback;

				$GiftProduct->save();

				if ($GiftProduct) {

					if ($request->gift_product_id != 0) {

						$response = successRes("Successfully saved gift product");

						$debugLog = array();
						$debugLog['name'] = "gift-category-edit";
						$debugLog['description'] = "gift category #" . $GiftProduct->id . "(" . $GiftProduct->name . ") has been updated ";
						saveDebugLog($debugLog);
					} else {
						$response = successRes("Successfully added gift product");
						$debugLog = array();
						$debugLog['name'] = "gift-category-add";
						$debugLog['description'] = "gift category #" . $GiftProduct->id . "(" . $GiftProduct->name . ") has been added ";
						saveDebugLog($debugLog);
					}
				}
			}
		}
		return response()->json($response)->header('Content-Type', 'application/json');
	}
	public function detail(Request $request)
	{

		$GiftProduct = GiftProduct::find($request->id);
		if ($GiftProduct) {

			$response = successRes("Successfully get main master");

			$response['data'] = $GiftProduct;
			$response['data']['category'] = GiftCategory::select('id', 'name as text')->find($GiftProduct->gift_category_id);
		} else {
			$response = errorRes("Invalid id");
		}
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function category(Request $request)
	{

		$giftCategoryType = CRMUserType();

		$results = array();
		$results = GiftCategory::select('id', 'name as text', 'type');
		$results->where('id', '!=', $request->id);
		$results->where('name', 'like', "%" . $request->q . "%");
		$results->where('status', 1);
		$results->limit(5);
		$results = $results->get();

		foreach ($results as $key => $value) {
			$results[$key]['text'] = $value['text'] . " - " . $giftCategoryType[$value['type']]['another_name'];
		}

		$response = array();
		$response['results'] = $results;
		$response['pagination']['more'] = false;
		return response()->json($response)->header('Content-Type', 'application/json');
	}
}
