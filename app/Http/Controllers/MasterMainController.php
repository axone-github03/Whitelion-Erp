<?php

namespace App\Http\Controllers;
use App\Models\MainMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

//use Session;

class MasterMainController extends Controller {

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
		$data['title'] = "Main Master ";
		return view('master/main', compact('data'));

	}

	function ajax(Request $request) {
		//DB::enableQueryLog();

		$searchColumns = array(

			0 => 'main_master.id',
			1 => 'main_master.name',
		);

		$columns = array(
			0 => 'main_master.id',
			1 => 'main_master.name',
			2 => 'main_master.code',
			3 => 'main_master.status',

		);

		$recordsTotal = MainMaster::count();
		$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.

		$query = MainMaster::query();
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
		// echo "<pre>";
		// print_r(DB::getQueryLog());
		// die;

		$data = json_decode(json_encode($data), true);

		if ($isFilterApply == 1) {
			$recordsFiltered = count($data);
		}

		foreach ($data as $key => $value) {
			$data[$key]['id'] = highlightString($data[$key]['id'],$search_value);
			$data[$key]['name'] = "<p>" . highlightString($data[$key]['name'],$search_value) . '</p>';
			$data[$key]['code'] = "<p>" . highlightString($data[$key]['code'],$search_value) . '</p>';

			$data[$key]['status'] = getMainMasterStatusLable($value['status']);

			$uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';

			$uiAction .= '<li class="list-inline-item px-2">';
			$uiAction .= '<a onclick="editView(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Edit"><i class="bx bx-edit-alt"></i></a>';
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

	public function save(Request $request) {

		$validator = Validator::make($request->all(), [
			'main_master_id' => ['required'],
			'main_master_name' => ['required'],
			'main_master_code' => ['required'],
			'main_master_status' => ['required'],

		]);
		if ($validator->fails()) {

			$response = array();
			$response['status'] = 0;
			$response['msg'] = "The request could not be understood by the server due to malformed syntax";
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();

			return response()->json($response)->header('Content-Type', 'application/json');

		} else {

			$alreadyName = MainMaster::query();

			if ($request->main_master_id != 0) {

				$alreadyName->where('name', $request->main_master_name);
				$alreadyName->where('id', '!=', $request->main_master_id);

			} else {
				$alreadyName->where('name', $request->main_master_name);

			}

			$alreadyName = $alreadyName->first();

			$alreadyCode = MainMaster::query();

			if ($request->main_master_id != 0) {

				$alreadyCode->where('code', $request->main_master_code);
				$alreadyCode->where('id', '!=', $request->main_master_id);

			} else {
				$alreadyCode->where('code', $request->main_master_code);

			}

			$alreadyCode = $alreadyCode->first();

			if ($alreadyName) {

				$response = errorRes("already name exits, Try with another name");

			} else if ($alreadyCode) {

				$response = errorRes("already code exits, Try with another code");

			} else {

				if ($request->main_master_id != 0) {

					$MainMaster = MainMaster::find($request->main_master_id);

				} else {
					$MainMaster = new MainMaster();

				}

				$MainMaster->name = $request->main_master_name;
				$MainMaster->code = $request->main_master_code;
				$MainMaster->status = $request->main_master_status;
				$MainMaster->save();
				if ($MainMaster) {

					if ($request->main_master_id != 0) {

						$response = successRes("Successfully saved main master");

						$debugLog = array();
						$debugLog['name'] = "main-master-edit";
						$debugLog['description'] = "main master #" . $MainMaster->id . "(" . $MainMaster->name . ") has been updated ";
						saveDebugLog($debugLog);

					} else {
						$response = successRes("Successfully added main master");

						$debugLog = array();
						$debugLog['name'] = "main-master-add";
						$debugLog['description'] = "main master #" . $MainMaster->id . "(" . $MainMaster->name . ") has been added ";
						saveDebugLog($debugLog);

					}

				}

			}

			return response()->json($response)->header('Content-Type', 'application/json');

		}

	}

	public function detail(Request $request) {

		$MainMaster = MainMaster::find($request->id);
		if ($MainMaster) {

			$response = successRes("Successfully get main master");
			$response['data'] = $MainMaster;

		} else {
			$response = errorRes("Invalid id");
		}
		return response()->json($response)->header('Content-Type', 'application/json');

	}

}