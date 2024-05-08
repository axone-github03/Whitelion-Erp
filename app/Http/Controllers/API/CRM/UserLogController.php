<?php

namespace App\Http\Controllers\API\CRM;
use App\Http\Controllers\Controller;
use App\Models\CRMLog;
use App\Models\Parameter;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserLogController extends Controller {

	public function __construct() {
		$this->middleware(function ($request, $next) {
			$tabCanAccessBy = array(202, 302);
			if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
				$response = errorRes("Invalid access", 401);
				return response()->json($response, $response['status_code'])->header('Content-Type', 'application/json');
			}
			return $next($request);
		});
	}

	public function poingValue() {

		$isArchitect = isArchitect();
		$isElectrician = isElectrician();

		if ($isArchitect == 1) {
			$Parameter = Parameter::where('code', 'point-value-architect')->first();
		} else if ($isElectrician == 1) {
			$Parameter = Parameter::where('code', 'point-value-electrician')->first();
		}

		$pointValue = $Parameter->name_value;
		$response = successRes("Point Value");
		$response['data'] = $pointValue;
		return response()->json($response, $response['status_code'])->header('Content-Type', 'application/json');

	}

	public function ajax(Request $request) {

		$searchColumns = array(
			'crm_log.id',
			'crm_log.name',
			'crm_log.description',
			'users.first_name',
			'users.last_name',

		);

		$sortingColumns = array(
			0 => 'crm_log.id',
			1 => 'crm_log.name',
			2 => 'crm_log.description',
			3 => 'users.first_name',
			4 => 'crm_log.created_at',

		);

		$selectColumns = array(
			'crm_log.id',
			'crm_log.name',
			'crm_log.description',
			'crm_log.user_id',
			'users.first_name',
			'users.last_name',
			'users.type as user_type',
			'crm_log.created_at',

		);

		// $query = CRMLog::query();
		// $query->leftJoin('users', 'crm_log.user_id', '=', 'users.id');
		// $query->where('crm_log.for_user_id', Auth::user()->id);
		// $recordsTotal = $query->count();
		// $recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.
		$query = CRMLog::query();
		$query->leftJoin('users', 'crm_log.user_id', '=', 'users.id');
		$query->where('crm_log.for_user_id', Auth::user()->id);
		$query->select($selectColumns);
		$query->orderBy('crm_log.id', 'desc');
		// $query->limit($request->length);
		// $query->offset($request->start);
		// $query->orderBy($sortingColumns[$request['order'][0]['column']], $request['order'][0]['dir']);

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

		$data = $query->paginate();

		foreach ($data as $key => $value) {
			$data[$key]['user_lable'] = getUserTypeName($value['user_type']);
		}

		$response = successRes("Transcation Log");
		$response['data'] = $data;
		return response()->json($response, $response['status_code'])->header('Content-Type', 'application/json');

	}
}