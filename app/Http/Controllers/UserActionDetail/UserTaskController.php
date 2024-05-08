<?php

namespace App\Http\Controllers\UserActionDetail;

use App\Http\Controllers\Controller;

use App\Models\UserTaskAction;
use App\Models\User;
use App\Models\UserNotes;
use App\Models\CRMSettingTaskOutcomeType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use DB;
use File;

class UserTaskController extends Controller
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


        $rules['task_user_id'] = 'required';
        $rules['task_id'] = 'required';
        $rules['task_assign_to'] = 'required';
        $rules['user_task'] = 'required';
        $rules['task_due_date'] = 'required';
        $rules['task_due_time'] = 'required';
        $rules['task_reminder_date_time'] = 'required';
        $rules['task_description'] = 'required';
        if ($request->task_move_to_close == "1") {
            $rules['task_outcome'] = 'required';
            $rules['task_description'] = 'required';
            $rules['task_closing_note'] = 'required';
        }
        $customMessage = array();
        $customMessage['task_user_id.required'] = "Invalid parameters";
        $customMessage['task_id.required'] = "Invalid parameters";


        $validator = Validator::make($request->all(), $rules, $customMessage);

        if ($validator->fails()) {
            $response = errorRes("The request could not be understood by the server due to malformed syntax");
            $response['data'] = $validator->errors();
        } else {
            $task_due_date_time = date('Y-m-d H:i:s', strtotime($request->task_due_date . "  " . $request->task_due_time));
            $reminder_date_time = getReminderTimeSlot($task_due_date_time)[$request->task_reminder_date_time]['datetime'];

            if ($request->task_id == 0) {
                $UserTask = new UserTaskAction();
                $UserTask->entryby = Auth::user()->id;
                $UserTask->entryip = $request->ip();
                $UserTask->updateby = Auth::user()->id;
                $UserTask->updateip = $request->ip();
            } else {
                $UserTask = UserTaskAction::find($request->task_id);
                $UserTask->updateby = Auth::user()->id;
                $UserTask->updateip = $request->ip();
            }

            $UserTask->user_id = $request->task_user_id;
            if ($request->task_assign_to == 0) {
                $UserTask->assign_to = Auth::user()->id;
            } else {
                $UserTask->assign_to = $request->task_assign_to;
            }

            $UserTask->task = $request->user_task;
            $UserTask->due_date_time = $task_due_date_time;
            $UserTask->is_notification = 1;
            $UserTask->reminder = $reminder_date_time;
            $UserTask->reminder_id = $request->task_reminder_date_time;
            $UserTask->description = $request->task_description;
            if (isset($request->task_outcome)) {
                $UserTask->outcome_type = $request->task_outcome;
            }
            $UserTask->is_notification = 1;

            if (isset($request->task_move_to_close) && $request->task_move_to_close == "1") {
                $is_action = 1;
                $UserTask->is_closed = 1;
                $UserTask->closed_date_time = date("Y-m-d H:i:s");
                $UserTask->close_note = $request->task_closing_note;
            }

            $UserTask->save();


            $UserUpdate = new UserNotes();
            $UserUpdate->user_id = $request->task_user_id;
            if ($UserTask->is_closed == 1) {
                $UserUpdate->note_type = "Close Task";
                $UserUpdate->note = $request->task_closing_note;
            } else if ($UserTask->is_closed == 0) {
                $UserUpdate->note_type = "Open Task";
                $UserUpdate->note = $request->task_description;
            }
            $UserUpdate->note_title = $request->user_task;
            $UserUpdate->entryby = Auth::user()->id;
            $UserUpdate->entryip = $request->ip();
            $UserUpdate->updateby = Auth::user()->id;
            $UserUpdate->updateip = $request->ip();
            $UserUpdate->save();

            $response = successRes("Successfully saved task");
            $response['id'] = $UserTask->user_id;
            $response['is_action'] = $is_action;
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }


    function searchAssignedTo(Request $request)
    {

        $q = $request->q;
        // $sales_parent_herarchi = getParentSalePersonsIdsforLead($Lead_Detail->assigned_to);
        $User = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"));

        $User->where('users.status', 1);
        $User->where('users.type', 2);
        // $User->whereIn('users.id', $sales_parent_herarchi);
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

        $data = CRMSettingTaskOutcomeType::select('id', 'name as text');
        $data->where('crm_setting_task_outcome_type.status', 1);
        $data->where('crm_setting_task_outcome_type.name', 'like', "%" . $searchKeyword . "%");
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

        $UserTask = UserTaskAction::find($request->id);
        if ($UserTask) {

            $UserTask['due_date'] = date('d-m-Y', strtotime($UserTask['due_date_time']));
            $UserTask['due_time'] = date('h:i A', strtotime($UserTask['due_date_time']));

            $UserTask['reminder_text'] = getReminderTimeSlot()[$UserTask['reminder_id']]['name'];
            
            $User = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"));

            $User->where('users.id', $UserTask->assign_to);
            $User = $User->first();

            $UserTask['assign_to'] = $User;

            $response = successRes("User task detail");
            $response['data'] = $UserTask;
        } else {
            $response = errorRes("Somethng went wrong");
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }
}