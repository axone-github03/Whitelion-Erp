<?php

namespace App\Exports;

use App\Models\Lead;
use App\Models\Architect;
use App\Models\UserNotes;
use App\Models\LeadStatusUpdate;
use App\Models\ChannelPartner;
use Maatwebsite\Excel\Concerns\FromCollection;

class ArchitectListExport implements FromCollection
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
        $objNewItemHeader['city'] = 'City';
        $objNewItemHeader['address'] = 'Address';
        $objNewItemHeader['phone_number'] = 'Phone Number';
        $objNewItemHeader['email'] = 'Email';

        $objNewItemHeader['architect_status'] = 'Arcchitect Status';
        $objNewItemHeader['account_status'] = 'Account Status';

        $objNewItemHeader['id'] = 'ERP ID';

        // $objNewItemHeader['type'] = 'Type';
        // $objNewItemHeader['owner_name'] = 'Owner Number';
        // $objNewItemHeader['firm_name'] = 'Firm Name';
        // $objNewItemHeader['status'] = 'Status';
        // $objNewItemHeader['account_status'] = 'Account Status';
        // $objNewItemHeader['lifetime_point'] = 'Lifetime Point';
        // $objNewItemHeader['available_point'] = 'Available Point';
        // $objNewItemHeader['won_lead'] = 'Won Deal';
        // $objNewItemHeader['lost_lead'] = 'Lost Lead & Deal';
        // $objNewItemHeader['running_lead'] = 'Running Lead & Deal';
        // $objNewItemHeader['total_lead'] = 'Total Lead & Deal';
        // $objNewItemHeader['lead_id_list'] = 'Lead & Deal Id List';
        // $objNewItemHeader['user_notes'] = 'Telesales Notes';
        array_push($lstleadData,$objNewItemHeader);

        $selectColumns = array(
			'users.id',
			'users.phone_number',
			'users.house_no',
			'users.address_line1',
			'users.address_line2',
			'users.area',
			'users.pincode',
			'users.email',
            'architect.firm_name',
			'city_list.name as city_name',
            'architect.total_point_current', 
            'architect.total_point', 
		);

        $startDate = date('Y-m-d', strtotime($this->startDate));
		$endDate = date('Y-m-d', strtotime($this->endDate));

        $query = Architect::query();
        $query->select($selectColumns);
        $query->selectRaw('CASE
        WHEN architect.status = 0 THEN "Rejected"
        WHEN architect.status = 1 THEN "On Boarded"
        WHEN architect.status = 2 THEN "Entry"
        WHEN architect.status = 3 THEN "Data Mismatch"
        WHEN architect.status = 4 THEN "Verified by Telecaller"
        WHEN architect.status = 5 THEN "Duplicate"
        WHEN architect.status = 7 THEN "Not Recieved"
        WHEN architect.status = 8 THEN "Data Pending"
        WHEN architect.status = 9 THEN "Language Issue"
        ELSE "Undifine"
        END AS status');
        $query->selectRaw('CASE
        WHEN users.status = 0 THEN "Inactive"
        WHEN users.status = 1 THEN "Active"
        WHEN users.status = 2 THEN "Pending"
        ELSE "Undifine"
        END AS account_status');
        $query->selectRaw('CASE
        WHEN users.type = 201 THEN "Non Prime"
        WHEN users.type = 202 THEN "Prime"
        ELSE "Undifine"
        END AS type');
        $query->selectRaw('CONCAT(users.first_name," ",users.last_name) AS user_name');
        $query->selectRaw('CONCAT(sale_person.first_name," ",sale_person.last_name) AS owner_name');
        $query->leftJoin('users', 'users.id', '=', 'architect.user_id');
        $query->leftJoin('users as sale_person', 'architect.sale_person_id', '=', 'sale_person.id');
        $query->leftJoin('users as created_by_user', 'users.created_by', '=', 'created_by_user.id');
        $query->leftJoin('city_list', 'city_list.id', '=', 'users.city_id');
        // $query->where('architect.total_point','>', 50);
        // $query->whereIn('architect.status', [0,7,8,9]);
        $query->whereIn('architect.type', [201, 202]);
        $query->whereDate('users.created_at', '>=', $startDate);
		$query->whereDate('users.created_at', '<=', $endDate);
        $query = $query->get();

        foreach ($query as $key => $value) {
            // $objNewItem['id'] = $value['id'];
            // $objNewItem['type'] = $value['type'];
            // $objNewItem['name'] = $value['user_name'];
            // $objNewItem['phone_number'] = $value['phone_number'];
            // $objNewItem['owner_name'] = $value['owner_name'];
            // $objNewItem['firm_name'] = $value['firm_name'];
            // $objNewItem['status'] = $value['status'];
            // $objNewItem['account_status'] = $value['account_status'];
            // $objNewItem['lifetime_point'] = $value['total_point'];
            // $objNewItem['available_point'] = $value['total_point_current'];

            // $LeadWonCount = Lead::query();
            // $LeadWonCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
            // $LeadWonCount->whereIn('lead_sources.source_type', ['user-201','user-202']);
			// $LeadWonCount->where('lead_sources.source', $value['id']);
			// $LeadWonCount->where('leads.status', 103);
            // $LeadWonCount = $LeadWonCount->distinct()->pluck('leads.id')->count();
            
            // $LeadLostCount = Lead::query();
            // $LeadLostCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
            // $LeadLostCount->whereIn('lead_sources.source_type', ['user-201','user-202']);
			// $LeadLostCount->where('lead_sources.source', $value['id']);
			// $LeadLostCount->whereIn('leads.status', [5,104]);
            // $LeadLostCount = $LeadLostCount->distinct()->pluck('leads.id')->count();
            
            // $LeadRunningCount = Lead::query();
            // $LeadRunningCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
            // $LeadRunningCount->whereIn('lead_sources.source_type', ['user-201','user-202']);
			// $LeadRunningCount->where('lead_sources.source', $value['id']);
			// $LeadRunningCount->whereIn('leads.status', [1,2,3,4,100,101,102]);
            // $LeadRunningCount = $LeadRunningCount->distinct()->pluck('leads.id')->count();
            
            // $LeadTotalCount = Lead::query();
            // $LeadTotalCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
            // $LeadTotalCount->whereIn('lead_sources.source_type', ['user-201','user-202']);
			// $LeadTotalCount->where('lead_sources.source', $value['id']);
            // $LeadTotalId = $LeadTotalCount->distinct()->pluck('leads.id')->all();
            // $LeadTotalCount = $LeadTotalCount->distinct()->pluck('leads.id')->count();

            // $objNewItem['won_lead'] = $LeadWonCount;
            // $objNewItem['lost_lead'] = $LeadLostCount;
            // $objNewItem['running_lead'] = $LeadRunningCount;
            // $objNewItem['total_lead'] = $LeadTotalCount;
            // $objNewItem['lead_id_list'] = $LeadTotalId;
            
            // $UserUpdateList = UserNotes::query();
            // $UserUpdateList->select('user_notes.id', 'user_notes.note', 'user_notes.user_id', 'user_notes.note_type', 'user_notes.note_title', 'created.first_name', 'created.last_name', 'user_notes.created_at');
            // $UserUpdateList->leftJoin('users as created', 'created.id', '=', 'user_notes.entryby');
            // $UserUpdateList->where('user_notes.user_id', $value['id']);
            // $UserUpdateList->where('created.type', 9);
            // $UserUpdateList->orderBy('user_notes.id', 'desc');
            // $UserUpdateList = $UserUpdateList->get();
            // $UserNotes = "";
            // foreach ($UserUpdateList as $note_key => $note_value) {
            //     $UserNotes = $note_value['note'] . " | ";
            // }
            // $objNewItem['user_notes'] = $UserNotes;

            $objNewItem['srno'] = ($key + 1);

            $objNewItem['type'] = $value['type'];

            $objNewItem['name'] = $value['user_name'];
            $objNewItem['city'] = $value['city_name'];
            $objNewItem['address'] = $value['house_no']." , ".$value['address_line1']." , ".$value['address_line2']." , ".$value['area']." , ".$value['pincode'];
            $objNewItem['phone_number'] = $value['phone_number'];
            $objNewItem['email'] = $value['email'];

            $objNewItem['architect_status'] = $value['status'];
            $objNewItem['account_status'] = $value['account_status'];

            $objNewItem['id'] = $value['id'];


            array_push($lstleadData,$objNewItem);

		}

        return collect([// creating a sheet collection
            'Lead' => $lstleadData,//Fetch users model with sheet name as user
            'Second sheet' => $lstleadData//Fetch project model with sheet name as project 
        ]);
    }
}
