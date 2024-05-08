<?php

namespace App\Http\Controllers;
use App\Models\ProductInventory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

//use Session;

class ProductInventoryStockController extends Controller {

	public function __construct() {

		$this->middleware(function ($request, $next) {

			$tabCanAccessBy = array(4);

			if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
				return redirect()->route('dashboard');

			}

			return $next($request);

		});

	}

	public function index() {
		$data = array();
		$data['title'] = "Product Inventory ";
		return view('product/inventory_stock', compact('data'));

	}

	function ajax(Request $request) {
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

			$data[$key]['product_brand'] = "<p>" . $value['product_brand_name'] . "</p>";
			$data[$key]['product_code'] = "<p>" . $value['product_code_name'] . "</p>";
			if ($value['status'] == 0) {

				$data[$key]['description'] = '<span class="badge badge-pill badge-soft-danger font-size-11">' . $data[$key]['description'] . '</span>';

			} else if ($value['status'] == 1) {
				$data[$key]['description'] = '<span class="badge badge-pill badge-soft-success font-size-11">' . $data[$key]['description'] . '</span>';

			}

			$data[$key]['price'] = '<i class="fas fa-rupee-sign"></i>' . priceLable($value['price']);
			$data[$key]['weight'] = $value['weight'] . " gm";

			$uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';

			// $uiAction .= '<li class="list-inline-item px-2">';
			// $uiAction .= '<a onclick="editDiscount(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Discount"><i class="bx bxs-discount"></i></a>';
			// $uiAction .= '</li>';

			$uiAction .= '<li class="list-inline-item px-2">';
			$uiAction .= '<a onclick="editView(\'' . $value['id'] . '\',\'plus\')" href="javascript: void(0);" title="Edit"><i class="bx bx-plus-circle"></i></a>';
			$uiAction .= '</li>';

			$uiAction .= '<li class="list-inline-item px-2">';
			$uiAction .= '<a onclick="editView(\'' . $value['id'] . '\',\'minus\')" href="javascript: void(0);" title="Edit"><i class="bx bx-minus-circle"></i></a>';
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

	public function detail(Request $request) {

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

	public function save(Request $request) {

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

			} else {

				$response = errorRes("You can't set minus quantity");

			}

		}
		return response()->json($response)->header('Content-Type', 'application/json');

	}
}