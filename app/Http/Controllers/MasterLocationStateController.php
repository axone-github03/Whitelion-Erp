<?php
namespace App\Http\Controllers;
use App\Models\CountryList;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

//use Illuminate\Support\Facades\Hash;
//use Session;

class MasterLocationStateController extends Controller {

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
		$data['title'] = "State List";
		$data['country_list'] = CountryList::get();
		return view('master/location/state', compact('data'));

	}

	function ajax(Request $request) {

		$columns = array(
			// datatable column index  => database column name
			0 => 'state_list.id',
			1 => 'state_list.name',
			2 => 'state_list.created_at',

		);

		$recordsTotal = DB::table('state_list')->where('country_id', $request->country_id)->count();
		$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.

		$query = DB::table('state_list');
		$query->select('state_list.id', 'state_list.name', 'state_list.created_at');
		$query->where('country_id', $request->country_id);
		$query->limit($request->length);
		$query->offset($request->start);
		$query->orderBy($columns[$request['order'][0]['column']], $request['order'][0]['dir']);
		$isFilterApply = 0;
        $search_value = '';

		if (isset($request['search']['value'])) {
			$isFilterApply = 1;
			$search_value = $request['search']['value'];

			$query->where(function ($query) use ($search_value) {
				$query->where('state_list.name', 'like', "%" . $search_value . "%")
					->orWhere('state_list.id', 'like', "%" . $search_value . "%");
			});

		}
		$data = $query->get();
		$data = json_decode(json_encode($data), true);

		if ($isFilterApply == 1) {
			$recordsFiltered = count($data);

		}

		foreach ($data as $key => $val) {

			$data[$key]['created_at'] = highlightString(convertDateTime($val['created_at']),$search_value);
			$data[$key]['name'] = highlightString($val['name'],$search_value);
			$data[$key]['id'] = highlightString($val['id'],$search_value);
			
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