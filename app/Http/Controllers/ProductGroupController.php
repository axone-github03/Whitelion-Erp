<?php

namespace App\Http\Controllers;
use App\Models\DataMaster;
use App\Models\MainMaster;
use App\Models\ProductGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

//use Session;

class ProductGroupController extends Controller {

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
		$data['title'] = "Product Group";
		return view('product/group', compact('data'));

	}

	function ajax(Request $request) {

		$searchColumns = array(
			0 => 'product_group.id',
			1 => 'product_group.name',

		);

		$sortingColumns = array(
			0 => 'product_group.id',
			1 => 'product_group.name',
			2 => 'product_group.product_brand',
			3 => 'product_group.status',

		);

		$selectColumns = array(
			0 => 'product_group.id',
			1 => 'product_group.name',
			2 => 'product_group.product_brand',
			3 => 'product_group.status',
		);

		$recordsTotal = ProductGroup::count();
		$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.
		$query = ProductGroup::query();
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

			$DataMaster = array();
			$DataMaster = DataMaster::select('name');
			$DataMaster->whereIn('id', explode(",", $value['product_brand']));
			// $DataMaster->where('name', 'like', "%" . $request->q . "%");
			$DataMaster = $DataMaster->get();
			$productBrandName = array();
			foreach ($DataMaster as $keyDataMaster => $valueDataMaster) {
				$productBrandName[] = $valueDataMaster->name;
			}

			$data[$key]['id'] = highlightString($value['id'],$search_value);
			$data[$key]['name'] = highlightString($value['name'],$search_value);
			$data[$key]['product_brand'] = "<p>" . highlightString(implode(",", $productBrandName),$search_value) . "</p>";
			$data[$key]['status'] = getProductGroupLable($value['status']);

			$uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';

			$uiAction .= '<li class="list-inline-item px-2">';
			$uiAction .= '<a onclick="editView(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Edit"><i class="bx bx-edit-alt"></i></a>';
			$uiAction .= '</li>';

			$uiAction .= '<li class="list-inline-item px-2">';
			$uiAction .= '<a onclick="deleteWarning(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Delete"><i class="bx bx-trash-alt"></i></a>';
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

	public function search(Request $request) {

		$results = array();

		$results = ProductGroup::select('id', 'name as text');
		$results->where('id', '!=', $request->id);
		$results->where('name', 'like', "%" . $request->q . "%");

		$results->limit(5);
		$results = $results->get();

		$response = array();
		$response['results'] = $results;
		$response['pagination']['more'] = false;
		return response()->json($response)->header('Content-Type', 'application/json');

	}

	public function save(Request $request) {

		$validator = Validator::make($request->all(), [
			'product_group_id' => ['required'],
			'product_group_name' => ['required'],
			'product_brand' => ['required'],
			'product_group_status' => ['required'],

		]);
		if ($validator->fails()) {

			$response = array();
			$response['status'] = 0;
			$response['msg'] = "The request could not be understood by the server due to malformed syntax";
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();

			return response()->json($response)->header('Content-Type', 'application/json');

		} else {

			$alreadyName = ProductGroup::query();

			if ($request->product_group_id != 0) {

				$alreadyName->where('name', $request->product_group_name);
				$alreadyName->where('id', '!=', $request->product_group_id);

			} else {

				$alreadyName->where('name', $request->product_group_name);

			}

			$alreadyName = $alreadyName->first();

			if ($alreadyName) {

				$response = errorRes("already name exits, Try with another name");

			} else {

				if ($request->product_group_id != 0) {

					$ProductGroup = ProductGroup::find($request->product_group_id);

				} else {
					$ProductGroup = new ProductGroup();

				}

				$ProductGroup->name = $request->product_group_name;
				$ProductGroup->product_brand = implode(",", $request->product_brand);
				$ProductGroup->status = $request->product_group_status;
				$ProductGroup->save();

				if ($ProductGroup) {

					if ($request->product_group_id != 0) {

						$response = successRes("Successfully saved product group");

						$debugLog = array();
						$debugLog['name'] = "product-group-edit";
						$debugLog['description'] = "product group #" . $ProductGroup->id . "(" . $ProductGroup->name . ") has been updated ";
						saveDebugLog($debugLog);

					} else {
						$response = successRes("Successfully added product group");

						$debugLog = array();
						$debugLog['name'] = "product-group-add";
						$debugLog['description'] = "product group #" . $ProductGroup->id . "(" . $ProductGroup->name . ") has been added ";
						saveDebugLog($debugLog);

					}

				}

			}

			return response()->json($response)->header('Content-Type', 'application/json');

		}

	}

	public function detail(Request $request) {

		$ProductGroup = ProductGroup::find($request->id);
		if ($ProductGroup) {

			$parent = array();
			$product_brand = array();

			if ($ProductGroup->product_brand != '') {

				$DataMaster = array();
				$DataMaster = DataMaster::select('id', 'name as text');
				$DataMaster->whereIn('id', explode(",", $ProductGroup->product_brand));
				// $DataMaster->where('name', 'like', "%" . $request->q . "%");
				$DataMaster = $DataMaster->get();

				$product_brand = $DataMaster;

			}

			$response = successRes("Successfully get product brand");
			$response['data'] = $ProductGroup;
			$response['product_brand'] = $product_brand;

		} else {
			$response = errorRes("Invalid id");
		}
		return response()->json($response)->header('Content-Type', 'application/json');

	}

	public function searchBrand(Request $request) {

		$DataMaster = array();

		$MainMaster = MainMaster::select('id')->where('code', 'PRODUCT_BRAND')->first();
		if ($MainMaster) {

			$DataMaster = array();
			$DataMaster = DataMaster::select('id', 'name as text');
			$DataMaster->where('main_master_id', $MainMaster->id);
			$DataMaster->where('name', 'like', "%" . $request->q . "%");
			$DataMaster->where('status', 1);
			$DataMaster->limit(5);
			$DataMaster = $DataMaster->get();

		}

		$response = array();
		$response['results'] = $DataMaster;
		$response['pagination']['more'] = false;
		return response()->json($response)->header('Content-Type', 'application/json');

	}

	public function delete(Request $request) {

		$ProductGroup = ProductGroup::find($request->id);
		if ($ProductGroup) {

			$debugLog = array();
			$debugLog['name'] = "product-group-delete";
			$debugLog['description'] = "product group #" . $ProductGroup->id . "(" . $ProductGroup->name . ") has been deleted";
			saveDebugLog($debugLog);

			$ProductGroup->delete();

		}
		$response = successRes("Successfully delete product group");
		return response()->json($response)->header('Content-Type', 'application/json');

	}
}