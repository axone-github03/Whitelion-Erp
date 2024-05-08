<?php

namespace App\Http\Controllers\CRM\Lead;

use App\Http\Controllers\Controller;
use App\Models\CRMSettingContactTag;
use App\Models\LeadContact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LeadContactController extends Controller {

	public function __construct() {

		$this->middleware(function ($request, $next) {

			$tabCanAccessBy = array(0, 1, 2, 6,9, 11, 13, 101, 102, 103, 104, 105, 202, 302);

			if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
				return redirect()->route('dashboard');
			}

			return $next($request);
		});
	}

	public function save(Request $request) {

		$rules = array();
		$rules['lead_contact_lead_id'] = 'required';
		$rules['lead_contact_first_name'] = 'required';
		$rules['lead_contact_last_name'] = 'required';
		// $rules['lead_contact_email'] = 'required';
		$rules['lead_contact_phone_number'] = 'required|digits:10|regex:/^[1-9][0-9]*$/';
		$rules['lead_contact_tag_id'] = 'required';

		$customMessage = array();
		$customMessage['lead_contact_lead_id.required'] = "Invalid parameters";

		$validator = Validator::make($request->all(), $rules, $customMessage);

		if ($validator->fails()) {

			$response = errorRes("The request could not be understood by the server due to malformed syntax");
			$response['data'] = $validator->errors();
		} else {

			if ($request->lead_contact_id == 0) {

				$LeadContact = LeadContact::where('lead_id', $request->lead_contact_lead_id)->where('phone_number', $request->lead_contact_phone_number)->first();

			} else {

				$LeadContact = LeadContact::where('id', '!=', $request->lead_contact_id)->where('lead_id', $request->lead_contact_lead_id)->where('phone_number', $request->lead_contact_phone_number)->first();
			}

			if ($LeadContact) {

				$response = errorRes("Contact number already link with lead, Please use another phone number");
				return response()->json($response)->header('Content-Type', 'application/json');
			} else {

				$alernate_phone_number = isset($request->lead_contact_alernate_phone_number) ? $request->lead_contact_alernate_phone_number : '';
				$lead_contact_email = isset($request->lead_contact_email) ? $request->lead_contact_email : '';

				if ($request->lead_contact_id == 0) {

					$LeadContact = new LeadContact();
				} else {
					$LeadContact = LeadContact::find($request->lead_contact_id);
				}
				$LeadContact->first_name = $request->lead_contact_first_name;
				$LeadContact->last_name = $request->lead_contact_last_name;
				$LeadContact->email = $lead_contact_email;
				$LeadContact->phone_number = $request->lead_contact_phone_number;
				$LeadContact->alernate_phone_number = $alernate_phone_number;
				$LeadContact->lead_id = $request->lead_contact_lead_id;
				$LeadContact->contact_tag_id = $request->lead_contact_tag_id;
				$LeadContact->type = '501';//Additional Contact
				$LeadContact->save();
				$response = successRes("Successfully saved lead");
				$response['id'] = $LeadContact->lead_id;
			}
		}

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	function searchTag(Request $request) {

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

	function detail(Request $request) {

		$LeadContact = LeadContact::find($request->id);
		if ($LeadContact) {
			$LeadContact = json_encode($LeadContact);
			$LeadContact = json_decode($LeadContact, true);

			$CRMSettingCallType = CRMSettingContactTag::select('id', 'name as text')->find($LeadContact['contact_tag_id']);

			if ($CRMSettingCallType) {
				$CRMSettingCallType = json_encode($CRMSettingCallType);
				$CRMSettingCallType = json_decode($CRMSettingCallType, true);
				$LeadContact['type'] = $CRMSettingCallType;
			}

			$response = successRes("");
			$response['data'] = $LeadContact;
		} else {
			$response = errorRes("Something went wrong");
		}

		return response()->json($response)->header('Content-Type', 'application/json');
	}
}
