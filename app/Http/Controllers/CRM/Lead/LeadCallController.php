<?php

namespace App\Http\Controllers\CRM\Lead;

use App\Http\Controllers\Controller;

use App\Models\Lead;
use App\Models\LeadCall;
use App\Models\LeadTask;
use App\Models\LeadUpdate;
use App\Models\CRMSettingCallType;
use App\Models\CRMSettingCallOutcomeType;
use App\Models\CRMSettingAdditionalInfo;
use App\Models\LeadContact;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use DB;
use File;

class LeadCallController extends Controller
{

    public function __construct()
    {

        $this->middleware(function ($request, $next) {

            $tabCanAccessBy = array(0, 1, 2, 6, 9, 11, 13, 101, 102, 103, 104, 105, 202, 302);

            if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
                return redirect()->route('dashboard');
            }

            return $next($request);
        });
    }

    public function save(Request $request)
    {
        if($request->lead_is_auto_call == 0){
            $rules = array();
            $rules['lead_call_lead_id'] = 'required';
            $rules['lead_call_type_id'] = 'required';
            $rules['lead_call_contact_name'] = 'required';
            $rules['lead_call_schedule_date'] = 'required';

            if(Auth::user()->type == 9){
                $rules['lead_call_assign_user'] = 'required';
            }
            $rules['lead_call_schedule_time'] = 'required';

            if ($request->lead_call_move_to_close == 1) {
                $rules['lead_call_call_outcome'] = 'required';
            $rules['lead_call_reminder_date_time'] = 'required';
            } else {
                $rules['lead_call_purpose'] = 'required';
            }

            if ($request->lead_call_type_id == 2) {
                $rules['lead_call_call_outcome'] = 'required';
            }
            if ($request->lead_call_type_id == 1) {
            $rules['lead_call_reminder_date_time'] = 'required';
            }
            // $rules['lead_call_description'] = 'required';
            $rules['lead_call_id'] = 'required';
            $customMessage = array();
            $customMessage['lead_file_lead_id.required'] = "Invalid parameters";


            $validator = Validator::make($request->all(), $rules, $customMessage);

            if ($validator->fails()) {

                $response = errorRes("The request could not be understood by the server due to malformed syntax");
                $response['data'] = $validator->errors();
            } else {

                $leadStatus = getLeadStatus();
                $askForStatusChange = 0;

                $hasCall = LeadCall::where('lead_id', $request->lead_call_lead_id)->first();
                $lead_detail = Lead::find($request->lead_call_lead_id);
                $statusArray = array();
                $convertToCall = 0;

                $lead_call_schedule = date('Y-m-d H:i:s', strtotime($request->lead_call_schedule_date . "  " . $request->lead_call_schedule_time));

                // is 0 then refresh open action 
                $is_action = 0;

                if (!$hasCall) {
                    $convertToCall = 1;
                }

                if ($request->lead_call_id == 0) {
                    $LeadCall = new LeadCall();
                } else {
                    $LeadCall = LeadCall::find($request->lead_call_id);
                }

                if(Auth::user()->type == 9){
                    if($request->lead_call_assign_user == 0){
                        $LeadCall->user_id = Auth::user()->id;
                    } else {
                        $LeadCall->user_id = $request->lead_call_assign_user;
                    }
                } else {
                    $LeadCall->user_id = Auth::user()->id;
                }

                $LeadCall->type_id = $request->lead_call_type_id;
                $LeadCall->lead_id = $request->lead_call_lead_id;
                $LeadCall->contact_name = $request->lead_call_contact_name;
                $LeadCall->call_schedule = $lead_call_schedule;
                if ($request->lead_call_type_id == 1) {

                    $reminder_date_time = getReminderTimeSlot($lead_call_schedule)[$request->lead_call_reminder_date_time]['datetime'];

                    $LeadCall->is_notification = 1;
                    $LeadCall->reminder = $reminder_date_time;
                    $LeadCall->reminder_id = $request->lead_call_reminder_date_time;
                }

                $LeadCall->purpose = $request->lead_call_purpose;
                
                $lead_call_description = isset($request->lead_call_description) ? $request->lead_call_description : "";
                $LeadCall->description = $lead_call_description;
                if (isset($request->lead_call_move_to_close) && $request->lead_call_move_to_close == "1" || $request->lead_call_type_id == 2) {
                    $is_action = 1;
                    $LeadCall->is_closed = 1;
                    $LeadCall->outcome_type = $request->lead_call_call_outcome;
                    $LeadCall->closed_date_time = date("Y-m-d H:i:s");
                    $LeadCall->close_note = $request->lead_call_closing_note;


                    $Lead = Lead::find($LeadCall->lead_id);

                    if ($Lead->status == 2) {
                        $askForStatusChange = 1;
                        $statusArray[] = $leadStatus[2];
                        $statusArray[] = $leadStatus[3];
                        $statusArray[] = $leadStatus[4];
                    }
                }

                

                $LeadCall->save();
                $statusupdate = '';
                if(isset($request->lead_call_status))
                {
                    if ($request->lead_call_id != 0 || $request->lead_call_type_id == 2) {
                        $statusupdate = saveLeadAndDealStatusInAction($LeadCall->lead_id, $request->lead_call_status, $request->ip());
                    }
                }

                if ($lead_detail->status == 2) {
                    $noOfCall = LeadCall::where('lead_id', $lead_detail->id)->count();
                    if ($noOfCall > 4) {
                        $oldStatus = $lead_detail->status;
                        $lead_detail->status = 5;
                        $lead_detail->save();
                        $newStatus = $lead_detail->status;

                        if ($oldStatus != $newStatus) {

                            $timeline = array();
                            $timeline['lead_id'] = $lead_detail->id;
                            $timeline['type'] = "lead-status-auto-change";
                            $timeline['reffrance_id'] = $lead_detail->id;
                            $timeline['description'] = "Lead status auto changed from  " . $leadStatus[$oldStatus]['name'] . " to " . $leadStatus[$newStatus]['name'] . " due to same status change by " . Auth::user()->first_name . " " . Auth::user()->last_name;
                            $timeline['source'] = "WEB";
                            saveLeadTimeline($timeline);
                        }
                    }
                }

                $LeadUpdate = new LeadUpdate();
                $LeadUpdate->user_id = Auth::user()->id;
                $LeadUpdate->lead_id = $LeadCall->lead_id;
                if ($LeadCall->is_closed == 1) {
                    $LeadUpdate->task = "Close Call";
                    if ($request->lead_call_closing_note != null && $request->lead_call_closing_note != '') {
                        $LeadUpdate->message = $request->lead_call_closing_note;
                    } else {
                        $LeadUpdate->message = '';
                    }
                } else if ($LeadCall->is_closed == 0) {
                    $LeadUpdate->task = "Open Call";
                    if ($request->lead_call_description != null && $request->lead_call_description != '') {
                        $LeadUpdate->message = $request->lead_call_description;
                    } else {
                        $LeadUpdate->message = '';
                    }
                }
                $LeadUpdate->task_title = $request->lead_call_purpose;
                $LeadUpdate->save();


                $response = successRes("Successfully saved call");
                $response['id'] = $LeadCall->lead_id;
                $response['ask_for_status_change'] = $askForStatusChange;
                $response['status_array'] = $statusArray;
                $response['is_action'] = $is_action;
                $response['data'] = $statusupdate;
                
            }

            return response()->json($response)->header('Content-Type', 'application/json');
        } else if($request->lead_is_auto_call == 1) {
            $rules = array();
            $rules['lead_auto_call_lead_id'] = 'required';
            $rules['lead_auto_call_outcome'] = 'required';

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {

                $response = errorRes("The request could not be understood by the server due to malformed syntax");
                $response['data'] = $validator->errors();
            } else {

                 // is 0 then refresh open action 
                 $is_action = 0;

                $lead_call_schedule = date('Y-m-d H:i:s', strtotime($request->lead_auto_call_schedule_date . "  " . $request->lead_auto_call_schedule_time));

                $Lead_contact = Lead::find($request->lead_auto_call_lead_id);
                $Lead_call_type = 0;
                if ($request->lead_auto_call_id == 0) {
                    $LeadCall = new LeadCall();
                    $LeadCall->type_id = 2;
                    $Lead_call_type = 2;
                } else {
                    $LeadCall = LeadCall::find($request->lead_auto_call_id);
                    $LeadCall->type_id = $LeadCall->type_id;
                    $Lead_call_type = $LeadCall->type_id;
                }
                $LeadCall->user_id = Auth::user()->id;
                $LeadCall->lead_id = $request->lead_auto_call_lead_id;
                $LeadCall->contact_name = $Lead_contact->main_contact_id;
                $LeadCall->call_schedule = $lead_call_schedule;
                
                $reminder_date_time = getReminderTimeSlot($lead_call_schedule)[1]['datetime'];
                $LeadCall->is_notification = 1;
                $LeadCall->reminder = $reminder_date_time;
                $LeadCall->reminder_id = 1;

                if(isset($request->lead_auto_call_move_to_close) && $request->lead_auto_call_move_to_close == "1" || $Lead_call_type == 2){
                    $is_action = 1;
                    $LeadCall->is_closed = 1;
                    $LeadCall->outcome_type = $request->lead_auto_call_outcome;
                    $is_reschedule = CRMSettingCallOutcomeType::find($request->lead_auto_call_outcome)->is_reschedule;
                    if($is_reschedule == 0){
                        $LeadCall->architect_name = $request->lead_auto_call_add_info_arc;
                        $LeadCall->electrician_name = $request->lead_auto_call_add_info_ele;
                        $LeadCall->additional_info = $request->lead_auto_call_add_info;
                        if(isset($request->lead_auto_call_add_info_text) && $request->lead_auto_call_add_info_text != '') {
                            $LeadCall->additional_info_text = $request->lead_auto_call_add_info_text;
                        }
                        $LeadTask = LeadTask::find($request->lead_auto_task_id);
                        $LeadTask->is_closed = 1;
                        $LeadTask->closed_date_time = date("Y-m-d H:i:s");
                        $LeadTask->close_note = $request->lead_auto_call_closing_note;
                        $LeadTask->outcome_type = 1;
                        $LeadTask->save();
                    }
                    $LeadCall->closed_date_time = date("Y-m-d H:i:s");
                    $LeadCall->close_note = $request->lead_auto_call_closing_note;
                }

                $LeadCall->purpose = $request->lead_auto_task;
                $LeadCall->description = $request->lead_auto_task_description;
                $LeadCall->reference_id = $request->lead_auto_task_id;
                $LeadCall->reference_type = "Task";
                $LeadCall->save();

                $Call_Not_Recevied = 0;
                if (isset($request->lead_auto_call_move_to_close) && $request->lead_auto_call_move_to_close == "1" && Auth::user()->type == 9){
                    $outcome_type = CRMSettingCallOutcomeType::find($LeadCall->outcome_type);
                    $call_count = $LeadCall::query()->where('reference_id', $LeadCall->reference_id)->where('is_closed', 1)->count();
                    $lead = Lead::find($LeadCall->lead_id);
                    if(in_array($outcome_type->is_reschedule, array(1, 2))) {
                        if($call_count > 3){
                            $lead->telesales_verification = 3;

                            $LeadTask = LeadTask::find($LeadCall->reference_id);
                            $LeadTask->is_closed = 1;
                            $LeadTask->closed_date_time = date("Y-m-d H:i:s");
                            $LeadTask->close_note = $LeadCall->close_note;
                            $LeadTask->outcome_type = 1;
                            $LeadTask->save();

                            $Call_Not_Recevied = 1;
                        } else {
                            $lead->telesales_verification = 1;
                        }
                    } else {
                        $lead->telesales_verification = 2;
                    }
                    $lead->save();
                }


                if($Call_Not_Recevied == 0){
                    if (isset($request->lead_auto_call_move_to_close) && $request->lead_auto_call_move_to_close == "1"  || $Lead_call_type == 2){
                        $outcome_type = CRMSettingCallOutcomeType::find($LeadCall->outcome_type);
                        if(in_array($outcome_type['is_reschedule'], [1, 2])) {
                            if($outcome_type['is_reschedule'] == 2){
                                $re_lead_call_schedule = date('Y-m-d H:i:s', strtotime($request->lead_auto_re_call_schedule_date . "  " . $request->lead_auto_call_re_schedule_time));
                                $re_reminder_date_time = getReminderTimeSlot($re_lead_call_schedule)[1]['datetime'];
                            } else {
                                $re_lead_call_schedule = date('Y-m-d H:i:s', strtotime($LeadCall->call_schedule . " +1 days"));
                                $re_reminder_date_time = getReminderTimeSlot($re_lead_call_schedule)[1]['datetime'];
                            }
                            
                            
                            $re_LeadCall = new LeadCall();

                            $re_LeadCall->lead_id = $LeadCall->lead_id;
                            $re_LeadCall->user_id = $LeadCall->user_id;
                            $re_LeadCall->type_id = 1;
                            $re_LeadCall->contact_name = $LeadCall->contact_name;
                            $re_LeadCall->purpose = $LeadCall->purpose;
                            $re_LeadCall->description = $LeadCall->description;
                            $re_LeadCall->is_notification = 1;
                            $re_LeadCall->reminder = $re_reminder_date_time;
                            $re_LeadCall->reminder_id = 1;
                            $re_LeadCall->call_schedule = $re_lead_call_schedule;
                            $re_LeadCall->reference_id = $LeadCall->reference_id;
                            $re_LeadCall->reference_type = $LeadCall->reference_type;

                            $re_LeadCall->save();
                        }
                    }
                }

                
               
                
                $response = successRes("Successfully saved call");
                $response['id'] = $LeadCall->lead_id;
                $response['is_action'] = $is_action;

            }
            return response()->json($response)->header('Content-Type', 'application/json');

            
        }
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

    function searchAdditionalInfo(Request $request)
    {

        $searchKeyword = isset($request->q) ? $request->q : "";

        $data = CRMSettingAdditionalInfo::select('id', 'name as text');
        $data->where('crm_setting_additional_info.status', 1);
        $data->where('crm_setting_additional_info.name', 'like', "%" . $searchKeyword . "%");
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

        $data = LeadContact::select('lead_contacts.id', 'lead_contacts.contact_tag_id', 'lead_contacts.type', 'lead_contacts.phone_number', 'crm_setting_contact_tag.name as tag_name', 'lead_contacts.first_name', 'lead_contacts.last_name', DB::raw("CONCAT(lead_contacts.first_name,' ',lead_contacts.last_name) AS text"));
        $data->leftJoin('crm_setting_contact_tag', 'crm_setting_contact_tag.id', '=', 'lead_contacts.contact_tag_id');
        $data->where('lead_contacts.lead_id', $request->lead_id);
        $data->where('lead_contacts.status', 1);
        $data->where('lead_contacts.first_name', 'like', "%" . $searchKeyword . "%");
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

    function searchAssignedTo(Request $request)
    {

        $q = $request->q;
        $Lead_Detail = Lead::find($request->lead_id);
        $sales_parent_herarchi = getParentSalePersonsIdsforLead($Lead_Detail->assigned_to);
        $User = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"));

        $User->where('users.status', 1);
        $User->whereIn('users.id', $sales_parent_herarchi);
        $User->where(function ($query) use ($q) {
            $query->where('users.first_name', 'like', '%' . $q . '%');
            $query->orWhere('users.last_name', 'like', '%' . $q . '%');
        });

        $User->limit(5);
        $User = $User->get();

        $user_new = array();
        foreach ($User as $key => $value) {
			if ($key == 0) {
				$listMonth1['id'] = 0;
				$listMonth1['text'] = 'SELF';
				$listMonth['id'] = $value['id'];
				$listMonth['text'] = $value['text'];
				array_push($user_new, $listMonth1);
				array_push($user_new, $listMonth);
			} else {
				$listMonth['id'] = $value['id'];
				$listMonth['text'] = $value['text'];
				array_push($user_new, $listMonth);
			}
		}

        $response = array();
        $response['results'] = $user_new;
        $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function detail(Request $request)
    {

        if(isset($request->Call_type) && $request->Call_type == 'is_auto_call'){
            $LeadCall = LeadCall::select('*')->where('reference_id', $request->id)->where('is_closed', 0)->first();
        } else {
            $LeadCall = LeadCall::find($request->id);
        }

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

            $LeadContact = LeadContact::select('lead_contacts.id', DB::raw("CONCAT(lead_contacts.first_name,' ',lead_contacts.last_name) AS text"))->find($LeadCall['contact_name']);
            if ($LeadContact) {
                $LeadContact = json_encode($LeadContact);
                $LeadContact = json_decode($LeadContact, true);
                $LeadCall['contact_name'] = $LeadContact;
            }


            $User = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"));
            $User->where('users.id', $LeadCall['user_id']);
            $User = $User->first();
            $LeadCall['assign_to'] = $User;


            $LeadType = Lead::select('is_deal', 'status')->find($LeadCall['lead_id']);
            if ($LeadType) {
                $LeadType = json_encode($LeadType);
                $LeadType = json_decode($LeadType, true);
                $LeadCall['lead_type'] = $LeadType;
                $LeadStatus = getLeadStatus();
                foreach ($LeadStatus as $key => $value) {
                    if ($value['id'] == $LeadType['status']) {
                        $LeadCall['lead_status'] = $value['name'];
                        break;
                    } else {
                        $LeadCall['lead_status'] = "";
                    }
                }
            }


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
    function getAdditionalInfoDetail(Request $request)
    {
        $AdditionalInfo = CRMSettingAdditionalInfo::find($request->id);
        if ($AdditionalInfo) {
            $AdditionalInfo = json_encode($AdditionalInfo);
            $AdditionalInfo = json_decode($AdditionalInfo, true);

            $response = successRes("Additional Info detail");
            $response['data'] = $AdditionalInfo;
        } else {
            $response = errorRes("Somethng went wrong");
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function getCallOutcomeTypeDetail(Request $request)
    {
        $CallOutcomeType = CRMSettingCallOutcomeType::find($request->id);
        if ($CallOutcomeType) {
            $response = successRes("CallOutcome Type detail");
            $response['data'] = $CallOutcomeType;
        } else {
            $response = errorRes("Somethng went wrong");
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }
    
    public function getCallDetail(Request $request){
        if($request->id != 0 && $request->id != '' && $request->id != null){
            $Call = LeadCall::find($request->id);

            $Lead = Lead::select(DB::raw("CONCAT(leads.id,'-',leads.first_name,' ',leads.last_name) AS text"))->where('id', $Call->lead_id)->first()->text;
            $Call['lead_detail'] = $Lead;

            $Call['created_by'] =  User::select(DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"))->where('id', $Call->user_id)->first()->text;

            $Call['contact_name'] =  LeadContact::select(DB::raw("CONCAT(lead_contacts.first_name,' ',lead_contacts.last_name) AS text"))->where('id', $Call->contact_name)->first()->text;            
           
            $Call['call_schedule'] = date('d/m/Y g:i A', strtotime($Call->call_schedule));

            if($Call['close_note'] == ''){
                $Call['close_note'] = '-'; 
            }

            if($Call['closed_date_time'] == ''){
                $Call['closed_date_time'] = '-'; 
            } else {
                $Call['closed_date_time'] = date('d/m/Y g:i A', strtotime($Call->closed_date_time)); 
            }

            if($Call['additional_info'] == ''){
                $Call['additional_info'] = '-'; 
            } else {
                $Call['additional_info'] = CRMSettingAdditionalInfo::find($Call['additional_info'])->name; 
            }

            if($Call['outcome_type'] == ''){
                $Call['outcome_type'] = '-'; 
            } else {
                $Call['outcome_type'] = CRMSettingCallOutcomeType::find($Call->outcome_type)->name; 
            }
            
            $Call = json_decode(json_encode($Call), true);
            $Call['created_at'] = date('d/m/Y g:i A', strtotime($Call['created_at']));

            $response = successRes();
            $response['data'] = $Call;
        } else {
            $response = errorRes("Call Not Available");
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function getTaskAndCallList(Request $request){
            $TeleSalesTask = LeadTask::select('lead_tasks.*', 'users.first_name', 'users.last_name');
            $TeleSalesTask->where('lead_tasks.lead_id', $request->lead_id);
            $TeleSalesTask->where('lead_tasks.is_autogenerate', 1);
            $TeleSalesTask->where('users.type', 9);
            $TeleSalesTask->leftJoin('users', 'users.id', '=', 'lead_tasks.assign_to');
            $TeleSalesTask = $TeleSalesTask->get();
            $TeleSalesTask = json_decode(json_encode($TeleSalesTask), true);

            $TeleSalesCall = array();
            foreach ($TeleSalesTask as $key => $value) {
                $TeleSalesTask[$key]['created_at'] = date('d/m/Y g:i A', strtotime($value['created_at']));

                $TeleSalesCall = LeadCall::select('lead_calls.*', 'users.first_name', 'users.last_name');
                $TeleSalesCall->where('lead_calls.reference_id', $value['id']);
                $TeleSalesCall->where('lead_calls.reference_type', 'Task');
                $TeleSalesCall->leftJoin('users', 'users.id', '=', 'lead_calls.user_id');
                $TeleSalesCall = $TeleSalesCall->get();
                $TeleSalesCall = json_decode(json_encode($TeleSalesCall), true);
                foreach ($TeleSalesCall as $call_key => $call_value) {
                    $TeleSalesCall[$call_key]['created_at'] = date('d/m/Y g:i A', strtotime($call_value['created_at']));
                }
            }

            $viewData = "";
            foreach ($TeleSalesTask as $task_key => $task_value) {
                $view = "";
                $view .= '<tr style="vertical-align: middle;">';
                $view .= '<td class="col-2"><span class="badge badge-pill badge-soft-primary font-size-11">Task</span></td>';
                $view .= '<td class="col-4">'. $task_value['task'] .'</td>';
                if($task_value['is_closed'] == 1){
                    $view .= '<td class="col-2 text-danger" style="font-weight: 600;">Close</td>';
                } else {
                    $view .= '<td class="col-2 text-success" style="font-weight: 600;">Open</td>';
                }
                $view .= '<td class="col-1" style="font-size: x-large;"><i class="bx bxs-show" onclick="TaskDetail('. $task_value['id'] .')"></i></td>';
                $view .= '</tr>';

                $viewData .= $view;
            }

            foreach ($TeleSalesCall as $call_key => $call_value) {
                $view = "";
                $view .= '<tr style="vertical-align: middle;">';
                $view .= '<td class="col-2"><span class="badge badge-pill badge-soft-info font-size-11">Call</span></td>';
                $view .= '<td class="col-4">'. $call_value['purpose'] .'</td>';
                if($call_value['is_closed'] == 1){
                    $view .= '<td class="col-2 text-danger" style="font-weight: 600;">Close</td>';
                } else {
                    $view .= '<td class="col-2 text-success" style="font-weight: 600;">Open</td>';
                }
                $view .= '<td class="col-1" style="font-size: x-large;"><i class="bx bxs-show" onclick="CallDetail('. $call_value['id'] .')"></i></td>';
                $view .= '</tr>';

                $viewData .= $view;
            }

            $response = successRes("Get List");
            $response['data'] = $viewData;

            return response()->json($response)->header('Content-Type', 'application/json');
    }

}