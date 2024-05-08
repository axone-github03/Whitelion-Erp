<?php

namespace App\Http\Controllers\Quotation;

use App\Http\Controllers\Controller;

use App\Models\WlmstItem;
use App\Models\WlmstCompany;
use Illuminate\Http\Request;
use App\Models\Wlmst_ItemGroup;
use App\Models\Wlmst_ItemPriceLog;
use App\Models\Wlmst_ItemPrice;
use App\Models\WlmstItemSubgroup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Database\QueryException;
use App\Models\WlmstQuotDiscountFlow;

use function PHPUnit\Framework\isEmpty;

class QuotItemPriceMasterController extends Controller
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
        $data['title'] = "Quotation Item Price Master ";
        return view('quotation/master/itemprice/index', compact('data'));
    }

    public function searchCategoryType(Request $request)
    {
        $CompanyList = array();
        $CompanyList[0]['id'] = 'POSH';
        $CompanyList[0]['text'] = 'Posh';
        $CompanyList[1]['id'] = 'QUARTZ';
        $CompanyList[1]['text'] = 'Quartz';

        $response = array();
        $response['results'] = $CompanyList;
        $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function searchMultiFlow(Request $request)
    {
        $CompanyList = array();
        $CompanyList = WlmstQuotDiscountFlow::select('id', 'name as text');
        $CompanyList->where('name', 'like', "%" . $request->q . "%");
        // $CompanyList->limit(5);
        $CompanyList = $CompanyList->get();
        
        $response = array();
        $response['results'] = $CompanyList;
        $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function searchCompany(Request $request)
    {
        $CompanyList = array();
        $CompanyList = WlmstCompany::select('id', 'companyname as text');
        $CompanyList->where('companyname', 'like', "%" . $request->q . "%");
        // $CompanyList->limit(5);
        $CompanyList = $CompanyList->get();

        $response = array();
        $response['results'] = $CompanyList;
        $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function searchItemGroup(Request $request)
    {
        $GroupList = array();
        $GroupList = Wlmst_ItemGroup::select('id', 'itemgroupname as text');
        // $GroupList->where('company_id', $request->company_id);
        $GroupList->where('itemgroupname', 'like', "%" . $request->q . "%");
        // $GroupList->limit(5);
        $GroupList = $GroupList->get();

        $response = array();
        $response['results'] = $GroupList;
        $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function searchItemSubGroup(Request $request)
    {
        $GroupList = array();
        $GroupList = WlmstItemSubgroup::select('id', 'itemsubgroupname as text');
        if ($request->filled('company_id')) {
            $GroupList->whereRaw("find_in_set(" . $request->company_id . ",company_id)");
        }
        if ($request->filled('group_id')) {
            $GroupList->where('itemgroup_id', $request->group_id);
        }

        $GroupList->where('itemsubgroupname', 'like', "%" . $request->q . "%");
        // $GroupList->limit(5);
        $GroupList = $GroupList->get();

        $response = array();
        $response['results'] = $GroupList;
        $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');
    }


    public function searchItem(Request $request)
    {
        $GroupList = array();
        // $GroupList = WlmstItem::select('id', 'itemname as text');
        $GroupList = WlmstItem::selectRaw('CONCAT(wlmst_items.itemname, " - ", wlmst_item_categories.itemcategoryname) AS text,wlmst_items.id');
        $GroupList->leftJoin('wlmst_item_categories', 'wlmst_item_categories.id', '=', 'wlmst_items.itemcategory_id');

        // $GroupList->where('company_id', $request->company_id);
        $GroupList->where('wlmst_items.itemname', 'like', "%" . $request->q . "%");
        $GroupList->limit(40);
        $GroupList = $GroupList->get();

        $response = array();
        $response['results'] = $GroupList;
        $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function ajax(Request $request)
    {
        //DB::enableQueryLog();

        $searchColumns = array(
            0 => 'wlmst_item_prices.id',
            1 => 'wlmst_items.itemname',
            2 => 'wlmst_companies.companyname',
            3 => 'wlmst_item_groups.itemgroupname',
            4 => 'wlmst_item_subgroups.itemsubgroupname',
            5 => 'wlmst_item_prices.code'
        );

        $columns = array(
            0 => 'wlmst_item_prices.id',
            1 => 'wlmst_item_prices.company_id',
            2 => 'wlmst_item_prices.itemgroup_id',
            3 => 'wlmst_item_prices.itemsubgroup_id',
            4 => 'wlmst_item_prices.item_id',
            5 => 'wlmst_item_prices.mrp',
            6 => 'wlmst_item_prices.code',
            7 => 'wlmst_item_prices.discount',
            8 => 'wlmst_companies.companyname',
            9 => 'wlmst_item_groups.itemgroupname',
            10 => 'wlmst_item_subgroups.itemsubgroupname',
            11 => 'wlmst_items.itemname',
            12 => 'wlmst_item_prices.isactive',
            13 => 'wlmst_item_prices.image',
        );

        $recordsTotal = Wlmst_ItemPrice::query();
        $recordsTotal->select($columns);
        $recordsTotal->leftJoin('wlmst_companies', 'wlmst_companies.id', '=', 'wlmst_item_prices.company_id');
        $recordsTotal->leftJoin('wlmst_item_groups', 'wlmst_item_groups.id', '=', 'wlmst_item_prices.itemgroup_id');
        $recordsTotal->leftJoin('wlmst_item_subgroups', 'wlmst_item_subgroups.id', '=', 'wlmst_item_prices.itemsubgroup_id');
        $recordsTotal->leftJoin('wlmst_items', 'wlmst_items.id', '=', 'wlmst_item_prices.item_id');
        $recordsTotal = json_decode(json_encode($recordsTotal->get()), true);
        $recordsTotal = count($recordsTotal);

        $recordsFiltered = Wlmst_ItemPrice::query();
        $recordsFiltered->select($columns);
        if ($request->item_subgroup) {
            $recordsFiltered->where('wlmst_item_prices.itemsubgroup_id', $request->item_subgroup);
        }
        $recordsFiltered->leftJoin('wlmst_companies', 'wlmst_companies.id', '=', 'wlmst_item_prices.company_id');
        $recordsFiltered->leftJoin('wlmst_item_groups', 'wlmst_item_groups.id', '=', 'wlmst_item_prices.itemgroup_id');
        $recordsFiltered->leftJoin('wlmst_item_subgroups', 'wlmst_item_subgroups.id', '=', 'wlmst_item_prices.itemsubgroup_id');
        $recordsFiltered->leftJoin('wlmst_items', 'wlmst_items.id', '=', 'wlmst_item_prices.item_id');
        if (isset($request['search']['value'])) {
            $isFilterApply = 1;
            $search_value = $request['search']['value'];
            $recordsFiltered->where(function ($recordsFiltered) use ($search_value, $searchColumns) {
                for ($i = 0; $i < count($searchColumns); $i++) {
                    if ($i == 0) {
                        $recordsFiltered->where($searchColumns[$i], 'like', "%" . $search_value . "%");
                    } else {
                        $recordsFiltered->orWhere($searchColumns[$i], 'like', "%" . $search_value . "%");
                    }
                }
            });
        }
        $recordsFiltered = json_decode(json_encode($recordsFiltered->get()), true);
        $recordsFiltered = count($recordsFiltered);



        $query = Wlmst_ItemPrice::query();
        // $query = DB::table('wlmst_item_groups');
        $query->leftJoin('wlmst_companies', 'wlmst_companies.id', '=', 'wlmst_item_prices.company_id');
        $query->leftJoin('wlmst_item_groups', 'wlmst_item_groups.id', '=', 'wlmst_item_prices.itemgroup_id');
        $query->leftJoin('wlmst_item_subgroups', 'wlmst_item_subgroups.id', '=', 'wlmst_item_prices.itemsubgroup_id');
        $query->leftJoin('wlmst_items', 'wlmst_items.id', '=', 'wlmst_item_prices.item_id');
        if ($request->item_subgroup) {
            $query->where('wlmst_item_prices.itemsubgroup_id', $request->item_subgroup);
        }
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

            $data[$key]['id'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">' .  highlightString($data[$key]['id'],$search_value) . '</a></h5>';
            $data[$key]['item_name_company'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">' .  highlightString($data[$key]['itemname'],$search_value) . '</a></h5>
            <p class="text-muted mb-0">' .  highlightString($data[$key]['companyname'],$search_value) . '</p>';

            $data[$key]['item_group_subgroup'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">' .  highlightString($data[$key]['itemgroupname'],$search_value) . '</a></h5>
            <p class="text-muted mb-0">' .  highlightString($data[$key]['itemsubgroupname'],$search_value) . '</p>';

            $data[$key]['code'] = "<p>" .  highlightString($data[$key]['code'],$search_value) . '</p>';
            $data[$key]['mrp'] = "<p>" .  highlightString($data[$key]['mrp'],$search_value) . '</p>';

            // $data[$key]['discount'] = "<p>" . $data[$key]['discount'] . '</p>';
            if($value['image'] == null){
                $image = '<img class="product-img" src="https://erp.whitelion.in/assets/images/favicon.ico" />';
            }else{
                $image = '<img class="product-img" src="' . getSpaceFilePath($value['image']) . '" />';
            }
            $data[$key]['image'] = $image;

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

    public function save(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'q_price_master_id' => ['required'],
            'q_price_company_id' => ['required'],
            'q_price_group_id' => ['required'],
            'q_price_item_id' => ['required'],
            'q_price_mrp' => ['required'],
            'q_price_code' => ['required'],
            'q_price_effectivedate' => ['required'],
            'q_price_item_type' => ['required'],
            // 'q_price_item_flow' => ['required'],
        ]);

        // create for add 
        if ($validator->fails()) {
            $response = array();
            $response['status'] = 0;
            $response['msg'] = "The request could not be understood by the server due to malformed syntax";
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors();

            return response()->json($response)->header('Content-Type', 'application/json');
        } else {

            $uploadedFile1 = "";

            if ($request->hasFile('q_price_item_image')) {

                $validator = Validator::make($request->all(), [
                    'q_price_item_image' => ['required'],
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
                    $fileObject1 = $request->file('q_price_item_image');

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

            $alreadyCode = Wlmst_ItemPrice::query();

            if ($request->q_price_master_id != 0) {
                $alreadyCode->where('code', $request->q_price_code);
                $alreadyCode->where('id', '!=', $request->q_price_master_id);
            } else {
                $alreadyCode->where('code', $request->q_price_code);
            }

            $alreadyCode = $alreadyCode->first();

            if ($alreadyCode) {
                $response = errorRes("already Item Code exits, Try with another Item Code");
            } else {

                if ($request->q_price_master_id != 0) {

                    $PriceMaster = Wlmst_ItemPrice::find($request->q_price_master_id);
                    $PriceMaster->updateby = Auth::user()->id;
                    $PriceMaster->updateip = $request->ip();
                } else {

                    $PriceMaster = new Wlmst_ItemPrice();
                    $PriceMaster->entryby = Auth::user()->id;
                    $PriceMaster->entryip = $request->ip();
                }

                // if ($request->hasFile('q_price_item_image')) {
                //     $imageName = uniqid() . '_' . $request->q_price_code . '.' . $request->q_price_item_image->extension();
                //     // Public Folder
                //     $request->q_price_item_image->move(public_path('item_image'), $imageName);
                //     $PriceMaster->image = $imageName;
                // }



                $PriceMaster->company_id = isset($request->q_price_company_id) ? $request->q_price_company_id : '0';
                $PriceMaster->itemgroup_id = isset($request->q_price_group_id) ? $request->q_price_group_id : '0';
                $PriceMaster->itemsubgroup_id = isset($request->q_price_subgroup_id) ? $request->q_price_subgroup_id : '0';
                $PriceMaster->item_id = isset($request->q_price_item_id) ? $request->q_price_item_id : '0';

                if ($uploadedFile1 != "") {
                    $PriceMaster->image = $uploadedFile1;
                }
                if ($uploadedFile1 != "") {
                    $PriceMaster->thumb_image = $uploadedFile1;
                }

                $PriceMaster->code = $request->q_price_code;
                $PriceMaster->mrp = $request->q_price_mrp;
                $PriceMaster->discount = $request->q_price_discount;
                $PriceMaster->channel_partners_discount = $request->q_price_channel_partners_discount;
                $PriceMaster->effectivedate = date('Y-m-d', strtotime($request->q_price_effectivedate));
                $PriceMaster->isactive = $request->q_price_status;
                $PriceMaster->item_type = implode(",", $request->q_price_item_type);
                $PriceMaster->flow_ids = isset($request->q_price_item_flow) ? implode(",", $request->q_price_item_flow) : '';

                $PriceMaster->remark = isset($request->q_price_remark) ? $request->q_price_remark : '';

                $PriceMaster->save();
                if ($PriceMaster) {

                    $PriceMasterLog = new Wlmst_ItemPriceLog();
                    $PriceMasterLog->price_id = $PriceMaster->id;
                    $PriceMasterLog->company_id = $PriceMaster->company_id;
                    $PriceMasterLog->itemgroup_id = $PriceMaster->itemgroup_id;
                    $PriceMasterLog->itemsubgroup_id = $PriceMaster->itemsubgroup_id;
                    $PriceMasterLog->item_id = $PriceMaster->item_id;
                    $PriceMasterLog->code = $PriceMaster->code;
                    $PriceMasterLog->mrp = $PriceMaster->mrp;
                    $PriceMasterLog->discount = $PriceMaster->discount;
                    $PriceMasterLog->channel_partners_discount = $PriceMaster->channel_partners_discount;
                    $PriceMasterLog->effectivedate = $PriceMaster->effectivedate;
                    $PriceMasterLog->item_type = $PriceMaster->item_type;
                    $PriceMasterLog->image = $PriceMaster->image;
                    $PriceMasterLog->thumb_image = $PriceMaster->thumb_image;
                    $PriceMasterLog->isactive = $PriceMaster->isactive;
                    $PriceMasterLog->remark = "remark";
                    $PriceMasterLog->entryby = Auth::user()->id;
                    $PriceMasterLog->entryip = $request->ip();
                    $PriceMasterLog->save();

                    if ($request->q_price_master_id != 0) {

                        $response = successRes("Successfully saved item price master");

                        $debugLog = array();
                        $debugLog['name'] = "quot-item-price-master-edit";
                        $debugLog['description'] = "quotation item price master #" . $PriceMaster->id . "(" . $PriceMaster->code . ")" . " has been updated Log #".$PriceMasterLog->id;
                        saveDebugLog($debugLog);

                    } else {

                        $response = successRes("Successfully added item price master");
                        $debugLog = array();
                        $debugLog['name'] = "quot-item-price-master-add";
                        $debugLog['description'] = "quotation item price master #" . $PriceMaster->id . "(" . $PriceMaster->code . ") has been added Log #".$PriceMasterLog->id;
                        saveDebugLog($debugLog);
                    }
                }
            }

            return response()->json($response)->header('Content-Type', 'application/json');
        }
    }

    public function ajaxItemPriceUpdate(Request $request)
    {
        $searchColumns = array(
            0 => 'wlmst_item_prices.id',
            1 => 'wlmst_items.itemname',
            2 => 'wlmst_companies.companyname',
            3 => 'wlmst_item_groups.itemgroupname',
            4 => 'wlmst_item_subgroups.itemsubgroupname'
        );

        $columns = array(
            0 => 'wlmst_item_prices.id',
            1 => 'wlmst_item_prices.company_id',
            2 => 'wlmst_item_prices.itemgroup_id',
            3 => 'wlmst_item_prices.itemsubgroup_id',
            4 => 'wlmst_item_prices.item_id',
            5 => 'wlmst_item_prices.mrp',
            6 => 'wlmst_item_prices.code',
            7 => 'wlmst_item_prices.discount',
            8 => 'wlmst_companies.companyname',
            9 => 'wlmst_item_groups.itemgroupname',
            10 => 'wlmst_item_subgroups.itemsubgroupname',
            11 => 'wlmst_items.itemname',
            12 => 'wlmst_item_prices.isactive',
        );

        $recordsTotal = Wlmst_ItemPrice::query();
        $recordsTotal->select($columns);
        $recordsTotal->leftJoin('wlmst_companies', 'wlmst_companies.id', '=', 'wlmst_item_prices.company_id');
        $recordsTotal->leftJoin('wlmst_item_groups', 'wlmst_item_groups.id', '=', 'wlmst_item_prices.itemgroup_id');
        $recordsTotal->leftJoin('wlmst_item_subgroups', 'wlmst_item_subgroups.id', '=', 'wlmst_item_prices.itemsubgroup_id');
        $recordsTotal->leftJoin('wlmst_items', 'wlmst_items.id', '=', 'wlmst_item_prices.item_id');
        $recordsTotal = json_decode(json_encode($recordsTotal->get()), true);
        $recordsTotal = count($recordsTotal);

        $recordsFiltered = Wlmst_ItemPrice::query();
        $recordsFiltered->select($columns);
        if ($request->filled('filter_company')) {
            $recordsFiltered->where('wlmst_item_prices.company_id', $request->filter_company);
        }
        if ($request->filled('filter_group')) {
            $recordsFiltered->where('wlmst_item_prices.itemgroup_id', $request->filter_group);
        }
        if ($request->filled('filter_subgroup')) {
            $recordsFiltered->where('wlmst_item_prices.itemsubgroup_id', $request->filter_subgroup);
        }
        if ($request->filled('filter_item')) {
            $recordsFiltered->where('wlmst_item_prices.item_id', $request->filter_item);
        }
        $recordsFiltered->leftJoin('wlmst_companies', 'wlmst_companies.id', '=', 'wlmst_item_prices.company_id');
        $recordsFiltered->leftJoin('wlmst_item_groups', 'wlmst_item_groups.id', '=', 'wlmst_item_prices.itemgroup_id');
        $recordsFiltered->leftJoin('wlmst_item_subgroups', 'wlmst_item_subgroups.id', '=', 'wlmst_item_prices.itemsubgroup_id');
        $recordsFiltered->leftJoin('wlmst_items', 'wlmst_items.id', '=', 'wlmst_item_prices.item_id');
        if (isset($request['search']['value'])) {
            $isFilterApply = 1;
            $search_value = $request['search']['value'];
            $recordsFiltered->where(function ($recordsFiltered) use ($search_value, $searchColumns) {
                for ($i = 0; $i < count($searchColumns); $i++) {
                    if ($i == 0) {
                        $recordsFiltered->where($searchColumns[$i], 'like', "%" . $search_value . "%");
                    } else {
                        $recordsFiltered->orWhere($searchColumns[$i], 'like', "%" . $search_value . "%");
                    }
                }
            });
        }
        $recordsFiltered = json_decode(json_encode($recordsFiltered->get()), true);
        $recordsFiltered = count($recordsFiltered);

        $query = Wlmst_ItemPrice::query();
        // $query = DB::table('wlmst_item_groups');
        $query->leftJoin('wlmst_companies', 'wlmst_companies.id', '=', 'wlmst_item_prices.company_id');
        $query->leftJoin('wlmst_item_groups', 'wlmst_item_groups.id', '=', 'wlmst_item_prices.itemgroup_id');
        $query->leftJoin('wlmst_item_subgroups', 'wlmst_item_subgroups.id', '=', 'wlmst_item_prices.itemsubgroup_id');
        $query->leftJoin('wlmst_items', 'wlmst_items.id', '=', 'wlmst_item_prices.item_id');
        if ($request->filled('filter_company')) {
            $query->where('wlmst_item_prices.company_id', $request->filter_company);
        }
        if ($request->filled('filter_group')) {
            $query->where('wlmst_item_prices.itemgroup_id', $request->filter_group);
        }
        if ($request->filled('filter_subgroup')) {
            $query->where('wlmst_item_prices.itemsubgroup_id', $request->filter_subgroup);
        }
        if ($request->filled('filter_item')) {
            $query->where('wlmst_item_prices.item_id', $request->filter_item);
        }
        $query->select($columns);
        $query->limit($request->length);
        $query->offset($request->start);
        $query->orderBy($columns[$request['order'][0]['column']], $request['order'][0]['dir']);
        $isFilterApply = 0;

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

        $data = json_decode(json_encode($data), true);

        if ($isFilterApply == 1) {
            $recordsFiltered = count($data);
        }

        foreach ($data as $key => $value) {

            $data[$key]['id'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">' . $data[$key]['id'] . '</a></h5>';
            $data[$key]['item'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">' . $data[$key]['itemname'] . '</a></h5>
            <p class="text-muted mb-0">' . $data[$key]['companyname'] . '</p>';

            $data[$key]['brand'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">' . $data[$key]['itemgroupname'] . '</a></h5>
            <p class="text-muted mb-0">' . $data[$key]['itemsubgroupname'] . '</p>';

            $data[$key]['code'] = "<p>" . $data[$key]['code'] . '</p>';
            $data[$key]['mrp'] = '<input type="number" tabindex="' . ($key + 1) . '"  class="form-control" name="input_price_text" id="' . $value['id'] . '" value="' .   $value['mrp'] . '"  />';
        }

        $jsonData = array(
            "draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($recordsTotal), // total number of records
            "recordsFiltered" => intval($recordsFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $data, // total data array
        );

        return $jsonData;
    }

    public function ajaxItemFlowUpdate(Request $request)
    {
        $searchColumns = array(
            0 => 'wlmst_item_prices.id',
            1 => 'wlmst_items.itemname',
            2 => 'wlmst_companies.companyname',
            3 => 'wlmst_item_groups.itemgroupname',
            4 => 'wlmst_item_subgroups.itemsubgroupname'
        );

        $columns = array(
            0 => 'wlmst_item_prices.id',
            1 => 'wlmst_item_prices.company_id',
            2 => 'wlmst_item_prices.itemgroup_id',
            3 => 'wlmst_item_prices.itemsubgroup_id',
            4 => 'wlmst_item_prices.item_id',
            5 => 'wlmst_item_prices.mrp',
            6 => 'wlmst_item_prices.code',
            7 => 'wlmst_item_prices.discount',
            8 => 'wlmst_companies.companyname',
            9 => 'wlmst_item_groups.itemgroupname',
            10 => 'wlmst_item_subgroups.itemsubgroupname',
            11 => 'wlmst_items.itemname',
            12 => 'wlmst_item_prices.isactive',
            13 => 'wlmst_item_prices.flow_ids',
            14 => 'wlmst_quot_discount_flow.name',
        );

        $recordsTotal = Wlmst_ItemPrice::query();
        $recordsTotal->select($columns);
        $recordsTotal->leftJoin('wlmst_companies', 'wlmst_companies.id', '=', 'wlmst_item_prices.company_id');
        $recordsTotal->leftJoin('wlmst_item_groups', 'wlmst_item_groups.id', '=', 'wlmst_item_prices.itemgroup_id');
        $recordsTotal->leftJoin('wlmst_item_subgroups', 'wlmst_item_subgroups.id', '=', 'wlmst_item_prices.itemsubgroup_id');
        $recordsTotal->leftJoin('wlmst_items', 'wlmst_items.id', '=', 'wlmst_item_prices.item_id');
        $recordsTotal->leftJoin('wlmst_quot_discount_flow', 'wlmst_quot_discount_flow.id', '=', 'wlmst_item_prices.flow_ids');
        $recordsTotal = json_decode(json_encode($recordsTotal->get()), true);
        $recordsTotal = count($recordsTotal);
        $recordsFiltered = Wlmst_ItemPrice::query();
        $recordsFiltered->select($columns);
        if ($request->filled('filter_company')) {
            $recordsFiltered->where('wlmst_item_prices.company_id', $request->filter_company);
        }
        if ($request->filled('filter_group')) {
            $recordsFiltered->where('wlmst_item_prices.itemgroup_id', $request->filter_group);
        }
        if ($request->filled('filter_subgroup')) {
            $recordsFiltered->where('wlmst_item_prices.itemsubgroup_id', $request->filter_subgroup);
        }
        if ($request->filled('filter_item')) {
            $recordsFiltered->where('wlmst_item_prices.item_id', $request->filter_item);
        }
        $recordsFiltered->leftJoin('wlmst_companies', 'wlmst_companies.id', '=', 'wlmst_item_prices.company_id');
        $recordsFiltered->leftJoin('wlmst_item_groups', 'wlmst_item_groups.id', '=', 'wlmst_item_prices.itemgroup_id');
        $recordsFiltered->leftJoin('wlmst_item_subgroups', 'wlmst_item_subgroups.id', '=', 'wlmst_item_prices.itemsubgroup_id');
        $recordsFiltered->leftJoin('wlmst_items', 'wlmst_items.id', '=', 'wlmst_item_prices.item_id');
        $recordsFiltered->leftJoin('wlmst_quot_discount_flow', 'wlmst_quot_discount_flow.id', '=', 'wlmst_item_prices.flow_ids');

        
        if (isset($request['search']['value'])) {
            $isFilterApply = 1;
            $search_value = $request['search']['value'];
            $recordsFiltered->where(function ($recordsFiltered) use ($search_value, $searchColumns) {
                for ($i = 0; $i < count($searchColumns); $i++) {
                    if ($i == 0) {
                        $recordsFiltered->where($searchColumns[$i], 'like', "%" . $search_value . "%");
                    } else {
                        $recordsFiltered->orWhere($searchColumns[$i], 'like', "%" . $search_value . "%");
                    }
                }
            });
        }
        $recordsFiltered = json_decode(json_encode($recordsFiltered->get()), true);
        $recordsFiltered = count($recordsFiltered);

        $query = Wlmst_ItemPrice::query();
        // $query = DB::table('wlmst_item_groups');
        $query->leftJoin('wlmst_companies', 'wlmst_companies.id', '=', 'wlmst_item_prices.company_id');
        $query->leftJoin('wlmst_item_groups', 'wlmst_item_groups.id', '=', 'wlmst_item_prices.itemgroup_id');
        $query->leftJoin('wlmst_item_subgroups', 'wlmst_item_subgroups.id', '=', 'wlmst_item_prices.itemsubgroup_id');
        $query->leftJoin('wlmst_items', 'wlmst_items.id', '=', 'wlmst_item_prices.item_id');
        $query->leftJoin('wlmst_quot_discount_flow', 'wlmst_quot_discount_flow.id', '=', 'wlmst_item_prices.flow_ids');

        if ($request->filled('filter_company')) {
            $query->where('wlmst_item_prices.company_id', $request->filter_company);
        }
        if ($request->filled('filter_group')) {
            $query->where('wlmst_item_prices.itemgroup_id', $request->filter_group);
        }
        if ($request->filled('filter_subgroup')) {
            $query->where('wlmst_item_prices.itemsubgroup_id', $request->filter_subgroup);
        }
        if ($request->filled('filter_item')) {
            $query->where('wlmst_item_prices.item_id', $request->filter_item);
        }
        $query->select($columns);
        $query->limit($request->length);
        $query->offset($request->start);
        $query->orderBy($columns[$request['order'][0]['column']], $request['order'][0]['dir']);
        $isFilterApply = 0;

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


        $data = json_decode(json_encode($data), true);

        if ($isFilterApply == 1) {
            $recordsFiltered = count($data);
        }

        foreach ($data as $key => $value) {

        $data[$key]['id'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">' . $data[$key]['id'] . '</a></h5>';
        $data[$key]['item'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">' . $data[$key]['itemname'] . '</a></h5>
        <p class="text-muted mb-0">' . $data[$key]['companyname'] . '</p>';

        $data[$key]['brand'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">' . $data[$key]['itemgroupname'] . '</a></h5>
        <p class="text-muted mb-0">' . $data[$key]['itemsubgroupname'] . '</p>';

        $data[$key]['code'] = "<p>" . $data[$key]['code'] . '</p>';

        $data[$key]['flow_ids'] = '
        <div class="col-md-12">
            <div class="ajax-select">
                <select class="form-control select2-ajax select2-multiple item_change_select2"
                    id="flow_ids_' . $value['id'] . '" 
                    name="flow_ids_' . $key . '[]" 
                    multiple="multiple" required> ';
                    // Explode the flow IDs string to get individual flow IDs
                    $selectedFlowIds = explode(',', $value['flow_ids']);
                    $flowNames = WlmstQuotDiscountFlow::whereIn('id', $selectedFlowIds)->pluck('name', 'id')->toArray();
                    
                    foreach ($selectedFlowIds as $flowId) {
                        // Get the flow name corresponding to the current flow ID
                        $flowName = isset($flowNames[$flowId]) ? $flowNames[$flowId] : '';
                    
                        // Append each option to the options string
                        $data[$key]['flow_ids'] .= '<option value="' . $flowId . '" ' . ($flowName ? 'selected' : '') . '>' . $flowName . '</option>';
                    }
                    
                    $data[$key]['flow_ids'] .= '
                    </select>
                    <div class="invalid-feedback">
                        Select New Flow.
                    </div>
                    <script type="text/javascript">
                        $(document).ready(function() {
                            var flowSelect = $("#flow_ids_' . $value['id'] . '");
            
                            // Initialize Select2
                            flowSelect.select2({
                                ajax: {
                                    url: "' . route("quot.itemprice.search.multi.flow") . '",
                                    dataType: "json",
                                    delay: 0,
                                    data: function(params) {
                                        return {
                                            q: params.term, // search term
                                            page: params.page
                                        };
                                    },
                                    processResults: function(data, params) {
                                        params.page = params.page || 1;
            
                                        return {
                                            results: data.results,
                                            pagination: {
                                                more: (params.page * 30) < data.total_count
                                            }
                                        };
                                    },
                                    cache: false
                                },
                                placeholder: "Select New Flow",
                                dropdownParent: $("#modalItemFlowUpdate"),
                            });
                        });
                    </script>
                </div>
            </div>';
        }
       
        $jsonData = array(
            "draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($recordsTotal), // total number of records
            "recordsFiltered" => intval($recordsFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $data, // total data array
        );

        return $jsonData;
    }

    public function detail(Request $request)
    {
        $MainMaster = Wlmst_ItemPrice::with(array('company' => function ($query) {
            $query->select('id', 'companyname');
        }, 'itemgroup' => function ($query) {
            $query->select('id', 'itemgroupname');
        }, 'itemsubgroup' => function ($query) {
            $query->select('id', 'itemsubgroupname');
        }, 'item' => function ($query) {
            $query->select('id', 'itemname');
        }))->find($request->id);

        $flowIds = explode(',', $MainMaster->flow_ids);
        $flows = WlmstQuotDiscountFlow::whereIn('id', $flowIds)->select('id', 'name')->get();
        $MainMaster->flows = $flows;
        $MainMaster['image'] = getSpaceFilePath($MainMaster->image);

        if ($MainMaster) {
            $response = successRes("Successfully get quotation item price master");
            $response['data'] = $MainMaster;
        } else {
            $response = errorRes("Invalid id");
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }
    public function updatePriceExcel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'q_price_excel' => ['required'],
        ]);
        if ($validator->fails()) {

            $response = array();
            $response['status'] = 0;
            $response['msg'] = "The request could not be understood by the server due to malformed syntax";
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors();

            return response()->json($response)->header('Content-Type', 'application/json');
        } else {
            $the_file = $request->file('q_price_excel');
            try {
                $spreadsheet = IOFactory::load($the_file->getRealPath());
                $sheet        = $spreadsheet->getActiveSheet();
                $row_limit    = $sheet->getHighestDataRow();
                $row_range    = range(2, $row_limit);
                $data = array();
                foreach ($row_range as $row) {

                    $ItemPriceMaster = Wlmst_ItemPrice::find($sheet->getCell('A' . $row)->getValue());
                    $ItemPriceMaster->mrp = $sheet->getCell('M' . $row)->getValue();
                    $ItemPriceMaster->updateip = $request->ip();
                    $ItemPriceMaster->updateby = Auth::user()->id;
                    $ItemPriceMaster->save();

                    $PriceMasterLog = new Wlmst_ItemPriceLog();
                    $PriceMasterLog->price_id = $ItemPriceMaster->id;
                    $PriceMasterLog->company_id = $ItemPriceMaster->company_id;
                    $PriceMasterLog->itemgroup_id = $ItemPriceMaster->itemgroup_id;
                    $PriceMasterLog->itemsubgroup_id = $ItemPriceMaster->itemsubgroup_id;
                    $PriceMasterLog->item_id = $ItemPriceMaster->item_id;
                    $PriceMasterLog->code = $ItemPriceMaster->code;
                    $PriceMasterLog->mrp = $ItemPriceMaster->mrp;
                    $PriceMasterLog->discount = $ItemPriceMaster->discount;
                    $PriceMasterLog->effectivedate = $ItemPriceMaster->effectivedate;
                    $PriceMasterLog->item_type = $ItemPriceMaster->item_type;
                    $PriceMasterLog->image = $ItemPriceMaster->image;
                    $PriceMasterLog->thumb_image = $ItemPriceMaster->thumb_image;
                    $PriceMasterLog->isactive = $ItemPriceMaster->isactive;
                    $PriceMasterLog->remark = "bulk update";
                    $PriceMasterLog->entryby = Auth::user()->id;
                    $PriceMasterLog->entryip = $request->ip();
                    $PriceMasterLog->save();
                }
                // DB::table('wlmst_companies')->insert($data);
                $response = successRes('Data Imported Successfully');
                $debugLog = array();
                $debugLog['name'] = "quot-item-price-data-excel-upload";
                $debugLog['description'] = "quotation item price master data has been updated Using Excel Upload Feature User #".Auth::user()->id." User IP #".$request->ip()." Log #".$PriceMasterLog->id;
                saveDebugLog($debugLog);
            } catch (Exception $e) {
                $response = errorRes($e->getMessage());
            }
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }
    public function delete(Request $request)
    {
        $ItemPrice = Wlmst_ItemPrice::find($request->id);
        if ($ItemPrice) {
            $debugLog = array();
            $debugLog['name'] = "quot-item-price-delete";
            $debugLog['description'] = "quot item price #" . $ItemPrice->id . "(" . $ItemPrice->code . ") has been deleted";
            saveDebugLog($debugLog);
            $ItemPrice->delete();
        }
        $response = successRes("Successfully delete ItemPrice");
        return response()->json($response)->header('Content-Type', 'application/json');
    }
    function export(Request $request)
    {
        $columns = array(
            'wlmst_item_prices.id',
            'wlmst_item_prices.company_id',
            'company.companyname',
            'wlmst_item_prices.itemgroup_id',
            'group.itemgroupname',
            'wlmst_item_prices.itemsubgroup_id',
            'subgroup.itemsubgroupname',
            'wlmst_item_prices.item_id',
            'item.itemname',
            'item.itemcategory_id',
            'category.itemcategoryname',
            'wlmst_item_prices.code',
            'wlmst_item_prices.mrp',
            'wlmst_item_prices.discount',
            'wlmst_item_prices.effectivedate',
            'wlmst_item_prices.image',
            'wlmst_item_prices.item_type',

            'wlmst_item_prices.isactive',
            'wlmst_item_prices.remark',
            'wlmst_item_prices.created_at',
            'wlmst_item_prices.entryby',
            DB::raw('CONCAT(entry_user.first_name," ",entry_user.last_name) as entrybyname'),
            'wlmst_item_prices.entryip',
            'wlmst_item_prices.updated_at',
            'wlmst_item_prices.updateby',
            DB::raw('CONCAT(update_user.first_name," ",update_user.last_name) as updatebyname'),
            'wlmst_item_prices.updateip',
        );

        $query = Wlmst_ItemPrice::query();
        $query->select($columns);
        $query->leftJoin('wlmst_companies as company', 'company.id', '=', 'wlmst_item_prices.company_id');
        $query->leftJoin('wlmst_item_groups as group', 'group.id', '=', 'wlmst_item_prices.itemgroup_id');
        $query->leftJoin('wlmst_item_subgroups as subgroup', 'subgroup.id', '=', 'wlmst_item_prices.itemsubgroup_id');
        $query->leftJoin('wlmst_items as item', 'item.id', '=', 'wlmst_item_prices.item_id');
        $query->leftJoin('wlmst_item_categories as category', 'category.id', '=', 'item.itemcategory_id');
        $query->leftJoin('users as entry_user', 'entry_user.id', '=', 'wlmst_item_prices.entryby');
        $query->leftJoin('users as update_user', 'update_user.id', '=', 'wlmst_item_prices.updateby');
        $data = $query->get();

        $headers = array(
            "#ID", "Company Id", "Company Name",
            "Group Id", "Group Name", "SubGroup Id", "SubGroup Name",
            "Item Id", "Item Name", "Category Id", "category Name",
            "Code", "MRP", "Discount", "Effective Date", "Image", "Item Type", "Status", "Status Label", "Remark", "Created At", "Entry By", "Entry By Name", "Entry Ip", "Updated At", "Update By", "Update By Name", "Update Ip"
        );

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="quotation-item-price-data.csv"');

        $fp = fopen('php://output', 'wb');

        fputcsv($fp, $headers);

        foreach ($data as $key => $value) {

            $lineVal = array(
                $value->id,
                $value->company_id,
                $value->companyname,
                $value->itemgroup_id,
                $value->itemgroupname,
                $value->itemsubgroup_id,
                $value->itemsubgroupname,
                $value->item_id,
                $value->itemname,
                $value->itemcategory_id,
                $value->itemcategoryname,
                $value->code,
                $value->mrp,
                $value->discount,
                $value->effectivedate,
                $value->image,
                $value->item_type,
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

    function saveFilteredPrice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'filter_company' => ['required'],
            'filter_group' => ['required'],
            'filter_subgroup' => ['required'],
            'filter_item' => ['required'],
            'price' => ['required']
        ]);

        if ($validator->fails()) {

            $response = array();
            $response['status'] = 0;
            $response['msg'] = "The request could not be understood by the server due to malformed syntax";
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors();
        } else {

            try {
                $ItemPriceArr = Wlmst_ItemPrice::select('*');
                if ($request->filter_company != 0) {
                    $ItemPriceArr->where('wlmst_item_prices.company_id', $request->filter_company);
                }
                if ($request->filter_group != 0) {
                    $ItemPriceArr->where('wlmst_item_prices.itemgroup_id', $request->filter_group);
                }
                if ($request->filter_subgroup != 0) {
                    $ItemPriceArr->where('wlmst_item_prices.itemsubgroup_id', $request->filter_subgroup);
                }
                if ($request->filter_item != 0) {
                    $ItemPriceArr->where('wlmst_item_prices.item_id', $request->filter_item);
                }

                foreach ($ItemPriceArr->get() as $key => $value) {
                    $PriceMaster = Wlmst_ItemPrice::find($value->id);
                    $PriceMaster->updateby = Auth::user()->id;
                    $PriceMaster->updateip = $request->ip();
                    $PriceMaster->mrp = $request->price;
                    $PriceMaster->save();

                    $PriceMasterLog = new Wlmst_ItemPriceLog();
                    $PriceMasterLog->price_id = $PriceMaster->id;
                    $PriceMasterLog->company_id = $PriceMaster->company_id;
                    $PriceMasterLog->itemgroup_id = $PriceMaster->itemgroup_id;
                    $PriceMasterLog->itemsubgroup_id = $PriceMaster->itemsubgroup_id;
                    $PriceMasterLog->item_id = $PriceMaster->item_id;
                    $PriceMasterLog->code = $PriceMaster->code;
                    $PriceMasterLog->mrp = $PriceMaster->mrp;
                    $PriceMasterLog->discount = $PriceMaster->discount;
                    $PriceMasterLog->effectivedate = $PriceMaster->effectivedate;
                    $PriceMasterLog->item_type = $PriceMaster->item_type;
                    $PriceMasterLog->image = $PriceMaster->image;
                    $PriceMasterLog->thumb_image = $PriceMaster->thumb_image;
                    $PriceMasterLog->isactive = $PriceMaster->isactive;
                    $PriceMasterLog->remark = "remark";
                    $PriceMasterLog->entryby = Auth::user()->id;
                    $PriceMasterLog->entryip = $request->ip();
                    $PriceMasterLog->save();
                }

                $response = successRes("All Item Price Updated ✅");

                $debugLog = array();
                $debugLog['name'] = "quot-item-price-data-update";
                $debugLog['description'] = "quotation item price master data has been updated Using Price Update dialog User #".Auth::user()->id." User IP #".$request->ip()." Log #".$PriceMasterLog->id;
                saveDebugLog($debugLog);
            } catch (QueryException $ex) {
                $response = errorRes("Please Contact To Admin");
                $response['data'] = $ex->getMessage();
            }

            return response()->json($response)->header('Content-Type', 'application/json');
        }
    }

    function saveFilteredFlow(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'filter_company' => ['required'],
            'filter_group' => ['required'],
            'filter_subgroup' => ['required'],
            'filter_item' => ['required'],
            'flow_ids' => ['required']
        ]);

        if ($validator->fails()) {

            $response = array();
            $response['status'] = 0;
            $response['msg'] = "The request could not be understood by the server due to malformed syntax";
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors();
        } else {

            try {
                $ItemPriceArr = Wlmst_ItemPrice::select('*');
                if ($request->filter_company != 0) {
                    $ItemPriceArr->where('wlmst_item_prices.company_id', $request->filter_company);
                }
                if ($request->filter_group != 0) {
                    $ItemPriceArr->where('wlmst_item_prices.itemgroup_id', $request->filter_group);
                }
                if ($request->filter_subgroup != 0) {
                    $ItemPriceArr->where('wlmst_item_prices.itemsubgroup_id', $request->filter_subgroup);
                }
                if ($request->filter_item != 0) {
                    $ItemPriceArr->where('wlmst_item_prices.item_id', $request->filter_item);
                }

                foreach ($ItemPriceArr->get() as $key => $value) {
                    $PriceMaster = Wlmst_ItemPrice::find($value->id);
                    $PriceMaster->updateby = Auth::user()->id;
                    $PriceMaster->updateip = $request->ip();
                    $PriceMaster->flow_ids = implode(',', $request->flow_ids);
                    $PriceMaster->save();
                }

                $response = successRes("All Item Flow Updated ✅");

                $debugLog = array();
                $debugLog['name'] = "quot-item-flow-data-update";
                $debugLog['description'] = "quotation item flow master data has been updated Using Price Update dialog User #".Auth::user()->id." User IP #".$request->ip();
                saveDebugLog($debugLog);
            } catch (QueryException $ex) {
                $response = errorRes("Please Contact To Admin");
                $response['data'] = $ex->getMessage();
            }

            return response()->json($response)->header('Content-Type', 'application/json');
        }
    }
    public function saveAllPrice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'price_list' => ['required']
        ]);

        if ($validator->fails()) {

            $response = array();
            $response['status'] = 0;
            $response['msg'] = "The request could not be understood by the server due to malformed syntax";
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors();
        } else {
            try {
                $new_price_list = $request->price_list;
                foreach ($new_price_list as $key => $value) {
                    $PriceMaster = Wlmst_ItemPrice::find($value['id']);
                    $PriceMaster->updateby = Auth::user()->id;
                    $PriceMaster->updateip = $request->ip();
                    $PriceMaster->mrp = $value['val'];
                    $PriceMaster->save();

                    $PriceMasterLog = new Wlmst_ItemPriceLog();
                    $PriceMasterLog->price_id = $PriceMaster->id;
                    $PriceMasterLog->company_id = $PriceMaster->company_id;
                    $PriceMasterLog->itemgroup_id = $PriceMaster->itemgroup_id;
                    $PriceMasterLog->itemsubgroup_id = $PriceMaster->itemsubgroup_id;
                    $PriceMasterLog->item_id = $PriceMaster->item_id;
                    $PriceMasterLog->code = $PriceMaster->code;
                    $PriceMasterLog->mrp = $PriceMaster->mrp;
                    $PriceMasterLog->discount = $PriceMaster->discount;
                    $PriceMasterLog->effectivedate = $PriceMaster->effectivedate;
                    $PriceMasterLog->item_type = $PriceMaster->item_type;
                    $PriceMasterLog->image = $PriceMaster->image;
                    $PriceMasterLog->thumb_image = $PriceMaster->thumb_image;
                    $PriceMasterLog->isactive = $PriceMaster->isactive;
                    $PriceMasterLog->remark = "Price Update Using Bulk Price Update Module";
                    $PriceMasterLog->entryby = Auth::user()->id;
                    $PriceMasterLog->entryip = $request->ip();
                    $PriceMasterLog->save();
                }

                $response = successRes("All Item Price Updated ✅");

                $debugLog = array();
                $debugLog['name'] = "quot-item-price-data-update";
                $debugLog['description'] = "quotation item price master data has been updated Using Price Update dialog User #".Auth::user()->id." User IP #".$request->ip()." Log #".$PriceMasterLog->id;
                saveDebugLog($debugLog);

            } catch (QueryException $ex) {
                $response = errorRes("Please Contact To Admin");
                $response['data'] = $ex->getMessage();
            }

            return response()->json($response)->header('Content-Type', 'application/json');
        }
    }

    public function saveAllFlow(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'flow_list' => ['required']
        ]);

        if ($validator->fails()) {

            $response = array();
            $response['status'] = 0;
            $response['msg'] = "The request could not be understood by the server due to malformed syntax";
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors();
        } else {
            try {
                $new_flow_list = $request->flow_list;
                foreach ($new_flow_list as $key => $value) {
                    if (isset($value['id'])) {
                        $id = (int) str_replace('flow_ids_', '', $value['id']);
                        $PriceMaster = Wlmst_ItemPrice::find($id);
                        if ($PriceMaster) {
                            $PriceMaster->updateby = Auth::user()->id;
                            $PriceMaster->updateip = $request->ip();
                            $flowIds = isset($value['val']) ? implode(',', $value['val']) : '';
                            $PriceMaster->flow_ids = $flowIds;
                            $PriceMaster->save();
                        }
                    }
                }

                $response = successRes("All Item Price Updated ✅");

                $debugLog = array();
                $debugLog['name'] = "quot-item-price-data-update";
                $debugLog['description'] = "quotation item price master data has been updated Using Price Update dialog User #".Auth::user()->id." User IP #".$request->ip();
                saveDebugLog($debugLog);

            } catch (QueryException $ex) {
                $response = errorRes("Please Contact To Admin");
                $response['data'] = $ex->getMessage();
            }

            return response()->json($response)->header('Content-Type', 'application/json');
        }
    }
}
