<?php

namespace App\Http\Controllers\CRM\Lead;

use DB;

use App\Models\Lead;
use App\Models\User;
use App\Models\LeadUpdate;
use App\Models\LeadContact;
use App\Models\LeadMeeting;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\CRMSettingMeetingType;
use App\Models\CRMSettingMeetingTitle;
use App\Models\LeadMeetingParticipant;
use Illuminate\Support\Facades\Validator;
use App\Models\CRMSettingMeetingOutcomeType;
use App\Http\Controllers\MicrosoftGraph\MicrosoftApiContoller;
use DateTime;
use DateTimeZone;
use Exception;

class LeadMeetingController extends Controller
{

    public function __construct()
    {

        $this->middleware(function ($request, $next) {

            $tabCanAccessBy = array(0, 1, 2, 6,9, 11, 13, 101, 102, 103, 104, 105, 202, 302);

            if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
                return redirect()->route('dashboard');
            }

            return $next($request);
        });
    }



    public function save(Request $request)
    {

        // is 0 then refresh open action 
        $is_action = 0;
        $rules = array();

        $rules['lead_meeting_id'] = 'required';
        $rules['lead_meeting_lead_id'] = 'required';
        $rules['lead_meeting_title_id'] = 'required';
        $rules['lead_meeting_location'] = 'required';
        $rules['lead_meeting_date'] = 'required';
        $rules['lead_meeting_time'] = 'required';
        $rules['lead_meeting_participants'] = 'required';
        $rules['lead_meeting_description'] = 'required';

        if ($request->lead_meeting_move_to_close == 1) {
            $rules['lead_meeting_meeting_outcome'] = 'required';
            $rules['close_meeting_note'] = 'required';
            $rules['lead_meeting_reminder_date_time'] = 'required';
        }

        if ($request->lead_meeting_type_id == 2) {
            $rules['lead_meeting_meeting_outcome'] = 'required';
        }
        if ($request->lead_meeting_type_id == 1) {
            $rules['lead_meeting_reminder_date_time'] = 'required';
        }

        $customMessage = array();
        $customMessage['lead_meeting_lead_id.required'] = "Invalid parameters";


        $validator = Validator::make($request->all(), $rules, $customMessage);

        if ($validator->fails()) {

            $response = errorRes("The request could not be understood by the server due to malformed syntax");
            $response['data'] = $validator->errors();
        } else {



            // $lead_meeting_meeting_date_time = $request->lead_closing_dlead_meeting_meeting_date_timeate_time . " 23:59:59";
            // $lead_meeting_meeting_date_time = date('Y-m-d H:i:s', strtotime($lead_meeting_meeting_date_time . " -5 hours"));
            // $lead_meeting_meeting_date_time = date('Y-m-d H:i:s', strtotime($lead_meeting_meeting_date_time . " -30 minutes"));

            $meeting_date_time = date('Y-m-d H:i:s', strtotime($request->lead_meeting_date . "  " . $request->lead_meeting_time));
            if ($request->lead_meeting_id != 0) {
                $LeadMeeting = LeadMeeting::find($request->lead_meeting_id);
                // LeadMeetingParticipant::where('meeting_id', $request->lead_meeting_id)->delete();
            } else {
                $LeadMeeting = new LeadMeeting();
            }

            $Lead_data = Lead::find($request->lead_meeting_lead_id);

            $LeadMeeting->user_id = Auth::user()->id;
            $LeadMeeting->title_id = $request->lead_meeting_title_id;
            $LeadMeeting->type_id = $request->lead_meeting_type_id;
            $LeadMeeting->lead_id = $request->lead_meeting_lead_id;
            $LeadMeeting->location = $request->lead_meeting_location;
            $LeadMeeting->meeting_date_time = $meeting_date_time;
            $LeadMeeting->meeting_interval_time = $request->lead_meeting_interval_time;
            $LeadMeeting->description = '#'.$request->lead_meeting_lead_id . ' - ' . $Lead_data->first_name . ' ' . $Lead_data->last_name . ' - ' . $request->lead_meeting_description;
            if ($request->lead_meeting_type_id == 1) {
                $LeadMeeting->is_notification = 1;

                $meeting_reminder = getReminderTimeSlot($meeting_date_time)[$request->lead_meeting_reminder_date_time]['datetime'];
                $LeadMeeting->reminder = $meeting_reminder;
                $LeadMeeting->reminder_id = $request->lead_meeting_reminder_date_time;
            }
            $askForStatusChange = 0;

            if (isset($request->lead_meeting_move_to_close) && $request->lead_meeting_move_to_close == "1" || $request->lead_meeting_type_id == 2) {
                $is_action = 1;
                $LeadMeeting->is_closed = 1;
                $LeadMeeting->closed_date_time = date("Y-m-d H:i:s");
                $LeadMeeting->close_note = $request->close_meeting_note;
                $LeadMeeting->outcome_type = $request->lead_meeting_meeting_outcome;
            }


            $LeadMeeting->save();
            $statusupdate = '';
            if ($LeadMeeting) {

                if(isset($request->lead_meeting_status)){
                    if ($request->lead_meeting_id != 0 || $request->lead_meeting_type_id == 2) {
                        $statusupdate = saveLeadAndDealStatusInAction($LeadMeeting->lead_id, $request->lead_meeting_status, $request->ip());
                    }
                }
                $main_mail = '';
                $is_main_mail = 0;
                $attendees = array();
                if ($request->lead_meeting_id == 0) {
                    if (isset($request->lead_meeting_participants)) {
                        foreach ($request->lead_meeting_participants as $value) {
                            $valuePieces = explode("-", $value);
                            $LeadMeetingParticipant = new LeadMeetingParticipant();
                            $LeadMeetingParticipant->lead_id = $LeadMeeting->lead_id;
                            $LeadMeetingParticipant->meeting_id = $LeadMeeting->id;
                            $LeadMeetingParticipant->type = $valuePieces[0];
                            $LeadMeetingParticipant->reference_id = $valuePieces[1];
                            $LeadMeetingParticipant->save();

                            if ($valuePieces[0] == "users") {
                                $user_detail = User::find($valuePieces[1]);

                                if ($user_detail->email != '' && $user_detail->email != null) {
                                    if (strpos($user_detail->email, "whitelion.in") !== false && $is_main_mail != 1) {

                                        // $main_mail = $user_detail->email;
                                        // $main_mail = "noreply@whitelion.in";
                                        // $main_mail = "poonam@whitelion.in";
                                        if(strpos(Auth::user()->email, "whitelion.in") !== false)
                                        {
                                            $main_mail = Auth::user()->email;
                                        } else {
                                            $main_mail = $user_detail->email;
                                        }
                                        $is_main_mail = 1;
                                    } else {

                                        $attend['emailAddress']['address'] = $user_detail->email;
                                        // $attend['emailAddress']['address'] = "acc2whitelion@gmail.com";
                                        $attend['emailAddress']['name'] = $user_detail->first_name . ' ' . $user_detail->last_name;
                                        $attend['type'] = "required";

                                        array_push($attendees,$attend);
                                    }
                                }

                            } elseif ($valuePieces[0] == "lead_contacts") {
                                $contact_detail = LeadContact::find($valuePieces[1]);

                                if ($contact_detail->email != '' && $contact_detail->email != null) {
                                    if (strpos($contact_detail->email, "whitelion.in") !== false  && $is_main_mail != 1) {
                                        
                                        // $main_mail = $contact_detail->email;
                                        if(strpos(Auth::user()->email, "whitelion.in") !== false)
                                        {
                                            $main_mail = Auth::user()->email;
                                        } else {
                                            $main_mail = $contact_detail->email;
                                        }
                                        $is_main_mail = 1;
                                    } else {

                                        // $attend['emailAddress']['address'] = "Poonam@whitelion.in";
                                        $attend['emailAddress']['address'] = $contact_detail->email;
                                        $attend['emailAddress']['name'] = $contact_detail->first_name . ' ' . $contact_detail->last_name;
                                        $attend['type'] = "required";

                                        array_push($attendees,$attend);
                                    }
                                }

                            }
                        }
                    }
                }

                $meeting_title = CRMSettingMeetingTitle::select('id', 'name as text');
                $meeting_title->where('crm_setting_meeting_title.status', 1);
                $meeting_title->where('crm_setting_meeting_title.id', $request->lead_meeting_title_id);
                $meeting_title = $meeting_title->first();

                $LeadUpdate = new LeadUpdate();
                $LeadUpdate->user_id = Auth::user()->id;
                $LeadUpdate->lead_id = $LeadMeeting->lead_id;
                if ($LeadMeeting->is_closed == 1 || $request->lead_meeting_type_id == 2) {
                    $LeadUpdate->task = "Close Meeting";
                    if ($request->close_meeting_note != null && $request->close_meeting_note != '') {
                        $LeadUpdate->message = $request->close_meeting_note;
                    } else {
                        $LeadUpdate->message = '';
                    }
                } else if ($LeadMeeting->is_closed == 0) {
                    $LeadUpdate->task = "Open Meeting";
                    $LeadUpdate->message = $request->lead_meeting_description;
                }
                $LeadUpdate->task_title = $meeting_title->text;
                $LeadUpdate->save();

                $response = successRes("Successfully saved meeting");
                $response['id'] = $LeadMeeting->lead_id;
                $response['is_action'] = $is_action;
                $response['data'] = $statusupdate;

                if ($LeadMeeting->is_closed == 0 && $main_mail != '' && $main_mail != null && $LeadMeeting->type_id == 1) {
                    $meeting_start_date_time = date('Y-m-d h:i:s', strtotime($LeadMeeting->meeting_date_time));
            
                    $interval_time_code = getIntervalTime()[$LeadMeeting->meeting_interval_time]['code'];
                    $meeting_end_date_time = date('Y-m-d h:i:s', strtotime($meeting_start_date_time .' '. $interval_time_code));

                    $start_dt = new DateTime($meeting_start_date_time);
                    $start_dt->setTimeZone(new DateTimeZone("UTC"));
                    $start_dt = $start_dt->format('Y-m-d h:i:s');

                    $end_dt = new DateTime($meeting_end_date_time);
                    $end_dt->setTimeZone(new DateTimeZone("UTC"));
                    $end_dt = $end_dt->format('Y-m-d h:i:s');

                    try{
                        $title = CRMSettingMeetingTitle::select('name')->find($LeadMeeting->title_id)->name;
                        $microsoftApiContoller = new MicrosoftApiContoller;
                        $perameater_request = new Request();
                        $perameater_request['main_mail'] = $main_mail;
                        $perameater_request['title'] = $title;
                        $perameater_request['location'] = $LeadMeeting->location;
                        $perameater_request['start_datetime'] =  $start_dt;
                        $perameater_request['end_datetime'] = $end_dt;
                        $perameater_request['attendees'] = $attendees;
                        $perameater_request['reminder_minute'] = getReminderTimeSlot()[$LeadMeeting->reminder_id]['minute'];
                        $ms_response = $microsoftApiContoller->createClanderEvent($perameater_request);
                        
                    } catch (Exception $e) {
                        $ms_response = $e->getMessage();
                    }
                    
                    $response["ms_response"] = $ms_response;
                }
            } else {
                $response = errorRes("please Contact to admin");
            }

        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }


    function searchTitle(Request $request)
    {
        $searchKeyword = isset($request->q) ? $request->q : "";
        $data = CRMSettingMeetingTitle::select('id', 'name as text');
        $data->where('crm_setting_meeting_title.status', 1);
        $data->where('crm_setting_meeting_title.name', 'like', "%" . $searchKeyword . "%");
        $data->limit(5);
        $data = $data->get();
        $response = array();
        $response['results'] = $data;
        $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');

        //CRMSettingScheduleCallType
    }

    function searchParticipants(Request $request)
    {

        $q = isset($request->q) ? $request->q : "";

        $UserResponse = array();
        $Lead_Detail = Lead::find($request->lead_id);
        
        $LeadContact = LeadContact::select('lead_contacts.id', 'lead_contacts.first_name', 'lead_contacts.last_name', DB::raw("CONCAT(lead_contacts.first_name,' ',lead_contacts.last_name) AS full_name"));
        $LeadContact->where('lead_contacts.status', 1);
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
        $response['results'] = $UserResponse;
        $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function searchMeetingOutcomeType(Request $request)
    {

        $searchKeyword = isset($request->q) ? $request->q : "";

        $data = CRMSettingMeetingOutcomeType::select('id', 'name as text');
        $data->where('crm_setting_meeting_outcome_type.status', 1);
        $data->where('crm_setting_meeting_outcome_type.name', 'like', "%" . $searchKeyword . "%");
        $data->limit(5);
        $data = $data->get();
        $response = array();
        $response['results'] = $data;
        $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');

        //CRMSettingScheduleCallType
    }
    function detail(Request $request)
    {

        $LeadMeeting = LeadMeeting::find($request->id);
        if ($LeadMeeting) {
            $LeadMeeting = json_encode($LeadMeeting);
            $LeadMeeting = json_decode($LeadMeeting, true);

            $LeadMeeting['meeting_date'] = date('d-m-Y', strtotime($LeadMeeting['meeting_date_time']));
            $LeadMeeting['meeting_time'] = date('h:i A', strtotime($LeadMeeting['meeting_date_time']));

            $LeadMeeting_interval['id'] = getIntervalTime()[$LeadMeeting['meeting_interval_time']]['id'];
            $LeadMeeting_interval['text'] = getIntervalTime()[$LeadMeeting['meeting_interval_time']]['name'];

            $LeadMeeting['meeting_interval_time'] = $LeadMeeting_interval;

            $LeadMeeting['reminder_text'] = getReminderTimeSlot()[$LeadMeeting['reminder_id']]['name'];

            $CRMSettingMeetingTitle = CRMSettingMeetingTitle::select('id', 'name as text')->find($LeadMeeting['title_id']);

            if ($CRMSettingMeetingTitle) {
                $CRMSettingMeetingTitle = json_encode($CRMSettingMeetingTitle);
                $CRMSettingMeetingTitle = json_decode($CRMSettingMeetingTitle, true);
                $LeadMeeting['title'] = $CRMSettingMeetingTitle;
            }

            $CRMSettingMeetingType = CRMSettingMeetingType::select('id', 'name as text')->find($LeadMeeting['type_id']);
            if ($CRMSettingMeetingType) {
                $CRMSettingMeetingType = json_encode($CRMSettingMeetingType);
                $CRMSettingMeetingType = json_decode($CRMSettingMeetingType, true);
                $LeadMeeting['type'] = $CRMSettingMeetingType;
            }

            $LeadType = Lead::select('is_deal', 'status')->find($LeadMeeting['lead_id']);
            if ($LeadType) {
                $LeadType = json_encode($LeadType);
                $LeadType = json_decode($LeadType, true);
                $LeadMeeting['lead_type'] = $LeadType;
                $LeadStatus = getLeadStatus();
                foreach ($LeadStatus as $key => $value) {
                    if ($value['id'] == $LeadType['status']) {
                        $LeadMeeting['lead_status'] = $value['name'];
                        break;
                    } else {
                        $LeadMeeting['lead_status'] = "";
                    }
                }
            }


            // $LeadMeeting['meeting_date_time'] = date('Y-m-d H:i:s', strtotime($LeadMeeting['meeting_date_time']  . " +5 hours"));
            // $LeadMeeting['meeting_date_time'] = date('Y-m-d', strtotime($LeadMeeting['meeting_date_time'] . " +30 minutes"));

            $LeadMeetingParticipant = LeadMeetingParticipant::where('meeting_id', $LeadMeeting['id'])->orderby('id', 'asc')->get();
            $LeadMeetingParticipant = json_encode($LeadMeetingParticipant);
            $LeadMeetingParticipant = json_decode($LeadMeetingParticipant, true);

            $UsersId = array();
            $ContactIds = array();

            foreach ($LeadMeetingParticipant as $key => $value) {

                if ($value['type'] == "users") {
                    $UsersId[] = $value['reference_id'];
                }
            }


            foreach ($LeadMeetingParticipant as $key => $value) {

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

                $LeadContact->whereIn('lead_contacts.id', $ContactIds);
                $LeadContact = $LeadContact->get();

                if (count($LeadContact) > 0) {
                    foreach ($LeadContact as $User_key => $User_value) {
                        $length = count($UserResponse);
                        $UserResponse[$length]['id'] = "lead_contacts-" . $User_value['id'];
                        $UserResponse[$length]['text'] = "Contact - " . $User_value['full_name'];
                    }
                }
            }








            $LeadMeeting['lead_meeting_participant'] = $UserResponse;



            $response = successRes("Lead meeting detail");
            $response['data'] = $LeadMeeting;
        } else {
            $response = errorRes("Somethng went wrong");
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function searchMeetingType(Request $request)
    {

        $searchKeyword = isset($request->q) ? $request->q : "";

        $data = CRMSettingMeetingType::select('id', 'name as text');
        $data->where('crm_setting_meeting_type.status', 1);
        $data->where('crm_setting_meeting_type.name', 'like', "%" . $searchKeyword . "%");
        $data->limit(5);
        $data = $data->get();
        $response = array();
        $response['results'] = $data;
        $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');

        //CRMSettingScheduleCallType
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
                        if ($user_detail->email != '' && $user_detail->email != null) {
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

                        if ($contact_detail->email != '' && $contact_detail->email != null) {
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
                    $action = "";
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

                            $action .= '<div class="border border-primary rounded mb-2 p-2 w-auto">';
                            if(isset($sch_value['subject'])){
                                $action .= '<a class="hidden_text" title="'. $sch_value['subject'] .'">'. $sch_value['subject'] .'</a>';
                            }
                            $action .= '<span class="">'. $User_data->first_name . ' ' . $User_data->last_name .'</span><br>';
                            $action .= '<span class="">'. $dayName .'  '. $start_date .'</span><br>';
                            $action .= '<span class="fw-bold">'. $start_time .' - '. $end_time .'</span><br>';
                            if($sch_value['status'] == "busy"){
                                $action .= '<span><i class="bx bxs-check-circle bx-flashing me-2" style="color:#ff0404; vertical-align: middle;"></i>'. $sch_value['status'] .'</span>';
                            } else {
                                $action .= '<span><i class="bx bxs-check-circle bx-flashing me-2" style="color:#147320; vertical-align: middle;"></i>'. $sch_value['status'] .'</span>';
                            }
                            $action .= '</div>';
                        }
                    }
                    $count = 0;
                    if($action != ""){
                        $viewData[$count]['action'] = $action;
                        $count++;
                    }
                }   
                
                $jsonData = array(
                    "draw" => intval($request['draw']), 
                    "data" => $viewData,
                );
            } else{
                $jsonData = array(
                    "message" => "Please Contact Admin", 
                    "status" => 0,
                    "data" => ""
                );
            }   

        } catch (Exception $e) {
            $jsonData = array(
                "message" => $e->getMessage(), 
                "status" => 0,
                "data" => "",
            );
        }
        return $jsonData;
    }
}