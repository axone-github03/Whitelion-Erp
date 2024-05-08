<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Http\Request;
use App\Models\CRMSettingBHK;
use App\Models\CRMSettingSiteType;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\CRMSettingCompetitors;
use App\Models\CRMSettingStageOfSite;
use App\Models\CRMSettingWantToCover;
use Config;
use App\Models\User;
use DB;

class MarketingLeadReportController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {

            $tabCanAccessBy = array(0, 1, 777);

            if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
                return redirect()->route('dashboard');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $data = array();
        $data['title'] = "Marketing Lead Report";
        return view('marketing_lead_report/index', compact('data'));
    }
    

    function ajax(Request $request)
	{
        $isSalePerson = isSalePerson();
        $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
        $source_type = getLeadSourceTypes();
        if ($isSalePerson == 1) {
            $parentSalesUsers = getParentSalePersonsIds(Auth::user()->id);
            $childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
        }
        
        $searchValue = "";

		if (isset($request['inquiry_filter_search_value']) && $request['inquiry_filter_search_value'] != "") {

			$searchValue = $request['inquiry_filter_search_value'];
		}

        $searchColumns = array(
            DB::raw("CONCAT(leads.first_name, ' ', leads.last_name)"),
            DB::raw("CONCAT(lead_owner.first_name, ' ', lead_owner.last_name)"),
            DB::raw("CONCAT(created_by.first_name, ' ', created_by.last_name)"),
			'leads.id',
			'leads.phone_number',
			'leads.pincode',
			'city_list.name',
			'leads.email',
			'leads.addressline1',
			'leads.addressline2',
			'leads.area',
			'leads.source_type',
			'leads.source',
			'leads.house_no',
            'leads.site_stage',
            'crm_setting_stage_of_site.name',
            'leads.sq_foot',
            DB::raw('CONCAT(architect.first_name," ",architect.last_name)'),
			DB::raw('CONCAT(architect.first_name," ",architect.last_name)'),
			DB::raw('CONCAT(architect.first_name," ",architect.last_name)'),
			DB::raw('CONCAT(electrician.first_name," ",electrician.last_name)'),
            DB::raw('CONCAT(electrician.first_name," ",electrician.last_name)'),
			DB::raw('CONCAT(electrician.first_name," ",electrician.last_name)'),
		);

        $sortingColumns = array(
            'leads.id',
            'leads.phone_number',
            'leads.house_no',
            'leads.status',
            'leads.site_stage',
            'leads.closing_date_time',
            'leads.assigned_to',
            'leads.source_type',
            'leads.first_name',
            'created_by.first_name',
            'CONVERT(leads.quotation_amount, SIGNED)',
        );

        $selectColumns = array(
			'leads.id',
			'leads.user_id',
			'leads.is_deal',
			'leads.first_name',
			'leads.last_name',
			'leads.phone_number',
			'leads.assigned_to',
			'leads.email',
			'leads.house_no',
			'leads.addressline1',
			'leads.addressline2',
			'leads.pincode',
			'leads.area',
			'leads.city_id',
			'leads.status',
			'leads.source_type',
			'leads.source',
			'leads.architect',
			'leads.electrician',
			'created_by.first_name as created_by_first_name',
			'created_by.last_name as created_by_last_name',
			'created_by.type as created_by_type',
			'created_by.id as created_by_user_id',
			'lead_owner.first_name as lead_owner_first_name',
			'lead_owner.last_name  as lead_owner_last_name',
            'electrician.first_name as electrician_first_name',
			'electrician.last_name as electrician_last_name',
            'electrician.phone_number as electrician_phone_number',
            'architect.first_name as architect_first_name',
			'architect.last_name as architect_last_name',
            'architect.phone_number as architect_phone_number',
			'city_list.name as city_list_name',
			'leads.sub_status',
			'leads.site_stage',
			'crm_setting_stage_of_site.name',
			'leads.quotation',
			'leads.quotation_file',
			'leads.quotation_date',
			'leads.created_at',
			'leads.closing_date_time',
			'leads.site_type',
			'leads.sq_foot',
			'leads.bhk',
			'leads.want_to_cover',
			'leads.competitor',
			'leads.budget',
			'leads.tag',
		);
        
        $query = Lead::query();
        if ($isSalePerson == 1) {
            $query->whereIn('leads.assigned_to', $childSalePersonsIds);
        }

        $query = Lead::query();
        $query->leftJoin('users as lead_owner', 'lead_owner.id', '=', 'leads.assigned_to');
        $query->leftJoin('users as created_by', 'created_by.id', '=', 'leads.user_id');
        $query->leftJoin('users as architect', 'architect.id', '=', 'leads.architect');
		$query->leftJoin('users as electrician', 'electrician.id', '=', 'leads.electrician');
		$query->leftJoin('crm_setting_stage_of_site', 'crm_setting_stage_of_site.id', '=', 'leads.site_stage');
        $query->leftJoin('city_list', 'city_list.id', '=', 'leads.city_id');
        $query->whereIn('leads.source_type', ['textnotrequired-1', 'textnotrequired-11', 'textnotrequired-12']);
        $query->select($selectColumns);
      
        if ($isSalePerson == 1) {
            $query->whereIn('leads.assigned_to', $childSalePersonsIds);
        }

      
        $query->orderBy($sortingColumns[$request['order'][0]['column']], $request['order'][0]['dir']);
        if (isset($request['search']['value'])) {
            $isFilterApply = 1;
            $search_value = $request['search']['value'];
            $query->where(function ($query) use ($search_value, $searchColumns) {
                for ($i = 0; $i < count($searchColumns); $i++) {
                    if ($i == 0) {
                        $query->where($searchColumns[$i], 'like', '%' . $search_value . '%');
                    } else {
                        $query->orWhere($searchColumns[$i], 'like', '%' . $search_value . '%');
                    }
                }
            });
        }

        if (!empty($searchValue)) {
            $query->where(function ($query) use ($searchValue, $searchColumns) {
                foreach ($searchColumns as $column) {
                    $query->orWhere($column, 'like', '%' . $searchValue . '%');
                }
            });
        }
    
        $recordsTotal = $query->count();
        $recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.
        
        $recordsFiltered = $query->count();
        $query = Lead::query();
        $query->leftJoin('users as lead_owner', 'lead_owner.id', '=', 'leads.assigned_to');
        $query->leftJoin('users as created_by', 'created_by.id', '=', 'leads.user_id');
        $query->leftJoin('users as architect', 'architect.id', '=', 'leads.architect');
		$query->leftJoin('users as electrician', 'electrician.id', '=', 'leads.electrician');
		$query->leftJoin('crm_setting_stage_of_site', 'crm_setting_stage_of_site.id', '=', 'leads.site_stage');
        $query->leftJoin('city_list', 'city_list.id', '=', 'leads.city_id');
        $query->whereIn('leads.source_type', ['textnotrequired-1', 'textnotrequired-11', 'textnotrequired-12']);
      
        if ($isSalePerson == 1) {
            $query->whereIn('leads.assigned_to', $childSalePersonsIds);
        }
        $query->select($selectColumns);
        $query->limit($request->length);
        $query->offset($request->start);
        $query->orderBy($sortingColumns[$request['order'][0]['column']], $request['order'][0]['dir']);
        
        $isFilterApply = 0;
        $search_value = '';

        if (isset($request['search']['value'])) {
            $isFilterApply = 1;
            $search_value = $request['search']['value'];
            $query->where(function ($query) use ($search_value, $searchColumns) {
                for ($i = 0; $i < count($searchColumns); $i++) {
                    if ($i == 0) {
                        $query->where($searchColumns[$i], 'like', '%' . $search_value . '%');
                    } else {
                        $query->orWhere($searchColumns[$i], 'like', '%' . $search_value . '%');
                    }
                }
            });
        }

        if (!empty($searchValue)) {
            $query->where(function ($query) use ($searchValue, $searchColumns) {
                foreach ($searchColumns as $column) {
                    $query->orWhere($column, 'like', '%' . $searchValue . '%');
                }
            });
        }

        $data = $query->get();
        $data = json_decode(json_encode($data), true);

        $viewData = [];
        $LeadStatus = getLeadStatus();
        
        foreach ($data as $key => $value) {
            $site_type = ''; 
            $CRMSettingSiteType = CRMSettingSiteType::find($value['site_type']);
            if ($CRMSettingSiteType) {
                $site_type = $CRMSettingSiteType->name;
            }

            $bhk = ''; 
            $CRMSettingBhk = CRMSettingBHK::find($value['bhk']);
            if ($CRMSettingBhk) {
                $bhk = $CRMSettingBhk->name;
            }

            $want_to_cover = ''; 
            $CRMSettingWantToCover = CRMSettingWantToCover::find($value['want_to_cover']);
            if ($CRMSettingWantToCover) {
                $want_to_cover = $CRMSettingWantToCover->name;
            }

            $competitor = ''; 
            $CRMSettingCompetitors = CRMSettingCompetitors::find($value['competitor']);
            if ($CRMSettingCompetitors) {
                $competitor = $CRMSettingCompetitors->name;
            }

            $closing_date_time = convertDateAndTime($value['closing_date_time'], "date") .' '.convertDateAndTime($value['closing_date_time'], "time");
            $create_date_time = convertDateAndTime($value['created_at'], "date") .' '.convertDateAndTime($value['created_at'], "time");

            $prifix = $value['is_deal'] == 1 ? 'DEAL' : 'LEAD';

            $source_type_explode = explode('-', $value['source_type']);
            $source_type_lable = '';
            foreach ($source_type as $source_key => $source_value) {
                if ($source_value['type'] == $source_type_explode[0] && $source_value['id'] == $source_type_explode[1]) {
                    $source_type_lable = $source_value['lable'];
                }
            }

            if ($value['status'] != 0) {
                $viewData[$key]['status'] = $LeadStatus[$value['status']]['name'];
            } else {
                $viewData[$key]['status'] = 'not define';
            }
            
            if (isset($value['quotation_file']) && $value['quotation_file'] !== "") {
                $viewData[$key]['quotation_file'] = "<a class='btn btn-sm btn-success btn-quotation' target='_blank'  href='" . Config::get('app.url') . "/" . $value['quotation_file'] . "' data-bs-toggle='tooltip' title='Quotation' >Quotation</a>";
            } else {
                $viewData[$key]['quotation_file'] = "-";
            }

            if ($data[$key]['architect'] == 0) {
                $data[$key]['architect_name'] = "-";
                $data[$key]['architect_phone_number'] = "-";
            } else {

                $data[$key]['architect_name'] = $data[$key]['architect_first_name'] . " " . $data[$key]['architect_last_name'];

                $data[$key]['architect_phone_number'] = "+91 " . $data[$key]['architect_phone_number'];
            }

            if ($data[$key]['electrician'] == 0) {
                $data[$key]['electrician_name'] = "-";
                $data[$key]['electrician_phone_number'] = "-";
            } else {

                $data[$key]['electrician_name'] = $data[$key]['electrician_first_name'] . " " . $data[$key]['electrician_last_name'];

                $data[$key]['electrician_phone_number'] = "+91 " . $data[$key]['electrician_phone_number'];
            }

            $client_name = $value['first_name'] . ' ' . $value['last_name'];
            $viewData[$key]['name'] = '<p class="border-box font-size-14 mb-0 text-dark d-flex justify-content-between align-items-center"><span><span class="lable-inquiry-id" id="inquiry-id-' . $data[$key]['id'] . '" >#' . $data[$key]['id'] . '</span><span id="inquiry-name-16539" data-bs-toggle="tooltip" title="" data-bs-original-title="'.$client_name.'"> '. highlightString($client_name,$search_value).' </span></span><a class="lable-inquiry-id" id="inquiry-id-16539">' . $prifix . '</a></p><p class="border-box font-size-14 mb-0 text-dark d-flex justify-content-between align-items-center"><span class="lable-inquiry-phone"><i class="bx bx-phone bx-sm extrasmallfont" data-bs-toggle="tooltip" title="" data-bs-original-title="Mobile No." aria-label="Mobile No."></i> +91 '. highlightString($value['phone_number'],$search_value).'</span></p><p class="border-box font-size-14 mb-0 text-dark d-flex justify-content-between align-items-center"><span class="lable-inquiry-phone"><i class="bx bx-envelope bx-sm extrasmallfont" data-bs-toggle="tooltip" title="" data-bs-original-title="Email ID." aria-label="Email ID."></i>  '. highlightString($value['email'],$search_value).'</span></p><p class="border-box font-size-14 mb-0 text-dark"><i class="bx bx-map bx-sm extrasmallfont" data-bs-toggle="tooltip" title="" data-bs-original-title="Address" aria-label="Address"></i><span data-bs-toggle="tooltip" title="" data-bs-original-title="'. $value['house_no'] . ' ' .$value['addressline1'] . ' ' . $value['addressline2'] . '">
            '. highlightString($value['house_no'] .'  '. $value['addressline1'] .'  '. $value['addressline2'],$search_value).'</span><br><span data-bs-toggle="tooltip" title="" data-bs-original-title="'.$value['pincode'] . ', '.$value['area'] . '">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.highlightString($value['pincode'] . ', '.$value['area'],$search_value) . '</span><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . highlightString($value['city_list_name'],$search_value) . '</p>';
 
            $viewData[$key]['created_by'] = '<td><p class="border-box mb-0"><i data-bs-toggle="tooltip" title="" class="bx bxs-user bx-sm extrasmallfont" data-bs-original-title="Created By" aria-label="Created By"></i> <span data-bs-toggle="tooltip" title="" data-bs-original-title="'.$value['created_by_first_name'] . ' ' . $value['created_by_last_name'].'">'. highlightString($value['created_by_first_name'] . ' ' . $value['created_by_last_name'],$search_value).'</span><a data-bs-toggle="tooltip" href="javascript: void(0);" class="createdicon" title="" data-bs-original-title="Created Date &amp; Time : '.$create_date_time.'" aria-label="Created Date &amp; Time : '.$create_date_time.'"><i class="bx bx-calendar"></i></a></p><p class="border-box mb-0 "><i data-bs-toggle="tooltip" title="" class="bx bx-shield-alt bx-sm extrasmallfont" data-bs-original-title="Source Type" aria-label="Source Type"></i> '. highlightString($source_type_lable,$search_value).'<br><i data-bs-toggle="tooltip" title="" class="bx bxs-user bx-sm extrasmallfont" data-bs-original-title="Source" aria-label="Source"></i><span data-bs-toggle="tooltip" title="" data-bs-original-title="'.$value['source'].'"><a href="javascript: void(0)"> '. highlightString($value['source'],$search_value).'</a></span></p><p class="border-box mb-0 "><i data-bs-toggle="tooltip" title="" class="bx bxs-user bx-sm extrasmallfont" data-bs-original-title="Architect Name" aria-label="Architect Name"></i><span data-bs-toggle="tooltip" title="" data-bs-original-title="'.displayStringLenth($data[$key]['architect_name'], 25).'"></span> '. highlightString(displayStringLenth($data[$key]['architect_name'], 25),$search_value).' <br> <i class="bx bx-phone bx-sm extrasmallfont" data-bs-toggle="tooltip" title="" data-bs-original-title="Architect Mobile No." aria-label="Architect Mobile No."></i> '. highlightString($data[$key]['architect_phone_number'],$search_value).'</p><p class="border-box mb-0 "><i data-bs-toggle="tooltip" title="" class="bx bxs-user bx-sm extrasmallfont" data-bs-original-title="Electrician Name" aria-label="Electrician Name"></i> <span data-bs-toggle="tooltip" title="" data-bs-original-title="'.displayStringLenth($data[$key]['electrician_name'], 25).'"></span>'. highlightString(displayStringLenth($data[$key]['electrician_name'], 25),$search_value).' <br> <i class="bx bx-phone bx-sm extrasmallfont" data-bs-toggle="tooltip" title="" data-bs-original-title="Electrician Mobile No." aria-label="Electrician Mobile No."></i> '. highlightString($data[$key]['electrician_phone_number'],$search_value).'</p></td>';
            
            $viewData[$key]['site_stage'] = '<td><p class="border-box mb-0"><i data-bs-toggle="tooltip" title="" class="bx bx-cube bx-sm extrasmallfont" data-bs-original-title="Site Stage" aria-label="Site Stage"></i><span data-bs-toggle="tooltip" title="" data-bs-original-title="'.$data[$key]['name'].'"> '. highlightString($data[$key]['name'],$search_value).'</span><i data-bs-toggle="tooltip" title="" class="bx bx-calendar bx-sm extrasmallfont" style="margin-left:1px;float:right" data-bs-original-title="Closing Date &amp; Time : '.$closing_date_time.'" aria-label="'.$closing_date_time.'"></i>&nbsp;&nbsp;&nbsp;&nbsp; <button style="margin-top: 3px;display: none;float:right" class="save_answer_closing_date_time btn btn-success btn-sm waves-effect waves-light" id="save_answer_closing_date_time_16545">Save</button></p><p class="border-box mb-0">
            <span class="lable-inquiry-quotation"><i data-bs-toggle="tooltip" title="" class="bx bx-receipt bx-sm extrasmallfont" data-bs-original-title="Quotation" aria-label="Quotation"></i>'.$viewData[$key]['quotation_file'].' / <i data-bs-toggle="tooltip" title="" class="bx bx bx-rupee  bx-sm extrasmallfont" data-bs-original-title="Quotation Amount" aria-label="Quotation Amount"></i>'.$value['quotation'].'</span></p>
            <p class="border-box mb-0 ">Site Type - '. highlightString($site_type,$search_value).'<br></p>
            <p class="border-box mb-0 ">Squre Foot - '. highlightString($value['sq_foot'],$search_value).'<br></p>
            <p class="border-box mb-0 ">BHK - '. highlightString($bhk,$search_value).'<br></p>
            <p class="border-box mb-0 ">Want To Cover - '. highlightString($want_to_cover,$search_value).'<br></p>
            <p class="border-box mb-0 ">Competitors - '. highlightString($competitor,$search_value).'<br></p></td>';

            $viewData[$key]['status'] = '<td><p class="border-box mb-0"><i data-bs-toggle="tooltip" title="" class="bx bxs-user bx-sm extrasmallfont" data-bs-original-title="Assigned" aria-label="Assigned"></i> <span data-bs-toggle="tooltip" title="" data-bs-original-title="'.$value['lead_owner_first_name'] . ' ' . $value['lead_owner_last_name'].'">'. highlightString($value['lead_owner_first_name'] . ' ' . $value['lead_owner_last_name'],$search_value).'</span><br></p>
            <p class="border-box mb-0"><i data-bs-toggle="tooltip" title="" class="bx bx-disc bx-sm extrasmallfont" data-bs-original-title="Status" aria-label="Status"></i>&nbsp;&nbsp;&nbsp;<span class="lable-status" data-bs-toggle="tooltip" title="" data-bs-original-title="Status">'. $LeadStatus[$value['status']]['name'] .'</span></select></p></td>';
        
        }

        $jsonData = [
            'draw' => intval($request['draw']),
            // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            'recordsTotal' => intval($recordsTotal),
            // total number of records
            'recordsFiltered' => intval($recordsFiltered),
            // total number of records after searching, if there is no searching then totalFiltered = totalData
            'data' => $viewData,
            // total data array
            'dataed' => $data, // total data array
        ];
        return $jsonData;
    }
}
