<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Architect;
use App\Models\Electrician;
use App\Models\LeadStatusUpdate;
use App\Models\Lead;
use App\Models\User;
use App\Models\ChannelPartner;
use App\Models\Invoice;
use Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Mail;
use DB;

class DashboardController extends Controller
{

	public function index()
	{
		$data = array();
		$data['title'] = "Dashboard";
		$data['is_title_header'] = 1;
		$data['is_first_load'] = 1;

		$MyPrivilege = getMyPrivilege('dashboard');

		if ($MyPrivilege == 1) {

			if (Auth::user()->type == 0) {
				$previouosMonths = getPreviousMonths(12);
				$data['previous_months'] = $previouosMonths;
				return view('dashboard/admin', compact('data'));
			} else if (Auth::user()->type == 1) {
				$previouosMonths = getPreviousMonths(12);
				$data['previous_months'] = $previouosMonths;
				return view('dashboard/company_admin', compact('data'));
			} else if (Auth::user()->type == 2) {
				if(in_array(Auth::User()->id,[8017,8018,8019,8020])){
					return view('dashboard/index', compact('data'));
				}else{
					$previouosMonths = getPreviousMonths(12);
					$data['previous_months'] = $previouosMonths;
					return view('dashboard/sale_person', compact('data'));
				}
				
			} else if (Auth::user()->type == 3) {
				$previouosMonths = getPreviousMonths(12);
				$data['previous_months'] = $previouosMonths;
				return view('dashboard/account', compact('data'));
			} else if (Auth::user()->type == 9) {
				$previouosMonths = getPreviousMonths(12);
				$data['previous_months'] = $previouosMonths;
				return view('dashboard/tele_sale_person', compact('data'));
			} else if (Auth::user()->type == 202) {
				//architectInquiryCalculation(Auth::user()->id);
				$Architect = Architect::where('user_id', Auth::user()->id)->first();
				$data['architect'] = $Architect;

				$deal_count = Lead::query();
            	$deal_count->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
				$deal_count->where('leads.architect', Auth::user()->id);
				$deal_count->orwhere('lead_sources.source', Auth::user()->id);
            	$deal_count = $deal_count->count();

				$data['architect']['total_lead_deal'] = $deal_count;
				$data['architect']['lifetime_point'] = $Architect['total_point'];
            	$data['architect']['redeemed_point'] = $Architect['total_point'] - $Architect['total_point_current'];
            	$data['architect']['available_point'] = $Architect['total_point_current'];

				return view('dashboard/architect', compact('data'));
			} else if (Auth::user()->type == 302) {

				//elecricianInquiryCalculation(Auth::user()->id);
				$Electrician = Electrician::where('user_id', Auth::user()->id)->first();
				$data['electrician'] = $Electrician;

				$deal_count = Lead::query();
            	$deal_count->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
            	$deal_count->where(function ($deal_count) use ($Electrician) {
            	    $deal_count->orwhere('leads.electrician', Auth::user()->id);
            	    $deal_count->orwhere('lead_sources.source', Auth::user()->id);
            	});
            	$deal_count = $deal_count->count();

				$data['electrician']['total_lead_deal'] = $deal_count;
				$data['electrician']['lifetime_point'] = $Electrician['total_point'];
            	$data['electrician']['redeemed_point'] = $Electrician['total_point'] - $Electrician['total_point_current'];
            	$data['electrician']['available_point'] = $Electrician['total_point_current'];

				return view('dashboard/electrician', compact('data'));
			} else if (isChannelPartner(Auth::user()->type) != 0) {

				$previouosMonths = getPreviousMonths(12);
				$data['previous_months'] = $previouosMonths;
				return view('dashboard/channel_partner', compact('data'));
			}
		}
		return view('dashboard/index', compact('data'));
	}

	public function profile()
	{
		$data = array();
		$data['title'] = "Profile";
		return view('dashboard/profile', compact('data'));
	}

	public function changePassword()
	{
		$data = array();
		$data['title'] = "Change Password";
		return view('dashboard/changepassword', compact('data'));
	}

	public function doChangePassword(Request $request)
	{

		// $formType = isset($request->form_type) ? $request->form_type : 'form_otp';

		// if ($formType == "form_otp") {

		// 	$validator = Validator::make($request->all(), [
		// 		'one_time_password' => ['required'],

		// 	]);

		// } else if ($formType == "form_change_password") {

		if (Auth::User()->is_changed_password == 1) {
			$validator = Validator::make($request->all(), [
				'old_password' => ['required'],
				'new_password' => ['required'],
				'confirm_password' => ['required'],

			]);
		} else if (Auth::User()->is_changed_password == 0) {

			$validator = Validator::make($request->all(), [

				'new_password' => ['required'],
				'confirm_password' => ['required'],

			]);
		}

		//} else {
		//$validator = Validator::make($request->all(), []);
		//}

		{
			if ($validator->fails()) {

				$response = array();
				$response['status'] = 0;
				$response['msg'] = "The request could not be understood by the server due to malformed syntax";
				$response['statuscode'] = 400;
				$response['data'] = $validator->errors();
			} else {

				// if ($formType == "form_otp" && Auth::User()->is_changed_password == 0) {

				// 	$one_time_password = implode("", $request->one_time_password);

				// 	$user = User::find(Auth::User()->id);

				// 	if ($user->one_time_password != "" && $user->one_time_password == $one_time_password) {

				// 		$response = successRes("Valid one time password");

				// 	} else {

				// 		$response = errorRes("Invalid one time password");

				// 	}

				// } else if ($formType == "form_change_password" && Auth::User()->is_changed_password == 0) {

				// 	$one_time_password = implode("", $request->one_time_password);
				// 	$user = User::find(Auth::User()->id);
				// 	if ($user->one_time_password != "" && $user->one_time_password == $one_time_password) {

				// 		if ($request->new_password == $request->confirm_password) {

				// 			Auth::User()->is_changed_password = 1;
				// 			Auth::User()->password = Hash::make($request->new_password);

				// 			Auth::User()->save();

				// 			$debugLog = array();
				// 			$debugLog['name'] = "user-password";
				// 			$debugLog['description'] = "user #" . Auth::user()->id . "(" . Auth::user()->email . ") has been updated password ";
				// 			saveDebugLog($debugLog);

				// 			$response = successRes("Successfully updated password");

				// 		} else {
				// 			$response = errorRes("New password and Confirm password mismatch");
				// 		}

				// 	} else {

				// 		$response = errorRes("Invalid one time password");

				// 	}

				// } else if ($formType == "form_change_password" && Auth::User()->is_changed_password == 1) {

				if (Auth::User()->is_changed_password == 1) {

					$current_password = Auth::User()->password;
					if (Hash::check($request->old_password, $current_password)) {

						if ($request->new_password == $request->confirm_password) {

							Auth::User()->password = Hash::make($request->new_password);
							Auth::User()->is_changed_password = 1;
							Auth::User()->save();

							$debugLog = array();
							$debugLog['name'] = "user-password";
							$debugLog['description'] = "user #" . Auth::user()->id . "(" . Auth::user()->email . ") has been updated password ";
							saveDebugLog($debugLog);

							$response = successRes("Successfully updated password");
						} else {
							$response = errorRes("New password and Confirm password mismatch");
						}
					} else {
						$response = errorRes("Invalid old password");
					}
				} else {
					Auth::User()->password = Hash::make($request->new_password);
					Auth::User()->is_changed_password = 1;
					Auth::User()->save();
					$debugLog = array();
					$debugLog['name'] = "user-password";
					$debugLog['description'] = "user #" . Auth::user()->id . "(" . Auth::user()->email . ") has been updated password ";
					saveDebugLog($debugLog);
					$response = successRes("Successfully updated password");
				}

				// } else {
				// 	$response = errorRes("Something went wrong");
				// }

			}
		}

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	function sendOTPForChangePassword()
	{

		if (Auth::User()->is_changed_password == 0) {
			$one_time_password = (rand(1000, 9999));
			Auth::User()->one_time_password = $one_time_password;
			Auth::User()->save();
			$reciever_lable = array();
			$params = array();

			if (Auth::User()->type != 302) {

				$params['to_email'] = Auth::User()->email;

				$configrationForNotify = configrationForNotify();
				$params['from_name'] = $configrationForNotify['from_name'];
				$params['from_email'] = $configrationForNotify['from_email'];
				$params['to_name'] = $configrationForNotify['to_name'];
				$params['subject'] = "OTP (One Time Password) - Whitelion";
				$params['one_time_password'] = $one_time_password;

				if (Config::get('app.env') == "local") {
					$params['to_email'] = $configrationForNotify['test_email'];
				}
				Mail::send('emails.one_time_password', ['params' => $params], function ($m) use ($params) {
					$m->from($params['from_email'], $params['from_name']);
					$m->to($params['to_email'], $params['to_name'])->subject($params['subject']);
				});
				$reciever_lable[] = $params['to_email'];
			}

			$params['mobile_numer'] = Auth::User()->phone_number;
			if (Config::get('app.env') == "local") {
				//$params['mobile_numer'] = "9913834380";

			}

			$reciever_lable[] = $params['mobile_numer'];

			sendOTPToMobile($params['mobile_numer'], $one_time_password);
			$response = successRes("Successfully sent otp");
			$response['reciever_lable'] = implode(" and ", $reciever_lable);
		} else {

			$response = errorRes("Something went wrong");
		}
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function searchChannelPartner(Request $request)
	{

		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		$isSalePerson = isSalePerson();
		$isChannelPartner = isChannelPartner(Auth::user()->type);
		$isAccountUser = isAccountUser();
		if ($isAdminOrCompanyAdmin == 1 || $isSalePerson == 1 || $isAccountUser == 1) {

			$ChannelPartner = array();
			$ChannelPartner = ChannelPartner::select('channel_partner.user_id as id', DB::raw('CONCAT(channel_partner.firm_name) as text'));
			$ChannelPartner->leftJoin('users', 'users.id', '=', 'channel_partner.user_id');
			$ChannelPartner->whereIn('channel_partner.type', array(101, 102, 103, 104, 105));
			$ChannelPartner->where('users.status', 1);
			if ($request->type != 0) {

				$ChannelPartner->where('channel_partner.type', $request->type);
			}

			$q = $request->q;

			if ($isSalePerson == 1) {

				$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);

				$ChannelPartner->where(function ($query) use ($childSalePersonsIds) {

					foreach ($childSalePersonsIds as $key => $value) {
						if ($key == 0) {
							$query->whereRaw('FIND_IN_SET("' . $value . '",channel_partner.sale_persons)>0');
						} else {
							$query->orWhereRaw('FIND_IN_SET("' . $value . '",channel_partner.sale_persons)>0');
						}
					}
				});
			}

			$ChannelPartner->where(function ($query) use ($q) {
				$query->where('channel_partner.firm_name', 'like', '%' . $q . '%');
			});

			$ChannelPartner->limit(10);
			$ChannelPartner = $ChannelPartner->get();

			$response = array();
			$response['results'] = $ChannelPartner;
			$response['pagination']['more'] = false;
			return response()->json($response)->header('Content-Type', 'application/json');
		}
	}

	public function searchUser(Request $request)
	{

		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		$isSalePerson = isSalePerson();
		$isChannelPartner = isChannelPartner(Auth::user()->type);
		$isAccountUser = isAccountUser();
		if ($isAdminOrCompanyAdmin == 1 || $isSalePerson == 1 || $isAccountUser == 1) {

			if ($isSalePerson == 1) {
				$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
			}

			$User = $UserResponse = array();
			$q = $request->q;
			$User = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
			$User->where('users.type', 2);
			$User->where('users.status', 1);
			if ($isSalePerson == 1) {
				$User->whereIn('id', $childSalePersonsIds);
			}
			$User->where(function ($query) use ($q) {
				$query->where(DB::raw("CONCAT(users.first_name,' ',users.last_name)"), 'like', '%' . $q . '%');
				// $query->orWhere('users.last_name', 'like', '%' . $q . '%');
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
	}

}
