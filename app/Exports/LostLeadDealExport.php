<?php

namespace App\Exports;

use App\Models\Lead;
use App\Models\LeadQuestionAnswer;
use App\Models\LeadQuestionOptions;
use App\Models\LeadStatusUpdate;
use App\Models\ChannelPartner;
use Maatwebsite\Excel\Concerns\FromCollection;

class LostLeadDealExport implements FromCollection
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate,$endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        $lstleadData = array();

        $objNewItemHeader['client_name'] = 'Client Name';
        $objNewItemHeader['contact_number'] = 'Contact Number';
        $objNewItemHeader['city'] = 'City';
        $objNewItemHeader['house_number'] = 'Address House Number';
        $objNewItemHeader['building_name'] = 'Address - Building Name';
        $objNewItemHeader['quotation_amount'] = 'Quotation Amount';
        $objNewItemHeader['billing_amount'] = 'Whitelion Billing Amount';
        $objNewItemHeader['source_type'] = 'Source type';
        $objNewItemHeader['source_name'] = 'Source Name';
        $objNewItemHeader['architect_name'] = 'Architect Name';
        $objNewItemHeader['electrician_name'] = 'Electrician Name';
        $objNewItemHeader['lead_owner'] = 'Lead Owner';
        $objNewItemHeader['lost_status_date'] = 'Lost Status Date & Time';
        $objNewItemHeader['lost_by'] = 'Lost status by';
        // $objNewItemHeader['client_selected_which_product'] = 'Q1. Client selected which product?';
        // $objNewItemHeader['reason_of_rejection'] = 'Q2. Reason of Rejection?';
        // $objNewItemHeader['compitator_name'] = 'Q3. Company Name?';
        // $objNewItemHeader['notes'] = 'Q4. Notes';
        $objNewItemHeader['closing_date'] = 'Closing date';
        $objNewItemHeader['site_stage'] = 'Site stage';
        array_push($lstleadData,$objNewItemHeader);


        $startDate = date('Y-m-d', strtotime($this->startDate));
		$endDate = date('Y-m-d', strtotime($this->endDate));

        $Lead = Lead::query();
		$Lead->leftJoin('lead_status_updates as lead_status_detail', function ($join) {
			$join->select('lead_status_detail.new_status', 'lead_status_detail.created_at');
			$join->on('lead_status_detail.lead_id', '=', 'leads.id');
			$join->whereIn('lead_status_detail.new_status', array(5, 104));
			$join->orderBy('lead_status_detail.created_at', 'DESC');
			$join->limit(1);
		});
		$Lead->whereDate('lead_status_detail.created_at', '>=', $startDate);
		$Lead->whereDate('lead_status_detail.created_at', '<=', $endDate);
		$LeadLostIds = $Lead->distinct()->pluck('leads.id')->all();

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
        $Query->whereIn('leads.id', $LeadLostIds);
        $Query->orderBy('leads.assigned_to', 'asc');
        $leaddata = $Query->get();

        
        foreach ($leaddata as $lead_key => $lead_value) {
            $LeadQuestionAnswer = LeadQuestionAnswer::select('lead_question_answer.lead_question_id', 'lead_question.question', 'lead_question_answer.answer', 'lead_question.type', 'lead_question_answer.created_at', 'lead_question_answer.updated_at');
            $LeadQuestionAnswer->leftJoin('lead_question', 'lead_question.id', '=', 'lead_question_answer.lead_question_id');
            $LeadQuestionAnswer->where('lead_question_answer.lead_id', $lead_value['id']);
            //LOST - 
            $LeadQuestionAnswer->whereIn('lead_question.id', array(10,11,12,13,20,21));
            
            $LeadQuestionAnswer->where('lead_question_answer.reference_type', 'Lead-Status-Update');
            $LeadQuestionAnswer->where('lead_question_answer.answer', '!=', '');
            $LeadQuestionAnswer = $LeadQuestionAnswer->get();

            // LOST
            // $client_selected_which_product = '';
            // $reason_of_rejection = '';
            // $compitator_name = '';
            // $notes = '';

            // $LeadQuestionAnswer = json_encode($LeadQuestionAnswer);
            // $LeadQuestionAnswer = json_decode($LeadQuestionAnswer, true);
            // foreach ($LeadQuestionAnswer as $key => $value) {
            //     if ($value['type'] == 0) {
            //         // $LeadQuestionAnswer[$key]['option'] = $value['answer'];
            //         $notes = $value['answer'];
            //     } elseif ($value['type'] == 1) {
            //         $option_selected = '';
            //         $option_ids = LeadQuestionOptions::select('id')
            //             ->where('lead_question_id', $value['lead_question_id'])
            //             ->distinct()
            //             ->pluck('id')
            //             ->all();

            //         if (!in_array($value['answer'], $option_ids)) {
            //             $ChannelPart = ChannelPartner::select('firm_name')
            //                 ->where('user_id', $value['answer'])
            //                 ->first();
            //             if ($ChannelPart) {
            //                 $option_selected = $ChannelPart->firm_name;
            //                 $MaterialSentBy = $ChannelPart->firm_name;
            //             } else {
            //                 $option_selected = '';
            //             }
            //         } else {
            //             $LeadQuestionOption = LeadQuestionOptions::select('lead_question_options.option');
            //             $LeadQuestionOption->where('id', $value['answer']);
            //             $LeadQuestionOption = $LeadQuestionOption->first();
            //             $option_selected = $LeadQuestionOption->option;
            //         }
            //         $client_selected_which_product = $option_selected;
            //         // $LeadQuestionAnswer[$key]['option'] = $option_selected;
                    
            //     } elseif ($value['type'] == 5) {
            //         // $LeadQuestionAnswer[$key]['option'] = $value['answer'];

            //     } elseif ($value['type'] == 4 || $value['type'] == 6) {
            //         $LeadQuestionOption = LeadQuestionOptions::select('lead_question_options.option');
            //         $LeadQuestionOption->whereIn('id', explode(',', $value['answer']));
            //         $LeadQuestionOption = $LeadQuestionOption->get();
                    
            //         $MultipleAnswer = '';
            //         foreach ($LeadQuestionOption as $Okey => $Ovalue) {
            //             $MultipleAnswer .= $Ovalue['option'] . ', ';
            //         }

            //         $reason_of_rejection = $MultipleAnswer;
            //         // $LeadQuestionAnswer[$key]['option'] = $MultipleAnswer;
                    
            //     } elseif ($value['type'] == 8) {
            //         $compitator_name = $value['answer'];
            //         // $LeadQuestionAnswer[$key]['option'] = getSpaceFilePath($value['answer']);
            //     }
                
            // }

            $LeadStatusUpdate = LeadStatusUpdate::query();
            $LeadStatusUpdate->selectRaw('DATE(lead_status_updates.created_at) AS lost_status_date');
            $LeadStatusUpdate->selectRaw('CONCAT(users.first_name," ",users.last_name) AS lost_by_name');
            $LeadStatusUpdate->leftJoin('users', 'users.id', '=', 'lead_status_updates.entryby');
            $LeadStatusUpdate->whereIn('lead_status_updates.new_status', array(5, 104));
			$LeadStatusUpdate->orderBy('lead_status_updates.created_at', 'DESC');
			$LeadStatusUpdate->where('lead_status_updates.lead_id', $lead_value['id']);
            $LeadStatusUpdate = $LeadStatusUpdate->first();
            
          
            $objNewItem['client_name'] = $lead_value['client_name'];
            $objNewItem['contact_number'] = $lead_value['phone_number'];
            $objNewItem['city'] = $lead_value['city_name'];
            $objNewItem['house_number'] = $lead_value['house_no'];
            $objNewItem['building_name'] = $lead_value['addressline1'];
            $objNewItem['quotation_amount'] = $lead_value['quot_total_amount'];
            $objNewItem['billing_amount'] = $lead_value['quot_billing_amount'];
            $objNewItem['source_type'] = $lead_value['source_type'];
            $objNewItem['source_name'] = $lead_value['source'];
            $objNewItem['architect_name'] = $lead_value['architect_name'];
            $objNewItem['electrician_name'] = $lead_value['electrician_name'];
            $objNewItem['lead_owner'] = $lead_value['owner_name'];

            $objNewItem['lost_status_date'] = '';
            $objNewItem['lost_by'] = '';
            if($LeadStatusUpdate){
                $objNewItem['lost_status_date'] = $LeadStatusUpdate['lost_status_date'];
                $objNewItem['lost_by'] = $LeadStatusUpdate['lost_by_name'];
            }
            // $objNewItem['client_selected_which_product'] = $client_selected_which_product;
            // $objNewItem['reason_of_rejection'] = $reason_of_rejection;
            // $objNewItem['compitator_name'] = $compitator_name;
            // $objNewItem['notes'] = $notes;
            $objNewItem['closing_date'] = $lead_value['closing_date'];
            $objNewItem['site_stage'] = $lead_value['site_stage'];
            array_push($lstleadData,$objNewItem);
            
		}


        return collect($lstleadData);
    }
}
