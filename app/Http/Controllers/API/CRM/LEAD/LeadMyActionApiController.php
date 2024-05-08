<?php

namespace App\Http\Controllers\API\CRM\LEAD;

use App\Models\DebugLog;
// use PDF;
// use Dompdf\Dompdf;
use Illuminate\Http\Request;
use App\Models\LeadMeeting;
use App\Models\CRMSettingMeetingType;
use App\Models\ChannelPartner;
// use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\LeadTask;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\LeadSource;
use App\Models\LeadContact;
use Illuminate\Support\Facades\DB;
use App\Models\CRMSettingSourceType;
use App\Http\Controllers\Controller;
use App\Models\CRMSettingSource;
use App\Models\CRMSettingCompetitors;
use App\Models\User;
use App\Models\Lead;
use App\Models\Wltrn_Quotation;
use App\Models\LeadCall;
use App\Models\QuotRequest;
use App\Models\CRMSettingCallType;
use App\Models\CRMSettingMeetingTitle;
use App\Models\LeadMeetingParticipant;
use App\Models\Wltrn_QuotItemdetail;
use Illuminate\Support\Facades\File;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Quotation\QuotationMasterController;
use App\Http\Controllers\CRM\LeadQuotationController;
use PhpParser\Node\Stmt\Break_;

// use Illuminate\Http\Request;
class LeadMyActionApiController extends Controller
{

    public function __construct()
    {

        $this->middleware(function ($request, $next) {

			$tabCanAccessBy = array(0, 1, 2, 9, 11, 101, 102, 103, 104, 105, 202, 302);

			if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
				$response = errorRes("You Don't Have An Access To This Page", 401);
				return response()->json($response, $response['status_code'])->header('Content-Type', 'application/json');
			}

			return $next($request);
		});
    }

    function myActionAjax(Request $request)
    {
        $isSalePerson = isSalePerson();
		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		$isAdmin = isAdmin();
        if ($isSalePerson == 1) {
			$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
		}
        try {
            $Contact_ids = LeadContact::select('id')->where(DB::raw("SUBSTRING_INDEX(lead_contacts.type_detail, '-', -1)"), '=', Auth::user()->id)->get();
            $status_arr = array(104, 105);
            //////   Today Call Action //////
            $req_startdate = isset($request->start_date) ? $request->start_date : date('Y-m-01');
		    $req_enddate = isset($request->end_date) ? $request->end_date : date('Y-m-t');
            $startDate = date('Y-m-d', strtotime($req_startdate));
		    $endDate = date('Y-m-d', strtotime($req_enddate));
		    $curruntDate = date('Y-m-d');

            $columns = array(
                0 => 'lead_calls.*',
                1 => 'lead_calls.purpose as title',
                2 => 'leads.first_name',
                3 => 'leads.last_name',
            );

            $query = LeadCall::query();
            $query->leftJoin('leads', 'leads.id', '=', 'lead_calls.lead_id');
            $query->where('lead_calls.is_closed', 0);
            $query->whereNotIn('leads.status', $status_arr);

            if (in_array(Auth::user()->type, [101, 102, 103, 104, 105, 202, 302])) {
                $query->whereIn('lead_calls.contact_name', $Contact_ids);
                $query->orWhere('lead_calls.user_id', Auth::user()->id);
            } else{
                $query->where('lead_calls.user_id', Auth::user()->id);
            }

            // $query->whereDate('lead_calls.call_schedule', '>=', $curruntDate);
			// $query->whereDate('lead_calls.call_schedule', '<=', $curruntDate);
            $query->select($columns);
            $data = $query->get();

            foreach ($data as $today_call_key => $today_call_value) {
                $LeadType = Lead::select('is_deal', 'status')->find($today_call_value['lead_id']);
                if ($LeadType) {
                    $LeadType = json_encode($LeadType);
                    $LeadType = json_decode($LeadType, true);
                    if ($LeadType['is_deal'] == 0) {
                        $data[$today_call_key]['lead_type'] = "Lead";
                    } else if ($LeadType['is_deal'] == 1) {
                        $data[$today_call_key]['lead_type'] = "Deal";
                    }
                    $LeadStatus = getLeadStatus()[$LeadType['status']]['name'];
                    $data[$today_call_key]['lead_status_label'] = $LeadStatus;
                    $data[$today_call_key]['lead_status'] = $LeadType['status'];
                }

                $CRMSettingCallType = CRMSettingCallType::select('id', 'name as text')->find($today_call_value['type_id']);
                if ($CRMSettingCallType) {
                    $CRMSettingCallType = json_encode($CRMSettingCallType);
                    $CRMSettingCallType = json_decode($CRMSettingCallType, true);
                    $data[$today_call_key]['call_type'] = $CRMSettingCallType;
                }

                $LeadContact = LeadContact::select('lead_contacts.id','lead_contacts.phone_number', DB::raw("CONCAT(lead_contacts.first_name,' ',lead_contacts.last_name) AS text"))->find($today_call_value['contact_name']);
                if ($LeadContact) {
                    $LeadContact = json_encode($LeadContact);
                    $LeadContact = json_decode($LeadContact, true);
                    $data[$today_call_key]['contact_name'] = $LeadContact;
                }
                $data[$today_call_key]['action_type'] = "call";

                $data[$today_call_key]['date'] = date('d-m-Y', strtotime($today_call_value['call_schedule']));
				$data[$today_call_key]['time'] = date('H:i A', strtotime($today_call_value['call_schedule']));
            }


            //////   Today Meeting Action //////
            $today_meetings_columns = array(
                0 => 'lead_meetings.*',
                1 => 'crm_setting_meeting_title.name as title',
                2 => 'leads.first_name',
                3 => 'leads.last_name',
            );

            $today_meetings_query = LeadMeeting::query();
            $today_meetings_query->leftJoin('crm_setting_meeting_title', 'crm_setting_meeting_title.id', '=', 'lead_meetings.title_id');
            $today_meetings_query->leftJoin('leads', 'leads.id', '=', 'lead_meetings.lead_id');
            $today_meetings_query->where('lead_meetings.is_closed', 0);
            $today_meetings_query->whereNotIn('leads.status', $status_arr);
            if (in_array(Auth::user()->type, [101, 102, 103, 104, 105, 202, 302])) {
                $Meeting_ids = LeadMeetingParticipant::select('meeting_id')->whereIn('reference_id', $Contact_ids)->get();
                $query->whereIn('lead_meetings.id', $Meeting_ids);
            } else{
                $query->where('lead_meetings.user_id', Auth::user()->id);
            }
            // $today_meetings_query->whereDate('lead_meetings.meeting_date_time', '>=', $curruntDate);
			// $today_meetings_query->whereDate('lead_meetings.meeting_date_time', '<=', $curruntDate);
            $today_meetings_query->select($today_meetings_columns);
            $data1 = $today_meetings_query->get();
            foreach ($data1 as $today_meeting_key => $today_meeting_value) {
                $LeadType = Lead::select('is_deal', 'status')->find($today_meeting_value['lead_id']);
                if ($LeadType) {
                    if ($LeadType['is_deal'] == 0) {
                        $data1[$today_meeting_key]['lead_type'] = "Lead";
                    } else if ($LeadType['is_deal'] == 1) {
                        $data1[$today_meeting_key]['lead_type'] = "Deal";
                    }

                    $LeadStatus = getLeadStatus()[$LeadType['status']]['name'];
                    $data1[$today_meeting_key]['lead_status_label'] = $LeadStatus;
                    $data1[$today_meeting_key]['lead_status'] = $LeadType['status'];
                }

                $CRMSettingMeetingTitle = CRMSettingMeetingTitle::select('id', 'name as text')->find($today_meeting_value['title_id']);

                if ($CRMSettingMeetingTitle) {
                    $CRMSettingMeetingTitle = json_encode($CRMSettingMeetingTitle);
                    $CRMSettingMeetingTitle = json_decode($CRMSettingMeetingTitle, true);
                    $data1[$today_meeting_key]['title'] = $CRMSettingMeetingTitle['text'];
                }

                $TodayLeadMeetingParticipant = LeadMeetingParticipant::where('meeting_id', $today_meeting_value['id'])->orderby('id', 'asc')->get();
                $TodayLeadMeetingParticipant = json_encode($TodayLeadMeetingParticipant);
                $TodayLeadMeetingParticipant = json_decode($TodayLeadMeetingParticipant, true);


                $LeadMeetingtype = CRMSettingMeetingType::select('id', 'name as text');
                $LeadMeetingtype->where('crm_setting_meeting_type.status', 1);
                $LeadMeetingtype->where('crm_setting_meeting_type.id', $today_meeting_value['type_id']);
                $LeadMeetingtype = $LeadMeetingtype->first();
                if ($LeadMeetingtype) {
                    $data1[$today_meeting_key]['type'] = $LeadMeetingtype;
                } else {
                    $data1[$today_meeting_key]['type'] = (object) array();
                }


                $UsersId = array();
                $ContactIds = array();

                foreach ($TodayLeadMeetingParticipant as $key => $value) {
                    if ($value['type'] == "users") {
                        $UsersId[] = $value['reference_id'];
                    }
                }

                foreach ($TodayLeadMeetingParticipant as $key => $value) {

                    if ($value['type'] == "lead_contacts") {
                        $ContactIds[] = $value['reference_id'];
                    }
                }

                $UserResponse = array();


                if (count($UsersId) > 0) {

                    $User = User::select('users.id', 'users.type', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
                    $User->whereIn('users.id', $UsersId);
                    $User = $User->get();
                    $getAllUserTypes = getAllUserTypes();

                    if (count($User) > 0) {
                        foreach ($User as $User_key => $User_value) {
                            $UserResponse[$User_key]['id'] = "users-" . $User_value['id'];
                            $UserResponse[$User_key]['text'] = $getAllUserTypes[$User_value['type']]['short_name'] . " - " . $User_value['full_name'];
                        }
                    }
                }



                if (count($ContactIds) > 0) {
                    $LeadContact = LeadContact::select('lead_contacts.id', 'lead_contacts.first_name', 'lead_contacts.last_name', DB::raw("CONCAT(lead_contacts.first_name,' ',lead_contacts.last_name) AS full_name"));

                    $LeadContact->where('lead_contacts.id', $ContactIds);
                    $LeadContact = $LeadContact->get();

                    if (count($LeadContact) > 0) {
                        foreach ($LeadContact as $User_key => $User_value) {
                            $length = count($UserResponse);
                            $UserResponse[$length]['id'] = "lead_contacts-" . $User_value['id'];
                            $UserResponse[$length]['text'] = "Contact - " . $User_value['full_name'];
                        }
                    }
                }
                $data1[$today_meeting_key]['meeting_participant'] = $UserResponse;
                $data1[$today_meeting_key]['action_type'] = "meeting";

                $data1[$today_meeting_key]['date'] = date('d-m-Y', strtotime($today_meeting_value['meeting_date_time']));
				$data1[$today_meeting_key]['time'] = date('H:i A', strtotime($today_meeting_value['meeting_date_time']));
            }

            //////   Today Task Action //////
            $today_task_columns = array(
                0 => 'lead_tasks.*',
                1 => 'lead_tasks.task as title',
                2 => 'leads.first_name',
                3 => 'leads.last_name',
            );


            $today_task_query = LeadTask::query();
            $today_task_query->leftJoin('leads', 'leads.id', '=', 'lead_tasks.lead_id');
            $today_task_query->where('lead_tasks.is_closed', 0);
            $today_task_query->whereNotIn('leads.status', $status_arr);
            $today_task_query->where(function ($query1) {
                $query1->orWhere('lead_tasks.assign_to', Auth::user()->id);
            });
            // $today_meetings_query->whereDate('lead_tasks.due_date_time', '>=', $curruntDate);
			// $today_meetings_query->whereDate('lead_tasks.due_date_time', '<=', $curruntDate);
            $today_task_query->select($today_task_columns);
            $data2 = $today_task_query->get();
            foreach ($data2 as $today_task_key => $today_task_value) {
                $LeadType = Lead::select('is_deal', 'status')->find($today_task_value['lead_id']);
                if ($LeadType) {
                    if ($LeadType['is_deal'] == 0) {
                        $data2[$today_task_key]['lead_type'] = "Lead";
                    } else if ($LeadType['is_deal'] == 1) {
                        $data2[$today_task_key]['lead_type'] = "Deal";
                    }
                    $LeadStatus = getLeadStatus()[$LeadType['status']]['name'];
                    $data2[$today_task_key]['lead_status'] = $LeadType['status'];
                    $data2[$today_task_key]['lead_status_label'] = $LeadStatus;
                }
                $Assign_User = User::select('users.id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"));
                $Assign_User->where('users.id', $today_task_value['assign_to']);
                $Assign_User = $Assign_User->first();
                $data2[$today_task_key]['assign_to'] = $Assign_User;

                $data2[$today_task_key]['action_type'] = "task";

                $data2[$today_task_key]['date'] = date('d-m-Y', strtotime($today_task_value['due_date_time']));
				$data2[$today_task_key]['time'] = date('H:i A', strtotime($today_task_value['due_date_time']));
            }


            //////   Previous Call Action //////
            $current_start_date = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . " -1 days"));

            $columns = array(
                0 => 'lead_calls.*',
                1 => 'lead_calls.purpose as title',
                2 => 'leads.first_name',
                3 => 'leads.last_name',
            );


            $previous_call_query = LeadCall::query();
            $previous_call_query->leftJoin('leads', 'leads.id', '=', 'lead_calls.lead_id');
            $previous_call_query->where('lead_calls.user_id', Auth::user()->id);
            $previous_call_query->where('lead_calls.call_schedule', '<', $current_start_date);
            // $previous_call_query->whereDate('lead_calls.call_schedule', '>=', $startDate);
			// $previous_call_query->whereDate('lead_calls.call_schedule', '<=', $endDate);
            $previous_call_query->where('lead_calls.is_closed', 0);
            $previous_call_query->whereNotIn('leads.status', $status_arr);
            $previous_call_query->select($columns);
            $data3 = $previous_call_query->get();
            foreach ($data3 as $previous_call_key => $previous_call_value) {

                $LeadType = Lead::select('is_deal', 'status')->find($previous_call_value['lead_id']);
                if ($LeadType) {
                    if ($LeadType['is_deal'] == 0) {
                        $data3[$previous_call_key]['lead_type'] = "Lead";
                    } else if ($LeadType['is_deal'] == 1) {
                        $data3[$previous_call_key]['lead_type'] = "Deal";
                    }
                    $LeadStatus = getLeadStatus()[$LeadType['status']]['name'];
                    $data3[$previous_call_key]['lead_status_label'] = $LeadStatus;
                    $data3[$previous_call_key]['lead_status'] = $LeadType['status'];
                }

                $CRMSettingCallType = CRMSettingCallType::select('id', 'name as text')->find($previous_call_value['type_id']);
                if ($CRMSettingCallType) {
                    $CRMSettingCallType = json_encode($CRMSettingCallType);
                    $CRMSettingCallType = json_decode($CRMSettingCallType, true);
                    $data3[$previous_call_key]['call_type'] = $CRMSettingCallType;
                }

                $LeadContact = LeadContact::select('lead_contacts.id','lead_contacts.phone_number', DB::raw("CONCAT(lead_contacts.first_name,' ',lead_contacts.last_name) AS text"))->find($previous_call_value['contact_name']);
                if ($LeadContact) {
                    $LeadContact = json_encode($LeadContact);
                    $LeadContact = json_decode($LeadContact, true);
                    $data3[$previous_call_key]['contact_name'] = $LeadContact;
                }

                $data3[$previous_call_key]['action_type'] = "Call";

                $data3[$previous_call_key]['date'] = date('d-m-Y', strtotime($previous_call_value['call_schedule']));
				$data3[$previous_call_key]['time'] = date('H:i A', strtotime($previous_call_value['call_schedule']));
            }

            //////   Previous Meeting Action //////

            $columns = array(
                0 => 'lead_meetings.*',
                1 => 'crm_setting_meeting_title.name as title',
                2 => 'leads.first_name',
                3 => 'leads.last_name',
            );


            $previous_meeting_query = LeadMeeting::query();
            $previous_meeting_query->leftJoin('crm_setting_meeting_title', 'crm_setting_meeting_title.id', '=', 'lead_meetings.title_id');
            $previous_meeting_query->leftJoin('leads', 'leads.id', '=', 'lead_meetings.lead_id');
            $previous_meeting_query->where('lead_meetings.user_id', Auth::user()->id);
            // $previous_meeting_query->whereDate('lead_meetings.meeting_date_time', '>=', $startDate);
			// $previous_meeting_query->whereDate('lead_meetings.meeting_date_time', '<=', $endDate);
            $previous_meeting_query->where('lead_meetings.is_closed', 0);
            $previous_meeting_query->whereNotIn('leads.status', $status_arr);
            $previous_meeting_query->select($columns);
            $data4 = $previous_meeting_query->get();
            foreach ($data4 as $previous_meeting_key => $previous_meeting_value) {
                $LeadType = Lead::select('is_deal', 'status')->find($previous_meeting_value['lead_id']);
                if ($LeadType) {
                    $LeadType = json_encode($LeadType);
                    $LeadType = json_decode($LeadType, true);
                    if ($LeadType['is_deal'] == 0) {
                        $data4[$previous_meeting_key]['lead_type'] = "Lead";
                    } else if ($LeadType['is_deal'] == 1) {
                        $data4[$previous_meeting_key]['lead_type'] = "Deal";
                    }
                    $LeadStatus = getLeadStatus()[$LeadType['status']]['name'];
                    $data4[$previous_meeting_key]['lead_status_label'] = $LeadStatus;
                    $data4[$previous_meeting_key]['lead_status'] = $LeadType['status'];
                }

                $CRMSettingMeetingTitle = CRMSettingMeetingTitle::select('id', 'name as text')->find($previous_meeting_value['title_id']);

                if ($CRMSettingMeetingTitle) {
                    $CRMSettingMeetingTitle = json_encode($CRMSettingMeetingTitle);
                    $CRMSettingMeetingTitle = json_decode($CRMSettingMeetingTitle, true);
                    $data4[$previous_meeting_key]['title'] = $CRMSettingMeetingTitle['text'];
                }

                $PreviousLeadMeetingParticipant = LeadMeetingParticipant::where('meeting_id', $previous_meeting_value['id'])->orderby('id', 'asc')->get();
                $PreviousLeadMeetingParticipant = json_encode($PreviousLeadMeetingParticipant);
                $PreviousLeadMeetingParticipant = json_decode($PreviousLeadMeetingParticipant, true);


                $LeadMeetingtype = CRMSettingMeetingType::select('id', 'name as text');
                $LeadMeetingtype->where('crm_setting_meeting_type.status', 1);
                $LeadMeetingtype->where('crm_setting_meeting_type.id', $previous_meeting_value['type_id']);
                $LeadMeetingtype = $LeadMeetingtype->first();
                if ($LeadMeetingtype) {
                    $LeadMeetingtype = json_encode($LeadMeetingtype);
                    $LeadMeetingtype = json_decode($LeadMeetingtype, true);
                    $data4[$previous_meeting_key]['type'] = $LeadMeetingtype;
                } else {
                    $data4[$previous_meeting_key]['type'] = (object) array();
                }

                $UsersId = array();
                $ContactIds = array();

                foreach ($PreviousLeadMeetingParticipant as $key => $value) {

                    if ($value['type'] == "users") {
                        $UsersId[] = $value['reference_id'];
                    }
                }


                foreach ($PreviousLeadMeetingParticipant as $key => $value) {

                    if ($value['type'] == "lead_contacts") {
                        $ContactIds[] = $value['reference_id'];
                    }
                }

                $UserResponse = array();


                if (count($UsersId) > 0) {

                    $User = User::select('users.id', 'users.type', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
                    $User->whereIn('users.id', $UsersId);
                    $User = $User->get();
                    $getAllUserTypes = getAllUserTypes();

                    if (count($User) > 0) {
                        foreach ($User as $User_key => $User_value) {
                            $UserResponse[$User_key]['id'] = "users-" . $User_value['id'];
                            $UserResponse[$User_key]['text'] = $getAllUserTypes[$User_value['type']]['short_name'] . " - " . $User_value['full_name'];
                        }
                    }
                }



                if (count($ContactIds) > 0) {
                    $LeadContact = LeadContact::select('lead_contacts.id', 'lead_contacts.first_name', 'lead_contacts.last_name', DB::raw("CONCAT(lead_contacts.first_name,' ',lead_contacts.last_name) AS full_name"));

                    $LeadContact->where('lead_contacts.id', $ContactIds);
                    $LeadContact = $LeadContact->get();

                    if (count($LeadContact) > 0) {
                        foreach ($LeadContact as $User_key => $User_value) {
                            $length = count($UserResponse);
                            $UserResponse[$length]['id'] = "lead_contacts-" . $User_value['id'];
                            $UserResponse[$length]['text'] = "Contact - " . $User_value['full_name'];
                        }
                    }
                }

                $data4[$previous_meeting_key]['meeting_participant'] = $UserResponse;
                $data4[$previous_meeting_key]['action_type'] = "meeting";

                $data4[$previous_meeting_key]['date'] = date('d-m-Y', strtotime($previous_meeting_value['meeting_date_time']));
				$data4[$previous_meeting_key]['time'] = date('H:i A', strtotime($previous_meeting_value['meeting_date_time']));
            }
            //////   Previous Task Action //////

            $columns = array(
                0 => 'lead_tasks.*',
                1 => 'lead_tasks.task as title',
                2 => 'leads.first_name',
                3 => 'leads.last_name',
            );


            $previous_task_query = LeadTask::query();
            $previous_task_query->leftJoin('leads', 'leads.id', '=', 'lead_tasks.lead_id');
            $previous_task_query->where(function ($query1) {
                $query1->orWhere('lead_tasks.user_id', Auth::user()->id);
                $query1->orWhere('lead_tasks.assign_to', Auth::user()->id);
            });
            $previous_task_query->where('lead_tasks.is_closed', 0);
            $previous_task_query->whereNotIn('leads.status', $status_arr);
            // $previous_task_query->whereDate('lead_tasks.due_date_time', '>=', $startDate);
			// $previous_task_query->whereDate('lead_tasks.due_date_time', '<=', $endDate);
            $previous_task_query->select($columns);
            $data5 = $previous_task_query->get();
            foreach ($data5 as $previous_task_key => $previous_task_value) {
                $LeadType = Lead::select('is_deal', 'status')->find($previous_task_value['lead_id']);
                if ($LeadType) {
                    $LeadType = json_encode($LeadType);
                    $LeadType = json_decode($LeadType, true);
                    if ($LeadType['is_deal'] == 0) {
                        $data5[$previous_task_key]['lead_type'] = "Lead";
                    } else if ($LeadType['is_deal'] == 1) {
                        $data5[$previous_task_key]['lead_type'] = "Deal";
                    }
                    $LeadStatus = getLeadStatus()[$LeadType['status']]['name'];
                    $data5[$previous_task_key]['lead_status_label'] = $LeadStatus;
                    $data5[$previous_task_key]['lead_status'] = $LeadType['status'];
                }

                $Assign_User = User::select('users.id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"));
                $Assign_User->where('users.id', $previous_task_value['assign_to']);
                $Assign_User = $Assign_User->first();
                $data5[$previous_task_key]['assign_to'] = $Assign_User;

                $data5[$previous_task_key]['action_type'] = "task";

                $data5[$previous_task_key]['date'] = date('d-m-Y', strtotime($previous_task_value['due_date_time']));
				$data5[$previous_task_key]['time'] = date('H:i A', strtotime($previous_task_value['due_date_time']));
            }

            
            $Quot_columns = array(
                'quotation_request.deal_id',
                'quotation_request.title',
                'quotation_request.assign_to',
                'quotation_request.group_id',
                'wltrn_quotation.customer_name',
                'users.first_name',
                'users.last_name',
                'users.type',
            );
    
            $quot_query = QuotRequest::select($Quot_columns);
            $quot_query->selectRaw('GROUP_CONCAT(quotation_request.id) as req_id');
            $quot_query->leftJoin('wltrn_quotation', 'wltrn_quotation.id', '=', 'quotation_request.quot_id');
            $quot_query->leftJoin('users', 'users.id', '=', 'quotation_request.assign_to');
            if($isAdminOrCompanyAdmin == 0){
                $quot_query->where('quotation_request.assign_to', Auth::user()->id);
            }
            $quot_query->where('quotation_request.status', 0);
            $quot_query->where('quotation_request.type', 'DISCOUNT');
            // $quot_query->whereDate('quotation_request.created_at', '>=', $startDate);
			// $quot_query->whereDate('quotation_request.created_at', '<=', $endDate);
            $quot_query->groupBy($Quot_columns);
            $quot_query = $quot_query->get();
    
            if($quot_query){
                $quot_query = json_decode(json_encode($quot_query), true);
    
                foreach ($quot_query as $quotkey => $quotvalue) {

                    $quot_req_query = QuotRequest::select('quotation_request.status','quotation_request.created_at');
                    $quot_req_query->selectRaw('CONCAT(created.first_name," ",created.last_name) AS request_created_name');
                    $quot_req_query->leftJoin('users as created', 'created.id', '=', 'quotation_request.entryby');
                    $quot_req_query->where('quotation_request.type', 'DISCOUNT');
                    $quot_req_query->whereIn('quotation_request.id',[$quotvalue['req_id']]);
                    $quot_req_query = $quot_req_query->first();

                    $quot_query[$quotkey]['status'] = $quot_req_query->status;
                    $quot_query[$quotkey]['created_at'] = $quot_req_query->created_at;
                    $quot_query[$quotkey]['assign_person'] = $quot_req_query->request_created_name;
                    $quot_query[$quotkey]['action_type'] = 'Quotation';
                    $quot_query[$quotkey]['date'] = date('d-m-Y', strtotime($quot_req_query->created_at));
				    $quot_query[$quotkey]['time'] = date('H:i A', strtotime($quot_req_query->created_at));

                    if(isChannelPartner($quotvalue['type'])){
                        $ChannelPartner = ChannelPartner::where('user_id',$quotvalue['assign_to'])->first();
                        if($ChannelPartner){
                            $quot_query[$quotkey]['first_name'] = $ChannelPartner->firm_name;
                            $quot_query[$quotkey]['last_name'] = "";
                        }
                    }else{
                        $quot_query[$quotkey]['first_name'] = $quotvalue['first_name'];
                        $quot_query[$quotkey]['last_name'] = $quotvalue['last_name'];
                    }
                    $quot_query[$quotkey]['type'] = $quotvalue['type'];
                }
            }

            $response = successRes();
            $response['data']['today_calls'] = $data;
            $response['data']['today_meetings'] = $data1;
            $response['data']['today_tasks'] = $data2;
            $response['data']['previous_calls'] = $data3;
            $response['data']['previous_meetings'] = $data4;
            $response['data']['previous_tasks'] = $data5;
            $response['data']['today_quot_request'] = $quot_query;
        } catch (QueryException $ex) {
            $response = errorRes();
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function teamActionAjax(Request $request){
        $isSalePerson = isSalePerson();
		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
        if ($isSalePerson == 1) {
			$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
		}
        
        try {
            //////   Today Call Action //////
            $current_start_date = $request->current_date . " 00:00:00";
            $current_start_date = date('Y-m-d H:i:s', strtotime($current_start_date . " -5 hours"));
            $current_start_date = date('Y-m-d H:i:s', strtotime($current_start_date . " -30 minutes"));

            $current_end_date = $request->current_date . " 23:59:59";
            $current_end_date = date('Y-m-d H:i:s', strtotime($current_end_date . " -5 hours"));
            $current_end_date = date('Y-m-d H:i:s', strtotime($current_end_date . " -30 minutes"));

            $columns = array(
                0 => 'lead_calls.*',
                1 => 'lead_calls.purpose as title',
                2 => 'leads.first_name',
                3 => 'leads.last_name',
            );

            $query = LeadCall::query();
            $query->leftJoin('leads', 'leads.id', '=', 'lead_calls.lead_id');
            $query->where('lead_calls.is_closed', 0);
            if($request->employee_id != 0){
                $query->where('lead_calls.user_id', $request->employee_id);
            }
            if ($isSalePerson == 1) {
                $query->whereIn('lead_calls.user_id', $childSalePersonsIds);
            }
            $query->whereBetween('lead_calls.call_schedule', [$current_start_date, $current_end_date]);
            $query->select($columns);
            $data = $query->get();

            foreach ($data as $today_call_key => $today_call_value) {
                $LeadType = Lead::select('is_deal', 'status')->find($today_call_value['lead_id']);
                if ($LeadType) {
                    $LeadType = json_encode($LeadType);
                    $LeadType = json_decode($LeadType, true);
                    if ($LeadType['is_deal'] == 0) {
                        $data[$today_call_key]['lead_type'] = "Lead";
                    } else if ($LeadType['is_deal'] == 1) {
                        $data[$today_call_key]['lead_type'] = "Deal";
                    }
                    $LeadStatus = getLeadStatus()[$LeadType['status']]['name'];
                    $data[$today_call_key]['lead_status_label'] = $LeadStatus;
                    $data[$today_call_key]['lead_status'] = $LeadType['status'];
                }

                $CRMSettingCallType = CRMSettingCallType::select('id', 'name as text')->find($today_call_value['type_id']);
                if ($CRMSettingCallType) {
                    $CRMSettingCallType = json_encode($CRMSettingCallType);
                    $CRMSettingCallType = json_decode($CRMSettingCallType, true);
                    $data[$today_call_key]['call_type'] = $CRMSettingCallType;
                }

                $LeadContact = LeadContact::select('lead_contacts.id','lead_contacts.phone_number', DB::raw("CONCAT(lead_contacts.first_name,' ',lead_contacts.last_name) AS text"))->find($today_call_value['contact_name']);
                if ($LeadContact) {
                    $LeadContact = json_encode($LeadContact);
                    $LeadContact = json_decode($LeadContact, true);
                    $data[$today_call_key]['contact_name'] = $LeadContact;
                }
                $data[$today_call_key]['action_type'] = "call";

                $data[$today_call_key]['date'] = date('d-m-Y', strtotime($today_call_value['call_schedule']));
				$data[$today_call_key]['time'] = date('H:i A', strtotime($today_call_value['call_schedule']));
            }


            //////   Today Meeting Action //////
            $today_meetings_columns = array(
                0 => 'lead_meetings.*',
                1 => 'crm_setting_meeting_title.name as title',
                2 => 'leads.first_name',
                3 => 'leads.last_name',
            );

            $today_meetings_query = LeadMeeting::query();
            $today_meetings_query->leftJoin('crm_setting_meeting_title', 'crm_setting_meeting_title.id', '=', 'lead_meetings.title_id');
            $today_meetings_query->leftJoin('leads', 'leads.id', '=', 'lead_meetings.lead_id');
            $today_meetings_query->where('lead_meetings.is_closed', 0);
            if($request->employee_id != 0){
                $today_meetings_query->where('lead_meetings.user_id', $request->employee_id);
            }
            if ($isSalePerson == 1) {
                $today_meetings_query->whereIn('lead_meetings.user_id', $childSalePersonsIds);
            }
            $query->whereBetween('lead_meetings.meeting_date_time', [$current_start_date, $current_end_date]);
            $today_meetings_query->select($today_meetings_columns);
            $data1 = $today_meetings_query->get();
            foreach ($data1 as $today_meeting_key => $today_meeting_value) {
                $LeadType = Lead::select('is_deal', 'status')->find($today_meeting_value['lead_id']);
                if ($LeadType) {
                    if ($LeadType['is_deal'] == 0) {
                        $data1[$today_meeting_key]['lead_type'] = "Lead";
                    } else if ($LeadType['is_deal'] == 1) {
                        $data1[$today_meeting_key]['lead_type'] = "Deal";
                    }

                    $LeadStatus = getLeadStatus()[$LeadType['status']]['name'];
                    $data1[$today_meeting_key]['lead_status_label'] = $LeadStatus;
                    $data1[$today_meeting_key]['lead_status'] = $LeadType['status'];
                }

                $CRMSettingMeetingTitle = CRMSettingMeetingTitle::select('id', 'name as text')->find($today_meeting_value['title_id']);

                if ($CRMSettingMeetingTitle) {
                    $CRMSettingMeetingTitle = json_encode($CRMSettingMeetingTitle);
                    $CRMSettingMeetingTitle = json_decode($CRMSettingMeetingTitle, true);
                    $data1[$today_meeting_key]['title'] = $CRMSettingMeetingTitle['text'];
                }

                $TodayLeadMeetingParticipant = LeadMeetingParticipant::where('meeting_id', $today_meeting_value['id'])->orderby('id', 'asc')->get();
                $TodayLeadMeetingParticipant = json_encode($TodayLeadMeetingParticipant);
                $TodayLeadMeetingParticipant = json_decode($TodayLeadMeetingParticipant, true);


                $LeadMeetingtype = CRMSettingMeetingType::select('id', 'name as text');
                $LeadMeetingtype->where('crm_setting_meeting_type.status', 1);
                $LeadMeetingtype->where('crm_setting_meeting_type.id', $today_meeting_value['type_id']);
                $LeadMeetingtype = $LeadMeetingtype->first();
                if ($LeadMeetingtype) {
                    $data1[$today_meeting_key]['type'] = $LeadMeetingtype;
                } else {
                    $data1[$today_meeting_key]['type'] = (object) array();
                }


                $UsersId = array();
                $ContactIds = array();

                foreach ($TodayLeadMeetingParticipant as $key => $value) {
                    if ($value['type'] == "users") {
                        $UsersId[] = $value['reference_id'];
                    }
                }

                foreach ($TodayLeadMeetingParticipant as $key => $value) {

                    if ($value['type'] == "lead_contacts") {
                        $ContactIds[] = $value['reference_id'];
                    }
                }

                $UserResponse = array();


                if (count($UsersId) > 0) {

                    $User = User::select('users.id', 'users.type', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
                    $User->whereIn('users.id', $UsersId);
                    $User = $User->get();
                    $getAllUserTypes = getAllUserTypes();

                    if (count($User) > 0) {
                        foreach ($User as $User_key => $User_value) {
                            $UserResponse[$User_key]['id'] = "users-" . $User_value['id'];
                            $UserResponse[$User_key]['text'] = $getAllUserTypes[$User_value['type']]['short_name'] . " - " . $User_value['full_name'];
                        }
                    }
                }



                if (count($ContactIds) > 0) {
                    $LeadContact = LeadContact::select('lead_contacts.id', 'lead_contacts.first_name', 'lead_contacts.last_name', DB::raw("CONCAT(lead_contacts.first_name,' ',lead_contacts.last_name) AS full_name"));

                    $LeadContact->where('lead_contacts.id', $ContactIds);
                    $LeadContact = $LeadContact->get();

                    if (count($LeadContact) > 0) {
                        foreach ($LeadContact as $User_key => $User_value) {
                            $length = count($UserResponse);
                            $UserResponse[$length]['id'] = "lead_contacts-" . $User_value['id'];
                            $UserResponse[$length]['text'] = "Contact - " . $User_value['full_name'];
                        }
                    }
                }
                $data1[$today_meeting_key]['meeting_participant'] = $UserResponse;
                $data1[$today_meeting_key]['action_type'] = "meeting";

                $data1[$today_meeting_key]['date'] = date('d-m-Y', strtotime($today_meeting_value['meeting_date_time']));
				$data1[$today_meeting_key]['time'] = date('H:i A', strtotime($today_meeting_value['meeting_date_time']));
            }

            //////   Today Task Action //////
            $today_task_columns = array(
                0 => 'lead_tasks.*',
                1 => 'lead_tasks.task as title',
                2 => 'leads.first_name',
                3 => 'leads.last_name',
            );


            $today_task_query = LeadTask::query();
            $today_task_query->leftJoin('leads', 'leads.id', '=', 'lead_tasks.lead_id');
            $today_task_query->where('lead_tasks.is_closed', 0);
            if($request->employee_id != 0){
                $today_task_query->where('lead_tasks.user_id', $request->employee_id);
            }
            if ($isSalePerson == 1) {
                $today_task_query->whereIn('lead_tasks.user_id', $childSalePersonsIds);
            }
            $today_task_query->whereBetween('lead_tasks.due_date_time', [$current_start_date, $current_end_date]);
            $today_task_query->select($today_task_columns);
            $data2 = $today_task_query->get();
            foreach ($data2 as $today_task_key => $today_task_value) {
                $LeadType = Lead::select('is_deal', 'status')->find($today_task_value['lead_id']);
                if ($LeadType) {
                    if ($LeadType['is_deal'] == 0) {
                        $data2[$today_task_key]['lead_type'] = "Lead";
                    } else if ($LeadType['is_deal'] == 1) {
                        $data2[$today_task_key]['lead_type'] = "Deal";
                    }
                    $LeadStatus = getLeadStatus()[$LeadType['status']]['name'];
                    $data2[$today_task_key]['lead_status'] = $LeadType['status'];
                    $data2[$today_task_key]['lead_status_label'] = $LeadStatus;
                }
                $Assign_User = User::select('users.id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"));
                $Assign_User->where('users.id', $today_task_value['assign_to']);
                $Assign_User = $Assign_User->first();
                $data2[$today_task_key]['assign_to'] = $Assign_User;

                $data2[$today_task_key]['action_type'] = "task";

                $data2[$today_task_key]['date'] = date('d-m-Y', strtotime($today_task_value['due_date_time']));
				$data2[$today_task_key]['time'] = date('H:i A', strtotime($today_task_value['due_date_time']));
            }


            //////   Previous Call Action //////
            $current_start_date = $request->current_date . " 00:00:00";
            $current_start_date = date('Y-m-d H:i:s', strtotime($current_start_date . " -5 hours"));
            $current_start_date = date('Y-m-d H:i:s', strtotime($current_start_date . " -30 minutes"));

            $columns = array(
                0 => 'lead_calls.*',
                1 => 'lead_calls.purpose as title',
                2 => 'leads.first_name',
                3 => 'leads.last_name',
            );


            $previous_call_query = LeadCall::query();
            $previous_call_query->leftJoin('leads', 'leads.id', '=', 'lead_calls.lead_id');
            if ($isSalePerson == 1) {
                $previous_call_query->whereIn('lead_calls.user_id', $childSalePersonsIds);
            }
            $previous_call_query->where('lead_calls.call_schedule', '<', $current_start_date);
            $previous_call_query->where('lead_calls.is_closed', 0);
            if($request->employee_id != 0){
                $previous_call_query->where('lead_calls.user_id', $request->employee_id);
            }
            $previous_call_query->select($columns);
            $data3 = $previous_call_query->get();
            foreach ($data3 as $previous_call_key => $previous_call_value) {

                $LeadType = Lead::select('is_deal', 'status')->find($previous_call_value['lead_id']);
                if ($LeadType) {
                    if ($LeadType['is_deal'] == 0) {
                        $data3[$previous_call_key]['lead_type'] = "Lead";
                    } else if ($LeadType['is_deal'] == 1) {
                        $data3[$previous_call_key]['lead_type'] = "Deal";
                    }
                    $LeadStatus = getLeadStatus()[$LeadType['status']]['name'];
                    $data3[$previous_call_key]['lead_status_label'] = $LeadStatus;
                    $data3[$previous_call_key]['lead_status'] = $LeadType['status'];
                }

                $CRMSettingCallType = CRMSettingCallType::select('id', 'name as text')->find($previous_call_value['type_id']);
                if ($CRMSettingCallType) {
                    $CRMSettingCallType = json_encode($CRMSettingCallType);
                    $CRMSettingCallType = json_decode($CRMSettingCallType, true);
                    $data3[$previous_call_key]['call_type'] = $CRMSettingCallType;
                }

                $LeadContact = LeadContact::select('lead_contacts.id','lead_contacts.phone_number', DB::raw("CONCAT(lead_contacts.first_name,' ',lead_contacts.last_name) AS text"))->find($previous_call_value['contact_name']);
                if ($LeadContact) {
                    $LeadContact = json_encode($LeadContact);
                    $LeadContact = json_decode($LeadContact, true);
                    $data3[$previous_call_key]['contact_name'] = $LeadContact;
                }

                $data3[$previous_call_key]['action_type'] = "Call";

                $data3[$previous_call_key]['date'] = date('d-m-Y', strtotime($previous_call_value['call_schedule']));
				$data3[$previous_call_key]['time'] = date('H:i A', strtotime($previous_call_value['call_schedule']));
            }

            //////   Previous Meeting Action //////
            $current_start_date = $request->current_date . " 00:00:00";
            $current_start_date = date('Y-m-d H:i:s', strtotime($current_start_date . " -5 hours"));
            $current_start_date = date('Y-m-d H:i:s', strtotime($current_start_date . " -30 minutes"));

            $columns = array(
                0 => 'lead_meetings.*',
                1 => 'crm_setting_meeting_title.name as title',
                2 => 'leads.first_name',
                3 => 'leads.last_name',
            );


            $previous_meeting_query = LeadMeeting::query();
            $previous_meeting_query->leftJoin('crm_setting_meeting_title', 'crm_setting_meeting_title.id', '=', 'lead_meetings.title_id');
            $previous_meeting_query->leftJoin('leads', 'leads.id', '=', 'lead_meetings.lead_id');
            if ($isSalePerson == 1) {
                $previous_meeting_query->whereIn('lead_meetings.user_id', $childSalePersonsIds);
            }
            $previous_meeting_query->where('lead_meetings.meeting_date_time', '<', $current_start_date);
            $previous_meeting_query->where('lead_meetings.is_closed', 0);
            if($request->employee_id != 0){
                $previous_meeting_query->where('lead_meetings.user_id', $request->employee_id);
            }
            $previous_meeting_query->select($columns);
            $data4 = $previous_meeting_query->get();
            foreach ($data4 as $previous_meeting_key => $previous_meeting_value) {
                $LeadType = Lead::select('is_deal', 'status')->find($previous_meeting_value['lead_id']);
                if ($LeadType) {
                    $LeadType = json_encode($LeadType);
                    $LeadType = json_decode($LeadType, true);
                    if ($LeadType['is_deal'] == 0) {
                        $data4[$previous_meeting_key]['lead_type'] = "Lead";
                    } else if ($LeadType['is_deal'] == 1) {
                        $data4[$previous_meeting_key]['lead_type'] = "Deal";
                    }
                    $LeadStatus = getLeadStatus()[$LeadType['status']]['name'];
                    $data4[$previous_meeting_key]['lead_status_label'] = $LeadStatus;
                    $data4[$previous_meeting_key]['lead_status'] = $LeadType['status'];
                }

                $CRMSettingMeetingTitle = CRMSettingMeetingTitle::select('id', 'name as text')->find($previous_meeting_value['title_id']);

                if ($CRMSettingMeetingTitle) {
                    $CRMSettingMeetingTitle = json_encode($CRMSettingMeetingTitle);
                    $CRMSettingMeetingTitle = json_decode($CRMSettingMeetingTitle, true);
                    $data4[$previous_meeting_key]['title'] = $CRMSettingMeetingTitle['text'];
                }

                $PreviousLeadMeetingParticipant = LeadMeetingParticipant::where('meeting_id', $previous_meeting_value['id'])->orderby('id', 'asc')->get();
                $PreviousLeadMeetingParticipant = json_encode($PreviousLeadMeetingParticipant);
                $PreviousLeadMeetingParticipant = json_decode($PreviousLeadMeetingParticipant, true);


                $LeadMeetingtype = CRMSettingMeetingType::select('id', 'name as text');
                $LeadMeetingtype->where('crm_setting_meeting_type.status', 1);
                $LeadMeetingtype->where('crm_setting_meeting_type.id', $previous_meeting_value['type_id']);
                $LeadMeetingtype = $LeadMeetingtype->first();
                if ($LeadMeetingtype) {
                    $LeadMeetingtype = json_encode($LeadMeetingtype);
                    $LeadMeetingtype = json_decode($LeadMeetingtype, true);
                    $data4[$previous_meeting_key]['type'] = $LeadMeetingtype;
                } else {
                    $data4[$previous_meeting_key]['type'] = (object) array();
                }

                $UsersId = array();
                $ContactIds = array();

                foreach ($PreviousLeadMeetingParticipant as $key => $value) {

                    if ($value['type'] == "users") {
                        $UsersId[] = $value['reference_id'];
                    }
                }


                foreach ($PreviousLeadMeetingParticipant as $key => $value) {

                    if ($value['type'] == "lead_contacts") {
                        $ContactIds[] = $value['reference_id'];
                    }
                }

                $UserResponse = array();


                if (count($UsersId) > 0) {

                    $User = User::select('users.id', 'users.type', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
                    $User->whereIn('users.id', $UsersId);
                    $User = $User->get();
                    $getAllUserTypes = getAllUserTypes();

                    if (count($User) > 0) {
                        foreach ($User as $User_key => $User_value) {
                            $UserResponse[$User_key]['id'] = "users-" . $User_value['id'];
                            $UserResponse[$User_key]['text'] = $getAllUserTypes[$User_value['type']]['short_name'] . " - " . $User_value['full_name'];
                        }
                    }
                }



                if (count($ContactIds) > 0) {
                    $LeadContact = LeadContact::select('lead_contacts.id', 'lead_contacts.first_name', 'lead_contacts.last_name', DB::raw("CONCAT(lead_contacts.first_name,' ',lead_contacts.last_name) AS full_name"));

                    $LeadContact->where('lead_contacts.id', $ContactIds);
                    $LeadContact = $LeadContact->get();

                    if (count($LeadContact) > 0) {
                        foreach ($LeadContact as $User_key => $User_value) {
                            $length = count($UserResponse);
                            $UserResponse[$length]['id'] = "lead_contacts-" . $User_value['id'];
                            $UserResponse[$length]['text'] = "Contact - " . $User_value['full_name'];
                        }
                    }
                }

                $data4[$previous_meeting_key]['meeting_participant'] = $UserResponse;
                $data4[$previous_meeting_key]['action_type'] = "meeting";

                $data4[$previous_meeting_key]['date'] = date('d-m-Y', strtotime($previous_meeting_value['meeting_date_time']));
				$data4[$previous_meeting_key]['time'] = date('H:i A', strtotime($previous_meeting_value['meeting_date_time']));
            }
            //////   Previous Task Action //////
            $current_start_date = $request->current_date . " 00:00:00";
            $current_start_date = date('Y-m-d H:i:s', strtotime($current_start_date . " -5 hours"));
            $current_start_date = date('Y-m-d H:i:s', strtotime($current_start_date . " -30 minutes"));

            $columns = array(
                0 => 'lead_tasks.*',
                1 => 'lead_tasks.task as title',
                2 => 'leads.first_name',
                3 => 'leads.last_name',
            );


            $previous_task_query = LeadTask::query();
            $previous_task_query->leftJoin('leads', 'leads.id', '=', 'lead_tasks.lead_id');
            if ($isSalePerson == 1) {
                $previous_task_query->whereIn('lead_tasks.user_id', $childSalePersonsIds);
            }
            $previous_task_query->where('lead_tasks.is_closed', 0);
            if($request->employee_id != 0){
                $previous_task_query->where('lead_tasks.user_id', $request->employee_id);
            }
            $previous_task_query->where('lead_tasks.due_date_time', '<', $current_start_date);
            $previous_task_query->select($columns);
            $data5 = $previous_task_query->get();
            foreach ($data5 as $previous_task_key => $previous_task_value) {
                $LeadType = Lead::select('is_deal', 'status')->find($previous_task_value['lead_id']);
                if ($LeadType) {
                    $LeadType = json_encode($LeadType);
                    $LeadType = json_decode($LeadType, true);
                    if ($LeadType['is_deal'] == 0) {
                        $data5[$previous_task_key]['lead_type'] = "Lead";
                    } else if ($LeadType['is_deal'] == 1) {
                        $data5[$previous_task_key]['lead_type'] = "Deal";
                    }
                    $statusName = '';
                    if($LeadType['status'] != null || $LeadType['status'] != 0){
                        $statusName = getLeadStatus()[$LeadType['status']]['name'];
                    }
                    $LeadStatus = $statusName;
                    $data5[$previous_task_key]['lead_status_label'] = $LeadStatus;
                    $data5[$previous_task_key]['lead_status'] = $LeadType['status'];
                }

                $Assign_User = User::select('users.id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"));
                $Assign_User->where('users.id', $previous_task_value['assign_to']);
                $Assign_User = $Assign_User->first();
                $data5[$previous_task_key]['assign_to'] = $Assign_User;

                $data5[$previous_task_key]['action_type'] = "task";

                $data5[$previous_task_key]['date'] = date('d-m-Y', strtotime($previous_task_value['due_date_time']));
				$data5[$previous_task_key]['time'] = date('H:i A', strtotime($previous_task_value['due_date_time']));
            }

            $response = successRes();
            $response['data']['today_calls'] = $data;
            $response['data']['today_meetings'] = $data1;
            $response['data']['today_tasks'] = $data2;
            $response['data']['previous_calls'] = $data3;
            $response['data']['previous_meetings'] = $data4;
            $response['data']['previous_tasks'] = $data5;
        } catch (QueryException $ex) {
            $response = errorRes();
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function searchTeamEmployee(Request $request){

        try {
            $searchKeyword = isset($request->q) ? $request->q : "";


            $data = User::select('id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"));
            $data->where(function ($query) use ($searchKeyword) {
                $query->where('users.first_name', 'like', '%' . $searchKeyword . '%');
                $query->orWhere('users.last_name', 'like', '%' . $searchKeyword . '%');
            });
            $data->where('users.type', '2'); 
            $data->limit(5);
            $data = $data->get();
            $response = successRes();
            $response['data'] = $data;
        } catch (QueryException $ex) {
            $response = errorRes();
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }
    
    public function quotationRequestDetail(Request $request) 
    {
        $validator = Validator::make($request->all(), [
            'group_id' => ['required']
        ]);

        if ($validator->fails()) {
            $response = errorRes($validator->errors()->first());
            $response['data'] = $validator->errors();
        } else {
            $data = QuotRequest::select('quotation_request.*','wltrn_quotation.quot_no_str', 'users.type as user_type', 'wlmst_item_subgroups.itemsubgroupname', 'wlmst_item_subgroups.manager_maxdisc', 'wlmst_item_subgroups.channel_partner_maxdisc', 'wlmst_item_subgroups.company_admin_maxdisc');
            $data->leftJoin('wlmst_item_subgroups', 'wlmst_item_subgroups.id', '=', 'quotation_request.subgroup_id');
            $data->leftJoin('wltrn_quotation', 'wltrn_quotation.id', '=', 'quotation_request.quot_id');
            $data->leftJoin('users', 'users.id', '=', 'quotation_request.assign_to');
            $data->where('quotation_request.group_id', $request->group_id);
            $data->where('quotation_request.type', 'DISCOUNT');
            $data->where('quotation_request.status', 0);
            $data = $data->get();
            foreach ($data as $key => $value) {
                $user_type_lable = getUserTypeMainLabel($value['user_type']);
                $max_dis = $value['discount'];
                if($user_type_lable == 'SALES'){
                    $max_dis = $value['manager_maxdisc'];
                }elseif (isChannelPartner($value['user_type']) != 0) {
                    $max_dis = $value['channel_partner_maxdisc'];
                }elseif ($user_type_lable == "ADMIN" || $user_type_lable == "COMPANY ADMIN") {
                    $max_dis = 100;
                }
                $data[$key]['max_dis'] = $max_dis;
            }
            $response = successRes();
            $response['data'] = $data;
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function saveDiscountApprovedOrReject(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => ['required'],
            'type' => ['required'],
            'discount' => ['required']
        ]);

        if ($validator->fails()) {
            $response = errorRes($validator->errors()->first());
            $response['data'] = $validator->errors();
        } else {

            if($request->type == "APPROVED"){

                $QuotReq = QuotRequest::find($request->id);

                if($QuotReq){

                    $objQuotRequest = QuotRequest::select('quotation_request.*', 'users.type as user_type', 'wlmst_item_subgroups.itemsubgroupname', 'wlmst_item_subgroups.manager_maxdisc', 'wlmst_item_subgroups.channel_partner_maxdisc', 'wlmst_item_subgroups.company_admin_maxdisc');
                    $objQuotRequest->leftJoin('wlmst_item_subgroups', 'wlmst_item_subgroups.id', '=', 'quotation_request.subgroup_id');
                    $objQuotRequest->leftJoin('users', 'users.id', '=', 'quotation_request.assign_to');
                    $objQuotRequest->where('quotation_request.id', $request->id);
                    $objQuotRequest->where('quotation_request.type', 'DISCOUNT');
                    $objQuotRequest = $objQuotRequest->first();
    
                    if($objQuotRequest){
                        $user_type_lable = getUserTypeMainLabel($objQuotRequest['user_type']);
                        $max_dis = $objQuotRequest['discount'];
                        if($user_type_lable == 'SALES'){
                            $max_dis = $objQuotRequest['manager_maxdisc'];
                        }elseif (isChannelPartner($objQuotRequest['user_type']) != 0) {
                            $max_dis = $objQuotRequest['channel_partner_maxdisc'];
                        }elseif ($user_type_lable == "ADMIN" || $user_type_lable == "COMPANY ADMIN") {
                            $max_dis = 100;
                        }
                        
                        if((float)$request->discount > (float)$max_dis){
                            $response = errorRes("⚠️ \n Sorry, you cannot apply a discount of more than ".$max_dis."%.");
                            return response()->json($response)->header('Content-Type', 'application/json');
                        }
                    }

                    $QuotReq->status = 1;
                    $QuotReq->updateby = Auth::user()->id;
                    $QuotReq->updateip = $request->ip();
                    $QuotReq->save();
                    $QuotItemDetailArr = Wltrn_QuotItemdetail::select('*')->where([['wltrn_quot_itemdetails.quot_id', $QuotReq->quot_id], ['wltrn_quot_itemdetails.quotgroup_id', $QuotReq->quotgroup_id], ['wltrn_quot_itemdetails.itemsubgroup_id', $QuotReq->subgroup_id]]);
                    foreach ($QuotItemDetailArr->get() as $key => $value) {
                        $QuotItemDetail = Wltrn_QuotItemdetail::find($value['id']);
    
                        $totalamt = floatval($QuotItemDetail->qty) * floatval($QuotItemDetail->rate);
                        $dis_amt = (floatval($totalamt) * floatval($request->discount)) / 100;
                        $new_grossamount = floatval($totalamt) - floatval($dis_amt);
                        $new_taxableamount = floatval($totalamt) - floatval($dis_amt);
    
                        $new_igst_amount = (floatval($new_taxableamount) * floatval($QuotItemDetail->igst_per)) / 100;
                        $new_cgst_amount = (floatval($new_taxableamount) * floatval($QuotItemDetail->cgst_per)) / 100;
                        $new_sgst_amount = (floatval($new_taxableamount) * floatval($QuotItemDetail->sgst_per)) / 100;
    
                        /* NET AMOUNT CALCULATION */
                        $NetTotalAmount = floatval($new_taxableamount) + floatval($new_igst_amount) + floatval($new_cgst_amount) + floatval($new_sgst_amount);
                        /* ROUND_UP AMOUNT CALCULATION */
                        $RoundUpAmount = floatval($NetTotalAmount) - floatval(round($NetTotalAmount));
                        /* NET FINAL AMOUNT CALCULATION */
                        $new_net_amount = round($NetTotalAmount);
    
                        $QuotItemDetail->discper = $request->discount;
                        $QuotItemDetail->discamount = $dis_amt;
                        $QuotItemDetail->grossamount = $new_grossamount;
                        $QuotItemDetail->taxableamount = $new_taxableamount;
                        $QuotItemDetail->igst_amount = $new_igst_amount;
                        $QuotItemDetail->cgst_amount = $new_cgst_amount;
                        $QuotItemDetail->sgst_amount = $new_sgst_amount;
                        $QuotItemDetail->roundup_amount = $RoundUpAmount;
                        $QuotItemDetail->net_amount = $new_net_amount;
    
                        $QuotItemDetail->save();
                    }
                    $response = successRes("✅ \n Discount request is approved successfully");
                    
                    $DebugLog = new DebugLog();
                    $DebugLog->user_id = Auth::user()->id;
                    $DebugLog->name = "quotation-discount";
                    $DebugLog->description = "Quotation Discount Approved By #" . Auth::user()->id . " (Quot id : " . $QuotReq->quot_id . " Discount : ".$request->discount.")";
                    $DebugLog->save();

                    $QuotMaster = Wltrn_Quotation::find($QuotReq->quot_id);
                    if($QuotMaster){
                        $Lead = Lead::find($QuotMaster->inquiry_id);
                        $notificationUserids = getParentSalePersonsIds($Lead->assigned_to);
                        $notificationUserids[] = $Lead->assigned_to;
                        $UsersNotificationTokens = UsersNotificationTokens($notificationUserids);

                        sendNotificationTOAndroid("Discount Approved", "Discount request has been approved for deal no #".$Lead->id, $UsersNotificationTokens, "LEAD", $Lead, '');
                    }

                    $checkReqCount = QuotRequest::where('quot_id',$QuotReq->quot_id)->where('quotation_request.type', 'DISCOUNT')->whereNotIn('status',[1,2])->count();
                    if($checkReqCount == 0){
                        $QuotMaster = Wltrn_Quotation::find($QuotReq->quot_id);
                        $QuotMaster->updateby = Auth::user()->id;
                        $QuotMaster->updateip = $request->ip();
                        // Wltrn_Quotation::where('quotgroup_id', $QuotReq->quotgroup_id)->update(['isfinal' => 0]);
                        Wltrn_Quotation::where('inquiry_id', $QuotReq->inquiry_id)->update(['isfinal' => 0]);
                        $QuotMaster->status = 3;
                        $QuotMaster->isfinal = 1;
                        $QuotMaster->save();

                        if ($QuotMaster) {
                            $Lead = Lead::find($QuotMaster->inquiry_id);
                            $Lead->is_deal = 1;
                            $Lead->save();
                        }
                    }
                } else {
                    $response = errorRes("❌ \n Request id not valid");
                }
            } else if($request->type == "REJECT") {
                $QuotReq = QuotRequest::find($request->id);
                $QuotReq->status = 2;
                $QuotReq->save();
                if($QuotReq){
                    $checkReqCount = QuotRequest::where('quot_id',$QuotReq->quot_id)->where('quotation_request.type', 'DISCOUNT')->whereNotIn('status',[1,2])->count();
                    if($checkReqCount == 0){
                        $QuotMaster = Wltrn_Quotation::find($QuotReq->quot_id);
                        $QuotMaster->updateby = Auth::user()->id;
                        $QuotMaster->updateip = $request->ip();
                        // Wltrn_Quotation::where('quotgroup_id', $QuotReq->quotgroup_id)->update(['isfinal' => 0]);
                        Wltrn_Quotation::where('inquiry_id', $QuotReq->inquiry_id)->update(['isfinal' => 0]);
                        $QuotMaster->status = 3;
                        $QuotMaster->isfinal = 1;
                        $QuotMaster->save();

                        if ($QuotMaster) {
                            $Lead = Lead::find($QuotMaster->inquiry_id);
                            $Lead->is_deal = 1;
                            $Lead->save();
                        }
                    }
                    $response = successRes("✅ \n Discount request is rejected successfully");

                    $QuotMaster = Wltrn_Quotation::find($QuotReq->quot_id);
                    if($QuotMaster){
                        $Lead = Lead::find($QuotMaster->inquiry_id);
                        $notificationUserids = getParentSalePersonsIds($Lead->assigned_to);
                        $notificationUserids[] = $Lead->assigned_to;
                        $UsersNotificationTokens = UsersNotificationTokens($notificationUserids);

                        sendNotificationTOAndroid("Discount Rejected", "Discount request has been rejected for deal no #".$Lead->id, $UsersNotificationTokens, "LEAD", $Lead, '');
                    }

                    $DebugLog = new DebugLog();
                    $DebugLog->user_id = Auth::user()->id;
                    $DebugLog->name = "quotation-discount";
                    $DebugLog->description = "Quotation Discount Rejected By #" . Auth::user()->id . " (Quot id : " . $QuotReq->quot_id . " Discount : ".$request->discount.")";
                    $DebugLog->save();
                } else {
                    $response = errorRes("❌ \n Request id not valid");
                }
            } else {
                $response = errorRes("❌ \n Request type not valid");
            }
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }
}