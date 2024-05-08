<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ChannelPartner;
use App\Models\CityList;
use App\Models\Company;
use App\Models\CreditTranscationLog;
use App\Models\Parameter;
use App\Models\ProductGroup;
use App\Models\ProductInventory;
use App\Models\SalePerson;
use App\Models\StateList;
use App\Models\User;
use App\Models\UserDiscount;
use Config;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Mail;

//use Session;

class ChannelPartnersController extends Controller
{

    public function __construct()
    {

        $this->middleware(function ($request, $next) {

            $tabCanAccessBy = array(0, 1, 2, 6, 9);

            if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
                return redirect()->route('dashboard');
            }

            return $next($request);
        });
    }

    public function ajax(Request $request)
    {

        $isSalePerson = isSalePerson();
        $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
        $isRequest = 0;
        if ($isAdminOrCompanyAdmin == 1 && isset($request->is_request) && $request->is_request == 1) {

            $isRequest = 1;
        }

        if ($isSalePerson == 1) {

            $childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
        }

        $isTaleSalesUser = isTaleSalesUser();

        if ($isTaleSalesUser == 1) {
            $TeleSalesCity = TeleSalesCity(Auth::user()->id);
        }

        if (isset($request['search']['value'])) {

            $query = DB::table('users');
            $query->select('users.id');
            $query->where('users.type', 2);

            $query->where(function ($query2) use ($request) {
                $query2->where('users.first_name', 'like', "%" . $request['search']['value'] . "%");
                $query2->orWhere('users.last_name', 'like', "%" . $request['search']['value'] . "%");
            });

            $searchSalesPerson = $query->get();
            $searchSalesPersonIds = array();
            foreach ($searchSalesPerson as $keyS => $valueS) {

                $searchSalesPersonIds[] = $valueS->id;
            }
        }

        $searchColumns = array(

            0 => 'users.id',
            1 => 'users.first_name',
            2 => 'users.last_name',
            3 => 'users.email',
            4 => 'users.phone_number',
            5 => 'channel_partner.gst_number',
            6 => 'channel_partner.firm_name',
            7 => 'reporting_channel_partner.firm_name',

        );

        $selectColumns = array(
            0 => 'users.id',
            1 => 'users.first_name',
            2 => 'users.email',
            3 => 'users.last_active_date_time',
            4 => 'users.last_login_date_time',
            5 => 'users.status',
            5 => 'users.last_name',
            6 => 'users.type',
            7 => 'users.created_at',
            8 => 'users.status',
            9 => 'users.phone_number',
            10 => 'channel_partner.gst_number',
            11 => 'channel_partner.reporting_manager_id',
            12 => 'channel_partner.reporting_company_id',
            13 => 'channel_partner.sale_persons',
            14 => 'channel_partner.firm_name',
            15 => 'channel_partner.data_verified',
            16 => 'channel_partner.data_not_verified',
            17 => 'channel_partner.missing_data',
            18 => 'channel_partner.tele_verified',
            19 => 'channel_partner.tele_not_verified',

        );

        $sortColumns = array(
            0 => 'users.id',
            1 => 'users.first_name',
            2 => 'users.email',
            3 => 'channel_partner.reporting_company_id',
            4 => 'channel_partner.sale_persons',
            5 => 'users.last_active_date_time',

        );

        $query = DB::table('users');
        $query->leftJoin('channel_partner', 'channel_partner.id', '=', 'users.reference_id');
        $query->leftJoin('channel_partner as reporting_channel_partner', 'channel_partner.reporting_manager_id', '=', 'reporting_channel_partner.user_id');
        //$query->where('users.type', $request->type);
        $query->whereIn('users.type', array(101, 102, 103, 104, 105));
        if ($isRequest == 1) {
            $query->where('users.status', 2);
        }
        if ($isSalePerson == 1) {

            $query->where(function ($query2) use ($childSalePersonsIds) {
                foreach ($childSalePersonsIds as $key => $value) {
                    if ($key == 0) {
                        $query2->whereRaw('FIND_IN_SET("' . $value . '",channel_partner.sale_persons)>0');
                    } else {
                        $query2->orWhereRaw('FIND_IN_SET("' . $value . '",channel_partner.sale_persons)>0');
                    }
                }
            });
        } else if ($isTaleSalesUser == 1) {
            $query->whereIn('users.city_id', $TeleSalesCity);
        }

        $recordsTotal = $query->count();
        $recordsFiltered = $recordsTotal;

        $query = DB::table('users');
        $query->select($selectColumns);
        $query->selectRaw('case when users.type = 101 then "ASM" 
		when users.type = 102 then "ADM"
		when users.type = 103 then "APM"
		when users.type = 104 then "AD"
		when users.type = 105 then "Retailer"
		else "Undifine"
		end as type_label');
        $query->leftJoin('channel_partner', 'channel_partner.id', '=', 'users.reference_id');
        $query->leftJoin('channel_partner as reporting_channel_partner', 'channel_partner.reporting_manager_id', '=', 'reporting_channel_partner.user_id');
        // $query->where('users.type', $request->type);
        $query->whereIn('users.type', array(101, 102, 103, 104, 105));
        if ($isSalePerson == 1) {

            $query->where(function ($query2) use ($childSalePersonsIds) {
                foreach ($childSalePersonsIds as $key => $value) {
                    if ($key == 0) {
                        $query2->whereRaw('FIND_IN_SET("' . $value . '",channel_partner.sale_persons)>0');
                    } else {
                        $query2->orWhereRaw('FIND_IN_SET("' . $value . '",channel_partner.sale_persons)>0');
                    }
                }
            });
        } else if ($isTaleSalesUser == 1) {
            $query->whereIn('users.city_id', $TeleSalesCity);
        }

        if ($isRequest == 1) {
            $query->where('users.status', 2);
        }
        // $query->limit($request->length);
        // $query->offset($request->start);
        $query->orderBy('users.id', 'desc');

        // $query->orderBy($sortColumns[$request['order'][0]['column']], $request['order'][0]['dir']);
        $isFilterApply = 0;

        if (isset($request['search']['value'])) {
            $isFilterApply = 1;
            $search_value = $request['search']['value'];
            $query->where(function ($query) use ($search_value, $searchColumns, $searchSalesPersonIds) {

                $hasSalesPerson = 0;

                if (count($searchSalesPersonIds) > 0) {

                    $hasSalesPerson = 1;

                    $query->where(function ($query2) use ($searchSalesPersonIds) {
                        foreach ($searchSalesPersonIds as $keyS => $valueS) {
                            if ($keyS == 0) {
                                $query2->whereRaw('FIND_IN_SET("' . $valueS . '",channel_partner.sale_persons)>0');
                            } else {
                                $query2->orWhereRaw('FIND_IN_SET("' . $valueS . '",channel_partner.sale_persons)>0');
                            }
                        }
                    });
                }

                for ($i = 0; $i < count($searchColumns); $i++) {

                    if ($i == 0) {
                        if ($hasSalesPerson == 0) {
                            $query->where($searchColumns[$i], 'like', "%" . $search_value . "%");
                        } else {
                            $query->orWhere($searchColumns[$i], 'like', "%" . $search_value . "%");
                        }
                    } else {

                        $query->orWhere($searchColumns[$i], 'like', "%" . $search_value . "%");
                    }
                }
            });
        }

        $data = $query->paginate(10);

        $data = json_decode(json_encode($data), true);
        $response = successRes("Channel Parnter list");
        $response['data'] = $data;
        return response()->json($response)->header('Content-Type', 'application/json');

    }

    public function reportingManager(Request $request)
    {

        $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();

        $finalArray = array();
        $Company = array();
        $Company = Company::select('id', 'name as text');
        $Company->where('name', 'like', "%" . $request->q . "%");
        $Company->where('status', 1);
        $Company->limit(5);
        $Company = $Company->get();

        foreach ($Company as $key => $value) {
            $finalArray[$key]['id'] = "c-" . $value['id'];
            $finalArray[$key]['text'] = $value['text'] . " (COMPANY)";
        }

        if ($request->user_id != "" && $request->user_type != "") {

            if ($request->user_type == 101) {
                $ChannelPartnersIds = array(101);
            } else if ($request->user_type == 102) {
                $ChannelPartnersIds = array(102, 101);
            } else if ($request->user_type == 103) {
                $ChannelPartnersIds = array(103, 102, 101);
            } else if ($request->user_type == 104 || $request->user_type == 105) {
                $ChannelPartnersIds = array(104, 103, 102, 101);
            }

            $query = DB::table('channel_partner');
            $query->select('channel_partner.type', 'channel_partner.reporting_company_id', 'users.id as id', DB::raw('channel_partner.firm_name AS text'));
            $query->leftJoin('users', 'users.id', '=', 'channel_partner.user_id');
            $query->whereIn('channel_partner.type', $ChannelPartnersIds);
            $query->whereIn('users.type', $ChannelPartnersIds);
            $query->where('users.reference_id', '!=', 0);
            $query->where('users.id', '!=', $request->user_id);

            $q = $request->q;
            $query->where(function ($query) use ($q) {
                $query->where('users.first_name', 'like', '%' . $q . '%');
                $query->orWhere('users.last_name', 'like', '%' . $q . '%');
                $query->orWhere('channel_partner.firm_name', 'like', '%' . $q . '%');
            });

            $query->limit(15);
            $data = $query->get();

            foreach ($data as $key => $value) {

                $cfinalArray = count($finalArray);
                $finalArray[$key]['id'] = "u-" . $value->id;
                $finalArray[$key]['text'] = $value->text . " (" . getUserTypeName($value->type) . ")";
            }
        }
        
        $response = successRes("Reporting Manager");
        $response['data'] = $finalArray;
        // $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function salePerson(Request $request)
    {

        $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
        $isSalePerson = isSalePerson();
        if ($isAdminOrCompanyAdmin == 1) {

            $data = array();
            if ($request->channel_partner_reporting_manager != "") {

                $channel_partner_reporting = explode("-", $request->channel_partner_reporting_manager);

                if ($channel_partner_reporting[0] == "c") {

                    $user_company_id = $channel_partner_reporting[1];
                } else {

                    $ChannelPartner = ChannelPartner::select('reporting_company_id')->where('user_id', $channel_partner_reporting[1])->first();
                    if ($ChannelPartner) {
                        $user_company_id = $ChannelPartner->reporting_company_id;
                    }
                }

                $query = DB::table('sale_person');
                $query->select('users.id as id', DB::raw('CONCAT(first_name," ", last_name) AS text'));
                $query->leftJoin('users', 'sale_person.user_id', '=', 'users.id');
                //$query->whereIn('sale_person.type', $SalesHierarchyId);
                $query->where('users.type', 2);
                $query->where('users.company_id', $user_company_id);
                $query->where('users.reference_id', '!=', 0);
                $query->where('users.id', '!=', $request->user_id);

                $q = $request->q;

                $query->where(function ($query) use ($q) {
                    $query->where('users.first_name', 'like', '%' . $q . '%');
                    $query->orWhere('users.last_name', 'like', '%' . $q . '%');
                });

                $query->limit(5);
                $data = $query->get();
            }

            $response = successRes("Sales Person");
            $response['data'] = $data;
            // $response['pagination']['more'] = false;
            return response()->json($response)->header('Content-Type', 'application/json');
        } else if ($isSalePerson == 1) {

            $childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);

            $data = array();
            if ($request->channel_partner_reporting_manager != "") {

                $channel_partner_reporting = explode("-", $request->channel_partner_reporting_manager);

                if ($channel_partner_reporting[0] == "c") {

                    $user_company_id = $channel_partner_reporting[1];
                } else {

                    $ChannelPartner = ChannelPartner::select('reporting_company_id')->where('user_id', $channel_partner_reporting[1])->first();
                    if ($ChannelPartner) {
                        $user_company_id = $ChannelPartner->reporting_company_id;
                    }
                }

                $query = DB::table('sale_person');
                $query->select('users.id as id', DB::raw('CONCAT(first_name," ", last_name) AS text'));
                $query->leftJoin('users', 'sale_person.user_id', '=', 'users.id');
                //$query->whereIn('sale_person.type', $SalesHierarchyId);
                $query->where('users.type', 2);
                $query->where('users.company_id', $user_company_id);
                $query->where('users.reference_id', '!=', 0);
                //$query->where('users.id', '!=', $request->user_id);
                $query->whereIn('users.id', $childSalePersonsIds);

                $q = $request->q;

                $query->where(function ($query) use ($q) {
                    $query->where('users.first_name', 'like', '%' . $q . '%');
                    $query->orWhere('users.last_name', 'like', '%' . $q . '%');
                });

                $query->limit(5);
                $data = $query->get();
            }

            $response = successRes("Sales Person");
            $response['data'] = $data;
            // $response['pagination']['more'] = false;
            return response()->json($response)->header('Content-Type', 'application/json');
        }
    }
    public function save(Request $request)
    {

        $isSalePerson = isSalePerson();

        $isTaleSalesUser = isTaleSalesUser();
        if ($isTaleSalesUser == 1) {
            $TeleSalesCity = TeleSalesCity(Auth::user()->id);
        }

        if ($isTaleSalesUser == 1 && !in_array($request->user_city_id, $TeleSalesCity)) {
            $response = errorRes("Invalid access");
        }

        if (!in_array($request->user_type, [104, 105])) {
            $response = errorRes("Invalid access");
        } else {

            $user_address_line2 = isset($request->user_address_line2) ? $request->user_address_line2 : '';
            $channel_partner_d_address_line2 = isset($request->channel_partner_d_address_line2) ? $request->channel_partner_d_address_line2 : '';
            $channel_partner_credit_days = isset($request->channel_partner_credit_days) ? $request->channel_partner_credit_days : 0;
            $channel_partner_credit_limit = isset($request->channel_partner_credit_limit) ? $request->channel_partner_credit_limit : 0;

            $data_verified = isset($request->channel_partner_data_verified) ? $request->channel_partner_data_verified : 0;
            $data_not_verified = isset($request->channel_partner_data_not_verified) ? $request->channel_partner_data_not_verified : 0;
            $missing_data = isset($request->channel_partner_missing_data) ? $request->channel_partner_missing_data : 0;
            $tele_verified = isset($request->channel_partner_tele_verified) ? $request->channel_partner_tele_verified : 0;
            $tele_not_verified = isset($request->channel_partner_tele_not_verified) ? $request->channel_partner_tele_not_verified : 0;

            $dataVerifiedStatus = 0;
            if ($data_verified == 1) {
                $dataVerifiedStatus = 1;
            } else if ($data_not_verified == 1) {
                $dataVerifiedStatus = 2;
            } else if ($missing_data == 1) {
                $dataVerifiedStatus = 3;
            }

            $rules = array();
		    $rules['user_id'] = 'required';
            $rules['user_id'] = 'required';
            $rules['user_type'] = 'required';
            $rules['user_first_name'] = 'required';
            $rules['user_last_name'] = 'required';
            $rules['user_email'] = 'required|email:rfc,dns';
            $rules['user_phone_number'] = 'required|digits:10|regex:/^[1-9][0-9]*$/';
            $rules['user_address_line1'] = 'required';
            $rules['user_pincode'] = 'required';
            $rules['user_country_id'] = 'required';
            $rules['user_state_id'] = 'required';
            $rules['user_city_id'] = 'required';
            $rules['channel_partner_firm_name'] = 'required';
            $rules['channel_partner_reporting_manager'] = 'required';
            $rules['channel_partner_payment_mode'] = 'required';
            $rules['channel_partner_shipping_limit'] = 'required';
            $rules['channel_partner_shipping_cost'] = 'required';
            $rules['channel_partner_d_country_id'] = 'required';
            $rules['channel_partner_d_state_id'] = 'required';
            $rules['channel_partner_d_city_id'] = 'required';
            $rules['channel_partner_d_pincode'] = 'required';
            $rules['channel_partner_d_address_line1'] = 'required';

            if ($request->user_type != 104) {
                $rules['channel_partner_gst_number'] = 'required';
            }

            if ($isSalePerson == 0) {
                //$rules['channel_partner_sale_persons'] = 'required';
            }

            $customMessage = array(
                'user_id.required' => "Invalid parameters",
                'user_type.required' => "Invalid type",
                'user_first_name.required' => "Please enter first name",
                'user_last_name.required' => "Please enter last name",
                'user_email.required' => "Please enter email",
                'user_phone_number.required' => "Please enter phone number",
                'user_address_line1.required' => "Please enter addreessline1",
                'user_pincode.required' => "Please enter pincode",
                'user_country_id.required' => "Please select country",
                'user_state_id.is_required' => "Please select state",
                'user_city_id.required' => "Please select city",
                'channel_partner_firm_name.required' => "Please select firm name",
                'channel_partner_reporting_manager.required' => "Please select reporting manager",
                'channel_partner_payment_mode.required' => "Please select payment type",
                'channel_partner_shipping_limit.required' => "Please enter shipping limit",
                'channel_partner_shipping_cost.required' => "Please enter shipping cost",
                'channel_partner_d_country_id.required' => "Please select delivery country",
                'channel_partner_d_state_id.required' => "Please select delivery state",
                'channel_partner_d_city_id.required' => "Please select delivery city",
                'channel_partner_d_pincode.required' => "Please enter delivery pincode",
                'channel_partner_d_address_line1.required' => "Please enter delivery addreessline1",
                'channel_partner_gst_number.required' => "Please enter delivery GST number",
                'channel_partner_sale_persons.required' => "Please select sale persons",
            );

            $validator = Validator::make($request->all(), $rules, $customMessage);

            if ($validator->fails()) {

                $response = array();
                $response['status'] = 0;
                $response['msg'] = $validator->errors()->first();
                $response['statuscode'] = 400;
                $response['data'] = $validator->errors();
            } else {

                //if ($isSalePerson == 0) {
                if (isset($request->channel_partner_sale_persons)) {
                    $channel_partner_sale_persons = implode(",", $request->channel_partner_sale_persons);
                } else {
                    $channel_partner_sale_persons = "";
                }

                // } else {

                // 	$channel_partner_sale_persons = Auth::user()->id;

                // }

                $phone_number = $request->user_phone_number;
                $channel_partner_gst_number = "";
                if (isset($request->channel_partner_gst_number) && $request->channel_partner_gst_number != "") {
                    $channel_partner_gst_number = $request->channel_partner_gst_number;
                }

                $alreadyEmail = User::query();
                $alreadyEmail->where('email', $request->user_email);

                if ($request->user_id != 0) {
                    $alreadyEmail->where('id', '!=', $request->user_id);
                }
                $alreadyEmail = $alreadyEmail->first();

                $alreadyPhoneNumber = User::query();
                $alreadyPhoneNumber->where('phone_number', $request->user_phone_number);

                if ($request->user_id != 0) {
                    $alreadyPhoneNumber->where('id', '!=', $request->user_id);
                }
                $alreadyPhoneNumber = $alreadyPhoneNumber->first();

                $AllUserTypes = getAllUserTypes();

                if ($alreadyEmail) {

                    $response = errorRes("Email already exists(" . $AllUserTypes[$alreadyEmail->type]['name'] . "), Try with another email");
                } else if ($alreadyPhoneNumber) {
                    $response = errorRes("Phone number already exists(" . $AllUserTypes[$alreadyPhoneNumber->type]['name'] . "), Try with another phone number");
                } else {

                    $channel_partner_reporting = explode("-", $request->channel_partner_reporting_manager);

                    if ($channel_partner_reporting[0] == "c") {

                        $user_company_id = $channel_partner_reporting[1];
                        $reporting_manager_id = 0;
                    } else {

                        $ChannelPartner = ChannelPartner::select('user_id', 'reporting_company_id')->where('user_id', $channel_partner_reporting[1])->first();
                        $user_company_id = $ChannelPartner->reporting_company_id;
                        $reporting_manager_id = $ChannelPartner->user_id;
                    }

                    $isCreditUpdate = 0;

                    $previousStatus = 1;
                    $paymentModeChannelPartner = 0;

                    if ($request->user_id == 0) {
                        $User = new User();
                        $User->created_by = Auth::user()->id;
                        $User->password = Hash::make("111111");
                        $User->last_active_date_time = date('Y-m-d H:i:s');
                        $User->last_login_date_time = date('Y-m-d H:i:s');
                        $User->avatar = "default.png";

                        $ChannelPartner = new ChannelPartner();
                        $ChannelPartner->credit_limit = $channel_partner_credit_limit;
                        $ChannelPartner->pending_credit = $channel_partner_credit_limit;
                        $isCreditUpdate = 1;
                    } else {
                        $User = User::find($request->user_id);
                        $previousStatus = $User->status;

                        $ChannelPartner = ChannelPartner::find($User->reference_id);
                        if (!$ChannelPartner) {

                            $ChannelPartner = new ChannelPartner();
                            $ChannelPartner->credit_limit = $channel_partner_credit_limit;
                            $ChannelPartner->pending_credit = $channel_partner_credit_limit;
                            $isCreditUpdate = 1;
                        } else {

                            $paymentModeChannelPartner = $ChannelPartner->payment_mode;

                            if ($ChannelPartner->payment_mode != 2 && $request->channel_partner_payment_mode == 2) {

                                $ChannelPartner->credit_limit = $channel_partner_credit_limit;
                                $ChannelPartner->pending_credit = $channel_partner_credit_limit;
                                $isCreditUpdate = 1;
                            }
                        }
                    }
                    $User->first_name = $request->user_first_name;
                    $User->last_name = $request->user_last_name;
                    $User->email = $request->user_email;
                    $User->dialing_code = "+91";
                    $User->phone_number = $request->user_phone_number;
                    $User->ctc = 0;
                    $User->address_line1 = $request->user_address_line1;
                    $User->address_line2 = $user_address_line2;
                    $User->pincode = $request->user_pincode;
                    $User->country_id = $request->user_country_id;
                    $User->state_id = $request->user_state_id;
                    $User->city_id = $request->user_city_id;
                    $User->company_id = $user_company_id;
                    $User->type = $request->user_type;
                    if ($isSalePerson == 0) {
                        $User->status = $request->user_status;
                    }

                    $User->reference_type = 0;
                    $User->reference_id = 0;
                    $User->save();

                    $ChannelPartner->data_verified_status = $dataVerifiedStatus;
                    $ChannelPartner->user_id = $User->id;
                    $ChannelPartner->type = $request->user_type;
                    $ChannelPartner->firm_name = $request->channel_partner_firm_name;
                    $ChannelPartner->reporting_manager_id = $reporting_manager_id;
                    $ChannelPartner->reporting_company_id = $user_company_id;

                    if ($isSalePerson == 1) {

                        if ($request->user_id == 0) {
                            $ChannelPartner->sale_persons = $channel_partner_sale_persons;
                        }
                    } else {

                        $ChannelPartner->sale_persons = $channel_partner_sale_persons;
                    }

                    $ChannelPartner->payment_mode = $request->channel_partner_payment_mode;
                    $ChannelPartner->credit_days = $channel_partner_credit_days;
                    $ChannelPartner->gst_number = $channel_partner_gst_number;
                    $ChannelPartner->shipping_limit = $request->channel_partner_shipping_limit;
                    $ChannelPartner->shipping_cost = $request->channel_partner_shipping_cost;
                    $ChannelPartner->d_address_line1 = $request->channel_partner_d_address_line1;
                    $ChannelPartner->d_address_line2 = $channel_partner_d_address_line2;
                    $ChannelPartner->d_pincode = $request->channel_partner_d_pincode;
                    $ChannelPartner->d_country_id = $request->channel_partner_d_country_id;
                    $ChannelPartner->d_state_id = $request->channel_partner_d_state_id;
                    $ChannelPartner->d_city_id = $request->channel_partner_d_city_id;

                    $ChannelPartner->data_verified = $data_verified;
                    $ChannelPartner->tele_verified = $tele_verified;
                    $ChannelPartner->tele_not_verified = $tele_not_verified;
                    $ChannelPartner->data_not_verified = $data_not_verified;
                    $ChannelPartner->missing_data = $missing_data;
                    $ChannelPartner->save();

                    $User->reference_type = getChannelPartners()[$User->type]['lable'];
                    $User->reference_id = $ChannelPartner->id;
                    if ($isSalePerson == 1 && $request->user_id == 0) {
                        $User->status = 2;
                    }
                    $User->save();
                    $currentStatus = $User->status;

                    //
                    if ($isCreditUpdate == 1) {

                        $CreditTranscationLog = new CreditTranscationLog();
                        $CreditTranscationLog->user_id = $User->id;
                        $CreditTranscationLog->type = 1;
                        $CreditTranscationLog->request_amount = $channel_partner_credit_limit;
                        $CreditTranscationLog->amount = $channel_partner_credit_limit;
                        $CreditTranscationLog->description = "intial";
                        $CreditTranscationLog->save();
                    }

                    //

                    if ($request->user_id != 0) {

                        $response = successRes("Successfully saved channel partner");

                        $debugLog = array();
                        $debugLog['name'] = "channel-partner-add";
                        $debugLog['description'] = "channelpartner #" . $User->id . "(" . $User->first_name . " " . $User->last_name . ") has been updated ";
                        saveDebugLog($debugLog);

                        if ($ChannelPartner->payment_mode != $paymentModeChannelPartner) {





                            //ADVANCE

                            if ($ChannelPartner->payment_mode == 1) {




                                $params = array();
                                // $params['bcc_email'] = "ankitsardhara4@gmail.com";
                                // $params['to_email'] = "ankitsardhara4@gmail.com";

                                $params['to_email'] = $User->email;
                                $Parameter = Parameter::where('code', 'channel-partner-deactivate-email')->first();
                                $channelPartnerDeactiveMail = $Parameter->name_value;
                                $bccEmails = array("nirav@whitelion.in", "jenish@whitelion.in", "vishal@whitelion.in", "marketing@whitelion.in");
                                if ($channelPartnerDeactiveMail != "") {
                                    $channelPartnerDeactiveMail = explode(",", $channelPartnerDeactiveMail);
                                    foreach ($channelPartnerDeactiveMail as $keyM => $valueM) {

                                        if ($valueM != "") {
                                            $bccEmails[] = $valueM;
                                        }
                                    }
                                }

                                $salePersons = $ChannelPartner->sale_persons;

                                $bccEmailUserIds = array();
                                if ($salePersons != "") {
                                    $salePersons = explode(",", $salePersons);
                                    foreach ($salePersons as $keyS => $valueS) {

                                        $salesParentsIds = getParentSalePersonsIds($valueS);
                                        $bccEmailUserIds = array_merge($bccEmailUserIds, $salesParentsIds);
                                    }
                                }

                                $bccEmailUserIds = array_unique($bccEmailUserIds);
                                $bccEmailUserIds = array_values($bccEmailUserIds);

                                $bccEmailUser = User::select('email')->where('id', $bccEmailUserIds)->get();
                                foreach ($bccEmailUser as $keyBE => $valueBE) {

                                    $bccEmails[] = $valueBE->email;
                                }

                                $accountAndDispatchUsers = User::select('email')->where('parent_id', $ChannelPartner->reporting_manager_id)->where('type', array(3, 4))->get();

                                foreach ($accountAndDispatchUsers as $keyBE => $valueBE) {
                                    $bccEmails[] = $valueBE->email;
                                }
                                //ADVANCE


                                $configrationForNotify = configrationForNotify();

                                $params['bcc_email'] = $bccEmails;
                                $params['firm_name'] = $ChannelPartner->firm_name;
                                $params['first_name'] = $User->first_name;
                                $params['last_name'] = $User->last_name;
                                $params['city_name'] = getCityName($ChannelPartner->d_city_id);
                                $params['channel_partner_type'] = getChannelPartners()[$User->type]['short_name'];


                                $params['from_name'] = $configrationForNotify['from_name'];
                                $params['from_email'] = $configrationForNotify['from_email'];
                                $params['to_name'] = $configrationForNotify['to_name'];
                                $params['subject'] = "Payment Terms Change";



                                if (Config::get('app.env') == "local") {

                                    $params['to_email'] = $configrationForNotify['test_email'];
                                    $params['bcc_email'] = $configrationForNotify['test_email_bcc'];
                                }



                                Mail::send('emails.channel_partner_advance', ['params' => $params], function ($m) use ($params) {
                                    $m->from($params['from_email'], $params['from_name']);
                                    $m->bcc($params['bcc_email']);
                                    $m->to($params['to_email'], $params['to_name'])->subject($params['subject']);
                                });
                            } else if ($ChannelPartner->payment_mode == 2) {

                                $params = array();
                                // $params['bcc_email'] = "ankitsardhara4@gmail.com";
                                // $params['to_email'] = "ankitsardhara4@gmail.com";

                                $params['to_email'] = $User->email;
                                $Parameter = Parameter::where('code', 'channel-partner-deactivate-email')->first();
                                $channelPartnerDeactiveMail = $Parameter->name_value;
                                $bccEmails = array("nirav@whitelion.in", "jenish@whitelion.in", "vishal@whitelion.in", "marketing@whitelion.in");
                                if ($channelPartnerDeactiveMail != "") {
                                    $channelPartnerDeactiveMail = explode(",", $channelPartnerDeactiveMail);
                                    foreach ($channelPartnerDeactiveMail as $keyM => $valueM) {

                                        if ($valueM != "") {
                                            $bccEmails[] = $valueM;
                                        }
                                    }
                                }

                                $salePersons = $ChannelPartner->sale_persons;

                                $bccEmailUserIds = array();
                                if ($salePersons != "") {
                                    $salePersons = explode(",", $salePersons);
                                    foreach ($salePersons as $keyS => $valueS) {

                                        $salesParentsIds = getParentSalePersonsIds($valueS);
                                        $bccEmailUserIds = array_merge($bccEmailUserIds, $salesParentsIds);
                                    }
                                }

                                $bccEmailUserIds = array_unique($bccEmailUserIds);
                                $bccEmailUserIds = array_values($bccEmailUserIds);

                                $bccEmailUser = User::select('email')->where('id', $bccEmailUserIds)->get();
                                foreach ($bccEmailUser as $keyBE => $valueBE) {

                                    $bccEmails[] = $valueBE->email;
                                }

                                $accountAndDispatchUsers = User::select('email')->where('parent_id', $ChannelPartner->reporting_manager_id)->where('type', array(3, 4))->get();

                                foreach ($accountAndDispatchUsers as $keyBE => $valueBE) {
                                    $bccEmails[] = $valueBE->email;
                                }
                                //ADVANCE


                                $configrationForNotify = configrationForNotify();
                                $params['bcc_email'] = $bccEmails;
                                $params['firm_name'] = $ChannelPartner->firm_name;
                                $params['first_name'] = $User->first_name;
                                $params['last_name'] = $User->last_name;



                                $params['from_name'] = $configrationForNotify['from_name'];
                                $params['from_email'] = $configrationForNotify['from_email'];
                                $params['to_name'] = $configrationForNotify['to_name'];
                                $params['subject'] = "Payment Terms Change";
                                $params['credit_limit'] = $channel_partner_credit_limit;
                                $params['credit_day'] = $channel_partner_credit_days;

                                if (Config::get('app.env') == "local") {

                                    $params['to_email'] = $configrationForNotify['test_email'];
                                    $params['bcc_email'] = $configrationForNotify['test_email_bcc'];
                                }

                                Mail::send('emails.channel_partner_credit', ['params' => $params], function ($m) use ($params) {
                                    $m->from($params['from_email'], $params['from_name']);
                                    $m->bcc($params['bcc_email']);
                                    $m->to($params['to_email'], $params['to_name'])->subject($params['subject']);
                                });
                            }
                        }
                    } else {
                        $response = successRes("Successfully added channel partner");

                        $debugLog = array();
                        $debugLog['name'] = "channel-partner-edit";
                        $debugLog['description'] = "channelpartner #" . $User->id . "(" . $User->first_name . " " . $User->last_name . ") has been added ";
                        saveDebugLog($debugLog);
                    }

                    if ($previousStatus == 1 && $currentStatus == 0) {

                        $params = array();
                        // $params['bcc_email'] = "ankitsardhara4@gmail.com";
                        // $params['to_email'] = "ankitsardhara4@gmail.com";

                        $params['to_email'] = $User->email;
                        $Parameter = Parameter::where('code', 'channel-partner-deactivate-email')->first();
                        $channelPartnerDeactiveMail = $Parameter->name_value;
                        $bccEmails = array("nirav@whitelion.in", "jenish@whitelion.in", "vishal@whitelion.in", "marketing@whitelion.in");
                        if ($channelPartnerDeactiveMail != "") {
                            $channelPartnerDeactiveMail = explode(",", $channelPartnerDeactiveMail);
                            foreach ($channelPartnerDeactiveMail as $keyM => $valueM) {

                                if ($valueM != "") {
                                    $bccEmails[] = $valueM;
                                }
                            }
                        }

                        $salePersons = $ChannelPartner->sale_persons;

                        $bccEmailUserIds = array();
                        if ($salePersons != "") {
                            $salePersons = explode(",", $salePersons);
                            foreach ($salePersons as $keyS => $valueS) {

                                $salesParentsIds = getParentSalePersonsIds($valueS);
                                $bccEmailUserIds = array_merge($bccEmailUserIds, $salesParentsIds);
                            }
                        }

                        $bccEmailUserIds = array_unique($bccEmailUserIds);
                        $bccEmailUserIds = array_values($bccEmailUserIds);

                        $bccEmailUser = User::select('email')->where('id', $bccEmailUserIds)->get();
                        foreach ($bccEmailUser as $keyBE => $valueBE) {

                            $bccEmails[] = $valueBE->email;
                        }

                        $accountAndDispatchUsers = User::select('email')->where('parent_id', $ChannelPartner->reporting_manager_id)->where('type', array(3, 4))->get();

                        foreach ($accountAndDispatchUsers as $keyBE => $valueBE) {
                            $bccEmails[] = $valueBE->email;
                        }

                        $configrationForNotify = configrationForNotify();

                        $params['bcc_email'] = $bccEmails;
                        $params['firm_name'] = $ChannelPartner->firm_name;
                        $params['first_name'] = $User->first_name;
                        $params['last_name'] = $User->last_name;
                        $params['city_name'] = getCityName($ChannelPartner->d_city_id);
                        $params['channel_partner_type'] = getChannelPartners()[$User->type]['short_name'];
                        $params['from_name'] = $configrationForNotify['from_name'];
                        $params['from_email'] = $configrationForNotify['from_email'];
                        $params['to_name'] = $configrationForNotify['to_name'];
                        $params['subject'] = "Updates: Channel Partner's account deactivated.";

                        if (Config::get('app.env') == "local") {

                            $params['to_email'] = $configrationForNotify['test_email'];
                            $params['bcc_email'] = $configrationForNotify['test_email_bcc'];
                        }

                        Mail::send('emails.channel_partner_deactive', ['params' => $params], function ($m) use ($params) {
                            $m->from($params['from_email'], $params['from_name']);
                            $m->bcc($params['bcc_email']);
                            $m->to($params['to_email'], $params['to_name'])->subject($params['subject']);
                        });
                    }

                    if ($request->user_id == 0) {

                        $params = array();

                        $params['to_email'] = $User->email;
                        $Parameter = Parameter::where('code', 'channel-partner-active-email')->first();
                        $channelPartnerDeactiveMail = $Parameter->name_value;
                        $bccEmails = array();
                        if ($channelPartnerDeactiveMail != "") {
                            $channelPartnerDeactiveMail = explode(",", $channelPartnerDeactiveMail);
                            foreach ($channelPartnerDeactiveMail as $keyM => $valueM) {

                                if ($valueM != "") {
                                    $bccEmails[] = $valueM;
                                }
                            }
                        }

                        $salePersons = $ChannelPartner->sale_persons;

                        $bccEmailUserIds = array();
                        if ($salePersons != "") {
                            $salePersons = explode(",", $salePersons);
                            foreach ($salePersons as $keyS => $valueS) {

                                $salesParentsIds = getParentSalePersonsIds($valueS);
                                $bccEmailUserIds = array_merge($bccEmailUserIds, $salesParentsIds);
                            }
                        }

                        $bccEmailUserIds = array_unique($bccEmailUserIds);
                        $bccEmailUserIds = array_values($bccEmailUserIds);

                        $bccEmailUser = User::select('email')->where('id', $bccEmailUserIds)->get();
                        foreach ($bccEmailUser as $keyBE => $valueBE) {

                            $bccEmails[] = $valueBE->email;
                        }

                        $configrationForNotify = configrationForNotify();
                        $params['bcc_email'] = $bccEmails;
                        $params['firm_name'] = $ChannelPartner->firm_name;
                        $params['first_name'] = $User->first_name;
                        $params['last_name'] = $User->last_name;
                        $params['city_name'] = getCityName($ChannelPartner->d_city_id);
                        $params['channel_partner_type'] = getChannelPartners()[$User->type]['short_name'];

                        $params['from_name'] = $configrationForNotify['from_name'];
                        $params['from_email'] = $configrationForNotify['from_email'];
                        $params['to_name'] = $configrationForNotify['to_name'];
                        $params['subject'] = "Updates: Channel Partner's account Open.";

                        if (Config::get('app.env') == "local") {

                            $params['to_email'] = $configrationForNotify['test_email'];
                            $params['bcc_email'] = $configrationForNotify['test_email_bcc'];
                        }

                        Mail::send('emails.channel_partner_active', ['params' => $params], function ($m) use ($params) {
                            $m->from($params['from_email'], $params['from_name']);
                            $m->bcc($params['bcc_email']);
                            $m->to($params['to_email'], $params['to_name'])->subject($params['subject']);
                        });
                    }
                }
            }
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function detail(Request $request)
    {

        $isSalePerson = isSalePerson();

        $User = User::with(array('country' => function ($query) {
            $query->select('id', 'name');
        }, 'state' => function ($query) {
            $query->select('id', 'name');
        }, 'city' => function ($query) {
            $query->select('id', 'name');
        }, 'company' => function ($query) {
            $query->select('id', 'name');
        }));
        $User->where('id', $request->id);
        $User->whereIn('type', array(101, 102, 103, 104, 105));

        $User = $User->first();
        if ($User) {

            if ($isSalePerson == 1) {

                $childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
            }

            $ChannelPartner = ChannelPartner::select('type', 'firm_name', 'reporting_manager_id', 'reporting_company_id', 'sale_persons', 'payment_mode', 'credit_days', 'credit_limit', 'pending_credit', 'gst_number', 'shipping_limit', 'shipping_cost', 'd_address_line1', 'd_address_line2', 'd_pincode', 'd_country_id', 'd_state_id', 'd_city_id', 'data_verified', 'data_not_verified', 'missing_data', 'tele_verified', 'tele_not_verified')->with(array('d_country' => function ($query) {
                $query->select('id', 'name');
            }, 'd_state' => function ($query) {
                $query->select('id', 'name');
            }, 'd_city' => function ($query) {
                $query->select('id', 'name');
            }));

            if ($isSalePerson == 1) {

                $ChannelPartner->where(function ($query2) use ($childSalePersonsIds) {
                    foreach ($childSalePersonsIds as $key => $value) {
                        if ($key == 0) {
                            $query2->whereRaw('FIND_IN_SET("' . $value . '",channel_partner.sale_persons)>0');
                        } else {
                            $query2->orWhereRaw('FIND_IN_SET("' . $value . '",channel_partner.sale_persons)>0');
                        }
                    }
                });
            }

            $ChannelPartner = $ChannelPartner->where('id', $User->reference_id);
            $ChannelPartner = $ChannelPartner->first();

            if ($ChannelPartner) {
                $User['channel_partner'] = $ChannelPartner;

                if ($User['channel_partner']['payment_mode'] == 0) {
                    $User['channel_partner']['payment_mode_text'] = "PDC";
                } else if ($User['channel_partner']['payment_mode'] == 1) {
                    $User['channel_partner']['payment_mode_text'] = "Advance";
                } else if ($User['channel_partner']['payment_mode'] == 2) { 
                    $User['channel_partner']['payment_mode_text'] = "Credit";
                } else {
                    $User['channel_partner']['payment_mode_text'] = "";
                }

                if ($User['channel_partner']['reporting_manager_id'] != 0) {

                    $query = DB::table('channel_partner');
                    $query->leftJoin('users', 'channel_partner.user_id', '=', 'users.id');
                    $query->select('channel_partner.type', 'channel_partner.reporting_company_id', 'users.id as id', DB::raw('channel_partner.firm_name AS text'));
                    $query->where('channel_partner.user_id', $User['channel_partner']['reporting_manager_id']);
                    $ChannelPartner = $query->first();
                    $ChannelPartner = json_decode(json_encode($ChannelPartner), true);

                    if ($ChannelPartner) {

                        $ChannelPartner['id'] = "u-" . $ChannelPartner['id'];
                        $ChannelPartner['text'] = $ChannelPartner['text'] . " (" . getUserTypeName($ChannelPartner['type']) . ")";
                    }

                    $User['channel_partner']['reporting_manager'] = $ChannelPartner;
                } else {

                    $Company = array();
                    $Company = Company::select('id', 'name as text');
                    $Company->where('id', $User['channel_partner']['reporting_company_id']);
                    $Company = $Company->first();
                    $Company = json_decode(json_encode($Company), true);
                    if ($Company) {
                        $Company['id'] = "c-" . $Company['id'];
                        $Company['text'] = $Company['text'] . " (COMPANY)";
                    }
                    $User['channel_partner']['reporting_manager'] = $Company;
                }

                $query = DB::table('sale_person');
                $query->select('users.id as id', DB::raw('CONCAT(first_name," ", last_name) AS text'));
                $query->leftJoin('users', 'sale_person.user_id', '=', 'users.id');
                $query->whereIn('users.id', explode(",", $User['channel_partner']['sale_persons']));
                $User['channel_partner']['sale_persons'] = $query->get();

                $response = successRes("Successfully get user");
                $response['data'] = $User;
            } else {
                $response = errorRes("Invalid id");
            }
        } else {
            $response = errorRes("Invalid id");
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function export(Request $request)
    {

        $isSalePerson = isSalePerson();
        $channelPartners = getChannelPartners();

        if ($isSalePerson == 1) {

            $childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
        }

        $columns = array(
            'users.id',
            'users.first_name',
            'users.last_name',
            'users.email',
            'users.dialing_code',
            'users.phone_number',
            'users.status',
            'users.created_at',
            'channel_partner.firm_name',
            'channel_partner.reporting_manager_id',
            'channel_partner.reporting_company_id',
            'channel_partner.sale_persons',
            'channel_partner.payment_mode',
            'channel_partner.credit_days',
            'channel_partner.credit_limit',
            'channel_partner.pending_credit',
            'channel_partner.gst_number',
            'channel_partner.shipping_limit',
            'channel_partner.shipping_cost',
            'channel_partner.d_address_line1',
            'channel_partner.d_address_line2',
            'channel_partner.d_pincode',
            'channel_partner.d_country_id',
            'channel_partner.d_state_id',
            'channel_partner.d_city_id',

        );

        $query = DB::table('users');
        $query->select($columns);
        $query->leftJoin('channel_partner', 'channel_partner.id', '=', 'users.reference_id');
        $query->where('users.type', $request->type);
        if ($isSalePerson == 1) {

            $query->where(function ($query2) use ($childSalePersonsIds) {
                foreach ($childSalePersonsIds as $key => $value) {
                    if ($key == 0) {
                        $query2->whereRaw('FIND_IN_SET("' . $value . '",channel_partner.sale_persons)>0');
                    } else {
                        $query2->orWhereRaw('FIND_IN_SET("' . $value . '",channel_partner.sale_persons)>0');
                    }
                }
            });
        }
        $query->whereIn('users.type', array(101, 102, 103, 104));
        $query->orderBy('id', 'desc');
        $data = $query->get();

        $headers = array("#ID", "Firstname", "Lastname", "Email", "Phone", "Status", "Created", "Firm Name", "Bill To", "Assign Sales Persons", "Payment Mode", "GST Number", "Shipping Limit", "Shipping Cost", "Delivery Address - Country ", "Delivery Address - State ", "Delivery Address - City ", "Delivery Address - Pincode ", "Delivery Address - Address line 1 ", "Delivery Address - Address line 2 ");

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $channelPartners[$request->type]['short_name'] . '.csv"');
        $fp = fopen('php://output', 'wb');

        fputcsv($fp, $headers);

        foreach ($data as $key => $value) {

            $createdAt = convertDateTime($value->created_at);
            $status = $value->status;
            if ($status == 0) {
                $status = "Inactive";
            } else if ($status == 1) {
                $status = "Active";
            } else if ($status == 2) {
                $status = "Blocked";
            }

            $billTo = "";
            if ($value->reporting_manager_id != 0) {

                $ChannelPartner = ChannelPartner::select('firm_name', 'type');
                $ChannelPartner->where('user_id', $value->reporting_manager_id);
                $ChannelPartner = $ChannelPartner->first();
                if ($ChannelPartner) {

                    $billTo = $ChannelPartner->firm_name;
                }
            } else {

                $Company = array();
                $Company = Company::select('id', 'name');
                $Company->where('id', $value->reporting_company_id);
                $Company = $Company->first();
                if ($Company) {
                    $billTo = $Company->name;
                }
            }

            $StrsalePersons = "";

            $salePersons = DB::table('sale_person');
            $salePersons->select('users.id as id', DB::raw('CONCAT(first_name," ", last_name) AS text'));
            $salePersons->leftJoin('users', 'sale_person.user_id', '=', 'users.id');
            $salePersons->whereIn('users.id', explode(",", $value->sale_persons));
            $salePersons = $salePersons->get();

            $StrsalePersons = "";
            foreach ($salePersons as $keySP => $valueSP) {

                $StrsalePersons .= $valueSP->text;
            }

            $paymentMode = "";

            if ($value->payment_mode == 0) {
                $paymentMode = "PDC";
            } else if ($value->payment_mode == 1) {
                $paymentMode = "ADVANCE";
            } else if ($value->payment_mode == 2) {
                $paymentMode = "CREDIT";
            }

            $countryName = getCountryName($value->d_country_id);
            $stateName = getStateName($value->d_state_id);
            $cityName = getCityName($value->d_city_id);

            $lineVal = array(
                $value->id,
                $value->first_name,
                $value->last_name,
                $value->email,
                $value->dialing_code . " " . $value->phone_number,
                $status,
                $createdAt,
                $value->firm_name,
                $billTo,
                $StrsalePersons,
                $paymentMode,
                $value->gst_number,
                $value->shipping_limit,
                $value->shipping_cost,
                $countryName,
                $stateName,
                $cityName,
                $value->d_pincode,
                $value->d_address_line1,
                $value->d_address_line2,

            );

            fputcsv($fp, $lineVal, ",");
        }

        fclose($fp);
    }

    public function getChannelPartnerTypes() {

		$data = getChannelPartners();
		$new_data = array();
		foreach ($data as $key => $value) {
            if($value['id'] == 104 || $value['id'] == 105){
                $new_data1['id'] = $value['id'];
                $new_data1['text'] = $value['name'];
                array_push($new_data, $new_data1);
            }
		}
		$response = successRes("Get Channel Partner Type");
		$response['data'] = $new_data;
		return response()->json($response, $response['status_code'])->header('Content-Type', 'application/json');

	}
}
