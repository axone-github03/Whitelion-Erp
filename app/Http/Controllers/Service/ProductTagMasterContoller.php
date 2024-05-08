<?php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Wlmst_tag;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductTagMasterContoller extends Controller
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

    public function index()
    {
        $data = array();
        $data['title'] = "Item Tag Master ";
        return view('service/master/tagmaster/tag', compact('data'));
    }

    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tag_master_name' => ['required'],
            'tag_master_code' => ['required'],
            'tag_master_status' => ['required'],
            'tag_master_remark' => ['required'],
        ]);
        if ($validator->fails()) {

            $response = array();
            $response['status'] = 0;
            $response['msg'] = "The request could not be understood by the server due to malformed syntax";
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors();

            return response()->json($response)->header('Content-Type', 'application/json');
        } else {

            $alreadyName = Wlmst_tag::query();

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
                    $MainMaster = Wlmst_tag::find($request->tag_master_id);
                    $MainMaster->updateby = Auth::user()->id;
                    $MainMaster->updateip = $request->ip();
                } else {
                    $MainMaster = new Wlmst_tag();
                    $MainMaster->entryby = Auth::user()->id;
                    $MainMaster->entryip = $request->ip();
                }

                $MainMaster->tagname = $request->tag_master_name;
                $MainMaster->shortname = $request->tag_master_code;
                $MainMaster->remark = isset($request->tag_master_remark) ? $request->tag_master_remark : '';
                $MainMaster->isactive = $request->tag_master_status;


                $MainMaster->save();
                if ($MainMaster) {

                    if ($request->tag_master_id != 0) {

                        $response = successRes("Successfully saved item tag master");

                        

                        $debugLog = array();
                        $debugLog['name'] = "service-item-tag-master-edit";
                        $debugLog['description'] = "item tag master #" . $MainMaster->id . "(" . $MainMaster->tagname . ")" . " has been updated ";
                        saveDebugLog($debugLog);
                    } else {
                        $response = successRes("Successfully added item tag master");

                        $debugLog = array();
                        $debugLog['name'] = "service-item-tag-master-add";
                        $debugLog['description'] = "item tag master #" . $MainMaster->id . "(" . $MainMaster->tagname . ") has been added ";
                        saveDebugLog($debugLog);
                    }
                }
            }

            return response()->json($response)->header('Content-Type', 'application/json');
        }
    }

    public function ajax(Request $request)
    {
        //DB::enableQueryLog();

        $searchColumns = array(
            0 => 'wlmst_tags.id',
            1 => 'wlmst_tags.tagname',
        );

        $columns = array(
            0 => 'wlmst_tags.id',
            1 => 'wlmst_tags.tagname',
            2 => 'wlmst_tags.shortname',
            3 => 'wlmst_tags.isactive',
        );

        $recordsTotal = Wlmst_tag::count();
        $recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.

        $query = Wlmst_tag::query();
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
        // echo "<pre>";
        // print_r(DB::getQueryLog());
        // die;

        $data = json_decode(json_encode($data), true);

        if ($isFilterApply == 1) {
            $recordsFiltered = count($data);
        }

        foreach ($data as $key => $value) {
			$data[$key]['id'] = highlightString($data[$key]['id'],$search_value);
            $data[$key]['tagname'] = "<p>" .  highlightString($data[$key]['tagname'],$search_value) . '</p>';
            $data[$key]['shortname'] = "<p>" .  highlightString($data[$key]['shortname'],$search_value) . '</p>';

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
            "draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($recordsTotal), // total number of records
            "recordsFiltered" => intval($recordsFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $data, // total data array
        );

        return $jsonData;
    }

    public function delete(Request $request)
    {

        $TagMaster = Wlmst_tag::find($request->id);
        if ($TagMaster) {

            $debugLog = array();
            $debugLog['name'] = "service-item-tag-delete";
            $debugLog['description'] = "quot item company #" . $TagMaster->id . "(" . $TagMaster->tagname . ") has been deleted";
            saveDebugLog($debugLog);

            $TagMaster->delete();
        }
        $response = successRes("Successfully delete Company");
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function detail(Request $request)
	{

		$MainMaster = Wlmst_tag::find($request->id);
		if ($MainMaster) {
			$response = successRes("Successfully get service item tag master");
			$response['data'] = $MainMaster;
		} else {
			$response = errorRes("Invalid id");
		}
		return response()->json($response)->header('Content-Type', 'application/json');
	}
}
