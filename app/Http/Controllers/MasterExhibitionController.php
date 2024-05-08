<?php

namespace App\Http\Controllers;

use App\Models\Exhibition;
use App\Models\ExhibitionSalesPersons;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

//use Session;

class MasterExhibitionController extends Controller
{

	public function __construct()
	{

		$this->middleware(function ($request, $next) {

			$tabCanAccessBy = array(0, 1);

			if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
				return redirect()->route('dashboard');
			}

			return $next($request);
		});
	}

	public function index()
	{
		$data = array();
		$data['title'] = "Exhibition";
		return view('master/exhibition', compact('data'));
	}

	function ajax(Request $request)
	{
		//DB::enableQueryLog();

		$searchColumns = array(

			0 => 'exhibition.id',
			1 => 'exhibition.name',
		);

		$columns = array(
			0 => 'exhibition.id',
			1 => 'exhibition.name',
			2 => 'exhibition.status',

		);

		$recordsTotal = Exhibition::count();
		$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.

		$query = Exhibition::query();
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

			$ExhibitionSalesPersons = ExhibitionSalesPersons::where('exhibition_id', $value['id'])->get();
			$ExhibitionSalesPersons = json_decode(json_encode($ExhibitionSalesPersons), true);
			$ExhibitionSalesPersons = array_column($ExhibitionSalesPersons, 'user_id');

			$data[$key]['sale_persons'] = '<p></p>';

			if (count($ExhibitionSalesPersons) > 0) {
				$data[$key]['id'] = highlightString($data[$key]['id'],$search_value);
				$data[$key]['name'] = "<p>" . highlightString($data[$key]['name'],$search_value) . '</p>';
				$query = DB::table('users');
				$query->select('users.id as id', DB::raw('CONCAT(first_name," ", last_name) AS text'));
				//	$query->leftJoin('users', 'sale_person.user_id', '=', 'users.id');
				//$query->whereIn('sale_person.type', $SalesHierarchyId);
				// $query->where('users.type', 2);
				$query->whereIn('users.id', $ExhibitionSalesPersons);
				$dataSalesPerson = $query->get();
				$dataSalesPerson = json_decode(json_encode($dataSalesPerson), true);
				$dataSalesPerson = array_column($dataSalesPerson, 'text');
				$dataSalesPerson = implode(",", $dataSalesPerson);
				$data[$key]['sale_persons'] = '<p>' . highlightString($dataSalesPerson,$search_value) . '</p>';
			}

			$data[$key]['status'] = getExhibitionStatusLable($value['status']);

			$uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';

			$uiAction .= '<li class="list-inline-item px-2">';
			$uiAction .= '<a onclick="editView(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Edit"><i class="bx bx-edit-alt"></i></a>';
			$uiAction .= '</li>';

			$uiAction .= '</ul>';
			$data[$key]['action'] = $uiAction;
		}

		$jsonData = array(
			"draw" => intval($request['draw']),
			// for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
			"recordsTotal" => intval($recordsTotal),
			// total number of records
			"recordsFiltered" => intval($recordsFiltered),
			// total number of records after searching, if there is no searching then totalFiltered = totalData
			"data" => $data, // total data array

		);
		return $jsonData;
	}

	public function save(Request $request)
	{

		$validator = Validator::make($request->all(), [
			'exhibition_id' => ['required'],
			'exhibition_name' => ['required'],
			'exhibition_sale_persons' => ['required'],
			'exhibition_status' => ['required'],
			'exhibition_city_id' => ['required'],

		]);
		if ($validator->fails()) {

			$response = array();
			$response['status'] = 0;
			$response['msg'] = "The request could not be understood by the server due to malformed syntax";
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();

			return response()->json($response)->header('Content-Type', 'application/json');
		} else {
			if ($request->exhibition_id != 0) {
				$Exhibition = Exhibition::find($request->exhibition_id);
			} else {
				$Exhibition = new Exhibition();
			}
			$Exhibition->name = $request->exhibition_name;
			$Exhibition->city_id = $request->exhibition_city_id;
			$Exhibition->status = $request->exhibition_status;
			$Exhibition->save();
			if ($Exhibition) {

				ExhibitionSalesPersons::where('exhibition_id', $Exhibition->id)->delete();

				$exhibition_sale_persons = $request->exhibition_sale_persons;

				foreach ($exhibition_sale_persons as $key => $value) {

					$ExhibitionSalesPersons = new ExhibitionSalesPersons();
					$ExhibitionSalesPersons->exhibition_id = $Exhibition->id;
					$ExhibitionSalesPersons->user_id = $value;
					$ExhibitionSalesPersons->save();
				}

				if ($request->exhibition_id != 0) {

					$response = successRes("Successfully saved exhibition");

					$debugLog = array();
					$debugLog['name'] = "exhibition-edit";
					$debugLog['description'] = "exhibition #" . $Exhibition->id . "(" . $Exhibition->name . ") has been updated ";
					saveDebugLog($debugLog);
				} else {
					$response = successRes("Successfully added exhibition");

					$debugLog = array();
					$debugLog['name'] = "exhibition-add";
					$debugLog['description'] = "exhibition #" . $Exhibition->id . "(" . $Exhibition->name . ") has been added ";
					saveDebugLog($debugLog);
				}
			}

			return response()->json($response)->header('Content-Type', 'application/json');
		}
	}

	public function detail(Request $request)
	{

		$Exhibition = Exhibition::find($request->id);
		if ($Exhibition) {
			$Exhibition = json_decode(json_encode($Exhibition), true);

			$response = successRes("Successfully exhibition");
			$response['data'] = $Exhibition;
			$response['data']['city'] = array();
			$response['data']['city']['id'] = $Exhibition['city_id'];
			$response['data']['city']['text'] = getCityName($Exhibition['city_id']);

			$ExhibitionSalesPersons = ExhibitionSalesPersons::where('exhibition_id', $Exhibition['id'])->get();
			$ExhibitionSalesPersons = json_decode(json_encode($ExhibitionSalesPersons), true);
			$ExhibitionSalesPersons = array_column($ExhibitionSalesPersons, 'user_id');

			if (count($ExhibitionSalesPersons) > 0) {

				$query = DB::table('users');
				$query->select('users.id as id', DB::raw('CONCAT(first_name," ", last_name) AS text'));
				//$query->leftJoin('users', 'sale_person.user_id', '=', 'users.id');
				//$query->whereIn('sale_person.type', $SalesHierarchyId);
				$query->whereIn('users.id', $ExhibitionSalesPersons);
				// $query->where('users.type', 2);
				$ExhibitionSalesPersons = $query->get();
			}

			$response['data']['sale_persons'] = $ExhibitionSalesPersons;
		} else {
			$response = errorRes("Invalid id");
		}
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function searchSalesPerson(Request $request)
	{

		$query = DB::table('users');
		$query->select('users.id as id','users.type', DB::raw('CONCAT(first_name," ", last_name) AS text'));
		// $query->leftJoin('users', 'sale_person.user_id', '=', 'users.id');
		//$query->whereIn('sale_person.type', $SalesHierarchyId);
		$query->whereIn('users.type', array(2, 201, 202, 301, 302, 101, 102, 103, 104, 105, 11, 6, 8, 0, 1));
		//$query->where('users.reference_id', '!=', 0);
		$q = $request->q;
		$query->where(function ($query) use ($q) {
			$query->where('users.first_name', 'like', '%' . $q . '%');
			$query->orWhere('users.last_name', 'like', '%' . $q . '%');
			$query->orWhereRaw("CONCAT(users.first_name,' ',users.last_name)" . ' like ? ', ["%" . $q . "%"]);
		});

		$query->limit(50);
		$data = $query->get();
		$newdata = array();
		foreach ($data as $key => $value) {
			$data1['id'] = $value->id;
			$data1['text'] = $value->text ." ". getUserTypeMainLabel($value->type);
			array_push($newdata, $data1);
		}
		$data = $newdata;

		$response = array();
		$response['results'] = $data;
		$response['pagination']['more'] = true;
		return response()->json($response)->header('Content-Type', 'application/json');
	}
}