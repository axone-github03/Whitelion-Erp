<?php

namespace App\Http\Controllers\API\Marketing;

use App\Http\Controllers\Controller;
use App\Models\ChannelPartner;
use App\Models\CityList;
use App\Models\CountryList;
use App\Models\MainMaster;
use App\Models\MarketingOrder;
use App\Models\MarketingOrderItem;
use App\Models\MarketingProductInventory;
use App\Models\StateList;
use App\Models\User;
use App\Models\CreditTranscationLog;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    //
    public function __construct()
    {

        $this->middleware(function ($request, $next) {

            $tabCanAccessBy = array(0, 1, 2, 6,101, 102, 102, 103, 104);

            if (!in_array(Auth::user()->type, $tabCanAccessBy)) {

                $response = errorRes("Invalid access", 401);
                return response()->json($response, $response['status_code'])->header('Content-Type', 'application/json');
            }

            return $next($request);
        });
    }



    function ajax(Request $request)
    {

        $isSalePerson = isSalePerson();
        $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();

        if ($isSalePerson == 1) {
            $childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
        }
        $isMarketingUser = isMarketingUser();

        $searchColumns = array(
            0 => 'marketing_orders.id',
            1 => 'users.first_name',
            2 => 'users.last_name',
            3 => 'channel_partner.firm_name',
            4 => "CONCAT(users.first_name,' ',users.last_name)",
        );

        $sortingColumns = array(
            0 => 'marketing_orders.id',
            1 => 'marketing_orders.user_id',
            2 => 'marketing_orders.channel_partner_user_id',
            3 => 'marketing_orders.sale_persons',
            4 => 'marketing_orders.payment_mode',
            5 => 'marketing_orders.status',

        );

        $selectColumns = array(
            0 => 'marketing_orders.id',
            1 => 'marketing_orders.user_id',
            2 => 'marketing_orders.channel_partner_user_id',
            3 => 'marketing_orders.sale_persons',
            4 => 'marketing_orders.payment_mode',
            5 => 'marketing_orders.status',
            6 => 'marketing_orders.created_at',
            7 => 'users.first_name as first_name',
            8 => 'users.last_name as last_name',
            9 => 'channel_partner.firm_name',
            10 => 'marketing_orders.payment_mode',
            11 => 'marketing_orders.total_mrp_minus_disocunt',
            12 => 'marketing_orders.total_payable',
            13 => 'marketing_orders.pending_total_payable',
            14 => 'marketing_orders.sub_status',
            15 => 'marketing_orders.challan',
            16 => 'channel_partner.type as channel_partner_type',
            17 => 'channel_partner_user.first_name as channel_partner_firstname',
            18 => 'channel_partner_user.last_name as channel_partner_lastname',
            19 => 'channel_partner_user.phone_number as channel_partner_user_phone_number',
            20 => 'marketing_orders.is_self',
            21 => 'users.phone_number'
        );

        $query = MarketingOrder::query();
        $query->leftJoin('users', 'users.id', '=', 'marketing_orders.user_id');
        $query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'marketing_orders.channel_partner_user_id');
        $query->leftJoin('users as channel_partner_user', 'channel_partner_user.id', '=', 'marketing_orders.channel_partner_user_id');

        if ($isAdminOrCompanyAdmin == 1) {
        } else if ($isSalePerson == 1) {

            $query->where(function ($query) use ($childSalePersonsIds) {

                foreach ($childSalePersonsIds as $key => $value) {
                    if ($key == 0) {
                        $query->whereRaw('FIND_IN_SET("' . $value . '",marketing_orders.sale_persons)>0');
                    } else {
                        $query->orWhereRaw('FIND_IN_SET("' . $value . '",marketing_orders.sale_persons)>0');
                    }
                }
            });

            // foreach ($childSalePersonsIds as $key => $value) {
            //  if ($key == 0) {
            //      $recordsTotal->whereRaw('FIND_IN_SET("' . $value . '",marketing_orders.sale_persons)>0');
            //  } else {
            //      $recordsTotal->orWhereRaw('FIND_IN_SET("' . $value . '",marketing_orders.sale_persons)>0');
            //  }
            // }

            $query->where('channel_partner.reporting_company_id', Auth::user()->company_id);
            $query->orwhere(function ($query) {
                $query->Where('marketing_orders.is_self', 1);
                $query->Where('marketing_orders.user_id', Auth::user()->id);
            });
        } else if ($isMarketingUser == 1) {
        } else if (isChannelPartner(Auth::user()->type) != 0) {
			$query->where('marketing_orders.user_id', Auth::user()->id);
		}
        $query->select($selectColumns);
        // $query->limit($request->length);
        // $query->offset($request->start);
        // $query->orderBy($sortingColumns[$request['order'][0]['column']], $request['order'][0]['dir']);
        $query->orderBy('marketing_orders.id', 'desc');

        // $isFilterApply = 0;

        if (isset($request->q)) {
			$isFilterApply = 1;
			$search_value = $request->q;
			$query->where(function ($query) use ($search_value, $searchColumns) {

				for ($i = 0; $i < count($searchColumns); $i++) {

					if ($i == 0) {
						$query->whereRaw($searchColumns[$i] . ' like ? ', [$search_value]);
						$query->orWhereRaw($searchColumns[$i] . ' like ? ', ["%" . $search_value . "%"]);
					} else {

						$query->orWhereRaw($searchColumns[$i] . ' like ? ', [$search_value]);
						$query->orWhereRaw($searchColumns[$i] . ' like ? ', ["%" . $search_value . "%"]);
					}
				}
			});
		}

        $data = $query->paginate(10);

        foreach ($data as $key => $value) {

            if ($value->sub_status == 1 && $value->status == 2) {
                $data[$key]->status_lable = 'PARTIALLY DISPATCHED';
            } else {
                $data[$key]->status_lable = getMarketingRequestStatus($value->status);
            }
        }

        $response = successRes("List of request");
        $response['data'] = $data;
        return response()->json($response, $response['status_code'])->header('Content-Type', 'application/json');

        // $data = json_decode(json_encode($data), true);
        // if ($isFilterApply == 1) {
        //     $recordsFiltered = count($data);
        // }

        // $channelPartner = getChannelPartners();



        // foreach ($data as $key => $value) {

        //     $data[$key]['detail'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">#MR' . $value['id'] . '</a></h5>
        //         <p class="text-muted mb-0" data-bs-toggle="tooltip"  data-bs-original-title="' . convertOrderDateTime($value['created_at'], "time") . '" >' . convertOrderDateTime($value['created_at'], "date") . '</p>';

        //     $paymentMode = "";

        //     $channelPartnerType = "";

        //     if ($value['is_self'] == 0) {
        //         $channelPartnerType = '<span class="badge rounded-pill badge-soft-dark font-size-11">' . $channelPartner[$value['channel_partner_type']]['short_name'] . '</span>';
        //     } else {
        //         $channelPartnerType = '<span class="badge rounded-pill badge-soft-dark font-size-11">SELF</p>';
        //     }


        //     $data[$key]['order_by'] = '<p class="text-muted mb-0" data-bs-toggle="tooltip" title="' . $value['first_name'] . '  ' . $value['last_name'] . '">' . displayStringLenth($value['first_name'] . '  ' . $value['last_name'], 10) . '</p>';
        //     $data[$key]['channel_partner'] = "";
        //     if ($value['is_self'] == 0) {
        //         $data[$key]['channel_partner'] = '<p class="text-muted mb-0 text-center"
        //         data-bs-toggle="tooltip" title="' . $value['channel_partner_user_first_name'] . ' ' . $value['channel_partner_user_last_name'] . '&#013;&#013; PHONE:' . $value['channel_partner_user_phone_number'] . '" >' . displayStringLenth($value['firm_name'], 15) . '</p><p class="text-muted mb-0 text-center">' . $channelPartnerType . " " . $paymentMode . '</p>';
        //     } else {

        //         $data[$key]['channel_partner'] = '<p class="text-muted mb-0 text-center"
        //         data-bs-toggle="tooltip" title="' . $value['first_name'] . ' ' . $value['last_name'] . '&#013;&#013; PHONE:' . $value['phone_number'] . '" >' . displayStringLenth($value['first_name'] . ' ' . $value['last_name'], 15) . '</p><p class="text-muted mb-0 text-center">' . $channelPartnerType . " " . $paymentMode . '</p>';
        //     }


        //     $sale_persons = explode(",", $value['sale_persons']);

        //     $Users = User::select('users.first_name', 'users.last_name', 'users.type', 'users.phone_number', 'sales_hierarchy.code as sales_hierarchy_code')->leftJoin('sale_person', 'sale_person.id', '=', 'users.reference_id')->leftJoin('sales_hierarchy', 'sales_hierarchy.id', '=', 'sale_person.type')->whereIn('users.id', $sale_persons)->get();

        //     $uiSalePerson = '<ul class="list-inline font-size-20 contact-links mb-0">';
        //     foreach ($Users as $kU => $vU) {
        //         $uiSalePerson .= '<li class="list-inline-item px-2">';
        //         $uiSalePerson .= '<a  data-bs-toggle="tooltip" title="' . $vU['first_name'] . ' ' . $vU['last_name'] . '&#013;' . $vU['sales_hierarchy_code'] . '&#013; PHONE:' . $vU['phone_number'] . '" href="javascript: void(0);" ><i class="bx bx-user"></i></a>';
        //         $uiSalePerson .= '</li>';
        //     }

        //     $uiSalePerson .= '</ul>';

        //     $data[$key]['sale_persons'] = $uiSalePerson;

        //     // $data[$key]['payment_detail'] = '<p class="text-muted mb-0">EXGST&nbsp&nbsp;&nbsp;&nbsp;: <i class="fas fa-rupee-sign"></i> <span class="price-lable-font">' . priceLable($value['total_mrp_minus_disocunt']) . '</span></p>

        //     //    <p class="text-muted mb-0 ">TOTAL&nbsp;&nbsp;&nbsp;&nbsp: <i class="fas fa-rupee-sign"></i> <span class="price-lable-font">' . priceLable($value['total_payable']) . '</span></p>
        //     //       ';

        //     $data[$key]['status'] = getMarketingRequestStatus($value['status']);
        //     $statusClass = "";

        //     if ($value['status'] == 0) {
        //         $statusClass = 'badge-soft-warning ';
        //     } else if ($value['status'] == 1) {
        //         $statusClass = 'badge-soft-success ';
        //     } else if ($value['status'] == 2) {
        //         $statusClass = 'badge-soft-success ';
        //     } else if ($value['status'] == 3) {
        //         $statusClass = 'badge-soft-danger ';
        //     } else {
        //         $statusClass = 'badge-soft-warning ';
        //     }
        //     $data[$key]['status'] = '<span class="badge ' . $statusClass . ' badge-pill badgefont-size-11">' . $data[$key]['status'] . '</span>';

        //     $uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';

        //     $uiAction .= '<li class="list-inline-item px-2">';
        //     $uiAction .= '<a onclick="ViewOrder(\'' . $value['id'] . '\')" href="javascript: void(0);" title="View"><i class="mdi mdi-eye"></i></a>';
        //     $uiAction .= '</li>';

        //     if ($value['status'] == 0 && Auth::user()->type == 0) {

        //         $uiAction .= '<li class="list-inline-item px-2">';
        //         $uiAction .= '<a onclick="CancelOrder(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Reject"><i class="mdi mdi-close-circle-outline"></i></a>';
        //         $uiAction .= '</li>';
        //     }

        //     $uiAction .= '</ul>';
        //     $data[$key]['action'] = $uiAction;
        // }

        // $jsonData = array(
        //     "draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
        //     "recordsTotal" => intval($recordsTotal), // total number of records
        //     "recordsFiltered" => intval($recordsFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
        //     "data" => $data, // total data array

        // );
        // return $jsonData;
    }

    public function detail(Request $request)
    {

        $Order = MarketingOrder::query();
        $Order->select('marketing_orders.id', 'marketing_orders.is_self', 'marketing_orders.channel_partner_user_id', 'marketing_orders.created_at', 'marketing_orders.gst_number', 'marketing_orders.payment_mode', 'marketing_orders.sale_persons', 'marketing_orders.total_mrp', 'marketing_orders.total_mrp_minus_disocunt', 'marketing_orders.total_mrp', 'marketing_orders.gst_tax', 'marketing_orders.delievery_charge', 'marketing_orders.total_payable', 'marketing_orders.pending_total_payable', 'marketing_orders.bill_address_line1', 'marketing_orders.bill_address_line2', 'marketing_orders.bill_pincode', 'marketing_orders.bill_state_id', 'marketing_orders.bill_city_id', 'marketing_orders.bill_country_id', 'marketing_orders.d_address_line1', 'marketing_orders.d_address_line2', 'marketing_orders.d_pincode', 'marketing_orders.d_state_id', 'marketing_orders.d_city_id', 'marketing_orders.d_country_id', 'marketing_orders.remark', 'channel_partner_detail.email as channel_partner_email', 'channel_partner_detail.phone_number as channel_partner_phone_number', 'channel_partner_detail.dialing_code as channel_partner_dialing_code', 'channel_partner_detail.first_name as channel_partner_first_name', 'channel_partner_detail.last_name as channel_partner_last_name', 'channel_partner.firm_name as channel_partner_firm_name', 'channel_partner.type as channel_partner_type', 'channel_partner.credit_limit as channel_partner_credit_limit', 'channel_partner.credit_days as channel_partner_credit_days', 'channel_partner.pending_credit as channel_partner_pending_credit', 'channel_partner.credit_days as channel_partner_credit_days');

        $Order->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'marketing_orders.channel_partner_user_id');
        $Order->leftJoin('users as channel_partner_detail', 'channel_partner_detail.id', '=', 'channel_partner.user_id');
        $Order->where('marketing_orders.id', $request->order_id);
        $Order = $Order->first();

        if ($Order) {

            $salePersons = explode(",", $Order['sale_persons']);
            $salePersons = User::select('first_name', 'last_name')->whereIn('id', $salePersons)->get();

            $salePersonsD = array();

            foreach ($salePersons as $keyS => $valueS) {

                $salePersonsD[$keyS] = $valueS['first_name'] . " " . $valueS['last_name'];
            }

            $Order['sale_persons'] = implode(", ", $salePersonsD);

            $BCityList = CityList::find($Order['bill_city_id']);
            $Order['bill_city_name'] = "";
            if ($BCityList) {
                $Order['bill_city_name'] = $BCityList->name;
            }

            $BStateList = StateList::find($Order['bill_state_id']);
            $Order['bill_state_name'] = "";
            if ($BStateList) {
                $Order['bill_state_name'] = $BStateList->name;
            }

            $BCountryList = CountryList::find($Order['bill_country_id']);
            $Order['bill_country_name'] = "";
            if ($BCountryList) {
                $Order['bill_country_name'] = $BCountryList->name;
            }

            $DCityList = CityList::find($Order['d_city_id']);
            $Order['d_city_name'] = "";
            if ($DCityList) {
                $Order['d_city_name'] = $DCityList->name;
            }

            $DStateList = StateList::find($Order['d_state_id']);
            $Order['d_state_name'] = "";
            if ($DStateList) {
                $Order['d_state_name'] = $DStateList->name;
            }

            $DCountryList = CountryList::find($Order['d_country_id']);
            $Order['d_country_name'] = "";
            if ($DCountryList) {
                $Order['d_country_name'] = $DCountryList->name;
            }




            $Order['channel_partner_type_name'] = getUserTypeName($Order['channel_partner_type']);
            $Order['display_date_time'] = convertOrderDateTime($Order->created_at, 'date');


            if ($Order['is_self'] == 0) {
                $Order['payment_mode_lable'] = getPaymentModeName($Order['payment_mode']);
            } else {
                $SelfUser = User::find($Order['channel_partner_user_id']);
                $Order['payment_mode_lable'] = "";
                $Order['channel_partner_firm_name'] = $SelfUser['first_name'] . " " . $SelfUser['last_name'];
                $Order['channel_partner_first_name'] = $SelfUser['first_name'];
                $Order['channel_partner_last_name'] = $SelfUser['first_name'];
                $Order['channel_partner_email'] = $SelfUser['email'];
                $Order['channel_partner_phone_number'] = $SelfUser['phone_number'];
                $Order['channel_partner_dialing_code'] = $SelfUser['dialing_code'];
                $Order['channel_partner_type'] = 0;
                $Order['channel_partner_credit_limit'] = 0;
                $Order['channel_partner_credit_days'] = 0;
                $Order['channel_partner_pending_credit'] = 0;
                $Order['channel_partner_type_name'] = "SELF";
            }

            $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();

            $OrderItem = MarketingOrderItem::query();

            if ($isAdminOrCompanyAdmin == 1) {

                $OrderItem->select('marketing_order_items.id', 'marketing_order_items.qty', 'marketing_order_items.total_mrp', 'marketing_order_items.pending_qty', 'marketing_order_items.marketing_product_inventory_id', 'marketing_product_inventory.thumb as product_image', 'product_code.name as product_code_name', 'marketing_product_inventory.quantity as product_stock', 'marketing_order_items.gst_percentage', 'marketing_order_items.gst_tax', 'marketing_order_items.total_gst_tax');
            } else {
                $OrderItem->select('marketing_order_items.id', 'marketing_order_items.qty', 'marketing_order_items.total_mrp', 'marketing_order_items.pending_qty', 'marketing_order_items.marketing_product_inventory_id', 'marketing_product_inventory.thumb as product_image', 'product_code.name as product_code_name', 'marketing_order_items.gst_percentage', 'marketing_order_items.gst_tax', 'marketing_order_items.total_gst_tax');
            }

            $OrderItem->leftJoin('marketing_product_inventory', 'marketing_product_inventory.id', '=', 'marketing_order_items.marketing_product_inventory_id');

            //$OrderItem->leftJoin('data_master as product_group', 'product_group.id', '=', 'marketing_product_inventory.marketing_product_group_id');
            $OrderItem->leftJoin('data_master as product_code', 'product_code.id', '=', 'marketing_product_inventory.marketing_product_code_id');

            $OrderItem->where('marketing_order_id', $Order->id);
            $OrderItem->orderBy('id', 'desc');
            $OrderItem = $OrderItem->get();

            // foreach ($OrderItem as $OK => $OV) {

            //  $OrderItem->product_image

            // }

            $Order['items'] = $OrderItem;
            // foreach ($Order['items'] as $it => $vit) {

            //  $path = getSpaceFilePath($Order['items'][$it]['product_image']);
            //  $type = pathinfo($path, PATHINFO_EXTENSION);
            //  $data = file_get_contents($path);
            //  $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            //  $Order['items'][$it]['product_image'] = $base64;

            // }

            $response = successRes("Order detail");

            $response['data'] = $Order;
        } else {
            $response = errorRes("Invalid order id");
        }

        $response = json_decode(json_encode($response), true);


        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function cancel(Request $request)
    {

        if (Auth::user()->type == 0) {

            $Order = MarketingOrder::find($request->id);
            if ($Order->status == 0) {
                $Order->status = 3;
                $Order->save();
                $response = successRes("Successfully mark as rejected");
            } else {
                $response = errorRes("Only Placed order can cancel");
            }
        } else {
            $response = errorRes("Invalid access");
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }


    public function searchChannelPartner(Request $request)
    {


        if (isset($request->channel_partner_type) && $request->channel_partner_type != 0) {
            $ChannelPartner = array();
            $ChannelPartner = ChannelPartner::select('channel_partner.user_id as id', DB::raw('CONCAT(channel_partner.firm_name," - ", city_list.name) AS text'));

            $ChannelPartner->where('channel_partner.type', $request->channel_partner_type);


            $ChannelPartner->leftJoin('city_list', 'city_list.id', '=', 'channel_partner.d_city_id');
            $ChannelPartner->leftJoin('users', 'users.id', '=', 'channel_partner.user_id');
            $ChannelPartner->where('users.status', 1);

            $q = $request->q;

            $isSalePerson = isSalePerson();
            if ($isSalePerson == 1) {

                $childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);

                $ChannelPartner->where(function ($query) use ($childSalePersonsIds) {

                    foreach ($childSalePersonsIds as $key => $value) {
                        if ($key == 0) {
                            $query->whereRaw('FIND_IN_SET("' . $value . '",channel_partner.sale_persons)>0');
                        } else {
                            $query->orWhereRaw('FIND_IN_SET("' . $value . '",channel_partner.sale_persons)>0');
                        }
                    }
                });
            } else if (isChannelPartner(Auth::user()->type) != 0) {

                $ChannelPartner->where('channel_partner.user_id', Auth::user()->id);
            }

            $ChannelPartner->where(function ($query) use ($q) {

                $query->where('channel_partner.firm_name', 'like', $q . "%");
                $query->orWhere('city_list.name', 'like', $q . "%");
            });
            $ChannelPartner->limit(9);
            $ChannelPartner = $ChannelPartner->get();
        } else {

            $ChannelPartner = User::select('id', DB::raw('CONCAT(users.first_name," ", users.last_name) AS text'))->where('id', Auth::user()->id)->where('users.status', 1)->get();
        }

        $response = successRes("");
        $response['data'] = $ChannelPartner;
        // $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function searchChannelPartnerTypes(Request $request)
    {

        $data = getChannelPartners();
        $data = array_values($data);
        foreach ($data as $key => $value) {
            unset($data[$key]['lable']);
            unset($data[$key]['key']);
            unset($data[$key]['url']);
            unset($data[$key]['url_view']);
            unset($data[$key]['url_sub_orders']);
            unset($data[$key]['can_login']);
            unset($data[$key]['inquiry_tab']);
        }

        $arrself = array();
        $arrself['id'] = 0;
        $arrself['name'] = "SELF";
        $arrself['short_name'] = "SELF";

        array_push($data, $arrself);
        $response = successRes("Get Channel Partner Type");
        $response['data'] = $data;
        return response()->json($response, $response['status_code'])->header('Content-Type', 'application/json');
    }
    public function channelPartnerDetail(Request $request)
    {

        if (Auth::user()->id != $request->channel_partner_user_id) {

            $ChannelPartner = ChannelPartner::query();
            $ChannelPartner->where('channel_partner.user_id', $request->channel_partner_user_id);
            $isSalePerson = isSalePerson();

            if ($isSalePerson == 1) {

                $childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);

                $ChannelPartner->where(function ($query) use ($childSalePersonsIds) {

                    foreach ($childSalePersonsIds as $key => $value) {
                        if ($key == 0) {
                            $query->whereRaw('FIND_IN_SET("' . $value . '",channel_partner.sale_persons)>0');
                        } else {
                            $query->orWhereRaw('FIND_IN_SET("' . $value . '",channel_partner.sale_persons)>0');
                        }
                    }
                });
            } else if (isChannelPartner(Auth::user()->type) != 0) {

                $ChannelPartner->where('channel_partner.user_id', Auth::user()->id);
            }

            $ChannelPartner->with(
                array(
                    'd_country' => function ($query) {
                        $query->select('id', 'name');
                    },
                    'd_state' => function ($query) {
                        $query->select('id', 'name');
                    },
                    'd_city' => function ($query) {
                        $query->select('id', 'name');
                    }
                )
            );

            $ChannelPartner = $ChannelPartner->first();

            $salePersons = User::select('users.id', 'users.first_name', 'users.last_name')->whereIn('users.id', explode(",", $ChannelPartner->sale_persons))->get();
            $salePersonsD = array();

			foreach ($salePersons as $keyS => $valueS) {

				$salePersonsD[$keyS] = $valueS['first_name'] . " " . $valueS['last_name'];
			}

            $obj_user = User::where('id', $ChannelPartner->user_id)->first();
            
			$ChannelPartner['sale_persons_full_name'] = implode(", ", $salePersonsD);
			$ChannelPartner['email'] = $obj_user->email;
			$ChannelPartner['phone_number'] = $obj_user->phone_number;
			$ChannelPartner['first_name'] = $obj_user->first_name;
			$ChannelPartner['last_name'] = $obj_user->last_name;
			$ChannelPartner['user_type'] = getUserTypeName($obj_user->type);


            $response = array();
            if ($ChannelPartner) {

                $response = successRes("");
                $response['data'] = $ChannelPartner;
                //$response = json_decode(json_encode($response), true);
            } else {
                $response = errorRes("Invalid Channel Partner");
            }
        } else {

            $ChannelPartner = User::where('id', Auth::user()->id)->first();
            $ChannelPartner = json_decode(json_encode($ChannelPartner), true);
            $ChannelPartner['user_id'] = $ChannelPartner['id'];
            $ChannelPartner['type'] = 0;
            $ChannelPartner['firm_name'] = $ChannelPartner['first_name'] . " " . $ChannelPartner['last_name'];
            $ChannelPartner['reporting_manager_id'] = 0;
            $ChannelPartner['reporting_company_id'] = 1;
            $ChannelPartner['sale_persons'] = "";
            $ChannelPartner['payment_mode'] = 0;
            $ChannelPartner['credit_days'] = 0;
            $ChannelPartner['credit_limit'] = 0;
            $ChannelPartner['credit_limit'] = 0;
            $ChannelPartner['pending_credit'] = 0;
            $ChannelPartner['gst_number'] = "";
            $ChannelPartner['shipping_limit'] = "";
            $ChannelPartner['shipping_cost'] = "";
            $ChannelPartner['d_address_line1'] = $ChannelPartner['address_line1'];
            $ChannelPartner['d_address_line2'] = $ChannelPartner['address_line2'];
            $ChannelPartner['d_pincode'] = $ChannelPartner['pincode'];
            $ChannelPartner['d_country_id'] = $ChannelPartner['country_id'];
            $ChannelPartner['d_state_id'] = $ChannelPartner['state_id'];
            $ChannelPartner['d_city_id'] = $ChannelPartner['city_id'];
            $ChannelPartner['tele_not_verified'] = 0;
            $ChannelPartner['tele_verified'] = 0;
            $ChannelPartner['missing_data'] = 0;
            $ChannelPartner['data_not_verified'] = 0;
            $ChannelPartner['data_not_verified'] = 0;
            $ChannelPartner['sale_persons_full_name'] = "";
            $ChannelPartner['email'] = $ChannelPartner['email'];
			$ChannelPartner['phone_number'] = $ChannelPartner['phone_number'];
			$ChannelPartner['first_name'] = $ChannelPartner['first_name'];
			$ChannelPartner['last_name'] = $ChannelPartner['last_name'];
			$ChannelPartner['user_type'] = getUserTypeName($ChannelPartner['type']);

            $DCityList = CityList::find($ChannelPartner['d_city_id']);
            $ChannelPartner['d_city'] = array();
            if ($DCityList) {

                $ChannelPartner['d_city']['id'] = $DCityList->id;
                $ChannelPartner['d_city']['name'] = $DCityList->name;
            }

            $DStateList = StateList::find($ChannelPartner['d_state_id']);
            $ChannelPartner['d_state'] = array();
            if ($DStateList) {
                $ChannelPartner['d_state'] = array();
                $ChannelPartner['d_state']['id'] = $DStateList->id;
                $ChannelPartner['d_state']['name'] = $DStateList->name;
            }

            $DCountryList = CountryList::find($ChannelPartner['d_country_id']);
            $ChannelPartner['d_country'] = array();
            if ($DCountryList) {

                $ChannelPartner['d_country'] = array();
                $ChannelPartner['d_country']['id'] = $DCountryList->id;
                $ChannelPartner['d_country']['name'] = $DCountryList->name;
            }


            $response = successRes("");
            $response['data'] = $ChannelPartner;
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function searchProduct(Request $request)
    {

        $DataMaster = array();
        $PRODUCT_CODE = MainMaster::select('id')->where('code', 'MARKETING_PRODUCT_CODE')->first();
        //$PRODUCT_GROUP = MainMaster::select('id')->where('code', 'MARKETING_PRODUCT_GROUP')->first();

        $DataMaster = MarketingProductInventory::select('marketing_product_inventory.id', DB::raw('CONCAT(product_code.name," (",marketing_product_inventory.description,")" )  as text'),'marketing_product_inventory.is_custome');
        //$DataMaster->leftJoin('data_master as product_group', 'product_group.id', '=', 'marketing_product_inventory.marketing_product_group_id');
        $DataMaster->leftJoin('data_master as product_code', 'product_code.id', '=', 'marketing_product_inventory.marketing_product_code_id');

        $searchValue = $request->q;
        $DataMaster->where('marketing_product_inventory.status', 1);
        // $DataMaster->where('product_group.main_master_id', $PRODUCT_GROUP->id);
        $DataMaster->where('product_code.main_master_id', $PRODUCT_CODE->id);
        
        if (isset($request->q)) {
            $DataMaster->whereRaw('CONCAT(product_code.name," (",marketing_product_inventory.description,")" )' . ' like ? ', ["%" . $searchValue . "%"]);
        }

        $DataMaster->limit(15);
        $DataMaster = $DataMaster->get();

        $response = successRes("Success");
        $response['data'] = $DataMaster;
        // $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function productDetail(Request $request)
    {
        $MarketingProductInventory = MarketingProductInventory::with(
            array(
                'product_group' => function ($query) {
                    $query->select('id', 'name');
                },
                'product_code' => function ($query) {
                    $query->select('id', 'name');
                }
            )
        )->where('id', $request->product_inventory_id)->first();
        $response = array();
        if ($MarketingProductInventory) {

            $response = successRes("");
            $response['data'] = $MarketingProductInventory;
        } else {
            $response = errorRes("Invalid Product");
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function calculation(Request $request)
    {

        if ($request->expectsJson()) {
            $inputJSON = $request->all();
            $orderItems = array();

            foreach ($inputJSON['order_items'] as $key => $value) {

                $orderItems[$key]['id'] = $value['id'];
                //$orderItems[$key]['info']['product_group'] = $value['product_group'];
                $orderItems[$key]['info']['product_code'] = $value['product_code'];
                $orderItems[$key]['info']['description'] = $value['description'];
                $orderItems[$key]['info']['image'] = $value['image'];
                $orderItems[$key]['info']['thumb'] = $value['thumb'];

                $orderItems[$key]['mrp'] = $value['sale_price'];
                $orderItems[$key]['qty'] = $value['order_qty'];
                $orderItems[$key]['discount_percentage'] = 0;
                $orderItems[$key]['weight'] = $value['weight'];
                $orderItems[$key]['gst_percentage'] = $value['gst_percentage'];
                $orderItems[$key]['width'] = 0;
                $orderItems[$key]['height'] = 0;
                $orderItems[$key]['box_image'] = ' ';
                $orderItems[$key]['sample_image'] = ' ';
                $orderItems[$key]['is_custom'] = 0;
            }

            $orderDetail = calculationProcessOfMarketingRequest($orderItems);

            $response = successRes("Order detail");
            $response['order'] = $orderDetail;

            $query = User::query();
            $query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
            $ChannelPartner = $query->find($inputJSON['channel_partner_user_id']);
            if ($inputJSON['channel_partner_user_id'] == Auth::user()->id) {

                $ChannelPartner = json_decode(json_encode($ChannelPartner), true);
                $ChannelPartner['user_id'] = $ChannelPartner['id'];
                $ChannelPartner['type'] = 0;
                $ChannelPartner['firm_name'] = $ChannelPartner['first_name'] . " " . $ChannelPartner['last_name'];
                $ChannelPartner['reporting_manager_id'] = 0;
                $ChannelPartner['reporting_company_id'] = 1;
                $ChannelPartner['sale_persons'] = "";
                $ChannelPartner['payment_mode'] = 0;
                $ChannelPartner['credit_days'] = 0;
                $ChannelPartner['credit_limit'] = 0;
                $ChannelPartner['credit_limit'] = 0;
                $ChannelPartner['pending_credit'] = 0;
                $ChannelPartner['gst_number'] = "";
                $ChannelPartner['shipping_limit'] = "";
                $ChannelPartner['shipping_cost'] = "";
                $ChannelPartner['d_address_line1'] = $ChannelPartner['address_line1'];
                $ChannelPartner['d_address_line2'] = $ChannelPartner['address_line2'];
                $ChannelPartner['d_pincode'] = $ChannelPartner['pincode'];
                $ChannelPartner['d_country_id'] = $ChannelPartner['country_id'];
                $ChannelPartner['d_state_id'] = $ChannelPartner['state_id'];
                $ChannelPartner['d_city_id'] = $ChannelPartner['city_id'];
                $ChannelPartner['tele_not_verified'] = 0;
                $ChannelPartner['tele_verified'] = 0;
                $ChannelPartner['missing_data'] = 0;
                $ChannelPartner['data_not_verified'] = 0;
                $ChannelPartner['data_not_verified'] = 0;
                $salePersons = array();
                $ChannelPartner['short_type_name'] = "SELF";
            } else {



                $ChannelPartner['short_type_name'] = getChannelPartners()[$ChannelPartner['type']]['short_name'];

                $salePersons = User::select('users.first_name', 'users.last_name')->whereIn('users.id', explode(",", $ChannelPartner->sale_persons))->get();
            }




            $data = $response;
            $data['channel_partner'] = json_decode(json_encode($ChannelPartner), true);
            $data['salePerson'] = json_decode(json_encode($salePersons), true);
            $data['d_country'] = $inputJSON['d_country'];
            $data['d_state'] = $inputJSON['d_state'];
            $data['d_city'] = $inputJSON['d_city'];
            $data['d_address_line1'] = $inputJSON['d_address_line1'];
            $data['d_address_line2'] = $inputJSON['d_address_line2'];
            $data['d_pincode'] = $inputJSON['d_pincode'];

            $response['preview'] = "";
            $response['preview'] = view('marketing/orders/preview', compact('data'))->render();
        } else {

            $response = errorRes("Something went wrong");
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function save(Request $request)
    {

        //$response = errorRes("Working...");
        //return response()->json($response)->header('Content-Type', 'application/json');

        $orderItems = array();

        foreach ($request->input_product_id as $key => $value) {

            $productInventory = MarketingProductInventory::find($value);

            if (!$productInventory) {
                $response = errorRes("Invalid Product");
                return response()->json($response)->header('Content-Type', 'application/json');
            } else if ($productInventory->status != 1) {

                $response = errorRes("Deactivated Product " . $productInventory->description);
                return response()->json($response)->header('Content-Type', 'application/json');
            }

            if (!isset($request->input_qty[$key])) {
                $response = errorRes("Invalid QTY");
                return response()->json($response)->header('Content-Type', 'application/json');
            }

            // $UserDiscount = UserDiscount::where('product_inventory_id', $value)->where('user_id', $request->channel_partner_user_id)->first();
            $discountPercentage = 0;
            // if ($UserDiscount) {
            // 	$discountPercentage = 0;
            // }

            $orderItems[$key]['id'] = $productInventory->id;
            $orderItems[$key]['info'] = "";
            $orderItems[$key]['mrp'] = $productInventory->sale_price;
            $orderItems[$key]['mrp2'] = $productInventory->purchase_price;
            $orderItems[$key]['qty'] = $request->input_qty[$key];
            $orderItems[$key]['discount_percentage'] = 0;
            $orderItems[$key]['weight'] = $productInventory->weight;
            //$orderItems[$key]['gst_tax'] = $productInventory->gst_tax;
            $orderItems[$key]['gst_percentage'] = $productInventory->gst_percentage;

            if ($productInventory->is_custome == 1) {
				$orderItems[$key]['width'] = $request->input_width[$key];	
				$orderItems[$key]['height'] = $request->input_height[$key];
				$orderItems[$key]['box_image'] = $request->input_box_image[$key];
				$orderItems[$key]['sample_image'] = $request->input_sample_image[$key];
				$orderItems[$key]['is_custom'] = 1;
			}
			else{
				$orderItems[$key]['width'] = 0;	
				$orderItems[$key]['height'] = 0;
				$orderItems[$key]['box_image'] = ' ';
				$orderItems[$key]['sample_image'] = ' ';
				$orderItems[$key]['is_custom'] = 0;
			}

            // $orderItems[$key]['width'] = 0;
            // $orderItems[$key]['height'] = 0;
            // $orderItems[$key]['box_image'] = ' ';
            // $orderItems[$key]['sample_image'] = ' ';
            // $orderItems[$key]['is_custom'] = 0;
        }

        $isSalePerson = isSalePerson();
        if ($request->channel_partner_user_id == Auth::user()->id) {

            $ChannelPartner = User::find($request->channel_partner_user_id);
            $ChannelPartner = json_decode(json_encode($ChannelPartner), true);
            $ChannelPartner['user_id'] = $ChannelPartner['id'];
            $ChannelPartner['type'] = 0;
            $ChannelPartner['firm_name'] = $ChannelPartner['first_name'] . " " . $ChannelPartner['last_name'];
            $ChannelPartner['reporting_manager_id'] = 0;
            $ChannelPartner['reporting_company_id'] = 1;
            $ChannelPartner['sale_persons'] = "";
            $ChannelPartner['payment_mode'] = 0;
            $ChannelPartner['credit_days'] = 0;
            $ChannelPartner['credit_limit'] = 0;
            $ChannelPartner['credit_limit'] = 0;
            $ChannelPartner['pending_credit'] = 0;
            $ChannelPartner['gst_number'] = "";
            $ChannelPartner['shipping_limit'] = "";
            $ChannelPartner['shipping_cost'] = "";
            $ChannelPartner['d_address_line1'] = $ChannelPartner['address_line1'];
            $ChannelPartner['d_address_line2'] = $ChannelPartner['address_line2'];
            $ChannelPartner['d_pincode'] = $ChannelPartner['pincode'];
            $ChannelPartner['d_country_id'] = $ChannelPartner['country_id'];
            $ChannelPartner['d_state_id'] = $ChannelPartner['state_id'];
            $ChannelPartner['d_city_id'] = $ChannelPartner['city_id'];
            $ChannelPartner['tele_not_verified'] = 0;
            $ChannelPartner['tele_verified'] = 0;
            $ChannelPartner['missing_data'] = 0;
            $ChannelPartner['data_not_verified'] = 0;
            $ChannelPartner['data_not_verified'] = 0;
            $salePersons = array();
            $ChannelPartner['short_type_name'] = "SELF";
        } else {

            $ChannelPartner = ChannelPartner::query();
            $ChannelPartner->where('user_id', $request->channel_partner_user_id);
            if ($isSalePerson == 1) {

                $childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);

                $ChannelPartner->where(function ($query) use ($childSalePersonsIds) {

                    foreach ($childSalePersonsIds as $key => $value) {
                        if ($key == 0) {
                            $query->whereRaw('FIND_IN_SET("' . $value . '",channel_partner.sale_persons)>0');
                        } else {
                            $query->orWhereRaw('FIND_IN_SET("' . $value . '",channel_partner.sale_persons)>0');
                        }
                    }
                });
            } else if (isChannelPartner(Auth::user()->type) != 0) {

                $ChannelPartner->where('channel_partner.user_id', Auth::user()->id);
            }
            $ChannelPartner = $ChannelPartner->first();
            $ChannelPartner = json_decode(json_encode($ChannelPartner), true);
        }




        $User = User::find($request->channel_partner_user_id);
        if ($ChannelPartner && $User) {

            //$shippingCost = floatval($ChannelPartner->shipping_cost);

        } else {
            $response = errorRes("Invalid Channel Partner");
            return response()->json($response)->header('Content-Type', 'application/json');
        }

        $orderDetail = calculationProcessOfMarketingRequest($orderItems);

        if ($request->verify_payable_total != $orderDetail['total_payable']) {
            $response = errorRes("Something went wrong with price calculation");
            $response['verify_payable_total'] = $request->verify_payable_total;
            $response['order'] = $orderDetail;
            return response()->json($response)->header('Content-Type', 'application/json');
        }

        if ($ChannelPartner['payment_mode'] == 2) {

            $pendingCredit = $ChannelPartner['pending_credit'];
            if ($pendingCredit >= $orderDetail['total_payable']) {

                $transcationCredit = CreditTranscationLog::where('user_id', $ChannelPartner['user_id'])->orderBy('id', 'desc')->first();
                if ($transcationCredit) {
                    $transcationCreditValue = $transcationCredit->amount;

                    if ($transcationCreditValue != $pendingCredit) {

                        // $response = errorRes("Channel Partner credit mismatch");
                        // return response()->json($response)->header('Content-Type', 'application/json');

                    }
                } else {

                    // $response = errorRes("Channel Partner has no credit transcation");
                    // return response()->json($response)->header('Content-Type', 'application/json');

                }
            } else {

                // $response = errorRes("Channel Partner has not enough credit to generate order");
                // return response()->json($response)->header('Content-Type', 'application/json');

            }

            $remainCredit = $pendingCredit - $orderDetail['total_payable'];
            if ($remainCredit < 0) {

                // $response = errorRes("Something went wrong with credit calculation");
                // return response()->json($response)->header('Content-Type', 'application/json');

            }
        }

        $Order = new MarketingOrder();
        $Order->user_id = Auth::user()->id;
        $Order->channel_partner_user_id = $ChannelPartner['user_id'];
        $Order->sale_persons = $ChannelPartner['sale_persons'];
        $Order->payment_mode = $ChannelPartner['payment_mode'];
        $Order->gst_number = $ChannelPartner['gst_number'];

        $Order->d_address_line1 = $request->d_address_line1;
        $Order->d_address_line2 = isset($request->d_address_line2) ? $request->d_address_line2 : '';
        $Order->d_pincode = $request->d_pincode;
        $Order->d_country_id = $request->d_country_id;
        $Order->d_state_id = $request->d_state_id;
        $Order->d_city_id = $request->d_city_id;

        if ($ChannelPartner['user_id'] == Auth::user()->id) {
            $Order->is_self = 1;
        }

        $Order->bill_address_line1 = $User->address_line1;
        $Order->bill_address_line2 = $User->address_line2;
        $Order->bill_pincode = $User->pincode;
        $Order->bill_country_id = $User->country_id;
        $Order->bill_state_id = $User->state_id;
        $Order->bill_city_id = $User->city_id;

        $Order->status = 0;
        $Order->sub_status = 0;
        $Order->total_qty = $orderDetail['total_qty'];
        $Order->total_mrp = $orderDetail['total_mrp'];
        $Order->total_discount = $orderDetail['total_discount'];
        $Order->total_mrp_minus_disocunt = $orderDetail['total_mrp_minus_disocunt'];
        //$Order->gst_percentage = 0;
        $Order->gst_tax = $orderDetail['gst_tax'];
        $Order->total_weight = $orderDetail['total_weight'];
        $Order->shipping_cost = 0;
        $Order->delievery_charge = 0;

        $Order->total_payable = $orderDetail['total_payable'];
        $Order->pending_total_payable = $orderDetail['total_payable'];
        $Order->remark = isset($request->remark) ? $request->remark : '';
        $Order->entryip = $request->ip();
		$Order->source = $request->app_source;
        $Order->save();

        foreach ($orderDetail['items'] as $key => $value) {
            $OrderItem = new MarketingOrderItem();
            $OrderItem->user_id = $Order->user_id;
            $OrderItem->channel_partner_user_id = $Order->channel_partner_user_id;
            $OrderItem->marketing_order_id = $Order->id;
            $OrderItem->marketing_product_inventory_id = $value['id'];
            $OrderItem->qty = $value['qty'];
            $OrderItem->pending_qty = $value['qty'];
            $OrderItem->mrp = $value['mrp'];
            $OrderItem->purchase_mrp = $value['mrp2'];
            $OrderItem->total_mrp = $value['total_mrp'];

            $OrderItem->gst_percentage = $value['gst_percentage'];
            $OrderItem->gst_tax = $value['gst_tax'];
            $OrderItem->total_gst_tax = $value['total_gst_tax'];

            $OrderItem->discount_percentage = $value['discount_percentage'];
            $OrderItem->discount = $value['discount'];
            $OrderItem->total_discount = $value['total_discount'];
            $OrderItem->mrp_minus_disocunt = $value['mrp_minus_disocunt'];
            $OrderItem->weight = $value['weight'];
            $OrderItem->total_weight = $value['total_weight'];
            $OrderItem->width = $value['width'];
			$OrderItem->height = $value['height'];

            if($value['is_custom'] == 1)
			{
				if ($value['box_image'] != '') {
					$folderPathofFile = '/s/marketing/box_image';
					$fileObject1 = base64_decode($value['box_image']);
					$temp = $value['box_image'];
					$extension = '.png';
					$fileName1 = uniqid() . '_' . $value['id'] . $extension;
					$destinationPath = public_path($folderPathofFile);
	
					// if (!is_dir(public_path($folderPathofFile))) {
					// 	mkdir(public_path($folderPathofFile));
					// }
	
					file_put_contents($destinationPath . '/' . $fileName1, $fileObject1);
	
					if (File::exists(public_path($folderPathofFile . '/' . $fileName1))) {
						$Box_Image_Path = $folderPathofFile . '/' . $fileName1;
						//START UPLOAD FILE ON SPACES
						$spaceUploadResponse = uploadFileOnSpaces(public_path($Box_Image_Path), $Box_Image_Path); //Live
	
						if ($spaceUploadResponse != 1) {
							$Box_Image_Path = "";
						} else {
							unlink(public_path($Box_Image_Path));
						}
						//END UPLOAD FILE ON SPACES
					} else {
						$Box_Image_Path = '/assets/images/logo.png';
					}
				} else {
					$Box_Image_Path = '/assets/images/logo.png';
				}
	
				if ($value['sample_image'] != '' && $value['sample_image'] != "" &&   $value['sample_image'] != null) {
					$folderPathofFile = '/s/marketing/sample_image';
					$fileObject1 = base64_decode($value['sample_image']);
					$extension = '.png';
					$fileName1 = uniqid() . '_' . $value['id'] . $extension;
					$destinationPath = public_path($folderPathofFile);
	
					// if (!is_dir(public_path($folderPathofFile))) {
					// 	mkdir(public_path($folderPathofFile));
					// }
	
					file_put_contents($destinationPath . '/' . $fileName1, $fileObject1);
	
					if (File::exists(public_path($folderPathofFile . '/' . $fileName1))) {
						$Sample_Image_Path = $folderPathofFile . '/' . $fileName1;
						//START UPLOAD FILE ON SPACES
						$spaceUploadResponse = uploadFileOnSpaces(public_path($Sample_Image_Path), $Sample_Image_Path); //Live
	
						if ($spaceUploadResponse != 1) {
							$Sample_Image_Path = "";
						} else {
							unlink(public_path($Sample_Image_Path));
						}
						//END UPLOAD FILE ON SPACES
					} else {
						$Sample_Image_Path = '/assets/images/logo.png';
					}
				} else {
					$Sample_Image_Path = '/assets/images/logo.png';
				}
				$OrderItem->box_image = $Box_Image_Path;
				$OrderItem->sample_image = $Sample_Image_Path;
			}
			else
			{
				$OrderItem->box_image = " ";
				$OrderItem->sample_image = " ";
			}

            // $OrderItem->box_image = " ";
            // $OrderItem->sample_image = " ";
            
            $OrderItem->entryip = $request->ip();
		    $OrderItem->source = $request->app_source;
            $OrderItem->save();
        }

        // echo '<pre>';
        // print_r($orderDetail['items']);
        // d

        $ChannelPartnerUser = User::find($ChannelPartner['user_id']);

        if ($ChannelPartnerUser) {

            $salesPersonString = array();
            $salePersons = User::select('users.first_name', 'users.last_name', 'users.email')->whereIn('users.id', explode(",", $ChannelPartner['sale_persons']))->get();

            foreach ($salePersons as $keyS => $valueS) {

                $salesPersonString[] = $valueS->first_name . " " . $valueS->last_name;
            }
            $salesPersonString = implode(" , ", $salesPersonString);

            foreach ($orderDetail['items'] as $key => $value) {

                $productInventory = MarketingProductInventory::find($value['id']);

                if ($productInventory->notify_when_order == 1) {

                    //$orderDetail['items'][$key]['notify_emails'] = array();

                    if ($productInventory->notify_emails != "") {

                        $notify_emails = explode(",", $productInventory->notify_emails);
                        foreach ($notify_emails as $keyNE => $valNE) {

                            if ($valNE != "") {

                                if (filter_var($valNE, FILTER_VALIDATE_EMAIL)) {
                                    //$fromEmailDetail = fromEmailDetail();

                                    // $MarketingProductInventory = MarketingProductInventory::select('marketing_product_inventory.id', DB::raw('CONCAT(product_brand.name," ",product_code.name," (",marketing_product_inventory.description,")" )  as text'));
                                    // $MarketingProductInventory->leftJoin('data_master as product_brand', 'product_brand.id', '=', 'marketing_product_inventory.product_brand_id');
                                    // $MarketingProductInventory->leftJoin('data_master as product_code', 'product_code.id', '=', 'marketing_product_inventory.product_code_id');
                                    // $MarketingProductInventory = $MarketingProductInventory->find($value['id']);

                                    // $params = array();
                                    // $params['from_email'] = $fromEmailDetail['email'];
                                    // $params['from_name'] = $fromEmailDetail['name'];
                                    // $params['to_email'] = $valNE;
                                    // $params['to_name'] = $fromEmailDetail['name'];
                                    // $params['subject'] = "New Order Placed - #" . $Order->id;

                                    // $params['order'] = array();
                                    // $params['order']['id'] = $Order->id;
                                    // $params['order']['company_name'] = $ChannelPartner->firm_name;
                                    // $params['order']['phone_number'] = $ChannelPartnerUser->phone_number;
                                    // $params['order']['first_name'] = $ChannelPartnerUser->first_name;
                                    // $params['order']['last_name'] = $ChannelPartnerUser->last_name;
                                    // $params['order']['address_line1'] = $ChannelPartnerUser->address_line1;
                                    // $params['order']['address_line2'] = $ChannelPartnerUser->address_line2;
                                    // $params['order']['pincode'] = $ChannelPartnerUser->pincode;
                                    // $params['order']['gst_number'] = $ChannelPartner->gst_number;
                                    // $params['order']['city_id'] = $ChannelPartnerUser->city_id;
                                    // $params['order']['state_id'] = $ChannelPartnerUser->state_id;
                                    // $params['order']['country_id'] = $ChannelPartnerUser->country_id;
                                    // $params['order']['sale_persons'] = $salesPersonString;
                                    // $params['order']['type'] = getAllUserTypes()[$ChannelPartner->type]['short_name'];
                                    // $params['order']['gst_number'] = $ChannelPartner->gst_number;
                                    // $params['order']['qty'] = $value['qty'];
                                    // $params['order']['item_name'] = $MarketingProductInventory->product_brand . " " . $MarketingProductInventory->code . " " . $MarketingProductInventory->text . "";

                                    // Mail::send('emails.notify_when_order', ['params' => $params], function ($m) use ($params) {
                                    // 	$m->from($params['from_email'], $params['from_name']);
                                    // 	$m->to($params['to_email'], $params['to_name'])->subject($params['subject']);

                                    // });

                                }
                            }
                        }
                    }
                }
            }

            // $fileName = 'order-' . $Order->id . '.pdf';
            // $filePath = 's/order/' . $fileName;
            // $this->savePDF($Order->id, $filePath);

            $params = array();

            // $fromEmailDetail = fromEmailDetail();
            // $params['from_email'] = $fromEmailDetail['email'];
            // $params['from_name'] = $fromEmailDetail['name'];
            // $params['to_email'] = $ChannelPartnerUser->email;
            // $params['to_name'] = $fromEmailDetail['name'];
            // $params['subject'] = "New Order Placed - #" . $Order->id;
            // $params['file_path'] = $filePath;
            // $params['file_name'] = $fileName;
            // $params['id'] = $Order->id;
            // $params['order_by'] = Auth::user()->first_name . " " . Auth::user()->last_name;

            // Mail::send('emails.order_channel_partner', ['params' => $params], function ($m) use ($params) {
            // 	$m->from($params['from_email'], $params['from_name']);
            // 	$m->to($params['to_email'], $params['to_name'])->subject($params['subject']);
            // 	$m->attach(public_path($params['file_path']), array(
            // 		'as' => $params['file_name']));
            // });

            // foreach ($salePersons as $keyS => $valueS) {

            // 	$params['from_email'] = $fromEmailDetail['email'];
            // 	$params['from_name'] = $fromEmailDetail['name'];
            // 	$params['to_email'] = $valueS->email;
            // 	$params['to_name'] = $fromEmailDetail['name'];
            // 	$params['subject'] = "New Order Placed - #" . $Order->id;
            // 	$params['file_path'] = $filePath;
            // 	$params['file_name'] = $fileName;
            // 	$params['id'] = $Order->id;

            // 	Mail::send('emails.order_sales_person', ['params' => $params], function ($m) use ($params) {
            // 		$m->from($params['from_email'], $params['from_name']);
            // 		$m->to($params['to_email'], $params['to_name'])->subject($params['subject']);
            // 		$m->attach(public_path($params['file_path']), array(
            // 			'as' => $params['file_name']));
            // 	});

            // }

            // if (is_file($filePath)) {
            // 	unlink($filePath);
            // }
        }

        $response = successRes("Successfully generated Markeing request #" . $Order->id);
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function invoiceList(Request $request)
    {

        $Order = MarketingOrder::select('id')->find($request->order_id);
        if ($Order) {
            $data['title'] = "Invoice";
            $data['order_id'] = $Order->id;
            return view('orders/invoice', compact('data'));
        } else {
            return redirect()->route('dashboard');
        }
    }

    function invoiceListAjax(Request $request)
    {

        $searchColumns = array(
            0 => 'invoice.id',
            1 => 'invoice.invoice_number',
            2 => 'invoice.invoice_date',

        );

        $sortingColumns = array(
            0 => 'invoice.id',
            1 => 'marketing_orders.user_id',
            2 => 'marketing_orders.channel_partner_user_id',
            3 => 'marketing_orders.sale_persons',
            4 => 'marketing_orders.payment_mode',
            5 => 'invoice.status',

        );

        $selectColumns = array(
            0 => 'invoice.id',
            1 => 'marketing_orders.user_id',
            2 => 'marketing_orders.channel_partner_user_id',
            3 => 'marketing_orders.sale_persons',
            4 => 'marketing_orders.payment_mode',
            5 => 'invoice.status',
            6 => 'invoice.created_at',
            7 => 'users.first_name as first_name',
            8 => 'users.last_name as last_name',
            9 => 'channel_partner.firm_name',
            10 => 'marketing_orders.payment_mode',
            11 => 'invoice.gst_tax',
            12 => 'invoice.total_payable',
            13 => 'invoice.invoice_file',
            14 => 'invoice.invoice_number',

        );

        $isSalePerson = isSalePerson();

        $recordsTotal = Invoice::query();
        $recordsTotal->leftJoin('orders', 'invoice.order_id', '=', 'marketing_orders.id');
        $recordsTotal->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'marketing_orders.channel_partner_user_id');
        $recordsTotal->where('channel_partner.reporting_company_id', Auth::user()->company_id);
        $recordsTotal->where('invoice.order_id', $request->order_id);

        if ($isSalePerson == 1) {
            $childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);

            $recordsTotal->where(function ($query) use ($childSalePersonsIds) {

                foreach ($childSalePersonsIds as $key => $value) {
                    if ($key == 0) {
                        $query->whereRaw('FIND_IN_SET("' . $value . '",marketing_orders.sale_persons)>0');
                    } else {
                        $query->orWhereRaw('FIND_IN_SET("' . $value . '",marketing_orders.sale_persons)>0');
                    }
                }
            });

            // foreach ($childSalePersonsIds as $key => $value) {
            //  if ($key == 0) {
            //      $recordsTotal->whereRaw('FIND_IN_SET("' . $value . '",marketing_orders.sale_persons)>0');
            //  } else {
            //      $recordsTotal->orWhereRaw('FIND_IN_SET("' . $value . '",marketing_orders.sale_persons)>0');
            //  }

            // }

        } else if (isChannelPartner(Auth::user()->type) != 0) {

            $recordsTotal->where('marketing_orders.channel_partner_user_id', Auth::user()->id);
        }

        $recordsTotal = $recordsTotal->count();

        $recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.
        $query = Invoice::query();
        $query->leftJoin('orders', 'invoice.order_id', '=', 'marketing_orders.id');
        $query->leftJoin('users', 'users.id', '=', 'marketing_orders.user_id');
        $query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'marketing_orders.channel_partner_user_id');
        $query->where('invoice.order_id', $request->order_id);

        if ($isSalePerson == 1) {

            $childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);

            $query->where(function ($query2) use ($childSalePersonsIds) {

                foreach ($childSalePersonsIds as $key => $value) {
                    if ($key == 0) {
                        $query2->whereRaw('FIND_IN_SET("' . $value . '",marketing_orders.sale_persons)>0');
                    } else {
                        $query2->orWhereRaw('FIND_IN_SET("' . $value . '",marketing_orders.sale_persons)>0');
                    }
                }
            });
            // foreach ($childSalePersonsIds as $key => $value) {
            //  if ($key == 0) {
            //      $query->whereRaw('FIND_IN_SET("' . $value . '",marketing_orders.sale_persons)>0');
            //  } else {
            //      $query->orWhereRaw('FIND_IN_SET("' . $value . '",marketing_orders.sale_persons)>0');
            //  }

            // }

        } else if (isChannelPartner(Auth::user()->type) != 0) {

            $query->where('marketing_orders.channel_partner_user_id', Auth::user()->id);
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
                        $query->where($searchColumns[$i], 'like', "%" . $search_value . "%");
                    } else {

                        $query->orWhere($searchColumns[$i], 'like', "%" . $search_value . "%");
                    }
                }
            });
        }

        $data = $query->get();

        $data = json_decode(json_encode($data), true);
        if ($isFilterApply == 1) {
            $recordsFiltered = count($data);
        }

        foreach ($data as $key => $value) {

            $data[$key]['detail'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">#' . $value['id'] . '</a></h5>
                <p class="text-muted mb-0" data-bs-toggle="tooltip" title="INVOICE NO">' . ($value['invoice_number']) . '</p>
                <p class="text-muted mb-0" data-bs-toggle="tooltip"  data-bs-original-title="' . convertOrderDateTime($value['created_at'], "time") . '" >' . convertOrderDateTime($value['created_at'], "date") . '</p>';

            $paymentMode = "";

            $paymentMode = '<span class="badge rounded-pill badge-soft-dark font-size-11">' . getPaymentModeName($value['payment_mode']) . '</span>';

            $data[$key]['order_by'] = '<p class="text-muted mb-0">' . $value['first_name'] . '  ' . $value['last_name'] . '</p>';
            $data[$key]['channel_partner'] = '<p class="text-muted mb-0">' . $value['firm_name'] . '</p><p class="text-muted mb-0">' . $paymentMode . '</p>';

            $sale_persons = explode(",", $value['sale_persons']);

            $Users = User::select('first_name', 'last_name')->whereIn('id', $sale_persons)->get();

            $uiSalePerson = '<ul class="list-inline font-size-20 contact-links mb-0">';
            foreach ($Users as $kU => $vU) {
                $uiSalePerson .= '<li class="list-inline-item px-2">';
                $uiSalePerson .= '<a  data-bs-toggle="tooltip" title="' . $vU['first_name'] . ' ' . $vU['last_name'] . '" href="javascript: void(0);" ><i class="bx bx-user"></i></a>';
                $uiSalePerson .= '</li>';
            }

            $uiSalePerson .= '</ul>';

            $data[$key]['sale_persons'] = $uiSalePerson;

            $data[$key]['payment_detail'] = '<p class="text-muted mb-0">GST&nbsp;&nbsp;&nbsp;&nbsp;&nbsp&nbsp;&nbsp;&nbsp;: <i class="fas fa-rupee-sign"></i> <span class="price-lable-font">' . priceLable($value['gst_tax']) . '</span></p>

            <p class="text-muted mb-0 ">Total &nbsp;&nbsp;&nbsp;&nbsp;&nbsp: <i class="fas fa-rupee-sign"></i> <span class="price-lable-font">' . priceLable($value['total_payable']) . '</span></p>


               ';

            $data[$key]['status'] = getInvoiceLable($value['status']);

            $uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';

            $uiAction .= '<li class="list-inline-item px-2">';
            $uiAction .= '<a onclick="ViewInvoice(\'' . $value['id'] . '\')" href="javascript: void(0);" title="View"><i class="mdi mdi-eye"></i></a>';
            $uiAction .= '</li>';

            $uiAction .= '<li class="list-inline-item px-2">';

            $uiAction .= '<a target="_blank" href="' . getSpaceFilePath($value['invoice_file']) . '" title="PDF"><i class="bx bxs-file-pdf"></i></a>';
            $uiAction .= '</li>';

            $uiAction .= '</ul>';
            $data[$key]['action'] = $uiAction;
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

    public function invoiceDetail(Request $request)
    {

        $isSalePerson = isSalePerson();

        $Order = Invoice::query();
        $Order->select('invoice.id', 'invoice.dispatch_detail', 'invoice.eway_bill', 'invoice.created_at', 'marketing_orders.gst_number', 'marketing_orders.payment_mode', 'marketing_orders.sale_persons', 'invoice.total_mrp_minus_disocunt', 'invoice.total_mrp', 'invoice.gst_tax', 'invoice.gst_percentage', 'invoice.delievery_charge', 'invoice.total_payable', 'marketing_orders.bill_address_line1', 'marketing_orders.bill_address_line2', 'marketing_orders.bill_pincode', 'marketing_orders.bill_state_id', 'marketing_orders.bill_city_id', 'marketing_orders.bill_country_id', 'marketing_orders.d_address_line1', 'marketing_orders.d_address_line2', 'marketing_orders.d_pincode', 'marketing_orders.d_state_id', 'marketing_orders.d_city_id', 'marketing_orders.d_country_id', 'channel_partner_detail.email as channel_partner_email', 'channel_partner_detail.phone_number as channel_partner_phone_number', 'channel_partner_detail.dialing_code as channel_partner_dialing_code', 'channel_partner_detail.first_name as channel_partner_first_name', 'channel_partner_detail.last_name as channel_partner_last_name', 'channel_partner.firm_name as channel_partner_firm_name', 'channel_partner.type as channel_partner_type', 'channel_partner.credit_limit as channel_partner_credit_limit', 'channel_partner.credit_days as channel_partner_credit_days', 'channel_partner.pending_credit as channel_partner_pending_credit', 'channel_partner.credit_days as channel_partner_credit_days');
        $Order->leftJoin('orders', 'invoice.order_id', '=', 'marketing_orders.id');
        $Order->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'marketing_orders.channel_partner_user_id');
        $Order->leftJoin('users as channel_partner_detail', 'channel_partner_detail.id', '=', 'channel_partner.user_id');
        $Order->where('invoice.id', $request->invoice_id);

        if ($isSalePerson == 1) {

            $childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);

            $Order->where(function ($query) use ($childSalePersonsIds) {

                foreach ($childSalePersonsIds as $key => $value) {
                    if ($key == 0) {
                        $query->whereRaw('FIND_IN_SET("' . $value . '",marketing_orders.sale_persons)>0');
                    } else {
                        $query->orWhereRaw('FIND_IN_SET("' . $value . '",marketing_orders.sale_persons)>0');
                    }
                }
            });

            // foreach ($childSalePersonsIds as $key => $value) {
            //  if ($key == 0) {
            //      $Order->whereRaw('FIND_IN_SET("' . $value . '",marketing_orders.sale_persons)>0');
            //  } else {
            //      $Order->orWhereRaw('FIND_IN_SET("' . $value . '",marketing_orders.sale_persons)>0');
            //  }

            // }

        } else if (isChannelPartner(Auth::user()->type) != 0) {

            $Order->where('marketing_orders.channel_partner_user_id', Auth::user()->id);
        }
        $Order = $Order->first();

        if ($Order) {

            $Order->dispatch_detail = explode(",", $Order->dispatch_detail);

            $salePersons = explode(",", $Order['sale_persons']);
            $salePersons = User::select('first_name', 'last_name')->whereIn('id', $salePersons)->get();

            $salePersonsD = array();

            foreach ($salePersons as $keyS => $valueS) {

                $salePersonsD[$keyS] = $valueS['first_name'] . " " . $valueS['last_name'];
            }

            $Order['sale_persons'] = implode(", ", $salePersonsD);

            $BCityList = CityList::find($Order['bill_city_id']);
            $Order['bill_city_name'] = "";
            if ($BCityList) {
                $Order['bill_city_name'] = $BCityList->name;
            }

            $BStateList = StateList::find($Order['bill_state_id']);
            $Order['bill_state_name'] = "";
            if ($BStateList) {
                $Order['bill_state_name'] = $BStateList->name;
            }

            $BCountryList = CountryList::find($Order['bill_country_id']);
            $Order['bill_country_name'] = "";
            if ($BCountryList) {
                $Order['bill_country_name'] = $BCountryList->name;
            }

            $DCityList = CityList::find($Order['d_city_id']);
            $Order['d_city_name'] = "";
            if ($DCityList) {
                $Order['d_city_name'] = $DCityList->name;
            }

            $DStateList = StateList::find($Order['d_state_id']);
            $Order['d_state_name'] = "";
            if ($DStateList) {
                $Order['d_state_name'] = $DStateList->name;
            }

            $DCountryList = CountryList::find($Order['d_country_id']);
            $Order['d_country_name'] = "";
            if ($DCountryList) {
                $Order['d_country_name'] = $DCountryList->name;
            }

            $Order['payment_mode_lable'] = getPaymentModeName($Order['payment_mode']);

            $Order['channel_partner_type_name'] = getUserTypeName($Order['channel_partner_type']);
            $Order['display_date_time'] = convertOrderDateTime($Order->created_at, "date");

            $OrderItem = InvoiceItem::query();
            $OrderItem->select('marketing_order_items.id', 'invoice_items.qty', 'invoice_items.total_mrp', 'marketing_product_inventory.thumb as product_image', 'product_brand.name as product_brand_name', 'product_code.name as product_code_name');
            $OrderItem->leftJoin('order_items', 'invoice_items.order_item_id', '=', 'marketing_order_items.id');
            $OrderItem->leftJoin('product_inventory', 'marketing_product_inventory.id', '=', 'marketing_order_items.marketing_product_inventory_id');
            $OrderItem->leftJoin('data_master as product_brand', 'product_brand.id', '=', 'marketing_product_inventory.product_brand_id');
            $OrderItem->leftJoin('data_master as product_code', 'product_code.id', '=', 'marketing_product_inventory.product_code_id');

            $OrderItem->where('invoice_id', $Order->id);
            $OrderItem->orderBy('id', 'desc');
            $OrderItem = $OrderItem->get();

            $Order['items'] = $OrderItem;

            $response = successRes("Order detail");
            $response['data'] = $Order;
        } else {
            $response = errorRes("Invalid order id");
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function export(Request $request)
    {

        $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
        $isAccountUser = isAccountUser();
        $isSalePerson = isSalePerson();
        $isChannelPartner = isChannelPartner(Auth::user()->type);

        $startDate = date('Y-m-d 00:00:00', strtotime($request->start_date));
        $startDate = date('Y-m-d H:i:s', strtotime($startDate . " -5 hours"));
        $startDate = date('Y-m-d H:i:s', strtotime($startDate . " -30 minutes"));

        $endDate = date('Y-m-d 23:59:59', strtotime($request->end_date));
        $endDate = date('Y-m-d H:i:s', strtotime($endDate . " -5 hours"));
        $endDate = date('Y-m-d H:i:s', strtotime($endDate . " -30 minutes"));

        if ($request->export_type == 0 || $request->export_type == 1) {

            $Order = MarketingOrder::query();
            $Order->select('marketing_orders.id', 'channel_partner.firm_name', 'marketing_orders.created_at');
            $Order->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'marketing_orders.channel_partner_user_id');
            $Order->orderBy('marketing_orders.id', 'desc');
            $Order->where('marketing_orders.created_at', '>=', $startDate);
            $Order->where('marketing_orders.created_at', '<=', $endDate);

            if (isset($request->channel_partner_user_id) && is_array($request->channel_partner_user_id)) {

                $Order->whereIn('marketing_orders.channel_partner_user_id', $request->channel_partner_user_id);
            }

            if ($request->filter_type == 1) {

                if ($isSalePerson == 1) {

                    $childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
                    $Order->where(function ($query) use ($childSalePersonsIds) {

                        foreach ($childSalePersonsIds as $key => $value) {
                            if ($key == 0) {
                                $query->whereRaw('FIND_IN_SET("' . $value . '",marketing_orders.sale_persons)>0');
                            } else {
                                $query->orWhereRaw('FIND_IN_SET("' . $value . '",marketing_orders.sale_persons)>0');
                            }
                        }
                    });

                    $Order->where('channel_partner.reporting_company_id', Auth::user()->company_id);
                } else if ($isChannelPartner != 0) {

                    $Order->where('marketing_orders.channel_partner_user_id', Auth::user()->id);
                }

                // ORDERS
                //$query->whereIn('marketing_orders.status', array(0, 1, 2));
            } else if ($request->filter_type == 2) {
                //SALES ORDERS
                $Order->whereIn('marketing_orders.status', array(0, 1, 2, 3));

                if ($isAccountUser == 1 || $isAdminOrCompanyAdmin == 1) {

                    if (Auth::user()->parent_id != 0) {

                        $parent = ChannelPartner::where('user_id', Auth::user()->parent_id)->first();

                        $Order->where('channel_partner.reporting_manager_id', $parent->user_id);
                    } else {

                        $Order->where('channel_partner.reporting_company_id', Auth::user()->company_id);
                        $Order->where('channel_partner.reporting_manager_id', 0);
                    }
                } else if (isChannelPartner(Auth::user()->type) != 0) {

                    $Order->where('channel_partner.reporting_manager_id', Auth::user()->id);
                }
            }

            if ($request->export_type == 0) {
                $Order->whereIn('marketing_orders.status', [0, 1, 2, 3]);
            } else if ($request->export_type == 1) {
                $Order->whereIn('marketing_orders.status', [0, 1, 2]);
            }

            $Order = $Order->get();

            $orderIds = array(0);

            foreach ($Order as $key => $value) {

                $orderIds[] = $value->id;
            }

            $orderItemsPendingQTY = array();

            $OrderItem = MarketingOrderItem::query();
            $OrderItem->select('marketing_order_items.pending_qty', 'marketing_order_items.order_id', 'marketing_order_items.marketing_product_inventory_id', 'marketing_order_items.order_id', 'marketing_order_items.qty');
            $OrderItem->orderBy('marketing_order_items.id', 'desc');
            $OrderItem->whereIn('marketing_order_items.order_id', $orderIds);

            if (isset($request->product_inventory_id) && is_array($request->product_inventory_id)) {

                $OrderItem->whereIn('marketing_order_items.marketing_product_inventory_id', $request->product_inventory_id);
            }
            if ($request->export_type != 0) {
                $OrderItem->where('marketing_order_items.pending_qty', '>', 0);
            }
            $OrderItems = $OrderItem->get();

            $productIds = array(0);

            $orderIds = array(0);

            foreach ($OrderItems as $key => $value) {
                $productIds[] = $value->product_inventory_id;

                if ($request->export_type == 0) {
                    $orderItemsPendingQTY[$value->order_id . "_" . $value->product_inventory_id] = $value->qty;
                } else {
                    $orderItemsPendingQTY[$value->order_id . "_" . $value->product_inventory_id] = $value->pending_qty;
                }

                $orderIds[] = $value->order_id;
            }

            if (isset($request->product_inventory_id) && is_array($request->product_inventory_id)) {

                $Order = MarketingOrder::query();
                $Order->select('marketing_orders.id', 'channel_partner.firm_name', 'marketing_orders.created_at');
                $Order->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'marketing_orders.channel_partner_user_id');
                $Order->orderBy('marketing_orders.id', 'desc');
                $Order->whereIn('marketing_orders.id', $orderIds);
                $Order = $Order->get();
            }

            $productIds = array_unique($productIds);
            $productIds = array_values($productIds);

            $Products = MarketingProductInventory::select('marketing_product_inventory.id', DB::raw('CONCAT(product_brand.name," ",product_code.name )  as text'));
            $Products->leftJoin('data_master as product_brand', 'product_brand.id', '=', 'marketing_product_inventory.product_brand_id');
            $Products->leftJoin('data_master as product_code', 'product_code.id', '=', 'marketing_product_inventory.product_code_id');
            $Products->whereIn('marketing_product_inventory.id', $productIds);
            $Products = $Products->get();

            $productIdText = array("");

            foreach ($productIds as $key => $value) {

                if ($key != 0) {

                    foreach ($Products as $keyP => $valueP) {

                        if ($value == $valueP->id) {
                            $productIdText[] = $valueP->text;
                            break;
                        }
                    }
                }
            }
            // echo '<pre>';
            // print_r($productIds);
            // print_r($productIdText);
            // die;

            $headers = array("Channel Partner/Products", "#orderId", "orderDate");
            foreach ($productIdText as $key => $value) {
                if ($key != 0) {
                    $headers[] = $value;
                }
            }

            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="reports.csv"');
            $fp = fopen('php://output', 'wb');

            fputcsv($fp, $headers);

            foreach ($Order as $key => $value) {

                $created_at = convertOrderDateTime($value->created_at, "date");

                $lineVal = array(
                    $value->firm_name,
                    $value->id,
                    $created_at,

                );

                foreach ($productIds as $keyP => $valeP) {

                    if ($keyP != 0) {

                        if (isset($orderItemsPendingQTY[$value->id . "_" . $valeP])) {

                            $lineVal[] = $orderItemsPendingQTY[$value->id . "_" . $valeP];
                        } else {
                            $lineVal[] = "";
                        }
                    }
                }

                fputcsv($fp, $lineVal, ",");
            }

            fclose($fp);
        } else if ($request->export_type == 2) {

            $Order = MarketingOrder::query();
            $Order->select('marketing_orders.id', 'channel_partner.firm_name', 'marketing_orders.created_at', 'users.first_name', 'users.last_name', 'marketing_orders.sale_persons', 'marketing_orders.total_mrp_minus_disocunt', 'marketing_orders.total_payable', 'marketing_orders.status', 'marketing_orders.sub_status');
            $Order->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'marketing_orders.channel_partner_user_id');
            $Order->leftJoin('users', 'users.id', '=', 'marketing_orders.user_id');
            $Order->orderBy('marketing_orders.id', 'desc');
            $Order->where('marketing_orders.created_at', '>=', $startDate);
            $Order->where('marketing_orders.created_at', '<=', $endDate);

            if (isset($request->channel_partner_user_id) && is_array($request->channel_partner_user_id)) {

                $Order->whereIn('marketing_orders.channel_partner_user_id', $request->channel_partner_user_id);
            }

            if ($request->filter_type == 1) {
                // ORDERS
                if ($isSalePerson == 1) {

                    $childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
                    $Order->where(function ($query) use ($childSalePersonsIds) {

                        foreach ($childSalePersonsIds as $key => $value) {
                            if ($key == 0) {
                                $query->whereRaw('FIND_IN_SET("' . $value . '",marketing_orders.sale_persons)>0');
                            } else {
                                $query->orWhereRaw('FIND_IN_SET("' . $value . '",marketing_orders.sale_persons)>0');
                            }
                        }
                    });

                    $Order->where('channel_partner.reporting_company_id', Auth::user()->company_id);
                } else if ($isChannelPartner != 0) {

                    $Order->where('marketing_orders.channel_partner_user_id', Auth::user()->id);
                }
            } else if ($request->filter_type == 2) {
                //SALES ORDERS
                $Order->whereIn('marketing_orders.status', array(0, 1, 2, 3));

                if ($isAccountUser == 1 || $isAdminOrCompanyAdmin == 1) {

                    if (Auth::user()->parent_id != 0) {

                        $parent = ChannelPartner::where('user_id', Auth::user()->parent_id)->first();

                        $Order->where('channel_partner.reporting_manager_id', $parent->user_id);
                    } else {

                        $Order->where('channel_partner.reporting_company_id', Auth::user()->company_id);
                        $Order->where('channel_partner.reporting_manager_id', 0);
                    }
                } else if (isChannelPartner(Auth::user()->type) != 0) {

                    $Order->where('channel_partner.reporting_manager_id', Auth::user()->id);
                }
            }

            $Order = $Order->get();

            $headers = array("DATE", "ORDER ID", "ORDER BY", "CHANNEL PARTNER", "EXGST", "STATUS");

            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="reports.csv"');
            $fp = fopen('php://output', 'wb');
            fputcsv($fp, $headers);
            foreach ($Order as $key => $value) {

                //$sale_persons = explode(",", $value['sale_persons']);

                // $Users = User::select('users.first_name', 'users.last_name', 'users.type', 'users.phone_number', 'sales_hierarchy.code as sales_hierarchy_code')->leftJoin('sale_person', 'sale_person.id', '=', 'users.reference_id')->leftJoin('sales_hierarchy', 'sales_hierarchy.id', '=', 'sale_person.type')->whereIn('users.id', $sale_persons)->get();

                // $uiSalePerson = '';
                // foreach ($Users as $kU => $vU) {
                //  $uiSalePerson .= $vU['first_name'] . ' ' . $vU['last_name'] . '|' . $vU['sales_hierarchy_code'] . '|PHONE:' . $vU['phone_number'] . ',';
                // }

                $paymentDetailEXGST = priceLable($value['total_mrp_minus_disocunt']);
                // $paymentDetailTotalPayable = priceLable($value['total_payable']);

                $subStatus = "";

                if ($value['status'] == 1 || $value['status'] == 2) {
                    $subStatus = getInvoiceLable($value['sub_status']);
                }

                $status = getOrderLable($value['status']);
                if ($subStatus != "") {
                    $status = $status . "-" . $subStatus;
                }

                $status = str_replace('<span class="badge badge-pill badge badge-soft-warning font-size-11">', "", $status);
                $status = str_replace('<span class="badge badge-pill badge badge-soft-info font-size-11">', "", $status);
                $status = str_replace('<span class="badge badge-pill badge badge-soft-orange font-size-11">', "", $status);
                $status = str_replace('<span class="badge badge-pill badge badge-soft-success font-size-11">', "", $status);
                $status = str_replace('<span class="badge badge-pill badge badge-soft-danger font-size-11">', "", $status);
                $status = str_replace('<span class="badge badge-pill badge bg-primary font-size-11">', "", $status);
                $status = str_replace('<span class="badge badge-pill badge-soft-info font-size-11">', "", $status);
                $status = str_replace('<span class="badge badge-pill badge-soft-success font-size-11">', "", $status);
                $status = str_replace('<span class="badge badge-pill badge-soft-dark font-size-11">', "", $status);
                $status = str_replace('</span>', "", $status);

                $lineVal = array(

                    convertOrderDateTime($value['created_at'], "date"),
                    $value->id,
                    $value['first_name'] . '  ' . $value['last_name'],
                    $value['firm_name'],
                    $paymentDetailEXGST,
                    $status,

                );

                fputcsv($fp, $lineVal, ",");
            }

            fclose($fp);
        }
    }
}