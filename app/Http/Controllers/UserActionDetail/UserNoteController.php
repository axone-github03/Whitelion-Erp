<?php

namespace App\Http\Controllers\UserActionDetail;

use App\Http\Controllers\Controller;

use App\Models\CRMSettingFileTag;
use App\Models\UserNotes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use DB;
use File;

class UserNoteController extends Controller
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

        $rules = array();
        $rules['user_id'] = 'required';
        $rules['note'] = 'required';

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {

            $response = errorRes("The request could not be understood by the server due to malformed syntax");
            $response['data'] = $validator->errors();
        } else {

            $UserUpdate = new UserNotes();
            $UserUpdate->user_id = $request->user_id;
            $UserUpdate->note = $request->note;
            $UserUpdate->note_type = "Note";
            $UserUpdate->note_title = "Note";
            $UserUpdate->entryby = Auth::user()->id;
            $UserUpdate->entryip = $request->ip();
            $UserUpdate->updateby = Auth::user()->id;
            $UserUpdate->updateip = $request->ip();
            $UserUpdate->save();
            
            if($UserUpdate){
                
                $response = successRes("Successfully saved update");
                $response['id'] = $UserUpdate->user_id;
            }else{
                $response = errorRes("pleas contact to admin");
                
            }
            $response['data'] = getUserNoteList($request->user_id);
            
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
