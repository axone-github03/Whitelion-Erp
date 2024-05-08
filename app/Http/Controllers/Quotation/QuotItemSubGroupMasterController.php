<?php

namespace App\Http\Controllers\Quotation;

use App\Http\Controllers\Controller;

use App\Models\Wlmst_ItemGroup;
use App\Models\WlmstCompany;
use App\Models\User;
use App\Models\WlmstItemSubgroup;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class QuotItemSubGroupMasterController extends Controller
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
        $data['title'] = "Quotation Item SubGroup Master ";
        return view('quotation/master/itemsubgroup/itemsubgroup', compact('data'));
    }

    public function searchCompany(Request $request)
    {
        $CompanyList = array();
        $CompanyList = WlmstCompany::select('id', 'companyname as text');
        $CompanyList->where('companyname', 'like', "%" . $request->q . "%");
        $CompanyList->limit(5);
        $CompanyList = $CompanyList->get();

        $response = array();
        $response['results'] = $CompanyList;
        $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function searchGroup(Request $request)
    {

        $GroupList = array();
        $GroupList = Wlmst_ItemGroup::select('id', 'itemgroupname as text');
        // $GroupList->where('company_id', $request->company_id);
        $GroupList->where('itemgroupname', 'like', "%" . $request->q . "%");
        $GroupList->limit(5);
        $GroupList = $GroupList->get();

        $response = array();
        $response['results'] = $GroupList;
        $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function searchManager(Request $request)
    {
        $q = $request->q;

        $ManagerList = array();
        $ManagerList = User::select('id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"));
        $ManagerList->where('type', 2);
        $ManagerList->where(function ($query) use ($q) {
            $query->where('users.first_name', 'like', '%' . $q . '%');
            $query->orWhere('users.last_name', 'like', '%' . $q . '%');
        });
        $ManagerList->limit(5);
        $ManagerList = $ManagerList->get();

        $response = array();
        $response['results'] = $ManagerList;
        $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function ajax(Request $request)
    {
        //DB::enableQueryLog();

        $searchColumns = array(
            0 => 'wlmst_item_subgroups.id',
            1 => 'wlmst_item_subgroups.itemsubgroupname',
        );

        $columns = array(
            0 => 'wlmst_item_subgroups.id',
            1 => 'wlmst_item_subgroups.itemsubgroupname',
            2 => 'wlmst_item_subgroups.shortname',
            3 => 'wlmst_item_subgroups.isactive',
            4 => 'wlmst_item_subgroups.company_id',
            5 => 'wlmst_companies.companyname',
            6 => 'wlmst_item_groups.itemgroupname',
        );

        $recordsTotal = WlmstItemSubgroup::count();
        $recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.

        $query = WlmstItemSubgroup::query();
        // $query = DB::table('wlmst_item_groups');
        $query->leftJoin('wlmst_companies', 'wlmst_companies.id', '=', 'wlmst_item_subgroups.company_id');
        $query->leftJoin('wlmst_item_groups', 'wlmst_item_groups.id', '=', 'wlmst_item_subgroups.itemgroup_id');
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
            $data[$key]['itemsubgroupname'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">' .  highlightString($data[$key]['itemsubgroupname'],$search_value) . '</a></h5>
            <p class="text-muted mb-0">' .  highlightString($data[$key]['companyname'] . " / " . $data[$key]['itemgroupname'],$search_value) . '</p>';

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

    public function save(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'q_subgroup_master_id' => ['required'],
            'q_subgroup_master_name' => ['required'],
            'q_subgroup_master_code' => ['required'],
            'q_subgroup_master_company_id' => ['required'],
            'q_subgroup_master_status' => ['required'],
        ]);
        if ($validator->fails()) {
            $response = array();
            $response['status'] = 0;
            $response['msg'] = "The request could not be understood by the server due to malformed syntax";
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors();

            return response()->json($response)->header('Content-Type', 'application/json');
        } else {

            // $alreadyCode = Wlmst_ItemGroup::query();

            // if ($request->q_group_master_id != 0) {

            // 	$alreadyCode->where('shortname', $request->q_group_master_code);
            // 	$alreadyCode->where('id', '!=', $request->q_group_master_id);
            // } else {
            // 	$alreadyCode->where('shortname', $request->q_group_master_code);
            // }

            // $alreadyCode = $alreadyCode->first();

            // if ($alreadyCode) {

            // 	$response = errorRes("already shortname exits, Try with another shortname");
            // } else 

            if ($request->q_subgroup_master_id != 0) {

                $SubGroupMaster = WlmstItemSubgroup::find($request->q_subgroup_master_id);
                $SubGroupMaster->updateby = Auth::user()->id;
                $SubGroupMaster->updateip = $request->ip();
            } else {
                $SubGroupMaster = new WlmstItemSubgroup();
                $SubGroupMaster->entryby = Auth::user()->id;
                $SubGroupMaster->entryip = $request->ip();
            }

            $SubGroupMaster->itemsubgroupname = $request->q_subgroup_master_name;
            $SubGroupMaster->shortname = $request->q_subgroup_master_code;
            // $SubGroupMaster->company_id = $request->q_subgroup_master_company_id;
            $SubGroupMaster->company_id = implode(",", $request->q_subgroup_master_company_id);
            $SubGroupMaster->itemgroup_id = $request->q_subgroup_master_group_id;
            $SubGroupMaster->isactive = $request->q_subgroup_master_status;

            $SubGroupMaster->default_disc = $request->q_subgroup_master_default_dis;
            $SubGroupMaster->maxdisc = $request->q_subgroup_master_user_dis;
            $SubGroupMaster->channel_partner_maxdisc = $request->q_subgroup_master_channel_partner_disc;
            $SubGroupMaster->manager_ids = implode(",", $request->q_subgroup_master_manager_ids);
            $SubGroupMaster->manager_maxdisc = $request->q_subgroup_master_manager_disc;
            // $SubGroupMaster->company_admin_maxdisc = $request->q_subgroup_master_admin_dis;

            $SubGroupMaster->remark = isset($request->q_subgroup_master_remark) ? $request->q_subgroup_master_remark : '';;

            $SubGroupMaster->save();
            if ($SubGroupMaster) {

                if ($request->q_subgroup_master_id != 0) {

                    $response = successRes("Successfully saved item subgroup master");

                    $debugLog = array();
                    $debugLog['name'] = "quot-item-subgroup-master-edit";
                    $debugLog['description'] = "quotation item Sub group master #" . $SubGroupMaster->id . "(" . $SubGroupMaster->itemsubgroupname . ")" . " has been updated ";
                    saveDebugLog($debugLog);
                } else {
                    $response = successRes("Successfully added item subgroup master");

                    $debugLog = array();
                    $debugLog['name'] = "quot-item-subgroup-master-add";
                    $debugLog['description'] = "quotation item sub group master #" . $SubGroupMaster->id . "(" . $SubGroupMaster->itemsubgroupname . ") has been added ";
                    saveDebugLog($debugLog);
                }
            }
            // }

            return response()->json($response)->header('Content-Type', 'application/json');
        }
    }

    public function detail(Request $request)
    {

        $MainMaster = WlmstItemSubgroup::with(array('itemgroup' => function ($query) {
            $query->select('id', 'itemgroupname');
        }))->find($request->id);

        if ($MainMaster) {
            $response = successRes("Successfully get quotation item subgroup master");
            $query_company = DB::table('wlmst_companies');
            $query_company->select('wlmst_companies.id AS id', 'wlmst_companies.companyname AS text');
            $query_company->whereIn('wlmst_companies.id', explode(",", $MainMaster['company_id']));

            $query_manager = DB::table('users');
            $query_manager->select('users.id AS id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"));
            $query_manager->whereIn('users.id', explode(",", $MainMaster['manager_ids']));
            $MainMaster['manager'] = $query_manager->get();
            
            $MainMaster['company'] = $query_company->get();
            $response['data'] = $MainMaster;
        } else {
            $response = errorRes("Invalid id");
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function delete(Request $request)
    {

        $ItemSubGroup = WlmstItemSubgroup::find($request->id);
        if ($ItemSubGroup) {

            $debugLog = array();
            $debugLog['name'] = "quot-item-subgroup-delete";
            $debugLog['description'] = "quot item subgroup #" . $ItemSubGroup->id . "(" . $ItemSubGroup->itemsubgroupname . ") has been deleted";
            saveDebugLog($debugLog);

            $ItemSubGroup->delete();
        }
        $response = successRes("Successfully delete ItemSubGroup");
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function export(Request $request)
    {
        $columns = array(
            'wlmst_item_subgroups.id',
            'wlmst_item_subgroups.itemsubgroupname',
            'wlmst_item_subgroups.itemgroup_id',
            'group.itemgroupname',
            'wlmst_item_subgroups.company_id',
            'wlmst_item_subgroups.shortname',
            'wlmst_item_subgroups.maxdisc',

            'wlmst_item_subgroups.isactive',
            'wlmst_item_subgroups.remark',
            'wlmst_item_subgroups.created_at',
            'wlmst_item_subgroups.entryby',
            DB::raw('CONCAT(entry_user.first_name," ",entry_user.last_name) as entrybyname'),
            'wlmst_item_subgroups.entryip',
            'wlmst_item_subgroups.updated_at',
            'wlmst_item_subgroups.updateby',
            DB::raw('CONCAT(update_user.first_name," ",update_user.last_name) as updatebyname'),
            'wlmst_item_subgroups.updateip',
        );

        $query = WlmstItemSubgroup::query();
        $query->select($columns);
        $query->leftJoin('wlmst_item_groups as group', 'group.id', '=', 'wlmst_item_subgroups.itemgroup_id');
        $query->leftJoin('users as entry_user', 'entry_user.id', '=', 'wlmst_item_subgroups.entryby');
        $query->leftJoin('users as update_user', 'update_user.id', '=', 'wlmst_item_subgroups.updateby');
        $data = $query->get();

        $headers = array("#ID", "SubGroup Name", "Group Id", "Group Name", "Company Id", "Company Name", "Short Name", "Max Disc.", "Status", "Status Label", "Remark", "Created At", "Entry By", "Entry By Name", "Entry Ip", "Updated At", "Update By", "Update By Name", "Update Ip");

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="quotation-sub-group-data.csv"');

        $fp = fopen('php://output', 'wb');

        fputcsv($fp, $headers);

        foreach ($data as $key => $value) {

            $query_company = DB::table('wlmst_companies');
            $query_company->select('wlmst_companies.id AS id', 'wlmst_companies.companyname AS text');
            $query_company->whereIn('wlmst_companies.id', explode(",", $value->company_id));
            $companyname = '';
            foreach ($query_company->get() as $key => $company) {
                $companyname .= $company->text.',';
            }

            $lineVal = array(
                $value->id,
                $value->itemsubgroupname,
                $value->itemgroup_id,
                $value->itemgroupname,
                $value->company_id,
                substr($companyname, 0, -1),
                $value->shortname,
                $value->maxdisc,
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
