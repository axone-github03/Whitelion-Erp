<?php

namespace App\Http\Controllers;

//use Illuminate\Support\Facades\Hash;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DebugLogController extends Controller
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
		$data['title'] = "Log";
		return view('debug/log', compact('data'));
	}

	function ajax(Request $request)
	{

		$columns = array(
			// datatable column index  => database column name
			0 => 'debug_log.id',
			1 => 'debug_log.name',
			2 => 'debug_log.description',
			3 => 'debug_log.user_id',
			4 => 'debug_log.created_at',

		);

		$recordsTotal = DB::table('debug_log')->count();
		$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.

		$query = DB::table('debug_log');
		$query->select('debug_log.*', 'users.id as user_id', 'users.first_name', 'users.last_name', 'users.type as user_type', 'channel_partner.firm_name');
		$query->leftJoin('users', 'debug_log.user_id', '=', 'users.id');
		$query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
		// $query->limit($request->length);
		// $query->offset($request->start);
		$query->orderBy($columns[$request['order'][0]['column']], $request['order'][0]['dir']);
		$isFilterApply = 0;

		if (isset($request['search']['value'])) {
			$isFilterApply = 1;
			$search_value = $request['search']['value'];

			$query->where(function ($query) use ($search_value) {
				$query->where('debug_log.name', 'like', "%" . $search_value . "%")
					->orWhere('debug_log.description', 'like', "%" . $search_value . "%");
			});
		}
		$recordsFiltered = $query->count();

		$query = DB::table('debug_log');
		$query->select('debug_log.*', 'users.id as user_id', 'users.first_name', 'users.last_name', 'users.type as user_type', 'channel_partner.firm_name');
		$query->leftJoin('users', 'debug_log.user_id', '=', 'users.id');
		$query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
		$query->limit($request->length);
		$query->offset($request->start);
		$query->orderBy($columns[$request['order'][0]['column']], $request['order'][0]['dir']);
		$isFilterApply = 0;

		if (isset($request['search']['value'])) {
			$isFilterApply = 1;
			$search_value = $request['search']['value'];

			$query->where(function ($query) use ($search_value) {
				$query->where('debug_log.name', 'like', "%" . $search_value . "%")
					->orWhere('debug_log.description', 'like', "%" . $search_value . "%");
			});
		}
		$data = $query->get();
		$data = json_decode(json_encode($data), true);



		foreach ($data as $key => $value) {

			$processBy = "";
			if (isset($value['firm_name']) && $value['firm_name'] != "") {
				$processBy = $value['firm_name'];
			} else {
				$processBy = $value['first_name'] . " " . $value['last_name'];
			}

			$data[$key]['process_by'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark" >#' . $value['user_id'] . ' ' . $processBy . '</a></h5>
             <p class="text-muted mb-0">' . getUserTypeName($value['user_type']) . '</p>';
			$data[$key]['process_name'] = '<a href="javascript: void(0);" class="text-body fw-bold">' . $value['name'] . '</a>';

			$data[$key]['description'] = '<p class="">' . $data[$key]['description'] . '</p>';
			$data[$key]['created_at'] = convertDateTime($value['created_at']);
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
