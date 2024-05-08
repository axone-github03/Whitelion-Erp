<?php

namespace App\Http\Controllers\Quotation;

use App\Http\Controllers\Controller;
use App\Models\WlmstItemCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

//use Session;

class QuotItemCategoryMasterController extends Controller 
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

	public function index()
	{
		$data = array();
		$data['title'] = "Quotation Item Category Master ";
		return view('quotation/master/itemcategory/itemcategory', compact('data'));
	}

	function ajax(Request $request)
	{
		//DB::enableQueryLog();

		$searchColumns = array(
			0 => 'wlmst_item_categories.id',
			1 => 'wlmst_item_categories.itemcategoryname',
		);

		$columns = array(
			0 => 'wlmst_item_categories.id',
			1 => 'wlmst_item_categories.itemcategoryname',
			2 => 'wlmst_item_categories.shortname',
			3 => 'wlmst_item_categories.isactive',
			4 => 'wlmst_item_categories.cat_type',
		);

		$recordsTotal = WlmstItemCategory::count();
		$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.

		$query = WlmstItemCategory::query();
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

		$data = json_decode(json_encode($data), true);

		if ($isFilterApply == 1) {
			$recordsFiltered = count($data);
		}

		foreach ($data as $key => $value) {

			$data[$key]['id'] = highlightString($data[$key]['id'],$search_value);
			$data[$key]['itemcategoryname'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">' .  highlightString($data[$key]['itemcategoryname'],$search_value) . '</a></h5>
            <p class="text-muted mb-0">' .  highlightString(ucwords(strtolower($data[$key]['cat_type'])),$search_value) . '</p>';
			
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
			'q_category_master_id' => ['required'],
			'q_category_master_name' => ['required'],
			'q_category_master_code' => ['required'],
			'q_category_master_status' => ['required'],
		]);
		if ($validator->fails()) {

			$response = array();
			$response['status'] = 0;
			$response['msg'] = "The request could not be understood by the server due to malformed syntax";
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();

			return response()->json($response)->header('Content-Type', 'application/json');
		} else {

			$alreadyName = WlmstItemCategory::query();

			if ($request->q_category_master_id != 0) {

				$alreadyName->where('itemcategoryname', $request->q_category_master_name);
				$alreadyName->where('id', '!=', $request->q_category_master_id);
			} else {
				$alreadyName->where('itemcategoryname', $request->q_category_master_name);
			}

			$alreadyName = $alreadyName->first();

			$alreadyCode = WlmstItemCategory::query();

			if ($request->q_category_master_id != 0) {

				$alreadyCode->where('shortname', $request->q_category_master_code);
				$alreadyCode->where('id', '!=', $request->q_category_master_id);
			} else {
				$alreadyCode->where('shortname', $request->q_category_master_code);
			}

			$alreadyCode = $alreadyCode->first();

			if ($alreadyName) {

				$response = errorRes("already name exits, Try with another name");
			} else if ($alreadyCode) {

				$response = errorRes("already shortname exits, Try with another shortname");
			} else {

				if ($request->q_category_master_id != 0) {

					$MainMaster = WlmstItemCategory::find($request->q_category_master_id);
					$MainMaster->updateby = Auth::user()->id;
					$MainMaster->updateip = $request->ip();
				} else {
					$MainMaster = new WlmstItemCategory();
					$MainMaster->entryby = Auth::user()->id;
					$MainMaster->entryip = $request->ip();
				}

				$MainMaster->itemcategoryname = $request->q_category_master_name;
				$MainMaster->shortname = $request->q_category_master_code;
				$MainMaster->isactive = $request->q_category_master_status;
				$MainMaster->app_sequence = $request->q_category_master_sequence;
				if ($request->filled('q_category_master_type')) {
					$MainMaster->cat_type = implode(",", $request->q_category_master_type);
				}
				$MainMaster->display_group = $request->q_category_master_display_group;
				$MainMaster->remark = isset($request->q_category_master_remark) ? $request->q_category_master_remark : '';
				


				$MainMaster->save();
				if ($MainMaster) {

					if ($request->q_category_master_id != 0) {

						$response = successRes("Successfully saved item caegory master");

						$debugLog = array();
						$debugLog['name'] = "quot-item-caegory-master-edit";
						$debugLog['description'] = "quotation item caegory master #" . $MainMaster->id . "(" . $MainMaster->itemcategoryname . ")"." has been updated ";
						saveDebugLog($debugLog);
					} else {
						$response = successRes("Successfully added item caegory master");

						$debugLog = array();
						$debugLog['name'] = "quot-item-caegory-master-add";
						$debugLog['description'] = "quotation item caegory master #" . $MainMaster->id . "(" . $MainMaster->itemcategoryname . ") has been added ";
						saveDebugLog($debugLog);
					}
				}
			}

			return response()->json($response)->header('Content-Type', 'application/json');
		}
	}

	public function detail(Request $request)
	{

		$MainMaster = WlmstItemCategory::find($request->id);
		if ($MainMaster) {

			$response = successRes("Successfully get quotation item caegory master");
			$response['data'] = $MainMaster;
		} else {
			$response = errorRes("Invalid id");
		}
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function delete(Request $request) {

		$ItemCategory = WlmstItemCategory::find($request->id);
		if ($ItemCategory) {

			$debugLog = array();
			$debugLog['name'] = "quot-item-category-delete";
			$debugLog['description'] = "quot item category #" . $ItemCategory->id . "(" . $ItemCategory->itemcategoryname . ") has been deleted";
			saveDebugLog($debugLog);

			$ItemCategory->delete();

		}
		$response = successRes("Successfully delete Category");
		return response()->json($response)->header('Content-Type', 'application/json');

	}

	function export(Request $request)
	{

		$columns = array(
			'wlmst_item_categories.id',
			'wlmst_item_categories.itemcategoryname',
			'wlmst_item_categories.shortname',
			'wlmst_item_categories.display_group',
			'wlmst_item_categories.isactive',
			'wlmst_item_categories.cat_type',
			'wlmst_item_categories.remark',
			'wlmst_item_categories.created_at',
			'wlmst_item_categories.entryby',
			DB::raw('CONCAT(entry_user.first_name," ",entry_user.last_name) as entrybyname'),
			'wlmst_item_categories.entryip',
			'wlmst_item_categories.updated_at',
			'wlmst_item_categories.updateby',
			DB::raw('CONCAT(update_user.first_name," ",update_user.last_name) as updatebyname'),
			'wlmst_item_categories.updateip',
		);

		$query = WlmstItemCategory::query();
		$query->select($columns);
		$query->leftJoin('users as entry_user', 'entry_user.id', '=', 'wlmst_item_categories.entryby');
		$query->leftJoin('users as update_user', 'update_user.id', '=', 'wlmst_item_categories.updateby');
		$data = $query->get();

		$headers = array("#ID", "Category Name", "Short Name", "Display Group", "Status", "Status Label", "Category Type", "Remark", "Created At", "Entry By", "Entry By Name", "Entry Ip", "Updated At", "Update By", "Update By Name", "Update Ip");

		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="quotation-category-data.csv"');

		$fp = fopen('php://output', 'wb');

		fputcsv($fp, $headers);

		foreach ($data as $key => $value) {

			$lineVal = array(
				$value->id,
				$value->itemcategoryname,
				$value->shortname,
				$value->display_group,
				$value->isactive,
				getUserStatus($value->isactive),
				$value->cat_type,
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
