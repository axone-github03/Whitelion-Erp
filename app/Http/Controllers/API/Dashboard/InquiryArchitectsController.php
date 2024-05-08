<?php

namespace App\Http\Controllers\API\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Architect;
use App\Models\Electrician;
use App\Models\Inquiry;
use App\Models\User;
use App\Models\Lead;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class InquiryArchitectsController extends Controller
{


	public function __construct()
	{

		$this->middleware(function ($request, $next) {

			$tabCanAccessBy = array(0, 1, 2);

			if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
				$response = errorRes("Invalid access", 401);
				return response()->json($response, $response['status_code'])->header('Content-Type', 'application/json');
			}

			$MyPrivilege = getMyPrivilege('dashboard');
			if ($MyPrivilege == 0) {
				$response = errorRes("Dashboard Can't Access", 402);
				return response()->json($response, $response['status_code'])->header('Content-Type', 'application/json');
			}

			return $next($request);

		});

	}

	function inquiryCount(Request $request)
	{
		$isSalePerson = isSalePerson();
		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();


		$rules = array();
		$rules['start_date'] = 'required';
		$rules['end_date'] = 'required';
		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			$response = array();
			$response['status'] = 0;
			$response['msg'] = "The request could not be understood by the server due to malformed syntax";
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();
		} else {

			if ($isSalePerson == 1) {
				$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
			}

			$startDate = date('Y-m-d', strtotime($request->start_date));

			$endDate = date('Y-m-d', strtotime($request->end_date));

			// INQUIRY & LEAD COUNT START
			$Inquiry = Inquiry::query();
			$Inquiry->whereDate('created_at', '>=', $startDate);
			$Inquiry->whereDate('created_at', '<=', $endDate);

			if ($isAdminOrCompanyAdmin == 1) {

				if (isset($request->user_id) && $request->user_id != "") {
					//$Inquiry->whereIn('user_id', $childSalePersonsIdOfUser);
					$Inquiry->where('assigned_to', $request->user_id);
				}
			} else if ($isSalePerson == 1) {

				if (isset($request->user_id) && $request->user_id != "" && in_array($request->user_id, $childSalePersonsIds)) {
					//$Inquiry->whereIn('user_id', $childSalePersonsIdOfUser);
					$Inquiry->where('assigned_to', $request->user_id);
				} else {
					//$Inquiry->whereIn('user_id', $childSalePersonsIds);
					$Inquiry->whereIn('assigned_to', $childSalePersonsIds);
				}
			}
			$Inquiry->whereNotIn('assigned_to', getInquiryTransferToLeadUserList());
			$InquiryCount = $Inquiry->count();

			// LEAD START
			$Lead = Lead::query();
			$Lead->whereDate('created_at', '>=', $startDate);
			$Lead->whereDate('created_at', '<=', $endDate);
			if ($isAdminOrCompanyAdmin == 1) {

				if (isset($request->user_id) && $request->user_id != "") {
					$Lead->where('assigned_to', $request->user_id);
				}
			} else if ($isSalePerson == 1) {

				if (isset($request->user_id) && $request->user_id != "" && in_array($request->user_id, $childSalePersonsIds)) {
					$Lead->where('assigned_to', $request->user_id);
				} else {
					$Lead->whereIn('assigned_to', $childSalePersonsIds);
				}
			}
			$LeadCount = $Lead->count();

			// LEAD END

			// INQUIRY & LEAD COUNT END

			// MATERIAL SENT COUNT START

			$InquiryCountMaterialSent = $Inquiry->count();

			$Inquiry = Inquiry::query();
			$Inquiry->whereDate('material_sent_date_time', '>=', $startDate);
			$Inquiry->whereDate('material_sent_date_time', '<=', $endDate);
			$Inquiry->whereIn('status', array(9, 11, 10));

			if ($isAdminOrCompanyAdmin == 1) {

				if (isset($request->user_id) && $request->user_id != "") {
					//$Inquiry->whereIn('user_id', $childSalePersonsIdOfUser);
					$Inquiry->where('assigned_to', $request->user_id);
				}

			} else if ($isSalePerson == 1) {

				if (isset($request->user_id) && $request->user_id != "" && in_array($request->user_id, $childSalePersonsIds)) {
					//$Inquiry->whereIn('user_id', $childSalePersonsIdOfUser);
					$Inquiry->where('assigned_to', $request->user_id);
				} else {
					$Inquiry->whereIn('assigned_to', $childSalePersonsIds);
					//$Inquiry->where('assigned_to', Auth::user()->id);
				}

			}
			$Inquiry->whereNotIn('assigned_to', getInquiryTransferToLeadUserList());
			$InquiryCountMaterialSent = $Inquiry->count();

			// LEAD START
			$Lead = Lead::query();
			$Lead->leftJoin('lead_status_updates as lead_status_detail', function ($join) {
				$join->select('lead_status_detail.new_status');
				$join->on('lead_status_detail.lead_id', '=', 'leads.id');
				$join->where('lead_status_detail.new_status', 103);
				$join->orderBy('lead_status_detail.created_at', 'DESC');
				$join->limit(1);
			});
			$Lead->whereDate('lead_status_detail.created_at', '>=', $startDate);
			$Lead->whereDate('lead_status_detail.created_at', '<=', $endDate);
			$Lead->whereIn('leads.status', array(103));
			if ($isAdminOrCompanyAdmin == 1) {

				if (isset($request->user_id) && $request->user_id != "") {
					$Lead->where('assigned_to', $request->user_id);
				}
			} else if ($isSalePerson == 1) {

				if (isset($request->user_id) && $request->user_id != "" && in_array($request->user_id, $childSalePersonsIds)) {
					$Lead->where('assigned_to', $request->user_id);
				} else {
					$Lead->whereIn('assigned_to', $childSalePersonsIds);
				}
			}
			$LeadCountMaterialSent = $Lead->count();
			// LEAD END

			// MATERIAL SENT COUNT END

			// REJECTED COUNT START
			$Inquiry = Inquiry::query();
			$Inquiry->where('answer_date_time', '>=', $startDate);
			$Inquiry->where('answer_date_time', '<=', $endDate);
			$Inquiry->where('status', 102);

			if ($isAdminOrCompanyAdmin == 1) {

				if (isset($request->user_id) && $request->user_id != "") {
					$Inquiry->where('assigned_to', $request->user_id);
				}

			} else if ($isSalePerson == 1) {

				if (isset($request->user_id) && $request->user_id != "" && in_array($request->user_id, $childSalePersonsIds)) {
					$Inquiry->where('assigned_to', $request->user_id);
				} else {
					$Inquiry->whereIn('assigned_to', $childSalePersonsIds);
				}

			}
			$Inquiry->whereNotIn('assigned_to', getInquiryTransferToLeadUserList());
			$InquiryCountRejected = $Inquiry->count();

			// LEAD START
			$Lead = Lead::query();
			$Lead->leftJoin('lead_status_updates as lead_status_detail', function ($join) {
				$join->select('lead_status_detail.new_status', 'lead_status_detail.created_at');
				$join->on('lead_status_detail.lead_id', '=', 'leads.id');
				$join->whereIn('lead_status_detail.new_status', array(5, 6, 104, 105));
				$join->orderBy('lead_status_detail.created_at', 'DESC');
				$join->limit(1);
			});
			$Lead->whereDate('lead_status_detail.created_at', '>=', $startDate);
			$Lead->whereDate('lead_status_detail.created_at', '<=', $endDate);
			// $Lead->whereIn('leads.status', array(5, 6, 104, 105));

			if ($isAdminOrCompanyAdmin == 1) {

				if (isset($request->user_id) && $request->user_id != "") {
					$Lead->where('assigned_to', $request->user_id);
				}
			} else if ($isSalePerson == 1) {

				if (isset($request->user_id) && $request->user_id != "" && in_array($request->user_id, $childSalePersonsIds)) {
					$Lead->where('assigned_to', $request->user_id);
				} else {
					$Lead->whereIn('assigned_to', $childSalePersonsIds);
				}
			}
			$LeadCountRejected = $Lead->count();
			// LEAD END

			// REJECTED COUNT EDN


			// NON-POTENTIAL COUNT START
			$Inquiry = Inquiry::query();
			$Inquiry->whereDate('answer_date_time', '>=', $startDate);
			$Inquiry->whereDate('answer_date_time', '<=', $endDate);
			$Inquiry->where('status', 101);

			if ($isAdminOrCompanyAdmin == 1) {

				if (isset($request->user_id) && $request->user_id != "") {
					//$Inquiry->whereIn('user_id', $childSalePersonsIdOfUser);
					$Inquiry->where('assigned_to', $request->user_id);
				}
			} else if ($isSalePerson == 1) {

				if (isset($request->user_id) && $request->user_id != "" && in_array($request->user_id, $childSalePersonsIds)) {
					//$Inquiry->whereIn('user_id', $childSalePersonsIdOfUser);
					$Inquiry->where('assigned_to', $request->user_id);
				} else {
					$Inquiry->whereIn('user_id', $childSalePersonsIds);
					//$Inquiry->where('assigned_to', Auth::user()->id);
				}
			}

			$InquiryCountNonPotential = $Inquiry->count();

			// LEAD START
			$Lead = Lead::query();
			$Lead->leftJoin('lead_status_updates as lead_status_detail', function ($join) {
				$join->select('lead_status_detail.new_status', 'lead_status_detail.created_at');
				$join->on('lead_status_detail.lead_id', '=', 'leads.id');
				$join->whereIn('lead_status_detail.new_status', array(6, 105));
				$join->orderBy('lead_status_detail.created_at', 'DESC');
				$join->limit(1);
			});
			$Lead->whereDate('lead_status_detail.created_at', '>=', $startDate);
			$Lead->whereDate('lead_status_detail.created_at', '<=', $endDate);
			// $Lead->whereIn('leads.status', array(5, 6, 104, 105));

			if ($isAdminOrCompanyAdmin == 1) {

				if (isset($request->user_id) && $request->user_id != "") {
					$Lead->where('assigned_to', $request->user_id);
				}
			} else if ($isSalePerson == 1) {

				if (isset($request->user_id) && $request->user_id != "" && in_array($request->user_id, $childSalePersonsIds)) {
					$Lead->where('assigned_to', $request->user_id);
				} else {
					$Lead->whereIn('assigned_to', $childSalePersonsIds);
				}
			}
			$LeadCountNonPotential = $Lead->count();
			// LEAD END
			
			// NON-POTENTIAL COUNT EDN

			$Architect = Architect::query();
			$Architect->whereDate('created_at', '>=', $startDate);
			$Architect->whereDate('created_at', '<=', $endDate);
			$Architect->whereIn('type', [201, 202]);

			if ($isAdminOrCompanyAdmin == 1) {

				if (isset($request->user_id) && $request->user_id != "") {
					//$Architect->whereIn('sale_person_id', $childSalePersonsIdOfUser);
					$Architect->where('sale_person_id', $request->user_id);
				}
			} else if ($isSalePerson == 1) {

				if (isset($request->user_id) && $request->user_id != "" && in_array($request->user_id, $childSalePersonsIds)) {

					//$Architect->whereIn('sale_person_id', $childSalePersonsIdOfUser);
					$Architect->where('sale_person_id', $request->user_id);
				} else {

					$Architect->whereIn('sale_person_id', $childSalePersonsIds);
					//$Architect->where('sale_person_id', Auth::user()->id);
				}
			}

			$ArchitectCount = $Architect->count();


			$Electrician = Electrician::query();
			$Electrician->whereDate('created_at', '>=', $startDate);
			$Electrician->whereDate('created_at', '<=', $endDate);
			$Electrician->whereIn('type', [301, 302]);

			if ($isAdminOrCompanyAdmin == 1) {

				if (isset($request->user_id) && $request->user_id != "") {
					//$Architect->whereIn('sale_person_id', $childSalePersonsIdOfUser);
					$Electrician->where('sale_person_id', $request->user_id);
				}
			} else if ($isSalePerson == 1) {

				if (isset($request->user_id) && $request->user_id != "" && in_array($request->user_id, $childSalePersonsIds)) {

					//$Architect->whereIn('sale_person_id', $childSalePersonsIdOfUser);
					$Electrician->where('sale_person_id', $request->user_id);
				} else {

					$Electrician->whereIn('sale_person_id', $childSalePersonsIds);
					//$Electrician->where('sale_person_id', Auth::user()->id);
				}
			}

			$ElectricianCount = $Electrician->count();

			


			$response = successRes("Get inquiry count");
			// $response['inquiry_count'] = $InquiryCount . ' + ' . $LeadCount . " = " . ($InquiryCount + $LeadCount);
			// $response['inquiry_material_sent'] = $InquiryCountMaterialSent . ' + ' . $LeadCountMaterialSent . " = " . ($InquiryCountMaterialSent + $LeadCountMaterialSent);
			// $response['inquiry_rejected'] = $InquiryCountRejected . ' + ' . $LeadCountRejected . " = " . ($InquiryCountRejected + $LeadCountRejected);
			// $response['inquiry_non_potential'] = $InquiryCountNonPotential . ' + ' . $LeadCountNonPotential . " = " . ($InquiryCountNonPotential + $LeadCountNonPotential);
			$response['inquiry_count'] = $LeadCount;
			$response['inquiry_material_sent'] = $LeadCountMaterialSent;
			$response['inquiry_rejected'] = $LeadCountRejected;
			$response['inquiry_non_potential'] = $LeadCountNonPotential;

			$response['non_prime_architects_count'] = $ArchitectCount;
			$response['prime_architects_count'] = $ArchitectCount;
			$response['architects_count'] = $ArchitectCount;

			$response['non_prime_electricians_count'] = $ElectricianCount;
			$response['prime_electricians_count'] = $ElectricianCount;
			$response['electricians_count'] = $ElectricianCount;

			$inquiry_conversion_ratio = 0;
			$lead_conversion_ratio = 0;

			if ($isSalePerson == 1) {
				$response['childsales'] = getChildSalePersonsIds(Auth::user()->id);
			}
			$totalInquiryCount = $InquiryCountMaterialSent + $InquiryCountRejected + $InquiryCountNonPotential;
			if ($totalInquiryCount != 0) {
				$inquiry_conversion_ratio = round((($InquiryCountMaterialSent * 100) / ($totalInquiryCount)), 2);
			}

			$totalLeadDealCount = $LeadCountMaterialSent + $LeadCountRejected + $LeadCountNonPotential;
			if ($totalLeadDealCount != 0) {
				$lead_conversion_ratio = round((($LeadCountMaterialSent * 100) / ($totalLeadDealCount)), 2);
			}

			// $response['conversion_ratio'] = $inquiry_conversion_ratio . ' / ' . $lead_conversion_ratio;
			$response['conversion_ratio'] = $lead_conversion_ratio;

		}
		return response()->json($response, $response['status_code'])->header('Content-Type', 'application/json');

	}

	public function searchUser(Request $request)
	{

		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		$isSalePerson = isSalePerson();

		if ($isSalePerson == 1) {
			$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);

		}

		$User = $UserResponse = array();
		$q = $request->q;
		$User = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
		$User->where('users.type', 2);
		if ($isSalePerson == 1) {

			$User->whereIn('id', $childSalePersonsIds);
		}
		$User->where('users.status', 1);
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
		$response = successRes("Sales Person");
		$response['data'] = $UserResponse;
		return response()->json($response, $response['status_code'])->header('Content-Type', 'application/json');

	}
}