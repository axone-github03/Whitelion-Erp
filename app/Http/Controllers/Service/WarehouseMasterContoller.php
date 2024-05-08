<?php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Wlmst_Warehouse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\StateList;
use App\Models\CountryList;
use App\Models\CityList;

class WarehouseMasterContoller extends Controller
{
    public function index()
    {
        $data = array();
        $data['title'] = "Warehouse Master";
        return view('service/master/warehousemaster/warehouse', compact('data'));
    }

    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'warehouse_name' => ['required'],
            'warehouse_address_line1' => ['required'],
            'warehouse_country_id' => ['required'],
            'warehouse_state_id' => ['required'],
            'warehouse_city_id' => ['required'],
            'warehouse_pincode' => ['required'],
            'warehouse_remark' => ['required'],
            'warehouse_status' => ['required'],
        ]);

        if ($validator->fails()) {

            $response = array();
            $response['status'] = 0;
            $response['msg'] = "The request could not be understood by the server due to malformed syntax";
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors();

            return response()->json($response)->header('Content-Type', 'application/json');
        } else {

            $alreadyName = Wlmst_Warehouse::query();
            if ($request->warehouse_id != 0) {

                $alreadyName->where('warehousename', $request->warehouse_name);
                $alreadyName->where('id', '!=', $request->warehouse_id);
            } else {
                $alreadyName->where('warehousename', $request->warehouse_name);
            }

            $alreadyName = $alreadyName->first();
            if ($alreadyName) {
                $response = errorRes("already name exits, Try with another name");
            } else {
                if ($request->warehouse_id != 0) {
                    $MainMaster = Wlmst_Warehouse::find($request->warehouse_id);
                    $MainMaster->updateby = Auth::user()->id;
                    $MainMaster->updateip = $request->ip();
                } else {
                    $MainMaster = new Wlmst_Warehouse();
                    $MainMaster->entryby = Auth::user()->id;
                    $MainMaster->entryip = $request->ip();
                }

                $MainMaster->warehousename = $request->warehouse_name;
                $MainMaster->shortname = strtoupper(preg_replace('/[^\p{L}\p{N}]/u', '_', $request->warehouse_name));
                $MainMaster->address1 = $request->warehouse_address_line1;
                $MainMaster->address2 = $request->warehouse_address_line2;
                $MainMaster->country = $request->warehouse_country_id;
                $MainMaster->state = $request->warehouse_state_id;
                $MainMaster->city = $request->warehouse_city_id;
                $MainMaster->pincode = $request->warehouse_pincode;
                $MainMaster->remark = isset($request->warehouse_remark) ? $request->warehouse_remark : '';
                $MainMaster->isactive = $request->warehouse_status;


                $MainMaster->save();
                if ($MainMaster) {

                    if ($request->warehouse_id != 0) {
                        $response = successRes("Successfully saved product tag master");
                        $debugLog = array();
                        $debugLog['name'] = "service-product-tag-master-edit";
                        $debugLog['description'] = "quotation company master #" . $MainMaster->id . "(" . $MainMaster->warehousename . ")" . " has been updated ";
                        saveDebugLog($debugLog);
                    } else {
                        $response = successRes("Successfully added product tag master");

                        $debugLog = array();
                        $debugLog['name'] = "service-product-tag-master-add";
                        $debugLog['description'] = "quotation company master #" . $MainMaster->id . "(" . $MainMaster->warehousename . ") has been added ";
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
            0 => 'wlmst_warehouses.id',
            1 => 'wlmst_warehouses.warehousename',
        );

        $columns = array(
            0 => 'wlmst_warehouses.id',
            1 => 'wlmst_warehouses.warehousename',
            2 => 'wlmst_warehouses.shortname',
            3 => 'wlmst_warehouses.address1',
            4 => 'wlmst_warehouses.address2',
            5 => 'wlmst_warehouses.isactive',
        );

        $recordsTotal = Wlmst_Warehouse::count();
        $recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.

        $query = Wlmst_Warehouse::query();
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
            $data[$key]['warehousename'] = "<p>" .  highlightString($data[$key]['warehousename'],$search_value) . '</p>';
            $data[$key]['shortname'] = "<p>" .  highlightString($data[$key]['shortname'],$search_value) . '</p>';
            $data[$key]['address1'] = "<p>" .  highlightString($data[$key]['address1'].' '. $data[$key]['address2'],$search_value) . '</p>';
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
            "data" => $data,
            // "countrylist" => $CountryList 
            // total data array
        );

        return $jsonData;
    }

    public function delete(Request $request)
    {

        $WarehouseMaster = Wlmst_Warehouse::find($request->id);
        if ($WarehouseMaster) {

            $debugLog = array();
            $debugLog['name'] = " service-warehouse-delete";
            $debugLog['description'] = "quot item company #" . $WarehouseMaster->id . "(" . $WarehouseMaster->warehousename . ") has been deleted";
            saveDebugLog($debugLog);

            $WarehouseMaster->delete();
        }
        $response = successRes("Successfully delete Warehouse");
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function export(Request $request)
    {

        $columns = array(
            'wlmst_warehouses.id',
            'wlmst_warehouses.warehousename',
            'wlmst_warehouses.shortname',
            'wlmst_warehouses.address1',
            'wlmst_warehouses.address2',
            'wlmst_warehouses.country',
            'wlmst_warehouses.state',
            'wlmst_warehouses.city',
            'wlmst_warehouses.pincode',
            'wlmst_warehouses.isactive',
            'wlmst_warehouses.remark',
            'wlmst_warehouses.created_at',
            'wlmst_warehouses.entryby',
            DB::raw('CONCAT(entry_user.first_name," ",entry_user.last_name) as entrybyname'),
            'wlmst_warehouses.entryip',
            'wlmst_warehouses.updated_at',
            'wlmst_warehouses.updateby',
            DB::raw('CONCAT(update_user.first_name," ",update_user.last_name) as updatebyname'),
            'wlmst_warehouses.updateip',
        );

        $query = Wlmst_Warehouse::query();
        $query->select($columns);
        $query->leftJoin('users as entry_user', 'entry_user.id', '=', 'wlmst_warehouses.entryby');
        $query->leftJoin('users as update_user', 'update_user.id', '=', 'wlmst_warehouses.updateby');
        $data = $query->get();

        $headers = array("#ID", "Warehouse Name", "Short Name", "Address1", "Address2","Country", "State", "City", "Pincode", "Status", "Status Label", "Remark", "Created At", "Entry By", "Entry By Name", "Entry Ip", "Updated At", "Update By", "Update By Name", "Update Ip");

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="warehouse-master-data.csv"');

        $fp = fopen('php://output', 'wb');

        fputcsv($fp, $headers);

        foreach ($data as $key => $value) {
            $CountryList = array();
            $CountryList = CountryList::select('name');
            $CountryList->where('id',$value->country);
            $CountryList = $CountryList->get();
            
            $StateList = array();
            $StateList = StateList::select('name');
            $StateList->where('id', $value->state);
            $StateList =  $StateList->get();
            
            $CityList = array();
            $CityList = CityList::select('name');
            $CityList->where('id', $value->city);
            $CityList = $CityList->get();

            $lineVal = array(
                $value->id,
                $value->warehousename,
                $value->shortname,
                $value->address1,
                $value->address2,
                $CountryList[0]->name,
                $StateList[0]->name,
                $CityList[0]->name,
                $value->pincode,
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

    public function detail(Request $request)
    {
        $column = array('wlmst_warehouses.*', 'country_list.name as country_name', 'state_list.name as state_name', 'city_list.name as city_name');
        $MainMaster = Wlmst_Warehouse::query();
        $MainMaster->select($column);
        $MainMaster->where('wlmst_warehouses.id', $request->id);
        $MainMaster->leftJoin('country_list', 'country_list.id', '=', 'wlmst_warehouses.country');
        $MainMaster->leftJoin('state_list', 'state_list.id', '=', 'wlmst_warehouses.state');
        $MainMaster->leftJoin('city_list', 'city_list.id', '=', 'wlmst_warehouses.city');
        $MainMaster= $MainMaster->get();

        if ($MainMaster) {
            $response = successRes("Successfully get WareHouse detail master");
            $response['data'] = $MainMaster[0];
        } else {
            $response = errorRes("Invalid id");
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function searchState(Request $request)
    {

        $StateList = array();
        $StateList = StateList::select('id', 'name as text');
        $StateList->where('country_id', $request->country_id);

        $StateList->where('name', 'like', "%" . $request->q . "%");

        $StateList->limit(5);
        $StateList = $StateList->get();

        $response = array();
        $response['results'] = $StateList;
        $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function searchCity(Request $request)
    {

        $CityList = array();
        $CityList = CityList::select('id', 'name as text');
        $CityList->where('country_id', $request->country_id);
        $CityList->where('state_id', $request->state_id);
        $CityList->where('name', 'like', "%" . $request->q . "%");
        $CityList->where('status', 1);
        $CityList->limit(5);
        $CityList = $CityList->get();

        $response = array();
        $response['results'] = $CityList;
        $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function searchCountry(Request $request)
    {

        $CountryList = array();
        $CountryList = CountryList::select('id', 'name as text');
        $CountryList->where('name', 'like', "%" . $request->q . "%");
        $CountryList = $CountryList->get();
        $response = array();
        $response['results'] = $CountryList;
       
        // $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');
    }
}
