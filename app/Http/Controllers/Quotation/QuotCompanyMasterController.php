<?php

namespace App\Http\Controllers\Quotation;

use App\Http\Controllers\Controller;

use App\Models\WlmstCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

//use Session;

class QuotCompanyMasterController extends Controller
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
		$data['title'] = "Quotation Company Master ";
		return view('quotation/master/company/company', compact('data'));
	}

	function ajax(Request $request)
	{
		//DB::enableQueryLog();

		$searchColumns = array(
			0 => 'wlmst_companies.id',
			1 => 'wlmst_companies.companyname',
		);

		$columns = array(
			0 => 'wlmst_companies.id',
			1 => 'wlmst_companies.companyname',
			2 => 'wlmst_companies.shortname',
			3 => 'wlmst_companies.isactive',
		);

		$recordsTotal = WlmstCompany::count();
		$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.

		$query = WlmstCompany::query();
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
			$data[$key]['companyname'] = "<p>" .  highlightString($data[$key]['companyname'],$search_value) . '</p>';
			$data[$key]['shortname'] = "<p>" .  highlightString($data[$key]['shortname'],$search_value) . '</p>';

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
			'q_company_master_id' => ['required'],
			'q_company_master_name' => ['required'],
			'q_company_master_code' => ['required'],
			'q_company_master_msdiscount' => ['required'],
			'q_company_master_status' => ['required'],
		]);
		if ($validator->fails()) {

			$response = array();
			$response['status'] = 0;
			$response['msg'] = "The request could not be understood by the server due to malformed syntax";
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();

			return response()->json($response)->header('Content-Type', 'application/json');
		} else {

			$alreadyName = WlmstCompany::query();

			if ($request->q_company_master_id != 0) {

				$alreadyName->where('companyname', $request->q_company_master_name);
				$alreadyName->where('id', '!=', $request->q_company_master_id);
			} else {
				$alreadyName->where('companyname', $request->q_company_master_name);
			}

			$alreadyName = $alreadyName->first();

			$alreadyCode = WlmstCompany::query();

			if ($request->q_company_master_id != 0) {

				$alreadyCode->where('shortname', $request->q_company_master_code);
				$alreadyCode->where('id', '!=', $request->q_company_master_id);
			} else {
				$alreadyCode->where('shortname', $request->q_company_master_code);
			}

			$alreadyCode = $alreadyCode->first();

			if ($alreadyName) {

				$response = errorRes("already name exits, Try with another name");
			} else if ($alreadyCode) {

				$response = errorRes("already shortname exits, Try with another shortname");
			} else {


				
				if ($request->q_company_master_id != 0) {
					$MainMaster = WlmstCompany::find($request->q_company_master_id);
					$MainMaster->updateby = Auth::user()->id;
					$MainMaster->updateip = $request->ip();
				} else {
					$MainMaster = new WlmstCompany();
					$MainMaster->entryby = Auth::user()->id;
					$MainMaster->entryip = $request->ip();
				}

				$MainMaster->companyname = $request->q_company_master_name;
				$MainMaster->shortname = $request->q_company_master_code;
				$MainMaster->maxdisc = $request->q_company_master_msdiscount;
				$MainMaster->isactive = $request->q_company_master_status;
				$MainMaster->remark = isset($request->q_company_master_remark) ? $request->q_company_master_remark : '';


				$MainMaster->save();
				if ($MainMaster) {

					if ($request->q_company_master_id != 0) {

						$response = successRes("Successfully saved company master");

						$debugLog = array();
						$debugLog['name'] = "quot-company-master-edit";
						$debugLog['description'] = "quotation company master #" . $MainMaster->id . "(" . $MainMaster->companyname . ")" . " has been updated ";
						saveDebugLog($debugLog);
					} else {
						$response = successRes("Successfully added company master");

						$debugLog = array();
						$debugLog['name'] = "quot-company-master-add";
						$debugLog['description'] = "quotation company master #" . $MainMaster->id . "(" . $MainMaster->companyname . ") has been added ";
						saveDebugLog($debugLog);
					}
				}
			}

			return response()->json($response)->header('Content-Type', 'application/json');
		}
	}

	public function detail(Request $request)
	{

		$MainMaster = WlmstCompany::find($request->id);
		if ($MainMaster) {

			$response = successRes("Successfully get quotation company master");
			$response['data'] = $MainMaster;
		} else {
			$response = errorRes("Invalid id");
		}
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function delete(Request $request)
	{

		$ItemCompany = WlmstCompany::find($request->id);
		if ($ItemCompany) {

			$debugLog = array();
			$debugLog['name'] = "quot-item-company-delete";
			$debugLog['description'] = "quot item company #" . $ItemCompany->id . "(" . $ItemCompany->companyname . ") has been deleted";
			saveDebugLog($debugLog);

			$ItemCompany->delete();
		}
		$response = successRes("Successfully delete Company");
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	function export(Request $request)
	{

		$columns = array(
			'wlmst_companies.id',
			'wlmst_companies.companyname',
			'wlmst_companies.shortname',
			'wlmst_companies.maxdisc',
			'wlmst_companies.isactive',
			'wlmst_companies.remark',
			'wlmst_companies.created_at',
			'wlmst_companies.entryby',
			DB::raw('CONCAT(entry_user.first_name," ",entry_user.last_name) as entrybyname'),
			'wlmst_companies.entryip',
			'wlmst_companies.updated_at',
			'wlmst_companies.updateby',
			DB::raw('CONCAT(update_user.first_name," ",update_user.last_name) as updatebyname'),
			'wlmst_companies.updateip',
		);

		$query = WlmstCompany::query();
		$query->select($columns);
		$query->leftJoin('users as entry_user', 'entry_user.id', '=', 'wlmst_companies.entryby');
		$query->leftJoin('users as update_user', 'update_user.id', '=', 'wlmst_companies.updateby');
		$data = $query->get();

		$headers = array("#ID", "Company Name", "Short Name", "Max Disc", "Status", "Status Label", "Remark", "Created At", "Entry By", "Entry By Name", "Entry Ip", "Updated At", "Update By", "Update By Name", "Update Ip");

		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="quotation-company-data.csv"');

		$fp = fopen('php://output', 'wb');

		fputcsv($fp, $headers);

		foreach ($data as $key => $value) {

			$lineVal = array(
				$value->id,
				$value->companyname,
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
