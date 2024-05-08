<?php

namespace App\Http\Controllers;
use App\Models\PrivilegeUserType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

//use Session;

class MasterRollController extends Controller {

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
		$data = array();
		$data['title'] = "Roll Master ";
		$data['type'] = isset($request->type) ? $request->type : 0;
		$data['type_name'] = getAllUserTypes()[$data['type']]['name'];
		$data['privilege'] = PrivilegeUserType::with('privilege')->where('user_type', $data['type'])->get();

		return view('master/roll', compact('data'));

	}

	function ajax(Request $request) {
		$searchColumns = array(
			0 => 'users.id',
			1 => 'users.first_name',
			2 => 'users.last_name',
			3 => 'users.email',
			4 => 'users.phone_number',

		);

		$sortingColumns = array(
			0 => 'users.id',
			1 => 'users.first_name',
			2 => 'users.last_name',
			3 => 'users.email',
			4 => 'users.phone_number',

		);

		$selectColumns = array(
			'users.id',
			'users.first_name',
			'users.last_name',
			'users.email',
			'users.phone_number',
			'users.type',

		);

		$query = User::query();
		$query->where('type', $request->type);
		$recordsTotal = $query->count();
		$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.
		$query = User::query();
		$query->where('type', $request->type);
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
			$viewData[$key]['id'] = highlightString($value['id'],$search_value);
			$viewData[$key]['first_name'] = highlightString($value['first_name'],$search_value);
			$viewData[$key]['last_name'] = highlightString($value['last_name'],$search_value);
			$viewData[$key]['email'] = highlightString($value['email'],$search_value);
			$viewData[$key]['phone_number'] = highlightString($value['phone_number'],$search_value);
			$viewData[$key]['type'] = highlightString(getUserTypeName($value['type']),$search_value);

			// $viewData[$key]['title'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">' . $value['title'] . '</a></h5>';

			// $viewData[$key]['download'] = '<a class="btn btn-primary waves-effect waves-light" target="_blank" href="' . getSpaceFilePath($value['file_name']) . '" ><i class="bx bx-download font-size-16 align-middle me-2"></i>Download</a>';

			// $viewData[$key]['publish_date_time'] = convertDateTime($value['publish_date_time']);

			// $viewData[$key]['type'] = $CRMUserType[$value['type']]['another_name'];

			// $viewData[$key]['status'] = getUserStatusLable($value['status']);

			$uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';
			$uiAction .= '<li class="list-inline-item px-2">';
			$uiAction .= '<a onclick="editView(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Edit"><i class="bx bx-shield-quarter"></i></a>';
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

		$validator = Validator::make($request->all(), [
			'user_id' => ['required'],

		]);
		if ($validator->fails()) {

			$response = array();
			$response['status'] = 0;
			$response['msg'] = "The request could not be understood by the server due to malformed syntax";
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();

			return response()->json($response)->header('Content-Type', 'application/json');

		} else {

			if ($request->user_id != 0) {

				$User = User::find($request->user_id);

				if ($User) {

					$Privilege = PrivilegeUserType::with('privilege')->where('user_type', $User->type)->get();

					$PrivilegeJSON = array();
					foreach ($Privilege as $key => $value) {

						if (isset($request->all()["privilege_" . $value->privilege->code]) && $request->all()["privilege_" . $value->privilege->code] == "on") {
							$PrivilegeJSON[$value->privilege->code] = 1;
						}

					}
					$User->privilege = json_encode($PrivilegeJSON);
					$User->save();
					$response = successRes("Successfully saved privilege");
					$debugLog = array();
					$debugLog['name'] = "user-privilege";
					$debugLog['description'] = "user privilege #" . $User->id . "(" . $User->name . ") has been updated ";
					saveDebugLog($debugLog);

				}

			} else {

				$Users = User::where('type', $request->user_type)->get();

				foreach ($Users as $key => $value) {

					$User = User::find($value->id);

					if ($User) {

						$Privilege = PrivilegeUserType::with('privilege')->where('user_type', $User->type)->get();

						$PrivilegeJSON = array();
						foreach ($Privilege as $key1 => $value1) {

							if (isset($request->all()["privilege_" . $value1->privilege->code]) && $request->all()["privilege_" . $value1->privilege->code] == "on") {
								$PrivilegeJSON[$value1->privilege->code] = 1;
							}

						}
						$User->privilege = json_encode($PrivilegeJSON);
						$User->save();
						$response = successRes("Successfully saved privilege");
						$debugLog = array();
						$debugLog['name'] = "user-privilege";
						$debugLog['description'] = "user privilege #" . $User->id . "(" . $User->name . ") has been updated ";
						saveDebugLog($debugLog);

					}
				}

			}

			return response()->json($response)->header('Content-Type', 'application/json');

		}

	}

	public function detail(Request $request) {

		$User = User::select('privilege', 'id')->find($request->id);
		if ($User) {

			if ($User->privilege != "") {
				$User->privilege = json_decode($User->privilege, true);
			} else {
				$User->privilege = array();
			}

			$response = successRes("Successfully get main master");
			$response['data'] = $User;

		} else {
			$response = errorRes("Invalid id");
		}
		return response()->json($response)->header('Content-Type', 'application/json');

	}

}