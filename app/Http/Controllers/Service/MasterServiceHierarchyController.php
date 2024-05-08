<?php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use App\Models\ServiceHierarchy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MasterServiceHierarchyController extends Controller
{


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
		$data['title'] = "Service Hierarchy";
		return view('service/master/servicehierarchy/main', compact('data'));

	}

	function ajax(Request $request) {
		//DB::enableQueryLog();

		$searchColumns = array(

			0 => 'service_hierarchies.id',
			1 => 'service_hierarchies.name',
		);

		$columns = array(
			0 => 'service_hierarchies.id',
			1 => 'service_hierarchies.name',
			2 => 'service_hierarchies.code',
			3 => 'service_hierarchies.parent_id',
			4 => 'service_hierarchies.status',

		);

		$recordsTotal = ServiceHierarchy::count();
		$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.

		$query = ServiceHierarchy::query();
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
			$data[$key]['name'] = "<p>" .  highlightString($data[$key]['name'],$search_value) . '</p>';
			$data[$key]['code'] = "<p>" .  highlightString($data[$key]['code'],$search_value) . '</p>';

			$data[$key]['status'] = getServiceHierarchyStatusLable($value['status']);
			if ($value['parent_id'] != 0) {

				$parent = ServiceHierarchy::find($value['parent_id']);
				if ($parent) {

					$data[$key]['parent'] = "<p>" . highlightString($parent->name,$search_value) . '</p>';

				} else {

					$data[$key]['parent'] = "";

				}

			} else {

				$data[$key]['parent'] = "";

			}

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

		$results = ServiceHierarchy::select('id', 'name as text');
		$results->where('id', '!=', $request->id);
		$results->where('name', 'like', "%" . $request->q . "%");

		$results->limit(5);
		$results = $results->get();

		$response = array();
		$response['results'] = $results;
		$response['pagination']['more'] = false;
		return response()->json($response)->header('Content-Type', 'application/json');

	}

	public function saveProcess(Request $request) {

		$validator = Validator::make($request->all(), [
			'service_hierarchy_id' => ['required'],
			'service_hierarchy_name' => ['required'],
			'service_hierarchy_code' => ['required'],
			'service_hierarchy_status' => ['required'],

		]);
		if ($validator->fails()) {

			$response = array();
			$response['status'] = 0;
			$response['msg'] = "The request could not be understood by the server due to malformed syntax";
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();

			return response()->json($response)->header('Content-Type', 'application/json');

		} else {

			$alreadyName = ServiceHierarchy::query();

			if ($request->service_hierarchy_id != 0) {

				$alreadyName->where('name', $request->service_hierarchy_name);
				$alreadyName->where('id', '!=', $request->service_hierarchy_id);

			} else {
				$alreadyName->where('name', $request->service_hierarchy_name);

			}

			$alreadyName = $alreadyName->first();

			$alreadyCode = ServiceHierarchy::query();

			if ($request->service_hierarchy_id != 0) {

				$alreadyCode->where('code', $request->service_hierarchy_code);
				$alreadyCode->where('id', '!=', $request->service_hierarchy_id);

			} else {
				$alreadyCode->where('code', $request->service_hierarchy_code);

			}

			$alreadyCode = $alreadyCode->first();

			if ($alreadyName) {

				$response = errorRes("already name exits, Try with another name");

			} else if ($alreadyCode) {

				$response = errorRes("already code exits, Try with another code");

			} else {

				$service_hierarchy_parent_id = isset($request->service_hierarchy_parent_id) ? $request->service_hierarchy_parent_id : 0;
				if ($service_hierarchy_parent_id != 0) {

					$Parent = ServiceHierarchy::where('id', $service_hierarchy_parent_id)->first();
					if ($Parent) {
						$service_hierarchy_parent_id = $Parent->id;
					} else {
						$service_hierarchy_parent_id = 0;
					}

				}

				if ($request->service_hierarchy_id != 0) {

					$ServiceHierarchy = ServiceHierarchy::find($request->service_hierarchy_id);

				} else {
					$ServiceHierarchy = new ServiceHierarchy();

				}

				$ServiceHierarchy->name = $request->service_hierarchy_name;
				$ServiceHierarchy->code = $request->service_hierarchy_code;
				$ServiceHierarchy->status = $request->service_hierarchy_status;
				$ServiceHierarchy->parent_id = $service_hierarchy_parent_id;
				$ServiceHierarchy->save();
				if ($ServiceHierarchy) {

					if ($request->service_hierarchy_id != 0) {

						$response = successRes("Successfully saved service hierarchy");

						$debugLog = array();
						$debugLog['name'] = "service-hierarchy-edit";
						$debugLog['description'] = "service hierarchy #" . $ServiceHierarchy->id . "(" . $ServiceHierarchy->name . ") has been updated ";
						saveDebugLog($debugLog);

					} else {
						$response = successRes("Successfully added service hierarchy");

						$debugLog = array();
						$debugLog['name'] = "service-hierarchy-add";
						$debugLog['description'] = "service hierarchy #" . $ServiceHierarchy->id . "(" . $ServiceHierarchy->name . ") has been added ";
						saveDebugLog($debugLog);

					}

				}

			}

			return response()->json($response)->header('Content-Type', 'application/json');

		}

	}

	public function detail(Request $request) {

		$ServiceHierarchy = ServiceHierarchy::find($request->id);
		if ($ServiceHierarchy) {

			$parent = array();
			$is_parent = 0;

			if ($ServiceHierarchy->parent_id != 0) {

				$parent = ServiceHierarchy::find($ServiceHierarchy->parent_id);
				if ($parent) {

					$is_parent = 1;

				}

			}

			$response = successRes("Successfully get service hierarchy");
			$response['data'] = $ServiceHierarchy;
			$response['parent'] = $parent;
			$response['is_parent'] = $is_parent;

		} else {
			$response = errorRes("Invalid id");
		}
		return response()->json($response)->header('Content-Type', 'application/json');

	}

	public function delete(Request $request) {

		$ServiceHierarchy = ServiceHierarchy::find($request->id);
		if ($ServiceHierarchy) {

			$debugLog = array();
			$debugLog['name'] = "service-hierarchy-delete";
			$debugLog['description'] = "service hierarchy #" . $ServiceHierarchy->id . "(" . $ServiceHierarchy->name . ") has been deleted";
			saveDebugLog($debugLog);

			$ServiceHierarchy->delete();

		}
		$response = successRes("Successfully delete service hierarchy");
		return response()->json($response)->header('Content-Type', 'application/json');

	}
}

