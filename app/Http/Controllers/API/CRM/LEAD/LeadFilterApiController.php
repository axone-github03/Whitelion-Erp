<?php

namespace App\Http\Controllers\API\CRM\LEAD;

use App\Models\User;
use App\Models\Exhibition;
use App\Models\SalePerson;
use App\Models\UserDefaultView;
use Illuminate\Http\Request;
use App\Models\CRMSettingSource;
use App\Models\CityList;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\CRMLeadAdvanceFilter;
use Illuminate\Support\Facades\Auth;
use App\Models\CRMSettingCompetitors;
use App\Models\CRMSettingStageOfSite;
use App\Models\CRMSettingWantToCover;
use App\Models\CRMLeadAdvanceFilterItem;
use App\Models\Tags;
use Illuminate\Support\Facades\Validator;

// use Illuminate\Http\Request;

class LeadFilterApiController extends Controller
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

    function searchAdvanceFilterColumn(Request $request)
    {
        $data = array();

        $Search_Value = $request->q;
        $Filter_Column = getFilterColumnCRM();

        $data = array();

        foreach ($Filter_Column as $key => $value) {
            if ($Search_Value != '') {
                if (preg_match("/" . $Search_Value . "/i", $value['name'])) {
                    $new_data['id'] = $value['id'];
                    $new_data['text'] = $value['name'];
                    $new_data['value_type'] = $value['value_type'];
                }
            } else {
                $new_data['id'] = $value['id'];
                $new_data['text'] = $value['name'];
                $new_data['value_type'] = $value['value_type'];
            }


            array_push($data, $new_data);
        }

        $response = successRes();
		$response['data'] = $data;
		return response()->json($response)->header('Content-Type', 'application/json');
    }

    function searchAdvanceFilterCondition(Request $request)
    {
        $data = array();
        $rules = array();
        $rules['column'] = 'required';

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {

            $response = errorRes($validator->errors()->first());
            $response['data'] = $validator->errors();

        } else {

            $Search_Value = $request->q;
            $Filter_Column = getFilterColumnCRM()[$request->column];
            $Filter_Condtion = getFilterCondtionCRM();

            $data = array();
            switch ($Filter_Column['value_type']) {
                case 'date':
                    foreach ($Filter_Condtion as $key => $value) {
                        $new_data = array();
                        if ($value['code'] == "is" || $value['code'] == "between") {
                            if ($Search_Value != '') {
                                if (preg_match("/" . $Search_Value . "/i", $value['name'])) {
                                    $new_data['id'] = $value['id'];
                                    $new_data['text'] = $value['name'];
                                    $new_data['value_type'] = $value['value_type'];
                                    $new_data['code'] = $value['code'];
                                    array_push($data, $new_data);
                                }
                            } else {
                                $new_data['id'] = $value['id'];
                                $new_data['text'] = $value['name'];
                                $new_data['value_type'] = $value['value_type'];
                                $new_data['code'] = $value['code'];
                                array_push($data, $new_data);
                            }
                        }
                    }
                    break;
                default:
                    foreach ($Filter_Condtion as $key => $value) {
                        $new_data = array();
                        if ($value['value_type'] == "single_select" || $value['value_type'] == "multi_select") {
                            if ($Search_Value != '') {
                                if (preg_match("/" . $Search_Value . "/i", $value['name'])) {
                                    $new_data['id'] = $value['id'];
                                    $new_data['text'] = $value['name'];
                                    $new_data['value_type'] = $value['value_type'];
                                    $new_data['code'] = $value['code'];
                                    array_push($data, $new_data);
                                }
                            } else {
                                $new_data['id'] = $value['id'];
                                $new_data['text'] = $value['name'];
                                $new_data['value_type'] = $value['value_type'];
                                $new_data['code'] = $value['code'];
                                array_push($data, $new_data);
                            }
                        }
                    }
                    break;
            }

        }

        $response = successRes();
		$response['data'] = $data;
		return response()->json($response)->header('Content-Type', 'application/json');
    }

    function saveViewAsDefault(Request $request)
    {
        $rules = array();
        $rules['view_id'] = 'required';

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {

            $response = errorRes($validator->errors()->first());
            $response['data'] = $validator->errors();

        } else {
            if ($request->view_id != '' && $request->view_id != 0) {

                $already_default_view_set = UserDefaultView::query();
                $already_default_view_set->where('user_id', Auth::user()->id);
                $already_default_view_set->where('default_type', 'user_wise');
                $already_default_view_set = $already_default_view_set->first();
                if ($already_default_view_set) {
                    $saveUserDefaultView = UserDefaultView::find($already_default_view_set->id);
                    $saveUserDefaultView->updateby = Auth::user()->id;
                    $saveUserDefaultView->updateip = $request->ip();
                } else {
                    $saveUserDefaultView = new UserDefaultView();
                    $saveUserDefaultView->entryby = Auth::user()->id;
                    $saveUserDefaultView->entryip = $request->ip();
                }
                $saveUserDefaultView->filterview_id = $request->view_id;
                $saveUserDefaultView->user_id = Auth::user()->id;
                $saveUserDefaultView->user_type = 0;
                $saveUserDefaultView->default_type = 'user_wise';
                $saveUserDefaultView->module = 'lead_deal';
                $saveUserDefaultView->remark = 'lead user wise default filter view';
                $saveUserDefaultView->save();

                if ($saveUserDefaultView) {
                    $response = successRes("Successfully set view as default");
                } else {
                    $response = errorRes("please contact to admin");
                }
            } else {

                $response = errorRes("please contact to admin");
            }
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function ViewSelectedFilter(Request $request)
    {

        $selected_view_set = CRMLeadAdvanceFilter::query();
        $selected_view_set->select('lead_advance_filter.id', 'lead_advance_filter.name');
        $selected_view_set->where('lead_advance_filter.id', $request->view_id);
        $selected_view_set = $selected_view_set->first();
        $selectedview = array();
        if ($selected_view_set) {

            $already_default_view_set = UserDefaultView::query();
            $already_default_view_set->where('user_id', Auth::user()->id);
            $already_default_view_set->where('filterview_id', $selected_view_set->id);
            $already_default_view_set->where('default_type', 'user_wise');
            $already_default_view_set = $already_default_view_set->first();

            if ($already_default_view_set) {
                $selectedview['id'] = $selected_view_set->id;
                $selectedview['text'] = $selected_view_set->name;
                $selectedview['isdefault'] = 1;
            } else {
                $selectedview['id'] = $selected_view_set->id;
                $selectedview['text'] = $selected_view_set->name;
                $selectedview['isdefault'] = 0;
            }


        } else {
            $selectedview['id'] = 0;
            $selectedview['text'] = '';
        }


        $response = successRes("Get Advance Filter View");
        $response['data'] = $selectedview;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function AdvanceFilterDelete(Request $request)
    {


        $rules = array();
        $rules['view_id'] = 'required';

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {

            $response = errorRes($validator->errors()->first());
            $response['data'] = $validator->errors();

        } else {
            if ($request->view_id != '' && $request->view_id != 0) {
                $is_public = CRMLeadAdvanceFilter::find($request->view_id)['is_public'];

                if ($is_public == 1) {

                    if( Auth::user()->type == 0){
                        CRMLeadAdvanceFilter::where([['lead_advance_filter.id', $request->view_id]])->delete();

                        CRMLeadAdvanceFilterItem::where([['lead_advance_filter_item.advance_filter_id', $request->view_id]])->delete();
    
                        $default_view_set = UserDefaultView::query();
                        $default_view_set->where('user_id', Auth::user()->id);
                        $default_view_set->where('filterview_id', $request->view_id);
                        $default_view_set->where('default_type', 'user_wise');
                        $default_view_set = $default_view_set->first();
    
                        if ($default_view_set) {
                            UserDefaultView::where([['user_default_views.id', $default_view_set->id]])->delete();
                        }
    
                        $response = successRes("Successsfully Delete Advance Filter");
                    }else{
                        $response = errorRes("You Can't Deleted The View");
                        $response['data'] = Auth::user()->type;
                    }


                } else {
                    CRMLeadAdvanceFilter::where([['lead_advance_filter.id', $request->view_id]])->delete();

                    CRMLeadAdvanceFilterItem::where([['lead_advance_filter_item.advance_filter_id', $request->view_id]])->delete();

                    $default_view_set = UserDefaultView::query();
                    $default_view_set->where('user_id', Auth::user()->id);
                    $default_view_set->where('filterview_id', $request->view_id);
                    $default_view_set->where('default_type', 'user_wise');
                    $default_view_set = $default_view_set->first();

                    if ($default_view_set) {
                        UserDefaultView::where([['user_default_views.id', $default_view_set->id]])->delete();
                    }

                    $response = successRes("Successsfully Delete Advance Filter");
    
                }

            } else {

                $response = errorRes("please contact to admin");
            }
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function searchReminderTimeSlot(Request $request)
    {

        $searchKeyword = isset($request->q) ? $request->q : "";

        $ReminderTimeSlot = getReminderTimeSlot();

        $finalArray[] = array();
        foreach ($ReminderTimeSlot as $key => $value) {
            $finalArray[$key]['id'] = $value['id'];
            $finalArray[$key]['text'] = $value['name'];
        }

        $response = array();
        $response['results'] = $finalArray;
        $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function searchAdvanceFilterView(Request $request)
    {
        $searchKeyword = isset($request->q) ? $request->q : "";

        $data = CRMLeadAdvanceFilter::select('id', 'name as text', 'lead_advance_filter.is_public');
        $data->where('lead_advance_filter.is_deal', $request->is_deal);

        $data->where(function ($query) {
            $query->orWhere('lead_advance_filter.user_id', Auth::user()->id);
            $query->orWhere('lead_advance_filter.is_public', 1);
        });

        $data->where('lead_advance_filter.name', 'like', "%" . $searchKeyword . "%");
        // $data->limit(10);
        $data = $data->get();

        $viewData = array();
        foreach ($data as $key => $value) {

            $viewData[$key] = array();
            $already_default_view_set = UserDefaultView::query();
            $already_default_view_set->where('user_id', Auth::user()->id);
            $already_default_view_set->where('filterview_id', $value->id);
            $already_default_view_set->where('default_type', 'user_wise');
            $already_default_view_set = $already_default_view_set->first();

            if ($value->is_public == 1 && Auth::user()->type != 0) {

                if ($already_default_view_set) {
                    $viewData[$key]['id'] = $value->id;
                    $viewData[$key]['name'] = $value->text;
                    $viewData[$key]['isdefault'] = 1;
                    $viewData[$key]['is_delatable'] = 0;
                } else {
                    $viewData[$key]['id'] = $value->id;
                    $viewData[$key]['name'] = $value->text;
                    $viewData[$key]['isdefault'] = 0;
                    $viewData[$key]['is_delatable'] = 0;
                }
            } else {
                if ($already_default_view_set) {
                    $viewData[$key]['id'] = $value->id;
                    $viewData[$key]['name'] = $value->text;
                    $viewData[$key]['isdefault'] = 1;
                    $viewData[$key]['is_delatable'] = 1;
                } else {
                    $viewData[$key]['id'] = $value->id;
                    $viewData[$key]['name'] = $value->text;
                    $viewData[$key]['isdefault'] = 0;
                    $viewData[$key]['is_delatable'] = 1;
                }

            }

        }

        $response = successRes("Get Advance Filter View");
        $response['data'] = $viewData;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function saveAdvanceFilter(Request $request)
    {
        $rules = array();
        $rules['view_name'] = 'required';
        $rules['is_deal'] = 'required';
        $rules['advance_filter_data'] = 'required';
        $rules['is_advance_filter'] = 'required';

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {

            $response = errorRes($validator->errors()->first());
            $response['data'] = $validator->errors();

        } else {
            if ($request->is_advance_filter > 0) {

                $AdvanceFilter = new CRMLeadAdvanceFilter();
                $AdvanceFilter->user_id = Auth::user()->id;
                $AdvanceFilter->is_deal = $request->is_deal;
                $AdvanceFilter->name = $request->view_name;
                $AdvanceFilter->is_public = $request->is_public;
                $AdvanceFilter->created_ip = $request->ip();
                $AdvanceFilter->created_by = Auth::user()->id;
                $AdvanceFilter->save();

                if ($AdvanceFilter) {
                    try {
                        foreach (json_decode($request->advance_filter_data, true) as $key => $filt_value) {
                            $column = getFilterColumnCRM()[$filt_value['column']];
                            $condtion = getFilterCondtionCRM()[$filt_value['condtion']];
                            $filter_value = '';
                            $source_type = '0';
                            if ($filt_value['clause'] == null || $filt_value['clause'] == '') {
                                $response = errorRes("Please Select Clause");
                                return response()->json($response)->header('Content-Type', 'application/json');
                                break;

                            } else if ($filt_value['column'] == null || $filt_value['column'] == '') {
                                $response = errorRes("Please Select column");
                                return response()->json($response)->header('Content-Type', 'application/json');
                                break;

                            } else if ($filt_value['condtion'] == null || $filt_value['condtion'] == '') {
                                $response = errorRes("Please Select condtion");
                                return response()->json($response)->header('Content-Type', 'application/json');
                                break;

                            } else {
                                if ($column['value_type'] == 'text') {

                                    if ($filt_value['value_text'] == null || $filt_value['value_text'] == '') {
                                        $response = errorRes("Please enter value");
                                        return response()->json($response)->header('Content-Type', 'application/json');
                                        break;
                                    } else {
                                        $filter_value = $filt_value['value_text'];
                                    }

                                } else if (($column['value_type'] == 'select') && ($condtion['value_type'] == 'single_select')) {
                                    if ($column['code'] == "leads_source" && ($filt_value['value_source_type'] == null || $filt_value['value_source_type'] == '')) {
                                        $response = errorRes("Please select value");
                                        return response()->json($response)->header('Content-Type', 'application/json');
                                        break;
                                    } else {
                                        $source_type = $filt_value['value_source_type'];
                                    }

                                    if ($filt_value['value_select'] == null || $filt_value['value_select'] == '') {
                                        $response = errorRes("Please select value");
                                        return response()->json($response)->header('Content-Type', 'application/json');
                                        break;
                                    } else {
                                        $filter_value = $filt_value['value_select'];
                                    }

                                } else if (($column['value_type'] == 'select') && ($condtion['value_type'] == 'multi_select')) {
                                    if ($column['code'] == "leads_source" && ($filt_value['value_source_type'] == null || $filt_value['value_source_type'] == '')) {
                                        $response = errorRes("Please select value");
                                        return response()->json($response)->header('Content-Type', 'application/json');
                                        break;
                                    } else {
                                        $source_type = $filt_value['value_source_type'];
                                    }
                                    if (!isset($filt_value['value_multi_select']) && empty($filt_value['value_multi_select'])) {
                                        $response = errorRes("Please select value");
                                        return response()->json($response)->header('Content-Type', 'application/json');
                                        break;
                                    } else {
                                        $filter_value = implode(",", $filt_value['value_multi_select']);
                                    }

                                    // if ($filt_value['value_multi_select'] == null || $filt_value['value_multi_select'] == '') {
                                    //     $response = errorRes("Please select value");
                                    //     return response()->json($response)->header('Content-Type', 'application/json');
                                    //     break;
                                    // } else {
                                    //     $filter_value = implode(",", $filt_value['value_multi_select']);
                                    // }

                                } else if (($column['value_type'] == 'date') && ($condtion['value_type'] == 'single_select')) {

                                    if ($filt_value['value_date'] == null || $filt_value['value_date'] == '') {
                                        $response = errorRes("Please enter date");
                                        return response()->json($response)->header('Content-Type', 'application/json');
                                        break;
                                    } else {
                                        $filter_value = $filt_value['value_date'];
                                    }

                                } else if (($column['value_type'] == 'date') && ($condtion['value_type'] == 'between')) {

                                    if (($filt_value['value_from_date'] == null || $filt_value['value_from_date'] == '') && ($filt_value['value_to_date'] == null || $filt_value['value_to_date'] == '')) {
                                        $response = errorRes("Please enter from to date");
                                        return response()->json($response)->header('Content-Type', 'application/json');
                                        break;
                                    } else {
                                        $filter_value = $filt_value['value_from_date'] . "," . $filt_value['value_to_date'];
                                    }
                                }

                                $AdvanceFilterItem = new CRMLeadAdvanceFilterItem();
                                $AdvanceFilterItem->user_id = Auth::user()->id;
                                $AdvanceFilterItem->is_deal = $AdvanceFilter->is_deal;
                                $AdvanceFilterItem->advance_filter_id = $AdvanceFilter->id;
                                if ($filt_value['clause'] == 0) {
                                    $AdvanceFilterItem->clause_id = 0;
                                } else {
                                    $AdvanceFilterItem->clause_id = $filt_value['clause'];
                                }
                                $AdvanceFilterItem->column_id = $filt_value['column'];
                                $AdvanceFilterItem->condition_id = $filt_value['condtion'];
                                $AdvanceFilterItem->value = $filter_value;
                                $AdvanceFilterItem->source_type = $source_type;
                                $AdvanceFilterItem->created_by = Auth::user()->id;
                                $AdvanceFilterItem->created_ip = $request->ip();
                                $AdvanceFilterItem->save();

                            }

                        }
                        $response = successRes("Filter View Saved Successfully");
                    } catch (\Exception $e) {
                        $response = errorRes($e->getMessage(), 400);
                    }
                }
            }
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function searchFilterValue(Request $request)
    {

        $data = array();
        $rules = array();
        $rules['is_deal'] = 'required';
        $rules['column'] = 'required';
        $rules['condtion'] = 'required';
        // $rules['source_type'] = 'required';

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {

            $response = errorRes($validator->errors()->first());
            $response['data'] = $validator->errors();

        } else {

            $isSalePerson = isSalePerson();
            $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
            $isThirdPartyUser = isThirdPartyUser();
            $isTeleSales = isTaleSalesUser();
            $isChannelPartner = isChannelPartner(Auth::user()->type);
            if ($isSalePerson == 1) {
                $childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
            }

            $Search_Value = $request->q;
            $Lead_type = $request->is_deal;
            $Filter_Column = getFilterColumnCRM()[$request->column];
            $Filter_Condtion = getFilterCondtionCRM()[$request->condtion];
            $LeadStatus = getLeadStatus();

            $data = array();
            switch ($Filter_Column['value_type']) {
                case 'select':
                    switch ($Filter_Column['code']) {
                        case 'leads_status':
                            foreach ($LeadStatus as $key => $value) {
                                $new_data = array();
                                if ($value['type'] == $Lead_type) {
                                    if ($Search_Value != '') {
                                        if (preg_match("/" . $Search_Value . "/i", $value['name'])) {
                                            $new_data['id'] = $value['id'];
                                            $new_data['text'] = $value['name'];
                                            array_push($data, $new_data);
                                        }
                                    } else {
                                        $new_data['id'] = $value['id'];
                                        $new_data['text'] = $value['name'];
                                        array_push($data, $new_data);
                                    }
                                }
                            }
                            break;

                        case 'leads_site_stage':

                            $data = CRMSettingStageOfSite::select('id', 'name as text');
                            $data->where('crm_setting_stage_of_site.status', 1);
                            $data->where('crm_setting_stage_of_site.name', 'like', "%" . $Search_Value . "%");
                            $data->limit(10);
                            $data = $data->get();
                            break;

                        case 'leads_competitor':

                            $data = CRMSettingCompetitors::select('id', 'name as text');
                            $data->where('crm_setting_competitors.status', 1);
                            $data->where('crm_setting_competitors.name', 'like', "%" . $Search_Value . "%");
                            $data->limit(10);
                            $data = $data->get();
                            break;

                        case 'leads_want_to_cover':

                            $data = CRMSettingWantToCover::select('id', 'name as text');
                            $data->where('crm_setting_want_to_cover.status', 1);
                            $data->where('crm_setting_want_to_cover.name', 'like', "%" . $Search_Value . "%");
                            $data->limit(10);
                            $data = $data->get();
                            break;

                        case 'leads_assigned_to':
                            $User = User::select('users.id', 'users.type', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));

                            // $User->where('users.status', 1);

                            if ($isAdminOrCompanyAdmin == 1) {
                                $User->whereIn('users.type', array(2));
                                // $User->whereIn('users.type', array(0, 1, 2));
                            } else if ($isThirdPartyUser == 1) {

                                $User->whereIn('users.type', array(2));
                                $User->where('users.city_id', Auth::user()->city_id);
                            } else if ($isSalePerson == 1) {

                                $User->where('users.type', 2);
                                $User->whereIn('users.id', $childSalePersonsIds);
                            } else if ($isChannelPartner != 0) {
                                $User->where('users.type', 2);
                                $User->where('users.city_id', Auth::user()->city_id);
                            } else if($isTeleSales == 1){
                                $User->whereIn('users.type', array(2));
                            }

                            $User->where(function ($query) use ($Search_Value) {
                                $query->where('users.first_name', 'like', '%' . $Search_Value . '%');
                                $query->orWhere('users.last_name', 'like', '%' . $Search_Value . '%');
                                $query->orWhereRaw("CONCAT(users.first_name,' ',users.last_name)" . ' like ?', ['%' . $Search_Value . '%']);
                            });

                            $User->limit(10);
                            $User = $User->get();

                            if (count($User) > 0) {
                                foreach ($User as $User_key => $User_value) {

                                    $label = ' - ' . getUserTypeMainLabel($User_value->type);
                                    $new_data['id'] = $User_value['id'];
                                    $new_data['text'] = $User_value['full_name'] . $label;

                                    array_push($data, $new_data);
                                }
                            }
                            break;

                        case 'leads_created_by':
                            $User = User::select('users.id', 'users.type', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));

                            $User->where('users.status', 1);

                            if ($isAdminOrCompanyAdmin == 1) {

                                $User->whereIn('users.type', [0, 1, 2]);
                                // $User->whereIn('users.type', array(0, 1, 2));
                            } else if ($isSalePerson == 1) {

                                $User->whereIn('users.type', [0, 1, 2]);
                                $User->whereIn('users.id', $childSalePersonsIds);
                            } else if ($isChannelPartner != 0) {

                                $User->whereIn('users.type', [0, 1, 2]);
                                $User->where('users.city_id', Auth::user()->city_id);
                            }

                            $User->where(function ($query) use ($Search_Value) {
                                $query->where('users.first_name', 'like', '%' . $Search_Value . '%');
                                $query->orWhere('users.last_name', 'like', '%' . $Search_Value . '%');
                                $query->orWhereRaw("CONCAT(users.first_name,' ',users.last_name)" . ' like ?', ['%' . $Search_Value . '%']);
                            });

                            $User->limit(10);
                            $User = $User->get();

                            if (count($User) > 0) {
                                foreach ($User as $User_key => $User_value) {

                                    $label = ' - ' . getUserTypeMainLabel($User_value->type);
                                    $new_data['id'] = $User_value['id'];
                                    $new_data['text'] = $User_value['full_name'] . $label;

                                    array_push($data, $new_data);
                                }
                            }
                            break;

                        case 'leads_source':

                            $isArchitect = isArchitect();
                            $isSalePerson = isSalePerson();
                            $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
                            $isThirdPartyUser = isThirdPartyUser();
                            $isChannelPartner = isChannelPartner(Auth::user()->type);


                            if ($request->source_type == '' || $request->source_type == null) {
                                $response = errorRes("please select source type");
                                return response()->json($response)->header('Content-Type', 'application/json');
                            }
                            $source_type = explode("-", $request->source_type);


                            if ($source_type[0] == "user") {

                                if (isset(getChannelPartners()[$source_type[1]]['short_name'])) {

                                    if ($isSalePerson == 1) {

                                        $childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);

                                        $salePerson = SalePerson::select('cities')->where('user_id', Auth::user()->id)->first();
                                        $cities = array();
                                        if ($salePerson) {

                                            $cities = explode(",", $salePerson->cities);
                                        } else {
                                            $cities = array(0);
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

                                    $data->where(function ($query) use ($Search_Value) {
                                        $query->where('channel_partner.firm_name', 'like', '%' . $Search_Value . '%');
                                    });
                                    $data->limit(5);
                                    $data = $data->get();

                                } else {
                                    $data = User::select('users.id', 'users.type', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"), 'users.phone_number');
                                    $data->where('users.status', 1);


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



                                    $data->where(function ($query) use ($Search_Value) {
                                        $query->where('users.first_name', 'like', '%' . $Search_Value . '%');
                                        $query->orWhere('users.last_name', 'like', '%' . $Search_Value . '%');
                                        $query->orWhere('users.phone_number', 'like', '%' . $Search_Value . '%');
                                        $query->orWhereRaw("CONCAT(users.first_name,' ',users.last_name)" . ' like ?', ['%' . $Search_Value . '%']);
                                    });

                                    $data->limit(5);
                                    $data = $data->get();
                                    $newdata = array();
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

                            } elseif ($source_type[0] == "master") {
                                $data = CRMSettingSource::select('id', 'name as text');
                                $data->where('crm_setting_source.status', 1);
                                $data->where('crm_setting_source.source_type_id', $source_type[1]);
                                $data->where('crm_setting_source.name', 'like', "%" . $Search_Value . "%");
                                $data->limit(5);
                                $data = $data->get();
                            } elseif ($source_type[0] == "exhibition") {
                                $data = Exhibition::select('id', 'name as text');
                                $data->where('exhibition.name', 'like', "%" . $Search_Value . "%");
                                $data->limit(5);
                                $data = $data->get();
                            } else {
                                $data = "";
                            }
                            break;

                        case 'leads_tag' :
                            $data = Tags::select('id', 'tagname as text');
                            $data->where('tag_master.isactive', 1);
                            $data->where('tag_master.tag_type', 201);
                            $data->where('tag_master.tagname', 'like', "%" . $Search_Value . "%");
                            $data->limit(10);
                            $data = $data->get();
                            break;
                        case 'lead_miss_data' :
                            $data = array();
                            foreach (getLeadFilterMissFilterValue() as $key => $value) {
                                $data_new['id'] = $value['id'];
                                $data_new['text'] = $value['name'];
                                array_push($data,$data_new);
                            }
                            break;
                        case 'leads_city_id' :
                            $data = CityList::select('id', 'name as text');
                            $data->where('status', 1);
                            $data->where('name', 'like', "%" . $Search_Value . "%");
                            $data->limit(10);
                            $data = $data->get();

                            break;
                        default:
                            $data = errorRes();
                            break;
                    }
                    break;
                case 'date':
                    switch ($Filter_Column['code']) {

                        case 'leads_closing_date_time':

                            foreach (getDateFilterValue() as $key => $value) {
                                $new_data = array();
                                if ($Search_Value != '') {
                                    if (preg_match("/" . $Search_Value . "/i", $value['name'])) {
                                        $new_data['id'] = $value['id'];
                                        $new_data['text'] = $value['name'];
                                        array_push($data, $new_data);
                                    }
                                } else {
                                    $new_data['id'] = $value['id'];
                                    $new_data['text'] = $value['name'];
                                    array_push($data, $new_data);
                                }
                            }
                            break;

                        case 'leads_created_at':

                            foreach (getDateFilterValue() as $key => $value) {
                                $new_data = array();
                                if ($Search_Value != '') {
                                    if (preg_match("/" . $Search_Value . "/i", $value['name'])) {
                                        $new_data['id'] = $value['id'];
                                        $new_data['text'] = $value['name'];
                                        array_push($data, $new_data);
                                    }
                                } else {
                                    $new_data['id'] = $value['id'];
                                    $new_data['text'] = $value['name'];
                                    array_push($data, $new_data);
                                }
                            }
                            break;

                        default:
                            $data = errorRes();
                            break;
                    }
                    break;


                default:
                    break;
            }

        }



        $response = successRes();
        $response['data'] = $data;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function getDetailAdvanceFilter(Request $request)
    {
        $rules = array();
        $rules['view_id'] = 'required';

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {

            $response = errorRes($validator->errors()->first());
            $response['data'] = $validator->errors();

        } else {

            $viewid = $request->view_id;
            $LeadStatus = getLeadStatus();
            $lstDateFilter = getDateFilterValue();

            $Filter = CRMLeadAdvanceFilter::find($viewid);

            if ($Filter) {
                // try {
                $data = array();
                $filteriteam = CRMLeadAdvanceFilterItem::query()->where('advance_filter_id', $Filter->id)->get();
                if (count($filteriteam) > 0) {
                    foreach ($filteriteam as $key => $filt_value) {
                        if ($filt_value['clause_id'] == 0) {
                            $clause = array();
                            $clause['id'] = 0;
                            $clause['name'] = "WHERE";
                            $clause['clause'] = "where";

                        } else {
                            $clause = getFilterClauseCRM()[$filt_value['clause_id']];
                        }
                        $column = getFilterColumnCRM()[$filt_value['column_id']];
                        $condtion = getFilterCondtionCRM()[$filt_value['condition_id']];

                        $filtval = $filt_value['value'];

                        $new_data['clause_id'] = $clause['id'];
                        $new_data['clause_text'] = $clause['name'];

                        $new_data['column_id'] = $column['id'];
                        $new_data['column_text'] = $column['name'];
                        $new_data['column_valtype'] = $column['value_type'];

                        $new_data['condtion_id'] = $condtion['id'];
                        $new_data['condtion_text'] = $condtion['name'];
                        $new_data['condtion_valtype'] = $condtion['value_type'];

                        $arrclause = array();
                        $User = array();

                        $new_source_type_id = '';
                        $new_source_type_text = '';
                        $new_data['source_type_database'] = $filt_value['source_type'];
                        if ($filt_value['source_type'] != "0" && $filt_value['source_type'] != null) {

                            $new_source_type_id = $filt_value['source_type'];
                            $new_source_type_text = '';

                            $source_type = explode("-", $filt_value['source_type']);
                            foreach (getLeadSourceTypes() as $source_type_key => $source_type_value) {

                                if ($source_type[1] == 201) {
                                    $source_type_id = 202;
                                } else if ($source_type[1] == 301) {
                                    $source_type_id = 302;
                                } else {
                                    $source_type_id = $source_type[1];
                                }

                                if ($source_type_value['type'] == $source_type[0] && $source_type_value['id'] == $source_type_id) {
                                    $new_source_type_text = $source_type_value['lable'];
                                    break;
                                }
                            }
                        }

                        $new_data['source_type_id'] = $new_source_type_id;
                        $new_data['source_type_text'] = $new_source_type_text;

                        if (($column['value_type'] == 'select') && ($column['code'] == 'leads_status')) {
                            if ($condtion['value_type'] == 'single_select') {
                                $new_valdata['id'] = $LeadStatus[$filtval]['id'];
                                $new_valdata['text'] = $LeadStatus[$filtval]['name'];
                                array_push($arrclause, $new_valdata);

                            } else if ($condtion['value_type'] == 'multi_select') {
                                foreach (explode(",", $filtval) as $key => $val) {
                                    $new_valdata['id'] = $LeadStatus[$val]['id'];
                                    $new_valdata['text'] = $LeadStatus[$val]['name'];
                                    array_push($arrclause, $new_valdata);
                                }
                            }

                        } else if (($column['value_type'] == 'select')) {
                            if ($condtion['value_type'] == 'single_select') {
                                $User = User::select('users.id', 'channel_partner.firm_name', 'users.type', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"));
                                $User->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
                                $User->where('users.status', 1);
                                $User->where('users.id', $filtval);
                                $User = $User->first();

                                if ($User) {
                                    $label = ' - ' . ucwords(strtolower(getUserTypeMainLabel($User->type)));
                                    $new_valdata['id'] = $User['id'];
                                    if (isset(getChannelPartners()[$User->type]['short_name'])) {
                                        $new_valdata['text'] = $User['firm_name'] . $label;
                                    } else {
                                        $new_valdata['text'] = $User['text'] . $label;
                                    }
                                    array_push($arrclause, $new_valdata);
                                } else {
                                    $new_valdata['id'] = 0;
                                    $new_valdata['text'] = 'undifine';
                                    array_push($arrclause, $new_valdata);
                                }

                            } else if ($condtion['value_type'] == 'multi_select') {
                                $User = User::select('users.id', 'channel_partner.firm_name', 'users.type', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS name"));
                                $User->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
                                $User->whereIn('users.id', explode(',', $filtval));
                                $User = $User->get();

                                if (count($User) > 1) {
                                    foreach ($User as $keyv => $valdata) {
                                        $label = ' - ' . ucwords(strtolower(getUserTypeMainLabel($valdata['type'])));
                                        $new_valdata['id'] = $valdata['id'];
                                        if (isset(getChannelPartners()[$valdata->type]['short_name'])) {
                                            $new_valdata['text'] = $valdata['firm_name'] . $label;
                                        } else {
                                            $new_valdata['text'] = $valdata['name'] . $label;
                                        }
                                        array_push($arrclause, $new_valdata);

                                    }
                                } else {
                                    $new_valdata['id'] = 0;
                                    $new_valdata['text'] = 'undifine';
                                    array_push($arrclause, $new_valdata);
                                }

                            }

                        } else if (($column['value_type'] == 'date') && $column['code'] == 'leads_closing_date_time' && $condtion['value_type'] == 'single_select') {

                            $new_valdata['id'] = $lstDateFilter[$filtval]['id'];
                            $new_valdata['text'] = $lstDateFilter[$filtval]['name'];
                            array_push($arrclause, $new_valdata);

                        } else {
                            $new_valdata['id'] = 0;
                            $new_valdata['text'] = $filtval;
                            array_push($arrclause, $new_valdata);
                        }

                        $new_data['value'] = $arrclause;
                        array_push($data, $new_data);
                    }
                }
                $response = successRes("Filter View Successfully");
                $response['filter'] = $Filter;
                $response['filter_item'] = $data;
                // } catch (\Exception $e) {
                //     $response = errorRes($e->getMessage(), 400);
                // }
            } else {
                $response = errorRes("Please Contact Admin");
                $response['filter'] = 0;
                $response['filter_item'] = "";
            }
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }

}