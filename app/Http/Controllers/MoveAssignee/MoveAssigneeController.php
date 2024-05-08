<?php

namespace App\Http\Controllers\MoveAssignee;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Doctrine\DBAL\Schema\Index;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\SalePerson;
use Illuminate\Support\Facades\Auth;
use App\Models\Architect;
use App\Models\Electrician;
use App\Models\ChannelPartner;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MoveAssigneeController extends Controller
{
    public function index()
    {
        $data = array();
        $data['title'] = "Move Assignee";
        return view('move_assignee_new/index', compact('data'));
    }

    public function searchAssignedUser(Request $request)
    {

        $fromUserType = 0;

        if ($request->user_id != "") {

            $SalePerson = SalePerson::select('type')->where('user_id', $request->user_id)->first();
            if ($SalePerson) {
                $fromUserType = $SalePerson->type;
            }
        }

        $User = $UserResponse = array();
        $q = $request->q;
        $User = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));
        $User->leftJoin('sale_person', 'sale_person.id', '=', 'users.reference_id');
        $User->where('users.status', 1);
        $User->whereIn('users.type', array(2));
        if ($fromUserType != 0) {

            if ($request->is_channel_partner == "true") {

                $User->where('sale_person.type', $fromUserType);
            }
            $User->where('users.id', "!=", $request->user_id);
        }

        $User->where(function ($query) use ($q) {
            $query->where('users.first_name', 'like', '%' . $q . '%');
            $query->orWhere('users.last_name', 'like', '%' . $q . '%');
        });
        $User->limit(5);
        $User = $User->get();

        if (count($User) > 0) {
            foreach ($User as $User_key => $User_value) {
                $UserResponse[$User_key]['id'] = $User_value['id'];
                $UserResponse[$User_key]['text'] = $User_value['full_name'];
            }
        }
        $response = array();
        $response['results'] = $UserResponse;
        $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function status(Request $request)
    {
        if ($request->type == 1) {
            $LeadStatus = getLeadStatus();
            $finalArray = array();
            foreach ($LeadStatus as $key => $value) {
                if ($value['type'] == 0) {
                    $countFinal = count($finalArray);
                    $finalArray[$countFinal] = array();
                    $finalArray[$countFinal]['id'] = $value['id'];
                    $finalArray[$countFinal]['text'] = $value['name'];
                }
            }
        } else if ($request->type == 2) {
            $LeadStatus = getLeadStatus();

            $finalArray = array();

            foreach ($LeadStatus as $key => $value) {
                if ($value['type'] == 1) {
                    $countFinal = count($finalArray);
                    $finalArray[$countFinal] = array();
                    $finalArray[$countFinal]['id'] = $value['id'];
                    $finalArray[$countFinal]['text'] = $value['name'];
                }
            }
        } else if ($request->type == 3) {
            $LeadStatus = getArchitectsStatus();

            $finalArray = array();

            foreach ($LeadStatus as $key => $value) {
                $countFinal = count($finalArray);
                $finalArray[$countFinal] = array();
                $finalArray[$countFinal]['id'] = $value['id'];
                $finalArray[$countFinal]['text'] = $value['code'];
            }
        } else if ($request->type == 4) {
            $LeadStatus = getElectricianStatus();

            $finalArray = array();

            foreach ($LeadStatus as $key => $value) {
                $countFinal = count($finalArray);
                $finalArray[$countFinal] = array();
                $finalArray[$countFinal]['id'] = $value['id'];
                $finalArray[$countFinal]['text'] = $value['code'];
            }
        } else if ($request->type == 5) {
            $LeadStatus = userStatus();

            $finalArray = array();

            foreach ($LeadStatus as $key => $value) {
                $countFinal = count($finalArray);
                $finalArray[$countFinal] = array();
                $finalArray[$countFinal]['id'] = $value['id'];
                $finalArray[$countFinal]['text'] = $value['name'];
            }
        }


        $response = array();
        $response['results'] = $finalArray;
        $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function ajax(Request $request)
    {

        $LeadStatus = getLeadStatus();

        $SearchLeadColumn = [
            'leads.id',
            'leads.first_name',
            'leads.last_name',
            'leads.phone_number',
        ];

        $LeadColumn = [
            'leads.id',
            'leads.first_name',
            'leads.last_name',
            'leads.phone_number',
            'leads.status',
        ];

        $SearchUserColumn = [
            'users.id',
            'users.first_name',
            'users.last_name',
            'users.phone_number',
        ];

        $UserColumn = [
            'users.id',
            'users.first_name',
            'users.last_name',
            'users.phone_number',
        ];

        $Type = isset($request->type) ? explode(',', $request->type) : [];
        $FromAssign = isset($request->from_assign) ? $request->from_assign : 0;
        $Status = isset($request->status) ? $request->status : -1;
        

        $recordsTotal = 0;
        $recordsFilteredCount = 0;
        if($Type != [] && $Type != null) {
            if(in_array(0, $Type)) {
                $viewData = [];
                $Alldata = array();
                foreach ($Type as $key => $value) {
                    if($value == 1) {
                        $LeadData = Lead::select($LeadColumn);
                        $LeadData->where('assigned_to', $FromAssign);
                        if($Status != -1){
                            $LeadData->where('status', $Status);
                        }
                        $LeadData->where('is_deal', 0);
                        $LeadDataCount = $LeadData->count();
                        $recordsTotal += (int)$LeadDataCount;
    

                        $LeadData = Lead::select($LeadColumn);
                        $LeadData->where('assigned_to', $FromAssign);
                        if($Status != -1){
                            $LeadData->where('status', $Status);
                        }

                        $LeadData->where('is_deal', 0);
                        $search_value = '';
                        if (isset($request['search']['value'])) {
                            $search_value = $request['search']['value'];
                            $LeadData->where(function ($query) use ($search_value, $SearchLeadColumn) {
                                for ($i = 0; $i < count($SearchLeadColumn); $i++) {
                                    if ($i == 0) {
                                        $query->whereRaw($SearchLeadColumn[$i] . ' like ? ', [$search_value]);
                                        $query->orWhereRaw($SearchLeadColumn[$i] . ' like ? ', ['%' . $search_value . '%']);
                                    } else {
                                        $query->orWhereRaw($SearchLeadColumn[$i] . ' like ? ', [$search_value]);
                                        $query->orWhereRaw($SearchLeadColumn[$i] . ' like ? ', ['%' . $search_value . '%']);
                                    }
                                }
                            });
                        }
                        $recordsFilteredCount += (int)$LeadData->count();
                        $LeadData = $LeadData->get();

                        foreach ($LeadData as $key => $value) {
                            $LeadData[$key]['type_id'] = 1;
                            $LeadData[$key]['type'] = '<span class="badge badge-soft-success  badge-pill badgefont-size-11">LEAD</span>';
                            $LeadData[$key]['status'] = highlightString($LeadStatus[$value['status']]['name'],$search_value);
                        }

                        $Alldata = array_merge($Alldata, $LeadData->toArray());
                    } else if($value == 2) {
                        $DealData = Lead::select($LeadColumn);
                        $DealData->where('assigned_to', $FromAssign);
                        if($Status != -1){
                            $DealData->where('status', $Status);
                        }
                        $DealData->where('is_deal', 1);
                        $DealDataCount = $DealData->count();
                        $recordsTotal += (int)$DealDataCount;
    

                        $DealData = Lead::select($LeadColumn);
                        $DealData->where('assigned_to', $FromAssign);
                        if($Status != -1){
                            $DealData->where('status', $Status);
                        }
                        $DealData->where('is_deal', 1);
                        $search_value = '';
                        if (isset($request['search']['value'])) {
                            $search_value = $request['search']['value'];
                            $DealData->where(function ($query) use ($search_value, $SearchLeadColumn) {
                                for ($i = 0; $i < count($SearchLeadColumn); $i++) {
                                    if ($i == 0) {
                                        $query->whereRaw($SearchLeadColumn[$i] . ' like ? ', [$search_value]);
                                        $query->orWhereRaw($SearchLeadColumn[$i] . ' like ? ', ['%' . $search_value . '%']);
                                    } else {
                                        $query->orWhereRaw($SearchLeadColumn[$i] . ' like ? ', [$search_value]);
                                        $query->orWhereRaw($SearchLeadColumn[$i] . ' like ? ', ['%' . $search_value . '%']);
                                    }
                                }
                            });
                        }
                        $recordsFilteredCount += (int)$DealData->count();
                        $DealData = $DealData->get();

                        foreach ($DealData as $key => $value) {
                            $DealData[$key]['type_id'] = 2;
                            $DealData[$key]['type'] = '<span class="badge badge-soft-primary  badge-pill badgefont-size-11">DEAL</span>';
                            $DealData[$key]['status'] = highlightString($LeadStatus[$value['status']]['name'],$search_value);
                        }

                        $Alldata = array_merge($Alldata, $DealData->toArray());
                    } else if($value == 3) {
                        $ArchitectData = User::select($UserColumn);
                        $ArchitectData->selectRaw('architect.status');
                        $ArchitectData->leftJoin('architect', 'architect.user_id', '=', 'users.id');
                        $ArchitectData->where('architect.sale_person_id', $FromAssign);
                        if($Status != -1){
                            $ArchitectData->where('architect.status', $Status);
                        }
                        $ArchitectDataCount = $ArchitectData->count();
                        $recordsTotal += (int)$ArchitectDataCount;
    

                        $ArchitectData = User::select($UserColumn);
                        $ArchitectData->selectRaw('architect.status');
                        $ArchitectData->leftJoin('architect', 'architect.user_id', '=', 'users.id');
                        $ArchitectData->where('architect.sale_person_id', $FromAssign);
                        if($Status != -1){
                            $ArchitectData->where('architect.status', $Status);
                        }
                        $search_value = '';
                        if (isset($request['search']['value'])) {
                            $search_value = $request['search']['value'];
                            $ArchitectData->where(function ($query) use ($search_value, $SearchUserColumn) {
                                for ($i = 0; $i < count($SearchUserColumn); $i++) {
                                    if ($i == 0) {
                                        $query->whereRaw($SearchUserColumn[$i] . ' like ? ', [$search_value]);
                                        $query->orWhereRaw($SearchUserColumn[$i] . ' like ? ', ['%' . $search_value . '%']);
                                    } else {
                                        $query->orWhereRaw($SearchUserColumn[$i] . ' like ? ', [$search_value]);
                                        $query->orWhereRaw($SearchUserColumn[$i] . ' like ? ', ['%' . $search_value . '%']);
                                    }
                                }
                            });
                        }
                        $recordsFilteredCount += (int)$ArchitectData->count();
                        $ArchitectData = $ArchitectData->get();

                        foreach ($ArchitectData as $key => $value) {
                            $ArchitectData[$key]['type_id'] = 3;
                            $ArchitectData[$key]['type'] = '<span class="badge badge-soft-danger  badge-pill badgefont-size-11">ARCHITECT</span>';
                            $ArchitectData[$key]['status'] = highlightString(getArchitectsStatus()[$value['status']]['code'],$search_value);
                        }

                        $Alldata = array_merge($Alldata, $ArchitectData->toArray());
                    } else if($value == 4) {
                        $ElectricianData = User::select($UserColumn);
                        $ElectricianData->selectRaw('electrician.status');
                        $ElectricianData->leftJoin('electrician', 'electrician.user_id', '=', 'users.id');
                        $ElectricianData->where('electrician.sale_person_id', $FromAssign);
                        if($Status != -1){
                            $ElectricianData->where('electrician.status', $Status);
                        }
                        $ElectricianDataCount = $ElectricianData->count();
                        $recordsTotal += (int)$ElectricianDataCount;
    

                        $ElectricianData = User::select($UserColumn);
                        $ElectricianData->selectRaw('electrician.status');
                        $ElectricianData->leftJoin('electrician', 'electrician.user_id', '=', 'users.id');
                        $ElectricianData->where('electrician.sale_person_id', $FromAssign);
                        if($Status != -1){
                            $ElectricianData->where('electrician.status', $Status);
                        }
                        $search_value = '';
                        if (isset($request['search']['value'])) {
                            $search_value = $request['search']['value'];
                            $ElectricianData->where(function ($query) use ($search_value, $SearchUserColumn) {
                                for ($i = 0; $i < count($SearchUserColumn); $i++) {
                                    if ($i == 0) {
                                        $query->whereRaw($SearchUserColumn[$i] . ' like ? ', [$search_value]);
                                        $query->orWhereRaw($SearchUserColumn[$i] . ' like ? ', ['%' . $search_value . '%']);
                                    } else {
                                        $query->orWhereRaw($SearchUserColumn[$i] . ' like ? ', [$search_value]);
                                        $query->orWhereRaw($SearchUserColumn[$i] . ' like ? ', ['%' . $search_value . '%']);
                                    }
                                }
                            });
                        }
                        $recordsFilteredCount += (int)$ElectricianData->count();
                        $ElectricianData = $ElectricianData->get();
                        
                        foreach ($ElectricianData as $key => $value) {
                            $ElectricianData[$key]['type_id'] = 4;
                            $ElectricianData[$key]['type'] = '<span class="badge badge-soft-warning  badge-pill badgefont-size-11">ELECTRICIAN</span>';
                            $ElectricianData[$key]['status'] = highlightString(getElectricianStatus()[$value['status']]['code'],$search_value);
                        }

                        $Alldata = array_merge($Alldata, $ElectricianData->toArray());
                    } else if($value == 5) {
                        $ChannelPartnerData = User::select($UserColumn);
                        $ChannelPartnerData->selectRaw('users.status');
                        $ChannelPartnerData->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
                        $ChannelPartnerData->whereRaw("FIND_IN_SET(".$FromAssign.", channel_partner.sale_persons)");
                        if($Status != -1){
                            $ChannelPartnerData->where('users.status', $Status);
                        }
                        $ChannelPartnerDataCount = $ChannelPartnerData->count();
                        $recordsTotal += (int)$ChannelPartnerDataCount;
    

                        $ChannelPartnerData = User::select($UserColumn);
                        $ChannelPartnerData->selectRaw('users.status');
                        $ChannelPartnerData->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
                        $ChannelPartnerData->whereRaw("FIND_IN_SET(".$FromAssign." , channel_partner.sale_persons)");
                        if($Status != -1){
                            $ChannelPartnerData->where('users.status', $Status);
                        }
                        $search_value = '';
                        if (isset($request['search']['value'])) {
                            $search_value = $request['search']['value'];
                            $ChannelPartnerData->where(function ($query) use ($search_value, $SearchUserColumn) {
                                for ($i = 0; $i < count($SearchUserColumn); $i++) {
                                    if ($i == 0) {
                                        $query->whereRaw($SearchUserColumn[$i] . ' like ? ', [$search_value]);
                                        $query->orWhereRaw($SearchUserColumn[$i] . ' like ? ', ['%' . $search_value . '%']);
                                    } else {
                                        $query->orWhereRaw($SearchUserColumn[$i] . ' like ? ', [$search_value]);
                                        $query->orWhereRaw($SearchUserColumn[$i] . ' like ? ', ['%' . $search_value . '%']);
                                    }
                                }
                            });
                        }
                        $recordsFilteredCount += (int)$ChannelPartnerData->count();
                        $ChannelPartnerData = $ChannelPartnerData->get();
                        foreach ($ChannelPartnerData as $key => $value) {
                            $ChannelPartnerData[$key]['type_id'] = 5;
                            $ChannelPartnerData[$key]['type'] = '<span class="badge badge-soft-info  badge-pill badgefont-size-11">CHANNEL PARTNER</span>';
                            $ChannelPartnerData[$key]['status'] = highlightString(userStatus()[$value['status']]['name'],$search_value);
                        }
                        $Alldata = array_merge($Alldata, $ChannelPartnerData->toArray());
                    }
                }
                foreach ($Alldata as $key => $value) {
                    $viewData[$key] = [];
                    $switch = "";
                    $switch .= '<div class="form-check">';
                    $switch .= '<input class="form-check-input dataTableCheckBox" type="checkbox"  data-id="'.$value['id'].'" data-type="'.$value['type_id'].'" checked>';
                    $switch .= '</div>';
                    
                    $viewData[$key]['switch'] = $switch;
                    $viewData[$key]['id'] = '<p class="mb-1">'.highlightString($value['id'],$search_value).'</p>';
                    $viewData[$key]['type'] = '<p class="mb-1">'.$value['type'].'</p>';
                    $viewData[$key]['first_name'] = '<p>'.highlightString($value['first_name'].' '.$value['last_name'],$search_value).'</p>';
                    $viewData[$key]['phone_number'] = '<p>'.highlightString($value['phone_number'],$search_value).'</p>';
                    $viewData[$key]['status'] = '<p>'.highlightString($value['status'],$search_value).'</p>';
                }

            } else {
                $viewData = [];
                if(in_array(1, $Type)) {
                    $LeadData = Lead::select($LeadColumn);
                    $LeadData->where('assigned_to', $FromAssign);
                    if($Status != -1){
                        $LeadData->where('status', $Status);
                    }
                    $LeadData->where('is_deal', 0);
                    $LeadDataCount = $LeadData->count();
                    $recordsTotal += (int)$LeadDataCount;

                    $LeadData = Lead::select($LeadColumn);
                    $LeadData->where('assigned_to', $FromAssign);
                    if($Status != -1){
                        $LeadData->where('status', $Status);
                    }
                    $LeadData->where('is_deal', 0);
                    $search_value = '';
                    if (isset($request['search']['value'])) {
                        $search_value = $request['search']['value'];
                        $LeadData->where(function ($query) use ($search_value, $SearchLeadColumn) {
                            for ($i = 0; $i < count($SearchLeadColumn); $i++) {
                                if ($i == 0) {
                                    $query->whereRaw($SearchLeadColumn[$i] . ' like ? ', [$search_value]);
                                    $query->orWhereRaw($SearchLeadColumn[$i] . ' like ? ', ['%' . $search_value . '%']);
                                } else {
                                    $query->orWhereRaw($SearchLeadColumn[$i] . ' like ? ', [$search_value]);
                                    $query->orWhereRaw($SearchLeadColumn[$i] . ' like ? ', ['%' . $search_value . '%']);
                                }
                            }
                        });
                    }
                    $recordsFilteredCount += (int)$LeadData->count();
                    $LeadData = $LeadData->get();
                    // $Alldata = array_merge($Alldata, $LeadData->toArray());
                    foreach ($LeadData as $key => $value) {
                        $viewData[$key] = [];
                        $switch = "";
                        $switch .= '<div class="form-check">';
                        $switch .= '<input class="form-check-input dataTableCheckBox" type="checkbox"  data-id="'.$value['id'].'" data-type="1" checked>';
                        $switch .= '</div>';

                        $viewData[$key]['switch'] = $switch;
                        $viewData[$key]['id'] = '<p class="mb-1">'.highlightString($value['id'],$search_value).'</p>';
                        $viewData[$key]['type'] = '<span class="badge badge-soft-success  badge-pill badgefont-size-11">LEAD</span>';
                        $viewData[$key]['first_name'] = '<p>'.highlightString($value['first_name'].' '.$value['last_name'],$search_value).'</p>';
                        $viewData[$key]['phone_number'] = '<p>'.highlightString($value['phone_number'],$search_value).'</p>';
                        $viewData[$key]['status'] = '<p>'.highlightString($LeadStatus[$value['status']]['name'],$search_value).'</p>';
                    }
                } else if(in_array(2, $Type)) {
                    $DealData = Lead::select($LeadColumn);
                    $DealData->where('assigned_to', $FromAssign);
                    if($Status != -1){
                        $DealData->where('status', $Status);
                    }
                    $DealData->where('is_deal', 1);
                    $DealDataCount = $DealData->count();
                    $recordsTotal += (int)$DealDataCount;

                    $DealData = Lead::select($LeadColumn);
                    $DealData->where('assigned_to', $FromAssign);
                    if($Status != -1){
                        $DealData->where('status', $Status);
                    }
                    $DealData->where('is_deal', 1);
                    $search_value = '';
                    if (isset($request['search']['value'])) {
                        $search_value = $request['search']['value'];
                        $DealData->where(function ($query) use ($search_value, $SearchLeadColumn) {
                            for ($i = 0; $i < count($SearchLeadColumn); $i++) {
                                if ($i == 0) {
                                    $query->whereRaw($SearchLeadColumn[$i] . ' like ? ', [$search_value]);
                                    $query->orWhereRaw($SearchLeadColumn[$i] . ' like ? ', ['%' . $search_value . '%']);
                                } else {
                                    $query->orWhereRaw($SearchLeadColumn[$i] . ' like ? ', [$search_value]);
                                    $query->orWhereRaw($SearchLeadColumn[$i] . ' like ? ', ['%' . $search_value . '%']);
                                }
                            }
                        });
                    }
                    $recordsFilteredCount += (int)$DealData->count();
                    $DealData = $DealData->get();
                    // $Alldata = array_merge($Alldata, $DealData->toArray());
                    foreach ($DealData as $key => $value) {
                        $viewData[$key] = [];
                        $switch = "";
                        $switch .= '<div class="form-check">';
                        $switch .= '<input class="form-check-input dataTableCheckBox" type="checkbox"  data-id="'.$value['id'].'" data-type="2" checked>';
                        $switch .= '</div>';
                        
                        $viewData[$key]['switch'] = $switch;
                        $viewData[$key]['id'] = '<p class="mb-1">'.highlightString($value['id'],$search_value).'</p>';
                        $viewData[$key]['type'] = '<span class="badge badge-soft-primary  badge-pill badgefont-size-11">DEAL</span>';
                        $viewData[$key]['first_name'] = '<p>'.highlightString($value['first_name'].' '.$value['last_name'],$search_value).'</p>';
                        $viewData[$key]['phone_number'] = '<p>'.highlightString($value['phone_number'],$search_value).'</p>';
                        $viewData[$key]['status'] = '<p>'.highlightString($LeadStatus[$value['status']]['name'],$search_value).'</p>';
                    }
                } else if(in_array(3, $Type)) {
                    $ArchitectData = User::select($UserColumn);
                    $ArchitectData->selectRaw('architect.status');
                    $ArchitectData->leftJoin('architect', 'architect.user_id', '=', 'users.id');
                    $ArchitectData->where('architect.sale_person_id', $FromAssign);
                    if($Status != -1){
                        $ArchitectData->where('architect.status', $Status);
                    }
                    $ArchitectDataCount = $ArchitectData->count();
                    $recordsTotal += (int)$ArchitectDataCount;

                    $ArchitectData = User::select($UserColumn);
                    $ArchitectData->selectRaw('architect.status');
                    $ArchitectData->leftJoin('architect', 'architect.user_id', '=', 'users.id');
                    $ArchitectData->where('architect.sale_person_id', $FromAssign);
                    if($Status != -1){
                        $ArchitectData->where('architect.status', $Status);
                    }
                    $search_value = '';
                    if (isset($request['search']['value'])) {
                        $search_value = $request['search']['value'];
                        $ArchitectData->where(function ($query) use ($search_value, $SearchUserColumn) {
                            for ($i = 0; $i < count($SearchUserColumn); $i++) {
                                if ($i == 0) {
                                    $query->whereRaw($SearchUserColumn[$i] . ' like ? ', [$search_value]);
                                    $query->orWhereRaw($SearchUserColumn[$i] . ' like ? ', ['%' . $search_value . '%']);
                                } else {
                                    $query->orWhereRaw($SearchUserColumn[$i] . ' like ? ', [$search_value]);
                                    $query->orWhereRaw($SearchUserColumn[$i] . ' like ? ', ['%' . $search_value . '%']);
                                }
                            }
                        });
                    }
                    $recordsFilteredCount += (int)$ArchitectData->count();
                    $ArchitectData = $ArchitectData->get();
                    // $Alldata = array_merge($Alldata, $ArchitectData->toArray());
                    foreach ($ArchitectData as $key => $value) {
                        $viewData[$key] = [];
                        $switch = "";
                        $switch .= '<div class="form-check">';
                        $switch .= '<input class="form-check-input dataTableCheckBox" type="checkbox"  data-id="'.$value['id'].'" data-type="3" checked>';
                        $switch .= '</div>';
                        
                        $viewData[$key]['switch'] = $switch;
                        $viewData[$key]['id'] = '<p class="mb-1">'.highlightString($value['id'],$search_value).'</p>';
                        $viewData[$key]['type'] = '<span class="badge badge-soft-danger  badge-pill badgefont-size-11">ARCHITECT</span>';
                        $viewData[$key]['first_name'] = '<p>'.highlightString($value['first_name'].' '.$value['last_name'],$search_value).'</p>';
                        $viewData[$key]['phone_number'] = '<p>'.highlightString($value['phone_number'],$search_value).'</p>';
                        $viewData[$key]['status'] = '<p>'.highlightString(getArchitectsStatus()[$value['status']]['code'],$search_value).'</p>';
                    }
                } else if(in_array(4, $Type)) {
                    $ElectricianData = User::select($UserColumn);
                    $ElectricianData->selectRaw('electrician.status');
                    $ElectricianData->leftJoin('electrician', 'electrician.user_id', '=', 'users.id');
                    $ElectricianData->where('electrician.sale_person_id', $FromAssign);
                    if($Status != -1){
                        $ElectricianData->where('electrician.status', $Status);
                    }
                    $ElectricianDataCount = $ElectricianData->count();
                    $recordsTotal += (int)$ElectricianDataCount;

                    $ElectricianData = User::select($UserColumn);
                    $ElectricianData->selectRaw('electrician.status');
                    $ElectricianData->leftJoin('electrician', 'electrician.user_id', '=', 'users.id');
                    $ElectricianData->where('electrician.sale_person_id', $FromAssign);
                    if($Status != -1){
                        $ElectricianData->where('electrician.status', $Status);
                    }
                    $search_value = '';
                    if (isset($request['search']['value'])) {
                        $search_value = $request['search']['value'];
                        $ElectricianData->where(function ($query) use ($search_value, $SearchUserColumn) {
                            for ($i = 0; $i < count($SearchUserColumn); $i++) {
                                if ($i == 0) {
                                    $query->whereRaw($SearchUserColumn[$i] . ' like ? ', [$search_value]);
                                    $query->orWhereRaw($SearchUserColumn[$i] . ' like ? ', ['%' . $search_value . '%']);
                                } else {
                                    $query->orWhereRaw($SearchUserColumn[$i] . ' like ? ', [$search_value]);
                                    $query->orWhereRaw($SearchUserColumn[$i] . ' like ? ', ['%' . $search_value . '%']);
                                }
                            }
                        });
                    }
                    $recordsFilteredCount += (int)$ElectricianData->count();
                    $ElectricianData = $ElectricianData->get();
                    // $Alldata = array_merge($Alldata, $ElectricianData->toArray());
                    foreach ($ElectricianData as $key => $value) {
                        $viewData[$key] = [];
                        $switch = "";
                        $switch .= '<div class="form-check">';
                        $switch .= '<input class="form-check-input dataTableCheckBox" type="checkbox"  data-id="'.$value['id'].'" data-type="4" checked>';
                        $switch .= '</div>';
                        
                        $viewData[$key]['switch'] = $switch;
                        $viewData[$key]['id'] = '<p class="mb-1">'.highlightString($value['id'],$search_value).'</p>';
                        $viewData[$key]['type'] = '<span class="badge badge-soft-warning  badge-pill badgefont-size-11">ELECTRICIAN</span>';
                        $viewData[$key]['first_name'] = '<p>'.highlightString($value['first_name'].' '.$value['last_name'],$search_value).'</p>';
                        $viewData[$key]['phone_number'] = '<p>'.highlightString($value['phone_number'],$search_value).'</p>';
                        $viewData[$key]['status'] = '<p>'.highlightString(getElectricianStatus()[$value['status']]['code'],$search_value).'</p>';
                    }
                } else if(in_array(5, $Type)) {
                    $ChannelPartnerData = User::select($UserColumn);
                    $ChannelPartnerData->selectRaw('users.status');
                    $ChannelPartnerData->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
                    $ChannelPartnerData->whereRaw("FIND_IN_SET(".$FromAssign.", channel_partner.sale_persons)");
                    if($Status != -1){
                        $ChannelPartnerData->where('users.status', $Status);
                    }
                    $ChannelPartnerDataCount = $ChannelPartnerData->count();
                    $recordsTotal += (int)$ChannelPartnerDataCount;

                    $ChannelPartnerData = User::select($UserColumn);
                    $ChannelPartnerData->selectRaw('users.status');
                    $ChannelPartnerData->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
                    $ChannelPartnerData->whereRaw("FIND_IN_SET(".$FromAssign.", channel_partner.sale_persons)");
                    if($Status != -1){
                        $ChannelPartnerData->where('users.status', $Status);
                    }
                    $search_value = '';
                    if (isset($request['search']['value'])) {
                        $search_value = $request['search']['value'];
                        $ChannelPartnerData->where(function ($query) use ($search_value, $SearchUserColumn) {
                            for ($i = 0; $i < count($SearchUserColumn); $i++) {
                                if ($i == 0) {
                                    $query->whereRaw($SearchUserColumn[$i] . ' like ? ', [$search_value]);
                                    $query->orWhereRaw($SearchUserColumn[$i] . ' like ? ', ['%' . $search_value . '%']);
                                } else {
                                    $query->orWhereRaw($SearchUserColumn[$i] . ' like ? ', [$search_value]);
                                    $query->orWhereRaw($SearchUserColumn[$i] . ' like ? ', ['%' . $search_value . '%']);
                                }
                            }
                        });
                    }
                    $recordsFilteredCount += (int)$ChannelPartnerData->count();
                    $ChannelPartnerData = $ChannelPartnerData->get();
                    // $Alldata = array_merge($Alldata, $ChannelPartnerData->toArray());
                    foreach ($ChannelPartnerData as $key => $value) {
                        $viewData[$key] = [];
                        $switch = "";
                        $switch .= '<div class="form-check">';
                        $switch .= '<input class="form-check-input dataTableCheckBox" type="checkbox" data-id="'.$value['id'].'" data-type="5" checked>';
                        $switch .= '</div>';
                        
                        $viewData[$key]['switch'] = $switch;
                        $viewData[$key]['id'] = '<p class="mb-1">'.highlightString($value['id'],$search_value).'</p>';
                        $viewData[$key]['type'] = '<span class="badge badge-soft-info  badge-pill badgefont-size-11">CHANNEL PARTNER</span>';
                        $viewData[$key]['first_name'] = '<p>'.highlightString($value['first_name'].' '.$value['last_name'],$search_value).'</p>';
                        $viewData[$key]['phone_number'] = '<p>'.highlightString($value['phone_number'],$search_value).'</p>';
                        $viewData[$key]['status'] = '<p>'.highlightString(userStatus()[$value['status']]['name'],$search_value).'</p>';
                    }
                }
            }

            $recordsFiltered = $recordsTotal;
            $recordsFiltered = $recordsFilteredCount;

            
        } else {
            $recordsFiltered = $recordsTotal;
            $recordsFiltered = $recordsFilteredCount;
            $viewData = [];
        }

        

        $jsonData = [
            'draw' => intval($request['draw']),
            'recordsTotal' => intval($recordsTotal),
            'recordsFiltered' => intval($recordsFiltered),
            'data' => $viewData,
        ];
        return $jsonData;
    }

    public function save(Request $request) {

        $validator = Validator::make($request->all(), [
            'to_assign' => ['required'],
        ]);

        if(count($request->data) == 0){
            $response = errorRes("Please select data");
            return response()->json($response)->header('Content-Type', 'application/json');
        }

        if ($validator->fails()) {
            $response = errorRes($validator->errors()->first());
            $response['data'] = $validator->errors()->first();
            return response()->json($response)->header('Content-Type', 'application/json');
        } else {

            $user_ids = "";
            foreach($request->data as $value) {
                if($value['type'] == 1) {
                    $Lead = Lead::find($value['id']);
                    $Lead->assigned_to = $request->to_assign;
                    $Lead->save();
                } else if($value['type'] == 2) {
                    $Deal = Lead::find($value['id']);
                    $Deal->assigned_to = $request->to_assign;
                    $Deal->save();
                } else if($value['type'] == 3) {
                    $Architect = Architect::where('user_id', $value['id'])->first();
                    $Architect->sale_person_id = $request->to_assign;
                    $Architect->save();
                } else if($value['type'] == 4) {
                    $Electrician = Electrician::where('user_id', $value['id'])->first();
                    $Electrician->sale_person_id = $request->to_assign;
                    $Electrician->save();
                } else if($value['type'] == 5) {
                    $ChannelPartner = ChannelPartner::where('user_id', $value['id'])->first();
                    $ChannelPartner->sale_persons = $request->to_assign;
                    $ChannelPartner->save();
                }
                $user_ids .= $value['id'].', ';
            }

            $fromAssign = User::find($request->from_assign)['first_name'] .' '. User::find($request->from_assign)['last_name'];
            $toAssign = User::find($request->to_assign)['first_name'] .' '. User::find($request->to_assign)['last_name'];
            
            $debugLog['name'] = 'move-assign';
            $debugLog['description'] = rtrim($user_ids, ', ').' transfer assign person '. $fromAssign .' To '. $toAssign;
            $response = successRes();
            saveDebugLog($debugLog);
            return response()->json($response)->header('Content-Type', 'application/json');
        }
    }
}
