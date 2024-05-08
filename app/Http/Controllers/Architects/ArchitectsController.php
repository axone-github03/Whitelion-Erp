<?php

namespace App\Http\Controllers\Architects;

use App\Http\Controllers\Controller;
use App\Models\Architect;
use App\Models\ArchitectCategory;
use App\Models\UserUpdate;
use App\Models\ChannelPartner;
use App\Models\CRMHelpDocument;
use App\Models\CRMLog;
use App\Models\CRMSettingSubStatus;
use App\Models\CRMSettingStageOfSite;
use App\Models\CRMSettingSourceType;
use App\Models\CRMSettingSource;
use App\Models\Exhibition;
use App\Models\Inquiry;
use App\Models\CityList;
use App\Models\StateList;
use App\Models\CountryList;
use App\Models\SalePerson;
use App\Models\User;
use App\Models\Lead;
use App\Models\UserContact;
use App\Models\UserCallAction;
use App\Models\UserMeetingParticipant;
use App\Models\CRMSettingMeetingTitle;
use App\Models\UserMeetingAction;
use App\Models\UserTaskAction;
use App\Models\UserFiles;
use App\Models\UserNotes;
use App\Models\TagMaster;
use App\Models\UserLog;
use Config;
use DB;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Whatsapp\WhatsappApiContoller;
use Mail;
use NunoMaduro\Collision\Adapters\Phpunit\State;

class ArchitectsController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            // $tabCanAccessBy = [0, 1, 2, 6, 7, 9, 101, 102, 103, 104, 105];
            $tabCanAccessBy = [0, 1, 2, 6, 7, 9, 13];

            if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
                return redirect()->route('dashboard');
            }

            return $next($request);
        });
    }
    public function index(Request $request)
    {

        $isSalePerson = isSalePerson();
        $isChannelPartner = isChannelPartner(Auth::user()->type);
        $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
        $isMarketingDispatcherUser = isMarketingDispatcherUser();
        $isTaleSalesUser = isTaleSalesUser();
        if ($isTaleSalesUser == 1) {
            $TeleSalesCity = TeleSalesCity(Auth::user()->id);
        }

        if ($isSalePerson == 1) {
            $SalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
        }


        $params = [];
        $data = [];
        $data['title'] = 'Architects';
        $data['id'] = isset($request->id) ? $request->id : 0;
        $data['type'] = 202;
        $data['is_architect_module'] = 1;
        $data['isSalePerson'] = isSalePerson();
        $data['addView'] = isset($request->add) && $request->add == 1 ? 1 : 0;
        $data['source_types'] = getArchitectsSourceTypes();
        $data['architect_categories'] = ArchitectCategory::orderBy('id', 'asc')->get();
        $data['viewMode'] = isset($request->view_mode) && $request->view_mode == 1 ? 1 : 0;
        $data['searchUserId'] = isset($request->id) ? $request->id : '';

        $ArchitectsStatus = getArchitectsStatus();
        $ArchitectsStatus[6]['id'] = 100;
        $ArchitectsStatus[6]['name'] = "All";
        $ArchitectsStatus[6]['code'] = "All";
        $ArchitectsStatus[6]['header_code'] = "All";
        $ArchitectsStatus[6]['sequence_id'] = 7;
        $ArchitectsStatus[6]['access_user_type'] = array(0, 9);

        $data['architect_status'] = $ArchitectsStatus;

        $total_count = 0;
        foreach ($data['architect_status'] as $key => $value) {

            if ($value['id'] == 100) {
                $data['architect_status'][$key]['count'] = $total_count;
            } else {
                $status_count = Architect::where('architect.status', $value['id']);
                $status_count->leftJoin('users', 'users.id', '=', 'architect.user_id');
                if ($isSalePerson == 1) {
                    $status_count->whereIn('architect.sale_person_id', $SalePersonsIds);
                } elseif ($isChannelPartner != 0) {
                    // $status_count->where('architect.added_by', Auth::user()->id);
                    $ObjChannelPartner = ChannelPartner::select('sale_persons')->where('user_id', Auth::user()->id)->first();
                    if($ObjChannelPartner){
                        $status_count->where('architect.sale_person_id', $ObjChannelPartner->sale_persons);
                    }
                } 
                // elseif ($isTaleSalesUser == 1) {
                //     $status_count->whereIn('users.city_id', $TeleSalesCity);
                // }
                $status_count = $status_count->count();
                $data['architect_status'][$key]['count'] = $status_count;
            }
            $total_count += $status_count;
        }
        $data['architect_status_total_count'] = $total_count;

        if ($data['isSalePerson'] == 1) {
            $data['viewMode'] = 0;
        }

        // return $ArchitectsStatus;
        return view('architects_new/index', compact('data'));
    }
    public function table(Request $request)
    {

        $isSalePerson = isSalePerson();
        $isChannelPartner = isChannelPartner(Auth::user()->type);
        $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
        $isMarketingDispatcherUser = isMarketingDispatcherUser();
        $isTaleSalesUser = isTaleSalesUser();
        if ($isTaleSalesUser == 1) {
            $TeleSalesCity = TeleSalesCity(Auth::user()->id);
        }

        if ($isSalePerson == 1) {
            $SalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
        }


        $data = [];
        $data['title'] = 'Architects';
        $data['type'] = 202;
        $data['source_types'] = getArchitectsSourceTypes();
        $data['isSalePerson'] = isSalePerson();
        $data['is_architect_module'] = 1;
        $data['searchUserId'] = isset($request->id) ? $request->id : '';
        $data['addView'] = isset($request->add) && $request->add == 1 ? 1 : 0;

        $ArchitectsStatus = getArchitectsStatus();
        $ArchitectsStatus[6]['id'] = 100;
        $ArchitectsStatus[6]['name'] = "All";
        $ArchitectsStatus[6]['code'] = "All";
        $ArchitectsStatus[6]['header_code'] = "All";
        $ArchitectsStatus[6]['sequence_id'] = 7;
        $ArchitectsStatus[6]['access_user_type'] = array(0, 9);

        $data['architect_status'] = $ArchitectsStatus;

        $total_count = 0;
        foreach ($data['architect_status'] as $key => $value) {
            if ($value['id'] == 100) {
                $data['architect_status'][$key]['count'] = $total_count;
            } else {
                $status_count = Architect::query();
                $status_count->where('architect.status', $value['id']);
                $status_count->leftJoin('users', 'users.id', '=', 'architect.user_id');
                if ($isSalePerson == 1) {
                    $status_count->whereIn('architect.sale_person_id', $SalePersonsIds);
                } elseif ($isChannelPartner != 0) {
                    // $status_count->where('architect.added_by', Auth::user()->id);
                    $ObjChannelPartner = ChannelPartner::select('sale_persons')->where('user_id', Auth::user()->id)->first();
                    if($ObjChannelPartner){
                        $status_count->where('architect.sale_person_id', $ObjChannelPartner->sale_persons);
                    }
                } 
                // elseif ($isTaleSalesUser == 1) {
                //     $status_count->whereIn('users.city_id', $TeleSalesCity);
                // }
                $status_count = $status_count->count();
                $data['architect_status'][$key]['count'] = $status_count;
            }
            $total_count += $status_count;
        }
        $data['architect_status_total_count'] = $total_count;

        return view('architects_new/table', compact('data'));
    }
    public function ajax(Request $request)
    {
        $isSalePerson = isSalePerson();
        $isChannelPartner = isChannelPartner(Auth::user()->type);
        $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
        $isCreUser = isCreUser();
        $isMarketingDispatcherUser = isMarketingDispatcherUser();
        $isTaleSalesUser = isTaleSalesUser();
        if ($isTaleSalesUser == 1) {
            $TeleSalesCity = TeleSalesCity(Auth::user()->id);
        }

        if ($isSalePerson == 1) {
            $SalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
        }

        $searchColumns = [
            'users.id',
            "CONCAT(users.first_name,' ',users.last_name)",
            "CONCAT(sale_person.first_name,' ',sale_person.last_name)",
            'users.phone_number',
            'city_list.name',
        ];

        $sortingColumns = [
            0 => 'users.id',
            1 => 'users.first_name',
            2 => 'users.email',
            3 => 'architect.sale_person_id',
            4 => 'architect.total_point_current',
            5 => 'architect.total_point',
            6 => 'users.status',
            7 => 'architect.category_id',
            8 => 'architect.principal_architect_name',
            9 => 'users.created_by',
        ];

        $selectColumns = [
            'users.id', 
            'users.type', 
            'users.first_name', 
            'users.last_name', 
            'users.email', 
            'users.phone_number', 
            'architect.sale_person_id', 
            'users.status as account_status', 
            'architect.status as architect_status', 
            'architect.total_point_current', 
            'sale_person.first_name as sale_person_first_name', 
            'sale_person.last_name  as sale_person_last_name', 
            'users.created_at', 
            'architect.category_id', 
            'architect.principal_architect_name', 
            'architect.tele_verified', 
            'architect.tele_not_verified', 
            'architect.instagram_link', 
            'architect.data_verified', 
            'architect.data_not_verified', 
            'architect.missing_data', 
            'architect.source_type_value', 
            'architect.source_type', 
            'architect.total_point', 
            'architect.joining_date', 
            'architect.firm_name', 
            'created_by_user.first_name as created_by_user_first_name', 
            'created_by_user.last_name as created_by_user_last_name',
            'city_list.name as city_name'];

        $query = Architect::query();
        $query->select($selectColumns);
        $query->leftJoin('users', 'users.id', '=', 'architect.user_id');
        $query->leftJoin('users as sale_person', 'architect.sale_person_id', '=', 'sale_person.id');
        $query->leftJoin('users as created_by_user', 'users.created_by', '=', 'created_by_user.id');
        $query->leftJoin('city_list', 'city_list.id', '=', 'users.city_id');
        $query->whereIn('architect.type', [201, 202]);

        $arr_where_clause = [];
        $arr_or_clause = [];
        $sortingColumns = array();
        $newdatares = [];
        $date_condtion = '';
        $isorderby = 0;

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
                    $column = getArchitectFilterColumn()[$filt_value['column']];
                    $condtion = getUserFilterCondtion()[$filt_value['condtion']];
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
                        if ($column['code'] == "user_source" && ($filt_value['value_source_type'] == null || $filt_value['value_source_type'] == '')) {
                            $response = errorRes("Please select value");
                            return response()->json($response)->header('Content-Type', 'application/json');
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
                        if ($column['code'] == "user_source" && ($filt_value['value_source_type'] == null || $filt_value['value_source_type'] == '')) {
                            $response = errorRes("Please select value");
                            return response()->json($response)->header('Content-Type', 'application/json');
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
                    } elseif ($column['value_type'] == 'select_order_by' && $condtion['value_type'] == 'single_select') {
                        $isorderby = 1;
                        if ($filt_value['value_select'] == null || $filt_value['value_select'] == '') {
                            $response = errorRes('Please enter from to date');
                            return response()
                                ->json($response)
                                ->header('Content-Type', 'application/json');
                        } else {
                            $filter_value = $filt_value['value_select'];
                        }
                    }

                    if ($filt_value['clause'] != 0) {
                        $clause = getUserFilterClause()[$filt_value['clause']];
                    }

                    if ($filt_value['clause'] == 0) {
                        $newdata['clause'] = 0;
                        $newdata['column'] = $column['id'];
                        $newdata['condtion'] = $condtion['id'];
                        $newdata['value'] = $filter_value;
                        $newdata['source_type'] = $source_type;

                        $newdatares = $newdata;

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

            foreach ($arr_where_clause as $wherekey => $objwhere) {
                $Column = getArchitectFilterColumn()[$objwhere['column']];
                $Condtion = getUserFilterCondtion()[$objwhere['condtion']];
                $lstDateFilter = getUserDateFilterValue();
                $Filter_Value = $objwhere['value'];
                $source_type = $objwhere['source_type'];

                if ($Condtion['code'] == 'is') {
                    if ($Column['value_type'] == 'date') {
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
                        } else {
                            $date_filter_value = explode(',', $objDateFilter['value']);
                            $from_date_filter = $date_filter_value[0];
                            $from_date_filter_value = date('Y-m-d', strtotime($from_date_filter));

                            $to_date_filter = $date_filter_value[1];
                            $to_date_filter_value = date('Y-m-d', strtotime($to_date_filter));

                            $query->whereBetween(DB::raw('DATE(' . $Column['column_name'] . ')'), [$from_date_filter_value, $to_date_filter_value]);
                        }
                    } elseif ($Column['value_type'] == 'select') {
                        if ($Column['code'] == 'user_source') {
                            $query->where($Column['column_name'], $source_type);
                        } else {
                            $query->where($Column['column_name'], $Filter_Value);
                        }
                    } elseif ($Column['value_type'] == 'select_order_by') {
                        $newSortingColumns['column'] = $Column['column_name'];
                        
                        if ($Filter_Value == 1) {
                            $newSortingColumns['sort'] = 'DESC';
                            // $query->orderBy($Column['column_name'], 'DESC');
                        } else {
                            $newSortingColumns['sort'] = 'ASC';
                            // $query->orderBy($Column['column_name'], 'ASC');
                        }
                        array_push($sortingColumns,$newSortingColumns);
                    } else {
                        $query->where($Column['column_name'], 'like', '%' . $Filter_Value . '%');
                    }
                } elseif ($Condtion['code'] == 'is_not') {
                    if ($Column['value_type'] == 'date') {
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
                        } else {
                            $date_filter_value = explode(',', $objDateFilter['value']);
                            $from_date_filter = $date_filter_value[0];
                            $from_date_filter_value = date('Y-m-d', strtotime($from_date_filter));

                            $to_date_filter = $date_filter_value[1];
                            $to_date_filter_value = date('Y-m-d', strtotime($to_date_filter));

                            $query->whereNotBetween(DB::raw('DATE(' . $Column['column_name'] . ')'), [$from_date_filter_value, $to_date_filter_value]);
                        }
                    } elseif ($Column['value_type'] == 'select') {
                        if ($Column['code'] == 'user_source') {
                            $query->where($Column['column_name'], '!=', $source_type);
                        } else {
                            $query->where($Column['column_name'], '!=', $Filter_Value);
                        }
                    } else {
                        $query->where($Column['column_name'], 'not like', '%' . $Filter_Value . '%');
                    }
                } elseif ($Condtion['code'] == 'contains') {
                    if ($Column['value_type'] == 'select') {
                        if ($Column['code'] == 'user_source') {
                            $query->whereIn($Column['column_name'], $source_type);
                        } else {
                            $query->whereIn($Column['column_name'], $Filter_Value);
                        }
                    } else {
                        $Filter_Value = explode(',', $Filter_Value);
                        $query->whereIn($Column['column_name'], $Filter_Value);
                    }
                } elseif ($Condtion['code'] == 'not_contains') {
                    if ($Column['value_type'] == 'select') {
                        if ($Column['code'] == 'user_source') {
                            $query->whereNotIn($Column['column_name'], $source_type);
                        } else {
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
                        $Column = getArchitectFilterColumn()[$objor['column']];
                        $Condtion = getUserFilterCondtion()[$objor['condtion']];
                        $lstDateFilter = getUserDateFilterValue();
                        $Filter_Value = $objor['value'];
                        $source_type = $objor['source_type'];
                        
                        if ($Condtion['code'] == 'is') {
                            if ($Column['value_type'] == 'date') {
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
                                if ($Column['code'] == 'user_source') {
                                    $query->orWhere($Column['column_name'], $source_type);
                                } else {
                                    $query->orWhere($Column['column_name'], $Filter_Value);
                                }
                            } elseif ($Column['value_type'] == 'select_order_by') {
                                $newSortingColumns['column'] = $Column['column_name'];
                                if ($Filter_Value == 1) {
                                    $newSortingColumns['sort'] = 'DESC';
                                } else {
                                    $newSortingColumns['sort'] = 'ASC';
                                }
                                array_push($sortingColumns,$newSortingColumns);
                            } else {
                                $query->orWhere($Column['column_name'], 'like', '%' . $Filter_Value . '%');
                            }
                        } elseif ($Condtion['code'] == 'is_not') {
                            if ($Column['value_type'] == 'date') {
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
                                if ($Column['code'] == 'user_source') {
                                    $query->orWhere($Column['column_name'], '!=', $source_type);
                                } else {
                                    $query->orWhere($Column['column_name'], '!=', $Filter_Value);
                                }
                            } else {
                                $query->orWhere($Column['column_name'], 'not like', '%' . $Filter_Value . '%');
                            }
                        } elseif ($Condtion['code'] == 'contains') {
                            if ($Column['value_type'] == 'select') {
                                if ($Column['code'] == 'user_source') {
                                    $query->orWhereIn($Column['column_name'], $Filter_Value);
                                } else {
                                    $query->orWhereIn($Column['column_name'], $Filter_Value);
                                }
                            } else {
                                $Filter_Value = explode(',', $Filter_Value);
                                $query->orWhereIn($Column['column_name'], $Filter_Value);
                            }
                        } elseif ($Condtion['code'] == 'not_contains') {
                            if ($Column['code'] == 'user_source') {
                                $query->orWhereNotIn($Column['column_name'], $Filter_Value);
                            }

                            if ($Column['value_type'] == 'select') {
                                $query->orWhereNotIn($Column['column_name'], $Filter_Value);
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

        if (isset($request->status)) {
            if ($request->status == 100) {
                $query->whereIn('architect.status', [0, 1, 2, 3, 4, 5]);
            } else {
                $query->where('architect.status', $request->status);
            }
        }

        if ($isSalePerson == 1) {
            $query->whereIn('architect.sale_person_id', getChildSalePersonsIds(Auth::user()->id));
        } elseif ($isChannelPartner != 0) {
            $ObjChannelPartner = ChannelPartner::select('sale_persons')->where('user_id', Auth::user()->id)->first();
            if($ObjChannelPartner){
                $query->where('architect.sale_person_id', $ObjChannelPartner->sale_persons);
            }
        } 
        $Filter_lead_ids = $query->distinct()->pluck('users.id');

        $recordsTotal = $query->distinct()->pluck('users.id')->count();
        $recordsFiltered = $recordsTotal;


        // RECORDSFILTERED START
        $query = Architect::query();
        $query->select($selectColumns);
        $query->leftJoin('users', 'users.id', '=', 'architect.user_id');
        $query->leftJoin('users as created_by_user', 'users.created_by', '=', 'created_by_user.id');
        $query->leftJoin('users as sale_person', 'architect.sale_person_id', '=', 'sale_person.id');
        $query->leftJoin('city_list', 'city_list.id', '=', 'users.city_id');
        $query->whereIn('users.id', $Filter_lead_ids);
        $search_value = '';
        if (isset($request['search']['value'])) {
            $isFilterApply = 1;
            $search_value = $request['search']['value'];
            $query->where(function ($query) use ($search_value, $searchColumns) {
                for ($i = 0; $i < count($searchColumns); $i++) {
                    if ($i == 0) {
                        $query->whereRaw($searchColumns[$i] . ' like ? ', [$search_value]);
                        $query->orWhereRaw($searchColumns[$i] . ' like ? ', ['%' . $search_value . '%']);
                    } else {
                        $query->orWhereRaw($searchColumns[$i] . ' like ? ', [$search_value]);
                        $query->orWhereRaw($searchColumns[$i] . ' like ? ', ['%' . $search_value . '%']);
                    }
                }
            });
        }
        $recordsFiltered = $query->count();
        // RECORDSFILTERED START

        $query = Architect::query();
        $query->select($selectColumns);
        $query->leftJoin('users', 'users.id', '=', 'architect.user_id');
        $query->leftJoin('users as created_by_user', 'users.created_by', '=', 'created_by_user.id');
        $query->leftJoin('users as sale_person', 'architect.sale_person_id', '=', 'sale_person.id');
        $query->leftJoin('city_list', 'city_list.id', '=', 'users.city_id');
        $query->whereIn('users.id', $Filter_lead_ids);
        $query->limit($request->length);
        $query->offset($request->start);
        if ($isorderby == 0) {
            $query->orderBy('users.id', 'DESC');
        }else {
            foreach ($sortingColumns as $key => $value) {
                $query->orderBy($value['column'], $value['sort']);
            }
        }
        $isFilterApply = 0;

        if (isset($request['search']['value'])) {
            $isFilterApply = 1;
            $search_value = $request['search']['value'];
            $query->where(function ($query) use ($search_value, $searchColumns) {
                for ($i = 0; $i < count($searchColumns); $i++) {
                    if ($i == 0) {
                        $query->whereRaw($searchColumns[$i] . ' like ? ', [$search_value]);
                        $query->orWhereRaw($searchColumns[$i] . ' like ? ', ['%' . $search_value . '%']);
                    } else {
                        $query->orWhereRaw($searchColumns[$i] . ' like ? ', [$search_value]);
                        $query->orWhereRaw($searchColumns[$i] . ' like ? ', ['%' . $search_value . '%']);
                    }
                }
            });
        }

        
        $data = $query->get();

        $data = json_decode(json_encode($data), true);
        // if($isFilterApply === 1){
        //     $recordsFiltered = count($data);
        // }

        $viewData = [];
        $ArchitectCategory = ArchitectCategory::orderBy('id', 'asc')->get();
        foreach ($data as $key => $value) {
            $viewData[$key] = [];
            $routeArchitects = route('new.architects.index') . '?id=' . $value['id'];
            $viewData[$key]['id'] = '<div class="avatar-xs"><span class="avatar-title rounded-circle">' . highlightString($value['id'],$search_value) . '</span></div>';
            if ($value['type'] == 201) {
                $viewData[$key]['name'] = '<a onclick="" target="_blank" href="' . $routeArchitects . '"><b>' . '#' . highlightString($value['id'],$search_value) . '  ' . '</b>' . highlightString(ucwords(strtolower($value['first_name'])) . ' ' . ucwords(strtolower($value['last_name'])),$search_value) . '</a>' . '<p class="text-muted mb-0">' . highlightString($value['firm_name'],$search_value) . '</p>';
            } else {
                $viewData[$key]['name'] = '<a onclick="" target="_blank" href ="' . $routeArchitects . '"><b>' . '#' . highlightString($value['id'],$search_value) . '  ' . '</b>' . highlightString(ucwords(strtolower($value['first_name'])) . ' ' . ucwords(strtolower($value['last_name'])),$search_value) . '</a><span class="badge rounded-pill bg-success ms-2">PRIME</span>' . '<p class="text-muted mb-0">' . highlightString($value['firm_name'],$search_value) . '</p>';
            }
            $viewData[$key]['firm_name'] = '<p class="text-muted mb-0">' . highlightString($value['firm_name'],$search_value) . '</p>';
            $viewData[$key]['city_name'] = '<p class="text-muted mb-0">' . highlightString($value['city_name'],$search_value) . '</p>';
            $viewData[$key]['points'] = 'Current : <a onclick="pointLogs(' . $value['id'] . ')" href="javascript: void(0);" title="Detail">' . $value['total_point_current'] . '</a><br>Lifetime : <a onclick="pointLogs(' . $value['id'] . ')" href="javascript: void(0);" title="Detail">' . $value['total_point'] . '</a>';
            
            $LeadWonCount = Lead::query();
            $LeadWonCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
			$LeadWonCount->where('lead_sources.source', $value['id']);
			$LeadWonCount->where('leads.status', 103);
            $LeadWonCount = $LeadWonCount->distinct()->pluck('leads.id')->count();
            
            $LeadLostCount = Lead::query();
            $LeadLostCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
			$LeadLostCount->where('lead_sources.source', $value['id']);
			$LeadLostCount->whereIn('leads.status', [5,104]);
            $LeadLostCount = $LeadLostCount->distinct()->pluck('leads.id')->count();
            
            $LeadRunningCount = Lead::query();
            $LeadRunningCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
			$LeadRunningCount->where('lead_sources.source', $value['id']);
			$LeadRunningCount->whereIn('leads.status', [1,2,3,4,100,101,102]);
            $LeadRunningCount = $LeadRunningCount->distinct()->pluck('leads.id')->count();
            
            $LeadTotalCount = Lead::query();
            $LeadTotalCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
			$LeadTotalCount->where('lead_sources.source', $value['id']);
            $LeadTotalCount = $LeadTotalCount->distinct()->pluck('leads.id')->count();
            
            $viewData[$key]['won_lead'] = '<p class="text-muted mb-0">' . $LeadWonCount . '</p>';
            $viewData[$key]['lost_lead'] = '<p class="text-muted mb-0">' . $LeadLostCount . '</p>';
            $viewData[$key]['running_lead'] = '<p class="text-muted mb-0">' . $LeadRunningCount . '</p>';
            $viewData[$key]['total_lead'] = '<p class="text-muted mb-0">' . $LeadTotalCount . '</p>';

            // $valueCreatedTime = convertDateTime($value['created_at']);
            // $valueJoiningDate = convertDateTime($value['joining_date']);
            
            // $UserUpdateCount = UserUpdate::where('for_user_id', $value['id'])
            //     ->orderBy('id', 'desc')
            //     ->count();

            // $viewData[$key] = [];
            // $routeArchitects = route('new.architects.index') . '?id=' . $value['id'];
            // $viewData[$key]['id'] = '<div class="avatar-xs"><span class="avatar-title rounded-circle">' . highlightString($value['id'],$search_value) . '</span></div>';
            // if ($value['type'] == 201) {
            //     $viewData[$key]['name'] = '<a onclick="" target="_blank" href="' . $routeArchitects . '"><b>' . '#' . highlightString($value['id'],$search_value) . '  ' . '</b>' . highlightString(ucwords(strtolower($value['first_name'])) . ' ' . ucwords(strtolower($value['last_name'])),$search_value) . '</a>';
            // } else {
            //     $viewData[$key]['name'] = '<a onclick="" target="_blank" href ="' . $routeArchitects . '"><b>' . '#' . highlightString($value['id'],$search_value) . '  ' . '</b>' . highlightString(ucwords(strtolower($value['first_name'])) . ' ' . ucwords(strtolower($value['last_name'])),$search_value) . '</a><span class="badge rounded-pill bg-success ms-2">PRIME</span>';
            // }

            // $viewData[$key]['email'] = '<p class="text-muted mb-0">' . highlightString($value['email'],$search_value) . '</p><p class="text-muted mb-0">' . highlightString($value['phone_number'],$search_value) . '</p>';
            // $viewData[$key]['status'] = getArchitectsStatusLable($value['architect_status']);


            // $isCategoryEditable = 'pe-none';
            // if($isAdminOrCompanyAdmin == 1 || $isCreUser == 1 || $isTaleSalesUser == 1 || isChannelPartner(Auth::user()->type) == 101){
            //     $isCategoryEditable = '';
            // }
            
            // if(isCreUser() == 0) {
            //     $ArchitectCategoryUI = '<div class="'.$isCategoryEditable.'" ><select class="select-category pe-none" id="selectCategory_' . $value['id'] . '" >';
            //     $ArchitectCategoryUI .= '<option value="0">-SELECT-</option>';
            //     foreach ($ArchitectCategory as $keyC => $valueC) {
            //         if ($valueC['id'] == $value['category_id']) {
            //             $ArchitectCategoryUI .= '<option selected value="' . $valueC['id'] . '">' . $valueC['name'] . '</option>';
            //         } else {
            //             $ArchitectCategoryUI .= '<option value="' . $valueC['id'] . '">' . $valueC['name'] . '</option>';
            //         }
            //     }
            //     $ArchitectCategoryUI .= '<select> </div>';
            // } else {
            //     $ArchitectCategoryUI = '<span>'.$value['category_id'].'</span>';
            // }

			// $viewData[$key]['category'] = $ArchitectCategoryUI;
            
            // $viewData[$key]['firm_name'] = '<p class="text-muted mb-0">' . highlightString($value['firm_name'],$search_value) . '</p>';
            // $viewData[$key]['points'] = 'Current : <a onclick="pointLogs(' . $value['id'] . ')" href="javascript: void(0);" title="Detail">' . $value['total_point_current'] . '</a><br>Lifetime : <a onclick="pointLogs(' . $value['id'] . ')" href="javascript: void(0);" title="Detail">' . $value['total_point'] . '</a>';

            // $source_type = explode('-', $value['source_type']);
            // if ($source_type[0] == 'user') {
            //     if (isChannelPartner($source_type[1]) != 0) {
            //         $User1 = User::select('users.id', DB::raw('channel_partner.firm_name'));
            //         $User1->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
            //         $User1->where('users.id', $value['source_type_value']);
            //         $User1->limit(1);
            //         $User1 = $User1->first();
            //         if ($User1) {
            //             $viewData[$key]['reference'] = '<p class="text-muted mb-0">' . highlightString($User1->firm_name,$search_value) . '</p>';
            //         } else {
            //             $viewData[$key]['reference'] = '<p class="text-muted mb-0">-</p>';
            //         }
            //     } else {
            //         $User1 = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
            //         $User1->where('id', $value['source_type_value']);
            //         $User1->limit(1);
            //         $User1 = $User1->first();
            //         if ($User1) {
            //             $viewData[$key]['reference'] = '<p class="text-muted mb-0">' . highlightString($User1->full_name,$search_value) . '</p>';
            //         } else {
            //             $viewData[$key]['reference'] = '<p class="text-muted mb-0">-</p>';
            //         }
            //     }
            //     // $viewData[$key]['reference'] = '<p class="text-muted mb-0">' . $User->full_name . '</p>';
            // } else {
            //     $viewData[$key]['reference'] = '<p class="text-muted mb-0">' . highlightString($value['source_type_value'],$search_value) . '</p>';
            // }
            // $viewData[$key]['account_owner'] = '<p class="text-muted mb-0">' . highlightString($value['sale_person_first_name'] . ' ' . $value['sale_person_last_name'],$search_value) . '</p>';
            // $viewData[$key]['created_by'] = '<span class="text-muted mb-0">' . highlightString($value['created_by_user_first_name'] . ' ' . $value['created_by_user_last_name'],$search_value) . ' </span><br><span><a data-bs-toggle="tooltip" data-bs-html="true"  href="javascript: void(0);" title="Created Date & Time : <br>' . $valueCreatedTime . '<br><br>Joining Date : <br>' . $valueJoiningDate . '" class="float-start h4 mb-0"><i class="bx bx-calendar"></i></a><span>';
            // $viewData[$key]['account_status'] = getUserStatusLable($value['account_status']);

            $uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';

            if(isCreUser() == 0) 
            {
                if ($value['architect_status'] == 2) {
                    // if (isTaleSalesUser() == 1) {
                    $uiAction .= '<li class="list-inline-item px-2">';
                    $uiAction .= '<a href="javascript:void(0)" onclick="getArchitectsDetails(' . $value['id'] . ')"><img src="' . asset('assets/images/pending-request.svg') . '" alt="" ></a>';
                    $uiAction .= '</li>';
                    // }
                } elseif ($value['architect_status'] == 4) {
                    $uiAction .= '<li class="list-inline-item px-2">';
                    $uiAction .= '<img src="' . asset('assets/images/order-approve.svg') . '" alt="" >';
                    $uiAction .= '</li>';

                    if (isAdminOrCompanyAdmin() == 1) {
                        $uiAction .= '<li class="list-inline-item px-2">';
                        $uiAction .= '<a href="javascript:void(0)" onclick="getArchitectsDetails(' . $value['id'] . ')"><img src="' . asset('assets/images/telecaller-approved.svg') . '" alt="" ></a>';
                        $uiAction .= '</li>';
                    }
                } elseif ($value['architect_status'] == 1) {
                    $uiAction .= '<li class="list-inline-item px-2">';
                    $uiAction .= '<img src="' . asset('assets/images/order-approve.svg') . '" alt="" >';
                    $uiAction .= '</li>';

                    $uiAction .= '<li class="list-inline-item px-2">';
                    $uiAction .= '<img src="' . asset('assets/images/order-approve.svg') . '" alt="" >';
                    $uiAction .= '</li>';
                } elseif ($value['architect_status'] == 3) {
                    $uiAction .= '<li class="list-inline-item px-2">';
                    $uiAction .= '<a href="javascript:void(0)" onclick="getArchitectsDetails(' . $value['id'] . ')"><img src="' . asset('assets/images/pending-request.svg') . '" alt="" ></a>';
                    $uiAction .= '</li>';
                }
            }

            $uiAction .= '</ul>';
            $viewData[$key]['action'] = $uiAction;
        }

        $jsonData = [
            'draw' => intval($request['draw']),
            'recordsTotal' => intval($recordsTotal),
            'recordsFiltered' => intval($recordsFiltered),
            'data' => $viewData,
            'where' => $arr_where_clause,
            'or' => $arr_or_clause,
        ];
        return $jsonData;
    }
    public function searchSalePerson(Request $request)
    {
        $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
        $isSalePerson = isSalePerson();
        $isChannelPartner = isChannelPartner(Auth::user()->type);
        $isMarketingDispatcherUser = isMarketingDispatcherUser();

        $searchKeyword = isset($request->q) ? $request->q : '';

        if ($isAdminOrCompanyAdmin == 1 || $isMarketingDispatcherUser == 1) {
            $SalePerson = SalePerson::query();
            $SalePerson->leftJoin('users', 'sale_person.user_id', '=', 'users.id');
            $SalePerson->select('users.id as id', DB::raw('CONCAT(first_name," ", last_name) AS text'));
            $SalePerson->where('users.status', 1);
            $SalePerson->where(function ($query) use ($searchKeyword) {
                $query->where('users.first_name', 'like', '%' . $searchKeyword . '%');
                $query->orWhere('users.last_name', 'like', '%' . $searchKeyword . '%');
            });
            $SalePerson->limit(5);
            $SalePerson = $SalePerson->get();
            $response = [];
            $response['results'] = $SalePerson;
            $response['pagination']['more'] = false;
        } elseif ($isSalePerson == 1) {
            $childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
            $SalePerson = SalePerson::query();
            $SalePerson->leftJoin('users', 'sale_person.user_id', '=', 'users.id');
            $SalePerson->select('users.id as id', DB::raw('CONCAT(first_name," ", last_name) AS text'));
            $SalePerson->where('users.status', 1);
            $SalePerson->whereIn('users.id', $childSalePersonsIds);
            $SalePerson->where(function ($query) use ($searchKeyword) {
                $query->where('users.first_name', 'like', '%' . $searchKeyword . '%');
                $query->orWhere('users.last_name', 'like', '%' . $searchKeyword . '%');
            });
            $SalePerson->limit(5);
            $SalePerson = $SalePerson->get();
            $response = [];
            $response['results'] = $SalePerson;
            $response['pagination']['more'] = false;

        }elseif ($isChannelPartner != 0) {
            $salesPersonIds = getChannelPartnerSalesPersonsIds(Auth::user()->id);

            $SalePerson = SalePerson::query();
            $SalePerson->leftJoin('users', 'sale_person.user_id', '=', 'users.id');
            $SalePerson->select('users.id as id', DB::raw('CONCAT(first_name," ", last_name) AS text'));
            $SalePerson->where('users.status', 1);
            $SalePerson->whereIn('users.id', $salesPersonIds);

            $SalePerson->where(function ($query) use ($searchKeyword) {
                $query->where('users.first_name', 'like', '%' . $searchKeyword . '%');
                $query->orWhere('users.last_name', 'like', '%' . $searchKeyword . '%');
            });
            $SalePerson->limit(5);
            $SalePerson = $SalePerson->get();

            $response = [];
            $response['results'] = $SalePerson;
            $response['pagination']['more'] = false;
        } else {
            $response = '';
        }

        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }
    public function save(Request $request)
    {
        $user_id = isset($request->user_id) ? $request->user_id : 0;

        $isSalePerson = isSalePerson();
        $isChannelPartner = isChannelPartner(Auth::user()->type);
        $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
        $isMarketingDispatcherUser = isMarketingDispatcherUser();
        $customMessage = [];
        if(isset($request->user_status) && !in_array($request->user_status, [0, 5, 7, 8])) {
            $rules = [];
            $rules['user_id'] = 'required';
            $rules['user_type'] = 'required';
            $rules['user_first_name'] = 'required';
            $rules['user_last_name'] = 'required';
            $rules['user_phone_number'] = 'required|digits:10|regex:/^[1-9][0-9]*$/';
            if ($request->user_id == 0) {
                $rules['user_house_no'] = 'required';
                $rules['user_area'] = 'required';
            }
            $rules['user_address_line1'] = 'required';
            $rules['user_pincode'] = 'required';
            $rules['user_city_id'] = 'required';
            $rules['architect_firm_name'] = 'required';

            if($user_id != 0 && isset($request->user_status)) {
                $rules['architect_note'] = 'required';
            }


            if ($isSalePerson == 0) {
                $rules['architect_sale_person_id'] = 'required';
            }

            if (isset($request->user_status) && $request->user_id != 0) {
                $rules['user_status'] = 'required';
                // $rules['architect_recording'] = 'required';
            }

            if ($user_id != 0 && $isAdminOrCompanyAdmin == 0) {
                $User = User::find($request->user_id);
                if ($User) {
                    if ($User->type == 202 && $request->user_type == 201) {
                        $response = errorRes("user can't convert prime to non-prime please contact to admin !");
                        return response()
                            ->json($response)
                            ->header('Content-Type', 'application/json');
                    }
                }
            }

            if ($request->user_type == 202) {
                $rules['user_email'] = 'required|email:rfc,dns';
                if ($request->user_id == 0) {
                    $rules['user_house_no'] = 'required';
                    $rules['user_area'] = 'required';
                }
                $rules['user_address_line1'] = 'required';
                $rules['user_pincode'] = 'required';
                $rules['user_city_id'] = 'required';
            }

            if ($isChannelPartner == 0 && isTaleSalesUser() == 0 && $user_id == 0) {
                $rules['architect_source_type'] = 'required';
            }

            if ($isSalePerson == 1 && $request->user_type == 202 && ($request->user_id != 0 && $request->user_id != '')) {
                $rules = [];
                $rules['user_id'] = 'required';
                $rules['user_type'] = 'required';
                $User = User::find($request->user_id);
                $requireFieldForSalesUser = [];

                if ($User) {
                    if ($User->pincode == '') {
                        $rules['user_pincode'] = 'required';
                        $requireFieldForSalesUser[] = 'user_pincode';
                    }

                    if ($User->address_line1 == '') {
                        $rules['user_address_line1'] = 'required';
                        $requireFieldForSalesUser[] = 'user_address_line1';
                    }

                    if ($request->user_type == 202) {
                        if ($User->email == '') {
                            $rules['user_email'] = 'required|email:rfc,dns';
                            $requireFieldForSalesUser[] = 'user_email';
                        }
                    }
                }
            }

            $customMessage = [];
            $customMessage['user_id.required'] = 'User Id Required Contact To Admin';
            $customMessage['user_type.required'] = 'Invalid type';
            $customMessage['user_first_name.required'] = 'Please enter first name';
            $customMessage['user_last_name.required'] = 'Please enter last name';
            if ($request->user_id == 0) {
                $customMessage['user_house_no.required'] = 'Please enter house number';
                $customMessage['user_area.required'] = 'Please enter area';
            }
            $customMessage['user_address_line1.required'] = 'Please enter address_line1';
            $customMessage['user_pincode.required'] = 'Please enter pincode';
            $customMessage['user_city_id.required'] = 'Please select city';
            // $customMessage['user_country_id.required'] = 'Please select country';
            // $customMessage['user_state_id.required'] = 'Please select state';
            if (isTaleSalesUser() == 0 && $user_id == 0) {
                $customMessage['architect_source_type.required'] = 'Please select reference type';
            }
            $customMessage['architect_sale_person_id.required'] = 'Please select sale person';
        } else if(isset($request->user_status) && $request->user_status == 5) {
            
            $rules = [];
            if (isset($request->user_status) && $request->user_id != 0) {
                $rules['duplicate_from'] = 'required';
                $customMessage['duplicate_from.required'] = 'Please Select Duplicate Value';
            }
            
        } else {
            $rules = [];
        }
        $validator = Validator::make($request->all(), $rules, $customMessage);
        
        if ($validator->fails()) {
            $response = [];
            $response['status'] = 0;
            $response['msg'] = $validator->errors()->first();
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors()->first();
        } else {
            $AllUserTypes = getAllUserTypes();

            if ($isSalePerson == 1 && $request->user_type == 202 && ($request->user_id != 0 && $request->user_id != '')) {
                $converted_prime2 = 1;
                if (count($requireFieldForSalesUser) > 0) {
                    if ($request->user_type == 202) {
                        if (in_array('user_email', $requireFieldForSalesUser)) {
                            $alreadyEmail = User::query();
                            $alreadyEmail->where('email', $request->user_email);
                            $alreadyEmail->where('id', '!=', $request->user_id);
                            $alreadyEmail = $alreadyEmail->first();

                            if ($alreadyEmail) {
                                $response = errorRes('Email already exists(' . $AllUserTypes[$alreadyEmail->type]['name'] . '), Try with another email');
                                return response()
                                    ->json($response)
                                    ->header('Content-Type', 'application/json');
                            } else {
                                $User->email = $request->user_email;
                            }
                        }
                    }

                    if (in_array('user_address_line1', $requireFieldForSalesUser)) {
                        $User->address_line1 = $request->user_address_line1;
                    }
                    if (in_array('user_pincode', $requireFieldForSalesUser)) {
                        $User->pincode = $request->user_pincode;
                    }

                    $User->type = $request->user_type;
                    $User->save();
                    $Architect = Architect::find($User->reference_id);

                    if ($request->user_id != 0 && $request->user_type == 202 && $Architect->converted_prime == 0) {
                        $Architect->prime_nonprime_date = date('Y-m-d H:i:s');
                        $converted_prime2 = 0;
                    } else {
                        $converted_prime2 = 1;
                    }
                    if ($request->user_type == 202) {
                        $Architect->converted_prime = 1;
                    }
                    $Architect->type = $request->user_type;
                    $Architect->save();
                    $debugLog['name'] = 'architect-edit';
                    $debugLog['description'] = 'user #' . $User->id . '(' . $User->first_name . ' ' . $User->last_name . ') has been updated ';
                    $response = successRes('Successfully saved user');
                    saveDebugLog($debugLog);
                } else {
                    $User->type = $request->user_type;
                    $User->save();
                    $Architect = Architect::find($User->reference_id);
                    if ($request->user_id != 0 && $request->user_type == 202 && $Architect->converted_prime == 0) {
                        $Architect->prime_nonprime_date = date('Y-m-d H:i:s');
                        $converted_prime2 = 0;
                    } else {
                        $converted_prime2 = 1;
                    }
                    if ($request->user_type == 202) {
                        $Architect->converted_prime = 1;
                    }
                    $Architect->type = $request->user_type;
                    $Architect->save();
                    $debugLog['name'] = 'architect-edit';
                    $debugLog['description'] = 'user #' . $User->id . '(' . $User->first_name . ' ' . $User->last_name . ') has been updated ';
                    $response = successRes('Successfully saved user');
                    saveDebugLog($debugLog);
                }

                if (($request->user_id == 0 && $request->user_type == 202) || ($request->user_id != 0 && $request->user_type == 202 && $converted_prime2 == 0)) {
                    $pointValue = 50;
                    $Architect->total_point = $Architect->total_point + $pointValue;
                    $Architect->total_point_current = $Architect->total_point_current + $pointValue;
                    $Architect->save();

                    $debugLog = [];
                    $debugLog['for_user_id'] = $Architect->user_id;
                    $debugLog['name'] = 'point-gain';
                    $debugLog['points'] = $pointValue;
                    $debugLog['description'] = $pointValue . ' Point gained joining bonus ';
                    $debugLog['type'] = '';
                    saveCRMUserLog($debugLog);

                    $configrationForNotify = configrationForNotify();

                    $query = CRMHelpDocument::query();
                    $query->where('status', 1);
                    $query->where('type', 202);
                    $query->limit(30);
                    $query->orderBy('publish_date_time', 'desc');
                    $helpDocuments = $query->get();

                    $file = "";
                    foreach ($helpDocuments as $new_helpDocument) {
                        $file .= $new_helpDocument['file_name'].', ';
                    }

                    $Mailarr = [];
                    $Mailarr['from_name'] = $configrationForNotify['from_name'];
                    $Mailarr['from_email'] = $configrationForNotify['from_email'];
                    $Mailarr['to_email'] = $User->email;
                    $Mailarr['to_name'] = $configrationForNotify['to_name'];
                    $Mailarr['bcc_email'] = "sales@whitelion.in, sc@whitelion.in, poonam@whitelion.in";
                    $Mailarr['cc_email'] = "";
                    $Mailarr['subject'] = 'Welcome to the Whitelion';
                    $Mailarr['transaction_id'] = $User->id;
                    $Mailarr['transaction_name'] = "Architect";
                    $Mailarr['transaction_type'] = "Email";
                    $Mailarr['transaction_detail'] = "emails.signup_architect";
                    $Mailarr['attachment'] = rtrim($file, ", ");
                    $Mailarr['remark'] = "Architect Create";
                    $Mailarr['source'] = "Web";
                    $Mailarr['entryip'] = $request->ip();
                    
                    if (Config::get('app.env') == 'local') {
                        $Mailarr['to_email'] = $configrationForNotify['test_email'];
                        $Mailarr['bcc_email'] = implode(', ', $configrationForNotify['test_email_bcc']);
                    }

                    saveNotificationScheduler($Mailarr);

                    
                    $helpDocument = CRMHelpDocument::query();
					$helpDocument->where('status', 1);
					$helpDocument->where('type', 202);
					$helpDocument->orderBy('publish_date_time', "desc");
					$helpDocument = $helpDocument->first();
					//TEMPLATE 7
					$whatsapp_controller = new WhatsappApiContoller;
					$perameater_request = new Request();
					$perameater_request['q_whatsapp_massage_mobileno'] = $User->phone_number;
					$perameater_request['q_whatsapp_massage_template'] = 'for_architect_download_mobileapp';
					$perameater_request['q_whatsapp_massage_attechment'] = $helpDocument ? getSpaceFilePath($helpDocument->file_name) : '';
					$perameater_request['q_broadcast_name'] = $User->first_name . ' ' . $User->last_name;
					$perameater_request['q_whatsapp_massage_parameters'] = array(
						'data[0]' => $User->email,
						'data[1]' => '111111'
					);
					$wp_response = $whatsapp_controller->sendTemplateMessage($perameater_request);
					$response["whatsapp"] = $wp_response;
                    
                    $response = successRes('Successfully saved user');

                }

                return response()
                    ->json($response)
                    ->header('Content-Type', 'application/json');
            }

            $source_type_value = '';
            $principal_architect_name = isset($request->architect_principal_architect_name) ? $request->architect_principal_architect_name : '';
            $instagram_link = isset($request->architect_instagram) ? $request->architect_instagram : '';
            // $data_verified = isset($request->architect_data_verified) ? $request->architect_data_verified : 0;
            // $data_not_verified = isset($request->architect_data_not_verified) ? $request->architect_data_not_verified : 0;
            // $missing_data = isset($request->architect_missing_data) ? $request->architect_missing_data : 0;
            // $tele_verified = isset($request->architect_tele_verified) ? $request->architect_tele_verified : 0;
            // $tele_not_verified = isset($request->architect_tele_not_verified) ? $request->architect_tele_not_verified : 0;

            if ($isChannelPartner == 0) {
                $source_type = $request->architect_source_type;
                $source_type_pieces = explode('-', $source_type);

                if((isset($request->user_status) && !in_array($request->user_status, [5, 7, 8])) || !isset($request->user_status)) {
                    if ($source_type_pieces[0] == 'user') {
                        if (!isset($request->architect_source_name) || $request->architect_source_name == '') {
                            $response = errorRes('Please select source');
                            return response()->json($response)->header('Content-Type', 'application/json');
                        }

                        $source_type_value = $request->architect_source_name;
                    } elseif ($source_type_pieces[0] == 'textrequired') {
                        if (!isset($request->architect_source_text) || $request->architect_source_text == '') {
                            $response = errorRes('Please enter source text');
                            return response()->json($response)->header('Content-Type', 'application/json');
                        }
    
                        $source_type_value = $request->architect_source_text;
                    } else {
                        $source_type_value = isset($request->architect_source_text) ? $request->architect_source_text : '';
                    }
                } else {
                    $source_type_value = isset($request->architect_source_text) ? $request->architect_source_text : '';
                }
                
            } elseif ($isChannelPartner != 0) {
                $source_type_value = '';
                $source_type = '';
            }

            if ($isSalePerson == 1) {
                $sale_person_id = Auth::user()->id;
            } else {
                $sale_person_id = $request->architect_sale_person_id;
            }

            if ($request->user_type == 201) {
                $email = time() . '@whitelion.in';
            } else {
                $email = isset($request->user_email) ? $request->user_email:'-';
            }

            $phone_number = $request->user_phone_number;

            $alreadyEmail = User::query();
            $alreadyEmail->where('email', $request->user_email);
            $alreadyEmail->where('type', '!=', 10000);
            $alreadyEmail->where('status', '=', 1);

            if ($request->user_id != 0) {
                $alreadyEmail->where('id', '!=', $request->user_id);
            }
            $alreadyEmail = $alreadyEmail->first();

            $alreadyPhoneNumber = User::query();
            $alreadyPhoneNumber->where('phone_number', $request->user_phone_number);
            $alreadyPhoneNumber->where('type', '!=', 10000);
            $alreadyPhoneNumber->where('status', '=', 1);

            if ($request->user_id != 0) {
                $alreadyPhoneNumber->where('id', '!=', $request->user_id);
            }
            $alreadyPhoneNumber = $alreadyPhoneNumber->first();

            if ($alreadyEmail && !in_array($request->user_status, [0,5, 7, 8])) {
                $response = errorRes('Email already exists(' . $AllUserTypes[$alreadyEmail->type]['name'] . '), Try with another email');
            } elseif ($alreadyPhoneNumber && !in_array($request->user_status, [0,5, 7, 8])) {
                $response = errorRes('Phone number already exists(' . $AllUserTypes[$alreadyPhoneNumber->type]['name'] . '), Try with another phone number');
            } else {
                $user_house_no = isset($request->user_house_no) ? $request->user_house_no : '';
                $user_address_line1 = isset($request->user_address_line1) ? $request->user_address_line1 : '';
                $user_area = isset($request->user_area) ? $request->user_area : '';
                // $user_address_line2 = isset($request->user_address_line2) ? $request->user_address_line2 : '';
                $user_pincode = isset($request->user_pincode) ? $request->user_pincode : '';

                $is_residential = isset($request->architect_is_residential) ? $request->architect_is_residential : 0;
                $is_commercial_or_office_space = isset($request->architect_is_commercial_or_office_space) ? $request->architect_is_commercial_or_office_space : 0;
                $interior = isset($request->architect_interior) ? $request->architect_interior : 0;
                $exterior = isset($request->architect_exterior) ? $request->architect_exterior : 0;
                $structural_design = isset($request->architect_structural_design) ? $request->architect_structural_design : 0;
                $practicing = isset($request->architect_practicing) ? $request->architect_practicing : 0;
                $brand_using_for_switch = isset($request->architect_brand_using_for_switch) ? $request->architect_brand_using_for_switch : '';

                $whitelion_smart_switches_before = isset($request->architect_whitelion_smart_switches_before) ? $request->architect_whitelion_smart_switches_before : 0;
                $how_many_projects_used_whitelion_smart_switches = isset($request->architect_how_many_projects_used_whitelion_smart_switches) ? $request->architect_how_many_projects_used_whitelion_smart_switches : '';

                $experience_with_whitelion = isset($request->architect_experience_with_whitelion) ? $request->architect_experience_with_whitelion : 0;

                $brand_used_before_home_automation = isset($request->architect_brand_used_before_home_automation) ? $request->architect_brand_used_before_home_automation : '';

                $suggestion = isset($request->architect_suggestion) ? $request->architect_suggestion : '';

                $uploadedFile1 = '';
                $uploadedFile2 = '';
                $uploadedFile3 = '';
                $uploadedFile4 = '';


                if ($request->hasFile('architect_visiting_card')) {
                    $folderPathofFile = '/s/architect';

                    $fileObject1 = $request->file('architect_visiting_card');
                    $extension = $fileObject1->getClientOriginalExtension();

                    $fileName1 = time() . mt_rand(10000, 99999) . '.' . $extension;

                    $destinationPath = public_path($folderPathofFile);

                    $fileObject1->move($destinationPath, $fileName1);

                    if (File::exists(public_path($folderPathofFile . '/' . $fileName1))) {
                        $uploadedFile1 = $folderPathofFile . '/' . $fileName1;
                        //START UPLOAD FILE ON SPACES

                        $spaceUploadResponse = uploadFileOnSpaces(public_path($uploadedFile1), $uploadedFile1);
                        if ($spaceUploadResponse != 1) {
                            $uploadedFile1 = '';
                        } else {
                            unlink(public_path($uploadedFile1));
                        }
                        //END UPLOAD FILE ON SPACES
                    }
                }

                if ($request->hasFile('architect_aadhar_card')) {
                    $folderPathofFile = '/s/architect';
                    $fileObject2 = $request->file('architect_aadhar_card');
                    $extension = $fileObject2->getClientOriginalExtension();

                    $fileName2 = time() . mt_rand(10000, 99999) . '.' . $extension;

                    $destinationPath = public_path($folderPathofFile);

                    $fileObject2->move($destinationPath, $fileName2);

                    if (File::exists(public_path($folderPathofFile . '/' . $fileName2))) {
                        $uploadedFile2 = $folderPathofFile . '/' . $fileName2;
                        //START UPLOAD FILE ON SPACES
                        $spaceUploadResponse = uploadFileOnSpaces(public_path($uploadedFile2), $uploadedFile2);
                        if ($spaceUploadResponse != 1) {
                            $uploadedFile2 = '';
                        } else {
                            unlink(public_path($uploadedFile2));
                        }
                        //END UPLOAD FILE ON SPACES
                    }
                }

                if ($request->hasFile('architect_recording')) {
                    $folderPathofFile = '/s/architect';
                    $fileObject2 = $request->file('architect_recording');
                    $extension = $fileObject2->getClientOriginalExtension();

                    $fileName2 = time() . mt_rand(10000, 99999) . '.' . $extension;

                    $destinationPath = public_path($folderPathofFile);

                    $fileObject2->move($destinationPath, $fileName2);

                    if (File::exists(public_path($folderPathofFile . '/' . $fileName2))) {
                        $uploadedFile3 = $folderPathofFile . '/' . $fileName2;
                        //START UPLOAD FILE ON SPACES
                        $spaceUploadResponse = uploadFileOnSpaces(public_path($uploadedFile3), $uploadedFile3);
                        if ($spaceUploadResponse != 1) {
                            $uploadedFile3 = '';
                        } else {
                            unlink(public_path($uploadedFile3));
                        }
                        //END UPLOAD FILE ON SPACES
                    }
                }

                if ($request->hasFile('architect_pan_card')) {
                    $folderPathofFile = '/s/architect';
                    $fileObject2 = $request->file('architect_pan_card');
                    $extension = $fileObject2->getClientOriginalExtension();

                    $fileName2 = time() . mt_rand(10000, 99999) . '.' . $extension;

                    $destinationPath = public_path($folderPathofFile);

                    $fileObject2->move($destinationPath, $fileName2);

                    if (File::exists(public_path($folderPathofFile . '/' . $fileName2))) {
                        $uploadedFile4 = $folderPathofFile . '/' . $fileName2;
                        //START UPLOAD FILE ON SPACES
                        $spaceUploadResponse = uploadFileOnSpaces(public_path($uploadedFile4), $uploadedFile4);
                        if ($spaceUploadResponse != 1) {
                            $uploadedFile4 = '';
                        } else {
                            unlink(public_path($uploadedFile4));
                        }
                        //END UPLOAD FILE ON SPACES
                    }
                }

                $user_company_id = 1;
                $isMovedFromPrimeToNonPrime = 0;

                if ($request->user_id == 0) {
                    $User = User::where('type', 10000)
                        ->where(function ($query) use ($request) {
                            $query->where('email', $request->user_email)->orWhere('phone_number', $request->user_phone_number);
                        })
                        ->first();

                    if ($User) {
                        $User->type = $request->user_type;
                        $User->reference_type = 'architect';
                        $User->reference_id = 0;
                    } else {
                        $User = new User();
                        $User->created_by = Auth::user()->id;
                        $User->password = Hash::make('111111');
                        $User->last_active_date_time = date('Y-m-d H:i:s');
                        $User->last_login_date_time = date('Y-m-d H:i:s');
                        $User->avatar = 'default.png';
                    }
                    $Architect = new Architect();
                    $User->status = 1;
                    if ($isSalePerson != 1) {
                        if (isset($request->user_status)) {
                            $Architect->status = 6;
                        }
                    }

                    $isMovedFromPrimeToNonPrime = 1;

                    //$User->is_sent_mail = 0;
                } else {
                    $User = User::find($request->user_id);
                    $Architect = Architect::query()->where('user_id', $request->user_id)->first();
                    if ($Architect && !in_array($request->user_status, [0,5, 7, 8])) {
                        if ($uploadedFile1 == '' && $Architect->visiting_card != '') {
                            $uploadedFile1 = $Architect->visiting_card;
                        }
                        if ($uploadedFile2 == '' && $Architect->aadhar_card != '') {
                            $uploadedFile2 = $Architect->aadhar_card;
                        }
    
                        if ($uploadedFile3 == '') {
                            $uploadedFile3 = $Architect->recording;
                        }
    
                        if ($uploadedFile4 == '') {
                            $uploadedFile4 = $Architect->pan_card;
                        }

                        $log_data = [];

                        if ($User->first_name != $request->user_first_name) {
                            $new_value = $request->user_first_name;
                            $old_value = $User->first_name;
                            $change_field = "User First Name Change : " . $old_value . " To " . $new_value;

                            $log_value = [];
                            $log_value['field_name'] = "user_first_name";
                            $log_value['new_value'] = $new_value;
                            $log_value['old_value'] = $old_value;
                            $log_value['description'] =  $change_field;

                            array_push($log_data, $log_value);
                        }

                        if ($User->last_name != $request->user_last_name) {
                            $new_value = $request->user_last_name;
                            $old_value = $User->last_name;
                            $change_field = "User Last Name Change : " . $old_value . " To " . $new_value;

                            $log_value = [];
                            $log_value['field_name'] = "user_last_name";
                            $log_value['new_value'] = $new_value;
                            $log_value['old_value'] = $old_value;
                            $log_value['description'] =  $change_field;

                            array_push($log_data, $log_value);
                        }

                        if ($User->house_no != $user_house_no) {
                            $new_value = $user_house_no;
                            $old_value = $User->house_no;
                            $change_field = "User House No Change : " . $old_value . " To " . $new_value;

                            $log_value = [];
                            $log_value['field_name'] = "user_house_no";
                            $log_value['new_value'] = $new_value;
                            $log_value['old_value'] = $old_value;
                            $log_value['description'] =  $change_field;

                            array_push($log_data, $log_value);
                        }

                        if ($User->address_line1 != $user_address_line1) {
                            $new_value = $user_address_line1;
                            $old_value = $User->address_line1;
                            $change_field = "User Address Line 1 Change : " . $old_value . " To " . $new_value;

                            $log_value = [];
                            $log_value['field_name'] = "user_address_line1";
                            $log_value['new_value'] = $new_value;
                            $log_value['old_value'] = $old_value;
                            $log_value['description'] =  $change_field;

                            array_push($log_data, $log_value);
                        }

                        if ($User->area != $user_area) {
                            $new_value = $user_area;
                            $old_value = $User->area;
                            $change_field = "User Area Change : " . $old_value . " To " . $new_value;

                            $log_value = [];
                            $log_value['field_name'] = "user_area";
                            $log_value['new_value'] = $new_value;
                            $log_value['old_value'] = $old_value;
                            $log_value['description'] =  $change_field;

                            array_push($log_data, $log_value);
                        }

                        if ($User->pincode != $user_pincode) {
                            $new_value = $user_pincode;
                            $old_value = $User->pincode;
                            $change_field = "User Pincode Change : " . $old_value . " To " . $new_value;

                            $log_value = [];
                            $log_value['field_name'] = "user_pincode";
                            $log_value['new_value'] = $new_value;
                            $log_value['old_value'] = $old_value;
                            $log_value['description'] =  $change_field;

                            array_push($log_data, $log_value);
                        }

                        if ($User->city_id != $request->user_city_id) {
                            $new_value = $request->user_city_id;
                            $old_value = $User->city_id;
                            $change_field = "User City Change : " . CityList::find($old_value)['name'] . " To " . CityList::find($new_value)['name'];

                            $log_value = [];
                            $log_value['field_name'] = "user_city";
                            $log_value['new_value'] = $new_value;
                            $log_value['old_value'] = $old_value;
                            $log_value['description'] =  $change_field;

                            array_push($log_data, $log_value);
                        }

                        if ($Architect->status != $request->user_status) {
                            $new_value = $request->user_status;
                            $old_value = $Architect->status;
                            $change_field = "User Status Change : " . getArchitectsStatus()[$old_value]['name'] . " To " . getArchitectsStatus()[$new_value]['name'];

                            $log_value = [];
                            $log_value['field_name'] = "user_status";
                            $log_value['new_value'] = $new_value;
                            $log_value['old_value'] = $old_value;
                            $log_value['description'] =  $change_field;

                            array_push($log_data, $log_value);
                        }

                        if ($Architect->principal_architect_name != $principal_architect_name) {
                            $new_value = $principal_architect_name;
                            $old_value = $Architect->principal_architect_name;
                            $change_field = "User Principal Architect Name Change : " . $old_value . " To " . $new_value;

                            $log_value = [];
                            $log_value['field_name'] = "principal_architect_name";
                            $log_value['new_value'] = $new_value;
                            $log_value['old_value'] = $old_value;
                            $log_value['description'] =  $change_field;

                            array_push($log_data, $log_value);
                        }

                        if ($Architect->firm_name != $request->architect_firm_name) {
                            $new_value = $request->architect_firm_name;
                            $old_value = $Architect->firm_name;
                            $change_field = "User Firm Change : " . $old_value . " To " . $new_value;

                            $log_value = [];
                            $log_value['field_name'] = "architect_firm_name";
                            $log_value['new_value'] = $new_value;
                            $log_value['old_value'] = $old_value;
                            $log_value['description'] =  $change_field;

                            array_push($log_data, $log_value);
                        }

                        if ($Architect->sale_person_id != $sale_person_id) {
                            $new_value = $sale_person_id;
                            $old_value = $Architect->sale_person_id;

                            $old_text = User::selectRaw(DB::raw('CONCAT(first_name," ", last_name) AS text'))->where('id', $old_value)->first()['text'];
                            $new_text = User::selectRaw(DB::raw('CONCAT(first_name," ", last_name) AS text'))->where('id', $new_value)->first()['text'];
                            $change_field = "User Sale Person Change : " . $old_text . " To " . $new_text;

                            $log_value = [];
                            $log_value['field_name'] = "sale_person";
                            $log_value['new_value'] = $new_value;
                            $log_value['old_value'] = $old_value;
                            $log_value['description'] =  $change_field;

                            array_push($log_data, $log_value);
                        }

                        if (date('m-d', strtotime($Architect->anniversary_date)) != date('m-d', strtotime($request->architect_anniversary_date))) {
                            $new_value = date('m-d', strtotime($request->architect_anniversary_date));
                            $old_value = date('m-d', strtotime($Architect->anniversary_date));
                            $change_field = "User Anniversary Date Change : " . $old_value . " To " .  $new_value;

                            $log_value = [];
                            $log_value['field_name'] = "anniversary_date";
                            $log_value['new_value'] = $new_value;
                            $log_value['old_value'] = $old_value;
                            $log_value['description'] =  $change_field;

                            array_push($log_data, $log_value);
                        }

                        if (date('m-d', strtotime($Architect->birth_date)) != date('m-d', strtotime($request->architect_birth_date))) {
                            $new_value = date('m-d', strtotime($request->architect_birth_date));
                            $old_value = date('m-d', strtotime($Architect->birth_date));
                            $change_field = "User Birth Date Change : " . $old_value . " To " .  $new_value;

                            $log_value = [];
                            $log_value['field_name'] = "birth_date";
                            $log_value['new_value'] = $new_value;
                            $log_value['old_value'] = $old_value;
                            $log_value['description'] =  $change_field;

                            array_push($log_data, $log_value);
                        }

                        foreach ($log_data as $log_value) {
                            $user_log = new UserLog();
                            $user_log->user_id = Auth::user()->id;
                            $user_log->log_type = "ARCHITECT-LOG";
                            $user_log->field_name = $log_value['field_name'];
                            $user_log->old_value = $log_value['old_value'];
                            $user_log->new_value = $log_value['new_value'];
                            $user_log->reference_type = "Architect";
                            $user_log->reference_id = $request->user_id;
                            $user_log->transaction_type = "Architect Edit";
                            $user_log->description = $log_value['description'];
                            $user_log->source = "WEB";
                            $user_log->entryby = Auth::user()->id;
                            $user_log->entryip = $request->ip();
                            $user_log->save();
                        }
                    }

                    if($Architect) {
                        $Architect->status = $request->user_status;
                    }

                    if ($Architect->type != $request->user_type) {
                        $isMovedFromPrimeToNonPrime = 1;
                    }
                }
                $User->first_name = $request->user_first_name;
                $User->last_name = $request->user_last_name;
                $User->email = $email;
                $User->dialing_code = '+91';
                $User->phone_number = $request->user_phone_number;
                $User->ctc = 0;
                if ($request->user_type == 202 || $request->user_id == 0) {
                    $PrivilegeJSON = [];
                    $PrivilegeJSON['dashboard'] = 1;
                    $User->privilege = json_encode($PrivilegeJSON);
                }
                $User->house_no = $user_house_no;
                $User->address_line1 = $user_address_line1;
                $User->address_line2 = '';
                $User->area = $user_area;
                $User->pincode = $user_pincode;
                $User->country_id = 0;
                $User->state_id = 0;
                $User->city_id = $request->user_city_id;
                $User->company_id = $user_company_id;
                if($request->user_status == 5) {
                    $User->duplicate_from = isset($request->duplicate_from) ? $request->duplicate_from : 0;
                } else {
                    $User->duplicate_from = 0;
                }
                $User->type = $request->user_type;
                if ($request->user_status == 1) {
                    $User->joining_date = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s')));
                }
                $User->save();

                if ($isMovedFromPrimeToNonPrime == 1) {
                    $Architect->prime_nonprime_date = date('Y-m-d H:i:s');
                }
                $Architect->user_id = $User->id;
                $Architect->type = $request->user_type;
                $Architect->firm_name = isset($request->architect_firm_name) ? $request->architect_firm_name : "";
                if($request->user_status == 5) {
                    $Architect->sale_person_id = isset($sale_person_id) ? $sale_person_id : 0;
                } else {
                    $Architect->sale_person_id = $sale_person_id;
                }
                $Architect->is_residential = (int) $is_residential;
                $Architect->is_commercial_or_office_space = (int) $is_commercial_or_office_space;
                $Architect->interior = (int) $interior;
                $Architect->exterior = (int) $exterior;
                $Architect->structural_design = (int) $structural_design;
                $Architect->practicing = (int) $practicing;
                $Architect->brand_using_for_switch = $brand_using_for_switch;
                $Architect->visiting_card = $uploadedFile1;
                $Architect->aadhar_card = $uploadedFile2;
                $Architect->recording = $uploadedFile3;
                $Architect->pan_card = $uploadedFile4;
                // $Architect->whitelion_smart_switches_before = $whitelion_smart_switches_before;
                // $Architect->how_many_projects_used_whitelion_smart_switches = $how_many_projects_used_whitelion_smart_switches;
                // $Architect->brand_used_before_home_automation = $brand_used_before_home_automation;
                // $Architect->experience_with_whitelion = $experience_with_whitelion;
                // $Architect->suggestion = $suggestion;
                $Architect->principal_architect_name = $principal_architect_name;
                $Architect->instagram_link = $instagram_link;

                if (Auth::user()->type == 9) {
                    if (isset($request->architect_note) || $request->architect_note != '') {
                        $Architect->tele_note = $request->architect_note;

                        $UserUpdate = new UserNotes();
                        $UserUpdate->user_id = $request->user_id;
                        // .' And Status Is '. getArchitectsStatus()[$request->user_status]['header_code']
                        $UserUpdate->note = $request->architect_note;
                        $UserUpdate->note_type = "Note";
                        $UserUpdate->note_title = "Note - ".getArchitectsStatus()[$request->user_status]['header_code'];
                        $UserUpdate->entryby = Auth::user()->id;
                        $UserUpdate->entryip = $request->ip();
                        $UserUpdate->updateby = Auth::user()->id;
                        $UserUpdate->updateip = $request->ip();
                        $UserUpdate->save();
                    } else {
                        $Architect->tele_note = '';
                    }

                    if (isset($request->architect_instagram)) {
                        $Architect->instagram_link = $request->architect_instagram;
                    } else {
                        $Architect->instagram_link = '';
                    }
                }

                if (in_array(Auth::user()->type, [0, 1])) {
                    if (isset($request->architect_note) || $request->architect_note != '') {
                        $Architect->hod_note = $request->architect_note;

                        $UserUpdate = new UserNotes();
                        $UserUpdate->user_id = $request->user_id;
                        $UserUpdate->note = $request->architect_note;
                        $UserUpdate->note_type = "Note";
                        $UserUpdate->note_title = "Note - ".getArchitectsStatus()[$request->user_status]['header_code'];
                        $UserUpdate->entryby = Auth::user()->id;
                        $UserUpdate->entryip = $request->ip();
                        $UserUpdate->updateby = Auth::user()->id;
                        $UserUpdate->updateip = $request->ip();
                        $UserUpdate->save();
                    } else {
                        $Architect->hod_note = '';
                    }
                }

                if (isset($request->user_status)) {
                    if ($request->user_status == 0) {
                        $Architect->data_verified = 0;
                        $Architect->tele_verified = 0;
                        $Architect->missing_data = 0;
                    } elseif ($request->user_status == 1) {
                        $Architect->data_verified = 1;
                        $Architect->tele_verified = 1;
                        $Architect->missing_data = 0;
                    } elseif ($request->user_status == 2) {
                        $Architect->data_verified = 0;
                        $Architect->tele_verified = 0;
                        $Architect->missing_data = 0;
                    } elseif ($request->user_status == 3) {
                        $Architect->data_verified = 0;
                        $Architect->tele_verified = 0;
                        $Architect->missing_data = 1;
                    } elseif ($request->user_status == 4) {
                        $Architect->data_verified = 1;
                        $Architect->tele_verified = 1;
                        $Architect->missing_data = 0;
                        
                    } elseif ($request->user_status == 5) {
                        $Architect->data_verified = 1;
                        $Architect->tele_verified = 1;
                        $Architect->missing_data = 0;
                    }
                } else {
                    $Architect->data_verified = 0;
                    $Architect->tele_verified = 0;
                    $Architect->missing_data = 0;
                }

                $Architect->data_not_verified = 0;
                $Architect->tele_not_verified = 0;

                if ($request->architect_birth_date != '') {
                    $Architect->birth_date = date('Y-m-d H:i:s', strtotime($request->architect_birth_date . "-1980"));
                } else {
                    $Architect->birth_date = null;
                }
                if ($request->architect_anniversary_date != '') {
                    $Architect->anniversary_date = date('Y-m-d H:i:s', strtotime($request->architect_anniversary_date . "-1980"));
                } else {
                    $Architect->anniversary_date = null;
                }
                $response2 = [];
                $response2 = $request->user_id . ' - ' . $request->user_type . ' - ' . $Architect->converted_prime;
                if ($request->user_id != 0 && $request->user_type == 202 && $Architect->converted_prime == 0) {
                    $converted_prime = 0;
                } else {
                    $converted_prime = 1;
                }

                if ($request->user_type == 202) {
                    $Architect->converted_prime = 1;
                }

                if($source_type != '' && $source_type != null) {
                    $Architect->source_type = $source_type;
                }

                if($source_type_value != '' && $source_type_value != null) {
                    $Architect->source_type_value = $source_type_value;
                }
                $Architect->added_by = Auth::user()->id;

                if ($request->user_status == 1) {
                    $Architect->joining_date = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s')));
                }

                if ($request->user_id == 0) {
                    $Architect->entryby = Auth::user()->id;
                    $Architect->entryip = $request->ip();
                } else {
                    $Architect->updateby = Auth::user()->id;
                    $Architect->updateip = $request->ip();
                }

                $Architect->save();

                $User->reference_type = 'architect';
                $User->reference_id = $Architect->id;
                $User->save();

                // if($user){
                //     $timeline = array();
                //     $timeline['user_id'] = $User_status->id;
                //     $timeline['log_type'] = "user";
                //     $timeline['field_name'] = "status";
                //     $timeline['old_value'] = $oldStatus;
                //     $timeline['new_value'] = $newStatus;
                //     $timeline['reference_type'] = "user";
                //     $timeline['reference_id'] = "0";
                //     $timeline['transaction_type'] = "update";
                //     $timeline['description'] = "User status changed from  " . $userStatus[$oldStatus]['name'] . " to " . $userStatus[$newStatus]['name'] . " by " . Auth::user()->first_name . " " . Auth::user()->last_name;
                //     $timeline['source'] = $entry_source;
                //     $timeline['ip'] = $ip;
                //     saveUserLog($timeline);
                // }

                if ($isSalePerson == 0) { 
                    if (isset($request->user_status)) {
                        saveUserStatus($User->id, $request->user_status, $request->ip());
                    } else {
                        saveUserStatus($User->id, 2, $request->ip());
                    }
                }

                if ($User && $Architect) {
                    
                    if ($request->user_id == 0) {
                        $UserContact = new UserContact();

                        $UserContact->user_id = $User->id;
                        $UserContact->contact_tag_id = 0;
                        $UserContact->first_name = $request->user_first_name;
                        $UserContact->last_name = $request->user_last_name;
                        $UserContact->phone_number = $request->user_phone_number;
                        $UserContact->alernate_phone_number = 0;
                        $UserContact->email = $request->user_email;
                        $UserContact->type = $request->user_type;
                        $UserContact->type_detail = 'user-' . $request->user_type . '-' . $User->id;
                        $UserContact->save();

                        if ($UserContact) {
                            $user_update = User::find($User->id);
                            $user_update->main_contact_id = $UserContact->id;
                            $user_update->save();
                        }
                    }else{
                        $user_update = User::find($User->id);
                        if(in_array($Architect->status,[1])){
                            $user_update->status = 1;
                            $user_update->save();
                        }else if (in_array($Architect->status,[0,5])) {
                            $user_update->status = 0;
                            $user_update->save();
                        }
                    }

                    if (($request->architect_source_type != null && $request->architect_source_type == 'user-201') || $request->architect_source_type == 'user-202' || $request->architect_source_type == 'user-301' || $request->architect_source_type == 'user-302' || $request->architect_source_type == 'user-101' || $request->architect_source_type == 'user-102' || $request->architect_source_type == 'user-103' || $request->architect_source_type == 'user-104' || $request->architect_source_type == 'user-105') {
                        if ($request->user_id == 0) {
                            $Main_source = User::where('id', $Architect->source_type_value)->first();

                            $UserContact = new UserContact();

                            $UserContact->user_id = $User->id;
                            $UserContact->contact_tag_id = 0;
                            $UserContact->first_name = $Main_source->first_name;
                            $UserContact->last_name = $Main_source->last_name;
                            $UserContact->phone_number = $Main_source->phone_number;
                            $UserContact->alernate_phone_number = 0;
                            $UserContact->email = $Main_source->email;
                            $UserContact->type = $Main_source->type;
                            $UserContact->type_detail = 'user-' . $Architect->type . '-' . $Architect->source_type_value;
                            $UserContact->save();

                            if ($UserContact) {
                                $user_update = User::find($User->id);
                                $user_update->main_contact_id = $UserContact->id;
                                $user_update->save();
                            }
                        } else {

                            $Main_source = User::where('id', $Architect->source_type_value)->first();

                            $UserContact = UserContact::select('*')
                                ->where('user_id', $User->id)
                                ->where('contact_tag_id', 0)
                                ->first();

                            if ($UserContact) {
                                $UserContact->user_id = $User->id;
                                $UserContact->contact_tag_id = 0;
                                $UserContact->first_name = $Main_source->first_name;
                                $UserContact->last_name = $Main_source->last_name;
                                $UserContact->phone_number = $Main_source->phone_number;
                                $UserContact->alernate_phone_number = 0;
                                $UserContact->email = $Main_source->email;
                                $UserContact->type = $Main_source->type;
                                $UserContact->type_detail = 'user-' . $Architect->type . '-' . $Architect->source_type_value;
                                $UserContact->save();
                                if ($UserContact) {
                                    $user_update = User::find($User->id);
                                    $user_update->main_contact_id = $UserContact->id;
                                    $user_update->save();
                                }

                            } else {
                                $UserContact = new UserContact();
                                $UserContact->user_id = $User->id;
                                $UserContact->contact_tag_id = 0;
                                $UserContact->first_name = $Main_source->first_name;
                                $UserContact->last_name = $Main_source->last_name;
                                $UserContact->phone_number = $Main_source->phone_number;
                                $UserContact->alernate_phone_number = 0;
                                $UserContact->email = $Main_source->email;
                                $UserContact->type = $Main_source->type;
                                $UserContact->type_detail = 'user-' . $Architect->type . '-' . $Architect->source_type_value;
                                $UserContact->save();

                                if ($UserContact) {
                                    $user_update = User::find($User->id);
                                    $user_update->main_contact_id = $UserContact->id;
                                    $user_update->save();
                                }
                            }
                        }
                    }

                }

                $debugLog = [];
                if ($request->user_id != 0) {
                    $debugLog['name'] = 'architect-edit';
                    $debugLog['description'] = 'user #' . $User->id . '(' . $User->first_name . ' ' . $User->last_name . ') has been updated ';
                    $response = successRes('Successfully saved user');
                    $response['cov'] = $converted_prime;
                    $response['cov2'] = $response2;
                    $response['user_id'] = $User->id;
                } else {
                    $mobileNotificationTitle = 'New Architect Create';
                    $mobileNotificationMessage = 'New Architect ' . $User->first_name . ' ' . $User->last_name . ' Added By ' . Auth::user()->first_name . ' ' . Auth::user()->last_name;
                    $notificationUserids = getParentSalePersonsIds($Architect->sale_person_id);
                    $notificationUserids[] = $Architect->sale_person_id;
                    $UsersNotificationTokens = UsersNotificationTokens($notificationUserids);
                    sendNotificationTOAndroid($mobileNotificationTitle, $mobileNotificationMessage, $UsersNotificationTokens, 'Architect', $User);

                    $user_log = new UserLog();
                    $user_log->user_id = Auth::user()->id;
                    $user_log->log_type = "ARCHITECT-LOG";
                    $user_log->field_name = '';
                    $user_log->old_value = '';
                    $user_log->new_value = '';
                    $user_log->reference_type = "Architect";
                    $user_log->reference_id = $User->id;
                    $user_log->transaction_type = "Architect Create";
                    $user_log->description = 'New Architect Created';
                    $user_log->source = "WEB";
                    $user_log->entryby = Auth::user()->id;
                    $user_log->entryip = $request->ip();
                    $user_log->save();

                    $debugLog['name'] = 'architect-add';
                    $debugLog['description'] = 'user #' . $User->id . '(' . $User->first_name . ' ' . $User->last_name . ') has been added ';
                    $response = successRes('Successfully added user');
                    $response['cov'] = $converted_prime;
                    $response['cov2'] = $response2;
                    $response['user_id'] = $User->id;
                }
                saveDebugLog($debugLog);

                if (($request->user_id == 0 && $request->user_type == 202) || ($request->user_id != 0 && $request->user_type == 202 && $converted_prime == 0)) {
                    // ARCHITECT JOINING BONUS
                    $pointValue = 50;
                    $Architect->total_point = $Architect->total_point + $pointValue;
                    $Architect->total_point_current = $Architect->total_point_current + $pointValue;
                    $Architect->save();

                    $debugLog = [];
                    $debugLog['for_user_id'] = $Architect->user_id;
                    $debugLog['name'] = 'point-gain';
                    $debugLog['points'] = $pointValue;
                    $debugLog['description'] = $pointValue . ' Point gained joining bonus ';
                    $debugLog['type'] = '';
                    saveCRMUserLog($debugLog);

                    // $configrationForNotify = configrationForNotify();

                    // $params = [];
                    // $params['from_name'] = $configrationForNotify['from_name'];
                    // $params['from_email'] = $configrationForNotify['from_email'];
                    // $params['to_email'] = $User->email;
                    // $params['to_name'] = $configrationForNotify['to_name'];
                    // $params['bcc_email'] = ['sales@whitelion.in', 'sc@whitelion.in', 'poonam@whitelion.in'];
                    // $params['subject'] = 'Welcome to the Whitelion';
                    // $params['user_first_name'] = $User->first_name;
                    // $params['user_last_name'] = $User->last_name;
                    // $params['user_mobile'] = $User->phone_number;
                    // $params['credentials_email'] = $User->email;
                    // $params['credentials_password'] = '111111';
                    // $query = CRMHelpDocument::query();
                    // $query->where('status', 1);
                    // $query->where('type', 202);
                    // $query->limit(30);
                    // $query->orderBy('publish_date_time', 'desc');
                    // $helpDocuments = $query->get();
                    // $params['help_documents'] = json_decode(json_encode($helpDocuments), true);

                    // if (Config::get('app.env') == 'local') {
                    //     // SEND MAIL
                    //     $params['to_email'] = $configrationForNotify['test_email'];
                    //     $params['bcc_email'] = $configrationForNotify['test_email_bcc'];
                    // }

                    // //TEMPLATE 6
                    // Mail::send('emails.signup_architect', ['params' => $params], function ($m) use ($params) {
                    //     $m->from($params['from_email'], $params['from_name']);
                    //     $m->bcc($params['bcc_email']);
                    //     $m->to($params['to_email'], $params['to_name'])->subject($params['subject']);

                    //     foreach ($params['help_documents'] as $new_helpDocument) {
                    //         $fileName = preg_replace('![^a-z0-9]+!i', '-', $new_helpDocument['file_name']);
                    //         $fileExtension = explode('.', $new_helpDocument['file_name']);
                    //         $fileExtension = end($fileExtension);
                    //         $fileName = $fileName . '.' . $fileExtension;

                    //         // if (is_file($new_helpDocument['title'])) {
                    //         $m->attach(getSpaceFilePath($new_helpDocument['file_name']), [
                    //             'as' => $fileName,
                    //         ]);
                    //         // }
                    //     }
                    // });

                    $query = CRMHelpDocument::query();
                    $query->where('status', 1);
                    $query->where('type', 202);
                    $query->limit(30);
                    $query->orderBy('publish_date_time', 'desc');
                    $helpDocuments = $query->get();
                    $file = "";
                    foreach ($helpDocuments as $new_helpDocument) {
                        $file .= $new_helpDocument['file_name'].', ';
                    }

                    $configrationForNotify = configrationForNotify();

                    $Mailarr = [];
                    $Mailarr['from_name'] = $configrationForNotify['from_name'];
                    $Mailarr['from_email'] = $configrationForNotify['from_email'];
                    $Mailarr['to_email'] = $User->email;
                    $Mailarr['to_name'] = $configrationForNotify['to_name'];
                    $Mailarr['bcc_email'] = "sales@whitelion.in, sc@whitelion.in, poonam@whitelion.in";
                    $Mailarr['cc_email'] = "";
                    $Mailarr['subject'] = 'Welcome to the Whitelion';
                    $Mailarr['transaction_id'] = $User->id;
                    $Mailarr['transaction_name'] = "Architect";
                    $Mailarr['transaction_type'] = "Email";
                    $Mailarr['transaction_detail'] = "emails.signup_architect";
                    $Mailarr['attachment'] = rtrim($file, ", ");
                    $Mailarr['remark'] = "Architect Create";
                    $Mailarr['source'] = "Web";
                    $Mailarr['entryip'] = $request->ip();
                    
                    if (Config::get('app.env') == 'local') {
                        $Mailarr['to_email'] = $configrationForNotify['test_email'];
                        $Mailarr['bcc_email'] = implode(', ', $configrationForNotify['test_email_bcc']);
                    }
                    saveNotificationScheduler($Mailarr);


                    $helpDocument = CRMHelpDocument::query();
					$helpDocument->where('status', 1);
					$helpDocument->where('type', 202);
					$helpDocument->orderBy('publish_date_time', "desc");
					$helpDocument = $helpDocument->first();
					//TEMPLATE 7
					$whatsapp_controller = new WhatsappApiContoller;
					$perameater_request = new Request();
					$perameater_request['q_whatsapp_massage_mobileno'] = $User->phone_number;
					$perameater_request['q_whatsapp_massage_template'] = 'for_architect_download_mobileapp';
					$perameater_request['q_whatsapp_massage_attechment'] = $helpDocument ? getSpaceFilePath($helpDocument->file_name) : '';
					$perameater_request['q_broadcast_name'] = $User->first_name . ' ' . $User->last_name;
					$perameater_request['q_whatsapp_massage_parameters'] = array(
						'data[0]' => $User->email,
						'data[1]' => '111111'
					);
					$wp_response = $whatsapp_controller->sendTemplateMessage($perameater_request);
					$response["whatsapp"] = $wp_response;
                }
            }
        }

        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }
    public function saveCategory(Request $request)
    {
        $rules = [];

        $rules['architect_id'] = 'required';
        $rules['category_id'] = 'required';

        $customMessage = [];
        $customMessage['architect_id.required'] = 'Architect Id Required';
        $customMessage['category_id.required'] = 'Invalid category';

        $validator = Validator::make($request->all(), $rules, $customMessage);

        if ($validator->fails()) {
            $response = [];
            $response['status'] = 0;
            $response['msg'] = 'The request could not be understood by the server due to malformed syntax';
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors();
        } else {
            $Architect = Architect::where('user_id', $request->architect_id)->first();
            if ($Architect) {
                $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
                $isSalePerson = isSalePerson();
                $isChannelPartner = isChannelPartner(Auth::user()->type);
                $isMarketingDispatcherUser = isMarketingDispatcherUser();
                $isTaleSalesUser = isTaleSalesUser();

                if ($isAdminOrCompanyAdmin == 1 || $isMarketingDispatcherUser == 1 || $isTaleSalesUser == 1 || ($isSalePerson == 1 && $Architect->sale_person_id == Auth::user()->id) || ($isChannelPartner != 0 && $Architect->added_by == Auth::user()->id)) {
                    $ArchitectCategory = ArchitectCategory::find($request->category_id);
                    if ($ArchitectCategory || $request->category_id == 0) {
                        
                        $log_data = [];
                        
                        if ($Architect->category_id != $request->category_id) {
                            $new_value = $request->category_id;
                            $old_value = $Architect->category_id;
                            $old_cat_name = '';
                            $oldArcCategory = ArchitectCategory::find($old_value);
                            if($oldArcCategory){
                                $old_cat_name = $oldArcCategory->name;
                            }
                            $change_field = "User Category Change : " . $old_cat_name . " To " . $ArchitectCategory->name;

                            $log_value = [];
                            $log_value['field_name'] = "user_category";
                            $log_value['new_value'] = $new_value;
                            $log_value['old_value'] = $old_value;
                            $log_value['description'] =  $change_field;

                            array_push($log_data, $log_value);
                        }

                        $Architect->category_id = $request->category_id;
                        $Architect->save();
                        $response = successRes('Successfully updated architect');

                        foreach ($log_data as $log_value) {
                            $user_log = new UserLog();
                            $user_log->user_id = Auth::user()->id;
                            $user_log->log_type = "ARCHITECT-LOG";
                            $user_log->field_name = $log_value['field_name'];
                            $user_log->old_value = $log_value['old_value'];
                            $user_log->new_value = $log_value['new_value'];
                            $user_log->reference_type = "Architect";
                            $user_log->reference_id = $request->architect_id;
                            $user_log->transaction_type = "Architect Edit";
                            $user_log->description = $log_value['description'];
                            $user_log->source = "WEB";
                            $user_log->entryby = Auth::user()->id;
                            $user_log->entryip = $request->ip();
                            $user_log->save();
                        }
    
                    } else {
                        $response = errorRes('Invalid access');
                    }
                } else {
                    $response = errorRes('Invalid access');
                }
            } else {
                $response = errorRes('Invalid architect');
            }
        }

        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }
    public function detail(Request $request)
    {
        $User = User::with([
            'country' => function ($query) {
                $query->select('id', 'name');
            },
            'state' => function ($query) {
                $query->select('id', 'name');
            },
            'city' => function ($query) {
                $query->select('id', 'name');
            },
            'company' => function ($query) {
                $query->select('id', 'name');
            },
        ])
            ->where('id', $request->id)
            ->whereIn('type', [201, 202])
            ->first();
        if ($User) {
            $CityList = CityList::select('city_list.id', 'city_list.name as city_list_name', 'state_list.name as state_list_name');
            $CityList->leftJoin('state_list', 'state_list.id', '=', 'city_list.state_id');
            $CityList->where('city_list.id', $User->city_id);
            $CityList = $CityList->first();
            if ($CityList) {
                $CityList = json_encode($CityList);
                $CityList = json_decode($CityList, true);

                $CityList['text'] = $CityList['city_list_name'] . ', ' . $CityList['state_list_name'] . ', India';

                $User['city']['name'] = $CityList['text'];
            }

            $Architect = Architect::where('user_id', $request->id)->first();


            if ($Architect) {
                $Architect = json_decode(json_encode($Architect), true);

                $source_type['id'] = 0;
                $source_type['text'] = '';
                $Architect['source'] = [];
                $Architect['source']['id'] = 0;
                $Architect['source']['text'] = '';
                if ($Architect['source_type'] != '' || $Architect['source_type'] != null) {

                    $source_type_pieces = explode('-', $Architect['source_type']);

                    $source_type = [];
                    $source_type['id'] = $source_type_pieces[1];
                    $source_type['text'] = $source_type_pieces[1];
                    if ($source_type_pieces[0] == 'user') {
                        if (isChannelPartner($source_type_pieces[1]) != 0) {
                            $User1 = User::select('users.id', DB::raw('channel_partner.firm_name'));
                            $User1->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
                            $User1->where('users.id', $Architect['source_type_value']);
                            $User1->limit(1);
                            $User1 = $User1->first();
                            if ($User1) {
                                $Architect['source'] = [];
                                $Architect['source']['id'] = $User1->id;
                                $Architect['source']['text'] = $User1->firm_name;
                            }
                        } else {
                            $User1 = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
                            $User1->where('id', $Architect['source_type_value']);
                            $User1->limit(1);
                            $User1 = $User1->first();
                            if ($User1) {
                                $Architect['source'] = [];
                                $Architect['source']['id'] = $User1->id;
                                $Architect['source']['text'] = $User1->full_name;
                            }
                        }
                    } else if($source_type_pieces[0] == 'exhibition') {
                        $Exhibition = Exhibition::find($Architect['source_type_value']);
                        if($Exhibition) {
                            $Architect['source'] = [];
                            $Architect['source']['id'] = $Exhibition->id;
                            $Architect['source']['text'] = $Exhibition->name;
                        }
                    }
                }

                $isSalePerson = Auth::user()->type == 2 ? 1 : 0;

                $SalePersonsIds = getChildSalePersonsIds(Auth::user()->id);

                // if ($isSalePerson == 0 || ($isSalePerson == 1 && in_array($Architect['sale_person_id'], $SalePersonsIds))) {
                if ($Architect['visiting_card'] != '') {
                    $Architect['visiting_card'] = '(<a target="_blank" href="' . getSpaceFilePath($Architect['visiting_card']) . '" title="File">Download</i></a>)';
                }

                if ($Architect['aadhar_card'] != '') {
                    $Architect['aadhar_card'] = '(<a target="_blank" href="' . getSpaceFilePath($Architect['aadhar_card']) . '" title="File">Download</i></a>)';
                }

                if ($Architect['instagram_link'] != '') {
                    $Architect['instagram_link'] = $Architect['instagram_link'];
                }

                if ($Architect['pan_card'] != '') {
                    $Architect['pan_card'] = '(<a target="_blank" href="' . getSpaceFilePath($Architect['pan_card']) . '" title="File">Download</i></a>)';
                }

                $salePerson = User::query();
                $salePerson->select('users.id as id', DB::raw('CONCAT(first_name," ", last_name) AS text'));
                $salePerson = $salePerson->find($Architect['sale_person_id']);

                $dupArchitect  = User::find($User->duplicate_from);
                if ($dupArchitect) {
                    $dupArchitect['text'] = $dupArchitect['id'] . '-' . $dupArchitect['first_name'] . ' ' . $dupArchitect['last_name'] . '-' . $dupArchitect['phone_number'];
                } else {
                    $dupArchitect['text'] = "";
                }

                if ($Architect['source_type']) {
                    $source_type_explode = explode('-', $Architect['source_type']);
                    foreach (getLeadSourceTypes() as $key => $value) {
                        if ($value['type'] == $source_type_explode[0] && $value['id'] == $source_type_explode[1]) {
                            $Architect['source_type_object']['id'] = $value['type'] . '-' . $value['id'];
                            $Architect['source_type_object']['text'] = $value['lable'];
                        }
                    }
                } else {
                    $Architect['source_type_object']['id'] = '-';
                    $Architect['source_type_object']['text'] = '-';
                }

                $Architect['anniversary_date'] = date('d-m', strtotime($Architect['anniversary_date']));
                $Architect['birth_date'] = date('d-m', strtotime($Architect['birth_date']));

                $data = json_decode(json_encode($User), true);
                $data['status_id'] = $Architect['status'];
                $data['status_text'] = getArchitectsStatus()[$Architect['status']]['name'];

                $response = successRes('Successfully get user');
                $response['data'] = $data;
                $response['data']['duplicate_from'] = $dupArchitect;
                $response['data']['architect'] = $Architect;
                if($salePerson != null) {
                    $response['data']['architect']['sale_person'] = $salePerson;
                } else {
                    $response['data']['architect']['sale_person'] = array();
                    $response['data']['architect']['sale_person']['text'] = "";
                }
                // } else {
                // $response = errorRes('Invalid id');
                // }
            } else {
                $response = errorRes('Invalid Architect');
            }
        } else {
            $response = errorRes('Invalid id');
        }
        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }
    public function searchUser(Request $request)
    {
        $isArchitect = isArchitect();
        $isSalePerson = isSalePerson();
        $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
        $isMarketingDispatcherUser = isMarketingDispatcherUser();

        if (isset($request->user_id) && $request->user_id != '') {
            if ($isSalePerson == 1) {
                $salePerson = SalePerson::select('cities')
                    ->where('user_id', Auth::user()->id)
                    ->first();
                $cities = [];
                if ($salePerson) {
                    $cities = explode(',', $salePerson->cities);
                } else {
                    $cities = [0];
                }
            }

            $User = User::query();
            $User->select('users.id', 'users.first_name', 'users.last_name', 'users.phone_number');
            $User->where('users.status', 1);
            $User->whereIn('users.type', [301, 302]);
            if ($isSalePerson == 1) {
                $User->leftJoin('electrician', 'electrician.user_id', '=', 'users.id');

                $User->where(function ($query) use ($cities) {
                    $query->whereIn('users.city_id', $cities);

                    // $query->orWhere('electrician.sale_person_id', Auth::user()->id);
                });
            }

            $User->where('users.id', $request->user_id);
            $User->limit(1);
            $UserResponse = $User->get();
        } else {
            if ($isSalePerson == 1 && ($request->source_type == 301 || $request->source_type == 302)) {
                $salePerson = SalePerson::select('cities')
                    ->where('user_id', Auth::user()->id)
                    ->first();
                $cities = [];
                if ($salePerson) {
                    $cities = explode(',', $salePerson->cities);
                } else {
                    $cities = [0];
                }

                //$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);

                $UserResponse = [];
                $q = $request->q;
                $User = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
                $User->where('users.type', $request->source_type);
                $User->leftJoin('electrician', 'electrician.user_id', '=', 'users.id');
                $User->where('users.status', 1);
                //$User->whereIn('electrician.sale_person_id', $childSalePersonsIds);
                $User->whereIn('users.city_id', $cities);
                //$User->where('users.city_id', Auth::user()->city_id);
                $User->where(function ($query) use ($q) {
                    $query->where('users.first_name', 'like', '%' . $q . '%');
                    $query->orWhere('users.last_name', 'like', '%' . $q . '%');
                });
                $User->limit(5);
                $User = $User->get();

                if (count($User) > 0) {
                    foreach ($User as $User_key => $User_value) {
                        $UserResponse[$User_key]['id'] = $User_value['id'];
                        $UserResponse[$User_key]['text'] = $User_value['full_name'];
                    }
                }
            } elseif (isChannelPartner($request->source_type) != 0) {
                if ($isSalePerson == 1) {
                    $childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);

                    $salePerson = SalePerson::select('cities')
                        ->where('user_id', Auth::user()->id)
                        ->first();
                    $cities = [];
                    if ($salePerson) {
                        $cities = explode(',', $salePerson->cities);
                    } else {
                        $cities = [0];
                    }
                }

                $UserResponse = [];
                $q = $request->q;
                $User = User::select('users.id', DB::raw('channel_partner.firm_name'));
                $User->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
                $User->where('users.status', 1);
                $User->where('users.type', $request->source_type);
                if ($isSalePerson == 1) {
                    $User->where(function ($query) use ($cities, $childSalePersonsIds) {
                        $query->whereIn('users.city_id', $cities);

                        $query->orWhere(function ($query2) use ($childSalePersonsIds) {
                            foreach ($childSalePersonsIds as $key => $value) {
                                if ($key == 0) {
                                    $query2->whereRaw('FIND_IN_SET("' . $value . '", channel_partner.sale_persons)>0');
                                } else {
                                    $query2->orWhereRaw('FIND_IN_SET("' . $value . '",channel_partner.sale_persons)>0');
                                }
                            }
                        });
                    });
                }

                $User->where(function ($query) use ($q) {
                    $query->where('channel_partner.firm_name', 'like', '%' . $q . '%');
                });
                $User->limit(5);
                $User = $User->get();

                if (count($User) > 0) {
                    foreach ($User as $User_key => $User_value) {
                        $UserResponse[$User_key]['id'] = $User_value['id'];
                        $UserResponse[$User_key]['text'] = $User_value['firm_name'];
                    }
                }
            } else {
                $UserResponse = [];
                $q = $request->q;
                $User = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
                $User->where('users.status', 1);
                $User->where('users.type', $request->source_type);
                $User->where(function ($query) use ($q) {
                    $query->where('users.first_name', 'like', '%' . $q . '%');
                    $query->orWhere('users.last_name', 'like', '%' . $q . '%');
                });
                $User->limit(5);
                $User = $User->get();

                if (count($User) > 0) {
                    foreach ($User as $User_key => $User_value) {
                        $UserResponse[$User_key]['id'] = $User_value['id'];
                        $UserResponse[$User_key]['text'] = $User_value['full_name'];
                    }
                }
            }
        }

        $response = [];
        $response['results'] = $UserResponse;
        $response['pagination']['more'] = false;
        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }
    function export(Request $request)
    {
        $isSalePerson = isSalePerson();
        $isChannelPartner = isChannelPartner(Auth::user()->type);
        $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
        $isMarketingDispatcherUser = isMarketingDispatcherUser();
        if ($isSalePerson == 1) {
            $SalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
        }

        $columns = ['users.id', 'users.first_name', 'users.last_name', 'users.email', 'users.dialing_code', 'users.phone_number', 'users.status', 'users.created_at', 'architect.firm_name', 'sale_person.first_name as sale_person_first_name', 'sale_person.last_name  as sale_person_last_name', 'users.type', 'city_list.name as city_name'];

        $query = Architect::query();
        $query->select($columns);
        $query->leftJoin('users', 'users.id', '=', 'architect.user_id');
        $query->leftJoin('users as sale_person', 'architect.sale_person_id', '=', 'sale_person.id');
        $query->leftJoin('city_list', 'city_list.id', '=', 'users.city_id');
        //$query->where('architect.type', $request->type);
        $query->whereIn('architect.type', [201, 202]);
        if ($isAdminOrCompanyAdmin == 1 || $isMarketingDispatcherUser == 1) {
        } elseif ($isSalePerson == 1) {
            $query->whereIn('architect.sale_person_id', $SalePersonsIds);
        } elseif ($isChannelPartner != 0) {
            $query->where('architect.added_by', Auth::user()->id);
        }

        $query->orderBy('architect.id', 'desc');
        $data = $query->get();

        // if ($request->type == 201) {
        // 	$headers = array("#ID", "Firstname", "Lastname", "Phone", "Status", "Created", "Firmname", "SalePerson");
        // } else {
        $headers = ['#ID', 'TYPE', 'Firstname', 'Lastname', 'Email', 'Phone', 'Status', 'Created', 'Firmname', 'SalePerson', 'City'];
        // }

        header('Content-Type: text/csv');
        // if ($request->type == 201) {
        // 	header('Content-Disposition: attachment; filename="architects-non-prime.csv"');
        // } else {
        // 	header('Content-Disposition: attachment; filename="architects-prime.csv"');
        // }
        header('Content-Disposition: attachment; filename="architects.csv"');
        $fp = fopen('php://output', 'wb');

        fputcsv($fp, $headers);

        foreach ($data as $key => $value) {
            $createdAt = convertDateTime($value->created_at);
            $status = $value->status;
            if ($status == 0) {
                $status = 'Inactive';
            } elseif ($status == 1) {
                $status = 'Active';
            } elseif ($status == 2) {
                $status = 'Blocked';
            }

            if ($value->type == 201) {
                $lineVal = [$value->id, 'NON-PRIME', $value->first_name, $value->last_name, '-', $value->dialing_code . ' ' . $value->phone_number, $status, $createdAt, $value->firm_name, $value->sale_person_first_name . ' ' . $value->sale_person_last_name, $value->city_name];
            } elseif ($value->type == 202) {
                $lineVal = [$value->id, 'PRIME', $value->first_name, $value->last_name, $value->email, $value->dialing_code . ' ' . $value->phone_number, $status, $createdAt, $value->firm_name, $value->sale_person_first_name . ' ' . $value->sale_person_last_name, $value->city_name];
            }

            fputcsv($fp, $lineVal, ',');
        }

        fclose($fp);
    }
    function pointLog(Request $request)
    {
        $searchColumns = [
            0 => 'crm_log.description',
        ];

        $sortingColumns = [
            0 => 'crm_log.id',
        ];

        $selectColumns = ['crm_log.description'];

        $query = CRMLog::query();
        $query->where('for_user_id', $request->user_id);
        $query->whereIn('name', ['point-gain', 'point-redeem']);
        $recordsTotal = $query->count();

        $recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.
        $query = CRMLog::query();
        $query->where('for_user_id', $request->user_id);
        $query->whereIn('name', ['point-gain', 'point-redeem']);
        $query->select($selectColumns);
        $query->limit($request->length);
        $query->offset($request->start);
        $query->orderBy($sortingColumns[$request['order'][0]['column']], $request['order'][0]['dir']);

        $isFilterApply = 0;

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

        $data = $query->get();
        $data = json_decode(json_encode($data), true);
        if ($isFilterApply == 1) {
            $recordsFiltered = count($data);
        }

        $viewData = [];

        foreach ($data as $key => $value) {
            $viewData[$key] = [];
            $viewData[$key]['log'] = $value['description'];
        }

        $jsonData = [
            'draw' => intval($request['draw']),
            // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            'recordsTotal' => intval($recordsTotal),
            // total number of records
            'recordsFiltered' => intval($recordsFiltered),
            // total number of records after searching, if there is no searching then totalFiltered = totalData
            'data' => $viewData, // total data array
        ];
        return $jsonData;
    }
    public function inquiryLog(Request $request)
    {
        $inquiryStatus = getInquiryStatus();

        $searchColumns = ['inquiry.id', 'CONCAT(inquiry.first_name," ",inquiry.last_name)', 'inquiry.status', 'inquiry.quotation_amount'];

        $sortingColumns = [
            0 => 'inquiry.id',
            1 => 'inquiry.first_name',
            2 => 'inquiry.status',
            3 => 'inquiry.quotation_amount',
        ];

        $selectColumns = ['inquiry.id', 'inquiry.first_name', 'inquiry.last_name', 'inquiry.status', 'inquiry.quotation_amount', 'inquiry.answer_date_time', 'inquiry.electrician', 'inquiry.source_type', 'inquiry.source_type_value'];

        $userId = $request->user_id;
        $title = '';
        $User = User::find($userId);
        if ($User) {
            $title = $User->first_name . ' ' . $User->last_name;
            $UserArchitect = Architect::where('user_id', $User->id)->first();
            if ($UserArchitect) {
                $title = $title . ' | Lifetime Point : ' . $UserArchitect->total_point . ' | Available Point : ' . $UserArchitect->total_point_current;
            }
        }

        $query = Inquiry::query();
        $query->where(function ($query2) use ($userId) {
            $query2->where(function ($query3) use ($userId) {
                $query3->whereIn('inquiry.source_type', ['user-201', 'user-202']);
                $query3->where('inquiry.source_type_value', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {
                $query3->whereIn('inquiry.source_type_1', ['user-201', 'user-202']);
                $query3->where('inquiry.source_type_value_1', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {
                $query3->whereIn('inquiry.source_type_2', ['user-201', 'user-202']);
                $query3->where('inquiry.source_type_value_2', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {
                $query3->whereIn('inquiry.source_type_3', ['user-201', 'user-202']);
                $query3->where('inquiry.source_type_value_3', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {
                $query3->whereIn('inquiry.source_type_4', ['user-201', 'user-202']);
                $query3->where('inquiry.source_type_value_4', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {
                $query3->where('inquiry.architect', $userId);
            });
        });

        if ($request->type != 0) {
            if ($request->type == 1) {
                $statusArray = isset($inquiryStatus[201]['for_user_ids']) ? $inquiryStatus[201]['for_user_ids'] : [0];
                $query->whereIn('inquiry.status', $statusArray);
            } elseif ($request->type == 2) {
                $statusArray = [9, 11, 10, 12, 14];
                $query->whereIn('inquiry.status', $statusArray);
            } elseif ($request->type == 3) {
                $statusArray = [101, 102];
                $query->whereIn('inquiry.status', $statusArray);
            }
        }

        $recordsTotal = $query->count();

        $query = Inquiry::query();
        $query->where(function ($query2) use ($userId) {
            $query2->where(function ($query3) use ($userId) {
                $query3->whereIn('inquiry.source_type', ['user-201', 'user-202']);
                $query3->where('inquiry.source_type_value', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {
                $query3->whereIn('inquiry.source_type_1', ['user-201', 'user-202']);
                $query3->where('inquiry.source_type_value_1', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {
                $query3->whereIn('inquiry.source_type_2', ['user-201', 'user-202']);
                $query3->where('inquiry.source_type_value_2', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {
                $query3->whereIn('inquiry.source_type_3', ['user-201', 'user-202']);
                $query3->where('inquiry.source_type_value_3', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {
                $query3->whereIn('inquiry.source_type_4', ['user-201', 'user-202']);
                $query3->where('inquiry.source_type_value_4', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {
                $query3->where('inquiry.architect', $userId);
            });
        });

        if ($request->type != 0) {
            if ($request->type == 1) {
                $statusArray = isset($inquiryStatus[201]['for_user_ids']) ? $inquiryStatus[201]['for_user_ids'] : [0];
                $query->whereIn('inquiry.status', $statusArray);
            } elseif ($request->type == 2) {
                $statusArray = [9, 11, 10, 12, 14];
                $query->whereIn('inquiry.status', $statusArray);
            } elseif ($request->type == 3) {
                $statusArray = [101, 102];
                $query->whereIn('inquiry.status', $statusArray);
            }
        }

        $quotationTotal = $query->sum('quotation_amount');

        $recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.
        $query = Inquiry::query();

        $query->where(function ($query2) use ($userId) {
            $query2->where(function ($query3) use ($userId) {
                $query3->whereIn('inquiry.source_type', ['user-201', 'user-202']);
                $query3->where('inquiry.source_type_value', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {
                $query3->whereIn('inquiry.source_type_1', ['user-201', 'user-202']);
                $query3->where('inquiry.source_type_value_1', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {
                $query3->whereIn('inquiry.source_type_2', ['user-201', 'user-202']);
                $query3->where('inquiry.source_type_value_2', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {
                $query3->whereIn('inquiry.source_type_3', ['user-201', 'user-202']);
                $query3->where('inquiry.source_type_value_3', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {
                $query3->whereIn('inquiry.source_type_4', ['user-201', 'user-202']);
                $query3->where('inquiry.source_type_value_4', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {
                $query3->where('inquiry.architect', $userId);
            });
        });

        $query->select($selectColumns);
        $query->limit($request->length);
        $query->offset($request->start);
        $query->orderBy($sortingColumns[$request['order'][0]['column']], $request['order'][0]['dir']);
        $isFilterApply = 0;

        if (isset($request['search']['value'])) {
            $isFilterApply = 1;
            $search_value = $request['search']['value'];
            $query->where(function ($query) use ($search_value, $searchColumns) {
                for ($i = 0; $i < count($searchColumns); $i++) {
                    if ($i == 0) {
                        $query->whereRaw($searchColumns[$i] . ' like ?', [$search_value]);
                    } else {
                        $query->orWhereRaw($searchColumns[$i] . ' like ?', ['%' . $search_value . '%']);
                    }
                }
            });
        }

        if ($request->type != 0) {
            if ($request->type == 1) {
                $statusArray = isset($inquiryStatus[201]['for_user_ids']) ? $inquiryStatus[201]['for_user_ids'] : [0];
                $query->whereIn('inquiry.status', $statusArray);
            } elseif ($request->type == 2) {
                $statusArray = [9, 11, 10, 12, 14];
                $query->whereIn('inquiry.status', $statusArray);
            } elseif ($request->type == 3) {
                $statusArray = [101, 102];
                $query->whereIn('inquiry.status', $statusArray);
            }
        }

        $data = $query->get();
        $data = json_decode(json_encode($data), true);
        if ($isFilterApply == 1) {
            $recordsFiltered = count($data);
        }

        $viewData = [];

        foreach ($data as $key => $value) {
            $viewData[$key] = [];
            $viewData[$key]['id'] = $value['id'];
            $viewData[$key]['name'] = '<p class="text-muted mb-0" data-bs-toggle="tooltip"  data-bs-original-title="' . $value['first_name'] . ' ' . $value['last_name'] . '"><a target="_blank" href="' . route('inquiry') . '?status=0&inquiry_id=' . $value['id'] . '" >' . displayStringLenth($value['first_name'] . ' ' . $value['last_name'], 20) . '</a></p>';

            $viewData[$key]['status'] = $inquiryStatus[$value['status']]['name'] . ' (' . convertDateTime($value['answer_date_time']) . ')';
            $viewData[$key]['quotation_amount'] = $value['quotation_amount'];

            $column4Val = '';
            $column5Val = '';

            if ($value['electrician'] != 0) {
                $User4 = User::find($value['electrician']);
                if ($User4) {
                    $column4Val = $User4->first_name . ' ' . $User4->last_name;
                }
            }

            if (in_array($value['source_type'], ['user-101', 'user-102', 'user-103', 'user-104', 'user-105']) && $value['source_type_value'] != 0) {
                $User5 = ChannelPartner::where('user_id', $value['source_type_value'])->first();
                if ($User5) {
                    $column5Val = $User5->firm_name;
                }
            }

            $viewData[$key]['column4'] = $column4Val;
            $viewData[$key]['column5'] = $column5Val;
        }

        $overview = [];

        $query = Inquiry::query();
        $query->where(function ($query2) use ($userId) {
            $query2->where(function ($query3) use ($userId) {
                $query3->whereIn('inquiry.source_type', ['user-201', 'user-202']);
                $query3->where('inquiry.source_type_value', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {
                $query3->whereIn('inquiry.source_type_1', ['user-201', 'user-202']);
                $query3->where('inquiry.source_type_value_1', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {
                $query3->whereIn('inquiry.source_type_2', ['user-201', 'user-202']);
                $query3->where('inquiry.source_type_value_2', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {
                $query3->whereIn('inquiry.source_type_3', ['user-201', 'user-202']);
                $query3->where('inquiry.source_type_value_3', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {
                $query3->whereIn('inquiry.source_type_4', ['user-201', 'user-202']);
                $query3->where('inquiry.source_type_value_4', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {
                $query3->where('inquiry.architect', $userId);
            });
        });

        $recordsTotal = $query->count();

        $overview['total_inquiry'] = $recordsTotal;

        $query = Inquiry::query();
        $query->where(function ($query2) use ($userId) {
            $query2->where(function ($query3) use ($userId) {
                $query3->whereIn('inquiry.source_type', ['user-201', 'user-202']);
                $query3->where('inquiry.source_type_value', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {
                $query3->whereIn('inquiry.source_type_1', ['user-201', 'user-202']);
                $query3->where('inquiry.source_type_value_1', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {
                $query3->whereIn('inquiry.source_type_2', ['user-201', 'user-202']);
                $query3->where('inquiry.source_type_value_2', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {
                $query3->whereIn('inquiry.source_type_3', ['user-201', 'user-202']);
                $query3->where('inquiry.source_type_value_3', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {
                $query3->whereIn('inquiry.source_type_4', ['user-201', 'user-202']);
                $query3->where('inquiry.source_type_value_4', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {
                $query3->where('inquiry.architect', $userId);
            });
        });
        $statusArray = isset($inquiryStatus[201]['for_user_ids']) ? $inquiryStatus[201]['for_user_ids'] : [0];
        $query->whereIn('inquiry.status', $statusArray);
        $recordsTotal = $query->count();
        $overview['total_running'] = $recordsTotal;

        $query = Inquiry::query();
        $query->where(function ($query2) use ($userId) {
            $query2->where(function ($query3) use ($userId) {
                $query3->whereIn('inquiry.source_type', ['user-201', 'user-202']);
                $query3->where('inquiry.source_type_value', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {
                $query3->whereIn('inquiry.source_type_1', ['user-201', 'user-202']);
                $query3->where('inquiry.source_type_value_1', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {
                $query3->whereIn('inquiry.source_type_2', ['user-201', 'user-202']);
                $query3->where('inquiry.source_type_value_2', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {
                $query3->whereIn('inquiry.source_type_3', ['user-201', 'user-202']);
                $query3->where('inquiry.source_type_value_3', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {
                $query3->whereIn('inquiry.source_type_4', ['user-201', 'user-202']);
                $query3->where('inquiry.source_type_value_4', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {
                $query3->where('inquiry.architect', $userId);
            });
        });
        $statusArray = isset($inquiryStatus[9]['for_user_ids']) ? $inquiryStatus[9]['for_user_ids'] : [0];
        $query->whereIn('inquiry.status', $statusArray);
        $recordsTotal = $query->count();
        $overview['total_won'] = $recordsTotal;

        $query = Inquiry::query();
        $query->where(function ($query2) use ($userId) {
            $query2->where(function ($query3) use ($userId) {
                $query3->whereIn('inquiry.source_type', ['user-201', 'user-202']);
                $query3->where('inquiry.source_type_value', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {
                $query3->whereIn('inquiry.source_type_1', ['user-201', 'user-202']);
                $query3->where('inquiry.source_type_value_1', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {
                $query3->whereIn('inquiry.source_type_2', ['user-201', 'user-202']);
                $query3->where('inquiry.source_type_value_2', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {
                $query3->whereIn('inquiry.source_type_3', ['user-201', 'user-202']);
                $query3->where('inquiry.source_type_value_3', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {
                $query3->whereIn('inquiry.source_type_4', ['user-201', 'user-202']);
                $query3->where('inquiry.source_type_value_4', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {
                $query3->where('inquiry.architect', $userId);
            });
        });
        // $statusArray = isset($inquiryStatus[102]['for_user_ids']) ? $inquiryStatus[102]['for_user_ids'] : array(0);
        $statusArray = [101, 102];
        $query->whereIn('inquiry.status', $statusArray);
        $recordsTotal = $query->count();
        $overview['total_rejected'] = $recordsTotal;

        $jsonData = [
            'draw' => intval($request['draw']),
            // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            'recordsTotal' => intval($recordsTotal),
            // total number of records
            'recordsFiltered' => intval($recordsFiltered),
            // total number of records after searching, if there is no searching then totalFiltered = totalData
            'data' => $viewData,
            // total data array
            'overview' => $overview,
            // total data array
            'type' => $request->type,
            'quotationAmount' => priceLable($quotationTotal),
            'title' => $title,
        ];
        return $jsonData;
    }
    public function getListAjax(Request $request)
    {
        $isSalePerson = isSalePerson();
        $isChannelPartner = isChannelPartner(Auth::user()->type);
        $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
        $isCreUser = isCreUser();
        $isMarketingDispatcherUser = isMarketingDispatcherUser();
        $isTaleSalesUser = isTaleSalesUser();
        if ($isTaleSalesUser == 1) {
            $TeleSalesCity = TeleSalesCity(Auth::user()->id);
        }

        if ($isSalePerson == 1) {
            $SalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
        }

        $searchColumns = [
            'users.id',
            "users.first_name",
            "users.last_name",
            "sale_person.first_name",
            "sale_person.last_name",
            "CONCAT(users.first_name,' ',users.last_name)",
            "CONCAT(sale_person.first_name,' ',sale_person.last_name)",
            'users.phone_number',
        ];

        $sortingColumns = [
            0 => 'users.id',
            1 => 'users.first_name',
            2 => 'users.email',
            3 => 'architect.sale_person_id',
            4 => 'architect.total_point_current',
            5 => 'architect.total_point',
            6 => 'users.status',
            7 => 'architect.category_id',
            8 => 'architect.principal_architect_name',
            9 => 'users.created_by',
        ];

        $selectColumns = ['users.id', 'users.type', 'users.first_name', 'users.last_name', 'users.email', 'users.phone_number', 'architect.sale_person_id', 'architect.status', 'architect.total_point_current', 'architect.category_id', 'architect.principal_architect_name', 'architect.tele_verified', 'architect.tele_not_verified', 'architect.instagram_link', 'architect.data_verified', 'architect.data_not_verified', 'architect.missing_data', 'architect.source_type_value', 'architect.source_type', 'architect.total_point', 'architect.joining_date', 'architect.firm_name'];

        $query = Architect::query();
        // $query->select($selectColumns);
        $query->leftJoin('users', 'users.id', '=', 'architect.user_id');
        $query->leftJoin('users as sale_person', 'architect.sale_person_id', '=', 'sale_person.id');
        $query->leftJoin('users as created_by_user', 'users.created_by', '=', 'created_by_user.id');
        $query->whereIn('architect.type', [201, 202]);

        if (isset($request->status)) {
            if ($request->status == 100) {
                $query->whereIn('architect.status', [0, 1, 2, 3, 4, 5]);
            } else {
                $query->where('architect.status', $request->status);
            }
        }

        
        // elseif ($isTaleSalesUser == 1) {
        //     $query->whereIn('users.city_id', $TeleSalesCity);
        // }
        
        $arr_where_clause = [];
        $arr_or_clause = [];
        $sortingColumns = array();
        $newdatares = [];
        $date_condtion = '';
        $isorderby = 0;

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
                    $column = getArchitectFilterColumn()[$filt_value['column']];
                    $condtion = getUserFilterCondtion()[$filt_value['condtion']];
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
                        if ($column['code'] == "user_source" && ($filt_value['value_source_type'] == null || $filt_value['value_source_type'] == '')) {
                            $response = errorRes("Please select value");
                            return response()->json($response)->header('Content-Type', 'application/json');
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
                        if ($column['code'] == "user_source" && ($filt_value['value_source_type'] == null || $filt_value['value_source_type'] == '')) {
                            $response = errorRes("Please select value");
                            return response()->json($response)->header('Content-Type', 'application/json');
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
                    } elseif ($column['value_type'] == 'select_order_by' && $condtion['value_type'] == 'single_select') {
                        $isorderby = 1;
                        if ($filt_value['value_select'] == null || $filt_value['value_select'] == '') {
                            $response = errorRes('Please enter from to date');
                            return response()
                                ->json($response)
                                ->header('Content-Type', 'application/json');
                        } else {
                            $filter_value = $filt_value['value_select'];
                        }
                    }

                    if ($filt_value['clause'] != 0) {
                        $clause = getUserFilterClause()[$filt_value['clause']];
                    }

                    if ($filt_value['clause'] == 0) {
                        $newdata['clause'] = 0;
                        $newdata['column'] = $column['id'];
                        $newdata['condtion'] = $condtion['id'];
                        $newdata['value'] = $filter_value;
                        $newdata['source_type'] = $source_type;

                        $newdatares = $newdata;

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

            foreach ($arr_where_clause as $wherekey => $objwhere) {
                $Column = getArchitectFilterColumn()[$objwhere['column']];
                $Condtion = getUserFilterCondtion()[$objwhere['condtion']];
                $lstDateFilter = getUserDateFilterValue();
                $Filter_Value = $objwhere['value'];
                $source_type = $objwhere['source_type'];

                if ($Condtion['code'] == 'is') {
                    if ($Column['value_type'] == 'date') {
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
                        } else {
                            $date_filter_value = explode(',', $objDateFilter['value']);
                            $from_date_filter = $date_filter_value[0];
                            $from_date_filter_value = date('Y-m-d', strtotime($from_date_filter));

                            $to_date_filter = $date_filter_value[1];
                            $to_date_filter_value = date('Y-m-d', strtotime($to_date_filter));

                            $query->whereBetween(DB::raw('DATE(' . $Column['column_name'] . ')'), [$from_date_filter_value, $to_date_filter_value]);
                        }
                    } elseif ($Column['value_type'] == 'select') {
                        if ($Column['code'] == 'user_source') {
                            $query->where($Column['column_name'], $source_type);
                        } else {
                            $query->where($Column['column_name'], $Filter_Value);
                        }
                    } elseif ($Column['value_type'] == 'select_order_by') {
                        $newSortingColumns['column'] = $Column['column_name'];
                        
                        if ($Filter_Value == 1) {
                            $newSortingColumns['sort'] = 'DESC';
                            // $query->orderBy($Column['column_name'], 'DESC');
                        } else {
                            $newSortingColumns['sort'] = 'ASC';
                            // $query->orderBy($Column['column_name'], 'ASC');
                        }
                        array_push($sortingColumns,$newSortingColumns);
                    } else {
                        $query->where($Column['column_name'], 'like', '%' . $Filter_Value . '%');
                    }
                } elseif ($Condtion['code'] == 'is_not') {
                    if ($Column['value_type'] == 'date') {
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
                        } else {
                            $date_filter_value = explode(',', $objDateFilter['value']);
                            $from_date_filter = $date_filter_value[0];
                            $from_date_filter_value = date('Y-m-d', strtotime($from_date_filter));

                            $to_date_filter = $date_filter_value[1];
                            $to_date_filter_value = date('Y-m-d', strtotime($to_date_filter));

                            $query->whereNotBetween(DB::raw('DATE(' . $Column['column_name'] . ')'), [$from_date_filter_value, $to_date_filter_value]);
                        }
                    } elseif ($Column['value_type'] == 'select') {
                        if ($Column['code'] == 'user_source') {
                            $query->where($Column['column_name'], '!=', $source_type);
                        } else {
                            $query->where($Column['column_name'], '!=', $Filter_Value);
                        }
                    } else {
                        $query->where($Column['column_name'], 'not like', '%' . $Filter_Value . '%');
                    }
                } elseif ($Condtion['code'] == 'contains') {
                    if ($Column['value_type'] == 'select') {
                        if ($Column['code'] == 'user_source') {
                            $query->whereIn($Column['column_name'], $source_type);
                        } else {
                            $query->whereIn($Column['column_name'], $Filter_Value);
                        }
                    } else {
                        $Filter_Value = explode(',', $Filter_Value);
                        $query->whereIn($Column['column_name'], $Filter_Value);
                    }
                } elseif ($Condtion['code'] == 'not_contains') {
                    if ($Column['value_type'] == 'select') {
                        if ($Column['code'] == 'user_source') {
                            $query->whereNotIn($Column['column_name'], $source_type);
                        } else {
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
                        $Column = getArchitectFilterColumn()[$objor['column']];
                        $Condtion = getUserFilterCondtion()[$objor['condtion']];
                        $lstDateFilter = getUserDateFilterValue();
                        $Filter_Value = $objor['value'];
                        $source_type = $objor['source_type'];
                        
                        if ($Condtion['code'] == 'is') {
                            if ($Column['value_type'] == 'date') {
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
                                if ($Column['code'] == 'user_source') {
                                    $query->orWhere($Column['column_name'], $source_type);
                                } else {
                                    $query->orWhere($Column['column_name'], $Filter_Value);
                                }
                            } elseif ($Column['value_type'] == 'select_order_by') {
                                $newSortingColumns['column'] = $Column['column_name'];
                                if ($Filter_Value == 1) {
                                    $newSortingColumns['sort'] = 'DESC';
                                } else {
                                    $newSortingColumns['sort'] = 'ASC';
                                }
                                array_push($sortingColumns,$newSortingColumns);
                            } else {
                                $query->orWhere($Column['column_name'], 'like', '%' . $Filter_Value . '%');
                            }
                        } elseif ($Condtion['code'] == 'is_not') {
                            if ($Column['value_type'] == 'date') {
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
                                if ($Column['code'] == 'user_source') {
                                    $query->orWhere($Column['column_name'], '!=', $source_type);
                                } else {
                                    $query->orWhere($Column['column_name'], '!=', $Filter_Value);
                                }
                            } else {
                                $query->orWhere($Column['column_name'], 'not like', '%' . $Filter_Value . '%');
                            }
                        } elseif ($Condtion['code'] == 'contains') {
                            if ($Column['value_type'] == 'select') {
                                if ($Column['code'] == 'user_source') {
                                    $query->orWhereIn($Column['column_name'], $Filter_Value);
                                } else {
                                    $query->orWhereIn($Column['column_name'], $Filter_Value);
                                }
                            } else {
                                $Filter_Value = explode(',', $Filter_Value);
                                $query->orWhereIn($Column['column_name'], $Filter_Value);
                            }
                        } elseif ($Condtion['code'] == 'not_contains') {
                            if ($Column['code'] == 'user_source') {
                                $query->orWhereNotIn($Column['column_name'], $Filter_Value);
                            }

                            if ($Column['value_type'] == 'select') {
                                $query->orWhereNotIn($Column['column_name'], $Filter_Value);
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


        if ($isAdminOrCompanyAdmin == 1 || $isMarketingDispatcherUser) {
        } elseif ($isSalePerson == 1) {
            $query->whereIn('architect.sale_person_id', $SalePersonsIds);
        } elseif ($isChannelPartner != 0) {
            
            // $query->where('architect.added_by', Auth::user()->id);
            $ObjChannelPartner = ChannelPartner::select('sale_persons')->where('user_id', Auth::user()->id)->first();
            if($ObjChannelPartner){
                $query->where('architect.sale_person_id', $ObjChannelPartner->sale_persons);
            }
        } 
        $Filter_lead_ids = $query->distinct()->pluck('users.id');

        $recordsTotal = $Filter_lead_ids->count();
        $recordsFiltered = $recordsTotal;


        // RECORDSFILTERED START
        $query = Architect::query();
        $query->select($selectColumns);
        $query->leftJoin('users', 'users.id', '=', 'architect.user_id');
        $query->leftJoin('users as created_by_user', 'users.created_by', '=', 'created_by_user.id');
        $query->leftJoin('users as sale_person', 'architect.sale_person_id', '=', 'sale_person.id');
        $query->whereIn('users.id', $Filter_lead_ids);
        $search_value = '';
        if (isset($request['search']['value'])) {
            $isFilterApply = 1;
            $search_value = $request['search']['value'];
            $query->where(function ($query) use ($search_value, $searchColumns) {
                for ($i = 0; $i < count($searchColumns); $i++) {
                    if ($i == 0) {
                        $query->whereRaw($searchColumns[$i] . ' like ? ', [$search_value]);
                        $query->orWhereRaw($searchColumns[$i] . ' like ? ', ['%' . $search_value . '%']);
                    } else {
                        $query->orWhereRaw($searchColumns[$i] . ' like ? ', [$search_value]);
                        $query->orWhereRaw($searchColumns[$i] . ' like ? ', ['%' . $search_value . '%']);
                    }
                }
            });
        }
        $recordsFiltered = $query->count();
        // RECORDSFILTERED START

        $query = Architect::query();
        $query->select($selectColumns);
        $query->leftJoin('users', 'users.id', '=', 'architect.user_id');
        $query->leftJoin('users as created_by_user', 'users.created_by', '=', 'created_by_user.id');
        $query->leftJoin('users as sale_person', 'architect.sale_person_id', '=', 'sale_person.id');
        $query->whereIn('users.id', $Filter_lead_ids);
        $query->limit($request->length);
        $query->offset($request->start);
        if ($isorderby == 0) {
            $query->orderBy('users.id', 'DESC');
        }else {
            foreach ($sortingColumns as $key => $value) {
                $query->orderBy($value['column'], $value['sort']);
            }
        }
        $isFilterApply = 0;

        if (isset($request['search']['value'])) {
            $isFilterApply = 1;
            $search_value = $request['search']['value'];
            $query->where(function ($query) use ($search_value, $searchColumns) {
                for ($i = 0; $i < count($searchColumns); $i++) {
                    if ($i == 0) {
                        $query->whereRaw($searchColumns[$i] . ' like ? ', [$search_value]);
                        $query->orWhereRaw($searchColumns[$i] . ' like ? ', ['%' . $search_value . '%']);
                    } else {
                        $query->orWhereRaw($searchColumns[$i] . ' like ? ', [$search_value]);
                        $query->orWhereRaw($searchColumns[$i] . ' like ? ', ['%' . $search_value . '%']);
                    }
                }
            });
        }

        
        $data = $query->get();

        if ($data->count() >= 1) {
            $FirstPageArchitectId = $data[0]['id'];
        } else {
            $FirstPageArchitectId = 0;
        }

        $data = json_decode(json_encode($data), true);

        $viewData = [];
        $ArchitectStatus = getArchitectsStatus();
        foreach ($data as $key => $value) {
            $view = '';
            $view .= '<li class="lead_li" id="lead_' . $value['id'] . '" onclick="getDataDetail(' . $value['id'] . ')" style="list-style:none">';
            $view .= '<a href="javascript: void(0);">';
            $view .= '<div class="d-flex">';
            $view .= '<div class="flex-grow-1 overflow-hidden">';
            if ($value['type'] == 201) {
                $view .= '<h5 class="text-truncate font-size-14 mb-1">#' . highlightString($value['id'],$search_value) . ' -  Non Prime</h5>';
            } elseif ($value['type'] == 202) {
                $view .= '<h5 class="text-truncate font-size-14 mb-1">#' . highlightString($value['id'],$search_value) . ' -  Prime</h5>';
            }
            $view .= '<p class="text-truncate mb-0">' . highlightString(ucwords(strtolower($value['first_name'])) . ' ' . ucwords(strtolower($value['last_name'])),$search_value) . '</p>';
            $view .= '</div>';
            $view .= '<div class="d-flex justify-content-end font-size-16">';
            $view .= '<span class="badge badge-pill badge badge-soft-info font-size-11" style="height: fit-content;" id="5471_lead_list_status">' . $ArchitectStatus[$value['status']]['header_code'] . '</span>';
            $view .= '</div>';
            $view .= '</div>';
            $view .= '</a>';
            $view .= '</li>';

            $viewData[$key] = [];
            $viewData[$key]['view'] = $view;
        }

        $jsonData = [
            'draw' => intval($request['draw']),
            // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            'recordsTotal' => intval($recordsTotal),
            // total number of records
            'recordsFiltered' => intval($recordsFiltered),
            // total number of records after searching, if there is no searching then totalFiltered = totalData
            'data' => $viewData,
            // total data array
            'dataed' => $data,
            // total data array
            'FirstPageLeadId' => $FirstPageArchitectId,
        ];
        return $jsonData;
    }
    function getList(Request $request)
    {
        $clause_detail = getUserFilterClause();
        $column_detail = getArchitectFilterColumn();
        $condtion_detail = getUserFilterCondtion();
        $isMarketingDispatcherUser = isMarketingDispatcherUser();
        $isChannelPartner = isChannelPartner(Auth::user()->type);

        $isSalePerson = isSalePerson();
        $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
        if ($isSalePerson == 1) {
            $childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
        }

        $selectColumns = ['users.id', 'users.first_name', 'users.last_name', 'users.email', 'users.phone_number', 'architect.status', 'architect.type'];

        $Architect = Architect::query();
        $Architect->leftJoin('users', 'users.id', '=', 'architect.user_id');
        $Architect->whereIn('architect.type', [201, 202]);
        $Architect->select($selectColumns);
        if (isset($request->search)) {
            if ($request->search != '') {
                $search = $request->search;
                $Architect->where(function ($query) use ($search) {
                    $query->where('users.id', 'like', '%' . $search . '%');
                    $query->orWhere('users.first_name', 'like', '%' . $search . '%');
                    $query->orWhere('users.last_name', 'like', '%' . $search . '%');
                });
            }
        }

        if (isset($request->status)) {
            $Architect->where('architect.status', $request->status);
        }

        $arr_where_clause = [];
        $arr_or_clause = [];
        $newdatares = [];
        $date_condtion = '';
        $isorderby = 0;

        if ($request->isAdvanceFilter == 1) {
            foreach ($request->AdvanceData as $key => $filt_value) {
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
                    $column = getArchitectFilterColumn()[$filt_value['column']];
                    $condtion = getUserFilterCondtion()[$filt_value['condtion']];
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
                        if ($column['code'] == "user_source" && ($filt_value['value_source_type'] == null || $filt_value['value_source_type'] == '')) {
                            $response = errorRes("Please select value");
                            return response()->json($response)->header('Content-Type', 'application/json');
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
                        if ($column['code'] == "user_source" && ($filt_value['value_source_type'] == null || $filt_value['value_source_type'] == '')) {
                            $response = errorRes("Please select value");
                            return response()->json($response)->header('Content-Type', 'application/json');
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
                    } elseif ($column['value_type'] == 'select_order_by' && $condtion['value_type'] == 'single_select') {
                        $isorderby = 1;
                        if ($filt_value['value_select'] == null || $filt_value['value_select'] == '') {
                            $response = errorRes('Please enter from to date');
                            return response()
                                ->json($response)
                                ->header('Content-Type', 'application/json');
                        } else {
                            $filter_value = $filt_value['value_select'];
                        }
                    }

                    if ($filt_value['clause'] != 0) {
                        $clause = getUserFilterClause()[$filt_value['clause']];
                    }

                    if ($filt_value['clause'] == 0) {
                        $newdata['clause'] = 0;
                        $newdata['column'] = $column['id'];
                        $newdata['condtion'] = $condtion['id'];
                        $newdata['value'] = $filter_value;
                        $newdata['source_type'] = $source_type;

                        $newdatares = $newdata;

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

            foreach ($arr_where_clause as $wherekey => $objwhere) {
                $Column = getArchitectFilterColumn()[$objwhere['column']];
                $Condtion = getUserFilterCondtion()[$objwhere['condtion']];
                $lstDateFilter = getUserDateFilterValue();
                $Filter_Value = $objwhere['value'];
                $source_type = $objwhere['source_type'];

                if ($Condtion['code'] == 'is') {
                    if ($Column['value_type'] == 'date') {
                        $objDateFilter = $lstDateFilter[$Filter_Value];

                        $currentStartDate = date('Y-m-d', strtotime(date('Y-m-d')));
                        $currentEndDate = date('Y-m-d', strtotime(date('Y-m-d')));

                        if ($objDateFilter['code'] == 'all_closing') {
                            $Architect->where($Column['column_name'], '!=', null);
                        } elseif ($objDateFilter['code'] == 'in_this_week') {
                            $currentWeekDay = date('w', strtotime($currentStartDate));
                            $weekStartDate = date('Y-m-d', strtotime($currentStartDate . ' -' . ($currentWeekDay - 1) . ' days'));
                            $weekEndDate = date('Y-m-d', strtotime($currentEndDate . ' +' . (7 - $currentWeekDay) . ' days'));
                            $Architect->whereDate($Column['column_name'], '<=', $weekEndDate);
                        } elseif ($objDateFilter['code'] == 'in_this_month') {
                            $monthStartDay = date('Y-m-01', strtotime($currentStartDate));
                            $monthEndDay = date('Y-m-t', strtotime($currentEndDate));
                            $Architect->whereDate($Column['column_name'], '<=', $monthEndDay);
                        } elseif ($objDateFilter['code'] == 'in_next_month') {
                            $currentStartDate = date('Y-m-d', strtotime($currentStartDate . ' +5 hours'));
                            $currentStartDate = date('Y-m-d', strtotime($currentStartDate . ' +30 minutes'));
                            $monthStartDay = date('Y-m-01', strtotime($currentStartDate . ' + 1 month'));
                            $monthEndDay = date('Y-m-t', strtotime($monthStartDay));
                            $Architect->whereDate($Column['column_name'], '<=', $monthEndDay);
                        } elseif ($objDateFilter['code'] == 'in_next_two_month') {
                            $monthStartDay = date('Y-m-01', strtotime($currentStartDate . ' + 1 month'));
                            $monthEndDay = date('Y-m-t', strtotime($currentEndDate . ' + 2 month'));
                            $Architect->whereDate($Column['column_name'], '<=', $monthEndDay);
                        } elseif ($objDateFilter['code'] == 'in_next_three_month') {
                            $monthStartDay = date('Y-m-01', strtotime($currentStartDate . ' + 1 month'));
                            $monthEndDay = date('Y-m-t', strtotime($currentEndDate . ' + 3 month'));
                            $Architect->whereDate($Column['column_name'], '>=', date('Y-m-d'));
                            $Architect->whereDate($Column['column_name'], '<=', $monthEndDay);
                        } else {
                            $date_filter_value = explode(',', $objDateFilter['value']);
                            $from_date_filter = $date_filter_value[0];
                            $from_date_filter_value = date('Y-m-d', strtotime($from_date_filter));

                            $to_date_filter = $date_filter_value[1];
                            $to_date_filter_value = date('Y-m-d', strtotime($to_date_filter));

                            $Architect->whereBetween(DB::raw('DATE(' . $Column['column_name'] . ')'), [$from_date_filter_value, $to_date_filter_value]);
                        }
                    } elseif ($Column['value_type'] == 'select') {
                        if ($Column['code'] == 'user_source') {
                            $Architect->where($Column['column_name'], $source_type);
                        } else {
                            $Architect->where($Column['column_name'], $Filter_Value);
                        }
                    } elseif ($Column['value_type'] == 'select_order_by') {
                        if ($Filter_Value == 1) {
                            $Architect->orderBy($Column['column_name'], 'DESC');
                        } else {
                            $Architect->orderBy($Column['column_name'], 'ASC');
                        }
                    } else {
                        $Architect->where($Column['column_name'], 'like', '%' . $Filter_Value . '%');
                    }
                } elseif ($Condtion['code'] == 'is_not') {
                    if ($Column['value_type'] == 'date') {
                        $objDateFilter = $lstDateFilter[$Filter_Value];

                        $currentStartDate = date('Y-m-d', strtotime(date('Y-m-d')));
                        $currentEndDate = date('Y-m-d', strtotime(date('Y-m-d')));

                        if ($objDateFilter['code'] == 'all_closing') {
                            $Architect->where($Column['column_name'], '!=', null);
                        } elseif ($objDateFilter['code'] == 'in_this_week') {
                            $currentWeekDay = date('w', strtotime($currentStartDate));
                            $weekStartDate = date('Y-m-d', strtotime($currentStartDate . ' -' . ($currentWeekDay - 1) . ' days'));
                            $weekEndDate = date('Y-m-d', strtotime($currentEndDate . ' +' . (7 - $currentWeekDay) . ' days'));
                            $Architect->whereDate($Column['column_name'], '<=', $weekEndDate);
                        } elseif ($objDateFilter['code'] == 'in_this_month') {
                            $monthStartDay = date('Y-m-01', strtotime($currentStartDate));
                            $monthEndDay = date('Y-m-t', strtotime($currentEndDate));
                            $Architect->whereDate($Column['column_name'], '<=', $monthEndDay);
                        } elseif ($objDateFilter['code'] == 'in_next_month') {
                            $currentStartDate = date('Y-m-d', strtotime($currentStartDate . ' +5 hours'));
                            $currentStartDate = date('Y-m-d', strtotime($currentStartDate . ' +30 minutes'));
                            $monthStartDay = date('Y-m-01', strtotime($currentStartDate . ' + 1 month'));
                            $monthEndDay = date('Y-m-t', strtotime($monthStartDay));
                            $Architect->whereDate($Column['column_name'], '<=', $monthEndDay);
                        } elseif ($objDateFilter['code'] == 'in_next_two_month') {
                            $monthStartDay = date('Y-m-01', strtotime($currentStartDate . ' + 1 month'));
                            $monthEndDay = date('Y-m-t', strtotime($currentEndDate . ' + 2 month'));
                            $Architect->whereDate($Column['column_name'], '<=', $monthEndDay);
                        } elseif ($objDateFilter['code'] == 'in_next_three_month') {
                            $monthStartDay = date('Y-m-01', strtotime($currentStartDate . ' + 1 month'));
                            $monthEndDay = date('Y-m-t', strtotime($currentEndDate . ' + 3 month'));
                            $Architect->whereDate($Column['column_name'], '>=', date('Y-m-d'));
                            $Architect->whereDate($Column['column_name'], '<=', $monthEndDay);
                        } else {
                            $date_filter_value = explode(',', $objDateFilter['value']);
                            $from_date_filter = $date_filter_value[0];
                            $from_date_filter_value = date('Y-m-d', strtotime($from_date_filter));

                            $to_date_filter = $date_filter_value[1];
                            $to_date_filter_value = date('Y-m-d', strtotime($to_date_filter));

                            $Architect->whereNotBetween(DB::raw('DATE(' . $Column['column_name'] . ')'), [$from_date_filter_value, $to_date_filter_value]);
                        }
                    } elseif ($Column['value_type'] == 'select') {
                        if ($Column['code'] == 'user_source') {
                            $Architect->where($Column['column_name'], '!=', $source_type);
                        } else {
                            $Architect->where($Column['column_name'], '!=', $Filter_Value);
                        }
                    } else {
                        $Architect->where($Column['column_name'], 'not like', '%' . $Filter_Value . '%');
                    }
                } elseif ($Condtion['code'] == 'contains') {
                    if ($Column['value_type'] == 'select') {
                        if ($Column['code'] == 'user_source') {
                            $Architect->whereIn($Column['column_name'], $source_type);
                        } else {
                            $Architect->whereIn($Column['column_name'], $Filter_Value);
                        }
                    } else {
                        $Filter_Value = explode(',', $Filter_Value);
                        $Architect->whereIn($Column['column_name'], $Filter_Value);
                    }
                } elseif ($Condtion['code'] == 'not_contains') {
                    if ($Column['value_type'] == 'select') {
                        if ($Column['code'] == 'user_source') {
                            $Architect->whereNotIn($Column['column_name'], $source_type);
                        } else {
                            $Architect->whereNotIn($Column['column_name'], $Filter_Value);
                        }
                    } else {
                        $Filter_Value = explode(',', $Filter_Value);
                        $Architect->whereNotIn($Column['column_name'], $Filter_Value);
                    }
                } elseif ($Condtion['code'] == 'between') {
                    if ($Column['value_type'] == 'date') {
                        $date_filter_value = explode(',', $Filter_Value);
                        $from_date_filter = $date_filter_value[0];
                        $from_date_filter_value = date('Y-m-d', strtotime($from_date_filter));

                        $to_date_filter = $date_filter_value[1];
                        $to_date_filter_value = date('Y-m-d', strtotime($to_date_filter));

                        $Architect->whereBetween(DB::raw('DATE(' . $Column['column_name'] . ')'), [$from_date_filter_value, $to_date_filter_value]);
                    }
                }
            }

            if (count($arr_or_clause) > 0) {
                $Architect->orWhere(function ($Architect) use ($arr_or_clause) {
                    foreach ($arr_or_clause as $orkey => $objor) {
                        $Column = getArchitectFilterColumn()[$objor['column']];
                        $Condtion = getUserFilterCondtion()[$objor['condtion']];
                        $lstDateFilter = getUserDateFilterValue();
                        $Filter_Value = $objor['value'];
                        $source_type = $objor['source_type'];
                        
                        if ($Condtion['code'] == 'is') {
                            if ($Column['value_type'] == 'date') {
                                $objDateFilter = $lstDateFilter[$Filter_Value];

                                $currentStartDate = date('Y-m-d', strtotime(date('Y-m-d')));
                                $currentEndDate = date('Y-m-d', strtotime(date('Y-m-d')));

                                if ($objDateFilter['code'] == 'all_closing') {
                                    $Architect->orwhere($Column['column_name'], '!=', null);
                                } elseif ($objDateFilter['code'] == 'in_this_week') {
                                    $currentWeekDay = date('w', strtotime($currentStartDate));
                                    $weekStartDate = date('Y-m-d', strtotime($currentStartDate . ' -' . ($currentWeekDay - 1) . ' days'));
                                    $weekEndDate = date('Y-m-d', strtotime($currentEndDate . ' +' . (7 - $currentWeekDay) . ' days'));
                                    $Architect->orWhereDate($Column['column_name'], '<=', $weekEndDate);
                                } elseif ($objDateFilter['code'] == 'in_this_month') {
                                    $monthStartDay = date('Y-m-01', strtotime($currentStartDate));
                                    $monthEndDay = date('Y-m-t', strtotime($currentEndDate));
                                    $Architect->orWhereDate($Column['column_name'], '<=', $monthEndDay);
                                } elseif ($objDateFilter['code'] == 'in_next_month') {
                                    $currentStartDate = date('Y-m-d', strtotime($currentStartDate . ' +5 hours'));
                                    $currentStartDate = date('Y-m-d', strtotime($currentStartDate . ' +30 minutes'));
                                    $monthStartDay = date('Y-m-01', strtotime($currentStartDate . ' + 1 month'));
                                    $monthEndDay = date('Y-m-t', strtotime($monthStartDay));
                                    $Architect->orWhereDate($Column['column_name'], '<=', $monthEndDay);
                                } elseif ($objDateFilter['code'] == 'in_next_two_month') {
                                    $monthStartDay = date('Y-m-01', strtotime($currentStartDate . ' + 1 month'));
                                    $monthEndDay = date('Y-m-t', strtotime($currentEndDate . ' + 2 month'));
                                    $Architect->orWhereDate($Column['column_name'], '<=', $monthEndDay);
                                } elseif ($objDateFilter['code'] == 'in_next_three_month') {
                                    $monthStartDay = date('Y-m-01', strtotime($currentStartDate . ' + 1 month'));
                                    $monthEndDay = date('Y-m-t', strtotime($currentEndDate . ' + 3 month'));
                                    $Architect->orWhereDate($Column['column_name'], '>=', date('Y-m-d'));
                                    $Architect->orWhereDate($Column['column_name'], '<=', $monthEndDay);
                                } else {
                                    $date_filter_value = explode(',', $objDateFilter['value']);
                                    $from_date_filter = $date_filter_value[0];
                                    $from_date_filter_value = date('Y-m-d', strtotime($from_date_filter));

                                    $to_date_filter = $date_filter_value[1];
                                    $to_date_filter_value = date('Y-m-d', strtotime($to_date_filter));

                                    $Architect->whereBetween(DB::raw('DATE(' . $Column['column_name'] . ')'), [$from_date_filter_value, $to_date_filter_value]);
                                }
                            } elseif ($Column['value_type'] == 'select') {
                                if ($Column['code'] == 'user_source') {
                                    $Architect->orWhere($Column['column_name'], $source_type);
                                } else {
                                    $Architect->orWhere($Column['column_name'], $Filter_Value);
                                }
                            } elseif ($Column['value_type'] == 'select_order_by') {
                                if ($Filter_Value == 1) {
                                    $Architect->orderBy($Column['column_name'], 'DESC');
                                } else {
                                    $Architect->orderBy($Column['column_name'], 'ASC');
                                }
                            } else {
                                $Architect->orWhere($Column['column_name'], 'like', '%' . $Filter_Value . '%');
                            }
                        } elseif ($Condtion['code'] == 'is_not') {
                            if ($Column['value_type'] == 'date') {
                                $objDateFilter = $lstDateFilter[$Filter_Value];

                                $currentStartDate = date('Y-m-d', strtotime(date('Y-m-d')));
                                $currentEndDate = date('Y-m-d', strtotime(date('Y-m-d')));

                                if ($objDateFilter['code'] == 'all_closing') {
                                    $Architect->orwhere($Column['column_name'], '!=', null);
                                } elseif ($objDateFilter['code'] == 'in_this_week') {
                                    $currentWeekDay = date('w', strtotime($currentStartDate));
                                    $weekStartDate = date('Y-m-d', strtotime($currentStartDate . ' -' . ($currentWeekDay - 1) . ' days'));
                                    $weekEndDate = date('Y-m-d', strtotime($currentEndDate . ' +' . (7 - $currentWeekDay) . ' days'));
                                    $Architect->orWhereDate($Column['column_name'], '<=', $weekEndDate);
                                } elseif ($objDateFilter['code'] == 'in_this_month') {
                                    $monthStartDay = date('Y-m-01', strtotime($currentStartDate));
                                    $monthEndDay = date('Y-m-t', strtotime($currentEndDate));
                                    $Architect->orWhereDate($Column['column_name'], '<=', $monthEndDay);
                                } elseif ($objDateFilter['code'] == 'in_next_month') {
                                    $currentStartDate = date('Y-m-d', strtotime($currentStartDate . ' +5 hours'));
                                    $currentStartDate = date('Y-m-d', strtotime($currentStartDate . ' +30 minutes'));
                                    $monthStartDay = date('Y-m-01', strtotime($currentStartDate . ' + 1 month'));
                                    $monthEndDay = date('Y-m-t', strtotime($monthStartDay));
                                    $Architect->orWhereDate($Column['column_name'], '<=', $monthEndDay);
                                } elseif ($objDateFilter['code'] == 'in_next_two_month') {
                                    $monthStartDay = date('Y-m-01', strtotime($currentStartDate . ' + 1 month'));
                                    $monthEndDay = date('Y-m-t', strtotime($currentEndDate . ' + 2 month'));
                                    $Architect->orWhereDate($Column['column_name'], '<=', $monthEndDay);
                                } elseif ($objDateFilter['code'] == 'in_next_three_month') {
                                    $monthStartDay = date('Y-m-01', strtotime($currentStartDate . ' + 1 month'));
                                    $monthEndDay = date('Y-m-t', strtotime($currentEndDate . ' + 3 month'));
                                    $Architect->orWhereDate($Column['column_name'], '>=', date('Y-m-d'));
                                    $Architect->orWhereDate($Column['column_name'], '<=', $monthEndDay);
                                } else {
                                    $date_filter_value = explode(',', $objDateFilter['value']);
                                    $from_date_filter = $date_filter_value[0];
                                    $from_date_filter_value = date('Y-m-d', strtotime($from_date_filter));

                                    $to_date_filter = $date_filter_value[1];
                                    $to_date_filter_value = date('Y-m-d', strtotime($to_date_filter));

                                    $Architect->whereNotBetween(DB::raw('DATE(' . $Column['column_name'] . ')'), [$from_date_filter_value, $to_date_filter_value]);
                                }
                            } elseif ($Column['value_type'] == 'select') {
                                if ($Column['code'] == 'user_source') {
                                    $Architect->orWhere($Column['column_name'], '!=', $source_type);
                                } else {
                                    $Architect->orWhere($Column['column_name'], '!=', $Filter_Value);
                                }
                            } else {
                                $Architect->orWhere($Column['column_name'], 'not like', '%' . $Filter_Value . '%');
                            }
                        } elseif ($Condtion['code'] == 'contains') {
                            if ($Column['value_type'] == 'select') {
                                if ($Column['code'] == 'user_source') {
                                    $Architect->orWhereIn($Column['column_name'], $Filter_Value);
                                } else {
                                    $Architect->orWhereIn($Column['column_name'], $Filter_Value);
                                }
                            } else {
                                $Filter_Value = explode(',', $Filter_Value);
                                $Architect->orWhereIn($Column['column_name'], $Filter_Value);
                            }
                        } elseif ($Condtion['code'] == 'not_contains') {
                            if ($Column['code'] == 'user_source') {
                                $Architect->orWhereNotIn($Column['column_name'], $Filter_Value);
                            }

                            if ($Column['value_type'] == 'select') {
                                $Architect->orWhereNotIn($Column['column_name'], $Filter_Value);
                            } else {
                                $Filter_Value = explode(',', $Filter_Value);
                                $Architect->orWhereNotIn($Column['column_name'], $Filter_Value);
                            }
                        } elseif ($Condtion['code'] == 'between') {
                            if ($Column['value_type'] == 'date') {
                                $date_filter_value = explode(',', $Filter_Value);
                                $from_date_filter = $date_filter_value[0];
                                $from_date_filter_value = date('Y-m-d', strtotime($from_date_filter));

                                $to_date_filter = $date_filter_value[1];
                                $to_date_filter_value = date('Y-m-d', strtotime($to_date_filter));

                                $Architect->orWhereBetween(DB::raw('DATE(' . $Column['column_name'] . ')'), [$from_date_filter_value, $to_date_filter_value]);
                            }
                        }
                    }
                });
            }
        }

        if ($isAdminOrCompanyAdmin == 1 || $isMarketingDispatcherUser) {
        } elseif ($isSalePerson == 1) {
            $Architect->whereIn('architect.sale_person_id', $childSalePersonsIds);
        } elseif ($isChannelPartner != 0) {
            // $Architect->where('architect.added_by', Auth::user()->id);
            $ObjChannelPartner = ChannelPartner::select('sale_persons')->where('user_id', Auth::user()->id)->first();
            if($ObjChannelPartner){
                $Architect->where('architect.sale_person_id', $ObjChannelPartner->sale_persons);
            }
        } 

        if ($isorderby == 0) {
            $Architect->orderBy('architect.id', 'DESC');
        }
        $Architect = $Architect->get();

        $Architect = json_encode($Architect);
        $Architect = json_decode($Architect, true);
        $lastPageArchitectId = 0;
        $FirstPageArchitectId = 0;
        $ArchitectR = array_reverse($Architect);
        if (count($ArchitectR) > 0) {
            $FirstPageArchitectId = $Architect[0]['id'];
            $lastPageArchitectId = $ArchitectR[0]['id'];
        }

        $data = [];
        $data['architect'] = $Architect;
        $response = successRes('Get List');
        $response['view'] = view('architects_new/comman/list', compact('data'))->render();
        $response['lastPageArchitectId'] = $lastPageArchitectId;
        $response['FirstPageArchitectId'] = $FirstPageArchitectId;

        $response['count'] = count($Architect);
        $response['data'] = $data;
        $response['arr_where_clause'] = $arr_where_clause;
        // $response['arr_or_clause'] = $query;
        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }
    function searchSourceType(Request $request)
    {
        $searchKeyword = isset($request->q) ? $request->q : '';

        $sourcetype_master = CRMSettingSourceType::select('id', 'name as text');
        $sourcetype_master->where('crm_setting_source_type.status', 1);
        $sourcetype_master->where('crm_setting_source_type.name', 'like', '%' . $searchKeyword . '%');
        $sourcetype_master = $sourcetype_master->get();

        $data = [];
        foreach ($sourcetype_master as $source_master_key => $source_master_value) {
            $source_master['id'] = 'master-' . $source_master_value['id'];
            $source_master['text'] = $source_master_value['text'];
            array_push($data, $source_master);
        }

        foreach (getLeadSourceTypes() as $static_key => $static_value) {
            if ($static_value['id'] != 8) {
                $fix_source_data['id'] = $static_value['type'] . '-' . $static_value['id'];
                $fix_source_data['text'] = $static_value['lable'];
                array_push($data, $fix_source_data);
            }
        }

        $response = [];
        $response['results'] = $data;
        $response['pagination']['more'] = false;
        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }
    function searchSource(Request $request)
    {
        $isArchitect = isArchitect();
        $isSalePerson = isSalePerson();
        $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
        $isThirdPartyUser = isThirdPartyUser();
        $isChannelPartner = isChannelPartner(Auth::user()->type);

        $searchKeyword = $request->q;
        $source_type = explode('-', $request->source_type);

        if ($source_type[0] == 'user') {
            if (isset(getChannelPartners()[$source_type[1]]['short_name'])) {
                if ($isSalePerson == 1) {
                    $childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);

                    $salePerson = SalePerson::select('cities')
                        ->where('user_id', Auth::user()->id)
                        ->first();
                    $cities = [];
                    if ($salePerson) {
                        $cities = explode(',', $salePerson->cities);
                    } else {
                        $cities = [0];
                    }
                }

                $data = User::select('users.id', 'channel_partner.firm_name  AS text', 'users.phone_number');
                $data->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
                $data->where('users.status', 1);
                $data->where('users.type', $source_type[1]);

                if ($isSalePerson == 1) {
                    $data->where(function ($query) use ($cities, $childSalePersonsIds) {
                        $query->whereIn('users.city_id', $cities);

                        $query->orWhere(function ($query2) use ($childSalePersonsIds) {
                            foreach ($childSalePersonsIds as $key => $value) {
                                if ($key == 0) {
                                    $query2->whereRaw('FIND_IN_SET("' . $value . '",channel_partner.sale_persons)>0');
                                } else {
                                    $query2->orWhereRaw('FIND_IN_SET("' . $value . '",channel_partner.sale_persons)>0');
                                }
                            }
                        });
                    });
                }

                $data->where(function ($query) use ($searchKeyword) {
                    $query->where('channel_partner.firm_name', 'like', '%' . $searchKeyword . '%');
                });
                $data->limit(5);
                $data = $data->get();
            } else {
                $data = User::select('users.id', 'users.type', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"), 'users.phone_number');
                $data->where('users.status', 1);

                if ($source_type[1] == 202) {
                    // FOR ARCHITECT
                    if ($isSalePerson == 1) {
                        $salePerson = SalePerson::select('cities')
                            ->where('user_id', Auth::user()->id)
                            ->first();
                        $cities = [];
                        if ($salePerson) {
                            $cities = explode(',', $salePerson->cities);
                        } else {
                            $cities = [0];
                        }
                        $data->whereIn('users.city_id', $cities);
                    } elseif ($isChannelPartner != 0) {
                        $data->where('users.city_id', Auth::user()->city_id);
                    }

                    $data->whereIn('users.type', [201, 202]);
                } elseif ($source_type[1] == 302) {
                    // FOR ELECTRICIAN
                    if ($isSalePerson == 1) {
                        $salePerson = SalePerson::select('cities')
                            ->where('user_id', Auth::user()->id)
                            ->first();
                        $cities = [];
                        if ($salePerson) {
                            $cities = explode(',', $salePerson->cities);
                        } else {
                            $cities = [0];
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

                $data->limit(5);
                $data = $data->get();
                $newdata = [];
                foreach ($data as $key => $value) {
                    $data1['id'] = $value->id;
                    $label = '';
                    if ($value->type == 301 || $value->type == 201) {
                        $label = ' - NonPrime';
                    } elseif ($value->type == 302 || $value->type == 202) {
                        $label = ' - Prime';
                    } else {
                        $label = '';
                    }
                    $data1['text'] = $value->text . '(' . $value->phone_number . ')' . $label;
                    $data1['phone_number'] = $value->phone_number;
                    array_push($newdata, $data1);
                }
                $data = $newdata;
            }
        } elseif ($source_type[0] == 'master') {
            $data = CRMSettingSource::select('id', 'name as text');
            $data->where('crm_setting_source.status', 1);
            $data->where('crm_setting_source.source_type_id', $source_type[1]);
            $data->where('crm_setting_source.name', 'like', '%' . $searchKeyword . '%');
            $data->limit(5);
            $data = $data->get();
        } elseif ($source_type[0] == 'exhibition') {
            $data = Exhibition::select('id', 'name as text');
            $data->where('exhibition.name', 'like', '%' . $searchKeyword . '%');
            $data->limit(5);
            $data = $data->get();
        } else {
            $data = '';
        }

        $response = [];
        $response['results'] = $data;
        $response['pagination']['more'] = false;
        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }
    function searchStatus(Request $request)
    {
        $Search_Value = $request->q;

        $status = getArchitectsStatus();

        $data = [];
        foreach ($status as $status_key => $status_value) {
            if (in_array(Auth::user()->type, $status_value['access_user_type'])) {
                if ($Search_Value != '') {
                    if (preg_match('/' . $Search_Value . '/i', $status_value['name'])) {
                        $source_master['id'] = $status_value['id'];
                        $source_master['text'] = $status_value['name'];
                        array_push($data, $source_master);
                    }
                } else {
                    $source_master['id'] = $status_value['id'];
                    $source_master['text'] = $status_value['name'];
                    array_push($data, $source_master);
                }
            }
        }

        $response = [];
        $response['results'] = $data;
        $response['pagination']['more'] = false;
        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }
    function getDetail(Request $request)
    {
        $selectColumns = ['users.*', 'architect.firm_name', 'architect.sale_person_id', 'architect.instagram_link', 'architect.total_point_current', 'architect.total_point', 'sales.first_name', 'city_list.name as city_name', 'state_list.name as state_name', 'country_list.name as country_name', 'architect.status as architect_status'];
        $Architect = User::select($selectColumns);
        $Architect->selectRaw('CONCAT(users.first_name," ",users.last_name) AS account_name');
        $Architect->selectRaw('CONCAT(sales.first_name," ",sales.last_name) AS account_owner');
        $Architect->selectRaw('CONCAT(created_by_user.first_name," ",created_by_user.last_name) AS created_by');
        $Architect->selectRaw('CONCAT(updated.first_name," ",updated.last_name) AS updated_by');
        $Architect->leftJoin('architect', 'architect.user_id', '=', 'users.id');
        $Architect->leftJoin('users as sales', 'sales.id', '=', 'architect.sale_person_id');
        $Architect->leftJoin('users as created_by_user', 'users.created_by', '=', 'created_by_user.id');
        $Architect->leftJoin('users as updated', 'updated.id', '=', 'architect.updateby');
        $Architect->leftJoin('city_list', 'city_list.id', '=', 'users.city_id');
        $Architect->leftJoin('state_list', 'state_list.id', '=', 'city_list.state_id');
        $Architect->leftJoin('country_list', 'country_list.id', '=', 'city_list.country_id');
        $Architect->where('architect.user_id', $request->id);
        $Architect = $Architect->first();

        // $User  = User::find($Architect->duplicate_from);


        $data = [];

        if ($Architect) {
            $Architect = json_encode($Architect);
            $Architect = json_decode($Architect, true);

            $data['user'] = $Architect;
            // if ($User) {
            //     $data['user']['duplicate_from'] = $User;
            // } else {
                $data['user']['duplicate_from'] = "";
            // }

            $data['user']['user_type_lable'] = '';
            if ($Architect['type'] == 201) {
                $data['user']['user_type_lable'] = '<span class="badge rounded-pill bg-success ms-2" style="font-size: 12px;">NON-PRIME</span>';
            } elseif ($Architect['type'] == 202) {
                $data['user']['user_type_lable'] = '<span class="badge rounded-pill bg-success ms-2" style="font-size: 12px;">PRIME</span>';
            }

            $data['user']['user_status_lable'] = '<span class="badge badge-pill badge badge-soft-info font-size-14" style="height: fit-content;">' . getArchitectsStatus()[$Architect['architect_status']]['header_code'] . '</span>';

            if ($data['user']['tag'] != null) {
                $CRMLeadDealTag = DB::table('tag_master');
                $CRMLeadDealTag->select('tag_master.id AS id', 'tag_master.tagname AS text');
                $CRMLeadDealTag->where('tag_master.isactive', 1);
                $CRMLeadDealTag->where('tag_master.tag_type', 202);
                $CRMLeadDealTag->whereIn('tag_master.id', explode(',', $data['user']['tag']));
                $data['user']['tag'] = $CRMLeadDealTag->get();
            } else {
                $data['user']['tag'] = '';
            }

            $data['user']['sale_person'] = array();
            if ($data['user']['sale_person_id'] != null) {
                $data['user']['sale_person']['id'] = $data['user']['sale_person_id'];
                $data['user']['sale_person']['text'] = $data['user']['account_owner'];
            } else {
                $data['user']['sale_person']['id'] = "";
                $data['user']['sale_person']['text'] = "";
            }

            $bonusPoint = CRMLog::query();
            $bonusPoint->select('crm_log.points');
            $bonusPoint->leftJoin('users', 'users.id', '=', 'crm_log.user_id');
            $bonusPoint->where('crm_log.for_user_id', $data['user']['id']);
            $bonusPoint->where('crm_log.description','LIKE', '%joining bonus%');
            $bonusPoint->whereIn('crm_log.name', ['point-gain']);
            $bonusPoint = $bonusPoint->first();
            $newLifetimePoint = $bonusPoint ? ($data['user']['total_point'] - $bonusPoint->points)." + ".$bonusPoint->points : $data['user']['total_point'];
            
            $data['user']['lifetime_point_lable'] = $newLifetimePoint;
            $data['user']['lifetime_point'] = $data['user']['total_point'];
            $data['user']['redeemed_point'] = $data['user']['total_point'] - $data['user']['total_point_current'];
            $data['user']['available_point'] = $data['user']['total_point_current'];

            $created_at = date('d/m/Y g:i A', strtotime($data['user']['created_at']));
            $updated_at = date('d/m/Y g:i A', strtotime($data['user']['updated_at']));

            // $data['user'] = $Architect;
            $data['user']['created_at1'] = $created_at;
            $data['user']['updated_at1'] = $updated_at;
            // $data['user']['tag'] = $CRMLeadDealTag->get();

            $data['is_architect'] = 1;
            $data['is_electrician'] = 0;

            $data['contacts'] = getUserContactList($data['user']['id'])['data'];
            $data['updates'] = getUserNoteList($data['user']['id'])['data'];
            $data['files'] = getUserFileList($data['user']['id'])['data'];

            $data['calls'] = getUserAllOpenList($data['user']['id'])['call_data'];
            $data['meetings'] = getUserAllOpenList($data['user']['id'])['meeting_data'];
            $data['tasks'] = getUserAllOpenList($data['user']['id'])['task_data'];
            $data['max_open_actions'] = getUserAllOpenList($data['user']['id'])['max_open_actions'];

            $data['calls_closed'] = getUserAllCloseList($data['user']['id'])['close_call_data'];
            $data['meetings_closed'] = getUserAllCloseList($data['user']['id'])['close_meeting_data'];
            $data['tasks_closed'] = getUserAllCloseList($data['user']['id'])['close_task_data'];
            $data['max_close_actions'] = getUserAllCloseList($data['user']['id'])['max_close_actions'];

            // $data['timeline'] = getUserTimelineList($data['user']['id'])['data'];

            $data['point_log'] = getUserPointLogList($data['user']['id'])['view'];
            $data['user_log'] = getUserLogList($data['user']['id'])['view'];

            $response = successRes('Get List');
            $response['view'] = view('architects_new/detail', compact('data'))->render();
            $response['data'] = $data;
        } else {
            $response = errorRes('Lead Data Not Available');
        }

        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }
    function getDetailold(Request $request)
    {
        $Architect = Architect::select('*')
            ->where('user_id', $request->id)
            ->first();
        $data = [];
        if ($Architect) {
            $Architect = json_encode($Architect);
            $Architect = json_decode($Architect, true);
            $Architect_Lead_id = Lead::select('id')
                ->where('architect', $request->id)
                ->get();

            //// ARCHITECT DETAIL START ////
            $data['architect_detail'] = $Architect;
            $Account_Owner = User::find($Architect['sale_person_id']);
            $data['architect_detail']['account_owner'] = $Account_Owner['first_name'] . ' ' . $Account_Owner['last_name'];
            $User = User::find($request->id);
            $data['architect_detail']['account_name'] = $User['first_name'] . ' ' . $User['last_name'];
            $data['architect_detail']['mobile_no'] = $User['phone_number'];
            $data['architect_detail']['email_id'] = $User['email'];

            $Created_by = User::select(DB::raw("CONCAT(users.first_name,' ',users.last_name) as created_by"))
                ->where('id', $Architect['added_by'])
                ->first();
            $data['architect_detail']['created_by'] = $Created_by['created_by'];
            $data['architect_detail']['house_no'] = '';
            $data['architect_detail']['address_1'] = $User['address_line1'];
            $data['architect_detail']['address_2'] = $User['address_line2'];
            $data['architect_detail']['area'] = '';
            $City_name = CityList::find($User['city_id']);
            $data['architect_detail']['city'] = $City_name['name'];
            $State_name = StateList::find($User['state_id']);
            $data['architect_detail']['state'] = $State_name['name'];
            $Country_name = CountryList::find($User['country_id']);
            $data['architect_detail']['country'] = $Country_name['name'];
            $data['architect_detail']['last_modify_by'] = '';

            //// ARCHITECT DETAIL END ////

            //// ARCHITECT LEAD DETAIL START ////

            $Architect_Lead = Lead::select('*')
                ->where('architect', $request->id)
                ->where('is_deal', 0)
                ->get();
            if ($Architect_Lead) {
                $data['architect_lead'] = $Architect_Lead;

                foreach ($Architect_Lead as $key => $lead_value) {
                    $data['architect_lead'][$key]['status'] = getLeadStatus()[$lead_value['status']]['name'];
                    $sub_status = CRMSettingSubStatus::find($lead_value['sub_status']);
                    if ($sub_status != null) {
                        $data['architect_lead'][$key]['sub_status'] = $sub_status['name'];
                    } else {
                        $data['architect_lead'][$key]['sub_status'] = '-';
                    }

                    $site_stage = CRMSettingStageOfSite::find($lead_value['site_stage'])['name'];
                    if ($site_stage != null) {
                        $data['architect_lead'][$key]['site_stage'] = $site_stage;
                    } else {
                        $data['architect_lead'][$key]['site_stage'] = '-';
                    }

                    if ($lead_value['closing_date_time'] != null) {
                        $data['architect_lead'][$key]['closing_date_time'] = date('Y-m-d', strtotime($lead_value['closing_date_time']));
                    } else {
                        $data['architect_lead'][$key]['closing_date_time'] = '-';
                    }

                    $data['architect_lead'][$key]['url'] = route('crm.lead') . '?id=' . $lead_value['id'];
                }
            } else {
                $data['architect_lead'] = '';
            }
            //// ARCHITECT LEAD DETAIL END ////

            //// ARCHITECT DEAL DETAIL START ////

            $Architect_Deal = Lead::select('*')
                ->where('architect', $request->id)
                ->where('is_deal', 1)
                ->get();
            if ($Architect_Deal) {
                $data['architect_deal'] = $Architect_Deal;

                foreach ($Architect_Deal as $key => $deal_value) {
                    $data['architect_deal'][$key]['status'] = getLeadStatus()[$deal_value['status']]['name'];
                    $sub_status = CRMSettingSubStatus::find($deal_value['sub_status']);
                    if ($sub_status != null) {
                        $data['architect_deal'][$key]['sub_status'] = $sub_status['name'];
                    } else {
                        $data['architect_deal'][$key]['sub_status'] = '-';
                    }

                    $site_stage = CRMSettingStageOfSite::find($deal_value['site_stage'])['name'];
                    if ($site_stage != null) {
                        $data['architect_deal'][$key]['site_stage'] = $site_stage;
                    } else {
                        $data['architect_deal'][$key]['site_stage'] = '-';
                    }

                    if ($deal_value['closing_date_time'] != null) {
                        $data['architect_deal'][$key]['closing_date_time'] = date('Y-m-d', strtotime($deal_value['closing_date_time']));
                    } else {
                        $data['architect_deal'][$key]['closing_date_time'] = '-';
                    }

                    $data['architect_deal'][$key]['url'] = route('crm.deal') . '?id=' . $deal_value['id'];
                }
            } else {
                $data['architect_deal'] = '';
            }
            //// ARCHITECT LEAD DETAIL END ////

            //// ARCHITECT SERVICE DETAIL END ////
            ///
            //// ARCHITECT SERVICE DETAIL END ////

            //// ARCHITECT CONTACT DETAIL END ////

            $Architect_Contact = LeadContact::query();
            $Architect_Contact->select('crm_setting_contact_tag.name as tag_name', 'lead_contacts.*');
            $Architect_Contact->leftJoin('crm_setting_contact_tag', 'crm_setting_contact_tag.id', '=', 'lead_contacts.contact_tag_id');
            $Architect_Contact->whereIn('lead_id', $Architect_Lead_id);
            $Architect_Contact = $Architect_Contact->get();
            if ($Architect_Contact) {
                $data['architect_contact'] = $Architect_Contact;
                foreach ($Architect_Contact as $key => $contact_value) {
                    if ($contact_value['contact_tag_id'] == 0) {
                        $data['architect_contact'][$key]['tag_name'] = ucwords(strtolower(getUserTypeNameForLeadTag($contact_value['type'])));
                    } else {
                        $data['architect_contact'][$key]['tag_name'] = $contact_value['tag_name'];
                    }
                }
            } else {
                $data['architect_contact'] = '';
            }
            //// ARCHITECT CONTACT DETAIL END ////

            //// ARCHITECT FILES DETAIL END ////
            $Architect_File = LeadFile::query();
            $Architect_File->select('crm_setting_file_tag.name as tag_name', 'lead_files.*', 'users.first_name', 'users.last_name');
            $Architect_File->leftJoin('crm_setting_file_tag', 'crm_setting_file_tag.id', '=', 'lead_files.file_tag_id');
            $Architect_File->leftJoin('users', 'users.id', '=', 'lead_files.uploaded_by');
            $Architect_File->whereIn('lead_files.lead_id', $Architect_Lead_id);
            $Architect_File = $Architect_File->get();

            if ($Architect_File) {
                $data['architect_file'] = $Architect_File;
                foreach ($Architect_File as $key => $file_value) {
                    $name = explode('/', $file_value['name']);

                    $data['architect_file'][$key]['name'] = end($name);
                    $data['architect_file'][$key]['download'] = getSpaceFilePath($file_value['name']);
                    $data['architect_file'][$key]['created_at'] = $file_value['created_at'];
                }
            } else {
                $data['architect_file'] = '';
            }
            //// ARCHITECT FILES DETAIL END ////

            //// ARCHITECT NOTES DETAIL END ////
            $Architect_Note = LeadUpdate::query();
            $Architect_Note->select('lead_updates.id', 'lead_updates.message', 'lead_updates.user_id', 'lead_updates.task', 'lead_updates.task_title', 'users.first_name', 'users.last_name', 'lead_updates.created_at');
            $Architect_Note->leftJoin('users', 'users.id', '=', 'lead_updates.user_id');
            $Architect_Note->whereIn('lead_updates.lead_id', $Architect_Lead_id);
            $Architect_Note = $Architect_Note->get();

            if ($Architect_Note) {
                $data['architect_notes'] = $Architect_Note;
                foreach ($Architect_Note as $key => $note_value) {
                    $Architect_Note[$key]['message'] = strip_tags($note_value['message']);
                }
            } else {
                $data['architect_notes'] = '';
            }
            //// ARCHITECT NOTES DETAIL END ////

            $LeadCall = UserCallAction::query();
            $LeadCall->select('user_call_action.*', 'users.first_name', 'users.last_name');
            $LeadCall->where('user_call_action.user_id', $data['architect_detail']['id']);
            $LeadCall->where('is_closed', 0);
            $LeadCall->leftJoin('users', 'users.id', '=', 'user_call_action.user_id');
            $LeadCall->orderBy('user_call_action.id', 'desc');
            $LeadCall = $LeadCall->get();
            $LeadCall = json_encode($LeadCall);
            $LeadCall = json_decode($LeadCall, true);
            foreach ($LeadCall as $key => $value) {
                $LeadCall[$key]['date'] = convertDateAndTime($value['call_schedule'], 'date');
                $LeadCall[$key]['time'] = convertDateAndTime($value['call_schedule'], 'time');
                $ContactName = LeadContact::select('lead_contacts.id', 'lead_contacts.first_name', 'lead_contacts.last_name', DB::raw("CONCAT(lead_contacts.first_name,' ',lead_contacts.last_name) AS text"));
                $ContactName->where('lead_contacts.id', $value['contact_name']);
                $ContactName = $ContactName->first();
                if ($ContactName) {
                    $LeadCall[$key]['contact_name'] = $ContactName->text;
                } else {
                    $LeadCall[$key]['contact_name'] = '';
                }
            }

            $LeadCallClosed = UserCallAction::query();
            $LeadCallClosed->select('user_call_action.*', 'users.first_name', 'users.last_name');
            $LeadCallClosed->where('user_call_action.user_id', $data['architect_detail']['id']);
            $LeadCallClosed->where('is_closed', 1);
            $LeadCallClosed->leftJoin('users', 'users.id', '=', 'user_call_action.user_id');
            $LeadCallClosed->orderBy('user_call_action.closed_date_time', 'desc');
            $LeadCallClosed = $LeadCallClosed->get();
            $LeadCallClosed = json_encode($LeadCallClosed);
            $LeadCallClosed = json_decode($LeadCallClosed, true);
            foreach ($LeadCallClosed as $key => $value) {
                $LeadCallClosed[$key]['date'] = convertDateAndTime($value['closed_date_time'], 'date');
                $LeadCallClosed[$key]['time'] = convertDateAndTime($value['closed_date_time'], 'time');
                $ContactName = LeadContact::select('lead_contacts.id', 'lead_contacts.first_name', 'lead_contacts.last_name', DB::raw("CONCAT(lead_contacts.first_name,' ',lead_contacts.last_name) AS text"));
                $ContactName->where('lead_contacts.id', $value['contact_name']);
                $ContactName = $ContactName->first();
                if ($ContactName) {
                    $LeadCallClosed[$key]['contact_name'] = $ContactName->text;
                } else {
                    $LeadCallClosed[$key]['contact_name'] = '';
                }
            }

            $LeadMeeting = UserMeetingAction::query();
            $LeadMeeting->select('user_meeting_action.*', 'users.first_name', 'users.last_name');
            $LeadMeeting->where('user_meeting_action.user_id', $data['architect_detail']['id']);
            $LeadMeeting->where('is_closed', 0);
            $LeadMeeting->leftJoin('users', 'users.id', '=', 'user_meeting_action.user_id');
            $LeadMeeting->orderBy('user_meeting_action.id', 'desc');
            $LeadMeeting = $LeadMeeting->get();
            $LeadMeeting = json_encode($LeadMeeting);
            $LeadMeeting = json_decode($LeadMeeting, true);
            foreach ($LeadMeeting as $key => $value) {
                $LeadMeeting[$key]['date'] = convertDateAndTime($value['meeting_date_time'], 'date');
                $LeadMeeting[$key]['time'] = convertDateAndTime($value['meeting_date_time'], 'time');

                $LeadMeetingTitle = CRMSettingMeetingTitle::select('name')
                    ->where('id', $value['title_id'])
                    ->first();

                if ($LeadMeetingTitle) {
                    $LeadMeeting[$key]['title_name'] = $LeadMeetingTitle->name;
                } else {
                    $LeadMeeting[$key]['title_name'] = $LeadMeetingTitle->name;
                }

                $LeadMeetingParticipant = LeadMeetingParticipant::where('meeting_id', $value['id'])
                    ->orderby('id', 'asc')
                    ->get();
                $LeadMeetingParticipant = json_decode(json_encode($LeadMeetingParticipant), true);

                $UsersId = [];
                $ContactIds = [];
                foreach ($LeadMeetingParticipant as $sales_key => $value) {
                    if ($value['type'] == 'users') {
                        $UsersId[] = $value['reference_id'];
                    }
                }

                foreach ($LeadMeetingParticipant as $contact_key => $value) {
                    if ($value['type'] == 'lead_contacts') {
                        $ContactIds[] = $value['reference_id'];
                    }
                }

                $UserResponse = '';
                if (count($ContactIds) > 0) {
                    $LeadContact = LeadContact::select('lead_contacts.id', 'lead_contacts.first_name', 'lead_contacts.last_name', DB::raw("CONCAT(lead_contacts.first_name,' ',lead_contacts.last_name) AS full_name"));
                    $LeadContact->whereIn('lead_contacts.id', $ContactIds);
                    $LeadContact = $LeadContact->get();
                    if (count($LeadContact) > 0) {
                        foreach ($LeadContact as $User_key => $User_value) {
                            $UserResponse .= 'Contact - ' . $User_value['full_name'] . '<br>';
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
                            $UserResponse .= $getAllUserTypes[$User_value['type']]['short_name'] . ' - ' . $User_value['full_name'] . '<br>';
                        }
                    }
                }

                if ($UserResponse) {
                    $LeadMeeting[$key]['meeting_participant'] = $UserResponse;
                } else {
                    $LeadMeeting[$key]['meeting_participant'] = '';
                }
            }

            $LeadMeetingClosed = UserMeetingAction::query();
            $LeadMeetingClosed->select('user_meeting_action.*', 'users.first_name', 'users.last_name');
            $LeadMeetingClosed->where('user_meeting_action.user_id', $data['architect_detail']['id']);
            $LeadMeetingClosed->where('is_closed', 1);
            $LeadMeetingClosed->leftJoin('users', 'users.id', '=', 'user_meeting_action.user_id');
            $LeadMeetingClosed->orderBy('user_meeting_action.closed_date_time', 'desc');
            $LeadMeetingClosed = $LeadMeetingClosed->get();
            $LeadMeetingClosed = json_encode($LeadMeetingClosed);
            $LeadMeetingClosed = json_decode($LeadMeetingClosed, true);
            foreach ($LeadMeetingClosed as $key => $value) {
                $LeadMeetingClosed[$key]['date'] = convertDateAndTime($value['closed_date_time'], 'date');
                $LeadMeetingClosed[$key]['time'] = convertDateAndTime($value['closed_date_time'], 'time');

                $LeadMeetingTitle = CRMSettingMeetingTitle::select('name')
                    ->where('id', $value['title_id'])
                    ->first();
                if ($LeadMeetingTitle) {
                    $LeadMeetingClosed[$key]['title_name'] = $LeadMeetingTitle->name;
                } else {
                    $LeadMeetingClosed[$key]['title_name'] = ' ';
                }

                $LeadMeetingParticipant = LeadMeetingParticipant::where('meeting_id', $value['id'])
                    ->orderby('id', 'asc')
                    ->get();
                $LeadMeetingParticipant = json_decode(json_encode($LeadMeetingParticipant), true);

                $UsersId = [];
                $ContactIds = [];
                foreach ($LeadMeetingParticipant as $sales_key => $value) {
                    if ($value['type'] == 'users') {
                        $UsersId[] = $value['reference_id'];
                    }
                }

                foreach ($LeadMeetingParticipant as $contact_key => $value) {
                    if ($value['type'] == 'lead_contacts') {
                        $ContactIds[] = $value['reference_id'];
                    }
                }

                $UserResponse = '';
                if (count($ContactIds) > 0) {
                    $LeadContact = LeadContact::select('lead_contacts.id', DB::raw("CONCAT(lead_contacts.first_name,' ',lead_contacts.last_name) AS full_name"));
                    $LeadContact->whereIn('lead_contacts.id', $ContactIds);
                    $LeadContact = $LeadContact->get();
                    if (count($LeadContact) > 0) {
                        foreach ($LeadContact as $User_key => $User_value) {
                            $UserResponse .= 'Contact - ' . $User_value['full_name'] . '<br>';
                        }
                    }
                }

                if (count($UsersId) > 0) {
                    $User = User::select('users.id', 'users.type', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
                    $User->whereIn('users.id', $UsersId);
                    $User = $User->get();
                    if (count($User) > 0) {
                        foreach ($User as $User_key => $User_value) {
                            $UserResponse .= getAllUserTypes()[$User_value['type']]['short_name'] . ' - ' . $User_value['full_name'] . '<br>';
                        }
                    }
                }

                if ($UserResponse) {
                    $LeadMeetingClosed[$key]['meeting_participant'] = $UserResponse;
                } else {
                    $LeadMeetingClosed[$key]['meeting_participant'] = '';
                }
            }

            $UserTask = UserTaskAction::query();
            $UserTask->select('user_task_action.*', 'users.first_name', 'users.last_name');
            $UserTask->where('user_task_action.user_id', $data['architect_detail']['id']);
            $UserTask->where('is_closed', 0);
            $UserTask->leftJoin('users', 'users.id', '=', 'user_task_action.user_id');
            $UserTask->orderBy('user_task_action.id', 'desc');
            $UserTask = $UserTask->get();
            $UserTask = json_encode($UserTask);
            $UserTask = json_decode($UserTask, true);
            foreach ($UserTask as $key => $value) {
                $UserTask[$key]['date'] = convertDateAndTime($value['due_date_time'], 'date');
                $UserTask[$key]['time'] = convertDateAndTime($value['due_date_time'], 'time');

                $Taskowner = User::select('users.id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"));
                $Taskowner->where('users.status', 1);
                $Taskowner->where('users.id', $value['assign_to']);
                $Taskowner = $Taskowner->first();

                if ($Taskowner) {
                    $UserTask[$key]['task_owner'] = $Taskowner->text;
                } else {
                    $UserTask[$key]['task_owner'] = ' ';
                }
            }

            $UserTaskClosed = UserTaskAction::query();
            $UserTaskClosed->select('user_task_action.*', 'users.first_name', 'users.last_name');
            $UserTaskClosed->where('user_task_action.user_id', $data['architect_detail']['id']);
            $UserTaskClosed->where('is_closed', 1);
            $UserTaskClosed->leftJoin('users', 'users.id', '=', 'user_task_action.user_id');
            $UserTaskClosed->orderBy('user_task_action.closed_date_time', 'desc');
            $UserTaskClosed = $UserTaskClosed->get();
            $UserTaskClosed = json_encode($UserTaskClosed);
            $UserTaskClosed = json_decode($UserTaskClosed, true);
            foreach ($UserTaskClosed as $key => $value) {
                $UserTaskClosed[$key]['date'] = convertDateAndTime($value['closed_date_time'], 'date');
                $UserTaskClosed[$key]['time'] = convertDateAndTime($value['closed_date_time'], 'time');

                $Taskowner = User::select('users.id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"));
                $Taskowner->where('users.status', 1);
                $Taskowner->where('users.id', $value['assign_to']);
                $Taskowner = $Taskowner->first();

                if ($Taskowner) {
                    $UserTaskClosed[$key]['task_owner'] = $Taskowner->text;
                } else {
                    $UserTaskClosed[$key]['task_owner'] = ' ';
                }
            }

            $countCall = count($LeadCall);
            $countTask = count($UserTask);
            $countMeeting = count($LeadMeeting);
            $maxOpenAction = max($countCall, $countTask, $countMeeting);

            $countCallClosed = count($LeadCallClosed);
            $countTaskClosed = count($UserTaskClosed);
            $countMeetingClosed = count($LeadMeetingClosed);
            $maxClosedAction = max($countCallClosed, $countTaskClosed, $countMeetingClosed);

            $data['calls'] = $LeadCall;
            $data['tasks'] = $UserTask;
            $data['meetings'] = $LeadMeeting;

            $data['max_open_actions'] = $maxOpenAction;

            $data['calls_closed'] = $LeadCallClosed;
            $data['tasks_closed'] = $UserTaskClosed;
            $data['meetings_closed'] = $LeadMeetingClosed;

            $data['max_close_actions'] = $maxClosedAction;

            // $response['view'] = view('architects_new/detail', compact('data'))->render();
            $response['data'] = $data;
        } else {
            $response = errorRes('Lead Data Not Available');
        }

        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }
    function addContactArcClient(Request $request)
    {
        try {
            $Arc = User::query();
            $Arc->whereIn('type', [201, 202]);
            $Arc = $Arc->get();

            $is_error = '';
            foreach ($Arc as $key => $value) {
                try {
                    $UserContact = new UserContact();

                    $UserContact->user_id = $value->id;
                    $UserContact->contact_tag_id = 0;
                    $UserContact->first_name = $value->first_name;
                    $UserContact->last_name = $value->last_name;
                    $UserContact->phone_number = $value->phone_number;
                    $UserContact->alernate_phone_number = 0;
                    $UserContact->email = $value->email;
                    $UserContact->type = $value->type;
                    $UserContact->type_detail = 'user-' . $value->type . '-' . $value->id;

                    $UserContact->save();

                    if ($UserContact) {
                        $user_update = User::find($value->id);
                        $user_update->main_contact_id = $UserContact->id;
                        $user_update->save();
                    }

                    $response = successRes('Successfully');
                    $response['data'] = $Arc;
                    $response['data1'] = $Arc->count();
                } catch (\Exception $e) {
                    $is_success = 107;
                    $is_error .= 'User ID : ' . $value->id . ' = ERRPR(5360) - ' . $e->getMessage() . ' </br>';
                    $response = errorRes($e->getMessage(), 400);
                }
            }
        } catch (\Throwable $th) {
            $response = errorRes($th->getMessage(), 400);
        }

        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }
    function usergetArchitect(Request $request)
    {
        $query = $request->input('q');
        $User = array();
        $User = User::select('id', 'first_name', 'last_name', 'phone_number');
        $User->whereIn('type', ['201', '202']);
        $User->where(function ($userQuery) use ($query) {
            $userQuery->where('id', 'like', '%' . $query . '%')
                ->orWhere('first_name', 'like', '%' . $query . '%')
                ->orWhere('last_name', 'like', '%' . $query . '%')
                ->orWhere('phone_number', 'like', '%' . $query . '%');
        });
        // $User->where('status', 1);
        $User->limit(5);
        $User = $User->get();

        $response = array();
        $response['results'] = $User;
        $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');
    }
    function saveEditArchitect(Request $request) {

        $rules = [];
        $rules['user_id'] = 'required';
        $rules['user_type'] = 'required';
        $rules['user_phone_number'] = 'required|digits:10|regex:/^[1-9][0-9]*$/';

        if($request->user_type == 202) {
            $rules['user_email'] = 'required';
        }

        $rules['user_first_name'] = 'required';
        $rules['user_last_name'] = 'required';
        $rules['user_house_no'] = 'required';
        $rules['user_address_line1'] = 'required';
        $rules['user_area'] = 'required';
        $rules['user_pincode'] = 'required';
        $rules['user_city_id'] = 'required';
        $rules['architect_source_type'] = 'required';
        $rules['architect_sale_person_id'] = 'required';

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $response = [];
            $response['status'] = 0;
            $response['msg'] = $validator->errors()->first();
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors()->first();
            return response()->json($response)->header('Content-Type', 'application/json');
        } else {

            $source_type = $request->architect_source_type;
            $source_type_pieces = explode('-', $source_type);
            if ($source_type_pieces[0] == 'user') {
                if (!isset($request->architect_source_name) || $request->architect_source_name == '') {
                    $response = errorRes('Please select source');
                    return response()->json($response)->header('Content-Type', 'application/json');
                }
                $source_type_value = $request->architect_source_name;
            } elseif ($source_type_pieces[0] == 'textrequired') {
                if (!isset($request->architect_source_text) || $request->architect_source_text == '') {
                    $response = errorRes('Please enter source text');
                    return response()->json($response)->header('Content-Type', 'application/json');
                }
                $source_type_value = $request->architect_source_text;
            } else {
                $source_type_value = isset($request->architect_source_text) ? $request->architect_source_text : '';
            }

            $principal_architect_name = isset($request->architect_principal_architect_name) ? $request->architect_principal_architect_name : '';

            $UserType = 0;
            if(Auth::user()->type == 2 && $request->user_type == 201) {
                $UserType = 202;
            } else {
                $UserType = $request->user_type;
            }


            if ($UserType == 201 && $request->user_email == "") {
                $email = 'test.'.time() . '@whitelion.in';
            } else {
                $email = $request->user_email;
            }

            $isMovedFromPrimeToNonPrime = 0;

            $User = User::find($request->user_id);
            $Architect = Architect::find($User->reference_id);

            if ($Architect->type != $UserType) {
                $isMovedFromPrimeToNonPrime = 1;
            }

            $log_data = [];

            if ($User->type != $UserType) {
                $new_value = getArchitects()[$UserType]['short_name'];
                $old_value = getArchitects()[$User->type]['short_name'];
                $change_field = "User Type Change : " . $old_value . " To " . $new_value;

                $log_value = [];
                $log_value['field_name'] = "user_type";
                $log_value['new_value'] = $new_value;
                $log_value['old_value'] = $old_value;
                $log_value['description'] =  $change_field;

                array_push($log_data, $log_value);
            }


            if ($User->first_name != $request->user_first_name) {
                $new_value = $request->user_first_name;
                $old_value = $User->first_name;
                $change_field = "User First Name Change : " . $old_value . " To " . $new_value;

                $log_value = [];
                $log_value['field_name'] = "user_first_name";
                $log_value['new_value'] = $new_value;
                $log_value['old_value'] = $old_value;
                $log_value['description'] =  $change_field;

                array_push($log_data, $log_value);
            }

            if ($User->last_name != $request->user_last_name) {
                $new_value = $request->user_last_name;
                $old_value = $User->last_name;
                $change_field = "User Last Name Change : " . $old_value . " To " . $new_value;

                $log_value = [];
                $log_value['field_name'] = "user_last_name";
                $log_value['new_value'] = $new_value;
                $log_value['old_value'] = $old_value;
                $log_value['description'] =  $change_field;

                array_push($log_data, $log_value);
            }

            if ($User->house_no != $request->user_house_no) {
                $new_value = $request->user_house_no;
                $old_value = $User->house_no;
                $change_field = "User House No Change : " . $old_value . " To " . $new_value;

                $log_value = [];
                $log_value['field_name'] = "user_house_no";
                $log_value['new_value'] = $new_value;
                $log_value['old_value'] = $old_value;
                $log_value['description'] =  $change_field;

                array_push($log_data, $log_value);
            }

            if ($User->address_line1 != $request->user_address_line1) {
                $new_value = $request->user_address_line1;
                $old_value = $User->address_line1;
                $change_field = "User Address Line 1 Change : " . $old_value . " To " . $new_value;

                $log_value = [];
                $log_value['field_name'] = "user_address_line1";
                $log_value['new_value'] = $new_value;
                $log_value['old_value'] = $old_value;
                $log_value['description'] =  $change_field;

                array_push($log_data, $log_value);
            }

            if ($User->area != $request->user_area) {
                $new_value = $request->user_area;
                $old_value = $User->area;
                $change_field = "User Area Change : " . $old_value . " To " . $new_value;

                $log_value = [];
                $log_value['field_name'] = "user_area";
                $log_value['new_value'] = $new_value;
                $log_value['old_value'] = $old_value;
                $log_value['description'] =  $change_field;

                array_push($log_data, $log_value);
            }

            if ($User->pincode != $request->user_pincode) {
                $new_value = $request->user_pincode;
                $old_value = $User->pincode;
                $change_field = "User Pincode Change : " . $old_value . " To " . $new_value;

                $log_value = [];
                $log_value['field_name'] = "user_pincode";
                $log_value['new_value'] = $new_value;
                $log_value['old_value'] = $old_value;
                $log_value['description'] =  $change_field;

                array_push($log_data, $log_value);
            }

            if ($User->city_id != $request->user_city_id) {
                $new_value = $request->user_city_id;
                $old_value = $User->city_id;
                $change_field = "User City Change : " . CityList::find($old_value)['name'] . " To " . CityList::find($new_value)['name'];

                $log_value = [];
                $log_value['field_name'] = "user_city";
                $log_value['new_value'] = $new_value;
                $log_value['old_value'] = $old_value;
                $log_value['description'] =  $change_field;

                array_push($log_data, $log_value);
            }

            if ($Architect->principal_architect_name != $principal_architect_name) {
                $new_value = $principal_architect_name;
                $old_value = $Architect->principal_architect_name;
                $change_field = "User Principal Architect Name Change : " . $old_value . " To " . $new_value;

                $log_value = [];
                $log_value['field_name'] = "principal_architect_name";
                $log_value['new_value'] = $new_value;
                $log_value['old_value'] = $old_value;
                $log_value['description'] =  $change_field;

                array_push($log_data, $log_value);
            }

            if ($Architect->firm_name != $request->architect_firm_name) {
                $new_value = $principal_architect_name;
                $old_value = $Architect->principal_architect_name;
                $change_field = "User Firm Change : " . $old_value . " To " . $new_value;

                $log_value = [];
                $log_value['field_name'] = "architect_firm_name";
                $log_value['new_value'] = $new_value;
                $log_value['old_value'] = $old_value;
                $log_value['description'] =  $change_field;

                array_push($log_data, $log_value);
            }

            if ($Architect->sale_person_id != $request->architect_sale_person_id) {
                $new_value = $request->architect_sale_person_id;
                $old_value = $Architect->sale_person_id;

                $old_text = User::selectRaw(DB::raw('CONCAT(first_name," ", last_name) AS text'))->where('id', $old_value)->first()['text'];
                $new_text = User::selectRaw(DB::raw('CONCAT(first_name," ", last_name) AS text'))->where('id', $new_value)->first()['text'];
                $change_field = "User Sale Person Change : " . $old_text . " To " . $new_text;

                $log_value = [];
                $log_value['field_name'] = "sale_person";
                $log_value['new_value'] = $new_value;
                $log_value['old_value'] = $old_value;
                $log_value['description'] =  $change_field;

                array_push($log_data, $log_value);
            }

            if (date('m-d', strtotime($Architect->anniversary_date)) != date('m-d', strtotime($request->architect_anniversary_date))) {
                $new_value = date('m-d', strtotime($request->architect_anniversary_date));
                $old_value = date('m-d', strtotime($Architect->anniversary_date));
                $change_field = "User Anniversary Date Change : " . $old_value . " To " .  $new_value;

                $log_value = [];
                $log_value['field_name'] = "anniversary_date";
                $log_value['new_value'] = $new_value;
                $log_value['old_value'] = $old_value;
                $log_value['description'] =  $change_field;

                array_push($log_data, $log_value);
            }

            if (date('m-d', strtotime($Architect->birth_date)) != date('m-d', strtotime($request->architect_birth_date))) {
                $new_value = date('m-d', strtotime($request->architect_birth_date));
                $old_value = date('m-d', strtotime($Architect->birth_date));
                $change_field = "User Birth Date Change : " . $old_value . " To " .  $new_value;

                $log_value = [];
                $log_value['field_name'] = "birth_date";
                $log_value['new_value'] = $new_value;
                $log_value['old_value'] = $old_value;
                $log_value['description'] =  $change_field;

                array_push($log_data, $log_value);
            }

            foreach ($log_data as $log_value) {
                $user_log = new UserLog();
                $user_log->user_id = Auth::user()->id;
                $user_log->log_type = "ARCHITECT-LOG";
                $user_log->field_name = $log_value['field_name'];
                $user_log->old_value = $log_value['old_value'];
                $user_log->new_value = $log_value['new_value'];
                $user_log->reference_type = "Architect";
                $user_log->reference_id = $request->user_id;
                $user_log->transaction_type = "Architect Edit";
                $user_log->description = $log_value['description'];
                $user_log->source = "WEB";
                $user_log->entryby = Auth::user()->id;
                $user_log->entryip = $request->ip();
                $user_log->save();
            }

            

            if ($request->user_id != 0 && $UserType == 202 && $Architect->converted_prime == 0) {
                $converted_prime = 0;
            } else {
                $converted_prime = 1;
            }


            $User->first_name = $request->user_first_name;
            $User->last_name = $request->user_last_name;
            $User->email = $email;
            $User->dialing_code = '+91';
            $User->phone_number = $request->user_phone_number;
            if ($UserType == 202) {
                $PrivilegeJSON = [];
                $PrivilegeJSON['dashboard'] = 1;
                $User->privilege = json_encode($PrivilegeJSON);
            }
            $User->house_no = $request->user_house_no;
            $User->address_line1 = $request->user_address_line1;
            $User->address_line2 = '';
            $User->area = $request->user_area;
            $User->pincode = $request->user_pincode;
            $User->country_id = 0;
            $User->state_id = 0;
            $User->city_id = $request->user_city_id;
            $User->type = $UserType;
            $User->save();

            if($User) {

                if ($isMovedFromPrimeToNonPrime == 1) {
                    $Architect->prime_nonprime_date = date('Y-m-d H:i:s');
                }
                
                $Architect->user_id = $User->id;
                $Architect->type = $UserType;
                $Architect->firm_name = isset($request->architect_firm_name) ? $request->architect_firm_name : "";
                $Architect->sale_person_id = $request->architect_sale_person_id;
                $Architect->principal_architect_name = $principal_architect_name;
    
                if ($request->architect_birth_date != '') {
                    $Architect->birth_date = date('Y-m-d H:i:s', strtotime($request->architect_birth_date . "-1980"));
                } else {
                    $Architect->birth_date = null;
                }
    
    
                if ($request->architect_anniversary_date != '') {
                    $Architect->anniversary_date = date('Y-m-d H:i:s', strtotime($request->architect_anniversary_date . "-1980"));
                } else {
                    $Architect->anniversary_date = null;
                }
    
                if ($UserType == 202) {
                    $Architect->converted_prime = 1;
                }
                $Architect->source_type = $source_type;
                $Architect->source_type_value = $source_type_value;
                $Architect->added_by = Auth::user()->id;
                $Architect->updateby = Auth::user()->id;
                $Architect->updateip = $request->ip();
                $Architect->save();
            }
            

            if ($User && $Architect) { 

                if (($request->architect_source_type != null && $request->architect_source_type == 'user-201') || $request->architect_source_type == 'user-202' || $request->architect_source_type == 'user-301' || $request->architect_source_type == 'user-302' || $request->architect_source_type == 'user-101' || $request->architect_source_type == 'user-102' || $request->architect_source_type == 'user-103' || $request->architect_source_type == 'user-104' || $request->architect_source_type == 'user-105') {
                    $Main_source = User::where('id', $Architect->source_type_value)->first();

                    $UserContact = UserContact::select('*')->where('user_id', $User->id)->where('contact_tag_id', 0)->first();

                    if ($UserContact) {
                        $UserContact->user_id = $User->id;
                        $UserContact->contact_tag_id = 0;
                        $UserContact->first_name = $Main_source->first_name;
                        $UserContact->last_name = $Main_source->last_name;
                        $UserContact->phone_number = $Main_source->phone_number;
                        $UserContact->alernate_phone_number = 0;
                        $UserContact->email = $Main_source->email;
                        $UserContact->type = $Main_source->type;
                        $UserContact->type_detail = 'user-' . $Architect->type . '-' . $Architect->source_type_value;

                        $UserContact->save();
                    } else {
                        $UserContact = new UserContact();
                        $UserContact->user_id = $User->id;
                        $UserContact->contact_tag_id = 0;
                        $UserContact->first_name = $Main_source->first_name;
                        $UserContact->last_name = $Main_source->last_name;
                        $UserContact->phone_number = $Main_source->phone_number;
                        $UserContact->alernate_phone_number = 0;
                        $UserContact->email = $Main_source->email;
                        $UserContact->type = $Main_source->type;
                        $UserContact->type_detail = 'user-' . $Architect->type . '-' . $Architect->source_type_value;

                        $UserContact->save();
                    }
                }


                $debugLog['name'] = 'architect-edit';
                $debugLog['description'] = 'user #' . $User->id . '(' . $User->first_name . ' ' . $User->last_name . ') has been updated ';
                $response = successRes('Successfully saved user');
                $response['cov'] = $converted_prime;
                $response['user_id'] = $User->id;


                if (($request->user_id != 0 && $UserType == 202 && $converted_prime == 0)) {
                    // ARCHITECT JOINING BONUS
                    $pointValue = 50;
                    $Architect->total_point = $Architect->total_point + $pointValue;
                    $Architect->total_point_current = $Architect->total_point_current + $pointValue;
                    $Architect->save();
        
                    $debugLog = [];
                    $debugLog['for_user_id'] = $Architect->user_id;
                    $debugLog['name'] = 'point-gain';
                    $debugLog['points'] = $pointValue;
                    $debugLog['description'] = $pointValue . ' Point gained joining bonus ';
                    $debugLog['type'] = '';
                    saveCRMUserLog($debugLog);
        
                    $query = CRMHelpDocument::query();
                    $query->where('status', 1);
                    $query->where('type', 202);
                    $query->limit(30);
                    $query->orderBy('publish_date_time', 'desc');
                    $helpDocuments = $query->get();

                    $file = "";
                    foreach ($helpDocuments as $new_helpDocument) {
                        $file .= $new_helpDocument['file_name'].', ';
                    }

                    $configrationForNotify = configrationForNotify();

                    $Mailarr = [];
                    $Mailarr['from_name'] = $configrationForNotify['from_name'];
                    $Mailarr['from_email'] = $configrationForNotify['from_email'];
                    $Mailarr['to_email'] = $User->email;
                    $Mailarr['to_name'] = $configrationForNotify['to_name'];
                    $Mailarr['bcc_email'] = "sales@whitelion.in, sc@whitelion.in, poonam@whitelion.in";
                    $Mailarr['cc_email'] = "";
                    $Mailarr['subject'] = 'Welcome to the Whitelion';
                    $Mailarr['transaction_id'] = $User->id;
                    $Mailarr['transaction_name'] = "Architect";
                    $Mailarr['transaction_type'] = "Email";
                    $Mailarr['transaction_detail'] = "emails.signup_architect";
                    $Mailarr['attachment'] = rtrim($file, ", ");
                    $Mailarr['remark'] = "Architect Create";
                    $Mailarr['source'] = "Web";
                    $Mailarr['entryip'] = $request->ip();
                    
                    if (Config::get('app.env') == 'local') {
                        $Mailarr['to_email'] = $configrationForNotify['test_email'];
                        $Mailarr['bcc_email'] = implode(', ', $configrationForNotify['test_email_bcc']);
                    }
                    saveNotificationScheduler($Mailarr);


                    $helpDocument = CRMHelpDocument::query();
					$helpDocument->where('status', 1);
					$helpDocument->where('type', 202);
					$helpDocument->orderBy('publish_date_time', "desc");
					$helpDocument = $helpDocument->first();
					//TEMPLATE 7
					$whatsapp_controller = new WhatsappApiContoller;
					$perameater_request = new Request();
					$perameater_request['q_whatsapp_massage_mobileno'] = $User->phone_number;
					$perameater_request['q_whatsapp_massage_template'] = 'for_architect_download_mobileapp';
					$perameater_request['q_whatsapp_massage_attechment'] = $helpDocument ? getSpaceFilePath($helpDocument->file_name) : '';
					$perameater_request['q_broadcast_name'] = $User->first_name . ' ' . $User->last_name;
					$perameater_request['q_whatsapp_massage_parameters'] = array(
						'data[0]' => $User->email,
						'data[1]' => '111111'
					);
					$wp_response = $whatsapp_controller->sendTemplateMessage($perameater_request);
					$response["whatsapp"] = $wp_response;

                    $response = successRes();
                }

                $response['user_id'] = $User->id;
                return response()->json($response)->header('Content-Type', 'application/json');
            }

        }
    }
}
