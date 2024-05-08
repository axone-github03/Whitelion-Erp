<?php

namespace App\Http\Controllers\UserActionDetail;

use App\Http\Controllers\Controller;

use App\Models\Lead;
use App\Models\UserMeetingAction;
use App\Models\UserMeetingParticipant;
use App\Models\User;
use App\Models\UserNotes;
use App\Models\UserContact;
use App\Models\CRMSettingMeetingTitle;
use App\Models\CRMSettingMeetingType;
use App\Models\CRMSettingMeetingOutcomeType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use DB;
use App\Models\LeadContact;
use Illuminate\Support\Arr;

class UserMeetingController extends Controller
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


    public function save(Request $request)
    {

        // is 0 then refresh open action 
        $is_action = 0;
        $rules = array();

        $rules['meeting_id'] = 'required';
        $rules['meeting_user_id'] = 'required';
        $rules['meeting_title_id'] = 'required';
        $rules['meeting_location'] = 'required';
        $rules['meeting_date'] = 'required';
        $rules['meeting_time'] = 'required';
        $rules['meeting_participants'] = 'required';
        $rules['meeting_description'] = 'required';
        $rules['meeting_type_id'] = 'required';

        if ($request->meeting_move_to_close == 1) {
            $rules['meeting_outcome'] = 'required';
            $rules['close_meeting_note'] = 'required';
            $rules['meeting_reminder_date_time'] = 'required';
        }

        if ($request->meeting_type_id == 2) {
            $rules['meeting_outcome'] = 'required';
        }
        if ($request->meeting_type_id == 1) {
            $rules['meeting_reminder_date_time'] = 'required';
        }

        $customMessage = array();
        $customMessage['meeting_user_id.required'] = "Invalid parameters";


        $validator = Validator::make($request->all(), $rules, $customMessage);

        if ($validator->fails()) {

            $response = errorRes("The request could not be understood by the server due to malformed syntax");
            $response['data'] = $validator->errors();
        } else {

            $meeting_date_time = date('Y-m-d H:i:s', strtotime($request->meeting_date . "  " . $request->meeting_time));

            if ($request->meeting_id != 0) {
                $UserMeeting = UserMeetingAction::find($request->meeting_id);
                $UserMeeting->updateby = Auth::user()->id;
                $UserMeeting->updateip = $request->ip();
            } else {
                $UserMeeting = new UserMeetingAction();
                $UserMeeting->entryby = Auth::user()->id;
                $UserMeeting->entryip = $request->ip();
                $UserMeeting->updateby = Auth::user()->id;
                $UserMeeting->updateip = $request->ip();
            }

            $UserMeeting->user_id = $request->meeting_user_id;
            $UserMeeting->title_id = $request->meeting_title_id;
            $UserMeeting->type_id = $request->meeting_type_id;
            $UserMeeting->location = $request->meeting_location;
            $UserMeeting->meeting_date_time = $meeting_date_time;
            $UserMeeting->description = $request->meeting_description;
            if ($request->meeting_type_id == 1) {
                $meeting_reminder = getReminderTimeSlot($meeting_date_time)[$request->meeting_reminder_date_time]['datetime'];
                
                $UserMeeting->is_notification = 1;
                $UserMeeting->reminder = $meeting_reminder;
                $UserMeeting->reminder_id = $request->meeting_reminder_date_time;
            }
            $askForStatusChange = 0;

            if (isset($request->meeting_move_to_close) && $request->meeting_move_to_close == "1" || $request->meeting_type_id == 2) {
                $is_action = 1;
                $UserMeeting->is_closed = 1;
                $UserMeeting->closed_date_time = date("Y-m-d H:i:s");
                $UserMeeting->close_note = $request->close_meeting_note;
                $UserMeeting->outcome_type = $request->meeting_outcome;
            }

            $UserMeeting->save();

            if ($request->meeting_id == 0) {
                if (isset($request->meeting_participants)) {
                    foreach ($request->meeting_participants as $value) {
                        $valuePieces = explode("-", $value);
                        $UserMeetingParticipant = new UserMeetingParticipant();
                        $UserMeetingParticipant->user_id = $UserMeeting->user_id;
                        $UserMeetingParticipant->meeting_id = $UserMeeting->id;
                        $UserMeetingParticipant->type = $valuePieces[0];
                        $UserMeetingParticipant->participant_id = $valuePieces[1];
                        $UserMeetingParticipant->entryby = Auth::user()->id;
                        $UserMeetingParticipant->entryip = $request->ip();
                        $UserMeetingParticipant->updateby = Auth::user()->id;
                        $UserMeetingParticipant->updateip = $request->ip();
                        $UserMeetingParticipant->save();
                    }
                }
            }

            $meeting_title = CRMSettingMeetingTitle::select('id', 'name as text');
            $meeting_title->where('crm_setting_meeting_title.status', 1);
            $meeting_title->where('crm_setting_meeting_title.id', $request->meeting_title_id);
            $meeting_title = $meeting_title->first();

            $UserUpdate = new UserNotes();
            $UserUpdate->user_id = $request->meeting_user_id;
            $UserUpdate->note_title = $request->note;
            if ($UserMeeting->is_closed == 1) {
                $UserUpdate->note_type = "Close Meeting";
                $UserUpdate->note = $request->close_meeting_note;
            } else if ($UserMeeting->is_closed == 0) {
                $UserUpdate->note_type = "Open Meeting";
                $UserUpdate->note = $request->meeting_description;
            }
            $UserUpdate->note_title = $meeting_title->text;
            $UserUpdate->entryby = Auth::user()->id;
            $UserUpdate->entryip = $request->ip();
            $UserUpdate->updateby = Auth::user()->id;
            $UserUpdate->updateip = $request->ip();
            $UserUpdate->save();

            $response = successRes("Successfully saved meeting");
            $response['id'] = $UserMeeting->user_id;
            $response['is_action'] = $is_action;
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

        $q = $request->q;


        // $Lead_Detail = Lead::find($request->user_id);
        $UserContact = UserContact::select('user_contact.id', 'user_contact.first_name', 'user_contact.last_name', DB::raw("CONCAT(user_contact.first_name,' ',user_contact.last_name) AS full_name"));

        $UserContact->where('user_contact.user_id', $request->user_id);
        $UserContact->where(function ($query) use ($q) {
            $query->where('user_contact.first_name', 'like', '%' . $q . '%');
            $query->orWhere('user_contact.last_name', 'like', '%' . $q . '%');
        });

        $UserContact->limit(5);
        $UserContact = $UserContact->get();

        if (count($UserContact) > 0) {
            foreach ($UserContact as $User_key => $User_value) {

                $UserResponse[$User_key]['id'] = "lead_contacts-" . $User_value['id'];
                $UserResponse[$User_key]['text'] = "Contact - " . $User_value['full_name'];
            }
        }

        $sales_parent_herarchi = getParentSalePersonsIdsforLead($request->user_id);
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

        $UserMeeting = UserMeetingAction::find($request->id);
        if ($UserMeeting) {

            $UserMeeting['meeting_date'] = date('d-m-Y', strtotime($UserMeeting['meeting_date_time']));
            $UserMeeting['meeting_time'] = date('h:i A', strtotime($UserMeeting['meeting_date_time']));

            $UserMeeting['reminder_text'] = getReminderTimeSlot()[$UserMeeting['reminder_id']]['name'];

            $CRMSettingMeetingTitle = CRMSettingMeetingTitle::select('id', 'name as text')->find($UserMeeting['title_id']);

            if ($CRMSettingMeetingTitle) {
                $UserMeeting['title'] = $CRMSettingMeetingTitle;
            }

            $CRMSettingMeetingType = CRMSettingMeetingType::select('id', 'name as text')->find($UserMeeting['type_id']);
            if ($CRMSettingMeetingType) {
                $UserMeeting['type'] = $CRMSettingMeetingType;
            }

            $LeadType = Lead::select('is_deal', 'status')->find($UserMeeting['lead_id']);
            if ($LeadType) {
                $UserMeeting['lead_type'] = $LeadType;
                $LeadStatus = getLeadStatus();
                foreach ($LeadStatus as $key => $value) {
                    if ($value['id'] == $LeadType['status']) {
                        $UserMeeting['lead_status'] = $value['name'];
                        break;
                    } else {
                        $UserMeeting['lead_status'] = "";
                    }
                }
            }

            $UserMeetingParticipant = UserMeetingParticipant::where('meeting_id', $UserMeeting['id'])->orderby('id', 'asc')->get();

            $UsersId = array();
            $ContactIds = array();

            foreach ($UserMeetingParticipant as $key => $value) {

                if ($value['type'] == "users") {
                    $UsersId[] = $value['participant_id'];
                }
            }


            foreach ($UserMeetingParticipant as $key => $value) {

                if ($value['type'] == "lead_contacts") {
                    $ContactIds[] = $value['participant_id'];
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


            $UserMeeting['user_meeting_participant'] = $UserResponse;

            $response = successRes("Lead meeting detail");
            $response['data'] = $UserMeeting;
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
}