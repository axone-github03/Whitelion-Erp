<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use App\Models\TeleSales;
use App\Models\User;
use App\Models\SalePerson;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;

class InquiryCalendarController extends Controller
{

	public function __construct()
	{
		$this->middleware(function ($request, $next) {
			$tabCanAccessBy = array(0, 1, 2, 9, 101, 102, 103, 104, 105);
			if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
				return redirect()->route('dashboard');
			}
			$MyPrivilege = getMyPrivilege('dashboard');
			if ($MyPrivilege == 0) {
				return redirect()->route('dashboard');
			}
			return $next($request);
		});
	}

	public function searchUser(Request $request)
	{

		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		$isSalePerson = isSalePerson();
		$isTaleSalesUser = isTaleSalesUser();
		$isChannelPartner = isChannelPartner(Auth::user()->type);

		if ($isAdminOrCompanyAdmin == 0 || $isSalePerson == 0) {

			if ($isSalePerson == 1) {
				$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
			}
			if ($isTaleSalesUser == 1) {
				$TeleSalesCity = TeleSalesCity(Auth::user()->id);
				$SalesPersonList = SalePerson::select('user_id')->whereIn('cities',$TeleSalesCity)->get();
				$SalesPersonList = Arr::pluck($SalesPersonList,'user_id');
			}

			$User = $UserResponse = array();
			$q = $request->q;
			$User = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));

			if ($isAdminOrCompanyAdmin == 1) {

				$User->whereIn('users.type', array(0, 1, 2));
			} else if ($isSalePerson == 1) {
				$User->where('users.type', 2);
				$User->whereIn('id', $childSalePersonsIds);
			} else if ($isTaleSalesUser == 1) {
				$User->where('users.type', 2);
				$User->whereIn('id', $SalesPersonList);
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
			$response = array();
			$response['results'] = $UserResponse;
			$response['pagination']['more'] = false;
			
			return response()->json($response)->header('Content-Type', 'application/json');
		}
	}

	public function calenderData(Request $request)
	{

		$startDate = $request->start;
		$endDate = $request->end;
		$startDate = explode("+", $startDate);
		$startDate = $startDate[0];
		$startDate = str_replace("T", " ", $startDate);

		$endDate = explode("+", $endDate);
		$endDate = $endDate[0];
		$endDate = str_replace("T", " ", $endDate);
		$assigned_to = Auth::user()->id;

		$isTaleSalesUser = isTaleSalesUser();
		$inquiryStatus = getInquiryStatus();

		$isAll = 0;
		if (isset($request->user_id) && $request->user_id != "null" && $request->user_id != "") {
			$assigned_to = $request->user_id;
			$isAll = 1;
		}

		if($isTaleSalesUser == 1){

			$teleSalesCity = TeleSales::query()->where('user_id',Auth::user()->id)->first();
			$lst_TeleSalesCity = array();
			if($teleSalesCity){
				$lst_TeleSalesCity = explode(",",$teleSalesCity->cities);
			}
			if($isAll == 1){

				$everntArray = Inquiry::select('id', 'first_name', 'last_name', 'assigned_to', 'follow_up_date_time', 'status', 'follow_up_type', 'closing_date_time')->whereIn('city_id', $lst_TeleSalesCity)->where('assigned_to', $assigned_to)->orWhere('inquiry.is_for_tele_sale',1)->where('closing_date_time', '<=', $endDate)->where('closing_date_time', '>=', $startDate)->orderBy('closing_date_time', 'asc')->get();
			}else{

				$everntArray = Inquiry::select('id', 'first_name', 'last_name', 'assigned_to', 'follow_up_date_time', 'status', 'follow_up_type', 'closing_date_time')->whereIn('city_id', $lst_TeleSalesCity)->orWhere('inquiry.is_for_tele_sale',1)->where('closing_date_time', '<=', $endDate)->where('closing_date_time', '>=', $startDate)->orderBy('closing_date_time', 'asc')->get();
			}
		}else{

			$everntArray = Inquiry::select('id', 'first_name', 'last_name', 'assigned_to', 'follow_up_date_time', 'status', 'follow_up_type', 'closing_date_time')->where('assigned_to', $assigned_to)->where('closing_date_time', '<=', $endDate)->where('closing_date_time', '>=', $startDate)->orderBy('closing_date_time', 'asc')->get();
		}


		$eventIndex = 0;

		$eventResponse = array();
		$eventResponseIndex = 0;
		foreach ($everntArray as $key => $value) {

			$eventResponse[$eventResponseIndex]['id'] = $value['id'];

			$eventResponse[$eventResponseIndex]['title'] = $value['follow_up_type'] . " - " . date('h:i A', strtotime($value->follow_up_date_time)) . " - Prediction \n #" . $value['id'] . " - " . $inquiryStatus[$value['status']]['name'] . " - " . $value['first_name'] . " " . $value['last_name'];
			//	$everntArray[$key]['title'] =  "(" . ($key + 1) . ") " . $value['follow_up_type'] . " - " . date('h:i A', strtotime($value->follow_up_date_time)) . " - FollowUp \n #" . $value['id'] . " - " . $inquiryStatus[$value['status']]['name'] . " - " . $value['first_name'] . " " . $value['last_name'];
			//$everntArray[$key]['title'] = "\n#" . $value['id'] . "\n" . $value['first_name'] . " " . $value['last_name'];

			$eventResponse[$eventResponseIndex]['description'] = $key + 1;
			$eventResponse[$eventResponseIndex]['allDay'] = true;

			$eventResponse[$eventResponseIndex]['start'] = strtotime($value->follow_up_date_time) * 1000 - (19800 * 1000);
			$eventResponse[$eventResponseIndex]['end'] = $everntArray[$key]['start'] + 1080000;
			$eventResponse[$eventResponseIndex]['index'] = $eventIndex + 1;
			$eventResponse[$eventResponseIndex]['className'] = "class-closing";
			$eventResponseIndex = $eventResponseIndex + 1;
		}

		$everntArray = Inquiry::select('id', 'first_name', 'last_name', 'assigned_to', 'follow_up_date_time', 'status', 'follow_up_type')->whereNotIn('status', array(9, 10, 11, 101, 102))->where('assigned_to', $assigned_to)->where('follow_up_date_time', '<=', $endDate)->where('follow_up_date_time', '>=', $startDate)->orderBy('follow_up_date_time', 'asc')->get();

		foreach ($everntArray as $key => $value) {

			$eventResponse[$eventResponseIndex]['id'] = $value['id'];
			$eventResponse[$eventResponseIndex]['title'] = $value['follow_up_type'] . " - " . date('h:i A', strtotime($value->follow_up_date_time)) . " - FollowUp \n #" . $value['id'] . " - " . $inquiryStatus[$value['status']]['name'] . " - " . $value['first_name'] . " " . $value['last_name'];
			//	$everntArray[$key]['title'] =  "(" . ($key + 1) . ") " . $value['follow_up_type'] . " - " . date('h:i A', strtotime($value->follow_up_date_time)) . " - FollowUp \n #" . $value['id'] . " - " . $inquiryStatus[$value['status']]['name'] . " - " . $value['first_name'] . " " . $value['last_name'];
			//$everntArray[$key]['title'] = "\n#" . $value['id'] . "\n" . $value['first_name'] . " " . $value['last_name'];

			$eventResponse[$eventResponseIndex]['description'] = $key + 1;
			$eventResponse[$eventResponseIndex]['allDay'] = true;

			$eventResponse[$eventResponseIndex]['start'] = strtotime($value->follow_up_date_time) * 1000 - (19800 * 1000);
			$eventResponse[$eventResponseIndex]['end'] = $everntArray[$key]['start'] + 1080000;
			$eventResponse[$eventResponseIndex]['index'] = $eventIndex + 1;
			$eventResponse[$eventResponseIndex]['className'] = "class-followup";
			$eventResponseIndex = $eventResponseIndex + 1;
		}

		// $everntArray = array();
		// $cEvents = count($everntArray);
		// $everntArray[$cEvents]['id'] = 1;
		// $everntArray[$cEvents]['title'] = "EventId -1";
		// $everntArray[$cEvents]['start'] = 1647339655892;

		// $cEvents = count($everntArray);
		// $everntArray[$cEvents]['id'] = 2;
		// $everntArray[$cEvents]['title'] = "EventId -2";
		// $everntArray[$cEvents]['start'] = 1647339665892;

		return response()->json($eventResponse)->header('Content-Type', 'application/json');
	}
}
