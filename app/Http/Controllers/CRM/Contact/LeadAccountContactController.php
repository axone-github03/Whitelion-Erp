<?php

namespace App\Http\Controllers\CRM\Contact;

use App\Http\Controllers\Controller;

use App\Models\CRMSettingContactTag;
use App\Models\LeadContact;
use App\Models\LeadAccountContact;
use App\Models\CRMSettingStageOfSite;
use App\Models\Lead;
use App\Models\User;
use App\Models\CityList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use DB;

class LeadAccountContactController extends Controller
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

    public function index(Request $request)
    {

        $data = array();
        $data['title'] = "Account Contacts";
        return view('crm/contact/account_contacts', compact('data'));
    }

    public function table(Request $request)
    {

        $data = array();
        $data['title'] = "Account Contacts";
        $data['id'] = isset($request->id) ? $request->id : 0;
        $data['is_account_contact'] = 1;
        return view('crm/contact/account_contact_detail', compact('data'));
    }

    public function ajax(Request $request)
    {
        $searchColumns = array(

            0 => 'lead_account_contacts.id',
            1 => 'lead_account_contacts.first_name',
            2 => 'lead_account_contacts.last_name',
            3 => 'lead_account_contacts.email',
            4 => 'lead_account_contacts.phone_number',
        );

        $columns = array(
            0 => 'lead_account_contacts.id',
            1 => 'lead_account_contacts.first_name',
            2 => 'lead_account_contacts.email',
            3 => 'lead_account_contacts.last_name',
            5 => 'lead_account_contacts.created_at',
            6 => 'lead_account_contacts.phone_number',
            7 => 'crm_setting_contact_tag.name as tag_name',
            8 => 'lead_account_contacts.alernate_phone_number',
            9 => 'users.first_name as account_first_name',
            10 => 'users.last_name as account_last_name',
            11 => 'users.email as account_email',


        );

        $recordsTotal = DB::table('lead_account_contacts')->count();
        $recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.
        $query = DB::table('lead_account_contacts');
        $query->leftJoin('crm_setting_contact_tag', 'crm_setting_contact_tag.id', '=', 'lead_account_contacts.contact_tag_id');
        $query->leftJoin('users', 'users.id', '=', 'lead_account_contacts.user_id');
        $query->select($columns);
        $query->limit($request->length);
        $query->offset($request->start);
        $query->orderBy($columns[$request['order'][0]['column']], $request['order'][0]['dir']);
        $isFilterApply = 0;

        $search_value = '';
        if (isset($request['search']['value'])) {
            $isFilterApply = 1;
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

        $data = $query->get();
        // echo "<pre>";
        // print_r(DB::getQueryLog());
        // die;

        $data = json_decode(json_encode($data), true);

        if ($isFilterApply == 1) {
            $recordsFiltered = count($data);
        }


        $routeAccountContactDetail = route('crm.lead.account.contact.table.detail');
        foreach ($data as $key => $value) {

            $data[$key]['id'] = '<div class="avatar-xs"><span class="avatar-title rounded-circle">' . highlightString($data[$key]['id'],$search_value) . '</span></div>';

            $data[$key]['name'] = '<h5 class="font-size-14 mb-1"><a href="' . $routeAccountContactDetail . '?id=' . $value['id'] . '"  class="text-dark" target="_blank">' . highlightString($value['first_name'] . " " . $value['last_name'],$search_value) . '</a></h5>
             <p class="text-muted mb-0">' . ($value['tag_name']) . '</p>';

            $data[$key]['phone_number'] = highlightString($value['phone_number'],$search_value) . " | " . highlightString($value['alernate_phone_number'],$search_value);


            $data[$key]['account'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">' . $value['account_first_name'] . " " . $value['account_last_name'] . '</a></h5>
             <p class="text-muted mb-0">' . ($value['account_email']) . '</p>';

            // if ($data[$key]['created_at'] == $data[$key]['last_active_date_time']) {

            // 	$data[$key]['last_active_date_time'] = "-";
            // 	$data[$key]['last_login_date_time'] = "-";

            // } else {

            // 	$data[$key]['last_active_date_time'] = convertDateTime($value['last_active_date_time']);
            // 	$data[$key]['last_login_date_time'] = convertDateTime($value['last_login_date_time']);

            // }



        }

        $jsonData = array(
            "draw" => intval($request['draw']),
            // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($recordsTotal),
            // total number of records
            "recordsFiltered" => intval($recordsFiltered),
            // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $data, // total data array

        );
        return $jsonData;
    }

    public function save(Request $request)
    {

        $rules = array();
        $rules['account_contact_id'] = 'required';
        $rules['account_contact_first_name'] = 'required';
        $rules['account_contact_last_name'] = 'required';
        $rules['account_contact_email'] = 'required';
        $rules['account_contact_phone_number'] = 'required';
        $rules['account_contact_alernate_phone_number'] = 'required';
        $rules['account_contact_tag_id'] = 'required';

        $customMessage = array();
        $customMessage['lead_contact_lead_id.required'] = "Invalid parameters";

        $validator = Validator::make($request->all(), $rules, $customMessage);

        if ($validator->fails()) {

            $response = errorRes("The request could not be understood by the server due to malformed syntax");
            $response['data'] = $validator->errors();
        } else {

            if ($request->account_contact_id == 0) {

                $LeadContact = LeadAccountContact::where('user_id', $request->account_contact_user_id)->where('phone_number', $request->account_contact_phone_number)->first();
            } else {

                $LeadContact = LeadAccountContact::where('id', '!=', $request->account_contact_id)->where('user_id', $request->account_contact_user_id)->where('phone_number', $request->account_contact_phone_number)->first();
            }

            if ($LeadContact) {

                $response = errorRes("Contact number already link with account, Please use another phone number");
                return response()->json($response)->header('Content-Type', 'application/json');
            } else {

                $alernate_phone_number = isset($request->account_contact_alernate_phone_number) ? $request->account_contact_alernate_phone_number : '';

                if ($request->account_contact_id == 0) {

                    $LeadContact = new LeadAccountContact();
                } else {
                    $LeadContact = LeadAccountContact::find($request->account_contact_id);
                }
                $LeadContact->first_name = $request->account_contact_first_name;
                $LeadContact->last_name = $request->account_contact_last_name;
                $LeadContact->email = $request->account_contact_email;
                $LeadContact->phone_number = $request->account_contact_phone_number;
                $LeadContact->alernate_phone_number = $alernate_phone_number;
                $LeadContact->user_id = $request->account_contact_user_id;
                $LeadContact->contact_tag_id = $request->account_contact_tag_id;
                $LeadContact->save();
                $response = successRes("Successfully saved lead");
                $response['id'] = $LeadContact->user_id;
            }
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function detail(Request $request)
    {

        $LeadContact = LeadAccountContact::find($request->id);
        if ($LeadContact) {
            $LeadContact = json_encode($LeadContact);
            $LeadContact = json_decode($LeadContact, true);

            $CRMSettingCallType = CRMSettingContactTag::select('id', 'name as text')->find($LeadContact['contact_tag_id']);

            if ($CRMSettingCallType) {
                $CRMSettingCallType = json_encode($CRMSettingCallType);
                $CRMSettingCallType = json_decode($CRMSettingCallType, true);
                $LeadContact['type'] = $CRMSettingCallType;
            }

            $response = successRes("");
            $response['data'] = $LeadContact;
        } else {
            $response = errorRes("Something went wrong");
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }


    function getList(Request $request)
    {

        $pageNo = isset($request->page_no) ? $request->page_no : 1;
        $Users = LeadAccountContact::query();
        if (isset($request->search)) {
            if ($request->search != "") {
                $search = $request->search;
                $Users->where(function ($query) use ($search) {
                    $query->where('lead_account_contacts.id', 'like', '%' . $search . '%');
                    $query->orWhere('lead_account_contacts.first_name', 'like', '%' . $search . '%');
                    $query->orWhere('lead_account_contacts.last_name', 'like', '%' . $search . '%');
                });
            }
        }
        $Users->orderBy('lead_account_contacts.id', 'desc');

        $Users = $Users->get();
        $Users = json_encode($Users);
        $Users = json_decode($Users, true);
        $lastPageLeadId = 0;
        $FirstPageLeadId = 0;
        $LeadR = array_reverse($Users);
        if (count($LeadR) > 0) {
            $FirstPageLeadId = $Users[0]['id'];
            $lastPageLeadId = $Users[0]['id'];
        }

        $data = array();
        $data['users'] = $Users;
        $response = successRes("Get List");
        $response['view'] = view('crm/contact/account_contact_list', compact('data'))->render();
        $response['lastPageLeadId'] = $lastPageLeadId;
        $response['FirstPageLeadId'] = $FirstPageLeadId;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function getDeatailView(Request $request)
    {
        $response = successRes("");
        $LeadContact = LeadAccountContact::find($request->id);
        $data = array();
        if ($LeadContact) {
            $LeadContact = json_encode($LeadContact);
            $LeadContact = json_decode($LeadContact, true);

            $data['leadcontact'] = $LeadContact;

            $CRMSettingCallType = CRMSettingContactTag::select('id', 'name as text')->find($data['leadcontact']['contact_tag_id']);

            if ($CRMSettingCallType) {
                $CRMSettingCallType = json_encode($CRMSettingCallType);
                $CRMSettingCallType = json_decode($CRMSettingCallType, true);
                $data['leadcontact']['type'] = $CRMSettingCallType;
            }
        }

        $response['data'] = $data;

        $response['view'] = view('crm/contact/account_contact_detail_view', compact('data'))->render();

        return response()->json($response)->header('Content-Type', 'application/json');
    }
}