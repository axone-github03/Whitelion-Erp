<?php

namespace App\Http\Controllers\UserActionDetail;

use App\Http\Controllers\Controller;

use App\Models\UserCallAction;
use App\Models\UserNotes;
use App\Models\CRMSettingCallType;
use App\Models\CRMSettingCallOutcomeType;
use App\Models\UserContact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use DB;
use File;

class UserCallController extends Controller
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

        $rules = array();
        $rules['call_user_id'] = 'required';
        $rules['call_type_id'] = 'required';
        $rules['call_contact_name'] = 'required';
        $rules['call_schedule_date'] = 'required';
        $rules['call_schedule_time'] = 'required';

        if ($request->call_move_to_close == 1) {
            $rules['call_call_outcome'] = 'required';
            // $rules['call_closing_note'] = 'required';
            $rules['call_reminder_date_time'] = 'required';
        } else {
            $rules['call_purpose'] = 'required';
        }

        if ($request->call_type_id == 2) {
            $rules['call_call_outcome'] = 'required';
        }
        if ($request->call_type_id == 1) {
            $rules['call_reminder_date_time'] = 'required';
        }
        // $rules['call_description'] = 'required';
        $rules['call_id'] = 'required';
        $customMessage = array();
        $customMessage['call_user_id.required'] = "Invalid parameters";


        $validator = Validator::make($request->all(), $rules, $customMessage);

        if ($validator->fails()) {

            $response = errorRes("The request could not be understood by the server due to malformed syntax");
            $response['data'] = $validator->errors();
        } else {

            $call_schedule = date('Y-m-d H:i:s', strtotime($request->call_schedule_date . "  " . $request->call_schedule_time));
            // is 0 then refresh open action 
            $is_action = 0;

            if ($request->call_id == 0) {
                $UserCall = new UserCallAction();
            } else {
                $UserCall = UserCallAction::find($request->call_id);
            }
            $UserCall->user_id = $request->call_user_id;
            $UserCall->type_id = $request->call_type_id;
            $UserCall->contact_person = $request->call_contact_name;
            $UserCall->call_schedule = $call_schedule;
            if ($request->call_type_id == 1) {

                $reminder_date_time = getReminderTimeSlot($call_schedule)[$request->call_reminder_date_time]['datetime'];

                $UserCall->is_notification = 1;
                $UserCall->reminder = $reminder_date_time;
                $UserCall->reminder_id = $request->call_reminder_date_time;
            }

            $UserCall->purpose = $request->call_purpose;
            $UserCall->description = $request->call_description;
            if (isset($request->call_move_to_close) && $request->call_move_to_close == "1" || $request->call_type_id == 2) {
                $is_action = 1;
                $UserCall->is_closed = 1;
                $UserCall->outcome_type = $request->call_call_outcome;
                $UserCall->closed_date_time = date("Y-m-d H:i:s");
                $UserCall->close_note = $request->call_closing_note;

            }

            $UserCall->save();


            $LeadUpdate = new UserNotes();
            $LeadUpdate->user_id =  $request->call_user_id;
            if ($UserCall->is_closed == 1) {
                $LeadUpdate->note_type = "Close Call";
                if ($request->call_closing_note != null && $request->call_closing_note != '') {
                    $LeadUpdate->note = $request->call_closing_note;
                } else {
                    $LeadUpdate->note = '';
                }
            } else if ($UserCall->is_closed == 0) {
                $LeadUpdate->note_type = "Open Call";
                if ($request->call_description != null && $request->call_description != '') {
                    $LeadUpdate->note = $request->call_description;
                } else {
                    $LeadUpdate->note = '';
                }
            }
            $LeadUpdate->note_title = $request->call_purpose;
            $LeadUpdate->save();


            $response = successRes("Successfully saved call");
            $response['id'] = $UserCall->user_id;
            $response['is_action'] = $is_action;
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function searchCallType(Request $request)
    {

        $searchKeyword = isset($request->q) ? $request->q : "";

        $data = CRMSettingCallType::select('id', 'name as text');
        $data->where('crm_setting_call_type.status', 1);
        $data->where('crm_setting_call_type.name', 'like', "%" . $searchKeyword . "%");
        $data->limit(5);
        $data = $data->get();
        $response = array();
        $response['results'] = $data;
        $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');

        //CRMSettingScheduleCallType
    }

    function searchCallOutcomeType(Request $request)
    {

        $searchKeyword = isset($request->q) ? $request->q : "";

        $data = CRMSettingCallOutcomeType::select('id', 'name as text');
        $data->where('crm_setting_call_outcome_type.status', 1);
        $data->where('crm_setting_call_outcome_type.name', 'like', "%" . $searchKeyword . "%");
        $data->limit(5);
        $data = $data->get();
        $response = array();
        $response['results'] = $data;
        $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');

        //CRMSettingScheduleCallType
    }

    function searchContact(Request $request)
    {

        $searchKeyword = isset($request->q) ? $request->q : "";

        $data = UserContact::select('user_contact.id', 'user_contact.contact_tag_id', 'user_contact.type', 'user_contact.phone_number', 'crm_setting_contact_tag.name as tag_name', 'user_contact.first_name', 'user_contact.last_name', DB::raw("CONCAT(user_contact.first_name,' ',user_contact.last_name) AS text"));
        $data->leftJoin('crm_setting_contact_tag', 'crm_setting_contact_tag.id', '=', 'user_contact.contact_tag_id');
        $data->where('user_contact.user_id', $request->user_id);
        $data->where('user_contact.first_name', 'like', "%" . $searchKeyword . "%");
        $data->limit(5);
        $data = $data->get();
        $newdata = array();
        foreach ($data as $key => $value) {
            $newdata1['id'] = $value->id;
            $phonenumber = " - " . $value->phone_number;
            $tagname = '';
            if ($value->contact_tag_id == 0) {
                $tagname = " - " . ucwords(strtolower(getUserTypeNameForLeadTag($value->type)));
            } else {
                $tagname = " - " . $value->tag_name;
            }
            $newdata1['text'] = $value->text . $phonenumber . $tagname;
            array_push($newdata, $newdata1);
        }
        $response = array();
        $response['results'] = $newdata;
        $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');

        //CRMSettingScheduleCallType
    }

    function detail(Request $request)
    {

        $LeadCall = UserCallAction::find($request->id);
        if ($LeadCall) {
            $LeadCall = json_encode($LeadCall);
            $LeadCall = json_decode($LeadCall, true);

            $LeadCall['schedule_date'] = date('d-m-Y', strtotime($LeadCall['call_schedule']));
            $LeadCall['schedule_time'] = date('h:i A', strtotime($LeadCall['call_schedule']));

            $LeadCall['reminder_text'] = getReminderTimeSlot()[$LeadCall['reminder_id']]['name'];

            $CRMSettingCallType = CRMSettingCallType::select('id', 'name as text')->find($LeadCall['type_id']);
            if ($CRMSettingCallType) {
                $CRMSettingCallType = json_encode($CRMSettingCallType);
                $CRMSettingCallType = json_decode($CRMSettingCallType, true);
                $LeadCall['type'] = $CRMSettingCallType;
            }

            $LeadContact = UserContact::select('user_contact.id', DB::raw("CONCAT(user_contact.first_name,' ',user_contact.last_name) AS text"))->find($LeadCall['contact_person']);
            if ($LeadContact) {
                $LeadContact = json_encode($LeadContact);
                $LeadContact = json_decode($LeadContact, true);
                $LeadCall['contact_name'] = $LeadContact;
            }



            // $LeadType = Lead::select('is_deal', 'status')->find($LeadCall['lead_id']);
            // if ($LeadType) {
            //     $LeadType = json_encode($LeadType);
            //     $LeadType = json_decode($LeadType, true);
            //     $LeadCall['lead_type'] = $LeadType;
            //     $LeadStatus = getLeadStatus();
            //     foreach ($LeadStatus as $key => $value) {
            //         if ($value['id'] == $LeadType['status']) {
            //             $LeadCall['lead_status'] = $value['name'];
            //             break;
            //         } else {
            //             $LeadCall['lead_status'] = "";
            //         }
            //     }
            // }


            $CRMSettingCallOutcomeType = CRMSettingCallOutcomeType::select('id', 'name as text')->find($LeadCall['outcome_type']);
            if ($CRMSettingCallOutcomeType) {
                $CRMSettingCallOutcomeType = json_encode($CRMSettingCallOutcomeType);
                $CRMSettingCallOutcomeType = json_decode($CRMSettingCallOutcomeType, true);
                $LeadCall['outcome_type'] = $CRMSettingCallOutcomeType;
            }

            $response = successRes("Lead call detail");
            $response['data'] = $LeadCall;
        } else {
            $response = errorRes("Somethng went wrong");
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }
}