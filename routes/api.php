<?php

use App\Http\Controllers\API\ArchitectsController;
use App\Http\Controllers\API\ChannelPartnersController;
use App\Http\Controllers\API\CRM\InquiryController as CRMInquiryController;
use App\Http\Controllers\API\CRM\UserGiftCategoryController as CRMUserGiftCategoryController;
use App\Http\Controllers\API\CRM\UserGiftProductController as CRMUserGiftProductController;
use App\Http\Controllers\API\CRM\CRMGiftProductOrderController;
use App\Http\Controllers\API\CRM\UserHelpDocumentController as CRMUserHelpDocumentController;
use App\Http\Controllers\API\CRM\UserLogController as CRMUserLogController;
use App\Http\Controllers\API\CRM\UserOrderController as CRMUserOrderController;
use App\Http\Controllers\API\CRM\UserRaiseQueryController as CRMUserRaiseQueryController;
use App\Http\Controllers\API\Dashboard\DashboardController;
use App\Http\Controllers\API\Dashboard\DashboardCountController;
use App\Http\Controllers\API\Dashboard\DashboardChartController;
use App\Http\Controllers\API\Dashboard\DashboardReportController;
use App\Http\Controllers\API\Dashboard\InquiryArchitectsController as DashboardInquiryArchitectsController;
use App\Http\Controllers\API\Dashboard\SalesOrderController as DashboardSalesOrderController;
use App\Http\Controllers\API\ElectriciansController;
use App\Http\Controllers\API\ExhibitionController;
use App\Http\Controllers\API\GeneralController;
use App\Http\Controllers\API\LoginController;
use App\Http\Controllers\API\Marketing\OrderController as MarketingOrderController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\MasterSearchController;
use App\Http\Controllers\API\ProductWarranty\ProductWarrantyApiController;
use App\Http\Controllers\API\Quotation\QuotationApiController;
use App\Http\Controllers\API\Quotation\QuotationOfflineApiController;
use App\Http\Controllers\API\Quotation\QuotationDetailUpdateApiController;
use App\Http\Controllers\API\Quotation\QuotationRequestController;
use App\Http\Controllers\API\Target\TargetViewApiController;
use App\Http\Controllers\API\ZohoController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\CRM\LEAD\LeadController;
use App\Http\Controllers\API\CRM\LEAD\LeadApiController;
use App\Http\Controllers\API\CRM\LEAD\LeadMeetingApiController;
use App\Http\Controllers\API\CRM\LEAD\LeadMyActionApiController;
use App\Http\Controllers\API\CRM\LEAD\LeadFilterApiController;
use App\Http\Controllers\Whatsapp\WhatsappApiContoller;


use App\Http\Controllers\API\UserAction\CommanUserDetailController;
use App\Http\Controllers\API\UserAction\UserAllDetailController;
use App\Http\Controllers\API\UserAction\UserCallController;
use App\Http\Controllers\API\UserAction\UserContactController;
use App\Http\Controllers\API\UserAction\UserFileController;
use App\Http\Controllers\API\UserAction\UserMeetingController;
use App\Http\Controllers\API\UserAction\UserNoteController;
use App\Http\Controllers\API\UserAction\UserTaskController;
use App\Http\Controllers\API\CRM\LEAD\LeadQuestionController;


use App\Http\Controllers\API\CommanController;
 /*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "API" middleware group. Enjoy building your API!
|
*/

// user

Route::get('/create-auto-generated-task', [GeneralController::class, "createAutoGenartedTask"]);
Route::get('/whatsappCheck', [GeneralController::class, "whatsappCheck"]);
Route::get('/facebookLead', [GeneralController::class, "facebookLead"]);
Route::get('/bill-list-hodapprove-but-point-not-credit', [GeneralController::class, "BillListHodApproveButPointPendingExcel"]);
Route::get('/lost-lead-deal-excel-with-question', [GeneralController::class, "LostLeadDealExcelWithQuestion"]);
Route::post('/lead-generate-threw-excel', [GeneralController::class, "LeadGenerateThrewExcel"]);
Route::post('/arc-ele-customer-threw-excel', [GeneralController::class, "ArcEleCustomerThrewExcel"]);
Route::get('/lead-duplicate-lead', [GeneralController::class, "duplicatDeal"])->name('lead.duplicate.lead');
Route::group(["middleware" => "api.container"], function () {
	
	Route::get('/create-channel-partner-by-zoho', [ZohoController::class, "createChannelPartner"]);
	Route::post('/create-channel-partner-by-zoho', [ZohoController::class, "createChannelPartner"]);
	Route::post('login', [LoginController::class, "loginProcess"]);
	Route::post('send-otp', [LoginController::class, "sendOTP"]);
	Route::post('verify-otp', [LoginController::class, "verifyOTP"]);
	Route::post('/post-product-warranty-register-save', [ProductWarrantyApiController::class, 'PostProductWarrantyRegisterSave']);
	Route::get('/city-list', [GeneralController::class, "searchCity"]);
	
	Route::group(["middleware" => "auth.api"], function () {
		
		Route::post('/excel-upload-to-create-deal', [CommanController::class, "createDeal"])->name('excel.upload.to.create.deal');

		Route::get('/profile', [LoginController::class, "profile"])->name('api.profile');
		Route::get('/logout', [LoginController::class, "logout"]);
		Route::post('/changepassword', [LoginController::class, "changePassword"])->name('api.do.changepassword');
		Route::post('/changempin', [LoginController::class, "changempin"])->name('api.do.changempin');

		Route::get('/get-channel-partner-type', [GeneralController::class, "getChannelPartnerTypes"]);
		Route::get('/get-architects-type', [GeneralController::class, "getArchitectsTypes"]);
		Route::get('/get-electricians-type', [GeneralController::class, "getElecriciansTypes"]);
		Route::get('/get-user-type', [GeneralController::class, "getUserTypes"]);
		Route::get('/get-user-status', [GeneralController::class, "getUserStatus"]);
		Route::post('/user-phone-number-check', [GeneralController::class, "checkUserPhoneNumber"]);
		Route::post('/user-email-check', [GeneralController::class, "checkUserEmail"]);
		Route::get('/get-banner-images', [GeneralController::class, "getBannerImages"]);
		Route::get('/search-architect-user-tag', [GeneralController::class, "searchArchitectUserTag"]);

		Route::get('/search-courier', [GeneralController::class, "searchCourier"]);
		Route::get('/search-country', [GeneralController::class, "searchCountry"]);
		Route::get('/search-city', [GeneralController::class, "searchCity"]);
		Route::get('/search-state-from-country', [GeneralController::class, "searchStateFromCountry"]);
		Route::get('/search-city-from-state', [GeneralController::class, "searchCityFromState"]);
		Route::get('/get-all-city', [GeneralController::class, "allCity"]);

		Route::get('/dashboard-sale-order-count-search-channel-partner', [DashboardSalesOrderController::class, "searchChannelPartner"]);
		Route::get('/dashboard-sale-order-count-search-user', [DashboardSalesOrderController::class, "searchUser"]);
		Route::post('/dashboard-sale-order-count-data', [DashboardSalesOrderController::class, "saleOrdercount"]);

		Route::get('/dashboard-inquiry-architects-count-search-user', [DashboardInquiryArchitectsController::class, "searchUser"]);
		Route::post('/dashboard-inquiry-architects-count-data', [DashboardInquiryArchitectsController::class, "inquiryCount"]);

		Route::get('/dashboard-architect', [DashboardController::class, "index"]);
		Route::get('/dashboard-electrician', [DashboardController::class, "index"]);

		Route::get('/search-dashboard-channelpartner-type', [DashboardCountController::class, "searchChannelPartnerTypes"]);
		Route::get('/search-dashboard-channelpartner', [DashboardCountController::class, "searchChannelPartner"]);
		Route::get('/search-dashboard-sales-user', [DashboardCountController::class, "searchUser"]);
		Route::post('/dashboard-count', [DashboardCountController::class, "dashboardCount"]);

		Route::post('/dashboard-barchart-data', [DashboardChartController::class, "barChartCount"]);

		Route::post('/dashboard-report-data', [DashboardReportController::class, "dashboardReport"]);

		Route::get('/architect-categories', [ArchitectsController::class, "getCategory"]);
		Route::post('/architects', [ArchitectsController::class, "ajax"]);
		Route::get('/architect-search-sale-person', [ArchitectsController::class, "searchSalePerson"]);
		Route::get('/architect-source-types', [GeneralController::class, "getArchitectsSourceTypes"]);
		Route::get('/architect-detail', [ArchitectsController::class, "detail"]);
		Route::post('/architect-save', [ArchitectsController::class, "save"]);
		Route::post('/architect-point-log', [ArchitectsController::class, "pointLog"]);
		Route::post('/architect-inquiry-log', [ArchitectsController::class, "inquiryLog"]);
		Route::post('/architect-lead-log', [ArchitectsController::class, "leadLog"]);
		Route::post('/architect-lead-log-count', [ArchitectsController::class, "getArchitectLogCount"]);
		Route::post('/architects-edit-save', [ArchitectsController::class, "saveEditArchitect"]);

		Route::post('/electricians', [ElectriciansController::class, "ajax"]);
		Route::get('/electricians-search-sale-person', [ElectriciansController::class, "searchSalePerson"]);
		Route::post('/electrician-save', [ElectriciansController::class, "save"]);
		Route::get('/electrician-detail', [ElectriciansController::class, "detail"]);
		Route::get('/electricians-export', [ElectriciansController::class, "export"]);
		Route::post('/electricians-point-log', [ElectriciansController::class, "pointLog"]);
		Route::post('/electricians-inquiry-log', [ElectriciansController::class, "inquiryLog"]);
		Route::post('/electricians-lead-log', [ElectriciansController::class, "leadLog"]);
		Route::post('/electricians-lead-log-count', [ElectriciansController::class, "getElectricianLogCount"]);

		Route::get('/order-search-channel-partner', [OrderController::class, "searchChannelPartner"]);
		Route::get('/order-channel-partner-detail', [OrderController::class, "channelPartnerDetail"]);
		Route::get('/order-product-detail', [OrderController::class, "productDetail"]);

		Route::get('/order-search-product', [OrderController::class, "searchProduct"]);
		Route::get('/order-search-product', [OrderController::class, "searchProduct"]);
		Route::post('/order-calculation', [OrderController::class, "calculation"]);
		Route::post('/orders-save', [OrderController::class, "save"]);
		Route::post('/orders-list', [OrderController::class, "ajax"]);
		Route::post('/orders-invoice-list', [OrderController::class, "invoiceListAjax"]);
		Route::get('/orders-cancel', [OrderController::class, "cancel"]);
		Route::get('/orders-detail', [OrderController::class, "detail"]);

		Route::get('/inquiry-stage-of-site', [CRMInquiryController::class, "stageOfsite"]);
		Route::get('/inquiry-pre-questions', [CRMInquiryController::class, "preQuestions"]);
		Route::post('/inquiry-phone-number-check', [CRMInquiryController::class, "checkPhoneNumber"]);
		Route::get('/inquiry-get-source-types', [CRMInquiryController::class, "getSourceTypes"]);
		//Route::get('/inquiry', [CRMInquiryController::class, "index"]);
		Route::post('/inquiry-list', [CRMInquiryController::class, "ajax"]);
		Route::get('/inquiry-detail', [CRMInquiryController::class, "detail"]);
		Route::get('/inquiry-search-user', [CRMInquiryController::class, "searchUser"]);
		Route::get('/inquiry-search-architect', [CRMInquiryController::class, "searchArchitect"]);
		Route::get('/inquiry-search-electrician', [CRMInquiryController::class, "searchElectrician"]);
		Route::get('/inquiry-search-assigned-user', [CRMInquiryController::class, "searchAssignedUser"]);
		Route::post('/inquiry-save', [CRMInquiryController::class, "saveInquiry"]);
		Route::get('/inquiry-questions', [CRMInquiryController::class, "inquiryQuestions"]);
		Route::post('/inquiry-answer-save', [CRMInquiryController::class, "saveInquiryAnswer"]);
		Route::get('/inquiry-assigned-user', [CRMInquiryController::class, "assignedUser"]);
		Route::post('/inquiry-assigned-to-save', [CRMInquiryController::class, "saveAssignedTo"]);
		Route::post('/inquiry-quotation-save', [CRMInquiryController::class, "saveQuotation"]);
		Route::post('/inquiry-billing-save', [CRMInquiryController::class, "saveBilling"]);
		Route::post('/inquiry-closing-datetime-to-save', [CRMInquiryController::class, "saveClosingDateTime"]);
		Route::post('/inquiry-follow-up-datetime-to-save', [CRMInquiryController::class, "saveFollowUpDateTime"]);
		Route::get('/inquiry-search-mention-users', [CRMInquiryController::class, "searchMentionUsers"]);
		Route::get('/inquiry-update-seen', [CRMInquiryController::class, "updateSeen"]);
		Route::get('/inquiry-stage-of-site-to-save', [CRMInquiryController::class, "saveStageOfSite"]);

		Route::post('/crm-point-value', [CRMUserLogController::class, "poingValue"]);

		Route::post('/crm-user-log', [CRMUserLogController::class, "ajax"]);
		Route::post('/crm-user-gift-category', [CRMUserGiftCategoryController::class, "ajax"]);
		Route::post('/crm-user-gift-products', [CRMUserGiftProductController::class, "ajax"]);
		Route::post('/crm-user-gift-products-place-order', [CRMUserOrderController::class, "placeOrder"]);
		Route::get('/crm-user-help-document', [CRMUserHelpDocumentController::class, "index"]);
		Route::post('/crm-user-orders', [CRMUserOrderController::class, "ajax"]);
		Route::get('/crm-user-order-detail', [CRMUserOrderController::class, "detail"]);
		Route::post('/crm-user-order-query-conversion-save', [CRMUserRaiseQueryController::class, "save"]);
		Route::post('/crm-user-order-send-query', [CRMUserRaiseQueryController::class, "send"]);
		Route::get('/crm-user-order-query-detail', [CRMUserRaiseQueryController::class, "detail"]);
		Route::post('/gift-product-orders-ajax', [CRMGiftProductOrderController::class, "ajax"]);

		Route::post('/channel-partners-ajax', [ChannelPartnersController::class, "ajax"]);
		Route::get('/channel-partners-search-reporting-manager', [ChannelPartnersController::class, "reportingManager"]);
		Route::get('/channel-partners-search-sale-person', [ChannelPartnersController::class, "salePerson"]);
		Route::post('/channel-partners-save', [ChannelPartnersController::class, "save"]);
		Route::get('/channel-partners-detail', [ChannelPartnersController::class, "detail"]);
		Route::get('/channel-partners-search-type', [ChannelPartnersController::class, "getChannelPartnerTypes"]);

		//Exhibition

		Route::get('/my-exhibition', [ExhibitionController::class, "index"]);
		Route::post('/save-exhibition-inquiry', [ExhibitionController::class, "saveInquiry"]);
		//Exhibition

		Route::post('/marketing-request-calculation', [MarketingOrderController::class, "calculation"]);
		// Route::get('/order-search-city', [OrderController::class, "searchCity"])->name('order.search.city');
		Route::get('/marketing-request-search-channel-partner', [MarketingOrderController::class, "searchChannelPartner"]);
		Route::get('/marketing-request-channel-partner-detail', [MarketingOrderController::class, "channelPartnerDetail"]);
		Route::get('/marketing-request-search-channel-partener-type', [MarketingOrderController::class, "searchChannelPartnerTypes"]);
		Route::get('/marketing-request-search-product', [MarketingOrderController::class, "searchProduct"]);
		Route::get('/marketing-request-product-detail', [MarketingOrderController::class, "productDetail"]);
		Route::post('/marketing-request-save', [MarketingOrderController::class, "save"]);
		Route::get('/marketing-request', [MarketingOrderController::class, "ajax"]);
		Route::get('/marketing-request-detail', [MarketingOrderController::class, "detail"]);

		/////////// ------- Quotation Api Routes Start ------- ///////////
		Route::get('/search-channel-partner', [QuotationApiController::class, "searchChannelPartner"]);
		Route::post('/PostQuotCompanyList', [QuotationApiController::class, 'PostQuotCompanyList']);
		Route::post('/PostSuggestionList', [QuotationApiController::class, 'PostSuggestionList']);
		Route::post('/PostQuotCategoryList', [QuotationApiController::class, 'PostQuotCategoryList']);
		Route::get('/GetQuotCategoryList', [QuotationApiController::class, 'PostQuotCategoryList']);
		Route::post('/PostQuotGroupList', [QuotationApiController::class, 'PostQuotGroupList']);
		Route::get('/GetQuotGroupList', [QuotationApiController::class, 'PostQuotGroupList']);
		Route::post('/PostQuotSubQuotGroupList', [QuotationApiController::class, 'PostQuotSubQuotGroupList']);
		Route::get('/GetQuotSubQuotGroupList', [QuotationApiController::class, 'PostQuotSubQuotGroupList']);
		Route::post('/PostQuotItemList', [QuotationApiController::class, 'PostQuotItemList']);
		Route::get('/PostQuotTypeList', [QuotationApiController::class, 'PostQuotTypeList']);
		Route::post('/GetPlatSizeList', [QuotationApiController::class, 'GetPlatSizeList']);
		Route::post('/PostQuotationList', [QuotationApiController::class, 'PostQuotationList']);
		Route::post('/PostQuotationhistoryList', [QuotationApiController::class, 'PostQuotationhistoryList']);
		Route::post('/PostQuotBasicDetaiSave', [QuotationApiController::class, 'PostQuotBasicDetaiSave']);
		Route::post('/PostQuotRoomNBoardSave', [QuotationApiController::class, 'PostQuotRoomNBoardSave']);
		Route::post('/PostQuotRoomList', [QuotationApiController::class, 'PostQuotRoomList']);
		Route::post('/PostQuotRoomWiseBoardList', [QuotationApiController::class, 'PostQuotRoomWiseBoardList']);
		Route::post('/PostSentQuotation', [QuotationApiController::class, 'PostSentQuotation']);
		Route::post('/PostRoomNBoardRename', [QuotationApiController::class, 'PostRoomNBoardRename']);
		Route::post('/PostRoomNBoardStatus', [QuotationApiController::class, 'PostRoomNBoardStatus']);
		Route::post('/PostQuotQuartzColour', [QuotationApiController::class, 'PostQuotQuartzColour']);
		Route::post('/PostCopyRoomNBoard', [QuotationApiController::class, 'PostCopyRoomNBoard']);
		Route::get('/PostDownloadPrint', [QuotationApiController::class, 'PostDownloadPrint']);
		Route::post('/PostChangeRoomNBoardRange', [QuotationApiController::class, 'PostChangeRoomNBoardRange']);
		Route::post('/PostChangeRoomNBoardRange_New', [QuotationApiController::class, 'PostChangeRoomNBoardRange_New']);
		Route::post('/PostCopyFullQuotation', [QuotationApiController::class, 'PostCopyFullQuotation']);
		Route::post('/PostQuotClientSave', [QuotationApiController::class, 'PostQuotClientSave']);
		Route::get('/PostQuotClientList', [QuotationApiController::class, 'PostQuotClientList']);
		Route::post('/PostCheckVersion', [QuotationApiController::class, 'PostCheckVersion']);
		Route::post('/PostQuotationhistoryListLeadWise', [QuotationApiController::class, 'PostQuotationhistoryListLeadWise']);/* Meet Create Routes 12-05-2023*/
		Route::post('/PostQuotDetailItemList', [QuotationApiController::class, 'PostQuotDetailItemList']);
		Route::post('/quot-apply-discount', [QuotationApiController::class, 'discountBrandWiseSave']);
		Route::post('/quot-request-brand-wise-status', [QuotationApiController::class, 'quotationRequestBrandWiseStatusList']);
		Route::get('/get-all-company-list', [QuotationOfflineApiController::class, 'GetAllCompanyList']);
		Route::get('/get-all-category-list', [QuotationOfflineApiController::class, 'GetAllCategoryList']);
		Route::get('/get-all-group-list', [QuotationOfflineApiController::class, 'GetAllGroupList']);
		Route::get('/get-all-subgroup-list', [QuotationOfflineApiController::class, 'GetAllSubGroupList']);
		Route::get('/get-all-item-list', [QuotationOfflineApiController::class, 'GetAllItemList']);
		Route::get('/get-all-item-price-list', [QuotationOfflineApiController::class, 'GetAllItemPriceList']);
		Route::get('/get-all-quotation-list', [QuotationOfflineApiController::class, 'GetAllIQuotationList']);
		Route::get('/get-all-quotation-detail-list', [QuotationOfflineApiController::class, 'GetAllIQuotationDetailList']);
		Route::get('/get-all-user-list', [QuotationOfflineApiController::class, 'getAllUserlist']);
		Route::get('/get-all-customer-list', [QuotationOfflineApiController::class, 'getAllCustomerlist']);
		Route::get('/get-quot-item-brand-list', [QuotationOfflineApiController::class, "GetAllItemBrandList"]);
		Route::get('/quotation-data-sync-json', [QuotationOfflineApiController::class, 'GetQuotationDataSyncJson']);
		Route::post('/quotation-offline-data-save', [QuotationOfflineApiController::class, 'PostQuotOflineDataSave']);
		
		Route::post('/quot-room-wise-board-status-list', [QuotationDetailUpdateApiController::class, 'quotRoomNBoardStatusList']);
		Route::post('/save-quot-room-board-status', [QuotationDetailUpdateApiController::class, 'saveQuotBulkRoomBoardStatus']);
		

		Route::post('/quot-room-wise-board-list', [QuotationRequestController::class, 'quotRoomWiseBoardList']);
		Route::post('/save-quot-conversation-request', [QuotationRequestController::class, 'saveQuotConversationRequest']);
		/////////// ------- Target Module Api Routes Start ------- ///////////
		Route::post('/PostTargetDashboard', [TargetViewApiController::class, 'target_dashboard']);

		
		//////////// ----------- LEAD AND DEAL MODULE API ROUTES START ----------- ////////////
		
		Route::post('/crm-lead-save', [LeadApiController::class, "save"])->name('crm.lead.save');
		Route::get('/crm-lead-search-source-type', [LeadApiController::class, "searchSourceType"])->name('crm.lead.search.source.type');
		Route::get('/crm-lead-search-source', [LeadApiController::class, "searchSource"])->name('crm.lead.search.source');
		Route::get('/crm-lead-search-assign-user', [LeadApiController::class, "searchAssignedUser"])->name('crm.lead.search.assign.user');
		Route::get('/crm-lead-detail', [LeadApiController::class, "getDetail"])->name('crm.lead.detail');
		Route::post('/crm-lead-save-edit-detail', [LeadApiController::class, "saveEditDetail"])->name('crm.lead.save.edit.detail');
		Route::post('/crm-lead-phone-number-check', [LeadApiController::class, "checkPhoneNumber"]);
		Route::get('/search-reminder-time-slot', [LeadApiController::class, "searchReminderTimeSlot"]);
		Route::get('/search-reminder-time-slot', [LeadApiController::class, "searchReminderTimeSlot"]);
		Route::get('/search-time-slot', [LeadApiController::class, "searchTimeSlot"]);
		Route::get('/crm-lead-amount-summary', [LeadApiController::class, "getLeadAmountSummary"]);
		Route::get('/search-lead-deal-tag', [LeadApiController::class, "searchLeadAndDealTag"]);
		Route::post('/crm-lead-quotation-save', [LeadApiController::class, "saveLeadQuotation"])->name('crm.lead.quotation.save');
		Route::post('/crm-lead-files-save', [LeadApiController::class, "saveLeadFiles"])->name('crm.lead.files.save');
		Route::get('/crm-lead-search-files-tag', [LeadApiController::class, "searchFileTag"])->name('crm.lead.search.files.tag');
		Route::get('/crm-lead-search-channel-partner', [LeadApiController::class, "searchChannelpartner"])->name('crm.lead.search.channel.partner');

		
		Route::post('/meeting-save', [LeadMeetingApiController::class, "meetingSave"])->name('meeting.save');
		Route::get('/search-lead-meeting-title', [LeadMeetingApiController::class, "searchMeetingTitle"])->name('search.lead.meeting.title');
		Route::get('/search-lead-meeting-participants', [LeadMeetingApiController::class, "searchMeetingParticipants"])->name('search.lead.meeting.participants');
		Route::get('/search-lead-meeting-out-come', [LeadMeetingApiController::class, "searchMeetingOutCome"])->name('search.lead.meeting.out.come');
		Route::get('/search-lead-meeting-interval-time', [LeadMeetingApiController::class, "searchInterval"])->name('search.lead.meeting.interval.time');
		Route::post('/search-lead-meeting-time', [LeadMeetingApiController::class, "findMeetingTimes"])->name('search.lead.meeting.time');

		Route::get('/today-call-action', [LeadMyActionApiController::class, "myActionAjax"])->name('today.call.action');
		Route::get('/team-action', [LeadMyActionApiController::class, "teamActionAjax"])->name('team.action');
		Route::get('/search-team-employee', [LeadMyActionApiController::class, "searchTeamEmployee"])->name('search.team.employee');
		Route::get('/quotation-request-detail', [LeadMyActionApiController::class, "quotationRequestDetail"])->name('quotation.request.detail');
		Route::post('/save-discount-approve-reject', [LeadMyActionApiController::class, "saveDiscountApprovedOrReject"])->name('save.discount.approve.reject');

		
		Route::post('/crm-lead-table-ajax', [LeadController::class, "tableAjax"])->name('crm.lead.table.ajax');
		Route::post('/crm-lead-table-ajax-new', [LeadController::class, "tableAjaxNew"])->name('crm.lead.table.ajax.new');
		Route::get('/crm-lead-search-site-stage', [LeadController::class, "searchSiteStage"])->name('crm.lead.search.site.stage');
		Route::get('/crm-lead-search-site-type', [LeadController::class, "searchSiteType"])->name('crm.lead.search.site.type');
		Route::get('/crm-lead-search-bhk', [LeadController::class, "searchBHK"])->name('crm.lead.search.bhk');
		Route::get('/crm-lead-search-want-to-cover', [LeadController::class, "searchWantToCover"])->name('crm.lead.search.want.to.cover');
		Route::get('/crm-lead-search-status', [LeadController::class, "searchStatus"])->name('crm.lead.search.status');
		Route::get('/crm-lead-search-sub-status', [LeadController::class, "searchSubStatus"])->name('crm.lead.search.sub.status');
		Route::get('/crm-lead-search-competitors', [LeadController::class, "searchCompetitors"])->name('crm.lead.search.competitors');
		
		Route::post('/crm-lead-save-excel', [LeadController::class, "SaveExcelLead"])->name('crm.lead.save.excel');

		
		Route::get('/crm-lead-contact-all', [LeadController::class, "allContact"])->name('crm.lead.contact.all');
		Route::get('/crm-lead-file-all', [LeadController::class, "allFiles"])->name('crm.lead.file.all');
		Route::get('/crm-lead-update-all', [LeadController::class, "allUpdates"])->name('crm.lead.update.all');
		Route::get('/crm-lead-status-change', [LeadController::class, "changeStatus"])->name('crm.lead.status.change');
		Route::post('/crm-lead-status-save', [LeadController::class, "saveStatus"])->name('crm.lead.status.save');


		Route::post('/call-save', [LeadController::class, "callSave"])->name('call.save');
		Route::post('/task-save', [LeadController::class, "taskSave"])->name('task.save');
		Route::post('/note-save', [LeadController::class, "noteSave"])->name('note.save');

		Route::get('/search-lead-call-type', [LeadController::class, "searchLeadCallType"])->name('search.lead.call.type');
		Route::get('/search-lead-contact', [LeadController::class, "searchLeadContact"])->name('search.lead.contact');
		Route::get('/search-lead-call-out-come', [LeadController::class, "searchCallOutCome"])->name('search.lead.call.out.come');


		Route::get('/search-task-assigned-to', [LeadController::class, "searchTaskAssignedTo"])->name('search.task.assigned.to');
		Route::get('/search-lead-task-out-come', [LeadController::class, "searchTaskOutCome"])->name('search.lead.task.out.come');


		Route::get('/search-lead-advance-filter-column', [LeadFilterApiController::class, "searchAdvanceFilterColumn"])->name('search.lead.advance.filter.column');
		Route::get('/search-lead-advance-filter-condtion', [LeadFilterApiController::class, "searchAdvanceFilterCondition"])->name('search.lead.advance.filter.condtion');
		Route::get('/search-lead-advance-filter-value', [LeadFilterApiController::class, "searchFilterValue"])->name('search.lead.advance.filter.value');
		Route::get('/search-lead-advance-filter-view', [LeadFilterApiController::class, "searchAdvanceFilterView"])->name('search.lead.advance.filter.view');
		Route::post('/save-lead-advance-filter', [LeadFilterApiController::class, "saveAdvanceFilter"])->name('save.lead.advance.filter');
		Route::post('/get-detail-advance-filter', [LeadFilterApiController::class, "getDetailAdvanceFilter"])->name('get.detail.lead.advance.filter');
		Route::post('/crm-filter-view-as-default-save', [LeadFilterApiController::class, "saveViewAsDefault"])->name('crm.filter.view.as.default.save');
		Route::get('/crm-lead-advance-filter-delete', [LeadFilterApiController::class, "AdvanceFilterDelete"])->name('crm.lead.advance.filter.delete');

		Route::get('/crm-master-search-module', [MasterSearchController::class, "Getmodules"])->name('crm.master.search.module');
		Route::post('/crm-master-search-ajax', [MasterSearchController::class, "MasterSearchAjax"])->name('crm.master.search.ajax');


		/// USER ACTION ROUTES START

		Route::get('/search-reminder-time-slot', [CommanUserDetailController::class, "searchReminderTimeSlot"])->name('search.reminder.time.slot');
		Route::get('/search-time-slot', [CommanUserDetailController::class, "searchTimeSlot"])->name('search.time.slot');
		Route::get('/search-user-tag', [CommanUserDetailController::class, "searchUserTag"])->name('search.user.tag');
		Route::POST('/save-user-detail', [CommanUserDetailController::class, "saveUserDetail"])->name('save.user.detail');

		Route::get('/user-action-search-contact-tag', [UserContactController::class, "searchTag"])->name('user.action.search.contact.tag');
		Route::post('/user-action-contact-save', [UserContactController::class, "save"])->name('user.action.contact.save');
		Route::get('/user-action-contact-detail', [UserContactController::class, "detail"])->name('user.action.contact.detail');
		
		Route::get('/user-action-search-file-tag', [UserFileController::class, "searchTag"])->name('user.action.search.file.tag');
		Route::post('/user-action-file-save', [UserFileController::class, "save"])->name('user.action.file.save');
		Route::get('/user-action-file-delete', [UserFileController::class, "delete"])->name('user.action.file.delete');

		Route::post('/user-action-update-save', [UserNoteController::class, "save"])->name('user.action.update.save');

		Route::get('/user-action-search-call-type', [UserCallController::class, "searchCallType"])->name('user.action.search.call.type');
		Route::get('/user-action-search-contact', [UserCallController::class, "searchContact"])->name('user.action.search.contact');
		Route::get('/user-action-search-call-outcome-type', [UserCallController::class, "searchCallOutcomeType"])->name('user.action.search.call.outcome.type');
		Route::post('/user-action-call-save', [UserCallController::class, "save"])->name('user.action.call.save');
		Route::get('/user-action-call-detail', [UserCallController::class, "detail"])->name('user.action.call.detail');

		Route::get('/user-action-search-task-assign-to', [UserTaskController::class, "searchAssignedTo"])->name('user.action.search.task.assign');
		Route::get('/user-action-search-task-outcome-type', [UserTaskController::class, "searchTaskOutcomeType"])->name('user.action.search.task.outcome.type');
		Route::post('/user-action-task-save', [UserTaskController::class, "save"])->name('user.action.task.save');
		Route::get('/user-action-task-detail', [UserTaskController::class, "detail"])->name('user.action.task.detail');

		Route::get('/user-action-search-meeting-title', [UserMeetingController::class, "searchTitle"])->name('user.action.search.meeting.title');
		Route::get('/user-action-search-meeting-type', [UserMeetingController::class, "searchMeetingType"])->name('user.action.search.meeting.type');
		Route::get('/user-action-search-meeting-participants', [UserMeetingController::class, "searchParticipants"])->name('user.action.search.meeting.participants');
		Route::get('/user-action-search-meeting-outcome-type', [UserMeetingController::class, "searchMeetingOutcomeType"])->name('user.action.search.meeting.outcome.type');
		Route::post('/user-action-meeting-save', [UserMeetingController::class, "save"])->name('user.action.meeting.save');
		Route::get('/user-action-meeting-detail', [UserMeetingController::class, "detail"])->name('user.action.meeting.detail');

		Route::get('/user-action-open-action-all', [UserAllDetailController::class, "allOpenAction"])->name('user.action.open.action.all');
		Route::get('/user-action-close-action-all', [UserAllDetailController::class, "allCloseAction"])->name('user.action.close.action.all');
		Route::get('/user-action-contact-all', [UserAllDetailController::class, "allContact"])->name('user.action.contact.all');
		Route::get('/user-action-files-all', [UserAllDetailController::class, "allFiles"])->name('user.action.files.all');
		Route::get('/user-action-notes-all', [UserAllDetailController::class, "allUpdates"])->name('user.action.notes.all');
		/// USER ACTION ROUTES END
		
		Route::get('/crm-lead-status-question', [LeadQuestionController::class, "searchQuestion"])->name('crm.lead.status.question');
		Route::post('/save-lead-status-question-answer', [LeadQuestionController::class, "saveLeadStatusAnswer"])->name('save-lead-status-question-answer');

	});
});
