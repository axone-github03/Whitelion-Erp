<?php

namespace App\Http\Controllers\CRM\Reward;

use App\Http\Controllers\Controller;

use App\Models\Lead;
use App\Models\User;
use App\Models\Architect;
use App\Models\CityList;
use App\Models\CRMSettingStageOfSite;
use App\Models\CRMSettingSiteType;
use App\Models\CRMSettingBHK;
use App\Models\CRMSettingWantToCover;
use App\Models\CRMSettingCompetitors;
use App\Models\CRMSettingSubStatus;
use App\Models\CRMSettingSourceType;
use App\Models\ChannelPartner;
use App\Models\CRMSettingSource;
use App\Models\LeadClosing;
use App\Models\LeadTimeline;
use App\Models\LeadTask;
use App\Models\LeadCall;
use App\Models\LeadFile;
use App\Models\CRMSettingTaskOutcomeType;
use App\Models\CRMSettingCallOutcomeType;
use App\Models\CRMSettingAdditionalInfo;
use App\Models\LeadContact;
use App\Models\Wltrn_QuotItemdetail;
use App\Models\Wltrn_Quotation;
use App\Models\LeadUpdate;
use App\Models\LeadMeeting;
use App\Models\CRMSettingMeetingTitle;
use App\Models\LeadMeetingParticipant;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Microsoft\Graph\Generated\Models\IdentityGovernance\Task;

class RewardController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $tabCanAccessBy = [0, 1, 13];

            if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
                return redirect()->route('dashboard');
            }

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $data = [];
        $data['title'] = 'Reward';
        $data['user_id'] = Auth::user()->id;
        if(Auth::user()->type == 202){
            $data['source_type_id'] = "user-202";
            $data['source_type'] = "Architect";
        } else if(Auth::user()->type == 302) {
            $data['source_type_id'] = "user-302";
            $data['source_type'] = "Electrician";
        } else if(Auth::user()->type == 101){
            $data['source_type_id'] = "user-101";
            $data['source_type'] = "ASM";
        } else if(Auth::user()->type == 102){
            $data['source_type_id'] = "user-102";
            $data['source_type'] = "ADM";
        } else if(Auth::user()->type == 103){
            $data['source_type_id'] = "user-103";
            $data['source_type'] = "APM";
        } else if(Auth::user()->type == 104){
            $data['source_type_id'] = "user-104";
            $data['source_type'] = "AD";
        } else if(Auth::user()->type == 105){
            $data['source_type_id'] = "user-105";
            $data['source_type'] = "Retailer";
        }  else if(Auth::user()->type == 12){
            $data['source_type_id'] = "textnotrequired-2";
            $data['source_type'] = "Whitelion HO";
        } else {
            $data['source_type_id'] = "";
            $data['source_type'] = "";
        }
        if(in_array(Auth::user()->type, [101, 102, 103, 104, 105])){
            $data['source_id'] = Auth::user()->id;
            $data['source_text'] = ChannelPartner::select('firm_name')->where('user_id', Auth::user()->id)->first()['firm_name'];
        } else if(in_array(Auth::user()->type, [202, 203])){
            $data['source_id'] = Auth::user()->id;
            $data['source_text'] = Auth::user()->first_name .' '. Auth::user()->last_name;
        }  else if(in_array(Auth::user()->type, [12])){
            $data['source_id'] = 0;
            $data['source_text'] = Auth::user()->first_name .' '. Auth::user()->last_name;
        } else {
            $data['source_id'] = 0;
            $data['source_text'] = "";
        }
        $data['user_type'] = Auth::user()->type;
        $data['is_leaddeal_reward_module'] = 1;
        return view('crm/reward/index', compact('data'));
    }

    public function getListAjax(Request $request)
    {
        $isSalePerson = isSalePerson();
        $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
        $source_type = getInquirySourceTypes();
        $isReception = isReception();
        if ($isSalePerson == 1) {
            $parentSalesUsers = getParentSalePersonsIds(Auth::user()->id);
            $childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
        }

        $selectColumns = ['leads.id', 'leads.inquiry_id', 'leads.first_name', 'leads.last_name', 'leads.phone_number', 'leads.status', 'leads.is_deal', 'created.first_name as lead_owner_name'];

        $searchColumns = [
            0 => 'leads.id',
            1 => 'leads.first_name',
            2 => 'leads.last_name',
            3 => 'leads.phone_number',
            4 => 'leads.inquiry_id',
        ];

        $sortingColumns = [
            0 => 'leads.id',
            1 => 'leads.id',
            2 => 'leads.first_name',
            3 => 'leads.phone_number',
            4 => 'leads.status',
            5 => 'leads.site_stage',
            6 => 'leads.closing_date_time',
            7 => 'leads.assigned_to',
            8 => 'leads.user_id',
        ];

        // RECORDSTOTAL START
        $query = Lead::query();
        $query->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
        $query->leftJoin('lead_files', 'lead_files.lead_id', '=', 'leads.id');
        $query->where('leads.is_deal', 1);
        $query->where('leads.status', 103);

        $arr_where_clause = [];
        $arr_or_clause = [];

        if ($request->isAdvanceFilter == 1) {
            foreach (json_decode($request->AdvanceData, true) as $key => $filt_value) {
                $filter_value = '';
                $source_type = '0';
                if ($filt_value['clause'] == null || $filt_value['clause'] == '') {
                    $response = errorRes('Please Select Clause');
                    return response()
                        ->json($response)
                        ->header('Content-Type', 'application/json');
                } elseif ($filt_value['column'] == null || $filt_value['column'] == '') {
                    $response = errorRes('Please Select column');
                    return response()
                        ->json($response)
                        ->header('Content-Type', 'application/json');
                } elseif ($filt_value['condtion'] == null || $filt_value['condtion'] == '') {
                    $response = errorRes('Please Select condtion');
                    return response()
                        ->json($response)
                        ->header('Content-Type', 'application/json');
                } else {
                    $column = getFilterColumnCRM()[$filt_value['column']];
                    $condtion = getFilterCondtionCRM()[$filt_value['condtion']];
                    if ($column['value_type'] == 'text') {
                        if ($filt_value['value_text'] == null || $filt_value['value_text'] == '') {
                            $response = errorRes('Please enter value');
                            return response()
                                ->json($response)
                                ->header('Content-Type', 'application/json');
                        } else {
                            $filter_value = $filt_value['value_text'];
                        }
                    } elseif ($column['value_type'] == 'select' && $condtion['value_type'] == 'single_select') {
                        if ($column['code'] == 'leads_source' && ($filt_value['value_source_type'] == null || $filt_value['value_source_type'] == '')) {
                            $response = errorRes('Please select value');
                            return response()
                                ->json($response)
                                ->header('Content-Type', 'application/json');
                        } else {
                            $source_type = $filt_value['value_source_type'];
                        }
                        if ($filt_value['value_select'] == null || $filt_value['value_select'] == '') {
                            $response = errorRes('Please select value');
                            return response()
                                ->json($response)
                                ->header('Content-Type', 'application/json');
                        } else {
                            $filter_value = $filt_value['value_select'];
                        }
                    } elseif ($column['value_type'] == 'select' && $condtion['value_type'] == 'multi_select') {
                        if ($column['code'] == 'leads_source' && ($filt_value['value_source_type'] == null || $filt_value['value_source_type'] == '')) {
                            $response = errorRes('Please select value');
                            return response()
                                ->json($response)
                                ->header('Content-Type', 'application/json');
                        } else {
                            $source_type = $filt_value['value_source_type'];
                        }
                        if (!isset($filt_value['value_multi_select']) && empty($filt_value['value_multi_select'])) {
                            $response = errorRes('Please select value');
                            return response()
                                ->json($response)
                                ->header('Content-Type', 'application/json');
                        } else {
                            $filter_value = $filt_value['value_multi_select'];
                        }
                    } elseif ($column['value_type'] == 'date' && $condtion['value_type'] == 'single_select') {
                        if ($filt_value['value_date'] == null || $filt_value['value_date'] == '') {
                            $response = errorRes('Please enter date');
                            return response()
                                ->json($response)
                                ->header('Content-Type', 'application/json');
                        } else {
                            $filter_value = $filt_value['value_date'];
                        }
                    } elseif ($column['value_type'] == 'date' && $condtion['value_type'] == 'between') {
                        if (($filt_value['value_from_date'] == null || $filt_value['value_from_date'] == '') && ($filt_value['value_to_date'] == null || $filt_value['value_to_date'] == '')) {
                            $response = errorRes('Please enter from to date');
                            return response()
                                ->json($response)
                                ->header('Content-Type', 'application/json');
                        } else {
                            $filter_value = $filt_value['value_from_date'] . ',' . $filt_value['value_to_date'];
                        }
                    } elseif ($column['value_type'] == 'reward_select') {
                        if ($filt_value['value_select'] == null || $filt_value['value_select'] == '') {
                            $response = errorRes('Please select value');
                            return response()
                                ->json($response)
                                ->header('Content-Type', 'application/json');
                        } else {
                            $filter_value = $filt_value['value_select'];
                        }
                    }
                    if ($filt_value['clause'] != 0) {
                        $clause = getFilterClauseCRM()[$filt_value['clause']];
                    }

                    if ($filt_value['clause'] == 0) {
                        $newdata['clause'] = 0;
                        $newdata['column'] = $column['id'];
                        $newdata['condtion'] = $condtion['id'];
                        $newdata['value'] = $filter_value;
                        $newdata['source_type'] = $source_type;

                        array_push($arr_where_clause, $newdata);
                    } elseif ($clause['clause'] == 'where') {
                        $newdata['clause'] = $clause['id'];
                        $newdata['column'] = $column['id'];
                        $newdata['condtion'] = $condtion['id'];
                        $newdata['value'] = $filter_value;
                        $newdata['source_type'] = $source_type;

                        array_push($arr_where_clause, $newdata);
                    } elseif ($clause['clause'] == 'orwhere') {
                        $newdata['clause'] = $clause['id'];
                        $newdata['column'] = $column['id'];
                        $newdata['condtion'] = $condtion['id'];
                        $newdata['value'] = $filter_value;
                        $newdata['source_type'] = $source_type;

                        array_push($arr_or_clause, $newdata);
                    }
                }
            }
        }

        if ($request->isAdvanceFilter == 1) {
            

            foreach ($arr_where_clause as $wherekey => $objwhere) {
                $Column = getFilterColumnCRM()[$objwhere['column']];
                $Condtion = getFilterCondtionCRM()[$objwhere['condtion']];
                $lstDateFilter = getDateFilterValue();
                $Filter_Value = $objwhere['value'];
                $source_type = $objwhere['source_type'];

                if ($Condtion['code'] == 'is') {
                    if ($Column['value_type'] == 'leads_source') {
                        $query->whereIn('lead_sources.source', $source_type);
                    } elseif ($Column['value_type'] == 'date') {
                        // $date_filter_value = date('Y-m-d', strtotime($Filter_Value));
                        // $query->whereDate($Column['column_name'], '=', $date_filter_value);
                        $objDateFilter = $lstDateFilter[$Filter_Value];

                        $currentStartDate = date('Y-m-d', strtotime(date('Y-m-d')));
                        $currentEndDate = date('Y-m-d', strtotime(date('Y-m-d')));

                        if ($objDateFilter['code'] == 'all_closing') {
                            $query->where($Column['column_name'], '!=', null);
                        } elseif ($objDateFilter['code'] == 'in_this_week') {
                            $currentWeekDay = date('w', strtotime($currentStartDate));
                            $weekStartDate = date('Y-m-d', strtotime($currentStartDate . ' -' . ($currentWeekDay - 1) . ' days'));
                            $weekEndDate = date('Y-m-d', strtotime($currentEndDate . ' +' . (7 - $currentWeekDay) . ' days'));
                            $query->whereDate($Column['column_name'], '<=', $weekEndDate);
                        } elseif ($objDateFilter['code'] == 'in_this_month') {
                            $monthStartDay = date('Y-m-01', strtotime($currentStartDate));
                            $monthEndDay = date('Y-m-t', strtotime($currentEndDate));
                            $query->whereDate($Column['column_name'], '<=', $monthEndDay);
                        } elseif ($objDateFilter['code'] == 'in_next_month') {
                            $currentStartDate = date('Y-m-d', strtotime($currentStartDate . ' +5 hours'));
                            $currentStartDate = date('Y-m-d', strtotime($currentStartDate . ' +30 minutes'));
                            $monthStartDay = date('Y-m-01', strtotime($currentStartDate . ' + 1 month'));
                            $monthEndDay = date('Y-m-t', strtotime($monthStartDay));
                            $query->whereDate($Column['column_name'], '<=', $monthEndDay);
                        } elseif ($objDateFilter['code'] == 'in_next_two_month') {
                            $monthStartDay = date('Y-m-01', strtotime($currentStartDate . ' + 1 month'));
                            $monthEndDay = date('Y-m-t', strtotime($currentEndDate . ' + 2 month'));
                            $query->whereDate($Column['column_name'], '<=', $monthEndDay);
                        } elseif ($objDateFilter['code'] == 'in_next_three_month') {
                            $monthStartDay = date('Y-m-01', strtotime($currentStartDate . ' + 1 month'));
                            $monthEndDay = date('Y-m-t', strtotime($currentEndDate . ' + 3 month'));
                            $query->whereDate($Column['column_name'], '>=', date('Y-m-d'));
                            $query->whereDate($Column['column_name'], '<=', $monthEndDay);
                        }
                        // if ($objDateFilter['code'] == "today") {
                        //     $date_filter_value = date('Y-m-d', strtotime(date('Y-m-d')));
                        //     $query->whereDate($Column['column_name'], '=', $date_filter_value);
                        //     // $query->whereDate($Column['column_name'], '=', date('Y-m-d'));
                        // }
                        else {
                            $date_filter_value = explode(',', $objDateFilter['value']);
                            $from_date_filter = $date_filter_value[0];
                            $from_date_filter_value = date('Y-m-d', strtotime($from_date_filter));

                            $to_date_filter = $date_filter_value[1];
                            $to_date_filter_value = date('Y-m-d', strtotime($to_date_filter));

                            $query->whereBetween(DB::raw('DATE(' . $Column['column_name'] . ')'), [$from_date_filter_value, $to_date_filter_value]);
                        }
                    } elseif ($Column['value_type'] == 'select') {
                        if($Column['code'] == "lead_miss_data"){
                            $missDataValue = getLeadFilterMissFilterValue()[$Filter_Value];
                            $query->where($missDataValue['column_name'], $missDataValue['value']);                         
                        }else{
                            $query->where($Column['column_name'], $Filter_Value);
                        }
                    } elseif ($Column['value_type'] == 'reward_select') {
                        if ($Filter_Value == 1) {
                            // $query->leftJoin('lead_files', 'lead_files.lead_id', '=', 'leads.id');
                            $query->where('lead_files.hod_approved', 0);
                            $query->where('lead_files.status', 100);
                        } elseif ($Filter_Value == 2) {
                            // $query->leftJoin('lead_files', 'lead_files.lead_id', '=', 'leads.id');
                            $query->Where('lead_files.hod_approved', 1);
                        }
                    } else {
                        $query->where($Column['column_name'], 'like', '%' . $Filter_Value . '%');
                    }
                } elseif ($Condtion['code'] == 'is_not') {
                    if ($Column['value_type'] == 'leads_source') {
                        $query->whereNotIn('lead_sources.source', $source_type);
                    } elseif ($Column['value_type'] == 'date') {
                        $objDateFilter = $lstDateFilter[$Filter_Value];

                        $currentStartDate = date('Y-m-d', strtotime(date('Y-m-d')));
                        $currentEndDate = date('Y-m-d', strtotime(date('Y-m-d')));

                        if ($objDateFilter['code'] == 'all_closing') {
                            $query->where($Column['column_name'], '!=', null);
                        } elseif ($objDateFilter['code'] == 'in_this_week') {
                            $currentWeekDay = date('w', strtotime($currentStartDate));
                            $weekStartDate = date('Y-m-d', strtotime($currentStartDate . ' -' . ($currentWeekDay - 1) . ' days'));
                            $weekEndDate = date('Y-m-d', strtotime($currentEndDate . ' +' . (7 - $currentWeekDay) . ' days'));
                            $query->whereDate($Column['column_name'], '<=', $weekEndDate);
                        } elseif ($objDateFilter['code'] == 'in_this_month') {
                            $monthStartDay = date('Y-m-01', strtotime($currentStartDate));
                            $monthEndDay = date('Y-m-t', strtotime($currentEndDate));
                            $query->whereDate($Column['column_name'], '<=', $monthEndDay);
                        } elseif ($objDateFilter['code'] == 'in_next_month') {
                            $currentStartDate = date('Y-m-d', strtotime($currentStartDate . ' +5 hours'));
                            $currentStartDate = date('Y-m-d', strtotime($currentStartDate . ' +30 minutes'));
                            $monthStartDay = date('Y-m-01', strtotime($currentStartDate . ' + 1 month'));
                            $monthEndDay = date('Y-m-t', strtotime($monthStartDay));
                            $query->whereDate($Column['column_name'], '<=', $monthEndDay);
                        } elseif ($objDateFilter['code'] == 'in_next_two_month') {
                            $monthStartDay = date('Y-m-01', strtotime($currentStartDate . ' + 1 month'));
                            $monthEndDay = date('Y-m-t', strtotime($currentEndDate . ' + 2 month'));
                            $query->whereDate($Column['column_name'], '<=', $monthEndDay);
                        } elseif ($objDateFilter['code'] == 'in_next_three_month') {
                            $monthStartDay = date('Y-m-01', strtotime($currentStartDate . ' + 1 month'));
                            $monthEndDay = date('Y-m-t', strtotime($currentEndDate . ' + 3 month'));
                            $query->whereDate($Column['column_name'], '>=', date('Y-m-d'));
                            $query->whereDate($Column['column_name'], '<=', $monthEndDay);
                        }

                        // if ($objDateFilter['code'] == "today") {
                        //     $date_filter_value = date('Y-m-d', strtotime(date('Y-m-d')));
                        //     $query->whereDate($Column['column_name'], '!=', $date_filter_value);
                        //     // $query->whereDate($Column['column_name'], '!=', date('Y-m-d'));
                        // }
                        else {
                            $date_filter_value = explode(',', $objDateFilter['value']);
                            $from_date_filter = $date_filter_value[0];
                            $from_date_filter_value = date('Y-m-d', strtotime($from_date_filter));

                            $to_date_filter = $date_filter_value[1];
                            $to_date_filter_value = date('Y-m-d', strtotime($to_date_filter));

                            $query->whereNotBetween(DB::raw('DATE(' . $Column['column_name'] . ')'), [$from_date_filter_value, $to_date_filter_value]);
                        }
                    } elseif ($Column['value_type'] == 'select') {
                        if($Column['code'] == "lead_miss_data"){
                            $missDataValue = getLeadFilterMissFilterValue()[$Filter_Value];
                            $query->where($missDataValue['column_name'], '<>',$missDataValue['value']);                         
                        }else{
                            $query->whereNotNull($Column['column_name']);
                            $query->where($Column['column_name'], '!=', $Filter_Value);
                        }
                    } else {
                        $query->whereNotNull($Column['column_name']);
                        $query->where($Column['column_name'], 'not like', '%' . $Filter_Value . '%');
                    }
                } elseif ($Condtion['code'] == 'contains') {
                    if ($Column['value_type'] == 'leads_source') {
                        $query->whereIn('lead_sources.source', $source_type);
                    }
                    if ($Column['value_type'] == 'select') {
                        if($Column['code'] == "lead_miss_data"){
                            foreach ($Filter_Value as $mis_key => $mis_value) {
                                $missDataValue = getLeadFilterMissFilterValue()[$mis_value];
                                $query->where($missDataValue['column_name'],$missDataValue['value']);
                            }
                        }else{
                            $query->whereIn($Column['column_name'], $Filter_Value);
                        }
                    } else {
                        $Filter_Value = explode(',', $Filter_Value);
                        $query->whereIn($Column['column_name'], $Filter_Value);
                    }
                } elseif ($Condtion['code'] == 'not_contains') {
                    if ($Column['value_type'] == 'leads_source') {
                        $query->whereNotIn('lead_sources.source', $source_type);
                    }
                    if ($Column['value_type'] == 'select') {
                        if($Column['code'] == "lead_miss_data"){
                            foreach ($Filter_Value as $mis_key => $mis_value) {
                                $missDataValue = getLeadFilterMissFilterValue()[$mis_value];
                                $query->where($missDataValue['column_name'],'<>',$missDataValue['value']);
                            }
                        }else{
                            $query->whereNotIn($Column['column_name'], $Filter_Value);
                        }
                    } else {
                        $Filter_Value = explode(',', $Filter_Value);
                        $query->whereNotIn($Column['column_name'], $Filter_Value);
                    }
                } elseif ($Condtion['code'] == 'between') {
                    if ($Column['value_type'] == 'date') {
                        $date_filter_value = explode(',', $Filter_Value);
                        $from_date_filter = $date_filter_value[0];
                        $from_date_filter_value = date('Y-m-d', strtotime($from_date_filter));

                        $to_date_filter = $date_filter_value[1];
                        $to_date_filter_value = date('Y-m-d', strtotime($to_date_filter));

                        $query->whereBetween(DB::raw('DATE(' . $Column['column_name'] . ')'), [$from_date_filter_value, $to_date_filter_value]);
                    }
                }
            }

            if (count($arr_or_clause) > 0) {
                $query->orWhere(function ($query) use ($arr_or_clause) {
                    foreach ($arr_or_clause as $orkey => $objor) {
                        $Column = getFilterColumnCRM()[$objor['column']];
                        $Condtion = getFilterCondtionCRM()[$objor['condtion']];
                        $lstDateFilter = getDateFilterValue();
                        $Filter_Value = $objor['value'];
                        $source_type = $objor['source_type'];

                        if ($Condtion['code'] == 'is') {
                            if ($Column['value_type'] == 'leads_source') {
                                $query->whereIn('lead_sources.source', $source_type);
                            } elseif ($Column['value_type'] == 'date') {
                                // $date_filter_value = date('Y-m-d', strtotime($Filter_Value));
                                // $query->orWhereDate($Column['column_name'], '=', $date_filter_value);

                                $objDateFilter = $lstDateFilter[$Filter_Value];

                                $currentStartDate = date('Y-m-d', strtotime(date('Y-m-d')));
                                $currentEndDate = date('Y-m-d', strtotime(date('Y-m-d')));

                                if ($objDateFilter['code'] == 'all_closing') {
                                    $query->orwhere($Column['column_name'], '!=', null);
                                } elseif ($objDateFilter['code'] == 'in_this_week') {
                                    $currentWeekDay = date('w', strtotime($currentStartDate));
                                    $weekStartDate = date('Y-m-d', strtotime($currentStartDate . ' -' . ($currentWeekDay - 1) . ' days'));
                                    $weekEndDate = date('Y-m-d', strtotime($currentEndDate . ' +' . (7 - $currentWeekDay) . ' days'));
                                    $query->orWhereDate($Column['column_name'], '<=', $weekEndDate);
                                } elseif ($objDateFilter['code'] == 'in_this_month') {
                                    $monthStartDay = date('Y-m-01', strtotime($currentStartDate));
                                    $monthEndDay = date('Y-m-t', strtotime($currentEndDate));
                                    $query->orWhereDate($Column['column_name'], '<=', $monthEndDay);
                                } elseif ($objDateFilter['code'] == 'in_next_month') {
                                    $currentStartDate = date('Y-m-d', strtotime($currentStartDate . ' +5 hours'));
                                    $currentStartDate = date('Y-m-d', strtotime($currentStartDate . ' +30 minutes'));
                                    $monthStartDay = date('Y-m-01', strtotime($currentStartDate . ' + 1 month'));
                                    $monthEndDay = date('Y-m-t', strtotime($monthStartDay));
                                    $query->orWhereDate($Column['column_name'], '<=', $monthEndDay);
                                } elseif ($objDateFilter['code'] == 'in_next_two_month') {
                                    $monthStartDay = date('Y-m-01', strtotime($currentStartDate . ' + 1 month'));
                                    $monthEndDay = date('Y-m-t', strtotime($currentEndDate . ' + 2 month'));
                                    $query->orWhereDate($Column['column_name'], '<=', $monthEndDay);
                                } elseif ($objDateFilter['code'] == 'in_next_three_month') {
                                    $monthStartDay = date('Y-m-01', strtotime($currentStartDate . ' + 1 month'));
                                    $monthEndDay = date('Y-m-t', strtotime($currentEndDate . ' + 3 month'));
                                    $query->orWhereDate($Column['column_name'], '>=', date('Y-m-d'));
                                    $query->orWhereDate($Column['column_name'], '<=', $monthEndDay);
                                } else {
                                    $date_filter_value = explode(',', $objDateFilter['value']);
                                    $from_date_filter = $date_filter_value[0];
                                    $from_date_filter_value = date('Y-m-d', strtotime($from_date_filter));

                                    $to_date_filter = $date_filter_value[1];
                                    $to_date_filter_value = date('Y-m-d', strtotime($to_date_filter));

                                    $query->whereBetween(DB::raw('DATE(' . $Column['column_name'] . ')'), [$from_date_filter_value, $to_date_filter_value]);
                                }
                            } elseif ($Column['value_type'] == 'select') {
                                if($Column['code'] == "lead_miss_data"){
                                    $missDataValue = getLeadFilterMissFilterValue()[$Filter_Value];
                                    $query->orWhere($missDataValue['column_name'],$missDataValue['value']);                         
                                }else{
                                    $query->orWhere($Column['column_name'], $Filter_Value);
                                }
                            } elseif ($Column['value_type'] == 'reward_select') {
                                if ($Filter_Value == 1) {
                                    // $query->leftJoin('lead_files', 'lead_files.lead_id', '=', 'leads.id');
                                    $query->where('lead_files.hod_approved', 0);
                                    $query->where('lead_files.status', 100);
                                } elseif ($Filter_Value == 2) {
                                    // $query->leftJoin('lead_files', 'lead_files.lead_id', '=', 'leads.id');
                                    $query->Where('lead_files.hod_approved', 1);
                                }
                            } else {
                                $query->orWhere($Column['column_name'], 'like', '%' . $Filter_Value . '%');
                            }
                        } elseif ($Condtion['code'] == 'is_not') {
                            if ($Column['value_type'] == 'leads_source') {
                                $query->whereIn('lead_sources.source', $source_type);
                            } elseif ($Column['value_type'] == 'date') {
                                // $query->orWhereDate($Column['column_name'], '!=', $date_filter_value);

                                $objDateFilter = $lstDateFilter[$Filter_Value];

                                $currentStartDate = date('Y-m-d', strtotime(date('Y-m-d')));
                                $currentEndDate = date('Y-m-d', strtotime(date('Y-m-d')));

                                if ($objDateFilter['code'] == 'all_closing') {
                                    $query->orwhere($Column['column_name'], '!=', null);
                                } elseif ($objDateFilter['code'] == 'in_this_week') {
                                    $currentWeekDay = date('w', strtotime($currentStartDate));
                                    $weekStartDate = date('Y-m-d', strtotime($currentStartDate . ' -' . ($currentWeekDay - 1) . ' days'));
                                    $weekEndDate = date('Y-m-d', strtotime($currentEndDate . ' +' . (7 - $currentWeekDay) . ' days'));
                                    $query->orWhereDate($Column['column_name'], '<=', $weekEndDate);
                                } elseif ($objDateFilter['code'] == 'in_this_month') {
                                    $monthStartDay = date('Y-m-01', strtotime($currentStartDate));
                                    $monthEndDay = date('Y-m-t', strtotime($currentEndDate));
                                    $query->orWhereDate($Column['column_name'], '<=', $monthEndDay);
                                } elseif ($objDateFilter['code'] == 'in_next_month') {
                                    $currentStartDate = date('Y-m-d', strtotime($currentStartDate . ' +5 hours'));
                                    $currentStartDate = date('Y-m-d', strtotime($currentStartDate . ' +30 minutes'));
                                    $monthStartDay = date('Y-m-01', strtotime($currentStartDate . ' + 1 month'));
                                    $monthEndDay = date('Y-m-t', strtotime($monthStartDay));
                                    $query->orWhereDate($Column['column_name'], '<=', $monthEndDay);
                                } elseif ($objDateFilter['code'] == 'in_next_two_month') {
                                    $monthStartDay = date('Y-m-01', strtotime($currentStartDate . ' + 1 month'));
                                    $monthEndDay = date('Y-m-t', strtotime($currentEndDate . ' + 2 month'));
                                    $query->orWhereDate($Column['column_name'], '<=', $monthEndDay);
                                } elseif ($objDateFilter['code'] == 'in_next_three_month') {
                                    $monthStartDay = date('Y-m-01', strtotime($currentStartDate . ' + 1 month'));
                                    $monthEndDay = date('Y-m-t', strtotime($currentEndDate . ' + 3 month'));
                                    $query->orWhereDate($Column['column_name'], '>=', date('Y-m-d'));
                                    $query->orWhereDate($Column['column_name'], '<=', $monthEndDay);
                                } else {
                                    $date_filter_value = explode(',', $objDateFilter['value']);
                                    $from_date_filter = $date_filter_value[0];
                                    $from_date_filter_value = date('Y-m-d', strtotime($from_date_filter));

                                    $to_date_filter = $date_filter_value[1];
                                    $to_date_filter_value = date('Y-m-d', strtotime($to_date_filter));

                                    $query->whereNotBetween(DB::raw('DATE(' . $Column['column_name'] . ')'), [$from_date_filter_value, $to_date_filter_value]);
                                }
                            } elseif ($Column['value_type'] == 'select') {
                                if($Column['code'] == "lead_miss_data"){
                                    $missDataValue = getLeadFilterMissFilterValue()[$Filter_Value];
                                    $query->orWhere($missDataValue['column_name'],'<>',$missDataValue['value']);                         
                                }else{
                                    $query->orWhere($Column['column_name'], '!=', $Filter_Value);
                                }
                            } else {
                                $query->orWhere($Column['column_name'], 'not like', '%' . $Filter_Value . '%');
                            }
                        } elseif ($Condtion['code'] == 'contains') {
                            if ($Column['value_type'] == 'leads_source') {
                                $query->whereIn('lead_sources.source', $source_type);
                            }
                            if ($Column['value_type'] == 'select') {
                                if ($Column['code'] == 'leads_source') {
                                } else if($Column['code'] == "lead_miss_data"){
                                    foreach ($Filter_Value as $mis_key => $mis_value) {
                                        $missDataValue = getLeadFilterMissFilterValue()[$mis_value];
                                        $query->orWhere($missDataValue['column_name'],$missDataValue['value']);
                                    }
                                }else{
                                    $query->orWhere($Column['column_name'], $Filter_Value);
                                }
                            } else {
                                $Filter_Value = explode(',', $Filter_Value);
                                $query->orWhereIn($Column['column_name'], $Filter_Value);
                            }
                        } elseif ($Condtion['code'] == 'not_contains') {
                            if ($Column['value_type'] == 'leads_source') {
                                $query->whereIn('lead_sources.source', $source_type);
                            }
                            if ($Column['value_type'] == 'select') {
                                if($Column['code'] == "lead_miss_data"){
                                    foreach ($Filter_Value as $mis_key => $mis_value) {
                                        $missDataValue = getLeadFilterMissFilterValue()[$mis_value];
                                        $query->orWhere($missDataValue['column_name'],'<>',$missDataValue['value']);
                                    }
                                }else{
                                    $query->orWhereNotIn($Column['column_name'], $Filter_Value);
                                }
                            } else {
                                $Filter_Value = explode(',', $Filter_Value);
                                $query->orWhereNotIn($Column['column_name'], $Filter_Value);
                            }
                        } elseif ($Condtion['code'] == 'between') {
                            if ($Column['value_type'] == 'date') {
                                $date_filter_value = explode(',', $Filter_Value);
                                $from_date_filter = $date_filter_value[0];
                                $from_date_filter_value = date('Y-m-d', strtotime($from_date_filter));

                                $to_date_filter = $date_filter_value[1];
                                $to_date_filter_value = date('Y-m-d', strtotime($to_date_filter));

                                $query->orWhereBetween(DB::raw('DATE(' . $Column['column_name'] . ')'), [$from_date_filter_value, $to_date_filter_value]);
                            }
                        }
                    }
                });
            }
        }

        if (isSalePerson() == 1) {
            $query->whereIn('assigned_to', getChildSalePersonsIds(Auth::user()->id));
        }

        if (isChannelPartner(Auth::user()->type) != 0) {
            $query->where('lead_sources.source', Auth::user()->id);
        }

        if (isArchitect() == 1) {
            $query->where('leads.architect', Auth::user()->id);
        }
        if ($isReception == 1) {
            $query->where('leads.created_by', Auth::user()->id);
        }

        if (isElectrician() == 1) {
            $query->where('leads.electrician', Auth::user()->id);
        }
        
        $search_value = '';
        if (isset($request['search']['value'])) {
            $isFilterApply = 1;
            $search_value = $request['search']['value'];
            $query->where(function ($query) use ($search_value, $searchColumns) {
                for ($i = 0; $i < count($searchColumns); $i++) {
                    if ($i == 0) {
                        $query->where($searchColumns[$i], 'like', '%' . $search_value . '%');
                    } else {
                        $query->orWhere($searchColumns[$i], 'like', '%' . $search_value . '%');
                    }
                }
            });
        }

        $recordsTotal = $query->count();
        $recordsFiltered = $recordsTotal;
        $Filter_lead_ids = $query->distinct()->pluck('leads.id');
        // RECORDSTOTAL END

        // RECORDSFILTERED START
        $query = Lead::query()
            ->whereIn('leads.id', $Filter_lead_ids)
            ->get();
        $recordsFiltered = $query->count();
        // RECORDSFILTERED START

        $Lead = Lead::query()
            ->whereIn('leads.id', $Filter_lead_ids)
            ->orderBy('leads.id', 'desc');
        $Lead_data_total = $Lead->count();
        $Lead->limit($request->length);
        $Lead->offset($request->start);

        $Lead_data = $Lead->get();

        if ($Lead->count() >= 1) {
            $FirstPageLeadId = $Lead_data[0]['id'];
        } else {
            $FirstPageLeadId = 0;
        }
        $data = json_decode(json_encode($Lead_data), true);

        $viewData = [];
        $LeadStatus = getLeadStatus();
        foreach ($data as $key => $value) {
            $view = '';
            $view = '<li class="lead_li" id="lead_' .$value['id'] . '" onclick="getDataDetail(' . $value['id'] . ')" style="list-style: none;">';
            $view .= '<a href="javascript: void(0);">';
            $view .= '<div class="d-flex">';
            $view .= '<div class="flex-grow-1 overflow-hidden">';
            if ($value['inquiry_id'] == '' || $value['inquiry_id'] == null) {
                $view .= '<h5 class="text-truncate font-size-14 mb-1">#' . highlightString($value['id'],$search_value) . '</h5>';
            } else {
                $view .= '<h5 class="text-truncate font-size-14 mb-1">#' . highlightString($value['id'],$search_value) . '-' . highlightString($value['inquiry_id'],$search_value) . '</h5>';
            }
            $view .= '<p class="text-truncate mb-0">' . highlightString(ucwords(strtolower($value['first_name'])),$search_value) . '</p>';
            $view .= '</div>';
            $view .= '<div class="d-flex justify-content-end font-size-16">';
            $view .= '<span class="badge badge-pill badge badge-soft-info font-size-11" style="height: fit-content;" id="' . $value['id'] . '_lead_list_status">' . $LeadStatus[$value['status']]['name'] . '</span>';

            $view .= '</div>';
            $view .= '</div>';
            $view .= '</a>';
            $view .= '</li>';

            $viewData[$key] = [];
            $viewData[$key]['view'] = $view;
        }

        $jsonData = [
            'draw' => intval($request['draw']),
            'recordsTotal' => intval($recordsTotal),
            'recordsFiltered' => intval($recordsFiltered),
            'data' => $viewData,
            'dataed' => $data,
            'count' => $Lead_data_total,
            'FirstPageLeadId' => $FirstPageLeadId,
        ];
        return $jsonData;
    }

    public function getDetail(Request $request)
    {
        $Lead = Lead::find($request->id);
        $data = [];

        if ($Lead) {
            $Lead = json_encode($Lead);
            $Lead = json_decode($Lead, true);

            $data['lead'] = $Lead;

            $data['lead']['status_label'] = getLeadStatus()[$data['lead']['status']]['name'];
            $data['lead']['suggest_step'] = getLeadNextStatus($data['lead']['status'])['name'];
            $data['lead']['created_at'] = date('d/m/Y g:i A', strtotime($data['lead']['created_at']));
            $data['lead']['updated_at'] = date('d/m/Y g:i A', strtotime($data['lead']['updated_at']));

            $main_source_type = explode('-', $data['lead']['source_type']);

            if ($main_source_type[0] != 'master') {
                $source_type = getLeadSourceTypes();
                foreach ($source_type as $key => $value) {
                    if ($value['type'] == $main_source_type[0] && $value['id'] == $main_source_type[1]) {
                        $data['lead']['source_type_id'] = $data['lead']['source_type'];
                        $data['lead']['source_type'] = $value['lable'];
                    }
                }
            } elseif ($main_source_type[0] == 'master') {
                $main_source_type = CRMSettingSourceType::select('id', 'name')
                    ->where('id', $main_source_type[1])
                    ->first();

                if ($main_source_type) {
                    $data['lead']['source_type_id'] = $main_source_type->id;
                    $data['lead']['source_type'] = $main_source_type->name;
                }
            }

            if ($main_source_type[0] == 'user') {
                if (isset(getChannelPartners()[$main_source_type[1]]['short_name'])) {
                    $lst_main_source = ChannelPartner::select('user_id AS id', 'firm_name AS text')
                        ->where('user_id', $data['lead']['source'])
                        ->first();
                    if ($lst_main_source) {
                        $main_source = $lst_main_source->text;
                    } else {
                        $main_source = '';
                    }
                } else {
                    $lst_main_source = User::select('id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"))
                        ->where('id', $data['lead']['source'])
                        ->first();
                    if ($lst_main_source) {
                        $main_source = $lst_main_source->text;
                    } else {
                        $main_source = '';
                    }
                }
            } elseif ($main_source_type[0] == 'master') {
                $lst_main_source = CRMSettingSource::select('id', 'name')
                    ->where('id', $data['lead']['source'])
                    ->first();
                if ($lst_main_source) {
                    $main_source = $lst_main_source->name;
                } else {
                    $main_source = '';
                }
            } else {
                $main_source['id'] = $data['lead']['source'];
                $main_source['text'] = $data['lead']['source'];
            }

            $data['lead']['source'] = $main_source;

            if ($data['lead']['closing_date_time'] != null) {
                $lead_closing_date_time = $data['lead']['closing_date_time'];
                $lead_closing_date_time = date('d-m-Y', strtotime($lead_closing_date_time));
                $data['lead']['closing_date_time'] = $lead_closing_date_time;
            }

            $assigned_to = User::select('users.id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"), 'users.phone_number');
            $assigned_to->where('users.id', $data['lead']['assigned_to']);
            $assigned_to = $assigned_to->first();
            if ($assigned_to) {
                $data['lead']['assigned'] = $assigned_to->text;
                $data['lead']['assigned_mobile'] = $assigned_to->phone_number;
            } else {
                $data['lead']['assigned'] = ' - ';
                $data['lead']['assigned_mobile'] = ' - ';
            }

            $architect = User::select('users.id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"), 'users.phone_number');
            $architect->where('users.id', $data['lead']['architect']);
            $architect = $architect->first();
            if ($architect) {
                $data['lead']['architect'] = $architect->text;
                $data['lead']['architect_mobile'] = $architect->phone_number;
            } else {
                $data['lead']['architect'] = ' - ';
                $data['lead']['architect_mobile'] = ' - ';
            }

            $created_by = User::select('users.id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"), 'users.phone_number');
            $created_by->where('users.id', $data['lead']['created_by']);
            $created_by = $created_by->first();
            if ($created_by) {
                $data['lead']['created_by'] = $created_by->text;
            } else {
                $data['lead']['created_by'] = ' - ';
            }

            $updated_by = User::select('users.id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"), 'users.phone_number');
            $updated_by->where('users.id', $data['lead']['updated_by']);
            $updated_by = $updated_by->first();
            if ($updated_by) {
                $data['lead']['updated_by'] = $updated_by->text;
            } else {
                $data['lead']['updated_by'] = ' - ';
            }

            $electrician = User::select('users.id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"), 'users.phone_number');
            $electrician->where('users.id', $data['lead']['electrician']);
            $electrician = $electrician->first();
            if ($electrician) {
                $data['lead']['electrician'] = $electrician->text;
                $data['lead']['electrician_mobile'] = $electrician->phone_number;
            } else {
                $data['lead']['electrician'] = ' - ';
                $data['lead']['electrician_mobile'] = ' - ';
            }

            $CityList = CityList::select('city_list.id', 'city_list.name as city_list_name', 'state_list.name as state_list_name');
            $CityList->leftJoin('state_list', 'state_list.id', '=', 'city_list.state_id');
            $CityList->where('city_list.id', $data['lead']['city_id']);
            $CityList = $CityList->first();

            $CityStateName = '';
            $data['lead']['city'] = "";
            if ($CityList) {
                $CityStateName = $CityList->city_list_name . ', ' . $CityList->state_list_name . ', India';
                $data['lead']['city'] = $CityStateName;
            }

            // $meetingCityList = CityList::select('city_list.id', 'city_list.name as city_list_name', 'state_list.name as state_list_name');
            // $meetingCityList->leftJoin('state_list', 'state_list.id', '=', 'city_list.state_id');
            // $meetingCityList->where('city_list.id', $data['lead']['meeting_city_id']);
            // $meetingCityList = $meetingCityList->first();

            $meetingCityList['text'] = '';
            $data['lead']['meeting_city'] = '';
            // if ($meetingCityList) {
            //     $meetingCityList['text'] = $meetingCityList->city_list_name . ', ' . $meetingCityList->state_list_name . ', India';
            //     $data['lead']['meeting_city'] = $meetingCityList->text;
            // }

            if ($data['lead']['site_stage'] != 0) {
                $CRMSettingStageOfSite = CRMSettingStageOfSite::select('id', 'name as text');
                $CRMSettingStageOfSite->where('crm_setting_stage_of_site.id', $data['lead']['site_stage']);
                $CRMSettingStageOfSite = $CRMSettingStageOfSite->first();
                if ($CRMSettingStageOfSite) {
                    $data['lead']['site_stage'] = $CRMSettingStageOfSite->text;
                } else {
                    $data['lead']['site_stage'] = '';
                }
            } else {
                $data['lead']['site_stage'] = '';
            }

            if ($data['lead']['site_type'] != 0) {
                $CRMSettingSiteType = CRMSettingSiteType::select('id', 'name as text');
                $CRMSettingSiteType->where('crm_setting_site_type.id', $data['lead']['site_type']);
                $CRMSettingSiteType = $CRMSettingSiteType->first();
                if ($CRMSettingSiteType) {
                    $data['lead']['site_type'] = $CRMSettingSiteType->text;
                } else {
                    $data['lead']['site_type'] = '';
                }
            } else {
                $data['lead']['site_type'] = '';
            }

            if ($data['lead']['bhk'] != 0) {
                $CRMSettingBHK = CRMSettingBHK::select('id', 'name as text');
                $CRMSettingBHK->where('crm_setting_bhk.id', $data['lead']['bhk']);
                $CRMSettingBHK = $CRMSettingBHK->first();
                if ($CRMSettingBHK) {
                    $data['lead']['bhk'] = $CRMSettingBHK->text;
                } else {
                    $data['lead']['bhk'] = '';
                }
            } else {
                $data['lead']['bhk'] = '';
            }

            if ($data['lead']['want_to_cover'] != 0) {
                $CRMSettingWantToCover = CRMSettingWantToCover::find(explode(',', $data['lead']['want_to_cover']));
                if ($CRMSettingWantToCover) {
                    $want_to_cover = '';
                    foreach ($CRMSettingWantToCover as $key => $value) {
                        $want_to_cover .= $value['name'] . ', ';
                    }
                    $data['lead']['want_to_cover'] = rtrim($want_to_cover, ', ');
                }
            } else {
                $data['lead']['want_to_cover'] = '';
            }

            if ($data['lead']['competitor'] != 0) {
                $CRMSettingCompetitors = CRMSettingCompetitors::find(explode(',', $data['lead']['competitor']));
                if ($CRMSettingCompetitors) {
                    $competitor = '';
                    foreach ($CRMSettingCompetitors as $key => $value) {
                        $competitor .= $value['name'] . ', ';
                    }
                    $data['lead']['competitor'] = rtrim($competitor, ', ');
                }
            } else {
                $data['lead']['competitor'] = '';
            }

            if ($data['lead']['sub_status'] != 0) {
                $CRMSettingSubStatus = CRMSettingSubStatus::select('name');
                $CRMSettingSubStatus->where('crm_setting_sub_status.id', $data['lead']['sub_status']);
                $CRMSettingSubStatus = $CRMSettingSubStatus->first();
                if ($CRMSettingSubStatus) {
                    $data['lead']['sub_status'] = $CRMSettingSubStatus->name;
                } else {
                    $data['lead']['sub_status'] = '';
                }
            } else {
                $data['lead']['sub_status'] = '';
            }

            if ($data['lead']['tag'] != 0) {
                $CRMLeadDealTag = DB::table('tag_master');
                $CRMLeadDealTag->select('tag_master.id AS id', 'tag_master.tagname AS text');
                $CRMLeadDealTag->where('tag_master.isactive', 1);
                $CRMLeadDealTag->where('tag_master.tag_type', 201);
                $CRMLeadDealTag->whereIn('tag_master.id', explode(',', $data['lead']['tag']));
                $data['lead']['tag'] = $CRMLeadDealTag->get();
            } else {
                $data['lead']['tag'] = '';
            }

            $is_bill_upload = 0;
            $LeadContact_List = LeadContact::query();
            $LeadContact_List->select('lead_contacts.*');
            $LeadContact_List->where('lead_contacts.lead_id', $data['lead']['id']);
            $LeadContact_List->where('lead_contacts.status', 1);
            $LeadContact_List = $LeadContact_List->get();
            foreach ($LeadContact_List as $key => $value) {
                if ($value['type'] == 202 || $value['type'] == 302) {
                    $is_bill_upload = 1;
                    break;
                } else {
                    $is_bill_upload = 0;
                }
            }

            $LeadClosingDate = LeadClosing::query();
            $LeadClosingDate->select('lead_closing.*');
            $LeadClosingDate->where('lead_closing.lead_id', $data['lead']['id']);
            $LeadClosingDate = $LeadClosingDate->get();

            $TeleSalesTask = LeadTask::select('lead_tasks.*', 'users.first_name', 'users.last_name');
            $TeleSalesTask->where('lead_tasks.lead_id', $data['lead']['id']);
            $TeleSalesTask->where('lead_tasks.is_autogenerate', 1);
            $TeleSalesTask->where('users.type', 9);
            $TeleSalesTask->leftJoin('users', 'users.id', '=', 'lead_tasks.assign_to');
            $TeleSalesTask = $TeleSalesTask->get();
            $TeleSalesTask = json_decode(json_encode($TeleSalesTask), true);

            $TeleSalesCall = [];
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

            $ServiceUserTask = LeadTask::select('lead_tasks.*', 'users.first_name', 'users.last_name');
            $ServiceUserTask->where('lead_tasks.lead_id', $data['lead']['id']);
            $ServiceUserTask->where('lead_tasks.is_autogenerate', 1);
            $ServiceUserTask->where('users.type', 11);
            $ServiceUserTask->leftJoin('users', 'users.id', '=', 'lead_tasks.assign_to');
            $ServiceUserTask = $ServiceUserTask->get();

            $ServiceUserTask = json_decode(json_encode($ServiceUserTask), true);
            foreach ($ServiceUserTask as $key => $value) {
                $ServiceUserTask[$key]['created_at'] = date('d/m/Y g:i A', strtotime($value['created_at']));
            }

            $LeadUpdate = LeadUpdate::query();
            $LeadUpdate->select('lead_updates.id', 'lead_updates.message', 'lead_updates.user_id', 'lead_updates.task', 'lead_updates.task_title', 'users.first_name', 'users.last_name', 'lead_updates.created_at');
            $LeadUpdate->leftJoin('users', 'users.id', '=', 'lead_updates.user_id');
            $LeadUpdate->where('lead_updates.lead_id', $data['lead']['id']);
            $LeadUpdate->orderBy('lead_updates.id', 'desc');
            $LeadUpdate->limit(5);
            $LeadUpdate = $LeadUpdate->get();
            $LeadUpdate = json_encode($LeadUpdate);
            $LeadUpdate = json_decode($LeadUpdate, true);


            foreach ($LeadUpdate as $key => $value) {
                $LeadUpdate[$key]['message'] = strip_tags($value['message']);

                $LeadUpdate[$key]['created_at'] = convertDateTime($value['created_at']);
                $LeadUpdate[$key]['date'] = convertDateAndTime($value['created_at'], "date");
                $LeadUpdate[$key]['time'] = convertDateAndTime($value['created_at'], "time");
            }


            $LeadContact_List = LeadContact::query();
            $LeadContact_List->select('crm_setting_contact_tag.name as tag_name', 'lead_contacts.*');
            $LeadContact_List->leftJoin('crm_setting_contact_tag', 'crm_setting_contact_tag.id', '=', 'lead_contacts.contact_tag_id');
            $LeadContact_List->where('lead_contacts.lead_id', $data['lead']['id']);
            $LeadContact_List->where('lead_contacts.status', 1);
            $LeadContact_List->orderBy('lead_contacts.id', 'desc');
            $LeadContact_List->limit(5);
            $LeadContact_List = $LeadContact_List->get();
            $LeadContact_List = json_encode($LeadContact_List);
            $LeadContact_List = json_decode($LeadContact_List, true);

            foreach ($LeadContact_List as $contact_key => $contact_value) {
                $LeadContact_List[$contact_key]['firm_name'] = '';
                if($contact_value['type_detail'] != null || $contact_value['type_detail'] != 0 || $contact_value['type_detail'] != ''){
                    $lst_detail = explode("-",$contact_value['type_detail']);
                    if(count($lst_detail) == 3){
                        if($lst_detail[1] == 202){
                            $architect = Architect::select('firm_name')->where('user_id', $lst_detail[2])->first();
                            if($architect){
                                $LeadContact_List[$contact_key]['firm_name'] = $architect->firm_name;
                            }

                        }elseif (isset(getChannelPartners()[$lst_detail[1]]['short_name'])) {
                            $chnnel_partener = ChannelPartner::select('firm_name')->where('user_id', $lst_detail[2])->first();
                            if($chnnel_partener){
                                $LeadContact_List[$contact_key]['firm_name'] = $chnnel_partener->firm_name;
                            }
                        }
                    }
                }
            }

            $LeadCall = LeadCall::query();
            $LeadCall->select('lead_calls.*', 'users.first_name', 'users.last_name');
            $LeadCall->where('lead_calls.lead_id', $data['lead']['id']);
            $LeadCall->where('is_closed', 0);
            $LeadCall->leftJoin('users', 'users.id', '=', 'lead_calls.user_id');
            $LeadCall->orderBy('lead_calls.id', 'desc');
            $LeadCall = $LeadCall->get();
            $LeadCall = json_encode($LeadCall);
            $LeadCall = json_decode($LeadCall, true);
            foreach ($LeadCall as $key => $value) {

                $LeadCall[$key]['date'] = convertDateAndTime($value['call_schedule'], "date");
                $LeadCall[$key]['time'] = convertDateAndTime($value['call_schedule'], "time");

                $LeadCall[$key]['tooltip_message'] = '';
                if($value['reference_id'] != 0){
                    $LeadRef = LeadTask::find($value['reference_id']);
                    if($LeadRef->is_autogenerate == 1){
                        if($LeadRef->assign_to == Auth::user()->id){
                            $LeadCall[$key]['is_reference'] = 1;
                        } else {
                            $LeadCall[$key]['is_reference'] = 2;
                            $user_type = User::find($LeadRef->assign_to)->type;
                            if($user_type == 1){
                                $LeadCall[$key]['tooltip_message'] = 'CRE User & Company Admin Edit Only';
                            } else if($user_type == 9){
                                $LeadCall[$key]['tooltip_message'] = 'TeleSales User Edit Only';
                            } else if($user_type == 11){
                                $LeadCall[$key]['tooltip_message'] = 'Service User Edit Only';
                            }
                        }
                    } else {
                        $LeadCall[$key]['is_reference'] = 0;
                    }
                } else {
                    $LeadCall[$key]['is_reference'] = 0;
                }

                $ContactName = LeadContact::select('lead_contacts.id', 'lead_contacts.first_name', 'lead_contacts.last_name', DB::raw("CONCAT(lead_contacts.first_name,' ',lead_contacts.last_name) AS text"));
                $ContactName->where('lead_contacts.id', $value['contact_name']);
                $ContactName = $ContactName->first();
                if ($ContactName) {
                    $LeadCall[$key]['contact_name'] = $ContactName->text;
                } else {
                    $LeadCall[$key]['contact_name'] = "";
                }
            }



            $LeadCallClosed = LeadCall::query();
            $LeadCallClosed->select('lead_calls.*', 'users.first_name', 'users.last_name');
            $LeadCallClosed->where('lead_calls.lead_id', $data['lead']['id']);
            $LeadCallClosed->where('is_closed', 1);
            $LeadCallClosed->leftJoin('users', 'users.id', '=', 'lead_calls.user_id');
            $LeadCallClosed->orderBy('lead_calls.closed_date_time', 'desc');
            $LeadCallClosed = $LeadCallClosed->get();
            $LeadCallClosed = json_encode($LeadCallClosed);
            $LeadCallClosed = json_decode($LeadCallClosed, true);
            foreach ($LeadCallClosed as $key => $value) {
                $LeadCallClosed[$key]['date'] = convertDateAndTime($value['closed_date_time'], "date");
                $LeadCallClosed[$key]['time'] = convertDateAndTime($value['closed_date_time'], "time");
                $ContactName = LeadContact::select('lead_contacts.id', 'lead_contacts.first_name', 'lead_contacts.last_name', DB::raw("CONCAT(lead_contacts.first_name,' ',lead_contacts.last_name) AS text"));
                $ContactName->where('lead_contacts.id', $value['contact_name']);
                $ContactName = $ContactName->first();
                if ($ContactName) {
                    $LeadCallClosed[$key]['contact_name'] = $ContactName->text;
                } else {
                    $LeadCallClosed[$key]['contact_name'] = "";
                }
            }

            $LeadMeeting = LeadMeeting::query();
            $LeadMeeting->select('lead_meetings.*', 'users.first_name', 'users.last_name');
            $LeadMeeting->where('lead_meetings.lead_id', $data['lead']['id']);
            $LeadMeeting->where('is_closed', 0);
            $LeadMeeting->leftJoin('users', 'users.id', '=', 'lead_meetings.user_id');
            $LeadMeeting->orderBy('lead_meetings.id', 'desc');
            $LeadMeeting = $LeadMeeting->get();
            $LeadMeeting = json_encode($LeadMeeting);
            $LeadMeeting = json_decode($LeadMeeting, true);
            foreach ($LeadMeeting as $key => $value) {
                $LeadMeeting[$key]['date'] = convertDateAndTime($value['meeting_date_time'], "date");
                $LeadMeeting[$key]['time'] = convertDateAndTime($value['meeting_date_time'], "time");

                $LeadMeetingTitle = CRMSettingMeetingTitle::select('name')->where('id', $value['title_id'])->first();

                if ($LeadMeetingTitle) {
                    $LeadMeeting[$key]['title_name'] = $LeadMeetingTitle->name;
                } else {
                    $LeadMeeting[$key]['title_name'] = $LeadMeetingTitle->name;
                }


                $LeadMeetingParticipant = LeadMeetingParticipant::where('meeting_id', $value['id'])->orderby('id', 'asc')->get();
                $LeadMeetingParticipant = json_decode(json_encode($LeadMeetingParticipant), true);

                $UsersId = array();
                $ContactIds = array();
                foreach ($LeadMeetingParticipant as $sales_key => $value) {
                    if ($value['type'] == "users") {
                        $UsersId[] = $value['reference_id'];
                    }
                }

                foreach ($LeadMeetingParticipant as $contact_key => $value) {
                    if ($value['type'] == "lead_contacts") {
                        $ContactIds[] = $value['reference_id'];
                    }
                }

                $UserResponse = "";
                if (count($ContactIds) > 0) {
                    $LeadContact = LeadContact::select('lead_contacts.id', 'lead_contacts.first_name', 'lead_contacts.last_name', DB::raw("CONCAT(lead_contacts.first_name,' ',lead_contacts.last_name) AS full_name"));
                    $LeadContact->whereIn('lead_contacts.id', $ContactIds);
                    $LeadContact = $LeadContact->get();
                    if (count($LeadContact) > 0) {
                        foreach ($LeadContact as $User_key => $User_value) {
                            $UserResponse .= "Contact - " . $User_value['full_name'] . '<br>';
                        }
                    }
                }

                if (count($UsersId) > 0) {
                    $User = User::select('users.id', 'users.type', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
                    $User->whereIn('users.id', $UsersId);
                    $User = $User->get();
                    $getAllUserTypes = getAllUserTypes();
                    if (count($User) > 0) {
                        foreach ($User as $User_key => $User_value) {
                            $UserResponse .= $getAllUserTypes[$User_value['type']]['short_name'] . " - " . $User_value['full_name'] . '<br>';
                        }
                    }
                }



                if ($UserResponse) {
                    $LeadMeeting[$key]['meeting_participant'] = $UserResponse;
                } else {
                    $LeadMeeting[$key]['meeting_participant'] = "";
                }
            }

            $LeadMeetingClosed = LeadMeeting::query();
            $LeadMeetingClosed->select('lead_meetings.*', 'users.first_name', 'users.last_name');
            $LeadMeetingClosed->where('lead_meetings.lead_id', $data['lead']['id']);
            $LeadMeetingClosed->where('is_closed', 1);
            $LeadMeetingClosed->leftJoin('users', 'users.id', '=', 'lead_meetings.user_id');
            $LeadMeetingClosed->orderBy('lead_meetings.closed_date_time', 'desc');
            $LeadMeetingClosed = $LeadMeetingClosed->get();
            $LeadMeetingClosed = json_encode($LeadMeetingClosed);
            $LeadMeetingClosed = json_decode($LeadMeetingClosed, true);
            foreach ($LeadMeetingClosed as $key => $value) {
                $LeadMeetingClosed[$key]['date'] = convertDateAndTime($value['closed_date_time'], "date");
                $LeadMeetingClosed[$key]['time'] = convertDateAndTime($value['closed_date_time'], "time");

                $LeadMeetingTitle = CRMSettingMeetingTitle::select('name')->where('id', $value['title_id'])->first();
                if ($LeadMeetingTitle) {
                    $LeadMeetingClosed[$key]['title_name'] = $LeadMeetingTitle->name;
                } else {
                    $LeadMeetingClosed[$key]['title_name'] = " ";
                }

                $LeadMeetingParticipant = LeadMeetingParticipant::where('meeting_id', $value['id'])->orderby('id', 'asc')->get();
                $LeadMeetingParticipant = json_decode(json_encode($LeadMeetingParticipant), true);

                $UsersId = array();
                $ContactIds = array();
                foreach ($LeadMeetingParticipant as $sales_key => $value) {
                    if ($value['type'] == "users") {
                        $UsersId[] = $value['reference_id'];
                    }
                }

                foreach ($LeadMeetingParticipant as $contact_key => $value) {
                    if ($value['type'] == "lead_contacts") {
                        $ContactIds[] = $value['reference_id'];
                    }
                }

                $UserResponse = "";
                if (count($ContactIds) > 0) {
                    $LeadContact = LeadContact::select('lead_contacts.id', DB::raw("CONCAT(lead_contacts.first_name,' ',lead_contacts.last_name) AS full_name"));
                    $LeadContact->whereIn('lead_contacts.id', $ContactIds);
                    $LeadContact = $LeadContact->get();
                    if (count($LeadContact) > 0) {
                        foreach ($LeadContact as $User_key => $User_value) {
                            $UserResponse .= "Contact - " . $User_value['full_name'] . '<br>';
                        }
                    }
                }

                if (count($UsersId) > 0) {
                    $User = User::select('users.id', 'users.type', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
                    $User->whereIn('users.id', $UsersId);
                    $User = $User->get();
                    if (count($User) > 0) {
                        foreach ($User as $User_key => $User_value) {
                            $UserResponse .= getAllUserTypes()[$User_value['type']]['short_name'] . " - " . $User_value['full_name'] . '<br>';
                        }
                    }
                }

                if ($UserResponse) {
                    $LeadMeetingClosed[$key]['meeting_participant'] = $UserResponse;
                } else {
                    $LeadMeetingClosed[$key]['meeting_participant'] = "";
                }
            }

            $LeadTask = LeadTask::query();
            $LeadTask->select('lead_tasks.*', 'users.first_name', 'users.last_name');
            $LeadTask->where('lead_tasks.lead_id', $data['lead']['id']);
            $LeadTask->where('is_closed', 0);
            $LeadTask->leftJoin('users', 'users.id', '=', 'lead_tasks.user_id');
            $LeadTask->orderBy('lead_tasks.id', 'desc');
            $LeadTask = $LeadTask->get();
            $LeadTask = json_encode($LeadTask);
            $LeadTask = json_decode($LeadTask, true);
            foreach ($LeadTask as $key => $value) {
                $LeadTask[$key]['date'] = convertDateAndTime($value['due_date_time'], "date");
                $LeadTask[$key]['time'] = convertDateAndTime($value['due_date_time'], "time");

                $LeadTask[$key]['tooltip_message'] = '';
                if($value['is_autogenerate'] == 1){
                    if($value['assign_to'] != Auth::user()->id){
                        $user_type = User::find($value['assign_to'])->type;
                        if($user_type == 1){
                            $LeadTask[$key]['tooltip_message'] = 'CRE User & Company Admin Edit Only';
                        } else if($user_type == 9){
                            $LeadTask[$key]['tooltip_message'] = 'TeleSales User Edit Only';
                        } else if($user_type == 11){
                            $LeadTask[$key]['tooltip_message'] = 'Service User Edit Only';
                        }
                    }
                }

                $Taskowner = User::select('users.id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"));
                // $Taskowner->where('users.status', 1);
                $Taskowner->where('users.id', $value['assign_to']);
                $Taskowner = $Taskowner->first();

                if ($Taskowner) {
                    $LeadTask[$key]['task_owner'] = $Taskowner->text;
                } else {
                    $LeadTask[$key]['task_owner'] = " ";
                }
            }


            $LeadTaskClosed = LeadTask::query();
            $LeadTaskClosed->select('lead_tasks.*', 'users.first_name', 'users.last_name');
            $LeadTaskClosed->where('lead_tasks.lead_id', $data['lead']['id']);
            $LeadTaskClosed->where('is_closed', 1);
            $LeadTaskClosed->leftJoin('users', 'users.id', '=', 'lead_tasks.user_id');
            $LeadTaskClosed->orderBy('lead_tasks.closed_date_time', 'desc');
            $LeadTaskClosed = $LeadTaskClosed->get();
            $LeadTaskClosed = json_encode($LeadTaskClosed);
            $LeadTaskClosed = json_decode($LeadTaskClosed, true);
            foreach ($LeadTaskClosed as $key => $value) {
                $LeadTaskClosed[$key]['date'] = convertDateAndTime($value['closed_date_time'], "date");
                $LeadTaskClosed[$key]['time'] = convertDateAndTime($value['closed_date_time'], "time");

                $Taskowner = User::select('users.id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"));
                // $Taskowner->where('users.status', 1);
                $Taskowner->where('users.id', $value['assign_to']);
                $Taskowner = $Taskowner->first();

                if ($Taskowner) {
                    $LeadTaskClosed[$key]['task_owner'] = $Taskowner->text;
                } else {
                    $LeadTaskClosed[$key]['task_owner'] = " ";
                }
            }

            $countCall = count($LeadCall);
            $countTask = count($LeadTask);
            $countMeeting = count($LeadMeeting);
            $maxOpenAction = max($countCall, $countTask, $countMeeting);

            $countCallClosed = count($LeadCallClosed);
            $countTaskClosed = count($LeadTaskClosed);
            $countMeetingClosed = count($LeadMeetingClosed);
            $maxClosedAction = max($countCallClosed, $countTaskClosed, $countMeetingClosed);

            $CompanyAdminTask = LeadTask::select('lead_tasks.*', 'users.first_name', 'users.last_name');
            $CompanyAdminTask->where('lead_tasks.lead_id', $data['lead']['id']);
            $CompanyAdminTask->where('lead_tasks.is_autogenerate', 1);
            $CompanyAdminTask->whereIn('users.type', array(1,13));
            $CompanyAdminTask->leftJoin('users', 'users.id', '=', 'lead_tasks.assign_to');
            $CompanyAdminTask = $CompanyAdminTask->get();

            $CompanyAdminTask = json_decode(json_encode($CompanyAdminTask), true);
            foreach ($CompanyAdminTask as $key => $value) {
                $CompanyAdminTask[$key]['created_at'] = date('d/m/Y g:i A', strtotime($value['created_at']));
            }

            $LeadBillSummary_claimed = LeadFile::query();
            $LeadBillSummary_claimed->where('lead_files.lead_id', $data['lead']['id']);
            $LeadBillSummary_claimed->where('lead_files.file_tag_id', 3);
            $LeadBillSummary_claimed->where('lead_files.status', 100);
            $LeadBillSummary_claimed = $LeadBillSummary_claimed->count();

            $LeadBillSummary_query = LeadFile::query();
            $LeadBillSummary_query->where('lead_files.lead_id', $data['lead']['id']);
            $LeadBillSummary_query->where('lead_files.file_tag_id', 3);
            $LeadBillSummary_query->where('lead_files.status', 101);
            $LeadBillSummary_query = $LeadBillSummary_query->count();

            $LeadBillSummary_laps = LeadFile::query();
            $LeadBillSummary_laps->where('lead_files.lead_id', $data['lead']['id']);
            $LeadBillSummary_laps->where('lead_files.file_tag_id', 3);
            $LeadBillSummary_laps->where('lead_files.status', 102);
            $LeadBillSummary_laps = $LeadBillSummary_laps->count();

            $LeadQuotation = Wltrn_Quotation::query();
            $LeadQuotation->select('id', 'quotgroup_id', 'quot_date', 'quot_no_str', 'isfinal', 'quotation_file', 'quottype_id', 'net_amount');
            $LeadQuotation->where('wltrn_quotation.inquiry_id', $data['lead']['id']);
            $LeadQuotation->where('wltrn_quotation.status', 3);
            $LeadQuotation->orderBy('wltrn_quotation.id', 'desc');
            $LeadQuotation = $LeadQuotation->get();
            $LeadQuotation = json_decode(json_encode($LeadQuotation), true);

            $LeadFile = LeadFile::query();
            $LeadFile->select('crm_setting_file_tag.name as tag_name', 'lead_files.*', 'users.first_name', 'users.last_name');
            $LeadFile->leftJoin('crm_setting_file_tag', 'crm_setting_file_tag.id', '=', 'lead_files.file_tag_id');
            $LeadFile->leftJoin('users', 'users.id', '=', 'lead_files.uploaded_by');
            $LeadFile->where('lead_files.lead_id', $data['lead']['id']);
            $LeadFile->limit(5);
            $LeadFile->orderBy('lead_files.id', 'desc');
            $LeadFile = $LeadFile->get();
            $LeadFile = json_encode($LeadFile);
            $LeadFile = json_decode($LeadFile, true);

            $active_bill_count = LeadFile::query()->where('lead_files.lead_id', $data['lead']['id'])->where('lead_files.file_tag_id', 3)->where('lead_files.is_active', 1)->count();
            foreach ($LeadFile as $key => $value) {

                $fileHtml = '';
                foreach (explode(",", $value['name']) as $filekey => $filevalue) {
                    $fileHtml .= '<a class="ms-1" target="_blank" href="' . getSpaceFilePath($filevalue) . '"><i class="bx bxs-file-pdf"></i></a>';
                }
                $LeadFile[$key]['download'] = $fileHtml;


                $LeadFile[$key]['created_at'] = convertDateTime($value['created_at']);
            }

            $quotation = array();
            if ($LeadQuotation) {
                foreach ($LeadQuotation as $key => $value) {
                    $quotation_details = array();

                    $whitelion_details = Wltrn_QuotItemdetail::query();
                    $whitelion_details->selectRaw('SUM(wltrn_quot_itemdetails.net_amount) as net_amount');
                    $whitelion_details->where([
                        ['wltrn_quot_itemdetails.quot_id', $value['id']],
                        ['wltrn_quot_itemdetails.quotgroup_id', $value['quotgroup_id']],
                        ['wltrn_quot_itemdetails.isactiveroom', 1],
                        ['wltrn_quot_itemdetails.isactiveboard', 1],
                    ]);
                    $whitelion_details->whereIn('wltrn_quot_itemdetails.itemgroup_id', [1, 3]);
                    $whitelion_total_amount = $whitelion_details->first();

                    $billing_details = Wltrn_QuotItemdetail::query();
                    $billing_details->selectRaw('SUM(wlmst_item_prices.mrp-(wlmst_item_prices.mrp*wlmst_item_prices.channel_partners_discount/100)) as total');
                    $billing_details->where([
                        ['wltrn_quot_itemdetails.quot_id', $value['id']],
                        ['wltrn_quot_itemdetails.quotgroup_id', $value['quotgroup_id']],
                        ['wltrn_quot_itemdetails.isactiveroom', 1],
                        ['wltrn_quot_itemdetails.isactiveboard', 1],
                    ]);
                    $billing_details->leftJoin('wlmst_item_prices', 'wlmst_item_prices.id', '=', 'wltrn_quot_itemdetails.item_price_id');
                    $billing_details->whereIn('wltrn_quot_itemdetails.itemgroup_id', [1, 3]);
                    $LeadQuotationBilling = $billing_details->first();

                    $others_details = Wltrn_QuotItemdetail::query();
                    $others_details->selectRaw('SUM(wltrn_quot_itemdetails.net_amount) as net_amount');
                    $others_details->where([
                        ['wltrn_quot_itemdetails.quot_id', $value['id']],
                        ['wltrn_quot_itemdetails.quotgroup_id', $value['quotgroup_id']],
                        ['wltrn_quot_itemdetails.isactiveroom', 1],
                        ['wltrn_quot_itemdetails.isactiveboard', 1],
                    ]);
                    $others_details->whereIn('wltrn_quot_itemdetails.itemgroup_id', [2, 4]);
                    $others_total_amount = $others_details->first();

                    $total_details = Wltrn_QuotItemdetail::query();
                    $total_details->selectRaw('SUM(wltrn_quot_itemdetails.net_amount) as net_amount');
                    $total_details->where([
                        ['wltrn_quot_itemdetails.quot_id', $value['id']],
                        ['wltrn_quot_itemdetails.quotgroup_id', $value['quotgroup_id']],
                        ['wltrn_quot_itemdetails.isactiveroom', 1],
                        ['wltrn_quot_itemdetails.isactiveboard', 1],
                    ]);
                    // $total_details->whereIn('wltrn_quot_itemdetails.itemgroup_id', [2, 4]);
                    $total_total_amount = $total_details->first();

                    if ($whitelion_total_amount) {
                        $whitelion_total_amount = $whitelion_total_amount->net_amount;
                    } else {
                        $whitelion_total_amount = 0;
                    }
                    if ($LeadQuotationBilling) {
                        $LeadQuotationBilling = $LeadQuotationBilling->total;
                    } else {
                        $LeadQuotationBilling = 0;
                    }

                    if ($others_total_amount) {
                        $others_total_amount = $others_total_amount->net_amount;
                    } else {
                        $others_total_amount = 0;
                    }

                    if ($total_total_amount) {
                        if ($value['quottype_id'] == 4) {

                            $total_total_amount = $value['net_amount'];
                        } else {
                            $total_total_amount = $total_total_amount->net_amount;
                        }
                    } else {
                        $total_total_amount = 0;
                    }

                    $quotation_details['quot_id'] = $value['id'];
                    $quotation_details['quot_groupid'] = $value['quotgroup_id'];
                    $quotation_details['quot_date'] = $value['quot_date'];
                    $quotation_details['isfinal'] = $value['isfinal'];
                    $quotation_details['quot_version'] = $value['quot_no_str'];
                    $quotation_details['quotation_file'] = $value['quotation_file'];
                    $quotation_details['whitelion_amount'] = numCommaFormat($whitelion_total_amount);
                    $quotation_details['other_amount'] = numCommaFormat($others_total_amount);
                    $quotation_details['billing_amount'] = numCommaFormat($LeadQuotationBilling);
                    $quotation_details['total_amount'] = numCommaFormat($total_total_amount);
                    array_push($quotation, $quotation_details);
                }
            } else {
                $quotation_details = array();
                if (($data['lead']['quotation'] != null) || ($data['lead']['quotation'] != '') || ($data['lead']['quotation'] != 0)) {
                    $quotation_details['quot_id'] = 0;
                    $quotation_details['quot_groupid'] = 0;
                    $quotation_details['whitelion_amount'] = 0;
                    $quotation_details['other_amount'] = 0;
                    $quotation_details['billing_amount'] = 0;
                    $quotation_details['total_amount'] = (int) $data['lead']['quotation'];
                    $quotation_details['quot_date'] = "";
                    $quotation_details['isfinal'] = "-";
                    $quotation_details['quot_version'] = " ";
                    $quotation_details['quotation_file'] = "";
                } else {
                    $quotation_details['quot_id'] = 0;
                    $quotation_details['quot_groupid'] = 0;
                    $quotation_details['whitelion_amount'] = 0;
                    $quotation_details['billing_amount'] = 0;
                    $quotation_details['other_amount'] = 0;
                    $quotation_details['total_amount'] = 0;
                    $quotation_details['quot_date'] = "";
                    $quotation_details['isfinal'] = "-";
                    $quotation_details['quot_version'] = " ";
                    $quotation_details['quotation_file'] = "";
                }
                array_push($quotation, $quotation_details);
            }

            $response = successRes('Get List');
            $data['lead_id'] = $request->id;
            $data['lead_status'] = getLeadStatus();
            $data['current_status'] = $data['lead']['status'];
            $data['closing_date'] = $LeadClosingDate;
            $data['closing_date_count'] = count($LeadClosingDate);
            $data['telesales_task'] = $TeleSalesTask;
            $data['telesales_call'] = $TeleSalesCall;
            $data['serviceuser_task'] = $ServiceUserTask;
            $data['companyadmin_task'] = $CompanyAdminTask;
            $data['contacts'] = $LeadContact_List;
            $data['calls'] = $LeadCall;
            $data['tasks'] = $LeadTask;
            $data['meetings'] = $LeadMeeting;
            $data['max_open_actions'] = $maxOpenAction;
            $data['calls_closed'] = $LeadCallClosed;
            $data['tasks_closed'] = $LeadTaskClosed;
            $data['meetings_closed'] = $LeadMeetingClosed;            
            $data['max_close_actions'] = $maxClosedAction;
            $data['updates'] = $LeadUpdate;
            $data['LeadBillSummary_claimed'] = $LeadBillSummary_claimed;
            $data['LeadBillSummary_query'] = $LeadBillSummary_query;
            $data['LeadBillSummary_laps'] = $LeadBillSummary_laps;

            $data['telesales_status'] = verificationStatus()[$data['lead']['telesales_verification']]['telesales_name'];
            $data['service_status'] = verificationStatus()[$data['lead']['service_verification']]['service_name'];
            $data['company_status'] = verificationStatus()[$data['lead']['companyadmin_verification']]['company_admin_name'];

            $data['is_bill_upload'] = $is_bill_upload;

            $data['files'] = $LeadFile;
            $data['quotation'] = $quotation;

            $LeadTimeline = LeadTimeline::select('lead_timeline.*', 'users.first_name', 'users.last_name')
                ->leftJoin('users', 'users.id', '=', 'lead_timeline.user_id')
                ->where('lead_id', $data['lead']['id'])
                ->orderBy('id', 'desc')
                ->get();

            $LeadTimeline = json_encode($LeadTimeline);
            $LeadTimeline = json_decode($LeadTimeline, true);

            $repeated_date = '';
            foreach ($LeadTimeline as $key => $value) {
                $date = convertDateAndTime($value['created_at'], 'date');
                if ($repeated_date == $date) {
                    $LeadTimeline[$key]['date'] = 0;
                } else {
                    $repeated_date = $date;
                    $LeadTimeline[$key]['date'] = convertDateAndTime($value['created_at'], 'date');
                }
                $LeadTimeline[$key]['created_date'] = convertDateAndTime($value['created_at'], 'date');
                $LeadTimeline[$key]['time'] = convertDateAndTime($value['created_at'], 'time');
                $LeadTimeline[$key]['created_at'] = convertDateTime($value['created_at']);
                $LeadTimeline[$key]['updated_at'] = convertDateTime($value['updated_at']);
            }

            $data['timeline'] = $LeadTimeline;

            $response['view'] = view('crm/reward/detail', compact('data'))->render();
            $response['data'] = $data;
        } else {
            $response = errorRes('Lead Data Not Available');
        }

        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }

    public function getTaskDetail(Request $request)
    {
        if ($request->id != 0 && $request->id != '' && $request->id != null) {
            $Task = LeadTask::find($request->id);

            $Lead = Lead::select(DB::raw("CONCAT(leads.id,'-',leads.first_name,' ',leads.last_name) AS text"))
                ->where('id', $Task->lead_id)
                ->first()->text;
            $Task['lead_detail'] = $Lead;

            $Task['created_by'] = User::select(DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"))
                ->where('id', $Task->user_id)
                ->first()->text;

            $Task['due_date_time'] = date('d/m/Y g:i A', strtotime($Task->due_date_time));

            if ($Task['close_note'] == '') {
                $Task['close_note'] = '-';
            }

            if ($Task['additional_info'] == '') {
                $Task['additional_info'] = '-';
            } else {
                $Task['additional_info'] = CRMSettingAdditionalInfo::find($Task['additional_info'])->name;
            }

            if ($Task['closed_date_time'] == '') {
                $Task['closed_date_time'] = '-';
            } else {
                $Task['closed_date_time'] = date('d/m/Y g:i A', strtotime($Task->closed_date_time));
            }

            if ($Task['outcome_type'] == '') {
                $Task['outcome_type'] = '-';
            } else {
                if ($Task->outcome_type > 100) {
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
            $response = errorRes('Task Not Available');
        }

        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }

    public function getCallDetail(Request $request)
    {
        if ($request->id != 0 && $request->id != '' && $request->id != null) {
            $Call = LeadCall::find($request->id);

            $Lead = Lead::select(DB::raw("CONCAT(leads.id,'-',leads.first_name,' ',leads.last_name) AS text"))
                ->where('id', $Call->lead_id)
                ->first()->text;
            $Call['lead_detail'] = $Lead;

            $Call['created_by'] = User::select(DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"))
                ->where('id', $Call->user_id)
                ->first()->text;

            $Call['contact_name'] = LeadContact::select(DB::raw("CONCAT(lead_contacts.first_name,' ',lead_contacts.last_name) AS text"))
                ->where('id', $Call->contact_name)
                ->first()->text;

            $Call['call_schedule'] = date('d/m/Y g:i A', strtotime($Call->call_schedule));
            $Call['call_schedule'] = date('d/m/Y g:i A', strtotime($Call->call_schedule));

            if ($Call['close_note'] == '') {
                $Call['close_note'] = '-';
            }

            if ($Call['additional_info'] == '') {
                $Call['additional_info'] = '-';
            } else {
                $Call['additional_info'] = CRMSettingAdditionalInfo::find($Call['additional_info'])->name;
            }

            if ($Call['closed_date_time'] == '') {
                $Call['closed_date_time'] = '-';
            } else {
                $Call['closed_date_time'] = date('d/m/Y g:i A', strtotime($Call->closed_date_time));
            }

            if ($Call['outcome_type'] == '') {
                $Call['outcome_type'] = '-';
            } else {
                $Call['outcome_type'] = CRMSettingCallOutcomeType::find($Call->outcome_type)->name;
            }

            $Call = json_decode(json_encode($Call), true);
            $Call['created_at'] = date('d/m/Y g:i A', strtotime($Call['created_at']));

            $response = successRes();
            $response['data'] = $Call;
        } else {
            $response = errorRes('Call Not Available');
        }

        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }

    public function getCompanyAdminAllTask(Request $request)
    {
        $CompanyAdminTask = LeadTask::select('lead_tasks.*', 'users.first_name', 'users.last_name');
        $CompanyAdminTask->where('lead_tasks.lead_id', $request->lead_id);
        $CompanyAdminTask->where('lead_tasks.is_autogenerate', 1);
        $CompanyAdminTask->whereIn('users.type', array(1,13));
        $CompanyAdminTask->leftJoin('users', 'users.id', '=', 'lead_tasks.assign_to');
        $CompanyAdminTask = $CompanyAdminTask->get();

        $CompanyAdminTask = json_decode(json_encode($CompanyAdminTask), true);
        $viewData = [];
        $view = '';
        foreach ($CompanyAdminTask as $key => $value) {
            $CompanyAdminTask[$key]['created_at'] = date('d/m/Y g:i A', strtotime($value['created_at']));
            $view .= '<tr style="vertical-align: middle;">';
            $view .= '<td class="col-1" style="font-weight: 600;color: #27b50b;"><span class="badge badge-pill badge-soft-primary font-size-11">Task</span></td>';
            $view .= '<td class="col-4">' . $value['task'] . '</td>';
            if ($value['is_closed'] == 0) {
                $view .= '<td class="col-3 text-success" style="font-weight: 600;">Open</td>';
            } else {
                $view .= '<td class="col-3 text-danger" style="font-weight: 600;">Close</td>';
            }
            $view .= '<td class="col-3"><i class="bx bxs-user me-1"></i>' . $value['first_name'] . ' ' . $value['last_name'] . '<br>' . $CompanyAdminTask[$key]['created_at'] . '</td>';
            $view .= '<td class="col-1" style="font-size: x-large;"><i class="bx bxs-show"  onclick="TaskDetail(' . $value['id'] . ')"></i></td>';
            $view .= '</tr>';

            $viewData[$key] = [];
            $viewData[$key]['view'] = $view;
        }

        $response = successRes();
        $response['data'] = $view;

        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }
}
