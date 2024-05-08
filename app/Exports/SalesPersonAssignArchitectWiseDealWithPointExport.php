<?php

namespace App\Exports;

use App\Models\Lead;
use App\Models\Architect;
use App\Models\LeadFile;
use App\Models\LeadStatusUpdate;
use App\Models\ChannelPartner;
use App\Models\LeadSource;
use App\Models\SalePerson;
use Maatwebsite\Excel\Concerns\FromCollection;

class SalesPersonAssignArchitectWiseDealWithPointExport implements FromCollection
{
    public function collection()
    {
        $lstleadData = array();

        $objNewItemHeader['sales_person_id'] = 'Sales Person Id';
        $objNewItemHeader['sales_person_name'] = 'Sales Person Name';
        $objNewItemHeader['sales_person_contact_number'] = 'Sales Person Phone Number';
        $objNewItemHeader['architect_name'] = 'Architect Name';
        $objNewItemHeader['architect_phone_number'] = 'Architect Phone Number';
        $objNewItemHeader['lead_deal'] = 'Lead/Deal';
        $objNewItemHeader['client_name'] = 'Deal Client Name';
        $objNewItemHeader['contact_number'] = 'Deal Contact Number';
        $objNewItemHeader['channel_partner_name'] = 'Deal Channel Partner Name';
        $objNewItemHeader['won_status_date'] = 'Deal Won Status Date & Time';
        $objNewItemHeader['won_by'] = 'Deal Won Status by';
        $objNewItemHeader['bill_sr_no'] = 'Bill Sr No.';
        $objNewItemHeader['bill_path'] = 'Bill Path';
        $objNewItemHeader['bill_amount'] = 'Bill Amount';
        $objNewItemHeader['bill_status'] = 'Bill Status';
        $objNewItemHeader['bill_point'] = 'Bill Point';
        $objNewItemHeader['bill_hod_approve'] = 'Bill Point Approve';
        array_push($lstleadData,$objNewItemHeader);
        
        $column = array(
            'sale_person.user_id',
            'users.phone_number'
        );
        $SalesQuery = SalePerson::query();
        $SalesQuery->select($column);
        $SalesQuery->selectRaw('CONCAT(users.first_name," ",users.last_name) AS sales_person_name');
        $SalesQuery->leftJoin('users', 'users.id', '=', 'sale_person.user_id');
        $SalesQuery->whereIn('users.id', [7725,29,5628,1233,5755,8016,5515,22,36,37,7856,28,38,5654,34,5629,5656,42,1751,5812,3,3440,4190,5592,5627,5630]);
        $dataList = $SalesQuery->get();

        foreach ($dataList as $key => $value) {
            $sales_person_id = $value['user_id'];
            $sales_person_name = $value['sales_person_name'];
            $sales_person_contact_number = $value['phone_number'];
            $architect_name = '';
            $architect_phone_number = '';


            $architect_column = array(
                'architect.user_id',
                'users.phone_number'
            );
            $ArchitectQuery = Architect::query();
            $ArchitectQuery->select($architect_column);
            $ArchitectQuery->selectRaw('CONCAT(users.first_name," ",users.last_name) AS architect_name');
            $ArchitectQuery->leftJoin('users', 'users.id', '=', 'architect.user_id');
            $ArchitectQuery->where('architect.sale_person_id', $value['user_id']);
            $architect_List = $ArchitectQuery->get();

            if($architect_List){
                foreach ($architect_List as $architect_key => $architect_value) {
                    $architect_name = $architect_value['architect_name'];
                    $architect_phone_number = $architect_value['phone_number'];

                    $lead_column = array(
                        'leads.id AS id',
                        'leads.phone_number AS phone_number',
                    );
                    $Query = Lead::query();
                    $Query->select($lead_column);
                    $Query->selectRaw('CASE WHEN leads.is_deal = 0 THEN "LEAD" WHEN leads.is_deal = 1 THEN "DEAL" ELSE "Undifine" END AS type');
                    $Query->selectRaw('CONCAT(leads.first_name," ",leads.last_name) AS client_name');
                    $Query->selectRaw('CONCAT(architect.first_name," ",architect.last_name) AS architect_name');
                    $Query->selectRaw('CONCAT(electrician.first_name," ",electrician.last_name) AS electrician_name');
                    $Query->leftJoin('users AS architect', 'architect.id', '=', 'leads.architect');
                    $Query->leftJoin('users AS electrician', 'electrician.id', '=', 'leads.electrician');
            	    $Query->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
				    $Query->where('leads.architect', $architect_value['user_id']);
				    $Query->where('leads.status', 103);
				    $Query->orwhere('lead_sources.source', $architect_value['user_id']);
                    $Query->whereDate('leads.created_at', '>=', '2023-04-01');
		            $Query->whereDate('leads.created_at', '<=', '2024-03-31');
                    $Query->orderBy('leads.assigned_to', 'asc');
            	    $leaddata = $Query->get();

                    if($leaddata){
                        foreach ($leaddata as $lead_key => $lead_value) {
        
                            $lead_deal = $lead_value['type'];
                            $client_name = $lead_value['client_name'];
                            $phone_number = $lead_value['phone_number'];
                            $architect_name = $lead_value['architect_name'];
                            $electrician_name = $lead_value['electrician_name'];
                            $channel_partner_name = '';
                            $won_status_date = '';
                            $won_by = '';
        
                            $ChannelPartner = LeadSource::query();
                            $ChannelPartner->selectRaw('GROUP_CONCAT(channel_partner.firm_name) AS firm_name');
                            $ChannelPartner->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'lead_sources.source');
                            $ChannelPartner->where('lead_sources.lead_id', $lead_value['id']);
                            $ChannelPartner->whereIn('lead_sources.source_type', ['user-101','user-102','user-103','user-104','user-105']);
                            $ChannelPartner->groupBy(['lead_sources.lead_id']);
                            $ChannelPartner = $ChannelPartner->first();
                            if($ChannelPartner){
                                $channel_partner_name = $ChannelPartner['firm_name'];
                            }
        
                            $LeadStatusUpdate = LeadStatusUpdate::query();
                            $LeadStatusUpdate->selectRaw('DATE(lead_status_updates.created_at) AS won_status_date');
                            $LeadStatusUpdate->selectRaw('CONCAT(users.first_name," ",users.last_name) AS won_by_name');
                            $LeadStatusUpdate->leftJoin('users', 'users.id', '=', 'lead_status_updates.entryby');
                            $LeadStatusUpdate->where('lead_status_updates.new_status', 103);
                            $LeadStatusUpdate->orderBy('lead_status_updates.created_at', 'DESC');
                            $LeadStatusUpdate->where('lead_status_updates.lead_id', $lead_value['id']);
                            $LeadStatusUpdate = $LeadStatusUpdate->first();
                            if($LeadStatusUpdate){
                                $won_status_date = $LeadStatusUpdate['won_status_date'];
                                $won_by = $LeadStatusUpdate['won_by_name'];
                            }
                            
                            $BillColumn = array(
                                'lead_files.name',
                                'lead_files.billing_amount',
                                'lead_files.point',
                                'lead_files.status',
                                'lead_files.hod_approved',
                            );
                            $LeadBill = LeadFile::query();
                            $LeadBill->select($BillColumn);
                            $LeadBill->selectRaw('CONCAT(users.first_name," ",users.last_name) AS uploaded_by_name');
                            $LeadBill->leftJoin('users', 'users.id', '=', 'lead_files.uploaded_by');
                            $LeadBill->where('lead_files.lead_id', $lead_value['id']);
                            $LeadBill->where('lead_files.file_tag_id', 3);
                            $LeadBill = $LeadBill->get();
                            if($LeadBill){
                                foreach ($LeadBill as $lead_bill_key => $lead_bill_value) {
        
                                    $objNewItem['sales_person_id'] = $sales_person_id;
                                    $objNewItem['sales_person_name'] = $sales_person_name;
                                    $objNewItem['sales_person_contact_number'] = $sales_person_contact_number;
                                    $objNewItem['architect_name'] = $architect_name;
                                    $objNewItem['architect_phone_number'] = $architect_phone_number;
                                    $objNewItem['lead_deal'] = $lead_deal;
                                    $objNewItem['client_name'] = $client_name;
                                    $objNewItem['contact_number'] = $phone_number;
                                    $objNewItem['channel_partner_name'] = $channel_partner_name;
                                    $objNewItem['won_status_date'] = $won_status_date;
                                    $objNewItem['won_by'] = $won_by;
                                    $objNewItem['bill_sr_no'] = ($lead_bill_key + 1);
                                    $objNewItem['bill_path'] = $lead_bill_value['name'];
                                    $objNewItem['bill_amount'] = $lead_bill_value['billing_amount'];
                                    if ($lead_bill_value['status'] == 100) {
                                        $objNewItem['bill_status'] = 'CLAIMED';
                                    } elseif ($lead_bill_value['status'] == 101) {
                                        $objNewItem['bill_status'] = 'QUERY';
                                    } elseif ($lead_bill_value['status'] == 102) {
                                        $objNewItem['bill_status'] = 'LAPSED';
                                    } else {
                                        $objNewItem['bill_status'] = '-';
                                    }
                                    $objNewItem['bill_point'] = $lead_bill_value['point'];
                                    $objNewItem['bill_hod_approve'] = ($lead_bill_value['hod_approved'] == 1) ? "HOD Approved" : ($lead_bill_value['hod_approved'] == 2) ? "HOD Rejected" : "HOD Pending";
                                    
                                    array_push($lstleadData,$objNewItem);
                                }
                            }else {
                                $objNewItem['sales_person_id'] = $sales_person_id;
                                $objNewItem['sales_person_name'] = $sales_person_name;
                                $objNewItem['sales_person_contact_number'] = $sales_person_contact_number;
                                $objNewItem['architect_name'] = 'Architect Name';
                                $objNewItem['architect_phone_number'] = 'Architect Phone Number';
                                $objNewItem['lead_deal'] = $lead_deal;
                                $objNewItem['client_name'] = $client_name;
                                $objNewItem['contact_number'] = $phone_number;
                                $objNewItem['channel_partner_name'] = $channel_partner_name;
                                $objNewItem['won_status_date'] = $won_status_date;
                                $objNewItem['won_by'] = $won_by;
                                $objNewItem['bill_sr_no'] = '';
                                $objNewItem['bill_path'] = '';
                                $objNewItem['bill_amount'] = '';
                                $objNewItem['bill_status'] = '';
                                $objNewItem['bill_point'] = '';
                                $objNewItem['bill_hod_approve'] = '';
                                
                                array_push($lstleadData,$objNewItem);
                            }
        
                        }
                        
                    }else {
                        $objNewItem['sales_person_id'] = $sales_person_id;
                        $objNewItem['sales_person_name'] = $sales_person_name;
                        $objNewItem['sales_person_contact_number'] = $sales_person_contact_number;
                        $objNewItem['architect_name'] = $architect_name;
                        $objNewItem['architect_phone_number'] = $architect_phone_number;
                        $objNewItem['lead_deal'] = '';
                        $objNewItem['client_name'] = '';
                        $objNewItem['contact_number'] = '';
                        $objNewItem['channel_partner_name'] = '';
                        $objNewItem['won_status_date'] = '';
                        $objNewItem['won_by'] = '';
                        $objNewItem['bill_sr_no'] = '';
                        $objNewItem['bill_path'] = '';
                        $objNewItem['bill_amount'] = '';
                        $objNewItem['bill_status'] = '';
                        $objNewItem['bill_point'] = '';
                        $objNewItem['bill_hod_approve'] = '';
                        array_push($lstleadData,$objNewItem);
                    }

                }
            }else{
                $objNewItem['sales_person_id'] = $sales_person_id;
                $objNewItem['sales_person_name'] = $sales_person_name;
                $objNewItem['sales_person_contact_number'] = $sales_person_contact_number;
                $objNewItem['architect_name'] = $architect_name;
                $objNewItem['architect_phone_number'] = $architect_phone_number;
                $objNewItem['lead_deal'] = '';
                $objNewItem['client_name'] = '';
                $objNewItem['contact_number'] = '';
                $objNewItem['channel_partner_name'] = '';
                $objNewItem['won_status_date'] = '';
                $objNewItem['won_by'] = '';
                $objNewItem['bill_sr_no'] = '';
                $objNewItem['bill_path'] = '';
                $objNewItem['bill_amount'] = '';
                $objNewItem['bill_status'] = '';
                $objNewItem['bill_point'] = '';
                $objNewItem['bill_hod_approve'] = '';
                array_push($lstleadData,$objNewItem);
            }
            
		}
        return collect($lstleadData);
    }
}
