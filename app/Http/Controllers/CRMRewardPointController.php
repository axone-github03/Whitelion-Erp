<?php

namespace App\Http\Controllers;

use Mail;
use App\Models\User;
use App\Models\Inquiry;
use App\Models\Architect;
use App\Models\Electrician;
use Illuminate\Http\Request;
use App\Models\CRMHelpDocument;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Whatsapp\WhatsappApiContoller;
use Exception;

class CRMRewardPointController extends Controller
{

	public function __construct()
	{
		$this->middleware(function ($request, $next) {
			$tabCanAccessBy = array(0, 1);
			if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
				return redirect()->route('dashboard');
			}
			return $next($request);
		});
	}

	public function index(Request $request)
	{

		$data = array();
		$data['title'] = "Add Manaually Point";
		return view('crm/manaually_point', compact('data'));
	}

	public function searchUser(Request $request)
	{

		$q = $request->q;

		$User = User::query();
		$User->select('users.id', 'users.first_name', 'users.last_name', 'users.phone_number', 'architect.total_point_current as architect_total_point_current', 'electrician.total_point_current as electrician_total_point_current', 'users.type');
		$User->where('users.status', 1);
		$User->whereIn('users.type', array(202, 302));
		$User->where('users.type', $request->type);

		$User->where(function ($query) use ($q) {

			$query->WhereRaw('CONCAT(users.first_name," ",users.last_name) like ?', [$q]);
			$query->orWhereRaw('CONCAT(users.first_name," ",users.last_name) like ? ', ["%" . $q . "%"]);
			$query->orWhereRaw('users.id like ? ', ["%" . $q . "%"]);
		});

		$User->leftJoin('architect', 'architect.user_id', '=', 'users.id');
		$User->leftJoin('electrician', 'electrician.user_id', '=', 'users.id');
		$User->limit(10);
		$UserResponse = $User->get();
		foreach ($UserResponse as $keyR => $valueR) {

			$currentPoint = "";

			if ($valueR->type == 202) {

				$currentPoint = " (current point : " . $valueR->architect_total_point_current . ")";
			} else if ($valueR->type == 302) {
				$currentPoint = " (current point : " . $valueR->electrician_total_point_current . ")";
			}

			$UserResponse[$keyR]['text'] = "#" . $valueR->id . " " . $valueR->first_name . " " . $valueR->last_name . " - " . $valueR->phone_number . $currentPoint;
		}

		$response = array();
		$response['results'] = $UserResponse;
		$response['pagination']['more'] = false;
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function searchInquiry(Request $request)
	{

		$q = $request->q;
		$User = Inquiry::query();
		$User->select('inquiry.id', 'inquiry.first_name', 'inquiry.last_name', 'inquiry.phone_number');
		$User->where(function ($query) use ($q) {

			$query->WhereRaw('CONCAT(inquiry.first_name," ",inquiry.last_name) like ?', [$q]);
			$query->orWhereRaw('CONCAT(inquiry.first_name," ",inquiry.last_name) like ? ', ["%" . $q . "%"]);
			$query->orWhereRaw('inquiry.id like ? ', ["%" . $q . "%"]);
		});
		$User->limit(10);
		$UserResponse = $User->get();
		foreach ($UserResponse as $keyR => $valueR) {

			$currentPoint = "";

			$UserResponse[$keyR]['text'] = "#" . $valueR->id . " (" . $valueR->first_name . " " . $valueR->last_name . " - " . $valueR->phone_number . ")";
		}

		$response = array();
		$response['results'] = $UserResponse;
		$response['pagination']['more'] = false;
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function addProcess(Request $request)
	{

		$validator = Validator::make($request->all(), [
			'calculation_type' => ['required'],
			'no_of_point' => ['required'],
			'inquiry_id' => ['required'],
			'user_id' => ['required'],
			'no_of_point' => ['required'],

		]);

		if ($validator->fails()) {

			$response = array();
			$response['status'] = 0;
			$response['msg'] = "The request could not be understood by the server due to malformed syntax";
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();
		} else {

			$User = User::find($request->user_id);

			$pointValue = $request->no_of_point;

			if ($request->calculation_type == 1) {

				$substring = "gained";
				$name = "point-gain";
			} else if ($request->calculation_type == -1) {

				$substring = "lose";
				$name = "point-lose";
			}

			if ($User) {

				$Inquiry = Inquiry::find($request->inquiry_id);
				if ($Inquiry) {

					if ($User->type == 202) {

						$Architect = Architect::where('user_id', $User->id)->first();

						if ($request->calculation_type == 1) {

							$Architect->total_point = $Architect->total_point + $pointValue;
							$Architect->total_point_current = $Architect->total_point_current + $pointValue;

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

							$params['inquiry_id'] = $Inquiry->id;
							$params['client_name'] = $Inquiry->first_name . ' ' . $Inquiry->last_name;
							$params['total_point'] = $Architect->total_point;

							$params['total_point'] = $Architect->total_point;
							$params['help_documents'] = json_decode(json_encode($helpDocuments), true);
						} else if ($request->calculation_type == -1) {

							$Architect->total_point = $Architect->total_point - $pointValue;
							$Architect->total_point_current = $Architect->total_point_current - $pointValue;
						}
						$Architect->save();

						$debugLog = array();
						$debugLog['user_id'] = 1;
						$debugLog['for_user_id'] = $User->id;
						$debugLog['inquiry_id'] = $Inquiry->id;
						$debugLog['is_manually'] = 1;
						$debugLog['points'] = $pointValue;
						$debugLog['name'] = $name;
						$debugLog['description'] = $pointValue . " Point " . $substring . " from inquiry #" . $Inquiry->id . "(" . $Inquiry->first_name . ' ' . $Inquiry->last_name . ") ";
						$debugLog['type'] = '';
						saveCRMUserLog($debugLog);
					} else if ($User->type == 302) {

						$Electrician = Electrician::where('user_id', $User->id)->first();

						if ($request->calculation_type == 1) {

							$Electrician->total_point = $Electrician->total_point + $pointValue;
							$Electrician->total_point_current = $Electrician->total_point_current + $pointValue;
						} else if ($request->calculation_type == -1) {

							$Electrician->total_point = $Electrician->total_point - $pointValue;
							$Electrician->total_point_current = $Electrician->total_point_current - $pointValue;
						}
						$Electrician->save();

						$debugLog = array();
						$debugLog['user_id'] = 1;
						$debugLog['for_user_id'] = $User->id;
						$debugLog['inquiry_id'] = $Inquiry->id;
						$debugLog['is_manually'] = 1;
						$debugLog['points'] = $pointValue;
						$debugLog['name'] = $name;
						$debugLog['description'] = $pointValue . " Point " . $substring . " from inquiry #" . $Inquiry->id . "(" . $Inquiry->first_name . ' ' . $Inquiry->last_name . ") ";
						$debugLog['type'] = '';
						saveCRMUserLog($debugLog);
					}

					if ($request->calculation_type == 1) {
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
						if ($User->type == 202) {
							$params['total_point'] = $Architect->total_point;
						} else if ($User->type == 302) {
							$params['total_point'] = $Electrician->total_point;
						}
						$params['help_documents'] = json_decode(json_encode($helpDocuments), true);
						$this->sendEmail($params, $User->type);
					}

					$response = successRes("Successfully updated reward point");
				}
			} else {
				$response = errorRes("Invalid User Id");
			}
		}

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	function sendEmail($params, $user_type)
	{

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
}
