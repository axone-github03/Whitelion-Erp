<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\ChannelPartner;
use App\Models\ProductInventory;
use App\Models\MainMaster;
use DB;
use App\Models\UserDiscount;
use App\Models\CreditTranscationLog;
use App\Models\BillItems;
use App\Models\Bill;
use Config;
use Mail;
use PDF;
use App\Models\StateList;
use App\Models\CityList;
use App\Models\CountryList;
use App\Models\InvoiceItem;
use App\Models\Invoice;
use PHPUnit\Framework\MockObject\Stub\ReturnReference;

class BillController extends Controller
{
    public function add()
    {
        $data = array();
        $data['type'] = array();
        if (Auth::user()->type == 0 || Auth::user()->type == 1) {

            $data['type'] = getChannelPartners();
        } else if (Auth::user()->type == 2) {
            $data['type'] = getChannelPartners();
        } else if (Auth::user()->type == 13) {
            $data['type'] = getChannelPartners();
        } else {
            $data['type'][] = getChannelPartners()[Auth::user()->type];
        }
        $data['title'] = "Add Bill";
        return view('bill/add', compact('data'));
    }
    public function calculation(Request $request)
    {

        if ($request->expectsJson()) {
            $inputJSON = $request->all();
            $orderItems = array();

            foreach ($inputJSON['order_items'] as $key => $value) {

                $orderItems[$key]['id'] = $value['id'];
                $orderItems[$key]['info']['product_brand'] = $value['product_brand'];
                $orderItems[$key]['info']['product_code'] = $value['product_code'];
                $orderItems[$key]['info']['description'] = $value['description'];
                $orderItems[$key]['info']['image'] = $value['image'];
                $orderItems[$key]['info']['thumb'] = $value['thumb'];

                $orderItems[$key]['mrp'] = $value['price'];
                $orderItems[$key]['qty'] = $value['order_qty'];
                $orderItems[$key]['discount_percentage'] = $value['discount_percentage'];
                $orderItems[$key]['weight'] = $value['weight'];
            }

            $GSTPercentage = GSTPercentage();
            $shippingCost = $inputJSON['shipping_cost'];
            $orderDetail = calculationProcessOfOrder($orderItems, $GSTPercentage, $shippingCost);
            $response = successRes("Bill detail");
            $response['order'] = $orderDetail;

            $query = User::query();
            $query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
            $ChannelPartner = $query->find($inputJSON['channel_partner_user_id']);
            $salePersons = User::select('users.first_name', 'users.last_name')->whereIn('users.id', explode(",", $ChannelPartner->sale_persons))->get();
            $data = $response;
            $data['channel_partner'] = json_decode(json_encode($ChannelPartner), true);
            $data['salePerson'] = json_decode(json_encode($salePersons), true);
            $data['d_country'] = $inputJSON['d_country'];
            $data['d_state'] = $inputJSON['d_state'];
            $data['d_city'] = $inputJSON['d_city'];
            $data['d_address_line1'] = $inputJSON['d_address_line1'];
            $data['d_address_line2'] = $inputJSON['d_address_line2'];
            $data['d_pincode'] = $inputJSON['d_pincode'];

            //$response['data'] = $data;
            $response['preview'] = view('bill/preview', compact('data'))->render();
        } else {

            $response = errorRes("Something went wrong");
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }
    public function searchChannelPartner(Request $request)
    {

        $ChannelPartner = array();
        $ChannelPartner = ChannelPartner::select('channel_partner.user_id as id', DB::raw('CONCAT(channel_partner.firm_name," - ", city_list.name) AS text'));
        if (isset($request->channel_partner_type)) {
            $ChannelPartner->where('channel_partner.type', $request->channel_partner_type);
        }

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

        $response = array();
        $response['results'] = $ChannelPartner;
        $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');
    }
    public function channelPartnerDetail(Request $request)
    {

        $ChannelPartner = ChannelPartner::query();
        $ChannelPartner->where('user_id', $request->channel_partner_user_id);
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

        $ChannelPartner->with(array('d_country' => function ($query) {
            $query->select('id', 'name');
        }, 'd_state' => function ($query) {
            $query->select('id', 'name');
        }, 'd_city' => function ($query) {
            $query->select('id', 'name');
        }));

        $ChannelPartner = $ChannelPartner->first();

        $response = array();
        if ($ChannelPartner) {

            $response = successRes("");
            $response['data'] = $ChannelPartner;
        } else {
            $response = errorRes("Invalid Channel Partner");
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function searchProduct(Request $request)
    {

        $DataMaster = array();
        $PRODUCT_CODE = MainMaster::select('id')->where('code', 'PRODUCT_CODE')->first();
        $PRODUCT_BRAND = MainMaster::select('id')->where('code', 'PRODUCT_BRAND')->first();

        $DataMaster = ProductInventory::select('product_inventory.id', DB::raw('CONCAT(product_brand.name," ",product_code.name," (",product_inventory.description,")" )  as text'));
        $DataMaster->leftJoin('data_master as product_brand', 'product_brand.id', '=', 'product_inventory.product_brand_id');
        $DataMaster->leftJoin('data_master as product_code', 'product_code.id', '=', 'product_inventory.product_code_id');

        $searchValue = $request->q;
        $DataMaster->where('product_inventory.status', 1);
        $DataMaster->where('product_brand.main_master_id', $PRODUCT_BRAND->id);
        $DataMaster->where('product_code.main_master_id', $PRODUCT_CODE->id);

        $searchValuePieces = explode(" ", $searchValue);

        if (count($searchValuePieces) > 1) {

            $DataMaster->where(function ($query) use ($searchValuePieces) {
                $query->where('product_brand.name', 'like', $searchValuePieces[0] . "%");
                $query->Where('product_code.name', 'like', $searchValuePieces[1] . "%");
            });
        } else {

            $DataMaster->where(function ($query) use ($searchValue) {
                $query->where('product_brand.name', 'like', $searchValue . "%");
                // $query->orWhere('product_code.name', 'like', "%" . $searchValue . "%");
            });
        }

        $DataMaster->limit(15);
        $DataMaster = $DataMaster->get();

        $response = array();
        $response['results'] = $DataMaster;
        $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');
    }
    public function productDetail(Request $request)
    {
        $ProductInventory = ProductInventory::with(array('product_brand' => function ($query) {
            $query->select('id', 'name');
        }, 'product_code' => function ($query) {

            $query->select('id', 'name');
        }))->where('id', $request->product_inventory_id)->first();
        $response = array();
        if ($ProductInventory) {
            $UserDiscount = UserDiscount::where('product_inventory_id', $request->product_inventory_id)->where('user_id', $request->channel_partner_user_id)->first();
            $discount_percentage = 0;
            if ($UserDiscount) {

                $discount_percentage = floatval($UserDiscount->discount_percentage);
            }

            $response = successRes("");
            $response['data'] = $ProductInventory;
            $response['data']['discount_percentage'] = $discount_percentage;
        } else {
            $response = errorRes("Invalid Product");
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }
    public function save(Request $request)
    {

        //$response = errorRes("Working...");
        //return response()->json($response)->header('Content-Type', 'application/json');

        $orderItems = array();

        foreach ($request->input_product_id as $key => $value) {

            $productInventory = ProductInventory::find($value);

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

            $UserDiscount = UserDiscount::where('product_inventory_id', $value)->where('user_id', $request->channel_partner_user_id)->first();
            $discountPercentage = 0;
            if ($UserDiscount) {
                $discountPercentage = $UserDiscount->discount_percentage;
            }

            $orderItems[$key]['id'] = $productInventory->id;
            $orderItems[$key]['info'] = "";
            $orderItems[$key]['mrp'] = $productInventory->price;
            $orderItems[$key]['qty'] = $request->input_qty[$key];
            $orderItems[$key]['discount_percentage'] = $discountPercentage;
            $orderItems[$key]['weight'] = $productInventory->weight;
        }

        $isSalePerson = isSalePerson();

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
        $User = User::find($request->channel_partner_user_id);
        if ($ChannelPartner && $User) {

            $shippingCost = floatval($ChannelPartner->shipping_cost);
        } else {
            $response = errorRes("Invalid Channel Partner");
            return response()->json($response)->header('Content-Type', 'application/json');
        }

        $GSTPercentage = GSTPercentage();
        $orderDetail = calculationProcessOfOrder($orderItems, $GSTPercentage, $shippingCost);

        if ($request->verify_payable_total != $orderDetail['total_payable']) {
            $response = errorRes("Something went wrong with price calculation");
            $response['verify_payable_total'] = $request->verify_payable_total;
            $response['order'] = $orderDetail;
            return response()->json($response)->header('Content-Type', 'application/json');
        }

        if ($ChannelPartner->payment_mode == 2) {

            $pendingCredit = $ChannelPartner->pending_credit;
            if ($pendingCredit >= $orderDetail['total_payable']) {

                $transcationCredit = CreditTranscationLog::where('user_id', $ChannelPartner->user_id)->orderBy('id', 'desc')->first();
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

        if ($ChannelPartner->shipping_limit < $orderDetail['delievery_charge']) {

            $response = errorRes("Channel Partner has not shipping limit");
            return response()->json($response)->header('Content-Type', 'application/json');
        }

        $Bill = new Bill();
        $Bill->user_id = Auth::user()->id;
        $Bill->channel_partner_user_id = $ChannelPartner->user_id;
        $Bill->lead_id = 0;
        $Bill->quotation_id = 0;
        $Bill->sale_persons = $ChannelPartner->sale_persons;
        $Bill->payment_mode = $ChannelPartner->payment_mode;
        $Bill->gst_number = $ChannelPartner->gst_number;

        $Bill->d_address_line1 = $request->d_address_line1;
        $Bill->d_address_line2 = isset($request->d_address_line2) ? $request->d_address_line2 : '';
        $Bill->d_pincode = $request->d_pincode;
        $Bill->d_country_id = $request->d_country_id;
        $Bill->d_state_id = $request->d_state_id;
        $Bill->d_city_id = $request->d_city_id;

        $Bill->bill_address_line1 = $User->address_line1;
        $Bill->bill_address_line2 = $User->address_line2;
        $Bill->bill_pincode = $User->pincode;
        $Bill->bill_country_id = $User->country_id;
        $Bill->bill_state_id = $User->state_id;
        $Bill->bill_city_id = $User->city_id;

        $Bill->status = 0;
        $Bill->sub_status = 0;

        $Bill->total_qty = $orderDetail['total_qty'];
        $Bill->total_mrp = $orderDetail['total_mrp'];
        $Bill->total_discount = $orderDetail['total_discount'];
        $Bill->total_mrp_minus_disocunt = $orderDetail['total_mrp_minus_disocunt'];
        $Bill->actual_total_mrp_minus_disocunt = $orderDetail['total_mrp_minus_disocunt'];
        $Bill->gst_percentage = $orderDetail['gst_percentage'];
        $Bill->gst_tax = $orderDetail['gst_tax'];
        $Bill->total_weight = $orderDetail['total_weight'];
        $Bill->shipping_cost = $orderDetail['shipping_cost'];
        $Bill->delievery_charge = $orderDetail['delievery_charge'];

        $Bill->total_payable = $orderDetail['total_payable'];
        $Bill->pending_total_payable = $orderDetail['total_payable'];
        $Bill->remark = isset($request->remark) ? $request->remark : '';
        $Bill->save();

        foreach ($orderDetail['items'] as $key => $value) {
            $BillItems = new BillItems();
            $BillItems->user_id = $Bill->user_id;
            $BillItems->channel_partner_user_id = $Bill->channel_partner_user_id;
            $BillItems->bill_id = $Bill->id;
            $BillItems->product_inventory_id = $value['id'];
            $BillItems->qty = $value['qty'];
            $BillItems->pending_qty = $value['qty'];
            $BillItems->mrp = $value['mrp'];
            $BillItems->total_mrp = $value['total_mrp'];
            $BillItems->discount_percentage = $value['discount_percentage'];
            $BillItems->discount = $value['discount'];
            $BillItems->total_discount = $value['total_discount'];
            $BillItems->mrp_minus_disocunt = $value['mrp_minus_disocunt'];
            $BillItems->weight = $value['weight'];
            $BillItems->total_weight = $value['total_weight'];
            $BillItems->save();
        }

        if ($Bill->id != "" && $Bill->payment_mode == 2) {

            $CreditTranscationLog = new CreditTranscationLog();
            $CreditTranscationLog->user_id = $Bill->channel_partner_user_id;
            $CreditTranscationLog->type = 0;
            $CreditTranscationLog->amount = $remainCredit;
            $CreditTranscationLog->request_amount = $orderDetail['total_payable'];
            $CreditTranscationLog->description = "Bill #" . $Bill->id;
            $CreditTranscationLog->save();
            //
            $ChannelPartner->pending_credit = $remainCredit;
            $ChannelPartner->save();
        }

        // echo '<pre>';
        // print_r($orderDetail['items']);
        // d

        // $ChannelPartnerUser = User::find($ChannelPartner->user_id);

        // if ($ChannelPartnerUser) {

        //     $salesPersonString = array();
        //     $salePersons = User::select('users.first_name', 'users.last_name', 'users.email')->whereIn('users.id', explode(",", $ChannelPartner->sale_persons))->get();

        //     foreach ($salePersons as $keyS => $valueS) {

        //         $salesPersonString[] = $valueS->first_name . " " . $valueS->last_name;
        //     }
        //     $salesPersonString = implode(" , ", $salesPersonString);

        //     foreach ($orderDetail['items'] as $key => $value) {

        //         $productInventory = ProductInventory::find($value['id']);

        //         if ($productInventory->notify_when_order == 1) {

        //             //$orderDetail['items'][$key]['notify_emails'] = array();

        //             if ($productInventory->notify_emails != "") {

        //                 $notify_emails = explode(",", $productInventory->notify_emails);
        //                 foreach ($notify_emails as $keyNE => $valNE) {

        //                     if ($valNE != "") {

        //                         if (filter_var($valNE, FILTER_VALIDATE_EMAIL)) {
        //                             $configrationForNotify = configrationForNotify();

        //                             $ProductInventory = ProductInventory::select('product_inventory.id', DB::raw('CONCAT(product_brand.name," ",product_code.name," (",product_inventory.description,")" )  as text'));
        //                             $ProductInventory->leftJoin('data_master as product_brand', 'product_brand.id', '=', 'product_inventory.product_brand_id');
        //                             $ProductInventory->leftJoin('data_master as product_code', 'product_code.id', '=', 'product_inventory.product_code_id');
        //                             $ProductInventory = $ProductInventory->find($value['id']);

        //                             $params = array();
        //                             $params['from_email'] = $configrationForNotify['from_email'];
        //                             $params['from_name'] = $configrationForNotify['from_name'];
        //                             $params['to_email'] = $valNE;
        //                             $params['to_name'] = $configrationForNotify['to_name'];
        //                             $params['subject'] = "New Bill Placed - #" . $Bill->id;

        //                             $params['order'] = array();
        //                             $params['order']['id'] = $Bill->id;
        //                             $params['order']['company_name'] = $ChannelPartner->firm_name;
        //                             $params['order']['phone_number'] = $ChannelPartnerUser->phone_number;
        //                             $params['order']['first_name'] = $ChannelPartnerUser->first_name;
        //                             $params['order']['last_name'] = $ChannelPartnerUser->last_name;
        //                             $params['order']['address_line1'] = $ChannelPartnerUser->address_line1;
        //                             $params['order']['address_line2'] = $ChannelPartnerUser->address_line2;
        //                             $params['order']['pincode'] = $ChannelPartnerUser->pincode;
        //                             $params['order']['gst_number'] = $ChannelPartner->gst_number;
        //                             $params['order']['city_id'] = $ChannelPartnerUser->city_id;
        //                             $params['order']['state_id'] = $ChannelPartnerUser->state_id;
        //                             $params['order']['country_id'] = $ChannelPartnerUser->country_id;
        //                             $params['order']['sale_persons'] = $salesPersonString;
        //                             $params['order']['type'] = getAllUserTypes()[$ChannelPartner->type]['short_name'];
        //                             $params['order']['gst_number'] = $ChannelPartner->gst_number;
        //                             $params['order']['qty'] = $value['qty'];
        //                             $params['order']['item_name'] = $ProductInventory->product_brand . " " . $ProductInventory->code . " " . $ProductInventory->text . "";

        //                             if (Config::get('app.env') == "local") { // SEND MAIL
        //                                 $params['to_email'] = $configrationForNotify['test_email'];
        //                                 $params['bcc_email'] = $configrationForNotify['test_email_bcc'];
        //                             }



        //                             Mail::send('emails.notify_when_order', ['params' => $params], function ($m) use ($params) {
        //                                 $m->from($params['from_email'], $params['from_name']);
        //                                 $m->to($params['to_email'], $params['to_name'])->subject($params['subject']);
        //                             });
        //                         }
        //                     }
        //                 }
        //             }
        //         }
        //     }

        //     $fileName = 'order-' . $Bill->id . '.pdf';
        //     $filePath = 's/order/' . $fileName;
        //     $this->savePDF($Bill->id, $filePath);

        //     $params = array();

        //     $configrationForNotify = configrationForNotify();
        //     $params['from_email'] = $configrationForNotify['from_email'];
        //     $params['from_name'] = $configrationForNotify['from_name'];
        //     $params['bcc_email'] = array("poonam@whitelion.in");
        //     $params['to_email'] = $ChannelPartnerUser->email;
        //     $params['to_name'] = $configrationForNotify['to_name'];
        //     $params['user_name'] = $ChannelPartnerUser->first_name . ' '  . $ChannelPartnerUser->last_name;
        //     $params['firm_name'] = $ChannelPartner->firm_name;
        //     $params['subject'] = "New Bill Placed";
        //     $params['file_path'] = $filePath;
        //     $params['file_name'] = $fileName;
        //     $params['id'] = $Bill->id;
        //     $params['order_by'] = Auth::user()->first_name . " " . Auth::user()->last_name;
        //     $params['order_date'] = convertDateTime($Bill->created_at);
        //     $params['order_amount'] = $Bill->total_mrp_minus_disocunt;
        //     if (Config::get('app.env') == "local") { // SEND MAIL
        //         $params['to_email'] = $configrationForNotify['test_email'];
        //         $params['bcc_email'] = $configrationForNotify['test_email_bcc'];
        //     }
        //     // TEMPLATE 14
        //     Mail::send('emails.order_channel_partner', ['params' => $params], function ($m) use ($params) {
        //         $m->from($params['from_email'], $params['from_name']);
        //         $m->bcc($params['bcc_email']);
        //         $m->to($params['to_email'], $params['to_name'])->subject($params['subject']);
        //         $m->attach(public_path($params['file_path']), array(
        //             'as' => $params['file_name']
        //         ));
        //     });

        //     $salesPersonsList = explode(",", $ChannelPartner->sale_persons);

        //     $totalUsers = array();

        //     foreach ($salesPersonsList as $ks => $vs) {

        //         if ($vs != "") {


        //             $notificationUserids = getParentSalePersonsIds($vs);
        //             $notificationUserids[] = $vs;
        //             $totalUsers = array_merge($totalUsers, $notificationUserids);
        //         }
        //     }

        //     $totalUsers[] = $ChannelPartner->user_id;
        //     if (count($totalUsers) > 0) {

        //         $totalUsers = array_unique($totalUsers);
        //         $totalUsers = array_values($totalUsers);
        //         $UsersNotificationTokens = UsersNotificationTokens($notificationUserids);
        //         $mobileNotificationTitle = "New Bill Place";
        //         //$mobileNotificationMessage = "New Bill Places " . $Bill->id . " By " . $params['order_by'];
        //         $mobileNotificationMessage = "New Bill Places #" . $Bill->id . " " . $ChannelPartner->firm_name . "  By " . $params['order_by'];;
        //         sendNotificationTOAndroid($mobileNotificationTitle, $mobileNotificationMessage, $UsersNotificationTokens, 'Bill', $Bill);

        //         $parent_SalesUser = User::select('first_name', 'last_name', 'email')->whereIn('id', $notificationUserids)->orWhere('id', 1)->get();
        //         foreach ($parent_SalesUser as $keyS => $valueS) {

        //             $params['to_email'] = $valueS->email;
        //             $params['to_name'] = $valueS->first_name . ' ' . $valueS->first_name;
        //             $params['subject'] = "New Bill Placed";
        //             $params['id'] = $Bill->id;
        //             if (Config::get('app.env') == "local") { // SEND MAIL
        //                 $params['to_email'] = $configrationForNotify['test_email'];
        //                 $params['bcc_email'] = $configrationForNotify['test_email_bcc'];
        //             }
        //             // TEMPLATE 15
        //             Mail::send('emails.order_sales_person', ['params' => $params], function ($m) use ($params) {
        //                 $m->from($params['from_email'], $params['from_name']);
        //                 $m->to($params['to_email'], $params['to_name'])->subject($params['subject']);
        //                 $m->attach(public_path($params['file_path']), array(
        //                     'as' => $params['file_name']
        //                 ));
        //             });
        //         }
        //     }

        //     // if ($ChannelPartnerUser->fcm_token != "") {

        //     // 	$mobileNotificationTitle = "New Bill Place";
        //     // 	$mobileNotificationMessage="New Bill Places ".$Bill->id." By ".$params['order_by'] ;
        //     // 	sendNotificationTOAndroid($mobileNotificationTitle, $mobileNotificationMessage, array($ChannelPartnerUser->fcm_token));

        //     // }

        //     // foreach ($salesPersonsList as $keyS => $valueS) {

        //     // 	$params['from_email'] = $fromEmailDetail['email'];
        //     // 	$params['from_name'] = $fromEmailDetail['name'];
        //     // 	$params['to_email'] = $valueS->email;
        //     // 	$params['to_name'] = $fromEmailDetail['name'];
        //     // 	$params['subject'] = "New Bill Placed - #" . $Bill->id;
        //     // 	$params['file_path'] = $filePath;
        //     // 	$params['file_name'] = $fileName;
        //     // 	$params['id'] = $Bill->id;

        //     // 	Mail::send('emails.order_sales_person', ['params' => $params], function ($m) use ($params) {
        //     // 		$m->from($params['from_email'], $params['from_name']);
        //     // 		$m->to($params['to_email'], $params['to_name'])->subject($params['subject']);
        //     // 		$m->attach(public_path($params['file_path']), array(
        //     // 			'as' => $params['file_name']));
        //     // 	});

        //     // }

        //     if (is_file($filePath)) {
        //         unlink($filePath);
        //     }
        // }

        $response = successRes("Successfully generated Bill #" . $Bill->id);
        return response()->json($response)->header('Content-Type', 'application/json');
    }
    public function cancel(Request $request)
    {

        if (isAdmin() == 1 || isCompanyAdmin() == 1 || isCreUser() == 1) {

            $Bill = Bill::find($request->id);
            if ($Bill->status == 0) {
                $Bill->status = 4;
                $Bill->is_cancelled = 1;
                $Bill->cancelled_total_qty = $Bill->total_qty;
                $Bill->actual_total_mrp_minus_disocunt = 0;
                $Bill->save();

                $OrederItems = BillItems::where('bill_id', $Bill->id)->get();
                foreach ($OrederItems as $key => $value) {
                    $BillItems = BillItems::find($value->id);
                    $BillItems->cancelled_qty = $BillItems->qty;
                    $BillItems->save();
                }
                $response = successRes("Successfully mark as cancelled");
            } else {
                $response = errorRes("Only Placed order can cancel");
            }
        } else {
            $response = errorRes("Invalid access");
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }
    public function createdSave(Request $request)
    {

        if (Auth::user()->type == 0) {

            $Bill = Bill::find($request->order_date_time_id);
            $startDate = date('Y-m-d H:i:s', strtotime($request->order_date_time));
            $startDate = date('Y-m-d H:i:s', strtotime($startDate . " -5 hours"));
            $startDate = date('Y-m-d H:i:s', strtotime($startDate . " -30 minutes"));
            $Bill->created_at = $startDate;
            $Bill->save();
            return redirect()->back()->with('success', 'Successfully Updated Created Date & Time');
        } else {
            return redirect()->back()->with('error', 'Invalid Access');
        }
    }
    function savePDF($orderID, $fileName)
    {

        $Bill = Bill::query();
        $Bill->select('bill.id', 'bill.created_at', 'bill.gst_number', 'bill.payment_mode', 'bill.sale_persons', 'bill.total_mrp', 'bill.total_mrp_minus_disocunt', 'bill.total_mrp', 'bill.gst_percentage', 'bill.gst_tax', 'bill.delievery_charge', 'bill.total_payable', 'bill.pending_total_payable', 'bill.bill_address_line1', 'bill.bill_address_line2', 'bill.bill_pincode', 'bill.bill_state_id', 'bill.bill_city_id', 'bill.bill_country_id', 'bill.d_address_line1', 'bill.d_address_line2', 'bill.d_pincode', 'bill.d_state_id', 'bill.d_city_id', 'bill.d_country_id', 'channel_partner_detail.email as channel_partner_email', 'channel_partner_detail.phone_number as channel_partner_phone_number', 'channel_partner_detail.dialing_code as channel_partner_dialing_code', 'channel_partner_detail.first_name as channel_partner_first_name', 'channel_partner_detail.last_name as channel_partner_last_name', 'channel_partner.firm_name as channel_partner_firm_name', 'channel_partner.type as channel_partner_type', 'channel_partner.credit_limit as channel_partner_credit_limit', 'channel_partner.credit_days as channel_partner_credit_days', 'channel_partner.pending_credit as channel_partner_pending_credit', 'channel_partner.credit_days as channel_partner_credit_days');

        $Bill->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'bill.channel_partner_user_id');
        $Bill->leftJoin('users as channel_partner_detail', 'channel_partner_detail.id', '=', 'channel_partner.user_id');
        $Bill->where('bill.id', $orderID);
        $Bill = $Bill->first();

        if ($Bill) {

            $salePersons = explode(",", $Bill['sale_persons']);
            $salePersons = User::select('first_name', 'last_name')->whereIn('id', $salePersons)->get();

            $salePersonsD = array();

            foreach ($salePersons as $keyS => $valueS) {

                $salePersonsD[$keyS] = $valueS['first_name'] . " " . $valueS['last_name'];
            }

            $Bill['sale_persons'] = implode(", ", $salePersonsD);

            $BCityList = CityList::find($Bill['bill_city_id']);
            $Bill['bill_city_name'] = "";
            if ($BCityList) {
                $Bill['bill_city_name'] = $BCityList->name;
            }

            $BStateList = StateList::find($Bill['bill_state_id']);
            $Bill['bill_state_name'] = "";
            if ($BStateList) {
                $Bill['bill_state_name'] = $BStateList->name;
            }

            $BCountryList = CountryList::find($Bill['bill_country_id']);
            $Bill['bill_country_name'] = "";
            if ($BCountryList) {
                $Bill['bill_country_name'] = $BCountryList->name;
            }

            $DCityList = CityList::find($Bill['d_city_id']);
            $Bill['d_city_name'] = "";
            if ($DCityList) {
                $Bill['d_city_name'] = $DCityList->name;
            }

            $DStateList = StateList::find($Bill['d_state_id']);
            $Bill['d_state_name'] = "";
            if ($DStateList) {
                $Bill['d_state_name'] = $DStateList->name;
            }

            $DCountryList = CountryList::find($Bill['d_country_id']);
            $Bill['d_country_name'] = "";
            if ($DCountryList) {
                $Bill['d_country_name'] = $DCountryList->name;
            }

            $Bill['payment_mode_lable'] = getPaymentModeName($Bill['payment_mode']);

            $Bill['channel_partner_type_name'] = getUserTypeName($Bill['channel_partner_type']);
            $Bill['display_date_time'] = convertOrderDateTime($Bill->created_at, 'date');

            $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();

            $BillItems = BillItems::query();

            // if ($isAdminOrCompanyAdmin == 1) {

            // 	$BillItems->select('bill_items.id', 'bill_items.qty', 'bill_items.total_mrp', 'bill_items.pending_qty', 'bill_items.product_inventory_id', 'product_inventory.image as product_image', 'product_brand.name as product_brand_name', 'product_code.name as product_code_name', 'product_inventory.quantity as product_stock');
            // } else {
            $BillItems->select('bill_items.id', 'bill_items.qty', 'bill_items.total_mrp', 'bill_items.mrp', 'bill_items.discount_percentage', 'bill_items.mrp_minus_disocunt', 'bill_items.pending_qty', 'bill_items.product_inventory_id', 'product_inventory.thumb as product_image', 'product_brand.name as product_brand_name', 'product_code.name as product_code_name');

            // }

            $BillItems->leftJoin('product_inventory', 'product_inventory.id', '=', 'bill_items.product_inventory_id');
            $BillItems->leftJoin('data_master as product_brand', 'product_brand.id', '=', 'product_inventory.product_brand_id');
            $BillItems->leftJoin('data_master as product_code', 'product_code.id', '=', 'product_inventory.product_code_id');

            $BillItems->where('bill_id', $Bill->id);
            $BillItems->orderBy('id', 'desc');
            $BillItems = $BillItems->get();

            $Bill['items'] = $BillItems;

            $data = $Bill;

            // echo '<pre>';
            // print_r($data);
            // die;
            $pdf = Pdf::loadView('bill.pdf', compact('data'));
            $pdf->save($fileName);
            return "";
        } else {
            $response = errorRes("Invalid order id");
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }
    public function index()
    {
        $data = array();
        $data['title'] = "Bill ";

        $ChannelPartner = array();
        $ChannelPartner = ChannelPartner::select('channel_partner.user_id as id', DB::raw('CONCAT(channel_partner.firm_name," - ", city_list.name) AS text'));
        $ChannelPartner->where('channel_partner.type', 102);
        $ChannelPartner->leftJoin('city_list', 'city_list.id', '=', 'channel_partner.d_city_id');
        $ChannelPartner->leftJoin('users', 'users.id', '=', 'channel_partner.user_id');
        $ChannelPartner = $ChannelPartner->get();
        $data['channel_partner'] = $ChannelPartner;

        $PRODUCT_CODE = MainMaster::select('id')->where('code', 'PRODUCT_CODE')->first();
        $PRODUCT_BRAND = MainMaster::select('id')->where('code', 'PRODUCT_BRAND')->first();
        $DataMaster = ProductInventory::select('product_inventory.id', DB::raw('CONCAT(product_brand.name," ",product_code.name," (",product_inventory.description,")" )  as text'));
        $DataMaster->leftJoin('data_master as product_brand', 'product_brand.id', '=', 'product_inventory.product_brand_id');
        $DataMaster->leftJoin('data_master as product_code', 'product_code.id', '=', 'product_inventory.product_code_id');
        $DataMaster->where('product_brand.main_master_id', $PRODUCT_BRAND->id);
        $DataMaster->where('product_code.main_master_id', $PRODUCT_CODE->id);
        $DataMaster->where('product_brand.id', 41);
        $DataMaster = $DataMaster->get();
        $data['product'] = $DataMaster;

        return view('bill/index', compact('data'));
    }
    function ajax(Request $request)
    {

        $isSalePerson = isSalePerson();
        $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
        $isCreUser = isCreUser();

        $searchColumns = array(
            0 => 'bill.id',
            1 => 'users.first_name',
            2 => 'users.last_name',
            3 => 'channel_partner.firm_name',

        );

        $sortingColumns = array(
            0 => 'bill.id',
            1 => 'bill.user_id',
            2 => 'bill.channel_partner_user_id',
            3 => 'bill.sale_persons',
            4 => 'bill.payment_mode',
            5 => 'bill.status',

        );

        $selectColumns = array(
            0 => 'bill.id',
            1 => 'bill.user_id',
            2 => 'bill.channel_partner_user_id',
            3 => 'bill.sale_persons',
            4 => 'bill.payment_mode',
            5 => 'bill.status',
            6 => 'bill.created_at',
            7 => 'users.first_name as first_name',
            8 => 'users.last_name as last_name',
            9 => 'channel_partner.firm_name',
            10 => 'bill.payment_mode',
            11 => 'bill.total_mrp_minus_disocunt',
            12 => 'bill.total_payable',
            13 => 'bill.pending_total_payable',
            14 => 'bill.sub_status',
            15 => 'bill.invoice',
            16 => 'channel_partner.type as channel_partner_type',
            17 => 'channel_partner_user.first_name as channel_partner_user_first_name',
            18 => 'channel_partner_user.last_name as channel_partner_user_last_name',
            19 => 'channel_partner_user.phone_number as channel_partner_user_phone_number',
            20 => 'bill.is_cancelled',


        );

        $recordsTotal = Bill::query();
        $recordsTotal->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'bill.channel_partner_user_id');

        if ($isSalePerson == 1) {

            $childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
            $recordsTotal->where(function ($query) use ($childSalePersonsIds) {

                foreach ($childSalePersonsIds as $key => $value) {
                    if ($key == 0) {
                        $query->whereRaw('FIND_IN_SET("' . $value . '",bill.sale_persons)>0');
                    } else {
                        $query->orWhereRaw('FIND_IN_SET("' . $value . '",bill.sale_persons)>0');
                    }
                }
            });

            // foreach ($childSalePersonsIds as $key => $value) {
            // 	if ($key == 0) {
            // 		$recordsTotal->whereRaw('FIND_IN_SET("' . $value . '",bill.sale_persons)>0');
            // 	} else {
            // 		$recordsTotal->orWhereRaw('FIND_IN_SET("' . $value . '",bill.sale_persons)>0');
            // 	}

            // }

            $recordsTotal->where('channel_partner.reporting_company_id', Auth::user()->company_id);
        } else if (isChannelPartner(Auth::user()->type) != 0) {

            $recordsTotal->where('bill.channel_partner_user_id', Auth::user()->id);
        }
        $recordsTotal = $recordsTotal->count();
        //$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.

        $query = Bill::query();
        $query->leftJoin('users', 'users.id', '=', 'bill.user_id');
        $query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'bill.channel_partner_user_id');
        $query->leftJoin('users as channel_partner_user', 'channel_partner_user.id', '=', 'bill.channel_partner_user_id');

        if ($isSalePerson == 1) {

            $childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
            $query->where(function ($query2) use ($childSalePersonsIds) {

                foreach ($childSalePersonsIds as $key => $value) {
                    if ($key == 0) {
                        $query2->whereRaw('FIND_IN_SET("' . $value . '",bill.sale_persons)>0');
                    } else {
                        $query2->orWhereRaw('FIND_IN_SET("' . $value . '",bill.sale_persons)>0');
                    }
                }
            });

            $query->where('channel_partner.reporting_company_id', Auth::user()->company_id);
        } else if (isChannelPartner(Auth::user()->type) != 0) {
            $query->where('bill.channel_partner_user_id', Auth::user()->id);
        }
        $query->select('bill.id');
        // $query->limit($request->length);
        // $query->offset($request->start);
        $query->orderBy($sortingColumns[$request['order'][0]['column']], $request['order'][0]['dir']);

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

        $recordsFiltered = $query->count();

        $query = Bill::query();
        $query->leftJoin('users', 'users.id', '=', 'bill.user_id');
        $query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'bill.channel_partner_user_id');
        $query->leftJoin('users as channel_partner_user', 'channel_partner_user.id', '=', 'bill.channel_partner_user_id');

        if ($isSalePerson == 1) {

            $childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
            $query->where(function ($query2) use ($childSalePersonsIds) {

                foreach ($childSalePersonsIds as $key => $value) {
                    if ($key == 0) {
                        $query2->whereRaw('FIND_IN_SET("' . $value . '",bill.sale_persons)>0');
                    } else {
                        $query2->orWhereRaw('FIND_IN_SET("' . $value . '",bill.sale_persons)>0');
                    }
                }
            });

            $query->where('channel_partner.reporting_company_id', Auth::user()->company_id);
        } else if (isChannelPartner(Auth::user()->type) != 0) {
            $query->where('bill.channel_partner_user_id', Auth::user()->id);
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

        $channelPartner = getChannelPartners();

        foreach ($data as $key => $value) {


            if (Auth::user()->type == 0) {



                $data[$key]['detail'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">#' . highlightString($value['id'], $search_value) . '</a></h5>
                <p class="text-muted mb-0" data-bs-toggle="tooltip"  data-bs-original-title="' . convertOrderDateTime($value['created_at'], "time") . '" ><a href="javascript:void(0)"  onclick="changeOrderDate(\'' . $value['id'] . '\')" >' . highlightString(convertOrderDateTime($value['created_at'], "date"), $search_value) . '</a></p>';
            } else {

                $data[$key]['detail'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">#' . highlightString($value['id'], $search_value) . '</a></h5>
                <p class="text-muted mb-0" data-bs-toggle="tooltip"  data-bs-original-title="' . convertOrderDateTime($value['created_at'], "time") . '" >' . highlightString(convertOrderDateTime($value['created_at'], "date"), $search_value) . '</p>';
            }


            $paymentMode = "";

            $paymentMode = getPaymentLable($value['payment_mode']);
            $channelPartnerType = '<span class="badge rounded-pill badge-soft-dark font-size-11">' . $channelPartner[$value['channel_partner_type']]['short_name'] . '</span>';

            $data[$key]['order_by'] = '<p class="text-muted mb-0" data-bs-toggle="tooltip" title="' . $value['first_name'] . '  ' . $value['last_name'] . '">' . highlightString(displayStringLenth($value['first_name'] . '  ' . $value['last_name'], 10), $search_value) . '</p>';

            $data[$key]['channel_partner'] = '<p class="text-muted mb-0 text-center"
			data-bs-toggle="tooltip" title="' . $value['channel_partner_user_first_name'] . ' ' . $value['channel_partner_user_last_name'] . '&#013;&#013; PHONE:' . $value['channel_partner_user_phone_number'] . '" >' . highlightString(displayStringLenth($value['firm_name'], 15), $search_value) . '</p><p class="text-muted mb-0 text-center">' . $channelPartnerType . " " . $paymentMode . '</p>';

            $sale_persons = explode(",", $value['sale_persons']);
            $Users = User::select('users.first_name', 'users.last_name', 'users.type', 'users.phone_number', 'sales_hierarchy.code as sales_hierarchy_code')->leftJoin('sale_person', 'sale_person.id', '=', 'users.reference_id')->leftJoin('sales_hierarchy', 'sales_hierarchy.id', '=', 'sale_person.type')->whereIn('users.id', $sale_persons)->get();

            $uiSalePerson = '<ul class="list-inline font-size-20 contact-links mb-0">';
            foreach ($Users as $kU => $vU) {
                $uiSalePerson .= '<li class="list-inline-item px-2">';
                $uiSalePerson .= '<a  data-bs-toggle="tooltip" title="' . $vU['first_name'] . ' ' . $vU['last_name'] . '&#013;' . $vU['sales_hierarchy_code'] . '&#013; PHONE:' . $vU['phone_number'] . '" href="javascript: void(0);" ><i class="bx bx-user"></i></a>';
                $uiSalePerson .= '</li>';
            }

            $uiSalePerson .= '</ul>';

            $data[$key]['sale_persons'] = $uiSalePerson;

            $data[$key]['payment_detail'] = '<p class="text-muted mb-0">EXGST&nbsp&nbsp;&nbsp;&nbsp;: <i class="fas fa-rupee-sign"></i> <span class="price-lable-font">' . highlightString(priceLable($value['total_mrp_minus_disocunt']), $search_value) . '</span></p>

			<p class="text-muted mb-0 ">TOTAL&nbsp;&nbsp;&nbsp;&nbsp: <i class="fas fa-rupee-sign"></i> <span class="price-lable-font">' . highlightString(priceLable($value['total_payable']), $search_value) . '</span></p>
			   ';

            $data[$key]['sub_status'] = "";



            $data[$key]['status'] = getOrderLable($value['status']);


            if ($value['status'] != 4 && $value['is_cancelled'] == 1) {
                $data[$key]['status'] = $data[$key]['status'] . "-" . '<span class="badge badge-pill badge badge-soft-danger font-size-11">PARTIALLY CANCELLED</span>';
            } else {

                if ($value['status'] == 1 || $value['status'] == 2) {
                    $data[$key]['sub_status'] = getInvoiceLable($value['sub_status']);
                }


                if ($data[$key]['sub_status'] != "") {
                    $data[$key]['status'] = $data[$key]['status'] . "-" . $data[$key]['sub_status'];
                }
            }



            $uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';



            $uiAction .= '<li class="list-inline-item px-2">';
            $uiAction .= '<a onclick="ViewOrder(\'' . $value['id'] . '\')" href="javascript: void(0);" title="View"><i class="mdi mdi-eye"></i></a>';
            $uiAction .= '</li>';

            if (($value['status'] == 0 && $value['is_cancelled'] == 0) && $isAdminOrCompanyAdmin || $isCreUser) {

                $uiAction .= '<li class="list-inline-item px-2">';
                $uiAction .= '<a onclick="CancelOrder(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Cancel"><i class="mdi mdi-close-circle-outline"></i></a>';
                $uiAction .= '</li>';
            }


            if ($value['invoice'] != "") {

                $routeInvoice = route('bill.invoice.list') . "?order_id=" . $value['id'];
                if (isCreUser() == 0) {
                    $uiAction .= '<li class="list-inline-item px-2">';
                    $uiAction .= '<a target="_blank"  href="' . $routeInvoice . '" title="Invoice"><i class="bx bx-receipt"></i></a>';
                    $uiAction .= '</li>';
                }
            }

            $uiAction .= '</ul>';
            $data[$key]['action'] = $uiAction;
        }

        $jsonData = array(
            "draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($recordsTotal), // total number of records
            "recordsFiltered" => intval($recordsFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $data, // total data array

        );
        return $jsonData;
    }
    public function detail(Request $request)
    {
        $Bill = Bill::query();
        $Bill->select('bill.id', 'bill.created_at', 'bill.gst_number', 'bill.payment_mode', 'bill.sale_persons', 'bill.total_mrp', 'bill.total_mrp_minus_disocunt', 'bill.total_mrp', 'bill.gst_percentage', 'bill.gst_tax', 'bill.delievery_charge', 'bill.total_payable', 'bill.pending_total_payable', 'bill.bill_address_line1', 'bill.bill_address_line2', 'bill.bill_pincode', 'bill.bill_state_id', 'bill.bill_city_id', 'bill.bill_country_id', 'bill.d_address_line1', 'bill.d_address_line2', 'bill.d_pincode', 'bill.d_state_id', 'bill.d_city_id', 'bill.d_country_id', 'bill.remark', 'channel_partner_detail.email as channel_partner_email', 'channel_partner_detail.phone_number as channel_partner_phone_number', 'channel_partner_detail.dialing_code as channel_partner_dialing_code', 'channel_partner_detail.first_name as channel_partner_first_name', 'channel_partner_detail.last_name as channel_partner_last_name', 'channel_partner.firm_name as channel_partner_firm_name', 'channel_partner.type as channel_partner_type', 'channel_partner.credit_limit as channel_partner_credit_limit', 'channel_partner.credit_days as channel_partner_credit_days', 'channel_partner.pending_credit as channel_partner_pending_credit', 'channel_partner.credit_days as channel_partner_credit_days');

        $Bill->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'bill.channel_partner_user_id');
        $Bill->leftJoin('users as channel_partner_detail', 'channel_partner_detail.id', '=', 'channel_partner.user_id');
        $Bill->where('bill.id', $request->order_id);
        $Bill = $Bill->first();
        // return $Bill;

        if ($Bill) {

            $salePersons = explode(",", $Bill['sale_persons']);
            $salePersons = User::select('first_name', 'last_name')->whereIn('id', $salePersons)->get();

            $salePersonsD = array();

            foreach ($salePersons as $keyS => $valueS) {

                $salePersonsD[$keyS] = $valueS['first_name'] . " " . $valueS['last_name'];
            }

            $Bill['sale_persons'] = implode(", ", $salePersonsD);

            $BCityList = CityList::find($Bill['bill_city_id']);
            $Bill['bill_city_name'] = "";
            if ($BCityList) {
                $Bill['bill_city_name'] = $BCityList->name;
            }

            $BStateList = StateList::find($Bill['bill_state_id']);
            $Bill['bill_state_name'] = "";
            if ($BStateList) {
                $Bill['bill_state_name'] = $BStateList->name;
            }

            $BCountryList = CountryList::find($Bill['bill_country_id']);
            $Bill['bill_country_name'] = "";
            if ($BCountryList) {
                $Bill['bill_country_name'] = $BCountryList->name;
            }

            $DCityList = CityList::find($Bill['d_city_id']);
            $Bill['d_city_name'] = "";
            if ($DCityList) {
                $Bill['d_city_name'] = $DCityList->name;
            }

            $DStateList = StateList::find($Bill['d_state_id']);
            $Bill['d_state_name'] = "";
            if ($DStateList) {
                $Bill['d_state_name'] = $DStateList->name;
            }

            $DCountryList = CountryList::find($Bill['d_country_id']);
            $Bill['d_country_name'] = "";
            if ($DCountryList) {
                $Bill['d_country_name'] = $DCountryList->name;
            }

            $Bill['payment_mode_lable'] = getPaymentModeName($Bill['payment_mode']);

            $Bill['channel_partner_type_name'] = getUserTypeName($Bill['channel_partner_type']);
            $Bill['display_date_time'] = convertOrderDateTime($Bill->created_at, 'date');

            $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();

            $BillItem = BillItems::query();

            if ($isAdminOrCompanyAdmin == 1) {

                $BillItem->select('bill_items.id', 'bill_items.qty', 'bill_items.total_mrp', 'bill_items.pending_qty', 'bill_items.product_inventory_id', 'product_inventory.thumb as product_image', 'product_brand.name as product_brand_name', 'product_code.name as product_code_name', 'product_inventory.quantity as product_stock');
            } else {
                $BillItem->select('bill_items.id', 'bill_items.qty', 'bill_items.total_mrp', 'bill_items.pending_qty', 'bill_items.product_inventory_id', 'product_inventory.thumb as product_image', 'product_brand.name as product_brand_name', 'product_code.name as product_code_name');
            }

            $BillItem->leftJoin('product_inventory', 'product_inventory.id', '=', 'bill_items.product_inventory_id');
            $BillItem->leftJoin('data_master as product_brand', 'product_brand.id', '=', 'product_inventory.product_brand_id');
            $BillItem->leftJoin('data_master as product_code', 'product_code.id', '=', 'product_inventory.product_code_id');

            $BillItem->where('bill_id', $Bill->id);
            $BillItem->orderBy('id', 'desc');
            $BillItem = $BillItem->get();

            // foreach ($BillItem as $OK => $OV) {

            // 	$BillItem->product_image

            // }

            $Bill['items'] = $BillItem;
            // foreach ($Bill['items'] as $it => $vit) {

            // 	$path = getSpaceFilePath($Bill['items'][$it]['product_image']);
            // 	$type = pathinfo($path, PATHINFO_EXTENSION);
            // 	$data = file_get_contents($path);
            // 	$base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            // 	$Bill['items'][$it]['product_image'] = $base64;

            // }

            $response = successRes("Bill detail");

            $response['data'] = $Bill;
            $response['data']['created_at_timestamp'] = strtotime($Bill['created_at']);
        } else {
            $response = errorRes("Invalid Bill id");
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function invoiceList(Request $request)
    {

        $Bill = Bill::select('id')->find($request->order_id);
        if ($Bill) {
            $data['title'] = "Invoice";
            $data['order_id'] = $Bill->id;
            return view('bill/invoice', compact('data'));
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
            1 => 'bill.user_id',
            2 => 'bill.channel_partner_user_id',
            3 => 'bill.sale_persons',
            4 => 'bill.payment_mode',
            5 => 'invoice.status',

        );

        $selectColumns = array(
            0 => 'invoice.id',
            1 => 'bill.user_id',
            2 => 'bill.channel_partner_user_id',
            3 => 'bill.sale_persons',
            4 => 'bill.payment_mode',
            5 => 'invoice.status',
            6 => 'invoice.created_at',
            7 => 'users.first_name as first_name',
            8 => 'users.last_name as last_name',
            9 => 'channel_partner.firm_name',
            10 => 'bill.payment_mode',
            11 => 'invoice.gst_tax',
            12 => 'invoice.total_payable',
            13 => 'invoice.invoice_file',
            14 => 'invoice.invoice_number',
            15 => 'invoice.total_mrp_minus_disocunt',

        );

        $isSalePerson = isSalePerson();

        $recordsTotal = Invoice::query();
        $recordsTotal->leftJoin('bill', 'invoice.order_id', '=', 'bill.id');
        $recordsTotal->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'bill.channel_partner_user_id');
        $recordsTotal->where('channel_partner.reporting_company_id', Auth::user()->company_id);
        $recordsTotal->where('invoice.order_id', $request->order_id);

        if ($isSalePerson == 1) {
            $childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);

            $recordsTotal->where(function ($query) use ($childSalePersonsIds) {

                foreach ($childSalePersonsIds as $key => $value) {
                    if ($key == 0) {
                        $query->whereRaw('FIND_IN_SET("' . $value . '",bill.sale_persons)>0');
                    } else {
                        $query->orWhereRaw('FIND_IN_SET("' . $value . '",bill.sale_persons)>0');
                    }
                }
            });

            // foreach ($childSalePersonsIds as $key => $value) {
            // 	if ($key == 0) {
            // 		$recordsTotal->whereRaw('FIND_IN_SET("' . $value . '",bill.sale_persons)>0');
            // 	} else {
            // 		$recordsTotal->orWhereRaw('FIND_IN_SET("' . $value . '",bill.sale_persons)>0');
            // 	}

            // }

        } else if (isChannelPartner(Auth::user()->type) != 0) {

            $recordsTotal->where('bill.channel_partner_user_id', Auth::user()->id);
        }

        $recordsTotal = $recordsTotal->count();

        $recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.
        $query = Invoice::query();
        $query->leftJoin('bill', 'invoice.order_id', '=', 'bill.id');
        $query->leftJoin('users', 'users.id', '=', 'bill.user_id');
        $query->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'bill.channel_partner_user_id');
        $query->where('invoice.order_id', $request->order_id);

        if ($isSalePerson == 1) {

            $childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);

            $query->where(function ($query2) use ($childSalePersonsIds) {

                foreach ($childSalePersonsIds as $key => $value) {
                    if ($key == 0) {
                        $query2->whereRaw('FIND_IN_SET("' . $value . '",bill.sale_persons)>0');
                    } else {
                        $query2->orWhereRaw('FIND_IN_SET("' . $value . '",bill.sale_persons)>0');
                    }
                }
            });
            // foreach ($childSalePersonsIds as $key => $value) {
            // 	if ($key == 0) {
            // 		$query->whereRaw('FIND_IN_SET("' . $value . '",bill.sale_persons)>0');
            // 	} else {
            // 		$query->orWhereRaw('FIND_IN_SET("' . $value . '",bill.sale_persons)>0');
            // 	}

            // }

        } else if (isChannelPartner(Auth::user()->type) != 0) {

            $query->where('bill.channel_partner_user_id', Auth::user()->id);
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

            $data[$key]['payment_detail'] = '<p class="text-muted mb-0">GST&nbsp;&nbsp;&nbsp;&nbsp;&nbsp&nbsp;&nbsp;&nbsp;: <i class="fas fa-rupee-sign"></i> <span class="price-lable-font">' . priceLable($value['total_mrp_minus_disocunt']) . '</span></p>

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
            "draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($recordsTotal), // total number of records
            "recordsFiltered" => intval($recordsFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $data, // total data array

        );
        return $jsonData;
    }

    public function invoiceDetail(Request $request)
    {

        $isSalePerson = isSalePerson();

        $Bill = Invoice::query();
        $Bill->select('invoice.id', 'invoice.dispatch_detail', 'invoice.eway_bill', 'invoice.created_at', 'bill.gst_number', 'bill.payment_mode', 'bill.sale_persons', 'invoice.total_mrp_minus_disocunt', 'invoice.total_mrp', 'invoice.gst_tax', 'invoice.gst_percentage', 'invoice.delievery_charge', 'invoice.total_payable', 'bill.bill_address_line1', 'bill.bill_address_line2', 'bill.bill_pincode', 'bill.bill_state_id', 'bill.bill_city_id', 'bill.bill_country_id', 'bill.d_address_line1', 'bill.d_address_line2', 'bill.d_pincode', 'bill.d_state_id', 'bill.d_city_id', 'bill.d_country_id', 'channel_partner_detail.email as channel_partner_email', 'channel_partner_detail.phone_number as channel_partner_phone_number', 'channel_partner_detail.dialing_code as channel_partner_dialing_code', 'channel_partner_detail.first_name as channel_partner_first_name', 'channel_partner_detail.last_name as channel_partner_last_name', 'channel_partner.firm_name as channel_partner_firm_name', 'channel_partner.type as channel_partner_type', 'channel_partner.credit_limit as channel_partner_credit_limit', 'channel_partner.credit_days as channel_partner_credit_days', 'channel_partner.pending_credit as channel_partner_pending_credit', 'channel_partner.credit_days as channel_partner_credit_days');
        $Bill->leftJoin('bill', 'invoice.order_id', '=', 'bill.id');
        $Bill->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'bill.channel_partner_user_id');
        $Bill->leftJoin('users as channel_partner_detail', 'channel_partner_detail.id', '=', 'channel_partner.user_id');
        $Bill->where('invoice.id', $request->invoice_id);

        if ($isSalePerson == 1) {

            $childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);

            $Bill->where(function ($query) use ($childSalePersonsIds) {

                foreach ($childSalePersonsIds as $key => $value) {
                    if ($key == 0) {
                        $query->whereRaw('FIND_IN_SET("' . $value . '",bill.sale_persons)>0');
                    } else {
                        $query->orWhereRaw('FIND_IN_SET("' . $value . '",bill.sale_persons)>0');
                    }
                }
            });

            // foreach ($childSalePersonsIds as $key => $value) {
            // 	if ($key == 0) {
            // 		$Bill->whereRaw('FIND_IN_SET("' . $value . '",bill.sale_persons)>0');
            // 	} else {
            // 		$Bill->orWhereRaw('FIND_IN_SET("' . $value . '",bill.sale_persons)>0');
            // 	}

            // }

        } else if (isChannelPartner(Auth::user()->type) != 0) {

            $Bill->where('bill.channel_partner_user_id', Auth::user()->id);
        }
        $Bill = $Bill->first();

        if ($Bill) {

            $Bill->dispatch_detail = explode(",", $Bill->dispatch_detail);

            $salePersons = explode(",", $Bill['sale_persons']);
            $salePersons = User::select('first_name', 'last_name')->whereIn('id', $salePersons)->get();

            $salePersonsD = array();

            foreach ($salePersons as $keyS => $valueS) {

                $salePersonsD[$keyS] = $valueS['first_name'] . " " . $valueS['last_name'];
            }

            $Bill['sale_persons'] = implode(", ", $salePersonsD);

            $BCityList = CityList::find($Bill['bill_city_id']);
            $Bill['bill_city_name'] = "";
            if ($BCityList) {
                $Bill['bill_city_name'] = $BCityList->name;
            }

            $BStateList = StateList::find($Bill['bill_state_id']);
            $Bill['bill_state_name'] = "";
            if ($BStateList) {
                $Bill['bill_state_name'] = $BStateList->name;
            }

            $BCountryList = CountryList::find($Bill['bill_country_id']);
            $Bill['bill_country_name'] = "";
            if ($BCountryList) {
                $Bill['bill_country_name'] = $BCountryList->name;
            }

            $DCityList = CityList::find($Bill['d_city_id']);
            $Bill['d_city_name'] = "";
            if ($DCityList) {
                $Bill['d_city_name'] = $DCityList->name;
            }

            $DStateList = StateList::find($Bill['d_state_id']);
            $Bill['d_state_name'] = "";
            if ($DStateList) {
                $Bill['d_state_name'] = $DStateList->name;
            }

            $DCountryList = CountryList::find($Bill['d_country_id']);
            $Bill['d_country_name'] = "";
            if ($DCountryList) {
                $Bill['d_country_name'] = $DCountryList->name;
            }

            $Bill['payment_mode_lable'] = getPaymentModeName($Bill['payment_mode']);

            $Bill['channel_partner_type_name'] = getUserTypeName($Bill['channel_partner_type']);
            $Bill['display_date_time'] = convertOrderDateTime($Bill->created_at, "date");

            $BillItem = InvoiceItem::query();
            $BillItem->select('bill_items.id', 'invoice_items.qty', 'invoice_items.total_mrp', 'product_inventory.thumb as product_image', 'product_brand.name as product_brand_name', 'product_code.name as product_code_name');
            $BillItem->leftJoin('bill_items', 'invoice_items.order_item_id', '=', 'bill_items.id');
            $BillItem->leftJoin('product_inventory', 'product_inventory.id', '=', 'bill_items.product_inventory_id');
            $BillItem->leftJoin('data_master as product_brand', 'product_brand.id', '=', 'product_inventory.product_brand_id');
            $BillItem->leftJoin('data_master as product_code', 'product_code.id', '=', 'product_inventory.product_code_id');

            $BillItem->where('invoice_id', $Bill->id);
            $BillItem->orderBy('id', 'desc');
            $BillItem = $BillItem->get();

            $Bill['items'] = $BillItem;

            $response = successRes("Order detail");
            $response['data'] = $Bill;
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

        $startDate = date('Y-m-d', strtotime($request->start_date));
        // $startDate = date('Y-m-d H:i:s', strtotime($startDate . " -5 hours"));
        // $startDate = date('Y-m-d H:i:s', strtotime($startDate . " -30 minutes"));

        $endDate = date('Y-m-d', strtotime($request->end_date));
        // $endDate = date('Y-m-d H:i:s', strtotime($endDate . " -5 hours"));
        // $endDate = date('Y-m-d H:i:s', strtotime($endDate . " -30 minutes"));

        if ($request->export_type == 0 || $request->export_type == 1) {

            $Bill = Bill::query();
            $Bill->select('bill.id', 'channel_partner.firm_name', 'bill.created_at', 'users.first_name', 'users.last_name');
            $Bill->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'bill.channel_partner_user_id');
            $Bill->leftJoin('users', 'users.id', '=', 'bill.user_id');
            $Bill->orderBy('bill.id', 'desc');
            $Bill->whereDate('bill.created_at', '>=', $startDate);
            $Bill->whereDate('bill.created_at', '<=', $endDate);

            if (isset($request->channel_partner_user_id) && is_array($request->channel_partner_user_id)) {

                $Bill->whereIn('bill.channel_partner_user_id', $request->channel_partner_user_id);
            }

            if ($request->filter_type == 1) {

                if ($isSalePerson == 1) {

                    $childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
                    $Bill->where(function ($query) use ($childSalePersonsIds) {

                        foreach ($childSalePersonsIds as $key => $value) {
                            if ($key == 0) {
                                $query->whereRaw('FIND_IN_SET("' . $value . '",bill.sale_persons)>0');
                            } else {
                                $query->orWhereRaw('FIND_IN_SET("' . $value . '",bill.sale_persons)>0');
                            }
                        }
                    });

                    $Bill->where('channel_partner.reporting_company_id', Auth::user()->company_id);
                } else if ($isChannelPartner != 0) {

                    $Bill->where('bill.channel_partner_user_id', Auth::user()->id);
                }

                // bill
                //$query->whereIn('bill.status', array(0, 1, 2));
            } else if ($request->filter_type == 2) {
                //SALES bill
                $Bill->whereIn('bill.status', array(0, 1, 2, 3));

                if ($isAccountUser == 1 || $isAdminOrCompanyAdmin == 1) {

                    if (Auth::user()->parent_id != 0) {

                        $parent = ChannelPartner::where('user_id', Auth::user()->parent_id)->first();

                        $Bill->where('channel_partner.reporting_manager_id', $parent->user_id);
                    } else {

                        $Bill->where('channel_partner.reporting_company_id', Auth::user()->company_id);
                        $Bill->where('channel_partner.reporting_manager_id', 0);
                    }
                } else if (isChannelPartner(Auth::user()->type) != 0) {

                    $Bill->where('channel_partner.reporting_manager_id', Auth::user()->id);
                }
            }

            if ($request->export_type == 0) {
                $Bill->whereIn('bill.status', [0, 1, 2, 3]);
            } else if ($request->export_type == 1) {
                $Bill->whereIn('bill.status', [0, 1, 2]);
            }

            $Bill = $Bill->get();

            $orderIds = array(0);

            foreach ($Bill as $key => $value) {

                $orderIds[] = $value->id;
            }

            $orderItemsPendingQTY = array();

            $BillItem = BillItems::query();
            $BillItem->select('bill_items.pending_qty', 'bill_items.bill_id', 'bill_items.product_inventory_id', 'bill_items.bill_id', 'bill_items.qty');
            $BillItem->orderBy('bill_items.id', 'desc');
            $BillItem->whereIn('bill_items.bill_id', $orderIds);

            if (isset($request->product_inventory_id) && is_array($request->product_inventory_id)) {

                $BillItem->whereIn('bill_items.product_inventory_id', $request->product_inventory_id);
            }
            if ($request->export_type != 0) {
                $BillItem->where('bill_items.pending_qty', '>', 0);
            }
            $OrderItems = $BillItem->get();

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

                $Bill = Bill::query();
                $Bill->select('bill.id', 'channel_partner.firm_name', 'bill.created_at', 'users.first_name', 'users.last_name');
                $Bill->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'bill.channel_partner_user_id');
                $Bill->leftJoin('users', 'users.id', '=', 'bill.user_id');
                $Bill->orderBy('bill.id', 'desc');
                $Bill->whereIn('bill.id', $orderIds);
                $Bill = $Bill->get();
            }

            $productIds = array_unique($productIds);
            $productIds = array_values($productIds);

            $Products = ProductInventory::select('product_inventory.id', DB::raw('CONCAT(product_brand.name," ",product_code.name )  as text'));
            $Products->leftJoin('data_master as product_brand', 'product_brand.id', '=', 'product_inventory.product_brand_id');
            $Products->leftJoin('data_master as product_code', 'product_code.id', '=', 'product_inventory.product_code_id');
            $Products->whereIn('product_inventory.id', $productIds);
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

            $headers = array("Channel Partner/Products", "#orderId", "orderDate", "orderCreatedBy");
            foreach ($productIdText as $key => $value) {
                if ($key != 0) {
                    $headers[] = $value;
                }
            }

            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="reports.csv"');
            $fp = fopen('php://output', 'wb');

            fputcsv($fp, $headers);

            foreach ($Bill as $key => $value) {

                $created_at = convertOrderDateTime($value->created_at, "date");

                $lineVal = array(
                    $value->firm_name,
                    $value->id,
                    $created_at,
                    $value->first_name . " " . $value->last_name,

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

            $Bill = Bill::query();
            $Bill->select('bill.id', 'channel_partner.firm_name', 'bill.created_at', 'users.first_name', 'users.last_name', 'bill.sale_persons', 'bill.total_mrp_minus_disocunt', 'bill.total_payable', 'bill.status', 'bill.sub_status');
            $Bill->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'bill.channel_partner_user_id');
            $Bill->leftJoin('users', 'users.id', '=', 'bill.user_id');
            $Bill->orderBy('bill.id', 'desc');
            $Bill->whereDate('bill.created_at', '>=', $startDate);
            $Bill->whereDate('bill.created_at', '<=', $endDate);

            if (isset($request->channel_partner_user_id) && is_array($request->channel_partner_user_id)) {

                $Bill->whereIn('bill.channel_partner_user_id', $request->channel_partner_user_id);
            }

            if ($request->filter_type == 1) {
                // bill
                if ($isSalePerson == 1) {

                    $childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
                    $Bill->where(function ($query) use ($childSalePersonsIds) {

                        foreach ($childSalePersonsIds as $key => $value) {
                            if ($key == 0) {
                                $query->whereRaw('FIND_IN_SET("' . $value . '",bill.sale_persons)>0');
                            } else {
                                $query->orWhereRaw('FIND_IN_SET("' . $value . '",bill.sale_persons)>0');
                            }
                        }
                    });

                    $Bill->where('channel_partner.reporting_company_id', Auth::user()->company_id);
                } else if ($isChannelPartner != 0) {

                    $Bill->where('bill.channel_partner_user_id', Auth::user()->id);
                }
            } else if ($request->filter_type == 2) {
                //SALES bill
                $Bill->whereIn('bill.status', array(0, 1, 2, 3));

                if ($isAccountUser == 1 || $isAdminOrCompanyAdmin == 1) {

                    if (Auth::user()->parent_id != 0) {

                        $parent = ChannelPartner::where('user_id', Auth::user()->parent_id)->first();

                        $Bill->where('channel_partner.reporting_manager_id', $parent->user_id);
                    } else {

                        $Bill->where('channel_partner.reporting_company_id', Auth::user()->company_id);
                        $Bill->where('channel_partner.reporting_manager_id', 0);
                    }
                } else if (isChannelPartner(Auth::user()->type) != 0) {

                    $Bill->where('channel_partner.reporting_manager_id', Auth::user()->id);
                }
            }

            $Bill = $Bill->get();

            $headers = array("DATE", "ORDER ID", "ORDER BY", "CHANNEL PARTNER", "EXGST", "STATUS");

            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="reports.csv"');
            $fp = fopen('php://output', 'wb');
            fputcsv($fp, $headers);
            foreach ($Bill as $key => $value) {

                //$sale_persons = explode(",", $value['sale_persons']);

                // $Users = User::select('users.first_name', 'users.last_name', 'users.type', 'users.phone_number', 'sales_hierarchy.code as sales_hierarchy_code')->leftJoin('sale_person', 'sale_person.id', '=', 'users.reference_id')->leftJoin('sales_hierarchy', 'sales_hierarchy.id', '=', 'sale_person.type')->whereIn('users.id', $sale_persons)->get();

                // $uiSalePerson = '';
                // foreach ($Users as $kU => $vU) {
                // 	$uiSalePerson .= $vU['first_name'] . ' ' . $vU['last_name'] . '|' . $vU['sales_hierarchy_code'] . '|PHONE:' . $vU['phone_number'] . ',';
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
        } else if ($request->export_type == 3) {

            $Bill = Bill::query();
            $Bill->select('bill.id', 'channel_partner.firm_name', 'bill.created_at', 'users.first_name', 'users.last_name', 'bill.sale_persons', 'bill.total_mrp_minus_disocunt', 'bill.total_payable', 'bill.status', 'bill.sub_status');
            $Bill->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'bill.channel_partner_user_id');
            $Bill->leftJoin('users', 'users.id', '=', 'bill.user_id');
            $Bill->orderBy('bill.id', 'desc');
            $Bill->whereDate('bill.created_at', '>=', $startDate);
            $Bill->whereDate('bill.created_at', '<=', $endDate);
            $Bill->whereIn('bill.status', array(4));
            if (isset($request->channel_partner_user_id) && is_array($request->channel_partner_user_id)) {

                $Bill->whereIn('bill.channel_partner_user_id', $request->channel_partner_user_id);
            }

            if ($request->filter_type == 1) {
                // bill
                if ($isSalePerson == 1) {

                    $childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
                    $Bill->where(function ($query) use ($childSalePersonsIds) {

                        foreach ($childSalePersonsIds as $key => $value) {
                            if ($key == 0) {
                                $query->whereRaw('FIND_IN_SET("' . $value . '",bill.sale_persons)>0');
                            } else {
                                $query->orWhereRaw('FIND_IN_SET("' . $value . '",bill.sale_persons)>0');
                            }
                        }
                    });

                    $Bill->where('channel_partner.reporting_company_id', Auth::user()->company_id);
                } else if ($isChannelPartner != 0) {

                    $Bill->where('bill.channel_partner_user_id', Auth::user()->id);
                }
            } else if ($request->filter_type == 2) {
                //SALES bill
                $Bill->whereIn('bill.status', array(4));

                if ($isAccountUser == 1 || $isAdminOrCompanyAdmin == 1) {

                    if (Auth::user()->parent_id != 0) {

                        $parent = ChannelPartner::where('user_id', Auth::user()->parent_id)->first();

                        $Bill->where('channel_partner.reporting_manager_id', $parent->user_id);
                    } else {

                        $Bill->where('channel_partner.reporting_company_id', Auth::user()->company_id);
                        $Bill->where('channel_partner.reporting_manager_id', 0);
                    }
                } else if (isChannelPartner(Auth::user()->type) != 0) {

                    $Bill->where('channel_partner.reporting_manager_id', Auth::user()->id);
                }
            }

            $Bill = $Bill->get();

            $headers = array("DATE", "ORDER ID", "ORDER BY", "CHANNEL PARTNER", "EXGST", "STATUS");

            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="reports.csv"');
            $fp = fopen('php://output', 'wb');
            fputcsv($fp, $headers);
            foreach ($Bill as $key => $value) {

                //$sale_persons = explode(",", $value['sale_persons']);

                // $Users = User::select('users.first_name', 'users.last_name', 'users.type', 'users.phone_number', 'sales_hierarchy.code as sales_hierarchy_code')->leftJoin('sale_person', 'sale_person.id', '=', 'users.reference_id')->leftJoin('sales_hierarchy', 'sales_hierarchy.id', '=', 'sale_person.type')->whereIn('users.id', $sale_persons)->get();

                // $uiSalePerson = '';
                // foreach ($Users as $kU => $vU) {
                // 	$uiSalePerson .= $vU['first_name'] . ' ' . $vU['last_name'] . '|' . $vU['sales_hierarchy_code'] . '|PHONE:' . $vU['phone_number'] . ',';
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
