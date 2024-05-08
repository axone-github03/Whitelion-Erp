<?php

namespace App\Http\Controllers;
// use App\Models\User;
use App\Models\User;
use Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Mail;

//
//use Session;

class ForgotPasswordController extends Controller {

	public function index() {

		if (Auth::check()) {
			return redirect()->route('dashboard');
		} else {
			return view('login/forgot_password');
		}

	}

	public function resetPasswordLink(Request $request) {

		$rules = array();
		$rules['email'] = "required";

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			$response = array();
			$response['status'] = 0;
			$response['msg'] = "The request could not be understood by the server due to malformed syntax";
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();

			return redirect()->back()->with("error", "Something went wrong with validation");
		} else {

			$User = User::where('email', $request->email)->first();
			if ($User) {
				$userTypes = getAllUserTypes();

				if (!isset($userTypes[$User->type]['can_login']) || (isset($userTypes[$User->type]['can_login']) && $userTypes[$User->type]['can_login'] == 0)) {

					return redirect()->route('forgot.password')->with("error", "You haven't access to reset password");

				} else if ($User->status != 1) {

					return redirect()->route('forgot.password')->with("error", "You cannot reset password because your account has been locked");

				} else {
					// $status = Password::sendResetLink(
					// 	$request->only('email')
					// );

					$resetPasswordToken = randomString("alpha-numeric", 50);

					$User->reset_password_token = $resetPasswordToken;
					$User->save();

					$configrationForNotify = configrationForNotify();

					$params = array();
					$params['from_name'] = $configrationForNotify['from_name'];
					$params['from_email'] = $configrationForNotify['from_email'];
					$params['to_email'] = $User->email;
					$params['to_name'] = $configrationForNotify['to_name'];
					$params['bcc_email'] = "sales@whitelion.in";
					$params['subject'] = "Reset Password | Whitelion";
					$params['user_name'] = $User->first_name." ".$User->last_name;
					$params['first_name'] = $User->first_name;
					$params['last_name'] = $User->last_name;
					$params['reset_password_token'] = $User->reset_password_token;

					if (Config::get('app.env') == "local") {
						$params['to_email'] = $configrationForNotify['test_email'];
						$params['bcc_email'] = $configrationForNotify['test_email_bcc'];
					}

					Mail::send('emails.reset_password_link', ['params' => $params], function ($m) use ($params) {
						$m->from($params['from_email'], $params['from_name']);
						$m->to($params['to_email'], $params['user_name'])->subject($params['subject']);

					});

					return redirect()->route('forgot.password')->with("success", "Successfully sent reset password to your email account");

				}
			} else {
				return redirect()->route('forgot.password')->with("error", "We can't find your email in our system");

			}

		}

	}

	public function resetPassword($token) {

		$User = User::where('reset_password_token', $token)->first();
		if (!$User) {

			return redirect()->route('forgot.password')->with("error", "Invalid reset password link, Please resend email");

		}

		$data = array();
		$data['reset_password_token'] = $token;
		return view('login/reset_password', compact('data'));

	}

	public function resetPasswordProcess(Request $request) {

		$rules = array();
		$rules['reset_password_token'] = "required";
		$rules['password'] = "required";
		$rules['cpassword'] = "required_with:password|same:password";

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			$response = array();
			$response['status'] = 0;
			$response['msg'] = "The request could not be understood by the server due to malformed syntax";
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();
			$response['data'] = json_decode(json_encode($response['data']), true);
			if (isset($response['data']['cpassword'][0]) && $response['data']['cpassword'][0] != "") {

				return redirect()->back()->with("error", "Password and Confirm Password mismatch");

			} else {

				return redirect()->back()->with("error", "Something went wrong with validation");

			}

		} else {

			$User = User::where('reset_password_token', $request->reset_password_token)->first();
			if ($User) {
				$User->reset_password_token = "";
				$User->password = Hash::make($request->password);
				$User->save();
				return redirect()->route('login')->with("success", "Successfully your password changed");

			} else {

				return redirect()->back()->with("error", "Invalid reset password link");

			}

		}

	}
}