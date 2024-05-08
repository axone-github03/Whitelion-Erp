<?php

namespace App\Http\Controllers;

use App\Models\PurchaseHierarchy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

//use Session;

class MasterPurchaseHierarchyController extends Controller
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
        $data['title'] = "Purchase Hierarchy ";
        return view('master/purchasehierarchy', compact('data'));
    }

    function ajax(Request $request)
    {
        //DB::enableQueryLog();

        $searchColumns = array(

            0 => 'purchase_hierarchy.id',
            1 => 'purchase_hierarchy.name',
        );

        $columns = array(
            0 => 'purchase_hierarchy.id',
            1 => 'purchase_hierarchy.name',
            2 => 'purchase_hierarchy.code',
            3 => 'purchase_hierarchy.parent_id',
            4 => 'purchase_hierarchy.status',

        );

        $recordsTotal = PurchaseHierarchy::count();
        $recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.

        $query = PurchaseHierarchy::query();
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
            $data[$key]['name'] = "<p>" . highlightString($data[$key]['name'],$search_value) . '</p>';
            $data[$key]['code'] = "<p>" . highlightString($data[$key]['code'],$search_value) . '</p>';

            $data[$key]['status'] = getPurchaseHierarchyStatusLable($value['status']);
            if ($value['parent_id'] != 0) {

                $parent = PurchaseHierarchy::find($value['parent_id']);
                if ($parent) {

                    $data[$key]['parent'] = "<p>" . highlightString($parent->name,$search_value) . '</p>';
                } else {

                    $data[$key]['parent'] = "";
                }
            } else {

                $data[$key]['parent'] = "";
            }

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

    public function search(Request $request)
    {

        $results = array();

        $results = PurchaseHierarchy::select('id', 'name as text');
        $results->where('id', '!=', $request->id);
        $results->where('name', 'like', "%" . $request->q . "%");

        $results->limit(5);
        $results = $results->get();

        $response = array();
        $response['results'] = $results;
        $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function saveProcess(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'sales_hierarchy_id' => ['required'],
            'sales_hierarchy_name' => ['required'],
            'sales_hierarchy_code' => ['required'],
            'sales_hierarchy_status' => ['required'],

        ]);
        if ($validator->fails()) {

            $response = array();
            $response['status'] = 0;
            $response['msg'] = "The request could not be understood by the server due to malformed syntax";
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors();

            return response()->json($response)->header('Content-Type', 'application/json');
        } else {

            $alreadyName = PurchaseHierarchy::query();

            if ($request->sales_hierarchy_id != 0) {

                $alreadyName->where('name', $request->sales_hierarchy_name);
                $alreadyName->where('id', '!=', $request->sales_hierarchy_id);
            } else {
                $alreadyName->where('name', $request->sales_hierarchy_name);
            }

            $alreadyName = $alreadyName->first();

            $alreadyCode = PurchaseHierarchy::query();

            if ($request->sales_hierarchy_id != 0) {

                $alreadyCode->where('code', $request->sales_hierarchy_code);
                $alreadyCode->where('id', '!=', $request->sales_hierarchy_id);
            } else {
                $alreadyCode->where('code', $request->sales_hierarchy_code);
            }

            $alreadyCode = $alreadyCode->first();

            if ($alreadyName) {

                $response = errorRes("already name exits, Try with another name");
            } else if ($alreadyCode) {

                $response = errorRes("already code exits, Try with another code");
            } else {

                $sales_hierarchy_parent_id = isset($request->sales_hierarchy_parent_id) ? $request->sales_hierarchy_parent_id : 0;
                if ($sales_hierarchy_parent_id != 0) {

                    $Parent = PurchaseHierarchy::where('id', $sales_hierarchy_parent_id)->first();
                    if ($Parent) {
                        $sales_hierarchy_parent_id = $Parent->id;
                    } else {
                        $sales_hierarchy_parent_id = 0;
                    }
                }

                if ($request->sales_hierarchy_id != 0) {

                    $PurchaseHierarchy = PurchaseHierarchy::find($request->sales_hierarchy_id);
                } else {
                    $PurchaseHierarchy = new PurchaseHierarchy();
                }

                $PurchaseHierarchy->name = $request->sales_hierarchy_name;
                $PurchaseHierarchy->code = $request->sales_hierarchy_code;
                $PurchaseHierarchy->status = $request->sales_hierarchy_status;
                $PurchaseHierarchy->parent_id = $sales_hierarchy_parent_id;
                $PurchaseHierarchy->save();
                if ($PurchaseHierarchy) {

                    if ($request->sales_hierarchy_id != 0) {

                        $response = successRes("Successfully saved sales hierarchy");

                        $debugLog = array();
                        $debugLog['name'] = "sales-purchase-edit";
                        $debugLog['description'] = "purchase hierarchy #" . $PurchaseHierarchy->id . "(" . $PurchaseHierarchy->name . ") has been updated ";
                        saveDebugLog($debugLog);
                    } else {
                        $response = successRes("Successfully added sales hierarchy");

                        $debugLog = array();
                        $debugLog['name'] = "sales-purchase-add";
                        $debugLog['description'] = "purchase hierarchy #" . $PurchaseHierarchy->id . "(" . $PurchaseHierarchy->name . ") has been added ";
                        saveDebugLog($debugLog);
                    }
                }
            }

            return response()->json($response)->header('Content-Type', 'application/json');
        }
    }

    public function detail(Request $request)
    {

        $PurchaseHierarchy = PurchaseHierarchy::find($request->id);
        if ($PurchaseHierarchy) {

            $parent = array();
            $is_parent = 0;

            if ($PurchaseHierarchy->parent_id != 0) {

                $parent = PurchaseHierarchy::find($PurchaseHierarchy->parent_id);
                if ($parent) {

                    $is_parent = 1;
                }
            }

            $response = successRes("Successfully get sales hierarchy");
            $response['data'] = $PurchaseHierarchy;
            $response['parent'] = $parent;
            $response['is_parent'] = $is_parent;
        } else {
            $response = errorRes("Invalid id");
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function delete(Request $request)
    {

        $PurchaseHierarchy = PurchaseHierarchy::find($request->id);
        if ($PurchaseHierarchy) {

            $debugLog = array();
            $debugLog['name'] = "purchase-hierarchy-delete";
            $debugLog['description'] = "purchase hierarchy #" . $PurchaseHierarchy->id . "(" . $PurchaseHierarchy->name . ") has been deleted";
            saveDebugLog($debugLog);

            $PurchaseHierarchy->delete();
        }
        $response = successRes("Successfully delete sales hierarchy");
        return response()->json($response)->header('Content-Type', 'application/json');
    }
}
