<?php

namespace App\Http\Controllers\CRM\Lead;

use App\Http\Controllers\Controller;
use App\Models\LeadMeeting;
use App\Models\LeadTask;
use App\Models\Lead;
use App\Models\LeadCall;
use App\Models\CRMSettingCallType;
use App\Models\CRMSettingMeetingTitle;
use App\Models\User;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeadTeamActionController extends Controller
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

    public function index(Request $request)
    {

        $data = array();
        $data['title'] = "Team Action ";
        return view('crm/lead/teamaction', compact('data'));
    }

    function TodayTeamActionAjax(Request $request)
    {

        $isSalePerson = isSalePerson();
		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
        if ($isSalePerson == 1) {
			$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
		}

        $current_start_date = $request->current_date . " 00:00:00";
        $current_start_date = date('Y-m-d H:i:s', strtotime($current_start_date . " -5 hours"));
        $current_start_date = date('Y-m-d H:i:s', strtotime($current_start_date . " -30 minutes"));

        $current_end_date = $request->current_date . " 23:59:59";
        $current_end_date = date('Y-m-d H:i:s', strtotime($current_end_date . " -5 hours"));
        $current_end_date = date('Y-m-d H:i:s', strtotime($current_end_date . " -30 minutes"));

        $columns = array(
            0 => 'lead_calls.id',
            1 => 'lead_calls.lead_id',
            2 => 'lead_calls.is_closed',
            3 => 'lead_calls.type_id',
            4 => 'lead_calls.purpose',
            5 => 'lead_calls.created_at',
            6 => 'lead_calls.user_id',
            7 => 'leads.first_name',
            8 => 'leads.last_name',
            9 => 'leads.is_deal',
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
        $recordsCallTotal = $data->count();

        $viewCallData = "";
        foreach ($data as $key => $value) {
            $date = convertDateAndTime($value['created_at'], 'date');
            $time = convertDateAndTime($value['created_at'], 'time');
            $data = CRMSettingCallType::select('*');
            $data->where('crm_setting_call_type.id', $value['type_id']);
            $data->where('crm_setting_call_type.status', 1);
            $data = $data->first();

            $user_name = User::find($value['user_id']);
            if ($value['is_closed'] == 0) {
                $viewCallData .= '<div class="b-bottom p-2">';
                $viewCallData .= '<table class="col-12">';
                $viewCallData .= '<tr>';
                $viewCallData .= '<td class="mb-0" style="font-weight: 600;color: #27b50b;"><i class="bx bx-check-circle text-success me-2" style="font-size: 1.2rem;" onclick="viewCall(\'' . $value['id'] . '\')"></i></td>';
                $viewCallData .= '<td class="mb-0 col-1" style="font-weight: 600;color: #27b50b;">Call</td>';
                $viewCallData .= '<td class="mb-0 col-4 text-primary ps-4" style="font-weight: 600;color: #27b50b;">' . $value['purpose'] . '</td>';
                $url = route('crm.deal') . "?id=" . $value['lead_id'];
                $lead_prifix = 'L';
                if( $value['is_deal'] == 1){
                    $lead_prifix = 'D';
                }

                $viewCallData .= '<td class="text-warning ms-4 col-4" style="font-weight: 600;"><a target="_blank" href="'.$url.'"> '.$value['first_name'] . ' ' . $value['last_name'].'</br>#'.$lead_prifix.$value['lead_id'].'</a></td>';
                $viewCallData .= '<td class="col-3"><i class="bx bxs-user me-1"></i>' . $user_name['first_name'] . ' ' . $user_name['last_name'] .'</br>' . $date . '  ' . $time . '</td>';
                // $viewCallData .= '<td class="mb-0 ms-4 col-3">' . $date . '  ' . $time . '</td>';
                $viewCallData .= '</tr>';
                $viewCallData .= '</table>';
                $viewCallData .= '</div>';
            }
        }


        $columns = array(
            0 => 'lead_meetings.id',
            1 => 'lead_meetings.lead_id',
            2 => 'lead_meetings.title_id',
            3 => 'crm_setting_meeting_title.name as title',
            4 => 'lead_meetings.location',
            5 => 'lead_meetings.is_closed',
            6 => 'lead_meetings.description',
            7 => 'lead_meetings.created_at',
            8 => 'lead_meetings.user_id',
            9 => 'leads.first_name',
            10 => 'leads.last_name',
            11 => 'leads.is_deal',

        );

        
        $query = LeadMeeting::query();
        $query->leftJoin('crm_setting_meeting_title', 'crm_setting_meeting_title.id', '=', 'lead_meetings.title_id');
        $query->leftJoin('leads', 'leads.id', '=', 'lead_meetings.lead_id');
        $query->where('lead_meetings.is_closed', 0);
        if($request->employee_id != 0){
            $query->where('lead_meetings.user_id', $request->employee_id);
        }
        if ($isSalePerson == 1) {
            $query->whereIn('lead_meetings.user_id', $childSalePersonsIds);
        }
        $query->whereBetween('lead_meetings.meeting_date_time', [$current_start_date, $current_end_date]);
        $query->select($columns);
        $data = $query->get();
        $recordsMeetingTotal = $data->count();
        $data = json_decode(json_encode($data), true);

        $viewMeetingData = "";
        foreach ($data as $key => $value) {

            $date = convertDateAndTime($value['created_at'], 'date');
            $time = convertDateAndTime($value['created_at'], 'time');
            $user_name = User::find($value['user_id']);
            if ($value['is_closed'] == 0) {
                $viewMeetingData .= '<div class="b-bottom p-2">';
                $viewMeetingData .= '<table class="col-12">';
                $viewMeetingData .= '<tr>';
                $viewMeetingData .= '<td class="mb-0" style="font-weight: 600;color: #27b50b;"><i class="bx bx-check-circle text-success me-2" style="font-size: 1.2rem;" onclick="viewMeeting(\'' . $value['id'] . '\')"></i></td>';
                $viewMeetingData .= '<td class="mb-0 col-1" style="font-weight: 600;color: #27b50b;">Meeting</td>';
                $viewMeetingData .= '<td class="mb-0 col-4 text-primary ps-4" style="font-weight: 600;">' . $value['title'] . '</td>';
                $url = route('crm.deal') . "?id=" . $value['lead_id'];
                $lead_prifix = 'L';
                if( $value['is_deal'] == 1){
                    $lead_prifix = 'D';
                }

                $viewMeetingData .= '<td class="text-warning ms-4 col-4" style="font-weight: 600;"><a target="_blank" href="'.$url.'"> '.$value['first_name'] . ' ' . $value['last_name'].'</br>#'.$lead_prifix.$value['lead_id'].'</a></td>';
                $viewMeetingData .= '<td class="col-3"><i class="bx bxs-user me-1"></i>' . $user_name['first_name'] . ' ' . $user_name['last_name'] .'</br>' . $date . '  ' . $time . '</td>';
                // $viewMeetingData .= '<td class="mb-0 ms-4 col-3">' . $date . '  ' . $time . '</td>';
                $viewMeetingData .= '</tr>';
                $viewMeetingData .= '</table>';
                $viewMeetingData .= '</div>';
            }
        }

        $columns = array(
            0 => 'lead_tasks.id',
            1 => 'lead_tasks.lead_id',
            2 => 'lead_tasks.is_closed',
            3 => 'lead_tasks.created_at',
            4 => 'lead_tasks.task',
            5 => 'lead_tasks.description',
            6 => 'leads.first_name',
            7 => 'leads.last_name',
            8 => 'lead_tasks.user_id',
            9 => 'leads.is_deal',
            10 => 'lead_tasks.is_autogenerate',
            11 => 'lead_tasks.assign_to',
        );


        $query = LeadTask::query();
        $query->leftJoin('leads', 'leads.id', '=', 'lead_tasks.lead_id');
        $query->where('lead_tasks.is_closed', 0);
        if($request->employee_id != 0){
            $query->where('lead_tasks.user_id', $request->employee_id);
        }
        if ($isSalePerson == 1) {
            $query->whereIn('lead_tasks.user_id', $childSalePersonsIds);
        }
        $query->whereBetween('lead_tasks.due_date_time', [$current_start_date, $current_end_date]);
        $query->select($columns);

        $data = $query->get();
        $recordsTaskTotal = $data->count();

        $data = json_decode(json_encode($data), true);
        $viewTaskData = "";
        foreach ($data as $key => $value) {
            $user_name = User::find($value['user_id']);
            $date = convertDateAndTime($value['created_at'], 'date');
            $time = convertDateAndTime($value['created_at'], 'time');
            if ($value['is_closed'] == 0) {
                $viewTaskData .= '<div class="b-bottom p-2">';
                $viewTaskData .= '<table class="col-12">';
                $viewTaskData .= '<tr>';
                if($value['is_autogenerate'] == 1){
                    if($value['assign_to'] == Auth::user()->id){
                        $viewTaskData .= '<td class="mb-0" style="font-weight: 600;color: #27b50b;"><i class="bx bx-check-circle text-success me-2" style="font-size: 1.2rem;" onclick="viewTask(\'' . $value['id'] . '\')"></i></td>';
                    } else {
                        $user_type = User::find($value['assign_to'])->type;
                        $tooltip_message = "";
                        if($user_type == 1){
                            $tooltip_message = "CRE User & Company Admin Edit Only";
                        } else if($user_type == 9){
                            $tooltip_message = "TeleSales User Edit Only";
                        } else if($user_type == 11){
                            $tooltip_message = "Service User Edit Only";
                        }
                        $viewTaskData .= '<td class="mb-0" style="font-weight: 600;color: #27b50b;"><div class="hover_tooltip"><i class="bx bx-check-circle text-danger me-2" style="font-size: 1.2rem;"></i><span class="tooltiptext">'.$tooltip_message.'</span></div></td>';
                    }
                } else {
                    $viewTaskData .= '<td class="mb-0" style="font-weight: 600;color: #27b50b;"><i class="bx bx-check-circle text-success me-2" style="font-size: 1.2rem;" onclick="viewTask(\'' . $value['id'] . '\')"></i></td>';
                }
                // $viewTaskData .= '<td class="mb-0" style="font-weight: 600;color: #27b50b;"><i class="bx bx-check-circle text-success me-2" style="font-size: 1.2rem;" onclick="viewTask(\'' . $value['id'] . '\')"></i></td>';
                $viewTaskData .= '<td class="mb-0 col-1" style="font-weight: 600;color: #27b50b;">Task</td>';
                $viewTaskData .= '<td class="mb-0 col-4 text-primary ps-4" style="font-weight: 600;">' . $value['task'] . '</td>';
                $url = route('crm.deal') . "?id=" . $value['lead_id'];
                $lead_prifix = 'L';
                if( $value['is_deal'] == 1){
                    $lead_prifix = 'D';
                }

                $viewTaskData .= '<td class="text-warning ms-4 col-4" style="font-weight: 600;"><a target="_blank" href="'.$url.'"> '.$value['first_name'] . ' ' . $value['last_name'].'</br>#'.$lead_prifix.$value['lead_id'].'</a></td>';
                $viewTaskData .= '<td class="col-3"><i class="bx bxs-user me-1"></i>' . $user_name['first_name'] . ' ' . $user_name['last_name'] .'</br>' . $date . '  ' . $time . '</td>';
                // $viewTaskData .= '<td class="mb-0 ms-4 col-3">' . $date . '  ' . $time . '</td>';
                $viewTaskData .= '</tr>';
                $viewTaskData .= '</table>';
                $viewTaskData .= '</div>';
            }
        }

        $jsonData = array(
            "draw" => intval($request['draw']),
            "recordsCallTotal" => intval($recordsCallTotal),
            "recordsMeetingTotal" => intval($recordsMeetingTotal),
            "recordsTaskTotal" => intval($recordsTaskTotal),
            "call_data" => $viewCallData,
            "meeeting_data" => $viewMeetingData,
            "task_data" => $viewTaskData,
            "allrecordsTotal" => intval($recordsCallTotal) + intval($recordsMeetingTotal) + intval($recordsTaskTotal), // total number of records

        );
        return $jsonData;
    }

    function PreviousTeamActionAjax(Request $request)
    {

        $isSalePerson = isSalePerson();
		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
        if ($isSalePerson == 1) {
			$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
		}

        $current_start_date = $request->current_date . " 00:00:00";
        $current_start_date = date('Y-m-d H:i:s', strtotime($current_start_date . " -5 hours"));
        $current_start_date = date('Y-m-d H:i:s', strtotime($current_start_date . " -30 minutes"));

        $columns = array(
            0 => 'lead_calls.lead_id',
            1 => 'lead_calls.is_closed',
            2 => 'lead_calls.type_id',
            3 => 'lead_calls.purpose',
            4 => 'lead_calls.created_at',
            5 => 'lead_calls.id',
            6 => 'leads.first_name',
            7 => 'leads.last_name',
            8 => 'lead_calls.user_id',
            9 => 'leads.is_deal',
        );


        $query = LeadCall::query();
        $query->leftJoin('leads', 'leads.id', '=', 'lead_calls.lead_id');
        $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
        if ($isSalePerson == 1) {
            $query->whereIn('lead_calls.user_id', $childSalePersonsIds);
        }
        $query->where('lead_calls.call_schedule', '<', $current_start_date);
        $query->where('lead_calls.is_closed', 0);
        if($request->employee_id != 0){
            $query->where('lead_calls.user_id', $request->employee_id);
        }
        $query->select($columns);

        $data = $query->get();
        $recordsCallTotal = $data->count();
        $data = json_decode(json_encode($data), true);

        $viewCallData = "";
        foreach ($data as $key => $value) {
            $date = convertDateAndTime($value['created_at'], 'date');
            $time = convertDateAndTime($value['created_at'], 'time');
            $data = CRMSettingCallType::select('*');
            $data->where('crm_setting_call_type.id', $value['type_id']);
            $data->where('crm_setting_call_type.status', 1);
            $data = $data->first();
            $user_name = User::find($value['user_id']);
            if ($value['is_closed'] == 0) {
                $viewCallData .= '<div class="b-bottom p-2">';
                $viewCallData .= '<table class="col-12">';
                $viewCallData .= '<tr>';
                $viewCallData .= '<td class="mb-0" style="font-weight: 600;color: #27b50b;"><i class="bx bx-check-circle text-success me-2" style="font-size: 1.2rem;" onclick="viewCall(\'' . $value['id'] . '\')"></i></td>';
                $viewCallData .= '<td class="mb-0 col-1" style="font-weight: 600;color: #27b50b;">Call</td>';
                $viewCallData .= '<td class="mb-0 col-4 text-primary ps-4" style="font-weight: 600;">' . $value['purpose'] . '</td>';
                $url = route('crm.deal') . "?id=" . $value['lead_id'];
                $lead_prifix = 'L';
                if( $value['is_deal'] == 1){
                    $lead_prifix = 'D';
                }

                $viewCallData .= '<td class="text-warning ms-4 col-4" style="font-weight: 600;"><a target="_blank" href="'.$url.'"> '.$value['first_name'] . ' ' . $value['last_name'].'</br>#'.$lead_prifix.$value['lead_id'].'</a></td>';
                $viewCallData .= '<td class="col-3"><i class="bx bxs-user me-1"></i>' . $user_name['first_name'] . ' ' . $user_name['last_name'] .'</br>' . $date . '  ' . $time . '</td>';
                // $viewCallData .= '<td class="mb-0 ms-4 col-3">' . $date . '  ' . $time . '</td>';
                $viewCallData .= '</tr>';
                $viewCallData .= '</table>';
                $viewCallData .= '</div>';
            }
        }

        $columns = array(
            0 => 'lead_meetings.id',
            1 => 'lead_meetings.lead_id',
            2 => 'lead_meetings.title_id',
            3 => 'crm_setting_meeting_title.name as title',
            4 => 'lead_meetings.location',
            5 => 'lead_meetings.created_at',
            6 => 'lead_meetings.description',
            7 => 'lead_meetings.is_closed',
            8 => 'leads.first_name',
            9 => 'leads.last_name',
            10 => 'lead_meetings.user_id',
            11 => 'leads.is_deal',
        );


        $query = LeadMeeting::query();
        $query->leftJoin('crm_setting_meeting_title', 'crm_setting_meeting_title.id', '=', 'lead_meetings.title_id');
        $query->leftJoin('leads', 'leads.id', '=', 'lead_meetings.lead_id');
        if ($isSalePerson == 1) {
            $query->whereIn('lead_meetings.user_id', $childSalePersonsIds);
        }
        $query->where('lead_meetings.meeting_date_time', '<', $current_start_date);
        $query->where('lead_meetings.is_closed', 0);
        if($request->employee_id != 0){
            $query->where('lead_meetings.user_id', $request->employee_id);
        }
        $query->select($columns);

        $data = $query->get();
        $recordsMeetingTotal = $data->count();

        $data = json_decode(json_encode($data), true);

        $viewMeetingData = "";
        foreach ($data as $key => $value) {
            $date = convertDateAndTime($value['created_at'], 'date');
            $time = convertDateAndTime($value['created_at'], 'time');

            $user_name = User::find($value['user_id']);
            if ($value['is_closed'] == 0) {
                $viewMeetingData .= '<div class="b-bottom p-2">';
                $viewMeetingData .= '<table class="col-12">';
                $viewMeetingData .= '<tr>';
                $viewMeetingData .= '<td class="mb-0" style="font-weight: 600;color: #27b50b;"><i class="bx bx-check-circle text-success me-2" style="font-size: 1.2rem;" onclick="viewMeeting(\'' . $value['id'] . '\')"></i></td>';
                $viewMeetingData .= '<td class="mb-0 col-1" style="font-weight: 600;color: #27b50b;">Meeting</td>';
                $viewMeetingData .= '<td class="mb-0 col-4 text-primary ps-4" style="font-weight: 600;">' . $value['title'] . '</td>';
                $url = route('crm.deal') . "?id=" . $value['lead_id'];
                $lead_prifix = 'L';
                if( $value['is_deal'] == 1){
                    $lead_prifix = 'D';
                }

                $viewMeetingData .= '<td class="text-warning ms-4 col-4" style="font-weight: 600;"><a target="_blank" href="'.$url.'"> '.$value['first_name'] . ' ' . $value['last_name'].'</br>#'.$lead_prifix.$value['lead_id'].'</a></td>';
                $viewMeetingData .= '<td class="col-3"><i class="bx bxs-user me-1"></i>' . $user_name['first_name'] . ' ' . $user_name['last_name'] .'</br>' . $date . '  ' . $time . '</td>';
                // $viewMeetingData .= '<td class="mb-0 ms-4 col-3">' . $date . '  ' . $time . '</td>';
                $viewMeetingData .= '</tr>';
                $viewMeetingData .= '</table>';
                $viewMeetingData .= '</div>';
            }
        }

        $columns = array(
            0 => 'lead_tasks.id',
            1 => 'lead_tasks.lead_id',
            2 => 'lead_tasks.task',
            3 => 'lead_tasks.created_at',
            4 => 'lead_tasks.is_closed',
            5 => 'lead_tasks.description',
            6 => 'leads.first_name',
            7 => 'leads.last_name',
            8 => 'lead_tasks.user_id',
            9 => 'leads.is_deal',
            10 => 'lead_tasks.is_autogenerate',
            11 => 'lead_tasks.assign_to',
        );


        $query = LeadTask::query();
        $query->leftJoin('leads', 'leads.id', '=', 'lead_tasks.lead_id');
        if ($isSalePerson == 1) {
            $query->whereIn('lead_tasks.user_id', $childSalePersonsIds);
        }
        $query->where('lead_tasks.is_closed', 0);
        if($request->employee_id != 0){
            $query->where('lead_tasks.user_id', $request->employee_id);
        }
        $query->where('lead_tasks.due_date_time', '<', $current_start_date);
        $query->select($columns);

        $data = $query->get();
        $recordsTaskTotal = $data->count();
        $data = json_decode(json_encode($data), true);

        $viewTaskData = "";
        foreach ($data as $key => $value) {
            $date = convertDateAndTime($value['created_at'], 'date');
            $time = convertDateAndTime($value['created_at'], 'time');

            $user_name = User::find($value['user_id']);
            if ($value['is_closed'] == 0) {
                $viewTaskData .= '<div class="b-bottom p-2">';
                $viewTaskData .= '<table class="col-12">';
                $viewTaskData .= '<tr>';
                if($value['is_autogenerate'] == 1){
                    if($value['assign_to'] == Auth::user()->id){
                        $viewTaskData .= '<td class="mb-0" style="font-weight: 600;color: #27b50b;"><i class="bx bx-check-circle text-success me-2" style="font-size: 1.2rem;" onclick="viewTask(\'' . $value['id'] . '\')"></i></td>';
                    } else {
                        $user_type = User::find($value['assign_to'])->type;
                        $tooltip_message = "";
                        if($user_type == 1){
                            $tooltip_message = "CRE User & Company Admin Edit Only";
                        } else if($user_type == 9){
                            $tooltip_message = "TeleSales User Edit Only";
                        } else if($user_type == 11){
                            $tooltip_message = "Service User Edit Only";
                        }
                        $viewTaskData .= '<td class="mb-0" style="font-weight: 600;color: #27b50b;"><div class="hover_tooltip"><i class="bx bx-check-circle text-danger me-2" style="font-size: 1.2rem;"></i><span class="tooltiptext">'.$tooltip_message.'</span></div></td>';
                    }
                } else {
                    $viewTaskData .= '<td class="mb-0" style="font-weight: 600;color: #27b50b;"><i class="bx bx-check-circle text-success me-2" style="font-size: 1.2rem;" onclick="viewTask(\'' . $value['id'] . '\')"></i></td>';
                }
                // $viewTaskData .= '<td class="mb-0" style="font-weight: 600;color: #27b50b;"><i class="bx bx-check-circle text-success me-2" style="font-size: 1.2rem;" onclick="viewTask(\'' . $value['id'] . '\')"></i></td>';
                $viewTaskData .= '<td class="mb-0 col-1" style="font-weight: 600; color: #27b50b;">Task</td>';
                $viewTaskData .= '<td class="mb-0 col-4 text-primary ps-4" style="font-weight: 600;">' . $value['task'] . '</td>';
                $url = route('crm.deal') . "?id=" . $value['lead_id'];
                $lead_prifix = 'L';
                if( $value['is_deal'] == 1){
                    $lead_prifix = 'D';
                }

                $viewTaskData .= '<td class="text-warning ms-4 col-4" style="font-weight: 600;"><a target="_blank" href="'.$url.'"> '.$value['first_name'] . ' ' . $value['last_name'].'</br>#'.$lead_prifix.$value['lead_id'].'</a></td>';
                $viewTaskData .= '<td class="col-3"><i class="bx bxs-user me-1"></i>' . $user_name['first_name'] . ' ' . $user_name['last_name'] .'</br>' . $date . '  ' . $time . '</td>';
                // $viewTaskData .= '<td class="mb-0 ms-4 col-3">' . $date . '  ' . $time . '</td>';
                $viewTaskData .= '</tr>';
                $viewTaskData .= '</table>';
                $viewTaskData .= '</div>';
            }
        }

        $jsonData = array(
            "draw" => intval($request['draw']),
            // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsCallTotal" => intval($recordsCallTotal),
            // total number of records
            "recordsMeetingTotal" => intval($recordsMeetingTotal),
            // total number of records
            "recordsTaskTotal" => intval($recordsTaskTotal),
            // total number of records
            "call_data" => $viewCallData,
            "meeeting_data" => $viewMeetingData,
            "task_data" => $viewTaskData,
            // total data array
            "allrecordsTotal" => intval($recordsCallTotal) + intval($recordsMeetingTotal) + intval($recordsTaskTotal), // total number of records

        );
        return $jsonData;
    }
    function CloseTeamActionAjax(Request $request)
    {
        $isSalePerson = isSalePerson();
		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
        if ($isSalePerson == 1) {
			$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
		}

        $current_start_date = $request->current_date . " 00:00:00";
        $current_start_date = date('Y-m-d H:i:s', strtotime($current_start_date . " -5 hours"));
        $current_start_date = date('Y-m-d H:i:s', strtotime($current_start_date . " -30 minutes"));

        $current_end_date = $request->current_date . " 23:59:59";
        $current_end_date = date('Y-m-d H:i:s', strtotime($current_end_date . " -5 hours"));
        $current_end_date = date('Y-m-d H:i:s', strtotime($current_end_date . " -30 minutes"));

        $columns = array(
            0 => 'lead_calls.id',
            1 => 'lead_calls.lead_id',
            2 => 'lead_calls.is_closed',
            3 => 'lead_calls.type_id',
            4 => 'lead_calls.purpose',
            5 => 'lead_calls.created_at',
            6 => 'leads.first_name',
            7 => 'leads.last_name',
            8 => 'lead_calls.user_id',
            9 => 'leads.is_deal',
        );


        $query = LeadCall::query();
        $query->leftJoin('leads', 'leads.id', '=', 'lead_calls.lead_id');
        if ($isSalePerson == 1) {
            $query->whereIn('lead_calls.user_id', $childSalePersonsIds);
        }
        $query->where('lead_calls.is_closed', 1);
        if($request->employee_id != 0){
            $query->where('lead_calls.user_id', $request->employee_id);
        }
        // $query->where('lead_calls.user_id', Auth::user()->id);

        if(isset($request->from_date) && $request->from_date != 0 && isset($request->to_date) && $request->to_date != 0){
            $from_date = $request->from_date . " 23:59:59";
            $to_date = $request->to_date . " 23:59:59";

            $query->whereBetween('lead_calls.updated_at', [$from_date, $to_date]);
        }
        else{
            $query->whereBetween('lead_calls.updated_at', [$current_start_date, $current_end_date]);
        }

        $query->select($columns);
        $data = $query->get();

        $viewCallData = "";
        foreach ($data as $key => $value) {
            $date = convertDateAndTime($value['created_at'], 'date');
            $time = convertDateAndTime($value['created_at'], 'time');
            $user_name = User::find($value['user_id']);
            if ($value['is_closed'] == 1) {
                $viewCallData .= '<div class="b-bottom p-2">';
                $viewCallData .= '<table class="col-12">';
                $viewCallData .= '<tr>';
                $viewCallData .= '<td class="mb-0 col-1" style="font-weight: 600;color: #27b50b;">Call</td>';
                $viewCallData .= '<td class="mb-0 col-4 text-primary ps-4" style="font-weight: 600;color: #27b50b;">' . $value['purpose'] . '</td>';
                $url = route('crm.deal') . "?id=" . $value['lead_id'];
                $lead_prifix = 'L';
                if( $value['is_deal'] == 1){
                    $lead_prifix = 'D';
                }

                $viewCallData .= '<td class="text-warning ms-4 col-4" style="font-weight: 600;"><a target="_blank" href="'.$url.'"> '.$value['first_name'] . ' ' . $value['last_name'].'</br>#'.$lead_prifix.$value['lead_id'].'</a></td>';
                $viewCallData .= '<td class="col-3"><i class="bx bxs-user me-1"></i>' . $user_name['first_name'] . ' ' . $user_name['last_name'] .'</br>' . $date . '  ' . $time . '</td>';
                // $viewCallData .= '<td class="mb-0 ms-4 col-3">' . $date . '  ' . $time . '</td>';
                $viewCallData .= '</tr>';
                $viewCallData .= '</table>';
                $viewCallData .= '</div>';
            }
        }

        $columns = array(
            0 => 'lead_meetings.id',
            1 => 'lead_meetings.lead_id',
            2 => 'lead_meetings.title_id',
            3 => 'crm_setting_meeting_title.name as title',
            4 => 'lead_meetings.location',
            5 => 'lead_meetings.is_closed',
            6 => 'lead_meetings.description',
            7 => 'lead_meetings.created_at',
            8 => 'leads.first_name',
            9 => 'leads.last_name',
            10 => 'lead_meetings.user_id',
            11 => 'leads.is_deal',
        );


        $query = LeadMeeting::query();
        $query->leftJoin('crm_setting_meeting_title', 'crm_setting_meeting_title.id', '=', 'lead_meetings.title_id');
        $query->leftJoin('leads', 'leads.id', '=', 'lead_meetings.lead_id');
        if ($isSalePerson == 1) {
            $query->whereIn('lead_meetings.user_id', $childSalePersonsIds);
        }
        $query->where('lead_meetings.is_closed', 1);
        if($request->employee_id != 0){
            $query->where('lead_meetings.user_id', $request->employee_id);
        }
        // $query->where('lead_meetings.user_id', Auth::user()->id);
        if(isset($request->from_date) && $request->from_date != 0 && isset($request->to_date) && $request->to_date != 0){
            $from_date = $request->from_date . " 23:59:59";
            $to_date = $request->to_date . " 23:59:59";
            $query->whereBetween('lead_meetings.updated_at', [$from_date, $to_date]);
        }
        else{
            $query->whereBetween('lead_meetings.updated_at', [$current_start_date, $current_end_date]);
        }
        $query->select($columns);


        $data = $query->get();
        $data = json_decode(json_encode($data), true);

        $viewMeetingData = "";
        foreach ($data as $key => $value) {

            $date = convertDateAndTime($value['created_at'], 'date');
            $time = convertDateAndTime($value['created_at'], 'time');

            $user_name = User::find($value['user_id']);
            if ($value['is_closed'] == 1) {
                $viewMeetingData .= '<div class="b-bottom p-2">';
                $viewMeetingData .= '<table class="col-12">';
                $viewMeetingData .= '<tr>';
                $viewMeetingData .= '<td class="mb-0 col-1" style="font-weight: 600;color: #27b50b;">Meeting</td>';
                $viewMeetingData .= '<td class="mb-0 col-4 text-primary ps-4" style="font-weight: 600;">' . $value['title'] . '</td>';
                $url = route('crm.deal') . "?id=" . $value['lead_id'];
                $lead_prifix = 'L';
                if( $value['is_deal'] == 1){
                    $lead_prifix = 'D';
                }

                $viewMeetingData .= '<td class="text-warning ms-4 col-4" style="font-weight: 600;"><a target="_blank" href="'.$url.'"> '.$value['first_name'] . ' ' . $value['last_name'].'</br>#'.$lead_prifix.$value['lead_id'].'</a></td>';
                $viewMeetingData .= '<td class="col-3"><i class="bx bxs-user me-1"></i>' . $user_name['first_name'] . ' ' . $user_name['last_name'] .'</br>' . $date . '  ' . $time . '</td>';
                // $viewMeetingData .= '<td class="mb-0 ms-4 col-3">' . $date . '  ' . $time . '</td>';
                $viewMeetingData .= '</tr>';
                $viewMeetingData .= '</table>';
                $viewMeetingData .= '</div>';
            }
        }


        $columns = array(
            0 => 'lead_tasks.id',
            1 => 'lead_tasks.lead_id',
            2 => 'lead_tasks.is_closed',
            3 => 'lead_tasks.created_at',
            4 => 'lead_tasks.task',
            5 => 'lead_tasks.description',
            6 => 'leads.first_name',
            7 => 'leads.last_name',
            8 => 'lead_tasks.user_id',
            9 => 'leads.is_deal',
        );


        $query = LeadTask::query();
        $query->leftJoin('leads', 'leads.id', '=', 'lead_tasks.lead_id');
        if ($isSalePerson == 1) {
            $query->whereIn('lead_tasks.user_id', $childSalePersonsIds);
        }
        $query->where('lead_tasks.is_closed', 1);
        if($request->employee_id != 0){
            $query->where('lead_tasks.user_id', $request->employee_id);
        }
        // $query->where('lead_tasks.user_id', Auth::user()->id);
        if(isset($request->from_date) && $request->from_date != 0 && isset($request->to_date) && $request->to_date != 0){
            $from_date = $request->from_date . " 23:59:59";
            $to_date = $request->to_date . " 23:59:59";
            $query->whereBetween('lead_tasks.updated_at', [$from_date, $to_date]);
        }
        else{
            $query->whereBetween('lead_tasks.updated_at', [$current_start_date, $current_end_date]);
        }
        $query->select($columns);

        $data = $query->get();
        $data = json_decode(json_encode($data), true);
        $viewTaskData = "";
        foreach ($data as $key => $value) {
            $date = convertDateAndTime($value['created_at'], 'date');
            $time = convertDateAndTime($value['created_at'], 'time');
            $user_name = User::find($value['user_id']);
            if ($value['is_closed'] == 1) {
                $viewTaskData .= '<div class="b-bottom p-2">';
                $viewTaskData .= '<table class="col-12">';
                $viewTaskData .= '<tr>';
                $viewTaskData .= '<td class="mb-0 col-1" style="font-weight: 600;color: #27b50b;">Task</td>';
                $viewTaskData .= '<td class="mb-0 col-4 text-primary ps-4" style="font-weight: 600;">' . $value['task'] . '</td>';
                $url = route('crm.deal') . "?id=" . $value['lead_id'];
                $lead_prifix = 'L';
                if( $value['is_deal'] == 1){
                    $lead_prifix = 'D';
                }

                $viewTaskData .= '<td class="text-warning ms-4 col-4" style="font-weight: 600;"><a target="_blank" href="'.$url.'"> '.$value['first_name'] . ' ' . $value['last_name'].'</br>#'.$lead_prifix.$value['lead_id'].'</a></td>';
                $viewTaskData .= '<td class="col-3"><i class="bx bxs-user me-1"></i>' . $user_name['first_name'] . ' ' . $user_name['last_name'] .'</br>' . $date . '  ' . $time . '</td>';
                // $viewTaskData .= '<td class="mb-0 ms-4 col-3">' . $date . '  ' . $time . '</td>';
                $viewTaskData .= '</tr>';
                $viewTaskData .= '</table>';
                $viewTaskData .= '</div>';
            }
        }

        $jsonData = array(
            "draw" => intval($request['draw']),
            "call_data" => $viewCallData,
            "meeeting_data" => $viewMeetingData,
            "task_data" => $viewTaskData, // total data array
        );
        return $jsonData;
    }

    function SearchTeamEmployee(Request $request){
        $searchKeyword = isset($request->q) ? $request->q : "";

        $data = User::select('id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"));
        $data->where(function ($query) use ($searchKeyword) {
            $query->where('users.first_name', 'like', '%' . $searchKeyword . '%');
            $query->orWhere('users.last_name', 'like', '%' . $searchKeyword . '%');
        });
        $data->where('users.type', '2'); 
        $data->limit(5);
        $data = $data->get();
        $response = array();
        $response['results'] = $data;
        $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    // function searchCloseActionAjax(Request $request)
    // {
    //     $isSalePerson = isSalePerson();
	// 	$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
    //     if ($isSalePerson == 1) {
	// 		$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
	// 	}

    //     $from_date = $request->from_date . " 23:59:59";
    //     // $from_date = date('Y-m-d H:i:s', strtotime($from_date . " -5 hours"));
    //     // $from_date = date('Y-m-d H:i:s', strtotime($from_date . " -30 minutes"));

    //     $to_date = $request->to_date . " 23:59:59";

    //     $columns = array(
    //         0 => 'lead_calls.id',
    //         1 => 'lead_calls.lead_id',
    //         2 => 'lead_calls.is_closed',
    //         3 => 'lead_calls.type_id',
    //         4 => 'lead_calls.purpose',
    //         5 => 'lead_calls.created_at',
    //         6 => 'leads.first_name',
    //         7 => 'leads.last_name',
    //         8 => 'lead_calls.user_id',
    //     );

    //     $query = LeadCall::query();
    //     $query->leftJoin('leads', 'leads.id', '=', 'lead_calls.lead_id');
    //     $query->where('lead_calls.is_closed', 1);
    //     if ($isSalePerson == 1) {
    //         $query->whereIn('leads.assigned_to', $childSalePersonsIds);
    //     }
    //     $query->whereBetween('lead_calls.updated_at', [$from_date, $to_date]);
    //     $query->select($columns);
    //     $data = $query->get();

    //     $viewCallData = "";
    //     foreach ($data as $key => $value) {
    //         $date = convertDateAndTime($value['created_at'], 'date');
    //         $time = convertDateAndTime($value['created_at'], 'time');

    //         $user_name = User::find($value['user_id']);
    //         if ($value['is_closed'] == 1) {
    //             $viewCallData .= '<div class="b-bottom p-2">';
    //             $viewCallData .= '<table class="col-12">';
    //             $viewCallData .= '<tr>';
    //             $viewCallData .= '<td class="mb-0 col-1" style="font-weight: 600;color: #27b50b;">Call</td>';
    //             $viewCallData .= '<td class="mb-0 col-3 text-primary ps-4" style="font-weight: 600;color: #27b50b;">' . $value['purpose'] . '</td>';
    //             $viewCallData .= '<td class="text-warning ms-4 col-2" style="font-weight: 600;">' . $value['first_name'] . ' ' . $value['last_name'] . '</td>';
    //             $viewCallData .= '<td class="col-3"><i class="bx bxs-user ms-4 me-1"></i>' . $user_name['first_name'] . ' ' . $user_name['last_name'] . '</td>';
    //             $viewCallData .= '<td class="mb-0 ms-4 col-3">' . $date . '  ' . $time . '</td>';
    //             $viewCallData .= '</tr>';
    //             $viewCallData .= '</table>';
    //             $viewCallData .= '</div>';
    //         }
    //     }

    //     $columns = array(
    //         0 => 'lead_meetings.id',
    //         1 => 'lead_meetings.lead_id',
    //         2 => 'lead_meetings.title_id',
    //         3 => 'crm_setting_meeting_title.name as title',
    //         4 => 'lead_meetings.location',
    //         5 => 'lead_meetings.is_closed',
    //         6 => 'lead_meetings.description',
    //         7 => 'lead_meetings.created_at',
    //         8 => 'leads.first_name',
    //         9 => 'leads.last_name',
    //         10 => 'lead_meetings.user_id',
    //     );

    //     $query = LeadMeeting::query();
    //     $query->leftJoin('crm_setting_meeting_title', 'crm_setting_meeting_title.id', '=', 'lead_meetings.title_id');
    //     $query->leftJoin('leads', 'leads.id', '=', 'lead_meetings.lead_id');
    //     $query->where('lead_meetings.is_closed', 1);
    //     if ($isSalePerson == 1) {
    //         $query->whereIn('leads.assigned_to', $childSalePersonsIds);
    //     }
    //     $query->whereBetween('lead_meetings.updated_at', [$from_date, $to_date]);
    //     $query->select($columns);


    //     $data = $query->get();
    //     $data = json_decode(json_encode($data), true);

    //     $viewMeetingData = "";
    //     foreach ($data as $key => $value) {

    //         $date = convertDateAndTime($value['created_at'], 'date');
    //         $time = convertDateAndTime($value['created_at'], 'time');

    //         $user_name = User::find($value['user_id']);
    //         if ($value['is_closed'] == 1) {
    //             $viewMeetingData .= '<div class="b-bottom p-2">';
    //             $viewMeetingData .= '<table class="col-12">';
    //             $viewMeetingData .= '<tr>';
    //             $viewMeetingData .= '<td class="mb-0 col-1" style="font-weight: 600;color: #27b50b;">Meeting</td>';
    //             $viewMeetingData .= '<td class="mb-0 col-3 text-primary ps-4" style="font-weight: 600;">' . $value['title'] . '</td>';
    //             $viewMeetingData .= '<td class="text-warning ms-4 col-2" style="font-weight: 600;">' . $value['first_name'] . ' ' . $value['last_name'] . '</td>';
    //             $viewMeetingData .= '<td class="col-3"><i class="bx bxs-user ms-4 me-1"></i>' . $user_name['first_name'] . ' ' . $user_name['last_name'] . '</td>';
    //             $viewMeetingData .= '<td class="mb-0 ms-4 col-3">' . $date . '  ' . $time . '</td>';
    //             $viewMeetingData .= '</tr>';
    //             $viewMeetingData .= '</table>';
    //             $viewMeetingData .= '</div>';
    //         }
    //     }

    //     $columns = array(
    //         0 => 'lead_tasks.id',
    //         1 => 'lead_tasks.lead_id',
    //         2 => 'lead_tasks.is_closed',
    //         3 => 'lead_tasks.created_at',
    //         4 => 'lead_tasks.task',
    //         5 => 'lead_tasks.description',
    //         6 => 'leads.first_name',
    //         7 => 'leads.last_name',
    //         8 => 'lead_tasks.user_id',
    //     );

    //     $query = LeadTask::query();
    //     $query->leftJoin('leads', 'leads.id', '=', 'lead_tasks.lead_id');
    //     $query->where('lead_tasks.is_closed', 1);
    //     if ($isSalePerson == 1) {
    //         $query->whereIn('leads.assigned_to', $childSalePersonsIds);
    //     }
    //     $query->whereBetween('lead_tasks.updated_at', [$from_date, $to_date]);
    //     $query->select($columns);

    //     $data = $query->get();
    //     $data = json_decode(json_encode($data), true);
    //     $viewTaskData = "";
    //     foreach ($data as $key => $value) {
    //         $date = convertDateAndTime($value['created_at'], 'date');
    //         $time = convertDateAndTime($value['created_at'], 'time');
    //         $user_name = User::find($value['user_id']);
    //         if ($value['is_closed'] == 1) {
    //             $viewTaskData .= '<div class="b-bottom p-2">';
    //             $viewTaskData .= '<table class="col-12">';
    //             $viewTaskData .= '<tr>';
    //             $viewTaskData .= '<td class="mb-0 col-1" style="font-weight: 600;color: #27b50b;">Task</td>';
    //             $viewTaskData .= '<td class="mb-0 col-3 text-primary ps-4" style="font-weight: 600;">' . $value['task'] . '</td>';
    //             $viewTaskData .= '<td class="ms-4 col-2 text-warning" style="font-weight: 600;">' . $value['first_name'] . ' ' . $value['last_name'] . '</td>';
    //             $viewTaskData .= '<td class="col-3"><i class="bx bxs-user ms-4 me-1"></i>' . $user_name['first_name'] . ' ' . $user_name['last_name'] . '</td>';
    //             $viewTaskData .= '<td class="mb-0 ms-4 col-3">' . $date . '  ' . $time . '</td>';
    //             $viewTaskData .= '</tr>';
    //             $viewTaskData .= '</table>';
    //             $viewTaskData .= '</div>';
    //         }
    //     }


    //     $jsonData = array(
    //         "draw" => intval($request['draw']),
    //         "call_data" => $viewCallData,
    //         "meeeting_data" => $viewMeetingData,
    //         "task_data" => $viewTaskData,
    //     );
    //     return $jsonData;
    // }
}