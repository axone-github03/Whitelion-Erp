<?php

namespace App\Http\Controllers\Quotation;

use App\Http\Controllers\Controller;

use App\Models\wlmst_app_user;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

//use Session;

class QuotAppUserMasterController extends Controller
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
		$data['title'] = "App User Master ";
		return view('quotation/master/appuser/appuser', compact('data'));
	}

	function ajax(Request $request)
	{
		//DB::enableQueryLog();

		$searchColumns = array(
			0 => 'wlmst_app_users.id',
			1 => 'wlmst_app_users.user_id',
		);

		$columns = array(
			'wlmst_app_users.id',
			'wlmst_app_users.user_id',
			'wlmst_app_users.user_type',
			'wlmst_app_users.description',
			'wlmst_app_users.source',
			'wlmst_app_users.created_at',
			'wlmst_app_users.entryby',
			'wlmst_app_users.entryip',
		);

		$recordsTotal = wlmst_app_user::count();
		$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.

		$query = wlmst_app_user::query();
		$query->select($columns);
		$query->limit($request->length);
		$query->offset($request->start);
		$query->orderBy($columns[$request['order'][0]['column']], $request['order'][0]['dir']);
		$isFilterApply = 0;
		$search_value = '';

		if (isset($request->q)) {
			$isFilterApply = 1;
			$search_value = $request->q;
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
			$data[$key]['companyname'] = "<p>" . highlightString($data[$key]['companyname'],$search_value) . '</p>';
			$data[$key]['name'] = "<p>" . highlightString($data[$key]['shortname'],$search_value) . '</p>';
			$data[$key]['user_type'] = "<p>" . highlightString($data[$key]['shortname'],$search_value) . '</p>';
			$data[$key]['description'] = "<p>" . highlightString($data[$key]['shortname'],$search_value) . '</p>';
			$data[$key]['source'] = "<p>" . highlightString($data[$key]['shortname'],$search_value) . '</p>';
			$data[$key]['login_date'] = "<p>" . highlightString($data[$key]['shortname'],$search_value) . '</p>';

			$uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';

			// $uiAction .= '<li class="list-inline-item px-2">';
			// $uiAction .= '<a onclick="editView(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Edit"><i class="bx bx-edit-alt"></i></a>';
			// $uiAction .= '</li>';

			// $uiAction .= '<li class="list-inline-item px-2">';
			// $uiAction .= '<a onclick="deleteWarning(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Delete"><i class="bx bx-trash-alt"></i></a>';
			// $uiAction .= '</li>';

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
}
