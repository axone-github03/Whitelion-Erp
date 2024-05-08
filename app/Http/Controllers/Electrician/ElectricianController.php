<?php

namespace App\Http\Controllers\Electrician;

use App\Models\ChannelPartner;
use App\Models\CRMLog;
use App\Models\Electrician;
use App\Models\Inquiry;
use App\Models\SalePerson;
use App\Models\StateList;
use App\Models\CRMHelpDocument;
use App\Models\User;
use App\Models\UserContact;
use App\Models\Lead;
use App\Models\UserLog;
use App\Models\CityList;
use App\Models\UserNotes;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Whatsapp\WhatsappApiContoller;
use App\Http\Controllers\Controller;
use App\Models\CountryList;

class ElectricianController extends Controller
{
    public function __construct()
    {

        $this->middleware(function ($request, $next) {

            $tabCanAccessBy = array(0, 1, 2, 7, 9, 101, 102, 103, 104, 105, 13);

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


        $data = array();
        $data['title'] = "Electrician";
        $data['type'] = 302;
        $data['id'] = isset($request->id) ? $request->id : 0;
        $data['source_types'] = getArchitectsSourceTypes();
        $data['isSalePerson'] = isSalePerson();
        $data['is_electrician_module'] = 1;
        $data['searchUserId'] = (isset($request->id)) ? $request->id : "";
        $data['addView'] = (isset($request->add) && $request->add == 1) ? 1 : 0;
        $data['viewMode'] = (isset($request->view_mode) && $request->view_mode == 1) ? 1 : 0;

        $ArchitectsStatus = getElectricianStatus();
        $ArchitectsStatus[6]['id'] = 100;
        $ArchitectsStatus[6]['name'] = "All";
        $ArchitectsStatus[6]['code'] = "All";
        $ArchitectsStatus[6]['header_code'] = "All";
        $ArchitectsStatus[6]['sequence_id'] = 7;
        $ArchitectsStatus[6]['access_user_type'] = array(0, 9);

        $data['electrician_status'] = $ArchitectsStatus;

        $total_count = 0;
        foreach ($data['electrician_status'] as $key => $value) {
            if ($value['id'] == 100) {
                $data['electrician_status'][$key]['count'] = $total_count;
            } else {
                $status_count = Electrician::query();
                $status_count->where('electrician.status', $value['id']);
                $status_count->leftJoin('users', 'users.id', '=', 'electrician.user_id');
                if ($isSalePerson == 1) {
                    $status_count->whereIn('electrician.sale_person_id', $SalePersonsIds);
                } else if ($isChannelPartner != 0) {
                    $status_count->where('electrician.added_by', Auth::user()->id);
                } 
                // else if ($isTaleSalesUser == 1) {
                //     $status_count->whereIn('users.city_id', $TeleSalesCity);
                // }

                $status_count = $status_count->count();
                $data['electrician_status'][$key]['count'] = $status_count;
            }
            $total_count += $status_count;
        }
        $data['electrician_status_total_count'] = $total_count;

        return view('electricians_new/index', compact('data'));
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
        
        $data = array();
        $data['title'] = "Electrician";
        $data['type'] = 302;
        $data['source_types'] = getArchitectsSourceTypes();
        $data['isSalePerson'] = isSalePerson();
        $data['is_electrician_module'] = 1;
        $data['searchUserId'] = (isset($request->id)) ? $request->id : "";
        $data['addView'] = (isset($request->add) && $request->add == 1) ? 1 : 0;
        $data['viewMode'] = (isset($request->view_mode) && $request->view_mode == 1) ? 1 : 0;


        $ArchitectsStatus = getElectricianStatus();
        $ArchitectsStatus[6]['id'] = 100;
        $ArchitectsStatus[6]['name'] = "All";
        $ArchitectsStatus[6]['code'] = "All";
        $ArchitectsStatus[6]['header_code'] = "All";
        $ArchitectsStatus[6]['sequence_id'] = 7;
        $ArchitectsStatus[6]['access_user_type'] = array(0, 9);

        $data['electrician_status'] = $ArchitectsStatus;

        $total_count = 0;
        foreach ($data['electrician_status'] as $key => $value) {
            if ($value['id'] == 100) {
                $data['electrician_status'][$key]['count'] = $total_count;
            } else {
                $status_count = Electrician::query();
                $status_count->where('electrician.status', $value['id']);
                $status_count->leftJoin('users', 'users.id', '=', 'electrician.user_id');
                if ($isSalePerson == 1) {
                    $status_count->whereIn('electrician.sale_person_id', $SalePersonsIds);
                } else if ($isChannelPartner != 0) {
                    $status_count->where('electrician.added_by', Auth::user()->id);
                } 
                // else if ($isTaleSalesUser == 1) {
                //     $status_count->whereIn('users.city_id', $TeleSalesCity);
                // }
                $status_count = $status_count->count();
                $data['electrician_status'][$key]['count'] = $status_count;
            }
            $total_count += $status_count;
        }
        $data['electrician_status_total_count'] = $total_count;

        return view('electricians_new/table', compact('data'));
    }

    public function ajax(Request $request)
    {

        $isSalePerson = isSalePerson();
        $isChannelPartner = isChannelPartner(Auth::user()->type);
        $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
        $isMarketingDispatcherUser = isMarketingDispatcherUser();
        if ($isSalePerson == 1) {
            $SalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
        }

        $isTaleSalesUser = isTaleSalesUser();
        if ($isTaleSalesUser == 1) {
            $TeleSalesCity = TeleSalesCity(Auth::user()->id);
        }

        $viewMode = (isset($request->view_mode) && $request->view_mode == 1) ? 1 : 0;

        $searchColumns = array(
            'users.id',
            'users.email',
            'users.phone_number',
            'CONCAT(users.first_name," ",users.last_name)',
            'CONCAT(sale_person.first_name," ",sale_person.last_name)',
            'city_list.name'

        );

        if ($request->type == 302) {

            $sortingColumns = array(
                0 => 'users.id',
                1 => 'users.first_name',
                2 => 'users.phone_number',
                3 => 'electrician.sale_person_id',
                4 => 'electrician.total_point_current',
                5 => 'electrician.total_point',
                6 => 'users.status',
                7 => 'users.created_by',

            );
        } else {

            $sortingColumns = array(
                0 => 'users.id',
                1 => 'users.first_name',
                2 => 'users.phone_number',
                3 => 'electrician.sale_person_id',
                4 => 'users.status',
                5 => 'users.created_by',

            );
        }

        $selectColumns = array(
            'users.id',
            'users.type',
            'users.first_name',
            'users.last_name',
            'users.email',
            'users.phone_number',
            'electrician.sale_person_id',
            'electrician.status as electrician_status',
            'users.status as account_status',
            'users.created_at',
            'electrician.joining_date',
            'electrician.total_point_current',
            'electrician.total_point',
            'created_by_user.first_name as created_by_user_first_name',
            'created_by_user.last_name as created_by_user_last_name',
            'city_list.name as city_name'
        );

        $query = Electrician::query();
        $query->leftJoin('users', 'users.id', '=', 'electrician.user_id');
        $query->leftJoin('city_list', 'city_list.id', '=', 'users.city_id');
        $query->whereIn('electrician.type', [301, 302]);
        if (isset($request->status)) {
            if ($request->status != "" && $request->status != "all" && $request->status != 100) {
                $query->where('electrician.status', $request->status);
            } else {
                $query->whereIn('electrician.status', array(0, 1, 2, 3, 4));
            }
        }

        if (isset($request->search_user_id) && $request->search_user_id != "") {
            $query->where('users.id', $request->search_user_id);
        }

       
        //  else if ($isTaleSalesUser == 1) {
        //     $query->whereIn('users.city_id', $TeleSalesCity);
        // }


        $arr_where_clause = [];
        $arr_or_clause = [];
        $newdatares = [];
        $chk_condtion = '';
        $date_condtion = '';
        $sortingColumns = array();
        $isorderby = 0;


        if ($request->isAdvanceFilter == 1) {
            foreach (json_decode($request->AdvanceData, true) as $key => $filt_value) {

                $filter_value = '';
                if ($filt_value['clause'] == null || $filt_value['clause'] == '') {
                    $response = errorRes("Please Select Clause");
                    return response()->json($response)->header('Content-Type', 'application/json');
                    $chk_condtion .= ", 1";
                } else if ($filt_value['column'] == null || $filt_value['column'] == '') {
                    $response = errorRes("Please Select column");
                    return response()->json($response)->header('Content-Type', 'application/json');
                    $chk_condtion .= ", 2";
                } else if ($filt_value['condtion'] == null || $filt_value['condtion'] == '') {
                    $response = errorRes("Please Select condtion");
                    return response()->json($response)->header('Content-Type', 'application/json');
                    $chk_condtion .= ", 3";
                } else {
                    $column = getElectricianFilterColumn()[$filt_value['column']];
                    $condtion = getUserFilterCondtion()[$filt_value['condtion']];
                    if ($column['value_type'] == 'text') {
                        $chk_condtion .= ", 4";
                        if ($filt_value['value_text'] == null || $filt_value['value_text'] == '') {
                            $response = errorRes("Please enter value");
                            return response()->json($response)->header('Content-Type', 'application/json');
                        } else {
                            $chk_condtion .= ", 5";
                            $filter_value = $filt_value['value_text'];
                        }
                    } else if (($column['value_type'] == 'select') && ($condtion['value_type'] == 'single_select')) {
                        if ($filt_value['value_select'] == null || $filt_value['value_select'] == '') {
                            $response = errorRes("Please select value");
                            return response()->json($response)->header('Content-Type', 'application/json');
                        } else {
                            $chk_condtion .= ", 6";
                            $filter_value = $filt_value['value_select'];
                        }
                    } else if (($column['value_type'] == 'select') && ($condtion['value_type'] == 'multi_select')) {
                        $chk_condtion .= ", 7";

                        if (!isset($filt_value['value_multi_select']) && empty($filt_value['value_multi_select'])) {
                            $response = errorRes("Please select value");
                            return response()->json($response)->header('Content-Type', 'application/json');
                        } else {
                            $chk_condtion .= ", 8";
                            $filter_value = $filt_value['value_multi_select'];
                        }
                    } else if (($column['value_type'] == 'date') && ($condtion['value_type'] == 'single_select')) {
                        $chk_condtion .= ", 9";

                        if ($filt_value['value_date'] == null || $filt_value['value_date'] == '') {
                            $response = errorRes("Please enter date");
                            return response()->json($response)->header('Content-Type', 'application/json');
                        } else {
                            $chk_condtion .= ", 10";
                            $filter_value = $filt_value['value_date'];
                        }
                    } else if (($column['value_type'] == 'date') && ($condtion['value_type'] == 'between')) {

                        $chk_condtion .= ", 11";
                        if (($filt_value['value_from_date'] == null || $filt_value['value_from_date'] == '') && ($filt_value['value_to_date'] == null || $filt_value['value_to_date'] == '')) {
                            $response = errorRes("Please enter from to date");
                            return response()->json($response)->header('Content-Type', 'application/json');
                        } else {
                            $chk_condtion .= ", 12";
                            $filter_value = $filt_value['value_from_date'] . "," . $filt_value['value_to_date'];
                        }
                    } else if (($column['value_type'] == 'select_order_by') && ($condtion['value_type'] == 'single_select')) {

                        $isorderby = 1;
                        $chk_condtion .= ", 12";
                        if (($filt_value['value_select'] == null || $filt_value['value_select'] == '')) {
                            $response = errorRes("Please enter from to date");
                            return response()->json($response)->header('Content-Type', 'application/json');
                        } else {
                            $chk_condtion .= ", 12";
                            $filter_value = $filt_value['value_select'];
                        }
                    }

                    if ($filt_value['clause'] != 0) {
                        $chk_condtion .= ", 13";
                        $clause = getUserFilterClause()[$filt_value['clause']];
                    }


                    if ($filt_value['clause'] == 0) {
                        $newdata['clause'] = 0;
                        $newdata['column'] = $column['id'];
                        $newdata['condtion'] = $condtion['id'];
                        $newdata['value'] = $filter_value;

                        $newdatares = $newdata;

                        array_push($arr_where_clause, $newdata);
                        $chk_condtion .= ", 14";
                    } else if ($clause['clause'] == 'where') {
                        $chk_condtion .= ", 15";
                        $newdata['clause'] = $clause['id'];
                        $newdata['column'] = $column['id'];
                        $newdata['condtion'] = $condtion['id'];
                        $newdata['value'] = $filter_value;

                        array_push($arr_where_clause, $newdata);
                        $chk_condtion .= ", 16";
                    } else if ($clause['clause'] == 'orwhere') {
                        $newdata['clause'] = $clause['id'];
                        $newdata['column'] = $column['id'];
                        $newdata['condtion'] = $condtion['id'];
                        $newdata['value'] = $filter_value;

                        array_push($arr_or_clause, $newdata);
                    }
                }
            }

            foreach ($arr_where_clause as $wherekey => $objwhere) {
                $Column = getElectricianFilterColumn()[$objwhere['column']];
                $Condtion = getUserFilterCondtion()[$objwhere['condtion']];
                $lstDateFilter = getUserDateFilterValue();
                $Filter_Value = $objwhere['value'];
                if ($Condtion['code'] == 'is') {
                    if ($Column['value_type'] == 'date') {
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
                        } else {

                            $date_filter_value = explode(",", $objDateFilter['value']);
                            $from_date_filter = $date_filter_value[0];
                            $from_date_filter_value = date('Y-m-d', strtotime($from_date_filter));

                            $to_date_filter = $date_filter_value[1];
                            $to_date_filter_value = date('Y-m-d', strtotime($to_date_filter));

                            $query->whereBetween(DB::raw('DATE(' . $Column["column_name"] . ')'), [$from_date_filter_value, $to_date_filter_value]);
                        }
                    } else if ($Column['value_type'] == 'select') {
                        $query->where($Column['column_name'], $Filter_Value);
                    } else if ($Column['value_type'] == 'select_order_by') {
                        $newSortingColumns['column'] = $Column['column_name'];
                        if ($Filter_Value == 1) {
                           $newSortingColumns['sort'] = 'DESC';
                        } else {
                           $newSortingColumns['sort'] = 'ASC';
                        }
                        array_push($sortingColumns,$newSortingColumns);

                    } else {
                        $query->where($Column['column_name'], 'like', "%" . $Filter_Value . "%");
                    }
                } elseif ($Condtion['code'] == 'is_not') {
                    if ($Column['value_type'] == 'date') {

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
                        } else {

                            $date_filter_value = explode(",", $objDateFilter['value']);
                            $from_date_filter = $date_filter_value[0];
                            $from_date_filter_value = date('Y-m-d', strtotime($from_date_filter));

                            $to_date_filter = $date_filter_value[1];
                            $to_date_filter_value = date('Y-m-d', strtotime($to_date_filter));

                            $query->whereNotBetween(DB::raw('DATE(' . $Column["column_name"] . ')'), [$from_date_filter_value, $to_date_filter_value]);
                        }
                    } else if ($Column['value_type'] == 'select') {

                        $query->where($Column['column_name'], '!=', $Filter_Value);
                    } else {
                        $query->where($Column['column_name'], 'not like', "%" . $Filter_Value . "%");
                    }
                } else if ($Condtion['code'] == "contains") {
                    if ($Column['value_type'] == 'select') {
                        $query->whereIn($Column['column_name'], $Filter_Value);
                    } else {
                        $Filter_Value = explode(",", $Filter_Value);
                        $query->whereIn($Column['column_name'], $Filter_Value);
                    }
                } else if ($Condtion['code'] == "not_contains") {

                    if ($Column['value_type'] == 'select') {
                        $query->whereNotIn($Column['column_name'], $Filter_Value);
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

                        $Column = getElectricianFilterColumn()[$objor['column']];
                        $Condtion = getUserFilterCondtion()[$objor['condtion']];
                        $lstDateFilter = getUserDateFilterValue();
                        $Filter_Value = $objor['value'];

                        if ($Condtion['code'] == 'is') {
                            if ($Column['value_type'] == 'date') {

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

                                $query->orWhere($Column['column_name'], $Filter_Value);
                            } else if ($Column['value_type'] == 'select_order_by') {
                                $newSortingColumns['column'] = $Column['column_name'];
                                if ($Filter_Value == 1) {
                                   $newSortingColumns['sort'] = 'DESC';
                                } else {
                                   $newSortingColumns['sort'] = 'ASC';
                                }
                                array_push($sortingColumns,$newSortingColumns);

                            } else {
                                $query->orWhere($Column['column_name'], 'like', "%" . $Filter_Value . "%");
                            }
                        } elseif ($Condtion['code'] == 'is_not') {
                            if ($Column['value_type'] == 'date') {

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

                                $query->orWhere($Column['column_name'], '!=', $Filter_Value);
                            } else {
                                $query->orWhere($Column['column_name'], 'not like', "%" . $Filter_Value . "%");
                            }
                        } else if ($Condtion['code'] == "contains") {
                            if ($Column['value_type'] == 'select') {
                                if ($Column['code'] == 'leads_source') {
                                } else {
                                    $query->orWhereIn($Column['column_name'], $Filter_Value);
                                }
                            } else {
                                $Filter_Value = explode(",", $Filter_Value);
                                $query->orWhereIn($Column['column_name'], $Filter_Value);
                            }
                        } else if ($Condtion['code'] == "not_contains") {
                            if ($Column['value_type'] == 'select') {
                                $query->orWhereNotIn($Column['column_name'], $Filter_Value);
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
        

        if ($isAdminOrCompanyAdmin == 1 || $isMarketingDispatcherUser == 1) {
        } else if ($isSalePerson == 1) {
            $query->whereIn('electrician.sale_person_id', $SalePersonsIds);
        } else if ($isChannelPartner != 0) {
            $query->where('electrician.added_by', Auth::user()->id);
        }

        $Filter_lead_ids = $query->distinct()->pluck('users.id');

        $recordsTotal = $Filter_lead_ids->count();
        $recordsFiltered = $recordsTotal;
        
        // RECORDSFILTERED START
        $query = Electrician::query();
        $query->select('electrician.id');
        $query->leftJoin('users', 'users.id', '=', 'electrician.user_id');
        $query->leftJoin('users as sale_person', 'electrician.sale_person_id', '=', 'sale_person.id');
        $query->leftJoin('city_list', 'city_list.id', '=', 'users.city_id');
        $query->whereIn('users.id', $Filter_lead_ids);

        $isFilterApply = 0;
        if (isset($request['search']['value'])) {
            $isFilterApply = 1;
            $search_value = $request['search']['value'];
            $query->where(function ($query) use ($search_value, $searchColumns) {

                for ($i = 0; $i < count($searchColumns); $i++) {

                    if ($i == 0) {
                        $query->whereRaw($searchColumns[$i] . ' like ? ', [$search_value]);
                        $query->orWhereRaw($searchColumns[$i] . ' like ? ', ["%" . $search_value . "%"]);
                    } else {

                        $query->orWhereRaw($searchColumns[$i] . ' like ? ', [$search_value]);
                        $query->orWhereRaw($searchColumns[$i] . ' like ? ', ["%" . $search_value . "%"]);
                    }
                }
            });
        }
        $recordsFiltered = $query->count(); // when there is no search parameter then total number rows = total number filtered rows.


        $query = Electrician::query();
        $query->select($selectColumns);
        $query->leftJoin('users', 'users.id', '=', 'electrician.user_id');
        $query->leftJoin('users as created_by_user', 'users.created_by', '=', 'created_by_user.id');
        $query->leftJoin('users as sale_person', 'electrician.sale_person_id', '=', 'sale_person.id');
        $query->leftJoin('city_list', 'city_list.id', '=', 'users.city_id');
        $query->whereIn('users.id', $Filter_lead_ids);
        $query->limit($request->length);
        $query->offset($request->start);

        $isFilterApply = 0;

        $search_value = '';
        if (isset($request['search']['value'])) {
            $isFilterApply = 1;
            $search_value = $request['search']['value'];
            $query->where(function ($query) use ($search_value, $searchColumns) {

                for ($i = 0; $i < count($searchColumns); $i++) {

                    if ($i == 0) {
                        $query->whereRaw($searchColumns[$i] . ' like ? ', [$search_value]);
                        $query->orWhereRaw($searchColumns[$i] . ' like ? ', ["%" . $search_value . "%"]);
                    } else {

                        $query->orWhereRaw($searchColumns[$i] . ' like ? ', [$search_value]);
                        $query->orWhereRaw($searchColumns[$i] . ' like ? ', ["%" . $search_value . "%"]);
                    }
                }
            });
        }

        if ($isorderby == 0) {
            $query->orderBy('users.id', 'DESC');
        }else {
           foreach ($sortingColumns as $key => $value) {
              $query->orderBy($value['column'], $value['sort']);
           }
        }
         

        $data = $query->get();
        $data = json_decode(json_encode($data), true);
        // if ($isFilterApply == 1) {
        // 	$recordsFiltered = count($data);
        // }

        $viewData = array();

        foreach ($data as $key => $value) {

            $viewData[$key] = array();
            $routeElectrician = route('new.electricians.index') . "?id=" . $value['id'];
            if ($value['type'] == 301) {
                $viewData[$key]['name'] = '<a onclick="" target="_blank" href="' . $routeElectrician . '" class=""><b>' . "#" . highlightString($value['id'],$search_value)  . "  " . '</b>' .  highlightString(ucwords(strtolower($value['first_name'])) . " " . ucwords(strtolower($value['last_name'])),$search_value) . '</a>';
            } else {
                $viewData[$key]['name'] = '<a onclick="" target="_blank" href="' . $routeElectrician . '" class=""><b>' . "#" . highlightString($value['id'],$search_value)  . "  " . '</b>' .  highlightString(ucwords(strtolower($value['first_name'])) . " " . ucwords(strtolower($value['last_name'])),$search_value) . '</a><span class="badge rounded-pill bg-success ms-2">PRIME</span>';
            }
            $viewData[$key]['city_name'] = '<p class="text-muted mb-0">' . highlightString($value['city_name'],$search_value) . '</p>';
            $viewData[$key]['point'] = 'Current : <a onclick="pointLogs(' . $value['id'] . ')" href="javascript: void(0);" title="Detail">' . $value['total_point_current'] . '</a><br>Lifetime : <a onclick="pointLogs(' . $value['id'] . ')" href="javascript: void(0);" title="Detail">' . $value['total_point'] . '</a>';

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

            // $viewData[$key] = array();
            // $routeElectrician = route('new.electricians.index') . "?id=" . $value['id'];
            // if ($value['type'] == 301) {
            //     $viewData[$key]['name'] = '<a onclick="" target="_blank" href="' . $routeElectrician . '" class=""><b>' . "#" . highlightString($value['id'],$search_value)  . "  " . '</b>' .  highlightString(ucwords(strtolower($value['first_name'])) . " " . ucwords(strtolower($value['last_name'])),$search_value) . '</a>';
            // } else {
            //     $viewData[$key]['name'] = '<a onclick="" target="_blank" href="' . $routeElectrician . '" class=""><b>' . "#" . highlightString($value['id'],$search_value)  . "  " . '</b>' .  highlightString(ucwords(strtolower($value['first_name'])) . " " . ucwords(strtolower($value['last_name'])),$search_value) . '</a><span class="badge rounded-pill bg-success ms-2">PRIME</span>';
            // }

            // $viewData[$key]['email'] = '<p class="text-muted mb-0">' . highlightString($value['email'],$search_value) . '</p><p class="text-muted mb-0">' . highlightString($value['phone_number'],$search_value) . '</p>';
            // $viewData[$key]['status'] = getElectricianStatusStatusLable($value['electrician_status']);


            // $salePerson = User::query();
            // $salePerson->select('users.id as id', DB::raw('CONCAT(first_name," ", last_name) AS text'));
            // $salePerson = $salePerson->find($value['sale_person_id']);
            // if ($salePerson) {
            //     $viewData[$key]['account_owner'] = '<p class="text-muted mb-0">' . highlightString($salePerson->text,$search_value) . '</p>';
            // } else {
            //     $viewData[$key]['account_owner'] = '<p class="text-muted mb-0">" "</p>';
            // }
            
            // $viewData[$key]['created_by'] = '<span class="text-muted mb-0">' . highlightString($value['created_by_user_first_name'] . ' ' . $value['created_by_user_last_name'],$search_value) . ' </span><span><a data-bs-toggle="tooltip" data-bs-html="true"  href="javascript: void(0);" title="Created Date & Time : <br>' . $valueCreatedTime . '<br><br>Joining Date : <br>' . $valueJoiningDate . '" class="float-end h4 mb-0"><i class="bx bx-calendar"></i></a><span>';
            // $viewData[$key]['account_status'] = getUserStatusLable($value['account_status']);

            $uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';

            if ($value['electrician_status'] == 2) {
                // if (isTaleSalesUser() == 1) {
                $uiAction .= '<li class="list-inline-item px-2">';
                $uiAction .= '<a href="javascript:void(0)" onclick="getElectricianDetails(' . $value['id'] . ')"><img src="' . asset("assets/images/pending-request.svg") . '" alt="" ></a>';
                $uiAction .= '</li>';
                // }
            } else if ($value['electrician_status'] == 4) {
                $uiAction .= '<li class="list-inline-item px-2">';
                $uiAction .= '<img src="' . asset("assets/images/order-approve.svg") . '" alt="" >';
                $uiAction .= '</li>';

                if (isAdminOrCompanyAdmin() == 1) {
                    $uiAction .= '<li class="list-inline-item px-2">';
                    $uiAction .= '<a href="javascript:void(0)" onclick="getElectricianDetails(' . $value['id'] . ')"><img src="' . asset("assets/images/telecaller-approved.svg") . '" alt="" ></a>';
                    $uiAction .= '</li>';
                }
            } else if ($value['electrician_status'] == 1) {
                $uiAction .= '<li class="list-inline-item px-2">';
                $uiAction .= '<img src="' . asset("assets/images/order-approve.svg") . '" alt="" >';
                $uiAction .= '</li>';

                $uiAction .= '<li class="list-inline-item px-2">';
                $uiAction .= '<img src="' . asset("assets/images/order-approve.svg") . '" alt="" >';
                $uiAction .= '</li>';
            } else if ($value['electrician_status'] == 3) {
                $uiAction .= '<li class="list-inline-item px-2">';
                $uiAction .= '<a href="javascript:void(0)" onclick="getElectricianDetails(' . $value['id'] . ')"><img src="' . asset("assets/images/pending-request.svg") . '" alt="" ></a>';
                $uiAction .= '</li>';
            }

            $uiAction .= '</ul>';
            $viewData[$key]['action'] = $uiAction;
        }

        $jsonData = array(
            "draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($recordsTotal), // total number of records
            "recordsFiltered" => intval($recordsFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $viewData, // total data array
            "data1" => $data,
        );
        return $jsonData;
    }

    public function searchSalePerson(Request $request)
    {

        $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
        $isMarketingDispatcherUser = isMarketingDispatcherUser();
        $isSalePerson = isSalePerson();
        $isChannelPartner = isChannelPartner(Auth::user()->type);

        $searchKeyword = isset($request->q) ? $request->q : "";

        if ($isAdminOrCompanyAdmin == 1 || $isMarketingDispatcherUser == 1 || $isSalePerson == 1) {

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
            $response = array();
            $response['results'] = $SalePerson;
            $response['pagination']['more'] = false;
        } else if ($isChannelPartner != 0) {

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
            $response = array();
            $response['results'] = $SalePerson;
            $response['pagination']['more'] = false;
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function save(Request $request)
    {
        // if(Auth::user()->type == 2 && $request->user_type == 301) {
        //     $UserType = 302;
        // } else {
        $UserType = $request->user_type;
        // }

        $user_id = isset($request->user_id) ? $request->user_id : 0;
        // $user_type = isset($request->user_type) ? $request->user_type : 301;

        $isSalePerson = isSalePerson();
        $rules = array();
        $rules['user_id'] = 'required';
        $rules['user_type'] = 'required';
        $rules['user_first_name'] = 'required';
        $rules['user_last_name'] = 'required';
        // $rules['user_address_line1'] = 'required';
        // $rules['user_pincode'] = 'required';
        // $rules['user_country_id'] = 'required';
        // $rules['user_state_id'] = 'required';
        $rules['user_city_id'] = 'required';
        if($user_id != 0 && isset($request->user_status) && in_array($request->user_status,[0,3,8])){
        }else {
            $rules['user_phone_number'] = 'required|digits:10|regex:/^[1-9][0-9]*$/';
            if ($UserType == 302) {
                // $rules['user_email'] = 'required';
                $rules['user_address_line1'] = 'required';
                //$rules['user_pincode'] = 'required';
            }
        }

        if($user_id != 0 && isset($request->user_status)) {
            $rules['electrician_note'] = 'required';
        }


        if ($isSalePerson == 0) {
            // $rules['user_status'] = 'required';
            $rules['electrician_sale_person_id'] = 'required';
        }

        if ($isSalePerson == 1 && $UserType == 302 && ($request->user_id != "" && $request->user_id != 0)) {
            $rules = array();
            $rules['user_id'] = 'required';
            $rules['user_type'] = 'required';
            $User = User::find($request->user_id);
            $requireFieldForSalesUser = array();
            if ($User) {

                if ($User->address_line1 == "") {
                    $rules['user_address_line1'] = 'required';
                    $requireFieldForSalesUser[] = "user_address_line1";
                }
            }
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {

            $response = array();
            $response['status'] = 0;
            $response['msg'] = $validator->errors()->first();
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors()->first();
        } else {

            $uploadedFile = "";

            if ($isSalePerson == 1 && $UserType == 302 && ($request->user_id != "" && $request->user_id != 0) != "") {


                if (count($requireFieldForSalesUser) > 0) {
                    if (in_array('user_address_line1', $requireFieldForSalesUser)) {

                        $User->address_line1 = $request->user_address_line1;
                    }
                    $User->type = $UserType;
                    $User->save();
                    $Electrician = Electrician::find($User->reference_id);
                    $Electrician->type = $UserType;
                    $Electrician->save();
                    $debugLog['name'] = "electrician-edit";
                    $debugLog['description'] = "user #" . $User->id . "(" . $User->first_name . " " . $User->last_name . ") has been updated ";
                    $response = successRes("Successfully saved user");
                    saveDebugLog($debugLog);
                    return response()->json($response)->header('Content-Type', 'application/json');
                } else {

                    $User->type = $UserType;
                    $User->save();
                    $Electrician = Electrician::find($User->reference_id);
                    $Electrician->type = $UserType;
                    $Electrician->save();
                    $debugLog['name'] = "electrician-edit";
                    $debugLog['description'] = "user #" . $User->id . "(" . $User->first_name . " " . $User->last_name . ") has been updated ";
                    $response = successRes("Successfully saved user");
                    saveDebugLog($debugLog);



                    return response()->json($response)->header('Content-Type', 'application/json');
                }
            }




            if ($isSalePerson == 1) {
                $sale_person_id = Auth::user()->id;
            } else {
                $sale_person_id = $request->electrician_sale_person_id;
            }

            // if ($user_type == 301) {

            $email = time() . "@whitelion.in";

            // } else {
            // 	$email = $request->user_email;
            // }

            $phone_number = $request->user_phone_number;

            $alreadyEmail = User::query();
            $alreadyEmail->where('email', $email);
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

            $AllUserTypes = getAllUserTypes();

            if ($alreadyEmail && !in_array($request->user_status,[5,0])) {

                $response = errorRes("Email already exists(" . $AllUserTypes[$alreadyEmail->type]['name'] . "), Try with another email");
            } else if ($alreadyPhoneNumber && !in_array($request->user_status,[5,0])) {
                $response = errorRes("Phone number already exists(" . $AllUserTypes[$alreadyPhoneNumber->type]['name'] . "), Try with another phone number");
            } else {

                $user_address_line1 = isset($request->user_address_line1) ? $request->user_address_line1 : '';
                // $user_address_line2 = isset($request->user_address_line2) ? $request->user_address_line2 : '';
                $user_pincode = isset($request->user_pincode) ? $request->user_pincode : '';

                $user_company_id = 1;

                if ($request->hasFile('electrician_pan_card')) {

                    $folderPathofFile = '/s/architect';
                    $fileObject2 = $request->file('electrician_pan_card');
                    $extension = $fileObject2->getClientOriginalExtension();

                    $fileName2 = time() . mt_rand(10000, 99999) . '.' . $extension;

                    $destinationPath = public_path($folderPathofFile);

                    $fileObject2->move($destinationPath, $fileName2);

                    if (File::exists(public_path($folderPathofFile . "/" . $fileName2))) {

                        $uploadedFile = $folderPathofFile . "/" . $fileName2;
                        //START UPLOAD FILE ON SPACES
                        $spaceUploadResponse = uploadFileOnSpaces(public_path($uploadedFile), $uploadedFile);
                        if ($spaceUploadResponse != 1) {
                            $uploadedFile = "";
                        } else {

                            unlink(public_path($uploadedFile));
                        }
                        //END UPLOAD FILE ON SPACES

                    }
                }

                $UserType = 0;
                if ($request->user_id == 0) {

                    $User = User::where('type', 10000)->where(function ($query) use ($request) {
                        $query->where('email', $request->user_email)->orWhere('phone_number', $request->user_phone_number);
                    })->first();

                    if ($User) {

                        $User->type = $UserType;
                        $User->reference_type = "electrician";
                        $User->reference_id = 0;
                    } else {


                        $User = new User();
                        $User->created_by = Auth::user()->id;
                        $User->password = Hash::make("111111");
                        $User->last_active_date_time = date('Y-m-d H:i:s');
                        $User->last_login_date_time = date('Y-m-d H:i:s');
                        $User->avatar = "default.png";
                    }
                    //$User->is_sent_mail = 0;

                    $Electrician = new Electrician();
                    $User->status = 1;
                    if ($isSalePerson != 1) {
                        $Electrician->status = 6;
                    }
                    $converted_prime = 1;

                    $UserType = $request->user_type;
                } else {
                    $User = User::find($request->user_id);
                    $Electrician = Electrician::find($User->reference_id);
                    if (!$Electrician) {

                        $response = errorRes("Something went wrong");
                    }

                    $converted_prime = $Electrician->converted_prime;

                    if ($uploadedFile == "") {
                        $uploadedFile = $Electrician->pan_card;
                    }

                    if(Auth::user()->type == 2 && $request->user_type == 301) {
                        $UserType = 302;
                    } else {
                        $UserType = $request->user_type;
                    }

                    //  USER LOG START
                    $log_data = [];

                    if ($User->type != $UserType) {
                        $new_value = getElectricians()[$UserType]['short_name'];
                        $old_value = getElectricians()[$User->type]['short_name'];
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

                    if ($Electrician->status != $request->user_status) {
                        $new_value = $request->user_status;
                        $old_value = $Electrician->status;
                        $change_field = "User Status Change : " . getElectricianStatus()[$old_value]['name'] . " To " . getElectricianStatus()[$new_value]['name'];

                        $log_value = [];
                        $log_value['field_name'] = "user_status";
                        $log_value['new_value'] = $new_value;
                        $log_value['old_value'] = $old_value;
                        $log_value['description'] =  $change_field;

                        array_push($log_data, $log_value);
                    }

                    if ($Electrician->sale_person_id != $sale_person_id) {
                        $new_value = $sale_person_id;
                        $old_value = $Electrician->sale_person_id;

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

                    foreach ($log_data as $log_value) {
                        $user_log = new UserLog();
                        $user_log->user_id = Auth::user()->id;
                        $user_log->log_type = "ELECTRICIAN-LOG";
                        $user_log->field_name = $log_value['field_name'];
                        $user_log->old_value = $log_value['old_value'];
                        $user_log->new_value = $log_value['new_value'];
                        $user_log->reference_type = "Electrician";
                        $user_log->reference_id = $request->user_id;
                        $user_log->transaction_type = "Electrician Edit";
                        $user_log->description = $log_value['description'];
                        $user_log->source = "WEB";
                        $user_log->entryby = Auth::user()->id;
                        $user_log->entryip = $request->ip();
                        $user_log->save();
                    }
                    $Electrician->status = $request->user_status;
                }
                $User->first_name = $request->user_first_name;
                $User->last_name = $request->user_last_name;
                $User->email = $email;
                $User->dialing_code = "+91";
                $User->phone_number = $request->user_phone_number;
                $User->ctc = 0;
                if ($UserType == 302 || $request->user_id == 0) {
                    $PrivilegeJSON = array();
                    $PrivilegeJSON['dashboard'] = 1;
                    $User->privilege = json_encode($PrivilegeJSON);
                }
                $User->house_no = $request->user_house_no;
                $User->address_line1 = $user_address_line1;
                $User->address_line2 = "";
                $User->area = $request->user_area;
                $User->pincode = $user_pincode;
                $User->country_id = CityList::find($request->user_city_id)['country_id'];
                $User->state_id = CityList::find($request->user_city_id)['state_id'];
                $User->city_id = $request->user_city_id;
                $User->company_id = $user_company_id;
                $User->duplicate_from = $request->duplicate_from;
                $User->type = $UserType;
                $User->reference_type = 0;
                $User->reference_id = 0;
                if ($request->user_status == 1) {
                    $User->joining_date = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s')));
                }
                $User->save();

                $Electrician->user_id = $User->id;
                $Electrician->type = $UserType;
                $Electrician->sale_person_id = $sale_person_id;
                $Electrician->pan_card = $uploadedFile;
                $Electrician->added_by = Auth::user()->id;
                if ($request->user_status == 1) {
                    $Electrician->joining_date = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s')));
                }

                if ($request->user_id == 0) {
                    $Electrician->entryby = Auth::user()->id;
                    $Electrician->entryip = $request->ip();
                } else {
                    $Electrician->updateby = Auth::user()->id;
                    $Electrician->updateip = $request->ip();
                }

                if (Auth::user()->type == 9) {
                    if (isset($request->electrician_note) || $request->electrician_note != '') {
                        $Electrician->tele_note = $request->electrician_note;

                        $UserUpdate = new UserNotes();
                        $UserUpdate->user_id = $request->user_id;
                        // .' And Status Is '. getArchitectsStatus()[$request->user_status]['header_code']
                        $UserUpdate->note = $request->electrician_note;
                        $UserUpdate->note_type = "Note";
                        $UserUpdate->note_title = "Note - ".getElectricianStatus()[$request->user_status]['header_code'];
                        $UserUpdate->entryby = Auth::user()->id;
                        $UserUpdate->entryip = $request->ip();
                        $UserUpdate->updateby = Auth::user()->id;
                        $UserUpdate->updateip = $request->ip();
                        $UserUpdate->save();
                    } else {
                        $Electrician->tele_note = '';
                    }

                    // if (isset($request->architect_instagram)) {
                    //     $Electrician->instagram_link = $request->architect_instagram;
                    // } else {
                    //     $Electrician->instagram_link = '';
                    // }
                }

                if (in_array(Auth::user()->type, [0, 1])) {
                    if (isset($request->electrician_note) || $request->electrician_note != '') {
                        $Electrician->hod_note = $request->electrician_note;

                        $UserUpdate = new UserNotes();
                        $UserUpdate->user_id = $request->user_id;
                        $UserUpdate->note = $request->electrician_note;
                        $UserUpdate->note_type = "Note";
                        $UserUpdate->note_title = "Note - ".getElectricianStatus()[$request->user_status]['header_code'];
                        $UserUpdate->entryby = Auth::user()->id;
                        $UserUpdate->entryip = $request->ip();
                        $UserUpdate->updateby = Auth::user()->id;
                        $UserUpdate->updateip = $request->ip();
                        $UserUpdate->save();
                    } else {
                        $Electrician->hod_note = '';
                    }
                }

                $Electrician->save();

                $User->reference_type = "electrician";
                $User->reference_id = $Electrician->id;
                $User->save();

                if ($isSalePerson == 0) {
                    if (isset($request->user_status)) {
                        saveUserStatus($User->id, $request->user_status, $request->ip());
                    } else {
                        saveUserStatus($User->id, 2, $request->ip());
                    }
                }

                if ($User && $Electrician) {
                    if ($request->user_id == 0) {
                        $UserContact = new UserContact();

                        $UserContact->user_id = $User->id;
                        $UserContact->contact_tag_id = 0;
                        $UserContact->first_name = $request->user_first_name;
                        $UserContact->last_name = $request->user_last_name;
                        $UserContact->phone_number = $request->user_phone_number;
                        $UserContact->alernate_phone_number = 0;
                        $UserContact->email = $request->user_email;
                        $UserContact->type = $UserType;
                        $UserContact->type_detail = "user-" . $UserType . '-' . $User->id;

                        $UserContact->save();


                        if ($UserContact) {
                            $user_update = User::find($User->id);
                            $user_update->main_contact_id = $UserContact->id;
                            $user_update->save();
                        }
                    }else{
                        $user_update = User::find($User->id);
                        if(in_array($Electrician->status,[1])){
                            $user_update->status = 1;
                            $user_update->save();
                        }else if (in_array($Electrician->status,[0,5])) {
                            $user_update->status = 0;
                            $user_update->save();
                        }
                    }
                }

                $debugLog = array();
                if ($request->user_id != 0) {
                    $debugLog['name'] = "electrician-edit";
                    $debugLog['description'] = "user #" . $User->id . "(" . $User->first_name . " " . $User->last_name . ") has been updated ";
                    $response = successRes("Successfully saved user");
                } else {
                    if (($request->user_id != 0 && $UserType == 202 && $converted_prime == 0)) {

                    }
                    $mobileNotificationTitle = "New Electrician Create";
                    $mobileNotificationMessage = "New Electrician " . $User->first_name . " " . $User->last_name . " Added By " . Auth::user()->first_name . " " . Auth::user()->last_name;
                    $notificationUserids = getParentSalePersonsIds($Electrician->sale_person_id);
                    $notificationUserids[] = $Electrician->sale_person_id;
                    $UsersNotificationTokens = UsersNotificationTokens($notificationUserids);
                    sendNotificationTOAndroid($mobileNotificationTitle, $mobileNotificationMessage, $UsersNotificationTokens, 'Electrician', $User);

                    //TEMPLATE 8

                    $helpDocument = CRMHelpDocument::query();
					$helpDocument->where('status', 1);
					$helpDocument->where('type', 302);
					$helpDocument->orderBy('publish_date_time', "desc");
					$helpDocument = $helpDocument->first();
				
					$whatsapp_controller = new WhatsappApiContoller;
					$perameater_request = new Request();
					$perameater_request['q_whatsapp_massage_template'] = 'for_electrician_download_app';
					$perameater_request['q_whatsapp_massage_mobileno'] = $User->phone_number;
					$perameater_request['q_whatsapp_massage_attechment'] = $helpDocument ? getSpaceFilePath($helpDocument->file_name) : '';
					$perameater_request['q_broadcast_name'] =  $User->first_name . ' ' . $User->last_name;
					$perameater_request['q_whatsapp_massage_parameters'] = array();

					$wp_response = $whatsapp_controller->sendTemplateMessage($perameater_request);
					$response["whatsapp"] = $wp_response;

                    $debugLog['name'] = "electrician-add";
                    $debugLog['description'] = "user #" . $User->id . "(" . $User->first_name . " " . $User->last_name . ") has been added";
                    $response = successRes("Successfully added user");
                }
                saveDebugLog($debugLog);
            }
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function detail(Request $request)
    {

        // $User = User::join('city_list', 'users.city_id', '=', 'city_list.id');
        // $User->join('state_list', 'city_list.state_id', '=', 'state_list.id');
        // $User->join('country_list', 'city_list.country_id', '=', 'country_list.id');
        // $User->select('users.*', 'city_list.*', 'country_list.*', '');
        // $User->select('users.*', 'city_list.*', 'country_list.*', 'state_list.*');
        // $User->where('users.id', $request->id);
        // $User->whereIn('users.type', array(301, 302));
        // $User = $User->get();

        $User = User::select('users.*', 'city_list.id as city_id', 'city_list.country_id as city_country_id', 'city_list.state_id as city_state_id', 'city_list.name as city_name', 'state_list.name as state_name', 'country_list.name as country_name');
        $User->leftJoin('city_list', 'city_list.id', '=', 'users.city_id');
        $User->leftJoin('state_list', 'state_list.id', '=', 'city_list.state_id');
        $User->leftJoin('country_list', 'country_list.id', '=', 'city_list.country_id');
        $User->where('users.id', $request->id);
        $User->whereIn('users.type', array(301, 302));
        $User = $User->first();

        $data = array();
        if ($User) {

            $Electrician = Electrician::find($User->reference_id);
            $data = $User;
            if ($Electrician) {
                $isSalePerson = isSalePerson();

                // $data['country']['id'] = $data['city_id'];
                // $data['country']['name'] = $data['city_name'];

                // $data['state']['id'] = $data['state_id'];
                // $data['state']['name'] = $data['state_name'];

                // $data['city']['id'] = $data['city_id'];
                // $data['city']['name'] = $data['country_name'];

                if ($isSalePerson == 1) {
                    $SalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
                }

                if ($isSalePerson == 0 || ($isSalePerson == 1 && in_array($Electrician->sale_person_id, $SalePersonsIds))) {

                    $salePerson = User::query();
                    $salePerson->select('users.id as id', DB::raw('CONCAT(first_name," ", last_name) AS text'));
                    $salePerson = $salePerson->find($Electrician->sale_person_id);

                    $dupElectricion  = User::find($User->duplicate_from);
                    if ($dupElectricion) {
                        $dupElectricion['text'] = $dupElectricion['id'] . '-' . $dupElectricion['first_name'] . ' ' . $dupElectricion['last_name'] . '-' . $dupElectricion['phone_number'];
                    } else {
                        $dupElectricion['text'] = "";
                    }

                    $data['status_text'] = getElectricianStatus()[$data['status']]['name'];
                    $response = successRes("Successfully get user");
                    $response['data'] = $data;
                    $response['data']['duplicate_from'] = $dupElectricion;

                    $response['data']['electrician'] = $Electrician;
                    $response['data']['electrician']['sale_person'] = $salePerson;
                } else {
                    $response = errorRes("Invalid id");
                }

                if ($Electrician['pan_card'] != "") {
                    $Electrician['pan_card'] = '(<a target="_blank" href="' . getSpaceFilePath($Electrician['pan_card']) . '" title="File">Download</i></a>)';
                }
            } else {
                $response = errorRes("Invalid id");
            }

            $response = successRes();
            $response['data'] = $User;
            
        } else {
            $response = errorRes("Invalid id");
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function export(Request $request)
    {

        $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
        $isMarketingDispatcherUser = isMarketingDispatcherUser();
        $isSalePerson = isSalePerson();
        $isChannelPartner = isChannelPartner(Auth::user()->type);

        if ($isSalePerson == 1) {
            $SalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
        }

        $columns = array(
            'users.id',
            'users.first_name',
            'users.last_name',
            'users.email',
            'users.dialing_code',
            'users.phone_number',
            'users.status',
            'users.created_at',
            'sale_person.first_name as sale_person_first_name',
            'sale_person.last_name  as sale_person_last_name',
            'users.type',

        );

        $query = Electrician::query();
        $query->select($columns);
        $query->leftJoin('users', 'users.id', '=', 'electrician.user_id');
        $query->leftJoin('users as sale_person', 'electrician.sale_person_id', '=', 'sale_person.id');
        //$query->where('electrician.type', $request->type);
        $query->whereIn('electrician.type', [301, 302]);
        if ($isAdminOrCompanyAdmin == 1 || $isMarketingDispatcherUser == 1) {
        } else if ($isSalePerson == 1) {
            $query->whereIn('electrician.sale_person_id', $SalePersonsIds);
        } else if ($isChannelPartner != 0) {
            $query->where('electrician.added_by', Auth::user()->id);
        }

        $query->orderBy('electrician.id', 'desc');
        $data = $query->get();

        // if ($request->type == 301) {
        // 	$headers = array("#ID", "Firstname", "Lastname", "Phone", "Status", "Created", "SalePerson");
        // } else {
        $headers = array("#ID", "TYPE", "Firstname", "Lastname", "Phone", "Status", "Created", "SalePerson");
        //}

        header('Content-Type: text/csv');
        // if ($request->type == 301) {
        // 	header('Content-Disposition: attachment; filename="electricians-non-prime.csv"');
        // } else {
        header('Content-Disposition: attachment; filename="electricians.csv"');
        // }
        $fp = fopen('php://output', 'wb');

        fputcsv($fp, $headers);

        foreach ($data as $key => $value) {

            $createdAt = convertDateTime($value->created_at);
            $status = $value->status;
            if ($status == 0) {
                $status = "Inactive";
            } else if ($status == 1) {
                $status = "Active";
            } else if ($status == 2) {
                $status = "Blocked";
            }

            $lineVal = array();

            if ($value->type == 301) {

                $lineVal = array(
                    $value->id,
                    "NON-PRIME",
                    $value->first_name,
                    $value->last_name,
                    $value->dialing_code . " " . $value->phone_number,
                    $status,
                    $createdAt,
                    $value->sale_person_first_name . ' ' . $value->sale_person_last_name,

                );
            } else if ($value->type == 302) {

                $lineVal = array(
                    $value->id,
                    "PRIME",
                    $value->first_name,
                    $value->last_name,
                    $value->dialing_code . " " . $value->phone_number,
                    $status,
                    $createdAt,
                    $value->sale_person_first_name . ' ' . $value->sale_person_last_name,

                );
            }

            fputcsv($fp, $lineVal, ",");
        }

        fclose($fp);
    }

    function pointLog(Request $request)
    {

        $searchColumns = array(
            0 => 'crm_log.description',
        );

        $sortingColumns = array(
            0 => 'crm_log.id',

        );

        $selectColumns = array(
            'crm_log.description',

        );

        $query = CRMLog::query();
        $query->where('for_user_id', $request->user_id);
        $query->whereIn('name', array('point-gain', 'point-redeem'));
        $recordsTotal = $query->count();

        $recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.
        $query = CRMLog::query();
        $query->where('for_user_id', $request->user_id);
        $query->whereIn('name', array('point-gain', 'point-redeem'));
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
                        $query->where($searchColumns[$i], 'like', "%" . $search_value . "%");
                    } else {

                        $query->orWhere($searchColumns[$i], 'like', "%" . $search_value . "%");
                    }
                }
            });
        }

        $data = $query->get();
        $data = json_decode(json_encode($data), true);
        if ($isFilterApply == 1) {
            $recordsFiltered = count($data);
        }

        $viewData = array();

        foreach ($data as $key => $value) {

            $viewData[$key] = array();
            $viewData[$key]['log'] = $value['description'];
        }

        $jsonData = array(
            "draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($recordsTotal), // total number of records
            "recordsFiltered" => intval($recordsFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $viewData, // total data array

        );
        return $jsonData;
    }

    public function inquiryLog(Request $request)
    {

        $inquiryStatus = getInquiryStatus();

        $searchColumns = array(
            'inquiry.id',
            'CONCAT(inquiry.first_name," ",inquiry.last_name)',
            'inquiry.status',
            'inquiry.quotation_amount',
        );

        $sortingColumns = array(
            0 => 'inquiry.id',
            1 => 'inquiry.first_name',
            2 => 'inquiry.status',
            3 => 'inquiry.quotation_amount',

        );

        $selectColumns = array(
            'inquiry.id',
            'inquiry.first_name',
            'inquiry.last_name',
            'inquiry.status',
            'inquiry.quotation_amount',
            'answer_date_time',
            'inquiry.architect',
            'inquiry.source_type',
            'inquiry.source_type_value',

        );

        $userId = $request->user_id;
        $title = "";
        $User = User::find($userId);
        if ($User) {
            $title = $User->first_name . " " . $User->last_name;
            $UserArchitect = Electrician::where('user_id', $User->id)->first();
            if ($UserArchitect) {
                $title = $title . " | Lifetime Point : " . $UserArchitect->total_point . " | Available Point : " . $UserArchitect->total_point_current;
            }
        }

        $query = Inquiry::query();
        $query->where(function ($query2) use ($userId) {

            $query2->where(function ($query3) use ($userId) {

                $query3->whereIn('inquiry.source_type', array("user-301", "user-302"));
                $query3->where('inquiry.source_type_value', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {

                $query3->whereIn('inquiry.source_type_1', array("user-301", "user-302"));
                $query3->where('inquiry.source_type_value_1', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {

                $query3->whereIn('inquiry.source_type_2', array("user-301", "user-302"));
                $query3->where('inquiry.source_type_value_2', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {

                $query3->whereIn('inquiry.source_type_3', array("user-301", "user-302"));
                $query3->where('inquiry.source_type_value_3', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {

                $query3->whereIn('inquiry.source_type_4', array("user-301", "user-302"));
                $query3->where('inquiry.source_type_value_4', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {

                $query3->where('inquiry.electrician', $userId);
            });
        });


        if ($request->type != 0) {

            if ($request->type == 1) {
                $statusArray = isset($inquiryStatus[301]['for_user_ids']) ? $inquiryStatus[301]['for_user_ids'] : array(0);
                $query->whereIn('inquiry.status', $statusArray);
            } else if ($request->type == 2) {
                $statusArray = array(9, 11, 10, 12, 14);
                $query->whereIn('inquiry.status', $statusArray);
            } else if ($request->type == 3) {
                $statusArray = array(101, 102);
                $query->whereIn('inquiry.status', $statusArray);
            }
        }

        $recordsTotal = $query->count();


        $recordsFiltered = $recordsTotal;
        $query = Inquiry::query();
        $query->where(function ($query2) use ($userId) {

            $query2->where(function ($query3) use ($userId) {

                $query3->whereIn('inquiry.source_type', array("user-301", "user-302"));
                $query3->where('inquiry.source_type_value', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {

                $query3->whereIn('inquiry.source_type_1', array("user-301", "user-302"));
                $query3->where('inquiry.source_type_value_1', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {

                $query3->whereIn('inquiry.source_type_2', array("user-301", "user-302"));
                $query3->where('inquiry.source_type_value_2', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {

                $query3->whereIn('inquiry.source_type_3', array("user-301", "user-302"));
                $query3->where('inquiry.source_type_value_3', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {

                $query3->whereIn('inquiry.source_type_4', array("user-301", "user-302"));
                $query3->where('inquiry.source_type_value_4', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {

                $query3->where('inquiry.electrician', $userId);
            });
        });


        if ($request->type != 0) {

            if ($request->type == 1) {
                $statusArray = isset($inquiryStatus[201]['for_user_ids']) ? $inquiryStatus[201]['for_user_ids'] : array(0);
                $query->whereIn('inquiry.status', $statusArray);
            } else if ($request->type == 2) {
                $statusArray = array(9, 11, 10, 12, 14);
                $query->whereIn('inquiry.status', $statusArray);
            } else if ($request->type == 3) {
                $statusArray = array(101, 102);
                $query->whereIn('inquiry.status', $statusArray);
            }
        }

        $recordsTotal = $query->count();
        $quotationTotal = $query->sum('quotation_amount');


        // when there is no search parameter then total number rows = total number filtered rows.
        $query = Inquiry::query();

        $query->where(function ($query2) use ($userId) {

            $query2->where(function ($query3) use ($userId) {

                $query3->whereIn('inquiry.source_type', array("user-301", "user-302"));
                $query3->where('inquiry.source_type_value', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {

                $query3->whereIn('inquiry.source_type_1', array("user-301", "user-302"));
                $query3->where('inquiry.source_type_value_1', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {

                $query3->whereIn('inquiry.source_type_2', array("user-301", "user-302"));
                $query3->where('inquiry.source_type_value_2', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {

                $query3->whereIn('inquiry.source_type_3', array("user-301", "user-302"));
                $query3->where('inquiry.source_type_value_3', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {

                $query3->whereIn('inquiry.source_type_4', array("user-301", "user-302"));
                $query3->where('inquiry.source_type_value_4', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {

                $query3->where('inquiry.electrician', $userId);
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

                        $query->orWhereRaw($searchColumns[$i] . ' like ?', ["%" . $search_value . "%"]);
                    }
                }
            });
        }


        if ($request->type != 0) {

            if ($request->type == 1) {
                $statusArray = isset($inquiryStatus[201]['for_user_ids']) ? $inquiryStatus[201]['for_user_ids'] : array(0);
                $query->whereIn('inquiry.status', $statusArray);
            } else if ($request->type == 2) {
                $statusArray = array(9, 11, 10, 12, 14);
                $query->whereIn('inquiry.status', $statusArray);
            } else if ($request->type == 3) {
                $statusArray = array(101, 102);
                $query->whereIn('inquiry.status', $statusArray);
            }
        }

        $data = $query->get();
        $data = json_decode(json_encode($data), true);
        if ($isFilterApply == 1) {
            $recordsFiltered = count($data);
        }

        $viewData = array();


        foreach ($data as $key => $value) {

            $viewData[$key] = array();
            $viewData[$key]['id'] = $value['id'];
            $viewData[$key]['name'] = '<p class="text-muted mb-0" data-bs-toggle="tooltip"  data-bs-original-title="' . $value['first_name'] . " " . $value['last_name'] . '"><a target="_blank" href="' . route('inquiry') . '?status=0&inquiry_id=' . $value['id'] . '" >' . displayStringLenth($value['first_name'] . " " . $value['last_name'], 20) . '</a></p>';

            $viewData[$key]['status'] = $inquiryStatus[$value['status']]['name'] . " (" . convertDateTime($value['answer_date_time']) . ")";
            $viewData[$key]['quotation_amount'] = $value['quotation_amount'];

            $column4Val = "";
            $column5Val = "";

            if ($value['architect'] != 0) {

                $User4 = User::find($value['architect']);
                if ($User4) {
                    $column4Val = $User4->first_name . " " . $User4->last_name;
                }
            }

            if (in_array($value['source_type'], array("user-101", "user-102", "user-103", "user-104", "user-105")) && $value['source_type_value'] != 0) {

                $User5 = ChannelPartner::where('user_id', $value['source_type_value'])->first();
                if ($User5) {
                    $column5Val = $User5->firm_name;
                }
            }

            $viewData[$key]['column4'] = $column4Val;
            $viewData[$key]['column5'] = $column5Val;
        }


        $overview = array();

        $query = Inquiry::query();
        $query->where(function ($query2) use ($userId) {

            $query2->where(function ($query3) use ($userId) {

                $query3->whereIn('inquiry.source_type', array("user-301", "user-302"));
                $query3->where('inquiry.source_type_value', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {

                $query3->whereIn('inquiry.source_type_1', array("user-301", "user-302"));
                $query3->where('inquiry.source_type_value_1', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {

                $query3->whereIn('inquiry.source_type_2', array("user-301", "user-302"));
                $query3->where('inquiry.source_type_value_2', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {

                $query3->whereIn('inquiry.source_type_3', array("user-301", "user-302"));
                $query3->where('inquiry.source_type_value_3', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {

                $query3->whereIn('inquiry.source_type_4', array("user-301", "user-302"));
                $query3->where('inquiry.source_type_value_4', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {

                $query3->where('inquiry.electrician', $userId);
            });
        });

        $recordsTotal = $query->count();
        $overview['total_inquiry'] = $recordsTotal;

        $query = Inquiry::query();
        $query->where(function ($query2) use ($userId) {

            $query2->where(function ($query3) use ($userId) {

                $query3->whereIn('inquiry.source_type', array("user-301", "user-302"));
                $query3->where('inquiry.source_type_value', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {

                $query3->whereIn('inquiry.source_type_1', array("user-301", "user-302"));
                $query3->where('inquiry.source_type_value_1', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {

                $query3->whereIn('inquiry.source_type_2', array("user-301", "user-302"));
                $query3->where('inquiry.source_type_value_2', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {

                $query3->whereIn('inquiry.source_type_3', array("user-301", "user-302"));
                $query3->where('inquiry.source_type_value_3', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {

                $query3->whereIn('inquiry.source_type_4', array("user-301", "user-302"));
                $query3->where('inquiry.source_type_value_4', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {

                $query3->where('inquiry.electrician', $userId);
            });
        });
        $statusArray = isset($inquiryStatus[201]['for_user_ids']) ? $inquiryStatus[201]['for_user_ids'] : array(0);
        $query->whereIn('inquiry.status', $statusArray);
        $recordsTotal = $query->count();
        $overview['total_running'] = $recordsTotal;


        $query = Inquiry::query();
        $query->where(function ($query2) use ($userId) {

            $query2->where(function ($query3) use ($userId) {

                $query3->whereIn('inquiry.source_type', array("user-301", "user-302"));
                $query3->where('inquiry.source_type_value', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {

                $query3->whereIn('inquiry.source_type_1', array("user-301", "user-302"));
                $query3->where('inquiry.source_type_value_1', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {

                $query3->whereIn('inquiry.source_type_2', array("user-301", "user-302"));
                $query3->where('inquiry.source_type_value_2', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {

                $query3->whereIn('inquiry.source_type_3', array("user-301", "user-302"));
                $query3->where('inquiry.source_type_value_3', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {

                $query3->whereIn('inquiry.source_type_4', array("user-301", "user-302"));
                $query3->where('inquiry.source_type_value_4', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {

                $query3->where('inquiry.electrician', $userId);
            });
        });
        $statusArray = isset($inquiryStatus[9]['for_user_ids']) ? $inquiryStatus[9]['for_user_ids'] : array(0);
        $query->whereIn('inquiry.status', $statusArray);
        $recordsTotal = $query->count();
        $overview['total_won'] = $recordsTotal;


        $query = Inquiry::query();
        $query->where(function ($query2) use ($userId) {

            $query2->where(function ($query3) use ($userId) {

                $query3->whereIn('inquiry.source_type', array("user-301", "user-302"));
                $query3->where('inquiry.source_type_value', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {

                $query3->whereIn('inquiry.source_type_1', array("user-301", "user-302"));
                $query3->where('inquiry.source_type_value_1', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {

                $query3->whereIn('inquiry.source_type_2', array("user-301", "user-302"));
                $query3->where('inquiry.source_type_value_2', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {

                $query3->whereIn('inquiry.source_type_3', array("user-301", "user-302"));
                $query3->where('inquiry.source_type_value_3', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {

                $query3->whereIn('inquiry.source_type_4', array("user-301", "user-302"));
                $query3->where('inquiry.source_type_value_4', $userId);
            });

            $query2->orWhere(function ($query3) use ($userId) {

                $query3->where('inquiry.electrician', $userId);
            });
        });
        $statusArray = array(101, 102);
        $query->whereIn('inquiry.status', $statusArray);
        $recordsTotal = $query->count();
        $overview['total_rejected'] = $recordsTotal;



        $jsonData = array(
            "draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($recordsTotal), // total number of records
            "recordsFiltered" => intval($recordsFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $viewData, // total data array
            "overview" => $overview, // total data array
            "type" => $request->type,
            "quotationAmount" => priceLable($quotationTotal),
            "title" => $title,

        );
        return $jsonData;
    }

    function getList(Request $request)
    {

        $isSalePerson = isSalePerson();
        $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
        if ($isSalePerson == 1) {
            $childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
        }

        $selectColumns = array(
            'users.id',
            'users.first_name',
            'users.last_name',
            'users.email',
            'users.phone_number',
            'electrician.status',
            'electrician.type',
        );

        DB::enableQueryLog();
        $Electrician = Electrician::query();
        $Electrician->leftJoin('users', 'users.id', '=', 'electrician.user_id');
        $Electrician->whereIn('electrician.type', [301, 302]);
        $Electrician->select($selectColumns);
        if (isset($request->search)) {
            if ($request->search != "") {
                $search = $request->search;
                $Electrician->where(function ($query) use ($search) {
                    $query->where('users.id', 'like', '%' . $search . '%');
                    $query->orWhere('users.first_name', 'like', '%' . $search . '%');
                    $query->orWhere('users.last_name', 'like', '%' . $search . '%');
                });
            }
        }

        if (isset($request->status)) {
            if ($request->status != 100) {
                $Electrician->where('electrician.status', $request->status);
            }
        }
        $arr_where_clause = array();
        $arr_or_clause = array();
        $newdatares = array();
        $chk_condtion = "";
        $date_condtion = "";
        $isorderby = 0;

        if ($request->isAdvanceFilter == 1) {
            foreach ($request->AdvanceData as $key => $filt_value) {

                $filter_value = '';
                if ($filt_value['clause'] == null || $filt_value['clause'] == '') {
                    $response = errorRes("Please Select Clause");
                    return response()->json($response)->header('Content-Type', 'application/json');
                    $chk_condtion .= ", 1";
                } else if ($filt_value['column'] == null || $filt_value['column'] == '') {
                    $response = errorRes("Please Select column");
                    return response()->json($response)->header('Content-Type', 'application/json');
                    $chk_condtion .= ", 2";
                } else if ($filt_value['condtion'] == null || $filt_value['condtion'] == '') {
                    $response = errorRes("Please Select condtion");
                    return response()->json($response)->header('Content-Type', 'application/json');
                    $chk_condtion .= ", 3";
                } else {
                    $column = getElectricianFilterColumn()[$filt_value['column']];
                    $condtion = getUserFilterCondtion()[$filt_value['condtion']];
                    if ($column['value_type'] == 'text') {
                        $chk_condtion .= ", 4";
                        if ($filt_value['value_text'] == null || $filt_value['value_text'] == '') {
                            $response = errorRes("Please enter value");
                            return response()->json($response)->header('Content-Type', 'application/json');
                        } else {
                            $chk_condtion .= ", 5";
                            $filter_value = $filt_value['value_text'];
                        }
                    } else if (($column['value_type'] == 'select') && ($condtion['value_type'] == 'single_select')) {
                        if ($filt_value['value_select'] == null || $filt_value['value_select'] == '') {
                            $response = errorRes("Please select value");
                            return response()->json($response)->header('Content-Type', 'application/json');
                        } else {
                            $chk_condtion .= ", 6";
                            $filter_value = $filt_value['value_select'];
                        }
                    } else if (($column['value_type'] == 'select') && ($condtion['value_type'] == 'multi_select')) {
                        $chk_condtion .= ", 7";

                        if (!isset($filt_value['value_multi_select']) && empty($filt_value['value_multi_select'])) {
                            $response = errorRes("Please select value");
                            return response()->json($response)->header('Content-Type', 'application/json');
                        } else {
                            $chk_condtion .= ", 8";
                            $filter_value = $filt_value['value_multi_select'];
                        }
                    } else if (($column['value_type'] == 'date') && ($condtion['value_type'] == 'single_select')) {
                        $chk_condtion .= ", 9";

                        if ($filt_value['value_date'] == null || $filt_value['value_date'] == '') {
                            $response = errorRes("Please enter date");
                            return response()->json($response)->header('Content-Type', 'application/json');
                        } else {
                            $chk_condtion .= ", 10";
                            $filter_value = $filt_value['value_date'];
                        }
                    } else if (($column['value_type'] == 'date') && ($condtion['value_type'] == 'between')) {

                        $chk_condtion .= ", 11";
                        if (($filt_value['value_from_date'] == null || $filt_value['value_from_date'] == '') && ($filt_value['value_to_date'] == null || $filt_value['value_to_date'] == '')) {
                            $response = errorRes("Please enter from to date");
                            return response()->json($response)->header('Content-Type', 'application/json');
                        } else {
                            $chk_condtion .= ", 12";
                            $filter_value = $filt_value['value_from_date'] . "," . $filt_value['value_to_date'];
                        }
                    } else if (($column['value_type'] == 'select_order_by') && ($condtion['value_type'] == 'single_select')) {

                        $isorderby = 1;
                        $chk_condtion .= ", 12";
                        if (($filt_value['value_select'] == null || $filt_value['value_select'] == '')) {
                            $response = errorRes("Please enter from to date");
                            return response()->json($response)->header('Content-Type', 'application/json');
                        } else {
                            $chk_condtion .= ", 12";
                            $filter_value = $filt_value['value_select'];
                        }
                    }

                    if ($filt_value['clause'] != 0) {
                        $chk_condtion .= ", 13";
                        $clause = getUserFilterClause()[$filt_value['clause']];
                    }


                    if ($filt_value['clause'] == 0) {
                        $newdata['clause'] = 0;
                        $newdata['column'] = $column['id'];
                        $newdata['condtion'] = $condtion['id'];
                        $newdata['value'] = $filter_value;

                        $newdatares = $newdata;

                        array_push($arr_where_clause, $newdata);
                        $chk_condtion .= ", 14";
                    } else if ($clause['clause'] == 'where') {
                        $chk_condtion .= ", 15";
                        $newdata['clause'] = $clause['id'];
                        $newdata['column'] = $column['id'];
                        $newdata['condtion'] = $condtion['id'];
                        $newdata['value'] = $filter_value;

                        array_push($arr_where_clause, $newdata);
                        $chk_condtion .= ", 16";
                    } else if ($clause['clause'] == 'orwhere') {
                        $newdata['clause'] = $clause['id'];
                        $newdata['column'] = $column['id'];
                        $newdata['condtion'] = $condtion['id'];
                        $newdata['value'] = $filter_value;

                        array_push($arr_or_clause, $newdata);
                    }
                }
            }

            foreach ($arr_where_clause as $wherekey => $objwhere) {
                $Column = getElectricianFilterColumn()[$objwhere['column']];
                $Condtion = getUserFilterCondtion()[$objwhere['condtion']];
                $lstDateFilter = getUserDateFilterValue();
                $Filter_Value = $objwhere['value'];
                if ($Condtion['code'] == 'is') {
                    if ($Column['value_type'] == 'date') {
                        $objDateFilter = $lstDateFilter[$Filter_Value];

                        $currentStartDate = date('Y-m-d', strtotime(date('Y-m-d')));
                        $currentEndDate = date('Y-m-d', strtotime(date('Y-m-d')));

                        if ($objDateFilter['code'] == "all_closing") {
                            $Electrician->where($Column['column_name'], '!=', null);
                        } else if ($objDateFilter['code'] == "in_this_week") {

                            $currentWeekDay = date('w', strtotime($currentStartDate));
                            $weekStartDate = date('Y-m-d', strtotime($currentStartDate . " -" . ($currentWeekDay - 1) . " days"));
                            $weekEndDate = date('Y-m-d', strtotime($currentEndDate . " +" . ((7 - $currentWeekDay)) . " days"));
                            $Electrician->whereDate($Column['column_name'], '<=', $weekEndDate);
                        } else if ($objDateFilter['code'] == "in_this_month") {

                            $monthStartDay = date('Y-m-01', strtotime($currentStartDate));
                            $monthEndDay = date('Y-m-t', strtotime($currentEndDate));
                            $Electrician->whereDate($Column['column_name'], '<=', $monthEndDay);
                        } else if ($objDateFilter['code'] == "in_next_month") {
                            $currentStartDate = date('Y-m-d', strtotime($currentStartDate . " +5 hours"));
                            $currentStartDate = date('Y-m-d', strtotime($currentStartDate . " +30 minutes"));
                            $monthStartDay = date('Y-m-01', strtotime($currentStartDate . " + 1 month"));
                            $monthEndDay = date('Y-m-t', strtotime($monthStartDay));
                            $Electrician->whereDate($Column['column_name'], '<=', $monthEndDay);
                        } else if ($objDateFilter['code'] == "in_next_two_month") {

                            $monthStartDay = date('Y-m-01', strtotime($currentStartDate . " + 1 month"));
                            $monthEndDay = date('Y-m-t', strtotime($currentEndDate . " + 2 month"));
                            $Electrician->whereDate($Column['column_name'], '<=', $monthEndDay);
                        } else if ($objDateFilter['code'] == "in_next_three_month") {

                            $monthStartDay = date('Y-m-01', strtotime($currentStartDate . " + 1 month"));
                            $monthEndDay = date('Y-m-t', strtotime($currentEndDate . " + 3 month"));
                            $Electrician->whereDate($Column['column_name'], '>=', date('Y-m-d'));
                            $Electrician->whereDate($Column['column_name'], '<=', $monthEndDay);
                        } else {

                            $date_filter_value = explode(",", $objDateFilter['value']);
                            $from_date_filter = $date_filter_value[0];
                            $from_date_filter_value = date('Y-m-d', strtotime($from_date_filter));

                            $to_date_filter = $date_filter_value[1];
                            $to_date_filter_value = date('Y-m-d', strtotime($to_date_filter));

                            $Electrician->whereBetween(DB::raw('DATE(' . $Column["column_name"] . ')'), [$from_date_filter_value, $to_date_filter_value]);
                        }
                    } else if ($Column['value_type'] == 'select') {
                        $Electrician->where($Column['column_name'], $Filter_Value);
                    } else if ($Column['value_type'] == 'select_order_by') {
                        if ($Filter_Value == 1) {
                            $Electrician->orderBy($Column['column_name'], 'DESC');
                        } else {
                            $Electrician->orderBy($Column['column_name'], 'ASC');
                        }
                    } else {
                        $Electrician->where($Column['column_name'], 'like', "%" . $Filter_Value . "%");
                    }
                } elseif ($Condtion['code'] == 'is_not') {
                    if ($Column['value_type'] == 'date') {

                        $objDateFilter = $lstDateFilter[$Filter_Value];

                        $currentStartDate = date('Y-m-d', strtotime(date('Y-m-d')));
                        $currentEndDate = date('Y-m-d', strtotime(date('Y-m-d')));

                        if ($objDateFilter['code'] == "all_closing") {

                            $Electrician->where($Column['column_name'], '!=', null);
                        } else if ($objDateFilter['code'] == "in_this_week") {

                            $currentWeekDay = date('w', strtotime($currentStartDate));
                            $weekStartDate = date('Y-m-d', strtotime($currentStartDate . " -" . ($currentWeekDay - 1) . " days"));
                            $weekEndDate = date('Y-m-d', strtotime($currentEndDate . " +" . ((7 - $currentWeekDay)) . " days"));
                            $Electrician->whereDate($Column['column_name'], '<=', $weekEndDate);
                        } else if ($objDateFilter['code'] == "in_this_month") {

                            $monthStartDay = date('Y-m-01', strtotime($currentStartDate));
                            $monthEndDay = date('Y-m-t', strtotime($currentEndDate));
                            $Electrician->whereDate($Column['column_name'], '<=', $monthEndDay);
                        } else if ($objDateFilter['code'] == "in_next_month") {
                            $currentStartDate = date('Y-m-d', strtotime($currentStartDate . " +5 hours"));
                            $currentStartDate = date('Y-m-d', strtotime($currentStartDate . " +30 minutes"));
                            $monthStartDay = date('Y-m-01', strtotime($currentStartDate . " + 1 month"));
                            $monthEndDay = date('Y-m-t', strtotime($monthStartDay));
                            $Electrician->whereDate($Column['column_name'], '<=', $monthEndDay);
                        } else if ($objDateFilter['code'] == "in_next_two_month") {

                            $monthStartDay = date('Y-m-01', strtotime($currentStartDate . " + 1 month"));
                            $monthEndDay = date('Y-m-t', strtotime($currentEndDate . " + 2 month"));
                            $Electrician->whereDate($Column['column_name'], '<=', $monthEndDay);
                        } else if ($objDateFilter['code'] == "in_next_three_month") {

                            $monthStartDay = date('Y-m-01', strtotime($currentStartDate . " + 1 month"));
                            $monthEndDay = date('Y-m-t', strtotime($currentEndDate . " + 3 month"));
                            $Electrician->whereDate($Column['column_name'], '>=', date('Y-m-d'));
                            $Electrician->whereDate($Column['column_name'], '<=', $monthEndDay);
                        } else {

                            $date_filter_value = explode(",", $objDateFilter['value']);
                            $from_date_filter = $date_filter_value[0];
                            $from_date_filter_value = date('Y-m-d', strtotime($from_date_filter));

                            $to_date_filter = $date_filter_value[1];
                            $to_date_filter_value = date('Y-m-d', strtotime($to_date_filter));

                            $Electrician->whereNotBetween(DB::raw('DATE(' . $Column["column_name"] . ')'), [$from_date_filter_value, $to_date_filter_value]);
                        }
                    } else if ($Column['value_type'] == 'select') {

                        $Electrician->where($Column['column_name'], '!=', $Filter_Value);
                    } else {
                        $Electrician->where($Column['column_name'], 'not like', "%" . $Filter_Value . "%");
                    }
                } else if ($Condtion['code'] == "contains") {
                    if ($Column['value_type'] == 'select') {
                        $Electrician->whereIn($Column['column_name'], $Filter_Value);
                    } else {
                        $Filter_Value = explode(",", $Filter_Value);
                        $Electrician->whereIn($Column['column_name'], $Filter_Value);
                    }
                } else if ($Condtion['code'] == "not_contains") {

                    if ($Column['value_type'] == 'select') {
                        $Electrician->whereNotIn($Column['column_name'], $Filter_Value);
                    } else {
                        $Filter_Value = explode(",", $Filter_Value);
                        $Electrician->whereNotIn($Column['column_name'], $Filter_Value);
                    }
                } else if ($Condtion['code'] == "between") {

                    if ($Column['value_type'] == 'date') {
                        $date_filter_value = explode(",", $Filter_Value);
                        $from_date_filter = $date_filter_value[0];
                        $from_date_filter_value = date('Y-m-d', strtotime($from_date_filter));

                        $to_date_filter = $date_filter_value[1];
                        $to_date_filter_value = date('Y-m-d', strtotime($to_date_filter));

                        $Electrician->whereBetween(DB::raw('DATE(' . $Column["column_name"] . ')'), [$from_date_filter_value, $to_date_filter_value]);
                    }
                }
            }

            if (count($arr_or_clause) > 0) {
                $Electrician->orWhere(function ($query) use ($arr_or_clause) {
                    foreach ($arr_or_clause as $orkey => $objor) {

                        $Column = getElectricianFilterColumn()[$objor['column']];
                        $Condtion = getUserFilterCondtion()[$objor['condtion']];
                        $lstDateFilter = getUserDateFilterValue();
                        $Filter_Value = $objor['value'];

                        if ($Condtion['code'] == 'is') {
                            if ($Column['value_type'] == 'date') {

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

                                $query->orWhere($Column['column_name'], $Filter_Value);
                            } else if ($Column['value_type'] == 'select_order_by') {
                                if ($Filter_Value == 1) {
                                    $query->orderBy($Column['column_name'], 'DESC');
                                } else {
                                    $query->orderBy($Column['column_name'], 'ASC');
                                }
                            } else {
                                $query->orWhere($Column['column_name'], 'like', "%" . $Filter_Value . "%");
                            }
                        } elseif ($Condtion['code'] == 'is_not') {
                            if ($Column['value_type'] == 'date') {

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

                                $query->orWhere($Column['column_name'], '!=', $Filter_Value);
                            } else {
                                $query->orWhere($Column['column_name'], 'not like', "%" . $Filter_Value . "%");
                            }
                        } else if ($Condtion['code'] == "contains") {
                            if ($Column['value_type'] == 'select') {
                                if ($Column['code'] == 'leads_source') {
                                } else {
                                    $query->orWhereIn($Column['column_name'], $Filter_Value);
                                }
                            } else {
                                $Filter_Value = explode(",", $Filter_Value);
                                $query->orWhereIn($Column['column_name'], $Filter_Value);
                            }
                        } else if ($Condtion['code'] == "not_contains") {
                            if ($Column['value_type'] == 'select') {
                                $query->orWhereNotIn($Column['column_name'], $Filter_Value);
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

        if ($isorderby == 0) {
            $Electrician->orderBy('electrician.id', 'DESC');
        }
        $Electrician = $Electrician->get();
        $query = DB::getQueryLog();
        $query = end($query);
        $Electrician = json_encode($Electrician);
        $Electrician = json_decode($Electrician, true);
        $lastPageArchitectId = 0;
        $FirstPageArchitectId = 0;
        $ArchitectR = array_reverse($Electrician);
        if (count($ArchitectR) > 0) {
            $FirstPageArchitectId = $Electrician[0]['id'];
            $lastPageArchitectId = $ArchitectR[0]['id'];
        }

        $data = array();
        $data['electrician'] = $Electrician;
        $response = successRes("Get List");
        $response['view'] = view('electricians_new/comman/list', compact('data'))->render();
        $response['lastPageArchitectId'] = $lastPageArchitectId;
        $response['FirstPageArchitectId'] = $FirstPageArchitectId;

        $response['count'] = count($Electrician);
        $response['data'] = $data;
        $response['data12'] = $query;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function getListAjax(Request $request)
    {

        $isSalePerson = isSalePerson();
        $isChannelPartner = isChannelPartner(Auth::user()->type);
        $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
        $isMarketingDispatcherUser = isMarketingDispatcherUser();
        if ($isSalePerson == 1) {
            $SalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
        }

        $isTaleSalesUser = isTaleSalesUser();
        if ($isTaleSalesUser == 1) {
            $TeleSalesCity = TeleSalesCity(Auth::user()->id);
        }

        $viewMode = (isset($request->view_mode) && $request->view_mode == 1) ? 1 : 0;

        $searchColumns = array(
            'users.id',
            'users.email',
            'users.phone_number',
            'CONCAT(users.first_name," ",users.last_name)',
            'CONCAT(sale_person.first_name," ",sale_person.last_name)',

        );

        if ($request->type == 302) {

            $sortingColumns = array(
                0 => 'users.id',
                1 => 'users.first_name',
                2 => 'users.phone_number',
                3 => 'electrician.sale_person_id',
                4 => 'electrician.total_point_current',
                5 => 'electrician.total_point',
                6 => 'users.status',
                7 => 'users.created_by',

            );
        } else {

            $sortingColumns = array(
                0 => 'users.id',
                1 => 'users.first_name',
                2 => 'users.phone_number',
                3 => 'electrician.sale_person_id',
                4 => 'users.status',
                5 => 'users.created_by',

            );
        }

        $selectColumns = array(
            'users.id',
            'users.type',
            'users.first_name',
            'users.last_name',
            'users.email',
            'users.phone_number',
            'electrician.sale_person_id',
            'users.status',
            'users.created_at',
            'electrician.joining_date',
            'electrician.total_point_current',
            'electrician.total_point',
            'created_by_user.first_name as created_by_user_first_name',
            'created_by_user.last_name as created_by_user_last_name',
        );

        $query = Electrician::query();
        $query->leftJoin('users', 'users.id', '=', 'electrician.user_id');
        $query->whereIn('electrician.type', [301, 302]);
        if (isset($request->status)) {
            if ($request->status != "" && $request->status != "all" && $request->status != 100) {
                $query->where('electrician.status', $request->status);
            } else {
                $query->whereIn('electrician.status', array(0, 1, 2, 3, 4));
            }
        }

        if (isset($request->search_user_id) && $request->search_user_id != "") {
            $query->where('users.id', $request->search_user_id);
        }

        
        //  else if ($isTaleSalesUser == 1) {
        //     $query->whereIn('users.city_id', $TeleSalesCity);
        // }


        $arr_where_clause = [];
        $arr_or_clause = [];
        $newdatares = [];
        $chk_condtion = '';
        $date_condtion = '';
        $sortingColumns = array();
        $isorderby = 0;


        if ($request->isAdvanceFilter == 1) {
            foreach (json_decode($request->AdvanceData, true) as $key => $filt_value) {

                $filter_value = '';
                if ($filt_value['clause'] == null || $filt_value['clause'] == '') {
                    $response = errorRes("Please Select Clause");
                    return response()->json($response)->header('Content-Type', 'application/json');
                    $chk_condtion .= ", 1";
                } else if ($filt_value['column'] == null || $filt_value['column'] == '') {
                    $response = errorRes("Please Select column");
                    return response()->json($response)->header('Content-Type', 'application/json');
                    $chk_condtion .= ", 2";
                } else if ($filt_value['condtion'] == null || $filt_value['condtion'] == '') {
                    $response = errorRes("Please Select condtion");
                    return response()->json($response)->header('Content-Type', 'application/json');
                    $chk_condtion .= ", 3";
                } else {
                    $column = getElectricianFilterColumn()[$filt_value['column']];
                    $condtion = getUserFilterCondtion()[$filt_value['condtion']];
                    if ($column['value_type'] == 'text') {
                        $chk_condtion .= ", 4";
                        if ($filt_value['value_text'] == null || $filt_value['value_text'] == '') {
                            $response = errorRes("Please enter value");
                            return response()->json($response)->header('Content-Type', 'application/json');
                        } else {
                            $chk_condtion .= ", 5";
                            $filter_value = $filt_value['value_text'];
                        }
                    } else if (($column['value_type'] == 'select') && ($condtion['value_type'] == 'single_select')) {
                        if ($filt_value['value_select'] == null || $filt_value['value_select'] == '') {
                            $response = errorRes("Please select value");
                            return response()->json($response)->header('Content-Type', 'application/json');
                        } else {
                            $chk_condtion .= ", 6";
                            $filter_value = $filt_value['value_select'];
                        }
                    } else if (($column['value_type'] == 'select') && ($condtion['value_type'] == 'multi_select')) {
                        $chk_condtion .= ", 7";

                        if (!isset($filt_value['value_multi_select']) && empty($filt_value['value_multi_select'])) {
                            $response = errorRes("Please select value");
                            return response()->json($response)->header('Content-Type', 'application/json');
                        } else {
                            $chk_condtion .= ", 8";
                            $filter_value = $filt_value['value_multi_select'];
                        }
                    } else if (($column['value_type'] == 'date') && ($condtion['value_type'] == 'single_select')) {
                        $chk_condtion .= ", 9";

                        if ($filt_value['value_date'] == null || $filt_value['value_date'] == '') {
                            $response = errorRes("Please enter date");
                            return response()->json($response)->header('Content-Type', 'application/json');
                        } else {
                            $chk_condtion .= ", 10";
                            $filter_value = $filt_value['value_date'];
                        }
                    } else if (($column['value_type'] == 'date') && ($condtion['value_type'] == 'between')) {

                        $chk_condtion .= ", 11";
                        if (($filt_value['value_from_date'] == null || $filt_value['value_from_date'] == '') && ($filt_value['value_to_date'] == null || $filt_value['value_to_date'] == '')) {
                            $response = errorRes("Please enter from to date");
                            return response()->json($response)->header('Content-Type', 'application/json');
                        } else {
                            $chk_condtion .= ", 12";
                            $filter_value = $filt_value['value_from_date'] . "," . $filt_value['value_to_date'];
                        }
                    } else if (($column['value_type'] == 'select_order_by') && ($condtion['value_type'] == 'single_select')) {

                        $isorderby = 1;
                        $chk_condtion .= ", 12";
                        if (($filt_value['value_select'] == null || $filt_value['value_select'] == '')) {
                            $response = errorRes("Please enter from to date");
                            return response()->json($response)->header('Content-Type', 'application/json');
                        } else {
                            $chk_condtion .= ", 12";
                            $filter_value = $filt_value['value_select'];
                        }
                    }

                    if ($filt_value['clause'] != 0) {
                        $chk_condtion .= ", 13";
                        $clause = getUserFilterClause()[$filt_value['clause']];
                    }


                    if ($filt_value['clause'] == 0) {
                        $newdata['clause'] = 0;
                        $newdata['column'] = $column['id'];
                        $newdata['condtion'] = $condtion['id'];
                        $newdata['value'] = $filter_value;

                        $newdatares = $newdata;

                        array_push($arr_where_clause, $newdata);
                        $chk_condtion .= ", 14";
                    } else if ($clause['clause'] == 'where') {
                        $chk_condtion .= ", 15";
                        $newdata['clause'] = $clause['id'];
                        $newdata['column'] = $column['id'];
                        $newdata['condtion'] = $condtion['id'];
                        $newdata['value'] = $filter_value;

                        array_push($arr_where_clause, $newdata);
                        $chk_condtion .= ", 16";
                    } else if ($clause['clause'] == 'orwhere') {
                        $newdata['clause'] = $clause['id'];
                        $newdata['column'] = $column['id'];
                        $newdata['condtion'] = $condtion['id'];
                        $newdata['value'] = $filter_value;

                        array_push($arr_or_clause, $newdata);
                    }
                }
            }

            foreach ($arr_where_clause as $wherekey => $objwhere) {
                $Column = getElectricianFilterColumn()[$objwhere['column']];
                $Condtion = getUserFilterCondtion()[$objwhere['condtion']];
                $lstDateFilter = getUserDateFilterValue();
                $Filter_Value = $objwhere['value'];
                if ($Condtion['code'] == 'is') {
                    if ($Column['value_type'] == 'date') {
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
                        } else {

                            $date_filter_value = explode(",", $objDateFilter['value']);
                            $from_date_filter = $date_filter_value[0];
                            $from_date_filter_value = date('Y-m-d', strtotime($from_date_filter));

                            $to_date_filter = $date_filter_value[1];
                            $to_date_filter_value = date('Y-m-d', strtotime($to_date_filter));

                            $query->whereBetween(DB::raw('DATE(' . $Column["column_name"] . ')'), [$from_date_filter_value, $to_date_filter_value]);
                        }
                    } else if ($Column['value_type'] == 'select') {
                        $query->where($Column['column_name'], $Filter_Value);
                    } else if ($Column['value_type'] == 'select_order_by') {
                        $newSortingColumns['column'] = $Column['column_name'];
                        if ($Filter_Value == 1) {
                           $newSortingColumns['sort'] = 'DESC';
                        } else {
                           $newSortingColumns['sort'] = 'ASC';
                        }
                        array_push($sortingColumns,$newSortingColumns);

                    } else {
                        $query->where($Column['column_name'], 'like', "%" . $Filter_Value . "%");
                    }
                } elseif ($Condtion['code'] == 'is_not') {
                    if ($Column['value_type'] == 'date') {

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
                        } else {

                            $date_filter_value = explode(",", $objDateFilter['value']);
                            $from_date_filter = $date_filter_value[0];
                            $from_date_filter_value = date('Y-m-d', strtotime($from_date_filter));

                            $to_date_filter = $date_filter_value[1];
                            $to_date_filter_value = date('Y-m-d', strtotime($to_date_filter));

                            $query->whereNotBetween(DB::raw('DATE(' . $Column["column_name"] . ')'), [$from_date_filter_value, $to_date_filter_value]);
                        }
                    } else if ($Column['value_type'] == 'select') {

                        $query->where($Column['column_name'], '!=', $Filter_Value);
                    } else {
                        $query->where($Column['column_name'], 'not like', "%" . $Filter_Value . "%");
                    }
                } else if ($Condtion['code'] == "contains") {
                    if ($Column['value_type'] == 'select') {
                        $query->whereIn($Column['column_name'], $Filter_Value);
                    } else {
                        $Filter_Value = explode(",", $Filter_Value);
                        $query->whereIn($Column['column_name'], $Filter_Value);
                    }
                } else if ($Condtion['code'] == "not_contains") {

                    if ($Column['value_type'] == 'select') {
                        $query->whereNotIn($Column['column_name'], $Filter_Value);
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

                        $Column = getElectricianFilterColumn()[$objor['column']];
                        $Condtion = getUserFilterCondtion()[$objor['condtion']];
                        $lstDateFilter = getUserDateFilterValue();
                        $Filter_Value = $objor['value'];

                        if ($Condtion['code'] == 'is') {
                            if ($Column['value_type'] == 'date') {

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

                                $query->orWhere($Column['column_name'], $Filter_Value);
                            } else if ($Column['value_type'] == 'select_order_by') {
                                $newSortingColumns['column'] = $Column['column_name'];
                                if ($Filter_Value == 1) {
                                   $newSortingColumns['sort'] = 'DESC';
                                } else {
                                   $newSortingColumns['sort'] = 'ASC';
                                }
                                array_push($sortingColumns,$newSortingColumns);

                            } else {
                                $query->orWhere($Column['column_name'], 'like', "%" . $Filter_Value . "%");
                            }
                        } elseif ($Condtion['code'] == 'is_not') {
                            if ($Column['value_type'] == 'date') {

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

                                $query->orWhere($Column['column_name'], '!=', $Filter_Value);
                            } else {
                                $query->orWhere($Column['column_name'], 'not like', "%" . $Filter_Value . "%");
                            }
                        } else if ($Condtion['code'] == "contains") {
                            if ($Column['value_type'] == 'select') {
                                if ($Column['code'] == 'leads_source') {
                                } else {
                                    $query->orWhereIn($Column['column_name'], $Filter_Value);
                                }
                            } else {
                                $Filter_Value = explode(",", $Filter_Value);
                                $query->orWhereIn($Column['column_name'], $Filter_Value);
                            }
                        } else if ($Condtion['code'] == "not_contains") {
                            if ($Column['value_type'] == 'select') {
                                $query->orWhereNotIn($Column['column_name'], $Filter_Value);
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

        if ($isAdminOrCompanyAdmin == 1 || $isMarketingDispatcherUser == 1) {
        } else if ($isSalePerson == 1) {
            $query->whereIn('electrician.sale_person_id', $SalePersonsIds);
        } else if ($isChannelPartner != 0) {
            $query->where('electrician.added_by', Auth::user()->id);
        }

        $Filter_lead_ids = $query->distinct()->pluck('users.id');

        $recordsTotal = $Filter_lead_ids->count();
        $recordsFiltered = $recordsTotal;
        
        // RECORDSFILTERED START
        $query = Electrician::query();
        $query->select('electrician.id');
        $query->leftJoin('users', 'users.id', '=', 'electrician.user_id');
        $query->leftJoin('users as sale_person', 'electrician.sale_person_id', '=', 'sale_person.id');
        $query->whereIn('users.id', $Filter_lead_ids);

        $isFilterApply = 0;
        $search_value = '';
        if (isset($request['search']['value'])) {
            $isFilterApply = 1;
            $search_value = $request['search']['value'];
            $query->where(function ($query) use ($search_value, $searchColumns) {

                for ($i = 0; $i < count($searchColumns); $i++) {

                    if ($i == 0) {
                        $query->whereRaw($searchColumns[$i] . ' like ? ', [$search_value]);
                        $query->orWhereRaw($searchColumns[$i] . ' like ? ', ["%" . $search_value . "%"]);
                    } else {

                        $query->orWhereRaw($searchColumns[$i] . ' like ? ', [$search_value]);
                        $query->orWhereRaw($searchColumns[$i] . ' like ? ', ["%" . $search_value . "%"]);
                    }
                }
            });
        }
        $recordsFiltered = $query->count(); // when there is no search parameter then total number rows = total number filtered rows.


        $query = Electrician::query();
        $query->select($selectColumns);
        $query->leftJoin('users', 'users.id', '=', 'electrician.user_id');
        $query->leftJoin('users as created_by_user', 'users.created_by', '=', 'created_by_user.id');
        $query->leftJoin('users as sale_person', 'electrician.sale_person_id', '=', 'sale_person.id');
        $query->whereIn('users.id', $Filter_lead_ids);
        $query->limit($request->length);
        $query->offset($request->start);

        $isFilterApply = 0;

        if (isset($request['search']['value'])) {
            $isFilterApply = 1;
            $search_value = $request['search']['value'];
            $query->where(function ($query) use ($search_value, $searchColumns) {

                for ($i = 0; $i < count($searchColumns); $i++) {

                    if ($i == 0) {
                        $query->whereRaw($searchColumns[$i] . ' like ? ', [$search_value]);
                        $query->orWhereRaw($searchColumns[$i] . ' like ? ', ["%" . $search_value . "%"]);
                    } else {

                        $query->orWhereRaw($searchColumns[$i] . ' like ? ', [$search_value]);
                        $query->orWhereRaw($searchColumns[$i] . ' like ? ', ["%" . $search_value . "%"]);
                    }
                }
            });
        }

        if ($isorderby == 0) {
            $query->orderBy('users.id', 'DESC');
        }else {
           foreach ($sortingColumns as $key => $value) {
              $query->orderBy($value['column'], $value['sort']);
           }
        }
         

        $data = $query->get();

        if ($data->count() >= 1) {
            $FirstPageArchitectId = $data[0]['id'];
        } else {
            $FirstPageArchitectId = 0;
        }
        
        $data = json_decode(json_encode($data), true);

        // $data = json_decode(json_encode($Architect_data), true);

        $viewData = array();
        $ArchitectStatus = getElectricianStatus();
        foreach ($data as $key => $value) {
            $view = "";
            $view .= '<li class="lead_li" id="lead_' . $value['id'] . '" onclick="getDataDetail(' . $value['id'] . ')" style="list-style:none">';
            $view .= '<a href="javascript: void(0);">';
            $view .= '<div class="d-flex">';
            $view .= '<div class="flex-grow-1 overflow-hidden">';
            if ($value['type'] == 301) {
                $view .= '<h5 class="text-truncate font-size-14 mb-1">#' . highlightString($value['id'],$search_value) . ' -  Non Prime</h5>';
            } else if ($value['type'] == 302) {
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

            $viewData[$key] = array();
            $viewData[$key]['view'] = $view;
        }



        $jsonData = array(
            "draw" => intval($request['draw']),
            // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($recordsTotal),
            // total number of records
            "recordsFiltered" => intval($recordsFiltered),
            // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $viewData,
            // total data array
            "dataed" => $data,
            // total data array
            "FirstPageLeadId" => $FirstPageArchitectId,

        );
        return $jsonData;
    }

    function getDetail(Request $request)
    {

        $selectColumns = array(
            'users.*',
            'electrician.sale_person_id',
            'electrician.total_point_current',
            'electrician.total_point',
            'sales.first_name',
            'city_list.name as city_name',
            'state_list.name as state_name',
            'country_list.name as country_name',
            'electrician.status as electrician_status'
        );
        $Electrician = User::select($selectColumns);
        $Electrician->selectRaw('CONCAT(users.first_name," ",users.last_name) AS account_name');
        $Electrician->selectRaw('CONCAT(sales.first_name," ",sales.last_name) AS account_owner');
        $Electrician->selectRaw('CONCAT(created.first_name," ",created.last_name) AS created_by');
        $Electrician->selectRaw('CONCAT(updated.first_name," ",updated.last_name) AS updated_by');
        $Electrician->leftJoin('electrician', 'electrician.user_id', '=', 'users.id');
        $Electrician->leftJoin('users as sales', 'sales.id', '=', 'electrician.sale_person_id');
        $Electrician->leftJoin('users as created', 'created.id', '=', 'electrician.added_by');
        $Electrician->leftJoin('users as updated', 'created.id', '=', 'electrician.added_by');
        $Electrician->leftJoin('city_list', 'city_list.id', '=', 'users.city_id');
        $Electrician->leftJoin('state_list', 'state_list.id', '=', 'city_list.state_id');
        $Electrician->leftJoin('country_list', 'country_list.id', '=', 'city_list.country_id');
        $Electrician->where('user_id', $request->id);
        $Electrician = $Electrician->first();

        $User  = User::find($Electrician->duplicate_from);
        $data = array();

        if ($Electrician) {

            $Electrician = json_encode($Electrician);
            $Electrician = json_decode($Electrician, true);

            $data['user'] = $Electrician;


            if ($Electrician['tag'] != 0) {
                $CRMLeadDealTag = DB::table('tag_master');
                $CRMLeadDealTag->select('tag_master.id AS id', 'tag_master.tagname AS text');
                $CRMLeadDealTag->whereIn('tag_master.id', explode(',', $Electrician['tag']));
                $data['user']['tag'] = $CRMLeadDealTag->get();
            } else {
                $data['user']['tag'] = "";
            }

            $bonusPoint = CRMLog::query();
            $bonusPoint->select('crm_log.points');
            $bonusPoint->leftJoin('users', 'users.id', '=', 'crm_log.user_id');
            $bonusPoint->where('crm_log.for_user_id', $Electrician['id']);
            $bonusPoint->where('crm_log.description','LIKE', '%joining bonus%');
            $bonusPoint->whereIn('crm_log.name', ['point-gain']);
            $bonusPoint = $bonusPoint->first();
            $newLifetimePoint = $bonusPoint ? ($data['user']['total_point'] - $bonusPoint->points)." + ".$bonusPoint->points : $data['user']['total_point'];
            
            $data['user']['lifetime_point_lable'] = $newLifetimePoint;

            $data['user']['lifetime_point'] = $Electrician['total_point'];
            $data['user']['redeemed_point'] = $Electrician['total_point'] - $Electrician['total_point_current'];
            $data['user']['available_point'] = $Electrician['total_point_current'];

            $created_at = date('d/m/Y g:i A', strtotime($Electrician['created_at']));
            $updated_at = date('d/m/Y g:i A', strtotime($Electrician['updated_at']));
            if ($User) {
                $data['user']['duplicate_from'] = $User;
            } else {
                $data['user']['duplicate_from'] = "";
            }

            // $data['user'] = $Electrician;

            

            $data['user']['user_type_lable'] = '';
            if ($Electrician['type'] == 301) {
                $data['user']['user_type_lable'] = '<span class="badge rounded-pill bg-success ms-2" style="font-size: 12px;">NON-PRIME</span>';
            } elseif ($Electrician['type'] == 302) {
                $data['user']['user_type_lable'] = '<span class="badge rounded-pill bg-success ms-2" style="font-size: 12px;">PRIME</span>';
            }

            $data['user']['sale_person'] = array();
            if ($Electrician['sale_person_id'] != null) {
                $data['user']['sale_person']['id'] = $Electrician['sale_person_id'];
                $data['user']['sale_person']['text'] = $Electrician['account_owner'];
            } else {
                $data['user']['sale_person']['id'] = "";
                $data['user']['sale_person']['text'] = "";
            }
            
            $data['user']['user_status_lable'] = '<span class="badge badge-pill badge badge-soft-info font-size-14" style="height: fit-content;">' . getElectricianStatus()[$Electrician['electrician_status']]['header_code'] . '</span>';

            $data['user']['created_at1'] = $created_at;
            $data['user']['updated_at1'] = $updated_at;
            $data['is_architect'] = 0;
            $data['is_electrician'] = 1;

            $data['contacts'] = getUserContactList($Electrician['id'])['data'];
            $data['updates'] = getUserNoteList($Electrician['id'])['data'];
            $data['files'] = getUserFileList($Electrician['id'])['data'];
            // $data['timeline'] = getUserTimelineList($Electrician['id'])['data'];

            $data['calls'] = getUserAllOpenList($Electrician['id'])['call_data'];
            $data['meetings'] = getUserAllOpenList($Electrician['id'])['meeting_data'];
            $data['tasks'] = getUserAllOpenList($Electrician['id'])['task_data'];
            $data['max_open_actions'] = getUserAllOpenList($Electrician['id'])['max_open_actions'];

            $data['calls_closed'] = getUserAllCloseList($Electrician['id'])['close_call_data'];
            $data['meetings_closed'] = getUserAllCloseList($Electrician['id'])['close_meeting_data'];
            $data['tasks_closed'] = getUserAllCloseList($Electrician['id'])['close_task_data'];
            $data['max_close_actions'] = getUserAllCloseList($Electrician['id'])['max_close_actions'];

            // $data['timeline'] = getUserTimelineList($data['user']['id'])['data'];

            $data['point_log'] = getUserPointLogList($data['user']['id'])['view'];
            $data['user_log'] = getUserLogList($data['user']['id'])['view'];

            $response = successRes("Get List");
            $response['view'] = view('electricians_new/detail', compact('data'))->render();
            $response['data'] = $data;
        } else {
            $response = errorRes("Lead Data Not Available");
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function searchStatus(Request $request)
    {

        $Search_Value = $request->q;

        $status = getElectricianStatus();

        $data = array();
        foreach ($status as $status_key => $status_value) {
            if (in_array(Auth::user()->type, $status_value['access_user_type'])) {
                if ($Search_Value != '') {
                    if (preg_match("/" . $Search_Value . "/i", $status_value['name'])) {
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

        $response = array();
        $response['results'] = $data;
        $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function addContactEleClient(Request $request)
    {
        try {

            $Ele = User::query();
            $Ele->whereIn('type', [301, 302]);
            $Ele = $Ele->get();

            $is_error = "";
            foreach ($Ele as $key => $value) {
                try {

                    $UserContact = new UserContact();

                    $UserContact->user_id = $value->id;
                    $UserContact->_id = 0;
                    $UserContact->first_name = $value->first_name;
                    $UserContact->last_name = $value->last_name;
                    $UserContact->phone_number = $value->phone_number;
                    $UserContact->alernate_phone_number = 0;
                    $UserContact->email = $value->email;
                    $UserContact->type = $value->type;
                    $UserContact->type_detail = "user-" . $value->type . '-' . $value->id;

                    $UserContact->save();

                    if ($UserContact) {
                        $user_update = User::find($value->id);
                        $user_update->main_contact_id = $UserContact->id;
                        $user_update->save();
                    }


                    $response = successRes("All Leads & Deals Transfered Successfully");
                    $response["data"] = $Ele;
                    $response["data1"] = $Ele->count();
                } catch (\Exception $e) {
                    $is_success = 107;
                    $is_error .= "User ID : " . $value->id . " = ERRPR(5360) - " . $e->getMessage() . " </br>";
                    $response = errorRes($e->getMessage(), 400);
                }
            }
        } catch (\Throwable $th) {
            $response = errorRes($th->getMessage(), 400);
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function usergetElectrician(Request $request)
    {
        $query = $request->input('q');
        $User = array();
        $User = User::select('id', 'first_name', 'last_name', 'phone_number');
        $User->whereIn('type', ['301', '302']);
        $User->where(function ($userQuery) use ($query) {
            $userQuery->where('id', 'like', '%' . $query . '%')
                ->orWhere('first_name', 'like', '%' . $query . '%')
                ->orWhere('last_name', 'like', '%' . $query . '%')
                ->orWhere('phone_number', 'like', '%' . $query . '%');
        });
        $User->where('status', 1);
        $User->limit(5);
        $User = $User->get();

        $response = array();
        $response['results'] = $User;
        $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');
    }
}
