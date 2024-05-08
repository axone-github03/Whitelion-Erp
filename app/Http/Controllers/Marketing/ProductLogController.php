<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\MarketingProductInventory;
use App\Models\MarketingProductLog;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductLogController extends Controller {
	//
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
		return view('marketing/product/log', compact('data'));

	}

	function ajax(Request $request) {

		$searchColumns = array(
			0 => 'marketing_product_log.id',
			1 => 'marketing_product_log.description',
		);

		$columns = array(
			// datatable column index  => database column name
			0 => 'marketing_product_log.id',
			1 => 'marketing_product_log.created_at',
			2 => 'marketing_product_log.user_id',
			3 => 'marketing_product_log.description',
			4 => 'marketing_product_log.request_quantity',
			5 => 'marketing_product_log.quantity',
			6 => 'marketing_product_log.name',
			7 => 'users.id as user_id',
			8 => 'users.first_name',
			9 => 'users.last_name',
			10 => 'marketing_product_inventory.description  as product_description',
			// 11 => 'marketing_product_group.name  as marketing_product_group_name',
			12 => 'marketing_product_code.name  as marketing_product_code_name',

		);

		$recordsTotal = MarketingProductLog::query();

		if ($request->product_inventory_id != 0) {
			$recordsTotal->where('marketing_product_log.marketing_product_inventory_id', $request->product_inventory_id);
		}

		$recordsTotal = $recordsTotal->count();

		$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.

		$query = MarketingProductLog::query();
		$query->leftJoin('users', 'marketing_product_log.user_id', '=', 'users.id');
		$query->leftJoin('marketing_product_inventory', 'marketing_product_log.marketing_product_inventory_id', '=', 'marketing_product_inventory.id');
		// $query->leftJoin('data_master as marketing_product_group', 'marketing_product_group.id', '=', 'marketing_product_inventory.marketing_product_group_id');
		$query->leftJoin('data_master as marketing_product_code', 'marketing_product_code.id', '=', 'marketing_product_inventory.marketing_product_code_id');

		if ($request->product_inventory_id != 0) {
			$query->where('marketing_product_log.marketing_product_inventory_id', $request->product_inventory_id);
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
			$data[$key]['product'] = highlightString($value['marketing_product_code_name'],$search_value);
			// $data[$key]['product'] = $value['marketing_product_group_name'] . " " . $value['marketing_product_code_name'];

			if ($value['name'] == "product-new") {
				$data[$key]['request_quantity'] = '<i class="bx bx-plus"></i>' . highlightString($data[$key]['request_quantity'],$search_value);
			} else if ($value['name'] == "product-quantity-plus") {
				$data[$key]['request_quantity'] = '<i class="bx bx-plus"></i>' . highlightString($data[$key]['request_quantity'],$search_value);

			} else if ($value['name'] == "product-quantity-minus") {
				$data[$key]['request_quantity'] = '<i class="bx bx-minus"></i>' . highlightString($data[$key]['request_quantity'],$search_value);

			}
			$data[$key]['process_by'] = '#' . highlightString($value['user_id'] . ' ' . $value['first_name'] . " " . $value['last_name'] . '',$search_value);

			$data[$key]['description'] = '' . highlightString($data[$key]['description'] . '',$search_value);
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

			$DataMaster = MarketingProductInventory::select('marketing_product_inventory.id', DB::raw('CONCAT(marketing_product_group.name," ",marketing_product_code.name," (",marketing_product_inventory.description,")" )  as text'));
			$DataMaster->leftJoin('data_master as marketing_product_group', 'marketing_product_group.id', '=', 'marketing_product_inventory.marketing_product_group_id');
			$DataMaster->leftJoin('data_master as marketing_product_code', 'marketing_product_code.id', '=', 'marketing_product_inventory.marketing_product_code_id');

			//$DataMaster->where('status', 1);

			$searchValue = $request->q;

			$searchValuePieces = explode(" ", $searchValue);

			if (count($searchValuePieces) > 1) {

				$DataMaster->where(function ($query) use ($searchValuePieces) {
					$query->where('marketing_product_group.name', 'like', "%" . $searchValuePieces[0] . "%");
					$query->orWhere('marketing_product_group.name', 'like', "%" . $searchValuePieces[1] . "%");
				});

			} else {

				$DataMaster->where(function ($query) use ($searchValue) {
					$query->where('marketing_product_code.name', 'like', "%" . $searchValue . "%");
					$query->orWhere('marketing_product_code.name', 'like', "%" . $searchValue . "%");
					$query->orWhere('marketing_product_inventory.description', 'like', "%" . $searchValue . "%");
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
