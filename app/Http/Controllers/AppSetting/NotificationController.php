<?php

namespace App\Http\Controllers\AppSetting;

use App\Http\Controllers\Controller;
use App\Models\NotificationMaster;
use App\Models\CityList;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $tabCanAccessBy = [0, 1];

            if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
                return redirect()->route('dashboard');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $data = [];
        $data['title'] = 'Notification Master';
        return view('app_setting/notification/notification', compact('data'));
    }


    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'q_master_user_type_id' => ['required'],
            'q_master_title' => ['required'],
            'q_master_message' => ['required'],
        ]);

        $response = [];
        if ($validator->fails()) {
            $response['status'] = 0;
            $response['msg'] = $validator->errors()->first();
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors();

            return response()
                ->json($response)
                ->header('Content-Type', 'application/json');
        } else {
            $uploadedFile1 = '';

            if ($request->hasFile('q_master_image')) {
                $validator = Validator::make($request->all(), [
                    'q_master_image' => ['required'],
                ]);

                if ($validator->fails()) {
                    $response = [];
                    $response['status'] = 0;
                    $response['msg'] = $validator->errors()->first();
                    $response['statuscode'] = 400;
                    $response['data'] = $validator->errors();

                    return response()
                        ->json($response)
                        ->header('Content-Type', 'application/json');
                } else {
                    $folderPathImage = '/notification';
                    $fileObject1 = $request->file('q_master_image');

                    $extension = $fileObject1->getClientOriginalExtension();
                    $fileName1 = 'mskhkdfjnhsjhsdckshdsfj.' . $extension;

                    $destinationPath = public_path($folderPathImage);

                    $fileObject1->move($destinationPath, $fileName1);

                    if (File::exists(public_path($folderPathImage . '/' . $fileName1))) {
                        $uploadedFile1 = $folderPathImage . '/' . $fileName1;

                        $spaceUploadResponse = uploadFileOnSpaces(public_path($uploadedFile1), $uploadedFile1);
                        if ($spaceUploadResponse != 1) {
                            $uploadedFile1 = '';
                        } else {
                            unlink(public_path($uploadedFile1));
                        }
                    }
                }
            }

            $MainMaster = new NotificationMaster();
            if ($uploadedFile1 != '') {
                $MainMaster->image = $uploadedFile1;
            }
            $MainMaster->title = $request->q_master_title;
            $MainMaster->text = $request->q_master_message;
            $MainMaster->user_type = implode(",",$request->q_master_user_type_id);
            $MainMaster->user_id = 'ALL';
            $MainMaster->status = 1;
            $MainMaster->entryby = Auth::user()->id;
            $MainMaster->entryip = $request->ip();
            $MainMaster->source = 'WEB';
            $MainMaster->save();

            if ($MainMaster) {
                $userTokens = UsersTypeNotificationTokens(explode(",",$MainMaster->user_type));
                $image='';
                // $image='https://plus.unsplash.com/premium_photo-1664474619075-644dd191935f?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MXx8aW1hZ2V8ZW58MHx8MHx8fDA%3D&auto=format&fit=crop&w=500&q=60';
                if($MainMaster->image != null || $MainMaster->image != ''){
                    $image = getSpaceFilePath($MainMaster->image);
                }
                $response = sendNotificationTOAndroid($MainMaster->title, $MainMaster->text, $userTokens,'no',$MainMaster,$image);
                $response['token'] = $userTokens;
                $response['master'] = $MainMaster;
            }else{
                $response = errorRes();
            }


            return response()
                ->json($response)
                ->header('Content-Type', 'application/json');
        }
    }


    public function searchUserType(Request $request)
    {
        $searchKeyword = isset($request->q) ? $request->q : '';

        $result = array();
        foreach (getNotificationUserType() as $key => $value) {
            if ($searchKeyword != '') {
                if (preg_match("/" . $searchKeyword . "/i", $value['name'])) {
                    $newData['id'] = $value['id'];
                    $newData['text'] = $value['name'];
                }
            } else {
                $newData['id'] = $value['id'];
                $newData['text'] = $value['name'];
            }
            
            array_push($result, $newData);
        }

        $response = [];
        $response['results'] = $result;
        $response['pagination']['more'] = false;

        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }
}
