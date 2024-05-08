<?php

namespace App\Http\Controllers;

//use Illuminate\Support\Facades\Hash;
use App\Models\ProductInventory;
use App\Models\ProductLog;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductLogController extends Controller {

	public function __construct() {

		$this->middleware(function ($request, $next) {

			$tabCanAccessBy = array(0, 1);

			if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
				return redirect()->route('dashboard');

			}

			return $next($request);

		});

	}

	public function index() {

		$data = array();
		$data['title'] = "Product Log";
		return view('product/log', compact('data'));

	}

	function ajax(Request $request) {

		$searchColumns = array(
			0 => 'product_log.id',
			1 => 'product_log.description',
		);

		$columns = array(
			// datatable column index  => database column name
			0 => 'product_log.id',
			1 => 'product_log.created_at',
			2 => 'product_log.user_id',
			3 => 'product_log.description',
			4 => 'product_log.request_quantity',
			5 => 'product_log.quantity',
			6 => 'product_log.name',
			7 => 'users.id as user_id',
			8 => 'users.first_name',
			9 => 'users.last_name',
			10 => 'product_inventory.description  as product_description',
			11 => 'product_brand.name  as product_brand_name',
			12 => 'product_code.name  as product_code_name',

		);

		$recordsTotal = ProductLog::query();

		if ($request->product_inventory_id != 0) {
			$recordsTotal->where('product_log.product_inventory_id', $request->product_inventory_id);
		}

		$recordsTotal = $recordsTotal->count();

		$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.

		$query = ProductLog::query();
		$query->leftJoin('users', 'product_log.user_id', '=', 'users.id');
		$query->leftJoin('product_inventory', 'product_log.product_inventory_id', '=', 'product_inventory.id');
		$query->leftJoin('data_master as product_brand', 'product_brand.id', '=', 'product_inventory.product_brand_id');
		$query->leftJoin('data_master as product_code', 'product_code.id', '=', 'product_inventory.product_code_id');

		if ($request->product_inventory_id != 0) {
			$query->where('product_log.product_inventory_id', $request->product_inventory_id);
		}
		$query->select($columns);
		$query->limit($request->length);
		$query->offset($request->start);
		$query->orderBy($columns[$request['order'][0]['column']], $request['order'][0]['dir']);
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
			$data[$key]['id'] = highlightString($data[$key]['id'],$search_value);
			$data[$key]['product'] = highlightString($value['product_code_name'],$search_value);
			// $data[$key]['product'] = $value['marketing_product_group_name'] . " " . $value['marketing_product_code_name'];

			if ($value['name'] == "product-new") {
				$data[$key]['request_quantity'] = '<i class="bx bx-plus"></i>' . $data[$key]['request_quantity'];
			} else if ($value['name'] == "product-quantity-plus") {
				$data[$key]['request_quantity'] = '<i class="bx bx-plus"></i>' . $data[$key]['request_quantity'];

			} else if ($value['name'] == "product-quantity-minus") {
				$data[$key]['request_quantity'] = '<i class="bx bx-minus"></i>' . $data[$key]['request_quantity'];

			}
			$data[$key]['process_by'] = '#' . highlightString($value['user_id'] . ' ' . $value['first_name'] . " " . $value['last_name'] . '',$search_value);

			$data[$key]['description'] = '' . highlightString($data[$key]['description'],$search_value) . '';
			$data[$key]['created_at'] = highlightString(convertDateTime($value['created_at']),$search_value);
		}

		$jsonData = array(
			"draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
			"recordsTotal" => intval($recordsTotal), // total number of records
			"recordsFiltered" => intval($recordsFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
			"data" => $data, // total data array

		);
		return $jsonData;

	}

	function searchProduct(Request $request) {

		$DataMaster = array();

		if (strtolower($request->q) != "all") {

			$DataMaster = ProductInventory::select('product_inventory.id', DB::raw('CONCAT(product_brand.name," ",product_code.name," (",product_inventory.description,")" )  as text'));
			$DataMaster->leftJoin('data_master as product_brand', 'product_brand.id', '=', 'product_inventory.product_brand_id');
			$DataMaster->leftJoin('data_master as product_code', 'product_code.id', '=', 'product_inventory.product_code_id');

			//$DataMaster->where('status', 1);

			$searchValue = $request->q;

			$searchValuePieces = explode(" ", $searchValue);

			if (count($searchValuePieces) > 1) {

				$DataMaster->where(function ($query) use ($searchValuePieces) {
					$query->where('product_brand.name', 'like', "%" . $searchValuePieces[0] . "%");
					$query->orWhere('product_code.name', 'like', "%" . $searchValuePieces[1] . "%");
				});

			} else {

				$DataMaster->where(function ($query) use ($searchValue) {
					$query->where('product_brand.name', 'like', "%" . $searchValue . "%");
					$query->orWhere('product_code.name', 'like', "%" . $searchValue . "%");
					$query->orWhere('product_inventory.description', 'like', "%" . $searchValue . "%");
				});

			}

			$DataMaster->limit(5);
			$DataMaster = $DataMaster->get();

		} else {

			$DataMaster[0]['id'] = 0;
			$DataMaster[0]['text'] = "All";

		}

		$response = array();
		$response['results'] = $DataMaster;
		$response['pagination']['more'] = false;
		return response()->json($response)->header('Content-Type', 'application/json');

	}
}