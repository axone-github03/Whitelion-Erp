<?php

namespace App\Http\Controllers\Cron;

use Mail;
use Config;
use App\Models\User;
use App\Models\Inquiry;
use App\Models\Architect;
use App\Models\Parameter;
use App\Models\Electrician;
use App\Models\CRMHelpDocument;
use App\Http\Controllers\Controller;
use App\Models\InquiryQuestionAnswer;
use App\Http\Controllers\Whatsapp\WhatsappApiContoller;
use Illuminate\Http\Request;
use Exception;

class InquiryPointCalculation extends Controller
{
	//

	public function index()
	{

		$currentDatetime = date('Y-m-d H:i:s', strtotime("-1 day"));
		$query = Inquiry::query();
		$query->select('inquiry.id');
		// $query->Where('inquiry.id', 12103);
		$query->Where('inquiry.is_point_calculated', 0);
		$query->Where('inquiry.claimed_date_time', '<', $currentDatetime);
		// $query->Where('inquiry.is_point_calculated', 1);
		// $query->WhereIn('inquiry.total_point', array(0, 1));
		$query->orderBy('inquiry.id', "asc");
		$inquiryList = $query->get();


		// $inquiryList = json_decode(json_encode($inquiryList), true);
		// echo '<pre>';
		// print_r(json_encode($inquiryList));
		// die;

		foreach ($inquiryList as $key => $value) {

			$Inquiry = Inquiry::select('id', 'first_name', 'last_name', 'source_type', 'source_type_value', 'source_type_1', 'source_type_value_1', 'source_type_2', 'source_type_value_2', 'source_type_3', 'source_type_value_3', 'source_type_4', 'source_type_value_4', 'architect', 'electrician', 'billing_amount')->find($value->id);

			$pointValue = 0;

			if (
				$Inquiry->source_type == "user-202" ||
				$Inquiry->source_type_1 == "user-202" ||
				$Inquiry->source_type_2 == "user-202" ||
				$Inquiry->source_type_3 == "user-202" ||
				$Inquiry->source_type_4 == "user-202" ||
				$Inquiry->architect != "0" ||
				$Inquiry->source_type == "user-302" ||
				$Inquiry->source_type_1 == "user-302" ||
				$Inquiry->source_type_2 == "user-302" ||
				$Inquiry->source_type_3 == "user-302" ||
				$Inquiry->source_type_4 == "user-302" ||
				$Inquiry->electrician != "0"
			) {

				$invoiceAmountAnswer = InquiryQuestionAnswer::where('inquiry_id', $Inquiry->id)->where('inquiry_question_id', 12)->first();
				$Parameter = Parameter::where('code', 'point-value')->first();
				if ($Parameter) {
					$invoiceAmount = (int) $Inquiry->billing_amount;
					$pointValueR = (int) $Parameter->name_value;
					$pointValue = round($invoiceAmount / $pointValueR);
				}


				$Inquiry->is_point_calculated = 1;
				$Inquiry->total_point = $pointValue;
				$Inquiry->save();

				// if ($Inquiry->source_type == "user-202" && $Inquiry->source_type_value != "") {

				// 	$Architect = Architect::select('id', 'total_point', 'total_point_current', 'user_id', 'type')->where('user_id', $Inquiry->source_type_value)->first();
				// 	if ($Architect) {

				// 		//$Architect->total_site_completed = $Architect->total_site_completed + 1;
				// 		$Architect->total_point = $Architect->total_point + $pointValue;
				// 		$Architect->total_point_current = $Architect->total_point_current + $pointValue;
				// 		$Architect->save();

				// 		$debugLog = array();
				// 		$debugLog['user_id'] = 1;
				// 		$debugLog['for_user_id'] = $Inquiry->source_type_value;
				// 		$debugLog['inquiry_id'] = $Inquiry->id;
				// 		$debugLog['points'] = $pointValue;
				// 		$debugLog['name'] = "point-gain";
				// 		$debugLog['description'] = $pointValue . " Point gained from inquiry #" . $Inquiry->id . "(" . $Inquiry->first_name . ' ' . $Inquiry->last_name . ") ";
				// 		saveCRMUserLog($debugLog);

				// 		$User = User::select('email', 'first_name', 'last_name')->find($Architect->user_id);
				// 		if ($User) {

				// 			$params = array();
				// 			$params['to_email'] = $User->email;
				// 			$params['first_name'] = $User->first_name;
				// 			$params['last_name'] = $User->last_name;
				// 			$params['point_value'] = $pointValue;
				// 			$this->sendEmail($params);

				// 		}

				// 	}

				// } else if ($Inquiry->source_type_1 == "user-202" && $Inquiry->source_type_value_1 != "") {

				// 	$Architect = Architect::select('id', 'total_point', 'total_point_current', 'user_id', 'type')->where('user_id', $Inquiry->source_type_value_1)->first();
				// 	if ($Architect) {
				// 		// $Architect->total_site_completed = $Architect->total_site_completed + 1;
				// 		$Architect->total_point = $Architect->total_point + $pointValue;
				// 		$Architect->total_point_current = $Architect->total_point_current + $pointValue;
				// 		$Architect->save();

				// 		$debugLog = array();
				// 		$debugLog['user_id'] = 1;
				// 		$debugLog['for_user_id'] = $Inquiry->source_type_value_1;
				// 		$debugLog['inquiry_id'] = $Inquiry->id;
				// 		$debugLog['points'] = $pointValue;
				// 		$debugLog['name'] = "point-gain";
				// 		$debugLog['description'] = $pointValue . " Point gained from inquiry #" . $Inquiry->id . "(" . $Inquiry->first_name . ' ' . $Inquiry->last_name . ") ";
				// 		saveCRMUserLog($debugLog);
				// 		$User = User::select('email', 'first_name', 'last_name')->find($Architect->user_id);
				// 		if ($User) {

				// 			$params = array();
				// 			$params['to_email'] = $User->email;
				// 			$params['first_name'] = $User->first_name;
				// 			$params['last_name'] = $User->last_name;
				// 			$params['point_value'] = $pointValue;
				// 			$this->sendEmail($params);

				// 		}

				// 	}

				// } else if ($Inquiry->source_type_2 == "user-202" && $Inquiry->source_type_value_2 != "") {

				// 	$Architect = Architect::select('id', 'total_point', 'total_point_current', 'user_id', 'type')->where('user_id', $Inquiry->source_type_value_2)->first();
				// 	if ($Architect) {
				// 		// $Architect->total_site_completed = $Architect->total_site_completed + 1;
				// 		$Architect->total_point = $Architect->total_point + $pointValue;
				// 		$Architect->total_point_current = $Architect->total_point_current + $pointValue;
				// 		$Architect->save();

				// 		$debugLog = array();
				// 		$debugLog['user_id'] = 1;
				// 		$debugLog['for_user_id'] = $Inquiry->source_type_value_2;
				// 		$debugLog['inquiry_id'] = $Inquiry->id;
				// 		$debugLog['points'] = $pointValue;
				// 		$debugLog['name'] = "point-gain";
				// 		$debugLog['description'] = $pointValue . " Point gained from inquiry #" . $Inquiry->id . "(" . $Inquiry->first_name . ' ' . $Inquiry->last_name . ") ";
				// 		saveCRMUserLog($debugLog);
				// 		$User = User::select('email', 'first_name', 'last_name')->find($Architect->user_id);
				// 		if ($User) {

				// 			$params = array();
				// 			$params['to_email'] = $User->email;
				// 			$params['first_name'] = $User->first_name;
				// 			$params['last_name'] = $User->last_name;
				// 			$params['point_value'] = $pointValue;
				// 			$this->sendEmail($params);

				// 		}

				// 	}

				// } else if ($Inquiry->source_type_3 == "user-202" && $Inquiry->source_type_value_3 != "") {

				// 	$Architect = Architect::select('id', 'total_point', 'total_point_current', 'user_id', 'type')->where('user_id', $Inquiry->source_type_value_3)->first();
				// 	if ($Architect) {
				// 		$Architect->total_site_completed = $Architect->total_site_completed + 1;
				// 		$Architect->total_point = $Architect->total_point + $pointValue;
				// 		$Architect->total_point_current = $Architect->total_point_current + $pointValue;
				// 		$Architect->save();

				// 		$debugLog = array();
				// 		$debugLog['user_id'] = 1;
				// 		$debugLog['for_user_id'] = $Inquiry->source_type_value_3;
				// 		$debugLog['inquiry_id'] = $Inquiry->id;
				// 		$debugLog['points'] = $pointValue;
				// 		$debugLog['name'] = "point-gain";
				// 		$debugLog['description'] = $pointValue . " Point gained from inquiry #" . $Inquiry->id . "(" . $Inquiry->first_name . ' ' . $Inquiry->last_name . ") ";
				// 		saveCRMUserLog($debugLog);
				// 		$User = User::select('email', 'first_name', 'last_name')->find($Architect->user_id);
				// 		if ($User) {

				// 			$params = array();
				// 			$params['to_email'] = $User->email;
				// 			$params['first_name'] = $User->first_name;
				// 			$params['last_name'] = $User->last_name;
				// 			$params['point_value'] = $pointValue;
				// 			$this->sendEmail($params);

				// 		}

				// 	}

				// } else if ($Inquiry->source_type_4 == "user-202" && $Inquiry->source_type_value_4 != "") {

				// 	$Architect = Architect::select('id', 'total_point', 'total_point_current', 'user_id', 'type')->where('user_id', $Inquiry->source_type_value_4)->first();
				// 	if ($Architect) {
				// 		$Architect->total_site_completed = $Architect->total_site_completed + 1;
				// 		$Architect->total_point = $Architect->total_point + $pointValue;
				// 		$Architect->total_point_current = $Architect->total_point_current + $pointValue;
				// 		$Architect->save();

				// 		$debugLog = array();
				// 		$debugLog['user_id'] = 1;
				// 		$debugLog['for_user_id'] = $Inquiry->source_type_value_4;
				// 		$debugLog['inquiry_id'] = $Inquiry->id;
				// 		$debugLog['points'] = $pointValue;
				// 		$debugLog['name'] = "point-gain";
				// 		$debugLog['description'] = $pointValue . " Point gained from inquiry #" . $Inquiry->id . "(" . $Inquiry->first_name . ' ' . $Inquiry->last_name . ") ";
				// 		saveCRMUserLog($debugLog);
				// 		$User = User::select('email', 'first_name', 'last_name')->find($Architect->user_id);
				// 		if ($User) {

				// 			$params = array();
				// 			$params['to_email'] = $User->email;
				// 			$params['first_name'] = $User->first_name;
				// 			$params['last_name'] = $User->last_name;
				// 			$params['point_value'] = $pointValue;
				// 			$this->sendEmail($params);

				// 		}

				// 	}

				// } else

				if ($Inquiry->architect != 0 && $pointValue > 0) {

					$Architect = Architect::select('id', 'total_point', 'total_point_current', 'user_id', 'type')->where('user_id', $Inquiry->architect)->first();

					if ($Architect && $Architect->type == 202) {
						$Architect->total_site_completed = $Architect->total_site_completed + 1;
						$Architect->total_point = $Architect->total_point + $pointValue;
						$Architect->total_point_current = $Architect->total_point_current + $pointValue;
						$Architect->save();

						$debugLog = array();
						$debugLog['user_id'] = 1;
						$debugLog['for_user_id'] = $Inquiry->architect;
						$debugLog['inquiry_id'] = $Inquiry->id;
						$debugLog['points'] = $pointValue;
						$debugLog['name'] = "point-gain";
						$debugLog['description'] = $pointValue . " Point gained from inquiry #" . $Inquiry->id . "(" . $Inquiry->first_name . ' ' . $Inquiry->last_name . ") ";
						saveCRMUserLog($debugLog);
						$User = User::select('email', 'first_name', 'last_name','phone_number')->find($Architect->user_id);
						if ($User) {

							$query = CRMHelpDocument::query();
							$query->where('status', 1);
							$query->where('type', 202);
							$query->orderBy('publish_date_time', "desc");
							$helpDocuments = $query->first();

							$params = array();
							$params['to_email'] = $User->email;
							$params['first_name'] = $User->first_name;
							$params['last_name'] = $User->last_name;
							$params['point_value'] = $pointValue;
							$params['user_mobile'] = $User->phone_number;

							$params['inquiry_id'] = $Inquiry->id;
							$params['client_name'] = $Inquiry->first_name . ' ' . $Inquiry->last_name;
							$params['total_point'] = $Architect->total_point;

							$params['help_documents'] = json_decode(json_encode($helpDocuments), true);

							$this->sendEmail($params,202);
						}
					}
				}

				// if ($Inquiry->source_type == "user-302" && $Inquiry->source_type_value != "") {

				// 	$Electrician = Electrician::select('id', 'total_point', 'total_point_current', 'user_id', 'type')->where('user_id', $Inquiry->source_type_value)->first();
				// 	if ($Electrician) {

				// 		$Electrician->total_site_completed = $Electrician->total_site_completed + 1;
				// 		$Electrician->total_point = $Electrician->total_point + $pointValue;
				// 		$Electrician->total_point_current = $Electrician->total_point_current + $pointValue;
				// 		$Electrician->save();

				// 		$debugLog = array();
				// 		$debugLog['user_id'] = 1;
				// 		$debugLog['for_user_id'] = $Electrician->user_id;
				// 		$debugLog['inquiry_id'] = $Inquiry->id;
				// 		$debugLog['points'] = $pointValue;
				// 		$debugLog['name'] = "point-gain";
				// 		$debugLog['description'] = $pointValue . " Point gained from inquiry #" . $Inquiry->id . "(" . $Inquiry->first_name . ' ' . $Inquiry->last_name . ") ";
				// 		saveCRMUserLog($debugLog);

				// 		$User = User::select('email', 'first_name', 'last_name')->find($Electrician->user_id);
				// 		if ($User) {

				// 			$params = array();
				// 			$params['to_email'] = $User->email;
				// 			$params['first_name'] = $User->first_name;
				// 			$params['last_name'] = $User->last_name;
				// 			$params['point_value'] = $pointValue;
				// 			$this->sendEmail($params);

				// 		}

				// 	}

				// } else if ($Inquiry->source_type_1 == "user-302" && $Inquiry->source_type_value_1 != "") {

				// 	$Electrician = Electrician::select('id', 'total_point', 'total_point_current', 'user_id', 'type')->where('user_id', $Inquiry->source_type_value_1)->first();
				// 	if ($Electrician) {
				// 		// $Architect->total_site_completed = $Architect->total_site_completed + 1;
				// 		$Electrician->total_point = $Electrician->total_point + $pointValue;
				// 		$Electrician->total_point_current = $Electrician->total_point_current + $pointValue;
				// 		$Electrician->save();

				// 		$debugLog = array();
				// 		$debugLog['user_id'] = 1;
				// 		$debugLog['for_user_id'] = $Electrician->user_id;
				// 		$debugLog['inquiry_id'] = $Inquiry->id;
				// 		$debugLog['points'] = $pointValue;
				// 		$debugLog['name'] = "point-gain";
				// 		$debugLog['description'] = $pointValue . " Point gained from inquiry #" . $Inquiry->id . "(" . $Inquiry->first_name . ' ' . $Inquiry->last_name . ") ";
				// 		saveCRMUserLog($debugLog);
				// 		$User = User::select('email', 'first_name', 'last_name')->find($Electrician->user_id);
				// 		if ($User) {

				// 			$params = array();
				// 			$params['to_email'] = $User->email;
				// 			$params['first_name'] = $User->first_name;
				// 			$params['last_name'] = $User->last_name;
				// 			$params['point_value'] = $pointValue;
				// 			$this->sendEmail($params);

				// 		}

				// 	}

				// } else if ($Inquiry->source_type_2 == "user-302" && $Inquiry->source_type_value_2 != "") {

				// 	$Electrician = Electrician::select('id', 'total_point', 'total_point_current', 'user_id', 'type')->where('user_id', $Inquiry->source_type_value_2)->first();

				// 	if ($Electrician) {
				// 		// $Architect->total_site_completed = $Architect->total_site_completed + 1;
				// 		$Electrician->total_point = $Electrician->total_point + $pointValue;
				// 		$Electrician->total_point_current = $Electrician->total_point_current + $pointValue;
				// 		$Electrician->save();

				// 		$debugLog = array();
				// 		$debugLog['user_id'] = 1;
				// 		$debugLog['for_user_id'] = $Electrician->user_id;
				// 		$debugLog['inquiry_id'] = $Inquiry->id;
				// 		$debugLog['points'] = $pointValue;
				// 		$debugLog['name'] = "point-gain";
				// 		$debugLog['description'] = $pointValue . " Point gained from inquiry #" . $Inquiry->id . "(" . $Inquiry->first_name . ' ' . $Inquiry->last_name . ") ";
				// 		saveCRMUserLog($debugLog);
				// 		$User = User::select('email', 'first_name', 'last_name')->find($Electrician->user_id);
				// 		if ($User) {

				// 			$params = array();
				// 			$params['to_email'] = $User->email;
				// 			$params['first_name'] = $User->first_name;
				// 			$params['last_name'] = $User->last_name;
				// 			$params['point_value'] = $pointValue;
				// 			$this->sendEmail($params);

				// 		}

				// 	}

				// } else if ($Inquiry->source_type_3 == "user-302" && $Inquiry->source_type_value_3 != "") {

				// 	$Electrician = Electrician::select('id', 'total_point', 'total_point_current', 'user_id', 'type')->where('user_id', $Inquiry->source_type_value_3)->first();
				// 	if ($Electrician) {
				// 		$Electrician->total_site_completed = $Electrician->total_site_completed + 1;
				// 		$Electrician->total_point = $Electrician->total_point + $pointValue;
				// 		$Electrician->total_point_current = $Electrician->total_point_current + $pointValue;
				// 		$Electrician->save();

				// 		$debugLog = array();
				// 		$debugLog['user_id'] = 1;
				// 		$debugLog['for_user_id'] = $Inquiry->source_type_value_3;
				// 		$debugLog['inquiry_id'] = $Inquiry->id;
				// 		$debugLog['points'] = $pointValue;
				// 		$debugLog['name'] = "point-gain";
				// 		$debugLog['description'] = $pointValue . " Point gained from inquiry #" . $Inquiry->id . "(" . $Inquiry->first_name . ' ' . $Inquiry->last_name . ") ";
				// 		saveCRMUserLog($debugLog);
				// 		$User = User::select('email', 'first_name', 'last_name')->find($Electrician->user_id);
				// 		if ($User) {

				// 			$params = array();
				// 			$params['to_email'] = $User->email;
				// 			$params['first_name'] = $User->first_name;
				// 			$params['last_name'] = $User->last_name;
				// 			$params['point_value'] = $pointValue;
				// 			$this->sendEmail($params);

				// 		}

				// 	}

				// } else if ($Inquiry->source_type_4 == "user-302" && $Inquiry->source_type_value_4 != "") {

				// 	$Electrician = Electrician::select('id', 'total_point', 'total_point_current', 'user_id', 'type')->where('user_id', $Inquiry->source_type_value_4)->first();
				// 	if ($Electrician) {
				// 		$Electrician->total_site_completed = $Electrician->total_site_completed + 1;
				// 		$Electrician->total_point = $Electrician->total_point + $pointValue;
				// 		$Electrician->total_point_current = $Electrician->total_point_current + $pointValue;
				// 		$Electrician->save();

				// 		$debugLog = array();
				// 		$debugLog['user_id'] = 1;
				// 		$debugLog['for_user_id'] = $Electrician->user_id;
				// 		$debugLog['inquiry_id'] = $Inquiry->id;
				// 		$debugLog['points'] = $pointValue;
				// 		$debugLog['name'] = "point-gain";
				// 		$debugLog['description'] = $pointValue . " Point gained from inquiry #" . $Inquiry->id . "(" . $Inquiry->first_name . ' ' . $Inquiry->last_name . ") ";
				// 		saveCRMUserLog($debugLog);
				// 		$User = User::select('email', 'first_name', 'last_name')->find($Electrician->user_id);
				// 		if ($User) {

				// 			$params = array();
				// 			$params['to_email'] = $User->email;
				// 			$params['first_name'] = $User->first_name;
				// 			$params['last_name'] = $User->last_name;
				// 			$params['point_value'] = $pointValue;
				// 			$this->sendEmail($params);

				// 		}

				// 	}

				// } else

				if ($Inquiry->electrician != 0) {

					$Electrician = Electrician::select('id', 'total_point', 'total_point_current', 'user_id', 'type')->where('user_id', $Inquiry->electrician)->first();

					if ($Electrician && $Electrician->type == 302) {

						$Electrician->total_site_completed = $Electrician->total_site_completed + 1;
						$Electrician->total_point = $Electrician->total_point + $pointValue;
						$Electrician->total_point_current = $Electrician->total_point_current + $pointValue;
						$Electrician->save();

						$debugLog = array();
						$debugLog['user_id'] = 1;
						$debugLog['for_user_id'] = $Electrician->user_id;
						$debugLog['inquiry_id'] = $Inquiry->id;
						$debugLog['points'] = $pointValue;
						$debugLog['name'] = "point-gain";
						$debugLog['description'] = $pointValue . " Point gained from inquiry #" . $Inquiry->id . "(" . $Inquiry->first_name . ' ' . $Inquiry->last_name . ") ";
						saveCRMUserLog($debugLog);
						$User = User::select('email', 'first_name', 'last_name','phone_number')->find($Electrician->user_id);

						if ($User) {
							$query = CRMHelpDocument::query();
							$query->where('status', 1);
							$query->where('type', 302);
							$query->orderBy('publish_date_time', "desc");
							$helpDocuments = $query->first();

							$params = array();
							$params['to_email'] = $User->email;
							$params['first_name'] = $User->first_name;
							$params['last_name'] = $User->last_name;
							$params['point_value'] = $pointValue;
							$params['inquiry_id'] = $Inquiry->id;
							$params['client_name'] = $Inquiry->first_name . ' ' . $Inquiry->last_name;
							$params['total_point'] = $Electrician->total_point;
							$params['help_documents'] = json_decode(json_encode($helpDocuments), true);
							$params['user_mobile'] = $User->phone_number;
							$this->sendEmail($params,302);
						}
					}
				}
			} else {

				$Inquiry->is_point_calculated = 1;
				$Inquiry->total_point = $pointValue;
				$Inquiry->save();
			}
		}
		$response = successRes("");
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	function sendEmail($params, $user_type)
	{

		// if (Config::get('app.env') == "local") {
		// 	$params['to_email'] = "ankitsardhara4@gmail.com";
		// }

		$configrationForNotify = configrationForNotify();

		$params['bcc_email'] = array("sales@whitelion.in", "sc@whitelion.in", "poonam@whitelion.in");
		$params['from_name'] = $configrationForNotify['from_name'];
		$params['from_email'] = $configrationForNotify['from_email'];
		$params['to_name'] = $configrationForNotify['to_name'];

		$params['subject'] = "You just earned points!";

		if (Config::get('app.env') == "local") {
			$params['to_email'] = $configrationForNotify['test_email'];
		}

		//TEMPLATE 9 ARCHITECT & ELECTRICIAN
		Mail::send('emails.architect_points', ['params' => $params], function ($m) use ($params) { // SEND MAIL
			$m->from($params['from_email'], $params['from_name']);
			$m->bcc($params['bcc_email']);
			$m->to($params['to_email'], $params['to_name'])->subject($params['subject']);

			$fileName = preg_replace("![^a-z0-9]+!i", "-", $params['help_documents']['title']);
			$fileExtension = explode(".", $params['help_documents']['file_name']);
			$fileExtension = end($fileExtension);
			$fileName = $fileName . "." . $fileExtension;

			if (is_file('/s/crm-help-document/' . $params['help_documents']['file_name'])) {
				$m->attach(public_path('/s/crm-help-document/' . $params['help_documents']['file_name']), array(
					'as' => $fileName
				));
			}
		});

		//TEMPLATE ARCHITECT & ELECTRICIAN GET POINT
		try {
			$whatsapp_controller = new WhatsappApiContoller;
			$perameater_request = new Request();
			$user_type_lable = "";
			if ($user_type == 202) { //ARCHITECT
				$user_type_lable = "Architect";
				$perameater_request['q_whatsapp_massage_template'] = 'architect_get_point';
			} else if ($user_type == 302) { //ELECTRICIAN
				$user_type_lable = "Electrician";
				$perameater_request['q_whatsapp_massage_template'] = 'electrician_get_point';
			}

			$perameater_request['q_whatsapp_massage_mobileno'] = $params['user_mobile'];
			$perameater_request['q_whatsapp_massage_attechment'] = '';
			$perameater_request['q_broadcast_name'] = $params['first_name'] . ' ' . $params['last_name'] . '-' . $user_type_lable;
			$perameater_request['q_whatsapp_massage_parameters'] = array(
				'data[0]' => $params['point_value'],
				'data[1]' => $params['point_value'],
				'data[2]' => $params['total_point'],
				'data[3]' => $params['inquiry_id'],
				'data[4]' => $params['client_name'],
				'data[5]' => 'Namrata Bhawagar',
				'data[6]' => '+91 9016203763'
			);
			$wp_response = $whatsapp_controller->sendTemplateMessage($perameater_request);
			$response["whatsapp"] = $wp_response;
		} catch (Exception $e) {
			$response["whatsapp"] = $e->getMessage();
		}
	}

	function testMail()
	{

		if (Config::get('app.env') == "local") {
			$params['to_email'] = "ankitsardhara4@gmail.com";
		}
		$params['bcc_email'] = array("sales@whitelion.in", "sc@whitelion.in");
		$params['from_name'] = "Whitelion";
		$params['from_email'] = "developer@whitelion.in";
		$params['to_name'] = "Whitelion";
		$params['subject'] = "Good News: Your points are added, Iphone 13 is very near";
		Mail::send('emails.architect_points', ['params' => $params], function ($m) use ($params) {
			$m->from($params['from_email'], $params['from_name']);
			$m->bcc($params['bcc_email']);
			$m->to($params['to_email'], $params['to_name'])->subject($params['subject']);
		});
	}
}
