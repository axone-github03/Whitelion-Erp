<?php

namespace App\Http\Controllers;

use App\Models\Architect;
use App\Models\ChannelPartner;
use App\Models\Electrician;
use App\Models\Exhibition;
use App\Models\Inquiry;
use App\Models\InquiryLog;
use App\Models\InquiryQuestion;
use App\Models\InquiryQuestionAnswer;
use App\Models\InquiryQuestionOption;
use App\Models\InquiryUpdate;
use App\Models\InquiryUpdateSeen;
use App\Models\SalePerson;
use App\Models\User;
use App\Models\UserNotification;
use Config;
use DB;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Mail;

class CRMInquiryController extends Controller
{

	public function __construct()
	{
		$this->middleware(function ($request, $next) {
			// $tabCanAccessBy = array(0, 1, 2, 8, 9, 202, 302, 101, 102, 102, 103, 104, 105);
			$tabCanAccessBy = array(0, 1);
			if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
				return redirect()->route('dashboard');
			}
			// if(in_array(Auth::user()->id,getInquiryTransferToLeadUserList())){
			// 	return redirect()->route('crm.lead');
			// }
			return $next($request);
		});
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

	public function index(Request $request)
	{

		$inquiryStatus = getInquiryStatus();

		$InquiryQuestions = InquiryQuestion::whereIn('id', array(8, 9, 10))->orderBy('status', 'asc')->orderBy('sequence', 'asc')->get();

		foreach ($InquiryQuestions as $iQK => $iQV) {
			$InquiryQuestions[$iQK]['options'] = InquiryQuestionOption::select('id', 'option')->where('inquiry_question_id', $iQV->id)->orderBy('id', 'asc')->get();
		}

		$stageOfSiteOptions = InquiryQuestionOption::select('id', 'option')->where('inquiry_question_id', 7)->orderBy('id', 'asc')->get();

		$data = array();
		$data['source_types'] = getInquirySourceTypes();

		$data['question'] = $InquiryQuestions;
		$data['status'] = isset($request->status) ? $request->status : 0;
		$data['title'] = $inquiryStatus[$data['status']]['name'] . " - Inquiry";
		$data['isArchitect'] = isArchitect();
		$data['isElectrician'] = isElectrician();
		$data['isSalePerson'] = isSalePerson();
		$data['isAdminOrCompanyAdmin'] = isAdminOrCompanyAdmin();
		$data['isChannelPartner'] = isChannelPartner(Auth::user()->type);
		$data['timeSlot'] = $this->getInquiryTimeSlot();
		$data['inquiry_id'] = isset($request->inquiry_id) ? $request->inquiry_id : 0;
		$data['stage_of_site'] = $stageOfSiteOptions;
		$data['no_of_inquiry_request'] = 0;
		$data['isThirdPartyUser'] = isThirdPartyUser();
		$data['isTaleSalesUser'] = isTaleSalesUser();
		if ($data['isSalePerson'] == 1) {

			$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
			$query = Inquiry::query();
			$query->whereIn('inquiry.assigned_to', $childSalePersonsIds);
			$query->where('inquiry.is_verified', 0);
			$data['no_of_inquiry_request'] = $query->count();
		}

		return view('crm/inquiry/index', compact('data'));
	}

	public function pendingRequest(Request $request)
	{

		$isSalePerson = isSalePerson();
		$isChannelPartner = isChannelPartner(Auth::user()->type);
		if ($isSalePerson == 1) {

			$data = array();
			$data['source_types'] = getInquirySourceTypes();
			$data['title'] = "Pending Inquiry";
			$data['status'] = isset($request->status) ? $request->status : 0;
			$data['is_verified'] = isset($request->is_verified) ? $request->is_verified : 0;
			if ($data['is_verified'] == 0) {
				$data['is_verified_lable'] = "Pending";
			} else if ($data['is_verified'] == 2) {
				$data['is_verified_lable'] = "Rejected";
			}
			$data['isArchitect'] = isArchitect();
			$data['isSalePerson'] = isSalePerson();
			$data['isAdminOrCompanyAdmin'] = isAdminOrCompanyAdmin();
			$data['isChannelPartner'] = isChannelPartner(Auth::user()->type);
			$data['timeSlot'] = $this->getInquiryTimeSlot();
			return view('crm/inquiry/pending', compact('data'));
		} else if ($isChannelPartner != 0) {

			$data = array();
			$data['source_types'] = getInquirySourceTypes();
			$data['title'] = "Pending Inquiry";
			$data['status'] = isset($request->status) ? $request->status : 0;
			$data['is_verified'] = isset($request->is_verified) ? $request->is_verified : 0;
			if ($data['is_verified'] == 0) {
				$data['is_verified_lable'] = "Pending";
			} else if ($data['is_verified'] == 2) {
				$data['is_verified_lable'] = "Rejected";
			}
			$data['isArchitect'] = isArchitect();
			$data['isSalePerson'] = isSalePerson();
			$data['isAdminOrCompanyAdmin'] = isAdminOrCompanyAdmin();
			$data['isChannelPartner'] = isChannelPartner(Auth::user()->type);
			$data['timeSlot'] = $this->getInquiryTimeSlot();
			return view('crm/inquiry/pending', compact('data'));
		}
	}

	function pendingRequestAjax(Request $request)
	{

		$isSalePerson = isSalePerson();
		$isChannelPartner = isChannelPartner(Auth::user()->type);

		if ($isSalePerson == 1 || $isChannelPartner != 0) {
			$inquiryStatus = getInquiryStatus();
			$timeSlot = $this->getInquiryTimeSlot();

			if ($isSalePerson == 1) {
				$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
			}

			$searchColumns = array(
				'',
				'CONCAT(inquiry.first_name," ",inquiry.last_name)',
				'inquiry.id',
				'inquiry.phone_number',
				'inquiry.pincode',
				'city_list.name',
				'inquiry.house_no',
				'inquiry.society_name',
				'inquiry.area',
				'CONCAT(assigned_to.first_name," ",assigned_to.last_name)',
				'CONCAT(architect.first_name," ",architect.last_name)',
				'CONCAT(electrician.first_name," ",electrician.last_name)',
				'inquiry.source_type',
				'inquiry.source_type_value',
				'CONCAT(architect.first_name," ",architect.last_name)',
				'CONCAT(architect.first_name," ",architect.last_name)',

			);

			$selectColumns = array(
				'inquiry.id',
				'inquiry.first_name',
				'inquiry.last_name',
				'inquiry.phone_number',
				'inquiry.house_no',
				'inquiry.society_name',
				'inquiry.area',
				'inquiry.city_id',
				'inquiry.status',
				'inquiry.pincode',
				'inquiry.source_type',
				'inquiry.source_type_lable',
				'inquiry.source_type_value',
				'created_by.first_name as created_by_first_name',
				'created_by.last_name as created_by_last_name',
				'created_by.type as created_by_type',
				'created_by.id as created_by_user_id',
				'assigned_to.first_name as assigned_to_first_name',
				'assigned_to.last_name as assigned_to_last_name',
				'city_list.name as city_list_name',
				'inquiry.follow_up_type',
				'inquiry.follow_up_date_time',
				'inquiry.architect',
				'architect.type as architect_type',
				'architect.first_name as architect_first_name',
				'architect.last_name as architect_last_name',
				'architect.phone_number as architect_phone_number',
				'inquiry.electrician',
				'electrician.type as electrician_type',
				'electrician.first_name as electrician_first_name',
				'electrician.last_name as electrician_last_name',
				'electrician.phone_number as electrician_phone_number',
				'inquiry.quotation',
				'inquiry.quotation_amount',
				'inquiry.stage_of_site',
				'inquiry.billing_invoice',
				'inquiry.billing_amount',
				'inquiry.created_at',
				'inquiry.update_count',
				'inquiry.last_update',
				'inquiry.claimed_date_time',
				'inquiry.is_verified',
			);

			$sortColumns = array(
				0 => 'inquiry.id',
				1 => 'inquiry.user_id',
				2 => 'inquiry.follow_up_date_time',
				3 => 'inquiry.status',

			);

			$query = Inquiry::query();
			if ($isSalePerson == 1) {

				$query->whereIn('inquiry.assigned_to', $childSalePersonsIds);
				$query->where('inquiry.is_verified', $request['is_verified']);
			} else if ($isChannelPartner != 0) {

				$query->where(function ($query2) {

					$query2->where(function ($query3) {

						$query3->where('inquiry.source_type', "user-" . Auth::user()->type);
						$query3->where('inquiry.source_type_value', Auth::user()->id);
					});

					$query2->orWhere(function ($query3) {

						$query3->where('inquiry.source_type_1', "user-" . Auth::user()->type);
						$query3->where('inquiry.source_type_value_1', Auth::user()->id);
					});

					$query2->orWhere(function ($query3) {

						$query3->where('inquiry.source_type_2', "user-" . Auth::user()->type);
						$query3->where('inquiry.source_type_value_2', Auth::user()->id);
					});
					$query2->orWhere(function ($query3) {

						$query3->where('inquiry.source_type_3', "user-" . Auth::user()->type);
						$query3->where('inquiry.source_type_value_3', Auth::user()->id);
					});

					$query2->orWhere(function ($query3) {

						$query3->where('inquiry.source_type_4', "user-" . Auth::user()->type);
						$query3->where('inquiry.source_type_value_4', Auth::user()->id);
					});
				});

				$query->where('inquiry.is_verified', $request['is_verified']);
			}

			if (isAdminOrCompanyAdmin() == 0) {
				$query->whereNotIn('inquiry.assigned_to', getInquiryTransferToLeadUserList());
			}
			$recordsTotal = $query->count();

			//	$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.

			$searchValue = $request['search']['value'];

			if ($searchValue != "") {

				$query = Inquiry::query();
				$query->select($selectColumns);
				$query->leftJoin('users as created_by', 'created_by.id', '=', 'inquiry.user_id');
				$query->leftJoin('users as assigned_to', 'assigned_to.id', '=', 'inquiry.assigned_to');
				$query->leftJoin('users as architect', 'architect.id', '=', 'inquiry.architect');
				$query->leftJoin('users as electrician', 'electrician.id', '=', 'inquiry.electrician');
				$query->leftJoin('city_list', 'city_list.id', '=', 'inquiry.city_id');
				if ($isSalePerson == 1) {

					$query->where('inquiry.assigned_to', Auth::user()->id);
					$query->where('inquiry.is_verified', $request['is_verified']);
				} else if ($isChannelPartner != 0) {

					$query->where(function ($query2) {

						$query2->where(function ($query3) {

							$query3->where('inquiry.source_type', "user-" . Auth::user()->type);
							$query3->where('inquiry.source_type_value', Auth::user()->id);
						});

						$query2->orWhere(function ($query3) {

							$query3->where('inquiry.source_type_1', "user-" . Auth::user()->type);
							$query3->where('inquiry.source_type_value_1', Auth::user()->id);
						});

						$query2->orWhere(function ($query3) {

							$query3->where('inquiry.source_type_2', "user-" . Auth::user()->type);
							$query3->where('inquiry.source_type_value_2', Auth::user()->id);
						});
						$query2->orWhere(function ($query3) {

							$query3->where('inquiry.source_type_3', "user-" . Auth::user()->type);
							$query3->where('inquiry.source_type_value_3', Auth::user()->id);
						});

						$query2->orWhere(function ($query3) {

							$query3->where('inquiry.source_type_4', "user-" . Auth::user()->type);
							$query3->where('inquiry.source_type_value_4', Auth::user()->id);
						});
					});

					$query->where('inquiry.is_verified', $request['is_verified']);
				}
				$query->orderBy($sortColumns[$request['order'][0]['column']], $request['order'][0]['dir']);
				$recordsFiltered = $query->count();
			} else {

				$recordsFiltered = $recordsTotal;
			}

			$query = Inquiry::query();
			$query->select($selectColumns);
			$query->leftJoin('users as created_by', 'created_by.id', '=', 'inquiry.user_id');
			$query->leftJoin('users as assigned_to', 'assigned_to.id', '=', 'inquiry.assigned_to');
			$query->leftJoin('users as architect', 'architect.id', '=', 'inquiry.architect');
			$query->leftJoin('users as electrician', 'electrician.id', '=', 'inquiry.electrician');
			$query->leftJoin('city_list', 'city_list.id', '=', 'inquiry.city_id');
			if ($isSalePerson == 1) {

				$query->whereIn('inquiry.assigned_to', $childSalePersonsIds);
				$query->where('inquiry.is_verified', $request['is_verified']);
			} else if ($isChannelPartner != 0) {

				$query->where(function ($query2) {

					$query2->where(function ($query3) {

						$query3->where('inquiry.source_type', "user-" . Auth::user()->type);
						$query3->where('inquiry.source_type_value', Auth::user()->id);
					});

					$query2->orWhere(function ($query3) {

						$query3->where('inquiry.source_type_1', "user-" . Auth::user()->type);
						$query3->where('inquiry.source_type_value_1', Auth::user()->id);
					});

					$query2->orWhere(function ($query3) {

						$query3->where('inquiry.source_type_2', "user-" . Auth::user()->type);
						$query3->where('inquiry.source_type_value_2', Auth::user()->id);
					});
					$query2->orWhere(function ($query3) {

						$query3->where('inquiry.source_type_3', "user-" . Auth::user()->type);
						$query3->where('inquiry.source_type_value_3', Auth::user()->id);
					});

					$query2->orWhere(function ($query3) {

						$query3->where('inquiry.source_type_4', "user-" . Auth::user()->type);
						$query3->where('inquiry.source_type_value_4', Auth::user()->id);
					});
				});

				$query->where('inquiry.is_verified', $request['is_verified']);
			}
			$query->limit($request->length);
			$query->offset($request->start);
			$query->orderBy($sortColumns[$request['order'][0]['column']], $request['order'][0]['dir']);

			if (isAdminOrCompanyAdmin() == 0) {
				$query->whereNotIn('inquiry.assigned_to', getInquiryTransferToLeadUserList());
			}

			$data = $query->get();

			$data = json_decode(json_encode($data), true);

			foreach ($data as $key => $value) {

				//$claimed24HoursIn = 0;

				$isViewMode = 1;
				$viewModeAttribue = "disabled";

				$data[$key]['inquiry_id'] = $data[$key]['id'];
				$valueCreatedTime = convertDateTime($value['created_at']);

				//$filedDisabledForChannelPartner = "";

				// if ($isChannelPartner != 0) {
				// 	$filedDisabledForChannelPartner = "";
				// }

				$data[$key]['first_name'] = '<p class="border-box font-size-14 mb-0 text-dark d-flex justify-content-between align-items-center"><span><span class="lable-inquiry-id" id="inquiry-id-' . $data[$key]['id'] . '" >#' . $data[$key]['id'] . '</span><span id="inquiry-name-' . $data[$key]['id'] . '" data-bs-toggle="tooltip" title="' . $value['first_name'] . " " . $value['last_name'] . '" > ' . displayStringLenth($value['first_name'] . " " . $value['last_name'], 25) . ' </span></span>';

				$timeFirst = strtotime($value['last_update']);
				$timeSecond = strtotime(date('Y-m-d H:i:s'));
				$differenceInSeconds = $timeSecond - $timeFirst;
				$updateHighLightClass = "";

				if ($differenceInSeconds < 172800) {
					$updateHighLightClass = "hightlight-update";
				}

				if ($value['update_count'] == 0) {
					$updateCount = "";
				} else {
					$updateCount = $value['update_count'];
				}

				$data[$key]['first_name'] .= '</p>';

				$data[$key]['first_name'] .= '<p class="border-box font-size-14 mb-0 text-dark d-flex justify-content-between align-items-center" ><span class="lable-inquiry-phone"><i class="bx bx-phone bx-sm extrasmallfont" data-bs-toggle="tooltip" title="Mobile No." ></i> +91 ' . $data[$key]['phone_number'] . ' </span>';

				$data[$key]['first_name'] .= '</p>';

				$data[$key]['first_name'] .= '<p class="border-box font-size-14 mb-0 text-dark"><i class="bx bx-map bx-sm extrasmallfont" data-bs-toggle="tooltip" title="Address" ></i><span data-bs-toggle="tooltip" title="' . $data[$key]['house_no'] . ' ' . $data[$key]['society_name'] . '" >
                     ' . displayStringLenth($data[$key]['house_no'] . ' ' . $data[$key]['society_name'], 30) . '</span><br><span data-bs-toggle="tooltip" title="' . $data[$key]['area'] . '" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . displayStringLenth($data[$key]['area'], 30) . '</span><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $data[$key]['pincode'] . ', ' . $data[$key]['city_list_name'] . '</p>
                ';

				if ($data[$key]['architect'] == 0) {
					$data[$key]['architect_name'] = "-";
					$data[$key]['architect_phone_number'] = "-";
				} else {

					$data[$key]['architect_name'] = $data[$key]['architect_first_name'] . " " . $data[$key]['architect_last_name'];

					$data[$key]['architect_phone_number'] = "+91 " . $data[$key]['architect_phone_number'];
				}

				if ($data[$key]['electrician'] == 0) {
					$data[$key]['electrician_name'] = "-";
					$data[$key]['electrician_phone_number'] = "-";
				} else {

					$data[$key]['electrician_name'] = $data[$key]['electrician_first_name'] . " " . $data[$key]['electrician_last_name'];

					$data[$key]['electrician_phone_number'] = "+91 " . $data[$key]['electrician_phone_number'];
				}

				// if ($data[$key]['electrician_name'] == "") {
				// 	$data[$key]['electrician_name'] = "-";

				// }
				// if ($data[$key]['electrician_phone_number'] == "") {
				// 	$data[$key]['electrician_phone_number'] = "-";

				// } else {
				// 	$data[$key]['electrician_phone_number'] = "+91 " . $data[$key]['electrician_phone_number'];
				// }

				// $sourceTypeName = isset($sourceType[$found_key]['another_name']) ? $sourceType[$found_key]['another_name'] : $sourceType[$found_key]['short_name'];

				$isChannelPartnerCreatedBy = isChannelPartner($value['created_by_type']);
				$createdBylableName = "";

				if ($isChannelPartnerCreatedBy != 0) {

					$channelPartnerCreatedBy = ChannelPartner::select('firm_name')->where('user_id', $value['created_by_user_id'])->first();

					if ($channelPartnerCreatedBy) {
						$createdBylableName = $channelPartnerCreatedBy->firm_name;
					}
				} else {

					$createdBylableName = $value['created_by_first_name'] . " " . $value['created_by_last_name'];
				}

				$data[$key]['created_by'] = "<p class='border-box mb-0'><i  data-bs-toggle='tooltip' title='Created By' class='bx bxs-user bx-sm extrasmallfont'></i> <span data-bs-toggle='tooltip' title='" . $createdBylableName . "' >" . displayStringLenth($createdBylableName, 25) . "</span><a data-bs-toggle='tooltip' href='javascript: void(0);' class='createdicon' title='Created Date & Time : " . $valueCreatedTime . "' ><i class='bx bx-calendar'></i></a></p>";

				$sourceTypePices = explode("-", $value['source_type']);

				if ($sourceTypePices[0] == "user") {

					$userType = $sourceTypePices[1];
					$sourceTypeName = $value['source_type_lable'];

					$sourceName = "";
					if (isChannelPartner($userType) != 0) {

						$ChannelPartner = ChannelPartner::select('firm_name')->where('user_id', $value['source_type_value'])->first();

						if ($ChannelPartner) {

							$sourceName = $ChannelPartner->firm_name;
						}
					} else {

						$User = User::select('first_name', 'last_name')->find($value['source_type_value']);

						if ($User) {

							$sourceName = $User->first_name . " " . $User->last_name;
						}
					}

					if ($sourceTypePices[1] == "201" || $sourceTypePices[1] == "202") {

						$sourceTypeName = "Architect";
					}
					if ($sourceTypePices[1] == "4") {

						$sourceTypeName = $value['source_type_lable'];
						$sourceName = $value['source_type_value'];
					}

					$data[$key]['created_by'] .= "<p class='border-box mb-0 '><i  data-bs-toggle='tooltip' title='Source Type' class='bx bx-shield-alt bx-sm extrasmallfont'></i> " . $sourceTypeName . "<br><i  data-bs-toggle='tooltip' title='Source' class='bx bxs-user bx-sm extrasmallfont'></i><span data-bs-toggle='tooltip' title='" . $sourceName . "' > " . displayStringLenth($sourceName, 25) . "</span></p>";
				} else if ($sourceTypePices[0] == "textrequired" || $sourceTypePices[0] == "textnotrequired") {

					$userType = $sourceTypePices[1];
					$sourceTypeName = $value['source_type_lable'];
					$sourceName = $value['source_type_value'];

					$data[$key]['created_by'] .= "<p class='border-box mb-0 pdiv-" . $userType . "'><i  data-bs-toggle='tooltip' title='Source Type' class='bx bx-shield-alt bx-sm extrasmallfont'></i> " . $sourceTypeName . "<br><i  data-bs-toggle='tooltip' title='Source' class='bx bxs-user bx-sm extrasmallfont'></i> <span data-bs-toggle='tooltip' title='" . $sourceName . "' >" . displayStringLenth($sourceName, 25) . "</span></p>";
				} else {

					$userType = $sourceTypePices[1];
					$sourceName = $value['source_type_lable'];

					$data[$key]['created_by'] .= "<p class='border-box mb-0 pdiv-" . $userType . "'><i  data-bs-toggle='tooltip' title='Source' class='bx bxs-user bx-sm extrasmallfont'></i> " . $sourceName . "</p>";
				}

				$primeArchitectClass = "";
				$primeEleactricanClass = "";
				$primeArchitectLable = "";
				$primeElecricianLable = "";
				if ($value['architect_type'] == 202) {
					$primeArchitectClass = "pdiv-202";
					$primeArchitectLable = ' <span class="badge rounded-pill bg-success">PRIME</span>';
				}

				if ($value['electrician_type'] == 302) {
					$primeEleactricanClass = "pdiv-302";
					$primeElecricianLable = ' <span class="badge rounded-pill bg-success">PRIME</span>';
				}

				$data[$key]['created_by'] .= "<p class='border-box mb-0 " . $primeArchitectClass . "'><i  data-bs-toggle='tooltip' title='Architect Name' class='bx bxs-user bx-sm extrasmallfont'></i><span data-bs-toggle='tooltip' title='" . $data[$key]['architect_name'] . "' > " . displayStringLenth($data[$key]['architect_name'], 25) . $primeArchitectLable . "</span> <br> <i class='bx bx-phone bx-sm extrasmallfont' data-bs-toggle='tooltip' title='Architect Mobile No.' ></i> " . $data[$key]['architect_phone_number'] . "</p>";

				$data[$key]['created_by'] .= "<p class='border-box mb-0 " . $primeEleactricanClass . "'><i  data-bs-toggle='tooltip' title='Electrician Name' class='bx bxs-user bx-sm extrasmallfont'></i> <span  data-bs-toggle='tooltip' title='" . $data[$key]['electrician_name'] . "' >" . displayStringLenth($data[$key]['electrician_name'], 25) . $primeElecricianLable . "</span> <br> <i class='bx bx-phone bx-sm extrasmallfont' data-bs-toggle='tooltip' title='Electrician Mobile No.' ></i> " . $data[$key]['electrician_phone_number'] . "</p>";

				if ($data[$key]['stage_of_site'] == "") {

					$data[$key]['stage_of_site'] .= "-";
				} else {
					$stageOfSite = $data[$key]['stage_of_site'];
				}

				if ($data[$key]['quotation'] == "") {

					$data[$key]['quotation'] = "-";
				} else {

					$data[$key]['quotation'] = "<a class='btn btn-sm btn-success btn-quotation' target='_blank'  href='" . Config::get('app.url') . "/" . $data[$key]['quotation'] . "' data-bs-toggle='tooltip' title='Quotation' >Quotation</a>";
				}

				if ($data[$key]['quotation_amount'] == "") {

					$data[$key]['quotation_amount'] = "-";
				}

				$data[$key]['follow_up'] = "<p class='border-box mb-0'><i  data-bs-toggle='tooltip' title='Stage of site' class='bx bx-cube bx-sm extrasmallfont'></i> " . $data[$key]['stage_of_site'] . "</p>";

				$data[$key]['follow_up'] .= "<p class='border-box mb-0'>
				<span class='lable-inquiry-quotation'><i  data-bs-toggle='tooltip' title='Quotation' class='bx bx-receipt bx-sm extrasmallfont'></i> " . $data[$key]['quotation'] . "/ <i  data-bs-toggle='tooltip' title='Quotation Amount' class='bx bx bx-rupee  bx-sm extrasmallfont'></i>" . $data[$key]['quotation_amount'] . "</span>";

				$data[$key]['follow_up'] .= "</p>";

				$follow_up_date_time = "";
				$folloupDateTimeClass = "has-no-followupdatetime";
				if ($data[$key]['follow_up_date_time'] != null) {

					$currentDatetime = date('Y-m-d H:i:s');
					if ($currentDatetime > $data[$key]['follow_up_date_time']) {

						if ($inquiryStatus[$value['status']]['highlight_deadend_followup'] == 1) {
							$folloupDateTimeClass = "expired-followupdatetime";
						} else {
							$folloupDateTimeClass = "followupdatetime";
						}
					} else {
						$folloupDateTimeClass = "followupdatetime";
					}
					$follow_up_date = date('d-m-Y', strtotime($data[$key]['follow_up_date_time']));
					$follow_up_time = date('h:i A', strtotime($data[$key]['follow_up_date_time']));

					// $follow_up_date_time = date('Y-m-d', strtotime($data[$key]['follow_up_date_time'])) . "T" . date('H:i', strtotime($data[$key]['follow_up_date_time']));

				} else {
					$follow_up_date = "";
					$follow_up_time = "";
				}

				$data[$key]['follow_up'] .= "<div class='border-box mb-0 " . $folloupDateTimeClass . "'><i  data-bs-toggle='tooltip' title='Follow up Type' class='bx bx-book-open bx-sm extrasmallfont'></i> " . $data[$key]['follow_up_type'] . " <br>";

				$data[$key]['follow_up'] .= "<i  data-bs-toggle='tooltip' title='Follow up date & time' class='bx bx-calendar bx-sm extrasmallfont' style='float:left'></i> ";

				$data[$key]['follow_up'] .= "<div class='input-group' id='inquiry_follow_up_date_time_" . $data[$key]['inquiry_id'] . "' style='width:80%;margin-left: 21px;'> ";

				$data[$key]['follow_up'] .= "<input class='input-followup-date-time form-control' type='text' onchange='changeFollowUpDateTime(" . $data[$key]['inquiry_id'] . ")' data-date-format='dd-mm-yyyy' data-date-container='#inquiry_follow_up_date_time_" . $data[$key]['inquiry_id'] . "' data-provide='datepicker' data-date-autoclose='true'   id='answer_follow_up_date_" . $data[$key]['inquiry_id'] . "'  value='" . $follow_up_date . "' placeholder='dd-mm-yyyy' " . $viewModeAttribue . "  >";

				$data[$key]['follow_up'] .= "<div style='width:50%;'><select onchange='changeFollowUpDateTime(" . $data[$key]['inquiry_id'] . ")' class='form-control input-followup-time select2-choices' id='answer_follow_up_time_" . $data[$key]['inquiry_id'] . "' name='inquiry_follow_up_time' " . $viewModeAttribue . " />";

				foreach ($timeSlot as $timeSlotObject) {

					if ($follow_up_time == $timeSlotObject) {

						$data[$key]['follow_up'] .= "<option selected	 value=" . $timeSlotObject . ">" . $timeSlotObject . "</option>";
					} else {
						$data[$key]['follow_up'] .= "<option value=" . $timeSlotObject . ">" . $timeSlotObject . "</option>";
					}
				}

				$data[$key]['follow_up'] .= "</select>";

				$data[$key]['follow_up'] .= "</div>";
				$data[$key]['follow_up'] .= "</div>";

				$data[$key]['follow_up'] .= " <button style='margin-top: 3px;display: none;' class='save_answer_follow_up_date_time btn btn-success btn-sm waves-effect waves-light' id='save_answer_follow_up_date_time_" . $data[$key]['inquiry_id'] . "'  >Save</button>";

				$data[$key]['follow_up'] .= "</div>";

				//

				if ($data[$key]['billing_invoice'] == "") {

					$data[$key]['billing_invoice'] = "-";
				} else {

					//$data[$key]['billing_invoice'] = "<a class='btn btn-sm btn-success btn-quotation'   href='" . Config::get('app.url') . "/" . $data[$key]['billing_invoice'] . "' data-bs-toggle='tooltip' title='Billing Invoice' >Billing Invoice</a>";
					$data[$key]['billing_invoice'] = "<a class='btn btn-sm btn-success btn-quotation' onclick='openBillingInvoiceModal(" . $value['id'] . "," . $data[$key]['id'] . ",\"" . $data[$key]['billing_invoice'] . "\")'  href='javascript:void(0)' data-bs-toggle='tooltip' title='Billing Invoice' >Billing Invoice</a>";
				}

				if ($data[$key]['billing_amount'] == "") {

					$data[$key]['billing_amount'] = "-";
				}

				$inqueryStatusList = array();

				$inqueryStatusList = $inquiryStatus[$value['status']]['can_move_channel_partner'];

				foreach ($inqueryStatusList as $keyIQS => $valIQS) {
					$inqueryStatusList[$keyIQS] = $inquiryStatus[$valIQS];
				}

				$data[$key]['status'] = '<p class="border-box mb-0"><i  data-bs-toggle="tooltip" title="Assigned" class="bx bxs-user bx-sm extrasmallfont"></i> <span data-bs-toggle="tooltip" title="' . $data[$key]['assigned_to_first_name'] . ' ' . $data[$key]['assigned_to_last_name'] . '" >' . displayStringLenth($data[$key]['assigned_to_first_name'] . ' ' . $data[$key]['assigned_to_last_name'], 25) . '</span>';

				$data[$key]['status'] .= '</p>

				<p class="border-box mb-0">';

				if ($value['is_verified'] == 0) {

					$data[$key]['status'] .= "<button class='btn btn-sm btn-success' onclick='verifyInquiry(" . $value['id'] . ",1)' >Accept </button> <button class='btn btn-sm btn-danger' onclick='verifyInquiry(" . $value['id'] . ",2)' >Reject </button>  ";
				} else if ($value['is_verified'] == 2) {
					$data[$key]['status'] .= "<span class='requestedforveify'> Rejected </span>";
				}

				$data[$key]['status'] .= '</p>';

				$data[$key]['status'] .= "<p class='border-box mb-0'><i  data-bs-toggle='tooltip' title='Billing Invoice' class='bx bx-receipt bx-sm extrasmallfont'></i> " . $data[$key]['billing_invoice'] . "/ <i  data-bs-toggle='tooltip' title='Billing Amount' class='bx bx bx-rupee  bx-sm extrasmallfont'></i>" . $data[$key]['billing_amount'];

				$data[$key]['status'] .= "</p>";
			}

			$jsonData = array(
				"draw" => intval($request['draw']),
				// for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
				"recordsTotal" => intval($recordsTotal),
				// total number of records
				"recordsFiltered" => intval($recordsFiltered),
				// total number of records after searching, if there is no searching then totalFiltered = totalData
				"data" => $data, // total data array
			);
			return $jsonData;
		}
	}

	public function acceptReject(Request $request)
	{

		$isSalePerson = isSalePerson();
		if ($isSalePerson == 1) {

			$Inquiry = Inquiry::find($request->inquiry_id);

			$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);

			if ($Inquiry && in_array($Inquiry->assigned_to, $childSalePersonsIds)) {

				if ($request->type == 2) {
					$Inquiry->is_verified = 2;
				} else if ($request->type == 1) {
					$Inquiry->is_verified = 1;
				}
				$Inquiry->save();
				$response = successRes("Successfully updated");
			} else {
				$response = errorRes("Invalid access");
			}
		} else {
			$response = errorRes("Invalid access");
		}

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	function ajax(Request $request)
	{

		$isArchitect = isArchitect();
		$isElectrician = isElectrician();
		$isSalePerson = isSalePerson();
		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		$isChannelPartner = isChannelPartner(Auth::user()->type);
		$inquiryStatus = getInquiryStatus();
		$timeSlot = $this->getInquiryTimeSlot();
		$inquirySourceType = getInquirySourceTypes();
		$isThirdPartyUser = isThirdPartyUser();
		$isTaleSalesUser = isTaleSalesUser();
		$SalesCity = array();

		if ($isSalePerson == 1) {
			$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
			$SalesCity = SalesCity(Auth::user()->id);
		}

		if ($isTaleSalesUser == 1) {

			$TaleSalesCities = TeleSalesCity(Auth::user()->id);
		}

		$InquiryQuestionsStageOfState = InquiryQuestion::where('id', 7)->orderBy('status', 'asc')->orderBy('sequence', 'asc')->first();

		if ($InquiryQuestionsStageOfState) {

			$InquiryQuestionsStageOfState['options'] = InquiryQuestionOption::select('id', 'option')->where('inquiry_question_id', $InquiryQuestionsStageOfState->id)->orderBy('id', 'asc')->get();
		}

		//START ADVANCE FILTER

		$advanceFilterType = $request->advance_filter_type;
		$advanceFilterText = $request->advance_filter_text;
		$advanceFilterType = json_decode($advanceFilterType, true);
		$advanceFilterText = json_decode($advanceFilterText, true);
		$hasAdvanceFilter = 0;
		$advanceFilterTextAdditional = array();
		if (count($advanceFilterType) > 0) {
			$hasAdvanceFilter = 1;
			foreach ($advanceFilterType as $keyA => $valueA) {

				if ($valueA == 13) {

					$sourceTypeFilter = array("xyz");

					foreach ($inquirySourceType as $key => $value) {

						if (str_contains(strtolower($value['lable']), strtolower($advanceFilterText[$keyA]))) {
							$sourceTypeFilter[] = $value['type'] . "-" . $value['id'];
						}
					}

					$advanceFilterText[$keyA] = $sourceTypeFilter;
				} else if ($valueA == 14) {

					$advanceFilterTextAdditional[$keyA] = $advanceFilterText[$keyA];

					if ($advanceFilterText[$keyA] != "") {

						$advanceSearchValue = $advanceFilterText[$keyA];

						$User = User::select('users.id');
						$User->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
						$User->where('users.status', 1);
						$User->where(function ($query) use ($advanceSearchValue) {
							$query->where('channel_partner.firm_name', 'like', '%' . $advanceSearchValue . '%');
							$query->orWhereRaw('CONCAT(users.first_name," ",users.last_name) like ?', [$advanceSearchValue]);
							$query->orWhereRaw('CONCAT(users.first_name," ",users.last_name) like ? ', ["%" . $advanceSearchValue . "%"]);
						});
						$User->limit(5);
						$User = $User->get();
						$sourceUsers = array();

						foreach ($User as $key => $value) {
							$sourceUsers[] = $value->id;
						}

						$advanceFilterText[$keyA] = $sourceUsers;
					} else {
						$advanceFilterText[$keyA] = array();
					}
				}
			}
		}

		//END ADVANCE FILTER

		$searchValue = "";

		if (isset($request['inquiry_filter_search_value']) && $request['inquiry_filter_search_value'] != "") {

			$searchValue = $request['inquiry_filter_search_value'];
		}

		if ($request['inquiry_filter_search_type'] == 12 || $request['inquiry_filter_search_type'] == 0) {
			if ($searchValue != "") {

				$sourceTypeFilter = array("xyz");
				foreach ($inquirySourceType as $key => $value) {

					if (str_contains(strtolower($value['lable']), strtolower($searchValue))) {
						$sourceTypeFilter[] = $value['type'] . "-" . $value['id'];
					}
				}
			}
		}

		$closingType = "";
		if (!isset($request->inquiry_filter_closing)) {
			$closingType = "0";
		} else {

			$closingType = $request->inquiry_filter_closing;
		}

		$hasQuotationFilter = 0;
		$quotationFilter = 0;
		if (!isset($request->inquiry_quotation_filter)) {
			$quotationFilter = 0;
		} else {

			$hasQuotationFilter = 1;
			$quotationFilter = (int) $request->inquiry_quotation_filter;
		}

		if (!isset($request->inquiry_filter_stage_of_site)) {
			$stageOfSite = "";
		} else {

			$stageOfSite = $request->inquiry_filter_stage_of_site;
		}

		if ($request['inquiry_filter_search_type'] == 13 || $request['inquiry_filter_search_type'] == 0) {
			$sourceUsers = array();
			if ($searchValue != "") {

				$User = User::select('users.id');
				$User->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
				$User->where('users.status', 1);
				$User->where(function ($query) use ($searchValue) {
					$query->where('channel_partner.firm_name', 'like', '%' . $searchValue . '%');
					$query->orWhereRaw('CONCAT(users.first_name," ",users.last_name) like ?', [$searchValue]);
					$query->orWhereRaw('CONCAT(users.first_name," ",users.last_name) like ? ', ["%" . $searchValue . "%"]);
				});
				$User->limit(5);
				$User = $User->get();

				foreach ($User as $key => $value) {
					$sourceUsers[] = $value->id;
				}
			}
		}

		$advanceFilterKey = array(
			'',
			'CONCAT(inquiry.first_name," ",inquiry.last_name)',
			'inquiry.phone_number',
			'inquiry.pincode',
			'city_list.name',
			'inquiry.house_no',
			'inquiry.society_name',
			'inquiry.area',
			'CONCAT(assigned_to.first_name," ",assigned_to.last_name)',
			'CONCAT(architect.first_name," ",architect.last_name)',
			'CONCAT(architect.first_name," ",architect.last_name)',
			'CONCAT(architect.first_name," ",architect.last_name)',
			'CONCAT(electrician.first_name," ",electrician.last_name)',
			'inquiry.source_type',
			'inquiry.source_type_value',
			'CONCAT(electrician.first_name," ",electrician.last_name)',
			'CONCAT(electrician.first_name," ",electrician.last_name)',
		);

		$searchColumns = array(
			'',
			'CONCAT(inquiry.first_name," ",inquiry.last_name)',
			'inquiry.id',
			'inquiry.phone_number',
			'inquiry.pincode',
			'city_list.name',
			'inquiry.house_no',
			'inquiry.society_name',
			'inquiry.area',
			'CONCAT(assigned_to.first_name," ",assigned_to.last_name)',
			'CONCAT(architect.first_name," ",architect.last_name)',
			'CONCAT(electrician.first_name," ",electrician.last_name)',
			'inquiry.source_type',
			'inquiry.source_type_value',
			'CONCAT(architect.first_name," ",architect.last_name)',
			'CONCAT(architect.first_name," ",architect.last_name)',
			'CONCAT(electrician.first_name," ",electrician.last_name)',
			'CONCAT(electrician.first_name," ",electrician.last_name)',

		);

		$selectColumns = array(
			'inquiry.id',
			'inquiry.first_name',
			'inquiry.last_name',
			'inquiry.phone_number',
			'inquiry.house_no',
			'inquiry.society_name',
			'inquiry.area',
			'inquiry.city_id',
			'inquiry.status',
			'inquiry.pincode',
			'inquiry.source_type',
			'inquiry.source_type_lable',
			'inquiry.source_type_value',
			'created_by.first_name as created_by_first_name',
			'created_by.last_name as created_by_last_name',
			'created_by.type as created_by_type',
			'created_by.id as created_by_user_id',
			'assigned_to.first_name as assigned_to_first_name',
			'assigned_to.last_name as assigned_to_last_name',
			'city_list.name as city_list_name',
			'inquiry.follow_up_type',
			'inquiry.follow_up_date_time',
			'inquiry.architect',
			'architect.type as architect_type',
			'architect.first_name as architect_first_name',
			'architect.last_name as architect_last_name',
			'architect.phone_number as architect_phone_number',
			'inquiry.electrician',
			'electrician.type as electrician_type',
			'electrician.first_name as electrician_first_name',
			'electrician.last_name as electrician_last_name',
			'electrician.phone_number as electrician_phone_number',
			'inquiry.quotation',
			'inquiry.quotation_amount',
			'inquiry.stage_of_site',
			'inquiry.billing_invoice',
			'inquiry.billing_amount',
			'inquiry.created_at',
			'inquiry.update_count',
			'inquiry.last_update',
			'inquiry.claimed_date_time',
			'inquiry.is_verified',
			'inquiry.closing_date_time',
			'inquiry.closing_history',
			'inquiry.answer_date_time',
			'inquiry.stage_of_site_date_time',
			'inquiry.source_type_1',
			'inquiry.source_type_value_1',
			'inquiry.source_type_2',
			'inquiry.source_type_value_2',
			'inquiry.source_type_3',
			'inquiry.source_type_value_3',
			'inquiry.source_type_4',
			'inquiry.source_type_value_4',
			'inquiry.material_sent_channel_partner',
			'inquiry.is_predication_sure',
			'inquiry.is_for_tele_sale',
			'inquiry.is_for_manager',
			'inquiry.is_from_mobile',

		);

		if ($request->view_type == 0) {

			$sortColumns = array(
				0 => 'inquiry.id',
				1 => 'inquiry.user_id',
				2 => 'inquiry.follow_up_date_time',
				3 => 'inquiry.status',

			);
		} else if ($request->view_type == 1) {

			$sortColumns = array(
				'inquiry.id',
				'inquiry.phone_number',
				'inquiry.house_no',
				'inquiry.status',
				'inquiry.stage_of_site',
				'inquiry.follow_up_type',
				'inquiry.follow_up_date_time',
				'inquiry.closing_date_time',
				'architect.first_name',
				'electrician.first_name',
				'inquiry.source_type',
				'assigned_to.first_name',
				'created_by.first_name',
				'CONVERT(inquiry.quotation_amount, SIGNED)',
				'CONVERT(inquiry.billing_amount, SIGNED)',

			);
		}
		//DB::enableQueryLog();

		$query = Inquiry::query();

		if ($isAdminOrCompanyAdmin == 1) {
		} else if ($isSalePerson == 1) {

			if ($request->status == 9 || $request->status == 102) {

				$query->where(function ($query2) use ($childSalePersonsIds, $SalesCity) {
					$query2->whereIn('inquiry.assigned_to', $childSalePersonsIds);
					// Axone
					// if (count($SalesCity) > 0) {
					// 	$query2->orWhereIn('inquiry.city_id', $SalesCity);
					// }
				});
			} else {
				$query->whereIn('inquiry.assigned_to', $childSalePersonsIds);
			}
		} else if ($isArchitect == 1) {

			$query->where(function ($query2) {

				$query2->where(function ($query3) {

					$query3->whereIn('inquiry.source_type', array("user-201", "user-202"));
					$query3->where('inquiry.source_type_value', Auth::user()->id);
				});

				$query2->orWhere(function ($query3) {

					$query3->whereIn('inquiry.source_type_1', array("user-201", "user-202"));
					$query3->where('inquiry.source_type_value_1', Auth::user()->id);
				});

				$query2->orWhere(function ($query3) {

					$query3->whereIn('inquiry.source_type_2', array("user-201", "user-202"));
					$query3->where('inquiry.source_type_value_2', Auth::user()->id);
				});

				$query2->orWhere(function ($query3) {

					$query3->whereIn('inquiry.source_type_3', array("user-201", "user-202"));
					$query3->where('inquiry.source_type_value_3', Auth::user()->id);
				});

				$query2->orWhere(function ($query3) {

					$query3->whereIn('inquiry.source_type_4', array("user-201", "user-202"));
					$query3->where('inquiry.source_type_value_4', Auth::user()->id);
				});

				$query2->orWhere(function ($query3) {

					$query3->where('inquiry.architect', Auth::user()->id);
				});
			});
		} else if ($isElectrician == 1) {

			$query->where(function ($query2) {

				$query2->where(function ($query3) {

					$query3->whereIn('inquiry.source_type', array("user-301", "user-301"));
					$query3->where('inquiry.source_type_value', Auth::user()->id);
				});

				$query2->orWhere(function ($query3) {

					$query3->whereIn('inquiry.source_type_1', array("user-301", "user-302"));
					$query3->where('inquiry.source_type_value_1', Auth::user()->id);
				});

				$query2->orWhere(function ($query3) {

					$query3->whereIn('inquiry.source_type_2', array("user-301", "user-302"));
					$query3->where('inquiry.source_type_value_2', Auth::user()->id);
				});

				$query2->orWhere(function ($query3) {

					$query3->whereIn('inquiry.source_type_3', array("user-301", "user-302"));
					$query3->where('inquiry.source_type_value_3', Auth::user()->id);
				});

				$query2->orWhere(function ($query3) {

					$query3->whereIn('inquiry.source_type_4', array("user-301", "user-302"));
					$query3->where('inquiry.source_type_value_4', Auth::user()->id);
				});

				$query2->orWhere(function ($query3) {

					$query3->where('inquiry.electrician', Auth::user()->id);
				});
			});
		} else if ($isChannelPartner != 0) {

			$query->where(function ($query2) {

				$query2->where(function ($query3) {

					$query3->where('inquiry.source_type', "user-" . Auth::user()->type);
					$query3->where('inquiry.source_type_value', Auth::user()->id);
				});

				$query2->orWhere(function ($query3) {

					$query3->where('inquiry.source_type_1', "user-" . Auth::user()->type);
					$query3->where('inquiry.source_type_value_1', Auth::user()->id);
				});

				$query2->orWhere(function ($query3) {

					$query3->where('inquiry.source_type_2', "user-" . Auth::user()->type);
					$query3->where('inquiry.source_type_value_2', Auth::user()->id);
				});
				$query2->orWhere(function ($query3) {

					$query3->where('inquiry.source_type_3', "user-" . Auth::user()->type);
					$query3->where('inquiry.source_type_value_3', Auth::user()->id);
				});

				$query2->orWhere(function ($query3) {

					$query3->where('inquiry.source_type_4', "user-" . Auth::user()->type);
					$query3->where('inquiry.source_type_value_4', Auth::user()->id);
				});
			});
		} else if ($isThirdPartyUser == 1) {

			$query->where(function ($query2) {

				$query2->where(function ($query3) {

					$query3->where('inquiry.source_type', "user-" . Auth::user()->type);
					$query3->where('inquiry.source_type_value', Auth::user()->id);
				});

				$query2->orWhere(function ($query3) {

					$query3->where('inquiry.source_type_1', "user-" . Auth::user()->type);
					$query3->where('inquiry.source_type_value_1', Auth::user()->id);
				});

				$query2->orWhere(function ($query3) {

					$query3->where('inquiry.source_type_2', "user-" . Auth::user()->type);
					$query3->where('inquiry.source_type_value_2', Auth::user()->id);
				});
				$query2->orWhere(function ($query3) {

					$query3->where('inquiry.source_type_3', "user-" . Auth::user()->type);
					$query3->where('inquiry.source_type_value_3', Auth::user()->id);
				});

				$query2->orWhere(function ($query3) {

					$query3->where('inquiry.source_type_4', "user-" . Auth::user()->type);
					$query3->where('inquiry.source_type_value_4', Auth::user()->id);
				});
			});
		} else if ($isTaleSalesUser == 1) {

			$query->whereIn('inquiry.city_id', $TaleSalesCities);
			if ($request->status == 202) {
				$query->whereNotIn('inquiry.status', [9, 11, 14, 12, 10, 101, 102]);
				$query->where('inquiry.is_for_tele_sale', 1);
			}
		}

		if ($isChannelPartner == 0) {

			$query->where('inquiry.is_verified', 1);
		}

		// if ($hasQuotationFilter == 1) {

		// 	$query->where('inquiry.quotation_amount', '>=', $quotationFilter);
		// }

		if ($closingType != "0" && $request->status == 8) {

			$query->where('inquiry.closing_date_time', '!=', null);
			$currentStartDatetime = date('Y-m-d 00:00:00');
			$currentStartDatetime = date('Y-m-d H:i:s', strtotime($currentStartDatetime . " +5 hours"));
			$currentStartDatetime = date('Y-m-d 00:00:00', strtotime($currentStartDatetime . " +30 minutes"));

			// $currentStartDatetime = date('Y-m-d H:i:s', strtotime($currentStartDatetime . " +5 hours"));
			// $currentStartDatetime = date('Y-m-d H:i:s', strtotime($currentStartDatetime . " +30 minutes"));

			$currentEndDatetime = date('Y-m-d 23:59:59');
			$currentEndDatetime = date('Y-m-d H:i:s', strtotime($currentStartDatetime . " +5 hours"));
			$currentEndDatetime = date('Y-m-d 23:59:59', strtotime($currentStartDatetime . " +30 minutes"));
			// $currentEndDatetime = date('Y-m-d H:i:s', strtotime($currentEndDatetime . " +5 hours"));
			// $currentEndDatetime = date('Y-m-d H:i:s', strtotime($currentEndDatetime . " +30 minutes"));

			$currentDatetime = date('Y-m-d H:i:s');

			if ($closingType == "1") {

				$currentWeekDay = date('w', strtotime($currentStartDatetime));
				$weekStartDatetime = date('Y-m-d H:i:s', strtotime($currentStartDatetime . " -" . ($currentWeekDay - 1) . " days"));

				$weekEndDatetime = date('Y-m-d H:i:s', strtotime($currentEndDatetime . " +" . ((7 - $currentWeekDay)) . " days"));

				// $query->where('inquiry.closing_date_time', '>=', $weekStartDatetime);

				//$query->where('inquiry.closing_date_time', '>=', $currentDatetime);
				$query->where('inquiry.closing_date_time', '<=', $weekEndDatetime);
			} else if ($closingType == "2") {

				$monthStartDay = date('Y-m-01 H:i:s', strtotime($currentStartDatetime));
				$monthEndDay = date('Y-m-t H:i:s', strtotime($currentEndDatetime));
				//$query->where('inquiry.closing_date_time', '>=', $monthStartDay);
				//$query->where('inquiry.closing_date_time', '>=', $currentDatetime);
				$query->where('inquiry.closing_date_time', '<=', $monthEndDay);
			} else if ($closingType == "3") {
				$currentStartDatetime = date('Y-m-d H:i:s', strtotime($currentStartDatetime . " +5 hours"));
				$currentStartDatetime = date('Y-m-d H:i:s', strtotime($currentStartDatetime . " +30 minutes"));

				$monthStartDay = date('Y-m-01 H:i:s', strtotime($currentStartDatetime . " + 1 month"));

				// $currentEndDatetimeD = date('d', strtotime($currentEndDatetime));
				// if ($currentEndDatetimeD == 31) {
				// 	$currentEndDatetime = date('Y-m-30 23:59:59', strtotime($currentEndDatetime));
				// }

				$monthEndDay = date('Y-m-t H:i:s', strtotime($monthStartDay));

				//$query->where('inquiry.closing_date_time', '>=', $monthStartDay);
				//$query->where('inquiry.closing_date_time', '>=', $currentDatetime);
				$query->where('inquiry.closing_date_time', '<=', $monthEndDay);

				//print_r($currentStartDatetime);
				// print_r($currentEndDatetime);

			} else if ($closingType == "4") {

				$monthStartDay = date('Y-m-01 H:i:s', strtotime($currentStartDatetime . " + 1 month"));
				$monthEndDay = date('Y-m-t H:i:s', strtotime($currentEndDatetime . " + 2 month"));
				//$query->where('inquiry.closing_date_time', '>=', $monthStartDay);
				//$query->where('inquiry.closing_date_time', '>=', $currentDatetime);

				$query->where('inquiry.closing_date_time', '<=', $monthEndDay);
			} else if ($closingType == "5") {

				$monthStartDay = date('Y-m-01 H:i:s', strtotime($currentStartDatetime . " + 1 month"));
				$monthEndDay = date('Y-m-t H:i:s', strtotime($currentEndDatetime . " + 3 month"));

				//$query->where('inquiry.closing_date_time', '>=', $monthStartDay);
				//$query->where('inquiry.closing_date_time', '>=', $currentDatetime);
				$query->where('inquiry.closing_date_time', '<=', $monthEndDay);
			}
		} else if ($request->status == 8) {
			$query->where('inquiry.closing_date_time', '!=', null);
		}

		if ($request->status == 8) {

			if ($request->inquiry_filter_sure_not_sure != 0) {

				if ($request->inquiry_filter_sure_not_sure == 1) {
					$query->where('inquiry.is_predication_sure', 1);
				} else if ($request->inquiry_filter_sure_not_sure == 2) {
					$query->where('inquiry.is_predication_sure', 0);
				} else if ($request->inquiry_filter_sure_not_sure == 3) {
					$query->where('inquiry.quotation_amount', "");
				}
			}
		}

		if ($request->inquiry_filter_following_date_time != 0) {

			if ($request->inquiry_filter_following_date_time == 1) {

				$query->where(function ($query2) {

					$currentDatetime = date('Y-m-d H:i:s');
					$query2->where('inquiry.follow_up_date_time', null);
				});
			}
		}

		if ($stageOfSite != "0") {

			$query->where(function ($query2) use ($stageOfSite) {

				$query2->where('inquiry.stage_of_site', $stageOfSite);
			});
		}

		if (isset($request->inquiry_filter_material_sent_type) && $request->inquiry_filter_material_sent_type != "0") {

			$materialSentType = $request->inquiry_filter_material_sent_type;
			$query->where(function ($query2) use ($materialSentType) {

				if ($materialSentType == 1) {
					$query2->whereIn('inquiry.status', array(9, 11));
				} else if ($materialSentType == 2) {
					$query2->where('inquiry.is_claimed', 1);
				} else if ($materialSentType == 3) {
					$query2->where('inquiry.billing_invoice', '!=', '');
				} else if ($materialSentType == 4) {
					$query2->whereIn('inquiry.status', array(9, 11));
					$query2->where('inquiry.billing_invoice', '!=', '');
				}
			});
		}

		if ($isArchitect == 1) {

			$statusArray = isset($inquiryStatus[$request->status]['for_architect_ids']) ? $inquiryStatus[$request->status]['for_architect_ids'] : array(0);
			$query->whereIn('inquiry.status', $statusArray);
		} else if ($isElectrician == 1) {

			$statusArray = isset($inquiryStatus[$request->status]['for_electrician_ids']) ? $inquiryStatus[$request->status]['for_electrician_ids'] : array(0);

			$query->whereIn('inquiry.status', $statusArray);
		} else if ($isChannelPartner != 0) {

			if ($request->status != 0) {

				$statusArray = isset($inquiryStatus[$request->status]['for_channel_partner_ids']) ? $inquiryStatus[$request->status]['for_channel_partner_ids'] : array(0);
				$query->whereIn('inquiry.status', $statusArray);
			}
		} else {

			if ($request->status != 0) {

				if ($request->status == 202) {

					// $statusArray = isset($inquiryStatus[201]['for_sales_ids']) ? $inquiryStatus[201]['for_sales_ids'] : array(0);
					// $query->whereIn('inquiry.status', $statusArray);
					$query->whereNotIn('inquiry.status', [9, 11, 14, 12, 10, 101, 102]);

					if ($isSalePerson == 1) {
						$query->Where('inquiry.is_for_manager', 1);
						$query->where(function ($query2) use ($childSalePersonsIds) {

							$query2->WhereIn('inquiry.assigned_to', $childSalePersonsIds);
						});
					} else if ($isTaleSalesUser == 1) {

						$query->where(function ($query2) {
							$query2->where('inquiry.is_for_tele_sale', 1);
						});
					} else {
						$query->where(function ($query2) {
							$query2->where('inquiry.is_for_tele_sale', 1);
							$query2->orWhere('inquiry.is_for_manager', 1);
						});
					}
				} else {
					if ($isSalePerson == 1) {
						$statusArray = isset($inquiryStatus[$request->status]['for_sales_ids']) ? $inquiryStatus[$request->status]['for_sales_ids'] : array(0);
						$query->whereIn('inquiry.status', $statusArray);
					} else if ($isChannelPartner != 0) {

						$statusArray = isset($inquiryStatus[$request->status]['for_channel_partner_ids']) ? $inquiryStatus[$request->status]['for_channel_partner_ids'] : array(0);
						$query->whereIn('inquiry.status', $statusArray);
					} else if ($isAdminOrCompanyAdmin == 1) {

						$statusArray = isset($inquiryStatus[$request->status]['for_user_ids']) ? $inquiryStatus[$request->status]['for_user_ids'] : array(0);
						$query->whereIn('inquiry.status', $statusArray);
					} else if ($isThirdPartyUser == 1) {

						$statusArray = isset($inquiryStatus[$request->status]['for_third_party_ids']) ? $inquiryStatus[$request->status]['for_third_party_ids'] : array(0);
						$query->whereIn('inquiry.status', $statusArray);
					} else if ($isTaleSalesUser == 1) {

						$statusArray = isset($inquiryStatus[$request->status]['for_tele_sales_ids']) ? $inquiryStatus[$request->status]['for_tele_sales_ids'] : array(0);

						$query->whereIn('inquiry.status', $statusArray);
					}
				}
			}
		}

		if (isAdminOrCompanyAdmin() == 0) {
			$query->whereNotIn('inquiry.assigned_to', getInquiryTransferToLeadUserList());
		}
		$recordsTotal = $query->count();

		// dd(DB::getQueryLog());

		//	$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.

		$query = Inquiry::query();
		$query->select($selectColumns);
		$query->leftJoin('users as created_by', 'created_by.id', '=', 'inquiry.user_id');
		$query->leftJoin('users as assigned_to', 'assigned_to.id', '=', 'inquiry.assigned_to');
		$query->leftJoin('users as architect', 'architect.id', '=', 'inquiry.architect');
		$query->leftJoin('users as electrician', 'electrician.id', '=', 'inquiry.electrician');
		$query->leftJoin('city_list', 'city_list.id', '=', 'inquiry.city_id');
		if ($isAdminOrCompanyAdmin == 1) {
		} else if ($isSalePerson == 1) {

			if ($request->status == 9 || $request->status == 102) {

				$query->where(function ($query2) use ($childSalePersonsIds, $SalesCity) {
					$query2->whereIn('inquiry.assigned_to', $childSalePersonsIds);
					// Axone
					// if (count($SalesCity) > 0) {
					// 	$query2->orWhereIn('inquiry.city_id', $SalesCity);
					// }
				});
			} else {
				$query->whereIn('inquiry.assigned_to', $childSalePersonsIds);
			}
		} else if ($isArchitect == 1) {

			$query->where(function ($query2) {

				$query2->where(function ($query3) {

					$query3->whereIn('inquiry.source_type', array("user-201", "user-202"));
					$query3->where('inquiry.source_type_value', Auth::user()->id);
				});

				$query2->orWhere(function ($query3) {

					$query3->whereIn('inquiry.source_type_1', array("user-201", "user-202"));
					$query3->where('inquiry.source_type_value_1', Auth::user()->id);
				});

				$query2->orWhere(function ($query3) {

					$query3->whereIn('inquiry.source_type_2', array("user-201", "user-202"));
					$query3->where('inquiry.source_type_value_2', Auth::user()->id);
				});

				$query2->orWhere(function ($query3) {

					$query3->whereIn('inquiry.source_type_3', array("user-201", "user-202"));
					$query3->where('inquiry.source_type_value_3', Auth::user()->id);
				});

				$query2->orWhere(function ($query3) {

					$query3->whereIn('inquiry.source_type_4', array("user-201", "user-202"));
					$query3->where('inquiry.source_type_value_4', Auth::user()->id);
				});

				$query2->orWhere(function ($query3) {

					$query3->where('inquiry.architect', Auth::user()->id);
				});
			});

			// $query->whereIn('inquiry.source_type', array("user-201", "user-202"));
			// // $query->where('inquiry.source_type_value', Auth::user()->id);

			// $query->where(function ($query2) {

			// 	$query2->where(function ($query3) {

			// 		$query3->whereIn('inquiry.source_type', array("user-201", "user-202"));
			// 		$query3->where('inquiry.source_type_value', Auth::user()->id);

			// 	});

			// });

			// $query->orWhere(function ($query2) {

			// 	$query2->where(function ($query3) {

			// 		$query3->whereIn('inquiry.source_type_1', array("user-201", "user-202"));
			// 		$query3->where('inquiry.source_type_value_1', Auth::user()->id);

			// 	});

			// });

			// $query->orWhere(function ($query2) {

			// 	$query2->where(function ($query3) {

			// 		$query3->whereIn('inquiry.source_type_2', array("user-201", "user-202"));
			// 		$query3->where('inquiry.source_type_value_2', Auth::user()->id);

			// 	});

			// });

			// $query->orWhere(function ($query2) {

			// 	$query2->where(function ($query3) {

			// 		$query3->whereIn('inquiry.source_type_3', array("user-201", "user-202"));
			// 		$query3->where('inquiry.source_type_value_3', Auth::user()->id);

			// 	});

			// });

			// $query->orWhere(function ($query2) {

			// 	$query2->where(function ($query3) {

			// 		$query3->whereIn('inquiry.source_type_4', array("user-201", "user-202"));
			// 		$query3->where('inquiry.source_type_value_4', Auth::user()->id);

			// 	});

			// });

			// $query->orWhere(function ($query2) {

			// 	$query2->where(function ($query3) {

			// 		$query3->where('inquiry.architect', Auth::user()->id);

			// 	});

			// });

		} else if ($isElectrician == 1) {

			$query->where(function ($query2) {

				$query2->where(function ($query3) {

					$query3->whereIn('inquiry.source_type', array("user-301", "user-301"));
					$query3->where('inquiry.source_type_value', Auth::user()->id);
				});

				$query2->orWhere(function ($query3) {

					$query3->whereIn('inquiry.source_type_1', array("user-301", "user-302"));
					$query3->where('inquiry.source_type_value_1', Auth::user()->id);
				});

				$query2->orWhere(function ($query3) {

					$query3->whereIn('inquiry.source_type_2', array("user-301", "user-302"));
					$query3->where('inquiry.source_type_value_2', Auth::user()->id);
				});

				$query2->orWhere(function ($query3) {

					$query3->whereIn('inquiry.source_type_3', array("user-301", "user-302"));
					$query3->where('inquiry.source_type_value_3', Auth::user()->id);
				});

				$query2->orWhere(function ($query3) {

					$query3->whereIn('inquiry.source_type_4', array("user-301", "user-302"));
					$query3->where('inquiry.source_type_value_4', Auth::user()->id);
				});

				$query2->orWhere(function ($query3) {

					$query3->where('inquiry.electrician', Auth::user()->id);
				});
			});
		} else if ($isChannelPartner != 0) {

			$query->where(function ($query2) {

				$query2->where(function ($query3) {

					$query3->where('inquiry.source_type', "user-" . Auth::user()->type);
					$query3->where('inquiry.source_type_value', Auth::user()->id);
				});

				$query2->orWhere(function ($query3) {

					$query3->where('inquiry.source_type_1', "user-" . Auth::user()->type);
					$query3->where('inquiry.source_type_value_1', Auth::user()->id);
				});

				$query2->orWhere(function ($query3) {

					$query3->where('inquiry.source_type_2', "user-" . Auth::user()->type);
					$query3->where('inquiry.source_type_value_2', Auth::user()->id);
				});
				$query2->orWhere(function ($query3) {

					$query3->where('inquiry.source_type_3', "user-" . Auth::user()->type);
					$query3->where('inquiry.source_type_value_3', Auth::user()->id);
				});

				$query2->orWhere(function ($query3) {

					$query3->where('inquiry.source_type_4', "user-" . Auth::user()->type);
					$query3->where('inquiry.source_type_value_4', Auth::user()->id);
				});
			});
		} else if ($isThirdPartyUser == 1) {

			$query->where(function ($query2) {

				$query2->where(function ($query3) {

					$query3->where('inquiry.source_type', "user-" . Auth::user()->type);
					$query3->where('inquiry.source_type_value', Auth::user()->id);
				});

				$query2->orWhere(function ($query3) {

					$query3->where('inquiry.source_type_1', "user-" . Auth::user()->type);
					$query3->where('inquiry.source_type_value_1', Auth::user()->id);
				});

				$query2->orWhere(function ($query3) {

					$query3->where('inquiry.source_type_2', "user-" . Auth::user()->type);
					$query3->where('inquiry.source_type_value_2', Auth::user()->id);
				});
				$query2->orWhere(function ($query3) {

					$query3->where('inquiry.source_type_3', "user-" . Auth::user()->type);
					$query3->where('inquiry.source_type_value_3', Auth::user()->id);
				});

				$query2->orWhere(function ($query3) {

					$query3->where('inquiry.source_type_4', "user-" . Auth::user()->type);
					$query3->where('inquiry.source_type_value_4', Auth::user()->id);
				});
			});
		} else if ($isTaleSalesUser == 1) {
			$query->whereIn('inquiry.city_id', $TaleSalesCities);
			if ($request->status == 202) {
				$query->whereNotIn('inquiry.status', [9, 11, 14, 12, 10, 101, 102]);
				$query->where('inquiry.is_for_tele_sale', 1);
			}
		}

		if ($isChannelPartner == 0) {

			$query->where('inquiry.is_verified', 1);
		}
		if ($hasQuotationFilter == 1) {
			$query->whereRaw('(`inquiry`.`quotation_amount` * 1 ) >=' . $quotationFilter);
		}

		if ($closingType != "0" && $request->status == 8) {

			$query->where('inquiry.closing_date_time', '!=', null);
			$currentStartDatetime = date('Y-m-d 00:00:00');
			$currentStartDatetime = date('Y-m-d H:i:s', strtotime($currentStartDatetime . " +5 hours"));
			$currentStartDatetime = date('Y-m-d 00:00:00', strtotime($currentStartDatetime . " +30 minutes"));

			// $currentStartDatetime = date('Y-m-d H:i:s', strtotime($currentStartDatetime . " +5 hours"));
			// $currentStartDatetime = date('Y-m-d H:i:s', strtotime($currentStartDatetime . " +30 minutes"));

			$currentEndDatetime = date('Y-m-d 23:59:59');

			$currentEndDatetime = date('Y-m-d H:i:s', strtotime($currentStartDatetime . " +5 hours"));
			$currentEndDatetime = date('Y-m-d 23:59:59', strtotime($currentStartDatetime . " +30 minutes"));

			if ($closingType == "1") {

				$currentWeekDay = date('w', strtotime($currentStartDatetime));
				$weekStartDatetime = date('Y-m-d H:i:s', strtotime($currentStartDatetime . " -" . ($currentWeekDay - 1) . " days"));

				$weekEndDatetime = date('Y-m-d H:i:s', strtotime($currentEndDatetime . " +" . ((7 - $currentWeekDay)) . " days"));

				// $query->where('inquiry.closing_date_time', '>=', $weekStartDatetime);

				//$query->where('inquiry.closing_date_time', '>=', $currentDatetime);
				$query->where('inquiry.closing_date_time', '<=', $weekEndDatetime);
			} else if ($closingType == "2") {

				$monthStartDay = date('Y-m-01 H:i:s', strtotime($currentStartDatetime));
				$monthEndDay = date('Y-m-t H:i:s', strtotime($currentEndDatetime));
				//$query->where('inquiry.closing_date_time', '>=', $monthStartDay);
				//$query->where('inquiry.closing_date_time', '>=', $currentDatetime);
				$query->where('inquiry.closing_date_time', '<=', $monthEndDay);
			} else if ($closingType == "3") {
				$currentStartDatetime = date('Y-m-d H:i:s', strtotime($currentStartDatetime . " +5 hours"));
				$currentStartDatetime = date('Y-m-d H:i:s', strtotime($currentStartDatetime . " +30 minutes"));

				$monthStartDay = date('Y-m-01 H:i:s', strtotime($currentStartDatetime . " + 1 month"));

				// $currentEndDatetimeD = date('d', strtotime($currentEndDatetime));
				// if ($currentEndDatetimeD == 31) {
				// 	$currentEndDatetime = date('Y-m-30 23:59:59', strtotime($currentEndDatetime));
				// }

				$monthEndDay = date('Y-m-t H:i:s', strtotime($monthStartDay));

				//$query->where('inquiry.closing_date_time', '>=', $monthStartDay);
				//$query->where('inquiry.closing_date_time', '>=', $currentDatetime);
				$query->where('inquiry.closing_date_time', '<=', $monthEndDay);
			} else if ($closingType == "4") {

				$monthStartDay = date('Y-m-01 H:i:s', strtotime($currentStartDatetime . " + 1 month"));
				$monthEndDay = date('Y-m-t H:i:s', strtotime($currentEndDatetime . " + 2 month"));
				//$query->where('inquiry.closing_date_time', '>=', $monthStartDay);
				//$query->where('inquiry.closing_date_time', '>=', $currentDatetime);

				$query->where('inquiry.closing_date_time', '<=', $monthEndDay);
			} else if ($closingType == "5") {

				$monthStartDay = date('Y-m-01 H:i:s', strtotime($currentStartDatetime . " + 1 month"));
				$monthEndDay = date('Y-m-t H:i:s', strtotime($currentEndDatetime . " + 3 month"));

				//$query->where('inquiry.closing_date_time', '>=', $monthStartDay);
				$query->where('inquiry.closing_date_time', '>=', $currentDatetime);
				$query->where('inquiry.closing_date_time', '<=', $monthEndDay);
			}
		} else if ($request->status == 8) {
			$query->where('inquiry.closing_date_time', '!=', null);
		}

		if ($request->status == 8) {

			if ($request->inquiry_filter_sure_not_sure != 0) {

				if ($request->inquiry_filter_sure_not_sure == 1) {
					$query->where('inquiry.is_predication_sure', 1);
				} else if ($request->inquiry_filter_sure_not_sure == 2) {
					$query->where('inquiry.is_predication_sure', 0);
				} else if ($request->inquiry_filter_sure_not_sure == 3) {
					$query->where('inquiry.quotation_amount', "");
				}
			}
		}

		if ($request->inquiry_filter_following_date_time != 0) {

			if ($request->inquiry_filter_following_date_time == 1) {

				$query->where(function ($query2) {

					$currentDatetime = date('Y-m-d H:i:s');

					$query2->where('inquiry.follow_up_date_time', null);
					$query2->orWhere('inquiry.follow_up_date_time', '<', $currentDatetime);
				});
			} else {

				$query->where(function ($query2) {
					$currentDatetime = date('Y-m-d H:i:s');
					$nextDateTime = date('Y-m-d H:i:s', strtotime("+2 day"));
					$query2->Where('inquiry.follow_up_date_time', '>', $currentDatetime);
					$query2->Where('inquiry.follow_up_date_time', '<', $nextDateTime);
				});
			}
		}

		if ($stageOfSite != "0") {

			$query->where(function ($query2) use ($stageOfSite) {

				$query2->where('inquiry.stage_of_site', $stageOfSite);
			});
		}

		if (isset($request->inquiry_filter_material_sent_type) && $request->inquiry_filter_material_sent_type != "0") {

			$materialSentType = $request->inquiry_filter_material_sent_type;
			$query->where(function ($query2) use ($materialSentType) {

				if ($materialSentType == 1) {
					$query2->whereIn('inquiry.status', array(9, 11));
				} else if ($materialSentType == 2) {
					$query2->where('inquiry.is_claimed', 1);
				} else if ($materialSentType == 3) {
					$query2->where('inquiry.billing_invoice', '!=', '');
				} else if ($materialSentType == 4) {
					$query2->whereIn('inquiry.status', array(9, 11));
					$query2->where('inquiry.billing_invoice', '!=', '');
				}
			});
		}
		if ($isArchitect == 1) {

			$statusArray = isset($inquiryStatus[$request->status]['for_architect_ids']) ? $inquiryStatus[$request->status]['for_architect_ids'] : array(0);

			$query->whereIn('inquiry.status', $statusArray);
		} else if ($isElectrician == 1) {

			$statusArray = isset($inquiryStatus[$request->status]['for_electrician_ids']) ? $inquiryStatus[$request->status]['for_electrician_ids'] : array(0);

			$query->whereIn('inquiry.status', $statusArray);
		} else if ($isChannelPartner != 0) {

			if ($request->status != 0) {

				$statusArray = isset($inquiryStatus[$request->status]['for_channel_partner_ids']) ? $inquiryStatus[$request->status]['for_channel_partner_ids'] : array(0);

				$query->whereIn('inquiry.status', $statusArray);
			}
		} else {

			if ($request->status != 0) {

				if ($request->status == 202) {

					$query->whereNotIn('inquiry.status', [9, 11, 14, 12, 10, 101, 102]);
					if ($isSalePerson == 1) {

						$query->Where('inquiry.is_for_manager', 1);
						$query->where(function ($query2) use ($childSalePersonsIds) {

							$query2->whereIn('inquiry.assigned_to', $childSalePersonsIds);
						});
					} else if ($isTaleSalesUser == 1) {

						$query->where(function ($query2) {
							$query2->where('inquiry.is_for_tele_sale', 1);
						});
					} else {
						$query->where(function ($query2) {
							$query2->where('inquiry.is_for_tele_sale', 1);
							$query2->orWhere('inquiry.is_for_manager', 1);
						});
					}
				} else {

					if ($isSalePerson == 1) {
						$statusArray = isset($inquiryStatus[$request->status]['for_sales_ids']) ? $inquiryStatus[$request->status]['for_sales_ids'] : array(0);
						$query->whereIn('inquiry.status', $statusArray);
					} else if ($isChannelPartner != 0) {

						$statusArray = isset($inquiryStatus[$request->status]['for_channel_partner_ids']) ? $inquiryStatus[$request->status]['for_channel_partner_ids'] : array(0);
						$query->whereIn('inquiry.status', $statusArray);
					} else if ($isAdminOrCompanyAdmin == 1) {

						$statusArray = isset($inquiryStatus[$request->status]['for_user_ids']) ? $inquiryStatus[$request->status]['for_user_ids'] : array(0);
						$query->whereIn('inquiry.status', $statusArray);
					} else if ($isThirdPartyUser == 1) {

						$statusArray = isset($inquiryStatus[$request->status]['for_third_party_ids']) ? $inquiryStatus[$request->status]['for_third_party_ids'] : array(0);
						$query->whereIn('inquiry.status', $statusArray);
					} else if ($isTaleSalesUser == 1) {

						$statusArray = isset($inquiryStatus[$request->status]['for_tele_sales_ids']) ? $inquiryStatus[$request->status]['for_tele_sales_ids'] : array(0);

						$query->whereIn('inquiry.status', $statusArray);
					}
				}
			}
		}
		// $query->limit($request->length);
		// $query->offset($request->start);
		$query->orderByRaw($sortColumns[$request['order'][0]['column']] . " " . $request['order'][0]['dir']);

		$i = $request['inquiry_filter_search_type'];

		if ($i == 14) {
			$query->Where('source_type', 'user-201');
		}

		if ($i == 15) {
			$query->Where('source_type', 'user-202');
		}

		if ($i == 16) {
			$query->Where('source_type', 'user-301');
		}
		if ($i == 17) {
			$query->Where('source_type', 'user-302');
		}

		if ($i == 18) {
			$query->Where('architect', 0);
		}
		if ($i == 19) {
			$query->Where('electrician', 0);
		}

		if ($searchValue != "") {
			$isFilterApply = 1;

			if ($i == 12) {

				$query->WhereIn('source_type', $sourceTypeFilter);
			} else if ($i == 13) {

				$query->where(function ($query) use ($searchValue, $sourceUsers) {

					if (count($sourceUsers) > 0) {
						$query->WhereIn('inquiry.source_type_value', $sourceUsers);
						$query->orWhereRaw('inquiry.source_type_value like ?', [$searchValue]);
						$query->orWhereRaw('inquiry.source_type_value like ? ', ["%" . $searchValue . "%"]);
					} else {
						$query->WhereRaw('inquiry.source_type_value like ?', [$searchValue]);
						$query->orWhereRaw('inquiry.source_type_value like ? ', ["%" . $searchValue . "%"]);
					}
				});
			} else {

				if ($i == 0) {

					$query->where(function ($query) use ($searchValue, $searchColumns, $i, $sourceTypeFilter, $sourceUsers) {

						foreach ($searchColumns as $keyS => $valueS) {

							if ($keyS == 0) {
								continue;
							}

							if ($keyS == 12) {
								$query->orWhereIn('source_type', $sourceTypeFilter);
								continue;
							}

							if ($keyS == 13) {
								$query->orWhere(function ($query) use ($searchValue, $sourceUsers) {

									if (count($sourceUsers) > 0) {
										$query->WhereIn('inquiry.source_type_value', $sourceUsers);
										$query->orWhereRaw('inquiry.source_type_value like ?', [$searchValue]);
										$query->orWhereRaw('inquiry.source_type_value like ? ', ["%" . $searchValue . "%"]);
									} else {
										$query->WhereRaw('inquiry.source_type_value like ?', [$searchValue]);
										$query->orWhereRaw('inquiry.source_type_value like ? ', ["%" . $searchValue . "%"]);
									}
								});
								continue;
							}

							if ($keyS == 1) {

								$query->WhereRaw($searchColumns[$keyS] . ' like ?', [$searchValue]);
							}

							$query->orWhereRaw($searchColumns[$keyS] . ' like ? ', ["%" . $searchValue . "%"]);
						}
					});
				} else {

					$query->where(function ($query) use ($searchValue, $searchColumns, $i) {

						$query->WhereRaw($searchColumns[$i] . ' like ?', [$searchValue]);
						$query->orWhereRaw($searchColumns[$i] . ' like ? ', ["%" . $searchValue . "%"]);
					});
				}
			}
		}

		/// START ADVANCE FILTER
		if ($hasAdvanceFilter == 1) {

			$query->where(function ($query2) use ($advanceFilterType, $advanceFilterText, $advanceFilterKey, $advanceFilterTextAdditional) {

				foreach ($advanceFilterType as $keyNameAdvance => $keyAdvance) {

					if ($keyAdvance == 10) {
						$query2->Where('source_type', 'user-201');
					} else if ($keyAdvance == 11) {
						$query2->Where('source_type', 'user-202');
					} elseif ($keyAdvance == 15) {
						$query2->Where('source_type', 'user-301');
					} else if ($keyAdvance == 16) {
						$query2->Where('source_type', 'user-302');
					} else if ($keyAdvance == 13) {

						$query2->WhereIn('source_type', $advanceFilterText[$keyNameAdvance]);
						continue;
					} else if ($keyAdvance == 14) {
						if (count($advanceFilterText[$keyNameAdvance]) > 0) {
							$query2->WhereIn('inquiry.source_type_value', $advanceFilterText[$keyNameAdvance]);
							$query2->orWhereRaw('inquiry.source_type_value like ?', [$advanceFilterTextAdditional[$keyNameAdvance]]);
							$query2->orWhereRaw('inquiry.source_type_value like ? ', ["%" . $advanceFilterTextAdditional[$keyNameAdvance] . "%"]);
						} else {
							$query2->WhereRaw('inquiry.source_type_value like ?', [$advanceFilterTextAdditional[$keyNameAdvance]]);
							$query2->orWhereRaw('inquiry.source_type_value like ? ', ["%" . $advanceFilterTextAdditional[$keyNameAdvance] . "%"]);
						}
						continue;
					}

					$query2->where(function ($query3) use ($advanceFilterType, $advanceFilterText, $advanceFilterKey, $keyNameAdvance, $keyAdvance) {

						//$query3->WhereRaw($advanceFilterKey[$keyAdvance] . ' = ?', [$advanceFilterText[$keyNameAdvance]]);

						if (isset($advanceFilterText[$keyNameAdvance]) && $advanceFilterText[$keyNameAdvance] != "") {

							$query3->WhereRaw($advanceFilterKey[$keyAdvance] . ' like ?', [$advanceFilterText[$keyNameAdvance]]);
							$query3->orWhereRaw($advanceFilterKey[$keyAdvance] . ' like ? ', ["%" . $advanceFilterText[$keyNameAdvance] . "%"]);
						}
					});
				}
			});
		}

		/// END ADVANCE FILTER
		if (isAdminOrCompanyAdmin() == 0) {
			$query->whereNotIn('inquiry.assigned_to', getInquiryTransferToLeadUserList());
		}

		$recordsFiltered = $query->count();

		// print_r(DB::getQueryLog());
		// die;
		$quotationTotal = $query->sum('quotation_amount');

		$query = Inquiry::query();
		$query->select($selectColumns);
		$query->leftJoin('users as created_by', 'created_by.id', '=', 'inquiry.user_id');
		$query->leftJoin('users as assigned_to', 'assigned_to.id', '=', 'inquiry.assigned_to');
		$query->leftJoin('users as architect', 'architect.id', '=', 'inquiry.architect');
		$query->leftJoin('users as electrician', 'electrician.id', '=', 'inquiry.electrician');
		$query->leftJoin('city_list', 'city_list.id', '=', 'inquiry.city_id');
		if ($isAdminOrCompanyAdmin == 1) {
		} else if ($isSalePerson == 1) {

			if ($request->status == 9 || $request->status == 102) {

				$query->where(function ($query2) use ($childSalePersonsIds, $SalesCity) {
					$query2->whereIn('inquiry.assigned_to', $childSalePersonsIds);
					// Axone
					// if (count($SalesCity) > 0) {
					// 	$query2->orWhereIn('inquiry.city_id', $SalesCity);
					// }
				});
			} else {
				$query->whereIn('inquiry.assigned_to', $childSalePersonsIds);
			}
		} else if ($isArchitect == 1) {

			$query->where(function ($query2) {

				$query2->where(function ($query3) {

					$query3->whereIn('inquiry.source_type', array("user-201", "user-202"));
					$query3->where('inquiry.source_type_value', Auth::user()->id);
				});

				$query2->orWhere(function ($query3) {

					$query3->whereIn('inquiry.source_type_1', array("user-201", "user-202"));
					$query3->where('inquiry.source_type_value_1', Auth::user()->id);
				});

				$query2->orWhere(function ($query3) {

					$query3->whereIn('inquiry.source_type_2', array("user-201", "user-202"));
					$query3->where('inquiry.source_type_value_2', Auth::user()->id);
				});

				$query2->orWhere(function ($query3) {

					$query3->whereIn('inquiry.source_type_3', array("user-201", "user-202"));
					$query3->where('inquiry.source_type_value_3', Auth::user()->id);
				});

				$query2->orWhere(function ($query3) {

					$query3->whereIn('inquiry.source_type_4', array("user-201", "user-202"));
					$query3->where('inquiry.source_type_value_4', Auth::user()->id);
				});

				$query2->orWhere(function ($query3) {

					$query3->where('inquiry.architect', Auth::user()->id);
				});
			});

			// $query->where(function ($query2) {

			// 	$query2->where(function ($query3) {

			// 		$query3->whereIn('inquiry.source_type', array("user-201", "user-202"));
			// 		$query3->where('inquiry.source_type_value', Auth::user()->id);

			// 	});

			// });

			// $query->orWhere(function ($query2) {

			// 	$query2->where(function ($query3) {

			// 		$query3->whereIn('inquiry.source_type_1', array("user-201", "user-202"));
			// 		$query3->where('inquiry.source_type_value_1', Auth::user()->id);

			// 	});

			// });

			// $query->orWhere(function ($query2) {

			// 	$query2->where(function ($query3) {

			// 		$query3->whereIn('inquiry.source_type_2', array("user-201", "user-202"));
			// 		$query3->where('inquiry.source_type_value_2', Auth::user()->id);

			// 	});

			// });

			// $query->orWhere(function ($query2) {

			// 	$query2->where(function ($query3) {

			// 		$query3->whereIn('inquiry.source_type_3', array("user-201", "user-202"));
			// 		$query3->where('inquiry.source_type_value_3', Auth::user()->id);

			// 	});

			// });

			// $query->orWhere(function ($query2) {

			// 	$query2->where(function ($query3) {

			// 		$query3->whereIn('inquiry.source_type_4', array("user-201", "user-202"));
			// 		$query3->where('inquiry.source_type_value_4', Auth::user()->id);

			// 	});

			// });

			// $query->orWhere(function ($query2) {

			// 	$query2->where(function ($query3) {

			// 		$query3->where('inquiry.architect', Auth::user()->id);

			// 	});

			// });

			// $query->whereIn('source_type', array("user-201", "user-202"));
			// $query->where('source_type_value', Auth::user()->id);

		} else if ($isElectrician == 1) {

			$query->where(function ($query2) {

				$query2->where(function ($query3) {

					$query3->whereIn('inquiry.source_type', array("user-301", "user-301"));
					$query3->where('inquiry.source_type_value', Auth::user()->id);
				});

				$query2->orWhere(function ($query3) {

					$query3->whereIn('inquiry.source_type_1', array("user-301", "user-302"));
					$query3->where('inquiry.source_type_value_1', Auth::user()->id);
				});

				$query2->orWhere(function ($query3) {

					$query3->whereIn('inquiry.source_type_2', array("user-301", "user-302"));
					$query3->where('inquiry.source_type_value_2', Auth::user()->id);
				});

				$query2->orWhere(function ($query3) {

					$query3->whereIn('inquiry.source_type_3', array("user-301", "user-302"));
					$query3->where('inquiry.source_type_value_3', Auth::user()->id);
				});

				$query2->orWhere(function ($query3) {

					$query3->whereIn('inquiry.source_type_4', array("user-301", "user-302"));
					$query3->where('inquiry.source_type_value_4', Auth::user()->id);
				});

				$query2->orWhere(function ($query3) {

					$query3->where('inquiry.electrician', Auth::user()->id);
				});
			});
		} else if ($isChannelPartner != 0) {

			$query->where(function ($query2) {

				$query2->where(function ($query3) {

					$query3->where('inquiry.source_type', "user-" . Auth::user()->type);
					$query3->where('inquiry.source_type_value', Auth::user()->id);
				});

				$query2->orWhere(function ($query3) {

					$query3->where('inquiry.source_type_1', "user-" . Auth::user()->type);
					$query3->where('inquiry.source_type_value_1', Auth::user()->id);
				});

				$query2->orWhere(function ($query3) {

					$query3->where('inquiry.source_type_2', "user-" . Auth::user()->type);
					$query3->where('inquiry.source_type_value_2', Auth::user()->id);
				});
				$query2->orWhere(function ($query3) {

					$query3->where('inquiry.source_type_3', "user-" . Auth::user()->type);
					$query3->where('inquiry.source_type_value_3', Auth::user()->id);
				});

				$query2->orWhere(function ($query3) {

					$query3->where('inquiry.source_type_4', "user-" . Auth::user()->type);
					$query3->where('inquiry.source_type_value_4', Auth::user()->id);
				});
			});
		} else if ($isThirdPartyUser == 1) {

			$query->where(function ($query2) {

				$query2->where(function ($query3) {

					$query3->where('inquiry.source_type', "user-" . Auth::user()->type);
					$query3->where('inquiry.source_type_value', Auth::user()->id);
				});

				$query2->orWhere(function ($query3) {

					$query3->where('inquiry.source_type_1', "user-" . Auth::user()->type);
					$query3->where('inquiry.source_type_value_1', Auth::user()->id);
				});

				$query2->orWhere(function ($query3) {

					$query3->where('inquiry.source_type_2', "user-" . Auth::user()->type);
					$query3->where('inquiry.source_type_value_2', Auth::user()->id);
				});
				$query2->orWhere(function ($query3) {

					$query3->where('inquiry.source_type_3', "user-" . Auth::user()->type);
					$query3->where('inquiry.source_type_value_3', Auth::user()->id);
				});

				$query2->orWhere(function ($query3) {

					$query3->where('inquiry.source_type_4', "user-" . Auth::user()->type);
					$query3->where('inquiry.source_type_value_4', Auth::user()->id);
				});
			});
		} else if ($isTaleSalesUser == 1) {

			$query->whereIn('inquiry.city_id', $TaleSalesCities);
			if ($request->status == 202) {
				$query->whereNotIn('inquiry.status', [9, 11, 14, 12, 10, 101, 102]);
				$query->where('inquiry.is_for_tele_sale', 1);
			}
		}

		if ($isChannelPartner == 0) {

			$query->where('inquiry.is_verified', 1);
		}
		if ($hasQuotationFilter == 1) {

			$query->whereRaw('(`inquiry`.`quotation_amount` * 1 ) >=' . $quotationFilter);
		}

		if ($closingType != "0" && $request->status == 8) {

			$query->where('inquiry.closing_date_time', '!=', null);
			$currentStartDatetime = date('Y-m-d 00:00:00');
			$currentStartDatetime = date('Y-m-d H:i:s', strtotime($currentStartDatetime . " +5 hours"));
			$currentStartDatetime = date('Y-m-d 00:00:00', strtotime($currentStartDatetime . " +30 minutes"));

			$currentEndDatetime = date('Y-m-d 23:59:59');
			$currentEndDatetime = date('Y-m-d H:i:s', strtotime($currentStartDatetime . " +5 hours"));
			$currentEndDatetime = date('Y-m-d 23:59:59', strtotime($currentStartDatetime . " +30 minutes"));

			if ($closingType == "1") {

				$currentWeekDay = date('w', strtotime($currentStartDatetime));
				$weekStartDatetime = date('Y-m-d H:i:s', strtotime($currentStartDatetime . " -" . ($currentWeekDay - 1) . " days"));

				$weekEndDatetime = date('Y-m-d H:i:s', strtotime($currentEndDatetime . " +" . ((7 - $currentWeekDay)) . " days"));

				// $query->where('inquiry.closing_date_time', '>=', $weekStartDatetime);

				//$query->where('inquiry.closing_date_time', '>=', $currentDatetime);
				$query->where('inquiry.closing_date_time', '<=', $weekEndDatetime);
			} else if ($closingType == "2") {

				$monthStartDay = date('Y-m-01 H:i:s', strtotime($currentStartDatetime));
				$monthEndDay = date('Y-m-t H:i:s', strtotime($currentEndDatetime));
				//$query->where('inquiry.closing_date_time', '>=', $monthStartDay);
				//$query->where('inquiry.closing_date_time', '>=', $currentDatetime);
				$query->where('inquiry.closing_date_time', '<=', $monthEndDay);
			} else if ($closingType == "3") {

				$currentStartDatetime = date('Y-m-d H:i:s', strtotime($currentStartDatetime . " +5 hours"));
				$currentStartDatetime = date('Y-m-d H:i:s', strtotime($currentStartDatetime . " +30 minutes"));

				$monthStartDay = date('Y-m-01 H:i:s', strtotime($currentStartDatetime . " + 1 month"));

				// $currentEndDatetimeD = date('d', strtotime($currentEndDatetime));
				// if ($currentEndDatetimeD == 31) {
				// 	$currentEndDatetime = date('Y-m-30 23:59:59', strtotime($currentEndDatetime));
				// }

				$monthEndDay = date('Y-m-t 23:59:59', strtotime($monthStartDay));

				//$query->where('inquiry.closing_date_time', '>=', $monthStartDay);
				//$query->where('inquiry.closing_date_time', '>=', $currentDatetime);
				$query->where('inquiry.closing_date_time', '<=', $monthEndDay);
			} else if ($closingType == "4") {

				$monthStartDay = date('Y-m-01 H:i:s', strtotime($currentStartDatetime . " + 1 month"));
				$monthEndDay = date('Y-m-t H:i:s', strtotime($currentEndDatetime . " + 2 month"));
				//$query->where('inquiry.closing_date_time', '>=', $monthStartDay);
				//$query->where('inquiry.closing_date_time', '>=', $currentDatetime);

				$query->where('inquiry.closing_date_time', '<=', $monthEndDay);
			} else if ($closingType == "5") {

				$monthStartDay = date('Y-m-01 H:i:s', strtotime($currentStartDatetime . " + 1 month"));
				$monthEndDay = date('Y-m-t H:i:s', strtotime($currentEndDatetime . " + 3 month"));

				//$query->where('inquiry.closing_date_time', '>=', $monthStartDay);
				//$query->where('inquiry.closing_date_time', '>=', $currentDatetime);
				$query->where('inquiry.closing_date_time', '<=', $monthEndDay);
			}
		} else if ($request->status == 8) {
			$query->where('inquiry.closing_date_time', '!=', null);
		}

		if ($request->status == 8) {

			if ($request->inquiry_filter_sure_not_sure != 0) {

				if ($request->inquiry_filter_sure_not_sure == 1) {
					$query->where('inquiry.is_predication_sure', 1);
				} else if ($request->inquiry_filter_sure_not_sure == 2) {
					$query->where('inquiry.is_predication_sure', 0);
				} else if ($request->inquiry_filter_sure_not_sure == 3) {
					$query->where('inquiry.quotation_amount', "");
				}
			}
		}

		if ($request->inquiry_filter_following_date_time != 0) {

			if ($request->inquiry_filter_following_date_time == 1) {

				$query->where(function ($query2) {

					$currentDatetime = date('Y-m-d H:i:s');

					$query2->where('follow_up_date_time', null);
					$query2->orWhere('follow_up_date_time', '<', $currentDatetime);
				});
			} else {

				$query->where(function ($query2) {
					$currentDatetime = date('Y-m-d H:i:s');
					$nextDateTime = date('Y-m-d H:i:s', strtotime("+2 day"));
					$query2->Where('follow_up_date_time', '>', $currentDatetime);
					$query2->Where('follow_up_date_time', '<', $nextDateTime);
				});
			}
		}

		if ($stageOfSite != "0") {

			$query->where(function ($query2) use ($stageOfSite) {

				$query2->where('inquiry.stage_of_site', $stageOfSite);
			});
		}

		if (isset($request->inquiry_filter_material_sent_type) && $request->inquiry_filter_material_sent_type != "0") {

			$materialSentType = $request->inquiry_filter_material_sent_type;

			$query->where(function ($query2) use ($materialSentType) {

				if ($materialSentType == 1) {
					$query2->whereIn('inquiry.status', array(9, 11));
				} else if ($materialSentType == 2) {
					$query2->where('inquiry.is_claimed', 1);
				} else if ($materialSentType == 3) {
					$query2->where('inquiry.billing_invoice', '!=', '');
				} else if ($materialSentType == 4) {
					$query2->whereIn('inquiry.status', array(9, 11));
					$query2->where('inquiry.billing_invoice', '!=', '');
				}
			});
		}

		if ($isArchitect == 1) {

			$statusArray = isset($inquiryStatus[$request->status]['for_architect_ids']) ? $inquiryStatus[$request->status]['for_architect_ids'] : array(0);

			$query->whereIn('inquiry.status', $statusArray);
		} else if ($isElectrician == 1) {

			$statusArray = isset($inquiryStatus[$request->status]['for_electrician_ids']) ? $inquiryStatus[$request->status]['for_electrician_ids'] : array(0);

			$query->whereIn('inquiry.status', $statusArray);
		} else if ($isChannelPartner != 0) {

			if ($request->status != 0) {

				$statusArray = isset($inquiryStatus[$request->status]['for_channel_partner_ids']) ? $inquiryStatus[$request->status]['for_channel_partner_ids'] : array(0);

				$query->whereIn('inquiry.status', $statusArray);
			}
		} else {

			if ($request->status != 0) {

				if ($request->status == 202) {

					$query->whereNotIn('inquiry.status', [9, 11, 14, 12, 10, 101, 102]);
					if ($isSalePerson == 1) {

						$query->Where('inquiry.is_for_manager', 1);
						$query->where(function ($query2) use ($childSalePersonsIds) {

							$query2->whereIn('inquiry.assigned_to', $childSalePersonsIds);
						});
					} else if ($isTaleSalesUser == 1) {

						$query->where(function ($query2) {
							$query2->where('inquiry.is_for_tele_sale', 1);
						});
					} else {
						$query->where(function ($query2) {
							$query2->where('inquiry.is_for_tele_sale', 1);
							$query2->orWhere('inquiry.is_for_manager', 1);
						});
					}
				} else {
					if ($isSalePerson == 1) {
						$statusArray = isset($inquiryStatus[$request->status]['for_sales_ids']) ? $inquiryStatus[$request->status]['for_sales_ids'] : array(0);
						$query->whereIn('inquiry.status', $statusArray);
					} else if ($isChannelPartner != 0) {

						$statusArray = isset($inquiryStatus[$request->status]['for_channel_partner_ids']) ? $inquiryStatus[$request->status]['for_channel_partner_ids'] : array(0);
						$query->whereIn('inquiry.status', $statusArray);
					} else if ($isAdminOrCompanyAdmin == 1) {

						$statusArray = isset($inquiryStatus[$request->status]['for_user_ids']) ? $inquiryStatus[$request->status]['for_user_ids'] : array(0);
						$query->whereIn('inquiry.status', $statusArray);
					} else if ($isThirdPartyUser == 1) {

						$statusArray = isset($inquiryStatus[$request->status]['for_third_party_ids']) ? $inquiryStatus[$request->status]['for_third_party_ids'] : array(0);
						$query->whereIn('inquiry.status', $statusArray);
					} else if ($isTaleSalesUser == 1) {

						$statusArray = isset($inquiryStatus[$request->status]['for_tele_sales_ids']) ? $inquiryStatus[$request->status]['for_tele_sales_ids'] : array(0);

						$query->whereIn('inquiry.status', $statusArray);
					}
				}
			}
		}
		$query->limit($request->length);
		$query->offset($request->start);
		$query->orderByRaw($sortColumns[$request['order'][0]['column']] . " " . $request['order'][0]['dir']);

		$i = $request['inquiry_filter_search_type'];

		if ($i == 14) {
			$query->Where('source_type', 'user-201');
		}

		if ($i == 15) {
			$query->Where('source_type', 'user-202');
		}
		if ($i == 16) {
			$query->Where('source_type', 'user-301');
		}
		if ($i == 17) {
			$query->Where('source_type', 'user-302');
		}
		if ($i == 18) {
			$query->Where('architect', 0);
		}
		if ($i == 19) {
			$query->Where('electrician', 0);
		}

		$isFilterApply = 0;

		if ($searchValue != "") {
			$isFilterApply = 1;

			if ($i == 12) {

				$query->WhereIn('source_type', $sourceTypeFilter);
			} else if ($i == 13) {

				$query->where(function ($query) use ($searchValue, $sourceUsers) {

					if (count($sourceUsers) > 0) {
						$query->WhereIn('inquiry.source_type_value', $sourceUsers);
						$query->orWhereRaw('inquiry.source_type_value like ?', [$searchValue]);
						$query->orWhereRaw('inquiry.source_type_value like ? ', ["%" . $searchValue . "%"]);
					} else {
						$query->WhereRaw('inquiry.source_type_value like ?', [$searchValue]);
						$query->orWhereRaw('inquiry.source_type_value like ? ', ["%" . $searchValue . "%"]);
					}
				});
			} else {

				if ($i == 0) {

					$query->where(function ($query) use ($searchValue, $searchColumns, $i, $sourceTypeFilter, $sourceUsers) {

						foreach ($searchColumns as $keyS => $valueS) {

							if ($keyS == 0) {
								continue;
							}

							if ($keyS == 12) {
								$query->orWhereIn('source_type', $sourceTypeFilter);
								continue;
							}

							if ($keyS == 13) {
								$query->orWhere(function ($query) use ($searchValue, $sourceUsers) {

									if (count($sourceUsers) > 0) {
										$query->WhereIn('inquiry.source_type_value', $sourceUsers);
										$query->orWhereRaw('inquiry.source_type_value like ?', [$searchValue]);
										$query->orWhereRaw('inquiry.source_type_value like ? ', ["%" . $searchValue . "%"]);
									} else {
										$query->WhereRaw('inquiry.source_type_value like ?', [$searchValue]);
										$query->orWhereRaw('inquiry.source_type_value like ? ', ["%" . $searchValue . "%"]);
									}
								});
								continue;
							}

							if ($keyS == 1) {

								$query->WhereRaw($searchColumns[$keyS] . ' like ?', [$searchValue]);
							}

							$query->orWhereRaw($searchColumns[$keyS] . ' like ? ', ["%" . $searchValue . "%"]);
						}
					});
				} else {

					$query->where(function ($query) use ($searchValue, $searchColumns, $i) {

						$query->WhereRaw($searchColumns[$i] . ' like ?', [$searchValue]);
						$query->orWhereRaw($searchColumns[$i] . ' like ? ', ["%" . $searchValue . "%"]);
					});
				}
			}
		}

		/// START ADVANCE FILTER
		if ($hasAdvanceFilter == 1) {

			$query->where(function ($query2) use ($advanceFilterType, $advanceFilterText, $advanceFilterKey, $advanceFilterTextAdditional) {

				foreach ($advanceFilterType as $keyNameAdvance => $keyAdvance) {

					if ($keyAdvance == 10) {
						$query2->Where('source_type', 'user-201');
					} else if ($keyAdvance == 11) {
						$query2->Where('source_type', 'user-202');
					} elseif ($keyAdvance == 15) {
						$query2->Where('source_type', 'user-301');
					} else if ($keyAdvance == 16) {
						$query2->Where('source_type', 'user-302');
					} else if ($keyAdvance == 13) {

						$query2->WhereIn('source_type', $advanceFilterText[$keyNameAdvance]);
						continue;
					} else if ($keyAdvance == 14) {
						if (count($advanceFilterText[$keyNameAdvance]) > 0) {
							$query2->WhereIn('inquiry.source_type_value', $advanceFilterText[$keyNameAdvance]);
							$query2->orWhereRaw('inquiry.source_type_value like ?', [$advanceFilterTextAdditional[$keyNameAdvance]]);
							$query2->orWhereRaw('inquiry.source_type_value like ? ', ["%" . $advanceFilterTextAdditional[$keyNameAdvance] . "%"]);
						} else {
							$query2->WhereRaw('inquiry.source_type_value like ?', [$advanceFilterTextAdditional[$keyNameAdvance]]);
							$query2->orWhereRaw('inquiry.source_type_value like ? ', ["%" . $advanceFilterTextAdditional[$keyNameAdvance] . "%"]);
						}
						continue;
					}

					$query2->where(function ($query3) use ($advanceFilterType, $advanceFilterText, $advanceFilterKey, $keyNameAdvance, $keyAdvance) {

						//$query3->WhereRaw($advanceFilterKey[$keyAdvance] . ' = ?', [$advanceFilterText[$keyNameAdvance]]);

						if (isset($advanceFilterText[$keyNameAdvance]) && $advanceFilterText[$keyNameAdvance] != "") {

							$query3->WhereRaw($advanceFilterKey[$keyAdvance] . ' like ?', [$advanceFilterText[$keyNameAdvance]]);
							$query3->orWhereRaw($advanceFilterKey[$keyAdvance] . ' like ? ', ["%" . $advanceFilterText[$keyNameAdvance] . "%"]);
						}
					});
				}
			});
		}

		/// END ADVANCE FILTER
		if (isAdminOrCompanyAdmin() == 0) {
			$query->whereNotIn('inquiry.assigned_to', getInquiryTransferToLeadUserList());
		}
		$data = $query->get();

		$data = json_decode(json_encode($data), true);

		// if ($isFilterApply == 1) {
		// 	$recordsFiltered = count($data);
		// }

		foreach ($data as $key => $value) {

			$valueAnserDateTime = convertDateTime($value['answer_date_time']);
			$valueStageOfSiteDateTime = convertDateTime($value['stage_of_site_date_time']);

			$createdSourceIcon = '<a href="javascript: void(0);" class="createdicon" ><i class="bx bx-globe"></i></a>';

			if ($value['is_from_mobile'] == 1) {
				$createdSourceIcon = '<a href="javascript: void(0);" class="createdicon" ><i class="bx bx-mobile"></i></a>';
			}

			$sureNotSureButton = '<i data-bs-toggle="tooltip" title="" class="bx bx-disc bx-sm extrasmallfont" data-bs-original-title="Status" aria-label="Status"></i>';
			if ($request['status'] == 8) {
				if ($value['is_predication_sure'] == 0) {
					//$sureNotSureButton = '<lable class="btn btn-sm  bg-danger btn-edit-detail">NOT SURE</lable> <button type="button" class="btn btn-info waves-effect waves-light btn-sm m-1 btn-edit-detail" onclick="moveToSure(' . $value['id'] . ',1)">DO-SURE</button>';
					$sureNotSureButton = '<i data-bs-toggle="tooltip" title="" class="bx bx-disc bx-sm extrasmallfont" data-bs-original-title="Status" aria-label="Status" onclick="moveToSure(' . $value['id'] . ',1)" ></i>';
				} else if ($value['is_predication_sure'] == 1) {
					//$sureNotSureButton = '<lable class="btn btn-sm  bg-success btn-edit-detail">SURE</lable> <button type="button" class="btn btn-info waves-effect waves-light btn-sm m-1 btn-edit-detail" onclick="moveToSure(' . $value['id'] . ',0)">DO-NOT SURE</button>';
					$sureNotSureButton = '<i data-bs-toggle="tooltip" title="" class="bx bx-disc  btn-success bx-disc-filled bx-sm extrasmallfont" data-bs-original-title="Status" aria-label="Status" onclick="moveToSure(' . $value['id'] . ',0)" ></i>';
				}
			}

			$buttonForTeleSaleAndManager = "";
			if ($value['is_for_tele_sale'] == 0) {
				$buttonT = '<button type="button" class="btn btn-t btn-outline-secondary waves-effect waves-light btn-sm m-1 " onclick="moveToTM(' . $value['id'] . ',\'T\')">T</button>';
			} else {
				$buttonT = '<button type="button" class="btn btn-t btn-success waves-effect waves-light btn-sm m-1 " onclick="removeTOTM(' . $value['id'] . ',\'T\')">T</button>';
			}

			if ($value['is_for_manager'] == 0) {
				$buttonM = '<button type="button" class="btn btn-m btn-outline-secondary waves-effect waves-light btn-sm m-1 " onclick="moveToTM(' . $value['id'] . ',\'M\')">M</button>';
			} else {
				$buttonM = '<button type="button" class="btn btn-m btn-success waves-effect waves-light btn-sm m-1 " onclick="removeTOTM(' . $value['id'] . ',\'M\')">M</button>';
			}

			$buttonForTeleSaleAndManager = $buttonT . "" . $buttonM;

			//$claimed24HoursIn = 0;
			$noOfclosing = 0;
			$closingHistory = array();
			if ($value['closing_history'] != "") {

				$closingHistory = json_decode($value['closing_history'], true);

				$noOfclosing = count($closingHistory);
			}

			// if ($isAdminOrCompanyAdmin == 1) {
			// 	if ($value['claimed_date_time'] == null) {
			// 		$claimed24HoursIn = 1;
			// 	} else {
			// 		$timeFirst = strtotime($value['claimed_date_time']);
			// 		$timeSecond = strtotime(date('Y-m-d H:i:s'));
			// 		$differenceInSeconds = $timeSecond - $timeFirst;
			// 		if ($differenceInSeconds < 86400) {
			// 			$claimed24HoursIn = 1;
			// 		}
			// 	}
			// }

			$isViewMode = 0;
			$viewModeAttribue = "";

			if ($value['is_verified'] == 0) {
				if ($isChannelPartner != 0) {

					$isViewMode = 1;
					$viewModeAttribue = "disabled";
				}
			}

			$data[$key]['inquiry_id'] = $data[$key]['id'];
			$valueCreatedTime = convertDateTime($value['created_at']);
			$updateHighLightClass = "";
			$updateCount = "";

			if ($isArchitect == 0 && $isElectrician == 0) {

				$timeFirst = strtotime($value['last_update']);
				$timeSecond = strtotime(date('Y-m-d H:i:s'));
				$differenceInSeconds = $timeSecond - $timeFirst;

				if ($differenceInSeconds < 172800) {
					$updateHighLightClass = "hightlight-update";
				}

				if ($value['update_count'] == 0) {
					$updateCount = "";
				} else {
					$updateCount = $value['update_count'];
				}
			}

			if ($request->view_type == 0) {

				$data[$key]['first_name'] = '<p class="border-box font-size-14 mb-0 text-dark d-flex justify-content-between align-items-center"><span><span class="lable-inquiry-id" id="inquiry-id-' . $data[$key]['id'] . '" >#' . $data[$key]['id'] . '</span><span id="inquiry-name-' . $data[$key]['id'] . '" data-bs-toggle="tooltip" title="' . $value['first_name'] . " " . $value['last_name'] . '" > ' . displayStringLenth($value['first_name'] . " " . $value['last_name'], 25) . ' </span>' . $buttonForTeleSaleAndManager . '</span>';

				if ($isArchitect == 0 && $isElectrician == 0) {

					if ($isViewMode == 0) {

						$data[$key]['first_name'] .= '<button type="button" class="btn  position-relative btn-detail ' . $updateHighLightClass . '" onclick="callDetail(' . $data[$key]['inquiry_id'] . ')"  >
                                                            <i class="fas fa-comments inquiry-comments-icon"></i>';

						if ($updateCount != "") {
							$data[$key]['first_name'] .= '<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill inquiry-update-badge ">' . $updateCount . '<span class="visually-hidden">unread messages</span>
                                                            </span>';
						}

						$data[$key]['first_name'] .= '</button>';
					}
				}

				$data[$key]['first_name'] .= '</p>';
				$data[$key]['first_name'] .= '<p class="border-box font-size-14 mb-0 text-dark d-flex justify-content-between align-items-center" ><span class="lable-inquiry-phone"><i class="bx bx-phone bx-sm extrasmallfont" data-bs-toggle="tooltip" title="Mobile No." ></i> +91 ' . $data[$key]['phone_number'] . '</span>';

				if ($isArchitect == 0 && $isElectrician == 0) {

					if ($isViewMode == 0) {

						if ($value['status'] != 10) {
							$data[$key]['first_name'] .= '<button type="button" class="btn btn-info waves-effect waves-light btn-sm m-1 btn-edit-detail" onclick="editView(' . $data[$key]['inquiry_id'] . ')"> EDIT</button>';
						} else if ($value['status'] == 10 && $isAdminOrCompanyAdmin == 1) {
							$data[$key]['first_name'] .= '<button type="button" class="btn btn-info waves-effect waves-light btn-sm m-1 btn-edit-detail" onclick="editView(' . $data[$key]['inquiry_id'] . ')"> EDIT</button>';
						}
					}
				}

				$data[$key]['first_name'] .= '</p>';
				$data[$key]['first_name'] .= '<p class="border-box font-size-14 mb-0 text-dark"><i class="bx bx-map bx-sm extrasmallfont" data-bs-toggle="tooltip" title="Address" ></i><span data-bs-toggle="tooltip" title="' . $data[$key]['house_no'] . ' ' . $data[$key]['society_name'] . '" >
                     ' . displayStringLenth($data[$key]['house_no'] . ' ' . $data[$key]['society_name'], 30) . '</span><br><span data-bs-toggle="tooltip" title="' . $data[$key]['area'] . '" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . displayStringLenth($data[$key]['area'], 30) . '</span><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $data[$key]['pincode'] . ', ' . $data[$key]['city_list_name'] . '</p>
                ';

				if ($data[$key]['architect'] == 0) {
					$data[$key]['architect_name'] = "-";
					$data[$key]['architect_phone_number'] = "-";
				} else {

					$data[$key]['architect_name'] = $data[$key]['architect_first_name'] . " " . $data[$key]['architect_last_name'];

					$data[$key]['architect_phone_number'] = "+91 " . $data[$key]['architect_phone_number'];
				}

				if ($data[$key]['electrician'] == 0) {
					$data[$key]['electrician_name'] = "-";
					$data[$key]['electrician_phone_number'] = "-";
				} else {

					$data[$key]['electrician_name'] = $data[$key]['electrician_first_name'] . " " . $data[$key]['electrician_last_name'];

					$data[$key]['electrician_phone_number'] = "+91 " . $data[$key]['electrician_phone_number'];
				}

				// if ($data[$key]['electrician_name'] == "") {
				// 	$data[$key]['electrician_name'] = "-";

				// }
				// if ($data[$key]['electrician_phone_number'] == "") {
				// 	$data[$key]['electrician_phone_number'] = "-";

				// } else {
				// 	$data[$key]['electrician_phone_number'] = "+91 " . $data[$key]['electrician_phone_number'];
				// }

				// $sourceTypeName = isset($sourceType[$found_key]['another_name']) ? $sourceType[$found_key]['another_name'] : $sourceType[$found_key]['short_name'];

				$isChannelPartnerCreatedBy = isChannelPartner($value['created_by_type']);
				$createdBylableName = "";

				if ($isChannelPartnerCreatedBy != 0) {

					$channelPartnerCreatedBy = ChannelPartner::select('firm_name')->where('user_id', $value['created_by_user_id'])->first();

					if ($channelPartnerCreatedBy) {
						$createdBylableName = $channelPartnerCreatedBy->firm_name;
					}
				} else {

					$createdBylableName = $value['created_by_first_name'] . " " . $value['created_by_last_name'];
				}

				$data[$key]['created_by'] = "<p class='border-box mb-0'><i  data-bs-toggle='tooltip' title='Created By' class='bx bxs-user bx-sm extrasmallfont'></i> <span data-bs-toggle='tooltip' title='" . $createdBylableName . "' >" . displayStringLenth($createdBylableName, 25) . "</span><a data-bs-toggle='tooltip' href='javascript: void(0);' class='createdicon' title='Created Date & Time : " . $valueCreatedTime . "' ><i class='bx bx-calendar'></i></a>" . $createdSourceIcon . "</p>";

				$sourceTypePices = explode("-", $value['source_type']);

				if ($sourceTypePices[0] == "user") {

					$userType = $sourceTypePices[1];
					$sourceTypeName = $value['source_type_lable'];

					$sourceName = "";
					if (isChannelPartner($userType) != 0) {

						$ChannelPartner = ChannelPartner::select('firm_name')->where('user_id', $value['source_type_value'])->first();

						if ($ChannelPartner) {

							$sourceName = $ChannelPartner->firm_name;
						}
					} else {

						$User = User::select('first_name', 'last_name')->find($value['source_type_value']);

						if ($User) {

							$sourceName = $User->first_name . " " . $User->last_name;
						}
					}

					if ($sourceTypePices[1] == "201" || $sourceTypePices[1] == "202") {

						$sourceTypeName = "Architect";
					}

					if ($sourceTypePices[1] == "301" || $sourceTypePices[1] == "302") {

						$sourceTypeName = "Electrician";
					}
					if ($sourceTypePices[1] == "4") {

						$sourceTypeName = $value['source_type_lable'];
						$sourceName = $value['source_type_value'];
					}

					if ($isAdminOrCompanyAdmin == 1) {

						$data[$key]['created_by'] .= "<p class='border-box mb-0 '><i  data-bs-toggle='tooltip' title='Source Type' class='bx bx-shield-alt bx-sm extrasmallfont'></i> " . $sourceTypeName . "<br><i  data-bs-toggle='tooltip' title='Source' class='bx bxs-user bx-sm extrasmallfont'></i><span data-bs-toggle='tooltip' title='" . $sourceName . "' ><a onclick='inquiryLogs(" . $value['source_type_value'] . ")' href='javascript: void(0)' > " . displayStringLenth($sourceName, 25) . "</a></span></p>";
					} else {

						$data[$key]['created_by'] .= "<p class='border-box mb-0 '><i  data-bs-toggle='tooltip' title='Source Type' class='bx bx-shield-alt bx-sm extrasmallfont'></i> " . $sourceTypeName . "<br><i  data-bs-toggle='tooltip' title='Source' class='bx bxs-user bx-sm extrasmallfont'></i><span data-bs-toggle='tooltip' title='" . $sourceName . "' >" . displayStringLenth($sourceName, 25) . "</span></p>";
					}
				} else if ($sourceTypePices[0] == "exhibition") {

					$userType = isset($sourceTypePices[1]) ? $sourceTypePices[1] : '';
					$Exhibition = Exhibition::select('name')->find($value['source_type_value']);
					$ExhibitionName = "";
					if ($Exhibition) {
						$ExhibitionName = $Exhibition->name;
					}


					$sourceName = $value['source_type_lable'] . " - " . $ExhibitionName;
					$data[$key]['created_by'] .= "<p class='border-box mb-0 pdiv-" . $userType . "'><i  data-bs-toggle='tooltip' title='Source' class='bx bxs-user bx-sm extrasmallfont'></i> " . $sourceName . "</p>";
				} else if ($sourceTypePices[0] == "textrequired" || $sourceTypePices[0] == "textnotrequired") {

					$userType = $sourceTypePices[1];
					$sourceTypeName = $value['source_type_lable'];
					$sourceName = $value['source_type_value'];

					$data[$key]['created_by'] .= "<p class='border-box mb-0 pdiv-" . $userType . "'><i  data-bs-toggle='tooltip' title='Source Type' class='bx bx-shield-alt bx-sm extrasmallfont'></i> " . $sourceTypeName . "<br><i  data-bs-toggle='tooltip' title='Source' class='bx bxs-user bx-sm extrasmallfont'></i> <span data-bs-toggle='tooltip' title='" . $sourceName . "' >" . displayStringLenth($sourceName, 25) . "</span></p>";
				} else {

					$userType = isset($sourceTypePices[1]) ? $sourceTypePices[1] : '';
					$sourceName = $value['source_type_lable'];

					$data[$key]['created_by'] .= "<p class='border-box mb-0 pdiv-" . $userType . "'><i  data-bs-toggle='tooltip' title='Source' class='bx bxs-user bx-sm extrasmallfont'></i> " . $sourceName . "</p>";
				}

				$primeArchitectClass = "";
				$primeArchitectLable = "";
				if ($value['architect_type'] == 202) {
					$primeArchitectClass = "pdiv-202";
					$primeArchitectLable = ' <span class="badge rounded-pill bg-success">PRIME</span>';
				}
				$primeEleactricanClass = "";
				$primeElecricianLable = "";

				if ($value['electrician_type'] == 302) {
					$primeEleactricanClass = "pdiv-302";
					$primeElecricianLable = ' <span class="badge rounded-pill bg-success">PRIME</span>';
				}

				$data[$key]['created_by'] .= "<p class='border-box mb-0 " . $primeArchitectClass . "'><i  data-bs-toggle='tooltip' title='Architect Name' class='bx bxs-user bx-sm extrasmallfont'></i><span data-bs-toggle='tooltip'  title='" . $data[$key]['architect_name'] . "'   ><a onclick='inquiryLogs(" . $value['architect'] . ")' href='javascript:void(0)'> " . displayStringLenth($data[$key]['architect_name'], 25) . $primeArchitectLable . "</a></span> <br> <i class='bx bx-phone bx-sm extrasmallfont' data-bs-toggle='tooltip' title='Architect Mobile No.' ></i> " . $data[$key]['architect_phone_number'] . "</p>";

				if ($isArchitect == 0 && $isElectrician == 0) {

					$data[$key]['created_by'] .= "<p class='border-box mb-0 " . $primeEleactricanClass . "'><i  data-bs-toggle='tooltip' title='Electrician Name' class='bx bxs-user bx-sm extrasmallfont'></i> <span  data-bs-toggle='tooltip' title='" . $data[$key]['electrician_name'] . "'   ><a onclick='inquiryLogs(" . $value['electrician'] . ")' href='javascript:void(0)'> " . displayStringLenth($data[$key]['electrician_name'], 25) . $primeElecricianLable . "</></span> <br> <i class='bx bx-phone bx-sm extrasmallfont' data-bs-toggle='tooltip' title='Electrician Mobile No.' ></i> " . $data[$key]['electrician_phone_number'] . "</p>";
				}

				if ($isArchitect == 0 && $isElectrician == 0) {
					if ($InquiryQuestionsStageOfState) {

						$stageOfSite = $data[$key]['stage_of_site'];

						if ($isViewMode == 0) {

							$data[$key]['stage_of_site'] = "<select  class='select_stage_of_site' id='stage_of_site_" . $data[$key]['inquiry_id'] . "'  onchange='changeStageOfSite(" . $data[$key]['inquiry_id'] . ")' style='width:46%'  >";

							$data[$key]['stage_of_site'] .= "<option value='0'>- Select -</option>";
							foreach ($InquiryQuestionsStageOfState['options'] as $option) {

								if ($stageOfSite == $option['option']) {

									$data[$key]['stage_of_site'] .= "<option selected='selected' value='" . $option->id . "'>" . $option['option'] . " </option>";
								} else {

									$data[$key]['stage_of_site'] .= "<option value='" . $option->id . "'>" . $option['option'] . " </option>";
								}
							}
							$data[$key]['stage_of_site'] .= "<select>";

							$closing_date_time = "";
							$closingDateTimeClass = "";
							$ClosingExpiredClass = "";
							$currentDatetimeSTR = strtotime(date('Y-m-d H:i:s'));

							if ($data[$key]['closing_date_time'] != null) {

								$currentDatetime = date('Y-m-d H:i:s');

								$currentDatetime = date('Y-m-d H:i:s', strtotime($currentDatetime . " +5 hours"));
								$currentDatetime = date('Y-m-d H:i:s', strtotime($currentDatetime . " +30 minutes"));

								if ($currentDatetime > $data[$key]['closing_date_time']) {

									$closingDateTimeClass = "expired-closingdatetime";
								} else {
									$closingDateTimeClass = "closingdatetime";
								}

								$closing_date = date('d-m-Y', strtotime($data[$key]['closing_date_time']));
								$closing_time = date('h:i A', strtotime($data[$key]['closing_date_time']));

								if ($currentDatetimeSTR > strtotime($data[$key]['closing_date_time'])) {

									$ClosingExpiredClass = "closing-expired";
								}
							} else {
								$closing_date = "";
								$closing_time = "";
							}

							$closingBadgeUI = '';
							if ($noOfclosing > 1) {

								$closingDates = '<ul class=\'list-unstyled chat-list\'>';

								foreach ($closingHistory as $closingKey => $closingVal) {
									$closingDates .= '<li>' . date('d-m-Y', strtotime($closingVal['closing_date_time'])) . '</li>';
								}

								$closingDates .= '</ul>';

								$closingBadgeUI = '<span class="closing-badge" data-bs-toggle="tooltip" data-bs-html="true" title="' . $closingDates . '" style="float:right;margin-top: 1px;" >' . $noOfclosing . '</span>';
							} else {
								$closingBadgeUI = '&nbsp;&nbsp;&nbsp;&nbsp;';
							}

							// $data[$key]['stage_of_site'] .= "<div class='border-box mb-0 " . $closingDateTimeClass . "'><i  data-bs-toggle='tooltip' title='Closing ' class='bx bx-time-five bx-sm extrasmallfont'></i> Closing <br>";

							// $data[$key]['stage_of_site'] .= "<i  data-bs-toggle='tooltip' title='Closing date & time' class='bx bx-calendar bx-sm extrasmallfont' style='float:left'></i> ";

							$data[$key]['stage_of_site'] .= "<span class='input-group' id='inquiry_closing_date_time_" . $data[$key]['inquiry_id'] . "' style='width:27%;margin-top: 26px;float:right' > ";

							$data[$key]['stage_of_site'] .= "<input class='input-closing-date-time form-control " . $ClosingExpiredClass . "' type='text' onchange='changeClosingUpDateTime(" . $data[$key]['inquiry_id'] . ")' data-date-format='dd-mm-yyyy' data-date-container='#inquiry_closing_date_time_" . $data[$key]['inquiry_id'] . "' data-provide='datepicker' data-date-autoclose='true'   id='answer_closing_date_" . $data[$key]['inquiry_id'] . "'    placeholder='dd-mm-yyyy' value='" . $closing_date . "' " . $viewModeAttribue . "   >";

							$data[$key]['stage_of_site'] .= "</span>";
							$data[$key]['stage_of_site'] .= "<i  data-bs-toggle='tooltip' title='Closing date & time' class='bx bx-calendar bx-sm extrasmallfont' style='margin-left:1px;float:right'></i>" . $closingBadgeUI;

							$data[$key]['stage_of_site'] .= " <button style='margin-top: 3px;display: none;float:right' class='save_answer_closing_date_time btn btn-success btn-sm waves-effect waves-light' id='save_answer_closing_date_time_" . $data[$key]['inquiry_id'] . "'  >Save</button>";

							// $data[$key]['stage_of_site'] .= "</div>";

						} else {
							$data[$key]['stage_of_site'] = $stageOfSite;
						}
					}
				} else {

					if ($data[$key]['stage_of_site'] == "") {

						$data[$key]['stage_of_site'] .= "-";
					}
				}

				if ($data[$key]['quotation'] == "") {

					$data[$key]['quotation'] = "-";
				} else {

					$data[$key]['quotation'] = "<a class='btn btn-sm btn-success btn-quotation' target='_blank'  href='" . Config::get('app.url') . "/" . $data[$key]['quotation'] . "' data-bs-toggle='tooltip' title='Quotation' >Quotation</a>";
				}

				if ($data[$key]['quotation_amount'] == "") {

					$data[$key]['quotation_amount'] = "-";
				}

				$data[$key]['follow_up'] = "<p class='border-box mb-0'><i  data-bs-toggle='tooltip' title='Stage of site' class='bx bx-cube bx-sm extrasmallfont'></i><i  data-bs-toggle='tooltip' title='Stage Of Site Update Date & Time : " . $valueStageOfSiteDateTime . "' class='bx bx-calendar bx-sm extrasmallfont'></i> " . $data[$key]['stage_of_site'] . "</p>";

				if ($isArchitect == 0 && $isElectrician == 0) {

					$data[$key]['follow_up'] .= "<p class='border-box mb-0'>
					<span class='lable-inquiry-quotation'><i  data-bs-toggle='tooltip' title='Quotation' class='bx bx-receipt bx-sm extrasmallfont'></i> " . $data[$key]['quotation'] . "/ <i  data-bs-toggle='tooltip' title='Quotation Amount' class='bx bx bx-rupee  bx-sm extrasmallfont'></i>" . $data[$key]['quotation_amount'] . "</span>";

					if ($isViewMode == 0) {

						$data[$key]['follow_up'] .= '<button type="button" class="btn btn-info waves-effect waves-light btn-sm m-1 btn-edit-detail" onclick="editQuotation(' . $data[$key]['inquiry_id'] . ')"> EDIT</button>';
					}

					$data[$key]['follow_up'] .= "</p>";

					$follow_up_date_time = "";
					$folloupDateTimeClass = "has-no-followupdatetime";

					if ($data[$key]['follow_up_date_time'] != null) {

						$currentDatetime = date('Y-m-d H:i:s');
						if ($currentDatetime > $data[$key]['follow_up_date_time']) {

							if ($inquiryStatus[$value['status']]['highlight_deadend_followup'] == 1) {
								$folloupDateTimeClass = "expired-followupdatetime";
							} else {
								$folloupDateTimeClass = "followupdatetime";
							}
						} else {
							$folloupDateTimeClass = "followupdatetime";
						}
						$follow_up_date = date('d-m-Y', strtotime($data[$key]['follow_up_date_time']));
						$follow_up_time = date('h:i A', strtotime($data[$key]['follow_up_date_time']));

						// $follow_up_date_time = date('Y-m-d', strtotime($data[$key]['follow_up_date_time'])) . "T" . date('H:i', strtotime($data[$key]['follow_up_date_time']));

					} else {
						$follow_up_date = "";
						$follow_up_time = "";
					}

					$meetingSelected = "";
					$callSelected = "";

					if ($value['follow_up_type'] == "Call") {

						$callSelected = "selected";
					} else if ($value['follow_up_type'] == "Meeting") {
						$meetingSelected = "selected";
					}

					$data[$key]['follow_up'] .= "<div class='border-box mb-0 " . $folloupDateTimeClass . "'><i  data-bs-toggle='tooltip' title='Follow up Type' class='bx bx-book-open bx-sm extrasmallfont'></i> <select  id='follow_type_id_" . $value['id'] . "' class='follow_type_id' onchange='changeFollowupType(" . $value['id'] . ")' ><option value='Meeting' " . $meetingSelected . " >Meeting</option><option value='Call' " . $callSelected . " >Call</option></select> <br>";

					$data[$key]['follow_up'] .= "<i  data-bs-toggle='tooltip' title='Follow up date & time' class='bx bx-calendar bx-sm extrasmallfont' style='float:left'></i> ";

					$data[$key]['follow_up'] .= "<div class='input-group' id='inquiry_follow_up_date_time_" . $data[$key]['inquiry_id'] . "' style='width:80%;margin-left: 21px;'> ";

					$data[$key]['follow_up'] .= "<input class='input-followup-date-time form-control' type='text' onchange='changeFollowUpDateTime(" . $data[$key]['inquiry_id'] . ")' data-date-format='dd-mm-yyyy' data-date-container='#inquiry_follow_up_date_time_" . $data[$key]['inquiry_id'] . "' data-provide='datepicker' data-date-autoclose='true'   id='answer_follow_up_date_" . $data[$key]['inquiry_id'] . "'  value='" . $follow_up_date . "' placeholder='dd-mm-yyyy' " . $viewModeAttribue . "  >";

					$data[$key]['follow_up'] .= "<div style='width:50%;'><select onchange='changeFollowUpDateTime(" . $data[$key]['inquiry_id'] . ")' class='form-control input-followup-time select2-choices' id='answer_follow_up_time_" . $data[$key]['inquiry_id'] . "' name='inquiry_follow_up_time' " . $viewModeAttribue . " />";

					foreach ($timeSlot as $timeSlotObject) {

						if ($follow_up_time == $timeSlotObject) {

							$data[$key]['follow_up'] .= "<option selected	 value=" . $timeSlotObject . ">" . $timeSlotObject . "</option>";
						} else {
							$data[$key]['follow_up'] .= "<option value=" . $timeSlotObject . ">" . $timeSlotObject . "</option>";
						}
					}

					$data[$key]['follow_up'] .= "</select>";

					$data[$key]['follow_up'] .= "</div>";
					$data[$key]['follow_up'] .= "</div>";

					$data[$key]['follow_up'] .= " <button style='margin-top: 3px;display: none;' class='save_answer_follow_up_date_time btn btn-success btn-sm waves-effect waves-light' id='save_answer_follow_up_date_time_" . $data[$key]['inquiry_id'] . "'  >Save</button>";

					$data[$key]['follow_up'] .= "</div>";
				}

				//

				$is_visible_billupload = 0;
				if ($data[$key]['billing_invoice'] == "") {

					$data[$key]['billing_invoice'] = "-";
				} else {

					//$data[$key]['billing_invoice'] = "<a class='btn btn-sm btn-success btn-quotation'   href='" . Config::get('app.url') . "/" . $data[$key]['billing_invoice'] . "' data-bs-toggle='tooltip' title='Billing Invoice' >Billing Invoice</a>";
					$data[$key]['billing_invoice'] = "<a class='btn btn-sm btn-success btn-quotation' onclick='openBillingInvoiceModal(" . $value['id'] . ",\"" . $data[$key]['billing_invoice'] . "\")'  href='javascript:void(0)' data-bs-toggle='tooltip' title='Billing Invoice' >Billing Invoice</a>";
					$is_visible_billupload = 1;
				}

				if ($data[$key]['billing_amount'] == "") {

					$data[$key]['billing_amount'] = "-";
				}

				if ($isArchitect == 0 && $isElectrician == 0) {

					$inqueryStatusList = array();
					if ($isAdminOrCompanyAdmin == 1) {
						$inqueryStatusList = $inquiryStatus[$value['status']]['can_move_user'];
					} else if ($isChannelPartner != 0) {
						$inqueryStatusList = $inquiryStatus[$value['status']]['can_move_sales'];
					} else if ($isSalePerson == 1) {
						$inqueryStatusList = $inquiryStatus[$value['status']]['can_move_channel_partner'];
					} else if ($isThirdPartyUser == 1) {
						$inqueryStatusList = $inquiryStatus[$value['status']]['can_move_third_party'];
					} else if ($isTaleSalesUser == 1) {
						$inqueryStatusList = $inquiryStatus[$value['status']]['can_move_tele_sales'];
					}

					foreach ($inqueryStatusList as $keyIQS => $valIQS) {
						$inqueryStatusList[$keyIQS] = $inquiryStatus[$valIQS];
					}

					$data[$key]['status'] = '<p class="border-box mb-0"><i  data-bs-toggle="tooltip" title="Assigned" class="bx bxs-user bx-sm extrasmallfont"></i> <span data-bs-toggle="tooltip" title="' . $data[$key]['assigned_to_first_name'] . ' ' . $data[$key]['assigned_to_last_name'] . '" >' . displayStringLenth($data[$key]['assigned_to_first_name'] . ' ' . $data[$key]['assigned_to_last_name'], 25) . '</span>';

					if ($isViewMode == 0) {

						$data[$key]['status'] .= '<br><i  data-bs-toggle="tooltip" title="Change Assigned" class="bx bx bx-edit-alt bx-sm extrasmallfont"></i>


				<button type="button" class="m-1 btn btn-warning waves-effect waves-light btn-sm btn-change-assigned" onclick="changeAssingedTo(' . $data[$key]['inquiry_id'] . ')"> Change</button>';
					}

					$data[$key]['status'] .= '</p>

				<p class="border-box mb-0">' . $sureNotSureButton . '<i  data-bs-toggle="tooltip" title="Status Update Date & Time : ' . $valueAnserDateTime . '" class="bx bx-calendar bx-sm extrasmallfont"></i>';

					if ($isViewMode == 0) {

						$data[$key]['status'] .= '<select class="m-1 select-change-status inquiry-status-lable-color-' . $value['status'] . '"  id="inquiry_status_' . $data[$key]['inquiry_id'] . '" onchange="return changeStatus(' . $data[$key]['inquiry_id'] . ',' . $value['status'] . ')"  >';

						foreach ($inqueryStatusList as $keyIQS => $valIQS) {

							if ($keyIQS == 0) {
								$data[$key]['status'] .= '<option selected value="' . $valIQS['id'] . '" >' . $valIQS['name'] . "</option>";
							} else {
								$data[$key]['status'] .= '<option value="' . $valIQS['id'] . '" >' . $valIQS['name'] . "</option>";
							}
						}

						$data[$key]['status'] .= '</select>';
					} else {
						$data[$key]['status'] .= "<span class='requestedforveify'> Requested for verify </span>";
					}

					$data[$key]['status'] .= '</p>';

					$data[$key]['status'] .= "<p class='border-box mb-0'><i  data-bs-toggle='tooltip' title='Billing Invoice' class='bx bx-receipt bx-sm extrasmallfont'></i> " . $data[$key]['billing_invoice'] . "/ <i  data-bs-toggle='tooltip' title='Billing Amount' class='bx bx bx-rupee  bx-sm extrasmallfont'></i>" . $data[$key]['billing_amount'];

					$popupString = array();

					if ($value['material_sent_channel_partner'] != 0) {

						$MaterialSentChannelPartner = ChannelPartner::where('user_id', $value['material_sent_channel_partner'])->first();
						if ($MaterialSentChannelPartner) {

							$popupString[] = "Material Sent Channel Partner : " . $MaterialSentChannelPartner->firm_name;
						}
					}

					if ($value['status'] == 12 || $value['status'] == 14) {

						$ReasonForPointlapse = InquiryQuestionAnswer::where('inquiry_question_id', 1069)->where('inquiry_id', $value['id'])->first();

						if ($ReasonForPointlapse) {
							$ReasonForPointlapseOptions = explode(",", $ReasonForPointlapse->answer);
							$InquiryQuestionOption = InquiryQuestionOption::where('id', $ReasonForPointlapseOptions)->get();
							$InquiryQuestionOptionString = array();
							foreach ($InquiryQuestionOption as $keyOP => $valueOP) {

								$InquiryQuestionOptionString[] = $valueOP->option;
							}
							$InquiryQuestionOptionString = implode(",", $InquiryQuestionOptionString);

							$popupString[] = "Reason For Point Point Lapsed : " . $InquiryQuestionOptionString;
						}
					}
					$popupString = implode(" | ", $popupString);

					if ($isViewMode == 0) {

						if ($isAdminOrCompanyAdmin == 1) {

							$data[$key]['status'] .= '<button type="button" class="btn btn-info waves-effect waves-light btn-sm m-1 btn-edit-detail" onclick="editBillingInvoice(' . $data[$key]['inquiry_id'] . ')"> EDIT</button><i data-bs-toggle="tooltip" title="" class="bx bx-dots-vertical bx-sm extrasmallfont" data-bs-original-title="' . $popupString . '" aria-label="' . $popupString . '"></i>';
						} else {
							if ($value['status'] == 9) {

								if ($is_visible_billupload == 0) {
									if (($value['source_type'] == "user-202" && $value['source_type_value'] != "") || ($value['source_type_1'] == "user-202" && $value['source_type_value_1'] != "") || ($value['source_type_2'] == "user-202" && $value['source_type_value_2'] != "") || ($value['source_type_3'] == "user-202" && $value['source_type_value_3'] != "") || $value['source_type_4'] == "user-202" && $value['source_type_value_4'] != "") {

										$data[$key]['status'] .= '<button type="button" class="btn btn-info waves-effect waves-light btn-sm m-1 btn-edit-detail" onclick="editBillingInvoice(' . $data[$key]['inquiry_id'] . ')"> EDIT 1</button><i data-bs-toggle="tooltip" title="" class="bx bx-dots-vertical bx-sm extrasmallfont" data-bs-original-title="' . $popupString . '" aria-label="' . $popupString . '"></i>';
									} else if (($value['source_type'] == "user-302" && $value['source_type_value'] != "") || ($value['source_type_1'] == "user-302" && $value['source_type_value_1'] != "") || ($value['source_type_2'] == "user-302" && $value['source_type_value_2'] != "") || ($value['source_type_3'] == "user-302" && $value['source_type_value_3'] != "") || $value['source_type_4'] == "user-302" && $value['source_type_value_4'] != "") {

										$data[$key]['status'] .= '<button type="button" class="btn btn-info waves-effect waves-light btn-sm m-1 btn-edit-detail" onclick="editBillingInvoice(' . $data[$key]['inquiry_id'] . ')"> EDIT</button>';
									} else if (($value['architect'] != 0 && $value['architect_type'] == 202)) {

										$data[$key]['status'] .= '<button type="button" class="btn btn-info waves-effect waves-light btn-sm m-1 btn-edit-detail" onclick="editBillingInvoice(' . $data[$key]['inquiry_id'] . ')"> EDIT</button><i data-bs-toggle="tooltip" title="" class="bx bx-dots-vertical bx-sm extrasmallfont" data-bs-original-title="' . $popupString . '" aria-label="' . $popupString . '"></i>';
									} else if (($value['electrician'] != 0 && $value['electrician_type'] == 302)) {

										$data[$key]['status'] .= '<button type="button" class="btn btn-info waves-effect waves-light btn-sm m-1 btn-edit-detail" onclick="editBillingInvoice(' . $data[$key]['inquiry_id'] . ')"> EDIT</button><i data-bs-toggle="tooltip" title="" class="bx bx-dots-vertical bx-sm extrasmallfont" data-bs-original-title="' . $popupString . '" aria-label="' . $popupString . '"></i>';
									}
								}
							}
						}
					}

					$data[$key]['status'] .= "</p>";
				} else {

					$InquiryLableName = isset($inquiryStatus[$data[$key]['status']]['name']) ? $inquiryStatus[$data[$key]['status']]['name'] : '';

					$data[$key]['status'] = '<p class="border-box mb-0"><i  data-bs-toggle="tooltip" title="Status" class="bx bx-disc bx-sm extrasmallfont"></i><lable> ' . $InquiryLableName . '</lable></p>';
					$data[$key]['status'] .= "<p class='border-box mb-0'><i  data-bs-toggle='tooltip' title='Billing Invoice' class='bx bx-receipt bx-sm extrasmallfont'></i> " . $data[$key]['billing_invoice'] . "/ <i  data-bs-toggle='tooltip' title='Billing Amount' class='bx bx bx-rupee  bx-sm extrasmallfont'></i>" . $data[$key]['billing_amount'] . "</p>";
				}
			} else {

				$data[$key]['name'] = '<p class="border-box font-size-14 mb-0 text-dark d-flex justify-content-between align-items-center"><span><span class="lable-inquiry-id" id="inquiry-id-' . $data[$key]['id'] . '" >#' . $data[$key]['id'] . '</span><span id="inquiry-name-' . $data[$key]['id'] . '" data-bs-toggle="tooltip" title="' . $value['first_name'] . " " . $value['last_name'] . '" > ' . displayStringLenth($value['first_name'] . " " . $value['last_name'], 25) . ' </span>' . $buttonForTeleSaleAndManager . '</span>';

				if ($isArchitect == 0 && $isElectrician == 0) {

					if ($isViewMode == 0) {

						if ($value['status'] != 10) {

							$data[$key]['name'] .= '<button type="button" class="btn btn-info waves-effect waves-light btn-sm m-1 btn-edit-detail" onclick="editView(' . $data[$key]['inquiry_id'] . ')"> EDIT</button>';
						} else if ($value['status'] == 10 && $isAdminOrCompanyAdmin == 1) {
							$data[$key]['name'] .= '<button type="button" class="btn btn-info waves-effect waves-light btn-sm m-1 btn-edit-detail" onclick="editView(' . $data[$key]['inquiry_id'] . ')"> EDIT</button>';
						}
					}
				}

				$data[$key]['name'] .= '<button type="button" class="btn  position-relative btn-detail ' . $updateHighLightClass . '" onclick="callDetail(' . $data[$key]['inquiry_id'] . ')"  >
                                                            <i class="fas fa-comments inquiry-comments-icon"></i>';

				if ($updateCount != "") {
					$data[$key]['name'] .= '<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill inquiry-update-badge ">' . $updateCount . '<span class="visually-hidden">unread messages</span>

                                                            </span>';
				}
				$data[$key]['name'] .= '</button>';
				$data[$key]['name'] .= '</p>';

				$data[$key]['phone_number'] = '<p class="border-box font-size-14 mb-0 text-dark d-flex justify-content-between align-items-center" ><span class="lable-inquiry-phone"><i class="bx bx-phone bx-sm extrasmallfont" data-bs-toggle="tooltip" title="Mobile No." ></i> +91 ' . $data[$key]['phone_number'] . '</span></p>';

				$data[$key]['address'] = '<p class="border-box font-size-14 mb-0 text-dark"><i class="bx bx-map bx-sm extrasmallfont" data-bs-toggle="tooltip" title="Address" ></i><span data-bs-toggle="tooltip" title="' . $data[$key]['house_no'] . ' ' . $data[$key]['society_name'] . " " . $data[$key]['area'] . " " . $data[$key]['pincode'] . "," . $data[$key]['city_list_name'] . '" >
                     ' . displayStringLenth($data[$key]['house_no'] . ' ' . $data[$key]['society_name'] . " " . $data[$key]['area'] . " " . $data[$key]['pincode'] . "," . $data[$key]['city_list_name'], 30) . '</span>

                     </p>';

				$isChannelPartnerCreatedBy = isChannelPartner($value['created_by_type']);
				$createdBylableName = "";

				if ($isChannelPartnerCreatedBy != 0) {

					$channelPartnerCreatedBy = ChannelPartner::select('firm_name')->where('user_id', $value['created_by_user_id'])->first();

					if ($channelPartnerCreatedBy) {
						$createdBylableName = $channelPartnerCreatedBy->firm_name;
					}
				} else {

					$createdBylableName = $value['created_by_first_name'] . " " . $value['created_by_last_name'];
				}

				$data[$key]['created_by'] = "<p class='border-box mb-0'><i  data-bs-toggle='tooltip' title='Created By' class='bx bxs-user bx-sm extrasmallfont'></i> <span data-bs-toggle='tooltip' title='" . $createdBylableName . "' >" . displayStringLenth($createdBylableName, 25) . "</span><a data-bs-toggle='tooltip' href='javascript: void(0);' class='createdicon' title='Created Date & Time : " . $valueCreatedTime . "' ><i class='bx bx-calendar'></i></a>" . $createdSourceIcon . "</p>";

				$sourceTypePices = explode("-", $value['source_type']);

				if ($sourceTypePices[0] == "user") {

					$userType = $sourceTypePices[1];
					$sourceTypeName = $value['source_type_lable'];

					$sourceName = "";
					if (isChannelPartner($userType) != 0) {

						$ChannelPartner = ChannelPartner::select('firm_name')->where('user_id', $value['source_type_value'])->first();

						if ($ChannelPartner) {

							$sourceName = $ChannelPartner->firm_name;
						}
					} else {

						$User = User::select('first_name', 'last_name')->find($value['source_type_value']);

						if ($User) {

							$sourceName = $User->first_name . " " . $User->last_name;
						}
					}

					if ($sourceTypePices[1] == "201" || $sourceTypePices[1] == "202") {

						$sourceTypeName = "Architect";
					}
					if ($sourceTypePices[1] == "301" || $sourceTypePices[1] == "302") {

						$sourceTypeName = "Electrician";
					}
					if ($sourceTypePices[1] == "4") {

						$sourceTypeName = $value['source_type_lable'];
						$sourceName = $value['source_type_value'];
					}
					if ($isAdminOrCompanyAdmin == 1) {
						//
						$data[$key]['source'] = "<p class='border-box mb-0 '><i  data-bs-toggle='tooltip' title='Source Type' class='bx bx-shield-alt bx-sm extrasmallfont'></i> " . $sourceTypeName . "<i  data-bs-toggle='tooltip' title='Source' class='bx bxs-user bx-sm extrasmallfont'></i><span data-bs-toggle='tooltip' title='" . $sourceName . "' > <a onclick='inquiryLogs(" . $value['source_type_value'] . ")' href='javascript: void(0)'>" . displayStringLenth($sourceName, 25) . "</a></span></p>";
					} else {

						$data[$key]['source'] = "<p class='border-box mb-0 '><i  data-bs-toggle='tooltip' title='Source Type' class='bx bx-shield-alt bx-sm extrasmallfont'></i> " . $sourceTypeName . "<i  data-bs-toggle='tooltip' title='Source' class='bx bxs-user bx-sm extrasmallfont'></i><span data-bs-toggle='tooltip' title='" . $sourceName . "' > " . displayStringLenth($sourceName, 25) . "</span></p>";
					}
				} else if ($sourceTypePices[0] == "exhibition") {


					$userType = isset($sourceTypePices[1]) ? $sourceTypePices[1] : '';
					$sourceName = $value['source_type_lable'];
					$Exhibition = Exhibition::select('name')->find($value['source_type_value']);
					$ExhibitionName = "";
					if ($Exhibition) {
						$ExhibitionName = $Exhibition->name;
					}
					$sourceName = $value['source_type_lable'] . " - " . $ExhibitionName;

					$data[$key]['source'] = "<p class='border-box mb-0 pdiv-" . $userType . "'><i  data-bs-toggle='tooltip' title='Source' class='bx bxs-user bx-sm extrasmallfont'></i> " . $sourceName . "</p>";
				} else if ($sourceTypePices[0] == "textrequired" || $sourceTypePices[0] == "textnotrequired") {

					$userType = $sourceTypePices[1];
					$sourceTypeName = $value['source_type_lable'];
					$sourceName = $value['source_type_value'];

					$data[$key]['source'] = "<p class='border-box mb-0 pdiv-" . $userType . "'><i  data-bs-toggle='tooltip' title='Source Type' class='bx bx-shield-alt bx-sm extrasmallfont'></i> " . $sourceTypeName . "<i  data-bs-toggle='tooltip' title='Source' class='bx bxs-user bx-sm extrasmallfont'></i> <span data-bs-toggle='tooltip' title='" . $sourceName . "' >" . displayStringLenth($sourceName, 25) . "</span></p>";
				} else {

					$userType = isset($sourceTypePices[1]) ? $sourceTypePices[1] : '';
					$sourceName = $value['source_type_lable'];

					$data[$key]['source'] = "<p class='border-box mb-0 pdiv-" . $userType . "'><i  data-bs-toggle='tooltip' title='Source' class='bx bxs-user bx-sm extrasmallfont'></i> " . $sourceName . "</p>";
				}

				$primeArchitectClass = "";
				$primeArchitectLable = "";
				if ($value['architect_type'] == 202) {
					$primeArchitectClass = "pdiv-202";
					$primeArchitectLable = ' <span class="badge rounded-pill bg-success">PRIME</span>';
				}
				$primeEleactricanClass = "";
				$primeElecricianLable = "";

				if ($value['electrician_type'] == 302) {
					$primeEleactricanClass = "pdiv-302";
					$primeElecricianLable = ' <span class="badge rounded-pill bg-success">PRIME</span>';
				}

				if ($data[$key]['architect'] == 0) {
					$data[$key]['architect_name'] = "-";
					$data[$key]['architect_phone_number'] = "-";
				} else {

					$data[$key]['architect_name'] = $data[$key]['architect_first_name'] . " " . $data[$key]['architect_last_name'];
					$data[$key]['architect_phone_number'] = "+91 " . $data[$key]['architect_phone_number'];
				}

				$data[$key]['architect'] = "<p class='border-box mb-0 " . $primeArchitectClass . "'><i  data-bs-toggle='tooltip' title='Architect Name' class='bx bxs-user bx-sm extrasmallfont'></i><span data-bs-toggle='tooltip'  title='" . $data[$key]['architect_name'] . "' > <a href='javascript:void(0)' onclick='inquiryLogs(" . $value['architect'] . ")'  > " . displayStringLenth($data[$key]['architect_name'], 25) . $primeArchitectLable . "</a></span> <i class='bx bx-phone bx-sm extrasmallfont' data-bs-toggle='tooltip' title='Architect Mobile No.' ></i> " . $data[$key]['architect_phone_number'] . "</p>";

				if ($data[$key]['electrician'] == 0) {
					$data[$key]['electrician_name'] = "-";
					$data[$key]['electrician_phone_number'] = "-";
				} else {

					$data[$key]['electrician_name'] = $data[$key]['electrician_first_name'] . " " . $data[$key]['electrician_last_name'];

					$data[$key]['electrician_phone_number'] = "+91 " . $data[$key]['electrician_phone_number'];
				}

				$data[$key]['electrician'] = "";

				if ($isArchitect == 0 && $isElectrician == 0) {

					$data[$key]['electrician'] = "<p class='border-box mb-0 " . $primeEleactricanClass . "'><i  data-bs-toggle='tooltip' title='Electrician Name' class='bx bxs-user bx-sm extrasmallfont'></i> <span  data-bs-toggle='tooltip' title='" . $data[$key]['electrician_name'] . "'   ><a onclick='inquiryLogs(" . $value['electrician'] . ")' href='javascript:void(0)'> " . displayStringLenth($data[$key]['electrician_name'], 25) . $primeElecricianLable . "</a></span> <i class='bx bx-phone bx-sm extrasmallfont' data-bs-toggle='tooltip' title='Electrician Mobile No.' ></i> " . $data[$key]['electrician_phone_number'] . "</p>";
				}

				if ($isArchitect == 0 && $isElectrician == 0) {
					if ($InquiryQuestionsStageOfState) {

						$stageOfSite = $data[$key]['stage_of_site'];

						if ($isViewMode == 0) {

							$data[$key]['stage_of_site'] = "<i  data-bs-toggle='tooltip' title='Stage Of Site Update Date & Time : " . $valueStageOfSiteDateTime . "' class='bx bx-calendar bx-sm extrasmallfont'></i><select  class='select_stage_of_site_list_view' id='stage_of_site_" . $data[$key]['inquiry_id'] . "'  onchange='changeStageOfSite(" . $data[$key]['inquiry_id'] . ")' style=''  >";

							$data[$key]['stage_of_site'] .= "<option value='0'>- Select -</option>";
							foreach ($InquiryQuestionsStageOfState['options'] as $option) {

								if ($stageOfSite == $option['option']) {

									$data[$key]['stage_of_site'] .= "<option selected='selected' value='" . $option->id . "'>" . $option['option'] . " </option>";
								} else {

									$data[$key]['stage_of_site'] .= "<option value='" . $option->id . "'>" . $option['option'] . " </option>";
								}
							}
							$data[$key]['stage_of_site'] .= "<select>";

							// $data[$key]['stage_of_site'] .= "</div>";

						} else {
							$data[$key]['stage_of_site'] = $stageOfSite;
						}
					}
				} else {

					if ($data[$key]['stage_of_site'] == "") {

						$data[$key]['stage_of_site'] .= "-";
					}
				}

				$data[$key]['quotation'] = "";
				$data[$key]['closing'] = "";

				if ($isArchitect == 0 && $isElectrician == 0) {

					if ($isViewMode == 0) {

						$closing_date_time = "";
						$closingDateTimeClass = "";
						$ClosingExpiredClass = "";
						$currentDatetimeSTR = strtotime(date('Y-m-d H:i:s'));

						if ($data[$key]['closing_date_time'] != null) {

							$currentDatetime = date('Y-m-d H:i:s');

							$currentDatetime = date('Y-m-d H:i:s', strtotime($currentDatetime . " +5 hours"));
							$currentDatetime = date('Y-m-d H:i:s', strtotime($currentDatetime . " +30 minutes"));

							if ($currentDatetime > $data[$key]['closing_date_time']) {

								$closingDateTimeClass = "expired-closingdatetime";
							} else {
								$closingDateTimeClass = "closingdatetime";
							}

							$closing_date = date('d-m-Y', strtotime($data[$key]['closing_date_time']));
							$closing_time = date('h:i A', strtotime($data[$key]['closing_date_time']));

							if ($currentDatetimeSTR > strtotime($data[$key]['closing_date_time'])) {

								$ClosingExpiredClass = "closing-expired";
							}
						} else {
							$closing_date = "";
							$closing_time = "";
						}

						$closingBadgeUI = '';
						if ($noOfclosing > 1) {

							$closingDates = '<ul class=\'list-unstyled chat-list\'>';

							foreach ($closingHistory as $closingKey => $closingVal) {
								$closingDates .= '<li>' . date('d-m-Y', strtotime($closingVal['closing_date_time'])) . '</li>';
							}

							$closingDates .= '</ul>';

							$closingBadgeUI = '<span class="closing-badge" data-bs-toggle="tooltip" data-bs-html="true" title="' . $closingDates . '" >' . $noOfclosing . '</span>';
						} else {
							$closingBadgeUI = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
						}

						$data[$key]['closing'] = "						<span class='input-group' id='inquiry_closing_date_time_" . $data[$key]['inquiry_id'] . "' style='' >" . $closingBadgeUI . "
 <i  data-bs-toggle='tooltip' title='Closing date & time' class='bx bx-calendar bx-sm extrasmallfont ' style='margin-top: 5px;'></i>";

						$data[$key]['closing'] .= "<input class='input-closing-date-time form-control " . $ClosingExpiredClass . "' type='text' onchange='changeClosingUpDateTime(" . $data[$key]['inquiry_id'] . ")' data-date-format='dd-mm-yyyy' data-date-container='#inquiry_closing_date_time_" . $data[$key]['inquiry_id'] . "' data-provide='datepicker' data-date-autoclose='true'   id='answer_closing_date_" . $data[$key]['inquiry_id'] . "'   placeholder='dd-mm-yyyy' value='" . $closing_date . "' " . $viewModeAttribue . "  style='    height: 21px;
    margin-top: 3px;margin-right: 12px;'  >";

						$data[$key]['closing'] .= "</span>";

						$data[$key]['closing'] .= " <button style='margin-top: 3px;display: none;' class='save_answer_closing_date_time btn btn-success btn-sm waves-effect waves-light' id='save_answer_closing_date_time_" . $data[$key]['inquiry_id'] . "'  >Save</button>";
						$data[$key]['closing'] .= '';
					}
				}

				if ($data[$key]['quotation'] == "") {

					$data[$key]['quotation'] = "-";
				} else {

					$data[$key]['quotation'] = "<a class='btn btn-sm btn-success btn-quotation' target='_blank'  href='" . Config::get('app.url') . "/" . $data[$key]['quotation'] . "' data-bs-toggle='tooltip' title='Quotation' >Quotation</a>";
				}

				if ($data[$key]['quotation_amount'] == "") {

					$data[$key]['quotation_amount'] = "-";
				}

				$data[$key]['quotation'] = "";

				if ($isArchitect == 0 && $isElectrician == 0) {

					$data[$key]['quotation'] = "<p class='border-box mb-0'>
				<span class='lable-inquiry-quotation'><i  data-bs-toggle='tooltip' title='Quotation' class='bx bx-receipt bx-sm extrasmallfont'></i> " . $data[$key]['quotation'] . "/ <i  data-bs-toggle='tooltip' title='Quotation Amount' class='bx bx bx-rupee  bx-sm extrasmallfont'></i>" . $data[$key]['quotation_amount'] . "</span>";

					if ($isViewMode == 0) {

						$data[$key]['quotation'] .= '<button type="button" class="btn btn-info waves-effect waves-light btn-sm m-1 btn-edit-detail" onclick="editQuotation(' . $data[$key]['inquiry_id'] . ')"> EDIT</button>';
					}

					$data[$key]['quotation'] .= "</p>";
				}

				$data[$key]['view_follow_up_type'] = "";
				$data[$key]['view_follow_up_date_time'] = "";

				if ($isArchitect == 0 && $isElectrician == 0) {

					$follow_up_date_time = "";
					$folloupDateTimeClass = "has-no-followupdatetime";

					if ($data[$key]['follow_up_date_time'] != null) {

						$currentDatetime = date('Y-m-d H:i:s');
						if ($currentDatetime > $data[$key]['follow_up_date_time']) {

							if ($inquiryStatus[$value['status']]['highlight_deadend_followup'] == 1) {
								$folloupDateTimeClass = "expired-followupdatetime";
							} else {
								$folloupDateTimeClass = "followupdatetime";
							}
						} else {
							$folloupDateTimeClass = "followupdatetime";
						}
						$follow_up_date = date('d-m-Y', strtotime($data[$key]['follow_up_date_time']));
						$follow_up_time = date('h:i A', strtotime($data[$key]['follow_up_date_time']));

						// $follow_up_date_time = date('Y-m-d', strtotime($data[$key]['follow_up_date_time'])) . "T" . date('H:i', strtotime($data[$key]['follow_up_date_time']));

					} else {
						$follow_up_date = "";
						$follow_up_time = "";
					}

					$meetingSelected = "";
					$callSelected = "";

					if ($value['follow_up_type'] == "Call") {

						$callSelected = "selected";
					} else if ($value['follow_up_type'] == "Meeting") {
						$meetingSelected = "selected";
					}

					$data[$key]['view_follow_up_type'] .= "<div class='border-box mb-0 " . $folloupDateTimeClass . "'><i  data-bs-toggle='tooltip' title='Follow up Type' class='bx bx-book-open bx-sm extrasmallfont'></i> <select id='follow_type_id_" . $value['id'] . "' class='follow_type_id' onchange='changeFollowupType(" . $value['id'] . ")'  ><option value='Meeting' " . $meetingSelected . " >Meeting</option><option value='Call' " . $callSelected . " >Call</option></select>  ";

					$data[$key]['view_follow_up_type'] .= " ";

					$data[$key]['view_follow_up_date_time'] = "<div class='input-group " . $folloupDateTimeClass . "' id='inquiry_follow_up_date_time_" . $data[$key]['inquiry_id'] . "' style='
    padding-top: 6px;
    padding-left: 4px;
    padding-right: 5px;
    border-radius: 5px;'> ";

					$data[$key]['view_follow_up_date_time'] .= "<i  data-bs-toggle='tooltip' title='Follow up date & time' class='bx bx-calendar bx-sm extrasmallfont' style='float:left'></i><input class='input-followup-date-time form-control' type='text' onchange='changeFollowUpDateTime(" . $data[$key]['inquiry_id'] . ")' data-date-format='dd-mm-yyyy' data-date-container='#inquiry_follow_up_date_time_" . $data[$key]['inquiry_id'] . "' data-provide='datepicker' data-date-autoclose='true'   id='answer_follow_up_date_" . $data[$key]['inquiry_id'] . "'  value='" . $follow_up_date . "' placeholder='dd-mm-yyyy' " . $viewModeAttribue . "  >";

					$data[$key]['view_follow_up_date_time'] .= "<div style='width:50%;'><select onchange='changeFollowUpDateTime(" . $data[$key]['inquiry_id'] . ")' class='form-control input-followup-time select2-choices' id='answer_follow_up_time_" . $data[$key]['inquiry_id'] . "' name='inquiry_follow_up_time' " . $viewModeAttribue . " />";

					foreach ($timeSlot as $timeSlotObject) {

						if ($follow_up_time == $timeSlotObject) {

							$data[$key]['view_follow_up_date_time'] .= "<option selected	 value=" . $timeSlotObject . ">" . $timeSlotObject . "</option>";
						} else {
							$data[$key]['view_follow_up_date_time'] .= "<option value=" . $timeSlotObject . ">" . $timeSlotObject . "</option>";
						}
					}

					$data[$key]['view_follow_up_date_time'] .= "</select>";
					$data[$key]['view_follow_up_date_time'] .= "</div>";
					$data[$key]['view_follow_up_date_time'] .= "</div>";

					$data[$key]['view_follow_up_date_time'] .= " <button style='margin-top: 3px;display: none;' class='save_answer_follow_up_date_time btn btn-success btn-sm waves-effect waves-light' id='save_answer_follow_up_date_time_" . $data[$key]['inquiry_id'] . "'  >Save</button>";

					$data[$key]['view_follow_up_date_time'] .= "</div>";
				}

				$data[$key]['billing'] = "";
				$data[$key]['assign'] = "";

				if ($data[$key]['billing_invoice'] == "") {

					$data[$key]['billing_invoice'] = "-";
				} else {

					//$data[$key]['billing_invoice'] = "<a class='btn btn-sm btn-success btn-quotation'   href='" . Config::get('app.url') . "/" . $data[$key]['billing_invoice'] . "' data-bs-toggle='tooltip' title='Billing Invoice' >Billing Invoice</a>";
					$data[$key]['billing_invoice'] = "<a class='btn btn-sm btn-success btn-quotation' onclick='openBillingInvoiceModal(" . $value['id'] . ",\"" . $data[$key]['billing_invoice'] . "\")'  href='javascript:void(0)' data-bs-toggle='tooltip' title='Billing Invoice' >Billing Invoice</a>";
				}

				if ($data[$key]['billing_amount'] == "") {

					$data[$key]['billing_amount'] = "-";
				}

				$data[$key]['billing'] = "<p class='border-box mb-0'><i  data-bs-toggle='tooltip' title='Billing Invoice' class='bx bx-receipt bx-sm extrasmallfont'></i> " . $data[$key]['billing_invoice'] . "/ <i  data-bs-toggle='tooltip' title='Billing Amount' class='bx bx bx-rupee  bx-sm extrasmallfont'></i>" . $data[$key]['billing_amount'];

				$popupString = array();

				if ($value['material_sent_channel_partner'] != 0) {

					$MaterialSentChannelPartner = ChannelPartner::where('user_id', $value['material_sent_channel_partner'])->first();
					if ($MaterialSentChannelPartner) {

						$popupString[] = "Material Sent Channel Partner : " . $MaterialSentChannelPartner->firm_name;
					}
				}

				if ($value['status'] == 12 || $value['status'] == 14) {

					$ReasonForPointlapse = InquiryQuestionAnswer::where('inquiry_question_id', 1069)->where('inquiry_id', $value['id'])->first();

					if ($ReasonForPointlapse) {
						$ReasonForPointlapseOptions = explode(",", $ReasonForPointlapse->answer);
						$InquiryQuestionOption = InquiryQuestionOption::where('id', $ReasonForPointlapseOptions)->get();
						$InquiryQuestionOptionString = array();
						foreach ($InquiryQuestionOption as $keyOP => $valueOP) {

							$InquiryQuestionOptionString[] = $valueOP->option;
						}
						$InquiryQuestionOptionString = implode(",", $InquiryQuestionOptionString);

						$popupString[] = "Reason For Point Point Lapsed : " . $InquiryQuestionOptionString;
					}
				}
				$popupString = implode(" | ", $popupString);

				if ($isViewMode == 0) {

					if ($isAdminOrCompanyAdmin == 1) {

						$data[$key]['billing'] .= '<button type="button" class="btn btn-info waves-effect waves-light btn-sm m-1 btn-edit-detail" onclick="editBillingInvoice(' . $data[$key]['inquiry_id'] . ')"> EDIT</button><i data-bs-toggle="tooltip" title="" class="bx bx-dots-vertical bx-sm extrasmallfont" data-bs-original-title="' . $popupString . '" aria-label="' . $popupString . '"></i>';
					} else {
						if ($value['status'] == 9) {

							if (($value['source_type'] == "user-202" && $value['source_type_value'] != "") || ($value['source_type_1'] == "user-202" && $value['source_type_value_1'] != "") || ($value['source_type_2'] == "user-202" && $value['source_type_value_2'] != "") || ($value['source_type_3'] == "user-202" && $value['source_type_value_3'] != "") || $value['source_type_4'] == "user-202" && $value['source_type_value_4'] != "") {

								$data[$key]['billing'] .= '<button type="button" class="btn btn-info waves-effect waves-light btn-sm m-1 btn-edit-detail" onclick="editBillingInvoice(' . $data[$key]['inquiry_id'] . ')"> EDIT</button><i data-bs-toggle="tooltip" title="" class="bx bx-dots-vertical bx-sm extrasmallfont" data-bs-original-title="' . $popupString . '" aria-label="' . $popupString . '"></i>';
							} else if (($value['source_type'] == "user-302" && $value['source_type_value'] != "") || ($value['source_type_1'] == "user-302" && $value['source_type_value_1'] != "") || ($value['source_type_2'] == "user-302" && $value['source_type_value_2'] != "") || ($value['source_type_3'] == "user-302" && $value['source_type_value_3'] != "") || $value['source_type_4'] == "user-302" && $value['source_type_value_4'] != "") {

								$data[$key]['billing'] .= '<button type="button" class="btn btn-info waves-effect waves-light btn-sm m-1 btn-edit-detail" onclick="editBillingInvoice(' . $data[$key]['inquiry_id'] . ')"> EDIT</button><i data-bs-toggle="tooltip" title="" class="bx bx-dots-vertical bx-sm extrasmallfont" data-bs-original-title="' . $popupString . '" aria-label="' . $popupString . '"></i>';
							} else if (($value['architect'] != 0 && $value['architect_type'] == 202)) {

								$data[$key]['billing'] .= '<button type="button" class="btn btn-info waves-effect waves-light btn-sm m-1 btn-edit-detail" onclick="editBillingInvoice(' . $data[$key]['inquiry_id'] . ')"> EDIT</button><i data-bs-toggle="tooltip" title="" class="bx bx-dots-vertical bx-sm extrasmallfont" data-bs-original-title="' . $popupString . '" aria-label="' . $popupString . '"></i>';
							} else if (($value['electrician'] != 0 && $value['electrician_type'] == 302)) {

								$data[$key]['billing'] .= '<button type="button" class="btn btn-info waves-effect waves-light btn-sm m-1 btn-edit-detail" onclick="editBillingInvoice(' . $data[$key]['inquiry_id'] . ')"> EDIT</button><i data-bs-toggle="tooltip" title="" class="bx bx-dots-vertical bx-sm extrasmallfont" data-bs-original-title="' . $popupString . '" aria-label="' . $popupString . '"></i>';
							}
						}
					}

					//$data[$key]['billing'] .= '<button type="button" class="btn btn-info waves-effect waves-light btn-sm m-1 btn-edit-detail" onclick="editBillingInvoice(' . $data[$key]['inquiry_id'] . ')"> EDIT</button>';
				}

				$data[$key]['billing'] .= "</p>";

				if ($isArchitect == 0 && $isElectrician == 0) {

					$inqueryStatusList = array();
					if ($isAdminOrCompanyAdmin == 1) {
						$inqueryStatusList = $inquiryStatus[$value['status']]['can_move_user'];
					} else if ($isChannelPartner != 0) {
						$inqueryStatusList = $inquiryStatus[$value['status']]['can_move_sales'];
					} else if ($isSalePerson == 1) {
						$inqueryStatusList = $inquiryStatus[$value['status']]['can_move_channel_partner'];
					} else if ($isThirdPartyUser == 1) {
						$inqueryStatusList = $inquiryStatus[$value['status']]['can_move_third_party'];
					} else if ($isTaleSalesUser == 1) {
						$inqueryStatusList = $inquiryStatus[$value['status']]['can_move_tele_sales'];
					}

					foreach ($inqueryStatusList as $keyIQS => $valIQS) {
						$inqueryStatusList[$keyIQS] = $inquiryStatus[$valIQS];
					}

					$data[$key]['assign'] = '<p class="border-box mb-0"><i  data-bs-toggle="tooltip" title="Assigned" class="bx bxs-user bx-sm extrasmallfont"></i> <span data-bs-toggle="tooltip" title="' . $data[$key]['assigned_to_first_name'] . ' ' . $data[$key]['assigned_to_last_name'] . '" >' . displayStringLenth($data[$key]['assigned_to_first_name'] . ' ' . $data[$key]['assigned_to_last_name'], 25) . '</span>';

					if ($isViewMode == 0) {

						$data[$key]['assign'] .= '


				<button type="button" class="m-1 btn btn-warning waves-effect waves-light btn-sm btn-change-assigned" onclick="changeAssingedTo(' . $data[$key]['inquiry_id'] . ')"> Change</button>';
					}

					$data[$key]['assign'] .= '</p>';

					$data[$key]['status'] = '<p class="border-box mb-0">' . $sureNotSureButton . '<i  data-bs-toggle="tooltip" title="Status Update Date & Time : ' . $valueAnserDateTime . '" class="bx bx-calendar bx-sm extrasmallfont"></i>';

					if ($isViewMode == 0) {

						$data[$key]['status'] .= '<select class="m-1 select-change-status inquiry-status-lable-color-' . $value['status'] . '"  id="inquiry_status_' . $data[$key]['inquiry_id'] . '" onchange="return changeStatus(' . $data[$key]['inquiry_id'] . ',' . $value['status'] . ')"  >';

						foreach ($inqueryStatusList as $keyIQS => $valIQS) {

							if ($keyIQS == 0) {
								$data[$key]['status'] .= '<option selected value="' . $valIQS['id'] . '" >' . $valIQS['name'] . "</option>";
							} else {
								$data[$key]['status'] .= '<option value="' . $valIQS['id'] . '" >' . $valIQS['name'] . "</option>";
							}
						}

						$data[$key]['status'] .= '</select>';
					} else {
						$data[$key]['status'] .= "<span class='requestedforveify'> Requested for verify </span>";
					}

					$data[$key]['status'] .= '</p>';
				} else {

					$InquiryLableName = isset($inquiryStatus[$data[$key]['status']]['name']) ? $inquiryStatus[$data[$key]['status']]['name'] : '';

					$data[$key]['status'] = '<p class="border-box mb-0">' . $sureNotSureButton . '<lable> ' . $InquiryLableName . '</lable></p>';
					$data[$key]['billing'] = "<p class='border-box mb-0'><i  data-bs-toggle='tooltip' title='Billing Invoice' class='bx bx-receipt bx-sm extrasmallfont'></i> " . $data[$key]['billing_invoice'] . "/ <i  data-bs-toggle='tooltip' title='Billing Amount' class='bx bx bx-rupee  bx-sm extrasmallfont'></i>" . $data[$key]['billing_amount'] . "</p>";
				}
			}
		}

		$jsonData = array(
			"draw" => intval($request['draw']),
			// for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
			"recordsTotal" => intval($recordsTotal),
			// total number of records
			"recordsFiltered" => intval($recordsFiltered),
			// total number of records after searching, if there is no searching then totalFiltered = totalData
			"data" => $data,
			// total data array
			"quotationAmount" => priceLable($quotationTotal),
		);
		return $jsonData;
	}

	function autoAssignCitySalesPerson($cityId)
	{

		$User = User::select('users.id');
		$User->leftJoin('sale_person', 'sale_person.user_id', '=', 'users.id');
		$User->where('users.status', 1);
		$User->where('users.id', '!=', 2);
		$User->orderByRaw('FIELD(sale_person.type,1,2,3,4,5,6,7)');
		$User->whereIn('users.type', array(2));
		$User->where('users.city_id', $cityId);
		$User = $User->first();
		if ($User) {
			return $User->id;
		} else {
			return 2;
		}
	}

	public function saveInquiry(Request $request)
	{
		$isArchitect = isArchitect();
		$isElectrician = isElectrician();
		$sourceTypes = getInquirySourceTypes();
		$isChannelPartner = isChannelPartner(Auth::user()->type);
		$isThirdPartyUser = isThirdPartyUser();
		$isTaleSalesUser = isTaleSalesUser();
		if ($isTaleSalesUser == 1) {
			$TaleSalesCities = TeleSalesCity(Auth::user()->id);
		}

		if ($request->new_inquiry_id == 0) {

			$rules = array();
			$rules['inquiry_first_name'] = 'required';
			$rules['inquiry_last_name'] = 'required';
			$rules['inquiry_phone_number'] = 'required|digits:10|regex:/^[1-9][0-9]*$/';
			$rules['inquiry_house_no'] = 'required';
			$rules['inquiry_society_name'] = 'required';
			$rules['inquiry_area'] = 'required';
			//$rules['inquiry_pincode'] = 'required';
			$rules['inquiry_city_id'] = 'required';
			$rules['pre_inquiry_questions_7'] = 'required';

			if ($isArchitect == 0 && $isElectrician == 0 && $isChannelPartner == 0 && $isThirdPartyUser == 0) {

				$rules['inquiry_source_type'] = 'required';
				$rules['inquiry_follow_up_type'] = 'required';
				$rules['inquiry_follow_up_date'] = 'required';
				$rules['inquiry_follow_up_time'] = 'required';
				$rules['inquiry_assigned_to'] = 'required';
			} else if ($isChannelPartner != 0) {
				$rules['inquiry_follow_up_type'] = 'required';
				$rules['inquiry_follow_up_date'] = 'required';
				$rules['inquiry_follow_up_time'] = 'required';
				$rules['inquiry_assigned_to'] = 'required';
			} else if ($isThirdPartyUser == 1) {
			}
		} else {

			$rules = array();
			$rules['inquiry_first_name'] = 'required';
			$rules['inquiry_last_name'] = 'required';
			$rules['inquiry_phone_number'] = 'required|digits:10|regex:/^[1-9][0-9]*$/';
			$rules['inquiry_house_no'] = 'required';
			$rules['inquiry_society_name'] = 'required';
			$rules['inquiry_area'] = 'required';
			//$rules['inquiry_pincode'] = 'required';
			$rules['inquiry_city_id'] = 'required';
			$rules['inquiry_follow_up_type'] = 'required';
			$rules['inquiry_follow_up_date'] = 'required';
			$rules['inquiry_follow_up_time'] = 'required';
			$rules['inquiry_assigned_to'] = 'required';
			$rules['inquiry_source_type'] = 'required';
		}

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

			$phone_number2 = isset($request->inquiry_phone_number2) ? $request->inquiry_phone_number2 : '';

			if ($request->new_inquiry_id == 0) {

				$isAlreadyInquiry = Inquiry::select('id')->where('phone_number', $request->inquiry_phone_number)->first();

				if ($isAlreadyInquiry) {

					$response = errorRes("Inquiry already registed with phone number, Please use another phone number");
					return response()->json($response)->header('Content-Type', 'application/json');
				}

				if ($isArchitect == 1) {

					$inquiry_follow_up_date_time = null;
					$source_type_lable = "Architect(Prime)";
					$source_type = "user-202";
					$source_type_value = Auth::user()->id;
					$inquiry_follow_up_type = "Call";
					$source_type_1 = "";
					$source_type_lable_1 = "";
					$source_type_value_1 = "";
					$source_type_2 = "";
					$source_type_lable_2 = "";
					$source_type_value_2 = "";
					$source_type_3 = "";
					$source_type_lable_3 = "";
					$source_type_value_3 = "";
					$source_type_4 = "";
					$source_type_lable_4 = "";
					$source_type_value_4 = "";

					$Architect = Architect::where('user_id', Auth::user()->id)->first();
					if ($Architect) {
						$assigned_to = $Architect->sale_person_id;
					}

					$architect = Auth::user()->id;
				} else if ($isElectrician == 1) {

					$inquiry_follow_up_date_time = null;
					$source_type_lable = "Electrician(Prime)";
					$source_type = "user-302";
					$source_type_value = Auth::user()->id;
					$inquiry_follow_up_type = "Call";

					$source_type_1 = "";
					$source_type_lable_1 = "";
					$source_type_value_1 = "";
					$source_type_2 = "";
					$source_type_lable_2 = "";
					$source_type_value_2 = "";
					$source_type_3 = "";
					$source_type_lable_3 = "";
					$source_type_value_3 = "";
					$source_type_4 = "";
					$source_type_lable_4 = "";
					$source_type_value_4 = "";

					$Electrician = Electrician::where('user_id', Auth::user()->id)->first();
					if ($Electrician) {
						$assigned_to = $Electrician->sale_person_id;
					}

					$electrician = Auth::user()->id;
				} else if ($isChannelPartner != 0) {

					$channelPartner = getChannelPartners();

					// $inquiry_follow_up_date_time = null;
					$source_type_lable = $channelPartner[Auth::user()->type]['short_name'];
					$source_type = "user-" . Auth::user()->type;
					$source_type_value = Auth::user()->id;
					$inquiry_follow_up_type = "Call";
					$architect = 0;
					$source_type_1 = "";
					$source_type_lable_1 = "";
					$source_type_value_1 = "";
					$source_type_2 = "";
					$source_type_lable_2 = "";
					$source_type_value_2 = "";
					$source_type_3 = "";
					$source_type_lable_3 = "";
					$source_type_value_3 = "";
					$source_type_4 = "";
					$source_type_lable_4 = "";
					$source_type_value_4 = "";

					$assigned_to = isset($request->inquiry_assigned_to) ? $request->inquiry_assigned_to : Auth::user()->id;

					$inquiry_follow_up_type = $request->inquiry_follow_up_type;
					$inquiry_follow_up_date_time = date('Y-m-d H:i:s', strtotime($request->inquiry_follow_up_date . " " . $request->inquiry_follow_up_time));
				} else if ($isThirdPartyUser == 1) {

					$inquiry_follow_up_date_time = null;
					$source_type_lable = "Third Party";
					$source_type = "user-8";
					$source_type_value = Auth::user()->id;
					$inquiry_follow_up_type = "Call";

					$source_type_1 = "";
					$source_type_lable_1 = "";
					$source_type_value_1 = "";
					$source_type_2 = "";
					$source_type_lable_2 = "";
					$source_type_value_2 = "";
					$source_type_3 = "";
					$source_type_lable_3 = "";
					$source_type_value_3 = "";
					$source_type_4 = "";
					$source_type_lable_4 = "";
					$source_type_value_4 = "";

					$assigned_to = $this->autoAssignCitySalesPerson($request->inquiry_city_id);
					$inquiry_follow_up_type = $request->inquiry_follow_up_type;
					$inquiry_follow_up_date_time = date('Y-m-d H:i:s', strtotime($request->inquiry_follow_up_date . " " . $request->inquiry_follow_up_time));
				} else {

					$inquiry_follow_up_type = $request->inquiry_follow_up_type;
					$inquiry_follow_up_date_time = date('Y-m-d H:i:s', strtotime($request->inquiry_follow_up_date . " " . $request->inquiry_follow_up_time));

					///MAIN SOURCE

					$source_type = $request->inquiry_source_type;
					$source_type_pieces = explode("-", $source_type);

					if ($source_type_pieces[0] == "fix" && $source_type_pieces[1] == 0) {

						$source_type_value = "";
						$source_type_lable = "";
						$source_type = "";
					} else {
						if ($source_type_pieces[0] == "user") {

							if (!isset($request->inquiry_source_user) || $request->inquiry_source_user == "") {
								$response = errorRes("Please select source");
								return response()->json($response)->header('Content-Type', 'application/json');
							}

							$source_type_value = $request->inquiry_source_user;
						} else if ($source_type_pieces[0] == "exhibition") {

							if (!isset($request->inquiry_source_exhibition) || $request->inquiry_source_exhibition == "") {
								$response = errorRes("Please select exhibition");
								return response()->json($response)->header('Content-Type', 'application/json');
							}

							$source_type_value = $request->inquiry_source_exhibition;
						} else if ($source_type_pieces[0] == "textrequired") {
							if (!isset($request->inquiry_source_text) || $request->inquiry_source_text == "") {
								$response = errorRes("Please enter source text");
								return response()->json($response)->header('Content-Type', 'application/json');
							}

							$source_type_value = $request->inquiry_source_text;
						} else if ($source_type_pieces[0] == "textnotrequired") {

							if (!isset($request->inquiry_source_text) || $request->inquiry_source_text == "") {
								$source_type_value = "";
							} else {
								$source_type_value = $request->inquiry_source_text;
							}
						} else {
							$source_type_value = "";
						}

						$source_type_lable = "";

						foreach ($sourceTypes as $key => $value) {

							if ($value['type'] == $source_type_pieces[0] && $value['id'] == $source_type_pieces[1]) {
								$source_type_lable = $value['lable'];
								break;
							}
						}
					}

					///END MAIN SOURCE

					///SUB SOURCE - 1

					$source_type_1 = $request->inquiry_source_type_1;
					$source_type_pieces_1 = explode("-", $source_type_1);
					if ($source_type_pieces_1[0] == "user") {

						if (!isset($request->inquiry_source_user_1) || $request->inquiry_source_user_1 == "") {
							$source_type_value_1 = "";
						} else {
							$source_type_value_1 = $request->inquiry_source_user_1;
						}
					} else if ($source_type_pieces_1[0] == "textrequired" || $source_type_pieces_1[0] == "textnotrequired") {
						if (!isset($request->inquiry_source_text) || $request->inquiry_source_text == "") {
							$source_type_value_1 = $request->inquiry_source_text_1;
						} else {
							$source_type_value_1 = "";
						}
					} else {
						$source_type_value_1 = "";
					}

					$source_type_lable_1 = "";

					foreach ($sourceTypes as $key => $value) {

						if ($value['type'] == $source_type_pieces_1[0] && $value['id'] == $source_type_pieces_1[1]) {
							$source_type_lable_1 = $value['lable'];
							break;
						}
					}

					///END SUB SOURCE - 1

					///SUB SOURCE - 2

					$source_type_2 = $request->inquiry_source_type_2;
					$source_type_pieces_2 = explode("-", $source_type_2);
					if ($source_type_pieces_2[0] == "user") {

						if (!isset($request->inquiry_source_user_2) || $request->inquiry_source_user_2 == "") {
							$source_type_value_2 = "";
						} else {
							$source_type_value_2 = $request->inquiry_source_user_2;
						}
					} else if ($source_type_pieces_2[0] == "textrequired" || $source_type_pieces_2[0] == "textnotrequired") {
						if (!isset($request->inquiry_source_text_2) || $request->inquiry_source_text_2 == "") {
							$source_type_value_2 = $request->inquiry_source_text_2;
						} else {
							$source_type_value_2 = "";
						}
						;
					} else {
						$source_type_value_2 = "";
					}

					$source_type_lable_2 = "";

					foreach ($sourceTypes as $key => $value) {

						if ($value['type'] == $source_type_pieces_2[0] && $value['id'] == $source_type_pieces_2[1]) {
							$source_type_lable_2 = $value['lable'];
							break;
						}
					}

					///END SUB SOURCE - 2

					///SUB SOURCE - 3

					$source_type_3 = $request->inquiry_source_type_3;
					$source_type_pieces_3 = explode("-", $source_type_3);
					if ($source_type_pieces_3[0] == "user") {

						if (!isset($request->inquiry_source_user_3) || $request->inquiry_source_user_3 == "") {
							$source_type_value_3 = "";
						} else {
							$source_type_value_3 = $request->inquiry_source_user_3;
						}
					} else if ($source_type_pieces_3[0] == "textrequired" || $source_type_pieces_3[0] == "textnotrequired") {
						if (!isset($request->inquiry_source_text_3) || $request->inquiry_source_text_3 == "") {
							$source_type_value_3 = $request->inquiry_source_text_3;
						} else {
							$source_type_value_3 = "";
						}
						;
					} else {
						$source_type_value_3 = "";
					}

					$source_type_lable_3 = "";

					foreach ($sourceTypes as $key => $value) {

						if ($value['type'] == $source_type_pieces_3[0] && $value['id'] == $source_type_pieces_3[1]) {
							$source_type_lable_3 = $value['lable'];
							break;
						}
					}

					///END SUB SOURCE - 3

					///SUB SOURCE - 4

					$source_type_4 = $request->inquiry_source_type_4;
					$source_type_pieces_4 = explode("-", $source_type_4);
					if ($source_type_pieces_3[0] == "user") {

						if (!isset($request->inquiry_source_user_4) || $request->inquiry_source_user_4 == "") {
							$source_type_value_4 = "";
						} else {
							$source_type_value_4 = $request->inquiry_source_user_4;
						}
					} else if ($source_type_pieces_4[0] == "textrequired" || $source_type_pieces_4[0] == "textnotrequired") {
						if (!isset($request->inquiry_source_text_4) || $request->inquiry_source_text_4 == "") {
							$source_type_value_4 = $request->inquiry_source_text_4;
						} else {
							$source_type_value_4 = "";
						}
						;
					} else {
						$source_type_value_4 = "";
					}

					$source_type_lable_4 = "";

					foreach ($sourceTypes as $key => $value) {

						if ($value['type'] == $source_type_pieces_4[0] && $value['id'] == $source_type_pieces_4[1]) {
							$source_type_lable_4 = $value['lable'];
							break;
						}
					}

					///END SUB SOURCE - 4

					$assigned_to = isset($request->inquiry_assigned_to) ? $request->inquiry_assigned_to : Auth::user()->id;

					$architect = isset($request->inquiry_architect) ? $request->inquiry_architect : 0;

					$electrician = isset($request->inquiry_electrician) ? $request->inquiry_electrician : 0;
				}

				$architect = isset($request->inquiry_architect) ? $request->inquiry_architect : 0;

				$electrician = isset($request->inquiry_electrician) ? $request->inquiry_electrician : 0;

				$stage_of_site = isset($request->pre_inquiry_questions_7) ? $request->pre_inquiry_questions_7 : '';
				$required_for_property = isset($request->pre_inquiry_questions_9) ? $request->pre_inquiry_questions_9 : '';
				$changes_of_closing_order = isset($request->pre_inquiry_questions_10) ? $request->pre_inquiry_questions_10 : '';

				$question_attachment_file_name = '';
				if ($request->hasFile('pre_inquiry_questions_8')) {

					$question_attachment = $request->file('pre_inquiry_questions_8');
					$extension = $question_attachment->getClientOriginalExtension();
					$question_attachment_file_name = time() . mt_rand(10000, 99999) . '.' . $extension;

					$destinationPath = public_path('/s/question-attachment');
					$question_attachment->move($destinationPath, $question_attachment_file_name);

					if (!File::exists('s/question-attachment/' . $question_attachment_file_name)) {
						$question_attachment_file_name = "";
					} else {
						$question_attachment_file_name = '/s/question-attachment/' . $question_attachment_file_name;
						$spaceUploadResponse = uploadFileOnSpaces(public_path($question_attachment_file_name), $question_attachment_file_name);
						if ($spaceUploadResponse != 1) {
							$question_attachment_file_name = "";
						} else {
							unlink(public_path($question_attachment_file_name));
						}
					}
				}

				if ($stage_of_site != "") {

					$Option = InquiryQuestionOption::find($stage_of_site);
					if ($Option) {
						$stage_of_site = $Option->option;
					} else {
						$stage_of_site = "";
					}
				}

				if ($required_for_property != "") {

					$Option = InquiryQuestionOption::find($required_for_property);
					if ($Option) {
						$required_for_property = $Option->option;
					} else {
						$required_for_property = "";
					}
				}

				if ($changes_of_closing_order != "") {

					$Option = InquiryQuestionOption::find($changes_of_closing_order);
					if ($Option) {
						$changes_of_closing_order = $Option->option;
					} else {
						$changes_of_closing_order = "";
					}
				}

				// Validation of architech
				if ($architect != 0) {

					$architechObject = User::select('id', 'first_name', 'last_name')->find($architect);
					if (!$architechObject) {
						$architect = 0;
					}
				}

				// Validation of electrician

				if ($electrician != 0) {

					$electricianObject = User::select('id', 'first_name', 'last_name')->find($electrician);
					if (!$electricianObject) {
						$electrician = 0;
					}
				}

				$Inquiry = new Inquiry();
				$Inquiry->answer_date_time = date('Y-m-d H:i:s');
				$Inquiry->stage_of_site_date_time = date('Y-m-d H:i:s');
				$Inquiry->status = 1;
				$Inquiry->user_id = Auth::user()->id;
				if ($isChannelPartner != 0) {
					$Inquiry->is_verified = 0;
				}

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
				$Inquiry->source_type_lable = $source_type_lable;
				$Inquiry->source_type = $source_type;
				$Inquiry->source_type_value = $source_type_value;
				$Inquiry->architect = $architect;
				$Inquiry->electrician = $electrician;
				// $Inquiry->architect_name = $architect_name;
				//$Inquiry->architect_phone_number = $architect_phone_number;
				//$Inquiry->electrician_name = $electrician_name;
				// $Inquiry->electrician_phone_number = $electrician_phone_number;
				$Inquiry->stage_of_site = $stage_of_site;
				$Inquiry->site_photos = $question_attachment_file_name;
				$Inquiry->required_for_property = $required_for_property;
				$Inquiry->changes_of_closing_order = $changes_of_closing_order;
				$Inquiry->follow_up_type = $inquiry_follow_up_type;
				$Inquiry->follow_up_date_time = $inquiry_follow_up_date_time;

				$Inquiry->source_type_lable_1 = $source_type_lable_1;
				$Inquiry->source_type_1 = $source_type_1;
				$Inquiry->source_type_value_1 = $source_type_value_1;

				$Inquiry->source_type_lable_2 = $source_type_lable_2;
				$Inquiry->source_type_2 = $source_type_2;
				$Inquiry->source_type_value_2 = $source_type_value_2;

				$Inquiry->source_type_lable_3 = $source_type_lable_3;
				$Inquiry->source_type_3 = $source_type_3;
				$Inquiry->source_type_value_3 = $source_type_value_3;

				$Inquiry->source_type_lable_4 = $source_type_lable_4;
				$Inquiry->source_type_4 = $source_type_4;
				$Inquiry->source_type_value_4 = $source_type_value_4;

				$Inquiry->save();

				if ($Inquiry) {

					if (($Inquiry->source_type == "user-201" || $Inquiry->source_type == "user-202") && $Inquiry->source_type_value != "") {
						architectInquiryCalculation($Inquiry->source_type_value);
					} else if (($Inquiry->source_type_1 == "user-201" || $Inquiry->source_type_1 == "user-202") && $Inquiry->source_type_value_1 != "") {
						architectInquiryCalculation($Inquiry->source_type_value_1);
					} else if (($Inquiry->source_type_2 == "user-201" || $Inquiry->source_type_2 == "user-202") && $Inquiry->source_type_value_2 != "") {
						architectInquiryCalculation($Inquiry->source_type_value_2);
					} else if (($Inquiry->source_type_3 == "user-201" || $Inquiry->source_type_3 == "user-202") && $Inquiry->source_type_value_3 != "") {
						architectInquiryCalculation($Inquiry->source_type_value_3);
					} else if (($Inquiry->source_type_4 == "user-201" || $Inquiry->source_type_4 == "user-202") && $Inquiry->source_type_value_4 != "") {
						architectInquiryCalculation($Inquiry->source_type_value_4);
					}

					if (($Inquiry->source_type == "user-301" || $Inquiry->source_type == "user-302") && $Inquiry->source_type_value != "") {
						elecricianInquiryCalculation($Inquiry->source_type_value);
					} else if (($Inquiry->source_type_1 == "user-301" || $Inquiry->source_type_1 == "user-302") && $Inquiry->source_type_value_1 != "") {
						elecricianInquiryCalculation($Inquiry->source_type_value_1);
					} else if (($Inquiry->source_type_2 == "user-301" || $Inquiry->source_type_2 == "user-302") && $Inquiry->source_type_value_2 != "") {
						elecricianInquiryCalculation($Inquiry->source_type_value_2);
					} else if (($Inquiry->source_type_3 == "user-301" || $Inquiry->source_type_3 == "user-302") && $Inquiry->source_type_value_3 != "") {
						elecricianInquiryCalculation($Inquiry->source_type_value_3);
					} else if (($Inquiry->source_type_4 == "user-301" || $Inquiry->source_type_4 == "user-302") && $Inquiry->source_type_value_4 != "") {
						elecricianInquiryCalculation($Inquiry->source_type_value_4);
					}

					// if ($Inquiry->source_type == "user-202") {

					// 	$Architect = Architect::where('user_id', $Inquiry->source_type_value)->first();
					// 	$Architect->total_inquiry = $Architect->total_inquiry + 1;
					// 	$Architect->save();

					// } else if ($Inquiry->source_type == "user-302") {

					// 	$Electrician = Electrician::where('user_id', $Inquiry->source_type_value)->first();
					// 	$Electrician->total_inquiry = $Electrician->total_inquiry + 1;
					// 	$Electrician->save();
					// }

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

					$mobileNotificationTitle = "New Inquiry Place";
					//$mobileNotificationMessage = "New Inquiry Place " . $Inquiry->id . " By " . Auth::user()->first_name . " " . Auth::user()->last_name . " This Inquiry Assign To " . $assignedToName;
					$mobileNotificationMessage = "New Inquiry Place " . $Inquiry->id . " " . $Inquiry->first_name . " " . $Inquiry->last_name . " By " . Auth::user()->first_name . " " . Auth::user()->last_name . " This Inquiry Assign To " . $assignedToName;

					$notificationUserids = getParentSalePersonsIds($Inquiry->assigned_to);
					$notificationUserids[] = $Inquiry->assigned_to;
					$UsersNotificationTokens = UsersNotificationTokens($notificationUserids);
					sendNotificationTOAndroid($mobileNotificationTitle, $mobileNotificationMessage, $UsersNotificationTokens, 'Inquiry', $Inquiry);

					if ($Inquiry->source_type == "user-202" || $Inquiry->source_type == "user-302" || $Inquiry->source_type == "user-201" || $Inquiry->source_type == "user-301") {

						if ($Inquiry->source_type_value != "") {

							$debugLog = array();
							$debugLog['name'] = "inquiry-add";
							$debugLog['for_user_id'] = $Inquiry->source_type_value;
							$debugLog['inquiry_id'] = $Inquiry->id;
							$debugLog['description'] = "inquiry #" . $Inquiry->id . "(" . $Inquiry->first_name . ' ' . $Inquiry->last_name . ") has been created ";
							$debugLog['type'] = '';
							saveCRMUserLog($debugLog);

							$newSourceName = "";

							if (isChannelPartner($source_type_pieces[1]) != 0) {

								$User = User::select('users.id', DB::raw("channel_partner.firm_name"));
								$User->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
								$User->where('users.id', $Inquiry->source_type_value);
								$User->limit(1);
								$User = $User->first();
								if ($User) {

									$newSourceName = $User->firm_name;
								}
							} else {

								$User = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
								$User->where('users.id', $Inquiry->source_type_value);
								$User->limit(1);
								$User = $User->first();
								if ($User) {

									$newSourceName = $User->full_name;
								}
							}

							$debugLog = array();
							$debugLog['inquiry_id'] = $Inquiry->id;
							$debugLog['name'] = "edit";
							$debugLog['description'] = "source added " . $newSourceName;
							saveInquiryLog($debugLog);
						}
					}

					if ($Inquiry->source_type_1 == "user-202" || $Inquiry->source_type_1 == "user-302" || $Inquiry->source_type_1 == "user-201" || $Inquiry->source_type_1 == "user-301") {

						if ($Inquiry->source_type_value_1 != "") {

							$debugLog = array();
							$debugLog['name'] = "inquiry-add";
							$debugLog['for_user_id'] = $Inquiry->source_type_value_1;
							$debugLog['inquiry_id'] = $Inquiry->id;
							$debugLog['description'] = "inquiry #" . $Inquiry->id . "(" . $Inquiry->first_name . ' ' . $Inquiry->last_name . ") has been created ";
							$debugLog['type'] = '';
							saveCRMUserLog($debugLog);

							$newSourceName = "";

							if (isChannelPartner($source_type_pieces_1[1]) != 0) {

								$User = User::select('users.id', DB::raw("channel_partner.firm_name"));
								$User->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
								$User->where('users.id', $Inquiry->source_type_value_1);
								$User->limit(1);
								$User = $User->first();
								if ($User) {

									$newSourceName = $User->firm_name;
								}
							} else {

								$User = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
								$User->where('users.id', $Inquiry->source_type_value_1);
								$User->limit(1);
								$User = $User->first();
								if ($User) {

									$newSourceName = $User->full_name;
								}
							}

							$debugLog = array();
							$debugLog['inquiry_id'] = $Inquiry->id;
							$debugLog['name'] = "edit";
							$debugLog['description'] = "source1 added " . $newSourceName;
							saveInquiryLog($debugLog);
						}
					}

					if ($Inquiry->source_type_2 == "user-202" || $Inquiry->source_type_2 == "user-302" || $Inquiry->source_type_2 == "user-201" || $Inquiry->source_type_2 == "user-301") {

						if ($Inquiry->source_type_value_2 != "") {

							$debugLog = array();
							$debugLog['name'] = "inquiry-add";
							$debugLog['for_user_id'] = $Inquiry->source_type_value_2;
							$debugLog['inquiry_id'] = $Inquiry->id;
							$debugLog['description'] = "inquiry #" . $Inquiry->id . "(" . $Inquiry->first_name . ' ' . $Inquiry->last_name . ") has been created ";
							$debugLog['type'] = '';
							saveCRMUserLog($debugLog);

							$newSourceName = "";

							if (isChannelPartner($source_type_pieces_2[1]) != 0) {

								$User = User::select('users.id', DB::raw("channel_partner.firm_name"));
								$User->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
								$User->where('users.id', $Inquiry->source_type_value);
								$User->limit(1);
								$User = $User->first();
								if ($User) {

									$newSourceName = $User->firm_name;
								}
							} else {

								$User = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
								$User->where('users.id', $Inquiry->source_type_value_2);
								$User->limit(1);
								$User = $User->first();
								if ($User) {

									$newSourceName = $User->full_name;
								}
							}

							$debugLog = array();
							$debugLog['inquiry_id'] = $Inquiry->id;
							$debugLog['name'] = "edit";
							$debugLog['description'] = "source2 added " . $newSourceName;
							saveInquiryLog($debugLog);
						}
					}

					if ($Inquiry->source_type_3 == "user-202" || $Inquiry->source_type_3 == "user-302" || $Inquiry->source_type_3 == "user-201" || $Inquiry->source_type_3 == "user-301") {

						if ($Inquiry->source_type_value_3 != "") {

							$debugLog = array();
							$debugLog['name'] = "inquiry-add";
							$debugLog['for_user_id'] = $Inquiry->source_type_value_3;
							$debugLog['inquiry_id'] = $Inquiry->id;
							$debugLog['description'] = "inquiry #" . $Inquiry->id . "(" . $Inquiry->first_name . ' ' . $Inquiry->last_name . ") has been created ";
							$debugLog['type'] = '';
							saveCRMUserLog($debugLog);

							$newSourceName = "";

							if (isChannelPartner($source_type_pieces_3[1]) != 0) {

								$User = User::select('users.id', DB::raw("channel_partner.firm_name"));
								$User->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
								$User->where('users.id', $Inquiry->source_type_value);
								$User->limit(1);
								$User = $User->first();
								if ($User) {

									$newSourceName = $User->firm_name;
								}
							} else {

								$User = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
								$User->where('users.id', $Inquiry->source_type_value_3);
								$User->limit(1);
								$User = $User->first();
								if ($User) {

									$newSourceName = $User->full_name;
								}
							}

							$debugLog = array();
							$debugLog['inquiry_id'] = $Inquiry->id;
							$debugLog['name'] = "edit";
							$debugLog['description'] = "source3 added " . $newSourceName;
							saveInquiryLog($debugLog);
						}
					}
					if ($Inquiry->source_type_4 == "user-202" || $Inquiry->source_type_4 == "user-302" || $Inquiry->source_type_4 == "user-201" || $Inquiry->source_type_4 == "user-301") {

						if ($Inquiry->source_type_value_4 != "") {

							$debugLog = array();
							$debugLog['name'] = "inquiry-add";
							$debugLog['for_user_id'] = $Inquiry->source_type_value_4;
							$debugLog['inquiry_id'] = $Inquiry->id;
							$debugLog['description'] = "inquiry #" . $Inquiry->id . "(" . $Inquiry->first_name . ' ' . $Inquiry->last_name . ") has been created ";
							$debugLog['type'] = '';
							saveCRMUserLog($debugLog);

							$newSourceName = "";

							if (isChannelPartner($source_type_pieces_4[1]) != 0) {

								$User = User::select('users.id', DB::raw("channel_partner.firm_name"));
								$User->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
								$User->where('users.id', $Inquiry->source_type_value);
								$User->limit(1);
								$User = $User->first();
								if ($User) {

									$newSourceName = $User->firm_name;
								}
							} else {

								$User = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
								$User->where('users.id', $Inquiry->source_type_value_4);
								$User->limit(1);
								$User = $User->first();
								if ($User) {

									$newSourceName = $User->full_name;
								}
							}

							$debugLog = array();
							$debugLog['inquiry_id'] = $Inquiry->id;
							$debugLog['name'] = "edit";
							$debugLog['description'] = "source4 added " . $newSourceName;
							saveInquiryLog($debugLog);
						}
					}

					if ($isThirdPartyUser == 1) {

						$assignedToUser = User::find($assigned_to);
						if ($assignedToUser) {

							$configrationForNotify = configrationForNotify();
							$params = array();
							$params['from_email'] = $configrationForNotify['from_email'];
							$params['from_name'] = $configrationForNotify['from_name'];
							$params['to_email'] = $assignedToUser->email;
							$params['to_name'] = $configrationForNotify['to_name'];
							$params['first_name'] = $assignedToUser->first_name;
							$params['last_name'] = $assignedToUser->last_name;
							$params['subject'] = "Inquiry from thirdparty user";
							$params['inquiry_id'] = $Inquiry->id;
							$params['inquiry_first_name'] = $Inquiry->first_name;
							$params['inquiry_last_name'] = $Inquiry->last_name;
							$params['inquiry_phone_number'] = $Inquiry->phone_number;

							if (Config::get('app.env') == "local") {

								$params['to_email'] = $configrationForNotify['test_email'];
							}

							Mail::send('emails.inquiry_from_thirdparty', ['params' => $params], function ($m) use ($params) {
								$m->from($params['from_email'], $params['from_name']);
								$m->to($params['to_email'], $params['to_name'])->subject($params['subject']);
							});
						}
					}
				}
			} else {

				$isAlreadyInquiry = Inquiry::select('id')->where('phone_number', $request->inquiry_phone_number)->where('id', '!=', $request->new_inquiry_id)->first();

				if ($isAlreadyInquiry) {

					$response = errorRes("Inquiry already registed with phone number, Please use another phone number");
					return response()->json($response)->header('Content-Type', 'application/json');
				}

				$Inquiry = Inquiry::find($request->new_inquiry_id);

				$isSalePerson = isSalePerson();
				$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
				$isChannelPartner = isChannelPartner(Auth::user()->type);

				if ($isSalePerson == 1) {
					$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
				}

				if ($Inquiry && ($isAdminOrCompanyAdmin == 1 || ($isThirdPartyUser == 1 && $Inquiry->source_type_value == Auth::user()->id) || ($isSalePerson == 1 && in_array($Inquiry->assigned_to, $childSalePersonsIds)) || ($isChannelPartner != 0 && $Inquiry->source_type_value == Auth::user()->id)) || ($isTaleSalesUser == 1 && in_array($Inquiry->city_id, $TaleSalesCities))) {

					$oldInquiry = $Inquiry;
					$oldInquiry = json_decode(json_encode($oldInquiry), true);

					$inquiry_follow_up_type = $request->inquiry_follow_up_type;
					$inquiry_follow_up_date_time = date('Y-m-d H:i:s', strtotime($request->inquiry_follow_up_date . " " . $request->inquiry_follow_up_time));

					$source_type_lable = "";
					$source_type = $request->inquiry_source_type;
					$source_type_value = "";
					$source_type_pieces = explode("-", $source_type);

					if ($isChannelPartner == 0) {

						if ($source_type_pieces[0] == "fix" && $source_type_pieces[1] == 0) {

							$source_type_value = "";
							$source_type_lable = "";
							$source_type = "";
						} else {

							if ($source_type_pieces[0] == "user") {

								if (!isset($request->inquiry_source_user) || $request->inquiry_source_user == "") {
									$response = errorRes("Please select source");
									return response()->json($response)->header('Content-Type', 'application/json');
								}

								$source_type_value = $request->inquiry_source_user;
							} else if ($source_type_pieces[0] == "exhibition") {

								if (!isset($request->inquiry_source_exhibition) || $request->inquiry_source_exhibition == "") {
									$response = errorRes("Please select source");
									return response()->json($response)->header('Content-Type', 'application/json');
								}

								$source_type_value = $request->inquiry_source_exhibition;
							} else if ($source_type_pieces[0] == "textrequired" || $source_type_pieces[0] == "textnotrequired") {
								if (!isset($request->inquiry_source_text) || $request->inquiry_source_text == "") {
									$response = errorRes("Please enter source text");
									return response()->json($response)->header('Content-Type', 'application/json');
								}

								$source_type_value = $request->inquiry_source_text;
							} else {
								$source_type_value = "";
							}

							$source_type_lable = "";

							$sourceTypes = getInquirySourceTypes();
							foreach ($sourceTypes as $key => $value) {

								if ($value['type'] == $source_type_pieces[0] && $value['id'] == $source_type_pieces[1]) {
									$source_type_lable = $value['lable'];
									break;
								}
							}
						}
					}

					///SUB SOURCE - 1

					$source_type_1 = "";
					$source_type_lable_1 = "";
					$source_type_value_1 = "";
					$source_type_2 = "";
					$source_type_lable_2 = "";
					$source_type_value_2 = "";
					$source_type_3 = "";
					$source_type_lable_3 = "";
					$source_type_value_3 = "";
					$source_type_4 = "";
					$source_type_lable_4 = "";
					$source_type_value_4 = "";

					if (isset($request->inquiry_source_type_1) && $request->inquiry_source_type_1 != "") {

						$source_type_1 = $request->inquiry_source_type_1;
						$source_type_pieces_1 = explode("-", $source_type_1);
						if ($source_type_pieces_1[0] == "fix" && $source_type_pieces_1[1] == 0) {

							$source_type_value_1 = "";
							$source_type_lable_1 = "";
							$source_type_1 = "";
						} else {
							if ($source_type_pieces_1[0] == "user") {

								if (!isset($request->inquiry_source_user_1) || $request->inquiry_source_user_1 == "") {
									$source_type_value_1 = "";
								} else {
									$source_type_value_1 = $request->inquiry_source_user_1;
								}
							} else if ($source_type_pieces_1[0] == "textrequired" || $source_type_pieces_1[0] == "textnotrequired") {

								if (isset($request->inquiry_source_text_1)) {
									$source_type_value_1 = $request->inquiry_source_text_1;
								} else {
									$source_type_value_1 = "";
								}
							} else {
								$source_type_value_1 = "";
							}

							$source_type_lable_1 = "";

							foreach ($sourceTypes as $key => $value) {

								if ($value['type'] == $source_type_pieces_1[0] && $value['id'] == $source_type_pieces_1[1]) {
									$source_type_lable_1 = $value['lable'];
									break;
								}
							}
						}
					}

					///END SUB SOURCE - 1

					///SUB SOURCE - 2
					if (isset($request->inquiry_source_type_2) && $request->inquiry_source_type_2 != "") {

						$source_type_2 = $request->inquiry_source_type_2;
						$source_type_pieces_2 = explode("-", $source_type_2);

						if ($source_type_pieces_2[0] == "fix" && $source_type_pieces_2[1] == 0) {

							$source_type_value_2 = "";
							$source_type_lable_2 = "";
							$source_type_2 = "";
						} else {
							if ($source_type_pieces_2[0] == "user") {

								if (!isset($request->inquiry_source_user_2) || $request->inquiry_source_user_2 == "") {
									$source_type_value_2 = "";
								} else {
									$source_type_value_2 = $request->inquiry_source_user_2;
								}
							} else if ($source_type_pieces_2[0] == "textrequired" || $source_type_pieces_2[0] == "textnotrequired") {
								if (isset($request->inquiry_source_text_2)) {
									$source_type_value_2 = $request->inquiry_source_text_1;
								} else {
									$source_type_value_2 = "";
								}
								;
							} else {
								$source_type_value_2 = "";
							}

							$source_type_lable_2 = "";

							foreach ($sourceTypes as $key => $value) {

								if ($value['type'] == $source_type_pieces_2[0] && $value['id'] == $source_type_pieces_2[1]) {
									$source_type_lable_2 = $value['lable'];
									break;
								}
							}
						}
					}
					///END SUB SOURCE - 2

					///SUB SOURCE - 3

					if (isset($request->inquiry_source_type_3) && $request->inquiry_source_type_3 != "") {

						$source_type_3 = $request->inquiry_source_type_3;
						$source_type_pieces_3 = explode("-", $source_type_3);

						if ($source_type_pieces_3[0] == "fix" && $source_type_pieces_3[1] == 0) {

							$source_type_value_3 = "";
							$source_type_lable_3 = "";
							$source_type_3 = "";
						} else {
							if ($source_type_pieces_3[0] == "user") {

								if (!isset($request->inquiry_source_user_3) || $request->inquiry_source_user_3 == "") {
									$source_type_value_3 = "";
								} else {
									$source_type_value_3 = $request->inquiry_source_user_3;
								}
							} else if ($source_type_pieces_3[0] == "textrequired" || $source_type_pieces_3[0] == "textnotrequired") {

								if (isset($request->inquiry_source_text_3)) {
									$source_type_value_3 = $request->inquiry_source_text_3;
								} else {
									$source_type_value_3 = "";
								}
							} else {
								$source_type_value_3 = "";
							}

							$source_type_lable_3 = "";

							foreach ($sourceTypes as $key => $value) {

								if ($value['type'] == $source_type_pieces_3[0] && $value['id'] == $source_type_pieces_3[1]) {
									$source_type_lable_3 = $value['lable'];
									break;
								}
							}
						}
					}

					///END SUB SOURCE - 3

					///SUB SOURCE - 4
					if (isset($request->inquiry_source_type_4) && $request->inquiry_source_type_4 != "") {

						$source_type_4 = $request->inquiry_source_type_4;
						$source_type_pieces_4 = explode("-", $source_type_4);

						if ($source_type_pieces_4[0] == "fix" && $source_type_pieces_4[1] == 0) {

							$source_type_value_4 = "";
							$source_type_lable_4 = "";
							$source_type_4 = "";
						} else {
							if ($source_type_pieces_4[0] == "user") {

								if (!isset($request->inquiry_source_user_4) || $request->inquiry_source_user_4 == "") {
									$source_type_value_4 = "";
								} else {
									$source_type_value_4 = $request->inquiry_source_user_4;
								}
							} else if ($source_type_pieces_4[0] == "textrequired" || $source_type_pieces_4[0] == "textnotrequired") {
								if (isset($request->inquiry_source_text_4)) {
									$source_type_value_4 = $request->inquiry_source_text_4;
								} else {
									$source_type_value_4 = "";
								}
							} else {
								$source_type_value_4 = "";
							}

							$source_type_lable_4 = "";

							foreach ($sourceTypes as $key => $value) {

								if ($value['type'] == $source_type_pieces_4[0] && $value['id'] == $source_type_pieces_4[1]) {
									$source_type_lable_4 = $value['lable'];
									break;
								}
							}
						}
					}

					///END SUB SOURCE - 4

					$assigned_to = isset($request->inquiry_assigned_to) ? $request->inquiry_assigned_to : Auth::user()->id;
					$architect = isset($request->inquiry_architect) ? $request->inquiry_architect : 0;
					$electrician = isset($request->inquiry_electrician) ? $request->inquiry_electrician : 0;

					// $architect_name = isset($request->inquiry_architect_name) ? $request->inquiry_architect_name : "";
					// $architect_phone_number = isset($request->inquiry_architect_phone_number) ? $request->inquiry_architect_phone_number : "";
					// $electrician_name = isset($request->inquiry_electrician_name) ? $request->inquiry_electrician_name : "";
					// $electrician_phone_number = isset($request->inquiry_electrician_phone_number) ? $request->inquiry_electrician_phone_number : "";

					$stage_of_site = isset($request->pre_inquiry_questions_7) ? $request->pre_inquiry_questions_7 : '';
					$required_for_property = isset($request->pre_inquiry_questions_9) ? $request->pre_inquiry_questions_9 : '';
					$changes_of_closing_order = isset($request->pre_inquiry_questions_10) ? $request->pre_inquiry_questions_10 : '';

					$question_attachment_file_name = '';
					if ($request->hasFile('pre_inquiry_questions_8')) {

						$question_attachment = $request->file('pre_inquiry_questions_8');
						$extension = $question_attachment->getClientOriginalExtension();
						$question_attachment_file_name = time() . mt_rand(10000, 99999) . '.' . $extension;

						$destinationPath = public_path('/s/question-attachment');
						$question_attachment->move($destinationPath, $question_attachment_file_name);

						if (!File::exists('s/question-attachment/' . $question_attachment_file_name)) {
							$question_attachment_file_name = "";
						} else {
							$question_attachment_file_name = '/s/question-attachment/' . $question_attachment_file_name;
							$spaceUploadResponse = uploadFileOnSpaces(public_path($question_attachment_file_name), $question_attachment_file_name);
							if ($spaceUploadResponse != 1) {
								$question_attachment_file_name = "";
							} else {
								unlink(public_path($question_attachment_file_name));
							}
						}
					}

					if ($stage_of_site != "") {

						$Option = InquiryQuestionOption::find($stage_of_site);
						if ($Option) {
							$stage_of_site = $Option->option;
						} else {
							$stage_of_site = "";
						}
					}

					if ($required_for_property != "") {

						$Option = InquiryQuestionOption::find($required_for_property);
						if ($Option) {
							$required_for_property = $Option->option;
						} else {
							$required_for_property = "";
						}
					}

					if ($changes_of_closing_order != "") {

						$Option = InquiryQuestionOption::find($changes_of_closing_order);
						if ($Option) {
							$changes_of_closing_order = $Option->option;
						} else {
							$changes_of_closing_order = "";
						}
					}

					// Validation of architech
					if ($architect != 0) {

						$architechObject = User::select('id', 'first_name', 'last_name')->find($architect);
						if (!$architechObject) {
							$architect = 0;
						}
					}

					// Validation of electrician

					if ($electrician != 0) {

						$electricianObject = User::select('id', 'first_name', 'last_name')->find($electrician);
						if (!$electricianObject) {
							$electrician = 0;
						}
					}

					if ($isChannelPartner == 0) {

						$Inquiry->source_type_lable = $source_type_lable;
						$Inquiry->source_type = $source_type;
						$Inquiry->source_type_value = $source_type_value;

						$Inquiry->source_type_lable_1 = $source_type_lable_1;
						$Inquiry->source_type_1 = $source_type_1;
						$Inquiry->source_type_value_1 = $source_type_value_1;

						$Inquiry->source_type_lable_2 = $source_type_lable_2;
						$Inquiry->source_type_2 = $source_type_2;
						$Inquiry->source_type_value_2 = $source_type_value_2;

						$Inquiry->source_type_lable_3 = $source_type_lable_3;
						$Inquiry->source_type_3 = $source_type_3;
						$Inquiry->source_type_value_3 = $source_type_value_3;

						$Inquiry->source_type_lable_4 = $source_type_lable_4;
						$Inquiry->source_type_4 = $source_type_4;
						$Inquiry->source_type_value_4 = $source_type_value_4;
					}

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
					$Inquiry->architect = $architect;
					$Inquiry->electrician = $electrician;
					// $Inquiry->architect_name = $architect_name;
					// $Inquiry->architect_phone_number = $architect_phone_number;
					// $Inquiry->electrician_name = $electrician_name;
					// $Inquiry->electrician_phone_number = $electrician_phone_number;
					$Inquiry->stage_of_site = $stage_of_site;
					if ($question_attachment_file_name != "") {
						$Inquiry->site_photos = $question_attachment_file_name;
					}
					$Inquiry->required_for_property = $required_for_property;
					$Inquiry->changes_of_closing_order = $changes_of_closing_order;
					$Inquiry->follow_up_type = $inquiry_follow_up_type;
					$Inquiry->follow_up_date_time = $inquiry_follow_up_date_time;
					$Inquiry->save();

					if (($Inquiry->source_type == "user-201" || $Inquiry->source_type == "user-202") && $Inquiry->source_type_value != "") {
						architectInquiryCalculation($Inquiry->source_type_value);
					} else if (($Inquiry->source_type_1 == "user-201" || $Inquiry->source_type_1 == "user-202") && $Inquiry->source_type_value_1 != "") {
						architectInquiryCalculation($Inquiry->source_type_value_1);
					} else if (($Inquiry->source_type_2 == "user-201" || $Inquiry->source_type_2 == "user-202") && $Inquiry->source_type_value_2 != "") {
						architectInquiryCalculation($Inquiry->source_type_value_2);
					} else if (($Inquiry->source_type_3 == "user-201" || $Inquiry->source_type_3 == "user-202") && $Inquiry->source_type_value_3 != "") {
						architectInquiryCalculation($Inquiry->source_type_value_3);
					} else if (($Inquiry->source_type_4 == "user-201" || $Inquiry->source_type_4 == "user-202") && $Inquiry->source_type_value_4 != "") {
						architectInquiryCalculation($Inquiry->source_type_value_4);
					}

					if (($Inquiry->source_type == "user-301" || $Inquiry->source_type == "user-302") && $Inquiry->source_type_value != "") {
						elecricianInquiryCalculation($Inquiry->source_type_value);
					} else if (($Inquiry->source_type_1 == "user-301" || $Inquiry->source_type_1 == "user-302") && $Inquiry->source_type_value_1 != "") {
						elecricianInquiryCalculation($Inquiry->source_type_value_1);
					} else if (($Inquiry->source_type_2 == "user-301" || $Inquiry->source_type_2 == "user-302") && $Inquiry->source_type_value_2 != "") {
						elecricianInquiryCalculation($Inquiry->source_type_value_2);
					} else if (($Inquiry->source_type_3 == "user-301" || $Inquiry->source_type_3 == "user-302") && $Inquiry->source_type_value_3 != "") {
						elecricianInquiryCalculation($Inquiry->source_type_value_3);
					} else if (($Inquiry->source_type_4 == "user-301" || $Inquiry->source_type_4 == "user-302") && $Inquiry->source_type_value_4 != "") {
						elecricianInquiryCalculation($Inquiry->source_type_value_4);
					}

					$response = successRes("Successfully updated inquiry");
					$Inquiry = json_decode(json_encode($Inquiry), true);
					$response['old'] = $oldInquiry;
					$response['new'] = $Inquiry;

					if ($oldInquiry['assigned_to'] != $Inquiry['assigned_to']) {

						$fromAssignedTo = User::select('first_name', 'last_name')->find($oldInquiry['assigned_to']);
						$fromassignedToName = "";
						if ($fromAssignedTo) {
							$fromassignedToName = $fromAssignedTo->first_name . " " . $fromAssignedTo->last_name;
						}

						$toAssignedTo = User::select('first_name', 'last_name')->find($Inquiry['assigned_to']);
						$toassignedToName = "";
						if ($toAssignedTo) {
							$toassignedToName = $toAssignedTo->first_name . " " . $toAssignedTo->last_name;
						}

						$debugLog = array();
						$debugLog['inquiry_id'] = $Inquiry['id'];
						$debugLog['name'] = "edit";
						$debugLog['description'] = "assigned to updated from " . $fromassignedToName . " to " . $toassignedToName;
						saveInquiryLog($debugLog);

						$UserNotification = UserNotification::where('inquiry_id', $Inquiry['id'])->where('type', 3)->first();
						if ($UserNotification) {
							$UserNotification->delete();
						}

						$UserNotify = array();
						$UserNotify['user_id'] = $Inquiry['assigned_to'];
						$UserNotify['type'] = 3;
						$UserNotify['from_user_id'] = Auth::user()->id;

						$UserNotify['title'] = "Inquiry #" . $Inquiry['id'] . " " . "(" . $Inquiry['first_name'] . " " . $Inquiry['last_name'] . ") assigned to you";

						$UserNotify['description'] = "Inquiry #" . $Inquiry['id'] . " (" . $Inquiry['first_name'] . " " . $Inquiry['last_name'] . ") Assigned to you, Please followup inquiry";
						$UserNotify['inquiry_id'] = $Inquiry['id'];
						saveUserNotification($UserNotify);
					}

					/// SOURCE

					if ($oldInquiry['source_type_value'] != $Inquiry['source_type_value']) {

						$old_source_type = $oldInquiry['source_type'];
						$old_source_type_pieces = explode("-", $old_source_type);
						$oldSourceName = "";
						$newSourceName = "";
						if ($old_source_type_pieces[0] == "user") {

							if (isChannelPartner($old_source_type_pieces[1]) != 0) {

								$User = User::select('users.id', DB::raw("channel_partner.firm_name"));
								$User->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
								$User->where('users.id', $oldInquiry['source_type_value']);
								$User->limit(1);
								$User = $User->first();
								if ($User) {

									$oldSourceName = $User->firm_name;
								}
							} else {

								$User = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
								$User->where('users.id', $oldInquiry['source_type_value']);
								$User->limit(1);
								$User = $User->first();
								if ($User) {

									$oldSourceName = $User->full_name;
								}
							}
						} else if ($old_source_type_pieces[0] == "textrequired" || $old_source_type_pieces[0] == "textnotrequired") {

							foreach ($sourceTypes as $key => $value) {

								if ($value['type'] == $old_source_type_pieces[0] && $value['id'] == $old_source_type_pieces[1]) {
									$oldSourceName = $value['lable'] . " - " . $oldInquiry['source_type_value'];
									break;
								}
							}
						} else {

							foreach ($sourceTypes as $key => $value) {

								if ($value['type'] == $old_source_type_pieces[0] && $value['id'] == $old_source_type_pieces[1]) {
									$oldSourceName = $value['lable'];
									break;
								}
							}
						}

						if ($source_type_pieces[0] == "user") {

							if (isChannelPartner($source_type_pieces[1]) != 0) {

								$User = User::select('users.id', DB::raw("channel_partner.firm_name"));
								$User->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
								$User->where('users.id', $Inquiry['source_type_value']);
								$User->limit(1);
								$User = $User->first();
								if ($User) {

									$newSourceName = $User->firm_name;
								}
							} else {

								$User = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
								$User->where('users.id', $Inquiry['source_type_value']);
								$User->limit(1);
								$User = $User->first();
								if ($User) {

									$newSourceName = $User->full_name;
								}
							}
						} else if ($source_type_pieces[0] == "textrequired" || $source_type_pieces[0] == "textnotrequired") {

							foreach ($sourceTypes as $key => $value) {

								if ($value['type'] == $source_type_pieces[0] && $value['id'] == $source_type_pieces[1]) {
									$newSourceName = $value['lable'] . " - " . $Inquiry['source_type_value'];
									break;
								}
							}
						} else {

							foreach ($sourceTypes as $key => $value) {

								if ($value['type'] == $source_type_pieces[0] && $value['id'] == $source_type_pieces[1]) {
									$newSourceName = $value['lable'];
									break;
								}
							}
						}

						$debugLog = array();
						$debugLog['inquiry_id'] = $Inquiry['id'];
						$debugLog['name'] = "edit";
						$debugLog['description'] = "source updated from " . $oldSourceName . " to " . $newSourceName;
						saveInquiryLog($debugLog);
					}

					if ($oldInquiry['source_type_value_1'] != $Inquiry['source_type_value_1']) {

						$old_source_type = $oldInquiry['source_type_1'];
						$old_source_type_pieces = explode("-", $old_source_type);
						$oldSourceName = "";
						$newSourceName = "";
						if ($old_source_type_pieces[0] == "user") {

							if (isChannelPartner($old_source_type_pieces[1]) != 0) {

								$User = User::select('users.id', DB::raw("channel_partner.firm_name"));
								$User->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
								$User->where('users.id', $oldInquiry['source_type_value_1']);
								$User->limit(1);
								$User = $User->first();
								if ($User) {

									$oldSourceName = $User->firm_name;
								}
							} else {

								$User = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
								$User->where('users.id', $oldInquiry['source_type_value_1']);
								$User->limit(1);
								$User = $User->first();
								if ($User) {

									$oldSourceName = $User->full_name;
								}
							}
						} else if ($old_source_type_pieces[0] == "textrequired" || $old_source_type_pieces[0] == "textnotrequired") {

							foreach ($sourceTypes as $key => $value) {

								if ($value['type'] == $old_source_type_pieces[0] && $value['id'] == $old_source_type_pieces[1]) {
									$oldSourceName = $value['lable'] . " - " . $oldInquiry['source_type_value_1'];
									break;
								}
							}
						} else {

							foreach ($sourceTypes as $key => $value) {

								if ($value['type'] == $old_source_type_pieces[0] && $value['id'] == $old_source_type_pieces[1]) {
									$oldSourceName = $value['lable'];
									break;
								}
							}
						}

						if ($source_type_pieces_1[0] == "user") {

							if (isChannelPartner($source_type_pieces_1[1]) != 0) {

								$User = User::select('users.id', DB::raw("channel_partner.firm_name"));
								$User->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
								$User->where('users.id', $Inquiry['source_type_value_1']);
								$User->limit(1);
								$User = $User->first();
								if ($User) {

									$newSourceName = $User->firm_name;
								}
							} else {

								$User = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
								$User->where('users.id', $Inquiry['source_type_value_1']);
								$User->limit(1);
								$User = $User->first();
								if ($User) {

									$newSourceName = $User->full_name;
								}
							}
						} else if ($source_type_pieces_1[0] == "textrequired" || $source_type_pieces_1[0] == "textnotrequired") {

							foreach ($sourceTypes as $key => $value) {

								if ($value['type'] == $source_type_pieces_1[0] && $value['id'] == $source_type_pieces_1[1]) {
									$newSourceName = $value['lable'] . " - " . $Inquiry['source_type_value_1'];
									break;
								}
							}
						} else {

							foreach ($sourceTypes as $key => $value) {

								if ($value['type'] == $source_type_pieces_1[0] && $value['id'] == $source_type_pieces_1[1]) {
									$newSourceName = $value['lable'];
									break;
								}
							}
						}

						$debugLog = array();
						$debugLog['inquiry_id'] = $Inquiry['id'];
						$debugLog['name'] = "edit";
						$debugLog['description'] = "source1 updated from " . $oldSourceName . " to " . $newSourceName;
						saveInquiryLog($debugLog);
					}

					/// SOURCE

					if ($oldInquiry['source_type_value_2'] != $Inquiry['source_type_value_2']) {

						$old_source_type = $oldInquiry['source_type_2'];
						$old_source_type_pieces = explode("-", $old_source_type);
						$oldSourceName = "";
						$newSourceName = "";
						if ($old_source_type_pieces[0] == "user") {

							if (isChannelPartner($old_source_type_pieces[1]) != 0) {

								$User = User::select('users.id', DB::raw("channel_partner.firm_name"));
								$User->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
								$User->where('users.id', $oldInquiry['source_type_value_2']);
								$User->limit(1);
								$User = $User->first();
								if ($User) {

									$oldSourceName = $User->firm_name;
								}
							} else {

								$User = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
								$User->where('users.id', $oldInquiry['source_type_value_2']);
								$User->limit(1);
								$User = $User->first();
								if ($User) {

									$oldSourceName = $User->full_name;
								}
							}
						} else if ($old_source_type_pieces[0] == "textrequired" || $old_source_type_pieces[0] == "textnotrequired") {

							foreach ($sourceTypes as $key => $value) {

								if ($value['type'] == $old_source_type_pieces[0] && $value['id'] == $old_source_type_pieces[1]) {
									$oldSourceName = $value['lable'] . " - " . $oldInquiry['source_type_value_2'];
									break;
								}
							}
						} else {

							foreach ($sourceTypes as $key => $value) {

								if ($value['type'] == $old_source_type_pieces[0] && $value['id'] == $old_source_type_pieces[1]) {
									$oldSourceName = $value['lable'];
									break;
								}
							}
						}

						if ($source_type_pieces_2[0] == "user") {

							if (isChannelPartner($source_type_pieces_2[1]) != 0) {

								$User = User::select('users.id', DB::raw("channel_partner.firm_name"));
								$User->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
								$User->where('users.id', $Inquiry['source_type_value_2']);
								$User->limit(1);
								$User = $User->first();
								if ($User) {

									$newSourceName = $User->firm_name;
								}
							} else {

								$User = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
								$User->where('users.id', $Inquiry['source_type_value_2']);
								$User->limit(1);
								$User = $User->first();
								if ($User) {

									$newSourceName = $User->full_name;
								}
							}
						} else if ($source_type_pieces_2[0] == "textrequired" || $source_type_pieces_2[0] == "textnotrequired") {

							foreach ($sourceTypes as $key => $value) {

								if ($value['type'] == $source_type_pieces_2[0] && $value['id'] == $source_type_pieces_2[1]) {
									$newSourceName = $value['lable'] . " - " . $Inquiry['source_type_value_2'];
									break;
								}
							}
						} else {

							foreach ($sourceTypes as $key => $value) {

								if ($value['type'] == $source_type_pieces_2[0] && $value['id'] == $source_type_pieces_2[1]) {
									$newSourceName = $value['lable'];
									break;
								}
							}
						}

						$debugLog = array();
						$debugLog['inquiry_id'] = $Inquiry['id'];
						$debugLog['name'] = "edit";
						$debugLog['description'] = "source2 updated from " . $oldSourceName . " to " . $newSourceName;
						saveInquiryLog($debugLog);
					}

					if ($oldInquiry['source_type_value_3'] != $Inquiry['source_type_value_3']) {

						$old_source_type = $oldInquiry['source_type_3'];
						$old_source_type_pieces = explode("-", $old_source_type);
						$oldSourceName = "";
						$newSourceName = "";
						if ($old_source_type_pieces[0] == "user") {

							if (isChannelPartner($old_source_type_pieces[1]) != 0) {

								$User = User::select('users.id', DB::raw("channel_partner.firm_name"));
								$User->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
								$User->where('users.id', $oldInquiry['source_type_value_3']);
								$User->limit(1);
								$User = $User->first();
								if ($User) {

									$oldSourceName = $User->firm_name;
								}
							} else {

								$User = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
								$User->where('users.id', $oldInquiry['source_type_value_3']);
								$User->limit(1);
								$User = $User->first();
								if ($User) {

									$oldSourceName = $User->full_name;
								}
							}
						} else if ($old_source_type_pieces[0] == "textrequired" || $old_source_type_pieces[0] == "textnotrequired") {

							foreach ($sourceTypes as $key => $value) {

								if ($value['type'] == $old_source_type_pieces[0] && $value['id'] == $old_source_type_pieces[1]) {
									$oldSourceName = $value['lable'] . " - " . $oldInquiry['source_type_value_3'];
									break;
								}
							}
						} else {

							foreach ($sourceTypes as $key => $value) {

								if ($value['type'] == $old_source_type_pieces[0] && $value['id'] == $old_source_type_pieces[1]) {
									$oldSourceName = $value['lable'];
									break;
								}
							}
						}

						if ($source_type_pieces_3[0] == "user") {

							if (isChannelPartner($source_type_pieces_3[1]) != 0) {

								$User = User::select('users.id', DB::raw("channel_partner.firm_name"));
								$User->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
								$User->where('users.id', $Inquiry['source_type_value_3']);
								$User->limit(1);
								$User = $User->first();
								if ($User) {

									$newSourceName = $User->firm_name;
								}
							} else {

								$User = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
								$User->where('users.id', $Inquiry['source_type_value_3']);
								$User->limit(1);
								$User = $User->first();
								if ($User) {

									$newSourceName = $User->full_name;
								}
							}
						} else if ($source_type_pieces_3[0] == "textrequired" || $source_type_pieces_3[0] == "textnotrequired") {

							foreach ($sourceTypes as $key => $value) {

								if ($value['type'] == $source_type_pieces_3[0] && $value['id'] == $source_type_pieces_3[1]) {
									$newSourceName = $value['lable'] . " - " . $Inquiry['source_type_value_3'];
									break;
								}
							}
						} else {

							foreach ($sourceTypes as $key => $value) {

								if ($value['type'] == $source_type_pieces_3[0] && $value['id'] == $source_type_pieces_3[1]) {
									$newSourceName = $value['lable'];
									break;
								}
							}
						}

						$debugLog = array();
						$debugLog['inquiry_id'] = $Inquiry['id'];
						$debugLog['name'] = "edit";
						$debugLog['description'] = "source3 updated from " . $oldSourceName . " to " . $newSourceName;
						saveInquiryLog($debugLog);
					}

					if ($oldInquiry['source_type_value_4'] != $Inquiry['source_type_value_4']) {

						$old_source_type = $oldInquiry['source_type_4'];
						$old_source_type_pieces = explode("-", $old_source_type);
						$oldSourceName = "";
						$newSourceName = "";
						if ($old_source_type_pieces[0] == "user") {

							if (isChannelPartner($old_source_type_pieces[1]) != 0) {

								$User = User::select('users.id', DB::raw("channel_partner.firm_name"));
								$User->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
								$User->where('users.id', $oldInquiry['source_type_value_4']);
								$User->limit(1);
								$User = $User->first();
								if ($User) {

									$oldSourceName = $User->firm_name;
								}
							} else {

								$User = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
								$User->where('users.id', $oldInquiry['source_type_value_4']);
								$User->limit(1);
								$User = $User->first();
								if ($User) {

									$oldSourceName = $User->full_name;
								}
							}
						} else if ($old_source_type_pieces[0] == "textrequired" || $old_source_type_pieces[0] == "textnotrequired") {

							foreach ($sourceTypes as $key => $value) {

								if ($value['type'] == $old_source_type_pieces[0] && $value['id'] == $old_source_type_pieces[1]) {
									$oldSourceName = $value['lable'] . " - " . $oldInquiry['source_type_value_4'];
									break;
								}
							}
						} else {

							foreach ($sourceTypes as $key => $value) {

								if ($value['type'] == $old_source_type_pieces[0] && $value['id'] == $old_source_type_pieces[1]) {
									$oldSourceName = $value['lable'];
									break;
								}
							}
						}

						if ($source_type_pieces_4[0] == "user") {

							if (isChannelPartner($source_type_pieces_4[1]) != 0) {

								$User = User::select('users.id', DB::raw("channel_partner.firm_name"));
								$User->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
								$User->where('users.id', $Inquiry['source_type_value_4']);
								$User->limit(1);
								$User = $User->first();
								if ($User) {

									$newSourceName = $User->firm_name;
								}
							} else {

								$User = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
								$User->where('users.id', $Inquiry['source_type_value_4']);
								$User->limit(1);
								$User = $User->first();
								if ($User) {

									$newSourceName = $User->full_name;
								}
							}
						} else if ($source_type_pieces_4[0] == "textrequired" || $source_type_pieces_4[0] == "textnotrequired") {

							foreach ($sourceTypes as $key => $value) {

								if ($value['type'] == $source_type_pieces_4[0] && $value['id'] == $source_type_pieces_4[1]) {
									$newSourceName = $value['lable'] . " - " . $Inquiry['source_type_value_4'];
									break;
								}
							}
						} else {

							foreach ($sourceTypes as $key => $value) {

								if ($value['type'] == $source_type_pieces_4[0] && $value['id'] == $source_type_pieces_4[1]) {
									$newSourceName = $value['lable'];
									break;
								}
							}
						}

						$debugLog = array();
						$debugLog['inquiry_id'] = $Inquiry['id'];
						$debugLog['name'] = "edit";
						$debugLog['description'] = "source4 updated from " . $oldSourceName . " to " . $newSourceName;
						saveInquiryLog($debugLog);
					}

					if ($oldInquiry['first_name'] != $Inquiry['first_name']) {

						$debugLog = array();
						$debugLog['inquiry_id'] = $Inquiry['id'];
						$debugLog['name'] = "edit";
						$debugLog['description'] = "first name updated from " . $oldInquiry['first_name'] . " to " . $Inquiry['first_name'];
						saveInquiryLog($debugLog);
					}

					if ($oldInquiry['last_name'] != $Inquiry['last_name']) {

						$debugLog = array();
						$debugLog['inquiry_id'] = $Inquiry['id'];
						$debugLog['name'] = "edit";
						$debugLog['description'] = "last name updated from " . $oldInquiry['last_name'] . " to " . $Inquiry['last_name'];
						saveInquiryLog($debugLog);
					}

					if ($oldInquiry['phone_number'] != $Inquiry['phone_number']) {

						$debugLog = array();
						$debugLog['inquiry_id'] = $Inquiry['id'];
						$debugLog['name'] = "edit";
						$debugLog['description'] = "phone number updated from " . $oldInquiry['phone_number'] . " to " . $Inquiry['phone_number'];
						saveInquiryLog($debugLog);
					}

					if ($oldInquiry['pincode'] != $Inquiry['pincode']) {

						$debugLog = array();
						$debugLog['inquiry_id'] = $Inquiry['id'];
						$debugLog['name'] = "edit";
						$debugLog['description'] = "pincode updated from " . $oldInquiry['pincode'] . " to " . $Inquiry['pincode'];
						saveInquiryLog($debugLog);
					}

					if ($oldInquiry['city_id'] != $Inquiry['city_id']) {

						$fromCityName = getCityName($oldInquiry['city_id']);
						$toCityName = getCityName($Inquiry['city_id']);

						$debugLog = array();
						$debugLog['inquiry_id'] = $Inquiry['id'];
						$debugLog['name'] = "edit";
						$debugLog['description'] = "city updated from " . $fromCityName . " to " . $toCityName;
						saveInquiryLog($debugLog);
					}

					if ($oldInquiry['house_no'] != $Inquiry['house_no']) {

						$debugLog = array();
						$debugLog['inquiry_id'] = $Inquiry['id'];
						$debugLog['name'] = "edit";
						$debugLog['description'] = "house no updated from " . $oldInquiry['house_no'] . " to " . $Inquiry['house_no'];
						saveInquiryLog($debugLog);
					}

					if ($oldInquiry['society_name'] != $Inquiry['society_name']) {

						$debugLog = array();
						$debugLog['inquiry_id'] = $Inquiry['id'];
						$debugLog['name'] = "edit";
						$debugLog['description'] = "society name updated from " . $oldInquiry['society_name'] . " to " . $Inquiry['society_name'];
						saveInquiryLog($debugLog);
					}

					if ($oldInquiry['area'] != $Inquiry['area']) {

						$debugLog = array();
						$debugLog['inquiry_id'] = $Inquiry['id'];
						$debugLog['name'] = "edit";
						$debugLog['description'] = "area updated from " . $oldInquiry['area'] . " to " . $Inquiry['area'];
						saveInquiryLog($debugLog);
					}

					if ($oldInquiry['architect'] != $Inquiry['architect']) {

						$fromArchitectName = "";

						$oldArchitech = User::select('first_name', 'last_name')->find($oldInquiry['architect']);

						if ($oldArchitech) {

							$fromArchitectName = $oldArchitech->first_name . " " . $oldArchitech->last_name;
						}

						$toArchitectName = "";

						if ($architect) {
							$toArchitectName = $architechObject->first_name . " " . $architechObject->last_name;
						}

						$debugLog = array();
						$debugLog['inquiry_id'] = $Inquiry['id'];
						$debugLog['name'] = "edit";
						$debugLog['description'] = "architect updated from " . $fromArchitectName . " to " . $toArchitectName;
						saveInquiryLog($debugLog);
					}

					if ($oldInquiry['electrician'] != $Inquiry['electrician']) {

						$fromElectricianName = "";

						$oldElectrician = User::select('first_name', 'last_name')->find($oldInquiry['architect']);
						if ($oldElectrician) {
							$fromElectricianName = $oldElectrician->first_name . " " . $oldElectrician->last_name;
						}

						$toElectricianName = "";

						if ($electrician) {
							$toElectricianName = $electricianObject->first_name . " " . $electricianObject->last_name;
						}

						$debugLog = array();
						$debugLog['inquiry_id'] = $Inquiry['id'];
						$debugLog['name'] = "edit";
						$debugLog['description'] = "electrician updated from " . $fromElectricianName . " to " . $toElectricianName;
						saveInquiryLog($debugLog);
					}

					if ($oldInquiry['stage_of_site'] != $Inquiry['stage_of_site']) {

						$debugLog = array();
						$debugLog['inquiry_id'] = $Inquiry['id'];
						$debugLog['name'] = "edit";
						$debugLog['description'] = "stage of site  updated from " . $oldInquiry['stage_of_site'] . " to " . $Inquiry['stage_of_site'];
						saveInquiryLog($debugLog);
					}

					if ($oldInquiry['site_photos'] != $Inquiry['site_photos']) {

						$debugLog = array();
						$debugLog['inquiry_id'] = $Inquiry['id'];
						$debugLog['name'] = "edit";
						$debugLog['description'] = "site photo updated";
						saveInquiryLog($debugLog);
					}

					if ($oldInquiry['required_for_property'] != $Inquiry['required_for_property']) {

						$debugLog = array();
						$debugLog['inquiry_id'] = $Inquiry['id'];
						$debugLog['name'] = "edit";
						$debugLog['description'] = "required for property  updated from " . $oldInquiry['required_for_property'] . " to " . $Inquiry['required_for_property'];
						saveInquiryLog($debugLog);
					}

					if ($oldInquiry['changes_of_closing_order'] != $Inquiry['changes_of_closing_order']) {

						$debugLog = array();
						$debugLog['inquiry_id'] = $Inquiry['id'];
						$debugLog['name'] = "edit";
						$debugLog['description'] = "changes of closing order  updated from " . $oldInquiry['changes_of_closing_order'] . " to " . $Inquiry['changes_of_closing_order'];
						saveInquiryLog($debugLog);
					}

					if ($oldInquiry['follow_up_type'] != $Inquiry['follow_up_type']) {

						$debugLog = array();
						$debugLog['inquiry_id'] = $Inquiry['id'];
						$debugLog['name'] = "edit";
						$debugLog['description'] = "follow up type  updated from " . $oldInquiry['follow_up_type'] . " to " . $Inquiry['follow_up_type'];
						saveInquiryLog($debugLog);
					}

					if ($oldInquiry['follow_up_date_time'] != $Inquiry['follow_up_date_time']) {

						$debugLog = array();
						$debugLog['inquiry_id'] = $Inquiry['id'];
						$debugLog['name'] = "follow-up-date-time";
						$debugLog['description'] = "Follow up date & time " . date('Y/m/d h:i:s A', strtotime($Inquiry['follow_up_date_time']));

						saveInquiryLog($debugLog);
					}
				} else {
					$response = errorRes("Invalid access");
				}
			}
		}

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function detail(Request $request)
	{

		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		$isThirdPartyUser = isThirdPartyUser();
		$isSalePerson = isSalePerson();
		$isChannelPartner = isChannelPartner(Auth::user()->type);
		$isTaleSalesUser = isTaleSalesUser();

		if ($isTaleSalesUser == 1) {

			$TaleSalesCities = TeleSalesCity(Auth::user()->id);
		}

		$Inquiry = Inquiry::select('id', 'assigned_to', 'first_name', 'last_name', 'phone_number', 'phone_number2', 'house_no', 'society_name', 'area', 'pincode', 'city_id', 'architect', 'electrician', 'assigned_to', 'required_for_property', 'stage_of_site', 'changes_of_closing_order', 'follow_up_type', 'follow_up_date_time', 'source_type', 'source_type_value', 'quotation', 'quotation_amount', 'source_type_1', 'source_type_value_1', 'source_type_2', 'source_type_value_2', 'source_type_3', 'source_type_value_3', 'source_type_4', 'source_type_value_4', 'billing_amount')->find($request->inquiry_id);

		$childSalePersonsIds = array();
		if ($isSalePerson == 1) {
			$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
		}

		if (($isAdminOrCompanyAdmin == 1) || ($isThirdPartyUser == 1) || ($isSalePerson == 1 && in_array($Inquiry->assigned_to, $childSalePersonsIds)) || ($isChannelPartner != 0 && ($Inquiry->source_type_value == Auth::user()->id || $Inquiry->source_type_value_1 == Auth::user()->id || $Inquiry->source_type_value_2 == Auth::user()->id || $Inquiry->source_type_value_2 == Auth::user()->id || $Inquiry->source_type_value_4 == Auth::user()->id)) || (($isTaleSalesUser == 1 && in_array($Inquiry->city_id, $TaleSalesCities)))) {

			$response = successRes("Successfully get Inquiry Questions");

			$data = array();
			$data['ui_type'] = $request->ui_type;
			$data['inquiry_id'] = $Inquiry->id;
			if ($data['ui_type'] == "inquiry_update") {

				$data['update'] = InquiryUpdate::select('inquiry_update.id', 'inquiry_update.message', 'inquiry_update.created_at', 'inquiry_update.user_id', 'users.first_name', 'users.last_name')->leftJoin('users', 'inquiry_update.user_id', '=', 'users.id')->where('inquiry_update.reply_id', 0)->where('inquiry_update.inquiry_id', $request->inquiry_id)->orderBy('inquiry_update.id', 'desc')->get();

				foreach ($data['update'] as $key => $value) {

					$InquiryUpdateSeen = InquiryUpdateSeen::where('inquiry_update_id', $value->id)->where('inquiry_id', $Inquiry->id)->where('user_id', Auth::user()->id)->first();

					if (!$InquiryUpdateSeen) {

						$InquiryUpdateSeen = new InquiryUpdateSeen();
						$InquiryUpdateSeen->inquiry_update_id = $value->id;
						$InquiryUpdateSeen->inquiry_id = $Inquiry->id;
						$InquiryUpdateSeen->user_id = Auth::user()->id;
						$InquiryUpdateSeen->save();
					}

					$data['update'][$key]['reply'] = InquiryUpdate::select('inquiry_update.id', 'inquiry_update.message', 'inquiry_update.created_at', 'inquiry_update.user_id', 'users.first_name', 'users.last_name')->leftJoin('users', 'inquiry_update.user_id', '=', 'users.id')->where('inquiry_update.reply_id', $value->id)->where('inquiry_update.inquiry_id', $request->inquiry_id)->orderBy('inquiry_update.id', 'asc')->get();

					foreach ($data['update'][$key]['reply'] as $keyR => $valueR) {

						$InquiryUpdateSeen = InquiryUpdateSeen::where('inquiry_update_id', $valueR->id)->where('inquiry_id', $Inquiry->id)->where('user_id', Auth::user()->id)->first();

						if (!$InquiryUpdateSeen) {

							$InquiryUpdateSeen = new InquiryUpdateSeen();
							$InquiryUpdateSeen->inquiry_update_id = $valueR->id;
							$InquiryUpdateSeen->inquiry_id = $Inquiry->id;
							$InquiryUpdateSeen->user_id = Auth::user()->id;
							$InquiryUpdateSeen->save();
						}
					}
				}

				$response['view'] = view('crm/inquiry/detail', compact('data'))->render();
			} elseif ($data['ui_type'] == "inquiry_files") {
				$data['files'] = InquiryQuestionAnswer::where('inquiry_id', $request->inquiry_id)->where('inquiry_question_id', 1)->where('question_type', 2)->where('answer', '!=', '')->where('inquiry_id', $request->inquiry_id)->orderBy('id', 'desc')->get();

				$response['view'] = view('crm/inquiry/detail', compact('data'))->render();
			} else if ($data['ui_type'] == "inquiry_log") {

				$data['log'] = InquiryLog::select('inquiry_log.id', 'inquiry_log.name', 'inquiry_log.description', 'inquiry_log.created_at', 'inquiry_log.user_id', 'users.first_name', 'users.last_name')->leftJoin('users', 'inquiry_log.user_id', '=', 'users.id')->where('inquiry_log.inquiry_id', $request->inquiry_id)->orderBy('inquiry_log.id', 'desc')->get();
				$response['view'] = view('crm/inquiry/detail', compact('data'))->render();
			} else if ($data['ui_type'] == "inquiry_answer") {

				$data['answer'] = InquiryQuestionAnswer::leftJoin('inquiry_questions', 'inquiry_questions.id', '=', 'inquiry_question_answer.inquiry_question_id')->where('inquiry_question_answer.inquiry_id', $request->inquiry_id)->orderBy('inquiry_question_answer.id', 'asc')->get();

				foreach ($data['answer'] as $key => $value) {

					if ($value->question_type == 1 || $value->question_type == 4 || $value->question_type == 6) {

						$answer = "";
						$options = InquiryQuestionOption::whereIn('id', explode(",", $value->answer))->get();
						if (count($options) > 0) {
							$answer = array();
							foreach ($options as $keyOP => $valueOP) {

								$answer[] = $valueOP->option;
							}

							$data['answer'][$key]['answer'] = implode(",", $answer);
						}
					} else if ($value->question_type == 3) {
						$answer = "";

						if ($value->answer == 1) {
							$answer = "Yes";
						} else if ($value->answer == 1) {
							$answer = "No";
						}

						$data['answer'][$key]['answer'] = $answer;
					} else if ($value->question_type == 2) {

						if ($value->answer != "") {
							$data['answer'][$key]['answer'] = '<a target="_blank" href="' . getSpaceFilePath($value->answer) . '"  class="btn btn-sm btn-dark waves-effect waves-light">
                                                <i class="mdi mdi-download font-size-16 align-middle me-2 "></i> Download
                                            </a>';
						} else {
							$data['answer'][$key]['answer'] = "";
						}
					} else if ($value->question_type == 7) {

						if ($value->answer != "") {

							$piecesOfAnswer = explode(",", $value->answer);

							$answerUI = "";

							foreach ($piecesOfAnswer as $keyMF => $valueMF) {

								$answerUI .= '<a target="_blank" href="' . getSpaceFilePath($valueMF) . '"  class="btn btn-sm btn-dark waves-effect waves-light">
							                                          <i class="mdi mdi-download font-size-16 align-middle me-2 "></i> Download
							                                      </a>&nbsp;&nbsp;&nbsp;';
							}

							$data['answer'][$key]['answer'] = $answerUI;
						} else {

							$data['answer'][$key]['answer'] = "";
						}

						// } else {
						// 	$data['answer'][$key]['answer'] = "";
						// }

					}
				}

				$response['view'] = view('crm/inquiry/detail', compact('data'))->render();
			} else if ($data['ui_type'] == "inquiry_detail") {

				$Inquiry = json_decode(json_encode($Inquiry), true);

				$source_type_pieces = explode("-", $Inquiry['source_type']);

				if ($source_type_pieces[0] == "user") {

					if (isChannelPartner($source_type_pieces[1]) != 0) {

						$User = User::select('users.id', DB::raw("channel_partner.firm_name"));
						$User->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
						$User->where('users.id', $Inquiry['source_type_value']);
						$User->limit(1);
						$User = $User->first();
						if ($User) {

							$Inquiry['source'] = array();
							$Inquiry['source']['id'] = $User->id;
							$Inquiry['source']['text'] = $User->firm_name;
						}
					} else {

						$User = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
						$User->where('users.id', $Inquiry['source_type_value']);
						$User->limit(1);
						$User = $User->first();
						if ($User) {

							$Inquiry['source'] = array();
							$Inquiry['source']['id'] = $User->id;
							$Inquiry['source']['text'] = $User->full_name;
						}
					}
				} else if ($source_type_pieces[0] == "exhibition") {

					$Exhibition = $ExhibitionInquiry = Exhibition::select('id', 'name as text');
					$Exhibition->where('exhibition.id', $Inquiry['source_type_value']);
					$Exhibition->limit(1);
					$Exhibition = $Exhibition->first();
					if ($Exhibition) {

						$Inquiry['source'] = array();
						$Inquiry['source']['id'] = $Exhibition->id;
						$Inquiry['source']['text'] = $Exhibition->text;
					}
				}

				$source_type_pieces_1 = explode("-", $Inquiry['source_type_1']);

				if ($source_type_pieces_1[0] == "user") {

					if (isChannelPartner($source_type_pieces_1[1]) != 0) {

						$User = User::select('users.id', DB::raw("channel_partner.firm_name"));
						$User->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
						$User->where('users.id', $Inquiry['source_type_value_1']);
						$User->limit(1);
						$User = $User->first();
						if ($User) {

							$Inquiry['source_1'] = array();
							$Inquiry['source_1']['id'] = $User->id;
							$Inquiry['source_1']['text'] = $User->firm_name;
						}
					} else {

						$User = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
						$User->where('users.id', $Inquiry['source_type_value_1']);
						$User->limit(1);
						$User = $User->first();
						if ($User) {

							$Inquiry['source_1'] = array();
							$Inquiry['source_1']['id'] = $User->id;
							$Inquiry['source_1']['text'] = $User->full_name;
						}
					}
				}

				$source_type_pieces_2 = explode("-", $Inquiry['source_type_2']);

				if ($source_type_pieces_2[0] == "user") {

					if (isChannelPartner($source_type_pieces_2[1]) != 0) {

						$User = User::select('users.id', DB::raw("channel_partner.firm_name"));
						$User->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
						$User->where('users.id', $Inquiry['source_type_value_2']);
						$User->limit(1);
						$User = $User->first();
						if ($User) {

							$Inquiry['source_2'] = array();
							$Inquiry['source_2']['id'] = $User->id;
							$Inquiry['source_2']['text'] = $User->firm_name;
						}
					} else {

						$User = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
						$User->where('users.id', $Inquiry['source_type_value_2']);
						$User->limit(1);
						$User = $User->first();
						if ($User) {

							$Inquiry['source_2'] = array();
							$Inquiry['source_2']['id'] = $User->id;
							$Inquiry['source_2']['text'] = $User->full_name;
						}
					}
				}

				$source_type_pieces_3 = explode("-", $Inquiry['source_type_3']);

				if ($source_type_pieces_3[0] == "user") {

					if (isChannelPartner($source_type_pieces_3[1]) != 0) {

						$User = User::select('users.id', DB::raw("channel_partner.firm_name"));
						$User->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
						$User->where('users.id', $Inquiry['source_type_value_3']);
						$User->limit(1);
						$User = $User->first();
						if ($User) {

							$Inquiry['source_3'] = array();
							$Inquiry['source_3']['id'] = $User->id;
							$Inquiry['source_3']['text'] = $User->firm_name;
						}
					} else {

						$User = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
						$User->where('id', $Inquiry['source_type_value_3']);
						$User->limit(1);
						$User = $User->first();
						if ($User) {

							$Inquiry['source_3'] = array();
							$Inquiry['source_3']['id'] = $User->id;
							$Inquiry['source_3']['text'] = $User->full_name;
						}
					}
				}

				$source_type_pieces_4 = explode("-", $Inquiry['source_type_4']);

				if ($source_type_pieces_4[0] == "user") {

					if (isChannelPartner($source_type_pieces_4[1]) != 0) {

						$User = User::select('users.id', DB::raw("channel_partner.firm_name"));
						$User->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
						$User->where('users.id', $Inquiry['source_type_value_4']);
						$User->limit(1);
						$User = $User->first();
						if ($User) {

							$Inquiry['source_4'] = array();
							$Inquiry['source_4']['id'] = $User->id;
							$Inquiry['source_4']['text'] = $User->firm_name;
						}
					} else {

						$User = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
						$User->where('users.id', $Inquiry['source_type_value_4']);
						$User->limit(1);
						$User = $User->first();
						if ($User) {

							$Inquiry['source_4'] = array();
							$Inquiry['source_4']['id'] = $User->id;
							$Inquiry['source_4']['text'] = $User->full_name;
						}
					}
				}

				$response['detail'] = $Inquiry;
				$response['detail']['city'] = array();
				$response['detail']['city']['id'] = $Inquiry['city_id'];
				$response['detail']['city']['text'] = getCityName($Inquiry['city_id']);

				$User = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
				$User->where('id', $Inquiry['assigned_to']);
				$User->limit(1);
				$User = $User->first();
				if ($User) {

					$response['detail']['assigned_to'] = array();
					$response['detail']['assigned_to']['id'] = $User->id;
					$response['detail']['assigned_to']['text'] = $User->full_name;
				}

				$User = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
				$User->where('users.id', $Inquiry['architect']);
				$User->limit(1);
				$User = $User->first();
				if ($User) {

					$response['detail']['architect'] = array();
					$response['detail']['architect']['id'] = $User->id;
					$response['detail']['architect']['text'] = $User->full_name;
				} else {
					$response['detail']['architect'] = array();
				}

				$User = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
				$User->where('id', $Inquiry['electrician']);
				$User->limit(1);
				$User = $User->first();
				if ($User) {

					$response['detail']['electrician'] = array();
					$response['detail']['electrician']['id'] = $User->id;
					$response['detail']['electrician']['text'] = $User->full_name;
				} else {
					$response['detail']['electrician'] = array();
				}

				if ($response['detail']['required_for_property'] != "") {

					$InquiryQuestionOption = InquiryQuestionOption::select('id', 'option')->where('inquiry_question_id', 9)->where('option', $response['detail']['required_for_property'])->orderBy('id', 'asc')->first();
					if ($InquiryQuestionOption) {
						$response['detail']['required_for_property'] = $InquiryQuestionOption->id;
					} else {
						$response['detail']['required_for_property'] = "";
					}
				}

				if ($response['detail']['stage_of_site'] != "") {

					$InquiryQuestionOption = InquiryQuestionOption::select('id', 'option')->where('inquiry_question_id', 7)->where('option', $response['detail']['stage_of_site'])->orderBy('id', 'asc')->first();
					if ($InquiryQuestionOption) {
						$response['detail']['stage_of_site'] = $InquiryQuestionOption->id;
					} else {
						$response['detail']['stage_of_site'] = "";
					}
				}

				if ($response['detail']['changes_of_closing_order'] != "") {

					$InquiryQuestionOption = InquiryQuestionOption::select('id', 'option')->where('inquiry_question_id', 10)->where('option', $response['detail']['changes_of_closing_order'])->orderBy('id', 'asc')->first();
					if ($InquiryQuestionOption) {
						$response['detail']['changes_of_closing_order'] = $InquiryQuestionOption->id;
					} else {
						$response['detail']['changes_of_closing_order'] = "";
					}
				}

				if ($response['detail']['follow_up_date_time'] != null) {

					$response['detail']['follow_up_date_time'] = date('Y-m-d', strtotime($response['detail']['follow_up_date_time'])) . "T" . date('H:i', strtotime($response['detail']['follow_up_date_time']));

					$response['detail']['follow_up_date'] = date('d-m-Y', strtotime($response['detail']['follow_up_date_time']));
					$response['detail']['follow_up_time'] = date('h:i A', strtotime($response['detail']['follow_up_date_time']));
				} else {
					$response['detail']['follow_up_date_time'] = "";
				}
			}
		} else {
			$response = errorRes("Invalid access");
		}

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function inquiryQuestions(Request $request)
	{
		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		$isChannelPartner = isChannelPartner(Auth::user()->type);
		$isSalePerson = isSalePerson();
		$isThirdPartyUser = isThirdPartyUser();
		$isTaleSalesUser = isTaleSalesUser();
		$TaleSalesCities = array();
		$childSalePersonsIds = array();

		if ($isTaleSalesUser == 1) {
			$TaleSalesCities = TeleSalesCity(Auth::user()->id);
		}

		if ($isSalePerson == 1) {
			$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
		}

		$Inquiry = Inquiry::find($request->inquiry_id);
		if ($Inquiry) {
			if ($isAdminOrCompanyAdmin == 1 || ($isThirdPartyUser == 1 && $Inquiry->source_type_value == Auth::user()->id) || ($isSalePerson == 1 && in_array($Inquiry->assigned_to, $childSalePersonsIds)) || ($isChannelPartner != 0 && $Inquiry->source_type_value == Auth::user()->id) || ($isTaleSalesUser == 1 || in_array($Inquiry->city_id, $TaleSalesCities))) {

				$inquiryStatus = getInquiryStatus();
				$currentStatus = $Inquiry->status;
				$currentStatusIndex = $inquiryStatus[$currentStatus]['index'];
				$newStatus = $request->new_status;
				$newStatusIndex = $inquiryStatus[$newStatus]['index'];
				$questionStatusList = array();
				$onlyIdQuesion = $inquiryStatus[$newStatus]['only_id_question'];
				$needFolloup = $inquiryStatus[$newStatus]['need_followup'];
				if ($onlyIdQuesion == 1) {

					if ($currentStatus == 14 && $newStatus == 12) {
					} else {

						if ($newStatus == 14) {

							$questionStatusList[] = 12;
						} else {
							$questionStatusList[] = $newStatus;
						}
					}
				} else if ($onlyIdQuesion == 0) {

					if ($currentStatus == 11) {
						$questionStatusList[] = 10;
					} else {

						foreach ($inquiryStatus as $keyIQS => $valIQS) {

							if ($valIQS['id'] == 12 || $valIQS['id'] == 14 || $valIQS['id'] == 101 || $valIQS['id'] == 102) {
								continue;
							} else if ($newStatus == 10) {
								if ($valIQS['id'] == 11) {
									continue;
								}
							}

							$nStatusIndex = $inquiryStatus[$valIQS['id']]['index'];

							if ($currentStatusIndex < $nStatusIndex && $newStatusIndex >= $nStatusIndex) {
								$questionStatusList[] = $valIQS['id'];
							}
						}
					}
				}

				$InquiryQuestions = InquiryQuestion::whereIn('status', $questionStatusList)->orderBy('status', 'asc')->orderBy('sequence', 'asc')->get();

				$finalQuestion = array();

				if ($InquiryQuestions) {

					foreach ($InquiryQuestions as $InquiryQuestionKey => $InquiryQuestionValue) {
						$checkForVisible = 0;

						if ($InquiryQuestionValue->is_depend_on_answer == 1) {

							$dependedQuestion = InquiryQuestion::find($InquiryQuestionValue->depended_question_id);

							if ($dependedQuestion && !in_array($dependedQuestion->status, $questionStatusList)) {

								$dependedAnswer = InquiryQuestionAnswer::where('inquiry_id', $request->inquiry_id)->where('inquiry_question_id', $dependedQuestion->id)->first();
								if ($dependedAnswer && $dependedAnswer->answer == $InquiryQuestionValue->depended_question_answer) {

									if ($dependedQuestion->type == 6 || $dependedQuestion->type == 4) {
										$dependedAnswer = explode(",", $dependedAnswer->answer);
										if (in_array($InquiryQuestionValue->depended_question_answer, $dependedAnswer)) {
											$checkForVisible = 1;
										}
									} else if ($dependedAnswer->answer == $InquiryQuestionValue->depended_question_answer) {

										$checkForVisible = 1;
									}
								}
							} else {
								$checkForVisible = 1;
							}
						} else {
							$checkForVisible = 1;
						}

						if ($InquiryQuestionValue->type == 1 || $InquiryQuestionValue->type == 4 || $InquiryQuestionValue->type == 6) {
							$InquiryQuestions[$InquiryQuestionKey]['options'] = InquiryQuestionOption::select('id', 'option')->where('inquiry_question_id', $InquiryQuestionValue->id)->orderBy('id', 'asc')->get();
						}

						if ($checkForVisible == 1) {
							$cFinalQuestion = count($finalQuestion);
							$finalQuestion[$cFinalQuestion] = $InquiryQuestions[$InquiryQuestionKey];
							if ($InquiryQuestions[$InquiryQuestionKey]->is_depend_on_answer == 1) {

								$dependedQuestion = InquiryQuestion::find($InquiryQuestions[$InquiryQuestionKey]['depended_question_id']);
								if ($dependedQuestion) {

									$InquiryQuestions[$InquiryQuestionKey]['depended_question'] = $dependedQuestion;
								} else {

									$InquiryQuestions[$InquiryQuestionKey]->is_depend_on_answer = 0;
								}
							}
						}
					}

					// echo '<pre>';
					// print_r(json_encode($InquiryQuestions));
					// die;

					$response = successRes("Successfully get Inquiry Questions");
					//$response['data'] = $InquiryQuestions;
					$data = array();
					$data['question'] = $finalQuestion;
					$data['inquiry_id'] = $request->inquiry_id;
					$data['inquiry_status'] = $request->new_status;
					$data['need_followup'] = $needFolloup;

					if ($Inquiry->stage_of_site != "") {

						$Option = InquiryQuestionOption::where('inquiry_question_id', 7)->where('option', $Inquiry->stage_of_site)->first();
						if ($Option) {
							$Inquiry->stage_of_site = $Option->id;
						} else {
							$Inquiry->stage_of_site = "";
						}
					}

					if ($Inquiry->required_for_property != "") {

						$Option = InquiryQuestionOption::where('inquiry_question_id', 9)->where('option', $Inquiry->required_for_property)->first();
						if ($Option) {
							$Inquiry->required_for_property = $Option->id;
						} else {
							$Inquiry->required_for_property = "";
						}
					}

					if ($Inquiry->changes_of_closing_order != "") {

						$Option = InquiryQuestionOption::where('inquiry_question_id', 10)->where('option', $Inquiry->changes_of_closing_order)->first();
						if ($Option) {
							$Inquiry->changes_of_closing_order = $Option->id;
						} else {
							$Inquiry->changes_of_closing_order = "";
						}
					}
					if ($Inquiry->site_photos != "") {

						$Inquiry->site_photos = '<a target="_blank" href="' . getSpaceFilePath($Inquiry->site_photos) . '"  class="btn btn-sm btn-dark waves-effect waves-light">
                                                <i class="mdi mdi-download font-size-16 align-middle me-2 "></i> Download
                                            </a>';
					}

					if ($Inquiry->quotation != "") {

						$Inquiry->quotation = '<a target="_blank" href="' . getSpaceFilePath($Inquiry->quotation) . '"  class="btn btn-sm btn-dark waves-effect waves-light">
                                                <i class="mdi mdi-download font-size-16 align-middle me-2 "></i> Download
                                            </a>';
					}

					$User = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
					$User->where('id', $Inquiry['architect']);
					$User->limit(1);
					$User = $User->first();
					if ($User) {

						$response['architect'] = array();
						$response['architect']['id'] = $User->id;
						$response['architect']['text'] = $User->full_name;
					} else {
						$response['architect'] = array();
					}

					$User = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
					$User->where('id', $Inquiry['electrician']);
					$User->limit(1);
					$User = $User->first();
					if ($User) {

						$response['electrician'] = array();
						$response['electrician']['id'] = $User->id;
						$response['electrician']['text'] = $User->full_name;
					} else {
						$response['detail']['electrician'] = array();
					}

					$response['inquiry'] = $Inquiry;
					$data['timeSlot'] = $this->getInquiryTimeSlot();
					$data['questionStatusList'] = $questionStatusList;
					$response['question_status_list'] = $questionStatusList;
					$response['view'] = view('crm/inquiry/answer', compact('data'))->render();
				} else {
					$response = errorRes("Invalid id");
				}
			} else {

				$response = errorRes("Invalid access");
			}
			return response()->json($response)->header('Content-Type', 'application/json');
		}
	}

	public function searchExhibition(Request $request)
	{

		$searchKeyword = isset($request->q) ? $request->q : "sur";

		$ExhibitionInquiry = Exhibition::select('id', 'name as text');
		$ExhibitionInquiry->where('name', 'like', "%" . $searchKeyword . "%");
		// $ExhibitionInquiry->where('status', 1);
		$ExhibitionInquiry->limit(5);
		$ExhibitionInquiry = $ExhibitionInquiry->get();
		$response = array();
		$response['results'] = $ExhibitionInquiry;
		$response['pagination']['more'] = false;
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function searchUser(Request $request)
	{

		$isArchitect = isArchitect();
		$isSalePerson = isSalePerson();
		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		$isThirdPartyUser = isThirdPartyUser();
		$isChannelPartner = isChannelPartner(Auth::user()->type);

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
			$User->whereIn('users.type', array(201, 202, 301, 302));

			if ($isSalePerson == 1) {

				$User->leftJoin('architect', 'architect.user_id', '=', 'users.id');
				$User->leftJoin('electrician', 'electrician.user_id', '=', 'users.id');

				$User->where(function ($query) use ($cities) {

					$query->whereIn('users.city_id', $cities);
					// $query->orWhere('electrician.sale_person_id', Auth::user()->id);

				});
			} else if ($isChannelPartner != 0) {

				$User->where('users.city_id', Auth::user()->city_id);
			} else if ($isThirdPartyUser == 1) {

				$User->where('users.city_id', $request->city_id);
			}

			$User->where('users.id', $request->user_id);
			$User->limit(1);
			$UserResponse = $User->get();
		} else {

			if ($isChannelPartner != 0 && ($request->source_type == 201 || $request->source_type == 202)) {

				$UserResponse = array();
				$q = $request->q;
				$User = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
				$User->where('users.status', 1);
				$User->where('users.type', $request->source_type);
				$User->leftJoin('architect', 'architect.user_id', '=', 'users.id');
				$User->where('users.city_id', Auth::user()->city_id);

				//$User->whereIn('architect.sale_person_id', $childSalePersonsIds);

				$User->where(function ($query) use ($q) {

					$query->WhereRaw('CONCAT(users.first_name," ",users.last_name) like ?', [$q]);
					$query->orWhereRaw('CONCAT(users.first_name," ",users.last_name) like ? ', ["%" . $q . "%"]);

					// $query->where('users.first_name', 'like', '%' . $q . '%');
					// $query->orWhere('users.last_name', 'like', '%' . $q . '%');
				});
				$User->limit(5);
				$User = $User->get();

				if (count($User) > 0) {
					foreach ($User as $User_key => $User_value) {
						$UserResponse[$User_key]['id'] = $User_value['id'];
						$UserResponse[$User_key]['text'] = $User_value['full_name'];
					}
				}
			} else if ($isChannelPartner != 0 && ($request->source_type == 301 || $request->source_type == 302)) {

				$UserResponse = array();
				$q = $request->q;
				$User = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
				$User->where('users.type', $request->source_type);
				$User->leftJoin('electrician', 'electrician.user_id', '=', 'users.id');
				$User->where('users.status', 1);
				//$User->whereIn('electrician.sale_person_id', $childSalePersonsIds);
				$User->where('users.city_id', Auth::user()->city_id);
				//$User->where('users.city_id', Auth::user()->city_id);
				$User->where(function ($query) use ($q) {

					$query->WhereRaw('CONCAT(users.first_name," ",users.last_name) like ?', [$q]);
					$query->orWhereRaw('CONCAT(users.first_name," ",users.last_name) like ? ', ["%" . $q . "%"]);

					// $query->where('users.first_name', 'like', '%' . $q . '%');
					// $query->orWhere('users.last_name', 'like', '%' . $q . '%');
				});
				$User->limit(5);
				$User = $User->get();

				if (count($User) > 0) {
					foreach ($User as $User_key => $User_value) {
						$UserResponse[$User_key]['id'] = $User_value['id'];
						$UserResponse[$User_key]['text'] = $User_value['full_name'];
					}
				}
			} else if ($isSalePerson == 1 && ($request->source_type == 201 || $request->source_type == 202)) {

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
				$User->where('users.status', 1);
				$User->where('users.type', $request->source_type);
				$User->leftJoin('architect', 'architect.user_id', '=', 'users.id');
				$User->whereIn('users.city_id', $cities);

				//$User->whereIn('architect.sale_person_id', $childSalePersonsIds);

				$User->where(function ($query) use ($q) {

					$query->WhereRaw('CONCAT(users.first_name," ",users.last_name) like ?', [$q]);
					$query->orWhereRaw('CONCAT(users.first_name," ",users.last_name) like ? ', ["%" . $q . "%"]);

					// $query->where('users.first_name', 'like', '%' . $q . '%');
					// $query->orWhere('users.last_name', 'like', '%' . $q . '%');
				});
				$User->limit(5);
				$User = $User->get();

				if (count($User) > 0) {
					foreach ($User as $User_key => $User_value) {
						$UserResponse[$User_key]['id'] = $User_value['id'];
						$UserResponse[$User_key]['text'] = $User_value['full_name'];
					}
				}
			} else if ($isSalePerson == 1 && ($request->source_type == 301 || $request->source_type == 302)) {

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

					$query->WhereRaw('CONCAT(users.first_name," ",users.last_name) like ?', [$q]);
					$query->orWhereRaw('CONCAT(users.first_name," ",users.last_name) like ? ', ["%" . $q . "%"]);

					// $query->where('users.first_name', 'like', '%' . $q . '%');
					// $query->orWhere('users.last_name', 'like', '%' . $q . '%');
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

				if ($isAdminOrCompanyAdmin == 1) {

					$UserKey = count($UserResponse);
					$UserResponse[$UserKey]['id'] = 0;
					$UserResponse[$UserKey]['text'] = "None";
				}
			} else if ($request->source_type == 4) {

				$UserResponse[0]['id'] = "Meta Ads";
				$UserResponse[0]['text'] = "Meta Ads";
				$UserResponse[1]['id'] = "Others";
				$UserResponse[1]['text'] = "Others";
			} else {

				$UserResponse = array();
				$q = $request->q;
				$User = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
				$User->where('users.status', 1);
				$User->where('users.type', $request->source_type);
				$User->where(function ($query) use ($q) {

					$query->WhereRaw('CONCAT(users.first_name," ",users.last_name) like ?', [$q]);
					$query->orWhereRaw('CONCAT(users.first_name," ",users.last_name) like ? ', ["%" . $q . "%"]);

					// $query->where('users.first_name', 'like', '%' . $q . '%');
					// $query->orWhere('users.last_name', 'like', '%' . $q . '%');
				});
				$User->limit(5);
				$User = $User->get();

				if (count($User) > 0) {
					foreach ($User as $User_key => $User_value) {
						$UserResponse[$User_key]['id'] = $User_value['id'];
						$UserResponse[$User_key]['text'] = $User_value['full_name'];
					}
				}

				if ($isAdminOrCompanyAdmin == 1) {

					$UserKey = count($UserResponse);
					$UserResponse[$UserKey]['id'] = 0;
					$UserResponse[$UserKey]['text'] = "None";
				}
			}
		}

		$response = array();
		$response['results'] = $UserResponse;
		$response['pagination']['more'] = false;
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function searchArchitect(Request $request)
	{

		$isSalePerson = isSalePerson();
		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		$isChannelPartner = isChannelPartner(Auth::user()->type);

		if (isset($request->user_id) && $request->user_id != "") {

			$User = User::query();
			$User->select('users.id', 'users.first_name', 'users.last_name', 'users.phone_number');
			$User->whereIn('users.type', array(201, 202));
			$User->where('users.id', $request->user_id);
			$User->where('users.status', 1);
			$User->limit(1);
			$UserResponse = $User->get();
		} else {

			$salePerson = SalePerson::select('cities')->where('user_id', Auth::user()->id)->first();
			$cities = array();
			if ($salePerson) {

				$cities = explode(",", $salePerson->cities);
			} else {
				$cities = array(0);
			}

			$UserResponse = array();
			$q = $request->q;
			$User = User::select('users.id', 'users.phone_number', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
			$User->where('users.status', 1);
			$User->whereIn('users.type', array(201, 202));
			$User->leftJoin('architect', 'architect.user_id', '=', 'users.id');
			if ($isSalePerson == 1) {
				$User->whereIn('users.city_id', $cities);
			} else if ($isChannelPartner != 0) {
				$User->where('users.city_id', Auth::user()->city_id);
			}

			//$User->whereIn('architect.sale_person_id', $childSalePersonsIds);

			// $User->where(function ($query) use ($q) {
			// 	$query->where('users.first_name', 'like', '%' . $q . '%');
			// 	$query->orWhere('users.last_name', 'like', '%' . $q . '%');
			// });
			$User->where(function ($query) use ($q) {

				$query->WhereRaw('CONCAT(users.first_name," ",users.last_name) like ?', [$q]);
				$query->orWhereRaw('CONCAT(users.first_name," ",users.last_name) like ? ', ["%" . $q . "%"]);
				$query->orWhereRaw('users.phone_number like ? ', ["%" . $q . "%"]);

				// $query->where('users.first_name', 'like', '%' . $q . '%');
				// $query->orWhere('users.last_name', 'like', '%' . $q . '%');
			});
			$User->limit(5);
			$User = $User->get();

			if (count($User) > 0) {
				foreach ($User as $User_key => $User_value) {
					$UserResponse[$User_key]['id'] = $User_value['id'];
					$UserResponse[$User_key]['text'] = $User_value['full_name'] . " - " . $User_value['phone_number'];
				}
			}

			if ($isAdminOrCompanyAdmin == 1) {

				$UserKey = count($UserResponse);
				$UserResponse[$UserKey]['id'] = 0;
				$UserResponse[$UserKey]['text'] = "None";
			}
		}

		$response = array();
		$response['results'] = $UserResponse;
		$response['pagination']['more'] = false;
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function searchChannelPartner(Request $request)
	{

		$isSalePerson = isSalePerson();
		$q = $request->q;
		$User = User::select('users.id', DB::raw("channel_partner.firm_name"), 'users.first_name', 'users.last_name');
		$User->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
		$User->where('users.status', 1);
		$User->whereIn('users.type', array(101, 102, 103, 104, 105));
		if ($isSalePerson == 1) {

			$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);

			$salePerson = SalePerson::select('cities')->where('user_id', Auth::user()->id)->first();
			$cities = array();
			if ($salePerson) {

				$cities = explode(",", $salePerson->cities);
			} else {
				$cities = array(0);
			}

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
			$query->orWhere('users.first_name', 'like', '%' . $q . '%');
			$query->orWhere('users.last_name', 'like', '%' . $q . '%');
			$query->orWhere('users.phone_number', 'like', '%' . $q . '%');
		});
		$User->limit(5);
		$User = $User->get();

		$UserResponse = array();

		if (count($User) > 0) {
			foreach ($User as $User_key => $User_value) {
				$UserResponse[$User_key]['id'] = $User_value['id'];
				$UserResponse[$User_key]['text'] = $User_value['firm_name'] . " (" . $User_value['first_name'] . " " . $User_value['last_name'] . ")";
			}
		}

		$response = array();
		$response['results'] = $UserResponse;
		$response['pagination']['more'] = false;

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function searchElectrician(Request $request)
	{

		$isSalePerson = isSalePerson();
		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		$isThirdPartyUser = isThirdPartyUser();
		$isChannelPartner = isChannelPartner(Auth::user()->type);

		if (isset($request->user_id) && $request->user_id != "") {

			$User = User::query();
			$User->select('users.id', 'users.first_name', 'users.last_name', 'users.phone_number');
			$User->whereIn('users.type', array(301, 302));
			if ($isSalePerson == 1) {
				$User->leftJoin('architect', 'architect.user_id', '=', 'users.id');
				$User->leftJoin('electrician', 'electrician.user_id', '=', 'users.id');
			} else if ($isChannelPartner != 0) {
				$User->where('users.city_id', Auth::user()->city_id);
			}

			$User->where('users.id', $request->user_id);
			$User->where('users.status', 1);
			$User->limit(1);
			$UserResponse = $User->get();
		} else {

			$salePerson = SalePerson::select('cities')->where('user_id', Auth::user()->id)->first();
			$cities = array();
			if ($salePerson) {

				$cities = explode(",", $salePerson->cities);
			} else {
				$cities = array(0);
			}

			$UserResponse = array();
			$q = $request->q;
			$User = User::select('users.id', 'users.phone_number', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
			$User->where('users.status', 1);
			$User->whereIn('users.type', array(301, 302));
			$User->leftJoin('architect', 'architect.user_id', '=', 'users.id');
			if ($isSalePerson == 1) {
				$User->whereIn('users.city_id', $cities);
			} else if ($isChannelPartner != 0) {
				$User->where('users.city_id', Auth::user()->city_id);
			}

			//$User->whereIn('architect.sale_person_id', $childSalePersonsIds);

			$User->where(function ($query) use ($q) {

				$query->WhereRaw('CONCAT(users.first_name," ",users.last_name) like ?', [$q]);
				$query->orWhereRaw('CONCAT(users.first_name," ",users.last_name) like ? ', ["%" . $q . "%"]);
				$query->orWhereRaw('users.phone_number like ? ', ["%" . $q . "%"]);

				// $query->where('users.first_name', 'like', '%' . $q . '%');
				// $query->orWhere('users.last_name', 'like', '%' . $q . '%');
			});
			$User->limit(5);
			$User = $User->get();

			if (count($User) > 0) {
				foreach ($User as $User_key => $User_value) {
					$UserResponse[$User_key]['id'] = $User_value['id'];
					$UserResponse[$User_key]['text'] = $User_value['full_name'] . " - " . $User_value['phone_number'];
				}
			}

			if ($isAdminOrCompanyAdmin == 1) {

				$UserKey = count($UserResponse);
				$UserResponse[$UserKey]['id'] = 0;
				$UserResponse[$UserKey]['text'] = "None";
			}
		}

		$response = array();
		$response['results'] = $UserResponse;
		$response['pagination']['more'] = false;
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function assignedUser(Request $request)
	{

		$inquiry = Inquiry::select('assigned_to')->find($request->inquiry_id);
		if ($inquiry) {

			$User = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
			// $User->where('users.status', 1);
			$User->where('id', $inquiry->assigned_to);
			$User->limit(1);
			$User = $User->get();
			if (count($User) > 0) {
				$response = successRes("");
				$response['data'] = $User;
			} else {
				$response = errorRes("");
			}
		} else {
			$response = errorRes("Invalid inquiry");
		}

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function searchAssignedUser(Request $request)
	{

		$isSalePerson = isSalePerson();
		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		$isThirdPartyUser = isThirdPartyUser();
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

	public function saveBilling(Request $request)
	{

		$isSalePerson = isSalePerson();
		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		$isThirdPartyUser = isThirdPartyUser();
		$isChannelPartner = isChannelPartner(Auth::user()->type);
		$isTaleSalesUser = isTaleSalesUser();
		$TaleSalesCities = TeleSalesCity(Auth::user()->id);

		$rules = array();
		$rules['billing_inquiry_id'] = "required";
		// if ($isAdminOrCompanyAdmin == 1) {
		// 	$rules['inquiry_billing_amount'] = "required";
		// }
		//$rules['inquiry_billing_invoice.*'] = "required|mimes:png,jpg,jpeg,csv,txt,xlx,xls,xlsx,pdf,application/octet-stream";
		$customMessage = array();
		$customMessage['billing_inquiry_id.required'] = "Invalid parameters";
		$customMessage['inquiry_billing_amount.required'] = "Please enter billing amount";
		$customMessage['inquiry_billing_invoice.*.*'] = "Please attach valid (png,jpg,jpeg,csv,txt,xlx,xls,xlsx,pdf) billing invoice";

		// $customMessage['inquiry_quotation.required'] = "Please attach valid quotation file";

		$validator = Validator::make($request->all(), $rules, $customMessage);

		if ($validator->fails()) {

			$response = errorRes("The request could not be understood by the server due to malformed syntax");
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();
		} else {

			$Inquiry = Inquiry::find($request->billing_inquiry_id);
			if ($Inquiry) {
				$isSalePerson = isSalePerson();
				$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
				if ($isAdminOrCompanyAdmin == 1 || $isThirdPartyUser == 1 || $isSalePerson == 1 || ($isChannelPartner != 0) || ($isTaleSalesUser == 1)) {

					if ($isSalePerson == 1) {

						$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
						if (!in_array($Inquiry->assigned_to, $childSalePersonsIds)) {

							$response = errorRes("Invalid access");
							return response()->json($response)->header('Content-Type', 'application/json');
						}
					} else if ($isChannelPartner != 0) {

						if ($Inquiry->source_type_value != Auth::user()->id) {

							$response = errorRes("Invalid access");
							return response()->json($response)->header('Content-Type', 'application/json');
						}
					} else if ($isTaleSalesUser == 1) {

						if (!in_array($Inquiry->city_id, $TaleSalesCities)) {

							$response = errorRes("Invalid access");
							return response()->json($response)->header('Content-Type', 'application/json');
						}
					}

					$uploadedFile1 = "";

					if ($request->hasFile('inquiry_billing_invoice')) {

						$folderPathImage = '/s/question-attachment';
						$question_attachment_file_name = array();

						foreach ($request->file('inquiry_billing_invoice') as $key => $value) {

							$question_attachment = $request->file('inquiry_billing_invoice')[$key];
							$extension = $question_attachment->getClientOriginalExtension();

							$question_attachment_file_name_temp = time() . mt_rand(10000, 99999) . '.' . $extension;

							$destinationPath = public_path('/s/question-attachment');
							$question_attachment->move($destinationPath, $question_attachment_file_name_temp);

							if (!File::exists('s/question-attachment/' . $question_attachment_file_name_temp)) {

								$question_attachment_file_name_temp = "";
							} else {

								$question_attachment_file_name_temp = '/s/question-attachment/' . $question_attachment_file_name_temp;

								$spaceUploadResponse = uploadFileOnSpaces(public_path($question_attachment_file_name_temp), $question_attachment_file_name_temp);
								if ($spaceUploadResponse != 1) {
									$question_attachment_file_name_temp = "";
								} else {
									unlink(public_path($question_attachment_file_name_temp));
									$question_attachment_file_name[] = $question_attachment_file_name_temp;
								}
							}
						}

						if (count($question_attachment_file_name) > 0) {
							$uploadedFile1 = implode(",", $question_attachment_file_name);
						}

						// $fileObject1 = $request->file('inquiry_billing_invoice');

						// $extension = $fileObject1->getClientOriginalExtension();
						// $fileName1 = time() . mt_rand(10000, 99999) . '.' . $extension;

						// $destinationPath = public_path($folderPathImage);
						// $supportedExtension = array("png", "jpg", "jpeg", "csv", "txt", "xlx", "xls", "pdf");

						// if (in_array($extension, $supportedExtension)) {

						// 	$fileObject1->move($destinationPath, $fileName1);

						// 	if (File::exists(public_path($folderPathImage . "/" . $fileName1))) {

						// 		$uploadedFile1 = "s/question-attachment/" . $fileName1;

						// 	}

						// }

					}

					if ($uploadedFile1 != "") {
						$Inquiry->billing_invoice = $uploadedFile1;
					}

					$Inquiry->save();

					$InquiryQuestionAnswer = InquiryQuestionAnswer::where('inquiry_id', $Inquiry->id)->where('inquiry_question_id', 11)->first();

					if ($InquiryQuestionAnswer) {
						$InquiryQuestionAnswer->user_id = Auth::user()->id;
						$InquiryQuestionAnswer->answer = $uploadedFile1;
						$InquiryQuestionAnswer->save();
					} else {

						// $InquiryQuestionAnswer = new InquiryQuestionAnswer();
						// $InquiryQuestionAnswer->inquiry_id = $Inquiry->id;
						// $InquiryQuestionAnswer->user_id = Auth::user()->id;
						// $InquiryQuestionAnswer->answer = $uploadedFile1;
						// $InquiryQuestionAnswer->inquiry_question_id = 11;
						// $InquiryQuestionAnswer->save();
					}

					if ($isAdminOrCompanyAdmin == 1 && isset($request->inquiry_billing_amount) && $request->inquiry_billing_amount != "") {

						$Inquiry->billing_amount = $request->inquiry_billing_amount;
						$Inquiry->save();

						$InquiryQuestionAnswer = InquiryQuestionAnswer::where('inquiry_id', $Inquiry->id)->where('inquiry_question_id', 12)->first();

						if ($InquiryQuestionAnswer) {
							$InquiryQuestionAnswer->user_id = Auth::user()->id;
							$InquiryQuestionAnswer->answer = $request->inquiry_billing_amount;
							$InquiryQuestionAnswer->save();
						} else {

							// $InquiryQuestionAnswer = new InquiryQuestionAnswer();
							// $InquiryQuestionAnswer->inquiry_id = $Inquiry->id;
							// $InquiryQuestionAnswer->user_id = Auth::user()->id;
							// $InquiryQuestionAnswer->answer = $request->inquiry_billing_amount;
							// $InquiryQuestionAnswer->inquiry_question_id = 12;
							// $InquiryQuestionAnswer->save();
						}
					}

					$debugLog = array();
					$debugLog['inquiry_id'] = $Inquiry->id;
					$debugLog['name'] = "billing-invoice";
					$debugLog['description'] = "Billing invoice updated";
					saveInquiryLog($debugLog);
					$response = successRes("Successfully updated billing invoice");
				} else {
					$response = errorRes("Invalid access");
				}
			} else {
				$response = errorRes("Invalid parameters");
			}
		}

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function saveQuotation(Request $request)
	{

		$isSalePerson = isSalePerson();
		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		$isThirdPartyUser = isThirdPartyUser();
		$isChannelPartner = isChannelPartner(Auth::user()->type);
		$isTaleSalesUser = isTaleSalesUser();
		$TaleSalesCities = TeleSalesCity(Auth::user()->id);

		$rules = array();
		$rules['quotation_inquiry_id'] = "required";
		//$rules['inquiry_quotation'] = "required";
		$rules['inquiry_quotation_amount'] = "required";

		$customMessage = array();
		$customMessage['quotation_inquiry_id.required'] = "Invalid parameters";
		// $customMessage['inquiry_quotation.required'] = "Please attach valid quotation file";

		$validator = Validator::make($request->all(), $rules, $customMessage);

		if ($validator->fails()) {

			$response = errorRes("The request could not be understood by the server due to malformed syntax");
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();
		} else {

			$Inquiry = Inquiry::find($request->quotation_inquiry_id);
			if ($Inquiry) {

				if ($isAdminOrCompanyAdmin == 1 || $isSalePerson == 1 || $isChannelPartner != 0) {

					if ($isSalePerson == 1) {

						$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
						if (!in_array($Inquiry->assigned_to, $childSalePersonsIds)) {

							$response = errorRes("Invalid access");
							return response()->json($response)->header('Content-Type', 'application/json');
						}
					} else if ($isChannelPartner != 0) {

						if ($Inquiry->source_type_value != Auth::user()->id) {

							$response = errorRes("Invalid access");
							return response()->json($response)->header('Content-Type', 'application/json');
						}
					} else if ($isThirdPartyUser == 1) {

						if ($Inquiry->source_type_value != Auth::user()->id) {

							$response = errorRes("Invalid access");
							return response()->json($response)->header('Content-Type', 'application/json');
						}
					} else if ($isTaleSalesUser == 1) {

						if (!in_array($Inquiry->city_id, $TaleSalesCities)) {

							$response = errorRes("Invalid access");
							return response()->json($response)->header('Content-Type', 'application/json');
						}
					}

					$uploadedFile1 = "";

					if ($request->hasFile('inquiry_quotation')) {

						$folderPathImage = '/s/question-attachment';
						$fileObject1 = $request->file('inquiry_quotation');

						$extension = $fileObject1->getClientOriginalExtension();
						$fileName1 = time() . mt_rand(10000, 99999) . '.' . $extension;

						$destinationPath = public_path($folderPathImage);
						$supportedExtension = array("png", "jpg", "jpeg", "csv", "txt", "xlx", "xls", "pdf");

						if (in_array($extension, $supportedExtension)) {

							$fileObject1->move($destinationPath, $fileName1);

							if (File::exists(public_path($folderPathImage . "/" . $fileName1))) {

								$uploadedFile1 = "/s/question-attachment/" . $fileName1;

								$spaceUploadResponse = uploadFileOnSpaces(public_path($uploadedFile1), $uploadedFile1);
								if ($spaceUploadResponse != 1) {
									$uploadedFile1 = "";
								} else {
									unlink(public_path($uploadedFile1));
								}
							}
						}
					}

					//if ($uploadedFile1 == "") {

					//$response = errorRes("Please attach file (png,jpg,jpeg,csv,txt,xlx,xls,pdf)");
					//return response()->json($response)->header('Content-Type', 'application/json');

					//}

					if ($uploadedFile1 != "") {
						$Inquiry->quotation = $uploadedFile1;
					}

					$Inquiry->quotation_amount = $request->inquiry_quotation_amount;
					$Inquiry->save();

					$InquiryQuestionAnswer = InquiryQuestionAnswer::where('inquiry_id', $Inquiry->id)->where('inquiry_question_id', 1)->first();

					if ($InquiryQuestionAnswer) {
						$InquiryQuestionAnswer->answer = $uploadedFile1;
						$InquiryQuestionAnswer->save();
					}

					$debugLog = array();
					$debugLog['inquiry_id'] = $Inquiry->id;
					$debugLog['name'] = "quotation";
					$debugLog['description'] = "Quotation updated to " . $Inquiry->quotation_amount;
					saveInquiryLog($debugLog);
					$response = successRes("Successfully updated quotation");
				} else {
					$response = errorRes("Invalid access");
				}
			} else {
				$response = errorRes("Invalid parameters");
			}
		}

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function saveAssignedTo(Request $request)
	{

		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		$isChannelPartner = isChannelPartner(Auth::user()->type);
		$isSalePerson = isSalePerson();
		$isThirdPartyUser = isThirdPartyUser();
		$isTaleSalesUser = isTaleSalesUser();
		$TaleSalesCities = array();
		$childSalePersonsIds = array();

		if ($isTaleSalesUser == 1) {
			$TaleSalesCities = TeleSalesCity(Auth::user()->id);
		}

		if ($isSalePerson == 1) {
			$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
		}

		$validator = Validator::make($request->all(), [

			'inquiry_change_assigned_to' => ['required'],
			'assigned_to_inquiry_id' => ['required'],

		]);

		if ($validator->fails()) {
			$response = array();
			$response['status'] = 0;
			$response['msg'] = "The request could not be understood by the server due to malformed syntax";
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();

			return redirect()->back()->with("error", "Something went wrong with validation");
		} else {

			$User = User::select('users.id', 'users.first_name', 'users.last_name');
			if ($isAdminOrCompanyAdmin == 1) {
				//$User->whereIn('users.type', array(0, 1));
			} else if ($isThirdPartyUser == 1) {
				$User->whereIn('users.type', array(2));
			} else if ($isSalePerson == 1) {
				$User->where('users.type', 2);
				$User->whereIn('id', $childSalePersonsIds);
			} else if ($isChannelPartner != 0) {
				$channelPartnersSalesPersons = getChannelPartnerSalesPersonsIds(Auth::user()->id);
				$User->where('users.type', 2);
				$User->whereIn('users.id', $channelPartnersSalesPersons);
			}
			$User->where('id', $request->inquiry_change_assigned_to);
			$User = $User->first();

			if ($User) {

				$Inquiry = Inquiry::find($request->assigned_to_inquiry_id);
				if ($Inquiry) {
					if ($isAdminOrCompanyAdmin == 1 || ($isThirdPartyUser == 1 && $Inquiry->source_type_value == Auth::user()->id) || ($isSalePerson == 1 && in_array($Inquiry->assigned_to, $childSalePersonsIds)) || ($isChannelPartner != 0 && $Inquiry->source_type_value == Auth::user()->id) || ($isTaleSalesUser == 1 || in_array($Inquiry->city_id, $TaleSalesCities))) {

						$assignedToName = $User->first_name . " " . $User->last_name;
						// $Inquiry = Inquiry::find($request->assigned_to_inquiry_id);
						$Inquiry->assigned_to = $User->id;
						$Inquiry->save();

						$UserNotification = UserNotification::where('inquiry_id', $Inquiry->id)->where('type', 3)->first();
						if ($UserNotification) {
							$UserNotification->delete();
						}

						$UserNotify = array();
						$UserNotify['user_id'] = $Inquiry->assigned_to;
						$UserNotify['type'] = 3;
						$UserNotify['from_user_id'] = Auth::user()->id;
						$UserNotify['title'] = "Inquiry #" . $Inquiry->id . " (" . $Inquiry->first_name . " " . $Inquiry->last_name . ")assigned to you";
						$UserNotify['description'] = "Inquiry #" . $Inquiry->id . " (" . $Inquiry->first_name . " " . $Inquiry->last_name . ") Assigned to you, Please followup inquiry";
						$UserNotify['inquiry_id'] = $Inquiry->id;
						saveUserNotification($UserNotify);

						$debugLog = array();
						$debugLog['inquiry_id'] = $Inquiry->id;
						$debugLog['name'] = "assigned_to";
						$debugLog['description'] = "Assigned to " . $assignedToName;
						saveInquiryLog($debugLog);

						$response = successRes("Successfully updated assigned to");
					} else {
						$response = errorRes("Invalid access");
					}
				} else {
					$response = errorRes("Invalid access");
				}
			} else {
				$response = errorRes("Invalid assigned to");
			}
		}

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function saveStageOfSite(Request $request)
	{

		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		$isChannelPartner = isChannelPartner(Auth::user()->type);
		$isSalePerson = isSalePerson();
		$isThirdPartyUser = isThirdPartyUser();
		$isTaleSalesUser = isTaleSalesUser();
		$TaleSalesCities = array();
		$childSalePersonsIds = array();

		if ($isTaleSalesUser == 1) {
			$TaleSalesCities = TeleSalesCity(Auth::user()->id);
		}

		if ($isSalePerson == 1) {
			$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
		}

		$rules = array();
		$rules['inquiry_id'] = 'required';
		$rules['stage_of_site'] = 'required';

		$messages = array();
		$messages['inquiry_id.required'] = 'Invalid parameters';
		$messages['stage_of_site.required'] = 'Invalid stage of site';

		$validator = Validator::make($request->all(), $rules, $messages);
		if ($validator->fails()) {
			$response = errorRes("The request could not be understood by the server due to malformed syntax");
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();
		} else {

			$Inquiry = Inquiry::select('id', 'stage_of_site', 'assigned_to', 'source_type_value')->find($request->inquiry_id);
			if ($Inquiry) {

				if ($isAdminOrCompanyAdmin == 1 || ($isThirdPartyUser == 1 && $Inquiry->source_type_value == Auth::user()->id) || ($isSalePerson == 1 && in_array($Inquiry->assigned_to, $childSalePersonsIds)) || ($isChannelPartner != 0 && $Inquiry->source_type_value == Auth::user()->id) || ($isTaleSalesUser == 1 || in_array($Inquiry->city_id, $TaleSalesCities))) {

					$oldStageOfSite = $Inquiry->stage_of_site;
					if ($oldStageOfSite == "") {
						$oldStageOfSite = "not selected";
					}

					$childSalePersonsIds = array();

					if ($isSalePerson == 1) {

						$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
					}
					//$query->where('inquiry.source_type_value', Auth::user()->id);

					if ($isAdminOrCompanyAdmin == 1 || ($isThirdPartyUser == 1 && $Inquiry->source_type_value == Auth::user()->id) || ($isSalePerson == 1 && in_array($Inquiry->assigned_to, $childSalePersonsIds)) || ($isChannelPartner != 0 && $Inquiry->source_type_value == Auth::user()->id) || ($isTaleSalesUser == 1 || in_array($Inquiry->city_id, $TaleSalesCities))) {

						$InquiryQuestionOption = InquiryQuestionOption::select('id', 'option')->where('inquiry_question_id', 7)->where('id', $request->stage_of_site)->orderBy('id', 'asc')->first();

						if ($InquiryQuestionOption) {

							$stageOfSite = $InquiryQuestionOption->option;
							$stageOfSiteId = $InquiryQuestionOption->id;

							$Inquiry->stage_of_site_date_time = date('Y-m-d H:i:s');
							$Inquiry->stage_of_site = $stageOfSite;
							$Inquiry->save();

							$InquiryQuestionAnswer = InquiryQuestionAnswer::where('inquiry_id', $Inquiry->id)->where('inquiry_question_id', 7)->first();

							if ($InquiryQuestionAnswer) {

								$InquiryQuestionAnswer->answer = $stageOfSiteId;
								$InquiryQuestionAnswer->save();
							}

							$response = successRes("Successfully updated stage of site");
							$debugLog = array();
							$debugLog['inquiry_id'] = $Inquiry->id;
							$debugLog['name'] = "stage-of-site";
							$debugLog['description'] = "stage of site updated from " . $oldStageOfSite . " to " . $stageOfSite;
							saveInquiryLog($debugLog);
						} else {

							$response = errorRes("Invalid stage of site");
						}
					} else {

						$response = errorRes("Invalid access of inquiry");
					}
				} else {
					$response = errorRes("Invalid access");
				}
			} else {

				$response = errorRes("Invalid access");
			}
		}

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function saveFollowupType(Request $request)
	{

		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		$isChannelPartner = isChannelPartner(Auth::user()->type);
		$isSalePerson = isSalePerson();
		$isThirdPartyUser = isThirdPartyUser();
		$isTaleSalesUser = isTaleSalesUser();
		$TaleSalesCities = array();
		$childSalePersonsIds = array();

		if ($isTaleSalesUser == 1) {
			$TaleSalesCities = TeleSalesCity(Auth::user()->id);
		}

		if ($isSalePerson == 1) {
			$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
		}

		$rules = array();
		$rules['inquiry_id'] = 'required';
		$rules['follow_up_type'] = 'required';

		$messages = array();
		$messages['inquiry_id.required'] = 'Invalid parameters';
		$messages['follow_up_type.required'] = 'Invalid follow up type';

		$validator = Validator::make($request->all(), $rules, $messages);
		if ($validator->fails()) {
			$response = errorRes("The request could not be understood by the server due to malformed syntax");
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();
		} else {

			$Inquiry = Inquiry::select('id', 'follow_up_type', 'assigned_to', 'source_type_value')->find($request->inquiry_id);
			if ($Inquiry) {

				if ($isAdminOrCompanyAdmin == 1 || ($isThirdPartyUser == 1 && $Inquiry->source_type_value == Auth::user()->id) || ($isSalePerson == 1 && in_array($Inquiry->assigned_to, $childSalePersonsIds)) || ($isChannelPartner != 0 && $Inquiry->source_type_value == Auth::user()->id) || ($isTaleSalesUser == 1 || in_array($Inquiry->city_id, $TaleSalesCities))) {

					$oldFollowType = $Inquiry->follow_up_type;
					if ($oldFollowType == "") {
						$oldFollowType = "Not selected";
					}

					$childSalePersonsIds = array();

					if ($isSalePerson == 1) {

						$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
					}
					//$query->where('inquiry.source_type_value', Auth::user()->id);

					if ($isAdminOrCompanyAdmin == 1 || ($isThirdPartyUser == 1 && $Inquiry->source_type_value == Auth::user()->id) || ($isSalePerson == 1 && in_array($Inquiry->assigned_to, $childSalePersonsIds)) || ($isChannelPartner != 0 && $Inquiry->source_type_value == Auth::user()->id) || ($isTaleSalesUser == 1 || in_array($Inquiry->city_id, $TaleSalesCities))) {

						if ($request->follow_up_type == "Call" || $request->follow_up_type == "Meeting") {

							$Inquiry->follow_up_type = $request->follow_up_type;
							$Inquiry->save();
							$response = successRes("Successfully updated follow up type");
							$debugLog = array();
							$debugLog['inquiry_id'] = $Inquiry->id;
							$debugLog['name'] = "stage-of-site";
							$debugLog['description'] = "follow up type updated from " . $oldFollowType . " to " . $request->follow_up_type;

							saveInquiryLog($debugLog);
						} else {

							$response = errorRes("Invalid stage of site");
						}
					} else {

						$response = errorRes("Invalid access of inquiry");
					}
				} else {
					$response = errorRes("Invalid access");
				}
			} else {

				$response = errorRes("Invalid access");
			}
		}

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function saveFollowUpDateTime(Request $request)
	{

		$isSalePerson = isSalePerson();
		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		$isThirdPartyUser = isThirdPartyUser();
		$isChannelPartner = isChannelPartner(Auth::user()->type);
		$isTaleSalesUser = isTaleSalesUser();
		$TaleSalesCities = TeleSalesCity(Auth::user()->id);

		$validator = Validator::make($request->all(), [

			'inquiry_id' => ['required'],
			'follow_up_date' => ['required'],
			'follow_up_time' => ['required'],

		]);

		if ($validator->fails()) {
			$response = array();
			$response['status'] = 0;
			$response['msg'] = "The request could not be understood by the server due to malformed syntax";
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();

			return redirect()->back()->with("error", "Something went wrong with validation");
		} else {

			$Inquiry = Inquiry::select('id', 'assigned_to', 'follow_up_date_time', 'city_id', 'source_type_value')->find($request->inquiry_id);
			if ($Inquiry) {

				$assigned_to = $Inquiry->assigned_to;

				if ($isSalePerson == 1) {
					$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
				}

				if ($isAdminOrCompanyAdmin == 1 || ($isThirdPartyUser == 1) || ($isSalePerson == 1 && (in_array($assigned_to, $childSalePersonsIds))) || ($isChannelPartner != 0 && $Inquiry->source_type_value == Auth::user()->id) || ($isTaleSalesUser == 1 && in_array($Inquiry->city_id, $TaleSalesCities))) {

					$Inquiry->follow_up_date_time = date('Y-m-d H:i:s', strtotime($request->follow_up_date . " " . $request->follow_up_time));
					$Inquiry->save();
					$response = successRes("Successfully updated follow up date & time");

					$Inquiry = Inquiry::select('id', 'follow_up_date_time')->find($request->inquiry_id);

					$debugLog = array();
					$debugLog['inquiry_id'] = $Inquiry->id;
					$debugLog['name'] = "follow-up-date-time";
					$debugLog['description'] = "Follow up date & time " . date('Y/m/d h:i:s A', strtotime($Inquiry->follow_up_date_time));

					saveInquiryLog($debugLog);
				} else {
					$response = errorRes("Invalid inquiry");
				}
			} else {
				$response = errorRes("Invalid inquiry");
			}
		}

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function saveClosingDateTime(Request $request)
	{

		$isSalePerson = isSalePerson();
		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		$isThirdPartyUser = isThirdPartyUser();
		$isChannelPartner = isChannelPartner(Auth::user()->type);

		$isTaleSalesUser = isTaleSalesUser();
		$TaleSalesCities = TeleSalesCity(Auth::user()->id);

		$validator = Validator::make($request->all(), [

			'inquiry_id' => ['required'],
			'closing_date' => ['required'],

		]);

		if ($validator->fails()) {
			$response = array();
			$response['status'] = 0;
			$response['msg'] = "The request could not be understood by the server due to malformed syntax";
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();
		} else {

			$Inquiry = Inquiry::select('id', 'assigned_to', 'closing_date_time', 'city_id')->find($request->inquiry_id);
			if ($Inquiry) {

				$assigned_to = $Inquiry->assigned_to;

				if ($isSalePerson == 1) {
					$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
				}

				if ($isAdminOrCompanyAdmin == 1 || ($isThirdPartyUser == 1 && $Inquiry->source_type_value == Auth::user()->id) || ($isSalePerson == 1 && (in_array($assigned_to, $childSalePersonsIds))) || ($isChannelPartner != 0 && $Inquiry->source_type_value == Auth::user()->id) || ($isTaleSalesUser == 1 && in_array($Inquiry->city_id, $TaleSalesCities))) {

					$Inquiry->closing_date_time = date('Y-m-d H:i:s', strtotime($request->closing_date . " 23:59:59"));
					$Inquiry->save();
					$response = successRes("Successfully updated closing date & time");

					$Inquiry = Inquiry::select('id', 'closing_date_time', 'closing_history')->find($request->inquiry_id);

					$debugLog = array();
					$debugLog['inquiry_id'] = $Inquiry->id;
					$debugLog['name'] = "closing-date-time";
					$debugLog['description'] = "Closing date & time " . date('Y/m/d h:i:s A', strtotime($Inquiry->closing_date_time));
					saveInquiryLog($debugLog);

					$closingHistory = array();
					if ($Inquiry->closing_history != "") {
						$closingHistory = json_decode($Inquiry->closing_history, true);
					}

					$nClosingHistory = count($closingHistory);
					$closingHistory[$nClosingHistory]['closing_date_time'] = $Inquiry->closing_date_time;
					$closingHistory[$nClosingHistory]['created_at'] = date('Y-m-d H:i:s');

					$Inquiry->closing_history = json_encode($closingHistory);
					$Inquiry->save();
					//$response['debug'] = $closingHistory;

				} else {
					$response = errorRes("Invalid inquiry");
				}
			} else {
				$response = errorRes("Invalid inquiry");
			}
		}

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function saveInquiryAnswer(Request $request)
	{

		$isSalePerson = isSalePerson();
		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		$isThirdPartyUser = isThirdPartyUser();
		$isChannelPartner = isChannelPartner(Auth::user()->type);

		$isTaleSalesUser = isTaleSalesUser();
		$TaleSalesCities = TeleSalesCity(Auth::user()->id);

		if ($isSalePerson == 1) {
			$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
		}

		$Inquiry = Inquiry::find($request->inquiry_id);
		if ($Inquiry) {

			if ($isAdminOrCompanyAdmin == 1 || ($isSalePerson == 1 && (in_array($Inquiry->assigned_to, $childSalePersonsIds))) || ($isChannelPartner != 0 && $Inquiry->source_type_value == Auth::user()->id) || ($isTaleSalesUser == 1 && in_array($Inquiry->city_id, $TaleSalesCities))) {

				$inquiryStatus = getInquiryStatus();
				$currentStatus = $Inquiry->status;
				$newStatus = $request->inquiry_status;

				$currentStatusIndex = $inquiryStatus[$currentStatus]['index'];
				$newStatusIndex = $inquiryStatus[$newStatus]['index'];

				$questionStatusList = array();
				$onlyIdQuesion = $inquiryStatus[$newStatus]['only_id_question'];
				$needFolloup = $inquiryStatus[$newStatus]['need_followup'];

				if ($onlyIdQuesion == 1) {

					if ($currentStatus == 14 && $newStatus == 12) {
					} else {

						if ($newStatus == 14) {

							$questionStatusList[] = 12;
						} else {
							$questionStatusList[] = $newStatus;
						}
					}
				} else if ($onlyIdQuesion == 0) {

					if ($currentStatus == 11) {
						$questionStatusList[] = 10;
					} else {

						foreach ($inquiryStatus as $keyIQS => $valIQS) {

							if ($valIQS['id'] == 12 || $valIQS['id'] == 14 || $valIQS['id'] == 101 || $valIQS['id'] == 102) {
								continue;
							} else if ($newStatus == 10) {
								if ($valIQS['id'] == 11) {
									continue;
								}
							}

							$nStatusIndex = $inquiryStatus[$valIQS['id']]['index'];

							if ($currentStatusIndex < $nStatusIndex && $newStatusIndex >= $nStatusIndex) {
								$questionStatusList[] = $valIQS['id'];
							}
						}
					}
				}

				// if ($onlyIdQuesion == 1) {

				// 	$questionStatusList[] = $newStatus;
				// } else if ($onlyIdQuesion == 0) {

				// 	if ($currentStatus == 11) {
				// 		$questionStatusList[] = 10;
				// 	} else {

				// 		foreach ($inquiryStatus as $keyIQS => $valIQS) {

				// 			if ($currentStatus < $valIQS['id'] && $newStatus >= $valIQS['id']) {

				// 				$questionStatusList[] = $valIQS['id'];
				// 			}
				// 		}
				// 	}
				// }

				$inquiryQuestion = InquiryQuestion::whereIn('status', $questionStatusList)->orderBy('status', 'asc')->orderBy('sequence', 'asc')->get();

				$rules = array();

				if (in_array(4, $questionStatusList) && $isChannelPartner == 0) {

					// $rules['answer_architect'] = 'required';
					// $rules['answer_electrician'] = 'required';

				}

				if (in_array(9, $questionStatusList) || in_array(11, $questionStatusList)) {

					// $rules['answer_architect'] = 'required';
					$rules['answer_material_send_channel_partner'] = 'required';
				}
				if ($needFolloup == 1) {

					$rules['answer_follow_up_type'] = 'required';
					$rules['answer_follow_up_date'] = 'required';
					$rules['answer_follow_up_time'] = 'required';
				}

				$requiredQuestionIds = array();

				if (count($inquiryQuestion) > 0) {
					foreach ($inquiryQuestion as $iQK => $iQV) {

						// $checkForVisible = 0;
						if ($iQV->id == 8 && $Inquiry->site_photos != "") {
							continue;
						}
						if ($iQV->id == 1 && $Inquiry->quotation != "") {
							continue;
						}
						if ($iQV->id == 11 && $Inquiry->billing_invoice != "") {
							continue;
						}
						
						if ($iQV->is_depend_on_answer == 1) {
							$dependedQuestion = InquiryQuestion::find($iQV->depended_question_id);
							if ($dependedQuestion && in_array($dependedQuestion->status, $questionStatusList)) {
								if ($dependedQuestion->type == 6) {
									if (isset($request->all()['inquiry_questions_' . $iQV->depended_question_id][$iQV->depended_question_answer]) && $request->all()['inquiry_questions_' . $iQV->depended_question_id][$iQV->depended_question_answer] == "on") {
										if ($iQV->is_required == 1 && $iQV->type == 2) {
											$rules['inquiry_questions_' . $iQV->id] = 'required|mimes:png,jpg,jpeg,csv,txt,xlx,xls,xlsx,pdf';
											$requiredQuestionIds[] = $iQV->id;
										} else if ($iQV->is_required == 1 && $iQV->type == 7) {
											$rules['inquiry_questions_' . $iQV->id . ".*"] = 'required|mimes:png,jpg,jpeg,csv,txt,xlx,xls,xlsx,pdf';
											$requiredQuestionIds[] = $iQV->id;
										} elseif ($iQV->is_required == 1) {
											$rules['inquiry_questions_' . $iQV->id] = 'required';
										}
									}
								} else if ($dependedQuestion->type == 4) {
									if (isset($request->all()['inquiry_questions_' . $iQV->depended_question_id])) {
										if (in_array($iQV->depended_question_answer, $request->all()['inquiry_questions_' . $iQV->depended_question_id])) {
											if ($iQV->is_required == 1 && $iQV->type == 2) {
												$rules['inquiry_questions_' . $iQV->id] = 'required|mimes:png,jpg,jpeg,csv,txt,xlx,xls,xlsx,pdf';
												$requiredQuestionIds[] = $iQV->id;
											} else if ($iQV->is_required == 1 && $iQV->type == 7) {
												$rules['inquiry_questions_' . $iQV->id . ".*"] = 'required|mimes:png,jpg,jpeg,csv,txt,xlx,xls,xlsx,pdf';
												$requiredQuestionIds[] = $iQV->id;
											} elseif ($iQV->is_required == 1) {
												$rules['inquiry_questions_' . $iQV->id] = 'required';
											}
										}
									}
								} else if (isset($request->all()['inquiry_questions_' . $iQV->depended_question_id]) && $request->all()['inquiry_questions_' . $iQV->depended_question_id] == $iQV->depended_question_answer) {
									if ($iQV->is_required == 1 && $iQV->type == 2) {
										$rules['inquiry_questions_' . $iQV->id] = 'required|mimes:png,jpg,jpeg,csv,txt,xlx,xls,xlsx,pdf';
										$requiredQuestionIds[] = $iQV->id;
									} else if ($iQV->is_required == 1 && $iQV->type == 7) {
										$rules['inquiry_questions_' . $iQV->id . ".*"] = 'required|mimes:png,jpg,jpeg,csv,txt,xlx,xls,xlsx,pdf';
										$requiredQuestionIds[] = $iQV->id;
									} elseif ($iQV->is_required == 1) {
										$rules['inquiry_questions_' . $iQV->id] = 'required';
									}
								}
							} else {
								if ($iQV->is_required == 1 && $iQV->type == 2) {
									$rules['inquiry_questions_' . $iQV->id] = 'required|mimes:png,jpg,jpeg,csv,txt,xlx,xls,pdf';
									$requiredQuestionIds[] = $iQV->id;
								} else if ($iQV->is_required == 1 && $iQV->type == 7) {
									$rules['inquiry_questions_' . $iQV->id . ".*"] = 'required|mimes:png,jpg,jpeg,csv,txt,xlx,xls,xlsx,pdf';
									$requiredQuestionIds[] = $iQV->id;
								} elseif ($iQV->is_required == 1) {
									$rules['inquiry_questions_' . $iQV->id] = 'required';
								}
							}
						} else {

							// Validation If question is not depended
							if ($iQV->is_required == 1 && $iQV->type == 2) {
								$rules['inquiry_questions_' . $iQV->id] = 'required|mimes:png,jpg,jpeg,csv,txt,xlx,xls,xlsx,pdf';
								$requiredQuestionIds[] = $iQV->id;
							} else if ($iQV->is_required == 1 && $iQV->type == 7) {
								$rules['inquiry_questions_' . $iQV->id . ".*"] = 'required|mimes:png,jpg,jpeg,csv,txt,xlx,xls,xlsx,pdf';
								$requiredQuestionIds[] = $iQV->id;
							} elseif ($iQV->is_required == 1) {
								$rules['inquiry_questions_' . $iQV->id] = 'required';
							}
						}
					}
				}

				$customMessage = array();
				$customMessage['answer_follow_up_type.required'] = "Please select follow up type";
				$customMessage['answer_follow_up_date.required'] = "Please select follow up date";
				$customMessage['answer_follow_up_time.required'] = "Please select follow up time";
				$customMessage['answer_architect.required'] = "Please select architect";
				$customMessage['answer_electrician.required'] = "Please select electrician";
				$customMessage['answer_material_send_channel_partner.required'] = "Please select which Channel partner Through A Material Sent on Site";

				foreach ($inquiryQuestion as $iQK => $iQV) {

					if ($iQV->type == 0) {
						$customMessage['inquiry_questions_' . $iQV->id . '.required'] = "Please enter Q. " . $iQV->question;
					} else if ($iQV->type == 1) {

						$customMessage['inquiry_questions_' . $iQV->id . '.required'] = "Please select Q. " . $iQV->question;
					} else if ($iQV->type == 2) {

						$customMessage['inquiry_questions_' . $iQV->id . '.*'] = "Please attach file (png,jpg,jpeg,csv,txt,xlx,xls,xlsx,pdf) Q. " . $iQV->question;
					} else if ($iQV->type == 3) {
						$customMessage['inquiry_questions_' . $iQV->id . '.required'] = "Please check Q. " . $iQV->question;
					} else if ($iQV->type == 4) {
						$customMessage['inquiry_questions_' . $iQV->id . '.required'] = "Please select Q. " . $iQV->question;
					} else if ($iQV->type == 5) {
						$customMessage['inquiry_questions_' . $iQV->id . '.required'] = "Please enter Q. " . $iQV->question;
					} else if ($iQV->type == 6) {
						$customMessage['inquiry_questions_' . $iQV->id . '.required'] = "Please check Q. " . $iQV->question;
					} else if ($iQV->type == 7) {

						$customMessage['inquiry_questions_' . $iQV->id . '.*.*'] = "Please attach files (png,jpg,jpeg,csv,txt,xlx,xls,xlsx,pdf) Q. " . $iQV->question;
					}
				}

				// echo '<pre>';
				// print_r($rules);
				// die;

				$validator = Validator::make($request->all(), $rules, $customMessage);
				if ($validator->fails()) {

					$response = array();
					$response['status'] = 0;
					$response['msg'] = "The request could not be understood by the server due to malformed syntax";
					$response['statuscode'] = 400;
					$response['data'] = $validator->errors();

					// return redirect()->back()->with("error", "Something went wrong with validation");
				} else {

					$inquiryQuestionAnswer = array();
					$quotation = "";
					$quotation_amount = "";
					$stage_of_site = "";
					$billing_invoice = "";
					$billing_amount = "";

					foreach ($inquiryQuestion as $iQK => $iQV) {

						$inquiryQuestionAnswer[$iQV->id]['inquiry_question_id'] = $iQV->id;
						$inquiryQuestionAnswer[$iQV->id]['inquiry_id'] = $request->inquiry_id;
						$inquiryQuestionAnswer[$iQV->id]['user_id'] = Auth::user()->id;
						$inquiryQuestionAnswer[$iQV->id]['question_type'] = $iQV->type;

						if ($iQV->type == 2) {

							$question_attachment_file_name = '';
							if ($request->hasFile('inquiry_questions_' . $iQV->id)) {

								$question_attachment = $request->file('inquiry_questions_' . $iQV->id);
								$extension = $question_attachment->getClientOriginalExtension();
								$question_attachment_file_name = time() . mt_rand(10000, 99999) . '.' . $extension;

								$destinationPath = public_path('/s/question-attachment');
								$question_attachment->move($destinationPath, $question_attachment_file_name);

								if (!File::exists('s/question-attachment/' . $question_attachment_file_name)) {
									$question_attachment_file_name = "";
								} else {
									$question_attachment_file_name = '/s/question-attachment/' . $question_attachment_file_name;

									$spaceUploadResponse = uploadFileOnSpaces(public_path($question_attachment_file_name), $question_attachment_file_name);
									if ($spaceUploadResponse != 1) {
										$question_attachment_file_name = "";
									} else {
										unlink(public_path($question_attachment_file_name));
									}
								}
							}

							if ($question_attachment_file_name == "") {

								if (in_array($iQV->id, $requiredQuestionIds)) {

									$response = errorRes("Please attach valid file (png,jpg,jpeg,csv,txt,xlx,xls,xlsx,pdf) Q. " . $iQV->question);
									return response()->json($response)->header('Content-Type', 'application/json');
								}
							}

							if ($question_attachment_file_name == "" && $inquiryQuestionAnswer[$iQV->id]['inquiry_question_id'] == 8 && $Inquiry->site_photos != "") {
								$question_attachment_file_name = $Inquiry->site_photos;
							}

							if ($question_attachment_file_name == "" && $inquiryQuestionAnswer[$iQV->id]['inquiry_question_id'] == 1 && $Inquiry->quotation != "") {
								$question_attachment_file_name = $Inquiry->quotation;
							}

							$inquiryQuestionAnswer[$iQV->id]['answer'] = $question_attachment_file_name;

							if ($inquiryQuestionAnswer[$iQV->id]['inquiry_question_id'] == 1) {
								$quotation = $inquiryQuestionAnswer[$iQV->id]['answer'];
							}
						} elseif ($iQV->type == 3) {
							$answerIsCheked = (isset($request->all()['inquiry_questions_' . $iQV->id]) && $request->all()['inquiry_questions_' . $iQV->id] == 'on') ? 1 : 0;
							$inquiryQuestionAnswer[$iQV->id]['answer'] = $answerIsCheked;
						} elseif ($iQV->type == 4) {

							$multipleOptions = isset($request->all()['inquiry_questions_' . $iQV->id]) ? $request->all()['inquiry_questions_' . $iQV->id] : array();
							$multipleOptions = implode(",", $multipleOptions);

							$inquiryQuestionAnswer[$iQV->id]['answer'] = $multipleOptions;
						} elseif ($iQV->type == 6) {

							$answerOfMultiCHeck = isset($request->all()['inquiry_questions_' . $iQV->id]) ? $request->all()['inquiry_questions_' . $iQV->id] : array();
							$answerOfMultiCHeck = array_keys($answerOfMultiCHeck);
							$answerOfMultiCHeck = implode(",", $answerOfMultiCHeck);
							$inquiryQuestionAnswer[$iQV->id]['answer'] = $answerOfMultiCHeck;
						} elseif ($iQV->type == 7) {

							$question_attachment_file_name = array();

							if ($request->hasFile('inquiry_questions_' . $iQV->id)) {

								foreach ($request->file('inquiry_questions_' . $iQV->id) as $key => $value) {

									$question_attachment = $request->file('inquiry_questions_' . $iQV->id)[$key];
									$extension = $question_attachment->getClientOriginalExtension();

									$question_attachment_file_name_temp = time() . mt_rand(10000, 99999) . '.' . $extension;

									$destinationPath = public_path('/s/question-attachment');
									$question_attachment->move($destinationPath, $question_attachment_file_name_temp);

									if (!File::exists('s/question-attachment/' . $question_attachment_file_name_temp)) {
										$question_attachment_file_name_temp = "";
									} else {

										$question_attachment_file_name_temp = '/s/question-attachment/' . $question_attachment_file_name_temp;

										$spaceUploadResponse = uploadFileOnSpaces(public_path($question_attachment_file_name_temp), $question_attachment_file_name_temp);
										if ($spaceUploadResponse != 1) {
											$question_attachment_file_name_temp = "";
										} else {

											$question_attachment_file_name[] = $question_attachment_file_name_temp;
											unlink(public_path($question_attachment_file_name_temp));
										}
									}
								}
							}

							if (count($question_attachment_file_name) == 0) {

								if (in_array($iQV->id, $requiredQuestionIds)) {

									$response = errorRes("Please attach valid files (png,jpg,jpeg,csv,txt,xlx,xls,xlsx,pdf) Q. " . $iQV->question);
									return response()->json($response)->header('Content-Type', 'application/json');
								}
							}

							$question_attachment_file_name = implode(",", $question_attachment_file_name);

							$inquiryQuestionAnswer[$iQV->id]['answer'] = $question_attachment_file_name;

							if ($inquiryQuestionAnswer[$iQV->id]['inquiry_question_id'] == 11) {

								$billing_invoice = $question_attachment_file_name;
							}
						} else {

							$answer = isset($request->all()['inquiry_questions_' . $iQV->id]) ? $request->all()['inquiry_questions_' . $iQV->id] : '';

							$inquiryQuestionAnswer[$iQV->id]['answer'] = $answer;

							if ($inquiryQuestionAnswer[$iQV->id]['inquiry_question_id'] == 2) {
								$quotation_amount = $inquiryQuestionAnswer[$iQV->id]['answer'];
							} else if ($inquiryQuestionAnswer[$iQV->id]['inquiry_question_id'] == 3) {
								//$architect_name = $inquiryQuestionAnswer[$iQV->id]['answer'];
							} else if ($inquiryQuestionAnswer[$iQV->id]['inquiry_question_id'] == 4) {
								// $architect_phone_number = $inquiryQuestionAnswer[$iQV->id]['answer'];

							} else if ($inquiryQuestionAnswer[$iQV->id]['inquiry_question_id'] == 5) {
								//$electrician_name = $inquiryQuestionAnswer[$iQV->id]['answer'];

							} else if ($inquiryQuestionAnswer[$iQV->id]['inquiry_question_id'] == 6) {
								//$electrician_phone_number = $inquiryQuestionAnswer[$iQV->id]['answer'];

							} else if ($inquiryQuestionAnswer[$iQV->id]['inquiry_question_id'] == 7) {
								$stage_of_site = $inquiryQuestionAnswer[$iQV->id]['answer'];
							} else if ($inquiryQuestionAnswer[$iQV->id]['inquiry_question_id'] == 12) {
								$billing_amount = $inquiryQuestionAnswer[$iQV->id]['answer'];
							}
						}

						$inquiryQuestionAnswer[$iQV->id]['created_at'] = date("Y-m-d H:i:s");
						$inquiryQuestionAnswer[$iQV->id]['updated_at'] = date("Y-m-d H:i:s");
					}

					// $inquiryStatus = getInquiryStatus();
					// $Inquiry = Inquiry::find($request->inquiry_id);

					if ($isAdminOrCompanyAdmin == 1 || ($isSalePerson == 1 && (in_array($Inquiry->assigned_to, $childSalePersonsIds))) || ($isChannelPartner != 0 && $Inquiry->source_type_value == Auth::user()->id) || ($isTaleSalesUser == 1 && in_array($Inquiry->city_id, $TaleSalesCities))) {

						$inquiryQuestionAnswer = array_values($inquiryQuestionAnswer);

						$InquiryQuestionAnswer = new InquiryQuestionAnswer();
						$inquiryQuestionAnswer = $InquiryQuestionAnswer->insert($inquiryQuestionAnswer);

						if ($InquiryQuestionAnswer) {

							if ($request->inquiry_id != 0) {

								$previousStatus = $inquiryStatus[$Inquiry->status]['name'];
								if (isset($request->answer_follow_up_type)) {
									$Inquiry->follow_up_type = $request->answer_follow_up_type;
								}

								if (isset($request->answer_follow_up_date) && isset($request->answer_follow_up_time)) {
									$Inquiry->follow_up_date_time = date('Y-m-d H:i:s', strtotime($request->answer_follow_up_date . " " . $request->answer_follow_up_time));
								}
								$Inquiry->status = $request->inquiry_status;

								if ($quotation != "") {
									$Inquiry->quotation = $quotation;
								}

								if ($quotation_amount != "") {
									$Inquiry->quotation_amount = $quotation_amount;
								}
								if ($stage_of_site != "") {

									$Option = InquiryQuestionOption::find($stage_of_site);
									if ($Option) {
										$Inquiry->stage_of_site = $Option->option;
									}
								}

								if (in_array(4, $questionStatusList) && $isChannelPartner == 0) {

									if (isset($request->answer_architect) && $request->answer_architect != "" && $request->answer_architect != 0) {

										$Inquiry->architect = $request->answer_architect;
									}

									if (isset($request->answer_electrician) && $request->answer_electrician != "" && $request->answer_electrician != 0) {

										$Inquiry->electrician = $request->answer_electrician;
									}
								}

								if (in_array(9, $questionStatusList) || in_array(11, $questionStatusList)) {
									$Inquiry->material_sent_channel_partner = $request->answer_material_send_channel_partner;
								}

								if ($billing_invoice != "") {
									$Inquiry->billing_invoice = $billing_invoice;
								}

								if ($billing_amount != "") {
									$Inquiry->billing_amount = $billing_amount;
								}

								$Inquiry->save();
								$currentStatus = $inquiryStatus[$Inquiry->status]['name'];
								$response = successRes("Successfully Inquiry Status changed");

								if ($Inquiry->status == 10) {
									$Inquiry->claimed_date_time = date('Y-m-d H:i:s');
									$Inquiry->is_claimed = 1;
									$Inquiry->save();
								}

								if ($Inquiry->status == 9 || $Inquiry->status == 11) {
									$Inquiry->material_sent_date_time = date('Y-m-d H:i:s');
									$Inquiry->save();
								}
								$Inquiry->answer_date_time = date('Y-m-d H:i:s');
								$Inquiry->save();

								$debugLog = array();
								$debugLog['inquiry_id'] = $Inquiry->id;
								$debugLog['name'] = "answer";
								$debugLog['description'] = "Status From " . $previousStatus . " to " . $currentStatus;
								saveInquiryLog($debugLog);

								$mobileNotificationTitle = "Inquiry Update";
								//$mobileNotificationMessage = "Your Inquiry " . $Inquiry->id . " Status Update " . $previousStatus . " To " . $currentStatus;
								$mobileNotificationMessage = "Your Inquiry #" . $Inquiry->id . " " . $Inquiry->first_name . " " . $Inquiry->last_name . " Status Update " . $previousStatus . " To " . $currentStatus;
								$notificationUserids = getParentSalePersonsIds($Inquiry->assigned_to);
								$notificationUserids[] = $Inquiry->assigned_to;
								$UsersNotificationTokens = UsersNotificationTokens($notificationUserids);
								sendNotificationTOAndroid($mobileNotificationTitle, $mobileNotificationMessage, $UsersNotificationTokens, 'Inquiry', $Inquiry);
							}
						} else {
							$response = errorRes("Invalid inquiry id1");
						}
					} else {
						$response = errorRes("Invalid inquiry id2");
					}
				}
			} else {
				$response = errorRes("Invalid inquiry id3");
			}
		} else {
			$response = errorRes("Invalid inquiry id");
		}

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	function getMentionsUsers($string)
	{

		$mentionUsers = array();
		$stringingLenth = strlen($string);
		$isStartKeywordDetected = 0;
		$mentionString = "";
		$startingWord = "@";
		$endingWord = " ";
		for ($i = 0; $i < $stringingLenth; $i++) {

			if ($isStartKeywordDetected == 0) {

				if ($string[$i] == $startingWord) {

					$mentionString = "";
					//$mentionString .= $string[$i];
					$isStartKeywordDetected = 1;
				}
			} else if ($isStartKeywordDetected == 1) {

				$isEndedDeteced = 0;

				if (preg_match('/[\'^£$%&*()}{#~?><>,|=_+¬-]/', $string[$i])) {
					// one or more of the 'special characters' found in $string
					$isEndedDeteced = 1;
				}

				if ($string[$i] == $endingWord || $isEndedDeteced == 1) {
					$mentionUsers[] = $mentionString;

					$mentionString = "";
					$isStartKeywordDetected = 0;
				} else {
					$mentionString .= $string[$i];
				}
			}
		}
		return $mentionUsers;

		// $subtring_start = strpos($string, $startingWord);
		// //Adding the starting index of the starting word to
		// //its length would give its ending index
		// $subtring_start += strlen($startingWord);
		// //Length of our required sub string
		// $size = strpos($string, $endingWord, $subtring_start) - $subtring_start;
		// // Return the substring from the index substring_start of length size
		// return substr($string, $subtring_start, $size);
	}

	public function saveTMUpdate(Request $request)
	{

		$isTaleSalesUser = isTaleSalesUser();
		$isSalePerson = isSalePerson();
		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();

		$meessageValidation = isset($request->update_TM_message) ? $request->update_TM_message : '';
		$meessageValidation = trim($meessageValidation);
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

		$validator = Validator::make($request->all(), [

			'update_TM_type' => ['required'],
			'update_TM_inquiry_id' => ['required'],
			'update_TM_message' => ['required'],

		]);

		if ($validator->fails()) {
			$response = array();
			$response['status'] = 0;
			$response['msg'] = "The request could not be understood by the server due to malformed syntax";
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();
		} else {

			$Inquiry = Inquiry::find($request->update_TM_inquiry_id);
			if ($Inquiry) {
				$TM_type = $request->update_TM_type;
				if ($TM_type == "T") {

					if ($isAdminOrCompanyAdmin == 1 || $isTaleSalesUser == 1) {

						$InquiryUpdate = new InquiryUpdate();
						$InquiryUpdate->message = trim($request->update_TM_message);
						$InquiryUpdate->user_id = Auth::user()->id;
						$InquiryUpdate->inquiry_id = $Inquiry->id;
						$InquiryUpdate->reply_id = 0;
						$InquiryUpdate->save();

						$Inquiry->last_update = date('Y-m-d H:i:s');
						$Inquiry->update_count = $Inquiry->update_count + 1;
						$Inquiry->is_for_tele_sale = 0;
						$Inquiry->save();
						$response = successRes("Successfully sent message");
					} else {
						$response = errorRes("You haven't privilege to update");
					}
				} else if ($TM_type == "M") {

					$parentSalesUsers = array();

					if ($isSalePerson == 1) {

						$parentSalesUsers = getParentSalePersonsIds($Inquiry->assigned_to);
					}

					if ($isAdminOrCompanyAdmin == 1 || ($isSalePerson == 1) && in_array(Auth::user()->id, $parentSalesUsers)) {
						$InquiryUpdate = new InquiryUpdate();
						$InquiryUpdate->message = trim($request->update_TM_message);
						$InquiryUpdate->user_id = Auth::user()->id;
						$InquiryUpdate->inquiry_id = $Inquiry->id;
						$InquiryUpdate->reply_id = 0;
						$InquiryUpdate->save();

						$Inquiry->last_update = date('Y-m-d H:i:s');
						$Inquiry->update_count = $Inquiry->update_count + 1;
						$Inquiry->is_for_manager = 0;
						$Inquiry->save();
						$response = successRes("Successfully sent message");
					} else {
						$response = errorRes("You haven't privilege to update");
					}
				}
			} else {
				$response = errorRes("Invalid inquiry id");
			}
		}

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

			$Inquiry = Inquiry::find($request->inquiry_id);

			if ($Inquiry) {

				if ($isSalePerson == 1) {
					$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
				}

				if ($isAdminOrCompanyAdmin == 1 || ($isSalePerson == 1 && in_array($Inquiry->assigned_to, $childSalePersonsIds)) || ($isChannelPartner != 0 && ($Inquiry->source_type_value == Auth::user()->id || $Inquiry->source_type_value_1 == Auth::user()->id || $Inquiry->source_type_value_2 == Auth::user()->id || $Inquiry->source_type_value_3 == Auth::user()->id || $Inquiry->source_type_value_4 == Auth::user()->id)) || ($isTaleSalesUser == 1 && in_array($Inquiry->city_id, $TaleSalesCities))) {

					if ($request->inquiry_update_id != 0) {
						$replyValidation = InquiryUpdate::find($request->inquiry_update_id);
					}

					if ($request->inquiry_update_id == 0 || ($replyValidation->inquiry_id == $Inquiry->id)) {

						$InquiryUpdate = new InquiryUpdate();
						$InquiryUpdate->message = trim($request->message);
						$InquiryUpdate->user_id = Auth::user()->id;
						$InquiryUpdate->inquiry_id = $request->inquiry_id;
						$InquiryUpdate->reply_id = $request->inquiry_update_id;
						$InquiryUpdate->save();
						$Inquiry->last_update = date('Y-m-d H:i:s');
						$Inquiry->update_count = $Inquiry->update_count + 1;
						$Inquiry->save();

						/// Mention Notification

						$mentionUsers = $this->getMentionsUsers($InquiryUpdate->message);
						$mentionUsers = array_unique($mentionUsers);
						$mentionUsers = array_values($mentionUsers);

						if (count($mentionUsers) > 0) {

							$mentionUsers = User::select('id')->whereIn('email', $mentionUsers)->get();
						}

						foreach ($mentionUsers as $mUser) {

							if ($mUser->id != Auth::user()->id) {

								$UserNotify = array();
								$UserNotify['user_id'] = $mUser->id;
								$UserNotify['type'] = 4;
								$UserNotify['from_user_id'] = Auth::user()->id;
								$UserNotify['title'] = "Inquiry #" . $Inquiry->id . " (" . $Inquiry->first_name . " " . $Inquiry->last_name . ") mention to you";
								$UserNotify['description'] = $InquiryUpdate->message;
								$UserNotify['inquiry_id'] = $InquiryUpdate->inquiry_id;
								saveUserNotification($UserNotify);
							}
						}

						if ($InquiryUpdate->reply_id == 0) {

							if ($isAdminOrCompanyAdmin == 1) {

								if ($Inquiry->assigned_to != Auth::user()->id) {

									$UserNotify = array();
									$UserNotify['user_id'] = $Inquiry->assigned_to;
									$UserNotify['type'] = 1;
									$UserNotify['from_user_id'] = Auth::user()->id;
									$UserNotify['title'] = "Inquiry #" . $Inquiry->id . " (" . $Inquiry->first_name . " " . $Inquiry->last_name . ") Update";
									$UserNotify['description'] = $InquiryUpdate->message;
									$UserNotify['inquiry_id'] = $InquiryUpdate->inquiry_id;
									saveUserNotification($UserNotify);

									$salesPerson = getSalesPersonReportingManager($Inquiry->assigned_to);

									if ($salesPerson && $salesPerson->reporting_manager_id != 0) {

										$UserNotify = array();
										$UserNotify['user_id'] = $salesPerson->reporting_manager_id;
										$UserNotify['type'] = 1;
										$UserNotify['from_user_id'] = Auth::user()->id;
										$UserNotify['title'] = "Inquiry #" . $Inquiry->id . " (" . $Inquiry->first_name . " " . $Inquiry->last_name . ") Update";
										$UserNotify['description'] = $InquiryUpdate->message;
										$UserNotify['inquiry_id'] = $InquiryUpdate->inquiry_id;
										saveUserNotification($UserNotify);
									}
								}
							} else if ($isSalePerson == 1) {

								if ($Inquiry->assigned_to != Auth::user()->id) {

									$UserNotify = array();
									$UserNotify['user_id'] = $Inquiry->assigned_to;
									$UserNotify['type'] = 1;
									$UserNotify['from_user_id'] = Auth::user()->id;
									$UserNotify['title'] = "Inquiry #" . $Inquiry->id . " (" . $Inquiry->first_name . " " . $Inquiry->last_name . ") Update";
									$UserNotify['description'] = $InquiryUpdate->message;
									$UserNotify['inquiry_id'] = $InquiryUpdate->inquiry_id;
									saveUserNotification($UserNotify);
								}

								$salesPerson = getSalesPersonReportingManager($Inquiry->assigned_to);

								if ($salesPerson && $salesPerson->reporting_manager_id != 0 && $salesPerson->reporting_manager_id != Auth::user()->id) {

									$UserNotify = array();
									$UserNotify['user_id'] = $salesPerson->reporting_manager_id;
									$UserNotify['type'] = 1;
									$UserNotify['from_user_id'] = Auth::user()->id;
									$UserNotify['title'] = "Inquiry #" . $Inquiry->id . " (" . $Inquiry->first_name . " " . $Inquiry->last_name . ") Update";
									$UserNotify['description'] = $InquiryUpdate->message;
									$UserNotify['inquiry_id'] = $InquiryUpdate->inquiry_id;
									saveUserNotification($UserNotify);
								}
							}
						} else if ($InquiryUpdate->reply_id != 0) {

							$InquiryUpdateReply = InquiryUpdate::select('user_id')->where('id', $InquiryUpdate->reply_id)->get();

							$repliedUsers = array();
							foreach ($InquiryUpdateReply as $key => $value) {

								$repliedUsers[] = $value->user_id;
							}

							$repliedUsers = array_unique(array_values($repliedUsers));

							foreach ($repliedUsers as $rUserId) {

								if ($rUserId != Auth::user()->id) {

									$UserNotify = array();
									$UserNotify['user_id'] = $rUserId;
									$UserNotify['type'] = 2;
									$UserNotify['from_user_id'] = Auth::user()->id;
									$UserNotify['title'] = "Inquiry #" . $Inquiry->id . " (" . $Inquiry->first_name . " " . $Inquiry->last_name . ") Update Reply";
									$UserNotify['description'] = $InquiryUpdate->message;
									$UserNotify['inquiry_id'] = $InquiryUpdate->inquiry_id;

									saveUserNotification($UserNotify);
								}
							}
						}

						///

						$response = successRes("Successfully sent message");
					} else {
						$response = errorRes("Invalid parameters");
					}
				} else {
					$response = errorRes("Invalid access");
				}
			} else {
				$response = errorRes("Invalid inquiry id");
			}
			return response()->json($response)->header('Content-Type', 'application/json');
		}
	}

	public function moveTosureNosure(Request $request)
	{

		$validator = Validator::make($request->all(), [
			'inquiry_id' => ['required'],
			'is_predication_sure' => ['required'],
		]);

		if ($validator->fails()) {
			$response = array();
			$response['status'] = 0;
			$response['msg'] = "The request could not be understood by the server due to malformed syntax";
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();
		} else {

			$Inquiry = Inquiry::find($request->inquiry_id);
			if ($Inquiry) {
				$Inquiry->is_predication_sure = $request->is_predication_sure;
				$Inquiry->save();

				if ($request->is_predication_sure == 0) {
					$response = successRes("Successfully mark as not sure");
				} else if ($request->is_predication_sure == 1) {
					$response = successRes("Successfully mark as sure");
				}
			} else {
				$response = errorRes("Inquiry not found");
			}
		}

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function moveToTM(Request $request)
	{

		$validator = Validator::make($request->all(), [
			'inquiry_id' => ['required'],
			'TM' => ['required'],
		]);

		if ($validator->fails()) {
			$response = array();
			$response['status'] = 0;
			$response['msg'] = "The request could not be understood by the server due to malformed syntax";
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();
		} else {

			$Inquiry = Inquiry::find($request->inquiry_id);
			if ($Inquiry) {

				if ($request->TM == "T") {
					$Inquiry->is_for_tele_sale = 1;
				} else if ($request->TM == "M") {
					$Inquiry->is_for_manager = 1;
				}

				$Inquiry->save();

				if ($request->TM == "T") {
					$response = successRes("Successfully mark as TeleSales");
				} else if ($request->TM == "M") {
					$response = successRes("Successfully mark as Manager");
				}
			} else {
				$response = errorRes("Inquiry not found");
			}
		}

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function updateSeen(Request $request)
	{

		$validator = Validator::make($request->all(), [

			'update_id' => ['required'],

		]);

		if ($validator->fails()) {
			$response = array();
			$response['status'] = 0;
			$response['msg'] = "The request could not be understood by the server due to malformed syntax";
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();
		} else {

			$InquiryUpdateSeen = InquiryUpdateSeen::query();
			$InquiryUpdateSeen->select('users.id', 'inquiry_update_seen.user_id', 'users.first_name', 'users.last_name');
			$InquiryUpdateSeen->leftJoin('users', 'inquiry_update_seen.user_id', '=', 'users.id');
			$InquiryUpdateSeen->where('inquiry_update_seen.inquiry_update_id', $request->update_id);
			// $InquiryUpdateSeen->where('inquiry_update_seen.user_id', Auth::user()->id);
			$InquiryUpdateSeen = $InquiryUpdateSeen->orderBy('inquiry_update_seen.id', 'desc')->get();

			$view = '<ul class="list-unstyled chat-list seen-ul" data-simplebar >';

			foreach ($InquiryUpdateSeen as $key => $value) {

				$firstLetterA = strtoupper(substr($value->first_name, 0, 1));
				$firstLetterB = strtoupper(substr($value->last_name, 0, 1));

				// 	$view .= '<div class=" avatar-xs inquiry-avatar-xs"><span class="seen-avatar avatar-title rounded-circle bg-primary bg-soft text-primary">' . $firstLetterA . '' . $firstLetterB . '</span></div>';

				$view .= ' <li>
                                                        <a href="javascript: void(0);">
                                                            <div class="d-flex align-items-center">
                                                                <div class="flex-shrink-0 me-2">
                                                                    <div class="avatar-xs">
                                                                        <span class="seen-avatar avatar-title rounded-circle bg-primary bg-soft text-primary">
                                                                            ' . $firstLetterA . '' . $firstLetterB . '
                                                                        </span>
                                                                    </div>
                                                                </div>

                                                                <div class="flex-grow-1">
                                                                    <h5 class="font-size-10 mb-0">' . $value->first_name . ' ' . $value->last_name . '</h5>
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </li>';
			}
			$view .= '</ul>';

			$response = successRes("Inquiry Update seen list");
			$response['data'] = $view;
		}

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	function searchMentionUsers(Request $request)
	{

		// $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		// $isSalePerson = isSalePerson();

		$User = $UserResponse = array();
		$q = $request->q;
		$User = User::select('users.email');
		$User->whereIn('users.type', array(0, 1, 2, 101, 102, 103, 104, 105));
		$User->where('users.status', 1);
		$User->where('users.id', '!=', Auth::user()->id);
		$User->where(function ($query) use ($q) {
			$query->where('users.first_name', 'like', '%' . $q . '%');
			$query->orWhere('users.last_name', 'like', '%' . $q . '%');
			$query->orWhere('users.email', 'like', '%' . $q . '%');
		});
		$User->limit(5);
		$User = $User->get();

		if (count($User) > 0) {
			foreach ($User as $User_key => $User_value) {
				$UserResponse[$User_key] = $User_value['email'];
				//$UserResponse[$User_key]['text'] = $User_value['full_name'];
			}
		}
		$response = $UserResponse;

		// $response['results'] = $UserResponse;
		// $response['pagination']['more'] = false;
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	function checkPhoneNumber(Request $request)
	{

		$isAlreadyInquiry = Inquiry::select('id', 'assigned_to')->where('phone_number', $request->inquiry_phone_number)->first();
		if ($isAlreadyInquiry) {
			$User = User::select('first_name', 'last_name')->find($isAlreadyInquiry->assigned_to);
			if ($User) {

				$response = errorRes("Inquiry already registed with phone number, #" . $isAlreadyInquiry->id . " assigned to " . $User->first_name . " " . $User->last_name);
			} else {
				$response = errorRes("Inquiry already registed with phone number");
			}
		} else {

			$response = successRes("Inquiry phone number is valid");
		}

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	function deleteInvoice(Request $request)
	{

		$validator = Validator::make($request->all(), [

			'inquiry_id' => ['required'],
			'invoice_index' => ['required'],

		]);

		if ($validator->fails()) {
			$response = array();
			$response['status'] = 0;
			$response['msg'] = "The request could not be understood by the server due to malformed syntax";
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();
		} else {

			$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
			if ($isAdminOrCompanyAdmin == 1) {

				$Inquiry = Inquiry::find($request->inquiry_id);
				if ($Inquiry) {

					$billing_invoice = $Inquiry->billing_invoice;
					$billing_invoice = explode(",", $billing_invoice);
					if (isset($billing_invoice[$request->invoice_index])) {
						unset($billing_invoice[$request->invoice_index]);
					}
					$billing_invoice = array_values($billing_invoice);
					$billing_invoice = implode(",", $billing_invoice);
					$Inquiry->billing_invoice = $billing_invoice;
					$Inquiry->save();

					$InquiryQuestionAnswer = InquiryQuestionAnswer::where('inquiry_id', $Inquiry->id)->where('inquiry_question_id', 11)->first();

					if ($InquiryQuestionAnswer) {
						$InquiryQuestionAnswer->answer = $billing_invoice;
						$InquiryQuestionAnswer->save();
					}

					$response = successRes("Successfully delete Inquiry invoice");
					$response['billing_invoice'] = $billing_invoice;
				} else {
					$response = errorRes("Invalid inquiry");
				}
			} else {
				$response = errorRes("Invalid access");
			}
		}

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	function pointLog(Request $request)
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
			3 => 'inquiry.id',
			4 => 'inquiry.id',
			5 => 'inquiry.id',

		);

		$selectColumns = array(
			'inquiry.id',
			'inquiry.first_name',
			'inquiry.last_name',
			'inquiry.status',
			'inquiry.quotation_amount',
			'inquiry.answer_date_time',
			'inquiry.architect',
			'inquiry.electrician',
			'inquiry.source_type',
			'inquiry.source_type_value',

		);

		$userId = $request->user_id;

		$User = User::find($userId);
		$column4 = "";
		$column5 = "";

		$typeOfProcess = 0;
		$title = "";

		if ($User) {

			$title = $User->first_name . " " . $User->last_name;

			$title = $title . " | " . getUserTypeName($User->type);
			if ($User->type == 202) {
				$title = str_replace("PRIME", '<span class="badge rounded-pill bg-success">PRIME</span>', $title);
			} else if ($User->type == 302) {
				$title = str_replace("PRIME", '<span class="badge rounded-pill bg-success">PRIME</span>', $title);
			}

			if ($User->type == 201 || $User->type == 202) {

				$UserArchitect = Architect::where('user_id', $User->id)->first();
				if ($UserArchitect) {
					$title = $title . " | Lifetime Point : " . $UserArchitect->total_point . " | Available Point : " . $UserArchitect->total_point_current;
				}

				$typeOfProcess = 1;
				$column4 = "Electrician";
				$column5 = "ChannelPartner";
			} else if ($User->type == 301 || $User->type == 302) {

				$UserElectician = Electrician::where('user_id', $User->id)->first();
				if ($UserElectician) {
					$title = $title . " | Lifetime Point : " . $UserElectician->total_point . " | Available Point : " . $UserElectician->total_point_current;
				}

				$typeOfProcess = 2;

				$column4 = "Architect";
				$column5 = "ChannelPartner";
			} else if (isChannelPartner($User->type) != 0) {
				$typeOfProcess = 3;

				$column4 = "Architect";
				$column5 = "Electrician";
			}
		}

		$query = Inquiry::query();
		$query->where(function ($query2) use ($userId, $typeOfProcess) {

			if ($typeOfProcess != 0) {

				if ($typeOfProcess == 1) {
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
				} else if ($typeOfProcess == 2) {
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
				} else if ($typeOfProcess == 3) {

					$query2->where(function ($query3) use ($userId) {

						$query3->whereIn('inquiry.source_type', array("user-101", "user-102", "user-103", "user-104", "user-105"));
						$query3->where('inquiry.source_type_value', $userId);
					});

					$query2->orWhere(function ($query3) use ($userId) {

						$query3->whereIn('inquiry.source_type_1', array("user-101", "user-102", "user-103", "user-104", "user-105"));
						$query3->where('inquiry.source_type_value_1', $userId);
					});
					$query2->orWhere(function ($query3) use ($userId) {

						$query3->whereIn('inquiry.source_type_2', array("user-101", "user-102", "user-103", "user-104", "user-105"));
						$query3->where('inquiry.source_type_value_2', $userId);
					});
					$query2->orWhere(function ($query3) use ($userId) {

						$query3->whereIn('inquiry.source_type_3', array("user-101", "user-102", "user-103", "user-104", "user-105"));
						$query3->where('inquiry.source_type_value_3', $userId);
					});

					$query2->orWhere(function ($query3) use ($userId) {

						$query3->whereIn('inquiry.source_type_4', array("user-101", "user-102", "user-103", "user-104", "user-105"));
						$query3->where('inquiry.source_type_value_4', $userId);
					});
				}
			}
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

		$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.

		$query = Inquiry::query();
		$query->where(function ($query2) use ($userId, $typeOfProcess) {

			if ($typeOfProcess != 0) {

				if ($typeOfProcess == 1) {
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
				} else if ($typeOfProcess == 2) {
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
				} else if ($typeOfProcess == 3) {

					$query2->where(function ($query3) use ($userId) {

						$query3->whereIn('inquiry.source_type', array("user-101", "user-102", "user-103", "user-104", "user-105"));
						$query3->where('inquiry.source_type_value', $userId);
					});

					$query2->orWhere(function ($query3) use ($userId) {

						$query3->whereIn('inquiry.source_type_1', array("user-101", "user-102", "user-103", "user-104", "user-105"));
						$query3->where('inquiry.source_type_value_1', $userId);
					});
					$query2->orWhere(function ($query3) use ($userId) {

						$query3->whereIn('inquiry.source_type_2', array("user-101", "user-102", "user-103", "user-104", "user-105"));
						$query3->where('inquiry.source_type_value_2', $userId);
					});
					$query2->orWhere(function ($query3) use ($userId) {

						$query3->whereIn('inquiry.source_type_3', array("user-101", "user-102", "user-103", "user-104", "user-105"));
						$query3->where('inquiry.source_type_value_3', $userId);
					});

					$query2->orWhere(function ($query3) use ($userId) {

						$query3->whereIn('inquiry.source_type_4', array("user-101", "user-102", "user-103", "user-104", "user-105"));
						$query3->where('inquiry.source_type_value_4', $userId);
					});
				}
			}
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

		$query = Inquiry::query();

		$query->where(function ($query2) use ($userId, $typeOfProcess) {

			if ($typeOfProcess != 0) {

				if ($typeOfProcess == 1) {
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
				} else if ($typeOfProcess == 2) {
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
				} else if ($typeOfProcess == 3) {

					$query2->where(function ($query3) use ($userId) {

						$query3->whereIn('inquiry.source_type', array("user-101", "user-102", "user-103", "user-104", "user-105"));
						$query3->where('inquiry.source_type_value', $userId);
					});

					$query2->orWhere(function ($query3) use ($userId) {

						$query3->whereIn('inquiry.source_type_1', array("user-101", "user-102", "user-103", "user-104", "user-105"));
						$query3->where('inquiry.source_type_value_1', $userId);
					});
					$query2->orWhere(function ($query3) use ($userId) {

						$query3->whereIn('inquiry.source_type_2', array("user-101", "user-102", "user-103", "user-104", "user-105"));
						$query3->where('inquiry.source_type_value_2', $userId);
					});
					$query2->orWhere(function ($query3) use ($userId) {

						$query3->whereIn('inquiry.source_type_3', array("user-101", "user-102", "user-103", "user-104", "user-105"));
						$query3->where('inquiry.source_type_value_3', $userId);
					});

					$query2->orWhere(function ($query3) use ($userId) {

						$query3->whereIn('inquiry.source_type_4', array("user-101", "user-102", "user-103", "user-104", "user-105"));
						$query3->where('inquiry.source_type_value_4', $userId);
					});
				}
			}
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
			$viewData[$key]['id'] = $value['id'];
			$viewData[$key]['name'] = '<p class="text-muted mb-0" data-bs-toggle="tooltip"  data-bs-original-title="' . $value['first_name'] . " " . $value['last_name'] . '"><a target="_blank" href="' . route('inquiry') . '?status=0&inquiry_id=' . $value['id'] . '" >' . displayStringLenth($value['first_name'] . " " . $value['last_name'], 20) . '</a></p>';

			$viewData[$key]['status'] = $inquiryStatus[$value['status']]['name'] . " (" . convertDateTime($value['answer_date_time']) . ")";
			$viewData[$key]['quotation_amount'] = $value['quotation_amount'];

			$viewData[$key]['column4'] = "";
			$viewData[$key]['column5'] = "";
			if ($User) {

				if ($User->type == 201 || $User->type == 202) {

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
				} else if ($User->type == 301 || $User->type == 302) {

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
				} else if (isChannelPartner($User->type) != 0) {

					$column4Val = "";
					$column5Val = "";

					if ($value['architect'] != 0) {

						$User4 = User::find($value['architect']);
						if ($User4) {
							$column4Val = $User4->first_name . " " . $User4->last_name;
						}
					}

					if ($value['electrician'] != 0) {

						$User5 = User::find($value['electrician']);
						if ($User5) {
							$column5Val = $User5->first_name . " " . $User5->last_name;
						}
					}

					$viewData[$key]['column4'] = $column4Val;
					$viewData[$key]['column5'] = $column5Val;
				}
			}
		}

		$overview = array();

		$query = Inquiry::query();
		$query->where(function ($query2) use ($userId, $typeOfProcess) {

			if ($typeOfProcess != 0) {

				if ($typeOfProcess == 1) {
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
				} else if ($typeOfProcess == 2) {
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
				} else if ($typeOfProcess == 3) {

					$query2->where(function ($query3) use ($userId) {

						$query3->whereIn('inquiry.source_type', array("user-101", "user-102", "user-103", "user-104", "user-105"));
						$query3->where('inquiry.source_type_value', $userId);
					});

					$query2->orWhere(function ($query3) use ($userId) {

						$query3->whereIn('inquiry.source_type_1', array("user-101", "user-102", "user-103", "user-104", "user-105"));
						$query3->where('inquiry.source_type_value_1', $userId);
					});
					$query2->orWhere(function ($query3) use ($userId) {

						$query3->whereIn('inquiry.source_type_2', array("user-101", "user-102", "user-103", "user-104", "user-105"));
						$query3->where('inquiry.source_type_value_2', $userId);
					});
					$query2->orWhere(function ($query3) use ($userId) {

						$query3->whereIn('inquiry.source_type_3', array("user-101", "user-102", "user-103", "user-104", "user-105"));
						$query3->where('inquiry.source_type_value_3', $userId);
					});

					$query2->orWhere(function ($query3) use ($userId) {

						$query3->whereIn('inquiry.source_type_4', array("user-101", "user-102", "user-103", "user-104", "user-105"));
						$query3->where('inquiry.source_type_value_4', $userId);
					});
				}
			}
		});
		$recordsTotal = $query->count();
		$overview['total_inquiry'] = $recordsTotal;

		$query = Inquiry::query();
		$query->where(function ($query2) use ($userId, $typeOfProcess) {

			if ($typeOfProcess != 0) {

				if ($typeOfProcess == 1) {
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
				} else if ($typeOfProcess == 2) {
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
				} else if ($typeOfProcess == 3) {

					$query2->where(function ($query3) use ($userId) {

						$query3->whereIn('inquiry.source_type', array("user-101", "user-102", "user-103", "user-104", "user-105"));
						$query3->where('inquiry.source_type_value', $userId);
					});

					$query2->orWhere(function ($query3) use ($userId) {

						$query3->whereIn('inquiry.source_type_1', array("user-101", "user-102", "user-103", "user-104", "user-105"));
						$query3->where('inquiry.source_type_value_1', $userId);
					});
					$query2->orWhere(function ($query3) use ($userId) {

						$query3->whereIn('inquiry.source_type_2', array("user-101", "user-102", "user-103", "user-104", "user-105"));
						$query3->where('inquiry.source_type_value_2', $userId);
					});
					$query2->orWhere(function ($query3) use ($userId) {

						$query3->whereIn('inquiry.source_type_3', array("user-101", "user-102", "user-103", "user-104", "user-105"));
						$query3->where('inquiry.source_type_value_3', $userId);
					});

					$query2->orWhere(function ($query3) use ($userId) {

						$query3->whereIn('inquiry.source_type_4', array("user-101", "user-102", "user-103", "user-104", "user-105"));
						$query3->where('inquiry.source_type_value_4', $userId);
					});
				}
			}
		});
		$statusArray = isset($inquiryStatus[201]['for_user_ids']) ? $inquiryStatus[201]['for_user_ids'] : array(0);
		$query->whereIn('inquiry.status', $statusArray);
		$recordsTotal = $query->count();
		$overview['total_running'] = $recordsTotal;

		$query = Inquiry::query();
		$query->where(function ($query2) use ($userId, $typeOfProcess) {

			if ($typeOfProcess != 0) {

				if ($typeOfProcess == 1) {
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
				} else if ($typeOfProcess == 2) {
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
				} else if ($typeOfProcess == 3) {

					$query2->where(function ($query3) use ($userId) {

						$query3->whereIn('inquiry.source_type', array("user-101", "user-102", "user-103", "user-104", "user-105"));
						$query3->where('inquiry.source_type_value', $userId);
					});

					$query2->orWhere(function ($query3) use ($userId) {

						$query3->whereIn('inquiry.source_type_1', array("user-101", "user-102", "user-103", "user-104", "user-105"));
						$query3->where('inquiry.source_type_value_1', $userId);
					});
					$query2->orWhere(function ($query3) use ($userId) {

						$query3->whereIn('inquiry.source_type_2', array("user-101", "user-102", "user-103", "user-104", "user-105"));
						$query3->where('inquiry.source_type_value_2', $userId);
					});
					$query2->orWhere(function ($query3) use ($userId) {

						$query3->whereIn('inquiry.source_type_3', array("user-101", "user-102", "user-103", "user-104", "user-105"));
						$query3->where('inquiry.source_type_value_3', $userId);
					});

					$query2->orWhere(function ($query3) use ($userId) {

						$query3->whereIn('inquiry.source_type_4', array("user-101", "user-102", "user-103", "user-104", "user-105"));
						$query3->where('inquiry.source_type_value_4', $userId);
					});
				}
			}
		});
		$statusArray = isset($inquiryStatus[9]['for_user_ids']) ? $inquiryStatus[9]['for_user_ids'] : array(0);
		$query->whereIn('inquiry.status', $statusArray);
		$recordsTotal = $query->count();
		$overview['total_won'] = $recordsTotal;

		$query = Inquiry::query();
		$query->where(function ($query2) use ($userId, $typeOfProcess) {

			if ($typeOfProcess != 0) {

				if ($typeOfProcess == 1) {
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
				} else if ($typeOfProcess == 2) {
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
				} else if ($typeOfProcess == 3) {

					$query2->where(function ($query3) use ($userId) {

						$query3->whereIn('inquiry.source_type', array("user-101", "user-102", "user-103", "user-104", "user-105"));
						$query3->where('inquiry.source_type_value', $userId);
					});

					$query2->orWhere(function ($query3) use ($userId) {

						$query3->whereIn('inquiry.source_type_1', array("user-101", "user-102", "user-103", "user-104", "user-105"));
						$query3->where('inquiry.source_type_value_1', $userId);
					});
					$query2->orWhere(function ($query3) use ($userId) {

						$query3->whereIn('inquiry.source_type_2', array("user-101", "user-102", "user-103", "user-104", "user-105"));
						$query3->where('inquiry.source_type_value_2', $userId);
					});
					$query2->orWhere(function ($query3) use ($userId) {

						$query3->whereIn('inquiry.source_type_3', array("user-101", "user-102", "user-103", "user-104", "user-105"));
						$query3->where('inquiry.source_type_value_3', $userId);
					});

					$query2->orWhere(function ($query3) use ($userId) {

						$query3->whereIn('inquiry.source_type_4', array("user-101", "user-102", "user-103", "user-104", "user-105"));
						$query3->where('inquiry.source_type_value_4', $userId);
					});
				}
			}
		});
		$statusArray = array(101, 102);
		$query->whereIn('inquiry.status', $statusArray);
		$recordsTotal = $query->count();
		$overview['total_rejected'] = $recordsTotal;

		$jsonData = array(
			"draw" => intval($request['draw']),
			// for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
			"recordsTotal" => intval($recordsTotal),
			// total number of records
			"recordsFiltered" => intval($recordsFiltered),
			// total number of records after searching, if there is no searching then totalFiltered = totalData
			"data" => $viewData,
			// total data array
			"column4" => $column4,
			// total data array
			"column5" => $column5,
			// total data array+
			"overview" => $overview,
			// total data array
			'type' => $request->type,
			"quotationAmount" => priceLable($quotationTotal),
			"title" => $title,

		);
		return $jsonData;
	}
}