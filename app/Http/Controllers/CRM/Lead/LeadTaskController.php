<?php

namespace App\Http\Controllers\CRM\Lead;

use App\Http\Controllers\Controller;

use App\Models\Lead;
use App\Models\LeadTask;
use App\Models\User;
use App\Models\LeadUpdate;
use App\Models\CRMSettingTaskOutcomeType;
use App\Models\CRMSettingAdditionalInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use DB;
use File;

class LeadTaskController extends Controller
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


        $rules['lead_task_id'] = 'required';
        $rules['lead_task_lead_id'] = 'required';
        $rules['lead_task_assign_to'] = 'required';
        $rules['lead_task_task'] = 'required';

        $rules['lead_task_due_date'] = 'required';
        $rules['lead_task_due_time'] = 'required';

        $rules['lead_task_reminder_date_time'] = 'required';

        $rules['lead_task_description'] = 'required';
        if($request->lead_task_move_to_close == "1")
        {
            $rules['lead_task_task_outcome'] = 'required';
            $rules['lead_task_description'] = 'required';
        }
        $customMessage = array();
        $customMessage['lead_task_lead_id.required'] = "Invalid parameters";


        $validator = Validator::make($request->all(), $rules, $customMessage);

        if ($validator->fails()) {
            $response = errorRes("The request could not be understood by the server due to malformed syntax");
            $response['data'] = $validator->errors();
        } else {

            $task_due_date_time = date('Y-m-d H:i:s', strtotime($request->lead_task_due_date . "  " . $request->lead_task_due_time));
            $reminder_date_time = getReminderTimeSlot($task_due_date_time)[$request->lead_task_reminder_date_time]['datetime'];
            
            if ($request->lead_task_id == 0) {
                $LeadTask = new LeadTask();
            } else {
                $LeadTask = LeadTask::find($request->lead_task_id);
            }

            $LeadTask->lead_id = $request->lead_task_lead_id;
            $LeadTask->user_id = Auth::user()->id;
            if($request->lead_task_assign_to == 0){
                $LeadTask->assign_to = Auth::user()->id;
            } else{
                $LeadTask->assign_to = $request->lead_task_assign_to;
            }

            $LeadTask->task = $request->lead_task_task;
            $LeadTask->due_date_time = $task_due_date_time;
            $LeadTask->reminder = $reminder_date_time;
            $LeadTask->reminder_id = $request->lead_task_reminder_date_time;
            $LeadTask->description = $request->lead_task_description;
            $LeadTask->outcome_type = $request->lead_task_task_outcome;
            $LeadTask->is_notification = 1;

            if (isset($request->lead_task_move_to_close) && $request->lead_task_move_to_close == "1") {
                $is_action = 1;
                $LeadTask->is_closed = 1;
                $LeadTask->closed_date_time = date("Y-m-d H:i:s");
                $LeadTask->close_note = $request->lead_task_closing_note;
            }

            if (isset($request->lead_task_move_to_close) && $request->lead_task_move_to_close == "1" && isset($LeadTask->is_autogenerate) || $LeadTask->is_autogenerate == 1){
                $LeadTask->architect_name = $request->lead_task_add_info_arc;
                $LeadTask->electrician_name = $request->lead_task_add_info_ele;
                $LeadTask->additional_info = $request->lead_task_add_info;
                if(isset($request->lead_task_add_info_text) && $request->lead_task_add_info_text != '') {
                    $LeadTask->additional_info_text = $request->lead_task_add_info_text;
                }
            }

            $LeadTask->save();

            if($LeadTask->is_closed == "1" &&  $LeadTask->is_autogenerate == 1 && Auth::user()->type == 11 && $LeadTask->outcome_type == 101) {
                $Lead = Lead::find($LeadTask->lead_id);
                $Lead->service_verification = 2;
                $Lead->save();
            } else if($LeadTask->is_closed == "1" &&  $LeadTask->is_autogenerate == 1 && Auth::user()->type == 11 && $LeadTask->outcome_type == 102) {
                $Lead = Lead::find($LeadTask->lead_id);
                $Lead->service_verification = 3;
                $Lead->save();
            }

            if($LeadTask->is_closed == "1" &&  $LeadTask->is_autogenerate == 1 && Auth::user()->type == 1){
                $Lead = Lead::find($LeadTask->lead_id);
                $Lead->companyadmin_verification = 2;
                $Lead->save();
            }

            // if($LeadTask){
            //     if (isset($request->lead_task_move_to_close) && $request->lead_task_move_to_close == "1") {
            //         $outcome_type = CRMSettingTaskOutcomeType::find($LeadTask->outcome_type);
    
            //         if($outcome_type['is_reschedule'] == 1) {
            //             $task_due_date_time = date('Y-m-d H:i:s', strtotime($LeadTask->due_date_time . " +1 days"));
            //             $re_reminder_date_time = getReminderTimeSlot($task_due_date_time)[$LeadTask->reminder_id]['datetime'];
    
            //             $re_LeadTask = new LeadTask();
    
            //             $re_LeadTask->lead_id = $LeadTask->lead_id;
            //             $re_LeadTask->user_id = $LeadTask->user_id;
            //             $re_LeadTask->assign_to = $LeadTask->assign_to;
            //             $re_LeadTask->task = $LeadTask->task;
            //             $re_LeadTask->due_date_time = $task_due_date_time;
            //             $re_LeadTask->is_notification = 1;
            //             $re_LeadTask->description = $LeadTask->description;
            //             $re_LeadTask->reminder = $re_reminder_date_time;
            //             $re_LeadTask->reminder_id = $LeadTask->reminder_id;
    
            //             $re_LeadTask->save();
            //         }
            //     }
            // }
            $statusupdate = '';
            if(isset($request->lead_task_status))
            {
                if($request->lead_task_id != 0)
                {
                    $statusupdate = saveLeadAndDealStatusInAction($LeadTask->lead_id, $request->lead_task_status,$request->ip());
                }
            }
           

            $LeadUpdate = new LeadUpdate();
            $LeadUpdate->user_id = Auth::user()->id;
            $LeadUpdate->lead_id = $LeadTask->lead_id;
            if($LeadTask->is_closed == 1)
            {
                $LeadUpdate->task = "Close Task";
                if ($request->lead_task_closing_note != null && $request->lead_task_closing_note != '') {
                    $LeadUpdate->message = $request->lead_task_closing_note;
                } else {
                    $LeadUpdate->message = '';
                }
            }
            else if($LeadTask->is_closed == 0)
            {
                $LeadUpdate->task = "Open Task";
                $LeadUpdate->message = $request->lead_task_description;
            }
            $LeadUpdate->task_title = $request->lead_task_task;
            $LeadUpdate->save();

            $response = successRes("Successfully saved task");
            $response['task_id'] = $LeadTask->id;
            $response['id'] = $LeadTask->lead_id;
            $response['is_action'] = $is_action;
            $response['lead_task_auto_generate'] = $request->lead_task_auto_generate;
            $response['user_type'] = Auth::user()->type;
            $response['data'] = $statusupdate;
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }


    function searchAssignedTo(Request $request)
    {


        // if ($isSalePerson == 1) {
        //     $childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
        // }


        $q = $request->q;
        $Lead_Detail = Lead::find($request->lead_id);
        $sales_parent_herarchi = getParentSalePersonsIdsforLead($Lead_Detail->assigned_to);
        $User = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"));

        $User->where('users.status', 1);
        // $User->where('users.type', 2);
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

    function searchTaskOutcomeType(Request $request)
    {

        $searchKeyword = isset($request->q) ? $request->q : "";
        if(Auth::user()->type == 11 && $request->is_auto_generate == 1) {

            $LeadStatus = getTaskOutComeType();

            $data[] = array();

            foreach ($LeadStatus as $key => $value) {
                $countFinal = count($data);
                $data[$countFinal] = array();
                $data[$countFinal]['id'] = $value['id'];
                $data[$countFinal]['text'] = $value['name'];
            }
        } else {
            $data = CRMSettingTaskOutcomeType::select('id', 'name as text');
            $data->where('crm_setting_task_outcome_type.status', 1);
            $data->where('crm_setting_task_outcome_type.name', 'like', "%" . $searchKeyword . "%");
            $data->limit(5);
            $data = $data->get();
        }

        $response = array();
        $response['results'] = $data;
        $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');

        //CRMSettingScheduleCallType
    }

    function detail(Request $request)
    {

        $LeadTask = LeadTask::find($request->id);
        if ($LeadTask) {
            $LeadTask = json_encode($LeadTask);
            $LeadTask = json_decode($LeadTask, true);

            $LeadTask['due_date'] = date('d-m-Y', strtotime($LeadTask['due_date_time']));
            $LeadTask['due_time'] = date('h:i A', strtotime($LeadTask['due_date_time']));

            $LeadTask['reminder_text'] = getReminderTimeSlot()[$LeadTask['reminder_id']]['name'];


            $User = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"));

            $User->where('users.id', $LeadTask['assign_to']);
            $User = $User->first();

            $LeadTask['assign_to'] = $User;

            // $LeadTask['due_date_time'] = date('Y-m-d H:i:s', strtotime($LeadTask['due_date_time']  . " +5 hours"));
            // $LeadTask['due_date_time'] = date('Y-m-d', strtotime($LeadTask['due_date_time'] . " +30 minutes"));

            // $LeadTask['reminder'] = date('Y-m-d H:i:s', strtotime($LeadTask['reminder']  . " +5 hours"));
            // $LeadTask['reminder'] = date('Y-m-d', strtotime($LeadTask['reminder'] . " +30 minutes"));

            $LeadType = Lead::select('is_deal', 'status','phone_number')->find($LeadTask['lead_id']);
            if($LeadType)
            {
                $LeadType = json_encode($LeadType);
                $LeadType = json_decode($LeadType, true);
                $LeadTask['lead_type'] = $LeadType;
                $LeadStatus = getLeadStatus();
                foreach ($LeadStatus as $key => $value) {
                    if($value['id'] ==  $LeadType['status'])
                    {
                        $LeadTask['lead_status'] = $value['name'];
                        break;
                    }
                    else{
                        $LeadTask['lead_status'] = "";
                    }
                }
            }


            // $CRMSettingCallType = CRMSettingCallType::select('id', 'name as text')->find($LeadTask['type_id']);

            // if ($CRMSettingCallType) {
            //     $CRMSettingCallType = json_encode($CRMSettingCallType);
            //     $CRMSettingCallType = json_decode($CRMSettingCallType, true);
            //     $LeadTask['type'] = $CRMSettingCallType;
            // }



            $response = successRes("Lead task detail");
            $response['data'] = $LeadTask;
        } else {
            $response = errorRes("Somethng went wrong");
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function getTaskDetail(Request $request){
        if($request->id != 0 && $request->id != '' && $request->id != null){
            $Task = LeadTask::find($request->id);

            $Lead = Lead::select(DB::raw("CONCAT(leads.id,'-',leads.first_name,' ',leads.last_name) AS text"),'leads.phone_number')->where('id', $Task->lead_id)->first();
            $Task['lead_detail'] = "";
            $Task['lead_mobile'] = "";
            if($Lead){
                $Task['lead_detail'] = $Lead->text;
                $Task['lead_mobile'] = $Lead->phone_number;
            }

            $Task['created_by'] =  User::select(DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"))->where('id', $Task->user_id)->first()->text;
           
            $Task['due_date_time'] = date('d/m/Y g:i A', strtotime($Task->due_date_time));

            if($Task['close_note'] == ''){
                $Task['close_note'] = '-'; 
            }

            if($Task['closed_date_time'] == ''){
                $Task['closed_date_time'] = '-'; 
            } else {
                $Task['closed_date_time'] = date('d/m/Y g:i A', strtotime($Task->closed_date_time)); 
            }

            if($Task['additional_info'] == ''){
                $Task['additional_info'] = '-'; 
            } else {
                $Task['additional_info'] = CRMSettingAdditionalInfo::find($Task['additional_info'])->name; 
            }

            if($Task['outcome_type'] == ''){
                $Task['outcome_type'] = '-'; 
            } else {
                if($Task->outcome_type > 100){
                    $Task['outcome_type'] = getTaskOutComeType()[$Task->outcome_type]['name']; 
                } else {
                    $Task['outcome_type'] = CRMSettingTaskOutcomeType::find($Task->outcome_type)->name; 
                }
            }
            
            $Task = json_decode(json_encode($Task), true);
            $Task['created_at'] = date('d/m/Y g:i A', strtotime($Task['created_at']));

            $response = successRes();
            $response['data'] = $Task;
        } else {
            $response = errorRes("Task Not Available");
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }
}