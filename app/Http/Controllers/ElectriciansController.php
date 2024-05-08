<?php

namespace App\Http\Controllers;

use App\Models\ChannelPartner;
use App\Models\CRMLog;
use App\Models\Electrician;
use App\Models\Inquiry;
use App\Models\CRMHelpDocument;
use App\Models\Lead;
use App\Models\SalePerson;
use App\Models\User;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Whatsapp\WhatsappApiContoller;

//use Illuminate\Support\Facades\Hash;

class ElectriciansController extends Controller
{

	public function __construct()
	{

		$this->middleware(function ($request, $next) {

			$tabCanAccessBy = array(0, 1, 2, 7, 9, 101, 102, 103, 104, 105);

			if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
				return redirect()->route('dashboard');
			}

			return $next($request);
		});
	}

	public function nonPrime(Request $request)
	{

		$data = array();
		$data['title'] = "Non Prime - Electricians";
		$data['type'] = 301;
		$data['isSalePerson'] = isSalePerson();
		$data['addView'] = (isset($request->add) && $request->add == 1) ? 1 : 0;
		$data['viewMode'] = (isset($request->view_mode) && $request->view_mode == 1) ? 1 : 0;
		if ($data['isSalePerson'] == 1) {
			$data['viewMode'] = 0;
		}
		return view('electricians/index', compact('data'));
	}

	public function prime(Request $request)
	{

		$data = array();
		$data['title'] = "Prime - Electricians";
		$data['type'] = 302;
		$data['isSalePerson'] = isSalePerson();
		$data['addView'] = (isset($request->add) && $request->add == 1) ? 1 : 0;
		$data['viewMode'] = (isset($request->view_mode) && $request->view_mode == 1) ? 1 : 0;
		if ($data['isSalePerson'] == 1) {
			$data['viewMode'] = 0;
		}
		$data['searchUserId'] = (isset($request->id)) ? $request->id : "";
		return view('electricians/index', compact('data'));
	}

	public function ajax(Request $request)
	{

		$isSalePerson = isSalePerson();
		$isChannelPartner = isChannelPartner(Auth::user()->type);
		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		$isMarketingDispatcherUser = isMarketingDispatcherUser();
		if ($isSalePerson == 1) {
			$SalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
		}

		$isTaleSalesUser = isTaleSalesUser();
		if ($isTaleSalesUser == 1) {
			$TeleSalesCity = TeleSalesCity(Auth::user()->id);
		}

		$viewMode = (isset($request->view_mode) && $request->view_mode == 1) ? 1 : 0;

		$searchColumns = array(
			'users.id',
			'users.email',
			'users.phone_number',
			'CONCAT(sale_person.first_name," ",sale_person.last_name)',
			'CONCAT(users.first_name," ",users.last_name)',
		);

		if ($request->type == 302) {

			$sortingColumns = array(
				0 => 'users.id',
				1 => 'users.first_name',
				2 => 'users.phone_number',
				3 => 'electrician.sale_person_id',
				4 => 'electrician.total_point_current',
				5 => 'electrician.total_point',
				6 => 'users.status',
				7 => 'users.created_by',

			);
		} else {

			$sortingColumns = array(
				0 => 'users.id',
				1 => 'users.first_name',
				2 => 'users.phone_number',
				3 => 'electrician.sale_person_id',
				4 => 'users.status',
				5 => 'users.created_by',

			);
		}

		$selectColumns = array(
			'users.id',
			'users.type',
			'users.first_name',
			'users.last_name',
			'users.email',
			'users.phone_number',
			'electrician.sale_person_id',
			'users.status',
			'users.created_at',
			'electrician.total_point_current',
			'electrician.total_point',
			'created_by_user.first_name as created_by_user_first_name',
			'created_by_user.last_name as created_by_user_last_name',

		);

		$query = Electrician::query();
		$query->leftJoin('users', 'users.id', '=', 'electrician.user_id');
		$query->whereIn('electrician.type', [301, 302]);

		if (isset($request->search_user_id) && $request->search_user_id != "") {
			$query->where('users.id', $request->search_user_id);
		}

		if ($isAdminOrCompanyAdmin == 1 || $isMarketingDispatcherUser == 1) {
		} else if ($isSalePerson == 1) {
			$query->whereIn('electrician.sale_person_id', $SalePersonsIds);
		} else if ($isChannelPartner != 0) {
			$query->where('electrician.added_by', Auth::user()->id);
		} else if ($isTaleSalesUser == 1) {
			$query->whereIn('users.city_id', $TeleSalesCity);
		}

		$recordsTotal = $query->count();

		$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.

		$query = Electrician::query();
		$query->leftJoin('users', 'users.id', '=', 'electrician.user_id');
		$query->leftJoin('users as sale_person', 'electrician.sale_person_id', '=', 'sale_person.id');
		$query->whereIn('electrician.type', [301, 302]);
		if ($isAdminOrCompanyAdmin == 1 || $isMarketingDispatcherUser == 1) {
		} else if ($isSalePerson == 1) {
			$query->whereIn('electrician.sale_person_id', $SalePersonsIds);
		} else if ($isChannelPartner != 0) {
			$query->where('electrician.added_by', Auth::user()->id);
		} else if ($isTaleSalesUser == 1) {
			$query->whereIn('users.city_id', $TeleSalesCity);
		}

		$query->select('electrician.id');
		// $query->limit($request->length);
		// $query->offset($request->start);
		$query->orderBy($sortingColumns[$request['order'][0]['column']], $request['order'][0]['dir']);

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

		$query = Electrician::query();
		$query->leftJoin('users', 'users.id', '=', 'electrician.user_id');
		$query->leftJoin('users as created_by_user', 'users.created_by', '=', 'created_by_user.id');
		$query->leftJoin('users as sale_person', 'electrician.sale_person_id', '=', 'sale_person.id');
		$query->whereIn('electrician.type', [301, 302]);

		if (isset($request->search_user_id) && $request->search_user_id != "") {
			$query->where('users.id', $request->search_user_id);
		}
		if ($isAdminOrCompanyAdmin == 1 || $isMarketingDispatcherUser == 1) {
		} else if ($isSalePerson == 1) {
			$query->whereIn('electrician.sale_person_id', $SalePersonsIds);
		} else if ($isChannelPartner != 0) {
			$query->where('electrician.added_by', Auth::user()->id);
		}
		$query->select($selectColumns);
		$query->limit($request->length);
		$query->offset($request->start);
		if (isset($request->filter_electrician_advance) && $request->filter_electrician_advance == "1") {

			$query->orderBy('electrician.total_inquiry', 'desc');
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

		foreach ($data as $key => $value) {

			$valueCreatedTime = convertDateTime($value['created_at']);

			$viewData[$key] = array();
			$viewData[$key]['id'] = '<div class="avatar-xs"><span class="avatar-title rounded-circle">' . $value['id'] . '</span></div>';

			if ($value['type'] == 301) {

				$viewData[$key]['name'] = '<a onclick="inquiryLogs(' . $value['id'] . ')" href="javascript: void(0);" class="">' . $value['first_name'] . " " . $value['last_name'] . '</a>
				<p class="text-muted mb-0">' . getUserTypeName($value['type']) . '</p>';
			} else if ($value['type'] == 302) {

				$viewData[$key]['name'] = '<a onclick="inquiryLogs(' . $value['id'] . ')" href="javascript: void(0);" class="">' . $value['first_name'] . " " . $value['last_name'] . ' </a> <span class="badge rounded-pill bg-success">PRIME</span>
				<p class="text-muted mb-0">' . getUserTypeName($value['type']) . '</p>';
			}




			$viewData[$key]['sale_person'] = "";

			$salePerson = User::query();
			$salePerson->select('users.id as id', DB::raw('CONCAT(first_name," ", last_name) AS text'));
			$salePerson = $salePerson->find($value['sale_person_id']);
			if ($salePerson) {

				$viewData[$key]['sale_person'] = '<p class="text-muted mb-0">' . $salePerson->text . '</p>';
			}

			$viewData[$key]['status'] = getUserStatusLable($value['status']);
			$viewData[$key]['point'] = $value['total_point_current'];
			$viewData[$key]['created_by'] = '<p class="text-muted mb-0">' . $value['created_by_user_first_name'] . ' ' . $value['created_by_user_last_name'] . '</p>';

			// if ($request->type == 302) {
			$viewData[$key]['total_point_current'] = '<a onclick="pointLogs(' . $value['id'] . ')" href="javascript: void(0);" title="Detail">' . $value['total_point_current'] . '</a>';
			$viewData[$key]['total_point'] = '<a onclick="pointLogs(' . $value['id'] . ')" href="javascript: void(0);" title="Detail">' . $value['total_point'] . '</a>';
			// }

			// if ($value['type'] == 301) {

			// 	$viewData[$key]['email'] = '';

			// } else {
			// 	$viewData[$key]['email'] = '<p class="text-muted mb-0">' . $value['email'] . '</p>';

			// }

			$viewData[$key]['email'] = '<p class="text-muted mb-0">' . $value['phone_number'] . '</p>';

			$uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';

			if ($viewMode == 0) {

				if ($isSalePerson == 1) {

					$uiAction .= '<li class="list-inline-item px-2">';
					$uiAction .= '<a data-bs-toggle="tooltip" href="javascript: void(0);" title="Created Date & Time : ' . $valueCreatedTime . '"><i class="bx bx-calendar"></i></a>';

					$uiAction .= '<li class="list-inline-item px-2">';

					if ($value['type'] == 301) {
						$uiAction .= '<a onclick="editView(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Edit"><i class="bx bx-edit-alt"></i></a>';
					} else {
						$uiAction .= '<a onclick="editView(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Edit"><i class="mdi mdi-eye"></i></a>';
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
		$isMarketingDispatcherUser = isMarketingDispatcherUser();
		$isSalePerson = isSalePerson();
		$isTaleSalesUser = isTaleSalesUser();
		$isChannelPartner = isChannelPartner(Auth::user()->type);

		$searchKeyword = isset($request->q) ? $request->q : "";

		if ($isAdminOrCompanyAdmin == 1 || $isMarketingDispatcherUser == 1 || $isSalePerson == 1 || $isTaleSalesUser == 1) {

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
		$user_type = isset($request->user_type) ? $request->user_type : 301;
		$isSalePerson = isSalePerson();
		$rules = array();
		$rules['user_id'] = 'required';
		$rules['user_type'] = 'required';
		$rules['user_first_name'] = 'required';
		$rules['user_last_name'] = 'required';
		// $rules['user_address_line1'] = 'required';
		// $rules['user_pincode'] = 'required';
		$rules['user_country_id'] = 'required';
		$rules['user_state_id'] = 'required';
		$rules['user_city_id'] = 'required';
		if ($user_type == 302) {

			//$rules['user_email'] = 'required';
			$rules['user_address_line1'] = 'required';
			//$rules['user_pincode'] = 'required';

		}

		if ($isSalePerson == 0) {
			$rules['user_status'] = 'required';
			$rules['electrician_sale_person_id'] = 'required';
		}

		if ($isSalePerson == 1 && $request->user_type == 302 && ($request->user_id != "" && $request->user_id != 0)) {
			$rules = array();
			$rules['user_id'] = 'required';
			$rules['user_type'] = 'required';
			$User = User::find($request->user_id);
			$requireFieldForSalesUser = array();
			if ($User) {

				if ($User->address_line1 == "") {
					$rules['user_address_line1'] = 'required';
					$requireFieldForSalesUser[] = "user_address_line1";
				}
			}
		}

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {

			$response = array();
			$response['status'] = 0;
			$response['msg'] = "The request could not be understood by the server due to malformed syntax";
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();
		} else {


			if ($isSalePerson == 1 && $request->user_type == 302 && ($request->user_id != "" && $request->user_id != 0) != "") {


				if (count($requireFieldForSalesUser) > 0) {
					if (in_array('user_address_line1', $requireFieldForSalesUser)) {

						$User->address_line1 = $request->user_address_line1;
					}
					$User->type = $request->user_type;
					$User->save();
					$Electrician = Electrician::find($User->reference_id);
					$Electrician->type = $request->user_type;
					$Electrician->save();
					$debugLog['name'] = "electrician-edit";
					$debugLog['description'] = "user #" . $User->id . "(" . $User->first_name . " " . $User->last_name . ") has been updated ";
					$response = successRes("Successfully saved user");
					saveDebugLog($debugLog);
					return response()->json($response)->header('Content-Type', 'application/json');
				} else {

					$User->type = $request->user_type;
					$User->save();
					$Electrician = Electrician::find($User->reference_id);
					$Electrician->type = $request->user_type;
					$Electrician->save();
					$debugLog['name'] = "electrician-edit";
					$debugLog['description'] = "user #" . $User->id . "(" . $User->first_name . " " . $User->last_name . ") has been updated ";
					$response = successRes("Successfully saved user");
					saveDebugLog($debugLog);

					

					return response()->json($response)->header('Content-Type', 'application/json');
				}
			}




			if ($isSalePerson == 1) {
				$sale_person_id = Auth::user()->id;
			} else {
				$sale_person_id = $request->electrician_sale_person_id;
			}

			// if ($user_type == 301) {

			$email = time() . "@whitelion.in";

			// } else {
			// 	$email = $request->user_email;
			// }

			$phone_number = $request->user_phone_number;

			$alreadyEmail = User::query();
			$alreadyEmail->where('email', $email);
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

			$AllUserTypes = getAllUserTypes();

			if ($alreadyEmail) {

				$response = errorRes("Email already exists(" . $AllUserTypes[$alreadyEmail->type]['name'] . "), Try with another email");
			} else if ($alreadyPhoneNumber) {
				$response = errorRes("Phone number already exists(" . $AllUserTypes[$alreadyPhoneNumber->type]['name'] . "), Try with another phone number");
			} else {

				$user_address_line1 = isset($request->user_address_line1) ? $request->user_address_line1 : '';
				$user_address_line2 = isset($request->user_address_line2) ? $request->user_address_line2 : '';
				$user_pincode = isset($request->user_pincode) ? $request->user_pincode : '';

				$user_company_id = 1;

				if ($request->user_id == 0) {

					$User = User::where('type', 10000)->where(function ($query) use ($request) {
						$query->where('email', $request->user_email)->orWhere('phone_number', $request->user_phone_number);
					})->first();

					if ($User) {

						$User->type = $request->user_type;
						$User->reference_type = "electrician";
						$User->reference_id = 0;
					} else {


						$User = new User();
						$User->created_by = Auth::user()->id;
						$User->password = Hash::make("111111");
						$User->last_active_date_time = date('Y-m-d H:i:s');
						$User->last_login_date_time = date('Y-m-d H:i:s');
						$User->avatar = "default.png";
					}
					//$User->is_sent_mail = 0;

					$Electrician = new Electrician();
					if ($isSalePerson == 1) {

						$User->status = 1;
					} else {
						$User->status = $request->user_status;
					}
					$converted_prime = 1;
				} else {
					$User = User::find($request->user_id);
					$Electrician = Electrician::find($User->reference_id);
					if (!$Electrician) {

						$response = errorRes("Something went wrong");
					}

					$converted_prime = $Electrician->converted_prime;

					if ($isSalePerson == 0) {

						$User->status = $request->user_status;
					}
				}
				$User->first_name = $request->user_first_name;
				$User->last_name = $request->user_last_name;
				$User->email = $email;
				$User->dialing_code = "+91";
				$User->phone_number = $request->user_phone_number;
				$User->ctc = 0;
				if ($request->user_type == 302 || $request->user_id == 0) {
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

				$Electrician->user_id = $User->id;
				$Electrician->type = $request->user_type;
				$Electrician->sale_person_id = $sale_person_id;
				$Electrician->added_by = Auth::user()->id;
				$Electrician->save();
				$User->reference_type = "electrician";
				$User->reference_id = $Electrician->id;
				$User->save();

				$debugLog = array();
				if ($request->user_id != 0) {
					$debugLog['name'] = "electrician-edit";
					$debugLog['description'] = "user #" . $User->id . "(" . $User->first_name . " " . $User->last_name . ") has been updated ";
					$response = successRes("Successfully saved user");
				} else {

					$mobileNotificationTitle = "New Electrician Create";
					$mobileNotificationMessage = "New Electrician " . $User->first_name . " " . $User->last_name . " Added By " . Auth::user()->first_name . " " . Auth::user()->last_name;;
					$notificationUserids = getParentSalePersonsIds($Electrician->sale_person_id);
					$notificationUserids[] = $Electrician->sale_person_id;
					$UsersNotificationTokens = UsersNotificationTokens($notificationUserids);
					sendNotificationTOAndroid($mobileNotificationTitle, $mobileNotificationMessage, $UsersNotificationTokens,'Electrician',$User);

					$helpDocument = CRMHelpDocument::query();
					$helpDocument->where('status', 1);
					$helpDocument->where('type', 302);
					$helpDocument->orderBy('publish_date_time', "desc");
					$helpDocument = $helpDocument->first();
					//TEMPLATE 8
					$whatsapp_controller = new WhatsappApiContoller;
					$perameater_request = new Request();
					$perameater_request['q_whatsapp_massage_template'] = 'for_electrician_download_app';
					$perameater_request['q_whatsapp_massage_mobileno'] = $User->phone_number;
					$perameater_request['q_whatsapp_massage_attechment'] = $helpDocument ? getSpaceFilePath($helpDocument->file_name) : '';
					$perameater_request['q_broadcast_name'] =  $User->first_name . ' ' . $User->last_name;
					$perameater_request['q_whatsapp_massage_parameters'] = array();

					$wp_response = $whatsapp_controller->sendTemplateMessage($perameater_request);
					$response["whatsapp"] = $wp_response;
					
					$debugLog['name'] = "electrician-add";
					$debugLog['description'] = "user #" . $User->id . "(" . $User->first_name . " " . $User->last_name . ") has been added ";
					$response = successRes("Successfully added user");
				}
				saveDebugLog($debugLog);
			}
		}

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function detail(Request $request)
	{

		$User = User::with(array('country' => function ($query) {
			$query->select('id', 'name');
		}, 'state' => function ($query) {
			$query->select('id', 'name');
		}, 'city' => function ($query) {
			$query->select('id', 'name');
		}, 'company' => function ($query) {
			$query->select('id', 'name');
		}))->where('id', $request->id)->whereIn('type', array(301, 302))->first();
		if ($User) {

			$Electrician = Electrician::find($User->reference_id);

			if ($Electrician) {

				$isSalePerson = isSalePerson();

				if ($isSalePerson == 1) {
					$SalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
				}

				if ($isSalePerson == 0 || ($isSalePerson == 1 && in_array($Electrician->sale_person_id, $SalePersonsIds))) {

					$salePerson = User::query();
					$salePerson->select('users.id as id', DB::raw('CONCAT(first_name," ", last_name) AS text'));
					$salePerson = $salePerson->find($Electrician->sale_person_id);

					$response = successRes("Successfully get user");
					$response['data'] = $User;
					$response['data']['electrician'] = $Electrician;
					$response['data']['electrician']['sale_person'] = $salePerson;
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

	function export(Request $request)
	{

		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		$isMarketingDispatcherUser = isMarketingDispatcherUser();
		$isSalePerson = isSalePerson();
		$isChannelPartner = isChannelPartner(Auth::user()->type);

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
			'sale_person.first_name as sale_person_first_name',
			'sale_person.last_name  as sale_person_last_name',
			'users.type',
			'city_list.name as city_name',
		);

		$query = Electrician::query();
		$query->select($columns);
		$query->leftJoin('users', 'users.id', '=', 'electrician.user_id');
		$query->leftJoin('users as sale_person', 'electrician.sale_person_id', '=', 'sale_person.id');
		$query->leftJoin('city_list', 'city_list.id', '=', 'users.city_id');
		//$query->where('electrician.type', $request->type);
		$query->whereIn('electrician.type', [301, 302]);
		if ($isAdminOrCompanyAdmin == 1 || $isMarketingDispatcherUser == 1) {
		} else if ($isSalePerson == 1) {
			$query->whereIn('electrician.sale_person_id', $SalePersonsIds);
		} else if ($isChannelPartner != 0) {
			$query->where('electrician.added_by', Auth::user()->id);
		}

		$query->orderBy('electrician.id', 'desc');
		$data = $query->get();

		// if ($request->type == 301) {
		// 	$headers = array("#ID", "Firstname", "Lastname", "Phone", "Status", "Created", "SalePerson");
		// } else {
		$headers = array("#ID", "TYPE", "Firstname", "Lastname", "Phone", "Status", "Created", "SalePerson","City");
		//}

		header('Content-Type: text/csv');
		// if ($request->type == 301) {
		// 	header('Content-Disposition: attachment; filename="electricians-non-prime.csv"');
		// } else {
		header('Content-Disposition: attachment; filename="electricians.csv"');
		// }
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

			$lineVal = array();

			if ($value->type == 301) {

				$lineVal = array(
					$value->id,
					"NON-PRIME",
					$value->first_name,
					$value->last_name,
					$value->dialing_code . " " . $value->phone_number,
					$status,
					$createdAt,
					$value->sale_person_first_name . ' ' . $value->sale_person_last_name,
					$value->city_name
				);
			} else if ($value->type == 302) {

				$lineVal = array(
					$value->id,
					"PRIME",
					$value->first_name,
					$value->last_name,
					$value->dialing_code . " " . $value->phone_number,
					$status,
					$createdAt,
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
			'inquiry.lead_id',
			'inquiry.quotation_amount',
			'answer_date_time',
			'inquiry.architect',
			'inquiry.source_type',
			'inquiry.source_type_value',

		);

		$userId = $request->user_id;
		$title = "";
		$User = User::find($userId);
		if ($User) {
			$title = $User->first_name . " " . $User->last_name;
			$UserArchitect = Electrician::where('user_id', $User->id)->first();
			if ($UserArchitect) {
				$title = $title . " | Lifetime Point : " . $UserArchitect->total_point . " | Available Point : " . $UserArchitect->total_point_current;
			}
		}

		$query = Inquiry::query();
		$query->where(function ($query2) use ($userId) {

			$query2->where(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type', array("user-301", "user-302"));
				$query3->where('inquiry.source_type_value', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_1', array("user-301", "user-302"));
				$query3->where('inquiry.source_type_value_1', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_2', array("user-301", "user-302"));
				$query3->where('inquiry.source_type_value_2', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_3', array("user-301", "user-302"));
				$query3->where('inquiry.source_type_value_3', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_4', array("user-301", "user-302"));
				$query3->where('inquiry.source_type_value_4', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->where('inquiry.electrician', $userId);
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


		$recordsFiltered = $recordsTotal;
		$query = Inquiry::query();
		$query->where(function ($query2) use ($userId) {

			$query2->where(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type', array("user-301", "user-302"));
				$query3->where('inquiry.source_type_value', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_1', array("user-301", "user-302"));
				$query3->where('inquiry.source_type_value_1', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_2', array("user-301", "user-302"));
				$query3->where('inquiry.source_type_value_2', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_3', array("user-301", "user-302"));
				$query3->where('inquiry.source_type_value_3', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_4', array("user-301", "user-302"));
				$query3->where('inquiry.source_type_value_4', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->where('inquiry.electrician', $userId);
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
		$quotationTotal = $query->sum('quotation_amount');


		// when there is no search parameter then total number rows = total number filtered rows.
		$query = Inquiry::query();

		$query->where(function ($query2) use ($userId) {

			$query2->where(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type', array("user-301", "user-302"));
				$query3->where('inquiry.source_type_value', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_1', array("user-301", "user-302"));
				$query3->where('inquiry.source_type_value_1', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_2', array("user-301", "user-302"));
				$query3->where('inquiry.source_type_value_2', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_3', array("user-301", "user-302"));
				$query3->where('inquiry.source_type_value_3', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_4', array("user-301", "user-302"));
				$query3->where('inquiry.source_type_value_4', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->where('inquiry.electrician', $userId);
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

			if ($value['architect'] != 0) {

				$User4 = User::find($value['architect']);
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

				$query3->whereIn('inquiry.source_type', array("user-301", "user-302"));
				$query3->where('inquiry.source_type_value', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_1', array("user-301", "user-302"));
				$query3->where('inquiry.source_type_value_1', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_2', array("user-301", "user-302"));
				$query3->where('inquiry.source_type_value_2', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_3', array("user-301", "user-302"));
				$query3->where('inquiry.source_type_value_3', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_4', array("user-301", "user-302"));
				$query3->where('inquiry.source_type_value_4', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->where('inquiry.electrician', $userId);
			});
		});

		$recordsTotal = $query->count();
		$overview['total_inquiry'] = $recordsTotal;

		$query = Inquiry::query();
		$query->where(function ($query2) use ($userId) {

			$query2->where(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type', array("user-301", "user-302"));
				$query3->where('inquiry.source_type_value', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_1', array("user-301", "user-302"));
				$query3->where('inquiry.source_type_value_1', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_2', array("user-301", "user-302"));
				$query3->where('inquiry.source_type_value_2', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_3', array("user-301", "user-302"));
				$query3->where('inquiry.source_type_value_3', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_4', array("user-301", "user-302"));
				$query3->where('inquiry.source_type_value_4', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->where('inquiry.electrician', $userId);
			});
		});
		$statusArray = isset($inquiryStatus[201]['for_user_ids']) ? $inquiryStatus[201]['for_user_ids'] : array(0);
		$query->whereIn('inquiry.status', $statusArray);
		$recordsTotal = $query->count();
		$overview['total_running'] = $recordsTotal;


		$query = Inquiry::query();
		$query->where(function ($query2) use ($userId) {

			$query2->where(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type', array("user-301", "user-302"));
				$query3->where('inquiry.source_type_value', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_1', array("user-301", "user-302"));
				$query3->where('inquiry.source_type_value_1', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_2', array("user-301", "user-302"));
				$query3->where('inquiry.source_type_value_2', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_3', array("user-301", "user-302"));
				$query3->where('inquiry.source_type_value_3', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_4', array("user-301", "user-302"));
				$query3->where('inquiry.source_type_value_4', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->where('inquiry.electrician', $userId);
			});
		});
		$statusArray = isset($inquiryStatus[9]['for_user_ids']) ? $inquiryStatus[9]['for_user_ids'] : array(0);
		$query->whereIn('inquiry.status', $statusArray);
		$recordsTotal = $query->count();
		$overview['total_won'] = $recordsTotal;


		$query = Inquiry::query();
		$query->where(function ($query2) use ($userId) {

			$query2->where(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type', array("user-301", "user-302"));
				$query3->where('inquiry.source_type_value', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_1', array("user-301", "user-302"));
				$query3->where('inquiry.source_type_value_1', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_2', array("user-301", "user-302"));
				$query3->where('inquiry.source_type_value_2', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_3', array("user-301", "user-302"));
				$query3->where('inquiry.source_type_value_3', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_4', array("user-301", "user-302"));
				$query3->where('inquiry.source_type_value_4', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->where('inquiry.electrician', $userId);
			});
		});
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
