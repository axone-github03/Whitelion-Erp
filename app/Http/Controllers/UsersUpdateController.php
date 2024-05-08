<?php

namespace App\Http\Controllers;

use App\Models\UserUpdate;
use App\Models\User;
use App\Models\UserUpdateSeen;
use Illuminate\Support\Facades\Validator;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UsersUpdateController extends Controller
{

    public function __construct()
    {

        $this->middleware(function ($request, $next) {

            $tabCanAccessBy = array(0, 1, 2);

            if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
                return redirect()->route('dashboard');
            }
            return $next($request);
        });
    }

    public function detail(Request $request)
    {

        $response = successRes("");

        $User = User::find($request->user_id);
        if ($User) {
            $data = array();
            $data['for_user_id'] = $User->id;
            $data['update'] = UserUpdate::select('user_updates.id', 'user_updates.message', 'user_updates.created_at', 'user_updates.user_id', 'users.first_name', 'users.last_name')->leftJoin('users', 'user_updates.user_id', '=', 'users.id')->where('user_updates.reply_id', 0)->where('user_updates.for_user_id', $User->id)->orderBy('user_updates.id', 'desc')->get();



            foreach ($data['update'] as $key => $value) {

                $UserUpdateSeen = UserUpdateSeen::where('for_user_update_id', $value->id)->where('user_id', Auth::user()->id)->first();

                if (!$UserUpdateSeen) {

                    $UserUpdateSeen = new UserUpdateSeen();
                    $UserUpdateSeen->for_user_update_id = $value->id;
                    $UserUpdateSeen->user_id = Auth::user()->id;
                    $UserUpdateSeen->save();
                }


                $data['update'][$key]['reply'] = UserUpdate::select('user_updates.id', 'user_updates.message', 'user_updates.created_at', 'user_updates.user_id', 'users.first_name', 'users.last_name')->leftJoin('users', 'user_updates.user_id', '=', 'users.id')->where('user_updates.reply_id', $value->id)->where('user_updates.for_user_id', $User->id)->orderBy('user_updates.id', 'asc')->get();

                foreach ($data['update'][$key]['reply'] as $keyR => $valueR) {

                    $UserUpdateSeen = UserUpdateSeen::where('for_user_update_id', $valueR->id)->where('user_id', Auth::user()->id)->first();

                    if (!$UserUpdateSeen) {

                        $UserUpdateSeen = new UserUpdateSeen();
                        $UserUpdateSeen->for_user_update_id = $valueR->id;
                        $UserUpdateSeen->user_id = Auth::user()->id;
                        $UserUpdateSeen->save();
                    }
                }
            }

            $response['view'] = view('users/updates', compact('data'))->render();
        }



        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function save(Request $request)
    {



        $validator = Validator::make($request->all(), [

            'message' => ['required'],
            'for_user_id' => ['required'],
            'user_update_id' => ['required'],

        ]);

        if ($validator->fails()) {
            $response = array();
            $response['status'] = 0;
            $response['msg'] = "The request could not be understood by the server due to malformed syntax";
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors();

            return redirect()->back()->with("error", "Something went wrong with validation");
        } else {

            $meessageValidation = trim($request->message);
            $meessageValidation = str_replace('<p>', '', $meessageValidation);
            $meessageValidation = str_replace('</p>', '', $meessageValidation);
            $meessageValidation = str_replace('<br>', '', $meessageValidation);
            $meessageValidation = str_replace('&nbsp;', '', $meessageValidation);
            $meessageValidation = str_replace(' ', '', $meessageValidation);
            $meessageValidation = trim($meessageValidation);

            if ($meessageValidation == "") {
                $response = errorRes("Please enter your update");
                return response()->json($response)->header('Content-Type', 'application/json');
            }

            $User = User::find($request->for_user_id);

            if ($User) {


                if ($request->user_update_id != 0) {
                    $replyValidation = UserUpdate::find($request->user_update_id);
                }

                if ($request->user_update_id == 0 || ($replyValidation->for_user_id == $User->id)) {

                    $UserUpdate = new UserUpdate();
                    $UserUpdate->message = trim($request->message);
                    $UserUpdate->user_id = Auth::user()->id;
                    $UserUpdate->for_user_id = $request->for_user_id;
                    $UserUpdate->reply_id = $request->user_update_id;
                    $UserUpdate->save();
                    ///

                    $response = successRes("Successfully sent message");
                } else {
                    $response = errorRes("Invalid parameters");
                }
            } else {
                $response = errorRes("Invalid user id");
            }
            return response()->json($response)->header('Content-Type', 'application/json');
        }
    }

    public function updateSeen(Request $request)
    {

        $validator = Validator::make($request->all(), [

            'update_id' => ['required'],

        ]);

        if ($validator->fails()) {
            $response = array();
            $response['status'] = 0;
            $response['msg'] = "The request could not be understood by the server due to malformed syntax";
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors();
        } else {

            $InquiryUpdateSeen = UserUpdateSeen::query();
            $InquiryUpdateSeen->select('users.id', 'user_update_seen.user_id', 'users.first_name', 'users.last_name');
            $InquiryUpdateSeen->leftJoin('users', 'user_update_seen.user_id', '=', 'users.id');
            $InquiryUpdateSeen->where('user_update_seen.for_user_update_id', $request->update_id);
            // $InquiryUpdateSeen->where('user_update_seen.user_id', Auth::user()->id);
            $InquiryUpdateSeen = $InquiryUpdateSeen->orderBy('user_update_seen.id', 'desc')->get();

            $view = '<ul class="list-unstyled chat-list seen-ul" data-simplebar >';

            foreach ($InquiryUpdateSeen as $key => $value) {

                $firstLetterA = strtoupper(substr($value->first_name, 0, 1));
                $firstLetterB = strtoupper(substr($value->last_name, 0, 1));

                // 	$view .= '<div class=" avatar-xs inquiry-avatar-xs"><span class="seen-avatar avatar-title rounded-circle bg-primary bg-soft text-primary">' . $firstLetterA . '' . $firstLetterB . '</span></div>';

                $view .= ' <li>
                                                        <a href="javascript: void(0);">
                                                            <div class="d-flex align-items-center">
                                                                <div class="flex-shrink-0 me-2">
                                                                    <div class="avatar-xs">
                                                                        <span class="seen-avatar avatar-title rounded-circle bg-primary bg-soft text-primary">
                                                                            ' . $firstLetterA . '' . $firstLetterB . '
                                                                        </span>
                                                                    </div>
                                                                </div>

                                                                <div class="flex-grow-1">
                                                                    <h5 class="font-size-10 mb-0">' . $value->first_name . ' ' . $value->last_name . '</h5>
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </li>';
            }
            $view .= '</ul>';

            $response = successRes("Inquiry Update seen list");
            $response['data'] = $view;
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }
}
