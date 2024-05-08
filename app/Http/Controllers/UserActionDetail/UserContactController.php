<?php

namespace App\Http\Controllers\UserActionDetail;

use App\Http\Controllers\Controller;

use App\Models\CRMSettingContactTag;
use App\Models\UserContact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserContactController extends Controller
{

	public function __construct()
	{

		$this->middleware(function ($request, $next) {

			$tabCanAccessBy = array(0, 1, 2, 7, 9, 101, 102, 103, 104, 105);

			if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
				return redirect()->route('dashboard');
			}

			return $next($request);
		});
	}

	public function save(Request $request)
	{

		$rules = array();
		$rules['contact_id'] = 'required';
		$rules['contact_user_id'] = 'required';
		$rules['contact_first_name'] = 'required';
		$rules['contact_last_name'] = 'required';
		$rules['contact_phone_number'] = 'required|digits:10|regex:/^[1-9][0-9]*$/';
		$rules['contact_tag_id'] = 'required';

		$customMessage = array();
		$customMessage['contact_user_id.required'] = "Invalid parameters";

		$validator = Validator::make($request->all(), $rules, $customMessage);

		if ($validator->fails()) {

			$response = errorRes("The request could not be understood by the server due to malformed syntax");
			$response['data'] = $validator->errors();
		} else {
			if ($request->contact_id == 0) {

				$UserContact = UserContact::where('user_id', $request->contact_user_id)->where('phone_number', $request->contact_phone_number)->first();

			} else {

				$UserContact = UserContact::where('id', '!=', $request->contact_id)->where('user_id', $request->contact_user_id)->where('phone_number', $request->contact_phone_number)->first();
			}

			if ($UserContact) {

				$response = errorRes("Contact number already link with user, Please use another phone number");
				return response()->json($response)->header('Content-Type', 'application/json');
			} else {

				$alernate_phone_number = isset($request->contact_alernate_phone_number) ? $request->contact_alernate_phone_number : 0;
				$contact_email = isset($request->contact_email) ? $request->contact_email : '';

				if ($request->contact_id == 0) {

					$UserContact = new UserContact();
					$UserContact->entryby = Auth::user()->id;
					$UserContact->entryip = $request->ip();
					$UserContact->updateby = Auth::user()->id;
					$UserContact->updateip = $request->ip();
				} else {
					$UserContact = UserContact::find($request->contact_id);
					$UserContact->updateby = Auth::user()->id;
					$UserContact->updateip = $request->ip();
				}
				$UserContact->user_id = $request->contact_user_id;
				$UserContact->contact_tag_id = $request->contact_tag_id;
				$UserContact->first_name = $request->contact_first_name;
				$UserContact->last_name = $request->contact_last_name;
				$UserContact->phone_number = $request->contact_phone_number;
				$UserContact->alernate_phone_number = $alernate_phone_number;
				$UserContact->email = $contact_email;
				$UserContact->type = '501'; //Additional Contact

				$UserContact->save();

				$response = successRes("Successfully saved contact");
				$response['id'] = $UserContact->user_id;
			}
		}
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	function searchTag(Request $request)
	{

		$searchKeyword = isset($request->q) ? $request->q : "";

		$data = CRMSettingContactTag::select('id', 'name as text');
		$data->where('crm_setting_contact_tag.status', 1);
		$data->where('crm_setting_contact_tag.name', 'like', "%" . $searchKeyword . "%");
		$data->limit(5);
		$data = $data->get();
		$response = array();
		$response['results'] = $data;
		$response['pagination']['more'] = false;
		return response()->json($response)->header('Content-Type', 'application/json');

		//CRMSettingScheduleCallType
	}

	function detail(Request $request)
	{

		$UserContact = UserContact::find($request->id);
		if ($UserContact) {
			$UserContact = json_encode($UserContact);
			$UserContact = json_decode($UserContact, true);

			$CRMSettingCallType = CRMSettingContactTag::select('id', 'name as text')->find($UserContact['contact_tag_id']);

			if ($CRMSettingCallType) {
				$CRMSettingCallType = json_encode($CRMSettingCallType);
				$CRMSettingCallType = json_decode($CRMSettingCallType, true);
				$UserContact['type'] = $CRMSettingCallType;
			}

			$response = successRes("");
			$response['data'] = $UserContact;
		} else {
			$response = errorRes("Something went wrong");
		}

		return response()->json($response)->header('Content-Type', 'application/json');
	}
}