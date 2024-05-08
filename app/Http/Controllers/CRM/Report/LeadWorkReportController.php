<?php

namespace App\Http\Controllers\CRM\Report;

use App\Http\Controllers\Controller;

use App\Models\ChannelPartner;
use App\Models\LeadTimeline;
use App\Models\Exhibition;
use App\Models\User;
use App\Models\LeadUpdate;
use App\Models\CRMSettingStageOfSite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

//use Session;

class LeadWorkReportController extends Controller
{

	public function __construct()
	{

		$this->middleware(function ($request, $next) {

			$tabCanAccessBy = array(0, 1, 2, 9);

			if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
				return redirect()->route('dashboard');
			}
			return $next($request);
		});
	}

	public function index()
	{
		$data = array();
		$data['title'] = "Lead Report";
		return view('crm/lead/report/lead_work_report', compact('data'));
	}

	function ajax(Request $request)
	{
		//DB::enableQueryLog();

		$req_startdate = $request->start_date;
		$req_enddate = $request->end_date;
		$user_id = isset($request->user_id) ? explode(',',$request->user_id) : '';

		$startDate = date('Y-m-d', strtotime($req_startdate));
		$endDate = date('Y-m-d', strtotime($req_enddate));

		$isTaleSalesUser = isTaleSalesUser();
		$isSalePerson = isSalePerson();
        $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
        $source_type = getInquirySourceTypes();
        if ($isSalePerson == 1) {
            $parentSalesUsers = getParentSalePersonsIds(Auth::user()->id);
            $childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
        }

		$LeadUpdateSearchColumns = array(
			'lead_updates.lead_id',
			'leads.phone_number',
			'lead_updates.message'
		);
		$LeadUpdateColumns = array(
			'lead_updates.user_id',
			'lead_updates.lead_id',
			'leads.is_deal',
			'leads.closing_date_time',
			'leads.source_type',
			'leads.source',
			'leads.status',
			'leads.site_stage',
			'leads.phone_number',
			'lead_updates.message as message',
			'lead_updates.created_at',
		);
		$LeadUpdateCount = LeadUpdate::query();
        $LeadUpdateCount->select($LeadUpdateColumns);
		$LeadUpdateCount->leftJoin('leads', 'leads.id', '=', 'lead_updates.lead_id');
        $LeadUpdateCount->leftJoin('users', 'users.id', '=', 'lead_updates.user_id');
		$LeadUpdateCount->leftJoin('users as lead_owner', 'lead_owner.id', '=', 'leads.assigned_to');
		$LeadUpdateCount->whereDate('lead_updates.created_at', '>=', $startDate);
		$LeadUpdateCount->whereDate('lead_updates.created_at', '<=', $endDate);
		if (isset($user_id) && is_array($user_id)) {
			$LeadUpdateCount->whereIn('lead_updates.user_id', $user_id);
		}
		if ($isSalePerson == 1) {
            $LeadUpdateCount->whereIn('leads.assigned_to', $childSalePersonsIds);
        }
		if ($isTaleSalesUser == 1) {
            $LeadUpdateCount->where('lead_updates.user_id', Auth::user()->id);
        }

		$LeadTimelineColumnsColumns = array(
			'lead_timeline.lead_id',
			'leads.phone_number',
			'lead_timeline.description as message',
			'lead_timeline.type'
		);
		$LeadTimelineColumns = array(
			'lead_timeline.user_id',
			'lead_timeline.lead_id',
			'leads.is_deal',
			'leads.closing_date_time',
			'leads.source_type',
			'leads.source',
			'leads.status',
			'leads.site_stage',
			'leads.phone_number',
			'lead_timeline.description as message',
			'lead_timeline.created_at',
		);
		$LeadTimelineCount = LeadTimeline::query();
		$LeadTimelineCount->select($LeadTimelineColumns);
		$LeadTimelineCount->leftJoin('users', 'users.id', '=', 'lead_timeline.user_id');
		$LeadTimelineCount->leftJoin('leads', 'leads.id', '=', 'lead_timeline.lead_id');
		$LeadTimelineCount->leftJoin('users as lead_owner', 'lead_owner.id', '=', 'leads.assigned_to');
		$LeadTimelineCount->unionAll($LeadUpdateCount);
		$LeadTimelineCount->orderBy('created_at', 'desc');
		$LeadTimelineCount->whereDate('lead_timeline.created_at', '>=', $startDate);
		$LeadTimelineCount->whereDate('lead_timeline.created_at', '<=', $endDate);
		if (isset($user_id) && is_array($user_id)) {
			$LeadTimelineCount->whereIn('lead_timeline.user_id', $user_id);
		}
		if ($isSalePerson == 1) {
            $LeadTimelineCount->whereIn('leads.assigned_to', $childSalePersonsIds);
        }
		if ($isTaleSalesUser == 1) {
            $LeadTimelineCount->where('lead_timeline.user_id', Auth::user()->id);
        }


		$recordsTotal = $LeadTimelineCount->count();
		$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.

		
		$LeadUpdate = LeadUpdate::query();
        $LeadUpdate->select($LeadUpdateColumns);
        $LeadUpdate->selectRaw('CONCAT(lead_updates.task," - ",lead_updates.task_title) as title');
        $LeadUpdate->selectRaw('CONCAT(users.first_name," ",users.last_name) as user_name');
		$LeadUpdate->selectRaw('CONCAT(leads.first_name," ",leads.last_name) as lead_full_name');
		$LeadUpdate->selectRaw('CONCAT(lead_owner.first_name," ",lead_owner.last_name) as lead_owner_name');
		$LeadUpdate->leftJoin('leads', 'leads.id', '=', 'lead_updates.lead_id');
        $LeadUpdate->leftJoin('users', 'users.id', '=', 'lead_updates.user_id');
		$LeadUpdate->leftJoin('users as lead_owner', 'lead_owner.id', '=', 'leads.assigned_to');
		$LeadUpdate->whereDate('lead_updates.created_at', '>=', $startDate);
		$LeadUpdate->whereDate('lead_updates.created_at', '<=', $endDate);
		if (isset($user_id) && is_array($user_id)) {
			$LeadUpdate->whereIn('lead_updates.user_id', $user_id);
		}
		if ($isSalePerson == 1) {
            $LeadUpdate->whereIn('leads.assigned_to', $childSalePersonsIds);
        }
		if ($isTaleSalesUser == 1) {
            $LeadUpdate->where('lead_updates.user_id', Auth::user()->id);
        }

		// if (isset($request['search']['value'])) {
		// 	$isFilterApply = 1;
		// 	$search_value = $request['search']['value'];
		// 	$LeadUpdate->where(function ($query) use ($search_value, $LeadUpdateSearchColumns) {
		// 		for ($i = 0; $i < count($LeadUpdateSearchColumns); $i++) {
		// 			if ($i == 0) {
		// 				$query->whereRaw($LeadUpdateSearchColumns[$i], 'like', "%" . $search_value . "%");
		// 			} else {
		// 				$query->orWhereRaw($LeadUpdateSearchColumns[$i], 'like', "%" . $search_value . "%");
		// 			}
		// 		}
		// 	});
		// }

		$LeadTimeline = LeadTimeline::query();
		$LeadTimeline->select($LeadTimelineColumns);
        $LeadTimeline->selectRaw('lead_timeline.type as title');
        $LeadTimeline->selectRaw('CONCAT(users.first_name," ",users.last_name) as user_name');
        $LeadTimeline->selectRaw('CONCAT(leads.first_name," ",leads.last_name) as lead_full_name');
		$LeadTimeline->selectRaw('CONCAT(lead_owner.first_name," ",lead_owner.last_name) as lead_owner_name');
		$LeadTimeline->leftJoin('users', 'users.id', '=', 'lead_timeline.user_id');
		$LeadTimeline->leftJoin('leads', 'leads.id', '=', 'lead_timeline.lead_id');
		$LeadTimeline->leftJoin('users as lead_owner', 'lead_owner.id', '=', 'leads.assigned_to');
		$LeadTimeline->unionAll($LeadUpdate);
		$LeadTimeline->orderBy('created_at', 'desc');
		$LeadTimeline->whereDate('lead_timeline.created_at', '>=', $startDate);
		$LeadTimeline->whereDate('lead_timeline.created_at', '<=', $endDate);
		if (isset($user_id) && is_array($user_id)) {
			$LeadTimeline->whereIn('lead_timeline.user_id', $user_id);
		}
		if ($isSalePerson == 1) {
            $LeadTimeline->whereIn('leads.assigned_to', $childSalePersonsIds);
        }
		if ($isTaleSalesUser == 1) {
            $LeadTimeline->where('lead_timeline.user_id', Auth::user()->id);
        }
		$LeadTimeline->limit($request->length);
		$LeadTimeline->offset($request->start);
		// $LeadTimeline->orderBy($searchColumns[$request['order'][0]['column']], $request['order'][0]['dir']);
		$isFilterApply = 0;

		// if (isset($request['search']['value'])) {
		// 	$isFilterApply = 1;
		// 	$search_value = $request['search']['value'];
		// 	$LeadTimeline->where(function ($query) use ($search_value, $LeadTimelineColumnsColumns) {
		// 		for ($i = 0; $i < count($LeadTimelineColumnsColumns); $i++) {
		// 			if ($i == 0) {
		// 				$query->whereRaw($LeadTimelineColumnsColumns[$i], 'like', "%" . $search_value . "%");
		// 			} else {
		// 				$query->orWhereRaw($LeadTimelineColumnsColumns[$i], 'like', "%" . $search_value . "%");
		// 			}
					
		// 		}
		// 	});
		// }

		$data = $LeadTimeline->get();
		// echo "<pre>";
		// print_r(DB::getQueryLog());
		// die;

		$data = json_decode(json_encode($data), true);

		if ($isFilterApply == 1) {
			$recordsFiltered = count($data);
		}

		$LeadStatus = getLeadStatus();

		foreach ($data as $key => $value) {
			if ($value['is_deal'] == 0) {
				$routeLead = route('crm.lead') . "?id=" . $value['lead_id'];
			} else {
				$routeLead = route('crm.deal') . "?id=" . $value['lead_id'];
			}

			if ($value['is_deal'] == 0) {
				$prifix = 'L';
			} else if ($value['is_deal'] == 1) {
				$prifix = 'D';
			}

			$data[$key]['col_1'] = "<a href='" . $routeLead . "' > " . "#" . $prifix . $value['lead_id'] . "</a>";
			$data[$key]['col_2'] = '<p class="text-muted mb-0">' . $value['lead_full_name'] . '</p><p class="text-muted mb-0">' . $value['phone_number'] . '</p>';
			$CRMSettingStageOfSite = CRMSettingStageOfSite::find($value['site_stage']);
			$site_stage = "";
            if ($CRMSettingStageOfSite) {
				$site_stage = $CRMSettingStageOfSite->name;
            }

			$closing_date_time = $value['closing_date_time'];
            if (($closing_date_time != '') || ($closing_date_time != null)) {
                $closing_date_time = date('Y-m-d', strtotime($closing_date_time));
            } else {
                $closing_date_time = "-";
            }
			if ($value['is_deal'] == 0) {
				$closing_date_time = "-";
			}
			$data[$key]['col_3'] = '<p class="text-muted mb-0">'. $site_stage .'</p><p class="text-muted mb-0">' . $closing_date_time . '</p>';
			
			$source_type = explode("-", $value['source_type']);

			$sourceType = '';
			foreach (getLeadSourceTypes() as $skey => $svalue) {
				if ($svalue['type'] == $source_type[0] && $svalue['id'] == $source_type[1]) {
					$sourceType = $svalue['lable'];
				}
			}
			$source = '';
			$source .= '<span>'. $value['lead_owner_name'] .'</span>';
			$source .= '<div class="border my-1"></div>';
			if($source_type[0] == 'user') {
				if(in_array($source_type[1], array(101, 102, 103, 104, 105))) {
					$sourceUser = ChannelPartner::select('firm_name')->where('user_id', $value['source'])->first();
					if($sourceUser) {
						$source .= '<span>'.$sourceUser['firm_name'] .'</span> - <span class="badge badge-pill badge-soft-success">' .$sourceType. '</span>';
					} else {
						$source .= '';
					}
				} else {
					$sourceUser = User::find($value['source']);
					if($sourceUser) {
						$source .= '<span>'.$sourceUser['first_name'] .''. $sourceUser['last_name'] .'</span> - <span class="badge badge-pill badge-soft-success">' .$sourceType. '</span>';
					} else {
						$source .= '';
					}
				}
			} else if($source_type[0] == 'exhibition') {
				$sourceUser = Exhibition::find($value['source']);
				if($sourceUser) {
					$source .= '<span>'.$sourceUser['name'] . '</span> - <span class="badge badge-pill badge-soft-success">' .$sourceType. '</span>';
				} else {
					$source .= '';
				}
			} else {
				$source .= '<span>'. $value['source'] .'</span> - <span class="badge badge-pill badge-soft-success">' .$sourceType. '</span>';
			}
			$source .= '';

			$data[$key]['col_4'] = $source;

			if ($value['status'] != 0) {
				$data[$key]['col_5'] = $LeadStatus[$value['status']]['name'];
			} else {
				$data[$key]['col_5'] = "not define";
			}


			$data[$key]['col_6'] = '<p class="text-muted mb-0">'. $value['title'] .'</p><p class="text-muted mb-0">' . $value['message'] . '</p>';
			$data[$key]['col_7'] = '<p class="text-muted mb-0">' . $value['user_name'] . '</p>';
			$data[$key]['col_8'] = "<p>" . convertDateAndTime($value['created_at'], "date") .'</br>'.convertDateAndTime($value['created_at'], "time") . '</p>';
		}

		$jsonData = array(
			"draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
			"recordsTotal" => intval($recordsTotal), // total number of records
			"recordsFiltered" => intval($recordsFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
			"data" => $data, // total data array
		);

		return $jsonData;
	}

	function searchUser(Request $request)
    {
		$isSalePerson = isSalePerson();
		if ($isSalePerson == 1) {
            $childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
        }
        $searchKeyword = $request->q;
            
        $data = User::select('users.id as id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"));
        $data->where('users.status', 1);
        $data->where(function ($query) use ($searchKeyword) {
            $query->where('users.first_name', 'like', '%' . $searchKeyword . '%');
            $query->orWhere('users.last_name', 'like', '%' . $searchKeyword . '%');
        });
		if ($isSalePerson == 1) {

            $data->where('users.type', 2);
            $data->whereIn('users.id', $childSalePersonsIds);
        } 

        $data->limit(20);
        $data = $data->get();

        $response = array();
        $response['results'] = $data;
        $response['pagination']['more'] = true;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

	function export(Request $request)
	{

		$req_startdate = $request->start_date;
		$req_enddate = $request->end_date;
		$user_id = isset($request->user_id) ? explode(',',$request->user_id) : '';

		$startDate = date('Y-m-d', strtotime($req_startdate));
		$endDate = date('Y-m-d', strtotime($req_enddate));
		$LeadStatus = getLeadStatus();
		$LeadUpdateColumns = array(
			'lead_updates.user_id',
			'lead_updates.lead_id',
			'leads.is_deal',
			'leads.closing_date_time',
			'leads.source_type',
			'leads.source',
			'leads.status',
			'leads.site_stage',
			'leads.phone_number',
			'lead_updates.message as message',
			'lead_updates.created_at',
		);

		$LeadUpdate = LeadUpdate::query();
        $LeadUpdate->select($LeadUpdateColumns);
        $LeadUpdate->selectRaw('CONCAT(lead_updates.task," - ",lead_updates.task_title) as title');
        $LeadUpdate->selectRaw('CONCAT(users.first_name," ",users.last_name) as user_name');
		$LeadUpdate->selectRaw('CONCAT(leads.first_name," ",leads.last_name) as lead_full_name');
		$LeadUpdate->selectRaw('CONCAT(lead_owner.first_name," ",lead_owner.last_name) as lead_owner_name');
		$LeadUpdate->leftJoin('leads', 'leads.id', '=', 'lead_updates.lead_id');
        $LeadUpdate->leftJoin('users', 'users.id', '=', 'lead_updates.user_id');
		$LeadUpdate->leftJoin('users as lead_owner', 'lead_owner.id', '=', 'leads.assigned_to');
		$LeadUpdate->whereDate('lead_updates.created_at', '>=', $startDate);
		$LeadUpdate->whereDate('lead_updates.created_at', '<=', $endDate);
		if (isset($user_id) && is_array($user_id)) {
			$LeadUpdate->whereIn('lead_updates.user_id', $user_id);
		}

		$LeadTimelineColumns = array(
			'lead_timeline.user_id',
			'lead_timeline.lead_id',
			'leads.is_deal',
			'leads.closing_date_time',
			'leads.source_type',
			'leads.source',
			'leads.status',
			'leads.site_stage',
			'leads.phone_number',
			'lead_timeline.description as message',
			'lead_timeline.created_at',
		);

		$LeadTimeline = LeadTimeline::query();
		$LeadTimeline->select($LeadTimelineColumns);
        $LeadTimeline->selectRaw('lead_timeline.type as title');
        $LeadTimeline->selectRaw('CONCAT(users.first_name," ",users.last_name) as user_name');
        $LeadTimeline->selectRaw('CONCAT(leads.first_name," ",leads.last_name) as lead_full_name');
		$LeadTimeline->selectRaw('CONCAT(lead_owner.first_name," ",lead_owner.last_name) as lead_owner_name');
		$LeadTimeline->leftJoin('users', 'users.id', '=', 'lead_timeline.user_id');
		$LeadTimeline->leftJoin('leads', 'leads.id', '=', 'lead_timeline.lead_id');
		$LeadTimeline->leftJoin('users as lead_owner', 'lead_owner.id', '=', 'leads.assigned_to');
		$LeadTimeline->unionAll($LeadUpdate);
		$LeadTimeline->orderBy('created_at', 'desc');
		$LeadTimeline->whereDate('lead_timeline.created_at', '>=', $startDate);
		$LeadTimeline->whereDate('lead_timeline.created_at', '<=', $endDate);
		if (isset($user_id) && is_array($user_id)) {
			$LeadTimeline->whereIn('lead_timeline.user_id', $user_id);
		}

		$data = $LeadTimeline->get();

		$headers = array("#No.","Lead Id","Type","Lead Name","Lead Phone Number","Site Stage","Closing Date","Lead Owner","Source Type","Source","Status","Title","Description","Created By","Created At");

		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="lead_work_report.csv"');

		$fp = fopen('php://output', 'wb');

		fputcsv($fp, $headers);

		foreach ($data as $key => $value) {

			if ($value['is_deal'] == 0) {
				$prifix = 'Lead';
			} else if ($value['is_deal'] == 1) {
				$prifix = 'Deal';
			}

			$LeadId = $value['lead_id'];
			$Type = $prifix;
			$LeadName = $value['lead_full_name'];
			$LeadPhoneNumber = $value['phone_number'];


			$CRMSettingStageOfSite = CRMSettingStageOfSite::find($value['site_stage']);
			$site_stage = "";
            if ($CRMSettingStageOfSite) {
				$site_stage = $CRMSettingStageOfSite->name;
            }

			$closing_date_time = $value['closing_date_time'];
            if (($closing_date_time != '') || ($closing_date_time != null)) {
                $closing_date_time = date('Y-m-d', strtotime($closing_date_time));
            } else {
                $closing_date_time = "-";
            }
			if ($value['is_deal'] == 0) {
				$closing_date_time = "-";
			}
			$SiteStage = $site_stage;
			$ClosingDate = $closing_date_time;
			
			$source_type = explode("-", $value['source_type']);
			$sourceType = '';
			foreach (getLeadSourceTypes() as $skey => $svalue) {
				if ($svalue['type'] == $source_type[0] && $svalue['id'] == $source_type[1]) {
					$sourceType = $svalue['lable'];
				}
			}
			$LeadOwner = $value['lead_owner_name'];
			$SourceType = $sourceType;

			$Source = '';
			if($source_type[0] == 'user') {
				if(in_array($source_type[1], array(101, 102, 103, 104, 105))) {
					$sourceUser = ChannelPartner::select('firm_name')->where('user_id', $value['source'])->first();
					if($sourceUser) {
						$Source = $sourceUser['firm_name'];
					} else {
						$Source = '';
					}
				} else {
					$sourceUser = User::find($value['source']);
					if($sourceUser) {
						$Source = $sourceUser['first_name'] .''. $sourceUser['last_name'];
					} else {
						$Source = '';
					}
				}
			} else if($source_type[0] == 'exhibition') {
				$sourceUser = Exhibition::find($value['source']);
				if($sourceUser) {
					$Source = $sourceUser['name'];
				} else {
					$Source = '';
				}
			} else {
				$Source = $value['source'];
			}


			$Status = "";
			if ($value['status'] != 0) {
				$Status = $LeadStatus[$value['status']]['name'];
			} else {
				$Status = "not define";
			}

			$Title = $value['title'];
			$Description = $value['message'];
			$CreatedBy = $value['user_name'];
			$CreatedAt = convertDateAndTime($value['created_at'], "date") .' '.convertDateAndTime($value['created_at'], "time");


			$lineVal = array(
				($key+1),
				$LeadId,
				$Type,
				$LeadName,
				$LeadPhoneNumber,
				$SiteStage,
				$ClosingDate,
				$LeadOwner,
				$SourceType,
				$Source,
				$Status,
				$Title,
				$Description,
				$CreatedBy,
				$CreatedAt
			);

			fputcsv($fp, $lineVal, ",");
		}

		fclose($fp);
	}
}
