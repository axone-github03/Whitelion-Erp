<?php

namespace App\Http\Controllers;

use App\Models\ArchitectLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CRMArchitechLogController extends Controller {

	public function __construct() {
		$this->middleware(function ($request, $next) {
			$tabCanAccessBy = array(202);
			if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
				return redirect()->route('dashboard');
			}
			return $next($request);
		});
	}

	public function index(Request $request) {

		$data = array();
		$data['title'] = "Transactional Log";
		return view('crm/architect/log', compact('data'));
	}

	public function ajax(Request $request) {

		$searchColumns = array(
			'architect_log.id',
			'architect_log.name',
			'architect_log.description',
			'users.first_name',
			'users.last_name',

		);

		$sortingColumns = array(
			0 => 'architect_log.id',
			1 => 'architect_log.name',
			2 => 'architect_log.description',
			3 => 'users.first_name',
			4 => 'architect_log.created_at',

		);

		$selectColumns = array(
			'architect_log.id',
			'architect_log.name',
			'architect_log.description',
			'architect_log.user_id',
			'users.first_name',
			'users.last_name',
			'users.type as user_type',
			'architect_log.created_at',

		);

		$query = ArchitectLog::query();
		$query->leftJoin('users', 'architect_log.user_id', '=', 'users.id');
		$query->where('architect_log.architect_user_id', Auth::user()->id);
		$recordsTotal = $query->count();
		$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.
		$query = ArchitectLog::query();
		$query->leftJoin('users', 'architect_log.user_id', '=', 'users.id');
		$query->where('architect_log.architect_user_id', Auth::user()->id);
		$query->select($selectColumns);
		$query->limit($request->length);
		$query->offset($request->start);
		$query->orderBy($sortingColumns[$request['order'][0]['column']], $request['order'][0]['dir']);

		$isFilterApply = 0;

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
			$viewData[$key]['id'] = $value['id'];

			$processBy = "";
			$processBy = $value['first_name'] . " " . $value['last_name'];
			$viewData[$key]['process_by'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark" >#' . $value['user_id'] . ' ' . $processBy . '</a></h5>
             <p class="text-muted mb-0">' . getUserTypeName($value['user_type']) . '</p>';
			$viewData[$key]['name'] = '<a href="javascript: void(0);" class="text-body fw-bold">' . $value['name'] . '</a>';

			$viewData[$key]['description'] = '<p class="">' . $data[$key]['description'] . '</p>';
			$viewData[$key]['created_at'] = convertDateTime($value['created_at']);
		}

		$jsonData = array(
			"draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
			"recordsTotal" => intval($recordsTotal), // total number of records
			"recordsFiltered" => intval($recordsFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
			"data" => $viewData, // total data array

		);
		return $jsonData;

	}
}