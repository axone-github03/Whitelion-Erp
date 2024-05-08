<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\LeadContact;
use Illuminate\Http\Request;
use App\Models\MarketingLeadSync;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use App\Http\Controllers\Whatsapp\WhatsappApiContoller;
use DB;


class MarketingLeadController extends Controller
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
        $data['title'] = "Marketing Lead";
        return view('marketing_lead/index', compact('data'));
    }

    public function ajax(Request $request)
    {
        $searchColumns = array(
            0 => 'marketing_lead_sync.id',
            1 => 'marketing_lead_sync.email',
            2 => 'marketing_lead_sync.full_name',
            3 => 'marketing_lead_sync.phone_number',
            4 => 'marketing_lead_sync.city',
            5 => 'marketing_lead_sync.platform',
        );

        $sortingColumns = array(
            0 => 'marketing_lead_sync.id',
            1 => 'marketing_lead_sync.email',
            2 => 'marketing_lead_sync.full_name',
            3 => 'marketing_lead_sync.phone_number',
            4 => 'marketing_lead_sync.city',
            5 => 'marketing_lead_sync.platform',
            6 => 'marketing_lead_sync.status',
            7 => 'marketing_lead_sync.remark',
            8 => 'marketing_lead_sync.source_type',
            9 => 'marketing_lead_sync.sub_source',
            10 => 'marketing_lead_sync.source',
            11 => 'marketing_lead_sync.assign_to',
            12 => 'marketing_lead_sync.lead_id',
            13 => 'marketing_lead_sync.date',
        );
        
        
        $query = MarketingLeadSync::query();
        $query->where('marketing_lead_sync.status', '!=', 1);

        $recordsTotal = $query->count();
        $search_value = '';

        if (isset($request['search']['value'])) {
            $search_value = $request['search']['value'];
            $query->where(function ($query) use ($search_value, $searchColumns) {
                for ($i = 0; $i < count($searchColumns); $i++) {
                    if ($i == 0) {
                        $query->where($searchColumns[$i], 'like', "%" . $search_value . "%");
                    } else {
                        $query->orWhere($searchColumns[$i], 'like', "%" . $search_value . "%");
                    }
                }
            });
        }
        $query->where('marketing_lead_sync.status', '!=', 1);
        $recordsFiltered = $query->count();
    
        $query->orderBy($sortingColumns[$request['order'][0]['column']], $request['order'][0]['dir']);
        $query->offset($request->start);
        $query->limit($request->length);
    
        $data = $query->get();
    
        $viewData = array();
        foreach ($data as $key => $value) {
            $viewData[$key]['id'] = '<div class="avatar-xs"><span class="avatar-title rounded-circle">' .  highlightString($value['id'],$search_value) . '</span></div>';
            $viewData[$key]['date'] = '<p class="text-muted mb-0">' .  highlightString($value['date'],$search_value) .'</p>';
            $viewData[$key]['email'] = '<p class="text-muted mb-0">' .  highlightString($value['email'],$search_value) .'</p>';
            $viewData[$key]['full_name'] = '<p class="text-muted mb-0">' .  highlightString($value['full_name'],$search_value) .'</p>';
            $viewData[$key]['phone_number'] = '<p class="text-muted mb-0">' .  highlightString($value['phone_number'],$search_value) .'</p>';
            $viewData[$key]['city'] = '<p class="text-muted mb-0">' .  highlightString($value['city'],$search_value) .'</p>';
            $viewData[$key]['platform'] = '<p class="text-muted mb-0">' .  highlightString($value['platform'],$search_value) .'</p>';
            $viewData[$key]['source_type'] = '<p class="text-muted mb-0">' .  highlightString($value['source_type'],$search_value) .'</p>';
            $viewData[$key]['sub_source'] = '<p class="text-muted mb-0">' .  highlightString($value['sub_source'],$search_value) .'</p>';
            $viewData[$key]['source'] = '<p class="text-muted mb-0">' .  highlightString($value['source'],$search_value) .'</p>';
            $viewData[$key]['assign_to'] = '<p class="text-muted mb-0">' .  highlightString($value['assign_to'],$search_value) .'</p>';
            $viewData[$key]['lead_id'] = '<p class="text-muted mb-0">' .  highlightString($value['lead_id'],$search_value) .'</p>';
            $viewData[$key]['status'] = marketingLeadStatus($value['status']);
            $statusClass = "";

			if ($value['status'] == 0) {
				$statusClass = 'badge-soft-warning ';
			} else if ($value['status'] == 1) {
				$statusClass = 'badge-soft-success ';
			} else if ($value['status'] == 2) {
				$statusClass = 'badge-soft-danger ';
			} else {
				$statusClass = 'badge-soft-warning ';
			}

			$viewData[$key]['status'] = '<span class="badge ' . $statusClass . ' badge-pill badgefont-size-11">' . $viewData[$key]['status'] . '</span>';
            $viewData[$key]['remark'] = '<p class="text-muted mb-0">' .  highlightString($value['remark'],$search_value) .'</p>';
        }
    
        // Build the response array
        $jsonData = array(
            "draw" => intval($request['draw']),
            "recordsTotal" => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "data" => $viewData,
        );
    
        return $jsonData;
    }

    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'q_marketing_lead_excel' => ['required'],
        ]);
        if ($validator->fails()) {

            $response = array();
            $response['status'] = 0;
            $response['msg'] = "The request could not be understood by the server due to malformed syntax";
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors();

            return response()->json($response)->header('Content-Type', 'application/json');
        } else {
            $the_file = $request->file('q_marketing_lead_excel');
            try {
                $spreadsheet = IOFactory::load($the_file->getRealPath());
                $sheet        = $spreadsheet->getActiveSheet();
                $row_limit    = $sheet->getHighestDataRow();
                $row_range    = range(2, $row_limit);
                $data = array();
                foreach ($row_range as $row) {
                    
                    $date = $sheet->getCell('A' . $row)->getValue();
                    $platform = $sheet->getCell('B' . $row)->getValue();
                    $how_soon_are_you_considering_to_automate_your_house = $sheet->getCell('C' . $row)->getValue();
                    $how_many_bedrooms_does_your_apartment_have = $sheet->getCell('D' . $row)->getValue();
                    $email = $sheet->getCell('E' . $row)->getValue();
                    $full_name = $sheet->getCell('F' . $row)->getValue();
                    $rawPhoneNumber = $sheet->getCell('G' . $row)->getValue();
                    $phone_number = str_replace(['p:+91', 'p:'], '', $rawPhoneNumber);
                    $city = $sheet->getCell('H' . $row)->getValue();
                    $telesales_team_remark= $sheet->getCell('I' . $row)->getValue();
                    $sales_team_remark= $sheet->getCell('M' . $row)->getValue();
                    $recorde_id = $sheet->getCell('R' . $row)->getValue();
                    $source_type = $sheet->getCell('S' . $row)->getValue();
                    $sub_source = $sheet->getCell('T' . $row)->getValue();
                    $source = $sheet->getCell('U' . $row)->getValue();
                    $assign_to = $sheet->getCell('V' . $row)->getValue();

                    if (!empty($full_name)) {
                        $MarketingLeadSync = new MarketingLeadSync();
                        $MarketingLeadSync->date = $date;
                        $MarketingLeadSync->how_soon_are_you_considering_to_automate_your_house = $how_soon_are_you_considering_to_automate_your_house;
                        $MarketingLeadSync->how_many_bedrooms_does_your_apartment_have = $how_many_bedrooms_does_your_apartment_have;
                        $MarketingLeadSync->email = $email;
                        $MarketingLeadSync->full_name = $full_name;
                        $MarketingLeadSync->phone_number = $phone_number;
                        $MarketingLeadSync->city = $city;
                        $MarketingLeadSync->telesales_team_remark= $telesales_team_remark;
                        $MarketingLeadSync->sales_team_remark= $sales_team_remark;
                        $MarketingLeadSync->recorde_id = $recorde_id;
                        if($platform == 'fb'){
                            $MarketingLeadSync->platform = 'facebook';
                        }else if($platform == 'ig'){
                            $MarketingLeadSync->platform = 'instagram'; 
                        }else if($platform == 'googleads'){
                            $MarketingLeadSync->platform = 'Google Ads'; 
                        }else{
                            $MarketingLeadSync->platform = '';
                        }
                        $MarketingLeadSync->source_type = $source_type;
                        $MarketingLeadSync->sub_source = $sub_source;
                        $MarketingLeadSync->source = $source;
                        $MarketingLeadSync->assign_to = $assign_to;
                        $MarketingLeadSync->entryby = Auth::user()->id;
                        $MarketingLeadSync->entryip = $request->ip();
                        $MarketingLeadSync->save();
                        if($MarketingLeadSync){
                            $MrketingUpdate = MarketingLeadSync::find($MarketingLeadSync->id);
                            $chkLeadPhoneNumber = Lead::where('phone_number', $MarketingLeadSync->phone_number)->first();
                            $chkLeadEmail = Lead::where('email', $MarketingLeadSync->email)->first();
                            if ($chkLeadPhoneNumber) {
                                $MrketingUpdate->lead_id = 0;
                                $MrketingUpdate->status = 0;
                                $MrketingUpdate->remark = 'Lead with the same phone number already exists. #'.$chkLeadPhoneNumber->id;
                                $MrketingUpdate->save();
                            }elseif ($chkLeadEmail) {
                                $MrketingUpdate->lead_id = 0;
                                $MrketingUpdate->status = 0;
                                $MrketingUpdate->remark = 'Lead with the same email already exists. #'.$chkLeadEmail->id;
                                $MrketingUpdate->save();
                            }else{
                                $clientName = explode(' ', $MarketingLeadSync->full_name);
							    $first_name = $clientName[0];
							    $last_name = isset($clientName[1]) ? implode(' ', array_slice($clientName, 1)) : '';
                                $lead = new Lead();
                                $lead->first_name = $first_name ?? '';
                                $lead->last_name = $last_name ?? '';
                                $lead->email = $MarketingLeadSync->email;
                                $lead->customer_id = 0;
                                $lead->phone_number = $MarketingLeadSync->phone_number;
                                if($MarketingLeadSync->assign_to == 'Farhad'){
                                    $lead->assigned_to = 5695;
                                }else{
                                    $lead->assigned_to = 3566;
                                }
                                
                                if($MarketingLeadSync->platform == 'facebook'){
                                    $lead->source_type = 'textnotrequired-1';
                                }else if($MarketingLeadSync->platform == 'instagram'){
                                    $lead->source_type = 'textnotrequired-11';
                                }else if($MarketingLeadSync->platform == 'Google Ads'){
                                    $lead->source_type = 'textnotrequired-12';
                                }else{
                                    $lead->source_type = '';
                                }
                                $lead->source = $MarketingLeadSync->source;
                                
                                $lead->is_deal = 0;
                                $lead->status = 1;
                                $lead->sub_status = 0;
                                $lead->created_by = Auth::user()->id;
                                $lead->updated_by = Auth::user()->id;
                                $lead->user_id = Auth::user()->id;
                                $lead->save();
                                if ($lead) {
                                    $timeline = array();
                                    $timeline['lead_id'] = $lead->id;
                                    $timeline['type'] = "lead-generate";
                                    $timeline['reffrance_id'] = $lead->id;
                                    $timeline['description'] = "Lead created by " . Auth::user()->first_name . " " . Auth::user()->last_name;
                                    $timeline['source'] = "WEB";
                                    saveLeadTimeline($timeline);

                                    $LeadContact = new LeadContact();
                                    $LeadContact->lead_id = $lead->id;
                                    $LeadContact->contact_tag_id = 1;
                                    $LeadContact->first_name = $lead->first_name;
                                    $LeadContact->last_name = $lead->last_name;
                                    $LeadContact->phone_number = $lead->phone_number;
                                    $LeadContact->alernate_phone_number = 0;
                                    $LeadContact->email = $lead->email;
                                    $LeadContact->type = 0;
                                    $LeadContact->type_detail = 0;
                                    $LeadContact->save();
        
                                    $LeadSource1 = new LeadSource();
                                    $LeadSource1->lead_id = $lead->id;
                                    $LeadSource1->source_type = $lead['source_type'];
                                    $LeadSource1->source = $lead['source'];
                                    $LeadSource1->is_main = 1;
                                    $LeadSource1->save();
                                }
                            }
                            
                        }
                    }
                    
                }
                $response = successRes('Data Imported Successfully');
                return redirect()->route('crm.marketing.lead');
            } catch (Exception $e) {
                $response = errorRes($e->getMessage());
                return response()->json($response)->header('Content-Type', 'application/json');
            }
        }
    }
}