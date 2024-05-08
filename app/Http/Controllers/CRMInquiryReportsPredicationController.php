<?php

namespace App\Http\Controllers;
use App\Models\Inquiry;
use App\Models\User;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CRMInquiryReportsPredicationController extends Controller {

	public function __construct() {

		$this->middleware(function ($request, $next) {

			$tabCanAccessBy = array(0, 1, 2);

			if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
				return redirect()->route('dashboard');

			}

			return $next($request);

		});

	}

	//
	public function index() {

		$currentYear = date('Y');
		$yearArray = [];
		$monthArray = [];
		$yearArray[] = $currentYear;

		for ($i = 0; $i < 5; $i++) {
			$yearArray[] = $yearArray[count($yearArray) - 1] - 1;
		}

		$yearArray = array_reverse($yearArray);

		for ($i = 0; $i < 5; $i++) {
			$yearArray[] = $yearArray[count($yearArray) - 1] + 1;
		}

		for ($i = 1; $i < 13; $i++) {
			$number = str_pad($i, 2, '0', STR_PAD_LEFT);
			$monthArray[] = $number;
		}

		$currentDatetime = date('Y-m-d H:i:s');
		$currentDatetime = date('Y-m-d H:i:s', strtotime($currentDatetime . " +5 hours"));
		$currentDatetime = date('Y-m-d H:i:s', strtotime($currentDatetime . " +30 minutes"));
		$currentYear = date('Y', strtotime($currentDatetime));
		$currentMonth = date('m', strtotime($currentDatetime));

		$reportType = array();

		$reportType[0] = "Predication Count";
		$reportType[1] = "Material Sent";
		$reportType[2] = "Not Potential";
		$reportType[3] = "Rejacted";
		$reportType[4] = "Date Carry Forward";
		$reportType[5] = "Missed";

		$data = array();
		$data['title'] = "Inquiry - Predication Reports";
		$data['source_types'] = getInquirySourceTypes();
		$data['year_array'] = $yearArray;
		$data['month_array'] = $monthArray;
		$data['current_year'] = $currentYear;
		$data['current_month'] = $currentMonth;
		$data['report_type'] = $reportType;

		return view('crm/reports_predication', compact('data'));

	}

	public function getSalesPersonReport(Request $request) {

		$startDate = $request->inquiry_year . "-" . $request->inquiry_month . "-" . "01 00:00:01";
		$endDate = date('Y-m-t 23:59:59', strtotime($startDate));
		$nextMonthEndDate = date("Y-m-d 23:59:59", strtotime($startDate . " -1 day"));

		$viewData = array();
		$key = 0;
		$viewData[$key]['type'] = "Predication Count";

		$query = Inquiry::query();
		$query->select("inquiry.id");
		//$query->where('inquiry.closing_date_time', '>=', $startDate);
		$query->where('inquiry.closing_date_time', '<=', $endDate);
		$query->where('inquiry.closing_date_time', '!=', null);
		$query->whereIn('inquiry.status', array(1, 2, 3, 4, 5, 6, 7, 8));
		if ($request->sales_user_id != 0) {
			$query->where('inquiry.assigned_to', $request->sales_user_id);
		}

		$typeCount = $query->count();
		$predication = $query->get();

		// $predicationId = array();
		// $predicationId[] = 0;
		// foreach ($predication as $keyP => $valueP) {
		// 	$predicationId[] = $valueP->id;

		// }

		$viewData[$key]['type_count'] = $typeCount;

		$key = 1;
		$viewData[$key]['type'] = "Material Sent";

		$query = Inquiry::query();
		$query->select("inquiry.id");
		// $query->whereIn('inquiry.id', $predicationId);
		$query->where('inquiry.closing_date_time', '<=', $endDate);
		//$query->where('inquiry.closing_date_time', '>=', $startDate);
		// $query->where('inquiry.closing_date_time', '<=', $endDate);
		// $query->where('inquiry.closing_date_time', '!=', null);
		$query->whereIn('inquiry.status', array(9, 11));
		if ($request->sales_user_id != 0) {
			$query->where('inquiry.assigned_to', $request->sales_user_id);
		}
		$typeCount = $query->count();
		$viewData[$key]['type_count'] = $typeCount;

		$key = 2;
		$viewData[$key]['type'] = "Not Potential";
		$query = Inquiry::query();
		$query->select("inquiry.id");
		// $query->whereIn('inquiry.id', $predicationId);
		$query->where('inquiry.closing_date_time', '<=', $endDate);
		//$query->where('inquiry.closing_date_time', '>=', $startDate);
		// $query->where('inquiry.closing_date_time', '<=', $endDate);
		// $query->where('inquiry.closing_date_time', '!=', null);
		$query->whereIn('inquiry.status', array(101));
		if ($request->sales_user_id != 0) {
			$query->where('inquiry.assigned_to', $request->sales_user_id);
		}
		$typeCount = $query->count();
		$viewData[$key]['type_count'] = $typeCount;

		$key = 3;
		$viewData[$key]['type'] = "Rejacted";
		$query = Inquiry::query();
		$query->select("inquiry.id");
		//$query->where('inquiry.closing_date_time', '>=', $startDate);
		// $query->whereIn('inquiry.id', $predicationId);
		$query->where('inquiry.closing_date_time', '<=', $endDate);
		//$query->where('inquiry.closing_date_time', '>=', $startDate);
		// $query->where('inquiry.closing_date_time', '<=', $endDate);
		// $query->where('inquiry.closing_date_time', '!=', null);
		$query->whereIn('inquiry.status', array(102));
		if ($request->sales_user_id != 0) {
			$query->where('inquiry.assigned_to', $request->sales_user_id);
		}
		$typeCount = $query->count();
		$viewData[$key]['type_count'] = $typeCount;

		$key = 4;
		$viewData[$key]['type'] = "Date Carry Forward";
		$query = Inquiry::query();
		$query->select("inquiry.id");
		//$query->where('inquiry.closing_date_time', '>=', $startDate);
		// $query->whereIn('inquiry.id', $predicationId);
		$query->where('inquiry.closing_date_time', '<=', $endDate);
		$query->where('inquiry.closing_history', '!=', '');
		//$query->where('inquiry.closing_date_time', '>=', $startDate);
		// $query->where('inquiry.closing_date_time', '<=', $endDate);
		// $query->where('inquiry.closing_date_time', '!=', null);

		if ($request->sales_user_id != 0) {
			$query->where('inquiry.assigned_to', $request->sales_user_id);
		}
		$typeCount = $query->count();
		$viewData[$key]['type_count'] = $typeCount;

		$key = 5;
		$viewData[$key]['type'] = "Missed";
		$query = Inquiry::query();
		$query->select("inquiry.id");
		$query->whereNotIn('inquiry.status', array(9, 11, 101, 102));
		//$query->where('inquiry.closing_date_time', '>=', $startDate);
		// $query->whereIn('inquiry.id', $predicationId);
		// $query->where('inquiry.closing_history', '!=', '');
		//$query->where('inquiry.closing_date_time', '>=', $startDate);
		$query->where('inquiry.closing_date_time', '<=', $nextMonthEndDate);
		// $query->where('inquiry.closing_date_time', '!=', null);

		if ($request->sales_user_id != 0) {
			$query->where('inquiry.assigned_to', $request->sales_user_id);
		}
		$typeCount = $query->count();
		$viewData[$key]['type_count'] = $typeCount;

		$recordsTotal = count($viewData);
		$recordsFiltered = count($viewData);

		$jsonData = array(
			"draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
			"recordsTotal" => intval($recordsTotal), // total number of records
			"recordsFiltered" => intval($recordsFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
			"data" => $viewData, // total data array

		);
		return $jsonData;

	}

	public function searchSalePerson(Request $request) {

		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		$isSalePerson = isSalePerson();

		if ($isSalePerson == 1) {
			$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);

		}

		$User = $UserResponse = array();
		$q = $request->q;
		$User = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));

		if ($isAdminOrCompanyAdmin == 1) {

			$User->whereIn('users.type', array(0, 1, 2));

		} else if ($isSalePerson == 1) {
			$User->where('users.type', 2);
			$User->whereIn('id', $childSalePersonsIds);
		}
		$User->where(function ($query) use ($q) {
			$query->where('users.first_name', 'like', '%' . $q . '%');
			$query->orWhere('users.last_name', 'like', '%' . $q . '%');
		});
		$User->where('users.status', 1);
		$User->limit(5);
		$User = $User->get();

		if (count($User) > 0) {
			foreach ($User as $User_key => $User_value) {
				$UserResponse[$User_key]['id'] = $User_value['id'];
				$UserResponse[$User_key]['text'] = $User_value['full_name'];
			}
		}

		$UserKey = count($UserResponse);
		$UserResponse[$UserKey]['id'] = 0;
		$UserResponse[$UserKey]['text'] = "All";
		$response = array();
		$response['results'] = $UserResponse;
		$response['pagination']['more'] = false;
		return response()->json($response)->header('Content-Type', 'application/json');

	}

	public function inquiryList(Request $request) {

		$startDate = $request->inquiry_year . "-" . $request->inquiry_month . "-" . "01 00:00:01";
		$endDate = date('Y-m-t 23:59:59', strtotime($startDate));
		$nextMonthEndDate = date("Y-m-d 23:59:59", strtotime($startDate . " -1 day"));

		$searchColumns = array(
			'inquiry.id',
			'inquiry.first_name',
			'inquiry.last_name',
			'inquiry.phone_number',
			'inquiry.house_no',
			'inquiry.society_name',
			'inquiry.area',
			'inquiry.pincode',
			'inquiry.city_id',
			'inquiry.status',
			'inquiry.source_type_lable',
			'inquiry.source_type_value',
			'inquiry.follow_up_type',
			'inquiry.follow_up_date_time',

		);

		$sortingColumns = array(
			0 => 'inquiry.id',
			1 => 'inquiry.first_name',
			2 => 'inquiry.last_name',
			3 => 'inquiry.phone_number',
			4 => 'inquiry.house_no',
			5 => 'inquiry.status',
			6 => 'inquiry.source_type_lable',
			7 => 'inquiry.source_type_value',
			8 => 'inquiry.id',

		);

		$selectColumns = array(
			'inquiry.id',
			'inquiry.first_name',
			'inquiry.last_name',
			'inquiry.phone_number',
			'inquiry.house_no',
			'inquiry.society_name',
			'inquiry.area',
			'inquiry.pincode',
			'inquiry.city_id',
			'inquiry.status',
			'inquiry.source_type',
			'inquiry.source_type_lable',
			'inquiry.source_type_value',
			'users.first_name as source_first_name',
			'users.last_name as source_last_name',
			'channel_partner.firm_name as source_firm_name',
			'inquiry.follow_up_type',
			'inquiry.follow_up_date_time',
			'inquiry.quotation_amount',
			'inquiry.answer_date_time',

		);

		$query = Inquiry::query();
		$query->select('inquiry.*');
		$query->leftJoin('users as architect_user', 'architect_user.id', '=', 'inquiry.architect');
		$query->leftJoin('users as electrician_user', 'electrician_user.id', '=', 'inquiry.electrician');
		//$query->where('inquiry.closing_date_time', '>=', $startDate);
		if ($request->report_type == 0) {

			$query->where('inquiry.closing_date_time', '<=', $endDate);
			$query->where('inquiry.closing_date_time', '!=', null);

		} else if ($request->report_type == 1) {

			$query->where('inquiry.closing_date_time', '<=', $endDate);
			$query->where('inquiry.closing_date_time', '!=', null);
			$query->whereIn('inquiry.status', array(9, 11));

		} else if ($request->report_type == 2) {

			$query->where('inquiry.closing_date_time', '<=', $endDate);
			$query->where('inquiry.closing_date_time', '!=', null);
			$query->whereIn('inquiry.status', array(101));

		} else if ($request->report_type == 3) {

			$query->where('inquiry.closing_date_time', '<=', $endDate);
			$query->where('inquiry.closing_date_time', '!=', null);
			$query->whereIn('inquiry.status', array(102));

		} else if ($request->report_type == 4) {

			$query->where('inquiry.closing_date_time', '<=', $endDate);
			$query->where('inquiry.closing_date_time', '!=', null);
			$query->where('inquiry.closing_history', '!=', '');

		} else if ($request->report_type == 5) {

			$query->whereNotIn('inquiry.status', array(9, 11, 101, 102));
			$query->where('inquiry.closing_date_time', '<=', $nextMonthEndDate);

		}

		if ($request->sales_user_id != 0) {
			$query->where('inquiry.assigned_to', $request->sales_user_id);
		}

		$recordsTotal = $query->count();
		$quotationTotal = $query->sum('quotation_amount');

		$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.
		$query = Inquiry::query();
		$query->select('inquiry.*');
		$query->leftJoin('users as architect_user', 'architect_user.id', '=', 'inquiry.architect');
		$query->leftJoin('users as electrician_user', 'electrician_user.id', '=', 'inquiry.electrician');
		$query->leftJoin('users', 'users.id', '=', 'inquiry.source_type_value');
		$query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
		if ($request->report_type == 0) {

			$query->where('inquiry.closing_date_time', '<=', $endDate);
			$query->where('inquiry.closing_date_time', '!=', null);

		} else if ($request->report_type == 1) {

			$query->where('inquiry.closing_date_time', '<=', $endDate);
			$query->where('inquiry.closing_date_time', '!=', null);
			$query->whereIn('inquiry.status', array(9, 11));

		} else if ($request->report_type == 2) {

			$query->where('inquiry.closing_date_time', '<=', $endDate);
			$query->where('inquiry.closing_date_time', '!=', null);
			$query->whereIn('inquiry.status', array(101));

		} else if ($request->report_type == 3) {

			$query->where('inquiry.closing_date_time', '<=', $endDate);
			$query->where('inquiry.closing_date_time', '!=', null);
			$query->whereIn('inquiry.status', array(102));

		} else if ($request->report_type == 4) {

			$query->where('inquiry.closing_date_time', '<=', $endDate);
			$query->where('inquiry.closing_date_time', '!=', null);
			$query->where('inquiry.closing_history', '!=', '');

		} else if ($request->report_type == 5) {

			$query->whereNotIn('inquiry.status', array(9, 11, 101, 102));
			$query->where('inquiry.closing_date_time', '<=', $nextMonthEndDate);

		}
		if ($request->sales_user_id != 0) {
			$query->where('inquiry.assigned_to', $request->sales_user_id);
		}

		$query->select($selectColumns);
		$query->limit($request->length);
		$query->offset($request->start);
		$query->orderBy($sortingColumns[$request['order'][0]['column']], $request['order'][0]['dir']);
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

		$viewData = array();
		$inquiryStatus = getInquiryStatus();

		foreach ($data as $key => $value) {
			$valueAnserDateTime = convertDateTime($value['answer_date_time']);

			$viewData[$key]['id'] = $value['id'];
			$viewData[$key]['name'] = '<p class="text-muted mb-0" data-bs-toggle="tooltip"  data-bs-original-title="' . $value['first_name'] . " " . $value['last_name'] . '">' . displayStringLenth($value['first_name'] . " " . $value['last_name'], 20) . '</p>';
			//$viewData[$key]['last_name'] = $value['last_name'];
			$viewData[$key]['phone_number'] = $value['phone_number'];
			$viewData[$key]['address'] = '<p class="text-muted mb-0" data-bs-toggle="tooltip"  data-bs-original-title="' . $value['house_no'] . " " . $value['society_name'] . " " . $value['area'] . " " . $value['pincode'] . '">' . displayStringLenth($value['house_no'] . " " . $value['society_name'] . " " . $value['area'] . " " . $value['pincode'], 40) . '</p>';

			$statusLable = $inquiryStatus[$value['status']]['name'];

			$uiAction = '<ul class="list-inline contact-links mb-0">';

			$uiAction .= '<li class="list-inline-item ">';
			$uiAction .= '<a data-bs-toggle="tooltip" href="javascript: void(0);" title="Status Update Date & Time : ' . $valueAnserDateTime . '"><i class="bx bx-calendar"></i></a>';

			$uiAction .= '<li class="list-inline-item ">';
			$uiAction .= '<a onclick="editView(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Edit">' . $statusLable . '</a>';
			$uiAction .= '</li>';
			$uiAction .= '</ul>';
			$viewData[$key]['status'] = $uiAction;

			$piecesOfSourceType = explode("-", $value['source_type']);
			if ($piecesOfSourceType[0] == "user") {

				$isChannelPartner = isChannelPartner($piecesOfSourceType[1]);

				if ($isChannelPartner != 0) {

					$viewData[$key]['source'] = '<p class="text-muted mb-0" data-bs-toggle="tooltip"  data-bs-original-title="' . $value['source_firm_name'] . '"> ' . displayStringLenth($value['source_firm_name'], 20) . '</p>';

				} else {

					$viewData[$key]['source'] = '<p class="text-muted mb-0" data-bs-toggle="tooltip"  data-bs-original-title="' . $value['source_first_name'] . " " . $value['source_last_name'] . '" >' . displayStringLenth($value['source_first_name'] . " " . $value['source_last_name'], 20) . '</p>';

				}

			} else {

				$viewData[$key]['source'] = '<p class="text-muted mb-0" data-bs-toggle="tooltip"  data-bs-original-title="' . $value['source_type_value'] . '" >' . displayStringLenth($value['source_type_value'], 20) . '</p>';

			}

			$viewData[$key]['detail'] = "<a class='' target='_blank' href='" . route('inquiry') . "?status=0&inquiry_id=" . $value['id'] . "'> Detail</a>";

			$viewData[$key]['source_type'] = $value['source_type_lable'];

			$viewData[$key]['quotation_amount'] = $value['quotation_amount'];
			if ($viewData[$key]['quotation_amount'] != "") {
				$viewData[$key]['quotation_amount'] = priceLable($value['quotation_amount']);
			}

		}

		$jsonData = array(
			"draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
			"recordsTotal" => intval($recordsTotal), // total number of records
			"recordsFiltered" => intval($recordsFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
			"data" => $viewData, // total data array
			"quotationAmount" => priceLable($quotationTotal),

		);
		return $jsonData;

	}

}
