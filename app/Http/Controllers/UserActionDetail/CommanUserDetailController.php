<?php

namespace App\Http\Controllers\UserActionDetail;

use App\Models\Lead;
use App\Models\User;
use App\Models\Architect;
use App\Models\TagMaster;
use App\Models\LeadFile;
use App\Models\Electrician;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class CommanUserDetailController extends Controller
{
    function searchReminderTimeSlot(Request $request)
    {

        $searchKeyword = isset($request->q) ? $request->q : "";

        $ReminderTimeSlot = getReminderTimeSlot();

        $finalArray[] = array();
        foreach ($ReminderTimeSlot as $key => $value) {
            $finalArray[$key]['id'] = $value['id'];
            $finalArray[$key]['text'] = $value['name'];
        }

        $response = array();
        $response['results'] = $finalArray;
        $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function searchUserTag(Request $request)
    {
        $searchKeyword = isset($request->q) ? $request->q : "";

        $data = TagMaster::select('id', 'tagname as text');
        $data->where('tag_master.isactive', 1);
        $data->where('tag_master.tag_type', 202);
        $data->where('tag_master.tagname', 'like', "%" . $searchKeyword . "%");
        $data->limit(5);
        $data = $data->get();

        $response = array();
        $response['results'] = $data;
        $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function saveUserDetail(Request $request)
    {
        $user = User::find($request->id);
        if ($user) {
            $Tag_id = isset($request->user_tag) ? $request->user_tag : "";
            if ($Tag_id != "") {
                $Old_Tag = '';
                $New_Tag = '';
                if ($user->tag !=  implode(',', $Tag_id)) {
                    $new_value = $Tag_id;
                    $old_value = explode(', ', $user->tag);

                    if ($new_value != 0 || $new_value != '') {
                        $Tag = TagMaster::select('id', 'tagname as text');
                        $Tag->whereIn('tag_master.id', $new_value);
                        $Tag->where('tag_master.isactive', 1);
                        $Tag->where('tag_master.tag_type', 202);
                        $Tag = $Tag->get();
                        if ($Tag) {
                            foreach ($Tag as $key => $value) {
                                $New_Tag .= $value['text'];
                                $New_Tag .= ', ';
                            }
                        }
                    }

                    if ($old_value != 0 || $old_value != '') {
                        $Tag = TagMaster::select('id', 'tagname as text');
                        $Tag->whereIn('tag_master.id', $old_value);
                        $Tag->where('tag_master.isactive', 1);
                        $Tag->where('tag_master.tag_type', 202);
                        $Tag = $Tag->get();
                        if ($Tag) {
                            foreach ($Tag as $key => $value) {
                                $Old_Tag .= $value['text'];
                                $Old_Tag .= ', ';
                            }
                        }
                    }
                }
                $user->tag = implode(',', $Tag_id);
                $user->save();

                if ($user) {
                    if(in_array($user['type'], [201, 202])) {
                        $timeline = array();
                        $timeline['user_id'] = $user->reference_id;
                        $timeline['log_type'] = "ARCHITECT-LOG";
                        $timeline['field_name'] = "tag";
                        $timeline['old_value'] = $Old_Tag;
                        $timeline['new_value'] = $New_Tag;
                        $timeline['reference_type'] = "Architect";
                        $timeline['reference_id'] = $user->id;
                        $timeline['transaction_type'] = "update";
                        $timeline['description'] = "Tag Change : " . $Old_Tag . " To " . $New_Tag;
                        $timeline['source'] = "web";
                        $timeline['ip'] = $request->ip();
                        saveUserLog($timeline);
                    } else if(in_array($user['type'], [301, 302])) { 
                        $timeline = array();
                        $timeline['user_id'] = $user->reference_id;
                        $timeline['log_type'] = "ELECTRICIAN-LOG";
                        $timeline['field_name'] = "tag";
                        $timeline['old_value'] = $Old_Tag;
                        $timeline['new_value'] = $New_Tag;
                        $timeline['reference_type'] = "Electrician";
                        $timeline['reference_id'] = $user->id;
                        $timeline['transaction_type'] = "update";
                        $timeline['description'] = "Tag Change : " . $Old_Tag . " To " . $New_Tag;
                        $timeline['source'] = "web";
                        $timeline['ip'] = $request->ip();
                        saveUserLog($timeline);
                    }
                    
                }
                $response = successRes("Succssfully Update Detail");
                return response()->json($response)->header('Content-Type', 'application/json');
            }

            $UserOwner = isset($request->user_owner) ? $request->user_owner : "";
            if($UserOwner != "") {
                if(in_array($user['type'], [201, 202])) {
                    $Architect = Architect::find($user['reference_id']);
                    if($Architect){
                        $new_value = $UserOwner;
                        if(isset($Architect->sale_person_id) && $Architect->sale_person_id != "" && $Architect->sale_person_id != null) {
                            $old_value = $Architect->sale_person_id;
                        } else {
                            $old_value = "";
                        }

                        if ($new_value !=  $old_value) {
                            $New_SalePerson = '';
                            if ($new_value != 0 || $new_value != '') {
                                $SalePerson = User::select('id', DB::raw('CONCAT(first_name, " ", last_name) as text'));
                                $SalePerson->where('users.id', $new_value);
                                $SalePerson = $SalePerson->first();
                                if ($SalePerson) {
                                    $New_SalePerson = $SalePerson->text;
                                }
                            }
    
                            $Old_SalePerson = '';
                            if ($old_value != 0 || $old_value != '') {
                                $SalePerson = User::select('id', DB::raw('CONCAT(first_name, " ", last_name) as text'));
                                $SalePerson->where('users.id', $old_value);
                                $SalePerson = $SalePerson->first();
                                if ($SalePerson) {
                                    $Old_SalePerson = $SalePerson->text;
                                }
                            }
    
                            $Architect->sale_person_id = $new_value;
                            $Architect->save();
                            if ($Architect) {
                                $timeline = array();
                                $timeline['user_id'] = $Architect->id;
                                $timeline['log_type'] = "ARCHITECT-LOG";
                                $timeline['field_name'] = "sale_person_id";
                                $timeline['old_value'] = $Old_SalePerson;
                                $timeline['new_value'] = $New_SalePerson;
                                $timeline['reference_type'] = "Architect";
                                $timeline['reference_id'] = $user->id;
                                $timeline['transaction_type'] = "update";
                                $timeline['description'] = "Sale Person Change : " . $Old_SalePerson . " To " . $New_SalePerson;
                                $timeline['source'] = "web";
                                $timeline['ip'] = $request->ip();
                                saveUserLog($timeline);
                            }
                            $response = successRes("Succssfully Update Detail");
                        }

                    } else {
                        $response = errorRes("User Not Found");
                    }
                    
                    return response()->json($response)->header('Content-Type', 'application/json');
                } else if(in_array($user['type'], [301, 302])) {
                    $Electrician = Electrician::find($user['reference_id']);
                    if($Electrician) {
                        $new_value = $UserOwner;
                        $old_value = $Electrician->sale_person_id;

                        if ($new_value !=  $old_value) {
                            $New_SalePerson = '';
                            if ($new_value != 0 || $new_value != '') {
                                $SalePerson = User::select('id', DB::raw('CONCAT(first_name, " ", last_name) as text'));
                                $SalePerson->where('users.id', $new_value);
                                $SalePerson = $SalePerson->first();
                                if ($SalePerson) {
                                    $New_SalePerson = $SalePerson->text;
                                }
                            }
    
                            $Old_SalePerson = '';
                            if ($old_value != 0 || $old_value != '') {
                                $SalePerson = User::select('id', DB::raw('CONCAT(first_name, " ", last_name) as text'));
                                $SalePerson->where('users.id', $old_value);
                                $SalePerson = $SalePerson->first();
                                if ($SalePerson) {
                                    $Old_SalePerson = $SalePerson->text;
                                }
                            }
    
                            $Electrician->sale_person_id = $new_value;
                            $Electrician->save();
                            if ($Electrician) {
                                $timeline = array();
                                $timeline['user_id'] = $Electrician->id;
                                $timeline['log_type'] = "ELECTRICIAN-LOG";
                                $timeline['field_name'] = "sale_person_id";
                                $timeline['old_value'] = $Old_SalePerson;
                                $timeline['new_value'] = $New_SalePerson;
                                $timeline['reference_type'] = "Electrician";
                                $timeline['reference_id'] = $user->id;
                                $timeline['transaction_type'] = "update";
                                $timeline['description'] = "Sale Person Change : " . $Old_SalePerson . " To " . $New_SalePerson;
                                $timeline['source'] = "web";
                                $timeline['ip'] = $request->ip();
                                saveUserLog($timeline);
                            }
                            $response = successRes("Succssfully Update Detail");
                        }
                    }
                    return response()->json($response)->header('Content-Type', 'application/json');
                } 
            }
        }
    }

    function viewLeadData(Request $request){


        $query = Lead::query();
        $query->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
        $query->leftJoin('lead_sources as source', 'source.lead_id', '=', 'leads.id');
        if ($request->is_arc == 1) {
            $query->leftJoin('users as lead_ele', 'lead_ele.id', '=', 'leads.electrician');
        } else {
            $query->leftJoin('users as lead_ele', 'lead_ele.id', '=', 'leads.architect');
        }
       
        $query->leftJoin(DB::raw('(
            SELECT ch.lead_id,
            group_concat(channel_partner.firm_name) as channel_partner_name
            FROM lead_sources as ch 
            LEFT JOIN channel_partner ON channel_partner.user_id = ch.source
            WHERE ch.source_type IN ("user-101","user-102","user-103","user-104","user-105")
            GROUP BY ch.lead_id
        ) AS ch_source'), 'ch_source.lead_id', '=', 'leads.id');
        
        if ($request->is_arc == 1) {
            $query->leftJoin(DB::raw('(
                SELECT so.lead_id,
                group_concat(CONCAT(source_user.first_name," ", source_user.last_name)) as arc_and_ele_name
                FROM lead_sources as so 
                LEFT JOIN users as source_user ON source_user.id = so.source
                WHERE so.source_type IN ("user-301","user-302")
                GROUP BY so.lead_id
            ) AS ele_source'), 'ele_source.lead_id', '=', 'leads.id');
        } else {
            $query->leftJoin(DB::raw('(
                SELECT so.lead_id,
                group_concat(CONCAT(source_user.first_name," ", source_user.last_name)) as arc_and_ele_name
                FROM lead_sources as so 
                LEFT JOIN users as source_user ON source_user.id = so.source
                WHERE so.source_type IN ("user-201","user-202")
                GROUP BY so.lead_id
            ) AS ele_source'), 'ele_source.lead_id', '=', 'leads.id');
        }
        $query->leftJoin('users as source_user', 'source_user.id', '=', 'source.source');
        // $query->whereIn('lead_sources.source_type', array('user-301', 'user-302'));
        $query->where('leads.is_deal', 0);
        $query->where(function ($query1) use ($request) {
            if ($request->is_arc == 1) {
                $query1->orwhere('leads.architect', $request->id);
            } else {
                $query1->orwhere('leads.electrician', $request->id);
            }
            $query1->orwhere('lead_sources.source', $request->id);
        });
        $Lead_ids = $query->distinct()->pluck('leads.id');
        


        $selectColumns = array(
            'leads.id',
            'leads.status',
            'leads.sub_status',
            'leads.closing_date_time',
            'crm_setting_stage_of_site.name as site_stage_name',
        );

        $searchColumns = [
            'leads.id',
            "CONCAT(leads.first_name,' ',leads.last_name)",
            "CONCAT(lead_ele.first_name,' ',lead_ele.last_name)",
            'ele_source.arc_and_ele_name',
            'crm_setting_stage_of_site.name',
        ];

        $query = Lead::query();
        $query->whereIn('leads.id', $Lead_ids);
        $recordsTotal = $query->count();
        $recordsFiltered = $recordsTotal;

        $LeadData = Lead::query();
        $LeadData->select($selectColumns);
        $LeadData->selectRaw('CONCAT(leads.first_name," ",leads.last_name) AS lead_name');
        $LeadData->selectRaw('CONCAT(lead_ele.first_name," ",lead_ele.last_name) AS main_arc_and_ele_name');
        $LeadData->selectRaw('ele_source.arc_and_ele_name AS source_arc_and_ele_name');
        $LeadData->selectRaw('ch_source.channel_partner_name AS source_channel_partner_name');
        $LeadData->leftJoin('crm_setting_stage_of_site', 'crm_setting_stage_of_site.id', '=', 'leads.site_stage');
        $LeadData->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
        $LeadData->leftJoin('lead_sources as source', 'source.lead_id', '=', 'leads.id');

        $LeadData->leftJoin(DB::raw('(
            SELECT ch.lead_id,
            group_concat(channel_partner.firm_name) as channel_partner_name
            FROM lead_sources as ch 
            LEFT JOIN channel_partner ON channel_partner.user_id = ch.source
            WHERE ch.source_type IN ("user-101","user-102","user-103","user-104","user-105")
            GROUP BY ch.lead_id
            ) AS ch_source'), 'ch_source.lead_id', '=', 'leads.id');
            
        if ($request->is_arc == 1) {
            $LeadData->leftJoin('users as lead_ele', 'lead_ele.id', '=', 'leads.electrician');
            $LeadData->leftJoin(DB::raw('(
                SELECT so.lead_id,
                group_concat(CONCAT(source_user.first_name," ", source_user.last_name)) as arc_and_ele_name
                FROM lead_sources as so 
                LEFT JOIN users as source_user ON source_user.id = so.source
                WHERE so.source_type IN ("user-301","user-302")
                GROUP BY so.lead_id
                ) AS ele_source'), 'ele_source.lead_id', '=', 'leads.id');
        } else {
            $LeadData->leftJoin('users as lead_ele', 'lead_ele.id', '=', 'leads.architect');
            $LeadData->leftJoin(DB::raw('(
                SELECT so.lead_id,
                group_concat(CONCAT(source_user.first_name," ", source_user.last_name)) as arc_and_ele_name
                FROM lead_sources as so 
                LEFT JOIN users as source_user ON source_user.id = so.source
                WHERE so.source_type IN ("user-201","user-202")
                GROUP BY so.lead_id
            ) AS ele_source'), 'ele_source.lead_id', '=', 'leads.id');
        }
        $LeadData->leftJoin('users as source_user', 'source_user.id', '=', 'source.source');
        // $LeadData->whereIn('lead_sources.source_type', array('user-301', 'user-302'));
        $LeadData->where('leads.is_deal', 0);
        $LeadData->where(function ($query) use ($request) {
            if ($request->is_arc == 1) {
                $query->orwhere('leads.architect', $request->id);
            } else {
                $query->orwhere('leads.electrician', $request->id);
            }
            $query->orwhere('lead_sources.source', $request->id);
        });
        if($request->status != 0){
            if($request->status == 1){
                $LeadData->whereIn('leads.status', [1, 2, 3, 4]);
            } else if($request->status == 2){
                $LeadData->where('leads.status', 5);
            }
            else if($request->status == 3){
                $LeadData->where('leads.status', 6);
            }
        }

        $isFilterApply = 0;
        if (isset($request['search']['value'])) {
            $isFilterApply = 1;
            $search_value = $request['search']['value'];
            $LeadData->where(function ($query) use ($search_value, $searchColumns) {
                for ($i = 0; $i < count($searchColumns); $i++) {
                    if ($i == 0) {
                        $query->whereRaw($searchColumns[$i] . ' like ? ', [$search_value]);
                        $query->orWhereRaw($searchColumns[$i] . ' like ? ', ['%' . $search_value . '%']);
                    } else {
                        $query->orWhereRaw($searchColumns[$i] . ' like ? ', [$search_value]);
                        $query->orWhereRaw($searchColumns[$i] . ' like ? ', ['%' . $search_value . '%']);
                    }
                }
            });
        }
        $LeadData->limit($request->length);
        $LeadData->offset($request->start);
        $LeadData->distinct()->pluck('leads.id');

        $data = $LeadData->get();
        $data = json_decode(json_encode($data), true);
        if($isFilterApply === 1){
            $recordsFiltered = count($data);
        }
        $viewData = array();

        foreach ($data as $key => $value) {
            $viewData[$key] = array();
            $url = route('crm.lead') . "?id=" . $value['id'];
            $viewData[$key]['name'] = '<a target="_blank" href="'.$url.'">'.$value['lead_name'].'</a>';
            $viewData[$key]['status'] = '<span>'.getLeadStatus()[$value['status']]['name'].'</span>';
            $viewData[$key]['site_stage'] = '<span>'. $value['site_stage_name'].'</span>';
            $viewData[$key]['arc_and_ele'] = '<span>'. $value['main_arc_and_ele_name'].'</span>';
            $viewData[$key]['channel_partner'] = '<span>'. $value['source_channel_partner_name'].'</span>';
        };




        $Status_count = Lead::query();
        $Status_count->selectRaw('SUM(CASE WHEN leads.status IN (1, 2, 3, 4) THEN 1 ELSE 0 END) as Running_lead, SUM(CASE WHEN leads.status = 5 THEN 1 ELSE 0 END) as Lost_lead, SUM(CASE WHEN leads.status =  6 THEN 1 ELSE 0 END) as Cold_lead ');
        $Status_count->whereIn('leads.id',  $Lead_ids);
        $Status_count = $Status_count->first();
        
        
         $jsonData = array(
            "draw" => intval($request['draw']),
            // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($recordsTotal),
            // total number of records
            "recordsFiltered" => intval($recordsFiltered),
            // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $viewData, // total data array
            "Running_lead" => $Status_count->Running_lead,
            "Lost_lead" => $Status_count->Lost_lead,
            "Cold_lead" => $Status_count->Cold_lead,
            "Total_lead" => intval($Status_count->Running_lead) + intval($Status_count->Lost_lead) + intval($Status_count->Cold_lead)
        );
        return $jsonData;

    }
    
    function viewDealData(Request $request){

        $query = Lead::query();
        $query->select('leads.id');
        $query->leftJoin('crm_setting_stage_of_site', 'crm_setting_stage_of_site.id', '=', 'leads.site_stage');
        $query->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
        $query->leftJoin(DB::raw('(
            SELECT ch.lead_id,
            group_concat(channel_partner.firm_name) as channel_partner_name
            FROM lead_sources as ch 
            LEFT JOIN channel_partner ON channel_partner.user_id = ch.source
            WHERE ch.source_type IN ("user-101","user-102","user-103","user-104","user-105")
            GROUP BY ch.lead_id
        ) AS ch_source'), 'ch_source.lead_id', '=', 'leads.id');

        if ($request->is_arc == 1) {
            $query->leftJoin('users as lead_ele', 'lead_ele.id', '=', 'leads.electrician');
            $query->leftJoin(DB::raw('(
                SELECT so.lead_id,
                group_concat(CONCAT(source_user.first_name," ", source_user.last_name)) as arc_and_ele_name
                FROM lead_sources as so 
                LEFT JOIN users as source_user ON source_user.id = so.source
                WHERE so.source_type IN ("user-301","user-302")
                GROUP BY so.lead_id
                ) AS ele_source'), 'ele_source.lead_id', '=', 'leads.id');
        } else {
            $query->leftJoin('users as lead_ele', 'lead_ele.id', '=', 'leads.architect');
            $query->leftJoin(DB::raw('(
                SELECT so.lead_id,
                group_concat(CONCAT(source_user.first_name," ", source_user.last_name)) as arc_and_ele_name
                FROM lead_sources as so 
                LEFT JOIN users as source_user ON source_user.id = so.source
                WHERE so.source_type IN ("user-201","user-202")
                GROUP BY so.lead_id
            ) AS ele_source'), 'ele_source.lead_id', '=', 'leads.id');
        }

        $query->where(function ($query1) use ($request) {
            if ($request->is_arc == 1) {
                $query1->orwhere('leads.architect', $request->id);
            } else {
                $query1->orwhere('leads.electrician', $request->id);
            }
            $query1->orwhere('lead_sources.source', $request->id);
        });
        
        $Deal_ids = $query->distinct()->pluck('leads.id');

        $selectColumns = array(
            'leads.id',
            'leads.status',
            'leads.sub_status',
            'leads.reward_status',
            'leads.closing_date_time',
            'leads.total_point',
            'leads.telesales_verification',
            'crm_setting_stage_of_site.name as site_stage_name',
        );

        $searchColumns = [
            'leads.id',
            "CONCAT(leads.first_name,' ',leads.last_name)",
            "CONCAT(lead_ele.first_name,' ',lead_ele.last_name)",
            'ele_source.arc_and_ele_name',
            'crm_setting_stage_of_site.name',
        ];
        
        $query = Lead::query();
        $query->whereIn('leads.id', $Deal_ids);
        $recordsTotal = $query->count();
        $recordsFiltered = $recordsTotal;

        $DealData = Lead::query();
        $DealData->select($selectColumns);
        $DealData->selectRaw('leads.quotation as quotation_old_netamount');
        $DealData->selectRaw('quotationdata.quotation_amount as quotation_new_netamount');
        $DealData->selectRaw('case when leads.quotation > 0 THEN leads.quotation else quotationdata.quotation_amount end as total');
        $DealData->selectRaw('CONCAT(leads.first_name," ",leads.last_name) AS deal_name');
        $DealData->selectRaw('CONCAT(lead_ele.first_name," ",lead_ele.last_name) AS main_arc_and_ele_name');
        $DealData->selectRaw('ele_source.arc_and_ele_name AS source_arc_and_ele_name');
        $DealData->selectRaw('ch_source.channel_partner_name AS source_channel_partner_name');
        $DealData->leftJoin('crm_setting_stage_of_site', 'crm_setting_stage_of_site.id', '=', 'leads.site_stage');
        $DealData->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
        
        $DealData->leftJoin(DB::raw('(
            SELECT ch.lead_id,
            group_concat(channel_partner.firm_name) as channel_partner_name
            FROM lead_sources as ch 
            LEFT JOIN channel_partner ON channel_partner.user_id = ch.source
            WHERE ch.source_type IN ("user-101","user-102","user-103","user-104","user-105")
            GROUP BY ch.lead_id
        ) AS ch_source'), 'ch_source.lead_id', '=', 'leads.id');
        if ($request->is_arc == 1) {
            $DealData->leftJoin('users as lead_ele', 'lead_ele.id', '=', 'leads.electrician');
            $DealData->leftJoin(DB::raw('(
                SELECT so.lead_id,
                group_concat(CONCAT(source_user.first_name," ", source_user.last_name)) as arc_and_ele_name
                FROM lead_sources as so 
                LEFT JOIN users as source_user ON source_user.id = so.source
                WHERE so.source_type IN ("user-301","user-302")
                GROUP BY so.lead_id
                ) AS ele_source'), 'ele_source.lead_id', '=', 'leads.id');
        } else {
            $DealData->leftJoin('users as lead_ele', 'lead_ele.id', '=', 'leads.architect');
            $DealData->leftJoin(DB::raw('(
                SELECT so.lead_id,
                group_concat(CONCAT(source_user.first_name," ", source_user.last_name)) as arc_and_ele_name
                FROM lead_sources as so 
                LEFT JOIN users as source_user ON source_user.id = so.source
                WHERE so.source_type IN ("user-201","user-202")
                GROUP BY so.lead_id
            ) AS ele_source'), 'ele_source.lead_id', '=', 'leads.id');
        }
       

        $DealData->leftJoin(DB::raw('(
            SELECT quot.id,quot.inquiry_id,
            quotaitemdetail.quotation_amount 
            FROM wltrn_quotation as quot 
            LEFT JOIN (
                SELECT quotitem.quot_id,
                quotitem.quotgroup_id,
                SUM(quotitem.net_amount) as quotation_amount 
                FROM wltrn_quot_itemdetails as quotitem 
                WHERE quotitem.isactiveroom = 1 
                AND quotitem.isactiveboard = 1 
                GROUP BY quotitem.quot_id,quotitem.quotgroup_id ) as quotaitemdetail ON quotaitemdetail.quot_id = quot.id
            WHERE quot.status = 3 
            AND quot.isfinal = 1 
            ORDER BY quot.id,quot.inquiry_id,quotaitemdetail.quotation_amount DESC LIMIT 1
        ) AS quotationdata'), 'quotationdata.inquiry_id', '=', 'leads.id');
        $DealData->where('leads.is_deal', 1);
        $DealData->where(function ($query) use ($request) {
            if ($request->is_arc == 1) {
                $query->orwhere('leads.architect', $request->id);
            } else {
                $query->orwhere('leads.electrician', $request->id);
            }
            $query->orwhere('lead_sources.source', $request->id);
        });
            
        if($request->status != 0){
            if($request->status == 1){
                $DealData->whereIn('leads.status', [100, 101, 102]);
            } else if($request->status == 2){
                $DealData->where('leads.status', 103);
            }
            else if($request->status == 3){
                $DealData->where('leads.status', 104);
            }
            else if($request->status == 4){
                $DealData->where('leads.status', 105);
            }
        }

        $isFilterApply = 0;
        if (isset($request['search']['value'])) {
            $isFilterApply = 1;
            $search_value = $request['search']['value'];
            $DealData->where(function ($query) use ($search_value, $searchColumns) {
                for ($i = 0; $i < count($searchColumns); $i++) {
                    if ($i == 0) {
                        $query->whereRaw($searchColumns[$i] . ' like ? ', [$search_value]);
                        $query->orWhereRaw($searchColumns[$i] . ' like ? ', ['%' . $search_value . '%']);
                    } else {
                        $query->orWhereRaw($searchColumns[$i] . ' like ? ', [$search_value]);
                        $query->orWhereRaw($searchColumns[$i] . ' like ? ', ['%' . $search_value . '%']);
                    }
                }
            });
        }
        $DealData->limit($request->length);
        $DealData->offset($request->start);
        $DealData->distinct()->pluck('leads.id');
        $DealData = $DealData->get();

        $data = json_decode(json_encode($DealData), true);
        if($isFilterApply === 1){
            $recordsFiltered = count($data);
        }
        $viewData = array();

        foreach ($data as $key => $value) {
            $viewData[$key] = array();
            $url = route('crm.deal') . "?id=" . $value['id'];
        
            $is_verify = '<i class="bx bxs-phone bx-tada text-warning font-size-16 me-1" style="text-decoration: underline;"></i>';
            if($value['telesales_verification'] == 2){
                $is_verify = '<i class="bx bxs-badge-check bx-tada text-success font-size-16 me-1" ></i>';
            }elseif ($value['telesales_verification'] == 3) {
                $is_verify = '<i class="bx bxs-phone bx-tada text-danger font-size-16 me-1" style="text-decoration: underline;"></i>';
            }
            $viewData[$key]['name'] = '<div class="d-flex align-items-center">'.$is_verify.'<a target="_blank" href="'.$url.'">  '.$value['deal_name'].'</a></div>';
            $viewData[$key]['amount'] = '<span>'.isset($value['total']) ? $value['total'] : 0 .'</span>';
            $viewData[$key]['status'] = '<span>'.getLeadStatus()[$value['status']]['name'].'</span>';

            $closing_date = '-';
            if($value['closing_date_time'] != null || $value['closing_date_time'] != ''){
                $closing_date = date('Y-m-d', strtotime($value['closing_date_time']));
            }

            $viewData[$key]['closing_date'] = '<span>'.$closing_date.'</span>';
            $viewData[$key]['site_stage'] = '<span>'. $value['site_stage_name'].'</span>';
            $viewData[$key]['arc_and_ele'] = '<span>'. $value['main_arc_and_ele_name'].'</span>';
            $viewData[$key]['channel_partner'] = '<span>'. $value['source_channel_partner_name'].'</span>';

            if($value['status'] == 103 && $value['total_point'] != 0){

                $objPoint = LeadFile::query();
                $objPoint->selectRaw('SUM(lead_files.point) AS point');
                $objPoint->where('lead_files.lead_id', $value['id']);
                $objPoint->where('lead_files.hod_approved', 1);
                $objPoint = $objPoint->first();
                if($objPoint){
                    $viewData[$key]['action'] = '<span class="text-primary" onclick="OpenClaimRewardModal('.$value['id'].')">'.$objPoint['point'].'</span>';
                }else {
                    $viewData[$key]['action'] = '<span>'.$value['total_point'].'</span>';
                }
            } else {
                $viewData[$key]['action'] = '<span>'.$value['total_point'].'</span>';
            }
        };



        $Status_count = Lead::query();
        $Status_count->selectRaw('SUM(CASE WHEN leads.status IN (100, 101, 102) THEN 1 ELSE 0 END) as Running_deal, SUM(CASE WHEN leads.status = 103 THEN 1 ELSE 0 END) as Won_deal, SUM(CASE WHEN leads.status = 104 THEN 1 ELSE 0 END) as Lost_deal, SUM(CASE WHEN leads.status =  105 THEN 1 ELSE 0 END) as Cold_deal ');
        $Status_count->whereIn('leads.id', $Deal_ids);
        $Status_count = $Status_count->first();


        $jsonData = array(
            "draw" => intval($request['draw']),
            // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($recordsTotal),
            // total number of records
            "recordsFiltered" => intval($recordsFiltered),
            // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $viewData, // total data array
            "Running_deal" => $Status_count->Running_deal,
            "Won_deal" => $Status_count->Won_deal,
            "Lost_deal" => $Status_count->Lost_deal,
            "Cold_deal" => $Status_count->Cold_deal,
            "Total_deal" => intval($Status_count->Running_deal) + intval($Status_count->Won_deal) + intval($Status_count->Lost_deal) + intval($Status_count->Cold_deal)
        );

        return $jsonData;
    }
    function viewChannelPartnerDealAjax(Request $request){

        $query = Lead::query();
        $query->select('leads.id');
        $query->leftJoin('crm_setting_stage_of_site', 'crm_setting_stage_of_site.id', '=', 'leads.site_stage');
        $query->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
        $query->leftJoin(DB::raw('(
            SELECT ch.lead_id,
            group_concat(channel_partner.firm_name) as channel_partner_name
            FROM lead_sources as ch 
            LEFT JOIN channel_partner ON channel_partner.user_id = ch.source
            WHERE ch.source_type IN ("user-101","user-102","user-103","user-104","user-105")
            GROUP BY ch.lead_id
        ) AS ch_source'), 'ch_source.lead_id', '=', 'leads.id');

        $query->where('leads.is_deal', 1);
        $query->where(function ($query1) use ($request) {
            $query1->orwhere('lead_sources.source', $request->id);
        });
        
        $Deal_ids = $query->distinct()->pluck('leads.id');

        $selectColumns = array(
            'leads.id',
            'leads.status',
            'leads.phone_number',

            'leads.house_no',
            'leads.addressline1',
            'leads.addressline2',
            'leads.area',
            'leads.pincode',

            'leads.sub_status',
            'leads.reward_status',
            'leads.closing_date_time',
            'leads.total_point',
            'leads.telesales_verification',
            'crm_setting_stage_of_site.name as site_stage_name',
        );

        $searchColumns = [
            'leads.id',
            "CONCAT(leads.first_name,' ',leads.last_name)",
            "CONCAT(lead_ele.first_name,' ',lead_ele.last_name)",
            'ele_source.arc_and_ele_name',
            'crm_setting_stage_of_site.name',
        ];
        
        $query = Lead::query();
        $query->whereIn('leads.id', $Deal_ids);
        $recordsTotal = $query->count();
        $recordsFiltered = $recordsTotal;

        $DealData = Lead::query();
        $DealData->select($selectColumns);
        $DealData->selectRaw('CONCAT(leads.first_name," ",leads.last_name) AS deal_name');
        $DealData->selectRaw('CONCAT(lead_ele.first_name," ",lead_ele.last_name) AS main_electrician_name');
        $DealData->selectRaw('CONCAT(lead_arc.first_name," ",lead_arc.last_name) AS main_architect_name');
        $DealData->leftJoin('crm_setting_stage_of_site', 'crm_setting_stage_of_site.id', '=', 'leads.site_stage');
        $DealData->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
        
        $DealData->leftJoin(DB::raw('(
            SELECT ch.lead_id,
            group_concat(channel_partner.firm_name) as channel_partner_name
            FROM lead_sources as ch 
            LEFT JOIN channel_partner ON channel_partner.user_id = ch.source
            WHERE ch.source_type IN ("user-101","user-102","user-103","user-104","user-105")
            GROUP BY ch.lead_id
        ) AS ch_source'), 'ch_source.lead_id', '=', 'leads.id');

        $DealData->leftJoin('users as lead_ele', 'lead_ele.id', '=', 'leads.electrician');
        $DealData->leftJoin('users as lead_arc', 'lead_arc.id', '=', 'leads.architect');
        
        $DealData->where('leads.is_deal', 1);
        $DealData->whereIn('leads.id', $Deal_ids);
        if($request->status != 0){
            if($request->status == 1){
                $DealData->whereIn('leads.status', [100, 101, 102]);
            } else if($request->status == 2){
                $DealData->where('leads.status', 103);
            }
            else if($request->status == 3){
                $DealData->where('leads.status', 104);
            }
            else if($request->status == 4){
                $DealData->where('leads.status', 105);
            }
        }

        $isFilterApply = 0;
        if (isset($request['search']['value'])) {
            $isFilterApply = 1;
            $search_value = $request['search']['value'];
            $DealData->where(function ($query) use ($search_value, $searchColumns) {
                for ($i = 0; $i < count($searchColumns); $i++) {
                    if ($i == 0) {
                        $query->whereRaw($searchColumns[$i] . ' like ? ', [$search_value]);
                        $query->orWhereRaw($searchColumns[$i] . ' like ? ', ['%' . $search_value . '%']);
                    } else {
                        $query->orWhereRaw($searchColumns[$i] . ' like ? ', [$search_value]);
                        $query->orWhereRaw($searchColumns[$i] . ' like ? ', ['%' . $search_value . '%']);
                    }
                }
            });
        }
        $DealData->limit($request->length);
        $DealData->offset($request->start);
        $DealData->distinct()->pluck('leads.id');
        $DealData = $DealData->get();

        $data = json_decode(json_encode($DealData), true);
        if($isFilterApply === 1){
            $recordsFiltered = count($data);
        }
        $viewData = array();

        foreach ($data as $key => $value) {
            $viewData[$key] = array();
            $url = route('crm.deal') . "?id=" . $value['id'];
        
            $is_verify = '<i class="bx bxs-phone bx-tada text-warning font-size-16 me-1" style="text-decoration: underline;"></i>';
            if($value['telesales_verification'] == 2){
                $is_verify = '<i class="bx bxs-badge-check bx-tada text-success font-size-16 me-1" ></i>';
            }elseif ($value['telesales_verification'] == 3) {
                $is_verify = '<i class="bx bxs-phone bx-tada text-danger font-size-16 me-1" style="text-decoration: underline;"></i>';
            }
            $viewData[$key]['name'] = '<div class="d-flex align-items-center">'.$is_verify.'<a target="_blank" href="'.$url.'">  '.$value['deal_name'].'</a></div>';
            $viewData[$key]['phone_number'] = '<span>'.isset($value['phone_number']) ? $value['phone_number'] : '-' .'</span>';

            $address = isset($value['house_no'])?$value['house_no']:'';
            $address .= isset($value['addressline1'])?', '.$value['addressline1']:'';
            $address .= isset($value['addressline2'])?', '.$value['addressline2']:'';
            $address .= isset($value['area'])?', '.$value['area']:'';
            $address .= isset($value['pincode'])?', '.$value['pincode']:'';
            $viewData[$key]['address'] = '<span>'.$address.'</span>';

            $closing_date = '-';
            if($value['closing_date_time'] != null || $value['closing_date_time'] != ''){
                $closing_date = date('Y-m-d', strtotime($value['closing_date_time']));
            }
            $viewData[$key]['closing_date'] = '<span>'.$closing_date.'</span>';
            $viewData[$key]['main_architect_name'] = '<span>'. $value['main_architect_name'].'</span>';
            $viewData[$key]['main_electrician_name'] = '<span>'. $value['main_electrician_name'].'</span>';
            $viewData[$key]['status'] = '<span>'.getLeadStatus()[$value['status']]['name'].'</span>';
        };



        $Status_count = Lead::query();
        $Status_count->selectRaw('SUM(CASE WHEN leads.status IN (100, 101, 102) THEN 1 ELSE 0 END) as Running_deal, SUM(CASE WHEN leads.status = 103 THEN 1 ELSE 0 END) as Won_deal, SUM(CASE WHEN leads.status = 104 THEN 1 ELSE 0 END) as Lost_deal, SUM(CASE WHEN leads.status =  105 THEN 1 ELSE 0 END) as Cold_deal ');
        $Status_count->whereIn('leads.id', $Deal_ids);
        $Status_count = $Status_count->first();


        $jsonData = array(
            "draw" => intval($request['draw']),
            // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($recordsTotal),
            // total number of records
            "recordsFiltered" => intval($recordsFiltered),
            // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $viewData, // total data array
            "newdata" => $data, // total data array
            "Running_deal" => $Status_count->Running_deal,
            "Won_deal" => $Status_count->Won_deal,
            "Lost_deal" => $Status_count->Lost_deal,
            "Cold_deal" => $Status_count->Cold_deal,
            "Total_deal" => intval($Status_count->Running_deal) + intval($Status_count->Won_deal) + intval($Status_count->Lost_deal) + intval($Status_count->Cold_deal)
        );

        return $jsonData;
    }

    function userStatusChange(Request $request) {
        $AllUserTypes = getAllUserTypes();
        if(isset($request->id) && $request->id != 0) {
            $User = User::find($request->id);
            $oldStatus = $User->status;
            $newStatus = $request->status;
            if ($request->status == 1) {
                $User->status = 0;
                $User->save();

                if($User){
                    $timeline = array();
                    $timeline['user_id'] = $User->id;
                    $timeline['log_type'] = "user";
                    $timeline['field_name'] = "status";
                    $timeline['old_value'] = $oldStatus;
                    $timeline['new_value'] = $newStatus;
                    $timeline['reference_type'] = "user";
                    $timeline['reference_id'] = $User->id;
                    $timeline['transaction_type'] = "update";
                    $timeline['description'] = "User Inactive ";
                    $timeline['source'] = "WEB";
                    $timeline['ip'] = $request->ip();
                    saveUserLog($timeline);
                }
                $response = successRes("SuccessFully User Deactive");
            } else if ($request->status == 0) {
                $alreadyEmail = User::query();
                $alreadyEmail->where('email', $User->email);
                $alreadyEmail->where('type', '!=', 10000);
                $alreadyEmail->where('status', '=', 1);
                $alreadyEmail = $alreadyEmail->first();


                $alreadyPhoneNumber = User::query();
                $alreadyPhoneNumber->where('phone_number', $User->phone_number);
                $alreadyPhoneNumber->where('type', '!=', 10000);
                $alreadyPhoneNumber->where('status', '=', 1);
                $alreadyPhoneNumber = $alreadyPhoneNumber->first();

                if ($alreadyEmail) {
                    $response = errorRes('Email already exists(' . $AllUserTypes[$alreadyEmail->type]['name'] . '), Try with another email');
                } elseif ($alreadyPhoneNumber) {
                    $response = errorRes('Phone number already exists(' . $AllUserTypes[$alreadyPhoneNumber->type]['name'] . '), Try with another phone number');
                } else {
                    $User->status = 1;
                    $User->save();
                    if($User){
                        $timeline = array();
                        $timeline['user_id'] = $User->id;
                        $timeline['log_type'] = "user";
                        $timeline['field_name'] = "status";
                        $timeline['old_value'] = $oldStatus;
                        $timeline['new_value'] = $newStatus;
                        $timeline['reference_type'] = "user";
                        $timeline['reference_id'] = $User->id;
                        $timeline['transaction_type'] = "update";
                        $timeline['description'] = "User Active ";
                        $timeline['source'] = "WEB";
                        $timeline['ip'] = $request->ip();
                        saveUserLog($timeline);
                    }
                    $response = successRes("SuccessFully User Active");
                }

            }
            $response['user_id'] = $User->id;
        } else {
            $response = errorRes("Please Valid User Id Pass");
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }
}
