<?php

namespace App\Http\Controllers;

use App\Models\GiftCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CRMGiftCategoryController extends Controller {

	public function __construct() {
		$this->middleware(function ($request, $next) {
			$tabCanAccessBy = array(0, 1);
			if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
				return redirect()->route('dashboard');
			}
			return $next($request);
		});
	}

	public function index(Request $request) {

		$giftCategoryType = CRMUserType();
		$data = array();
		$data['title'] = "Gift Category";
		$data['gift_category_type'] = $giftCategoryType;
		return view('crm/gift_category', compact('data'));
	}

	public function ajax(Request $request) {

		$searchColumns = array(
			0 => 'gift_categories.id',
			1 => 'gift_categories.name',

		);

		$sortingColumns = array(
			0 => 'gift_categories.id',
			1 => 'gift_categories.name',
			2 => 'gift_categories.type',
			3 => 'gift_categories.status',

		);

		$selectColumns = array(
			0 => 'gift_categories.id',
			1 => 'gift_categories.name',
			2 => 'gift_categories.type',
			3 => 'gift_categories.status',

		);

		$isSalePerson = (Auth::user()->type == 2) ? 1 : 0;

		$query = GiftCategory::query();
		$recordsTotal = $query->count();
		$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.
		$query = GiftCategory::query();
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

			$viewData[$key]['name'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">' . highlightString($value['name'],$search_value) . '</a></h5>';

			$viewData[$key]['status'] = getGiftCategoryStatusLable($value['status']);
			$viewData[$key]['type'] = highlightString($giftCategoryType[$value['type']]['another_name'],$search_value);

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

	public function save(Request $request) {

		$rules = array();
		$rules['gift_category_id'] = 'required';
		$rules['gift_category_name'] = 'required';
		$rules['gift_category_status'] = 'required';
		$rules['gift_category_type'] = 'required';

		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			$response = array();
			$response['status'] = 0;
			$response['msg'] = "The request could not be understood by the server due to malformed syntax";
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();

			return redirect()->back()->with("error", "Something went wrong with validation");

		} else {

			$alreadyName = GiftCategory::query();
			if ($request->gift_category_id != 0) {
				$alreadyName->where('name', $request->gift_category_name);
				$alreadyName->where('id', '!=', $request->gift_category_id);
			} else {
				$alreadyName->where('name', $request->gift_category_name);
			}
			$alreadyName->where('type', $request->gift_category_type);
			$alreadyName = $alreadyName->first();

			if ($alreadyName) {

				$response = errorRes("already name exits, Try with another name");

			} else {

				if ($request->gift_category_id == 0) {

					$GiftCategory = new GiftCategory();

				} else {

					$GiftCategory = GiftCategory::find($request->gift_category_id);

				}
				$GiftCategory->name = $request->gift_category_name;
				$GiftCategory->status = $request->gift_category_status;
				$GiftCategory->type = $request->gift_category_type;
				$GiftCategory->save();

				if ($GiftCategory) {

					if ($request->gift_category_id != 0) {

						$response = successRes("Successfully saved gift category");

						$debugLog = array();
						$debugLog['name'] = "gift-category-edit";
						$debugLog['description'] = "gift category #" . $GiftCategory->id . "(" . $GiftCategory->name . ") has been updated ";
						saveDebugLog($debugLog);

					} else {
						$response = successRes("Successfully added gift category");
						$debugLog = array();
						$debugLog['name'] = "gift-category-add";
						$debugLog['description'] = "gift category #" . $GiftCategory->id . "(" . $GiftCategory->name . ") has been added ";
						saveDebugLog($debugLog);

					}

				}
			}

		}
		return response()->json($response)->header('Content-Type', 'application/json');

	}
	public function detail(Request $request) {

		$GiftCategory = GiftCategory::find($request->id);
		if ($GiftCategory) {

			$response = successRes("Successfully get main master");
			$response['data'] = $GiftCategory;

		} else {
			$response = errorRes("Invalid id");
		}
		return response()->json($response)->header('Content-Type', 'application/json');

	}
}