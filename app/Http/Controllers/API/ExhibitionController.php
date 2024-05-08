<?php

namespace App\Http\Controllers\API;

use App\Models\Lead;
use App\Models\User;
use App\Models\UserLog;
use App\Models\CityList;
use App\Models\Architect;
use App\Models\TeleSales;
use App\Models\UserNotes;
use App\Models\Exhibition;
use App\Models\LeadSource;
use App\Models\LeadUpdate;
use App\Models\LeadContact;
use App\Models\UserContact;
use App\Models\Wlmst_Client;
use Illuminate\Http\Request;
use App\Models\ExhibitionInquiry;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\CRMSettingStageOfSite;
use App\Models\ExhibitionSalesPersons;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Whatsapp\WhatsappApiContoller;

class ExhibitionController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $tabCanAccessBy = array(2, 201, 202, 301, 302, 101, 102, 103, 104, 105, 11, 6, 8, 0, 1);
            if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
                $response = errorRes("Invalid access", 401);
                return response()->json($response, $response['status_code'])->header('Content-Type', 'application/json');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $query = ExhibitionSalesPersons::query();
        $query->leftJoin('exhibition', 'exhibition_sales_persons.exhibition_id', '=', 'exhibition.id');
        $query->leftJoin('city_list', 'exhibition.city_id', '=', 'city_list.id');
        $query->where('exhibition_sales_persons.user_id', Auth::user()->id);
        $query->where('exhibition.status', 1);
        $query->select('exhibition.*', 'city_list.name as city_name');
        $query->orderBy('exhibition.id', 'desc');
        $data = $query->paginate(10);
        $response = successRes("List of exhibition");
        $response['data'] = $data;
        return response()->json($response, $response['status_code'])->header('Content-Type', 'application/json');
    }

    public function saveInquiry(Request $request)
    {

        $rules = array();
        $rules['exhibition_json_array'] = 'required';
        // $rules['exhibition_id'] = 'required';
        // $rules['type'] = 'required';
        // $rules['phone_number'] = 'required';
        // $rules['email'] = 'required';
        // $rules['first_name'] = 'required';
        // $rules['last_name'] = 'required';
        // $rules['city_id'] = 'required';
        // $rules['address_line1'] = 'required';
        // $rules['plan_type'] = 'required';
        // $rules['stage_of_site'] = 'required';
        // $rules['source'] = 'required';

        // $address_line2 = isset($request->address_line2) ? $request->address_line2 : '';
        // $firm_name = isset($request->firm_name) ? $request->firm_name : '';
        // $remark = isset($request->remark) ? $request->remark : '';



        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {

            $response = array();
            $response['status'] = 0;
            $response['msg'] = "The request could not be understood by the server due to malformed syntax";
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors();
        } else {


            // USER TYPE
            // "Architect"
            // "Electrician"
            // "Client"
            // "Channel Partner"

            $exhibitionJsonArray = json_decode($request->exhibition_json_array, true);
            $ids = [];
            foreach ($exhibitionJsonArray as $keyI => $valueI) {
                $ExhibitionSalesPerson = ExhibitionSalesPersons::where('user_id', Auth::user()->id)->where('exhibition_id', $valueI['exhibition_id'])->first();
                if ($ExhibitionSalesPerson) {

                    $Exhibition = Exhibition::find($valueI['exhibition_id']);
                    if ($Exhibition && $Exhibition->status == 1) {

                        $ExhibitionInquiry = new ExhibitionInquiry();
                        $ExhibitionInquiry->user_id = Auth::user()->id;
                        $ExhibitionInquiry->exhibition_id = $valueI['exhibition_id'];
                        $ExhibitionInquiry->type = $valueI['type'];
                        $ExhibitionInquiry->phone_number = $valueI['phone_number'];
                        $ExhibitionInquiry->email = $valueI['email'];
                        $ExhibitionInquiry->first_name = $valueI['first_name'];
                        $ExhibitionInquiry->last_name = $valueI['last_name'];
                        $ExhibitionInquiry->city_id = $valueI['city_id'];
                        $ExhibitionInquiry->address_line1 = $valueI['address_line1'];
                        $ExhibitionInquiry->address_line2 = isset($valueI['address_line2']) ? $valueI['address_line2'] : '';
                        $ExhibitionInquiry->plan_type = $valueI['plan_type'];
                        $ExhibitionInquiry->stage_of_site = $valueI['stage_of_site'];
                        $ExhibitionInquiry->source = $valueI['source'];
                        $ExhibitionInquiry->remark = isset($valueI['remark']) ? $valueI['remark'] : '';
                        $ExhibitionInquiry->firm_name = isset($valueI['firm_name']) ? $valueI['firm_name'] : '';
                        $ExhibitionInquiry->save();

                        if($ExhibitionInquiry){

                            if ($valueI['type'] == "Architect") {

                                $User = User::where('phone_number', $ExhibitionInquiry->phone_number)->first();
                                if ($User) {

                                    $UserUpdate = new UserNotes();
                                    $UserUpdate->user_id = $User->id;
                                    $UserUpdate->note = 'Visited Exhibition '.$Exhibition->name.'';
                                    $UserUpdate->note_type = "Note";
                                    $UserUpdate->note_title = "Note";
                                    $UserUpdate->entryby = Auth::user()->id;
                                    $UserUpdate->entryip = $request->ip();
                                    $UserUpdate->updateby = Auth::user()->id;
                                    $UserUpdate->updateip = $request->ip();
                                    $UserUpdate->save();
                                } else {
                                    $CityList = CityList::find($ExhibitionInquiry->city_id);
    
                                    $User = new User();
                                    $User->first_name = $ExhibitionInquiry->first_name;
                                    $User->last_name = $ExhibitionInquiry->last_name;
                                    $User->email = $ExhibitionInquiry->email;
                                    $User->dialing_code = "+91";
                                    $User->phone_number = $ExhibitionInquiry->phone_number;
                                    $User->ctc = 0;
                                    $User->address_line1 = $ExhibitionInquiry->address_line1;
                                    $User->address_line2 = $ExhibitionInquiry->address_line2;
                                    $User->pincode = "";
                                    $User->avatar = "";
                                    $User->country_id = $CityList->country_id;
                                    $User->state_id = $CityList->state_id;
                                    $User->city_id = $CityList->id;
                                    $User->company_id = 1;
                                    $User->type = 201;
                                    $User->status = 1;
                                    $User->reference_type = 0;
                                    $User->reference_id = 0;
                                    $User->last_active_date_time = date('Y-m-d H:i:s');
                                    $User->last_login_date_time = date('Y-m-d H:i:s');
                                    $User->save();
    
                                    $Architect = new Architect();
                                    $Architect->user_id = $User->id;
                                    $Architect->type = 201;
                                    $Architect->firm_name = $ExhibitionInquiry->firm_name;
                                    $Architect->source_type = "exhibition-9";
                                    $Architect->source_type_value = $ExhibitionInquiry->exhibition_id;
                                    $Architect->added_by = Auth::user()->id;
                                    $Architect->save();
    
                                    if($User && $Architect)
                                    {
                                        $User->reference_type = 'architect';
                                        $User->reference_id = $Architect->id;
                                        $User->save();
                                        
                                        $user_log = new UserLog();
                                        $user_log->user_id = Auth::user()->id;
                                        $user_log->log_type = "ARCHITECT-LOG";
                                        $user_log->field_name = '';
                                        $user_log->old_value = '';
                                        $user_log->new_value = '';
                                        $user_log->reference_type = "Architect";
                                        $user_log->reference_id = $User->id;
                                        $user_log->transaction_type = "Architect Create Threw Exhibition";
                                        $user_log->description = 'New Architect Created Threw Exhibition';
                                        $user_log->source = $request->app_source;
                                        $user_log->entryby = Auth::user()->id;
                                        $user_log->entryip = $request->ip();
                                        $user_log->save();

                                        $UserUpdate = new UserNotes();
                                        $UserUpdate->user_id = $User->id;
                                        $UserUpdate->note = 'Visited Exhibition '.$Exhibition->name.'';
                                        $UserUpdate->note_type = "Note";
                                        $UserUpdate->note_title = "Note";
                                        $UserUpdate->entryby = Auth::user()->id;
                                        $UserUpdate->entryip = $request->ip();
                                        $UserUpdate->updateby = Auth::user()->id;
                                        $UserUpdate->updateip = $request->ip();
                                        $UserUpdate->save();
                                        
                                        if ($request->user_id == 0) {
                                            $UserContact = new UserContact();
                                            $UserContact->user_id = $User->id;
                                            $UserContact->contact_tag_id = 0;
                                            $UserContact->first_name = $User->first_name;
                                            $UserContact->last_name = $User->last_name;
                                            $UserContact->phone_number = $User->phone_number;
                                            $UserContact->alernate_phone_number = 0;
                                            $UserContact->email = $User->email;
                                            $UserContact->type = 201;
                                            $UserContact->type_detail = "user-201-".$User->id;
    
                                            $UserContact->save();
    
                                            if($UserContact)
                                            {
                                                $user_update = User::find($User->id);
                                                $user_update->main_contact_id = $UserContact->id;
                                                $user_update->save();
                                            }
                                        }
                                    }
                                }


                                
                                $whatsapp_controller = new WhatsappApiContoller;
                                $perameater_request = new Request();
                                $perameater_request['q_whatsapp_massage_mobileno'] = $valueI['phone_number'];
					            $perameater_request['q_whatsapp_massage_template'] = 'abid_exhibition_thankyou_message';
					            $perameater_request['q_whatsapp_massage_attechment'] = '';
					            $perameater_request['q_broadcast_name'] = $valueI['first_name'] . ' ' . $valueI['last_name'];
					            $perameater_request['q_whatsapp_massage_parameters'] = array();

                                // $perameater_request['q_whatsapp_massage_mobileno'] = "7984951484";
                                // $perameater_request['q_whatsapp_massage_template'] = 'exhibitioncustomer';
                                // $perameater_request['q_whatsapp_massage_template'] = 'jito_exhibition';
                                // $perameater_request['q_whatsapp_massage_template'] = 'jaipur_architect_inquiry1';
                                // $perameater_request['q_whatsapp_massage_template'] = 'aiexhibitionthankyoumessage';
                                
                                $whatsapp_controller->sendTemplateMessage($perameater_request);
    
                            } else if ($valueI['type'] == "Client") {
    
                                $Lead = Lead::where('phone_number', $ExhibitionInquiry->phone_number)->first();
                                if ($Lead) {
                                    $Notes = new LeadUpdate();
                                    $Notes->user_id = Auth::user()->id;
                                    $Notes->lead_id = $Lead->id;
                                    $Notes->message = 'Visited Exhibition '.$Exhibition->name.'';
                                    $Notes->task = 'Note';
                                    $Notes->task_title = 'Note';
                                    $Notes->save();
                                } else {
                                    $Lead = new Lead();
                                    $Lead->user_id = Auth::user()->id;
                                    $city_id = CityList::find($ExhibitionInquiry->city_id);
                                    $User_id = User::select('id')->where('type', 9)->where('status', 1)->get();
                                    $Telesales = TeleSales::query()->whereIn('user_id', $User_id)->whereRaw("FIND_IN_SET('$city_id->state_id', states)")->first();
                                    if ($Telesales) {
                                        $Lead->assigned_to = $Telesales->id;
                                    }else{
                                        $Lead->assigned_to = Auth::user()->id;
                                    }
                                    $Lead->first_name = $ExhibitionInquiry->first_name . ' ' . $ExhibitionInquiry->last_name;
                                    $Lead->last_name = " ";
                                    $Lead->email = $ExhibitionInquiry->email;
                                    $Lead->phone_number = $ExhibitionInquiry->phone_number;


                                    $Lead->house_no = "";
                                    $Lead->addressline1 = $ExhibitionInquiry->address_line1;
                                    $Lead->addressline2 = $ExhibitionInquiry->address_line2;
                                    $Lead->area = "";
                                    $Lead->pincode = "";
                                    $Lead->city_id = $ExhibitionInquiry->city_id;

                                    $site_stage_id = 0;
                                    $chkStageOfSite = CRMSettingStageOfSite::select('id', 'name');
                                    $chkStageOfSite->where('name', $ExhibitionInquiry->stage_of_site);
                                    $chkStageOfSite = $chkStageOfSite->first();
                                    if ($chkStageOfSite) {
                                        $site_stage_id = $chkStageOfSite->id;
                                    } else {
                                        $stageofsite = new CRMSettingStageOfSite();
                                        $stageofsite->name = $ExhibitionInquiry->stage_of_site;
                                        $stageofsite->status = 1;
                                        $stageofsite->save();
                                        if($stageofsite){
                                            $site_stage_id = $stageofsite->id;
                                        }
                                    }
                                    $Lead->site_stage = $site_stage_id;

                                    $Lead->source_type = "exhibition-9";
                                    $Lead->source = $ExhibitionInquiry->exhibition_id;

                                    $Lead->customer_id = 0;
                                    $Lead->status = 1;
                                    $Lead->sub_status = 0;
                                    $Lead->created_by = Auth::user()->id;
                                    $Lead->updated_by = Auth::user()->id;
                                    $Lead->entryip = $request->ip();
                                    $Lead->entry_source = $request->app_source;

                                    $Lead->save();

                                    if($Lead){
                                        $Notes = new LeadUpdate();
                                        $Notes->user_id = Auth::user()->id;
                                        $Notes->lead_id = $Lead->id;
                                        $Notes->message = 'Visited Exhibition '.$Exhibition->name.'';
                                        $Notes->task = 'Note';
                                        $Notes->task_title = 'Note';
                                        $Notes->save();


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

                                        $Lead_main_contact = Lead::find($LeadContact->lead_id);
                                        $Lead_main_contact->main_contact_id = $LeadContact->id;
                                        $Lead_main_contact->save();
                                        
                                        $LeadSource1 = new LeadSource();
                                        $LeadSource1->lead_id = $Lead->id;
                                        $LeadSource1->source_type = "exhibition-9";
                                        $LeadSource1->source = $ExhibitionInquiry->exhibition_id;
                                        $LeadSource1->is_main = 1;
                                        $LeadSource1->save();
                                    }
                                        
                                }
                                 
                                $whatsapp_controller = new WhatsappApiContoller;
                                $perameater_request = new Request();
                                $perameater_request['q_whatsapp_massage_mobileno'] = $valueI['phone_number'];
					            $perameater_request['q_whatsapp_massage_template'] = 'abid_exhibition_thankyou_message';
					            $perameater_request['q_whatsapp_massage_attechment'] = '';
					            $perameater_request['q_broadcast_name'] = $valueI['first_name'] . ' ' . $valueI['last_name'];
					            $perameater_request['q_whatsapp_massage_parameters'] = array();

                                $whatsapp_controller->sendTemplateMessage($perameater_request);
                            }
                        }

                        $ids[] = $ExhibitionInquiry->id;
                    }
                }
            }

            $response = successRes("Successfullly added inquiry");
            $response['ids'] = $ids;
        }


        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function createArchitectUsingExhibition(Request $request) {

        $Exhibition = ExhibitionInquiry::query();
        $Exhibition->where('type', 'Architect');
        $Exhibition = $Exhibition->get();

        $Temp = "";
        foreach ($Exhibition as $key => $value) {
            $User = User::query();
            $User->where('phone_number', $value['phone_number']);
            $User = $User->first();

            if($User) {
                $UserUpdate = new UserNotes();
                $UserUpdate->user_id = $User->id;
                if($value['exhibition_id'] == 1) {
                    $UserUpdate->note = 'Visited Exhibition Abid Kolkata 2023';
                } else if($value['exhibition_id'] == 2) {
                    $UserUpdate->note = 'Visited Exhibition JITO ABD';
                } else if($value['exhibition_id'] == 3) {
                    $UserUpdate->note = 'Visited Exhibition IIID Jaipur Exhibition';                    
                } else if($value['exhibition_id'] == 4) {
                    $UserUpdate->note = 'Visited Exhibition V Light Rajkot 2023';                    
                } else if($value['exhibition_id'] == 5) {
                    $UserUpdate->note = 'Visited Exhibition RAF Jaipur 2023';                                        
                } else if($value['exhibition_id'] == 6) {
                    $UserUpdate->note = 'Visited Exhibition A&I Pune 2024';                                        
                }
                $UserUpdate->note_type = "Note";
                $UserUpdate->note_title = "Note";
                $UserUpdate->entryby = Auth::user()->id;
                $UserUpdate->entryip = $request->ip();
                $UserUpdate->updateby = Auth::user()->id;
                $UserUpdate->updateip = $request->ip();
                $UserUpdate->save();
            } else {
                $CityList = CityList::find($value['city_id']);
                $User = new User();
                $User->first_name = $value['first_name'];
                $User->last_name = $value['last_name'];
                $User->email = $value['email'];
                $User->dialing_code = "+91";
                $User->phone_number = str_replace(' ', '', $value['phone_number']);
                $User->ctc = 0;
                $User->address_line1 = $value['address_line1'];
                $User->address_line2 = $value['address_line2'];
                $User->pincode = "";
                $User->avatar = "";
                $User->country_id = $CityList->country_id;
                $User->state_id = $CityList->state_id;
                $User->city_id = $CityList->id;
                $User->company_id = 1;
                $User->type = 201;
                $User->status = 1;
                $User->reference_type = 0;
                $User->reference_id = 0;
                $User->last_active_date_time = date('Y-m-d H:i:s');
                $User->last_login_date_time = date('Y-m-d H:i:s');
                $User->save();

                $Architect = new Architect();
                $Architect->user_id = $User->id;
                $Architect->type = 201;
                if($value['exhibition_id'] == 1) {
                    $Architect->sale_person_id = 37;
                } else if($value['exhibition_id'] == 2) {
                    $Architect->sale_person_id = 29;
                } else if($value['exhibition_id'] == 3) {
                    $Architect->sale_person_id = 22;
                } else if($value['exhibition_id'] == 4) {
                    $Architect->sale_person_id = 1751;
                } else if($value['exhibition_id'] == 5) {
                    $Architect->sale_person_id = 22;
                } else if($value['exhibition_id'] == 6) {
                    $Architect->sale_person_id = 34;
                }
                $Architect->firm_name = $value['firm_name'];
                $Architect->source_type = "exhibition-9";
                $Architect->source_type_value = $value['exhibition_id'];
                $Architect->added_by = Auth::user()->id;
                $Architect->save();

                if($User && $Architect)
                {
                    $User->reference_type = 'architect';
                    $User->reference_id = $Architect->id;
                    $User->save();

                    $user_log = new UserLog();
                    $user_log->user_id = Auth::user()->id;
                    $user_log->log_type = "ARCHITECT-LOG";
                    $user_log->field_name = '';
                    $user_log->old_value = '';
                    $user_log->new_value = '';
                    $user_log->reference_type = "Architect";
                    $user_log->reference_id = $User->id;
                    $user_log->transaction_type = "Architect Create Threw Exhibition";
                    $user_log->description = 'New Architect Created Threw Exhibition';
                    $user_log->source = $request->app_source;
                    $user_log->entryby = Auth::user()->id;
                    $user_log->entryip = $request->ip();
                    $user_log->save();
                    
                    $UserUpdate = new UserNotes();
                    $UserUpdate->user_id = $User->id;
                    if($value['exhibition_id'] == 1) {
                        $UserUpdate->note = 'Visited Exhibition Abid Kolkata 2023';
                    } else if($value['exhibition_id'] == 2) {
                        $UserUpdate->note = 'Visited Exhibition JITO ABD';
                    } else if($value['exhibition_id'] == 3) {
                        $UserUpdate->note = 'Visited Exhibition IIID Jaipur Exhibition';                    
                    } else if($value['exhibition_id'] == 4) {
                        $UserUpdate->note = 'Visited Exhibition V Light Rajkot 2023';                    
                    } else if($value['exhibition_id'] == 5) {
                        $UserUpdate->note = 'Visited Exhibition RAF Jaipur 2023';                                        
                    } else if($value['exhibition_id'] == 6) {
                        $UserUpdate->note = 'Visited Exhibition A&I Pune 2024';                                        
                    }
                    $UserUpdate->note_type = "Note";
                    $UserUpdate->note_title = "Note";
                    $UserUpdate->entryby = Auth::user()->id;
                    $UserUpdate->entryip = $request->ip();
                    $UserUpdate->updateby = Auth::user()->id;
                    $UserUpdate->updateip = $request->ip();
                    $UserUpdate->save();
                    
                    if ($request->user_id == 0) {
                        $UserContact = new UserContact();
                        $UserContact->user_id = $User->id;
                        $UserContact->contact_tag_id = 0;
                        $UserContact->first_name = $User->first_name;
                        $UserContact->last_name = $User->last_name;
                        $UserContact->phone_number = str_replace(' ', '', $User->phone_number);
                        $UserContact->alernate_phone_number = 0;
                        $UserContact->email = $User->email;
                        $UserContact->type = 201;
                        $UserContact->type_detail = "user-201-".$User->id;

                        $UserContact->save();

                        if($UserContact)
                        {
                            $user_update = User::find($User->id);
                            $user_update->main_contact_id = $UserContact->id;
                            $user_update->save();
                        }
                    }
                }
            }
        }

        $response = successRes();
        // $response['data'] = $Temp;
        return response()->json($response)->header('Content-Type', 'application/json');
    }
}



