<?php

namespace App\Http\Controllers\UserFilter;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\CRMSettingStageOfSite;
use App\Models\CRMSettingCompetitors;
use App\Models\CRMSettingWantToCover;
use App\Models\CityList;
use App\Models\ArchitectCategory;
use App\Models\SalePerson;
use App\Models\CRMSettingSource;
use App\Models\Exhibition;
use App\Models\Tags;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class UserFilterController extends Controller
{
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
            if ($request->is_architect == 1) {
                $Filter_Column = getArchitectFilterColumn()[$request->column];
            } elseif ($request->is_architect == 0) {
                $Filter_Column = getElectricianFilterColumn()[$request->column];
            }
            $Filter_Condtion = getUserFilterCondtion();

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
                case 'select_order_by':
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
        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }

    function searchFilterValue(Request $request)
    {
        $data = [];
        $rules = [];
        $rules['column'] = 'required';
        $rules['condtion'] = 'required';

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $response = errorRes($validator->errors()->first());
            $response['data'] = $validator->errors();
        } else {
            $isSalePerson = isSalePerson();
            $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
            $isThirdPartyUser = isThirdPartyUser();
            $isChannelPartner = isChannelPartner(Auth::user()->type);
            if ($isSalePerson == 1) {
                $childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
            }

            $Search_Value = $request->q;
            if ($request->is_architect == 1) {
                $Filter_Column = getArchitectFilterColumn()[$request->column];
            } elseif ($request->is_architect == 0) {
                $Filter_Column = getElectricianFilterColumn()[$request->column];
            }
            $Filter_Condtion = getUserFilterCondtion()[$request->condtion];
            $Architectstatus = getArchitectsStatus();

            $data = [];
            switch ($Filter_Column['value_type']) {
                case 'select':
                    switch ($Filter_Column['code']) {
                        case 'user_status':
                            foreach ($Architectstatus as $key => $value) {
                                $new_data = [];
                                if ($Search_Value != '') {
                                    if (preg_match('/' . $Search_Value . '/i', $value['header_code'])) {
                                        $new_data['id'] = $value['id'];
                                        $new_data['text'] = $value['header_code'];
                                    }
                                } else {
                                    $new_data['id'] = $value['id'];
                                    $new_data['text'] = $value['header_code'];
                                }
                                array_push($data, $new_data);
                            }
                            break;
                        case 'user_type':
                            if ($request->is_architect == 1) {
                                foreach (getArchitects() as $key => $value) {
                                    $new_data = [];
                                    if ($Search_Value != '') {
                                        if (preg_match('/' . $Search_Value . '/i', $value['short_name'])) {
                                            $new_data['id'] = $value['id'];
                                            $new_data['text'] = $value['short_name'];
                                        }
                                    } else {
                                        $new_data['id'] = $value['id'];
                                        $new_data['text'] = $value['short_name'];
                                    }
                                    array_push($data, $new_data);
                                }
                            } elseif ($request->is_architect == 0) {
                                foreach (getElectricians() as $key => $value) {
                                    $new_data = [];
                                    if ($Search_Value != '') {
                                        if (preg_match('/' . $Search_Value . '/i', $value['short_name'])) {
                                            $new_data['id'] = $value['id'];
                                            $new_data['text'] = $value['short_name'];
                                        }
                                    } else {
                                        $new_data['id'] = $value['id'];
                                        $new_data['text'] = $value['short_name'];
                                    }
                                    array_push($data, $new_data);
                                }
                            }

                            break;
                        case 'user_total_point':
                            foreach (getPointValue() as $key => $value) {
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
                        
                        case 'account_owner':
                            $User = User::select('users.id', 'users.type', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));

                            $User->where('users.status', 1);
                            $User->where('users.type', 2);

                            if ($isAdminOrCompanyAdmin == 1) {
                                // $User->whereIn('users.type', array(0, 1, 2));
                            } elseif ($isThirdPartyUser == 1) {
                                $User->where('users.city_id', Auth::user()->city_id);
                            } elseif ($isSalePerson == 1) {
                                $User->whereIn('users.id', $childSalePersonsIds);
                            } elseif ($isChannelPartner != 0) {
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
                        case 'user_city_id':
                            $data = CityList::select('id', 'name as text');
                            $data->where('city_list.status', 1);
                            $data->where('city_list.name', 'like', '%' . $Search_Value . '%');
                            $data->limit(10);
                            $data = $data->get();
                            break;
                        case 'user_created_by':
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
                        case 'user_tag':
                            $data = Tags::select('id', 'tagname as text');
                            $data->where('tag_master.isactive', 1);
                            $data->where('tag_master.tag_type', 202);
                            $data->where('tag_master.tagname', 'like', '%' . $Search_Value . '%');
                            $data->limit(10);
                            $data = $data->get();

                            break;
                        case 'architect_category_id':
                            $data = ArchitectCategory::select('id', 'name as text');
                            $data->where('name', 'like', '%' . $Search_Value . '%');
                            $data->limit(10);
                            $data = $data->get();

                            break;
                        case 'user_source':
                            $isArchitect = isArchitect();
                            $isSalePerson = isSalePerson();
                            $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
                            $isThirdPartyUser = isThirdPartyUser();
                            $isChannelPartner = isChannelPartner(Auth::user()->type);


                            if ($request->source_type == '' || $request->source_type == null) {
                                $response = errorRes("please select source type");
                                return response()->json($response)->header('Content-Type', 'application/json');
                            }
                            $source_type = explode("-", $request->source_type);


                            if ($source_type[0] == "user") {

                                if (isset(getChannelPartners()[$source_type[1]]['short_name'])) {

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
                                            $salePerson = SalePerson::select('cities')->where('user_id', Auth::user()->id)->first();
                                            $cities = array();
                                            if ($salePerson) {

                                                $cities = explode(",", $salePerson->cities);
                                            } else {
                                                $cities = array(0);
                                            }
                                            $data->whereIn('users.city_id', $cities);
                                        } elseif ($isChannelPartner != 0) {
                                            $data->where('users.city_id', Auth::user()->city_id);
                                        }

                                        $data->whereIn('users.type', [201, 202]);

                                    } else if ($source_type[1] == 302) {
                                        // FOR ELECTRICIAN
                                        if ($isSalePerson == 1) {
                                            $salePerson = SalePerson::select('cities')->where('user_id', Auth::user()->id)->first();
                                            $cities = array();
                                            if ($salePerson) {

                                                $cities = explode(",", $salePerson->cities);
                                            } else {
                                                $cities = array(0);
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
                                    $newdata = array();
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

                            } elseif ($source_type[0] == "master") {
                                $data = CRMSettingSource::select('id', 'name as text');
                                $data->where('crm_setting_source.status', 1);
                                $data->where('crm_setting_source.source_type_id', $source_type[1]);
                                $data->where('crm_setting_source.name', 'like', "%" . $Search_Value . "%");
                                $data->limit(5);
                                $data = $data->get();
                            } elseif ($source_type[0] == "exhibition") {
                                $data = Exhibition::select('id', 'name as text');
                                $data->where('exhibition.name', 'like', "%" . $Search_Value . "%");
                                $data->limit(5);
                                $data = $data->get();
                            } else {
                                $data = "";
                            }
                            break;
                        default:
                            $data = errorRes();
                            break;
                    }

                    break;

                case 'date':
                    switch ($Filter_Column['code']) {
                        case 'user_created_at':
                            foreach (getUserDateFilterValue() as $key => $value) {
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

                case 'select_order_by':
                    switch ($Filter_Column['code']) {
                        case 'user_total_point':
                            foreach (getPointValue() as $key => $value) {
                                $new_data = [];

                                $new_data['id'] = $value['id'];
                                $new_data['text'] = $value['name'];

                                array_push($data, $new_data);
                            }
                            break;
                            case 'user_total_point_current':
                                foreach (getPointValue() as $key => $value) {
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

                default:
                    break;
            }
        }

        $response = [];
        $response['results'] = $data;
        $response['pagination']['more'] = true;
        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }
}
