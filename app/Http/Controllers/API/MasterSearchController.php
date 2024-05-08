<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Models\PrivilegeUserType;
use App\Models\User;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

//use Session;

class MasterSearchController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            return $next($request);
        });
    }

    function Getmodules(Request $request)
    {
        $isSalePerson = isSalePerson();
		$isChannelPartner = isChannelPartner(Auth::user()->type);
		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		$isMarketingDispatcherUser = isMarketingDispatcherUser();
		if ($isSalePerson == 1) {
			$SalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
		}
        if ($isSalePerson == 1) {
            $childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
        }
        $isTaleSalesUser = isTaleSalesUser();

        if ($isTaleSalesUser == 1) {
            $TeleSalesCity = TeleSalesCity(Auth::user()->id);
        }

        $search_value = $request->q;
        $data = [];

        // {SALES} USER BUTTONS ONLY
        $Select_user_column = [
            0 => 'users.id',
            1 => 'users.first_name',
            2 => 'users.last_name',
            3 => 'users.email',
            4 => 'users.phone_number',
            5 => 'users.address_line1',
            6 => 'users.address_line2',
        ];
        $User = User::query();
        $User->select('users.type');
        $User->selectRaw('COUNT(users.id) as count');
        $User->where('users.type', 2);
        $User->where(function ($query33) use ($search_value, $Select_user_column) {
            for ($i = 0; $i < count($Select_user_column); $i++) {
                if ($i == 0) {
                    $query33->where($Select_user_column[$i], 'like', '%' . $search_value . '%');
                } else {
                    $query33->orWhere($Select_user_column[$i], 'like', '%' . $search_value . '%');
                }
            }
        });
        $User->groupBy(['users.type']);
        $User = $User->get();
        if ($User) {
            foreach ($User as $key => $value) {
                $datanew['name'] = 'Sales';
                $datanew['module'] = 'sales_user';
                $datanew['type'] = '2';
                $datanew['count'] = $value['count'];
                array_push($data, $datanew);
            }
        } else {
            $datanew['name'] = 'Sales';
            $datanew['module'] = 'sales_user';
            $datanew['type'] = '2';
            $datanew['count'] = 0;
            array_push($data, $datanew);
        }
        // {CHANNELPARTNER} USER BUTTONS ONLY
        // $User_Search_column = ['users.id', 'users.first_name', 'users.last_name', 'users.phone_number', 'users.address_line1', 'users.address_line2', DB::raw('CONCAT(users.first_name," ",users.last_name)'), DB::raw('CONCAT(users.address_line1," ",users.address_line2)')];
        // $channelpartener_query = User::query();
        // $User->select('users.type');
        // $User->selectRaw('COUNT(users.id) as count');
        // $channelpartener_query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
        // $channelpartener_query->whereIn('users.type', [101, 102, 103, 104, 105]);
        // if ($isSalePerson == 1) {
        //     $channelpartener_query->where(function ($channelpartener_query2) use ($childSalePersonsIds) {
        //         foreach ($childSalePersonsIds as $key => $value) {
        //             if ($key == 0) {
        //                 $channelpartener_query2->whereRaw('FIND_IN_SET("' . $value . '",channel_partner.sale_persons)>0');
        //             } else {
        //                 $channelpartener_query2->orWhereRaw('FIND_IN_SET("' . $value . '",channel_partner.sale_persons)>0');
        //             }
        //         }
        //     });
        // } else if ($isTaleSalesUser == 1) {
        //     $channelpartener_query->whereIn('users.city_id', $TeleSalesCity);
        // }
        // $channelpartener_query->where(function ($channelpartener_query) use ($search_value, $User_Search_column) {
        //     for ($i = 0; $i < count($User_Search_column); $i++) {
        //         if ($i == 0) {
        //             $channelpartener_query->where($User_Search_column[$i], 'like', '%' . $search_value . '%');
        //         } else {
        //             $channelpartener_query->orWhere($User_Search_column[$i], 'like', '%' . $search_value . '%');
        //         }
        //     }
        // });
        // $channelpartener_query->groupBy(['users.type']);
        // $channelpartener_query->get();
        // foreach ($channelpartener_query as $key => $value) {
        //     if (isset(getChannelPartners()[$value->type]['id'])) {
        //         $datanew['name'] = getChannelPartners()[$value->type]['short_name'];
        //         $datanew['module'] = getChannelPartners()[$value->type]['lable'];
        //         $datanew['type'] = $value->type;
        //         $datanew['count'] = $value['count'];
        //         array_push($data, $datanew);
        //     }
        // }

        // ARCHITECT USER
        $Select_architect_column = [
            0 => 'users.id',
            1 => 'users.first_name',
            2 => 'users.last_name',
            3 => 'users.email',
            4 => 'users.phone_number',
            5 => 'users.address_line1',
            6 => 'users.address_line2',
        ];
        $architectCount = User::whereIn('users.type', [201, 202]);
        $architectCount->leftJoin('architect', 'architect.user_id', '=', 'users.id');
        if ($isAdminOrCompanyAdmin == 1) {
        } else if ($isSalePerson == 1) {
            $architectCount->whereIn('architect.sale_person_id', $SalePersonsIds);
        } else if ($isChannelPartner != 0) {
            $architectCount->where('architect.added_by', Auth::user()->id);
        }
        $architectCount->where(function ($query33) use ($search_value, $Select_architect_column) {
            for ($i = 0; $i < count($Select_architect_column); $i++) {
                if ($i == 0) {
                    $query33->where($Select_architect_column[$i], 'like', '%' . $search_value . '%');
                } else {
                    $query33->orWhere($Select_architect_column[$i], 'like', '%' . $search_value . '%');
                }
            }
        });
        $architectCount = $architectCount->count();
        if ($architectCount > 0) {
            $datanew['name'] = 'Architect';
            $datanew['module'] = 'architect_user';
            $datanew['type'] = '201,202';
            $datanew['count'] = $architectCount;
            array_push($data, $datanew);
        } else {
            $datanew['name'] = 'Architect';
            $datanew['module'] = 'architect_user';
            $datanew['type'] = '201,202';
            $datanew['count'] = 0;
            array_push($data, $datanew);
        }

        // ELECTRICIAN USER
        $Select_electrician_column = [
            0 => 'users.id',
            1 => 'users.first_name',
            2 => 'users.last_name',
            3 => 'users.email',
            4 => 'users.phone_number',
            5 => 'users.address_line1',
            6 => 'users.address_line2',
        ];
        $electricianCount = User::whereIn('users.type', [301, 302]);
        $electricianCount->leftJoin('electrician', 'electrician.user_id', '=', 'users.id');
        if ($isAdminOrCompanyAdmin == 1 || $isMarketingDispatcherUser == 1) {

        } else if ($isSalePerson == 1) {
            $electricianCount->whereIn('electrician.sale_person_id', $SalePersonsIds);
        } else if ($isChannelPartner != 0) {
            $electricianCount->where('electrician.added_by', Auth::user()->id);
        }
        $electricianCount->where(function ($query33) use ($search_value, $Select_electrician_column) {
            for ($i = 0; $i < count($Select_electrician_column); $i++) {
                if ($i == 0) {
                    $query33->where($Select_electrician_column[$i], 'like', '%' . $search_value . '%');
                } else {
                    $query33->orWhere($Select_electrician_column[$i], 'like', '%' . $search_value . '%');
                }
            }
        });
        $electricianCount = $electricianCount->count();
        if ($electricianCount > 0) {
            $datanew['name'] = 'Electrician';
            $datanew['module'] = 'electrician_user';
            $datanew['type'] = '301,302';
            $datanew['count'] = $electricianCount;
            array_push($data, $datanew);
        } else {
            $datanew['name'] = 'Electrician';
            $datanew['module'] = 'electrician_user';
            $datanew['type'] = '301,302';
            $datanew['count'] = 0;
            array_push($data, $datanew);
        }

        // LEAD USER
        $Select_lead_column = [
            0 => 'leads.id',
            1 => 'leads.first_name',
            2 => 'leads.last_name',
            3 => 'leads.email',
            4 => 'leads.phone_number',
            5 => 'leads.addressline1',
            6 => 'leads.addressline2',
        ];
        $leadCount = Lead::where('leads.is_deal', '0');
        if ($isSalePerson == 1) {
            $leadCount->whereIn('leads.assigned_to', $childSalePersonsIds);
        }
        $leadCount->where(function ($query33) use ($search_value, $Select_lead_column) {
            for ($i = 0; $i < count($Select_lead_column); $i++) {
                if ($i == 0) {
                    $query33->where($Select_lead_column[$i], 'like', '%' . $search_value . '%');
                } else {
                    $query33->orWhere($Select_lead_column[$i], 'like', '%' . $search_value . '%');
                }
            }
        });
        $leadCount = $leadCount->count();
        if ($leadCount > 0) {
            $datanew['name'] = 'Lead';
            $datanew['module'] = 'lead';
            $datanew['type'] = '0';
            $datanew['count'] = $leadCount;
            array_push($data, $datanew);
        } else {
            $datanew['name'] = 'Lead';
            $datanew['module'] = 'lead';
            $datanew['type'] = '0';
            $datanew['count'] = 0;
            array_push($data, $datanew);
        }

        // DEAL USER
        $Select_deal_column = [
            0 => 'leads.id',
            1 => 'leads.first_name',
            2 => 'leads.last_name',
            3 => 'leads.email',
            4 => 'leads.phone_number',
            5 => 'leads.addressline1',
            6 => 'leads.addressline2',
        ];
        $dealCount = Lead::where('leads.is_deal', '1');
        if ($isSalePerson == 1) {
            $dealCount->whereIn('leads.assigned_to', $childSalePersonsIds);
        }
        $dealCount->where(function ($query33) use ($search_value, $Select_deal_column) {
            for ($i = 0; $i < count($Select_deal_column); $i++) {
                if ($i == 0) {
                    $query33->where($Select_deal_column[$i], 'like', '%' . $search_value . '%');
                } else {
                    $query33->orWhere($Select_deal_column[$i], 'like', '%' . $search_value . '%');
                }
            }
        });
        $dealCount = $dealCount->count();
        if ($dealCount > 0) {
            $datanew['name'] = 'Deal';
            $datanew['module'] = 'deal';
            $datanew['type'] = '1';
            $datanew['count'] = $dealCount;
            array_push($data, $datanew);
        }
        $response = successRes('success');
        $response['data'] = $data;

        return response()
            ->json($response, $response['status_code'])
            ->header('Content-Type', 'application/json');
    }

    function MasterSearchAjax(Request $request)
    {

        $isSalePerson = isSalePerson();
		$isChannelPartner = isChannelPartner(Auth::user()->type);
		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		$isMarketingDispatcherUser = isMarketingDispatcherUser();
		if ($isSalePerson == 1) {
			$SalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
		}
        if ($isSalePerson == 1) {
            $childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
        }
        $isTaleSalesUser = isTaleSalesUser();

        if ($isTaleSalesUser == 1) {
            $TeleSalesCity = TeleSalesCity(Auth::user()->id);
        }

        $rules = [];
        $rules['module'] = 'required';
        $rules['type'] = 'required';
        $rules['q'] = 'required';

        $customMessage = [];
        $customMessage['module.required'] = 'Please Pass Valid Module';
        $customMessage['type.required'] = 'Please Pass Valid Type';
        $customMessage['q.required'] = 'Please Enter Search Text';

        $validator = Validator::make($request->all(), $rules, $customMessage);

        if ($validator->fails()) {
            $response = errorRes($validator->errors()->first(), 400);
            $response['data'] = $validator->errors();
        } else {
            $data = [];
            $search_value = $request->q;
            $module = $request->module;
            $dataType = explode(',',$request->type);
            if ($module == 'sales_user') {
                $User_Search_column = ['users.id', 'users.first_name', 'users.last_name', 'users.phone_number', 'users.address_line1', 'users.address_line2', DB::raw('CONCAT(users.first_name," ",users.last_name)'), DB::raw('CONCAT(users.address_line1," ",users.address_line2)')];
                $User_Select_column = [
                    0 => 'users.id',
                    1 => 'users.first_name',
                    2 => 'users.last_name',
                    3 => 'users.email',
                    4 => 'users.phone_number',
                    5 => 'users.address_line1',
                    6 => 'users.address_line2',
                    7 => 'users.type',
                    8 => 'users.city_id',
			        9 => 'city_list.name as city_list_name',
                ];
                $user_query = User::query();
                $user_query->select($User_Select_column);
                $user_query->whereIn('users.type', $dataType);
			    $user_query->leftJoin('city_list', 'city_list.id', '=', 'users.city_id');

                $user_query->where(function ($query) use ($search_value, $User_Search_column) {
                    for ($i = 0; $i < count($User_Search_column); $i++) {
                        if ($i == 0) {
                            $query->where($User_Search_column[$i], 'like', '%' . $search_value . '%');
                        } else {
                            $query->orWhere($User_Search_column[$i], 'like', '%' . $search_value . '%');
                        }
                    }
                });

                $data = $user_query->paginate(10);
            } elseif ($module == 'architect_user') {
                $User_Search_column = ['users.id', 'users.first_name', 'users.last_name', 'users.phone_number', 'users.address_line1', 'users.address_line2', 'architect.total_point', 'architect.total_point_current','architect.instagram_link','architect.firm_name', DB::raw('CONCAT(users.first_name," ",users.last_name)'), DB::raw('CONCAT(users.address_line1," ",users.address_line2)')];
                $User_Select_column = [
                    0 => 'users.id',
                    1 => 'users.first_name',
                    2 => 'users.last_name',
                    4 => 'users.phone_number',
                    5 => 'users.address_line1',
                    6 => 'users.address_line2',
                    7 => 'users.type',
                    8 => 'users.city_id',
			        9 => 'city_list.name as city_list_name',
                    10 =>'architect.total_point as life_time_points',
                    11 =>'architect.total_point_current as available_points',
                    12 =>'architect.instagram_link',
                    13 =>'architect.firm_name'
                ];
                $user_query = User::query();
                $user_query->select($User_Select_column);
                $user_query->whereIn('users.type', $dataType);

                if($module == 'architect_user'){
                    $user_query->leftJoin('architect', 'architect.user_id', '=', 'users.id');
			        $user_query->leftJoin('city_list', 'city_list.id', '=', 'users.city_id');
                    if ($isAdminOrCompanyAdmin == 1) {
                        
                    } else if ($isSalePerson == 1) {
                        $user_query->whereIn('architect.sale_person_id', $SalePersonsIds);
                    } else if ($isChannelPartner != 0) {
                        $user_query->where('architect.added_by', Auth::user()->id);
                    }
                }

                $user_query->where(function ($query) use ($search_value, $User_Search_column) {
                    for ($i = 0; $i < count($User_Search_column); $i++) {
                        if ($i == 0) {
                            $query->where($User_Search_column[$i], 'like', '%' . $search_value . '%');
                        } else {
                            $query->orWhere($User_Search_column[$i], 'like', '%' . $search_value . '%');
                        }
                    }
                });

                $data = $user_query->paginate(10);
              
            } elseif ($module == 'electrician_user') {
                $User_Search_column = ['users.id', 'users.first_name', 'users.last_name', 'users.phone_number', 'users.address_line1', 'users.address_line2', 'electrician.total_point', 'electrician.total_point_current', DB::raw('CONCAT(users.first_name," ",users.last_name)'), DB::raw('CONCAT(users.address_line1," ",users.address_line2)')];
                $User_Select_column = [
                    0 => 'users.id',
                    1 => 'users.first_name',
                    2 => 'users.last_name',
                    3 => 'users.phone_number',
                    4 => 'users.address_line1',
                    5 => 'users.address_line2',
                    6 => 'users.type',
                    7 => 'users.city_id',
			        8 => 'city_list.name as city_list_name',
                    9 => 'electrician.total_point as life_time_points',
                    10 =>'electrician.total_point_current as available_points',
                ];
                $user_query = User::query();
                $user_query->select($User_Select_column);
                $user_query->whereIn('users.type', $dataType);

                $user_query->where(function ($query) use ($search_value, $User_Search_column) {
                    for ($i = 0; $i < count($User_Search_column); $i++) {
                        if ($i == 0) {
                            $query->where($User_Search_column[$i], 'like', '%' . $search_value . '%');
                        } else {
                            $query->orWhere($User_Search_column[$i], 'like', '%' . $search_value . '%');
                        }
                    }
                });

                if($module == 'electrician_user'){
                    $user_query->leftJoin('electrician', 'electrician.user_id', '=', 'users.id');
			        $user_query->leftJoin('city_list', 'city_list.id', '=', 'users.city_id');
                    if ($isAdminOrCompanyAdmin == 1 || $isMarketingDispatcherUser == 1) {

                    } else if ($isSalePerson == 1) {
                        $user_query->whereIn('electrician.sale_person_id', $SalePersonsIds);
                    } else if ($isChannelPartner != 0) {
                        $user_query->where('electrician.added_by', Auth::user()->id);
                    }
                }

                $data = $user_query->paginate(10);
            } elseif (isset(getChannelPartners()[$dataType[0]]['lable'])) {
                $User_Search_column = ['users.id', 'users.first_name', 'users.last_name', 'users.phone_number', 'users.address_line1', 'users.address_line2', DB::raw('CONCAT(users.first_name," ",users.last_name)'), DB::raw('CONCAT(users.address_line1," ",users.address_line2)')];

                $User_Select_column = [
                    0 => 'users.id',
                    1 => 'users.first_name',
                    2 => 'users.last_name',
                    3 => 'users.email',
                    4 => 'users.phone_number',
                    5 => 'users.address_line1',
                    6 => 'users.address_line2',
                ];
                $channelpartener_query = User::query();
                $channelpartener_query->select($User_Select_column);
                $channelpartener_query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
                $channelpartener_query->whereIn('users.type', $dataType);
                if ($isSalePerson == 1) {
                    $channelpartener_query->where(function ($channelpartener_query2) use ($childSalePersonsIds) {
                        foreach ($childSalePersonsIds as $key => $value) {
                            if ($key == 0) {
                                $channelpartener_query2->whereRaw('FIND_IN_SET("' . $value . '",channel_partner.sale_persons)>0');
                            } else {
                                $channelpartener_query2->orWhereRaw('FIND_IN_SET("' . $value . '",channel_partner.sale_persons)>0');
                            }
                        }
                    });
                } else if ($isTaleSalesUser == 1) {
                    $channelpartener_query->whereIn('users.city_id', $TeleSalesCity);
                }
                $channelpartener_query->where(function ($channelpartener_query) use ($search_value, $User_Search_column) {
                    for ($i = 0; $i < count($User_Search_column); $i++) {
                        if ($i == 0) {
                            $channelpartener_query->where($User_Search_column[$i], 'like', '%' . $search_value . '%');
                        } else {
                            $channelpartener_query->orWhere($User_Search_column[$i], 'like', '%' . $search_value . '%');
                        }
                    }
                });

                $data = $channelpartener_query->paginate(10);
            } elseif ($module == 'lead' || $module == 'deal') {
                $source_type = getLeadSourceTypes();

                $Select_lead_column = [
                    'leads.id',
                    'leads.first_name',
                    'leads.last_name',
                    'leads.status',
                    'leads.phone_number',
                    'leads.addressline1 as address_line1',
                    'leads.addressline2 as address_line2',
                    'leads.city_id',
                    'city_list.name as city_list_name',
                    'leads.site_stage',
                    'crm_setting_stage_of_site.name as site_stage_name',
                    'leads.source_type',
                    'leads.source',
                ];

                $Search_deal_column = [
                    'leads.id',
                    'leads.first_name',
                    'leads.last_name',
                    'leads.phone_number',
                    'leads.addressline1',
                    'leads.addressline2',
                    'leads.site_stage',
                    'leads.source_type',
                    'leads.source',
                    DB::raw('CONCAT(leads.first_name," ",leads.last_name)'),
                    DB::raw('CONCAT(leads.addressline1," ",leads.addressline2)')
                ];

                $lead_query = Lead::query();
                $lead_query->select($Select_lead_column);
                $lead_query->where('leads.is_deal', $dataType);
                $lead_query->leftJoin('city_list', 'city_list.id', '=', 'leads.city_id');
                $lead_query->leftJoin('crm_setting_stage_of_site', 'crm_setting_stage_of_site.id', '=', 'leads.site_stage');
                
                if ($isSalePerson == 1) {
                    $lead_query->whereIn('leads.assigned_to', $childSalePersonsIds);
                }

                // Apply search filter
                $lead_query->where(function ($query) use ($search_value, $Search_deal_column) {
                    foreach ($Search_deal_column as $index => $column) {
                        if ($index == 0) {
                            $query->where($column, 'like', '%' . $search_value . '%');
                        } else {
                            $query->orWhere($column, 'like', '%' . $search_value . '%');
                        }
                    }
                });

                // Paginate the results
                $data = $lead_query->paginate(10);
                $view_data = array();
                foreach ($data as $key => $value) {
                    $source_type_explode = explode('-', $value['source_type']);
                    $source_type_lable = '';
                    foreach ($source_type as $source_key => $source_value) {
                        if ($source_value['type'] == $source_type_explode[0] && $source_value['id'] == $source_type_explode[1]) {
                            $source_type_lable = $source_value['lable'];
                        }
                    }

                    $lst_main_source = User::select('id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"))
                    ->where('id', $value['source'])
                    ->first();
                    if ($lst_main_source) {
                        $main_source_label = $lst_main_source->text;
                    } else {
                        $main_source_label = $value['source'];
                    }

                    $data[$key]['source_type'] = $source_type_lable;
                    $data[$key]['status'] = getLeadStatus()[$value['status']]['name'];
                    $data[$key]['city_id'] = $value['city_list_name'];
                    $data[$key]['source'] = $main_source_label;
                };
            }


            $response = successRes('success');
            $response['data'] = $data;
        }

        return response()
            ->json($response, $response['status_code'])
            ->header('Content-Type', 'application/json');
    }
}
