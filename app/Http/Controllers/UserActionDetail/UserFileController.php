<?php

namespace App\Http\Controllers\UserActionDetail;

use App\Http\Controllers\Controller;

use App\Models\CRMSettingFileTag;
use App\Models\UserFiles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use DB;
use File;

class UserFileController extends Controller
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
        $rules['file_user_id'] = 'required';
        $rules['file_name'] = 'required';
        $rules['file_tag_id'] = 'required';
        $customMessage = array();
        $customMessage['file_user_id.required'] = "Invalid parameters";


        $validator = Validator::make($request->all(), $rules, $customMessage);

        if ($validator->fails()) {

            $response = errorRes("The request could not be understood by the server due to malformed syntax");
            $response['data'] = $validator->errors();
        } else {

            $uploadedFile1 = "";
            $fileSize = 0;


            if ($request->hasFile('file_name')) {

                $folderPathofFile = '/s/user-files/';
                if (!is_dir(public_path($folderPathofFile))) {
                    mkdir(public_path($folderPathofFile));
                }

                $folderPathofFile = '/s/user-files/' . date('Y');

                if (!is_dir(public_path($folderPathofFile))) {

                    mkdir(public_path($folderPathofFile));
                }

                $folderPathofFile = '/s/user-files/' . date('Y') . "/" . date('m');
                if (!is_dir(public_path($folderPathofFile))) {
                    mkdir(public_path($folderPathofFile));
                }

                $fileObject1 = $request->file('file_name');
                $extension = $fileObject1->getClientOriginalExtension();



                $fileName1 = time() . mt_rand(10000, 99999) . '.' . $extension;

                $destinationPath = public_path($folderPathofFile);
                $fileObject1->move($destinationPath, $fileName1);

                if (File::exists(public_path($folderPathofFile . "/" . $fileName1))) {

                    $uploadedFile1 = $folderPathofFile . "/" . $fileName1;

                    $fileSize = filesize(public_path($uploadedFile1));

                    $spaceUploadResponse = uploadFileOnSpaces(public_path($uploadedFile1), $uploadedFile1);
                    if ($spaceUploadResponse != 1) {
                        $uploadedFile1 = "";
                    } else {
                        unlink(public_path($uploadedFile1));
                    }
                }
            }

            if ($uploadedFile1 != "") {

                $UserFile = new UserFiles();
                $UserFile->user_id = $request->file_user_id;
                $UserFile->file_tag_id = $request->file_tag_id;
                $UserFile->file_size = $fileSize;
                $UserFile->name = $uploadedFile1;
                $UserFile->entryby = Auth::user()->id;
                $UserFile->entryip = $request->ip();
                $UserFile->updateby = Auth::user()->id;;
                $UserFile->updateip = $request->ip();
                $UserFile->save();


                $response = successRes("Successfully saved user file");
                $response['id'] = $UserFile->user_id;
            } else {

                $response = errorRes("Something went wrong");
            }
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

    function delete(Request $request)
    {

        $LeadFile = UserFiles::find($request->id);
        if ($LeadFile) {
            $LeadFile->delete();
        }

        $response = successRes("Successfully delete file");
        return response()->json($response)->header('Content-Type', 'application/json');
    }
}
