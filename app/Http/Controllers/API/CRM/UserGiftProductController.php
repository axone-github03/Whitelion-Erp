<?php

namespace App\Http\Controllers\API\CRM;
use App\Http\Controllers\Controller;
use App\Models\GiftProduct;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserGiftProductController extends Controller {

	public function __construct() {
		$this->middleware(function ($request, $next) {
			$tabCanAccessBy = array(202, 302);
			if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
				$response = errorRes("Invalid access", 401);
				return response()->json($response, $response['status_code'])->header('Content-Type', 'application/json');
			}
			return $next($request);
		});
	}

	public function ajax(Request $request) {

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
			'gift_products.has_cashback',
			'gift_products.cashback',
			'gift_products.description',

		);

		// $isSalePerson = isSalePerson();

		//$query = GiftProduct::query();
		// $query->leftJoin('gift_categories', 'gift_categories.id', '=', 'gift_products.gift_category_id');
		// $query->where('gift_categories.type', $request->type);
		// $recordsTotal = $query->count();
		// $recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.
		$query = GiftProduct::query();
		$query->leftJoin('gift_categories', 'gift_categories.id', '=', 'gift_products.gift_category_id');
		$query->where('gift_categories.type', Auth::user()->type);
		if (isset($request->gift_category_id) && $request->gift_category_id != 0) {
			$query->where('gift_products.gift_category_id', $request->gift_category_id);
		}
		$query->where('gift_products.status', 1);
		$query->select($selectColumns);
		// $query->limit($request->length);
		// $query->offset($request->start);
		//$query->orderBy($sortingColumns[$request['order'][0]['column']], $request['order'][0]['dir']);
		$query->orderBy('gift_products.point_value', 'desc');

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

		$data = $query->paginate();
		foreach ($data as $key => $value) {

			$data[$key]['image'] = getSpaceFilePath($value['image']);

			$imageFull = array();
			$image2 = explode(",", $value['image2']);
			foreach ($image2 as $key2 => $value2) {
				if ($value2 != "") {
					$imageFull[] = getSpaceFilePath($value2);
				}

			}

			$data[$key]['image2'] = $imageFull;

		}
		$data = json_decode(json_encode($data), true);

		$response = successRes("Gift Products");
		$response['data'] = $data;
		return response()->json($response, $response['status_code'])->header('Content-Type', 'application/json');

	}

}