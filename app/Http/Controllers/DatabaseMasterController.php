<?php

namespace App\Http\Controllers;

use App\Models\Architect;
use App\Models\Electrician;
use App\Models\User;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DatabaseMasterController extends Controller {

	public function __construct() {
		$this->middleware(function ($request, $next) {
			$tabCanAccessBy = array(0, 1);
			if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
				return redirect()->route('dashboard');
			}
			return $next($request);
		});
	}

	public function index(Request $request) {
		$data = array();
		$data['title'] = "Move - Assignee";
		return view('database_master/move_assignee', compact('data'));
	}

	public function searchAssignedUser(Request $request) {

		$User = $UserResponse = array();
		$q = $request->q;
		$User = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));

		$User->where('users.status', 1);

		if (Auth::user()->type != 0 && Auth::user()->type != 1) {
			$User->where('users.type', 2);

			$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
			$User->whereIn('id', $childSalePersonsIds);

		} else {
			$User->whereIn('users.type', array(0, 1, 2));
		}
		$User->where(function ($query) use ($q) {
			$query->where('users.first_name', 'like', '%' . $q . '%');
			$query->orWhere('users.last_name', 'like', '%' . $q . '%');
		});
		$User->limit(5);
		$User = $User->get();

		if (count($User) > 0) {
			foreach ($User as $User_key => $User_value) {
				$UserResponse[$User_key]['id'] = $User_value['id'];
				$UserResponse[$User_key]['text'] = $User_value['full_name'];
			}
		}
		$response = array();
		$response['results'] = $UserResponse;
		$response['pagination']['more'] = false;
		return response()->json($response)->header('Content-Type', 'application/json');

	}

	public function save(Request $request) {

		$validator = Validator::make($request->all(), [
			'user_type' => ['required'],
			'from_assignee' => ['required'],
			'to_assignee' => ['required'],

		]);

		if ($validator->fails()) {

			$response = array();
			$response['status'] = 0;
			$response['msg'] = "The request could not be understood by the server due to malformed syntax";
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();

		} else {

			if ($request->user_type == "201,202") {

				Architect::query()->where('sale_person_id', $request->from_assignee)->update(['sale_person_id' => $request->to_assignee]);

			} else if ($request->user_type == "301,302") {

				Electrician::query()->where('sale_person_id', $request->from_assignee)->update(['sale_person_id' => $request->to_assignee]);

			}

			$response = successRes("Successfully moved assignee");

		}

		return response()->json($response)->header('Content-Type', 'application/json');

	}

}
