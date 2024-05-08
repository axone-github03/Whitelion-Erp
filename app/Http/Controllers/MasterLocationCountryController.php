<?php

namespace App\Http\Controllers;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

//use Session;

class MasterLocationCountryController extends Controller {

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
		$data['title'] = "Country List";
		return view('master/location/country', compact('data'));

	}

	function ajax(Request $request) {

		$columns = array(
			// datatable column index  => database column name
			0 => 'country_list.id',
			1 => 'country_list.code',
			2 => 'country_list.name',
			3 => 'country_list.name',
			4 => 'country_list.created_at',

		);

		$recordsTotal = DB::table('country_list')->count();
		$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.

		$query = DB::table('country_list');
		$query->select('country_list.id', 'country_list.code', 'country_list.name', 'country_list.created_at');
		$query->limit($request->length);
		$query->offset($request->start);
		$query->orderBy($columns[$request['order'][0]['column']], $request['order'][0]['dir']);
		$isFilterApply = 0;
        $search_value = '';

		if (isset($request['search']['value'])) {
			$isFilterApply = 1;
			$search_value = $request['search']['value'];

			$query->where(function ($query) use ($search_value) {
				$query->where('country_list.code', 'like', "%" . $search_value . "%")
					->orWhere('country_list.name', 'like', "%" . $search_value . "%")->orWhere('country_list.id', 'like', "%" . $search_value . "%");
			});

		}
		$data = $query->get();
		$data = json_decode(json_encode($data), true);

		if ($isFilterApply == 1) {
			$recordsFiltered = count($data);

		}

		foreach ($data as $key => $val) {

			$data[$key]['created_at'] = convertDateTime($val['created_at']);
			$data[$key]['name'] = highlightString($val['name'], $search_value);
			$data[$key]['code'] = highlightString($val['code'], $search_value);
			$data[$key]['id'] = highlightString($val['id'], $search_value);
			$data[$key]['flag'] = '<img id="header-lang-img" src="' . URL('/') . "/assets/images/flags/" . $val['name'] . ".jpg" . '" height="16">';
		}

		$jsonData = array(
			"draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
			"recordsTotal" => intval($recordsTotal), // total number of records
			"recordsFiltered" => intval($recordsFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
			"data" => $data, // total data array

		);
		return $jsonData;

	}
}