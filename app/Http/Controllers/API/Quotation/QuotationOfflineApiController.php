<?php

namespace App\Http\Controllers\API\Quotation;

use App\Models\DebugLog;
use App\Models\WlmstItem;
use App\Models\Wlmst_Client;
// use PDF;
// use Dompdf\Dompdf;
use App\Models\WlmstCompany;
use Illuminate\Http\Request;
use App\Models\Wlmst_ItemGroup;
use App\Models\Wlmst_ItemPrice;
// use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Wltrn_Quotation;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\WlmstItemCategory;
use App\Models\WlmstItemSubgroup;
use Illuminate\Support\Facades\DB;
use App\Models\Wlmst_QuotationType;
use App\Http\Controllers\Controller;
use App\Models\Wlmst_NameSuggestion;
use App\Models\Wlmst_QuotationError;
use App\Models\LeadContact;
use App\Models\QuotRequest;
use App\Models\User;
use App\Models\Lead;
use App\Models\ChannelPartner;
use App\Models\LeadSource;
use App\Models\SalePerson;
use App\Models\CityList;
use App\Models\Wltrn_QuotItemdetail;
use Illuminate\Support\Facades\File;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Quotation\QuotationMasterController;
use App\Http\Controllers\CRM\Lead\LeadQuotationController;
use GrahamCampbell\ResultType\Success;
use Illuminate\Http\Response;

// use Illuminate\Http\Request;
class QuotationOfflineApiController extends Controller
{
    public function getAllUserlist(Request $request)
    {
        $response = [];
        try {
            $response = [];
            $query = User::query();
            $query->select('users.id', 'users.first_name', 'users.last_name', 'users.phone_number', 'users.email', 'users.type', 'users.created_by', 'channel_partner.firm_name');
            $query->leftJoin('channel_partner', 'users.id', '=', 'channel_partner.user_id');
            $query->where('users.status', 1);
            $data = $query->get();

            $response = quotsuccessRes();
            $response['data'] = $data;
        } catch (QueryException $ex) {
            $response = quoterrorRes();
            $response['data'] = $ex->getMessage();
        }
        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }

    public function getAllCustomerlist(Request $request)
    {
        $response = [];
        try {
            $response = [];
            $query = Wlmst_Client::query();
            $query->select('*');
            $query->where('wlmst_client.isactive', 1);
            $data = $query->get();

            $response = quotsuccessRes();
            $response['data'] = $data;
        } catch (QueryException $ex) {
            $response = quoterrorRes();
            $response['data'] = $ex->getMessage();
        }
        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }
    public function GetAllCompanyList(Request $request)
    {
        $response = [];
        try {
            $response = [];
            $query = WlmstCompany::query();
            $query->select('*');
            $query->where('wlmst_companies.isactive', 1);
            $data = $query->get();

            $response = quotsuccessRes();
            $response['data'] = $data;
        } catch (QueryException $ex) {
            $response = quoterrorRes();
            $response['data'] = $ex->getMessage();
        }
        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }
    public function GetAllCategoryList(Request $request)
    {
        $response = [];
        try {
            $response = [];
            $query = WlmstItemCategory::query();
            $query->select('*');
            $query->where('wlmst_item_categories.isactive', 1);
            $data = $query->get();

            $response = quotsuccessRes();
            $response['data'] = $data;
        } catch (QueryException $ex) {
            $response = quoterrorRes();
            $response['data'] = $ex->getMessage();
        }
        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }
    public function GetAllGroupList(Request $request)
    {
        $response = [];
        try {
            $response = [];
            $query = Wlmst_ItemGroup::query();
            $query->select('*');
            $query->where('wlmst_item_groups.isactive', 1);
            $data = $query->get();

            $response = quotsuccessRes();
            $response['data'] = $data;
        } catch (QueryException $ex) {
            $response = quoterrorRes();
            $response['data'] = $ex->getMessage();
        }
        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }
    public function GetAllSubGroupList(Request $request)
    {
        $response = [];
        try {
            $response = [];
            $query = WlmstItemSubgroup::query();
            $query->select('*');
            $query->where('wlmst_item_subgroups.isactive', 1);
            $data = $query->get();

            $response = quotsuccessRes();
            $response['data'] = $data;
        } catch (QueryException $ex) {
            $response = quoterrorRes();
            $response['data'] = $ex->getMessage();
        }
        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }
    public function GetAllItemList(Request $request)
    {
        $response = [];
        try {
            $response = [];
            $query = WlmstItem::query();
            $query->select('*');
            $query->where('wlmst_items.isactive', 1);
            $data = $query->get();
            foreach ($data as $key => $value) {
                $data[$key]['image'] = getSpaceFilePath($value->image);
            }
            $response = quotsuccessRes();
            $response['data'] = $data;
        } catch (QueryException $ex) {
            $response = quoterrorRes();
            $response['data'] = $ex->getMessage();
        }
        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }
    public function GetAllItemPriceList(Request $request)
    {
        $response = [];
        try {
            $response = [];
            $query = Wlmst_ItemPrice::query();
            $query->select(['wlmst_item_prices.*', 'wlmst_items.itemcategory_id']);
            $query->where('wlmst_item_prices.isactive', 1);
            $query->leftJoin('wlmst_items', 'wlmst_items.id', '=', 'wlmst_item_prices.item_id');
            $query->leftJoin('wlmst_item_categories', 'wlmst_items.itemcategory_id', '=', 'wlmst_item_categories.id');
            $data = $query->get();
            foreach ($data as $key => $value) {
                $itemSubgoup = WlmstItemSubgroup::find($value->itemsubgroup_id);
                if ($itemSubgoup) {
                    if ($value->itemcategory_id == 13) {
                        if (strpos(strtolower($itemSubgoup->itemsubgroupname), 'white') == true) {
                            $data[$key]['image'] = 'https://whitelion.sgp1.digitaloceanspaces.com/erp.whitelion.in/quotation/item/168440428710446.png';
                        } else {
                            $data[$key]['image'] = 'https://whitelion.sgp1.digitaloceanspaces.com/erp.whitelion.in/quotation/item/168440423561646.png';
                        }
                    } else {
                        if ($value->image == null) {
                            $data[$key]['image'] = 'http://axoneerp.whitelion.in/assets/images/logo.svg';
                        } else {
                            $data[$key]['image'] = getSpaceFilePath($value->image);
                        }
                    }
                } else {
                    if ($value->image == null) {
                        $data[$key]['image'] = 'http://axoneerp.whitelion.in/assets/images/logo.svg';
                    } else {
                        $data[$key]['image'] = getSpaceFilePath($value->image);
                    }
                }
            }

            $response = quotsuccessRes();
            $response['data'] = $data;
        } catch (QueryException $ex) {
            $response = quoterrorRes();
            $response['data'] = $ex->getMessage();
        }
        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }
    public function GetAllIQuotationList(Request $request)
    {
        $response = [];
        try {
            $response = [];
            $query = Wltrn_Quotation::query();
            $query->select('*');
            if (isAdminOrCompanyAdmin() != 1) {
                //Live
                $query->where('wltrn_quotation.entryby', Auth::user()->id);
            }
            $query->where('wltrn_quotation.quottype_id', '!=', '4');
            $data = $query->get();

            $response = quotsuccessRes();
            $response['data'] = $data;
        } catch (QueryException $ex) {
            $response = quoterrorRes();
            $response['data'] = $ex->getMessage();
        }
        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }
    public function GetAllIQuotationDetailList(Request $request)
    {
        $response = [];
        try {
            $response = [];
            $query = Wltrn_QuotItemdetail::query();
            $query->select('*');
            if (isAdminOrCompanyAdmin() != 1) {
                //Live
                $query->where('wltrn_quot_itemdetails.entryby', Auth::user()->id);
            }
            $data = $query->get();

            $response = quotsuccessRes();
            $response['data'] = $data;
        } catch (QueryException $ex) {
            $response = quoterrorRes();
            $response['data'] = $ex->getMessage();
        }
        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }

    public function GetAllItemBrandList(Request $request)
    {
        $SearchValue = isset($request->q) ? $request->q : '';
        $brand_query = Wltrn_QuotItemdetail::query();
        $brand_query->select('wlmst_item_prices.itemsubgroup_id', 'wlmst_item_subgroups.itemsubgroupname as text', 'wlmst_item_subgroups.default_disc as discount');
        $brand_query->leftJoin('wlmst_item_prices', 'wlmst_item_prices.id', '=', 'wltrn_quot_itemdetails.item_price_id');
        $brand_query->leftJoin('wlmst_item_subgroups', 'wlmst_item_subgroups.id', '=', 'wlmst_item_prices.itemsubgroup_id');
        $brand_query->where('wltrn_quot_itemdetails.quot_id', $request->quot_id);
        $brand_query->where('wltrn_quot_itemdetails.quotgroup_id', $request->quot_groupid);
        $brand_query->where('wlmst_item_subgroups.itemsubgroupname', 'like', '%' . $SearchValue . '%');
        $brand_query->groupBy(['wlmst_item_prices.itemsubgroup_id', 'wlmst_item_subgroups.itemsubgroupname', 'wlmst_item_subgroups.default_disc']);
        $brand_query_data = $brand_query->get();
        if ($brand_query_data) {
            $qry_quotation = Wltrn_Quotation::find($request->quot_id);
            if ($qry_quotation && $qry_quotation->status == 2) {
                $Quot_columns = ['quotation_request.deal_id', 'quotation_request.title', 'quotation_request.assign_to', 'quotation_request.status', 'quotation_request.group_id', 'wltrn_quotation.customer_name', 'quotation_request.created_at', 'users.first_name', 'users.last_name'];

                $quot_query = QuotRequest::select($Quot_columns);
                $quot_query->leftJoin('wltrn_quotation', 'wltrn_quotation.id', '=', 'quotation_request.quot_id');
                $quot_query->leftJoin('users', 'users.id', '=', 'quotation_request.assign_to');
                $quot_query->where('quotation_request.quot_id', $request->quot_id);
                // $quot_query->where('quotation_request.status', 0);
                $quot_query->where('quotation_request.type', 'DISCOUNT');
                $quot_query = $quot_query->get();
            }
        }

        $response = successRes('Item Brand list');
        $response['data'] = $brand_query_data;
        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }
    public function GetQuotationDataSyncJson(Request $request)
    {
        // DB::enableQueryLog();
        $column = [
            'wltrn_quotation.id as wq_id',
            'wltrn_quotation.quotgroup_id as wq_quotgroup_id',
            'wltrn_quotation.yy as wq_yy',
            'wltrn_quotation.mm as wq_mm',
            'wltrn_quotation.quottype_id as wq_quottype_id',
            'wltrn_quotation.quotno as wq_quotno',
            'wltrn_quotation.quot_no_str as wq_quot_no_str',
            'wltrn_quotation.architech_id as wq_architech_id',
            'wltrn_quotation.electrician_id as wq_electrician_id',
            'wltrn_quotation.salesexecutive_id as wq_salesexecutive_id',
            'wltrn_quotation.channelpartner_id as wq_channelpartner_id',
            'wltrn_quotation.inquiry_id as wq_inquiry_id',
            'wltrn_quotation.site_name as wq_site_name',
            'wltrn_quotation.siteaddress as wq_siteaddress',
            'wltrn_quotation.site_state_id as wq_site_state_id',
            'wltrn_quotation.site_city_id as wq_site_city_id',
            'wltrn_quotation.site_country_id as wq_site_country_id',
            'wltrn_quotation.additional_remark as wq_additional_remark',
            'wltrn_quotation.quot_date as wq_quot_date',
            'wltrn_quotation.quotationsource as wq_quotationsource',
            'wltrn_quotation.refrence_name as wq_refrence_name',
            'wltrn_quotation.refrencequotation_id as wq_refrencequotation_id',
            'wltrn_quotation.status as wq_status',
            'wltrn_quotation.email_count as wq_email_count',
            'wltrn_quotation.print_count as wq_print_count',
            'wltrn_quotation.copyfromquotation_id as wq_copyfromquotation_id',
            'wltrn_quotation.site_pincode as wq_site_pincode',
            'wltrn_quotation.print_count as wq_print_count',
            'wltrn_quotation.default_range as wq_default_range',
            'customer.name as customer_name',
            'customer.email as customer_email',
            'customer.mobile as customer_mobile',
            'customer.address as customer_address',
            'customer.remark as customer_remark',
            'wqi.*',
        ];
        $quotation_array = Wltrn_Quotation::query();
        $quotation_array->select($column);
        $quotation_array->leftJoin('wltrn_quot_itemdetails as wqi', function ($join) {
            $join->on('wqi.quot_id', '=', 'wltrn_quotation.id');
            $join->on('wqi.quotgroup_id', '=', 'wltrn_quotation.quotgroup_id');
        });
        $quotation_array->leftJoin('wlmst_client as customer', 'customer.id', '=', 'wltrn_quotation.customer_id');

        $quotation_array = $quotation_array->get();
        // $quotation_array['query'] = DB::getQueryLog();

        return response()
            ->json($quotation_array)
            ->header('Content-Type', 'application/json');
    }

    public function PostQuotOflineDataSave(Request $request)
    {
        DB::enableQueryLog();

        $new_quot_number = 0;
        $new_quot_group_number = 0;
        $old_quot_number = 0;
        $old_quot_group_number = 0;
        $reqData = $request['data'];
        foreach ($reqData as $key => $reqval) {
            $old_quot_number = (int) $reqval['wq_id'];
            $old_quot_group_number = (int) $reqval['wq_quotgroup_id'];
            if ($new_quot_number != $old_quot_number) {
                $new_quot_number = 0;
            }
            if ($new_quot_group_number != $old_quot_group_number) {
                $new_quot_group_number = 0;
            }
            // ------------ ADD OR CREATE CUSTOMER START ------------
            $customer = Wlmst_Client::query();
            $customer->where('email', $reqval['customer_email']);
            $customer->where('mobile', $reqval['customer_mobile']);
            $customer = $customer->first();

            if ($customer) {
                // COUSTORMER ALREADY EXIST
                $Client = $customer;
            } else {
                // COUSTORMER NOT EXIST
                $Client = new Wlmst_Client();
                $Client->entryby = Auth::user()->id; //Live
                $Client->entryip = $request->ip();
                $Client->name = $reqval['customer_name'];
                $Client->email = $reqval['customer_email'];
                $Client->mobile = $reqval['customer_mobile'];
                $Client->address = $reqval['customer_address'];
                $Client->remark = $reqval['customer_remark'];
                $Client->save();

                if ($Client) {
                    // CLIENT SAVED
                    $DebugLog = new DebugLog();
                    $DebugLog->user_id = Auth::user()->id;
                    $DebugLog->name = 'quot-client-master-add';
                    $DebugLog->description = 'client master #' . $Client->id . '(' . $Client->name . ') has been added';
                    $DebugLog->save();
                } else {
                    // CLIENT NOT SAVED
                    $response = errorRes('customer detail mismatch please check !', 400);
                    $response['error'] = 'NAME : ' . $reqval['customer_name'] . ' EMAIL : ' . $reqval['customer_email'] . ' MOBILE : ' . $reqval['customer_mobile'];
                    return response()->json($response)->header('Content-Type', 'application/json');
                }
            }
            // ------------ ADD OR CREATE CUSTOMER END ------------

            // ------------ ADD OR CREATE QUOTATION START ------------
            if ($new_quot_number != 0) {
                $QuotDetail = Wltrn_Quotation::find($new_quot_number);
                $new_quot_number = $QuotDetail->id;
                $new_quot_group_number = $QuotDetail->quotgroup_id;
            } else {
                $QuotDetail = new Wltrn_Quotation();
                $QuotDetail->entryby = Auth::user()->id; //Live
                $QuotDetail->entryip = $request->ip();

                if ($new_quot_group_number != 0) {
                    $QuotDetail->quotgroup_id = $new_quot_group_number;
                    $QuotDetail->quotno = $new_quot_group_number;
                } else {
                    $QuotDetail->quotgroup_id = Wltrn_Quotation::max('quotgroup_id') + 1;
                    $QuotDetail->quotno = Wltrn_Quotation::max('quotno') + 1;
                }
                $QuotDetail->yy = substr(date('Y'), -2);
                $QuotDetail->mm = date('m');
                $QuotDetail->quot_date = date('Y-m-d');
                $QuotDetail->quot_no_str = $reqval['wq_quot_no_str'];

                $QuotDetail->quottype_id = $reqval['wq_quottype_id'];
                $QuotDetail->customer_id = $Client->id;
                $QuotDetail->customer_name = $Client->name;
                $QuotDetail->customer_contact_no = $Client->mobile;

                $QuotDetail->architech_id = $reqval['wq_architech_id'];
                $QuotDetail->electrician_id = $reqval['wq_electrician_id'];
                if (isSalePerson() == 1) {
                    //Live
                    $QuotDetail->salesexecutive_id = Auth::user()->id;
                } else {
                    $QuotDetail->salesexecutive_id = $reqval['wq_salesexecutive_id'];
                }
                $QuotDetail->channelpartner_id = $reqval['wq_channelpartner_id'];
                $QuotDetail->site_name = $reqval['wq_site_name'];
                $QuotDetail->siteaddress = $reqval['wq_siteaddress'];
                $QuotDetail->site_country_id = $reqval['wq_site_country_id'];
                $QuotDetail->site_state_id = $reqval['wq_site_state_id'];
                $QuotDetail->site_city_id = $reqval['wq_site_city_id'];
                $QuotDetail->site_pincode = $reqval['wq_site_pincode'];
                $QuotDetail->inquiry_id = 0;
                $QuotDetail->additional_remark = $reqval['wq_additional_remark'];

                $QuotDetail->quotationsource = $reqval['wq_quotationsource'];

                $QuotDetail->save();

                if ($QuotDetail) {
                    $new_quot_number = $QuotDetail->id;
                    $new_quot_group_number = $QuotDetail->quotgroup_id;
                    $DebugLog = new DebugLog();
                    $DebugLog->user_id = 1;
                    $DebugLog->name = 'quot-master-basicdetail-add';
                    $DebugLog->description = 'Quotation master Basic Detail #' . $QuotDetail->id . '(' . $QuotDetail->id . ') has been added';
                    $DebugLog->save();
                } else {
                    // CLIENT NOT SAVED
                    $response = errorRes('fatching error from quotation add time !', 400);
                    $response['error'] = 'QUOTE ID : ' . $reqval['wq_id'] . ' QUOTE GROUP ID : ' . $reqval['wq_quotgroup_id'] . ' VERSION : ' . $reqval['wq_quot_no_str'];
                    return response()
                        ->json($response)
                        ->header('Content-Type', 'application/json');
                }
            }

            // ------------ ADD OR CREATE QUOTATION END ------------

            $QuotId = $QuotDetail->id;
            $QuotGroupId = $QuotDetail->quotgroup_id;
            $RoomNo = $reqval['room_no'];
            $BoardNo = $reqval['board_no'];

            // ------------ ADD OR CREATE QUOTATION BOARD IMAGE START ------------
            $boardExist = Wltrn_QuotItemdetail::where([['quot_id', $QuotId], ['quotgroup_id', $QuotGroupId], ['room_no', $RoomNo], ['room_no', $BoardNo]])->first();
            if ($boardExist) {
                $Image_Path = $boardExist->board_image;
            } else {
                if ($reqval['board_image'] != '') {
                    $folderPathofFile = '/quotation/board';
                    $fileObject1 = base64_decode($reqval['board_image']);
                    $extension = '.png';
                    $fileName1 = uniqid() . '_' . $QuotId . '_' . $QuotGroupId . '_' . $RoomNo . '_' . $BoardNo . $extension;
                    $destinationPath = public_path($folderPathofFile);

                    if (!File::exists($destinationPath)) {
                        File::makeDirectory($destinationPath);
                    }

                    file_put_contents($destinationPath . '/' . $fileName1, $fileObject1);

                    if (File::exists(public_path($folderPathofFile . '/' . $fileName1))) {
                        $Image_Path = $folderPathofFile . '/' . $fileName1;
                        //START UPLOAD FILE ON SPACES
                        $spaceUploadResponse = uploadFileOnSpaces(public_path($Image_Path), $Image_Path); //Live

                        if ($spaceUploadResponse != 1) {
                            $Image_Path = '';
                        } else {
                            unlink(public_path($Image_Path));
                        }
                        //END UPLOAD FILE ON SPACES
                    } else {
                        $Image_Path = 'aa';
                    }
                } else {
                    $Image_Path = '/assets/images/logo.png';
                }
            }

            // ------------ ADD OR CREATE QUOTATION BOARD IMAGE END ------------

            // ------------ ADD OR CREATE QUOTATION DETAIL START ------------
            if (intval($reqval['qty']) != 0) {
                $ItemMaster = WlmstItem::find($reqval['item_id']);
                $ItemPriceMaster = Wlmst_ItemPrice::find($reqval['item_price_id']);
                $SubTotal = floatval($ItemPriceMaster->mrp) * floatval($reqval['qty']);
                $Discount_Amount = (floatval($SubTotal) * floatval($ItemPriceMaster->discount)) / 100;
                $GrossAmount = floatval($SubTotal) - floatval($Discount_Amount);
                if ($QuotDetail->site_state_id == '9' /*IS GUJARAT*/) {
                    /* CGST CALCULATION */
                    $CGST_Per = $ItemMaster->cgst_per;
                    $CGST_Amount = (floatval($GrossAmount) * floatval($ItemMaster->cgst_per)) / 100;
                    /* SGST CALCULATION */
                    $SGST_Per = $ItemMaster->sgst_per;
                    $SGST_Amount = (floatval($GrossAmount) * floatval($ItemMaster->sgst_per)) / 100;
                    /* IGST CALCULATION */
                    $IGST_Per = '0.00';
                    $IGST_Amount = '0.00';

                    /* NET AMOUNT CALCULATION */
                    $NetTotalAmount = floatval($GrossAmount) + floatval($CGST_Amount) + floatval($SGST_Amount);
                    /* ROUND_UP AMOUNT CALCULATION */
                    $RoundUpAmount = floatval($NetTotalAmount) - floatval(round($NetTotalAmount));
                    /* NET FINAL AMOUNT CALCULATION */
                    $NetAmount = round($NetTotalAmount);
                } else {
                    /* CGST CALCULATION */
                    $CGST_Per = '0';
                    $CGST_Amount = '0.00';
                    /* SGST CALCULATION */
                    $SGST_Per = '0';
                    $SGST_Amount = '0.00';
                    /* IGST CALCULATION */
                    $IGST_Per = $ItemMaster->igst_per;
                    $IGST_Amount = (floatval($GrossAmount) * floatval($ItemMaster->igst_per)) / 100;

                    /* NET AMOUNT CALCULATION */
                    $NetTotalAmount = floatval($GrossAmount) + floatval($IGST_Amount);
                    /* ROUND_UP AMOUNT CALCULATION */
                    $RoundUpAmount = floatval($NetTotalAmount) - floatval(round($NetTotalAmount));
                    /* NET FINAL AMOUNT CALCULATION */
                    $NetAmount = round($NetTotalAmount);
                }

                $qry_add_quot_item = new Wltrn_QuotItemdetail();
                $qry_add_quot_item->quot_id = $QuotId;
                $qry_add_quot_item->quotgroup_id = $QuotGroupId;
                $qry_add_quot_item->room_no = $RoomNo;
                $qry_add_quot_item->room_name = $reqval['room_name'];
                $qry_add_quot_item->board_no = $BoardNo;
                $qry_add_quot_item->board_name = $reqval['board_name'];
                $qry_add_quot_item->board_size = $reqval['board_size'];
                $qry_add_quot_item->board_item_id = $reqval['board_item_id'];
                $qry_add_quot_item->board_item_price_id = $reqval['board_item_price_id'];
                if ($Image_Path != '') {
                    $qry_add_quot_item->board_image = $Image_Path;
                }
                $qry_add_quot_item->itemdescription = $reqval['itemdescription'];
                $qry_add_quot_item->item_id = $ItemMaster->id;
                $qry_add_quot_item->item_price_id = $ItemPriceMaster->id;
                $qry_add_quot_item->company_id = $ItemPriceMaster->company_id;
                $qry_add_quot_item->itemgroup_id = $ItemPriceMaster->itemgroup_id;
                $qry_add_quot_item->itemsubgroup_id = $ItemPriceMaster->itemsubgroup_id;
                $qry_add_quot_item->itemcode = $ItemPriceMaster->code;
                $qry_add_quot_item->qty = $reqval['qty'];
                $qry_add_quot_item->rate = $ItemPriceMaster->mrp;

                $qry_add_quot_item->discper = $ItemPriceMaster->discount;
                $qry_add_quot_item->discamount = $Discount_Amount;

                $qry_add_quot_item->grossamount = $GrossAmount;
                $qry_add_quot_item->taxableamount = $GrossAmount;
                $qry_add_quot_item->igst_per = $IGST_Per;
                $qry_add_quot_item->igst_amount = $IGST_Amount;
                $qry_add_quot_item->cgst_per = $CGST_Per;
                $qry_add_quot_item->cgst_amount = $CGST_Amount;
                $qry_add_quot_item->sgst_per = $SGST_Per;
                $qry_add_quot_item->sgst_amount = $SGST_Amount;
                $qry_add_quot_item->roundup_amount = $RoundUpAmount;
                $qry_add_quot_item->net_amount = $NetAmount;
                $qry_add_quot_item->item_type = $reqval['item_type'];
                $qry_add_quot_item->room_range = $reqval['room_range'];
                $qry_add_quot_item->board_range = $reqval['board_range'];
                $qry_add_quot_item->entryby = Auth::user()->id; //Live
                $qry_add_quot_item->entryip = $request->ip();
                $qry_add_quot_item->save();
            }
            // ------------ ADD OR CREATE QUOTATION DETAIL END ------------
        }

        $response = quotsuccessRes();
        $response['data'] = '';
        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }

}
