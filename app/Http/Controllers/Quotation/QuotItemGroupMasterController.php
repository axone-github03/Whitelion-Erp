<?php

namespace App\Http\Controllers\Quotation;

use App\Http\Controllers\Controller;

use App\Models\Wlmst_ItemGroup;
use App\Models\WlmstCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class QuotItemGroupMasterController extends Controller
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
        $data['title'] = "Quotation Item Group Master ";
        return view('quotation/master/itemgroup/itemgroup', compact('data'));
    }

    public function searchCompany(Request $request)
    {

        $StateList = array();
        $StateList = WlmstCompany::select('id', 'companyname as text');
        $StateList->where('companyname', 'like', "%" . $request->q . "%");
        $StateList->limit(5);
        $StateList = $StateList->get();

        $response = array();
        $response['results'] = $StateList;
        $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function ajax(Request $request)
    {
        //DB::enableQueryLog();

        $searchColumns = array(
            0 => 'wlmst_item_groups.id',
            1 => 'wlmst_item_groups.itemgroupname'
        );

        $columns = array(
            0 => 'wlmst_item_groups.id',
            1 => 'wlmst_item_groups.itemgroupname',
            2 => 'wlmst_item_groups.shortname',
            3 => 'wlmst_item_groups.isactive',
            4 => 'wlmst_item_groups.app_isactive',
        );

        $recordsTotal = Wlmst_ItemGroup::count();
        $recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.

        $query = Wlmst_ItemGroup::query();
        // $query = DB::table('wlmst_item_groups');
        // $query->leftJoin('wlmst_companies', 'wlmst_companies.id', '=', 'wlmst_item_groups.company_id');
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
            $data[$key]['itemgroupname'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">' .  highlightString($data[$key]['itemgroupname'],$search_value) . '</a></h5>';
            // <p class="text-muted mb-0">' . $data[$key]['companyname'] . '</p>';

            $data[$key]['shortname'] = "<p>" .  highlightString($data[$key]['shortname'],$search_value) . '</p>';

            $data[$key]['isactive'] = getMainMasterStatusLable($value['isactive']);
            $data[$key]['appactive'] = getMainMasterStatusLable($value['app_isactive']);

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
            'q_group_master_id' => ['required'],
            'q_group_master_name' => ['required'],
            'q_group_master_code' => ['required'],
            // 'q_group_master_company_id' => ['required'],
            'q_group_master_status' => ['required'],
        ]);
        if ($validator->fails()) {

            $response = array();
            $response['status'] = 0;
            $response['msg'] = "The request could not be understood by the server due to malformed syntax";
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors();

            return response()->json($response)->header('Content-Type', 'application/json');
        } else {

            $alreadyCode = Wlmst_ItemGroup::query();

            if ($request->q_group_master_id != 0) {

                $alreadyCode->where('shortname', $request->q_group_master_code);
                $alreadyCode->where('id', '!=', $request->q_group_master_id);
            } else {
                $alreadyCode->where('shortname', $request->q_group_master_code);
            }

            $alreadyCode = $alreadyCode->first();

            if ($alreadyCode) {

                $response = errorRes("already group name exits, Try with another group name");
            } else {

                if ($request->q_group_master_id != 0) {

                    $MainMaster = Wlmst_ItemGroup::find($request->q_group_master_id);
                    $MainMaster->updateby = Auth::user()->id;
                    $MainMaster->updateip = $request->ip();
                } else {
                    $MainMaster = new Wlmst_ItemGroup();
                    $MainMaster->entryby = Auth::user()->id;
                    $MainMaster->entryip = $request->ip();
                }

                $MainMaster->itemgroupname = $request->q_group_master_name;
                $MainMaster->shortname = $request->q_group_master_code;
                $MainMaster->isactive = $request->q_group_master_status;
                $MainMaster->sequence = $request->q_group_master_sequence;
                $MainMaster->app_isactive = $request->q_group_master_appstatus;
                $MainMaster->remark = isset($request->q_group_master_remark) ? $request->q_group_master_remark : '';
                


                $MainMaster->save();
                if ($MainMaster) {

                    if ($request->q_group_master_id != 0) {

                        $response = successRes("Successfully saved item group master");

                        $debugLog = array();
                        $debugLog['name'] = "quot-item-group-master-edit";
                        $debugLog['description'] = "quotation item group master #" . $MainMaster->id . "(" . $MainMaster->itemgroupname . ")" . " has been updated ";
                        saveDebugLog($debugLog);
                    } else {
                        $response = successRes("Successfully added item group master");

                        $debugLog = array();
                        $debugLog['name'] = "quot-item-group-master-add";
                        $debugLog['description'] = "quotation item group master #" . $MainMaster->id . "(" . $MainMaster->itemgroupname . ") has been added ";
                        saveDebugLog($debugLog);
                    }
                }
            }

            return response()->json($response)->header('Content-Type', 'application/json');
        }
    }

    public function detail(Request $request)
    {

        // $MainMaster = Wlmst_ItemGroup::with(array('company' => function ($query) {
        // 	$query->select('id', 'companyname');
        // }))->find($request->id);

        $MainMaster = Wlmst_ItemGroup::find($request->id);

        if ($MainMaster) {
            $response = successRes("Successfully get quotation item group master");
            $response['data'] = $MainMaster;
        } else {
            $response = errorRes("Invalid id");
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function delete(Request $request)
    {

        $ItemGroup = Wlmst_ItemGroup::find($request->id);
        if ($ItemGroup) {

            $debugLog = array();
            $debugLog['name'] = "quot-item-group-delete";
            $debugLog['description'] = "quot item group #" . $ItemGroup->id . "(" . $ItemGroup->itemgroupname . ") has been deleted";
            saveDebugLog($debugLog);

            $ItemGroup->delete();
        }
        $response = successRes("Successfully delete Item Group");
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function export(Request $request)
	{

		$columns = array(
			'wlmst_item_groups.id',
			'wlmst_item_groups.itemgroupname',
			'wlmst_item_groups.shortname',
			'wlmst_item_groups.maxdisc',
			'wlmst_item_groups.sequence',
			'wlmst_item_groups.app_isactive',
			'wlmst_item_groups.isactive',
			'wlmst_item_groups.remark',
			'wlmst_item_groups.created_at',
			'wlmst_item_groups.entryby',
			DB::raw('CONCAT(entry_user.first_name," ",entry_user.last_name) as entrybyname'),
			'wlmst_item_groups.entryip',
			'wlmst_item_groups.updated_at',
			'wlmst_item_groups.updateby',
			DB::raw('CONCAT(update_user.first_name," ",update_user.last_name) as updatebyname'),
			'wlmst_item_groups.updateip',
		);

		$query = Wlmst_ItemGroup::query();
		$query->select($columns);
		$query->leftJoin('users as entry_user', 'entry_user.id', '=', 'wlmst_item_groups.entryby');
		$query->leftJoin('users as update_user', 'update_user.id', '=', 'wlmst_item_groups.updateby');
		$data = $query->get();

		$headers = array("#ID", "Group Name", "Short Name", "Max Disc", "Sequence", "App Status", "App Status Label", "Status", "Status Label", "Remark", "Created At", "Entry By", "Entry By Name", "Entry Ip", "Updated At", "Update By", "Update By Name", "Update Ip");

		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="quotation-group-data.csv"');

		$fp = fopen('php://output', 'wb');

		fputcsv($fp, $headers);

		foreach ($data as $key => $value) {

			$lineVal = array(
				$value->id,
				$value->itemgroupname,
				$value->shortname,
				$value->maxdisc,
				$value->sequence,
				$value->app_isactive,
                getUserStatus($value->app_isactive),
				$value->isactive,
                getUserStatus($value->isactive),
				$value->remark,
				$value->created_at,
				$value->entryby,
				$value->entrybyname,
				$value->entryip,
				$value->updated_at,
				$value->updateby,
				$value->updatebyname,
				$value->updateip,
			);

			fputcsv($fp, $lineVal, ",");
		}

		fclose($fp);
	}
}
