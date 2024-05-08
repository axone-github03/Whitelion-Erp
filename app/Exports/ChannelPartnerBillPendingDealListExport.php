<?php

namespace App\Exports;

use App\Models\Lead;
use App\Models\LeadFile;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Support\Facades\DB;

class ChannelPartnerBillPendingDealListExport implements FromCollection
{
    protected $user_id;

    public function __construct($user_id)
    {
        $this->user_id = $user_id;
    }
    
    public function collection()
    {
        $user_id = $this->user_id;
        $lstleadData = array();

        $objNewItemHeader['lead_id'] = 'Lead Id';
        $objNewItemHeader['lead_name'] = 'Lead name';
        $objNewItemHeader['phone_number'] = 'Phone Number';
        $objNewItemHeader['address'] = 'Address';
        $objNewItemHeader['closing_date'] = 'Closing Date';
        $objNewItemHeader['main_architect_name'] = 'Architect Name';
        $objNewItemHeader['main_electrician_name'] = 'Electrician Name';
        $objNewItemHeader['channel_partner_name'] = 'Channel Partner Name';
        $objNewItemHeader['status'] = 'Status';
        array_push($lstleadData,$objNewItemHeader);

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
            'ch_source.channel_partner_name',
            // 'wltrn_quotation.quot_whitelion_amount',
            // 'wltrn_quotation.quot_billing_amount',
            // 'wltrn_quotation.quot_other_amount',
            // 'wltrn_quotation.quot_total_amount',
        );

        $DealData = Lead::query();
        $DealData->select($selectColumns);
        $DealData->selectRaw('CONCAT(leads.first_name," ",leads.last_name) AS deal_name');
        $DealData->selectRaw('CONCAT(lead_ele.first_name," ",lead_ele.last_name) AS main_electrician_name');
        $DealData->selectRaw('CONCAT(lead_arc.first_name," ",lead_arc.last_name) AS main_architect_name');
        $DealData->leftJoin('crm_setting_stage_of_site', 'crm_setting_stage_of_site.id', '=', 'leads.site_stage');
        $DealData->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
        // $DealData->leftJoin('wltrn_quotation', function($join) {
        //     $join->on('wltrn_quotation.inquiry_id', '=', 'leads.id');
        //     $join->where('wltrn_quotation.isfinal', '=', 1);
        // });
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
        $DealData->where('leads.status', 103);
        $DealData->where(function ($query1) use ($user_id) {
            $query1->orwhere('lead_sources.source', $user_id);
        });

        $DealData->distinct()->pluck('leads.id');
        $DealData = $DealData->get();

        $data = json_decode(json_encode($DealData), true);

        foreach ($data as $key => $value) {

            $billcount = LeadFile::query()->where('lead_files.lead_id', '=', $value['id'])->where('lead_files.file_tag_id', '=', 3)->count();
            if($billcount == 0){
                $objNewItem['lead_id'] = $value['id'];
                $objNewItem['lead_name'] = $value['deal_name'];
                $objNewItem['phone_number'] = isset($value['phone_number']) ? $value['phone_number'] : '-';
    
                $address = isset($value['house_no'])?$value['house_no']:'';
                $address .= isset($value['addressline1'])?', '.$value['addressline1']:'';
                $address .= isset($value['addressline2'])?', '.$value['addressline2']:'';
                $address .= isset($value['area'])?', '.$value['area']:'';
                $address .= isset($value['pincode'])?', '.$value['pincode']:'';
                $objNewItem['address'] = $address;
    
                $closing_date = '-';
                if($value['closing_date_time'] != null || $value['closing_date_time'] != ''){
                    $closing_date = date('Y-m-d', strtotime($value['closing_date_time']));
                }
                $objNewItem['closing_date'] = $closing_date;
                $objNewItem['main_architect_name'] =  $value['main_architect_name'];
                $objNewItem['main_electrician_name'] =  $value['main_electrician_name'];
                $objNewItem['channel_partner_name'] =  $value['channel_partner_name'];
                $objNewItem['status'] = getLeadStatus()[$value['status']]['name'];
    
                array_push($lstleadData,$objNewItem);
            }


        };

        return collect($lstleadData);
    }
}
