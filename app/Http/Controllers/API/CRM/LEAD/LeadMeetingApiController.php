<?php

namespace App\Http\Controllers\API\CRM\LEAD;

use App\Models\DebugLog;
// use PDF;
// use Dompdf\Dompdf;
use App\Models\WlmstCompany;
use Illuminate\Http\Request;
use App\Models\LeadMeeting;
use App\Models\CRMSettingMeetingOutcomeType;
// use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\LeadClosing;
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
use App\Models\LeadUpdate;
use App\Models\CRMSettingMeetingTitle;
use App\Models\LeadMeetingParticipant;
use Illuminate\Support\Facades\File;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Quotation\QuotationMasterController;
use App\Http\Controllers\CRM\LeadQuotationController;
use PhpParser\Node\Stmt\Break_;
use App\Http\Controllers\MicrosoftGraph\MicrosoftApiContoller;
use Exception;
use DateTime;
use DateTimeZone;

// use Illuminate\Http\Request;
class LeadMeetingApiController extends Controller
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

    function searchMeetingTitle(Request $request)
    {
        try {
            $searchKeyword = isset($request->q) ? $request->q : "";
            $data = CRMSettingMeetingTitle::select('id', 'name as text');
            $data->where('crm_setting_meeting_title.status', 1);
            $data->where('crm_setting_meeting_title.name', 'like', "%" . $searchKeyword . "%");
            $data->limit(5);
            $data = $data->get();
            $response = array();
            $response = successRes();
            $response['data'] = $data;
            //CRMSettingScheduleCallType
        } catch (QueryException $ex) {
            $response = errorRes();
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }
    function searchMeetingParticipants(Request $request)
    {

        try {
            $q = $request->q;


            $LeadContact = LeadContact::select('lead_contacts.id', 'lead_contacts.first_name', 'lead_contacts.last_name', DB::raw("CONCAT(lead_contacts.first_name,' ',lead_contacts.last_name) AS full_name"));

            $LeadContact->where('lead_contacts.lead_id', $request->lead_id);
            $LeadContact->where(function ($query) use ($q) {
                $query->where('lead_contacts.first_name', 'like', '%' . $q . '%');
                $query->orWhere('lead_contacts.last_name', 'like', '%' . $q . '%');
            });

            $LeadContact->limit(5);
            $LeadContact = $LeadContact->get();

            if (count($LeadContact) > 0) {
                foreach ($LeadContact as $User_key => $User_value) {

                    $UserResponse[$User_key]['id'] = "lead_contacts-" . $User_value['id'];
                    $UserResponse[$User_key]['text'] = "Contact - " . $User_value['full_name'];
                }
            }

            $Lead_Detail = Lead::find($request->lead_id);

            $sales_parent_herarchi = getParentSalePersonsIdsforLead($Lead_Detail->assigned_to);
            $User = User::select('users.id', 'users.type', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
            $User->where('users.status', 1);

            // $User->whereIn('users.type', array(2));
            $User->whereIn('users.id', $sales_parent_herarchi);

            $User->where(function ($query) use ($q) {
                $query->where('users.first_name', 'like', '%' . $q . '%');
                $query->orWhere('users.last_name', 'like', '%' . $q . '%');
            });

            $User->limit(5);
            $User = $User->get();
            $getAllUserTypes = getAllUserTypes();

            if (count($User) > 0) {
                foreach ($User as $User_key => $User_value) {
                    $length = count($UserResponse);
                    $UserResponse[$length]['id'] = "users-" . $User_value['id'];
                    $UserResponse[$length]['text'] = $getAllUserTypes[$User_value['type']]['short_name'] . " - " . $User_value['full_name'];
                }
            }


            $response = array();
            $response = successRes();
            $response['data'] = $UserResponse;
        } catch (QueryException $ex) {
            $response = errorRes();
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }
    function searchMeetingOutCome(Request $request)
    {

        try {
            $searchKeyword = isset($request->q) ? $request->q : "";

            $data = CRMSettingMeetingOutcomeType::select('id', 'name as text');
            $data->where('crm_setting_meeting_outcome_type.status', 1);
            $data->where('crm_setting_meeting_outcome_type.name', 'like', "%" . $searchKeyword . "%");
            $data = $data->get();
            $response = array();
            $response = successRes();
            $response['data'] = $data;
            //CRMSettingScheduleCallType
        } catch (QueryException $ex) {
            $response = errorRes();
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }
    public function meetingSave(Request $request)
    {

        $rules = array();

        $rules['meeting_id'] = 'required';
        $rules['meeting_lead_id'] = 'required';
        $rules['meeting_title_id'] = 'required';
        $rules['meeting_location'] = 'required';
        $rules['meeting_date'] = 'required';
        $rules['meeting_time'] = 'required';
        $rules['meeting_participants'] = 'required';
        $rules['meeting_description'] = 'required';

        if ($request->meeting_move_to_close == 1) {
            $rules['meeting_outcome'] = 'required';
            $rules['close_meeting_note'] = 'required';
            $rules['meeting_reminder'] = 'required';
        }

        if ($request->meeting_type_id == 2) {
            $rules['meeting_outcome'] = 'required';
        }

        if ($request->meeting_type_id == 1) {
            $rules['meeting_reminder'] = 'required';
        }

        $customMessage = array();
        $customMessage['meeting_lead_id.required'] = "Invalid parameters";


        $validator = Validator::make($request->all(), $rules, $customMessage);

        if ($validator->fails()) {

            $response = errorRes("The request could not be understood by the server due to malformed syntax");
            $response['data'] = $validator->errors();
        } else {

            $meeting_date_time = date('Y-m-d H:i:s', strtotime($request->meeting_date . "  " . $request->meeting_time));

            if ($request->meeting_id != 0) {
                $LeadMeeting = LeadMeeting::find($request->meeting_id);
                // LeadMeetingParticipant::where('meeting_id', $request->meeting_id)->delete();
            } else {
                $LeadMeeting = new LeadMeeting();
            }

            $Lead_data = Lead::find($request->meeting_lead_id);

            $LeadMeeting->user_id = Auth::user()->id;
            $LeadMeeting->title_id = $request->meeting_title_id;
            $LeadMeeting->lead_id = $request->meeting_lead_id;
            $LeadMeeting->location = $request->meeting_location;
            $LeadMeeting->meeting_date_time = $meeting_date_time;
            $LeadMeeting->description = '#'.$request->meeting_lead_id . ' - ' . $Lead_data->first_name . ' ' . $Lead_data->last_name . ' - ' . $request->meeting_description;
            $askForStatusChange = 0;

            if ($request->meeting_type_id == 1) {
                $LeadMeeting->is_notification = 1;

                $meeting_reminder = getReminderTimeSlot($meeting_date_time)[$request->meeting_reminder]['datetime'];
                $LeadMeeting->reminder = $meeting_reminder;
                $LeadMeeting->reminder_id = $request->meeting_reminder;
            }

            if (isset($request->meeting_move_to_close) && $request->meeting_move_to_close == "1") {

                $LeadMeeting->is_closed = 1;
                $LeadMeeting->closed_date_time = date("Y-m-d H:i:s");
            }

            if (isset($request->meeting_move_to_close) && $request->meeting_move_to_close == "1" || $request->meeting_type_id == 2) {
                $LeadMeeting->is_closed = 1;
                $LeadMeeting->closed_date_time = date("Y-m-d H:i:s");
                $LeadMeeting->close_note = $request->close_meeting_note;
                $LeadMeeting->outcome_type = $request->meeting_outcome;
            }


            $LeadMeeting->save();
            $statusupdate = '';
            if (isset($request->meeting_status)) {
                $statusupdate = saveLeadAndDealStatusInAction($LeadMeeting->lead_id, $request->meeting_status,$request->ip(),$request->app_source);
            }
            if ($request->meeting_id == 0) {
                if (isset($request->meeting_participants)) {
                    foreach (explode(",", $request->meeting_participants) as $value) {
                        $valuePieces = explode("-", $value);
                        $LeadMeetingParticipant = new LeadMeetingParticipant();
                        $LeadMeetingParticipant->lead_id = $LeadMeeting->lead_id;
                        $LeadMeetingParticipant->meeting_id = $LeadMeeting->id;
                        $LeadMeetingParticipant->type = $valuePieces[0];
                        $LeadMeetingParticipant->reference_id = $valuePieces[1];
                        $LeadMeetingParticipant->save();
                    }
                }
            }

            $meeting_title = CRMSettingMeetingTitle::select('id', 'name as text');
            $meeting_title->where('crm_setting_meeting_title.status', 1);
            $meeting_title->where('crm_setting_meeting_title.id', $request->meeting_title_id);
            $meeting_title = $meeting_title->first();

            $LeadUpdate = new LeadUpdate();
            $LeadUpdate->user_id = Auth::user()->id;
            $LeadUpdate->lead_id = $LeadMeeting->lead_id;
            $LeadUpdate->message = $request->meeting_description;
            if ($LeadMeeting->is_closed == 1) {
                $LeadUpdate->task = "Close Meeting";
            } else if ($LeadMeeting->is_closed == 0) {
                $LeadUpdate->task = "Open Meeting";
            }
            $LeadUpdate->task_title = $meeting_title->text;
            $LeadUpdate->save();

            $response = successRes("Successfully saved meeting");
            $response['id'] = $LeadMeeting->lead_id;
            $response['data'] = $statusupdate;
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function searchInterval(Request $request)
    {
        try {
            $finalArray = array();

            foreach (getIntervalTime() as $key => $value) {
                $LeadStatus['id'] = $value['id'];
                $LeadStatus['text'] = $value['name'];

                array_push($finalArray,$LeadStatus);
            }

            $response = array();
            $response = successRes();
            $response['data'] = $finalArray;
        } catch (QueryException $ex) {
            $response = errorRes();
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function findMeetingTimes(Request $request)
    {
        try {
            $main_mail = '';
            $is_main_mail = 0;
            $attendees = array();
            $schedules = array();
            if (isset($request->lead_meeting_participants)) {
                foreach (explode(',', $request->lead_meeting_participants) as $value) {
                    $valuePieces = explode("-", $value);
                    if ($valuePieces[0] == "users") {
                        $user_detail = User::find($valuePieces[1]);
                        if ($user_detail && $user_detail->email != '' || $user_detail->email != null) {
                            if (strpos($user_detail->email, "whitelion.in") !== false) {

                                if($is_main_mail != 1){
                                    $main_mail = $user_detail->email;
                                    $is_main_mail = 1;
                                    array_push($schedules, $user_detail->email);
                                }else{
                                    array_push($schedules, $user_detail->email);
                                }
                            } else {

                                // $attend['emailAddress']['address'] = $user_detail->email;
                                $attend['emailAddress']['address'] = "poonam@whitelion.in";
                                $attend['emailAddress']['name'] = $user_detail->first_name . ' ' . $user_detail->last_name;
                                $attend['type'] = "required";

                                array_push($attendees,$attend);
                            }
                        }
                    } elseif ($valuePieces[0] == "lead_contacts") {
                        $contact_detail = LeadContact::find($valuePieces[1]);

                        if ($contact_detail && $contact_detail->email != '' || $contact_detail->email != null) {
                            if (strpos($contact_detail->email, "whitelion.in") !== false) {
                                
                                if($is_main_mail != 1){
                                    $main_mail = $contact_detail->email;
                                    $is_main_mail = 1;
                                    array_push($schedules, $contact_detail->email);
                                }else{
                                    array_push($schedules, $contact_detail->email);
                                }
                            } else {

                                // $attend['emailAddress']['address'] = $contact_detail->email;
                                $attend['emailAddress']['address'] = "poonam@whitelion.in";
                                $attend['emailAddress']['name'] = $contact_detail->first_name . ' ' . $contact_detail->last_name;
                                $attend['type'] = "required";

                                array_push($attendees, $attend);
                            }
                        }

                    }
                }
            } else {
                $response = errorRes("please Contact to admin");
            }

            if ($main_mail != '' && $main_mail != null) {

                $meeting_start_date_time = date('Y-m-d h:i:s A', strtotime($request->lead_meeting_start_date . "  " . $request->lead_meeting_start_time));
                
                $interval_time_code = getIntervalTime()[$request->lead_meeting_interval_time]['code'];
                $meeting_end_date_time = date('Y-m-d h:i:s A', strtotime($meeting_start_date_time."".$interval_time_code));

                $start_dt = new DateTime($meeting_start_date_time, new DateTimeZone("Asia/Kolkata"));
                $start_dt->setTimeZone(new DateTimeZone("UTC"));
                $start_dt = $start_dt->format('Y-m-d h:i:s A');

                $end_dt = new DateTime($meeting_end_date_time, new DateTimeZone("Asia/Kolkata"));
                $end_dt->setTimeZone(new DateTimeZone("UTC"));
                $end_dt = $end_dt->format('Y-m-d h:i:s A');

                $interval_minute = getIntervalTime()[$request->lead_meeting_interval_time]['minute'];

                $microsoftApiContoller = new MicrosoftApiContoller;

                $perameater_request = new Request();
                $perameater_request['main_mail'] = $main_mail;
                $perameater_request['location'] = $request->location;
                $perameater_request['start_datetime'] =  $start_dt;
                $perameater_request['end_datetime'] = $end_dt;
                $perameater_request['attendees'] = $attendees;
                $perameater_request['schedules'] = $schedules;
                $perameater_request['user_mail'] = $main_mail;
                $perameater_request['interval_minute'] = $interval_minute;

                $ms_response = $microsoftApiContoller->findMeetingTimes($perameater_request);

                $viewData = array();
                foreach ($ms_response['value'] as $key => $value) {
                    
                    $User_data = User::select('first_name', 'last_name')->where('email', $value['scheduleId'])->first();
                    $action = [];
                    if(isset($value['scheduleItems']) && $value['scheduleItems'] != "" && $value['scheduleItems'] != []){
                        foreach($value['scheduleItems'] as $sch_key => $sch_value){
                            $start_dt_india = new DateTime($sch_value['start']['dateTime'], new DateTimeZone("UTC"));
                            $start_dt_india->setTimeZone(new DateTimeZone("Asia/Kolkata"));
                            $start_dt_india = $start_dt_india->format('Y-m-d h:i:s');

                            $end_dt_india = new DateTime($sch_value['end']['dateTime'], new DateTimeZone("UTC"));
                            $end_dt_india->setTimeZone(new DateTimeZone("Asia/Kolkata"));
                            $end_dt_india = $end_dt_india->format('Y-m-d h:i:s');

                            $start_date = date("m/d", strtotime($start_dt_india));
                            $start_time = date("h:i", strtotime($start_dt_india));

                            $dateString = date("Y-m-d", strtotime($start_dt_india));
                            $dateTime = new DateTime($dateString);
                            $dayName = $dateTime->format('D');

                            $end_time = date("H:i", strtotime($end_dt_india));

                            if(isset($sch_value['subject'])){
                                $action ['subject'] = $sch_value['subject'];
                            }else{
                                $action ['subject'] = '';
                            }
                            $action ['name'] = $User_data->first_name . ' ' . $User_data->last_name;
                            $action ['date'] = $dayName .' '. $start_date;
                            $action ['time'] = $start_time .' - '. $end_time;
                            $action ['time'] = $sch_value['status'];
                        }
                    }
                    $count = 0;
                    if($action != ""){
                        array_push($viewData,$action);
                        $count++;
                    }
                }   
                $response = successRes("Success");
                $response['data'] = $viewData;
            } else{
                $response = errorRes("please Contact to admin");
            }   

        } catch (Exception $e) {
            $response = errorRes($e->getMessage());
            
        }
        return $response;
    }


}