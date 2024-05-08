<?php

namespace App\Http\Controllers;

use App\Models\Architect;
use App\Models\ArchitectCategory;
use App\Models\UserUpdate;
use App\Models\ChannelPartner;
use App\Models\CRMHelpDocument;
use App\Models\CRMLog;
use App\Models\Inquiry;
use App\Models\SalePerson;
use App\Models\User;
use App\Models\StateList;
use App\Models\CountryList;
use App\Models\CityList;
use App\Models\Lead;
use Config;
use DB;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Whatsapp\WhatsappApiContoller;
use Mail;

//use Illuminate\Support\Facades\Hash;

class ArchitectsController extends Controller
{

	public function __construct()
	{

		$this->middleware(function ($request, $next) {

			$tabCanAccessBy = array(0, 1, 2,6, 7, 9, 101, 102, 103, 104, 105);

			if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
				return redirect()->route('dashboard');
			}

			return $next($request);
		});
	}

	public function nonPrime(Request $request)
	{

		$data = array();
		$data['title'] = "Non Prime - Architects";
		$data['type'] = 201;
		$data['isSalePerson'] = isSalePerson();
		$data['addView'] = (isset($request->add) && $request->add == 1) ? 1 : 0;

		$data['source_types'] = getArchitectsSourceTypes();
		$data['viewMode'] = (isset($request->view_mode) && $request->view_mode == 1) ? 1 : 0;

		if ($data['isSalePerson'] == 1) {
			$data['viewMode'] = 0;
		}

		return view('architects/index', compact('data'));
	}

	public function prime(Request $request)
	{

		$params = array();
		$data = array();
		$data['title'] = "Prime - Architects";
		$data['type'] = 202;
		$data['isSalePerson'] = isSalePerson();
		$data['addView'] = (isset($request->add) && $request->add == 1) ? 1 : 0;
		$data['source_types'] = getArchitectsSourceTypes();
		$data['architect_categories'] = ArchitectCategory::orderBy('id', 'asc')->get();
		$data['viewMode'] = (isset($request->view_mode) && $request->view_mode == 1) ? 1 : 0;
		$data['searchUserId'] = (isset($request->id)) ? $request->id : "";



		if ($data['isSalePerson'] == 1) {
			$data['viewMode'] = 0;
		}

		return view('architects/index', compact('data'));
	}

	public function ajax(Request $request)
	{

		$isSalePerson = isSalePerson();
		$isChannelPartner = isChannelPartner(Auth::user()->type);
		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		$isMarketingDispatcherUser = isMarketingDispatcherUser();
		$isTaleSalesUser = isTaleSalesUser();
		if ($isTaleSalesUser == 1) {
			$TeleSalesCity = TeleSalesCity(Auth::user()->id);
		}

		if ($isSalePerson == 1) {
			$SalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
		}

		$viewMode = (isset($request->view_mode) && $request->view_mode == 1) ? 1 : 0;

		$searchColumns = array(
			0 => 'users.id',
			1 => 'users.first_name',
			2 => 'users.last_name',
			3 => 'users.email',
			4 => 'users.phone_number',
			5 => 'sale_person.first_name',
			6 => 'sale_person.last_name',
			7 => "CONCAT(users.first_name,' ',users.last_name)",
			8 => "CONCAT(sale_person.first_name,' ',sale_person.last_name)",

		);

		$sortingColumns = array(
			0 => 'users.id',
			1 => 'users.first_name',
			2 => 'users.email',
			3 => 'architect.sale_person_id',
			4 => 'architect.total_point_current',
			5 => 'architect.total_point',
			6 => 'users.status',
			7 => 'architect.category_id',
			8 => 'architect.principal_architect_name',
			9 => 'users.created_by',

		);

		$selectColumns = array(
			'users.id',
			'users.type',
			'users.first_name',
			'users.last_name',
			'users.email',
			'users.phone_number',
			'architect.sale_person_id',
			'users.status',
			'architect.total_point_current',
			'sale_person.first_name as sale_person_first_name',
			'sale_person.last_name  as sale_person_last_name',
			'users.created_at',
			'architect.category_id',
			'architect.principal_architect_name',
			'architect.tele_verified',
			'architect.tele_not_verified',
			'architect.instagram_link',
			'architect.data_verified',
			'architect.data_not_verified',
			'architect.missing_data',
			'architect.total_point',
			'created_by_user.first_name as created_by_user_first_name',
			'created_by_user.last_name as created_by_user_last_name',

		);

		$query = Architect::query();
		$query->leftJoin('users', 'users.id', '=', 'architect.user_id');

		$query->leftJoin('users as sale_person', 'architect.sale_person_id', '=', 'sale_person.id');
		$query->whereIn('architect.type', [201, 202]);
		if ($isAdminOrCompanyAdmin == 1 || $isMarketingDispatcherUser) {
		} else if ($isSalePerson == 1) {
			$query->whereIn('architect.sale_person_id', $SalePersonsIds);
		} else if ($isChannelPartner != 0) {
			$query->where('architect.added_by', Auth::user()->id);
		} else if ($isTaleSalesUser == 1) {
			$query->whereIn('users.city_id', $TeleSalesCity);
		}

		if (isset($request->search_user_id) && $request->search_user_id != "") {
			$query->where('users.id', $request->search_user_id);
		}

		if (isset($request->category_id) && $request->category_id != "-1") {
			$query->where('architect.category_id', $request->category_id);
		}

		$recordsTotal = $query->count();
		$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.

		$query = Architect::query();
		$query->leftJoin('users', 'users.id', '=', 'architect.user_id');
		$query->leftJoin('users as sale_person', 'architect.sale_person_id', '=', 'sale_person.id');
		$query->whereIn('architect.type', [201, 202]);
		if ($isAdminOrCompanyAdmin == 1 || $isMarketingDispatcherUser) {
		} else if ($isSalePerson == 1) {
			$query->whereIn('architect.sale_person_id', $SalePersonsIds);
		} else if ($isChannelPartner != 0) {
			$query->where('architect.added_by', Auth::user()->id);
		} else if ($isTaleSalesUser == 1) {
			$query->whereIn('users.city_id', $TeleSalesCity);
		}
		if (isset($request->search_user_id) && $request->search_user_id != "") {
			$query->where('users.id', $request->search_user_id);
		}

		if (isset($request->category_id) && $request->category_id != "-1") {
			$query->where('architect.category_id', $request->category_id);
		}
		$query->select('architect.id');
		// $query->limit($request->length);
		// $query->offset($request->start);

		$isFilterApply = 0;

		if (isset($request['search']['value'])) {
			$isFilterApply = 1;
			$search_value = $request['search']['value'];
			$query->where(function ($query) use ($search_value, $searchColumns) {

				for ($i = 0; $i < count($searchColumns); $i++) {

					if ($i == 0) {
						$query->whereRaw($searchColumns[$i] . ' like ? ', [$search_value]);
						$query->orWhereRaw($searchColumns[$i] . ' like ? ', ["%" . $search_value . "%"]);
					} else {

						$query->orWhereRaw($searchColumns[$i] . ' like ? ', [$search_value]);
						$query->orWhereRaw($searchColumns[$i] . ' like ? ', ["%" . $search_value . "%"]);
					}
				}
			});
		}

		$recordsFiltered = $query->count();

		$query = Architect::query();
		$query->leftJoin('users', 'users.id', '=', 'architect.user_id');
		$query->leftJoin('users as created_by_user', 'users.created_by', '=', 'created_by_user.id');
		$query->leftJoin('users as sale_person', 'architect.sale_person_id', '=', 'sale_person.id');
		$query->whereIn('architect.type', [201, 202]);
		if ($isAdminOrCompanyAdmin == 1 || $isMarketingDispatcherUser) {
		} else if ($isSalePerson == 1) {
			$query->whereIn('architect.sale_person_id', $SalePersonsIds);
		} else if ($isChannelPartner != 0) {
			$query->where('architect.added_by', Auth::user()->id);
		}

		if (isset($request->search_user_id) && $request->search_user_id != "") {
			$query->where('users.id', $request->search_user_id);
		}
		if (isset($request->category_id) && $request->category_id != "-1") {
			$query->where('architect.category_id', $request->category_id);
		}
		$query->select($selectColumns);
		$query->limit($request->length);
		$query->offset($request->start);
		//$query->orderBy($sortingColumns[$request['order'][0]['column']], $request['order'][0]['dir']);
		if (isset($request->filter_architect_advance) && $request->filter_architect_advance == "1") {
			$query->orderBy('architect.total_inquiry', 'desc');
		} else {
			$query->orderBy($sortingColumns[$request['order'][0]['column']], $request['order'][0]['dir']);
		}
		$isFilterApply = 0;

		if (isset($request['search']['value'])) {
			$isFilterApply = 1;
			$search_value = $request['search']['value'];
			$query->where(function ($query) use ($search_value, $searchColumns) {

				for ($i = 0; $i < count($searchColumns); $i++) {

					if ($i == 0) {
						$query->whereRaw($searchColumns[$i] . ' like ? ', [$search_value]);
						$query->orWhereRaw($searchColumns[$i] . ' like ? ', ["%" . $search_value . "%"]);
					} else {

						$query->orWhereRaw($searchColumns[$i] . ' like ? ', [$search_value]);
						$query->orWhereRaw($searchColumns[$i] . ' like ? ', ["%" . $search_value . "%"]);
					}
				}
			});
		}
		$data = $query->get();
		$data = json_decode(json_encode($data), true);
		// if ($isFilterApply == 1) {
		// 	$recordsFiltered = count($data);
		// }

		$viewData = array();
		$ArchitectCategory = ArchitectCategory::orderBy('id', 'asc')->get();

		foreach ($data as $key => $value) {

			$valueCreatedTime = convertDateTime($value['created_at']);

			$UserUpdateCount = UserUpdate::where('for_user_id', $value['id'])->orderBy('id', 'desc')->count();


			$viewData[$key] = array();
			$viewData[$key]['id'] = '<div class="avatar-xs"><span class="avatar-title rounded-circle">' . $value['id'] . '</span></div>';






			$uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';
			$uiAction .= '<li class="list-inline-item px-2">';
			if ($UserUpdateCount == 0) {
				$uiAction .= '<button type="button" class="btn btn-sm user-comments-icon" onclick="getDetail(' . $value['id'] . ')"  ><i class="fas fa-comments inquiry-comments-icon"></i><span class="position-absolute top-0 start-100 translate-middle badge rounded-pill user-update-badge "></button>';
			} else {

				$uiAction .= '<button type="button" class="btn btn-sm position-relative user-comments-icon " onclick="getDetail(' . $value['id'] . ')"  ><i class="fas fa-comments inquiry-comments-icon"></i><span class="position-absolute top-0 start-100 translate-middle badge rounded-pill user-update-badge ">' . $UserUpdateCount . '<span class="visually-hidden">unread messages</span>
					</span></button>';
			}



			$uiAction .= '</li>';
			$uiAction .= '</ul>';
			if(Auth::user()->type == 6)
			{
				$viewData[$key]['name'] = '<span href="javascript: void(0);" class="">' . $value['first_name'] . " " . $value['last_name'] . '</span>
				<p class="text-muted mb-0">' . getUserTypeName($value['type']) . '</p>' . $uiAction;
			}
			else
			{
				$viewData[$key]['name'] = '<a onclick="inquiryLogs(' . $value['id'] . ')" href="javascript: void(0);" class="">' . $value['first_name'] . " " . $value['last_name'] . '</a>
				<p class="text-muted mb-0">' . getUserTypeName($value['type']) . '</p>' . $uiAction;
			}
			




			$viewData[$key]['total_point_current'] = '<a onclick="pointLogs(' . $value['id'] . ')" href="javascript: void(0);" title="Detail">' . $value['total_point_current'] . '</a>';
			$viewData[$key]['total_point'] = '<a onclick="pointLogs(' . $value['id'] . ')" href="javascript: void(0);" title="Detail">' . $value['total_point'] . '</a>';

			$viewData[$key]['principal_architect_name'] = '<p class="text-muted mb-0">' . $value['principal_architect_name'] . '</p>';

			$viewData[$key]['sale_person'] = '<p class="text-muted mb-0">' . $value['sale_person_first_name'] . ' ' . $value['sale_person_last_name'] . '</p>';

			$viewData[$key]['created_by'] = '<p class="text-muted mb-0">' . $value['created_by_user_first_name'] . ' ' . $value['created_by_user_last_name'] . '</p>';

			// if ($value['created_at'] == $value['last_active_date_time']) {
			// 	$value['last_active_date_time'] = "-";
			// 	$value['last_login_date_time'] = "-";
			// } else {
			// 	$value['last_active_date_time'] = convertDateTime($value['last_active_date_time']);
			// 	$value['last_login_date_time'] = convertDateTime($value['last_login_date_time']);

			// }

			// $viewData[$key]['active_login'] = '<p class="text-muted mb-0">' . $value['last_active_date_time'] . '</p>
			//           <p class="text-muted mb-0">' . $value['last_login_date_time'] . '</p>';

			$viewData[$key]['status'] = getUserStatusLable($value['status']);
			if ($value['type'] == 202) {

				$viewData[$key]['email'] = '<p class="text-muted mb-0">' . $value['email'] . ' <span class="badge rounded-pill bg-success">PRIME</span></p>
             <p class="text-muted mb-0">' . $value['phone_number'] . '</p>';
			} else {

				$viewData[$key]['email'] = '
             <p class="text-muted mb-0">' . $value['phone_number'] . '</p>';
			}



			$ArchitectCategoryUI = '<select class="select-category" id="select_' . $value['id'] . '" >';
			$ArchitectCategoryUI .= '<option value="0">-SELECT-</option>';
			foreach ($ArchitectCategory as $keyC => $valueC) {

				if ($valueC['id'] == $value['category_id']) {

					$ArchitectCategoryUI .= '<option selected value="' . $valueC['id'] . '">' . $valueC['name'] . '</option>';
				} else {

					$ArchitectCategoryUI .= '<option value="' . $valueC['id'] . '">' . $valueC['name'] . '</option>';
				}
			}
			$ArchitectCategoryUI .= '<select>';

			$viewData[$key]['category'] = $ArchitectCategoryUI;


			$uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';

			$hasDataVerified = 0;

			if ($value['data_verified'] == 1) {

				$uiAction .= '<li class="list-inline-item px-2 ">';
				$uiAction .= '<a data-bs-toggle="tooltip"  data-bs-original-title="Data Verified"  href="javascript: void(0);" title="Data Verified" class=" "><i class="badge-soft-warning bx bx-check-circle"></i></a>';
				$uiAction .= '</li>';
				$hasDataVerified = 1;
			}

			if ($value['data_not_verified'] == 1) {

				$uiAction .= '<li class="list-inline-item px-2 ">';
				$uiAction .= '<a data-bs-toggle="tooltip"  data-bs-original-title="Data Not Verified"  href="javascript: void(0);" title="Data Not Verified" class=" "><i class="badge-soft-danger bx bx-x-circle"></i></a>';
				$uiAction .= '</li>';
				$hasDataVerified = 1;
			}

			if ($hasDataVerified == 0) {

				$uiAction .= '<li class="list-inline-item px-2 ">';
				$uiAction .= '<a data-bs-toggle="tooltip"    href="javascript: void(0);" class=" ">&nbsp;&nbsp;&nbsp;&nbsp;</a>';
				$uiAction .= '</li>';
			}

			if ($value['missing_data'] == 1) {

				$uiAction .= '<li class="list-inline-item px-2 ">';
				$uiAction .= '<a data-bs-toggle="tooltip"  data-bs-original-title="Missing Data"  href="javascript: void(0);" title="Missing Data" class="badge-soft-default "><i class="bx bx-question-mark"></i></a>';
				$uiAction .= '</li>';
			} else {

				$uiAction .= '<li class="list-inline-item px-2 ">';
				$uiAction .= '<a data-bs-toggle="tooltip"    href="javascript: void(0);" class=" ">&nbsp;&nbsp;&nbsp;&nbsp;</a>';
				$uiAction .= '</li>';
			}

			$hasTeleData = 0;

			if ($value['tele_verified'] == 1) {

				$uiAction .= '<li class="list-inline-item px-2">';
				$uiAction .= '<a data-bs-toggle="tooltip"  data-bs-original-title="Tele Verified" href="javascript: void(0);" title="Tele Verified"><i class="badge-soft-success  mdi mdi-cellphone-iphone"></i></a>';
				$uiAction .= '</li>';
				$hasTeleData = 1;
			}

			if ($value['tele_not_verified'] == 1) {

				$uiAction .= '<li class="list-inline-item px-2">';
				$uiAction .= '<a data-bs-toggle="tooltip"  data-bs-original-title="Tele Not Verified" href="javascript: void(0);" title="Tele Not Verified"><i class="badge-soft-danger mdi mdi-cellphone-off"></i></a>';
				$uiAction .= '</li>';
				$hasTeleData = 1;
			}

			if ($hasTeleData == 0) {

				$uiAction .= '<li class="list-inline-item px-2 ">';
				$uiAction .= '<a data-bs-toggle="tooltip"    href="javascript: void(0);" class=" ">&nbsp;&nbsp;&nbsp;&nbsp;</a>';
				$uiAction .= '</li>';
			}

			if ($value['instagram_link'] != "") {

				$uiAction .= '<li class="list-inline-item px-2">';
				$uiAction .= '<a data-bs-toggle="tooltip"  data-bs-original-title="Instagram Link" href="' . $value['instagram_link'] . '" target="_blank" title="Instagram"><i class="bx bxl-instagram"></i></a>';
				$uiAction .= '</li>';
			} else {
				$uiAction .= '<li class="list-inline-item px-2 ">';
				$uiAction .= '<a data-bs-toggle="tooltip"    href="javascript: void(0);" class=" ">&nbsp;&nbsp;&nbsp;&nbsp;</a>';
				$uiAction .= '</li>';
			}

			if ($viewMode == 0) {

				if ($isSalePerson == 1) {

					$uiAction .= '<li class="list-inline-item px-2">';
					$uiAction .= '<a data-bs-toggle="tooltip" href="javascript: void(0);" title="Created Date & Time : ' . $valueCreatedTime . '"><i class="bx bx-calendar"></i></a>';
					$uiAction .= '<li class="list-inline-item px-2">';

					if ($value['type'] == 201) {

						$uiAction .= '<a onclick="editView(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Edit"><i class="bx bx-edit-alt"></i></a>';
					} else {

						$uiAction .= '<a onclick="editView(\'' . $value['id'] . '\')" href="javascript: void(0);" title="View"><i class="mdi mdi-eye"></i></a>';
					}


					$uiAction .= '</li>';
				} else {

					$uiAction .= '<li class="list-inline-item px-2">';
					$uiAction .= '<a data-bs-toggle="tooltip" href="javascript: void(0);" title="Created Date & Time : ' . $valueCreatedTime . '"><i class="bx bx-calendar"></i></a>';
					$uiAction .= '<li class="list-inline-item px-2">';
					$uiAction .= '<a onclick="editView(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Edit"><i class="bx bx-edit-alt"></i></a>';
					$uiAction .= '</li>';
				}
			} else {

				$uiAction .= '<li class="list-inline-item px-2">';
				$uiAction .= '<a data-bs-toggle="tooltip" href="javascript: void(0);" title="Created Date & Time : ' . $valueCreatedTime . '"><i class="bx bx-calendar"></i></a>';

				$uiAction .= '<li class="list-inline-item px-2">';
				$uiAction .= '<a onclick="editView(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Edit"><i class="mdi mdi-eye"></i></a>';
				$uiAction .= '</li>';
			}

			$uiAction .= '</ul>';
			$viewData[$key]['action'] = $uiAction;
		}

		$jsonData = array(
			"draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
			"recordsTotal" => intval($recordsTotal), // total number of records
			"recordsFiltered" => intval($recordsFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
			"data" => $viewData, // total data array

		);
		return $jsonData;
	}

	public function searchSalePerson(Request $request)
	{

		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		$isSalePerson = isSalePerson();
		$isChannelPartner = isChannelPartner(Auth::user()->type);
		$isMarketingDispatcherUser = isMarketingDispatcherUser();

		$searchKeyword = isset($request->q) ? $request->q : "";

		if ($isAdminOrCompanyAdmin == 1 || $isSalePerson == 1 || $isMarketingDispatcherUser == 1) {

			$SalePerson = SalePerson::query();
			$SalePerson->leftJoin('users', 'sale_person.user_id', '=', 'users.id');
			$SalePerson->select('users.id as id', DB::raw('CONCAT(first_name," ", last_name) AS text'));
			$SalePerson->where('users.status', 1);
			$SalePerson->where(function ($query) use ($searchKeyword) {
				$query->where('users.first_name', 'like', '%' . $searchKeyword . '%');
				$query->orWhere('users.last_name', 'like', '%' . $searchKeyword . '%');
			});
			$SalePerson->limit(5);
			$SalePerson = $SalePerson->get();
			$response = array();
			$response['results'] = $SalePerson;
			$response['pagination']['more'] = false;
		} else if ($isChannelPartner != 0) {

			$salesPersonIds = getChannelPartnerSalesPersonsIds(Auth::user()->id);

			$SalePerson = SalePerson::query();
			$SalePerson->leftJoin('users', 'sale_person.user_id', '=', 'users.id');
			$SalePerson->select('users.id as id', DB::raw('CONCAT(first_name," ", last_name) AS text'));
			$SalePerson->where('users.status', 1);
			$SalePerson->whereIn('users.id', $salesPersonIds);

			$SalePerson->where(function ($query) use ($searchKeyword) {
				$query->where('users.first_name', 'like', '%' . $searchKeyword . '%');
				$query->orWhere('users.last_name', 'like', '%' . $searchKeyword . '%');
			});
			$SalePerson->limit(5);
			$SalePerson = $SalePerson->get();

			$response = array();
			$response['results'] = $SalePerson;
			$response['pagination']['more'] = false;
		}

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function save(Request $request)
	{

		$user_id = isset($request->user_id) ? $request->user_id : 0;

		$isSalePerson = isSalePerson();
		$isChannelPartner = isChannelPartner(Auth::user()->type);
		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		$isMarketingDispatcherUser = isMarketingDispatcherUser();

		$rules = array();
		$rules['user_type'] = 'required';
		$rules['user_first_name'] = 'required';
		$rules['user_last_name'] = 'required';
		$rules['user_phone_number'] = 'required|digits:10|regex:/^[1-9][0-9]*$/';
		$rules['user_country_id'] = 'required';
		$rules['user_state_id'] = 'required';
		$rules['user_city_id'] = 'required';

		$rules['architect_firm_name'] = 'required';
		if ($isSalePerson == 0) {
			$rules['user_status'] = 'required';
			$rules['architect_sale_person_id'] = 'required';
		}


		if ($user_id != 0 && $isAdminOrCompanyAdmin == 0) {
			$User = User::find($request->user_id);
			if ($User) {
				if ($User->type == 202 && $request->user_type == 201) {
					$response = errorRes("user can't convert prime to non-prime please contact to admin !");
					return response()->json($response)->header('Content-Type', 'application/json');
				}
			}
		}

		if ($request->user_type == 202) {

			$rules['user_email'] = 'required|email:rfc,dns';
			$rules['user_address_line1'] = 'required';
			$rules['user_pincode'] = 'required';

			// $rules['architect_practicing'] = 'required';
			// $rules['architect_brand_using_for_switch'] = 'required';
			// $rules['architect_brand_used_before_home_automation'] = 'required';
			// $rules['architect_whitelion_smart_switches_before'] = 'required';

			if ($user_id == 0) {
				//$rules['architect_visiting_card'] = 'required';
			} else {
				$Architect = Architect::select('visiting_card')->where('user_id', $user_id)->first();
				if ($Architect && $Architect->visiting_card == "") {
					//$rules['architect_visiting_card'] = 'required';
				}
			}
		}

		if ($isChannelPartner == 0  && isMarketingUser() == 0) {
			$rules['architect_source_type'] = 'required';
		}
		if ($isSalePerson == 1 && $request->user_type == 202 && ($request->user_id != 0 && $request->user_id != "")) {
			$rules = array();
			$rules['user_id'] = 'required';
			$rules['user_type'] = 'required';
			$User = User::find($request->user_id);
			$requireFieldForSalesUser = array();


			if ($User) {

				if ($User->pincode == "") {
					$rules['user_pincode'] = 'required';
					$requireFieldForSalesUser[] = "user_pincode";
				}


				if ($User->address_line1 == "") {
					$rules['user_address_line1'] = 'required';
					$requireFieldForSalesUser[] = "user_address_line1";
				}

				if ($User->email == "") {
					$rules['user_email'] = 'required|email:rfc,dns';
					$requireFieldForSalesUser[] = "user_email";
				}
			}
		}

		$customMessage = array();
		$customMessage['user_id.required'] = "Invalid parameters";
		$customMessage['user_type.required'] = "Invalid type";
		$customMessage['user_first_name.required'] = 'Please enter first name';
		$customMessage['user_last_name.required'] = 'Please enter last name';
		$customMessage['user_address_line1.required'] = 'Please enter address_line1';
		$customMessage['user_pincode.required'] = 'Please enter pincode';
		$customMessage['user_country_id.required'] = 'Please select country';
		$customMessage['user_state_id.required'] = 'Please select state';
		$customMessage['architect_source_type.required'] = 'Please select source type';
		$customMessage['architect_visiting_card.required'] = 'Please attach visiting card';
		$customMessage['architect_sale_person_id.required'] = 'Please select sale person';
		$customMessage['architect_practicing.required'] = 'Please select How long have you been practicing?';
		$customMessage['architect_brand_using_for_switch.required'] = 'Please select How long have you been practicing?';
		$customMessage['architect_brand_used_before_home_automation.required'] = 'Please enter Which brand have you used before for Home Automation ?';
		$customMessage['architect_whitelion_smart_switches_before.required'] = 'Please enter Which brand are you using for switches ?';

		$validator = Validator::make($request->all(), $rules, $customMessage);

		if ($validator->fails()) {

			$response = array();
			$response['status'] = 0;
			$response['msg'] = "The request could not be understood by the server due to malformed syntax";
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();
		} else {

			$AllUserTypes = getAllUserTypes();


			if ($isSalePerson == 1 && $request->user_type == 202 && ($request->user_id != 0 && $request->user_id != "")) {

				$converted_prime2 = 1;
				if (count($requireFieldForSalesUser) > 0) {

					if (in_array('user_email', $requireFieldForSalesUser)) {

						$alreadyEmail = User::query();
						$alreadyEmail->where('email', $request->user_email);
						$alreadyEmail->where('id', '!=', $request->user_id);
						$alreadyEmail->where('status', '=', 1);
						$alreadyEmail = $alreadyEmail->first();


						if ($alreadyEmail) {

							$response = errorRes("Email already exists(" . $AllUserTypes[$alreadyEmail->type]['name'] . "), Try with another email");
							return response()->json($response)->header('Content-Type', 'application/json');
						} else {
							$User->email = $request->user_email;
						}
					}


					if (in_array('user_address_line1', $requireFieldForSalesUser)) {

						$User->address_line1 = $request->user_address_line1;
					}
					if (in_array('user_pincode', $requireFieldForSalesUser)) {

						$User->pincode = $request->user_pincode;
					}

					$User->type = $request->user_type;
					$User->save();
					$Architect = Architect::find($User->reference_id);
					
					if ($request->user_id != 0 && $request->user_type == 202 && $Architect->converted_prime == 0) {
						$Architect->prime_nonprime_date = date('Y-m-d H:i:s');
						$converted_prime2 = 0;
					} else {
						$converted_prime2 = 1;
					}
					if ($request->user_type == 202) {
						$Architect->converted_prime = 1;
					}
					$Architect->type = $request->user_type;
					$Architect->save();
					$debugLog['name'] = "architect-edit";
					$debugLog['description'] = "user #" . $User->id . "(" . $User->first_name . " " . $User->last_name . ") has been updated ";
					$response = successRes("Successfully saved user");
					saveDebugLog($debugLog);
				} else {
					$User->type = $request->user_type;
					$User->save();
					$Architect = Architect::find($User->reference_id);
					if ($request->user_id != 0 && $request->user_type == 202 && $Architect->converted_prime == 0) {
						$Architect->prime_nonprime_date = date('Y-m-d H:i:s');
						$converted_prime2 = 0;
					} else {
						$converted_prime2 = 1;
					}
					if ($request->user_type == 202) {
						$Architect->converted_prime = 1;
					}
					$Architect->type = $request->user_type;
					$Architect->save();
					$debugLog['name'] = "architect-edit";
					$debugLog['description'] = "user #" . $User->id . "(" . $User->first_name . " " . $User->last_name . ") has been updated ";
					$response = successRes("Successfully saved user");
					saveDebugLog($debugLog);
				}


				if (($request->user_id == 0 && $request->user_type == 202) || ($request->user_id != 0 && $request->user_type == 202 && $converted_prime2 == 0)){
					$pointValue = 50;
					$Architect->total_point = $Architect->total_point + $pointValue;
					$Architect->total_point_current = $Architect->total_point_current + $pointValue;
					$Architect->save();

					$debugLog = array();
					$debugLog['for_user_id'] = $Architect->user_id;
					$debugLog['name'] = "point-gain";
					$debugLog['points'] = $pointValue;
					$debugLog['description'] = $pointValue . " Point gained joining bonus ";
					$debugLog['type'] = '';
					saveCRMUserLog($debugLog);

					$configrationForNotify = configrationForNotify();

					$params = array();
					$params['from_name'] = $configrationForNotify['from_name'];
					$params['from_email'] = $configrationForNotify['from_email'];
					$params['to_email'] = $User->email;
					$params['to_name'] = $configrationForNotify['to_name'];
					$params['bcc_email'] = array("sales@whitelion.in", "sc@whitelion.in", "Poonam@whitelion.in");
					$params['subject'] = "Welcome to the Whitelion";
					$params['user_first_name'] = $User->first_name;
					$params['user_last_name'] = $User->last_name;
					$params['user_mobile'] = $User->phone_number;
					$params['credentials_email'] = $User->email;
					$params['credentials_password'] = "111111";
					$query = CRMHelpDocument::query();
					$query->where('status', 1);
					$query->where('type', 202);
					$query->limit(30);
					$query->orderBy('publish_date_time', "desc");
					$helpDocuments = $query->get();
					$params['help_documents'] = json_decode(json_encode($helpDocuments), true);

					if (Config::get('app.env') == "local") { // SEND MAIL
						$params['to_email'] = $configrationForNotify['test_email'];
						$params['bcc_email'] = $configrationForNotify['test_email_bcc'];
					}

					//TEMPLATE 6
					Mail::send('emails.signup_architect', ['params' => $params], function ($m) use ($params) {

						$m->from($params['from_email'], $params['from_name']);
						$m->bcc($params['bcc_email']);
						$m->to($params['to_email'], $params['to_name'])->subject($params['subject']);

						foreach ($params['help_documents'] as $helpDocument) {

							$fileName = "SmartClubRewardProgram";
							// preg_replace("![^a-z0-9]+!i", "-", $helpDocument['title']);
							$fileExtension = explode(".", $helpDocument['file_name']);
							$fileExtension = end($fileExtension);
							$fileName = $fileName . "." . $fileExtension;

							if (is_file('/s/crm-help-document/' . $helpDocument['file_name'])) {
								$m->attach(public_path('/s/crm-help-document/' . $helpDocument['file_name']), array(
									'as' => $fileName
								));
							}
						}
					});

					$helpDocument = CRMHelpDocument::query();
					$helpDocument->where('status', 1);
					$helpDocument->where('type', 202);
					$helpDocument->orderBy('publish_date_time', "desc");
					$helpDocument = $helpDocument->first();
					//TEMPLATE 7
					$whatsapp_controller = new WhatsappApiContoller;
					$perameater_request = new Request();
					$perameater_request['q_whatsapp_massage_mobileno'] = $User->phone_number;
					$perameater_request['q_whatsapp_massage_template'] = 'for_add_new_architect';
					$perameater_request['q_whatsapp_massage_attechment'] = $helpDocument ? getSpaceFilePath($helpDocument->file_name) : '';
					$perameater_request['q_broadcast_name'] = $User->first_name . ' ' . $User->last_name;
					$perameater_request['q_whatsapp_massage_parameters'] = array();

					$wp_response = $whatsapp_controller->sendTemplateMessage($perameater_request);
					$response["whatsapp"] = $wp_response;
				}
				
				return response()->json($response)->header('Content-Type', 'application/json');
			}


			$source_type_value = "";
			$principal_architect_name = isset($request->architect_principal_architect_name) ? $request->architect_principal_architect_name : '';
			$instagram_link = isset($request->architect_instagram_link) ? $request->architect_instagram_link : '';
			$data_verified = isset($request->architect_data_verified) ? $request->architect_data_verified : 0;
			$data_not_verified = isset($request->architect_data_not_verified) ? $request->architect_data_not_verified : 0;
			$missing_data = isset($request->architect_missing_data) ? $request->architect_missing_data : 0;
			$tele_verified = isset($request->architect_tele_verified) ? $request->architect_tele_verified : 0;
			$tele_not_verified = isset($request->architect_tele_not_verified) ? $request->architect_tele_not_verified : 0;

			if ($isChannelPartner == 0) {

				$source_type = $request->architect_source_type;
				$source_type_pieces = explode("-", $source_type);

				if ($source_type_pieces[0] == "user") {

					if (!isset($request->architect_source_user) || $request->architect_source_user == "") {
						$response = errorRes("Please select source");
						return response()->json($response)->header('Content-Type', 'application/json');
					}

					$source_type_value = $request->architect_source_user;
				} else if ($source_type_pieces[0] == "textrequired") {
					if (!isset($request->architect_source_text) || $request->architect_source_text == "") {
						$response = errorRes("Please enter source text");
						return response()->json($response)->header('Content-Type', 'application/json');
					}

					$source_type_value = $request->architect_source_text;
				} else {
					$source_type_value = isset($request->architect_source_text) ? $request->architect_source_text : '';
				}
			} else if ($isChannelPartner != 0) {
				$source_type_value = "";
				$source_type = "";
			}

			if ($isSalePerson == 1) {
				$sale_person_id = Auth::user()->id;
			} else {
				$sale_person_id = $request->architect_sale_person_id;
			}

			if ($request->user_type == 201) {
				$email = time() . "@whitelion.in";
			} else {
				$email = $request->user_email;
			}

			$phone_number = $request->user_phone_number;

			$alreadyEmail = User::query();
			$alreadyEmail->where('email', $request->user_email);
			$alreadyEmail->where('type', '!=', 10000);
			$alreadyEmail->where('status', '=', 1);

			if ($request->user_id != 0) {
				$alreadyEmail->where('id', '!=', $request->user_id);
			}
			$alreadyEmail = $alreadyEmail->first();

			$alreadyPhoneNumber = User::query();
			$alreadyPhoneNumber->where('phone_number', $request->user_phone_number);
			$alreadyPhoneNumber->where('type', '!=', 10000);
			$alreadyPhoneNumber->where('status', '=', 1);

			if ($request->user_id != 0) {
				$alreadyPhoneNumber->where('id', '!=', $request->user_id);
			}
			$alreadyPhoneNumber = $alreadyPhoneNumber->first();



			if ($alreadyEmail) {

				$response = errorRes("Email already exists(" . $AllUserTypes[$alreadyEmail->type]['name'] . "), Try with another email");
			} else if ($alreadyPhoneNumber) {

				$response = errorRes("Phone number already exists(" . $AllUserTypes[$alreadyPhoneNumber->type]['name'] . "), Try with another phone number");
			} else {



				$user_address_line1 = isset($request->user_address_line1) ? $request->user_address_line1 : '';
				$user_address_line2 = isset($request->user_address_line2) ? $request->user_address_line2 : '';
				$user_pincode = isset($request->user_pincode) ? $request->user_pincode : '';

				$is_residential = isset($request->architect_is_residential) ? $request->architect_is_residential : 0;
				$is_commercial_or_office_space = isset($request->architect_is_commercial_or_office_space) ? $request->architect_is_commercial_or_office_space : 0;
				$interior = isset($request->architect_interior) ? $request->architect_interior : 0;
				$exterior = isset($request->architect_exterior) ? $request->architect_exterior : 0;
				$structural_design = isset($request->architect_structural_design) ? $request->architect_structural_design : 0;
				$practicing = isset($request->architect_practicing) ? $request->architect_practicing : 0;
				$brand_using_for_switch = isset($request->architect_brand_using_for_switch) ? $request->architect_brand_using_for_switch : "";

				$whitelion_smart_switches_before = isset($request->architect_whitelion_smart_switches_before) ? $request->architect_whitelion_smart_switches_before : 0;
				$how_many_projects_used_whitelion_smart_switches = isset($request->architect_how_many_projects_used_whitelion_smart_switches) ? $request->architect_how_many_projects_used_whitelion_smart_switches : '';

				$experience_with_whitelion = isset($request->architect_experience_with_whitelion) ? $request->architect_experience_with_whitelion : 0;

				$brand_used_before_home_automation = isset($request->architect_brand_used_before_home_automation) ? $request->architect_brand_used_before_home_automation : '';

				$suggestion = isset($request->architect_suggestion) ? $request->architect_suggestion : '';

				$uploadedFile1 = "";
				$uploadedFile2 = "";

				if ($request->hasFile('architect_visiting_card')) {

					$folderPathofFile = '/s/architect';

					$fileObject1 = $request->file('architect_visiting_card');
					$extension = $fileObject1->getClientOriginalExtension();

					$fileName1 = time() . mt_rand(10000, 99999) . '.' . $extension;

					$destinationPath = public_path($folderPathofFile);

					$fileObject1->move($destinationPath, $fileName1);

					if (File::exists(public_path($folderPathofFile . "/" . $fileName1))) {

						$uploadedFile1 = $folderPathofFile . "/" . $fileName1;
						//START UPLOAD FILE ON SPACES

						$spaceUploadResponse = uploadFileOnSpaces(public_path($uploadedFile1), $uploadedFile1);
						if ($spaceUploadResponse != 1) {
							$uploadedFile1 = "";
						} else {
							unlink(public_path($uploadedFile1));
						}
						//END UPLOAD FILE ON SPACES

					}
				}

				if ($request->hasFile('architect_aadhar_card')) {

					$folderPathofFile = '/s/architect';
					$fileObject2 = $request->file('architect_aadhar_card');
					$extension = $fileObject2->getClientOriginalExtension();

					$fileName2 = time() . mt_rand(10000, 99999) . '.' . $extension;

					$destinationPath = public_path($folderPathofFile);

					$fileObject2->move($destinationPath, $fileName2);

					if (File::exists(public_path($folderPathofFile . "/" . $fileName2))) {

						$uploadedFile2 = $folderPathofFile . "/" . $fileName2;
						//START UPLOAD FILE ON SPACES
						$spaceUploadResponse = uploadFileOnSpaces(public_path($uploadedFile2), $uploadedFile2);
						if ($spaceUploadResponse != 1) {
							$uploadedFile2 = "";
						} else {

							unlink(public_path($uploadedFile2));
						}
						//END UPLOAD FILE ON SPACES

					}
				}

				$user_company_id = 1;
				$isMovedFromPrimeToNonPrime = 0;

				if ($request->user_id == 0) {

					$User = User::where('type', 10000)->where(function ($query) use ($request) {
						$query->where('email', $request->user_email)->orWhere('phone_number', $request->user_phone_number);
					})->first();

					if ($User) {

						$User->type = $request->user_type;
						$User->reference_type = "architect";
						$User->reference_id = 0;
					} else {
						$User = new User();
						$User->created_by = Auth::user()->id;
						$User->password = Hash::make("111111");
						$User->last_active_date_time = date('Y-m-d H:i:s');
						$User->last_login_date_time = date('Y-m-d H:i:s');
						$User->avatar = "default.png";
					}
					$Architect = new Architect();
					if ($isSalePerson == 1) {
						$User->status = 1;
					} else {
						$User->status = $request->user_status;
					}

					$isMovedFromPrimeToNonPrime = 1;


					//$User->is_sent_mail = 0;
				} else {


					$User = User::find($request->user_id);
					$Architect = Architect::find($User->reference_id);
					if (!$Architect) {

						$response = errorRes("Something went wrong");
					}


					if ($uploadedFile1 == "") {
						$uploadedFile1 = $Architect->visiting_card;
					}
					if ($uploadedFile2 == "") {
						$uploadedFile2 = $Architect->aadhar_card;
					}

					if ($isSalePerson == 0) {

						$User->status = $request->user_status;
					}

					if ($Architect->type != $request->user_type) {

						$isMovedFromPrimeToNonPrime = 1;
					}
				}
				$User->first_name = $request->user_first_name;
				$User->last_name = $request->user_last_name;
				$User->email = $email;
				$User->dialing_code = "+91";
				$User->phone_number = $request->user_phone_number;
				$User->ctc = 0;
				if ($request->user_type == 202 || $request->user_id == 0) {
					$PrivilegeJSON = array();
					$PrivilegeJSON['dashboard'] = 1;
					$User->privilege = json_encode($PrivilegeJSON);
				}
				$User->address_line1 = $user_address_line1;
				$User->address_line2 = $user_address_line2;
				$User->pincode = $user_pincode;
				$User->country_id = $request->user_country_id;
				$User->state_id = $request->user_state_id;
				$User->city_id = $request->user_city_id;
				$User->company_id = $user_company_id;
				$User->type = $request->user_type;
				$User->reference_type = 0;
				$User->reference_id = 0;
				$User->save();
				if ($isMovedFromPrimeToNonPrime == 1) {
					$Architect->prime_nonprime_date = date('Y-m-d H:i:s');
				}
				$Architect->user_id = $User->id;
				$Architect->type = $request->user_type;
				$Architect->firm_name = $request->architect_firm_name;
				$Architect->sale_person_id = $sale_person_id;
				$Architect->is_residential = (int) $is_residential;
				$Architect->is_commercial_or_office_space = (int) $is_commercial_or_office_space;
				$Architect->interior = (int) $interior;
				$Architect->exterior = (int) $exterior;
				$Architect->structural_design = (int) $structural_design;
				$Architect->practicing = (int) $practicing;
				$Architect->brand_using_for_switch = $brand_using_for_switch;
				$Architect->visiting_card = $uploadedFile1;
				$Architect->aadhar_card = $uploadedFile2;
				// $Architect->whitelion_smart_switches_before = $whitelion_smart_switches_before;
				// $Architect->how_many_projects_used_whitelion_smart_switches = $how_many_projects_used_whitelion_smart_switches;
				// $Architect->brand_used_before_home_automation = $brand_used_before_home_automation;
				// $Architect->experience_with_whitelion = $experience_with_whitelion;
				// $Architect->suggestion = $suggestion;
				$Architect->principal_architect_name = $principal_architect_name;
				$Architect->data_verified = $data_verified;
				$Architect->tele_verified = $tele_verified;
				$Architect->tele_not_verified = $tele_not_verified;
				$Architect->data_not_verified = $data_not_verified;
				$Architect->missing_data = $missing_data;
				$Architect->instagram_link = $instagram_link;

				if ($request->architect_birth_date != "") {

					$Architect->birth_date = $request->architect_birth_date;
				} else {
					$Architect->birth_date = null;
				}
				if ($request->architect_anniversary_date != "") {

					$Architect->anniversary_date = $request->architect_anniversary_date;
				} else {
					$Architect->anniversary_date = null;
				}
				$response2 = array();
				$response2 = $request->user_id . " - " . $request->user_type . " - " . $Architect->converted_prime;
				if ($request->user_id != 0 && $request->user_type == 202 && $Architect->converted_prime == 0) {
					$converted_prime = 0;
				} else {
					$converted_prime = 1;
				}

				if ($request->user_type == 202) {
					$Architect->converted_prime = 1;
				}

				if(isMarketingUser() == 0)
				{
					$Architect->source_type = $source_type;
					$Architect->source_type_value = $source_type_value;
				}
				$Architect->added_by = Auth::user()->id;
				$Architect->save();
				$User->reference_type = "architect";
				$User->reference_id = $Architect->id;
				$User->save();
				$debugLog = array();
				if ($request->user_id != 0) {

					$debugLog['name'] = "architect-edit";
					$debugLog['description'] = "user #" . $User->id . "(" . $User->first_name . " " . $User->last_name . ") has been updated ";
					$response = successRes("Successfully saved user");
					$response['cov'] = $converted_prime;
					$response['cov2'] = $response2;
				} else {

					$mobileNotificationTitle = "New Architect Create";
					$mobileNotificationMessage = "New Architect " . $User->first_name . " " . $User->last_name . " Added By " . Auth::user()->first_name . " " . Auth::user()->last_name;;
					$notificationUserids = getParentSalePersonsIds($Architect->sale_person_id);
					$notificationUserids[] = $Architect->sale_person_id;
					$UsersNotificationTokens = UsersNotificationTokens($notificationUserids);
					sendNotificationTOAndroid($mobileNotificationTitle, $mobileNotificationMessage, $UsersNotificationTokens,'Architect',$User);


					$debugLog['name'] = "architect-add";
					$debugLog['description'] = "user #" . $User->id . "(" . $User->first_name . " " . $User->last_name . ") has been added ";
					$response = successRes("Successfully added user");
					$response['cov'] = $converted_prime;
					$response['cov2'] = $response2;
				}
				saveDebugLog($debugLog);

				if (($request->user_id == 0 && $request->user_type == 202) || ($request->user_id != 0 && $request->user_type == 202 && $converted_prime == 0)) {
					// ARCHITECT JOINING BONUS
					$pointValue = 50;
					$Architect->total_point = $Architect->total_point + $pointValue;
					$Architect->total_point_current = $Architect->total_point_current + $pointValue;
					$Architect->save();

					$debugLog = array();
					$debugLog['for_user_id'] = $Architect->user_id;
					$debugLog['name'] = "point-gain";
					$debugLog['points'] = $pointValue;
					$debugLog['description'] = $pointValue . " Point gained joining bonus ";
					$debugLog['type'] = '';
					saveCRMUserLog($debugLog);

					$configrationForNotify = configrationForNotify();

					$params = array();
					$params['from_name'] = $configrationForNotify['from_name'];
					$params['from_email'] = $configrationForNotify['from_email'];
					$params['to_email'] = $User->email;
					$params['to_name'] = $configrationForNotify['to_name'];
					$params['bcc_email'] = array("sales@whitelion.in", "sc@whitelion.in", "poonam@whitelion.in");
					$params['subject'] = "Welcome to the Whitelion";
					$params['user_first_name'] = $User->first_name;
					$params['user_last_name'] = $User->last_name;
					$params['user_mobile'] = $User->phone_number;
					$params['credentials_email'] = $User->email;
					$params['credentials_password'] = "111111";
					$query = CRMHelpDocument::query();
					$query->where('status', 1);
					$query->where('type', 202);
					$query->limit(30);
					$query->orderBy('publish_date_time', "desc");
					$helpDocuments = $query->get();
					$params['help_documents'] = json_decode(json_encode($helpDocuments), true);

					if (Config::get('app.env') == "local") { // SEND MAIL
						$params['to_email'] = $configrationForNotify['test_email'];
						$params['bcc_email'] = $configrationForNotify['test_email_bcc'];
					}

					//TEMPLATE 6
					Mail::send('emails.signup_architect', ['params' => $params], function ($m) use ($params) {

						$m->from($params['from_email'], $params['from_name']);
						$m->bcc($params['bcc_email']);
						$m->to($params['to_email'], $params['to_name'])->subject($params['subject']);

						foreach ($params['help_documents'] as $new_helpDocument) {

							$fileName = preg_replace("![^a-z0-9]+!i", "-", $new_helpDocument['file_name']);
							$fileExtension = explode(".", $new_helpDocument['file_name']);
							$fileExtension = end($fileExtension);
							$fileName = $fileName . "." . $fileExtension;

							// if (is_file($new_helpDocument['title'])) {
								$m->attach(getSpaceFilePath($new_helpDocument['file_name']), array(
									'as' => $fileName
								));
							// }
						}
					});

					$helpDocument = CRMHelpDocument::query();
					$helpDocument->where('status', 1);
					$helpDocument->where('type', 202);
					$helpDocument->orderBy('publish_date_time', "desc");
					$helpDocument = $helpDocument->first();
					//TEMPLATE 7
					$whatsapp_controller = new WhatsappApiContoller;
					$perameater_request = new Request();
					$perameater_request['q_whatsapp_massage_mobileno'] = $User->phone_number;
					$perameater_request['q_whatsapp_massage_template'] = 'for_architect_download_mobileapp';
					$perameater_request['q_whatsapp_massage_attechment'] = $helpDocument ? getSpaceFilePath($helpDocument->file_name) : '';
					$perameater_request['q_broadcast_name'] = $User->first_name . ' ' . $User->last_name;
					$perameater_request['q_whatsapp_massage_parameters'] = array(
						'data[0]' => $User->email,
						'data[1]' => '111111'
					);
					$wp_response = $whatsapp_controller->sendTemplateMessage($perameater_request);
					$response["whatsapp"] = $wp_response;
				}
			}
		}

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function saveCategory(Request $request)
	{

		$rules = array();

		$rules['architect_id'] = 'required';
		$rules['category_id'] = 'required';

		$customMessage = array();
		$customMessage['architect_id.required'] = "Invalid parameters";
		$customMessage['category_id.required'] = "Invalid category";

		$validator = Validator::make($request->all(), $rules, $customMessage);

		if ($validator->fails()) {

			$response = array();
			$response['status'] = 0;
			$response['msg'] = "The request could not be understood by the server due to malformed syntax";
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();
		} else {

			$Architect = Architect::where('user_id', $request->architect_id)->first();
			if ($Architect) {

				$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
				$isSalePerson = isSalePerson();
				$isChannelPartner = isChannelPartner(Auth::user()->type);
				$isMarketingDispatcherUser = isMarketingDispatcherUser();

				if ($isAdminOrCompanyAdmin == 1 || ($isMarketingDispatcherUser == 1) || ($isSalePerson == 1 && $Architect->sale_person_id == Auth::user()->id) || ($isChannelPartner != 0 && $Architect->added_by == Auth::user()->id)) {

					$ArchitectCategory = ArchitectCategory::find($request->category_id);
					if ($ArchitectCategory || $request->category_id == 0) {
						$Architect->category_id = $request->category_id;
						$Architect->save();
						$response = successRes("Successfully updated architect");
					} else {
						$response = errorRes("Invalid access");
					}
				} else {
					$response = errorRes("Invalid access");
				}
			} else {

				$response = errorRes("Invalid architect");
			}
		}

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function detail(Request $request)
	{

		$User = User::with(array('city' => function ($query) {
			$query->select('id', 'name');
		}, 'company' => function ($query) {
			$query->select('id', 'name');
		}))->where('id', $request->id)->whereIn('type', array(201, 202))->first();
		if ($User) {

			$Architect = Architect::find($User->reference_id);

			if ($Architect) {
				$Architect = json_decode(json_encode($Architect), true);

				$source_type_pieces = explode("-", $Architect['source_type']);

				if ($source_type_pieces[0] == "user") {

					if (isChannelPartner($Architect['source_type_value']) != 0) {

						$User1 = User::select('users.id', DB::raw("channel_partner.firm_name"));
						$User1->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
						$User1->where('id', $Architect['source_type_value']);
						$User1->limit(1);
						$User1 = $User1->first();
						if ($User1) {

							$Architect['source'] = array();
							$Architect['source']['id'] = $User1->id;
							$Architect['source']['text'] = $User1->firm_name;
						}
					} else {

						$User1 = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
						$User1->where('id', $Architect['source_type_value']);
						$User1->limit(1);
						$User1 = $User1->first();
						if ($User1) {

							$Architect['source'] = array();
							$Architect['source']['id'] = $User1->id;
							$Architect['source']['text'] = $User1->full_name;
						}
					}
				}

				$isSalePerson = (Auth::user()->type == 2) ? 1 : 0;

				$SalePersonsIds = getChildSalePersonsIds(Auth::user()->id);

				if ($isSalePerson == 0 || ($isSalePerson == 1 && in_array($Architect['sale_person_id'], $SalePersonsIds))) {

					if ($Architect['visiting_card'] != "") {

						$Architect['visiting_card'] = '(<a target="_blank" href="' . getSpaceFilePath($Architect['visiting_card']) . '" title="File">Download</i></a>)';
					}

					if ($Architect['aadhar_card'] != "") {

						$Architect['aadhar_card'] = '(<a target="_blank" href="' . getSpaceFilePath($Architect['aadhar_card']) . '" title="File">Download</i></a>)';
					}

					$salePerson = User::query();
					$salePerson->select('users.id as id', DB::raw('CONCAT(first_name," ", last_name) AS text'));
					$salePerson = $salePerson->find($Architect['sale_person_id']);


					$User = json_decode(json_encode($User), true);

					$City = CityList::find($User['city_id']);
					$State = StateList::find($City->state_id);
					$Country = CountryList::find($City->country_id);

					$User['country']['id'] = $Country->id;
					$User['country']['name'] = $Country->name;

					$User['state']['id'] = $State->id;
					$User['state']['name'] = $State->name;


					$response = successRes("Successfully get user");
					$response['data'] = $User;
					$response['data']['architect'] = $Architect;
					$response['data']['architect']['sale_person'] = $salePerson;
					$response['data']['login_user_type'] = Auth::user()->type;
				} else {
					$response = errorRes("Invalid id");
				}
			} else {
				$response = errorRes("Invalid id");
			}
		} else {
			$response = errorRes("Invalid id");
		}
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function searchUser(Request $request)
	{

		$isArchitect = isArchitect();
		$isSalePerson = isSalePerson();
		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		$isMarketingDispatcherUser = isMarketingDispatcherUser();

		if (isset($request->user_id) && $request->user_id != "") {

			if ($isSalePerson == 1) {

				$salePerson = SalePerson::select('cities')->where('user_id', Auth::user()->id)->first();
				$cities = array();
				if ($salePerson) {

					$cities = explode(",", $salePerson->cities);
				} else {
					$cities = array(0);
				}
			}

			$User = User::query();
			$User->select('users.id', 'users.first_name', 'users.last_name', 'users.phone_number');
			$User->where('users.status', 1);
			$User->whereIn('users.type', array(301, 302));
			if ($isSalePerson == 1) {

				$User->leftJoin('electrician', 'electrician.user_id', '=', 'users.id');

				$User->where(function ($query) use ($cities) {

					$query->whereIn('users.city_id', $cities);

					// $query->orWhere('electrician.sale_person_id', Auth::user()->id);

				});
			}

			$User->where('users.id', $request->user_id);
			$User->limit(1);
			$UserResponse = $User->get();
		} else {

			if ($isSalePerson == 1 && ($request->source_type == 301 || $request->source_type == 302)) {

				$salePerson = SalePerson::select('cities')->where('user_id', Auth::user()->id)->first();
				$cities = array();
				if ($salePerson) {

					$cities = explode(",", $salePerson->cities);
				} else {
					$cities = array(0);
				}

				//$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);

				$UserResponse = array();
				$q = $request->q;
				$User = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
				$User->where('users.type', $request->source_type);
				$User->leftJoin('electrician', 'electrician.user_id', '=', 'users.id');
				$User->where('users.status', 1);
				//$User->whereIn('electrician.sale_person_id', $childSalePersonsIds);
				$User->whereIn('users.city_id', $cities);
				//$User->where('users.city_id', Auth::user()->city_id);
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
			} else if (isChannelPartner($request->source_type) != 0) {

				if ($isSalePerson == 1) {

					$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);

					$salePerson = SalePerson::select('cities')->where('user_id', Auth::user()->id)->first();
					$cities = array();
					if ($salePerson) {

						$cities = explode(",", $salePerson->cities);
					} else {
						$cities = array(0);
					}
				}

				$UserResponse = array();
				$q = $request->q;
				$User = User::select('users.id', DB::raw("channel_partner.firm_name"));
				$User->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
				$User->where('users.status', 1);
				$User->where('users.type', $request->source_type);
				if ($isSalePerson == 1) {

					$User->where(function ($query) use ($cities, $childSalePersonsIds) {

						$query->whereIn('users.city_id', $cities);

						$query->orWhere(function ($query2) use ($childSalePersonsIds) {
							foreach ($childSalePersonsIds as $key => $value) {
								if ($key == 0) {
									$query2->whereRaw('FIND_IN_SET("' . $value . '",channel_partner.sale_persons)>0');
								} else {
									$query2->orWhereRaw('FIND_IN_SET("' . $value . '",channel_partner.sale_persons)>0');
								}
							}
						});
					});
				}

				$User->where(function ($query) use ($q) {
					$query->where('channel_partner.firm_name', 'like', '%' . $q . '%');
				});
				$User->limit(5);
				$User = $User->get();

				if (count($User) > 0) {
					foreach ($User as $User_key => $User_value) {
						$UserResponse[$User_key]['id'] = $User_value['id'];
						$UserResponse[$User_key]['text'] = $User_value['firm_name'];
					}
				}
			} else {

				$UserResponse = array();
				$q = $request->q;
				$User = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
				$User->where('users.status', 1);
				$User->where('users.type', $request->source_type);
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
			}
		}

		$response = array();
		$response['results'] = $UserResponse;
		$response['pagination']['more'] = false;
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	function export(Request $request)
	{

		$isSalePerson = isSalePerson();
		$isChannelPartner = isChannelPartner(Auth::user()->type);
		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		$isMarketingDispatcherUser = isMarketingDispatcherUser();
		if ($isSalePerson == 1) {
			$SalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
		}



		$columns = array(
			'users.id',
			'users.first_name',
			'users.last_name',
			'users.email',
			'users.dialing_code',
			'users.phone_number',
			'users.status',
			'users.created_at',
			'architect.firm_name',
			'sale_person.first_name as sale_person_first_name',
			'sale_person.last_name  as sale_person_last_name',
			'users.type',
			'city_list.name as city_name'

		);

		$query = Architect::query();
		$query->select($columns);
		$query->leftJoin('users', 'users.id', '=', 'architect.user_id');
		$query->leftJoin('users as sale_person', 'architect.sale_person_id', '=', 'sale_person.id');
		$query->leftJoin('city_list', 'city_list.id', '=', 'users.city_id');
		//$query->where('architect.type', $request->type);
		$query->whereIn('architect.type', [201, 202]);
		if ($isAdminOrCompanyAdmin == 1 || $isMarketingDispatcherUser == 1) {
		} else if ($isSalePerson == 1) {
			$query->whereIn('architect.sale_person_id', $SalePersonsIds);
		} else if ($isChannelPartner != 0) {
			$query->where('architect.added_by', Auth::user()->id);
		}

		$query->orderBy('architect.id', 'desc');
		$data = $query->get();

		// if ($request->type == 201) {
		// 	$headers = array("#ID", "Firstname", "Lastname", "Phone", "Status", "Created", "Firmname", "SalePerson");
		// } else {
		$headers = array("#ID", "TYPE", "Firstname", "Lastname", "Email", "Phone", "Status", "Created", "Firmname", "SalePerson","City");
		// }

		header('Content-Type: text/csv');
		// if ($request->type == 201) {
		// 	header('Content-Disposition: attachment; filename="architects-non-prime.csv"');
		// } else {
		// 	header('Content-Disposition: attachment; filename="architects-prime.csv"');
		// }
		header('Content-Disposition: attachment; filename="architects.csv"');
		$fp = fopen('php://output', 'wb');

		fputcsv($fp, $headers);

		foreach ($data as $key => $value) {

			$createdAt = convertDateTime($value->created_at);
			$status = $value->status;
			if ($status == 0) {
				$status = "Inactive";
			} else if ($status == 1) {
				$status = "Active";
			} else if ($status == 2) {
				$status = "Blocked";
			}

			if ($value->type == 201) {

				$lineVal = array(
					$value->id,
					"NON-PRIME",
					$value->first_name,
					$value->last_name,
					"-",
					$value->dialing_code . " " . $value->phone_number,
					$status,
					$createdAt,
					$value->firm_name,
					$value->sale_person_first_name . ' ' . $value->sale_person_last_name,
					$value->city_name

				);
			} else if ($value->type == 202) {

				$lineVal = array(
					$value->id,
					"PRIME",
					$value->first_name,
					$value->last_name,
					$value->email,
					$value->dialing_code . " " . $value->phone_number,
					$status,
					$createdAt,
					$value->firm_name,
					$value->sale_person_first_name . ' ' . $value->sale_person_last_name,
					$value->city_name
				);
			}

			fputcsv($fp, $lineVal, ",");
		}

		fclose($fp);
	}

	function pointLog(Request $request)
	{

		$searchColumns = array(
			0 => 'crm_log.description',
		);

		$sortingColumns = array(
			0 => 'crm_log.id',

		);

		$selectColumns = array(
			'crm_log.description',
		);

		$query = CRMLog::query();
		$query->where('for_user_id', $request->user_id);
		$query->whereIn('name', array('point-gain', 'point-redeem', 'point-back', 'point-lose'));
		$recordsTotal = $query->count();

		$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.
		$query = CRMLog::query();
		$query->where('for_user_id', $request->user_id);
		$query->whereIn('name', array('point-gain', 'point-redeem', 'point-back', 'point-lose'));
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
			$viewData[$key]['log'] = $value['description'];
		}

		$jsonData = array(
			"draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
			"recordsTotal" => intval($recordsTotal), // total number of records
			"recordsFiltered" => intval($recordsFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
			"data" => $viewData, // total data array

		);
		return $jsonData;
	}

	public function inquiryLog(Request $request)
	{

		$inquiryStatus = getInquiryStatus();

		$searchColumns = array(
			'inquiry.id',
			'CONCAT(inquiry.first_name," ",inquiry.last_name)',
			'inquiry.status',
			'inquiry.quotation_amount',
		);

		$sortingColumns = array(
			0 => 'inquiry.id',
			1 => 'inquiry.first_name',
			2 => 'inquiry.status',
			3 => 'inquiry.quotation_amount',

		);

		$selectColumns = array(
			'inquiry.id',
			'inquiry.first_name',
			'inquiry.last_name',
			'inquiry.status',
			'inquiry.quotation_amount',
			'inquiry.answer_date_time',
			'inquiry.electrician',
			'inquiry.lead_id',
			'inquiry.source_type',
			'inquiry.source_type_value',

		);

		$userId = $request->user_id;
		$title = "";
		$User = User::find($userId);
		if ($User) {
			$title = $User->first_name . " " . $User->last_name;
			$UserArchitect = Architect::where('user_id', $User->id)->first();
			if ($UserArchitect) {
				$title = $title . " | Lifetime Point : " . $UserArchitect->total_point . " | Available Point : " . $UserArchitect->total_point_current;
			}
		}

		$query = Inquiry::query();
		$query->where(function ($query2) use ($userId) {

			$query2->where(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type', array("user-201", "user-202"));
				$query3->where('inquiry.source_type_value', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_1', array("user-201", "user-202"));
				$query3->where('inquiry.source_type_value_1', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_2', array("user-201", "user-202"));
				$query3->where('inquiry.source_type_value_2', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_3', array("user-201", "user-202"));
				$query3->where('inquiry.source_type_value_3', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_4', array("user-201", "user-202"));
				$query3->where('inquiry.source_type_value_4', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->where('inquiry.architect', $userId);
			});
		});

		if ($request->type != 0) {

			if ($request->type == 1) {
				$statusArray = isset($inquiryStatus[201]['for_user_ids']) ? $inquiryStatus[201]['for_user_ids'] : array(0);
				$query->whereIn('inquiry.status', $statusArray);
			} else if ($request->type == 2) {
				$statusArray = array(9, 11, 10, 12, 14);
				$query->whereIn('inquiry.status', $statusArray);
			} else if ($request->type == 3) {
				$statusArray = array(101, 102);
				$query->whereIn('inquiry.status', $statusArray);
			}
		}




		$recordsTotal = $query->count();

		$query = Inquiry::query();
		$query->where(function ($query2) use ($userId) {

			$query2->where(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type', array("user-201", "user-202"));
				$query3->where('inquiry.source_type_value', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_1', array("user-201", "user-202"));
				$query3->where('inquiry.source_type_value_1', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_2', array("user-201", "user-202"));
				$query3->where('inquiry.source_type_value_2', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_3', array("user-201", "user-202"));
				$query3->where('inquiry.source_type_value_3', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_4', array("user-201", "user-202"));
				$query3->where('inquiry.source_type_value_4', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->where('inquiry.architect', $userId);
			});
		});

		if ($request->type != 0) {

			if ($request->type == 1) {
				$statusArray = isset($inquiryStatus[201]['for_user_ids']) ? $inquiryStatus[201]['for_user_ids'] : array(0);
				$query->whereIn('inquiry.status', $statusArray);
			} else if ($request->type == 2) {
				$statusArray = array(9, 11, 10, 12, 14);
				$query->whereIn('inquiry.status', $statusArray);
			} else if ($request->type == 3) {
				$statusArray = array(101, 102);
				$query->whereIn('inquiry.status', $statusArray);
			}
		}

		$quotationTotal = $query->sum('quotation_amount');

		$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.
		$query = Inquiry::query();

		$query->where(function ($query2) use ($userId) {

			$query2->where(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type', array("user-201", "user-202"));
				$query3->where('inquiry.source_type_value', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_1', array("user-201", "user-202"));
				$query3->where('inquiry.source_type_value_1', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_2', array("user-201", "user-202"));
				$query3->where('inquiry.source_type_value_2', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_3', array("user-201", "user-202"));
				$query3->where('inquiry.source_type_value_3', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_4', array("user-201", "user-202"));
				$query3->where('inquiry.source_type_value_4', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->where('inquiry.architect', $userId);
			});
		});

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
						$query->whereRaw($searchColumns[$i] . ' like ?', [$search_value]);
					} else {

						$query->orWhereRaw($searchColumns[$i] . ' like ?', ["%" . $search_value . "%"]);
					}
				}
			});
		}

		if ($request->type != 0) {

			if ($request->type == 1) {
				$statusArray = isset($inquiryStatus[201]['for_user_ids']) ? $inquiryStatus[201]['for_user_ids'] : array(0);
				$query->whereIn('inquiry.status', $statusArray);
			} else if ($request->type == 2) {
				$statusArray = array(9, 11, 10, 12, 14);
				$query->whereIn('inquiry.status', $statusArray);
			} else if ($request->type == 3) {
				$statusArray = array(101, 102);
				$query->whereIn('inquiry.status', $statusArray);
			}
		}

		$data = $query->get();
		$data = json_decode(json_encode($data), true);
		if ($isFilterApply == 1) {
			$recordsFiltered = count($data);
		}

		$viewData = array();



		foreach ($data as $key => $value) {

			$viewData[$key] = array();
			$lead = Lead::query()->select('id','is_deal')->where('id',$value['lead_id'])->first();

			if($lead){
				if($lead->is_deal == 0){
					$viewData[$key]['id'] = '#L'.$lead->id;
				}else{
					$viewData[$key]['id'] = '#D'.$lead->id;
				}
				$url = route('crm.lead') . "?id=" . $lead->id;

			}else{
				$viewData[$key]['id'] = $value['id'];
				$url = 'javascript:void(0)';
			}

			// $viewData[$key]['name'] = '<p class="text-muted mb-0" data-bs-toggle="tooltip"  data-bs-original-title="' . $value['first_name'] . " " . $value['last_name'] . '"><a target="_blank" href="' . route('inquiry') . '?status=0&inquiry_id=' . $value['id'] . '" >' . displayStringLenth($value['first_name'] . " " . $value['last_name'], 20) . '</a></p>';
			$viewData[$key]['name'] = '<p class="text-muted mb-0" data-bs-toggle="tooltip"  data-bs-original-title="' . $value['first_name'] . " " . $value['last_name'] . '"><a target="_blank" href="' . $url . '" >' . displayStringLenth($value['first_name'] . " " . $value['last_name'], 20) . '</a></p>';

			$viewData[$key]['status'] = $inquiryStatus[$value['status']]['name'] . " (" . convertDateTime($value['answer_date_time']) . ")";
			$viewData[$key]['quotation_amount'] = $value['quotation_amount'];

			$column4Val = "";
			$column5Val = "";

			if ($value['electrician'] != 0) {

				$User4 = User::find($value['electrician']);
				if ($User4) {
					$column4Val = $User4->first_name . " " . $User4->last_name;
				}
			}

			if (in_array($value['source_type'], array("user-101", "user-102", "user-103", "user-104", "user-105")) && $value['source_type_value'] != 0) {

				$User5 = ChannelPartner::where('user_id', $value['source_type_value'])->first();
				if ($User5) {
					$column5Val = $User5->firm_name;
				}
			}

			$viewData[$key]['column4'] = $column4Val;
			$viewData[$key]['column5'] = $column5Val;
		}








		$overview = array();

		$query = Inquiry::query();
		$query->where(function ($query2) use ($userId) {

			$query2->where(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type', array("user-201", "user-202"));
				$query3->where('inquiry.source_type_value', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_1', array("user-201", "user-202"));
				$query3->where('inquiry.source_type_value_1', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_2', array("user-201", "user-202"));
				$query3->where('inquiry.source_type_value_2', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_3', array("user-201", "user-202"));
				$query3->where('inquiry.source_type_value_3', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_4', array("user-201", "user-202"));
				$query3->where('inquiry.source_type_value_4', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->where('inquiry.architect', $userId);
			});
		});


		$recordsTotal = $query->count();


		$overview['total_inquiry'] = $recordsTotal;

		$query = Inquiry::query();
		$query->where(function ($query2) use ($userId) {

			$query2->where(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type', array("user-201", "user-202"));
				$query3->where('inquiry.source_type_value', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_1', array("user-201", "user-202"));
				$query3->where('inquiry.source_type_value_1', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_2', array("user-201", "user-202"));
				$query3->where('inquiry.source_type_value_2', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_3', array("user-201", "user-202"));
				$query3->where('inquiry.source_type_value_3', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_4', array("user-201", "user-202"));
				$query3->where('inquiry.source_type_value_4', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->where('inquiry.architect', $userId);
			});
		});
		$statusArray = isset($inquiryStatus[201]['for_user_ids']) ? $inquiryStatus[201]['for_user_ids'] : array(0);
		$query->whereIn('inquiry.status', $statusArray);
		$recordsTotal = $query->count();
		$overview['total_running'] = $recordsTotal;


		$query = Inquiry::query();
		$query->where(function ($query2) use ($userId) {

			$query2->where(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type', array("user-201", "user-202"));
				$query3->where('inquiry.source_type_value', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_1', array("user-201", "user-202"));
				$query3->where('inquiry.source_type_value_1', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_2', array("user-201", "user-202"));
				$query3->where('inquiry.source_type_value_2', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_3', array("user-201", "user-202"));
				$query3->where('inquiry.source_type_value_3', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_4', array("user-201", "user-202"));
				$query3->where('inquiry.source_type_value_4', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->where('inquiry.architect', $userId);
			});
		});
		$statusArray = isset($inquiryStatus[9]['for_user_ids']) ? $inquiryStatus[9]['for_user_ids'] : array(0);
		$query->whereIn('inquiry.status', $statusArray);
		$recordsTotal = $query->count();
		$overview['total_won'] = $recordsTotal;


		$query = Inquiry::query();
		$query->where(function ($query2) use ($userId) {

			$query2->where(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type', array("user-201", "user-202"));
				$query3->where('inquiry.source_type_value', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_1', array("user-201", "user-202"));
				$query3->where('inquiry.source_type_value_1', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_2', array("user-201", "user-202"));
				$query3->where('inquiry.source_type_value_2', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_3', array("user-201", "user-202"));
				$query3->where('inquiry.source_type_value_3', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_4', array("user-201", "user-202"));
				$query3->where('inquiry.source_type_value_4', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->where('inquiry.architect', $userId);
			});
		});
		// $statusArray = isset($inquiryStatus[102]['for_user_ids']) ? $inquiryStatus[102]['for_user_ids'] : array(0);
		$statusArray = array(101, 102);
		$query->whereIn('inquiry.status', $statusArray);
		$recordsTotal = $query->count();
		$overview['total_rejected'] = $recordsTotal;



		$jsonData = array(
			"draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
			"recordsTotal" => intval($recordsTotal), // total number of records
			"recordsFiltered" => intval($recordsFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
			"data" => $viewData, // total data array
			"overview" => $overview, // total data array
			"type" => $request->type,
			"quotationAmount" => priceLable($quotationTotal),
			"title" => $title,

		);
		return $jsonData;
	}
}
