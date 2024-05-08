<?php

namespace App\Http\Controllers\CRM\Lead;

use Carbon\Carbon;
use App\Models\Lead;
use App\Models\Tags;
use App\Models\User;
use Mockery\Undefined;
use App\Models\Inquiry;
use App\Models\CityList;
use App\Models\DebugLog;
use App\Models\LeadCall;
use App\Models\LeadFile;
use App\Models\LeadTask;
use App\Models\Architect;
use App\Models\TagMaster;
use App\Models\Exhibition;
use App\Models\InquiryLog;
use App\Models\LeadSource;
use App\Models\LeadUpdate;
use App\Models\SalePerson;
use App\Models\Electrician;
use App\Models\LeadClosing;
use App\Models\LeadContact;
use App\Models\LeadMeeting;
use Illuminate\Support\Arr;
use App\Models\LeadQuestion;
use App\Models\LeadTimeline;
use App\Models\Wlmst_Client;
use Illuminate\Http\Request;
use App\Models\CRMSettingBHK;
use App\Models\InquiryUpdate;
use App\Models\ChannelPartner;
use App\Models\LeadCompetitor;
use App\Models\UserDefaultView;
use App\Models\Wltrn_Quotation;
use App\Models\CRMSettingSource;
use App\Models\LeadStatusUpdate;
use App\Models\CRMSettingSiteType;
use App\Models\LeadQuestionAnswer;
use Illuminate\Database\QueryException;
// use DB;
use Illuminate\Support\Facades\DB;
use App\Models\CRMSettingSubStatus;
use App\Models\LeadQuestionOptions;
use App\Http\Controllers\Controller;
use App\Models\CRMLeadAdvanceFilter;
use App\Models\CRMSettingSourceType;
use App\Models\Wltrn_QuotItemdetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use App\Models\CRMSettingCompetitors;
use App\Models\CRMSettingStageOfSite;
use App\Models\CRMSettingWantToCover;
use App\Models\InquiryQuestionAnswer;
use App\Models\CRMSettingMeetingTitle;
use App\Models\LeadMeetingParticipant;
use Illuminate\Support\Facades\Config;
use App\Models\CRMLeadAdvanceFilterItem;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Whatsapp\WhatsappApiContoller;

date_default_timezone_set('Asia/Kolkata');
class LeadController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $tabCanAccessBy = [0, 1, 2, 6, 9, 11, 13, 101, 102, 103, 104, 105, 202, 302, 12, 13];

            if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
                return redirect()->route('dashboard');
            }

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $isSalePerson = isSalePerson();
        $isReception = isReception();
        $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
        if ($isSalePerson == 1) {
            $childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
        }

        $data = [];
        $data['title'] = 'Lead';
        $data['is_deal'] = 0;
        $data['lead_status'] = getLeadStatus();
        $data['source_types'] = getLeadSourceTypes();
        $data['is_leaddeal_module'] = 1;

        $default_view_set = UserDefaultView::query();
        $default_view_set->select('lead_advance_filter.id', 'lead_advance_filter.name');
        $default_view_set->leftJoin('lead_advance_filter', 'lead_advance_filter.id', '=', 'user_default_views.filterview_id');
        $default_view_set->where('lead_advance_filter.is_deal', 0);
        $default_view_set->where('user_default_views.user_id', Auth::user()->id);
        $default_view_set->where('user_default_views.default_type', 'user_wise');
        $default_view_set = $default_view_set->first();
        $defaultview = [];
        if ($default_view_set) {
            $defaultview['id'] = $default_view_set->id;
            $defaultview['text'] = '<div><label class="star-radio d-flex align-items-center justify-content-between" data-value="' . $default_view_set->id . '"><input type="radio"  id="setViewAsFavorite_' . $default_view_set->id . '" name="setDefaultViewAsFavorite" value="' . $default_view_set->id . '" checked><span class="star" onclick="setViewAsFavorite(' . $default_view_set->id . ');"></span><span>' . $default_view_set->name . '</span><i class="bx bx-x-circle" style="font-size: large;" onclick="clearAllFilter(1)"></i></label></div>';
            $data['default_filter_id'] = $default_view_set->id;
        } else {
            $defaultview['id'] = 0;
            $defaultview['text'] = '<div><label class="star-radio d-flex align-items-center justify-content-between" style="color: #74788d; font-weight: 400; font-size: .8125rem;"><span>Select View</span><i class="bx bxs-down-arrow"></i></label></div>';
            $data['default_filter_id'] = 0;
        }
        $data['default_filter'] = $defaultview;

        $Lead = Lead::query();
        $Lead->where('leads.is_deal', 0);
        $Lead->when(isSalePerson(), function ($query) {
            $query->whereIn('assigned_to', getChildSalePersonsIds(Auth::user()->id));
        });
        $Lead->orderBy('leads.id', 'DESC');
        $Lead = $Lead->first();
        $data['id'] = isset($request->id) ? $request->id : null;
        if ($Lead) {
            $data['current_status'] = $Lead['status'];
        } else {
            $data['current_status'] = 1;
        }

        foreach ($data['lead_status'] as $key => $value) {
            if (isArchitect() == 1 || isElectrician() == 1) {
                $status_count = Lead::where('status', $value['id']);
                if ($isSalePerson == 1) {
                    $status_count->whereIn('leads.assigned_to', $childSalePersonsIds);
                }

                if (isChannelPartner(Auth::user()->type) != 0) {
                    $status_count->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                    $status_count->where('lead_sources.source', Auth::user()->id);
                }

                if (isArchitect() == 1) {
                    $status_count->where('leads.architect', Auth::user()->id);
                }
                if ($isReception == 1) {
                    $status_count->where('leads.created_at', Auth::user()->id);
                }

                if (isElectrician() == 1) {
                    $status_count->where('leads.electrician', Auth::user()->id);
                }

                $data['lead_status'][$key]['count'] = $status_count->count();
            } else {
                if ($value['type'] == 0) {
                    $status_count = Lead::where('status', $value['id']);
                    if ($isSalePerson == 1) {
                        $status_count->whereIn('leads.assigned_to', $childSalePersonsIds);
                    }

                    if (isChannelPartner(Auth::user()->type) != 0) {
                        $status_count->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                        $status_count->where('lead_sources.source', Auth::user()->id);
                    }

                    if (isArchitect() == 1) {
                        $status_count->where('leads.architect', Auth::user()->id);
                    }
                    if ($isReception == 1) {
                        $status_count->where('leads.created_at', Auth::user()->id);
                    }

                    if (isElectrician() == 1) {
                        $status_count->where('leads.electrician', Auth::user()->id);
                    }

                    $data['lead_status'][$key]['count'] = $status_count->count();
                }
            }
        }

        $data['user_id'] = Auth::user()->id;
        if (Auth::user()->type == 202) {
            $data['source_type_id'] = 'user-202';
            $data['source_type'] = 'Architect';
        } elseif (Auth::user()->type == 302) {
            $data['source_type_id'] = 'user-302';
            $data['source_type'] = 'Electrician';
        } elseif (Auth::user()->type == 101) {
            $data['source_type_id'] = 'user-101';
            $data['source_type'] = 'ASM';
        } elseif (Auth::user()->type == 102) {
            $data['source_type_id'] = 'user-102';
            $data['source_type'] = 'ADM';
        } elseif (Auth::user()->type == 103) {
            $data['source_type_id'] = 'user-103';
            $data['source_type'] = 'APM';
        } elseif (Auth::user()->type == 104) {
            $data['source_type_id'] = 'user-104';
            $data['source_type'] = 'AD';
        } elseif (Auth::user()->type == 105) {
            $data['source_type_id'] = 'user-105';
            $data['source_type'] = 'Retailer';
        } elseif (Auth::user()->type == 12) {
            $data['source_type_id'] = 'textnotrequired-2';
            $data['source_type'] = 'Whitelion HO';
        } else {
            $data['source_type_id'] = '';
            $data['source_type'] = '';
        }
        if (in_array(Auth::user()->type, [101, 102, 103, 104, 105])) {
            $data['source_id'] = Auth::user()->id;
            $data['source_text'] = ChannelPartner::select('firm_name')
                ->where('user_id', Auth::user()->id)
                ->first()['firm_name'];
        } elseif (in_array(Auth::user()->type, [202, 203])) {
            $data['source_id'] = Auth::user()->id;
            $data['source_text'] = Auth::user()->first_name . ' ' . Auth::user()->last_name;
        } elseif (in_array(Auth::user()->type, [12])) {
            $data['source_id'] = 0;
            $data['source_text'] = Auth::user()->first_name . ' ' . Auth::user()->last_name;
        } else {
            $data['source_id'] = 0;
            $data['source_text'] = '';
        }
        $data['user_type'] = Auth::user()->type;

        return view('crm/lead/index', compact('data'));
    }

    public function indexDeal(Request $request)
    {
        $isSalePerson = isSalePerson();
        $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
        if ($isSalePerson == 1) {
            $childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
        }

        $data = [];
        $data['title'] = 'Deal';
        // $data['id'] = isset($request->id) ? $request->id : 0;
        $data['is_deal'] = 1;
        $data['lead_status'] = getLeadStatus();
        $data['source_types'] = getLeadSourceTypes();
        $data['is_leaddeal_module'] = 1;

        $default_view_set = UserDefaultView::query();
        $default_view_set->select('lead_advance_filter.id', 'lead_advance_filter.name');
        $default_view_set->leftJoin('lead_advance_filter', 'lead_advance_filter.id', '=', 'user_default_views.filterview_id');
        $default_view_set->where('lead_advance_filter.is_deal', 1);
        $default_view_set->where('user_default_views.user_id', Auth::user()->id);
        $default_view_set->where('user_default_views.default_type', 'user_wise');
        $default_view_set = $default_view_set->first();
        $defaultview = [];
        if ($default_view_set) {
            $defaultview['id'] = $default_view_set->id;
            $defaultview['text'] = '<div><label class="star-radio d-flex align-items-center justify-content-between" data-value="' . $default_view_set->id . '"><input type="radio"  id="setViewAsFavorite_' . $default_view_set->id . '" name="setDefaultViewAsFavorite" value="' . $default_view_set->id . '" checked><span class="star" onclick="setViewAsFavorite(' . $default_view_set->id . ');"></span><span>' . $default_view_set->name . '</span><i class="bx bx-x-circle" style="font-size: large;" onclick="clearAllFilter(1)"></i></label></div>';
            $data['default_filter_id'] = $default_view_set->id;
        } else {
            $defaultview['id'] = 0;
            $defaultview['text'] = '<div><label class="star-radio d-flex align-items-center justify-content-between" style="color: #74788d; font-weight: 400; font-size: .8125rem;"><span>Select View</span><i class="bx bxs-down-arrow"></i></label></div>';
            $data['default_filter_id'] = 0;
        }
        $data['default_filter'] = $defaultview;

        $Lead = Lead::query();
        $Lead->where('leads.is_deal', 1);
        $Lead->when(isSalePerson(), function ($query) {
            $query->whereIn('assigned_to', getChildSalePersonsIds(Auth::user()->id));
        });
        $Lead->orderBy('leads.id', 'DESC');
        $Lead = $Lead->first();
        $data['id'] = isset($request->id) ? $request->id : null;
        if ($Lead) {
            $data['current_status'] = $Lead['status'];
        }
        $data['current_status'] = 100;

        foreach ($data['lead_status'] as $key => $value) {
            if ($value['type'] == 1) {
                $status_count = Lead::where('status', $value['id']);
                if ($isSalePerson == 1) {
                    $status_count->whereIn('leads.assigned_to', $childSalePersonsIds);
                }

                if (isChannelPartner(Auth::user()->type) != 0) {
                    $status_count->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                    $status_count->where('lead_sources.source', Auth::user()->id);
                }

                if (isArchitect() == 1) {
                    $status_count->where('leads.architect', Auth::user()->id);
                }

                if (isElectrician() == 1) {
                    $status_count->where('leads.electrician', Auth::user()->id);
                }

                $data['lead_status'][$key]['count'] = $status_count->count();
            }
        }

        $data['source_type_id'] = 0;
        $data['source_type'] = '';

        $data['source_id'] = 0;
        $data['source_text'] = '';

        $data['user_type'] = 0;

        return view('crm/lead/index', compact('data'));
    }

    public function table(Request $request)
    {
        $data = [];
        $data['is_deal'] = 0;
        $data['title'] = 'Lead';
        $data['source_types'] = getLeadSourceTypes();

        return view('crm/lead/table', compact('data'));
    }

    public function tableDeal(Request $request)
    {
        $data = [];
        $data['is_deal'] = 1;
        $data['title'] = 'Deal';
        $data['source_types'] = getLeadSourceTypes();
        return view('crm/lead/table', compact('data'));
    }

    public function tableAjax(Request $request)
    {
        $isSalePerson = isSalePerson();
        $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
        $source_type = getInquirySourceTypes();
        if ($isSalePerson == 1) {
            $parentSalesUsers = getParentSalePersonsIds(Auth::user()->id);
            $childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
        }

        $searchColumns = [
            0 => 'leads.id',
            1 => 'leads.first_name',
            2 => 'leads.last_name',
            3 => 'leads.email',
            4 => 'leads.phone_number',
            5 => 'leads.inquiry_id',
        ];

        $sortingColumns = [
            0 => 'leads.id',
            1 => 'leads.id',
            2 => 'leads.first_name',
            3 => 'leads.phone_number',
            4 => 'leads.status',
            5 => 'leads.site_stage',
            6 => 'leads.closing_date_time',
            7 => 'leads.assigned_to',
            8 => 'leads.user_id',
        ];

        $selectColumns = [
            0 => 'leads.id',
            1 => 'leads.id',
            2 => 'leads.first_name',
            3 => 'leads.phone_number',
            4 => 'leads.status',
            5 => 'leads.site_stage',
            6 => 'leads.closing_date_time',
            7 => 'leads.assigned_to',
            8 => 'leads.user_id',
            9 => 'leads.last_name',
            10 => 'lead_owner.first_name as lead_owner_first_name',
            11 => 'lead_owner.last_name  as lead_owner_last_name',
            12 => 'created_by.first_name as created_by_first_name',
            13 => 'created_by.last_name  as created_by_last_name',
            14 => 'leads.source_type',
            15 => 'leads.inquiry_id',
        ];

        $query = Lead::query();
        if ($request->is_deal == 0) {
            $query->where('leads.is_deal', 0);
        } elseif ($request->is_deal == 1) {
            $query->where('leads.is_deal', 1);
        }
        if ($isSalePerson == 1) {
            $query->whereIn('leads.assigned_to', $childSalePersonsIds);
        }
        $recordsTotal = $query->count();
        $recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.

        $query = Lead::query();
        $query->leftJoin('users as lead_owner', 'lead_owner.id', '=', 'leads.assigned_to');
        $query->leftJoin('users as created_by', 'created_by.id', '=', 'leads.user_id');
        $query->select($selectColumns);
        if ($request->is_deal == 0) {
            $query->where('leads.is_deal', 0);
        } elseif ($request->is_deal == 1) {
            $query->where('leads.is_deal', 1);
        }
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

        $recordsFiltered = $query->count();

        $query = Lead::query();
        $query->leftJoin('users as lead_owner', 'lead_owner.id', '=', 'leads.assigned_to');
        $query->leftJoin('users as created_by', 'created_by.id', '=', 'leads.user_id');
        if ($request->is_deal == 0) {
            $query->where('leads.is_deal', 0);
        } elseif ($request->is_deal == 1) {
            $query->where('leads.is_deal', 1);
        }
        if ($isSalePerson == 1) {
            $query->whereIn('leads.assigned_to', $childSalePersonsIds);
        }
        $query->select($selectColumns);
        $query->limit($request->length);
        $query->offset($request->start);
        $query->orderBy($sortingColumns[$request['order'][0]['column']], $request['order'][0]['dir']);

        $isFilterApply = 0;

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

        $data = $query->get();
        $data = json_decode(json_encode($data), true);

        $viewData = [];
        $LeadStatus = getLeadStatus();
        foreach ($data as $key => $value) {
            $LeadHasTask = LeadTask::where('lead_id', $value['id'])->first();
            $task = '';
            if ($LeadHasTask) {
                $task = '<ul class="list-inline font-size-20 contact-links mb-0">';
                $task .= '<li class="list-inline-item px-2">';
                $task .= '<a href="javascript: void(0);" title="Edit"><i class="bx bxs-calendar-check"></i></a>';
                $task .= '</li>';
                $task .= '</ul>';
            }

            $phone_email = '';
            $phone_email = '<ul class="list-inline font-size-20 contact-links mb-0">';
            $phone_email .= '<li class="list-inline-item px-2">';
            $phone_email .= '<a href="javascript: void(0);" title="Edit"><i class="bx bxs-phone"></i></a>';
            $phone_email .= '</li>';

            $phone_email .= '<li class="list-inline-item px-2">';
            $phone_email .= '<a href="javascript: void(0);" title="Edit"><i class="bx bx-mail-send"></i></a>';
            $phone_email .= '</li>';
            $phone_email .= '</ul>';

            $CRMSettingStageOfSite = CRMSettingStageOfSite::find($value['site_stage']);
            if ($CRMSettingStageOfSite) {
                $site_stage = $CRMSettingStageOfSite->name;
            }

            $closing_date_time = $value['closing_date_time'];
            if ($closing_date_time != '' || $closing_date_time != null) {
                $closing_date_time = date('Y-m-d', strtotime($closing_date_time));
            } else {
                $closing_date_time = '-';
            }

            if ($request->is_deal == 0) {
                $routeLead = route('crm.lead') . '?id=' . $value['id'];
            } else {
                $routeLead = route('crm.deal') . '?id=' . $value['id'];
            }

            $viewData[$key] = [];
            $viewData[$key]['task'] = $task;
            if ($value['inquiry_id'] != 0) {
                $inquiry_id = ' - ' . $value['inquiry_id'];
            } else {
                $inquiry_id = '';
            }

            if ($request->is_deal == 0) {
                $prifix = 'L';
            } elseif ($request->is_deal == 1) {
                $prifix = 'D';
            }

            $viewData[$key]['id'] = "<a href='" . $routeLead . "' > " . '#' . $prifix . $value['id'] . $inquiry_id . '</a>';
            $viewData[$key]['name'] = $value['first_name'] . ' ' . $value['last_name'];
            $viewData[$key]['phone_email'] = $phone_email;

            $source_type_explode = explode('-', $value['source_type']);
            $source_type_lable = '';
            foreach ($source_type as $source_key => $source_value) {
                if ($source_value['type'] == $source_type_explode[0] && $source_value['id'] == $source_type_explode[1]) {
                    $source_type_lable = $source_value['lable'];
                }
            }
            $viewData[$key]['source'] = $source_type_lable;

            if ($value['status'] != 0) {
                $viewData[$key]['status'] = $LeadStatus[$value['status']]['name'];
            } else {
                $viewData[$key]['status'] = 'not define';
            }
            $viewData[$key]['site_stage'] = $site_stage;
            $viewData[$key]['closing_date'] = $closing_date_time;
            $viewData[$key]['lead_owner'] = $value['lead_owner_first_name'] . ' ' . $value['lead_owner_last_name'];
            $viewData[$key]['created_by'] = $value['created_by_first_name'] . ' ' . $value['created_by_last_name'];

            $uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';
            $uiAction .= '<li class="list-inline-item px-2">';
            $uiAction .= '<a onclick="editView(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Edit"><i class="bx bx-edit-alt"></i></a>';
            $uiAction .= '</li>';
            $uiAction .= '</ul>';
            $viewData[$key]['action'] = $uiAction;
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
    public function save(Request $request)
    {
        $isArchitect = isArchitect();
        $isReception = isReception();
        $isCreUser = isCreUser();
        $isElectrician = isElectrician();
        $sourceTypes = getInquirySourceTypes();
        $isChannelPartner = isChannelPartner(Auth::user()->type);
        $isThirdPartyUser = isThirdPartyUser();
        $isSalePerson = isSalePerson();
        $isTaleSalesUser = isTaleSalesUser();
        $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
        $assigned_to = 1;

        // return $isElectrician .'===='. $isArchitect;

        $rules = [];
        $rules['lead_id'] = 'required';
        $rules['no_of_source'] = 'required';
        $rules['lead_first_name'] = [
            'required', function ($attribute, $value, $fail) {
                if (preg_match('/^(Mr|Miss|.)$/i', $value)) {
                    $fail('The ' . $attribute . ' field cannot contain "Mr", "Miss", or "Ji".');
                }
            },
        ];
        $rules['lead_last_name'] = [
            'required', function ($attribute, $value, $fail) {
                if (preg_match('/^(Mr|Miss|Ji|.)$/i', $value)) {
                    $fail('The ' . $attribute . ' field cannot contain "Mr", "Miss", or "Ji".');
                }
            },
        ];
        $rules['lead_phone_number'] = 'required|digits:10|regex:/^[1-9][0-9]*$/';

        $rules['lead_assign_to'] = 'required';

        if ($isElectrician != 1 || !$isArchitect != 1) {
            $rules['lead_house_no'] = [
                'required', function ($attribute, $value, $fail) {
                    if (preg_match('/^(.)$/i', $value)) {
                        $fail('The ' . $attribute . ' field cannot contain "."');
                    }
                },
            ];
            $rules['lead_addressline1'] = [
                'required', function ($attribute, $value, $fail) {
                    if (preg_match('/^(.)$/i', $value)) {
                        $fail('The ' . $attribute . ' field cannot contain "."');
                    }
                },
            ];
            $rules['lead_area'] = [
                'required', function ($attribute, $value, $fail) {
                    if (preg_match('/^(.)$/i', $value)) {
                        $fail('The ' . $attribute . ' field cannot contain "."');
                    }
                },
            ];
            $rules['lead_city_id'] = 'required';
            // $rules['lead_meeting_addressline1'] = 'required';
            // $rules['lead_meeting_area'] = 'required';
            // $rules['lead_meeting_city_id'] = 'required';
            $rules['lead_site_stage'] = 'required';
            $rules['lead_site_type'] = 'required';
            $rules['lead_bhk'] = 'required';
            $rules['lead_want_to_cover'] = 'required';
            $rules['lead_source_type'] = 'required';
            if (isset($request->lead_email)) {
                $rules['lead_email'] = 'email:rfc,dns';
            }
            if (isset($request->lead_site_type)) {
                $objSiteType = CRMSettingSiteType::find($request->lead_site_type);
                if (isset($objSiteType)) {
                    if ($objSiteType->is_bhk == 0) {
                        $rules['lead_sq_foot'] = 'required|gt:0';
                        $customMessage['lead_bhk.required'] = 'Please Enter SQ FT';
                    }
                }
            }
            if (isset($request->all()['lead_source_type'])) {
                $source_type = $request->all()['lead_source_type'];

                if (explode('-', $source_type)[0] == 'textrequired') {
                    $rules['lead_source_text'] = 'required';
                } elseif (explode('-', $source_type)[0] == 'textnotrequired') {
                    $rules['lead_source_text'] = 'required';
                } elseif (explode('-', $source_type)[0] == 'fix') {
                    $rules['lead_source_text'] = 'required';
                } else {
                    $rules['lead_source'] = 'required';
                }
            }
            if ($request->no_of_source > 1) {
                for ($i = 1; $i <= $request->no_of_source; $i++) {
                    if (isset($request->all()['lead_source_type_' . $i])) {
                        $multi_source_type = $request->all()['lead_source_type_' . $i];

                        if (explode('-', $multi_source_type)[0] == 'textrequired') {
                            $rules['lead_source_text_' . $i] = 'required';
                        } elseif (explode('-', $multi_source_type)[0] == 'textnotrequired') {
                            $rules['lead_source_text_' . $i] = 'required';
                        } elseif (explode('-', $multi_source_type)[0] == 'fix') {
                            $rules['lead_source_text_' . $i] = 'required';
                        } else {
                            $rules['lead_source_' . $i] = 'required';
                        }
                    }
                }
            }
        }



        $customMessage = [];
        $customMessage['lead_id.required'] = 'Invalid parameters';
        $customMessage['lead_first_name.required'] = 'Please enter first name';
        $customMessage['user_first_name.required'] = 'Please enter first name';
        $customMessage['lead_assign_to.required'] = 'Please Select Lead Owner';
        if (!$isElectrician == 1 || !$isArchitect == 1) {
            $customMessage['lead_email.email'] = 'Please enter valid email address';
            $customMessage['lead_sq_foot.required'] = 'Please enter SQ FT';
        }

        $lead_email = isset($request->lead_email) ? $request->lead_email : '';
        $lead_house_no = isset($request->lead_house_no) ? $request->lead_house_no : '';
        $lead_addressline1 = isset($request->lead_addressline1) ? $request->lead_addressline1 : '';
        $lead_addressline2 = isset($request->lead_addressline2) ? $request->lead_addressline2 : '';
        $lead_meeting_addressline2 = isset($request->lead_meeting_addressline2) ? $request->lead_meeting_addressline2 : '';
        $lead_area = isset($request->lead_area) ? $request->lead_area : '';
        $lead_city_id = isset($request->lead_city_id) ? $request->lead_city_id : 0;
        $lead_site_stage = isset($request->lead_site_stage) && is_array($request->lead_site_stage) ? $request->lead_site_stage : 0;
        $lead_site_type = isset($request->lead_site_type) && is_array($request->lead_site_type) ? $request->lead_site_type : 0;
        $lead_bhk = isset($request->lead_bhk) && is_array($request->lead_bhk) ? $request->lead_bhk : 0;

        $lead_pincode = isset($request->lead_pincode) ? $request->lead_pincode : '';
        $lead_meeting_pincode = isset($request->lead_meeting_pincode) ? $request->lead_meeting_pincode : '';
        $lead_competitor = isset($request->lead_competitor) ? $request->lead_competitor : [];
        // $lead_closing_date_and_time = date('Y-m-d H:i:s', strtotime($request->lead_closing_date_time));
        $lead_closing_date_and_time = date('Y-m-d H:i:s', strtotime($request->lead_closing_date_time . date('H:i:s')));
        $lead_architect = isset($request->lead_architect) ? $request->lead_architect : 0;
        $lead_electrician = isset($request->lead_electrician) ? $request->lead_electrician : 0;
        $lead_channel_partner = isset($request->lead_channel_partner) ? $request->lead_channel_partner : 0;
        $lead_want_to_cover = isset($request->lead_want_to_cover) && is_array($request->lead_want_to_cover) ? $request->lead_want_to_cover : [];
        $lead_source_type = isset($request->lead_source_type) && is_array($request->lead_source_type) ? $request->lead_source_type : 0;
        $lead_source_text = isset($request->lead_source_text) && is_array($request->lead_source_text) ? $request->lead_source_text : '';
        $lead_source = isset($request->lead_source) && is_array($request->lead_source) ? $request->lead_source : 0;



        if ($isSalePerson == 1) {
            $assigned_to = $request->lead_assign_to;
        } elseif ($isAdminOrCompanyAdmin == 1 || $isTaleSalesUser == 1 || $isChannelPartner != 0 || $isElectrician == 1 || $isArchitect == 1 || $isReception == 1 || $isCreUser == 1) {
            $assigned_to = $request->lead_assign_to;
        } else {
            $response = errorRes('Invalid access', 401);
            return response()
                ->json($response, $response['status_code'])
                ->header('Content-Type', 'application/json');
        }

        $change_field = '';

        $temp_comptitor = [];

        $lead_budget = isset($request->lead_budget) ? $request->lead_budget : 0;
        $lead_sq_foot = isset($request->lead_sq_foot) ? $request->lead_sq_foot : 0;


        $validator = Validator::make($request->all(), $rules, $customMessage);

        if ($validator->fails()) {
            $response = errorRes($validator->errors()->first());
            $response['data'] = $validator->errors()->first();
        } else {
            $main_source_type = $lead_source_type;
            if (explode('-', $main_source_type)[0] == 'textrequired') {
                $main_source = $lead_source_text;
            } elseif (explode('-', $main_source_type)[0] == 'textnotrequired') {
                $main_source = $lead_source_text;
            } elseif (explode('-', $main_source_type)[0] == 'fix') {
                $main_source = $lead_source_text;
            } else {
                $main_source = $lead_source;
            }

            if ($request->lead_id != 0) {
                $Lead = Lead::find($request->lead_id);
                $Lead->updated_by = Auth::user()->id;
                $Lead->updateip = $request->ip();
                $Lead->update_source = 'WEB';

                if ($Lead->first_name != $request->lead_first_name) {
                    $new_value = $request->lead_first_name;
                    $old_value = $Lead->first_name;
                    $change_field .= ' | Client Name First Name Change : ' . $old_value . ' To ' . $new_value;
                }

                if ($Lead->last_name != $request->lead_last_name) {
                    $new_value = $request->lead_last_name;
                    $old_value = $Lead->last_name;
                    $change_field .= ' | Client Name Last Name Change : ' . $old_value . ' To ' . $new_value;
                }

                if ($Lead->email != $lead_email) {
                    $new_value = $lead_email;
                    $old_value = $Lead->email;
                    $change_field .= ' | Client Email Change : ' . $old_value . ' To ' . $new_value;
                }

                if ($Lead->phone_number != $request->lead_phone_number) {
                    $new_value = $request->lead_phone_number;
                    $old_value = $Lead->phone_number;
                    $change_field .= ' | Client Mobile NO. Change : ' . $old_value . ' To ' . $new_value;
                }

                if ($Lead->assigned_to != $assigned_to) {
                    $old_value = $assigned_to;
                    $new_value = $Lead->assigned_to;

                    $old_text_value = '';
                    $old_val_text = User::select(DB::raw("CONCAT(users.first_name,' ',users.last_name) AS name"))->where('id', $old_value)->first();
                    if ($old_val_text) {
                        $old_text_value = $old_val_text->name;
                    }

                    $new_text_value = '';
                    $new_val_text = User::select(DB::raw("CONCAT(users.first_name,' ',users.last_name) AS name"))->where('id', $new_value)->first();
                    if ($new_val_text) {
                        $new_text_value = $new_val_text->name;
                    }

                    $change_field .= ' | Lead Owner Change : ' . $old_text_value . '(' . $old_value . ') To ' . $new_text_value . '(' . $new_value . ')';
                }

                if ($Lead->architect != $lead_architect) {
                    $old_value = $lead_architect;
                    $new_value = $Lead->architect;

                    $old_text_value = '';
                    $old_val_text = User::select(DB::raw("CONCAT(users.first_name,' ',users.last_name) AS name"))->where('id', $old_value)->first();
                    if ($old_val_text) {
                        $old_text_value = $old_val_text->name;
                    }

                    $new_text_value = '';
                    $new_val_text = User::select(DB::raw("CONCAT(users.first_name,' ',users.last_name) AS name"))->where('id', $new_value)->first();
                    if ($new_val_text) {
                        $new_text_value = $new_val_text->name;
                    }

                    $change_field .= ' | Lead Architect Change : ' . $old_text_value . '(' . $old_value . ') To ' . $new_text_value . '(' . $new_value . ')';
                }

                if ($Lead->electrician != $lead_electrician) {
                    $old_value = $lead_electrician;
                    $new_value = $Lead->electrician;

                    $old_text_value = '';
                    $old_val_text = User::select(DB::raw("CONCAT(users.first_name,' ',users.last_name) AS name"))->where('id', $old_value)->first();
                    if ($old_val_text) {
                        $old_text_value = $old_val_text->name;
                    }

                    $new_text_value = '';
                    $new_val_text = User::select(DB::raw("CONCAT(users.first_name,' ',users.last_name) AS name"))->where('id', $new_value)->first();
                    if ($new_val_text) {
                        $new_text_value = $new_val_text->name;
                    }

                    $change_field .= ' | Lead Electrician Change : ' . $old_text_value . '(' . $old_value . ') To ' . $new_text_value . '(' . $new_value . ')';
                }

                if ($Lead->channel_partner != $lead_channel_partner) {
                    $old_value = $lead_channel_partner;
                    $new_value = $Lead->channel_partner;

                    $old_text_value = '';
                    $old_val_text = User::select(DB::raw("CONCAT(users.first_name,' ',users.last_name) AS name"))->where('id', $old_value)->first();
                    if ($old_val_text) {
                        $old_text_value = $old_val_text->name;
                    }

                    $new_text_value = '';
                    $new_val_text = User::select(DB::raw("CONCAT(users.first_name,' ',users.last_name) AS name"))->where('id', $new_value)->first();
                    if ($new_val_text) {
                        $new_text_value = $new_val_text->name;
                    }

                    $change_field .= ' | Lead Channel Partner Change : ' . $old_text_value . '(' . $old_value . ') To ' . $new_text_value . '(' . $new_value . ')';
                }

                if ($Lead->house_no != $request->lead_house_no || $Lead->addressline1 != $request->lead_addressline1 || $Lead->area != $request->lead_area || $Lead->pincode != $lead_pincode || $Lead->city_id != $request->lead_city_id) {
                    $NewCityList = CityList::select('name')->find($request->lead_city_id);
                    if ($NewCityList) {
                        $New_City_name = $NewCityList->name;
                    } else {
                        $New_City_name = '';
                    }
                    $new_value = $request->lead_house_no . ', ' . $request->lead_addressline1 . ', ' . $request->lead_area . ', ' . $lead_pincode . ', ' . $New_City_name;
                    $OldCityList = CityList::select('name')->find($Lead->city_id);
                    if ($OldCityList) {
                        $Old_City_name = $OldCityList->name;
                    } else {
                        $Old_City_name = '';
                    }
                    $old_value = $Lead->house_no . ', ' . $Lead->addressline1 . ', ' . $Lead->area . ', ' . $Lead->pincode . ', ' . $Old_City_name;
                    $change_field .= ' | Client Address Change : ' . $old_value . ' To ' . $new_value;
                }

                if ($Lead->source_type != $main_source_type || $Lead->source != $main_source) {
                    // FIEND NEW SOURCE TYPE AND NAME
                    $new_source_type = $main_source_type;
                    $new_source_value = $main_source;
                    $new_final_source_type = '';
                    $source_type = explode('-', $new_source_type);
                    foreach (getLeadSourceTypes() as $source_type_key => $source_type_value) {
                        if ($source_type[1] == 201) {
                            $source_type_id = 202;
                        } elseif ($source_type[1] == 301) {
                            $source_type_id = 302;
                        } else {
                            $source_type_id = $source_type[1];
                        }

                        if ($source_type_value['type'] == $source_type[0] && $source_type_value['id'] == $source_type_id) {
                            $new_final_source_type = $source_type_value['lable'];
                            break;
                        }
                    }

                    if ($source_type[0] == 'user') {
                        if (isset(getChannelPartners()[$source_type[1]]['short_name'])) {
                            $new_source['val_id'] = $new_source_value;
                            $val_text = ChannelPartner::select('firm_name')->where('user_id', $new_source_value)->first();
                            if ($val_text) {
                                $new_final_source_value = $val_text->firm_name;
                            } else {
                                $new_final_source_value = ' ';
                            }
                        } else {
                            $new_source['val_id'] = $new_source_value;
                            $val_text = User::select(DB::raw("CONCAT(users.first_name,' ',users.last_name) AS name"))->where('id', $new_source_value)->first();
                            if ($val_text) {
                                $new_final_source_value = $val_text->name;
                            } else {
                                $new_final_source_value = ' ';
                            }
                        }
                    } elseif ($source_type[0] == 'master') {
                        // $new_final_source_value = CRMSettingSource::select('name')->where('source_type_id', $new_source_value)->first()->name;
                        $val_text = CRMSettingSource::select('name')->where('source_type_id', $new_source_value)->first();
                        if ($val_text) {
                            $new_final_source_value = $val_text->name;
                        } else {
                            $new_final_source_value = ' ';
                        }
                    } else {
                        $new_final_source_value = $new_source_value;
                    }
                    // FIEND OLD SOURCE TYPE AND NAME
                    $old_source_type = $Lead->source_type;
                    $old_source_value = $Lead->source;
                    $old_final_source_type = '';
                    $source_type = explode('-', $old_source_type);
                    foreach (getLeadSourceTypes() as $source_type_key => $source_type_value) {
                        if ($source_type[1] == 201) {
                            $source_type_id = 202;
                        } elseif ($source_type[1] == 301) {
                            $source_type_id = 302;
                        } else {
                            $source_type_id = $source_type[1];
                        }

                        if ($source_type_value['type'] == $source_type[0] && $source_type_value['id'] == $source_type_id) {
                            $old_final_source_type = $source_type_value['lable'];
                            break;
                        }
                    }

                    if ($source_type[0] == 'user') {
                        if (isset(getChannelPartners()[$source_type[1]]['short_name'])) {
                            $val_text = ChannelPartner::select('firm_name')->where('user_id', $old_source_value)->first();
                            if ($val_text) {
                                $old_final_source_value = $val_text->firm_name;
                            } else {
                                $old_final_source_value = ' ';
                            }
                        } else {
                            $val_text = User::select(DB::raw("CONCAT(users.first_name,' ',users.last_name) AS name"))->where('id', $old_source_value)->first();
                            if ($val_text) {
                                $old_final_source_value = $val_text->name;
                            } else {
                                $old_final_source_value = ' ';
                            }
                        }
                    } elseif ($source_type[0] == 'master') {
                        $val_text = CRMSettingSource::select('name')->where('source_type_id', $old_source_value)->first();
                        if ($val_text) {
                            $old_final_source_value = $val_text->name;
                        } else {
                            $old_final_source_value = ' ';
                        }
                    } else {
                        $old_final_source_value = $old_source_value;
                    }

                    $change_field .= ' | Main Source Change : ' . $new_final_source_value . '(' . $new_final_source_type . ') TO ' . $old_final_source_value . '(' . $old_final_source_type . ')';
                }

                if ($Lead->site_stage != $request->lead_site_stage) {
                    $new_value = $request->lead_site_stage;
                    $old_value = $Lead->site_stage;

                    $New_Site_Stage = '';
                    if ($new_value != 0 || $new_value != '') {
                        $CRMSettingStageOfSite = CRMSettingStageOfSite::select('id', 'name as text');
                        $CRMSettingStageOfSite->where('crm_setting_stage_of_site.id', $new_value);
                        $CRMSettingStageOfSite = $CRMSettingStageOfSite->first();
                        if ($CRMSettingStageOfSite) {
                            $New_Site_Stage = $CRMSettingStageOfSite->text;
                        }
                    }
                    $Old_Site_Stage = '';
                    if ($old_value != 0 || $old_value != '') {
                        $CRMSettingStageOfSite = CRMSettingStageOfSite::select('id', 'name as text');
                        $CRMSettingStageOfSite->where('crm_setting_stage_of_site.id', $old_value);
                        $CRMSettingStageOfSite = $CRMSettingStageOfSite->first();
                        if ($CRMSettingStageOfSite) {
                            $Old_Site_Stage = $CRMSettingStageOfSite->text;
                        }
                    }

                    $change_field .= ' | Site Stage Change : ' . $Old_Site_Stage . ' To ' . $New_Site_Stage;
                }

                if ($Lead->site_type != $request->lead_site_type) {
                    $new_value = $request->lead_site_type;
                    $old_value = $Lead->site_type;

                    $New_Site_Type = '';
                    if ($new_value != 0 || $new_value != '') {
                        $CRMSettingSiteType = CRMSettingSiteType::select('id', 'name as text');
                        $CRMSettingSiteType->where('crm_setting_site_type.id', $new_value);
                        $CRMSettingSiteType = $CRMSettingSiteType->first();
                        if ($CRMSettingSiteType) {
                            $New_Site_Type = $CRMSettingSiteType->text;
                        }
                    }

                    $Old_Site_Type = '';
                    if ($old_value != 0 || $old_value != '') {
                        $CRMSettingSiteType = CRMSettingSiteType::select('id', 'name as text');
                        $CRMSettingSiteType->where('crm_setting_site_type.id', $old_value);
                        $CRMSettingSiteType = $CRMSettingSiteType->first();
                        if ($CRMSettingSiteType) {
                            $Old_Site_Type = $CRMSettingSiteType->text;
                        }
                    }

                    $change_field .= ' | Site Type Change : ' . $Old_Site_Type . ' To ' . $New_Site_Type;
                }

                if ($Lead->bhk != $request->lead_bhk) {
                    $new_value = $request->lead_bhk;
                    $old_value = $Lead->bhk;

                    $New_Bhk = '';
                    if ($new_value != 0 || $new_value != '') {
                        $CRMSettingBHK = CRMSettingBHK::select('id', 'name as text');
                        $CRMSettingBHK->where('crm_setting_bhk.id', $new_value);
                        $CRMSettingBHK = $CRMSettingBHK->first();
                        if ($CRMSettingBHK) {
                            $New_Bhk = $CRMSettingBHK->text;
                        }
                    }

                    $Old_Bhk = '';
                    if ($old_value != 0 || $old_value != '') {
                        $CRMSettingBHK = CRMSettingBHK::select('id', 'name as text');
                        $CRMSettingBHK->where('crm_setting_bhk.id', $old_value);
                        $CRMSettingBHK = $CRMSettingBHK->first();
                        if ($CRMSettingBHK) {
                            $Old_Bhk = $CRMSettingBHK->text;
                        }
                    }

                    $change_field .= ' | Site Type Change : ' . $Old_Bhk . ' To ' . $New_Bhk;
                }

                if ($Lead->sq_foot != $lead_sq_foot) {
                    $new_value = $lead_sq_foot;
                    $old_value = $Lead->sq_foot;
                    $change_field .= ' | SQ FT Change : ' . $old_value . ' To ' . $new_value;
                }

                if ($Lead->budget != $lead_budget) {
                    $new_value = $lead_budget;
                    $old_value = $Lead->budget;
                    $change_field .= ' | Budget Change : ' . $old_value . ' To ' . $new_value;
                }

                if ($Lead->competitor != $lead_competitor) {
                    $isDifferent = !empty(array_diff(explode(',', $Lead->competitor), $lead_competitor)) || !empty(array_diff($lead_competitor, explode(',', $Lead->competitor)));
                    if ($isDifferent) {
                        $new_value = '';
                        $old_value = '';

                        foreach (explode(',', $Lead->competitor) as $oldKey => $oldValue) {
                            $Competitor = CRMSettingCompetitors::select('name')->where('id', $oldValue)->first();
                            if ($Competitor) {
                                $old_value .= $Competitor->name . ', ';
                            }
                        }
                        foreach ($lead_competitor as $newKey => $newValue) {
                            $Competitor = CRMSettingCompetitors::select('name')->where('id', $newValue)->first();
                            if ($Competitor) {
                                $new_value .= $Competitor->name . ', ';
                            } else {
                                $new_value .= $newValue . ', ';
                            }
                        }
                        $change_field .= ' | Competitors Change : [' . $old_value . '] To [' . $new_value . ']';
                    }
                }
            } else {
                $Lead = Lead::where('phone_number', $request->lead_phone_number)->first();
                if ($Lead) {
                    $response = errorRes("Phone number is already register in #$Lead->id ($Lead->first_name  $Lead->last_name) this lead , Please use another phone number");
                    return response()->json($response)->header('Content-Type', 'application/json');
                } else {
                    $wlmst_client = new Wlmst_Client();
                    $wlmst_client->name = $request->lead_first_name;
                    $wlmst_client->email = $lead_email;
                    $wlmst_client->mobile = $request->lead_phone_number;
                    $wlmst_client->address = $request->lead_house_no . ', ' . $request->lead_addressline1 . ', ' . $lead_addressline2 . ', ' . $request->lead_area;
                    $wlmst_client->isactive = 1;
                    $wlmst_client->remark = 0;
                    $wlmst_client->save();

                    $Lead = new Lead();
                    $Lead->customer_id = $wlmst_client->id;
                    $Lead->status = 1;
                    $Lead->sub_status = 0;
                    $Lead->created_by = Auth::user()->id;
                    $Lead->updated_by = Auth::user()->id;
                    $Lead->entryip = $request->ip();
                    $Lead->entry_source = 'WEB';
                }
            }

            foreach ($lead_competitor as $key => $value) {
                $is_CRMSettingCompetitor = CRMSettingCompetitors::select('id')->where('id', $value)->orWhere('name', $value)->first();
                if ($is_CRMSettingCompetitor) {
                    array_push($temp_comptitor, $is_CRMSettingCompetitor->id);
                } else {
                    $CRMSettingCompetitor = new CRMSettingCompetitors();
                    $CRMSettingCompetitor->name = $value;
                    $CRMSettingCompetitor->status = 1;
                    $CRMSettingCompetitor->save();

                    array_push($temp_comptitor, $CRMSettingCompetitor->id);
                }
            }

            $Lead->first_name = $request->lead_first_name;
            $Lead->last_name = $request->lead_last_name;
            // $Lead->last_name = ' ';
            $Lead->email = $lead_email;
            $Lead->phone_number = $request->lead_phone_number;

            $Lead->house_no = $lead_house_no;
            $Lead->addressline1 = $lead_addressline1;
            $Lead->addressline2 = $lead_addressline2;
            $Lead->area = $lead_area;
            $Lead->pincode = $lead_pincode;
            $Lead->city_id = $lead_city_id;

            // $Lead->meeting_house_no = $request->lead_meeting_house_no;
            // $Lead->meeting_addressline1 = $request->lead_meeting_addressline1;
            // $Lead->meeting_addressline2 = $lead_meeting_addressline2;
            // $Lead->meeting_area = $request->lead_meeting_area;
            // $Lead->meeting_pincode = $lead_meeting_pincode;
            // $Lead->meeting_city_id = $request->lead_meeting_city_id;
            $Lead->meeting_house_no = '';
            $Lead->meeting_addressline1 = '';
            $Lead->meeting_addressline2 = '';
            $Lead->meeting_area = '';
            $Lead->meeting_pincode = 0;
            $Lead->meeting_city_id = 0;
            $Lead->source_type = $main_source_type;
            $Lead->source = $main_source;
            $Lead->site_stage = $lead_site_stage;
            $Lead->site_type = $lead_site_type;
            $Lead->bhk = $lead_bhk;
            $Lead->sq_foot = $lead_sq_foot;
            $Lead->budget = $lead_budget;
            $Lead->competitor = implode(',', $temp_comptitor);

            // LOG NOT CREATED THIS FIELD
            $Leadwant_to_cover = implode(',', $lead_want_to_cover);
            $Lead->want_to_cover = $Leadwant_to_cover;
            $Lead->assigned_to = $assigned_to;
            $Lead->architect = $lead_architect;
            $Lead->electrician = $lead_electrician;
            $Lead->channel_partner = $lead_channel_partner;

            $Lead->user_id = Auth::user()->id;
            if ($Lead->is_deal == 1) {
                $Lead->is_deal = 1;
            } else {
                $Lead->is_deal = 0;
            }
            $Lead->save();

            $response_error = [];
            if ($Lead) {
                if ($request->lead_id == 0) {
                    $whatsapp_controller = new WhatsappApiContoller();
                    $perameater_request = new Request();
                    $perameater_request['q_whatsapp_massage_mobileno'] = $Lead->phone_number;
                    $perameater_request['q_whatsapp_massage_template'] = 'lead_status1_inquiry';
                    $perameater_request['q_whatsapp_massage_attechment'] = '';
                    $perameater_request['q_broadcast_name'] = $Lead->first_name . ' ' . $Lead->last_name;
                    $perameater_request['q_whatsapp_massage_parameters'] = array();
                    $whatsapp_controller->sendTemplateMessage($perameater_request);
                    // NEW LEAD SAVE TIME
                    try {
                        $timeline = [];
                        $timeline['lead_id'] = $Lead->id;
                        $timeline['type'] = 'lead-generate';
                        $timeline['reffrance_id'] = $Lead->id;
                        $timeline['description'] = 'Lead created by ' . Auth::user()->first_name . ' ' . Auth::user()->last_name;
                        $timeline['source'] = 'WEB';
                        saveLeadTimeline($timeline);

                        $LeadContact = new LeadContact();
                        $LeadContact->lead_id = $Lead->id;
                        $LeadContact->contact_tag_id = 1;
                        $LeadContact->first_name = $Lead->first_name;
                        $LeadContact->last_name = $Lead->last_name;
                        $LeadContact->phone_number = $Lead->phone_number;
                        $LeadContact->alernate_phone_number = 0;
                        $LeadContact->email = $Lead->email;
                        $LeadContact->type = 0;
                        $LeadContact->type_detail = 0;
                        $LeadContact->save();
                        $Lead_contact_id = 0;
                        if ($LeadContact) {
                            $Lead_contact_id = $LeadContact->id;
                        }

                        // ADD ARCHITECT CONTACT START
                        try {
                            if ($Lead->architect != 0) {
                                $Architect = User::find($Lead->architect);

                                if ($Architect) {
                                    $LeadContact_arc = new LeadContact();
                                    $LeadContact_arc->lead_id = $Lead->id;
                                    $LeadContact_arc->contact_tag_id = 0;
                                    $LeadContact_arc->first_name = $Architect->first_name;
                                    $LeadContact_arc->last_name = $Architect->last_name;
                                    $LeadContact_arc->phone_number = $Architect->phone_number;
                                    $LeadContact_arc->alernate_phone_number = 0;
                                    $LeadContact_arc->email = $Architect->email;
                                    $LeadContact_arc->type = $Architect->type;
                                    $LeadContact_arc->type_detail = 'user-' . $Architect->type . '-' . $Architect->id;
                                    $LeadContact_arc->save();
                                }
                            }
                        } catch (\Exception $e) {
                            $response_error['error_arc'] = errorRes($e->getMessage(), 400);
                        }
                        // ADD ARCHITECT CONTACT END

                        // ADD ELECTRICIAN CONTACT START
                        try {
                            if ($Lead->electrician != 0) {
                                $Electrician = User::find($Lead->electrician);

                                if ($Electrician) {
                                    $LeadContact_ele = new LeadContact();
                                    $LeadContact_ele->lead_id = $Lead->id;
                                    $LeadContact_ele->contact_tag_id = 0;
                                    $LeadContact_ele->first_name = $Electrician->first_name;
                                    $LeadContact_ele->last_name = $Electrician->last_name;
                                    $LeadContact_ele->phone_number = $Electrician->phone_number;
                                    $LeadContact_ele->alernate_phone_number = 0;
                                    $LeadContact_ele->email = $Electrician->email;
                                    $LeadContact_ele->type = $Electrician->type;
                                    $LeadContact_ele->type_detail = 'user-' . $Electrician->type . '-' . $Electrician->id;
                                    $LeadContact_ele->save();
                                }
                            }
                        } catch (\Exception $e) {
                            $response_error['error_ele'] = errorRes($e->getMessage(), 400);
                        }
                        // ADD ELECTRICIAN CONTACT END

                        // ADD MULTI SOURCE SAVE TO CONTACT START
                        try {
                            if ($request->no_of_source > 0) {
                                for ($i = 1; $i <= $request->no_of_source; $i++) {
                                    if ($request->all()['lead_source_type_' . $i]) {

                                        $multi_source_type = $request->all()['lead_source_type_' . $i];

                                        if (explode('-', $multi_source_type)[0] == 'textrequired') {
                                            $multi_source = $request->all()['lead_source_text_' . $i];
                                        } elseif (explode('-', $multi_source_type)[0] == 'textnotrequired') {
                                            $multi_source = $request->all()['lead_source_text_' . $i];
                                        } elseif (explode('-', $multi_source_type)[0] == 'fix') {
                                            $multi_source = $request->all()['lead_source_text_' . $i];
                                        } else {
                                            $multi_source = $request->all()['lead_source_' . $i];
                                        }

                                        if ($multi_source_type != null || $multi_source_type == 'user-201' || $multi_source_type == 'user-202' || $multi_source_type == 'user-301' || $multi_source_type == 'user-302' || $multi_source_type == 'user-101' || $multi_source_type == 'user-102' || $multi_source_type == 'user-103' || $multi_source_type == 'user-104' || $multi_source_type == 'user-105') {
                                            if ($multi_source != 0 || $multi_source != null || $multi_source != '') {
                                                if ($multi_source != $Lead->electrician && $multi_source != $Lead->architect) {
                                                    $multi_Source_1 = User::where('id', $multi_source)->first();

                                                    if ($multi_Source_1) {
                                                        if ($multi_Source_1->id != $Lead->electrician && $multi_Source_1->id != $Lead->architect) {
                                                            $LeadContact_s1 = new LeadContact();
                                                            $LeadContact_s1->lead_id = $Lead->id;
                                                            $LeadContact_s1->contact_tag_id = 0;
                                                            if (isChannelPartner($multi_Source_1->type) != 0) {
                                                                $ChannelPartner = ChannelPartner::find($multi_Source_1->reference_id);
                                                                $LeadContact_s1->first_name = $ChannelPartner->firm_name;
                                                                $LeadContact_s1->last_name = '';
                                                            } else {
                                                                $LeadContact_s1->first_name = $multi_Source_1->first_name;
                                                                $LeadContact_s1->last_name = $multi_Source_1->last_name;
                                                            }
                                                            // $LeadContact_s1->first_name = $multi_Source_1->first_name;
                                                            // $LeadContact_s1->last_name = $multi_Source_1->last_name;
                                                            $LeadContact_s1->phone_number = $multi_Source_1->phone_number;
                                                            $LeadContact_s1->alernate_phone_number = 0;
                                                            $LeadContact_s1->email = $multi_Source_1->email;
                                                            $LeadContact_s1->type = $multi_Source_1->type;
                                                            $LeadContact_s1->type_detail = 'user-' . $multi_Source_1->type . '-' . $multi_Source_1->id;
                                                            $LeadContact_s1->save();
                                                        }
                                                    }
                                                }

                                                // $multi_Source_1 = User::where('id', $multi_source)->first();

                                                // if ($multi_Source_1) {
                                                //     if ($multi_Source_1->id != $multi_source) {
                                                //         $LeadContact_s1 = new LeadContact();
                                                //         $LeadContact_s1->lead_id = $Lead->id;
                                                //         $LeadContact_s1->contact_tag_id = 0;
                                                //         $LeadContact_s1->first_name = $multi_Source_1->first_name;
                                                //         $LeadContact_s1->last_name = $multi_Source_1->last_name;
                                                //         $LeadContact_s1->phone_number = $multi_Source_1->phone_number;
                                                //         $LeadContact_s1->alernate_phone_number = 0;
                                                //         $LeadContact_s1->email = $multi_Source_1->email;
                                                //         $LeadContact_s1->type = $multi_Source_1->type;
                                                //         $LeadContact_s1->type_detail = "user-" . $multi_Source_1->type . "-" . $multi_Source_1->id;
                                                //         $LeadContact_s1->save();
                                                //     }
                                                // }
                                                $LeadSource1 = new LeadSource();
                                                $LeadSource1->lead_id = $Lead->id;
                                                $LeadSource1->source_type = $multi_source_type;
                                                $LeadSource1->source = $multi_source;
                                                $LeadSource1->save();
                                            }
                                        } else {
                                            if ($multi_source != 0 || $multi_source != null || $multi_source != '') {
                                                $LeadSource1 = new LeadSource();
                                                $LeadSource1->lead_id = $Lead->id;
                                                $LeadSource1->source_type = $multi_source_type;
                                                $LeadSource1->source = $multi_source;
                                                $LeadSource1->save();
                                            }
                                        }
                                    }
                                }
                            }
                        } catch (\Exception $e) {
                            $response_error['error_multi_source'] = errorRes($e->getMessage(), 400);
                        }
                        // ADD MULTI SOURCE SAVE TO CONTACT END

                        // ADD MAIN SOURCE TO LEAD START
                        try {
                            if ($main_source_type != null || $main_source_type == 'user-201' || $main_source_type == 'user-202' || $main_source_type == 'user-301' || $main_source_type == 'user-302' || $main_source_type == 'user-101' || $main_source_type == 'user-102' || $main_source_type == 'user-103' || $main_source_type == 'user-104' || $main_source_type == 'user-105') {
                                if ($main_source != 0 || $main_source != null || $main_source != '') {
                                    if ($main_source != $Lead->electrician && $main_source != $Lead->architect) {
                                        $Source_1 = User::where('id', $main_source)->first();

                                        if ($Source_1) {
                                            if ($Source_1->id != $Lead->electrician && $Source_1->id != $Lead->architect) {
                                                $LeadContact_s1 = new LeadContact();
                                                $LeadContact_s1->lead_id = $Lead->id;
                                                $LeadContact_s1->contact_tag_id = 0;
                                                if (isChannelPartner($Source_1->type) != 0) {
                                                    $ChannelPartner = ChannelPartner::find($Source_1->reference_id);
                                                    $LeadContact_s1->first_name = $ChannelPartner->firm_name;
                                                    $LeadContact_s1->last_name = '';
                                                } else {
                                                    $LeadContact_s1->first_name = $Source_1->first_name;
                                                    $LeadContact_s1->last_name = $Source_1->last_name;
                                                }
                                                // $LeadContact_s1->first_name = $Source_1->first_name;
                                                // $LeadContact_s1->last_name = $Source_1->last_name;
                                                $LeadContact_s1->phone_number = $Source_1->phone_number;
                                                $LeadContact_s1->alernate_phone_number = 0;
                                                $LeadContact_s1->email = $Source_1->email;
                                                $LeadContact_s1->type = $Source_1->type;
                                                $LeadContact_s1->type_detail = 'user-' . $Source_1->type . '-' . $Source_1->id;
                                                $LeadContact_s1->save();
                                            }
                                        }

                                        // $Source_1 = User::where('id', $main_source)->first();

                                        // if ($Source_1) {
                                        //     if ($Source_1->id != $main_source) {
                                        //         $LeadContact_s1 = new LeadContact();
                                        //         $LeadContact_s1->lead_id = $Lead->id;
                                        //         $LeadContact_s1->contact_tag_id = 0;
                                        //         $LeadContact_s1->first_name = $Source_1->first_name;
                                        //         $LeadContact_s1->last_name = $Source_1->last_name;
                                        //         $LeadContact_s1->phone_number = $Source_1->phone_number;
                                        //         $LeadContact_s1->alernate_phone_number = 0;
                                        //         $LeadContact_s1->email = $Source_1->email;
                                        //         $LeadContact_s1->type = $Source_1->type;
                                        //         $LeadContact_s1->type_detail = "user-" . $Source_1->type . "-" . $Source_1->id;
                                        //         $LeadContact_s1->save();
                                        //     }
                                        // }
                                    }
                                    $LeadSource1 = new LeadSource();
                                    $LeadSource1->lead_id = $Lead->id;
                                    $LeadSource1->source_type = $main_source_type;
                                    $LeadSource1->source = $main_source;
                                    $LeadSource1->is_main = 1;
                                    $LeadSource1->save();
                                }
                            } else {
                                if ($main_source != 0 || $main_source != null || $main_source != '') {
                                    $LeadSource1 = new LeadSource();
                                    $LeadSource1->lead_id = $Lead->id;
                                    $LeadSource1->source_type = $main_source_type;
                                    $LeadSource1->is_main = 1;
                                    $LeadSource1->source = $main_source;
                                    $LeadSource1->save();
                                }
                            }
                        } catch (\Exception $e) {
                            $response_error['error_source'] = errorRes($e->getMessage(), 400);
                        }
                        // ADD MAIN SOURCE TO LEAD END

                        // ADD CLOSING DATE IN CLOSING TABLE START
                        // if ($request->lead_closing_date_time != '' || $request->lead_closing_date_time != null) {
                        //     $lead_closing_date_time = $lead_closing_date_and_time . " 23:59:59";
                        //     $lead_closing_date_time = date('Y-m-d H:i:s', strtotime($request->lead_closing_date_time . date('H:i:s')));
                        //     try {
                        //         $LeadClosing = new LeadClosing();
                        //         $LeadClosing->lead_id = $Lead->id;
                        //         $LeadClosing->closing_date = $lead_closing_date_time;
                        //         $LeadClosing->entryby = Auth::user()->id;
                        //         $LeadClosing->entryip = $request->ip();
                        //         $LeadClosing->save();
                        //     } catch (\Exception $e) {
                        //         $response_error['error_closingdate'] = errorRes($e->getMessage(), 400);
                        //     }
                        // } else {
                        //     $lead_closing_date_time = $request->lead_closing_date_time;
                        // }
                        // ADD CLOSING DATE IN CLOSING TABLE END

                        if ($Lead_contact_id != 0) {
                            $Lead_Update = Lead::find($Lead->id);
                            $Lead_Update->main_contact_id = $Lead_contact_id;
                            $Lead_Update->save();
                        }

                        $response = successRes('Successfully saved lead');
                        $response['id'] = $Lead->id;
                        $response['lead_source_type'] = $main_source_type;
                    } catch (\Exception $e) {
                        $response = errorRes($e->getMessage(), 400);
                    }
                } else {
                    // LEAD EDIT TIME
                    try {
                        if ($change_field != '') {
                            $timeline = [];
                            $timeline['lead_id'] = $Lead->id;
                            $timeline['type'] = 'lead-update';
                            $timeline['reffrance_id'] = $Lead->id;
                            $timeline['description'] = 'Lead Detail Updated ' . $change_field . ' by ' . Auth::user()->first_name . ' ' . Auth::user()->last_name;
                            $timeline['source'] = 'WEB';
                            saveLeadTimeline($timeline);
                        }

                        $LeadContact = LeadContact::find($Lead->main_contact_id);
                        $LeadContact->lead_id = $Lead->id;
                        $LeadContact->contact_tag_id = 1;
                        $LeadContact->first_name = $Lead->first_name;
                        $LeadContact->last_name = $Lead->last_name;
                        $LeadContact->phone_number = $Lead->phone_number;
                        $LeadContact->alernate_phone_number = 0;
                        $LeadContact->email = $Lead->email;
                        $LeadContact->type = 0;
                        $LeadContact->type_detail = 0;
                        $LeadContact->save();
                        $Lead_contact_id = 0;
                        if ($LeadContact) {
                            $Lead_contact_id = $LeadContact->id;
                        }

                        $LeadContactUpdate = LeadContact::where([['lead_contacts.lead_id', $Lead->id], ['lead_contacts.id', '!=', $Lead->main_contact_id], ['lead_contacts.type', '!=', 0]]);
                        $LeadContactUpdate->update(['status' => 0]);

                        LeadSource::where([['lead_sources.lead_id', $Lead->id]])->delete();

                        // ADD ARCHITECT CONTACT START
                        try {
                            if ($Lead->architect != 0) {
                                $Architect = User::find($Lead->architect);

                                if ($Architect) {
                                    $new_type_detail = 'user-' . $Architect->type . '-' . $Lead->architect;

                                    $status_update_arc = LeadContact::query();
                                    $status_update_arc->where('lead_id', $Lead->id);
                                    $status_update_arc->where('contact_tag_id', 0);
                                    $status_update_arc->where('type_detail', $new_type_detail);
                                    $status_update_arc = $status_update_arc->first();

                                    if ($status_update_arc) {
                                        $status_update_arc->status = 1;
                                        $status_update_arc->save();
                                    } else {
                                        $LeadContact_arc = new LeadContact();
                                        $LeadContact_arc->lead_id = $Lead->id;
                                        $LeadContact_arc->contact_tag_id = 0;
                                        $LeadContact_arc->first_name = $Architect->first_name;
                                        $LeadContact_arc->last_name = $Architect->last_name;
                                        $LeadContact_arc->phone_number = $Architect->phone_number;
                                        $LeadContact_arc->alernate_phone_number = 0;
                                        $LeadContact_arc->email = $Architect->email;
                                        $LeadContact_arc->type = $Architect->type;
                                        $LeadContact_arc->type_detail = 'user-' . $Architect->type . '-' . $Lead->architect;
                                        $LeadContact_arc->save();
                                    }
                                }
                            }
                        } catch (\Exception $e) {
                            $response_error['error_arc'] = errorRes($e->getMessage(), 400);
                        }
                        // ADD ARCHITECT CONTACT END

                        // ADD ELECTRICIAN CONTACT START
                        try {
                            if ($Lead->electrician != 0) {
                                $Electrician = User::find($Lead->electrician);

                                if ($Electrician) {
                                    $new_type_detail = 'user-' . $Electrician->type . '-' . $Lead->electrician;

                                    $status_update_ele = LeadContact::query();
                                    $status_update_ele->where('lead_id', $Lead->id);
                                    $status_update_ele->where('contact_tag_id', 0);
                                    $status_update_ele->where('type_detail', $new_type_detail);
                                    $status_update_ele = $status_update_ele->first();

                                    if ($status_update_ele) {
                                        $status_update_ele->status = 1;
                                        $status_update_ele->save();
                                    } else {
                                        $LeadContact_ele = new LeadContact();
                                        $LeadContact_ele->lead_id = $Lead->id;
                                        $LeadContact_ele->contact_tag_id = 0;
                                        $LeadContact_ele->first_name = $Electrician->first_name;
                                        $LeadContact_ele->last_name = $Electrician->last_name;
                                        $LeadContact_ele->phone_number = $Electrician->phone_number;
                                        $LeadContact_ele->alernate_phone_number = 0;
                                        $LeadContact_ele->email = $Electrician->email;
                                        $LeadContact_ele->type = $Electrician->type;
                                        $LeadContact_ele->type_detail = 'user-' . $Electrician->type . '-' . $Lead->electrician;
                                        $LeadContact_ele->save();
                                    }
                                }
                            }
                        } catch (\Exception $e) {
                            $response_error['error_ele'] = errorRes($e->getMessage(), 400);
                        }
                        // ADD ELECTRICIAN CONTACT END

                        // ADD MULTI SOURCE SAVE TO CONTACT START
                        try {
                            if ($request->no_of_source > 0) {
                                for ($i = 1; $i <= $request->no_of_source; $i++) {
                                    if (isset($request->all()['lead_source_type_' . $i])) {

                                        $multi_source_type = $request->all()['lead_source_type_' . $i];

                                        if (explode('-', $multi_source_type)[0] == 'textrequired') {
                                            $multi_source = $request->all()['lead_source_text_' . $i];
                                        } elseif (explode('-', $multi_source_type)[0] == 'textnotrequired') {
                                            $multi_source = $request->all()['lead_source_text_' . $i];
                                        } elseif (explode('-', $multi_source_type)[0] == 'fix') {
                                            $multi_source = $request->all()['lead_source_text_' . $i];
                                        } else {
                                            $multi_source = $request->all()['lead_source_' . $i];
                                        }

                                        if (($multi_source_type != null && $multi_source_type == 'user-201') || $multi_source_type == 'user-202' || $multi_source_type == 'user-301' || $multi_source_type == 'user-302' || $multi_source_type == 'user-101' || $multi_source_type == 'user-102' || $multi_source_type == 'user-103' || $multi_source_type == 'user-104' || $multi_source_type == 'user-105') {
                                            if ($multi_source != 0 || $multi_source != null || $multi_source != '') {
                                                if ($multi_source != $Lead->electrician && $multi_source != $Lead->architect) {
                                                    $multi_Source_1 = User::where('id', $multi_source)->first();

                                                    if ($multi_Source_1) {
                                                        if ($multi_Source_1->id != $Lead->electrician && $multi_Source_1->id != $Lead->architect) {
                                                            $new_type_detail = 'user-' . $multi_Source_1->type . '-' . $multi_Source_1->id;

                                                            $status_update_s1 = LeadContact::query();
                                                            $status_update_s1->where('lead_id', $Lead->id);
                                                            $status_update_s1->where('contact_tag_id', 0);
                                                            $status_update_s1->where('type_detail', $new_type_detail);
                                                            $status_update_s1 = $status_update_s1->first();

                                                            if ($status_update_s1) {
                                                                $status_update_s1->status = 1;
                                                                $status_update_s1->save();
                                                            } else {
                                                                $LeadContact_s1 = new LeadContact();
                                                                $LeadContact_s1->lead_id = $Lead->id;
                                                                $LeadContact_s1->contact_tag_id = 0;
                                                                if (isChannelPartner($multi_Source_1->type) != 0) {
                                                                    $ChannelPartner = ChannelPartner::find($multi_Source_1->reference_id);
                                                                    $LeadContact_s1->first_name = $ChannelPartner->firm_name;
                                                                    $LeadContact_s1->last_name = '';
                                                                } else {
                                                                    $LeadContact_s1->first_name = $multi_Source_1->first_name;
                                                                    $LeadContact_s1->last_name = $multi_Source_1->last_name;
                                                                }
                                                                $LeadContact_s1->phone_number = $multi_Source_1->phone_number;
                                                                $LeadContact_s1->alernate_phone_number = 0;
                                                                $LeadContact_s1->email = $multi_Source_1->email;
                                                                $LeadContact_s1->type = $multi_Source_1->type;
                                                                $LeadContact_s1->type_detail = 'user-' . $multi_Source_1->type . '-' . $multi_Source_1->id;
                                                                $LeadContact_s1->save();
                                                            }
                                                        }
                                                    }
                                                }

                                                // $multi_Source_1 = User::where('id', $multi_source)->first();

                                                // if ($multi_Source_1) {
                                                //     if ($multi_Source_1->id != $multi_source) {
                                                //         $LeadContact_s1 = new LeadContact();
                                                //         $LeadContact_s1->lead_id = $Lead->id;
                                                //         $LeadContact_s1->contact_tag_id = 0;
                                                //         $LeadContact_s1->first_name = $multi_Source_1->first_name;
                                                //         $LeadContact_s1->last_name = $multi_Source_1->last_name;
                                                //         $LeadContact_s1->phone_number = $multi_Source_1->phone_number;
                                                //         $LeadContact_s1->alernate_phone_number = 0;
                                                //         $LeadContact_s1->email = $multi_Source_1->email;
                                                //         $LeadContact_s1->type = $multi_Source_1->type;
                                                //         $LeadContact_s1->type_detail = "user-" . $multi_Source_1->type . "-" . $multi_Source_1->id;
                                                //         $LeadContact_s1->save();
                                                //     }
                                                // }
                                                $LeadSource1 = new LeadSource();
                                                $LeadSource1->lead_id = $Lead->id;
                                                $LeadSource1->source_type = $multi_source_type;
                                                $LeadSource1->is_main = 0;
                                                $LeadSource1->source = $multi_source;
                                                $LeadSource1->save();
                                            }
                                        } else {
                                            if ($multi_source != 0 || $multi_source != null || $multi_source != '') {
                                                $LeadSource1 = new LeadSource();
                                                $LeadSource1->lead_id = $Lead->id;
                                                $LeadSource1->source_type = $multi_source_type;
                                                $LeadSource1->is_main = 0;
                                                $LeadSource1->source = $multi_source;
                                                $LeadSource1->save();
                                            }
                                        }
                                    }
                                }
                            }
                        } catch (\Exception $e) {
                            $response_error['error_multi_source'] = errorRes($e->getMessage(), 400);
                        }
                        // ADD MULTI SOURCE SAVE TO CONTACT END

                        // ADD MAIN SOURCE TO LEAD START
                        try {
                            if ($main_source_type != null || $main_source_type == 'user-201' || $main_source_type == 'user-202' || $main_source_type == 'user-301' || $main_source_type == 'user-302' || $main_source_type == 'user-101' || $main_source_type == 'user-102' || $main_source_type == 'user-103' || $main_source_type == 'user-104' || $multi_source_type == 'user-105') {
                                if ($main_source != 0 || $main_source != null || $main_source != '') {
                                    if ($main_source != $Lead->electrician && $main_source != $Lead->architect) {
                                        $Source_1 = User::where('id', $main_source)->first();

                                        if ($Source_1) {
                                            if ($Source_1->id != $Lead->electrician && $Source_1->id != $Lead->architect) {
                                                $new_type_detail = 'user-' . $Source_1->type . '-' . $Source_1->id;

                                                $status_update_s2 = LeadContact::query();
                                                $status_update_s2->where('lead_id', $Lead->id);
                                                $status_update_s2->where('contact_tag_id', 0);
                                                $status_update_s2->where('type_detail', $new_type_detail);
                                                $status_update_s2 = $status_update_s2->first();

                                                if ($status_update_s2) {
                                                    $status_update_s2->status = 1;
                                                    $status_update_s2->save();
                                                } else {
                                                    $LeadContact_s1 = new LeadContact();
                                                    $LeadContact_s1->lead_id = $Lead->id;
                                                    $LeadContact_s1->contact_tag_id = 0;
                                                    if (isChannelPartner($Source_1->type) != 0) {
                                                        $ChannelPartner = ChannelPartner::find($Source_1->reference_id);
                                                        $LeadContact_s1->first_name = $ChannelPartner->firm_name;
                                                        $LeadContact_s1->last_name = '';
                                                    } else {
                                                        $LeadContact_s1->first_name = $Source_1->first_name;
                                                        $LeadContact_s1->last_name = $Source_1->last_name;
                                                    }
                                                    // $LeadContact_s1->first_name = $Source_1->first_name;
                                                    // $LeadContact_s1->last_name = $Source_1->last_name;
                                                    $LeadContact_s1->phone_number = $Source_1->phone_number;
                                                    $LeadContact_s1->alernate_phone_number = 0;
                                                    $LeadContact_s1->email = $Source_1->email;
                                                    $LeadContact_s1->type = $Source_1->type;
                                                    $LeadContact_s1->type_detail = 'user-' . $Source_1->type . '-' . $Source_1->id;
                                                    $LeadContact_s1->save();
                                                }
                                            }
                                        }
                                    }

                                    // $Source_1 = User::where('id', $main_source)->first();

                                    // if ($Source_1) {
                                    //     if ($Source_1->id != $main_source) {
                                    //         $LeadContact_s1 = new LeadContact();
                                    //         $LeadContact_s1->lead_id = $Lead->id;
                                    //         $LeadContact_s1->contact_tag_id = 0;
                                    //         $LeadContact_s1->first_name = $Source_1->first_name;
                                    //         $LeadContact_s1->last_name = $Source_1->last_name;
                                    //         $LeadContact_s1->phone_number = $Source_1->phone_number;
                                    //         $LeadContact_s1->alernate_phone_number = 0;
                                    //         $LeadContact_s1->email = $Source_1->email;
                                    //         $LeadContact_s1->type = $Source_1->type;
                                    //         $LeadContact_s1->type_detail = "user-" . $Source_1->type . "-" . $Source_1->id;
                                    //         $LeadContact_s1->save();
                                    //     }
                                    // }
                                    $LeadSource1 = new LeadSource();
                                    $LeadSource1->lead_id = $Lead->id;
                                    $LeadSource1->source_type = $main_source_type;
                                    $LeadSource1->source = $main_source;
                                    $LeadSource1->is_main = 1;
                                    $LeadSource1->save();
                                }
                            } else {
                                if ($main_source != 0 || $main_source != null || $main_source != '') {
                                    $LeadSource1 = new LeadSource();
                                    $LeadSource1->lead_id = $Lead->id;
                                    $LeadSource1->source_type = $main_source_type;
                                    $LeadSource1->source = $main_source;
                                    $LeadSource1->is_main = 1;
                                    $LeadSource1->save();
                                }
                            }
                        } catch (\Exception $e) {
                            $response_error['error_source'] = errorRes($e->getMessage(), 400);
                        }
                        // ADD MAIN SOURCE TO LEAD END

                        // ADD CLOSING DATE IN CLOSING TABLE START
                        if ($lead_closing_date_and_time != '' || $lead_closing_date_and_time != null) {
                            $lead_closing_date_time = date('Y-m-d H:i:s', strtotime($request->lead_closing_date_time . date('H:i:s')));

                            try {
                                $LeadClosing = new LeadClosing();
                                $LeadClosing->lead_id = $Lead->id;
                                $LeadClosing->closing_date = $lead_closing_date_time;
                                $LeadClosing->entryby = Auth::user()->id;
                                $LeadClosing->entryip = $request->ip();
                                $LeadClosing->save();
                            } catch (\Exception $e) {
                                $response_error['error_closingdate'] = errorRes($e->getMessage(), 400);
                            }
                        } else {
                            $lead_closing_date_time = $lead_closing_date_and_time;
                        }
                        // ADD CLOSING DATE IN CLOSING TABLE END

                        $Lead_Update = Lead::find($Lead->id);
                        if ($Lead_contact_id != 0) {
                            $Lead_Update->main_contact_id = $Lead_contact_id;
                        }

                        $Lead_Update->closing_date_time = $lead_closing_date_time;
                        $Lead_Update->save();

                        $response = successRes('Successfully Updated lead');
                        $response['id'] = $Lead->id;
                        $response['lead_source_type'] = $request->lead_source_type;
                    } catch (\Exception $e) {
                        $response = errorRes($e->getMessage(), 400);
                        $response['err'] = $e;
                        $response['line'] = $e->getLine();
                    }
                }
            } else {
                $response = errorRes('Lead Not Saved', 400);
            }

            $response['error'] = $response_error;
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }
    function searchSiteStage(Request $request)
    {
        $searchKeyword = isset($request->q) ? $request->q : '';

        $data = CRMSettingStageOfSite::select('id', 'name as text');
        $data->where('crm_setting_stage_of_site.status', 1);
        $data->where('crm_setting_stage_of_site.name', 'like', '%' . $searchKeyword . '%');
        $data->limit(15);
        $data = $data->get();
        $response = [];
        $response['results'] = $data;
        $response['pagination']['more'] = true;
        return response()->json($response)->header('Content-Type', 'application/json');
    }
    function searchSiteType(Request $request)
    {
        $searchKeyword = isset($request->q) ? $request->q : '';

        $data = CRMSettingSiteType::select('id', 'name as text');
        $data->where('crm_setting_site_type.status', 1);
        $data->where('crm_setting_site_type.name', 'like', '%' . $searchKeyword . '%');
        $data->limit(15);
        $data = $data->get();
        $response = [];
        $response['results'] = $data;
        $response['pagination']['more'] = true;
        return response()->json($response)->header('Content-Type', 'application/json');
    }
    function searchBHK(Request $request)
    {
        $searchKeyword = isset($request->q) ? $request->q : '';

        $data = CRMSettingBHK::select('id', 'name as text');
        $data->where('crm_setting_bhk.status', 1);
        $data->where('crm_setting_bhk.name', 'like', '%' . $searchKeyword . '%');
        $objSiteType = CRMSettingSiteType::find($request->site_type);
        if (isset($objSiteType)) {
            if ($objSiteType->is_bhk == 0) {
                $data->where('crm_setting_bhk.id', 7);
            }
        }
        $data->limit(15);
        $data = $data->get();
        $response = [];
        $response['results'] = $data;
        $response['pagination']['more'] = true;
        return response()->json($response)->header('Content-Type', 'application/json');
    }
    function searchWantToCover(Request $request)
    {
        $searchKeyword = isset($request->q) ? $request->q : '';

        $data = CRMSettingWantToCover::select('id', 'name as text');
        $data->where('crm_setting_want_to_cover.status', 1);
        $data->where('crm_setting_want_to_cover.name', 'like', '%' . $searchKeyword . '%');
        $data->limit(15);
        $data = $data->get();
        $response = [];
        $response['results'] = $data;
        $response['pagination']['more'] = true;
        return response()->json($response)->header('Content-Type', 'application/json');
    }
    function searchSourceType(Request $request)
    {
        $searchKeyword = isset($request->q) ? $request->q : '';
        $data = [];

        foreach (getLeadSourceTypes() as $static_key => $static_value) {
            if (isset($request->is_advance_filter) && $request->is_advance_filter == 0) {
                if ($request->source_type != null) {
                    if (!in_array($static_value['id'], array_map('intval', explode(',', $request->source_type)))) {
                        if ($static_value['id'] != 8 && $static_value['id'] != 4) {
                            $fix_source_data['id'] = $static_value['type'] . '-' . $static_value['id'];
                            $fix_source_data['text'] = $static_value['lable'];
                            array_push($data, $fix_source_data);
                        }
                    }
                } else {
                    if ($static_value['id'] != 8 && $static_value['id'] != 4) {
                        $fix_source_data['id'] = $static_value['type'] . '-' . $static_value['id'];
                        $fix_source_data['text'] = $static_value['lable'];
                        array_push($data, $fix_source_data);
                    }
                }
            } elseif ($static_value['is_editable'] != 0) {
                if ($request->source_type != null) {
                    if (!in_array($static_value['id'], array_map('intval', explode(',', $request->source_type)))) {
                        if ($static_value['id'] != 8 && $static_value['id'] != 4) {
                            $fix_source_data['id'] = $static_value['type'] . '-' . $static_value['id'];
                            $fix_source_data['text'] = $static_value['lable'];
                            array_push($data, $fix_source_data);
                        }
                    }
                } else {
                    if ($static_value['id'] != 8 && $static_value['id'] != 4) {
                        $fix_source_data['id'] = $static_value['type'] . '-' . $static_value['id'];
                        $fix_source_data['text'] = $static_value['lable'];
                        array_push($data, $fix_source_data);
                    }
                }
            }
        }

        if (!empty($searchKeyword)) {
            $data = array_filter($data, function ($item) use ($searchKeyword) {
                return stripos($item['text'], $searchKeyword) !== false;
            });
        }

        $data = array_values($data);

        $response = [];
        $response['results'] = $data;
        $response['pagination']['more'] = true;
        return response()->json($response)->header('Content-Type', 'application/json');
    }
    function searchSource(Request $request)
    {
        $isArchitect = isArchitect();
        $isSalePerson = isSalePerson();
        $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
        $isThirdPartyUser = isThirdPartyUser();
        $isChannelPartner = isChannelPartner(Auth::user()->type);

        $searchKeyword = $request->q;
        $source_type = explode('-', $request->source_type);

        if ($source_type[0] == 'user') {
            if (isset(getChannelPartners()[$source_type[1]]['short_name'])) {
                if ($isSalePerson == 1) {
                    $childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);

                    $salePerson = SalePerson::select('cities')
                        ->where('user_id', Auth::user()->id)
                        ->first();
                    $cities = [];
                    if ($salePerson) {
                        $cities = explode(',', $salePerson->cities);
                    } else {
                        $cities = [0];
                    }
                }

                $data = User::select('users.id', 'channel_partner.firm_name  AS text', 'users.phone_number');
                $data->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
                $data->whereIn('users.status', [1, 2, 3, 4, 5]);
                $data->where('users.type', $source_type[1]);
                // dd($data);
                if ($isSalePerson == 1) {
                    $data->where(function ($query) use ($cities, $childSalePersonsIds) {
                        $query->whereIn('users.city_id', $cities);

                        $query->orWhere(function ($query2) use ($childSalePersonsIds) {
                            foreach ($childSalePersonsIds as $key => $value) {
                                if ($key == 0) {
                                    $query2->whereRaw('FIND_IN_SET("' . $value . '",channel_partner.sale_persons)>0');
                                } else {
                                    $query2->orWhereRaw('FIND_IN_SET("' . $value . '",channel_partner.sale_persons)>0');
                                }
                            }
                        });
                    });
                }

                $data->where(function ($query) use ($searchKeyword) {
                    $query->where('channel_partner.firm_name', 'like', '%' . $searchKeyword . '%');
                    $query->orWhere('users.phone_number', 'like', '%' . $searchKeyword . '%'); // Search for phone numbers
                
                });
                $data->limit(5);
                $data = $data->get();
                $newdata = [];
                foreach ($data as $key => $value) {
                    $data1['id'] = $value->id;
                    $data1['text'] = $value->text . '(' . $value->phone_number . ')';
                    $data1['phone_number'] = $value->phone_number;
                    array_push($newdata, $data1);
                }
                $data = $newdata;
            } else {
                $data = User::select('users.id', 'users.type', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"), 'users.phone_number');
                $data->whereIn('users.status', [1, 2, 3, 4, 5]);

                if ($source_type[1] == 202) {
                    // FOR ARCHITECT
                    if ($isSalePerson == 1) {
                        $salePerson = SalePerson::select('cities')
                            ->where('user_id', Auth::user()->id)
                            ->first();
                        $cities = [];
                        if ($salePerson) {
                            $cities = explode(',', $salePerson->cities);
                        } else {
                            $cities = [0];
                        }
                        $data->whereIn('users.city_id', $cities);
                    } elseif ($isChannelPartner != 0) {
                        $data->where('users.city_id', Auth::user()->city_id);
                    }

                    $data->whereIn('users.type', [201, 202]);
                } elseif ($source_type[1] == 302) {
                    // FOR ELECTRICIAN
                    if ($isSalePerson == 1) {
                        $salePerson = SalePerson::select('cities')
                            ->where('user_id', Auth::user()->id)
                            ->first();
                        $cities = [];
                        if ($salePerson) {
                            $cities = explode(',', $salePerson->cities);
                        } else {
                            $cities = [0];
                        }
                        $data->whereIn('users.city_id', $cities);
                    } elseif ($isChannelPartner != 0) {
                        $data->where('users.city_id', Auth::user()->city_id);
                    }

                    $data->whereIn('users.type', [301, 302]);
                } else {
                    $data->where('users.type', $source_type[1]);
                }

                $data->where(function ($query) use ($searchKeyword) {
                    $query->where('users.first_name', 'like', '%' . $searchKeyword . '%');
                    $query->orWhere('users.last_name', 'like', '%' . $searchKeyword . '%');
                    $query->orWhere('users.phone_number', 'like', '%' . $searchKeyword . '%');
                    $query->orWhereRaw("CONCAT(users.first_name,' ',users.last_name)" . ' like ? ', ["%" . $searchKeyword . "%"]);
                });

                $data->limit(5);
                $data = $data->get();
                $newdata = [];
                foreach ($data as $key => $value) {
                    $data1['id'] = $value->id;
                    $label = '';
                    if ($value->type == 301 || $value->type == 201) {
                        $label = ' - NonPrime';
                    } elseif ($value->type == 302 || $value->type == 202) {
                        $label = ' - Prime';
                    } else {
                        $label = '';
                    }
                    $data1['text'] = $value->text . '(' . $value->phone_number . ')' . $label;
                    $data1['phone_number'] = $value->phone_number;
                    array_push($newdata, $data1);
                }
                $data = $newdata;
            }
        } elseif ($source_type[0] == 'master') {
            $data = CRMSettingSource::select('id', 'name as text');
            $data->where('crm_setting_source.status', 1);
            $data->where('crm_setting_source.source_type_id', $source_type[1]);
            $data->where('crm_setting_source.name', 'like', '%' . $searchKeyword . '%');
            $data->limit(5);
            $data = $data->get();
        } elseif ($source_type[0] == 'exhibition') {
            $data = Exhibition::select('id', 'name as text');
            $data->where('exhibition.name', 'like', '%' . $searchKeyword . '%');
            $data->limit(5);
            $data = $data->get();
        } else {
            $data = '';
        }

        $response = [];
        $response['results'] = $data;
        $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');
    }
    function searchSourceOld(Request $request)
    {
        $searchKeyword = isset($request->q) ? $request->q : 'a';

        $isUser = 0;
        $userType = [];

        if (strpos($request->souce_type, 'user-') !== false) {
            $isUser = 1;

            if (strpos($request->souce_type, ',') !== false) {
                $explodeSourceType = explode(',', $request->souce_type);
                foreach ($explodeSourceType as $key => $value) {
                    $sourcePieces = explode('-', $value);
                    if (count($sourcePieces) > 0) {
                        $userType[] = $sourcePieces[1];
                    }
                }
            } else {
                $sourcePieces = explode('-', $request->souce_type);
                if (count($sourcePieces) > 0) {
                    $userType[] = $sourcePieces[1];
                }
            }
        }

        if ($isUser == 1) {
            $channel_partner = ['user-101', 'user-102', 'user-103', 'user-104', 'user-105'];
            if (in_array($request->souce_type, $channel_partner)) {
                $data = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text2"), 'channel_partner.firm_name  AS text');
                $data->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
                $data->where('users.status', 1);
                $data->whereIn('users.type', $userType);
                $data->where(function ($query) use ($searchKeyword) {
                    $query->where('users.first_name', 'like', '%' . $searchKeyword . '%');
                    $query->orWhere('users.last_name', 'like', '%' . $searchKeyword . '%');
                    $query->orWhere('channel_partner.firm_name', 'like', '%' . $searchKeyword . '%');
                });
                $data->limit(5);
                $data = $data->get();
                $data = json_encode($data);
                $data = json_decode($data, true);
            } else {
                $data = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"));
                $data->where('users.status', 1);
                $data->whereIn('users.type', $userType);
                $data->where(function ($query) use ($searchKeyword) {
                    $query->where('users.first_name', 'like', '%' . $searchKeyword . '%');
                    $query->orWhere('users.last_name', 'like', '%' . $searchKeyword . '%');
                });
                $data->limit(5);
                $data = $data->get();
                $data = json_encode($data);
                $data = json_decode($data, true);
            }
        } else {
            $data = CRMSettingSource::select('id', 'name as text');
            $data->where('crm_setting_source.status', 1);
            $data->where('crm_setting_source.name', 'like', '%' . $searchKeyword . '%');
            $data->limit(5);
            $data = $data->get();

            $data = json_encode($data);
            $data = json_decode($data, true);
        }

        $response = [];

        $response['results'] = $data;
        $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');
    }
    function searchStatus(Request $request)
    {
        $searchKeyword = isset($request->q) ? $request->q : '';
        $type = isset($request->type) ? $request->type : 0;

        $LeadStatus = getLeadStatus();

        $finalArray[] = [];

        foreach ($LeadStatus as $key => $value) {
            // $LeadStatus[$key]['id'] = $value['id'] . "";
            // $LeadStatus[$key]['text'] = $value['name'];

            if ($value['type'] == 0) {
                $countFinal = count($finalArray);
                $finalArray[$countFinal] = [];
                $finalArray[$countFinal]['id'] = $value['id'];
                $finalArray[$countFinal]['text'] = $value['name'];
            }
        }

        $response = [];
        $response['results'] = $finalArray;
        $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');
    }
    function searchSubStatus(Request $request)
    {
        $searchKeyword = isset($request->q) ? $request->q : '';

        $data = CRMSettingSubStatus::select('id', 'name as text');
        $data->where('crm_setting_sub_status.status', 1);
        $data->where('crm_setting_sub_status.lead_status', $request->lead_status);
        $data->where('crm_setting_sub_status.name', 'like', '%' . $searchKeyword . '%');
        $data->limit(15);
        $data = $data->get();
        $response = [];
        $response['results'] = $data;
        $response['pagination']['more'] = true;
        return response()->json($response)->header('Content-Type', 'application/json');
    }
    function searchCompetitors(Request $request)
    {
        $searchKeyword = isset($request->q) ? $request->q : '';

        $data = CRMSettingCompetitors::select('id', 'name as text');
        $data->where('crm_setting_competitors.status', 1);
        $data->where('crm_setting_competitors.name', 'like', '%' . $searchKeyword . '%');
        $data->limit(15);
        $data = $data->get();
        $response = [];
        $response['results'] = $data;
        $response['pagination']['more'] = true;
        return response()->json($response)->header('Content-Type', 'application/json');
    }
    function searchAssignedUser(Request $request)
    {
        $isSalePerson = isSalePerson();
        $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
        $isThirdPartyUser = isThirdPartyUser();
        $isTaleSalesUser = isTaleSalesUser();
        $isArchitect = isArchitect();
        $isElectrician = isElectrician();
        $isChannelPartner = isChannelPartner(Auth::user()->type);
        if ($isSalePerson == 1) {
            $childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
        } elseif ($isChannelPartner != 0) {
            // $channelPartnersSalesPersons = getChannelPartnerSalesPersonsIds(Auth::user()->id);
        }

        $UserResponse = [];
        $q = $request->q;
        $User = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));

        $User->where('users.status', 1);

        if ($isAdminOrCompanyAdmin == 1) {
            $User->whereIn('users.type', [2]);
            // $User->whereIn('users.type', array(0, 1, 2));
        } elseif ($isThirdPartyUser == 1) {
            $User->whereIn('users.type', [2]);
            $User->where('users.city_id', Auth::user()->city_id);
        } elseif ($isSalePerson == 1) {
            $User->where('users.type', 2);
            $User->whereIn('users.id', $childSalePersonsIds);
        } elseif ($isChannelPartner != 0) {
            // $ChannelPartner =  ChannelPartner::find(Auth::user()->reference_id);
            $User->where('users.type', 2);
            $User->where('users.city_id', Auth::user()->city_id);
            // $User->orWhereIn('users.id', explode(',', $ChannelPartner['sale_persons']));
        } elseif ($isTaleSalesUser == 1) {
            $User->where('users.type', 2);
        } elseif ($isArchitect == 1 || $isElectrician == 1) {
            $User->where('users.type', 2);
            $User->where('users.city_id', Auth::user()->city_id);
        }

        if (isset($q)) {
            $User->where(function ($query) use ($q) {
                $query->where('users.first_name', 'like', '%' . $q . '%');
                $query->orWhere('users.last_name', 'like', '%' . $q . '%');
            });
        }

        $User->limit(15);
        $User = $User->get();

        $newData = [];
        if (count($User) > 0) {
            foreach ($User as $User_key => $User_value) {
                $UserResponse['id'] = $User_value['id'];
                $UserResponse['text'] = $User_value['full_name'];
                array_push($newData, $UserResponse);
            }
        }

        $response = [];
        $response['results'] = $newData;
        $response['pagination']['more'] = true;
        return response()->json($response)->header('Content-Type', 'application/json');
    }
    public function getListAjax(Request $request)
    {
        $isSalePerson = isSalePerson();
        $isReception = isReception();
        $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
        $source_type = getInquirySourceTypes();
        if ($isSalePerson == 1) {
            $parentSalesUsers = getParentSalePersonsIds(Auth::user()->id);
            $childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
        }

        $selectColumns = ['leads.id', 'leads.inquiry_id', 'leads.first_name', 'leads.last_name', 'leads.phone_number', 'leads.status', 'leads.is_deal', 'created.first_name as lead_owner_name'];

        $searchColumns = [
            0 => 'leads.id',
            1 => 'leads.first_name',
            2 => 'leads.last_name',
            3 => 'leads.phone_number',
            4 => 'leads.inquiry_id',
        ];

        $sortingColumns = [
            0 => 'leads.id',
            1 => 'leads.id',
            2 => 'leads.first_name',
            3 => 'leads.phone_number',
            4 => 'leads.status',
            5 => 'leads.site_stage',
            6 => 'leads.closing_date_time',
            7 => 'leads.assigned_to',
            8 => 'leads.user_id',
        ];

        // RECORDSTOTAL START
        $query = Lead::query();
        $query->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
        $query->leftJoin('lead_files', 'lead_files.lead_id', '=', 'leads.id');
        if (isArchitect() == 1 || isElectrician() == 1) {
            $query->whereIn('leads.is_deal', [0, 1]);
        } else {
            if ($request->is_deal == 0) {
                $query->where('leads.is_deal', 0);
            } elseif ($request->is_deal == 1) {
                $query->where('leads.is_deal', 1);
            }
        }

        if (isset($request->status)) {
            if ($request->status != 0) {
                $query->where('leads.status', $request->status);
            }
        }

        $arr_where_clause = [];
        $arr_or_clause = [];

        if ($request->isAdvanceFilter == 1) {
            foreach (json_decode($request->AdvanceData, true) as $key => $filt_value) {
                $filter_value = '';
                $source_type = '0';
                if ($filt_value['clause'] == null || $filt_value['clause'] == '') {
                    $response = errorRes('Please Select Clause');
                    return response()->json($response)->header('Content-Type', 'application/json');
                } elseif ($filt_value['column'] == null || $filt_value['column'] == '') {
                    $response = errorRes('Please Select column');
                    return response()->json($response)->header('Content-Type', 'application/json');
                } elseif ($filt_value['condtion'] == null || $filt_value['condtion'] == '') {
                    $response = errorRes('Please Select condtion');
                    return response()->json($response)->header('Content-Type', 'application/json');
                } else {
                    $column = getFilterColumnCRM()[$filt_value['column']];
                    $condtion = getFilterCondtionCRM()[$filt_value['condtion']];
                    if ($column['value_type'] == 'text') {
                        if ($filt_value['value_text'] == null || $filt_value['value_text'] == '') {
                            $response = errorRes('Please enter value');
                            return response()->json($response)->header('Content-Type', 'application/json');
                        } else {
                            $filter_value = $filt_value['value_text'];
                        }
                    } elseif ($column['value_type'] == 'select' && $condtion['value_type'] == 'single_select') {
                        if ($column['code'] == 'leads_source' && ($filt_value['value_source_type'] == null || $filt_value['value_source_type'] == '')) {
                            $response = errorRes('Please select value');
                            return response()->json($response)->header('Content-Type', 'application/json');
                        } else {
                            $source_type = $filt_value['value_source_type'];
                        }
                        if ($column['code'] == 'leads_source') {
                            $source_type = explode('-', $source_type);
                            if ($source_type[0] == 'user') {
                                $filter_value = $filt_value['value_select'];
                            } elseif ($source_type[0] == 'master') {
                                $filter_value = $filt_value['value_select'];
                            } elseif ($source_type[0] == 'exhibition') {
                                $filter_value = $filt_value['value_select'];
                            } else {
                                $filter_value = $filt_value['value_text'];
                            }
                        } else {
                            if ($filt_value['value_select'] == null || $filt_value['value_select'] == '') {
                                $response = errorRes('Please select value');
                                return response()->json($response)->header('Content-Type', 'application/json');
                            } else {
                                $filter_value = $filt_value['value_select'];
                            }
                        }
                    } elseif ($column['value_type'] == 'select' && $condtion['value_type'] == 'multi_select') {
                        if ($column['code'] == 'leads_source' && ($filt_value['value_source_type'] == null || $filt_value['value_source_type'] == '')) {
                            $response = errorRes('Please select value');
                            return response()->json($response)->header('Content-Type', 'application/json');
                        } else {
                            $source_type = $filt_value['value_source_type'];
                        }
                        if (!isset($filt_value['value_multi_select']) && empty($filt_value['value_multi_select'])) {
                            $response = errorRes('Please select value');
                            return response()->json($response)->header('Content-Type', 'application/json');
                        } else {
                            $filter_value = $filt_value['value_multi_select'];
                        }
                    } elseif ($column['value_type'] == 'date' && $condtion['value_type'] == 'single_select') {
                        if ($filt_value['value_date'] == null || $filt_value['value_date'] == '') {
                            $response = errorRes('Please enter date');
                            return response()->json($response)->header('Content-Type', 'application/json');
                        } else {
                            $filter_value = $filt_value['value_date'];
                        }
                    } elseif ($column['value_type'] == 'date' && $condtion['value_type'] == 'between') {
                        if (($filt_value['value_from_date'] == null || $filt_value['value_from_date'] == '') && ($filt_value['value_to_date'] == null || $filt_value['value_to_date'] == '')) {
                            $response = errorRes('Please enter from to date');
                            return response()->json($response)->header('Content-Type', 'application/json');
                        } else {
                            $filter_value = $filt_value['value_from_date'] . ',' . $filt_value['value_to_date'];
                        }
                    } elseif ($column['value_type'] == 'reward_select') {
                        if ($filt_value['value_select'] == null || $filt_value['value_select'] == '') {
                            $response = errorRes('Please select value');
                            return response()->json($response)->header('Content-Type', 'application/json');
                        } else {
                            $filter_value = $filt_value['value_select'];
                        }
                    }
                    if ($filt_value['clause'] != 0) {
                        $clause = getFilterClauseCRM()[$filt_value['clause']];
                    }

                    if ($filt_value['clause'] == 0) {
                        $newdata['clause'] = 0;
                        $newdata['column'] = $column['id'];
                        $newdata['condtion'] = $condtion['id'];
                        $newdata['value'] = $filter_value;
                        $newdata['source_type'] = $source_type;

                        array_push($arr_where_clause, $newdata);
                    } elseif ($clause['clause'] == 'where') {
                        $newdata['clause'] = $clause['id'];
                        $newdata['column'] = $column['id'];
                        $newdata['condtion'] = $condtion['id'];
                        $newdata['value'] = $filter_value;
                        $newdata['source_type'] = $source_type;

                        array_push($arr_where_clause, $newdata);
                    } elseif ($clause['clause'] == 'orwhere') {
                        $newdata['clause'] = $clause['id'];
                        $newdata['column'] = $column['id'];
                        $newdata['condtion'] = $condtion['id'];
                        $newdata['value'] = $filter_value;
                        $newdata['source_type'] = $source_type;

                        array_push($arr_or_clause, $newdata);
                    }
                }
            }
        }

        if ($request->isAdvanceFilter == 1) {
            foreach ($arr_where_clause as $wherekey => $objwhere) {
                $Column = getFilterColumnCRM()[$objwhere['column']];
                $Condtion = getFilterCondtionCRM()[$objwhere['condtion']];
                $lstDateFilter = getDateFilterValue();
                $Filter_Value = $objwhere['value'];
                $source_type = $objwhere['source_type'];

                if ($Condtion['code'] == 'is') {
                    if ($Column['value_type'] == 'leads_source') {
                        $query->whereIn('lead_sources.source', $source_type);
                    } elseif ($Column['value_type'] == 'date') {
                        // $date_filter_value = date('Y-m-d', strtotime($Filter_Value));
                        // $query->whereDate($Column['column_name'], '=', $date_filter_value);
                        $objDateFilter = $lstDateFilter[$Filter_Value];

                        $currentStartDate = date('Y-m-d', strtotime(date('Y-m-d')));
                        $currentEndDate = date('Y-m-d', strtotime(date('Y-m-d')));

                        if ($objDateFilter['code'] == 'all_closing') {
                            $query->where($Column['column_name'], '!=', null);
                        } elseif ($objDateFilter['code'] == 'in_this_week') {
                            $currentWeekDay = date('w', strtotime($currentStartDate));
                            $weekStartDate = date('Y-m-d', strtotime($currentStartDate . ' -' . ($currentWeekDay - 1) . ' days'));
                            $weekEndDate = date('Y-m-d', strtotime($currentEndDate . ' +' . (7 - $currentWeekDay) . ' days'));
                            $query->whereDate($Column['column_name'], '<=', $weekEndDate);
                        } elseif ($objDateFilter['code'] == 'in_this_month') {
                            $monthStartDay = date('Y-m-01', strtotime($currentStartDate));
                            $monthEndDay = date('Y-m-t', strtotime($currentEndDate));
                            $query->whereDate($Column['column_name'], '<=', $monthEndDay);
                        } elseif ($objDateFilter['code'] == 'in_next_month') {
                            // $currentStartDate = date('Y-m-d', strtotime($currentStartDate));
                            // $currentStartDate = date('Y-m-d', strtotime($currentStartDate));

                            // $monthStartDay = date('Y-m-01', strtotime($currentStartDate . " + 1 month"));
                            // $monthEndDay = date('Y-m-t', strtotime($monthStartDay));
                            // $query->whereDate($Column['column_name'], '<', $monthEndDay);

                            $NextStartDate = Carbon::parse(date('Y-m-d', strtotime(date('Y-m-d'))));
                            $nextMonth = $NextStartDate->addMonthNoOverflow()->endOfMonth();
                            $query->whereDate($Column['column_name'], '<=', $nextMonth);
                        } elseif ($objDateFilter['code'] == 'in_next_two_month') {
                            // $monthStartDay = date('Y-m-01', strtotime($currentStartDate . " + 1 month"));
                            // $monthEndDay = date('Y-m-t', strtotime($currentEndDate . " + 2 month"));
                            // $query->whereDate($Column['column_name'], '<=', $monthEndDay);

                            $NextStartDate = Carbon::parse(date('Y-m-d', strtotime(date('Y-m-d'))));
                            $nextTwoMonth = $NextStartDate->addMonthNoOverflow(2)->endOfMonth();
                            $query->whereDate($Column['column_name'], '<=', $nextTwoMonth);
                        } elseif ($objDateFilter['code'] == 'in_next_three_month') {
                            // $monthStartDay = date('Y-m-01', strtotime($currentStartDate . " + 1 month"));
                            // $monthEndDay = date('Y-m-t', strtotime($currentEndDate . " + 3 month"));
                            // $query->whereDate($Column['column_name'], '>=', date('Y-m-d'));
                            // $query->whereDate($Column['column_name'], '<=', $monthEndDay);

                            $NextStartDate = Carbon::parse(date('Y-m-d', strtotime(date('Y-m-d'))));
                            $nextThreeMonth = $NextStartDate->addMonthNoOverflow(3)->endOfMonth();
                            $query->whereDate($Column['column_name'], '<=', $nextThreeMonth);
                        }
                        // if ($objDateFilter['code'] == "today") {
                        //     $date_filter_value = date('Y-m-d', strtotime(date('Y-m-d')));
                        //     $query->whereDate($Column['column_name'], '=', $date_filter_value);
                        //     // $query->whereDate($Column['column_name'], '=', date('Y-m-d'));
                        // }
                        else {
                            $date_filter_value = explode(',', $objDateFilter['value']);
                            $from_date_filter = $date_filter_value[0];
                            $from_date_filter_value = date('Y-m-d', strtotime($from_date_filter));

                            $to_date_filter = $date_filter_value[1];
                            $to_date_filter_value = date('Y-m-d', strtotime($to_date_filter));

                            $query->whereBetween(DB::raw('DATE(' . $Column['column_name'] . ')'), [$from_date_filter_value, $to_date_filter_value]);
                        }
                    } elseif ($Column['value_type'] == 'select') {
                        if ($Column['code'] == 'lead_miss_data') {
                            $missDataValue = getLeadFilterMissFilterValue()[$Filter_Value];
                            $query->where($missDataValue['column_name'], $missDataValue['value']);
                        } else {
                            $query->where($Column['column_name'], $Filter_Value);
                        }
                    } elseif ($Column['value_type'] == 'reward_select') {
                        if ($Filter_Value == 1) {
                            $query->leftJoin('lead_files', 'lead_files.lead_id', '=', 'leads.id');
                            $query->where('lead_files.hod_approved', 0);
                            $query->where('lead_files.status', 100);
                        } elseif ($Filter_Value == 2) {
                            $query->leftJoin('lead_files', 'lead_files.lead_id', '=', 'leads.id');
                            $query->Where('lead_files.hod_approved', 1);
                        }
                    } else {
                        $query->where($Column['column_name'], 'like', '%' . $Filter_Value . '%');
                    }
                } elseif ($Condtion['code'] == 'is_not') {
                    if ($Column['value_type'] == 'leads_source') {
                        $query->whereNotIn('lead_sources.source', $source_type);
                    } elseif ($Column['value_type'] == 'date') {
                        $objDateFilter = $lstDateFilter[$Filter_Value];

                        $currentStartDate = date('Y-m-d', strtotime(date('Y-m-d')));
                        $currentEndDate = date('Y-m-d', strtotime(date('Y-m-d')));

                        if ($objDateFilter['code'] == 'all_closing') {
                            $query->where($Column['column_name'], '!=', null);
                        } elseif ($objDateFilter['code'] == 'in_this_week') {
                            $currentWeekDay = date('w', strtotime($currentStartDate));
                            $weekStartDate = date('Y-m-d', strtotime($currentStartDate . ' -' . ($currentWeekDay - 1) . ' days'));
                            $weekEndDate = date('Y-m-d', strtotime($currentEndDate . ' +' . (7 - $currentWeekDay) . ' days'));
                            $query->whereDate($Column['column_name'], '<=', $weekEndDate);
                        } elseif ($objDateFilter['code'] == 'in_this_month') {
                            $monthStartDay = date('Y-m-01', strtotime($currentStartDate));
                            $monthEndDay = date('Y-m-t', strtotime($currentEndDate));
                            $query->whereDate($Column['column_name'], '<=', $monthEndDay);
                        } elseif ($objDateFilter['code'] == 'in_next_month') {
                            // $currentStartDate = date('Y-m-d', strtotime($currentStartDate . " +5 hours"));
                            // $currentStartDate = date('Y-m-d', strtotime($currentStartDate . " +30 minutes"));
                            $NextStartDate = Carbon::parse(date('Y-m-d', strtotime(date('Y-m-d'))));
                            $nextMonth = $NextStartDate->addMonthNoOverflow()->endOfMonth();
                            $query->whereDate($Column['column_name'], '<=', $nextMonth);

                            // $monthStartDay = date('Y-m-01', strtotime($currentStartDate . " + 1 month"));
                            // $monthEndDay = date('Y-m-t', strtotime($monthStartDay));
                        } elseif ($objDateFilter['code'] == 'in_next_two_month') {
                            // $monthStartDay = date('Y-m-01', strtotime($currentStartDate . " + 1 month"));
                            // $monthEndDay = date('Y-m-t', strtotime($currentEndDate . " + 2 month"));
                            // $query->whereDate($Column['column_name'], '<=', $monthEndDay);

                            $NextStartDate = Carbon::parse(date('Y-m-d', strtotime(date('Y-m-d'))));
                            $nextTwoMonth = $NextStartDate->addMonthNoOverflow(2)->endOfMonth();
                            $query->whereDate($Column['column_name'], '<=', $nextTwoMonth);
                        } elseif ($objDateFilter['code'] == 'in_next_three_month') {
                            // $monthStartDay = date('Y-m-01', strtotime($currentStartDate . " + 1 month"));
                            // $monthEndDay = date('Y-m-t', strtotime($currentEndDate . " + 3 month"));
                            // $query->whereDate($Column['column_name'], '>=', date('Y-m-d'));
                            // $query->whereDate($Column['column_name'], '<=', $monthEndDay);

                            $NextStartDate = Carbon::parse(date('Y-m-d', strtotime(date('Y-m-d'))));
                            $nextThreeMonth = $NextStartDate->addMonthNoOverflow(3)->endOfMonth();
                            $query->whereDate($Column['column_name'], '<=', $nextThreeMonth);
                        }

                        // if ($objDateFilter['code'] == "today") {
                        //     $date_filter_value = date('Y-m-d', strtotime(date('Y-m-d')));
                        //     $query->whereDate($Column['column_name'], '!=', $date_filter_value);
                        //     // $query->whereDate($Column['column_name'], '!=', date('Y-m-d'));
                        // }
                        else {
                            $date_filter_value = explode(',', $objDateFilter['value']);
                            $from_date_filter = $date_filter_value[0];
                            $from_date_filter_value = date('Y-m-d', strtotime($from_date_filter));

                            $to_date_filter = $date_filter_value[1];
                            $to_date_filter_value = date('Y-m-d', strtotime($to_date_filter));

                            $query->whereNotBetween(DB::raw('DATE(' . $Column['column_name'] . ')'), [$from_date_filter_value, $to_date_filter_value]);
                        }
                    } elseif ($Column['value_type'] == 'select') {
                        if ($Column['code'] == 'lead_miss_data') {
                            $missDataValue = getLeadFilterMissFilterValue()[$Filter_Value];
                            $query->where($missDataValue['column_name'], '<>', $missDataValue['value']);
                        } else {
                            $query->whereNotNull($Column['column_name']);
                            $query->where($Column['column_name'], '!=', $Filter_Value);
                        }
                    } else {
                        $query->whereNotNull($Column['column_name']);
                        $query->where($Column['column_name'], 'not like', '%' . $Filter_Value . '%');
                    }
                } elseif ($Condtion['code'] == 'contains') {
                    if ($Column['value_type'] == 'leads_source') {
                        $query->whereIn('lead_sources.source', $source_type);
                    }
                    if ($Column['value_type'] == 'select') {
                        if ($Column['code'] == 'lead_miss_data') {
                            foreach ($Filter_Value as $mis_key => $mis_value) {
                                $missDataValue = getLeadFilterMissFilterValue()[$mis_value];
                                $query->where($missDataValue['column_name'], $missDataValue['value']);
                            }
                        } else {
                            $query->whereIn($Column['column_name'], $Filter_Value);
                        }
                    } else {
                        $Filter_Value = explode(',', $Filter_Value);
                        $query->whereIn($Column['column_name'], $Filter_Value);
                    }
                } elseif ($Condtion['code'] == 'not_contains') {
                    if ($Column['value_type'] == 'leads_source') {
                        $query->whereNotIn('lead_sources.source', $source_type);
                    }
                    if ($Column['value_type'] == 'select') {
                        if ($Column['code'] == 'lead_miss_data') {
                            foreach ($Filter_Value as $mis_key => $mis_value) {
                                $missDataValue = getLeadFilterMissFilterValue()[$mis_value];
                                $query->where($missDataValue['column_name'], '<>', $missDataValue['value']);
                            }
                        } else {
                            $query->whereNotIn($Column['column_name'], $Filter_Value);
                        }
                    } else {
                        $Filter_Value = explode(',', $Filter_Value);
                        $query->whereNotIn($Column['column_name'], $Filter_Value);
                    }
                } elseif ($Condtion['code'] == 'between') {
                    if ($Column['value_type'] == 'date') {
                        $date_filter_value = explode(',', $Filter_Value);
                        $from_date_filter = $date_filter_value[0];
                        $from_date_filter_value = date('Y-m-d', strtotime($from_date_filter));

                        $to_date_filter = $date_filter_value[1];
                        $to_date_filter_value = date('Y-m-d', strtotime($to_date_filter));

                        $query->whereBetween(DB::raw('DATE(' . $Column['column_name'] . ')'), [$from_date_filter_value, $to_date_filter_value]);
                    }
                }
            }

            if (count($arr_or_clause) > 0) {
                $query->orWhere(function ($query) use ($arr_or_clause) {
                    foreach ($arr_or_clause as $orkey => $objor) {
                        $Column = getFilterColumnCRM()[$objor['column']];
                        $Condtion = getFilterCondtionCRM()[$objor['condtion']];
                        $lstDateFilter = getDateFilterValue();
                        $Filter_Value = $objor['value'];
                        $source_type = $objor['source_type'];

                        if ($Condtion['code'] == 'is') {
                            if ($Column['value_type'] == 'leads_source') {
                                $query->whereIn('lead_sources.source', $source_type);
                            } elseif ($Column['value_type'] == 'date') {
                                // $date_filter_value = date('Y-m-d', strtotime($Filter_Value));
                                // $query->orWhereDate($Column['column_name'], '=', $date_filter_value);

                                $objDateFilter = $lstDateFilter[$Filter_Value];

                                $currentStartDate = date('Y-m-d', strtotime(date('Y-m-d')));
                                $currentEndDate = date('Y-m-d', strtotime(date('Y-m-d')));

                                if ($objDateFilter['code'] == 'all_closing') {
                                    $query->orwhere($Column['column_name'], '!=', null);
                                } elseif ($objDateFilter['code'] == 'in_this_week') {
                                    $currentWeekDay = date('w', strtotime($currentStartDate));
                                    $weekStartDate = date('Y-m-d', strtotime($currentStartDate . ' -' . ($currentWeekDay - 1) . ' days'));
                                    $weekEndDate = date('Y-m-d', strtotime($currentEndDate . ' +' . (7 - $currentWeekDay) . ' days'));
                                    $query->orWhereDate($Column['column_name'], '<=', $weekEndDate);
                                } elseif ($objDateFilter['code'] == 'in_this_month') {
                                    $monthStartDay = date('Y-m-01', strtotime($currentStartDate));
                                    $monthEndDay = date('Y-m-t', strtotime($currentEndDate));
                                    $query->orWhereDate($Column['column_name'], '<=', $monthEndDay);
                                } elseif ($objDateFilter['code'] == 'in_next_month') {
                                    // $currentStartDate = date('Y-m-d', strtotime($currentStartDate . " +5 hours"));
                                    // $currentStartDate = date('Y-m-d', strtotime($currentStartDate . " +30 minutes"));
                                    // $monthStartDay = date('Y-m-01', strtotime($currentStartDate . " + 1 month"));
                                    // $monthEndDay = date('Y-m-t', strtotime($monthStartDay));
                                    // $query->orWhereDate($Column['column_name'], '<', $monthEndDay);

                                    $NextStartDate = Carbon::parse(date('Y-m-d', strtotime(date('Y-m-d'))));
                                    $nextMonth = $NextStartDate->addMonthNoOverflow()->endOfMonth();
                                    $query->whereDate($Column['column_name'], '<=', $nextMonth);
                                } elseif ($objDateFilter['code'] == 'in_next_two_month') {
                                    // $monthStartDay = date('Y-m-01', strtotime($currentStartDate . " + 1 month"));
                                    // $monthEndDay = date('Y-m-t', strtotime($currentEndDate . " + 2 month"));
                                    // $query->orWhereDate($Column['column_name'], '<=', $monthEndDay);

                                    $NextStartDate = Carbon::parse(date('Y-m-d', strtotime(date('Y-m-d'))));
                                    $nextTwoMonth = $NextStartDate->addMonthNoOverflow(2)->endOfMonth();
                                    $query->whereDate($Column['column_name'], '<=', $nextTwoMonth);
                                } elseif ($objDateFilter['code'] == 'in_next_three_month') {
                                    // $monthStartDay = date('Y-m-01', strtotime($currentStartDate . " + 1 month"));
                                    // $monthEndDay = date('Y-m-t', strtotime($currentEndDate . " + 3 month"));
                                    // $query->orWhereDate($Column['column_name'], '>=', date('Y-m-d'));
                                    // $query->orWhereDate($Column['column_name'], '<=', $monthEndDay);

                                    $NextStartDate = Carbon::parse(date('Y-m-d', strtotime(date('Y-m-d'))));
                                    $nextThreeMonth = $NextStartDate->addMonthNoOverflow(3)->endOfMonth();
                                    $query->whereDate($Column['column_name'], '<=', $nextThreeMonth);
                                } else {
                                    $date_filter_value = explode(',', $objDateFilter['value']);
                                    $from_date_filter = $date_filter_value[0];
                                    $from_date_filter_value = date('Y-m-d', strtotime($from_date_filter));

                                    $to_date_filter = $date_filter_value[1];
                                    $to_date_filter_value = date('Y-m-d', strtotime($to_date_filter));

                                    $query->whereBetween(DB::raw('DATE(' . $Column['column_name'] . ')'), [$from_date_filter_value, $to_date_filter_value]);
                                }
                            } elseif ($Column['value_type'] == 'select') {
                                if ($Column['code'] == 'lead_miss_data') {
                                    $missDataValue = getLeadFilterMissFilterValue()[$Filter_Value];
                                    $query->orWhere($missDataValue['column_name'], $missDataValue['value']);
                                } else {
                                    $query->orWhere($Column['column_name'], $Filter_Value);
                                }
                            } elseif ($Column['value_type'] == 'reward_select') {
                                if ($Filter_Value == 1) {
                                    $query->leftJoin('lead_files', 'lead_files.lead_id', '=', 'leads.id');
                                    $query->where('lead_files.hod_approved', 0);
                                    $query->where('lead_files.status', 100);
                                } elseif ($Filter_Value == 2) {
                                    $query->leftJoin('lead_files', 'lead_files.lead_id', '=', 'leads.id');
                                    $query->Where('lead_files.hod_approved', 1);
                                }
                            } else {
                                $query->orWhere($Column['column_name'], 'like', '%' . $Filter_Value . '%');
                            }
                        } elseif ($Condtion['code'] == 'is_not') {
                            if ($Column['value_type'] == 'leads_source') {
                                $query->whereIn('lead_sources.source', $source_type);
                            } elseif ($Column['value_type'] == 'date') {
                                // $query->orWhereDate($Column['column_name'], '!=', $date_filter_value);

                                $objDateFilter = $lstDateFilter[$Filter_Value];

                                $currentStartDate = date('Y-m-d', strtotime(date('Y-m-d')));
                                $currentEndDate = date('Y-m-d', strtotime(date('Y-m-d')));

                                if ($objDateFilter['code'] == 'all_closing') {
                                    $query->orwhere($Column['column_name'], '!=', null);
                                } elseif ($objDateFilter['code'] == 'in_this_week') {
                                    $currentWeekDay = date('w', strtotime($currentStartDate));
                                    $weekStartDate = date('Y-m-d', strtotime($currentStartDate . ' -' . ($currentWeekDay - 1) . ' days'));
                                    $weekEndDate = date('Y-m-d', strtotime($currentEndDate . ' +' . (7 - $currentWeekDay) . ' days'));
                                    $query->orWhereDate($Column['column_name'], '<=', $weekEndDate);
                                } elseif ($objDateFilter['code'] == 'in_this_month') {
                                    $monthStartDay = date('Y-m-01', strtotime($currentStartDate));
                                    $monthEndDay = date('Y-m-t', strtotime($currentEndDate));
                                    $query->orWhereDate($Column['column_name'], '<=', $monthEndDay);
                                } elseif ($objDateFilter['code'] == 'in_next_month') {
                                    // $currentStartDate = date('Y-m-d', strtotime($currentStartDate . " +5 hours"));
                                    // $currentStartDate = date('Y-m-d', strtotime($currentStartDate . " +30 minutes"));
                                    // $monthStartDay = date('Y-m-01', strtotime($currentStartDate . " + 1 month"));
                                    // $monthEndDay = date('Y-m-t', strtotime($monthStartDay));
                                    // $query->orWhereDate($Column['column_name'], '<', $monthEndDay);

                                    $NextStartDate = Carbon::parse(date('Y-m-d', strtotime(date('Y-m-d'))));
                                    $nextMonth = $NextStartDate->addMonthNoOverflow()->endOfMonth();
                                    $query->whereDate($Column['column_name'], '<=', $nextMonth);
                                } elseif ($objDateFilter['code'] == 'in_next_two_month') {
                                    // $monthStartDay = date('Y-m-01', strtotime($currentStartDate . " + 1 month"));
                                    // $monthEndDay = date('Y-m-t', strtotime($currentEndDate . " + 2 month"));
                                    // $query->orWhereDate($Column['column_name'], '<=', $monthEndDay);

                                    $NextStartDate = Carbon::parse(date('Y-m-d', strtotime(date('Y-m-d'))));
                                    $nextTwoMonth = $NextStartDate->addMonthNoOverflow(2)->endOfMonth();
                                    $query->whereDate($Column['column_name'], '<=', $nextTwoMonth);
                                } elseif ($objDateFilter['code'] == 'in_next_three_month') {
                                    // $monthStartDay = date('Y-m-01', strtotime($currentStartDate . " + 1 month"));
                                    // $monthEndDay = date('Y-m-t', strtotime($currentEndDate . " + 3 month"));
                                    // $query->orWhereDate($Column['column_name'], '>=', date('Y-m-d'));
                                    // $query->orWhereDate($Column['column_name'], '<=', $monthEndDay);

                                    $NextStartDate = Carbon::parse(date('Y-m-d', strtotime(date('Y-m-d'))));
                                    $nextThreeMonth = $NextStartDate->addMonthNoOverflow(3)->endOfMonth();
                                    $query->whereDate($Column['column_name'], '<=', $nextThreeMonth);
                                } else {
                                    $date_filter_value = explode(',', $objDateFilter['value']);
                                    $from_date_filter = $date_filter_value[0];
                                    $from_date_filter_value = date('Y-m-d', strtotime($from_date_filter));

                                    $to_date_filter = $date_filter_value[1];
                                    $to_date_filter_value = date('Y-m-d', strtotime($to_date_filter));

                                    $query->whereNotBetween(DB::raw('DATE(' . $Column['column_name'] . ')'), [$from_date_filter_value, $to_date_filter_value]);
                                }
                            } elseif ($Column['value_type'] == 'select') {
                                if ($Column['code'] == 'lead_miss_data') {
                                    $missDataValue = getLeadFilterMissFilterValue()[$Filter_Value];
                                    $query->orWhere($missDataValue['column_name'], '<>', $missDataValue['value']);
                                } else {
                                    $query->orWhere($Column['column_name'], '!=', $Filter_Value);
                                }
                            } else {
                                $query->orWhere($Column['column_name'], 'not like', '%' . $Filter_Value . '%');
                            }
                        } elseif ($Condtion['code'] == 'contains') {
                            if ($Column['value_type'] == 'leads_source') {
                                $query->whereIn('lead_sources.source', $source_type);
                            }
                            if ($Column['value_type'] == 'select') {
                                if ($Column['code'] == 'leads_source') {
                                } elseif ($Column['code'] == 'lead_miss_data') {
                                    foreach ($Filter_Value as $mis_key => $mis_value) {
                                        $missDataValue = getLeadFilterMissFilterValue()[$mis_value];
                                        $query->orWhere($missDataValue['column_name'], $missDataValue['value']);
                                    }
                                } else {
                                    $query->orWhere($Column['column_name'], $Filter_Value);
                                }
                            } else {
                                $Filter_Value = explode(',', $Filter_Value);
                                $query->orWhereIn($Column['column_name'], $Filter_Value);
                            }
                        } elseif ($Condtion['code'] == 'not_contains') {
                            if ($Column['value_type'] == 'leads_source') {
                                $query->whereIn('lead_sources.source', $source_type);
                            }
                            if ($Column['value_type'] == 'select') {
                                if ($Column['code'] == 'lead_miss_data') {
                                    foreach ($Filter_Value as $mis_key => $mis_value) {
                                        $missDataValue = getLeadFilterMissFilterValue()[$mis_value];
                                        $query->orWhere($missDataValue['column_name'], '<>', $missDataValue['value']);
                                    }
                                } else {
                                    $query->orWhereNotIn($Column['column_name'], $Filter_Value);
                                }
                            } else {
                                $Filter_Value = explode(',', $Filter_Value);
                                $query->orWhereNotIn($Column['column_name'], $Filter_Value);
                            }
                        } elseif ($Condtion['code'] == 'between') {
                            if ($Column['value_type'] == 'date') {
                                $date_filter_value = explode(',', $Filter_Value);
                                $from_date_filter = $date_filter_value[0];
                                $from_date_filter_value = date('Y-m-d', strtotime($from_date_filter));

                                $to_date_filter = $date_filter_value[1];
                                $to_date_filter_value = date('Y-m-d', strtotime($to_date_filter));

                                $query->orWhereBetween(DB::raw('DATE(' . $Column['column_name'] . ')'), [$from_date_filter_value, $to_date_filter_value]);
                            }
                        }
                    }
                });
            }
        }

        if (isSalePerson() == 1) {
            $query->whereIn('leads.assigned_to', getChildSalePersonsIds(Auth::user()->id));
        }

        if (isChannelPartner(Auth::user()->type) != 0) {
            $query->where('lead_sources.source', Auth::user()->id);
        }

        if (isArchitect() == 1) {
            $query->where(function ($query) {
                $query->orwhere('leads.architect', Auth::user()->id);
                $query->orwhere('lead_sources.source', Auth::user()->id);
            });
        }
        if ($isReception == 1) {
            $query->where('leads.created_by', Auth::user()->id);
        }

        if (isElectrician() == 1) {
            $query->where(function ($query) {
                $query->orwhere('leads.electrician', Auth::user()->id);
                $query->orwhere('lead_sources.source', Auth::user()->id);
            });
        }

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
        $Filter_lead_ids = $query->distinct()->pluck('leads.id');

        $recordsTotal = $Filter_lead_ids->count();
        $recordsFiltered = $recordsTotal;
        // RECORDSTOTAL END

        // RECORDSFILTERED START
        $query = Lead::query()->whereIn('leads.id', $Filter_lead_ids)->get();
        $recordsFiltered = $query->count();
        // RECORDSFILTERED START

        $Lead = Lead::query()->whereIn('leads.id', $Filter_lead_ids)->orderBy('leads.id', 'desc');
        $Lead_data_total = $Lead->count();
        $Lead->limit($request->length);
        $Lead->offset($request->start);

        $Lead_data = $Lead->get();

        if ($Lead->count() >= 1) {
            $FirstPageLeadId = $Lead_data[0]['id'];
        } else {
            $FirstPageLeadId = 0;
        }
        $data = json_decode(json_encode($Lead_data), true);

        $viewData = [];
        $LeadStatus = getLeadStatus();
        foreach ($data as $key => $value) {
            $view = '';
            $view = '<li class="lead_li" id="lead_' . $value['id'] . '" onclick="getDataDetail(' . $value['id'] . ')" style="list-style: none;">';
            $view .= '<a href="javascript: void(0);">';
            $view .= '<div class="d-flex">';
            $view .= '<div class="flex-grow-1 overflow-hidden">';
            if ($value['inquiry_id'] == '' || $value['inquiry_id'] == null) {
                if ($value['is_deal'] == 0) {
                    $view .= '<h5 class="text-truncate font-size-14 mb-1">#L' . highlightString($value['id'], $search_value) . '</h5>';
                } elseif ($value['is_deal'] == 1) {
                    $view .= '<h5 class="text-truncate font-size-14 mb-1">#D' . highlightString($value['id'], $search_value) . '</h5>';
                }
            } else {
                if ($value['is_deal'] == 0) {
                    $view .= '<h5 class="text-truncate font-size-14 mb-1">#L' . highlightString($value['id'], $search_value) . '-' . highlightString($value['inquiry_id'], $search_value) . '</h5>';
                } elseif ($value['is_deal'] == 1) {
                    $view .= '<h5 class="text-truncate font-size-14 mb-1">#D' . highlightString($value['id'], $search_value) . '-' . highlightString($value['inquiry_id'], $search_value) . '</h5>';
                }
            }
            $view .= '<p class="text-truncate mb-0">' . highlightString(ucwords(strtolower($value['first_name'])), $search_value) . '</p>';
            $view .= '</div>';
            $view .= '<div class="d-flex justify-content-end font-size-16">';
            // $view .= '<span class="badge badge-pill badge badge-soft-info font-size-11" style="height: fit-content;" id="' . $value['id'] . '_lead_list_status">' . $value['status'] . '</span>';
            $view .= '<span class="badge badge-pill badge badge-soft-info font-size-11" style="height: fit-content;" id="' . $value['id'] . '_lead_list_status">' . $LeadStatus[$value['status']]['name'] . '</span>';

            $view .= '</div>';
            $view .= '</div>';
            $view .= '</a>';
            $view .= '</li>';

            $viewData[$key] = [];
            $viewData[$key]['view'] = $view;
        }

        $jsonData = [
            'draw' => intval($request['draw']),
            'recordsTotal' => intval($recordsTotal),
            'recordsFiltered' => intval($recordsFiltered),
            'data' => $viewData,
            'count' => $Lead_data_total,
            'FirstPageLeadId' => $FirstPageLeadId,
            'Filter_lead_ids' => $Filter_lead_ids,
        ];
        return $jsonData;
    }
    function getLeadAmountSummary(Request $request)
    {
        $isSalePerson = isSalePerson();
        $isReception = isReception();
        $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
        $source_type = getInquirySourceTypes();
        if ($isSalePerson == 1) {
            $parentSalesUsers = getParentSalePersonsIds(Auth::user()->id);
            $childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
        }

        $selectColumns = ['leads.id', 'leads.inquiry_id', 'leads.first_name', 'leads.last_name', 'leads.phone_number', 'leads.status', 'leads.is_deal', 'created.first_name as lead_owner_name'];

        $arr_where_clause = [];
        $arr_or_clause = [];

        if ($request->isAdvanceFilter == 1) {
            foreach (json_decode($request->AdvanceData, true) as $key => $filt_value) {
                $filter_value = '';
                $source_type = '0';
                if ($filt_value['clause'] == null || $filt_value['clause'] == '') {
                    $response = errorRes('Please Select Clause');
                    return response()->json($response)->header('Content-Type', 'application/json');
                } elseif ($filt_value['column'] == null || $filt_value['column'] == '') {
                    $response = errorRes('Please Select column');
                    return response()->json($response)->header('Content-Type', 'application/json');
                } elseif ($filt_value['condtion'] == null || $filt_value['condtion'] == '') {
                    $response = errorRes('Please Select condtion');
                    return response()->json($response)->header('Content-Type', 'application/json');
                } else {
                    $column = getFilterColumnCRM()[$filt_value['column']];
                    $condtion = getFilterCondtionCRM()[$filt_value['condtion']];
                    if ($column['value_type'] == 'text') {
                        if ($filt_value['value_text'] == null || $filt_value['value_text'] == '') {
                            $response = errorRes('Please enter value');
                            return response()->json($response)->header('Content-Type', 'application/json');
                        } else {
                            $filter_value = $filt_value['value_text'];
                        }
                    } elseif ($column['value_type'] == 'select' && $condtion['value_type'] == 'single_select') {
                        if ($column['code'] == 'leads_source' && ($filt_value['value_source_type'] == null || $filt_value['value_source_type'] == '')) {
                            $response = errorRes('Please select value');
                            return response()->json($response)->header('Content-Type', 'application/json');
                        } else {
                            $source_type = $filt_value['value_source_type'];
                        }
                        if ($column['code'] == 'leads_source') {
                            $source_type = explode('-', $source_type);
                            if ($source_type[0] == 'user') {
                                $filter_value = $filt_value['value_select'];
                            } elseif ($source_type[0] == 'master') {
                                $filter_value = $filt_value['value_select'];
                            } elseif ($source_type[0] == 'exhibition') {
                                $filter_value = $filt_value['value_select'];
                            } else {
                                $filter_value = $filt_value['value_text'];
                            }
                        } else {
                            if ($filt_value['value_select'] == null || $filt_value['value_select'] == '') {
                                $response = errorRes('Please select value');
                                return response()->json($response)->header('Content-Type', 'application/json');
                            } else {
                                $filter_value = $filt_value['value_select'];
                            }
                        }
                    } elseif ($column['value_type'] == 'reward_select') {
                        if ($filt_value['value_select'] == null || $filt_value['value_select'] == '') {
                            $response = errorRes('Please select value');
                            return response()->json($response)->header('Content-Type', 'application/json');
                        } else {
                            $filter_value = $filt_value['value_select'];
                        }
                    } elseif ($column['value_type'] == 'select' && $condtion['value_type'] == 'multi_select') {
                        if ($column['code'] == 'leads_source' && ($filt_value['value_source_type'] == null || $filt_value['value_source_type'] == '')) {
                            $response = errorRes('Please select value');
                            return response()->json($response)->header('Content-Type', 'application/json');
                        } else {
                            $source_type = $filt_value['value_source_type'];
                        }
                        if (!isset($filt_value['value_multi_select']) && empty($filt_value['value_multi_select'])) {
                            $response = errorRes('Please select value');
                            return response()->json($response)->header('Content-Type', 'application/json');
                        } else {
                            $filter_value = $filt_value['value_multi_select'];
                        }
                    } elseif ($column['value_type'] == 'date' && $condtion['value_type'] == 'single_select') {
                        if ($filt_value['value_date'] == null || $filt_value['value_date'] == '') {
                            $response = errorRes('Please enter date');
                            return response()->json($response)->header('Content-Type', 'application/json');
                        } else {
                            $filter_value = $filt_value['value_date'];
                        }
                    } elseif ($column['value_type'] == 'date' && $condtion['value_type'] == 'between') {
                        if (($filt_value['value_from_date'] == null || $filt_value['value_from_date'] == '') && ($filt_value['value_to_date'] == null || $filt_value['value_to_date'] == '')) {
                            $response = errorRes('Please enter from to date');
                            return response()->json($response)->header('Content-Type', 'application/json');
                        } else {
                            $filter_value = $filt_value['value_from_date'] . ',' . $filt_value['value_to_date'];
                        }
                    }

                    if ($filt_value['clause'] != 0) {
                        $clause = getFilterClauseCRM()[$filt_value['clause']];
                    }

                    if ($filt_value['clause'] == 0) {
                        $newdata['clause'] = 0;
                        $newdata['column'] = $column['id'];
                        $newdata['condtion'] = $condtion['id'];
                        $newdata['value'] = $filter_value;
                        $newdata['source_type'] = $source_type;

                        array_push($arr_where_clause, $newdata);
                    } elseif ($clause['clause'] == 'where') {
                        $newdata['clause'] = $clause['id'];
                        $newdata['column'] = $column['id'];
                        $newdata['condtion'] = $condtion['id'];
                        $newdata['value'] = $filter_value;
                        $newdata['source_type'] = $source_type;

                        array_push($arr_where_clause, $newdata);
                    } elseif ($clause['clause'] == 'orwhere') {
                        $newdata['clause'] = $clause['id'];
                        $newdata['column'] = $column['id'];
                        $newdata['condtion'] = $condtion['id'];
                        $newdata['value'] = $filter_value;
                        $newdata['source_type'] = $source_type;

                        array_push($arr_or_clause, $newdata);
                    }
                }
            }
        }

        $Lead = Lead::query();
        $Lead->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
        $Lead->leftJoin('users as lead_owner', 'lead_owner.id', '=', 'leads.assigned_to');
        $Lead->leftJoin('users as created', 'created.id', '=', 'leads.created_by');
        $Lead->leftJoin('wltrn_quotation', 'wltrn_quotation.inquiry_id', '=', 'leads.id');
        $Lead->leftJoin('lead_files', 'lead_files.lead_id', '=', 'leads.id');
        $Lead->select($selectColumns);

        if (isArchitect() == 1 || isElectrician() == 1) {
            $Lead->whereIn('leads.is_deal', [0, 1]);
        } else {
            if ($request->is_deal == 0) {
                $Lead->where('leads.is_deal', 0);
            } elseif ($request->is_deal == 1) {
                $Lead->where('leads.is_deal', 1);
            }
        }

        if ($isSalePerson == 1) {
            $Lead->whereIn('leads.assigned_to', $childSalePersonsIds);
        }

        if (isChannelPartner(Auth::user()->type) != 0) {
            $Lead->where('lead_sources.source', Auth::user()->id);
        }

        if ($isReception == 1) {
            $Lead->where('leads.created_by', Auth::user()->id);
        }

        if (isArchitect() == 1) {
            $Lead->where(function ($query) {
                $query->orwhere('leads.architect', Auth::user()->id);
                $query->orwhere('lead_sources.source', Auth::user()->id);
            });
        }

        if (isElectrician() == 1) {
            $Lead->where(function ($query) {
                $query->orwhere('leads.electrician', Auth::user()->id);
                $query->orwhere('lead_sources.source', Auth::user()->id);
            });
        }

        if (isset($request->status)) {
            if ($request->status != 0) {
                $Lead->where('leads.status', $request->status);
            }
        }

        $Lead->orderBy('leads.id', 'DESC');

        if ($request->isAdvanceFilter == 1) {
            foreach ($arr_where_clause as $wherekey => $objwhere) {
                $Column = getFilterColumnCRM()[$objwhere['column']];
                $Condtion = getFilterCondtionCRM()[$objwhere['condtion']];
                $lstDateFilter = getDateFilterValue();
                $Filter_Value = $objwhere['value'];
                $source_type = $objwhere['source_type'];

                if ($Condtion['code'] == 'is') {
                    if ($Column['value_type'] == 'leads_source') {
                        $Lead->whereIn('lead_sources.source', $source_type);
                    } elseif ($Column['value_type'] == 'date') {
                        // $date_filter_value = date('Y-m-d', strtotime($Filter_Value));
                        // $Lead->whereDate($Column['column_name'], '=', $date_filter_value);
                        $objDateFilter = $lstDateFilter[$Filter_Value];

                        $currentStartDate = date('Y-m-d', strtotime(date('Y-m-d')));
                        $currentEndDate = date('Y-m-d', strtotime(date('Y-m-d')));

                        if ($objDateFilter['code'] == 'all_closing') {
                            $Lead->where($Column['column_name'], '!=', null);
                        } elseif ($objDateFilter['code'] == 'in_this_week') {
                            $currentWeekDay = date('w', strtotime($currentStartDate));
                            $weekStartDate = date('Y-m-d', strtotime($currentStartDate . ' -' . ($currentWeekDay - 1) . ' days'));
                            $weekEndDate = date('Y-m-d', strtotime($currentEndDate . ' +' . (7 - $currentWeekDay) . ' days'));
                            $Lead->whereDate($Column['column_name'], '<=', $weekEndDate);
                        } elseif ($objDateFilter['code'] == 'in_this_month') {
                            $monthStartDay = date('Y-m-01', strtotime($currentStartDate));
                            $monthEndDay = date('Y-m-t', strtotime($currentEndDate));
                            $Lead->whereDate($Column['column_name'], '<=', $monthEndDay);
                        } elseif ($objDateFilter['code'] == 'in_next_month') {
                            // $currentStartDate = date('Y-m-d', strtotime($currentStartDate . " +5 hours"));
                            // $currentStartDate = date('Y-m-d', strtotime($currentStartDate . " +30 minutes"));
                            // $monthStartDay = date('Y-m-01', strtotime($currentStartDate . " + 1 month"));
                            // $monthEndDay = date('Y-m-t', strtotime($monthStartDay));
                            // $Lead->whereDate($Column['column_name'], '<', $monthEndDay);

                            $NextStartDate = Carbon::parse(date('Y-m-d', strtotime(date('Y-m-d'))));
                            $nextMonth = $NextStartDate->addMonthNoOverflow()->endOfMonth();
                            $Lead->whereDate($Column['column_name'], '<=', $nextMonth);
                        } elseif ($objDateFilter['code'] == 'in_next_two_month') {
                            // $monthStartDay = date('Y-m-01', strtotime($currentStartDate . " + 1 month"));
                            // $monthEndDay = date('Y-m-t', strtotime($currentEndDate . " + 2 month"));
                            // $Lead->whereDate($Column['column_name'], '<=', $monthEndDay);

                            $NextStartDate = Carbon::parse(date('Y-m-d', strtotime(date('Y-m-d'))));
                            $nextTwoMonth = $NextStartDate->addMonthNoOverflow(2)->endOfMonth();
                            $Lead->whereDate($Column['column_name'], '<=', $nextTwoMonth);
                        } elseif ($objDateFilter['code'] == 'in_next_three_month') {
                            // $monthStartDay = date('Y-m-01', strtotime($currentStartDate . " + 1 month"));
                            // $monthEndDay = date('Y-m-t', strtotime($currentEndDate . " + 3 month"));
                            // $Lead->whereDate($Column['column_name'], '>=', date('Y-m-d'));
                            // $Lead->whereDate($Column['column_name'], '<=', $monthEndDay);

                            $NextStartDate = Carbon::parse(date('Y-m-d', strtotime(date('Y-m-d'))));
                            $nextThreeMonth = $NextStartDate->addMonthNoOverflow(3)->endOfMonth();
                            $Lead->whereDate($Column['column_name'], '<=', $nextThreeMonth);
                        }
                        // if ($objDateFilter['code'] == "today") {
                        //     $date_filter_value = date('Y-m-d', strtotime(date('Y-m-d')));
                        //     $Lead->whereDate($Column['column_name'], '=', $date_filter_value);
                        //     // $Lead->whereDate($Column['column_name'], '=', date('Y-m-d'));
                        // }
                        else {
                            $date_filter_value = explode(',', $objDateFilter['value']);
                            $from_date_filter = $date_filter_value[0];
                            $from_date_filter_value = date('Y-m-d', strtotime($from_date_filter));

                            $to_date_filter = $date_filter_value[1];
                            $to_date_filter_value = date('Y-m-d', strtotime($to_date_filter));

                            $Lead->whereBetween(DB::raw('DATE(' . $Column['column_name'] . ')'), [$from_date_filter_value, $to_date_filter_value]);
                        }
                    } elseif ($Column['value_type'] == 'select') {
                        if ($Column['code'] == 'lead_miss_data') {
                            $missDataValue = getLeadFilterMissFilterValue()[$Filter_Value];
                            $Lead->where($missDataValue['column_name'], $missDataValue['value']);
                        } else {
                            $Lead->where($Column['column_name'], $Filter_Value);
                        }
                    } elseif ($Column['value_type'] == 'reward_select') {
                        if ($Filter_Value == 1) {
                            $Lead->leftJoin('lead_files', 'lead_files.lead_id', '=', 'leads.id');
                            $Lead->where('lead_files.hod_approved', 0);
                            $Lead->where('lead_files.status', 100);
                        } elseif ($Filter_Value == 2) {
                            $Lead->leftJoin('lead_files', 'lead_files.lead_id', '=', 'leads.id');
                            $Lead->Where('lead_files.hod_approved', 1);
                        }
                    } else {
                        $Lead->where($Column['column_name'], 'like', '%' . $Filter_Value . '%');
                    }
                } elseif ($Condtion['code'] == 'is_not') {
                    if ($Column['value_type'] == 'leads_source') {
                        $Lead->whereNotIn('lead_sources.source', $source_type);
                    } elseif ($Column['value_type'] == 'date') {
                        $objDateFilter = $lstDateFilter[$Filter_Value];

                        $currentStartDate = date('Y-m-d', strtotime(date('Y-m-d')));
                        $currentEndDate = date('Y-m-d', strtotime(date('Y-m-d')));

                        if ($objDateFilter['code'] == 'all_closing') {
                            $Lead->where($Column['column_name'], '!=', null);
                        } elseif ($objDateFilter['code'] == 'in_this_week') {
                            $currentWeekDay = date('w', strtotime($currentStartDate));
                            $weekStartDate = date('Y-m-d', strtotime($currentStartDate . ' -' . ($currentWeekDay - 1) . ' days'));
                            $weekEndDate = date('Y-m-d', strtotime($currentEndDate . ' +' . (7 - $currentWeekDay) . ' days'));
                            $Lead->whereDate($Column['column_name'], '<=', $weekEndDate);
                        } elseif ($objDateFilter['code'] == 'in_this_month') {
                            $monthStartDay = date('Y-m-01', strtotime($currentStartDate));
                            $monthEndDay = date('Y-m-t', strtotime($currentEndDate));
                            $Lead->whereDate($Column['column_name'], '<=', $monthEndDay);
                        } elseif ($objDateFilter['code'] == 'in_next_month') {
                            // $currentStartDate = date('Y-m-d', strtotime($currentStartDate . " +5 hours"));
                            // $currentStartDate = date('Y-m-d', strtotime($currentStartDate . " +30 minutes"));
                            $NextStartDate = Carbon::parse(date('Y-m-d', strtotime(date('Y-m-d'))));
                            $nextMonth = $NextStartDate->addMonthNoOverflow()->endOfMonth();
                            $Lead->whereDate($Column['column_name'], '<=', $nextMonth);
                        } elseif ($objDateFilter['code'] == 'in_next_two_month') {
                            // $monthStartDay = date('Y-m-01', strtotime($currentStartDate . " + 1 month"));
                            // $monthEndDay = date('Y-m-t', strtotime($currentEndDate . " + 2 month"));
                            // $Lead->whereDate($Column['column_name'], '<=', $monthEndDay);

                            $NextStartDate = Carbon::parse(date('Y-m-d', strtotime(date('Y-m-d'))));
                            $nextTwoMonth = $NextStartDate->addMonthNoOverflow(2)->endOfMonth();
                            $Lead->whereDate($Column['column_name'], '<=', $nextTwoMonth);
                        } elseif ($objDateFilter['code'] == 'in_next_three_month') {
                            // $monthStartDay = date('Y-m-01', strtotime($currentStartDate . " + 1 month"));
                            // $monthEndDay = date('Y-m-t', strtotime($currentEndDate . " + 3 month"));
                            // $Lead->whereDate($Column['column_name'], '>=', date('Y-m-d'));
                            // $Lead->whereDate($Column['column_name'], '<=', $monthEndDay);

                            $NextStartDate = Carbon::parse(date('Y-m-d', strtotime(date('Y-m-d'))));
                            $nextThreeMonth = $NextStartDate->addMonthNoOverflow(3)->endOfMonth();
                            $Lead->whereDate($Column['column_name'], '<=', $nextThreeMonth);
                        }

                        // if ($objDateFilter['code'] == "today") {
                        //     $date_filter_value = date('Y-m-d', strtotime(date('Y-m-d')));
                        //     $Lead->whereDate($Column['column_name'], '!=', $date_filter_value);
                        //     // $Lead->whereDate($Column['column_name'], '!=', date('Y-m-d'));
                        // }
                        else {
                            $date_filter_value = explode(',', $objDateFilter['value']);
                            $from_date_filter = $date_filter_value[0];
                            $from_date_filter_value = date('Y-m-d', strtotime($from_date_filter));

                            $to_date_filter = $date_filter_value[1];
                            $to_date_filter_value = date('Y-m-d', strtotime($to_date_filter));

                            $Lead->whereNotBetween(DB::raw('DATE(' . $Column['column_name'] . ')'), [$from_date_filter_value, $to_date_filter_value]);
                        }
                    } elseif ($Column['value_type'] == 'select') {
                        if ($Column['code'] == 'lead_miss_data') {
                            $missDataValue = getLeadFilterMissFilterValue()[$Filter_Value];
                            $Lead->where($missDataValue['column_name'], '<>', $missDataValue['value']);
                        } else {
                            $Lead->whereNotNull($Column['column_name']);
                            $Lead->where($Column['column_name'], '!=', $Filter_Value);
                        }
                    } else {
                        $Lead->where($Column['column_name'], 'not like', '%' . $Filter_Value . '%');
                    }
                } elseif ($Condtion['code'] == 'contains') {
                    if ($Column['value_type'] == 'leads_source') {
                        $Lead->whereIn('lead_sources.source', $source_type);
                    }
                    if ($Column['value_type'] == 'select') {
                        if ($Column['code'] == 'lead_miss_data') {
                            foreach ($Filter_Value as $mis_key => $mis_value) {
                                $missDataValue = getLeadFilterMissFilterValue()[$mis_value];
                                $Lead->where($missDataValue['column_name'], $missDataValue['value']);
                            }
                        } else {
                            $Lead->whereIn($Column['column_name'], $Filter_Value);
                        }
                    } else {
                        $Filter_Value = explode(',', $Filter_Value);
                        $Lead->whereIn($Column['column_name'], $Filter_Value);
                    }
                } elseif ($Condtion['code'] == 'not_contains') {
                    if ($Column['value_type'] == 'leads_source') {
                        $Lead->whereIn('lead_sources.source', $source_type);
                    }
                    if ($Column['value_type'] == 'select') {
                        if ($Column['code'] == 'lead_miss_data') {
                            foreach ($Filter_Value as $mis_key => $mis_value) {
                                $missDataValue = getLeadFilterMissFilterValue()[$mis_value];
                                $Lead->where($missDataValue['column_name'], '<>', $missDataValue['value']);
                            }
                        } else {
                            $Lead->whereNotIn($Column['column_name'], $Filter_Value);
                        }
                    } else {
                        $Filter_Value = explode(',', $Filter_Value);
                        $Lead->whereNotIn($Column['column_name'], $Filter_Value);
                    }
                } elseif ($Condtion['code'] == 'between') {
                    if ($Column['value_type'] == 'date') {
                        $date_filter_value = explode(',', $Filter_Value);
                        $from_date_filter = $date_filter_value[0];
                        $from_date_filter_value = date('Y-m-d', strtotime($from_date_filter));

                        $to_date_filter = $date_filter_value[1];
                        $to_date_filter_value = date('Y-m-d', strtotime($to_date_filter));

                        $Lead->whereBetween(DB::raw('DATE(' . $Column['column_name'] . ')'), [$from_date_filter_value, $to_date_filter_value]);
                    }
                }
            }

            if (count($arr_or_clause) > 0) {
                $Lead->orWhere(function ($query) use ($arr_or_clause) {
                    foreach ($arr_or_clause as $orkey => $objor) {
                        $Column = getFilterColumnCRM()[$objor['column']];
                        $Condtion = getFilterCondtionCRM()[$objor['condtion']];
                        $lstDateFilter = getDateFilterValue();
                        $Filter_Value = $objor['value'];
                        $source_type = $objor['source_type'];

                        if ($Condtion['code'] == 'is') {
                            if ($Column['value_type'] == 'leads_source') {
                                $query->whereIn('lead_sources.source', $source_type);
                            } elseif ($Column['value_type'] == 'date') {
                                // $date_filter_value = date('Y-m-d', strtotime($Filter_Value));
                                // $query->orWhereDate($Column['column_name'], '=', $date_filter_value);

                                $objDateFilter = $lstDateFilter[$Filter_Value];

                                $currentStartDate = date('Y-m-d', strtotime(date('Y-m-d')));
                                $currentEndDate = date('Y-m-d', strtotime(date('Y-m-d')));

                                if ($objDateFilter['code'] == 'all_closing') {
                                    $query->orwhere($Column['column_name'], '!=', null);
                                } elseif ($objDateFilter['code'] == 'in_this_week') {
                                    $currentWeekDay = date('w', strtotime($currentStartDate));
                                    $weekStartDate = date('Y-m-d', strtotime($currentStartDate . ' -' . ($currentWeekDay - 1) . ' days'));
                                    $weekEndDate = date('Y-m-d', strtotime($currentEndDate . ' +' . (7 - $currentWeekDay) . ' days'));
                                    $query->orWhereDate($Column['column_name'], '<=', $weekEndDate);
                                } elseif ($objDateFilter['code'] == 'in_this_month') {
                                    $monthStartDay = date('Y-m-01', strtotime($currentStartDate));
                                    $monthEndDay = date('Y-m-t', strtotime($currentEndDate));
                                    $query->orWhereDate($Column['column_name'], '<=', $monthEndDay);
                                } elseif ($objDateFilter['code'] == 'in_next_month') {
                                    // $currentStartDate = date('Y-m-d', strtotime($currentStartDate . " +5 hours"));
                                    // $currentStartDate = date('Y-m-d', strtotime($currentStartDate . " +30 minutes"));
                                    $NextStartDate = Carbon::parse(date('Y-m-d', strtotime(date('Y-m-d'))));
                                    $nextMonth = $NextStartDate->addMonthNoOverflow()->endOfMonth();
                                    $query->whereDate($Column['column_name'], '<=', $nextMonth);
                                } elseif ($objDateFilter['code'] == 'in_next_two_month') {
                                    // $monthStartDay = date('Y-m-01', strtotime($currentStartDate . " + 1 month"));
                                    // $monthEndDay = date('Y-m-t', strtotime($currentEndDate . " + 2 month"));
                                    // $query->orWhereDate($Column['column_name'], '<=', $monthEndDay);

                                    $NextStartDate = Carbon::parse(date('Y-m-d', strtotime(date('Y-m-d'))));
                                    $nextTwoMonth = $NextStartDate->addMonthNoOverflow(2)->endOfMonth();
                                    $query->whereDate($Column['column_name'], '<=', $nextTwoMonth);
                                } elseif ($objDateFilter['code'] == 'in_next_three_month') {
                                    // $monthStartDay = date('Y-m-01', strtotime($currentStartDate . " + 1 month"));
                                    // $monthEndDay = date('Y-m-t', strtotime($currentEndDate . " + 3 month"));
                                    // $query->orWhereDate($Column['column_name'], '>=', date('Y-m-d'));
                                    // $query->orWhereDate($Column['column_name'], '<=', $monthEndDay);

                                    $NextStartDate = Carbon::parse(date('Y-m-d', strtotime(date('Y-m-d'))));
                                    $nextThreeMonth = $NextStartDate->addMonthNoOverflow(3)->endOfMonth();
                                    $query->whereDate($Column['column_name'], '<=', $nextThreeMonth);
                                } else {
                                    $date_filter_value = explode(',', $objDateFilter['value']);
                                    $from_date_filter = $date_filter_value[0];
                                    $from_date_filter_value = date('Y-m-d', strtotime($from_date_filter));

                                    $to_date_filter = $date_filter_value[1];
                                    $to_date_filter_value = date('Y-m-d', strtotime($to_date_filter));

                                    $query->whereBetween(DB::raw('DATE(' . $Column['column_name'] . ')'), [$from_date_filter_value, $to_date_filter_value]);
                                }
                            } elseif ($Column['value_type'] == 'select') {
                                if ($Column['code'] == 'lead_miss_data') {
                                    $missDataValue = getLeadFilterMissFilterValue()[$Filter_Value];
                                    $query->orWhere($missDataValue['column_name'], $missDataValue['value']);
                                } else {
                                    $query->orWhere($Column['column_name'], $Filter_Value);
                                }
                            } elseif ($Column['value_type'] == 'reward_select') {
                                if ($Filter_Value == 1) {
                                    $query->leftJoin('lead_files', 'lead_files.lead_id', '=', 'leads.id');
                                    $query->where('lead_files.hod_approved', 0);
                                    $query->where('lead_files.status', 100);
                                } elseif ($Filter_Value == 2) {
                                    $query->leftJoin('lead_files', 'lead_files.lead_id', '=', 'leads.id');
                                    $query->Where('lead_files.hod_approved', 1);
                                }
                            } else {
                                $query->orWhere($Column['column_name'], 'like', '%' . $Filter_Value . '%');
                            }
                        } elseif ($Condtion['code'] == 'is_not') {
                            if ($Column['value_type'] == 'leads_source') {
                                $query->whereIn('lead_sources.source', $source_type);
                            } elseif ($Column['value_type'] == 'date') {
                                // $query->orWhereDate($Column['column_name'], '!=', $date_filter_value);

                                $objDateFilter = $lstDateFilter[$Filter_Value];

                                $currentStartDate = date('Y-m-d', strtotime(date('Y-m-d')));
                                $currentEndDate = date('Y-m-d', strtotime(date('Y-m-d')));

                                if ($objDateFilter['code'] == 'all_closing') {
                                    $query->orwhere($Column['column_name'], '!=', null);
                                } elseif ($objDateFilter['code'] == 'in_this_week') {
                                    $currentWeekDay = date('w', strtotime($currentStartDate));
                                    $weekStartDate = date('Y-m-d', strtotime($currentStartDate . ' -' . ($currentWeekDay - 1) . ' days'));
                                    $weekEndDate = date('Y-m-d', strtotime($currentEndDate . ' +' . (7 - $currentWeekDay) . ' days'));
                                    $query->orWhereDate($Column['column_name'], '<=', $weekEndDate);
                                } elseif ($objDateFilter['code'] == 'in_this_month') {
                                    $monthStartDay = date('Y-m-01', strtotime($currentStartDate));
                                    $monthEndDay = date('Y-m-t', strtotime($currentEndDate));
                                    $query->orWhereDate($Column['column_name'], '<=', $monthEndDay);
                                } elseif ($objDateFilter['code'] == 'in_next_month') {
                                    // $currentStartDate = date('Y-m-d', strtotime($currentStartDate . " +5 hours"));
                                    // $currentStartDate = date('Y-m-d', strtotime($currentStartDate . " +30 minutes"));
                                    $NextStartDate = Carbon::parse(date('Y-m-d', strtotime(date('Y-m-d'))));
                                    $nextMonth = $NextStartDate->addMonthNoOverflow()->endOfMonth();
                                    $query->whereDate($Column['column_name'], '<=', $nextMonth);
                                } elseif ($objDateFilter['code'] == 'in_next_two_month') {
                                    // $monthStartDay = date('Y-m-01', strtotime($currentStartDate . " + 1 month"));
                                    // $monthEndDay = date('Y-m-t', strtotime($currentEndDate . " + 2 month"));
                                    // $query->orWhereDate($Column['column_name'], '<=', $monthEndDay);

                                    $NextStartDate = Carbon::parse(date('Y-m-d', strtotime(date('Y-m-d'))));
                                    $nextTwoMonth = $NextStartDate->addMonthNoOverflow(2)->endOfMonth();
                                    $query->whereDate($Column['column_name'], '<=', $nextTwoMonth);
                                } elseif ($objDateFilter['code'] == 'in_next_three_month') {
                                    // $monthStartDay = date('Y-m-01', strtotime($currentStartDate . " + 1 month"));
                                    // $monthEndDay = date('Y-m-t', strtotime($currentEndDate . " + 3 month"));
                                    // $query->orWhereDate($Column['column_name'], '>=', date('Y-m-d'));
                                    // $query->orWhereDate($Column['column_name'], '<=', $monthEndDay);

                                    $NextStartDate = Carbon::parse(date('Y-m-d', strtotime(date('Y-m-d'))));
                                    $nextThreeMonth = $NextStartDate->addMonthNoOverflow(3)->endOfMonth();
                                    $query->whereDate($Column['column_name'], '<=', $nextThreeMonth);
                                } else {
                                    $date_filter_value = explode(',', $objDateFilter['value']);
                                    $from_date_filter = $date_filter_value[0];
                                    $from_date_filter_value = date('Y-m-d', strtotime($from_date_filter));

                                    $to_date_filter = $date_filter_value[1];
                                    $to_date_filter_value = date('Y-m-d', strtotime($to_date_filter));

                                    $query->whereNotBetween(DB::raw('DATE(' . $Column['column_name'] . ')'), [$from_date_filter_value, $to_date_filter_value]);
                                }
                            } elseif ($Column['value_type'] == 'select') {
                                if ($Column['code'] == 'lead_miss_data') {
                                    $missDataValue = getLeadFilterMissFilterValue()[$Filter_Value];
                                    $query->orWhere($missDataValue['column_name'], '<>', $missDataValue['value']);
                                } else {
                                    $query->orWhere($Column['column_name'], '!=', $Filter_Value);
                                }
                            } else {
                                $query->orWhere($Column['column_name'], 'not like', '%' . $Filter_Value . '%');
                            }
                        } elseif ($Condtion['code'] == 'contains') {
                            if ($Column['value_type'] == 'leads_source') {
                                $query->whereIn('lead_sources.source', $source_type);
                            }
                            if ($Column['value_type'] == 'select') {
                                if ($Column['code'] == 'leads_source') {
                                } elseif ($Column['code'] == 'lead_miss_data') {
                                    foreach ($Filter_Value as $mis_key => $mis_value) {
                                        $missDataValue = getLeadFilterMissFilterValue()[$mis_value];
                                        $query->orWhere($missDataValue['column_name'], $missDataValue['value']);
                                    }
                                } else {
                                    $query->orWhere($Column['column_name'], $Filter_Value);
                                }
                            } else {
                                $Filter_Value = explode(',', $Filter_Value);
                                $query->orWhereIn($Column['column_name'], $Filter_Value);
                            }
                        } elseif ($Condtion['code'] == 'not_contains') {
                            if ($Column['value_type'] == 'leads_source') {
                                $query->whereIn('lead_sources.source', $source_type);
                            }
                            if ($Column['value_type'] == 'select') {
                                if ($Column['code'] == 'lead_miss_data') {
                                    foreach ($Filter_Value as $mis_key => $mis_value) {
                                        $missDataValue = getLeadFilterMissFilterValue()[$mis_value];
                                        $query->orWhere($missDataValue['column_name'], '<>', $missDataValue['value']);
                                    }
                                } else {
                                    $query->orWhereNotIn($Column['column_name'], $Filter_Value);
                                }
                            } else {
                                $Filter_Value = explode(',', $Filter_Value);
                                $query->orWhereNotIn($Column['column_name'], $Filter_Value);
                            }
                        } elseif ($Condtion['code'] == 'between') {
                            if ($Column['value_type'] == 'date') {
                                $date_filter_value = explode(',', $Filter_Value);
                                $from_date_filter = $date_filter_value[0];
                                $from_date_filter_value = date('Y-m-d', strtotime($from_date_filter));

                                $to_date_filter = $date_filter_value[1];
                                $to_date_filter_value = date('Y-m-d', strtotime($to_date_filter));

                                $query->orWhereBetween(DB::raw('DATE(' . $Column['column_name'] . ')'), [$from_date_filter_value, $to_date_filter_value]);
                            }
                        }
                    }
                });
            }
        }

        $Lead_filtered_ids = $Lead->distinct()->pluck('leads.id');

        $total_billing_amount = 0;
        $total_whitelion_amount = 0;
        $total_other_amount = 0;
        $total_amount = 0;

        $Leadamount = Lead::query();
        $Leadamount->selectRaw('SUM(wltrn_quotation.quot_whitelion_amount) AS whitelion_amount');
        $Leadamount->selectRaw('SUM(wltrn_quotation.quot_billing_amount) AS billing_amount');
        $Leadamount->selectRaw('SUM(wltrn_quotation.quot_other_amount) AS other_amount');
        $Leadamount->selectRaw('SUM(wltrn_quotation.quot_total_amount) AS total_amount');
        $Leadamount->leftJoin('wltrn_quotation', 'wltrn_quotation.inquiry_id', '=', 'leads.id');
        $Leadamount->whereIn('leads.id', $Lead_filtered_ids);
        $Leadamount->where('wltrn_quotation.isfinal', 1);
        $Leadamount = $Leadamount->first();

        if ($Leadamount) {
            $total_whitelion_amount = $Leadamount->whitelion_amount;
            $total_billing_amount = $Leadamount->billing_amount;
            $total_other_amount = $Leadamount->other_amount;
            $total_amount = $Leadamount->total_amount;
        }

        $response = successRes('Success');
        $response['data']['whitelion_amt'] = numCommaFormat($total_whitelion_amount);
        $response['data']['billing_amt'] = numCommaFormat($total_billing_amount);
        $response['data']['other_amt'] = numCommaFormat($total_other_amount);
        $response['data']['total_amt'] = numCommaFormat($total_amount);

        return $response;
    }
    function refreshStatus(Request $request)
    {
        $Lead = Lead::find($request->lead_id);

        $data = [];
        $data['lead_status'] = getLeadStatus();
        $data['lead'] = $Lead;
        $data['lead_id'] = $request->lead_id;
        $response = successRes('All Update List');
        $response['view'] = view('crm/lead/detail_tab/detail_status', compact('data'))->render();

        return response()->json($response)->header('Content-Type', 'application/json');
    }
    function getDeatail(Request $request)
    {
        $Lead = Lead::find($request->id);
        $data = [];

        if ($Lead) {
            $Lead = json_encode($Lead);
            $Lead = json_decode($Lead, true);

            $data['lead'] = $Lead;

            $data['lead']['status_label'] = getLeadStatus()[$data['lead']['status']]['name'];
            $data['lead']['suggest_step'] = getLeadNextStatus($data['lead']['status'])['name'];
            $data['lead']['created_at'] = date('d/m/Y g:i A', strtotime($data['lead']['created_at']));
            $data['lead']['updated_at'] = date('d/m/Y g:i A', strtotime($data['lead']['updated_at']));

            $main_source_type = explode('-', $data['lead']['source_type']);

            if ($main_source_type[0] != 'master') {
                $source_type = getLeadSourceTypes();
                foreach ($source_type as $key => $value) {
                    if ($value['type'] == $main_source_type[0] && $value['id'] == $main_source_type[1]) {
                        $data['lead']['source_type_id'] = $data['lead']['source_type'];
                        $data['lead']['source_type'] = $value['lable'];
                        $data['lead']['is_editable'] = $value['is_editable'];
                    }
                }
            } elseif ($main_source_type[0] == 'master') {
                $main_source_type = CRMSettingSourceType::select('id', 'name')
                    ->where('id', $main_source_type[1])
                    ->first();

                if ($main_source_type) {
                    $data['lead']['source_type_id'] = $main_source_type->id;
                    $data['lead']['source_type'] = $main_source_type->name;
                    $data['lead']['is_editable'] = 1;
                }
            }

            if ($main_source_type[0] == 'user') {
                if (isset(getChannelPartners()[$main_source_type[1]]['short_name'])) {
                    $lst_main_source = ChannelPartner::select('user_id AS id', 'firm_name AS text')
                        ->where('user_id', $data['lead']['source'])
                        ->first();
                    if ($lst_main_source) {
                        $main_source = $lst_main_source;
                    } else {
                        $main_source = '';
                    }
                } else {
                    $lst_main_source = User::select('id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"))
                        ->where('id', $data['lead']['source'])
                        ->first();
                    if ($lst_main_source) {
                        $main_source = $lst_main_source;
                    } else {
                        $main_source = '';
                    }
                }
            } elseif ($main_source_type[0] == 'master') {
                $lst_main_source = CRMSettingSource::select('id', 'name')
                    ->where('id', $data['lead']['source'])
                    ->first();
                if ($lst_main_source) {
                    $main_source = $lst_main_source;
                } else {
                    $main_source = '';
                }
            } elseif ($main_source_type[0] == 'exhibition') {
                $Exhibition_data = Exhibition::find($data['lead']['source']);
                $main_source['id'] = $Exhibition_data->id;
                $main_source['text'] = $Exhibition_data->name;
            } else {
                $main_source['id'] = $data['lead']['source'];
                $main_source['text'] = $data['lead']['source'];
            }

            // $source = User::select('users.id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"));
            // $source = $source->where('users.status', 1);
            // $source = $source->where('users.id', $data['lead']['source']);
            // $source = $source->first();
            $data['lead']['source'] = $main_source;

            if ($data['lead']['closing_date_time'] != null) {
                $lead_closing_date_time = $data['lead']['closing_date_time'];
                $lead_closing_date_time = date('d-m-Y', strtotime($lead_closing_date_time));
                $data['lead']['closing_date_time'] = $lead_closing_date_time;
            }

            $assigned_to = User::select('users.id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"), 'users.phone_number');
            // $assigned_to->where('users.status', 1);
            $assigned_to->where('users.id', $data['lead']['assigned_to']);
            // $assigned_to->whereIn('users.type', ['201', '202']);
            $assigned_to = $assigned_to->first();
            if ($assigned_to) {
                $data['lead']['assigned'] = $assigned_to->text;
                $data['lead']['assigned_mobile'] = $assigned_to->phone_number;
            } else {
                $data['lead']['assigned'] = ' - ';
                $data['lead']['assigned_mobile'] = ' - ';
            }

            $architect = User::select('users.id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"), 'users.phone_number');
            // $architect->where('users.status', 1);
            $architect->where('users.id', $data['lead']['architect']);
            // $architect->whereIn('users.type', ['201', '202']);
            $architect = $architect->first();
            if ($architect) {
                // $data['lead']['architect'] = $architect['text'] ."          📞  ". $architect['phone_number'];
                $data['lead']['architect'] = $architect->text;
                $data['lead']['architect_mobile'] = $architect->phone_number;
            } else {
                $data['lead']['architect'] = ' - ';
                $data['lead']['architect_mobile'] = ' - ';
            }

            $created_by = User::select('users.id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"), 'users.phone_number');
            // $created_by->where('users.status', 1);
            $created_by->where('users.id', $data['lead']['created_by']);
            // $created_by->whereIn('users.type', ['201', '202']);
            $created_by = $created_by->first();
            if ($created_by) {
                $data['lead']['created_by'] = $created_by->text;
            } else {
                $data['lead']['created_by'] = ' - ';
            }

            $updated_by = User::select('users.id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"), 'users.phone_number');
            // $updated_by->where('users.status', 1);
            $updated_by->where('users.id', $data['lead']['updated_by']);
            // $updated_by->whereIn('users.type', ['201', '202']);
            $updated_by = $updated_by->first();
            if ($updated_by) {
                $data['lead']['updated_by'] = $updated_by->text;
            } else {
                $data['lead']['updated_by'] = ' - ';
            }

            $electrician = User::select('users.id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"), 'users.phone_number');
            // $electrician->where('users.status', 1);
            $electrician->where('users.id', $data['lead']['electrician']);
            // $electrician->whereIn('users.type', ['301', '302']);
            $electrician = $electrician->first();
            if ($electrician) {
                $data['lead']['electrician'] = $electrician->text;
                $data['lead']['electrician_mobile'] = $electrician->phone_number;
            } else {
                $data['lead']['electrician'] = ' - ';
                $data['lead']['electrician_mobile'] = ' - ';
            }

            $CityList = CityList::select('city_list.id', 'city_list.name as city_list_name', 'state_list.name as state_list_name');
            $CityList->leftJoin('state_list', 'state_list.id', '=', 'city_list.state_id');
            $CityList->where('city_list.id', $data['lead']['city_id']);
            $CityList = $CityList->first();

            if ($CityList) {
                $CityList['text'] = $CityList->city_list_name . ', ' . $CityList->state_list_name . ', India';

                $data['lead']['city'] = $CityList['text'];
            }

            $CityList = CityList::select('city_list.id', 'city_list.name as city_list_name', 'state_list.name as state_list_name');
            $CityList->leftJoin('state_list', 'state_list.id', '=', 'city_list.state_id');
            $CityList->where('city_list.id', $data['lead']['meeting_city_id']);
            $CityList = $CityList->first();

            if ($CityList) {
                $CityList['text'] = $CityList->city_list_name . ', ' . $CityList->state_list_name . ', India';
                $data['lead']['meeting_city'] = $CityList->text;
            }

            if ($data['lead']['site_stage'] != 0) {
                $CRMSettingStageOfSite = CRMSettingStageOfSite::select('id', 'name as text');
                $CRMSettingStageOfSite->where('crm_setting_stage_of_site.id', $data['lead']['site_stage']);
                $CRMSettingStageOfSite = $CRMSettingStageOfSite->first();
                if ($CRMSettingStageOfSite) {
                    $data['lead']['site_stage'] = $CRMSettingStageOfSite;
                } else {
                    $data['lead']['site_stage'] = '';
                }
            } else {
                $data['lead']['site_stage'] = '';
            }

            if ($data['lead']['site_type'] != 0) {
                $CRMSettingSiteType = CRMSettingSiteType::select('id', 'name as text');
                $CRMSettingSiteType->where('crm_setting_site_type.id', $data['lead']['site_type']);
                $CRMSettingSiteType = $CRMSettingSiteType->first();
                if ($CRMSettingSiteType) {
                    $data['lead']['site_type'] = $CRMSettingSiteType;
                } else {
                    $data['lead']['site_type'] = '';
                }
            } else {
                $data['lead']['site_type'] = '';
            }

            if ($data['lead']['bhk'] != 0) {
                $CRMSettingBHK = CRMSettingBHK::select('id', 'name as text');
                $CRMSettingBHK->where('crm_setting_bhk.id', $data['lead']['bhk']);
                $CRMSettingBHK = $CRMSettingBHK->first();
                if ($CRMSettingBHK) {
                    $data['lead']['bhk'] = $CRMSettingBHK->text;
                } else {
                    $data['lead']['bhk'] = '';
                }
            } else {
                $data['lead']['bhk'] = '';
            }

            if ($data['lead']['want_to_cover'] != 0) {
                $CRMSettingWantToCover = CRMSettingWantToCover::find(explode(',', $data['lead']['want_to_cover']));
                if ($CRMSettingWantToCover) {
                    $want_to_cover = '';
                    foreach ($CRMSettingWantToCover as $key => $value) {
                        $want_to_cover .= $value['name'] . ', ';
                    }
                    $data['lead']['want_to_cover'] = rtrim($want_to_cover, ', ');
                }
            } else {
                $data['lead']['want_to_cover'] = '';
            }

            if ($data['lead']['competitor'] != 0) {
                $CRMSettingCompetitors = CRMSettingCompetitors::find(explode(',', $data['lead']['competitor']));
                if ($CRMSettingCompetitors) {
                    $competitor = '';
                    foreach ($CRMSettingCompetitors as $key => $value) {
                        $competitor .= $value['name'] . ', ';
                    }
                    $data['lead']['competitor'] = rtrim($competitor, ', ');
                }
            } else {
                $data['lead']['competitor'] = '';
            }

            if ($data['lead']['sub_status'] != 0) {
                $CRMSettingSubStatus = CRMSettingSubStatus::select('id', 'name as text');
                $CRMSettingSubStatus->where('crm_setting_sub_status.id', $data['lead']['sub_status']);
                $CRMSettingSubStatus = $CRMSettingSubStatus->first();
                if ($CRMSettingSubStatus) {
                    $data['lead']['sub_status'] = $CRMSettingSubStatus;
                } else {
                    $data['lead']['sub_status'] = '';
                }
            } else {
                $data['lead']['sub_status'] = '';
            }

            $data['lead']['tag_lable'] = '';

            if ($data['lead']['tag'] != '') {
                $CRMLeadDealTag = DB::table('tag_master');
                $CRMLeadDealTag->select('tag_master.id AS id', 'tag_master.tagname AS text');
                $CRMLeadDealTag->where('tag_master.isactive', 1);
                $CRMLeadDealTag->where('tag_master.tag_type', 201);
                $CRMLeadDealTag->whereIn('tag_master.id', explode(',', $data['lead']['tag']));
                $data['lead']['tag'] = $CRMLeadDealTag->get();
                $tag_ids = $CRMLeadDealTag->distinct()->pluck('tag_master.id')->all();
                if (in_array(9, $tag_ids)) {
                    $data['lead']['tag_lable'] = '<span class="badge rounded-pill bg-danger ms-2" style="font-size: 12px;">DND</span>';
                }
            } else {
                $data['lead']['tag'] = '';
            }

            $LeadUpdate = LeadUpdate::query();
            $LeadUpdate->select('lead_updates.id', 'lead_updates.message', 'lead_updates.user_id', 'lead_updates.task', 'lead_updates.task_title', 'users.first_name', 'users.last_name', 'lead_updates.created_at');
            $LeadUpdate->leftJoin('users', 'users.id', '=', 'lead_updates.user_id');
            $LeadUpdate->where('lead_updates.lead_id', $data['lead']['id']);
            $LeadUpdate->orderBy('lead_updates.id', 'desc');
            $LeadUpdate->limit(5);
            $LeadUpdate = $LeadUpdate->get();
            $LeadUpdate = json_encode($LeadUpdate);
            $LeadUpdate = json_decode($LeadUpdate, true);

            foreach ($LeadUpdate as $key => $value) {
                $LeadUpdate[$key]['message'] = strip_tags($value['message']);

                $LeadUpdate[$key]['created_at'] = convertDateTime($value['created_at']);
                $LeadUpdate[$key]['date'] = convertDateAndTime($value['created_at'], 'date');
                $LeadUpdate[$key]['time'] = convertDateAndTime($value['created_at'], 'time');
            }

            $LeadQuestionAnswer = LeadQuestionAnswer::select('lead_question.question');
            $LeadQuestionAnswer->leftJoin('lead_question', 'lead_question.id', '=', 'lead_question_answer.lead_question_id');
            $LeadQuestionAnswer->where('lead_question_answer.lead_id', $data['lead']['id']);
            $LeadQuestionAnswer->where('lead_question_answer.reference_type', 'Lead-Status-Update');
            $LeadQuestionAnswer->where('lead_question_answer.answer', '!=', '');
            $LeadQuestionAnswer->get();

            $LeadContact_List = LeadContact::query();
            $LeadContact_List->select('crm_setting_contact_tag.name as tag_name', 'lead_contacts.*');
            $LeadContact_List->leftJoin('crm_setting_contact_tag', 'crm_setting_contact_tag.id', '=', 'lead_contacts.contact_tag_id');
            $LeadContact_List->where('lead_contacts.lead_id', $data['lead']['id']);
            $LeadContact_List->where('lead_contacts.status', 1);
            $LeadContact_List->orderBy('lead_contacts.id', 'desc');
            $LeadContact_List->limit(5);
            $LeadContact_List = $LeadContact_List->get();
            $LeadContact_List = json_encode($LeadContact_List);
            $LeadContact_List = json_decode($LeadContact_List, true);

            foreach ($LeadContact_List as $contact_key => $contact_value) {
                $LeadContact_List[$contact_key]['firm_name'] = '';
                if ($contact_value['type_detail'] != null || $contact_value['type_detail'] != 0 || $contact_value['type_detail'] != '') {
                    $lst_detail = explode('-', $contact_value['type_detail']);
                    if (count($lst_detail) == 3) {
                        if ($lst_detail[1] == 202) {
                            $architect = Architect::select('firm_name')
                                ->where('user_id', $lst_detail[2])
                                ->first();
                            if ($architect) {
                                $LeadContact_List[$contact_key]['firm_name'] = $architect->firm_name;
                            }
                        } elseif (isset(getChannelPartners()[$lst_detail[1]]['short_name'])) {
                            $chnnel_partener = ChannelPartner::select('firm_name')
                                ->where('user_id', $lst_detail[2])
                                ->first();
                            if ($chnnel_partener) {
                                $LeadContact_List[$contact_key]['firm_name'] = $chnnel_partener->firm_name;
                            }
                        }
                    }
                }
            }

            $LeadBillSummary_claimed = LeadFile::query();
            $LeadBillSummary_claimed->selectRaw('SUM(lead_files.point) as total_point');
            $LeadBillSummary_claimed->where('lead_files.lead_id', $data['lead']['id']);
            $LeadBillSummary_claimed->where('lead_files.file_tag_id', 3);
            $LeadBillSummary_claimed->where('lead_files.status', 100);
            $LeadBillSummary_claimed = $LeadBillSummary_claimed->first();
            $LeadBillSummary_claimed = $LeadBillSummary_claimed ? $LeadBillSummary_claimed->total_point : '0';

            $LeadBillSummary_query = LeadFile::query();
            $LeadBillSummary_query->selectRaw('SUM(lead_files.point) as total_point');
            $LeadBillSummary_query->where('lead_files.lead_id', $data['lead']['id']);
            $LeadBillSummary_query->where('lead_files.file_tag_id', 3);
            $LeadBillSummary_query->where('lead_files.status', 101);
            $LeadBillSummary_query = $LeadBillSummary_query->first();
            $LeadBillSummary_query = $LeadBillSummary_query ? $LeadBillSummary_query->total_point : '0';

            $LeadBillSummary_laps = LeadFile::query();
            $LeadBillSummary_laps->selectRaw('SUM(lead_files.point) as total_point');
            $LeadBillSummary_laps->where('lead_files.lead_id', $data['lead']['id']);
            $LeadBillSummary_laps->where('lead_files.file_tag_id', 3);
            $LeadBillSummary_laps->where('lead_files.status', 102);
            $LeadBillSummary_laps = $LeadBillSummary_laps->first();
            $LeadBillSummary_laps = $LeadBillSummary_laps ? $LeadBillSummary_laps->total_point : '0';

            $LeadFile = LeadFile::query();
            $LeadFile->select('crm_setting_file_tag.name as tag_name', 'lead_files.*', 'users.first_name', 'users.last_name');
            $LeadFile->leftJoin('crm_setting_file_tag', 'crm_setting_file_tag.id', '=', 'lead_files.file_tag_id');
            $LeadFile->leftJoin('users', 'users.id', '=', 'lead_files.uploaded_by');
            $LeadFile->where('lead_files.lead_id', $data['lead']['id']);
            $LeadFile->limit(5);
            $LeadFile->orderBy('lead_files.id', 'desc');
            $LeadFile = $LeadFile->get();
            $LeadFile = json_encode($LeadFile);
            $LeadFile = json_decode($LeadFile, true);

            $active_bill_count = LeadFile::query()
                ->where('lead_files.lead_id', $data['lead']['id'])
                ->where('lead_files.file_tag_id', 3)
                ->where('lead_files.is_active', 1)
                ->count();
            foreach ($LeadFile as $key => $value) {
                $fileHtml = '';
                foreach (explode(',', $value['name']) as $filekey => $filevalue) {
                    $fileHtml .= '<a class="ms-1" target="_blank" href="' . getSpaceFilePath($filevalue) . '"><i class="bx bxs-file-pdf"></i></a>';
                }
                $LeadFile[$key]['download'] = $fileHtml;

                if ($value['status'] == 100) {
                    $LeadFile[$key]['point'] = $value['point'];
                } else {
                    $LeadFile[$key]['point'] = '-';
                }

                $LeadFile[$key]['created_at'] = convertDateTime($value['created_at']);
            }

            $LeadCall = LeadCall::query();
            $LeadCall->select('lead_calls.*', 'users.first_name', 'users.last_name');
            $LeadCall->where('lead_calls.lead_id', $data['lead']['id']);
            $LeadCall->where('is_closed', 0);
            $LeadCall->leftJoin('users', 'users.id', '=', 'lead_calls.user_id');
            $LeadCall->orderBy('lead_calls.id', 'desc');
            $LeadCall = $LeadCall->get();
            $LeadCall = json_encode($LeadCall);
            $LeadCall = json_decode($LeadCall, true);
            foreach ($LeadCall as $key => $value) {
                $LeadCall[$key]['date'] = convertDateAndTime($value['call_schedule'], 'date');
                $LeadCall[$key]['time'] = convertDateAndTime($value['call_schedule'], 'time');

                $LeadCall[$key]['tooltip_message'] = '';
                if ($value['reference_id'] != 0) {
                    $LeadRef = LeadTask::find($value['reference_id']);
                    if ($LeadRef->is_autogenerate == 1) {
                        if ($LeadRef->assign_to == Auth::user()->id) {
                            $LeadCall[$key]['is_reference'] = 1;
                        } else {
                            $LeadCall[$key]['is_reference'] = 2;
                            $user_type = User::find($LeadRef->assign_to)->type;
                            if ($user_type == 1 || $user_type == 13) {
                                $LeadCall[$key]['tooltip_message'] = 'CRE User & Company Admin Edit Only';
                            } elseif ($user_type == 9) {
                                $LeadCall[$key]['tooltip_message'] = 'TeleSales User Edit Only';
                            } elseif ($user_type == 11) {
                                $LeadCall[$key]['tooltip_message'] = 'Service User Edit Only';
                            }
                        }
                    } else {
                        $LeadCall[$key]['is_reference'] = 0;
                    }
                } else {
                    $LeadCall[$key]['is_reference'] = 0;
                }

                $ContactName = LeadContact::select('lead_contacts.id', 'lead_contacts.first_name', 'lead_contacts.last_name', DB::raw("CONCAT(lead_contacts.first_name,' ',lead_contacts.last_name) AS text"));
                $ContactName->where('lead_contacts.id', $value['contact_name']);
                $ContactName = $ContactName->first();
                if ($ContactName) {
                    $LeadCall[$key]['contact_name'] = $ContactName->text;
                } else {
                    $LeadCall[$key]['contact_name'] = '';
                }
            }

            $LeadCallClosed = LeadCall::query();
            $LeadCallClosed->select('lead_calls.*', 'users.first_name', 'users.last_name');
            $LeadCallClosed->where('lead_calls.lead_id', $data['lead']['id']);
            $LeadCallClosed->where('is_closed', 1);
            $LeadCallClosed->leftJoin('users', 'users.id', '=', 'lead_calls.user_id');
            $LeadCallClosed->orderBy('lead_calls.closed_date_time', 'desc');
            $LeadCallClosed = $LeadCallClosed->get();
            $LeadCallClosed = json_encode($LeadCallClosed);
            $LeadCallClosed = json_decode($LeadCallClosed, true);
            foreach ($LeadCallClosed as $key => $value) {
                $LeadCallClosed[$key]['date'] = convertDateAndTime($value['closed_date_time'], 'date');
                $LeadCallClosed[$key]['time'] = convertDateAndTime($value['closed_date_time'], 'time');
                $ContactName = LeadContact::select('lead_contacts.id', 'lead_contacts.first_name', 'lead_contacts.last_name', DB::raw("CONCAT(lead_contacts.first_name,' ',lead_contacts.last_name) AS text"));
                $ContactName->where('lead_contacts.id', $value['contact_name']);
                $ContactName = $ContactName->first();
                if ($ContactName) {
                    $LeadCallClosed[$key]['contact_name'] = $ContactName->text;
                } else {
                    $LeadCallClosed[$key]['contact_name'] = '';
                }
            }

            $LeadMeeting = LeadMeeting::query();
            $LeadMeeting->select('lead_meetings.*', 'users.first_name', 'users.last_name');
            $LeadMeeting->where('lead_meetings.lead_id', $data['lead']['id']);
            $LeadMeeting->where('is_closed', 0);
            $LeadMeeting->leftJoin('users', 'users.id', '=', 'lead_meetings.user_id');
            $LeadMeeting->orderBy('lead_meetings.id', 'desc');
            $LeadMeeting = $LeadMeeting->get();
            $LeadMeeting = json_encode($LeadMeeting);
            $LeadMeeting = json_decode($LeadMeeting, true);
            foreach ($LeadMeeting as $key => $value) {
                $LeadMeeting[$key]['date'] = convertDateAndTime($value['meeting_date_time'], 'date');
                $LeadMeeting[$key]['time'] = convertDateAndTime($value['meeting_date_time'], 'time');

                $LeadMeetingTitle = CRMSettingMeetingTitle::select('name')
                    ->where('id', $value['title_id'])
                    ->first();

                if ($LeadMeetingTitle) {
                    $LeadMeeting[$key]['title_name'] = $LeadMeetingTitle->name;
                } else {
                    $LeadMeeting[$key]['title_name'] = $LeadMeetingTitle->name;
                }

                $LeadMeetingParticipant = LeadMeetingParticipant::where('meeting_id', $value['id'])
                    ->orderby('id', 'asc')
                    ->get();
                $LeadMeetingParticipant = json_decode(json_encode($LeadMeetingParticipant), true);

                $UsersId = [];
                $ContactIds = [];
                foreach ($LeadMeetingParticipant as $sales_key => $value) {
                    if ($value['type'] == 'users') {
                        $UsersId[] = $value['reference_id'];
                    }
                }

                foreach ($LeadMeetingParticipant as $contact_key => $value) {
                    if ($value['type'] == 'lead_contacts') {
                        $ContactIds[] = $value['reference_id'];
                    }
                }

                $UserResponse = '';
                if (count($ContactIds) > 0) {
                    $LeadContact = LeadContact::select('lead_contacts.id', 'lead_contacts.first_name', 'lead_contacts.last_name', DB::raw("CONCAT(lead_contacts.first_name,' ',lead_contacts.last_name) AS full_name"));
                    $LeadContact->whereIn('lead_contacts.id', $ContactIds);
                    $LeadContact = $LeadContact->get();
                    if (count($LeadContact) > 0) {
                        foreach ($LeadContact as $User_key => $User_value) {
                            $UserResponse .= 'Contact - ' . $User_value['full_name'] . '<br>';
                        }
                    }
                }

                if (count($UsersId) > 0) {
                    $User = User::select('users.id', 'users.type', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
                    $User->whereIn('users.id', $UsersId);
                    $User = $User->get();
                    $getAllUserTypes = getAllUserTypes();
                    if (count($User) > 0) {
                        foreach ($User as $User_key => $User_value) {
                            $UserResponse .= $getAllUserTypes[$User_value['type']]['short_name'] . ' - ' . $User_value['full_name'] . '<br>';
                        }
                    }
                }

                if ($UserResponse) {
                    $LeadMeeting[$key]['meeting_participant'] = $UserResponse;
                } else {
                    $LeadMeeting[$key]['meeting_participant'] = '';
                }
            }

            $LeadMeetingClosed = LeadMeeting::query();
            $LeadMeetingClosed->select('lead_meetings.*', 'users.first_name', 'users.last_name');
            $LeadMeetingClosed->where('lead_meetings.lead_id', $data['lead']['id']);
            $LeadMeetingClosed->where('is_closed', 1);
            $LeadMeetingClosed->leftJoin('users', 'users.id', '=', 'lead_meetings.user_id');
            $LeadMeetingClosed->orderBy('lead_meetings.closed_date_time', 'desc');
            $LeadMeetingClosed = $LeadMeetingClosed->get();
            $LeadMeetingClosed = json_encode($LeadMeetingClosed);
            $LeadMeetingClosed = json_decode($LeadMeetingClosed, true);
            foreach ($LeadMeetingClosed as $key => $value) {
                $LeadMeetingClosed[$key]['date'] = convertDateAndTime($value['closed_date_time'], 'date');
                $LeadMeetingClosed[$key]['time'] = convertDateAndTime($value['closed_date_time'], 'time');

                $LeadMeetingTitle = CRMSettingMeetingTitle::select('name')
                    ->where('id', $value['title_id'])
                    ->first();
                if ($LeadMeetingTitle) {
                    $LeadMeetingClosed[$key]['title_name'] = $LeadMeetingTitle->name;
                } else {
                    $LeadMeetingClosed[$key]['title_name'] = ' ';
                }

                $LeadMeetingParticipant = LeadMeetingParticipant::where('meeting_id', $value['id'])
                    ->orderby('id', 'asc')
                    ->get();
                $LeadMeetingParticipant = json_decode(json_encode($LeadMeetingParticipant), true);

                $UsersId = [];
                $ContactIds = [];
                foreach ($LeadMeetingParticipant as $sales_key => $value) {
                    if ($value['type'] == 'users') {
                        $UsersId[] = $value['reference_id'];
                    }
                }

                foreach ($LeadMeetingParticipant as $contact_key => $value) {
                    if ($value['type'] == 'lead_contacts') {
                        $ContactIds[] = $value['reference_id'];
                    }
                }

                $UserResponse = '';
                if (count($ContactIds) > 0) {
                    $LeadContact = LeadContact::select('lead_contacts.id', DB::raw("CONCAT(lead_contacts.first_name,' ',lead_contacts.last_name) AS full_name"));
                    $LeadContact->whereIn('lead_contacts.id', $ContactIds);
                    $LeadContact = $LeadContact->get();
                    if (count($LeadContact) > 0) {
                        foreach ($LeadContact as $User_key => $User_value) {
                            $UserResponse .= 'Contact - ' . $User_value['full_name'] . '<br>';
                        }
                    }
                }

                if (count($UsersId) > 0) {
                    $User = User::select('users.id', 'users.type', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
                    $User->whereIn('users.id', $UsersId);
                    $User = $User->get();
                    if (count($User) > 0) {
                        foreach ($User as $User_key => $User_value) {
                            $UserResponse .= getAllUserTypes()[$User_value['type']]['short_name'] . ' - ' . $User_value['full_name'] . '<br>';
                        }
                    }
                }

                if ($UserResponse) {
                    $LeadMeetingClosed[$key]['meeting_participant'] = $UserResponse;
                } else {
                    $LeadMeetingClosed[$key]['meeting_participant'] = '';
                }
            }

            $LeadTask = LeadTask::query();
            $LeadTask->select('lead_tasks.*', 'users.first_name', 'users.last_name');
            $LeadTask->where('lead_tasks.lead_id', $data['lead']['id']);
            $LeadTask->where('is_closed', 0);
            $LeadTask->leftJoin('users', 'users.id', '=', 'lead_tasks.user_id');
            $LeadTask->orderBy('lead_tasks.id', 'desc');
            $LeadTask = $LeadTask->get();
            $LeadTask = json_encode($LeadTask);
            $LeadTask = json_decode($LeadTask, true);
            foreach ($LeadTask as $key => $value) {
                $LeadTask[$key]['date'] = convertDateAndTime($value['due_date_time'], 'date');
                $LeadTask[$key]['time'] = convertDateAndTime($value['due_date_time'], 'time');

                $LeadTask[$key]['tooltip_message'] = '';
                if ($value['is_autogenerate'] == 1) {
                    if ($value['assign_to'] != Auth::user()->id) {
                        $user_type = User::find($value['assign_to'])->type;
                        if ($user_type == 1 || $user_type == 13) {
                            $LeadTask[$key]['tooltip_message'] = 'CRE User & Company Admin Edit Only';
                        } elseif ($user_type == 9) {
                            $LeadTask[$key]['tooltip_message'] = 'TeleSales User Edit Only';
                        } elseif ($user_type == 11) {
                            $LeadTask[$key]['tooltip_message'] = 'Service User Edit Only';
                        }
                    }
                }

                $Taskowner = User::select('users.id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"));
                // $Taskowner->where('users.status', 1);
                $Taskowner->where('users.id', $value['assign_to']);
                $Taskowner = $Taskowner->first();

                if ($Taskowner) {
                    $LeadTask[$key]['task_owner'] = $Taskowner->text;
                } else {
                    $LeadTask[$key]['task_owner'] = ' ';
                }
            }

            $LeadTaskClosed = LeadTask::query();
            $LeadTaskClosed->select('lead_tasks.*', 'users.first_name', 'users.last_name');
            $LeadTaskClosed->where('lead_tasks.lead_id', $data['lead']['id']);
            $LeadTaskClosed->where('is_closed', 1);
            $LeadTaskClosed->leftJoin('users', 'users.id', '=', 'lead_tasks.user_id');
            $LeadTaskClosed->orderBy('lead_tasks.closed_date_time', 'desc');
            $LeadTaskClosed = $LeadTaskClosed->get();
            $LeadTaskClosed = json_encode($LeadTaskClosed);
            $LeadTaskClosed = json_decode($LeadTaskClosed, true);
            foreach ($LeadTaskClosed as $key => $value) {
                $LeadTaskClosed[$key]['date'] = convertDateAndTime($value['closed_date_time'], 'date');
                $LeadTaskClosed[$key]['time'] = convertDateAndTime($value['closed_date_time'], 'time');

                $Taskowner = User::select('users.id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"));
                // $Taskowner->where('users.status', 1);
                $Taskowner->where('users.id', $value['assign_to']);
                $Taskowner = $Taskowner->first();

                if ($Taskowner) {
                    $LeadTaskClosed[$key]['task_owner'] = $Taskowner->text;
                } else {
                    $LeadTaskClosed[$key]['task_owner'] = ' ';
                }
            }

            $countCall = count($LeadCall);
            $countTask = count($LeadTask);
            $countMeeting = count($LeadMeeting);
            $maxOpenAction = max($countCall, $countTask, $countMeeting);

            $countCallClosed = count($LeadCallClosed);
            $countTaskClosed = count($LeadTaskClosed);
            $countMeetingClosed = count($LeadMeetingClosed);
            $maxClosedAction = max($countCallClosed, $countTaskClosed, $countMeetingClosed);

            $LeadQuotation = Wltrn_Quotation::query();
            $LeadQuotation->select('id as quot_id', 'quotgroup_id as quot_groupid', 'quottype_id as quottype_id', 'quot_date', 'isfinal', 'quot_no_str as quot_version', 'quotation_file', 'wltrn_quotation.quot_whitelion_amount as whitelion_amount', 'wltrn_quotation.quot_other_amount as other_amount', 'wltrn_quotation.quot_billing_amount as billing_amount', 'wltrn_quotation.quot_total_amount as total_amount', 'wltrn_quotation.status', 'wltrn_quotation.created_at');
            $LeadQuotation->where('wltrn_quotation.inquiry_id', $request->id);
            $LeadQuotation->where('wltrn_quotation.status', 3);
            $LeadQuotation->orderBy('wltrn_quotation.id', 'desc');
            $LeadQuotation = $LeadQuotation->get();
            $LeadQuotation = json_decode(json_encode($LeadQuotation), true);

            $quotation = [];
            foreach ($LeadQuotation as $key => $value) {
                $quotation_details = $value;
                $quotation_details['whitelion_amount'] = numCommaFormat($value['whitelion_amount']);
                $quotation_details['other_amount'] = numCommaFormat($value['other_amount']);
                $quotation_details['billing_amount'] = numCommaFormat($value['billing_amount']);
                $quotation_details['total_amount'] = numCommaFormat($value['total_amount']);
                array_push($quotation, $quotation_details);
            }

            $LeadClosingDate = LeadClosing::query();
            $LeadClosingDate->select('lead_closing.*');
            $LeadClosingDate->where('lead_closing.lead_id', $data['lead']['id']);
            $LeadClosingDate = $LeadClosingDate->get();

            $response = successRes('Get List');
            $data['lead_id'] = $request->id;
            $data['contacts'] = $LeadContact_List;
            $data['updates'] = $LeadUpdate;

            $data['files'] = $LeadFile;
            $data['active_bill_count'] = $active_bill_count;
            $data['LeadBillSummary_claimed'] = $LeadBillSummary_claimed;
            $data['LeadBillSummary_query'] = $LeadBillSummary_query;
            $data['LeadBillSummary_laps'] = $LeadBillSummary_laps;
            $data['current_status'] = $data['lead']['status'];

            $data['calls'] = $LeadCall;
            $data['tasks'] = $LeadTask;
            $data['meetings'] = $LeadMeeting;

            $data['max_open_actions'] = $maxOpenAction;

            $data['calls_closed'] = $LeadCallClosed;
            $data['tasks_closed'] = $LeadTaskClosed;
            $data['meetings_closed'] = $LeadMeetingClosed;
            $data['closing_date'] = $LeadClosingDate;
            $data['closing_date_count'] = count($LeadClosingDate);

            $data['max_close_actions'] = $maxClosedAction;
            $data['lead_status'] = getLeadStatus();
            $data['quotation'] = $quotation;

            $LeadTimeline = LeadTimeline::select('lead_timeline.*', 'users.first_name', 'users.last_name')
                ->leftJoin('users', 'users.id', '=', 'lead_timeline.user_id')
                ->where('lead_id', $data['lead']['id'])
                ->orderBy('id', 'desc')
                ->get();

            $LeadTimeline = json_encode($LeadTimeline);
            $LeadTimeline = json_decode($LeadTimeline, true);

            $repeated_date = '';
            foreach ($LeadTimeline as $key => $value) {
                $date = convertDateAndTime($value['created_at'], 'date');
                if ($repeated_date == $date) {
                    $LeadTimeline[$key]['date'] = 0;
                } else {
                    $repeated_date = $date;
                    $LeadTimeline[$key]['date'] = convertDateAndTime($value['created_at'], 'date');
                }
                $LeadTimeline[$key]['created_date'] = convertDateAndTime($value['created_at'], 'date');
                $LeadTimeline[$key]['time'] = convertDateAndTime($value['created_at'], 'time');
                $LeadTimeline[$key]['created_at'] = convertDateTime($value['created_at']);
                $LeadTimeline[$key]['updated_at'] = convertDateTime($value['updated_at']);
            }

            $data['lead']['material_sent_by'] = "";
            $LeadQuestionAnswer = LeadQuestionAnswer::select('lead_question_answer.lead_question_id', 'lead_question.question', 'lead_question_answer.answer', 'lead_question.type', 'lead_question_answer.created_at', 'lead_question_answer.updated_at');
            $LeadQuestionAnswer->leftJoin('lead_question', 'lead_question.id', '=', 'lead_question_answer.lead_question_id');
            $LeadQuestionAnswer->where('lead_question_answer.lead_id', $data['lead']['id']);
            $LeadQuestionAnswer->where('lead_question_answer.reference_type', 'Lead-Status-Update');
            $LeadQuestionAnswer->where('lead_question_answer.answer', '!=', '');
            $LeadQuestionAnswer = $LeadQuestionAnswer->get();

            $LeadQuestionAnswer = json_encode($LeadQuestionAnswer);
            $LeadQuestionAnswer = json_decode($LeadQuestionAnswer, true);
            foreach ($LeadQuestionAnswer as $key => $value) {

                if ($value['type'] == 0) {
                    $LeadQuestionAnswer[$key]['option'] = $value['answer'];
                } elseif ($value['type'] == 1) {
                    $option_ids = LeadQuestionOptions::select('id')
                        ->where('lead_question_id', $value['lead_question_id'])
                        ->distinct()
                        ->pluck('id')
                        ->all();

                    if (!in_array($value['answer'], $option_ids)) {
                        $ChannelPart = ChannelPartner::select('firm_name')
                            ->where('user_id', $value['answer'])
                            ->first();
                        if ($ChannelPart) {
                            $LeadQuestionAnswer[$key]['option'] = $ChannelPart->firm_name;
                            if ((int)$value['lead_question_id'] == 19) {
                                $data['lead']['material_sent_by'] = $ChannelPart->firm_name;
                            }
                        } else {
                            $LeadQuestionAnswer[$key]['option'] = '';
                        }
                    } else {
                        $LeadQuestionOption = LeadQuestionOptions::select('lead_question_options.option');
                        $LeadQuestionOption->where('id', $value['answer']);
                        $LeadQuestionOption = $LeadQuestionOption->first();
                        $LeadQuestionAnswer[$key]['option'] = $LeadQuestionOption->option;
                    }
                } elseif ($value['type'] == 5) {
                    $LeadQuestionAnswer[$key]['option'] = $value['answer'];
                } elseif ($value['type'] == 4 || $value['type'] == 6) {
                    $LeadQuestionOption = LeadQuestionOptions::select('lead_question_options.option');
                    $LeadQuestionOption->whereIn('id', explode(',', $value['answer']));
                    $LeadQuestionOption = $LeadQuestionOption->get();

                    $MultipleAnswer = '';
                    foreach ($LeadQuestionOption as $Okey => $Ovalue) {
                        $MultipleAnswer .= $Ovalue['option'] . '<br>';
                    }

                    $LeadQuestionAnswer[$key]['option'] = $MultipleAnswer;
                } elseif ($value['type'] == 7) {
                    $LeadQuestionAnswer[$key]['option'] = getSpaceFilePath($value['answer']);
                } else {
                    $LeadQuestionAnswer[$key]['option'] = $value['answer'];
                }

                $date = convertDateAndTime($value['created_at'], 'date');
                if ($repeated_date == $date) {
                    $LeadQuestionAnswer[$key]['date'] = 0;
                } else {
                    $repeated_date = $date;
                    $LeadQuestionAnswer[$key]['date'] = convertDateAndTime($value['created_at'], 'date');
                }
                $LeadQuestionAnswer[$key]['created_date'] = convertDateAndTime($value['created_at'], 'date');
                $LeadQuestionAnswer[$key]['time'] = convertDateAndTime($value['created_at'], 'time');
                $LeadQuestionAnswer[$key]['created_at'] = convertDateTime($value['created_at']);
                $LeadQuestionAnswer[$key]['updated_at'] = convertDateTime($value['updated_at']);
            }

            $data['timeline'] = $LeadTimeline;
            $data['question'] = $LeadQuestionAnswer;


            $response['view'] = view('crm/lead/detail', compact('data'))->render();
            $response['data'] = $data;
        } else {
            $response = errorRes('Lead Data Not Available');
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }
    function allContact(Request $request)
    {
        $LeadContact = LeadContact::query();
        $LeadContact->select('crm_setting_contact_tag.name as tag_name', 'lead_contacts.*');
        $LeadContact->leftJoin('crm_setting_contact_tag', 'crm_setting_contact_tag.id', '=', 'lead_contacts.contact_tag_id');
        $LeadContact->where('lead_contacts.lead_id', $request->lead_id);
        $LeadContact->where('lead_contacts.status', 1);
        $LeadContact->orderBy('lead_contacts.id', 'desc');
        if ($request->islimit == 1) {
            $LeadContact->limit(5);
        }
        $LeadContact = $LeadContact->get();
        $LeadContact = json_encode($LeadContact);
        $LeadContact = json_decode($LeadContact, true);
        foreach ($LeadContact as $contact_key => $contact_value) {
            $LeadContact[$contact_key]['firm_name'] = '';
            if ($contact_value['type_detail'] != null || $contact_value['type_detail'] != 0 || $contact_value['type_detail'] != '') {
                $lst_detail = explode('-', $contact_value['type_detail']);
                if (count($lst_detail) == 3) {
                    if ($lst_detail[1] == 202) {
                        $architect = Architect::select('firm_name')
                            ->where('user_id', $lst_detail[2])
                            ->first();
                        if ($architect) {
                            $LeadContact[$contact_key]['firm_name'] = $architect->firm_name;
                        }
                    } elseif (isset(getChannelPartners()[$lst_detail[1]]['short_name'])) {
                        $chnnel_partener = ChannelPartner::select('firm_name')
                            ->where('user_id', $lst_detail[2])
                            ->first();
                        if ($chnnel_partener) {
                            $LeadContact[$contact_key]['firm_name'] = $chnnel_partener->firm_name;
                        }
                    }
                }
            }
        }
        $response = successRes('All contact List');
        $response['cont'] = $LeadContact;
        $data['contacts'] = $LeadContact;
        $data['lead_id'] = $request->lead_id;
        $response['view'] = view('crm/lead/detail_tab/detail_contact_tab', compact('data'))->render();
        return response()->json($response)->header('Content-Type', 'application/json');
    }
    function allFiles(Request $request)
    {
        $LeadFile = LeadFile::query();
        $LeadFile->select('crm_setting_file_tag.name as tag_name', 'lead_files.*', 'users.first_name', 'users.last_name');
        $LeadFile->leftJoin('crm_setting_file_tag', 'crm_setting_file_tag.id', '=', 'lead_files.file_tag_id');
        $LeadFile->leftJoin('users', 'users.id', '=', 'lead_files.uploaded_by');
        $LeadFile->where('lead_files.lead_id', $request->lead_id);
        if ($request->islimit == 1) {
            $LeadFile->limit(5);
        }
        $LeadFile->orderBy('lead_files.id', 'desc');
        $LeadFile = $LeadFile->get();
        $LeadFile = json_encode($LeadFile);
        $LeadFile = json_decode($LeadFile, true);

        $active_bill_count = LeadFile::query()
            ->where('lead_files.lead_id', $request->lead_id)
            ->where('lead_files.file_tag_id', 3)
            ->where('lead_files.is_active', 1)
            ->count();

        foreach ($LeadFile as $key => $value) {
            $fileHtml = '';
            foreach (explode(',', $value['name']) as $filekey => $filevalue) {
                $fileHtml .= '<a class="ms-1" target="_blank" href="' . getSpaceFilePath($filevalue) . '"><i class="bx bxs-file-pdf"></i></a>';
            }
            $LeadFile[$key]['download'] = $fileHtml;
            $LeadFile[$key]['created_at'] = convertDateTime($value['created_at']);
        }

        $response = successRes('All files List');
        $data['files'] = $LeadFile;
        $data['lead_id'] = $request->lead_id;
        $response['view'] = view('crm/lead/detail_tab/detail_file_tab', compact('data'))->render();
        $response['files'] = $LeadFile;
        $response['active_bill_count'] = $active_bill_count;
        return response()->json($response)->header('Content-Type', 'application/json');
    }
    function allUpdates(Request $request)
    {
        $LeadUpdate = LeadUpdate::query();
        $LeadUpdate->select('lead_updates.id', 'lead_updates.message', 'lead_updates.task', 'lead_updates.task_title', 'lead_updates.user_id', 'users.first_name', 'users.last_name', 'lead_updates.created_at');
        $LeadUpdate->leftJoin('users', 'users.id', '=', 'lead_updates.user_id');
        $LeadUpdate->where('lead_updates.lead_id', $request->lead_id);
        $LeadUpdate->orderBy('lead_updates.id', 'desc');
        if ($request->islimit == 1) {
            $LeadUpdate->limit(5);
        }
        $LeadUpdate = $LeadUpdate->get();
        $LeadUpdate = json_encode($LeadUpdate);
        $LeadUpdate = json_decode($LeadUpdate, true);

        foreach ($LeadUpdate as $key => $value) {
            $LeadUpdate[$key]['created_at'] = convertDateTime($value['created_at']);
            $LeadUpdate[$key]['date'] = convertDateAndTime($value['created_at'], 'date');
            $LeadUpdate[$key]['time'] = convertDateAndTime($value['created_at'], 'time');
        }

        $data = [];
        $data['updates'] = $LeadUpdate;
        $data['lead_id'] = $request->lead_id;
        $response = successRes('All Update List');
        $response['view'] = view('crm/lead/detail_tab/detail_notes_tab', compact('data'))->render();
        // $response['data'] = $data;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function allOpenAction(Request $request)
    {
        $Lead = Lead::find($request->lead_id);
        if ($Lead) {
            $Lead = json_encode($Lead);
            $Lead = json_decode($Lead, true);

            $lead_status_label = getLeadStatus()[$Lead['status']]['name'];
        }

        $LeadCall = LeadCall::query();
        $LeadCall->select('lead_calls.*', 'users.first_name', 'users.last_name');
        $LeadCall->where('lead_calls.lead_id', $request->lead_id);
        $LeadCall->where('is_closed', 0);
        $LeadCall->leftJoin('users', 'users.id', '=', 'lead_calls.user_id');
        $LeadCall->orderBy('lead_calls.id', 'desc');
        $LeadCall = $LeadCall->get();
        $LeadCall = json_encode($LeadCall);
        $LeadCall = json_decode($LeadCall, true);
        foreach ($LeadCall as $key => $value) {
            $LeadCall[$key]['date'] = convertDateAndTime($value['call_schedule'], 'date');
            $LeadCall[$key]['time'] = convertDateAndTime($value['call_schedule'], 'time');

            $LeadCall[$key]['tooltip_message'] = '';
            if ($value['reference_id'] != 0) {
                $LeadRef = LeadTask::find($value['reference_id']);
                if ($LeadRef->is_autogenerate == 1) {
                    if ($LeadRef->assign_to == Auth::user()->id) {
                        $LeadCall[$key]['is_reference'] = 1;
                    } else {
                        $LeadCall[$key]['is_reference'] = 2;
                        $user_type = User::find($LeadRef->assign_to)->type;
                        if ($user_type == 1 || $user_type == 13) {
                            $LeadCall[$key]['tooltip_message'] = 'CRE User & Company Admin Edit Only';
                        } elseif ($user_type == 9) {
                            $LeadCall[$key]['tooltip_message'] = 'TeleSales User Edit Only';
                        } elseif ($user_type == 11) {
                            $LeadCall[$key]['tooltip_message'] = 'Service User Edit Only';
                        }
                    }
                } else {
                    $LeadCall[$key]['is_reference'] = 0;
                }
            } else {
                $LeadCall[$key]['is_reference'] = 0;
            }

            $ContactName = LeadContact::select('lead_contacts.id', 'lead_contacts.first_name', 'lead_contacts.last_name', DB::raw("CONCAT(lead_contacts.first_name,' ',lead_contacts.last_name) AS text"));
            $ContactName->where('lead_contacts.id', $value['contact_name']);
            $ContactName = $ContactName->first();
            if ($ContactName) {
                $LeadCall[$key]['contact_name'] = $ContactName->text;
            } else {
                $LeadCall[$key]['contact_name'] = '';
            }
        }

        $LeadMeeting = LeadMeeting::query();
        $LeadMeeting->select('lead_meetings.*', 'users.first_name', 'users.last_name');
        $LeadMeeting->where('lead_meetings.lead_id', $request->lead_id);
        $LeadMeeting->where('is_closed', 0);
        $LeadMeeting->leftJoin('users', 'users.id', '=', 'lead_meetings.user_id');
        $LeadMeeting->orderBy('lead_meetings.id', 'desc');
        $LeadMeeting = $LeadMeeting->get();
        $LeadMeeting = json_encode($LeadMeeting);
        $LeadMeeting = json_decode($LeadMeeting, true);
        foreach ($LeadMeeting as $key => $value) {
            $LeadMeeting[$key]['date'] = convertDateAndTime($value['meeting_date_time'], 'date');
            $LeadMeeting[$key]['time'] = convertDateAndTime($value['meeting_date_time'], 'time');

            $LeadMeetingTitle = CRMSettingMeetingTitle::select('name')
                ->where('id', $value['title_id'])
                ->first();

            if ($LeadMeetingTitle) {
                $LeadMeeting[$key]['title_name'] = $LeadMeetingTitle->name;
            } else {
                $LeadMeeting[$key]['title_name'] = $LeadMeetingTitle->name;
            }

            $LeadMeetingParticipant = LeadMeetingParticipant::where('meeting_id', $value['id'])
                ->orderby('id', 'asc')
                ->get();
            $LeadMeetingParticipant = json_decode(json_encode($LeadMeetingParticipant), true);

            $UsersId = [];
            $ContactIds = [];
            foreach ($LeadMeetingParticipant as $sales_key => $value) {
                if ($value['type'] == 'users') {
                    $UsersId[] = $value['reference_id'];
                }
            }

            foreach ($LeadMeetingParticipant as $contact_key => $value) {
                if ($value['type'] == 'lead_contacts') {
                    $ContactIds[] = $value['reference_id'];
                }
            }

            $UserResponse = '';
            if (count($ContactIds) > 0) {
                $LeadContact = LeadContact::select('lead_contacts.id', 'lead_contacts.first_name', 'lead_contacts.last_name', DB::raw("CONCAT(lead_contacts.first_name,' ',lead_contacts.last_name) AS full_name"));
                $LeadContact->whereIn('lead_contacts.id', $ContactIds);
                $LeadContact = $LeadContact->get();
                if (count($LeadContact) > 0) {
                    foreach ($LeadContact as $User_key => $User_value) {
                        $UserResponse .= 'Contact - ' . $User_value['full_name'] . '<br>';
                    }
                }
            }

            if (count($UsersId) > 0) {
                $User = User::select('users.id', 'users.type', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
                $User->whereIn('users.id', $UsersId);
                $User = $User->get();
                $getAllUserTypes = getAllUserTypes();
                if (count($User) > 0) {
                    foreach ($User as $User_key => $User_value) {
                        $UserResponse .= $getAllUserTypes[$User_value['type']]['short_name'] . ' - ' . $User_value['full_name'] . '<br>';
                    }
                }
            }

            if ($UserResponse) {
                $LeadMeeting[$key]['meeting_participant'] = $UserResponse;
            } else {
                $LeadMeeting[$key]['meeting_participant'] = '';
            }
        }

        $LeadTask = LeadTask::query();
        $LeadTask->select('lead_tasks.*', 'users.first_name', 'users.last_name');
        $LeadTask->where('lead_tasks.lead_id', $request->lead_id);
        $LeadTask->where('is_closed', 0);
        $LeadTask->leftJoin('users', 'users.id', '=', 'lead_tasks.user_id');
        $LeadTask->orderBy('lead_tasks.id', 'desc');
        $LeadTask = $LeadTask->get();
        $LeadTask = json_encode($LeadTask);
        $LeadTask = json_decode($LeadTask, true);
        foreach ($LeadTask as $key => $value) {
            $LeadTask[$key]['date'] = convertDateAndTime($value['due_date_time'], 'date');
            $LeadTask[$key]['time'] = convertDateAndTime($value['due_date_time'], 'time');

            $LeadTask[$key]['tooltip_message'] = '';
            if ($value['is_autogenerate'] == 1) {
                if ($value['assign_to'] != Auth::user()->id) {
                    $user_type = User::find($value['assign_to'])->type;
                    if ($user_type == 1 || $user_type == 13) {
                        $LeadTask[$key]['tooltip_message'] = 'CRE User & Company Admin Edit Only';
                    } elseif ($user_type == 9) {
                        $LeadTask[$key]['tooltip_message'] = 'TeleSales User Edit Only';
                    } elseif ($user_type == 11) {
                        $LeadTask[$key]['tooltip_message'] = 'Service User Edit Only';
                    }
                }
            }

            $Taskowner = User::select('users.id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"));
            // $Taskowner->where('users.status', 1);
            $Taskowner->where('users.id', $value['assign_to']);
            $Taskowner = $Taskowner->first();

            if ($Taskowner) {
                $LeadTask[$key]['task_owner'] = $Taskowner->text;
            } else {
                $LeadTask[$key]['task_owner'] = ' ';
            }
        }

        $countCall = count($LeadCall);
        $countTask = count($LeadTask);
        $countMeeting = count($LeadMeeting);

        $maxOpenAction = max($countCall, $countTask, $countMeeting);

        $data = [];
        $data['calls'] = $LeadCall;
        $data['tasks'] = $LeadTask;
        $data['meetings'] = $LeadMeeting;
        $data['max_open_actions'] = $maxOpenAction;
        $data['lead_id'] = $request->lead_id;
        $response = successRes('All Open List');
        $response['view'] = view('crm/lead/detail_tab/detail_open_action_tab', compact('data'))->render();
        $response['lead_status'] = $lead_status_label;
        $response['lead_id'] = $request->lead_id;

        // $response['data'] = $data;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function allCloseAction(Request $request)
    {
        $Lead = Lead::find($request->lead_id);
        if ($Lead) {
            $Lead = json_encode($Lead);
            $Lead = json_decode($Lead, true);

            $lead_status_label = getLeadStatus()[$Lead['status']]['name'];
        }

        $LeadCallClosed = LeadCall::query();
        $LeadCallClosed->select('lead_calls.*', 'users.first_name', 'users.last_name');
        $LeadCallClosed->where('lead_calls.lead_id', $request->lead_id);
        $LeadCallClosed->where('is_closed', 1);
        $LeadCallClosed->leftJoin('users', 'users.id', '=', 'lead_calls.user_id');
        $LeadCallClosed->orderBy('lead_calls.closed_date_time', 'desc');
        $LeadCallClosed = $LeadCallClosed->get();
        $LeadCallClosed = json_encode($LeadCallClosed);
        $LeadCallClosed = json_decode($LeadCallClosed, true);
        foreach ($LeadCallClosed as $key => $value) {
            $LeadCallClosed[$key]['date'] = convertDateAndTime($value['closed_date_time'], 'date');
            $LeadCallClosed[$key]['time'] = convertDateAndTime($value['closed_date_time'], 'time');
            $ContactName = LeadContact::select('lead_contacts.id', 'lead_contacts.first_name', 'lead_contacts.last_name', DB::raw("CONCAT(lead_contacts.first_name,' ',lead_contacts.last_name) AS text"));
            $ContactName->where('lead_contacts.id', $value['contact_name']);
            $ContactName = $ContactName->first();
            if ($ContactName) {
                $LeadCallClosed[$key]['contact_name'] = $ContactName->text;
            } else {
                $LeadCallClosed[$key]['contact_name'] = '';
            }
        }

        $LeadMeetingClosed = LeadMeeting::query();
        $LeadMeetingClosed->select('lead_meetings.*', 'users.first_name', 'users.last_name');
        $LeadMeetingClosed->where('lead_meetings.lead_id', $request->lead_id);
        $LeadMeetingClosed->where('is_closed', 1);
        $LeadMeetingClosed->leftJoin('users', 'users.id', '=', 'lead_meetings.user_id');
        $LeadMeetingClosed->orderBy('lead_meetings.closed_date_time', 'desc');
        $LeadMeetingClosed = $LeadMeetingClosed->get();
        $LeadMeetingClosed = json_encode($LeadMeetingClosed);
        $LeadMeetingClosed = json_decode($LeadMeetingClosed, true);
        foreach ($LeadMeetingClosed as $key => $value) {
            $LeadMeetingClosed[$key]['date'] = convertDateAndTime($value['closed_date_time'], 'date');
            $LeadMeetingClosed[$key]['time'] = convertDateAndTime($value['closed_date_time'], 'time');

            $LeadMeetingTitle = CRMSettingMeetingTitle::select('name')
                ->where('id', $value['title_id'])
                ->first();
            if ($LeadMeetingTitle) {
                $LeadMeetingClosed[$key]['title_name'] = $LeadMeetingTitle->name;
            } else {
                $LeadMeetingClosed[$key]['title_name'] = ' ';
            }

            $LeadMeetingParticipant = LeadMeetingParticipant::where('meeting_id', $value['id'])
                ->orderby('id', 'asc')
                ->get();
            $LeadMeetingParticipant = json_decode(json_encode($LeadMeetingParticipant), true);

            $UsersId = [];
            $ContactIds = [];
            foreach ($LeadMeetingParticipant as $sales_key => $value) {
                if ($value['type'] == 'users') {
                    $UsersId[] = $value['reference_id'];
                }
            }

            foreach ($LeadMeetingParticipant as $contact_key => $value) {
                if ($value['type'] == 'lead_contacts') {
                    $ContactIds[] = $value['reference_id'];
                }
            }

            $UserResponse = '';
            if (count($ContactIds) > 0) {
                $LeadContact = LeadContact::select('lead_contacts.id', DB::raw("CONCAT(lead_contacts.first_name,' ',lead_contacts.last_name) AS full_name"));
                $LeadContact->whereIn('lead_contacts.id', $ContactIds);
                $LeadContact = $LeadContact->get();
                if (count($LeadContact) > 0) {
                    foreach ($LeadContact as $User_key => $User_value) {
                        $UserResponse .= 'Contact - ' . $User_value['full_name'] . '<br>';
                    }
                }
            }

            if (count($UsersId) > 0) {
                $User = User::select('users.id', 'users.type', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
                $User->whereIn('users.id', $UsersId);
                $User = $User->get();
                if (count($User) > 0) {
                    foreach ($User as $User_key => $User_value) {
                        $UserResponse .= getAllUserTypes()[$User_value['type']]['short_name'] . ' - ' . $User_value['full_name'] . '<br>';
                    }
                }
            }

            if ($UserResponse) {
                $LeadMeetingClosed[$key]['meeting_participant'] = $UserResponse;
            } else {
                $LeadMeetingClosed[$key]['meeting_participant'] = '';
            }
        }

        $LeadTaskClosed = LeadTask::query();
        $LeadTaskClosed->select('lead_tasks.*', 'users.first_name', 'users.last_name');
        $LeadTaskClosed->where('lead_tasks.lead_id', $request->lead_id);
        $LeadTaskClosed->where('is_closed', 1);
        $LeadTaskClosed->leftJoin('users', 'users.id', '=', 'lead_tasks.user_id');
        $LeadTaskClosed->orderBy('lead_tasks.closed_date_time', 'desc');
        $LeadTaskClosed = $LeadTaskClosed->get();
        $LeadTaskClosed = json_encode($LeadTaskClosed);
        $LeadTaskClosed = json_decode($LeadTaskClosed, true);
        foreach ($LeadTaskClosed as $key => $value) {
            $LeadTaskClosed[$key]['date'] = convertDateAndTime($value['closed_date_time'], 'date');
            $LeadTaskClosed[$key]['time'] = convertDateAndTime($value['closed_date_time'], 'time');

            $Taskowner = User::select('users.id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"));
            // $Taskowner->where('users.status', 1);
            $Taskowner->where('users.id', $value['assign_to']);
            $Taskowner = $Taskowner->first();

            if ($Taskowner) {
                $LeadTaskClosed[$key]['task_owner'] = $Taskowner->text;
            } else {
                $LeadTaskClosed[$key]['task_owner'] = ' ';
            }
        }

        $countCallClosed = count($LeadCallClosed);
        $countTaskClosed = count($LeadTaskClosed);
        $countMeetingClosed = count($LeadMeetingClosed);

        $maxClosedAction = max($countCallClosed, $countTaskClosed, $countMeetingClosed);

        $data = [];
        $data['calls_closed'] = $LeadCallClosed;
        $data['tasks_closed'] = $LeadTaskClosed;
        $data['meetings_closed'] = $LeadMeetingClosed;
        $data['max_close_actions'] = $maxClosedAction;
        $data['lead_id'] = $request->lead_id;
        $response = successRes('All Close List');
        $response['view'] = view('crm/lead/detail_tab/detail_close_action_tab', compact('data'))->render();
        $response['lead_status'] = $lead_status_label;
        $response['lead_id'] = $request->lead_id;

        // $response['data'] = $data;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function changeStatus(Request $request)
    {
        $response = saveLeadAndDealStatus($request->id, $request->status, 'WEB');
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function saveStatus(Request $request)
    {
        $response = saveLeadAndDealStatus($request->lead_status_lead_id, $request->lead_status_new, 'WEB');

        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function editDetail(Request $request)
    {
        $Lead = Lead::find($request->id);
        if ($Lead) {
            $Lead = json_encode($Lead);
            $Lead = json_decode($Lead, true);

            $data['lead'] = $Lead;

            $LeadContact = LeadContact::query();
            $source_type = getLeadSourceTypes();
            $LeadContact->select('lead_contacts.first_name', 'lead_contacts.last_name', 'lead_contacts.contact_tag_id');
            $LeadContact->selectRaw('case when lead_contacts.contact_tag_id = 1 then "Client"
            when lead_contacts.contact_tag_id = 2 then "Architect"
            when lead_contacts.contact_tag_id = 3 then "Electrician"
            when lead_contacts.contact_tag_id = 4 then "Channel Partner"
            else "Undifine"
            end as source_type_label');
            $LeadContact->where('lead_contacts.lead_id', $data['lead']['id']);
            $LeadContact->where('lead_contacts.contact_tag_id', '!=', '1');
            $LeadContact->orderBy('lead_contacts.id', 'desc');
            $LeadContact = $LeadContact->get();
            $LeadContact = json_encode($LeadContact);
            $LeadContact = json_decode($LeadContact, true);

            $AssignUser = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"))
                ->where('users.id', $data['lead']['assigned_to'])
                ->first();
            $data['lead']['assign_person_name'] = '';
            if ($AssignUser) {
                $data['lead']['assign_person_name'] = $AssignUser->full_name;
            }

            $LeadSource = LeadSource::query();
            $LeadSource->select('source_type', 'source');
            $LeadSource->where('lead_id', $data['lead']['id']);
            $LeadSource->where('is_main', '!=', 1);
            $LeadSource->where('source', '!=', '');
            $LeadSource->orWhereNull('source');
            $LeadSourcelist = $LeadSource->get();

            $LeadSource_new = [];
            foreach ($LeadSourcelist as $source_key => $source_value) {
                if ($source_value->source_type != 0 || $source_value->source_type != null || $source_value->source_type != '') {
                    if ($source_value->source_type == 'user-201') {
                        $new_source['type_id'] = 202;
                    } elseif ($source_value->source_type == 'user-301') {
                        $new_source['type_id'] = 302;
                    } else {
                        $new_source['type_id'] = $source_value->source_type;
                    }

                    $source_type = explode('-', $source_value->source_type);
                    foreach (getLeadSourceTypes() as $source_type_key => $source_type_value) {
                        if ($source_type[1] == 201) {
                            $source_type_id = 202;
                        } elseif ($source_type[1] == 301) {
                            $source_type_id = 302;
                        } else {
                            $source_type_id = $source_type[1];
                        }

                        if ($source_type_value['type'] == $source_type[0] && $source_type_value['id'] == $source_type_id) {
                            $new_source['type_text'] = $source_type_value['lable'];
                            $new_source['source_type_is_editable'] = $source_type_value['is_editable'];
                            break;
                        }
                    }

                    if ($source_type[0] == 'user') {
                        if (isset(getChannelPartners()[$source_type[1]]['short_name'])) {
                            $new_source['val_id'] = $source_value->source;
                            // $new_source['val_text'] = ChannelPartner::select('firm_name')->where('user_id', $source_value->source)->first()->firm_name;
                            $val_text = ChannelPartner::select('firm_name')
                                ->where('user_id', $source_value->source)
                                ->first();
                            if ($val_text) {
                                $new_source['val_text'] = $val_text->firm_name;
                            } else {
                                $new_source['val_text'] = ' ';
                            }
                        } else {
                            $new_source['val_id'] = $source_value->source;
                            // $new_source['val_text'] = User::select(DB::raw("CONCAT(users.first_name,' ',users.last_name) AS name"))->where('id', $source_value->source)->first()->name;
                            $val_text = User::select(DB::raw("CONCAT(users.first_name,' ',users.last_name) AS name"))
                                ->where('id', $source_value->source)
                                ->first();
                            if ($val_text) {
                                $new_source['val_text'] = $val_text->name;
                            } else {
                                $new_source['val_text'] = ' ';
                            }
                        }
                    } elseif ($source_type[0] == 'master') {
                        $new_source['val_id'] = $source_value->source;
                        // $new_source['val_text'] = CRMSettingSource::select('name')->where('source_type_id', $source_value->source)->first()->name;
                        $val_text = CRMSettingSource::select('name')
                            ->where('source_type_id', $source_value->source)
                            ->first();
                        if ($val_text) {
                            $new_source['val_text'] = $val_text->name;
                        } else {
                            $new_source['val_text'] = ' ';
                        }
                    } elseif ($source_type[0] == 'exhibition') {
                        $Exhibition_data = Exhibition::find($data['lead']['source']);
                        $new_source['val_id'] = $Exhibition_data->id;
                        $new_source['val_text'] = $Exhibition_data->name;
                    } else {
                        $new_source['val_id'] = $source_value->source;
                        $new_source['val_text'] = $source_value->source;
                    }
                    array_push($LeadSource_new, $new_source);
                }
            }
            $data['lead']['no_of_source'] = $LeadSource->count();
            $data['lead']['add_more_source'] = $LeadSource_new;

            if ($data['lead']['source_type'] != 1) {
                $source_type_explode = explode('-', $data['lead']['source_type']);

                foreach (getLeadSourceTypes() as $key => $value) {
                    $source_type_id = $source_type_explode[1];
                    if ($source_type_id == 201) {
                        $source_type_id = 202;
                    } elseif ($source_type_id == 301) {
                        $source_type_id = 302;
                    }
                    if ($value['type'] == $source_type_explode[0] && $value['id'] == $source_type_id) {
                        $data['lead']['source_type_id'] = $value['type'] . '-' . $value['id'];
                        $data['lead']['source_type'] = $value['lable'];
                        $data['lead']['source_type_is_editable'] = $value['is_editable'];
                    }
                }
            } else {
                $data['lead']['source_type_id'] = '1';
                $data['lead']['source_type'] = 'Facebook';
                $data['lead']['source_type_is_editable'] = 0;
            }

            // if(str_contains($data['lead']['source_type_id'], '-')){
            // }else{
            //     $main_source_type = 'ankit-0';
            // }

            $main_sourceid = 0;
            $main_sourcename = '';
            if (isset($data['lead']['source_type_id']) && $data['lead']['source_type_id'] != '') {
                $main_source_type = explode('-', $data['lead']['source_type_id']);
                if ($main_source_type[0] == 'user') {
                    if (isset(getChannelPartners()[$main_source_type[1]]['short_name'])) {
                        $main_sourceid = $data['lead']['source'];
                        $val_text = ChannelPartner::select('firm_name')
                            ->where('user_id', $data['lead']['source'])
                            ->first();
                        if ($val_text) {
                            $main_sourcename = $val_text->firm_name;
                        } else {
                            $main_sourcename = ' ';
                        }
                    } else {
                        $main_sourceid = $data['lead']['source'];
                        $val_text = User::select(DB::raw("CONCAT(users.first_name,' ',users.last_name) AS name"))
                            ->where('id', $data['lead']['source'])
                            ->first();
                        if ($val_text) {
                            $main_sourcename = $val_text->name;
                        } else {
                            $main_sourcename = ' ';
                        }
                    }
                } elseif ($main_source_type[0] == 'master') {
                    $main_sourceid = $data['lead']['source'];
                    $val_text = CRMSettingSource::select('name')
                        ->where('source_type_id', $data['lead']['source'])
                        ->first();
                    if ($val_text) {
                        $main_sourcename = $val_text->name;
                    } else {
                        $main_sourcename = ' ';
                    }
                } elseif ($main_source_type[0] == 'exhibition') {
                    $Exhibition_data = Exhibition::find($data['lead']['source']);
                    $main_sourceid = $Exhibition_data->id;
                    $main_sourcename = $Exhibition_data->name;
                } else {
                    $main_sourceid = $data['lead']['source'];
                    $main_sourcename = $data['lead']['source'];
                }
            }
            $data['lead']['source_id'] = $main_sourceid;
            $data['lead']['source'] = $main_sourcename;

            if ($data['lead']['closing_date_time'] != null) {
                $lead_closing_date_time = $data['lead']['closing_date_time'];
                $lead_closing_date_time = date('Y-m-d H:i:s', strtotime($lead_closing_date_time . ' +5 hours'));
                $lead_closing_date_time = date('Y-m-d H:i:s', strtotime($lead_closing_date_time . ' +30 minutes'));
                $data['lead']['closing_date_time'] = date('Y-m-d', strtotime($lead_closing_date_time));
            }

            $electrician = User::select('users.id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"));
            // $electrician->where('users.status', 1);
            $electrician->where('users.id', $data['lead']['electrician']);
            $electrician = $electrician->first();
            $data['lead']['electrician'] = $electrician ? $electrician : '';

            $architect = User::select('users.id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"));
            // $architect->where('users.status', 1);
            $architect->where('users.id', $data['lead']['architect']);
            $architect = $architect->first();
            $data['lead']['architect'] = $architect ? $architect : '';

            $channel_partner = User::select('users.id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"));
            $channel_partner->where('users.id', $data['lead']['channel_partner']);
            $channel_partner = $channel_partner->first();
            $data['lead']['channel_partner'] = $channel_partner ? $channel_partner : '';

            $CityList = CityList::select('city_list.id', 'city_list.name as city_list_name', 'state_list.name as state_list_name');
            $CityList->leftJoin('state_list', 'state_list.id', '=', 'city_list.state_id');
            $CityList->where('city_list.id', $data['lead']['city_id']);
            $CityList = $CityList->first();
            if ($CityList) {
                $CityList = json_encode($CityList);
                $CityList = json_decode($CityList, true);

                $CityList['text'] = $CityList['city_list_name'] . ', ' . $CityList['state_list_name'] . ', India';

                $data['lead']['city'] = $CityList['text'];
            }

            $CityList = CityList::select('city_list.id', 'city_list.name as city_list_name', 'state_list.name as state_list_name');
            $CityList->leftJoin('state_list', 'state_list.id', '=', 'city_list.state_id');
            $CityList->where('city_list.id', $data['lead']['meeting_city_id']);
            $CityList = $CityList->first();

            if ($CityList) {
                $CityList = json_encode($CityList);
                $CityList = json_decode($CityList, true);
                $CityList['text'] = $CityList['city_list_name'] . ', ' . $CityList['state_list_name'] . ', India';
                $data['lead']['meeting_city'] = $CityList['text'];
            }

            if ($data['lead']['site_stage'] != 0) {
                $data['lead']['site_stage_id'] = $data['lead']['site_stage'];
                $CRMSettingStageOfSite = CRMSettingStageOfSite::select('id', 'name as text');
                $CRMSettingStageOfSite->where('crm_setting_stage_of_site.id', $data['lead']['site_stage']);
                $CRMSettingStageOfSite = $CRMSettingStageOfSite->first();
                if ($CRMSettingStageOfSite) {
                    $data['lead']['site_stage'] = $CRMSettingStageOfSite->text;
                }
            } else {
                $data['lead']['site_stage_id'] = '';
                $data['lead']['site_stage'] = '';
            }

            if ($data['lead']['site_type'] != 0) {
                $data['lead']['site_type_id'] = $data['lead']['site_type'];
                $CRMSettingSiteType = CRMSettingSiteType::select('id', 'name as text');
                $CRMSettingSiteType->where('crm_setting_site_type.id', $data['lead']['site_type']);
                $CRMSettingSiteType = $CRMSettingSiteType->first();
                if ($CRMSettingSiteType) {
                    $data['lead']['site_type'] = $CRMSettingSiteType->text;
                }
            } else {
                $data['lead']['site_type_id'] = '';
                $data['lead']['site_type'] = '';
            }

            if ($data['lead']['bhk'] != 0) {
                $data['lead']['bhk_id'] = $data['lead']['bhk'];
                $CRMSettingBHK = CRMSettingBHK::select('id', 'name as text');
                $CRMSettingBHK->where('crm_setting_bhk.id', $data['lead']['bhk']);
                $CRMSettingBHK = $CRMSettingBHK->first();
                if ($CRMSettingBHK) {
                    $data['lead']['bhk'] = $CRMSettingBHK->text;
                }
            } else {
                $data['lead']['bhk_id'] = '';
                $data['lead']['bhk'] = '';
            }

            if ($data['lead']['want_to_cover'] != 0) {
                $query_category = DB::table('crm_setting_want_to_cover');
                $query_category->select('crm_setting_want_to_cover.id AS id', 'crm_setting_want_to_cover.name AS text');
                $query_category->whereIn('crm_setting_want_to_cover.id', explode(',', $data['lead']['want_to_cover']));
                $data['lead']['want_to_cover'] = $query_category->get();
            } else {
                $data['lead']['want_to_cover'] = '';
            }

            if ($data['lead']['competitor'] != 0) {
                $query_category = DB::table('crm_setting_competitors');
                $query_category->select('crm_setting_competitors.id AS id', 'crm_setting_competitors.name AS text');
                $query_category->whereIn('crm_setting_competitors.id', explode(',', $data['lead']['competitor']));
                $data['lead']['competitor'] = $query_category->get();
            } else {
                $data['lead']['competitor'] = '';
            }

            if ($data['lead']['tag'] != 0) {
                $CRMLeadDealTag = TagMaster::query();
                $CRMLeadDealTag->where('tag_master.isactive', 1);
                $CRMLeadDealTag->where('tag_master.tag_type', 201);
                $CRMLeadDealTag->whereIn('tag_master.id', explode(',', $data['lead']['tag']));
                $CRMLeadDealTag = $CRMLeadDealTag->get();

                $tag = '';
                foreach ($CRMLeadDealTag as $key => $value) {
                    $tag .= $value['tagname'] . ', ';
                }
                $data['lead']['tag'] = rtrim($tag, ', ');
            } else {
                $data['lead']['tag'] = '';
            }

            $response = successRes('Get List');
            $response['data'] = $data['lead'];
        } else {
            $response = errorRes('Something went wrong');
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function updateDetail(Request $request)
    {
        $rules = [];
        $rules['lead_id'] = 'required';
        $rules['lead_site_stage'] = 'required';
        // $rules['lead_site_type'] = 'required';
        $rules['lead_source_type'] = 'required';
        // $rules['lead_source'] = 'required';
        $source_type = $request->all()['lead_source_type'];

        if (explode('-', $source_type)[0] == 'textrequired') {
            $rules['lead_source_text'] = 'required';
        } elseif (explode('-', $source_type)[0] == 'textnotrequired') {
            $rules['lead_source_text'] = 'required';
        } elseif (explode('-', $source_type)[0] == 'fix') {
            $rules['lead_source_text'] = 'required';
        } else {
            $rules['lead_source'] = 'required';
        }
        $rules['lead_status'] = 'required';

        $customMessage = [];
        $customMessage['lead_site_type.required'] = 'The lead Home Details field is required';

        $validator = Validator::make($request->all(), $rules, $customMessage);

        if ($validator->fails()) {
            $response = errorRes($validator->errors()->first());
            $response['data'] = $validator->errors();
        } elseif (isReception() == 1) {
            $response = errorRes("You Don't Have An Access");
        } else {
            $lead_closing_date_and_time = date('Y-m-d H:i:s', strtotime($request->lead_closing_date_time . date('H:i:s')));
            $new_closing_date = date('Y-m-d', strtotime($lead_closing_date_and_time));
            // if ($lead_closing_date_and_time != '' || $lead_closing_date_and_time != null) {
            // $lead_closing_date_time = $lead_closing_date_and_time;
            // $lead_closing_date_time = date('Y-m-d H:i:s', strtotime($lead_closing_date_time . " -5 hours"));
            // $lead_closing_date_time = date('Y-m-d H:i:s', strtotime($lead_closing_date_time . " -30 minutes"));
            // } else {
            $lead_closing_date_time = $lead_closing_date_and_time;

            $Lead = Lead::find($request->lead_id);

            if ($Lead) {
                $old_type_detail = $Lead->source_type . '-' . $Lead->source;

                $change_field = '';

                $old_closing_date = date('Y-m-d', strtotime($Lead->closing_date_time));

                $main_source_type = $request->lead_source_type;
                if (explode('-', $main_source_type)[0] == 'textrequired') {
                    $main_source = $request->lead_source_text;
                } elseif (explode('-', $main_source_type)[0] == 'textnotrequired') {
                    $main_source = $request->lead_source_text;
                } elseif (explode('-', $main_source_type)[0] == 'fix') {
                    $main_source = $request->lead_source_text;
                } else {
                    $main_source = $request->lead_source;
                }

                if ($request->lead_closing_date_time != '' || $request->lead_closing_date_time != null) {
                    if ($old_closing_date != $new_closing_date) {
                        $change_field .= ' | Closing Date Update : ' . $old_closing_date . ' To ' . $new_closing_date;
                        $Lead->closing_date_time = $lead_closing_date_time;
                        try {
                            $LeadClosing = new LeadClosing();
                            $LeadClosing->lead_id = $Lead->id;
                            $LeadClosing->closing_date = $lead_closing_date_time;
                            $LeadClosing->entryby = Auth::user()->id;
                            $LeadClosing->entryip = $request->ip();
                            $LeadClosing->save();
                        } catch (\Exception $e) {
                            $response_error['error_closingdate'] = errorRes($e->getMessage(), 400);
                        }
                    }
                }

                if ($Lead->source_type != $request->lead_source_type || $Lead->source != $main_source) {
                    // FIEND NEW SOURCE TYPE AND NAME
                    $new_source_type = $request->lead_source_type;
                    $new_source_value = $main_source;
                    $new_final_source_type = '';
                    $source_type = explode('-', $new_source_type);
                    foreach (getLeadSourceTypes() as $source_type_key => $source_type_value) {
                        if ($source_type[1] == 201) {
                            $source_type_id = 202;
                        } elseif ($source_type[1] == 301) {
                            $source_type_id = 302;
                        } else {
                            $source_type_id = $source_type[1];
                        }

                        if ($source_type_value['type'] == $source_type[0] && $source_type_value['id'] == $source_type_id) {
                            $new_final_source_type = $source_type_value['lable'];
                            break;
                        }
                    }

                    if ($source_type[0] == 'user') {
                        if (isset(getChannelPartners()[$source_type[1]]['short_name'])) {
                            $new_source['val_id'] = $new_source_value;
                            $val_text = ChannelPartner::select('firm_name')->where('user_id', $new_source_value)->first();
                            if ($val_text) {
                                $new_final_source_value = $val_text->firm_name;
                            } else {
                                $new_final_source_value = ' ';
                            }
                        } else {
                            $new_source['val_id'] = $new_source_value;
                            $val_text = User::select(DB::raw("CONCAT(users.first_name,' ',users.last_name) AS name"))->where('id', $new_source_value)->first();
                            if ($val_text) {
                                $new_final_source_value = $val_text->name;
                            } else {
                                $new_final_source_value = ' ';
                            }
                        }
                    } elseif ($source_type[0] == 'master') {
                        // $new_final_source_value = CRMSettingSource::select('name')->where('source_type_id', $new_source_value)->first()->name;
                        $val_text = CRMSettingSource::select('name')->where('source_type_id', $new_source_value)->first();
                        if ($val_text) {
                            $new_final_source_value = $val_text->name;
                        } else {
                            $new_final_source_value = ' ';
                        }
                    } else {
                        $new_final_source_value = $new_source_value;
                    }
                    // FIEND OLD SOURCE TYPE AND NAME
                    $old_source_type = $Lead->source_type;
                    $old_source_value = $Lead->source;
                    $old_final_source_type = '';
                    $source_type = explode('-', $old_source_type);
                    if (count($source_type) > 1) {
                        foreach (getLeadSourceTypes() as $source_type_key => $source_type_value) {
                            if ($source_type[1] == 201) {
                                $source_type_id = 202;
                            } elseif ($source_type[1] == 301) {
                                $source_type_id = 302;
                            } else {
                                $source_type_id = $source_type[1];
                            }

                            if ($source_type_value['type'] == $source_type[0] && $source_type_value['id'] == $source_type_id) {
                                $old_final_source_type = $source_type_value['lable'];
                                break;
                            }
                        }
                    }

                    if ($source_type[0] == 'user') {
                        if (isset(getChannelPartners()[$source_type[1]]['short_name'])) {
                            $val_text = ChannelPartner::select('firm_name')->where('user_id', $old_source_value)->first();
                            if ($val_text) {
                                $old_final_source_value = $val_text->firm_name;
                            } else {
                                $old_final_source_value = ' ';
                            }
                        } else {
                            $val_text = User::select(DB::raw("CONCAT(users.first_name,' ',users.last_name) AS name"))->where('id', $old_source_value)->first();
                            if ($val_text) {
                                $old_final_source_value = $val_text->name;
                            } else {
                                $old_final_source_value = ' ';
                            }
                        }
                    } elseif ($source_type[0] == 'master') {
                        $val_text = CRMSettingSource::select('name')->where('source_type_id', $old_source_value)->first();
                        if ($val_text) {
                            $old_final_source_value = $val_text->name;
                        } else {
                            $old_final_source_value = ' ';
                        }
                    } else {
                        $old_final_source_value = $old_source_value;
                    }

                    $change_field .= ' | Main Source Change : ' . $new_final_source_value . '(' . $new_final_source_type . ') TO ' . $old_final_source_value . '(' . $old_final_source_type . ')';
                }

                if ($Lead->site_stage != $request->lead_site_stage) {
                    $new_value = $request->lead_site_stage;
                    $old_value = $Lead->site_stage;

                    $New_Site_Stage = '';
                    if ($new_value != 0 || $new_value != '') {
                        $CRMSettingStageOfSite = CRMSettingStageOfSite::select('id', 'name as text');
                        $CRMSettingStageOfSite->where('crm_setting_stage_of_site.id', $new_value);
                        $CRMSettingStageOfSite = $CRMSettingStageOfSite->first();
                        if ($CRMSettingStageOfSite) {
                            $New_Site_Stage = $CRMSettingStageOfSite->text;
                        }
                    }
                    $Old_Site_Stage = '';
                    if ($old_value != 0 || $old_value != '') {
                        $CRMSettingStageOfSite = CRMSettingStageOfSite::select('id', 'name as text');
                        $CRMSettingStageOfSite->where('crm_setting_stage_of_site.id', $old_value);
                        $CRMSettingStageOfSite = $CRMSettingStageOfSite->first();
                        if ($CRMSettingStageOfSite) {
                            $Old_Site_Stage = $CRMSettingStageOfSite->text;
                        }
                    }

                    $change_field .= ' | Site Stage Change : ' . $Old_Site_Stage . ' To ' . $New_Site_Stage;
                }

                // if ($Lead->site_type != $request->lead_site_type) {
                //     $new_value = $request->lead_site_type;
                //     $old_value = $Lead->site_type;

                //     $New_Site_Type = '';
                //     if ($new_value != 0 || $new_value != '') {
                //         $CRMSettingSiteType = CRMSettingSiteType::select('id', 'name as text');
                //         $CRMSettingSiteType->where('crm_setting_site_type.id', $new_value);
                //         $CRMSettingSiteType = $CRMSettingSiteType->first();
                //         if ($CRMSettingSiteType) {
                //             $New_Site_Type = $CRMSettingSiteType->text;
                //         }
                //     }

                //     $Old_Site_Type = '';
                //     if ($old_value != 0 || $old_value != '') {
                //         $CRMSettingSiteType = CRMSettingSiteType::select('id', 'name as text');
                //         $CRMSettingSiteType->where('crm_setting_site_type.id', $old_value);
                //         $CRMSettingSiteType = $CRMSettingSiteType->first();
                //         if ($CRMSettingSiteType) {
                //             $Old_Site_Type = $CRMSettingSiteType->text;
                //         }
                //     }

                //     $change_field .= ' | Site Type Change : ' . $Old_Site_Type . ' To ' . $New_Site_Type;
                // }

                if (isset($request->lead_deal_tag)) {
                    $Tag_id = $request->lead_deal_tag;
                    if ($Lead->tag != implode(',', $Tag_id)) {
                        $new_value = $Tag_id;
                        $old_value = $Lead->tag;

                        $New_Tag = '';
                        if ($new_value != 0 && $new_value != '' && $new_value != null) {
                            $Tag = TagMaster::select('id', 'tagname as text');
                            $Tag->whereIn('tag_master.id', $new_value);
                            $Tag = $Tag->get();
                            if ($Tag) {
                                foreach ($Tag as $key => $value) {
                                    $New_Tag .= $value['text'];
                                    $New_Tag .= ', ';
                                }
                            }
                        }

                        $Old_Tag = '';
                        if ($old_value != 0 && $old_value != '' && $old_value != null) {
                            $Tag = TagMaster::select('id', 'tagname as text');
                            $Tag->whereIn('tag_master.id', explode(',', $old_value));
                            $Tag = $Tag->get();
                            if ($Tag) {
                                foreach ($Tag as $key => $value) {
                                    $Old_Tag .= $value['text'];
                                    $Old_Tag .= ', ';
                                }
                            }
                        }
                        $change_field .= ' | Tag Change : ' . $Old_Tag . ' To ' . $New_Tag;
                        $Lead->tag = implode(',', $Tag_id);
                    } else {
                        $change_field .= '';
                        $Lead->tag = '';
                    }
                } else {
                    $change_field .= '';
                    $Lead->tag = '';
                }

                $Lead->site_stage = $request->lead_site_stage;
                // $Lead->site_type = $request->lead_site_type;
                $Lead->source_type = $request->lead_source_type;
                $Lead->source = $main_source;
                if (isset($request->sub_status)) {
                    $Lead->sub_status = $request->sub_status;
                }
                $Lead->save();
                $RessaveLeadAndDealStatusInAction = '';
                if ($Lead) {
                    $RessaveLeadAndDealStatusInAction = saveLeadAndDealStatusInAction($Lead->id, $request->lead_status, $request->ip());
                    if ($RessaveLeadAndDealStatusInAction['timeline_id'] != 0 || $RessaveLeadAndDealStatusInAction['timeline_id'] != '') {
                        $LeadTimeline = LeadTimeline::find($RessaveLeadAndDealStatusInAction['timeline_id']);
                        $LeadTimeline->answer_ids = $request->answer_ids;
                        $LeadTimeline->save();
                    }
                    $new_type_detail = $Lead->source_type . '-' . $Lead->source;

                    $leadSource_id = LeadSource::select('id')
                        ->where('lead_sources.lead_id', $Lead->id)
                        ->where('lead_sources.is_main', 1)
                        ->first();
                    if ($leadSource_id) {
                        $LeadSource1 = LeadSource::find($leadSource_id->id);
                        $LeadSource1->lead_id = $request->lead_id;
                        $LeadSource1->source_type = $request->lead_source_type;
                        $LeadSource1->source = $main_source;
                        $LeadSource1->is_main = 1;
                        $LeadSource1->save();
                    }

                    if ($main_source_type == 'user-201' || $main_source_type == 'user-202' || $main_source_type == 'user-301' || $main_source_type == 'user-302' || $main_source_type == 'user-101' || $main_source_type == 'user-102' || $main_source_type == 'user-103' || $main_source_type == 'user-104' || $main_source_type == 'user-105') {
                        if ($main_source != 0 || $main_source != null || $main_source != '') {
                            if ($main_source != $Lead->electrician && $main_source != $Lead->architect) {
                                $Source_1 = User::where('id', $main_source)->first();

                                if ($Source_1) {
                                    $new_type_detail = 'user-' . $Source_1->type . '-' . $Source_1->id;

                                    $status_update_old = LeadContact::query();
                                    $status_update_old->where('lead_id', $Lead->id);
                                    $status_update_old->where('contact_tag_id', 0);
                                    $status_update_old->where('type_detail', $old_type_detail);
                                    $status_update_old = $status_update_old->first();

                                    if ($status_update_old) {
                                        $status_update_old->status = 0;
                                        $status_update_old->save();

                                        if ($status_update_old) {
                                            $status_update_new = LeadContact::query();
                                            $status_update_new->where('lead_id', $Lead->id);
                                            $status_update_new->where('contact_tag_id', 0);
                                            $status_update_new->where('type_detail', $new_type_detail);
                                            $status_update_new = $status_update_new->first();

                                            if ($status_update_new) {
                                                $status_update_new->status = 1;
                                                $status_update_new->save();
                                            } else {
                                                $LeadContact_s1 = new LeadContact();
                                                $LeadContact_s1->lead_id = $Lead->id;
                                                $LeadContact_s1->contact_tag_id = 0;
                                                if (isChannelPartner($Source_1->type) != 0) {
                                                    $ChannelPartner = ChannelPartner::find($Source_1->reference_id);
                                                    $LeadContact_s1->first_name = $ChannelPartner->firm_name;
                                                    $LeadContact_s1->last_name = '';
                                                } else {
                                                    $LeadContact_s1->first_name = $Source_1->first_name;
                                                    $LeadContact_s1->last_name = $Source_1->last_name;
                                                }
                                                $LeadContact_s1->phone_number = $Source_1->phone_number;
                                                $LeadContact_s1->alernate_phone_number = 0;
                                                $LeadContact_s1->email = $Source_1->email;
                                                $LeadContact_s1->type = $Source_1->type;
                                                $LeadContact_s1->type_detail = 'user-' . $Source_1->type . '-' . $Source_1->id;
                                                $LeadContact_s1->save();
                                            }
                                        }
                                    } else {
                                        $LeadContact_s1 = new LeadContact();
                                        $LeadContact_s1->lead_id = $Lead->id;
                                        $LeadContact_s1->contact_tag_id = 0;
                                        if (isChannelPartner($Source_1->type) != 0) {
                                            $ChannelPartner = ChannelPartner::find($Source_1->reference_id);
                                            $LeadContact_s1->first_name = $ChannelPartner->firm_name;
                                            $LeadContact_s1->last_name = '';
                                        } else {
                                            $LeadContact_s1->first_name = $Source_1->first_name;
                                            $LeadContact_s1->last_name = $Source_1->last_name;
                                        }
                                        $LeadContact_s1->phone_number = $Source_1->phone_number;
                                        $LeadContact_s1->alernate_phone_number = 0;
                                        $LeadContact_s1->email = $Source_1->email;
                                        $LeadContact_s1->type = $Source_1->type;
                                        $LeadContact_s1->type_detail = 'user-' . $Source_1->type . '-' . $Source_1->id;
                                        $LeadContact_s1->save();
                                    }
                                }
                            }
                        }
                    }
                }

                $leadStatus = getLeadStatus();

                if ($change_field != '') {
                    $timeline = [];
                    $timeline['lead_id'] = $Lead->id;
                    $timeline['type'] = 'lead-update';
                    $timeline['reffrance_id'] = $Lead->id;
                    $timeline['description'] = 'Lead Detail Updated ' . $change_field . ' by ' . Auth::user()->first_name . ' ' . Auth::user()->last_name;
                    $timeline['source'] = 'WEB';
                    saveLeadTimeline($timeline);
                }

                $response = successRes('Succssfully Update Detail');
                $response['data'] = $RessaveLeadAndDealStatusInAction;


                //     $response['tag_old'] = $Lead->tag;
            } else {
                $response = errorRes('Something went wrong');
            }
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function searchStatusInAction(Request $request)
    {
        $Lead = Lead::select('is_deal')
            ->where('id', $request->lead_id)
            ->first();
        $Lead_type = $Lead->is_deal;

        $searchKeyword = isset($request->q) ? $request->q : '';
        $type = isset($request->type) ? $request->type : 0;

        $LeadStatus = getLeadStatus();
        $finalArray[] = [];

        if ($Lead_type == 1) {
            foreach ($LeadStatus as $key => $value) {
                if ($value['type'] == 1) {
                    $countFinal = count($finalArray);
                    $finalArray[$countFinal] = [];
                    $finalArray[$countFinal]['id'] = $value['id'];
                    $finalArray[$countFinal]['text'] = $value['name'];
                }
            }
        } else {
            foreach ($LeadStatus as $key => $value) {
                if ($value['type'] == 0) {
                    $countFinal = count($finalArray);
                    $finalArray[$countFinal] = [];
                    $finalArray[$countFinal]['id'] = $value['id'];
                    $finalArray[$countFinal]['text'] = $value['name'];
                }
            }
        }

        $response = [];
        $response['results'] = $finalArray;
        $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function searchAdvanceFilterView(Request $request)
    {
        $searchKeyword = isset($request->q) ? $request->q : '';

        $data = CRMLeadAdvanceFilter::select('id', 'name as text', 'lead_advance_filter.is_public');
        $data->where('lead_advance_filter.is_deal', $request->is_deal);

        $data->where(function ($query) {
            $query->orWhere('lead_advance_filter.user_id', Auth::user()->id);
            $query->orWhere('lead_advance_filter.is_public', 1);
        });

        $data->where('lead_advance_filter.name', 'like', '%' . $searchKeyword . '%');
        $data->limit(10);
        $data = $data->get();

        $viewData = [];
        foreach ($data as $key => $value) {
            $viewData[$key] = [];
            $already_default_view_set = UserDefaultView::query();
            $already_default_view_set->where('user_id', Auth::user()->id);
            $already_default_view_set->where('filterview_id', $value->id);
            $already_default_view_set->where('default_type', 'user_wise');
            $already_default_view_set = $already_default_view_set->first();

            if ($value->is_public == 1 && Auth::user()->type != 0) {
                if ($already_default_view_set) {
                    $viewData[$key]['name'] = '<div class="border-bottom pb-2 pt-2"><label class="star-radio d-flex align-items-center justify-content-between"><div class="col-2 text-center"><input type="radio"  id="setViewAsFavorite_' . $value->id . '" name="setViewAsFavorite" value="' . $value->id . '" checked><span class="star" onclick="setViewAsFavorite(' . $value->id . ');"></span></div><div class="col-8 text-center" onclick="AdvanceFilterViewText(' . $value->id . ')"><span>' . $value->text . '</span></div><div class="Advance_Filter_View_Delete col-2 text-center" onclick="AdvanceFilterViewText(' . $value->id . ')"><span><i class="bx bxs-lock-alt" style="line-height: inherit !important;"></i></span></div></label></div>';
                } else {
                    $viewData[$key]['name'] = '<div class="border-bottom pb-2 pt-2"><label class="star-radio d-flex align-items-center justify-content-between"><div class="col-2 text-center"><input type="radio"  id="setViewAsFavorite_' . $value->id . '" name="setViewAsFavorite" value="' . $value->id . '"><span class="star" onclick="setViewAsFavorite(' . $value->id . ');"></span></div><div class="col-8 text-center" onclick="AdvanceFilterViewText(' . $value->id . ')"><span>' . $value->text . '</span></div><div class="Advance_Filter_View_Delete col-2 text-center" onclick="AdvanceFilterViewText(' . $value->id . ')"><span><i class="bx bxs-lock-alt" style="line-height: inherit !important;"></i></span></div></label></div>';
                }
            } else {
                if ($already_default_view_set) {
                    $viewData[$key]['name'] = '<div class="border-bottom pb-2 pt-2"><label class="star-radio d-flex align-items-center justify-content-between"><div class="col-2 text-center"><input type="radio"  id="setViewAsFavorite_' . $value->id . '" name="setViewAsFavorite" value="' . $value->id . '" checked><span class="star" onclick="setViewAsFavorite(' . $value->id . ');"></span></div><div class="col-8 text-center" onclick="AdvanceFilterViewText(' . $value->id . ')"><span>' . $value->text . '</span></div><div class="Advance_Filter_View_Delete col-2 text-center" onclick="AdvanceFilterDelete(' . $value->id . ')"><span><i class="bx bxs-trash-alt" style="line-height: inherit !important;"></i></span></div></label></div>';
                } else {
                    $viewData[$key]['name'] = '<div class="border-bottom pb-2 pt-2"><label class="star-radio d-flex align-items-center justify-content-between"><div class="col-2 text-center"><input type="radio"  id="setViewAsFavorite_' . $value->id . '" name="setViewAsFavorite" value="' . $value->id . '"><span class="star" onclick="setViewAsFavorite(' . $value->id . ');"></span></div><div class="col-8 text-center" onclick="AdvanceFilterViewText(' . $value->id . ')"><span>' . $value->text . '</span></div><div class="Advance_Filter_View_Delete col-2 text-center" onclick="AdvanceFilterDelete(' . $value->id . ')"><span><i class="bx bxs-trash-alt" style="line-height: inherit !important;"></i></span></div></label></div>';
                }
            }
        }

        $response = successRes('Get Advance Filter View');
        $response['data'] = $viewData;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function saveAdvanceFilter(Request $request)
    {
        $rules = [];
        $rules['view_name'] = 'required';
        $rules['arr_filter'] = 'required';
        $rules['isAdvanceFilter'] = 'required';

        $customMessage = [];
        $customMessage['lead_id.required'] = 'Invalid parameters';
        $customMessage['lead_first_name.required'] = 'Invalid type';
        $customMessage['user_first_name.required'] = 'Please enter first name';

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $response = errorRes($validator->errors()->first());
            $response['data'] = $validator->errors();
        } else {
            if ($request->isAdvanceFilter > 0) {
                $AdvanceFilter = new CRMLeadAdvanceFilter();
                $AdvanceFilter->user_id = Auth::user()->id;
                $AdvanceFilter->is_deal = $request->is_deal;
                $AdvanceFilter->name = $request->view_name;
                $AdvanceFilter->is_public = $request->is_public;
                $AdvanceFilter->created_ip = $request->ip();
                $AdvanceFilter->created_by = Auth::user()->id;
                $AdvanceFilter->save();

                if ($AdvanceFilter) {
                    try {
                        foreach ($request->arr_filter as $key => $filt_value) {
                            $column = getFilterColumnCRM()[$filt_value['column']];
                            $condtion = getFilterCondtionCRM()[$filt_value['condtion']];
                            $filter_value = '';
                            $source_type = '0';
                            if ($filt_value['clause'] == null || $filt_value['clause'] == '') {
                                $response = errorRes('Please Select Clause');
                                return response()->json($response)->header('Content-Type', 'application/json');
                                break;
                            } elseif ($filt_value['column'] == null || $filt_value['column'] == '') {
                                $response = errorRes('Please Select column');
                                return response()->json($response)->header('Content-Type', 'application/json');
                                break;
                            } elseif ($filt_value['condtion'] == null || $filt_value['condtion'] == '') {
                                $response = errorRes('Please Select condtion');
                                return response()->json($response)->header('Content-Type', 'application/json');
                                break;
                            } else {
                                if ($column['value_type'] == 'text') {
                                    if ($filt_value['value_text'] == null || $filt_value['value_text'] == '') {
                                        $response = errorRes('Please enter value');
                                        return response()->json($response)->header('Content-Type', 'application/json');
                                        break;
                                    } else {
                                        $filter_value = $filt_value['value_text'];
                                    }
                                } elseif ($column['value_type'] == 'select' && $condtion['value_type'] == 'single_select') {
                                    if ($column['code'] == 'leads_source' && ($filt_value['value_source_type'] == null || $filt_value['value_source_type'] == '')) {
                                        $response = errorRes('Please select value');
                                        return response()->json($response)->header('Content-Type', 'application/json');
                                        break;
                                    } else {
                                        $source_type = $filt_value['value_source_type'];
                                    }

                                    if ($filt_value['value_select'] == null || $filt_value['value_select'] == '') {
                                        $response = errorRes('Please select value');
                                        return response()->json($response)->header('Content-Type', 'application/json');
                                        break;
                                    } else {
                                        $filter_value = $filt_value['value_select'];
                                    }
                                } elseif ($column['value_type'] == 'select' && $condtion['value_type'] == 'multi_select') {
                                    if ($column['code'] == 'leads_source' && ($filt_value['value_source_type'] == null || $filt_value['value_source_type'] == '')) {
                                        $response = errorRes('Please select value');
                                        return response()->json($response)->header('Content-Type', 'application/json');
                                        break;
                                    } else {
                                        $source_type = $filt_value['value_source_type'];
                                    }
                                    if (!isset($filt_value['value_multi_select']) && empty($filt_value['value_multi_select'])) {
                                        $response = errorRes('Please select value');
                                        return response()->json($response)->header('Content-Type', 'application/json');
                                        break;
                                    } else {
                                        $filter_value = implode(',', $filt_value['value_multi_select']);
                                    }

                                    // if ($filt_value['value_multi_select'] == null || $filt_value['value_multi_select'] == '') {
                                    //     $response = errorRes("Please select value");
                                    //     return response()->json($response)->header('Content-Type', 'application/json');
                                    //     break;
                                    // } else {
                                    //     $filter_value = implode(",", $filt_value['value_multi_select']);
                                    // }
                                } elseif ($column['value_type'] == 'date' && $condtion['value_type'] == 'single_select') {
                                    if ($filt_value['value_date'] == null || $filt_value['value_date'] == '') {
                                        $response = errorRes('Please enter date');
                                        return response()->json($response)->header('Content-Type', 'application/json');
                                        break;
                                    } else {
                                        $filter_value = $filt_value['value_date'];
                                    }
                                } elseif ($column['value_type'] == 'date' && $condtion['value_type'] == 'between') {
                                    if (($filt_value['value_from_date'] == null || $filt_value['value_from_date'] == '') && ($filt_value['value_to_date'] == null || $filt_value['value_to_date'] == '')) {
                                        $response = errorRes('Please enter from to date');
                                        return response()->json($response)->header('Content-Type', 'application/json');
                                        break;
                                    } else {
                                        $filter_value = $filt_value['value_from_date'] . ',' . $filt_value['value_to_date'];
                                    }
                                }

                                $AdvanceFilterItem = new CRMLeadAdvanceFilterItem();
                                $AdvanceFilterItem->user_id = Auth::user()->id;
                                $AdvanceFilterItem->is_deal = $AdvanceFilter->is_deal;
                                $AdvanceFilterItem->advance_filter_id = $AdvanceFilter->id;
                                if ($filt_value['clause'] == 0) {
                                    $AdvanceFilterItem->clause_id = 0;
                                } else {
                                    $AdvanceFilterItem->clause_id = $filt_value['clause'];
                                }
                                $AdvanceFilterItem->column_id = $filt_value['column'];
                                $AdvanceFilterItem->condition_id = $filt_value['condtion'];
                                $AdvanceFilterItem->value = $filter_value;
                                $AdvanceFilterItem->source_type = $source_type;
                                $AdvanceFilterItem->created_by = Auth::user()->id;
                                $AdvanceFilterItem->created_ip = $request->ip();
                                $AdvanceFilterItem->save();
                            }
                        }
                        $response = successRes('Filter View Saved Successfully');
                    } catch (\Exception $e) {
                        $response = errorRes($e->getMessage(), 400);
                    }
                }
            }
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function searchFilterValue(Request $request)
    {
        $data = [];
        $rules = [];
        $rules['is_deal'] = 'required';
        $rules['column'] = 'required';
        $rules['condtion'] = 'required';
        // $rules['source_type'] = 'required';

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $response = errorRes($validator->errors()->first());
            $response['data'] = $validator->errors();
        } else {
            $isSalePerson = isSalePerson();
            $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
            $isThirdPartyUser = isThirdPartyUser();
            $isTeleSales = isTaleSalesUser();
            $isChannelPartner = isChannelPartner(Auth::user()->type);
            if ($isSalePerson == 1) {
                $childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
            }

            $Search_Value = $request->q;
            $Lead_type = $request->is_deal;
            $Filter_Column = getFilterColumnCRM()[$request->column];
            $Filter_Condtion = getFilterCondtionCRM()[$request->condtion];
            $LeadStatus = getLeadStatus();
            $LeadBillstatus = getLeadBillstatus();

            $data = [];
            switch ($Filter_Column['value_type']) {
                case 'select':
                    switch ($Filter_Column['code']) {
                        case 'leads_status':
                            foreach ($LeadStatus as $key => $value) {
                                $new_data = [];
                                if ($value['type'] == $Lead_type) {
                                    if ($Search_Value != '') {
                                        if (preg_match('/' . $Search_Value . '/i', $value['name'])) {
                                            $new_data['id'] = $value['id'];
                                            $new_data['text'] = $value['name'];
                                        }
                                    } else {
                                        $new_data['id'] = $value['id'];
                                        $new_data['text'] = $value['name'];
                                    }
                                }
                                array_push($data, $new_data);
                            }
                            break;

                        case 'leads_site_stage':
                            $data = CRMSettingStageOfSite::select('id', 'name as text');
                            $data->where('crm_setting_stage_of_site.status', 1);
                            $data->where('crm_setting_stage_of_site.name', 'like', '%' . $Search_Value . '%');
                            $data->limit(10);
                            $data = $data->get();
                            break;

                        case 'leads_competitor':
                            $data = CRMSettingCompetitors::select('id', 'name as text');
                            $data->where('crm_setting_competitors.status', 1);
                            $data->where('crm_setting_competitors.name', 'like', '%' . $Search_Value . '%');
                            $data->limit(10);
                            $data = $data->get();
                            break;

                        case 'leads_want_to_cover':
                            $data = CRMSettingWantToCover::select('id', 'name as text');
                            $data->where('crm_setting_want_to_cover.status', 1);
                            $data->where('crm_setting_want_to_cover.name', 'like', '%' . $Search_Value . '%');
                            $data->limit(10);
                            $data = $data->get();
                            break;

                        case 'leads_assigned_to':
                            $User = User::select('users.id', 'users.type', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));

                            // $User->where('users.status', 1);

                            if ($isAdminOrCompanyAdmin == 1) {
                                $User->whereIn('users.type', [2]);
                                // $User->whereIn('users.type', array(0, 1, 2));
                            } elseif ($isThirdPartyUser == 1) {
                                $User->whereIn('users.type', [2]);
                                $User->where('users.city_id', Auth::user()->city_id);
                            } elseif ($isSalePerson == 1) {
                                $User->where('users.type', 2);
                                $User->whereIn('users.id', $childSalePersonsIds);
                            } elseif ($isChannelPartner != 0) {
                                $User->where('users.type', 2);
                                $User->where('users.city_id', Auth::user()->city_id);
                            } elseif ($isTeleSales == 1) {
                                $User->whereIn('users.type', [2]);
                            }

                            $User->where(function ($query) use ($Search_Value) {
                                $query->where('users.first_name', 'like', '%' . $Search_Value . '%');
                                $query->orWhere('users.last_name', 'like', '%' . $Search_Value . '%');
                                $query->orWhereRaw("CONCAT(users.first_name,' ',users.last_name)" . ' like ?', ['%' . $Search_Value . '%']);
                            });

                            $User->limit(10);
                            $User = $User->get();

                            if (count($User) > 0) {
                                foreach ($User as $User_key => $User_value) {
                                    $label = ' - ' . getUserTypeMainLabel($User_value->type);
                                    $new_data['id'] = $User_value['id'];
                                    $new_data['text'] = $User_value['full_name'] . $label;

                                    array_push($data, $new_data);
                                }
                            }
                            break;

                        case 'leads_created_by':
                            $User = User::select('users.id', 'users.type', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));

                            // $User->where('users.status', 1);

                            if ($isAdminOrCompanyAdmin == 1) {
                                $User->whereIn('users.type', [0, 1, 2]);
                                // $User->whereIn('users.type', array(0, 1, 2));
                            } elseif ($isSalePerson == 1) {
                                $User->whereIn('users.type', [0, 1, 2]);
                                $User->whereIn('users.id', $childSalePersonsIds);
                            } elseif ($isChannelPartner != 0) {
                                $User->whereIn('users.type', [0, 1, 2]);
                                $User->where('users.city_id', Auth::user()->city_id);
                            }

                            $User->where(function ($query) use ($Search_Value) {
                                $query->where('users.first_name', 'like', '%' . $Search_Value . '%');
                                $query->orWhere('users.last_name', 'like', '%' . $Search_Value . '%');
                                $query->orWhereRaw("CONCAT(users.first_name,' ',users.last_name)" . ' like ?', ['%' . $Search_Value . '%']);
                            });

                            $User->limit(10);
                            $User = $User->get();

                            if (count($User) > 0) {
                                foreach ($User as $User_key => $User_value) {
                                    $label = ' - ' . getUserTypeMainLabel($User_value->type);
                                    $new_data['id'] = $User_value['id'];
                                    $new_data['text'] = $User_value['full_name'] . $label;

                                    array_push($data, $new_data);
                                }
                            }
                            break;

                        case 'leads_source':
                            // $User = User::select('users.id', 'channel_partner.firm_name', 'users.type', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
                            // $User->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
                            // $User->where('users.status', 1);
                            // if ($isAdminOrCompanyAdmin == 1) {
                            //     $User->whereIn('users.type', [101, 102, 103, 104, 105, 201, 202, 301, 302]);
                            //     // $User->whereIn('users.type', array(0, 1, 2));
                            // } else if ($isSalePerson == 1) {
                            //     $User->whereIn('users.type', [101, 102, 103, 104, 105, 201, 202, 301, 302]);
                            //     $User->whereIn('users.id', $childSalePersonsIds);
                            // } else if ($isChannelPartner != 0) {
                            //     $User->whereIn('users.type', [101, 102, 103, 104, 105, 201, 202, 301, 302]);
                            //     $User->where('users.city_id', Auth::user()->city_id);
                            // }
                            // $User->where(function ($query) use ($Search_Value) {
                            //     $query->where('users.first_name', 'like', '%' . $Search_Value . '%');
                            //     $query->orWhere('users.last_name', 'like', '%' . $Search_Value . '%');
                            // });
                            // $User->limit(10);
                            // $User = $User->get();
                            // if (count($User) > 0) {
                            //     foreach ($User as $User_key => $User_value) {
                            //         $label = ' - ' . ucwords(strtolower(getUserTypeMainLabel($User_value->type)));
                            //         $new_data['id'] = $User_value['id'];
                            //         if (isset(getChannelPartners()[$User_value->type]['short_name'])) {
                            //             $new_data['text'] = $User_value['firm_name'] . $label;
                            //         } else {
                            //             $new_data['text'] = $User_value['full_name'] . $label;
                            //         }
                            //         array_push($data, $new_data);
                            //     }
                            // }

                            $isArchitect = isArchitect();
                            $isSalePerson = isSalePerson();
                            $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
                            $isThirdPartyUser = isThirdPartyUser();
                            $isChannelPartner = isChannelPartner(Auth::user()->type);

                            if ($request->source_type == '' || $request->source_type == null) {
                                $response = errorRes('please select source type');
                                return response()->json($response)->header('Content-Type', 'application/json');
                            }
                            $source_type = explode('-', $request->source_type);

                            if ($source_type[0] == 'user') {
                                if (isset(getChannelPartners()[$source_type[1]]['short_name'])) {
                                    if ($isSalePerson == 1) {
                                        $childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);

                                        $salePerson = SalePerson::select('cities')
                                            ->where('user_id', Auth::user()->id)
                                            ->first();
                                        $cities = [];
                                        if ($salePerson) {
                                            $cities = explode(',', $salePerson->cities);
                                        } else {
                                            $cities = [0];
                                        }
                                    }

                                    $data = User::select('users.id', 'channel_partner.firm_name  AS text', 'users.phone_number');
                                    $data->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
                                    // $data->where('users.status', 1);
                                    $data->where('users.type', $source_type[1]);

                                    if ($isSalePerson == 1) {
                                        $data->where(function ($query) use ($cities, $childSalePersonsIds) {
                                            $query->whereIn('users.city_id', $cities);

                                            $query->orWhere(function ($query2) use ($childSalePersonsIds) {
                                                foreach ($childSalePersonsIds as $key => $value) {
                                                    if ($key == 0) {
                                                        $query2->whereRaw('FIND_IN_SET("' . $value . '",channel_partner.sale_persons)>0');
                                                    } else {
                                                        $query2->orWhereRaw('FIND_IN_SET("' . $value . '",channel_partner.sale_persons)>0');
                                                    }
                                                }
                                            });
                                        });
                                    }

                                    $data->where(function ($query) use ($Search_Value) {
                                        $query->where('channel_partner.firm_name', 'like', '%' . $Search_Value . '%');
                                    });
                                    $data->limit(5);
                                    $data = $data->get();
                                } else {
                                    $data = User::select('users.id', 'users.type', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"), 'users.phone_number');
                                    // $data->where('users.status', 1);

                                    if ($source_type[1] == 202) {
                                        // FOR ARCHITECT
                                        if ($isSalePerson == 1) {
                                            $salePerson = SalePerson::select('cities')
                                                ->where('user_id', Auth::user()->id)
                                                ->first();
                                            $cities = [];
                                            if ($salePerson) {
                                                $cities = explode(',', $salePerson->cities);
                                            } else {
                                                $cities = [0];
                                            }
                                            $data->whereIn('users.city_id', $cities);
                                        } elseif ($isChannelPartner != 0) {
                                            $data->where('users.city_id', Auth::user()->city_id);
                                        }

                                        $data->whereIn('users.type', [201, 202]);
                                    } elseif ($source_type[1] == 302) {
                                        // FOR ELECTRICIAN
                                        if ($isSalePerson == 1) {
                                            $salePerson = SalePerson::select('cities')
                                                ->where('user_id', Auth::user()->id)
                                                ->first();
                                            $cities = [];
                                            if ($salePerson) {
                                                $cities = explode(',', $salePerson->cities);
                                            } else {
                                                $cities = [0];
                                            }
                                            $data->whereIn('users.city_id', $cities);
                                        } elseif ($isChannelPartner != 0) {
                                            $data->where('users.city_id', Auth::user()->city_id);
                                        }

                                        $data->whereIn('users.type', [301, 302]);
                                    } else {
                                        $data->where('users.type', $source_type[1]);
                                    }

                                    $data->where(function ($query) use ($Search_Value) {
                                        $query->where('users.first_name', 'like', '%' . $Search_Value . '%');
                                        $query->orWhere('users.last_name', 'like', '%' . $Search_Value . '%');
                                        $query->orWhere('users.phone_number', 'like', '%' . $Search_Value . '%');
                                        $query->orWhereRaw("CONCAT(users.first_name,' ',users.last_name)" . ' like ?', ['%' . $Search_Value . '%']);
                                    });

                                    $data->limit(5);
                                    $data = $data->get();
                                    $newdata = [];
                                    foreach ($data as $key => $value) {
                                        $data1['id'] = $value->id;
                                        $label = '';
                                        if ($value->type == 301 || $value->type == 201) {
                                            $label = ' - NonPrime';
                                        } elseif ($value->type == 302 || $value->type == 202) {
                                            $label = ' - Prime';
                                        } else {
                                            $label = '';
                                        }
                                        $data1['text'] = $value->text . '(' . $value->phone_number . ')' . $label;
                                        $data1['phone_number'] = $value->phone_number;
                                        array_push($newdata, $data1);
                                    }
                                    $data = $newdata;
                                }
                            } elseif ($source_type[0] == 'master') {
                                $data = CRMSettingSource::select('id', 'name as text');
                                $data->where('crm_setting_source.status', 1);
                                $data->where('crm_setting_source.source_type_id', $source_type[1]);
                                $data->where('crm_setting_source.name', 'like', '%' . $Search_Value . '%');
                                $data->limit(5);
                                $data = $data->get();
                            } elseif ($source_type[0] == 'exhibition') {
                                $data = Exhibition::select('id', 'name as text');
                                $data->where('exhibition.name', 'like', '%' . $Search_Value . '%');
                                $data->limit(5);
                                $data = $data->get();
                            } else {
                                $data = '';
                            }
                            break;
                        case 'leads_tag':
                            $data = Tags::select('id', 'tagname as text');
                            $data->where('tag_master.isactive', 1);
                            $data->where('tag_master.tag_type', 201);
                            $data->where('tag_master.tagname', 'like', '%' . $Search_Value . '%');
                            $data->limit(10);
                            $data = $data->get();

                            break;
                        case 'leads_service_verification':
                            $data = [];
                            foreach (verificationStatus() as $key => $value) {
                                $data_new['id'] = $value['id'];
                                $data_new['text'] = $value['service_code'];
                                array_push($data, $data_new);
                            }
                            break;
                        case 'leads_telesales_verification':
                            $data = [];
                            foreach (verificationStatus() as $key => $value) {
                                $data_new['id'] = $value['id'];
                                $data_new['text'] = $value['telesales_code'];
                                array_push($data, $data_new);
                            }
                            break;
                        case 'leads_city_id':
                            $data = CityList::select('id', 'name as text');
                            $data->where('status', 1);
                            $data->where('name', 'like', '%' . $Search_Value . '%');
                            $data->limit(10);
                            $data = $data->get();

                            break;
                        case 'leads_companyadmin_verification':
                            $data = [];
                            foreach (verificationStatus() as $key => $value) {
                                $data_new['id'] = $value['id'];
                                $data_new['text'] = $value['company_admin_code'];
                                array_push($data, $data_new);
                            }
                            break;
                        case 'lead_miss_data':
                            $data = [];
                            foreach (getLeadFilterMissFilterValue() as $key => $value) {
                                $data_new['id'] = $value['id'];
                                $data_new['text'] = $value['name'];
                                array_push($data, $data_new);
                            }
                            break;
                        case 'lead_files_bills_status':
                            foreach ($LeadBillstatus as $key => $value) {
                                $new_data = [];
                                if ($Search_Value != '') {
                                    if (preg_match('/' . $Search_Value . '/i', $value['name'])) {
                                        $new_data['id'] = $value['id'];
                                        $new_data['text'] = $value['name'];
                                    }
                                } else {
                                    $new_data['id'] = $value['id'];
                                    $new_data['text'] = $value['name'];
                                }
                                array_push($data, $new_data);
                            }
                            break;

                        default:
                            $data = errorRes();
                            break;
                    }

                    break;

                case 'date':
                    switch ($Filter_Column['code']) {
                        case 'leads_closing_date_time':
                            foreach (getDateFilterValue() as $key => $value) {
                                $new_data = [];
                                if ($Search_Value != '') {
                                    if (preg_match('/' . $Search_Value . '/i', $value['name'])) {
                                        $new_data['id'] = $value['id'];
                                        $new_data['text'] = $value['name'];
                                    }
                                } else {
                                    $new_data['id'] = $value['id'];
                                    $new_data['text'] = $value['name'];
                                }
                                array_push($data, $new_data);
                            }
                            break;

                        case 'leads_created_at':
                            foreach (getDateFilterValue() as $key => $value) {
                                $new_data = [];
                                if ($Search_Value != '') {
                                    if (preg_match('/' . $Search_Value . '/i', $value['name'])) {
                                        $new_data['id'] = $value['id'];
                                        $new_data['text'] = $value['name'];
                                    }
                                } else {
                                    $new_data['id'] = $value['id'];
                                    $new_data['text'] = $value['name'];
                                }
                                array_push($data, $new_data);
                            }
                            break;

                        default:
                            $data = errorRes();
                            break;
                    }

                    break;

                case 'reward_select':
                    switch ($Filter_Column['code']) {
                        case 'lead_files_hod_approved':
                            foreach (getRewardValue() as $key => $value) {
                                $new_data = [];

                                $new_data['id'] = $value['id'];
                                $new_data['text'] = $value['name'];
                                array_push($data, $new_data);
                            }
                            break;

                        default:
                            $data = errorRes();
                            break;
                    }
                    break;
                default:
                    break;
            }
        }

        $response = [];
        $response['results'] = $data;
        $response['pagination']['more'] = true;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function searchAdvanceFilterCondition(Request $request)
    {
        $data = [];
        $rules = [];
        $rules['column'] = 'required';

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $response = errorRes($validator->errors()->first());
            $response['data'] = $validator->errors();
        } else {
            $Search_Value = $request->q;
            $Filter_Column = getFilterColumnCRM()[$request->column];
            $Filter_Condtion = getFilterCondtionCRM();

            $data = [];
            switch ($Filter_Column['value_type']) {
                case 'date':
                    foreach ($Filter_Condtion as $key => $value) {
                        $new_data = [];
                        if ($value['code'] == 'is' || $value['code'] == 'between') {
                            if ($Search_Value != '') {
                                if (preg_match('/' . $Search_Value . '/i', $value['name'])) {
                                    $new_data['id'] = $value['id'];
                                    $new_data['text'] = $value['name'];
                                }
                            } else {
                                $new_data['id'] = $value['id'];
                                $new_data['text'] = $value['name'];
                            }
                        }
                        array_push($data, $new_data);
                    }
                    break;
                case 'reward_select':
                    foreach ($Filter_Condtion as $key => $value) {
                        $new_data = [];
                        if ($value['code'] == 'is') {
                            if ($Search_Value != '') {
                                if (preg_match('/' . $Search_Value . '/i', $value['name'])) {
                                    $new_data['id'] = $value['id'];
                                    $new_data['text'] = $value['name'];
                                }
                            } else {
                                $new_data['id'] = $value['id'];
                                $new_data['text'] = $value['name'];
                            }
                        }
                        array_push($data, $new_data);
                    }
                    break;
                default:
                    foreach ($Filter_Condtion as $key => $value) {
                        $new_data = [];
                        if ($value['value_type'] == 'single_select' || $value['value_type'] == 'multi_select') {
                            if ($Search_Value != '') {
                                if (preg_match('/' . $Search_Value . '/i', $value['name'])) {
                                    $new_data['id'] = $value['id'];
                                    $new_data['text'] = $value['name'];
                                }
                            } else {
                                $new_data['id'] = $value['id'];
                                $new_data['text'] = $value['name'];
                            }
                        }
                        array_push($data, $new_data);
                    }
                    break;
            }
        }

        $response = [];
        $response['results'] = $data;
        $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function getDetailAdvanceFilter(Request $request)
    {
        $rules = [];
        $rules['view_id'] = 'required';

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $response = errorRes($validator->errors()->first());
            $response['data'] = $validator->errors();
        } else {
            $viewid = $request->view_id;
            $LeadStatus = getLeadStatus();
            $lstDateFilter = getDateFilterValue();

            $Filter = CRMLeadAdvanceFilter::find($viewid);

            if ($Filter) {
                // try {
                $data = [];
                $filteriteam = CRMLeadAdvanceFilterItem::query()
                    ->where('advance_filter_id', $Filter->id)
                    ->get();
                if (count($filteriteam) > 0) {
                    foreach ($filteriteam as $key => $filt_value) {
                        if ($filt_value['clause_id'] == 0) {
                            $clause = [];
                            $clause['id'] = 0;
                            $clause['name'] = 'WHERE';
                            $clause['clause'] = 'where';
                        } else {
                            $clause = getFilterClauseCRM()[$filt_value['clause_id']];
                        }
                        $column = getFilterColumnCRM()[$filt_value['column_id']];
                        $condtion = getFilterCondtionCRM()[$filt_value['condition_id']];

                        $filtval = $filt_value['value'];

                        $new_data['clause_id'] = $clause['id'];
                        $new_data['clause_text'] = $clause['name'];

                        $new_data['column_id'] = $column['id'];
                        $new_data['column_text'] = $column['name'];
                        $new_data['column_valtype'] = $column['value_type'];

                        $new_data['condtion_id'] = $condtion['id'];
                        $new_data['condtion_text'] = $condtion['name'];
                        $new_data['condtion_valtype'] = $condtion['value_type'];

                        $arrclause = [];
                        $User = [];

                        $new_source_type_id = '';
                        $new_source_type_text = '';
                        $new_data['source_type_database'] = $filt_value['source_type'];
                        if ($filt_value['source_type'] != '0' && $filt_value['source_type'] != null) {
                            $new_source_type_id = $filt_value['source_type'];
                            $new_source_type_text = '';

                            $source_type = explode('-', $filt_value['source_type']);
                            foreach (getLeadSourceTypes() as $source_type_key => $source_type_value) {
                                if ($source_type[1] == 201) {
                                    $source_type_id = 202;
                                } elseif ($source_type[1] == 301) {
                                    $source_type_id = 302;
                                } else {
                                    $source_type_id = $source_type[1];
                                }

                                if ($source_type_value['type'] == $source_type[0] && $source_type_value['id'] == $source_type_id) {
                                    $new_source_type_text = $source_type_value['lable'];
                                    break;
                                }
                            }
                        }

                        $new_data['source_type_id'] = $new_source_type_id;
                        $new_data['source_type_text'] = $new_source_type_text;

                        if ($column['value_type'] == 'select' && $column['code'] == 'leads_status') {
                            if ($condtion['value_type'] == 'single_select') {
                                $new_valdata['id'] = $LeadStatus[$filtval]['id'];
                                $new_valdata['text'] = $LeadStatus[$filtval]['name'];
                                array_push($arrclause, $new_valdata);
                            } elseif ($condtion['value_type'] == 'multi_select') {
                                foreach (explode(',', $filtval) as $key => $val) {
                                    $new_valdata['id'] = $LeadStatus[$val]['id'];
                                    $new_valdata['text'] = $LeadStatus[$val]['name'];
                                    array_push($arrclause, $new_valdata);
                                }
                            }
                        } elseif ($column['value_type'] == 'select') {
                            if ($condtion['value_type'] == 'single_select') {
                                $User = User::select('users.id', 'channel_partner.firm_name', 'users.type', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"));
                                $User->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
                                $User->where('users.status', 1);
                                $User->where('users.id', $filtval);
                                $User = $User->first();

                                if ($User) {
                                    $label = ' - ' . ucwords(strtolower(getUserTypeMainLabel($User->type)));
                                    $new_valdata['id'] = $User['id'];
                                    if (isset(getChannelPartners()[$User->type]['short_name'])) {
                                        $new_valdata['text'] = $User['firm_name'] . $label;
                                    } else {
                                        $new_valdata['text'] = $User['text'] . $label;
                                    }
                                    array_push($arrclause, $new_valdata);
                                } else {
                                    $new_valdata['id'] = 0;
                                    $new_valdata['text'] = 'undifine';
                                    array_push($arrclause, $new_valdata);
                                }
                            } elseif ($condtion['value_type'] == 'multi_select') {
                                $User = User::select('users.id', 'channel_partner.firm_name', 'users.type', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS name"));
                                $User->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
                                $User->whereIn('users.id', explode(',', $filtval));
                                $User = $User->get();

                                if (count($User) > 1) {
                                    foreach ($User as $keyv => $valdata) {
                                        $label = ' - ' . ucwords(strtolower(getUserTypeMainLabel($valdata['type'])));
                                        $new_valdata['id'] = $valdata['id'];
                                        if (isset(getChannelPartners()[$valdata->type]['short_name'])) {
                                            $new_valdata['text'] = $valdata['firm_name'] . $label;
                                        } else {
                                            $new_valdata['text'] = $valdata['name'] . $label;
                                        }
                                        array_push($arrclause, $new_valdata);
                                    }
                                } else {
                                    $new_valdata['id'] = 0;
                                    $new_valdata['text'] = 'undifine';
                                    array_push($arrclause, $new_valdata);
                                }
                            }
                        } elseif ($column['value_type'] == 'date' && $column['code'] == 'leads_closing_date_time' && $condtion['value_type'] == 'single_select') {
                            $new_valdata['id'] = $lstDateFilter[$filtval]['id'];
                            $new_valdata['text'] = $lstDateFilter[$filtval]['name'];
                            array_push($arrclause, $new_valdata);
                        } else {
                            $new_valdata['id'] = 0;
                            $new_valdata['text'] = $filtval;
                            array_push($arrclause, $new_valdata);
                        }

                        $new_data['value'] = $arrclause;
                        array_push($data, $new_data);
                    }
                }
                $response = successRes('Filter View Successfully');
                $response['filter'] = $Filter;
                $response['filter_item'] = $data;
                // } catch (\Exception $e) {
                //     $response = errorRes($e->getMessage(), 400);
                // }
            } else {
                $response = errorRes('Please Contact Admin');
                $response['filter'] = 0;
                $response['filter_item'] = '';
            }
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function changeFinalQuotation(Request $request)
    {
        $rules = [];
        $rules['quotid'] = 'required';
        $rules['quotgroupid'] = 'required';

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $response = errorRes($validator->errors()->first());
            $response['data'] = $validator->errors();
        } else {
            if ($request->quotid != 0) {
                // $quot_isfinal_update = Wltrn_Quotation::where('quotgroup_id', $request->quotgroupid);
                // $quot_isfinal_update->update(['isfinal' => 0]);

                $QuotMaster = Wltrn_Quotation::find($request->quotid);

                Wltrn_Quotation::where('inquiry_id', $QuotMaster->inquiry_id)->update(['isfinal' => 0]);

                $QuotMaster->updateby = Auth::user()->id;
                $QuotMaster->updateip = $request->ip();
                $QuotMaster->isfinal = 1;
                $QuotMaster->save();

                if ($QuotMaster) {
                    $response = successRes('Final Quotation Updated ✅');

                    $DebugLog = new DebugLog();
                    $DebugLog->user_id = 1;
                    $DebugLog->name = 'quotation-isfinal-change';
                    $DebugLog->description = 'Quotation #' . $QuotMaster->id . '(' . $QuotMaster->quotgroup_id . ')' . ' isfinal has been change Successfully';
                    $DebugLog->save();
                } else {
                    $response = errorRes('Final quotation has not been updated');
                }
            } else {
                $response = errorRes('Final quotation has not been updated');
            }

            return response()->json($response)->header('Content-Type', 'application/json');
        }
    }

    function saveViewAsDefault(Request $request)
    {
        $rules = [];
        $rules['view_id'] = 'required';

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $response = errorRes($validator->errors()->first());
            $response['data'] = $validator->errors();
        } else {
            if ($request->view_id != '' && $request->view_id != 0) {
                $already_default_view_set = UserDefaultView::query();
                $already_default_view_set->where('user_id', Auth::user()->id);
                $already_default_view_set->where('default_type', 'user_wise');
                $already_default_view_set = $already_default_view_set->first();
                if ($already_default_view_set) {
                    $saveUserDefaultView = UserDefaultView::find($already_default_view_set->id);
                    $saveUserDefaultView->updateby = Auth::user()->id;
                    $saveUserDefaultView->updateip = $request->ip();
                } else {
                    $saveUserDefaultView = new UserDefaultView();
                    $saveUserDefaultView->entryby = Auth::user()->id;
                    $saveUserDefaultView->entryip = $request->ip();
                }
                $saveUserDefaultView->filterview_id = $request->view_id;
                $saveUserDefaultView->user_id = Auth::user()->id;
                $saveUserDefaultView->user_type = 0;
                $saveUserDefaultView->default_type = 'user_wise';
                $saveUserDefaultView->module = 'lead_deal';
                $saveUserDefaultView->remark = 'lead user wise default filter view';
                $saveUserDefaultView->save();

                if ($saveUserDefaultView) {
                    $response = successRes('Successfully set view as default');
                } else {
                    $response = errorRes('please contact to admin');
                }
            } else {
                $response = errorRes('please contact to admin');
            }
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function ViewLeadLog(Request $request)
    {
        $LeadStatus = getLeadStatus();

        $user_type = '';
        if (in_array($request->user_type, ['201', '202'])) {
            $user_type = '201';
        } elseif (in_array($request->user_type, ['301', '302'])) {
            $user_type = '301';
        } else {
            $user_type = $request->user_type;
        }

        $searchColumns = ['leads.id', 'CONCAT(leads.first_name," ",leads.last_name)', 'leads.quotation'];

        $sortingColumns = [
            0 => 'leads.id',
            1 => 'leads.first_name',
            2 => 'leads.status',
            3 => 'leads.quotation',
            4 => 'leads.source',
        ];

        $selectColumns = ['leads.id', 'leads.is_deal', 'leads.first_name', 'leads.last_name', 'leads.status', 'leads.quotation', 'leads.electrician', 'leads.architect', 'leads.source_type', 'leads.source'];

        // TITLE CREATE START
        $User = User::find($request->id);
        if ($User) {
            if ($User->type == '201') {
                $title = $User->first_name . ' ' . $User->last_name . ' - Architect(Non Prime)';
            } elseif ($User->type == '202') {
                $title = $User->first_name . ' ' . $User->last_name . ' - Architect(Prime)';
            } elseif ($User->type == '301') {
                $title = $User->first_name . ' ' . $User->last_name . ' - Electrician(Non Prime)';
            } elseif ($User->type == '302') {
                $title = $User->first_name . ' ' . $User->last_name . ' - Electrician(Prime)';
            } elseif (in_array($User->type, ['101', '102', '103', '104', '105'])) {
                $ch_firm_name = ChannelPartner::query()
                    ->where('user_id', $request->id)
                    ->first();
                $title = $ch_firm_name->firm_name . ' - Channel Partner';
            }
        }
        // TITLE CREATE END

        // TOTAL RECORD START
        $query = Lead::query();
        if (in_array($request->user_type, ['201', '202'])) {
            $query->where('architect', $request->id);
        } elseif (in_array($request->user_type, ['301', '302'])) {
            $query->where('electrician', $request->id);
        } elseif (in_array($request->user_type, ['101', '102', '103', '104', '105'])) {
            $query->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
            $query->whereIn('lead_sources.source_type', ['user-101', 'user-102', 'user-103', 'user-104', 'user-105']);
            $query->where('lead_sources.source', $request->id);
        }
        if ($request->filter_type != 0) {
            if ($request->filter_type == 1) {
                $statusArray = [1, 2, 3, 4, 100, 101, 102];
                $query->whereIn('leads.status', $statusArray);
            } elseif ($request->filter_type == 2) {
                $statusArray = [103];
                $query->whereIn('leads.status', $statusArray);
            } elseif ($request->filter_type == 3) {
                $statusArray = [5, 6, 104, 105];
                $query->whereIn('leads.status', $statusArray);
            }
        } else {
            // $query = $query->whereIn('leads.status', ['1', '2', '3', '4', '100', '101', '102']);
        }

        if ($request->has('data_view_type') && $request->data_view_type == 1) {
            $startDate = Carbon::now()->subMonth()->startOfMonth()->toDateString();
            $endDate = Carbon::now()->subMonth()->endOfMonth()->toDateString();

            // Use the start and end date to filter your data
            // For example, assuming your data is stored in a database table named 'leads':
            $query->whereBetween('leads.created_at', [$startDate, $endDate]);
        } elseif ($request->has('data_view_type') && $request->data_view_type == 2) {
            $currentYear = date('Y');
            $currentMonth = date('m');

            // Use whereYear and whereMonth to filter records based on the current year and month
            $query->whereYear('leads.created_at', $currentYear)->whereMonth('leads.created_at', $currentMonth);
        } elseif ($request->has('data_view_type') && $request->data_view_type == 3) {
            $lastYearStartDate = Carbon::now()->subYear()->startOfYear()->toDateString();
            $lastYearEndDate = Carbon::now()->subYear()->endOfYear()->toDateString();

            // Use the start and end date to filter records for the last year
            $query->whereBetween('leads.created_at', [$lastYearStartDate, $lastYearEndDate]);
        } elseif ($request->has('data_view_type') && $request->data_view_type == 4) {
            $currentYear = date('Y'); // Get the current month

            // Use whereMonth to filter records based on the current month
            $query->whereYear('leads.created_at', $currentYear);
        } elseif ($request->has('data_view_type') && $request->data_view_type == 5) {
            if (isset($request->from_date) && isset($request->to_date)) {
                $from_date_filter_value = date('Y-m-d', strtotime($request->from_date));
                $to_date_filter_value = date('Y-m-d', strtotime($request->to_date));

                $query->whereBetween('leads.created_at', [$from_date_filter_value, $to_date_filter_value]);
            } else {
                $query->whereYear('leads.created_at', date('Y', strtotime(date('Y'))));
            }
        } else {
            // Default to current year if no date filter type is provided
            // $currentYear = date('Y');
            // $query->whereYear('leads.created_at', $currentYear);
        }

        $recordsTotal = $query->count();
        // TOTAL RECORD END

        //  RECORD FILTER START
        $query = Lead::query();
        if (in_array($request->user_type, ['201', '202'])) {
            $query->where('architect', $request->id);
        } elseif (in_array($request->user_type, ['301', '302'])) {
            $query->where('electrician', $request->id);
        } elseif (in_array($request->user_type, ['101', '102', '103', '104', '105'])) {
            $query->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
            $query->whereIn('lead_sources.source_type', ['user-101', 'user-102', 'user-103', 'user-104', 'user-105']);
            $query->where('lead_sources.source', $request->id);
        }
        if ($request->filter_type != 0) {
            if ($request->filter_type == 1) {
                $statusArray = [1, 2, 3, 4, 100, 101, 102];
                $query->whereIn('leads.status', $statusArray);
            } elseif ($request->filter_type == 2) {
                $statusArray = [103];
                $query->whereIn('leads.status', $statusArray);
            } elseif ($request->filter_type == 3) {
                $statusArray = [5, 6, 104, 105];
                $query->whereIn('leads.status', $statusArray);
            }
        } else {
            // $query = $query->whereIn('leads.status', ['1', '2', '3', '4', '100', '101', '102']);
        }

        if ($request->has('data_view_type') && $request->data_view_type == 1) {
            $startDate = Carbon::now()->subMonth()->startOfMonth()->toDateString();
            $endDate = Carbon::now()->subMonth()->endOfMonth()->toDateString();

            // Use the start and end date to filter your data
            // For example, assuming your data is stored in a database table named 'leads':
            $query->whereBetween('leads.created_at', [$startDate, $endDate]);
        } elseif ($request->has('data_view_type') && $request->data_view_type == 2) {
            $currentYear = date('Y');
            $currentMonth = date('m');

            // Use whereYear and whereMonth to filter records based on the current year and month
            $query->whereYear('leads.created_at', $currentYear)->whereMonth('leads.created_at', $currentMonth);
        } elseif ($request->has('data_view_type') && $request->data_view_type == 3) {
            $lastYearStartDate = Carbon::now()->subYear()->startOfYear()->toDateString();
            $lastYearEndDate = Carbon::now()->subYear()->endOfYear()->toDateString();

            // Use the start and end date to filter records for the last year
            $query->whereBetween('leads.created_at', [$lastYearStartDate, $lastYearEndDate]);
        } elseif ($request->has('data_view_type') && $request->data_view_type == 4) {
            $currentYear = date('Y'); // Get the current month

            // Use whereMonth to filter records based on the current month
            $query->whereYear('leads.created_at', $currentYear);
        } elseif ($request->has('data_view_type') && $request->data_view_type == 5) {
            if (isset($request->from_date) && isset($request->to_date)) {
                $from_date_filter_value = date('Y-m-d', strtotime($request->from_date));
                $to_date_filter_value = date('Y-m-d', strtotime($request->to_date));

                $query->whereBetween('leads.created_at', [$from_date_filter_value, $to_date_filter_value]);
            } else {
                $query->whereYear('leads.created_at', date('Y', strtotime(date('Y'))));
            }
        } else {
            // Default to current year if no date filter type is provided
            // $currentYear = date('Y');
            // $query->whereYear('leads.created_at', $currentYear);
        }

        $recordsFiltered = $recordsTotal;
        //  RECORD FILTER END

        // ALL DATA GET START
        $query = Lead::query();
        if (in_array($request->user_type, ['201', '202'])) {
            $query->where('architect', $request->id);
        } elseif (in_array($request->user_type, ['301', '302'])) {
            $query->where('electrician', $request->id);
        } elseif (in_array($request->user_type, ['101', '102', '103', '104', '105'])) {
            $query->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
            $query->whereIn('lead_sources.source_type', ['user-101', 'user-102', 'user-103', 'user-104', 'user-105']);
            $query->where('lead_sources.source', $request->id);
        }

        if ($request->has('data_view_type') && $request->data_view_type == 1) {
            $startDate = Carbon::now()->subMonth()->startOfMonth()->toDateString();
            $endDate = Carbon::now()->subMonth()->endOfMonth()->toDateString();

            // Use the start and end date to filter your data
            // For example, assuming your data is stored in a database table named 'leads':
            $query->whereBetween('leads.created_at', [$startDate, $endDate]);
        } elseif ($request->has('data_view_type') && $request->data_view_type == 2) {
            $currentYear = date('Y');
            $currentMonth = date('m');

            // Use whereYear and whereMonth to filter records based on the current year and month
            $query->whereYear('leads.created_at', $currentYear)->whereMonth('leads.created_at', $currentMonth);
        } elseif ($request->has('data_view_type') && $request->data_view_type == 3) {
            $lastYearStartDate = Carbon::now()->subYear()->startOfYear()->toDateString();
            $lastYearEndDate = Carbon::now()->subYear()->endOfYear()->toDateString();

            // Use the start and end date to filter records for the last year
            $query->whereBetween('leads.created_at', [$lastYearStartDate, $lastYearEndDate]);
        } elseif ($request->has('data_view_type') && $request->data_view_type == 4) {
            $currentYear = date('Y'); // Get the current month

            // Use whereMonth to filter records based on the current month
            $query->whereYear('leads.created_at', $currentYear);
        } elseif ($request->has('data_view_type') && $request->data_view_type == 5) {
            if (isset($request->from_date) && isset($request->to_date)) {
                $from_date_filter_value = date('Y-m-d', strtotime($request->from_date));
                $to_date_filter_value = date('Y-m-d', strtotime($request->to_date));

                $query->whereBetween('leads.created_at', [$from_date_filter_value, $to_date_filter_value]);
            } else {
                $query->whereYear('leads.created_at', date('Y', strtotime(date('Y'))));
            }
        } else {
            // Default to current year if no date filter type is provided
            // $currentYear = date('Y');
            // $query->whereYear('leads.created_at', $currentYear);
        }

        $query->select($selectColumns);
        $query->limit($request->length);
        $query->offset($request->start);
        $query->orderBy($sortingColumns[$request['order'][0]['column']], $request['order'][0]['dir']);
        $isFilterApply = 0;
        if (isset($request['search']['value'])) {
            $isFilterApply = 1;
            $search_value = $request['search']['value'];
            $query->where(function ($query) use ($search_value, $searchColumns) {
                for ($i = 0; $i < count($searchColumns); $i++) {
                    if ($i == 0) {
                        $query->whereRaw($searchColumns[$i] . ' like ?', [$search_value]);
                    } else {
                        $query->orWhereRaw($searchColumns[$i] . ' like ?', ['%' . $search_value . '%']);
                    }
                }
            });
        }
        if ($request->filter_type != 0) {
            if ($request->filter_type == 1) {
                $statusArray = [1, 2, 3, 4, 100, 101, 102];
                $query->whereIn('leads.status', $statusArray);
            } elseif ($request->filter_type == 2) {
                $statusArray = [103];
                $query->whereIn('leads.status', $statusArray);
            } elseif ($request->filter_type == 3) {
                $statusArray = [5, 6, 104, 105];
                $query->whereIn('leads.status', $statusArray);
            }
        } else {
            // $query = $query->whereIn('leads.status', ['1', '2', '3', '4', '100', '101', '102']);
        }
        $data = $query->get();
        $data = json_decode(json_encode($data), true);
        if ($isFilterApply == 1) {
            $recordsFiltered = count($data);
        }
        // ALL DATA GET END

        //  AJAX DATA START
        $final_quotation = 0;
        if ($data) {
            $total_quotation = 0;
            foreach ($data as $key => $value) {
                $viewData[$key] = [];
                $viewData[$key]['id'] = $value['id'];
                if ($value['is_deal'] == 0) {
                    $routeLead = route('crm.lead') . '?id=' . $value['id'];
                } else {
                    $routeLead = route('crm.deal') . '?id=' . $value['id'];
                }

                $viewData[$key]['name'] = '<p class="text-muted mb-0" data-bs-toggle="tooltip"  data-bs-original-title="' . $value['first_name'] . ' ' . $value['last_name'] . '"><a target="_blank" href="' . $routeLead . '" >' . displayStringLenth($value['first_name'] . ' ' . $value['last_name'], 20) . '</a></p>';
                $viewData[$key]['status'] = $LeadStatus[$value['status']]['name'];

                $LeadQuotation = Wltrn_Quotation::query();
                $LeadQuotation->select('id', 'quotgroup_id', 'quot_date', 'quot_no_str', 'isfinal');
                $LeadQuotation->where('wltrn_quotation.inquiry_id', $value['id']);
                $LeadQuotation->where('wltrn_quotation.status', 3);
                $LeadQuotation->orderBy('wltrn_quotation.id', 'desc');
                $LeadQuotation = $LeadQuotation->get();
                $LeadQuotation = json_decode(json_encode($LeadQuotation), true);

                $quotation = 0;
                if ($LeadQuotation) {
                    foreach ($LeadQuotation as $quot_key => $quot_value) {
                        $total_details = Wltrn_QuotItemdetail::query();
                        $total_details->selectRaw('SUM(wltrn_quot_itemdetails.net_amount) as net_amount');
                        $total_details->where([['wltrn_quot_itemdetails.quot_id', $quot_value['id']], ['wltrn_quot_itemdetails.quotgroup_id', $quot_value['quotgroup_id']], ['wltrn_quot_itemdetails.isactiveroom', 1], ['wltrn_quot_itemdetails.isactiveboard', 1]]);
                        $total_total_amount = $total_details->first();

                        if ($total_total_amount) {
                            $total_total_amount = $total_total_amount ? $total_total_amount->net_amount : 0;
                        } else {
                            $total_total_amount = 0;
                        }
                        $quotation += $total_total_amount;
                    }
                } else {
                    $quotation_details = 0;
                    if ($value['quotation'] != null || $value['quotation'] != '' || $value['quotation'] != 0) {
                        $quotation_details = (int) $value['quotation'];
                    } else {
                        $quotation_details = 0;
                    }

                    $quotation = $quotation_details;
                }

                $total_quotation +=  $quotation;
                $viewData[$key]['quotation_amount'] = '<i class="fas fa-rupee-sign"></i> ' . numCommaFormat($quotation);

                $arc_and_ele_source = Lead::query();
                $arc_and_ele_source->select('leads.id');
                if (in_array($request->user_type, ['201', '202'])) {
                    $arc_and_ele_source->selectRaw('source_full_name.electrician_name AS ele_source_name');
                } elseif (in_array($request->user_type, ['301', '302'])) {
                    $arc_and_ele_source->selectRaw('source_full_name.architect_name AS arc_source_name');
                } elseif (in_array($request->user_type, ['101', '102', '103', '104', '105'])) {
                    $arc_and_ele_source->selectRaw('arc_source_full_name.architect_name AS arc_source_name');
                    $arc_and_ele_source->selectRaw('ele_source_full_name.electrician_name AS ele_source_name');
                }

                if (in_array($request->user_type, ['201', '202'])) {
                    $arc_and_ele_source->leftJoin(
                        DB::raw('(SELECT
                        source_user.id,
                        CONCAT(source_user.first_name," ", source_user.last_name) as electrician_name
                        FROM lead_sources as so
                        LEFT JOIN users as source_user ON source_user.id = so.source
                        WHERE so.source_type IN ("user-301","user-302")) as source_full_name'),
                        'source_full_name.id',
                        '=',
                        'leads.electrician',
                    );
                } elseif (in_array($request->user_type, ['301', '302'])) {
                    $arc_and_ele_source->leftJoin(
                        DB::raw('(SELECT
                        source_user.id,
                        CONCAT(source_user.first_name," ", source_user.last_name) as architect_name
                        FROM lead_sources as so
                        LEFT JOIN users as source_user ON source_user.id = so.source
                        WHERE so.source_type IN ("user-201","user-202")) as source_full_name'),
                        'source_full_name.id',
                        '=',
                        'leads.architect',
                    );
                } elseif (in_array($request->user_type, ['101', '102', '103', '104', '105'])) {
                    $arc_and_ele_source->leftJoin(
                        DB::raw('(SELECT
                    ele_source_user.id,
                    CONCAT(ele_source_user.first_name," ", ele_source_user.last_name) as electrician_name
                    FROM lead_sources as so
                    LEFT JOIN users as ele_source_user ON ele_source_user.id = so.source
                    WHERE so.source_type IN ("user-301","user-302")) as ele_source_full_name'),
                        'ele_source_full_name.id',
                        '=',
                        'leads.electrician',
                    );

                    $arc_and_ele_source->leftJoin(
                        DB::raw('(SELECT
                        arc_source_user.id,
                        CONCAT(arc_source_user.first_name," ", arc_source_user.last_name) as architect_name
                        FROM lead_sources as so
                        LEFT JOIN users as arc_source_user ON arc_source_user.id = so.source
                        WHERE so.source_type IN ("user-201","user-202")) as arc_source_full_name'),
                        'arc_source_full_name.id',
                        '=',
                        'leads.architect',
                    );
                }
                $arc_and_ele_source->where('leads.id', $value['id']);
                $arc_and_ele_source = $arc_and_ele_source->first();

                if ($arc_and_ele_source) {
                    if (in_array($request->user_type, ['201', '202'])) {
                        $viewData[$key]['arc_and_ele_source'] = $arc_and_ele_source->ele_source_name;
                    } elseif (in_array($request->user_type, ['301', '302'])) {
                        $viewData[$key]['arc_and_ele_source'] = $arc_and_ele_source->arc_source_name;
                    } elseif (in_array($request->user_type, ['101', '102', '103', '104', '105'])) {
                        $viewData[$key]['arc_and_ele_source'] = $arc_and_ele_source->arc_source_name;
                        $viewData[$key]['channel_partner'] = $arc_and_ele_source->ele_source_name;
                    }
                } else {
                    $viewData[$key]['arc_and_ele_source'] = '-';
                }
                if (!in_array($request->user_type, ['101', '102', '103', '104', '105'])) {
                    $source = LeadSource::query();
                    $source->selectRaw('group_concat(channel_partner.firm_name) as names');
                    $source->whereIn('lead_sources.source_type', ['user-101', 'user-102', 'user-103', 'user-104', 'user-105']);
                    $source->where('lead_sources.lead_id', $value['id']);
                    $source->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'lead_sources.source');
                    $source = $source->first();
                    $channel_partener = $source->names;
                    $viewData[$key]['channel_partner'] = $channel_partener;
                }
            }

            $final_quotation = $total_quotation;
        } else {
            $viewData = '';
        }
        //  AJAX DATA END

        // RUNNING LEAD COUNT START
        $RunningLeadCount = Lead::query();

        if (in_array($request->user_type, ['201', '202'])) {
            $RunningLeadCount->where('architect', $request->id);
        } elseif (in_array($request->user_type, ['301', '302'])) {
            $RunningLeadCount->where('electrician', $request->id);
        } elseif (in_array($request->user_type, ['101', '102', '103', '104', '105'])) {
            $RunningLeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
            $RunningLeadCount->whereIn('lead_sources.source_type', ['user-101', 'user-102', 'user-103', 'user-104', 'user-105']);
            $RunningLeadCount->where('lead_sources.source', $request->id);
        }

        if (isset($request->date_filter)) {
            $RunningLeadCount->whereDate('leads.created_at', date('Y-m-d', strtotime($request->date_filter)));
        } else {
            if ($request->has('data_view_type')) {
                $dateFilterType = $request->data_view_type;
                switch ($dateFilterType) {
                    case 1: // Last Month
                        $lastMonthStartDate = Carbon::now()->subMonth()->startOfMonth()->toDateString();
                        $lastMonthEndDate = Carbon::now()->subMonth()->endOfMonth()->toDateString();
                        $RunningLeadCount->whereBetween('leads.created_at', [$lastMonthStartDate, $lastMonthEndDate]);
                        break;
                    case 2: // Current Year and Month
                        $currentYear = date('Y');
                        $currentMonth = date('m');
                        $RunningLeadCount->whereYear('leads.created_at', $currentYear)
                            ->whereMonth('leads.created_at', $currentMonth);
                        break;
                    case 3: // Last Year
                        $lastYearStartDate = Carbon::now()->subYear()->startOfYear()->toDateString();
                        $lastYearEndDate = Carbon::now()->subYear()->endOfYear()->toDateString();
                        $RunningLeadCount->whereBetween('leads.created_at', [$lastYearStartDate, $lastYearEndDate]);
                        break;
                    case 4: // Current Year
                        $currentYear = date('Y');
                        $RunningLeadCount->whereYear('leads.created_at', $currentYear);
                        break;
                    case 5: // Custom Date Range (You need to handle custom date range separately)
                        if (isset($request->from_date) && isset($request->to_date)) {
                            $fromDate = date('Y-m-d', strtotime($request->from_date));
                            $toDate = date('Y-m-d', strtotime($request->to_date));
                            $RunningLeadCount->whereBetween('leads.created_at', [$fromDate, $toDate]);
                        }
                        break;
                    default: // Default to current year if no date filter type is provided
                        // $currentYear = date('Y');
                        // $RunningLeadCount->whereYear('leads.created_at', $currentYear);
                        break;
                }
            } else { // Default to current year if no date filter type is provided
                $currentYear = date('Y');
                $RunningLeadCount->whereYear('leads.created_at', $currentYear);
            }
        }

        $RunningLeadCount->whereIn('leads.status', ['1', '2', '3', '4', '100', '101', '102']);
        $RunningLeadCount = $RunningLeadCount->count();
        // RUNNING LEAD COUNT END

        // WON LEAD COUNT START
        $WonLeadCount = Lead::query();
        if (in_array($request->user_type, ['201', '202'])) {
            $WonLeadCount->where('architect', $request->id);
        } elseif (in_array($request->user_type, ['301', '302'])) {
            $WonLeadCount->where('electrician', $request->id);
        } elseif (in_array($request->user_type, ['101', '102', '103', '104', '105'])) {
            $WonLeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
            $WonLeadCount->whereIn('lead_sources.source_type', ['user-101', 'user-102', 'user-103', 'user-104', 'user-105']);
            $WonLeadCount->where('lead_sources.source', $request->id);
        }
        if (isset($request->date_filter)) {
            $WonLeadCount->whereDate('leads.created_at', date('Y-m-d', strtotime($request->date_filter)));
        } else {
            if ($request->has('data_view_type')) {
                $dateFilterType = $request->data_view_type;
                switch ($dateFilterType) {
                    case 1: // Last Month
                        $lastMonthStartDate = Carbon::now()->subMonth()->startOfMonth()->toDateString();
                        $lastMonthEndDate = Carbon::now()->subMonth()->endOfMonth()->toDateString();
                        $WonLeadCount->whereBetween('leads.created_at', [$lastMonthStartDate, $lastMonthEndDate]);
                        break;
                    case 2: // Current Year and Month
                        $currentYear = date('Y');
                        $currentMonth = date('m');
                        $WonLeadCount->whereYear('leads.created_at', $currentYear)
                            ->whereMonth('leads.created_at', $currentMonth);
                        break;
                    case 3: // Last Year
                        $lastYearStartDate = Carbon::now()->subYear()->startOfYear()->toDateString();
                        $lastYearEndDate = Carbon::now()->subYear()->endOfYear()->toDateString();
                        $WonLeadCount->whereBetween('leads.created_at', [$lastYearStartDate, $lastYearEndDate]);
                        break;
                    case 4: // Current Year
                        $currentYear = date('Y');
                        $WonLeadCount->whereYear('leads.created_at', $currentYear);
                        break;
                    case 5: // Custom Date Range (You need to handle custom date range separately)
                        if (isset($request->from_date) && isset($request->to_date)) {
                            $fromDate = date('Y-m-d', strtotime($request->from_date));
                            $toDate = date('Y-m-d', strtotime($request->to_date));
                            $WonLeadCount->whereBetween('leads.created_at', [$fromDate, $toDate]);
                        }
                        break;
                    default: // Default to current year if no date filter type is provided
                        // $currentYear = date('Y');
                        // $WonLeadCount->whereYear('leads.created_at', $currentYear);
                        break;
                }
            } else { // Default to current year if no date filter type is provided
                $currentYear = date('Y');
                $WonLeadCount->whereYear('leads.created_at', $currentYear);
            }
        }
        $WonLeadCount->where('leads.status', '103');
        $WonLeadCount = $WonLeadCount->count();
        // WON LEAD COUNT END

        // LOST LEAD COUNT START
        $LostLeadCount = Lead::query();
        if (in_array($request->user_type, ['201', '202'])) {
            $LostLeadCount->where('architect', $request->id);
        } elseif (in_array($request->user_type, ['301', '302'])) {
            $LostLeadCount->where('electrician', $request->id);
        } elseif (in_array($request->user_type, ['101', '102', '103', '104', '105'])) {
            $LostLeadCount->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
            $LostLeadCount->whereIn('lead_sources.source_type', ['user-101', 'user-102', 'user-103', 'user-104', 'user-105']);
            $LostLeadCount->where('lead_sources.source', $request->id);
        }
        if (isset($request->date_filter)) {
            $LostLeadCount->whereDate('leads.created_at', date('Y-m-d', strtotime($request->date_filter)));
        } else {
            if ($request->has('data_view_type')) {
                $dateFilterType = $request->data_view_type;
                switch ($dateFilterType) {
                    case 1: // Last Month
                        $lastMonthStartDate = Carbon::now()->subMonth()->startOfMonth()->toDateString();
                        $lastMonthEndDate = Carbon::now()->subMonth()->endOfMonth()->toDateString();
                        $LostLeadCount->whereBetween('leads.created_at', [$lastMonthStartDate, $lastMonthEndDate]);
                        break;
                    case 2: // Current Year and Month
                        $currentYear = date('Y');
                        $currentMonth = date('m');
                        $LostLeadCount->whereYear('leads.created_at', $currentYear)
                            ->whereMonth('leads.created_at', $currentMonth);
                        break;
                    case 3: // Last Year
                        $lastYearStartDate = Carbon::now()->subYear()->startOfYear()->toDateString();
                        $lastYearEndDate = Carbon::now()->subYear()->endOfYear()->toDateString();
                        $LostLeadCount->whereBetween('leads.created_at', [$lastYearStartDate, $lastYearEndDate]);
                        break;
                    case 4: // Current Year
                        $currentYear = date('Y');
                        $LostLeadCount->whereYear('leads.created_at', $currentYear);
                        break;
                    case 5: // Custom Date Range (You need to handle custom date range separately)
                        if (isset($request->from_date) && isset($request->to_date)) {
                            $fromDate = date('Y-m-d', strtotime($request->from_date));
                            $toDate = date('Y-m-d', strtotime($request->to_date));
                            $LostLeadCount->whereBetween('leads.created_at', [$fromDate, $toDate]);
                        }
                        break;
                    default: // Default to current year if no date filter type is provided
                        // $currentYear = date('Y');
                        // $LostLeadCount->whereYear('leads.created_at', $currentYear);
                        break;
                }
            } else { // Default to current year if no date filter type is provided
                $currentYear = date('Y');
                $LostLeadCount->whereYear('leads.created_at', $currentYear);
            }
        }
        $LostLeadCount->whereIn('leads.status', ['5', '6', '104', '105']);
        $LostLeadCount = $LostLeadCount->count();
        // LOST LEAD COUNT END

        $jsonData = [
            'draw' => intval($request['draw']),
            'recordsTotal' => intval($recordsTotal),
            'recordsFiltered' => intval($recordsFiltered),
            'data' => $viewData,
            'title' => $title,
            'type' => $request->filter_type,
            'quotation_amount' => numCommaFormat($final_quotation),
            'TotalLeadCount' => $RunningLeadCount + $WonLeadCount + $LostLeadCount,
            'RunningLeadCount' => $RunningLeadCount,
            'WonLeadCount' => $WonLeadCount,
            'LostLeadCount' => $LostLeadCount,
            'user_type' => $user_type,
        ];
        return $jsonData;
    }

    function ViewSelectedFilter(Request $request)
    {
        $selected_view_set = CRMLeadAdvanceFilter::query();
        $selected_view_set->select('lead_advance_filter.id', 'lead_advance_filter.name');
        $selected_view_set->where('lead_advance_filter.id', $request->view_id);
        $selected_view_set = $selected_view_set->first();
        $selectedview = [];
        if ($selected_view_set) {
            $already_default_view_set = UserDefaultView::query();
            $already_default_view_set->where('user_id', Auth::user()->id);
            $already_default_view_set->where('filterview_id', $selected_view_set->id);
            $already_default_view_set->where('default_type', 'user_wise');
            $already_default_view_set = $already_default_view_set->first();

            if ($already_default_view_set) {
                $selectedview['id'] = $selected_view_set->id;
                $selectedview['text'] = '<div><label class="star-radio d-flex align-items-center justify-content-between" data-value="' . $selected_view_set->id . '"><input type="radio"  id="setViewAsFavorite_' . $selected_view_set->id . '" name="setselectedViewAsFavorite" value="' . $selected_view_set->id . '" checked><span class="star" onclick="setViewAsFavorite(' . $selected_view_set->id . ');"></span><span>' . $selected_view_set->name . '</span><i class="bx bx-x-circle" style="font-size: large;" onclick="clearAllFilter(1)"></i></label></div>';
            } else {
                $selectedview['id'] = $selected_view_set->id;
                $selectedview['text'] = '<div><label class="star-radio d-flex align-items-center justify-content-between" data-value="' . $selected_view_set->id . '"><input type="radio"  id="setViewAsFavorite_' . $selected_view_set->id . '" name="setselectedViewAsFavorite" value="' . $selected_view_set->id . '"><span class="star" onclick="setViewAsFavorite(' . $selected_view_set->id . ');"></span><span>' . $selected_view_set->name . '</span><i class="bx bx-x-circle" style="font-size: large;" onclick="clearAllFilter(1)"></i></label></div>';
            }
        } else {
            $selectedview['id'] = 0;
            $selectedview['text'] = '';
        }
        $data['selected_filter'] = $selectedview;
        $data['selected_filter_id'] = $selected_view_set->id;

        $response = successRes('Get Advance Filter View');
        $response['data'] = $selectedview;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function AdvanceFilterDelete(Request $request)
    {
        CRMLeadAdvanceFilter::where([['lead_advance_filter.id', $request->view_id]])->delete();

        CRMLeadAdvanceFilterItem::where([['lead_advance_filter_item.advance_filter_id', $request->view_id]])->delete();

        $default_view_set = UserDefaultView::query();
        $default_view_set->where('user_id', Auth::user()->id);
        $default_view_set->where('filterview_id', $request->view_id);
        $default_view_set->where('default_type', 'user_wise');
        $default_view_set = $default_view_set->first();

        if ($default_view_set) {
            UserDefaultView::where([['user_default_views.id', $default_view_set->id]])->delete();
        }

        $response = successRes('Successsfully Delete Advance Filter');
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function searchReminderTimeSlot(Request $request)
    {
        $searchKeyword = isset($request->q) ? $request->q : '';

        $ReminderTimeSlot = getReminderTimeSlot();

        $finalArray[] = [];
        foreach ($ReminderTimeSlot as $key => $value) {
            $finalArray[$key]['id'] = $value['id'];
            $finalArray[$key]['text'] = $value['name'];
        }

        $response = [];
        $response['results'] = $finalArray;
        $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function checkPhoneNumber(Request $request)
    {
        $rules = [];
        $rules['lead_phone_number'] = 'required|digits:10|regex:/^[1-9][0-9]*$/';

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $response = errorRes('The request could not be understood by the server due to malformed syntax');
            $response['data'] = $validator->errors();
        } else {
            $isAlreadyLead = Lead::select('id', 'assigned_to', 'is_deal')
                ->where('phone_number', $request->lead_phone_number)
                ->first();
            if ($isAlreadyLead) {
                $User = User::select('first_name', 'last_name')->find($isAlreadyLead->assigned_to);
                if ($User) {
                    $prifix = '';
                    if ($isAlreadyLead->is_deal == 0) {
                        $prifix = 'L';
                    } else {
                        $prifix = 'D';
                    }

                    $response = errorRes('Lead already registed with phone number, #' . $prifix . $isAlreadyLead->id . ' assigned to ' . $User->first_name . ' ' . $User->last_name);
                } else {
                    $response = errorRes('Lead already registed with phone number');
                }
            } else {
                $response = successRes('Lead phone number is valid');
            }
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function searchLeadAndDealTag(Request $request)
    {
        $searchKeyword = isset($request->q) ? $request->q : '';

        $data = TagMaster::select('id', 'tagname as text');
        $data->where('tag_master.isactive', 1);
        $data->where('tag_master.tag_type', 201);
        $data->where('tag_master.tagname', 'like', '%' . $searchKeyword . '%');
        $data->limit(20);
        $data = $data->get();
        $response = [];
        $response['results'] = $data;
        $response['pagination']['more'] = true;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function getRewardBillStatus(Request $request)
    {
        $LeadFile = LeadFile::query();
        $LeadFile->select('crm_setting_file_tag.name as tag_name', 'lead_files.*', 'users.first_name', 'users.last_name');
        $LeadFile->leftJoin('crm_setting_file_tag', 'crm_setting_file_tag.id', '=', 'lead_files.file_tag_id');
        $LeadFile->leftJoin('users', 'users.id', '=', 'lead_files.uploaded_by');
        $LeadFile->where('lead_files.lead_id', $request->lead_id);
        $LeadFile->where('lead_files.is_active', 1);
        $LeadFile = $LeadFile->get();
        $LeadFile = json_encode($LeadFile);
        $LeadFile = json_decode($LeadFile, true);

        $view = '';
        $count = 1;
        foreach ($LeadFile as $key => $value) {
            if ($value['file_tag_id'] == 3) {
                $fileHtml = '';
                foreach (explode(',', $value['name']) as $filekey => $filevalue) {
                    $fileHtml .= '<a class="ms-1" target="_blank" href="' . getSpaceFilePath($filevalue) . '"><i class="bx bxs-file-pdf"></i></a>';
                }

                $view .= '<tr>';
                $view .= '<td>' . $count++ . '</td>';

                $view .= '<td>' . $fileHtml . '</td>';
                $view .= '<td>' . $value['billing_amount'] . '</td>';
                $view .= '<td>' . $value['point'] . '</td>';

                if ($value['status'] == 100) {
                    $view .= '<td><div class="text-center"><span class="badge badge-soft-success  badge-pill badgefont-size-11">CLAIMED</span></div></td>';
                } elseif ($value['status'] == 101) {
                    $view .= '<td><div class="text-center"><span class="badge badge-soft-primary  badge-pill badgefont-size-11">QUERY</span></div></td>';
                } elseif ($value['status'] == 102) {
                    $view .= '<td><div class="text-center"><span class="badge badge-soft-danger  badge-pill badgefont-size-11">LAPSED</span></div></td>';
                } else {
                    $view .= '<td><div class="text-center"><span class="badge badge-soft-warning  badge-pill badgefont-size-11">-</span></div></td>';
                }

                if ($value['hod_approved'] == 1) {
                    $view .= '<td><div class="text-center"><span class="badge badge-soft-success  badge-pill badgefont-size-11">HOD APRROVED</span></div></td>';
                } elseif ($value['hod_approved'] == 2) {
                    $view .= '<td><div class="text-center"><span class="badge badge-soft-danger  badge-pill badgefont-size-11">HOD REJECTED</span></div></td>';
                } else {
                    $view .= '<td><div class="text-center"><span class="badge badge-soft-warning  badge-pill badgefont-size-11">-</span></div></td>';
                }
                $view .= '</tr>';
            }
        }

        $response = successRes();
        $response['data'] = $view;

        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function searchQuestion(Request $request)
    {
        $question = LeadQuestion::query()->where('status', $request->status)->where('tag', 2)->where('is_active', 1)->get();

        $finalQuestion = [];
        foreach ($question as $key => $value) {
            $checkForVisible = 0;
            $option_temp = LeadQuestionOptions::query()->where('lead_question_id', $value->id)->get();
            $question[$key]['options'] = $option_temp;

            if ($value->is_depend_on_answer == 1) {
                $dependedQuestion = LeadQuestion::find($value->depended_question_id);

                if ($dependedQuestion && $dependedQuestion->status != $request->status) {
                    $dependedAnswer = LeadQuestionAnswer::where('lead_id', $request->lead_id)
                        ->where('lead_question_id', $dependedQuestion->id)
                        ->first();
                    if ($dependedAnswer && $dependedAnswer->answer == $value->depended_question_answer) {
                        if ($dependedQuestion->type == 6 || $dependedQuestion->type == 4) {
                            $dependedAnswer = explode(',', $dependedAnswer->answer);
                            if (in_array($value->depended_question_answer, $dependedAnswer)) {
                                $checkForVisible = 1;
                            }
                        } elseif ($dependedAnswer->answer == $value->depended_question_answer) {
                            $checkForVisible = 1;
                        }
                    }
                } else {
                    $checkForVisible = 1;
                }
            } else {
                $checkForVisible = 1;
            }

            if ($value->type == 1 || $value->type == 4 || $value->type == 6) {
                $TypeOption = LeadQuestionOptions::select('id', 'option', 'is_database_side')
                    ->where('lead_question_id', $value->id)
                    ->orderBy('id', 'asc')
                    ->get();
                $question[$key]['options'] = $TypeOption;
                foreach ($TypeOption as $Opkey => $Opvalue) {
                    if ($Opvalue['is_database_side'] == 1) {
                        $LeadOwner = Lead::find($request->lead_id)['assigned_to'];
                        // $LeadOwnerCity = User::find($LeadOwner)['city_id'];
                        $LeadOwnerCity = SalesCity($LeadOwner);

                        $ChannelPartner_list = User::select('users.id', 'channel_partner.firm_name as text');
                        $ChannelPartner_list->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
                        $ChannelPartner_list->whereIn('users.city_id', $LeadOwnerCity);
                        $ChannelPartner_list->whereIn('users.type', [101, 102, 103, 104, 105]);
                        $ChannelPartner_list = $ChannelPartner_list->get();

                        $question[$key]['user_list'] = $ChannelPartner_list;
                    }
                }
            }

            if ($checkForVisible == 1) {
                $cFinalQuestion = count($finalQuestion);
                $finalQuestion[$cFinalQuestion] = $question[$key];
                if ($question[$key]->is_depend_on_answer == 1) {
                    $dependedQuestion = LeadQuestion::find($question[$key]['depended_question_id']);
                    if ($dependedQuestion) {
                        $question[$key]['depended_question'] = $dependedQuestion;
                    } else {
                        $question[$key]->is_depend_on_answer = 0;
                    }
                }
            }
        }

        $data = [];
        $data['lead_id'] = $request->lead_id;
        $data['lead_status'] = $request->status;
        $data['question'] = $question;

        $response = successRes('Successfully get Lead Questions');
        $response['view'] = view('crm/lead/answer', compact('data'))->render();
        $response['data'] = $question;
        $response['checkForVisible'] = $checkForVisible;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function saveLeadStatusAnswer(Request $request)
    {
        $Lead = Lead::find($request->lead_id);
        if ($Lead) {
            $leadQuestion = LeadQuestion::where('status', $request->lead_status)
                ->where('tag', 2)
                ->where('is_active', 1)
                ->get();
            $requiredQuestionIds = [];
            if (count($leadQuestion) > 0) {
                $rules = [];
                $customMessage = [];

                foreach ($leadQuestion as $key => $value) {
                    if ($value->is_depend_on_answer == 1) {
                        $dependedQuestion = LeadQuestion::find($value->depended_question_id);
                        if ($dependedQuestion && $dependedQuestion->status == 104) {
                            if ($dependedQuestion->type == 6) {
                                if (isset($request->all()['lead_questions_' . $value->depended_question_id][$value->depended_question_answer]) && $request->all()['lead_questions_' . $value->depended_question_id][$value->depended_question_answer] == 'on') {
                                    if ($value->is_required == 1 && $value->type == 2) {
                                        $rules['lead_questions_' . $value->id] = 'required|mimes:png,jpg,jpeg,csv,txt,xlx,xls,xlsx,pdf';
                                        $requiredQuestionIds[] = $value->id;
                                    } elseif ($value->is_required == 1 && $value->type == 7) {
                                        $rules['lead_questions_' . $value->id . '.*'] = 'required|mimes:png,jpg,jpeg,csv,txt,xlx,xls,xlsx,pdf';
                                        $requiredQuestionIds[] = $value->id;
                                    } elseif ($value->is_required == 1) {
                                        $rules['lead_questions_' . $value->id] = 'required';
                                    }
                                }
                            } elseif ($dependedQuestion->type == 4) {
                                if (isset($request->all()['lead_questions_' . $value->depended_question_id])) {
                                    if (in_array($value->depended_question_answer, $request->all()['lead_questions_' . $value->depended_question_id])) {
                                        if ($value->is_required == 1 && $value->type == 2) {
                                            $rules['lead_questions_' . $value->id] = 'required|mimes:png,jpg,jpeg,csv,txt,xlx,xls,xlsx,pdf';
                                            $requiredQuestionIds[] = $value->id;
                                        } elseif ($value->is_required == 1 && $value->type == 7) {
                                            $rules['lead_questions_' . $value->id . '.*'] = 'required|mimes:png,jpg,jpeg,csv,txt,xlx,xls,xlsx,pdf';
                                            $requiredQuestionIds[] = $value->id;
                                        } elseif ($value->is_required == 1) {
                                            $rules['lead_questions_' . $value->id] = 'required';
                                        }
                                    }
                                }
                            } elseif (isset($request->all()['lead_questions_' . $value->depended_question_id]) && $request->all()['lead_questions_' . $value->depended_question_id] == $value->depended_question_answer) {
                                if ($value->is_required == 1 && $value->type == 2) {
                                    $rules['lead_questions_' . $value->id] = 'required|mimes:png,jpg,jpeg,csv,txt,xlx,xls,xlsx,pdf';
                                    $requiredQuestionIds[] = $value->id;
                                } elseif ($value->is_required == 1 && $value->type == 7) {
                                    $rules['lead_questions_' . $value->id . '.*'] = 'required|mimes:png,jpg,jpeg,csv,txt,xlx,xls,xlsx,pdf';
                                    $requiredQuestionIds[] = $value->id;
                                } elseif ($value->is_required == 1) {
                                    $rules['lead_questions_' . $value->id] = 'required';
                                }
                            }
                        } else {
                            if ($value->is_required == 1 && $value->type == 2) {
                                $rules['lead_questions_' . $value->id] = 'required|mimes:png,jpg,jpeg,csv,txt,xlx,xls,pdf';
                                $requiredQuestionIds[] = $value->id;
                            } elseif ($value->is_required == 1 && $value->type == 7) {
                                $rules['lead_questions_' . $value->id . '.*'] = 'required|mimes:png,jpg,jpeg,csv,txt,xlx,xls,xlsx,pdf';
                                $requiredQuestionIds[] = $value->id;
                            } elseif ($value->is_required == 1) {
                                $rules['lead_questions_' . $value->id] = 'required';
                            }
                        }
                    } else {
                        // Validation If question is not depended
                        if ($value->is_required == 1 && $value->type == 2) {
                            $rules['lead_questions_' . $value->id] = 'required|mimes:png,jpg,jpeg,csv,txt,xlx,xls,xlsx,pdf';
                            $requiredQuestionIds[] = $value->id;
                        } elseif ($value->is_required == 1 && $value->type == 7) {
                            $rules['lead_questions_' . $value->id . '.*'] = 'required|mimes:png,jpg,jpeg,csv,txt,xlx,xls,xlsx,pdf';
                            $requiredQuestionIds[] = $value->id;
                        } elseif ($value->is_required == 1) {
                            $rules['lead_questions_' . $value->id] = 'required';
                        }
                    }
                }

                $validator = Validator::make($request->all(), $rules, $customMessage);
                if ($validator->fails()) {
                    $response = errorRes();
                    $response['msg'] = 'The request could not be understood by the server due to malformed syntax';
                    $response['data'] = $validator->errors();
                    return response()->json($response)->header('Content-Type', 'application/json');
                } else {
                    $AnswerIds = [];
                    foreach ($leadQuestion as $key => $value) {
                        $answer = '';
                        $leadQuestionAnswer = new LeadQuestionAnswer();
                        $leadQuestionAnswer->lead_question_id = $value->id;
                        $leadQuestionAnswer->question_type = $value->type;
                        $leadQuestionAnswer->lead_id = $request->lead_id;
                        $leadQuestionAnswer->reference_type = 'Lead-Status-Update';
                        $leadQuestionAnswer->reference_id = $request->lead_id;

                        if ($value->type == 4) {
                            $multipleOptions = isset($request->all()['lead_questions_' . $value->id]) ? $request->all()['lead_questions_' . $value->id] : [];
                            $multipleOptions = implode(',', $multipleOptions);
                            $answer = $multipleOptions;
                        } elseif ($value->type == 6) {
                            $answerOfMultiCHeck = isset($request->all()['lead_questions_' . $value->id]) ? $request->all()['lead_questions_' . $value->id] : [];
                            $answerOfMultiCHeck = array_keys($answerOfMultiCHeck);
                            $answerOfMultiCHeck = implode(',', $answerOfMultiCHeck);
                            $answer = $answerOfMultiCHeck;
                        } elseif ($value->type == 7) {
                            $question_attachment_file_name = [];

                            if ($request->hasFile('lead_questions_' . $value->id)) {
                                foreach ($request->file('lead_questions_' . $value->id) as $key => $file_value) {
                                    $question_attachment = $request->file('lead_questions_' . $value->id)[$key];
                                    $extension = $question_attachment->getClientOriginalExtension();

                                    $question_attachment_file_name_temp = time() . mt_rand(10000, 99999) . '.' . $extension;

                                    $destinationPath = public_path('/s/question-attachment');
                                    $question_attachment->move($destinationPath, $question_attachment_file_name_temp);

                                    if (!File::exists('s/question-attachment/' . $question_attachment_file_name_temp)) {
                                        $question_attachment_file_name_temp = '';
                                    } else {
                                        $question_attachment_file_name_temp = '/s/question-attachment/' . $question_attachment_file_name_temp;

                                        $spaceUploadResponse = uploadFileOnSpaces(public_path($question_attachment_file_name_temp), $question_attachment_file_name_temp);
                                        if ($spaceUploadResponse != 1) {
                                            $question_attachment_file_name_temp = '';
                                        } else {
                                            $question_attachment_file_name[] = $question_attachment_file_name_temp;
                                            unlink(public_path($question_attachment_file_name_temp));
                                        }
                                    }
                                }
                            }

                            if (count($question_attachment_file_name) == 0) {
                                if (in_array($value->id, $requiredQuestionIds)) {
                                    $response = errorRes('Please attach valid files (png,jpg,jpeg,csv,txt,xlx,xls,xlsx,pdf) Q. ' . $value->question);
                                    return response()->json($response)->header('Content-Type', 'application/json');
                                }
                            }

                            $question_attachment_file_name = implode(',', $question_attachment_file_name);

                            $answer = $question_attachment_file_name;
                        } else {
                            $answer = isset($request->all()['lead_questions_' . $value->id]) ? $request->all()['lead_questions_' . $value->id] : '';

                            $answer = $answer;
                        }

                        $leadQuestionAnswer->answer = $answer;
                        $leadQuestionAnswer->save();

                        if ($leadQuestionAnswer) {
                            array_push($AnswerIds, $leadQuestionAnswer->id);
                        }
                    }

                    $response = successRes();
                    $response['data'] = implode(',', $AnswerIds);
                    $response['lead_id'] = $request->lead_id;
                    return response()->json($response)->header('Content-Type', 'application/json');
                }
            }
        }
    }

    public function channelPartnerdetail(Request $request)
    {
        $data = [];
        $User = User::find($request->id);

        if ($User) {
            $ChannelPartner = ChannelPartner::select('firm_name', 'sale_persons')
                ->where('user_id', $User->id)
                ->first();

            if ($ChannelPartner) {
                $SalesPerson = User::select(DB::raw("CONCAT(users.first_name,' ',users.last_name) AS name"))
                    ->where('id', $ChannelPartner->sale_persons)
                    ->get();
            }

            $channelPartners = getChannelPartners();

            if (isset($channelPartners[$User->type])) {
                $ChannelPartnerType = $channelPartners[$User->type]['short_name'];
            } else {
                $ChannelPartnerType = null;
            }
        }

        $data = $User;
        $response = successRes();
        $response['data'] = $data;

        if (!empty($ChannelPartner)) {
            $response['data']['firm_name'] = $ChannelPartner->firm_name;
        }

        if (!empty($SalesPerson)) {
            $response['data']['sales_person'] = $SalesPerson[0]['name'];
        }

        if (!empty($ChannelPartnerType)) {
            $response['data']['short_name'] = $ChannelPartnerType;
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function architectdetail(Request $request)
    {
        $data = [];
        $User = User::find($request->id);

        if ($User) {
            $Architect = Architect::select('firm_name', 'sale_person_id')
                ->where('user_id', $User->id)
                ->first();

            if ($Architect) {
                $SalesPerson = User::select(DB::raw("CONCAT(users.first_name,' ',users.last_name) AS name"))
                    ->where('id', $Architect->sale_person_id)
                    ->get();
            }

            $architects = getArchitects();

            if (isset($architects[$User->type])) {
                $ArchitectType = $architects[$User->type]['short_name'];
            } else {
                $ArchitectType = null;
            }
        }

        $data = $User;
        $response = successRes();
        $response['data'] = $data;

        if (!empty($Architect)) {
            $response['data']['firm_name'] = $Architect->firm_name;
        }

        if (!empty($SalesPerson)) {
            $response['data']['sales_person'] = $SalesPerson[0]['name'];
        }

        if (!empty($ArchitectType)) {
            $response['data']['short_name'] = $ArchitectType;
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function electriciandetail(Request $request)
    {
        $data = [];
        $User = User::find($request->id);

        if ($User) {
            $Electrician = Electrician::select('sale_person_id')
                ->where('user_id', $User->id)
                ->first();

            if ($Electrician) {
                $SalesPerson = User::select(DB::raw("CONCAT(users.first_name,' ',users.last_name) AS name"))
                    ->where('id', $Electrician->sale_person_id)
                    ->get();
            }

            $electricians = getElectricians();

            if (isset($electricians[$User->type])) {
                $ElectricianType = $electricians[$User->type]['short_name'];
            } else {
                $ElectricianType = null;
            }
        }

        $data = $User;
        $response = successRes();
        $response['data'] = $data;

        if (!empty($Electrician)) {
            $response['data']['firm_name'] = $Electrician->firm_name;
        }

        if (!empty($SalesPerson)) {
            $response['data']['sales_person'] = $SalesPerson[0]['name'];
        }

        if (!empty($ElectricianType)) {
            $response['data']['short_name'] = $ElectricianType;
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function searchChannelpartner(Request $request)
    {
        $isArchitect = isArchitect();
        $isSalePerson = isSalePerson();
        $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
        $isThirdPartyUser = isThirdPartyUser();
        $isChannelPartner = isChannelPartner(Auth::user()->type);
        $searchKeyword = $request->q;
        if ($isSalePerson == 1) {
            $childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
            $salePerson = SalePerson::select('cities')->where('user_id', Auth::user()->id)->first();
            $cities = array();
            if ($salePerson) {
                $cities = explode(",", $salePerson->cities);
            } else {
                $cities = array(0);
            }
        }
        $data = User::select('users.id', 'channel_partner.firm_name  AS text', 'users.phone_number');
        $data->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
        $data->whereIn('users.status', [1, 2, 3, 4, 5]);
        $data->whereIn('users.type', [101, 102, 103, 104, 105]);
        if ($isSalePerson == 1) {
            $data->where(function ($query) use ($cities, $childSalePersonsIds) {
                $query->whereIn('users.city_id', $cities);
                $query->orWhere(function ($query2) use ($childSalePersonsIds) {
                    foreach ($childSalePersonsIds as $key => $value) {
                        if ($key == 0) {
                            $query2->whereRaw('FIND_IN_SET("' . $value . '",channel_partner.sale_persons)>0');
                        } else {
                            $query2->orWhereRaw('FIND_IN_SET("' . $value . '",channel_partner.sale_persons)>0');
                        }
                    }
                });
            });
        }
        if (isset($request->q)) {
            $data->where(function ($query) use ($searchKeyword) {
                $query->where('channel_partner.firm_name', 'like', '%' . $searchKeyword . '%');
            });
        }
        $data->limit(15);
        $data = $data->get();

        $response = [];
        $response['results'] = $data;
        $response['pagination']['more'] = true;
        return response()->json($response)->header('Content-Type', 'application/json');
    }
}
