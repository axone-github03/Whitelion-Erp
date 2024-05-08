<?php

namespace App\Http\Controllers\API\CRM\LEAD;

use App\Models\Lead;
use App\Models\User;
use App\Models\CityList;
use App\Models\LeadCall;
use App\Models\LeadFile;
use App\Models\LeadTask;
use App\Models\LeadSource;
use App\Models\LeadUpdate;
use App\Models\LeadClosing;
use App\Models\LeadContact;
use App\Models\SalePerson;
use App\Models\LeadMeeting;
use App\Models\LeadTimeline;
use App\Models\Wlmst_Client;
use Illuminate\Http\Request;
use App\Models\CRMSettingBHK;
use App\Models\LeadCompetitor;
use App\Models\Wltrn_Quotation;
use App\Models\CRMSettingSource;
use App\Models\CRMSettingFileTag;
use App\Models\CRMSettingCallType;
use App\Models\CRMSettingSiteType;
use Illuminate\Support\Facades\DB;
use App\Models\CRMSettingSubStatus;
use App\Http\Controllers\Controller;
use App\Models\CRMSettingContactTag;
use App\Models\CRMSettingSourceType;
use App\Models\Wltrn_QuotItemdetail;
use Illuminate\Support\Facades\Auth;
use App\Models\CRMSettingCompetitors;
use App\Models\CRMSettingMeetingType;
use App\Models\CRMSettingStageOfSite;
use App\Models\CRMSettingWantToCover;
use App\Models\CRMSettingMeetingTitle;
use App\Models\LeadMeetingParticipant;
use Illuminate\Database\QueryException;
use App\Models\CRMSettingCallOutcomeType;
use App\Models\CRMSettingTaskOutcomeType;
use Illuminate\Support\Facades\Validator;
use App\Models\CRMSettingMeetingOutcomeType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Hash;


class LeadController extends Controller
{
    public function __construct()
    {

        $this->middleware(function ($request, $next) {

            $tabCanAccessBy = array(0, 1, 2, 9, 11, 101, 102, 103, 104, 105, 202, 302, 12);

            if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
                $response = errorRes("You Don't Have An Access To This Page", 401);
                return response()->json($response, $response['status_code'])->header('Content-Type', 'application/json');
            }

            return $next($request);
        });
    }

    public function tableAjax(Request $request)
    {
        try {
            $isSalePerson = isSalePerson();
            if ($isSalePerson == 1) {
                $childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
            }

            $searchColumns = array(
                0 => 'leads.id',
                1 => 'leads.first_name',
                2 => 'leads.last_name',
                3 => 'leads.email',
                4 => 'leads.phone_number',
            );

            $selectColumns = array(
                0 => 'leads.id',
                1 => 'leads.id',
                2 => 'leads.first_name',
                3 => 'leads.phone_number',
                4 => 'leads.status',
                5 => 'leads.site_stage',
                6 => 'leads.closing_date_time',
                7 => 'leads.assigned_to',
                8 => 'leads.user_id',
                9 => 'leads.last_name',
                10 => 'lead_owner.first_name as lead_owner_first_name',
                11 => 'lead_owner.last_name  as lead_owner_last_name',
                12 => 'created_by.first_name as created_by_first_name',
                13 => 'created_by.last_name  as created_by_last_name',
                14 => 'leads.is_deal',
            );

            $query = Lead::query();

            $query->leftJoin('users as lead_owner', 'lead_owner.id', '=', 'leads.assigned_to');
            $query->leftJoin('users as created_by', 'created_by.id', '=', 'leads.user_id');

            if (isset($request->is_deal)) {
                if ($request->is_deal == 0) {
                    $query->where('leads.is_deal', 0);
                } else if ($request->is_deal == 1) {
                    $query->where('leads.is_deal', 1);
                }
            }
            if ($isSalePerson == 1) {
                $query->whereIn('leads.assigned_to', $childSalePersonsIds);
            }
            $query->select($selectColumns);
            $query->orderBy('leads.id', 'desc');

            $isFilterApply = 0;
            if (isset($request['search']['value'])) {
                $isFilterApply = 1;
                $search_value = $request['search']['value'];
                $query->where(function ($query) use ($search_value, $searchColumns) {

                    for ($i = 0; $i < count($searchColumns); $i++) {

                        if ($i == 0) {
                            $query->where($searchColumns[$i], 'like', "%" . $search_value . "%");
                        } else {
                            $query->orWhere($searchColumns[$i], 'like', "%" . $search_value . "%");
                        }
                    }
                });
            }

            $data = $query->get();
            $data = json_decode(json_encode($data), true);

            $viewData = array();
            $LeadStatus = getLeadStatus();
            foreach ($data as $key => $value) {

                $site_stage = '';
                $CRMSettingStageOfSite = CRMSettingStageOfSite::find($value['site_stage']);
                if ($CRMSettingStageOfSite) {
                    $site_stage = $CRMSettingStageOfSite->name;
                }

                $closing_date_time = $value['closing_date_time'];
                $closing_date_time = date('Y-m-d H:i:s', strtotime($closing_date_time . " +5 hours"));
                $closing_date_time = date('Y-m-d', strtotime($closing_date_time . " +30 minutes"));


                $viewData[$key] = array();
                $viewData[$key]['id'] = $value['id'];
                if ($value['is_deal'] == 0) {
                    $viewData[$key]['id_label'] = '#L'.$value['id'];
                } else if ($value['is_deal'] == 1) {
                    $viewData[$key]['id_label'] = '#D'.$value['id'];
                }
                $viewData[$key] = $value;
                $viewData[$key]['name'] = $value['first_name'] . " " . $value['last_name'];

                if ($value['status'] != 0) {
                    $LeadStatus = getLeadStatus()[$value['status']]['name'];
                    $viewData[$key]['lead_status_label'] = $LeadStatus;
                    $viewData[$key]['lead_status'] = $value['status'];
                } else {
                    $viewData[$key]['lead_status_label'] = "not define";
                    $viewData[$key]['lead_status'] = "not define";
                }

                $viewData[$key]['site_stage'] = $site_stage;
                $viewData[$key]['closing_date'] = $closing_date_time;
                $viewData[$key]['lead_owner'] = $value['lead_owner_first_name'] . " " . $value['lead_owner_last_name'];
                $viewData[$key]['created_by'] = $value['created_by_first_name'] . " " . $value['created_by_last_name'];
            }

            $response = successRes();
            $response['data'] = $viewData;

            
        } catch (QueryException $ex) {
            $response = errorRes();
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function tableAjaxNew(Request $request)
    {
        try {
            $isSalePerson = isSalePerson();
            $isReception = isReception();
            if ($isSalePerson == 1) {
                $childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
            }

            $searchColumns = array(
                0 => 'leads.id',
                1 => 'leads.first_name',
                2 => 'leads.last_name',
                3 => 'leads.email',
                4 => 'leads.phone_number',
            );

            $selectColumns = array(
                'leads.id',
                'leads.first_name',
                'leads.phone_number',
                'leads.status',
                'leads.site_stage',
                'leads.closing_date_time',
                'leads.assigned_to',
                'leads.user_id',
                'leads.last_name',
                'lead_owner.first_name as lead_owner_first_name',
                'lead_owner.last_name  as lead_owner_last_name',
                'created_by.first_name as created_by_first_name',
                'created_by.last_name  as created_by_last_name',
                'leads.is_deal',
            );

            $query = Lead::query();

            $query->leftJoin('users as lead_owner', 'lead_owner.id', '=', 'leads.assigned_to');
            $query->leftJoin('users as created_by', 'created_by.id', '=', 'leads.user_id');
            $query->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
            $query->leftJoin('lead_files', 'lead_files.lead_id', '=', 'leads.id');

            if (isset($request->is_deal)) {
                if(isArchitect() == 0 && isElectrician() == 0){
                    if ($request->is_deal == 0) {
                        $query->where('leads.is_deal', 0);
                    } else if ($request->is_deal == 1) {
                        $query->where('leads.is_deal', 1);
                    }
                }
            }

            $arr_where_clause = array();
            $arr_or_clause = array();
            if ($request->is_advance_filter == 1) {
                foreach (json_decode($request->advance_filter_data, true) as $key => $filt_value) {
                    $filter_value = '';
                    $source_type = '0';
                    if ($filt_value['clause'] == null || $filt_value['clause'] == '') {
                        $response = errorRes("Please Select Clause");
                        return response()->json($response)->header('Content-Type', 'application/json');
    
                    } else if ($filt_value['column'] == null || $filt_value['column'] == '') {
                        $response = errorRes("Please Select column");
                        return response()->json($response)->header('Content-Type', 'application/json');
    
                    } else if ($filt_value['condtion'] == null || $filt_value['condtion'] == '') {
                        $response = errorRes("Please Select condtion");
                        return response()->json($response)->header('Content-Type', 'application/json');
    
                    } else {
                        $column = getFilterColumnCRM()[$filt_value['column']];
                        $condtion = getFilterCondtionCRM()[$filt_value['condtion']];
                        if ($column['value_type'] == 'text') {
    
                            if ($filt_value['value_text'] == null || $filt_value['value_text'] == '') {
                                $response = errorRes("Please enter value");
                                return response()->json($response)->header('Content-Type', 'application/json');
                            } else {
                                $filter_value = $filt_value['value_text'];
                            }
    
                        } else if (($column['value_type'] == 'select') && ($condtion['value_type'] == 'single_select')) {
                            if ($column['code'] == "leads_source" && ($filt_value['value_source_type'] == null || $filt_value['value_source_type'] == '')) {
                                $response = errorRes("Please select value");
                                return response()->json($response)->header('Content-Type', 'application/json');
                            } else {
                                $source_type = $filt_value['value_source_type'];
                            }
                            if ($filt_value['value_select'] == null || $filt_value['value_select'] == '') {
                                $response = errorRes("Please select value");
                                return response()->json($response)->header('Content-Type', 'application/json');
                            } else {
                                $filter_value = $filt_value['value_select'];
                            }
    
                        } else if (($column['value_type'] == 'select') && ($condtion['value_type'] == 'multi_select')) {
                            if ($column['code'] == "leads_source" && ($filt_value['value_source_type'] == null || $filt_value['value_source_type'] == '')) {
                                $response = errorRes("Please select value");
                                return response()->json($response)->header('Content-Type', 'application/json');
                            } else {
                                $source_type = $filt_value['value_source_type'];
                            }
                            if (!isset($filt_value['value_multi_select']) && empty($filt_value['value_multi_select'])) {
                                $response = errorRes("Please select value");
                                return response()->json($response)->header('Content-Type', 'application/json');
                            } else {
                                $filter_value = $filt_value['value_multi_select'];
                            }
    
                        } else if (($column['value_type'] == 'date') && ($condtion['value_type'] == 'single_select')) {
    
                            if ($filt_value['value_date'] == null || $filt_value['value_date'] == '') {
                                $response = errorRes("Please enter date");
                                return response()->json($response)->header('Content-Type', 'application/json');
                            } else {
                                $filter_value = $filt_value['value_date'];
                            }
    
                        } else if (($column['value_type'] == 'date') && ($condtion['value_type'] == 'between')) {
    
                            if (($filt_value['value_from_date'] == null || $filt_value['value_from_date'] == '') && ($filt_value['value_to_date'] == null || $filt_value['value_to_date'] == '')) {
                                $response = errorRes("Please enter from to date");
                                return response()->json($response)->header('Content-Type', 'application/json');
                            } else {
                                $filter_value = $filt_value['value_from_date'] . "," . $filt_value['value_to_date'];
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
    
                            $newdatares = $newdata;
    
                            array_push($arr_where_clause, $newdata);
    
                        } else if ($clause['clause'] == 'where') {
                            $newdata['clause'] = $clause['id'];
                            $newdata['column'] = $column['id'];
                            $newdata['condtion'] = $condtion['id'];
                            $newdata['value'] = $filter_value;
                            $newdata['source_type'] = $source_type;
    
                            array_push($arr_where_clause, $newdata);
    
                        } else if ($clause['clause'] == 'orwhere') {
                            $newdata['clause'] = $clause['id'];
                            $newdata['column'] = $column['id'];
                            $newdata['condtion'] = $condtion['id'];
                            $newdata['value'] = $filter_value;
                            $newdata['source_type'] = $source_type;
    
                            array_push($arr_or_clause, $newdata);
                        }
    
                    }
    
                }
    
                
                foreach ($arr_where_clause as $wherekey => $objwhere) {
    
                    $Column = getFilterColumnCRM()[$objwhere['column']];
                    $Condtion = getFilterCondtionCRM()[$objwhere['condtion']];
                    $lstDateFilter = getDateFilterValue();
                    $Filter_Value = $objwhere['value'];
                    $source_type = $objwhere['source_type'];
    
                    if ($Condtion['code'] == 'is') {
                        if ($Column['value_type'] == 'leads_source') {
                            $query->whereIn('lead_sources.source', $source_type);
    
                        } else if ($Column['value_type'] == 'date') {
                            // $date_filter_value = date('Y-m-d', strtotime($Filter_Value));
                            // $date_condtion = $date_filter_value;
                            // $Lead->whereDate($Column['column_name'], '=', $date_filter_value);
                            $objDateFilter = $lstDateFilter[$Filter_Value];
    
                            $currentStartDate = date('Y-m-d', strtotime(date('Y-m-d')));
                            $currentEndDate = date('Y-m-d', strtotime(date('Y-m-d')));
    
                            if ($objDateFilter['code'] == "all_closing") {
    
                                $query->where($Column['column_name'], '!=', null);
    
                            } else if ($objDateFilter['code'] == "in_this_week") {
    
                                $currentWeekDay = date('w', strtotime($currentStartDate));
                                $weekStartDate = date('Y-m-d', strtotime($currentStartDate . " -" . ($currentWeekDay - 1) . " days"));
                                $weekEndDate = date('Y-m-d', strtotime($currentEndDate . " +" . ((7 - $currentWeekDay)) . " days"));
                                $query->whereDate($Column['column_name'], '<=', $weekEndDate);
    
                            } else if ($objDateFilter['code'] == "in_this_month") {
    
                                $monthStartDay = date('Y-m-01', strtotime($currentStartDate));
                                $monthEndDay = date('Y-m-t', strtotime($currentEndDate));
                                $query->whereDate($Column['column_name'], '<=', $monthEndDay);
    
                            } else if ($objDateFilter['code'] == "in_next_month") {
                                $currentStartDate = date('Y-m-d', strtotime($currentStartDate . " +5 hours"));
                                $currentStartDate = date('Y-m-d', strtotime($currentStartDate . " +30 minutes"));
                                $monthStartDay = date('Y-m-01', strtotime($currentStartDate . " + 1 month"));
                                $monthEndDay = date('Y-m-t', strtotime($monthStartDay));
                                $query->whereDate($Column['column_name'], '<=', $monthEndDay);
    
                            } else if ($objDateFilter['code'] == "in_next_two_month") {
    
                                $monthStartDay = date('Y-m-01', strtotime($currentStartDate . " + 1 month"));
                                $monthEndDay = date('Y-m-t', strtotime($currentEndDate . " + 2 month"));
                                $query->whereDate($Column['column_name'], '<=', $monthEndDay);
    
                            } else if ($objDateFilter['code'] == "in_next_three_month") {
    
                                $monthStartDay = date('Y-m-01', strtotime($currentStartDate . " + 1 month"));
                                $monthEndDay = date('Y-m-t', strtotime($currentEndDate . " + 3 month"));
                                $query->whereDate($Column['column_name'], '>=', date('Y-m-d'));
                                $query->whereDate($Column['column_name'], '<=', $monthEndDay);
                            }
                            // if ($objDateFilter['code'] == "today") {
                            //     $date_filter_value = date('Y-m-d', strtotime(date('Y-m-d')));
                            //     $Lead->whereDate($Column['column_name'], '=', $date_filter_value);
                            //     // $Lead->whereDate($Column['column_name'], '=', date('Y-m-d'));
    
                            // } 
                            else {
    
                                $date_filter_value = explode(",", $objDateFilter['value']);
                                $from_date_filter = $date_filter_value[0];
                                $from_date_filter_value = date('Y-m-d', strtotime($from_date_filter));
    
                                $to_date_filter = $date_filter_value[1];
                                $to_date_filter_value = date('Y-m-d', strtotime($to_date_filter));
    
                                $query->whereBetween(DB::raw('DATE(' . $Column["column_name"] . ')'), [$from_date_filter_value, $to_date_filter_value]);
                            }
    
                        } else if ($Column['value_type'] == 'select') {
    
                            if($Column['code'] == "lead_miss_data"){
                                $missDataValue = getLeadFilterMissFilterValue()[$Filter_Value];
                                $query->where($missDataValue['column_name'], $missDataValue['value']);                         
                            }else{
                                $query->where($Column['column_name'], $Filter_Value);
                            }
    
                        } else {
                            $query->where($Column['column_name'], 'like', "%" . $Filter_Value . "%");
                        }
    
                    } elseif ($Condtion['code'] == 'is_not') {
                        if ($Column['value_type'] == 'leads_source') {
                            $query->whereNotIn('lead_sources.source', $source_type);
    
                        } else if ($Column['value_type'] == 'date') {
    
                            $objDateFilter = $lstDateFilter[$Filter_Value];
    
                            $currentStartDate = date('Y-m-d', strtotime(date('Y-m-d')));
                            $currentEndDate = date('Y-m-d', strtotime(date('Y-m-d')));
    
                            if ($objDateFilter['code'] == "all_closing") {
    
                                $query->where($Column['column_name'], '!=', null);
    
                            } else if ($objDateFilter['code'] == "in_this_week") {
    
                                $currentWeekDay = date('w', strtotime($currentStartDate));
                                $weekStartDate = date('Y-m-d', strtotime($currentStartDate . " -" . ($currentWeekDay - 1) . " days"));
                                $weekEndDate = date('Y-m-d', strtotime($currentEndDate . " +" . ((7 - $currentWeekDay)) . " days"));
                                $query->whereDate($Column['column_name'], '<=', $weekEndDate);
    
                            } else if ($objDateFilter['code'] == "in_this_month") {
    
                                $monthStartDay = date('Y-m-01', strtotime($currentStartDate));
                                $monthEndDay = date('Y-m-t', strtotime($currentEndDate));
                                $query->whereDate($Column['column_name'], '<=', $monthEndDay);
    
                            } else if ($objDateFilter['code'] == "in_next_month") {
                                $currentStartDate = date('Y-m-d', strtotime($currentStartDate . " +5 hours"));
                                $currentStartDate = date('Y-m-d', strtotime($currentStartDate . " +30 minutes"));
                                $monthStartDay = date('Y-m-01', strtotime($currentStartDate . " + 1 month"));
                                $monthEndDay = date('Y-m-t', strtotime($monthStartDay));
                                $query->whereDate($Column['column_name'], '<=', $monthEndDay);
    
                            } else if ($objDateFilter['code'] == "in_next_two_month") {
    
                                $monthStartDay = date('Y-m-01', strtotime($currentStartDate . " + 1 month"));
                                $monthEndDay = date('Y-m-t', strtotime($currentEndDate . " + 2 month"));
                                $query->whereDate($Column['column_name'], '<=', $monthEndDay);
    
                            } else if ($objDateFilter['code'] == "in_next_three_month") {
    
                                $monthStartDay = date('Y-m-01', strtotime($currentStartDate . " + 1 month"));
                                $monthEndDay = date('Y-m-t', strtotime($currentEndDate . " + 3 month"));
                                $query->whereDate($Column['column_name'], '>=', date('Y-m-d'));
                                $query->whereDate($Column['column_name'], '<=', $monthEndDay);
                            }
    
                            // if ($objDateFilter['code'] == "today") {
                            //     $date_filter_value = date('Y-m-d', strtotime(date('Y-m-d')));
                            //     $Lead->whereDate($Column['column_name'], '!=', $date_filter_value);
                            //     // $Lead->whereDate($Column['column_name'], '!=', date('Y-m-d'));
    
                            // } 
                            else {
    
                                $date_filter_value = explode(",", $objDateFilter['value']);
                                $from_date_filter = $date_filter_value[0];
                                $from_date_filter_value = date('Y-m-d', strtotime($from_date_filter));
    
                                $to_date_filter = $date_filter_value[1];
                                $to_date_filter_value = date('Y-m-d', strtotime($to_date_filter));
    
                                $query->whereNotBetween(DB::raw('DATE(' . $Column["column_name"] . ')'), [$from_date_filter_value, $to_date_filter_value]);
                            }
                        } else if ($Column['value_type'] == 'select') {
                            if($Column['code'] == "lead_miss_data"){
                                $missDataValue = getLeadFilterMissFilterValue()[$Filter_Value];
                                $query->where($missDataValue['column_name'], '<>',$missDataValue['value']);                         
                            }else{
                                $query->whereNotNull($Column['column_name']);
                                $query->where($Column['column_name'], '!=', $Filter_Value);
                            }
    
                        } else {
                            $query->where($Column['column_name'], 'not like', "%" . $Filter_Value . "%");
                        }
    
                    } else if ($Condtion['code'] == "contains") {
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
                            $Filter_Value = explode(",", $Filter_Value);
                            $query->whereIn($Column['column_name'], $Filter_Value);
                        }
    
                    } else if ($Condtion['code'] == "not_contains") {
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
    
                            $Filter_Value = explode(",", $Filter_Value);
                            $query->whereNotIn($Column['column_name'], $Filter_Value);
                        }
    
                    } else if ($Condtion['code'] == "between") {
    
                        if ($Column['value_type'] == 'date') {
    
                            $date_filter_value = explode(",", $Filter_Value);
                            $from_date_filter = $date_filter_value[0];
                            $from_date_filter_value = date('Y-m-d', strtotime($from_date_filter));
    
                            $to_date_filter = $date_filter_value[1];
                            $to_date_filter_value = date('Y-m-d', strtotime($to_date_filter));
    
                            $query->whereBetween(DB::raw('DATE(' . $Column["column_name"] . ')'), [$from_date_filter_value, $to_date_filter_value]);
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
                                } else if ($Column['value_type'] == 'date') {
                                    // $date_filter_value = date('Y-m-d', strtotime($Filter_Value));
                                    // $query->orWhereDate($Column['column_name'], '=', $date_filter_value);
    
                                    $objDateFilter = $lstDateFilter[$Filter_Value];
    
                                    $currentStartDate = date('Y-m-d', strtotime(date('Y-m-d')));
                                    $currentEndDate = date('Y-m-d', strtotime(date('Y-m-d')));
    
                                    if ($objDateFilter['code'] == "all_closing") {
    
                                        $query->orwhere($Column['column_name'], '!=', null);
    
                                    } else if ($objDateFilter['code'] == "in_this_week") {
    
                                        $currentWeekDay = date('w', strtotime($currentStartDate));
                                        $weekStartDate = date('Y-m-d', strtotime($currentStartDate . " -" . ($currentWeekDay - 1) . " days"));
                                        $weekEndDate = date('Y-m-d', strtotime($currentEndDate . " +" . ((7 - $currentWeekDay)) . " days"));
                                        $query->orWhereDate($Column['column_name'], '<=', $weekEndDate);
    
                                    } else if ($objDateFilter['code'] == "in_this_month") {
    
                                        $monthStartDay = date('Y-m-01', strtotime($currentStartDate));
                                        $monthEndDay = date('Y-m-t', strtotime($currentEndDate));
                                        $query->orWhereDate($Column['column_name'], '<=', $monthEndDay);
    
                                    } else if ($objDateFilter['code'] == "in_next_month") {
                                        $currentStartDate = date('Y-m-d', strtotime($currentStartDate . " +5 hours"));
                                        $currentStartDate = date('Y-m-d', strtotime($currentStartDate . " +30 minutes"));
                                        $monthStartDay = date('Y-m-01', strtotime($currentStartDate . " + 1 month"));
                                        $monthEndDay = date('Y-m-t', strtotime($monthStartDay));
                                        $query->orWhereDate($Column['column_name'], '<=', $monthEndDay);
    
                                    } else if ($objDateFilter['code'] == "in_next_two_month") {
    
                                        $monthStartDay = date('Y-m-01', strtotime($currentStartDate . " + 1 month"));
                                        $monthEndDay = date('Y-m-t', strtotime($currentEndDate . " + 2 month"));
                                        $query->orWhereDate($Column['column_name'], '<=', $monthEndDay);
    
                                    } else if ($objDateFilter['code'] == "in_next_three_month") {
    
                                        $monthStartDay = date('Y-m-01', strtotime($currentStartDate . " + 1 month"));
                                        $monthEndDay = date('Y-m-t', strtotime($currentEndDate . " + 3 month"));
                                        $query->orWhereDate($Column['column_name'], '>=', date('Y-m-d'));
                                        $query->orWhereDate($Column['column_name'], '<=', $monthEndDay);
                                    } else {
                                        $date_filter_value = explode(",", $objDateFilter['value']);
                                        $from_date_filter = $date_filter_value[0];
                                        $from_date_filter_value = date('Y-m-d', strtotime($from_date_filter));
    
                                        $to_date_filter = $date_filter_value[1];
                                        $to_date_filter_value = date('Y-m-d', strtotime($to_date_filter));
    
                                        $query->whereBetween(DB::raw('DATE(' . $Column["column_name"] . ')'), [$from_date_filter_value, $to_date_filter_value]);
                                    }
    
                                } else if ($Column['value_type'] == 'select') {
                                    if($Column['code'] == "lead_miss_data"){
                                        $missDataValue = getLeadFilterMissFilterValue()[$Filter_Value];
                                        $query->orWhere($missDataValue['column_name'],$missDataValue['value']);                         
                                    }else{
                                        $query->orWhere($Column['column_name'], $Filter_Value);
                                    }
    
                                } else {
                                    $query->orWhere($Column['column_name'], 'like', "%" . $Filter_Value . "%");
                                }
    
                            } elseif ($Condtion['code'] == 'is_not') {
                                if ($Column['value_type'] == 'leads_source') {
                                    $query->whereIn('lead_sources.source', $source_type);
                                } else if ($Column['value_type'] == 'date') {
                                    // $query->orWhereDate($Column['column_name'], '!=', $date_filter_value);
    
                                    $objDateFilter = $lstDateFilter[$Filter_Value];
    
                                    $currentStartDate = date('Y-m-d', strtotime(date('Y-m-d')));
                                    $currentEndDate = date('Y-m-d', strtotime(date('Y-m-d')));
    
                                    if ($objDateFilter['code'] == "all_closing") {
    
                                        $query->orwhere($Column['column_name'], '!=', null);
    
                                    } else if ($objDateFilter['code'] == "in_this_week") {
    
                                        $currentWeekDay = date('w', strtotime($currentStartDate));
                                        $weekStartDate = date('Y-m-d', strtotime($currentStartDate . " -" . ($currentWeekDay - 1) . " days"));
                                        $weekEndDate = date('Y-m-d', strtotime($currentEndDate . " +" . ((7 - $currentWeekDay)) . " days"));
                                        $query->orWhereDate($Column['column_name'], '<=', $weekEndDate);
    
                                    } else if ($objDateFilter['code'] == "in_this_month") {
    
                                        $monthStartDay = date('Y-m-01', strtotime($currentStartDate));
                                        $monthEndDay = date('Y-m-t', strtotime($currentEndDate));
                                        $query->orWhereDate($Column['column_name'], '<=', $monthEndDay);
    
                                    } else if ($objDateFilter['code'] == "in_next_month") {
                                        $currentStartDate = date('Y-m-d', strtotime($currentStartDate . " +5 hours"));
                                        $currentStartDate = date('Y-m-d', strtotime($currentStartDate . " +30 minutes"));
                                        $monthStartDay = date('Y-m-01', strtotime($currentStartDate . " + 1 month"));
                                        $monthEndDay = date('Y-m-t', strtotime($monthStartDay));
                                        $query->orWhereDate($Column['column_name'], '<=', $monthEndDay);
    
                                    } else if ($objDateFilter['code'] == "in_next_two_month") {
    
                                        $monthStartDay = date('Y-m-01', strtotime($currentStartDate . " + 1 month"));
                                        $monthEndDay = date('Y-m-t', strtotime($currentEndDate . " + 2 month"));
                                        $query->orWhereDate($Column['column_name'], '<=', $monthEndDay);
    
                                    } else if ($objDateFilter['code'] == "in_next_three_month") {
    
                                        $monthStartDay = date('Y-m-01', strtotime($currentStartDate . " + 1 month"));
                                        $monthEndDay = date('Y-m-t', strtotime($currentEndDate . " + 3 month"));
                                        $query->orWhereDate($Column['column_name'], '>=', date('Y-m-d'));
                                        $query->orWhereDate($Column['column_name'], '<=', $monthEndDay);
                                    } else {
                                        $date_filter_value = explode(",", $objDateFilter['value']);
                                        $from_date_filter = $date_filter_value[0];
                                        $from_date_filter_value = date('Y-m-d', strtotime($from_date_filter));
    
                                        $to_date_filter = $date_filter_value[1];
                                        $to_date_filter_value = date('Y-m-d', strtotime($to_date_filter));
    
                                        $query->whereNotBetween(DB::raw('DATE(' . $Column["column_name"] . ')'), [$from_date_filter_value, $to_date_filter_value]);
                                    }
    
                                } else if ($Column['value_type'] == 'select') {
                                    if($Column['code'] == "lead_miss_data"){
                                        $missDataValue = getLeadFilterMissFilterValue()[$Filter_Value];
                                        $query->orWhere($missDataValue['column_name'],'<>',$missDataValue['value']);                         
                                    }else{
                                        $query->orWhere($Column['column_name'], '!=', $Filter_Value);
                                    }
    
                                } else {
                                    $query->orWhere($Column['column_name'], 'not like', "%" . $Filter_Value . "%");
                                }
    
                            } else if ($Condtion['code'] == "contains") {
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
                                    $Filter_Value = explode(",", $Filter_Value);
                                    $query->orWhereIn($Column['column_name'], $Filter_Value);
                                }
    
                            } else if ($Condtion['code'] == "not_contains") {
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
                                    $Filter_Value = explode(",", $Filter_Value);
                                    $query->orWhereNotIn($Column['column_name'], $Filter_Value);
                                }
    
    
                            } else if ($Condtion['code'] == "between") {
    
                                if ($Column['value_type'] == 'date') {
    
                                    $date_filter_value = explode(",", $Filter_Value);
                                    $from_date_filter = $date_filter_value[0];
                                    $from_date_filter_value = date('Y-m-d', strtotime($from_date_filter));
    
                                    $to_date_filter = $date_filter_value[1];
                                    $to_date_filter_value = date('Y-m-d', strtotime($to_date_filter));
    
                                    $query->orWhereBetween(DB::raw('DATE(' . $Column["column_name"] . ')'), [$from_date_filter_value, $to_date_filter_value]);
                                }
                            }
                        }
                    });
                }
    
            }

            if ($isSalePerson == 1) {
                $query->whereIn('leads.assigned_to', $childSalePersonsIds);
            }

            if(isChannelPartner(Auth::user()->type) != 0){
                
                $query->where('lead_sources.source', Auth::user()->id);
            }

            if (isArchitect() == 1) {
                $query->where(function ($query) {
                    $query->orwhere('leads.architect', Auth::user()->id);
                    $query->orwhere('lead_sources.source', Auth::user()->id);
                });
            }
            if ($isReception == 1) {
                $query->where('leads.created_by', Auth::user()->id);
            }
    
            if (isElectrician() == 1) {
                $query->where(function ($query) {
                    $query->orwhere('leads.electrician', Auth::user()->id);
                    $query->orwhere('lead_sources.source', Auth::user()->id);
                });
            }
    
            
            $query->orderBy('leads.id', 'desc');

            if (isset($request['search']['value'])) {
                $search_value = $request['search']['value'];
                $query->where(function ($query) use ($search_value, $searchColumns) {
                    for ($i = 0; $i < count($searchColumns); $i++) {
                        if ($i == 0) {
                            $query->where($searchColumns[$i], 'like', "%" . $search_value . "%");
                        } else {
                            $query->orWhere($searchColumns[$i], 'like', "%" . $search_value . "%");
                        }
                    }
                });
            }

            $Filter_lead_ids = $query->distinct()->pluck('leads.id');

            $Lead = Lead::query();
            $Lead->select($selectColumns);
            $Lead->leftJoin('users as lead_owner', 'lead_owner.id', '=', 'leads.assigned_to');
            $Lead->leftJoin('users as created_by', 'created_by.id', '=', 'leads.user_id');
            $Lead->whereIn('leads.id', $Filter_lead_ids);
            $Lead->orderBy('leads.id', 'desc');
    
            $data = $Lead->paginate(25);
            foreach ($data as $key => $value) {
                $site_stage = '';
                $CRMSettingStageOfSite = CRMSettingStageOfSite::find($value['site_stage']);
                if ($CRMSettingStageOfSite) {
                    $site_stage = $CRMSettingStageOfSite->name;
                }

                $closing_date_time = $value['closing_date_time'];
                $closing_date_time = date('Y-m-d H:i:s', strtotime($closing_date_time . " +5 hours"));
                $closing_date_time = date('Y-m-d', strtotime($closing_date_time . " +30 minutes"));

                $data[$key]['id'] = $value['id'];
                if ($value['is_deal'] == 0) {
                    $data[$key]['id_label'] = '#L' . $value['id'];
                } else if ($value['is_deal'] == 1) {
                    $data[$key]['id_label'] = '#D' . $value['id'];
                }
                $data[$key] = $value;
                $data[$key]['name'] = $value['first_name'] . " " . $value['last_name'];

                if ($value['status'] != 0) {
                    $LeadStatus = getLeadStatus()[$value['status']]['name'];
                    $data[$key]['lead_status_label'] = $LeadStatus;
                    $data[$key]['lead_status'] = $value['status'];
                } else {
                    $data[$key]['lead_status_label'] = "not define";
                    $data[$key]['lead_status'] = "not define";
                }

                $data[$key]['site_stage'] = $site_stage;
                $data[$key]['closing_date'] = $closing_date_time;
                $data[$key]['lead_owner'] = $value['lead_owner_first_name'] . " " . $value['lead_owner_last_name'];
                $data[$key]['created_by'] = $value['created_by_first_name'] . " " . $value['created_by_last_name'];
            }

            $response = successRes();
            $response['data'] = $data;

            
        } catch (QueryException $ex) {
            $response = errorRes($ex->getMessage());
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function searchSiteStage(Request $request)
    {
        try {
            $searchKeyword = isset($request->q) ? $request->q : "";
            $data = CRMSettingStageOfSite::select('id', 'name as text');
            $data->where('crm_setting_stage_of_site.status', 1);
            $data->where('crm_setting_stage_of_site.name', 'like', "%" . $searchKeyword . "%");
            $data->limit(5);
            $data = $data->get();
            $response = array();
            $response = successRes();
            $response['data'] = $data;
        } catch (QueryException $ex) {
            $response = errorRes();
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function searchSiteType(Request $request)
    {
        try {
            $searchKeyword = isset($request->q) ? $request->q : "";
            $data = CRMSettingSiteType::select('id', 'name as text');
            $data->where('crm_setting_site_type.status', 1);
            $data->where('crm_setting_site_type.name', 'like', "%" . $searchKeyword . "%");
            $data->limit(5);
            $data = $data->get();
            $response = array();
            $response = successRes();
            $response['data'] = $data;
        } catch (QueryException $ex) {
            $response = errorRes();
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function searchBHK(Request $request)
    {
        try {
            $searchKeyword = isset($request->q) ? $request->q : "";

            $data = CRMSettingBHK::select('id', 'name as text');
            $data->where('crm_setting_bhk.status', 1);
            $data->where('crm_setting_bhk.name', 'like', "%" . $searchKeyword . "%");
            if(isset($request->site_type)){
                $objSiteType = CRMSettingSiteType::find($request->site_type);
                if (isset($objSiteType)) {
                    if ($objSiteType->is_bhk == 0) {
                        $data->where('crm_setting_bhk.id', 7);
                    }
                }
            }
            $data->limit(15);
            $data = $data->get();
            $response = array();
            $response = successRes();
            $response['data'] = $data;
        } catch (QueryException $ex) {
            $response = errorRes();
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function searchWantToCover(Request $request)
    {
        try {
            $searchKeyword = isset($request->q) ? $request->q : "";

            $data = CRMSettingWantToCover::select('id', 'name as text');
            $data->where('crm_setting_want_to_cover.status', 1);
            $data->where('crm_setting_want_to_cover.name', 'like', "%" . $searchKeyword . "%");
            $data->limit(5);
            $data = $data->get();
            $response = array();
            $response = successRes();
            $response['data'] = $data;
        } catch (QueryException $ex) {
            $response = errorRes();
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function searchSourceType(Request $request)
    {
        $searchKeyword = isset($request->q) ? $request->q : "";

        // $sourcetype_master = CRMSettingSourceType::select('id', 'name as text');
        // $sourcetype_master->where('crm_setting_source_type.status', 1);
        // $sourcetype_master->where('crm_setting_source_type.name', 'like', "%" . $searchKeyword . "%");
        // $sourcetype_master = $sourcetype_master->get();

        $data = array();
        // foreach ($sourcetype_master as $source_master_key => $source_master_value) {
        //     $source_master['id'] = "master-" . $source_master_value['id'];
        //     $source_master['type'] = "master";
        //     $source_master['text'] = $source_master_value['text'];
        //     array_push($data, $source_master);
        // }

        foreach (getLeadSourceTypes() as $static_key => $static_value) {
            if ($static_value['id'] != 8 && $static_value['id'] != 9) {

                $fix_source_data['id'] = $static_value['type'] . "-" . $static_value['id'];
                $fix_source_data['type'] = $static_value['type'];
                $fix_source_data['text'] = $static_value['lable'];
                array_push($data, $fix_source_data);
            }
        }

        $response = array();
        $response = successRes();
        $response['data'] = $data;
        return response()->json($response)->header('Content-Type', 'application/json');


    }

    function searchSource(Request $request)
    {
        try {
            $searchKeyword = isset($request->q) ? $request->q : "";

            $isUser = 0;
            $userType = array();
            $isArchitect = isArchitect();
            $isSalePerson = isSalePerson();
            $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
            $isThirdPartyUser = isThirdPartyUser();
            $isChannelPartner = isChannelPartner(Auth::user()->type);

            if (strpos($request->source_type, "user-") !== false || $request->type == "user") {
                $isUser = 1;
                if (strpos($request->source_type, ",") !== false) {
                    $explodeSourceType = explode(",", $request->source_type);
                    foreach ($explodeSourceType as $key => $value) {
                        $sourcePieces = explode("-", $value);
                        if (count($sourcePieces) > 0) {
                            $userType[] = $sourcePieces[1];
                        }
                    }
                } else {
                    $sourcePieces = explode("-", $request->source_type);
                    if (count($sourcePieces) > 0) {
                        $userType[] = $sourcePieces[1];
                    }
                }
            }

            if ($isUser == 1) {
                $channel_partner = array('user-101', 'user-102', 'user-103', 'user-104', 'user-105');
                if (in_array($request->source_type, $channel_partner)) {
                    $data = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text2"), 'channel_partner.firm_name  AS text');
                    $data->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
                    $data->whereIn('users.status', [1,2,3,4,5]);
                    $data->whereIn('users.type', $userType);
                    $data->where(function ($query) use ($searchKeyword) {
                        $query->where('users.first_name', 'like', '%' . $searchKeyword . '%');
                        $query->orWhere('users.last_name', 'like', '%' . $searchKeyword . '%');
                    });
                    $data->limit(10);
                    $data = $data->get();
                    $data = json_encode($data);
                    $data = json_decode($data, true);
                } else {
                    $source_type = explode("-", $request->source_type);
                    $data = User::select('users.id', 'users.type','users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"), 'users.phone_number');
                    $data->whereIn('users.status', [1,2,3,4,5]);
                    if ($source_type[1] == 202) {
                        // FOR ARCHITECT
                        if ($isSalePerson == 1) {
                            $salePerson = SalePerson::select('cities')->where('user_id', Auth::user()->id)->first();
                            $cities = array();
                            if ($salePerson) {

                                $cities = explode(",", $salePerson->cities);
                            } else {
                                $cities = array(0);
                            }
                            $data->whereIn('users.city_id', $cities);
                        } elseif ($isChannelPartner != 0) {
                            $data->where('users.city_id', Auth::user()->city_id);
                        }

                        $data->whereIn('users.type', [201, 202]);

                    } else if ($source_type[1] == 302) {
                        // FOR ELECTRICIAN
                        if ($isSalePerson == 1) {
                            $salePerson = SalePerson::select('cities')->where('user_id', Auth::user()->id)->first();
                            $cities = array();
                            if ($salePerson) {

                                $cities = explode(",", $salePerson->cities);
                            } else {
                                $cities = array(0);
                            }
                            $data->whereIn('users.city_id', $cities);
                        } elseif ($isChannelPartner != 0) {
                            $data->where('users.city_id', Auth::user()->city_id);
                        }

                        $data->whereIn('users.type', [301, 302]);

                    } else {
                        $data->where('users.type', $source_type[1]);
                    }



                    $data->where(function ($query) use ($searchKeyword) {
                        $query->where('users.first_name', 'like', '%' . $searchKeyword . '%');
                        $query->orWhere('users.last_name', 'like', '%' . $searchKeyword . '%');
                        $query->orWhere('users.phone_number', 'like', '%' . $searchKeyword . '%');
                    });

                    $data->limit(10);
                    $data = $data->get();
                
                    // $data = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"));
                    // $data->whereIn('users.status', [1,2,3,4,5]);
                    // $data->whereIn('users.type', $userType);
                    // $data->where(function ($query) use ($searchKeyword) {
                    //     $query->where('users.first_name', 'like', '%' . $searchKeyword . '%');
                    //     $query->orWhere('users.last_name', 'like', '%' . $searchKeyword . '%');
                    // });
                    // $data->limit(5);
                    // $data = $data->get();
                    $data = json_encode($data);
                    $data = json_decode($data, true);
                }

            } else {
                $data = CRMSettingSource::select('id', 'name as text');
                $data->where('crm_setting_source.status', 1);
                $data->where('crm_setting_source.name', 'like', "%" . $searchKeyword . "%");
                $data->limit(10);
                $data = $data->get();
                $data = json_encode($data);
                $data = json_decode($data, true);
            }

            $response = array();

            $response = successRes();
            $response['data'] = $data;
            $response['isUser'] = $isUser;
        } catch (QueryException $ex) {
            $response = errorRes();
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function searchStatus(Request $request)
    {
        try {
            $searchKeyword = isset($request->q) ? $request->q : "";
            $type = isset($request->type) ? $request->type : 0;

            $LeadStatus = getLeadStatus();

            $finalArray = array();

            foreach ($LeadStatus as $key => $value) {
                // $LeadStatus[$key]['id'] = $value['id'] . "";
                // $LeadStatus[$key]['text'] = $value['name'];

                if ($value['type'] == $type) {
                    $countFinal = count($finalArray);
                    $finalArray[$countFinal] = array();
                    $finalArray[$countFinal]['id'] = $value['id'];
                    $finalArray[$countFinal]['text'] = $value['name'];
                }
            }

            $response = array();
            $response = successRes();
            $response['data'] = $finalArray;
        } catch (QueryException $ex) {
            $response = errorRes();
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function searchSubStatus(Request $request)
    {
        try {
            $searchKeyword = isset($request->q) ? $request->q : "";

            $data = CRMSettingSubStatus::select('id', 'name as text');
            $data->where('crm_setting_sub_status.status', 1);
            $data->where('crm_setting_sub_status.name', 'like', "%" . $searchKeyword . "%");
            $data->limit(5);
            $data = $data->get();
            $response = array();
            $response = successRes();
            $response['data'] = $data;
        } catch (QueryException $ex) {
            $response = errorRes();
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function searchCompetitors(Request $request)
    {
        try {
            $searchKeyword = isset($request->q) ? $request->q : "";

            $data = CRMSettingCompetitors::select('id', 'name as text');
            $data->where('crm_setting_competitors.status', 1);
            $data->where('crm_setting_competitors.name', 'like', "%" . $searchKeyword . "%");
            $data->limit(5);
            $data = $data->get();
            $response = array();
            $response = successRes();
            $response['data'] = $data;
        } catch (QueryException $ex) {
            $response = errorRes();
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function allContact(Request $request)
    {
        try {
            $LeadContact = LeadContact::query();
            $LeadContact->select('crm_setting_contact_tag.name as tag_name', 'lead_contacts.*');
            $LeadContact->leftJoin('crm_setting_contact_tag', 'crm_setting_contact_tag.id', '=', 'lead_contacts.contact_tag_id');
            $LeadContact->where('lead_contacts.lead_id', $request->lead_id);
            $LeadContact->orderBy('lead_contacts.id', 'desc');
            // $LeadContact->limit(5);
            $LeadContact = $LeadContact->get();
            $LeadContact = json_encode($LeadContact);
            $LeadContact = json_decode($LeadContact, true);
            $response = successRes();
            $response['data'] = $LeadContact;
        } catch (QueryException $ex) {
            $response = errorRes();
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function allFiles(Request $request)
    {
        try {

            $LeadFile = LeadFile::query();
            $LeadFile->select('crm_setting_file_tag.name as tag_name', 'lead_files.*', 'users.first_name', 'users.last_name');
            $LeadFile->leftJoin('crm_setting_file_tag', 'crm_setting_file_tag.id', '=', 'lead_files.file_tag_id');
            $LeadFile->leftJoin('users', 'users.id', '=', 'lead_files.uploaded_by');
            $LeadFile->where('lead_files.lead_id', $request->lead_id);
            //$LeadFile->limit(5);
            $LeadFile->orderBy('lead_files.id', 'desc');
            $LeadFile = $LeadFile->get();
            $LeadFile = json_encode($LeadFile);
            $LeadFile = json_decode($LeadFile, true);

            foreach ($LeadFile as $key => $value) {
                $name = explode("/", $value['name']);

                $LeadFile[$key]['name'] = end($name);
                $LeadFile[$key]['download'] = getSpaceFilePath($value['name']);
                $LeadFile[$key]['created_at'] = convertDateTime($value['created_at']);
            }
            $response = successRes();
            $response['data'] = $LeadFile;
        } catch (QueryException $ex) {
            $response = errorRes();
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }
    function allUpdates(Request $request)
    {
        try {
            $LeadUpdate = LeadUpdate::query();
            $LeadUpdate->select('lead_updates.id', 'lead_updates.message', 'lead_updates.user_id', 'users.first_name', 'users.last_name', 'lead_updates.created_at');
            $LeadUpdate->leftJoin('users', 'users.id', '=', 'lead_updates.user_id');
            $LeadUpdate->where('lead_updates.lead_id', $request->lead_id);
            $LeadUpdate->orderBy('lead_updates.id', 'desc');
            //$LeadUpdate->limit(5);
            $LeadUpdate = $LeadUpdate->get();
            $LeadUpdate = json_encode($LeadUpdate);
            $LeadUpdate = json_decode($LeadUpdate, true);

            foreach ($LeadUpdate as $key => $value) {
                $LeadUpdate[$key]['created_at'] = convertDateTime($value['created_at']);
            }

            $response = successRes();
            $response['data'] = $LeadUpdate;
        } catch (QueryException $ex) {
            $response = errorRes();
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function changeStatus(Request $request)
    {
        try {
            $response = saveLeadAndDealStatus($request->id, $request->status, $request->app_source);
        } catch (QueryException $ex) {
            $response = errorRes();
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function saveStatus(Request $request)
    {
        try {
            $response = saveLeadAndDealStatus($request->lead_status_lead_id, $request->lead_status_new, $request->app_source);
        } catch (QueryException $ex) {
            $response = errorRes();
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function searchLeadCallType(Request $request)
    {

        try {
            $searchKeyword = isset($request->q) ? $request->q : "";

            $data = CRMSettingCallType::select('id', 'name as text');
            $data->where('crm_setting_call_type.status', 1);
            $data->where('crm_setting_call_type.name', 'like', "%" . $searchKeyword . "%");
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
    function searchLeadContact(Request $request)
    {

        try {
            $searchKeyword = isset($request->q) ? $request->q : "";

            $data = LeadContact::select('lead_contacts.id', 'lead_contacts.first_name', 'lead_contacts.last_name', DB::raw("CONCAT(lead_contacts.first_name,' ',lead_contacts.last_name) AS text"));
            $data->where('lead_contacts.lead_id', $request->lead_id);
            $data->where('lead_contacts.first_name', 'like', "%" . $searchKeyword . "%");
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
    function searchCallOutCome(Request $request)
    {

        try {
            $searchKeyword = isset($request->q) ? $request->q : "";

            $data = CRMSettingCallOutcomeType::select('id', 'name as text');
            $data->where('crm_setting_call_outcome_type.status', 1);
            $data->where('crm_setting_call_outcome_type.name', 'like', "%" . $searchKeyword . "%");
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
    public function callSave(Request $request)
    {
        try {

            $rules = array();
            $rules['call_lead_id'] = 'required';
            $rules['call_type_id'] = 'required';
            $rules['call_contact_name'] = 'required';
            $rules['call_schedule_date'] = 'required';
            $rules['call_schedule_time'] = 'required';
            $rules['call_purpose'] = 'required';
            $rules['call_description'] = 'required';
            $rules['call_id'] = 'required';
            if ($request->call_move_to_close == 1) {
                $rules['call_outcome'] = 'required';
                $rules['call_closing_note'] = 'required';
            } else {
                $rules['call_purpose'] = 'required';
            }

            if ($request->call_type_id == 2) {
                $rules['call_outcome'] = 'required';
            }
            if ($request->call_type_id == 1) {
                $rules['call_reminder'] = 'required';
            }

            $customMessage = array();
            $customMessage['lead_file_lead_id.required'] = "Invalid parameters";


            $validator = Validator::make($request->all(), $rules, $customMessage);

            if ($validator->fails()) {

                $response = errorRes("The request could not be understood by the server due to malformed syntax");
                $response['data'] = $validator->errors();
            } else {

                $lead_call_schedule = date('Y-m-d H:i:s', strtotime($request->call_schedule_date . "  " . $request->call_schedule_time));

                $leadStatus = getLeadStatus();
                $askForStatusChange = 0;

                $hasCall = LeadCall::where('lead_id', $request->call_lead_id)->first();
                $statusArray = array();
                $convertToCall = 0;

                if (!$hasCall) {
                    $convertToCall = 1;
                }

                if ($request->call_id == 0) {
                    $LeadCall = new LeadCall();
                } else {
                    $LeadCall = LeadCall::find($request->call_id);
                }

                $LeadCall->user_id = Auth::user()->id;
                $LeadCall->type_id = $request->call_type_id;
                $LeadCall->lead_id = $request->call_lead_id;
                $LeadCall->contact_name = $request->call_contact_name;
                $LeadCall->call_schedule = $lead_call_schedule;
                $LeadCall->purpose = $request->call_purpose;
                $LeadCall->description = $request->call_description;
                if ($request->call_type_id == 1) {
                    $LeadCall->is_notification = 1;

                    $reminder_date_time = getReminderTimeSlot($lead_call_schedule)[$request->call_reminder]['datetime'];

                    $LeadCall->reminder = $reminder_date_time;
                    $LeadCall->reminder_id = $request->call_reminder;
                }

                if (isset($request->call_move_to_close) && $request->call_move_to_close == "1" || $request->call_type_id == 2) {
                    $LeadCall->is_closed = 1;
                    $LeadCall->outcome_type = $request->call_outcome;
                    $LeadCall->closed_date_time = date("Y-m-d H:i:s");
                    $LeadCall->close_note = $request->call_closing_note;

                    $Lead = Lead::find($LeadCall->lead_id);
                }

                $LeadCall->save();
                $statusupdate = '';
                if (isset($request->lead_call_status)) {
                    $statusupdate = saveLeadAndDealStatusInAction($LeadCall->lead_id, $request->lead_call_status, $request->ip(), $request->app_source);
                }


                $LeadUpdate = new LeadUpdate();
                $LeadUpdate->user_id = Auth::user()->id;
                $LeadUpdate->lead_id = $LeadCall->lead_id;
                $LeadUpdate->message = $request->call_description;
                if ($LeadCall->is_closed == 1) {
                    $LeadUpdate->task = "Close Call";
                } else if ($LeadCall->is_closed == 0) {
                    $LeadUpdate->task = "Open Call";
                }
                $LeadUpdate->task_title = $request->call_purpose;
                $LeadUpdate->save();



                $response = successRes("Successfully saved call");
                $response['id'] = $LeadCall->lead_id;
                $response['ask_for_status_change'] = $askForStatusChange;
                $response['status_array'] = $statusArray;
                $response['data'] = $statusupdate;
            }
        } catch (QueryException $ex) {
            $response = errorRes($ex);
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function searchTaskAssignedTo(Request $request)
    {

        try {

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
            $response = successRes();
            $response['data'] = $user_new;
        } catch (QueryException $ex) {
            $response = errorRes();
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }
    function searchTaskOutCome(Request $request)
    {

        try {
            $searchKeyword = isset($request->q) ? $request->q : "";

            $data = CRMSettingTaskOutcomeType::select('id', 'name as text');
            $data->where('crm_setting_task_outcome_type.status', 1);
            $data->where('crm_setting_task_outcome_type.name', 'like', "%" . $searchKeyword . "%");
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
    function searchLeadStatus(Request $request)
    {

        try {
            $searchKeyword = isset($request->q) ? $request->q : "";

            $data = CRMSettingTaskOutcomeType::select('id', 'name as text');
            $data->where('crm_setting_task_outcome_type.status', 1);
            $data->where('crm_setting_task_outcome_type.name', 'like', "%" . $searchKeyword . "%");
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
    public function taskSave(Request $request)
    {

        $rules = array();


        $rules['task_id'] = 'required';
        $rules['task_lead_id'] = 'required';
        $rules['task_assign_to'] = 'required';
        $rules['task'] = 'required';

        $rules['task_due_date'] = 'required';
        $rules['task_due_time'] = 'required';

        $rules['task_reminder'] = 'required';

        $rules['task_description'] = 'required';
        if ($request->lead_task_move_to_close == 1) {
            $rules['lead_task_outcome'] = 'required';
            $rules['lead_task_closing_note'] = 'required';
        }

        $customMessage = array();
        $customMessage['task_lead_id.required'] = "Invalid parameters";


        $validator = Validator::make($request->all(), $rules, $customMessage);

        if ($validator->fails()) {

            $response = errorRes("The request could not be understood by the server due to malformed syntax");
            $response['data'] = $validator->errors();
        } else {

            $task_due_date_time = date('Y-m-d H:i:s', strtotime($request->task_due_date . "  " . $request->task_due_time));
            $reminder_date_time = getReminderTimeSlot($task_due_date_time)[$request->task_reminder]['datetime'];

            // $task_due_date_time = $request->task_due_date_time . " 23:59:59";
            // $task_due_date_time = date('Y-m-d H:i:s', strtotime($task_due_date_time . " -5 hours"));
            // $task_due_date_time = date('Y-m-d H:i:s', strtotime($task_due_date_time . " -30 minutes"));


            // $task_reminder_date_time = $request->task_reminder . " 23:59:59";
            // $task_reminder_date_time = date('Y-m-d H:i:s', strtotime($task_reminder_date_time . " -5 hours"));
            // $task_reminder_date_time = date('Y-m-d H:i:s', strtotime($task_reminder_date_time . " -30 minutes"));

            if ($request->task_id == 0) {
                $LeadTask = new LeadTask();
            } else {
                $LeadTask = LeadTask::find($request->task_id);
            }

            $LeadTask->lead_id = $request->task_lead_id;
            $LeadTask->user_id = Auth::user()->id;

            if ($request->task_assign_to == 0) {
                $LeadTask->assign_to = Auth::user()->id;
            } else {
                $LeadTask->assign_to = $request->task_assign_to;
            }

            $LeadTask->task = $request->task;
            $LeadTask->due_date_time = $task_due_date_time;
            $LeadTask->reminder = $reminder_date_time;
            $LeadTask->reminder_id = $request->task_reminder;
            $LeadTask->description = $request->task_description;
            $LeadTask->is_notification = 1;

            if (isset($request->task_move_to_close) && $request->task_move_to_close == 1) {
                $LeadTask->is_closed = 1;
                $LeadTask->outcome_type = $request->task_outcome;
                $LeadTask->closed_date_time = date("Y-m-d H:i:s");
                $LeadTask->close_note = $request->task_closing_note;
            }

            $statusupdate = '';
            if (isset($request->lead_task_status)) {
                $statusupdate = saveLeadAndDealStatusInAction($LeadTask->lead_id, $request->lead_task_status, $request->ip(), $request->app_source);
            }

            $LeadTask->save();
            $LeadUpdate = new LeadUpdate();
            $LeadUpdate->user_id = Auth::user()->id;
            $LeadUpdate->lead_id = $LeadTask->lead_id;
            $LeadUpdate->message = $request->task_description;
            if ($LeadTask->is_closed == 1) {
                $LeadUpdate->task = "Close Task";
            } else if ($LeadTask->is_closed == 0) {
                $LeadUpdate->task = "Open Task";
            }
            $LeadUpdate->task_title = $request->task;
            $LeadUpdate->save();

            $response = successRes("Successfully saved task");
            $response['id'] = $LeadTask->lead_id;
            $response['data'] = $statusupdate;
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }
    public function noteSave(Request $request)
    {

        $rules = array();
        $rules['lead_id'] = 'required';
        $rules['message'] = 'required';
        $rules['title'] = 'required';

        $customMessage = array();
        $customMessage['lead_file_lead_id.required'] = "Invalid parameters";


        $validator = Validator::make($request->all(), $rules, $customMessage);

        if ($validator->fails()) {

            $response = errorRes("The request could not be understood by the server due to malformed syntax");
            $response['data'] = $validator->errors();
        } else {



            $LeadUpdate = new LeadUpdate();
            $LeadUpdate->user_id = Auth::user()->id;
            $LeadUpdate->lead_id = $request->lead_id;
            $LeadUpdate->message = $request->message;
            $LeadUpdate->task = "";
            $LeadUpdate->task_title = $request->title;
            $LeadUpdate->save();

            $response = successRes("Successfully saved update");
            $response['id'] = $LeadUpdate->lead_id;
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function SaveExcelLead(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lead_excel' => ['required'],
        ]);
        if ($validator->fails()) {
            $response = [];
            $response['status'] = 0;
            $response['msg'] = 'The request could not be understood by the server due to malformed syntax';
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors();

            return response()->json($response)->header('Content-Type', 'application/json');
        } else {
            $the_file = $request->file('lead_excel');
            // try {
            $spreadsheet = IOFactory::load($the_file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $row_limit = $sheet->getHighestDataRow();
            $row_range = range(2, $row_limit);

            foreach ($row_range as $row) {
                $Created = trim($sheet->getCell('A' . $row)->getValue(), ' ');
                $Name = trim($sheet->getCell('B' . $row)->getValue(), ' ');
                $Email = trim($sheet->getCell('C' . $row)->getValue(), ' ');
                $Phone = trim($sheet->getCell('D' . $row)->getValue(), ' ');
                $Source = trim($sheet->getCell('E' . $row)->getValue(), ' ');
                $Form = trim($sheet->getCell('F' . $row)->getValue(), ' ');
                $Channel = trim($sheet->getCell('G' . $row)->getValue(), ' ');
                $Stage = trim($sheet->getCell('H' . $row)->getValue(), ' ');
                $Owner = trim($sheet->getCell('I' . $row)->getValue(), ' ');
                $Labels = trim($sheet->getCell('J' . $row)->getValue(), ' ');

                $objNewItem['created'] = $Created;
                $objNewItem['name'] = $Name;
                $objNewItem['email'] = $Email;
                $objNewItem['phone'] = $Phone;
                $objNewItem['source'] = $Source;
                $objNewItem['form'] = $Form;
                $objNewItem['channel'] = $Channel;
                $objNewItem['stage'] = $Stage;
                $objNewItem['owner'] = $Owner;
                $objNewItem['labels'] = $Labels;

                $objItem = Lead::where('first_name', $Name)->first();

                $wlmst_client = new Wlmst_Client();
                $wlmst_client->name = $Name;
                $wlmst_client->email = $Email;
                $wlmst_client->mobile = $Phone;
                $wlmst_client->address = '';
                $wlmst_client->isactive = 1;
                $wlmst_client->remark = 0;
                $wlmst_client->save();


                if ($objItem) {
                    // Lead exists, update existing record
                    $ItemCode = Lead::find($objItem->id);
                } else {
                    // Lead does not exist, create a new record
                    $ItemCode = new Lead();
                    $ItemCode->created_by = Auth::user()->id;
                }

                $ItemCode->first_name = $Name;
                $ItemCode->last_name = '';
                $ItemCode->email = $Email;
                $ItemCode->phone_number = $Phone;
                $ItemCode->house_no = 0;
                $ItemCode->customer_id = $wlmst_client->id;
                $ItemCode->addressline1 = '';
                $ItemCode->addressline2 = '';
                $ItemCode->area = '';
                $ItemCode->pincode = 0;
                $ItemCode->meeting_house_no = '';
                $ItemCode->meeting_addressline1 = '';
                $ItemCode->meeting_addressline2 = '';
                $ItemCode->meeting_area = '';
                $ItemCode->is_deal = 0;
                $ItemCode->meeting_pincode = '';
                $ItemCode->source_type = 'facebook';
                $ItemCode->source = 'facebook';
                $ItemCode->updated_by = Auth::user()->id;
                $ItemCode->assigned_to = Auth::user()->id;
                $ItemCode->quotation_file = '';
                $ItemCode->save();

                if ($ItemCode) {
                    $timeline = array();
                    $timeline['lead_id'] = $ItemCode->id;
                    $timeline['type'] = "lead-Updated";
                    $timeline['reffrance_id'] = $ItemCode->id;
                    $timeline['source'] = "WEB";

                    $Contact = LeadContact::where('lead_id', $ItemCode->id)->first();

                    if ($Contact) {
                        // Existing contact, update it
                        $Contact->lead_id = $ItemCode->id;
                        $Contact->contact_tag_id = 1;
                        $Contact->first_name = $ItemCode->first_name;
                        $Contact->last_name = '';
                        $Contact->phone_number = $ItemCode->phone_number;
                        $Contact->alernate_phone_number = 0;
                        $Contact->email = $ItemCode->email;
                        $Contact->type = 0;
                        $Contact->type_detail = 0;
                        $Contact->save();
                        $timeline['description'] = "Lead Updated by " . Auth::user()->first_name . " " . Auth::user()->last_name;
                    } else {
                        // New contact, create it
                        $Contact = new LeadContact();
                        $Contact->lead_id = $ItemCode->id;
                        $Contact->contact_tag_id = 1;
                        $Contact->first_name = $ItemCode->first_name;
                        $Contact->last_name = '';
                        $Contact->phone_number = $ItemCode->phone_number;
                        $Contact->alernate_phone_number = 0;
                        $Contact->email = $ItemCode->email;
                        $Contact->type = 0;
                        $Contact->type_detail = 0;
                        $Contact->save();
                        $timeline['description'] = "Lead Created by " . Auth::user()->first_name . " " . Auth::user()->last_name;
                    }
                    saveLeadTimeline($timeline);
                }
            }


            $response = successRes();
            // $response = $row_range;
            return response()->json($response)->header('Content-Type', 'application/json');
            // } catch (Exception $e) {
            //     $response = errorRes($e->getMessage());
            //     return response()->json($response)->header('Content-Type', 'application/json');
            // }
        }
    }
}