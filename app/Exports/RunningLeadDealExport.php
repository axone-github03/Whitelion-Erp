<?php

namespace App\Exports;

use App\Models\Lead;
use App\Models\LeadQuestionAnswer;
use App\Models\LeadQuestionOptions;
use App\Models\LeadStatusUpdate;
use App\Models\ChannelPartner;
use Maatwebsite\Excel\Concerns\FromCollection;

class RunningLeadDealExport implements FromCollection
{

    public function __construct()
    {

    }

    public function collection()
    {
        $lstleadData = array();

        $objNewItemHeader['lead_id'] = 'ID';
        $objNewItemHeader['type'] = 'Type';
        $objNewItemHeader['client_name'] = 'Deal Client Name';
        $objNewItemHeader['contact_number'] = 'Deal Contact Number';
        $objNewItemHeader['city'] = 'City';
        $objNewItemHeader['house_number'] = 'Address House Number';
        $objNewItemHeader['building_name'] = 'Address - Building Name';
        $objNewItemHeader['quotation_amount'] = 'Quotation Amount';
        $objNewItemHeader['status'] = 'Status';
        $objNewItemHeader['source_type'] = 'Source type';
        $objNewItemHeader['source_name'] = 'Source Name';
        $objNewItemHeader['architect_name'] = 'Architect Name';
        $objNewItemHeader['electrician_name'] = 'Electrician Name';
        $objNewItemHeader['lead_owner'] = 'Lead Owner';
        $objNewItemHeader['site_stage'] = 'Site stage';
        array_push($lstleadData,$objNewItemHeader);


        $column = array(
            'leads.id AS id',
            'leads.phone_number AS phone_number',
            'leads.house_no',
            'leads.addressline1',
            'leads.addressline2',
            'leads.area',
            'leads.pincode',
            'city.name AS city_name',
            'crm_setting_stage_of_site.name AS site_stage',
            'quot.quot_whitelion_amount',
            'quot.quot_billing_amount',
            'quot.quot_other_amount',
            'quot.quot_total_amount',
            
        );
        $Query = Lead::query();
        $Query->select($column);
        $Query->selectRaw('CASE
        WHEN leads.is_deal = 0 THEN "Lead"
        WHEN leads.is_deal = 1 THEN "Deal"
        ELSE "Undifine"
        END AS type');
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
        WHEN leads.source_type = "user-4" THEN "Marketing activities"
        WHEN leads.source_type = "textnotrequired-1" THEN "Facebook"
        WHEN leads.source_type = "textnotrequired-11" THEN "Instagram"
        WHEN leads.source_type = "textnotrequired-12" THEN "Google Ads"
        WHEN leads.source_type = "fix-3" THEN "Cold call"
        ELSE leads.source_type
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
        WHEN leads.source_type = "fix-3" THEN leads.source
        ELSE leads.source
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
        $Query->selectRaw('MONTHNAME(leads.closing_date_time) AS month_number');
        $Query->selectRaw('CONCAT(users.first_name," ",users.last_name) AS owner_name');
        $Query->selectRaw('CONCAT(architect.first_name," ",architect.last_name) AS architect_name');
        $Query->selectRaw('CONCAT(electrician.first_name," ",electrician.last_name) AS electrician_name');
        $Query->selectRaw('DATE(leads.created_at) AS created_date');
        $Query->selectRaw('CONCAT(created.first_name," ",created.last_name) AS created_by');
        $Query->leftJoin('users', 'users.id', '=', 'leads.assigned_to');
        $Query->leftJoin('city_list as city', 'city.id', '=', 'leads.city_id');
        $Query->leftJoin('wltrn_quotation as quot', function($join){
            $join->on('quot.inquiry_id', '=', 'leads.id');
            $join->where('quot.isfinal', 1);
        });
        $Query->leftJoin('users AS source_1', 'source_1.id', '=', 'leads.source');
        $Query->leftJoin('users AS created', 'created.id', '=', 'leads.created_by');
        // $Query->leftJoin('users AS created', 'created.id', '=', 'leads.created_by');
        $Query->leftJoin('users AS architect', 'architect.id', '=', 'leads.architect');
        $Query->leftJoin('users AS electrician', 'electrician.id', '=', 'leads.electrician');
        $Query->leftJoin('channel_partner AS source_2', 'source_2.user_id', '=', 'leads.source');
        $Query->leftJoin('exhibition AS source_3', 'source_3.id', '=', 'leads.source');
        $Query->leftJoin('crm_setting_stage_of_site', 'crm_setting_stage_of_site.id', '=', 'leads.site_stage');
        // MARKETING RUNNING LEAD AND DEAL START
        // $Query->whereIn('leads.source_type', ['user-4','textnotrequired-1','textnotrequired-11','textnotrequired-12']);
        // $Query->whereIn('leads.status', array(1,2,3,4,100,101,102));
        // MARKETING RUNNING LEAD AND DEAL END
        // EXHIBITION RUNNING LEAD AND DEAL START
        // $Query->whereIn('leads.source_type', ['user-4','textnotrequired-1','textnotrequired-11','textnotrequired-12']);
        $Query->whereIn('leads.status', array(1,2,3,4,100,101,102));
        $Query->whereIn('leads.source_type', ['exhibition-9']);
        // EXHIBITION RUNNING LEAD AND DEAL END
        // $Query->where('users.status', 0);
        $Query->orderBy('leads.assigned_to', 'asc');
        $leaddata = $Query->get();

        
        foreach ($leaddata as $lead_key => $lead_value) {
            
            $objNewItem['lead_id'] = $lead_value['id'];
            $objNewItem['type'] = $lead_value['type'];
            $objNewItem['client_name'] = $lead_value['client_name'];
            $objNewItem['contact_number'] = $lead_value['phone_number'];
            $objNewItem['city'] = $lead_value['city_name'];
            $objNewItem['house_number'] = $lead_value['house_no'];
            $objNewItem['building_name'] = $lead_value['addressline1'];
            $objNewItem['quotation_amount'] = $lead_value['quot_total_amount'];
            $objNewItem['status'] = $lead_value['status'];
            $objNewItem['source_type'] = $lead_value['source_type'];
            $objNewItem['source_name'] = $lead_value['source'];
            $objNewItem['architect_name'] = $lead_value['architect_name'];
            $objNewItem['electrician_name'] = $lead_value['electrician_name'];
            $objNewItem['lead_owner'] = $lead_value['owner_name'];
            $objNewItem['site_stage'] = $lead_value['site_stage'];

            array_push($lstleadData,$objNewItem);
            
		}


        return collect($lstleadData);
    }
}
