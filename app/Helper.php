<?php

use App\Models\Lead;
use App\Models\User;
use App\Models\CRMLog;
use App\Models\Inquiry;
use App\Models\CityList;
use App\Models\DebugLog;
use App\Models\NotificationScheduler;
use App\Models\LeadCall;
use App\Models\Architect;
use App\Models\StateList;
use App\Models\TeleSales;
use App\Models\InquiryLog;
use App\Models\ProductLog;
use App\Models\SalePerson;
use App\Models\LeadStatusUpdate;
use App\Models\UserContact;
use App\Models\UserFiles;
use App\Models\CountryList;
use App\Models\UserNotes;
use App\Models\Electrician;
use App\Models\UserCallAction;
use App\Models\UserMeetingParticipant;
use App\Models\CRMSettingMeetingTitle;
use App\Models\UserMeetingAction;
use App\Models\UserTaskAction;
use App\Models\LeadTimeline;
use App\Models\LeadTask;
use App\Models\UserLog;
use Illuminate\Http\Request;
use App\Models\ChannelPartner;
use App\Models\UserNotification;
use App\Models\wlmst_appversion;
use App\Models\MarketingProductLog;
use Illuminate\Support\Facades\Auth;
use App\Models\Wlmst_ServiceExecutive;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use App\Models\wlmst_user_created_board_log;
use App\Http\Controllers\Whatsapp\WhatsappApiContoller;
use App\Http\Controllers\Quotation\QuotationMasterController;
use App\Models\Wltrn_Quotation;

function successRes($msg = 'Success', $statusCode = 200)
{
    $return = [];
    $return['status'] = 1; // 1=Success; 0=error; 2=appupdate
    $return['status_code'] = $statusCode;
    $return['msg'] = $msg;
    return $return;
}

function errorRes($msg = 'Error', $statusCode = 400)
{
    $return = [];
    $return['status'] = 0; // 1=Success; 0=error; 2=appupdate
    $return['status_code'] = $statusCode;
    $return['msg'] = $msg;
    return $return;
}

function getSpacesFolder()
{
    // if($_SERVER['HTTP_HOST'] == '103.218.110.153:242'){
    // 	// return '127.0.0.1:8000';
    // 	return "erp.whitelion.in";
    // }else {
    // 	return $_SERVER['HTTP_HOST'];
    // }
    return 'erp.whitelion.in';
}



function uploadFileOnSpaces($diskFilePath, $spaceFilePath)
{
    $spacesFolder = getSpacesFolder();
    return Storage::disk('spaces')->put($spacesFolder . '/' . $spaceFilePath, @file_get_contents($diskFilePath));
}

function getSpaceFilePath($filePath)
{
    $spacesFolder = getSpacesFolder();
    return 'https://whitelion.sgp1.digitaloceanspaces.com/' . $spacesFolder . '' . $filePath;
}

function loadTextLimit($string, $limit)
{
    $string = htmlspecialchars_decode($string);
    if (strlen($string) > $limit) {
        return substr($string, 0, $limit - 3) . '...';
    } else {
        return $string;
    }
}

function randomString($stringType, $stringLenth)
{
    if ($stringType == 'numeric') {
        $characters = '0123456789';
    } elseif ($stringType == 'alpha-numeric') {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    }
    $randstring = '';
    for ($i = 0; $i < $stringLenth; $i++) {
        $randstring .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randstring;
}

function websiteTimeZone()
{
    return 'Asia/Kolkata';
}

function convertDateTime($GMTDateTime)
{
    $TIMEZONE = websiteTimeZone();
    try {
        // $GMTDateTime = str_replace("T", " ", $GMTDateTime);
        // $GMTDateTime = explode(".", $GMTDateTime);
        // $GMTDateTime = $GMTDateTime[0];
        // print_r($GMTDateTime);
        // die;

        $dt = new DateTime('@' . strtotime($GMTDateTime));
        $dt->setTimeZone(new DateTimeZone($TIMEZONE));
        return $dt->format('d/m/Y h:i A');
    } catch (Exception $e) {
        return $GMTDateTime;
    }
}

function convertDateAndTime($GMTDateTime, $type)
{
    $TIMEZONE = websiteTimeZone();
    try {
        $dt = new DateTime('@' . strtotime($GMTDateTime));
        $dt->setTimeZone(new DateTimeZone($TIMEZONE));
        if ($type == 'date') {
            return $dt->format('d/m/Y');
        } elseif ($type == 'time') {
            return $dt->format('h:i A');
        }
    } catch (Exception $e) {
        return $GMTDateTime;
    }
}

function convertDateAndTimeMounth($GMTDateTime, $type)
{
    $TIMEZONE = websiteTimeZone();
    try {
        $dt = new DateTime('@' . strtotime($GMTDateTime));
        $dt->setTimeZone(new DateTimeZone($TIMEZONE));
        if ($type == 'date') {
            return $dt->format('d M Y');
        } elseif ($type == 'time') {
            return $dt->format('h:i A');
        }
    } catch (Exception $e) {
        return $GMTDateTime;
    }
}

function convertDateAndTime2($GMTDateTime, $type)
{
    if ($type == 'date') {
        return date('d M Y', strtotime($GMTDateTime));
    } elseif ($type == 'time') {
        return date('h:i A', strtotime($GMTDateTime));
    }
}

function convertOrderDateTime($GMTDateTime, $showType)
{
    $TIMEZONE = websiteTimeZone();
    try {
        $dt = new DateTime('@' . strtotime($GMTDateTime));
        $dt->setTimeZone(new DateTimeZone($TIMEZONE));

        if ($showType == 'date') {
            return $dt->format('d M y');
        } elseif ($showType == 'time') {
            return $dt->format('h:i:s A');
        }
    } catch (Exception $e) {
        return $GMTDateTime;
    }
}

function saveLeadTimeline($params)
{
    $LeadTimeline = new LeadTimeline();
    $LeadTimeline->user_id = Auth::user()->id;
    $LeadTimeline->type = $params['type'];
    $LeadTimeline->lead_id = $params['lead_id'];
    $LeadTimeline->reffrance_id = $params['reffrance_id'];
    $LeadTimeline->description = $params['description'];
    $LeadTimeline->source = $params['source'];
    $LeadTimeline->save();

    return $LeadTimeline;
}

function saveDebugLog($params)
{
    $DebugLog = new DebugLog();
    $DebugLog->user_id = Auth::user()->id;
    $DebugLog->name = $params['name'];
    $DebugLog->description = $params['description'];
    $DebugLog->save();
}

function saveProductLog($params)
{
    $DebugLog = new ProductLog();
    $DebugLog->product_inventory_id = $params['product_inventory_id'];
    $DebugLog->request_quantity = $params['request_quantity'];
    $DebugLog->quantity = $params['quantity'];
    //$DebugLog->user_id = 275;
    $DebugLog->user_id = Auth::user()->id;
    $DebugLog->name = $params['name'];
    $DebugLog->description = $params['description'];
    $DebugLog->save();
}

function saveMarketingProductLog($params)
{
    $DebugLog = new MarketingProductLog();
    $DebugLog->marketing_product_inventory_id = $params['marketing_product_inventory_id'];
    $DebugLog->request_quantity = $params['request_quantity'];
    $DebugLog->quantity = $params['quantity'];
    //$DebugLog->user_id = 275;
    $DebugLog->user_id = Auth::user()->id;
    $DebugLog->name = $params['name'];
    $DebugLog->description = $params['description'];
    $DebugLog->save();
}

function saveInquiryLog($params)
{
    $DebugLog = new InquiryLog();
    $DebugLog->inquiry_id = $params['inquiry_id'];
    $DebugLog->user_id = Auth::user()->id;
    $DebugLog->name = $params['name'];
    $DebugLog->description = $params['description'];
    $DebugLog->save();
}
function saveCRMUserLog($params)
{
    if (isset(Auth::user()->id) && Auth::user()->id != '') {
        $params['user_id'] = Auth::user()->id;
    }

    if (isset($params['inquiry_id'])) {
        $params['inquiry_id'] = $params['inquiry_id'];
    } else {
        $params['inquiry_id'] = 0;
    }

    if (isset($params['is_manually'])) {
        $params['is_manually'] = $params['is_manually'];
    } else {
        $params['is_manually'] = 0;
    }

    if (isset($params['points'])) {
        $params['points'] = $params['points'];
    } else {
        $params['points'] = 0;
    }

    if (isset($params['order_id'])) {
        $params['order_id'] = $params['order_id'];
    } else {
        $params['order_id'] = 0;
    }


    $DebugLog = new CRMLog();
    $DebugLog->user_id = $params['user_id'];
    $DebugLog->for_user_id = $params['for_user_id'];
    if ($params['type'] == "LEAD") {
        $DebugLog->lead_id = $params['inquiry_id'];
    } else {
        $DebugLog->inquiry_id = $params['inquiry_id'];
    }
    $DebugLog->is_manually = $params['is_manually'];
    $DebugLog->points = $params['points'];
    $DebugLog->order_id = $params['order_id'];
    $DebugLog->name = $params['name'];
    $DebugLog->description = $params['description'];
    $DebugLog->save();
}

function getCityName($cityId)
{
    $CityListName = '';

    $CityList = CityList::select('name')->find($cityId);
    if ($CityList) {
        $CityListName = $CityList->name;
    }

    return $CityListName;
}

function getStateName($stateId)
{
    $StateListName = '';

    $StateList = StateList::select('name')->find($stateId);
    if ($StateList) {
        $StateListName = $StateList->name;
    }

    return $StateListName;
}

function getCountryName($stateId)
{
    $CountryListName = '';

    $CountryList = CountryList::select('name')->find($stateId);
    if ($CountryList) {
        $CountryListName = $CountryList->name;
    }

    return $CountryListName;
}

function priceLable($price)
{
    return number_format($price, 2);
}

function productFeatureList()
{
    $featureList = [];
    $featureList[1]['id'] = 1;
    $featureList[1]['code'] = 'ON-OFF';
    $featureList[1]['display_name'] = 'On/Off';

    $featureList[2]['id'] = 2;
    $featureList[2]['code'] = 'FAN';
    $featureList[2]['display_name'] = 'Fan';

    $featureList[3]['id'] = 3;
    $featureList[3]['code'] = 'MASTER';
    $featureList[3]['display_name'] = 'Master';

    $featureList[4]['id'] = 4;
    $featureList[4]['code'] = 'CURTAIN';
    $featureList[4]['display_name'] = 'Curtain';

    $featureList[5]['id'] = 5;
    $featureList[5]['code'] = 'DIMMER';
    $featureList[5]['display_name'] = 'Dimmer';

    $featureList[6]['id'] = 5;
    $featureList[6]['code'] = 'BELL';
    $featureList[6]['display_name'] = 'Bell';

    $featureList[7]['id'] = 5;
    $featureList[7]['code'] = 'HL';
    $featureList[7]['display_name'] = 'HL';

    return $featureList;
}

function getCRMStageOfSiteStatusLable($setting)
{
    $setting = (int) $setting;

    if ($setting == 0) {
        $setting = '<span class="badge badge-pill badge-soft-danger font-size-11"> Inactive</span>';
    } elseif ($setting == 1) {
        $setting = '<span class="badge badge-pill badge-soft-success font-size-11"> Active</span>';
    }
    return $setting;
}

function getCRMSiteTypeStatusLable($setting)
{
    $setting = (int) $setting;

    if ($setting == 0) {
        $setting = '<span class="badge badge-pill badge-soft-danger font-size-11"> Inactive</span>';
    } elseif ($setting == 1) {
        $setting = '<span class="badge badge-pill badge-soft-success font-size-11"> Active</span>';
    }
    return $setting;
}
function getCRMBHKStatusLable($setting)
{
    $setting = (int) $setting;

    if ($setting == 0) {
        $setting = '<span class="badge badge-pill badge-soft-danger font-size-11"> Inactive</span>';
    } elseif ($setting == 1) {
        $setting = '<span class="badge badge-pill badge-soft-success font-size-11"> Active</span>';
    }
    return $setting;
}
function getCRMWantToCoverStatusLable($setting)
{
    $setting = (int) $setting;

    if ($setting == 0) {
        $setting = '<span class="badge badge-pill badge-soft-danger font-size-11"> Inactive</span>';
    } elseif ($setting == 1) {
        $setting = '<span class="badge badge-pill badge-soft-success font-size-11"> Active</span>';
    }
    return $setting;
}

function getCRMSouceTypeStatusLable($setting)
{
    $setting = (int) $setting;

    if ($setting == 0) {
        $setting = '<span class="badge badge-pill badge-soft-danger font-size-11"> Inactive</span>';
    } elseif ($setting == 1) {
        $setting = '<span class="badge badge-pill badge-soft-success font-size-11"> Active</span>';
    }
    return $setting;
}

function getCRMCompetitorsStatusLable($setting)
{
    $setting = (int) $setting;

    if ($setting == 0) {
        $setting = '<span class="badge badge-pill badge-soft-danger font-size-11"> Inactive</span>';
    } elseif ($setting == 1) {
        $setting = '<span class="badge badge-pill badge-soft-success font-size-11"> Active</span>';
    }
    return $setting;
}

function getCRMSourceStatusLable($setting)
{
    $setting = (int) $setting;

    if ($setting == 0) {
        $setting = '<span class="badge badge-pill badge-soft-danger font-size-11"> Inactive</span>';
    } elseif ($setting == 1) {
        $setting = '<span class="badge badge-pill badge-soft-success font-size-11"> Active</span>';
    }
    return $setting;
}

function getCRMSSubStatusLable($setting)
{
    $setting = (int) $setting;

    if ($setting == 0) {
        $setting = '<span class="badge badge-pill badge-soft-danger font-size-11"> Inactive</span>';
    } elseif ($setting == 1) {
        $setting = '<span class="badge badge-pill badge-soft-success font-size-11"> Active</span>';
    }
    return $setting;
}

function getCRMContactTagLable($setting)
{
    $setting = (int) $setting;

    if ($setting == 0) {
        $setting = '<span class="badge badge-pill badge-soft-danger font-size-11"> Inactive</span>';
    } elseif ($setting == 1) {
        $setting = '<span class="badge badge-pill badge-soft-success font-size-11"> Active</span>';
    }
    return $setting;
}

function getGiftOrderLable($OrderStatus)
{
    // code...
    $OrderStatus = (int) $OrderStatus;

    if ($OrderStatus == 0) {
        $OrderStatus = '<span class="badge badge-pill badge badge-soft-warning font-size-11"> PLACED / ON REVIEW</span>';
    } elseif ($OrderStatus == 1) {
        $OrderStatus = '<span class="badge badge-pill badge badge-soft-success font-size-11">  ACCEPTED</span>';
    } elseif ($OrderStatus == 2) {
        $OrderStatus = '<span class="badge badge-pill badge badge-soft-success font-size-11">  DISPATCHED</span>';
    } elseif ($OrderStatus == 3) {
        $OrderStatus = '<span class="badge badge-pill badge badge-soft-danger font-size-11">  REJECTED</span>';
    } elseif ($OrderStatus == 4) {
        $OrderStatus = '<span class="badge badge-pill badge badge-soft-primary font-size-11">  DELIEVERED</span>';
    } elseif ($OrderStatus == 5) {
        $OrderStatus = '<span class="badge badge-pill badge badge-soft-info font-size-11">  RECEIVED</span>';
    }

    return $OrderStatus;
}

function getOrderLable($OrderStatus)
{
    // code...
    $OrderStatus = (int) $OrderStatus;

    if ($OrderStatus == 0) {
        $OrderStatus = '<span class="badge badge-pill badge badge-soft-warning font-size-11"> PLACED</span>';
    } elseif ($OrderStatus == 1) {
        $OrderStatus = '<span class="badge badge-pill badge badge-soft-info font-size-11"> PROCESSING</span>';
    } elseif ($OrderStatus == 2) {
        $OrderStatus = '<span class="badge badge-pill badge badge-soft-orange font-size-11"> PARTIALLY DISPATCHED</span>';
    } elseif ($OrderStatus == 3) {
        $OrderStatus = '<span class="badge badge-pill badge badge-soft-success font-size-11"> FULLY DISPATCHED</span>';
    } elseif ($OrderStatus == 4) {
        $OrderStatus = '<span class="badge badge-pill badge badge-soft-danger font-size-11"> CANCELLED</span>';
    }
    return $OrderStatus;
}

function getOrderStatus($OrderStatus)
{
    // code...
    $OrderStatus = (int) $OrderStatus;

    if ($OrderStatus == 0) {
        $OrderStatus = 'PLACED';
    } elseif ($OrderStatus == 1) {
        $OrderStatus = 'PROCESSING';
    } elseif ($OrderStatus == 2) {
        $OrderStatus = 'PARTIALLY DISPATCHED';
    } elseif ($OrderStatus == 3) {
        $OrderStatus = 'FULLY DISPATCHED';
    } elseif ($OrderStatus == 4) {
        $OrderStatus = 'CANCELLED';
    }
    return $OrderStatus;
}

function getOrderStatusList()
{
    $ArchitectsStatus = [];
    $ArchitectsStatus[0]['id'] = 0;
    $ArchitectsStatus[0]['name'] = 'Placed';
    $ArchitectsStatus[0]['code'] = 'PLACED';

    $ArchitectsStatus[1]['id'] = 1;
    $ArchitectsStatus[1]['name'] = 'Processing';
    $ArchitectsStatus[1]['code'] = 'PROCESSING';

    $ArchitectsStatus[2]['id'] = 2;
    $ArchitectsStatus[2]['name'] = 'Partially Dispatched';
    $ArchitectsStatus[2]['code'] = 'PARTIALLY DISPATCHED';

    $ArchitectsStatus[3]['id'] = 3;
    $ArchitectsStatus[3]['name'] = 'Fully Dispatched';
    $ArchitectsStatus[3]['code'] = 'FULLY DISPATCHED';

    $ArchitectsStatus[4]['id'] = 4;
    $ArchitectsStatus[4]['name'] = 'Cancelled';
    $ArchitectsStatus[4]['code'] = 'CANCELLED';

    return $ArchitectsStatus;
}

function getMarketingRequestStatus($MarketingRequestStatus)
{
    // code...
    $MarketingRequestStatus = (int) $MarketingRequestStatus;

    if ($MarketingRequestStatus == 0) {
        $MarketingRequestStatus = 'REQUESTED';
    } elseif ($MarketingRequestStatus == 1) {
        $MarketingRequestStatus = 'APPROVED';
    } elseif ($MarketingRequestStatus == 2) {
        $MarketingRequestStatus = 'PARTIALLY APPROVED';
    } elseif ($MarketingRequestStatus == 3) {
        $MarketingRequestStatus = 'REJECTED';
    }
    return $MarketingRequestStatus;
}

function getMarketingRequestDelieveryChallanStatus($MarketingDelieveryChallanStatus)
{
    // code...
    $MarketingDelieveryChallanStatus = (int) $MarketingDelieveryChallanStatus;

    if ($MarketingDelieveryChallanStatus == 0) {
        $MarketingDelieveryChallanStatus = 'CHALLAN RAISED';
    } elseif ($MarketingDelieveryChallanStatus == 1) {
        $MarketingDelieveryChallanStatus = 'PACKED';
    } elseif ($MarketingDelieveryChallanStatus == 2) {
        $MarketingDelieveryChallanStatus = 'DISPATCHED';
    }
    return $MarketingDelieveryChallanStatus;
}

function getInvoiceLable($invoiceStatus)
{
    if ($invoiceStatus == 0) {
        $invoiceStatus = '<span class="badge badge-pill badge bg-primary font-size-11">INVOICE RAISED</span>';
    } elseif ($invoiceStatus == 1) {
        $invoiceStatus = '<span class="badge badge-pill badge-soft-info font-size-11"> PACKED</span>';
    } elseif ($invoiceStatus == 2) {
        $invoiceStatus = '<span class="badge badge-pill badge-soft-success font-size-11"> DISPATCHED</span>';
    } elseif ($invoiceStatus == 3) {
        $invoiceStatus = '<span class="badge badge-pill badge-soft-dark font-size-11"> RECIEVED</span>';
    } elseif ($invoiceStatus == 4) {
        $invoiceStatus = '<span class="badge badge-pill badge-soft-danger font-size-11"> CANCELLED</span>';
    }
    return $invoiceStatus;
    // code...
}

function getInvoiceStatus($invoiceStatus)
{
    if ($invoiceStatus == 0) {
        $invoiceStatus = 'INVOICE RAISED';
    } elseif ($invoiceStatus == 1) {
        $invoiceStatus = 'PACKED';
    } elseif ($invoiceStatus == 2) {
        $invoiceStatus = 'DISPATCHED';
    } elseif ($invoiceStatus == 3) {
        $invoiceStatus = 'RECIEVED';
    } elseif ($invoiceStatus == 4) {
        $invoiceStatus = 'CANCELLED';
    }
    return $invoiceStatus;
    // code...
}

function getPaymentModeName($paymentMode)
{
    if ($paymentMode == 0) {
        $paymentMode = 'PDC';
    } elseif ($paymentMode == 1) {
        $paymentMode = 'ADVANCE';
    } elseif ($paymentMode == 2) {
        $paymentMode = 'CREDIT';
    }
    return $paymentMode;
}

function getPaymentLable($paymentMode)
{
    if ($paymentMode == 0) {
        $paymentMode = '<span class="badge badge-pill badge-soft-danger font-size-11">PDC</span>';
    } elseif ($paymentMode == 1) {
        $paymentMode = '<span class="badge badge-pill badge-soft-danger font-size-11"> ADVANCE</span>';
    } elseif ($paymentMode == 2) {
        $paymentMode = '<span class="badge badge-pill badge-soft-success font-size-11"> CREDIT</span>';
    }
    return $paymentMode;
}

function getProductGroupLable($ProductGroupStatus)
{
    // code...
    $ProductGroupStatus = (int) $ProductGroupStatus;

    if ($ProductGroupStatus == 0) {
        $ProductGroupStatus = '<span class="badge badge-pill badge-soft-danger font-size-11"> Inactive</span>';
    } elseif ($ProductGroupStatus == 1) {
        $ProductGroupStatus = '<span class="badge badge-pill badge-soft-success font-size-11"> Active</span>';
    }
    return $ProductGroupStatus;
}

function getDataMasterStatusLable($dataMasterStatus)
{
    // code...
    $dataMasterStatus = (int) $dataMasterStatus;

    if ($dataMasterStatus == 0) {
        $dataMasterStatus = '<span class="badge badge-pill badge-soft-danger font-size-11"> Inactive</span>';
    } elseif ($dataMasterStatus == 1) {
        $dataMasterStatus = '<span class="badge badge-pill badge-soft-success font-size-11"> Active</span>';
    } elseif ($dataMasterStatus == 2) {
        $dataMasterStatus = '<span class="badge badge-pill badge-soft-danger font-size-11"> Blocked</span>';
    }
    return $dataMasterStatus;
}

function getMainMasterStatusLable($mainMasterStatus)
{
    $mainMasterStatus = (int) $mainMasterStatus;

    if ($mainMasterStatus == 0) {
        $mainMasterStatus = '<span class="badge badge-pill badge-soft-danger font-size-11"> Inactive</span>';
    } elseif ($mainMasterStatus == 1) {
        $mainMasterStatus = '<span class="badge badge-pill badge-soft-success font-size-11"> Active</span>';
    } elseif ($mainMasterStatus == 2) {
        $mainMasterStatus = '<span class="badge badge-pill badge-soft-danger font-size-11"> Blocked</span>';
    }
    return $mainMasterStatus;
}

function getExhibitionStatusLable($exhibitionStatus)
{
    $exhibitionStatus = (int) $exhibitionStatus;

    if ($exhibitionStatus == 0) {
        $exhibitionStatus = '<span class="badge badge-pill badge-soft-danger font-size-11"> Completed</span>';
    } elseif ($exhibitionStatus == 1) {
        $exhibitionStatus = '<span class="badge badge-pill badge-soft-success font-size-11">Live/Upcoming</span>';
    }
    return $exhibitionStatus;
}

function getSalesHierarchyStatusLable($salesHierarchyStatus)
{
    $salesHierarchyStatus = (int) $salesHierarchyStatus;

    if ($salesHierarchyStatus == 0) {
        $salesHierarchyStatus = '<span class="badge badge-pill badge-soft-danger font-size-11"> Inactive</span>';
    } elseif ($salesHierarchyStatus == 1) {
        $salesHierarchyStatus = '<span class="badge badge-pill badge-soft-success font-size-11"> Active</span>';
    } elseif ($salesHierarchyStatus == 2) {
        $salesHierarchyStatus = '<span class="badge badge-pill badge-soft-danger font-size-11"> Blocked</span>';
    }
    return $salesHierarchyStatus;
}

function getPurchaseHierarchyStatusLable($salesHierarchyStatus)
{
    $salesHierarchyStatus = (int) $salesHierarchyStatus;

    if ($salesHierarchyStatus == 0) {
        $salesHierarchyStatus = '<span class="badge badge-pill badge-soft-danger font-size-11"> Inactive</span>';
    } elseif ($salesHierarchyStatus == 1) {
        $salesHierarchyStatus = '<span class="badge badge-pill badge-soft-success font-size-11"> Active</span>';
    } elseif ($salesHierarchyStatus == 2) {
        $salesHierarchyStatus = '<span class="badge badge-pill badge-soft-danger font-size-11"> Blocked</span>';
    }
    return $salesHierarchyStatus;
}

function getCityStatusLable($cityStatus)
{
    $cityStatus = (int) $cityStatus;

    if ($cityStatus == 0) {
        $cityStatus = '<span class="badge badge-pill badge-soft-danger font-size-11"> Inactive</span>';
    } elseif ($cityStatus == 1) {
        $cityStatus = '<span class="badge badge-pill badge-soft-success font-size-11"> Active</span>';
    } elseif ($cityStatus == 2) {
        $cityStatus = '<span class="badge badge-pill badge-soft-danger font-size-11"> Blocked</span>';
    }
    return $cityStatus;
}

function getArchitectsStatus()
{
    $ArchitectsStatus = [];
    $ArchitectsStatus[2]['id'] = 2;
    $ArchitectsStatus[2]['name'] = 'Pending';
    $ArchitectsStatus[2]['code'] = 'Pending';
    $ArchitectsStatus[2]['header_code'] = 'Entry';
    $ArchitectsStatus[2]['sequence_id'] = 1;
    $ArchitectsStatus[2]['access_user_type'] = [0, 9];

    $ArchitectsStatus[4]['id'] = 4;
    $ArchitectsStatus[4]['name'] = 'Approved';
    $ArchitectsStatus[4]['code'] = 'Telecaller Approved';
    $ArchitectsStatus[4]['header_code'] = 'Verified by Telecaller';
    $ArchitectsStatus[4]['sequence_id'] = 2;
    $ArchitectsStatus[4]['access_user_type'] = [9];

    $ArchitectsStatus[1]['id'] = 1;
    $ArchitectsStatus[1]['name'] = 'Approved';
    $ArchitectsStatus[1]['code'] = 'HOD Approved';
    $ArchitectsStatus[1]['header_code'] = 'On Boarded';
    $ArchitectsStatus[1]['sequence_id'] = 3;
    $ArchitectsStatus[1]['access_user_type'] = [0];

    $ArchitectsStatus[0]['id'] = 0;
    $ArchitectsStatus[0]['name'] = 'Reject';
    $ArchitectsStatus[0]['code'] = 'Reject';
    $ArchitectsStatus[0]['header_code'] = 'Rejected';
    $ArchitectsStatus[0]['sequence_id'] = 4;
    $ArchitectsStatus[0]['access_user_type'] = [0, 9];

    $ArchitectsStatus[3]['id'] = 3;
    $ArchitectsStatus[3]['name'] = 'Data Mismatch';
    $ArchitectsStatus[3]['code'] = 'Data Mismatch';
    $ArchitectsStatus[3]['header_code'] = 'Data Mismatch';
    $ArchitectsStatus[3]['sequence_id'] = 5;
    $ArchitectsStatus[3]['access_user_type'] = [0, 9];

    $ArchitectsStatus[5]['id'] = 5;
    $ArchitectsStatus[5]['name'] = 'Duplicate';
    $ArchitectsStatus[5]['code'] = 'Duplicate';
    $ArchitectsStatus[5]['header_code'] = 'Duplicate';
    $ArchitectsStatus[5]['sequence_id'] = 7;
    $ArchitectsStatus[5]['access_user_type'] = [0, 9];

    $ArchitectsStatus[7]['id'] = 7;
    $ArchitectsStatus[7]['name'] = 'Not Recieved';
    $ArchitectsStatus[7]['code'] = 'Not Recieved';
    $ArchitectsStatus[7]['header_code'] = 'Not Recieved';
    $ArchitectsStatus[7]['sequence_id'] = 8;
    $ArchitectsStatus[7]['access_user_type'] = [9];

    $ArchitectsStatus[8]['id'] = 8;
    $ArchitectsStatus[8]['name'] = 'Data Pending';
    $ArchitectsStatus[8]['code'] = 'Data Pending';
    $ArchitectsStatus[8]['header_code'] = 'Data Pending';
    $ArchitectsStatus[8]['sequence_id'] = 9;
    $ArchitectsStatus[8]['access_user_type'] = [9];

    $ArchitectsStatus[9]['id'] = 9;
    $ArchitectsStatus[9]['name'] = 'Language Issue';
    $ArchitectsStatus[9]['code'] = 'Language Issue';
    $ArchitectsStatus[9]['header_code'] = 'Language Issue';
    $ArchitectsStatus[9]['sequence_id'] = 9;
    $ArchitectsStatus[9]['access_user_type'] = [9];

    return $ArchitectsStatus;
}

function getElectricianStatus()
{
    $ArchitectsStatus = [];
    $ArchitectsStatus[2]['id'] = 2;
    $ArchitectsStatus[2]['name'] = 'Pending';
    $ArchitectsStatus[2]['code'] = 'Pending';
    $ArchitectsStatus[2]['header_code'] = 'Entry';
    $ArchitectsStatus[2]['sequence_id'] = 1;
    $ArchitectsStatus[2]['access_user_type'] = [0, 9];

    $ArchitectsStatus[4]['id'] = 4;
    $ArchitectsStatus[4]['name'] = 'Approved';
    $ArchitectsStatus[4]['code'] = 'Telecaller Approved';
    $ArchitectsStatus[4]['header_code'] = 'Verified by Telecaller';
    $ArchitectsStatus[4]['sequence_id'] = 2;
    $ArchitectsStatus[4]['access_user_type'] = [9];

    $ArchitectsStatus[1]['id'] = 1;
    $ArchitectsStatus[1]['name'] = 'Approved';
    $ArchitectsStatus[1]['code'] = 'HOD Approved';
    $ArchitectsStatus[1]['header_code'] = 'On Boarded';
    $ArchitectsStatus[1]['sequence_id'] = 3;
    $ArchitectsStatus[1]['access_user_type'] = [0];

    $ArchitectsStatus[0]['id'] = 0;
    $ArchitectsStatus[0]['name'] = 'Reject';
    $ArchitectsStatus[0]['code'] = 'Reject';
    $ArchitectsStatus[0]['header_code'] = 'Rejected';
    $ArchitectsStatus[0]['sequence_id'] = 4;
    $ArchitectsStatus[0]['access_user_type'] = [0, 9];

    $ArchitectsStatus[3]['id'] = 3;
    $ArchitectsStatus[3]['name'] = 'Data Mismatch';
    $ArchitectsStatus[3]['code'] = 'Data Mismatch';
    $ArchitectsStatus[3]['header_code'] = 'Data Mismatch';
    $ArchitectsStatus[3]['sequence_id'] = 5;
    $ArchitectsStatus[3]['access_user_type'] = [0, 9];

    $ArchitectsStatus[5]['id'] = 5;
    $ArchitectsStatus[5]['name'] = 'Duplicate';
    $ArchitectsStatus[5]['code'] = 'Duplicate';
    $ArchitectsStatus[5]['header_code'] = 'Duplicate';
    $ArchitectsStatus[5]['sequence_id'] = 7;
    $ArchitectsStatus[5]['access_user_type'] = [0, 9];

    $ArchitectsStatus[7]['id'] = 7;
    $ArchitectsStatus[7]['name'] = 'Not Recieved';
    $ArchitectsStatus[7]['code'] = 'Not Recieved';
    $ArchitectsStatus[7]['header_code'] = 'Not Recieved';
    $ArchitectsStatus[7]['sequence_id'] = 8;
    $ArchitectsStatus[7]['access_user_type'] = [9];

    $ArchitectsStatus[8]['id'] = 8;
    $ArchitectsStatus[8]['name'] = 'Data Pending';
    $ArchitectsStatus[8]['code'] = 'Data Pending';
    $ArchitectsStatus[8]['header_code'] = 'Data Pending';
    $ArchitectsStatus[8]['sequence_id'] = 9;
    $ArchitectsStatus[8]['access_user_type'] = [9];

    $ArchitectsStatus[9]['id'] = 9;
    $ArchitectsStatus[9]['name'] = 'Language Issue';
    $ArchitectsStatus[9]['code'] = 'Language Issue';
    $ArchitectsStatus[9]['header_code'] = 'Language Issue';
    $ArchitectsStatus[9]['sequence_id'] = 9;
    $ArchitectsStatus[9]['access_user_type'] = [9];

    return $ArchitectsStatus;
}

function getArchitectsStatusLable($architectsStatus)
{
    $architectsStatus = (int) $architectsStatus;
    if ($architectsStatus == 0) {
        $architectsStatus = '<span class="badge badge-pill badge-soft-danger font-size-11">Rejected</span>';
    } elseif ($architectsStatus == 1) {
        $architectsStatus = '<span class="badge badge-pill badge-soft-success font-size-11">On Borded</span>';
    } elseif ($architectsStatus == 2) {
        $architectsStatus = '<span class="badge badge-pill badge-soft-danger font-size-11">Entry</span>';
    } elseif ($architectsStatus == 3) {
        $architectsStatus = '<span class="badge badge-pill badge-soft-danger font-size-11">Data Mismatch</span>';
    } elseif ($architectsStatus == 4) {
        $architectsStatus = '<span class="badge badge-pill badge-soft-danger font-size-11">Verified by Telecaller</span>';
    } elseif ($architectsStatus == 5) {
        $architectsStatus = '<span class="badge badge-pill badge-soft-danger font-size-11">Duplicate</span>';
    } elseif ($architectsStatus == 7) {
        $architectsStatus = '<span class="badge badge-pill badge-soft-danger font-size-11">Not Recieved</span>';
    } elseif ($architectsStatus == 8) {
        $architectsStatus = '<span class="badge badge-pill badge-soft-danger font-size-11">Data Pending</span>';
    } elseif ($architectsStatus == 9) {
        $architectsStatus = '<span class="badge badge-pill badge-soft-danger font-size-11">Language Issue</span>';
    }
    return $architectsStatus;
}

function getElectricianStatusStatusLable($architectsStatus)
{
    $architectsStatus = (int) $architectsStatus;
    if ($architectsStatus == 0) {
        $architectsStatus = '<span class="badge badge-pill badge-soft-danger font-size-11"> Rejected</span>';
    } elseif ($architectsStatus == 1) {
        $architectsStatus = '<span class="badge badge-pill badge-soft-success font-size-11"> On Borded</span>';
    } elseif ($architectsStatus == 2) {
        $architectsStatus = '<span class="badge badge-pill badge-soft-danger font-size-11"> Entry</span>';
    } elseif ($architectsStatus == 3) {
        $architectsStatus = '<span class="badge badge-pill badge-soft-danger font-size-11">Data Mismatch</span>';
    } elseif ($architectsStatus == 4) {
        $architectsStatus = '<span class="badge badge-pill badge-soft-danger font-size-11">Verified by Telecaller</span>';
    } elseif ($architectsStatus == 5) {
        $architectsStatus = '<span class="badge badge-pill badge-soft-danger font-size-11">Duplicate</span>';
    } elseif ($architectsStatus == 7) {
        $architectsStatus = '<span class="badge badge-pill badge-soft-danger font-size-11">Not Recieved</span>';
    } elseif ($architectsStatus == 8) {
        $architectsStatus = '<span class="badge badge-pill badge-soft-danger font-size-11">Data Pending</span>';
    } elseif ($architectsStatus == 9) {
        $architectsStatus = '<span class="badge badge-pill badge-soft-danger font-size-11">Language Issue</span>';
    }
    return $architectsStatus;
}
// function getArchitectsStatusLable($architectsStatus)
// {
// 	$architectsStatus = (int) $architectsStatus;
// 	if ($architectsStatus == 0) {
// 		$architectsStatus = '<span class="badge badge-pill badge-soft-danger font-size-11"> Rejected</span>';
// 	} else if ($architectsStatus == 1) {
// 		$architectsStatus = '<span class="badge badge-pill badge-soft-success font-size-11"> On Borded</span>';
// 	} else if ($architectsStatus == 2) {
// 		$architectsStatus = '<span class="badge badge-pill badge-soft-danger font-size-11"> Entry</span>';
// 	} else if ($architectsStatus == 3) {
// 		$architectsStatus = '<span class="badge badge-pill badge-soft-danger font-size-11">Data Mismatch</span>';
// 	} else if ($architectsStatus == 4) {
// 		$architectsStatus = '<span class="badge badge-pill badge-soft-danger font-size-11">Verified by Telecaller</span>';
// 	}
// 	return $architectsStatus;
// }

function userStatus()
{
    $userTypes = [];
    $userTypes[0]['id'] = 0;
    $userTypes[0]['name'] = 'Inactive';

    $userTypes[1]['id'] = 1;
    $userTypes[1]['name'] = 'Active';

    $userTypes[2]['id'] = 2;
    $userTypes[2]['name'] = 'Pending';
    return $userTypes;
}

function getUserStatusLable($userStatus)
{
    $userStatus = (int) $userStatus;
    if ($userStatus == 0) {
        $userStatus = '<span class="badge badge-pill badge-soft-danger font-size-11"> Inactive</span>';
    } elseif ($userStatus == 1) {
        $userStatus = '<span class="badge badge-pill badge-soft-success font-size-11">Active</span>';
    } elseif ($userStatus == 2) {
        $userStatus = '<span class="badge badge-pill badge-soft-danger font-size-11">Pending</span>';
    }
    return $userStatus;
}


function getUserStatus($userStatus)
{
    $userStatus = (int) $userStatus;
    if ($userStatus == 0) {
        $userStatus = 'Inactive';
    } elseif ($userStatus == 1) {
        $userStatus = 'Active';
    } elseif ($userStatus == 2) {
        $userStatus = 'Pending';
    }
    return $userStatus;
}

function getCompanyStatusLable($companyStatus)
{
    $companyStatus = (int) $companyStatus;
    if ($companyStatus == 0) {
        $companyStatus = '<span class="badge badge-pill badge-soft-danger font-size-11"> Inactive</span>';
    } elseif ($companyStatus == 1) {
        $companyStatus = '<span class="badge badge-pill badge-soft-success font-size-11"> Active</span>';
    } elseif ($companyStatus == 2) {
        $companyStatus = '<span class="badge badge-pill badge-soft-danger font-size-11"> Blocked</span>';
    }
    return $companyStatus;
}

function getGiftCategoryStatusLable($GiftCategoryStatus)
{
    $GiftCategoryStatus = (int) $GiftCategoryStatus;
    if ($GiftCategoryStatus == 0) {
        $GiftCategoryStatus = '<span class="badge badge-pill badge-soft-danger font-size-11"> Inactive</span>';
    } elseif ($GiftCategoryStatus == 1) {
        $GiftCategoryStatus = '<span class="badge badge-pill badge-soft-success font-size-11"> Active</span>';
    }
    return $GiftCategoryStatus;
}

function getGiftProductStatusLable($GiftCategoryStatus)
{
    $GiftCategoryStatus = (int) $GiftCategoryStatus;
    if ($GiftCategoryStatus == 0) {
        $GiftCategoryStatus = '<span class="badge badge-pill badge-soft-danger font-size-11"> Inactive</span>';
    } elseif ($GiftCategoryStatus == 1) {
        $GiftCategoryStatus = '<span class="badge badge-pill badge-soft-success font-size-11"> Active</span>';
    }
    return $GiftCategoryStatus;
}

function getCRMHelpDocumentStatusLable($HelpDocumentStatus)
{
    $HelpDocumentStatus = (int) $HelpDocumentStatus;
    if ($HelpDocumentStatus == 0) {
        $HelpDocumentStatus = '<span class="badge badge-pill badge-soft-danger font-size-11"> Inactive</span>';
    } elseif ($HelpDocumentStatus == 1) {
        $HelpDocumentStatus = '<span class="badge badge-pill badge-soft-success font-size-11"> Active</span>';
    }
    return $HelpDocumentStatus;
    // code...
}

/////

function getUserTypes()
{
    $userTypes = [];
    $userTypes[0]['id'] = 0;
    $userTypes[0]['name'] = 'Admin';
    $userTypes[0]['short_name'] = 'ADMIN';
    $userTypes[0]['lable'] = 'user-admin';
    $userTypes[0]['key'] = 't-user-admin';
    $userTypes[0]['url'] = route('users.admin');
    $userTypes[0]['can_login'] = 1;

    $userTypes[1]['id'] = 1;
    $userTypes[1]['name'] = 'Company Admin';
    $userTypes[1]['short_name'] = 'COMPANY ADMIN';
    $userTypes[1]['lable'] = 'user-company-admin';
    $userTypes[1]['key'] = 't-user-company-admin';
    $userTypes[1]['url'] = route('users.company.admin');
    $userTypes[1]['can_login'] = 1;

    $userTypes[2]['id'] = 2;
    $userTypes[2]['name'] = 'Sales';
    $userTypes[2]['short_name'] = 'SALES';
    $userTypes[2]['lable'] = 'user-sales';
    $userTypes[2]['key'] = 't-user-sale-person';
    $userTypes[2]['url'] = route('users.sale.person');
    $userTypes[2]['can_login'] = 1;

    $userTypes[3]['id'] = 3;
    $userTypes[3]['name'] = 'Account';
    $userTypes[3]['short_name'] = 'ACCOUNT';
    $userTypes[3]['lable'] = 'user-account';
    $userTypes[3]['key'] = 't-user-account-user';
    $userTypes[3]['url'] = route('users.account');
    $userTypes[3]['can_login'] = 1;

    $userTypes[4]['id'] = 4;
    $userTypes[4]['name'] = 'Dispatcher';
    $userTypes[4]['short_name'] = 'DISPATCHER';
    $userTypes[4]['lable'] = 'user-dispatcher';
    $userTypes[4]['key'] = 't-user-dispatcher-user';
    $userTypes[4]['url'] = route('users.dispatcher');
    $userTypes[4]['can_login'] = 1;

    $userTypes[5]['id'] = 5;
    $userTypes[5]['name'] = 'Production';
    $userTypes[5]['short_name'] = 'PRODUCTION';
    $userTypes[5]['lable'] = 'user-production';
    $userTypes[5]['key'] = 't-user-production-user';
    $userTypes[5]['url'] = route('users.production');
    $userTypes[5]['can_login'] = 1;

    $userTypes[6]['id'] = 6;
    $userTypes[6]['name'] = 'Marketing';
    $userTypes[6]['short_name'] = 'MARKETING';
    $userTypes[6]['lable'] = 'user-marketing';
    $userTypes[6]['key'] = 't-user-marketing-user';
    $userTypes[6]['url'] = route('users.marketing');
    $userTypes[6]['can_login'] = 1;

    $userTypes[7]['id'] = 7;
    $userTypes[7]['name'] = 'Marketing - Dispatcher ';
    $userTypes[7]['short_name'] = 'MARKETING - DISPATCHER';
    $userTypes[7]['lable'] = 'user-marketing-dispatcher';
    $userTypes[7]['key'] = 't-user-marketing-user-dispatcher';
    $userTypes[7]['url'] = route('users.marketing.dispatcher');
    $userTypes[7]['can_login'] = 1;

    $userTypes[8]['id'] = 8;
    $userTypes[8]['name'] = 'Third Party';
    $userTypes[8]['short_name'] = 'THIRD PARTY';
    $userTypes[8]['lable'] = 'user-third-party';
    $userTypes[8]['key'] = 't-user-third-party';
    $userTypes[8]['url'] = route('users.thirdparty');
    $userTypes[8]['can_login'] = 1;

    $userTypes[9]['id'] = 9;
    $userTypes[9]['name'] = 'Tele Sales';
    $userTypes[9]['short_name'] = 'TELE SALE';
    $userTypes[9]['lable'] = 'user-tele-sale';
    $userTypes[9]['key'] = 't-user-tele-tele';
    $userTypes[9]['url'] = route('users.tele.sale');
    $userTypes[9]['can_login'] = 1;

    $userTypes[10]['id'] = 10;
    $userTypes[10]['name'] = 'Purchase';
    $userTypes[10]['short_name'] = 'PURCHASE';
    $userTypes[10]['lable'] = 'user-purchase';
    $userTypes[10]['key'] = 't-user-purchase-person';
    $userTypes[10]['url'] = route('users.purchase.person');
    $userTypes[10]['can_login'] = 1;

    $userTypes[11]['id'] = 11;
    $userTypes[11]['name'] = 'Service User';
    $userTypes[11]['short_name'] = 'SERVICE USER';
    $userTypes[11]['lable'] = 'user-service-executive';
    $userTypes[11]['key'] = 't-user-service-executive';
    $userTypes[11]['url'] = route('users.service.executive');
    $userTypes[11]['can_login'] = 1;

    $userTypes[12]['id'] = 12;
    $userTypes[12]['name'] = 'Reception';
    $userTypes[12]['short_name'] = 'RECEPTION USER';
    $userTypes[12]['lable'] = 'user-reception';
    $userTypes[12]['key'] = 't-user-reception';
    $userTypes[12]['url'] = route('users.reception');
    $userTypes[12]['can_login'] = 1;

    $userTypes[13]['id'] = 13;
    $userTypes[13]['name'] = 'CRE';
    $userTypes[13]['short_name'] = 'CRE USER';
    $userTypes[13]['lable'] = 'user-cre';
    $userTypes[13]['key'] = 't-user-cre';
    $userTypes[13]['url'] = route('users.cre');
    $userTypes[13]['can_login'] = 1;

    $userTypes[777]['id'] = 777;
    $userTypes[777]['name'] = 'Marketing Person';
    $userTypes[777]['short_name'] = 'MARKETING PERSON';
    $userTypes[777]['lable'] = 'user-marketing-person';
    $userTypes[777]['key'] = 't-user-marketing-person';
    $userTypes[777]['url'] = '';
    $userTypes[777]['can_login'] = 1;

    return $userTypes;
}

function getChannelPartners()
{
    $userTypes = [];
    $userTypes[101]['id'] = 101;
    $userTypes[101]['name'] = 'ASM(Authorize Stocklist Merchantize)';
    $userTypes[101]['lable'] = 'channel-partner-asm';
    $userTypes[101]['short_name'] = 'ASM';
    $userTypes[101]['key'] = 't-channel-partner-stockist';
    $userTypes[101]['url'] = route('channel.partners.stockist');
    $userTypes[101]['url_view'] = route('channel.partners.stockist.view');
    $userTypes[101]['url_sub_orders'] = route('orders.sub.asm');
    $userTypes[101]['can_login'] = 1;
    $userTypes[101]['inquiry_tab'] = 1;

    $userTypes[102]['id'] = 102;
    $userTypes[102]['name'] = 'ADM(Authorize Distributor Merchantize)';
    $userTypes[102]['lable'] = 'channel-partner-adm';
    $userTypes[102]['short_name'] = 'ADM';
    $userTypes[102]['key'] = 't-channel-partner-adm';
    $userTypes[102]['url'] = route('channel.partners.adm');
    $userTypes[102]['url_view'] = route('channel.partners.adm.view');
    $userTypes[102]['url_sub_orders'] = route('orders.sub.adm');
    $userTypes[102]['can_login'] = 1;
    $userTypes[102]['inquiry_tab'] = 1;

    $userTypes[103]['id'] = 103;
    $userTypes[103]['name'] = 'APM(Authorize Project Merchantize)';
    $userTypes[103]['lable'] = 'channel-partner-apm';
    $userTypes[103]['short_name'] = 'APM';
    $userTypes[103]['key'] = 't-channel-partner-apm';
    $userTypes[103]['url'] = route('channel.partners.apm');
    $userTypes[103]['url_view'] = route('channel.partners.apm.view');
    $userTypes[103]['url_sub_orders'] = route('orders.sub.apm');
    $userTypes[103]['can_login'] = 1;
    $userTypes[103]['inquiry_tab'] = 1;

    $userTypes[104]['id'] = 104;
    $userTypes[104]['name'] = 'AD(Authorised Dealer)';
    $userTypes[104]['lable'] = 'channel-partner-ad';
    $userTypes[104]['short_name'] = 'AD';
    $userTypes[104]['key'] = 't-channel-partner-ad';
    $userTypes[104]['url'] = route('channel.partners.ad');
    $userTypes[104]['url_view'] = route('channel.partners.ad.view');
    $userTypes[104]['url_sub_orders'] = route('orders.sub.ad');
    $userTypes[104]['can_login'] = 1;
    $userTypes[104]['inquiry_tab'] = 1;

    $userTypes[105]['id'] = 105;
    $userTypes[105]['name'] = 'Retailer';
    $userTypes[105]['lable'] = 'channel-partner-retailer';
    $userTypes[105]['short_name'] = 'Retailer';
    $userTypes[105]['key'] = 't-channel-partner-retailer';
    $userTypes[105]['url'] = route('channel.partners.retailer');
    $userTypes[105]['url_view'] = route('channel.partners.retailer.view');
    $userTypes[105]['url_sub_orders'] = route('orders.sub.retailer');
    $userTypes[105]['can_login'] = 1;
    $userTypes[105]['inquiry_tab'] = 1;

    $userTypes[106]['id'] = 106;
    $userTypes[106]['name'] = 'AFM(Authorize Franchisee Merchantize)';
    $userTypes[106]['lable'] = 'channel-partner-afm';
    $userTypes[106]['short_name'] = 'AFM';
    $userTypes[106]['key'] = 't-channel-partner-afm';
    $userTypes[106]['url'] = route('channel.partners.afm');
    $userTypes[106]['url_view'] = route('channel.partners.afm.view');
    $userTypes[106]['url_sub_orders'] = route('orders.sub.afm');
    $userTypes[106]['can_login'] = 1;
    $userTypes[106]['inquiry_tab'] = 1;
    return $userTypes;
}

function getArchitects()
{
    $userTypes = [];
    $userTypes[201]['id'] = 201;
    $userTypes[201]['name'] = 'Architect(Non Prime)';
    $userTypes[201]['lable'] = 'architect-non-prime';
    $userTypes[201]['short_name'] = 'NON PRIME';
    $userTypes[201]['another_name'] = 'ARCHITECT';
    $userTypes[201]['url'] = route('architects.prime');
    //$userTypes[201]['url'] = route('architects.non.prime');
    $userTypes[201]['can_login'] = 0;

    $userTypes[202]['id'] = 202;
    $userTypes[202]['name'] = 'Architect(Prime)';
    $userTypes[202]['lable'] = 'architect-prime';
    $userTypes[202]['short_name'] = 'PRIME';
    $userTypes[202]['another_name'] = 'ARCHITECT';
    $userTypes[202]['url'] = route('architects.prime');
    $userTypes[202]['can_login'] = 1;

    return $userTypes;
}

function getElectricians()
{
    $userTypes = [];
    $userTypes[301]['id'] = 301;
    $userTypes[301]['name'] = 'Electrician(Non Prime)';
    $userTypes[301]['lable'] = 'electrician-non-prime';
    $userTypes[301]['short_name'] = 'NON PRIME';
    $userTypes[301]['another_name'] = 'ELECTRICIAN';
    //$userTypes[301]['url'] = route('electricians.non.prime');
    $userTypes[301]['url'] = route('electricians.prime');
    $userTypes[301]['can_login'] = 0;

    $userTypes[302]['id'] = 302;
    $userTypes[302]['name'] = 'Electrician(Prime)';
    $userTypes[302]['lable'] = 'electrician-prime';
    $userTypes[302]['short_name'] = 'PRIME';
    $userTypes[302]['another_name'] = 'ELECTRICIAN';
    $userTypes[302]['url'] = route('electricians.prime');
    $userTypes[302]['can_login'] = 1;

    return $userTypes;
}

function CRMUserType()
{
    $userTypes = [];

    $userTypes[202]['id'] = 202;
    $userTypes[202]['name'] = 'Architect(Prime)';
    $userTypes[202]['lable'] = 'architect-prime';
    $userTypes[202]['short_name'] = 'PRIME';
    $userTypes[202]['another_name'] = 'ARCHITECT';

    $userTypes[302]['can_login'] = 1;
    $userTypes[302]['id'] = 302;
    $userTypes[302]['name'] = 'Electrician(Prime)';
    $userTypes[302]['lable'] = 'electrician-prime';
    $userTypes[302]['short_name'] = 'PRIME';
    $userTypes[302]['another_name'] = 'ELECTRICIAN';

    return $userTypes;
}

function getCustomers()
{
    $userTypes = [];
    $userTypes[10000]['id'] = 10000;
    $userTypes[10000]['name'] = 'User';
    $userTypes[10000]['lable'] = 'user';
    $userTypes[10000]['short_name'] = 'USER';
    $userTypes[10000]['another_name'] = 'USER';
    $userTypes[10000]['can_login'] = 0;
    return $userTypes;
}

function getInquiryStatus()
{
    //Inquiry status type
    $inquiryStatus = [];
    $inquiryStatus[202]['id'] = 202;
    $inquiryStatus[202]['name'] = 'Focus';
    $inquiryStatus[202]['key'] = 't-inquiry-focus';
    $inquiryStatus[202]['for_architect_ids'] = [0];
    $inquiryStatus[202]['for_electrician_ids'] = [0];
    $inquiryStatus[202]['for_user_ids'] = [1, 2, 3, 4, 5, 6, 7, 8];
    $inquiryStatus[202]['for_third_party_ids'] = [1, 2, 3, 4, 5, 6, 7, 8];
    $inquiryStatus[202]['for_sales_ids'] = [1, 2, 3, 4, 5, 6, 7, 8];
    $inquiryStatus[202]['for_tele_sales_ids'] = [1, 2, 3, 4, 5, 6, 7, 8];
    $inquiryStatus[202]['for_channel_partner_ids'] = [1, 2, 3, 4, 5, 6, 7, 8];
    $inquiryStatus[202]['can_move_user'] = [];
    $inquiryStatus[202]['can_move_third_party'] = [];
    $inquiryStatus[202]['can_move_sales'] = [];
    $inquiryStatus[202]['can_move_tele_sales'] = [];
    $inquiryStatus[202]['can_move_channel_partner'] = [];
    $inquiryStatus[202]['only_id_question'] = 0;
    $inquiryStatus[202]['need_followup'] = 0;
    $inquiryStatus[202]['has_question'] = 0;
    $inquiryStatus[202]['can_display_on_inquiry_user'] = 1;
    $inquiryStatus[202]['can_display_on_inquiry_third_party'] = 1;
    $inquiryStatus[202]['can_display_on_inquiry_tele_sales'] = 1;
    $inquiryStatus[202]['can_display_on_inquiry_architect'] = 0;
    $inquiryStatus[202]['can_display_on_inquiry_electrician'] = 0;
    $inquiryStatus[202]['can_display_on_inquiry_sales_person'] = 1;
    $inquiryStatus[202]['can_display_on_inquiry_channel_partner'] = 1;
    $inquiryStatus[202]['background'] = '#0d0d0d';
    $inquiryStatus[202]['color'] = '#ffffff';
    $inquiryStatus[202]['index'] = -1;

    $inquiryStatus[201]['id'] = 201;
    $inquiryStatus[201]['name'] = 'Running';
    $inquiryStatus[201]['key'] = 't-inquiry-running';
    $inquiryStatus[201]['for_architect_ids'] = [1, 2, 3, 4, 5, 6, 7, 8];
    $inquiryStatus[201]['for_electrician_ids'] = [1, 2, 3, 4, 5, 6, 7, 8];
    $inquiryStatus[201]['for_user_ids'] = [1, 2, 3, 4, 5, 6, 7, 8];
    $inquiryStatus[201]['for_third_party_ids'] = [1, 2, 3, 4, 5, 6, 7, 8];
    $inquiryStatus[201]['for_sales_ids'] = [1, 2, 3, 4, 5, 6, 7, 8];
    $inquiryStatus[201]['for_tele_sales_ids'] = [1, 2, 3, 4, 5, 6, 7, 8];
    $inquiryStatus[201]['for_channel_partner_ids'] = [1, 2, 3, 4, 5, 6, 7, 8];
    $inquiryStatus[201]['can_move_user'] = [];
    $inquiryStatus[201]['can_move_third_party'] = [];
    $inquiryStatus[201]['can_move_sales'] = [];
    $inquiryStatus[201]['can_move_tele_sales'] = [];
    $inquiryStatus[201]['can_move_channel_partner'] = [];
    $inquiryStatus[201]['only_id_question'] = 0;
    $inquiryStatus[201]['need_followup'] = 0;
    $inquiryStatus[201]['has_question'] = 0;
    $inquiryStatus[201]['can_display_on_inquiry_user'] = 1;
    $inquiryStatus[201]['can_display_on_inquiry_third_party'] = 1;
    $inquiryStatus[201]['can_display_on_inquiry_tele_sales'] = 1;
    $inquiryStatus[201]['can_display_on_inquiry_architect'] = 0;
    $inquiryStatus[201]['can_display_on_inquiry_electrician'] = 0;
    $inquiryStatus[201]['can_display_on_inquiry_sales_person'] = 1;
    $inquiryStatus[201]['can_display_on_inquiry_channel_partner'] = 1;
    $inquiryStatus[201]['background'] = '#0d0d0d';
    $inquiryStatus[201]['color'] = '#ffffff';
    $inquiryStatus[201]['index'] = 0;

    $inquiryStatus[1]['id'] = 1;
    $inquiryStatus[1]['name'] = 'Inquiry';
    $inquiryStatus[1]['key'] = 't-inquiry';
    $inquiryStatus[1]['for_architect_ids'] = [1, 2, 3, 4, 5, 6, 7, 8];
    $inquiryStatus[1]['for_electrician_ids'] = [1, 42, 3, 4, 5, 6, 7, 8];
    $inquiryStatus[1]['for_user_ids'] = [1];
    $inquiryStatus[1]['for_third_party_ids'] = [1];
    $inquiryStatus[1]['for_sales_ids'] = [1];
    $inquiryStatus[1]['for_tele_sales_ids'] = [1];
    $inquiryStatus[1]['for_channel_partner_ids'] = [1];
    $inquiryStatus[1]['can_move_user'] = [1, 2, 3, 4, 5, 6, 7, 13, 9, 10, 102];
    $inquiryStatus[1]['can_move_third_party'] = [1, 2, 3, 4, 5, 6, 7, 13, 9, 10, 102];
    $inquiryStatus[1]['can_move_sales'] = [1, 2, 3, 4, 5, 6, 7, 13, 9, 102];
    $inquiryStatus[1]['can_move_tele_sales'] = [1, 2, 3, 4, 5, 6, 7, 13, 9, 102];
    $inquiryStatus[1]['can_move_channel_partner'] = [1, 2, 3, 4, 5, 6, 7, 13, 9, 102];
    $inquiryStatus[1]['only_id_question'] = 0;
    $inquiryStatus[1]['need_followup'] = 1;
    $inquiryStatus[1]['has_question'] = 0;
    $inquiryStatus[1]['can_display_on_inquiry_user'] = 1;
    $inquiryStatus[1]['can_display_on_inquiry_third_party'] = 1;
    $inquiryStatus[1]['can_display_on_inquiry_tele_sales'] = 1;
    $inquiryStatus[1]['can_display_on_inquiry_architect'] = 1;
    $inquiryStatus[1]['can_display_on_inquiry_electrician'] = 1;
    $inquiryStatus[1]['can_display_on_inquiry_sales_person'] = 1;
    $inquiryStatus[1]['can_display_on_inquiry_channel_partner'] = 1;
    $inquiryStatus[1]['background'] = '#0d0d0d';
    $inquiryStatus[1]['color'] = '#ffffff';
    $inquiryStatus[1]['highlight_deadend_followup'] = 1;
    $inquiryStatus[1]['index'] = 1;

    $inquiryStatus[2]['id'] = 2;
    $inquiryStatus[2]['name'] = 'Potential Inquiry';
    $inquiryStatus[2]['key'] = 't-potential-inquiry';
    $inquiryStatus[2]['for_architect_ids'] = [2, 3, 4, 5, 6, 7, 8];
    $inquiryStatus[2]['for_electrician_ids'] = [2, 3, 4, 5, 6, 7, 8];
    $inquiryStatus[2]['for_user_ids'] = [2];
    $inquiryStatus[2]['for_third_party_ids'] = [2];
    $inquiryStatus[2]['for_sales_ids'] = [2];
    $inquiryStatus[2]['for_tele_sales_ids'] = [2];
    $inquiryStatus[2]['for_channel_partner_ids'] = [2];
    $inquiryStatus[2]['can_move_user'] = [2, 3, 4, 5, 6, 7, 13, 9, 10, 102];
    $inquiryStatus[2]['can_move_third_party'] = [2, 3, 4, 5, 6, 7, 9, 10, 102];
    $inquiryStatus[2]['can_move_sales'] = [2, 3, 4, 5, 6, 7, 13, 9, 102];
    $inquiryStatus[2]['can_move_tele_sales'] = [2, 3, 4, 5, 6, 7, 13, 9, 102];
    $inquiryStatus[2]['can_move_channel_partner'] = [2, 3, 4, 5, 6, 7, 13, 9, 102];
    $inquiryStatus[2]['only_id_question'] = 0;
    $inquiryStatus[2]['need_followup'] = 1;
    $inquiryStatus[2]['has_question'] = 1;
    $inquiryStatus[2]['can_display_on_inquiry_user'] = 1;
    $inquiryStatus[2]['can_display_on_inquiry_third_party'] = 1;
    $inquiryStatus[2]['can_display_on_inquiry_tele_sales'] = 1;
    $inquiryStatus[2]['can_display_on_inquiry_architect'] = 0;
    $inquiryStatus[2]['can_display_on_inquiry_electrician'] = 0;
    $inquiryStatus[2]['can_display_on_inquiry_sales_person'] = 1;
    $inquiryStatus[2]['can_display_on_inquiry_channel_partner'] = 1;
    $inquiryStatus[2]['background'] = '#f19e06';
    $inquiryStatus[2]['color'] = '#ffffff';
    $inquiryStatus[2]['highlight_deadend_followup'] = 1;
    $inquiryStatus[2]['index'] = 2;

    $inquiryStatus[3]['id'] = 3;
    $inquiryStatus[3]['name'] = 'Demo Done';
    $inquiryStatus[3]['key'] = 't-demo-done';
    $inquiryStatus[3]['for_architect_ids'] = [2, 3, 4, 5, 6, 7, 8];
    $inquiryStatus[3]['for_electrician_ids'] = [2, 3, 4, 5, 6, 7, 8];
    $inquiryStatus[3]['for_user_ids'] = [3];
    $inquiryStatus[3]['for_third_party_ids'] = [3];
    $inquiryStatus[3]['for_sales_ids'] = [3];
    $inquiryStatus[3]['for_tele_sales_ids'] = [3];
    $inquiryStatus[3]['for_channel_partner_ids'] = [3];
    $inquiryStatus[3]['can_move_user'] = [3, 4, 5, 6, 7, 13, 9, 10, 102];
    $inquiryStatus[3]['can_move_third_party'] = [3, 4, 5, 6, 7, 13, 9, 10, 102];
    $inquiryStatus[3]['can_move_sales'] = [3, 4, 5, 6, 7, 13, 9, 102];
    $inquiryStatus[3]['can_move_tele_sales'] = [3, 4, 5, 6, 7, 13, 9, 102];
    $inquiryStatus[3]['can_move_channel_partner'] = [3, 4, 5, 6, 7, 13, 9, 102];
    $inquiryStatus[3]['only_id_question'] = 0;
    $inquiryStatus[3]['need_followup'] = 1;
    $inquiryStatus[3]['has_question'] = 1;
    $inquiryStatus[3]['can_display_on_inquiry_user'] = 1;
    $inquiryStatus[3]['can_display_on_inquiry_third_party'] = 1;
    $inquiryStatus[3]['can_display_on_inquiry_tele_sales'] = 1;
    $inquiryStatus[3]['can_display_on_inquiry_architect'] = 0;
    $inquiryStatus[3]['can_display_on_inquiry_electrician'] = 0;
    $inquiryStatus[3]['can_display_on_inquiry_sales_person'] = 1;
    $inquiryStatus[3]['can_display_on_inquiry_channel_partner'] = 1;
    $inquiryStatus[3]['background'] = '#f5b3be';
    $inquiryStatus[3]['color'] = '#ffffff';
    $inquiryStatus[3]['highlight_deadend_followup'] = 1;
    $inquiryStatus[3]['index'] = 3;

    $inquiryStatus[4]['id'] = 4;
    $inquiryStatus[4]['name'] = 'Site Visit';
    $inquiryStatus[4]['key'] = 't-site-visit';
    $inquiryStatus[4]['for_architect_ids'] = [0];
    $inquiryStatus[4]['for_electrician_ids'] = [0];
    $inquiryStatus[4]['for_user_ids'] = [4];
    $inquiryStatus[4]['for_third_party_ids'] = [4];
    $inquiryStatus[4]['for_sales_ids'] = [4];
    $inquiryStatus[4]['for_tele_sales_ids'] = [4];
    $inquiryStatus[4]['for_channel_partner_ids'] = [4];
    $inquiryStatus[4]['can_move_user'] = [4, 5, 6, 7, 13, 9, 10, 102];
    $inquiryStatus[4]['can_move_third_party'] = [4, 5, 6, 7, 13, 9, 10, 102];
    $inquiryStatus[4]['can_move_sales'] = [4, 5, 6, 7, 13, 9, 101];
    $inquiryStatus[4]['can_move_tele_sales'] = [4, 5, 6, 7, 13, 9, 102];
    $inquiryStatus[4]['can_move_channel_partner'] = [4, 5, 6, 7, 13, 9, 102];
    $inquiryStatus[4]['only_id_question'] = 0;
    $inquiryStatus[4]['need_followup'] = 1;
    $inquiryStatus[4]['has_question'] = 1;
    $inquiryStatus[4]['can_display_on_inquiry_user'] = 1;
    $inquiryStatus[4]['can_display_on_inquiry_third_party'] = 1;
    $inquiryStatus[4]['can_display_on_inquiry_tele_sales'] = 1;
    $inquiryStatus[4]['can_display_on_inquiry_architect'] = 0;
    $inquiryStatus[4]['can_display_on_inquiry_electrician'] = 0;
    $inquiryStatus[4]['can_display_on_inquiry_sales_person'] = 1;
    $inquiryStatus[4]['can_display_on_inquiry_channel_partner'] = 1;
    $inquiryStatus[4]['background'] = '#b12d2d';
    $inquiryStatus[4]['color'] = '#ffffff';
    $inquiryStatus[4]['highlight_deadend_followup'] = 1;
    $inquiryStatus[4]['index'] = 4;

    $inquiryStatus[5]['id'] = 5;
    $inquiryStatus[5]['name'] = 'Quotation';
    $inquiryStatus[5]['key'] = 't-quotation';
    $inquiryStatus[5]['for_architect_ids'] = [0];
    $inquiryStatus[5]['for_electrician_ids'] = [0];
    $inquiryStatus[5]['for_user_ids'] = [5];
    $inquiryStatus[5]['for_third_party_ids'] = [5];
    $inquiryStatus[5]['for_sales_ids'] = [5];
    $inquiryStatus[5]['for_tele_sales_ids'] = [5];
    $inquiryStatus[5]['for_channel_partner_ids'] = [5];
    $inquiryStatus[5]['can_move_user'] = [5, 6, 7, 13, 9, 10, 102];
    $inquiryStatus[5]['can_move_third_party'] = [5, 6, 7, 13, 9, 10, 102];
    $inquiryStatus[5]['can_move_sales'] = [5, 6, 7, 13, 9, 102];
    $inquiryStatus[5]['can_move_tele_sales'] = [5, 6, 7, 13, 9, 102];
    $inquiryStatus[5]['can_move_channel_partner'] = [5, 6, 7, 13, 9, 102];
    $inquiryStatus[5]['only_id_question'] = 0;
    $inquiryStatus[5]['need_followup'] = 1;
    $inquiryStatus[5]['has_question'] = 1;
    $inquiryStatus[5]['can_display_on_inquiry_user'] = 1;
    $inquiryStatus[5]['can_display_on_inquiry_third_party'] = 1;
    $inquiryStatus[5]['can_display_on_inquiry_tele_sales'] = 1;
    $inquiryStatus[5]['can_display_on_inquiry_architect'] = 0;
    $inquiryStatus[5]['can_display_on_inquiry_electrician'] = 0;
    $inquiryStatus[5]['can_display_on_inquiry_sales_person'] = 1;
    $inquiryStatus[5]['can_display_on_inquiry_channel_partner'] = 1;
    $inquiryStatus[5]['background'] = '#750375';
    $inquiryStatus[5]['color'] = '#ffffff';
    $inquiryStatus[5]['highlight_deadend_followup'] = 1;
    $inquiryStatus[5]['index'] = 5;

    $inquiryStatus[6]['id'] = 6;
    $inquiryStatus[6]['name'] = 'Negotiation';
    $inquiryStatus[6]['key'] = 't-negotiation';
    $inquiryStatus[6]['for_architect_ids'] = [0];
    $inquiryStatus[6]['for_electrician_ids'] = [0];
    $inquiryStatus[6]['for_user_ids'] = [6];
    $inquiryStatus[6]['for_third_party_ids'] = [6];
    $inquiryStatus[6]['for_sales_ids'] = [6];
    $inquiryStatus[6]['for_tele_sales_ids'] = [6];
    $inquiryStatus[6]['for_channel_partner_ids'] = [6];
    $inquiryStatus[6]['can_move_user'] = [6, 7, 13, 9, 10, 102];
    $inquiryStatus[6]['can_move_third_party'] = [6, 7, 13, 9, 10, 102];
    $inquiryStatus[6]['can_move_sales'] = [6, 7, 13, 9, 102];
    $inquiryStatus[6]['can_move_tele_sales'] = [6, 7, 13, 9, 102];
    $inquiryStatus[6]['can_move_channel_partner'] = [6, 7, 13, 9, 102];
    $inquiryStatus[6]['only_id_question'] = 0;
    $inquiryStatus[6]['need_followup'] = 1;
    $inquiryStatus[6]['has_question'] = 1;
    $inquiryStatus[6]['can_display_on_inquiry_user'] = 1;
    $inquiryStatus[6]['can_display_on_inquiry_third_party'] = 1;
    $inquiryStatus[6]['can_display_on_inquiry_tele_sales'] = 1;
    $inquiryStatus[6]['can_display_on_inquiry_architect'] = 0;
    $inquiryStatus[6]['can_display_on_inquiry_electrician'] = 0;
    $inquiryStatus[6]['can_display_on_inquiry_sales_person'] = 1;
    $inquiryStatus[6]['can_display_on_inquiry_channel_partner'] = 1;
    $inquiryStatus[6]['background'] = '#e70e0e';
    $inquiryStatus[6]['color'] = '#ffffff';
    $inquiryStatus[6]['highlight_deadend_followup'] = 1;
    $inquiryStatus[6]['index'] = 6;

    $inquiryStatus[7]['id'] = 7;
    $inquiryStatus[7]['name'] = 'Token Received';
    $inquiryStatus[7]['key'] = 't-token-received';
    $inquiryStatus[7]['for_architect_ids'] = [0];
    $inquiryStatus[7]['for_electrician_ids'] = [0];
    $inquiryStatus[7]['for_user_ids'] = [7];
    $inquiryStatus[7]['for_third_party_ids'] = [7];
    $inquiryStatus[7]['for_sales_ids'] = [7];
    $inquiryStatus[7]['for_tele_sales_ids'] = [7];
    $inquiryStatus[7]['for_channel_partner_ids'] = [7];
    $inquiryStatus[7]['can_move_user'] = [7, 13, 9, 10, 102];
    $inquiryStatus[7]['can_move_third_party'] = [7, 13, 9, 10, 102];
    $inquiryStatus[7]['can_move_sales'] = [7, 13, 9, 102];
    $inquiryStatus[7]['can_move_tele_sales'] = [7, 13, 9, 102];
    $inquiryStatus[7]['can_move_channel_partner'] = [7, 13, 9, 102];
    $inquiryStatus[7]['only_id_question'] = 0;
    $inquiryStatus[7]['need_followup'] = 1;
    $inquiryStatus[7]['has_question'] = 1;
    $inquiryStatus[7]['can_display_on_inquiry_user'] = 1;
    $inquiryStatus[7]['can_display_on_inquiry_third_party'] = 1;
    $inquiryStatus[7]['can_display_on_inquiry_tele_sales'] = 1;
    $inquiryStatus[7]['can_display_on_inquiry_architect'] = 0;
    $inquiryStatus[7]['can_display_on_inquiry_electrician'] = 0;
    $inquiryStatus[7]['can_display_on_inquiry_sales_person'] = 1;
    $inquiryStatus[7]['can_display_on_inquiry_channel_partner'] = 1;
    $inquiryStatus[7]['background'] = '#418107';
    $inquiryStatus[7]['color'] = '#ffffff';
    $inquiryStatus[7]['highlight_deadend_followup'] = 1;
    $inquiryStatus[7]['index'] = 7;

    $inquiryStatus[8]['id'] = 8;
    $inquiryStatus[8]['name'] = 'Prediction';
    $inquiryStatus[8]['key'] = 't-predication';
    $inquiryStatus[8]['for_architect_ids'] = [0];
    $inquiryStatus[8]['for_electrician_ids'] = [0];
    $inquiryStatus[8]['for_user_ids'] = [1, 2, 3, 4, 5, 6, 7, 8];
    $inquiryStatus[8]['for_third_party_ids'] = [1, 2, 3, 4, 5, 6, 7, 8];
    $inquiryStatus[8]['for_sales_ids'] = [1, 2, 3, 4, 5, 6, 7, 8];
    $inquiryStatus[8]['for_tele_sales_ids'] = [1, 2, 3, 4, 5, 6, 7, 8];
    $inquiryStatus[8]['for_channel_partner_ids'] = [1, 2, 3, 4, 5, 6, 7, 8];
    $inquiryStatus[8]['can_move_user'] = [8, 9, 10, 102];
    $inquiryStatus[8]['can_move_third_party'] = [8, 9, 10, 102];
    $inquiryStatus[8]['can_move_sales'] = [8, 9, 102];
    $inquiryStatus[8]['can_move_tele_sales'] = [8, 9, 102];
    $inquiryStatus[8]['can_move_channel_partner'] = [8, 9, 102];
    $inquiryStatus[8]['only_id_question'] = 0;
    $inquiryStatus[8]['need_followup'] = 1;
    $inquiryStatus[8]['has_question'] = 1;
    $inquiryStatus[8]['can_display_on_inquiry_user'] = 1;
    $inquiryStatus[8]['can_display_on_inquiry_third_party'] = 1;
    $inquiryStatus[8]['can_display_on_inquiry_tele_sales'] = 1;
    $inquiryStatus[8]['can_display_on_inquiry_architect'] = 0;
    $inquiryStatus[8]['can_display_on_inquiry_electrician'] = 0;
    $inquiryStatus[8]['can_display_on_inquiry_sales_person'] = 1;
    $inquiryStatus[8]['can_display_on_inquiry_channel_partner'] = 1;
    $inquiryStatus[8]['background'] = '#e70e0e';
    $inquiryStatus[8]['color'] = '#ffffff';
    $inquiryStatus[8]['highlight_deadend_followup'] = 1;
    $inquiryStatus[8]['index'] = 8;

    $inquiryStatus[13]['id'] = 13;
    $inquiryStatus[13]['name'] = 'Material Ordered';
    $inquiryStatus[13]['key'] = 't-lapsed';
    $inquiryStatus[13]['for_architect_ids'] = [13];
    $inquiryStatus[13]['for_electrician_ids'] = [13];
    $inquiryStatus[13]['for_user_ids'] = [13];
    $inquiryStatus[13]['for_third_party_ids'] = [13];
    $inquiryStatus[13]['for_sales_ids'] = [13];
    $inquiryStatus[13]['for_tele_sales_ids'] = [13];
    $inquiryStatus[13]['for_channel_partner_ids'] = [12];
    $inquiryStatus[13]['can_move_user'] = [13, 9, 10, 102];
    $inquiryStatus[13]['can_move_third_party'] = [13, 9, 10, 102];
    $inquiryStatus[13]['can_move_sales'] = [13, 9, 10, 102];
    $inquiryStatus[13]['can_move_tele_sales'] = [13, 9, 10, 102];
    $inquiryStatus[13]['can_move_channel_partner'] = [13, 9, 10, 102];
    $inquiryStatus[13]['only_id_question'] = 0;
    $inquiryStatus[13]['need_followup'] = 1;
    $inquiryStatus[13]['has_question'] = 1;
    $inquiryStatus[13]['can_display_on_inquiry_user'] = 1;
    $inquiryStatus[13]['can_display_on_inquiry_third_party'] = 1;
    $inquiryStatus[13]['can_display_on_inquiry_tele_sales'] = 1;
    $inquiryStatus[13]['can_display_on_inquiry_architect'] = 0;
    $inquiryStatus[13]['can_display_on_inquiry_electrician'] = 0;
    $inquiryStatus[13]['can_display_on_inquiry_sales_person'] = 1;
    $inquiryStatus[13]['can_display_on_inquiry_channel_partner'] = 0;
    $inquiryStatus[13]['background'] = '#e70e0e';
    $inquiryStatus[13]['color'] = '#ffffff';
    $inquiryStatus[13]['highlight_deadend_followup'] = 0;
    $inquiryStatus[13]['index'] = 9;

    $inquiryStatus[9]['id'] = 9;
    $inquiryStatus[9]['name'] = 'Material Sent';
    $inquiryStatus[9]['key'] = 't-material-sent';
    $inquiryStatus[9]['for_architect_ids'] = [9, 11, 10];
    $inquiryStatus[9]['for_electrician_ids'] = [9, 11, 10];
    $inquiryStatus[9]['for_user_ids'] = [9, 11, 10];
    $inquiryStatus[9]['for_third_party_ids'] = [9, 11, 10];
    $inquiryStatus[9]['for_sales_ids'] = [9, 11, 10];
    $inquiryStatus[9]['for_tele_sales_ids'] = [9, 11, 10];
    $inquiryStatus[9]['for_channel_partner_ids'] = [9, 11, 10];
    $inquiryStatus[9]['can_move_user'] = [9, 10, 12, 14, 102];
    $inquiryStatus[9]['can_move_third_party'] = [9, 10, 12, 14, 102];
    $inquiryStatus[9]['can_move_sales'] = [9, 12, 102];
    $inquiryStatus[9]['can_move_tele_sales'] = [9, 12, 102];
    $inquiryStatus[9]['can_move_channel_partner'] = [9, 102];
    $inquiryStatus[9]['only_id_question'] = 0;
    $inquiryStatus[9]['need_followup'] = 0;
    $inquiryStatus[9]['has_question'] = 1;
    $inquiryStatus[9]['can_display_on_inquiry_user'] = 1;
    $inquiryStatus[9]['can_display_on_inquiry_third_party'] = 1;
    $inquiryStatus[9]['can_display_on_inquiry_tele_sales'] = 1;
    $inquiryStatus[9]['can_display_on_inquiry_architect'] = 1;
    $inquiryStatus[9]['can_display_on_inquiry_electrician'] = 1;
    $inquiryStatus[9]['can_display_on_inquiry_sales_person'] = 1;
    $inquiryStatus[9]['can_display_on_inquiry_channel_partner'] = 1;
    $inquiryStatus[9]['background'] = '#e70e0e';
    $inquiryStatus[9]['color'] = '#ffffff';
    $inquiryStatus[9]['highlight_deadend_followup'] = 0;
    $inquiryStatus[9]['index'] = 10;

    $inquiryStatus[11]['id'] = 11;
    $inquiryStatus[11]['name'] = 'Direct Material Sent';
    $inquiryStatus[11]['key'] = 't-direct-material-sent';
    $inquiryStatus[11]['for_architect_ids'] = [9, 10];
    $inquiryStatus[11]['for_electrician_ids'] = [9, 10];
    $inquiryStatus[11]['for_user_ids'] = [10];
    $inquiryStatus[11]['for_third_party_ids'] = [10];
    $inquiryStatus[11]['for_sales_ids'] = [10];
    $inquiryStatus[11]['for_tele_sales_ids'] = [10];
    $inquiryStatus[11]['for_channel_partner_ids'] = [10];
    $inquiryStatus[11]['can_move_user'] = [11, 10, 102];
    $inquiryStatus[11]['can_move_third_party'] = [11, 10, 102];
    $inquiryStatus[11]['can_move_sales'] = [11, 102];
    $inquiryStatus[11]['can_move_tele_sales'] = [11, 102];
    $inquiryStatus[11]['can_move_channel_partner'] = [11, 102];
    $inquiryStatus[11]['only_id_question'] = 1;
    $inquiryStatus[11]['need_followup'] = 0;
    $inquiryStatus[11]['has_question'] = 1;
    $inquiryStatus[11]['can_display_on_inquiry_user'] = 0;
    $inquiryStatus[11]['can_display_on_inquiry_third_party'] = 0;
    $inquiryStatus[11]['can_display_on_inquiry_tele_sales'] = 1;
    $inquiryStatus[11]['can_display_on_inquiry_architect'] = 0;
    $inquiryStatus[11]['can_display_on_inquiry_electrician'] = 0;
    $inquiryStatus[11]['can_display_on_inquiry_sales_person'] = 0;
    $inquiryStatus[11]['can_display_on_inquiry_channel_partner'] = 0;
    $inquiryStatus[11]['background'] = '#e70e0e';
    $inquiryStatus[11]['color'] = '#ffffff';
    $inquiryStatus[11]['highlight_deadend_followup'] = 0;
    $inquiryStatus[11]['index'] = 11;

    $inquiryStatus[14]['id'] = 14;
    $inquiryStatus[14]['name'] = 'Points Query';
    $inquiryStatus[14]['key'] = 't-point-query';
    $inquiryStatus[14]['for_architect_ids'] = [14];
    $inquiryStatus[14]['for_electrician_ids'] = [14];
    $inquiryStatus[14]['for_user_ids'] = [14];
    $inquiryStatus[14]['for_third_party_ids'] = [14];
    $inquiryStatus[14]['for_sales_ids'] = [14];
    $inquiryStatus[14]['for_tele_sales_ids'] = [14];
    $inquiryStatus[14]['for_channel_partner_ids'] = [14];
    $inquiryStatus[14]['can_move_user'] = [14, 12, 10];
    $inquiryStatus[14]['can_move_third_party'] = [];
    $inquiryStatus[14]['can_move_sales'] = [2];
    $inquiryStatus[14]['can_move_tele_sales'] = [9];
    $inquiryStatus[14]['can_move_channel_partner'] = [];
    $inquiryStatus[14]['only_id_question'] = 1;
    $inquiryStatus[14]['need_followup'] = 0;
    $inquiryStatus[14]['has_question'] = 1;
    $inquiryStatus[14]['can_display_on_inquiry_user'] = 1;
    $inquiryStatus[14]['can_display_on_inquiry_third_party'] = 1;
    $inquiryStatus[14]['can_display_on_inquiry_tele_sales'] = 1;
    $inquiryStatus[14]['can_display_on_inquiry_architect'] = 0;
    $inquiryStatus[14]['can_display_on_inquiry_electrician'] = 0;
    $inquiryStatus[14]['can_display_on_inquiry_sales_person'] = 1;
    $inquiryStatus[14]['can_display_on_inquiry_channel_partner'] = 0;
    $inquiryStatus[14]['background'] = '#e70e0e';
    $inquiryStatus[14]['color'] = '#ffffff';
    $inquiryStatus[14]['highlight_deadend_followup'] = 0;
    $inquiryStatus[14]['index'] = 11;

    $inquiryStatus[12]['id'] = 12;
    $inquiryStatus[12]['name'] = 'Points Lapsed';
    $inquiryStatus[12]['key'] = 't-lapsed';
    $inquiryStatus[12]['for_architect_ids'] = [12];
    $inquiryStatus[12]['for_electrician_ids'] = [12];
    $inquiryStatus[12]['for_user_ids'] = [12];
    $inquiryStatus[12]['for_third_party_ids'] = [12];
    $inquiryStatus[12]['for_sales_ids'] = [12];
    $inquiryStatus[12]['for_tele_sales_ids'] = [12];
    $inquiryStatus[12]['for_channel_partner_ids'] = [12];
    $inquiryStatus[12]['can_move_user'] = [12, 10];
    $inquiryStatus[12]['can_move_third_party'] = [];
    $inquiryStatus[12]['can_move_sales'] = [];
    $inquiryStatus[12]['can_move_tele_sales'] = [];
    $inquiryStatus[12]['can_move_channel_partner'] = [];
    $inquiryStatus[12]['only_id_question'] = 1;
    $inquiryStatus[12]['need_followup'] = 0;
    $inquiryStatus[12]['has_question'] = 1;
    $inquiryStatus[12]['can_display_on_inquiry_user'] = 1;
    $inquiryStatus[12]['can_display_on_inquiry_third_party'] = 1;
    $inquiryStatus[12]['can_display_on_inquiry_tele_sales'] = 1;
    $inquiryStatus[12]['can_display_on_inquiry_architect'] = 0;
    $inquiryStatus[12]['can_display_on_inquiry_electrician'] = 0;
    $inquiryStatus[12]['can_display_on_inquiry_sales_person'] = 0;
    $inquiryStatus[12]['can_display_on_inquiry_channel_partner'] = 0;
    $inquiryStatus[12]['background'] = '#e70e0e';
    $inquiryStatus[12]['color'] = '#ffffff';
    $inquiryStatus[12]['highlight_deadend_followup'] = 0;
    $inquiryStatus[12]['index'] = 12;

    $inquiryStatus[10]['id'] = 10;
    $inquiryStatus[10]['name'] = 'Claimed';
    $inquiryStatus[10]['key'] = 't-claimed';
    $inquiryStatus[10]['for_architect_ids'] = [9, 10];
    $inquiryStatus[10]['for_electrician_ids'] = [9, 10];
    $inquiryStatus[10]['for_user_ids'] = [9, 10];
    $inquiryStatus[10]['for_third_party_ids'] = [10];
    $inquiryStatus[10]['for_sales_ids'] = [10];
    $inquiryStatus[10]['for_tele_sales_ids'] = [10];
    $inquiryStatus[10]['for_channel_partner_ids'] = [10];
    $inquiryStatus[10]['can_move_user'] = [10];
    $inquiryStatus[10]['can_move_third_party'] = [10];
    $inquiryStatus[10]['can_move_sales'] = [10];
    $inquiryStatus[10]['can_move_tele_sales'] = [10];
    $inquiryStatus[10]['can_move_channel_partner'] = [10];
    $inquiryStatus[10]['only_id_question'] = 0;
    $inquiryStatus[10]['need_followup'] = 0;
    $inquiryStatus[10]['has_question'] = 1;
    $inquiryStatus[10]['can_display_on_inquiry_user'] = 1;
    $inquiryStatus[10]['can_display_on_inquiry_third_party'] = 1;
    $inquiryStatus[10]['can_display_on_inquiry_tele_sales'] = 1;
    $inquiryStatus[10]['can_display_on_inquiry_architect'] = 0;
    $inquiryStatus[10]['can_display_on_inquiry_electrician'] = 0;
    $inquiryStatus[10]['can_display_on_inquiry_sales_person'] = 0;
    $inquiryStatus[10]['can_display_on_inquiry_channel_partner'] = 0;
    $inquiryStatus[10]['background'] = '#e70e0e';
    $inquiryStatus[10]['color'] = '#ffffff';
    $inquiryStatus[10]['highlight_deadend_followup'] = 0;
    $inquiryStatus[10]['index'] = 13;

    $inquiryStatus[101]['id'] = 101;
    $inquiryStatus[101]['name'] = 'Non Potential';
    $inquiryStatus[101]['key'] = 't-non-potential';
    $inquiryStatus[101]['for_architect_ids'] = [];
    $inquiryStatus[101]['for_electrician_ids'] = [];
    $inquiryStatus[101]['for_user_ids'] = [];
    $inquiryStatus[101]['for_third_party_ids'] = [];
    $inquiryStatus[101]['for_sales_ids'] = [];
    $inquiryStatus[101]['for_tele_sales_ids'] = [];
    $inquiryStatus[101]['for_channel_partner_ids'] = [];
    $inquiryStatus[101]['can_move_user'] = [];
    $inquiryStatus[101]['can_move_third_party'] = [];
    $inquiryStatus[101]['can_move_sales'] = [];
    $inquiryStatus[101]['can_move_tele_sales'] = [];
    $inquiryStatus[101]['can_move_channel_partner'] = [];
    $inquiryStatus[101]['only_id_question'] = 1;
    $inquiryStatus[101]['need_followup'] = 0;
    $inquiryStatus[101]['has_question'] = 0;
    $inquiryStatus[101]['can_display_on_inquiry_user'] = 0;
    $inquiryStatus[101]['can_display_on_inquiry_third_party'] = 0;
    $inquiryStatus[101]['can_display_on_inquiry_tele_sales'] = 1;
    $inquiryStatus[101]['can_display_on_inquiry_architect'] = 0;
    $inquiryStatus[101]['can_display_on_inquiry_electrician'] = 0;
    $inquiryStatus[101]['can_display_on_inquiry_sales_person'] = 0;
    $inquiryStatus[101]['can_display_on_inquiry_channel_partner'] = 0;
    $inquiryStatus[101]['background'] = '#88cbe6';
    $inquiryStatus[101]['color'] = '#ffffff';
    $inquiryStatus[101]['highlight_deadend_followup'] = 0;
    $inquiryStatus[101]['index'] = 4;

    $inquiryStatus[102]['id'] = 102;
    $inquiryStatus[102]['name'] = 'Rejected';
    $inquiryStatus[102]['key'] = 't-rejected';
    $inquiryStatus[102]['for_architect_ids'] = [102];
    $inquiryStatus[102]['for_electrician_ids'] = [102];
    $inquiryStatus[102]['for_user_ids'] = [102];
    $inquiryStatus[102]['for_third_party_ids'] = [102];
    $inquiryStatus[102]['for_sales_ids'] = [102];
    $inquiryStatus[102]['for_tele_sales_ids'] = [102];
    $inquiryStatus[102]['for_channel_partner_ids'] = [102];
    $inquiryStatus[102]['can_move_user'] = [102];
    $inquiryStatus[102]['can_move_third_party'] = [102];
    $inquiryStatus[102]['can_move_sales'] = [102];
    $inquiryStatus[102]['can_move_tele_sales'] = [102];
    $inquiryStatus[102]['can_move_channel_partner'] = [102];
    $inquiryStatus[102]['only_id_question'] = 1;
    $inquiryStatus[102]['need_followup'] = 0;
    $inquiryStatus[102]['has_question'] = 1;
    $inquiryStatus[102]['can_display_on_inquiry_user'] = 1;
    $inquiryStatus[102]['can_display_on_inquiry_third_party'] = 1;
    $inquiryStatus[102]['can_display_on_inquiry_tele_sales'] = 1;
    $inquiryStatus[102]['can_display_on_inquiry_architect'] = 1;
    $inquiryStatus[102]['can_display_on_inquiry_electrician'] = 1;
    $inquiryStatus[102]['can_display_on_inquiry_sales_person'] = 1;
    $inquiryStatus[102]['can_display_on_inquiry_channel_partner'] = 1;
    $inquiryStatus[102]['background'] = '#88cbe6';
    $inquiryStatus[102]['color'] = '#ffffff';
    $inquiryStatus[102]['highlight_deadend_followup'] = 0;
    $inquiryStatus[102]['index'] = 15;

    $inquiryStatus[0]['id'] = 0;
    $inquiryStatus[0]['name'] = 'All';
    $inquiryStatus[0]['key'] = 't-all';
    $inquiryStatus[0]['for_architect_ids'] = [0];
    $inquiryStatus[0]['for_electrician_ids'] = [0];
    $inquiryStatus[0]['for_user_ids'] = [0];
    $inquiryStatus[0]['for_third_party_ids'] = [0];
    $inquiryStatus[0]['for_sales_ids'] = [0];
    $inquiryStatus[0]['for_tele_sales_ids'] = [0];
    $inquiryStatus[0]['for_channel_partner_ids'] = [0];
    $inquiryStatus[0]['can_move_user'] = [0];
    $inquiryStatus[0]['can_move_third_party'] = [];
    $inquiryStatus[0]['can_move_sales'] = [];
    $inquiryStatus[0]['can_move_tele_sales'] = [];
    $inquiryStatus[0]['can_move_channel_partner'] = [];
    $inquiryStatus[0]['only_id_question'] = 0;
    $inquiryStatus[0]['need_followup'] = 0;
    $inquiryStatus[0]['has_question'] = 0;
    $inquiryStatus[0]['can_display_on_inquiry_user'] = 1;
    $inquiryStatus[0]['can_display_on_inquiry_third_party'] = 1;
    $inquiryStatus[0]['can_display_on_inquiry_tele_sales'] = 1;
    $inquiryStatus[0]['can_display_on_inquiry_architect'] = 0;
    $inquiryStatus[0]['can_display_on_inquiry_electrician'] = 0;
    $inquiryStatus[0]['can_display_on_inquiry_sales_person'] = 1;
    $inquiryStatus[0]['can_display_on_inquiry_channel_partner'] = 1;
    $inquiryStatus[0]['background'] = '#0d0d0d';
    $inquiryStatus[0]['color'] = '#ffffff';
    $inquiryStatus[0]['highlight_deadend_followup'] = 1;
    $inquiryStatus[0]['index'] = 16;

    //	$inquiryStatus[0]['sub_ids'] = array(0);
    // $inquiryStatus[0]['is_last_status'] = 0;
    // $inquiryStatus[0]['need_followup'] = 0;

    // $inquiryStatus[1]['id'] = 1;
    // $inquiryStatus[1]['sub_ids'] = array(1);
    // $inquiryStatus[1]['name'] = "Inquiry";
    // $inquiryStatus[1]['key'] = "t-inquiry";
    // $inquiryStatus[1]['can_move_user'] = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 101);
    // $inquiryStatus[1]['is_last_status'] = 0;
    // $inquiryStatus[1]['need_followup'] = 1;

    // $inquiryStatus[2]['id'] = 2;
    // $inquiryStatus[2]['sub_ids'] = array(2, 3, 4, 5, 6, 7, 8);
    // $inquiryStatus[2]['name'] = "Potential Inquiry";
    // $inquiryStatus[2]['key'] = "t-potential-inquiry";
    // $inquiryStatus[2]['can_move_user'] = array(2, 3, 4, 5, 6, 7, 8, 9, 10, 101);
    // $inquiryStatus[2]['is_last_status'] = 0;
    // $inquiryStatus[2]['need_followup'] = 1;

    // $inquiryStatus[3]['id'] = 3;
    // $inquiryStatus[3]['sub_ids'] = array(0);
    // $inquiryStatus[3]['name'] = "Demo Done";
    // $inquiryStatus[3]['key'] = "t-demo-done";
    // $inquiryStatus[3]['can_move_user'] = array(3, 4, 5, 6, 7, 8, 9, 10, 101);
    // $inquiryStatus[3]['is_last_status'] = 0;
    // $inquiryStatus[3]['need_followup'] = 1;

    // $inquiryStatus[4]['id'] = 4;
    // $inquiryStatus[4]['sub_ids'] = array(0);
    // $inquiryStatus[4]['name'] = "Site Visit";
    // $inquiryStatus[4]['key'] = "t-site-visit";
    // $inquiryStatus[4]['can_move_user'] = array(4, 5, 6, 7, 8, 9, 10, 101);
    // $inquiryStatus[4]['is_last_status'] = 0;
    // $inquiryStatus[4]['need_followup'] = 1;

    // $inquiryStatus[5]['id'] = 5;
    // $inquiryStatus[5]['sub_ids'] = array(0);
    // $inquiryStatus[5]['name'] = "Quotation";
    // $inquiryStatus[5]['key'] = "t-quotation";
    // $inquiryStatus[5]['can_move_user'] = array(5, 6, 7, 8, 9, 10, 102);
    // $inquiryStatus[5]['is_last_status'] = 0;
    // $inquiryStatus[5]['need_followup'] = 1;

    // $inquiryStatus[6]['id'] = 6;
    // $inquiryStatus[6]['sub_ids'] = array(0);
    // $inquiryStatus[6]['name'] = "Negotiation";
    // $inquiryStatus[6]['key'] = "t-negotiation";
    // $inquiryStatus[6]['can_move_user'] = array(6, 7, 8, 9, 10, 102);
    // $inquiryStatus[6]['is_last_status'] = 0;
    // $inquiryStatus[6]['need_followup'] = 1;

    // $inquiryStatus[7]['id'] = 7;
    // $inquiryStatus[7]['sub_ids'] = array(0);
    // $inquiryStatus[7]['name'] = "Order Confrimed";
    // $inquiryStatus[7]['key'] = "t-order-confrimed";
    // $inquiryStatus[7]['can_move_user'] = array(7, 8, 9, 10, 102);
    // $inquiryStatus[7]['is_last_status'] = 0;
    // $inquiryStatus[7]['need_followup'] = 1;

    // $inquiryStatus[8]['id'] = 8;
    // $inquiryStatus[8]['sub_ids'] = array(0);
    // $inquiryStatus[8]['name'] = "Closing";
    // $inquiryStatus[8]['key'] = "t-closing";
    // $inquiryStatus[8]['can_move_user'] = array(8, 9, 10, 102);
    // $inquiryStatus[8]['is_last_status'] = 0;
    // $inquiryStatus[8]['need_followup'] = 1;

    // $inquiryStatus[9]['id'] = 9;
    // $inquiryStatus[9]['sub_ids'] = array(9, 10);
    // $inquiryStatus[9]['name'] = "Material Sent";
    // $inquiryStatus[9]['key'] = "t-material-sent";
    // $inquiryStatus[9]['can_move_user'] = array(9, 10, 102);
    // $inquiryStatus[9]['is_last_status'] = 0;
    // $inquiryStatus[9]['need_followup'] = 0;

    // $inquiryStatus[10]['id'] = 10;
    // $inquiryStatus[10]['sub_ids'] = array(10);
    // $inquiryStatus[10]['name'] = "Claimed";
    // $inquiryStatus[10]['key'] = "t-climed";
    // $inquiryStatus[10]['can_move_user'] = array(10);
    // $inquiryStatus[10]['is_last_status'] = 0;
    // $inquiryStatus[10]['need_followup'] = 0;

    // $inquiryStatus[101]['id'] = 101;
    // $inquiryStatus[101]['sub_ids'] = array(101);
    // $inquiryStatus[101]['name'] = "Non Potential";
    // $inquiryStatus[101]['key'] = "t-non-potential";
    // $inquiryStatus[101]['can_move_user'] = array(101);
    // $inquiryStatus[101]['is_last_status'] = 1;
    // $inquiryStatus[101]['need_followup'] = 0;

    // $inquiryStatus[102]['id'] = 102;
    // $inquiryStatus[102]['sub_ids'] = array(102);
    // $inquiryStatus[102]['name'] = "Rejected";
    // $inquiryStatus[102]['key'] = "t-rejected";
    // $inquiryStatus[102]['can_move_user'] = array(102);
    // $inquiryStatus[102]['is_last_status'] = 1;
    // $inquiryStatus[102]['need_followup'] = 0;

    return $inquiryStatus;
}

function getLeadStatus($statusID = '')
{
    $leadStatus = [];
    $leadStatus[1]['id'] = 1;
    $leadStatus[1]['name'] = 'Entry';
    $leadStatus[1]['type'] = 0;
    $leadStatus[1]['index'] = 1;
    $leadStatus[1]['is_active'] = 0;

    $leadStatus[2]['id'] = 2;
    $leadStatus[2]['name'] = 'Call';
    $leadStatus[2]['type'] = 0;
    $leadStatus[2]['index'] = 2;
    $leadStatus[2]['is_active'] = 0;

    $leadStatus[3]['id'] = 3;
    $leadStatus[3]['name'] = 'Qualified';
    $leadStatus[3]['type'] = 0;
    $leadStatus[3]['index'] = 3;
    $leadStatus[3]['is_active'] = 0;

    $leadStatus[4]['id'] = 4;
    $leadStatus[4]['name'] = 'Demo Meeting Done';
    $leadStatus[4]['type'] = 0;
    $leadStatus[4]['index'] = 4;
    $leadStatus[4]['is_active'] = 0;

    $leadStatus[5]['id'] = 5;
    $leadStatus[5]['name'] = 'Not Qualified';
    $leadStatus[5]['type'] = 0;
    $leadStatus[5]['index'] = 5;
    $leadStatus[5]['is_active'] = 0;

    $leadStatus[6]['id'] = 6;
    $leadStatus[6]['name'] = 'Cold';
    $leadStatus[6]['type'] = 0;
    $leadStatus[6]['index'] = 6;
    $leadStatus[6]['is_active'] = 0;

    // $leadStatus[7]['id'] = 7;
    // $leadStatus[7]['name'] = "Demo Meeting Done";
    // $leadStatus[7]['type'] = 0;
    // $leadStatus[7]['index'] = 7;

    $leadStatus[100]['id'] = 100;
    $leadStatus[100]['name'] = 'Quotation';
    $leadStatus[100]['type'] = 1;
    $leadStatus[100]['index'] = 7;
    $leadStatus[100]['is_active'] = 0;

    $leadStatus[101]['id'] = 101;
    $leadStatus[101]['name'] = 'Negotiation';
    $leadStatus[101]['type'] = 1;
    $leadStatus[101]['index'] = 8;
    $leadStatus[101]['is_active'] = 0;

    $leadStatus[102]['id'] = 102;
    // $leadStatus[102]['name'] = "Order Confirm";
    $leadStatus[102]['name'] = 'Token Received';
    $leadStatus[102]['type'] = 1;
    $leadStatus[102]['index'] = 9;
    $leadStatus[102]['is_active'] = 0;

    $leadStatus[103]['id'] = 103;
    $leadStatus[103]['name'] = 'Won';
    $leadStatus[103]['type'] = 1;
    $leadStatus[103]['index'] = 10;
    $leadStatus[103]['is_active'] = 0;

    $leadStatus[104]['id'] = 104;
    $leadStatus[104]['name'] = 'Lost';
    $leadStatus[104]['type'] = 1;
    $leadStatus[104]['index'] = 11;
    $leadStatus[104]['is_active'] = 0;

    $leadStatus[105]['id'] = 105;
    $leadStatus[105]['name'] = 'Cold';
    $leadStatus[105]['type'] = 1;
    $leadStatus[105]['index'] = 12;
    $leadStatus[105]['is_active'] = 0;

    if ($statusID != 0 && $statusID != '') {
        $leadStatus[$statusID]['is_active'] = 1;
    }

    return $leadStatus;
}
function getLeadStatusForArcEle($LeadStatus)
{
    $leadStatus = [];
    $leadStatus[1]['id'] = 1;
    $leadStatus[1]['name'] = 'Entry';
    $leadStatus[1]['is_active'] = 0;

    $leadStatus[2]['id'] = 2;
    $leadStatus[2]['name'] = 'In Progress';
    $leadStatus[2]['is_active'] = 0;

    $leadStatus[3]['id'] = 3;
    $leadStatus[3]['name'] = 'Won';
    $leadStatus[3]['is_active'] = 0;

    $leadStatus[4]['id'] = 4;
    $leadStatus[4]['name'] = 'Lost';
    $leadStatus[4]['is_active'] = 0;

    // $leadStatus[5]['id'] = 5;
    // $leadStatus[5]['name'] = 'Point Claim';
    // $leadStatus[5]['is_active'] = 0;

    if ($LeadStatus == 1) {
        $leadStatus[1]['is_active'] = 1;
    } elseif ($LeadStatus == 103) {
        $leadStatus[3]['is_active'] = 1;
    } elseif ($LeadStatus == 5 || $LeadStatus == 6 || $LeadStatus == 104 || $LeadStatus == 105) {
        $leadStatus[4]['is_active'] = 1;
    } else {
        $leadStatus[2]['is_active'] = 1;
    }

    return $leadStatus;
}

function getLeadNextStatus($status_id)
{
    if (in_array($status_id, [5, 6, 103, 104, 105])) {
        $nextstatus = [];
        $nextstatus['id'] = 0;
        $nextstatus['name'] = 'None';
        $nextstatus['type'] = 0;
        $nextstatus['index'] = 0;
        $nextstatus['is_active'] = 0;
    } else {
        $status_id = (int) $status_id;
        $next_status_index = (int) getLeadStatus()[$status_id]['index'] + 1;
        $nextstatus = '';
        foreach (getLeadStatus() as $key => $value) {
            if ($value['index'] == $next_status_index) {
                $nextstatus = $value;
            }
        }
    }

    return $nextstatus;
}

function getArchitectsSourceTypes()
{
    $sourceTypes = [];

    $cSourceType = count($sourceTypes);
    $sourceTypeObject = [];
    $sourceTypeObject['lable'] = 'None';
    $sourceTypeObject['type'] = 'fix';
    $sourceTypeObject['id'] = 50;
    $sourceTypes[$cSourceType] = $sourceTypeObject;

    $cSourceType = count($sourceTypes);
    $sourceTypeObject = [];
    $sourceTypeObject['lable'] = 'Electrician(Non Prime)';
    $sourceTypeObject['type'] = 'user';
    $sourceTypeObject['id'] = 301;
    $sourceTypes[$cSourceType] = $sourceTypeObject;

    $cSourceType = count($sourceTypes);

    $sourceTypeObject = [];
    $sourceTypeObject['lable'] = 'Electrician(Prime)';
    $sourceTypeObject['type'] = 'user';
    $sourceTypeObject['id'] = 302;
    $sourceTypes[$cSourceType] = $sourceTypeObject;

    $cSourceType = count($sourceTypes);

    $sourceTypeObject = [];
    $sourceTypeObject['lable'] = 'ASM';
    $sourceTypeObject['type'] = 'user';
    $sourceTypeObject['id'] = 101;
    $sourceTypes[$cSourceType] = $sourceTypeObject;

    $cSourceType = count($sourceTypes);

    $sourceTypeObject = [];
    $sourceTypeObject['lable'] = 'ADM';
    $sourceTypeObject['type'] = 'user';
    $sourceTypeObject['id'] = 102;
    $sourceTypes[$cSourceType] = $sourceTypeObject;
    $cSourceType = count($sourceTypes);

    $sourceTypeObject = [];
    $sourceTypeObject['lable'] = 'APM';
    $sourceTypeObject['type'] = 'user';
    $sourceTypeObject['id'] = 103;
    $sourceTypes[$cSourceType] = $sourceTypeObject;
    $cSourceType = count($sourceTypes);

    $sourceTypeObject = [];
    $sourceTypeObject['lable'] = 'AD';
    $sourceTypeObject['type'] = 'user';
    $sourceTypeObject['id'] = 104;
    $sourceTypes[$cSourceType] = $sourceTypeObject;
    $cSourceType = count($sourceTypes);

    $sourceTypeObject = [];
    $sourceTypeObject['lable'] = 'Retailer';
    $sourceTypeObject['type'] = 'user';
    $sourceTypeObject['id'] = 105;
    $sourceTypes[$cSourceType] = $sourceTypeObject;

    // $cSourceType = count($sourceTypes);
    // $sourceTypeObject = array();
    // $sourceTypeObject['lable'] = "Retailer";
    // $sourceTypeObject['type'] = "textrequired";
    // $sourceTypeObject['id'] = 51;
    // $sourceTypes[$cSourceType] = $sourceTypeObject;

    $cSourceType = count($sourceTypes);
    $sourceTypeObject = [];
    $sourceTypeObject['lable'] = 'Whitelion HO';
    $sourceTypeObject['type'] = 'textnotrequired';
    $sourceTypeObject['id'] = 52;
    $sourceTypes[$cSourceType] = $sourceTypeObject;

    $cSourceType = count($sourceTypes);
    $sourceTypeObject = [];
    $sourceTypeObject['lable'] = 'Cold call';
    $sourceTypeObject['type'] = 'fix';
    $sourceTypeObject['id'] = 53;
    $sourceTypes[$cSourceType] = $sourceTypeObject;

    $cSourceType = count($sourceTypes);
    $sourceTypeObject = [];
    $sourceTypeObject['lable'] = 'Marketing activities';
    $sourceTypeObject['type'] = 'fix';
    $sourceTypeObject['id'] = 54;
    $sourceTypes[$cSourceType] = $sourceTypeObject;

    $cSourceType = count($sourceTypes);
    $sourceTypeObject = [];
    $sourceTypeObject['lable'] = 'Other';
    $sourceTypeObject['type'] = 'textrequired';
    $sourceTypeObject['id'] = 55;
    $sourceTypes[$cSourceType] = $sourceTypeObject;

    $cSourceType = count($sourceTypes);
    $sourceTypeObject = [];
    $sourceTypeObject['lable'] = 'Existing Client';
    $sourceTypeObject['type'] = 'textnotrequired';
    $sourceTypeObject['id'] = 56;
    $sourceTypes[$cSourceType] = $sourceTypeObject;

    return $sourceTypes;
}

function getInquirySourceTypes()
{
    $sourceTypes = [];
    $cSourceType = count($sourceTypes);
    $sourceTypeObject = [];
    $sourceTypeObject['lable'] = 'Architect(Non Prime)';
    $sourceTypeObject['type'] = 'user';
    $sourceTypeObject['id'] = 201;
    $sourceTypes[$cSourceType] = $sourceTypeObject;
    $cSourceType = count($sourceTypes);

    $sourceTypeObject = [];
    $sourceTypeObject['lable'] = 'Architect(Prime)';
    $sourceTypeObject['type'] = 'user';
    $sourceTypeObject['id'] = 202;
    $sourceTypes[$cSourceType] = $sourceTypeObject;

    $cSourceType = count($sourceTypes);
    $sourceTypeObject = [];
    $sourceTypeObject['lable'] = 'Electrician(Non Prime)';
    $sourceTypeObject['type'] = 'user';
    $sourceTypeObject['id'] = 301;
    $sourceTypes[$cSourceType] = $sourceTypeObject;

    $cSourceType = count($sourceTypes);

    $sourceTypeObject = [];
    $sourceTypeObject['lable'] = 'Electrician(Prime)';
    $sourceTypeObject['type'] = 'user';
    $sourceTypeObject['id'] = 302;
    $sourceTypes[$cSourceType] = $sourceTypeObject;

    $cSourceType = count($sourceTypes);

    $sourceTypeObject = [];
    $sourceTypeObject['lable'] = 'ASM';
    $sourceTypeObject['type'] = 'user';
    $sourceTypeObject['id'] = 101;
    $sourceTypes[$cSourceType] = $sourceTypeObject;

    $cSourceType = count($sourceTypes);

    $sourceTypeObject = [];
    $sourceTypeObject['lable'] = 'ADM';
    $sourceTypeObject['type'] = 'user';
    $sourceTypeObject['id'] = 102;
    $sourceTypes[$cSourceType] = $sourceTypeObject;
    $cSourceType = count($sourceTypes);

    $sourceTypeObject = [];
    $sourceTypeObject['lable'] = 'APM';
    $sourceTypeObject['type'] = 'user';
    $sourceTypeObject['id'] = 103;
    $sourceTypes[$cSourceType] = $sourceTypeObject;
    $cSourceType = count($sourceTypes);

    $sourceTypeObject = [];
    $sourceTypeObject['lable'] = 'AD';
    $sourceTypeObject['type'] = 'user';
    $sourceTypeObject['id'] = 104;
    $sourceTypes[$cSourceType] = $sourceTypeObject;

    $cSourceType = count($sourceTypes);
    $sourceTypeObject = [];
    $sourceTypeObject['lable'] = 'Retailer';
    $sourceTypeObject['type'] = 'user';
    $sourceTypeObject['id'] = 105;
    $sourceTypes[$cSourceType] = $sourceTypeObject;

    // $cSourceType = count($sourceTypes);
    // $sourceTypeObject = array();
    // $sourceTypeObject['lable'] = "Retailer";
    // $sourceTypeObject['type'] = "textrequired";
    // $sourceTypeObject['id'] = 1;
    // $sourceTypes[$cSourceType] = $sourceTypeObject;

    $cSourceType = count($sourceTypes);
    $sourceTypeObject = [];
    $sourceTypeObject['lable'] = 'Whitelion HO';
    $sourceTypeObject['type'] = 'textnotrequired';

    $sourceTypeObject['id'] = 2;
    $sourceTypes[$cSourceType] = $sourceTypeObject;

    $cSourceType = count($sourceTypes);
    $sourceTypeObject = [];
    $sourceTypeObject['lable'] = 'Cold call';
    $sourceTypeObject['type'] = 'fix';
    $sourceTypeObject['id'] = 3;
    $sourceTypes[$cSourceType] = $sourceTypeObject;

    $cSourceType = count($sourceTypes);
    $sourceTypeObject = [];
    $sourceTypeObject['lable'] = 'Marketing activities';
    // $sourceTypeObject['type'] = "fix";
    $sourceTypeObject['type'] = 'user';
    $sourceTypeObject['id'] = 4;
    $sourceTypes[$cSourceType] = $sourceTypeObject;

    $cSourceType = count($sourceTypes);
    $sourceTypeObject = [];
    $sourceTypeObject['lable'] = 'Other';
    $sourceTypeObject['type'] = 'textrequired';
    $sourceTypeObject['id'] = 5;
    $sourceTypes[$cSourceType] = $sourceTypeObject;

    $cSourceType = count($sourceTypes);
    $sourceTypeObject = [];
    $sourceTypeObject['lable'] = 'Existing Client';
    $sourceTypeObject['type'] = 'textnotrequired';
    $sourceTypeObject['id'] = 6;
    $sourceTypes[$cSourceType] = $sourceTypeObject;

    $cSourceType = count($sourceTypes);
    $sourceTypeObject = [];
    $sourceTypeObject['lable'] = 'Third Party';
    $sourceTypeObject['type'] = 'user';
    $sourceTypeObject['id'] = 8;
    $sourceTypes[$cSourceType] = $sourceTypeObject;

    $cSourceType = count($sourceTypes);
    $sourceTypeObject = [];
    $sourceTypeObject['lable'] = 'Exhibition';
    $sourceTypeObject['type'] = 'exhibition';
    $sourceTypeObject['id'] = 9;
    $sourceTypes[$cSourceType] = $sourceTypeObject;

    $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
    if ($isAdminOrCompanyAdmin == 1) {
        $cSourceType = count($sourceTypes);
        $sourceTypeObject = [];
        $sourceTypeObject['lable'] = 'None';
        $sourceTypeObject['type'] = 'fix';
        $sourceTypeObject['id'] = 0;
        $sourceTypes[$cSourceType] = $sourceTypeObject;
    }

    return $sourceTypes;
}

function getLeadSourceTypes()
{
    $sourceTypes = [];
    // $cSourceType = count($sourceTypes);
    // $sourceTypeObject = array();
    // $sourceTypeObject['lable'] = "Architect(Non Prime)";
    // $sourceTypeObject['type'] = "user";
    // $sourceTypeObject['id'] = 201;
    // $sourceTypes[$cSourceType] = $sourceTypeObject;
    $cSourceType = count($sourceTypes);

    $sourceTypeObject = [];
    // $sourceTypeObject['lable'] = "Architect(Prime)";
    $sourceTypeObject['lable'] = 'Architect';
    $sourceTypeObject['type'] = 'user';
    $sourceTypeObject['is_editable'] = 1;
    $sourceTypeObject['id'] = 202;
    $sourceTypes[$cSourceType] = $sourceTypeObject;

    $cSourceType = count($sourceTypes);
    // $sourceTypeObject = array();
    // $sourceTypeObject['lable'] = "Electrician(Non Prime)";
    // $sourceTypeObject['type'] = "user";
    // $sourceTypeObject['id'] = 301;
    // $sourceTypes[$cSourceType] = $sourceTypeObject;

    // $cSourceType = count($sourceTypes);

    $sourceTypeObject = [];
    // $sourceTypeObject['lable'] = "Electrician(Prime)";
    $sourceTypeObject['lable'] = 'Electrician';
    $sourceTypeObject['type'] = 'user';
    $sourceTypeObject['is_editable'] = 1;
    $sourceTypeObject['id'] = 302;
    $sourceTypes[$cSourceType] = $sourceTypeObject;

    $cSourceType = count($sourceTypes);

    $sourceTypeObject = [];
    $sourceTypeObject['lable'] = 'ASM';
    $sourceTypeObject['type'] = 'user';
    $sourceTypeObject['is_editable'] = 1;
    $sourceTypeObject['id'] = 101;
    $sourceTypes[$cSourceType] = $sourceTypeObject;

    $cSourceType = count($sourceTypes);

    $sourceTypeObject = [];
    $sourceTypeObject['lable'] = 'ADM';
    $sourceTypeObject['type'] = 'user';
    $sourceTypeObject['is_editable'] = 1;
    $sourceTypeObject['id'] = 102;
    $sourceTypes[$cSourceType] = $sourceTypeObject;
    $cSourceType = count($sourceTypes);

    $sourceTypeObject = [];
    $sourceTypeObject['lable'] = 'APM';
    $sourceTypeObject['type'] = 'user';
    $sourceTypeObject['is_editable'] = 1;
    $sourceTypeObject['id'] = 103;
    $sourceTypes[$cSourceType] = $sourceTypeObject;
    $cSourceType = count($sourceTypes);

    $sourceTypeObject = [];
    $sourceTypeObject['lable'] = 'AD';
    $sourceTypeObject['type'] = 'user';
    $sourceTypeObject['is_editable'] = 1;
    $sourceTypeObject['id'] = 104;
    $sourceTypes[$cSourceType] = $sourceTypeObject;

    $cSourceType = count($sourceTypes);
    $sourceTypeObject = [];
    $sourceTypeObject['lable'] = 'Retailer';
    $sourceTypeObject['type'] = 'user';
    $sourceTypeObject['is_editable'] = 1;
    $sourceTypeObject['id'] = 105;
    $sourceTypes[$cSourceType] = $sourceTypeObject;

    $cSourceType = count($sourceTypes);
    $sourceTypeObject = [];
    $sourceTypeObject['lable'] = 'Whitelion HO';
    $sourceTypeObject['type'] = 'textnotrequired';
    $sourceTypeObject['is_editable'] = 1;
    $sourceTypeObject['id'] = 2;
    $sourceTypes[$cSourceType] = $sourceTypeObject;

    $cSourceType = count($sourceTypes);
    $sourceTypeObject = [];
    $sourceTypeObject['lable'] = 'Cold call';
    $sourceTypeObject['type'] = 'fix';
    $sourceTypeObject['is_editable'] = 1;
    $sourceTypeObject['id'] = 3;
    $sourceTypes[$cSourceType] = $sourceTypeObject;

    $cSourceType = count($sourceTypes);
    $sourceTypeObject = [];
    $sourceTypeObject['lable'] = 'Marketing activities';
    // $sourceTypeObject['type'] = "fix";
    $sourceTypeObject['type'] = 'user';
    $sourceTypeObject['is_editable'] = 0;
    $sourceTypeObject['id'] = 4;
    $sourceTypes[$cSourceType] = $sourceTypeObject;

    $cSourceType = count($sourceTypes);
    $sourceTypeObject = [];
    $sourceTypeObject['lable'] = 'Other';
    $sourceTypeObject['type'] = 'textrequired';
    $sourceTypeObject['is_editable'] = 0;
    $sourceTypeObject['id'] = 5;
    $sourceTypes[$cSourceType] = $sourceTypeObject;

    // $cSourceType = count($sourceTypes);
    // $sourceTypeObject = [];
    // $sourceTypeObject['lable'] = 'Other';
    // $sourceTypeObject['type'] = 'textrequired';
    // $sourceTypeObject['id'] = 1;
    // $sourceTypes[$cSourceType] = $sourceTypeObject;

    $cSourceType = count($sourceTypes);
    $sourceTypeObject = [];
    $sourceTypeObject['lable'] = 'Facebook';
    $sourceTypeObject['type'] = 'textnotrequired';
    $sourceTypeObject['is_editable'] = 0;
    $sourceTypeObject['id'] = 1;
    $sourceTypes[$cSourceType] = $sourceTypeObject;

    $cSourceType = count($sourceTypes);
    $sourceTypeObject = [];
    $sourceTypeObject['lable'] = 'Instagram';
    $sourceTypeObject['type'] = 'textnotrequired';
    $sourceTypeObject['is_editable'] = 0;
    $sourceTypeObject['id'] = 11;
    $sourceTypes[$cSourceType] = $sourceTypeObject;

    $cSourceType = count($sourceTypes);
    $sourceTypeObject = [];
    $sourceTypeObject['lable'] = 'Google Ads';
    $sourceTypeObject['type'] = 'textnotrequired';
    $sourceTypeObject['is_editable'] = 0;
    $sourceTypeObject['id'] = 12;
    $sourceTypes[$cSourceType] = $sourceTypeObject;

    $cSourceType = count($sourceTypes);
    $sourceTypeObject = [];
    $sourceTypeObject['lable'] = 'Existing Client';
    $sourceTypeObject['type'] = 'textnotrequired';
    $sourceTypeObject['is_editable'] = 1;
    $sourceTypeObject['id'] = 6;
    $sourceTypes[$cSourceType] = $sourceTypeObject;

    $cSourceType = count($sourceTypes);
    $sourceTypeObject = [];
    $sourceTypeObject['lable'] = 'Third Party';
    $sourceTypeObject['type'] = 'user';
    $sourceTypeObject['is_editable'] = 0;
    $sourceTypeObject['id'] = 8;
    $sourceTypes[$cSourceType] = $sourceTypeObject;

    $cSourceType = count($sourceTypes);
    $sourceTypeObject = [];
    $sourceTypeObject['lable'] = 'Exhibition';
    $sourceTypeObject['type'] = 'exhibition';
    $sourceTypeObject['is_editable'] = 0;
    $sourceTypeObject['id'] = 9;
    $sourceTypes[$cSourceType] = $sourceTypeObject;

    sort($sourceTypes);

    return $sourceTypes;
}

function getAllUserTypes()
{
    $userTypes = getUserTypes();
    $channelPartners = getChannelPartners();
    $architects = getArchitects();
    $electricians = getElectricians();
    $customers = getCustomers();
    foreach ($channelPartners as $key => $value) {
        $userTypes[$key] = $value;
    }
    foreach ($architects as $key => $value) {
        $userTypes[$key] = $value;
    }
    foreach ($electricians as $key => $value) {
        $userTypes[$key] = $value;
    }
    foreach ($customers as $key => $value) {
        $userTypes[$key] = $value;
    }

    return $userTypes;
}

function getUserTypeName($userType)
{
    $userType = (int) $userType;
    $userTypeLable = '';
    if (isset(getUserTypes()[$userType]['short_name'])) {
        $userTypeLable = getUserTypes()[$userType]['short_name'];
    } elseif (isset(getChannelPartners()[$userType]['short_name'])) {
        $userTypeLable = getChannelPartners()[$userType]['short_name'];
    } elseif (isset(getArchitects()[$userType]['short_name'])) {
        $userTypeLable = getArchitects()[$userType]['short_name'];
    } elseif (isset(getElectricians()[$userType]['short_name'])) {
        $userTypeLable = getElectricians()[$userType]['short_name'];
    } elseif (isset(getCustomers()[$userType]['short_name'])) {
        $userTypeLable = getCustomers()[$userType]['short_name'];
    }

    return $userTypeLable;
}

function getUserTypeMainLabel($userType)
{
    $userType = (int) $userType;
    $userTypeLable = '';
    if (isset(getUserTypes()[$userType]['short_name'])) {
        $userTypeLable = getUserTypes()[$userType]['short_name'];
    } elseif (isset(getChannelPartners()[$userType]['short_name'])) {
        $userTypeLable = getChannelPartners()[$userType]['short_name'];
    } elseif (isset(getArchitects()[$userType]['short_name'])) {
        $userTypeLable = 'ARCHITECT ' . getArchitects()[$userType]['short_name'];
    } elseif (isset(getElectricians()[$userType]['short_name'])) {
        $userTypeLable = 'ELECTRICIAN ' . getElectricians()[$userType]['short_name'];
    } elseif (isset(getCustomers()[$userType]['short_name'])) {
        $userTypeLable = getCustomers()[$userType]['short_name'];
    }

    return $userTypeLable;
}
function getUserTypeNameForLeadTag($userType)
{
    $userType = (int) $userType;
    $userTypeLable = '';
    if (isset(getUserTypes()[$userType]['short_name'])) {
        $userTypeLable = getUserTypes()[$userType]['short_name'];
    } elseif (isset(getChannelPartners()[$userType]['short_name'])) {
        $userTypeLable = getChannelPartners()[$userType]['short_name'];
    } elseif (isset(getArchitects()[$userType]['short_name'])) {
        $userTypeLable = 'ARCHITECT ' . getArchitects()[$userType]['short_name'];
    } elseif (isset(getElectricians()[$userType]['short_name'])) {
        $userTypeLable = 'ELECTRICIAN ' . getElectricians()[$userType]['short_name'];
    } elseif (isset(getCustomers()[$userType]['short_name'])) {
        $userTypeLable = getCustomers()[$userType]['short_name'];
    }

    return $userTypeLable;
}

function getChannelPartnersForAccount()
{
    if (Auth::user()->parent_id != 0) {
        $ChannelPartner = ChannelPartner::where('user_id', Auth::user()->parent_id)->first();
        $viewAccountOFChannelPartner = [];
        if ($ChannelPartner) {
            $viewAccountOFChannelPartner[] = getChannelPartners()[$ChannelPartner->type];
        }
    } else {
        $viewAccountOFChannelPartner = getChannelPartners();
    }

    return $viewAccountOFChannelPartner;
}

function isChannelPartner($userType)
{
    $isChannelPartner = 0;
    if (isset(getChannelPartners()[$userType]['id'])) {
        $isChannelPartner = getChannelPartners()[$userType]['id'];
    }
    return $isChannelPartner;
}

function isAdminOrCompanyAdmin()
{
    return Auth::user()->type == 0 || Auth::user()->type == 1 ? 1 : 0;
}
function isAdmin()
{
    return Auth::user()->type == 0 ? 1 : 0;
}
function isCompanyAdmin()
{
    return Auth::user()->type == 1 ? 1 : 0;
}
function isSalePerson()
{
    return Auth::user()->type == 2 ? 1 : 0;
}

function isPurchasePerson()
{
    return Auth::user()->type == 10 ? 1 : 0;
}
function isAccountUser()
{
    return Auth::user()->type == 3 ? 1 : 0;
}

function isDispatcherUser()
{
    return Auth::user()->type == 4 ? 1 : 0;
}
function isArchitect()
{
    return Auth::user()->type == 202 ? 1 : 0;
}
function isReception()
{
    return Auth::user()->type == 12 ? 1 : 0;
}
function isChannelPartnerADM()
{
    return Auth::user()->type == 102 ? 1 : 0;
}

function isElectrician()
{
    return Auth::user()->type == 302 ? 1 : 0;
}

function isMarketingUser()
{
    return Auth::user()->type == 6 ? 1 : 0;
}

function isMarketingDispatcherUser()
{
    return Auth::user()->type == 7 ? 1 : 0;
}

function isThirdPartyUser()
{
    return Auth::user()->type == 8 ? 1 : 0;
}

function isTaleSalesUser()
{
    return Auth::user()->type == 9 ? 1 : 0;
}
function isCreUser()
{
    return Auth::user()->type == 13 ? 1 : 0;
}
function userHasAcccess($userType)
{
    $accessTypes = getUsersAccess(Auth::user()->type);

    $accessTypesList = [];
    foreach ($accessTypes as $key => $value) {
        $accessTypesList[] = $value['id'];
    }

    if (in_array($userType, $accessTypesList)) {
        return true;
    } else {
        return false;
    }
}

function TeleSalesCity($userId)
{
    $TeleSales = TeleSales::where('user_id', $userId)->first();
    $cities = [0];
    if ($TeleSales) {
        if ($TeleSales->cities != '') {
            $cities = explode(',', $TeleSales->cities);
        }
    }
    return $cities;
}

function SalesCity($userId)
{
    $SalePerson = SalePerson::where('user_id', $userId)->first();
    $cities = [0];
    if ($SalePerson) {
        if ($SalePerson->cities != '') {
            $cities = explode(',', $SalePerson->cities);
        }
    }
    return $cities;
}

// function getInquiryStatusTabs($inquiryStatus) {

// 	$list = array();
// 	if ($inquiryStatus == "all") {

// 		$list = getInquiryStatus();

// 	} else {
// 		$inquiryStatus = explode(",", $inquiryStatus);
// 		foreach ($inquiryStatus as $key => $value) {
// 			$list[$key] = getInquiryStatus()[$value];
// 		}
// 	}

// 	return $list;

// }

function getUsersAccess($userType)
{
    $accessArray = [];

    $AllUserTypes = getUserTypes();

    if ($userType == 0) {
        $accessIds = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13];

        foreach ($accessIds as $key => $value) {
            $accessArray[$key] = $AllUserTypes[$value];
        }
    } elseif ($userType == 1) {
        $accessIds = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13];

        foreach ($accessIds as $key => $value) {
            $accessArray[$key] = $AllUserTypes[$value];
        }
    } elseif ($userType == 2) {
        $accessIds = [];

        foreach ($accessIds as $key => $value) {
            $accessArray[$key] = $AllUserTypes[$value];
        }
    } elseif ($userType == 3) {
        $accessIds = [];

        foreach ($accessIds as $key => $value) {
            $accessArray[$key] = $AllUserTypes[$value];
        }
    } elseif ($userType == 4) {
        $accessIds = [];

        foreach ($accessIds as $key => $value) {
            $accessArray[$key] = $AllUserTypes[$value];
        }
    } elseif ($userType == 5) {
        $accessIds = [];

        foreach ($accessIds as $key => $value) {
            $accessArray[$key] = $AllUserTypes[$value];
        }
    } elseif ($userType == 6) {
        $accessIds = [];

        foreach ($accessIds as $key => $value) {
            $accessArray[$key] = $AllUserTypes[$value];
        }
    } elseif ($userType == 7) {
        $accessIds = [];

        foreach ($accessIds as $key => $value) {
            $accessArray[$key] = $AllUserTypes[$value];
        }
    } elseif ($userType == 101) {
        $accessIds = [3, 4];

        foreach ($accessIds as $key => $value) {
            $accessArray[$key] = $AllUserTypes[$value];
        }
    } elseif ($userType == 102) {
        $accessIds = [3, 4];

        foreach ($accessIds as $key => $value) {
            $accessArray[$key] = $AllUserTypes[$value];
        }
    } elseif ($userType == 103) {
        $accessIds = [3, 4];

        foreach ($accessIds as $key => $value) {
            $accessArray[$key] = $AllUserTypes[$value];
        }
    } elseif ($userType == 104) {
        $accessIds = [];

        foreach ($accessIds as $key => $value) {
            $accessArray[$key] = $AllUserTypes[$value];
        }
    } elseif ($userType == 105) {
        $accessIds = [];

        foreach ($accessIds as $key => $value) {
            $accessArray[$key] = $AllUserTypes[$value];
        }
    } elseif ($userType == 11) {
        $accessIds = [0, 1, 11];

        foreach ($accessIds as $key => $value) {
            $accessArray[$key] = $AllUserTypes[$value];
        }
    }
    return $accessArray;
}

function getChannelPartnersAccess($userType)
{
    $accessArray = [];

    $AllUserTypes = getChannelPartners();

    if ($userType == 0) {
        $accessIds = [101, 102, 103, 104, 105, 106];

        foreach ($accessIds as $key => $value) {
            $accessArray[$key] = $AllUserTypes[$value];
        }
    } elseif ($userType == 1) {
        $accessIds = [101, 102, 103, 104, 105, 106];

        foreach ($accessIds as $key => $value) {
            $accessArray[$key] = $AllUserTypes[$value];
        }
    } elseif ($userType == 2) {
        $accessIds = [104, 105];

        foreach ($accessIds as $key => $value) {
            $accessArray[$key] = $AllUserTypes[$value];
        }
    } elseif ($userType == 6) {
        $accessIds = [101, 102, 103, 104, 105];

        foreach ($accessIds as $key => $value) {
            $accessArray[$key] = $AllUserTypes[$value];
        }
    } elseif ($userType == 9) {
        $accessIds = [101, 102, 103, 104, 105];

        foreach ($accessIds as $key => $value) {
            $accessArray[$key] = $AllUserTypes[$value];
        }
    }

    return $accessArray;
}

function getsubOrdersTabs($userType)
{
    $accessArray = [];

    $AllChannelPartners = getChannelPartners();

    if ($userType == 0) {
        $accessIds = [101, 102, 103, 104, 105];

        foreach ($accessIds as $key => $value) {
            $accessArray[$key] = $AllChannelPartners[$value];
        }
    } elseif ($userType == 1) {
        $accessIds = [101, 102, 103, 104, 105];

        foreach ($accessIds as $key => $value) {
            $accessArray[$key] = $AllChannelPartners[$value];
        }
    } elseif ($userType == 101) {
        $accessIds = [102, 103, 104, 105];

        foreach ($accessIds as $key => $value) {
            $accessArray[$key] = $AllChannelPartners[$value];
        }
    } elseif ($userType == 102) {
        $accessIds = [103, 104, 105];

        foreach ($accessIds as $key => $value) {
            $accessArray[$key] = $AllChannelPartners[$value];
        }
    } elseif ($userType == 103) {
        $accessIds = [104, 105];

        foreach ($accessIds as $key => $value) {
            $accessArray[$key] = $AllChannelPartners[$value];
        }
    }

    return $accessArray;
}
function createThumbs($sourceFilePath, $destinationFilePath, $maxWidth)
{
    /////////// CREATE THUMB

    $quality = 100;
    $imgsize = getimagesize($sourceFilePath);
    $width = $imgsize[0];
    $height = $imgsize[1];
    $mime = $imgsize['mime'];

    switch ($mime) {
        case 'image/gif':
            $imageCreate = 'imagecreatefromgif';
            $image = 'imagegif';
            break;

        case 'image/png':
            $imageCreate = 'imagecreatefrompng';
            $image = 'imagepng';
            $quality = 7;
            break;

        case 'image/jpeg':
            $imageCreate = 'imagecreatefromjpeg';
            $image = 'imagejpeg';
            $quality = 80;
            break;
        default:
            return false;
            break;
    }

    $scalRatio = $maxWidth / $width;
    $maxHeight = round($scalRatio * $height);
    $dstImg = imagecreatetruecolor($maxWidth, $maxHeight);
    ///////////////
    imagealphablending($dstImg, false);
    imagesavealpha($dstImg, true);
    ///IF IMAGE IS TRANSPERANT THEN THUMBNAI RESIZABLE IMAGE WILL TRANSPERANT ,,IF NOT USE THIS FUNCTION GET IMAGE BACKGROUD WHITE
    $transparent = imagecolorallocatealpha($dstImg, 255, 255, 255, 127);
    imagefilledrectangle($dstImg, 0, 0, $maxWidth, $maxHeight, $transparent);
    /////////////
    $srcImg = $imageCreate($sourceFilePath);
    imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $maxWidth, $maxHeight, $width, $height);

    $image($dstImg, $destinationFilePath, $quality);
    if ($dstImg) {
        imagedestroy($dstImg);
    }

    if ($srcImg) {
        imagedestroy($srcImg);
    }
}

function getSalesPersonReportingManager($userId)
{
    $SalePerson = SalePerson::select('reporting_manager_id')
        ->where('user_id', $userId)
        ->first();
    return $SalePerson;
}

function getChannelPartnerSalesPersonsIds($userId)
{
    $ChannelPartner = ChannelPartner::select('sale_persons')
        ->where('user_id', $userId)
        ->first();
    $salesPersons = [];
    if ($ChannelPartner) {
        $salePersons = $ChannelPartner->sale_persons;
        $salePersons = explode(',', $salePersons);
    }

    return $salePersons;
}

function getChildSalePersonsIds($userId)
{
    $SalePersons = SalePerson::select('user_id')
        ->where('reporting_manager_id', $userId)
        ->get();
    $SalePersonsIds = [];
    $SalePersonsIds[] = $userId;

    foreach ($SalePersons as $key => $value) {
        $SalePersonsIds[] = $value['user_id'];
        $getChildSalePersonsIds = getChildSalePersonsIds($value['user_id']);
        $SalePersonsIds = array_merge($SalePersonsIds, $getChildSalePersonsIds);
    }
    $SalePersonsIds = array_unique($SalePersonsIds);
    $SalePersonsIds = array_values($SalePersonsIds);
    return $SalePersonsIds;
}

function getParentSalePersonsIds($userId)
{
    $SalePersons = SalePerson::select('reporting_manager_id')
        ->where('user_id', $userId)
        ->first();
    $SalePersonsIds = [];
    if ($SalePersons) {
        if ($SalePersons->reporting_manager_id == 0) {
            return [0];
        } else {
            $SalePersonsIds[] = $SalePersons->reporting_manager_id;

            $getParentsSalePersonsIds = getParentSalePersonsIds($SalePersons->reporting_manager_id);

            $SalePersonsIds = array_merge($SalePersonsIds, $getParentsSalePersonsIds);
        }
    } else {
        return [0];
    }
    $SalePersonsIds = array_unique($SalePersonsIds);
    $SalePersonsIds = array_values($SalePersonsIds);
    return $SalePersonsIds;
}

function getParentSalePersonsIdsforLead($userId)
{
    $SalePersons = SalePerson::select('reporting_manager_id')
        ->where('user_id', $userId)
        ->first();
    $SalePersonsIds = [];
    $SalePersonsIds[] = $userId;
    if ($SalePersons) {
        if ($SalePersons->reporting_manager_id == 0) {
            return [0];
        } else {
            $SalePersonsIds[] = $SalePersons->reporting_manager_id;

            $getParentsSalePersonsIds = getParentSalePersonsIds($SalePersons->reporting_manager_id);

            $SalePersonsIds = array_merge($SalePersonsIds, $getParentsSalePersonsIds);
        }
    } else {
        return [0];
    }
    $SalePersonsIds = array_unique($SalePersonsIds);
    $SalePersonsIds = array_values($SalePersonsIds);
    return $SalePersonsIds;
}

function UsersNotificationTokens($userId)
{
    $notificationTokens = [];
    $Users = User::select('fcm_token')
        ->whereIn('id', $userId)
        ->orWhere('type', 0)
        ->get();
    if (count($Users) > 0) {
        foreach ($Users as $keyPush => $valuePush) {
            $notificationTokens[] = $valuePush->fcm_token;
        }
    }

    return $notificationTokens;
}

function getServiceHierarchyStatusLable($serviceHierarchyStatus)
{
    $serviceHierarchyStatus = (int) $serviceHierarchyStatus;

    if ($serviceHierarchyStatus == 0) {
        $serviceHierarchyStatus = '<span class="badge badge-pill badge-soft-danger font-size-11"> Inactive</span>';
    } elseif ($serviceHierarchyStatus == 1) {
        $serviceHierarchyStatus = '<span class="badge badge-pill badge-soft-success font-size-11"> Active</span>';
    } elseif ($serviceHierarchyStatus == 2) {
        $serviceHierarchyStatus = '<span class="badge badge-pill badge-soft-danger font-size-11"> Blocked</span>';
    }
    return $serviceHierarchyStatus;
}

function isServiceExecutiveUser()
{
    return Auth::user()->type == 11 ? 1 : 0;
}

function getParentServiceExecutivesIds($userId)
{
    $ServiceExecutives = Wlmst_ServiceExecutive::select('reporting_manager_id')
        ->where('user_id', $userId)
        ->first();
    $ServiceExecutivessIds = [];
    if ($ServiceExecutives) {
        if ($ServiceExecutives->reporting_manager_id == 0) {
            return [0];
        } else {
            $ServiceExecutivessIds[] = $ServiceExecutives->reporting_manager_id;

            $getParentsServiceExecutivessIds = getParentServiceExecutivesIds($ServiceExecutives->reporting_manager_id);

            $ServiceExecutivessIds = array_merge($ServiceExecutivessIds, $getParentsServiceExecutivessIds);
        }
    } else {
        return [0];
    }
    $ServiceExecutivessIds = array_unique($ServiceExecutivessIds);
    $ServiceExecutivessIds = array_values($ServiceExecutivessIds);
    return $ServiceExecutivessIds;
}

function getChildChannelPartners($userId, $type)
{
    $childChannelPartnersId = [];

    if ($type == 0) {
        $ChannelPartners102 = ChannelPartner::select('user_id')
            ->where('type', 102)
            ->where('reporting_manager_id', $userId)
            ->get();

        if (count($ChannelPartners102) > 0) {
            $ChannelPartners102Id = [];

            foreach ($ChannelPartners102 as $key => $value) {
                $ChannelPartners102Id[] = $value->user_id;
            }

            $childChannelPartnersId = array_merge($childChannelPartnersId, $ChannelPartners102Id);

            $ChannelPartners103 = ChannelPartner::select('user_id')
                ->where('type', 103)
                ->whereIn('reporting_manager_id', $ChannelPartners102Id)
                ->get();

            if (count($ChannelPartners103) > 0) {
                $ChannelPartners103Id = [];

                foreach ($ChannelPartners103 as $key => $value) {
                    $ChannelPartners103Id[] = $value->user_id;
                }

                $childChannelPartnersId = array_merge($childChannelPartnersId, $ChannelPartners103Id);

                $ChannelPartners104 = ChannelPartner::select('user_id')
                    ->where('type', 104)
                    ->whereIn('reporting_manager_id', $ChannelPartners103Id)
                    ->get();

                if (count($ChannelPartners104) > 0) {
                    foreach ($ChannelPartners104 as $key => $value) {
                        $childChannelPartnersId[] = $value->user_id;
                    }
                }
            }
        }
    } elseif ($type == 102) {
        $ChannelPartners102 = ChannelPartner::select('user_id')
            ->where('type', 102)
            ->where('reporting_manager_id', $userId)
            ->get();

        if (count($ChannelPartners102) > 0) {
            foreach ($ChannelPartners102 as $key => $value) {
                $childChannelPartnersId[] = $value->user_id;
            }
        }
    } elseif ($type == 103) {
        $ChannelPartners102 = ChannelPartner::select('user_id')
            ->where('type', 102)
            ->where('reporting_manager_id', $userId)
            ->get();

        if (count($ChannelPartners102) > 0) {
            $ChannelPartners102Id = [];

            foreach ($ChannelPartners102 as $key => $value) {
                $ChannelPartners102Id[] = $value->user_id;
            }

            $ChannelPartners103 = ChannelPartner::select('user_id')
                ->where('type', 103)
                ->whereIn('reporting_manager_id', $ChannelPartners102Id)
                ->get();

            if (count($ChannelPartners103) > 0) {
                foreach ($ChannelPartners103 as $key => $value) {
                    $childChannelPartnersId[] = $value->user_id;
                }
            }
        }
    } elseif ($type == 104) {
        $ChannelPartners102 = ChannelPartner::select('user_id')
            ->where('type', 102)
            ->where('reporting_manager_id', $userId)
            ->get();

        if (count($ChannelPartners102) > 0) {
            $ChannelPartners102Id = [];

            foreach ($ChannelPartners102 as $key => $value) {
                $ChannelPartners102Id[] = $value->user_id;
            }

            $ChannelPartners103 = ChannelPartner::select('user_id')
                ->where('type', 103)
                ->whereIn('reporting_manager_id', $ChannelPartners102Id)
                ->get();

            if (count($ChannelPartners103) > 0) {
                $ChannelPartners103Id = [];

                foreach ($ChannelPartners103 as $key => $value) {
                    $ChannelPartners103Id[] = $value->user_id;
                }

                $ChannelPartners104 = ChannelPartner::select('user_id')
                    ->where('type', 104)
                    ->whereIn('reporting_manager_id', $ChannelPartners103Id)
                    ->get();

                if (count($ChannelPartners104) > 0) {
                    foreach ($ChannelPartners104 as $key => $value) {
                        $childChannelPartnersId[] = $value->user_id;
                    }
                }
            }
        }
    } elseif ($type == 105) {
        $ChannelPartners102 = ChannelPartner::select('user_id')
            ->where('type', 102)
            ->where('reporting_manager_id', $userId)
            ->get();

        if (count($ChannelPartners102) > 0) {
            $ChannelPartners102Id = [];

            foreach ($ChannelPartners102 as $key => $value) {
                $ChannelPartners102Id[] = $value->user_id;
            }

            $ChannelPartners103 = ChannelPartner::select('user_id')
                ->where('type', 103)
                ->whereIn('reporting_manager_id', $ChannelPartners102Id)
                ->get();

            if (count($ChannelPartners103) > 0) {
                $ChannelPartners103Id = [];

                foreach ($ChannelPartners103 as $key => $value) {
                    $ChannelPartners103Id[] = $value->user_id;
                }

                $ChannelPartners104 = ChannelPartner::select('user_id')
                    ->where('type', 104)
                    ->whereIn('reporting_manager_id', $ChannelPartners103Id)
                    ->get();

                if (count($ChannelPartners104) > 0) {
                    $ChannelPartners104Id = [];
                    foreach ($ChannelPartners104 as $key => $value) {
                        $ChannelPartners104Id[] = $value->user_id;
                    }

                    $ChannelPartners105 = ChannelPartner::select('user_id')
                        ->where('type', 105)
                        ->whereIn('reporting_manager_id', $ChannelPartners104Id)
                        ->get();

                    if (count($ChannelPartners105) > 0) {
                        foreach ($ChannelPartners105 as $key => $value) {
                            $childChannelPartnersId[] = $value->user_id;
                        }
                    }
                }
            }
        }
    }

    return $childChannelPartnersId;
}

function GSTPercentage()
{
    return 18;
}

function calculationProcessOfOrder($orderItems, $GSTPercentage, $shippingCost)
{
    $order = [];
    $order['total_qty'] = 0;
    $order['total_weight'] = 0;
    $order['total_mrp'] = 0;
    $order['total_discount'] = 0;
    $order['total_mrp_minus_disocunt'] = 0;
    $order['gst_percentage'] = floatval($GSTPercentage);
    $order['gst_tax'] = 0;
    $order['shipping_cost'] = floatval($shippingCost);
    $order['delievery_charge'] = 0;
    $order['total_payable'] = 0;
    $order['created_dt'] = date('Y-m-d H:i:s');

    foreach ($orderItems as $key => $value) {
        $orderItems[$key]['id'] = $value['id'];
        if (isset($value['info'])) {
            $orderItems[$key]['info'] = $value['info'];
        }

        //
        $productPrice = floatval($value['mrp']);
        $orderItems[$key]['mrp'] = $productPrice;
        //

        //
        $orderItemQTY = intval($value['qty']);
        $orderItems[$key]['qty'] = $orderItemQTY;
        $order['total_qty'] = $order['total_qty'] + $orderItemQTY;
        //

        //
        $OrderItemsMRP = $orderItemQTY * $productPrice;
        $orderItems[$key]['total_mrp'] = $OrderItemsMRP;
        $order['total_mrp'] = $order['total_mrp'] + $OrderItemsMRP;
        //

        //
        $discountPercentage = floatval($value['discount_percentage']);
        $orderItems[$key]['discount_percentage'] = $discountPercentage;

        $totalDiscount = 0;
        if ($discountPercentage > 0) {
            $totalDiscount = round(($discountPercentage / 100) * $OrderItemsMRP, 2);
        }

        $discount = 0;

        if ($discountPercentage > 0) {
            $discount = round(($discountPercentage / 100) * $productPrice, 2);
        }

        //
        $orderItems[$key]['discount'] = $discount;
        $orderItems[$key]['total_discount'] = $totalDiscount;
        $order['total_discount'] = round($order['total_discount'] + $totalDiscount, 2);
        //

        //
        $mrpMinusDiscount = round($OrderItemsMRP - $totalDiscount, 2);
        $orderItems[$key]['mrp_minus_disocunt'] = $mrpMinusDiscount;
        $order['total_mrp_minus_disocunt'] = $order['total_mrp_minus_disocunt'] + $mrpMinusDiscount;

        //
        $productWeight = floatval($value['weight']);
        $orderItemTotalWeight = $productWeight * $orderItemQTY;
        $orderItems[$key]['weight'] = $productWeight;
        $orderItems[$key]['total_weight'] = $orderItemTotalWeight;
        $order['total_weight'] = $order['total_weight'] + $orderItemTotalWeight;
    }

    $order['total_mrp_minus_disocunt'] = round($order['total_mrp_minus_disocunt'], 2);

    if ($order['gst_percentage'] != 0) {
        $order['gst_tax'] = round(($order['gst_percentage'] / 100) * $order['total_mrp_minus_disocunt'], 2);
    }

    $order['weightInKG'] = $order['total_weight'] / 1000;
    $order['delievery_charge'] = round($order['weightInKG'] * $order['shipping_cost'], 2);

    $order['total_payable'] = round($order['total_mrp_minus_disocunt'] + $order['gst_tax'] + $order['delievery_charge'], 2);
    $order['items'] = $orderItems;

    return $order;
}

function calculationProcessOfMarketingRequest($orderItems)
{
    $order = [];
    $order['total_qty'] = 0;
    $order['total_weight'] = 0;
    $order['total_mrp'] = 0;
    $order['total_discount'] = 0;
    $order['total_mrp_minus_disocunt'] = 0;
    // $order['gst_percentage'] = floatval($GSTPercentage);
    $order['gst_tax'] = 0;
    $order['shipping_cost'] = 0;
    $order['delievery_charge'] = 0;
    $order['total_payable'] = 0;
    $order['created_dt'] = date('Y-m-d H:i:s');

    foreach ($orderItems as $key => $value) {
        $orderItems[$key]['id'] = $value['id'];
        if (isset($value['info'])) {
            $orderItems[$key]['info'] = $value['info'];
        }

        //
        $productPrice = floatval($value['mrp']);
        $orderItems[$key]['mrp'] = $productPrice;
        $GSTPercentage = floatval($value['gst_percentage']);

        $orderItems[$key]['gst_percentage'] = $GSTPercentage;
        //

        //
        $orderItemQTY = intval($value['qty']);
        $orderItems[$key]['qty'] = $orderItemQTY;
        $order['total_qty'] = $order['total_qty'] + $orderItemQTY;
        //

        //
        $OrderItemsMRP = $orderItemQTY * $productPrice;
        $orderItems[$key]['total_mrp'] = $OrderItemsMRP;
        $order['total_mrp'] = $order['total_mrp'] + $OrderItemsMRP;
        //

        $orderItems[$key]['gst_percentage'] = $value['gst_percentage'];

        //
        $GSTTax = ($productPrice * $GSTPercentage) / 100;
        $orderItems[$key]['gst_tax'] = $GSTTax;
        $GSTTaxTotal = $orderItemQTY * $GSTTax;
        $orderItems[$key]['total_gst_tax'] = $GSTTaxTotal;
        $order['gst_tax'] = $order['gst_tax'] + $GSTTaxTotal;
        //

        //
        $discountPercentage = floatval($value['discount_percentage']);
        $orderItems[$key]['discount_percentage'] = $discountPercentage;

        $totalDiscount = 0;
        if ($discountPercentage > 0) {
            $totalDiscount = round(($discountPercentage / 100) * $OrderItemsMRP, 2);
        }

        $discount = 0;

        if ($discountPercentage > 0) {
            $discount = round(($discountPercentage / 100) * $productPrice, 2);
        }

        //
        $orderItems[$key]['discount'] = $discount;
        $orderItems[$key]['total_discount'] = $totalDiscount;
        $order['total_discount'] = round($order['total_discount'] + $totalDiscount, 2);
        //

        //
        $mrpMinusDiscount = round($OrderItemsMRP - $totalDiscount, 2);
        $orderItems[$key]['mrp_minus_disocunt'] = $mrpMinusDiscount;
        $order['total_mrp_minus_disocunt'] = $order['total_mrp_minus_disocunt'] + $mrpMinusDiscount;

        //	$order['total_mrp_minus_disocunt'] = $order['total_mrp_minus_disocunt'] + $mrpMinusDiscount;

        //
        $productWeight = floatval($value['weight']);
        $orderItemTotalWeight = $productWeight * $orderItemQTY;
        $orderItems[$key]['weight'] = $productWeight;
        $orderItems[$key]['total_weight'] = $orderItemTotalWeight;
        $order['total_weight'] = $order['total_weight'] + $orderItemTotalWeight;

        $orderItems[$key]['width'] = $value['width'];
        $orderItems[$key]['height'] = $value['height'];
        if ($value['box_image'] != '') {
            $orderItems[$key]['box_image'] = $value['box_image'];
        } else {
            $orderItems[$key]['box_image'] = '';
        }

        if ($value['sample_image'] != '') {
            $orderItems[$key]['sample_image'] = $value['sample_image'];
        } else {
            $orderItems[$key]['sample_image'] = '';
        }

        if (isset($value['is_custom'])) {
            $orderItems[$key]['is_custom'] = $value['is_custom'];
        }
    }

    $order['total_mrp_minus_disocunt'] = round($order['total_mrp_minus_disocunt'], 2);
    $order['gst_tax'] = round($order['gst_tax'], 2);

    // if ($order['gst_percentage'] != 0) {
    // 	$order['gst_tax'] = round(($order['gst_percentage'] / 100) * $order['total_mrp_minus_disocunt'], 2);
    // }

    // $order['weightInKG'] = $order['total_weight'] / 1000;
    // $order['delievery_charge'] = (round($order['weightInKG'] * $order['shipping_cost'], 2));

    $order['total_payable'] = round($order['total_mrp_minus_disocunt'] + $order['gst_tax'], 2);
    $order['items'] = $orderItems;

    // echo '<pre>';
    // print_r($order);
    // die;

    return $order;
}

function acceptFileTypes($type, $systemType)
{
    if ($type == 'order.invoice') {
        if ($systemType == 'client') {
            return ['application/pdf', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/*'];
        } elseif ($systemType == 'server') {
            return ['pdf', 'doc', 'xlsx', 'xls', 'png', 'jpeg', 'jpg'];
        }
    } elseif ($type == 'order.eway.bill') {
        if ($systemType == 'client') {
            return ['application/pdf', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/*'];
        } elseif ($systemType == 'server') {
            return ['pdf', 'doc', 'xlsx', 'xls', 'png', 'jpeg', 'jpg'];
        }
    } elseif ($type == 'order.dispatch.detail') {
        if ($systemType == 'client') {
            return ['application/pdf', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/*'];
        } elseif ($systemType == 'server') {
            return ['pdf', 'doc', 'xls', 'xls', 'png', 'jpeg', 'jpg'];
        }
    } elseif ($type == 'gift.order.dispatch.detail') {
        if ($systemType == 'client') {
            return ['application/pdf', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/*'];
        } elseif ($systemType == 'server') {
            return ['pdf', 'doc', 'xls', 'xls', 'png', 'jpeg', 'jpg'];
        }
    } elseif ($type == 'marketing.challan') {
        if ($systemType == 'client') {
            return ['application/pdf', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/*'];
        } elseif ($systemType == 'server') {
            return ['pdf', 'doc', 'xls', 'xls', 'png', 'jpeg', 'jpg'];
        }
    }
}

function getPreviousMonths($noOfMonth)
{
    $GMTDateTime = date('Y-m-d H:i:s');
    $TIMEZONE = websiteTimeZone();
    $dt = new DateTime('@' . strtotime($GMTDateTime));
    $dt->setTimeZone(new DateTimeZone($TIMEZONE));
    $myCurrentDate = $dt->format('Y-m-d H:i:s');

    $r = [];

    for ($i = 0; $i < $noOfMonth; $i++) {
        if ($i != 0) {
            $myCurrentDate = date('Y-m-d H:i:s', strtotime($myCurrentDate . ' -1 month'));
        }

        $r[$i]['start'] = date('Y-m-1 00:00:00', strtotime($myCurrentDate));
        $r[$i]['end'] = date('Y-m-t 23:59:59', strtotime($myCurrentDate));
        $r[$i]['name'] = date('Y-F', strtotime($myCurrentDate));

        $start = new DateTime($r[$i]['start'], new DateTimeZone($TIMEZONE));
        $start->setTimeZone(new DateTimeZone('GMT'));

        $end = new DateTime($r[$i]['end'], new DateTimeZone($TIMEZONE));
        $end->setTimeZone(new DateTimeZone('GMT'));

        $r[$i]['start_gmt'] = $start->format('Y-m-d H:i:s');
        $r[$i]['end_gmt'] = $end->format('Y-m-d H:i:s');
    }

    return $r;
}

function displayStringLenth($string, $maxLength)
{
    $totalStringLenth = strlen($string);
    if ($totalStringLenth > $maxLength) {
        $stringCrop = substr($string, 0, $maxLength - 3);
        $string = $stringCrop . '...';
    }
    return $string;
}

function getUserNotificationTypes()
{
    $userTypes = [];
    $userTypes[1]['id'] = 1;
    $userTypes[1]['description'] = 'Inquiry Update';
    $userTypes[1]['assigned'] = 0;
    $userTypes[1]['mentioned'] = 0;

    $userTypes[2]['id'] = 2;
    $userTypes[2]['description'] = 'Inquiry Update Reply';
    $userTypes[2]['assigned'] = 0;
    $userTypes[2]['mentioned'] = 0;

    $userTypes[3]['id'] = 3;
    $userTypes[3]['description'] = 'Inquiry change assigned';
    $userTypes[3]['assigned'] = 1;
    $userTypes[3]['mentioned'] = 0;

    $userTypes[4]['id'] = 4;
    $userTypes[4]['description'] = 'Inquiry mentioned ';
    $userTypes[4]['assigned'] = 0;
    $userTypes[4]['mentioned'] = 1;

    return $userTypes;
}

function saveUserNotification($params)
{
    if (!isset($params['inquiry_id'])) {
        $params['inquiry_id'] = 0;
    }

    $UserNotification = new UserNotification();
    $UserNotification->user_id = $params['user_id'];
    $UserNotification->type = $params['type'];
    $UserNotification->from_user_id = $params['from_user_id'];
    $UserNotification->title = $params['title'];
    $UserNotification->description = $params['description'];
    $UserNotification->inquiry_id = $params['inquiry_id'];
    $UserNotification->save();
}

function architectInquiryCalculation($userId)
{
    $query = Inquiry::query();
    $query->where(function ($query2) use ($userId) {
        $query2->where(function ($query3) use ($userId) {
            $query3->whereIn('inquiry.source_type', ['user-201', 'user-202']);
            $query3->where('inquiry.source_type_value', $userId);
        });

        $query2->orWhere(function ($query3) use ($userId) {
            $query3->whereIn('inquiry.source_type_1', ['user-201', 'user-202']);
            $query3->where('inquiry.source_type_value_1', $userId);
        });

        $query2->orWhere(function ($query3) use ($userId) {
            $query3->whereIn('inquiry.source_type_2', ['user-201', 'user-202']);
            $query3->where('inquiry.source_type_value_2', $userId);
        });

        $query2->orWhere(function ($query3) use ($userId) {
            $query3->whereIn('inquiry.source_type_3', ['user-201', 'user-202']);
            $query3->where('inquiry.source_type_value_3', $userId);
        });

        $query2->orWhere(function ($query3) use ($userId) {
            $query3->whereIn('inquiry.source_type_4', ['user-201', 'user-202']);
            $query3->where('inquiry.source_type_value_4', $userId);
        });

        $query2->orWhere(function ($query3) use ($userId) {
            $query3->where('inquiry.architect', $userId);
        });
    });
    $recordsTotal = $query->count();
    $Architect = Architect::where('user_id', $userId)->first();
    if ($Architect) {
        $Architect->total_inquiry = $recordsTotal;
        $Architect->save();
    }
}
function elecricianInquiryCalculation($userId)
{
    $query = Inquiry::query();
    $query->where(function ($query2) use ($userId) {
        $query2->where(function ($query3) use ($userId) {
            $query3->whereIn('inquiry.source_type', ['user-301', 'user-302']);
            $query3->where('inquiry.source_type_value', $userId);
        });

        $query2->orWhere(function ($query3) use ($userId) {
            $query3->whereIn('inquiry.source_type_1', ['user-301', 'user-302']);
            $query3->where('inquiry.source_type_value_1', $userId);
        });

        $query2->orWhere(function ($query3) use ($userId) {
            $query3->whereIn('inquiry.source_type_2', ['user-301', 'user-302']);
            $query3->where('inquiry.source_type_value_2', $userId);
        });

        $query2->orWhere(function ($query3) use ($userId) {
            $query3->whereIn('inquiry.source_type_3', ['user-301', 'user-302']);
            $query3->where('inquiry.source_type_value_3', $userId);
        });

        $query2->orWhere(function ($query3) use ($userId) {
            $query3->whereIn('inquiry.source_type_4', ['user-301', 'user-302']);
            $query3->where('inquiry.source_type_value_4', $userId);
        });

        $query2->orWhere(function ($query3) use ($userId) {
            $query3->where('inquiry.electrician', $userId);
        });
    });
    $recordsTotal = $query->count();

    $Electrician = Electrician::where('user_id', $userId)->first();
    if ($Electrician) {
        $Electrician->total_inquiry = $recordsTotal;
        $Electrician->save();
    }
}

function getMyPrivilege($code)
{
    $hasPrivilege = 0;
    if (Auth::user()->privilege != '') {
        $privilege = json_decode(Auth::user()->privilege, true);
        if (isset($privilege[$code]) && $privilege[$code] == 1) {
            $hasPrivilege = 1;
        }
    }
    return $hasPrivilege;
}

function configrationForNotify()
{
    $response = [];
    $response['from_email'] = 'noreply@whitelion.in';
    $response['from_name'] = 'Whitelion';
    $response['to_name'] = 'Whitelion';

    ////TESTING
    $response['test_email'] = 'ankit.in1184@gmail.com';
    // $response['test_email'] = 'sheliyad.03@gmail.com';
    $response['test_phone_number'] = "9824717656";
    // $response['test_phone_number'] = '9016202912';
    $response['test_email_bcc'] = ['akshitaasalaliya16@gmail.com'];
    $response['test_email_cc'] = ['ankit.in1184@gmail.com'];
    return $response;
}

// function fromEmailDetail() {
// 	$fromEmailDetail = array();
// 	$fromEmailDetail['email'] = "developer@whitelion.in";
// 	$fromEmailDetail['name'] = "Whitelion";
// 	return $fromEmailDetail;
// }

function getMainMasterPrivilege($userType)
{
    $MainMasterPrivilege = [];

    if ($userType == 0) {
        $MainMasterPrivilege[] = 'PRODUCT_BRAND';
        $MainMasterPrivilege[] = 'PRODUCT_CODE';
        $MainMasterPrivilege[] = 'INCENTIVE_QUARTER';
        $MainMasterPrivilege[] = 'COURIER_SERVICE';
        $MainMasterPrivilege[] = 'MARKETING_PRODUCT_CODE';
        $MainMasterPrivilege[] = 'MARKETING_PRODUCT_GROUP';
    } elseif ($userType == 1) {
        $MainMasterPrivilege[] = 'PRODUCT_BRAND';
        $MainMasterPrivilege[] = 'PRODUCT_CODE';
        $MainMasterPrivilege[] = 'INCENTIVE_QUARTER';
        $MainMasterPrivilege[] = 'COURIER_SERVICE';
        $MainMasterPrivilege[] = 'MARKETING_PRODUCT_CODE';
        $MainMasterPrivilege[] = 'MARKETING_PRODUCT_GROUP';
    } elseif ($userType == 6) {
        $MainMasterPrivilege[] = 'MARKETING_PRODUCT_CODE';
        $MainMasterPrivilege[] = 'MARKETING_PRODUCT_GROUP';
    }
    return $MainMasterPrivilege;
}

function sendOTPToMobile($mobileNumber, $otp)
{
    if (Config::get('app.env') == 'local') {
        $mobileNumber = '9016202912'; // Poonam
        // $mobileNumber = "9081187602"; // AKSHITA
    }
    $curl = curl_init();
    curl_setopt_array($curl, [
        // CURLOPT_URL => "https://api.msg91.com/api/v5/otp?template_id=624fe2f9427ab2782b2fae2b&mobile=" . $mobileNumber . "&authkey=124116Awe37ib8e57e66f9b&otp=" . $otp,
        CURLOPT_URL => 'https://api.msg91.com/api/v5/otp?template_id=6486f14ed6fc0567113a2fa2&mobile=' . $mobileNumber . '&authkey=124116Awe37ib8e57e66f9b&otp=' . $otp,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => '',
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_SSL_VERIFYPEER => 0,
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
        //echo "cURL Error #:" . $err;
        $return = errorRes('');
        $return['response'] = $err;
        return $return;
    } else {
        $return = successRes('');
        $return['response'] = $response;
        return $return;
    }
}

function sendNotificationTOAndroid($title, $message, $FcmToken, $screenName, $data_value, $image = '')
{
    if (count($FcmToken) > 0) {
        $url = 'https://fcm.googleapis.com/fcm/send';

        $serverKey = 'AAAAjO_9mB8:APA91bFUg5s0ou4vzSmuf6EqTLNu3bLpOXJa-v8GwW9HHzC-27ZtEUFloHiMx0Itc6ZhuN3MOitsjG1eRaV5RjDInoSqT4veSXu-TqnyGL_bFkSIH0hIYUmxB6YA77vVenEWPraVR1ma';

        $data = [
            'registration_ids' => $FcmToken,
            'notification' => [
                'title' => $title,
                'body' => $message,
                'sound' => 'Default',
                'badge' => 1,
                'image' => $image,
            ],
            'data' => [
                'priority' => 'high',
                'sound' => 'default',
                'content_available' => true,
                'screen' => $screenName,
                'data_value' => json_encode($data_value),
            ],
        ];
        $encodedData = json_encode($data);

        $headers = ['Authorization:key=' . $serverKey, 'Content-Type: application/json'];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        // curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        // Disabling SSL Certificate support temporarly
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedData);

        // Execute post

        $result = curl_exec($ch);
        if ($result === false) {
            die('Curl failed: ' . curl_error($ch));
        }
        // Close connection
        curl_close($ch);
        // FCM response
        // dd($result);
        // return $result;
        $noti_responce = json_decode($result);
        $response = [];
        $response['status'] = $noti_responce->success;
        $response['status_code'] = $noti_responce->success == 1 ? 200 : 400;
        $response['msg'] = $noti_responce->success == 1 ? 'Notification Send Successfully' : 'Notification Failed';
        $response['noti_msg'] = $noti_responce;
    } else {
        $response = [];
        $response['status'] = 0;
        $response['status_code'] = 400;
        $response['msg'] = 'No Token';
        $response['noti_msg'] = '';
    }

    return $response;
}

// -------------------- QUOTATION GLOBLE CREATED START --------------------
function getCheckAppVersion($appsource, $appversion)
{
    $alreadyName = wlmst_appversion::query();

    $alreadyName->where('source', $appsource);
    $alreadyName->where('version', $appversion);
    $alreadyName->where('isactive', 1);
    $alreadyName = $alreadyName->first();

    if ($alreadyName) {
        return true;
    } else {
        return false;
    }
}

function quoterrorRes($status = 0, $statusCode = 400, $msg = 'Error')
{
    $return = [];
    $return['status'] = $status; // 1=Success; 0=error; 2=appupdate
    $return['status_code'] = $statusCode;
    $return['msg'] = $msg;
    return $return;
}
function quotsuccessRes($status = 1, $statusCode = 200, $msg = 'Success')
{
    $return = [];
    $return['status'] = $status; // 1=Success; 0=error; 2=appupdate
    $return['status_code'] = $statusCode;
    $return['msg'] = $msg;
    return $return;
}

function saveBoardSaveLog($params)
{
    $BoardSaveLog = new wlmst_user_created_board_log();
    // $BoardSaveLog->user_id = Auth::user()->id;
    $BoardSaveLog->user_id = '1';
    $BoardSaveLog->quot_id = $params['quot_id'];
    $BoardSaveLog->quotgroup_id = $params['quotgroup_id'];
    $BoardSaveLog->room_no = $params['room_no'];
    $BoardSaveLog->board_no = $params['board_no'];
    $BoardSaveLog->description = $params['description'];
    $BoardSaveLog->source = $params['source'];
    $BoardSaveLog->entryby = '1';
    // $BoardSaveLog->entryby = Auth::user()->id;
    $BoardSaveLog->entryip = $params['entryip'];
    $BoardSaveLog->save();
}

function getQuotationMasterStatusLable($mainMasterStatus)
{
    $mainMasterStatus = (int) $mainMasterStatus;

    if ($mainMasterStatus == 0) {
        $mainMasterStatus = '<span class="badge badge-pill badge-soft-running font-size-11"> Running</span>';
    } elseif ($mainMasterStatus == 1) {
        $mainMasterStatus = '<span class="badge badge-pill badge-soft-success font-size-11"> New Request</span>';
    } elseif ($mainMasterStatus == 2) {
        $mainMasterStatus = '<span class="badge badge-pill badge-soft-change-request font-size-11"> Change Request</span>';
    } elseif ($mainMasterStatus == 3) {
        $mainMasterStatus = '<span class="badge badge-pill badge-soft-confirm font-size-11"> Confirm Quotation</span>';
    } elseif ($mainMasterStatus == 4) {
        $mainMasterStatus = '<span class="badge badge-pill badge-soft-danger font-size-11"> Rejected Quotation</span>';
    } elseif ($mainMasterStatus == 5) {
        $mainMasterStatus = '<span class="badge badge-pill badge-soft-danger font-size-11"> Dis. Approval</span>';
    } elseif ($mainMasterStatus == 6) {
        $mainMasterStatus = '<span class="badge badge-pill badge-soft-danger font-size-11"> Freez</span>';
    }
    return $mainMasterStatus;
}
function getQuotationMasterStatusLableText($mainMasterStatus)
{
    $mainMasterStatus = (int) $mainMasterStatus;

    if ($mainMasterStatus == 0) {
        $mainMasterStatus = 'Running';
    } elseif ($mainMasterStatus == 1) {
        $mainMasterStatus = 'New Request';
    } elseif ($mainMasterStatus == 2) {
        $mainMasterStatus = 'Change Request';
    } elseif ($mainMasterStatus == 3) {
        $mainMasterStatus = 'Confirm';
    } elseif ($mainMasterStatus == 4) {
        $mainMasterStatus = 'Rejected';
    } elseif ($mainMasterStatus == 5) {
        $mainMasterStatus = 'Dis. Approval';
    } elseif ($mainMasterStatus == 6) {
        $mainMasterStatus = 'Freez';
    }
    return $mainMasterStatus;
}

function getQuotationStatus()
{
    $QuotStatusTypes = [];
    $QuotStatusTypes[0]['id'] = 0;
    $QuotStatusTypes[0]['name'] = 'Running';
    $QuotStatusTypes[0]['short_name'] = 'RUNNING';
    $QuotStatusTypes[0]['sequence'] = 5;

    $QuotStatusTypes[1]['id'] = 1;
    $QuotStatusTypes[1]['name'] = 'New Request';
    $QuotStatusTypes[1]['short_name'] = 'NEW REQUEST';
    $QuotStatusTypes[1]['sequence'] = 1;

    $QuotStatusTypes[2]['id'] = 2;
    $QuotStatusTypes[2]['name'] = 'Change Request';
    $QuotStatusTypes[2]['short_name'] = 'CHANGE REQUEST';
    $QuotStatusTypes[2]['sequence'] = 2;

    $QuotStatusTypes[3]['id'] = 3;
    $QuotStatusTypes[3]['name'] = 'Confirm';
    $QuotStatusTypes[3]['short_name'] = 'CONFIRM';
    $QuotStatusTypes[3]['sequence'] = 3;

    $QuotStatusTypes[4]['id'] = 4;
    $QuotStatusTypes[4]['name'] = 'Rejected';
    $QuotStatusTypes[4]['short_name'] = 'REJECTED';
    $QuotStatusTypes[4]['sequence'] = 4;

    $QuotStatusTypes[5]['id'] = 5;
    $QuotStatusTypes[5]['name'] = 'Dis. Approval';
    $QuotStatusTypes[5]['short_name'] = 'DISCOUNT APPROVAL';
    $QuotStatusTypes[5]['sequence'] = 5;

    $QuotStatusTypes[6]['id'] = 6;
    $QuotStatusTypes[6]['name'] = 'Freez';
    $QuotStatusTypes[6]['short_name'] = 'FREEZ';
    $QuotStatusTypes[6]['sequence'] = 6;

    return $QuotStatusTypes;
}

function formatbBytes($bytes, $precision = 2)
{
    $units = ['B', 'KB', 'MB', 'GB'];

    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1000));
    $pow = min($pow, count($units) - 1);

    $bytes /= pow(1000, $pow);

    return round($bytes, $precision) . ' ' . $units[$pow];
}

// -------------------- QUOTATION GLOBLE API CREATED END --------------------
function getpercentage($base_amt, $new_amt)
{
    if ($base_amt == 0) {
        return 0.0;
    } else {
        return number_format((floatval($new_amt) * 100) / $base_amt, 2, '.', '');
    }
}
function getQuaterFromMonth($monthNumber)
{
    // START FROM JAN = Q1
    // return floor(($monthNumber - 1) / 3) + 1;

    // START FROM APRIL = Q1
    $quarter = floor(($monthNumber - 1) / 3);
    return $quarter == 0 ? 4 : $quarter;
}
function getDateFromFinancialYear($financialyear)
{
    $start_year = explode('-', $financialyear)[0];
    $end_year = explode('-', $financialyear)[1];

    $start_date = $start_year . '-04-01' . ' 00:00:00';
    $end_date = $end_year . '-03-31' . ' 23:59:59';

    $start = date('Y-m-d 00:00:00', strtotime($start_date));
    $end = date('Y-m-d 23:59:59', strtotime($end_date));

    $return = [];
    $return['start'] = $start;
    $return['end'] = $end;
    return $return;
}

function getDatesFromQuarter($quarter, $financialyear)
{
    $start_year = explode('-', $financialyear)[0];
    $end_year = explode('-', $financialyear)[1];
    $start_year = $quarter == 4 ? $end_year : $start_year;
    $end_year = $quarter == 4 ? $end_year : $start_year;

    $start_date = $quarter == 4 ? '01' : 3 * $quarter + 1 . '-01 00:00:00';
    $start_date = $start_year . '-' . $start_date;
    $end_date = $quarter == 4 ? '03' : 3 * $quarter + 3 . '-' . ($quarter == 3 || $quarter == 4 ? 31 : 30) . ' 23:59:59';
    $end_date = $end_year . '-' . $end_date;

    $start = date('Y-m-d 00:00:00', strtotime($start_date));
    $end = date('Y-m-d 23:59:59', strtotime($end_date));

    $return = [];
    $return['start'] = $start;
    $return['end'] = $end;
    return $return;
}

function getDatesFromMonth($month, $financialyear)
{
    $quarter = getQuaterFromMonth($month);
    $start_year = explode('-', $financialyear)[0];
    $end_year = explode('-', $financialyear)[1];
    $start_year = $quarter == 4 ? $end_year : $start_year;
    $end_year = $quarter == 4 ? $end_year : $start_year;

    $expected_date = $start_year . '-' . $month . '-02';

    $start = date('Y-m-01 00:00:00', strtotime($expected_date));

    $end = date('Y-m-t 23:59:59', strtotime($expected_date));

    $return = [];
    $return['start'] = $start;
    $return['end'] = $end;
    return $return;
}

function numCommaFormat($number, $decimals = 0)
{
    // $number = 555;
    // $decimals=0;
    // $number = 555.000;
    // $number = 555.123456;

    if (strpos($number, '.') != null) {
        $decimalNumbers = substr($number, strpos($number, '.'));
        $decimalNumbers = substr($decimalNumbers, 1, $decimals);
    } else {
        $decimalNumbers = 0;
        for ($i = 2; $i <= $decimals; $i++) {
            $decimalNumbers = $decimalNumbers . '0';
        }
    }
    // return $decimalNumbers;

    $number = (int) $number;
    // reverse
    $number = strrev($number);

    $n = '';
    $stringlength = strlen($number);

    for ($i = 0; $i < $stringlength; $i++) {
        if ($i % 2 == 0 && $i != $stringlength - 1 && $i > 1) {
            $n = $n . $number[$i] . ',';
        } else {
            $n = $n . $number[$i];
        }
    }

    $number = $n;
    // reverse
    $number = strrev($number);

    $decimals != 0 ? ($number = $number . '.' . $decimalNumbers) : $number;

    return $number;
}

//  ADVANCE FILTER FOR LEAD & DEALS
function getFilterColumnCRM()
{
    $filter_column = [];
    $filter_column[1]['id'] = 1;
    $filter_column[1]['name'] = '#id';
    $filter_column[1]['column_name'] = 'leads.id';
    $filter_column[1]['code'] = 'leads_id';
    $filter_column[1]['type'] = 'bigint';
    $filter_column[1]['value_type'] = 'text';

    $filter_column[2]['id'] = 2;
    $filter_column[2]['name'] = 'Client Name';
    $filter_column[2]['column_name'] = 'leads.first_name';
    $filter_column[2]['code'] = 'leads_first_name';
    $filter_column[2]['type'] = 'varchar';
    $filter_column[2]['value_type'] = 'text';

    $filter_column[3]['id'] = 3;
    $filter_column[3]['name'] = 'Tag';
    $filter_column[3]['column_name'] = 'leads.tag';
    $filter_column[3]['code'] = 'leads_tag';
    $filter_column[3]['type'] = 'varchar';
    $filter_column[3]['value_type'] = 'select';

    // $filter_column[3]['id'] = 3;
    // $filter_column[3]['name'] = "Email";
    // $filter_column[3]['column_name'] = "leads.email";
    // $filter_column[4]['code'] = "leads.email";
    // $filter_column[3]['type'] = "varchar";
    // $filter_column[3]['value_type'] = "text";

    $filter_column[4]['id'] = 4;
    $filter_column[4]['name'] = 'Phone Number';
    $filter_column[4]['column_name'] = 'leads.phone_number';
    $filter_column[4]['code'] = 'leads_phone_number';
    $filter_column[4]['type'] = 'varchar';
    $filter_column[4]['value_type'] = 'text';

    $filter_column[5]['id'] = 5;
    $filter_column[5]['name'] = 'Status';
    $filter_column[5]['column_name'] = 'leads.status';
    $filter_column[5]['code'] = 'leads_status';
    $filter_column[5]['type'] = 'tinyint';
    $filter_column[5]['value_type'] = 'select';

    // SUB STATUS

    $filter_column[6]['id'] = 6;
    $filter_column[6]['name'] = 'House No.';
    $filter_column[6]['column_name'] = 'leads.house_no';
    $filter_column[6]['code'] = 'leads_house_no';
    $filter_column[6]['type'] = 'varchar';
    $filter_column[6]['value_type'] = 'text';

    $filter_column[7]['id'] = 7;
    $filter_column[7]['name'] = 'Building/Soc.';
    $filter_column[7]['column_name'] = 'leads.addressline1';
    $filter_column[7]['code'] = 'leads_addressline1';
    $filter_column[7]['type'] = 'varchar';
    $filter_column[7]['value_type'] = 'text';

    // $filter_column[9]['id'] = 9;
    // $filter_column[9]['name'] = "Landmark";
    // $filter_column[9]['column_name'] = "leads.addressline2";
    // $filter_column[9]['code'] = "leads_addressline2";
    // $filter_column[9]['type'] = "varchar";
    // $filter_column[9]['value_type'] = "text";

    $filter_column[10]['id'] = 10;
    $filter_column[10]['name'] = 'Area';
    $filter_column[10]['column_name'] = 'leads.area';
    $filter_column[10]['code'] = 'leads_area';
    $filter_column[10]['type'] = 'varchar';
    $filter_column[10]['value_type'] = 'text';

    $filter_column[11]['id'] = 11;
    $filter_column[11]['name'] = 'Pincode';
    $filter_column[11]['column_name'] = 'leads.pincode';
    $filter_column[11]['code'] = 'leads_pincode';
    $filter_column[11]['type'] = 'varchar';
    $filter_column[11]['value_type'] = 'text';

    $filter_column[12]['id'] = 12;
    $filter_column[12]['name'] = 'City';
    $filter_column[12]['column_name'] = 'leads.city_id';
    $filter_column[12]['code'] = 'leads_city_id';
    $filter_column[12]['type'] = 'bigint';
    $filter_column[12]['value_type'] = 'select';

    // $filter_column[13]['id'] = 13;
    // $filter_column[13]['name'] = "Meeting House No.";
    // $filter_column[13]['column_name'] = "leads.meeting_house_no";
    // $filter_column[13]['code'] = "leads_meeting_house_no";
    // $filter_column[13]['type'] = "varchar";
    // $filter_column[13]['value_type'] = "text";

    // $filter_column[14]['id'] = 14;
    // $filter_column[14]['name'] = "Meeting Building/Soc.";
    // $filter_column[14]['column_name'] = "leads.meeting_addressline1";
    // $filter_column[14]['code'] = "leads_meeting_addressline1";
    // $filter_column[14]['type'] = "varchar";
    // $filter_column[14]['value_type'] = "text";

    // $filter_column[15]['id'] = 15;
    // $filter_column[15]['name'] = "Meeting Landmark";
    // $filter_column[15]['column_name'] = "leads.meeting_addressline2";
    // $filter_column[15]['code'] = "leads_meeting_addressline2";
    // $filter_column[15]['type'] = "varchar";
    // $filter_column[15]['value_type'] = "text";

    // $filter_column[16]['id'] = 16;
    // $filter_column[16]['name'] = "Meeting Area";
    // $filter_column[16]['column_name'] = "leads.meeting_area";
    // $filter_column[16]['code'] = "leads_meeting_area";
    // $filter_column[16]['type'] = "varchar";
    // $filter_column[16]['value_type'] = "text";

    // $filter_column[17]['id'] = 17;
    // $filter_column[17]['name'] = "Meeting Pincode";
    // $filter_column[17]['column_name'] = "leads.meeting_pincode";
    // $filter_column[17]['code'] = "leads_meeting_pincode";
    // $filter_column[17]['type'] = "varchar";
    // $filter_column[17]['value_type'] = "text";

    $filter_column[18]['id'] = 18;
    $filter_column[18]['name'] = 'Closing Date';
    $filter_column[18]['column_name'] = 'leads.closing_date_time';
    $filter_column[18]['code'] = 'leads_closing_date_time';
    $filter_column[18]['type'] = 'date';
    $filter_column[18]['value_type'] = 'date';

    $filter_column[19]['id'] = 19;
    $filter_column[19]['name'] = 'Budget';
    $filter_column[19]['column_name'] = 'leads.budget';
    $filter_column[19]['code'] = 'leads_budget';
    $filter_column[19]['type'] = 'bigint';
    $filter_column[19]['value_type'] = 'text';

    $filter_column[20]['id'] = 20;
    $filter_column[20]['name'] = 'Lead Owner';
    $filter_column[20]['column_name'] = 'leads.assigned_to';
    $filter_column[20]['code'] = 'leads_assigned_to';
    $filter_column[20]['type'] = 'bigint';
    $filter_column[20]['value_type'] = 'select';

    $filter_column[21]['id'] = 21;
    $filter_column[21]['name'] = 'Created By';
    $filter_column[21]['column_name'] = 'leads.created_by';
    $filter_column[21]['code'] = 'leads_created_by';
    $filter_column[21]['type'] = 'bigint';
    $filter_column[21]['value_type'] = 'select';

    $filter_column[22]['id'] = 22;
    $filter_column[22]['name'] = 'Source';
    $filter_column[22]['column_name'] = 'lead_sources.source';
    $filter_column[22]['code'] = 'leads_source';
    $filter_column[22]['type'] = 'varchar';
    $filter_column[22]['value_type'] = 'select';

    $filter_column[23]['id'] = 23;
    $filter_column[23]['name'] = 'Site Stage';
    $filter_column[23]['column_name'] = 'leads.site_stage';
    $filter_column[23]['code'] = 'leads_site_stage';
    $filter_column[23]['type'] = 'bigint';
    $filter_column[23]['value_type'] = 'select';

    $filter_column[24]['id'] = 24;
    $filter_column[24]['name'] = 'Competitor';
    $filter_column[24]['column_name'] = 'leads.competitor';
    $filter_column[24]['code'] = 'leads_competitor';
    $filter_column[24]['type'] = 'varchar';
    $filter_column[24]['value_type'] = 'select';

    $filter_column[25]['id'] = 25;
    $filter_column[25]['name'] = 'Want To Cover';
    $filter_column[25]['column_name'] = 'leads.want_to_cover';
    $filter_column[25]['code'] = 'leads_want_to_cover';
    $filter_column[25]['type'] = 'bigint';
    $filter_column[25]['value_type'] = 'select';

    $filter_column[26]['id'] = 26;
    $filter_column[26]['name'] = 'Created Date';
    $filter_column[26]['column_name'] = 'leads.created_at';
    $filter_column[26]['code'] = 'leads_created_at';
    $filter_column[26]['type'] = 'date';
    $filter_column[26]['value_type'] = 'date';

    $filter_column[27]['id'] = 27;
    $filter_column[27]['name'] = 'Hod Status';
    $filter_column[27]['column_name'] = 'lead_files.hod_approved';
    $filter_column[27]['code'] = 'lead_files_hod_approved';
    $filter_column[27]['type'] = 'tinyint';
    $filter_column[27]['value_type'] = 'reward_select';

    $filter_column[28]['id'] = 28;
    $filter_column[28]['name'] = 'Service Status';
    $filter_column[28]['column_name'] = 'leads.service_verification';
    $filter_column[28]['code'] = 'leads_service_verification';
    $filter_column[28]['type'] = 'tinyint';
    $filter_column[28]['value_type'] = 'select';

    $filter_column[29]['id'] = 29;
    $filter_column[29]['name'] = 'Tale-Sales Status';
    $filter_column[29]['column_name'] = 'leads.telesales_verification';
    $filter_column[29]['code'] = 'leads_telesales_verification';
    $filter_column[29]['type'] = 'tinyint';
    $filter_column[29]['value_type'] = 'select';

    $filter_column[30]['id'] = 30;
    $filter_column[30]['name'] = 'CompanyAdmin Status';
    $filter_column[30]['column_name'] = 'leads.companyadmin_verification';
    $filter_column[30]['code'] = 'leads_companyadmin_verification';
    $filter_column[30]['type'] = 'tinyint';
    $filter_column[30]['value_type'] = 'select';

    $filter_column[31]['id'] = 31;
    $filter_column[31]['name'] = 'Bill Status';
    $filter_column[31]['column_name'] = 'lead_files.status';
    $filter_column[31]['code'] = 'lead_files_bills_status';
    $filter_column[31]['type'] = 'tinyint';
    $filter_column[31]['value_type'] = 'select';

    $filter_column[32]['id'] = 32;
    $filter_column[32]['name'] = 'Mis Data';
    $filter_column[32]['column_name'] = 'leads.architect';
    $filter_column[32]['code'] = 'lead_miss_data';
    $filter_column[32]['type'] = 'tinyint';
    $filter_column[32]['value_type'] = 'select';

    return $filter_column;
}
function getFilterCondtionCRM()
{
    $filter_condtion = [];
    $filter_condtion[1]['id'] = 1;
    $filter_condtion[1]['name'] = 'IS';
    $filter_condtion[1]['code'] = 'is';
    $filter_condtion[1]['condtion'] = '=';
    $filter_condtion[1]['value_type'] = 'single_select';

    $filter_condtion[2]['id'] = 2;
    $filter_condtion[2]['name'] = 'Is Not';
    $filter_condtion[2]['code'] = 'is_not';
    $filter_condtion[2]['condtion'] = '!=';
    $filter_condtion[2]['value_type'] = 'single_select';

    $filter_condtion[3]['id'] = 3;
    $filter_condtion[3]['name'] = 'Contains';
    $filter_condtion[3]['code'] = 'contains';
    $filter_condtion[3]['condtion'] = 'IN';
    $filter_condtion[3]['value_type'] = 'multi_select';

    $filter_condtion[4]['id'] = 4;
    $filter_condtion[4]['name'] = "Doesn't Contains";
    $filter_condtion[4]['code'] = 'not_contains';
    $filter_condtion[4]['condtion'] = 'NOTIN';
    $filter_condtion[4]['value_type'] = 'multi_select';

    $filter_condtion[5]['id'] = 5;
    $filter_condtion[5]['name'] = 'Between';
    $filter_condtion[5]['code'] = 'between';
    $filter_condtion[5]['condtion'] = 'BETWEEN';
    $filter_condtion[5]['value_type'] = 'between';

    return $filter_condtion;
}

function getLeadFilterMissFilterValue()
{
    $filter_clause = [];

    $filter_clause[1]['id'] = 1;
    $filter_clause[1]['name'] = 'Architect';
    $filter_clause[1]['column_name'] = 'leads.architect';
    $filter_clause[1]['code'] = 'lead_miss_architect_data';
    $filter_clause[1]['value'] = 0;

    $filter_clause[2]['id'] = 2;
    $filter_clause[2]['name'] = 'Electrician';
    $filter_clause[2]['column_name'] = 'leads.electrician';
    $filter_clause[2]['code'] = 'lead_miss_electrician_data';
    $filter_clause[2]['value'] = 0;

    return $filter_clause;
}

function getDateFilterValue()
{
    $filter_clause = [];
    // $filter_clause[1]['id'] = 1;
    // $filter_clause[1]['name'] = "Today";
    // $filter_clause[1]['code'] = "today";
    // $filter_clause[1]['value'] = date('Y-m-d').','.date('Y-m-d');

    // $filter_clause[2]['id'] = 2;
    // $filter_clause[2]['name'] = "This Week";
    // $filter_clause[2]['code'] = "this_week";
    // $filter_clause[2]['value'] = date("Y-m-d",strtotime('next Monday -1 week')).','.date("Y-m-d",strtotime(date("Y-m-d",date('w', strtotime('next Monday -1 week'))==date('w') ? strtotime(date("Y-m-d",strtotime('next Monday -1 week'))." +7 days") : strtotime('next Monday -1 week'))." +6 days"));

    // $filter_clause[3]['id'] = 3;
    // $filter_clause[3]['name'] = "Next Week";
    // $filter_clause[3]['code'] = "next_week";
    // $filter_clause[3]['value'] = date("Y-m-d",strtotime('next Monday 0 week')).','.date("Y-m-d",strtotime(date("Y-m-d",date('w', strtotime('next Monday 0 week'))==date('w') ? strtotime(date("Y-m-d",strtotime('next Monday 0 week'))." +7 days") : strtotime('next Monday 0 week'))." +6 days"));

    // $filter_clause[4]['id'] = 4;
    // $filter_clause[4]['name'] = "Previous Week";
    // $filter_clause[4]['code'] = "previous_week";
    // $filter_clause[4]['value'] = date("Y-m-d",strtotime('next Monday -2 week')).','.date("Y-m-d",strtotime(date("Y-m-d",date('w', strtotime('next Monday -2 week'))==date('w') ? strtotime(date("Y-m-d",strtotime('next Monday -2 week'))." +7 days") : strtotime('next Monday -2 week'))." +6 days"));
    $filter_clause[1]['id'] = 1;
    $filter_clause[1]['name'] = 'All Closing';
    $filter_clause[1]['code'] = 'all_closing';
    $filter_clause[1]['value'] = date('Y-m-d') . ',' . date('Y-m-d');

    $filter_clause[2]['id'] = 2;
    $filter_clause[2]['name'] = 'In This Week';
    $filter_clause[2]['code'] = 'in_this_week';
    $filter_clause[2]['value'] = date('Y-m-d', strtotime('next Monday -1 week')) . ',' . date('Y-m-d', strtotime(date('Y-m-d', date('w', strtotime('next Monday -1 week')) == date('w') ? strtotime(date('Y-m-d', strtotime('next Monday -1 week')) . ' +7 days') : strtotime('next Monday -1 week')) . ' +6 days'));

    $filter_clause[3]['id'] = 3;
    $filter_clause[3]['name'] = 'In This Month';
    $filter_clause[3]['code'] = 'in_this_month';
    $filter_clause[3]['value'] = date('Y-m-d', strtotime('next Monday 0 week')) . ',' . date('Y-m-d', strtotime(date('Y-m-d', date('w', strtotime('next Monday 0 week')) == date('w') ? strtotime(date('Y-m-d', strtotime('next Monday 0 week')) . ' +7 days') : strtotime('next Monday 0 week')) . ' +6 days'));

    $filter_clause[4]['id'] = 4;
    $filter_clause[4]['name'] = 'In Next Month';
    $filter_clause[4]['code'] = 'in_next_month';
    $filter_clause[4]['value'] = date('Y-m-d', strtotime('next Monday -2 week')) . ',' . date('Y-m-d', strtotime(date('Y-m-d', date('w', strtotime('next Monday -2 week')) == date('w') ? strtotime(date('Y-m-d', strtotime('next Monday -2 week')) . ' +7 days') : strtotime('next Monday -2 week')) . ' +6 days'));

    $filter_clause[5]['id'] = 5;
    $filter_clause[5]['name'] = 'In Next Two Month';
    $filter_clause[5]['code'] = 'in_next_two_month';
    $filter_clause[5]['value'] = date('Y-m-d', strtotime('next Monday -2 week')) . ',' . date('Y-m-d', strtotime(date('Y-m-d', date('w', strtotime('next Monday -2 week')) == date('w') ? strtotime(date('Y-m-d', strtotime('next Monday -2 week')) . ' +7 days') : strtotime('next Monday -2 week')) . ' +6 days'));

    $filter_clause[6]['id'] = 6;
    $filter_clause[6]['name'] = 'In Next Three Month';
    $filter_clause[6]['code'] = 'in_next_three_month';
    $filter_clause[6]['value'] = date('Y-m-d', strtotime('next Monday -2 week')) . ',' . date('Y-m-d', strtotime(date('Y-m-d', date('w', strtotime('next Monday -2 week')) == date('w') ? strtotime(date('Y-m-d', strtotime('next Monday -2 week')) . ' +7 days') : strtotime('next Monday -2 week')) . ' +6 days'));

    // $filter_clause[5]['id'] = 5;
    // $filter_clause[5]['name'] = "This Month";
    // $filter_clause[5]['value'] = date("Y-m-d",strtotime('next Monday -2 week')).','.date("Y-m-d",strtotime(date("Y-m-d",date('w', strtotime('next Monday -2 week'))==date('w') ? strtotime(date("Y-m-d",strtotime('next Monday -2 week'))." +7 days") : strtotime('next Monday -2 week'))." +6 days"));

    return $filter_clause;
}

function getFilterClauseCRM()
{
    $filter_clause = [];
    $filter_clause[1]['id'] = 1;
    $filter_clause[1]['name'] = 'And';
    $filter_clause[1]['clause'] = 'where';

    $filter_clause[2]['id'] = 2;
    $filter_clause[2]['name'] = 'Or';
    $filter_clause[2]['clause'] = 'orwhere';

    return $filter_clause;
}

function saveLeadAndDealStatus($lead_id, $lead_new_status, $source)
{
    $Lead_status = Lead::find($lead_id);
    if ($Lead_status) {
        $isstatus_change = 0;
        if ($Lead_status->status != $lead_new_status) {
            $oldStatus = $Lead_status->status;
            $newStatus = $lead_new_status;
            $isstatus_change = 1;
        }

        $leadStatus = getLeadStatus();
        if ($isstatus_change == 1) {
            $timeline = [];
            $timeline['lead_id'] = $Lead_status->id;
            $timeline['type'] = 'lead-status-change';
            $timeline['reffrance_id'] = $Lead_status->id;
            $timeline['description'] = 'Lead status changed from  ' . $leadStatus[$oldStatus]['name'] . ' to ' . $leadStatus[$newStatus]['name'];
            $timeline['source'] = $source;
            saveLeadTimeline($timeline);
        }

        $Lead_status->status = $lead_new_status;
        $Lead_status->save();

        if ($Lead_status->status == 2) {
            $noOfCall = LeadCall::where('lead_id', $Lead_status->id)->count();
            if ($noOfCall > 4) {
                $Lead_status->status = 5;
                $Lead_status->save();
                $newStatus = $Lead_status->status;

                if ($oldStatus != $newStatus) {
                    $leadStatus = getLeadStatus();

                    $timeline = [];
                    $timeline['lead_id'] = $Lead_status->id;
                    $timeline['type'] = 'lead-status-auto-change';
                    $timeline['reffrance_id'] = $Lead_status->id;
                    $timeline['description'] = 'Lead status auto changed from  ' . $leadStatus[$oldStatus]['name'] . ' to ' . $leadStatus[$newStatus]['name'] . ' due to same status change ';
                    $timeline['source'] = $source;
                    saveLeadTimeline($timeline);
                }
            }
        }

        if ($Lead_status) {
            if ($Lead_status->status == 103) {
                $current_date = date('Y-m-d H:i:s');
                $Plus_three_day = date('Y-m-d H:i:s', strtotime($current_date . ' +3 days'));
                $lead = Lead::find($Lead_status->id);
                $city_id = CityList::find($lead->city_id);
                $User_id = User::select('id')
                    ->where('type', 9)
                    ->where('status', 1)
                    ->get();
                // $Telesales = TeleSales::query()->whereIn('user_id', $User_id)->whereRaw("FIND_IN_SET('$city_id->state_id', states)")->first();
                $Telesales = 3566;

                // START TELESALES TASK ASSIGN
                $LeadTask = new LeadTask();
                $LeadTask->lead_id = $Lead_status->id;
                $LeadTask->user_id = $Telesales;
                $LeadTask->assign_to = $Telesales;
                $LeadTask->task = 'Verified Architect & Electrician Detail';
                $LeadTask->due_date_time = $Plus_three_day;
                $LeadTask->reminder = getReminderTimeSlot($Plus_three_day)[1]['datetime'];
                $LeadTask->reminder_id = 1;
                $LeadTask->description = 'Auto Generated Task';
                $LeadTask->is_notification = 1;
                $LeadTask->is_autogenerate = 1;
                $LeadTask->save();
                // END TELESALES TASK ASSIGN

                // START SERVICE USER TASK ASSIGN
                $LeadTask = new LeadTask();
                $LeadTask->lead_id = $Lead_status->id;
                $LeadTask->user_id = 4871;
                $LeadTask->assign_to = 4871;
                $LeadTask->task = 'Verified Installation Status From ' . $lead->id . '-' . $lead->first_name . ' ' . $lead->last_name . '';
                $LeadTask->due_date_time = $Plus_three_day;
                $LeadTask->reminder = getReminderTimeSlot($Plus_three_day)[1]['datetime'];
                $LeadTask->reminder_id = 1;
                $LeadTask->description = 'Auto Generated Task';
                $LeadTask->is_notification = 1;
                $LeadTask->is_autogenerate = 1;
                $LeadTask->save();
                // END SERVICE USER TASK ASSIGN

                // START SERVICE USER TASK ASSIGN

                // END SERVICE USER TASK ASSIGN

                $Lead = Lead::find($Lead_status->id);
                $Lead->telesales_verification = 1;
                $Lead->service_verification = 1;
                $Lead->hod_verification = 1;
                $Lead->save();
            }

            if (in_array($Lead_status->status, [5, 104])) {

                if ($Lead_status->status == 104) {
                    $Quot_id = Wltrn_Quotation::select('id', 'quotgroup_id');
                    $Quot_id->where('inquiry_id', $Lead_status->id);
                    $Quot_id->where('isfinal', 1);
                    $Quot_id = $Quot_id->first();
                    if ($Quot_id) {
                        $arr = [
                            [
                                "area_page_visible" => "1",
                                "area_summary_visible" => "1",
                                "product_summary_visible" => "1",
                                "area_detailed_summary_visible" => "1",
                                "area_detailed_gst_visible" => "1",
                                "area_detailed_discount_visible" => "1",
                                "area_detailed_rate_total_visible" => "1",
                                "product_detailed_summary_visible" => "1",
                                "product_detailed_gst_visible" => "1",
                                "product_detailed_discount_visible" => "1",
                                "product_detailed_rate_total_visible" => "1",
                                "wlt_and_others_detailed_summary_visible" => "1",
                                "wlt_and_others_detailed_gst_visible" => "1",
                                "wlt_and_others_detailed_discount_visible" => "1",
                                "wlt_and_others_detailed_rate_total_visible" => "1",
                            ]
                        ];



                        $whatsapp_controller = new QuotationMasterController();
                        $perameater_request = new Request();
                        $perameater_request['quot_id'] = $Quot_id->id;
                        $perameater_request['quotgroup_id'] = $Quot_id->quotgroup_id;
                        $perameater_request['visible_array'] = json_encode($arr);
                        $perameater_request['is_helper'] = 1;

                        $resp = $whatsapp_controller->PostItemWiseDownloadPrint($perameater_request);
                    }
                } else {
                    $resp = "";
                }

                $configrationForNotify = configrationForNotify();

                $getUpperIds = getParentSalePersonsIds($Lead_status->assigned_to);
                $emails = User::select('email')->whereIn('id', $getUpperIds)->distinct()->pluck('email')->all();

                $params = array();
                $params['from_email'] = $configrationForNotify['from_email'];
                $params['from_name'] = $configrationForNotify['from_name'];
                $params['to_email'] = implode(', ', $emails);
                $params['to_name'] = $configrationForNotify['to_name'];
                $params['bcc_email'] = "Poonam@whitelion.in";

                if (Config::get('app.env') == 'local') {
                    $params['to_email'] = $configrationForNotify['test_email'];
                    $params['bcc_email'] = implode(', ', $configrationForNotify['test_email_bcc']);
                }
                $params['cc_email'] = "";
                if ($Lead_status->is_deal == 1) {
                    $params['subject'] = "Your Deal Is Lost";
                } else {
                    $params['subject'] = "Your Lead Is Lost";
                }
                $params['transaction_id'] = $Lead_status->id;
                $params['transaction_name'] = 'Lead';
                $params['transaction_type'] = 'Email';
                $params['transaction_detail'] = 'emails.lost_lead';
                $params['attachment'] = $resp;
                $params['remark'] = "Lead Status Move In Lost";
                $params['source'] = $source;
                $params['entryip'] = "";
                saveNotificationScheduler($params);
            }
        }
        $response['id'] = $Lead_status->id;
    } else {
        $response = errorRes('Something went wrong');
    }
    return $response;
}

function saveLeadAndDealStatusInAction($lead_id, $lead_new_status, $ip = '', $entry_source = 'WEB')
{
    $leadStatus = getLeadStatus();

    $Lead_status = Lead::find($lead_id);

    $oldStatus = $Lead_status->status;
    $newStatus = $lead_new_status;
    $response = successRes();
    $response['timeline_id'] = 0;

    if ($oldStatus != $newStatus) {
        $Lead_status->status = $newStatus;
        $Lead_status->updateip = $ip;
        $Lead_status->update_source = $entry_source;
        $Lead_status->save();

        if ($Lead_status) {

            if ($Lead_status->status == 103) {
                $current_date = date('Y-m-d H:i:s');
                // $Plus_three_day = date('Y-m-d H:i:s', strtotime($current_date . " +3 days"));

                $lead = Lead::find($Lead_status->id);
                $city_id = CityList::find($lead->city_id);
                $User_id = User::select('id')
                    ->where('type', 9)
                    ->where('status', 1)
                    ->get();
                // $Telesales = TeleSales::query()->whereIn('user_id', $User_id)->whereRaw("FIND_IN_SET('$city_id->state_id', states)")->first();
                $Telesales = 3566;

                // START TELESALES TASK ASSIGN
                $LeadTask = new LeadTask();
                $LeadTask->lead_id = $Lead_status->id;
                $LeadTask->user_id = $Telesales;
                $LeadTask->assign_to = $Telesales;
                $LeadTask->task = 'Verified Architect & Electrician Detail';
                $LeadTask->due_date_time = $current_date;
                $LeadTask->reminder = getReminderTimeSlot($current_date)[1]['datetime'];
                $LeadTask->reminder_id = 1;
                $LeadTask->description = 'Auto Generated Task';
                $LeadTask->is_notification = 1;
                $LeadTask->is_autogenerate = 1;
                $LeadTask->save();
                // END TELESALES TASK ASSIGN

                // START SERVICE USER TASK ASSIGN
                $LeadTask = new LeadTask();
                $LeadTask->lead_id = $Lead_status->id;
                $LeadTask->user_id = 4871;
                $LeadTask->assign_to = 4871;
                $LeadTask->task = 'Verified Installation Status From ' . $lead->id . '-' . $lead->first_name . ' ' . $lead->last_name . '';
                $LeadTask->due_date_time = $current_date;
                $LeadTask->reminder = getReminderTimeSlot($current_date)[1]['datetime'];
                $LeadTask->reminder_id = 1;
                $LeadTask->description = 'Auto Generated Task';
                $LeadTask->is_notification = 1;
                $LeadTask->is_autogenerate = 1;
                $LeadTask->save();
                // END SERVICE USER TASK ASSIGN

                // START SERVICE USER TASK ASSIGN

                // END SERVICE USER TASK ASSIGN

                $Lead = Lead::find($Lead_status->id);
                $Lead->telesales_verification = 1;
                $Lead->service_verification = 1;
                $Lead->hod_verification = 1;
                $Lead->save();
            }

            if (in_array($Lead_status->status, [5, 104])) {
                $resp = "";
                if ($Lead_status->status == 104) {
                    try {
                        $Quot_id = Wltrn_Quotation::select('id', 'quotgroup_id');
                        $Quot_id->where('inquiry_id', $Lead_status->id);
                        $Quot_id->where('isfinal', 1);
                        $Quot_id = $Quot_id->first();
                        if ($Quot_id) {
                            $arr = [
                                [
                                    "area_page_visible" => "1",
                                    "area_summary_visible" => "1",
                                    "product_summary_visible" => "1",
                                    "area_detailed_summary_visible" => "1",
                                    "area_detailed_gst_visible" => "1",
                                    "area_detailed_discount_visible" => "1",
                                    "area_detailed_rate_total_visible" => "1",
                                    "product_detailed_summary_visible" => "1",
                                    "product_detailed_gst_visible" => "1",
                                    "product_detailed_discount_visible" => "1",
                                    "product_detailed_rate_total_visible" => "1",
                                    "wlt_and_others_detailed_summary_visible" => "1",
                                    "wlt_and_others_detailed_gst_visible" => "1",
                                    "wlt_and_others_detailed_discount_visible" => "1",
                                    "wlt_and_others_detailed_rate_total_visible" => "1",
                                ]
                            ];

                            $whatsapp_controller = new QuotationMasterController();
                            $perameater_request = new Request();
                            $perameater_request['quot_id'] = $Quot_id->id;
                            $perameater_request['quotgroup_id'] = $Quot_id->quotgroup_id;
                            $perameater_request['visible_array'] = json_encode($arr);
                            $perameater_request['is_helper'] = 1;

                            $resp = $whatsapp_controller->PostItemWiseDownloadPrint($perameater_request);
                        }
                    } catch (\Exception $e) {
                        $resp = "";
                    }
                } else {
                    $resp = "";
                }

                $getUpperIds = getParentSalePersonsIds($Lead_status->assigned_to);
                $emails = User::select('email')->whereIn('id', $getUpperIds)->distinct()->pluck('email')->all();

                $configrationForNotify = configrationForNotify();

                $params = array();
                $params['from_email'] = $configrationForNotify['from_email'];
                $params['from_name'] = $configrationForNotify['from_name'];
                $params['to_email'] = implode(', ', $emails);
                $params['to_name'] = $configrationForNotify['to_name'];
                $params['bcc_email'] = "Poonam@whitelion.in";

                if (Config::get('app.env') == 'local') {
                    $params['to_email'] = $configrationForNotify['test_email'];
                    $params['bcc_email'] = implode(', ', $configrationForNotify['test_email_bcc']);
                }
                $params['cc_email'] = "";
                if ($Lead_status->is_deal == 1) {
                    $params['subject'] = "Your Deal Is Lost";
                } else {
                    $params['subject'] = "Your Lead Is Lost";
                }
                $params['transaction_id'] = $Lead_status->id;
                $params['transaction_name'] = 'Lead';
                $params['transaction_type'] = 'Email';
                $params['transaction_detail'] = 'emails.lost_lead';
                $params['attachment'] = $resp;
                $params['remark'] = "Lead Status Move In Lost";
                $params['source'] = $entry_source;
                $params['entryip'] = $ip;
                saveNotificationScheduler($params);
            }

            $timeline = [];
            $timeline['lead_id'] = $Lead_status->id;
            $timeline['type'] = 'lead-status-change';
            $timeline['reffrance_id'] = $Lead_status->id;
            $timeline['description'] = 'Lead status changed from  ' . $leadStatus[$oldStatus]['name'] . ' to ' . $leadStatus[$newStatus]['name'];
            $timeline['source'] = $entry_source;
            $TimeLineData = saveLeadTimeline($timeline);
            if ($TimeLineData) {
                $response['timeline_id'] = $TimeLineData->id;
            }

            $LeadStatusUpdate = new LeadStatusUpdate();
            $LeadStatusUpdate->lead_id = $Lead_status->id;
            $LeadStatusUpdate->old_status = $oldStatus;
            $LeadStatusUpdate->new_status = $newStatus;
            $LeadStatusUpdate->remark = 'Status Change';

            $LeadStatusUpdate->entryby = Auth::user()->id;
            $LeadStatusUpdate->entryip = $ip;

            $LeadStatusUpdate->updateby = Auth::user()->id;
            $LeadStatusUpdate->updateip = $ip;
            $LeadStatusUpdate->save();

            $whatsapp_controller = new WhatsappApiContoller();
            $perameater_request = new Request();
            $mobileNO = $Lead_status->phone_number;

            $perameater_request['q_whatsapp_massage_mobileno'] = $mobileNO;
            $perameater_request['q_broadcast_name'] = $Lead_status->first_name . ' ' . $Lead_status->last_name;
            $perameater_request['q_whatsapp_massage_parameters'] = array();


            if ($newStatus == 4) {
                // STATUS MOVE TO DEMO MEETING DONE
                $perameater_request['q_whatsapp_massage_attechment'] = 'https://erp.whitelion.in/whatsapp/lead_status_demodone.pdf';
                $perameater_request['q_whatsapp_massage_template'] = 'lead_status_demodone';
                $whatsapp_message = $whatsapp_controller->sendTemplateMessage($perameater_request);
                $response['whatsapp'] = $whatsapp_message;
            } elseif ($newStatus == 102) {
                // STATUS MOVE TO TOKEN RECEIVE / ORDER CONFIRMED
                $perameater_request['q_whatsapp_massage_template'] = 'lead_status_token_received';
                $whatsapp_message = $whatsapp_controller->sendTemplateMessage($perameater_request);
                $response['whatsapp'] = $whatsapp_message;
            } elseif ($newStatus == 103) {
                // STATUS MOVE TO WON
                $perameater_request['q_whatsapp_massage_parameters'] = array();
                // $perameater_request['q_whatsapp_massage_parameters'] = [
                //     [
                //         'name' => 'name',
                //         'value' => $Lead_status->first_name . ' ' . $Lead_status->last_name,
                //     ],
                //     [
                //         'name' => 'image_url',
                //         'value' => 'https://erp.whitelion.in/watti/installation_step.jpeg',
                //     ],
                // ];
                $perameater_request['q_whatsapp_massage_template'] = 'lead_status_material_sent';
                $whatsapp_message = $whatsapp_controller->sendTemplateMessage($perameater_request);
                $response['whatsapp'] = $whatsapp_message;
            }
        }
    }

    return $response;
}

// FOR USER ACTION START
function getUserNoteList($user_id)
{
    $UserUpdateList = UserNotes::query();
    $UserUpdateList->select('user_notes.id', 'user_notes.note', 'user_notes.user_id', 'user_notes.note_type', 'user_notes.note_title', 'created.first_name', 'created.last_name', 'user_notes.created_at');
    $UserUpdateList->leftJoin('users as created', 'created.id', '=', 'user_notes.entryby');
    $UserUpdateList->where('user_notes.user_id', $user_id);
    $UserUpdateList->orderBy('user_notes.id', 'desc');
    $UserUpdateList->limit(5);
    $UserUpdateList = $UserUpdateList->get();
    $UserUpdateList = json_encode($UserUpdateList);
    $UserUpdateList = json_decode($UserUpdateList, true);

    foreach ($UserUpdateList as $key => $value) {
        $UserUpdateList[$key]['message'] = strip_tags($value['note']);

        $UserUpdateList[$key]['created_at'] = convertDateTime($value['created_at']);
        $UserUpdateList[$key]['date'] = convertDateAndTime($value['created_at'], 'date');
        $UserUpdateList[$key]['time'] = convertDateAndTime($value['created_at'], 'time');
    }
    $data = [];
    $data['updates'] = $UserUpdateList;
    $response['view'] = view('user_action/detail_tab/detail_notes_tab', compact('data'))->render();
    $response['data'] = $UserUpdateList;
    return $response;
}

function getUserContactList($user_id)
{
    $UserContact = UserContact::query();
    $UserContact->select('crm_setting_contact_tag.name as tag_name', 'user_contact.*');
    $UserContact->leftJoin('crm_setting_contact_tag', 'crm_setting_contact_tag.id', '=', 'user_contact.contact_tag_id');
    $UserContact->where('user_contact.user_id', $user_id);
    $UserContact->orderBy('user_contact.id', 'desc');
    $UserContact->limit(5);
    $UserContact = $UserContact->get();

    foreach ($UserContact as $key => $value) {
        $UserContact[$key]['message'] = strip_tags($value['note']);
        $UserContact[$key]['created_at'] = $value['created_at'];
        $UserContact[$key]['date'] = convertDateAndTime($value['created_at'], 'date');
        $UserContact[$key]['time'] = convertDateAndTime($value['created_at'], 'time');
    }
    $data = [];
    $data['contacts'] = $UserContact;
    $data['user']['id'] = $user_id;
    $response['view'] = view('user_action/detail_tab/detail_contact_tab', compact('data'))->render();
    $response['data'] = $UserContact;
    return $response;
}

function getUserFileList($user_id)
{
    $UserFile = UserFiles::query();
    $UserFile->select('crm_setting_file_tag.name as tag_name', 'user_files.*', 'users.first_name', 'users.last_name');
    $UserFile->leftJoin('crm_setting_file_tag', 'crm_setting_file_tag.id', '=', 'user_files.file_tag_id');
    $UserFile->leftJoin('users', 'users.id', '=', 'user_files.entryby');
    $UserFile->where('user_files.user_id', $user_id);
    $UserFile->limit(5);
    $UserFile->orderBy('user_files.id', 'desc');
    $UserFile = $UserFile->get();
    $UserFile = json_encode($UserFile);
    $UserFile = json_decode($UserFile, true);

    foreach ($UserFile as $key => $value) {
        $name = explode('/', $value['name']);

        $UserFile[$key]['name'] = end($name);
        $UserFile[$key]['download'] = getSpaceFilePath($value['name']);
        $UserFile[$key]['created_at'] = convertDateTime($value['created_at']);
    }
    $data = [];
    $data['user']['id'] = $user_id;
    $data['files'] = $UserFile;
    $response['view'] = view('user_action/detail_tab/detail_file_tab', compact('data'))->render();
    $response['data'] = $UserFile;
    return $response;
}

function getUserAllOpenList($user_id)
{
    // ACTION CALL START
    $UserCall = UserCallAction::query();
    $UserCall->select('user_call_action.*', 'users.first_name', 'users.last_name');
    $UserCall->where('user_call_action.user_id', $user_id);
    $UserCall->where('is_closed', 0);
    $UserCall->leftJoin('users', 'users.id', '=', 'user_call_action.user_id');
    $UserCall->orderBy('user_call_action.id', 'desc');
    $UserCall = $UserCall->get();
    $UserCall = json_encode($UserCall);
    $UserCall = json_decode($UserCall, true);
    foreach ($UserCall as $key => $value) {
        $UserCall[$key]['date'] = convertDateAndTime($value['call_schedule'], 'date');
        $UserCall[$key]['time'] = convertDateAndTime($value['call_schedule'], 'time');
        $ContactName = UserContact::select('user_contact.id', 'user_contact.first_name', 'user_contact.last_name', DB::raw("CONCAT(user_contact.first_name,' ',user_contact.last_name) AS text"));
        $ContactName->where('user_contact.id', $value['contact_person']);
        $ContactName = $ContactName->first();
        if ($ContactName) {
            $UserCall[$key]['contact_name'] = $ContactName->text;
        } else {
            $UserCall[$key]['contact_name'] = '';
        }
    }
    // ACTION CALL END

    //  ACTION MEETING START
    $UserMeeting = UserMeetingAction::query();
    $UserMeeting->select('user_meeting_action.*', 'users.first_name', 'users.last_name');
    $UserMeeting->where('user_meeting_action.user_id', $user_id);
    $UserMeeting->where('is_closed', 0);
    $UserMeeting->leftJoin('users', 'users.id', '=', 'user_meeting_action.user_id');
    $UserMeeting->orderBy('user_meeting_action.id', 'desc');
    $UserMeeting = $UserMeeting->get();
    $UserMeeting = json_encode($UserMeeting);
    $UserMeeting = json_decode($UserMeeting, true);
    foreach ($UserMeeting as $key => $value) {
        $UserMeeting[$key]['date'] = convertDateAndTime($value['meeting_date_time'], 'date');
        $UserMeeting[$key]['time'] = convertDateAndTime($value['meeting_date_time'], 'time');

        $UserMeetingTitle = CRMSettingMeetingTitle::select('name')
            ->where('id', $value['title_id'])
            ->first();

        if ($UserMeetingTitle) {
            $UserMeeting[$key]['title_name'] = $UserMeetingTitle->name;
        } else {
            $UserMeeting[$key]['title_name'] = $UserMeetingTitle->name;
        }

        $UserMeetingParticipant = UserMeetingParticipant::where('meeting_id', $value['id'])
            ->orderby('id', 'asc')
            ->get();
        $UserMeetingParticipant = json_decode(json_encode($UserMeetingParticipant), true);

        $UsersId = [];
        $ContactIds = [];
        foreach ($UserMeetingParticipant as $sales_key => $value) {
            if ($value['type'] == 'users') {
                $UsersId[] = $value['participant_id'];
            } elseif ($value['type'] == 'lead_contacts') {
                $ContactIds[] = $value['participant_id'];
            }
        }

        $UserResponse = '';
        if (count($ContactIds) > 0) {
            $LeadContact = UserContact::select('user_contact.id', 'user_contact.first_name', 'user_contact.last_name', DB::raw("CONCAT(user_contact.first_name,' ',user_contact.last_name) AS full_name"));
            $LeadContact->whereIn('user_contact.id', $ContactIds);
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
            $UserMeeting[$key]['meeting_participant'] = $UserResponse;
        } else {
            $UserMeeting[$key]['meeting_participant'] = '';
        }
    }
    //  ACTION MEETING END

    // ACTION TASK START
    $UserTask = UserTaskAction::query();
    $UserTask->select('user_task_action.*', 'users.first_name', 'users.last_name');
    $UserTask->where('user_task_action.user_id', $user_id);
    $UserTask->where('is_closed', 0);
    $UserTask->leftJoin('users', 'users.id', '=', 'user_task_action.user_id');
    $UserTask->orderBy('user_task_action.id', 'desc');
    $UserTask = $UserTask->get();
    $UserTask = json_encode($UserTask);
    $UserTask = json_decode($UserTask, true);
    foreach ($UserTask as $key => $value) {
        $UserTask[$key]['date'] = convertDateAndTime($value['due_date_time'], 'date');
        $UserTask[$key]['time'] = convertDateAndTime($value['due_date_time'], 'time');

        $Taskowner = User::select('users.id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"));
        $Taskowner->where('users.status', 1);
        $Taskowner->where('users.id', $value['assign_to']);
        $Taskowner = $Taskowner->first();

        if ($Taskowner) {
            $UserTask[$key]['task_owner'] = $Taskowner->text;
        } else {
            $UserTask[$key]['task_owner'] = ' ';
        }
    }
    // ACTION TASK END

    $data = [];
    $data['calls'] = $UserCall;
    $data['meetings'] = $UserMeeting;
    $data['task'] = $UserTask;
    $data['max_open_actions'] = max(count($UserCall), count($UserMeeting), count($UserTask));
    $data['user']['id'] = $user_id;
    $response['view'] = view('user_action/detail_tab/detail_open_action_tab', compact('data'))->render();
    $response['max_open_actions'] = max(count($UserCall), count($UserMeeting), count($UserTask));
    $response['call_data'] = $UserCall;
    $response['meeting_data'] = $UserMeeting;
    $response['task_data'] = $UserTask;
    return $response;
}

function getUserAllCloseList($user_id)
{
    // ACTION CLOSE CALL START
    $UserCallClosed = UserCallAction::query();
    $UserCallClosed->select('user_call_action.*', 'users.first_name', 'users.last_name');
    $UserCallClosed->where('user_call_action.user_id', $user_id);
    $UserCallClosed->where('is_closed', 1);
    $UserCallClosed->leftJoin('users', 'users.id', '=', 'user_call_action.user_id');
    $UserCallClosed->orderBy('user_call_action.closed_date_time', 'desc');
    $UserCallClosed = $UserCallClosed->get();
    $UserCallClosed = json_encode($UserCallClosed);
    $UserCallClosed = json_decode($UserCallClosed, true);
    foreach ($UserCallClosed as $key => $value) {
        $UserCallClosed[$key]['date'] = convertDateAndTime($value['closed_date_time'], 'date');
        $UserCallClosed[$key]['time'] = convertDateAndTime($value['closed_date_time'], 'time');
        $ContactName = UserContact::select('user_contact.id', 'user_contact.first_name', 'user_contact.last_name', DB::raw("CONCAT(user_contact.first_name,' ',user_contact.last_name) AS text"));
        $ContactName->where('user_contact.id', $value['contact_person']);
        $ContactName = $ContactName->first();
        if ($ContactName) {
            $UserCallClosed[$key]['contact_name'] = $ContactName->text;
        } else {
            $UserCallClosed[$key]['contact_name'] = '';
        }
    }
    // ACTION CLOSE CALL END

    // ACTION CLOSE MEETING START
    $UserMeetingClosed = UserMeetingAction::query();
    $UserMeetingClosed->select('user_meeting_action.*', 'users.first_name', 'users.last_name');
    $UserMeetingClosed->where('user_meeting_action.user_id', $user_id);
    $UserMeetingClosed->where('is_closed', 1);
    $UserMeetingClosed->leftJoin('users', 'users.id', '=', 'user_meeting_action.user_id');
    $UserMeetingClosed->orderBy('user_meeting_action.closed_date_time', 'desc');
    $UserMeetingClosed = $UserMeetingClosed->get();
    $UserMeetingClosed = json_encode($UserMeetingClosed);
    $UserMeetingClosed = json_decode($UserMeetingClosed, true);
    foreach ($UserMeetingClosed as $key => $value) {
        $UserMeetingClosed[$key]['date'] = convertDateAndTime($value['closed_date_time'], 'date');
        $UserMeetingClosed[$key]['time'] = convertDateAndTime($value['closed_date_time'], 'time');

        $UserMeetingTitle = CRMSettingMeetingTitle::select('name')
            ->where('id', $value['title_id'])
            ->first();
        if ($UserMeetingTitle) {
            $UserMeetingClosed[$key]['title_name'] = $UserMeetingTitle->name;
        } else {
            $UserMeetingClosed[$key]['title_name'] = ' ';
        }

        $UserMeetingParticipant = UserMeetingParticipant::where('meeting_id', $value['id'])
            ->orderby('id', 'asc')
            ->get();
        $UserMeetingParticipant = json_decode(json_encode($UserMeetingParticipant), true);

        $UsersId = [];
        $ContactIds = [];
        foreach ($UserMeetingParticipant as $sales_key => $value) {
            if ($value['type'] == 'users') {
                $UsersId[] = $value['participant_id'];
            } elseif ($value['type'] == 'lead_contacts') {
                $ContactIds[] = $value['participant_id'];
            }
        }

        $UserResponse = '';
        if (count($ContactIds) > 0) {
            $LeadContact = UserContact::select('user_contact.id', DB::raw("CONCAT(user_contact.first_name,' ',user_contact.last_name) AS full_name"));
            $LeadContact->whereIn('user_contact.id', $ContactIds);
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
            $UserMeetingClosed[$key]['meeting_participant'] = $UserResponse;
        } else {
            $UserMeetingClosed[$key]['meeting_participant'] = '';
        }
    }
    // ACTION CLOSE MEETING END

    // ACTION CLOSE TASK START
    $UserTaskClosed = UserTaskAction::query();
    $UserTaskClosed->select('user_task_action.*', 'users.first_name', 'users.last_name');
    $UserTaskClosed->where('user_task_action.user_id', $user_id);
    $UserTaskClosed->where('is_closed', 1);
    $UserTaskClosed->leftJoin('users', 'users.id', '=', 'user_task_action.user_id');
    $UserTaskClosed->orderBy('user_task_action.closed_date_time', 'desc');
    $UserTaskClosed = $UserTaskClosed->get();
    $UserTaskClosed = json_encode($UserTaskClosed);
    $UserTaskClosed = json_decode($UserTaskClosed, true);
    foreach ($UserTaskClosed as $key => $value) {
        $UserTaskClosed[$key]['date'] = convertDateAndTime($value['closed_date_time'], 'date');
        $UserTaskClosed[$key]['time'] = convertDateAndTime($value['closed_date_time'], 'time');

        $Taskowner = User::select('users.id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"));
        $Taskowner->where('users.status', 1);
        $Taskowner->where('users.id', $value['assign_to']);
        $Taskowner = $Taskowner->first();

        if ($Taskowner) {
            $UserTaskClosed[$key]['task_owner'] = $Taskowner->text;
        } else {
            $UserTaskClosed[$key]['task_owner'] = ' ';
        }
    }
    // ACTION CLOSE TASK END

    $data = [];
    $data['calls_closed'] = $UserCallClosed;
    $data['meetings_closed'] = $UserMeetingClosed;
    $data['task_closed'] = $UserTaskClosed;
    $data['max_close_actions'] = max(count($UserCallClosed), count($UserMeetingClosed), count($UserTaskClosed));
    $data['user']['id'] = $user_id;
    $response['view'] = view('user_action/detail_tab/detail_close_action_tab', compact('data'))->render();
    $response['max_close_actions'] = max(count($UserCallClosed), count($UserMeetingClosed), count($UserTaskClosed));
    $response['close_call_data'] = $UserCallClosed;
    $response['close_meeting_data'] = $UserMeetingClosed;
    $response['close_task_data'] = $UserTaskClosed;
    return $response;
}

function getUserTimelineList($user_id)
{
    $UserLog = UserLog::select('user_log.*', 'users.first_name', 'users.last_name')
        ->leftJoin('users', 'users.id', '=', 'user_log.user_id')
        ->where('user_log.user_id', $user_id)
        ->orderBy('id', 'desc')
        ->get();

    $UserLog = json_encode($UserLog);
    $UserLog = json_decode($UserLog, true);

    $repeated_date = '';
    foreach ($UserLog as $key => $value) {
        $date = convertDateAndTime($value['created_at'], 'date');
        if ($repeated_date == $date) {
            $UserLog[$key]['date'] = 0;
        } else {
            $repeated_date = $date;
            $UserLog[$key]['date'] = convertDateAndTime($value['created_at'], 'date');
        }
        $UserLog[$key]['created_date'] = convertDateAndTime($value['created_at'], 'date');
        $UserLog[$key]['time'] = convertDateAndTime($value['created_at'], 'time');
        $UserLog[$key]['created_at'] = convertDateTime($value['created_at']);
        $UserLog[$key]['updated_at'] = convertDateTime($value['updated_at']);
    }

    $data['timeline'] = $UserLog;

    $data = [];
    $data['user']['id'] = $user_id;
    $data['timeline'] = $UserLog;
    $response['view'] = view('user_action/detail_tab/detail_user_timeline_tab', compact('data'))->render();
    $response['data'] = $UserLog;
    return $response;
}
function getUserPointLogList($user_id)
{
    $selectColumnsPointlog = ['crm_log.description', 'crm_log.created_at', 'crm_log.updated_at', 'crm_log.files', 'users.first_name', 'users.last_name'];

    $QryPointLog = CRMLog::query();
    $QryPointLog->select($selectColumnsPointlog);
    $QryPointLog->leftJoin('users', 'users.id', '=', 'crm_log.user_id');
    $QryPointLog->where('crm_log.for_user_id', $user_id);
    $QryPointLog->whereIn('crm_log.name', ['point-gain', 'point-redeem', 'point-back', 'point-lose']);
    $QryPointLog = $QryPointLog->get();

    $QryPointLog = json_encode($QryPointLog);
    $QryPointLog = json_decode($QryPointLog, true);

    $repeated_date = '';
    foreach ($QryPointLog as $key => $value) {
        $date = convertDateAndTime($value['created_at'], 'date');
        if ($repeated_date == $date) {
            $QryPointLog[$key]['date'] = 0;
        } else {
            $repeated_date = $date;
            $QryPointLog[$key]['date'] = convertDateAndTime($value['created_at'], 'date');
        }
        $QryPointLog[$key]['created_date'] = convertDateAndTime($value['created_at'], 'date');
        $QryPointLog[$key]['time'] = convertDateAndTime($value['created_at'], 'time');
        $QryPointLog[$key]['created_at'] = convertDateTime($value['created_at']);
        $QryPointLog[$key]['updated_at'] = convertDateTime($value['updated_at']);
        $QryPointLog[$key]['files'] = isset($value['files']) ? '<a target="_blank" href="' . getSpaceFilePath($value['files']) . '"><i class="bx bxs-file-pdf"></i></a>' : '';
    }

    $data['timeline'] = $QryPointLog;

    $data = [];
    $data['user']['id'] = $user_id;
    $data['timeline'] = $QryPointLog;
    $response['view'] = view('user_action/detail_tab/detail_user_point_log_tab', compact('data'))->render();
    $response['data'] = $QryPointLog;
    return $response;
}

function getUserLogList($user_id)
{
    $selectColumnsPointlog = ['user_log.description', 'user_log.created_at', 'user_log.updated_at', 'user_log.source', 'users.first_name', 'users.last_name'];

    $QryUserLog = UserLog::query();
    $QryUserLog->select($selectColumnsPointlog);
    $QryUserLog->leftJoin('users', 'users.id', '=', 'user_log.entryby');
    // $QryUserLog->leftJoin('users', 'users.id', '=', 'user_log.user_id');
    $QryUserLog->where('user_log.reference_id', $user_id);
    $QryUserLog->orderBy('user_log.id', 'DESC');
    $QryUserLog = $QryUserLog->get();

    $QryUserLog = json_encode($QryUserLog);
    $QryUserLog = json_decode($QryUserLog, true);

    $repeated_date = '';
    foreach ($QryUserLog as $key => $value) {
        $date = convertDateAndTime($value['created_at'], 'date');
        if ($repeated_date == $date) {
            $QryUserLog[$key]['date'] = 0;
        } else {
            $repeated_date = $date;
            $QryUserLog[$key]['date'] = convertDateAndTime($value['created_at'], 'date');
        }
        $QryUserLog[$key]['created_date'] = convertDateAndTime($value['created_at'], 'date');
        $QryUserLog[$key]['time'] = convertDateAndTime($value['created_at'], 'time');
        $QryUserLog[$key]['created_at'] = convertDateTime($value['created_at']);
        $QryUserLog[$key]['updated_at'] = convertDateTime($value['updated_at']);
    }

    $data['timeline'] = $QryUserLog;

    $data = [];
    $data['user']['id'] = $user_id;
    $data['timeline'] = $QryUserLog;
    $response['view'] = view('user_action/detail_tab/detail_user_user_log_tab', compact('data'))->render();
    $response['data'] = $QryUserLog;
    return $response;
}

// FOR USER ACTION START

function saveUserLog($params)
{
    $UserLog = new UserLog();
    $UserLog->user_id = $params['user_id'];
    $UserLog->log_type = $params['log_type'];
    $UserLog->field_name = $params['field_name'];
    $UserLog->old_value = $params['old_value'];
    $UserLog->new_value = $params['new_value'];
    $UserLog->reference_type = $params['reference_type'];
    $UserLog->reference_id = $params['reference_id'];
    $UserLog->transaction_type = $params['transaction_type'];
    $UserLog->description = $params['description'];
    $UserLog->source = $params['source'];
    $UserLog->entryby = Auth::user()->id;
    $UserLog->entryip = $params['ip'];
    $UserLog->save();
}

function saveUserStatus($user_id, $user_new_status, $ip = '', $entry_source = 'WEB')
{
    $userStatus = getArchitectsStatus();

    $User_status = User::find($user_id);

    $Architect = Architect::select('*')
        ->where('user_id', $user_id)
        ->first();

    $Electrician = Electrician::select('*')
        ->where('user_id', $user_id)
        ->first();

    $oldStatus = $User_status->status;
    $newStatus = $user_new_status;

    if ($oldStatus != $newStatus) {
        if ($Architect) {
            $oldStatus = $Architect->status;
            if ($user_new_status == 3 || $user_new_status == 4) {
                $User_status->status = 2;
            } else {
                $User_status->status = $user_new_status;
            }
            $Architect->status = $user_new_status;
            $Architect->save();
        } elseif ($Electrician) {
            $oldStatus = $Electrician->status;
            if ($user_new_status == 3 || $user_new_status == 4) {
                $User_status->status = 2;
            } else {
                $User_status->status = $user_new_status;
            }
            $Electrician->status = $user_new_status;
            $Electrician->save();
        }
        $User_status->save();

        if ($User_status) {
            $timeline = [];
            $timeline['user_id'] = $User_status->id;
            $timeline['log_type'] = 'user';
            $timeline['field_name'] = 'status';
            $timeline['old_value'] = $oldStatus;
            $timeline['new_value'] = $newStatus;
            $timeline['reference_type'] = 'user';
            $timeline['reference_id'] = '0';
            if ($oldStatus == 6) {
                $timeline['transaction_type'] = 'Create';
                if ($Architect) {
                    $timeline['description'] = 'Architect Created by ' . Auth::user()->first_name . ' ' . Auth::user()->last_name;
                } elseif ($Electrician) {
                    $timeline['description'] = 'Electrician Created by ' . Auth::user()->first_name . ' ' . Auth::user()->last_name;
                }
            } else {
                $timeline['transaction_type'] = 'update';
                $timeline['description'] = 'User status changed from  ' . $userStatus[$oldStatus]['name'] . ' to ' . $userStatus[$newStatus]['name'] . ' by ' . Auth::user()->first_name . ' ' . Auth::user()->last_name;
            }
            $timeline['source'] = $entry_source;
            $timeline['ip'] = $ip;
            saveUserLog($timeline);
        }
    }
}

function getTimeSlot()
{
    $timeSlot = [];
    $strtotimeStart = strtotime(date('h:00:00'));
    $latestDateTime = date('h:00:00', $strtotimeStart);
    $i = 0;
    $timeSlot[$i] = date('h:i A', strtotime($latestDateTime));
    for ($i = 1; $i < 48; $i++) {
        $timeSlot[$i] = date('h:i A', strtotime($latestDateTime . ' +30 minutes'));
        $latestDateTime = $timeSlot[$i];
    }
    return $timeSlot;
}

function getReminderTimeSlot($dateTime = '')
{
    if ($dateTime != 0 && $dateTime != '') {
        $dateTime = $dateTime;
    } else {
        $dateTime = date('Y-m-d H:i:s');
    }

    $reminderTimeSlot = [];
    $reminderTimeSlot[1]['id'] = 1;
    $reminderTimeSlot[1]['name'] = '15 Min Before';
    $reminderTimeSlot[1]['datetime'] = date('Y-m-d H:i:s', strtotime($dateTime . ' -15 minutes'));
    $reminderTimeSlot[1]['minute'] = 15;

    $reminderTimeSlot[2]['id'] = 2;
    $reminderTimeSlot[2]['name'] = '30 Min Before';
    $reminderTimeSlot[2]['datetime'] = date('Y-m-d H:i:s', strtotime($dateTime . ' -30 minutes'));
    $reminderTimeSlot[2]['minute'] = 30;

    $reminderTimeSlot[3]['id'] = 3;
    $reminderTimeSlot[3]['name'] = '1 Hour Before';
    $reminderTimeSlot[3]['datetime'] = date('Y-m-d H:i:s', strtotime($dateTime . ' -1 hours'));
    $reminderTimeSlot[3]['minute'] = 60;

    $reminderTimeSlot[4]['id'] = 4;
    $reminderTimeSlot[4]['name'] = '1 Day Before';
    $reminderTimeSlot[4]['datetime'] = date('Y-m-d H:i:s', strtotime($dateTime . ' -1 days'));
    $reminderTimeSlot[4]['minute'] = 1440; // (24*60)

    return $reminderTimeSlot;
}

function getArchitectFilterColumn()
{
    $filter_column = [];
    $filter_column[1]['id'] = 1;
    $filter_column[1]['name'] = '#id';
    $filter_column[1]['column_name'] = 'users.id';
    $filter_column[1]['code'] = 'user_id';
    $filter_column[1]['type'] = 'bigint';
    $filter_column[1]['value_type'] = 'text';

    $filter_column[2]['id'] = 2;
    $filter_column[2]['name'] = 'User Type';
    $filter_column[2]['column_name'] = 'architect.type';
    $filter_column[2]['code'] = 'user_type';
    $filter_column[2]['type'] = 'varchar';
    $filter_column[2]['value_type'] = 'select';

    $filter_column[3]['id'] = 3;
    $filter_column[3]['name'] = 'Total Point';
    $filter_column[3]['column_name'] = 'architect.total_point';
    $filter_column[3]['code'] = 'user_total_point';
    $filter_column[3]['type'] = 'varchar';
    $filter_column[3]['value_type'] = 'select_order_by';

    $filter_column[4]['id'] = 4;
    $filter_column[4]['name'] = 'Account Owner';
    $filter_column[4]['column_name'] = 'architect.sale_person_id';
    $filter_column[4]['code'] = 'account_owner';
    $filter_column[4]['type'] = 'varchar';
    $filter_column[4]['value_type'] = 'select';

    $filter_column[5]['id'] = 5;
    $filter_column[5]['name'] = 'Account Name';
    $filter_column[5]['column_name'] = 'users.first_name';
    $filter_column[5]['code'] = 'account_name';
    $filter_column[5]['type'] = 'varchar';
    $filter_column[5]['value_type'] = 'text';

    $filter_column[6]['id'] = 6;
    $filter_column[6]['name'] = 'Mobile number';
    $filter_column[6]['column_name'] = 'users.phone_number';
    $filter_column[6]['code'] = 'user_phone_number';
    $filter_column[6]['type'] = 'varchar';
    $filter_column[6]['value_type'] = 'text';

    $filter_column[7]['id'] = 7;
    $filter_column[7]['name'] = 'Email';
    $filter_column[7]['column_name'] = 'users.email';
    $filter_column[7]['code'] = 'user_email';
    $filter_column[7]['type'] = 'varchar';
    $filter_column[7]['value_type'] = 'text';

    $filter_column[8]['id'] = 8;
    $filter_column[8]['name'] = 'City';
    $filter_column[8]['column_name'] = 'users.city_id';
    $filter_column[8]['code'] = 'user_city_id';
    $filter_column[8]['type'] = 'varchar';
    $filter_column[8]['value_type'] = 'select';

    $filter_column[9]['id'] = 9;
    $filter_column[9]['name'] = 'Status';
    $filter_column[9]['column_name'] = 'architect.status';
    $filter_column[9]['code'] = 'user_status';
    $filter_column[9]['type'] = 'tinyint';
    $filter_column[9]['value_type'] = 'select';

    $filter_column[10]['id'] = 10;
    $filter_column[10]['name'] = 'Created By';
    $filter_column[10]['column_name'] = 'users.created_by';
    $filter_column[10]['code'] = 'user_created_by';
    $filter_column[10]['type'] = 'bigint';
    $filter_column[10]['value_type'] = 'select';

    $filter_column[11]['id'] = 11;
    $filter_column[11]['name'] = 'Created Date';
    $filter_column[11]['column_name'] = 'users.created_at';
    $filter_column[11]['code'] = 'user_created_at';
    $filter_column[11]['type'] = 'date';
    $filter_column[11]['value_type'] = 'date';

    $filter_column[12]['id'] = 12;
    $filter_column[12]['name'] = 'Tag';
    $filter_column[12]['column_name'] = 'users.tag';
    $filter_column[12]['code'] = 'user_tag';
    $filter_column[12]['type'] = 'varchar';
    $filter_column[12]['value_type'] = 'select';

    $filter_column[13]['id'] = 13;
    $filter_column[13]['name'] = 'Available Point';
    $filter_column[13]['column_name'] = 'architect.total_point_current';
    $filter_column[13]['code'] = 'user_total_point_current';
    $filter_column[13]['type'] = 'varchar';
    $filter_column[13]['value_type'] = 'select_order_by';

    $filter_column[14]['id'] = 14;
    $filter_column[14]['name'] = 'Category';
    $filter_column[14]['column_name'] = 'architect.category_id';
    $filter_column[14]['code'] = 'architect_category_id';
    $filter_column[14]['type'] = 'varchar';
    $filter_column[14]['value_type'] = 'select';

    $filter_column[15]['id'] = 15;
    $filter_column[15]['name'] = 'Source';
    $filter_column[15]['column_name'] = 'architect.source_type';
    $filter_column[15]['code'] = 'user_source';
    $filter_column[15]['type'] = 'varchar';
    $filter_column[15]['value_type'] = 'select';

    return $filter_column;
}

function getElectricianFilterColumn()
{
    $filter_column = [];
    $filter_column[1]['id'] = 1;
    $filter_column[1]['name'] = '#id';
    $filter_column[1]['column_name'] = 'users.id';
    $filter_column[1]['code'] = 'user_id';
    $filter_column[1]['type'] = 'bigint';
    $filter_column[1]['value_type'] = 'text';

    $filter_column[2]['id'] = 2;
    $filter_column[2]['name'] = 'User Type';
    $filter_column[2]['column_name'] = 'electrician.type';
    $filter_column[2]['code'] = 'user_type';
    $filter_column[2]['type'] = 'varchar';
    $filter_column[2]['value_type'] = 'select';

    $filter_column[3]['id'] = 3;
    $filter_column[3]['name'] = 'Total Point';
    $filter_column[3]['column_name'] = 'electrician.total_point';
    $filter_column[3]['code'] = 'user_total_point';
    $filter_column[3]['type'] = 'varchar';
    $filter_column[3]['value_type'] = 'select_order_by';

    $filter_column[4]['id'] = 4;
    $filter_column[4]['name'] = 'Account Owner';
    $filter_column[4]['column_name'] = 'electrician.sale_person_id';
    $filter_column[4]['code'] = 'account_owner';
    $filter_column[4]['type'] = 'varchar';
    $filter_column[4]['value_type'] = 'select';

    $filter_column[5]['id'] = 5;
    $filter_column[5]['name'] = 'Account Name';
    $filter_column[5]['column_name'] = 'users.first_name';
    $filter_column[5]['code'] = 'account_name';
    $filter_column[5]['type'] = 'varchar';
    $filter_column[5]['value_type'] = 'text';

    $filter_column[6]['id'] = 6;
    $filter_column[6]['name'] = 'Mobile number';
    $filter_column[6]['column_name'] = 'users.phone_number';
    $filter_column[6]['code'] = 'user_phone_number';
    $filter_column[6]['type'] = 'varchar';
    $filter_column[6]['value_type'] = 'text';

    $filter_column[7]['id'] = 7;
    $filter_column[7]['name'] = 'Email';
    $filter_column[7]['column_name'] = 'users.email';
    $filter_column[7]['code'] = 'user_email';
    $filter_column[7]['type'] = 'varchar';
    $filter_column[7]['value_type'] = 'text';

    $filter_column[8]['id'] = 8;
    $filter_column[8]['name'] = 'City';
    $filter_column[8]['column_name'] = 'users.city_id';
    $filter_column[8]['code'] = 'user_city_id';
    $filter_column[8]['type'] = 'varchar';
    $filter_column[8]['value_type'] = 'select';

    $filter_column[9]['id'] = 9;
    $filter_column[9]['name'] = 'Status';
    $filter_column[9]['column_name'] = 'electrician.status';
    $filter_column[9]['code'] = 'user_status';
    $filter_column[9]['type'] = 'tinyint';
    $filter_column[9]['value_type'] = 'select';

    $filter_column[10]['id'] = 10;
    $filter_column[10]['name'] = 'Created By';
    $filter_column[10]['column_name'] = 'users.created_by';
    $filter_column[10]['code'] = 'user_created_by';
    $filter_column[10]['type'] = 'bigint';
    $filter_column[10]['value_type'] = 'select';

    $filter_column[11]['id'] = 11;
    $filter_column[11]['name'] = 'Created Date';
    $filter_column[11]['column_name'] = 'users.created_at';
    $filter_column[11]['code'] = 'user_created_at';
    $filter_column[11]['type'] = 'date';
    $filter_column[11]['value_type'] = 'date';

    $filter_column[12]['id'] = 12;
    $filter_column[12]['name'] = 'Tag';
    $filter_column[12]['column_name'] = 'users.tag';
    $filter_column[12]['code'] = 'user_tag';
    $filter_column[12]['type'] = 'varchar';
    $filter_column[12]['value_type'] = 'select';

    $filter_column[13]['id'] = 13;
    $filter_column[13]['name'] = 'Available Point';
    $filter_column[13]['column_name'] = 'electrician.total_point_current';
    $filter_column[13]['code'] = 'user_total_point_current';
    $filter_column[13]['type'] = 'varchar';
    $filter_column[13]['value_type'] = 'select_order_by';

    return $filter_column;
}

function getUserFilterCondtion()
{
    $filter_condtion = [];
    $filter_condtion[1]['id'] = 1;
    $filter_condtion[1]['name'] = 'IS';
    $filter_condtion[1]['code'] = 'is';
    $filter_condtion[1]['condtion'] = '=';
    $filter_condtion[1]['value_type'] = 'single_select';

    $filter_condtion[2]['id'] = 2;
    $filter_condtion[2]['name'] = 'Is Not';
    $filter_condtion[2]['code'] = 'is_not';
    $filter_condtion[2]['condtion'] = '!=';
    $filter_condtion[2]['value_type'] = 'single_select';

    $filter_condtion[3]['id'] = 3;
    $filter_condtion[3]['name'] = 'Contains';
    $filter_condtion[3]['code'] = 'contains';
    $filter_condtion[3]['condtion'] = 'IN';
    $filter_condtion[3]['value_type'] = 'multi_select';

    $filter_condtion[4]['id'] = 4;
    $filter_condtion[4]['name'] = "Doesn't Contains";
    $filter_condtion[4]['code'] = 'not_contains';
    $filter_condtion[4]['condtion'] = 'NOTIN';
    $filter_condtion[4]['value_type'] = 'multi_select';

    $filter_condtion[5]['id'] = 5;
    $filter_condtion[5]['name'] = 'Between';
    $filter_condtion[5]['code'] = 'between';
    $filter_condtion[5]['condtion'] = 'BETWEEN';
    $filter_condtion[5]['value_type'] = 'between';

    return $filter_condtion;
}

function getUserFilterClause()
{
    $filter_clause = [];
    $filter_clause[1]['id'] = 1;
    $filter_clause[1]['name'] = 'And';
    $filter_clause[1]['clause'] = 'where';

    $filter_clause[2]['id'] = 2;
    $filter_clause[2]['name'] = 'Or';
    $filter_clause[2]['clause'] = 'orwhere';

    return $filter_clause;
}
function getLeadBillstatus()
{
    $filter_clause = [];
    $filter_clause[101]['id'] = 101;
    $filter_clause[101]['name'] = 'Query';
    $filter_clause[101]['code'] = 'QUERY';

    $filter_clause[102]['id'] = 102;
    $filter_clause[102]['name'] = 'Lapsed';
    $filter_clause[102]['clause'] = 'LAPSED';

    $filter_clause[100]['id'] = 100;
    $filter_clause[100]['name'] = 'Claimed';
    $filter_clause[100]['clause'] = 'CLAIMED';

    return $filter_clause;
}

function getPointValue()
{
    $point_clause = [];

    $point_clause[1]['id'] = 1;
    $point_clause[1]['name'] = 'Sort By Max Point';
    $point_clause[1]['code'] = 'short_by_max_point';

    $point_clause[2]['id'] = 2;
    $point_clause[2]['name'] = 'Sort By Min Point';
    $point_clause[2]['code'] = 'short_by_min_point';

    return $point_clause;
}

function getUserDateFilterValue()
{
    $filter_clause = [];
    $filter_clause[1]['id'] = 1;
    $filter_clause[1]['name'] = 'All Date';
    $filter_clause[1]['code'] = 'all_closing';
    $filter_clause[1]['value'] = date('Y-m-d') . ',' . date('Y-m-d');

    $filter_clause[2]['id'] = 2;
    $filter_clause[2]['name'] = 'In This Week';
    $filter_clause[2]['code'] = 'in_this_week';
    $filter_clause[2]['value'] = date('Y-m-d', strtotime('next Monday -1 week')) . ',' . date('Y-m-d', strtotime(date('Y-m-d', date('w', strtotime('next Monday -1 week')) == date('w') ? strtotime(date('Y-m-d', strtotime('next Monday -1 week')) . ' +7 days') : strtotime('next Monday -1 week')) . ' +6 days'));

    $filter_clause[3]['id'] = 3;
    $filter_clause[3]['name'] = 'In This Month';
    $filter_clause[3]['code'] = 'in_this_month';
    $filter_clause[3]['value'] = date('Y-m-d', strtotime('next Monday 0 week')) . ',' . date('Y-m-d', strtotime(date('Y-m-d', date('w', strtotime('next Monday 0 week')) == date('w') ? strtotime(date('Y-m-d', strtotime('next Monday 0 week')) . ' +7 days') : strtotime('next Monday 0 week')) . ' +6 days'));

    $filter_clause[4]['id'] = 4;
    $filter_clause[4]['name'] = 'In Next Month';
    $filter_clause[4]['code'] = 'in_next_month';
    $filter_clause[4]['value'] = date('Y-m-d', strtotime('next Monday -2 week')) . ',' . date('Y-m-d', strtotime(date('Y-m-d', date('w', strtotime('next Monday -2 week')) == date('w') ? strtotime(date('Y-m-d', strtotime('next Monday -2 week')) . ' +7 days') : strtotime('next Monday -2 week')) . ' +6 days'));

    $filter_clause[5]['id'] = 5;
    $filter_clause[5]['name'] = 'In Next Two Month';
    $filter_clause[5]['code'] = 'in_next_two_month';
    $filter_clause[5]['value'] = date('Y-m-d', strtotime('next Monday -2 week')) . ',' . date('Y-m-d', strtotime(date('Y-m-d', date('w', strtotime('next Monday -2 week')) == date('w') ? strtotime(date('Y-m-d', strtotime('next Monday -2 week')) . ' +7 days') : strtotime('next Monday -2 week')) . ' +6 days'));

    $filter_clause[6]['id'] = 6;
    $filter_clause[6]['name'] = 'In Next Three Month';
    $filter_clause[6]['code'] = 'in_next_three_month';
    $filter_clause[6]['value'] = date('Y-m-d', strtotime('next Monday -2 week')) . ',' . date('Y-m-d', strtotime(date('Y-m-d', date('w', strtotime('next Monday -2 week')) == date('w') ? strtotime(date('Y-m-d', strtotime('next Monday -2 week')) . ' +7 days') : strtotime('next Monday -2 week')) . ' +6 days'));

    return $filter_clause;
}

function getInquiryTransferToLeadUserList()
{
    // 5515 New User
    return ['4190', '4', '4344', '37', '5262', '4425', '1233', '3352', '29', '36', '5515', '3245', '22', '38', '21', '5263', '3'];
}

function getIntervalTime()
{
    $intervalTime = [];
    $intervalTime[1]['id'] = 1;
    $intervalTime[1]['name'] = '30 Min';
    $intervalTime[1]['code'] = ' +30 minutes ';
    $intervalTime[1]['minute'] = 30;

    $intervalTime[2]['id'] = 2;
    $intervalTime[2]['name'] = '1 Hour';
    $intervalTime[2]['code'] = ' +1 hours ';
    $intervalTime[2]['minute'] = 60;

    $intervalTime[3]['id'] = 3;
    $intervalTime[3]['name'] = '1.5 Hours';
    $intervalTime[3]['code'] = ' +1 hours +30 minutes ';
    $intervalTime[3]['minute'] = 90;

    $intervalTime[4]['id'] = 4;
    $intervalTime[4]['name'] = '2 Hours';
    $intervalTime[4]['code'] = ' +2 hours ';
    $intervalTime[4]['minute'] = 120;

    $intervalTime[5]['id'] = 5;
    $intervalTime[5]['name'] = '2.5 Hours';
    $intervalTime[5]['code'] = ' +2 hours +30 minutes ';
    $intervalTime[5]['minute'] = 150;

    $intervalTime[6]['id'] = 6;
    $intervalTime[6]['name'] = '3 Hours';
    $intervalTime[6]['code'] = ' +3 hours ';
    $intervalTime[6]['minute'] = 180;

    $intervalTime[7]['id'] = 7;
    $intervalTime[7]['name'] = '3.5 Hours';
    $intervalTime[7]['code'] = ' +3 hours +30 minutes ';
    $intervalTime[7]['minute'] = 210;

    $intervalTime[8]['id'] = 8;
    $intervalTime[8]['name'] = '4 Hours';
    $intervalTime[8]['code'] = ' +4 hours ';
    $intervalTime[8]['minute'] = 240;

    $intervalTime[9]['id'] = 9;
    $intervalTime[9]['name'] = '4.5 Hours';
    $intervalTime[9]['code'] = ' +4 hours +30 minutes ';
    $intervalTime[8]['minute'] = 270;

    $intervalTime[10]['id'] = 10;
    $intervalTime[10]['name'] = '5 Hours';
    $intervalTime[10]['code'] = ' +5 hours ';
    $intervalTime[10]['minute'] = 300;

    $intervalTime[11]['id'] = 11;
    $intervalTime[11]['name'] = '5.5 Hours';
    $intervalTime[11]['code'] = ' +5 hours +30 minutes ';
    $intervalTime[11]['minute'] = 330;

    $intervalTime[12]['id'] = 12;
    $intervalTime[12]['name'] = '6 Hours';
    $intervalTime[12]['code'] = ' +6 hours ';
    $intervalTime[12]['minute'] = 360;

    // $intervalTime[13]['id'] = 13;
    // $intervalTime[13]['name'] = "1.5 Hours";
    // $intervalTime[13]['code'] = " +1 hours +30 minute ";

    // $intervalTime[14]['id'] = 14;
    // $intervalTime[14]['name'] = "7 Hours";
    // $intervalTime[14]['code'] = " +1 hours +30 minute ";

    // $intervalTime[15]['id'] = 15;
    // $intervalTime[15]['name'] = "1.5 Hours";
    // $intervalTime[15]['code'] = " +1 hours +30 minute ";

    // $intervalTime[16]['id'] = 16;
    // $intervalTime[16]['name'] = "8 Hours";
    // $intervalTime[16]['code'] = " +1 hours +30 minute ";

    // $intervalTime[17]['id'] = 17;
    // $intervalTime[17]['name'] = "1.5 Hours";
    // $intervalTime[17]['code'] = " +1 hours +30 minute ";

    // $intervalTime[3]['id'] = 3;
    // $intervalTime[3]['name'] = "9 Hours";
    // $intervalTime[3]['code'] = " +1 hours +30 minute ";

    // $intervalTime[3]['id'] = 3;
    // $intervalTime[3]['name'] = "1.5 Hours";
    // $intervalTime[3]['code'] = " +1 hours +30 minute ";

    // $intervalTime[3]['id'] = 3;
    // $intervalTime[3]['name'] = "10 Hours";
    // $intervalTime[3]['code'] = " +1 hours +30 minute ";

    return $intervalTime;
}

function getRewardValue()
{
    $reward_value = [];

    $reward_value[1]['id'] = 1;
    $reward_value[1]['name'] = 'HOD Pending';
    $reward_value[1]['code'] = 'hod_pending';

    $reward_value[2]['id'] = 2;
    $reward_value[2]['name'] = 'Hod Approved';
    $reward_value[2]['code'] = 'hod_approved';

    return $reward_value;
}

function verificationStatus()
{
    $verification_status = [];

    $verification_status[0]['id'] = 0;
    $verification_status[0]['telesales_name'] = '<span class="badge badge-pill badge-soft-warning font-size-11">Pending</span>';
    $verification_status[0]['telesales_code'] = 'Pending';
    $verification_status[0]['service_name'] = '<span class="badge badge-pill badge-soft-warning font-size-11">Pending</span>';
    $verification_status[0]['service_code'] = 'Pending';
    $verification_status[0]['company_admin_name'] = '<span class="badge badge-pill badge-soft-warning font-size-11">Pending</span>';
    $verification_status[0]['company_admin_code'] = 'Pending';

    $verification_status[1]['id'] = 1;
    $verification_status[1]['telesales_name'] = '<span class="badge badge-pill badge-soft-primary font-size-11">Working</span>';
    $verification_status[1]['telesales_code'] = 'Working';
    $verification_status[1]['service_name'] = '<span class="badge badge-pill badge-soft-primary font-size-11">Working</span>';
    $verification_status[1]['service_code'] = 'Working';
    $verification_status[1]['company_admin_name'] = '<span class="badge badge-pill badge-soft-primary font-size-11">Working</span>';
    $verification_status[1]['company_admin_code'] = 'Working';

    $verification_status[2]['id'] = 2;
    $verification_status[2]['telesales_name'] = '<span class="badge badge-pill badge-soft-success font-size-11">Verified</span>';
    $verification_status[2]['telesales_code'] = 'Verified';
    $verification_status[2]['service_name'] = '<span class="badge badge-pill badge-soft-success font-size-11">Installation Done</span>';
    $verification_status[2]['service_code'] = 'Installation Done';
    $verification_status[2]['company_admin_name'] = '<span class="badge badge-pill badge-soft-success font-size-11">Approved</span>';
    $verification_status[2]['company_admin_code'] = 'Approved';

    $verification_status[3]['id'] = 3;
    $verification_status[3]['telesales_name'] = '<span class="badge badge-pill badge-soft-danger font-size-11">Not Verified</span>';
    $verification_status[3]['telesales_code'] = 'Not Verified';
    $verification_status[3]['service_name'] = '<span class="badge badge-pill badge-soft-danger font-size-11">Installation Request Not Received</span>';
    $verification_status[3]['service_code'] = 'Installation Request Not Received';
    $verification_status[3]['company_admin_name'] = '<span class="badge badge-pill badge-soft-danger font-size-11">Rejected</span>';
    $verification_status[3]['company_admin_code'] = 'Rejected';

    return $verification_status;
}

function getTaskOutComeType()
{
    $outcome_value = [];

    $outcome_value[101]['id'] = 101;
    $outcome_value[101]['name'] = 'Installation Done';

    $outcome_value[102]['id'] = 102;
    $outcome_value[102]['name'] = 'Installation Request Not Received';

    return $outcome_value;
}

function getNotificationUserType()
{
    $userTypes = [];
    $userTypes[0]['id'] = 0;
    $userTypes[0]['name'] = 'Admin';
    $userTypes[0]['user_type'] = 0;

    $userTypes[1]['id'] = 1;
    $userTypes[1]['name'] = 'Company Admin';
    $userTypes[1]['user_type'] = 1;

    $userTypes[2]['id'] = 2;
    $userTypes[2]['name'] = 'Sales';
    $userTypes[2]['user_type'] = 2;

    $userTypes[201]['id'] = 201;
    $userTypes[201]['name'] = 'Architect(Non Prime)';
    $userTypes[201]['user_type'] = 201;

    $userTypes[202]['id'] = 202;
    $userTypes[202]['name'] = 'Architect(Prime)';
    $userTypes[202]['user_type'] = 202;

    $userTypes[301]['id'] = 301;
    $userTypes[301]['name'] = 'Electrician(Non Prime)';
    $userTypes[301]['user_type'] = 301;

    $userTypes[302]['id'] = 302;
    $userTypes[302]['name'] = 'Electrician(Prime)';
    $userTypes[302]['user_type'] = 302;

    $userTypes[101]['id'] = 101;
    $userTypes[101]['name'] = 'ASM(Authorize Stocklist Merchantize)';
    $userTypes[101]['user_type'] = 101;

    $userTypes[102]['id'] = 102;
    $userTypes[102]['name'] = 'ADM(Authorize Distributor Merchantize)';
    $userTypes[102]['user_type'] = 102;

    $userTypes[103]['id'] = 103;
    $userTypes[103]['name'] = 'APM(Authorize Project Merchantize)';
    $userTypes[103]['user_type'] = 103;

    $userTypes[104]['id'] = 104;
    $userTypes[104]['name'] = 'AD(Authorised Dealer)';
    $userTypes[104]['user_type'] = 104;

    $userTypes[105]['id'] = 105;
    $userTypes[105]['name'] = 'Retailer';
    $userTypes[105]['user_type'] = 105;

    return $userTypes;
}

function UsersTypeNotificationTokens($userType)
{
    $notificationTokens = [];
    $Users = User::select('fcm_token')
        ->whereIn('type', $userType)
        ->get();
    if (count($Users) > 0) {
        foreach ($Users as $keyPush => $valuePush) {
            if ($valuePush->fcm_token != '' || $valuePush->fcm_token != null) {
                $notificationTokens[] = $valuePush->fcm_token;
            }
        }
    }
    return $notificationTokens;
}

function highlightString($str, $search_term)
{
    if (empty($search_term)) {
        return $str;
    }

    $pos = strpos(strtolower($str), strtolower($search_term));

    if ($pos !== false) {
        $replaced = substr($str, 0, $pos);
        $replaced .= '<span class="highlight">' . substr($str, $pos, strlen($search_term)) . '</span>';
        $replaced .= substr($str, $pos + strlen($search_term));
    } else {
        $replaced = $str;
    }

    return $replaced;
}

function saveNotificationScheduler($params)
{
    // return $params;
    $NotificationScheduler = new NotificationScheduler();
    $NotificationScheduler->from_mail = $params['from_email'];
    $NotificationScheduler->from_name = $params['from_name'];
    $NotificationScheduler->to_email = $params['to_email'];
    $NotificationScheduler->to_name = $params['to_name'];
    $NotificationScheduler->bcc_mail = $params['bcc_email'];
    $NotificationScheduler->cc_mail = $params['cc_email'];
    $NotificationScheduler->subject = $params['subject'];
    $NotificationScheduler->transaction_id = $params['transaction_id'];
    $NotificationScheduler->transaction_name = $params['transaction_name'];
    $NotificationScheduler->transaction_type = $params['transaction_type'];
    $NotificationScheduler->transaction_detail = $params['transaction_detail'];
    $NotificationScheduler->attachment = $params['attachment'];
    if ($params['transaction_name'] == 'Deal') {
        $NotificationScheduler->lead_id = $params['lead_id'];
        $NotificationScheduler->point_value = $params['point_value'];
    } else {
        $NotificationScheduler->lead_id = 0;
        $NotificationScheduler->point_value = 0;
    }
    $NotificationScheduler->remark = $params['remark'];
    $NotificationScheduler->source = $params['source'];
    $NotificationScheduler->entryby = Auth::user()->id;
    $NotificationScheduler->entryip = $params['entryip'];
    $NotificationScheduler->save();
}

function marketingLeadStatus($marketingLeadStatus)
{
    $marketingLeadStatus = (int) $marketingLeadStatus;

    if ($marketingLeadStatus == 0) {
        $marketingLeadStatus = 'PENDING';
    } elseif ($marketingLeadStatus == 1) {
        $marketingLeadStatus = 'LEAD GENERATED';
    } elseif ($marketingLeadStatus == 2) {
        $marketingLeadStatus = 'LEAD ERROR';
    }
    return $marketingLeadStatus;
}
