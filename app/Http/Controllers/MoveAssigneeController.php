<?php

namespace App\Http\Controllers;

use App\Models\Architect;
use App\Models\ChannelPartner;
use App\Models\Electrician;
use App\Models\Inquiry;
use App\Models\SalePerson;
use App\Models\User;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MoveAssigneeController extends Controller
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

	public function index(Request $request)
	{
		$data = array();
		$featureList = array();
		$cfeatureList = count($featureList);
		$featureList[$cfeatureList]['id'] = 1;
		$featureList[$cfeatureList]['name'] = "Inquiry";
		$cfeatureList = count($featureList);
		$featureList[$cfeatureList]['id'] = 2;
		$featureList[$cfeatureList]['name'] = "Architect";
		$cfeatureList = count($featureList);
		$featureList[$cfeatureList]['id'] = 3;
		$featureList[$cfeatureList]['name'] = "Electrician";

		$cfeatureList = count($featureList);
		$featureList[$cfeatureList]['id'] = 4;
		$featureList[$cfeatureList]['name'] = "Channel Partner";
		$data['title'] = "Move - Assignee";
		$data['feature_list'] = $featureList;

		return view('move_assignee/index', compact('data'));
	}

	public function searchAssignedUser(Request $request)
	{

		$fromUserType = 0;

		if ($request->user_id != "") {

			$SalePerson = SalePerson::select('type')->where('user_id', $request->user_id)->first();
			if ($SalePerson) {
				$fromUserType = $SalePerson->type;
			}
		}

		$User = $UserResponse = array();
		$q = $request->q;
		$User = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
		$User->leftJoin('sale_person', 'sale_person.id', '=', 'users.reference_id');
		$User->where('users.status', 1);
		$User->whereIn('users.type', array(2));
		if ($fromUserType != 0) {

			if ($request->is_channel_partner == "true") {

				$User->where('sale_person.type', $fromUserType);
			} else {

				// $childSalePersonsIds = getChildSalePersonsIds($request->user_id);
				// $User->whereIn('users.id', $childSalePersonsIds);

			}

			$User->where('users.id', "!=", $request->user_id);
		}

		$User->where(function ($query) use ($q) {
			$query->where('users.first_name', 'like', '%' . $q . '%');
			$query->orWhere('users.last_name', 'like', '%' . $q . '%');
		});
		$User->limit(5);
		$User = $User->get();

		if (count($User) > 0) {
			foreach ($User as $User_key => $User_value) {
				$UserResponse[$User_key]['id'] = $User_value['id'];
				$UserResponse[$User_key]['text'] = $User_value['full_name'];
			}
		}
		$response = array();
		$response['results'] = $UserResponse;
		$response['pagination']['more'] = false;
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function save(Request $request)
	{

		$validator = Validator::make($request->all(), [
			'from_assignee' => ['required'],
			'to_assignee' => ['required'],

		]);

		if ($validator->fails()) {

			$response = array();
			$response['status'] = 0;
			$response['msg'] = "The request could not be understood by the server due to malformed syntax";
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();
		} else {

			//check_feature_all_inqury

			if (isset($request->check_feature_1) && $request->check_feature_1 == "on" && isset($request->check_feature_all_inqury) && $request->check_feature_all_inqury == "on") {

				if ($request->inquiry_status == 0) {

					Inquiry::query()->where('assigned_to', $request->from_assignee)->update(['assigned_to' => $request->to_assignee]);
				} else {
					Inquiry::query()->where('assigned_to', $request->from_assignee)->where('status', $request->inquiry_status)->update(['assigned_to' => $request->to_assignee]);
				}
			} else if (isset($request->check_feature_1) && $request->check_feature_1 == "on") {

				if (isset($request->check_inqury) && is_array($request->check_inqury)) {

					$inquiryIds = array_keys($request->check_inqury);
					if (count($inquiryIds) > 0) {

						Inquiry::query()->where('assigned_to', $request->from_assignee)->where('id', $inquiryIds)->update(['assigned_to' => $request->to_assignee]);
					}
				}
			}
			// print_r($request->all());
			// die;

			if (isset($request->check_feature_2) && $request->check_feature_2 == "on") {

				Architect::query()->where('sale_person_id', $request->from_assignee)->update(['sale_person_id' => $request->to_assignee]);
			}
			if (isset($request->check_feature_3) && $request->check_feature_3 == "on") {

				Electrician::query()->where('sale_person_id', $request->from_assignee)->update(['sale_person_id' => $request->to_assignee]);
			}

			if (isset($request->check_feature_4) && $request->check_feature_4 == "on") {

				$fromAssignedTo = $request->from_assignee;

				$ChannelPartner = ChannelPartner::query();
				$ChannelPartner->where(function ($query2) use ($fromAssignedTo) {
					$query2->whereRaw('FIND_IN_SET("' . $fromAssignedTo . '",channel_partner.sale_persons)>0');
				});
				$ChannelPartner = $ChannelPartner->get();
				foreach ($ChannelPartner as $key => $value) {

					$salesPerson = explode(",", $value->sale_persons);
					$newSalesPerson = array();

					foreach ($salesPerson as $s) {

						if ($s == $fromAssignedTo) {
							$newSalesPerson[] = $request->to_assignee;
						} else {
							$newSalesPerson[] = $s;
						}
					}
					$newSalesPerson = implode(",", $newSalesPerson);
					$ChannelPartnerObject = ChannelPartner::find($value->id);
					$ChannelPartnerObject->sale_persons = $newSalesPerson;
					$ChannelPartnerObject->save();
				}
			}

			$response = successRes("Successfully moved assignee");
		}

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	function ajaxInquiry(Request $request)
	{

		$searchColumns = array(
			0 => 'inquiry.id',
			1 => 'inquiry.id',
			2 => 'inquiry.first_name',
			3 => 'inquiry.last_name',
			4 => 'inquiry.phone_number',

		);

		$sortingColumns = array(
			0 => 'inquiry.id',
			1 => 'inquiry.id',
			2 => 'inquiry.first_name',
			3 => 'inquiry.last_name',
			4 => 'inquiry.phone_number',

		);

		$selectColumns = array(
			'inquiry.id',
			'inquiry.first_name',
			'inquiry.last_name',
			'inquiry.phone_number',
			'inquiry.status',

		);

		$query = Inquiry::query();
		$query->where('inquiry.assigned_to', $request->assigned_to);
		if ($request->inquiry_status != 0) {
			$query->where('inquiry.status', $request->inquiry_status);
		}

		$recordsTotal = $query->count();
		$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.
		$query = Inquiry::query();
		$query->where('inquiry.assigned_to', $request->assigned_to);

		if ($request->inquiry_status != 0) {
			$query->where('inquiry.status', $request->inquiry_status);
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

		$data = $query->get();
		$data = json_decode(json_encode($data), true);
		if ($isFilterApply == 1) {
			$recordsFiltered = count($data);
		}

		$viewData = array();
		$inquiryStatus = getInquiryStatus();

		foreach ($data as $key => $value) {

			$viewData[$key] = array();
			$viewData[$key]['checkbox'] = '<input  class="checkbox_inquiry" type="checkbox" id="check_inqury_' . $value['id'] . '" name="check_inqury[' . $value['id'] . ']"
                                                                 >';

			// $viewData[$key]['checkbox'] = "";
			$viewData[$key]['id'] = highlightString($value['id'],$search_value);
			$viewData[$key]['name'] = highlightString($value['first_name'],$search_value);
			$viewData[$key]['phone_number'] = highlightString($value['phone_number'],$search_value);
			$viewData[$key]['status'] = highlightString(isset($inquiryStatus[$data[$key]['status']]['name']) ? $inquiryStatus[$data[$key]['status']]['name'] : '',$search_value);
		}

		$jsonData = array(
			"draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
			"recordsTotal" => intval($recordsTotal), // total number of records
			"recordsFiltered" => intval($recordsFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
			"data" => $viewData, // total data array

		);
		return $jsonData;
	}
}
