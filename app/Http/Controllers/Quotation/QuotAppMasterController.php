<?php

namespace App\Http\Controllers\Quotation;

use App\Http\Controllers\Controller;
use App\Models\Wlmst_ItemPrice;
use App\Models\WlmstCompany;
use App\Models\Wlmst_ItemGroup;
use App\Models\WlmstItemSubgroup;
use App\Models\wlmst_appversion;
use App\Models\WlmstItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\WlmstItemCategory;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Validator;
use App\Models\Wltrn_Quotation;
use App\Models\Wltrn_QuotItemdetail;
use App\Models\Wlmst_QuotationError;
use App\Models\wlmst_user_created_board_log;
use App\Models\Wlmst_QuotationType;
use App\Models\Wlmst_NameSuggestion;
use App\Models\Wlmst_target;
use App\Models\Wlmst_targetdetail;
use App\Models\Wlmst_Client;
use App\Models\LeadCall;
use App\Models\LeadMeeting;
use App\Models\LeadTask;
use App\Models\Lead;
use App\Models\LeadUpdate;
use App\Models\LeadContact;
use App\Models\LeadFile;
use App\Models\LeadTimeline;
use App\Models\LeadClosing;
use App\Models\LeadSource;
use App\Models\LeadAccountContact;
use App\Models\LeadCompetitor;
use App\Models\LeadMeetingParticipant;

class QuotAppMasterController extends Controller
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
        $data['title'] = "Application Master ";
        return view('quotation/master/appsetting/appsetting', compact('data'));
    }

    function ajax(Request $request)
    {
        //DB::enableQueryLog();

        $searchColumns = array(
            0 => 'wlmst_appversion.id',
            1 => 'wlmst_appversion.source',
            2 => 'wlmst_appversion.version',
        );

        $columns = array(
            0 => 'wlmst_appversion.id',
            1 => 'wlmst_appversion.source',
            2 => 'wlmst_appversion.version',
            3 => 'wlmst_appversion.isactive',
            4 => 'wlmst_appversion.remark',
        );

        $recordsTotal = wlmst_appversion::count();
        $recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.

        $query = wlmst_appversion::query();
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

            $data[$key]['source'] = "<p>" . $data[$key]['source'] . '</p>';
            $data[$key]['version'] = "<p>" . $data[$key]['version'] . '</p>';

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
            'q_app_master_id' => ['required'],
            'q_app_master_source' => ['required'],
            'q_app_master_version' => ['required'],
            'q_app_master_isactive' => ['required']
        ]);
        if ($validator->fails()) {

            $response = array();
            $response['status'] = 0;
            $response['msg'] = "The request could not be understood by the server due to malformed syntax";
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors();

            return response()->json($response)->header('Content-Type', 'application/json');
        } else {
            $alreadyName = wlmst_appversion::query();

            if ($request->q_app_master_id != 0) {

                $alreadyName->where('version', $request->q_app_master_version);
                $alreadyName->where('source', $request->q_app_master_source);
                $alreadyName->where('id', '!=', $request->q_app_master_id);
            } else {
                $alreadyName->where('version', $request->q_app_master_version);
                $alreadyName->where('source', $request->q_app_master_source);
            }

            $alreadyName = $alreadyName->first();

            if ($alreadyName) {
                $response = errorRes("already app version exits, Try with another name");
            } else {

                if ($request->q_app_master_id != 0) {
                    $MainMaster = wlmst_appversion::find($request->q_app_master_id);
                    $MainMaster->updateby = Auth::user()->id;
                    $MainMaster->updateip = $request->ip();
                } else {
                    $MainMaster = new wlmst_appversion();
                    $MainMaster->entryby = Auth::user()->id;
                    $MainMaster->entryip = $request->ip();
                }

                $MainMaster->source = $request->q_app_master_source;
                $MainMaster->version = $request->q_app_master_version;
                $MainMaster->isactive = $request->q_app_master_isactive;
                $MainMaster->remark = isset($request->q_app_master_remark) ? $request->q_app_master_remark : '';

                $MainMaster->save();
                if ($MainMaster) {

                    if ($request->q_company_master_id != 0) {

                        $response = successRes("Successfully updated new version");

                        $debugLog = array();
                        $debugLog['name'] = "quot-app-master-version-edit";
                        $debugLog['description'] = "quotation app setting master #" . $MainMaster->id . "(" . $MainMaster->version . ")" . " has been updated ";
                        saveDebugLog($debugLog);
                    } else {
                        $response = successRes("Successfully added new version");

                        $debugLog = array();
                        $debugLog['name'] = "quot-app-master-version-add";
                        $debugLog['description'] = "quotation app setting master #" . $MainMaster->id . "(" . $MainMaster->version . ") has been added ";
                        saveDebugLog($debugLog);
                    }
                }
            }

            return response()->json($response)->header('Content-Type', 'application/json');
        }
    }

    public function detail(Request $request)
    {

        $MainMaster = wlmst_appversion::find($request->id);
        if ($MainMaster) {

            $response = successRes("Successfully get version detail");
            $response['data'] = $MainMaster;
        } else {
            $response = errorRes("Invalid id");
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function delete(Request $request)
    {

        $ItemVersion = wlmst_appversion::find($request->id);
        if ($ItemVersion) {

            $debugLog = array();
            $debugLog['name'] = "quot-app-master-version-delete";
            $debugLog['description'] = "quotation app setting master #" . $ItemVersion->id . "(" . $ItemVersion->version . ") has been deleted";
            saveDebugLog($debugLog);

            $ItemVersion->delete();
        }
        $response = successRes("Successfully delete Company");
        return response()->json($response)->header('Content-Type', 'application/json');
    }
    public function getlogs(Request $request)
    {

        $getlogs = wlmst_user_created_board_log::query()->get();
        $response = successRes("Successfully delete Company");
        $response['data'] = $getlogs;
        return response()->json($response)->header('Content-Type', 'application/json');
    }


    function cmimport(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'uploaded_file' => ['required'],
            'radioFilter' => ['required'],
        ]);
        if ($validator->fails()) {

            $response = array();
            $response['status'] = 0;
            $response['msg'] = "The request could not be understood by the server due to malformed syntax";
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors();

            return response()->json($response)->header('Content-Type', 'application/json');
        } else {
            $the_file = $request->file('uploaded_file');
            $filetype = $request->radioFilter;
            try {
                $spreadsheet = IOFactory::load($the_file->getRealPath());
                $sheet        = $spreadsheet->getActiveSheet();
                $row_limit    = $sheet->getHighestDataRow();
                $column_limit = $sheet->getHighestDataColumn();
                $row_range    = range(2, $row_limit);
                $column_range = range('N', $column_limit);
                $data = array();
                foreach ($row_range as $row) {

                    if ($filetype == 'COMPANY') {
                        $CompanyMaster = new WlmstCompany();
                        $CompanyMaster->companyname = $sheet->getCell('B' . $row)->getValue();
                        $CompanyMaster->shortname = $sheet->getCell('C' . $row)->getValue();
                        $CompanyMaster->maxdisc = $sheet->getCell('D' . $row)->getValue();
                        $CompanyMaster->isactive = $sheet->getCell('E' . $row)->getValue();
                        $CompanyMaster->remark = $sheet->getCell('G' . $row)->getValue();
                        $CompanyMaster->entryby = Auth::user()->id;
                        $CompanyMaster->entryip = $request->ip();
                        $CompanyMaster->save();
                    } else if ($filetype == 'CATEGORY') {

                        $CategoryMaster = new WlmstItemCategory();
                        $CategoryMaster->itemcategoryname = $sheet->getCell('B' . $row)->getValue();
                        $CategoryMaster->shortname = $sheet->getCell('C' . $row)->getValue();
                        $CategoryMaster->display_group = $sheet->getCell('D' . $row)->getValue();
                        $CategoryMaster->isactive = $sheet->getCell('E' . $row)->getValue();
                        $CategoryMaster->cat_type = $sheet->getCell('G' . $row)->getValue();
                        $CategoryMaster->remark = $sheet->getCell('H' . $row)->getValue();
                        $CategoryMaster->entryby = Auth::user()->id;
                        $CategoryMaster->entryip = $request->ip();
                        $CategoryMaster->save();
                    } else if ($filetype == 'GROUP') {
                        $GroupMaster = new Wlmst_ItemGroup();
                        $GroupMaster->itemgroupname = $sheet->getCell('B' . $row)->getValue();
                        $GroupMaster->shortname = $sheet->getCell('C' . $row)->getValue();
                        $GroupMaster->maxdisc = $sheet->getCell('D' . $row)->getValue();
                        $GroupMaster->sequence = $sheet->getCell('E' . $row)->getValue();
                        $GroupMaster->app_isactive = $sheet->getCell('F' . $row)->getValue();
                        $GroupMaster->isactive = $sheet->getCell('H' . $row)->getValue();
                        $GroupMaster->remark = $sheet->getCell('J' . $row)->getValue();
                        $GroupMaster->entryby = Auth::user()->id;
                        $GroupMaster->entryip = $request->ip();
                        $GroupMaster->save();

                    } else if ($filetype == 'SUBGROUP') {
                        $SubGroupMaster = new WlmstItemSubgroup();
                        $SubGroupMaster->itemsubgroupname = $sheet->getCell('B' . $row)->getValue();
                        $SubGroupMaster->itemgroup_id = $sheet->getCell('C' . $row)->getValue();
                        $SubGroupMaster->company_id = $sheet->getCell('E' . $row)->getValue();
                        $SubGroupMaster->shortname = $sheet->getCell('G' . $row)->getValue();
                        $SubGroupMaster->maxdisc = $sheet->getCell('H' . $row)->getValue();
                        $SubGroupMaster->isactive = $sheet->getCell('I' . $row)->getValue();
                        $SubGroupMaster->remark = $sheet->getCell('K' . $row)->getValue();
                        $SubGroupMaster->entryby = Auth::user()->id;
                        $SubGroupMaster->entryip = $request->ip();
                        $SubGroupMaster->save();

                    } else if ($filetype == 'ITEM') {

                        $ItemMaster = new WlmstItem();
                        $ItemMaster->itemname = $sheet->getCell('B' . $row)->getValue();
                        $ItemMaster->itemcategory_id = $sheet->getCell('C' . $row)->getValue();
                        $ItemMaster->shortname = $sheet->getCell('E' . $row)->getValue();
                        $ItemMaster->module = $sheet->getCell('F' . $row)->getValue();
                        $ItemMaster->image = $sheet->getCell('G' . $row)->getValue();
                        $ItemMaster->is_special = $sheet->getCell('H' . $row)->getValue();
                        $ItemMaster->additional_remark = $sheet->getCell('I' . $row)->getValue();
                        $ItemMaster->sgst_per = $sheet->getCell('J' . $row)->getValue();
                        $ItemMaster->cgst_per = $sheet->getCell('K' . $row)->getValue();
                        $ItemMaster->igst_per = $sheet->getCell('L' . $row)->getValue();
                        $ItemMaster->app_display_name = $sheet->getCell('M' . $row)->getValue();
                        $ItemMaster->app_sequence = $sheet->getCell('N' . $row)->getValue();
                        $ItemMaster->max_module = $sheet->getCell('O' . $row)->getValue();
                        $ItemMaster->isactive = $sheet->getCell('P' . $row)->getValue();
                        $ItemMaster->remark = $sheet->getCell('R' . $row)->getValue();
                        $ItemMaster->entryby = Auth::user()->id;
                        $ItemMaster->entryip = $request->ip();
                        $ItemMaster->save();

                    } else if ($filetype == 'ITEMPRICE') {
                        $ItemPriceMaster = new Wlmst_ItemPrice();
                        $ItemPriceMaster->company_id = $sheet->getCell('B' . $row)->getValue();
                        $ItemPriceMaster->itemgroup_id = $sheet->getCell('D' . $row)->getValue();
                        $ItemPriceMaster->itemsubgroup_id = $sheet->getCell('F' . $row)->getValue();
                        $ItemPriceMaster->item_id = $sheet->getCell('H' . $row)->getValue();
                        $ItemPriceMaster->code = $sheet->getCell('L' . $row)->getValue();
                        $ItemPriceMaster->mrp = $sheet->getCell('M' . $row)->getValue();
                        $ItemPriceMaster->discount = $sheet->getCell('N' . $row)->getValue();
                        $ItemPriceMaster->effectivedate = $sheet->getCell('O' . $row)->getValue();
                        $ItemPriceMaster->image = $sheet->getCell('P' . $row)->getValue();
                        $ItemPriceMaster->item_type = $sheet->getCell('Q' . $row)->getValue();
                        $ItemPriceMaster->isactive = $sheet->getCell('R' . $row)->getValue();
                        $ItemPriceMaster->remark = $sheet->getCell('T' . $row)->getValue();
                        $ItemPriceMaster->entryby = Auth::user()->id;
                        $ItemPriceMaster->entryip = $request->ip();
                        $ItemPriceMaster->save();

                    } else if ($filetype == 'QUOTTYPE') {
                        $QuotTypeMaster = new Wlmst_QuotationType();
                        $QuotTypeMaster->name = $sheet->getCell('B' . $row)->getValue();
                        $QuotTypeMaster->shortname = $sheet->getCell('C' . $row)->getValue();
                        $QuotTypeMaster->isactive = $sheet->getCell('D' . $row)->getValue();
                        $QuotTypeMaster->remark = $sheet->getCell('E' . $row)->getValue();
                        $QuotTypeMaster->entryby = Auth::user()->id;
                        $QuotTypeMaster->save();
                    } else if ($filetype == 'SUGGESTION') {
                        $QuotTypeMaster = new Wlmst_NameSuggestion();
                        $QuotTypeMaster->type = $sheet->getCell('B' . $row)->getValue();
                        $QuotTypeMaster->name = $sheet->getCell('C' . $row)->getValue();
                        $QuotTypeMaster->remark = $sheet->getCell('D' . $row)->getValue();
                        $QuotTypeMaster->source = $sheet->getCell('E' . $row)->getValue();
                        $QuotTypeMaster->isactive = $sheet->getCell('L' . $row)->getValue();
                        $QuotTypeMaster->entryby = Auth::user()->id;
                        $QuotTypeMaster->entryip = $request->ip();
                        $QuotTypeMaster->save();
                    }
                }
                // DB::table('wlmst_companies')->insert($data);
                $response = successRes('Data Imported Successfully');
            } catch (Exception $e) {
                $error_code = $e->getMessage();
                $response = errorRes("already name exits, Try with another name");
            }
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function cmtruncatedata(Request $request)
    {
        $this->validate($request, [
            'radioFilter' => 'required'
        ]);

        try {
            $filetype = $request->radioFilter;
            if ($filetype == 'COMPANY') {
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                WlmstCompany::query()->truncate();
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');

                $response = successRes('Company Master Table Truncate');
            } else if ($filetype == 'CATEGORY') {

                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                WlmstItemCategory::query()->truncate();
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');

                $response = successRes('Category Master Table Truncate');
            } else if ($filetype == 'GROUP') {

                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                Wlmst_ItemGroup::query()->truncate();
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');

                $response = successRes('Group Master Table Truncate');
            } else if ($filetype == 'SUBGROUP') {

                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                WlmstItemSubgroup::query()->truncate();
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');

                $response = successRes('Subgroup Master Table Truncate');
            } else if ($filetype == 'ITEM') {

                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                WlmstItem::query()->truncate();
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');

                $response = successRes('Item Master Table Truncate');
            } else if ($filetype == 'ITEMPRICE') {

                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                Wlmst_ItemPrice::query()->truncate();
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');

                $response = successRes('Item Price Master Table Truncate');
            } else if ($filetype == 'QUOTATION') {

                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                Wltrn_Quotation::query()->truncate();
                Wltrn_QuotItemdetail::query()->truncate();
                Wlmst_QuotationError::query()->truncate();
                wlmst_user_created_board_log::query()->truncate();
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');

                $response = successRes('Quotation & Quotation Item Detail & Quotation Error Table Truncate');
            } else if ($filetype == 'SUGGESTION') {

                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                Wlmst_NameSuggestion::query()->truncate();
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');

                $response = successRes('Quotation & Quotation Item Detail & Quotation Error Table Truncate');
            } else if ($filetype == 'TARGET') {

                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                Wlmst_target::query()->truncate();
                Wlmst_targetdetail::query()->truncate();
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');

                $response = successRes('Target Table Truncate');
            } else if ($filetype == 'CLIENT') {

                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                Wlmst_Client::query()->truncate();
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
                $response = successRes('Wlmst Client Table Truncate');
            }
            else if ($filetype == 'LEADDEAL') {

                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                LeadAccountContact::query()->truncate();
                LeadCall::query()->truncate();
                LeadClosing::query()->truncate();
                LeadCompetitor::query()->truncate();
                LeadContact::query()->truncate();
                LeadFile::query()->truncate();
                LeadMeetingParticipant::query()->truncate();
                LeadMeeting::query()->truncate();
                LeadSource::query()->truncate();
                LeadTask::query()->truncate();
                LeadTimeline::query()->truncate();
                LeadUpdate::query()->truncate();
                Lead::query()->truncate();
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
                $response = successRes('Lead All Table Truncate');
            }
        } catch (Exception $e) {
            $error_message = $e->getMessage();
            $response = errorRes("Perameater Passing Error : " . $error_message);
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function sendNotificationFirebase(Request $request)
    {
        $FcnToken = array(
            // 'chmfkYxbQcW6qSpaprYwcr:APA91bHH6d3TDhZ6qHXPiOxUFOa8v-mXGqYSz9znO9krF1Xvi2OBu2L0tjAKI84mn_HsKlNkdazWan5mKQ3f1FRb_p45s_PqJy_s1dzB_R-Z0ows5FPu4GhrNyNjXun3lsxfxVbwXlec',
            $request->q_app_noti_token,
        );
        $noti_responce = sendNotificationTOAndroid($request->q_app_noti_title, $request->q_app_noti_message, $FcnToken,"","");

        return response()->json($noti_responce)->header('Content-Type', 'application/json');
    }
}
