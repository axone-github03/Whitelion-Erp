<?php

namespace App\Http\Controllers\Production\Warehouse;

use App\Http\Controllers\Controller;
use FontLib\Table\Type\name;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use App\Models\MstWarehouse;


class WarehouseController extends Controller
{

    public function index()
    {
        $data = "MASTER-HOUSE";
        return view('production/wearhouse/main')->with($data);
    }


    public function save(Request $request)
    {

        // $data = new MstWarehouse();

        
        // $data->entryby = Auth::user()->id;
        // $data->entryip = $request->ip();
        // $data->updateby = Auth::user()->id;
        // $data->updateip = $request->ip();

        // $data->save();
        // $response['status'] = 0; 
        // $response['msg'] = "The request could not be understood by the server due to malformed syntax";
       

        if ($request->warehouse_id != 0) {

            $mainhouse = MstWarehouse::find($request->warehouse_id);
            $mainhouse->updateby = Auth::user()->id;
            $mainhouse->updateip = $request->ip();
        } else {
            $mainhouse = new MstWarehouse();
            $mainhouse->entryby = Auth::user()->id;
            $mainhouse->entryip = $request->ip();

        }

        $mainhouse->name = $request->name;
        $mainhouse->company_id = 0;
        $mainhouse->shortname = $request->shortname;
        $mainhouse->address_line_1 = $request->address_line_1;
        $mainhouse->address_line_2 = $request->second_eddress;
        $mainhouse->pincode = $request->pincode;
        $mainhouse->area = $request->area;
        $mainhouse->country_id = 0;
        $mainhouse->state_id = 0;
        $mainhouse->city = $request->city;
        $mainhouse->status = 1;
        $mainhouse->source = "web";

        $mainhouse->save();

        $response = successRes("Successfully saved warehouse master");
        return response()->json($response)->header('Content-Type', 'application/json');
    }
    public function ajax(Request $request)
    {
        $serschcolm = array(

            0 => 'mst_warehouse.id',
            1 => 'mst_warehouse.name',
            2 => 'mst_warehouse.shortname',
            3 => 'mst_warehouse.pincode',

        );

        $colums = array(

            0 => 'mst_warehouse.id',
            1 => 'mst_warehouse.name',
            2 => 'mst_warehouse.shortname',
            3 => 'mst_warehouse.address_line_1',
            4 => 'mst_warehouse.address_line_2',
            5 => 'mst_warehouse.pincode',
            6 => 'mst_warehouse.area',
            7 => 'mst_warehouse.city',
        );

        $recodeTotal = DB::table('mst_warehouse')->count();
        $recodFilterd = $recodeTotal;
        $query = DB::table('mst_warehouse');
        $query->select($colums);
        $query->limit($request->length);
        $query->offset($request->start);
        $filesApply = 0;

        if (isset($request['search']['value'])) {
            $filesApply = 1;
            $search_Value = $request['search']['value'];
            $query->where(function ($query) use ($search_Value, $serschcolm) {

                for ($i = 0; $i < count($serschcolm); $i++) {
                    if ($i == 0) {
                        $query->where($serschcolm[$i], 'Like', "%" . $search_Value . "%");
                    } else {
                        $query->where($serschcolm[$i], 'Like', "%" . $search_Value . "%");
                    }
                }
            });
        }

        $data = $query->get();
        $data = json_decode(json_encode($data), true);

        if ($filesApply == 1) {
            $recodFilterd = count($data);
        }
        foreach ($data as $key => $value) {
            $data[$key]['id'] = '<div class="avatar-xs"><span class="avatar-title rounded-circle">' . $data[$key]['id'] . '</span></div>';
            $data[$key]['name'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">' . $value['name'] . " " . $value['shortname'] . '</a></h5>';
            $data[$key]['address_line_1'] = '<p class="text-muted mb-0">' . $value['address_line_1'] . '</p><p class="text-muted mb-0">' . $value['address_line_2'] . '</p>';
            $data[$key]['city'] = '<p class="text-muted mb-0">' . $value['city'] . '</p>';
            $uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';
            $uiAction .= '<a onclick="editView(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Edit"><i class="bx bx-edit-alt"></i></a>';
            $data[$key]['action'] = $uiAction;
        }

        $jsondata = array(
            'drow' => intval($request['drow']),
            'recodeTotal' => intval($recodeTotal),
            'recodFilterd' => intval($recodFilterd),
            'data' => $data,
        );
        return $jsondata;
    }
    public function detail(Request $request)
    {
        $mainhouse = MstWarehouse::find($request->id);
        if ($mainhouse) {
            $response = successRes("Successfully get quotation item master");
            $response['data'] = $mainhouse;
        } else {
            $response = errorRes("Invalid id");
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }
}