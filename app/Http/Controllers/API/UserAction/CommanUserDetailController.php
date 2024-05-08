<?php

namespace App\Http\Controllers\API\UserAction;

use App\Http\Controllers\Controller;
use App\Models\TagMaster;
use App\Models\Lead;
use App\Models\User;
use DB;
use Illuminate\Http\Request;

class CommanUserDetailController extends Controller
{
    function searchTimeSlot(Request $request)
    {
        $searchKeyword = isset($request->q) ? $request->q : "";

        $ReminderTimeSlot = getTimeSlot();

        $finalArray = array();
        foreach ($ReminderTimeSlot as $key => $value) {
            array_push($finalArray, ["id" => $value, "text" => $value]);
        }

        $response = successRes();
		$response['data'] = $finalArray;
        return response()->json($response)->header('Content-Type', 'application/json');
    }
    function searchReminderTimeSlot(Request $request)
    {
        $searchKeyword = isset($request->q) ? $request->q : "";

        $ReminderTimeSlot = getReminderTimeSlot();

        $finalArray = array();
        foreach ($ReminderTimeSlot as $key => $value) {
            array_push($finalArray, ["id" => $value['id'], "text" => $value['name']]);
        }

        $response = successRes();
		$response['data'] = $finalArray;
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

        $response = successRes();
		$response['data'] = $data;
        return response()->json($response)->header('Content-Type', 'application/json');
    }
    function saveUserDetail(Request $request)
    {
        $user = User::find($request->id);
        if ($user) {
            $Tag_id = isset($request->user_tag) ? $request->user_tag : "";
            if ($Tag_id != "") {
                if ($user->tag !=  implode(',', $Tag_id)) {
                    $new_value = $Tag_id;
                    $old_value = $user->tag;

                    $New_Tag = '';
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

                    $Old_Tag = '';
                    if ($old_value != 0 || $old_value != '') {
                        $Tag = TagMaster::select('id', 'tagname as text');
                        $Tag->whereIn('tag_master.id', $new_value);
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
                    $timeline = array();
                    $timeline['user_id'] = $user->id;
                    $timeline['log_type'] = "user";
                    $timeline['field_name'] = "tag";
                    $timeline['old_value'] = $Old_Tag;
                    $timeline['new_value'] = $New_Tag;
                    $timeline['reference_type'] = "user";
                    $timeline['reference_id'] = "0";
                    $timeline['transaction_type'] = "updat";
                    $timeline['description'] = "Tag Change : " . $Old_Tag . " To " . $New_Tag;
                    $timeline['source'] = "web";
                    $timeline['ip'] = $request->ip();
                    saveUserLog($timeline);
                }
                $response = successRes("Succssfully Update Detail");
                return response()->json($response)->header('Content-Type', 'application/json');
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

        $query = Lead::query();
        $query->whereIn('leads.id', $Lead_ids);
        $query->limit($request->length);
        $query->offset($request->start);
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
        $LeadData->limit($request->length);
        $LeadData->offset($request->start);
        $LeadData->distinct()->pluck('leads.id');

        $data = $LeadData->get();
        $data = json_decode(json_encode($data), true);
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
            'crm_setting_stage_of_site.name as site_stage_name',
        );

       
        
        $query = Lead::query();
        $query->whereIn('leads.id', $Deal_ids);
        $query->limit($request->length);
        $query->offset($request->start);
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
        $DealData->limit($request->length);
        $DealData->offset($request->start);
        $DealData->distinct()->pluck('leads.id');
        $DealData = $DealData->get();

        $data = json_decode(json_encode($DealData), true);
        $viewData = array();

        foreach ($data as $key => $value) {
            $viewData[$key] = array();
            $url = route('crm.deal') . "?id=" . $value['id'];
            $viewData[$key]['name'] = '<a target="_blank" href="'.$url.'">'.$value['deal_name'].'</a>';
            $viewData[$key]['amount'] = '<span>'.isset($value['total']) ? $value['total'] : 0 .'</span>';
            $viewData[$key]['status'] = '<span>'.getLeadStatus()[$value['status']]['name'].'</span>';
            $viewData[$key]['closing_date'] = '<span>'.date('Y-m-d', strtotime($value['closing_date_time'])).'</span>';
            $viewData[$key]['site_stage'] = '<span>'. $value['site_stage_name'].'</span>';
            $viewData[$key]['arc_and_ele'] = '<span>'. $value['main_arc_and_ele_name'].'</span>';
            $viewData[$key]['channel_partner'] = '<span>'. $value['source_channel_partner_name'].'</span>';
            if($value['status'] == 103 && $value['total_point'] != 0){
                $viewData[$key]['action'] = '<span class="text-primary" onclick="OpenClaimRewardModal('.$value['id'].')">'.$value['total_point'].'</span>';
            } 
            else {
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
}
