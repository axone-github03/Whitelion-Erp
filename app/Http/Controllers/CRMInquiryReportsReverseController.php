<?php

namespace App\Http\Controllers;
use App\Models\Inquiry;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CRMInquiryReportsReverseController extends Controller {

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

		$data = array();
		$data['title'] = "Inquiry - Reverse Reports";
		$data['source_types'] = getInquirySourceTypes();

		return view('crm/reports_reverse', compact('data'));

	}

	public function getSalesPersonReport(Request $request) {

		$startDate = date('Y-m-d 00:00:00', strtotime($request->start_date));
		$startDate = date('Y-m-d H:i:s', strtotime($startDate . " -5 hours"));
		$startDate = date('Y-m-d H:i:s', strtotime($startDate . " -30 minutes"));

		$endDate = date('Y-m-d 23:59:59', strtotime($request->end_date));
		$endDate = date('Y-m-d H:i:s', strtotime($endDate . " -5 hours"));
		$endDate = date('Y-m-d H:i:s', strtotime($endDate . " -30 minutes"));

		// $sourceTypePieces = explode("-", $request->source_type);

		$query = Inquiry::query();
		//$query->select('source_type', 'source_type_value', 'source_type_1', 'source_type_value_1', 'source_type_2', 'source_type_value_2', 'source_type_3', 'source_type_value_3', 'source_type_4', 'source_type_value_4', 'assigned_to');

		$query->select('source_type', 'source_type_value', 'source_type_1', 'source_type_value_1', 'source_type_2', 'source_type_value_2', 'source_type_3', 'source_type_value_3', 'source_type_4', 'source_type_value_4');
		$query->where('inquiry.created_at', '>=', $startDate);
		$query->where('inquiry.created_at', '<=', $endDate);

		if ($request->source_type != "0-0") {

			$query->where('inquiry.source_type', $request->source_type);
			$query->orWhere('inquiry.source_type_1', $request->source_type);
			$query->orWhere('inquiry.source_type_2', $request->source_type);
			$query->orWhere('inquiry.source_type_3', $request->source_type);
			$query->orWhere('inquiry.source_type_4', $request->source_type);

		}

		$inquiryGiven = $query->get();

		$inquiryHasSource = array();

		foreach ($inquiryGiven as $key => $value) {

			$piecesOfSourceType = explode(",", $value->source_type);
			if ($piecesOfSourceType == "user") {
				if ($value->source_type_value != "") {
					$inquiryHasSource[] = $value->source_type_value;
				}

			}

			$piecesOfSourceType = explode(",", $value->source_type_2);
			if ($piecesOfSourceType == "user") {
				if ($value->source_type_value != "") {
					$inquiryHasSource[] = $value->source_type_value_2;
				}

			}
			$piecesOfSourceType = explode(",", $value->source_type_3);
			if ($piecesOfSourceType == "user") {
				if ($value->source_type_value != "") {
					$inquiryHasSource[] = $value->source_type_value_3;
				}

			}

			$piecesOfSourceType = explode(",", $value->source_type_4);
			if ($piecesOfSourceType == "user") {
				if ($value->source_type_value != "") {
					$inquiryHasSource[] = $value->source_type_value_4;
				}

			}

		}

		$inquiryHasSource = array_unique($inquiryHasSource);
		$inquiryHasSource = array_values($inquiryHasSource);

		$sourceType = explode("-", $request->source_type);

		$query = User::select('id', 'first_name', 'last_name');

		$query->whereIn('type', array(201, 202, 301, 302, 101, 102, 103, 104, 105));
		$query->where('users.status', 1);

		if (count($inquiryHasSource) > 0) {
			$query->whereNotIn('id', $inquiryHasSource);
		}

		if ($request->source_type != "0-0") {
			if ($sourceType[1]) {

				$query->where('type', $sourceType[1]);
			}
		}

		$recordsTotal = $query->count();
		$recordsFiltered = $recordsTotal;

		$searchColumns = array(
			0 => 'users.id',
			1 => 'users.first_name',
			2 => 'users.last_name',
			3 => 'users.type',

		);

		$sortingColumns = array(
			0 => 'users.id',
			1 => 'users.first_name',
			2 => 'users.last_name',
			3 => 'users.type',

		);

		$selectColumns = array(
			0 => 'users.id',
			1 => 'users.first_name',
			2 => 'users.last_name',
			3 => 'users.type',

		);

		$query = User::query();
		$query->where('users.status', 1);
		$query->whereIn('type', array(201, 202, 301, 302, 101, 102, 103, 104, 105));
		if ($request->source_type != "0-0") {
			if ($sourceType[1]) {
				$query->where('type', $sourceType[1]);
			}
		}

		if (count($inquiryHasSource) > 0) {
			$query->whereNotIn('id', $inquiryHasSource);
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

		foreach ($data as $key => $value) {

			$viewData[$key]['id'] = $value['id'];
			$viewData[$key]['source'] = $value['first_name'] . " " . $value['last_name'];
			$viewData[$key]['type'] = getAllUserTypes()[$value['type']]['short_name'];
			$anotherName = isset(getAllUserTypes()[$value['type']]['another_name']) ? getAllUserTypes()[$value['type']]['another_name'] : '';

			$viewData[$key]['type'] = $viewData[$key]['type'] . " " . $anotherName;
		}

		$jsonData = array(
			"draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
			"recordsTotal" => intval($recordsTotal), // total number of records
			"recordsFiltered" => intval($recordsFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
			"data" => $viewData, // total data array

		);
		return $jsonData;

	}

	public function download(Request $request) {

		$startDate = date('Y-m-d 00:00:00', strtotime($request->start_date));
		$startDate = date('Y-m-d H:i:s', strtotime($startDate . " -5 hours"));
		$startDate = date('Y-m-d H:i:s', strtotime($startDate . " -30 minutes"));

		$endDate = date('Y-m-d 23:59:59', strtotime($request->end_date));
		$endDate = date('Y-m-d H:i:s', strtotime($endDate . " -5 hours"));
		$endDate = date('Y-m-d H:i:s', strtotime($endDate . " -30 minutes"));

		$query = Inquiry::query();
		//$query->select('source_type', 'source_type_value', 'source_type_1', 'source_type_value_1', 'source_type_2', 'source_type_value_2', 'source_type_3', 'source_type_value_3', 'source_type_4', 'source_type_value_4', 'assigned_to');
		$query->select('assigned_to');
		$query->where('inquiry.created_at', '>=', $startDate);
		$query->where('inquiry.created_at', '<=', $endDate);

		if ($request->source_type != "0-0") {

			$query->where('inquiry.source_type', $request->source_type);

		}

		$inquiryGiven = $query->get();

		$assingedInquiry = array();

		foreach ($inquiryGiven as $key => $value) {

			$assingedInquiry[] = $value->assigned_to;

		}

		$assingedInquiry = array_unique($assingedInquiry);
		$assingedInquiry = array_values($assingedInquiry);

		$query = User::select('id', 'first_name', 'last_name', 'phone_number');

		$query->where('type', 2);

		if (count($assingedInquiry) > 0) {
			$query->whereIn('id', $assingedInquiry);
		}

		$data = $query->get();

		$headers = array(
			'#id',
			"first name",
			"last name",
			"Phone Number",

		);

		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="reports.csv"');
		$fp = fopen('php://output', 'wb');

		fputcsv($fp, $headers);
		foreach ($data as $key => $value) {

			$lineVal = array(
				$value->id,
				$value->first_name,
				$value->last_name,
				$value->phone_number,

			);

			fputcsv($fp, $lineVal, ",");
		}
		fclose($fp);

	}

}
