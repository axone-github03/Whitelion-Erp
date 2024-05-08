<?php

namespace App\Http\Controllers;
use App\Models\DataMaster;
use App\Models\MainMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

//use Session;

class MasterDataController extends Controller {

	public function __construct() {

		$this->middleware(function ($request, $next) {

			$tabCanAccessBy = array(0, 1, 6);

			if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
				return redirect()->route('dashboard');

			}

			return $next($request);

		});

	}

	public function index() {
		$data = array();
		$data['title'] = "Data Master ";

		$accessMainMaster = getMainMasterPrivilege(Auth::user()->type);

		$results = MainMaster::where('status', 1)->orderBy('id', 'asc');

		if (count($accessMainMaster) > 0) {
			$results->whereIn('code', $accessMainMaster);
		} else {
			$results->where('code', "");
		}
		$data['defaul_main_master'] = $results->first();

		return view('master/data', compact('data'));

	}

	function ajax(Request $request) {
		//DB::enableQueryLog();

		$accessMainMaster = getMainMasterPrivilege(Auth::user()->type);

		$searchColumns = array(

			0 => 'data_master.id',
			1 => 'data_master.name',
		);

		$columns = array(
			0 => 'data_master.id',
			1 => 'data_master.name',
			2 => 'data_master.code',
			3 => 'data_master.status',

		);

		$recordsTotal = DataMaster::where('main_master_id', $request->main_master_id)->count();
		$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.

		$query = DataMaster::query();
		$query->leftJoin('main_master', 'main_master.id', '=', 'data_master.main_master_id');
		if (count($accessMainMaster) > 0) {
			$query->whereIn('main_master.code', $accessMainMaster);
		} else {
			$query->where('main_master.code', "");
		}
		$query->select($columns);
		$query->where('main_master_id', $request->main_master_id);

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

			$data[$key]['status'] = getDataMasterStatusLable($value['status']);

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
			'data_master_id' => ['required'],
			'data_master_name' => ['required'],
			's_main_master_id' => ['required'],
			'data_master_code' => ['required'],
			'data_master_status' => ['required'],

		]);
		if ($validator->fails()) {

			$response = array();
			$response['status'] = 0;
			$response['msg'] = "The request could not be understood by the server due to malformed syntax";
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();

			return response()->json($response)->header('Content-Type', 'application/json');

		} else {

			$accessMainMaster = getMainMasterPrivilege(Auth::user()->type);

			$MainMaster = MainMaster::find($request->s_main_master_id);
			if (!in_array($MainMaster->code, $accessMainMaster)) {

				$response = errorRes("Invalid privillage");
				return response()->json($response)->header('Content-Type', 'application/json');

			}

			$alreadyName = DataMaster::query();

			if ($request->data_master_id != 0) {

				$alreadyName->where('name', $request->data_master_name);
				$alreadyName->where('id', '!=', $request->data_master_id);
				$alreadyName->where('main_master_id', $request->s_main_master_id);

			} else {
				$alreadyName->where('name', $request->data_master_name);
				$alreadyName->where('main_master_id', $request->s_main_master_id);

			}
			$alreadyName = $alreadyName->first();

			$alreadyCode = DataMaster::query();

			if ($request->data_master_id != 0) {

				$alreadyCode->where('code', $request->data_master_code);
				$alreadyCode->where('id', '!=', $request->data_master_id);
				$alreadyCode->where('main_master_id', $request->s_main_master_id);

			} else {
				$alreadyCode->where('code', $request->data_master_code);
				$alreadyCode->where('main_master_id', $request->s_main_master_id);
			}

			$alreadyCode = $alreadyCode->first();

			if ($alreadyName) {

				$response = errorRes("already name exits, Try with another name");

			} else if ($alreadyCode) {

				$response = errorRes("already code exits, Try with another code");

			} else {

				if ($request->data_master_id != 0) {

					$DataMaster = DataMaster::find($request->data_master_id);

				} else {
					$DataMaster = new DataMaster();

				}

				$DataMaster->name = $request->data_master_name;
				$DataMaster->code = $request->data_master_code;
				$DataMaster->status = $request->data_master_status;
				$DataMaster->main_master_id = $request->s_main_master_id;
				$DataMaster->save();
				if ($DataMaster) {

					if ($request->data_master_id != 0) {

						$response = successRes("Successfully saved data master");

						$debugLog = array();
						$debugLog['name'] = "data-master-edit";
						$debugLog['description'] = "data master #" . $DataMaster->id . "(" . $DataMaster->name . ") has been updated ";
						saveDebugLog($debugLog);

					} else {
						$response = successRes("Successfully added data master");

						$debugLog = array();
						$debugLog['name'] = "main-data-add";
						$debugLog['description'] = "data master #" . $DataMaster->id . "(" . $DataMaster->name . ") has been added ";
						saveDebugLog($debugLog);

					}

				}

			}

			return response()->json($response)->header('Content-Type', 'application/json');

		}

	}

	public function detail(Request $request) {

		$DataMaster = DataMaster::find($request->id);
		if ($DataMaster) {

			$response = successRes("Successfully get main master");
			$response['data'] = $DataMaster;

		} else {
			$response = errorRes("Invalid id");
		}
		return response()->json($response)->header('Content-Type', 'application/json');

	}

	public function searchMainMaster(Request $request) {

		$accessMainMaster = getMainMasterPrivilege(Auth::user()->type);

		$results = array();
		$results = MainMaster::select('id', 'name as text');
		$results->where('name', 'like', "%" . $request->q . "%");
		if (count($accessMainMaster) > 0) {
			$results->whereIn('code', $accessMainMaster);
		} else {
			$results->where('code', "");
		}

		$results->limit(5);
		$results = $results->get();

		$response = array();
		$response['results'] = $results;
		$response['pagination']['more'] = false;
		return response()->json($response)->header('Content-Type', 'application/json');

	}

}