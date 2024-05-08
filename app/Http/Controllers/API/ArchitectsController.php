<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Architect;
use App\Models\ArchitectCategory;
use App\Models\CRMHelpDocument;
use App\Models\CRMLog;
use App\Models\Inquiry;
use App\Models\SalePerson;
use App\Models\UserLog;
use App\Models\User;
use App\Models\StateList;
use App\Models\CountryList;
use App\Models\CityList;
use App\Models\Lead;
use App\Models\Wltrn_Quotation;
use Config;
use DB;
use Facade\Ignition\SolutionProviders\RunningLaravelDuskInProductionProvider;
use File;
use App\Models\UserContact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Whatsapp\WhatsappApiContoller;
use Mail;

//use Illuminate\Support\Facades\Hash;

class ArchitectsController extends Controller
{

	public function __construct()
	{

		$this->middleware(function ($request, $next) {

			$tabCanAccessBy = array(0, 1, 2, 7, 101, 102, 103, 104);

			if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
				$response = errorRes("Invalid access", 401);
				return response()->json($response, $response['status_code'])->header('Content-Type', 'application/json');
			}

			return $next($request);
		});
	}

	public function getCategory()
	{
		$ArchitectCategory = ArchitectCategory::select('id', 'name')->orderBy('id', 'asc')->get();
		$response = successRes("Architect Category");
		$response['data'] = $ArchitectCategory;
		return response()->json($response, $response['status_code'])->header('Content-Type', 'application/json');
	}

	public function ajax(Request $request)
	{

		$isSalePerson = isSalePerson();
		$isChannelPartner = isChannelPartner(Auth::user()->type);
		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		$isMarketingDispatcherUser = isMarketingDispatcherUser();
		if ($isSalePerson == 1) {
			$SalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
		}

		$selectColumns = array(
			'users.id',
			'users.type',
			'users.first_name',
			'users.last_name',
			'users.email',
			'users.phone_number',
			'architect.sale_person_id',
			'users.status',
			'architect.total_point_current',
			'sale_person.first_name as sale_person_first_name',
			'sale_person.last_name  as sale_person_last_name',
			'users.created_at',
			'architect.category_id',

		);

		$searchColumns = array(
			0 => 'users.id',
			1 => 'users.first_name',
			2 => 'users.last_name',
			3 => 'users.email',
			4 => 'users.phone_number',
			5 => 'sale_person.first_name',
			6 => 'sale_person.last_name',
			7 => "CONCAT(users.first_name,' ',users.last_name)",

		);

		$query = Architect::query();
		$query->leftJoin('users', 'users.id', '=', 'architect.user_id');
		$query->leftJoin('users as sale_person', 'architect.sale_person_id', '=', 'sale_person.id');
        $query->where('architect.status', '!=', 0);
		if (isset($request->type) && $request->type != "") {
			$query->where('architect.type', $request->type);
		}


		$query->whereIn('architect.type', array(201, 202));
        $query->where('architect.status', '!=', 0);
		if ($isAdminOrCompanyAdmin == 1) {
		} else if ($isSalePerson == 1) {
			$query->whereIn('architect.sale_person_id', $SalePersonsIds);
		} else if ($isChannelPartner != 0) {
			$query->where('architect.added_by', Auth::user()->id);
		}
		if (isset($request->category_id) && $request->category_id != "-1") {
			$query->where('architect.category_id', $request->category_id);
		}
		$query->select($selectColumns);
		$query->orderBy('architect.id', 'desc');

		// $query->orderBy($sortingColumns[$request['order'][0]['column']], $request['order'][0]['dir']);
		$isFilterApply = 0;
		if (isset($request['search']['value'])) {
			$isFilterApply = 1;
			$search_value = $request['search']['value'];
			$query->where(function ($query) use ($search_value, $searchColumns) {

				for ($i = 0; $i < count($searchColumns); $i++) {

					if ($i == 0) {
						$query->whereRaw($searchColumns[$i] . ' like ? ', [$search_value]);
						$query->orWhereRaw($searchColumns[$i] . ' like ? ', ["%" . $search_value . "%"]);
					} else {

						$query->orWhereRaw($searchColumns[$i] . ' like ? ', [$search_value]);
						$query->orWhereRaw($searchColumns[$i] . ' like ? ', ["%" . $search_value . "%"]);
					}
				}
			});
		}

		$data = $query->paginate(10);

		foreach ($data as $key => $value) {

			$data[$key]['status_lable'] = getArchitectsStatus()[$value['status']]['header_code'];
		}

		$response = successRes("Architect");
		$response['data'] = $data;
		return response()->json($response, $response['status_code'])->header('Content-Type', 'application/json');
	}

	public function searchSalePerson(Request $request)
	{

		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		$isSalePerson = isSalePerson();
		$isChannelPartner = isChannelPartner(Auth::user()->type);
		$isMarketingDispatcherUser = isMarketingDispatcherUser();

		$searchKeyword = isset($request->q) ? $request->q : "";

		if ($isAdminOrCompanyAdmin == 1 || $isSalePerson == 1) {

			$SalePerson = SalePerson::query();
			$SalePerson->leftJoin('users', 'sale_person.user_id', '=', 'users.id');
			$SalePerson->select('users.id as id', DB::raw('CONCAT(first_name," ", last_name) AS text'));
			$SalePerson->where('users.status', 1);
			$SalePerson->where(function ($query) use ($searchKeyword) {
				$query->where('users.first_name', 'like', '%' . $searchKeyword . '%');
				$query->orWhere('users.last_name', 'like', '%' . $searchKeyword . '%');
			});
			$SalePerson->limit(5);
			$SalePerson = $SalePerson->get();
			$response = successRes("Sales Person");
			$response['data'] = $SalePerson;
		} else if ($isChannelPartner != 0) {

			$salesPersonIds = getChannelPartnerSalesPersonsIds(Auth::user()->id);
			$SalePerson = SalePerson::query();
			$SalePerson->leftJoin('users', 'sale_person.user_id', '=', 'users.id');
			$SalePerson->select('users.id as id', DB::raw('CONCAT(first_name," ", last_name) AS text'));
			$SalePerson->where('users.status', 1);
			$SalePerson->whereIn('users.id', $salesPersonIds);

			$SalePerson->where(function ($query) use ($searchKeyword) {
				$query->where('users.first_name', 'like', '%' . $searchKeyword . '%');
				$query->orWhere('users.last_name', 'like', '%' . $searchKeyword . '%');
			});
			$SalePerson->limit(5);
			$SalePerson = $SalePerson->get();

			$response = successRes("Sales Person");
			$response['data'] = $SalePerson;
		}

		return response()->json($response, $response['status_code'])->header('Content-Type', 'application/json');
	}

	public function saveold(Request $request)
	{

		$user_id = isset($request->user_id) ? $request->user_id : 0;
		$isSalePerson = isSalePerson();
		$isChannelPartner = isChannelPartner(Auth::user()->type);
		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		$isMarketingDispatcherUser = isMarketingDispatcherUser();

		$rules = array();
		$rules['user_id'] = 'required';
		$rules['user_type'] = 'required';
		$rules['user_first_name'] = 'required';
		$rules['user_last_name'] = 'required';
		$rules['user_phone_number'] = 'required|digits:10|regex:/^[1-9][0-9]*$/';
		$rules['user_country_id'] = 'required';
		$rules['user_state_id'] = 'required';
		$rules['user_city_id'] = 'required';

		$rules['architect_firm_name'] = 'required';
		if ($isSalePerson == 0) {
			$rules['user_status'] = 'required';
			$rules['architect_sale_person_id'] = 'required';
		}

		if ($request->user_type == 202) {

			$rules['user_email'] = 'required|email:rfc,dns';
			$rules['user_address_line1'] = 'required';
			$rules['user_pincode'] = 'required';

			$rules['architect_practicing'] = 'required';
			$rules['architect_brand_using_for_switch'] = 'required';
			$rules['architect_brand_used_before_home_automation'] = 'required';
			$rules['architect_whitelion_smart_switches_before'] = 'required';

			if ($user_id == 0) {
				//$rules['architect_visiting_card'] = 'required';
			} else {
				$Architect = Architect::select('visiting_card')->where('user_id', $user_id)->first();
				if ($Architect && $Architect->visiting_card == "") {
					//$rules['architect_visiting_card'] = 'required';
				}
			}
		}

		if ($isChannelPartner == 0) {
			$rules['architect_source_type'] = 'required';
		}

		$customMessage = array();
		$customMessage['user_id.required'] = "Invalid parameters";
		$customMessage['user_type.required'] = "Invalid type";
		$customMessage['user_first_name.required'] = 'Please enter first name';
		$customMessage['user_last_name.required'] = 'Please enter last name';
		$customMessage['user_address_line1.required'] = 'Please enter address_line1';
		$customMessage['user_pincode.required'] = 'Please enter pincode';
		$customMessage['user_country_id.required'] = 'Please select country';
		$customMessage['user_state_id.required'] = 'Please select state';
		$customMessage['architect_source_type.required'] = 'Please select source type';
		$customMessage['architect_visiting_card.required'] = 'Please attach visiting card';
		$customMessage['architect_sale_person_id.required'] = 'Please select sale person';
		$customMessage['architect_practicing.required'] = 'Please select How long have you been practicing?';
		$customMessage['architect_brand_using_for_switch.required'] = 'Please select How long have you been practicing?';
		$customMessage['architect_brand_used_before_home_automation.required'] = 'Please enter Which brand have you used before for Home Automation ?';
		$customMessage['architect_whitelion_smart_switches_before.required'] = 'Please enter Which brand are you using for switches ?';

		$validator = Validator::make($request->all(), $rules, $customMessage);

		if ($validator->fails()) {

			$response = errorRes($validator->errors()->first(), 400);
			$response['data'] = $validator->errors();
			return response()->json($response, $response['status_code'])->header('Content-Type', 'application/json');
		} else {
			$source_type_value = "";

			if ($isChannelPartner == 0) {

				$source_type = $request->architect_source_type;
				$source_type_pieces = explode("-", $source_type);

				if ($source_type_pieces[0] == "user") {

					if (!isset($request->architect_source_user) || $request->architect_source_user == "") {
						$response = errorRes("Please select source");
						return response()->json($response)->header('Content-Type', 'application/json');
					}

					$source_type_value = $request->architect_source_user;
				} else if ($source_type_pieces[0] == "textrequired") {
					if (!isset($request->architect_source_user) || $request->architect_source_user == "") {
						$response = errorRes("Please enter source text");
						return response()->json($response)->header('Content-Type', 'application/json');
					}

					$source_type_value = $request->architect_source_user;
				} else {
					$source_type_value = isset($request->architect_source_user) ? $request->architect_source_user : '';
				}
			} else if ($isChannelPartner != 0) {
				$source_type_value = "";
				$source_type = "";
			}

			if ($isSalePerson == 1) {
				$sale_person_id = Auth::user()->id;
			} else {
				$sale_person_id = $request->architect_sale_person_id;
			}

			if ($request->user_type == 201) {
				$email = "";
			} else {
				$email = $request->user_email;
			}

			$phone_number = $request->user_phone_number;

			$alreadyEmail = User::query();
			$alreadyEmail->where('email', $request->user_email);
			$alreadyEmail->where('status', '=', 1);

			if ($request->user_id != 0) {
				$alreadyEmail->where('id', '!=', $request->user_id);
			}
			$alreadyEmail = $alreadyEmail->first();

			$alreadyPhoneNumber = User::query();
			$alreadyPhoneNumber->where('phone_number', $request->user_phone_number);
			$alreadyPhoneNumber->where('status', '=', 1);

			if ($request->user_id != 0) {
				$alreadyPhoneNumber->where('id', '!=', $request->user_id);
			}
			$alreadyPhoneNumber = $alreadyPhoneNumber->first();

			$AllUserTypes = getAllUserTypes();

			if ($alreadyEmail) {

				$response = errorRes("Email already exists(" . $AllUserTypes[$alreadyEmail->type]['name'] . "), Try with another email");
			} else if ($alreadyPhoneNumber) {

				$response = errorRes("Phone number already exists(" . $AllUserTypes[$alreadyPhoneNumber->type]['name'] . "), Try with another phone number");
			} else {

				$user_address_line1 = isset($request->user_address_line1) ? $request->user_address_line1 : '';
				$user_address_line2 = isset($request->user_address_line2) ? $request->user_address_line2 : '';
				$user_pincode = isset($request->user_pincode) ? $request->user_pincode : '';

				$is_residential = isset($request->architect_is_residential) ? $request->architect_is_residential : 0;
				$is_commercial_or_office_space = isset($request->architect_is_commercial_or_office_space) ? $request->architect_is_commercial_or_office_space : 0;
				$interior = isset($request->architect_interior) ? $request->architect_interior : 0;
				$exterior = isset($request->architect_exterior) ? $request->architect_exterior : 0;
				$structural_design = isset($request->architect_structural_design) ? $request->architect_structural_design : 0;
				$practicing = isset($request->architect_practicing) ? $request->architect_practicing : 0;
				$brand_using_for_switch = isset($request->architect_brand_using_for_switch) ? $request->architect_brand_using_for_switch : "";

				$whitelion_smart_switches_before = isset($request->architect_whitelion_smart_switches_before) ? $request->architect_whitelion_smart_switches_before : 0;

				$how_many_projects_used_whitelion_smart_switches = isset($request->architect_how_many_projects_used_whitelion_smart_switches) ? $request->architect_how_many_projects_used_whitelion_smart_switches : '';

				$experience_with_whitelion = isset($request->architect_experience_with_whitelion) ? $request->architect_experience_with_whitelion : 0;

				$brand_used_before_home_automation = isset($request->architect_brand_used_before_home_automation) ? $request->architect_brand_used_before_home_automation : '';

				$suggestion = isset($request->architect_suggestion) ? $request->architect_suggestion : '';

				$uploadedFile1 = "";
				$uploadedFile2 = "";

				if ($request->hasFile('architect_visiting_card')) {

					$folderPathofFile = '/s/architect';

					$fileObject1 = $request->file('architect_visiting_card');
					$extension = $fileObject1->getClientOriginalExtension();

					$fileName1 = time() . mt_rand(10000, 99999) . '.' . $extension;

					$destinationPath = public_path($folderPathofFile);

					$fileObject1->move($destinationPath, $fileName1);

					if (File::exists(public_path($folderPathofFile . "/" . $fileName1))) {

						$uploadedFile1 = $folderPathofFile . "/" . $fileName1;
						//START UPLOAD FILE ON SPACES

						$spaceUploadResponse = uploadFileOnSpaces(public_path($uploadedFile1), $uploadedFile1);
						if ($spaceUploadResponse != 1) {
							$uploadedFile1 = "";
						} else {
							unlink(public_path($uploadedFile1));
						}
						//END UPLOAD FILE ON SPACES

					}
				}

				if ($request->hasFile('architect_aadhar_card')) {

					$folderPathofFile = '/s/architect';
					$fileObject2 = $request->file('architect_aadhar_card');
					$extension = $fileObject2->getClientOriginalExtension();

					$fileName2 = time() . mt_rand(10000, 99999) . '.' . $extension;

					$destinationPath = public_path($folderPathofFile);

					$fileObject2->move($destinationPath, $fileName2);

					if (File::exists(public_path($folderPathofFile . "/" . $fileName2))) {

						$uploadedFile2 = $folderPathofFile . "/" . $fileName2;
						//START UPLOAD FILE ON SPACES
						$spaceUploadResponse = uploadFileOnSpaces(public_path($uploadedFile2), $uploadedFile2);
						if ($spaceUploadResponse != 1) {
							$uploadedFile2 = "";
						} else {

							unlink(public_path($uploadedFile2));
						}
						//END UPLOAD FILE ON SPACES

					}
				}

				$user_company_id = 1;
				$isMovedFromPrimeToNonPrime = 0;

				if ($request->user_id == 0) {
					$User = new User();
					$User->created_by = Auth::user()->id;
					$User->password = Hash::make("111111");
					$User->last_active_date_time = date('Y-m-d H:i:s');
					$User->last_login_date_time = date('Y-m-d H:i:s');
					$User->avatar = "default.png";
					$Architect = new Architect();
					if ($isSalePerson == 1) {

						$User->status = 1;
					} else {
						$User->status = $request->user_status;
					}

					$converted_prime = 1;
					$isMovedFromPrimeToNonPrime = 1;
					//$User->is_sent_mail = 0;
				} else {
					$User = User::find($request->user_id);
					$Architect = Architect::find($User->reference_id);
					if (!$Architect) {

						$response = errorRes("Something went wrong");
					}

					$converted_prime = $Architect->converted_prime;

					if ($uploadedFile1 == "") {
						$uploadedFile1 = $Architect->visiting_card;
					}
					if ($uploadedFile2 == "") {
						$uploadedFile2 = $Architect->aadhar_card;
					}

					if ($isSalePerson == 0) {

						$User->status = $request->user_status;
					}

					if ($Architect->type != $request->user_type) {

						$isMovedFromPrimeToNonPrime = 1;
					}
				}
				$User->first_name = $request->user_first_name;
				$User->last_name = $request->user_last_name;
				$User->email = $email;
				$User->dialing_code = "+91";
				$User->phone_number = $request->user_phone_number;
				$User->ctc = 0;
				$User->address_line1 = $user_address_line1;
				$User->address_line2 = $user_address_line2;
				$User->pincode = $user_pincode;
				$User->country_id = $request->user_country_id;
				$User->state_id = $request->user_state_id;
				$User->city_id = $request->user_city_id;
				$User->company_id = $user_company_id;
				$User->type = $request->user_type;
				$User->reference_type = 0;
				$User->reference_id = 0;

				$User->save();
				if ($isMovedFromPrimeToNonPrime == 1) {
					$Architect->prime_nonprime_date = date('Y-m-d H:i:s');
				}

				$Architect->user_id = $User->id;
				$Architect->type = $request->user_type;
				$Architect->firm_name = $request->architect_firm_name;
				$Architect->sale_person_id = $sale_person_id;
				$Architect->is_residential = (int) $is_residential;
				$Architect->is_commercial_or_office_space = (int) $is_commercial_or_office_space;
				$Architect->interior = (int) $interior;
				$Architect->exterior = (int) $exterior;
				$Architect->structural_design = (int) $structural_design;
				$Architect->practicing = (int) $practicing;
				$Architect->brand_using_for_switch = $brand_using_for_switch;
				$Architect->visiting_card = $uploadedFile1;
				$Architect->aadhar_card = $uploadedFile2;

				$Architect->whitelion_smart_switches_before = $whitelion_smart_switches_before;
				$Architect->how_many_projects_used_whitelion_smart_switches = $how_many_projects_used_whitelion_smart_switches;
				$Architect->brand_used_before_home_automation = $brand_used_before_home_automation;
				$Architect->experience_with_whitelion = $experience_with_whitelion;
				$Architect->suggestion = $suggestion;

				if ($request->architect_birth_date != "") {

					$Architect->birth_date = $request->architect_birth_date;
				} else {
					$Architect->birth_date = null;
				}
				if ($request->architect_anniversary_date != "") {

					$Architect->anniversary_date = $request->architect_anniversary_date;
				} else {
					$Architect->anniversary_date = null;
				}

				if ($request->user_type == 202) {
					$Architect->converted_prime = 1;
				}
				$Architect->source_type = $source_type;
				$Architect->source_type_value = $source_type_value;
				$Architect->added_by = Auth::user()->id;
				$Architect->save();
				
				$User->reference_type = "architect";
				$User->reference_id = $Architect->id;
				$User->save();
				$debugLog = array();
				if ($request->user_id != 0) {
					$debugLog['name'] = "architect-edit";
					$debugLog['description'] = "user #" . $User->id . "(" . $User->first_name . " " . $User->last_name . ") has been updated ";
					
					
				} else {


					$mobileNotificationTitle = "New Architect Create";
					$mobileNotificationMessage = "New Architect " . $User->first_name . " " . $User->last_name . " Added By " . Auth::user()->first_name . " " . Auth::user()->last_name;;
					$notificationUserids = getParentSalePersonsIds($Architect->sale_person_id);
					$notificationUserids[] = $Architect->sale_person_id;
					$UsersNotificationTokens = UsersNotificationTokens($notificationUserids);
					// sendNotificationTOAndroid($mobileNotificationTitle, $mobileNotificationMessage, $UsersNotificationTokens);
					sendNotificationTOAndroid($mobileNotificationTitle, $mobileNotificationMessage, $UsersNotificationTokens,'Architect',$User);


					$debugLog['name'] = "architect-add";
					$debugLog['description'] = "user #" . $User->id . "(" . $User->first_name . " " . $User->last_name . ") has been added ";
					$response = successRes("Successfully added user");
				}
				saveDebugLog($debugLog);

				if (($request->user_id == 0 && $request->user_type == 202) || ($request->user_id != 0 && $request->user_type == 202 && $converted_prime2 == 0)){
					$pointValue = 50;
					$Architect->total_point = $Architect->total_point + $pointValue;
					$Architect->total_point_current = $Architect->total_point_current + $pointValue;
					$Architect->save();

					$debugLog = array();
					$debugLog['for_user_id'] = $Architect->user_id;
					$debugLog['name'] = "point-gain";
					$debugLog['points'] = $pointValue;
					$debugLog['description'] = $pointValue . " Point gained joining bonus ";
                    $debugLog['type'] = '';
					saveCRMUserLog($debugLog);

					$configrationForNotify = configrationForNotify();

					$params = array();
					$params['from_name'] = $configrationForNotify['from_name'];
					$params['from_email'] = $configrationForNotify['from_email'];
					$params['to_email'] = $User->email;
					$params['to_name'] = $configrationForNotify['to_name'];
					$params['bcc_email'] = array("sales@whitelion.in", "sc@whitelion.in", "poonam@whitelion.in");
					$params['subject'] = "Welcome to the Whitelion";
					$params['user_first_name'] = $User->first_name;
					$params['user_last_name'] = $User->last_name;
					$params['user_mobile'] = $User->phone_number;
					$params['credentials_email'] = $User->email;
					$params['credentials_password'] = "111111";
					$query = CRMHelpDocument::query();
					$query->where('status', 1);
					$query->where('type', 202);
					$query->limit(30);
					$query->orderBy('publish_date_time', "desc");
					$helpDocuments = $query->get();
					$params['help_documents'] = json_decode(json_encode($helpDocuments), true);

					if (Config::get('app.env') == "local") { // SEND MAIL
						$params['to_email'] = $configrationForNotify['test_email'];
						$params['bcc_email'] = $configrationForNotify['test_email_bcc'];
					}

					//TEMPLATE 6
					Mail::send('emails.signup_architect', ['params' => $params], function ($m) use ($params) {

						$m->from($params['from_email'], $params['from_name']);
						$m->bcc($params['bcc_email']);
						$m->to($params['to_email'], $params['to_name'])->subject($params['subject']);

						foreach ($params['help_documents'] as $helpDocument) {

							$fileName = preg_replace("![^a-z0-9]+!i", "-", $helpDocument['title']);
							$fileExtension = explode(".", $helpDocument['file_name']);
							$fileExtension = end($fileExtension);
							$fileName = $fileName . "." . $fileExtension;

							if (is_file('/s/crm-help-document/' . $helpDocument['file_name'])) {
								$m->attach(public_path('/s/crm-help-document/' . $helpDocument['file_name']), array(
									'as' => $fileName
								));
							}
						}
					});

                    $helpDocument = CRMHelpDocument::query();
					$helpDocument->where('status', 1);
					$helpDocument->where('type', 202);
					$helpDocument->orderBy('publish_date_time', "desc");
					$helpDocument = $helpDocument->first();
					//TEMPLATE 7
					$whatsapp_controller = new WhatsappApiContoller;
					$perameater_request = new Request();
					$perameater_request['q_whatsapp_massage_mobileno'] = $User->phone_number;
					$perameater_request['q_whatsapp_massage_template'] = 'for_architect_download_mobileapp';
					$perameater_request['q_whatsapp_massage_attechment'] = $helpDocument ? getSpaceFilePath($helpDocument->file_name) : '';
					$perameater_request['q_broadcast_name'] = $User->first_name . ' ' . $User->last_name;
					$perameater_request['q_whatsapp_massage_parameters'] = array(
						'data[0]' => $User->email,
						'data[1]' => '111111'
					);
					$wp_response = $whatsapp_controller->sendTemplateMessage($perameater_request);
					$response["whatsapp"] = $wp_response;
				}
			}
		}

		return response()->json($response, $response['status_code'])->header('Content-Type', 'application/json');
	}

	public function save(Request $request)
    {
        $user_id = isset($request->user_id) ? $request->user_id : 0;

        $isSalePerson = isSalePerson();
        $isChannelPartner = isChannelPartner(Auth::user()->type);
        $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
        $isMarketingDispatcherUser = isMarketingDispatcherUser();

        $rules = [];
        $rules['user_id'] = 'required';
        $rules['user_type'] = 'required';
        $rules['user_first_name'] = 'required';
        $rules['user_last_name'] = 'required';
        $rules['user_phone_number'] = 'required|digits:10|regex:/^[1-9][0-9]*$/';
        if ($request->user_id == 0) {
            $rules['user_house_no'] = 'required';
            $rules['user_area'] = 'required';
        }
        $rules['user_address_line1'] = 'required';
        $rules['user_pincode'] = 'required';
        $rules['user_city_id'] = 'required';
        $rules['architect_firm_name'] = 'required';
        if ($isSalePerson == 0) {
            $rules['architect_sale_person_id'] = 'required';
        }

        if (isset($request->user_status) && $request->user_id != 0) {
            $rules['user_status'] = 'required';
            // $rules['architect_recording'] = 'required';
        }

        if ($user_id != 0 && $isAdminOrCompanyAdmin == 0) {
            $User = User::find($request->user_id);
            if ($User) {
                if ($User->type == 202 && $request->user_type == 201) {
                    $response = errorRes("user can't convert prime to non-prime please contact to admin !");
                    return response()
                        ->json($response)
                        ->header('Content-Type', 'application/json');
                }
            }
        }

        if ($request->user_type == 202) {
            $rules['user_email'] = 'required|email:rfc,dns';
            if ($request->user_id == 0) {
                $rules['user_house_no'] = 'required';
                $rules['user_area'] = 'required';
            }
            $rules['user_address_line1'] = 'required';
            $rules['user_pincode'] = 'required';
            $rules['user_city_id'] = 'required';
        }

        if ($isChannelPartner == 0 && isTaleSalesUser() == 0 && $user_id == 0) {
            $rules['architect_source_type'] = 'required';
        }
        if ($isSalePerson == 1 && $request->user_type == 202 && ($request->user_id != 0 && $request->user_id != '')) {
            $rules = [];
            $rules['user_id'] = 'required';
            $rules['user_type'] = 'required';
            $User = User::find($request->user_id);
            $requireFieldForSalesUser = [];

            if ($User) {
                if ($User->pincode == '') {
                    $rules['user_pincode'] = 'required';
                    $requireFieldForSalesUser[] = 'user_pincode';
                }

                if ($User->address_line1 == '') {
                    $rules['user_address_line1'] = 'required';
                    $requireFieldForSalesUser[] = 'user_address_line1';
                }

                if ($request->user_type == 202) {
                    if ($User->email == '') {
                        $rules['user_email'] = 'required|email:rfc,dns';
                        $requireFieldForSalesUser[] = 'user_email';
                    }
                }
            }
        }

        $customMessage = [];
        $customMessage['user_id.required'] = 'Invalid parameters';
        $customMessage['user_type.required'] = 'Invalid type';
        $customMessage['user_first_name.required'] = 'Please enter first name';
        $customMessage['user_last_name.required'] = 'Please enter last name';
        if ($request->user_id == 0) {
            $customMessage['user_house_no.required'] = 'Please enter house number';
            $customMessage['user_area.required'] = 'Please enter area';
        }
        $customMessage['user_address_line1.required'] = 'Please enter address_line1';
        $customMessage['user_pincode.required'] = 'Please enter pincode';
        $customMessage['user_city_id.required'] = 'Please select city';
        // $customMessage['user_country_id.required'] = 'Please select country';
        // $customMessage['user_state_id.required'] = 'Please select state';
        if (isTaleSalesUser() == 0 && $user_id == 0) {
            $customMessage['architect_source_type.required'] = 'Please select reference type';
        }
        $customMessage['architect_sale_person_id.required'] = 'Please select sale person';
        $customMessage['architect_practicing.required'] = 'Please select How long have you been practicing?';
        $customMessage['architect_brand_using_for_switch.required'] = 'Please select How long have you been practicing?';
        $customMessage['architect_brand_used_before_home_automation.required'] = 'Please enter Which brand have you used before for Home Automation ?';
        $customMessage['architect_whitelion_smart_switches_before.required'] = 'Please enter Which brand are you using for switches ?';

        $validator = Validator::make($request->all(), $rules, $customMessage);

        if ($validator->fails()) {
            $response = [];
            $response['status'] = 0;
            $response['msg'] = $validator->errors()->first();
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors()->first();
        } else {
            $AllUserTypes = getAllUserTypes();

            if ($isSalePerson == 1 && $request->user_type == 202 && ($request->user_id != 0 && $request->user_id != '')) {
                $converted_prime2 = 1;
                if (count($requireFieldForSalesUser) > 0) {
                    if ($request->user_type == 202) {
                        if (in_array('user_email', $requireFieldForSalesUser)) {
                            $alreadyEmail = User::query();
                            $alreadyEmail->where('email', $request->user_email);
                            $alreadyEmail->where('id', '!=', $request->user_id);
                            $alreadyEmail = $alreadyEmail->first();

                            if ($alreadyEmail) {
                                $response = errorRes('Email already exists(' . $AllUserTypes[$alreadyEmail->type]['name'] . '), Try with another email');
                                return response()
                                    ->json($response)
                                    ->header('Content-Type', 'application/json');
                            } else {
                                $User->email = $request->user_email;
                            }
                        }
                    }

                    if (in_array('user_address_line1', $requireFieldForSalesUser)) {
                        $User->address_line1 = $request->user_address_line1;
                    }
                    if (in_array('user_pincode', $requireFieldForSalesUser)) {
                        $User->pincode = $request->user_pincode;
                    }

                    $User->type = $request->user_type;
                    $User->save();
                    $Architect = Architect::find($User->reference_id);

                    if ($request->user_id != 0 && $request->user_type == 202 && $Architect->converted_prime == 0) {
                        $Architect->prime_nonprime_date = date('Y-m-d H:i:s');
                        $converted_prime2 = 0;
                    } else {
                        $converted_prime2 = 1;
                    }
                    if ($request->user_type == 202) {
                        $Architect->converted_prime = 1;
                    }
                    $Architect->type = $request->user_type;
                    $Architect->save();
                    $debugLog['name'] = 'architect-edit';
                    $debugLog['description'] = 'user #' . $User->id . '(' . $User->first_name . ' ' . $User->last_name . ') has been updated ';
                    $response = successRes('Successfully saved user');
                    saveDebugLog($debugLog);
                } else {
                    $User->type = $request->user_type;
                    $User->save();
                    $Architect = Architect::find($User->reference_id);
                    if ($request->user_id != 0 && $request->user_type == 202 && $Architect->converted_prime == 0) {
                        $Architect->prime_nonprime_date = date('Y-m-d H:i:s');
                        $converted_prime2 = 0;
                    } else {
                        $converted_prime2 = 1;
                    }
                    if ($request->user_type == 202) {
                        $Architect->converted_prime = 1;
                    }
                    $Architect->type = $request->user_type;
                    $Architect->save();
                    $debugLog['name'] = 'architect-edit';
                    $debugLog['description'] = 'user #' . $User->id . '(' . $User->first_name . ' ' . $User->last_name . ') has been updated ';
                    $response = successRes('Successfully saved user');
                    saveDebugLog($debugLog);
                }

                if (($request->user_id == 0 && $request->user_type == 202) || ($request->user_id != 0 && $request->user_type == 202 && $converted_prime2 == 0)) {
                    $pointValue = 50;
                    $Architect->total_point = $Architect->total_point + $pointValue;
                    $Architect->total_point_current = $Architect->total_point_current + $pointValue;
                    $Architect->save();

                    $debugLog = [];
                    $debugLog['for_user_id'] = $Architect->user_id;
                    $debugLog['name'] = 'point-gain';
                    $debugLog['points'] = $pointValue;
                    $debugLog['description'] = $pointValue . ' Point gained joining bonus ';
                    $debugLog['type'] = '';
                    saveCRMUserLog($debugLog);

                    $configrationForNotify = configrationForNotify();

                    $params = [];
                    $params['from_name'] = $configrationForNotify['from_name'];
                    $params['from_email'] = $configrationForNotify['from_email'];
                    $params['to_email'] = $User->email;
                    $params['to_name'] = $configrationForNotify['to_name'];
                    $params['bcc_email'] = ['sales@whitelion.in', 'sc@whitelion.in', 'poonam@whitelion.in'];
                    $params['subject'] = 'Welcome to the Whitelion';
                    $params['user_first_name'] = $User->first_name;
                    $params['user_last_name'] = $User->last_name;
                    $params['user_mobile'] = $User->phone_number;
                    $params['credentials_email'] = $User->email;
                    $params['credentials_password'] = '111111';
                    $query = CRMHelpDocument::query();
                    $query->where('status', 1);
                    $query->where('type', 202);
                    $query->limit(30);
                    $query->orderBy('publish_date_time', 'desc');
                    $helpDocuments = $query->get();
                    $params['help_documents'] = json_decode(json_encode($helpDocuments), true);

                    if (Config::get('app.env') == 'local') {
                        // SEND MAIL
                        $params['to_email'] = $configrationForNotify['test_email'];
                        $params['bcc_email'] = $configrationForNotify['test_email_bcc'];
                    }

                    //TEMPLATE 6
                    Mail::send('emails.signup_architect', ['params' => $params], function ($m) use ($params) {
                        $m->from($params['from_email'], $params['from_name']);
                        $m->bcc($params['bcc_email']);
                        $m->to($params['to_email'], $params['to_name'])->subject($params['subject']);

                        foreach ($params['help_documents'] as $helpDocument) {
                            $fileName = preg_replace('![^a-z0-9]+!i', '-', $helpDocument['title']);
                            $fileExtension = explode('.', $helpDocument['file_name']);
                            $fileExtension = end($fileExtension);
                            $fileName = $fileName . '.' . $fileExtension;

                            if (is_file('/s/crm-help-document/' . $helpDocument['file_name'])) {
                                $m->attach(public_path('/s/crm-help-document/' . $helpDocument['file_name']), [
                                    'as' => $fileName,
                                ]);
                            }
                        }
                    });

                    $helpDocument = CRMHelpDocument::query();
					$helpDocument->where('status', 1);
					$helpDocument->where('type', 202);
					$helpDocument->orderBy('publish_date_time', "desc");
					$helpDocument = $helpDocument->first();
                    //TEMPLATE 7
                    $whatsapp_controller = new WhatsappApiContoller();
                    $perameater_request = new Request();
                    $perameater_request['q_whatsapp_massage_mobileno'] = $User->phone_number;
                    $perameater_request['q_whatsapp_massage_template'] = 'for_architect_download_mobileapp';
					$perameater_request['q_whatsapp_massage_attechment'] = $helpDocument ? getSpaceFilePath($helpDocument->file_name) : '';
                    $perameater_request['q_broadcast_name'] = $User->first_name . ' ' . $User->last_name;
                    $perameater_request['q_whatsapp_massage_parameters'] = array(
						'data[0]' => $User->email,
						'data[1]' => '111111'
					);
                    $wp_response = $whatsapp_controller->sendTemplateMessage($perameater_request);
                    $response['whatsapp'] = $wp_response;
                }

                return response()
                    ->json($response)
                    ->header('Content-Type', 'application/json');
            }

            $source_type_value = '';
            $principal_architect_name = isset($request->architect_principal_architect_name) ? $request->architect_principal_architect_name : '';
            $instagram_link = isset($request->architect_instagram) ? $request->architect_instagram : '';
            // $data_verified = isset($request->architect_data_verified) ? $request->architect_data_verified : 0;
            // $data_not_verified = isset($request->architect_data_not_verified) ? $request->architect_data_not_verified : 0;
            // $missing_data = isset($request->architect_missing_data) ? $request->architect_missing_data : 0;
            // $tele_verified = isset($request->architect_tele_verified) ? $request->architect_tele_verified : 0;
            // $tele_not_verified = isset($request->architect_tele_not_verified) ? $request->architect_tele_not_verified : 0;

            if ($isChannelPartner == 0) {
                $source_type = $request->architect_source_type;
                $source_type_pieces = explode('-', $source_type);

                if ($source_type_pieces[0] == 'user') {
                    if (!isset($request->architect_source_name) || $request->architect_source_name == '') {
                        $response = errorRes('Please select source');
                        return response()
                            ->json($response)
                            ->header('Content-Type', 'application/json');
                    }

                    $source_type_value = $request->architect_source_name;
                } elseif ($source_type_pieces[0] == 'textrequired') {
                    if (!isset($request->architect_source_text) || $request->architect_source_text == '') {
                        $response = errorRes('Please enter source text');
                        return response()
                            ->json($response)
                            ->header('Content-Type', 'application/json');
                    }

                    $source_type_value = $request->architect_source_text;
                } else {
                    $source_type_value = isset($request->architect_source_text) ? $request->architect_source_text : '';
                }
            } elseif ($isChannelPartner != 0) {
                $source_type_value = '';
                $source_type = '';
            }

            if ($isSalePerson == 1) {
                $sale_person_id = Auth::user()->id;
            } else {
                $sale_person_id = $request->architect_sale_person_id;
            }

            if ($request->user_type == 201) {
                $email = "";
            } else {
                $email = $request->user_email;
            }

            $phone_number = $request->user_phone_number;

            $alreadyEmail = User::query();
            $alreadyEmail->where('email', $request->user_email);
            $alreadyEmail->where('type', '!=', 10000);
            $alreadyEmail->where('status', '=', 1);

            if ($request->user_id != 0) {
                $alreadyEmail->where('id', '!=', $request->user_id);
            }
            $alreadyEmail = $alreadyEmail->first();

            $alreadyPhoneNumber = User::query();
            $alreadyPhoneNumber->where('phone_number', $request->user_phone_number);
            $alreadyPhoneNumber->where('type', '!=', 10000);
            $alreadyPhoneNumber->where('status', '=', 1);

            if ($request->user_id != 0) {
                $alreadyPhoneNumber->where('id', '!=', $request->user_id);
            }
            $alreadyPhoneNumber = $alreadyPhoneNumber->first();

            if ($alreadyEmail) {
                $response = errorRes('Email already exists(' . $AllUserTypes[$alreadyEmail->type]['name'] . '), Try with another email');
            } elseif ($alreadyPhoneNumber) {
                $response = errorRes('Phone number already exists(' . $AllUserTypes[$alreadyPhoneNumber->type]['name'] . '), Try with another phone number');
            } else {
                $user_house_no = isset($request->user_house_no) ? $request->user_house_no : '';
                $user_address_line1 = isset($request->user_address_line1) ? $request->user_address_line1 : '';
                $user_area = isset($request->user_area) ? $request->user_area : '';
                // $user_address_line2 = isset($request->user_address_line2) ? $request->user_address_line2 : '';
                $user_pincode = isset($request->user_pincode) ? $request->user_pincode : '';

                $is_residential = isset($request->architect_is_residential) ? $request->architect_is_residential : 0;
                $is_commercial_or_office_space = isset($request->architect_is_commercial_or_office_space) ? $request->architect_is_commercial_or_office_space : 0;
                $interior = isset($request->architect_interior) ? $request->architect_interior : 0;
                $exterior = isset($request->architect_exterior) ? $request->architect_exterior : 0;
                $structural_design = isset($request->architect_structural_design) ? $request->architect_structural_design : 0;
                $practicing = isset($request->architect_practicing) ? $request->architect_practicing : 0;
                $brand_using_for_switch = isset($request->architect_brand_using_for_switch) ? $request->architect_brand_using_for_switch : '';

                $whitelion_smart_switches_before = isset($request->architect_whitelion_smart_switches_before) ? $request->architect_whitelion_smart_switches_before : 0;
                $how_many_projects_used_whitelion_smart_switches = isset($request->architect_how_many_projects_used_whitelion_smart_switches) ? $request->architect_how_many_projects_used_whitelion_smart_switches : '';

                $experience_with_whitelion = isset($request->architect_experience_with_whitelion) ? $request->architect_experience_with_whitelion : 0;

                $brand_used_before_home_automation = isset($request->architect_brand_used_before_home_automation) ? $request->architect_brand_used_before_home_automation : '';

                $suggestion = isset($request->architect_suggestion) ? $request->architect_suggestion : '';

                $uploadedFile1 = '';
                $uploadedFile2 = '';
                $uploadedFile3 = '';
                $uploadedFile4 = '';


                if ($request->hasFile('architect_visiting_card')) {
                    $folderPathofFile = '/s/architect';

                    $fileObject1 = $request->file('architect_visiting_card');
                    $extension = $fileObject1->getClientOriginalExtension();

                    $fileName1 = time() . mt_rand(10000, 99999) . '.' . $extension;

                    $destinationPath = public_path($folderPathofFile);

                    $fileObject1->move($destinationPath, $fileName1);

                    if (File::exists(public_path($folderPathofFile . '/' . $fileName1))) {
                        $uploadedFile1 = $folderPathofFile . '/' . $fileName1;
                        //START UPLOAD FILE ON SPACES

                        $spaceUploadResponse = uploadFileOnSpaces(public_path($uploadedFile1), $uploadedFile1);
                        if ($spaceUploadResponse != 1) {
                            $uploadedFile1 = '';
                        } else {
                            unlink(public_path($uploadedFile1));
                        }
                        //END UPLOAD FILE ON SPACES
                    }
                }

                if ($request->hasFile('architect_aadhar_card')) {
                    $folderPathofFile = '/s/architect';
                    $fileObject2 = $request->file('architect_aadhar_card');
                    $extension = $fileObject2->getClientOriginalExtension();

                    $fileName2 = time() . mt_rand(10000, 99999) . '.' . $extension;

                    $destinationPath = public_path($folderPathofFile);

                    $fileObject2->move($destinationPath, $fileName2);

                    if (File::exists(public_path($folderPathofFile . '/' . $fileName2))) {
                        $uploadedFile2 = $folderPathofFile . '/' . $fileName2;
                        //START UPLOAD FILE ON SPACES
                        $spaceUploadResponse = uploadFileOnSpaces(public_path($uploadedFile2), $uploadedFile2);
                        if ($spaceUploadResponse != 1) {
                            $uploadedFile2 = '';
                        } else {
                            unlink(public_path($uploadedFile2));
                        }
                        //END UPLOAD FILE ON SPACES
                    }
                }

                if ($request->hasFile('architect_recording')) {
                    $folderPathofFile = '/s/architect';
                    $fileObject2 = $request->file('architect_recording');
                    $extension = $fileObject2->getClientOriginalExtension();

                    $fileName2 = time() . mt_rand(10000, 99999) . '.' . $extension;

                    $destinationPath = public_path($folderPathofFile);

                    $fileObject2->move($destinationPath, $fileName2);

                    if (File::exists(public_path($folderPathofFile . '/' . $fileName2))) {
                        $uploadedFile3 = $folderPathofFile . '/' . $fileName2;
                        //START UPLOAD FILE ON SPACES
                        $spaceUploadResponse = uploadFileOnSpaces(public_path($uploadedFile3), $uploadedFile3);
                        if ($spaceUploadResponse != 1) {
                            $uploadedFile3 = '';
                        } else {
                            unlink(public_path($uploadedFile3));
                        }
                        //END UPLOAD FILE ON SPACES
                    }
                }

                if ($request->hasFile('architect_pan_card')) {
                    $folderPathofFile = '/s/architect';
                    $fileObject2 = $request->file('architect_pan_card');
                    $extension = $fileObject2->getClientOriginalExtension();

                    $fileName2 = time() . mt_rand(10000, 99999) . '.' . $extension;

                    $destinationPath = public_path($folderPathofFile);

                    $fileObject2->move($destinationPath, $fileName2);

                    if (File::exists(public_path($folderPathofFile . '/' . $fileName2))) {
                        $uploadedFile4 = $folderPathofFile . '/' . $fileName2;
                        //START UPLOAD FILE ON SPACES
                        $spaceUploadResponse = uploadFileOnSpaces(public_path($uploadedFile4), $uploadedFile4);
                        if ($spaceUploadResponse != 1) {
                            $uploadedFile4 = '';
                        } else {
                            unlink(public_path($uploadedFile4));
                        }
                        //END UPLOAD FILE ON SPACES
                    }
                }

                $user_company_id = 1;
                $isMovedFromPrimeToNonPrime = 0;

                if ($request->user_id == 0) {
                    $User = User::where('type', 10000)
                        ->where(function ($query) use ($request) {
                            $query->where('email', $request->user_email)->orWhere('phone_number', $request->user_phone_number);
                        })
                        ->first();

                    if ($User) {
                        $User->type = $request->user_type;
                        $User->reference_type = 'architect';
                        $User->reference_id = 0;
                    } else {
                        $User = new User();
                        $User->created_by = Auth::user()->id;
                        $User->password = Hash::make('111111');
                        $User->last_active_date_time = date('Y-m-d H:i:s');
                        $User->last_login_date_time = date('Y-m-d H:i:s');
                        $User->avatar = 'default.png';
                    }
                    $Architect = new Architect();
                    if ($isSalePerson == 1) {
                        $User->status = 1;
                    } else {
                        if (isset($request->user_status)) {
                            //     if ($request->user_status == 3 || $request->user_status == 4) {
                            //         $User->status = 2;
                            //     } else {
                            //         $User->status = $request->user_status;
                            //     }
                            //     $Architect->status = $request->user_status;
                            $User->status = 6;
                            $Architect->status = 6;
                        } else {
                        }
                    }

                    $isMovedFromPrimeToNonPrime = 1;

                    //$User->is_sent_mail = 0;
                } else {
                    $User = User::find($request->user_id);
                    $Architect = Architect::find($User->reference_id);
                    if (!$Architect) {
                        $response = errorRes('Something went wrong');
                    }

                    if ($uploadedFile1 == '' && $Architect->visiting_card != '') {
                        $uploadedFile1 = $Architect->visiting_card;
                    }
                    if ($uploadedFile2 == '' && $Architect->aadhar_card != '') {
                        $uploadedFile2 = $Architect->aadhar_card;
                    }

                    if ($uploadedFile3 == '') {
                        $uploadedFile3 = $Architect->recording;
                    }

                    if ($uploadedFile4 == '') {
                        $uploadedFile4 = $Architect->pan_card;
                    }

                    

                    $log_data = [];

                    if ($User->first_name != $request->user_first_name) {
                        $new_value = $request->user_first_name;
                        $old_value = $User->first_name;
                        $change_field = "User First Name Change : " . $old_value . " To " . $new_value;
                        
                        $log_value = [];
                        $log_value['field_name'] = "user_first_name";
                        $log_value['new_value'] = $new_value;
                        $log_value['old_value'] = $old_value;
                        $log_value['description'] =  $change_field;

                        array_push($log_data, $log_value);
                    }

                    if ($User->last_name != $request->user_last_name) {
                        $new_value = $request->user_last_name;
                        $old_value = $User->last_name;
                        $change_field = "User Last Name Change : " . $old_value . " To " . $new_value;
                        
                        $log_value = [];
                        $log_value['field_name'] = "user_last_name";
                        $log_value['new_value'] = $new_value;
                        $log_value['old_value'] = $old_value;
                        $log_value['description'] =  $change_field;

                        array_push($log_data, $log_value);
                    }

                    if ($User->house_no != $user_house_no) {
                        $new_value = $user_house_no;
                        $old_value = $User->house_no;
                        $change_field = "User House No Change : " . $old_value . " To " . $new_value;
                        
                        $log_value = [];
                        $log_value['field_name'] = "user_house_no";
                        $log_value['new_value'] = $new_value;
                        $log_value['old_value'] = $old_value;
                        $log_value['description'] =  $change_field;

                        array_push($log_data, $log_value);
                    }

                    if ($User->address_line1 != $user_address_line1) {
                        $new_value = $user_address_line1;
                        $old_value = $User->address_line1;
                        $change_field = "User Address Line 1 Change : " . $old_value . " To " . $new_value;
                        
                        $log_value = [];
                        $log_value['field_name'] = "user_address_line1";
                        $log_value['new_value'] = $new_value;
                        $log_value['old_value'] = $old_value;
                        $log_value['description'] =  $change_field;

                        array_push($log_data, $log_value);
                    }

                    if ($User->area != $user_area) {
                        $new_value = $user_area;
                        $old_value = $User->area;
                        $change_field = "User Area Change : " . $old_value . " To " . $new_value;
                        
                        $log_value = [];
                        $log_value['field_name'] = "user_area";
                        $log_value['new_value'] = $new_value;
                        $log_value['old_value'] = $old_value;
                        $log_value['description'] =  $change_field;

                        array_push($log_data, $log_value);
                    }

                    if ($User->pincode != $user_pincode) {
                        $new_value = $user_pincode;
                        $old_value = $User->pincode;
                        $change_field = "User Pincode Change : " . $old_value . " To " . $new_value;
                        
                        $log_value = [];
                        $log_value['field_name'] = "user_pincode";
                        $log_value['new_value'] = $new_value;
                        $log_value['old_value'] = $old_value;
                        $log_value['description'] =  $change_field;

                        array_push($log_data, $log_value);
                    }

                    if ($User->city_id != $request->user_city_id) {
                        $new_value = $request->user_city_id;
                        $old_value = $User->city_id;
                        $change_field = "User City Change : " . CityList::find($old_value)['name'] . " To " . CityList::find($new_value)['name'];
                        
                        $log_value = [];
                        $log_value['field_name'] = "user_city";
                        $log_value['new_value'] = $new_value;
                        $log_value['old_value'] = $old_value;
                        $log_value['description'] =  $change_field;

                        array_push($log_data, $log_value);
                    }

                    if ($User->status != $request->user_status) {
                        $new_value = $request->user_status;
                        $old_value = $User->status;
                        $change_field = "User Status Change : " . getArchitectsStatus()[$old_value]['name'] . " To " . getArchitectsStatus()[$new_value]['name'];
                        
                        $log_value = [];
                        $log_value['field_name'] = "user_status";
                        $log_value['new_value'] = $new_value;
                        $log_value['old_value'] = $old_value;
                        $log_value['description'] =  $change_field;

                        array_push($log_data, $log_value);
                    }

                    if ($Architect->principal_architect_name != $principal_architect_name) {
                        $new_value = $principal_architect_name;
                        $old_value = $Architect->principal_architect_name;
                        $change_field = "User Principal Architect Name Change : " . $old_value . " To " . $new_value;
                        
                        $log_value = [];
                        $log_value['field_name'] = "principal_architect_name";
                        $log_value['new_value'] = $new_value;
                        $log_value['old_value'] = $old_value;
                        $log_value['description'] =  $change_field;

                        array_push($log_data, $log_value);
                    }

                    if ($Architect->firm_name != $request->architect_firm_name) {
                        $new_value = $principal_architect_name;
                        $old_value = $Architect->principal_architect_name;
                        $change_field = "User Firm Change : " . $old_value . " To " . $new_value;
                        
                        $log_value = [];
                        $log_value['field_name'] = "architect_firm_name";
                        $log_value['new_value'] = $new_value;
                        $log_value['old_value'] = $old_value;
                        $log_value['description'] =  $change_field;

                        array_push($log_data, $log_value);
                    }

                    if ($Architect->sale_person_id != $sale_person_id) {
                        $new_value = $sale_person_id;
                        $old_value = $Architect->sale_person_id;

                        $old_text = User::selectRaw(DB::raw('CONCAT(first_name," ", last_name) AS text'))->where('id', $old_value)->first()['text'];
                        $new_text = User::selectRaw(DB::raw('CONCAT(first_name," ", last_name) AS text'))->where('id', $new_value)->first()['text'];
                        $change_field = "User Sale Person Change : " . $old_text . " To " . $new_text;
                        
                        $log_value = [];
                        $log_value['field_name'] = "sale_person";
                        $log_value['new_value'] = $new_value;
                        $log_value['old_value'] = $old_value;
                        $log_value['description'] =  $change_field;

                        array_push($log_data, $log_value);
                    }

                    if (date('m-d', strtotime($Architect->anniversary_date)) != date('m-d', strtotime($request->architect_anniversary_date))) {
                        $new_value = date('m-d', strtotime($request->architect_anniversary_date));
                        $old_value = date('m-d', strtotime($Architect->anniversary_date));
                        $change_field = "User Anniversary Date Change : " . $old_value . " To " .  $new_value;
                        
                        $log_value = [];
                        $log_value['field_name'] = "anniversary_date";
                        $log_value['new_value'] = $new_value;
                        $log_value['old_value'] = $old_value;
                        $log_value['description'] =  $change_field;

                        array_push($log_data, $log_value);
                    }

                    if (date('m-d', strtotime($Architect->birth_date)) != date('m-d', strtotime($request->architect_birth_date))) {
                        $new_value = date('m-d', strtotime($request->architect_birth_date));
                        $old_value = date('m-d', strtotime($Architect->birth_date));
                        $change_field = "User Birth Date Change : " . $old_value . " To " .  $new_value;
                        
                        $log_value = [];
                        $log_value['field_name'] = "birth_date";
                        $log_value['new_value'] = $new_value;
                        $log_value['old_value'] = $old_value;
                        $log_value['description'] =  $change_field;

                        array_push($log_data, $log_value);
                    }

                    foreach($log_data as $log_value) {
                        $user_log = new UserLog();
                        $user_log->user_id = Auth::user()->id;
                        $user_log->log_type = "ARCHITECT-LOG";
                        $user_log->field_name = $log_value['field_name'];
                        $user_log->old_value = $log_value['old_value'];
                        $user_log->new_value = $log_value['new_value'];
                        $user_log->reference_type = "Architect";
                        $user_log->reference_id = $request->user_id;
                        $user_log->transaction_type = "Architect Edit";
                        $user_log->description = $log_value['description'];
                        $user_log->source = $request->app_source;
                        $user_log->entryby = Auth::user()->id;
                        $user_log->entryip = $request->ip();
                        $user_log->save();
                    }

                    $User->status = $request->user_status;
                    $Architect->status = $request->user_status;
                    // if ($isSalePerson == 0) {
                    //     if (isset($request->user_status)) {
                    //         if ($request->user_status == 3 || $request->user_status == 4) {
                    //             $User->status = 2;
                    //         } else {
                    //             $User->status = $request->user_status;
                    //         }
                    //         $Architect->status = $request->user_status;
                    //     } else {
                    //         $User->status = 2;
                    //         $Architect->status = 2;
                    //     }
                    // }

                    if ($Architect->type != $request->user_type) {
                        $isMovedFromPrimeToNonPrime = 1;
                    }
                }
                $User->first_name = $request->user_first_name;
                $User->last_name = $request->user_last_name;
                $User->email = $email;
                $User->dialing_code = '+91';
                $User->phone_number = $request->user_phone_number;
                $User->ctc = 0;
                if ($request->user_type == 202 || $request->user_id == 0) {
                    $PrivilegeJSON = [];
                    $PrivilegeJSON['dashboard'] = 1;
                    $User->privilege = json_encode($PrivilegeJSON);
                }
                $User->house_no = $user_house_no;
                $User->address_line1 = $user_address_line1;
                $User->address_line2 = '';
                $User->area = $user_area;
                $User->pincode = $user_pincode;
                $User->country_id = 0;
                $User->state_id = 0;
                $User->city_id = $request->user_city_id;
                $User->company_id = $user_company_id;
                $User->type = $request->user_type;
                $User->reference_type = 0;
                $User->reference_id = 0;
                if ($request->user_status == 1) {
                    $User->joining_date = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s')));
                }
                $User->save();

                if ($isMovedFromPrimeToNonPrime == 1) {
                    $Architect->prime_nonprime_date = date('Y-m-d H:i:s');
                }
                $Architect->user_id = $User->id;
                $Architect->type = $request->user_type;
                $Architect->firm_name = $request->architect_firm_name;
                $Architect->sale_person_id = $sale_person_id;
                $Architect->is_residential = (int) $is_residential;
                $Architect->is_commercial_or_office_space = (int) $is_commercial_or_office_space;
                $Architect->interior = (int) $interior;
                $Architect->exterior = (int) $exterior;
                $Architect->structural_design = (int) $structural_design;
                $Architect->practicing = (int) $practicing;
                $Architect->brand_using_for_switch = $brand_using_for_switch;
                $Architect->visiting_card = $uploadedFile1;
                $Architect->aadhar_card = $uploadedFile2;
                $Architect->recording = $uploadedFile3;
                $Architect->pan_card = $uploadedFile4;
                // $Architect->whitelion_smart_switches_before = $whitelion_smart_switches_before;
                // $Architect->how_many_projects_used_whitelion_smart_switches = $how_many_projects_used_whitelion_smart_switches;
                // $Architect->brand_used_before_home_automation = $brand_used_before_home_automation;
                // $Architect->experience_with_whitelion = $experience_with_whitelion;
                // $Architect->suggestion = $suggestion;
                $Architect->principal_architect_name = $principal_architect_name;
                $Architect->instagram_link = $instagram_link;

                if (Auth::user()->type == 9) {
                    if (isset($request->architect_note)) {
                        $Architect->tele_note = $request->architect_note;
                    } else {
                        $Architect->tele_note = '';
                    }

                    if (isset($request->architect_instagram)) {
                        $Architect->instagram_link = $request->architect_instagram;
                    } else {
                        $Architect->instagram_link = '';
                    }
                }

                if (Auth::user()->type == 1) {
                    if (isset($request->architect_note)) {
                        $Architect->hod_note = $request->architect_note;
                    } else {
                        $Architect->hod_note = '';
                    }
                }

                if (isset($request->user_status)) {
                    if ($request->user_status == 0) {
                        $Architect->data_verified = 0;
                        $Architect->tele_verified = 0;
                        $Architect->missing_data = 0;
                    } elseif ($request->user_status == 1) {
                        $Architect->data_verified = 1;
                        $Architect->tele_verified = 1;
                        $Architect->missing_data = 0;
                    } elseif ($request->user_status == 2) {
                        $Architect->data_verified = 0;
                        $Architect->tele_verified = 0;
                        $Architect->missing_data = 0;
                    } elseif ($request->user_status == 3) {
                        $Architect->data_verified = 0;
                        $Architect->tele_verified = 0;
                        $Architect->missing_data = 1;
                    } elseif ($request->user_status == 4) {
                        $Architect->data_verified = 1;
                        $Architect->tele_verified = 1;
                        $Architect->missing_data = 0;
                    }
                } else {
                    $Architect->data_verified = 0;
                    $Architect->tele_verified = 0;
                    $Architect->missing_data = 0;
                }

                $Architect->data_not_verified = 0;
                $Architect->tele_not_verified = 0;

                if ($request->architect_birth_date != '') {
                    $Architect->birth_date = date('Y-m-d H:i:s', strtotime($request->architect_birth_date."-1980"));
                } else {
                    $Architect->birth_date = null;
                }
                if ($request->architect_anniversary_date != '') {
                    $Architect->anniversary_date = date('Y-m-d H:i:s', strtotime($request->architect_anniversary_date."-1980"));
                } else {
                    $Architect->anniversary_date = null;
                }
                $response2 = [];
                $response2 = $request->user_id . ' - ' . $request->user_type . ' - ' . $Architect->converted_prime;
                if ($request->user_id != 0 && $request->user_type == 202 && $Architect->converted_prime == 0) {
                    $converted_prime = 0;
                } else {
                    $converted_prime = 1;
                }

                if ($request->user_type == 202) {
                    $Architect->converted_prime = 1;
                }

                    $Architect->source_type = $source_type;
                    $Architect->source_type_value = $source_type_value;
                $Architect->added_by = Auth::user()->id;

                if ($request->user_status == 1) {
                    $Architect->joining_date = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s')));
                }

                if ($request->user_id == 0) {
                    $Architect->entryby = Auth::user()->id;
                    $Architect->entryip = $request->ip();
                } else {
                    $Architect->updateby = Auth::user()->id;
                    $Architect->updateip = $request->ip();
                }

                $Architect->save();

                $User->reference_type = 'architect';
                $User->reference_id = $Architect->id;
                $User->save();

                // if($user){
                //     $timeline = array();
                //     $timeline['user_id'] = $User_status->id;
                //     $timeline['log_type'] = "user";
                //     $timeline['field_name'] = "status";
                //     $timeline['old_value'] = $oldStatus;
                //     $timeline['new_value'] = $newStatus;
                //     $timeline['reference_type'] = "user";
                //     $timeline['reference_id'] = "0";
                //     $timeline['transaction_type'] = "update";
                //     $timeline['description'] = "User status changed from  " . $userStatus[$oldStatus]['name'] . " to " . $userStatus[$newStatus]['name'] . " by " . Auth::user()->first_name . " " . Auth::user()->last_name;
                //     $timeline['source'] = $entry_source;
                //     $timeline['ip'] = $ip;
                //     saveUserLog($timeline);
                // }

                if ($isSalePerson == 0) {
                    if (isset($request->user_status)) {
                        saveUserStatus($User->id, $request->user_status, $request->ip());
                    } else {
                        saveUserStatus($User->id, 2, $request->ip());
                    }
                }

                if ($User && $Architect) {
                    if ($request->user_id == 0) {
                        $UserContact = new UserContact();

                        $UserContact->user_id = $User->id;
                        $UserContact->contact_tag_id = 0;
                        $UserContact->first_name = $request->user_first_name;
                        $UserContact->last_name = $request->user_last_name;
                        $UserContact->phone_number = $request->user_phone_number;
                        $UserContact->alernate_phone_number = 0;
                        $UserContact->email = $request->user_email;
                        $UserContact->type = $request->user_type;
                        $UserContact->type_detail = 'user-' . $request->user_type . '-' . $User->id;

                        $UserContact->save();

                        if ($UserContact) {
                            $user_update = User::find($User->id);
                            $user_update->main_contact_id = $UserContact->id;
                            $user_update->save();
                        }
                    }

                    if (($request->architect_source_type != null && $request->architect_source_type == 'user-201') || $request->architect_source_type == 'user-202' || $request->architect_source_type == 'user-301' || $request->architect_source_type == 'user-302' || $request->architect_source_type == 'user-101' || $request->architect_source_type == 'user-102' || $request->architect_source_type == 'user-103' || $request->architect_source_type == 'user-104' || $request->architect_source_type == 'user-105') {
                        if ($request->user_id == 0) {
                            $Main_source = User::where('id', $Architect->source_type_value)->first();

                            $UserContact = new UserContact();

                            $UserContact->user_id = $User->id;
                            $UserContact->contact_tag_id = 0;
                            $UserContact->first_name = $Main_source->first_name;
                            $UserContact->last_name = $Main_source->last_name;
                            $UserContact->phone_number = $Main_source->phone_number;
                            $UserContact->alernate_phone_number = 0;
                            $UserContact->email = $Main_source->email;
                            $UserContact->type = $Main_source->type;
                            $UserContact->type_detail = 'user-' . $Architect->type . '-' . $Architect->source_type_value;

                            $UserContact->save();
                        } else {

                            $Main_source = User::where('id', $Architect->source_type_value)->first();

                            $UserContact = UserContact::select('*')
                                ->where('user_id', $User->id)
                                ->where('contact_tag_id', 0)
                                ->first();

                            if($UserContact){
                                $UserContact->user_id = $User->id;
                                $UserContact->contact_tag_id = 0;
                                $UserContact->first_name = $Main_source->first_name;
                                $UserContact->last_name = $Main_source->last_name;
                                $UserContact->phone_number = $Main_source->phone_number;
                                $UserContact->alernate_phone_number = 0;
                                $UserContact->email = $Main_source->email;
                                $UserContact->type = $Main_source->type;
                                $UserContact->type_detail = 'user-' . $Architect->type . '-' . $Architect->source_type_value;
    
                                $UserContact->save();
                            } else {
                                    $UserContact = new UserContact();
                                $UserContact->user_id = $User->id;
                                $UserContact->contact_tag_id = 0;
                                $UserContact->first_name = $Main_source->first_name;
                                $UserContact->last_name = $Main_source->last_name;
                                $UserContact->phone_number = $Main_source->phone_number;
                                $UserContact->alernate_phone_number = 0;
                                $UserContact->email = $Main_source->email;
                                $UserContact->type = $Main_source->type;
                                $UserContact->type_detail = 'user-' . $Architect->type . '-' . $Architect->source_type_value;

                                $UserContact->save();
                            }
                        }
                    }
                }

                $debugLog = [];
                if ($request->user_id != 0) {
                    $debugLog['name'] = 'architect-edit';
                    $debugLog['description'] = 'user #' . $User->id . '(' . $User->first_name . ' ' . $User->last_name . ') has been updated ';
                    $response = successRes('Successfully saved user');
                    $response['cov'] = $converted_prime;
                    $response['cov2'] = $response2;
                    $response['user_id'] = $User->id;
                } else {
                    $mobileNotificationTitle = 'New Architect Create';
                    $mobileNotificationMessage = 'New Architect ' . $User->first_name . ' ' . $User->last_name . ' Added By ' . Auth::user()->first_name . ' ' . Auth::user()->last_name;
                    $notificationUserids = getParentSalePersonsIds($Architect->sale_person_id);
                    $notificationUserids[] = $Architect->sale_person_id;
                    $UsersNotificationTokens = UsersNotificationTokens($notificationUserids);
                    sendNotificationTOAndroid($mobileNotificationTitle, $mobileNotificationMessage, $UsersNotificationTokens, 'Architect', $User);

					$user_log = new UserLog();
                    $user_log->user_id = Auth::user()->id;
                    $user_log->log_type = "ARCHITECT-LOG";
                    $user_log->field_name = '';
                    $user_log->old_value = '';
                    $user_log->new_value = '';
                    $user_log->reference_type = "Architect";
                    $user_log->reference_id = $User->id;
                    $user_log->transaction_type = "Architect Create";
                    $user_log->description = 'New Architect Created';
                    $user_log->source = $request->app_source;
                    $user_log->entryby = Auth::user()->id;
                    $user_log->entryip = $request->ip();
                    $user_log->save();

                    $debugLog['name'] = 'architect-add';
                    $debugLog['description'] = 'user #' . $User->id . '(' . $User->first_name . ' ' . $User->last_name . ') has been added ';
                    $response = successRes('Successfully added user');
                    $response['cov'] = $converted_prime;
                    $response['cov2'] = $response2;
                    $response['user_id'] = $User->id;
                }
                saveDebugLog($debugLog);

                if (($request->user_id == 0 && $request->user_type == 202) || ($request->user_id != 0 && $request->user_type == 202 && $converted_prime == 0)) {
                    // ARCHITECT JOINING BONUS
                    $pointValue = 50;
                    $Architect->total_point = $Architect->total_point + $pointValue;
                    $Architect->total_point_current = $Architect->total_point_current + $pointValue;
                    $Architect->save();

                    $debugLog = [];
                    $debugLog['for_user_id'] = $Architect->user_id;
                    $debugLog['name'] = 'point-gain';
                    $debugLog['points'] = $pointValue;
                    $debugLog['description'] = $pointValue . ' Point gained joining bonus ';
                    $debugLog['type'] = '';
                    saveCRMUserLog($debugLog);

                    $configrationForNotify = configrationForNotify();

                    $params = [];
                    $params['from_name'] = $configrationForNotify['from_name'];
                    $params['from_email'] = $configrationForNotify['from_email'];
                    $params['to_email'] = $User->email;
                    $params['to_name'] = $configrationForNotify['to_name'];
                    $params['bcc_email'] = ['sales@whitelion.in', 'sc@whitelion.in', 'poonam@whitelion.in'];
                    $params['subject'] = 'Welcome to the Whitelion';
                    $params['user_first_name'] = $User->first_name;
                    $params['user_last_name'] = $User->last_name;
                    $params['user_mobile'] = $User->phone_number;
                    $params['credentials_email'] = $User->email;
                    $params['credentials_password'] = '111111';
                    $query = CRMHelpDocument::query();
                    $query->where('status', 1);
                    $query->where('type', 202);
                    $query->limit(30);
                    $query->orderBy('publish_date_time', 'desc');
                    $helpDocuments = $query->get();
                    $params['help_documents'] = json_decode(json_encode($helpDocuments), true);

                    if (Config::get('app.env') == 'local') {
                        // SEND MAIL
                        $params['to_email'] = $configrationForNotify['test_email'];
                        $params['bcc_email'] = $configrationForNotify['test_email_bcc'];
                    }

                    //TEMPLATE 6
                    Mail::send('emails.signup_architect', ['params' => $params], function ($m) use ($params) {
                        $m->from($params['from_email'], $params['from_name']);
                        $m->bcc($params['bcc_email']);
                        $m->to($params['to_email'], $params['to_name'])->subject($params['subject']);

                        foreach ($params['help_documents'] as $new_helpDocument) {
                            $fileName = preg_replace('![^a-z0-9]+!i', '-', $new_helpDocument['file_name']);
                            $fileExtension = explode('.', $new_helpDocument['file_name']);
                            $fileExtension = end($fileExtension);
                            $fileName = $fileName . '.' . $fileExtension;

                            // if (is_file($new_helpDocument['title'])) {
                            $m->attach(getSpaceFilePath($new_helpDocument['file_name']), [
                                'as' => $fileName,
                            ]);
                            // }
                        }
                    });

                    $helpDocument = CRMHelpDocument::query();
					$helpDocument->where('status', 1);
					$helpDocument->where('type', 202);
					$helpDocument->orderBy('publish_date_time', "desc");
					$helpDocument = $helpDocument->first();
                    //TEMPLATE 7
                    $whatsapp_controller = new WhatsappApiContoller();
                    $perameater_request = new Request();
                    $perameater_request['q_whatsapp_massage_mobileno'] = $User->phone_number;
                    $perameater_request['q_whatsapp_massage_template'] = 'for_architect_download_mobileapp';
					$perameater_request['q_whatsapp_massage_attechment'] = $helpDocument ? getSpaceFilePath($helpDocument->file_name) : '';
                    $perameater_request['q_broadcast_name'] = $User->first_name . ' ' . $User->last_name;
                    $perameater_request['q_whatsapp_massage_parameters'] = array(
						'data[0]' => $User->email,
						'data[1]' => '111111'
					);
                    $wp_response = $whatsapp_controller->sendTemplateMessage($perameater_request);
                    $response['whatsapp'] = $wp_response;
                }
            }
        }

        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }

	public function saveCategory(Request $request)
	{

		$rules = array();

		$rules['architect_id'] = 'required';
		$rules['category_id'] = 'required';

		$customMessage = array();
		$customMessage['architect_id.required'] = "Invalid parameters";
		$customMessage['category_id.required'] = "Invalid category";

		$validator = Validator::make($request->all(), $rules, $customMessage);

		if ($validator->fails()) {

			$response = array();
			$response['status'] = 0;
			$response['msg'] = "The request could not be understood by the server due to malformed syntax";
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();
		} else {

			$Architect = Architect::where('user_id', $request->architect_id)->first();
			if ($Architect) {

				$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
				$isSalePerson = isSalePerson();
				$isChannelPartner = isChannelPartner(Auth::user()->type);
				$isMarketingDispatcherUser = isMarketingDispatcherUser();

				if ($isAdminOrCompanyAdmin == 1 || ($isSalePerson == 1 && $Architect->sale_person_id == Auth::user()->id) || ($isChannelPartner != 0 && $Architect->added_by == Auth::user()->id)) {

					$ArchitectCategory = ArchitectCategory::find($request->category_id);
					if ($ArchitectCategory || $request->category_id == 0) {
						$Architect->category_id = $request->category_id;
						$Architect->save();
						$response = successRes("Successfully updated architect");
					} else {
						$response = errorRes("Invalid access");
					}
				} else {
					$response = errorRes("Invalid access");
				}
			} else {

				$response = errorRes("Invalid architect");
			}
		}

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function detail(Request $request)
	{

		$User = User::with(array('city' => function ($query) {
			$query->select('id', 'name');
		}, 'company' => function ($query) {
			$query->select('id', 'name');
		}))->where('id', $request->id)->whereIn('type', array(201, 202))->first();
		if ($User) {

            if($User['house_no'] == null) {
                $User['house_no'] = ""; 
            }

            if($User['area'] == null) {
                $User['area'] = ""; 
            }

            if($User['duplicate_from'] == null) {
                $User['duplicate_from'] = ""; 
            }
            if($User['joining_date'] == null) {
                $User['joining_date'] = ""; 
            }
            if($User['firm_name'] == null) {
                $User['firm_name'] = ""; 
            }

			$Architect = Architect::find($User->reference_id);

			if ($Architect) {
				$Architect = json_decode(json_encode($Architect), true);

				$User1 = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
				$User1->where('id', $Architect['added_by']);
				$User1->limit(1);
				$User1 = $User1->first();

				if ($User1) {
					$Architect['added_by'] = array();
					$Architect['added_by']['id'] = $User1->id;
					$Architect['added_by']['text'] = $User1->full_name;
				}



				$User->status_lable = getArchitectsStatus()[$User->status]['header_code'];

				$source_type_pieces = explode("-", $Architect['source_type']);

				if ($source_type_pieces[0] == "user") {

					if (isChannelPartner($Architect['source_type_value']) != 0) {

						$User1 = User::select('users.id', DB::raw("channel_partner.firm_name"));
						$User1->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
						$User1->where('id', $Architect['source_type_value']);
						$User1->limit(1);
						$User1 = $User1->first();
						if ($User1) {

							$Architect['source'] = array();
							$Architect['source']['id'] = $User1->id;
							$Architect['source']['text'] = $User1->firm_name;
						}
					} else {

						$User1 = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
						$User1->where('id', $Architect['source_type_value']);
						$User1->limit(1);
						$User1 = $User1->first();
						if ($User1) {

							$Architect['source'] = array();
							$Architect['source']['id'] = $User1->id;
							$Architect['source']['text'] = $User1->full_name;
						}
					}
				}

				$isSalePerson = (Auth::user()->type == 2) ? 1 : 0;

				$SalePersonsIds = getChildSalePersonsIds(Auth::user()->id);

				if ($isSalePerson == 0 || ($isSalePerson == 1 && in_array($Architect['sale_person_id'], $SalePersonsIds))) {

					if ($Architect['visiting_card'] != "" && $Architect['visiting_card'] != null) {
						$Architect['visiting_card'] = getSpaceFilePath($Architect['visiting_card']);
					} else {
                        $Architect['visiting_card'] = "";
                    }

					if ($Architect['aadhar_card'] != "" && $Architect['aadhar_card'] != null) {
						$Architect['aadhar_card'] = getSpaceFilePath($Architect['aadhar_card']);
					} else {
                        $Architect['aadhar_card'] = "";
                    }

					$salePerson = User::query();
					$salePerson->select('users.id as id', DB::raw('CONCAT(first_name," ", last_name) AS text'));
					$salePerson = $salePerson->find($Architect['sale_person_id']);

					
					$User = json_decode(json_encode($User), true);

					$City = CityList::find($User['city_id']);
					$State = StateList::find($City->state_id);
					$Country = CountryList::find($City->country_id);

					$User['country']['id'] = $Country->id;
					$User['country']['name'] = $Country->name;

					$User['state']['id'] = $State->id;
					$User['state']['name'] = $State->name;

                    $User['contacts'] = getUserContactList($User['id'])['data'];
                    $User['updates'] = getUserNoteList($User['id'])['data'];
                    $User['files'] = getUserFileList($User['id'])['data'];

                    $User['calls'] = getUserAllOpenList($User['id'])['call_data'];
                    $User['meetings'] = getUserAllOpenList($User['id'])['meeting_data'];
                    $User['tasks'] = getUserAllOpenList($User['id'])['task_data'];
                    $User['max_open_actions'] = getUserAllOpenList($User['id'])['max_open_actions'];

                    $User['calls_closed'] = getUserAllCloseList($User['id'])['close_call_data'];
                    $User['meetings_closed'] = getUserAllCloseList($User['id'])['close_meeting_data'];
                    $User['tasks_closed'] = getUserAllCloseList($User['id'])['close_task_data'];
                    $User['max_close_actions'] = getUserAllCloseList($User['id'])['max_close_actions'];

					$response = successRes("Successfully get user");
					$response['data'] = $User;
					$response['data']['architect'] = $Architect;
                    if($salePerson == null) {
                        $response['data']['architect']['sale_person'] = array();
                        $response['data']['architect']['sale_person']['text'] = "";
                    } else {
                        $response['data']['architect']['sale_person'] = $salePerson;
                    }

                    $query = Lead::query();
                    $query->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                    $query->leftJoin('lead_sources as source', 'source.lead_id', '=', 'leads.id');
                    $query->leftJoin('users as lead_ele', 'lead_ele.id', '=', 'leads.architect');
                    $query->leftJoin('users as source_user', 'source_user.id', '=', 'source.source');
                    $query->where(function ($query1) use ($request) {
                        if ($request->is_arc == 1) {
                            $query1->orwhere('leads.architect', $request->id);
                        }
                        $query1->orwhere('lead_sources.source', $request->id);
                    });
                    $Lead_ids = $query->distinct()->pluck('leads.id');

                    $Status_count = Lead::query();
                    $Status_count->selectRaw('SUM(CASE WHEN leads.status IN (1, 2, 3, 4, 100, 101, 102) THEN 1 ELSE 0 END) as Running_lead, SUM(CASE WHEN leads.status = 103 THEN 1 ELSE 0 END) as Won_lead, SUM(CASE WHEN leads.status IN (5, 104) THEN 1 ELSE 0 END) as Lost_lead');
                    $Status_count->whereIn('leads.id',  $Lead_ids);
                    $Status_count = $Status_count->first();
                    
                    if($Status_count->Running_lead == null) {
                        $response['data']['architect']['running_lead'] = 0;
                    } else {
                        $response['data']['architect']['running_lead'] = $Status_count->Running_lead;
                    }

                    if($Status_count->Won_lead == null) {
                        $response['data']['architect']['won_lead'] = 0;
                    } else {
                        $response['data']['architect']['won_lead'] = $Status_count->Won_lead;
                    }

                    if($Status_count->Lost_lead == null) {
                        $response['data']['architect']['lost_lead'] = 0;
                    } else {
                        $response['data']['architect']['lost_lead'] = $Status_count->Lost_lead;
                    }

                    if($Status_count->Running_lead == null && $Status_count->Won_lead == null && $Status_count->Lost_lead == null) { 
                        $response['data']['architect']['total_lead'] = 0;
                    } else {
                        $response['data']['architect']['total_lead'] = intval($Status_count->Running_lead) + intval($Status_count->Won_lead) + intval($Status_count->Lost_lead);
                    }


					$response['data']['architect']['inquiry_running'] = $this->runningInquiry($response['data']['id']);
					$response['data']['architect']['inquiry_material_sent'] = $this->materialSentInquiry($response['data']['id']);
					$response['data']['architect']['inquiry_rejected'] = $this->rejectedInquiry($response['data']['id']);
					$response['data']['architect']['inquiry_non_potential'] = $this->nonPotentialInquiry($response['data']['id']);

                    
				} else {
					$response = errorRes("Invalid id");
				}
			} else {
				$response = errorRes("Invalid id");
			}
		} else {
			$response = errorRes("Invalid id");
		}
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	function runningInquiry($userId)
	{

		$inquiryStatus = getInquiryStatus();

		$query = Inquiry::query();
		$query->where(function ($query2) use ($userId) {

			$query2->where(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type', array("user-201", "user-202"));
				$query3->where('inquiry.source_type_value', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_1', array("user-201", "user-202"));
				$query3->where('inquiry.source_type_value_1', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_2', array("user-201", "user-202"));
				$query3->where('inquiry.source_type_value_2', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_3', array("user-201", "user-202"));
				$query3->where('inquiry.source_type_value_3', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_4', array("user-201", "user-202"));
				$query3->where('inquiry.source_type_value_4', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->where('inquiry.architect', $userId);
			});
		});

		$statusArray = isset($inquiryStatus[201]['for_sales_ids']) ? $inquiryStatus[201]['for_sales_ids'] : array(0);
		$query->whereIn('inquiry.status', $statusArray);

		return $query->count();
	}

	function materialSentInquiry($userId)
	{
		$inquiryStatus = getInquiryStatus();

		$query = Inquiry::query();
		$query->where(function ($query2) use ($userId) {

			$query2->where(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type', array("user-201", "user-202"));
				$query3->where('inquiry.source_type_value', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_1', array("user-201", "user-202"));
				$query3->where('inquiry.source_type_value_1', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_2', array("user-201", "user-202"));
				$query3->where('inquiry.source_type_value_2', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_3', array("user-201", "user-202"));
				$query3->where('inquiry.source_type_value_3', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_4', array("user-201", "user-202"));
				$query3->where('inquiry.source_type_value_4', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->where('inquiry.architect', $userId);
			});
		});

		$statusArray = isset($inquiryStatus[9]['for_sales_ids']) ? $inquiryStatus[9]['for_sales_ids'] : array(0);
		$query->whereIn('inquiry.status', $statusArray);

		return $query->count();
	}

	function rejectedInquiry($userId)
	{
		$inquiryStatus = getInquiryStatus();

		$query = Inquiry::query();
		$query->where(function ($query2) use ($userId) {

			$query2->where(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type', array("user-201", "user-202"));
				$query3->where('inquiry.source_type_value', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_1', array("user-201", "user-202"));
				$query3->where('inquiry.source_type_value_1', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_2', array("user-201", "user-202"));
				$query3->where('inquiry.source_type_value_2', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_3', array("user-201", "user-202"));
				$query3->where('inquiry.source_type_value_3', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_4', array("user-201", "user-202"));
				$query3->where('inquiry.source_type_value_4', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->where('inquiry.architect', $userId);
			});
		});

		$statusArray = isset($inquiryStatus[102]['for_sales_ids']) ? $inquiryStatus[102]['for_sales_ids'] : array(0);
		$query->whereIn('inquiry.status', $statusArray);

		return $query->count();
	}

	function nonPotentialInquiry($userId)
	{
		$inquiryStatus = getInquiryStatus();

		$query = Inquiry::query();
		$query->where(function ($query2) use ($userId) {

			$query2->where(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type', array("user-201", "user-202"));
				$query3->where('inquiry.source_type_value', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_1', array("user-201", "user-202"));
				$query3->where('inquiry.source_type_value_1', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_2', array("user-201", "user-202"));
				$query3->where('inquiry.source_type_value_2', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_3', array("user-201", "user-202"));
				$query3->where('inquiry.source_type_value_3', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->whereIn('inquiry.source_type_4', array("user-201", "user-202"));
				$query3->where('inquiry.source_type_value_4', $userId);
			});

			$query2->orWhere(function ($query3) use ($userId) {

				$query3->where('inquiry.architect', $userId);
			});
		});

		$statusArray = isset($inquiryStatus[101]['for_sales_ids']) ? $inquiryStatus[101]['for_sales_ids'] : array(0);
		$query->whereIn('inquiry.status', $statusArray);

		return $query->count();
	}

	public function searchUser(Request $request)
	{

		$isArchitect = isArchitect();
		$isSalePerson = isSalePerson();
		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		$isMarketingDispatcherUser = isMarketingDispatcherUser();

		if (isset($request->user_id) && $request->user_id != "") {

			if ($isSalePerson == 1) {

				$salePerson = SalePerson::select('cities')->where('user_id', Auth::user()->id)->first();
				$cities = array();
				if ($salePerson) {

					$cities = explode(",", $salePerson->cities);
				} else {
					$cities = array(0);
				}
			}

			$User = User::query();
			$User->select('users.id', 'users.first_name', 'users.last_name', 'users.phone_number');
			$User->where('users.status', 1);
			$User->whereIn('users.type', array(301, 302));
			if ($isSalePerson == 1) {

				$User->leftJoin('electrician', 'electrician.user_id', '=', 'users.id');

				$User->where(function ($query) use ($cities) {

					$query->whereIn('users.city_id', $cities);

					// $query->orWhere('electrician.sale_person_id', Auth::user()->id);

				});
			}

			$User->where('users.id', $request->user_id);
			$User->limit(1);
			$UserResponse = $User->get();
		} else {

			if ($isSalePerson == 1 && ($request->source_type == 301 || $request->source_type == 302)) {

				$salePerson = SalePerson::select('cities')->where('user_id', Auth::user()->id)->first();
				$cities = array();
				if ($salePerson) {

					$cities = explode(",", $salePerson->cities);
				} else {
					$cities = array(0);
				}

				//$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);

				$UserResponse = array();
				$q = $request->q;
				$User = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
				$User->where('users.type', $request->source_type);
				$User->leftJoin('electrician', 'electrician.user_id', '=', 'users.id');
				$User->where('users.status', 1);
				//$User->whereIn('electrician.sale_person_id', $childSalePersonsIds);
				$User->whereIn('users.city_id', $cities);
				//$User->where('users.city_id', Auth::user()->city_id);
				$User->where(function ($query) use ($q) {
					$query->where('users.first_name', 'like', '%' . $q . '%');
					$query->orWhere('users.last_name', 'like', '%' . $q . '%');
				});
				$User->limit(5);
				$User = $User->get();

				if (count($User) > 0) {
					foreach ($User as $User_key => $User_value) {
						$UserResponse[$User_key]['id'] = $User_value['id'];
						$UserResponse[$User_key]['text'] = $User_value['full_name'];
					}
				}
			} else if (isChannelPartner($request->source_type) != 0) {

				if ($isSalePerson == 1) {

					$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);

					$salePerson = SalePerson::select('cities')->where('user_id', Auth::user()->id)->first();
					$cities = array();
					if ($salePerson) {

						$cities = explode(",", $salePerson->cities);
					} else {
						$cities = array(0);
					}
				}

				$UserResponse = array();
				$q = $request->q;
				$User = User::select('users.id', DB::raw("channel_partner.firm_name"));
				$User->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
				$User->where('users.status', 1);
				$User->where('users.type', $request->source_type);
				if ($isSalePerson == 1) {

					$User->where(function ($query) use ($cities, $childSalePersonsIds) {

						$query->whereIn('users.city_id', $cities);

						$query->orWhere(function ($query2) use ($childSalePersonsIds) {
							foreach ($childSalePersonsIds as $key => $value) {
								if ($key == 0) {
									$query2->whereRaw('FIND_IN_SET("' . $value . '",channel_partner.sale_persons)>0');
								} else {
									$query2->orWhereRaw('FIND_IN_SET("' . $value . '",channel_partner.sale_persons)>0');
								}
							}
						});
					});
				}

				$User->where(function ($query) use ($q) {
					$query->where('channel_partner.firm_name', 'like', '%' . $q . '%');
				});
				$User->limit(5);
				$User = $User->get();

				if (count($User) > 0) {
					foreach ($User as $User_key => $User_value) {
						$UserResponse[$User_key]['id'] = $User_value['id'];
						$UserResponse[$User_key]['text'] = $User_value['firm_name'];
					}
				}
			} else {

				$UserResponse = array();
				$q = $request->q;
				$User = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
				$User->where('users.status', 1);
				$User->where('users.type', $request->source_type);
				$User->where(function ($query) use ($q) {
					$query->where('users.first_name', 'like', '%' . $q . '%');
					$query->orWhere('users.last_name', 'like', '%' . $q . '%');
				});
				$User->limit(5);
				$User = $User->get();

				if (count($User) > 0) {
					foreach ($User as $User_key => $User_value) {
						$UserResponse[$User_key]['id'] = $User_value['id'];
						$UserResponse[$User_key]['text'] = $User_value['full_name'];
					}
				}
			}
		}

		$response = array();
		$response['results'] = $UserResponse;
		$response['pagination']['more'] = false;
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	function export(Request $request)
	{

		$isSalePerson = isSalePerson();
		$isChannelPartner = isChannelPartner(Auth::user()->type);
		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		if ($isSalePerson == 1) {
			$SalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
		}
		$columns = array(
			'users.id',
			'users.first_name',
			'users.last_name',
			'users.email',
			'users.dialing_code',
			'users.phone_number',
			'users.status',
			'users.created_at',
			'architect.firm_name',
			'sale_person.first_name as sale_person_first_name',
			'sale_person.last_name  as sale_person_last_name',

		);

		$query = Architect::query();
		$query->select($columns);
		$query->leftJoin('users', 'users.id', '=', 'architect.user_id');
		$query->leftJoin('users as sale_person', 'architect.sale_person_id', '=', 'sale_person.id');
		$query->where('architect.type', $request->type);
		if ($isAdminOrCompanyAdmin == 1) {
		} else if ($isSalePerson == 1) {
			$query->whereIn('architect.sale_person_id', $SalePersonsIds);
		} else if ($isChannelPartner != 0) {
			$query->where('architect.added_by', Auth::user()->id);
		}

		$query->orderBy('architect.id', 'desc');
		$data = $query->get();

		if ($request->type == 201) {
			$headers = array("#ID", "Firstname", "Lastname", "Phone", "Status", "Created", "Firmname", "SalePerson");
		} else {
			$headers = array("#ID", "Firstname", "Lastname", "Email", "Phone", "Status", "Created", "Firmname", "SalePerson");
		}

		header('Content-Type: text/csv');
		if ($request->type == 201) {
			header('Content-Disposition: attachment; filename="architects-non-prime.csv"');
		} else {
			header('Content-Disposition: attachment; filename="architects-prime.csv"');
		}
		$fp = fopen('php://output', 'wb');

		fputcsv($fp, $headers);

		foreach ($data as $key => $value) {

			$createdAt = convertDateTime($value->created_at);
			$status = $value->status;
			if ($status == 0) {
				$status = "Inactive";
			} else if ($status == 1) {
				$status = "Active";
			} else if ($status == 2) {
				$status = "Blocked";
			}

			if ($request->type == 201) {

				$lineVal = array(
					$value->id,
					$value->first_name,
					$value->last_name,
					$value->dialing_code . " " . $value->phone_number,
					$status,
					$createdAt,
					$value->firm_name,
					$value->sale_person_first_name . ' ' . $value->sale_person_last_name,

				);
			} else if ($request->type == 202) {

				$lineVal = array(
					$value->id,
					$value->first_name,
					$value->last_name,
					$value->email,
					$value->dialing_code . " " . $value->phone_number,
					$status,
					$createdAt,
					$value->firm_name,
					$value->sale_person_first_name . ' ' . $value->sale_person_last_name,

				);
			}

			fputcsv($fp, $lineVal, ",");
		}

		fclose($fp);
	}

	function pointLog(Request $request)
	{

		$searchColumns = array(
			0 => 'crm_log.description',
		);

		$selectColumns = array(
			'crm_log.description',
			'leads.id as lead_id',
			'crm_log.order_id',

		);
		
		$query = CRMLog::query();
        $query->select($selectColumns);
		$query->leftJoin('leads',function ($join) {
			$join->on('leads.inquiry_id', '=' , 'crm_log.inquiry_id') ;
			$join->where('crm_log.inquiry_id','!=','0') ;
		});
        $query->where('for_user_id', $request->user_id);
        $query->whereIn('name', array('point-gain', 'point-redeem'));
		$query->orderBy('crm_log.id', 'desc');

		$isFilterApply = 0;

		if (isset($request['search']['value'])) {
			$isFilterApply = 1;
			$search_value = $request['search']['value'];
			$query->where(function ($query) use ($search_value, $searchColumns) {

				for ($i = 0; $i < count($searchColumns); $i++) {

					if ($i == 0) {
						$query->where($searchColumns[$i], 'like', "%" . $search_value . "%");
					} else {

						$query->orWhere($searchColumns[$i], 'like', "%" . $search_value . "%");
					}
				}
			});
		}

		// $data = $query->get()->paginate(10);
		// $data = json_decode(json_encode($data), true);
		$data = $query->paginate(10);
		$response = successRes("Architect Point Log");
		$response['data'] = $data;
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function inquiryLog(Request $request)
	{

		$rules = array();
		$rules['user_id'] = 'required';

		$customMessage = array();
		$customMessage['user_id.required'] = "Invalid parameters";

		$validator = Validator::make($request->all(), $rules, $customMessage);

		if ($validator->fails()) {

			$response = errorRes("Validation Error", 400);
			$response['data'] = $validator->errors();
			return response()->json($response, $response['status_code'])->header('Content-Type', 'application/json');
		} else {

			$searchColumns = array(
				'inquiry.id',
				'CONCAT(inquiry.first_name," ",inquiry.last_name)',
				'inquiry.status',
				'inquiry.quotation_amount',
			);

			$sortingColumns = array(
				0 => 'inquiry.id',
				1 => 'inquiry.first_name',
				2 => 'inquiry.status',
				3 => 'inquiry.quotation_amount',

			);

			$selectColumns = array(
				'inquiry.id',
				'inquiry.first_name',
				'inquiry.last_name',
				'inquiry.status',
				'inquiry.quotation_amount',
				'inquiry.answer_date_time',

			);

			$userId = $request->user_id;

			// $query = Inquiry::query();
			// $query->where(function ($query2) use ($userId) {

			// 	$query2->where(function ($query3) use ($userId) {

			// 		$query3->whereIn('inquiry.source_type', array("user-201", "user-202"));
			// 		$query3->where('inquiry.source_type_value', $userId);

			// 	});

			// 	$query2->orWhere(function ($query3) use ($userId) {

			// 		$query3->whereIn('inquiry.source_type_1', array("user-201", "user-202"));
			// 		$query3->where('inquiry.source_type_value_1', $userId);

			// 	});

			// 	$query2->orWhere(function ($query3) use ($userId) {

			// 		$query3->whereIn('inquiry.source_type_2', array("user-201", "user-202"));
			// 		$query3->where('inquiry.source_type_value_2', $userId);

			// 	});

			// 	$query2->orWhere(function ($query3) use ($userId) {

			// 		$query3->whereIn('inquiry.source_type_3', array("user-201", "user-202"));
			// 		$query3->where('inquiry.source_type_value_3', $userId);

			// 	});

			// 	$query2->orWhere(function ($query3) use ($userId) {

			// 		$query3->whereIn('inquiry.source_type_4', array("user-201", "user-202"));
			// 		$query3->where('inquiry.source_type_value_4', $userId);

			// 	});

			// 	$query2->orWhere(function ($query3) use ($userId) {

			// 		$query3->where('inquiry.architect', $userId);

			// 	});

			// });
			// $recordsTotal = $query->count();

			//$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.
			$query = Inquiry::query();

			$query->where(function ($query2) use ($userId) {

				$query2->where(function ($query3) use ($userId) {

					$query3->whereIn('inquiry.source_type', array("user-201", "user-202"));
					$query3->where('inquiry.source_type_value', $userId);
				});

				$query2->orWhere(function ($query3) use ($userId) {

					$query3->whereIn('inquiry.source_type_1', array("user-201", "user-202"));
					$query3->where('inquiry.source_type_value_1', $userId);
				});

				$query2->orWhere(function ($query3) use ($userId) {

					$query3->whereIn('inquiry.source_type_2', array("user-201", "user-202"));
					$query3->where('inquiry.source_type_value_2', $userId);
				});

				$query2->orWhere(function ($query3) use ($userId) {

					$query3->whereIn('inquiry.source_type_3', array("user-201", "user-202"));
					$query3->where('inquiry.source_type_value_3', $userId);
				});

				$query2->orWhere(function ($query3) use ($userId) {

					$query3->whereIn('inquiry.source_type_4', array("user-201", "user-202"));
					$query3->where('inquiry.source_type_value_4', $userId);
				});

				$query2->orWhere(function ($query3) use ($userId) {

					$query3->where('inquiry.architect', $userId);
				});
			});

			$query->select($selectColumns);
			// $query->limit($request->length);
			// $query->offset($request->start);
			// $query->orderBy($sortingColumns[$request['order'][0]['column']], $request['order'][0]['dir']);
			$query->orderBy('inquiry.id', 'desc');

			$isFilterApply = 0;

			if (isset($request['search']['value'])) {
				$isFilterApply = 1;
				$search_value = $request['search']['value'];
				$query->where(function ($query) use ($search_value, $searchColumns) {

					for ($i = 0; $i < count($searchColumns); $i++) {

						if ($i == 0) {
							$query->whereRaw($searchColumns[$i] . ' like ?', [$search_value]);
						} else {

							$query->orWhereRaw($searchColumns[$i] . ' like ?', ["%" . $search_value . "%"]);
						}
					}
				});
			}

			// $data = $query->paginate();
			//$data = json_decode(json_encode($data), true);
			// if ($isFilterApply == 1) {
			// 	$recordsFiltered = count($data);
			// }

			$data = $query->paginate(10);
			$response = successRes("Architect Inquiry Log");
			$response['data'] = $data;
		}
		return response()->json($response)->header('Content-Type', 'application/json');

		// foreach ($data as $key => $value) {

		// 	$viewData[$key] = array();
		// 	$viewData[$key]['id'] = $value['id'];
		// 	$viewData[$key]['name'] = '<p class="text-muted mb-0" data-bs-toggle="tooltip"  data-bs-original-title="' . $value['first_name'] . " " . $value['last_name'] . '"><a target="_blank" href="' . route('inquiry') . '?status=0&inquiry_id=' . $value['id'] . '" >' . displayStringLenth($value['first_name'] . " " . $value['last_name'], 20) . '</a></p>';

		// 	$viewData[$key]['status'] = $inquiryStatus[$value['status']]['name'] . " (" . convertDateTime($value['answer_date_time']) . ")";
		// 	$viewData[$key]['quotation_amount'] = $value['quotation_amount'];

		// }

		// $jsonData = array(
		// 	"draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
		// 	"recordsTotal" => intval($recordsTotal), // total number of records
		// 	"recordsFiltered" => intval($recordsFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
		// 	"data" => $viewData, // total data array

		// );
		// return $jsonData;
	}

	function saveEditArchitect(Request $request) {

        $rules = [];
        $rules['user_id'] = 'required';
        $rules['user_type'] = 'required';
        $rules['user_phone_number'] = 'required|digits:10|regex:/^[1-9][0-9]*$/';
        
        if($request->user_type == 202) {
            $rules['user_email'] = 'required|email:rfc,dns';
        }

        $rules['user_first_name'] = 'required';
        $rules['user_last_name'] = 'required';
        $rules['user_house_no'] = 'required';
        $rules['user_address_line1'] = 'required';
        $rules['user_area'] = 'required';
        $rules['user_pincode'] = 'required';
        $rules['user_city_id'] = 'required';

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $response = [];
            $response['status'] = 0;
            $response['msg'] = $validator->errors()->first();
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors()->first();
            return response()->json($response)->header('Content-Type', 'application/json');
        } else {


            $principal_architect_name = isset($request->architect_principal_architect_name) ? $request->architect_principal_architect_name : '';

            $UserType = 0;
            if(Auth::user()->type == 2 && $request->user_type == 201) {
                $UserType = 202;
            } else {
                $UserType = $request->user_type;
            }


            if ($UserType == 201 && $request->user_email == "") {
                $email = "";
            } else {
                $email = $request->user_email;
            }

            $isMovedFromPrimeToNonPrime = 0;

            $User = User::find($request->user_id);
            $Architect = Architect::find($User->reference_id);

            if ($Architect->type != $UserType) {
                $isMovedFromPrimeToNonPrime = 1;
            }

            $log_data = [];

            if ($User->type != $UserType) {
                $new_value = getArchitects()[$UserType]['short_name'];
                $old_value = getArchitects()[$User->type]['short_name'];
                $change_field = "User Type Change : " . $old_value . " To " . $new_value;

                $log_value = [];
                $log_value['field_name'] = "user_type";
                $log_value['new_value'] = $new_value;
                $log_value['old_value'] = $old_value;
                $log_value['description'] =  $change_field;

                array_push($log_data, $log_value);
            }


            if ($User->first_name != $request->user_first_name) {
                $new_value = $request->user_first_name;
                $old_value = $User->first_name;
                $change_field = "User First Name Change : " . $old_value . " To " . $new_value;

                $log_value = [];
                $log_value['field_name'] = "user_first_name";
                $log_value['new_value'] = $new_value;
                $log_value['old_value'] = $old_value;
                $log_value['description'] =  $change_field;

                array_push($log_data, $log_value);
            }

            if ($User->last_name != $request->user_last_name) {
                $new_value = $request->user_last_name;
                $old_value = $User->last_name;
                $change_field = "User Last Name Change : " . $old_value . " To " . $new_value;

                $log_value = [];
                $log_value['field_name'] = "user_last_name";
                $log_value['new_value'] = $new_value;
                $log_value['old_value'] = $old_value;
                $log_value['description'] =  $change_field;

                array_push($log_data, $log_value);
            }

            if ($User->house_no != $request->user_house_no) {
                $new_value = $request->user_house_no;
                $old_value = $User->house_no;
                $change_field = "User House No Change : " . $old_value . " To " . $new_value;

                $log_value = [];
                $log_value['field_name'] = "user_house_no";
                $log_value['new_value'] = $new_value;
                $log_value['old_value'] = $old_value;
                $log_value['description'] =  $change_field;

                array_push($log_data, $log_value);
            }

            if ($User->address_line1 != $request->user_address_line1) {
                $new_value = $request->user_address_line1;
                $old_value = $User->address_line1;
                $change_field = "User Address Line 1 Change : " . $old_value . " To " . $new_value;

                $log_value = [];
                $log_value['field_name'] = "user_address_line1";
                $log_value['new_value'] = $new_value;
                $log_value['old_value'] = $old_value;
                $log_value['description'] =  $change_field;

                array_push($log_data, $log_value);
            }

            if ($User->area != $request->user_area) {
                $new_value = $request->user_area;
                $old_value = $User->area;
                $change_field = "User Area Change : " . $old_value . " To " . $new_value;

                $log_value = [];
                $log_value['field_name'] = "user_area";
                $log_value['new_value'] = $new_value;
                $log_value['old_value'] = $old_value;
                $log_value['description'] =  $change_field;

                array_push($log_data, $log_value);
            }

            if ($User->pincode != $request->user_pincode) {
                $new_value = $request->user_pincode;
                $old_value = $User->pincode;
                $change_field = "User Pincode Change : " . $old_value . " To " . $new_value;

                $log_value = [];
                $log_value['field_name'] = "user_pincode";
                $log_value['new_value'] = $new_value;
                $log_value['old_value'] = $old_value;
                $log_value['description'] =  $change_field;

                array_push($log_data, $log_value);
            }

            if ($User->city_id != $request->user_city_id) {
                $new_value = $request->user_city_id;
                $old_value = $User->city_id;
                $change_field = "User City Change : " . CityList::find($old_value)['name'] . " To " . CityList::find($new_value)['name'];

                $log_value = [];
                $log_value['field_name'] = "user_city";
                $log_value['new_value'] = $new_value;
                $log_value['old_value'] = $old_value;
                $log_value['description'] =  $change_field;

                array_push($log_data, $log_value);
            }

            if ($Architect->principal_architect_name != $principal_architect_name) {
                $new_value = $principal_architect_name;
                $old_value = $Architect->principal_architect_name;
                $change_field = "User Principal Architect Name Change : " . $old_value . " To " . $new_value;

                $log_value = [];
                $log_value['field_name'] = "principal_architect_name";
                $log_value['new_value'] = $new_value;
                $log_value['old_value'] = $old_value;
                $log_value['description'] =  $change_field;

                array_push($log_data, $log_value);
            }

            if ($Architect->firm_name != $request->architect_firm_name) {
                $new_value = $principal_architect_name;
                $old_value = $Architect->principal_architect_name;
                $change_field = "User Firm Change : " . $old_value . " To " . $new_value;

                $log_value = [];
                $log_value['field_name'] = "architect_firm_name";
                $log_value['new_value'] = $new_value;
                $log_value['old_value'] = $old_value;
                $log_value['description'] =  $change_field;

                array_push($log_data, $log_value);
            }

            if ($Architect->sale_person_id != $request->architect_sale_person_id) {
                $new_value = $request->architect_sale_person_id;
                $old_value = $Architect->sale_person_id;

                $old_text = User::selectRaw(DB::raw('CONCAT(first_name," ", last_name) AS text'))->where('id', $old_value)->first()['text'];
                $new_text = User::selectRaw(DB::raw('CONCAT(first_name," ", last_name) AS text'))->where('id', $new_value)->first()['text'];
                $change_field = "User Sale Person Change : " . $old_text . " To " . $new_text;

                $log_value = [];
                $log_value['field_name'] = "sale_person";
                $log_value['new_value'] = $new_value;
                $log_value['old_value'] = $old_value;
                $log_value['description'] =  $change_field;

                array_push($log_data, $log_value);
            }

            if (date('m-d', strtotime($Architect->anniversary_date)) != date('m-d', strtotime($request->architect_anniversary_date))) {
                $new_value = date('m-d', strtotime($request->architect_anniversary_date));
                $old_value = date('m-d', strtotime($Architect->anniversary_date));
                $change_field = "User Anniversary Date Change : " . $old_value . " To " .  $new_value;

                $log_value = [];
                $log_value['field_name'] = "anniversary_date";
                $log_value['new_value'] = $new_value;
                $log_value['old_value'] = $old_value;
                $log_value['description'] =  $change_field;

                array_push($log_data, $log_value);
            }

            if (date('m-d', strtotime($Architect->birth_date)) != date('m-d', strtotime($request->architect_birth_date))) {
                $new_value = date('m-d', strtotime($request->architect_birth_date));
                $old_value = date('m-d', strtotime($Architect->birth_date));
                $change_field = "User Birth Date Change : " . $old_value . " To " .  $new_value;

                $log_value = [];
                $log_value['field_name'] = "birth_date";
                $log_value['new_value'] = $new_value;
                $log_value['old_value'] = $old_value;
                $log_value['description'] =  $change_field;

                array_push($log_data, $log_value);
            }

            foreach ($log_data as $log_value) {
                $user_log = new UserLog();
                $user_log->user_id = Auth::user()->id;
                $user_log->log_type = "ARCHITECT-LOG";
                $user_log->field_name = $log_value['field_name'];
                $user_log->old_value = $log_value['old_value'];
                $user_log->new_value = $log_value['new_value'];
                $user_log->reference_type = "Architect";
                $user_log->reference_id = $request->user_id;
                $user_log->transaction_type = "Architect Edit";
                $user_log->description = $log_value['description'];
                $user_log->source = "WEB";
                $user_log->entryby = Auth::user()->id;
                $user_log->entryip = $request->ip();
                $user_log->save();
            }

            

            if ($request->user_id != 0 && $UserType == 202 && $Architect->converted_prime == 0) {
                $converted_prime = 0;
            } else {
                $converted_prime = 1;
            }


            $User->first_name = $request->user_first_name;
            $User->last_name = $request->user_last_name;
            $User->email = $email;
            $User->dialing_code = '+91';
            $User->phone_number = $request->user_phone_number;
            if ($UserType == 202) {
                $PrivilegeJSON = [];
                $PrivilegeJSON['dashboard'] = 1;
                $User->privilege = json_encode($PrivilegeJSON);
            }
            $User->house_no = $request->user_house_no;
            $User->address_line1 = $request->user_address_line1;
            $User->address_line2 = '';
            $User->area = $request->user_area;
            $User->pincode = $request->user_pincode;
            $User->country_id = 0;
            $User->state_id = 0;
            $User->city_id = $request->user_city_id;
            $User->type = $UserType;
            $User->save();

            if($User) {

                if ($isMovedFromPrimeToNonPrime == 1) {
                    $Architect->prime_nonprime_date = date('Y-m-d H:i:s');
                }
                
                $Architect->user_id = $User->id;
                $Architect->type = $UserType;
                $Architect->firm_name = isset($request->architect_firm_name) ? $request->architect_firm_name : "";
                $Architect->sale_person_id = $request->architect_sale_person_id;
                $Architect->principal_architect_name = $principal_architect_name;
    
                if ($request->architect_birth_date != '') {
                    $Architect->birth_date = date('Y-m-d H:i:s', strtotime($request->architect_birth_date . "-1980"));
                } else {
                    $Architect->birth_date = null;
                }
    
    
                if ($request->architect_anniversary_date != '') {
                    $Architect->anniversary_date = date('Y-m-d H:i:s', strtotime($request->architect_anniversary_date . "-1980"));
                } else {
                    $Architect->anniversary_date = null;
                }
    
                if ($UserType == 202) {
                    $Architect->converted_prime = 1;
                }
                // $Architect->source_type = $source_type;
                // $Architect->source_type_value = $source_type_value;
                $Architect->added_by = Auth::user()->id;
                $Architect->updateby = Auth::user()->id;
                $Architect->updateip = $request->ip();
                $Architect->save();
            }
            

            if ($User && $Architect) { 

                if (($request->architect_source_type != null && $request->architect_source_type == 'user-201') || $request->architect_source_type == 'user-202' || $request->architect_source_type == 'user-301' || $request->architect_source_type == 'user-302' || $request->architect_source_type == 'user-101' || $request->architect_source_type == 'user-102' || $request->architect_source_type == 'user-103' || $request->architect_source_type == 'user-104' || $request->architect_source_type == 'user-105') {
                    $Main_source = User::where('id', $Architect->source_type_value)->first();

                    $UserContact = UserContact::select('*')->where('user_id', $User->id)->where('contact_tag_id', 0)->first();

                    if ($UserContact) {
                        $UserContact->user_id = $User->id;
                        $UserContact->contact_tag_id = 0;
                        $UserContact->first_name = $Main_source->first_name;
                        $UserContact->last_name = $Main_source->last_name;
                        $UserContact->phone_number = $Main_source->phone_number;
                        $UserContact->alernate_phone_number = 0;
                        $UserContact->email = $Main_source->email;
                        $UserContact->type = $Main_source->type;
                        $UserContact->type_detail = 'user-' . $Architect->type . '-' . $Architect->source_type_value;

                        $UserContact->save();
                    } else {
                        $UserContact = new UserContact();
                        $UserContact->user_id = $User->id;
                        $UserContact->contact_tag_id = 0;
                        $UserContact->first_name = $Main_source->first_name;
                        $UserContact->last_name = $Main_source->last_name;
                        $UserContact->phone_number = $Main_source->phone_number;
                        $UserContact->alernate_phone_number = 0;
                        $UserContact->email = $Main_source->email;
                        $UserContact->type = $Main_source->type;
                        $UserContact->type_detail = 'user-' . $Architect->type . '-' . $Architect->source_type_value;

                        $UserContact->save();
                    }
                }


                $debugLog['name'] = 'architect-edit';
                $debugLog['description'] = 'user #' . $User->id . '(' . $User->first_name . ' ' . $User->last_name . ') has been updated ';
                $response = successRes('Successfully saved user');
                $response['cov'] = $converted_prime;
                $response['user_id'] = $User->id;


                if (($request->user_id != 0 && $UserType == 202 && $converted_prime == 0)) {
                    // ARCHITECT JOINING BONUS
                    $pointValue = 50;
                    $Architect->total_point = $Architect->total_point + $pointValue;
                    $Architect->total_point_current = $Architect->total_point_current + $pointValue;
                    $Architect->save();
        
                    $debugLog = [];
                    $debugLog['for_user_id'] = $Architect->user_id;
                    $debugLog['name'] = 'point-gain';
                    $debugLog['points'] = $pointValue;
                    $debugLog['description'] = $pointValue . ' Point gained joining bonus ';
                    $debugLog['type'] = '';
                    saveCRMUserLog($debugLog);
        
                    $configrationForNotify = configrationForNotify();
        
                    $params = [];
                    $params['from_name'] = $configrationForNotify['from_name'];
                    $params['from_email'] = $configrationForNotify['from_email'];
                    $params['to_email'] = $User->email;
                    $params['to_name'] = $configrationForNotify['to_name'];
                    $params['bcc_email'] = ['sales@whitelion.in', 'sc@whitelion.in', 'poonam@whitelion.in'];
                    $params['subject'] = 'Welcome to the Whitelion';
                    $params['user_first_name'] = $User->first_name;
                    $params['user_last_name'] = $User->last_name;
                    $params['user_mobile'] = $User->phone_number;
                    $params['credentials_email'] = $User->email;
                    $params['credentials_password'] = '111111';
                    $query = CRMHelpDocument::query();
                    $query->where('status', 1);
                    $query->where('type', 202);
                    $query->limit(30);
                    $query->orderBy('publish_date_time', 'desc');
                    $helpDocuments = $query->get();
                    $params['help_documents'] = json_decode(json_encode($helpDocuments), true);
        
                    if (Config::get('app.env') == 'local') {
                        // SEND MAIL
                        $params['to_email'] = $configrationForNotify['test_email'];
                        $params['bcc_email'] = $configrationForNotify['test_email_bcc'];
                    }
        
                    //TEMPLATE 6
                    Mail::send('emails.signup_architect', ['params' => $params], function ($m) use ($params) {
                        $m->from($params['from_email'], $params['from_name']);
                        $m->bcc($params['bcc_email']);
                        $m->to($params['to_email'], $params['to_name'])->subject($params['subject']);
        
                        foreach ($params['help_documents'] as $new_helpDocument) {
                            $fileName = preg_replace('![^a-z0-9]+!i', '-', $new_helpDocument['file_name']);
                            $fileExtension = explode('.', $new_helpDocument['file_name']);
                            $fileExtension = end($fileExtension);
                            $fileName = $fileName . '.' . $fileExtension;
        
                            // if (is_file($new_helpDocument['title'])) {
                            $m->attach(getSpaceFilePath($new_helpDocument['file_name']), [
                                'as' => $fileName,
                            ]);
                            // }
                        }
                    });
        
                    $helpDocument = CRMHelpDocument::query();
					$helpDocument->where('status', 1);
					$helpDocument->where('type', 202);
					$helpDocument->orderBy('publish_date_time', "desc");
					$helpDocument = $helpDocument->first();
                    //TEMPLATE 7
                    $whatsapp_controller = new WhatsappApiContoller();
                    $perameater_request = new Request();
                    $perameater_request['q_whatsapp_massage_mobileno'] = $User->phone_number;
                    $perameater_request['q_whatsapp_massage_template'] = 'for_architect_download_mobileapp';
					$perameater_request['q_whatsapp_massage_attechment'] = $helpDocument ? getSpaceFilePath($helpDocument->file_name) : '';
                    $perameater_request['q_broadcast_name'] = $User->first_name . ' ' . $User->last_name;
                    $perameater_request['q_whatsapp_massage_parameters'] = array(
						'data[0]' => $User->email,
						'data[1]' => '111111'
					);
                    $wp_response = $whatsapp_controller->sendTemplateMessage($perameater_request);
                    $response['whatsapp'] = $wp_response;
                }

                $response = successRes();
                $response['user_id'] = $User->id;
                return response()->json($response)->header('Content-Type', 'application/json');
            }

        }
    }

    public function leadLog(Request $request)
	{

        $LeadIds = Lead::query();
        $LeadIds->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
        $LeadIds->where(function ($query) use ($request) {
            $query->orwhere('leads.architect', $request->user_id);
            $query->orwhere('lead_sources.source', $request->user_id);
        });
        $LeadIds->where('is_deal', $request->type);
        if($request->type == 0) {
            if($request->status_type == 2) {
                $LeadIds->whereIn('leads.status', [1, 2, 3, 4]);
            } else if($request->status_type == 3) {
                $LeadIds->where('leads.status', 5);
            } else if($request->status_type == 4) {
                $LeadIds->where('leads.status', 6);
            }
        } else if($request->type == 1) {
            if($request->status_type == 2) {
                $LeadIds->whereIn('leads.status', [100, 101, 102]);
            } else if($request->status_type == 3) {
                $LeadIds->where('leads.status', 103);
            } else if($request->status_type == 4) {
                $LeadIds->where('leads.status', 104);
            } else if($request->status_type == 5) {
                $LeadIds->where('leads.status', 105);
            }
        }
        $LeadIds = $LeadIds->distinct()->pluck('leads.id');

        $selectColumns = array(
            'leads.id',
            'leads.first_name',
            'leads.last_name',
            'leads.status',
            'leads.electrician',
            'leads.source_type',
            'leads.source',
            'leads.total_point',
            'wltrn_quotation.quot_total_amount',
            'crm_setting_stage_of_site.name',
        );

        $DealData = Lead::query();
        $DealData->select($selectColumns);
        $DealData->selectRaw('crm_setting_stage_of_site.name as site_stage_name');
        // $DealData->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
        $DealData->leftJoin('crm_setting_stage_of_site', 'crm_setting_stage_of_site.id', '=', 'leads.site_stage');
        $DealData->leftJoin('wltrn_quotation', function($join) {
            $join->on('wltrn_quotation.inquiry_id', '=', 'leads.id');
            $join->where('wltrn_quotation.isfinal', '=', 1);
        });
        $DealData->whereIn('leads.id', $LeadIds);
        // $DealData->where('lead_sources.is_main', 1);
        $DealData->groupBy($selectColumns);
        $data = $DealData->paginate();

        $view_data = array();
        foreach ($data as $key => $value) {

            $data[$key]['client_name'] = $value->first_name .' '. $value->last_name;
            if($value['electrician'] != 0) {
                $user = User::select(DB::raw('CONCAT(users.first_name," ",users.last_name) AS ele_name'))->where('id', $value['electrician'])->first();
                $data[$key]['electrician'] = $user->ele_name;
            } else {
                $data[$key]['electrician'] = "";
            }

            if($value['quot_total_amount'] == null) {
                $data[$key]['quotation_amount'] = 0;
            } else {
                $data[$key]['quotation_amount'] = $value['quot_total_amount'];
            }

            if($value['is_deal'] == 1) {
                $data[$key]['data_id'] = '#D'.$value['id'];
            } else {
                $data[$key]['data_id'] = '#L'.$value['id'];
            }


            $source_type_pieces = explode("-", $value['source_type']);
            if ($source_type_pieces[0] == "user") {
                if (isChannelPartner($source_type_pieces[1]) != 0) {

                    $User1 = User::select(DB::raw("channel_partner.firm_name"));
                    $User1->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
                    $User1->where('users.id', $value['source']);
                    $User1->limit(1);
                    $User1 = $User1->first();

                    if ($User1) {
                        $data[$key]['source'] = $User1->firm_name;
                    } else {
                        $data[$key]['source'] = "";
                    }
                } else {

                    $User1 = User::select(DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
                    $User1->where('users.id', $value['source']);
                    $User1->limit(1);
                    $User1 = $User1->first();

                    if ($User1) {
                        $data[$key]['source'] = $User1->full_name;
                    } else {
                        $data[$key]['source'] = "";
                    }
                }
            } else {
                $data[$key]['source'] = $value['source'];
            }
            $data[$key]['status_label'] = getLeadStatus()[$value['status']]['name'];
        };

        $response = successRes("Architect Lead Log");
		$response['data'] = $data;
		// $response['LeadIds'] = $LeadIds;
		return response()->json($response)->header('Content-Type', 'application/json');
	}

    public function getArchitectLogCount(Request $request) {
        $selectColumns = array(
            'leads.id',
            'leads.first_name',
            'leads.last_name',
            'leads.status',
            'leads.is_deal',
            'wltrn_quotation.quot_total_amount',
        );


        $query = Lead::query();
        $query->select($selectColumns);
        $query->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
        $query->leftJoin('wltrn_quotation', function($join) {
            $join->on('wltrn_quotation.inquiry_id', '=', 'leads.id');
            $join->where('wltrn_quotation.isfinal', '=', 1);
        });
        $query->where(function ($query) use ($request) {
            $query->orwhere('leads.architect', $request->user_id);
            $query->orwhere('lead_sources.source', $request->user_id);
        });
        $query->groupBy($selectColumns);
        $Lead_ids = $query->distinct()->pluck('leads.id');
        $query = $query->get();

       

        $lead = array();
        $lead['id'] = 0;
        $lead['text'] = "Lead";
        $lead['count'] = 0;
        $lead['status_list'] = array();

        $deal = array();
        $deal['id'] = 1;
        $deal['text'] = "Deal";
        $deal['count'] = 0;
        $deal['status_list'] = array();

        foreach ($query as $key => $value) {
            $Status_count = Lead::query();
            if($value->is_deal == 0) {
                $Status_count->selectRaw('SUM(CASE WHEN leads.status IN (1, 2, 3, 4) THEN 1 ELSE 0 END) as LeadRunning, SUM(CASE WHEN leads.status = 5 THEN 1 ELSE 0 END) as LeadLost, SUM(CASE WHEN leads.status =  6 THEN 1 ELSE 0 END) as LeadCold');
            } else {
                $Status_count->selectRaw('SUM(CASE WHEN leads.status IN (100, 101, 102) THEN 1 ELSE 0 END) as Running, SUM(CASE WHEN leads.status = 103 THEN 1 ELSE 0 END) as Won, SUM(CASE WHEN leads.status = 104 THEN 1 ELSE 0 END) as Lost, SUM(CASE WHEN leads.status =  105 THEN 1 ELSE 0 END) as Cold');
            }
            $Status_count->whereIn('leads.id',  $Lead_ids);
            $Status_count = $Status_count->first();


            if($value->is_deal == 0) {
                $lead['id'] = $value->is_deal;
                $lead['text'] = 'Lead';
                $lead['count'] = $lead['count'] + 1;

                $lead['status_list'][0]['id'] = 1;
                $lead['status_list'][0]['text'] = "Total";
                $lead['status_list'][0]['count'] = (int)$Status_count->LeadRunning + (int)$Status_count->LeadLost + (int)$Status_count->LeadCold;

                $lead['status_list'][1]['id'] = 2;
                $lead['status_list'][1]['text'] = "Running";
                $lead['status_list'][1]['count'] = $Status_count->LeadRunning;

                $lead['status_list'][2]['id'] = 3;
                $lead['status_list'][2]['text'] = "Lost";
                $lead['status_list'][2]['count'] = $Status_count->LeadLost;

                $lead['status_list'][3]['id'] = 4;
                $lead['status_list'][3]['text'] = "Cold";
                $lead['status_list'][3]['count'] = $Status_count->LeadCold;

                

            } else {
                $deal['id'] = $value->is_deal;
                $deal['text'] = 'Deal';
                $deal['count'] = $deal['count'] + 1;

                $deal['status_list'][0]['id'] = 1;
                $deal['status_list'][0]['text'] = "Total";
                $deal['status_list'][0]['count'] = (int)$Status_count->Running + (int)$Status_count->Won + (int)$Status_count->Lost + (int)$Status_count->Cold;

                $deal['status_list'][1]['id'] = 2;
                $deal['status_list'][1]['text'] = "Running";
                $deal['status_list'][1]['count'] = $Status_count->Running;

                $deal['status_list'][2]['id'] = 3;
                $deal['status_list'][2]['text'] = "Won";
                $deal['status_list'][2]['count'] = $Status_count->Won;

                $deal['status_list'][3]['id'] = 4;
                $deal['status_list'][3]['text'] = "Lost";
                $deal['status_list'][3]['count'] = $Status_count->Lost;

                $deal['status_list'][4]['id'] = 5;
                $deal['status_list'][4]['text'] = "Cold";
                $deal['status_list'][4]['count'] = $Status_count->Cold;

                
            }
        }

        $data= array();
        $data['lead'] = $lead;
        $data['deal'] = $deal;

        $response = successRes("Architect Lead Log Count");
		$response['data'] = $data;
		return response()->json($response)->header('Content-Type', 'application/json');
    }
}
