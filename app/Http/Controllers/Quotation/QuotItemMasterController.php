<?php

namespace App\Http\Controllers\Quotation;

use App\Http\Controllers\Controller;
use App\Models\WlmstItem;
use App\Models\WlmstItemCategory;
use App\Models\wlmst_item_details;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class QuotItemMasterController extends Controller
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
        $data['title'] = "Quotation Item Master ";
        return view('quotation/master/item/item', compact('data'));
    }

    public function searchCategory(Request $request)
    {

        $StateList = array();
        $StateList = WlmstItemCategory::select('id', 'itemcategoryname as text');
        $StateList->where('itemcategoryname', 'like', "%" . $request->q . "%");
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
            0 => 'wlmst_items.id',
            1 => 'wlmst_items.itemname',
            2 => 'wlmst_item_categories.itemcategoryname',
        );

        $columns = array(
            0 => 'wlmst_items.id',
            1 => 'wlmst_items.itemname',
            2 => 'wlmst_items.shortname',
            3 => 'wlmst_items.isactive',
            4 => 'wlmst_items.itemcategory_id',
            5 => 'wlmst_item_categories.itemcategoryname',
            6 => 'wlmst_items.module',
        );

        $recordsTotal = WlmstItem::count();
        $recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.

        $query = WlmstItem::query();
        // $query = DB::table('wlmst_item_groups');
        // $query->leftJoin('wlmst_item_categories', 'wlmst_item_categories.id', '=', 'wlmst_items.itemcategory_id');
        $query->leftjoin("wlmst_item_categories", DB::raw("FIND_IN_SET(wlmst_items.itemcategory_id,wlmst_item_categories.id)"), ">", DB::raw("'0'"));
        // $query->whereRaw("find_in_set(" . $request->company_id . ",wlmst_item_subgroups.company_id)");
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
        $data2 = json_decode(json_encode($data), true);

        if ($isFilterApply == 1) {
            $recordsFiltered = count($data);
        }

        foreach ($data as $key => $value) {

            $data[$key]['id'] = highlightString($data[$key]['id'],$search_value);
            $data[$key]['itemname'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">' .  highlightString($value['itemname'],$search_value) . '</a></h5>
            <p class="text-muted mb-0">' .  highlightString($value['itemcategoryname'],$search_value) . '</p>';

            $data[$key]['module'] = "<p>" .  highlightString($value['module'],$search_value) . '</p>';

            $data[$key]['isactive'] = getMainMasterStatusLable($value['isactive']);

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

    public function uploadImageAdditionalInfo(Request $request)
    {

        $uploadedFile1 = "";

        if ($request->hasFile('upload')) {

            $folderPathImage = '/ckeditor';
            $fileObject1 = $request->file('upload');

            $extension = $fileObject1->getClientOriginalExtension();
            $fileName1 = time() . mt_rand(10000, 99999) . '.' . $extension;

            $destinationPath = public_path($folderPathImage);

            $fileObject1->move($destinationPath, $fileName1);

            if (File::exists(public_path($folderPathImage . "/" . $fileName1))) {

                $uploadedFile1 = $folderPathImage . "/" . $fileName1;

                $spaceUploadResponse = uploadFileOnSpaces(public_path($uploadedFile1), $uploadedFile1); //Live
                if ($spaceUploadResponse != 1) {
                    $uploadedFile1 = "";
                } else {
                    unlink(public_path($uploadedFile1));
                }
            }
        }

        $response =  json_encode([
            'default' => getSpaceFilePath($uploadedFile1),
            '500' => getSpaceFilePath($uploadedFile1)
        ]);

        return $response;
    }

    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'q_item_master_id' => ['required'],
            'q_item_master_name' => ['required'],
            // 'q_item_master_app_display_name' => ['required'],
            'q_item_master_code' => ['required'],
            'q_item_master_module' => ['required'],
            'q_item_master_max_module' => ['required'],
            'q_item_master_status' => ['required']
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
            // } else {
            $uploadedFile1 = "";

            if ($request->hasFile('q_item_image')) {

                $validator = Validator::make($request->all(), [
                    'q_item_image' => ['required'],
                ]);

                // create for add 
                if ($validator->fails()) {
                    $response = array();
                    $response['status'] = 0;
                    $response['msg'] = "Please Upload Svg File ";
                    $response['statuscode'] = 400;
                    $response['data'] = $validator->errors();

                    return response()->json($response)->header('Content-Type', 'application/json');
                } else {



                    $folderPathImage = '/quotation/item';
                    $fileObject1 = $request->file('q_item_image');

                    $extension = $fileObject1->getClientOriginalExtension();
                    $fileName1 = time() . mt_rand(10000, 99999) . '.' . $extension;

                    $destinationPath = public_path($folderPathImage);

                    $fileObject1->move($destinationPath, $fileName1);

                    if (File::exists(public_path($folderPathImage . "/" . $fileName1))) {

                        $uploadedFile1 = $folderPathImage . "/" . $fileName1;

                        $spaceUploadResponse = uploadFileOnSpaces(public_path($uploadedFile1), $uploadedFile1);
                        if ($spaceUploadResponse != 1) {
                            $uploadedFile1 = "";
                        } else {
                            unlink(public_path($uploadedFile1));
                        }
                    }
                }
            }

            if ($request->q_item_master_id != 0) {
                $MainMaster = WlmstItem::find($request->q_item_master_id);
                $MainMaster->updateby = Auth::user()->id;
                $MainMaster->updateip = $request->ip();
            } else {
                $MainMaster = new WlmstItem();
                $MainMaster->entryby = Auth::user()->id;
                $MainMaster->entryip = $request->ip();
            }

            $MainMaster->itemname = $request->q_item_master_name;
            $MainMaster->app_display_name = $request->q_item_master_app_display_name;
            $MainMaster->shortname = $request->q_item_master_code;
            $MainMaster->itemcategory_id = implode(",", $request->q_item_master_category_id);
            $MainMaster->module = $request->q_item_master_module;
            $MainMaster->max_module = $request->q_item_master_max_module;
            $MainMaster->app_sequence = $request->q_item_master_sequence;
            $MainMaster->isactive = $request->q_item_master_status;
            $MainMaster->igst_per = $request->q_item_master_igst;
            $MainMaster->cgst_per = $request->q_item_master_cgst;
            $MainMaster->sgst_per = $request->q_item_master_sgst;
            $MainMaster->remark = isset($request->q_item_master_remark) ? $request->q_item_master_remark : '';
            $MainMaster->additional_info = isset($request->additional_info) ? $request->additional_info : '';
            if ($uploadedFile1 != "") {
                $MainMaster->image = $uploadedFile1;
            }
            $MainMaster->is_special = $request->q_item_master_isspecial_value;
            $MainMaster->additional_remark = $request->q_item_master_additional_remark;


            $MainMaster->save();


            if ($MainMaster) {
                $ItemDetails = wlmst_item_details::query()->where('item_id', $request->q_item_master_id)->first();
                if (!$ItemDetails) {
                    $ItemDetails = new wlmst_item_details();
                }
                $ItemDetails->item_id = $MainMaster->id;
                $ItemDetails->touch_on_off = $request->q_item_master_touch_on_off;
                $ItemDetails->touch_fan_regulator = $request->q_item_master_touch_fan_regulator;
                $ItemDetails->wl_plug = $request->q_item_master_wl_plug;
                $ItemDetails->special = $request->q_item_master_special;
                $ItemDetails->wl_accessories = $request->q_item_master_wl_accessories;
                $ItemDetails->normal_switch = $request->q_item_master_normal_switch;
                $ItemDetails->normal_fan_regulator = $request->q_item_master_normal_fan_regulator;
                $ItemDetails->other_plug = $request->q_item_master_other_plug;
                $ItemDetails->other = $request->q_item_master_other;
                $ItemDetails->save();

                if ($request->q_group_master_id != 0) {

                    $response = successRes("Successfully saved item master");

                    $debugLog = array();
                    $debugLog['name'] = "quot-item-master-edit";
                    $debugLog['description'] = "quotation item master #" . $MainMaster->id . "(" . $MainMaster->itemname . ")" . " has been updated ";
                    saveDebugLog($debugLog);
                } else {
                    $response = successRes("Successfully added item master");

                    $debugLog = array();
                    $debugLog['name'] = "quot-item-master-add";
                    $debugLog['description'] = "quotation item master #" . $MainMaster->id . "(" . $MainMaster->itemname . ") has been added ";
                    saveDebugLog($debugLog);
                }
            }


            // }

            return response()->json($response)->header('Content-Type', 'application/json');
        }
    }

    public function detail(Request $request)
    {

        $MainMaster = WlmstItem::find($request->id);

        if ($MainMaster) {
            $response = successRes("Successfully get quotation item master");
            $query_category = DB::table('wlmst_item_categories');
            $query_category->select('wlmst_item_categories.id AS id', 'wlmst_item_categories.itemcategoryname AS text');
            $query_category->whereIn('wlmst_item_categories.id', explode(",", $MainMaster['itemcategory_id']));
            $data['MainMaster'] = $MainMaster;
            $data['MainMaster']['category'] = $query_category->get();

            $ItemDetails = wlmst_item_details::query()->where('item_id', $request->id)->first();
            if ($ItemDetails) {
                $data['item_details'] = $ItemDetails;
            } else {
                $data['item_details'] = 0;
            }
            $data['MainMaster']['image'] = getSpaceFilePath($MainMaster->image);
            $response['data'] = $data;
        } else {
            $response = errorRes("Invalid id");
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function delete(Request $request)
    {

        $Item = WlmstItem::find($request->id);
        // $query_company->whereIn('wlmst_companies.id', explode(",", $MainMaster['company_id']));
        if ($Item) {

            $debugLog = array();
            $debugLog['name'] = "quot-item-delete";
            $debugLog['description'] = "quot item #" . $Item->id . "(" . $Item->itemname . ") has been deleted";
            saveDebugLog($debugLog);

            $Item->delete();
        }
        $response = successRes("Successfully delete Item");
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function export(Request $request)
    {
        $columns = array(
            'wlmst_items.id',
            'wlmst_items.itemname',
            'wlmst_items.itemcategory_id',
            'category.itemcategoryname',
            'wlmst_items.shortname',
            'wlmst_items.module',
            'wlmst_items.image',
            'wlmst_items.is_special',
            'wlmst_items.additional_remark',
            'wlmst_items.sgst_per',
            'wlmst_items.cgst_per',
            'wlmst_items.igst_per',
            'wlmst_items.app_display_name',
            'wlmst_items.app_sequence',
            'wlmst_items.max_module',

            'wlmst_items.isactive',
            'wlmst_items.remark',
            'wlmst_items.created_at',
            'wlmst_items.entryby',
            DB::raw('CONCAT(entry_user.first_name," ",entry_user.last_name) as entrybyname'),
            'wlmst_items.entryip',
            'wlmst_items.updated_at',
            'wlmst_items.updateby',
            DB::raw('CONCAT(update_user.first_name," ",update_user.last_name) as updatebyname'),
            'wlmst_items.updateip',
        );

        $query = WlmstItem::query();
        $query->select($columns);
        $query->leftJoin('wlmst_item_categories as category', 'category.id', '=', 'wlmst_items.itemcategory_id');
        $query->leftJoin('users as entry_user', 'entry_user.id', '=', 'wlmst_items.entryby');
        $query->leftJoin('users as update_user', 'update_user.id', '=', 'wlmst_items.updateby');
        $data = $query->get();

        $headers = array("#ID", "Item Name", "Category Id", "Category Name", "Short Name", "Module", "Image", "Is Special", "Additional Remark", "Sgst Per", "Cgst Per", "Igst Per", "App Display Name", "App Sequence", "Max Module", "Status", "Status Label", "Remark", "Created At", "Entry By", "Entry By Name", "Entry Ip", "Updated At", "Update By", "Update By Name", "Update Ip");

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="quotation-item-data.csv"');

        $fp = fopen('php://output', 'wb');

        fputcsv($fp, $headers);

        foreach ($data as $key => $value) {

            $lineVal = array(
                $value->id,
                $value->itemname,
                $value->itemcategory_id,
                $value->itemcategoryname,
                $value->shortname,
                $value->module,
                $value->image,
                $value->is_special,
                $value->additional_remark,
                $value->sgst_per,
                $value->cgst_per,
                $value->igst_per,
                $value->app_display_name,
                $value->app_sequence,
                $value->max_module,
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
