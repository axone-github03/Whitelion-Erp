<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\ExhibitionInquiry;
use App\Models\Exhibition;
use App\Models\ExhibitionInquiryUpdate;
use App\Models\Inquiry;
use App\Models\InquiryQuestionOption;
use App\Models\InquiryUpdate;
use App\Models\User;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Architect;
use App\Models\Electrician;
use App\Models\CityList;
use Illuminate\Support\Facades\Hash;

class InquiryExhibitionController extends Controller
{

	public function __construct()
	{

		$this->middleware(function ($request, $next) {

			$tabCanAccessBy = array(0, 1, 9);

			if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
				return redirect()->route('dashboard');
			}

			return $next($request);
		});
	}

	// searchExhibition
	public function searchExhibition(Request $request)
	{
		$FYList = array();
		$FYList = Exhibition::select('id', 'name as text');
		// $GroupList->where('company_id', $request->company_id);
		$FYList->where('name', 'like', "%" . $request->q . "%");
		$FYList->limit(5);
		$FYList = $FYList->get();

		$FYList_final = array();

		foreach ($FYList as $key => $value) {
			if ($key == 0) {
				$FYList_new1['id'] = 0;
				$FYList_new1['text'] = 'All';
				$FYList_new['id'] = $value['id'];
				$FYList_new['text'] = $value['text'];
				array_push($FYList_final, $FYList_new1);
				array_push($FYList_final, $FYList_new);
			} else {
				$FYList_new['id'] = $value['id'];
				$FYList_new['text'] = $value['text'];
				array_push($FYList_final, $FYList_new);
			}
		}

		$response = array();
		$response['results'] = $FYList_final;
		$response['pagination']['more'] = false;

		return response()->json($response)->header('Content-Type', 'application/json');
	}
	public function searchUserType(Request $request)
	{
		$FYList = array();
		$FYList = ExhibitionInquiry::select('type');
		// $GroupList->where('company_id', $request->company_id);
		$FYList->where('type', 'like', "%" . $request->q . "%");
		$FYList->groupBy('type');
		$FYList->limit(5);
		$FYList = $FYList->get();

		$FYList_final = array();

		foreach ($FYList as $key => $value) {
			if ($key == 0) {
				$FYList_new1['id'] = 'All';
				$FYList_new1['text'] = 'All';
				$FYList_new['id'] = $value['type'];
				$FYList_new['text'] = $value['type'];
				array_push($FYList_final, $FYList_new1);
				array_push($FYList_final, $FYList_new);
			} else {
				$FYList_new['id'] = $value['type'];
				$FYList_new['text'] = $value['type'];
				array_push($FYList_final, $FYList_new);
			}
		}

		$response = array();
		$response['results'] = $FYList_final;
		$response['pagination']['more'] = false;

		return response()->json($response)->header('Content-Type', 'application/json');
	}
	public function searchInquiryConverted(Request $request)
	{

		$FYList_final = array();

		$FYList_final[0]['id'] = 0;
		$FYList_final[0]['text'] = 'All';
		$FYList_final[1]['id'] = 1;
		$FYList_final[1]['text'] = 'Converted To Inquiry';
		$FYList_final[2]['id'] = 2;
		$FYList_final[2]['text'] = 'Inquiry Not Converted';

		$response = array();
		$response['results'] = $FYList_final;
		$response['pagination']['more'] = false;

		return response()->json($response)->header('Content-Type', 'application/json');
	}
	function getInquiryTimeSlot()
	{
		$timeSlot = array();
		$strtotimeStart = strtotime(date('00:00:00'));
		$latestDateTime = date('00:00:00', $strtotimeStart);
		$i = 0;
		$timeSlot[$i] = date('h:i A', strtotime($latestDateTime . " +30 minutes"));
		for ($i = 1; $i < 48; $i++) {
			$timeSlot[$i] = date('h:i A', strtotime($latestDateTime . " +30 minutes"));
			$latestDateTime = $timeSlot[$i];
		}
		return $timeSlot;
	}

	public function index()
	{

		$data = array();
		$data['title'] = "Exhibition Inquiry";
		$stageOfSiteOptions = InquiryQuestionOption::select('id', 'option')->where('inquiry_question_id', 7)->orderBy('id', 'asc')->get();

		$data['stage_of_site'] = $stageOfSiteOptions;
		$data['isArchitect'] = isArchitect();
		$data['isElectrician'] = isElectrician();
		$data['isSalePerson'] = isSalePerson();
		$data['isAdminOrCompanyAdmin'] = isAdminOrCompanyAdmin();
		$data['isChannelPartner'] = isChannelPartner(Auth::user()->type);
		$data['timeSlot'] = $this->getInquiryTimeSlot();
		$data['no_of_inquiry_request'] = 0;
		$data['isThirdPartyUser'] = isThirdPartyUser();
		$data['isTaleSalesUser'] = isTaleSalesUser();

		$data['timeSlot'] = $this->getInquiryTimeSlot();

		return view('crm/inquiry/exhibition', compact('data'));
	}

	function ajax(Request $request)
	{
		DB::enableQueryLog();
		$searchColumns = array(

			0 => 'exhibition_inquiry.id',
			1 => 'exhibition_inquiry.first_name',
			2 => 'exhibition_inquiry.last_name',
			3 => 'exhibition.name',
			4 => 'exhibition_inquiry.firm_name',
			5 => 'exhibition_inquiry.phone_number',
			6 => 'city_list.name',
		);

		$columns = array(
			0 => 'exhibition_inquiry.id',
			1 => 'exhibition_inquiry.first_name',
			2 => 'exhibition_inquiry.phone_number',
			3 => 'exhibition_inquiry.firm_name',
			4 => 'exhibition_inquiry.id',
			5 => 'exhibition_inquiry.last_name',
			6 => 'exhibition_inquiry.inquiry_id',
			7 => 'exhibition_inquiry.type',
			8 => 'exhibition_inquiry.source',
			9 => 'exhibition_inquiry.plan_type',
			10 => 'exhibition_inquiry.remark',
			11 => 'exhibition_inquiry.stage_of_site',
			12 => 'exhibition_inquiry.link_user_id',
			13 => 'exhibition.name as exhibition_name',
			14 => 'city_list.name as city_name',
			15 => 'exhibition_inquiry.created_at',

		);

		$recordsTotal = ExhibitionInquiry::query();
		if ($request->exhibition_filter != 0) {
			$recordsTotal->where('exhibition_inquiry.exhibition_id', $request->exhibition_filter);
		}
		if (isset($request->usertype_filter)) {
			if ($request->usertype_filter != 'All') {
				$recordsTotal->where('exhibition_inquiry.type', $request->usertype_filter);
			}
		}
		if (isset($request->usertype_filter)) {
			if ($request->isconvertinquiry_filter != 0) {
				if ($request->isconvertinquiry_filter == 1) {
					$recordsTotal->where('exhibition_inquiry.inquiry_id', '<>', '0');
				} elseif ($request->isconvertinquiry_filter == 2) {
					$recordsTotal->where('exhibition_inquiry.inquiry_id', 'IS NULL', null, 'and');
				}
			}
		}
		$recordsTotal = $recordsTotal->get();
		$recordsFiltered = count(json_decode(json_encode($recordsTotal), true)); // when there is no search parameter then total number rows = total number filtered rows.

		$query = ExhibitionInquiry::query();
		$query->select($columns);
		$query->selectRaw('CONCAT(users.first_name," ", users.last_name) AS created_by');
		$query->leftJoin('exhibition', 'exhibition.id', '=', 'exhibition_inquiry.exhibition_id');
		$query->leftJoin('city_list', 'city_list.id', '=', 'exhibition_inquiry.city_id');
		$query->leftJoin('users', 'users.id', '=', 'exhibition_inquiry.user_id');
		$query->limit($request->length);
		$query->offset($request->start);
		$query->orderBy($columns[$request['order'][0]['column']], $request['order'][0]['dir']);
		$isFilterApply = 0;

		if ($request->exhibition_filter != 0) {
			$query->where('exhibition_inquiry.exhibition_id', $request->exhibition_filter);
		}
		if (isset($request->usertype_filter)) {
			if ($request->usertype_filter != 'All') {
				$query->where('exhibition_inquiry.type', $request->usertype_filter);
			}
		}
		if ($request->isconvertinquiry_filter != 0) {
			if ($request->isconvertinquiry_filter == 1) {
				$query->where('exhibition_inquiry.inquiry_id', '<>', '0');
			} elseif ($request->isconvertinquiry_filter == 2) {
				$query->where('exhibition_inquiry.inquiry_id', 'IS NULL', null, 'and');
			}
		}

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

			$data[$key]['exhibition_name'] = $value['exhibition_name'];
			$data[$key]['city_name'] = $value['city_name'];

			if ($value['inquiry_id'] == 0) {

				$ExhibitionInquiryUpdate = ExhibitionInquiryUpdate::where('exhibition_inquiry_id', $value['id'])->orderBy('id', 'desc')->count();

				$uiAction = '<ul class="list-inline font-size-13 contact-links mb-0">';
				$uiAction .= $value['first_name'] . " " . $value['last_name'];
				$uiAction .= '<li class="list-inline-item px-2">';
				if ($ExhibitionInquiryUpdate == 0) {
					$uiAction .= '<button type="button" class="btn  position-relative btn-detail hightlight-update" onclick="getDetail(' . $data[$key]['id'] . ')"  ><i class="fas fa-comments inquiry-comments-icon"></i><span class="position-absolute top-0 start-100 translate-middle badge rounded-pill inquiry-update-badge "></button>';
				} else {

					$uiAction .= '<button type="button" class="btn  position-relative btn-detail hightlight-update" onclick="getDetail(' . $data[$key]['id'] . ')"  ><i class="fas fa-comments inquiry-comments-icon"></i><span class="position-absolute top-0 start-100 translate-middle badge rounded-pill inquiry-update-badge ">' . $ExhibitionInquiryUpdate . '<span class="visually-hidden">unread messages</span>
					</span></button>';
				}



				$uiAction .= '</li>';
				$uiAction .= '</ul>';
			} else {

				$uiAction = '';
			}


			$data[$key]['name'] = $uiAction;
			$data[$key]['created_by'] = '<ul class="list-inline font-size-13 contact-links mb-0">'.$value['created_by'].'<li class="list-inline-item px-2 "><a data-bs-toggle="tooltip" href="javascript: void(0);" title="Created Date & Time : ' . convertDateTime($value['created_at']) . '"><i class="bx bx-calendar"></i></a></li></ul>';

			if ($value['inquiry_id'] == 0 && $value['link_user_id'] == 0) {
				$uiAction = '<ul class="list-inline font-size-20 mb-0">';

				// $uiAction .= '<li class="list-inline-item px-2">';
				// $uiAction .= '<a onclick="getDetail(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Edit"><i class="fas fa-comments"></i></a>';
				// $uiAction .= '</li>';

				$uiAction .= '<li class="list-inline-item px-2">';
				$uiAction .= '<a class="btn btn-sm btn-primary" onclick="editView(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Edit">Convert to Inquiry</a>';
				$uiAction .= '</li>';

				$uiAction .= '<li class="list-inline-item px-2">';
				$uiAction .= '<a class="btn btn-sm btn-primary" id="' . $value['id'] . '" onclick="convertToUser(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Edit">Convert to User</a>';
				$uiAction .= '</li>';


				$uiAction .= '</ul>';
			} else {

				if ($value['inquiry_id'] != 0) {
					$uiAction = "Inquiry #" . $value['inquiry_id'];
				} else if ($value['link_user_id'] != 0) {
					$uiAction = "User #" . $value['link_user_id'];
				}
			}

			$data[$key]['action'] = $uiAction;
		}

		$jsonData = array(
			"draw" => intval($request['draw']),
			// for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
			"recordsTotal" => $recordsTotal,
			// total number of records
			"recordsFiltered" => intval($recordsFiltered),
			// total number of records after searching, if there is no searching then totalFiltered = totalData
			"data" => $data, // total data array

		);
		return $jsonData;
	}

	function detail(Request $request)
	{

		$ExhibitionInquiry = ExhibitionInquiry::find($request->id);
		if ($ExhibitionInquiry) {
			$ExhibitionInquiry = json_decode(json_encode($ExhibitionInquiry), true);

			$User = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
			$User->where('id', $ExhibitionInquiry['user_id']);
			$User->where('type', 2);
			$User->limit(1);
			$User = $User->first();
			$ExhibitionInquiry['assigned'] = 0;
			if ($User) {

				$ExhibitionInquiry['assigned'] = $User->id;

				$ExhibitionInquiry['assigned_to'] = array();
				$ExhibitionInquiry['assigned_to']['id'] = $User->id;
				$ExhibitionInquiry['assigned_to']['text'] = $User->full_name;
			}

			$InquiryQuestionOption = InquiryQuestionOption::select('id', 'option')->where('inquiry_question_id', 7)->where('option', $ExhibitionInquiry['stage_of_site'])->orderBy('id', 'asc')->first();
			if ($InquiryQuestionOption) {
				$ExhibitionInquiry['stage_of_site_id'] = $InquiryQuestionOption->id;
			} else {
				$ExhibitionInquiry['stage_of_site_id'] = 0;
			}

			$ExhibitionInquiry['city'] = array();
			$ExhibitionInquiry['city']['id'] = $ExhibitionInquiry['city_id'];
			$ExhibitionInquiry['city']['text'] = getCityName($ExhibitionInquiry['city_id']);

			$response = successRes("Successfully exhibition inquiry");
			$response['data'] = $ExhibitionInquiry;
		} else {
			$response = errorRes("Invalid id");
		}
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	function save(Request $request)
	{

		$rules = array();
		$rules['inquiry_first_name'] = 'required';
		$rules['inquiry_last_name'] = 'required';
		$rules['inquiry_phone_number'] = 'required|digits:10|regex:/^[1-9][0-9]*$/';
		$rules['inquiry_house_no'] = 'required';
		$rules['inquiry_society_name'] = 'required';
		$rules['inquiry_area'] = 'required';
		$rules['inquiry_city_id'] = 'required';
		$rules['pre_inquiry_questions_7'] = 'required';
		$rules['inquiry_assigned_to'] = 'required';

		$inquiry_pincode = isset($request->inquiry_pincode) ? $request->inquiry_pincode : '';

		$customMessage = array();
		$customMessage['inquiry_first_name.required'] = 'Please enter first name';
		$customMessage['inquiry_last_name.required'] = 'Please enter last name';
		$customMessage['inquiry_phone_number.required'] = 'Please enter phone number';
		$customMessage['inquiry_house_no.required'] = 'Please enter house no';
		$customMessage['inquiry_society_name.required'] = 'Please enter society name';
		$customMessage['inquiry_area.required'] = 'Please enter area';
		$customMessage['inquiry_pincode.required'] = 'Please enter pincode';
		$customMessage['inquiry_city_id.required'] = 'Please select city';
		$customMessage['inquiry_source_type.required'] = 'Please select source type';
		$customMessage['inquiry_follow_up_type.required'] = 'Please select follow up type';
		$customMessage['inquiry_follow_up_date.required'] = 'Please select follow up date';
		$customMessage['inquiry_follow_up_time.required'] = 'Please select follow up time';
		$customMessage['inquiry_source_type.required'] = 'Please select source type';
		$customMessage['inquiry_source_user.required'] = 'Please select source ';
		$customMessage['inquiry_source_text.required'] = 'Please select source ';
		$customMessage['inquiry_assigned_to.required'] = 'Please select assigned to';
		$customMessage['inquiry_architect.required'] = 'Please select architect';
		$customMessage['inquiry_electrician.required'] = 'Please select electrician';
		$customMessage['pre_inquiry_questions_7.required'] = 'Please select stage of site';

		$validator = Validator::make($request->all(), $rules, $customMessage);

		if ($validator->fails()) {
			$response = array();
			$response['status'] = 0;
			$response['msg'] = "The request could not be understood by the server due to malformed syntax";
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();
		} else {

			$ExhibitionInquiry = ExhibitionInquiry::find($request->exhibition_inquiry_id);


			$isAlreadyInquiry = Inquiry::select('id')->where('phone_number', $request->inquiry_phone_number)->first();

			if ($isAlreadyInquiry) {

				$response = errorRes("Inquiry already registed with phone number, Please use another phone number");
				return response()->json($response)->header('Content-Type', 'application/json');
			}

			$stage_of_site = isset($request->pre_inquiry_questions_7) ? $request->pre_inquiry_questions_7 : '';
			$phone_number2 = isset($request->inquiry_phone_number2) ? $request->inquiry_phone_number2 : '';

			$assigned_to = isset($request->inquiry_assigned_to) ? $request->inquiry_assigned_to : Auth::user()->id;

			$inquiry_follow_up_type = $request->inquiry_follow_up_type;
			$inquiry_follow_up_date_time = date('Y-m-d H:i:s', strtotime($request->inquiry_follow_up_date . " " . $request->inquiry_follow_up_time));

			if ($stage_of_site != "") {

				$Option = InquiryQuestionOption::find($stage_of_site);
				if ($Option) {
					$stage_of_site = $Option->option;
				} else {
					$stage_of_site = "";
				}
			}

			$Inquiry = new Inquiry();
			$Inquiry->answer_date_time = date('Y-m-d H:i:s');
			$Inquiry->stage_of_site_date_time = date('Y-m-d H:i:s');
			$Inquiry->status = 1;
			$Inquiry->user_id = Auth::user()->id;
			$Inquiry->assigned_to = $assigned_to;
			$Inquiry->first_name = $request->inquiry_first_name;
			$Inquiry->last_name = $request->inquiry_last_name;
			$Inquiry->phone_number = $request->inquiry_phone_number;
			$Inquiry->phone_number2 = $phone_number2;
			$Inquiry->pincode = $inquiry_pincode;
			$Inquiry->city_id = $request->inquiry_city_id;
			$Inquiry->house_no = $request->inquiry_house_no;
			$Inquiry->society_name = $request->inquiry_society_name;
			$Inquiry->area = $request->inquiry_area;
			$Inquiry->stage_of_site = $stage_of_site;
			$Inquiry->follow_up_type = $inquiry_follow_up_type;
			$Inquiry->follow_up_date_time = $inquiry_follow_up_date_time;
			$Inquiry->source_type_lable = "Exhibition";
			$Inquiry->source_type = "exhibition-9";
			$Inquiry->source_type_value = $ExhibitionInquiry->exhibition_id;

			$Inquiry->save();

			if ($Inquiry) {

				$ExhibitionInquiryUpdate = ExhibitionInquiryUpdate::where('exhibition_inquiry_id', $request->exhibition_inquiry_id)->orderBy('id', 'asc')->get();

				$conversion = array();
				foreach ($ExhibitionInquiryUpdate as $key => $value) {

					$replyId = 0;

					if ($value->reply_id != 0) {
						$replyId = $conversion[$value->reply_id];
					}
					$InquiryUpdate = new InquiryUpdate();
					$InquiryUpdate->user_id = $value->user_id;
					$InquiryUpdate->inquiry_id = $Inquiry->id;
					$InquiryUpdate->message = $value->message;
					$InquiryUpdate->reply_id = $replyId;
					$InquiryUpdate->created_at = $value->created_at;
					$InquiryUpdate->updated_at = $value->updated_at;
					$InquiryUpdate->save();
					$conversion[$value->id] = $InquiryUpdate->id;
				}

				$assignedTo = User::select('first_name', 'last_name')->find($Inquiry->assigned_to);
				$assignedToName = "";
				if ($assignedTo) {
					$assignedToName = $assignedTo->first_name . " " . $assignedTo->last_name;
				}

				$response = successRes("Successfully added inquiry");
				$debugLog = array();
				$debugLog['inquiry_id'] = $Inquiry->id;
				$debugLog['name'] = "add";
				$debugLog['description'] = "inquiry #" . $Inquiry->id . "(" . $Inquiry->first_name . ' ' . $Inquiry->last_name . ") has been created and assigned to " . $assignedToName;
				saveInquiryLog($debugLog);

				$ExhibitionInquiry->inquiry_id = $Inquiry->id;
				$ExhibitionInquiry->save();
			} else {

				$response = errorRes("Something went wrong");
				return response()->json($response)->header('Content-Type', 'application/json');
			}
		}
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function searchAssignedUser(Request $request)
	{

		$isSalePerson = isSalePerson();
		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		$isThirdPartyUser = isThirdPartyUser();
		$isServiceUser = isServiceExecutiveUser();
		$isChannelPartner = isChannelPartner(Auth::user()->type);
		if ($isSalePerson == 1) {
			$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
		} else if ($isChannelPartner != 0) {
			// $channelPartnersSalesPersons = getChannelPartnerSalesPersonsIds(Auth::user()->id);
		}

		$User = $UserResponse = array();
		$q = $request->q;
		$User = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));

		$User->where('users.status', 1);

		if ($isAdminOrCompanyAdmin == 1) {

			$User->whereIn('users.type', array(0, 1, 2));
		} else if ($isThirdPartyUser == 1) {

			$User->whereIn('users.type', array(2));
			$User->where('users.city_id', Auth::user()->city_id);
		} else if ($isSalePerson == 1) {

			$User->where('users.type', 2);
			$User->whereIn('users.id', $childSalePersonsIds);
		} else if ($isChannelPartner != 0) {

			$User->where('users.type', 2);
			$User->where('users.city_id', Auth::user()->city_id);
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

	public function detail2(Request $request)
	{

		$Inquiry = ExhibitionInquiry::find($request->inquiry_id);
		$data['ui_type'] = $request->ui_type;
		$data['inquiry_id'] = $request->inquiry_id;

		$data['update'] = ExhibitionInquiryUpdate::select('exhibition_inquiry_update.id', 'exhibition_inquiry_update.message', 'exhibition_inquiry_update.created_at', 'exhibition_inquiry_update.user_id', 'users.first_name', 'users.last_name')->leftJoin('users', 'exhibition_inquiry_update.user_id', '=', 'users.id')->where('exhibition_inquiry_update.reply_id', 0)->where('exhibition_inquiry_update.exhibition_inquiry_id', $request->inquiry_id)->orderBy('exhibition_inquiry_update.id', 'desc')->get();

		foreach ($data['update'] as $key => $value) {

			// $InquiryUpdateSeen = InquiryUpdateSeen::where('inquiry_update_id', $value->id)->where('inquiry_id', $Inquiry->id)->where('user_id', Auth::user()->id)->first();

			// if (!$InquiryUpdateSeen) {

			// 	$InquiryUpdateSeen = new InquiryUpdateSeen();
			// 	$InquiryUpdateSeen->inquiry_update_id = $value->id;
			// 	$InquiryUpdateSeen->inquiry_id = $Inquiry->id;
			// 	$InquiryUpdateSeen->user_id = Auth::user()->id;
			// 	$InquiryUpdateSeen->save();
			// }

			$data['update'][$key]['reply'] = ExhibitionInquiryUpdate::select('exhibition_inquiry_update.id', 'exhibition_inquiry_update.message', 'exhibition_inquiry_update.created_at', 'exhibition_inquiry_update.user_id', 'users.first_name', 'users.last_name')->leftJoin('users', 'exhibition_inquiry_update.user_id', '=', 'users.id')->where('exhibition_inquiry_update.reply_id', $value->id)->where('exhibition_inquiry_update.exhibition_inquiry_id', $request->inquiry_id)->orderBy('exhibition_inquiry_update.id', 'asc')->get();

			foreach ($data['update'][$key]['reply'] as $keyR => $valueR) {

				// $InquiryUpdateSeen = InquiryUpdateSeen::where('inquiry_update_id', $valueR->id)->where('inquiry_id', $Inquiry->id)->where('user_id', Auth::user()->id)->first();

				// if (!$InquiryUpdateSeen) {

				// 	$InquiryUpdateSeen = new InquiryUpdateSeen();
				// 	$InquiryUpdateSeen->inquiry_update_id = $valueR->id;
				// 	$InquiryUpdateSeen->inquiry_id = $Inquiry->id;
				// 	$InquiryUpdateSeen->user_id = Auth::user()->id;
				// 	$InquiryUpdateSeen->save();
				// }
			}
		}

		$response = successRes("");

		$response['view'] = view('crm/inquiry/detail', compact('data'))->render();
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function saveUpdate(Request $request)
	{

		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		$isThirdPartyUser = isThirdPartyUser();
		$isChannelPartner = isChannelPartner(Auth::user()->type);
		$isSalePerson = isSalePerson();
		$isTaleSalesUser = isTaleSalesUser();
		$TaleSalesCities = TeleSalesCity(Auth::user()->id);

		$validator = Validator::make($request->all(), [

			'message' => ['required'],
			'inquiry_id' => ['required'],
			'inquiry_update_id' => ['required'],

		]);

		if ($validator->fails()) {
			$response = array();
			$response['status'] = 0;
			$response['msg'] = "The request could not be understood by the server due to malformed syntax";
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();

			return redirect()->back()->with("error", "Something went wrong with validation");
		} else {

			$meessageValidation = trim($request->message);
			$meessageValidation = str_replace('<p>', '', $meessageValidation);
			$meessageValidation = str_replace('</p>', '', $meessageValidation);
			$meessageValidation = str_replace('<br>', '', $meessageValidation);
			$meessageValidation = str_replace('&nbsp;', '', $meessageValidation);
			$meessageValidation = str_replace(' ', '', $meessageValidation);
			$meessageValidation = trim($meessageValidation);

			if ($meessageValidation == "") {
				$response = errorRes("Please enter your update");
				return response()->json($response)->header('Content-Type', 'application/json');
			}

			$Inquiry = ExhibitionInquiry::find($request->inquiry_id);

			if ($Inquiry) {

				$InquiryUpdate = new ExhibitionInquiryUpdate();
				$InquiryUpdate->message = trim($request->message);
				$InquiryUpdate->user_id = Auth::user()->id;
				$InquiryUpdate->exhibition_inquiry_id = $request->inquiry_id;
				$InquiryUpdate->reply_id = $request->inquiry_update_id;
				$InquiryUpdate->save();

				///

				$response = successRes("Successfully sent message");
			} else {
				$response = errorRes("Invalid parameters");
			}
		}

		return response()->json($response)->header('Content-Type', 'application/json');
	}


	function converToUser(Request $request)
	{

		$rules = array();
		$rules['id'] = 'required';

		$customMessage = array();
		$customMessage['id.required'] = 'Invalid parameters';

		$validator = Validator::make($request->all(), $rules, $customMessage);

		if ($validator->fails()) {
			$response = array();
			$response['status'] = 0;
			$response['msg'] = "The request could not be understood by the server due to malformed syntax";
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();
		} else {

			$ExhibitionInquiry = ExhibitionInquiry::find($request->id);
			if ($ExhibitionInquiry) {

				if ($ExhibitionInquiry->type == "Architect") {

					$User = User::where(function ($query) use ($ExhibitionInquiry) {
						$query->where('email', $ExhibitionInquiry->email)->orWhere('phone_number', $ExhibitionInquiry->phone_number);
					})->first();

					if ($User) {
						$response = errorRes("User already available");
					} else {

						$CityList = CityList::find($ExhibitionInquiry->city_id);
						$User = new User();
						$User->created_by = Auth::user()->id;
						$User->password = Hash::make("111111");
						$User->last_active_date_time = date('Y-m-d H:i:s');
						$User->last_login_date_time = date('Y-m-d H:i:s');
						$User->avatar = "default.png";
						$User->status = 1;
						$User->first_name = $ExhibitionInquiry->first_name;
						$User->last_name = $ExhibitionInquiry->last_name;
						$User->email = $ExhibitionInquiry->email;
						$User->dialing_code = "+91";
						$User->phone_number = $ExhibitionInquiry->phone_number;
						$User->ctc = 0;
						$User->address_line1 = $ExhibitionInquiry->address_line1;
						$User->address_line2 = $ExhibitionInquiry->address_line2;
						$User->pincode = "";
						$User->country_id = $CityList->country_id;
						$User->state_id = $CityList->state_id;
						$User->city_id = $CityList->id;
						$User->company_id = 1;
						$User->type = 201;
						$User->reference_type = 0;
						$User->reference_id = 0;
						$User->save();



						$Architect = new Architect();
						$Architect->user_id = $User->id;
						$Architect->type = $User->type;
						$Architect->firm_name = "";
						$Architect->birth_date = null;
						$Architect->anniversary_date = null;
						$Architect->converted_prime = 0;
						$Architect->added_by = Auth::user()->id;
						$Architect->save();
						$User->reference_type = "architect";
						$User->reference_id = $Architect->id;
						$User->save();

						$ExhibitionInquiry->link_user_id = $User->id;
						$ExhibitionInquiry->save();
						$debugLog = array();
						$debugLog['name'] = "architect-add";
						$debugLog['description'] = "user #" . $User->id . "(" . $User->first_name . " " . $User->last_name . ") has been added ";

						saveDebugLog($debugLog);
						$response = successRes("Successfully added user");
					}
				} else if ($ExhibitionInquiry->type == "Electrician") {

					$User = User::where(function ($query) use ($ExhibitionInquiry) {
						$query->where('email', $ExhibitionInquiry->email)->orWhere('phone_number', $ExhibitionInquiry->phone_number);
					})->first();

					if ($User) {
						$response = errorRes("User already available");
					} else {

						$CityList = CityList::find($ExhibitionInquiry->city_id);
						$User = new User();
						$User->created_by = Auth::user()->id;
						$User->password = Hash::make("111111");
						$User->last_active_date_time = date('Y-m-d H:i:s');
						$User->last_login_date_time = date('Y-m-d H:i:s');
						$User->avatar = "default.png";
						$User->status = 1;
						$User->first_name = $ExhibitionInquiry->first_name;
						$User->last_name = $ExhibitionInquiry->last_name;
						$User->email = $ExhibitionInquiry->email;
						$User->dialing_code = "+91";
						$User->phone_number = $ExhibitionInquiry->phone_number;
						$User->ctc = 0;
						$User->address_line1 = $ExhibitionInquiry->address_line1;
						$User->address_line2 = $ExhibitionInquiry->address_line2;
						$User->pincode = "";
						$User->country_id = $CityList->country_id;
						$User->state_id = $CityList->state_id;
						$User->city_id = $CityList->id;
						$User->company_id = 1;
						$User->type = 301;
						$User->reference_type = 0;
						$User->reference_id = 0;
						$User->save();



						$Electrician = new Electrician();
						$Electrician->user_id = $User->id;
						$Electrician->type = $User->type;
						$Electrician->sale_person_id = 0;
						$Electrician->added_by = Auth::user()->id;
						$Electrician->save();
						$User->reference_type = "electrician";
						$User->reference_id = $Electrician->id;
						$User->save();



						$ExhibitionInquiry->link_user_id = $User->id;
						$ExhibitionInquiry->save();
						$debugLog = array();
						$debugLog['name'] = "electrician-add";
						$debugLog['description'] = "user #" . $User->id . "(" . $User->first_name . " " . $User->last_name . ") has been added ";
						saveDebugLog($debugLog);
						$response = successRes("Successfully added user");
					}
				} else {

					$response = errorRes($ExhibitionInquiry->type . " Can't convert to user");
				}
			} else {
				$response = errorRes("Invalid parameters");
			}
		}
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function download(Request $request)
	{

		$columns = array(
			'exhibition_inquiry.id',
			'exhibition_inquiry.phone_number',
			'exhibition_inquiry.firm_name',
			'exhibition_inquiry.address_line1',
			'exhibition_inquiry.address_line2',
			'city_list.name as cityname',
			'exhibition_inquiry.type',
			'exhibition_inquiry.plan_type',
			'exhibition_inquiry.remark',
			'exhibition_inquiry.stage_of_site',
			'exhibition.name as exhibition_name',
			'exhibition_inquiry.created_at',
		);

		$query = ExhibitionInquiry::query();
		$query->select($columns);
		$query->selectRaw('CONCAT(exhibition_inquiry.address_line1,", ", exhibition_inquiry.address_line2,", ", city_list.name) AS address');
		$query->selectRaw('CONCAT(exhibition_inquiry.first_name," ", exhibition_inquiry.last_name) AS name');
		$query->selectRaw('CONCAT(users.first_name," ", users.last_name) AS created_by');
		$query->leftJoin('exhibition', 'exhibition.id', '=', 'exhibition_inquiry.exhibition_id');
		$query->leftJoin('city_list', 'city_list.id', '=', 'exhibition_inquiry.city_id');
		$query->leftJoin('users', 'users.id', '=', 'exhibition_inquiry.user_id');

		if ($request->exhibition_filter != 0) {
			$query->where('exhibition_inquiry.exhibition_id', $request->exhibition_filter);
		}
		if (isset($request->usertype_filter)) {
			if ($request->usertype_filter != 'All') {
				$query->where('exhibition_inquiry.type', $request->usertype_filter);
			}
		}
		if ($request->isconvertinquiry_filter != 0) {
			if ($request->isconvertinquiry_filter == 1) {
				$query->where('exhibition_inquiry.inquiry_id', '<>', '0');
			} elseif ($request->isconvertinquiry_filter == 2) {
				$query->where('exhibition_inquiry.inquiry_id', 'IS NULL', null, 'and');
			}
		}

		$data = $query->get();


		$headers = array(
			'#id',
			"Name",
			"Phone Number",
			"Firm Name",
			"Address Line 1",
			"Address Line 2",
			"City Name",
			"Type",
			"Plan Type",
			"Remark",
			"Stage Of Site",
			"Exhibition Name",
			"Created By",
			"Created At",
		);

		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="Exhibition Report.csv"');
		$fp = fopen('php://output', 'wb');

		fputcsv($fp, $headers);

		foreach ($data as $key => $value) {


			$lineVal = array(
				$value->id,
				$value->name,
				$value->phone_number,
				$value->firm_name,
				$value->address_line1,
				$value->address_line2,
				$value->cityname,
				$value->type,
				$value->plan_type,
				$value->remark,
				$value->stage_of_site,
				$value->exhibition_name,
				$value->created_by,
				convertDateTime($value->created_at),

			);

			fputcsv($fp, $lineVal, ",");
		}
		fclose($fp);
	}
}