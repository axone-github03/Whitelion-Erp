<?php

namespace App\Http\Controllers;
use App\Models\CityList;
use App\Models\CountryList;
use App\Models\StateList;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

//use Session;

class MasterLocationCityController extends Controller {

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
		$data['title'] = "City List";
		$data['country_list'] = CountryList::get();
		return view('master/location/city', compact('data'));

	}

	public function searchState(Request $request) {

		$StateList = array();

		$needAll = isset($request->need_all) ? $request->need_all : 0;

		if ($needAll == 1) {

			if (strtolower($request->q) != "all") {

				$StateList = StateList::select('id', 'name as text');

				if ($request->country_id != 0) {
					$StateList->where('country_id', $request->country_id);

				}
				$StateList->where('name', 'like', "%" . $request->q . "%");
				$StateList->limit(5);
				$StateList = $StateList->get();

			} else {

				$CStateList = count($StateList);
				$StateList[$CStateList]['id'] = 0;
				$StateList[$CStateList]['text'] = "ALL";

			}

		} else {

			$StateList = StateList::select('id', 'name as text');

			if ($request->country_id != 0) {
				$StateList->where('country_id', $request->country_id);

			}
			$StateList->where('name', 'like', "%" . $request->q . "%");
			$StateList->limit(5);
			$StateList = $StateList->get();

		}

		$response = array();
		$response['results'] = $StateList;
		$response['pagination']['more'] = false;
		return response()->json($response)->header('Content-Type', 'application/json');

	}

	function ajax(Request $request) {

		$columns = array(
			// datatable column index  => database column name
			0 => 'city_list.id',
			1 => 'city_list.name',
			2 => 'state_list.state_name',
			3 => 'city_list.status',
			4 => 'city_list.created_at',

		);

		$query = DB::table('city_list');

		if ($request->state_id != 0) {
			$query->where('city_list.state_id', $request->state_id);
		}

		if ($request->country_id != 0) {
			$query->where('city_list.country_id', $request->country_id);
		}

		$recordsTotal = $query->count();

		$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.

		$query = DB::table('city_list');
		$query->leftJoin('state_list', 'state_list.id', '=', 'city_list.state_id');
		$query->select('city_list.id', 'city_list.name', 'state_list.name as state_name', 'city_list.created_at', 'city_list.status');

		if ($request->country_id != 0) {
			$query->where('city_list.country_id', $request->country_id);
		}

		if ($request->state_id != 0) {
			$query->where('city_list.state_id', $request->state_id);
		}
		$query->limit($request->length);
		$query->offset($request->start);
		$query->orderBy($columns[$request['order'][0]['column']], $request['order'][0]['dir']);
		$isFilterApply = 0;
        $search_value = '';

		if (isset($request['search']['value'])) {
			$isFilterApply = 1;
			$search_value = $request['search']['value'];
			$query->where(function ($query) use ($search_value) {
				$query->where('city_list.name', 'like', "%" . $search_value . "%")->orWhere('city_list.id', 'like', "%" . $search_value . "%");
			});

		}
		$data = $query->get();
		$data = json_decode(json_encode($data), true);

		if ($isFilterApply == 1) {
			$recordsFiltered = count($data);

		}

		foreach ($data as $key => $value) {

			//$data[$key]['created_at'] = convertDateTime($value['created_at']);
			$data[$key]['status'] = getCityStatusLable($value['status']);
			$data[$key]['name'] = highlightString($value['name'], $search_value);
			$data[$key]['state_name'] = highlightString($value['state_name'], $search_value);
			$data[$key]['id'] = highlightString($value['id'], $search_value);
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

	function searchCountry(Request $request) {

		$searchKeyword = isset($request->q) ? $request->q : "ind";
		$CountryList = array();
		$CountryList = CountryList::select('id', 'name as text');
		$CountryList->where('name', 'like', $searchKeyword . "%");
		$CountryList->limit(5);
		$CountryList = $CountryList->get();
		$response = array();
		$response['results'] = $CountryList;
		$response['pagination']['more'] = false;
		return response()->json($response)->header('Content-Type', 'application/json');

	}

	function detail(Request $request) {

		$CityList = CityList::find($request->id);
		if ($CityList) {

			$CityList['country'] = CountryList::select('id', 'name as text')->find($CityList->country_id);
			$CityList['state'] = StateList::select('id', 'name as text')->find($CityList->state_id);

			$response = successRes("Successfully get city detail");
			$response['data'] = $CityList;

		} else {
			$response = errorRes("Invalid id");
		}
		return response()->json($response)->header('Content-Type', 'application/json');

	}

	function save(Request $request) {

		$validator = Validator::make($request->all(), [
			'city_id' => ['required'],
			'city_name' => ['required'],
			'city_country_id' => ['required'],
			'city_state_id' => ['required'],
			'city_status' => ['required'],

		]);

		if ($validator->fails()) {

			$response = array();
			$response['status'] = 0;
			$response['msg'] = "Please fill required filed.";
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();

		} else {

			$alreadyCity = CityList::query();
			$alreadyCity->where('name', $request->city_name);
			if ($request->city_id != 0) {
				$alreadyCity->where('id', '!=', $request->city_id);
			}
			$alreadyCity = $alreadyCity->first();

			if ($alreadyCity) {

				$response = errorRes("Already name exists, Try with another name");

			} else {

				if ($request->city_id != 0) {
					$CityList = CityList::find($request->city_id);

				} else {
					$CityList = new CityList();
				}

				$CityList->name = $request->city_name;
				$CityList->country_id = $request->city_country_id;
				$CityList->state_id = $request->city_state_id;
				$CityList->status = $request->city_status;
				$CityList->save();
				$response = successRes("Successfully added city");

			}

		}

		return response()->json($response)->header('Content-Type', 'application/json');

	}
}