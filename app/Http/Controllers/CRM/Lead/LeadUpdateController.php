<?php

namespace App\Http\Controllers\CRM\Lead;

use App\Http\Controllers\Controller;

use App\Models\CRMSettingFileTag;
use App\Models\LeadUpdate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use DB;
use File;

class LeadUpdateController extends Controller
{

    public function __construct()
    {

        $this->middleware(function ($request, $next) {

            $tabCanAccessBy = array(0, 1, 2, 6,9, 11,13, 101, 102, 103, 104, 105, 202, 302);

            if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
                return redirect()->route('dashboard');
            }

            return $next($request);
        });
    }



    public function save(Request $request)
    {

        $rules = array();
        $rules['lead_id'] = 'required';
        $rules['lead_update'] = 'required';

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
            $LeadUpdate->message = $request->lead_update;
            $LeadUpdate->task = "Note";
            $LeadUpdate->task_title = "Note";
            $LeadUpdate->save();

            $response = successRes("Successfully saved update");
            $response['id'] = $LeadUpdate->lead_id;
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }


    function searchTag(Request $request)
    {
        $searchKeyword = isset($request->q) ? $request->q : "";

        $data = CRMSettingFileTag::select('id', 'name as text');
        $data->where('crm_setting_file_tag.status', 1);
        $data->where('crm_setting_file_tag.name', 'like', "%" . $searchKeyword . "%");
        $data->limit(5);
        $data = $data->get();
        $response = array();
        $response['results'] = $data;
        $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');
    }
}
