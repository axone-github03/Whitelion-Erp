<?php

namespace App\Http\Controllers\API\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Architect;
use App\Models\Electrician;
use App\Models\ChannelPartner;
use App\Models\User;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use DB;
use App\Models\Inquiry;

class DashboardController extends Controller
{

	public function index()
	{

		$MyPrivilege = getMyPrivilege('dashboard');

		if ($MyPrivilege == 1) {

			$userId = Auth::user()->id;
			$inquiryStatus = getInquiryStatus();

			if (Auth::user()->type == 202) {

				$response = successRes("Dashboard detail");
				architectInquiryCalculation(Auth::user()->id);
				$Architect = Architect::select('total_inquiry', 'total_site_completed', 'total_point', 'total_point_used', 'total_point_current')->where('user_id', Auth::user()->id)->first();
				$response['data'] = $Architect;

				$deal_count = Lead::query();
            	$deal_count->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
            	$deal_count->where(function ($deal_count) use ($Architect) {
            	    $deal_count->orwhere('leads.architect', Auth::user()->id);
            	    $deal_count->orwhere('lead_sources.source', Auth::user()->id);
            	});
            	$deal_count = $deal_count->count();
				$response['data']['total_inquiry'] = $deal_count;

				$wonLead = Lead::query();
				$wonLead->leftJoin('lead_status_updates as lead_status_detail', function ($join) {
					$join->select('lead_status_detail.new_status');
					$join->on('lead_status_detail.lead_id', '=', 'leads.id');
					$join->where('lead_status_detail.new_status', 103);
					$join->orderBy('lead_status_detail.created_at', 'DESC');
					$join->limit(1);
				});
				$wonLead->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
            	$wonLead->where(function ($wonLead) use ($Architect) {
            	    $wonLead->orwhere('leads.architect', Auth::user()->id);
            	    $wonLead->orwhere('lead_sources.source', Auth::user()->id);
            	});
				$response['data']['total_won_inquiry'] = $wonLead->distinct()->pluck('leads.id')->count();

				$lostLead = Lead::query();
				$lostLead->leftJoin('lead_status_updates as lead_status_detail', function ($join) {
					$join->select('lead_status_detail.new_status');
					$join->on('lead_status_detail.lead_id', '=', 'leads.id');
					$join->whereIn('lead_status_detail.new_status', array(5, 104));
					$join->orderBy('lead_status_detail.created_at', 'DESC');
					$join->limit(1);
				});
				$lostLead->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
            	$lostLead->where(function ($lostLead) use ($Architect) {
            	    $lostLead->orwhere('leads.architect', Auth::user()->id);
            	    $lostLead->orwhere('lead_sources.source', Auth::user()->id);
            	});
				$response['data']['total_lost_inquiry'] = $lostLead->distinct()->pluck('leads.id')->count();

				$runningLead = Lead::query();
				$runningLead->whereIn('status', array(1,2,3,4,100,101,102));
				$runningLead->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
            	$runningLead->where(function ($runningLead) use ($Architect) {
            	    $runningLead->orwhere('leads.architect', Auth::user()->id);
            	    $runningLead->orwhere('lead_sources.source', Auth::user()->id);
            	});
				$response['data']['total_running_inquiry'] = $runningLead->distinct()->pluck('leads.id')->count();

				$response['data']['lifetime_point'] = $Architect['total_point'];
            	$response['data']['redeemed_point'] = $Architect['total_point'] - $Architect['total_point_current'];
            	$response['data']['available_point'] = $Architect['total_point_current'];


			} else if (Auth::user()->type == 302) {

				$response = successRes("Dashboard detail");
				elecricianInquiryCalculation(Auth::user()->id);
				$Electrician = Electrician::select('total_inquiry', 'total_site_completed', 'total_point', 'total_point_used', 'total_point_current')->where('user_id', Auth::user()->id)->first();
				$response['data'] = $Electrician;

				$deal_count = Lead::query();
            	$deal_count->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
            	$deal_count->where(function ($deal_count) use ($Electrician) {
            	    $deal_count->orwhere('leads.architect', Auth::user()->id);
            	    $deal_count->orwhere('lead_sources.source', Auth::user()->id);
            	});
            	$deal_count = $deal_count->count();
				$response['data']['total_inquiry'] = $deal_count;

				$wonLead = Lead::query();
				$wonLead->leftJoin('lead_status_updates as lead_status_detail', function ($join) {
					$join->select('lead_status_detail.new_status');
					$join->on('lead_status_detail.lead_id', '=', 'leads.id');
					$join->where('lead_status_detail.new_status', 103);
					$join->orderBy('lead_status_detail.created_at', 'DESC');
					$join->limit(1);
				});
				$wonLead->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
            	$wonLead->where(function ($wonLead) use ($Electrician) {
            	    $wonLead->orwhere('leads.electrician', Auth::user()->id);
            	    $wonLead->orwhere('lead_sources.source', Auth::user()->id);
            	});
				$response['data']['total_won_inquiry'] = $wonLead->distinct()->pluck('leads.id')->count();

				$lostLead = Lead::query();
				$lostLead->leftJoin('lead_status_updates as lead_status_detail', function ($join) {
					$join->select('lead_status_detail.new_status');
					$join->on('lead_status_detail.lead_id', '=', 'leads.id');
					$join->whereIn('lead_status_detail.new_status', array(5, 104));
					$join->orderBy('lead_status_detail.created_at', 'DESC');
					$join->limit(1);
				});
				$lostLead->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
            	$lostLead->where(function ($lostLead) use ($Electrician) {
            	    $lostLead->orwhere('leads.electrician', Auth::user()->id);
            	    $lostLead->orwhere('lead_sources.source', Auth::user()->id);
            	});
				$response['data']['total_lost_inquiry'] = $lostLead->distinct()->pluck('leads.id')->count();

				$runningLead = Lead::query();
				$runningLead->whereIn('status', array(1,2,3,4,100,101,102));
				$runningLead->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
            	$runningLead->where(function ($runningLead) use ($Electrician) {
            	    $runningLead->orwhere('leads.electrician', Auth::user()->id);
            	    $runningLead->orwhere('lead_sources.source', Auth::user()->id);
            	});
				$response['data']['total_running_inquiry'] = $runningLead->distinct()->pluck('leads.id')->count();

				$response['data']['lifetime_point'] = $Electrician['total_point'];
            	$response['data']['redeemed_point'] = $Electrician['total_point'] - $Electrician['total_point_current'];
            	$response['data']['available_point'] = $Electrician['total_point_current'];

			} else {
				$response = errorRes("Invalid access", 402);
				return response()->json($response, $response['status_code'])->header('Content-Type', 'application/json');
			}
		} else {
			$response = errorRes("Invalid access", 402);
			return response()->json($response, $response['status_code'])->header('Content-Type', 'application/json');
		}

		return response()->json($response, $response['status_code'])->header('Content-Type', 'application/json');

	}

	public function doChangePassword(Request $request)
	{

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

		if ($validator->fails()) {

			$response = errorRes("Validation Error", 400);
			$response['data'] = $validator->errors();
			return response()->json($response, $response['status_code'])->header('Content-Type', 'application/json');

		} else {

			if (Auth::User()->is_changed_password == 1) {

				$current_password = Auth::User()->password;
				if (Hash::check($request->old_password, $current_password)) {

					if ($request->new_password == $request->confirm_password) {

						Auth::User()->is_changed_password = 1;
						Auth::User()->password = Hash::make($request->new_password);

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

				Auth::User()->is_changed_password = 1;
				Auth::User()->password = Hash::make($request->new_password);
				Auth::User()->save();

				$debugLog = array();
				$debugLog['name'] = "user-password";
				$debugLog['description'] = "user #" . Auth::user()->id . "(" . Auth::user()->email . ") has been updated password ";
				saveDebugLog($debugLog);
				$response = successRes("Successfully updated password");

			}

		}

		return response()->json($response)->header('Content-Type', 'application/json');

	}

	
}