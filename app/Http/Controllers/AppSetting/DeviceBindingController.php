<?php

namespace App\Http\Controllers\AppSetting;

use App\Http\Controllers\Controller;
use App\Models\DeviceBindingMaster;
use App\Models\User;
use App\Models\CityList;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DeviceBindingController extends Controller
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
        $data['title'] = 'Device Binding Master';
        return view('app_setting/device_binding/device_binding', compact('data'));
    }

    function ajax(Request $request)
    {
        //DB::enableQueryLog();

        $searchColumns = ['device_binding_master.id', DB::raw("CONCAT(users.first_name, ' ', users.last_name)"), 'device_binding_master.web_uid', 'device_binding_master.app_uid'];

        $columns = ['device_binding_master.id', DB::raw("CONCAT(users.first_name, ' ', users.last_name) as user_name") , 'device_binding_master.web_uid', 'device_binding_master.app_uid', 'device_binding_master.status'];

        $recordsTotal = DeviceBindingMaster::count();
        $recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.

        $query = DeviceBindingMaster::query();
        $query->select($columns);
        $query->leftJoin('users', 'users.id', '=', 'device_binding_master.user_id');
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
                        $query->where($searchColumns[$i], 'like', '%' . $search_value . '%');
                    } else {
                        $query->orWhere($searchColumns[$i], 'like', '%' . $search_value . '%');
                    }
                }
            });
        }

        $data = $query->get();
        // echo "<pre>";
        // print_r(DB::getQueryLog());
        // die;

        $data = json_decode(json_encode($data), true);
        $data2 = json_decode(json_encode($data), true);

        if ($isFilterApply == 1) {
            $recordsFiltered = count($data);
        }

        foreach ($data as $key => $value) {
            $data[$key]['id'] = '<div class="avatar-xs"><span class="avatar-title rounded-circle">' . $value['id'] . '</span></div>';

            // $User_Detail = User::find($value['user_id']);
            $data[$key]['user'] = '<p>' . highlightString($value['user_name'],$search_value) . '</p>';
            $data[$key]['web_uid'] = '<p>' . highlightString($value['web_uid'],$search_value) . '</p>';
            $data[$key]['app_uid'] = '<p>' . highlightString($value['app_uid'],$search_value) . '</p>';

            $data[$key]['isactive'] = getMainMasterStatusLable($value['status']);

            $uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';
            $uiAction .= '<li class="list-inline-item px-2">';
            $uiAction .= '<a onclick="editView(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Edit"><i class="bx bx-edit-alt"></i></a>';
            $uiAction .= '</li>';

            // $uiAction .= '<li class="list-inline-item px-2">';
            // $uiAction .= '<a onclick="deleteWarning(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Delete"><i class="bx bx-trash-alt"></i></a>';
            // $uiAction .= '</li>';

            $uiAction .= '</ul>';

            $data[$key]['action'] = $uiAction;
        }

        $jsonData = [
            'draw' => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            'recordsTotal' => intval($recordsTotal), // total number of records
            'recordsFiltered' => intval($recordsFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
            'data' => $data, // total data array
        ];

        return $jsonData;
    }

    public function save(Request $request)
    {
        if ($request->q_master_id != 0) {
            $validator = Validator::make($request->all(), [
                'q_master_id' => ['required'],
                'q_master_user_id' => ['required'],
                'q_master_web_uid' => ['required'],
                'q_master_app_uid' => ['required'],
                'q_master_status' => ['required'],
            ]);
        } else {
            $validator = Validator::make($request->all(), [
                'q_master_id' => ['required'],
                'q_master_user_id' => ['required'],
                'q_master_web_uid' => ['required'],
                'q_master_app_uid' => ['required'],
                'q_master_status' => ['required'],
            ]);
        }

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
            if ($request->q_master_id != 0) {
                $MainMaster = DeviceBindingMaster::find($request->q_master_id);
                $MainMaster->updateby = Auth::user()->id;
                $MainMaster->updateip = $request->ip();
            } else {
                $MainMaster = new DeviceBindingMaster();
                $MainMaster->entryby = Auth::user()->id;
                $MainMaster->entryip = $request->ip();
            }

            $MainMaster->user_id = $request->q_master_user_id;
            $MainMaster->web_uid = $request->q_master_web_uid;
            $MainMaster->app_uid = $request->q_master_app_uid;
            $MainMaster->status = $request->q_master_status;
            $MainMaster->source = "WEB";
            $MainMaster->save();

            if ($MainMaster) {
                if ($request->q_master_id != 0) {
                    $response = successRes('Successfully saved Device Binding');

                    $debugLog = [];
                    $debugLog['name'] = 'device-binding-master-edit';
                    $debugLog['description'] = 'Device Binding #' . $MainMaster->id . '(' . $MainMaster->web_uid . ')' . ' has been updated ';
                    saveDebugLog($debugLog);
                } else {
                    $response = successRes('Successfully added Device Binding');

                    $debugLog = [];
                    $debugLog['name'] = 'device-binding-master-add';
                    $debugLog['description'] = 'Device Binding #' . $MainMaster->id . '(' . $MainMaster->web_uid . ') has been added ';
                    saveDebugLog($debugLog);
                }
            }

            return response()
                ->json($response)
                ->header('Content-Type', 'application/json');
        }
    }

    public function detail(Request $request)
    {
        $MainMaster = DeviceBindingMaster::find($request->id);

        if ($MainMaster) {
            $response = successRes('Successfully get Device Binding Master');

            $data['MainMaster'] = $MainMaster;
            $data['MainMaster']['user'] = User::select('id', DB::raw("CONCAT(first_name, ' ', last_name) as text"))->where('id', $MainMaster->user_id)->first();

            $response['data'] = $data;
        } else {
            $response = errorRes('Invalid id');
        }
        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }

    public function searchUser(Request $request){
        
        $userIds = DeviceBindingMaster::distinct()->pluck('user_id');

        $results = array();
		$results = User::select('id', DB::raw("CONCAT(first_name, ' ', last_name) as text"));
		$results->where('id', '!=', $request->id);
		$results->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%" . $request->q . "%");
		$results->whereNotIn('id', $userIds);

		$results->limit(5);
		$results = $results->get();

		$response = array();
		$response['results'] = $results;
		$response['pagination']['more'] = false;
		return response()->json($response)->header('Content-Type', 'application/json');
    }
}
