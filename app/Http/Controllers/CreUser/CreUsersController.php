<?php
namespace App\Http\Controllers\CreUser;
use App\Http\Controllers\Controller;
use App\Models\CountryList;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CreUsersController extends Controller {

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
		$data['title'] = "CRE - Users";
		$data['country_list'] = CountryList::get();
		$data['type'] = 12;
		return view('users/cre', compact('data'));
	}

	public function ajax(Request $request) {

		$searchColumns = array(
			'users.id',
			'users.first_name',
			'users.last_name',
			'users.email',
			'users.phone_number',
		);

		$sortColumns = array(
			0 => 'users.id',
			1 => 'users.first_name',
			2 => 'users.email',
			3 => 'companies.name',
			4 => 'users.status',
		);

		$selectColumns = array(
			'users.id',
			'users.first_name',
			'users.email',
			'wlmst_service_user.reporting_manager_id',
			'users.status',
			'users.last_name',
			'users.type',
			'users.status',
			'users.phone_number',
			'companies.name as company_name',
			'companies.first_name as company_first_name',
			'companies.last_name as company_last_name',
			'service_hierarchies.code as service_hierarchies_code',
			'users.last_login_date_time',

		);

		$recordsTotal = DB::table('users')->where('type', 13)->count();
		$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.
		$query = DB::table('users');
		$query->leftJoin('companies', 'companies.id', '=', 'users.company_id');
		$query->leftJoin('wlmst_service_user', 'wlmst_service_user.id', '=', 'users.reference_id');
		$query->leftJoin('service_hierarchies', 'service_hierarchies.id', '=', 'wlmst_service_user.type');
		$query->select($selectColumns);
		$query->where('users.type', 13);
		$query->limit($request->length);
		$query->offset($request->start);
		$query->orderBy($sortColumns[$request['order'][0]['column']], $request['order'][0]['dir']);
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

			$data[$key]['id'] = '<div class="avatar-xs"><span class="avatar-title rounded-circle">' . $data[$key]['id'] . '</span></div>';

			$data[$key]['name'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">' . highlightString($value['first_name'] . " " . $value['last_name'],$search_value) . '</a></h5>
             <p class="text-muted mb-0">' . highlightString(getUserTypeName($value['type']),$search_value) . '</p><p class="text-muted mb-0">' . highlightString($value['service_hierarchies_code'],$search_value) . '</p>';

			if ($value['reporting_manager_id'] != 0) {

				$reportingManager = User::select('first_name', 'last_name', 'email', 'service_hierarchies.code')->leftJoin('wlmst_service_user', 'wlmst_service_user.user_id', '=', 'users.id')->leftJoin('service_hierarchies', 'service_hierarchies.id', '=', 'wlmst_service_user.type')->find($value['reporting_manager_id']);
				if ($reportingManager) {

					$data[$key]['reporting_manager'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">' . $reportingManager->first_name . " " . $reportingManager->last_name . '</h5>
					<p class="text-muted mb-0">' . $reportingManager->code . '</p>
					<p class="text-muted mb-0">' . $reportingManager->email . '</p>';

				}

			} else {
				$data[$key]['reporting_manager'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">' . $value['company_name'] . '</a></h5>
				<p class="text-muted mb-0">COMPANY</p>
             <p class="text-muted mb-0">' . highlightString($value['company_first_name'] . ' ' . $value['company_last_name'],$search_value) . '</p>';
			}

			// $data[$key]['company'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">' . $value['company_name'] . '</a></h5>
			//           <p class="text-muted mb-0">' . $value['company_first_name'] . ' ' . $value['company_last_name'] . '</p>';

			// if ($data[$key]['created_at'] == $data[$key]['last_active_date_time']) {

			// 	$data[$key]['last_active_date_time'] = "-";
			// 	$data[$key]['last_login_date_time'] = "-";

			// } else {

			// 	$data[$key]['last_active_date_time'] = convertDateTime($value['last_active_date_time']);
			// 	$data[$key]['last_login_date_time'] = convertDateTime($value['last_login_date_time']);

			// }

			// $data[$key]['active_login'] = '<p class="text-muted mb-0">' . $data[$key]['last_active_date_time'] . '</p>
			//           <p class="text-muted mb-0">' . $data[$key]['last_login_date_time'] . '</p>';

			$uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';

			$data[$key]['status'] = getUserStatusLable($value['status']);
			$data[$key]['email'] = '<p class="text-muted mb-0">' . highlightString($value['email'],$search_value) . '</p>
             <p class="text-muted mb-0">' . highlightString($value['phone_number'],$search_value) . '</p>';

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

}