<?php

namespace App\Http\Controllers\CRM\Lead;

use App\Http\Controllers\Controller;
use App\Models\LeadMeeting;
use App\Models\LeadTask;
use App\Models\Lead;
use App\Models\LeadCall;
use App\Models\CRMSettingCallType;
use App\Models\CRMSettingMeetingTitle;
use App\Models\LeadContact;
use App\Models\User;
use App\Models\LeadMeetingParticipant;
use App\Models\QuotRequest;
use App\Models\ChannelPartner;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;

class LeadActionController extends Controller
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
        $data['title'] = "Action ";
        return view('crm/lead/action', compact('data'));
    }
    
    function TodayMyActionAjax(Request $request)
    {
        $Contact_ids = LeadContact::select('id')->where(DB::raw("SUBSTRING_INDEX(lead_contacts.type_detail, '-', -1)"), '=', Auth::user()->id)->get();

        $status_arr = array(104, 105);

        $isSalePerson = isSalePerson();
		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
        if ($isSalePerson == 1) {
			$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
		}

        $current_start_date = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . " -1 days"));
        
        $current_end_date = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s')));

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
            10 => 'lead_calls.reference_id',
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
                if($value['reference_id'] != 0){
                    $LeadRef = LeadTask::find($value['reference_id']);
                    if($LeadRef->is_autogenerate == 1){
                        if($LeadRef->assign_to == Auth::user()->id){
                            $type = "call";
                            $viewCallData .= '<td class="mb-0" style="font-weight: 600;color: #27b50b;"><i class="bx bx-check-circle text-success me-2" style="font-size: 1.2rem;" onclick="viewAutoScheduleAction(\'' . $value['id'] . '\', \'' . $type . '\')"></i></td>';
                        } else {
                            $user_type = User::find($LeadRef->assign_to)->type;
                            $tooltip_message = "";
                            if($user_type == 1 || $user_type == 13){
                                $tooltip_message = "CRE User & Company Admin Edit Only";
                            } else if($user_type == 9){
                                $tooltip_message = "TeleSales User Edit Only";
                            } else if($user_type == 11){
                                $tooltip_message = "Service User Edit Only";
                            }
                            $viewCallData .= '<td class="mb-0" style="font-weight: 600;color: #27b50b;"><div class="hover_tooltip"><i class="bx bx-check-circle text-danger me-2" style="font-size: 1.2rem;"></i><span class="tooltiptext">'.$tooltip_message.'</span></div></td>';
                        }
                    } else {
                        $viewCallData .= '<td class="mb-0" style="font-weight: 600;color: #27b50b;"><i class="bx bx-check-circle text-success me-2" style="font-size: 1.2rem;" onclick="viewCall(\'' . $value['id'] . '\')"></i></td>';
                    }
                } else {
                    $viewCallData .= '<td class="mb-0" style="font-weight: 600;color: #27b50b;"><i class="bx bx-check-circle text-success me-2" style="font-size: 1.2rem;" onclick="viewCall(\'' . $value['id'] . '\')"></i></td>';
                }
                $viewCallData .= '<td class="mb-0 col-1" style="font-weight: 600;color: #27b50b;">Call</td>';
                $viewCallData .= '<td class="mb-0 col-4 text-primary ps-4" style="font-weight: 600;color: #27b50b;">' . $value['purpose'] . '</td>';
                $url = route('crm.deal') . "?id=" . $value['lead_id'];
                $lead_prifix = 'L';
                if( $value['is_deal'] == 1){
                    $lead_prifix = 'D';
                }
                $viewCallData .= '<td class="text-warning ms-4 col-4" style="font-weight: 600;"><a target="_blank" href="'.$url.'"> '.$value['first_name'] . ' ' . $value['last_name'].'</br>#'.$lead_prifix.$value['lead_id'].'</a></td>';
                $viewCallData .= '<td class="col-3"><i class="bx bxs-user me-1"></i>' . $user_name['first_name'] . ' ' . $user_name['last_name'] . '</br>' . $date . '  ' . $time . '</td>';
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
        $query->whereNotIn('leads.status', $status_arr);
        if (in_array(Auth::user()->type, [101, 102, 103, 104, 105, 202, 302])) {
            $Meeting_ids = LeadMeetingParticipant::select('meeting_id')->whereIn('reference_id', $Contact_ids)->get();
            $query->whereIn('lead_meetings.id', $Meeting_ids);
        } else{
            $query->where('lead_meetings.user_id', Auth::user()->id);
        }
        // $query->where('leads.assigned_to', Auth::user()->id);
        // if ($isSalePerson == 1) {
        // }
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
                $viewMeetingData .= '<td class="col-3"><i class="bx bxs-user me-1"></i>' . $user_name['first_name'] . ' ' . $user_name['last_name'] . '</br>' . $date . '  ' . $time . '</td>';
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
        $query->whereNotIn('leads.status', $status_arr);
        $query->where(function ($query1) {
            $query1->orWhere('lead_tasks.assign_to', Auth::user()->id);
        });
        // $query->where('leads.assigned_to', Auth::user()->id);
        // if ($isSalePerson == 1) {
        // }
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
                        if(Auth::user()->type == 9) {
                            $type = "task";
                            $viewTaskData .= '<td class="mb-0" style="font-weight: 600;color: #27b50b;"><i class="bx bx-check-circle text-success me-2" style="font-size: 1.2rem;" onclick="viewAutoScheduleAction(\'' . $value['id'] . '\', \'' . $type . '\')"></i></td>';
                        } else {
                            $viewTaskData .= '<td class="mb-0" style="font-weight: 600;color: #27b50b;"><i class="bx bx-check-circle text-success me-2" style="font-size: 1.2rem;" onclick="viewTask(\'' . $value['id'] . '\')"></i></td>';
                        }
                    } else {
                        $user_type = User::find($value['assign_to'])->type;
                        $tooltip_message = "";
                        if($user_type == 1 || $user_type == 13){
                            $tooltip_message = "CRE User & Company Admin Edit Only";
                        } else if($user_type == 9){
                            $tooltip_message = "TeleSales User Edit Only";
                        } else if($user_type == 11){
                            $tooltip_message = "Service User Edit Only";
                        }
                        $viewTaskData .= '<td class="mb-0" style="font-weight: 600;color: #27b50b;"><div class="hover_tooltip"><i class="bx bx-check-circle text-danger me-2" style="font-size: 1.2rem;"></i><span class="tooltiptext">'.$tooltip_message.'</span></div></td>';
                        // $viewTaskData .= '<td class="mb-0" style="font-weight: 600;color: #27b50b;"><i class="bx bx-check-circle text-danger me-2" style="font-size: 1.2rem;"></i></td>';
                    }
                } else {
                    $viewTaskData .= '<td class="mb-0" style="font-weight: 600;color: #27b50b;"><i class="bx bx-check-circle text-success me-2" style="font-size: 1.2rem;" onclick="viewTask(\'' . $value['id'] . '\')"></i></td>';
                }
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

    function PreviousMyActionAjax(Request $request)
    {
        $status_arr = array(104, 105);

        $isSalePerson = isSalePerson();
		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
        $isAdmin = isAdmin();
        if ($isSalePerson == 1) {
			$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
		}

        $req_startdate = $request->start_date;
		$req_enddate = $request->end_date;
        $startDate = date('Y-m-d', strtotime($req_startdate));
		$endDate = date('Y-m-d', strtotime($req_enddate));

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
            9 => 'lead_calls.reference_id',
            10 => 'leads.is_deal',
        );


        $query = LeadCall::query();
        $query->leftJoin('leads', 'leads.id', '=', 'lead_calls.lead_id');
        $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
        $query->where('lead_calls.user_id', Auth::user()->id);
        // $query->where('leads.assigned_to', Auth::user()->id);
        // if ($isSalePerson == 1) {
        // }
        $query->where('lead_calls.is_closed', 0);
        $query->whereNotIn('leads.status', $status_arr);
        // $query->whereDate('lead_calls.call_schedule', '>=', $startDate);
        // $query->whereDate('lead_calls.call_schedule', '<=', $endDate);
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
                if($value['reference_id'] != 0){
                    $LeadRef = LeadTask::find($value['reference_id']);
                    if($LeadRef->is_autogenerate == 1){
                        if($LeadRef->assign_to == Auth::user()->id){
                            $type = "call";
                            $viewCallData .= '<td class="mb-0" style="font-weight: 600;color: #27b50b;"><i class="bx bx-check-circle text-success me-2" style="font-size: 1.2rem;" onclick="viewAutoScheduleAction(\'' . $value['id'] . '\', \'' . $type . '\')"></i></td>';
                        } else {
                            $user_type = User::find($LeadRef->assign_to)->type;
                            $tooltip_message = "";
                            if($user_type == 1 || $user_type == 13){
                                $tooltip_message = "CRE User & Company Admin Edit Only";
                            } else if($user_type == 9){
                                $tooltip_message = "TeleSales User Edit Only";
                            } else if($user_type == 11){
                                $tooltip_message = "Service User Edit Only";
                            }
                            $viewCallData .= '<td class="mb-0" style="font-weight: 600;color: #27b50b;"><div class="hover_tooltip"><i class="bx bx-check-circle text-danger me-2" style="font-size: 1.2rem;"></i><span class="tooltiptext">'.$tooltip_message.'</span></div></td>';
                        }
                    } else {
                        $viewCallData .= '<td class="mb-0" style="font-weight: 600;color: #27b50b;"><i class="bx bx-check-circle text-success me-2" style="font-size: 1.2rem;" onclick="viewCall(\'' . $value['id'] . '\')"></i></td>';
                    }
                } else {
                    $viewCallData .= '<td class="mb-0" style="font-weight: 600;color: #27b50b;"><i class="bx bx-check-circle text-success me-2" style="font-size: 1.2rem;" onclick="viewCall(\'' . $value['id'] . '\')"></i></td>';
                }
                
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
        // $query->where('leads.assigned_to', Auth::user()->id);
        // if ($isSalePerson == 1) {
        // }
        $query->where('lead_meetings.user_id', Auth::user()->id);
        $query->where('lead_meetings.is_closed', 0);
        $query->whereNotIn('leads.status', $status_arr);
        // $query->whereDate('lead_meetings.meeting_date_time', '>=', $startDate);
        // $query->whereDate('lead_meetings.meeting_date_time', '<=', $endDate);
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
        $query->where(function ($query1) {
            $query1->orWhere('lead_tasks.user_id', Auth::user()->id);
            $query1->orWhere('lead_tasks.assign_to', Auth::user()->id);
        });
        $query->where('lead_tasks.is_closed', 0);
        $query->whereNotIn('leads.status', $status_arr);
        // $query->whereDate('lead_tasks.due_date_time', '>=', $startDate);
        // $query->whereDate('lead_tasks.due_date_time', '<=', $endDate);
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
                $viewTaskData .= '<tr>';
                if($value['is_autogenerate'] == 1){
                    if($value['assign_to'] == Auth::user()->id){
                        if(Auth::user()->type == 9) {
                            $type = "task";
                            $viewTaskData .= '<td class="mb-0" style="font-weight: 600;color: #27b50b;"><i class="bx bx-check-circle text-success me-2" style="font-size: 1.2rem;" onclick="viewAutoScheduleAction(\'' . $value['id'] . '\', \'' . $type . '\')"></i></td>';
                        } else {
                            $viewTaskData .= '<td class="mb-0" style="font-weight: 600;color: #27b50b;"><i class="bx bx-check-circle text-success me-2" style="font-size: 1.2rem;" onclick="viewTask(\'' . $value['id'] . '\')"></i></td>';
                        }
                    } else {
                        $user_type = User::find($value['assign_to'])->type;
                        $tooltip_message = "";
                        if($user_type == 1 || $user_type == 13){
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

        $Quot_columns = array(
            'quotation_request.quot_id',
            'quotation_request.deal_id',
            'quotation_request.title',
            'quotation_request.assign_to',
            'quotation_request.status',
            'quotation_request.group_id',
            'wltrn_quotation.customer_name',
            'wltrn_quotation.quotgroup_id',
            'wltrn_quotation.quot_no_str',
            'users.first_name',
            'users.last_name',
            'wltrn_quotation.quot_whitelion_amount',
            'wltrn_quotation.quot_billing_amount',
            'wltrn_quotation.quot_other_amount',
            'wltrn_quotation.quot_total_amount',
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

        $recordsQuotationTotal = $quot_query->count();
        if($quot_query){
            $quot_query = json_decode(json_encode($quot_query), true);

            $viewQuotData = "";
            $viewQuotData .= '<div class="b-bottom p-2">';
            $viewQuotData .= '<table class="col-12">';
            $viewQuotData .= '<tr>';
            $viewQuotData .= '<td class="text-warning" style="font-weight: 600;"><i class="bx bx-check-circle text-success me-2" style="font-size: 1.2rem;"></i></td>';
            $viewQuotData .= '<td class="text-warning col-1" style="font-weight: 600;">Type</td>';
            $viewQuotData .= '<td class="text-warning col-3 ps-4" style="font-weight: 600;">Quotation PDF</td>';
            $viewQuotData .= '<td class="text-warning col-3" style="font-weight: 600;">Customer</td>';
            $viewQuotData .= '<td class="text-warning col-3" style="font-weight: 600;">Assign To</td>';
            $viewQuotData .= '<td class="text-warning col-2" style="font-weight: 600;">Created</td>';
            $viewQuotData .= '</tr>';
            $viewQuotData .= '</table>';
            $viewQuotData .= '</div>';
            foreach ($quot_query as $key => $quotvalue) {
                $quot_req_query = QuotRequest::query();
                $quot_req_query = $quot_req_query->select('quotation_request.status','quotation_request.created_at');
                $quot_req_query = $quot_req_query->selectRaw('CONCAT(created.first_name," ",created.last_name) AS request_created_name');
                $quot_req_query = $quot_req_query->leftJoin('users as created', 'created.id', '=', 'quotation_request.entryby');
                $quot_req_query = $quot_req_query->where('quotation_request.type', 'DISCOUNT');
                $quot_req_query = $quot_req_query->whereIn('quotation_request.id',[$quotvalue['req_id']]);
                $quot_req_query = $quot_req_query->first();
                
                $date = convertDateAndTime($quot_req_query->created_at, 'date');
                $time = convertDateAndTime($quot_req_query->created_at, 'time');

                $user_name = User::find($quotvalue['assign_to']);
                if($user_name){

                    if(isChannelPartner($user_name['type'])){
                        $ChannelPartner = ChannelPartner::where('user_id',$user_name['id'])->first();
                        if($ChannelPartner){
                            $user_name = $ChannelPartner->firm_name;
                        }else {
                            $user_name = $user_name['first_name'] . ' ' . $user_name['last_name'];
                        }
                    }else{
                        $user_name = $user_name['first_name'] . ' ' . $user_name['last_name'];
                    }
                }else{

                    $user_name = '';
                }
                $viewQuotData .= '<div class="b-bottom p-2">';
                $viewQuotData .= '<table class="col-12">';
                $viewQuotData .= '<tr>';
                $viewQuotData .= '<td class="mb-0" style="font-weight: 600;color: #27b50b;"><i class="bx bx-check-circle text-success me-2" style="font-size: 1.2rem;" onclick="ShowBrandWiseDiscount(\'' . $quotvalue['group_id'] . '\')"></i></td>';
                $viewQuotData .= '<td class="mb-0 col-1" style="font-weight: 600; color: #27b50b;">Quotation</td>';
                
                $viewQuotData .= '<td class="mb-0 col-3 text-primary ps-4" style="font-weight: 600;">' . $quotvalue['title'] . '<br><a class="btn-sm edit" title="Pdf" onclick="ItemWisePrint('.$quotvalue['quot_id'].', '.$quotvalue['quotgroup_id'] .')"
                                    style="color: #74788d;"><i class="bx bxs-file-pdf"></i>'.$quotvalue['quotgroup_id'].'('.$quotvalue['quot_no_str'].')'.'
                                </a></td>';
                // $url = route('quot.itemquotedetail') . '?quotno=' . $quotvalue['quot_id'];
                // if (!in_array(Auth::user()->type, array(0, 1, 13))) {
                //     $url = 'javascript: void(0);';
                // }
                $url = route('crm.deal') . "?id=" . $quotvalue['deal_id'];
                
                $viewQuotData .= '<td class="text-warning ms-4 col-3" style="font-weight: 600;"><a target="_blank" href="'.$url.'"> '.$quotvalue['customer_name'].'</br>#D'.$quotvalue['deal_id'].'</a></td>';
                $viewQuotData .= '<td class="col-3"><i class="bx bxs-user me-1"></i>' . $user_name .'</br>' . $date . '  ' . $time . '</td>';
                $viewQuotData .= '<td class="col-2"><i class="bx bxs-user me-1"></i>' . $quot_req_query->request_created_name .'</td>';
                $viewQuotData .= '</tr>';
                $viewQuotData .= '</table>';
                $viewQuotData .= '</div>';
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
            "recordsQuotationTotal" => intval($recordsQuotationTotal),
            // total number of records
            "call_data" => $viewCallData,
            "meeeting_data" => $viewMeetingData,
            "task_data" => $viewTaskData,
            "quotation_request" => $viewQuotData,
            // total data array
            "allrecordsTotal" => intval($recordsCallTotal) + intval($recordsMeetingTotal) + intval($recordsTaskTotal), // total number of records

        );
        return $jsonData;
    }

    function CloseMyActionAjax(Request $request)
    {
        $status_arr = array(104, 105);

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
        // $query->where('leads.assigned_to', Auth::user()->id);
        // if ($isSalePerson == 1) {
        // }
        $query->where('lead_calls.is_closed', 1);
        $query->where('lead_calls.user_id', Auth::user()->id);
        $query->whereNotIn('leads.status', $status_arr);
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
        // $query->where('leads.assigned_to', Auth::user()->id);
        // if ($isSalePerson == 1) {
        // }
        $query->where('lead_meetings.is_closed', 1);
        $query->where('lead_meetings.user_id', Auth::user()->id);
        $query->whereNotIn('leads.status', $status_arr);
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
        // $query->where('leads.assigned_to', Auth::user()->id);
        // if ($isSalePerson == 1) {
        // }
        $query->where('lead_tasks.is_closed', 1);
        $query->where(function ($query1) {
            $query1->orWhere('lead_tasks.user_id', Auth::user()->id);
            $query1->orWhere('lead_tasks.assign_to', Auth::user()->id);
        });
        $query->whereNotIn('leads.status', $status_arr);
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
}