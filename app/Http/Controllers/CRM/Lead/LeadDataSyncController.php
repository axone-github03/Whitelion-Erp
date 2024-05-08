<?php

namespace App\Http\Controllers\CRM\Lead;

use App\Models\Lead;
use App\Models\User;
use App\Models\Inquiry;
use App\Models\LeadFile;
use App\Models\InquiryLog;
use App\Models\LeadSource;
use App\Models\LeadUpdate;
use App\Models\LeadClosing;
use App\Models\LeadStatusUpdate;
use App\Models\LeadContact;
use App\Models\LeadTimeline;
use App\Models\Wlmst_Client;
use App\Models\LeadQuestionAnswer;
use Illuminate\Http\Request;
use App\Models\InquiryUpdate;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\CRMSettingStageOfSite;
use App\Models\InquiryQuestionAnswer;
use App\Models\Wltrn_Quotation;

date_default_timezone_set('Asia/Kolkata');
class LeadDataSyncController extends Controller
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
    function convertInquiryToLead(Request $request)
    {
        // SELECT
        // i.*,
        // CONCAT(assign.first_name,' ',assign.last_name) AS assign_name,
        // CONCAT(architect.first_name,' ',architect.last_name) AS architect_name,
        // CONCAT(electrician.first_name,' ',electrician.last_name) AS electrician_name,
        // city.name AS city_name
        // FROM inquiry i
        // LEFT JOIN users assign ON assign.id = i.assigned_to
        // LEFT JOIN users architect ON architect.id = i.architect
        // LEFT JOIN users electrician ON electrician.id = i.electrician
        // LEFT JOIN city_list city ON city.id = i.city_id
        // WHERE i.city_id = 405 AND i.assigned_to = 4190;

        $selectColumns = [
            'inquiry.id',

            'inquiry.assigned_to',
            'assigned_to.first_name as assigned_to_first_name',
            'assigned_to.last_name as assigned_to_last_name',

            'inquiry.architect',
            'architect.type as architect_type',
            'architect.first_name as architect_first_name',
            'architect.last_name as architect_last_name',
            'architect.phone_number as architect_phone_number',

            'inquiry.electrician',
            'electrician.type as electrician_type',
            'electrician.first_name as electrician_first_name',
            'electrician.last_name as electrician_last_name',
            'electrician.phone_number as electrician_phone_number',

            'inquiry.first_name',
            'inquiry.last_name',
            'inquiry.phone_number',
            'inquiry.phone_number2',
            'inquiry.house_no',
            'inquiry.society_name',
            'inquiry.area',
            'inquiry.pincode',

            'inquiry.city_id',
            'city_list.name as city_list_name',

            'inquiry.status',
            'inquiry.source_type_lable',
            'inquiry.source_type',
            'inquiry.source_type_value',

            'inquiry.follow_up_type',
            'inquiry.follow_up_date_time',
            'inquiry.closing_date_time',
            'inquiry.closing_history',
            'inquiry.answer_date_time',
            'inquiry.material_sent_date_time',
            'inquiry.stage_of_site_date_time',
            'inquiry.quotation',
            'inquiry.quotation_amount',
            'inquiry.stage_of_site',
            'inquiry.required_for_property',
            'inquiry.site_photos',
            'inquiry.changes_of_closing_order',
            'inquiry.billing_amount',
            'inquiry.billing_invoice',
            'inquiry.is_verified',
            'inquiry.total_point',
            'inquiry.is_point_calculated',
            'inquiry.is_claimed',
            'inquiry.material_sent_channel_partner',
            'inquiry.is_for_manager',
            'inquiry.is_for_tele_sale',
            'inquiry.is_from_mobile',
            'inquiry.is_predication_sure',
            'inquiry.monday_dot_com_id',
            'inquiry.last_update',
            'inquiry.update_count',
            'inquiry.created_at',
            'inquiry.updated_at',
            'inquiry.source_type_lable_1',
            'inquiry.source_type_1',
            'inquiry.source_type_value_1',
            'inquiry.source_type_lable_2',
            'inquiry.source_type_2',
            'inquiry.source_type_value_2',
            'inquiry.source_type_lable_3',
            'inquiry.source_type_3',
            'inquiry.source_type_value_3',
            'inquiry.source_type_lable_4',
            'inquiry.source_type_4',
            'inquiry.source_type_value_4',
            'inquiry.claimed_date_time',

            'created_by.first_name as created_by_first_name',
            'created_by.last_name as created_by_last_name',
            'created_by.type as created_by_type',
            'created_by.id as created_by_user_id',
        ];

        $query = Inquiry::query();
        $query->select($selectColumns);
        $query->leftJoin('users as created_by', 'created_by.id', '=', 'inquiry.user_id');
        $query->leftJoin('users as assigned_to', 'assigned_to.id', '=', 'inquiry.assigned_to');
        $query->leftJoin('users as architect', 'architect.id', '=', 'inquiry.architect');
        $query->leftJoin('users as electrician', 'electrician.id', '=', 'inquiry.electrician');
        $query->leftJoin('city_list', 'city_list.id', '=', 'inquiry.city_id');

        // $query->where('inquiry.city_id', '!=', 405); DONE
        // $query->where('inquiry.assigned_to', 4190); DONE
        // $query->where('inquiry.assigned_to', 4); DONE
        // $query->where('inquiry.assigned_to', 4344); DONE
        // $query->where('inquiry.assigned_to', 37); DONE
        // $query->where('inquiry.assigned_to', 5262); DONE
        // $query->whereIn('inquiry.assigned_to', [4425,1233,3352]); DONE
        // $query->whereIn('inquiry.assigned_to', [29]); DONE
        // $query->whereIn('inquiry.assigned_to', [36]); DONE
        // $query->whereIn('inquiry.assigned_to', [3245]); DONE
        // $query->whereIn('inquiry.assigned_to', [22]); DONE
        // $query->whereIn('inquiry.assigned_to', [38,21,5263]);
        // $query->whereIn('inquiry.assigned_to', [34]); DONE
        // $query->whereIn('inquiry.assigned_to', [2,14,28,30,35,2891,4031,4191,3831]); DONE
        // $query->whereIn('inquiry.assigned_to', [0]);
        $query->whereIn('inquiry.id', [10,9437,12151,12409,13221,13519,14166,14226,14832,15551,15777,15903,16099,16272,16357,16358,16383,16391,16521,16523,16527,16540,16542]);

        $list_inquiry = $query->get();

        $is_success = 0;
        $is_error = '';

        foreach ($list_inquiry as $key => $value) {
            try {
                $wlmst_client = new Wlmst_Client();
                $wlmst_client->name = $value->first_name . ' ' . $value->last_name;
                $wlmst_client->email = $value->first_name . '@gmail.com';
                if (strlen($value->phone_number) > 14) {
                    $wlmst_client->mobile = substr($value->phone_number, 0, 14);
                } else {
                    $wlmst_client->mobile = $value->phone_number;
                }
                $wlmst_client->address = $value->house_no . ', ' . $value->society_name . ', ' . $value->area . ', ' . $value->city_list_name . ', ' . $value->pincode;
                $wlmst_client->isactive = 1;
                $wlmst_client->remark = 0;
                $wlmst_client->save();

                // ------------ ADD OR CREATE STAGE OF SITE START ------------
                $al_stageofsite = CRMSettingStageOfSite::query();
                $al_stageofsite->where('name', $value->stage_of_site);
                $al_stageofsite = $al_stageofsite->first();

                if ($al_stageofsite) {
                    $Stage_Of_Site = $al_stageofsite->id;
                } else {
                    $stageofsite = new CRMSettingStageOfSite();
                    $stageofsite->name = $value->stage_of_site;
                    $stageofsite->status = 1;
                    $stageofsite->save();

                    if ($stageofsite) {
                        $Stage_Of_Site = $stageofsite->id;
                    } else {
                        $response = errorRes('stage of site not save', 400);
                        return response()
                            ->json($response)
                            ->header('Content-Type', 'application/json');
                    }
                }
                // ------------ ADD OR CREATE STAGE OF SITE END ------------

                $Lead = new Lead();
                $Lead->customer_id = $wlmst_client->id;

                $Lead->first_name = $value->first_name . ' ' . $value->last_name;
                $Lead->last_name = '';
                $Lead->email = ' - ';
                $Lead->phone_number = $value->phone_number;

                /*PPP */
                if ($value->status == 1) {
                    $Lead->status = 1;
                } elseif ($value->status == 2) {
                    $Lead->status = 3;
                } elseif ($value->status == 3 || $value->status == 4) {
                    $Lead->status = 4;
                } elseif ($value->status == 5) {
                    $Lead->status = 100;
                } elseif ($value->status == 6) {
                    $Lead->status = 101;
                }elseif ($value->status == 8) {
                    $Lead->status = 100;
                } elseif ($value->status == 7 || $value->status == 13) {
                    $Lead->status = 102;
                } elseif ($value->status == 9 || $value->status == 11 || $value->status == 14 || $value->status == 12 || $value->status == 10) {
                    $Lead->status = 103;
                } elseif ($value->status == 101 || $value->status == 102) {
                    $Lead->status = 104;
                }
                $Lead->sub_status = 0;
                /*PPP */

                $Lead->house_no = $value->house_no;
                $Lead->addressline1 = $value->society_name;
                $Lead->addressline2 = $value->city_list_name;
                $Lead->area = $value->area;
                $Lead->pincode = $value->pincode;
                $Lead->city_id = $value->city_id;
                $Lead->update_count = $value->update_count;

                $Lead->meeting_house_no = $value->house_no;
                $Lead->meeting_addressline1 = $value->society_name;
                $Lead->meeting_addressline2 = $value->city_list_name;
                $Lead->meeting_area = $value->area;
                $Lead->meeting_pincode = $value->pincode;
                $Lead->meeting_city_id = $value->city_id;

                $Lead->site_stage = $Stage_Of_Site;
                $Lead->site_type = 0;

                $Lead->bhk = 0;
                $Lead->sq_foot = 0;
                $Lead->want_to_cover = 0;
                $Lead->source_type = $value->source_type;
                $Lead->source = $value->source_type_value;
                $Lead->budget = 0;
                $Lead->closing_date_time = $value->closing_date_time;
                $Lead->competitor = 0;
                $Lead->assigned_to = $value->assigned_to;
                $Lead->created_by = $value->created_by_user_id;
                $Lead->updated_by = $value->created_by_user_id;
                $Lead->architect = $value->architect;
                $Lead->electrician = $value->electrician;
                $Lead->user_id = $value->created_by_user_id;
                $Lead->inquiry_id = $value->id;
                $Lead->quotation_file = $value->quotation;

                if ($value->quotation_amount != null || $value->quotation_amount != '') {
                    $quotation_amount = $value->quotation_amount;
                } else {
                    $quotation_amount = 0;
                }
                $Lead->quotation = $quotation_amount;

                if ($value->status == 1 || $value->status == 2 || $value->status == 3 || $value->status == 4) {
                    if ($value->quotation_amount != null && (int) $value->quotation_amount > 0 && $value->quotation_amount != '') {
                        $Lead->is_deal = 1;
                        $Lead->status = 101;
                    } else {
                        $Lead->is_deal = 0;
                    }
                } else {
                    if (($value->quotation_amount != null && (int) $value->quotation_amount > 0) || $value->quotation_amount != '') {
                        $Lead->is_deal = 1;
                    } else {
                        $Lead->is_deal = 1;
                    }
                }
                $Lead->updated_at = $value->updated_at;
                $Lead->created_at = $value->created_at;
                $Lead->entryip = $request->ip();
                $Lead->entry_source = 'WEB';
                $Lead->updateip = $request->ip();
                $Lead->update_source = 'WEB';

                $Lead->timestamps = false;
                $Lead->save();

                if ($Lead) {
                    try {
                        // $LeadCall = new LeadCall();
                        // $LeadContact = new LeadContact();
                        // $LeadFile = new LeadFile();
                        // $LeadMeeting = new LeadMeeting();
                        // $LeadTask = new LeadTask();
                        // $LeadTimeline = new LeadTimeline();
                        // $LeadUpdate = new LeadUpdate();

                        $Inquiry = Inquiry::find($value->id);
                        $Inquiry->lead_id = $Lead->id;
                        $Inquiry->save();

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

                        if ($Lead->status == 103) {
                            $LeadStatusUpdate = new LeadStatusUpdate();
                            $LeadStatusUpdate->lead_id = $Lead->id;
                            $LeadStatusUpdate->old_status = 0;
                            $LeadStatusUpdate->new_status = $Lead->status;
                            $LeadStatusUpdate->remark = 'Transfer To Inquiry';

                            $LeadStatusUpdate->created_at = $value->material_sent_date_time;
                            $LeadStatusUpdate->entryby = $Lead->created_by;
                            $LeadStatusUpdate->entryip = $request->ip();

                            $LeadStatusUpdate->updated_at = $value->material_sent_date_time;
                            $LeadStatusUpdate->updateby = $Lead->created_by;
                            $LeadStatusUpdate->updateip = $request->ip();
                            $LeadStatusUpdate->timestamps = false;
                            $LeadStatusUpdate->save();
                        }

                        // ADD LEAD UPDATE START
                        try {
                            $Lead_Update = Lead::find($Lead->id);
                            $Lead_Update->main_contact_id = $LeadContact->id;

                            if ($value->answer_date_time != null && $value->answer_date_time != '' && $value->answer_date_time != 0) {
                                $Lead_Update->updated_at = $value->answer_date_time;
                                $Lead_Update->timestamps = false;
                            }

                            $Lead_Update->save();
                        } catch (\Exception $e) {
                            $is_success = 107;
                            $is_error .= 'Lead ID : ' . $Lead->id . ' Inquiry ID : ' . $value->id . ' = ERRPR(387) - ' . $e->getMessage() . ' </br>';
                            $response = errorRes($e->getMessage(), 400);
                            $response['lead_data'] = $Lead;
                            $response['inquiry_data'] = $value;
                        }
                        // ADD LEAD UPDATE END

                        // ADD CLOSING DATE TO LEAD CLOSING DATE START
                        try {
                            if ($value->closing_history != 0 || $value->closing_history != '' || $value->closing_history != null) {
                                foreach (json_decode($value->closing_history) as $key_closing => $closing_val) {
                                    $LeadClosing = new LeadClosing();
                                    $LeadClosing->lead_id = $Lead->id;
                                    $LeadClosing->closing_date = $closing_val->closing_date_time;
                                    $LeadClosing->entryby = 0;
                                    $LeadClosing->entryip = 0;
                                    $LeadClosing->updateby = 0;
                                    $LeadClosing->updateip = 0;
                                    $LeadClosing->created_at = $closing_val->created_at;
                                    $LeadClosing->updated_at = $closing_val->created_at;
                                    $LeadClosing->timestamps = false;
                                    $LeadClosing->save();
                                }
                            }
                        } catch (\Exception $e) {
                            $is_success = 105;
                            $is_error .= 'Lead ID : ' . $Lead->id . ' Inquiry ID : ' . $value->id . ' = ERRPR(3316) - ' . $e->getMessage() . ' </br>';
                            $response = errorRes($e->getMessage(), 400);
                            $response['lead_data'] = $Lead;
                            $response['inquiry_data'] = $value;
                        }
                        // ADD CLOSING DATE TO LEAD CLOSING DATE END

                        // ADD ARCHITECT CONTACT START
                        try {
                            if ($value->architect != 0) {
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
                                    $LeadContact_arc->type_detail = 'user-' . $Architect->type . '-' . $Lead->architect;
                                    $LeadContact_arc->save();
                                }
                            }
                        } catch (\Exception $e) {
                            $is_success = 105;
                            $is_error .= 'Lead ID : ' . $Lead->id . ' Inquiry ID : ' . $value->id . ' = ERRPR(3344) - ' . $e->getMessage() . ' </br>';
                            $response = errorRes($e->getMessage(), 400);
                            $response['lead_data'] = $Lead;
                            $response['inquiry_data'] = $value;
                        }
                        // ADD ARCHITECT CONTACT END

                        // ADD ELECTRICIAN CONTACT START
                        try {
                            if ($value->electrician != 0) {
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
                                    $LeadContact_ele->type_detail = 'user-' . $Electrician->type . '-' . $Electrician->id;
                                    $LeadContact_ele->save();
                                }
                            }
                        } catch (\Exception $e) {
                            $is_success = 105;
                            $is_error .= 'Lead ID : ' . $Lead->id . ' Inquiry ID : ' . $value->id . ' = ERRPR(3372) - ' . $e->getMessage() . ' </br>';
                            $response = errorRes($e->getMessage(), 400);
                            $response['lead_data'] = $Lead;
                            $response['inquiry_data'] = $value;
                        }
                        // ADD ELECTRICIAN CONTACT END

                        // ADD SOURCE-0 TO CONTACT START
                        try {
                            if (($value->source_type != null && $value->source_type == 'user-201') || $value->source_type == 'user-202' || $value->source_type == 'user-301' || $value->source_type == 'user-302' || $value->source_type == 'user-101' || $value->source_type == 'user-102' || $value->source_type == 'user-103' || $value->source_type == 'user-104') {
                                if ($value->source_type_value != 0 || $value->source_type_value != null || $value->source_type_value != '') {
                                    if ($value->source_type_value != $Lead->electrician && $value->source_type_value != $Lead->architect) {
                                        $Source_1 = User::where('id', $value->source_type_value)->first();

                                        if ($Source_1) {
                                            if ($Source_1->id != $Lead->electrician && $Source_1->id != $Lead->architect) {
                                                $LeadContact_s1 = new LeadContact();
                                                $LeadContact_s1->lead_id = $Lead->id;
                                                $LeadContact_s1->contact_tag_id = 0;
                                                $LeadContact_s1->first_name = $Source_1->first_name;
                                                $LeadContact_s1->last_name = $Source_1->last_name;
                                                $LeadContact_s1->phone_number = $Source_1->phone_number;
                                                $LeadContact_s1->alernate_phone_number = 0;
                                                $LeadContact_s1->email = $Source_1->email;
                                                $LeadContact_s1->type = $Source_1->type;
                                                $LeadContact_s1->type_detail = 'user-' . $Source_1->type . '-' . $Source_1->id;
                                                $LeadContact_s1->save();
                                            }
                                        }
                                    }
                                    $LeadSource1 = new LeadSource();
                                    $LeadSource1->lead_id = $Lead->id;
                                    $LeadSource1->is_main = 1;
                                    $LeadSource1->source_type = $value->source_type;
                                    $LeadSource1->source = $value->source_type_value;
                                    $LeadSource1->save();
                                }
                            } else {
                                if ($value->source_type_value != 0 || $value->source_type_value != null || $value->source_type_value != '') {
                                    $LeadSource1 = new LeadSource();
                                    $LeadSource1->lead_id = $Lead->id;
                                    $LeadSource1->is_main = 1;
                                    $LeadSource1->source_type = $value->source_type;
                                    $LeadSource1->source = $value->source_type_value;
                                    $LeadSource1->save();
                                }
                            }
                        } catch (\Exception $e) {
                            $is_success = 101;
                            $is_error .= 'Lead ID : ' . $Lead->id . ' Inquiry ID : ' . $value->id . ' = ERRPR(3422) - ' . $e->getMessage() . ' </br>';
                            $response = errorRes($e->getMessage(), 400);
                            $response['lead_data'] = $Lead;
                            $response['inquiry_data'] = $value;
                        }
                        // ADD SOURCE-0 TO CONTACT END

                        // ADD SOURCE-1 TO CONTACT START
                        try {
                            if (($value->source_type_1 != null && $value->source_type_1 == 'user-201') || $value->source_type_1 == 'user-202' || $value->source_type_1 == 'user-301' || $value->source_type_1 == 'user-302' || $value->source_type_1 == 'user-101' || $value->source_type_1 == 'user-102' || $value->source_type_1 == 'user-103' || $value->source_type_1 == 'user-104') {
                                if ($value->source_type_value_1 != 0 || $value->source_type_value_1 != null || $value->source_type_value_1 != '') {
                                    if ($value->source_type_value_1 != $Lead->electrician && $value->source_type_value_1 != $Lead->architect) {
                                        $Source_1 = User::where('id', $value->source_type_value_1)->first();

                                        if ($Source_1) {
                                            if ($Source_1->id != $Lead->electrician && $Source_1->id != $Lead->architect) {
                                                $LeadContact_s1 = new LeadContact();
                                                $LeadContact_s1->lead_id = $Lead->id;
                                                $LeadContact_s1->contact_tag_id = 0;
                                                $LeadContact_s1->first_name = $Source_1->first_name;
                                                $LeadContact_s1->last_name = $Source_1->last_name;
                                                $LeadContact_s1->phone_number = $Source_1->phone_number;
                                                $LeadContact_s1->alernate_phone_number = 0;
                                                $LeadContact_s1->email = $Source_1->email;
                                                $LeadContact_s1->type = $Source_1->type;
                                                $LeadContact_s1->type_detail = 'user-' . $Source_1->type . '-' . $Source_1->id;
                                                $LeadContact_s1->save();
                                            }
                                        }
                                    }
                                    $LeadSource1 = new LeadSource();
                                    $LeadSource1->lead_id = $Lead->id;
                                    $LeadSource1->is_main = 0;
                                    $LeadSource1->source_type = $value->source_type_1;
                                    $LeadSource1->source = $value->source_type_value_1;
                                    $LeadSource1->save();
                                }
                            } else {
                                if ($value->source_type_value_1 != 0 || $value->source_type_value_1 != null || $value->source_type_value_1 != '') {
                                    $LeadSource1 = new LeadSource();
                                    $LeadSource1->lead_id = $Lead->id;
                                    $LeadSource1->is_main = 0;
                                    $LeadSource1->source_type = $value->source_type_1;
                                    $LeadSource1->source = $value->source_type_value_1;
                                    $LeadSource1->save();
                                }
                            }
                        } catch (\Exception $e) {
                            $is_success = 101;
                            $is_error .= 'Lead ID : ' . $Lead->id . ' Inquiry ID : ' . $value->id . ' = ERRPR(3472) - ' . $e->getMessage() . ' </br>';
                            $response = errorRes($e->getMessage(), 400);
                            $response['lead_data'] = $Lead;
                            $response['inquiry_data'] = $value;
                        }
                        // ADD SOURCE-1 TO CONTACT END

                        // ADD SOURCE-2 TO CONTACT START
                        try {
                            if (($value->source_type_2 != null && $value->source_type_2 == 'user-201') || $value->source_type_2 == 'user-202' || $value->source_type_2 == 'user-301' || $value->source_type_2 == 'user-302' || $value->source_type_2 == 'user-101' || $value->source_type_2 == 'user-102' || $value->source_type_2 == 'user-103' || $value->source_type_2 == 'user-104') {
                                if ($value->source_type_value_2 != 0 || $value->source_type_value_2 != null || $value->source_type_value_2 != '' || $value->source_type_value_2 != ' ') {
                                    if ($value->source_type_value_2 != $Lead->electrician && $value->source_type_value_2 != $Lead->architect) {
                                        $Source_2 = User::where('id', $value->source_type_value_2)->first();
                                        if ($Source_2) {
                                            if ($Source_2->id != $Lead->electrician && $Source_2->id != $Lead->architect) {
                                                $LeadContact_s2 = new LeadContact();
                                                $LeadContact_s2->lead_id = $Lead->id;
                                                $LeadContact_s2->contact_tag_id = 0;
                                                $LeadContact_s2->first_name = $Source_2->first_name;
                                                $LeadContact_s2->last_name = $Source_2->last_name;
                                                $LeadContact_s2->phone_number = $Source_2->phone_number;
                                                $LeadContact_s2->alernate_phone_number = 0;
                                                $LeadContact_s2->email = $Source_2->email;
                                                $LeadContact_s2->type = $Source_2->type;
                                                $LeadContact_s2->type_detail = 'user-' . $Source_2->type . '-' . $Source_2->id;
                                                $LeadContact_s2->save();
                                            }
                                        }
                                    }
                                    $LeadSource2 = new LeadSource();
                                    $LeadSource2->lead_id = $Lead->id;
                                    $LeadSource1->is_main = 1;
                                    $LeadSource2->source_type = $value->source_type_2;
                                    $LeadSource2->source = $value->source_type_value_2;
                                    $LeadSource2->save();
                                }
                            } else {
                                if ($value->source_type_value_2 != 0 || $value->source_type_value_2 != null || $value->source_type_value_2 != '' || $value->source_type_value_2 != ' ') {
                                    $LeadSource2 = new LeadSource();
                                    $LeadSource2->lead_id = $Lead->id;
                                    $LeadSource1->is_main = 1;
                                    $LeadSource2->source_type = $value->source_type_2;
                                    $LeadSource2->source = $value->source_type_value_2;
                                    $LeadSource2->save();
                                }
                            }
                        } catch (\Exception $e) {
                            $is_success = 102;
                            $is_error .= 'Lead ID : ' . $Lead->id . ' Inquiry ID : ' . $value->id . ' = ERRPR(3522) - ' . $e->getMessage() . ' </br>';
                            $response = errorRes($e->getMessage(), 400);
                            $response['lead_data'] = $Lead;
                            $response['inquiry_data'] = $value;
                        }
                        // ADD SOURCE-2 TO CONTACT END

                        // ADD SOURCE-3 TO CONTACT START
                        try {
                            if (($value->source_type_3 != null && $value->source_type_3 == 'user-201') || $value->source_type_3 == 'user-202' || $value->source_type_3 == 'user-301' || $value->source_type_3 == 'user-302' || $value->source_type_3 == 'user-101' || $value->source_type_3 == 'user-102' || $value->source_type_3 == 'user-103' || $value->source_type_3 == 'user-104') {
                                if ($value->source_type_value_3 != 0 || $value->source_type_value_3 != null || $value->source_type_value_3 != '' || $value->source_type_value_3 != ' ') {
                                    if ($value->source_type_value_3 != $Lead->electrician && $value->source_type_value_3 != $Lead->architect) {
                                        $Source_3 = User::where('id', $value->source_type_value_3)->first();

                                        if ($Source_3) {
                                            if ($Source_3->id != $Lead->electrician && $Source_3->id != $Lead->architect) {
                                                $LeadContact_s3 = new LeadContact();
                                                $LeadContact_s3->lead_id = $Lead->id;
                                                $LeadContact_s3->contact_tag_id = 0;
                                                $LeadContact_s3->first_name = $Source_3->first_name;
                                                $LeadContact_s3->last_name = $Source_3->last_name;
                                                $LeadContact_s3->phone_number = $Source_3->phone_number;
                                                $LeadContact_s3->alernate_phone_number = 0;
                                                $LeadContact_s3->email = $Source_3->email;
                                                $LeadContact_s3->type = $Source_3->type;
                                                $LeadContact_s3->type_detail = 'user-' . $Source_3->type . '-' . $Source_3->id;
                                                $LeadContact_s3->save();
                                            }
                                        }
                                    }
                                    $LeadSource3 = new LeadSource();
                                    $LeadSource3->lead_id = $Lead->id;
                                    $LeadSource1->is_main = 1;
                                    $LeadSource3->source_type = $value->source_type_3;
                                    $LeadSource3->source = $value->source_type_value_3;
                                    $LeadSource3->save();
                                }
                            } else {
                                if ($value->source_type_value_3 != 0 || $value->source_type_value_3 != null || $value->source_type_value_3 != '' || $value->source_type_value_3 != ' ') {
                                    $LeadSource3 = new LeadSource();
                                    $LeadSource3->lead_id = $Lead->id;
                                    $LeadSource1->is_main = 1;
                                    $LeadSource3->source_type = $value->source_type_3;
                                    $LeadSource3->source = $value->source_type_value_3;
                                    $LeadSource3->save();
                                }
                            }
                        } catch (\Exception $e) {
                            $is_success = 103;
                            $is_error .= 'Lead ID : ' . $Lead->id . ' Inquiry ID : ' . $value->id . ' = ERRPR(3572) - ' . $e->getMessage() . ' </br>';
                            $response = errorRes($e->getMessage(), 400);
                            $response['lead_data'] = $Lead;
                            $response['inquiry_data'] = $value;
                        }
                        // ADD SOURCE-3 TO CONTACT END

                        // ADD SOURCE-4 TO CONTACT START
                        try {
                            if (($value->source_type_4 != null && $value->source_type_4 == 'user-201') || $value->source_type_4 == 'user-202' || $value->source_type_4 == 'user-301' || $value->source_type_4 == 'user-302' || $value->source_type_4 == 'user-101' || $value->source_type_4 == 'user-102' || $value->source_type_4 == 'user-103' || $value->source_type_4 == 'user-104') {
                                if ($value->source_type_value_4 != 0 || $value->source_type_value_4 != null || $value->source_type_value_4 != '' || $value->source_type_value_4 != ' ') {
                                    if ($value->source_type_value_4 != $Lead->electrician && $value->source_type_value_4 != $Lead->architect) {
                                        $Source_4 = User::where('id', $value->source_type_value_4)->first();

                                        if ($Source_4) {
                                            if ($Source_4->id != $Lead->electrician && $Source_4->id != $Lead->architect) {
                                                $LeadContact_s4 = new LeadContact();
                                                $LeadContact_s4->lead_id = $Lead->id;
                                                $LeadContact_s4->contact_tag_id = 0;
                                                $LeadContact_s4->first_name = $Source_4->first_name;
                                                $LeadContact_s4->last_name = $Source_4->last_name;
                                                $LeadContact_s4->phone_number = $Source_4->phone_number;
                                                $LeadContact_s4->alernate_phone_number = 0;
                                                $LeadContact_s4->email = $Source_4->email;
                                                $LeadContact_s4->type = $Source_4->type;
                                                $LeadContact_s4->type_detail = 'user-' . $Source_4->type . '-' . $Source_4->id;
                                                $LeadContact_s4->save();
                                            }
                                        }
                                    }
                                    $LeadSource4 = new LeadSource();
                                    $LeadSource4->lead_id = $Lead->id;
                                    $LeadSource1->is_main = 1;
                                    $LeadSource4->source_type = $value->source_type_4;
                                    $LeadSource4->source = $value->source_type_value_4;
                                    $LeadSource4->save();
                                }
                            } else {
                                if ($value->source_type_value_4 != 0 || $value->source_type_value_4 != null || $value->source_type_value_4 != '' || $value->source_type_value_4 != ' ') {
                                    $LeadSource4 = new LeadSource();
                                    $LeadSource4->lead_id = $Lead->id;
                                    $LeadSource1->is_main = 1;
                                    $LeadSource4->source_type = $value->source_type_4;
                                    $LeadSource4->source = $value->source_type_value_4;
                                    $LeadSource4->save();
                                }
                            }
                        } catch (\Exception $e) {
                            $is_success = 104;
                            $is_error .= 'Lead ID : ' . $Lead->id . ' Inquiry ID : ' . $value->id . ' = ERRPR(3623) - ' . $e->getMessage() . ' </br>';
                            $response = errorRes($e->getMessage(), 400);
                            $response['lead_data'] = $Lead;
                            $response['inquiry_data'] = $value;
                        }
                        // ADD SOURCE-4 TO CONTACT END

                        // ADD FILES TO FILES START
                        try {
                            $InquiryFiles = InquiryQuestionAnswer::where('inquiry_id', $value->id)
                                ->whereIn('inquiry_question_id', [1, 11])
                                ->where('question_type', 7)
                                ->where('answer', '!=', '')
                                ->get();
                            foreach ($InquiryFiles as $key_files => $files_val) {
                                foreach (explode(',', $files_val->answer) as $key_eachfiles => $eachfiles_val) {
                                    $LeadFile = new LeadFile();
                                    $LeadFile->uploaded_by = $files_val->user_id;
                                    $LeadFile->file_size = 0;
                                    $LeadFile->name = $eachfiles_val;
                                    $LeadFile->lead_id = $Lead->id;
                                    if ((int) $files_val->inquiry_question_id == 1) {
                                        $LeadFile->file_tag_id = 2;
                                    } elseif ((int) $files_val->inquiry_question_id == 11) {
                                        $LeadFile->file_tag_id = 3;
                                    } else {
                                        $LeadFile->file_tag_id = 0;
                                    }
                                    $LeadFile->save();
                                }
                            }
                        } catch (\Exception $e) {
                            $is_success = 105;
                            $is_error .= 'Lead ID : ' . $Lead->id . ' Inquiry ID : ' . $value->id . ' = ERRPR(3653) - ' . $e->getMessage() . ' </br>';
                            $response = errorRes($e->getMessage(), 400);
                            $response['lead_data'] = $Lead;
                            $response['inquiry_data'] = $value;
                        }
                        // ADD FILES TO FILES END

                        // ADD UPDATES TO NOTES START
                        try {
                            $InquiryUpdate = InquiryUpdate::where('inquiry_id', $value->id)->get();
                            foreach ($InquiryUpdate as $key_updates => $files_updates) {
                                $LeadUpdate = new LeadUpdate();
                                $LeadUpdate->user_id = $files_updates->user_id;
                                $LeadUpdate->lead_id = $Lead->id;
                                $LeadUpdate->message = $files_updates->message;
                                $LeadUpdate->task = 'Note';
                                $LeadUpdate->task_title = 'Note';
                                $LeadUpdate->created_at = $files_updates->created_at;
                                $LeadUpdate->updated_at = $files_updates->updated_at;
                                $LeadUpdate->timestamps = false;
                                $LeadUpdate->save();
                            }
                        } catch (\Exception $e) {
                            $is_success = 106;
                            $is_error .= 'Lead ID : ' . $Lead->id . ' Inquiry ID : ' . $value->id . ' = ERRPR(3677) - ' . $e->getMessage() . ' </br>';
                            $response = errorRes($e->getMessage(), 400);
                            $response['lead_data'] = $Lead;
                            $response['inquiry_data'] = $value;
                        }
                        // ADDUPDATES TO NOTES END

                        // ADD ACTIVITY LOGS TO TIMELION START
                        try {
                            $InquiryLogs = InquiryLog::where('inquiry_id', $value->id)->get();
                            foreach ($InquiryLogs as $key_log => $value_log) {
                                $LeadTimeline = new LeadTimeline();
                                $LeadTimeline->user_id = $value_log->user_id;
                                $LeadTimeline->type = $value_log->name;
                                $LeadTimeline->lead_id = $Lead->id;
                                $LeadTimeline->reffrance_id = $Lead->id;
                                $LeadTimeline->description = $value_log->description;
                                $LeadTimeline->created_at = $value_log->created_at;
                                $LeadTimeline->updated_at = $value_log->updated_at;
                                $LeadTimeline->timestamps = false;
                                $LeadTimeline->save();
                            }
                        } catch (\Exception $e) {
                            $is_success = 107;
                            $is_error .= 'Lead ID : ' . $Lead->id . ' Inquiry ID : ' . $value->id . ' = ERRPR(3702) - ' . $e->getMessage() . ' </br>';
                            $response = errorRes($e->getMessage(), 400);
                            $response['lead_data'] = $Lead;
                            $response['inquiry_data'] = $value;
                        }
                        // ADD ACTIVITY LOGS TO TIMELION END

                        // ADD LEAD STATUS UPDATE START
                        if ($value->inquiry_material_sent_date_time != null && $value->inquiry_material_sent_date_time != '' && $value->inquiry_material_sent_date_time != 0) {
                            try {
                                $LeadStatusUpdate = new LeadStatusUpdate();
                                $LeadStatusUpdate->lead_id = $Lead->id;
                                $LeadStatusUpdate->old_status = 0;
                                $LeadStatusUpdate->new_status = $Lead->status;
                                $LeadStatusUpdate->remark = 'Status Updates Get From Old Inquiry';

                                $LeadStatusUpdate->created_at = $value->material_sent_date_time;
                                $LeadStatusUpdate->entryby = $Lead->created_by;
                                $LeadStatusUpdate->entryip = $Lead->entryip;

                                $LeadStatusUpdate->updated_at = $value->material_sent_date_time;
                                $LeadStatusUpdate->updateby = $Lead->updated_by;
                                $LeadStatusUpdate->updateip = $Lead->updateip;

                                $LeadStatusUpdate->timestamps = false;
                                $LeadStatusUpdate->save();
                            } catch (\Exception $e) {
                                $is_success = 107;
                                $is_error .= 'Lead ID : ' . $Lead->id . ' Inquiry ID : ' . $value->id . ' = ERRPR(810) - ' . $e->getMessage() . ' </br>';
                                $response = errorRes($e->getMessage(), 400);
                                $response['lead_data'] = $Lead;
                                $response['inquiry_data'] = $value;
                            }
                        }
                        // ADD LEAD STATUS UPDATE END

                        $is_success = 1;
                        $is_error .= 'Lead ID : ' . $Lead->id . ' Inquiry ID : ' . $value->id . ' = SUCCESS </br>';
                        $response = successRes('Successfully saved lead');
                        $response['lead_data'] = $Lead;
                        $response['inquiry_data'] = $value;
                    } catch (\Exception $e) {
                        $is_success = 2;
                        $is_error .= 'Lead ID : ' . $Lead->id . ' Inquiry ID : ' . $value->id . ' = ERRPR(3715) - ' . $e->getMessage() . ' </br>';
                        $response = errorRes($e->getMessage(), 400);
                        $response['lead_data'] = $Lead;
                        $response['inquiry_data'] = $value;
                    }
                } else {
                    $is_success = 3;
                    $is_error .= 'Inquiry ID : ' . $value->id . ' = ERRPR(3722) - LEAD SAVING ISSUE </br>';
                    $response = errorRes('stage of site not save', 400);
                    $response['lead_data'] = '';
                    $response['inquiry_data'] = $value;
                }
            } catch (\Exception $e) {
                $is_success = 4;
                $is_error .= 'Inquiry ID : ' . $value->id . ' = ERRPR(3729) - ' . $e->getMessage() . ' </br>';
                $response = errorRes($e->getMessage(), 400);
                $response['lead_data'] = '';
                $response['inquiry_data'] = $value;
            }
        }

        if ($is_success == 1) {
            $response = successRes('All Leads & Deals Transfered Successfully');
            $response['error_code'] = $is_success;
            $response['error'] = $is_error;
        } else {
            $response = errorRes('Not Success', 400);
            $response['error_code'] = $is_success;
            $response['error'] = $is_error;
        }

        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }

    function deleteLeadThrewInquiryTrans(Request $request)
    {
        $query = Lead::query();
        $query->select('id');
        // $query->where('assigned_to', 5262);
        // $query->whereIn('id', [4626,4625]);
        // $query->whereIn('assigned_to', [4425,1233]);
        // $query->whereIn('assigned_to', [3245]);
        $query->where('id','>=', 33269);
        // $query->where('inquiry_id','!=', 0);
        $list_inquiry = $query->get();

        $is_success = 0;
        $is_error = '';
        foreach ($list_inquiry as $key => $value) {
            try {
                LeadClosing::query()
                    ->where('lead_id', $value->id)
                    ->delete();
                $is_error .= 'SUCCESS : ' . $key . '. CLOSING DATA Lead ID : ' . $value->id . ' DELETED </br>';
            } catch (\Exception $e) {
                $is_success = 1;
                $is_error .= 'ERROR : ' . $key . '. CLOSING DATA Lead ID : ' . $value->id . ' DELETED MSG : ' . $e->getMessage() . '</br>';
            }
            try {
                LeadContact::query()
                    ->where('lead_id', $value->id)
                    ->delete();
                $is_error .= 'SUCCESS : ' . $key . '. CONTACT DATA Lead ID : ' . $value->id . ' DELETED </br>';
            } catch (\Exception $e) {
                $is_success = 2;
                $is_error .= 'ERROR : ' . $key . '. CONTACT DATA Lead ID : ' . $value->id . ' DELETED MSG : ' . $e->getMessage() . '</br>';
            }
            try {
                LeadSource::query()
                    ->where('lead_id', $value->id)
                    ->delete();
                $is_error .= 'SUCCESS : ' . $key . '. SOURCE DATA Lead ID : ' . $value->id . ' DELETED </br>';
            } catch (\Exception $e) {
                $is_success = 3;
                $is_error .= 'ERROR : ' . $key . '. SOURCE DATA Lead ID : ' . $value->id . ' DELETED MSG : ' . $e->getMessage() . '</br>';
            }
            try {
                LeadFile::query()
                    ->where('lead_id', $value->id)
                    ->delete();
                $is_error .= 'SUCCESS : ' . $key . '. FILES DATA Lead ID : ' . $value->id . ' DELETED </br>';
            } catch (\Exception $e) {
                $is_success = 4;
                $is_error .= 'ERROR : ' . $key . '. FILES DATA Lead ID : ' . $value->id . ' DELETED MSG : ' . $e->getMessage() . '</br>';
            }
            try {
                LeadUpdate::query()
                    ->where('lead_id', $value->id)
                    ->delete();
                $is_error .= 'SUCCESS : ' . $key . '. UPDATES DATA Lead ID : ' . $value->id . ' DELETED </br>';
            } catch (\Exception $e) {
                $is_success = 5;
                $is_error .= 'ERROR : ' . $key . '. UPDATES DATA Lead ID : ' . $value->id . ' DELETED MSG : ' . $e->getMessage() . '</br>';
            }
            try {
                LeadTimeline::query()
                    ->where('lead_id', $value->id)
                    ->delete();
                $is_error .= 'SUCCESS : ' . $key . '. TIMELION DATA Lead ID : ' . $value->id . ' DELETED </br>';
            } catch (\Exception $e) {
                $is_success = 6;
                $is_error .= 'ERROR : ' . $key . '. TIMELION DATA Lead ID : ' . $value->id . ' DELETED MSG : ' . $e->getMessage() . '</br>';
            }
            try {
                LeadStatusUpdate::query()
                    ->where('lead_id', $value->id)
                    ->delete();
                $is_error .= 'SUCCESS : ' . $key . '. TIMELION DATA Lead ID : ' . $value->id . ' DELETED </br>';
            } catch (\Exception $e) {
                $is_success = 6;
                $is_error .= 'ERROR : ' . $key . '. TIMELION DATA Lead ID : ' . $value->id . ' DELETED MSG : ' . $e->getMessage() . '</br>';
            }

            // Lead::query()
            //     ->where('id', $value->id)
            //     ->delete();
        }

        if ($is_success > 0) {
            $MAX_Lead = Lead::query()->max('id') + 1;
            DB::statement('ALTER SEQUENCE leads RESTART WITH ' . $MAX_Lead);
            $MAX_LeadClosing = LeadClosing::query()->max('id') + 1;
            DB::statement('ALTER SEQUENCE lead_closing RESTART WITH ' . $MAX_LeadClosing);
            $MAX_LeadContact = LeadContact::query()->max('id') + 1;
            DB::statement('ALTER SEQUENCE lead_contacts RESTART WITH ' . $MAX_LeadContact);
            $MAX_LeadSource = LeadSource::query()->max('id') + 1;
            DB::statement('ALTER SEQUENCE lead_sources RESTART WITH ' . $MAX_LeadSource);
            $MAX_LeadFile = LeadFile::query()->max('id') + 1;
            DB::statement('ALTER SEQUENCE lead_files RESTART WITH ' . $MAX_LeadFile);
            $MAX_LeadUpdate = LeadUpdate::query()->max('id') + 1;
            DB::statement('ALTER SEQUENCE lead_updates RESTART WITH ' . $MAX_LeadUpdate);
            $MAX_LeadTimeline = LeadTimeline::query()->max('id') + 1;
            DB::statement('ALTER SEQUENCE lead_timeline RESTART WITH ' . $MAX_LeadTimeline);
            $MAX_LeadStatusUpdate = LeadStatusUpdate::query()->max('id') + 1;
            DB::statement('ALTER SEQUENCE lead_status_updates RESTART WITH ' . $MAX_LeadStatusUpdate);

            $response = successRes('All Leads & Deals Deleted Successfully');
            $response['error_code'] = $is_success;
            $response['error'] = $is_error;
        } else {
            $response = errorRes('Not Success', 400);
            $response['error_code'] = $is_success;
            $response['error'] = $is_error;
        }

        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }
    function deleteLeadOtherData(Request $request)
    {
        
        $is_error = '';
        $is_success = 0;
            try{
                LeadClosing::query()->whereBetween('lead_id', [33269, 43827])->delete();
            } catch (\Exception $e) {
                $is_success = 1;
                $is_error .= 'ERROR : DELETED MSG : ' . $e->getMessage() . '</br>';
            }
            try {
                LeadContact::query()->whereBetween('lead_id', [33269, 43827])->delete();
            } catch (\Exception $e) {
                $is_success = 2;
                $is_error .= 'ERROR : DELETED MSG : ' . $e->getMessage() . '</br>';
            }
            try {
                LeadSource::query()->whereBetween('lead_id', [33269, 43827])->delete();
            } catch (\Exception $e) {
                $is_success = 3;
                $is_error .= 'ERROR : DELETED MSG : ' . $e->getMessage() . '</br>';
            }
            try {
                LeadFile::query()->whereBetween('lead_id', [33269, 43827])->delete();
            } catch (\Exception $e) {
                $is_success = 4;
                $is_error .= 'ERROR : DELETED MSG : ' . $e->getMessage() . '</br>';
            }
            try {
                LeadUpdate::query()->whereBetween('lead_id', [33269, 43827])->delete();
            } catch (\Exception $e) {
                $is_success = 5;
                $is_error .= 'ERROR : DELETED MSG : ' . $e->getMessage() . '</br>';
            }
            try {
                LeadTimeline::query()->whereBetween('lead_id', [33269, 43827])->delete();
            } catch (\Exception $e) {
                $is_success = 6;
                $is_error .= 'ERROR : DELETED MSG : ' . $e->getMessage() . '</br>';
            }
            try {
                LeadStatusUpdate::query()->whereBetween('lead_id', [33269, 43827])->delete();
            } catch (\Exception $e) {
                $is_success = 6;
                $is_error .= 'ERROR : DELETED MSG : ' . $e->getMessage() . '</br>';
            }

            // $MAX_LeadClosing = LeadClosing::query()->max('id') + 1;
            // DB::statement('ALTER SEQUENCE lead_closing RESTART WITH ' . $MAX_LeadClosing);
            // $MAX_LeadContact = LeadContact::query()->max('id') + 1;
            // DB::statement('ALTER SEQUENCE lead_contacts RESTART WITH ' . $MAX_LeadContact);
            // $MAX_LeadSource = LeadSource::query()->max('id') + 1;
            // DB::statement('ALTER SEQUENCE lead_sources RESTART WITH ' . $MAX_LeadSource);
            // $MAX_LeadFile = LeadFile::query()->max('id') + 1;
            // DB::statement('ALTER SEQUENCE lead_files RESTART WITH ' . $MAX_LeadFile);
            // $MAX_LeadUpdate = LeadUpdate::query()->max('id') + 1;
            // DB::statement('ALTER SEQUENCE lead_updates RESTART WITH ' . $MAX_LeadUpdate);
            // $MAX_LeadTimeline = LeadTimeline::query()->max('id') + 1;
            // DB::statement('ALTER SEQUENCE lead_timeline RESTART WITH ' . $MAX_LeadTimeline);
            // $MAX_LeadStatusUpdate = LeadStatusUpdate::query()->max('id') + 1;
            // DB::statement('ALTER SEQUENCE lead_status_updates RESTART WITH ' . $MAX_LeadStatusUpdate);

            $response = successRes('All Leads & Deals Deleted Successfully');
            $response['error_code'] = $is_success;
            $response['error'] = $is_error;

        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }

    function updateOldLeadData(Request $request)
    {
        $selectColumns = ['leads.id as lead_id', 'leads.status as lead_status', 'leads.created_by as lead_created_by', 'leads.updated_by as lead_updated_by', 'inquiry.id as inquiry_id', 'inquiry.answer_date_time as inquiry_answer_date_time', 'inquiry.material_sent_date_time as inquiry_material_sent_date_time'];

        $query = Lead::query();
        $query->select($selectColumns);
        $query->leftJoin('inquiry as inquiry', 'inquiry.id', '=', 'leads.inquiry_id');
        $list_lead = $query->get();

        $is_success = 0;
        $is_error = '';

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        LeadStatusUpdate::query()->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        foreach ($list_lead as $key => $value) {
            try {
                if ($value->inquiry_material_sent_date_time != null && $value->inquiry_material_sent_date_time != '' && $value->inquiry_material_sent_date_time != 0) {
                    $LeadStatusUpdate = new LeadStatusUpdate();
                    $LeadStatusUpdate->lead_id = $value->lead_id;
                    $LeadStatusUpdate->old_status = 0;
                    $LeadStatusUpdate->new_status = $value->lead_status;
                    $LeadStatusUpdate->remark = 'Transfer To Inquiry';

                    $LeadStatusUpdate->created_at = $value->inquiry_material_sent_date_time;
                    $LeadStatusUpdate->entryby = $value->lead_created_by;
                    $LeadStatusUpdate->entryip = $request->ip();

                    $LeadStatusUpdate->updated_at = $value->inquiry_material_sent_date_time;
                    $LeadStatusUpdate->updateby = $value->lead_updated_by;
                    $LeadStatusUpdate->updateip = $request->ip();

                    $LeadStatusUpdate->timestamps = false;
                    $LeadStatusUpdate->save();
                }

                if ($value->inquiry_answer_date_time != null && $value->inquiry_answer_date_time != '' && $value->inquiry_answer_date_time != 0) {
                    $Lead_Update = Lead::find($value->lead_id);
                    $Lead_Update->updated_at = $value->inquiry_answer_date_time;

                    $Lead_Update->timestamps = false;
                    $Lead_Update->save();
                }

                $is_success = 1;
                $is_error .= 'Lead ID : ' . $value->lead_id . ' Inquiry ID : ' . $value->inquiry_id . ' = SUCCESS </br>';
                $response = successRes('Successfully saved lead');
            } catch (\Exception $e) {
                $is_success = 4;
                $is_error .= 'Inquiry ID : ' . $value->inquiry_id . ' = ERRPR(3729) - ' . $e->getMessage() . ' </br>';
                $response = errorRes($e->getMessage(), 400);
                $response['lead_data'] = '';
                $response['inquiry_data'] = $value;
            }
        }

        if ($is_success == 1) {
            $response = successRes('All Leads & Deals Transfered Successfully');
            $response['error_code'] = $is_success;
            $response['error'] = $is_error;
        } else {
            $response = errorRes('Not Success', 400);
            $response['error_code'] = $is_success;
            $response['error'] = $is_error;
        }

        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }

    function updatestageofsitedata(Request $request)
    {
        $query = Lead::query();
        $list_inquiry = $query->get();

        $is_success = 0;
        $is_error = '';
        foreach ($list_inquiry as $key => $value) {
            try {
                if ($value->inquiry_id != '' && $value->inquiry_id != 0 && $value->inquiry_id != null) {
                    $inquiry_detail = Inquiry::find($value->inquiry_id);
                    $old_stageofsite = CRMSettingStageOfSite::query();
                    $old_stageofsite->where('name', $inquiry_detail->stage_of_site);
                    $old_stageofsite = $old_stageofsite->first();

                    if ($old_stageofsite) {
                        $LeadUpdate = Lead::find($value->id);
                        $LeadUpdate->site_stage = $old_stageofsite->id;
                        $LeadUpdate->save();
                    } else {
                        $new_stageofsite = new CRMSettingStageOfSite();
                        $new_stageofsite->name = $inquiry_detail->stage_of_site;
                        $new_stageofsite->status = 1;
                        $new_stageofsite->save();

                        if ($new_stageofsite) {
                            $LeadUpdate = Lead::find($value->id);
                            $LeadUpdate->site_stage = $new_stageofsite->id;
                            $LeadUpdate->save();
                        } else {
                            $LeadUpdate = Lead::find($value->id);
                            $LeadUpdate->site_stage = 0;
                            $LeadUpdate->save();
                        }
                    }
                } else {
                    // $LeadUpdate = Lead::find($value->id);
                    // $LeadUpdate->site_stage = 0;
                    // $LeadUpdate->save();
                }
                $is_success = 200;
                $is_error .= 'LEAD ID : ' . $value->id . ' = SUCCESS(1071) </br>';
            } catch (\Exception $e) {
                $is_success = 404;
                $is_error .= 'LEAD ID : ' . $value->id . ' = ERRPR(1075) - ' . $e->getMessage() . ' </br>';
            }
        }

        $response = successRes('All Leads & Deals Transfered Successfully');
        $response['error_code'] = $is_success;
        $response['error'] = $is_error;
        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }

    function inquiryBillTransferLeadAndDeal(Request $request)
    {
        $selectColumns = array(
            'leads.id as lead_id', 
            'inquiry.id as inquiry_id', 
            'inquiry.status as inquiry_status', 
            'inquiry.user_id as inquiry_user_id', 
            'inquiry.billing_amount', 
            'inquiry.billing_invoice', 
            'inquiry.is_verified', 
            'inquiry.total_point', 
            'inquiry.is_point_calculated', 
            'inquiry.is_claimed', 
            'inquiry.claimed_date_time'
        );

        $query = Lead::query();
        $query->select($selectColumns);
        $query->leftJoin('inquiry', 'inquiry.id', '=', 'leads.inquiry_id');
        // $query->leftJoin('inquiry_question_answer', 'inquiry_question_answer.id', '=', 'inquiry.id');
        // $query->leftJoin('inquiry_questions', 'inquiry_questions.id', '=', 'inquiry_question_answer.inquiry_question_id');
        // $query->leftJoin('inquiry_question_options', 'inquiry_question_options.id', '=', 'inquiry_question_answer.answer');
        $query->where('leads.inquiry_id', '<>', 0);
        $list_inquiry = $query->get();

        $message = '';
        foreach ($list_inquiry as $key => $value) {
            // Question Id = 1069 Reason Of point Lapsed ?
            // In These Condtion Two Type Of Posibility
            // 1.Bill Uploded
            // 2.Bill Not Uploaded
            try {
            
            if ($value->billing_invoice != '' && $value->billing_invoice != null) {
                // Comma Not Available in String
                $billLog = InquiryLog::query()
                    ->where('inquiry_id', $value->inquiry_id)
                    ->where('name', 'billing-invoice')
                    ->first();
                $LeadFile = new LeadFile();
                $LeadFile->file_size = 0;
                $LeadFile->lead_id = $value->lead_id;
                $LeadFile->file_tag_id = 3;
                $LeadFile->name = $value->billing_invoice;
                if($value->billing_amount != null || $value->billing_amount != ''){
                    $LeadFile->billing_amount = $value->billing_amount;
                }else{
                    $LeadFile->billing_amount = 0;
                }
                $LeadFile->point = $value->total_point;
                if ($billLog) {
                    $LeadFile->uploaded_by = $billLog->user_id;
                    $LeadFile->created_at = $billLog->created_at;
                    $LeadFile->updated_at = $billLog->updated_at;
                } else {
                    $LeadFile->uploaded_by = $value->inquiry_user_id;
                    $LeadFile->created_at = $value->created_at;
                    $LeadFile->updated_at = $value->updated_at;
                }
                $LeadFile->timestamps = false;
                $LeadFile->save();
                if($LeadFile){
                    if ($value->is_claimed == 0) {
                        // In These Condtion Two Type Of Posibility
                        // INQUIRY STATUS 14-Query 12-Lapes
                        // 1.If Inquiry Status Is Rejected
                        // 2.Point Lapes
                        // 3.Point Query
                        if ($value->inquiry_status == 102) {
                            // 1.If Inquiry Status Is Rejected
                            $LeadFile->hod_approved = 2;
                            $LeadFile->status = 102;
                            $LeadFile->timestamps = false;
                            $LeadFile->save();
                            if ($LeadFile) {
                                $LeadUpdate = Lead::find($value->lead_id);
                                $LeadUpdate->telesales_verification = 3;
                                $LeadUpdate->service_verification = 3;
                                $LeadUpdate->companyadmin_verification = 3;
                                $LeadUpdate->save();
                            }
                        }elseif($value->inquiry_status == 12){
                            // 2.Point Lapes
                            $inquiryQuestionAnswer = InquiryQuestionAnswer::query();
                            $inquiryQuestionAnswer->where('inquiry_id',$value->inquiry_id);
                            $inquiryQuestionAnswer->where('inquiry_question_id',1069);
                            $inquiryQuestionAnswer = $inquiryQuestionAnswer->get();
                            $inquiryQuestionAnswerCount = $inquiryQuestionAnswer->count();

                            if($inquiryQuestionAnswerCount > 0){
                                
                                foreach ($inquiryQuestionAnswer as $queAnskey => $queAnsValue) {
                                    
                                    $leadQuestionAnswer = new LeadQuestionAnswer();
                                    // Bill-Query , Bill-Lapsed
                                    $leadQuestionAnswer->lead_question_id = 1;
                                    $leadQuestionAnswer->question_type = 6;
                                    $leadQuestionAnswer->lead_id = $value->lead_id;
                                    $leadQuestionAnswer->reference_type = 'Bill-Lapsed';
                                    $leadQuestionAnswer->reference_id = $LeadFile->id;
                                    $leadQuestionAnswer->answer = $queAnsValue->answer;
                                    $leadQuestionAnswer->created_at = $queAnsValue->created_at;
                                    $leadQuestionAnswer->entryby = $queAnsValue->user_id;
                                    $leadQuestionAnswer->updated_at = $queAnsValue->updated_at;
                                    $leadQuestionAnswer->updateby = $queAnsValue->user_id;
                                    $leadQuestionAnswer->source = 'WEB';
                                    $leadQuestionAnswer->timestamps = false;
                                    $leadQuestionAnswer->save();
                                }

                            }

                            $LeadFile->hod_approved = 2;
                            $LeadFile->status = 102;
                            $LeadFile->timestamps = false;
                            $LeadFile->save();
                            if ($LeadFile) {
                                $LeadUpdate = Lead::find($value->lead_id);
                                $LeadUpdate->companyadmin_verification = 3;
                                $LeadUpdate->save();
                            }
                            
                        }elseif($value->inquiry_status == 14){
                            // 2.Point Query
                            $inquiryQuestionAnswer = InquiryQuestionAnswer::query();
                            $inquiryQuestionAnswer->where('inquiry_id',$value->inquiry_id);
                            $inquiryQuestionAnswer->where('inquiry_question_id',1069);
                            $inquiryQuestionAnswer = $inquiryQuestionAnswer->get();
                            $inquiryQuestionAnswerCount = $inquiryQuestionAnswer->count();

                            if($inquiryQuestionAnswerCount > 0){
                                
                                foreach ($inquiryQuestionAnswer as $queAnskey => $queAnsValue) {
                                    
                                    $leadQuestionAnswer = new LeadQuestionAnswer();
                                    // Bill-Query , Bill-Lapsed
                                    $leadQuestionAnswer->lead_question_id = 1;
                                    $leadQuestionAnswer->question_type = 6;
                                    $leadQuestionAnswer->lead_id = $value->lead_id;
                                    $leadQuestionAnswer->reference_type = 'Bill-Query';
                                    $leadQuestionAnswer->reference_id = $LeadFile->id;
                                    $leadQuestionAnswer->answer = $queAnsValue->answer;
                                    $leadQuestionAnswer->created_at = $queAnsValue->created_at;
                                    $leadQuestionAnswer->entryby = $queAnsValue->user_id;
                                    $leadQuestionAnswer->updated_at = $queAnsValue->updated_at;
                                    $leadQuestionAnswer->updateby = $queAnsValue->user_id;
                                    $leadQuestionAnswer->source = 'WEB';
                                    $leadQuestionAnswer->timestamps = false;
                                    $leadQuestionAnswer->save();
                                }

                            }
                            
                            $LeadFile->hod_approved = 0;
                            $LeadFile->status = 101;
                            $LeadFile->timestamps = false;
                            $LeadFile->save();
                            if ($LeadFile) {
                                $LeadUpdate = Lead::find($value->lead_id);
                                $LeadUpdate->telesales_verification = 2;
                                $LeadUpdate->service_verification = 2;
                                $LeadUpdate->companyadmin_verification = 1;
                                $LeadUpdate->save();
                            }
                            
                        }

                    } elseif ($value->is_claimed == 1) {
                        // In These Condtion Two Type Of Posibility
                        // 1.Point Claim
                        $LeadFile->status = 100;
                        $LeadFile->hod_approved = 1;
                        $LeadFile->claimed_date_time = $value->claimed_date_time;
                        $LeadFile->hod_approved_at = $value->claimed_date_time;
                        $LeadFile->timestamps = false;
                        $LeadFile->save();
                        if ($LeadFile) {
                            $LeadUpdate = Lead::find($value->lead_id);
                            $LeadUpdate->total_billing_amount = $value->billing_amount;
                            $LeadUpdate->total_point = $value->total_point;
                            $LeadUpdate->telesales_verification = 2;
                            $LeadUpdate->service_verification = 2;
                            $LeadUpdate->companyadmin_verification = 2;
                            $LeadUpdate->save();
    
                            $LeadTimeline = new LeadTimeline();
                            $LeadTimeline->user_id = $LeadFile->uploaded_by;
                            $LeadTimeline->type = 'Reward';
                            $LeadTimeline->lead_id = $LeadFile->lead_id;
                            $LeadTimeline->reffrance_id = $LeadFile->id;
                            $LeadTimeline->reference_type = 'files';
                            $LeadTimeline->description = 'Point Claimed For This Bill #' . $LeadFile->id;
                            $LeadTimeline->created_at = $value->claimed_date_time;
                            $LeadTimeline->updated_at = $value->claimed_date_time;
                            $LeadTimeline->timestamps = false;
                            $LeadTimeline->save();
                        }
                    }
                }
            }
                $message .= 'SUCCESS :: Inquiry ID : ' . $value->inquiry_id . ' & Lead ID : ' . $value->lead_id . ' -  </br>';
            } catch (\Exception $e) {
                $message .= 'ERROR :: Inquiry ID : ' . $value->inquiry_id . ' & Lead ID : ' . $value->lead_id . ' - ' . $e->getMessage() . ' </br>';
            }
        }

        $response = successRes('All Bill Transfered Successfully');
        $response['data'] = $message;

        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }

    function leadAmountToQuotation(Request $request){

        $message = '';
        $selectColumns = array(
            'leads.id as lead_id',
            'leads.quotation',
            'leads.quotation_file',
        );

        $query = Lead::query();
        $query->select($selectColumns);
        $query->where('leads.quotation', '<>', 0);
        $list_inquiry = $query->get();

        foreach ($list_inquiry as $quot_key => $quot_value) {
            
            $isQuotation = Wltrn_Quotation::query();
            $isQuotation->where('wltrn_quotation.inquiry_id', $quot_value->lead_id);
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
			$QuotMaster->quotation_file = $quot_value->quotation_file;

            if($quot_value->quotation_file != '' || $quot_value->quotation_file != null){
                $QuotMaster->print_count = count(explode(',', $quot_value->quotation_file));
            }else{
                $QuotMaster->print_count = 0;
            }
			$QuotMaster->status = 3;
			$QuotMaster->inquiry_id = $quot_value->lead_id;
			$QuotMaster->net_amount = $quot_value->quotation;
			$QuotMaster->entryby = Auth::user()->id; //Live
			$QuotMaster->entryip = $request->ip();
			$QuotMaster->save();
			if($QuotMaster){
				// Wltrn_Quotation::where('quotgroup_id', $QuotMaster->quotgroup_id)->update(['isfinal' => 0]);
            	// $QuotMaster->isfinal = 1;
				// $QuotMaster->save();
				$timeline = array();
                $timeline['lead_id'] = $QuotMaster->inquiry_id;
                $timeline['type'] = "lead-quot-update";
                $timeline['reffrance_id'] = $QuotMaster->inquiry_id;
                $timeline['description'] = "Add Old Inquiry Quotation Amount As Manual Quotation File (".$QuotMaster->print_count.") In Lead #" . $QuotMaster->inquiry_id . " Amount is : ".$QuotMaster->net_amount;
                $timeline['source'] = "WEB";
                saveLeadTimeline($timeline);
				
				$message .= 'SUCCESS :: Lead ID : ' . $QuotMaster->inquiry_id . ' & Quotation ID : ' . $QuotMaster->id . ' -  </br>';
			}
        }
        
        $response = successRes('All Bill Transfered Successfully');
        $response['data'] = $message;
        
			

		return response()->json($response)->header('Content-Type', 'application/json');
	}
    
    function lastQuotationAutoTick(Request $request){

        $message = '';

        $lstQuota = Wltrn_Quotation::query();
        $lstQuota->selectRaw('wltrn_quotation.inquiry_id');
        $lstQuota->selectRaw('COUNT(wltrn_quotation.id) AS quot_count');
        $lstQuota->selectRaw('GROUP_CONCAT(wltrn_quotation.isfinal)');
        $lstQuota->selectRaw('GROUP_CONCAT(DISTINCT(wltrn_quotation.quotgroup_id)) AS group_id');
        $lstQuota->selectRaw('COUNT(DISTINCT wltrn_quotation.quotgroup_id) AS group_id_count');
        $lstQuota->where('wltrn_quotation.inquiry_id', '<>', 0);
        $lstQuota->where('wltrn_quotation.status', 3);
        $lstQuota->groupBy('wltrn_quotation.inquiry_id');
        $lstQuota = $lstQuota->get();

        foreach ($lstQuota as $quot_key => $quot_value) {
            try {
                Wltrn_Quotation::where('wltrn_quotation.inquiry_id', $quot_value->inquiry_id)->update(['isfinal' => 0]);

                $objQuotation = Wltrn_Quotation::where('wltrn_quotation.inquiry_id', $quot_value->inquiry_id);
                $objQuotation->where('wltrn_quotation.status', 3);
                $objQuotation->orderBy('wltrn_quotation.id', 'desc');
                $objQuotation = $objQuotation->first();

                $QuotationUpdate = Wltrn_Quotation::find($objQuotation->id);
                $QuotationUpdate->isfinal = 1;
                $QuotationUpdate->save();

                if($QuotationUpdate){
                    $message .= 'SUCCESS :: Lead ID : ' . $QuotationUpdate->inquiry_id . ' & Quotation ID : ' . $QuotationUpdate->id . ' </br>';
                }
            } catch (\Exception $e) {
                $message .= 'ERROR :: Lead ID : ' . $QuotationUpdate->inquiry_id . ' & Quotation ID : ' . $QuotationUpdate->id . ' & Message : ' . $e->getMessage() . ' </br>';
            }
        }
        
        $response = successRes();
        $response['data'] = $message;

		return response()->json($response)->header('Content-Type', 'application/json');
	}
}


