<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CRMLog;
use App\Models\Electrician;
use App\Models\Inquiry;
use App\Models\Lead;
use App\Models\SalePerson;
use App\Models\UserLog;
use App\Models\User;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

//use Illuminate\Support\Facades\Hash;

class ElectriciansController extends Controller
{

	public function __construct()
	{

		$this->middleware(function ($request, $next) {

			$tabCanAccessBy = array(0, 1, 2, 7, 101, 102, 103, 104);

			if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
				$response = errorRes("Invalid access", 401);
				return response()->json($response, $response['status_code'])->header('Content-Type', 'application/json');
			}

			return $next($request);
		});
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
			2 => 'users.phone_number',
			3 => 'electrician.sale_person_id',
			4 => 'electrician.total_point_current',
			5 => 'users.status',
			6 => 'users.last_active_date_time',

		);

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
			'sale_person.first_name as sale_person_first_name',
			'sale_person.last_name  as sale_person_last_name',

		);

		$query = Electrician::query();
		$query->leftJoin('users', 'users.id', '=', 'electrician.user_id');
		$query->leftJoin('users as sale_person', 'electrician.sale_person_id', '=', 'sale_person.id');
		$query->where('electrician.status', '!=', 0);
		if (isset($request->type) && $request->type != "") {
			$query->where('electrician.type', $request->type);
		}

		if ($isAdminOrCompanyAdmin == 1 || $isMarketingDispatcherUser == 1) {
		} else if ($isSalePerson == 1) {
			$query->whereIn('electrician.sale_person_id', $SalePersonsIds);
		} else if ($isChannelPartner != 0) {
			$query->where('electrician.added_by', Auth::user()->id);
		}
		$query->orderBy('electrician.id', 'desc');
		$query->select($selectColumns);
		//$query->limit($request->length);
		//$query->offset($request->start);
		//$query->orderBy($sortingColumns[$request['order'][0]['column']], $request['order'][0]['dir']);

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

		$data = $query->paginate(10);

		foreach ($data as $key => $value) {

			$data[$key]['status_lable'] = getElectricianStatus()[$value['status']]['header_code'];
		}



		$response = successRes("Electrician");
		$response['data'] = $data;
		return response()->json($response, $response['status_code'])->header('Content-Type', 'application/json');
	}

	public function searchSalePerson(Request $request)
	{

		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		$isMarketingDispatcherUser = isMarketingDispatcherUser();
		$isSalePerson = isSalePerson();
		$isChannelPartner = isChannelPartner(Auth::user()->type);

		$searchKeyword = isset($request->q) ? $request->q : "";

		if ($isAdminOrCompanyAdmin == 1 || $isMarketingDispatcherUser == 1 || $isSalePerson == 1) {

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
			$response = successRes("Sales Person");
			$response['data'] = $SalePerson;
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
			$response = successRes("Sales Person");
			$response['data'] = $SalePerson;
		}
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function save(Request $request)
	{

		// if(Auth::user()->type == 2 && $request->user_type == 301) {
        //     $UserType = 302;
        // } else {
        $UserType = $request->user_type;
        // }
		$user_id = isset($request->user_id) ? $request->user_id : 0;
		// $user_type = isset($request->user_type) ? $request->user_type : 301;
		$isSalePerson = isSalePerson();
		$rules = array();
		$rules['user_id'] = 'required';
		$rules['user_type'] = 'required';
		$rules['user_first_name'] = 'required';
		$rules['user_last_name'] = 'required';
		$rules['user_phone_number'] = 'required|digits:10|regex:/^[1-9][0-9]*$/';
		// $rules['user_address_line1'] = 'required';
		// $rules['user_pincode'] = 'required';
		$rules['user_country_id'] = 'required';
		$rules['user_state_id'] = 'required';
		$rules['user_city_id'] = 'required';
		$rules['user_city_id'] = 'required';
		if ($UserType == 302) {

			//$rules['user_email'] = 'required';
			$rules['user_address_line1'] = 'required';
			//$rules['user_pincode'] = 'required';

		}

		if ($isSalePerson == 0) {
			$rules['user_status'] = 'required';
			$rules['electrician_sale_person_id'] = 'required';
		}

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {

			$response = array();
			$response['status'] = 0;
			$response['msg'] = "The request could not be understood by the server due to malformed syntax";
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();
		} else {

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
			$alreadyEmail->where('status', '=', 1);

			if ($request->user_id != 0) {
				$alreadyEmail->where('id', '!=', $request->user_id);
			}
			$alreadyEmail = $alreadyEmail->first();

			$alreadyPhoneNumber = User::query();
			$alreadyPhoneNumber->where('phone_number', $request->user_phone_number);
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
					$User = new User();
					$User->created_by = Auth::user()->id;
					$User->password = Hash::make("111111");
					$User->last_active_date_time = date('Y-m-d H:i:s');
					$User->last_login_date_time = date('Y-m-d H:i:s');
					$User->avatar = "default.png";
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
				$User->address_line1 = $user_address_line1;
				$User->address_line2 = $user_address_line2;
				$User->pincode = $user_pincode;
				$User->country_id = $request->user_country_id;
				$User->state_id = $request->user_state_id;
				$User->city_id = $request->user_city_id;
				$User->company_id = $user_company_id;
				$User->type = $UserType;
				$User->reference_type = 0;
				$User->reference_id = 0;
				$User->save();

				$Electrician->user_id = $User->id;
				$Electrician->type = $UserType;
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

					$user_log = new UserLog();
                    $user_log->user_id = Auth::user()->id;
                    $user_log->log_type = "ELECTRICIAN-LOG";
                    $user_log->field_name = '';
                    $user_log->old_value = '';
                    $user_log->new_value = '';
                    $user_log->reference_type = "Electrician";
                    $user_log->reference_id = $User->id;
                    $user_log->transaction_type = "Electrician Create";
                    $user_log->description = 'New Electrician Created';
                    $user_log->source = $request->app_source;
                    $user_log->entryby = Auth::user()->id;
                    $user_log->entryip = $request->ip();
                    $user_log->save();

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

				$Electrician = json_decode(json_encode($Electrician), true);

				$User1 = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
				$User1->where('id', $Electrician['added_by']);
				$User1->limit(1);
				$User1 = $User1->first();

				if ($User1) {
					$Electrician['added_by'] = array();
					$Electrician['added_by']['id'] = $User1->id;
					$Electrician['added_by']['text'] = $User1->full_name;
				}

				$User->status_lable = getElectricianStatus()[$User->status]['header_code'];

				$isSalePerson = (Auth::user()->type == 2) ? 1 : 0;

				// if ($isSalePerson == 0 || ($isSalePerson == 1 && $Electrician['sale_person_id'] == Auth::user()->id)) {

					$salePerson = User::query();
					$salePerson->select('users.id as id', DB::raw('CONCAT(first_name," ", last_name) AS text'));
					$salePerson = $salePerson->find($Electrician['sale_person_id']);

					$User['contacts'] = getUserContactList($User['id'])['data'];
                    $User['updates'] = getUserNoteList($User['id'])['data'];
                    $User['files'] = getUserFileList($User['id'])['data'];

                    $User['calls'] = getUserAllOpenList($User['id'])['call_data'];
                    $User['meetings'] = getUserAllOpenList($User['id'])['meeting_data'];
                    $User['tasks'] = getUserAllOpenList($User['id'])['task_data'];
                    $User['max_open_actions'] = getUserAllOpenList($User['id'])['max_open_actions'];

                    $User['calls_closed'] = getUserAllCloseList($User['id'])['close_call_data'];
                    $User['meetings_closed'] = getUserAllCloseList($User['id'])['close_meeting_data'];
                    $User['tasks_closed'] = getUserAllCloseList($User['id'])['close_task_data'];
                    $User['max_close_actions'] = getUserAllCloseList($User['id'])['max_close_actions'];

					$response = successRes("Successfully get user");
					$User = json_decode(json_encode($User), true);
					$response['data'] = $User;
					$response['data']['electrician'] = $Electrician;
					$response['data']['electrician']['sale_person'] = array();
					$response['data']['electrician']['sale_person'] = $salePerson;

					$query = Lead::query();
                    $query->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                    $query->leftJoin('lead_sources as source', 'source.lead_id', '=', 'leads.id');
                    $query->leftJoin('users as lead_ele', 'lead_ele.id', '=', 'leads.electrician');
                    $query->leftJoin('users as source_user', 'source_user.id', '=', 'source.source');
                    $query->where(function ($query1) use ($request) {
                        if ($request->is_arc == 1) {
                            $query1->orwhere('leads.electrician', $request->id);
                        }
                        $query1->orwhere('lead_sources.source', $request->id);
                    });
                    $Lead_ids = $query->distinct()->pluck('leads.id');

                    $Status_count = Lead::query();
                    $Status_count->selectRaw('SUM(CASE WHEN leads.status IN (1, 2, 3, 4, 100, 101, 102) THEN 1 ELSE 0 END) as Running_lead, SUM(CASE WHEN leads.status = 103 THEN 1 ELSE 0 END) as Won_lead, SUM(CASE WHEN leads.status IN (5, 104) THEN 1 ELSE 0 END) as Lost_lead');
                    $Status_count->whereIn('leads.id',  $Lead_ids);
                    $Status_count = $Status_count->first();

					$response['data']['electrician']['running_lead'] = $Status_count->Running_lead;
					$response['data']['electrician']['won_lead'] = $Status_count->Won_lead;
					$response['data']['electrician']['lost_lead'] = $Status_count->Lost_lead;
					$response['data']['electrician']['total_lead'] = intval($Status_count->Running_lead) + intval($Status_count->Won_lead) + intval($Status_count->Lost_lead);

					$response['data']['electrician']['inquiry_running'] = $this->runningInquiry($response['data']['id']);
					$response['data']['electrician']['inquiry_material_sent'] = $this->materialSentInquiry($response['data']['id']);
					$response['data']['electrician']['inquiry_rejected'] = $this->rejectedInquiry($response['data']['id']);
					$response['data']['electrician']['inquiry_non_potential'] = $this->nonPotentialInquiry($response['data']['id']);
				// } else {
				// 	$response = errorRes("Invalid id");
				// }
			} else {
				$response = errorRes("Invalid id");
			}
		} else {
			$response = errorRes("Invalid id");
		}
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	function runningInquiry($userId)
	{
		$inquiryStatus = getInquiryStatus();

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

		$statusArray = isset($inquiryStatus[201]['for_sales_ids']) ? $inquiryStatus[201]['for_sales_ids'] : array(0);
		$query->whereIn('inquiry.status', $statusArray);

		return $query->count();
	}

	function nonPotentialInquiry($userId)
	{
		$inquiryStatus = getInquiryStatus();

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

		$statusArray = isset($inquiryStatus[101]['for_sales_ids']) ? $inquiryStatus[101]['for_sales_ids'] : array(0);
		$query->whereIn('inquiry.status', $statusArray);

		return $query->count();
	}

	function materialSentInquiry($userId)
	{
		$inquiryStatus = getInquiryStatus();

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

		$statusArray = isset($inquiryStatus[9]['for_sales_ids']) ? $inquiryStatus[9]['for_sales_ids'] : array(0);
		$query->whereIn('inquiry.status', $statusArray);

		return $query->count();
	}

	function rejectedInquiry($userId)
	{
		$inquiryStatus = getInquiryStatus();

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

		$statusArray = isset($inquiryStatus[102]['for_sales_ids']) ? $inquiryStatus[102]['for_sales_ids'] : array(0);
		$query->whereIn('inquiry.status', $statusArray);

		return $query->count();
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

		);

		$query = Electrician::query();
		$query->select($columns);
		$query->leftJoin('users', 'users.id', '=', 'electrician.user_id');
		$query->leftJoin('users as sale_person', 'electrician.sale_person_id', '=', 'sale_person.id');
		$query->where('electrician.type', $request->type);
		if ($isAdminOrCompanyAdmin == 1 || $isMarketingDispatcherUser == 1) {
		} else if ($isSalePerson == 1) {
			$query->whereIn('electrician.sale_person_id', $SalePersonsIds);
		} else if ($isChannelPartner != 0) {
			$query->where('electrician.added_by', Auth::user()->id);
		}

		$query->orderBy('electrician.id', 'desc');
		$data = $query->get();

		if ($request->type == 301) {
			$headers = array("#ID", "Firstname", "Lastname", "Phone", "Status", "Created", "SalePerson");
		} else {
			$headers = array("#ID", "Firstname", "Lastname", "Email", "Phone", "Status", "Created", "SalePerson");
		}

		header('Content-Type: text/csv');
		if ($request->type == 301) {
			header('Content-Disposition: attachment; filename="electricians-non-prime.csv"');
		} else {
			header('Content-Disposition: attachment; filename="electricians-prime.csv"');
		}
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

			if ($request->type == 301) {

				$lineVal = array(
					$value->id,
					$value->first_name,
					$value->last_name,
					$value->dialing_code . " " . $value->phone_number,
					$status,
					$createdAt,
					$value->sale_person_first_name . ' ' . $value->sale_person_last_name,

				);
			} else if ($request->type == 302) {

				$lineVal = array(
					$value->id,
					$value->first_name,
					$value->last_name,
					$value->email,
					$value->dialing_code . " " . $value->phone_number,
					$status,
					$createdAt,
					$value->sale_person_first_name . ' ' . $value->sale_person_last_name,

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

		$selectColumns = array(
			'crm_log.description',
			'leads.id as lead_id',
			'crm_log.order_id',
		);

		$query = CRMLog::query();
        $query->select($selectColumns);
		$query->leftJoin('leads',function ($join) {
			$join->on('leads.inquiry_id', '=' , 'crm_log.inquiry_id') ;
			$join->where('crm_log.inquiry_id','!=','0') ;
		});
        $query->where('for_user_id', $request->user_id);
        $query->whereIn('name', array('point-gain', 'point-redeem'));
		$query->orderBy('crm_log.id', 'desc');

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

		$data = $query->paginate(10);
		$response = successRes("Electrician Point Log");
		$response['data'] = $data;
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function inquiryLog(Request $request)
	{

		$rules = array();
		$rules['user_id'] = 'required';

		$customMessage = array();
		$customMessage['user_id.required'] = "Invalid parameters";

		$validator = Validator::make($request->all(), $rules, $customMessage);

		if ($validator->fails()) {

			$response = errorRes("Validation Error", 400);
			$response['data'] = $validator->errors();
			return response()->json($response, $response['status_code'])->header('Content-Type', 'application/json');
		} else {

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
				'answer_date_time',

			);

			$userId = $request->user_id;

			// $query = Inquiry::query();
			// $query->where(function ($query2) use ($userId) {

			// 	$query2->where(function ($query3) use ($userId) {

			// 		$query3->whereIn('inquiry.source_type', array("user-301", "user-302"));
			// 		$query3->where('inquiry.source_type_value', $userId);

			// 	});

			// 	$query2->orWhere(function ($query3) use ($userId) {

			// 		$query3->whereIn('inquiry.source_type_1', array("user-301", "user-302"));
			// 		$query3->where('inquiry.source_type_value_1', $userId);

			// 	});

			// 	$query2->orWhere(function ($query3) use ($userId) {

			// 		$query3->whereIn('inquiry.source_type_2', array("user-301", "user-302"));
			// 		$query3->where('inquiry.source_type_value_2', $userId);

			// 	});

			// 	$query2->orWhere(function ($query3) use ($userId) {

			// 		$query3->whereIn('inquiry.source_type_3', array("user-301", "user-302"));
			// 		$query3->where('inquiry.source_type_value_3', $userId);

			// 	});

			// 	$query2->orWhere(function ($query3) use ($userId) {

			// 		$query3->whereIn('inquiry.source_type_4', array("user-301", "user-302"));
			// 		$query3->where('inquiry.source_type_value_4', $userId);

			// 	});

			// 	$query2->orWhere(function ($query3) use ($userId) {

			// 		$query3->where('inquiry.electrician', $userId);

			// 	});

			// });

			// $recordsTotal = $query->count();

			// $recordsFiltered = $recordsTotal;
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
			// $query->limit($request->length);
			// $query->offset($request->start);
			// $query->orderBy($sortingColumns[$request['order'][0]['column']], $request['order'][0]['dir']);
			$query->orderBy('inquiry.id', 'desc');
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

			$data = $query->paginate(10);
			$response = successRes("Electrician Inquiry Log");
			$response['data'] = $data;
			return response()->json($response)->header('Content-Type', 'application/json');
		}
	}

	public function leadLog(Request $request)
	{
        
        $LeadIds = Lead::query();
        $LeadIds->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
        $LeadIds->where(function ($query) use ($request) {
            $query->orwhere('leads.electrician', $request->user_id);
            $query->orwhere('lead_sources.source', $request->user_id);
        });
        $LeadIds->where('is_deal', $request->type);
        if($request->type == 0) {
            if($request->status_type == 2) {
                $LeadIds->whereIn('leads.status', [1, 2, 3, 4]);
            } else if($request->status_type == 3) {
                $LeadIds->where('leads.status', 5);
            } else if($request->status_type == 4) {
                $LeadIds->where('leads.status', 6);
            }
        } else if($request->type == 1) {
            if($request->status_type == 2) {
                $LeadIds->whereIn('leads.status', [100, 101, 102]);
            } else if($request->status_type == 3) {
                $LeadIds->where('leads.status', 103);
            } else if($request->status_type == 4) {
                $LeadIds->where('leads.status', 104);
            } else if($request->status_type == 5) {
                $LeadIds->where('leads.status', 105);
            }
        }
        $LeadIds = $LeadIds->distinct()->pluck('leads.id');

        $selectColumns = array(
            'leads.id',
            'leads.first_name',
            'leads.last_name',
            'leads.status',
            'leads.architect',
            'leads.source_type',
            'leads.source',
			'leads.total_point',
            'wltrn_quotation.quot_total_amount',
            'crm_setting_stage_of_site.name',
        );

        $DealData = Lead::query();
        $DealData->select($selectColumns);
        $DealData->selectRaw('crm_setting_stage_of_site.name as site_stage_name');
        // $DealData->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
        $DealData->leftJoin('crm_setting_stage_of_site', 'crm_setting_stage_of_site.id', '=', 'leads.site_stage');
        $DealData->leftJoin('wltrn_quotation', function($join) {
            $join->on('wltrn_quotation.inquiry_id', '=', 'leads.id');
            $join->where('wltrn_quotation.isfinal', '=', 1);
        });
        $DealData->whereIn('leads.id', $LeadIds);
        // $DealData->where('lead_sources.is_main', 1);
        $DealData->groupBy($selectColumns);
        $data = $DealData->paginate();

        foreach ($data as $key => $value) {
			$data[$key]['client_name'] = $value->first_name .' '. $value->last_name;
            if($value['architect'] != 0) {
                $user = User::select(DB::raw('CONCAT(users.first_name," ",users.last_name) AS arc_name'))->where('id', $value['architect'])->first();
                $data[$key]['architect'] = $user->arc_name;
            } else {
                $data[$key]['architect'] = "";
            }

            if($value['quot_total_amount'] == null) {
                $data[$key]['quotation_amount'] = 0;
            } else {
                $data[$key]['quotation_amount'] = $value['quot_total_amount'];
            }

            if($value['is_deal'] == 1) {
                $data[$key]['data_id'] = '#D'.$value['id'];
            } else {
                $data[$key]['data_id'] = '#L'.$value['id'];
            }


            $source_type_pieces = explode("-", $value['source_type']);
            if ($source_type_pieces[0] == "user") {
                if (isChannelPartner($source_type_pieces[1]) != 0) {

                    $User1 = User::select(DB::raw("channel_partner.firm_name"));
                    $User1->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
                    $User1->where('users.id', $value['source']);
                    $User1->limit(1);
                    $User1 = $User1->first();

                    if ($User1) {
                        $data[$key]['source'] = $User1->firm_name;
                    } else {
                        $data[$key]['source'] = "";
                    }
                } else {

                    $User1 = User::select(DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
                    $User1->where('users.id', $value['source']);
                    $User1->limit(1);
                    $User1 = $User1->first();

                    if ($User1) {
                        $data[$key]['source'] = $User1->full_name;
                    } else {
                        $data[$key]['source'] = "";
                    }
                }
            } else {
                $data[$key]['source'] = $value['source'];
            }
            $data[$key]['status_label'] = getLeadStatus()[$value['status']]['name'];
        };
        $response = successRes("Electrician Lead Log");
		$response['data'] = $data;
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function getElectricianLogCount(Request $request) {
        $selectColumns = array(
            'leads.id',
            'leads.first_name',
            'leads.last_name',
            'leads.status',
            'leads.is_deal',
            'wltrn_quotation.quot_total_amount',
        );

        $query = Lead::query();
        $query->select($selectColumns);
        $query->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
        $query->leftJoin('wltrn_quotation', function($join) {
            $join->on('wltrn_quotation.inquiry_id', '=', 'leads.id');
            $join->where('wltrn_quotation.isfinal', '=', 1);
        });
        $query->where(function ($query) use ($request) {
            $query->orwhere('leads.electrician', $request->user_id);
            $query->orwhere('lead_sources.source', $request->user_id);
        });
		$query->groupBy($selectColumns);
        $Lead_ids = $query->distinct()->pluck('leads.id');
        $query = $query->get();

       

        $lead = array();
        $lead['id'] = 0;
        $lead['text'] = "Lead";
        $lead['count'] = 0;
        $lead['status_list'] = array();

        $deal = array();
        $deal['id'] = 1;
        $deal['text'] = "Deal";
        $deal['count'] = 0;
        $deal['status_list'] = array();

        foreach ($query as $key => $value) {
            $Status_count = Lead::query();
            if($value->is_deal == 0) {
                $Status_count->selectRaw('SUM(CASE WHEN leads.status IN (1, 2, 3, 4) THEN 1 ELSE 0 END) as LeadRunning, SUM(CASE WHEN leads.status = 5 THEN 1 ELSE 0 END) as LeadLost, SUM(CASE WHEN leads.status =  6 THEN 1 ELSE 0 END) as LeadCold');
            } else {
                $Status_count->selectRaw('SUM(CASE WHEN leads.status IN (100, 101, 102) THEN 1 ELSE 0 END) as Running, SUM(CASE WHEN leads.status = 103 THEN 1 ELSE 0 END) as Won, SUM(CASE WHEN leads.status = 104 THEN 1 ELSE 0 END) as Lost, SUM(CASE WHEN leads.status =  105 THEN 1 ELSE 0 END) as Cold');
            }
            $Status_count->whereIn('leads.id',  $Lead_ids);
            $Status_count = $Status_count->first();


            if($value->is_deal == 0) {
                $lead['id'] = $value->is_deal;
                $lead['text'] = 'Lead';
                $lead['count'] = $lead['count'] + 1;

                $lead['status_list'][0]['id'] = 1;
                $lead['status_list'][0]['text'] = "Total";
                $lead['status_list'][0]['count'] = (int)$Status_count->LeadRunning + (int)$Status_count->LeadLost + (int)$Status_count->LeadCold;

                $lead['status_list'][1]['id'] = 2;
                $lead['status_list'][1]['text'] = "Running";
                $lead['status_list'][1]['count'] = $Status_count->LeadRunning;

                $lead['status_list'][2]['id'] = 3;
                $lead['status_list'][2]['text'] = "Lost";
                $lead['status_list'][2]['count'] = $Status_count->LeadLost;

                $lead['status_list'][3]['id'] = 4;
                $lead['status_list'][3]['text'] = "Cold";
                $lead['status_list'][3]['count'] = $Status_count->LeadCold;

            } else {
                $deal['id'] = $value->is_deal;
                $deal['text'] = 'Deal';
                $deal['count'] = $deal['count'] + 1;

                $deal['status_list'][0]['id'] = 1;
                $deal['status_list'][0]['text'] = "Total";
                $deal['status_list'][0]['count'] = (int)$Status_count->Running + (int)$Status_count->Won + (int)$Status_count->Lost + (int)$Status_count->Cold;

                $deal['status_list'][1]['id'] = 2;
                $deal['status_list'][1]['text'] = "Running";
                $deal['status_list'][1]['count'] = $Status_count->Running;

                $deal['status_list'][2]['id'] = 3;
                $deal['status_list'][2]['text'] = "Won";
                $deal['status_list'][2]['count'] = $Status_count->Won;

                $deal['status_list'][3]['id'] = 4;
                $deal['status_list'][3]['text'] = "Lost";
                $deal['status_list'][3]['count'] = $Status_count->Lost;

                $deal['status_list'][4]['id'] = 5;
                $deal['status_list'][4]['text'] = "Cold";
                $deal['status_list'][4]['count'] = $Status_count->Cold;
            }
        }

        $data= array();
        $data['lead'] = $lead;
        $data['deal'] = $deal;

        $response = successRes("Architect Lead Log Count");
		$response['data'] = $data;
		return response()->json($response)->header('Content-Type', 'application/json');
    }
}
