<?php

namespace App\Http\Controllers;
use App\Models\Architect;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class DashboardController extends Controller {

	public function index() {

		$data = array();
		$data['title'] = "Dashboard";

		if (Auth::user()->type == 0) {
			return view('dashboard/admin', compact('data'));
		} else if (Auth::user()->type == 2) {
			if(in_array(Auth::User()->id,[5592,8017,8018,8019])){
				return view('dashboard/index', compact('data'));
			}else{

				$previouosMonths = getPreviousMonths(12);
				$data['previous_months'] = $previouosMonths;
	
				return view('dashboard/sale_person', compact('data'));
			}
		} else if (Auth::user()->type == 202) {
			$Architect = Architect::where('user_id', Auth::user()->id)->first();
			$data['architect'] = $Architect;

			$deal_count = Lead::query();
            $deal_count->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
            $deal_count->where(function ($deal_count) use ($Architect) {
                $deal_count->orwhere('leads.architect', Auth::user()->id);
                $deal_count->orwhere('lead_sources.source', Auth::user()->id);
            });
            $deal_count = $deal_count->count();

			$data['architect']['total_lead_deal'] = $deal_count;
			$data['architect']['lifetime_point'] = $Architect['total_point'];
            $data['architect']['redeemed_point'] = $Architect['total_point'] - $Architect['total_point_current'];
            $data['architect']['available_point'] = $Architect['total_point_current'];

			return view('dashboard/architect', compact('data'));

		}
		return view('dashboard/index', compact('data'));

	}

	public function profile() {
		$data = array();
		$data['title'] = "Profile";
		return view('dashboard/profile', compact('data'));
	}

	public function changePassword() {
		$data = array();
		$data['title'] = "Change Password";
		return view('dashboard/changepassword', compact('data'));
	}

	public function doChangePassword(Request $request) {

		$validator = Validator::make($request->all(), [
			'old_password' => ['required'],
			'new_password' => ['required'],
			'confirm_password' => ['required'],

		]);

		if ($validator->fails()) {

			$response = array();
			$response['status'] = 0;
			$response['msg'] = "The request could not be understood by the server due to malformed syntax";
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();

			return redirect()->back()->with("error", "Something went wrong with validation");

		} else {

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

		}

		return response()->json($response)->header('Content-Type', 'application/json');

	}
}