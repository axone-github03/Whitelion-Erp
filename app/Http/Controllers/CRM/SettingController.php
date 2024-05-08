<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CRMSettingStageOfSite;
use App\Models\CRMSettingSiteType;
use App\Models\CRMSettingBHK;
use App\Models\CRMSettingCompetitors;
use App\Models\CRMSettingSourceType;
use App\Models\CRMSettingSubStatus;
use App\Models\CRMSettingSource;
use App\Models\CRMSettingWantToCover;
use App\Models\CRMSettingContactTag;
use App\Models\CRMSettingMeetingTitle;
use App\Models\CRMSettingCallType;
use App\Models\CRMSettingMeetingType;
use App\Models\CRMSettingCallOutcomeType;
use App\Models\CRMSettingMeetingOutcomeType;
use App\Models\CRMSettingTaskOutcomeType;
use App\Models\CRMSettingAdditionalInfo;
use App\Models\Tags;

use App\Models\CRMSettingFileTag;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class SettingController extends Controller
{

    public function __construct()
    {

        $this->middleware(function ($request, $next) {

            $tabCanAccessBy = array(0, 1);

            if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
                return redirect()->route('dashboard');
            }

            return $next($request);
        });
    }


    public function index(Request $request)
    {
        $data = array();
        $data['title'] = "Setting";
        $data['type'] = isset($request->type) ? $request->type : 'site_stage';
        return view('crm/setting/' . $data['type'], compact('data'));
    }


    public function ajaxStageOfSite(Request $request)
    {



        $searchColumns = array(

            0 => 'crm_setting_stage_of_site.id',
            1 => 'crm_setting_stage_of_site.name',
        );

        $columns = array(
            0 => 'crm_setting_stage_of_site.id',
            1 => 'crm_setting_stage_of_site.name',
            2 => 'crm_setting_stage_of_site.status',

        );

        $recordsTotal = CRMSettingStageOfSite::count();
        $recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.

        $query = CRMSettingStageOfSite::query();
        $query->select($columns);
        $query->limit($request->length);
        $query->offset($request->start);
        $query->orderBy($columns[$request['order'][0]['column']], $request['order'][0]['dir']);
        $isFilterApply = 0;
        $search_value = '';

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

            $viewData[$key]['id'] = "<p>" . highlightString($value['id'],$search_value) . '</p>';
            $viewData[$key]['name'] = "<p>" . highlightString($value['name'],$search_value) . '</p>';
            $viewData[$key]['status'] = getCRMStageOfSiteStatusLable($value['status']);

            $uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';
            $uiAction .= '<li class="list-inline-item px-2">';
            $uiAction .= '<a onclick="editView(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Edit"><i class="bx bx-edit-alt"></i></a>';
            $uiAction .= '</li>';
            $uiAction .= '</ul>';

            $viewData[$key]['action'] = $uiAction;
        }

        $jsonData = array(
            "draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($recordsTotal), // total number of records
            "recordsFiltered" => intval($recordsFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $viewData, // total data array

        );
        return $jsonData;
    }

    public function saveStageOfSite(Request $request)
    {

        $rules = array();
        $rules['data_id'] = 'required';
        $rules['data_name'] = 'required';
        $rules['data_status'] = 'required';
        $customMessage = array();
        $validator = Validator::make($request->all(), $rules, $customMessage);

        if ($validator->fails()) {

            $response = errorRes("The request could not be understood by the server due to malformed syntax");
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors();
        } else {

            $alreadyName = CRMSettingStageOfSite::query();
            $alreadyName->where('name', $request->data_name);

            if ($request->data_id != 0) {
                $alreadyName->where('id', '!=', $request->data_id);
            }
            $alreadyName = $alreadyName->first();
            if ($alreadyName) {

                $response = errorRes("already name exits, Try with another name");
            } else {
                if ($request->data_id != 0) {

                    $data = CRMSettingStageOfSite::find($request->data_id);
                } else {
                    $data = new CRMSettingStageOfSite();
                }
                $data->name = $request->data_name;
                $data->status = $request->data_status;
                $data->save();
                $response = successRes("Successfully saved data");
            }
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function deailStageOfSite(Request $request)
    {

        $data = CRMSettingStageOfSite::find($request->id);
        if ($data) {

            $response = successRes("Successfully get main master");
            $response['data'] = $data;
        } else {
            $response = errorRes("Invalid id");
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function ajaxSiteType(Request $request)
    {
        $searchColumns = array(

            0 => 'crm_setting_site_type.id',
            1 => 'crm_setting_site_type.name',
        );

        $columns = array(
            0 => 'crm_setting_site_type.id',
            1 => 'crm_setting_site_type.name',
            2 => 'crm_setting_site_type.status',

        );

        $recordsTotal = CRMSettingSiteType::count();
        $recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.

        $query = CRMSettingSiteType::query();
        $query->select($columns);
        $query->limit($request->length);
        $query->offset($request->start);
        $query->orderBy($columns[$request['order'][0]['column']], $request['order'][0]['dir']);
        $isFilterApply = 0;
        $search_value = '';

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

            $viewData[$key]['id'] = "<p>" . highlightString($value['id'],$search_value) . '</p>';
            $viewData[$key]['name'] = "<p>" . highlightString($value['name'],$search_value) . '</p>';
            $viewData[$key]['status'] = getCRMSiteTypeStatusLable($value['status']);

            $uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';
            $uiAction .= '<li class="list-inline-item px-2">';
            $uiAction .= '<a onclick="editView(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Edit"><i class="bx bx-edit-alt"></i></a>';
            $uiAction .= '</li>';
            $uiAction .= '</ul>';

            $viewData[$key]['action'] = $uiAction;
        }

        $jsonData = array(
            "draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($recordsTotal), // total number of records
            "recordsFiltered" => intval($recordsFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $viewData, // total data array

        );
        return $jsonData;
    }

    public function saveSiteType(Request $request)
    {

        $rules = array();
        $rules['data_id'] = 'required';
        $rules['data_name'] = 'required';
        $rules['data_status'] = 'required';
        $customMessage = array();
        $validator = Validator::make($request->all(), $rules, $customMessage);

        if ($validator->fails()) {

            $response = errorRes("The request could not be understood by the server due to malformed syntax");
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors();
        } else {

            $alreadyName = CRMSettingSiteType::query();
            $alreadyName->where('name', $request->data_name);

            if ($request->data_id != 0) {
                $alreadyName->where('id', '!=', $request->data_id);
            }
            $alreadyName = $alreadyName->first();
            if ($alreadyName) {

                $response = errorRes("already name exits, Try with another name");
            } else {
                if ($request->data_id != 0) {

                    $data = CRMSettingSiteType::find($request->data_id);
                } else {
                    $data = new CRMSettingSiteType();
                }
                $data->name = $request->data_name;
                $data->status = $request->data_status;
                $data->is_bhk = isset($request->is_bhk) ?$request->is_bhk : 0;
                $data->save();
                $response = successRes("Successfully saved data");
            }
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function deailSiteType(Request $request)
    {

        $data = CRMSettingSiteType::find($request->id);
        if ($data) {

            $response = successRes("Successfully get main master");
            $response['data'] = $data;
        } else {
            $response = errorRes("Invalid id");
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function ajaxBHK(Request $request)
    {



        $searchColumns = array(

            0 => 'crm_setting_bhk.id',
            1 => 'crm_setting_bhk.name',
        );

        $columns = array(
            0 => 'crm_setting_bhk.id',
            1 => 'crm_setting_bhk.name',
            2 => 'crm_setting_bhk.status',

        );

        $recordsTotal = CRMSettingBHK::count();
        $recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.

        $query = CRMSettingBHK::query();
        $query->select($columns);
        $query->limit($request->length);
        $query->offset($request->start);
        $query->orderBy($columns[$request['order'][0]['column']], $request['order'][0]['dir']);
        $isFilterApply = 0;
        $search_value = '';

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

            $viewData[$key]['id'] = "<p>" . highlightString($value['id'],$search_value) . '</p>';
            $viewData[$key]['name'] = "<p>" . highlightString($value['name'],$search_value) . '</p>';
            $viewData[$key]['status'] = getCRMBHKStatusLable($value['status']);

            $uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';
            $uiAction .= '<li class="list-inline-item px-2">';
            $uiAction .= '<a onclick="editView(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Edit"><i class="bx bx-edit-alt"></i></a>';
            $uiAction .= '</li>';
            $uiAction .= '</ul>';

            $viewData[$key]['action'] = $uiAction;
        }

        $jsonData = array(
            "draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($recordsTotal), // total number of records
            "recordsFiltered" => intval($recordsFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $viewData, // total data array

        );
        return $jsonData;
    }

    public function saveBHK(Request $request)
    {

        $rules = array();
        $rules['data_id'] = 'required';
        $rules['data_name'] = 'required';
        $rules['data_status'] = 'required';
        $customMessage = array();
        $validator = Validator::make($request->all(), $rules, $customMessage);

        if ($validator->fails()) {

            $response = errorRes("The request could not be understood by the server due to malformed syntax");
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors();
        } else {

            $alreadyName = CRMSettingBHK::query();
            $alreadyName->where('name', $request->data_name);

            if ($request->data_id != 0) {
                $alreadyName->where('id', '!=', $request->data_id);
            }
            $alreadyName = $alreadyName->first();
            if ($alreadyName) {

                $response = errorRes("already name exits, Try with another name");
            } else {
                if ($request->data_id != 0) {

                    $data = CRMSettingBHK::find($request->data_id);
                } else {
                    $data = new CRMSettingBHK();
                }
                $data->name = $request->data_name;
                $data->status = $request->data_status;
                $data->save();
                $response = successRes("Successfully saved data");
            }
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function deailBHK(Request $request)
    {

        $data = CRMSettingBHK::find($request->id);
        if ($data) {

            $response = successRes("Successfully get main master");
            $response['data'] = $data;
        } else {
            $response = errorRes("Invalid id");
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function ajaxWantToCover(Request $request)
    {



        $searchColumns = array(

            0 => 'crm_setting_want_to_cover.id',
            1 => 'crm_setting_want_to_cover.name',
        );

        $columns = array(
            0 => 'crm_setting_want_to_cover.id',
            1 => 'crm_setting_want_to_cover.name',
            2 => 'crm_setting_want_to_cover.status',

        );

        $recordsTotal = CRMSettingWantToCover::count();
        $recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.

        $query = CRMSettingWantToCover::query();
        $query->select($columns);
        $query->limit($request->length);
        $query->offset($request->start);
        $query->orderBy($columns[$request['order'][0]['column']], $request['order'][0]['dir']);
        $isFilterApply = 0;
        $search_value = '';


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

            $viewData[$key]['id'] = "<p>" . highlightString($value['id'],$search_value) . '</p>';
            $viewData[$key]['name'] = "<p>" . highlightString($value['name'],$search_value) . '</p>';
            $viewData[$key]['status'] = getCRMWantToCoverStatusLable($value['status']);

            $uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';
            $uiAction .= '<li class="list-inline-item px-2">';
            $uiAction .= '<a onclick="editView(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Edit"><i class="bx bx-edit-alt"></i></a>';
            $uiAction .= '</li>';
            $uiAction .= '</ul>';

            $viewData[$key]['action'] = $uiAction;
        }

        $jsonData = array(
            "draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($recordsTotal), // total number of records
            "recordsFiltered" => intval($recordsFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $viewData, // total data array

        );
        return $jsonData;
    }

    public function saveWantToCover(Request $request)
    {

        $rules = array();
        $rules['data_id'] = 'required';
        $rules['data_name'] = 'required';
        $rules['data_status'] = 'required';
        $customMessage = array();
        $validator = Validator::make($request->all(), $rules, $customMessage);

        if ($validator->fails()) {

            $response = errorRes("The request could not be understood by the server due to malformed syntax");
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors();
        } else {

            $alreadyName = CRMSettingWantToCover::query();
            $alreadyName->where('name', $request->data_name);

            if ($request->data_id != 0) {
                $alreadyName->where('id', '!=', $request->data_id);
            }
            $alreadyName = $alreadyName->first();
            if ($alreadyName) {

                $response = errorRes("already name exits, Try with another name");
            } else {
                if ($request->data_id != 0) {

                    $data = CRMSettingWantToCover::find($request->data_id);
                } else {
                    $data = new CRMSettingWantToCover();
                }
                $data->name = $request->data_name;
                $data->status = $request->data_status;
                $data->save();
                $response = successRes("Successfully saved data");
            }
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function deailWantToCover(Request $request)
    {

        $data = CRMSettingWantToCover::find($request->id);
        if ($data) {

            $response = successRes("Successfully get main master");
            $response['data'] = $data;
        } else {
            $response = errorRes("Invalid id");
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function ajaxSouceType(Request $request)
    {



        $searchColumns = array(

            0 => 'crm_setting_source_type.id',
            1 => 'crm_setting_source_type.name',
        );

        $columns = array(
            0 => 'crm_setting_source_type.id',
            1 => 'crm_setting_source_type.name',
            2 => 'crm_setting_source_type.status',

        );

        $recordsTotal = CRMSettingSourceType::count();
        $recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.

        $query = CRMSettingSourceType::query();
        $query->select($columns);
        $query->limit($request->length);
        $query->offset($request->start);
        $query->orderBy($columns[$request['order'][0]['column']], $request['order'][0]['dir']);
        $isFilterApply = 0;
        $search_value = '';

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

            $viewData[$key]['id'] = "<p>" . highlightString($value['id'],$search_value) . '</p>';
            $viewData[$key]['name'] = "<p>" . highlightString($value['name'],$search_value) . '</p>';
            $viewData[$key]['status'] = getCRMSouceTypeStatusLable($value['status']);

            $uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';
            $uiAction .= '<li class="list-inline-item px-2">';
            $uiAction .= '<a onclick="editView(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Edit"><i class="bx bx-edit-alt"></i></a>';
            $uiAction .= '</li>';
            $uiAction .= '</ul>';

            $viewData[$key]['action'] = $uiAction;
        }

        $jsonData = array(
            "draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($recordsTotal), // total number of records
            "recordsFiltered" => intval($recordsFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $viewData, // total data array

        );
        return $jsonData;
    }

    public function saveSouceType(Request $request)
    {

        $rules = array();
        $rules['data_id'] = 'required';
        $rules['data_name'] = 'required';
        $rules['data_status'] = 'required';
        $customMessage = array();
        $validator = Validator::make($request->all(), $rules, $customMessage);

        if ($validator->fails()) {

            $response = errorRes("The request could not be understood by the server due to malformed syntax");
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors();
        } else {

            $alreadyName = CRMSettingSourceType::query();
            $alreadyName->where('name', $request->data_name);

            if ($request->data_id != 0) {
                $alreadyName->where('id', '!=', $request->data_id);
            }
            $alreadyName = $alreadyName->first();
            if ($alreadyName) {

                $response = errorRes("already name exits, Try with another name");
            } else {
                if ($request->data_id != 0) {

                    $data = CRMSettingSourceType::find($request->data_id);
                } else {
                    $data = new CRMSettingSourceType();
                }
                $data->name = $request->data_name;
                $data->status = $request->data_status;
                $data->save();
                $response = successRes("Successfully saved data");
            }
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function deailSouceType(Request $request)
    {

        $data = CRMSettingSourceType::find($request->id);
        if ($data) {

            $response = successRes("Successfully get main master");
            $response['data'] = $data;
        } else {
            $response = errorRes("Invalid id");
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function ajaxCompetitors(Request $request)
    {



        $searchColumns = array(

            0 => 'crm_setting_competitors.id',
            1 => 'crm_setting_competitors.name',
        );

        $columns = array(
            0 => 'crm_setting_competitors.id',
            1 => 'crm_setting_competitors.name',
            2 => 'crm_setting_competitors.status',

        );

        $recordsTotal = CRMSettingCompetitors::count();
        $recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.

        $query = CRMSettingCompetitors::query();
        $query->select($columns);
        $query->limit($request->length);
        $query->offset($request->start);
        $query->orderBy($columns[$request['order'][0]['column']], $request['order'][0]['dir']);
        $isFilterApply = 0;
        $search_value = '';

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

            $viewData[$key]['id'] = "<p>" . highlightString($value['id'],$search_value) . '</p>';
            $viewData[$key]['name'] = "<p>" . highlightString($value['name'],$search_value) . '</p>';
            $viewData[$key]['status'] = getCRMCompetitorsStatusLable($value['status']);

            $uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';
            $uiAction .= '<li class="list-inline-item px-2">';
            $uiAction .= '<a onclick="editView(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Edit"><i class="bx bx-edit-alt"></i></a>';
            $uiAction .= '</li>';
            $uiAction .= '</ul>';

            $viewData[$key]['action'] = $uiAction;
        }

        $jsonData = array(
            "draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($recordsTotal), // total number of records
            "recordsFiltered" => intval($recordsFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $viewData, // total data array

        );
        return $jsonData;
    }

    public function saveCompetitors(Request $request)
    {

        $rules = array();
        $rules['data_id'] = 'required';
        $rules['data_name'] = 'required';
        $rules['data_status'] = 'required';
        $customMessage = array();
        $validator = Validator::make($request->all(), $rules, $customMessage);

        if ($validator->fails()) {

            $response = errorRes("The request could not be understood by the server due to malformed syntax");
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors();
        } else {

            $alreadyName = CRMSettingCompetitors::query();
            $alreadyName->where('name', $request->data_name);

            if ($request->data_id != 0) {
                $alreadyName->where('id', '!=', $request->data_id);
            }
            $alreadyName = $alreadyName->first();
            if ($alreadyName) {

                $response = errorRes("already name exits, Try with another name");
            } else {
                if ($request->data_id != 0) {

                    $data = CRMSettingCompetitors::find($request->data_id);
                } else {
                    $data = new CRMSettingCompetitors();
                }
                $data->name = $request->data_name;
                $data->status = $request->data_status;
                $data->save();
                $response = successRes("Successfully saved data");
            }
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function deailCompetitors(Request $request)
    {

        $data = CRMSettingCompetitors::find($request->id);
        if ($data) {

            $response = successRes("Successfully get main master");
            $response['data'] = $data;
        } else {
            $response = errorRes("Invalid id");
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function ajaxSource(Request $request)
    {



        $searchColumns = array(

            0 => 'crm_setting_source.id',
            1 => 'crm_setting_source.name',
        );

        $columns = array(
            0 => 'crm_setting_source.id',
            1 => 'crm_setting_source.name',
            2 => 'crm_setting_source_type.name as source_type',
            3 => 'crm_setting_source.status',

        );

        $recordsTotal = CRMSettingSource::count();
        $recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.

        $query = CRMSettingSource::query();
        $query->leftJoin('crm_setting_source_type', 'crm_setting_source_type.id', '=', 'crm_setting_source.source_type_id');
        $query->select($columns);
        $query->limit($request->length);
        $query->offset($request->start);
        $query->orderBy($columns[$request['order'][0]['column']], $request['order'][0]['dir']);
        $isFilterApply = 0;
        $search_value = '';

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

            $viewData[$key]['id'] = "<p>" . highlightString($value['id'],$search_value) . '</p>';
            $viewData[$key]['name'] = "<p>" . highlightString($value['name'],$search_value) . '</p>';
            $viewData[$key]['source_type'] = "<p>" . $value['source_type'] . '</p>';
            $viewData[$key]['status'] = getCRMSourceStatusLable($value['status']);

            $uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';
            $uiAction .= '<li class="list-inline-item px-2">';
            $uiAction .= '<a onclick="editView(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Edit"><i class="bx bx-edit-alt"></i></a>';
            $uiAction .= '</li>';
            $uiAction .= '</ul>';

            $viewData[$key]['action'] = $uiAction;
        }

        $jsonData = array(
            "draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($recordsTotal), // total number of records
            "recordsFiltered" => intval($recordsFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $viewData, // total data array

        );
        return $jsonData;
    }

    function searchSourceType(Request $request)
    {

        $searchKeyword = isset($request->q) ? $request->q : "";

        $CRMSettingSourceType = CRMSettingSourceType::select('id', 'name as text');;
        $CRMSettingSourceType->where('crm_setting_source_type.name', 'like', "%" . $searchKeyword . "%");
        $CRMSettingSourceType->limit(5);
        $CRMSettingSourceType = $CRMSettingSourceType->get();

        $response = array();
        $response['results'] = $CRMSettingSourceType;
        $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function saveSource(Request $request)
    {

        $rules = array();
        $rules['data_id'] = 'required';
        $rules['data_name'] = 'required';
        $rules['data_status'] = 'required';
        $rules['data_source_type'] = 'required';


        $customMessage = array();
        $validator = Validator::make($request->all(), $rules, $customMessage);

        if ($validator->fails()) {

            $response = errorRes("The request could not be understood by the server due to malformed syntax");
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors();
        } else {

            $alreadyName = CRMSettingSource::query();
            $alreadyName->where('name', $request->data_name);
            $alreadyName->where('source_type_id', $request->data_source_type);

            if ($request->data_id != 0) {
                $alreadyName->where('id', '!=', $request->data_id);
            }
            $alreadyName = $alreadyName->first();
            if ($alreadyName) {

                $response = errorRes("already name exits, Try with another name");
            } else {
                if ($request->data_id != 0) {

                    $data = CRMSettingSource::find($request->data_id);
                } else {
                    $data = new CRMSettingSource();
                }
                $data->name = $request->data_name;
                $data->status = $request->data_status;
                $data->source_type_id = $request->data_source_type;
                $data->save();
                $response = successRes("Successfully saved data");
            }
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function deailSource(Request $request)
    {

        $data = CRMSettingSource::find($request->id);
        if ($data) {

            $data = json_decode(json_encode($data), true);

            $CRMSettingSourceType = CRMSettingSourceType::select('id', 'name as text');;
            $CRMSettingSourceType->where('crm_setting_source_type.id',  $data['source_type_id']);
            $CRMSettingSourceType->limit(1);
            $CRMSettingSourceType = $CRMSettingSourceType->first();

            $data['source_type'] = $CRMSettingSourceType;
            $response = successRes("Successfully get detail");
            $response['data'] = $data;
        } else {
            $response = errorRes("Invalid id");
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function searchStatus(Request $request)
    {

        $LeadStatus = getLeadStatus();


        foreach ($LeadStatus as $key => $value) {
            // $LeadStatus[$key]['id'] = $value['id'] . "";
            $LeadStatus[$key]['text'] = $value['name'];
        }

        $LeadStatus = array_values($LeadStatus);


        $response = array();
        $response['results'] = $LeadStatus;
        $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function saveSubStatus(Request $request)
    {

        $rules = array();
        $rules['data_id'] = 'required';
        $rules['data_name'] = 'required';
        $rules['data_status'] = 'required';
        $rules['data_lead_status'] = 'required';


        $customMessage = array();
        $validator = Validator::make($request->all(), $rules, $customMessage);

        if ($validator->fails()) {

            $response = errorRes("The request could not be understood by the server due to malformed syntax");
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors();
        } else {

            $alreadyName = CRMSettingSubStatus::query();
            $alreadyName->where('name', $request->data_name);
            $alreadyName->where('lead_status', $request->data_lead_status);

            if ($request->data_id != 0) {
                $alreadyName->where('id', '!=', $request->data_id);
            }
            $alreadyName = $alreadyName->first();
            if ($alreadyName) {

                $response = errorRes("already name exits, Try with another name");
            } else {
                if ($request->data_id != 0) {

                    $data = CRMSettingSubStatus::find($request->data_id);
                } else {
                    $data = new CRMSettingSubStatus();
                }
                $data->name = $request->data_name;
                $data->status = $request->data_status;
                $data->lead_status = $request->data_lead_status;
                $data->save();
                $response = successRes("Successfully saved data");
            }
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function ajaxSubStatus(Request $request)
    {

        $LeadStatus = getLeadStatus();

        $searchColumns = array(

            0 => 'crm_setting_sub_status.id',
            1 => 'crm_setting_sub_status.name',
        );

        $columns = array(
            0 => 'crm_setting_sub_status.id',
            1 => 'crm_setting_sub_status.name',
            2 => 'crm_setting_sub_status.lead_status',
            3 => 'crm_setting_sub_status.status',

        );

        $recordsTotal = CRMSettingSubStatus::count();
        $recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.

        $query = CRMSettingSubStatus::query();
        //$query->leftJoin('crm_setting_source_type', 'crm_setting_source_type.id', '=', 'crm_setting_source.source_type_id');
        $query->select($columns);
        $query->limit($request->length);
        $query->offset($request->start);
        $query->orderBy($columns[$request['order'][0]['column']], $request['order'][0]['dir']);
        $isFilterApply = 0;
        $search_value = '';

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

            $viewData[$key]['id'] = "<p>" . highlightString($value['id'],$search_value) . '</p>';
            $viewData[$key]['name'] = "<p>" . highlightString($value['name'],$search_value) . '</p>';
            $viewData[$key]['lead_status'] = "<p>" . $LeadStatus[$value['lead_status']]['name'] . '</p>';
            $viewData[$key]['status'] = getCRMSSubStatusLable($value['status']);

            $uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';
            $uiAction .= '<li class="list-inline-item px-2">';
            $uiAction .= '<a onclick="editView(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Edit"><i class="bx bx-edit-alt"></i></a>';
            $uiAction .= '</li>';
            $uiAction .= '</ul>';

            $viewData[$key]['action'] = $uiAction;
        }

        $jsonData = array(
            "draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($recordsTotal), // total number of records
            "recordsFiltered" => intval($recordsFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $viewData, // total data array

        );
        return $jsonData;
    }

    function deailSubStatus(Request $request)
    {

        $data = CRMSettingSubStatus::find($request->id);
        if ($data) {

            $data = json_decode(json_encode($data), true);

            $LeadStatus = getLeadStatus();
            $subStatus = array();
            $subStatus['id'] = $data['lead_status'];
            $subStatus['text'] = $LeadStatus[$data['lead_status']]['name'];
            $data['lead_status'] = $subStatus;
            $response = successRes("Successfully get detail");
            $response['data'] = $data;
        } else {
            $response = errorRes("Invalid id");
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function ajaxContactTag(Request $request)
    {



        $searchColumns = array(

            0 => 'crm_setting_contact_tag.id',
            1 => 'crm_setting_contact_tag.name',
        );

        $columns = array(
            0 => 'crm_setting_contact_tag.id',
            1 => 'crm_setting_contact_tag.name',
            2 => 'crm_setting_contact_tag.status',

        );

        $recordsTotal = CRMSettingContactTag::count();
        $recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.

        $query = CRMSettingContactTag::query();
        $query->select($columns);
        $query->limit($request->length);
        $query->offset($request->start);
        $query->orderBy($columns[$request['order'][0]['column']], $request['order'][0]['dir']);
        $isFilterApply = 0;
        $search_value = '';

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

            $viewData[$key]['id'] = "<p>" . highlightString($value['id'],$search_value) . '</p>';
            $viewData[$key]['name'] = "<p>" . highlightString($value['name'],$search_value) . '</p>';
            $viewData[$key]['status'] = getCRMContactTagLable($value['status']);

            $uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';
            $uiAction .= '<li class="list-inline-item px-2">';
            $uiAction .= '<a onclick="editView(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Edit"><i class="bx bx-edit-alt"></i></a>';
            $uiAction .= '</li>';
            $uiAction .= '</ul>';

            $viewData[$key]['action'] = $uiAction;
        }

        $jsonData = array(
            "draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($recordsTotal), // total number of records
            "recordsFiltered" => intval($recordsFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $viewData, // total data array

        );
        return $jsonData;
    }

    public function saveContactTag(Request $request)
    {

        $rules = array();
        $rules['data_id'] = 'required';
        $rules['data_name'] = 'required';
        $rules['data_status'] = 'required';
        $customMessage = array();
        $validator = Validator::make($request->all(), $rules, $customMessage);

        if ($validator->fails()) {

            $response = errorRes("The request could not be understood by the server due to malformed syntax");
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors();
        } else {

            $alreadyName = CRMSettingContactTag::query();
            $alreadyName->where('name', $request->data_name);

            if ($request->data_id != 0) {
                $alreadyName->where('id', '!=', $request->data_id);
            }
            $alreadyName = $alreadyName->first();
            if ($alreadyName) {

                $response = errorRes("already name exits, Try with another name");
            } else {
                if ($request->data_id != 0) {

                    $data = CRMSettingContactTag::find($request->data_id);
                } else {
                    $data = new CRMSettingContactTag();
                }
                $data->name = $request->data_name;
                $data->status = $request->data_status;
                $data->save();
                $response = successRes("Successfully saved data");
            }
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function deailContactTag(Request $request)
    {

        $data = CRMSettingContactTag::find($request->id);
        if ($data) {

            $response = successRes("Successfully get main master");
            $response['data'] = $data;
        } else {
            $response = errorRes("Invalid id");
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }


    public function ajaxFileTag(Request $request)
    {



        $searchColumns = array(

            0 => 'crm_setting_file_tag.id',
            1 => 'crm_setting_file_tag.name',
        );

        $columns = array(
            0 => 'crm_setting_file_tag.id',
            1 => 'crm_setting_file_tag.name',
            2 => 'crm_setting_file_tag.status',

        );

        $recordsTotal = CRMSettingFileTag::count();
        $recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.

        $query = CRMSettingFileTag::query();
        $query->select($columns);
        $query->limit($request->length);
        $query->offset($request->start);
        $query->orderBy($columns[$request['order'][0]['column']], $request['order'][0]['dir']);
        $isFilterApply = 0;
        $search_value = '';

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

            $viewData[$key]['id'] = "<p>" . highlightString($value['id'],$search_value) . '</p>';
            $viewData[$key]['name'] = "<p>" . highlightString($value['name'],$search_value) . '</p>';
            $viewData[$key]['status'] = getCRMContactTagLable($value['status']);

            $uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';
            $uiAction .= '<li class="list-inline-item px-2">';
            $uiAction .= '<a onclick="editView(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Edit"><i class="bx bx-edit-alt"></i></a>';
            $uiAction .= '</li>';
            $uiAction .= '</ul>';

            $viewData[$key]['action'] = $uiAction;
        }

        $jsonData = array(
            "draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($recordsTotal), // total number of records
            "recordsFiltered" => intval($recordsFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $viewData, // total data array

        );
        return $jsonData;
    }

    public function saveFileTag(Request $request)
    {

        $rules = array();
        $rules['data_id'] = 'required';
        $rules['data_name'] = 'required';
        $rules['data_status'] = 'required';
        $customMessage = array();
        $validator = Validator::make($request->all(), $rules, $customMessage);

        if ($validator->fails()) {

            $response = errorRes("The request could not be understood by the server due to malformed syntax");
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors();
        } else {

            $alreadyName = CRMSettingFileTag::query();
            $alreadyName->where('name', $request->data_name);

            if ($request->data_id != 0) {
                $alreadyName->where('id', '!=', $request->data_id);
            }
            $alreadyName = $alreadyName->first();
            if ($alreadyName) {

                $response = errorRes("already name exits, Try with another name");
            } else {
                if ($request->data_id != 0) {

                    $data = CRMSettingFileTag::find($request->data_id);
                } else {
                    $data = new CRMSettingFileTag();
                }
                $data->name = $request->data_name;
                $data->status = $request->data_status;
                $data->save();
                $response = successRes("Successfully saved data");
            }
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function deailFileTag(Request $request)
    {

        $data = CRMSettingFileTag::find($request->id);
        if ($data) {

            $response = successRes("Successfully get main master");
            $response['data'] = $data;
        } else {
            $response = errorRes("Invalid id");
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function ajaxMeetingTitle(Request $request)
    {



        $searchColumns = array(

            0 => 'crm_setting_meeting_title.id',
            1 => 'crm_setting_meeting_title.name',
        );

        $columns = array(
            0 => 'crm_setting_meeting_title.id',
            1 => 'crm_setting_meeting_title.name',
            2 => 'crm_setting_meeting_title.status',

        );

        $recordsTotal = CRMSettingMeetingTitle::count();
        $recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.

        $query = CRMSettingMeetingTitle::query();
        $query->select($columns);
        $query->limit($request->length);
        $query->offset($request->start);
        $query->orderBy($columns[$request['order'][0]['column']], $request['order'][0]['dir']);
        $isFilterApply = 0;
        $search_value = '';

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

            $viewData[$key]['id'] = "<p>" . highlightString($value['id'],$search_value) . '</p>';
            $viewData[$key]['name'] = "<p>" . highlightString($value['name'],$search_value) . '</p>';
            $viewData[$key]['status'] = getCRMWantToCoverStatusLable($value['status']);

            $uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';
            $uiAction .= '<li class="list-inline-item px-2">';
            $uiAction .= '<a onclick="editView(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Edit"><i class="bx bx-edit-alt"></i></a>';
            $uiAction .= '</li>';
            $uiAction .= '</ul>';

            $viewData[$key]['action'] = $uiAction;
        }

        $jsonData = array(
            "draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($recordsTotal), // total number of records
            "recordsFiltered" => intval($recordsFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $viewData, // total data array

        );
        return $jsonData;
    }

    public function saveMeetingTitle(Request $request)
    {

        $rules = array();
        $rules['data_id'] = 'required';
        $rules['data_name'] = 'required';
        $rules['data_status'] = 'required';
        $customMessage = array();
        $validator = Validator::make($request->all(), $rules, $customMessage);

        if ($validator->fails()) {

            $response = errorRes("The request could not be understood by the server due to malformed syntax");
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors();
        } else {

            $alreadyName = CRMSettingMeetingTitle::query();
            $alreadyName->where('name', $request->data_name);

            if ($request->data_id != 0) {
                $alreadyName->where('id', '!=', $request->data_id);
            }
            $alreadyName = $alreadyName->first();
            if ($alreadyName) {

                $response = errorRes("already name exits, Try with another name");
            } else {
                if ($request->data_id != 0) {

                    $data = CRMSettingMeetingTitle::find($request->data_id);
                } else {
                    $data = new CRMSettingMeetingTitle();
                }
                $data->name = $request->data_name;
                $data->status = $request->data_status;
                $data->save();
                $response = successRes("Successfully saved data");
            }
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function deailMeetingTitle(Request $request)
    {

        $data = CRMSettingMeetingTitle::find($request->id);
        if ($data) {

            $response = successRes("Successfully get main master");
            $response['data'] = $data;
        } else {
            $response = errorRes("Invalid id");
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function ajaxScheduleType(Request $request)
    {



        $searchColumns = array(

            0 => 'crm_setting_call_type.id',
            1 => 'crm_setting_call_type.name',
        );

        $columns = array(
            0 => 'crm_setting_call_type.id',
            1 => 'crm_setting_call_type.name',
            2 => 'crm_setting_call_type.status',

        );

        $recordsTotal = CRMSettingCallType::count();
        $recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.

        $query = CRMSettingCallType::query();
        $query->select($columns);
        $query->limit($request->length);
        $query->offset($request->start);
        $query->orderBy($columns[$request['order'][0]['column']], $request['order'][0]['dir']);
        $isFilterApply = 0;
        $search_value = '';

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

            $viewData[$key]['id'] = "<p>" . highlightString($value['id'],$search_value) . '</p>';
            $viewData[$key]['name'] = "<p>" . highlightString($value['name'],$search_value) . '</p>';
            $viewData[$key]['status'] = getCRMWantToCoverStatusLable($value['status']);

            $uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';
            $uiAction .= '<li class="list-inline-item px-2">';
            $uiAction .= '<a onclick="editView(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Edit"><i class="bx bx-edit-alt"></i></a>';
            $uiAction .= '</li>';
            $uiAction .= '</ul>';

            $viewData[$key]['action'] = $uiAction;
        }

        $jsonData = array(
            "draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($recordsTotal), // total number of records
            "recordsFiltered" => intval($recordsFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $viewData, // total data array

        );
        return $jsonData;
    }

    public function saveScheduleType(Request $request)
    {

        $rules = array();
        $rules['data_id'] = 'required';
        $rules['data_name'] = 'required';
        $rules['data_status'] = 'required';
        $customMessage = array();
        $validator = Validator::make($request->all(), $rules, $customMessage);

        if ($validator->fails()) {

            $response = errorRes("The request could not be understood by the server due to malformed syntax");
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors();
        } else {

            $alreadyName = CRMSettingCallType::query();
            $alreadyName->where('name', $request->data_name);

            if ($request->data_id != 0) {
                $alreadyName->where('id', '!=', $request->data_id);
            }
            $alreadyName = $alreadyName->first();
            if ($alreadyName) {

                $response = errorRes("already name exits, Try with another name");
            } else {
                if ($request->data_id != 0) {

                    $data = CRMSettingCallType::find($request->data_id);
                } else {
                    $data = new CRMSettingCallType();
                }
                $data->name = $request->data_name;
                $data->status = $request->data_status;
                $data->save();
                $response = successRes("Successfully saved data");
            }
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function deailScheduleType(Request $request)
    {

        $data = CRMSettingCallType::find($request->id);
        if ($data) {

            $response = successRes("Successfully get main master");
            $response['data'] = $data;
        } else {
            $response = errorRes("Invalid id");
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function ajaxScheduleMeetingType(Request $request)
    {



        $searchColumns = array(

            0 => 'crm_setting_meeting_type.id',
            1 => 'crm_setting_meeting_type.name',
        );

        $columns = array(
            0 => 'crm_setting_meeting_type.id',
            1 => 'crm_setting_meeting_type.name',
            2 => 'crm_setting_meeting_type.status',

        );

        $recordsTotal = CRMSettingMeetingType::count();
        $recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.

        $query = CRMSettingMeetingType::query();
        $query->select($columns);
        $query->limit($request->length);
        $query->offset($request->start);
        $query->orderBy($columns[$request['order'][0]['column']], $request['order'][0]['dir']);
        $isFilterApply = 0;
        $search_value = '';

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

            $viewData[$key]['id'] = "<p>" . highlightString($value['id'],$search_value) . '</p>';
            $viewData[$key]['name'] = "<p>" . highlightString($value['name'],$search_value) . '</p>';
            $viewData[$key]['status'] = getCRMWantToCoverStatusLable($value['status']);

            $uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';
            $uiAction .= '<li class="list-inline-item px-2">';
            $uiAction .= '<a onclick="editView(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Edit"><i class="bx bx-edit-alt"></i></a>';
            $uiAction .= '</li>';
            $uiAction .= '</ul>';

            $viewData[$key]['action'] = $uiAction;
        }

        $jsonData = array(
            "draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($recordsTotal), // total number of records
            "recordsFiltered" => intval($recordsFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $viewData, // total data array

        );
        return $jsonData;
    }

    public function saveScheduleMeetingType(Request $request)
    {

        $rules = array();
        $rules['data_id'] = 'required';
        $rules['data_name'] = 'required';
        $rules['data_status'] = 'required';
        $customMessage = array();
        $validator = Validator::make($request->all(), $rules, $customMessage);

        if ($validator->fails()) {

            $response = errorRes("The request could not be understood by the server due to malformed syntax");
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors();
        } else {

            $alreadyName = CRMSettingMeetingType::query();
            $alreadyName->where('name', $request->data_name);

            if ($request->data_id != 0) {
                $alreadyName->where('id', '!=', $request->data_id);
            }
            $alreadyName = $alreadyName->first();
            if ($alreadyName) {

                $response = errorRes("already name exits, Try with another name");
            } else {
                if ($request->data_id != 0) {

                    $data = CRMSettingMeetingType::find($request->data_id);
                } else {
                    $data = new CRMSettingMeetingType();
                }
                $data->name = $request->data_name;
                $data->status = $request->data_status;
                $data->save();
                $response = successRes("Successfully saved data");
            }
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function deailScheduleMeetingType(Request $request)
    {

        $data = CRMSettingMeetingType::find($request->id);
        if ($data) {

            $response = successRes("Successfully get main master");
            $response['data'] = $data;
        } else {
            $response = errorRes("Invalid id");
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function ajaxCallOutcomeType(Request $request)
    {



        $searchColumns = array(

            0 => 'crm_setting_call_outcome_type.id',
            1 => 'crm_setting_call_outcome_type.name',
        );

        $columns = array(
            0 => 'crm_setting_call_outcome_type.id',
            1 => 'crm_setting_call_outcome_type.name',
            2 => 'crm_setting_call_outcome_type.status',

        );

        $recordsTotal = CRMSettingCallOutcomeType::count();
        $recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.

        $query = CRMSettingCallOutcomeType::query();
        $query->select($columns);
        $query->limit($request->length);
        $query->offset($request->start);
        $query->orderBy($columns[$request['order'][0]['column']], $request['order'][0]['dir']);
        $isFilterApply = 0;
        $search_value = '';

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

            $viewData[$key]['id'] = "<p>" . highlightString($value['id'],$search_value) . '</p>';
            $viewData[$key]['name'] = "<p>" . highlightString($value['name'],$search_value) . '</p>';
            $viewData[$key]['status'] = getCRMWantToCoverStatusLable($value['status']);

            $uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';
            $uiAction .= '<li class="list-inline-item px-2">';
            $uiAction .= '<a onclick="editView(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Edit"><i class="bx bx-edit-alt"></i></a>';
            $uiAction .= '</li>';
            $uiAction .= '</ul>';

            $viewData[$key]['action'] = $uiAction;
        }

        $jsonData = array(
            "draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($recordsTotal), // total number of records
            "recordsFiltered" => intval($recordsFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $viewData, // total data array

        );
        return $jsonData;
    }

    public function saveCallOutcomeType(Request $request)
    {

        $rules = array();
        $rules['data_id'] = 'required';
        $rules['data_name'] = 'required';
        $rules['data_status'] = 'required';
        $customMessage = array();
        $validator = Validator::make($request->all(), $rules, $customMessage);

        if ($validator->fails()) {

            $response = errorRes("The request could not be understood by the server due to malformed syntax");
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors();
        } else {

            $alreadyName = CRMSettingCallOutcomeType::query();
            $alreadyName->where('name', $request->data_name);

            if ($request->data_id != 0) {
                $alreadyName->where('id', '!=', $request->data_id);
            }
            $alreadyName = $alreadyName->first();
            if ($alreadyName) {

                $response = errorRes("already name exits, Try with another name");
            } else {
                if ($request->data_id != 0) {

                    $data = CRMSettingCallOutcomeType::find($request->data_id);
                } else {
                    $data = new CRMSettingCallOutcomeType();
                }
                $data->name = $request->data_name;
                $data->status = $request->data_status;
                if(isset($request->data_re_schedule)){
                    $data->is_reschedule = $request->data_re_schedule;
                }
                $data->save();
                $response = successRes("Successfully saved data");
            }
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function detailCallOutcomeType(Request $request)
    {

        $data = CRMSettingCallOutcomeType::find($request->id);
        if ($data) {

            $response = successRes("Successfully get main master");
            $response['data'] = $data;
        } else {
            $response = errorRes("Invalid id");
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }


    public function ajaxMeetingOutcomeType(Request $request)
    {



        $searchColumns = array(

            0 => 'crm_setting_meeting_outcome_type.id',
            1 => 'crm_setting_meeting_outcome_type.name',
        );

        $columns = array(
            0 => 'crm_setting_meeting_outcome_type.id',
            1 => 'crm_setting_meeting_outcome_type.name',
            2 => 'crm_setting_meeting_outcome_type.status',

        );

        $recordsTotal = CRMSettingMeetingOutcomeType::count();
        $recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.

        $query = CRMSettingMeetingOutcomeType::query();
        $query->select($columns);
        $query->limit($request->length);
        $query->offset($request->start);
        $query->orderBy($columns[$request['order'][0]['column']], $request['order'][0]['dir']);
        $isFilterApply = 0;
        $search_value = '';

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

            $viewData[$key]['id'] = "<p>" . highlightString($value['id'],$search_value) . '</p>';
            $viewData[$key]['name'] = "<p>" . highlightString($value['name'],$search_value) . '</p>';
            $viewData[$key]['status'] = getCRMWantToCoverStatusLable($value['status']);

            $uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';
            $uiAction .= '<li class="list-inline-item px-2">';
            $uiAction .= '<a onclick="editView(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Edit"><i class="bx bx-edit-alt"></i></a>';
            $uiAction .= '</li>';
            $uiAction .= '</ul>';

            $viewData[$key]['action'] = $uiAction;
        }

        $jsonData = array(
            "draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($recordsTotal), // total number of records
            "recordsFiltered" => intval($recordsFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $viewData, // total data array

        );
        return $jsonData;
    }

    public function saveMeetingOutcomeType(Request $request)
    {

        $rules = array();
        $rules['data_id'] = 'required';
        $rules['data_name'] = 'required';
        $rules['data_status'] = 'required';
        $customMessage = array();
        $validator = Validator::make($request->all(), $rules, $customMessage);

        if ($validator->fails()) {

            $response = errorRes("The request could not be understood by the server due to malformed syntax");
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors();
        } else {

            $alreadyName = CRMSettingMeetingOutcomeType::query();
            $alreadyName->where('name', $request->data_name);

            if ($request->data_id != 0) {
                $alreadyName->where('id', '!=', $request->data_id);
            }
            $alreadyName = $alreadyName->first();
            if ($alreadyName) {

                $response = errorRes("already name exits, Try with another name");
            } else {
                if ($request->data_id != 0) {

                    $data = CRMSettingMeetingOutcomeType::find($request->data_id);
                } else {
                    $data = new CRMSettingMeetingOutcomeType();
                }
                $data->name = $request->data_name;
                $data->status = $request->data_status;
                $data->save();
                $response = successRes("Successfully saved data");
            }
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function detailMeetingOutcomeType(Request $request)
    {

        $data = CRMSettingMeetingOutcomeType::find($request->id);
        if ($data) {

            $response = successRes("Successfully get main master");
            $response['data'] = $data;
        } else {
            $response = errorRes("Invalid id");
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }


    public function ajaxTaskOutcomeType(Request $request)
    {

        $searchColumns = array(

            0 => 'crm_setting_task_outcome_type.id',
            1 => 'crm_setting_task_outcome_type.name',
        );

        $columns = array(
            0 => 'crm_setting_task_outcome_type.id',
            1 => 'crm_setting_task_outcome_type.name',
            2 => 'crm_setting_task_outcome_type.status',

        );

        $recordsTotal = CRMSettingTaskOutcomeType::count();
        $recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.

        $query = CRMSettingTaskOutcomeType::query();
        $query->select($columns);
        $query->limit($request->length);
        $query->offset($request->start);
        $query->orderBy($columns[$request['order'][0]['column']], $request['order'][0]['dir']);
        $isFilterApply = 0;
        $search_value = '';

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

            $viewData[$key]['id'] = "<p>" . highlightString($value['id'],$search_value) . '</p>';
            $viewData[$key]['name'] = "<p>" . highlightString($value['name'],$search_value) . '</p>';
            $viewData[$key]['status'] = getCRMWantToCoverStatusLable($value['status']);

            $uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';
            $uiAction .= '<li class="list-inline-item px-2">';
            $uiAction .= '<a onclick="editView(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Edit"><i class="bx bx-edit-alt"></i></a>';
            $uiAction .= '</li>';
            $uiAction .= '</ul>';

            $viewData[$key]['action'] = $uiAction;
        }

        $jsonData = array(
            "draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($recordsTotal), // total number of records
            "recordsFiltered" => intval($recordsFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $viewData, // total data array

        );
        return $jsonData;
    }

    public function saveTaskOutcomeType(Request $request)
    {

        $rules = array();
        $rules['data_id'] = 'required';
        $rules['data_name'] = 'required';
        $rules['data_status'] = 'required';
        $customMessage = array();
        $validator = Validator::make($request->all(), $rules, $customMessage);

        if ($validator->fails()) {

            $response = errorRes("The request could not be understood by the server due to malformed syntax");
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors();
        } else {

            $alreadyName = CRMSettingTaskOutcomeType::query();
            $alreadyName->where('name', $request->data_name);

            if ($request->data_id != 0) {
                $alreadyName->where('id', '!=', $request->data_id);
            }
            $alreadyName = $alreadyName->first();
            if ($alreadyName) {

                $response = errorRes("already name exits, Try with another name");
            } else {
                if ($request->data_id != 0) {

                    $data = CRMSettingTaskOutcomeType::find($request->data_id);
                } else {
                    $data = new CRMSettingTaskOutcomeType();
                }
                $data->name = $request->data_name;
                $data->status = $request->data_status;
                if(isset($request->data_re_schedule) && $request->data_re_schedule != 0){
                    $data->is_reschedule = 1;
                }
                $data->save();
                $response = successRes("Successfully saved data");
            }
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function detailTaskOutcomeType(Request $request)
    {

        $data = CRMSettingTaskOutcomeType::find($request->id);
        if ($data) {

            $response = successRes("Successfully get main master");
            $response['data'] = $data;
        } else {
            $response = errorRes("Invalid id");
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function saveLeadDealTag(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tag_master_name' => ['required'],
            'tag_master_code' => ['required'],
            'tag_master_status' => ['required'],
        ]);
        if ($validator->fails()) {

            $response = array();
            $response['status'] = 0;
            $response['msg'] = "The request could not be understood by the server due to malformed syntax";
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors();

            return response()->json($response)->header('Content-Type', 'application/json');
        } else {

            $alreadyName = Tags::query();

            if ($request->tag_master_id != 0) {

                $alreadyName->where('tagname', $request->tag_master_name);
                $alreadyName->where('id', '!=', $request->tag_master_id);
            } else {
                $alreadyName->where('tagname', $request->tag_master_name);
            }

            $alreadyName = $alreadyName->first();
            if ($alreadyName) {
                $response = errorRes("already name exits, Try with another name");
            } else {
                if ($request->tag_master_id != 0) {
                    $MainMaster = Tags::find($request->tag_master_id);
                    $MainMaster->updateby = Auth::user()->id;
                    $MainMaster->updateip = $request->ip();
                } else {
                    $MainMaster = new Tags();
                    $MainMaster->entryby = Auth::user()->id;
                    $MainMaster->entryip = $request->ip();
                }

                $MainMaster->tagname = $request->tag_master_name;
                $MainMaster->shortname = $request->tag_master_code;
                $MainMaster->remark = isset($request->tag_master_remark) ? $request->tag_master_remark : '';
                $MainMaster->isactive = $request->tag_master_status;
                $MainMaster->tag_type = 201;


                $MainMaster->save();
                if ($MainMaster) {

                    if ($request->tag_master_id != 0) {

                        $response = successRes("Successfully saved lead deal tag master");



                        $debugLog = array();
                        $debugLog['name'] = "lead-deal-tag-master-edit";
                        $debugLog['description'] = "lead deal tag master #" . $MainMaster->id . "(" . $MainMaster->tagname . ")" . " has been updated ";
                        saveDebugLog($debugLog);
                    } else {
                        $response = successRes("Successfully added lead deal tag master");

                        $debugLog = array();
                        $debugLog['name'] = "lead-deal-tag-master-add";
                        $debugLog['description'] = "lead deal tag master #" . $MainMaster->id . "(" . $MainMaster->tagname . ") has been added ";
                        saveDebugLog($debugLog);
                    }
                }
            }

            return response()->json($response)->header('Content-Type', 'application/json');
        }
    }

    public function ajaxLEadDealTag(Request $request)
    {
        //DB::enableQueryLog();

        $searchColumns = array(
            0 => 'tag_master.id',
            1 => 'tag_master.tagname',
        );

        $columns = array(
            0 => 'tag_master.id',
            1 => 'tag_master.tagname',
            2 => 'tag_master.shortname',
            3 => 'tag_master.isactive',
        );

        $recordsTotal = Tags::query()->where('tag_type', 201)->count();
        $recordsFiltered = $recordsTotal;

        $query = Tags::query();
        $query->select($columns);
        $query->limit($request->length);
        $query->where('tag_type', 201);
        $query->offset($request->start);
        $query->orderBy($columns[$request['order'][0]['column']], $request['order'][0]['dir']);
        $isFilterApply = 0;
        $search_value = '';

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
        // echo "<pre>";
        // print_r(DB::getQueryLog());
        // die;

        $data = json_decode(json_encode($data), true);

        if ($isFilterApply == 1) {
            $recordsFiltered = count($data);
        }

        foreach ($data as $key => $value) {

            $data[$key]['tagname'] = "<p>" . highlightString($data[$key]['tagname'],$search_value) . '</p>';
            $data[$key]['shortname'] = "<p>" . highlightString($data[$key]['shortname'],$search_value) . '</p>';

            $data[$key]['isactive'] = getMainMasterStatusLable($value['isactive']);

            $uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';

            $uiAction .= '<li class="list-inline-item px-2">';
            $uiAction .= '<a onclick="editView(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Edit"><i class="bx bx-edit-alt"></i></a>';
            $uiAction .= '</li>';

            $uiAction .= '<li class="list-inline-item px-2">';
            $uiAction .= '<a onclick="deleteWarning(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Delete"><i class="bx bx-trash-alt"></i></a>';
            $uiAction .= '</li>';

            $uiAction .= '</ul>';

            $data[$key]['action'] = $uiAction;
        }

        $jsonData = array(
            "draw" => intval($request['draw']),
            // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($recordsTotal),
            // total number of records
            "recordsFiltered" => intval($recordsFiltered),
            // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $data, // total data array
        );

        return $jsonData;
    }

    public function detailLeadDealTag(Request $request)
    {

        $MainMaster = Tags::query()->where('id', $request->id)->where('tag_type', 201)->first();
        if ($MainMaster) {
            $response = successRes("Successfully get lead deal tag master");
            $response['data'] = $MainMaster;
        } else {
            $response = errorRes("Invalid id");
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function saveUserTag(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tag_master_name' => ['required'],
            'tag_master_code' => ['required'],
            'tag_master_status' => ['required'],
        ]);
        if ($validator->fails()) {

            $response = array();
            $response['status'] = 0;
            $response['msg'] = "The request could not be understood by the server due to malformed syntax";
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors();

            return response()->json($response)->header('Content-Type', 'application/json');
        } else {

            $alreadyName = Tags::query();

            if ($request->tag_master_id != 0) {

                $alreadyName->where('tagname', $request->tag_master_name);
                $alreadyName->where('id', '!=', $request->tag_master_id);
            } else {
                $alreadyName->where('tagname', $request->tag_master_name);
            }

            $alreadyName = $alreadyName->first();
            if ($alreadyName) {
                $response = errorRes("already name exits, Try with another name");
            } else {
                if ($request->tag_master_id != 0) {
                    $MainMaster = Tags::find($request->tag_master_id);
                    $MainMaster->updateby = Auth::user()->id;
                    $MainMaster->updateip = $request->ip();
                } else {
                    $MainMaster = new Tags();
                    $MainMaster->entryby = Auth::user()->id;
                    $MainMaster->entryip = $request->ip();
                }

                $MainMaster->tagname = $request->tag_master_name;
                $MainMaster->shortname = $request->tag_master_code;
                $MainMaster->remark = isset($request->tag_master_remark) ? $request->tag_master_remark : '';
                $MainMaster->isactive = $request->tag_master_status;
                $MainMaster->tag_type = 202;


                $MainMaster->save();
                if ($MainMaster) {

                    if ($request->tag_master_id != 0) {

                        $response = successRes("Successfully saved user tag master");



                        $debugLog = array();
                        $debugLog['name'] = "user-tag-master-edit";
                        $debugLog['description'] = "user tag master #" . $MainMaster->id . "(" . $MainMaster->tagname . ")" . " has been updated ";
                        saveDebugLog($debugLog);
                    } else {
                        $response = successRes("Successfully added user tag master");

                        $debugLog = array();
                        $debugLog['name'] = "user-tag-master-add";
                        $debugLog['description'] = "user tag master #" . $MainMaster->id . "(" . $MainMaster->tagname . ") has been added ";
                        saveDebugLog($debugLog);
                    }
                }
            }

            return response()->json($response)->header('Content-Type', 'application/json');
        }
    }

    public function ajaxUserTag(Request $request)
    {
        //DB::enableQueryLog();

        $searchColumns = array(
            0 => 'tag_master.id',
            1 => 'tag_master.tagname',
        );

        $columns = array(
            0 => 'tag_master.id',
            1 => 'tag_master.tagname',
            2 => 'tag_master.shortname',
            3 => 'tag_master.isactive',
        );

        $recordsTotal = Tags::query()->where('tag_type', 202)->count();
        $recordsFiltered = $recordsTotal;

        $query = Tags::query();
        $query->select($columns);
        $query->limit($request->length);
        $query->where('tag_type', 202);
        $query->offset($request->start);
        $query->orderBy($columns[$request['order'][0]['column']], $request['order'][0]['dir']);
        $isFilterApply = 0;
        $search_value = '';

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
        // echo "<pre>";
        // print_r(DB::getQueryLog());
        // die;

        $data = json_decode(json_encode($data), true);

        if ($isFilterApply == 1) {
            $recordsFiltered = count($data);
        }

        foreach ($data as $key => $value) {

            $data[$key]['tagname'] = "<p>" . highlightString($data[$key]['tagname'],$search_value) . '</p>';
            $data[$key]['shortname'] = "<p>" . highlightString($data[$key]['shortname'],$search_value) . '</p>';

            $data[$key]['isactive'] = getMainMasterStatusLable($value['isactive']);

            $uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';

            $uiAction .= '<li class="list-inline-item px-2">';
            $uiAction .= '<a onclick="editView(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Edit"><i class="bx bx-edit-alt"></i></a>';
            $uiAction .= '</li>';

            $uiAction .= '<li class="list-inline-item px-2">';
            $uiAction .= '<a onclick="deleteWarning(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Delete"><i class="bx bx-trash-alt"></i></a>';
            $uiAction .= '</li>';

            $uiAction .= '</ul>';

            $data[$key]['action'] = $uiAction;
        }

        $jsonData = array(
            "draw" => intval($request['draw']),
            // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($recordsTotal),
            // total number of records
            "recordsFiltered" => intval($recordsFiltered),
            // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $data, // total data array
        );

        return $jsonData;
    }

    public function detailUserTag(Request $request)
    {

        $MainMaster = Tags::query()->where('id', $request->id)->where('tag_type', 202)->first();
        if ($MainMaster) {
            $response = successRes("Successfully get user tag master");
            $response['data'] = $MainMaster;
        } else {
            $response = errorRes("Invalid id");
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function ajaxCallAdditionalInfo(Request $request)
    {



        $searchColumns = array(

            0 => 'crm_setting_additional_info.id',
            1 => 'crm_setting_additional_info.name',
        );

        $columns = array(
            0 => 'crm_setting_additional_info.id',
            1 => 'crm_setting_additional_info.name',
            2 => 'crm_setting_additional_info.status',

        );

        $recordsTotal = CRMSettingAdditionalInfo::count();
        $recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.

        $query = CRMSettingAdditionalInfo::query();
        $query->select($columns);
        $query->limit($request->length);
        $query->offset($request->start);
        $query->orderBy($columns[$request['order'][0]['column']], $request['order'][0]['dir']);
        $isFilterApply = 0;
        $search_value = '';

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

            $viewData[$key]['id'] = "<p>" . highlightString($value['id'],$search_value) . '</p>';
            $viewData[$key]['name'] = "<p>" . highlightString($value['name'],$search_value) . '</p>';
            $viewData[$key]['status'] = getCRMWantToCoverStatusLable($value['status']);

            $uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';
            $uiAction .= '<li class="list-inline-item px-2">';
            $uiAction .= '<a onclick="editView(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Edit"><i class="bx bx-edit-alt"></i></a>';
            $uiAction .= '</li>';
            $uiAction .= '</ul>';

            $viewData[$key]['action'] = $uiAction;
        }

        $jsonData = array(
            "draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($recordsTotal), // total number of records
            "recordsFiltered" => intval($recordsFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $viewData, // total data array

        );
        return $jsonData;
    }

    public function saveCallAdditionalInfo(Request $request)
    {

        $rules = array();
        $rules['data_id'] = 'required';
        $rules['data_name'] = 'required';
        $rules['data_status'] = 'required';
        $customMessage = array();
        $validator = Validator::make($request->all(), $rules, $customMessage);

        if ($validator->fails()) {

            $response = errorRes("The request could not be understood by the server due to malformed syntax");
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors();
        } else {

            $alreadyName = CRMSettingAdditionalInfo::query();
            $alreadyName->where('name', $request->data_name);

            if ($request->data_id != 0) {
                $alreadyName->where('id', '!=', $request->data_id);
            }
            $alreadyName = $alreadyName->first();
            if ($alreadyName) {

                $response = errorRes("already name exits, Try with another name");
            } else {
                if ($request->data_id != 0) {

                    $data = CRMSettingAdditionalInfo::find($request->data_id);
                } else {
                    $data = new CRMSettingAdditionalInfo();
                }
                $data->name = $request->data_name;
                $data->status = $request->data_status;
                
                if(isset($request->data_is_textfield)){
                    $data->is_textfield = $request->data_is_textfield;
                }
                $data->save();
                $response = successRes("Successfully saved data");
            }
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function detailCallAdditionalInfo(Request $request)
    {

        $data = CRMSettingAdditionalInfo::find($request->id);
        if ($data) {

            $response = successRes("Successfully get main master");
            $response['data'] = $data;
        } else {
            $response = errorRes("Invalid id");
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }
}
