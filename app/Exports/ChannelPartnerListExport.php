<?php

namespace App\Exports;

use App\Models\Lead;
use App\Models\LeadQuestionAnswer;
use App\Models\LeadQuestionOptions;
use App\Models\LeadStatusUpdate;
use App\Models\ChannelPartner;
use Maatwebsite\Excel\Concerns\FromCollection;

class ChannelPartnerListExport implements FromCollection
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

        $objNewItemHeader['srno'] = 'Sr no.';
        $objNewItemHeader['type'] = 'Type';
        $objNewItemHeader['name'] = 'Name';
        $objNewItemHeader['firm_name'] = 'Firm Name';
        $objNewItemHeader['city'] = 'City';
        $objNewItemHeader['address'] = 'Address';
        $objNewItemHeader['phone_number'] = 'Phone Number';
        $objNewItemHeader['email'] = 'Email';
        $objNewItemHeader['account_status'] = 'Account Status';
        $objNewItemHeader['id'] = 'ERP ID';

        // $objNewItemHeader['firm_name'] = 'Firm Name';
        // $objNewItemHeader['owner_name'] = 'Owner Number';
        // $objNewItemHeader['phone_number'] = 'Phone Number';
        
        // $objNewItemHeader['house_no'] = 'House No';
        // $objNewItemHeader['address_line1'] = 'Addresss 1';
        // $objNewItemHeader['address_line2'] = 'Addresss 2';
        // $objNewItemHeader['area'] = 'Area';
        // $objNewItemHeader['pincode'] = 'Pincode';
        // $objNewItemHeader['city'] = 'City';

        // $objNewItemHeader['type'] = 'Type';
        // $objNewItemHeader['won_deal_count'] = 'Won Deal';
        // $objNewItemHeader['running_deal_count'] = 'Running Lead & Deal';
        // $objNewItemHeader['lost_deal_count'] = 'Lost Lead & Deal';
        // $objNewItemHeader['status'] = 'Status';
        array_push($lstleadData,$objNewItemHeader);

        $selectColumns = array(
            'channel_partner_user.id',
			'channel_partner_user.email',
			'channel_partner.firm_name',
			'channel_partner.user_id',
			'channel_partner_user.phone_number',
			'city_list.name as city_name',
			'channel_partner_user.house_no as house_no',
			'channel_partner_user.address_line1 as address_line1',
			'channel_partner_user.address_line2 as address_line2',
			'channel_partner_user.area as area',
			'channel_partner_user.pincode as pincode',
		);

        $startDate = date('Y-m-d', strtotime($this->startDate));
		$endDate = date('Y-m-d', strtotime($this->endDate));

        $query = ChannelPartner::query();
        $query->select($selectColumns);
        $query->selectRaw('CASE
        WHEN channel_partner_user.type = 101 THEN "ASM"
        WHEN channel_partner_user.type = 102 THEN "ADM"
        WHEN channel_partner_user.type = 103 THEN "APM"
        WHEN channel_partner_user.type = 104 THEN "AD"
        WHEN channel_partner_user.type = 105 THEN "Retailer"
        ELSE "Undifine"
        END AS type');
        $query->selectRaw('CASE
        WHEN channel_partner_user.status = 0 THEN "Inactive"
        WHEN channel_partner_user.status = 1 THEN "Active"
        WHEN channel_partner_user.status = 2 THEN "Pending"
        ELSE "Undifine"
        END AS status');
        $query->selectRaw('CONCAT(channel_partner_user.first_name," ",channel_partner_user.last_name) AS owner_name');
        $query->selectRaw('CONCAT(channel_partner_user.first_name," ",channel_partner_user.last_name) AS user_name');
        // $query->selectRaw('CONCAT(sales_person.first_name," ",sales_person.last_name) AS owner_name');
		$query->leftJoin('users as sales_person', 'sales_person.id', '=', 'channel_partner.sale_persons');
		$query->leftJoin('users as channel_partner_user', 'channel_partner_user.id', '=', 'channel_partner.user_id');
		$query->leftJoin('city_list', 'city_list.id', '=', 'channel_partner_user.city_id');
		$query->whereDate('channel_partner_user.created_at', '>=', $startDate);
		$query->whereDate('channel_partner_user.created_at', '<=', $endDate);
        $query = $query->get();

        foreach ($query as $key => $value) {
            // $LeadWonCount = Lead::query();
            // $LeadWonCount->where('leads.status', 103);
			// $LeadWonCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
			// $LeadWonCount->where('lead_sources.source', $value['user_id']);
			// $LeadWonCount = $LeadWonCount->distinct()->pluck('leads.id')->count();
            
            // $LeadLostCount = Lead::query();
            // $LeadLostCount->whereIn('leads.status', array(5, 104));
			// $LeadLostCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
			// $LeadLostCount->where('lead_sources.source', $value['user_id']);
			// $LeadLostCount = $LeadLostCount->distinct()->pluck('leads.id')->count();

            // $RunningLead = Lead::query();
			// $RunningLead->whereIn('status', array(1,2,3,4,100,101,102));
			// $RunningLead->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
			// $RunningLead->where('lead_sources.source', $value['user_id']);
			// $LeadRunningCount = $RunningLead->distinct()->pluck('leads.id')->count();

            // $objNewItem['firm_name'] = $value['firm_name'];
            // $objNewItem['owner_name'] = $value['owner_name'];
            // $objNewItem['phone_number'] = $value['phone_number'];

            // $objNewItem['house_no'] = $value['house_no'];
            // $objNewItem['address_line1'] = $value['address_line1'];
            // $objNewItem['address_line2'] = $value['address_line2'];
            // $objNewItem['area'] = $value['area'];
            // $objNewItem['pincode'] = $value['pincode'];
            // $objNewItem['city'] = $value['city_name'];

            // $objNewItem['type'] = $value['type'];
            // $objNewItem['won_deal_count'] = $LeadWonCount;
            // $objNewItem['running_deal_count'] = $LeadRunningCount;
            // $objNewItem['lost_deal_count'] = $LeadLostCount;

            // $objNewItem['status'] = $value['status'];

            $objNewItem['srno'] = ($key + 1);
            $objNewItem['type'] = $value['type'];
            $objNewItem['name'] = $value['user_name'];
            $objNewItem['firm_name'] = $value['firm_name'];
            $objNewItem['city'] = $value['city_name'];
            $objNewItem['address'] = $value['house_no']." , ".$value['address_line1']." , ".$value['address_line2']." , ".$value['area']." , ".$value['pincode'];
            $objNewItem['phone_number'] = $value['phone_number'];
            $objNewItem['email'] = $value['email'];
            $objNewItem['account_status'] = $value['status'];
            $objNewItem['id'] = $value['id'];

            array_push($lstleadData,$objNewItem);
          
            
		}


        return collect($lstleadData);
    }
}
