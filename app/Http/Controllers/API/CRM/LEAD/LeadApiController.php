<?php

namespace App\Http\Controllers\API\CRM\LEAD;

use App\Models\Lead;
use App\Models\User;
use App\Models\CityList;
// use PDF;
// use Dompdf\Dompdf;
use App\Models\DebugLog;
use App\Models\LeadCall;
use App\Models\LeadFile;
use App\Models\LeadTask;
// use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\WlmstItem;
use App\Models\LeadSource;
use App\Models\LeadUpdate;
use App\Models\SalePerson;
use App\Models\Electrician;
use App\Models\LeadClosing;
use App\Models\LeadContact;
use App\Models\LeadMeeting;
use App\Models\LeadTimeline;
use App\Models\Wlmst_Client;
use Illuminate\Http\Request;
use App\Models\CRMSettingBHK;
use App\Models\Wltrn_Quotation;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpParser\Node\Stmt\Break_;
use App\Models\CRMSettingSource;
use App\Models\CRMSettingFileTag;
use App\Models\CRMSettingCallType;
use App\Models\CRMSettingSiteType;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\CRMSettingContactTag;
use App\Models\CRMSettingSourceType;
use App\Models\Wltrn_QuotItemdetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use App\Models\CRMSettingCompetitors;
use App\Models\CRMSettingMeetingType;
use App\Models\CRMSettingStageOfSite;
use App\Models\TagMaster;
use App\Models\CRMSettingWantToCover;
use App\Models\LeadAccountContact;
use App\Models\CRMSettingMeetingTitle;
use App\Models\LeadMeetingParticipant;
use App\Models\Exhibition;
use App\Models\LeadQuestionAnswer;
use Illuminate\Support\Facades\Config;
use App\Models\ChannelPartner;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\CRM\LeadQuotationController;
use App\Http\Controllers\Whatsapp\WhatsappApiContoller;
use App\Http\Controllers\Quotation\QuotationMasterController;

// use Illuminate\Http\Request;

class LeadApiController extends Controller
{

	public function __construct()
	{

		$this->middleware(function ($request, $next) {

			$tabCanAccessBy = array(0, 1, 2, 9, 11, 101, 102, 103, 104, 105, 202, 302, 12);

			if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
				$response = errorRes("You Don't Have An Access To This Page", 401);
				return response()->json($response, $response['status_code'])->header('Content-Type', 'application/json');
			}

			return $next($request);
		});
	}

	public function save(Request $request)
	{

		$isArchitect = isArchitect();
		$isElectrician = isElectrician();
		$isReception = isReception();
		$sourceTypes = getInquirySourceTypes();
		$isChannelPartner = isChannelPartner(Auth::user()->type);
		$isThirdPartyUser = isThirdPartyUser();
		$isSalePerson = isSalePerson();
		$isTaleSalesUser = isTaleSalesUser();
		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();

		$assigned_to = 0;
		$lead_architect = 0;
		$lead_electrician = 0;

		$rules = array();
		$rules['lead_id'] = 'required';
		$rules['lead_first_name'] = ['required',function ($attribute, $value, $fail) {
				if (preg_match('/^(Mr|Miss|.)$/i', $value)) {
					$fail('The '.$attribute.' field cannot contain "Mr", "Miss", or "Ji".');
				}
			},
		];
		$rules['lead_last_name'] = ['required',function ($attribute, $value, $fail) {
				if (preg_match('/^(Mr|Miss|Ji|.)$/i', $value)) {
					$fail('The '.$attribute.' field cannot contain "Mr", "Miss", or "Ji".');
				}
			},
		];
		$rules['lead_phone_number'] = 'required|digits:10|regex:/^[1-9][0-9]*$/';
		$rules['lead_house_no'] = ['required',function ($attribute, $value, $fail) {
				if (preg_match('/^(.)$/i', $value)) {
					$fail('The '.$attribute.' field cannot contain "."');
				}
			},
		];
		$rules['lead_addressline1'] = ['required',function ($attribute, $value, $fail) {
				if (preg_match('/^(.)$/i', $value)) {
					$fail('The '.$attribute.' field cannot contain "."');
				}
			},
		];
		$rules['lead_area'] = 'required';
		$rules['lead_city_id'] = 'required';

		// $rules['lead_meeting_house_no'] = 'required';
		// $rules['lead_meeting_addressline1'] = 'required';
		// $rules['lead_meeting_area'] = 'required';
		// $rules['lead_meeting_city_id'] = 'required';
		$rules['lead_site_stage'] = 'required';
		$rules['lead_site_type'] = 'required';
		$rules['lead_bhk'] = 'required';
		$rules['lead_want_to_cover'] = 'required';
		$rules['lead_source_type'] = 'required';
		$rules['assigned_to'] = 'required';

		$customMessage = array();
		$customMessage['lead_id.required'] = 'Invalid Perameater';
		$customMessage['lead_first_name.required'] = 'Please enter first name';
		$customMessage['lead_last_name.required'] = 'Please enter last name';
		$customMessage['lead_phone_number.required'] = 'Please enter mobile no';
		$customMessage['lead_house_no.required'] = 'Please enter house no';
		$customMessage['lead_addressline1.required'] = 'Please enter society name';
		$customMessage['lead_area.required'] = 'Please enter area';
		// $customMessage['lead_pincode.required'] = 'Please enter pincode';
		$customMessage['lead_city_id.required'] = 'Please select city';
		$customMessage['lead_meeting_house_no.required'] = 'Please enter meeting house no';
		$customMessage['lead_meeting_addressline1.required'] = 'Please enter meeting society name';
		$customMessage['lead_meeting_area.required'] = 'Please enter meeting area';
		// $customMessage['lead_meeting_pincode.required'] = 'Please enter meeting pincode';
		$customMessage['lead_meeting_city_id.required'] = 'Please select meeting city';
		$customMessage['lead_site_stage.required'] = 'Please select site stage';
		$customMessage['lead_site_type.required'] = 'Please select site type';
		$customMessage['lead_bhk.required'] = 'Please select bhk';
		$customMessage['lead_want_to_cover.required'] = 'Please select want to cover';
		$customMessage['assigned_to.required'] = 'Please select lead owner';
		
		if(isset($request->lead_email)){
			$rules['lead_email'] = 'email:rfc,dns';
            $customMessage['lead_email.email'] = 'Please enter valid email address';
        }

		if(isset($request->lead_site_type)){
            $objSiteType = CRMSettingSiteType::find($request->lead_site_type);
            if (isset($objSiteType)) {
				if ($objSiteType->is_bhk == 0) {
					$rules['lead_sq_foot'] = 'required|gt:0';
					$customMessage['lead_bhk.required'] = 'Please Enter SQ FT';
                }
            }
        }
		$source_type = $request->all()['lead_source_type'];

		if (explode("-", $source_type)[0] == "textrequired") {
			$rules['lead_source_text'] = 'required';

		} else if (explode("-", $source_type)[0] == "textnotrequired") {
			$rules['lead_source_text'] = 'required';

		} else if (explode("-", $source_type)[0] == "fix") {
			// $rules['lead_source_text'] = 'required';

		} else {
			$rules['lead_source'] = 'required';
		}

		// if (count($request->lead_source_type) > 1) {
		// 	for ($i = 1; $i <= count($request->lead_source_type); $i++) {

		// 		$multi_source_type = $request->all()['lead_source_type_' . $i];

		// 		if (explode("-", $multi_source_type)[0] == "textrequired") {
		// 			$rules['lead_source_text_' . $i] = 'required';

		// 		} else if (explode("-", $multi_source_type)[0] == "textnotrequired") {
		// 			$rules['lead_source_text_' . $i] = 'required';

		// 		} else if (explode("-", $multi_source_type)[0] == "fix") {
		// 			$rules['lead_source_text_' . $i] = 'required';

		// 		} else {
		// 			$rules['lead_source_' . $i] = 'required';
		// 		}
		// 	}
		// }

		if ($isSalePerson == 1) {

			$assigned_to = $request->assigned_to;

		} else if ($isAdminOrCompanyAdmin == 1 || $isTaleSalesUser == 1 || $isChannelPartner != 0 || $isElectrician == 1 || $isArchitect == 1 || $isReception == 1) {

			$assigned_to = $request->assigned_to;

		} else {

			$response = errorRes("Invalid access", 401);
			return response()->json($response, $response['status_code'])->header('Content-Type', 'application/json');
		}

		$lead_email = isset($request->lead_email) ? $request->lead_email : '';
		$lead_addressline2 = isset($request->lead_addressline2) ? $request->lead_addressline2 : '';
		$lead_meeting_addressline2 = isset($request->lead_meeting_addressline2) ? $request->lead_meeting_addressline2 : '';

		$lead_pincode = isset($request->lead_pincode) ? $request->lead_pincode : '';
		$lead_meeting_pincode = isset($request->lead_meeting_pincode) ? $request->lead_meeting_pincode : '';
		$lead_competitor = isset($request->lead_competitor) ? $request->lead_competitor : array();
		$lead_closing_date_and_time = $request->lead_closing_date_time;
		$lead_architect = isset($request->lead_architect) ? $request->lead_architect : 0;
		$lead_electrician = isset($request->lead_electrician) ? $request->lead_electrician : 0;
		$lead_channel_partner = isset($request->lead_channel_partner) ? $request->lead_channel_partner : 0;

		$change_field = "";


		$lead_budget = isset($request->lead_budget) ? $request->lead_budget : 0;
		$lead_sq_foot = isset($request->lead_sq_foot) ? $request->lead_sq_foot : 0;
		$temp_comptitor = array();

		$validator = Validator::make($request->all(), $rules, $customMessage);

		if ($validator->fails()) {
			$response = array();
			$response['status'] = 0;
			$response['msg'] = $validator->errors()->first();
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();
		} else {

			$main_source_type = $request->lead_source_type;
			if (explode("-", $main_source_type)[0] == "textrequired") {
				$main_source = $request->lead_source_text;

			} else if (explode("-", $main_source_type)[0] == "textnotrequired") {
				$main_source = $request->lead_source_text;

			} else if (explode("-", $main_source_type)[0] == "fix") {
				// $main_source = $request->lead_source_text;
				$main_source = '-';

			} else {
				$main_source = $request->lead_source;
			}

			if ($request->lead_id != 0) {
				$Lead = Lead::find($request->lead_id);
				$Lead->updated_by = Auth::user()->id;
				$Lead->updateip = $request->ip();
				$Lead->update_source = $request->app_source;

				if ($Lead->first_name != $request->lead_first_name) {
					$new_value = $request->lead_first_name;
					$old_value = $Lead->first_name;
					$change_field .= " | Client Name Change : " . $old_value . " To " . $new_value;
				}

				if ($Lead->last_name != $request->lead_last_name) {
                    $new_value = $request->lead_last_name;
                    $old_value = $Lead->last_name;
                    $change_field .= ' | Client Name Last Name Change : ' . $old_value . ' To ' . $new_value;
                }

				if ($Lead->email != $lead_email) {
					$new_value = $lead_email;
					$old_value = $Lead->email;
					$change_field .= " | Client Email Change : " . $old_value . " To " . $new_value;
				}
				if ($Lead->phone_number != $request->lead_phone_number) {
					$new_value = $request->lead_phone_number;
					$old_value = $Lead->phone_number;
					$change_field .= " | Client Mobile NO. Change : " . $old_value . " To " . $new_value;
				}

				if ($Lead->assigned_to != $assigned_to) {
                    $old_value = $assigned_to;
                    $new_value = $Lead->assigned_to;

                    $old_text_value = '';
                    $old_val_text = User::select(DB::raw("CONCAT(users.first_name,' ',users.last_name) AS name"))->where('id', $old_value)->first();
                    if ($old_val_text) {
                        $old_text_value = $old_val_text->name;
                    }
                    
                    $new_text_value = '';
                    $new_val_text = User::select(DB::raw("CONCAT(users.first_name,' ',users.last_name) AS name"))->where('id', $new_value)->first();
                    if ($new_val_text) {
                        $new_text_value = $new_val_text->name;
                    }

                    $change_field .= ' | Lead Owner Change : '.$old_text_value.'(' . $old_value . ') To '.$new_text_value.'(' . $new_value.')';
                }

                if ($Lead->architect != $lead_architect) {
                    $old_value = $lead_architect;
                    $new_value = $Lead->architect;

                    $old_text_value = '';
                    $old_val_text = User::select(DB::raw("CONCAT(users.first_name,' ',users.last_name) AS name"))->where('id', $old_value)->first();
                    if ($old_val_text) {
                        $old_text_value = $old_val_text->name;
                    }
                    
                    $new_text_value = '';
                    $new_val_text = User::select(DB::raw("CONCAT(users.first_name,' ',users.last_name) AS name"))->where('id', $new_value)->first();
                    if ($new_val_text) {
                        $new_text_value = $new_val_text->name;
                    }

                    $change_field .= ' | Lead Architect Change : '.$old_text_value.'(' . $old_value . ') To '.$new_text_value.'(' . $new_value.')';
                }

                if ($Lead->electrician != $lead_electrician) {
                    $old_value = $lead_electrician;
                    $new_value = $Lead->electrician;

                    $old_text_value = '';
                    $old_val_text = User::select(DB::raw("CONCAT(users.first_name,' ',users.last_name) AS name"))->where('id', $old_value)->first();
                    if ($old_val_text) {
                        $old_text_value = $old_val_text->name;
                    }
                    
                    $new_text_value = '';
                    $new_val_text = User::select(DB::raw("CONCAT(users.first_name,' ',users.last_name) AS name"))->where('id', $new_value)->first();
                    if ($new_val_text) {
                        $new_text_value = $new_val_text->name;
                    }

                    $change_field .= ' | Lead Electrician Change : '.$old_text_value.'(' . $old_value . ') To '.$new_text_value.'(' . $new_value.')';
                }
				
				if ($Lead->channel_partner != $lead_channel_partner) {
                    $old_value = $lead_channel_partner;
                    $new_value = $Lead->channel_partner;

                    $old_text_value = '';
                    $old_val_text = User::select(DB::raw("CONCAT(users.first_name,' ',users.last_name) AS name"))->where('id', $old_value)->first();
                    if ($old_val_text) {
                        $old_text_value = $old_val_text->name;
                    }
                    
                    $new_text_value = '';
                    $new_val_text = User::select(DB::raw("CONCAT(users.first_name,' ',users.last_name) AS name"))->where('id', $new_value)->first();
                    if ($new_val_text) {
                        $new_text_value = $new_val_text->name;
                    }

                    $change_field .= ' | Lead Channel Partner Change : '.$old_text_value.'(' . $old_value . ') To '.$new_text_value.'(' . $new_value.')';
                }

				if ($Lead->house_no != $request->lead_house_no || $Lead->addressline1 != $request->lead_addressline1 || $Lead->area != $request->lead_area || $Lead->pincode != $lead_pincode || $Lead->city_id != $request->lead_city_id) {
					$NewCityList = CityList::select('name')->find($request->lead_city_id);
					if ($NewCityList) {
						$New_City_name = $NewCityList->name;
					} else {
						$New_City_name = "";
					}
					$new_value = $request->lead_house_no . ", " . $request->lead_addressline1 . ", " . $request->lead_area . ", " . $lead_pincode . ", " . $New_City_name;
					$OldCityList = CityList::select('name')->find($Lead->city_id);
					if ($OldCityList) {
						$Old_City_name = $OldCityList->name;
					} else {
						$Old_City_name = "";
					}
					$old_value = $Lead->house_no . ", " . $Lead->addressline1 . ", " . $Lead->area . ", " . $Lead->pincode . ", " . $Old_City_name;
					$change_field .= " | Client Address Change : " . $old_value . " To " . $new_value;
				}

				if ($Lead->meeting_house_no != $request->lead_meeting_house_no || $Lead->meeting_addressline1 != $request->lead_meeting_addressline1 || $Lead->meeting_area != $request->lead_meeting_area || $Lead->meeting_pincode != $lead_meeting_pincode || $Lead->meeting_city_id != $request->lead_meeting_city_id) {
					$NewCityList = CityList::select('name')->find($request->lead_meeting_city_id);
					if ($NewCityList) {
						$New_City_name = $NewCityList->name;
					} else {
						$New_City_name = "";
					}
					$new_value = $request->lead_meeting_house_no . ", " . $request->lead_meeting_addressline1 . ", " . $request->lead_meeting_area . ", " . $lead_meeting_pincode . ", " . $New_City_name;
					$OldCityList = CityList::select('name')->find($Lead->meeting_city_id);
					if ($OldCityList) {
						$Old_City_name = $OldCityList->name;
					} else {
						$Old_City_name = "";
					}
					$old_value = $Lead->meeting_house_no . ", " . $Lead->meeting_addressline1 . ", " . $Lead->meeting_area . ", " . $Lead->meeting_pincode . ", " . $Old_City_name;

					$change_field .= " | Client Meeting Address Change : " . $old_value . " To " . $new_value;
				}

				if ($Lead->source_type != $main_source_type || $Lead->source != $main_source) {
					// FIEND NEW SOURCE TYPE AND NAME
					$new_source_type = $main_source_type;
					$new_source_value = $main_source;
					$new_final_source_type = '';
					$source_type = explode("-", $new_source_type);
					foreach (getLeadSourceTypes() as $source_type_key => $source_type_value) {

						if ($source_type[1] == 201) {
							$source_type_id = 202;
						} else if ($source_type[1] == 301) {
							$source_type_id = 302;
						} else {
							$source_type_id = $source_type[1];
						}

						if ($source_type_value['type'] == $source_type[0] && $source_type_value['id'] == $source_type_id) {
							$new_final_source_type = $source_type_value['lable'];
							break;
						}
					}

					if ($source_type[0] == "user") {

						if (isset(getChannelPartners()[$source_type[1]]['short_name'])) {

							$new_source['val_id'] = $new_source_value;
							$val_text = ChannelPartner::select('firm_name')->where('user_id', $new_source_value)->first();
							if ($val_text) {
								$new_final_source_value = $val_text->firm_name;
							} else {
								$new_final_source_value = " ";
							}

						} else {

							$new_source['val_id'] = $new_source_value;
							$val_text = User::select(DB::raw("CONCAT(users.first_name,' ',users.last_name) AS name"))->where('id', $new_source_value)->first();
							if ($val_text) {
								$new_final_source_value = $val_text->name;
							} else {
								$new_final_source_value = " ";
							}
						}

					} else if ($source_type[0] == "master") {

						// $new_final_source_value = CRMSettingSource::select('name')->where('source_type_id', $new_source_value)->first()->name;
						$val_text = CRMSettingSource::select('name')->where('source_type_id', $new_source_value)->first();
						if ($val_text) {
							$new_final_source_value = $val_text->name;
						} else {
							$new_final_source_value = " ";
						}

					} else {
						$new_final_source_value = $new_source_value;
					}
					// FIEND OLD SOURCE TYPE AND NAME
					$old_source_type = $Lead->source_type;
					$old_source_value = $Lead->source;
					$old_final_source_type = '';
					$source_type = explode("-", $old_source_type);
					foreach (getLeadSourceTypes() as $source_type_key => $source_type_value) {

						if ($source_type[1] == 201) {
							$source_type_id = 202;
						} else if ($source_type[1] == 301) {
							$source_type_id = 302;
						} else {
							$source_type_id = $source_type[1];
						}

						if ($source_type_value['type'] == $source_type[0] && $source_type_value['id'] == $source_type_id) {
							$old_final_source_type = $source_type_value['lable'];
							break;
						}
					}

					if ($source_type[0] == "user") {

						if (isset(getChannelPartners()[$source_type[1]]['short_name'])) {

							$val_text = ChannelPartner::select('firm_name')->where('user_id', $old_source_value)->first();
							if ($val_text) {
								$old_final_source_value = $val_text->firm_name;
							} else {
								$old_final_source_value = " ";
							}

						} else {

							$val_text = User::select(DB::raw("CONCAT(users.first_name,' ',users.last_name) AS name"))->where('id', $old_source_value)->first();
							if ($val_text) {
								$old_final_source_value = $val_text->name;
							} else {
								$old_final_source_value = " ";
							}
						}

					} else if ($source_type[0] == "master") {

						$val_text = CRMSettingSource::select('name')->where('source_type_id', $old_source_value)->first();
						if ($val_text) {
							$old_final_source_value = $val_text->name;
						} else {
							$old_final_source_value = " ";
						}

					} else {
						$old_final_source_value = $old_source_value;
					}

					$change_field .= " | Main Source Change : " . $new_final_source_value . "(" . $new_final_source_type . ") TO " . $old_final_source_value . "(" . $old_final_source_type . ")";
				}

				if ($Lead->site_stage != $request->lead_site_stage) {
					$new_value = $request->lead_site_stage;
					$old_value = $Lead->site_stage;

					$New_Site_Stage = '';
					if ($new_value != 0 || $new_value != '') {

						$CRMSettingStageOfSite = CRMSettingStageOfSite::select('id', 'name as text');
						$CRMSettingStageOfSite->where('crm_setting_stage_of_site.id', $new_value);
						$CRMSettingStageOfSite = $CRMSettingStageOfSite->first();
						if ($CRMSettingStageOfSite) {
							$New_Site_Stage = $CRMSettingStageOfSite->text;
						}
					}
					$Old_Site_Stage = '';
					if ($old_value != 0 || $old_value != '') {

						$CRMSettingStageOfSite = CRMSettingStageOfSite::select('id', 'name as text');
						$CRMSettingStageOfSite->where('crm_setting_stage_of_site.id', $old_value);
						$CRMSettingStageOfSite = $CRMSettingStageOfSite->first();
						if ($CRMSettingStageOfSite) {
							$Old_Site_Stage = $CRMSettingStageOfSite->text;
						}
					}

					$change_field .= " | Site Stage Change : " . $Old_Site_Stage . " To " . $New_Site_Stage;
				}
				if ($Lead->site_type != $request->lead_site_type) {
					$new_value = $request->lead_site_type;
					$old_value = $Lead->site_type;

					$New_Site_Type = '';
					if ($new_value != 0 || $new_value != '') {
						$CRMSettingSiteType = CRMSettingSiteType::select('id', 'name as text');
						$CRMSettingSiteType->where('crm_setting_site_type.id', $new_value);
						$CRMSettingSiteType = $CRMSettingSiteType->first();
						if ($CRMSettingSiteType) {
							$New_Site_Type = $CRMSettingSiteType->text;
						}
					}

					$Old_Site_Type = '';
					if ($old_value != 0 || $old_value != '') {
						$CRMSettingSiteType = CRMSettingSiteType::select('id', 'name as text');
						$CRMSettingSiteType->where('crm_setting_site_type.id', $old_value);
						$CRMSettingSiteType = $CRMSettingSiteType->first();
						if ($CRMSettingSiteType) {
							$Old_Site_Type = $CRMSettingSiteType->text;
						}
					}

					$change_field .= " | Site Type Change : " . $Old_Site_Type . " To " . $New_Site_Type;
				}

				if ($Lead->bhk != $request->lead_bhk) {
					$new_value = $request->lead_bhk;
					$old_value = $Lead->bhk;

					$New_Bhk = '';
					if ($new_value != 0 || $new_value != '') {
						$CRMSettingBHK = CRMSettingBHK::select('id', 'name as text');
						$CRMSettingBHK->where('crm_setting_bhk.id', $new_value);
						$CRMSettingBHK = $CRMSettingBHK->first();
						if ($CRMSettingBHK) {
							$New_Bhk = $CRMSettingBHK->text;
						}
					}

					$Old_Bhk = '';
					if ($old_value != 0 || $old_value != '') {
						$CRMSettingBHK = CRMSettingBHK::select('id', 'name as text');
						$CRMSettingBHK->where('crm_setting_bhk.id', $old_value);
						$CRMSettingBHK = $CRMSettingBHK->first();
						if ($CRMSettingBHK) {
							$Old_Bhk = $CRMSettingBHK->text;
						}
					}

					$change_field .= " | Site Type Change : " . $Old_Bhk . " To " . $New_Bhk;
				}
			} else {
				$already_phone_number = Lead::where('phone_number', $request->lead_phone_number)->first();
				$already_addrress = Lead::where('addressline1', $request->lead_addressline1)->where('house_no', $request->lead_house_no)->first();
				if ($already_phone_number) {
					$response = errorRes("Phone number is already register in #$already_phone_number->id ($already_phone_number->first_name  $already_phone_number->last_name) this lead , Please use another phone number");
					return response()->json($response)->header('Content-Type', 'application/json');
				} elseif ($already_addrress) {
					// $response = errorRes("Inquiry already registed with House No. OR Society/Building Name, Please use another Address");
					$response = errorRes("Address is already register in #$already_addrress->id ($already_addrress->first_name  $already_addrress->last_name) this lead , Please use another address");
					return response()->json($response)->header('Content-Type', 'application/json');
				} else {
					$wlmst_client = new Wlmst_Client();
					// $wlmst_client->name = $request->lead_first_name . ' ' . $request->lead_last_name;
					$wlmst_client->name = $request->lead_first_name;
					$wlmst_client->email = $lead_email;
					$wlmst_client->mobile = $request->lead_phone_number;
					$wlmst_client->address = $request->lead_house_no . ', ' . $request->lead_addressline1 . ', ' . $lead_addressline2 . ', ' . $request->lead_area;
					$wlmst_client->isactive = 1;
					$wlmst_client->remark = 0;
					$wlmst_client->save();

					$Lead = new Lead();
					$Lead->customer_id = $wlmst_client->id;
					$Lead->status = 1;
					$Lead->sub_status = 0;
					$Lead->created_by = Auth::user()->id;
					$Lead->updated_by = Auth::user()->id;
					$Lead->entryip = $request->ip();
					$Lead->entry_source = $request->app_source;
				}

			}

			foreach (explode(",", $lead_competitor) as $key => $value) {
				$is_CRMSettingCompetitor = CRMSettingCompetitors::select('id')->where('id', $value)->orWhere('name', $value)->first();
				if ($is_CRMSettingCompetitor) {

					array_push($temp_comptitor, $is_CRMSettingCompetitor->id);
				} else {

					$CRMSettingCompetitor = new CRMSettingCompetitors();
					$CRMSettingCompetitor->name = $value;
					$CRMSettingCompetitor->status = 1;
					$CRMSettingCompetitor->save();

					array_push($temp_comptitor, $CRMSettingCompetitor->id);
				}
			}



			$Lead->first_name = $request->lead_first_name;
			$Lead->last_name = $request->lead_last_name;
			$Lead->email = $lead_email;
			$Lead->phone_number = $request->lead_phone_number;


			$Lead->house_no = $request->lead_house_no;
			$Lead->addressline1 = $request->lead_addressline1;
			$Lead->addressline2 = $lead_addressline2;
			$Lead->area = $request->lead_area;
			$Lead->pincode = $lead_pincode;
			$Lead->city_id = $request->lead_city_id;

			$Lead->meeting_house_no = $request->lead_meeting_house_no;
			$Lead->meeting_addressline1 = $request->lead_meeting_addressline1;
			$Lead->meeting_addressline2 = $lead_meeting_addressline2;
			$Lead->meeting_area = $request->lead_meeting_area;
			$Lead->meeting_pincode = $lead_meeting_pincode;
			$Lead->meeting_city_id = $request->lead_meeting_city_id;

			$Lead->source_type = $main_source_type;
			$Lead->source = $main_source;


			$Lead->site_stage = $request->lead_site_stage;
			$Lead->site_type = $request->lead_site_type;
			$Lead->bhk = $request->lead_bhk;
			$Lead->sq_foot = $lead_sq_foot;
			$Lead->want_to_cover = rtrim($request->lead_want_to_cover, ',');

			$Lead->budget = $lead_budget;

			$Lead->competitor = implode(',', $temp_comptitor);
			$Lead->assigned_to = $assigned_to;
			$Lead->architect = $lead_architect;
			$Lead->electrician = $lead_electrician;
			$Lead->channel_partner = $lead_channel_partner;
			$Lead->user_id = $assigned_to;
			if ($Lead->is_deal == 1) {
				$Lead->is_deal = 1;
			} else {
				$Lead->is_deal = 0;
			}
			$Lead->save();


			$response_error = array();
			if ($Lead) {
				if ($request->lead_id == 0) {
					$whatsapp_controller = new WhatsappApiContoller;
					$perameater_request = new Request();
					$perameater_request['q_whatsapp_massage_mobileno'] = $Lead->phone_number;
					$perameater_request['q_whatsapp_massage_template'] = 'lead_status1_inquiry';
					$perameater_request['q_whatsapp_massage_attechment'] = '';
					$perameater_request['q_broadcast_name'] = $Lead->first_name . ' ' . $Lead->last_name;
					$perameater_request['q_whatsapp_massage_parameters'] = array();
					$whatsapp_controller->sendTemplateMessage($perameater_request);
					// NEW LEAD SAVE TIME
					try {
						$timeline = array();
						$timeline['lead_id'] = $Lead->id;
						$timeline['type'] = "lead-generate";
						$timeline['reffrance_id'] = $Lead->id;
						$timeline['description'] = "Lead created";
						$timeline['source'] = $request->app_source;
						saveLeadTimeline($timeline);

						$LeadContact = new LeadContact();
						$LeadContact->lead_id = $Lead->id;
						$LeadContact->contact_tag_id = 1;
						$LeadContact->first_name = $Lead->first_name;
						$LeadContact->last_name = $Lead->last_name;
						$LeadContact->phone_number = $Lead->phone_number;
						$LeadContact->alernate_phone_number = 0;
						$LeadContact->email = $Lead->email;
						$LeadContact->type = 0;
						$LeadContact->type_detail = 0;
						$LeadContact->save();
						$Lead_contact_id = 0;
						if ($LeadContact) {
							$Lead_contact_id = $LeadContact->id;
						}

						// ADD ARCHITECT CONTACT START
						try {
							if ($Lead->architect != 0) {
								$Architect = User::find($Lead->architect);

								if ($Architect) {
									$LeadContact_arc = new LeadContact();
									$LeadContact_arc->lead_id = $Lead->id;
									$LeadContact_arc->contact_tag_id = 0;
									$LeadContact_arc->first_name = $Architect->first_name;
									$LeadContact_arc->last_name = $Architect->last_name;
									$LeadContact_arc->phone_number = $Architect->phone_number;
									$LeadContact_arc->alernate_phone_number = 0;
									$LeadContact_arc->email = $Architect->email;
									$LeadContact_arc->type = $Architect->type;
									$LeadContact_arc->type_detail = "user-" . $Architect->type . "-" . $Lead->architect;
									$LeadContact_arc->save();
								}
							}
						} catch (\Exception $e) {
							$response = errorRes("architect contact not saved, please contact to admin");
							$response['error'] = errorRes($e->getMessage(), 400);
							return response()->json($response)->header('Content-Type', 'application/json');
						}
						// ADD ARCHITECT CONTACT END

						// ADD ELECTRICIAN CONTACT START
						try {
							if ($Lead->electrician != 0) {
								$Electrician = User::find($Lead->electrician);

								if ($Electrician) {
									$LeadContact_ele = new LeadContact();
									$LeadContact_ele->lead_id = $Lead->id;
									$LeadContact_ele->contact_tag_id = 0;
									$LeadContact_ele->first_name = $Electrician->first_name;
									$LeadContact_ele->last_name = $Electrician->last_name;
									$LeadContact_ele->phone_number = $Electrician->phone_number;
									$LeadContact_ele->alernate_phone_number = 0;
									$LeadContact_ele->email = $Electrician->email;
									$LeadContact_ele->type = $Electrician->type;
									$LeadContact_ele->type_detail = "user-" . $Electrician->type . "-" . $Lead->electrician;
									$LeadContact_ele->save();
								}
							}
						} catch (\Exception $e) {
							$response = errorRes("electrician contact not saved, please contact to admin");
							$response['error'] = errorRes($e->getMessage(), 400);
							return response()->json($response)->header('Content-Type', 'application/json');
						}
						// ADD ELECTRICIAN CONTACT END
						// ADD MAIN SOURCE TO LEAD START
						try {
							if ($main_source != 0 && $main_source != null && $main_source != '') {
								if ($main_source_type != null || $main_source_type == "user-201" || $main_source_type == "user-202" || $main_source_type == "user-301" || $main_source_type == "user-302" || $main_source_type == "user-101" || $main_source_type == "user-102" || $main_source_type == "user-103" || $main_source_type == "user-104" || $multi_source_type == "user-105") {


									if ($main_source != $Lead->electrician && $main_source != $Lead->architect) {
										$Source_1 = User::where('id', $main_source)->first();

										if ($Source_1) {
											if ($Source_1->id != $Lead->electrician && $Source_1->id != $Lead->architect) {
												$LeadContact_s1 = new LeadContact();
												$LeadContact_s1->lead_id = $Lead->id;
												$LeadContact_s1->contact_tag_id = 0;
												if (isChannelPartner($Source_1->type) != 0) {
													$ChannelPartner = ChannelPartner::find($Source_1->reference_id);
													$LeadContact_s1->first_name = $ChannelPartner->firm_name;
													$LeadContact_s1->last_name = "";
												} else {
													$LeadContact_s1->first_name = $Source_1->first_name;
													$LeadContact_s1->last_name = $Source_1->last_name;
												}

												// $LeadContact_s1->first_name = $Source_1->first_name;
												// $LeadContact_s1->last_name = $Source_1->last_name;
												$LeadContact_s1->phone_number = $Source_1->phone_number;
												$LeadContact_s1->alernate_phone_number = 0;
												$LeadContact_s1->email = $Source_1->email;
												$LeadContact_s1->type = $Source_1->type;
												$LeadContact_s1->type_detail = "user-" . $Source_1->type . "-" . $Source_1->id;
												$LeadContact_s1->save();
											}
										}

										// $Source_1 = User::where('id', $main_source)->first();

										// if ($Source_1) {
										//     if ($Source_1->id != $main_source) {
										//         $LeadContact_s1 = new LeadContact();
										//         $LeadContact_s1->lead_id = $Lead->id;
										//         $LeadContact_s1->contact_tag_id = 0;
										//         $LeadContact_s1->first_name = $Source_1->first_name;
										//         $LeadContact_s1->last_name = $Source_1->last_name;
										//         $LeadContact_s1->phone_number = $Source_1->phone_number;
										//         $LeadContact_s1->alernate_phone_number = 0;
										//         $LeadContact_s1->email = $Source_1->email;
										//         $LeadContact_s1->type = $Source_1->type;
										//         $LeadContact_s1->type_detail = "user-" . $Source_1->type . "-" . $Source_1->id;
										//         $LeadContact_s1->save();
										//     }
										// }
									}
									$LeadSource1 = new LeadSource();
									$LeadSource1->lead_id = $Lead->id;
									$LeadSource1->source_type = $main_source_type;
									$LeadSource1->source = $main_source;
									$LeadSource1->is_main = 1;
									$LeadSource1->save();
								} else {
									if (($main_source != 0) || ($main_source != null) || ($main_source != '')) {
										$LeadSource1 = new LeadSource();
										$LeadSource1->lead_id = $Lead->id;
										$LeadSource1->source_type = $main_source_type;
										$LeadSource1->is_main = 1;
										$LeadSource1->source = $main_source;
										$LeadSource1->save();
									}
								}
							}
						} catch (\Exception $e) {
							$response = errorRes("main source not saved, please contact to admin");
							$response['error'] = errorRes($e->getMessage(), 400);
							return response()->json($response)->header('Content-Type', 'application/json');
						}
						// ADD MAIN SOURCE TO LEAD END
						// ADD MULTI SOURCE SAVE TO CONTACT START
						try {
							if ($request->no_of_source > 0) {
                                for ($i = 1; $i <= $request->no_of_source; $i++) {
                                    if(isset($request->all()['lead_source_type_' . $i])){

                                        $multi_source_type = $request->all()['lead_source_type_' . $i];
    
                                        if (explode('-', $multi_source_type)[0] == 'textrequired') {
                                            $multi_source = $request->all()['lead_source_text_' . $i];
                                        } elseif (explode('-', $multi_source_type)[0] == 'textnotrequired') {
                                            $multi_source = $request->all()['lead_source_text_' . $i];
                                        } elseif (explode('-', $multi_source_type)[0] == 'fix') {
                                            $multi_source = $request->all()['lead_source_text_' . $i];
                                        } else {
                                            $multi_source = $request->all()['lead_source_' . $i];
                                        }
    
                                        if ($multi_source_type != null || $multi_source_type == 'user-201' || $multi_source_type == 'user-202' || $multi_source_type == 'user-301' || $multi_source_type == 'user-302' || $multi_source_type == 'user-101' || $multi_source_type == 'user-102' || $multi_source_type == 'user-103' || $multi_source_type == 'user-104' || $multi_source_type == 'user-105') {
                                            if ($multi_source != 0 || $multi_source != null || $multi_source != '') {
                                                if ($multi_source != $Lead->electrician && $multi_source != $Lead->architect) {
                                                    $multi_Source_1 = User::where('id', $multi_source)->first();
    
                                                    if ($multi_Source_1) {
                                                        if ($multi_Source_1->id != $Lead->electrician && $multi_Source_1->id != $Lead->architect) {
                                                            $LeadContact_s1 = new LeadContact();
                                                            $LeadContact_s1->lead_id = $Lead->id;
                                                            $LeadContact_s1->contact_tag_id = 0;
                                                            if (isChannelPartner($multi_Source_1->type) != 0) {
                                                                $ChannelPartner = ChannelPartner::find($multi_Source_1->reference_id);
                                                                $LeadContact_s1->first_name = $ChannelPartner->firm_name;
                                                                $LeadContact_s1->last_name = '';
                                                            } else {
                                                                $LeadContact_s1->first_name = $multi_Source_1->first_name;
                                                                $LeadContact_s1->last_name = $multi_Source_1->last_name;
                                                            }
                                                            // $LeadContact_s1->first_name = $multi_Source_1->first_name;
                                                            // $LeadContact_s1->last_name = $multi_Source_1->last_name;
                                                            $LeadContact_s1->phone_number = $multi_Source_1->phone_number;
                                                            $LeadContact_s1->alernate_phone_number = 0;
                                                            $LeadContact_s1->email = $multi_Source_1->email;
                                                            $LeadContact_s1->type = $multi_Source_1->type;
                                                            $LeadContact_s1->type_detail = 'user-' . $multi_Source_1->type . '-' . $multi_Source_1->id;
                                                            $LeadContact_s1->save();
                                                        }
                                                    }
                                                }
    
                                                // $multi_Source_1 = User::where('id', $multi_source)->first();
    
                                                // if ($multi_Source_1) {
                                                //     if ($multi_Source_1->id != $multi_source) {
                                                //         $LeadContact_s1 = new LeadContact();
                                                //         $LeadContact_s1->lead_id = $Lead->id;
                                                //         $LeadContact_s1->contact_tag_id = 0;
                                                //         $LeadContact_s1->first_name = $multi_Source_1->first_name;
                                                //         $LeadContact_s1->last_name = $multi_Source_1->last_name;
                                                //         $LeadContact_s1->phone_number = $multi_Source_1->phone_number;
                                                //         $LeadContact_s1->alernate_phone_number = 0;
                                                //         $LeadContact_s1->email = $multi_Source_1->email;
                                                //         $LeadContact_s1->type = $multi_Source_1->type;
                                                //         $LeadContact_s1->type_detail = "user-" . $multi_Source_1->type . "-" . $multi_Source_1->id;
                                                //         $LeadContact_s1->save();
                                                //     }
                                                // }
                                                $LeadSource1 = new LeadSource();
                                                $LeadSource1->lead_id = $Lead->id;
                                                $LeadSource1->source_type = $multi_source_type;
                                                $LeadSource1->source = $multi_source;
                                                $LeadSource1->save();
                                            }
                                        } else {
                                            if ($multi_source != 0 || $multi_source != null || $multi_source != '') {
                                                $LeadSource1 = new LeadSource();
                                                $LeadSource1->lead_id = $Lead->id;
                                                $LeadSource1->source_type = $multi_source_type;
                                                $LeadSource1->source = $multi_source;
                                                $LeadSource1->save();
                                            }
                                        }
                                    }
                                }
                            }

						} catch (\Exception $e) {
							$response = errorRes("multi source not saved, please contact to admin");
							$response['error'] = errorRes($e->getMessage(), 400);
							return response()->json($response)->header('Content-Type', 'application/json');
						}
						// ADD MULTI SOURCE SAVE TO CONTACT END



						// ADD CLOSING DATE IN CLOSING TABLE START
						// if ($lead_closing_date_and_time != '' || $lead_closing_date_and_time != null) {
						// 	$lead_closing_date_time = $lead_closing_date_and_time . date('H:i:s');
						// 	$lead_closing_date_time = date('Y-m-d H:i:s', strtotime($lead_closing_date_time));

						// 	try {
						// 		$LeadClosing = new LeadClosing();
						// 		$LeadClosing->lead_id = $Lead->id;
						// 		$LeadClosing->closing_date = $lead_closing_date_time;
						// 		$LeadClosing->entryby = Auth::user()->id;
						// 		$LeadClosing->entryip = $request->ip();
						// 		$LeadClosing->save();
						// 	} catch (\Exception $e) {
						// 		$response = errorRes("closing date not saved, please contact to admin");
						// 		$response['error'] = errorRes($e->getMessage(), 400);
						// 		return response()->json($response)->header('Content-Type', 'application/json');
						// 	}
						// } else {
						// 	$lead_closing_date_time = $lead_closing_date_and_time;
						// }
						// ADD CLOSING DATE IN CLOSING TABLE END

						if($Lead_contact_id != 0){
							$Lead_Update = Lead::find($Lead->id);
							$Lead_Update->main_contact_id = $Lead_contact_id;
							// $Lead_Update->closing_date_time = $lead_closing_date_time;
							$Lead_Update->save();
                        }

						$response = successRes("Successfully saved lead");
						$response['id'] = $Lead->id;
					} catch (\Exception $e) {
						$response = errorRes("lead date not saved, please contact to admin");
						$response['error'] = errorRes($e->getMessage(), 400);
						return response()->json($response)->header('Content-Type', 'application/json');
					}
				} else {
					// LEAD EDIT TIME
					try {

						$timeline = array();
						$timeline['lead_id'] = $Lead->id;
						$timeline['type'] = "lead-update";
						$timeline['reffrance_id'] = $Lead->id;
						$timeline['description'] = "Lead Detail Updated " . $change_field;
						$timeline['source'] = $request->app_source;
						saveLeadTimeline($timeline);



						$LeadContact = LeadContact::find($Lead->main_contact_id);
						$LeadContact->lead_id = $Lead->id;
						$LeadContact->contact_tag_id = 1;
						$LeadContact->first_name = $Lead->first_name;
						$LeadContact->last_name = $Lead->last_name;
						$LeadContact->phone_number = $Lead->phone_number;
						$LeadContact->alernate_phone_number = 0;
						$LeadContact->email = $Lead->email;
						$LeadContact->type = 0;
						$LeadContact->type_detail = 0;
						$LeadContact->save();
						$Lead_contact_id = 0;
						if ($LeadContact) {
							$Lead_contact_id = $LeadContact->id;
						}

						LeadContact::where([
							['lead_contacts.lead_id', $Lead->id],
							['lead_contacts.id', '!=', $Lead->main_contact_id],
							['lead_contacts.type', '!=', 0],
						])->delete();
						LeadSource::where([
							['lead_sources.lead_id', $Lead->id]
						])->delete();

						// ADD ARCHITECT CONTACT START
						try {
							if ($Lead->architect != 0) {

								$Architect = User::find($Lead->architect);

								if ($Architect) {
									$new_type_detail = "user-" . $Architect->type . "-" . $Lead->architect;

									$status_update_arc = LeadContact::query();
									$status_update_arc->where('lead_id', $Lead->id);
									$status_update_arc->where('contact_tag_id', 0);
									$status_update_arc->where('type_detail', $new_type_detail);
									$status_update_arc = $status_update_arc->first();

									if ($status_update_arc) {
										$status_update_arc->status = 1;
										$status_update_arc->save();
									} else {
										$LeadContact_arc = new LeadContact();
										$LeadContact_arc->lead_id = $Lead->id;
										$LeadContact_arc->contact_tag_id = 0;
										$LeadContact_arc->first_name = $Architect->first_name;
										$LeadContact_arc->last_name = $Architect->last_name;
										$LeadContact_arc->phone_number = $Architect->phone_number;
										$LeadContact_arc->alernate_phone_number = 0;
										$LeadContact_arc->email = $Architect->email;
										$LeadContact_arc->type = $Architect->type;
										$LeadContact_arc->type_detail = "user-" . $Architect->type . "-" . $Lead->architect;
										$LeadContact_arc->save();
									}
								}
							}
						} catch (\Exception $e) {
							$response = errorRes("lead architect not saved, please contact to admin");
							$response['error'] = errorRes($e->getMessage(), 400);
							return response()->json($response)->header('Content-Type', 'application/json');
						}
						// ADD ARCHITECT CONTACT END

						// ADD ELECTRICIAN CONTACT START
						try {
							if ($Lead->electrician != 0) {
								$Electrician = User::find($Lead->electrician);

								if ($Electrician) {
									$new_type_detail = "user-" . $Electrician->type . "-" . $Lead->electrician;

									$status_update_ele = LeadContact::query();
									$status_update_ele->where('lead_id', $Lead->id);
									$status_update_ele->where('contact_tag_id', 0);
									$status_update_ele->where('type_detail', $new_type_detail);
									$status_update_ele = $status_update_ele->first();

									if ($status_update_ele) {
										$status_update_ele->status = 1;
										$status_update_ele->save();
									} else {
										$LeadContact_ele = new LeadContact();
										$LeadContact_ele->lead_id = $Lead->id;
										$LeadContact_ele->contact_tag_id = 0;
										$LeadContact_ele->first_name = $Electrician->first_name;
										$LeadContact_ele->last_name = $Electrician->last_name;
										$LeadContact_ele->phone_number = $Electrician->phone_number;
										$LeadContact_ele->alernate_phone_number = 0;
										$LeadContact_ele->email = $Electrician->email;
										$LeadContact_ele->type = $Electrician->type;
										$LeadContact_ele->type_detail = "user-" . $Electrician->type . "-" . $Lead->electrician;
										$LeadContact_ele->save();
									}
								}
							}
						} catch (\Exception $e) {
							$response = errorRes("lead electrician not saved, please contact to admin");
							$response['error'] = errorRes($e->getMessage(), 400);
							return response()->json($response)->header('Content-Type', 'application/json');
						}
						// ADD ELECTRICIAN CONTACT END

						// ADD MULTI SOURCE SAVE TO CONTACT START
						try {
                            if ($request->no_of_source > 0) {
                                for ($i = 1; $i <= $request->no_of_source; $i++) {
                                    if(isset($request->all()['lead_source_type_' . $i])){

                                        $multi_source_type = $request->all()['lead_source_type_' . $i];
    
                                        if (explode('-', $multi_source_type)[0] == 'textrequired') {
                                            $multi_source = $request->all()['lead_source_text_' . $i];
                                        } elseif (explode('-', $multi_source_type)[0] == 'textnotrequired') {
                                            $multi_source = $request->all()['lead_source_text_' . $i];
                                        } elseif (explode('-', $multi_source_type)[0] == 'fix') {
                                            $multi_source = $request->all()['lead_source_text_' . $i];
                                        } else {
                                            $multi_source = $request->all()['lead_source_' . $i];
                                        }
    
                                        if (($multi_source_type != null && $multi_source_type == 'user-201') || $multi_source_type == 'user-202' || $multi_source_type == 'user-301' || $multi_source_type == 'user-302' || $multi_source_type == 'user-101' || $multi_source_type == 'user-102' || $multi_source_type == 'user-103' || $multi_source_type == 'user-104' || $multi_source_type == 'user-105') {
                                            if ($multi_source != 0 || $multi_source != null || $multi_source != '') {
                                                if ($multi_source != $Lead->electrician && $multi_source != $Lead->architect) {
                                                    $multi_Source_1 = User::where('id', $multi_source)->first();
    
                                                    if ($multi_Source_1) {
                                                        if ($multi_Source_1->id != $Lead->electrician && $multi_Source_1->id != $Lead->architect) {
                                                            $new_type_detail = 'user-' . $multi_Source_1->type . '-' . $multi_Source_1->id;
    
                                                            $status_update_s1 = LeadContact::query();
                                                            $status_update_s1->where('lead_id', $Lead->id);
                                                            $status_update_s1->where('contact_tag_id', 0);
                                                            $status_update_s1->where('type_detail', $new_type_detail);
                                                            $status_update_s1 = $status_update_s1->first();
    
                                                            if ($status_update_s1) {
                                                                $status_update_s1->status = 1;
                                                                $status_update_s1->save();
                                                            } else {
                                                                $LeadContact_s1 = new LeadContact();
                                                                $LeadContact_s1->lead_id = $Lead->id;
                                                                $LeadContact_s1->contact_tag_id = 0;
                                                                if (isChannelPartner($multi_Source_1->type) != 0) {
                                                                    $ChannelPartner = ChannelPartner::find($multi_Source_1->reference_id);
                                                                    $LeadContact_s1->first_name = $ChannelPartner->firm_name;
                                                                    $LeadContact_s1->last_name = '';
                                                                } else {
                                                                    $LeadContact_s1->first_name = $multi_Source_1->first_name;
                                                                    $LeadContact_s1->last_name = $multi_Source_1->last_name;
                                                                }
                                                                $LeadContact_s1->phone_number = $multi_Source_1->phone_number;
                                                                $LeadContact_s1->alernate_phone_number = 0;
                                                                $LeadContact_s1->email = $multi_Source_1->email;
                                                                $LeadContact_s1->type = $multi_Source_1->type;
                                                                $LeadContact_s1->type_detail = 'user-' . $multi_Source_1->type . '-' . $multi_Source_1->id;
                                                                $LeadContact_s1->save();
                                                            }
                                                        }
                                                    }
                                                }
    
                                                // $multi_Source_1 = User::where('id', $multi_source)->first();
    
                                                // if ($multi_Source_1) {
                                                //     if ($multi_Source_1->id != $multi_source) {
                                                //         $LeadContact_s1 = new LeadContact();
                                                //         $LeadContact_s1->lead_id = $Lead->id;
                                                //         $LeadContact_s1->contact_tag_id = 0;
                                                //         $LeadContact_s1->first_name = $multi_Source_1->first_name;
                                                //         $LeadContact_s1->last_name = $multi_Source_1->last_name;
                                                //         $LeadContact_s1->phone_number = $multi_Source_1->phone_number;
                                                //         $LeadContact_s1->alernate_phone_number = 0;
                                                //         $LeadContact_s1->email = $multi_Source_1->email;
                                                //         $LeadContact_s1->type = $multi_Source_1->type;
                                                //         $LeadContact_s1->type_detail = "user-" . $multi_Source_1->type . "-" . $multi_Source_1->id;
                                                //         $LeadContact_s1->save();
                                                //     }
                                                // }
                                                $LeadSource1 = new LeadSource();
                                                $LeadSource1->lead_id = $Lead->id;
                                                $LeadSource1->source_type = $multi_source_type;
                                                $LeadSource1->is_main = 0;
                                                $LeadSource1->source = $multi_source;
                                                $LeadSource1->save();
                                            }
                                        } else {
                                            if ($multi_source != 0 || $multi_source != null || $multi_source != '') {
                                                $LeadSource1 = new LeadSource();
                                                $LeadSource1->lead_id = $Lead->id;
                                                $LeadSource1->source_type = $multi_source_type;
                                                $LeadSource1->is_main = 0;
                                                $LeadSource1->source = $multi_source;
                                                $LeadSource1->save();
                                            }
                                        }
                                    }
                                }
                            }
                        } catch (\Exception $e) {
                            $response_error['error_multi_source'] = errorRes($e->getMessage(), 400);
                        }
						// ADD MULTI SOURCE SAVE TO CONTACT END

						// ADD MAIN SOURCE TO LEAD START
						try {
							if (($main_source != 0) && ($main_source != null) && ($main_source != '')) {
								if ($main_source_type != null || $main_source_type == "user-201" || $main_source_type == "user-202" || $main_source_type == "user-301" || $main_source_type == "user-302" || $main_source_type == "user-101" || $main_source_type == "user-102" || $main_source_type == "user-103" || $main_source_type == "user-104" || $multi_source_type == "user-105") {


									if ($main_source != $Lead->electrician && $main_source != $Lead->architect) {
										$Source_1 = User::where('id', $main_source)->first();

										if ($Source_1) {
											if (($Source_1->id != $Lead->electrician) && ($Source_1->id != $Lead->architect)) {
												$new_type_detail = "user-" . $Source_1->type . "-" . $Source_1->id;

												$status_update_s2 = LeadContact::query();
												$status_update_s2->where('lead_id', $Lead->id);
												$status_update_s2->where('contact_tag_id', 0);
												$status_update_s2->where('type_detail', $new_type_detail);
												$status_update_s2 = $status_update_s2->first();

												if ($status_update_s2) {
													$status_update_s2->status = 1;
													$status_update_s2->save();
												} else {
													$LeadContact_s1 = new LeadContact();
													$LeadContact_s1->lead_id = $Lead->id;
													$LeadContact_s1->contact_tag_id = 0;
													if (isChannelPartner($Source_1->type) != 0) {
														$ChannelPartner = ChannelPartner::find($Source_1->reference_id);
														$LeadContact_s1->first_name = $ChannelPartner->firm_name;
														$LeadContact_s1->last_name = "";
													} else {
														$LeadContact_s1->first_name = $Source_1->first_name;
														$LeadContact_s1->last_name = $Source_1->last_name;
													}
													// $LeadContact_s1->first_name = $Source_1->first_name;
													// $LeadContact_s1->last_name = $Source_1->last_name;
													$LeadContact_s1->phone_number = $Source_1->phone_number;
													$LeadContact_s1->alernate_phone_number = 0;
													$LeadContact_s1->email = $Source_1->email;
													$LeadContact_s1->type = $Source_1->type;
													$LeadContact_s1->type_detail = "user-" . $Source_1->type . "-" . $Source_1->id;
													$LeadContact_s1->save();
												}
											}
										}
									}

									// $Source_1 = User::where('id', $main_source)->first();

									// if ($Source_1) {
									//     if ($Source_1->id != $main_source) {
									//         $LeadContact_s1 = new LeadContact();
									//         $LeadContact_s1->lead_id = $Lead->id;
									//         $LeadContact_s1->contact_tag_id = 0;
									//         $LeadContact_s1->first_name = $Source_1->first_name;
									//         $LeadContact_s1->last_name = $Source_1->last_name;
									//         $LeadContact_s1->phone_number = $Source_1->phone_number;
									//         $LeadContact_s1->alernate_phone_number = 0;
									//         $LeadContact_s1->email = $Source_1->email;
									//         $LeadContact_s1->type = $Source_1->type;
									//         $LeadContact_s1->type_detail = "user-" . $Source_1->type . "-" . $Source_1->id;
									//         $LeadContact_s1->save();
									//     }
									// }
									$LeadSource1 = new LeadSource();
									$LeadSource1->lead_id = $Lead->id;
									$LeadSource1->source_type = $main_source_type;
									$LeadSource1->source = $main_source;
									$LeadSource1->is_main = 1;
									$LeadSource1->save();
								} else {
									if (($main_source != 0) || ($main_source != null) || ($main_source != '')) {
										$LeadSource1 = new LeadSource();
										$LeadSource1->lead_id = $Lead->id;
										$LeadSource1->source_type = $main_source_type;
										$LeadSource1->source = $main_source;
										$LeadSource1->is_main = 1;
										$LeadSource1->save();
									}
								}
							}
						} catch (\Exception $e) {
							$response = errorRes("lead source not saved, please contact to admin");
							$response['error'] = errorRes($e->getMessage(), 400);
							return response()->json($response)->header('Content-Type', 'application/json');
						}
						// ADD MAIN SOURCE TO LEAD END

						// ADD CLOSING DATE IN CLOSING TABLE START
						if ($lead_closing_date_and_time != '' || $lead_closing_date_and_time != null) {
							$lead_closing_date_time = $lead_closing_date_and_time . " 23:59:59";
							$lead_closing_date_time = date('Y-m-d H:i:s', strtotime($lead_closing_date_time . " -5 hours"));
							$lead_closing_date_time = date('Y-m-d H:i:s', strtotime($lead_closing_date_time . " -30 minutes"));

							try {
								$LeadClosing = new LeadClosing();
								$LeadClosing->lead_id = $Lead->id;
								$LeadClosing->closing_date = $lead_closing_date_time;
								$LeadClosing->entryby = Auth::user()->id;
								$LeadClosing->entryip = $request->ip();
								$LeadClosing->save();
							} catch (\Exception $e) {
								$response = errorRes("lead closing date not saved, please contact to admin");
								$response['error'] = errorRes($e->getMessage(), 400);
								return response()->json($response)->header('Content-Type', 'application/json');
							}
						} else {
							$lead_closing_date_time = $lead_closing_date_and_time;
						}
						// ADD CLOSING DATE IN CLOSING TABLE END

						$Lead_Update = Lead::find($Lead->id);
						if($Lead_contact_id != 0){
                            $Lead_Update->main_contact_id = $Lead_contact_id;
                        }

						$Lead_Update->closing_date_time = $lead_closing_date_time;
						$Lead_Update->save();

						$response = successRes("Successfully Updated lead");
						$response['id'] = $Lead->id;
						// $response['lead_source_type'] = $request->lead_source_type;
					} catch (\Exception $e) {
						$response = errorRes("lead  not saved, please contact to admin");
						$response['error'] = errorRes($e->getMessage(), 400);
						return response()->json($response)->header('Content-Type', 'application/json');
					}
				}
			} else {
				$response = errorRes("lead not saved, please contact to admin");
				$response['error'] = errorRes($Lead, 400);
				$response['error_id'] = $request->lead_id;
				return response()->json($response)->header('Content-Type', 'application/json');
			}

			$response['error'] = $response_error;
		}

		return response()->json($response)->header('Content-Type', 'application/json');
	}
	function searchSourceType(Request $request)
	{
		$searchKeyword = isset($request->q) ? $request->q : "";

		// $sourcetype_master = CRMSettingSourceType::select('id', 'name as text');
		// $sourcetype_master->where('crm_setting_source_type.status', 1);
		// $sourcetype_master->where('crm_setting_source_type.name', 'like', "%" . $searchKeyword . "%");
		// $sourcetype_master = $sourcetype_master->get();

		$data = array();
		// foreach ($sourcetype_master as $source_master_key => $source_master_value) {
		// 	$source_master['id'] = "master-" . $source_master_value['id'];
		// 	$source_master['type'] = "master";
		// 	$source_master['text'] = $source_master_value['text'];
		// 	array_push($data, $source_master);
		// }

		foreach (getLeadSourceTypes() as $static_key => $static_value) {
			if ($static_value['id'] != 8 && $static_value['id'] != 5) {
				$fix_source_data['id'] = $static_value['type'] . "-" . $static_value['id'];
				$fix_source_data['type'] = $static_value['type'];
				$fix_source_data['text'] = $static_value['lable'];
				$fix_source_data['is_editable'] = $static_value['is_editable'];
				array_push($data, $fix_source_data);
			}
		}

		$response = array();

		$response = successRes();
		$response['data'] = $data;
		return response()->json($response)->header('Content-Type', 'application/json');


	}

	function searchSource(Request $request)
	{
		try {
			$isArchitect = isArchitect();
			$isSalePerson = isSalePerson();
			$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
			$isThirdPartyUser = isThirdPartyUser();
			$isChannelPartner = isChannelPartner(Auth::user()->type);

			$searchKeyword = $request->q;
			$source_type = explode("-", $request->source_type);


			if ($source_type[0] == "user") {

				if (isset(getChannelPartners()[$source_type[1]]['short_name'])) {

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

					$data = User::select('users.id', 'channel_partner.firm_name  AS text', 'users.phone_number');
					$data->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
					$data->whereIn('users.status', [1,2,3,4,5]);
					$data->where('users.type', $source_type[1]);
					if ($isSalePerson == 1) {

						$data->where(function ($query) use ($cities, $childSalePersonsIds) {

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
					$data->where(function ($query) use ($searchKeyword) {
						$query->where('channel_partner.firm_name', 'like', '%' . $searchKeyword . '%');
					});
					$data->limit(10);
					$data = $data->get();
				} else {
					$data = User::select('users.id', 'users.type', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"), 'users.phone_number');
					$data->whereIn('users.status', [1,2,3,4,5]);

					if ($source_type[1] == 202) {
						// FOR ARCHITECT
						if ($isSalePerson == 1) {
							$salePerson = SalePerson::select('cities')->where('user_id', Auth::user()->id)->first();
							$cities = array();
							if ($salePerson) {

								$cities = explode(",", $salePerson->cities);
							} else {
								$cities = array(0);
							}
							$data->whereIn('users.city_id', $cities);
						} elseif ($isChannelPartner != 0) {
							$data->where('users.city_id', Auth::user()->city_id);
						}

						$data->whereIn('users.type', [201, 202]);

					} else if ($source_type[1] == 302) {
						// FOR ELECTRICIAN
						if ($isSalePerson == 1) {
							$salePerson = SalePerson::select('cities')->where('user_id', Auth::user()->id)->first();
							$cities = array();
							if ($salePerson) {

								$cities = explode(",", $salePerson->cities);
							} else {
								$cities = array(0);
							}
							$data->whereIn('users.city_id', $cities);
						} elseif ($isChannelPartner != 0) {
							$data->where('users.city_id', Auth::user()->city_id);
						}

						$data->whereIn('users.type', [301, 302]);

					} else {
						$data->where('users.type', $source_type[1]);

					}

					$data->where(function ($query) use ($searchKeyword) {
						$query->where('users.first_name', 'like', '%' . $searchKeyword . '%');
						$query->orWhere('users.last_name', 'like', '%' . $searchKeyword . '%');
					});
					$data->limit(10);
					$data = $data->get();
					$newdata = array();
					foreach ($data as $key => $value) {
						$data1['id'] = $value->id;
						$label = '';
						if ($value->type == 301 || $value->type == 201) {
							$label = ' - NonPrime';
						} elseif ($value->type == 302 || $value->type == 202) {
							$label = ' - Prime';
						} else {
							$label = '';
						}
						$data1['text'] = $value->text . $label;
						$data1['phone_number'] = $value->phone_number;
						array_push($newdata, $data1);
					}
					$data = $newdata;



				}
				$response = successRes();
				$response['data'] = $data;

			} elseif ($source_type[0] == "master") {
				$data = CRMSettingSource::select('id', 'name as text');
				$data->where('crm_setting_source.status', 1);
				$data->where('crm_setting_source.source_type_id', $source_type[1]);
				$data->where('crm_setting_source.name', 'like', "%" . $searchKeyword . "%");
				$data->limit(5);
				$data = $data->get();
				$response = successRes();
				$response['data'] = $data;
			} elseif ($source_type[0] == "exhibition") {
				$data = Exhibition::select('id', 'name as text');
				$data->where('exhibition.name', 'like', "%" . $searchKeyword . "%");
				$data->limit(5);
				$data = $data->get();
				$response = successRes();
				$response['data'] = $data;
			} else {
				$response = successRes();
				$response['data'] = "";
			}

		} catch (QueryException $ex) {
			$response = errorRes();
		}
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function searchAssignedUser(Request $request)
	{

		$isSalePerson = isSalePerson();
		$isReception = isReception();
		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		$isThirdPartyUser = isThirdPartyUser();
		$isChannelPartner = isChannelPartner(Auth::user()->type);
		$isTaleSalesUser = isTaleSalesUser();
        $isArchitect = isArchitect();
        $isElectrician = isElectrician();
		if ($isSalePerson == 1) {
			$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
		} else if ($isChannelPartner != 0) {
			// $channelPartnersSalesPersons = getChannelPartnerSalesPersonsIds(Auth::user()->id);
		}

		$UserResponse = array();
		$q = $request->q;
		$User = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));

		$User->where('users.status', 1);

		if ($isAdminOrCompanyAdmin == 1) {
            $User->whereIn('users.type', array(2));
            // $User->whereIn('users.type', array(0, 1, 2));
			
        } else if ($isThirdPartyUser == 1) {
            $User->whereIn('users.type', array(2));
            $User->where('users.city_id', Auth::user()->city_id);

        } else if ($isSalePerson == 1) {
            $User->where('users.type', 2);
            $User->whereIn('users.id', $childSalePersonsIds);

        } else if ($isChannelPartner != 0) {
            $User->where('users.type', 2);
            $User->where('users.city_id', Auth::user()->city_id);

        } elseif ($isTaleSalesUser == 1 || $isReception == 1) {
            $User->where('users.type', 2);

        } else if ($isArchitect == 1 || $isElectrician == 1){
            $User->where('users.type', 2);
            $User->where('users.city_id', Auth::user()->city_id);
        }

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

		$response = successRes("search assigned user");
		$response['data'] = $UserResponse;
		return response()->json($response, $response['status_code'])->header('Content-Type', 'application/json');
	}

	function getDetail(Request $request)
	{
		// try {
		$isArchitect = isArchitect();
		$isElectrician = isElectrician();
		$Lead = Lead::find($request->id);
		$data = array();

		if ($Lead) {

			$Lead = json_encode($Lead);
			$Lead = json_decode($Lead, true);

			$data['lead'] = $Lead;
			$data['lead']['created_at1'] = convertDateAndTimeMounth($data['lead']['created_at'], "date") . " " . convertDateAndTimeMounth($data['lead']['created_at'], "time");

			$LeadStatus = getLeadStatus();

			if ($data['lead']['status']) {
				$LeadStatus = getLeadStatus()[$data['lead']['status']]['name'];
				$data['lead']['lead_status_label'] = $LeadStatus;
				$data['lead']['lead_status'] = $data['lead']['status'];
			} else {
				$data['lead']['lead_status_label'] = "not define";
				$data['lead']['lead_status'] = " ";
			}

			if ($data['lead']['status']) {
				if ($isArchitect == 1 || $isElectrician == 1) {
					$LeadArcEleStatus = getLeadStatusForArcEle($data['lead']['status']);
					$data['lead']['lead_status_for_architect_or_electrician'] = $LeadArcEleStatus;
				} else {
					$LeadStatusForUser_new = array();
					$LeadStatusForUser = getLeadStatus($data['lead']['status']);
					$l = 1;
					$d = 1;
					$is_active = 0;
					foreach ($LeadStatusForUser as $st_key => $st_value) {
						if($st_value['is_active'] == 1)
						{
							$is_active = 1;
						}
						
						if($is_active == 1){
							if ($data['lead']['is_deal'] == 0) {
								if ($st_value['type'] == 0) {
									$LeadStatusForUser_new[$l]['id'] = $st_value['id'];
									$LeadStatusForUser_new[$l]['name'] = $st_value['name'];
									$LeadStatusForUser_new[$l]['type'] = $st_value['type'];
									$LeadStatusForUser_new[$l]['index'] = $st_value['index'];
									$LeadStatusForUser_new[$l]['is_active'] = $st_value['is_active'];
									
									$l++;
								}
							} else {
								if ($st_value['type'] == 1) {
									$LeadStatusForUser_new[$d]['id'] = $st_value['id'];
									$LeadStatusForUser_new[$d]['name'] = $st_value['name'];
									$LeadStatusForUser_new[$d]['type'] = $st_value['type'];
									$LeadStatusForUser_new[$d]['index'] = $st_value['index'];
									$LeadStatusForUser_new[$d]['is_active'] = $st_value['is_active'];
									$d++;
								}
							}
						}
					}
					$data['lead']['lead_status_for_architect_or_electrician'] = $LeadStatusForUser_new;
				}
			} else {
				$data['lead']['lead_status_for_architect_or_electrician'] = "";
			}
			
			$data['lead']['material_sent_by'] = "";
			
			if($data['lead']['status'] == 103){
				$LeadQuestionAnswer = LeadQuestionAnswer::select('lead_question_answer.lead_question_id', 'lead_question.question', 'lead_question_answer.answer', 'lead_question.type', 'lead_question_answer.created_at', 'lead_question_answer.updated_at');
            	$LeadQuestionAnswer->leftJoin('lead_question', 'lead_question.id', '=', 'lead_question_answer.lead_question_id');
            	$LeadQuestionAnswer->where('lead_question_answer.lead_id', $data['lead']['id']);
            	$LeadQuestionAnswer->whereIn('lead_question.id', array(19));
            	$LeadQuestionAnswer->where('lead_question_answer.reference_type', 'Lead-Status-Update');
            	$LeadQuestionAnswer->where('lead_question_answer.answer', '!=', '');
            	$LeadQuestionAnswer = $LeadQuestionAnswer->first();
				if($LeadQuestionAnswer){
					$ChannelPart = ChannelPartner::select('firm_name')->where('user_id', $LeadQuestionAnswer->answer)->first();
					if ($ChannelPart) {
						$data['lead']['material_sent_by'] = $ChannelPart->firm_name;
					}
				}
			}


			// $LeadNextStatus = getLeadStatus();
			// $next_status = $data['lead']['status'] + 1;
			// foreach ($LeadNextStatus as $key => $next_status_value) {
			// 	if ($next_status_value['id'] == $next_status) {
			// 		$data['lead']['lead_next_status_label'] = getLeadStatus();
			// 		break;
			// 	} else {
			// 		$data['lead']['lead_next_status_label'] = getLeadStatus();
			// 	}
			// }

			$LeadSource = LeadSource::query();
            $LeadSource->select('source_type', 'source');
            $LeadSource->where('lead_id', $data['lead']['id']);
            $LeadSource->where('is_main', "!=", 1);
            $LeadSource->where('source', "!=", '');
            $LeadSource->orWhereNull('source');
            $LeadSourcelist = $LeadSource->get();

            $LeadSource_new = [];
            foreach ($LeadSourcelist as $source_key => $source_value) {
                if ($source_value->source_type != 0 || $source_value->source_type != null || $source_value->source_type != '') {
                    if ($source_value->source_type == 'user-201') {
                        $new_source['id'] = 202;
                    } elseif ($source_value->source_type == 'user-301') {
                        $new_source['id'] = 302;
                    } else {
                        $new_source['id'] = $source_value->source_type;
                    }

                    $source_type = explode('-', $source_value->source_type);
                    foreach (getLeadSourceTypes() as $source_type_key => $source_type_value) {
                        if ($source_type[1] == 201) {
                            $source_type_id = 202;
                        } elseif ($source_type[1] == 301) {
                            $source_type_id = 302;
                        } else {
                            $source_type_id = $source_type[1];
                        }

                        if ($source_type_value['type'] == $source_type[0] && $source_type_value['id'] == $source_type_id) {
                            $new_source['text'] = $source_type_value['lable'];
                            $new_source['type'] = $source_type_value['type'];
                            $new_source['source_type_is_editable'] = $source_type_value['is_editable'];
                            break;
                        }
                    }

                    if ($source_type[0] == 'user') {
                        if (isset(getChannelPartners()[$source_type[1]]['short_name'])) {
                            $new_source['val_id'] = $source_value->source;
                            // $new_source['val_text'] = ChannelPartner::select('firm_name')->where('user_id', $source_value->source)->first()->firm_name;
                            $val_text = ChannelPartner::select('firm_name')
                                ->where('user_id', $source_value->source)
                                ->first();
                            if ($val_text) {
                                $new_source['val_text'] = $val_text->firm_name;
                            } else {
                                $new_source['val_text'] = ' ';
                            }
                        } else {
                            $new_source['val_id'] = $source_value->source;
                            // $new_source['val_text'] = User::select(DB::raw("CONCAT(users.first_name,' ',users.last_name) AS name"))->where('id', $source_value->source)->first()->name;
                            $val_text = User::select(DB::raw("CONCAT(users.first_name,' ',users.last_name) AS name"))
                                ->where('id', $source_value->source)
                                ->first();
                            if ($val_text) {
                                $new_source['val_text'] = $val_text->name;
                            } else {
                                $new_source['val_text'] = ' ';
                            }
                        }
                    } elseif ($source_type[0] == 'master') {
                        $new_source['val_id'] = $source_value->source;
                        // $new_source['val_text'] = CRMSettingSource::select('name')->where('source_type_id', $source_value->source)->first()->name;
                        $val_text = CRMSettingSource::select('name')
                            ->where('source_type_id', $source_value->source)
                            ->first();
                        if ($val_text) {
                            $new_source['val_text'] = $val_text->name;
                        } else {
                            $new_source['val_text'] = ' ';
                        }
                    } elseif ($source_type[0] == 'exhibition') {
                        $Exhibition_data = Exhibition::find($data['lead']['source']);
                        $new_source['val_id'] = $Exhibition_data->id;
                        $new_source['val_text'] = $Exhibition_data->name;
                    } else {
                        $new_source['val_id'] = $source_value->source;
                        $new_source['val_text'] = $source_value->source;
                    }
                    array_push($LeadSource_new, $new_source);
                }
            }
			$data['lead']['no_of_more_source'] = $LeadSource->count();
            $data['lead']['add_more_source'] = $LeadSource_new;

			$data['lead']['main_source_type'] = array();
			if ($data['lead']['source_type'] != 1) {
                $source_type_explode = explode("-", $data['lead']['source_type']);

                foreach (getLeadSourceTypes() as $key => $value) {
					$source_type_id = $source_type_explode[1];
                    if ($source_type_id == 201) {
                        $source_type_id = 202;
                    } else if ($source_type_id == 301) {
                        $source_type_id = 302;
                    }
                    if ($value['type'] == $source_type_explode[0] && $value['id'] == $source_type_id) {
						// $data['lead']['source_type_id'] = $value['type'].'-'.$value['id'];
                        // $data['lead']['source_type'] = $value['lable'];
						// $data['lead']['source_type_is_editable'] = $value['is_editable'];
						$data['lead']['main_source_type']['id'] = $value['type'].'-'.$value['id'];
						$data['lead']['main_source_type']['text'] = $value['lable'];
						$data['lead']['main_source_type']['type'] = $value['type'];
						$data['lead']['main_source_type']['is_editable'] = $value['is_editable'];
                    }
                }
            } else {
				$sourceTypeObject = array();
				$sourceTypeObject['id'] = "master-1";
				$sourceTypeObject['text'] = "Facebook";
				$sourceTypeObject['type'] = "master";
				$sourceTypeObject['is_editable'] = 0;
				$data['lead']['main_source_type'] = $sourceTypeObject;
            }

			
            $main_sourceid = 0;
            $main_sourcename = "";
			if(isset($data['lead']['main_source_type']['id']) && $data['lead']['main_source_type']['id'] != ''){
            	$main_source_type = explode("-", $data['lead']['main_source_type']['id']);
            	if ($main_source_type[0] == "user") {

                if (isset(getChannelPartners()[$main_source_type[1]]['short_name'])) {

                    $main_sourceid = $data['lead']['source'];
                    $val_text = ChannelPartner::select('firm_name')->where('user_id', $data['lead']['source'])->first();
                    if ($val_text) {
                        $main_sourcename = $val_text->firm_name;
                    } else {
                        $main_sourcename = " ";
                    }

                } else {

                    $main_sourceid = $data['lead']['source'];
                    $val_text = User::select(DB::raw("CONCAT(users.first_name,' ',users.last_name) AS name"))->where('id', $data['lead']['source'])->first();
                    if ($val_text) {
                        $main_sourcename = $val_text->name;
                    } else {
                        $main_sourcename = " ";
                    }
                }

            	} else if ($main_source_type[0] == "master") {

                $main_sourceid = $data['lead']['source'];
                $val_text = CRMSettingSource::select('name')->where('source_type_id', $data['lead']['source'])->first();
                if ($val_text) {
                    $main_sourcename = $val_text->name;
                } else {
                    $main_sourcename = " ";
                }

            	} else if($main_source_type[0] == "exhibition") {
            	    $Exhibition_data = Exhibition::find($data['lead']['source']);
            	    $main_sourceid = $Exhibition_data->id;
            	    $main_sourcename = $Exhibition_data->name;
            	} else {
            	    $main_sourceid = $data['lead']['source'];
            	    $main_sourcename = $data['lead']['source'];
            	}
			}
			$data['lead']['main_source_type']['val_id'] = $main_sourceid;
            $data['lead']['main_source_type']['val_text'] = $main_sourcename;

			$user_detail = User::select('users.id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"), 'users.phone_number', 'users.type');
			// $user_detail = $user_detail->where('users.status', 1);
			$user_detail = $user_detail->where('users.id', $data['lead']['source']);
			$user_detail = $user_detail->first();


			if ($user_detail) {
				$channel_partner = array('101', '102', '103', '104', '105');
				$source = array();
				if (in_array($user_detail->type, $channel_partner)) {
					$source = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text2"), 'users.phone_number', 'channel_partner.firm_name  AS text');
					$source->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
					$source->where('users.id', $user_detail->id);
					// $source->where('users.status', 1);
					$source = $source->first();
				} else {
					$source = $user_detail;
				}
				$source = json_encode($source);
				$source = json_decode($source, true);
				$data['lead']['source'] = $source;
			} else {
				$data['lead']['source'] = (object) array();
			}

			if ($data['lead']['closing_date_time'] != null) {

				$lead_closing_date_time = $data['lead']['closing_date_time'];
				$lead_closing_date_time = date('Y-m-d H:i:s', strtotime($lead_closing_date_time . " +5 hours"));
				$lead_closing_date_time = date('Y-m-d H:i:s', strtotime($lead_closing_date_time . " +30 minutes"));
				$data['lead']['closing_date_time'] = date('Y-m-d', strtotime($lead_closing_date_time));
			}

			$architect = User::select('users.id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"), 'users.phone_number');
			// $architect->where('users.status', 1);
			$architect->where('users.id', $data['lead']['architect']);
			// $architect->whereIn('users.type', ['201', '202']);
			$architect = $architect->first();
			if ($architect) {
				$architect = json_encode($architect);
				$architect = json_decode($architect, true);
				$data['lead']['architect'] = $architect;
			} else {
				$data['lead']['architect'] = (object) array();
			}



			$electrician = User::select('users.id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"), 'users.phone_number');
			// $electrician->where('users.status', 1);
			$electrician->where('users.id', $data['lead']['electrician']);
			// $electrician->whereIn('users.type', ['301', '302']);
			$electrician = $electrician->first();
			if ($electrician) {
				$electrician = json_encode($electrician);
				$electrician = json_decode($electrician, true);
				$data['lead']['electrician'] = $electrician;
			} else {
				$data['lead']['electrician'] = (object) array();
			}

			if(isset($data['lead']['channel_partner'])){
				$obj_channel_partner = User::select('users.id', "channel_partner.firm_name AS text", 'users.phone_number');
				$obj_channel_partner->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
				$obj_channel_partner->where('users.id', $data['lead']['channel_partner']);
				$obj_channel_partner = $obj_channel_partner->first();
				if ($obj_channel_partner) {
					$obj_channel_partner = json_encode($obj_channel_partner);
					$obj_channel_partner = json_decode($obj_channel_partner, true);
					$data['lead']['channel_partner'] = $obj_channel_partner;
				} else {
					$data['lead']['channel_partner'] = (object) array();
				}
			} else {
				$data['lead']['channel_partner'] = (object) array();
			}


			$user = User::select('users.id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"));
			// $user->where('users.status', 1);
			$user->where('users.id', $data['lead']['user_id']);
			$user = $user->first();
			if ($user) {
				$user = json_encode($user);
				$user = json_decode($user, true);
				$data['lead']['user_id'] = $user;
				$data['lead']['user'] = $user;
			} else {
				$data['lead']['user_id'] = (object) array();
				$data['lead']['user'] = (object) array();
			}


			$assigned_to = User::select('users.id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"));
			// $assigned_to->where('users.status', 1);
			$assigned_to->where('users.id', $data['lead']['assigned_to']);
			$assigned_to = $assigned_to->first();
			if ($assigned_to) {
				$assigned_to = json_encode($assigned_to);
				$assigned_to = json_decode($assigned_to, true);
				$data['lead']['assigned_to'] = $assigned_to;
			} else {
				$data['lead']['assigned_to'] = (object) array();
			}

			$created_by = User::select('users.id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"));
			// $created_by->where('users.status', 1);
			$created_by->where('users.id', $data['lead']['created_by']);
			$created_by = $created_by->first();
			if ($created_by) {
				$created_by = json_encode($created_by);
				$created_by = json_decode($created_by, true);
				$data['lead']['created_by'] = $created_by;
			} else {
				$data['lead']['created_by'] = (object) array();
			}

			$CityList = CityList::select('city_list.id as id', DB::raw('CONCAT(city_list.name, ", ", state_list.name) AS text'));
			$CityList->leftJoin('state_list', 'state_list.id', '=', 'city_list.state_id');
			$CityList->where('city_list.id', $data['lead']['city_id']);
			$CityList = $CityList->first();

			if ($CityList) {

				$CityList = json_encode($CityList);
				$CityList = json_decode($CityList, true);

				$CityList['text'] = $CityList['text'] . ", India";

				$data['lead']['city'] = $CityList;
			}

			$CityList = CityList::select('city_list.id as id', DB::raw('CONCAT(city_list.name, ", ", state_list.name) AS text'));
			$CityList->leftJoin('state_list', 'state_list.id', '=', 'city_list.state_id');
			$CityList->where('city_list.id', $data['lead']['meeting_city_id']);
			$CityList = $CityList->first();

			if ($CityList) {

				$CityList = json_encode($CityList);
				$CityList = json_decode($CityList, true);
				$CityList['text'] = $CityList['text'] . ", India";
				$data['lead']['meeting_city'] = $CityList;
			}

			if ($data['lead']['site_stage'] != 0) {

				$CRMSettingStageOfSite = CRMSettingStageOfSite::select('id', 'name as text');
				// $CRMSettingStageOfSite->where('crm_setting_stage_of_site.status', 1);
				$CRMSettingStageOfSite->where('crm_setting_stage_of_site.id', $data['lead']['site_stage']);
				$CRMSettingStageOfSite = $CRMSettingStageOfSite->first();
				if ($CRMSettingStageOfSite) {

					$data['lead']['site_stage_id'] = $data['lead']['site_stage'];
					$data['lead']['site_stage'] = $CRMSettingStageOfSite->text;
				}
			} else {
				$data['lead']['site_stage_id'] = 0;
				$data['lead']['site_stage'] = "";
			}

			if ($data['lead']['site_type'] != 0) {

				$CRMSettingSiteType = CRMSettingSiteType::select('id', 'name as text');
				// $CRMSettingStageOfSite->where('crm_setting_stage_of_site.status', 1);
				$CRMSettingSiteType->where('crm_setting_site_type.id', $data['lead']['site_type']);
				$CRMSettingSiteType = $CRMSettingSiteType->first();
				if ($CRMSettingSiteType) {

					$data['lead']['site_type_id'] = $data['lead']['site_type'];
					$data['lead']['site_type'] = $CRMSettingSiteType->text;
				}
			} else {
				$data['lead']['site_type_id'] = 0;
				$data['lead']['site_type'] = "";
			}

			if ($data['lead']['bhk'] != 0) {

				$CRMSettingBHK = CRMSettingBHK::select('id', 'name as text');
				// $CRMSettingStageOfSite->where('crm_setting_stage_of_site.status', 1);
				$CRMSettingBHK->where('crm_setting_bhk.id', $data['lead']['bhk']);
				$CRMSettingBHK = $CRMSettingBHK->first();
				if ($CRMSettingBHK) {

					$data['lead']['bhk_id'] = $data['lead']['bhk'];
					$data['lead']['bhk'] = $CRMSettingBHK->text;
				}
			} else {
				$data['lead']['bhk_id'] = 0;
				$data['lead']['bhk'] = "";
			}

			if ($data['lead']['want_to_cover'] != 0 && $data['lead']['want_to_cover'] != null && $data['lead']['want_to_cover'] != '') {

				$query_category = CRMSettingWantToCover::select('crm_setting_want_to_cover.id AS id', 'crm_setting_want_to_cover.name AS text');
				$query_category->whereIn('crm_setting_want_to_cover.id', explode(',', $data['lead']['want_to_cover']));
				$data['lead']['want_to_cover'] = $query_category->get();

			} else {
				$data['lead']['want_to_cover'] = [];
			}

			if ($data['lead']['competitor'] != 0 && $data['lead']['competitor'] != null && $data['lead']['competitor'] != '') {
				$query_category = CRMSettingCompetitors::select('crm_setting_competitors.id AS id', 'crm_setting_competitors.name AS text');
				$query_category->whereIn('crm_setting_competitors.id', explode(',', $data['lead']['competitor']));
				$data['lead']['competitor'] = $query_category->get();
			} else {
				$data['lead']['competitor'] = [];
			}

			if ($data['lead']['tag'] != 0 && $data['lead']['tag'] != null && $data['lead']['tag'] != '') {
				$query_category = TagMaster::select('tag_master.id AS id', 'tag_master.tagname AS text');
				$query_category->whereIn('tag_master.id', explode(',', $data['lead']['tag']));
				$data['lead']['tag'] = $query_category->get();
			} else {
				$data['lead']['tag'] = [];
			}

			// if ($data['lead']['competitor'] != 0) {

			// 	$CRMSettingCompetitors = CRMSettingCompetitors::select('id', 'name as text');
			// 	// $CRMSettingStageOfSite->where('crm_setting_stage_of_site.status', 1);
			// 	$CRMSettingCompetitors->where('crm_setting_competitors.id', $data['lead']['competitor']);
			// 	$CRMSettingCompetitors = $CRMSettingCompetitors->first();
			// 	if ($CRMSettingCompetitors) {

			// 		$data['lead']['competitor'] = $CRMSettingCompetitors->text;
			// 	}
			// } else {
			// 	$data['lead']['competitor'] = "";
			// }

			// LEAD NOTES DATA START
			$LeadUpdate = LeadUpdate::query();
			$LeadUpdate->select('lead_updates.id', 'lead_updates.message', 'lead_updates.task', 'lead_updates.task_title', 'lead_updates.user_id', 'users.first_name', 'users.last_name', 'lead_updates.created_at');
			$LeadUpdate->leftJoin('users', 'users.id', '=', 'lead_updates.user_id');
			$LeadUpdate->where('lead_updates.lead_id', $data['lead']['id']);
			$LeadUpdate->orderBy('lead_updates.id', 'desc');
			$LeadUpdate->limit(5);
			$LeadUpdate = $LeadUpdate->get();
			$LeadUpdate = json_encode($LeadUpdate);
			$LeadUpdate = json_decode($LeadUpdate, true);

			foreach ($LeadUpdate as $key => $value) {

				$user = User::select('users.id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"));
				// $user->where('users.status', 1);
				$user->where('users.id', $value['user_id']);
				$user = $user->first();
				if ($user) {
					$user = json_encode($user);
					$user = json_decode($user, true);
					$LeadUpdate[$key]['user'] = $user;
				} else {
					$LeadUpdate[$key]['user'] = (object) array();
				}

				$LeadUpdate[$key]['created_at'] = convertDateTime($value['created_at']);
				$LeadUpdate[$key]['date'] = convertDateAndTime($value['created_at'], "date");
				$LeadUpdate[$key]['time'] = convertDateAndTime($value['created_at'], "time");
			}
			// LEAD NOTES DATA END

			// LEAD CONTACT DATA START
			$LeadContact_List = LeadContact::query();
			$LeadContact_List->select('crm_setting_contact_tag.name as tag_name', 'lead_contacts.*');
			$LeadContact_List->leftJoin('crm_setting_contact_tag', 'crm_setting_contact_tag.id', '=', 'lead_contacts.contact_tag_id');
			$LeadContact_List->where('lead_contacts.lead_id', $data['lead']['id']);
			$LeadContact_List->orderBy('lead_contacts.id', 'desc');
			$LeadContact_List->limit(5);
			$LeadContact_List = $LeadContact_List->get();
			$LeadContact_List = json_encode($LeadContact_List);
			$LeadContact_List = json_decode($LeadContact_List, true);

			foreach ($LeadContact_List as $key => $value) {
				$Contact_tag = CRMSettingContactTag::select('crm_setting_contact_tag.id as id', 'crm_setting_contact_tag.name as text');
				$Contact_tag->where('id', $value['contact_tag_id']);
				$Contact_tag = $Contact_tag->first();

				if ($Contact_tag) {
					$Contact_tag = json_encode($Contact_tag);
					$Contact_tag = json_decode($Contact_tag, true);
					$LeadContact_List[$key]['contact_tag'] = $Contact_tag;
				} else {
					$LeadContact_List[$key]['contact_tag'] = (object) array();
				}
			}
			// LEAD CONTACT DATA END



			// LEAD FILES DATA START
			$LeadFile = LeadFile::query();
			$LeadFile->select('crm_setting_file_tag.name as tag_name', 'lead_files.*', 'users.first_name', 'users.last_name');
			$LeadFile->leftJoin('crm_setting_file_tag', 'crm_setting_file_tag.id', '=', 'lead_files.file_tag_id');
			$LeadFile->leftJoin('users', 'users.id', '=', 'lead_files.uploaded_by');
			$LeadFile->where('lead_files.lead_id', $data['lead']['id']);
			$LeadFile->limit(5);
			$LeadFile->orderBy('lead_files.id', 'desc');
			$LeadFile = $LeadFile->get();
			$LeadFile = json_encode($LeadFile);
			$LeadFile = json_decode($LeadFile, true);

			foreach ($LeadFile as $key => $value) {
				$name = explode("/", $value['name']);

				$LeadFile[$key]['name'] = end($name);
				$LeadFile[$key]['download'] = getSpaceFilePath($value['name']);
				$LeadFile[$key]['created_at'] = convertDateTime($value['created_at']);

				$File_tag = CRMSettingFileTag::select('id', 'name as text');
				$File_tag->where('id', $value['file_tag_id']);
				$File_tag = $File_tag->first();

				if ($File_tag) {
					$File_tag = json_encode($File_tag);
					$File_tag = json_decode($File_tag, true);
					$LeadFile[$key]['file_tag'] = $File_tag;
				} else {
					$LeadFile[$key]['file_tag'] = (object) array();
				}
			}
			// LEAD FILES DATA END

			// LEAD CALLS DATA START
			$LeadCall = LeadCall::query();
			$LeadCall->where('lead_calls.lead_id', $data['lead']['id']);
			$LeadCall->where('is_closed', 0);
			$LeadCall->orderBy('lead_calls.id', 'desc');
			$LeadCall = $LeadCall->get();
			$LeadCall = json_encode($LeadCall);
			$LeadCall = json_decode($LeadCall, true);

			foreach ($LeadCall as $key => $value) {
				$leadcall_user = User::select('users.id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"));
				// $leadcall_user->where('users.status', 1);
				$leadcall_user->where('users.id', $value['user_id']);
				$leadcall_user = $leadcall_user->first();
				if ($leadcall_user) {
					$leadcall_user = json_encode($leadcall_user);
					$leadcall_user = json_decode($leadcall_user, true);
					$LeadCall[$key]['user'] = $leadcall_user;
				} else {
					$LeadCall[$key]['user'] = (object) array();
				}


				$Call_type = CRMSettingCallType::select('id', 'name as text');
				$Call_type->where('crm_setting_call_type.status', 1);
				$Call_type->where('crm_setting_call_type.id', $value['type_id']);
				$Call_type = $Call_type->first();
				if ($Call_type) {
					$Call_type = json_encode($Call_type);
					$Call_type = json_decode($Call_type, true);
					$LeadCall[$key]['type'] = $Call_type;
				} else {
					$LeadCall[$key]['type'] = (object) array();
				}


				$Call_contact = LeadContact::select('lead_contacts.id', DB::raw("CONCAT(lead_contacts.first_name,' ',lead_contacts.last_name) AS text"));
				$Call_contact->where('lead_contacts.id', $value['contact_name']);
				$Call_contact = $Call_contact->first();
				if ($Call_contact) {
					$Call_contact = json_encode($Call_contact);
					$Call_contact = json_decode($Call_contact, true);
					$LeadCall[$key]['contact'] = $Call_contact;
				} else {
					$LeadCall[$key]['contact'] = (object) array();
				}

				$LeadCall[$key]['date'] = convertDateAndTime2($value['call_schedule'], "date");
				$LeadCall[$key]['time'] = convertDateAndTime2($value['call_schedule'], "time");
			}

			$LeadCallClosed = LeadCall::query();
			$LeadCallClosed->where('lead_calls.lead_id', $data['lead']['id']);
			$LeadCallClosed->where('is_closed', 1);
			$LeadCallClosed->orderBy('lead_calls.closed_date_time', 'desc');
			$LeadCallClosed = $LeadCallClosed->get();
			$LeadCallClosed = json_encode($LeadCallClosed);
			$LeadCallClosed = json_decode($LeadCallClosed, true);


			foreach ($LeadCallClosed as $key => $value) {
				$lead_close_call_user = User::select('users.id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"));
				// $lead_close_call_user->where('users.status', 1);
				$lead_close_call_user->where('users.id', $value['user_id']);
				$lead_close_call_user = $lead_close_call_user->first();
				if ($lead_close_call_user) {
					$lead_close_call_user = json_encode($lead_close_call_user);
					$lead_close_call_user = json_decode($lead_close_call_user, true);
					$LeadCallClosed[$key]['user'] = $lead_close_call_user;
				} else {
					$LeadCallClosed[$key]['user'] = (object) array();
				}

				$Close_call_type = CRMSettingCallType::select('id', 'name as text');
				$Close_call_type->where('crm_setting_call_type.status', 1);
				$Close_call_type->where('crm_setting_call_type.id', $value['type_id']);
				$Close_call_type = $Close_call_type->first();
				if ($Close_call_type) {
					$Close_call_type = json_encode($Close_call_type);
					$Close_call_type = json_decode($Close_call_type, true);
					$LeadCallClosed[$key]['type'] = $Close_call_type;
				} else {
					$LeadCallClosed[$key]['type'] = (object) array();
				}

				$Call_close_contact = LeadContact::select('lead_contacts.id', DB::raw("CONCAT(lead_contacts.first_name,' ',lead_contacts.last_name) AS text"));
				$Call_close_contact->where('lead_contacts.id', $value['contact_name']);
				$Call_close_contact = $Call_close_contact->first();
				if ($Call_close_contact) {
					$Call_close_contact = json_encode($Call_close_contact);
					$Call_close_contact = json_decode($Call_close_contact, true);
					$LeadCallClosed[$key]['contact'] = $Call_close_contact;
				} else {
					$LeadCallClosed[$key]['contact'] = (object) array();
				}


				$LeadCallClosed[$key]['date'] = convertDateAndTime2($value['closed_date_time'], "date");
				$LeadCallClosed[$key]['time'] = convertDateAndTime2($value['closed_date_time'], "time");
			}
			// LEAD CALLS DATA END


			// LEAD MEETING DATA START

			$LeadMeeting = LeadMeeting::query();
			$LeadMeeting->where('lead_meetings.lead_id', $data['lead']['id']);
			$LeadMeeting->where('is_closed', 0);
			$LeadMeeting->orderBy('lead_meetings.id', 'desc');
			$LeadMeeting = $LeadMeeting->get();


			foreach ($LeadMeeting as $key => $value) {

				// MEETING USER DETAIL START
				$lead_meeting_user = User::select('users.id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"));
				// $lead_meeting_user->where('users.status', 1);
				$lead_meeting_user->where('users.id', $value['user_id']);
				$lead_meeting_user = $lead_meeting_user->first();
				if ($lead_meeting_user) {
					$lead_meeting_user = json_encode($lead_meeting_user);
					$lead_meeting_user = json_decode($lead_meeting_user, true);
					$LeadMeeting[$key]['user'] = $lead_meeting_user;
				} else {
					$LeadMeeting[$key]['user'] = (object) array();
				}
				// MEETING USER DETAIL END

				// MEETING TITLE DETAIL START
				$Meeting_title = CRMSettingMeetingTitle::select('id', 'name as text');
				$Meeting_title->where('crm_setting_meeting_title.status', 1);
				$Meeting_title->where('crm_setting_meeting_title.id', $value['title_id']);
				$Meeting_title = $Meeting_title->first();
				if ($Meeting_title) {
					$Meeting_title = json_encode($Meeting_title);
					$Meeting_title = json_decode($Meeting_title, true);
					$LeadMeeting[$key]['title'] = $Meeting_title;
				} else {
					$LeadMeeting[$key]['title'] = (object) array();
				}
				// MEETING TITLE DETAIL END

				// MEETING DATE& TIME DETAIL START
				$LeadMeeting[$key]['date'] = convertDateAndTime2($value['meeting_date_time'], "date");
				$LeadMeeting[$key]['time'] = convertDateAndTime2($value['meeting_date_time'], "time");
				// MEETING DATE& TIME DETAIL END

				// MEETING DATE& TIME DETAIL START
				$LeadMeetingParticipant = LeadMeetingParticipant::where('meeting_id', $value['id'])->orderby('id', 'asc')->get();
				$LeadMeetingParticipant = json_encode($LeadMeetingParticipant);
				$LeadMeetingParticipant = json_decode($LeadMeetingParticipant, true);


				$LeadMeetingtype = CRMSettingMeetingType::select('id', 'name as text');
				$LeadMeetingtype->where('crm_setting_meeting_type.status', 1);
				$LeadMeetingtype->where('crm_setting_meeting_type.id', $value['type_id']);
				$LeadMeetingtype = $LeadMeetingtype->first();
				if ($LeadMeetingtype) {
					$LeadMeetingtype = json_encode($LeadMeetingtype);
					$LeadMeetingtype = json_decode($LeadMeetingtype, true);
					$LeadMeeting[$key]['type'] = $LeadMeetingtype;
				} else {
					$LeadMeeting[$key]['type'] = (object) array();
				}


				$UserResponse = array();

				foreach ($LeadMeetingParticipant as $users_key => $value) {

					if ($value['type'] == "users") {

						$User = User::select('users.id', 'users.type', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
						$User->where('users.id', $value['reference_id']);
						$User = $User->first();
						$new_UserResponse['id'] = "users-" . $User['id'];
						$new_UserResponse['text'] = getAllUserTypes()[$User['type']]['short_name'] . " - " . $User['full_name'];

					} else if ($value['type'] == "lead_contacts") {

						$LeadContact = LeadContact::select('lead_contacts.id', DB::raw("CONCAT(lead_contacts.first_name,' ',lead_contacts.last_name) AS full_name"));
						$LeadContact->where('lead_contacts.id', $value['reference_id']);
						$LeadContact = $LeadContact->first();

						$new_UserResponse['id'] = "users-" . $LeadContact['id'];
						$new_UserResponse['text'] = "Contact - " . $LeadContact['full_name'];
					}
					array_push($UserResponse, $new_UserResponse);
				}

				if ($UserResponse) {
					$UserResponse = json_encode($UserResponse);
					$UserResponse = json_decode($UserResponse, true);
					$LeadMeeting[$key]['participant'] = $UserResponse;
				} else {
					$LeadMeeting[$key]['participant'] = array();
				}
			}

			$LeadMeetingClosed = LeadMeeting::query();
			$LeadMeetingClosed->where('lead_meetings.lead_id', $data['lead']['id']);
			$LeadMeetingClosed->where('is_closed', 1);
			$LeadMeetingClosed->orderBy('lead_meetings.closed_date_time', 'desc');
			$LeadMeetingClosed = $LeadMeetingClosed->get();
			$LeadMeetingClosed = json_encode($LeadMeetingClosed);
			$LeadMeetingClosed = json_decode($LeadMeetingClosed, true);

			foreach ($LeadMeetingClosed as $key => $value) {
				$lead_close_meeting_user = User::select('users.id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"));
				// $lead_close_meeting_user->where('users.status', 1);
				$lead_close_meeting_user->where('users.id', $value['user_id']);
				$lead_close_meeting_user = $lead_close_meeting_user->first();
				if ($lead_close_meeting_user) {
					$lead_close_meeting_user = json_encode($lead_close_meeting_user);
					$lead_close_meeting_user = json_decode($lead_close_meeting_user, true);
					$LeadMeetingClosed[$key]['user'] = $lead_close_meeting_user;
				} else {
					$LeadMeetingClosed[$key]['user'] = (object) array();
				}

				$Meeting_close_title = CRMSettingMeetingTitle::select('id', 'name as text');
				$Meeting_close_title->where('crm_setting_meeting_title.status', 1);
				$Meeting_close_title->where('crm_setting_meeting_title.id', $value['title_id']);
				$Meeting_close_title = $Meeting_close_title->first();
				if ($Meeting_close_title) {
					$Meeting_close_title = json_encode($Meeting_close_title);
					$Meeting_close_title = json_decode($Meeting_close_title, true);
					$LeadMeetingClosed[$key]['title'] = $Meeting_close_title;
				} else {
					$LeadMeetingClosed[$key]['title'] = (object) array();
				}

				$LeadMeetingClosed[$key]['date'] = convertDateAndTime2($value['closed_date_time'], "date");
				$LeadMeetingClosed[$key]['time'] = convertDateAndTime2($value['closed_date_time'], "time");

				$LeadMeetingParticipant = LeadMeetingParticipant::where('meeting_id', $value['id'])->orderby('id', 'asc')->get();
				$LeadMeetingParticipant = json_encode($LeadMeetingParticipant);
				$LeadMeetingParticipant = json_decode($LeadMeetingParticipant, true);

				$Meeting_close_type = CRMSettingMeetingType::select('id', 'name as text');
				$Meeting_close_type->where('crm_setting_meeting_type.status', 1);
				$Meeting_close_type->where('crm_setting_meeting_type.id', $value['type_id']);
				$Meeting_close_type = $Meeting_close_type->first();
				if ($Meeting_close_type) {
					$Meeting_close_type = json_encode($Meeting_close_type);
					$Meeting_close_type = json_decode($Meeting_close_type, true);
					$LeadMeetingClosed[$key]['type'] = $Meeting_close_type;
				} else {
					$LeadMeetingClosed[$key]['type'] = (object) array();
					;
				}

				$UserResponse = array();

				foreach ($LeadMeetingParticipant as $users_key => $value) {

					if ($value['type'] == "users") {

						$User = User::select('users.id', 'users.type', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
						$User->where('users.id', $value['reference_id']);
						$User = $User->first();
						$new_UserResponse['id'] = "users-" . $User['id'];
						$new_UserResponse['text'] = getAllUserTypes()[$User['type']]['short_name'] . " - " . $User['full_name'];

					} else if ($value['type'] == "lead_contacts") {

						$LeadContact = LeadContact::select('lead_contacts.id', DB::raw("CONCAT(lead_contacts.first_name,' ',lead_contacts.last_name) AS full_name"));
						$LeadContact->where('lead_contacts.id', $value['reference_id']);
						$LeadContact = $LeadContact->first();
						if($LeadContact){
							$new_UserResponse['id'] = "users-" . $LeadContact['id'];
							$new_UserResponse['text'] = "Contact - " . $LeadContact['full_name'];
						} else {
							$new_UserResponse['id'] = "-";
							$new_UserResponse['text'] = "-";
						}
					}
					array_push($UserResponse, $new_UserResponse);
				}

				if ($UserResponse) {
					$UserResponse = json_encode($UserResponse);
					$UserResponse = json_decode($UserResponse, true);
					$LeadMeetingClosed[$key]['participant'] = $UserResponse;
				} else {
					$LeadMeetingClosed[$key]['participant'] = array();
				}
			}
			// LEAD MEETING DATA END

			// LEAD TASK DATA START
			$LeadTask = LeadTask::query();
			$LeadTask->where('lead_tasks.lead_id', $data['lead']['id']);
			$LeadTask->where('is_closed', 0);
			$LeadTask->orderBy('lead_tasks.id', 'desc');
			$LeadTask = $LeadTask->get();
			$LeadTask = json_encode($LeadTask);
			$LeadTask = json_decode($LeadTask, true);

			foreach ($LeadTask as $key => $value) {
				$lead_task_user = User::select('users.id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"));
				// $lead_task_user->where('users.status', 1);
				$lead_task_user->where('users.id', $value['user_id']);
				$lead_task_user = $lead_task_user->first();
				if ($lead_task_user) {
					$lead_task_user = json_encode($lead_task_user);
					$lead_task_user = json_decode($lead_task_user, true);
					$LeadTask[$key]['user'] = $lead_task_user;
				} else {
					$LeadTask[$key]['user'] = (object) array();
				}


				$Task_assign_to_person = User::select('users.id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"));
				// $Task_assign_to_person->where('users.status', 1);
				// $Task_assign_to_person->where('users.type', 2);
				$Task_assign_to_person->where('users.id', $value['assign_to']);
				$Task_assign_to_person = $Task_assign_to_person->first();
				if ($Task_assign_to_person) {
					$Task_assign_to_person = json_encode($Task_assign_to_person);
					$Task_assign_to_person = json_decode($Task_assign_to_person, true);
					$LeadTask[$key]['assign_to'] = $Task_assign_to_person;
				} else {
					$LeadTask[$key]['assign_to'] = (object) array();
				}

				$LeadTask[$key]['date'] = convertDateAndTime2($value['due_date_time'], "date");
				$LeadTask[$key]['time'] = convertDateAndTime2($value['due_date_time'], "time");
			}

			$LeadTaskClosed = LeadTask::query();
			$LeadTaskClosed->where('lead_tasks.lead_id', $data['lead']['id']);
			$LeadTaskClosed->where('is_closed', 1);
			$LeadTaskClosed->orderBy('lead_tasks.closed_date_time', 'desc');
			$LeadTaskClosed = $LeadTaskClosed->get();
			$LeadTaskClosed = json_encode($LeadTaskClosed);
			$LeadTaskClosed = json_decode($LeadTaskClosed, true);

			foreach ($LeadTaskClosed as $key => $value) {
				$lead_close_task_user = User::select('users.id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"));
				// $lead_close_task_user->where('users.status', 1);
				$lead_close_task_user->where('users.id', $value['user_id']);
				$lead_close_task_user = $lead_close_task_user->first();
				if ($lead_close_task_user) {
					$lead_close_task_user = json_encode($lead_close_task_user);
					$lead_close_task_user = json_decode($lead_close_task_user, true);
					$LeadTaskClosed[$key]['user'] = $lead_close_task_user;
				} else {
					$LeadTaskClosed[$key]['user'] = (object) array();
				}

				$Task_assign_to_person = User::select('users.id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"));
				// $Task_assign_to_person->where('users.status', 1);
				// $Task_assign_to_person->where('users.type', 2);
				$Task_assign_to_person->where('users.id', $value['assign_to']);
				$Task_assign_to_person = $Task_assign_to_person->first();
				if ($Task_assign_to_person) {
					$Task_assign_to_person = json_encode($Task_assign_to_person);
					$Task_assign_to_person = json_decode($Task_assign_to_person, true);
					$LeadTaskClosed[$key]['assign_to'] = $Task_assign_to_person;
				} else {
					$LeadTaskClosed[$key]['assign_to'] = (object) array();
				}

				$LeadTaskClosed[$key]['date'] = convertDateAndTime2($value['closed_date_time'], "date");
				$LeadTaskClosed[$key]['time'] = convertDateAndTime2($value['closed_date_time'], "time");
			}

			// LEAD TASK DATA END

			$countCall = count($LeadCall);
			$countTask = count($LeadTask);
			$countMeeting = count($LeadMeeting);

			$maxOpenAction = $countCall;
			if ($countCall < $countTask) {
				$maxOpenAction = $countTask;
			}

			if ($countCall < $countMeeting) {
				$maxOpenAction = $countMeeting;
			}

			$countCallClosed = count($LeadCallClosed);
			$countTaskClosed = count($LeadTaskClosed);
			$countMeetingClosed = count($LeadMeetingClosed);

			$maxClosedAction = $countCallClosed;
			if ($countCallClosed < $countTaskClosed) {
				$maxClosedAction = $countTaskClosed;
			}

			if ($countCallClosed < $countMeetingClosed) {
				$maxClosedAction = $countMeetingClosed;
			}

			$isQuotation = Wltrn_Quotation::query();
			$isQuotation->where('wltrn_quotation.inquiry_id', $request->id);
			$isQuotation->orderBy('wltrn_quotation.id', 'desc');
			$isQuotation = $isQuotation->first();
			if ($isQuotation) {
				$isQuotation = 1;
			} else {
				$isQuotation = 0;

			}

			$LeadQuotation = Wltrn_Quotation::query();
            $LeadQuotation->select(
                'id as quot_id', 
                'quotgroup_id as quot_groupid', 
                'quottype_id as quottype_id', 
                'quot_date', 
                'isfinal', 
                'quot_no_str as quot_version', 
                'quotation_file',
                'wltrn_quotation.quot_whitelion_amount as whitelion_amount',
                'wltrn_quotation.quot_other_amount as other_amount',
                'wltrn_quotation.quot_billing_amount as billing_amount',
                'wltrn_quotation.quot_total_amount as total_amount',
            );
            $LeadQuotation->where('wltrn_quotation.inquiry_id', $request->id);
            $LeadQuotation->where('wltrn_quotation.status', 3);
            $LeadQuotation->where('wltrn_quotation.isfinal', 1);
            $LeadQuotation = $LeadQuotation->get();
            $LeadQuotation = json_decode(json_encode($LeadQuotation), true);

			$quotation = array();
			
            foreach ($LeadQuotation as $key => $value) {
                $quotation_details = $value;
                $quotation_details['whitelion_amount'] = numCommaFormat($value['whitelion_amount']);
                $quotation_details['other_amount'] = numCommaFormat($value['other_amount']);
                $quotation_details['billing_amount'] = numCommaFormat($value['billing_amount']);
                $quotation_details['total_amount'] = numCommaFormat($value['total_amount']);
                $quotation = $quotation_details;
            }

			if(!$quotation){
				$quotation_details['whitelion_amount'] = 0;
                $quotation_details['other_amount'] = 0;
                $quotation_details['billing_amount'] = 0;
                $quotation_details['total_amount'] = 0;
				$quotation = $quotation_details;
			}
			

			$response = successRes("Get List");
			$data['contacts'] = $LeadContact_List;
			$data['updates'] = $LeadUpdate;
			$data['files'] = $LeadFile;

			$data['calls'] = $LeadCall;
			$data['tasks'] = $LeadTask;
			$data['meetings'] = $LeadMeeting;

			$data['max_open_actions'] = $maxOpenAction;

			$data['calls_closed'] = $LeadCallClosed;
			$data['tasks_closed'] = $LeadTaskClosed;
			$data['meetings_closed'] = $LeadMeetingClosed;

			$data['max_close_actions'] = $maxClosedAction;
			$data['is_quotation'] = $isQuotation;
			$data['quotation_details'] = $quotation;
			$LeadTimeline = LeadTimeline::where('lead_id', $data['lead']['id'])->orderBy('id', 'desc')->get();

			$LeadTimeline = json_encode($LeadTimeline);
			$LeadTimeline = json_decode($LeadTimeline, true);

			$repeated_date = '';
			foreach ($LeadTimeline as $key => $value) {
				$date = convertDateAndTime($value['created_at'], "date");
				if ($repeated_date == $date) {
					$LeadTimeline[$key]['date'] = 0;
				} else {
					$repeated_date = $date;
					$LeadTimeline[$key]['date'] = convertDateAndTime($value['created_at'], "date");
				}

				$lead_close_task_user = User::select('users.id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"));
				$lead_close_task_user->where('users.status', 1);
				$lead_close_task_user->where('users.id', $value['user_id']);
				$lead_close_task_user = $lead_close_task_user->first();
				if ($lead_close_task_user) {
					$lead_close_task_user = json_encode($lead_close_task_user);
					$lead_close_task_user = json_decode($lead_close_task_user, true);
					$LeadTimeline[$key]['user'] = $lead_close_task_user;
				} else {
					$LeadTimeline[$key]['user'] = (object) array();
				}

				$LeadTimeline[$key]['time'] = convertDateAndTime($value['created_at'], "time");
				$LeadTimeline[$key]['created_at'] = convertDateTime($value['created_at']);
			}

			$data['timeline'] = $LeadTimeline;

			// $response['view'] = view('crm/lead/detail', compact('data'))->render();
			$response = successRes();
			$response['data'] = $data;
		} else {
			$response = errorRes("Something went wrong");
		}
		// } catch (QueryException $ex) {
		// 	$response = errorRes($ex->getMessage());
		// }
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	function saveEditDetail(Request $request)
	{

		$rules = array();
		$rules['lead_id'] = 'required';

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			$response = errorRes($validator->errors()->first());
			$response['data'] = $validator->errors();
		} elseif (isReception() == 1) {
            $response = errorRes("You Don't Have An Access");
        }
		else {

			$lead_closing_date_and_time = $request->lead_closing_date_time;
			$new_closing_date = date('Y-m-d', strtotime($lead_closing_date_and_time));
			if ($lead_closing_date_and_time != '' || $lead_closing_date_and_time != null) {
				$lead_closing_date_time = $lead_closing_date_and_time . " 23:59:59";
				$lead_closing_date_time = date('Y-m-d H:i:s', strtotime($lead_closing_date_time . " -5 hours"));
				$lead_closing_date_time = date('Y-m-d H:i:s', strtotime($lead_closing_date_time . " -30 minutes"));
			} else {
				$lead_closing_date_time = $lead_closing_date_and_time;
			}


			$Lead = Lead::find($request->lead_id);

			if ($Lead) {
				$old_closing_date = date('Y-m-d', strtotime($Lead->closing_date_time));

				$change_field = "";
				if ($lead_closing_date_and_time != '' || $lead_closing_date_and_time != null) {
					$Lead->closing_date_time = $lead_closing_date_time;
					if ($old_closing_date != $new_closing_date) {
						$change_field .= " | Closing Date Update : " . $old_closing_date . " To " . $new_closing_date;
						try {
							$LeadClosing = new LeadClosing();
							$LeadClosing->lead_id = $Lead->id;
							$LeadClosing->closing_date = $lead_closing_date_time;
							$LeadClosing->entryby = Auth::user()->id;
							$LeadClosing->entryip = $request->ip();
							$LeadClosing->save();
						} catch (\Exception $e) {
							$response_error['error_closingdate'] = errorRes($e->getMessage(), 400);
						}
					}
				}

				if ($request->lead_site_stage != '' || $request->lead_site_stage != null) {
					$Lead->site_stage = $request->lead_site_stage;
					if ($Lead->site_stage != $request->lead_site_stage) {
						$new_value = $request->lead_site_stage;
						$old_value = $Lead->site_stage;

						$New_Site_Stage = '';
						if ($new_value != 0 || $new_value != '') {

							$CRMSettingStageOfSite = CRMSettingStageOfSite::select('id', 'name as text');
							$CRMSettingStageOfSite->where('crm_setting_stage_of_site.id', $new_value);
							$CRMSettingStageOfSite = $CRMSettingStageOfSite->first();
							if ($CRMSettingStageOfSite) {
								$New_Site_Stage = $CRMSettingStageOfSite->text;
							}
						}
						$Old_Site_Stage = '';
						if ($old_value != 0 || $old_value != '') {
							$CRMSettingStageOfSite = CRMSettingStageOfSite::select('id', 'name as text');
							$CRMSettingStageOfSite->where('crm_setting_stage_of_site.id', $old_value);
							$CRMSettingStageOfSite = $CRMSettingStageOfSite->first();
							if ($CRMSettingStageOfSite) {
								$Old_Site_Stage = $CRMSettingStageOfSite->text;
							}
						}
						$change_field .= " | Site Stage Change : " . $Old_Site_Stage . " To " . $New_Site_Stage;
					}
				}

				if (isset($request->lead_tag)) {
                    $Tag_id = $request->lead_tag;
                    if ($Lead->tag != $Tag_id) {
                        $new_value = $Tag_id;
                        $old_value = $Lead->tag;

                        $New_Tag = '';
                        if ($new_value != 0 && $new_value != '' && $new_value != null) {
                            $Tag = TagMaster::select('id', 'tagname as text');
                            $Tag->whereIn('tag_master.id', explode(",", $new_value));
                            $Tag = $Tag->get();
                            if ($Tag) {
                                foreach ($Tag as $key => $value) {
                                    $New_Tag .= $value['text'];
                                    $New_Tag .= ', ';
                                }
                            }
                        }

                        $Old_Tag = '';
                        if ($old_value != 0 && $old_value != '' && $old_value != null) {
                            $Tag = TagMaster::select('id', 'tagname as text');
                            $Tag->whereIn('tag_master.id', explode(',', $old_value));
                            $Tag = $Tag->get();
                            if ($Tag) {
                                foreach ($Tag as $key => $value) {
                                    $Old_Tag .= $value['text'];
                                    $Old_Tag .= ', ';
                                }
                            }
                        }
                        $change_field .= " | Tag Change : " . $Old_Tag . " To " . $New_Tag;
                        $Lead->tag = $Tag_id;
                    }
                }

				$Lead->save();
				$statusupdate = saveLeadAndDealStatusInAction($Lead->id, $request->lead_status, $request->ip(),$request->app_source);

				if ($change_field != '') {
					$timeline = array();
					$timeline['lead_id'] = $Lead->id;
					$timeline['type'] = "lead-update";
					$timeline['reffrance_id'] = $Lead->id;
					$timeline['description'] = "Lead Detail Updated " . $change_field;
					$timeline['source'] = $request->app_source;
					saveLeadTimeline($timeline);
				}

				$response = successRes("Succssfully Update Detail");
				$response['data'] = $statusupdate;
			} else {
				$response = errorRes("Something went wrong");
			}
		}
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	function checkPhoneNumber(Request $request)
	{
		$rules = array();
		$rules['lead_phone_number'] = 'required|digits:10|regex:/^[1-9][0-9]*$/';

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			$response = errorRes($validator->errors()->first());
			$response['data'] = $validator->errors();
		} else {

			$isAlreadyLead = Lead::select('id', 'assigned_to')->where('phone_number', $request->lead_phone_number)->first();
			if ($isAlreadyLead) {
				$User = User::select('first_name', 'last_name')->find($isAlreadyLead->assigned_to);
				if ($User) {
					$response = errorRes("Lead already registed with phone number, #" . $isAlreadyLead->id . " assigned to " . $User->first_name . " " . $User->last_name, 200);
					$response['data']['is_valid'] = false;
				} else {
					$response = errorRes("Lead already registed with phone number", 200);
					$response['data']['is_valid'] = false;
				}
				$response['status'] = 1;
			} else {
				$response = successRes("Lead phone number is valid", 200);
				$response['status'] = 0;

				$response['data']['is_valid'] = true;
			}
		}
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	function searchTimeSlot(Request $request)
    {

        $searchKeyword = isset($request->q) ? $request->q : "";

        $TimeSlot = getTimeSlot();

        $finalArray = array();
        foreach ($TimeSlot as $key => $value) {
			$countFinal = count($finalArray);
			$finalArray[$countFinal] = array();
			$finalArray[$countFinal] = array();
            $finalArray[$countFinal]['id'] = $value;
            $finalArray[$countFinal]['text'] = $value;
        }

		$response = successRes("Success");
        $response['data'] = $finalArray;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

	function searchReminderTimeSlot(Request $request)
    {

        $searchKeyword = isset($request->q) ? $request->q : "";

        $ReminderTimeSlot = getReminderTimeSlot();

        $finalArray = array();
        foreach ($ReminderTimeSlot as $key => $value) {
			$countFinal = count($finalArray);
			$finalArray[$countFinal] = array();
			$finalArray[$countFinal] = array();
            $finalArray[$countFinal]['id'] = $value['id'];
            $finalArray[$countFinal]['text'] = $value['name'];
        }
		
		$response = successRes("Success");
        $response['data'] = $finalArray;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

	public function getLeadAmountSummary(Request $request)
    {

        $isSalePerson = isSalePerson();
        $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
        $source_type = getInquirySourceTypes();
		$isReception = isReception();
        if ($isSalePerson == 1) {
            $parentSalesUsers = getParentSalePersonsIds(Auth::user()->id);
            $childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
        }

        $selectColumns = array(
            'leads.id',
            'leads.inquiry_id',
            'leads.first_name',
            'leads.last_name',
            'leads.phone_number',
            'leads.status',
            'leads.is_deal',
            'created.first_name as lead_owner_name',
        );

        $arr_where_clause = array();
        $arr_or_clause = array();

        if ($request->is_advance_filter == 1) {
            foreach (json_decode($request->advance_filter_data, true) as $key => $filt_value) {

                $filter_value = '';
                $source_type = '0';
                if ($filt_value['clause'] == null || $filt_value['clause'] == '') {
                    $response = errorRes("Please Select Clause");
                    return response()->json($response)->header('Content-Type', 'application/json');


                } else if ($filt_value['column'] == null || $filt_value['column'] == '') {
                    $response = errorRes("Please Select column");
                    return response()->json($response)->header('Content-Type', 'application/json');


                } else if ($filt_value['condtion'] == null || $filt_value['condtion'] == '') {
                    $response = errorRes("Please Select condtion");
                    return response()->json($response)->header('Content-Type', 'application/json');

                } else {
                    $column = getFilterColumnCRM()[$filt_value['column']];
                    $condtion = getFilterCondtionCRM()[$filt_value['condtion']];
                    if ($column['value_type'] == 'text') {

                        if ($filt_value['value_text'] == null || $filt_value['value_text'] == '') {
                            $response = errorRes("Please enter value");
                            return response()->json($response)->header('Content-Type', 'application/json');
                        } else {
                            $filter_value = $filt_value['value_text'];
                        }

                    } else if (($column['value_type'] == 'select') && ($condtion['value_type'] == 'single_select')) {
                        if ($column['code'] == "leads_source" && ($filt_value['value_source_type'] == null || $filt_value['value_source_type'] == '')) {
                            $response = errorRes("Please select value");
                            return response()->json($response)->header('Content-Type', 'application/json');
                        } else {
                            $source_type = $filt_value['value_source_type'];
                        }
                        if ($filt_value['value_select'] == null || $filt_value['value_select'] == '') {
                            $response = errorRes("Please select value");
                            return response()->json($response)->header('Content-Type', 'application/json');
                        } else {
                            $filter_value = $filt_value['value_select'];
                        }

                    } else if (($column['value_type'] == 'select') && ($condtion['value_type'] == 'multi_select')) {
                        if ($column['code'] == "leads_source" && ($filt_value['value_source_type'] == null || $filt_value['value_source_type'] == '')) {
                            $response = errorRes("Please select value");
                            return response()->json($response)->header('Content-Type', 'application/json');
                        } else {
                            $source_type = $filt_value['value_source_type'];
                        }
                        if (!isset($filt_value['value_multi_select']) && empty($filt_value['value_multi_select'])) {
                            $response = errorRes("Please select value");
                            return response()->json($response)->header('Content-Type', 'application/json');
                        } else {
                            $filter_value = $filt_value['value_multi_select'];
                        }

                    } else if (($column['value_type'] == 'date') && ($condtion['value_type'] == 'single_select')) {

                        if ($filt_value['value_date'] == null || $filt_value['value_date'] == '') {
                            $response = errorRes("Please enter date");
                            return response()->json($response)->header('Content-Type', 'application/json');
                        } else {
                            $filter_value = $filt_value['value_date'];
                        }

                    } else if (($column['value_type'] == 'date') && ($condtion['value_type'] == 'between')) {

                        if (($filt_value['value_from_date'] == null || $filt_value['value_from_date'] == '') && ($filt_value['value_to_date'] == null || $filt_value['value_to_date'] == '')) {
                            $response = errorRes("Please enter from to date");
                            return response()->json($response)->header('Content-Type', 'application/json');
                        } else {
                            $filter_value = $filt_value['value_from_date'] . "," . $filt_value['value_to_date'];
                        }
                    }

                    if ($filt_value['clause'] != 0) {
                        $clause = getFilterClauseCRM()[$filt_value['clause']];
                    }


                    if ($filt_value['clause'] == 0) {
                        $newdata['clause'] = 0;
                        $newdata['column'] = $column['id'];
                        $newdata['condtion'] = $condtion['id'];
                        $newdata['value'] = $filter_value;
                        $newdata['source_type'] = $source_type;


                        array_push($arr_where_clause, $newdata);

                    } else if ($clause['clause'] == 'where') {
                        $newdata['clause'] = $clause['id'];
                        $newdata['column'] = $column['id'];
                        $newdata['condtion'] = $condtion['id'];
                        $newdata['value'] = $filter_value;
                        $newdata['source_type'] = $source_type;

                        array_push($arr_where_clause, $newdata);

                    } else if ($clause['clause'] == 'orwhere') {
                        $newdata['clause'] = $clause['id'];
                        $newdata['column'] = $column['id'];
                        $newdata['condtion'] = $condtion['id'];
                        $newdata['value'] = $filter_value;
                        $newdata['source_type'] = $source_type;

                        array_push($arr_or_clause, $newdata);
                    }

                }

            }
        }


        $Lead = Lead::query();
        $Lead->leftJoin('users as lead_owner', 'lead_owner.id', '=', 'leads.assigned_to');
        $Lead->leftJoin('users as created', 'created.id', '=', 'leads.created_by');
		$Lead->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
		$Lead->leftJoin('lead_files', 'lead_files.lead_id', '=', 'leads.id');
        $Lead->select($selectColumns);

        if ($request->is_deal == 0) {
            $Lead->where('leads.is_deal', 0);
        } else if ($request->is_deal == 1) {
            $Lead->where('leads.is_deal', 1);
        }

        if ($isSalePerson == 1) {
            $Lead->whereIn('assigned_to', $childSalePersonsIds);
        }

		if(isChannelPartner(Auth::user()->type) != 0){
			
			$Lead->where('lead_sources.source', Auth::user()->id);
		}

		if (isArchitect() == 1) {
            $Lead->where(function ($query) {
                $query->orwhere('leads.architect', Auth::user()->id);
                $query->orwhere('lead_sources.source', Auth::user()->id);
            });
        }
        if ($isReception == 1) {
            $Lead->where('leads.created_by', Auth::user()->id);
        }

        if (isElectrician() == 1) {
            $Lead->where(function ($query) {
                $query->orwhere('leads.electrician', Auth::user()->id);
                $query->orwhere('lead_sources.source', Auth::user()->id);
            });
        }

        if (isset($request->status)) {
            if ($request->status != 0) {
                $Lead->where('leads.status', $request->status);
            }
        }

        $Lead->orderBy('leads.id', 'DESC');


        if ($request->is_advance_filter == 1) {

			
            foreach ($arr_where_clause as $wherekey => $objwhere) {

                $Column = getFilterColumnCRM()[$objwhere['column']];
                $Condtion = getFilterCondtionCRM()[$objwhere['condtion']];
                $lstDateFilter = getDateFilterValue();
                $Filter_Value = $objwhere['value'];
                $source_type = $objwhere['source_type'];

                if ($Condtion['code'] == 'is') {
                    if ($Column['value_type'] == 'leads_source') {
                        $Lead->whereIn('lead_sources.source', $source_type);

                    } else if ($Column['value_type'] == 'date') {
                        // $date_filter_value = date('Y-m-d', strtotime($Filter_Value));
                        // $Lead->whereDate($Column['column_name'], '=', $date_filter_value);
                        $objDateFilter = $lstDateFilter[$Filter_Value];

                        $currentStartDate = date('Y-m-d', strtotime(date('Y-m-d')));
                        $currentEndDate = date('Y-m-d', strtotime(date('Y-m-d')));

                        if ($objDateFilter['code'] == "all_closing") {

                            $Lead->where($Column['column_name'], '!=', null);

                        } else if ($objDateFilter['code'] == "in_this_week") {

                            $currentWeekDay = date('w', strtotime($currentStartDate));
                            $weekStartDate = date('Y-m-d', strtotime($currentStartDate . " -" . ($currentWeekDay - 1) . " days"));
                            $weekEndDate = date('Y-m-d', strtotime($currentEndDate . " +" . ((7 - $currentWeekDay)) . " days"));
                            $Lead->whereDate($Column['column_name'], '<=', $weekEndDate);

                        } else if ($objDateFilter['code'] == "in_this_month") {

                            $monthStartDay = date('Y-m-01', strtotime($currentStartDate));
                            $monthEndDay = date('Y-m-t', strtotime($currentEndDate));
                            $Lead->whereDate($Column['column_name'], '<=', $monthEndDay);

                        } else if ($objDateFilter['code'] == "in_next_month") {
                            $currentStartDate = date('Y-m-d', strtotime($currentStartDate . " +5 hours"));
                            $currentStartDate = date('Y-m-d', strtotime($currentStartDate . " +30 minutes"));
                            $monthStartDay = date('Y-m-01', strtotime($currentStartDate . " + 1 month"));
                            $monthEndDay = date('Y-m-t', strtotime($monthStartDay));
                            $Lead->whereDate($Column['column_name'], '<=', $monthEndDay);

                        } else if ($objDateFilter['code'] == "in_next_two_month") {

                            $monthStartDay = date('Y-m-01', strtotime($currentStartDate . " + 1 month"));
                            $monthEndDay = date('Y-m-t', strtotime($currentEndDate . " + 2 month"));
                            $Lead->whereDate($Column['column_name'], '<=', $monthEndDay);

                        } else if ($objDateFilter['code'] == "in_next_three_month") {

                            $monthStartDay = date('Y-m-01', strtotime($currentStartDate . " + 1 month"));
                            $monthEndDay = date('Y-m-t', strtotime($currentEndDate . " + 3 month"));
                            $Lead->whereDate($Column['column_name'], '>=', date('Y-m-d'));
                            $Lead->whereDate($Column['column_name'], '<=', $monthEndDay);
                        }
                        // if ($objDateFilter['code'] == "today") {
                        //     $date_filter_value = date('Y-m-d', strtotime(date('Y-m-d')));
                        //     $Lead->whereDate($Column['column_name'], '=', $date_filter_value);
                        //     // $Lead->whereDate($Column['column_name'], '=', date('Y-m-d'));

                        // } 
                        else {

                            $date_filter_value = explode(",", $objDateFilter['value']);
                            $from_date_filter = $date_filter_value[0];
                            $from_date_filter_value = date('Y-m-d', strtotime($from_date_filter));

                            $to_date_filter = $date_filter_value[1];
                            $to_date_filter_value = date('Y-m-d', strtotime($to_date_filter));

                            $Lead->whereBetween(DB::raw('DATE(' . $Column["column_name"] . ')'), [$from_date_filter_value, $to_date_filter_value]);
                        }

                    } else if ($Column['value_type'] == 'select') {
						if($Column['code'] == "lead_miss_data"){
                            $missDataValue = getLeadFilterMissFilterValue()[$Filter_Value];
                            $Lead->where($missDataValue['column_name'], $missDataValue['value']);                         
                        }else{
                            $Lead->where($Column['column_name'], $Filter_Value);
                        }

                    } else {
                        $Lead->where($Column['column_name'], 'like', "%" . $Filter_Value . "%");
                    }

                } elseif ($Condtion['code'] == 'is_not') {
                    if ($Column['value_type'] == 'leads_source') {
                        $Lead->whereNotIn('lead_sources.source', $source_type);

                    } else if ($Column['value_type'] == 'date') {

                        $objDateFilter = $lstDateFilter[$Filter_Value];

                        $currentStartDate = date('Y-m-d', strtotime(date('Y-m-d')));
                        $currentEndDate = date('Y-m-d', strtotime(date('Y-m-d')));

                        if ($objDateFilter['code'] == "all_closing") {

                            $Lead->where($Column['column_name'], '!=', null);

                        } else if ($objDateFilter['code'] == "in_this_week") {

                            $currentWeekDay = date('w', strtotime($currentStartDate));
                            $weekStartDate = date('Y-m-d', strtotime($currentStartDate . " -" . ($currentWeekDay - 1) . " days"));
                            $weekEndDate = date('Y-m-d', strtotime($currentEndDate . " +" . ((7 - $currentWeekDay)) . " days"));
                            $Lead->whereDate($Column['column_name'], '<=', $weekEndDate);

                        } else if ($objDateFilter['code'] == "in_this_month") {

                            $monthStartDay = date('Y-m-01', strtotime($currentStartDate));
                            $monthEndDay = date('Y-m-t', strtotime($currentEndDate));
                            $Lead->whereDate($Column['column_name'], '<=', $monthEndDay);

                        } else if ($objDateFilter['code'] == "in_next_month") {
                            $currentStartDate = date('Y-m-d', strtotime($currentStartDate . " +5 hours"));
                            $currentStartDate = date('Y-m-d', strtotime($currentStartDate . " +30 minutes"));
                            $monthStartDay = date('Y-m-01', strtotime($currentStartDate . " + 1 month"));
                            $monthEndDay = date('Y-m-t', strtotime($monthStartDay));
                            $Lead->whereDate($Column['column_name'], '<=', $monthEndDay);

                        } else if ($objDateFilter['code'] == "in_next_two_month") {

                            $monthStartDay = date('Y-m-01', strtotime($currentStartDate . " + 1 month"));
                            $monthEndDay = date('Y-m-t', strtotime($currentEndDate . " + 2 month"));
                            $Lead->whereDate($Column['column_name'], '<=', $monthEndDay);

                        } else if ($objDateFilter['code'] == "in_next_three_month") {

                            $monthStartDay = date('Y-m-01', strtotime($currentStartDate . " + 1 month"));
                            $monthEndDay = date('Y-m-t', strtotime($currentEndDate . " + 3 month"));
                            $Lead->whereDate($Column['column_name'], '>=', date('Y-m-d'));
                            $Lead->whereDate($Column['column_name'], '<=', $monthEndDay);
                        }

                        // if ($objDateFilter['code'] == "today") {
                        //     $date_filter_value = date('Y-m-d', strtotime(date('Y-m-d')));
                        //     $Lead->whereDate($Column['column_name'], '!=', $date_filter_value);
                        //     // $Lead->whereDate($Column['column_name'], '!=', date('Y-m-d'));

                        // } 
                        else {

                            $date_filter_value = explode(",", $objDateFilter['value']);
                            $from_date_filter = $date_filter_value[0];
                            $from_date_filter_value = date('Y-m-d', strtotime($from_date_filter));

                            $to_date_filter = $date_filter_value[1];
                            $to_date_filter_value = date('Y-m-d', strtotime($to_date_filter));

                            $Lead->whereNotBetween(DB::raw('DATE(' . $Column["column_name"] . ')'), [$from_date_filter_value, $to_date_filter_value]);
                        }
                    } else if ($Column['value_type'] == 'select') {
						if($Column['code'] == "lead_miss_data"){
                            $missDataValue = getLeadFilterMissFilterValue()[$Filter_Value];
                            $Lead->where($missDataValue['column_name'], '<>',$missDataValue['value']);                         
                        }else{
                            $Lead->whereNotNull($Column['column_name']);
                            $Lead->where($Column['column_name'], '!=', $Filter_Value);
                        }

                    } else {
                        $Lead->where($Column['column_name'], 'not like', "%" . $Filter_Value . "%");
                    }

                } else if ($Condtion['code'] == "contains") {
                    if ($Column['value_type'] == 'leads_source') {
                        $Lead->whereIn('lead_sources.source', $source_type);
                    }
                    if ($Column['value_type'] == 'select') {
                        if($Column['code'] == "lead_miss_data"){
                            foreach ($Filter_Value as $mis_key => $mis_value) {
                                $missDataValue = getLeadFilterMissFilterValue()[$mis_value];
                                $Lead->where($missDataValue['column_name'],$missDataValue['value']);
                            }
                        }else{
                            $Lead->whereIn($Column['column_name'], $Filter_Value);
                        }
                    } else {
                        $Filter_Value = explode(",", $Filter_Value);
                        $Lead->whereIn($Column['column_name'], $Filter_Value);
                    }

                } else if ($Condtion['code'] == "not_contains") {
                    if ($Column['value_type'] == 'leads_source') {
                        $Lead->whereNotIn('lead_sources.source', $source_type);
                    }
                    if ($Column['value_type'] == 'select') {
                        if($Column['code'] == "lead_miss_data"){
                            foreach ($Filter_Value as $mis_key => $mis_value) {
                                $missDataValue = getLeadFilterMissFilterValue()[$mis_value];
                                $Lead->where($missDataValue['column_name'],'<>',$missDataValue['value']);
                            }
                        }else{
                            $Lead->whereNotIn($Column['column_name'], $Filter_Value);
                        }
                    } else {

                        $Filter_Value = explode(",", $Filter_Value);
                        $Lead->whereNotIn($Column['column_name'], $Filter_Value);
                    }

                } else if ($Condtion['code'] == "between") {

                    if ($Column['value_type'] == 'date') {

                        $date_filter_value = explode(",", $Filter_Value);
                        $from_date_filter = $date_filter_value[0];
                        $from_date_filter_value = date('Y-m-d', strtotime($from_date_filter));

                        $to_date_filter = $date_filter_value[1];
                        $to_date_filter_value = date('Y-m-d', strtotime($to_date_filter));

                        $Lead->whereBetween(DB::raw('DATE(' . $Column["column_name"] . ')'), [$from_date_filter_value, $to_date_filter_value]);
                    }
                }

            }

            if (count($arr_or_clause) > 0) {
                $Lead->orWhere(function ($query) use ($arr_or_clause) {
                    foreach ($arr_or_clause as $orkey => $objor) {

                        $Column = getFilterColumnCRM()[$objor['column']];
                        $Condtion = getFilterCondtionCRM()[$objor['condtion']];
                        $lstDateFilter = getDateFilterValue();
                        $Filter_Value = $objor['value'];
                        $source_type = $objor['source_type'];

                        if ($Condtion['code'] == 'is') {
                            if ($Column['value_type'] == 'leads_source') {
                                $query->whereIn('lead_sources.source', $source_type);
                            } else if ($Column['value_type'] == 'date') {
                                // $date_filter_value = date('Y-m-d', strtotime($Filter_Value));
                                // $query->orWhereDate($Column['column_name'], '=', $date_filter_value);

                                $objDateFilter = $lstDateFilter[$Filter_Value];

                                $currentStartDate = date('Y-m-d', strtotime(date('Y-m-d')));
                                $currentEndDate = date('Y-m-d', strtotime(date('Y-m-d')));

                                if ($objDateFilter['code'] == "all_closing") {

                                    $query->orwhere($Column['column_name'], '!=', null);

                                } else if ($objDateFilter['code'] == "in_this_week") {

                                    $currentWeekDay = date('w', strtotime($currentStartDate));
                                    $weekStartDate = date('Y-m-d', strtotime($currentStartDate . " -" . ($currentWeekDay - 1) . " days"));
                                    $weekEndDate = date('Y-m-d', strtotime($currentEndDate . " +" . ((7 - $currentWeekDay)) . " days"));
                                    $query->orWhereDate($Column['column_name'], '<=', $weekEndDate);

                                } else if ($objDateFilter['code'] == "in_this_month") {

                                    $monthStartDay = date('Y-m-01', strtotime($currentStartDate));
                                    $monthEndDay = date('Y-m-t', strtotime($currentEndDate));
                                    $query->orWhereDate($Column['column_name'], '<=', $monthEndDay);

                                } else if ($objDateFilter['code'] == "in_next_month") {
                                    $currentStartDate = date('Y-m-d', strtotime($currentStartDate . " +5 hours"));
                                    $currentStartDate = date('Y-m-d', strtotime($currentStartDate . " +30 minutes"));
                                    $monthStartDay = date('Y-m-01', strtotime($currentStartDate . " + 1 month"));
                                    $monthEndDay = date('Y-m-t', strtotime($monthStartDay));
                                    $query->orWhereDate($Column['column_name'], '<=', $monthEndDay);

                                } else if ($objDateFilter['code'] == "in_next_two_month") {

                                    $monthStartDay = date('Y-m-01', strtotime($currentStartDate . " + 1 month"));
                                    $monthEndDay = date('Y-m-t', strtotime($currentEndDate . " + 2 month"));
                                    $query->orWhereDate($Column['column_name'], '<=', $monthEndDay);

                                } else if ($objDateFilter['code'] == "in_next_three_month") {

                                    $monthStartDay = date('Y-m-01', strtotime($currentStartDate . " + 1 month"));
                                    $monthEndDay = date('Y-m-t', strtotime($currentEndDate . " + 3 month"));
                                    $query->orWhereDate($Column['column_name'], '>=', date('Y-m-d'));
                                    $query->orWhereDate($Column['column_name'], '<=', $monthEndDay);
                                } else {
                                    $date_filter_value = explode(",", $objDateFilter['value']);
                                    $from_date_filter = $date_filter_value[0];
                                    $from_date_filter_value = date('Y-m-d', strtotime($from_date_filter));

                                    $to_date_filter = $date_filter_value[1];
                                    $to_date_filter_value = date('Y-m-d', strtotime($to_date_filter));

                                    $query->whereBetween(DB::raw('DATE(' . $Column["column_name"] . ')'), [$from_date_filter_value, $to_date_filter_value]);
                                }

                            } else if ($Column['value_type'] == 'select') {
								if($Column['code'] == "lead_miss_data"){
                                    $missDataValue = getLeadFilterMissFilterValue()[$Filter_Value];
                                    $query->orWhere($missDataValue['column_name'],$missDataValue['value']);                         
                                }else{
                                    $query->orWhere($Column['column_name'], $Filter_Value);
                                }

                            } else {
                                $query->orWhere($Column['column_name'], 'like', "%" . $Filter_Value . "%");
                            }

                        } elseif ($Condtion['code'] == 'is_not') {
                            if ($Column['value_type'] == 'leads_source') {
                                $query->whereIn('lead_sources.source', $source_type);
                            } else if ($Column['value_type'] == 'date') {
                                // $query->orWhereDate($Column['column_name'], '!=', $date_filter_value);

                                $objDateFilter = $lstDateFilter[$Filter_Value];

                                $currentStartDate = date('Y-m-d', strtotime(date('Y-m-d')));
                                $currentEndDate = date('Y-m-d', strtotime(date('Y-m-d')));

                                if ($objDateFilter['code'] == "all_closing") {

                                    $query->orwhere($Column['column_name'], '!=', null);

                                } else if ($objDateFilter['code'] == "in_this_week") {

                                    $currentWeekDay = date('w', strtotime($currentStartDate));
                                    $weekStartDate = date('Y-m-d', strtotime($currentStartDate . " -" . ($currentWeekDay - 1) . " days"));
                                    $weekEndDate = date('Y-m-d', strtotime($currentEndDate . " +" . ((7 - $currentWeekDay)) . " days"));
                                    $query->orWhereDate($Column['column_name'], '<=', $weekEndDate);

                                } else if ($objDateFilter['code'] == "in_this_month") {

                                    $monthStartDay = date('Y-m-01', strtotime($currentStartDate));
                                    $monthEndDay = date('Y-m-t', strtotime($currentEndDate));
                                    $query->orWhereDate($Column['column_name'], '<=', $monthEndDay);

                                } else if ($objDateFilter['code'] == "in_next_month") {
                                    $currentStartDate = date('Y-m-d', strtotime($currentStartDate . " +5 hours"));
                                    $currentStartDate = date('Y-m-d', strtotime($currentStartDate . " +30 minutes"));
                                    $monthStartDay = date('Y-m-01', strtotime($currentStartDate . " + 1 month"));
                                    $monthEndDay = date('Y-m-t', strtotime($monthStartDay));
                                    $query->orWhereDate($Column['column_name'], '<=', $monthEndDay);

                                } else if ($objDateFilter['code'] == "in_next_two_month") {

                                    $monthStartDay = date('Y-m-01', strtotime($currentStartDate . " + 1 month"));
                                    $monthEndDay = date('Y-m-t', strtotime($currentEndDate . " + 2 month"));
                                    $query->orWhereDate($Column['column_name'], '<=', $monthEndDay);

                                } else if ($objDateFilter['code'] == "in_next_three_month") {

                                    $monthStartDay = date('Y-m-01', strtotime($currentStartDate . " + 1 month"));
                                    $monthEndDay = date('Y-m-t', strtotime($currentEndDate . " + 3 month"));
                                    $query->orWhereDate($Column['column_name'], '>=', date('Y-m-d'));
                                    $query->orWhereDate($Column['column_name'], '<=', $monthEndDay);
                                } else {
                                    $date_filter_value = explode(",", $objDateFilter['value']);
                                    $from_date_filter = $date_filter_value[0];
                                    $from_date_filter_value = date('Y-m-d', strtotime($from_date_filter));

                                    $to_date_filter = $date_filter_value[1];
                                    $to_date_filter_value = date('Y-m-d', strtotime($to_date_filter));

                                    $query->whereNotBetween(DB::raw('DATE(' . $Column["column_name"] . ')'), [$from_date_filter_value, $to_date_filter_value]);
                                }

                            } else if ($Column['value_type'] == 'select') {
								if($Column['code'] == "lead_miss_data"){
                                    $missDataValue = getLeadFilterMissFilterValue()[$Filter_Value];
                                    $query->orWhere($missDataValue['column_name'],'<>',$missDataValue['value']);                         
                                }else{
                                    $query->orWhere($Column['column_name'], '!=', $Filter_Value);
                                }

                            } else {
                                $query->orWhere($Column['column_name'], 'not like', "%" . $Filter_Value . "%");
                            }

                        } else if ($Condtion['code'] == "contains") {
                            if ($Column['value_type'] == 'leads_source') {
                                $query->whereIn('lead_sources.source', $source_type);
                            }
                            if ($Column['value_type'] == 'select') {
                                if ($Column['code'] == 'leads_source') {
                                } else if($Column['code'] == "lead_miss_data"){
                                    foreach ($Filter_Value as $mis_key => $mis_value) {
                                        $missDataValue = getLeadFilterMissFilterValue()[$mis_value];
                                        $query->orWhere($missDataValue['column_name'],$missDataValue['value']);
                                    }
                                }else{
                                    $query->orWhere($Column['column_name'], $Filter_Value);
                                }
                            } else {
                                $Filter_Value = explode(",", $Filter_Value);
                                $query->orWhereIn($Column['column_name'], $Filter_Value);
                            }

                        } else if ($Condtion['code'] == "not_contains") {
                            if ($Column['value_type'] == 'leads_source') {
                                $query->whereIn('lead_sources.source', $source_type);
                            }
                            if ($Column['value_type'] == 'select') {
                                if($Column['code'] == "lead_miss_data"){
                                    foreach ($Filter_Value as $mis_key => $mis_value) {
                                        $missDataValue = getLeadFilterMissFilterValue()[$mis_value];
                                        $query->orWhere($missDataValue['column_name'],'<>',$missDataValue['value']);
                                    }
                                }else{
                                    $query->orWhereNotIn($Column['column_name'], $Filter_Value);
                                }
                            } else {
                                $Filter_Value = explode(",", $Filter_Value);
                                $query->orWhereNotIn($Column['column_name'], $Filter_Value);
                            }


                        } else if ($Condtion['code'] == "between") {

                            if ($Column['value_type'] == 'date') {

                                $date_filter_value = explode(",", $Filter_Value);
                                $from_date_filter = $date_filter_value[0];
                                $from_date_filter_value = date('Y-m-d', strtotime($from_date_filter));

                                $to_date_filter = $date_filter_value[1];
                                $to_date_filter_value = date('Y-m-d', strtotime($to_date_filter));

                                $query->orWhereBetween(DB::raw('DATE(' . $Column["column_name"] . ')'), [$from_date_filter_value, $to_date_filter_value]);
                            }
                        }
                    }
                });
            }

        }

        $Lead_filtered_ids = $Lead->distinct()->pluck('leads.id');

        $total_billing_amount = 0;
        $total_whitelion_amount = 0;
        $total_other_amount = 0;
        $total_amount = 0;

		$Leadamount = Lead::query();
        $Leadamount->selectRaw('SUM(wltrn_quotation.quot_whitelion_amount) AS whitelion_amount');
        $Leadamount->selectRaw('SUM(wltrn_quotation.quot_billing_amount) AS billing_amount');
        $Leadamount->selectRaw('SUM(wltrn_quotation.quot_other_amount) AS other_amount');
        $Leadamount->selectRaw('SUM(wltrn_quotation.quot_total_amount) AS total_amount');
        $Leadamount->leftJoin('wltrn_quotation', 'wltrn_quotation.inquiry_id', '=', 'leads.id');
        $Leadamount->whereIn('leads.id', $Lead_filtered_ids);
        $Leadamount->where('wltrn_quotation.isfinal', 1);
        $Leadamount = $Leadamount->first();

		if($Leadamount){
            $total_whitelion_amount = $Leadamount->whitelion_amount;
            $total_billing_amount = $Leadamount->billing_amount;
            $total_other_amount = $Leadamount->other_amount;
            $total_amount = $Leadamount->total_amount;
        }

        $response = successRes("Succss");
        $response['data']['whitelion_amt'] = numCommaFormat($total_whitelion_amount);
        $response['data']['billing_amt'] = numCommaFormat($total_billing_amount);
        $response['data']['other_amt'] = numCommaFormat($total_other_amount);
        $response['data']['total_amt'] = numCommaFormat($total_amount);

        return $response;

    }

	function searchLeadAndDealTag(Request $request)
    {
        $searchKeyword = isset($request->q) ? $request->q : "";

        $data = TagMaster::select('id', 'tagname as text');
        $data->where('tag_master.isactive', 1);
        $data->where('tag_master.tag_type', 201);
        $data->where('tag_master.tagname', 'like', "%" . $searchKeyword . "%");
        $data->limit(5);
        $data = $data->get();
        $response = array();
		$response = successRes();
		$response['data'] = $data;
		return response()->json($response)->header('Content-Type', 'application/json');
    }

	function saveLeadQuotation(Request $request){

		$rules = array();
		$rules['lead_id'] = 'required';
		$rules['lead_quotation_file'] = 'required';
		$rules['lead_quotation_amount'] = 'required';

		$customMessage = array();
		$customMessage['lead_id.required'] = "Invalid parameters";

		$validator = Validator::make($request->all(), $rules, $customMessage);

		if ($validator->fails()) {

			$response = errorRes("The request could not be understood by the server due to malformed syntax");
			$response['data'] = $validator->errors();
		} else {

			$uploadedFile1 = array();
			$fileSize = 0;
			$print_count = 0;
			if ($request->hasFile('lead_quotation_file')) {
				
				$folderPathofFile = '/s/lead-files/';
                if (!is_dir(public_path($folderPathofFile))) {
                    mkdir(public_path($folderPathofFile));
                }
                $folderPathofFile = '/s/lead-files/' . date('Y');

                if (!is_dir(public_path($folderPathofFile))) {
                    mkdir(public_path($folderPathofFile));
                }

                $folderPathofFile = '/s/lead-files/' . date('Y') . "/" . date('m');
                if (!is_dir(public_path($folderPathofFile))) {
                    mkdir(public_path($folderPathofFile));
                }

				foreach ($request->file('lead_quotation_file') as $key => $value) {

					$fileObject1 = $value;
					$extension = $fileObject1->extension();

					$fileName1 = time() . mt_rand(10000, 99999) . '.' . $extension;

					$destinationPath = public_path($folderPathofFile);
					$fileObject1->move($destinationPath, $fileName1);

					if (File::exists($folderPathofFile. "/" . $fileName1)) {
						$fileName1 = "";
					} 
					else {

						$fileName1 = $folderPathofFile. "/" . $fileName1;

						$spaceUploadResponse = uploadFileOnSpaces(public_path($fileName1), $fileName1);
						if ($spaceUploadResponse != 1) {
							$fileName1 = "";
						} else {

							$uploadedFile1[] = $fileName1;
							// array_push($uploadedFile1,$fileName1);
							unlink(public_path($fileName1));
						}
					}
				}
			}

			if (count($uploadedFile1) > 0) {
				
				$isQuotation = Wltrn_Quotation::query();
            	$isQuotation->where('wltrn_quotation.inquiry_id', $request->lead_id);
            	$isQuotation->orderBy('wltrn_quotation.id', 'desc');
            	$isQuotation = $isQuotation->first();
            	$QuotMaster = new Wltrn_Quotation();
            	if ($isQuotation) {
            	    $new_quot_no_str = Wltrn_Quotation::selectRaw('max(wltrn_quotation.quot_no_str + 1) as newversion')
            	        ->where('quotgroup_id', $isQuotation->quotgroup_id)
            	        ->first();
            	    $QuotMaster->quotgroup_id = $isQuotation->quotgroup_id;
            	    $QuotMaster->yy = substr(date('Y'), -2);
            	    $QuotMaster->mm = date('m');
            	    $QuotMaster->quotno = $isQuotation->quotno;
            	    [$major, $minor] = explode('.', $new_quot_no_str->newversion);
            	    $QuotMaster->quot_no_str = $major . '.01';
            	    $QuotMaster->quot_date = date('Y-m-d');
            	} else {
            	    $QuotMaster->quotgroup_id = Wltrn_Quotation::max('quotgroup_id') + 1;
            	    $QuotMaster->yy = substr(date('Y'), -2);
            	    $QuotMaster->mm = date('m');
            	    $QuotMaster->quotno = Wltrn_Quotation::max('quotno') + 1;
            	    $QuotMaster->quot_no_str = '1.01';
            	    $QuotMaster->quot_date = date('Y-m-d');
            	}

				$QuotMaster->quottype_id = 4;
				$QuotMaster->quotation_file = implode(",", $uploadedFile1);
				$QuotMaster->print_count = $print_count;
				$QuotMaster->status = 3;
				$QuotMaster->inquiry_id = $request->lead_id;
				$QuotMaster->net_amount = $request->lead_quotation_amount;
				$QuotMaster->quot_total_amount = $request->lead_quotation_amount;
				$QuotMaster->entryby = Auth::user()->id; //Live
				$QuotMaster->entryip = $request->ip();
				$QuotMaster->save();

				if($QuotMaster){

					// Wltrn_Quotation::where('quotgroup_id', $QuotMaster->quotgroup_id)->update(['isfinal' => 0]);
					Wltrn_Quotation::where('inquiry_id', $QuotMaster->inquiry_id)->update(['isfinal' => 0]);
                	$QuotMaster->isfinal = 1;
					$QuotMaster->save();

					$timeline = array();
                    $timeline['lead_id'] = $QuotMaster->inquiry_id;
                    $timeline['type'] = "lead-update";
                    $timeline['reffrance_id'] = $QuotMaster->inquiry_id;
                    $timeline['description'] = "Add Manual Quotation In Lead #" . $QuotMaster->inquiry_id . " Amount is : ".$QuotMaster->net_amount;
                    $timeline['source'] = $request->app_source;
                    saveLeadTimeline($timeline);

					$Lead_quotation_count = Wltrn_Quotation::where('inquiry_id', $request->lead_id)->count();
					$LeadFile = new LeadFile();
					$LeadFile->uploaded_by = Auth::user()->id;
					$LeadFile->file_size = $fileSize;
					$LeadFile->name = $QuotMaster->quotation_file;
					$LeadFile->lead_id = $request->lead_id;
					$LeadFile->file_tag_id = 2;
					$LeadFile->save();
					if($Lead_quotation_count == 1){
						
						$Lead = Lead::find($request->lead_id);
						if ($Lead->is_deal == 0) {

							$Lead->is_deal = 1;
							$Lead->status = 101;
							$Lead->save();
							if($Lead){
								$account_user_id = $this->accountCreate($Lead,strval($request->ip()),Auth::user()->id,$request->app_source);
								$Lead->account_user_id = $account_user_id;
								$Lead->save();
							}
							
							$timeline = array();
							$timeline['lead_id'] = $Lead->id;
							$timeline['type'] = "convert-to-deal";
							$timeline['reffrance_id'] = $LeadFile->id;
							$timeline['description'] = "Quatation upload - convert to deal";
							$timeline['source'] = $request->app_source;
							saveLeadTimeline($timeline);
						}

						
					} else {

					}

					$response = successRes("Successfully saved lead quotation");
					$response['id'] = $request->lead_id;
					$response['count'] = $Lead_quotation_count;
				}
			} else {
				$response = errorRes("Hello !");
			}
		}

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	function accountCreate($lead)
	{

		$AllUserTypes = getAllUserTypes();

		$alreadyEmail = User::query();
		$alreadyEmail->where('email', $lead->email);
		$alreadyEmail = $alreadyEmail->first();

		$alreadyPhoneNumber = User::query();
		$alreadyPhoneNumber->where('phone_number', $lead->phone_number);
		$alreadyPhoneNumber = $alreadyPhoneNumber->first();

		$Account_id = 0;
		if ($alreadyEmail) {
			$alreadyEmail->no_of_deal = $alreadyEmail->no_of_deal + 1;
			$alreadyEmail->save();
			
			$Account_id = $alreadyEmail->id;
		}elseif ($alreadyPhoneNumber){
			$alreadyPhoneNumber->no_of_deal = $alreadyPhoneNumber->no_of_deal + 1;
			$alreadyPhoneNumber->save();
			
			$Account_id = $alreadyPhoneNumber->id;
		}else{

			$CityList = CityList::find($lead->city_id);
	
			$User = new User();
			$User->created_by = Auth::user()->id;
			$User->password = Hash::make("111111");
			$User->last_active_date_time = date('Y-m-d H:i:s');
			$User->last_login_date_time = date('Y-m-d H:i:s');
			$User->avatar = "default.png";
			$User->type = 10000;
			$User->company_id = 1;
			$User->reference_type = getCustomers()[$User->type]['lable'];
			$User->reference_id = 0;
			$User->first_name = $lead->first_name;
			$User->last_name = $lead->last_name;
			$User->email = $lead->email;
			$User->dialing_code = "+91";
			$User->phone_number = $lead->phone_number;
			$User->ctc = 0;
			$User->address_line1 = $lead->addressline1;
			$User->address_line2 == $lead->addressline2;
			$User->pincode = $lead->pincode;
			$User->country_id = 1;
			$User->state_id = $CityList->state_id;
			$User->city_id = $lead->city_id;
			$User->status = 1;
			$User->no_of_deal = 1;
			$User->save();
	
			
			$debugLog = array();
			$debugLog['name'] = "user-add";
			$debugLog['description'] = "user #" . $User->id . "(" . $User->first_name . " " . $User->last_name . ") has been added ";
			saveDebugLog($debugLog);
			
			$Account_id = $User->id;
		}
		$this->moveAccountContacts($lead,$Account_id);
		
		return $Account_id;
	}

	function moveAccountContacts($lead,$Account_id)
	{
		$LeadContact = LeadContact::where('lead_id', $lead->id)->orderBy('id', 'asc')->get();

		foreach ($LeadContact as $key => $value) {
			// $alreadyPhoneNumber = LeadAccountContact::query();
			// $alreadyPhoneNumber->where('phone_number', $value->phone_number);
			// $alreadyPhoneNumber = $alreadyPhoneNumber->first();

			// if ($alreadyPhoneNumber) {
				
			// }else{

				$LeadAccountContact = new LeadAccountContact();
				$LeadAccountContact->user_id = $Account_id;
				$LeadAccountContact->contact_tag_id = $value->contact_tag_id;
				$LeadAccountContact->first_name = $value->first_name;
				$LeadAccountContact->last_name = $value->last_name;
				$LeadAccountContact->phone_number = $value->phone_number;
				$LeadAccountContact->alernate_phone_number = $value->alernate_phone_number;
				$LeadAccountContact->email = $value->email;
				$LeadAccountContact->type = $value->type;
				$LeadAccountContact->type_detail = $value->type_detail;

				$LeadAccountContact->save();
			// }
		}
	}

	function saveLeadFiles(Request $request){

		$rules = array();
		$rules['lead_id'] = 'required';
		$rules['lead_file'] = 'required';
		$rules['lead_file_tag'] = 'required';

		$customMessage = array();
		$customMessage['lead_id.required'] = "Please Enter Lead Id";
		$customMessage['lead_file.required'] = "Please Select Files";
		$customMessage['lead_file_tag.required'] = "PLease Select File Tag";

		$validator = Validator::make($request->all(), $rules, $customMessage);

		if ($validator->fails()) {
			$response = errorRes($validator->errors()->first());
			$response['data'] = $validator->errors();
		} else {

			$uploadedFile1 = array();
			$fileSize = 0;
			$lst_fileSize = array();
			$print_count = 0;
			if ($request->hasFile('lead_file')) {
				
				$folderPathofFile = '/s/lead-files/';
                if (!is_dir(public_path($folderPathofFile))) {
                    mkdir(public_path($folderPathofFile));
                }
                $folderPathofFile = '/s/lead-files/' . date('Y');

                if (!is_dir(public_path($folderPathofFile))) {
                    mkdir(public_path($folderPathofFile));
                }

                $folderPathofFile = '/s/lead-files/' . date('Y') . "/" . date('m');
                if (!is_dir(public_path($folderPathofFile))) {
                    mkdir(public_path($folderPathofFile));
                }

				foreach ($request->file('lead_file') as $key => $value) {

					$fileObject1 = $value;
					$extension = $fileObject1->extension();

					$fileName1 = time() . mt_rand(10000, 99999) . '.' . $extension;

					$destinationPath = public_path($folderPathofFile);
					$fileObject1->move($destinationPath, $fileName1);

					if (File::exists($folderPathofFile. "/" . $fileName1)) {
						$fileName1 = "";
					} 
					else {

						$fileName1 = $folderPathofFile. "/" . $fileName1;
						$fileSize = filesize(public_path($fileName1));
						$lst_fileSize[] = filesize(public_path($fileName1));
						$spaceUploadResponse = uploadFileOnSpaces(public_path($fileName1), $fileName1);
						if ($spaceUploadResponse != 1) {
							$fileName1 = "";
						} else {

							$uploadedFile1[] = $fileName1;
							// array_push($uploadedFile1,$fileName1);
							unlink(public_path($fileName1));
						}
					}
				}
			}

			if (count($uploadedFile1) > 0) {
				
				$File_Ids = array();
                foreach ($uploadedFile1 as $key => $value) {

                    $LeadFile = new LeadFile();
                    $LeadFile->uploaded_by = Auth::user()->id;
                    $LeadFile->file_size = $lst_fileSize[$key];
                    $LeadFile->name = $value;
                    $LeadFile->lead_id = $request->lead_id;
                    $LeadFile->file_tag_id = $request->lead_file_tag;
                    $LeadFile->save();

                
    
                    if ($LeadFile) {

                        array_push($File_Ids, $LeadFile->id);

                        $tag = CRMSettingFileTag::find($LeadFile->file_tag_id)['name'];
    
                        $timeline = array();
                        $timeline['lead_id'] = $LeadFile->lead_id;
                        $timeline['type'] = "file-upload";
                        $timeline['reffrance_id'] = $LeadFile->lead_id;
                        $timeline['description'] = "" . $tag . " Upload";
                        $timeline['source'] = $request->app_source;
                        saveLeadTimeline($timeline);
                    }
                }

                if($LeadFile->file_tag_id == 3){
                    $current_date = date('Y-m-d H:i:s');
                    $Plus_three_day = date('Y-m-d H:i:s');
                    $lead = Lead::find($LeadFile->lead_id);

                    // START COMPANY ADMIN TASK ASSIGN
                    $LeadTask = new LeadTask();
                    $LeadTask->lead_id = $lead->id;
                    $LeadTask->user_id = 5867;
                    $LeadTask->assign_to = 5867;
                    $LeadTask->task = "Verified Uploaded Bill In " . $LeadFile->lead_id . "-" . $lead->first_name . " " . $lead->last_name . "";
                    $LeadTask->due_date_time = $Plus_three_day;
                    $LeadTask->reminder = getReminderTimeSlot($Plus_three_day)[1]['datetime'];
                    $LeadTask->reminder_id = 1;
                    $LeadTask->description = 'Auto Generated Task';
                    $LeadTask->is_notification = 1;
                    $LeadTask->is_autogenerate = 1;
                    $LeadTask->save();
                    // END COMPANY ADMIN TASK ASSIGN

                    if($LeadTask){
                                
                        $Lead = Lead::find($LeadTask->lead_id);
                        $Lead->companyadmin_verification = 1;
                        $Lead->save();

                        DB::table('lead_files')->whereIn('lead_files.id', $File_Ids)->update(['reference_id' => $LeadTask->id,'reference_type' => 'Task']);
                    }
                }
                $response = successRes("Successfully saved lead file");
                $response['id'] = $LeadFile->lead_id;
			} else {
				$response = errorRes("Hello !");
			}
		}

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	function searchFileTag(Request $request)
    {
		$rules = array();
		$rules['lead_id'] = 'required';

		$customMessage = array();
		$customMessage['lead_id.required'] = "Please Enter Lead Id";

		$validator = Validator::make($request->all(), $rules, $customMessage);

		if ($validator->fails()) {
			$response = errorRes($validator->errors()->first());
			$response['data'] = $validator->errors();
		} else {
			
			$data = CRMSettingFileTag::select('id', 'name as text');
        	$data->where('crm_setting_file_tag.status', 1);
        	$data->where('id','!=', 2);
			if(isset($request->q)){
				$searchKeyword = $request->q;
				$data->where('crm_setting_file_tag.name', 'like', "%" . $searchKeyword . "%");
			}
        	$data->limit(5);
        	$data = $data->get();

			$resData = array();
			$lead = Lead::select('status')->where('id',$request->lead_id)->first();

			$isPrimeUser = LeadContact::query();
        	$isPrimeUser->where('lead_id', $request->lead_id);
        	$isPrimeUser->where('status', 1);
        	$isPrimeUser->where(function ($query) {
				$query->where('type', 202)->orWhere('type', 302);
			});
			$isPrimeUser = $isPrimeUser->count();
			
			$isBillCount = LeadFile::query();
        	$isBillCount->where('lead_id', $request->lead_id);
        	$isBillCount->where('is_active', 1);
        	$isBillCount->where('file_tag_id', 3);
			$isBillCount = $isBillCount->count();

        	foreach ($data as $key => $value) {
        	    if(isAdminOrCompanyAdmin() == 1){
					if($lead->status == 103 && $isPrimeUser >= 1)
        	        {
        	            $newdata['id'] = $value['id'];
        	            $newdata['text'] = $value['text'];
						array_push($resData,$newdata);
        	        } else {
						if($value['id'] != 3){
							$newdata['id'] = $value['id'];
        	                $newdata['text'] = $value['text'];
							array_push($resData,$newdata);
        	            }
        	        }
        	    } else{
					if($lead->status == 103 && $isPrimeUser >= 1 && $isBillCount == 0)
        	        {
						$newdata['id'] = $value['id'];
        	            $newdata['text'] = $value['text'];                    
						array_push($resData,$newdata);
        	        } else {
						if($value['id'] != 3){
							$newdata['id'] = $value['id'];
        	                $newdata['text'] = $value['text'];
							array_push($resData,$newdata);
        	            }
        	        } 
        	    }
        	}
			$response = successRes();
			$response['data'] = $resData;
		}
		return response()->json($response)->header('Content-Type', 'application/json');
    }

	function searchChannelpartner(Request $request)
	{
		try {
			$isArchitect = isArchitect();
			$isSalePerson = isSalePerson();
			$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
			$isThirdPartyUser = isThirdPartyUser();
			$isChannelPartner = isChannelPartner(Auth::user()->type);

			$searchKeyword = $request->q;

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
			$data = User::select('users.id', 'channel_partner.firm_name  AS text', 'users.phone_number');
			$data->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
			$data->whereIn('users.status', [1,2,3,4,5]);
			$data->whereIn('users.type', [101,102,103,104,105]);
			if ($isSalePerson == 1) {
				$data->where(function ($query) use ($cities, $childSalePersonsIds) {
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
			if(isset($request->q)){
				$data->where(function ($query) use ($searchKeyword) {
					$query->where('channel_partner.firm_name', 'like', '%' . $searchKeyword . '%');
				});
			}
			$data->limit(15);
			$data = $data->get();
			
			$response = successRes();
			$response['data'] = $data;


		} catch (QueryException $ex) {
			$response = errorRes();
		}
		return response()->json($response)->header('Content-Type', 'application/json');
	}
}