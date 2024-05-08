<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

// use Illuminate\Http\Request;

class ZohoController extends Controller {
	//

	public function createChannelPartner() {

		$fileContent = file_put_contents('/var/www/html/encodework-apps/whitelion-erp/public/zoholog/' . time() . ".json", json_encode($request->all()));
		$response = successRes("Successfully called API");
		return response()->json($response)->header('Content-Type', 'application/json');

		$requestJSON = array();

		$user_address_line2 = isset($requestJSON['user_address_line2']) ? $requestJSON['user_address_line2'] : '';
		$channel_partner_d_address_line2 = isset($request->channel_partner_d_address_line2) ? $request->channel_partner_d_address_line2 : '';
		$channel_partner_credit_days = isset($request->channel_partner_credit_days) ? $request->channel_partner_credit_days : 0;
		$channel_partner_credit_limit = isset($request->channel_partner_credit_limit) ? $request->channel_partner_credit_limit : 0;

		$rules = array(
			'user_id' => ['required'],
			'user_type' => ['required'],
			'user_first_name' => ['required'],
			'user_last_name' => ['required'],
			'user_email' => ['required','email:rfc,dns'],
			'user_phone_number' => ['required','digits:10','regex:/^[1-9][0-9]*$/'],
			'user_address_line1' => ['required'],
			'user_pincode' => ['required'],
			'user_country_id' => ['required'],
			'user_state_id' => ['required'],
			'user_city_id' => ['required'],
			'channel_partner_firm_name' => ['required'],
			'channel_partner_reporting_manager' => ['required'],
			'channel_partner_payment_mode' => ['required'],
			'channel_partner_shipping_limit' => ['required'],
			'channel_partner_shipping_cost' => ['required'],
			'channel_partner_d_country_id' => ['required'],
			'channel_partner_d_state_id' => ['required'],
			'channel_partner_d_city_id' => ['required'],
			'channel_partner_d_pincode' => ['required'],
			'channel_partner_d_address_line1' => ['required'],

		);

		if ($request->user_type != 104) {
			$rules['channel_partner_gst_number'] = 'required';
		}

		if ($isSalePerson == 0) {
			$rules['channel_partner_sale_persons'] = 'required';
		}

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {

			$response = array();
			$response['status'] = 0;
			$response['msg'] = "The request could not be understood by the server due to malformed syntax";
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();

		} else {

			if ($isSalePerson == 0) {

				$channel_partner_sale_persons = implode(",", $request->channel_partner_sale_persons);

			} else {

				$channel_partner_sale_persons = Auth::user()->id;

			}

			$phone_number = $request->user_phone_number;
			$channel_partner_gst_number = "";
			if (isset($request->channel_partner_gst_number) && $request->channel_partner_gst_number != "") {
				$channel_partner_gst_number = $request->channel_partner_gst_number;

			}

			$alreadyEmail = User::query();
			$alreadyEmail->where('email', $request->user_email);

			if ($request->user_id != 0) {
				$alreadyEmail->where('id', '!=', $request->user_id);
			}
			$alreadyEmail = $alreadyEmail->first();

			$alreadyPhoneNumber = User::query();
			$alreadyPhoneNumber->where('phone_number', $request->user_phone_number);

			if ($request->user_id != 0) {
				$alreadyPhoneNumber->where('id', '!=', $request->user_id);
			}
			$alreadyPhoneNumber = $alreadyPhoneNumber->first();

			if ($alreadyEmail) {

				$response = errorRes("Email already exists, Try with another email");

			} else if ($alreadyPhoneNumber) {
				$response = errorRes("Phone number already exists, Try with another phone number");

			} else {

				$channel_partner_reporting = explode("-", $request->channel_partner_reporting_manager);

				if ($channel_partner_reporting[0] == "c") {

					$user_company_id = $channel_partner_reporting[1];
					$reporting_manager_id = 0;

				} else {

					$ChannelPartner = ChannelPartner::select('user_id', 'reporting_company_id')->where('user_id', $channel_partner_reporting[1])->first();
					$user_company_id = $ChannelPartner->reporting_company_id;
					$reporting_manager_id = $ChannelPartner->user_id;
				}

				$isCreditUpdate = 0;

				if ($request->user_id == 0) {
					$User = new User();
					$User->created_by = Auth::user()->id;
					$User->password = Hash::make("111111");
					$User->last_active_date_time = date('Y-m-d H:i:s');
					$User->last_login_date_time = date('Y-m-d H:i:s');
					$User->avatar = "default.png";

					$ChannelPartner = new ChannelPartner();
					$ChannelPartner->credit_limit = $channel_partner_credit_limit;
					$ChannelPartner->pending_credit = $channel_partner_credit_limit;
					$isCreditUpdate = 1;

				} else {
					$User = User::find($request->user_id);

					$ChannelPartner = ChannelPartner::find($User->reference_id);
					if (!$ChannelPartner) {

						$ChannelPartner = new ChannelPartner();
						$ChannelPartner->credit_limit = $channel_partner_credit_limit;
						$ChannelPartner->pending_credit = $channel_partner_credit_limit;
						$isCreditUpdate = 1;

					} else {

						if ($ChannelPartner->payment_mode != 2 && $request->channel_partner_payment_mode == 2) {

							$ChannelPartner->credit_limit = $channel_partner_credit_limit;
							$ChannelPartner->pending_credit = $channel_partner_credit_limit;
							$isCreditUpdate = 1;

						}

					}
				}
				$User->first_name = $request->user_first_name;
				$User->last_name = $request->user_last_name;
				$User->email = $request->user_email;
				$User->dialing_code = "+91";
				$User->phone_number = $request->user_phone_number;
				$User->ctc = 0;
				$User->address_line1 = $request->user_address_line1;
				$User->address_line2 = $user_address_line2;
				$User->pincode = $request->user_pincode;
				$User->country_id = $request->user_country_id;
				$User->state_id = $request->user_state_id;
				$User->city_id = $request->user_city_id;
				$User->company_id = $user_company_id;
				$User->type = $request->user_type;
				$User->status = $request->user_status;
				$User->reference_type = 0;
				$User->reference_id = 0;
				$User->save();

				$ChannelPartner->user_id = $User->id;
				$ChannelPartner->type = $request->user_type;
				$ChannelPartner->firm_name = $request->channel_partner_firm_name;
				$ChannelPartner->reporting_manager_id = $reporting_manager_id;
				$ChannelPartner->reporting_company_id = $user_company_id;

				if ($isSalePerson == 1) {

					if ($request->user_id == 0) {
						$ChannelPartner->sale_persons = $channel_partner_sale_persons;

					}

				} else {

					$ChannelPartner->sale_persons = $channel_partner_sale_persons;

				}

				$ChannelPartner->payment_mode = $request->channel_partner_payment_mode;
				$ChannelPartner->credit_days = $channel_partner_credit_days;
				$ChannelPartner->gst_number = $channel_partner_gst_number;
				$ChannelPartner->shipping_limit = $request->channel_partner_shipping_limit;
				$ChannelPartner->shipping_cost = $request->channel_partner_shipping_cost;
				$ChannelPartner->d_address_line1 = $request->channel_partner_d_address_line1;
				$ChannelPartner->d_address_line2 = $channel_partner_d_address_line2;
				$ChannelPartner->d_pincode = $request->channel_partner_d_pincode;
				$ChannelPartner->d_country_id = $request->channel_partner_d_country_id;
				$ChannelPartner->d_state_id = $request->channel_partner_d_state_id;
				$ChannelPartner->d_city_id = $request->channel_partner_d_city_id;

				$ChannelPartner->save();

				$User->reference_type = getChannelPartners()[$User->type]['lable'];
				$User->reference_id = $ChannelPartner->id;
				$User->save();

				//
				if ($isCreditUpdate == 1) {

					$CreditTranscationLog = new CreditTranscationLog();
					$CreditTranscationLog->user_id = $User->id;
					$CreditTranscationLog->type = 1;
					$CreditTranscationLog->request_amount = $channel_partner_credit_limit;
					$CreditTranscationLog->amount = $channel_partner_credit_limit;
					$CreditTranscationLog->description = "intial";
					$CreditTranscationLog->save();

				}

				//

				if ($request->user_id != 0) {

					$response = successRes("Successfully saved channel partner");

					$debugLog = array();
					$debugLog['name'] = "channel-partner-add";
					$debugLog['description'] = "channelpartner #" . $User->id . "(" . $User->first_name . " " . $User->last_name . ") has been updated ";
					saveDebugLog($debugLog);

				} else {
					$response = successRes("Successfully added channel partner");

					$debugLog = array();
					$debugLog['name'] = "channel-partner-edit";
					$debugLog['description'] = "channelpartner #" . $User->id . "(" . $User->first_name . " " . $User->last_name . ") has been added ";
					saveDebugLog($debugLog);

				}

			}

		}

		return response()->json($response)->header('Content-Type', 'application/json');

	}

}
