<?php

namespace App\Http\Controllers;
use App\Models\CityList;
use App\Models\Company;
use App\Models\StateList;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

//use Session;

class MasterCompanyController extends Controller {

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
		$data['title'] = "Company Master";
		return view('master/company', compact('data'));
	}

	public function searchState(Request $request) {

		$StateList = array();
		$StateList = StateList::select('id', 'name as text');
		$StateList->where('country_id', $request->country_id);

		$StateList->where('name', 'like', "%" . $request->q . "%");
		$StateList->limit(5);
		$StateList = $StateList->get();

		$response = array();
		$response['results'] = $StateList;
		$response['pagination']['more'] = false;
		return response()->json($response)->header('Content-Type', 'application/json');

	}

	public function searchCity(Request $request) {

		$CityList = array();
		$CityList = CityList::select('id', 'name as text');
		$CityList->where('country_id', $request->country_id);
		$CityList->where('state_id', $request->state_id);
		$CityList->where('name', 'like', "%" . $request->q . "%");
		$CityList->where('status', 1);
		$CityList->limit(5);
		$CityList = $CityList->get();

		$response = array();
		$response['results'] = $CityList;
		$response['pagination']['more'] = false;
		return response()->json($response)->header('Content-Type', 'application/json');

	}

	function ajax(Request $request) {
		//DB::enableQueryLog();

		$searchColumns = array(

			0 => 'companies.id',
			1 => 'companies.name',
		);

		$columns = array(
			0 => 'companies.id',
			1 => 'companies.name',
			2 => 'companies.city_id',
			3 => 'companies.status',
			4 => 'companies.last_name',
			6 => 'companies.state_id',
			7 => 'companies.country_id',
			8 => 'companies.email',
			9 => 'companies.phone_number',
			10 => 'country_list.name as country_list_name',
			11 => 'state_list.name as state_list_name',
			12 => 'city_list.name as city_list_name',
			13 => 'companies.first_name',

		);

		$recordsTotal = DB::table('companies')->count();
		$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.
		$query = DB::table('companies');
		$query->leftJoin('country_list', 'country_list.id', '=', 'companies.country_id');
		$query->leftJoin('state_list', 'state_list.id', '=', 'companies.state_id');
		$query->leftJoin('city_list', 'city_list.id', '=', 'companies.city_id');
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
			$data[$key]['status'] = getCompanyStatusLable($value['status']);

			$data[$key]['name'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">' . highlightString($value['name'],$search_value) . '</a><p class="text-muted mb-0">' . highlightString($value['first_name'] . " " . $value['last_name'],$search_value) . '</p></h5>';

			$data[$key]['email'] = '<p class="text-muted mb-0">' . highlightString($value['email'],$search_value) . '</p>
             <p class="text-muted mb-0">' . highlightString($value['phone_number'],$search_value) . '</p>';

			$data[$key]['address'] = highlightString($value['city_list_name'] . "," . $value['state_list_name'] . "," . $value['country_list_name'],$search_value);

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

		$company_address_line2 = isset($request->company_address_line2) ? $request->company_address_line2 : '';

		$validator = Validator::make($request->all(), [
			'company_id' => ['required'],
			'company_name' => ['required'],
			'company_first_name' => ['required'],
			'company_last_name' => ['required'],
			'company_email' => ['required','email:rfc,dns'],
			'company_phone_number' => ['required','digits:10','regex:/^[1-9][0-9]*$/'],
			'company_address_line1' => ['required'],
			'company_country_id' => ['required'],
			'company_state_id' => ['required'],
			'company_city_id' => ['required'],
			'company_status' => ['required'],

		]);

		if ($validator->fails()) {

			$response = array();
			$response['status'] = 0;
			$response['msg'] = "The request could not be understood by the server due to malformed syntax";
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();

			return response()->json($response)->header('Content-Type', 'application/json');

		} else {

			$alreadyName = Company::query();

			if ($request->company_id != 0) {

				$alreadyName->where('name', $request->company_name);
				$alreadyName->where('id', '!=', $request->company_id);

			} else {
				$alreadyName->where('name', $request->company_name);

			}

			$alreadyName = $alreadyName->first();

			if ($alreadyName) {

				$response = errorRes("already company name exits, Try with another company name");

			} else {

				if ($request->company_id != 0) {

					$Company = Company::find($request->company_id);

				} else {
					//$Company = new Company();
					$response = errorRes("Invalid Process");
					return response()->json($response)->header('Content-Type', 'application/json');

				}

				$Company->name = $request->company_name;
				$Company->first_name = $request->company_first_name;
				$Company->last_name = $request->company_last_name;
				$Company->email = $request->company_email;
				$Company->phone_number = $request->company_phone_number;
				$Company->address_line1 = $request->company_address_line1;
				$Company->address_line2 = $company_address_line2;
				$Company->pincode = $request->company_pincode;
				$Company->country_id = $request->company_country_id;
				$Company->state_id = $request->company_state_id;
				$Company->city_id = $request->company_city_id;
				$Company->status = $request->company_status;
				$Company->save();

				if ($Company) {

					if ($request->company_id != 0) {

						$response = successRes("Successfully saved company");

						$debugLog = array();
						$debugLog['name'] = "company-edit";
						$debugLog['description'] = "company #" . $Company->id . "(" . $Company->name . ") has been updated ";
						saveDebugLog($debugLog);

					} else {
						$response = successRes("Successfully added company");

						$debugLog = array();
						$debugLog['name'] = "company-add";
						$debugLog['description'] = "company #" . $Company->id . "(" . $Company->name . ") has been added ";
						saveDebugLog($debugLog);

					}

				}

			}

			return response()->json($response)->header('Content-Type', 'application/json');

		}

	}

	public function detail(Request $request) {

		$Company = Company::with(array('country' => function ($query) {
			$query->select('id', 'name');
		}, 'state' => function ($query) {
			$query->select('id', 'name');
		}, 'city' => function ($query) {
			$query->select('id', 'name');
		}))->find($request->id);
		if ($Company) {

			$response = successRes("Successfully get user");
			$response['data'] = $Company;

		} else {
			$response = errorRes("Invalid id");
		}
		return response()->json($response)->header('Content-Type', 'application/json');

	}
}