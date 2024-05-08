<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\DebugLog;
use App\Models\WlmstCompany;
use App\Models\WlmstItem;
use App\Models\WlmstItemCategory;
use App\Models\WlmstItemSubgroup;
// use PDF;
// use Dompdf\Dompdf;
use App\Models\Wlmst_Client;
use App\Models\Wlmst_ItemGroup;
use App\Models\Wlmst_ItemPrice;
use App\Models\Wlmst_QuotationError;
use App\Models\Wltrn_Quotation;
use App\Models\Wltrn_QuotItemdetail;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

// use Illuminate\Http\Request;
date_default_timezone_set("Asia/Kolkata");
class QuotationApiController extends Controller {

	public function PostQuotCompanyList(Request $request) {

		$searchColumns = array(
			0 => 'wlmst_companies.companyname',
		);

		$columns = array(
			0 => 'wlmst_companies.id',
			1 => 'wlmst_companies.companyname',
			2 => 'wlmst_companies.shortname',
			3 => 'wlmst_companies.isactive',
		);

		$response = array();
		$status = 0;
		$status_code = http_response_code();
		$msg = "api";
		$data = null;

		try {
			$response = array();
			$status = 1;
			$msg = "Quotation Item Company List Success";
			$query = WlmstCompany::query();
			$query->select($columns);
			$query->where('isactive', '1');

			if (isset($request->q)) {
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

			$data = $query->paginate(20);
		} catch (QueryException $ex) {
			$response = array();
			$status = 0;
			$msg = "Please Contact To Admin";
			$data = $ex;
		}

		$response['status'] = $status;
		$response['status_code'] = $status_code;
		$response['msg'] = $msg;
		$response['data'] = $data;

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function PostQuotCategoryList(Request $request) {

		$searchColumns = array(
			0 => 'wlmst_item_categories.itemcategoryname',
		);

		$columns = array(
			0 => 'wlmst_item_categories.id',
			1 => 'wlmst_item_categories.itemcategoryname',
			2 => 'wlmst_item_categories.shortname',
			3 => 'wlmst_item_categories.isactive',
			4 => 'wlmst_item_categories.display_group',
		);

		$response = array();
		$status = 0;
		$status_code = http_response_code();
		$msg = "api";
		$data = null;

		try {
			$response = array();
			$status = 1;
			$msg = "Quotation Item Category List Success";
			$query = WlmstItemCategory::query();
			$query->select($columns);
			$query->where('isactive', '1');
			$query->where('id', '<>', '13');
			if (isset($request->display_group)) {
				$query->where('display_group', $request->display_group);
			}

			if (isset($request->q)) {
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

			$data = $query->paginate(20);
		} catch (QueryException $ex) {
			$response = array();
			$status = 0;
			$msg = "Please Contact To Admin";
			$data = $ex;
		}

		$response['status'] = $status;
		$response['status_code'] = $status_code;
		$response['msg'] = $msg;
		$response['data'] = $data;

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function PostQuotGroupList(Request $request) {

		$searchColumns = array(
			0 => 'wlmst_item_groups.itemgroupname',
		);

		$columns = array(
			0 => 'wlmst_item_groups.id',
			1 => 'wlmst_item_groups.itemgroupname',
			2 => 'wlmst_item_groups.shortname',
			3 => 'wlmst_item_groups.isactive',
		);

		$response = array();
		$status = 0;
		$status_code = http_response_code();
		$msg = "api";
		$data = null;

		try {
			$response = array();
			$status = 1;
			$msg = "Quotation Item Group List Success";
			$query = Wlmst_ItemGroup::query();
			$query->select($columns);
			$query->where('app_isactive', '1');
			$query->orderBy('sequence');

			if (isset($request->q)) {
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

			$data = $query->paginate(20);
		} catch (QueryException $ex) {
			$response = array();
			$status = 0;
			$msg = "Please Contact To Admin";
			$data = $ex;
		}

		$response['status'] = $status;
		$response['status_code'] = $status_code;
		$response['msg'] = $msg;
		$response['data'] = $data;

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function PostQuotSubQuotGroupList(Request $request) {
		$searchColumns = array(
			0 => 'wlmst_item_subgroups.itemsubgroupname',
		);

		$columns = array(
			0 => 'wlmst_item_subgroups.id',
			1 => 'wlmst_item_subgroups.itemsubgroupname',
			2 => 'wlmst_item_subgroups.shortname',
			3 => 'wlmst_item_subgroups.isactive',
			4 => 'wlmst_item_subgroups.company_id',
			5 => 'wlmst_item_subgroups.itemgroup_id',
		);

		$response = array();
		$status = 0;
		$status_code = http_response_code();
		$msg = "api";
		$data = null;

		try {
			$response = array();
			$status = 1;
			$msg = "Quotation Item SubGroup List Success";
			$query = WlmstItemSubgroup::with(array('company' => function ($query) {
				$query->select('id', 'companyname');
			}, 'itemgroup' => function ($query) {
				$query->select('id', 'itemgroupname');
			}));
			$query->select($columns);
			$query->where('wlmst_item_subgroups.isactive', '1');
			if (isset($request->company_id)) {
				// $query->where('wlmst_item_subgroups.company_id', $request->company_id);
				// $query->whereIn('wlmst_item_subgroups.company_id', [$request->company_id]);
				// $query->whereRaw('FIND_IN_SET(' . $request->company_id . ',wlmst_item_subgroups.company_id)');
				$query->whereRaw("find_in_set(" . $request->company_id . ",wlmst_item_subgroups.company_id)");
				// $query->where(DB::raw("find_in_set('$request->company_id', 'wlmst_item_subgroups.company_id')"));
			}
			if (isset($request->itemgroup_id)) {
				$query->where('wlmst_item_subgroups.itemgroup_id', $request->itemgroup_id);
			}

			if (isset($request->q)) {
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

			$data = $query->paginate(20);
		} catch (QueryException $ex) {
			$response = array();
			$status = 0;
			$msg = "Please Contact To Admin";
			$data = $ex;
		}

		$response['status'] = $status;
		$response['status_code'] = $status_code;
		$response['msg'] = $msg;
		$response['data'] = $data;

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function PostQuotItemList(Request $request) {
		DB::enableQueryLog();
		$searchColumns = array(
			0 => 'wlmst_items.itemname',
			1 => 'wlmst_item_categories.itemcategoryname',
		);

		$columns = array(
			'wlmst_item_prices.id as priceid',
			'wlmst_items.id as itemid',
			'wlmst_items.itemname',
			'wlmst_items.app_display_name',
			'wlmst_items.module',
			'wlmst_items.max_module',
			'wlmst_items.is_special',
			'wlmst_items.additional_remark',
			'wlmst_items.remark',
			'wlmst_items.itemcategory_id',
			'wlmst_item_categories.itemcategoryname',
			'wlmst_item_prices.mrp',
			'wlmst_item_prices.company_id',
			'wlmst_companies.companyname',
			'wlmst_item_prices.itemgroup_id',
			'wlmst_item_groups.itemgroupname',
			'wlmst_item_prices.itemsubgroup_id',
			'wlmst_item_subgroups.itemsubgroupname',
			'wlmst_items.image',
			'wlmst_item_prices.code',
		);

		$response = array();
		$status = 0;
		$status_code = http_response_code();
		$msg = "api";
		$data = null;

		try {
			$response = array();
			$status = 1;
			$msg = "Quotation Item List Success";

			// $request
			$query = WlmstItem::query();
			$query->select($columns);

			$query->leftJoin('wlmst_item_categories', 'wlmst_items.itemcategory_id', '=', 'wlmst_item_categories.id');
			$query->leftJoin('wlmst_item_prices', 'wlmst_items.id', '=', 'wlmst_item_prices.item_id');
			$query->leftJoin('wlmst_companies', 'wlmst_companies.id', '=', 'wlmst_item_prices.company_id');
			$query->leftJoin('wlmst_item_groups', 'wlmst_item_groups.id', '=', 'wlmst_item_prices.itemgroup_id');
			$query->leftJoin('wlmst_item_subgroups', 'wlmst_item_subgroups.id', '=', 'wlmst_item_prices.itemsubgroup_id');

			$query->where('wlmst_items.isactive', 1);

			if (isset($request->quot_id) || isset($request->quotgroup_id) || isset($request->room_no) || isset($request->board_no)) {
				$Quot_Id = $request->quot_id;
				$Quotgroup_Id = $request->quotgroup_id;
				$Room_No = $request->room_no;
				$Board_No = $request->board_no;

				$query->selectRaw('case when wltrn_quot_itemdetails.qty is null then 0 else wltrn_quot_itemdetails.qty end as qty');
				$query->selectRaw('case when wltrn_quot_itemdetails.qty is null then wlmst_item_prices.mrp else wltrn_quot_itemdetails.rate end as mrp');
				$query->selectRaw('case when wltrn_quot_itemdetails.qty is null then 0 else wltrn_quot_itemdetails.net_amount end as net_amount');

				$query->leftJoin('wltrn_quot_itemdetails', function ($query) use ($Quot_Id, $Quotgroup_Id, $Room_No, $Board_No) {
					$query->where('wltrn_quot_itemdetails.quot_id', '=', $Quot_Id);
					$query->where('wltrn_quot_itemdetails.quotgroup_id', '=', $Quotgroup_Id);
					$query->where('wltrn_quot_itemdetails.room_no', '=', $Room_No);
					$query->where('wltrn_quot_itemdetails.board_no', '=', $Board_No);
					// $query->on('wltrn_quot_itemdetails.company_id', '=', 'wlmst_item_prices.company_id');
					// $query->on('wltrn_quot_itemdetails.itemgroup_id', '=', 'wlmst_item_prices.itemgroup_id');
					// $query->on('wltrn_quot_itemdetails.itemsubgroup_id', '=', 'wlmst_item_prices.itemsubgroup_id');
					$query->on('wltrn_quot_itemdetails.item_id', '=', 'wlmst_item_prices.item_id');
				});
			} else {
				$query->addSelect(DB::raw("'0' as qty"));
			}

			if (isset($request->range_subgroup)) {
				$Range_Subgroup = explode(',', $request->range_subgroup);
				// $Range_group = explode(',', $request->range_group);
				$query->where(function ($query) use ($Range_Subgroup) {
					for ($i = 0; $i < count($Range_Subgroup); $i++) {
						$range_group_id = WlmstItemSubgroup::find($Range_Subgroup[$i])->itemgroup_id;
						// $range_group_id = $Range_group[$i];
						$range_subgroup_id = $Range_Subgroup[$i];
						if ($i == 0) {
							$query->where(function ($query) use ($range_group_id, $range_subgroup_id) {
								$query->whereIn('wlmst_item_prices.itemgroup_id', [$range_group_id])
									->whereIn('wlmst_item_prices.itemsubgroup_id', [$range_subgroup_id]);
							});
						} else {
							$query->orWhere(function ($query) use ($range_group_id, $range_subgroup_id) {
								$query->whereIn('wlmst_item_prices.itemgroup_id', [$range_group_id])
									->whereIn('wlmst_item_prices.itemsubgroup_id', [$range_subgroup_id]);
							});
						}
					}
				});
			}

			if (isset($request->company_id)) {
				$query->where('wlmst_item_prices.company_id', $request->company_id);
			}

			if (isset($request->itemgroup_id)) {
				$query->where('wlmst_item_prices.itemgroup_id', $request->itemgroup_id);
			}

			if (isset($request->itemsubgroup_id)) {
				$query->whereIn('wlmst_item_prices.itemsubgroup_id', explode(",", $request->itemsubgroup_id));
			}

			if (isset($request->itemcategory_id)) {
				$query->where('wlmst_items.itemcategory_id', $request->itemcategory_id);
			}

			if (isset($request->q)) {
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

			$data = $query->paginate(20);
			// $data = $query->get();
		} catch (QueryException $ex) {
			$response = array();
			$status = 0;
			$msg = $query;
			$data = $ex;
		}

		$response['status'] = $status;
		$response['status_code'] = $status_code;
		$response['msg'] = $msg;
		// $response['msg'] = DB::getQueryLog();
		$response['data'] = $data;

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function PostQuotTypeList(Request $request) {
		$response = array();
		$status_code = http_response_code();
		$status = 1;
		$msg = "Quotation Item List Success";
		$data = [
			array('id' => 1, 'name' => 'Site Visit'),
			array('id' => 2, 'name' => 'Architect Layout'),
		];

		$response['status'] = $status;
		$response['status_code'] = $status_code;
		$response['msg'] = $msg;
		$response['data'] = $data;

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function PostQuotTypeList_new(Request $request) {

		$searchColumns = array(
			0 => 'wlmst_items.itemname',
			1 => 'wlmst_item_categories.itemcategoryname',
		);

		$columns = array(
			0 => 'wlmst_quotation_type.id',
			1 => 'wlmst_quotation_type.id',
			2 => 'wlmst_quotation_type.itemname',
			3 => 'wlmst_quotation_type.module',
			4 => 'wlmst_quotation_type.is_special',
			5 => 'wlmst_quotation_type.additional_remark',
		);

		$response = array();
		$status = 0;
		$status_code = http_response_code();
		$msg = "api";
		$data = null;

		try {
			$response = array();
			$status = 1;
			$msg = "Quotation Item List Success";
			$query = WlmstItem::query();
			$query->select($columns);
			$query->addSelect(DB::raw("'0' as qty"));
			$query->leftJoin('wlmst_item_categories', 'wlmst_items.itemcategory_id', '=', 'wlmst_item_categories.id');
			$query->leftJoin('wlmst_item_prices', 'wlmst_items.id', '=', 'wlmst_item_prices.item_id');
			$query->where('wlmst_item_prices.itemgroup_id', '5');

			if (isset($request->range_subgroup)) {
				$Range_Subgroup = explode(',', $request->range_subgroup);
				// $Range_group = explode(',', $request->range_group);
				$query->where(function ($query) use ($Range_Subgroup) {
					for ($i = 0; $i < count($Range_Subgroup); $i++) {
						$range_group_id = WlmstItemSubgroup::find($Range_Subgroup[$i])->itemgroup_id;
						// $range_group_id = $Range_group[$i];
						$range_subgroup_id = $Range_Subgroup[$i];
						if ($i == 0) {
							$query->where(function ($query) use ($range_group_id, $range_subgroup_id) {
								$query->whereIn('wlmst_item_prices.itemgroup_id', [$range_group_id])
									->whereIn('wlmst_item_prices.itemsubgroup_id', [$range_subgroup_id]);
							});
						} else {
							$query->orWhere(function ($query) use ($range_group_id, $range_subgroup_id) {
								$query->whereIn('wlmst_item_prices.itemgroup_id', [$range_group_id])
									->whereIn('wlmst_item_prices.itemsubgroup_id', [$range_subgroup_id]);
							});
						}
					}
				});
			}

			if (isset($request->company_id)) {
				$query->where('wlmst_item_prices.company_id', $request->company_id);
			}

			if (isset($request->itemsubgroup_id)) {
				$query->whereIn('wlmst_item_prices.itemsubgroup_id', explode(",", $request->itemsubgroup_id));
			}

			if (isset($request->itemcategory_id)) {
				$query->where('wlmst_items.itemcategory_id', $request->itemcategory_id);
			}

			if (isset($request->q)) {
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
		} catch (QueryException $ex) {
			$response = array();
			$status = 0;
			$msg = $query;
			$data = $ex;
		}

		$response['status'] = $status;
		$response['status_code'] = $status_code;
		$response['msg'] = $msg;
		$response['data'] = $data;

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function GetPlatSizeList(Request $request) {

		DB::enableQueryLog();
		$searchColumns = array(
			0 => 'wlmst_items.itemname',
			1 => 'wlmst_item_categories.itemcategoryname',
		);

		$columns = array(
			'wlmst_item_prices.id as priceid',
			'wlmst_items.id as itemid',
			'wlmst_items.itemname',
			'wlmst_items.app_display_name',
			'wlmst_items.module',
			'wlmst_items.is_special',
			'wlmst_items.additional_remark',
			'wlmst_items.remark',
			'wlmst_items.itemcategory_id',
			'wlmst_item_categories.itemcategoryname',
			'wlmst_item_prices.mrp',
			'wlmst_item_prices.company_id',
			'wlmst_companies.companyname',
			'wlmst_item_prices.itemgroup_id',
			'wlmst_item_groups.itemgroupname',
			'wlmst_item_prices.itemsubgroup_id',
			'wlmst_item_subgroups.itemsubgroupname',
			'wlmst_items.image',
			'wlmst_item_prices.code',
			'wlmst_items.max_module',
		);

		$response = array();
		$status = 0;
		$status_code = http_response_code();
		$msg = "api";
		$data = null;

		try {
			$response = array();
			$status = 1;
			$msg = "Quotation Item List Success";

			// ------------- PLATE LIST QUERY START -------------
			$query = WlmstItem::query();
			$query->select($columns);

			$query->leftJoin('wlmst_item_categories', 'wlmst_items.itemcategory_id', '=', 'wlmst_item_categories.id');
			$query->leftJoin('wlmst_item_prices', 'wlmst_items.id', '=', 'wlmst_item_prices.item_id');
			$query->leftJoin('wlmst_companies', 'wlmst_companies.id', '=', 'wlmst_item_prices.company_id');
			$query->leftJoin('wlmst_item_groups', 'wlmst_item_groups.id', '=', 'wlmst_item_prices.itemgroup_id');
			$query->leftJoin('wlmst_item_subgroups', 'wlmst_item_subgroups.id', '=', 'wlmst_item_prices.itemsubgroup_id');
			$query->where('wlmst_item_prices.itemgroup_id', '5');

			if (isset($request->quot_id) || isset($request->quotgroup_id) || isset($request->room_no) || isset($request->board_no)) {
				$Quot_Id = $request->quot_id;
				$Quotgroup_Id = $request->quotgroup_id;
				$Room_No = $request->room_no;
				$Board_No = $request->board_no;

				$query->selectRaw('case when wltrn_quot_itemdetails.qty is null then 0 else wltrn_quot_itemdetails.qty end as qty');
				$query->selectRaw('case when wltrn_quot_itemdetails.qty is null then wlmst_item_prices.mrp else wltrn_quot_itemdetails.rate end as mrp');
				$query->selectRaw('case when wltrn_quot_itemdetails.qty is null then 0 else wltrn_quot_itemdetails.net_amount end as net_amount');

				$query->leftJoin('wltrn_quot_itemdetails', function ($query) use ($Quot_Id, $Quotgroup_Id, $Room_No, $Board_No) {
					$query->where('wltrn_quot_itemdetails.quot_id', '=', $Quot_Id);
					$query->where('wltrn_quot_itemdetails.quotgroup_id', '=', $Quotgroup_Id);
					$query->where('wltrn_quot_itemdetails.room_no', '=', $Room_No);
					$query->where('wltrn_quot_itemdetails.board_no', '=', $Board_No);
					// $query->on('wltrn_quot_itemdetails.company_id', '=', 'wlmst_item_prices.company_id');
					// $query->on('wltrn_quot_itemdetails.itemgroup_id', '=', 'wlmst_item_prices.itemgroup_id');
					// $query->on('wltrn_quot_itemdetails.itemsubgroup_id', '=', 'wlmst_item_prices.itemsubgroup_id');
					$query->on('wltrn_quot_itemdetails.item_id', '=', 'wlmst_item_prices.item_id');
				});
			} else {
				$query->addSelect(DB::raw("'0' as qty"));
			}

			if (isset($request->range_subgroup)) {
				$Range_Subgroup = explode(',', $request->range_subgroup);
				// $Range_group = explode(',', $request->range_group);
				$query->where(function ($query) use ($Range_Subgroup) {
					for ($i = 0; $i < count($Range_Subgroup); $i++) {
						$range_group_id = WlmstItemSubgroup::find($Range_Subgroup[$i])->itemgroup_id;
						// $range_group_id = $Range_group[$i];
						$range_subgroup_id = $Range_Subgroup[$i];
						if ($i == 0) {
							$query->where(function ($query) use ($range_group_id, $range_subgroup_id) {
								$query->whereIn('wlmst_item_prices.itemgroup_id', [$range_group_id])
									->whereIn('wlmst_item_prices.itemsubgroup_id', [$range_subgroup_id]);
							});
						} else {
							$query->orWhere(function ($query) use ($range_group_id, $range_subgroup_id) {
								$query->whereIn('wlmst_item_prices.itemgroup_id', [$range_group_id])
									->whereIn('wlmst_item_prices.itemsubgroup_id', [$range_subgroup_id]);
							});
						}
					}
				});
			}

			if (isset($request->company_id)) {
				$query->where('wlmst_item_prices.company_id', $request->company_id);
			}

			if (isset($request->itemsubgroup_id)) {
				$query->whereIn('wlmst_item_prices.itemsubgroup_id', explode(",", $request->itemsubgroup_id));
			}

			if (isset($request->itemcategory_id)) {
				$query->where('wlmst_items.itemcategory_id', $request->itemcategory_id);
			}

			if (isset($request->q)) {
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
			$query->orderBy('module');

			$data_plate = $query->get();
			// ------------- PLATE LIST QUERY END -------------
			// ------------- CATEGORY DISPLAY 1 LIST QUERY START -------------
			$cat_1columns = array(
				0 => 'wlmst_item_categories.id',
				1 => 'wlmst_item_categories.itemcategoryname',
				2 => 'wlmst_item_categories.shortname',
				3 => 'wlmst_item_categories.isactive',
				4 => 'wlmst_item_categories.display_group',
			);
			$cat1query = WlmstItemCategory::query();
			$cat1query->select($cat_1columns);
			$cat1query->where('isactive', '1');
			$cat1query->where('display_group', '1');

			$data_cat_1 = $cat1query->get();
			// ------------- CATEGORY DISPLAY 1 LIST QUERY END -------------
			// ------------- CATEGORY DISPLAY 2 LIST QUERY START -------------
			$cat_2columns = array(
				0 => 'wlmst_item_categories.id',
				1 => 'wlmst_item_categories.itemcategoryname',
				2 => 'wlmst_item_categories.shortname',
				3 => 'wlmst_item_categories.isactive',
				4 => 'wlmst_item_categories.display_group',
			);
			$cat2query = WlmstItemCategory::query();
			$cat2query->select($cat_2columns);
			$cat2query->where('isactive', '1');
			$cat2query->where('display_group', '2');

			$data_cat_2 = $cat2query->get();
			// ------------- CATEGORY DISPLAY 2 LIST QUERY END -------------

			// ------------- BOARD ADD ON LIST QUERY START -------------

			$board_addon_query = WlmstItem::query();
			$board_addon_query->select($columns);

			$board_addon_query->leftJoin('wlmst_item_categories', 'wlmst_items.itemcategory_id', '=', 'wlmst_item_categories.id');
			$board_addon_query->leftJoin('wlmst_item_prices', 'wlmst_items.id', '=', 'wlmst_item_prices.item_id');
			$board_addon_query->leftJoin('wlmst_companies', 'wlmst_companies.id', '=', 'wlmst_item_prices.company_id');
			$board_addon_query->leftJoin('wlmst_item_groups', 'wlmst_item_groups.id', '=', 'wlmst_item_prices.itemgroup_id');
			$board_addon_query->leftJoin('wlmst_item_subgroups', 'wlmst_item_subgroups.id', '=', 'wlmst_item_prices.itemsubgroup_id');
			$board_addon_query->where('wlmst_items.itemcategory_id', '6');

			if (isset($request->quot_id) || isset($request->quotgroup_id) || isset($request->room_no) || isset($request->board_no)) {
				$Quot_Id = $request->quot_id;
				$Quotgroup_Id = $request->quotgroup_id;
				$Room_No = $request->room_no;
				$Board_No = $request->board_no;

				$board_addon_query->selectRaw('case when wltrn_quot_itemdetails.qty is null then 0 else wltrn_quot_itemdetails.qty end as qty');
				$board_addon_query->selectRaw('case when wltrn_quot_itemdetails.qty is null then wlmst_item_prices.mrp else wltrn_quot_itemdetails.rate end as mrp');
				$board_addon_query->selectRaw('case when wltrn_quot_itemdetails.qty is null then 0 else wltrn_quot_itemdetails.net_amount end as net_amount');

				$board_addon_query->leftJoin('wltrn_quot_itemdetails', function ($board_addon_query) use ($Quot_Id, $Quotgroup_Id, $Room_No, $Board_No) {
					$board_addon_query->where('wltrn_quot_itemdetails.quot_id', '=', $Quot_Id);
					$board_addon_query->where('wltrn_quot_itemdetails.quotgroup_id', '=', $Quotgroup_Id);
					$board_addon_query->where('wltrn_quot_itemdetails.room_no', '=', $Room_No);
					$board_addon_query->where('wltrn_quot_itemdetails.board_no', '=', $Board_No);
					// $query->on('wltrn_quot_itemdetails.company_id', '=', 'wlmst_item_prices.company_id');
					// $query->on('wltrn_quot_itemdetails.itemgroup_id', '=', 'wlmst_item_prices.itemgroup_id');
					// $query->on('wltrn_quot_itemdetails.itemsubgroup_id', '=', 'wlmst_item_prices.itemsubgroup_id');
					$board_addon_query->on('wltrn_quot_itemdetails.item_id', '=', 'wlmst_item_prices.item_id');
				});
			} else {
				$board_addon_query->addSelect(DB::raw("'0' as qty"));
			}

			$data_board_addon = $board_addon_query->get();
			// ------------- BOARD ADD ON LIST QUERY END -------------
			$response['data'] = $data_plate;
			$response['data1'] = $data_cat_1;
			$response['data2'] = $data_cat_2;
			$response['data3'] = $data_board_addon;
		} catch (QueryException $ex) {
			$response = array();
			$status = 0;
			$msg = $query;
			$response['data'] = $ex;
		}

		$response['status'] = $status;
		$response['status_code'] = $status_code;
		$response['msg'] = $msg;
		// $response['msg'] = DB::getQueryLog();

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function PostQuotClientSave(Request $request) {

		$res_status = "";
		$res_status_code = "";
		$res_message = "";
		$res_data = "";
		$response = array();

		$validator = Validator::make($request->all(), [
			'name' => ['required'],
			'mobile' => ['required'],
			'address' => ['required'],
		]);

		if ($validator->fails()) {
			$res_status = 0;
			$res_message = "Please Check Perameater And Value";
			$res_status_code = 400;
			$res_data = $validator->errors();
		} else {

			$alreadyName = Wlmst_Client::query();

			if ($request->client_id != 0) {

				$alreadyName->where('name', $request->name);
				$alreadyName->where('id', '!=', $request->client_id);
			} else {
				$alreadyName->where('name', $request->name);
			}

			$alreadyName = $alreadyName->first();

			$alreadyMobile = Wlmst_Client::query();

			if ($request->client_id != 0) {

				$alreadyMobile->where('mobile', $request->mobile);
				$alreadyMobile->where('id', '!=', $request->client_id);
			} else {
				$alreadyMobile->where('mobile', $request->mobile);
			}

			$alreadyMobile = $alreadyMobile->first();

			if ($alreadyName) {
				$res_status = 0;
				$res_status_code = 400;
				$res_message = "already name exits, Try with another name";
			} else if ($alreadyMobile) {
				$res_status = 0;
				$res_status_code = 400;
				$res_message = "already mobile exits, Try with another mobile";
			} else {

				if ($request->client_id != 0) {
					$ClientMaster = Wlmst_Client::find($request->client_id);
					$ClientMaster->updateby = '1';
					$ClientMaster->updateip = $request->ip();
				} else {
					$ClientMaster = new Wlmst_Client();
					$ClientMaster->entryby = '1';
					$ClientMaster->entryip = $request->ip();
				}

				$ClientMaster->name = $request->name;
				$ClientMaster->email = $request->email;
				$ClientMaster->mobile = $request->mobile;
				$ClientMaster->address = $request->address;
				$ClientMaster->remark = '0';

				$ClientMaster->save();
				if ($ClientMaster) {

					if ($request->client_id != 0) {
						$res_status = 1;
						$res_status_code = 200;
						$res_message = "Successfully saved client";

						$DebugLog = new DebugLog();
						$DebugLog->user_id = 1;
						$DebugLog->name = "quot-client-master-edit";
						$DebugLog->description = "client master #" . $ClientMaster->id . "(" . $ClientMaster->name . ")" . " has been updated";
						$DebugLog->save();
					} else {
						$res_status = 1;
						$res_status_code = 200;
						$res_message = "Successfully added client";

						$DebugLog = new DebugLog();
						$DebugLog->user_id = 1;
						$DebugLog->name = "quot-client-master-add";
						$DebugLog->description = "client master #" . $ClientMaster->id . "(" . $ClientMaster->name . ") has been added";
						$DebugLog->save();
					}
				}
			}
		}

		$response['status'] = $res_status;
		$response['status_code'] = $res_status_code;
		$response['msg'] = $res_message;
		$response['data'] = $res_data;

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function PostQuotClientList(Request $request) {
		$searchColumns = array(
			0 => 'wlmst_client.name',
			1 => 'wlmst_client.mobile',
		);

		$columns = array(
			0 => 'wlmst_client.id',
			1 => 'wlmst_client.name',
			2 => 'wlmst_client.email',
			3 => 'wlmst_client.mobile',
			4 => 'wlmst_client.address',
		);

		$response = array();
		$status = 0;
		$status_code = http_response_code();
		$msg = "api";
		$data = null;

		try {
			$response = array();
			$status = 1;
			$status_code = 200;
			$msg = "Client List Success";
			$query = Wlmst_Client::query();
			$query->select($columns);
			$query->where('isactive', '1');
			$query->limit(15);

			if (isset($request->id)) {
				$query->where('id', $request->id);
			}

			if (isset($request->q)) {
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

			// $data = $query->paginate(20);
			$data = $query->get();
		} catch (QueryException $ex) {
			$response = array();
			$status = 0;
			$status_code = 400;
			$msg = "Please Contact To Admin";
			$data = $ex;
		}

		$response['status'] = $status;
		$response['status_code'] = $status_code;
		$response['msg'] = $msg;
		$response['data'] = $data;

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function PostQuotationList(Request $request) {
		DB::enableQueryLog();

		// if (getCheckAppVersion($request->app_source, $request->app_version)) {

		$searchColumns = array(
			0 => 'wltrn_quotation.customer_name',
			0 => 'wltrn_quotation.customer_contact_no',
		);

		$columns = array(
			'wltrn_quotation.id',
			'wltrn_quotation.quotgroup_id',
			'wltrn_quotation.yy',
			'wltrn_quotation.mm',
			'wltrn_quotation.quottype_id',
			'quot_type.name AS quot_type',
			'wltrn_quotation.quotno',
			'wltrn_quotation.quot_no_str',
			'wltrn_quotation.customer_id',
			'wltrn_quotation.customer_name',
			'wltrn_quotation.customer_contact_no',

			'wltrn_quotation.architech_id',
			'arc_user.first_name AS architect_first_name',
			'arc_user.phone_number AS architect_mobile',
			'wltrn_quotation.electrician_id',
			'ele_user.first_name AS electrician_first_name',
			'ele_user.phone_number AS electrician_mobile',
			'wltrn_quotation.salesexecutive_id',
			'sales_user.first_name AS sales_first_name',
			'sales_user.phone_number AS sales_mobile',
			'wltrn_quotation.channelpartner_id',
			'chann_user.first_name AS channelpartener_first_name',
			'chann_user.phone_number AS channelpartener_mobile',

			'wltrn_quotation.inquiry_id',
			'wltrn_quotation.site_name',
			'wltrn_quotation.siteaddress',

			'wltrn_quotation.site_state_id',
			'state_list.name AS state',
			'wltrn_quotation.site_country_id',
			'country_list.name AS country',
			'wltrn_quotation.site_city_id',
			'city_list.name AS city',

			'wltrn_quotation.additional_remark',
			'wltrn_quotation.quot_date',
			'wltrn_quotation.quotationsource',
			'wltrn_quotation.default_range',
			'wltrn_quotation.created_at',
			'wltrn_quotation.entryby',
		);

		$response = array();
		$status = 0;
		$status_code = http_response_code();
		$msg = "api";
		$data = null;

		try {
			$response = array();
			$status = 1;
			$msg = "Quotation List Success";
			$query = Wltrn_Quotation::query();
			$query->select($columns);
			$query->leftJoin('users AS arc_user', 'arc_user.id', '=', 'wltrn_quotation.architech_id');
			$query->leftJoin('users AS ele_user', 'ele_user.id', '=', 'wltrn_quotation.electrician_id');
			$query->leftJoin('users AS sales_user', 'sales_user.id', '=', 'wltrn_quotation.salesexecutive_id');
			$query->leftJoin('users AS chann_user', 'chann_user.id', '=', 'wltrn_quotation.channelpartner_id');
			$query->leftJoin('state_list', 'state_list.id', '=', 'wltrn_quotation.site_state_id');
			$query->leftJoin('city_list', 'city_list.id', '=', 'wltrn_quotation.site_country_id');
			$query->leftJoin('country_list', 'country_list.id', '=', 'wltrn_quotation.site_country_id');
			$query->leftJoin('wlmst_quotation_type as quot_type', 'quot_type.id', '=', 'wltrn_quotation.quottype_id');

			$query->addSelect(DB::raw("'0' as range_company"));

			if (isset($request->q)) {
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
			$quotlist = $query->get();
			$quot_array = array();
			foreach ($quotlist as $key => $value) {
				$quot_f_array = array();

				if ($quotlist[$key]['default_range'] != "" || $quotlist[$key]['default_range'] != null) {
					$Range_Subgroup = explode(',', $quotlist[$key]['default_range']);
					// $Range_Subgroup = $quotlist[$key]['default_range'];
					$range_group = "";
					$range_company = "";
					for ($i = 0; $i < count($Range_Subgroup); $i++) {
						$range_group .= WlmstItemSubgroup::find($Range_Subgroup[$i])->itemgroup_id . ",";
						$range_company .= WlmstItemSubgroup::find($Range_Subgroup[$i])->company_id . ",";
					}
					$range_group_f = substr($range_group, 0, -1);
					// $range_company_f = substr($range_company,0,-1);
					$range_company_f = explode(",", $range_company)[0];
				} else {
					$range_group_f = "";
					$range_company_f = "";
				}

				$quot_f_array = $value;
				$quot_f_array['range_group'] = $range_group_f;
				$quot_f_array['range_company'] = $range_company_f;
				array_push($quot_array, $quot_f_array);
			}

			// $data = $query->paginate(20);
			$data = $quot_array;
		} catch (QueryException $ex) {
			$response = array();
			$status = 0;
			$msg = "Please Contact To Admin";
			$data = $ex;
		}

		$response['status'] = $status;
		$response['status_code'] = $status_code;
		$response['msg'] = $msg;
		$response['data'] = $data;
	}

	public function PostQuotationhistoryList(Request $request) {
		$searchColumns = array(
			'wltrn_quotation.customer_name',
			'wltrn_quotation.customer_contact_no',
		);

		$columns = array(
			'wltrn_quotation.id',
			'wltrn_quotation.quotgroup_id',
			'wltrn_quotation.yy',
			'wltrn_quotation.mm',
			'wltrn_quotation.quottype_id',
			'quot_type.name AS quot_type',
			'wltrn_quotation.quotno',
			'wltrn_quotation.quot_no_str',
			'wltrn_quotation.customer_id',
			'wltrn_quotation.customer_name',
			'wltrn_quotation.customer_contact_no',

			'wltrn_quotation.architech_id',
			'arc_user.first_name AS architect_first_name',
			'arc_user.phone_number AS architect_mobile',
			'wltrn_quotation.electrician_id',
			'ele_user.first_name AS electrician_first_name',
			'ele_user.phone_number AS electrician_mobile',
			'wltrn_quotation.salesexecutive_id',
			'sales_user.first_name AS sales_first_name',
			'sales_user.phone_number AS sales_mobile',
			'wltrn_quotation.channelpartner_id',
			'chann_user.first_name AS channelpartener_first_name',
			'chann_user.phone_number AS channelpartener_mobile',

			'wltrn_quotation.inquiry_id',
			'wltrn_quotation.site_name',
			'wltrn_quotation.siteaddress',

			'wltrn_quotation.site_state_id',
			'state_list.name AS state',
			'wltrn_quotation.site_country_id',
			'country_list.name AS country',
			'wltrn_quotation.site_city_id',
			'city_list.name AS city',

			'wltrn_quotation.additional_remark',
			'wltrn_quotation.quot_date',
			'wltrn_quotation.quotationsource',
			'wltrn_quotation.default_range',
			'wltrn_quotation.created_at',
			'wltrn_quotation.entryby',
		);

		$response = array();
		$status = 0;
		$status_code = http_response_code();
		$msg = "api";
		$data = null;

		try {
			$response = array();
			$status = 1;
			$msg = "Quotation List Success";
			$query = Wltrn_Quotation::query();
			$query->select($columns);
			$query->leftJoin('users AS arc_user', 'arc_user.id', '=', 'wltrn_quotation.architech_id');
			$query->leftJoin('users AS ele_user', 'ele_user.id', '=', 'wltrn_quotation.electrician_id');
			$query->leftJoin('users AS sales_user', 'sales_user.id', '=', 'wltrn_quotation.salesexecutive_id');
			$query->leftJoin('users AS chann_user', 'chann_user.id', '=', 'wltrn_quotation.channelpartner_id');
			$query->leftJoin('state_list', 'state_list.id', '=', 'wltrn_quotation.site_state_id');
			$query->leftJoin('city_list', 'city_list.id', '=', 'wltrn_quotation.site_country_id');
			$query->leftJoin('country_list', 'country_list.id', '=', 'wltrn_quotation.site_country_id');
			$query->leftJoin('wlmst_quotation_type as quot_type', 'quot_type.id', '=', 'wltrn_quotation.quottype_id');
			if ($request->quotgroup_id != 0) {
				$query->where('wltrn_quotation.quotgroup_id', $request->quotgroup_id);
			}

			if (isset($request->q)) {
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

			$data = $query->paginate(20);
		} catch (QueryException $ex) {
			$response = array();
			$status = 0;
			$msg = "Please Contact To Admin";
			$data = $ex;
		}

		$response['status'] = $status;
		$response['status_code'] = $status_code;
		$response['msg'] = $msg;
		$response['data'] = $data;

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function PostQuotBasicDetaiSave(Request $request) {
		$res_status = "";
		$res_status_code = "";
		$res_message = "";
		$res_data = "";
		$response = array();

		$validator = Validator::make($request->all(), [
			'quot_id' => ['required'],
			'quotgroup_id' => ['required'],
			'site_name' => ['required'],
			'siteaddress' => ['required'],
			'site_country_id' => ['required'],
			'site_state_id' => ['required'],
			'site_city_id' => ['required'],
			'quottype_id' => ['required'],
		]);

		if ($validator->fails()) {
			$res_status = 0;
			$res_message = "Please Check Perameater And Value";
			$res_status_code = 400;
			$res_data = $validator->errors();
		} else {
			if ($request->quot_id != 0) {
				$QuotMaster = Wltrn_Quotation::find($request->quot_id);
				$QuotMaster->updateby = '1';
				$QuotMaster->updateip = $request->ip();
			} else {
				$QuotMaster = new Wltrn_Quotation();
				$QuotMaster->entryby = '1';
				$QuotMaster->entryip = $request->ip();

				$QuotMaster->quotgroup_id = Wltrn_Quotation::max('quotgroup_id') + 1;
				$QuotMaster->yy = substr(date('Y'), -2);
				$QuotMaster->mm = date('m');
				$QuotMaster->quotno = Wltrn_Quotation::max('quotno') + 1;
				$QuotMaster->quot_no_str = '1.1';
				$QuotMaster->quot_date = date('Y-m-d');
			}

			$QuotMaster->quottype_id = $request->quottype_id;
			$CustomerMaster = Wlmst_Client::find($request->customer_id);
			$QuotMaster->customer_id = $request->customer_id;
			$QuotMaster->customer_name = $CustomerMaster->name;
			$QuotMaster->customer_contact_no = $CustomerMaster->mobile;
			$QuotMaster->architech_id = $request->architech_id;
			$QuotMaster->electrician_id = $request->electrician_id;
			$QuotMaster->salesexecutive_id = $request->salesexecutive_id;
			$QuotMaster->channelpartner_id = $request->channelpartner_id;
			$QuotMaster->site_name = $request->site_name;
			$QuotMaster->siteaddress = $request->siteaddress;
			$QuotMaster->site_country_id = $request->site_country_id;
			$QuotMaster->site_state_id = $request->site_state_id;
			$QuotMaster->site_city_id = $request->site_city_id;
			$QuotMaster->additional_remark = $request->additional_remark;

			$QuotMaster->quotationsource = $request->quotationsource;

			$QuotMaster->save();
			if ($QuotMaster) {

				if ($request->quot_id != 0) {
					$res_status = 1;
					$res_status_code = 200;
					$res_message = "Successfully Saved Quotation Basic Detail";
					$res_data = $QuotMaster;

					$DebugLog = new DebugLog();
					$DebugLog->user_id = 1;
					$DebugLog->name = "quot-quot-master-basicdetail-edit";
					$DebugLog->description = "Quotation master Basic Detail #" . $QuotMaster->id . "(" . $QuotMaster->id . ")" . " has been updated";
					$DebugLog->save();
				} else {
					$res_status = 1;
					$res_status_code = 200;
					$res_message = "Successfully Added Quotation Basic Detail";
					$res_data = $QuotMaster;

					$DebugLog = new DebugLog();
					$DebugLog->user_id = 1;
					$DebugLog->name = "quot-quot-master-basicdetail-add";
					$DebugLog->description = "Quotation master Basic Detail #" . $QuotMaster->id . "(" . $QuotMaster->id . ") has been added";
					$DebugLog->save();
				}
			}
		}

		$response['status'] = $res_status;
		$response['status_code'] = $res_status_code;
		$response['msg'] = $res_message;
		$response['data'] = $res_data;

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function PostQuotRoomNBoardSave(Request $request) {
		$res_status = "";
		$res_status_code = "";
		$res_message = "";
		$res_data = array();
		$response = array();
		$request_data = $request->input();
		$validator = Validator::make($request->input(), [
			'room' => ['required'],
			'item' => ['required'],
		]);

		if ($validator->fails()) {
			$res_status = 0;
			$res_message = "Please Check Perameater And Value";
			$res_status_code = 400;
			$res_data = $validator->errors();
		} else {
			/* ROOM ARRAY KEY VALUE SEY HERE */
			$quot_itemdetail_id = $request['room'][0]['id'];
			$quot_id = $request['room'][0]['quot_id'];
			$quotgroup_id = $request['room'][0]['quotgroup_id'];
			$room_no = $request['room'][0]['room_no'];
			$room_adon = $request['room'][0]['room_adon'];
			$room_name = $request['room'][0]['room_name'];
			$board_no = $request['room'][0]['board_no'];
			$board_name = $request['room'][0]['board_name'];
			$board_size = $request['room'][0]['board_size'];
			$board_item_id = $request['room'][0]['board_item_id'];
			$board_item_price_id = $request['room'][0]['board_item_price_id'];
			$itemdescription = $request['room'][0]['itemdescription'];
			$default_range = $request['room'][0]['default_range'];
			$room_range = $request['room'][0]['room_range'];
			$board_range = $request['room'][0]['board_range'];
			$board_image = $request['room'][0]['board_image'];

			if ($board_image != '') {
				$file = base64_decode($board_image);
				$folderName = '/quotation_board/image/';
				$safeName = uniqid() . '.' . 'png';
				$destinationPath = public_path() . $folderName;
				file_put_contents($destinationPath . $safeName, $file);
				$Image_Path = $folderName . $safeName;
			} else {
				$Image_Path = '';
			}
			/* ------------------- END ------------------- */

			if ($quot_itemdetail_id != 0) {

				$QuotMaster = Wltrn_Quotation::find($quot_id);
				$QuotMaster->default_range = $default_range;
				$QuotMaster->save();

				// DELETE OLD BOARD START
				$board_item_columns = array(
					'wltrn_quot_itemdetails.id',
					'wltrn_quot_itemdetails.board_image',
				);
				$board_query = Wltrn_QuotItemdetail::query();
				$board_query->select($board_item_columns);
				$board_query->where('wltrn_quot_itemdetails.quot_id', $quot_id);
				$board_query->where('wltrn_quot_itemdetails.quotgroup_id', $quotgroup_id);
				$board_query->where('wltrn_quot_itemdetails.room_no', $room_no);
				$board_query->where('wltrn_quot_itemdetails.board_no', $board_no);
				$board_query->groupBy(['wltrn_quot_itemdetails.board_no']);
				foreach ($board_query->get() as $key => $board_value) {
					unlink(public_path($board_value->board_image));
				}
				$DeleteOldEntry = Wltrn_QuotItemdetail::where([
					['wltrn_quot_itemdetails.quot_id', $quot_id],
					['wltrn_quot_itemdetails.quotgroup_id', $quotgroup_id],
					['wltrn_quot_itemdetails.room_no', $room_no],
					['wltrn_quot_itemdetails.board_no', $board_no],
				])->delete();
				// DELETE OLD BOARD END

				foreach ($request_data['item'] as $value) {
					$QuotationMaster = Wltrn_Quotation::find($quot_id);
					$ItemMaster = WlmstItem::find($value['itemid']);
					$GrossAmount = round(floatval($value['mrp']) * floatval($value['qty']));
					if ($QuotationMaster->site_state_id == '9' /*IS GUJARAT*/) {
						/* CGST CALCULATION */
						$CGST_Per = $ItemMaster->cgst_per;
						$CGST_Amount = floatval($GrossAmount) * floatval($ItemMaster->cgst_per) / 100;
						/* SGST CALCULATION */
						$SGST_Per = $ItemMaster->sgst_per;
						$SGST_Amount = floatval($GrossAmount) * floatval($ItemMaster->sgst_per) / 100;
						/* IGST CALCULATION */
						$IGST_Per = '0.00';
						$IGST_Amount = '0.00';
						/* NetAmount AMOUNT CALCULATION */
						$NetAmount = round(floatval($GrossAmount) + (floatval($GrossAmount) * floatval($ItemMaster->cgst_per) / 100) + (floatval($GrossAmount) * floatval($ItemMaster->sgst_per) / 100));
					} else {
						/* CGST CALCULATION */
						$CGST_Per = "0";
						$CGST_Amount = "0.00";
						/* SGST CALCULATION */
						$SGST_Per = "0";
						$SGST_Amount = "0.00";
						/* IGST CALCULATION */
						$IGST_Per = $ItemMaster->igst_per;
						$IGST_Amount = floatval($GrossAmount) * floatval($ItemMaster->igst_per) / 100;
						/* NetAmount AMOUNT CALCULATION */
						$NetAmount = round(floatval($GrossAmount) + (floatval($GrossAmount) * floatval($ItemMaster->igst_per) / 100));
					}

					$qry_add_quot_item = new Wltrn_QuotItemdetail();
					$qry_add_quot_item->quot_id = $quot_id;
					$qry_add_quot_item->quotgroup_id = $quotgroup_id;
					$qry_add_quot_item->room_no = $room_no;
					$qry_add_quot_item->room_name = $room_name;
					$qry_add_quot_item->board_no = $board_no;
					$qry_add_quot_item->board_name = $board_name;
					$qry_add_quot_item->board_size = $board_size;
					$qry_add_quot_item->board_item_id = $board_item_id;
					$qry_add_quot_item->board_item_price_id = $board_item_price_id;
					$qry_add_quot_item->board_image = $Image_Path;
					$qry_add_quot_item->itemdescription = $itemdescription;
					$qry_add_quot_item->item_id = $value['itemid'];
					$qry_add_quot_item->item_price_id = $value['priceid'];
					$qry_add_quot_item->company_id = $value['company_id'];
					$qry_add_quot_item->itemgroup_id = $value['itemgroup_id'];
					$qry_add_quot_item->itemsubgroup_id = $value['itemsubgroup_id'];
					$qry_add_quot_item->itemcategory_id = $value['itemcategory_id'];
					$qry_add_quot_item->itemcode = $value['code'];
					$qry_add_quot_item->qty = $value['qty'];
					$qry_add_quot_item->rate = $value['mrp'];
					$qry_add_quot_item->sequence_no = $value['sequence_no'];
					$qry_add_quot_item->grossamount = $GrossAmount;
					$qry_add_quot_item->taxableamount = $GrossAmount;
					$qry_add_quot_item->igst_per = $IGST_Per;
					$qry_add_quot_item->igst_amount = $IGST_Amount;
					$qry_add_quot_item->cgst_per = $CGST_Per;
					$qry_add_quot_item->cgst_amount = $CGST_Amount;
					$qry_add_quot_item->sgst_per = $SGST_Per;
					$qry_add_quot_item->sgst_amount = $SGST_Amount;
					$qry_add_quot_item->net_amount = $NetAmount;
					$qry_add_quot_item->item_type = $value['item_type'];
					$qry_add_quot_item->room_range = $room_range;
					$qry_add_quot_item->board_range = $board_range;
					$qry_add_quot_item->created_at = date("Y-m-d h:i:s");
					$qry_add_quot_item->entryby = '1';
					$qry_add_quot_item->entryip = $request->ip();
					$qry_add_quot_item->save();
				}

				$res_status = 1;
				$res_status_code = 200;
				$res_message = "Successfully Saved Quotation Item Detail";
				$res_data = $this->ShowRoomNBoardList($quot_id, $quotgroup_id, 0, 1);

				$DebugLog = new DebugLog();
				$DebugLog->user_id = 1;
				$DebugLog->name = "quot-quot-master-basicdetail-edit";
				$DebugLog->description = "Quotation Item Detail has been Updated
					(#Quote ID = " . $quot_id . ")
					(#Quote Group ID = " . $quotgroup_id . ")
					(#Room No = " . $room_no . ")
					(#Board No = " . $board_no . ")";
				$DebugLog->save();
			} else {
				$QuotMaster = Wltrn_Quotation::find($quot_id);
				$QuotMaster->default_range = $default_range;
				$QuotMaster->save();

				if ($room_adon == '0') {
					$BoardNo = Wltrn_QuotItemdetail::where([
						['quot_id', $quot_id],
						['quotgroup_id', $quotgroup_id],
						['room_no', $room_no],
					])->max('board_no') + 1;
				} else {
					$BoardNo = 0;
				}

				foreach ($request_data['item'] as $value) {
					$QuotationMaster = Wltrn_Quotation::find($quot_id);
					$ItemMaster = WlmstItem::find($value['itemid']);
					$GrossAmount = round(floatval($value['mrp']) * floatval($value['qty']));
					if ($QuotationMaster->site_state_id == '9' /*IS GUJARAT*/) {
						/* CGST CALCULATION */
						$CGST_Per = $ItemMaster->cgst_per;
						$CGST_Amount = floatval($GrossAmount) * floatval($ItemMaster->cgst_per) / 100;
						/* SGST CALCULATION */
						$SGST_Per = $ItemMaster->sgst_per;
						$SGST_Amount = floatval($GrossAmount) * floatval($ItemMaster->sgst_per) / 100;
						/* IGST CALCULATION */
						$IGST_Per = '0.00';
						$IGST_Amount = '0.00';
						/* NetAmount AMOUNT CALCULATION */
						$NetAmount = round(floatval($GrossAmount) + (floatval($GrossAmount) * floatval($ItemMaster->cgst_per) / 100) + (floatval($GrossAmount) * floatval($ItemMaster->sgst_per) / 100));
					} else {
						/* CGST CALCULATION */
						$CGST_Per = "0";
						$CGST_Amount = "0.00";
						/* SGST CALCULATION */
						$SGST_Per = "0";
						$SGST_Amount = "0.00";
						/* IGST CALCULATION */
						$IGST_Per = $ItemMaster->igst_per;
						$IGST_Amount = floatval($GrossAmount) * floatval($ItemMaster->igst_per) / 100;
						/* NetAmount AMOUNT CALCULATION */
						$NetAmount = round(floatval($GrossAmount) + (floatval($GrossAmount) * floatval($ItemMaster->igst_per) / 100));
					}

					$qry_add_quot_item = new Wltrn_QuotItemdetail();
					$qry_add_quot_item->quot_id = $quot_id;
					$qry_add_quot_item->quotgroup_id = $quotgroup_id;
					$qry_add_quot_item->room_no = $room_no;
					$qry_add_quot_item->room_name = $room_name;
					$qry_add_quot_item->board_no = $BoardNo;
					$qry_add_quot_item->board_name = $board_name;
					$qry_add_quot_item->board_size = $board_size;
					$qry_add_quot_item->board_item_id = $board_item_id;
					$qry_add_quot_item->board_item_price_id = $board_item_price_id;
					$qry_add_quot_item->board_image = $Image_Path;
					$qry_add_quot_item->itemdescription = $itemdescription;
					$qry_add_quot_item->item_id = $value['itemid'];
					$qry_add_quot_item->item_price_id = $value['priceid'];
					$qry_add_quot_item->company_id = $value['company_id'];
					$qry_add_quot_item->itemgroup_id = $value['itemgroup_id'];
					$qry_add_quot_item->itemsubgroup_id = $value['itemsubgroup_id'];
					$qry_add_quot_item->itemgroup_id = $value['itemgroup_id'];
					$qry_add_quot_item->itemcode = $value['code'];
					$qry_add_quot_item->qty = $value['qty'];
					$qry_add_quot_item->rate = $value['mrp'];
					$qry_add_quot_item->grossamount = $GrossAmount;
					$qry_add_quot_item->taxableamount = $value['mrp'];
					$qry_add_quot_item->igst_per = $IGST_Per;
					$qry_add_quot_item->igst_amount = $IGST_Amount;
					$qry_add_quot_item->cgst_per = $CGST_Per;
					$qry_add_quot_item->cgst_amount = $CGST_Amount;
					$qry_add_quot_item->sgst_per = $SGST_Per;
					$qry_add_quot_item->sgst_amount = $SGST_Amount;
					$qry_add_quot_item->net_amount = $NetAmount;
					$qry_add_quot_item->item_type = $value['item_type'];
					$qry_add_quot_item->room_range = $room_range;
					$qry_add_quot_item->board_range = $board_range;
					$qry_add_quot_item->created_at = date("Y-m-d h:i:s");
					$qry_add_quot_item->entryby = '1';
					$qry_add_quot_item->entryip = $request->ip();
					$qry_add_quot_item->save();
				}

				$res_status = 1;
				$res_status_code = 200;
				$res_message = "Successfully Added Quotation Item Detail";
				$res_data = $this->ShowRoomNBoardList($quot_id, $quotgroup_id, 0, 1);

				$DebugLog = new DebugLog();
				$DebugLog->user_id = 1;
				$DebugLog->name = "quot-quot-itemdetail-add";
				$DebugLog->description = "Quotation Item Detail has been added
					(#Quote ID = " . $quot_id . ")
					(#Quote Group ID = " . $quotgroup_id . ")
					(#Room No = " . $room_no . ")
					(#Board No = " . $BoardNo . ")";
				$DebugLog->save();
			}
		}

		$response['status'] = $res_status;
		$response['status_code'] = $res_status_code;
		$response['msg'] = $res_message;
		$response['data'] = $res_data;

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function PostQuotRoomList(Request $request) {
		$searchColumns = array(
			0 => 'wltrn_quot_itemdetails.room_name',
		);

		$room_columns = array(
			0 => 'wltrn_quot_itemdetails.room_no',
			1 => 'wltrn_quot_itemdetails.room_name',
			2 => 'wltrn_quot_itemdetails.room_range',
			3 => 'wltrn_quot_itemdetails.quot_id',
			4 => 'wltrn_quot_itemdetails.quotgroup_id',
			5 => 'wltrn_quot_itemdetails.srno',
			6 => 'wltrn_quot_itemdetails.isactiveroom',
		);

		$response = array();
		$status = 0;
		$status_code = http_response_code();
		$msg = "api";
		$data = null;

		try {
			$response = array();
			$status = 1;
			$msg = "Quotation Room List Success";

			$room_query = Wltrn_QuotItemdetail::query();
			$room_query->select($room_columns);
			$room_query->where([
				['wltrn_quot_itemdetails.quot_id', $request->quot_id],
				['wltrn_quot_itemdetails.quotgroup_id', $request->quotgroup_id],
			]);
			$room_query->groupBy(['wltrn_quot_itemdetails.room_no']);

			if (isset($request->q)) {
				$search_value = $request->q;
				$room_query->where(function ($query) use ($search_value, $searchColumns) {
					for ($i = 0; $i < count($searchColumns); $i++) {
						if ($i == 0) {
							$query->where($searchColumns[$i], 'like', "%" . $search_value . "%");
						} else {
							$query->orWhere($searchColumns[$i], 'like', "%" . $search_value . "%");
						}
					}
				});
			}

			$roomlist = $room_query->get();
			$quot_array = array();
			foreach ($roomlist as $key => $value) {
				$quot_f_array = array();

				if ($roomlist[$key]['room_range'] != "" || $roomlist[$key]['room_range'] != null) {
					$Range_Subgroup = explode(',', $roomlist[$key]['room_range']);
					// $Range_Subgroup = $quotlist[$key]['default_range'];
					$range_group = "";
					$range_company = "";
					for ($i = 0; $i < count($Range_Subgroup); $i++) {
						$range_group .= WlmstItemSubgroup::find($Range_Subgroup[$i])->itemgroup_id . ",";
						$range_company .= WlmstItemSubgroup::find($Range_Subgroup[$i])->company_id . ",";
					}
					$range_group_f = substr($range_group, 0, -1);
					// $range_company_f = substr($range_company,0,-1);
					$range_company_f = explode(",", $range_company)[0];
				} else {
					$range_group_f = "";
					$range_company_f = "";
				}

				$quot_f_array = $value;
				$quot_f_array['range_group'] = $range_group_f;
				$quot_f_array['range_company'] = $range_company_f;
				array_push($quot_array, $quot_f_array);
			}

			// $data = $query->paginate(20);
			$data = $quot_array;
		} catch (QueryException $ex) {
			$response = array();
			$status = 0;
			$msg = "Please Contact To Admin";
			$data = $ex;
		}

		$response['status'] = $status;
		$response['status_code'] = $status_code;
		$response['msg'] = $msg;
		$response['data'] = $data;

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function PostQuotQuartzColour(Request $request) {
		$searchColumns = array(
			0 => 'wlmst_item_subgroups.itemsubgroupname',
		);

		$columns = array(
			0 => 'wlmst_item_subgroups.id AS itemsubgroup_id',
			1 => 'wlmst_item_subgroups.itemsubgroupname',
			2 => 'wlmst_item_subgroups.company_id',
			3 => 'wlmst_companies.companyname',
			4 => 'wlmst_item_subgroups.itemgroup_id',
			5 => 'wlmst_item_groups.itemgroupname',
			6 => 'wlmst_item_subgroups.shortname',
			7 => 'wlmst_item_subgroups.remark',
		);

		$response = array();
		$status = 0;
		$status_code = http_response_code();
		$msg = "api";
		$data = null;

		try {
			$response = array();
			$status = 1;
			$msg = "Quotation Quartz Colours List Success";

			$quartz_query = WlmstItemSubgroup::query();
			$quartz_query->select($columns);
			$quartz_query->leftJoin('wlmst_companies', 'wlmst_companies.id', '=', 'wlmst_item_subgroups.company_id');
			$quartz_query->leftJoin('wlmst_item_groups', 'wlmst_item_groups.id', '=', 'wlmst_item_subgroups.itemgroup_id');
			$quartz_query->where('wlmst_item_subgroups.itemsubgroupname', 'like', "%Quartz%");
			$quartz_query->where('wlmst_item_subgroups.isactive', '1');
			$quartz_query->where('wlmst_item_subgroups.itemgroup_id', '2');

			if (isset($request->q)) {
				$search_value = $request->q;
				$quartz_query->where(function ($query) use ($search_value, $searchColumns) {
					for ($i = 0; $i < count($searchColumns); $i++) {
						if ($i == 0) {
							$query->where($searchColumns[$i], 'like', "%" . $search_value . "%");
						} else {
							$query->orWhere($searchColumns[$i], 'like', "%" . $search_value . "%");
						}
					}
				});
			}

			$data = $quartz_query->get();
		} catch (QueryException $ex) {
			$response = array();
			$status = 0;
			$msg = "Please Contact To Admin";
			$data = $ex;
		}

		$response['status'] = $status;
		$response['status_code'] = $status_code;
		$response['msg'] = $msg;
		$response['data'] = $data;

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function PostQuotRoomWiseBoardList(Request $request) {
		$searchColumns = array(
			0 => 'wltrn_quot_itemdetails.room_name',
			1 => 'wltrn_quot_itemdetails.board_no',
		);

		$columns = array(
			0 => 'wltrn_quot_itemdetails.id',
			1 => 'wltrn_quot_itemdetails.board_no',
			2 => 'wltrn_quot_itemdetails.board_name',
			3 => 'wltrn_quot_itemdetails.isactiveboard',
			5 => 'wltrn_quot_itemdetails.board_range',
			6 => 'wlmst_items.itemname',
			7 => DB::raw('CONCAT("http://103.218.110.153:623/whitelion_new/public","",wltrn_quot_itemdetails.board_image) as image'),
			8 => 'wltrn_quot_itemdetails.quot_id',
			9 => 'wltrn_quot_itemdetails.quotgroup_id',
			10 => 'wltrn_quot_itemdetails.srno',
			11 => 'wltrn_quot_itemdetails.isactiveboard',
			12 => 'wltrn_quot_itemdetails.room_no',
		);

		$response = array();
		$status = 0;
		$status_code = http_response_code();
		$msg = "api";
		$data = null;

		try {
			$response = array();
			$status = 1;
			$msg = "Quotation Room List Success";

			$board_query = Wltrn_QuotItemdetail::query();
			$board_query->select($columns);
			$board_query->leftJoin('wlmst_items', 'wltrn_quot_itemdetails.board_item_id', '=', 'wlmst_items.id');
			$board_query->where([
				['wltrn_quot_itemdetails.quot_id', $request->quot_id],
				['wltrn_quot_itemdetails.quotgroup_id', $request->quotgroup_id],
				['wltrn_quot_itemdetails.room_no', $request->room_id],
			]);
			$board_query->where('wltrn_quot_itemdetails.board_no', '!=', 0);
			$board_query->groupBy(['wltrn_quot_itemdetails.board_no']);

			if (isset($request->q)) {
				$search_value = $request->q;
				$board_query->where(function ($query) use ($search_value, $searchColumns) {
					for ($i = 0; $i < count($searchColumns); $i++) {
						if ($i == 0) {
							$query->where($searchColumns[$i], 'like', "%" . $search_value . "%");
						} else {
							$query->orWhere($searchColumns[$i], 'like', "%" . $search_value . "%");
						}
					}
				});
			}

			// $data = $board_query->get();
			$boardlist = $board_query->get();
			$quot_array = array();
			foreach ($boardlist as $key => $value) {
				$quot_f_array = array();

				if ($boardlist[$key]['board_range'] != "" || $boardlist[$key]['board_range'] != null) {
					$Range_Subgroup = explode(',', $boardlist[$key]['board_range']);
					$range_group = "";
					$range_company = "";
					for ($i = 0; $i < count($Range_Subgroup); $i++) {
						$range_group .= WlmstItemSubgroup::find($Range_Subgroup[$i])->itemgroup_id . ",";
						$range_company .= WlmstItemSubgroup::find($Range_Subgroup[$i])->company_id . ",";
					}
					$range_group_f = substr($range_group, 0, -1);
					// $range_company_f = substr($range_company,0,-1);
					$range_company_f = explode(",", $range_company)[0];
				} else {
					$range_group_f = " ";
					$range_company_f = " ";
				}

				$quot_f_array = $value;
				$quot_f_array['range_group'] = $range_group_f;
				$quot_f_array['range_company'] = $range_company_f;
				array_push($quot_array, $quot_f_array);
			}
			$data = $quot_array;
		} catch (QueryException $ex) {
			$response = array();
			$status = 0;
			$msg = "Please Contact To Admin";
			$data = $ex;
		}

		$response['status'] = $status;
		$response['status_code'] = $status_code;
		$response['msg'] = $msg;
		$response['data'] = $data;

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function PostSentQuotation(Request $request) {

		$res_status = "";
		$res_status_code = "";
		$res_message = "";
		$res_data = "";
		$response = array();

		$ClientMaster = Wltrn_Quotation::find($request->quotgroup_id);
		$ClientMaster->updateby = '1';
		$ClientMaster->updateip = $request->ip();
		$ClientMaster->status = '1';
		$ClientMaster->save();

		if ($ClientMaster) {
			$res_status = 1;
			$res_status_code = 200;
			$res_message = "Successfully Sent Quotation";

			$DebugLog = new DebugLog();
			$DebugLog->user_id = 1;
			$DebugLog->name = "quotation-sent";
			$DebugLog->description = "Quotation #" . $ClientMaster->id . "(" . $ClientMaster->quotgroup_id . ")" . " has been sent Successfully";
			$DebugLog->save();
		}

		$response['status'] = $res_status;
		$response['status_code'] = $res_status_code;
		$response['msg'] = $res_message;
		$response['data'] = $res_data;

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function PostCopyRoomNBoard(Request $request) {

		$res_status = "";
		$res_status_code = http_response_code();
		$res_message = "";
		$res_data = "";
		$response = array();

		$validator = Validator::make($request->all(), [
			'quot_id' => ['required'],
			'quotgroup_id' => ['required'],
			'room_no' => ['required'],
		]);

		if ($validator->fails()) {
			$res_status = 0;
			$res_message = "Please Check Perameater And Value";
			$res_data = $validator->errors();
		} else {

			try {

				if ($request->board_no != 0) {
					$BoardNo = Wltrn_QuotItemdetail::where([
						['quot_id', $request->quot_id],
						['quotgroup_id', $request->quotgroup_id],
						['room_no', $request->room_no],
					])->max('board_no') + 1;

					$board_detail_query = Wltrn_QuotItemdetail::where([
						['wltrn_quot_itemdetails.quot_id', $request->quot_id],
						['wltrn_quot_itemdetails.quotgroup_id', $request->quotgroup_id],
						['wltrn_quot_itemdetails.room_no', $request->room_no],
						['wltrn_quot_itemdetails.board_no', $request->board_no],
					])->get();

					foreach ($board_detail_query as $key => $value) {
						$board_replicate_unit = Wltrn_QuotItemdetail::find($value->id);
						$query_board_copy = $board_replicate_unit->replicate();
						$query_board_copy->board_no = $BoardNo;
						$query_board_copy->board_name = $request->name;
						$query_board_copy->copyfromboard_no = $request->board_no;
						$query_board_copy->save();
					}
				} else {
					$RoomNo = Wltrn_QuotItemdetail::where([
						['quot_id', $request->quot_id],
						['quotgroup_id', $request->quotgroup_id],
					])->max('room_no') + 1;

					$board_detail_query = Wltrn_QuotItemdetail::where([
						['wltrn_quot_itemdetails.quot_id', $request->quot_id],
						['wltrn_quot_itemdetails.quotgroup_id', $request->quotgroup_id],
						['wltrn_quot_itemdetails.room_no', $request->room_no],
					])->get();

					foreach ($board_detail_query as $key => $value) {
						$board_replicate_unit = Wltrn_QuotItemdetail::find($value->id);
						$query_board_copy = $board_replicate_unit->replicate();
						$query_board_copy->room_no = $RoomNo;
						$query_board_copy->room_name = $request->name;
						$query_board_copy->copyfromroom_no = $request->room_no;
						$query_board_copy->save();
					}
				}
				$data = $this->ShowRoomNBoardList($request->quot_id, $request->quotgroup_id, 0, $request->room_id);

				$res_status = 1;
				$res_message = "board copy successfully";
				$res_data = $data;
			} catch (QueryException $ex) {
				$response = array();
				$res_status = 0;
				$res_message = "Please Contact To Admin";
				$res_data = $ex;
			}
		}

		// if ($query_copy) {
		// 	$res_status = 1;
		// 	$res_status_code = 200;
		// 	$res_message = "Successfully Copy Row";
		// 	$res_data = $query_copy;

		// 	// $DebugLog = new DebugLog();
		// 	// $DebugLog->user_id = 1;
		// 	// $DebugLog->name = "quotation-sent";
		// 	// $DebugLog->description = "Quotation #" . $query_copy->id . "(" . $query->query_copy . ")" . " has been sent Successfully";
		// 	// $DebugLog->save();
		// }

		$response['status'] = $res_status;
		$response['status_code'] = $res_status_code;
		$response['msg'] = $res_message;
		$response['data'] = $res_data;

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function PostCopyFullQuotation12(Request $request) {

		$res_status = "";
		$res_status_code = http_response_code();
		$res_message = "";
		$res_data = "";
		$res_value1 = "";
		$res_value2 = "";
		$response = array();

		$validator = Validator::make($request->all(), [
			'quot_id' => ['required'],
			'quotgroup_id' => ['required'],
		]);

		if ($validator->fails()) {
			$res_status = 0;
			$res_message = "Please Check Perameater And Value";
			$res_data = $validator->errors();
		} else {

			try {

				$quot_id = $request->quot_id;
				$quotgroup_id = $request->quotgroup_id;
				$entryip = $request->ip();
				$entryby = '1';
				// $quot_detail = new Wltrn_QuotItemdetail();
				// $new_quot = $quot_detail->PostCopyFullQuotationVersion($quot_id,$quotgroup_id,$entryip,$entryby);
				$new_quot = $this->PostCopyFullQuotationVersion($quot_id, $quotgroup_id, $entryip, $entryby);
				// $new_quot = $this->quot_detail->PostCopyFullQuotationVersion($quot_id, $quotgroup_id, $entryip, $entryby);

				$json = json_decode($new_quot, true);

				$res_status = 1;
				$res_message = "Quotation copy successfully";
				$res_data = $json;
				// $res_value1 = $json[0]['status'];
				// $res_value2 = $json->status;
			} catch (QueryException $ex) {
				$response = array();
				$res_status = 0;
				$res_message = "Please Contact To Admin";
				$res_data = $ex;
			}
		}

		// if ($query_copy) {
		// 	$res_status = 1;
		// 	$res_status_code = 200;
		// 	$res_message = "Successfully Copy Row";
		// 	$res_data = $query_copy;

		// 	// $DebugLog = new DebugLog();
		// 	// $DebugLog->user_id = 1;
		// 	// $DebugLog->name = "quotation-sent";
		// 	// $DebugLog->description = "Quotation #" . $query_copy->id . "(" . $query->query_copy . ")" . " has been sent Successfully";
		// 	// $DebugLog->save();
		// }

		$response['status'] = $res_status;
		$response['status_code'] = $res_status_code;
		$response['msg'] = $res_message;
		$response['data'] = $res_data;
		$response['res_value1'] = $res_value1;
		$response['res_value2'] = $res_value2;

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function PostChangeRoomNBoardRange(Request $request) {
		$res_status = "";
		$res_status_code = http_response_code();
		$res_message = "";
		$res_data = "";
		$response = array();

		$validator = Validator::make($request->all(), [
			'quot_id' => ['required'],
			'quotgroup_id' => ['required'],
			'range' => ['required'],
			'room_no' => ['required'],
			'board_no' => ['required'],
		]);

		if ($validator->fails()) {
			$res_status = 0;
			$res_message = "Please Check Perameater And Value";
			$res_data = $validator->errors();
		} else {

			try {

				$Range_Subgroup = explode(',', $request->range);
				foreach ($Range_Subgroup as $rage_key => $range_value) {
					$range_group = WlmstItemSubgroup::find($range_value)->itemgroup_id;
					$range_subgroup = $range_value;
					$range_company = WlmstItemSubgroup::find($range_value)->company_id;

					if ($request->board_no != 0) {
						/* CHNAGE BOARD RANGE */
						$board_detail_query = Wltrn_QuotItemdetail::where([
							['wltrn_quot_itemdetails.quot_id', $request->quot_id],
							['wltrn_quot_itemdetails.quotgroup_id', $request->quotgroup_id],
							['wltrn_quot_itemdetails.room_no', $request->room_no],
							['wltrn_quot_itemdetails.board_no', $request->board_no],
							['wltrn_quot_itemdetails.itemgroup_id', $range_group],
							['wltrn_quot_itemdetails.itemsubgroup_id', '<>', '0'],
						])->get();
					} else if ($request->board_no == 'FULL') {
						/* CHNAGE FULL QUOTATION RANGE */
						$board_detail_query = Wltrn_QuotItemdetail::where([
							['wltrn_quot_itemdetails.quot_id', $request->quot_id],
							['wltrn_quot_itemdetails.quotgroup_id', $request->quotgroup_id],
							['wltrn_quot_itemdetails.itemgroup_id', $range_group],
							['wltrn_quot_itemdetails.itemsubgroup_id', '<>', '0'],
						])->get();
						$query_default_range_update = Wltrn_Quotation::find($request->quot_id);
						$query_default_range_update->default_range = $request->range;
						$query_default_range_update->updated_at = date("Y-m-d h:i:s");
						$query_default_range_update->updateby = '1';
						$query_default_range_update->updateip = $this->getIp();
						$query_default_range_update->save();
					} else if ($request->board_no == 0) {
						/* CHNAGE ROOM RANGE */
						$board_detail_query = Wltrn_QuotItemdetail::where([
							['wltrn_quot_itemdetails.quot_id', $request->quot_id],
							['wltrn_quot_itemdetails.quotgroup_id', $request->quotgroup_id],
							['wltrn_quot_itemdetails.room_no', $request->room_no],
							['wltrn_quot_itemdetails.itemgroup_id', $range_group],
							['wltrn_quot_itemdetails.itemsubgroup_id', '<>', '0'],
						])->get();
					}

					foreach ($board_detail_query as $key => $value) {

						$item_price_detail = Wlmst_ItemPrice::where([
							['wlmst_item_prices.company_id', $range_company],
							['wlmst_item_prices.itemgroup_id', $range_group],
							['wlmst_item_prices.itemsubgroup_id', $range_subgroup],
							['wlmst_item_prices.item_id', $value->item_id],
						])->first();

						if ($item_price_detail) {

							$QuotationMaster = Wltrn_Quotation::find($request->quot_id);
							$ItemMaster = WlmstItem::find($item_price_detail->item_id);
							$GrossAmount = round(floatval($item_price_detail->mrp) * floatval($value->qty));
							if ($QuotationMaster->site_state_id == '9' /*IS GUJARAT*/) {
								/* CGST CALCULATION */
								$CGST_Per = $ItemMaster->cgst_per;
								$CGST_Amount = floatval($GrossAmount) * floatval($ItemMaster->cgst_per) / 100;
								/* SGST CALCULATION */
								$SGST_Per = $ItemMaster->sgst_per;
								$SGST_Amount = floatval($GrossAmount) * floatval($ItemMaster->sgst_per) / 100;
								/* IGST CALCULATION */
								$IGST_Per = '0.00';
								$IGST_Amount = '0.00';
								/* NetAmount AMOUNT CALCULATION */
								$NeteAmount = round(floatval($GrossAmount) + (floatval($GrossAmount) * floatval($ItemMaster->cgst_per) / 100) + (floatval($GrossAmount) * floatval($ItemMaster->sgst_per) / 100));
							} else {
								/* CGST CALCULATION */
								$CGST_Per = "0";
								$CGST_Amount = "0.00";
								/* SGST CALCULATION */
								$SGST_Per = "0";
								$SGST_Amount = "0.00";
								/* IGST CALCULATION */
								$IGST_Per = $ItemMaster->igst_per;
								$IGST_Amount = floatval($GrossAmount) * floatval($ItemMaster->igst_per) / 100;
								/* NetAmount AMOUNT CALCULATION */
								$NeteAmount = round(floatval($GrossAmount) + (floatval($GrossAmount) * floatval($ItemMaster->igst_per) / 100));
							}

							$board_item_change = Wltrn_QuotItemdetail::find($value->id);
							$board_item_change->company_id = $item_price_detail->company_id;
							$board_item_change->itemgroup_id = $item_price_detail->itemgroup_id;
							$board_item_change->itemsubgroup_id = $item_price_detail->itemsubgroup_id;
							$board_item_change->itemcategory_id = $ItemMaster->itemcategory_id;
							$board_item_change->item_id = $item_price_detail->item_id;
							$board_item_change->itemcode = $item_price_detail->code;
							$board_item_change->rate = $item_price_detail->mrp;

							// $board_item_change->discper = $value->discper;
							// $board_item_change->discamount = $value->discamount;
							$board_item_change->grossamount = $GrossAmount;
							// $board_item_change->addamount = $value->addamount;
							// $board_item_change->lessamount = $value->lessamount;
							$board_item_change->taxableamount = $GrossAmount;

							$board_item_change->igst_per = $IGST_Per;
							$board_item_change->igst_amount = $IGST_Amount;

							$board_item_change->cgst_per = $CGST_Per;
							$board_item_change->cgst_amount = $CGST_Amount;

							$board_item_change->sgst_per = $SGST_Per;
							$board_item_change->sgst_amount = $SGST_Amount;

							$board_item_change->net_amount = $NeteAmount;

							$board_item_change->item_price_id = $item_price_detail->id;

							if ($request->board_no != 0) {
								/* CHNAGE BOARD RANGE */
								$board_item_change->board_range = $request->range;
							} else if ($request->board_no == 'FULL') {
								/* CHNAGE FULL QUOTATION RANGE */
								$board_item_change->board_range = $request->range;
								$board_item_change->room_range = $request->range;
							} else if ($request->board_no == 0) {
								/* CHNAGE ROOM RANGE */
								$board_item_change->board_range = $request->range;
								$board_item_change->room_range = $request->range;
							}
							if ($item_price_detail->itemgroup_id == '5') {
								$board_item_change->board_item_id = $item_price_detail->item_id;
								$board_item_change->board_item_price_id = $item_price_detail->id;
							}
							$board_item_change->updated_at = date("Y-m-d h:i:s");
							$board_item_change->updateby = '1';
							$board_item_change->updateip = $this->getIp();
							$board_item_change->save();
						} else {
							$board_item_change = Wltrn_QuotItemdetail::find($value->id);
							$board_item_change->company_id = $item_price_detail->company_id;
							$board_item_change->itemgroup_id = $item_price_detail->itemgroup_id;
							$board_item_change->itemsubgroup_id = $item_price_detail->itemsubgroup_id;
							$board_item_change->itemcategory_id = '0';
							$board_item_change->item_id = $item_price_detail->item_id;
							$board_item_change->itemcode = $item_price_detail->code;
							$board_item_change->rate = $item_price_detail->mrp;

							$board_item_change->grossamount = '0';
							$board_item_change->taxableamount = '0';
							$board_item_change->igst_per = '0';
							$board_item_change->igst_amount = '0';
							$board_item_change->cgst_per = '0';
							$board_item_change->cgst_amount = '0';
							$board_item_change->sgst_per = '0';
							$board_item_change->sgst_amount = '0';
							$board_item_change->net_amount = '0';
							$board_item_change->item_price_id = $item_price_detail->id;

							if ($request->board_no != 0) {
								/* CHNAGE BOARD RANGE */
								$board_item_change->board_range = $request->range;
							} else if ($request->board_no == 'FULL') {
								/* CHNAGE FULL QUOTATION RANGE */
								$board_item_change->board_range = $request->range;
								$board_item_change->room_range = $request->range;
							} else if ($request->board_no == 0) {
								/* CHNAGE ROOM RANGE */
								$board_item_change->board_range = $request->range;
								$board_item_change->room_range = $request->range;
							}
							$board_item_change->status = '2';
							$board_item_change->updated_at = date("Y-m-d h:i:s");
							$board_item_change->updateby = '1';
							$board_item_change->updateip = $this->getIp();
							$board_item_change->save();

							$error_query = new Wlmst_QuotationError();
							$error_query->quot_id = $request->quot_id;
							$error_query->quotgroup_id = $request->quotgroup_id;
							$error_query->quotitemdetail_id = $value->id;
							$error_query->srno = $value->srno;
							$error_query->floorno = $value->floor_no;
							$error_query->roomno = $value->room_no;
							$error_query->boardno = $value->board_no;

							$error_query->old_company_id = $value->company_id;
							$error_query->old_itemgroup_id = $value->itemgroup_id;
							$error_query->old_itemsubgroup_id = $value->itemsubgroup_id;
							$error_query->old_itemcategory_id = $value->itemcategory_id;
							$error_query->old_item_id = $value->item_id;
							$error_query->old_itemcode = $value->itemcode;
							$error_query->old_item_price_id = $value->item_price_id;

							$error_query->new_company_id = $range_company;
							$error_query->new_itemgroup_id = $range_group;
							$error_query->new_itemsubgroup_id = $range_subgroup;
							$error_query->new_itemcategory_id = '0';
							$error_query->new_item_id = '0';
							$error_query->new_itemcode = '0';
							$error_query->new_item_price_id = '0';
							$error_query->description = 'In This Range Some Product Mismatch';
							$error_query->status = '400';
							$error_query->created_at = date("Y-m-d h:i:s");
							$error_query->entryby = '1';
							$error_query->entryip = $this->getIp();
							$error_query->save();

							// $error_board_item = Wltrn_QuotItemdetail::find($value->id);
							// $error_board_item->delete();
						}
					}
				}

				$chk_error_columns = array(
					'wlmst_quotation_errors.old_company_id',
					'wlmst_quotation_errors.old_itemgroup_id',
					'wlmst_quotation_errors.old_itemsubgroup_id',
					'wlmst_quotation_errors.old_itemcategory_id',
					'wlmst_quotation_errors.old_item_id',
					'wlmst_quotation_errors.old_item_price_id',
					'wlmst_quotation_errors.new_company_id',
					'wlmst_quotation_errors.new_itemgroup_id',
					'wlmst_quotation_errors.new_itemsubgroup_id',
					'wlmst_quotation_errors.new_itemcategory_id',
					'wlmst_quotation_errors.description',
					'wlmst_quotation_errors.status',
				);

				$error_data_query = Wlmst_QuotationError::query();
				$error_data_query->select($chk_error_columns);
				// $error_data_query->leftJoin('country_list', 'country_list.id', '=', 'wltrn_quotation.site_country_id');
				$error_data_query->where([
					['wlmst_quotation_errors.quot_id', $request->quot_id],
					['wlmst_quotation_errors.quotgroup_id', $request->quotgroup_id],
					['wlmst_quotation_errors.roomno', $request->room_no],
					['wlmst_quotation_errors.status', '400'],
				]);
				$error_data = $error_data_query->get();
				if (count($error_data) >= 1) {
					$res_status = 1;
					$res_message = "In This Range Change Time Some Product Mismatch";
				} else {
					$res_status = 1;
					$res_message = "Range Change successfully";
				}

				$res_data = $error_data;
			} catch (QueryException $ex) {
				$response = array();
				$res_status = 0;
				$res_message = "Please Contact To Admin";
				$res_data = $ex;
			}
		}

		$response['status'] = $res_status;
		$response['status_code'] = $res_status_code;
		$response['msg'] = $res_message;
		$response['data'] = $res_data;

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function PostRoomNBoardRename(Request $request) {
		$res_status = "";
		$res_status_code = "";
		$res_message = "";

		$validator = Validator::make($request->input(), [
			'title' => ['required'],
			'quot_id' => ['required'],
			'quotgroup_id' => ['required'],
			'room_no' => ['required'],
		]);

		if ($validator->fails()) {
			$res_status = 0;
			$res_message = "please check perameater and value";
			$res_status_code = 400;
		} else {
			$Title = $request->title;
			$Name = $request->name;
			$quot_id = $request->quot_id;
			$quotgroup_id = $request->quotgroup_id;

			if ($Title == "ROOM") {

				if (isset($request->room_no)) {
					$room_no = $request->room_no;
					$QuotItemdetail = Wltrn_QuotItemdetail::where([
						['wltrn_quot_itemdetails.quot_id', $quot_id],
						['wltrn_quot_itemdetails.quotgroup_id', $quotgroup_id],
						['wltrn_quot_itemdetails.room_no', $room_no],
					]);
					$QuotItemdetail->update(['room_name' => $Name, 'updateby' => '1', 'updateip' => $request->ip()]);

					$DebugLog = new DebugLog();
					$DebugLog->user_id = 1;
					$DebugLog->name = "quotation-sent";
					$DebugLog->description = "Quotation Item Detail Room Name has been Updated
						(#Quote ID = " . $quot_id . ")
						(#Quote Group ID = " . $quotgroup_id . ")
						(#Room No = " . $room_no . ")";
					$DebugLog->save();

					$res_status = 1;
					$res_status_code = 200;
					$res_message = "successfully rename room name";
				} else {
					$res_status = 0;
					$res_message = "please check perameater and value";
					$res_status_code = 400;
				}
			} elseif ($Title == "BOARD") {

				if (isset($request->board_no)) {
					$board_no = $request->board_no;
					$QuotItemdetail = Wltrn_QuotItemdetail::where([
						['wltrn_quot_itemdetails.quot_id', $quot_id],
						['wltrn_quot_itemdetails.quotgroup_id', $quotgroup_id],
						['wltrn_quot_itemdetails.board_no', $board_no],
					]);
					$QuotItemdetail->update(['board_name' => $Name, 'updateby' => '1', 'updateip' => $request->ip()]);

					$DebugLog = new DebugLog();
					$DebugLog->user_id = 1;
					$DebugLog->name = "quotation-sent";
					$DebugLog->description = "Quotation Item Detail Board Name has been Updated
						(#Quote ID = " . $quot_id . ")
						(#Quote Group ID = " . $quotgroup_id . ")
						(#Board No = " . $board_no . ")";
					$DebugLog->save();

					$res_status = 1;
					$res_status_code = 200;
					$res_message = "successfully rename board name";
				} else {
					$res_status = 0;
					$res_message = "please check perameater and value";
					$res_status_code = 400;
				}
			} else {
				$res_status = 0;
				$res_message = "please check perameater and value";
				$res_status_code = 400;
			}
		}

		$response['status'] = $res_status;
		$response['status_code'] = $res_status_code;
		$response['msg'] = $res_message;

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function PostRoomNBoardStatus(Request $request) {
		$res_status = "";
		$res_status_code = "";
		$res_message = "";

		$validator = Validator::make($request->input(), [
			'title' => ['required'],
			'quot_id' => ['required'],
			'quotgroup_id' => ['required'],
			'room_no' => ['required'],
		]);

		if ($validator->fails()) {
			$res_status = 0;
			$res_message = "please check perameater and value";
			$res_status_code = 400;
		} else {
			$Title = $request->title;
			$Status = $request->status;
			$quot_id = $request->quot_id;
			$quotgroup_id = $request->quotgroup_id;

			if ($Title == "ROOM") {

				if (isset($request->room_no)) {
					$room_no = $request->room_no;
					$QuotItemdetail = Wltrn_QuotItemdetail::where([
						['wltrn_quot_itemdetails.quot_id', $quot_id],
						['wltrn_quot_itemdetails.quotgroup_id', $quotgroup_id],
						['wltrn_quot_itemdetails.room_no', $room_no],
					]);
					$QuotItemdetail->update(['isactiveroom' => $Status, 'updateby' => '1', 'updateip' => $request->ip()]);

					$DebugLog = new DebugLog();
					$DebugLog->user_id = 1;
					$DebugLog->name = "quotation-sent";
					$DebugLog->description = "Quotation Item Detail Room Status has been Updated
						(#Quote ID = " . $quot_id . ")
						(#Quote Group ID = " . $quotgroup_id . ")
						(#Room No = " . $room_no . ")";
					$DebugLog->save();

					$res_status = 1;
					$res_status_code = 200;
					$res_message = "successfully status change";
				} else {
					$res_status = 0;
					$res_message = "please check room number";
					$res_status_code = 400;
				}
			} elseif ($Title == "BOARD") {

				if (isset($request->board_no)) {
					$board_no = $request->board_no;
					$QuotItemdetail = Wltrn_QuotItemdetail::where([
						['wltrn_quot_itemdetails.quot_id', $quot_id],
						['wltrn_quot_itemdetails.quotgroup_id', $quotgroup_id],
						['wltrn_quot_itemdetails.board_no', $board_no],
					]);
					$QuotItemdetail->update(['isactiveboard' => $Status, 'updateby' => '1', 'updateip' => $request->ip()]);

					$DebugLog = new DebugLog();
					$DebugLog->user_id = 1;
					$DebugLog->name = "quotation-sent";
					$DebugLog->description = "Quotation Item Detail Board Status has been Updated
						(#Quote ID = " . $quot_id . ")
						(#Quote Group ID = " . $quotgroup_id . ")
						(#Board No = " . $board_no . ")";
					$DebugLog->save();

					$res_status = 1;
					$res_status_code = 200;
					$res_message = "successfully status change";
				} else {
					$res_status = 0;
					$res_message = "please check board number";
					$res_status_code = 400;
				}
			} else {
				$res_status = 0;
				$res_message = "please check perameater and value";
				$res_status_code = 400;
			}
		}

		$response['status'] = $res_status;
		$response['status_code'] = $res_status_code;
		$response['msg'] = $res_message;

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function getIp() {
		foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key) {
			if (array_key_exists($key, $_SERVER) === true) {
				foreach (explode(',', $_SERVER[$key]) as $ip) {
					$ip = trim($ip); // just to be safe
					if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
						return $ip;
					}
				}
			}
		}
		return request()->ip(); // it will return server ip when no client ip found
	}

	public function ShowRoomNBoardList($quot_id, $quotgroup_id, $quot_srno, $quot_room_id) {
		/* *********************************************************************************** ROOM LIST *********************************************************************************** */
		$room_columns = array(
			0 => 'wltrn_quot_itemdetails.room_no',
			1 => 'wltrn_quot_itemdetails.room_name',
			2 => 'wltrn_quot_itemdetails.room_range',
			3 => 'wltrn_quot_itemdetails.quot_id',
			4 => 'wltrn_quot_itemdetails.quotgroup_id',
			5 => 'wltrn_quot_itemdetails.srno',
			6 => 'wltrn_quot_itemdetails.isactiveroom',
		);
		$room_query = Wltrn_QuotItemdetail::query();
		$room_query->select($room_columns);
		$room_query->where([
			['wltrn_quot_itemdetails.quot_id', $quot_id],
			['wltrn_quot_itemdetails.quotgroup_id', $quotgroup_id],
		]);
		$room_query->groupBy(['wltrn_quot_itemdetails.room_no']);

		$roomlist = $room_query->get();
		$quot_room_array = array();
		foreach ($roomlist as $key => $value) {
			$quot_f_array = array();

			if ($roomlist[$key]['room_range'] != "" || $roomlist[$key]['room_range'] != null) {
				$Range_Subgroup = explode(',', $roomlist[$key]['room_range']);
				// $Range_Subgroup = $quotlist[$key]['default_range'];
				$range_group = "";
				$range_company = "";
				for ($i = 0; $i < count($Range_Subgroup); $i++) {
					$range_group .= WlmstItemSubgroup::find($Range_Subgroup[$i])->itemgroup_id . ",";
					$range_company .= WlmstItemSubgroup::find($Range_Subgroup[$i])->company_id . ",";
				}
				$range_group_f = substr($range_group, 0, -1);
				// $range_company_f = substr($range_company,0,-1);
				$range_company_f = explode(",", $range_company)[0];
			} else {
				$range_group_f = "";
				$range_company_f = "";
			}

			$quot_f_array = $value;
			$quot_f_array['range_group'] = $range_group_f;
			$quot_f_array['range_company'] = $range_company_f;
			array_push($quot_room_array, $quot_f_array);
		}
		/* *********************************************************************************** BOARD LIST *********************************************************************************** */
		$board_columns = array(
			0 => 'wltrn_quot_itemdetails.id',
			1 => 'wltrn_quot_itemdetails.board_no',
			2 => 'wltrn_quot_itemdetails.board_name',
			3 => 'wltrn_quot_itemdetails.isactiveboard',
			5 => 'wltrn_quot_itemdetails.board_range',
			6 => 'wlmst_items.itemname',
			7 => DB::raw('CONCAT("http://103.218.110.153:623/whitelion_new/public","",wltrn_quot_itemdetails.board_image) as image'),
			8 => 'wltrn_quot_itemdetails.quot_id',
			9 => 'wltrn_quot_itemdetails.quotgroup_id',
			10 => 'wltrn_quot_itemdetails.srno',
			11 => 'wltrn_quot_itemdetails.isactiveboard',
			12 => 'wltrn_quot_itemdetails.room_no',
		);
		$board_query = Wltrn_QuotItemdetail::query();
		$board_query->select($board_columns);
		$board_query->leftJoin('wlmst_items', 'wltrn_quot_itemdetails.board_item_id', '=', 'wlmst_items.id');
		$board_query->where([
			['wltrn_quot_itemdetails.quot_id', $quot_id],
			['wltrn_quot_itemdetails.quotgroup_id', $quotgroup_id],
			['wltrn_quot_itemdetails.room_no', $quot_room_id],
		]);
		$board_query->where('wltrn_quot_itemdetails.board_no', '!=', 0);
		$board_query->groupBy(['wltrn_quot_itemdetails.board_no']);

		// $data = $board_query->get();
		$boardlist = $board_query->get();
		$board_array = array();
		foreach ($boardlist as $key => $value) {
			$quot_f_array = array();

			if ($boardlist[$key]['board_range'] != "" || $boardlist[$key]['board_range'] != null) {
				$Range_Subgroup = explode(',', $boardlist[$key]['board_range']);
				$range_group = "";
				$range_company = "";
				for ($i = 0; $i < count($Range_Subgroup); $i++) {
					$range_group .= WlmstItemSubgroup::find($Range_Subgroup[$i])->itemgroup_id . ",";
					$range_company .= WlmstItemSubgroup::find($Range_Subgroup[$i])->company_id . ",";
				}
				$range_group_f = substr($range_group, 0, -1);
				// $range_company_f = substr($range_company,0,-1);
				$range_company_f = explode(",", $range_company)[0];
			} else {
				$range_group_f = "";
				$range_company_f = "";
			}

			$quot_f_array = $value;
			$quot_f_array['range_group'] = $range_group_f;
			$quot_f_array['range_company'] = $range_company_f;
			array_push($board_array, $quot_f_array);
		}
		$res_data = array('room' => $quot_room_array, 'board' => $board_array);
		return $res_data; // it will return room & board list
	}

	public function PostDownloadPrint(Request $request) {
		$pdf = Pdf::setOption(['dpi' => 120, 'defaultFont' => 'sans-serif']);
		$pdf->loadView('sample', [
			'title' => 'CodeAndDeploy.com Laravel Pdf Tutorial',
			'description' => 'This is an example Laravel pdf tutorial.',
			'footer' => 'by <a href="https://codeanddeploy.com">codeanddeploy.com</a>',
		]);
		$pdf->setPaper('a4', 'portrait');

		// $pdf->setPaper('A4', 'landscape');
		// $pdf->setPaper('Letter', 'landscape');
		// $pdf->set_paper("A4", "portrait");
		// $pdf->set_paper("Letter", "portrait");

		return $pdf->download('invoice.pdf');
		// return Pdf::loadFile(public_path().'/myfile.html')->save('/path-to/my_stored_file.pdf')->stream('download.pdf');
		$data = array();
		$data['title'] = "Quotation  Master ";
		return view('sample', compact('data'));
	}

	public function PostCopyFullQuotation(Request $request) {
		$res_status = "";
		$res_message = "";
		$response = array();

		try {
			$quot_id = $request->quot_id;
			$quotgroup_id = $request->quotgroup_id;
			$entryip = $request->ip();
			$entryby = '1';

			$quot_replicate_unit = Wltrn_Quotation::find($quot_id);
			$query_quot_copy = $quot_replicate_unit->replicate();
			$query_quot_copy->quot_no_str = number_format((floatval($quot_replicate_unit->quot_no_str) + floatval(0.1)), 2);
			$query_quot_copy->quot_date = date('Y-m-d');
			$query_quot_copy->created_at = date("Y-m-d h:i:s");
			$query_quot_copy->status = 0;
			$query_quot_copy->entryby = $entryby;
			$query_quot_copy->entryip = $entryip;
			$query_quot_copy->updateby = $entryby;
			$query_quot_copy->updateip = $entryip;
			$query_quot_copy->copyfromquotation_id = $quot_id;
			$query_quot_copy->save();

			$board_detail_query = Wltrn_QuotItemdetail::where([
				['wltrn_quot_itemdetails.quot_id', $quot_id],
				['wltrn_quot_itemdetails.quotgroup_id', $quotgroup_id],
			])->get();

			foreach ($board_detail_query as $key => $value) {
				$board_replicate_unit = Wltrn_QuotItemdetail::find($value->id);
				$query_board_copy = $board_replicate_unit->replicate();
				$query_board_copy->quot_id = $query_quot_copy->id;
				$query_board_copy->created_at = date("Y-m-d h:i:s");
				$query_board_copy->entryby = $entryby;
				$query_board_copy->entryip = $entryip;
				$query_board_copy->updateby = $entryby;
				$query_board_copy->updateip = $entryip;
				$query_board_copy->save();
			}

			$res_status = 1;
			$res_message = $query_quot_copy;
		} catch (QueryException $ex) {
			$response = array();
			$res_status = 0;
			$res_message = $ex;
		}

		$response['status'] = $res_status;
		$response['msg'] = $res_message;

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function SaveImage(Request $request) {
		$res_status = "";
		$res_message = "";
		$response = array();

		if ($request->board_image) {

			try {
				$file = base64_decode($request->board_image);
				$folderName = '/quotation_board/image/';
				$safeName = uniqid() . '.' . 'png';
				$destinationPath = public_path() . $folderName;
				file_put_contents(public_path() . '/quotation_board/image/' . $safeName, $file);

				$res_status = 1;
				$res_message = $safeName;
			} catch (Exception $e) {
				$res_status = 0;
				$res_message = $e->getMessage();
			}

			$response['status'] = $res_status;
			$response['msg'] = $res_message;
		}
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function PostCheckVersion(Request $request) {
		if (getCheckAppVersion($request->app_source, $request->app_version)) {
			$response = quotsuccessRes(1, 200, "App Already Updated");
		} else {
			$response = quoterrorRes(2, 402, "Please Update Your App");
		}
		return response()->json($response)->header('Content-Type', 'application/json');
	}
}
