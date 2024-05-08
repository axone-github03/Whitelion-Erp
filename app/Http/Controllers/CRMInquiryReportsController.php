<?php

namespace App\Http\Controllers;

use App\Models\Inquiry;
use App\Models\InquiryQuestion;
use App\Models\InquiryQuestionAnswer;
use App\Models\InquiryQuestionOption;
use App\Models\User;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CRMInquiryReportsController extends Controller
{

	public function __construct()
	{

		$this->middleware(function ($request, $next) {

			$tabCanAccessBy = array(0, 1);
			// $tabCanAccessBy = array(0, 1, 2);

			if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
				return redirect()->route('dashboard');
			}

			return $next($request);
		});
	}

	//
	public function index()
	{

		$ClientProduct = InquiryQuestion::where('id', 1061)->orderBy('status', 'asc')->orderBy('sequence', 'asc')->first();

		$ClientProduct['options'] = InquiryQuestionOption::select('id', 'option')->where('inquiry_question_id', $ClientProduct->id)->orderBy('id', 'asc')->get();

		$data = array();
		$data['title'] = "Inquiry - Reports";
		$data['source_types'] = getInquirySourceTypes();
		$data['client_product'] = $ClientProduct;

		return view('crm/reports', compact('data'));
	}

	public function searchSalePerson(Request $request)
	{

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

	public function searchSource(Request $request)
	{

		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		$isSalePerson = isSalePerson();

		if ($isSalePerson == 1) {
			$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
		}

		$UserResponse = array();
		$q = $request->q;
		$User = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"), 'channel_partner.firm_name');

		$User->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
		$sourceTypePieces = explode("-", $request->source_type);

		$User->whereIn('users.type', array(201, 202, 301, 302, 101, 102, 103, 104, 105));
		if ($isAdminOrCompanyAdmin == 1) {

			$User->where('users.type', $sourceTypePieces[1]);
		} else if ($isSalePerson == 1) {
			$User->whereIn('users.type', array(201, 202, 301, 302, 101, 102, 103, 104, 105));
			$User->where('users.type', $sourceTypePieces[1]);

			$User->leftJoin('sale_person', 'sale_person.user_id', '=', 'users.id');
			$User->leftJoin('architect', 'architect.user_id', '=', 'users.id');
			$User->leftJoin('electrician', 'electrician.user_id', '=', 'users.id');
			// print_r($childSalePersonsIds);
			// die;
			$User->where(function ($query) use ($childSalePersonsIds) {

				$query->where(function ($query2) use ($childSalePersonsIds) {

					foreach ($childSalePersonsIds as $key => $value) {
						if ($key == 0) {
							$query2->whereRaw('FIND_IN_SET("' . $value . '",channel_partner.sale_persons)>0');
						} else {
							$query2->orWhereRaw('FIND_IN_SET("' . $value . '",channel_partner.sale_persons)>0');
						}
					}
				});

				$query->orWhereIn('architect.sale_person_id', $childSalePersonsIds);
				$query->orWhereIn('electrician.sale_person_id', $childSalePersonsIds);
			});
		}
		$User->where(function ($query) use ($q) {
			$query->where('users.first_name', 'like', '%' . $q . '%');
			$query->orWhere('users.last_name', 'like', '%' . $q . '%');
			$query->orWhere('channel_partner.firm_name', 'like', '%' . $q . '%');
		});
		$User->where('users.status', 1);
		$User->limit(5);
		$User = $User->get();

		if (count($User) > 0) {
			foreach ($User as $UserKey => $UserValue) {

				$UserResponse[$UserKey]['id'] = $UserValue['id'];
				$UserResponse[$UserKey]['text'] = isset($UserValue['firm_name']) ? $UserValue['firm_name'] : $UserValue['full_name'];
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

	public function getSalesPersonReport(Request $request)
	{
		DB::enableQueryLog();
	
		$isSalePerson = isSalePerson();
		if ($isSalePerson == 1) {
			$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
		}

		if ($request->status == "102" && $request->client_product != 0) {

			$inquiryClientProduct = array(0);

			$InquiryQuestionAnswer = InquiryQuestionAnswer::select('inquiry_id')->where('inquiry_question_id', 1061)->where('answer', $request->client_product)->get();
			foreach ($InquiryQuestionAnswer as $keyQ => $valueQ) {
				$inquiryClientProduct[] = $valueQ->inquiry_id;
			}
		}

		$searchColumns = array(
			0 => 'inquiry.id',
			1 => 'users.first_name',

		);

		$sortingColumns = array(
			0 => 'inquiry.id',

		);

		$selectColumns = array(
			'inquiry.assigned_to',
			DB::raw('count(*) as inquiry_count'),

		);

		$startDate = date('Y-m-d', strtotime($request->start_date));
		// $startDate = date('Y-m-d 00:00:00', strtotime($request->start_date));
		// $startDate = date('Y-m-d H:i:s', strtotime($startDate . " -5 hours"));
		// $startDate = date('Y-m-d H:i:s', strtotime($startDate . " -30 minutes"));

		$endDate = date('Y-m-d', strtotime($request->end_date));
		// $endDate = date('Y-m-d 23:59:59', strtotime($request->end_date));
		// $endDate = date('Y-m-d H:i:s', strtotime($endDate . " -5 hours"));
		// $endDate = date('Y-m-d H:i:s', strtotime($endDate . " -30 minutes"));

		$query = Inquiry::query();
		// $query->select($selectColumns);
		$query->leftJoin('users as architect_user', 'architect_user.id', '=', 'inquiry.architect');
		$query->leftJoin('users as electrician_user', 'electrician_user.id', '=', 'inquiry.electrician');
		$query->groupBy('inquiry.assigned_to');
		if ($request->status != "0") {

			if ($request->status != "!10") {
				if ($request->status == "9,10,11") {

					$query->whereDate('inquiry.material_sent_date_time', '>=', $startDate);
					$query->whereDate('inquiry.material_sent_date_time', '<=', $endDate);
				} else {

					if ($request->status != "1,2,3,4,5,6,7,8" && $request->status != "!10") {
						$query->whereDate('inquiry.answer_date_time', '>=', $startDate);
						$query->whereDate('inquiry.answer_date_time', '<=', $endDate);
					}
				}

				$query->whereIn('inquiry.status', explode(",", $request->status));
			} else {
				// if ($request->status != "1,2,3,4,5,6,7,8" && $request->status != "!10") {
				// 	$query->where('inquiry.claimed_date_time', '>=', $startDate);
				// 	$query->where('inquiry.claimed_date_time', '<=', $endDate);
				// }
				$query->where('inquiry.is_point_calculated', 0);
				$query->whereIn('inquiry.status', array(9, 11));
				//$query->where('inquiry.billing_invoice', '');

				$query->where(function ($query2) {

					$query2->where(function ($query3) {

						$query3->where('inquiry.source_type', "user-202");
						$query3->orWhere('inquiry.source_type', "user-302");
					});

					$query2->orWhere(function ($query3) {

						$query3->where('inquiry.source_type_1', "user-202");
						$query3->orWhere('inquiry.source_type_1', "user-302");
					});

					$query2->orWhere(function ($query3) {

						$query3->where('inquiry.source_type_2', "user-202");
						$query3->orWhere('inquiry.source_type_2', "user-302");
					});

					$query2->orWhere(function ($query3) {

						$query3->where('inquiry.source_type_3', "user-202");
						$query3->orWhere('inquiry.source_type_3', "user-302");
					});

					$query2->orWhere(function ($query3) {

						$query3->where('inquiry.source_type_4', "user-202");
						$query3->orWhere('inquiry.source_type_4', "user-302");
					});

					$query2->orWhere(function ($query3) {

						$query3->where(function ($query4) {

							$query4->where('inquiry.architect', '!=', 0);
							$query4->where('architect_user.type', 202);
						});

						$query3->orWhere(function ($query4) {

							$query4->where('inquiry.electrician', '!=', 0);
							$query4->where('electrician_user.type', 302);
						});
					});
				});
			}

			if ($request->status == "102" && $request->client_product != 0) {

				$query->whereIn('inquiry.id', $inquiryClientProduct);
			}
		} else {
			if ($request->status != "1,2,3,4,5,6,7,8" && $request->status != "!10") {

				$query->whereDate('inquiry.created_at', '>=', $startDate);
				$query->whereDate('inquiry.created_at', '<=', $endDate);
			}
		}

		if ($request->source_user != 0) {

			$query->where('inquiry.source_type_value', $request->source_user);
		}

		if ($request->source_type != "0-0") {

			$query->where('inquiry.source_type', $request->source_type);
		}

		if ($isSalePerson == 1) {

			$query->whereIn('inquiry.assigned_to', $childSalePersonsIds);
		}
		$query->whereNotIn('assigned_to', getInquiryTransferToLeadUserList());
		$recordsTotal = $query->count();

		$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.
		$query = Inquiry::query();
		// $query->select('inquiry.*');
		// $query->selectRaw('CONCAT(users.first_name," ",users.last_name) AS assign_person');
		$query->leftJoin('users as architect_user', 'architect_user.id', '=', 'inquiry.architect');
		$query->leftJoin('users as electrician_user', 'electrician_user.id', '=', 'inquiry.electrician');
		$query->leftJoin('users', 'users.id', '=', 'inquiry.assigned_to');
		$query->groupBy('inquiry.assigned_to');
		// $query->where('inquiry.created_at', '>=', $startDate);
		// $query->where('inquiry.created_at', '<=', $endDate);
		if ($request->status != "0") {

			if ($request->status != "!10") {

				if ($request->status == "9,10,11") {

					$query->whereDate('inquiry.material_sent_date_time', '>=', $startDate);
					$query->whereDate('inquiry.material_sent_date_time', '<=', $endDate);
				} else {

					if ($request->status != "1,2,3,4,5,6,7,8" && $request->status != "!10") {

						$query->whereDate('inquiry.answer_date_time', '>=', $startDate);
						$query->whereDate('inquiry.answer_date_time', '<=', $endDate);
					}
				}
				$query->whereIn('inquiry.status', explode(",", $request->status));
			} else {
				$query->where('inquiry.is_point_calculated', 0);
				$query->whereIn('inquiry.status', array(9, 11));
				//$query->where('inquiry.billing_invoice', '');

				$query->where(function ($query2) {

					$query2->where(function ($query3) {

						$query3->where('inquiry.source_type', "user-202");
						$query3->orWhere('inquiry.source_type', "user-302");
					});

					$query2->orWhere(function ($query3) {

						$query3->where('inquiry.source_type_1', "user-202");
						$query3->orWhere('inquiry.source_type_1', "user-302");
					});

					$query2->orWhere(function ($query3) {

						$query3->where('inquiry.source_type_2', "user-202");
						$query3->orWhere('inquiry.source_type_2', "user-302");
					});

					$query2->orWhere(function ($query3) {

						$query3->where('inquiry.source_type_3', "user-202");
						$query3->orWhere('inquiry.source_type_3', "user-302");
					});

					$query2->orWhere(function ($query3) {

						$query3->where('inquiry.source_type_4', "user-202");
						$query3->orWhere('inquiry.source_type_4', "user-302");
					});

					// $query2->orWhere(function ($query3) {

					// 	$query3->where(function ($query4) {

					// 		$query4->where('inquiry.architect', '!=', 0);
					// 		$query4->where('architect_user.type', 202);

					// 	});

					// 	$query3->orWhere(function ($query4) {

					// 		$query4->where('inquiry.electrician', '!=', 0);
					// 		$query4->where('electrician_user.type', 302);

					// 	});

					// });

				});
			}

			if ($request->status == "102" && $request->client_product != 0) {

				$query->whereIn('inquiry.id', $inquiryClientProduct);
			}
		} else {
			if ($request->status != "1,2,3,4,5,6,7,8" && $request->status != "!10") {

				$query->whereDate('inquiry.created_at', '>=', $startDate);
				$query->whereDate('inquiry.created_at', '<=', $endDate);
			}
		}

		if ($request->source_user != 0) {

			$query->where('inquiry.source_type_value', $request->source_user);
		}
		if ($request->source_type != "0-0") {

			$query->where('inquiry.source_type', $request->source_type);
		}
		if ($isSalePerson == 1) {

			$query->whereIn('inquiry.assigned_to', $childSalePersonsIds);
		}

		$query->select($selectColumns);
		// $query->limit($request->length);
		// $query->offset($request->start);
		//$query->orderBy($sortingColumns[$request['order'][0]['column']], $request['order'][0]['dir']);

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
		$query->whereNotIn('assigned_to', getInquiryTransferToLeadUserList());
		$data = $query->get();
		$data = json_decode(json_encode($data), true);
		if ($isFilterApply == 1) {
			$recordsFiltered = count($data);
		}

		$userIds = array();
		// $userIds[] = 0;

		foreach ($data as $key => $valuea) {
			$userIds[] = $valuea['assigned_to'];
		}

		$Users = User::select('id', 'first_name', 'last_name')->whereIn('id', $userIds)->get();
		$UserReffeance = array();
		foreach ($Users as $key => $valueb) {
			$UserReffeance[$valueb->id] = $valueb->first_name . " " . $valueb->last_name;
		}

		$viewData = array();
		$totalInquiry = 0;

		foreach ($data as $key => $value) {

			$viewData[$key]['sale_persons'] = highlightString($UserReffeance[$value['assigned_to']],$search_value);
			// $viewData[$key]['sale_persons'] = $value['assigned_to'];
			$viewData[$key]['inquiry_count'] = highlightString($value['inquiry_count'],$search_value);
			$totalInquiry = $totalInquiry + $value['inquiry_count'];
		}
		$noOFRecord = count($viewData);
		$viewData[$noOFRecord]['sale_persons'] = highlightString("Total",$search_value);
		$viewData[$noOFRecord]['inquiry_count'] = highlightString($totalInquiry,$search_value);

		$jsonData = array(
			"draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
			"recordsTotal" => intval($recordsTotal), // total number of records
			"recordsFiltered" => intval($recordsFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
			"data" => $viewData, // total data array
			"data1" => $userIds, // total data array
			"user" => $Users, // total data array
			"query" => DB::getQueryLog(), // total data array

		);
		return $jsonData;
	}

	public function getSourceTypesReport(Request $request)
	{
		$sourceTypes = getInquirySourceTypes();
		$isSalePerson = isSalePerson();
		if ($isSalePerson == 1) {
			$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
		}

		if ($request->status == "102" && $request->client_product != 0) {

			$inquiryClientProduct = array();

			$InquiryQuestionAnswer = InquiryQuestionAnswer::select('inquiry_id')->where('inquiry_question_id', 1061)->where('answer', $request->client_product)->get();
			foreach ($InquiryQuestionAnswer as $keyQ => $valueQ) {
				$inquiryClientProduct[] = $valueQ->inquiry_id;
			}
		}

		$startDate = date('Y-m-d', strtotime($request->start_date));
		// $startDate = date('Y-m-d 00:00:00', strtotime($request->start_date));
		// $startDate = date('Y-m-d H:i:s', strtotime($startDate . " -5 hours"));
		// $startDate = date('Y-m-d H:i:s', strtotime($startDate . " -30 minutes"));

		$endDate = date('Y-m-d', strtotime($request->end_date));
		// $endDate = date('Y-m-d 23:59:59', strtotime($request->end_date));
		// $endDate = date('Y-m-d H:i:s', strtotime($endDate . " -5 hours"));
		// $endDate = date('Y-m-d H:i:s', strtotime($endDate . " -30 minutes"));
		$query = Inquiry::query();
		$query->select('inquiry.source_type', DB::raw('count(*) as inquiry_count'),);
		$query->leftJoin('users as architect_user', 'architect_user.id', '=', 'inquiry.architect');
		$query->leftJoin('users as electrician_user', 'electrician_user.id', '=', 'inquiry.electrician');
		$query->groupBy('inquiry.source_type');
		// $query->where('inquiry.created_at', '>=', $startDate);
		// $query->where('inquiry.created_at', '<=', $endDate);

		if ($request->status != "0") {

			if ($request->status != "!10") {

				if ($request->status == "9,10,11") {

					$query->whereDate('inquiry.material_sent_date_time', '>=', $startDate);
					$query->whereDate('inquiry.material_sent_date_time', '<=', $endDate);
				} else {

					if ($request->status != "1,2,3,4,5,6,7,8" && $request->status != "!10") {

						$query->whereDate('inquiry.answer_date_time', '>=', $startDate);
						$query->whereDate('inquiry.answer_date_time', '<=', $endDate);
					}
				}
				$query->whereIn('inquiry.status', explode(",", $request->status));
			} else {
				$query->where('inquiry.is_point_calculated', 0);
				$query->whereIn('inquiry.status', array(9, 11));
				//$query->where('inquiry.billing_invoice', '');

				$query->where(function ($query2) {

					$query2->where(function ($query3) {

						$query3->where('inquiry.source_type', "user-202");
						$query3->orWhere('inquiry.source_type', "user-302");
					});

					$query2->orWhere(function ($query3) {

						$query3->where('inquiry.source_type_1', "user-202");
						$query3->orWhere('inquiry.source_type_1', "user-302");
					});

					$query2->orWhere(function ($query3) {

						$query3->where('inquiry.source_type_2', "user-202");
						$query3->orWhere('inquiry.source_type_2', "user-302");
					});

					$query2->orWhere(function ($query3) {

						$query3->where('inquiry.source_type_3', "user-202");
						$query3->orWhere('inquiry.source_type_3', "user-302");
					});

					$query2->orWhere(function ($query3) {

						$query3->where('inquiry.source_type_4', "user-202");
						$query3->orWhere('inquiry.source_type_4', "user-302");
					});

					$query2->orWhere(function ($query3) {

						$query3->where(function ($query4) {

							$query4->where('inquiry.architect', '!=', 0);
							$query4->where('architect_user.type', 202);
						});

						$query3->orWhere(function ($query4) {

							$query4->where('inquiry.electrician', '!=', 0);
							$query4->where('electrician_user.type', 302);
						});
					});
				});
			}

			if ($request->status == "102" && $request->client_product != 0) {

				$query->whereIn('inquiry.id', $inquiryClientProduct);
			}
		} else {
			if ($request->status != "1,2,3,4,5,6,7,8" && $request->status != "!10") {

				$query->whereDate('inquiry.created_at', '>=', $startDate);
				$query->whereDate('inquiry.created_at', '<=', $endDate);
			}
		}

		$query->where('inquiry.assigned_to', $request->sales_user_id);

		if ($isSalePerson == 1) {

			$query->whereIn('inquiry.assigned_to', $childSalePersonsIds);
		}
		$query->whereNotIn('inquiry.assigned_to', getInquiryTransferToLeadUserList());
		$data = $query->get();

		$sourceTypeInquiry = array();

		foreach ($data as $key => $value) {
			$sourceTypeInquiry[$value->source_type] = $value->inquiry_count;
		}

		$tableResponse = "";
		$totalInquiryCount = 0;
		foreach ($sourceTypes as $key => $value) {

			$inquiryCount = isset($sourceTypeInquiry[$value['type'] . "-" . $value['id']]) ? $sourceTypeInquiry[$value['type'] . "-" . $value['id']] : 0;
			$tableResponse .= "<tr>";
			$tableResponse .= "<td>";
			$tableResponse .= $value['lable'];
			$tableResponse .= "</td>";
			$tableResponse .= "<td>";
			$tableResponse .= "" . $inquiryCount;
			$tableResponse .= "</td>";
			$tableResponse .= "</tr>";
			$totalInquiryCount = $totalInquiryCount + $inquiryCount;
		}

		$tableResponse .= "<tr>";
		$tableResponse .= "<td>";
		$tableResponse .= "Total";
		$tableResponse .= "</td>";
		$tableResponse .= "<td>";
		$tableResponse .= "" . $totalInquiryCount;
		$tableResponse .= "</td>";
		$tableResponse .= "</tr>";

		$response = successRes("source types reports");
		$response['view'] = $tableResponse;
		$response['data'] = $data;

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function getSourceReport(Request $request)
	{

		$sourceTypes = getInquirySourceTypes();

		$isSalePerson = isSalePerson();
		if ($isSalePerson == 1) {
			$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
		}

		if ($request->status == "102" && $request->client_product != 0) {

			$inquiryClientProduct = array();

			$InquiryQuestionAnswer = InquiryQuestionAnswer::select('inquiry_id')->where('inquiry_question_id', 1061)->where('answer', $request->client_product)->get();
			foreach ($InquiryQuestionAnswer as $keyQ => $valueQ) {
				$inquiryClientProduct[] = $valueQ->inquiry_id;
			}
		}

		$searchColumns = array(
			0 => 'inquiry.id',
			1 => 'users.first_name',
			2 => 'users.last_name',

		);

		$sortingColumns = array(
			0 => 'inquiry.id',

		);

		$selectColumns = array(
			'inquiry.source_type_value',
			DB::raw('count(*) as inquiry_count'),

		);

		$startDate = date('Y-m-d', strtotime($request->start_date));
		// $startDate = date('Y-m-d 00:00:00', strtotime($request->start_date));
		// $startDate = date('Y-m-d H:i:s', strtotime($startDate . " -5 hours"));
		// $startDate = date('Y-m-d H:i:s', strtotime($startDate . " -30 minutes"));

		$endDate = date('Y-m-d', strtotime($request->end_date));
		// $endDate = date('Y-m-d 23:59:59', strtotime($request->end_date));
		// $endDate = date('Y-m-d H:i:s', strtotime($endDate . " -5 hours"));
		// $endDate = date('Y-m-d H:i:s', strtotime($endDate . " -30 minutes"));
		$query = Inquiry::query();
		// $query->select($selectColumns);
		$query->leftJoin('users as architect_user', 'architect_user.id', '=', 'inquiry.architect');
		$query->leftJoin('users as electrician_user', 'electrician_user.id', '=', 'inquiry.electrician');
		$query->groupBy('inquiry.source_type_value');
		// $query->where('inquiry.created_at', '>=', $startDate);
		// $query->where('inquiry.created_at', '<=', $endDate);

		if ($request->status != "0") {

			if ($request->status != "!10") {

				if ($request->status == "9,10,11") {

					$query->whereDate('inquiry.material_sent_date_time', '>=', $startDate);
					$query->whereDate('inquiry.material_sent_date_time', '<=', $endDate);
				} else {
					if ($request->status != "1,2,3,4,5,6,7,8" && $request->status != "!10") {

						$query->whereDate('inquiry.answer_date_time', '>=', $startDate);
						$query->whereDate('inquiry.answer_date_time', '<=', $endDate);
					}
				}
				$query->whereIn('inquiry.status', explode(",", $request->status));
			} else {
				$query->where('inquiry.is_point_calculated', 0);
				$query->whereIn('inquiry.status', array(9, 11));
				//$query->where('inquiry.billing_invoice', '');

				$query->where(function ($query2) {

					$query2->where(function ($query3) {

						$query3->where('inquiry.source_type', "user-202");
						$query3->orWhere('inquiry.source_type', "user-302");
					});

					$query2->orWhere(function ($query3) {

						$query3->where('inquiry.source_type_1', "user-202");
						$query3->orWhere('inquiry.source_type_1', "user-302");
					});

					$query2->orWhere(function ($query3) {

						$query3->where('inquiry.source_type_2', "user-202");
						$query3->orWhere('inquiry.source_type_2', "user-302");
					});

					$query2->orWhere(function ($query3) {

						$query3->where('inquiry.source_type_3', "user-202");
						$query3->orWhere('inquiry.source_type_3', "user-302");
					});

					$query2->orWhere(function ($query3) {

						$query3->where('inquiry.source_type_4', "user-202");
						$query3->orWhere('inquiry.source_type_4', "user-302");
					});

					$query2->orWhere(function ($query3) {

						$query3->where(function ($query4) {

							$query4->where('inquiry.architect', '!=', 0);
							$query4->where('architect_user.type', 202);
						});

						$query3->orWhere(function ($query4) {

							$query4->where('inquiry.electrician', '!=', 0);
							$query4->where('electrician_user.type', 302);
						});
					});
				});
			}

			if ($request->status == "102" && $request->client_product != 0) {

				$query->whereIn('inquiry.id', $inquiryClientProduct);
			}
		} else {
			if ($request->status != "1,2,3,4,5,6,7,8" && $request->status != "!10") {

				$query->whereDate('inquiry.created_at', '>=', $startDate);
				$query->whereDate('inquiry.created_at', '<=', $endDate);
			}
		}

		$query->where('inquiry.source_type', $request->source_type);
		if (isset($request->source_user) && $request->source_user != "") {
			$query->where('inquiry.source_type_value', $request->source_user);
		}

		if ($isSalePerson == 1) {

			$query->whereIn('inquiry.assigned_to', $childSalePersonsIds);
		}
		$query->whereNotIn('inquiry.assigned_to', getInquiryTransferToLeadUserList());
		$recordsTotal = $query->count();

		$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.
		$query = Inquiry::query();
		// $query->select('inquiry.*');
		$query->leftJoin('users as architect_user', 'architect_user.id', '=', 'inquiry.architect');
		$query->leftJoin('users as electrician_user', 'electrician_user.id', '=', 'inquiry.electrician');
		$query->leftJoin('users', 'users.id', '=', 'inquiry.source_type_lable');
		$query->groupBy('inquiry.source_type_value');
		// $query->where('inquiry.created_at', '>=', $startDate);
		// $query->where('inquiry.created_at', '<=', $endDate);
		if ($request->status != "0") {

			if ($request->status != "!10") {

				if ($request->status == "9,10,11") {

					$query->whereDate('inquiry.material_sent_date_time', '>=', $startDate);
					$query->whereDate('inquiry.material_sent_date_time', '<=', $endDate);
				} else {
					if ($request->status != "1,2,3,4,5,6,7,8" && $request->status != "!10") {

						$query->whereDate('inquiry.answer_date_time', '>=', $startDate);
						$query->whereDate('inquiry.answer_date_time', '<=', $endDate);
					}
				}
				$query->whereIn('inquiry.status', explode(",", $request->status));
			} else {
				$query->where('inquiry.is_point_calculated', 0);
				$query->whereIn('inquiry.status', array(9, 11));
				//$query->where('inquiry.billing_invoice', '');

				$query->where(function ($query2) {

					$query2->where(function ($query3) {

						$query3->where('inquiry.source_type', "user-202");
						$query3->orWhere('inquiry.source_type', "user-302");
					});

					$query2->orWhere(function ($query3) {

						$query3->where('inquiry.source_type_1', "user-202");
						$query3->orWhere('inquiry.source_type_1', "user-302");
					});

					$query2->orWhere(function ($query3) {

						$query3->where('inquiry.source_type_2', "user-202");
						$query3->orWhere('inquiry.source_type_2', "user-302");
					});

					$query2->orWhere(function ($query3) {

						$query3->where('inquiry.source_type_3', "user-202");
						$query3->orWhere('inquiry.source_type_3', "user-302");
					});

					$query2->orWhere(function ($query3) {

						$query3->where('inquiry.source_type_4', "user-202");
						$query3->orWhere('inquiry.source_type_4', "user-302");
					});

					$query2->orWhere(function ($query3) {

						$query3->where(function ($query4) {

							$query4->where('inquiry.architect', '!=', 0);
							$query4->where('architect_user.type', 202);
						});

						$query3->orWhere(function ($query4) {

							$query4->where('inquiry.electrician', '!=', 0);
							$query4->where('electrician_user.type', 302);
						});
					});
				});
			}
		} else {
			if ($request->status != "1,2,3,4,5,6,7,8" && $request->status != "!10") {

				$query->whereDate('inquiry.created_at', '>=', $startDate);
				$query->whereDate('inquiry.created_at', '<=', $endDate);
			}
		}
		$query->where('inquiry.source_type', $request->source_type);
		$query->where('inquiry.assigned_to', $request->sales_user_id);
		if ($isSalePerson == 1) {

			$query->whereIn('inquiry.assigned_to', $childSalePersonsIds);
		}
		if (isset($request->source_user) && $request->source_user != "") {
			$query->where('inquiry.source_type_value', $request->source_user);
		}

		//$query->where('inquiry.assigned_to', $request->assigned_to);
		// $query->leftJoin('users as sale_person', 'architect.sale_person_id', '=', 'sale_person.id');
		// $query->where('architect.type', $request->type);
		// if ($isSalePerson == 1) {
		// 	$query->whereIn('architect.sale_person_id', $SalePersonsIds);
		// }
		$query->select($selectColumns);
		// $query->limit($request->length);
		// $query->offset($request->start);
		//$query->orderBy($sortingColumns[$request['order'][0]['column']], $request['order'][0]['dir']);

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
		$query->whereNotIn('inquiry.assigned_to', getInquiryTransferToLeadUserList());
		$data = $query->get();
		$data = json_decode(json_encode($data), true);
		if ($isFilterApply == 1) {
			$recordsFiltered = count($data);
		}

		$userIds = array();
		$userIds[] = 0;

		foreach ($data as $key => $value) {
			$userIds[] = $value['source_type_value'];
		}

		$Users = User::select('users.id', 'users.first_name', 'users.last_name', 'channel_partner.firm_name')->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id')->whereIn('users.id', $userIds)->get();
		$UserReffeance = array();
		foreach ($Users as $key => $value) {

			if (isset($value->firm_name) && $value->firm_name != "") {
				$UserReffeance[$value->id] = $value->firm_name;
			} else {
				$UserReffeance[$value->id] = $value->first_name . " " . $value->last_name;
			}
		}

		$viewData = array();
		$sourceType = $request->source_type;
		$sourceTypePieces = explode("-", $sourceType);

		$sourceTypeName = "";

		foreach ($sourceTypes as $key => $value) {

			if ($value['id'] == $sourceTypePieces[1]) {
				$sourceTypeName = $value['lable'];
			}
		}
		$totalInquiry = 0;

		foreach ($data as $key => $value) {

			$source = isset($UserReffeance[$value['source_type_value']]) ? $UserReffeance[$value['source_type_value']] : $sourceTypeName;

			$viewData[$key]['source'] = $source;
			$viewData[$key]['inquiry_count'] = $value['inquiry_count'];
			$totalInquiry = $totalInquiry + $value['inquiry_count'];
		}

		$noOFRecord = count($viewData);
		$viewData[$noOFRecord]['source'] = "Total";
		$viewData[$noOFRecord]['inquiry_count'] = $totalInquiry;

		$jsonData = array(
			"draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
			"recordsTotal" => intval($recordsTotal), // total number of records
			"recordsFiltered" => intval($recordsFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
			"data" => $viewData, // total data array

		);
		return $jsonData;
	}

	public function download(Request $request)
	{

		$isSalePerson = isSalePerson();
		if ($isSalePerson == 1) {
			$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
		}

		if ($request->status == "102" && $request->client_product != 0) {

			$inquiryClientProduct = array();

			$InquiryQuestionAnswer = InquiryQuestionAnswer::select('inquiry_id')->where('inquiry_question_id', 1061)->where('answer', $request->client_product)->get();
			foreach ($InquiryQuestionAnswer as $keyQ => $valueQ) {
				$inquiryClientProduct[] = $valueQ->inquiry_id;
			}
		}

		$startDate = date('Y-m-d', strtotime($request->start_date));
		// $startDate = date('Y-m-d 00:00:00', strtotime($request->start_date));
		// $startDate = date('Y-m-d H:i:s', strtotime($startDate . " -5 hours"));
		// $startDate = date('Y-m-d H:i:s', strtotime($startDate . " -30 minutes"));

		$endDate = date('Y-m-d', strtotime($request->end_date));
		// $endDate = date('Y-m-d 23:59:59', strtotime($request->end_date));
		// $endDate = date('Y-m-d H:i:s', strtotime($endDate . " -5 hours"));
		// $endDate = date('Y-m-d H:i:s', strtotime($endDate . " -30 minutes"));

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
			'inquiry.follow_up_type',
			'inquiry.follow_up_date_time',
			'users.first_name as source_first_name',
			'users.last_name as source_last_name',
			'channel_partner.firm_name as source_firm_name',
			'users.phone_number as source_phone_number',

		);
		$query = Inquiry::query();
		// $query->select('inquiry.*');
		
		$query->leftJoin('users as architect_user', 'architect_user.id', '=', 'inquiry.architect');
		$query->leftJoin('users as electrician_user', 'electrician_user.id', '=', 'inquiry.electrician');
		$query->leftJoin('users', 'users.id', '=', 'inquiry.source_type_value');
		$query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
		$query->leftJoin('users as assigne', 'assigne.id', '=', 'inquiry.assigned_to');
		// $query->where('inquiry.created_at', '>=', $startDate);
		// $query->where('inquiry.created_at', '<=', $endDate);
		if ($request->status != "0") {

			if ($request->status != "!10") {

				if ($request->status == "9,10,11") {

					$query->whereDate('inquiry.material_sent_date_time', '>=', $startDate);
					$query->whereDate('inquiry.material_sent_date_time', '<=', $endDate);
				} else {

					if ($request->status != "1,2,3,4,5,6,7,8" && $request->status != "!10") {

						$query->whereDate('inquiry.answer_date_time', '>=', $startDate);
						$query->whereDate('inquiry.answer_date_time', '<=', $endDate);
					}
				}
				$query->whereIn('inquiry.status', explode(",", $request->status));
			} else {
				$query->where('inquiry.is_point_calculated', 0);
				$query->whereIn('inquiry.status', array(9, 11));
				//$query->where('inquiry.billing_invoice', '');

				$query->where(function ($query2) {

					$query2->where(function ($query3) {

						$query3->where('inquiry.source_type', "user-202");
						$query3->orWhere('inquiry.source_type', "user-302");
					});

					$query2->orWhere(function ($query3) {

						$query3->where('inquiry.source_type_1', "user-202");
						$query3->orWhere('inquiry.source_type_1', "user-302");
					});

					$query2->orWhere(function ($query3) {

						$query3->where('inquiry.source_type_2', "user-202");
						$query3->orWhere('inquiry.source_type_2', "user-302");
					});

					$query2->orWhere(function ($query3) {

						$query3->where('inquiry.source_type_3', "user-202");
						$query3->orWhere('inquiry.source_type_3', "user-302");
					});

					$query2->orWhere(function ($query3) {

						$query3->where('inquiry.source_type_4', "user-202");
						$query3->orWhere('inquiry.source_type_4', "user-302");
					});

					$query2->orWhere(function ($query3) {

						$query3->where(function ($query4) {

							$query4->where('inquiry.architect', '!=', 0);
							$query4->where('architect_user.type', 202);
						});

						$query3->orWhere(function ($query4) {

							$query4->where('inquiry.electrician', '!=', 0);
							$query4->where('electrician_user.type', 302);
						});
					});
				});
			}

			if ($request->status == "102" && $request->client_product != 0) {

				$query->whereIn('inquiry.id', $inquiryClientProduct);
			}
		} else {

			if ($request->status != "1,2,3,4,5,6,7,8" && $request->status != "!10") {

				$query->whereDate('inquiry.created_at', '>=', $startDate);
				$query->whereDate('inquiry.created_at', '<=', $endDate);
			}
		}
		if ($isSalePerson == 1) {
			$query->whereIn('inquiry.assigned_to', $childSalePersonsIds);
		}
		if (isset($request->sales_user_id) && $request->sales_user_id != 0) {
			$query->where('inquiry.assigned_to', $request->sales_user_id);
		}
		if (isset($request->source_type) && $request->source_type != "0-0") {
			$query->where('inquiry.source_type', $request->source_type);
		}
		if (isset($request->source_user) && $request->source_user != 0) {
			$query->where('inquiry.source_type_value', $request->source_user);
		}

		$query->select($selectColumns);
		$query->selectRaw('CONCAT(assigne.first_name," ",assigne.last_name) as owner');
		$query->whereNotIn('inquiry.assigned_to', getInquiryTransferToLeadUserList());
		$data = $query->get();

		$headers = array(
			'#id',
			"first name",
			"last name",
			"Phone Number",
			"Hours No",
			"Building/Society Name",
			"Area",
			"Pincode",
			"City",
			"Status",
			"Source Type",
			"Source Name",
			"Source Phone Number",
			"Follow Up Type",
			"Follow Up Date & Time",
			"Inquiry Owner",

		);

		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="reports.csv"');
		$fp = fopen('php://output', 'wb');

		fputcsv($fp, $headers);
		$inquiryStatus = getInquiryStatus();

		foreach ($data as $key => $value) {

			$piecesOfSourceType = explode("-", $value['source_type']);

			if ($piecesOfSourceType[0] == "user") {

				$isChannelPartner = isChannelPartner($piecesOfSourceType[1]);

				if ($isChannelPartner != 0) {

					$source = $value['source_firm_name'];
				} else {

					$source = $value['source_first_name'] . " " . $value['source_last_name'];
				}
			} else {

				$source = $value['source_type_value'];
			}

			$cityName = getCityName($value->city_id);
			$status = $inquiryStatus[$value->status]['name'];

			$lineVal = array(
				$value->id,
				$value->first_name,
				$value->last_name,
				$value->phone_number,
				$value->house_no,
				$value->society_name,
				$value->area,
				$value->pincode,
				$cityName,
				$status,
				$value->source_type_lable,
				$source,
				$value->source_phone_number,
				$value->follow_up_type,
				convertDateTime($value->follow_up_date_time),
				$value->owner

			);

			fputcsv($fp, $lineVal, ",");
		}
		fclose($fp);
	}

	public function inquiryList(Request $request)
	{

		$isSalePerson = isSalePerson();
		if ($isSalePerson == 1) {
			$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
		}

		if ($request->status == "102" && $request->client_product != 0) {

			$inquiryClientProduct = array();

			$InquiryQuestionAnswer = InquiryQuestionAnswer::select('inquiry_id')->where('inquiry_question_id', 1061)->where('answer', $request->client_product)->get();
			foreach ($InquiryQuestionAnswer as $keyQ => $valueQ) {
				$inquiryClientProduct[] = $valueQ->inquiry_id;
			}
		}

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

		// $isSalePerson = isSalePerson();
		
		// if ($isSalePerson == 1) {
		// 	$SalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
		// }

		$startDate = date('Y-m-d', strtotime($request->start_date));
		// $startDate = date('Y-m-d 00:00:00', strtotime($request->start_date));
		// $startDate = date('Y-m-d H:i:s', strtotime($startDate . " -5 hours"));
		// $startDate = date('Y-m-d H:i:s', strtotime($startDate . " -30 minutes"));
		
		$endDate = date('Y-m-d', strtotime($request->end_date));
		// $endDate = date('Y-m-d 23:59:59', strtotime($request->end_date));
		// $endDate = date('Y-m-d H:i:s', strtotime($endDate . " -5 hours"));
		// $endDate = date('Y-m-d H:i:s', strtotime($endDate . " -30 minutes"));
		$query = Inquiry::query();
		$query->select('inquiry.*');
		$query->leftJoin('users as architect_user', 'architect_user.id', '=', 'inquiry.architect');
		$query->leftJoin('users as electrician_user', 'electrician_user.id', '=', 'inquiry.electrician');
		// $query->where('inquiry.created_at', '>=', $startDate);
		// $query->where('inquiry.created_at', '<=', $endDate);

		if ($request->status != "0") {

			if ($request->status != "!10") {

				if ($request->status == "9,10,11") {

					$query->whereDate('inquiry.material_sent_date_time', '>=', $startDate);
					$query->whereDate('inquiry.material_sent_date_time', '<=', $endDate);
				} else {

					if ($request->status != "1,2,3,4,5,6,7,8" && $request->status != "!10") {

						$query->whereDate('inquiry.answer_date_time', '>=', $startDate);
						$query->whereDate('inquiry.answer_date_time', '<=', $endDate);
					}
				}

				$query->whereIn('inquiry.status', explode(",", $request->status));
			} else {
				$query->where('inquiry.is_point_calculated', 0);
				$query->whereIn('inquiry.status', array(9, 11));
				//$query->where('inquiry.billing_invoice', '');

				$query->where(function ($query2) {

					$query2->where(function ($query3) {

						$query3->where('inquiry.source_type', "user-202");
						$query3->orWhere('inquiry.source_type', "user-302");
					});

					$query2->orWhere(function ($query3) {

						$query3->where('inquiry.source_type_1', "user-202");
						$query3->orWhere('inquiry.source_type_1', "user-302");
					});

					$query2->orWhere(function ($query3) {

						$query3->where('inquiry.source_type_2', "user-202");
						$query3->orWhere('inquiry.source_type_2', "user-302");
					});

					$query2->orWhere(function ($query3) {

						$query3->where('inquiry.source_type_3', "user-202");
						$query3->orWhere('inquiry.source_type_3', "user-302");
					});

					$query2->orWhere(function ($query3) {

						$query3->where('inquiry.source_type_4', "user-202");
						$query3->orWhere('inquiry.source_type_4', "user-302");
					});

					$query2->orWhere(function ($query3) {

						$query3->where(function ($query4) {

							$query4->where('inquiry.architect', '!=', 0);
							$query4->where('architect_user.type', 202);
						});

						$query3->orWhere(function ($query4) {

							$query4->where('inquiry.electrician', '!=', 0);
							$query4->where('electrician_user.type', 302);
						});
					});
				});
			}

			if ($request->status == "102" && $request->client_product != 0) {

				$query->whereIn('inquiry.id', $inquiryClientProduct);
			}
		} else {
			if ($request->status != "1,2,3,4,5,6,7,8" && $request->status != "!10") {

				$query->whereDate('inquiry.created_at', '>=', $startDate);
				$query->whereDate('inquiry.created_at', '<=', $endDate);
			}
		}
		if (isset($request->sales_user_id) && $request->sales_user_id != 0) {

			$query->where('inquiry.assigned_to', $request->sales_user_id);
		}
		if (isset($request->source_type) && $request->source_type != "0-0") {
			$query->where('inquiry.source_type', $request->source_type);
		}
		if (isset($request->source_user) && $request->source_user != 0) {
			$query->where('inquiry.source_type_value', $request->source_user);
		}

		if ($isSalePerson == 1) {

			$query->whereIn('inquiry.assigned_to', $childSalePersonsIds);
		}
		$query->whereNotIn('inquiry.assigned_to', getInquiryTransferToLeadUserList());
		$recordsTotal = $query->count();
		$quotationTotal = $query->sum('quotation_amount');

		$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.
		$query = Inquiry::query();
		$query->select('inquiry.*');
		$query->leftJoin('users as architect_user', 'architect_user.id', '=', 'inquiry.architect');
		$query->leftJoin('users as electrician_user', 'electrician_user.id', '=', 'inquiry.electrician');
		// $query->where('inquiry.created_at', '>=', $startDate);
		// $query->where('inquiry.created_at', '<=', $endDate);

		if ($request->status != "0") {

			if ($request->status != "!10") {

				if ($request->status == "9,10,11") {

					$query->whereDate('inquiry.material_sent_date_time', '>=', $startDate);
					$query->whereDate('inquiry.material_sent_date_time', '<=', $endDate);
				} else {
					if ($request->status != "1,2,3,4,5,6,7,8" && $request->status != "!10") {

						$query->whereDate('inquiry.answer_date_time', '>=', $startDate);
						$query->whereDate('inquiry.answer_date_time', '<=', $endDate);
					}
				}
				$query->whereIn('inquiry.status', explode(",", $request->status));
			} else {
				$query->where('inquiry.is_point_calculated', 0);
				$query->whereIn('inquiry.status', array(9, 11));
				//$query->where('inquiry.billing_invoice', '');

				$query->where(function ($query2) {

					$query2->where(function ($query3) {

						$query3->where('inquiry.source_type', "user-202");
						$query3->orWhere('inquiry.source_type', "user-302");
					});

					$query2->orWhere(function ($query3) {

						$query3->where('inquiry.source_type_1', "user-202");
						$query3->orWhere('inquiry.source_type_1', "user-302");
					});

					$query2->orWhere(function ($query3) {

						$query3->where('inquiry.source_type_2', "user-202");
						$query3->orWhere('inquiry.source_type_2', "user-302");
					});

					$query2->orWhere(function ($query3) {

						$query3->where('inquiry.source_type_3', "user-202");
						$query3->orWhere('inquiry.source_type_3', "user-302");
					});

					$query2->orWhere(function ($query3) {

						$query3->where('inquiry.source_type_4', "user-202");
						$query3->orWhere('inquiry.source_type_4', "user-302");
					});

					$query2->orWhere(function ($query3) {

						$query3->where(function ($query4) {

							$query4->where('inquiry.architect', '!=', 0);
							$query4->where('architect_user.type', 202);
						});

						$query3->orWhere(function ($query4) {

							$query4->where('inquiry.electrician', '!=', 0);
							$query4->where('electrician_user.type', 302);
						});
					});
				});
			}

			if ($request->status == "102" && $request->client_product != 0) {

				$query->whereIn('inquiry.id', $inquiryClientProduct);
			}
		} else {
			if ($request->status != "1,2,3,4,5,6,7,8" && $request->status != "!10") {

				$query->whereDate('inquiry.created_at', '>=', $startDate);
				$query->whereDate('inquiry.created_at', '<=', $endDate);
			}
		}

		$query->leftJoin('users', 'users.id', '=', 'inquiry.source_type_value');
		$query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
		if (isset($request->sales_user_id) && $request->sales_user_id != 0) {

			$query->where('inquiry.assigned_to', $request->sales_user_id);
		}
		if (isset($request->source_type) && $request->source_type != "0-0") {
			$query->where('inquiry.source_type', $request->source_type);
		}
		if (isset($request->source_user) && $request->source_user != 0) {
			$query->where('inquiry.source_type_value', $request->source_user);
		}
		if ($isSalePerson == 1) {
			$query->whereIn('inquiry.assigned_to', $childSalePersonsIds);
		}
		$query->select($selectColumns);
		$query->limit($request->length);
		$query->offset($request->start);
		$query->orderBy($sortingColumns[$request['order'][0]['column']], $request['order'][0]['dir']);
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
		$query->whereNotIn('inquiry.assigned_to', getInquiryTransferToLeadUserList());
		$data = $query->get();

		$data = json_decode(json_encode($data), true);
		if ($isFilterApply == 1) {
			$recordsFiltered = count($data);
		}

		$viewData = array();
		$inquiryStatus = getInquiryStatus();

		foreach ($data as $key => $value) {

			$valueAnserDateTime = convertDateTime($value['answer_date_time']);

			$viewData[$key]['id'] = highlightString($value['id'],$search_value);
			$viewData[$key]['name'] = '<p class="text-muted mb-0" data-bs-toggle="tooltip"  data-bs-original-title="' . $value['first_name'] . " " . $value['last_name'] . '"><a target="_blank" href="' . route('inquiry') . '?status=0&inquiry_id=' . $value['id'] . '" >' . highlightString(displayStringLenth($value['first_name'] . " " . $value['last_name'], 20),$search_value) . '</a></p>';
			//$viewData[$key]['last_name'] = $value['last_name'];
			$viewData[$key]['phone_number'] = highlightString($value['phone_number'],$search_value);
			$viewData[$key]['address'] = '<p class="text-muted mb-0" data-bs-toggle="tooltip"  data-bs-original-title="' . $value['house_no'] . " " . $value['society_name'] . " " . $value['area'] . " " . $value['pincode'] . '">' . highlightString(displayStringLenth($value['house_no'] . " " . $value['society_name'] . " " . $value['area'] . " " . $value['pincode'], 40),$search_value) . '</p>';

			$statusLable = highlightString($inquiryStatus[$value['status']]['name'],$search_value);

			$uiAction = '<ul class="list-inline contact-links mb-0">';

			$uiAction .= '<li class="list-inline-item ">';
			$uiAction .= '<a data-bs-toggle="tooltip" href="javascript: void(0);" title="Status Update Date & Time : ' . $valueAnserDateTime . '"><i class="bx bx-calendar"></i></a>';

			$uiAction .= '<li class="list-inline-item ">';
			$uiAction .= '<a  href="javascript: void(0);" title="Edit">' . $statusLable . '</a>';
			$uiAction .= '</li>';
			$uiAction .= '</ul>';
			$viewData[$key]['status'] = $uiAction;

			$piecesOfSourceType = explode("-", $value['source_type']);
			if ($piecesOfSourceType[0] == "user") {

				$isChannelPartner = isChannelPartner($piecesOfSourceType[1]);

				if ($isChannelPartner != 0) {

					$viewData[$key]['source'] = '<p class="text-muted mb-0" data-bs-toggle="tooltip"  data-bs-original-title="' . $value['source_firm_name'] . '"> ' . highlightString(displayStringLenth($value['source_firm_name'], 20),$search_value) . '</p>';
				} else {

					$viewData[$key]['source'] = '<p class="text-muted mb-0" data-bs-toggle="tooltip"  data-bs-original-title="' . $value['source_first_name'] . " " . $value['source_last_name'] . '" >' . highlightString(displayStringLenth($value['source_first_name'] . " " . $value['source_last_name'], 20),$search_value) . '</p>';
				}
			} else {

				$viewData[$key]['source'] = '<p class="text-muted mb-0" data-bs-toggle="tooltip"  data-bs-original-title="' . $value['source_type_value'] . '" >' . highlightString(displayStringLenth($value['source_type_value'], 20),$search_value) . '</p>';
			}

			// $viewData[$key]['detail'] = "<a class='' target='_blank' href='" . route('inquiry') . "?status=0&inquiry_id=" . $value['id'] . "'> Detail</a>";

			$viewData[$key]['source_type'] = highlightString($value['source_type_lable'],$search_value);

			$viewData[$key]['quotation_amount'] = $value['quotation_amount'];
			if ($viewData[$key]['quotation_amount'] != "") {
				$viewData[$key]['quotation_amount'] = highlightString(priceLable($value['quotation_amount']),$search_value);
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
