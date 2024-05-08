<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use App\Models\CityList;
use App\Models\CountryList;
use App\Models\DataMaster;
use App\Models\MainMaster;
use App\Models\LeadTimeline;
use App\Models\LeadSource;
use App\Models\BannerMaster;
use App\Models\StateList;
use App\Models\User;
use App\Models\Lead;
use App\Models\LeadContact;
use App\Models\LeadFile;
use App\Models\Electrician;
use App\Models\LeadUpdate;
use App\Models\CRMLog;
use App\Models\LeadQuestionAnswer;
use App\Models\TagMaster;
use App\Models\LeadTask;
use App\Models\Wltrn_Quotation;
use App\Models\Wltrn_QuotItemdetail;
use App\Models\ChannelPartner;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\LeadQuestionOptions;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Facebook\Facebook;
use Facebook\Exceptions\FacebookSDKException;

class GeneralController extends Controller
{
    public function getChannelPartnerTypes()
    {
        $data = getChannelPartners();
        $data = array_values($data);
        foreach ($data as $key => $value) {
            unset($data[$key]['lable']);
            unset($data[$key]['key']);
            unset($data[$key]['url']);
            unset($data[$key]['url_view']);
            unset($data[$key]['url_sub_orders']);
            unset($data[$key]['can_login']);
            unset($data[$key]['inquiry_tab']);
        }
        $response = successRes('Get Channel Partner Type');
        $response['data'] = $data;
        return response()
            ->json($response, $response['status_code'])
            ->header('Content-Type', 'application/json');
    }

    public function searchCountry(Request $request)
    {
        $searchKeyword = isset($request->q) ? $request->q : 'in';
        $id = isset($request->id) ? $request->id : 0;

        $CountryList = CountryList::select('id', 'name as text');
        if ($id != 0) {
            $CountryList->where('id', $id);
            $CountryList->limit(1);
        } else {
            $CountryList->where('name', 'like', '%' . $searchKeyword . '%');
            $CountryList->limit(5);
        }

        $CountryList = $CountryList->get();
        $response = successRes('CountryList');
        $response['data'] = $CountryList;
        return response()
            ->json($response, $response['status_code'])
            ->header('Content-Type', 'application/json');
    }

    public function searchCity(Request $request)
    {
        $searchKeyword = isset($request->q) ? $request->q : 'sur';

        $CityList = CityList::select('id', 'name as text');
        $CityList->where('name', 'like', '%' . $searchKeyword . '%');
        $CityList->where('status', 1);
        $CityList->limit(5);
        $CityList = $CityList->get();
        $response = [];
        $response = successRes('CityList');
        $response['data'] = $CityList;
        return response()
            ->json($response, $response['status_code'])
            ->header('Content-Type', 'application/json');
    }

    public function searchStateFromCountry(Request $request)
    {
        $searchKeyword = isset($request->q) ? $request->q : 'guj';
        $countryId = isset($request->country_id) ? $request->country_id : '';
        $id = isset($request->id) ? $request->id : 0;
        $StateList = [];

        if ($countryId != '') {
            $StateList = StateList::select('id', 'name as text');
            $StateList->where('name', 'like', '%' . $searchKeyword . '%');
            $StateList->where('country_id', $request->country_id);
            $StateList->limit(5);
            $StateList = $StateList->get();
        } elseif ($id != 0) {
            $StateList = StateList::select('id', 'name as text');
            $StateList->where('id', $id);
            $StateList->limit(1);
            $StateList = $StateList->get();
        }

        $response = successRes('StateList');
        $response['data'] = $StateList;
        return response()
            ->json($response, $response['status_code'])
            ->header('Content-Type', 'application/json');
    }

    public function searchCityFromState(Request $request)
    {
        $searchKeyword = isset($request->q) ? $request->q : 'sur';
        $stateId = isset($request->state_id) ? $request->state_id : '';
        $id = isset($request->id) ? $request->id : 0;
        $CityList = [];

        if ($stateId != '') {
            $CityList = CityList::select('id', 'name as text');
            $CityList->where('state_id', $request->state_id);
            $CityList->where('name', 'like', '%' . $searchKeyword . '%');
            $CityList->where('status', 1);
            $CityList->limit(5);
            $CityList = $CityList->get();
        } elseif ($id != 0) {
            $CityList = CityList::select('id', 'name as text');
            $CityList->where('id', $id);
            $CityList->limit(1);
            $CityList = $CityList->get();
        }

        $response = successRes('CityList');
        $response['data'] = $CityList;
        return response()
            ->json($response, $response['status_code'])
            ->header('Content-Type', 'application/json');
    }

    public function allCity(Request $request)
    {
        $CityList = CityList::select('city_list.id as city_id', 'city_list.name as city_name', 'country_list.id as country_id', 'country_list.name as country_name', 'state_list.id as state_id', 'state_list.name as state_name');
        $CityList->leftJoin('state_list', 'state_list.id', '=', 'city_list.state_id');
        $CityList->leftJoin('country_list', 'country_list.id', '=', 'city_list.country_id');
        // $CityList->where('state_id', $request->state_id);
        // $CityList->where('name', 'like', "%" . $searchKeyword . "%");
        $CityList->where('city_list.status', 1);
        // $CityList->limit(5);
        $CityList = $CityList->get();

        $response = successRes('CityList');
        $response['data'] = $CityList;
        return response()
            ->json($response, $response['status_code'])
            ->header('Content-Type', 'application/json');
    }

    function searchCourier(Request $request)
    {
        $DataMaster = [];

        $MainMaster = MainMaster::select('id')->where('code', 'COURIER_SERVICE')->first();
        if ($MainMaster) {
            $DataMaster = [];
            $DataMaster = DataMaster::select('id', 'name as text');
            $DataMaster->where('main_master_id', $MainMaster->id);
            $DataMaster->where('name', 'like', '%' . $request->q . '%');
            $DataMaster->limit(5);
            $DataMaster = $DataMaster->get();
        }

        $response = successRes('Courier');
        $response['data'] = $DataMaster;
        return response()
            ->json($response, $response['status_code'])
            ->header('Content-Type', 'application/json');
    }

    function getArchitectsSourceTypes(Request $request)
    {
        $data = getArchitectsSourceTypes();
        $response = successRes('Architects Source Types');
        $response['data'] = $data;
        return response()
            ->json($response, $response['status_code'])
            ->header('Content-Type', 'application/json');
    }

    public function getArchitectsTypes()
    {
        $data = getArchitects();
        $data = array_values($data);
        foreach ($data as $key => $value) {
            unset($data[$key]['lable']);
            unset($data[$key]['short_name']);
            unset($data[$key]['another_name']);
            unset($data[$key]['key']);
            unset($data[$key]['url']);
            unset($data[$key]['can_login']);
        }
        $response = successRes('Architects Types');
        $response['data'] = $data;
        return response()
            ->json($response, $response['status_code'])
            ->header('Content-Type', 'application/json');
    }

    public function getElecriciansTypes()
    {
        $data = getElectricians();
        $data = array_values($data);
        foreach ($data as $key => $value) {
            unset($data[$key]['lable']);
            unset($data[$key]['short_name']);
            unset($data[$key]['another_name']);
            unset($data[$key]['key']);
            unset($data[$key]['url']);
            unset($data[$key]['can_login']);
        }
        $response = successRes('Elecricians Types');
        $response['data'] = $data;
        return response()
            ->json($response, $response['status_code'])
            ->header('Content-Type', 'application/json');
    }

    public function getUserTypes()
    {
        $data = getAllUserTypes();
        $data = array_values($data);
        foreach ($data as $key => $value) {
            unset($data[$key]['lable']);
            unset($data[$key]['short_name']);
            //unset($data[$key]['another_name']);
            unset($data[$key]['key']);
            unset($data[$key]['url']);
            unset($data[$key]['can_login']);
            if (isset($data[$key]['url_view'])) {
                unset($data[$key]['url_view']);
            }

            if (isset($data[$key]['url_sub_orders'])) {
                unset($data[$key]['url_sub_orders']);
            }
            if (isset($data[$key]['inquiry_tab'])) {
                unset($data[$key]['inquiry_tab']);
            }

            if (isset($data[$key]['another_name'])) {
                unset($data[$key]['another_name']);
            }
        }
        $response = successRes('User Types');
        $response['data'] = $data;
        return response()
            ->json($response, $response['status_code'])
            ->header('Content-Type', 'application/json');
    }

    public function getUserStatus()
    {
        //$data = getUserStatusLable();

        $data = [];
        $noOfCount = count($data);
        $data[$noOfCount]['id'] = 0;
        $data[$noOfCount]['name'] = 'Inactive';

        $noOfCount = count($data);
        $data[$noOfCount]['id'] = 1;
        $data[$noOfCount]['name'] = 'Active';

        $noOfCount = count($data);
        $data[$noOfCount]['id'] = 3;
        $data[$noOfCount]['name'] = 'Pending';

        $response = successRes('User Types');
        $response['data'] = $data;
        return response()
            ->json($response, $response['status_code'])
            ->header('Content-Type', 'application/json');
    }

    function checkUserPhoneNumber(Request $request)
    {
        $rules = [];
        $rules['user_phone_number'] = 'required|digits:10|regex:/^[1-9][0-9]*$/';

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $response = errorRes($validator->errors()->first());
            $response['data'] = $validator->errors();
        } else {
            $User = User::select('id', 'first_name', 'last_name');
            if ((isset($request->user_id) && $request->user_id != 0) || $request->user_id != '') {
                $User->where('id', '!=', $request->user_id);
            }
            $User = $User->where('phone_number', $request->user_phone_number)->first();

            if ($User) {
                $response = errorRes('User already registed with phone number, #' . $User['id'] . ' assigned to ' . $User['first_name'] . ' ' . $User['last_name'], 200);
                $response['data']['is_valid'] = false;
                $response['status'] = 1;
            } else {
                $response = successRes('User phone number is valid', 200);
                $response['data']['is_valid'] = true;
                $response['status'] = 0;
            }
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function checkUserEmail(Request $request)
    {
        $rules = [];
        $rules['user_email'] = 'required|email:rfc,dns';

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $response = errorRes($validator->errors()->first());
            $response['data'] = $validator->errors();
        } else {
            $User = User::select('id', 'first_name', 'last_name');
            if ((isset($request->user_id) && $request->user_id != 0) || $request->user_id != '') {
                $User->where('id', '!=', $request->user_id);
            }
            $User = $User->where('email', $request->user_email)->first();

            if ($User) {
                $response = errorRes('User already registed with email, #' . $User['id'] . ' assigned to ' . $User['first_name'] . ' ' . $User['last_name'], 200);
                $response['data']['is_valid'] = false;
                $response['status'] = 1;
            } else {
                $response = successRes('User email is valid', 200);
                $response['data']['is_valid'] = true;
                $response['status'] = 0;
            }
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function getBannerImages(Request $request)
    {
        $columns = ['banner_master.id', 'banner_master.name', 'banner_master.image', 'banner_master.status'];

        $query = BannerMaster::query();
        $query->where('banner_master.status', 1);
        $query->select($columns);
        $query->limit(5);
        $query->orderBy('banner_master.id', 'DESC');
        $data = $query->get();
        foreach ($data as $key => $value) {
            $image = 'https://erp.whitelion.in/assets/images/favicon.ico';
            if ($value['image'] == null) {
                $image = 'https://erp.whitelion.in/assets/images/favicon.ico';
            } else {
                $image = getSpaceFilePath($value['image']);
            }
            $data[$key]['image'] = $image;
        }

        $response = successRes('Success');
        $response['data'] = $data;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function createAutoGenartedTask(Request $request)
    {
        $lead_task = LeadTask::query();
        $lead_task->select('lead_tasks.lead_id');
        $lead_task->from('lead_tasks');
        $lead_task->where('lead_tasks.is_autogenerate', '=', 1);
        $leadTaskIds = $lead_task->distinct()->pluck('lead_tasks.lead_id');
        $lead = Lead::query();
        $lead->whereNotIn('leads.id', $leadTaskIds);
        $lead->where('leads.is_deal', '=', 1);
        $lead->where('leads.status', '=', 103);
        // $lead->where('leads.inquiry_id', '!=', 0);
        $leadIds = $lead->distinct()->pluck('leads.id');
        $lead = $lead->get();

        $arrLeadIds = [];
        $arrErrorLeadIds = [];

        foreach ($lead as $key => $value) {
            try {
                // $current_date = date('Y-m-d H:i:s');
                // $Plus_three_day = date('Y-m-d H:i:s', strtotime($current_date . " +3 days"));
                // $city_id = CityList::find($value->city_id);
                // $User_id = User::select('id')->where('type', 9)->where('status', 1)->get();
                // $Telesales = TeleSales::query()->whereIn('user_id', $User_id)->whereRaw("FIND_IN_SET('$city_id->state_id', states)")->first();
                // $Telesales = 3566;

                // START TELESALES TASK ASSIGN
                // $LeadTask = new LeadTask();
                // $LeadTask->lead_id = $value->id;
                // $LeadTask->user_id = $Telesales;
                // $LeadTask->assign_to = $Telesales;
                // $LeadTask->task = "Verified Architect & Electrician Detail";
                // $LeadTask->due_date_time = $Plus_three_day;
                // $LeadTask->reminder = getReminderTimeSlot($Plus_three_day)[1]['datetime'];
                // $LeadTask->reminder_id = 1;
                // $LeadTask->description = 'Auto Generated Task';
                // $LeadTask->is_notification = 1;
                // $LeadTask->is_autogenerate = 1;
                // $LeadTask->save();
                // END TELESALES TASK ASSIGN

                // START SERVICE USER TASK ASSIGN
                // $LeadTask = new LeadTask();
                // $LeadTask->lead_id = $value->id;
                // $LeadTask->user_id = 4871;
                // $LeadTask->assign_to = 4871;
                // $LeadTask->task = "Verified Installation Status From " . $value->id . "-" . $value->first_name . " " . $value->last_name . "";
                // $LeadTask->due_date_time = $Plus_three_day;
                // $LeadTask->reminder = getReminderTimeSlot($Plus_three_day)[1]['datetime'];
                // $LeadTask->reminder_id = 1;
                // $LeadTask->description = 'Auto Generated Task';
                // $LeadTask->is_notification = 1;
                // $LeadTask->is_autogenerate = 1;
                // $LeadTask->save();
                // END SERVICE USER TASK ASSIGN

                $res['lead_id'] = $value->id;
                $res['status_entry'] = 'success';
                array_push($arrLeadIds, $res);
            } catch (\Exception $e) {
                $error['lead_id'] = $value->id;
                $error['error_entry'] = $e->getMessage();
                $error['status_entry'] = 'error';
                array_push($arrErrorLeadIds, $error);
            }
        }

        $response = successRes();
        $response['lead_ids_only'] = $leadIds;
        $response['lead_ids'] = $arrLeadIds;
        $response['error_lead_ids'] = $arrErrorLeadIds;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function facebookLead(Request $request)
    {
		$accessToken = 'EAAHY9SQZBvPMBOzYaOe8wylSV5NTii0jI6cqy9QqcrbqUT5EETeomtpQJ7ZBlQ5FQybY1TjPesIzfsdszjTwhyYSk5ZBO75ttEe9FGGKRLGr7IM5VvQJo94afYUAU3TKZCinyUVDSS3gMmkYZCsUIiKld4ZC8yaZBYxaYgufZC3PoYQ0ZCs3XVwRDsrhBUFcXEH76fwZDZD';
        $pageId = '379301865557959';

		$data = array();
        try {
            $fb = new Facebook([
                'app_id' => '1600121854059793',
                'app_secret' => 'ddc7c47651aa83b50f0957394914fda8',
                'default_graph_version' => 'v12.0',
            ]);

            $response = $fb->get("/$pageId/leads?access_token=$accessToken");
            $graphEdge = $response->getGraphEdge();
			$data = $graphEdge;

            // foreach ($graphEdge as $lead) {
            //     // Process and store the lead data in your database
            //     $leadData = $lead->asArray();
            //     // Your database storage logic goes here
            // }

            return response()->json(['message' => 'Leads synced successfully']);
        } catch (FacebookSDKException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
        $response = successRes();
        $response['data'] = $data;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function whatsappCheck(Request $request){
        $RessaveLeadAndDealStatusInAction = saveLeadAndDealStatusInAction(46300, 4, $request->ip());
        return response()->json($RessaveLeadAndDealStatusInAction);
    }

    function BillListHodApproveButPointPendingExcel(Request $request) {
		
		$Query = LeadFile::query();
        $Query->select('crm_setting_file_tag.name as tag_name', 'lead_files.*');
        $Query->leftJoin('crm_setting_file_tag', 'crm_setting_file_tag.id', '=', 'lead_files.file_tag_id');
        $Query->where('lead_files.file_tag_id', 3);
        $Query->where('lead_files.is_active', 1);
        $Query->where('lead_files.status', 100);
        $Query->where('lead_files.hod_approved', 1);
        $billData = $Query->get();

		$data = array();

		foreach ($billData as $key => $value) {
			$LeadContact = LeadContact::query();
            $LeadContact->select('crm_setting_contact_tag.name as tag_name', 'lead_contacts.*');
            $LeadContact->leftJoin('crm_setting_contact_tag', 'crm_setting_contact_tag.id', '=', 'lead_contacts.contact_tag_id');
            $LeadContact->where('lead_contacts.lead_id', $value->lead_id);
            $LeadContact->where('lead_contacts.status', 1);
            $LeadContact = $LeadContact->get();

			foreach ($LeadContact as $LeadContactKey => $LeadContactValue) {
				if ($LeadContactValue['type'] == 202) {
				    $newdata['lead_id'] = $LeadContactValue['lead_id'];
					$newdata['user_type'] = "Architect(Prime)";
                    $newdata['user_name'] = $LeadContactValue['first_name'].' '.$LeadContactValue['last_name'];
				    $newdata['user_mobile_number'] = $LeadContactValue['phone_number'];
				    $newdata['bill_amount'] = $value->billing_amount;
				    $newdata['bill_point'] = $value->point;
				    $newdata['bill_status'] = isset(getLeadBillstatus()[$value->status]['code']) ? getLeadBillstatus()[$value->status]['code'] : 0;
				    $newdata['hod_approved'] = ($value->hod_approved == 1) ? 'Approved' : 'Hod Pending';
				    $newdata['point_status'] = 'Not Credited';
                    
                    $arc_id = explode('-', $LeadContactValue['type_detail'])[2];
                    
                    $chk_log = CRMLog::query();
                    $chk_log->where('lead_id',$LeadContactValue['lead_id']);
                    $chk_log->where('for_user_id',$arc_id);
                    $chk_log->where('points',$value->point);
                    $chk_log->first();
				    if($chk_log){
				    	$newdata['point_status'] = 'Credited';
				    }

				    array_push($data,$newdata);
				} else if ($LeadContactValue['type'] == 302) {
                    $newdata['lead_id'] = $LeadContactValue['lead_id'];
					$newdata['user_type'] = "Electrician(Prime)";
                    $newdata['user_name'] = $LeadContactValue['first_name'].' '.$LeadContactValue['last_name'];
				    $newdata['user_mobile_number'] = $LeadContactValue['phone_number'];
				    $newdata['bill_amount'] = $value->billing_amount;
				    $newdata['bill_point'] = $value->point;
				    $newdata['bill_status'] = isset(getLeadBillstatus()[$value->status]['code']) ? getLeadBillstatus()[$value->status]['code'] : 0;
				    $newdata['hod_approved'] = ($value->hod_approved == 1) ? 'Approved' : 'Hod Pending';
				    $newdata['point_status'] = 'Not Credited';
                    
                    $arc_id = explode('-', $LeadContactValue['type_detail'])[2];
                    
                    $chk_log = CRMLog::query();
                    $chk_log->where('lead_id',$LeadContactValue['lead_id']);
                    $chk_log->where('for_user_id',$arc_id);
                    $chk_log->where('points',$value->point);
                    $chk_log->first();
				    if($chk_log){
				    	$newdata['point_status'] = 'Credited';
				    }
                        
				    array_push($data,$newdata);
				}
				
			}

		}
		$response = successRes();
        $response['data'] = $data;
		return response()->json($response)->header('Content-Type', 'application/json');
	}

    function LostLeadDealExcelWithQuestion(Request $request) {
		
        $column = array(
            'leads.id AS id',
            'quot.quot_whitelion_amount',
            'quot.quot_billing_amount',
            'quot.quot_other_amount',
            'quot.quot_total_amount',
        );
		$Query = Lead::query();
        $Query->select($column);
        $Query->selectRaw('CASE WHEN leads.is_deal = 0 THEN "Lead" WHEN leads.is_deal = 1 THEN "Deal" ELSE "Undifine" END AS type');
        $Query->selectRaw('CONCAT(leads.first_name," ",leads.last_name) AS client_name');
        $Query->selectRaw('CASE
        WHEN leads.source_type = "user-202" THEN "Architect"
        WHEN leads.source_type = "user-201" THEN "Architect"
        WHEN leads.source_type = "user-302" THEN "Electrician"
        WHEN leads.source_type = "user-301" THEN "Electrician"
        WHEN leads.source_type = "user-101" THEN "ASM"
        WHEN leads.source_type = "user-102" THEN "ADM"
        WHEN leads.source_type = "user-103" THEN "APM"
        WHEN leads.source_type = "user-104" THEN "AD"
        WHEN leads.source_type = "user-105" THEN "Retailer"
        WHEN leads.source_type = "exhibition-9" THEN "Exhibition"
        WHEN leads.source_type = "textnotrequired-2" THEN "Whitelion HO"
        WHEN leads.source_type = "textnotrequired-6" THEN "Existing Client"
        WHEN leads.source_type = "textrequired-5" THEN "Other"
        WHEN leads.source_type = "textnotrequired-1" THEN "Facebook"
        WHEN leads.source_type = "textnotrequired-11" THEN "Instagram"
        WHEN leads.source_type = "textnotrequired-12" THEN "Google Ads"
        WHEN leads.source_type = "fix-3" THEN "Cold call"
        ELSE "Undifine"
        END AS source_type');
        $Query->selectRaw('CASE
        WHEN leads.source_type = "user-202" THEN CONCAT(source_1.first_name," ",source_1.last_name)
        WHEN leads.source_type = "user-201" THEN CONCAT(source_1.first_name," ",source_1.last_name)
        WHEN leads.source_type = "user-302" THEN CONCAT(source_1.first_name," ",source_1.last_name)
        WHEN leads.source_type = "user-301" THEN CONCAT(source_1.first_name," ",source_1.last_name)
        WHEN leads.source_type = "user-101" THEN source_2.firm_name
        WHEN leads.source_type = "user-102" THEN source_2.firm_name
        WHEN leads.source_type = "user-103" THEN source_2.firm_name
        WHEN leads.source_type = "user-104" THEN source_2.firm_name
        WHEN leads.source_type = "user-105" THEN source_2.firm_name
        WHEN leads.source_type = "exhibition-9" THEN source_3.name
        WHEN leads.source_type = "textnotrequired-2" THEN leads.source
        WHEN leads.source_type = "textnotrequired-6" THEN leads.source
        WHEN leads.source_type = "textrequired-5" THEN leads.source
        WHEN leads.source_type = "textnotrequired-1" THEN leads.source
        WHEN leads.source_type = "textnotrequired-11" THEN leads.source
        WHEN leads.source_type = "textnotrequired-12" THEN leads.source
        WHEN leads.source_type = "fix-3" THEN leads.source
        ELSE "Undifine"
        END AS source');
        $Query->selectRaw('CASE
        WHEN leads.status = 1 THEN "Entry"
        WHEN leads.status = 2 THEN "Call"
        WHEN leads.status = 3 THEN "Qualified"
        WHEN leads.status = 4 THEN "Demo Meeting Done"
        WHEN leads.status = 5 THEN "Not Qualified"
        WHEN leads.status = 6 THEN "Cold"
        WHEN leads.status = 100 THEN "Quotation"
        WHEN leads.status = 101 THEN "Negotiation"
        WHEN leads.status = 102 THEN "Token Received"
        WHEN leads.status = 103 THEN "Won"
        WHEN leads.status = 104 THEN "Lost"
        WHEN leads.status = 105 THEN "Cold"
        ELSE "Undifine"
        END AS status');
        $Query->selectRaw('DATE(leads.closing_date_time) AS closing_date');
        $Query->selectRaw('CONCAT(users.first_name," ",users.last_name) AS owner_name');
        $Query->selectRaw('DATE(leads.created_at) AS created_date');
        $Query->selectRaw('CONCAT(created.first_name," ",created.last_name) AS created_by');
        $Query->leftJoin('users', 'users.id', '=', 'leads.assigned_to');
        $Query->leftJoin('city_list as city', 'city.id', '=', 'leads.city_id');


        // $Query->selectRaw('MONTHNAME(lead_status_detail.created_at) AS status_update_month');
        // $Query->selectRaw('DATE(lead_status_detail.created_at) AS status_update_date');
        // $Query->selectRaw("DATE_FORMAT(lead_status_detail.created_at, '%H:%i:%s') AS status_update_time");
        // $Query->selectRaw('CONCAT(status_update_by.first_name," ",status_update_by.last_name) AS status_update_by');
        $Query->leftJoin('wltrn_quotation as quot', function($join)
        {
            $join->on('quot.inquiry_id', '=', 'leads.id');
            $join->where('quot.isfinal', 1);
        });
        $Query->leftJoin('users AS source_1', 'source_1.id', '=', 'leads.source');
        $Query->leftJoin('users AS created', 'created.id', '=', 'leads.created_by');
        $Query->leftJoin('channel_partner AS source_2', 'source_2.user_id', '=', 'leads.source');
        $Query->leftJoin('exhibition AS source_3', 'source_3.id', '=', 'leads.source');
        // $Query->leftJoin('lead_status_updates as lead_status_detail', function ($join) {
        //     $join->select('lead_status_detail.new_status');
        //     $join->on('lead_status_detail.lead_id', '=', 'leads.id');
        //     $join->where('lead_status_detail.new_status', 'leads.status');
            // $join->where('lead_status_detail.new_status', 103);
            // $join->whereIn('lead_status_detail.new_status', array(5, 104));
        //     $join->orderBy('lead_status_detail.created_at', 'DESC');
        //     $join->limit(1);
        // });
        // $Query->leftJoin('users AS status_update_by', 'status_update_by.id', '=', 'lead_status_detail.entryby');
        //LOST - $Query->whereIn('leads.id', array(44247,44716,26598,26462,45810,45823,45565,44082,45243,26364,45662,45185,45830,45045,45157,45269,45351,45789,46149,11569,19699,45741,45053,45233,45181,45446,44199,44041,19601,19584,46163,150,45999,45453,45718,26791,46388,46266,46264,46166,46199,46285,46286,45852,30845,20406,44025,44423,46462,24237,44885,45421,24806,45752,11437,24042,45845,11440,11476,26832,26834,45705,24177,45835,46375,46073,21522,44592,19579,19641,44135,32877,46740,46739,44409,44457,11320,46445,46447,44773,45213,33212,33126,45579,46346,21385,21210,45126,26886,21132,21418,21419,21452,46101,46040,46421,44781,44164,44007,12212,12243,46180,26601,11578,46617,16252,46031,33048,16451,16103,46234,46806,16256,26736,45414,46481,46055,16466,16295,46341,46232,46046,46045,45670,45572,45552,44863,44990,11242,44938,32621,33047,32636,16119,16443,16478,11498,33016,46072,45863,10994,24408,44918,46223,46222,46221,46219,46211,46410,46034,45850,45777,45659,44916,44881,44570,44568,44563,44557,44552,44542,26829,46352,46320,46334,46261,46086,46487,45679,46244,46355,46245,46287,46136,46228,46202,45491,46048,45251,45899,45887,45136,44994,44843,46063,29519,43867,46814,45265,46373,16315,45397,45091,45310,15953));
        //WON - $Query->whereIn('leads.id', array(45109,45082,45017,44392,24006,45252,43895,24810,21420,16393,44028,44720,44619,45227,45288,44725,45219,44995,45308,45107,45260,45115,45257,45337,45004,45306,45216,24824,45272,45226,44967,33245,45340,45377,45396,44371,24383,45096,44262,24371,30852,45431,11143,26860,44850,45124,45415,45291,45238,45205,45444,45220,45461,45128,45083,45129,45056,45160,45224,45236,44629,29522,29520,45005,44815,45445,45106,44940,44949,45215,45442,45158,16395,25523,45627,45190,16261,30712,19,45383,45276,45370,45325,45290,45701,45706,11982,45320,45460,45025,45432,45223,45188,9090,106,44947,24400,24419,24427,26781,24334,43943,44355,44682,44839,45302,45753,45760,45764,44239,45759,44932,44149,45771,45772,11281,45626,45180,45688,44880,45023,45698,45817,45818,45820,30844,24453,45293,44854,45030,20201,44783,44762,45266,44866,44678,45263,44726,45348,45441,24510,45267,11579,24044,45084,45371,45716,44639,45435,44080,26588,26717,45147,45047,26757,26713,45164,45668,45361,45162,45778,44805,30848,45689,45872,45873,45874,45875,45876,45879,45666,45596,45268,44862,45816,44382,45426,45719,45775,45437,44590,45245,45920,45914,102,11537,45919,45152,19625,44657,46038,45840,45683,45319,45112,46060,46022,21456,21443,46082,45347,46088,45795,46002,46000,46099,45286,45812,24063,45282,44546,45143,24358,45200,70,45380,46065,44050,43871,45773,45788,44671,45369,45786,26875,45357,26614,46078,44966,12329,46033,45761,45409,45862,16468,46154,46148,45127,46243,24132,45700,46028,45678,24131,16216,45381,46265,45614,44581,33250,19448,46013,46111,45763,45672,45256,130,46251,24410,30610,30670,24340,43994,46190,45721,46369,45838,46229,46077,45858,46289,46296,46269,45368,46153,45375,46249,24807,44976,26648,45774,45916,45783,44789,46324,45062,16088));
        // $Query->whereIn('leads.assigned_to', array());
        $Query->whereIn('leads.source_type', array("textnotrequired-1","textnotrequired-11","textnotrequired-12"));
        $Query->orderBy('leads.assigned_to', 'asc');
        $leaddata = $Query->get();

		$data = array();

		// foreach ($leaddata as $lead_key => $lead_value) {
        //     $LeadQuestionAnswer = LeadQuestionAnswer::select('lead_question_answer.lead_question_id', 'lead_question.question', 'lead_question_answer.answer', 'lead_question.type', 'lead_question_answer.created_at', 'lead_question_answer.updated_at');
        //     $LeadQuestionAnswer->leftJoin('lead_question', 'lead_question.id', '=', 'lead_question_answer.lead_question_id');
        //     $LeadQuestionAnswer->where('lead_question_answer.lead_id', $lead_value['id']);
        //     //LOST - $LeadQuestionAnswer->whereIn('lead_question.id', array(10,11,12,13,20));
        //     //WON - $LeadQuestionAnswer->whereIn('lead_question.id', array(19));
        //     $LeadQuestionAnswer->where('lead_question_answer.reference_type', 'Lead-Status-Update');
        //     $LeadQuestionAnswer->where('lead_question_answer.answer', '!=', '');
        //     $LeadQuestionAnswer = $LeadQuestionAnswer->get();

        //     // LOST
        //     $client_selected_which_product = '';
        //     $reason_of_rejection = '';
        //     $notes = '';

        //     // WON
        //     $MaterialSentBy = '';
        //     $LeadQuestionAnswer = json_encode($LeadQuestionAnswer);
        //     $LeadQuestionAnswer = json_decode($LeadQuestionAnswer, true);
        //     foreach ($LeadQuestionAnswer as $key => $value) {
        //         if ($value['type'] == 0) {
        //             // $LeadQuestionAnswer[$key]['option'] = $value['answer'];
        //             $notes = $value['answer'];
        //         } elseif ($value['type'] == 1) {
        //             $option_selected = '';
        //             $option_ids = LeadQuestionOptions::select('id')
        //                 ->where('lead_question_id', $value['lead_question_id'])
        //                 ->distinct()
        //                 ->pluck('id')
        //                 ->all();

        //             if (!in_array($value['answer'], $option_ids)) {
        //                 $ChannelPart = ChannelPartner::select('firm_name')
        //                     ->where('user_id', $value['answer'])
        //                     ->first();
        //                 if ($ChannelPart) {
        //                     $option_selected = $ChannelPart->firm_name;
        //                     $MaterialSentBy = $ChannelPart->firm_name;
        //                 } else {
        //                     $option_selected = '';
        //                 }
        //             } else {
        //                 $LeadQuestionOption = LeadQuestionOptions::select('lead_question_options.option');
        //                 $LeadQuestionOption->where('id', $value['answer']);
        //                 $LeadQuestionOption = $LeadQuestionOption->first();
        //                 $option_selected = $LeadQuestionOption->option;
        //             }
        //             $client_selected_which_product = $option_selected;
        //             // $LeadQuestionAnswer[$key]['option'] = $option_selected;
                    
        //         } elseif ($value['type'] == 5) {
        //             // $LeadQuestionAnswer[$key]['option'] = $value['answer'];

        //         } elseif ($value['type'] == 4 || $value['type'] == 6) {
        //             $LeadQuestionOption = LeadQuestionOptions::select('lead_question_options.option');
        //             $LeadQuestionOption->whereIn('id', explode(',', $value['answer']));
        //             $LeadQuestionOption = $LeadQuestionOption->get();

        //             $MultipleAnswer = '';
        //             foreach ($LeadQuestionOption as $Okey => $Ovalue) {
        //                 $MultipleAnswer .= $Ovalue['option'] . ', ';
        //             }

        //             $reason_of_rejection = $MultipleAnswer;
        //             // $LeadQuestionAnswer[$key]['option'] = $MultipleAnswer;
                    
        //         } elseif ($value['type'] == 7) {
        //             // $LeadQuestionAnswer[$key]['option'] = getSpaceFilePath($value['answer']);
        //         }
                
        //     }
        //     // LOST
        //     $leaddata[$lead_key]['Q1. Client selected which product?'] = $client_selected_which_product;
        //     $leaddata[$lead_key]['Q2. Reason of Rejection?'] = $reason_of_rejection;
        //     $leaddata[$lead_key]['Q3. Notes'] = $notes;

        //     // WON
        //     // $leaddata[$lead_key]['Which Channel partner Through A Material Sent on Site?'] = $MaterialSentBy;
            
		// }
		$response = successRes();
        $response['data'] = $leaddata;
		return response()->json($response)->header('Content-Type', 'application/json');
	}

    public function LeadGenerateThrewExcel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'q_lead_excel' => ['required'],
        ]);
        if ($validator->fails()) {

            $response = array();
            $response['status'] = 0;
            $response['msg'] = "The request could not be understood by the server due to malformed syntax";
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors();

            return response()->json($response)->header('Content-Type', 'application/json');
        } else {
            $the_file = $request->file('q_lead_excel');
            try {
                $spreadsheet = IOFactory::load($the_file->getRealPath());
                $sheet        = $spreadsheet->getActiveSheet();
                $row_limit    = $sheet->getHighestDataRow();
                $row_range    = range(2, $row_limit);
                $data = array();
                foreach ($row_range as $row) {
                    
                    $sr_no = $sheet->getCell('A' . $row)->getValue();
                    $bill_name = $sheet->getCell('B' . $row)->getValue();
                    $customer_name = $sheet->getCell('C' . $row)->getValue();
                    $mobile_number = $sheet->getCell('D' . $row)->getValue();
                    $address = $sheet->getCell('E' . $row)->getValue();
                    $date = $sheet->getCell('F' . $row)->getValue();
                    $quotation_amount = $sheet->getCell('G' . $row)->getValue();
                    $clnz_name = $sheet->getCell('H' . $row)->getValue();
                    $deal_no = $sheet->getCell('I' . $row)->getValue();
                    $bill_mrp = $sheet->getCell('J' . $row)->getValue();
                    $bill_point= $sheet->getCell('K' . $row)->getValue();

                    $data_new = [];
                    $data_new['sr_no'] = $sr_no;
                    $data_new['bill_name'] = $bill_name;
                    $data_new['client_name'] = $customer_name;
                    $data_new['phone_number'] = $mobile_number;
                    $data_new['address'] = $address;
                    $data_new['date'] = $date;
                    $data_new['amount'] = $quotation_amount;
                    $data_new['clnz_name'] = $clnz_name;
                    $data_new['deal_no'] = $deal_no;
                    $data_new['mrp'] = $bill_mrp;
                    $data_new['point'] = $bill_point;
                    $data_new['message'] = 'Only Excel';
                    $data_new['lead_id'] = 0;
                    
                    if (!empty($mobile_number)) {
                        $chkLeadPhoneNumber = Lead::where('phone_number', $mobile_number)->first();
                        if ($chkLeadPhoneNumber) {
                            $data_new['message'] = 'Lead with the same phone number already exists. #'.$chkLeadPhoneNumber->id;
                            $data_new['lead_id'] = $chkLeadPhoneNumber->id;

                            $LeadFile = new LeadFile();
                            $LeadFile->uploaded_by = 1;
                            $LeadFile->file_size = '0';
                            $LeadFile->name = "/s/question-attachment/".$bill_name.".pdf";
                            $LeadFile->lead_id = $chkLeadPhoneNumber->id;
                            $LeadFile->file_tag_id = 3;
                            $LeadFile->billing_amount = $bill_mrp;
                            $LeadFile->point = $bill_point;
                            $LeadFile->status = 100;
                            $LeadFile->claimed_date_time = date('Y-m-d H:i:s');
                            $LeadFile->save();

                            if($LeadFile){
                                $Plus_three_day = date('Y-m-d H:i:s');
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
                                    DB::table('lead_files')->where('lead_files.id', $LeadFile->id)->update(['reference_id' => $LeadTask->id,'reference_type' => 'Task']);
                                    $LeadFile = LeadFile::find($LeadFile->id);
                                    $is_not_clear_bill = LeadFile::query()->where('reference_id', $LeadFile['reference_id'])->whereIn('status', [101, 0])->count();
                                    if ($is_not_clear_bill == 0) {
                                        $Lead_task = LeadTask::find($LeadFile->reference_id);
                                        $Lead_task->is_closed = 1;
                                        $Lead_task->closed_date_time = date("Y-m-d H:i:s");
                                        $Lead_task->outcome_type = 1;
                                        $Lead_task->save();
                                    
                                        if ($Lead_task) {
                                            $is_not_close_task = LeadTask::query()->where('assign_to', 12)->where('lead_id', $Lead_task->lead_id)->where('is_closed', 0)->where('is_autogenerate', 1)->count();
                                            if ($is_not_close_task == 0) {
                                                $Lead = Lead::find($Lead_task->lead_id);
                                                $Lead->companyadmin_verification = 2;
                                                $Lead->save();
                                            }
                                        }
                                    }
                                    $Lead = Lead::find($Lead->id);
                                    $Lead->total_billing_amount = $Lead->total_billing_amount + $request->billing_amount;
                                    $Lead->total_point = $Lead->total_point + $request->point;
                                    $Lead->reward_status = 100;
                                    $Lead->updated_by = 1;
                                    $Lead->updateip = $request->ip();
                                    $Lead->update_source = 'WEB';
                                    $Lead->save();
                                    if($Lead){
                                        $description = "Lead Id " . $Lead->id . " Add " . $Lead->total_billing_amount . " Billing Amount And " . $Lead->total_point . " Total Point";
                                        $LeadTimeline = new LeadTimeline();
                                        $LeadTimeline->user_id = 1;
                                        $LeadTimeline->type = "file-upload";
                                        $LeadTimeline->lead_id = $Lead->id;
                                        $LeadTimeline->reffrance_id = $Lead->id;
                                        $LeadTimeline->description = $description;
                                        $LeadTimeline->source = "WEB";
                                        $LeadTimeline->save();
                                    }
                                }
                                
                            }
                        }else{
                            $clientName = explode(' ', $customer_name);
						    $first_name = $clientName[0];
						    $last_name = isset($clientName[1]) ? implode(' ', array_slice($clientName, 1)) : '';
                            $lead = new Lead();
                            $lead->first_name = $first_name ?? '';
                            $lead->last_name = $last_name ?? '';
                            $lead->email = '';
                            $lead->addressline1 = $address;
                            $lead->customer_id = 0;
                            $lead->phone_number = $mobile_number;
                            $lead->assigned_to = 29;
                            $lead->source_type = 'user-202';
                            $lead->source = 257;
                            $lead->is_deal = 1;
                            $lead->architect = 257;
                            $lead->status = 103;
                            $lead->sub_status = 0;
                            $lead->created_by = 1;
                            $lead->updated_by = 1;
                            $lead->user_id = 1;
                            $lead->save();

                            if ($lead) {
                                $data_new['message'] = 'Lead Generated Successfully';
                                $data_new['lead_id'] = $lead->id;

                                $LeadTimeline = new LeadTimeline();
                                $LeadTimeline->user_id = 1;
                                $LeadTimeline->type = "lead-generate";
                                $LeadTimeline->lead_id = $lead->id;
                                $LeadTimeline->reffrance_id = $lead->id;
                                $LeadTimeline->description = "Lead created by Admin User";
                                $LeadTimeline->source = "WEB";
                                $LeadTimeline->save();

                                $LeadContact = new LeadContact();
                                $LeadContact->lead_id = $lead->id;
                                $LeadContact->contact_tag_id = 1;
                                $LeadContact->first_name = $lead->first_name;
                                $LeadContact->last_name = $lead->last_name;
                                $LeadContact->phone_number = $lead->phone_number;
                                $LeadContact->alernate_phone_number = 0;
                                $LeadContact->email = $lead->email;
                                $LeadContact->type = 0;
                                $LeadContact->type_detail = 0;
                                $LeadContact->save();

                                $Architect = User::find($lead->architect);

                                if ($Architect) {
                                    $LeadContact_arc = new LeadContact();
                                    $LeadContact_arc->lead_id = $lead->id;
                                    $LeadContact_arc->contact_tag_id = 0;
                                    $LeadContact_arc->first_name = $Architect->first_name;
                                    $LeadContact_arc->last_name = $Architect->last_name;
                                    $LeadContact_arc->phone_number = $Architect->phone_number;
                                    $LeadContact_arc->alernate_phone_number = 0;
                                    $LeadContact_arc->email = $Architect->email;
                                    $LeadContact_arc->type = $Architect->type;
                                    $LeadContact_arc->type_detail = 'user-' . $Architect->type . '-' . $Architect->id;
                                    $LeadContact_arc->save();
                                }
    
                                $LeadSource1 = new LeadSource();
                                $LeadSource1->lead_id = $lead->id;
                                $LeadSource1->source_type = $lead['source_type'];
                                $LeadSource1->source = $lead['source'];
                                $LeadSource1->is_main = 1;
                                $LeadSource1->save();

                                $LeadFile = new LeadFile();
                                $LeadFile->uploaded_by = 1;
                                $LeadFile->file_size = '0';
                                $LeadFile->name = "/s/question-attachment/".$bill_name.".pdf";
                                $LeadFile->lead_id = $lead->id;
                                $LeadFile->file_tag_id = 3;
                                $LeadFile->billing_amount = $bill_mrp;
                                $LeadFile->point = $bill_point;
                                $LeadFile->status = 100;
                                $LeadFile->claimed_date_time = date('Y-m-d H:i:s');
                                $LeadFile->save();

                                if($LeadFile){
                                    $Plus_three_day = date('Y-m-d H:i:s');
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
                                        DB::table('lead_files')->where('lead_files.id', $LeadFile->id)->update(['reference_id' => $LeadTask->id,'reference_type' => 'Task']);

                                        $LeadFile = LeadFile::find($LeadFile->id);
                                        $is_not_clear_bill = LeadFile::query()->where('reference_id', $LeadFile['reference_id'])->whereIn('status', [101, 0])->count();
                                        if ($is_not_clear_bill == 0) {
                                            $Lead_task = LeadTask::find($LeadFile->reference_id);
                                            $Lead_task->is_closed = 1;
                                            $Lead_task->closed_date_time = date("Y-m-d H:i:s");
                                            $Lead_task->outcome_type = 1;
                                            $Lead_task->save();
                                        
                                            if ($Lead_task) {
                                                $is_not_close_task = LeadTask::query()->where('assign_to', 12)->where('lead_id', $Lead_task->lead_id)->where('is_closed', 0)->where('is_autogenerate', 1)->count();
                                                if ($is_not_close_task == 0) {
                                                    $Lead = Lead::find($Lead_task->lead_id);
                                                    $Lead->companyadmin_verification = 2;
                                                    $Lead->save();
                                                }
                                            }
                                        }

                                        $Lead = Lead::find($Lead->id);
                                        $Lead->total_billing_amount = $Lead->total_billing_amount + $request->billing_amount;
                                        $Lead->total_point = $Lead->total_point + $request->point;
                                        $Lead->reward_status = 100;
                                        $Lead->updated_by = 1;
                                        $Lead->updateip = $request->ip();
                                        $Lead->update_source = 'WEB';
                                        $Lead->save();

                                        if($Lead){
                                            $description = "Lead Id " . $Lead->id . " Add " . $Lead->total_billing_amount . " Billing Amount And " . $Lead->total_point . " Total Point";
                                            $LeadTimeline = new LeadTimeline();
                                            $LeadTimeline->user_id = 1;
                                            $LeadTimeline->type = "file-upload";
                                            $LeadTimeline->lead_id = $Lead->id;
                                            $LeadTimeline->reffrance_id = $Lead->id;
                                            $LeadTimeline->description = $description;
                                            $LeadTimeline->source = "WEB";
                                            $LeadTimeline->save();
                                        }
                                    }
                                    
                                }

                            }else{
                                $data_new['message'] = 'Lead Save Time Error';
                            }
                        }
                    }else{
                        $data_new['message'] = 'Mobile Number Not Exist';
                    }
                    array_push($data,$data_new);
                }
                $response = successRes('Data Imported Successfully');
                $response['data'] = $data;
            } catch (Exception $e) {
                $response = errorRes($e->getMessage());
                $response['data'] = $e->getMessage();
            }

            return response()->json($response)->header('Content-Type', 'application/json');
        }
    }
    public function ArcEleCustomerThrewExcel(Request $request)
    {

        $lstData = array();
        $objNewItemHeader['firm_name'] = 'Name';
        $objNewItemHeader['phone_number'] = 'Phone Number';
        $objNewItemHeader['city'] = 'City';
        $objNewItemHeader['customer_name'] = 'Customer Name';
        $objNewItemHeader['customer_phone_number'] = 'Customer Phone Number';
        array_push($lstData,$objNewItemHeader);

        $objNewItemHeader['firm_name'] = '';
        $objNewItemHeader['phone_number'] = '';
        $objNewItemHeader['city'] = '';
        $objNewItemHeader['customer_name'] = 'Customer Name';
        $objNewItemHeader['customer_phone_number'] = 'Customer Phone Number';
        array_push($lstData,$objNewItemHeader);

		$Query = Electrician::query();
        $Query->select('users.id AS id','city.name AS city_name');
        $Query->selectRaw("CONCAT(users.first_name,' ',users.last_name) AS name");
        $Query->selectRaw("CONCAT(users.dialing_code,' ',users.phone_number) AS phone_number");
        $Query->leftJoin('users', 'users.id', '=', 'electrician.user_id');
        $Query->leftJoin('city_list as city', 'city.id', '=', 'users.city_id');
        $Query->whereIn('users.type', array(301,302));
        $data_list = $Query->get();
        
		foreach ($data_list as $key => $value) {
            
            
            $firm_name = $value->name;
            $phone_number = $value->phone_number;
            $city = $value->city_name;

            $qry_lead = Lead::query();
            $qry_lead->selectRaw("CONCAT(leads.first_name,' ',leads.last_name) AS name");
            $qry_lead->selectRaw("leads.phone_number AS phone_number");
			$qry_lead->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
			$qry_lead->where('lead_sources.source',$value->id);
            $qry_lead = $qry_lead->get();
            if($qry_lead){

                foreach ($qry_lead as $lead_key => $lead_value) {
                    $objNewItem['firm_name'] = $firm_name;
                    $objNewItem['phone_number'] = $phone_number;
                    $objNewItem['city'] = $city;
                    $objNewItem['customer_name'] = $lead_value['name'];
                    $objNewItem['customer_phone_number'] = $lead_value['phone_number'];
                    array_push($lstData,$objNewItem);    
                }
            }else{
                $objNewItem['firm_name'] = $firm_name;
                $objNewItem['phone_number'] = $phone_number;
                $objNewItem['city'] = $city;
                $objNewItem['customer_name'] = '';
                $objNewItem['customer_phone_number'] = '';
                array_push($lstData,$objNewItem);
            }

		}
		$response = successRes();
        $response['data'] = $lstData;
		return response()->json($response)->header('Content-Type', 'application/json');
        
    }

    public function duplicatDeal(Request $request)
    {

        $old_lead_id = $request->lead_id;
        $lead_replicate_unit = Lead::find($old_lead_id);
        $qry_new_lead = $lead_replicate_unit->replicate();
        $qry_new_lead->save();

        if($qry_new_lead){
            $new_lead_id = $qry_new_lead->id;
            // DUPLICATE CONTACT
            $lead_contact_list = LeadContact::query();
            $lead_contact_list->where('lead_id',$old_lead_id);
            $lead_contact_list = $lead_contact_list->get();
            foreach ($lead_contact_list as $contact_key => $contact_value) {
                $lead_replicate_contact = LeadContact::find($contact_value->id);
                $qry_new_contact = $lead_replicate_contact->replicate();
                $qry_new_contact->lead_id = $new_lead_id;
                $qry_new_contact->save();
            }
            
            // DUPLICATE QUOTATION
            $lead_quotation_list = Wltrn_Quotation::query();
            $lead_quotation_list->where('inquiry_id',$old_lead_id);
            $lead_quotation_list = $lead_quotation_list->get();
            foreach ($lead_quotation_list as $quotation_key => $quotation_value) {
                $lead_replicate_quotation = Wltrn_Quotation::find($quotation_value->id);
                $qry_new_quotation = $lead_replicate_quotation->replicate();
                $qry_new_quotation->inquiry_id = $new_lead_id;
                $qry_new_quotation->save();
                
                if($qry_new_quotation){
                    $lead_quotation_items_list = Wltrn_QuotItemdetail::query();
                    $lead_quotation_items_list->where('quot_id',$quotation_value->id);
                    $lead_quotation_items_list = $lead_quotation_items_list->get();
                    
                    foreach ($lead_quotation_items_list as $quotation_items_key => $quotation_items_value) {
                        $lead_replicate_quotation_item = Wltrn_QuotItemdetail::find($quotation_items_value->id);
                        $qry_new_quotation_item = $lead_replicate_quotation_item->replicate();
                        $qry_new_quotation_item->quot_id = $qry_new_quotation->id;
                        $qry_new_quotation_item->save();
                    }
                }
            }

            // DUPLICATE NOTES
            $lead_notes_list = LeadUpdate::query();
            $lead_notes_list->where('lead_id',$old_lead_id);
            $lead_notes_list = $lead_notes_list->get();
            foreach ($lead_notes_list as $notes_key => $notes_value) {
                $lead_replicate_notes = LeadUpdate::find($notes_value->id);
                $qry_new_notes = $lead_replicate_notes->replicate();
                $qry_new_notes->lead_id = $new_lead_id;
                $qry_new_notes->save();
            }

            // DUPLICATE TIMELINE
            $lead_timeline_list = LeadTimeline::query();
            $lead_timeline_list->where('lead_id',$old_lead_id);
            $lead_timeline_list = $lead_timeline_list->get();
            foreach ($lead_timeline_list as $timeline_key => $timeline_value) {
                $lead_replicate_timeline = LeadTimeline::find($timeline_value->id);
                $qry_new_timeline = $lead_replicate_timeline->replicate();
                $qry_new_timeline->lead_id = $new_lead_id;
                $qry_new_timeline->save();
            }


        }
        
		
		$response = successRes();
		return response()->json($response)->header('Content-Type', 'application/json');
        
    }

    function searchArchitectUserTag(Request $request)
    {
        $searchKeyword = isset($request->q) ? $request->q : "";

        $data = TagMaster::select('id', 'tagname as text');
        $data->where('tag_master.isactive', 1);
        $data->where('tag_master.tag_type', 202);
        $data->where('tag_master.tagname', 'like', "%" . $searchKeyword . "%");
        $data = $data->get();

        $response = successRes();
        $response['data'] = $data;
        return response()->json($response, $response['status_code'])->header('Content-Type', 'application/json');
    }
    
}
