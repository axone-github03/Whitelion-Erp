<?php

namespace App\Http\Controllers\Warranty;

use App\Models\Wlmst_ProductWarranty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

//use Session;

class WarrantyManagementController extends Controller
{

	public function __construct()
	{

		$this->middleware(function ($request, $next) {

			$tabCanAccessBy = array(0, 1, 11);

			if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
				return redirect()->route('dashboard');
			}
			return $next($request);
		});
	}

	public function index()
	{
		$data = array();
		$data['title'] = "Warranty Registration";
		return view('warranty/main', compact('data'));
	}

	function ajax(Request $request)
	{
		//DB::enableQueryLog();

		$searchColumns = array(
			0 => 'wlmst_product_warranty.id',
			1 => 'wlmst_product_warranty.fullname',
			2 => 'wlmst_product_warranty.mobile',
			3 => 'wlmst_product_warranty.email',
		);

		$columns = array(
			'wlmst_product_warranty.id',
			'wlmst_product_warranty.fullname',
			'wlmst_product_warranty.email',
			'wlmst_product_warranty.mobile',
			'wlmst_product_warranty.address_houseno',
			'wlmst_product_warranty.address_society',
			'wlmst_product_warranty.address_area',
			'wlmst_product_warranty.address_city',
			'wlmst_product_warranty.invoice_image',
			'wlmst_product_warranty.warranty_start_date',
			'wlmst_product_warranty.isverify',
		);

		$recordsTotal = Wlmst_ProductWarranty::count();
		$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.

		$query = Wlmst_ProductWarranty::query();
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
		$data1 = $query->get();
		// echo "<pre>";
		// print_r(DB::getQueryLog());
		// die;

		$data = json_decode(json_encode($data), true);

		if ($isFilterApply == 1) {
			$recordsFiltered = count($data);
		}

		foreach ($data as $key => $value) {
			$data[$key]['id'] = highlightString($data[$key]['id'],$search_value);
			$data[$key]['name'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">' .  highlightString($data[$key]['fullname'],$search_value) . '</a></h5>
            <p class="text-muted mb-0">' .  highlightString($data[$key]['email'],$search_value) . '</p>
            <p class="text-muted mb-0">' .  highlightString($data[$key]['mobile'],$search_value) . '</p>';

			$data[$key]['address'] = '<p class="text-muted mb-0">' .  highlightString($data[$key]['address_houseno'] .' '.$data[$key]['address_society'],$search_value) . '</p>'.
			'<p class="text-muted mb-0">' .  highlightString($data[$key]['address_area'] .' '.$data[$key]['address_city'],$search_value) . '</p>';

			$warranty_start_date = "-";
			if($data[$key]['warranty_start_date'] != null || $data[$key]['warranty_start_date'] != ''){
				$warranty_start_date = date('d-m-Y', strtotime($data[$key]['warranty_start_date']));
			}

			$data[$key]['warranty_start_date'] = '<p class="text-muted mb-0">' .  highlightString($warranty_start_date,$search_value) .'</p>';

			$uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';

			if ($value['isverify'] == 1) {

				$uiAction .= '<li class="list-inline-item px-2 ">';
				$uiAction .= '<a data-bs-toggle="tooltip"  data-bs-original-title="Data Verified"  href="javascript: void(0);" title="Data Verified" class=" "><i class="bx bx-check-circle" style="color: #34c38f;"></i></a>';
				$uiAction .= '</li>';

			}

			if ($value['isverify'] == 0) {

				$uiAction .= '<li class="list-inline-item px-2 ">';
				$uiAction .= '<a data-bs-toggle="tooltip"  data-bs-original-title="Data Not Verified"  href="javascript: void(0);" title="Data Not Verified" class=" "><i class="bx bx-x-circle" style="color: #f46a6a;"></i></a>';
				$uiAction .= '</li>';

			}

			$uiAction .= '<li class="list-inline-item px-2">';
			$uiAction .= '<a onclick="showdetail(\'' . $value['id'] . '\')" href="javascript: void(0);" title="View Detail"><i class="mdi mdi-eye"></i></a>';
			$uiAction .= '</li>';

			$uiAction .= '</ul>';

			$data[$key]['action'] = $uiAction;
		}

		$jsonData = array(
			"draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
			"recordsTotal" => intval($recordsTotal), // total number of records
			"recordsFiltered" => intval($recordsFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
			"data" => $data, // total data array
			"data1" => $data1, // total data array
		);

		return $jsonData;
	}

	public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'q_warranty_id' => ['required'],
            'q_warranty_start_date' => ['required']
        ]);

        if ($validator->fails()) {

            $response = array();
            $response['status'] = 0;
            $response['msg'] = "The request could not be understood by the server due to malformed syntax";
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors();

            return response()->json($response)->header('Content-Type', 'application/json');
        } else {

            if ($request->q_warranty_id != 0) {

                $MainMaster = Wlmst_ProductWarranty::find($request->q_warranty_id);
                $MainMaster->updateby = Auth::user()->id;
                $MainMaster->updateip = $request->ip();
            } else {
                $MainMaster = new Wlmst_ProductWarranty();
                $MainMaster->entryby = Auth::user()->id;
                $MainMaster->entryip = $request->ip();
            }

            $MainMaster->warranty_start_date = date('Y-m-d', strtotime($request->q_warranty_start_date));
            $MainMaster->isverify = '1';

            $MainMaster->save();
            if ($MainMaster) {

                if ($request->q_warranty_id != 0) {

                    $response = successRes("Successfully saved product warranty data");

                    $debugLog = array();
                    $debugLog['name'] = "product-warranty-edit";
                    $debugLog['description'] = "product warranty data #" . $MainMaster->id . "(" . $MainMaster->warranty_start_date . ")" . " has been updated ";
                    saveDebugLog($debugLog);
                } else {
                    $response = successRes("Successfully product warranty data");

                    $debugLog = array();
                    $debugLog['name'] = "product-warranty-add";
                    $debugLog['description'] = "product warranty data #" . $MainMaster->id . "(" . $MainMaster->warranty_start_date . ") has been added ";
                    saveDebugLog($debugLog);
                }
            }
            // }

            return response()->json($response)->header('Content-Type', 'application/json');
        }
    }
	public function detail(Request $request)
    {
        $WarrantyMaster = Wlmst_ProductWarranty::find($request->id);

        if ($WarrantyMaster) {
            $response = successRes("Successfully get warranty register data");

			if($WarrantyMaster->invoice_image != null && $WarrantyMaster->invoice_image != ''){
				$WarrantyMaster['invoice_image'] = getSpaceFilePath($WarrantyMaster->invoice_image);
			}
        
            $response['data'] = $WarrantyMaster;
        } else {
            $response = errorRes("Invalid id");
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }
}
