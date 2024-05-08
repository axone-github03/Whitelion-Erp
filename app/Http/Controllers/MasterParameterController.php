<?php

namespace App\Http\Controllers;
use App\Models\Parameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

//use Session;

class MasterParameterController extends Controller {

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
		$data['title'] = "Parameter";
		return view('master/parameter', compact('data'));

	}
	function ajax(Request $request) {

		$searchColumns = array(
			0 => 'parameter.id',
			1 => 'parameter.name',
			2 => 'parameter.description',

		);

		$sortingColumns = array(
			0 => 'parameter.id',
			1 => 'parameter.description',
			2 => 'parameter.name_value',

		);

		$selectColumns = array(
			'parameter.id',
			'parameter.name',
			'parameter.description',
			'parameter.name_value',

		);

		$query = Parameter::query();
		$recordsTotal = $query->count();
		$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.
		$query = Parameter::query();
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

		$viewData = array();

		foreach ($data as $key => $value) {

			$viewData[$key] = array();

			$viewData[$key]['name'] = '<h5 class="font-size-14 mb-1">' . highlightString($value['name'],$search_value) . '</h5>';

			$viewData[$key]['description'] = "<p>" . highlightString($value['description'],$search_value) . "</p>";
			$viewData[$key]['name_value'] = "<p>" . highlightString($value['name_value'],$search_value) . "</p>";

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

	public function detail(Request $request) {
		$Parameter = Parameter::find($request->id);
		if ($Parameter) {

			$response = successRes("Successfully get parameter");
			$response['data'] = $Parameter;

		} else {
			$response = errorRes("Invalid id");
		}
		return response()->json($response)->header('Content-Type', 'application/json');

	}

	public function save(Request $request) {

		$validator = Validator::make($request->all(), [
			'parameter_id' => ['required'],
			'parameter_description' => ['required'],
			'parameter_name_value' => ['required'],

		]);
		if ($validator->fails()) {

			$response = array();
			$response['status'] = 0;
			$response['msg'] = "The request could not be understood by the server due to malformed syntax";
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();

		} else {
			$Parameter = Parameter::find($request->parameter_id);
			if ($Parameter) {
				$Parameter->name_value = $request->parameter_name_value;
				$Parameter->description = $request->parameter_description;
				$Parameter->save();
				$response = successRes("Successfully saved parameter");
				$debugLog = array();
				$debugLog['name'] = "parameter-edit";
				$debugLog['description'] = "parameter #" . $Parameter->id . "(" . $Parameter->name . ") has been updated to " . $Parameter->name_value;
				saveDebugLog($debugLog);

			} else {
				$response = errorRes("Invalid id");
			}

		}

		return response()->json($response)->header('Content-Type', 'application/json');

	}
}