<?php

namespace App\Http\Controllers\Target;

use App\Http\Controllers\Controller;
use App\Models\Wlmst_financialyear;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class YearMasterController extends Controller
{

    public function __construct()
    {

        $this->middleware(function ($request, $next) {

            $tabCanAccessBy = array(0, 1, 3);

            if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
                return redirect()->route('dashboard');
            }
            return $next($request);
        });
    }

    function ajax(Request $request)
    {
        //DB::enableQueryLog();

        $searchColumns = array(
            0 => 'wlmst_financialyear.id',
            1 => 'wlmst_financialyear.name',
        );

        $columns = array(
            0 => 'wlmst_financialyear.id',
            1 => 'wlmst_financialyear.name',
        );

        $recordsTotal = Wlmst_financialyear::count();
        $recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.

        $query = Wlmst_financialyear::query();
        $query->select($columns);
        $query->limit($request->length);
        $query->offset($request->start);
        $query->orderBy($columns[$request['order'][0]['column']], $request['order'][0]['dir']);
        $isFilterApply = 0;

        if (isset($request->q)) {
            $isFilterApply = 1;
            $search_value = $request->q;
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

            $data[$key]['no'] = "<p>" . $data[$key]['id'] . '</p>';
            $data[$key]['name'] = "<p>" . $data[$key]['name'] . '</p>';

            $uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';

            $uiAction .= '<li class="list-inline-item px-2">';
            $uiAction .= '<a onclick="editYearView(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Edit"><i class="bx bx-edit-alt"></i></a>';
            $uiAction .= '</li>';

            $uiAction .= '<li class="list-inline-item px-2">';
            $uiAction .= '<a onclick="deleteFinWarning(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Delete"><i class="bx bx-trash-alt"></i></a>';
            $uiAction .= '</li>';

            $uiAction .= '</ul>';

            $data[$key]['action'] = $uiAction;
        }

        $jsonData = array(
            "draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($recordsTotal), // total number of records
            "recordsFiltered" => intval($recordsFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $data, // total data array
        );

        return $jsonData;
    }

    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'q_year_master_id' => ['required'],
            'q_year_master_name' => ['required'],
        ]);
        if ($validator->fails()) {

            $response = array();
            $response['status'] = 0;
            $response['msg'] = "The request could not be understood by the server due to malformed syntax";
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors();

            return response()->json($response)->header('Content-Type', 'application/json');
        } else {
            $alreadyName = Wlmst_financialyear::query();

            if ($request->q_year_master_id != 0) {

                $alreadyName->where('name', $request->q_year_master_name);
                $alreadyName->where('id', '!=', $request->q_year_master_id);
            } else {
                $alreadyName->where('name', $request->q_year_master_name);
            }

            $alreadyName = $alreadyName->first();

            if ($alreadyName) {
                $response = errorRes("financial year already exits, Try with another year");
            } else {

                if ($request->q_year_master_id != 0) {
                    $MainMaster = Wlmst_financialyear::find($request->q_year_master_id);
                    $MainMaster->updateby = Auth::user()->id;
                    $MainMaster->updateip = $request->ip();
                } else {
                    $MainMaster = new Wlmst_financialyear();
                    $MainMaster->entryby = Auth::user()->id;
                    $MainMaster->entryip = $request->ip();
                }

                $MainMaster->name = $request->q_year_master_name;
                $MainMaster->source = 'WEB';

                $MainMaster->save();
                if ($MainMaster) {

                    if ($request->q_company_master_id != 0) {

                        $response = successRes("Successfully updated financial year");

                        $debugLog = array();
                        $debugLog['name'] = " financial-year-master-edit";
                        $debugLog['description'] = "financial year master #" . $MainMaster->id . "(" . $MainMaster->name . ")" . " has been updated ";
                        saveDebugLog($debugLog);
                    } else {
                        $response = successRes("Successfully added new financial year");

                        $debugLog = array();
                        $debugLog['name'] = "financial-year-master-add";
                        $debugLog['description'] = "financial year master master #" . $MainMaster->id . "(" . $MainMaster->name . ") has been added ";
                        saveDebugLog($debugLog);
                    }
                }
            }

            return response()->json($response)->header('Content-Type', 'application/json');
        }
    }

    public function detail(Request $request)
    {

        $MainMaster = Wlmst_financialyear::find($request->id);
        if ($MainMaster) {

            $response = successRes("Successfully get financial year detail");
            $response['data'] = $MainMaster;
        } else {
            $response = errorRes("Invalid id");
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function delete(Request $request)
    {

        $ItemVersion = Wlmst_financialyear::find($request->id);
        if ($ItemVersion) {

            $debugLog = array();
            $debugLog['name'] = "financial-year-master-delete";
            $debugLog['description'] = "financial year master master #" . $ItemVersion->id . "(" . $ItemVersion->name . ") has been deleted";
            saveDebugLog($debugLog);

            $ItemVersion->delete();
        }
        $response = successRes("Successfully delete financial year");
        return response()->json($response)->header('Content-Type', 'application/json');
    }

}
