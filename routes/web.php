<?php

use App\Exports\WonDealExport;
use App\Exports\LostLeadDealExport;
use App\Exports\ChannelPartnerListExport;
use App\Exports\ChannelPartnerListWithLeadDeal;
use App\Exports\PredictionListExport;
use App\Exports\SalesPersonHierarchy;
use App\Exports\ChannelPartnerBillPendingDealListExport;
use App\Exports\SalesPersonWiseDealWithBillExport;
use App\Exports\ArchitectListExport;
use App\Exports\SalesPersonAssignArchitectWiseDealWithPointExport;
use App\Exports\RunningLeadDealExport;
use App\Exports\ElectricianListExport;
use App\Exports\MarketingLeadDealExport;
use App\Exports\MarketingLeadDealExport_jay;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\GeneralController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\Ai\AiChatContoller;
use App\Http\Controllers\DebugLogController;
use App\Http\Controllers\OrderSubController;
use App\Http\Controllers\IncentiveController;
use App\Http\Controllers\ArchitectsController;
use App\Http\Controllers\CRMInquiryController;
use App\Http\Controllers\CRMUserLogController;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\MasterMainController;
use App\Http\Controllers\MasterRollController;
use App\Http\Controllers\MoveStatusController;
use App\Http\Controllers\OrderSalesController;
use App\Http\Controllers\ProductLogController;
use App\Http\Controllers\UsersAdminController;
use App\Http\Controllers\UsersUpdateController;
use App\Http\Controllers\CRMUserOrderController;
use App\Http\Controllers\ElectriciansController;
use App\Http\Controllers\MasterSearchController;
use App\Http\Controllers\MoveAssigneeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProductGroupController;
use App\Http\Controllers\UsersAccountController;
use App\Http\Controllers\CRM\Lead\LeadController;
use App\Http\Controllers\MarketingLeadController;
use App\Http\Controllers\MasterCompanyController;
use App\Http\Controllers\UsersTeleSaleController;
use App\Http\Controllers\VersionUpdateController;
use App\Http\Controllers\API\ExhibitionController;
use App\Http\Controllers\CRMGiftProductController;
use App\Http\Controllers\CRMRewardPointController;
use App\Http\Controllers\DatabaseMasterController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\UsersMarketingController;
use App\Http\Controllers\ChannelPartnersController;
use App\Http\Controllers\CRMGiftCategoryController;
use App\Http\Controllers\CRMHelpDocumentController;
use App\Http\Controllers\MasterParameterController;
use App\Http\Controllers\UsersDispatcherController;
use App\Http\Controllers\UsersProductionController;
use App\Http\Controllers\UsersSalePersonController;
use App\Http\Controllers\UsersThirdPartyController;
use App\Http\Controllers\CreUser\CreUsersController;
use App\Http\Controllers\MasterExhibitionController;
use App\Http\Controllers\ProductInventoryController;
use App\Http\Controllers\AppSetting\BannerController;
use App\Http\Controllers\AppSetting\ReviewController;
use App\Http\Controllers\CRM\Lead\LeadCallController;
use App\Http\Controllers\CRM\Lead\LeadFileController;
use App\Http\Controllers\CRM\Lead\LeadTaskController;
use App\Http\Controllers\CRM\Reward\RewardController;
use App\Http\Controllers\CRMInquiryReportsController;
use App\Http\Controllers\CRMUserRaiseQueryController;
use App\Http\Controllers\InvoiceManagementController;
use App\Http\Controllers\Target\TargetViewController;
use App\Http\Controllers\Target\YearMasterController;
use App\Http\Controllers\UsersCompanyAdminController;
use App\Http\Controllers\CRM\Lead\LeadPointController;
use App\Http\Controllers\CRMInquiryQuestionController;
use App\Http\Controllers\CRMUserGiftProductController;
use App\Http\Controllers\MasterLocationCityController;
use App\Http\Controllers\ChannelPartnersViewController;
use App\Http\Controllers\CRM\Lead\LeadActionController;
use App\Http\Controllers\CRM\Lead\LeadUpdateController;
use App\Http\Controllers\CRMGiftProductOrderController;
use App\Http\Controllers\CRMUserHelpDocumentController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\MarketingLeadReportController;
use App\Http\Controllers\MasterLocationStateController;
use App\Http\Controllers\UsersPurchasePersonController;
use App\Http\Controllers\Whatsapp\WhatsappApiContoller;
use App\Http\Controllers\CRM\Lead\LeadContactController;
use App\Http\Controllers\CRM\Lead\LeadMeetingController;
use App\Http\Controllers\MasterSalesHierarchyController;
use App\Http\Controllers\ProductInventoryViewController;
use App\Http\Controllers\CRM\Lead\LeadDataSyncController;
use App\Http\Controllers\MasterLocationCountryController;
use App\Http\Controllers\ProductInventoryStockController;
use App\Http\Controllers\UserFilter\UserFilterController;
use App\Http\Controllers\ChannelPartnersReportsController;
use App\Http\Controllers\CRM\Lead\LeadQuotationController;
use App\Http\Controllers\CRMInquiryMoveAssigneeController;
use App\Http\Controllers\Service\WarehouseMasterContoller;
use App\Http\Controllers\TagMaster\UserTagMasterContoller;
use App\Http\Controllers\CRM\Lead\LeadTeamActionController;
use App\Http\Controllers\MasterPurchaseHierarchyController;
use App\Http\Controllers\Quotation\QuotAppMasterController;
use App\Http\Controllers\Service\ProductTagMasterContoller;
use App\Http\Controllers\AppSetting\DeviceBindingController;
use App\Http\Controllers\CRM\Accounts\LeadAccountController;
use App\Http\Controllers\CRMGiftProductOrderQueryController;
use App\Http\Controllers\CRMInquiryReportsReverseController;
use App\Http\Controllers\Dashboard\DashboardChartController;
use App\Http\Controllers\Dashboard\DashboardCountController;
use App\Http\Controllers\Quotation\QuotItemMasterController;
use App\Http\Controllers\Quotation\QuotTypeMasterController;
use App\Http\Controllers\Reception\UsersReceptionController;
use App\Http\Controllers\Target\TargetAchievementController;
use App\Http\Controllers\UsersMarketingDispatcherController;
use App\Http\Controllers\CRM\Report\LeadWorkReportController;
use App\Http\Controllers\Dashboard\DashboardReportController;
use App\Http\Controllers\Quotation\QuotationMasterController;
use App\Http\Controllers\UserActionDetail\UserCallController;
use App\Http\Controllers\UserActionDetail\UserFileController;
use App\Http\Controllers\UserActionDetail\UserNoteController;
use App\Http\Controllers\UserActionDetail\UserTaskController;
use App\Http\Controllers\Quotation\QuotAppUserMasterController;
use App\Http\Controllers\Quotation\QuotCompanyMasterController;
use App\Http\Controllers\TagMaster\LeadDealTagMasterController;
use App\Http\Controllers\Warranty\WarrantyManagementController;
use App\Http\Controllers\CRMInquiryReportsPredicationController;
use App\Http\Controllers\Quotation\QuotDiscountMasterController;
use App\Http\Controllers\UserActionDetail\UserContactController;
use App\Http\Controllers\UserActionDetail\UserMeetingController;
use App\Http\Controllers\Quotation\QuotItemGroupMasterController;
use App\Http\Controllers\Quotation\QuotItemPriceMasterController;
use App\Http\Controllers\Service\UsersServiceExecutiveController;
use App\Http\Controllers\CRM\Contact\LeadAccountContactController;
use App\Http\Controllers\Production\Warehouse\WarehouseController;
use App\Http\Controllers\Service\MasterServiceHierarchyController;
use App\Http\Controllers\UserActionDetail\UserAllDetailController;

use App\Http\Controllers\Quotation\QuotationConvertationController;
use App\Http\Controllers\Quotation\QuotationDetailMasterController;
use App\Http\Controllers\Quotation\QuotItemCategoryMasterController;
use App\Http\Controllers\Quotation\QuotItemSubGroupMasterController;
use App\Http\Controllers\UserActionDetail\CommanUserDetailController;
use App\Http\Controllers\CRM\SettingController as CRMSettingController;
use App\Http\Controllers\Marketing\OrderController as MarketingOrderController;

use App\Http\Controllers\Architects\ArchitectsController as NewArchitectsController;

use App\Http\Controllers\ChannelPartners\ChannelPartnersController as NewChannelPartnersController;

use App\Http\Controllers\Cron\InquiryPointCalculation as CronInquiryPointCalculation;

use App\Http\Controllers\AppSetting\NotificationController as PromotionalNotification;

use App\Http\Controllers\Cron\DatabaseBackupController as CronDatabaseBackupController;
use App\Http\Controllers\Electrician\ElectricianController as NewElectricianController;
use App\Http\Controllers\Dashboard\SalesOrderController as DashboardSalesOrderController;
use App\Http\Controllers\Marketing\OrderSalesController as MarketingOrderSalesController;
use App\Http\Controllers\Marketing\ProductLogController as MarketingProductLogController;
use App\Http\Controllers\MoveAssignee\MoveAssigneeController as NewMoveAssigneeController;
use App\Http\Controllers\CRM\InquiryExhibitionController as CRMInquiryExhibitionController;

use App\Http\Controllers\Cron\NotificationSchedulerController as CronNotificationScheduler;
use App\Http\Controllers\Dashboard\InquiryCalendarController as DashboardInquiryCalendarController;
use App\Http\Controllers\Marketing\ProductInventoryController as MarketingProductInventoryController;
use App\Http\Controllers\Dashboard\InquiryArchitectsController as DashboardInquiryArchitectsController;
use App\Http\Controllers\Marketing\DeliveryChallanController as MarketingOrderDeliveryChallanController;
use App\Http\Controllers\Marketing\ChallanManagementController as MarketingDeliveryChallanManagementController;
use Illuminate\Http\Request;
use App\Http\Controllers\BillController;

use App\Http\Controllers\InventorySync\InventorySyncController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */

//Login
Route::get('/', [LoginController::class, "index"])->name('login');
Route::get('/login-with-otp', [LoginController::class, "loginWithOTP"])->name('login.otp');
Route::post('login-process', [LoginController::class, "loginProcess"])->name('login.process');
Route::post('login-otp-process', [LoginController::class, "loginWithOTPProcess"])->name('login.otp.process');
Route::get('logout', [LoginController::class, "logout"])->name('logout');
Route::get('forgot-password', [ForgotPasswordController::class, "index"])->name('forgot.password');
Route::post('resed-passswrod-link', [ForgotPasswordController::class, "resetPasswordLink"])->name('forgot.password.reset');
Route::get('reset-passswrod/{token}', [ForgotPasswordController::class, "resetPassword"])->name('reset.password');
Route::post('reset-passswrod-process', [ForgotPasswordController::class, "resetPasswordProcess"])->name('reset.password.process');
Route::get('send-test-whatsapp', [WhatsappApiContoller::class, "sendtest"])->name('sendtest');



/// CRON
Route::get('cron-inquiry-point-calculation', [CronInquiryPointCalculation::class, "index"]);
Route::get('cron-database-backup', [CronDatabaseBackupController::class, "index"]);
Route::get('cron-notification-scheduler', [CronNotificationScheduler::class, "index"]);
Route::get('ai-chat', [AiChatContoller::class, "getResponse"])->name('ai.chat');
Route::get('migrate', function () {
	$msg = Artisan::call('migrate');
	return 'Database migration : ' . $msg;
});

Route::get('/computer-id', [LoginController::class, "getComputerId"]);
/// END CRON

//Route::get('/migration-process', [MigrationProcessController::class, "index"]);

Route::group(["middleware" => "auth"], function () {

	Route::get('/won_deal_export', function (Request $request) {
		$startDate = $request->start_date;
		$endDate = $request->end_date;
		return Excel::download(new WonDealExport($startDate, $endDate), 'Won Deal List.xlsx');
	})->name('won.deal.export');
	Route::get('/lost_lead_and_deal_export', function (Request $request) {
		$startDate = $request->start_date;
		$endDate = $request->end_date;
		return Excel::download(new LostLeadDealExport($startDate, $endDate), 'Lost Lead And Deal List.xlsx');
	})->name('lost.lead.and.deal.export');

	Route::get('/channelpartner-list-export', function (Request $request) {
		$startDate = $request->start_date;
		$endDate = $request->end_date;
		return Excel::download(new ChannelPartnerListExport($startDate, $endDate), 'Channelpartner List.xlsx');
	})->name('channelpartner.list.export');
	Route::get('/channelpartner-list-lead-deal-export', function (Request $request) {
		return Excel::download(new ChannelPartnerListWithLeadDeal(), 'ChannelPartner List With Lead Deal.xlsx');
	})->name('channelpartner.list.lead.deal.export');

	Route::get('/prediction-deal-list-export', function (Request $request) {
		$startDate = $request->start_date;
		$endDate = $request->end_date;
		return Excel::download(new PredictionListExport($startDate, $endDate), 'Prediction List.xlsx');
	})->name('prediction.deal.list.export');

	Route::get('/sales-person-hierarchy-deal-list-export', function (Request $request) {
		return Excel::download(new SalesPersonHierarchy(), 'Sales Person Hierarchy List.xlsx');
	})->name('sales.person.hierarchy.deal.list.export');

	Route::get('/running-lead-deal-list-export', function (Request $request) {
		return Excel::download(new RunningLeadDealExport(), 'Marketing Running Lead Deal.xlsx');
	})->name('running.lead.deal.list.export');

	Route::get('/sales-person-wise-lead-bill-list-export', function (Request $request) {
		return Excel::download(new SalesPersonWiseDealWithBillExport(), 'Sales Person Wise Lead And Bill List.xlsx');
	})->name('sales.person.wise.lead.bill.list.export');
	Route::get('/sales-person-wise-architect-lead-bill-list-export', function (Request $request) {
		return Excel::download(new SalesPersonAssignArchitectWiseDealWithPointExport(), 'Sales Person Wise architect and there Lead And Bill List.xlsx');
	})->name('sales.person.wise.architect.lead.bill.list.export');

	Route::get('/architect-list-export', function (Request $request) {
		$startDate = $request->start_date;
		$endDate = $request->end_date;
		return Excel::download(new ArchitectListExport($startDate, $endDate), 'Architect List.xlsx');
	})->name('architect.list.export');

	Route::get('/electrician-list-export', function (Request $request) {
		$startDate = $request->start_date;
		$endDate = $request->end_date;
		return Excel::download(new ElectricianListExport($startDate, $endDate), 'Electrician List.xlsx');
	})->name('electrician.list.export');

	Route::get('/marketing-lead-deal-list-export', function (Request $request) {
		$startDate = $request->start_date;
		$endDate = $request->end_date;
		// return Excel::download(new MarketingLeadDealExport(), 'Marketing Lead & Deal List.xlsx');
		return Excel::download(new MarketingLeadDealExport(), 'Marketing Lead & Deal List.xlsx');
	})->name('marketing.lead.deal.list.export');
	
	Route::get('/marketing-lead-deal-list-jay-export', function (Request $request) {
		return Excel::download(new MarketingLeadDealExport_jay(), 'Marketing Lead & Deal List - jay.xlsx');
	})->name('marketing.lead.deal.list.jay.export');


	Route::get('test-pdf', [InvoiceManagementController::class, "test"]);

	Route::get('/version-update', [VersionUpdateController::class, "index"]);

	/// START GENERAL FUNCTIONS

	// Route::get('/create-architect-using-exhibition', [ExhibitionController::class, "createArchitectUsingExhibition"]);

	Route::get('/search-country', [GeneralController::class, "searchCountry"])->name('search.country');
	Route::get('/search-city', [GeneralController::class, "searchCity"])->name('search.city');
	Route::get('/search-city-state-country', [GeneralController::class, "searchCityStateCountry"])->name('search.city.state.country');
	Route::get('/search-state-from-country', [GeneralController::class, "searchStateFromCountry"])->name('search.state.from.country');
	Route::get('/search-city-from-state', [GeneralController::class, "searchCityFromState"])->name('search.city.from.state');
	Route::get('/search-courier', [GeneralController::class, "searchCourier"])->name('search.courier');
	Route::get('/auto-notification-scheduler', [GeneralController::class, "notificationScheduler"])->name('auto.notification.scheduler');
	Route::get('/lead-account-save-general', [GeneralController::class, "LeadAccountSave"])->name('lead.account.save.general');

	/// END GENERAL FUNCTIONS

	Route::get('/dashboard', [DashboardController::class, "index"])->name('dashboard');

	/// START DASHBOARD INQUIRY CALENDER

	Route::get('/dashboard-inquiry-calender-search-user', [DashboardInquiryCalendarController::class, "searchUser"])->name('dashboard.inquiry.calender.search.user');

	Route::get('/dashboard-inquiry-calender-data', [DashboardInquiryCalendarController::class, "calenderData"])->name('dashboard.inquiry.calender.data');

	/// END DASHBOARD INQUIRY CALENDER

	/// START DASHBOARD SALES ORDER COUNT

	Route::post('/dashboard-sale-order-count-data', [DashboardSalesOrderController::class, "saleOrdercount"])->name('dashboard.sale.order.count.data');
	Route::get('/dashboard-sale-order-count-search-channel-partner', [DashboardSalesOrderController::class, "searchChannelPartner"])->name('dashboard.sale.order.count.search.channel.partner');
	Route::get('/dashboard-sale-order-count-search-user', [DashboardSalesOrderController::class, "searchUser"])->name('dashboard.sale.order.count.search.user');

	/// END DASHBOARD SALES ORDER COUNT

	/// START DASHBOARD INQUIRY ARCHITECTS COUNT

	Route::get('/dashboard-inquiry-architects-count-search-user', [DashboardInquiryArchitectsController::class, "searchUser"])->name('dashboard.inquiry.architects.count.search.user');

	Route::post('/dashboard-inquiry-architects-count-data', [DashboardInquiryArchitectsController::class, "inquiryCount"])->name('dashboard.inquiry.architects.count.data');

	/// END DASHBOARD INQUIRY ARCHITECTS COUNT

	Route::get('/profile', [DashboardController::class, "profile"])->name('profile');
	Route::get('/change-password', [DashboardController::class, "changePassword"])->name('changepassword');
	Route::get('/change-password-otp', [DashboardController::class, "sendOTPForChangePassword"])->name('changepassword.otp');
	Route::post('/do-change-password', [DashboardController::class, "doChangePassword"])->name('do.changepassword');
	Route::get('/dashboard-search-channel-partner', [DashboardController::class, "searchChannelPartner"])->name('dashboard.search.channel.partner');
	Route::get('/dashboard-search-sale-user', [DashboardController::class, "searchUser"])->name('dashboard.search.sale.user');


	Route::post('/dashboard-data-count', [DashboardCountController::class, "dashboardCount"])->name('dashboard.data.count');

	Route::get('/dashboard-search-state', [DashboardReportController::class, "searchState"])->name('dashboard.search.state');
	Route::get('/dashboard-search-city', [DashboardReportController::class, "searchCity"])->name('dashboard.search.city');

	Route::get('/dashboard-get-bar-chart-count', [DashboardChartController::class, "barChartCount"])->name('dashboard.get.bar.chart.count');
	Route::get('/dashboard-get-bar-chart-lead', [DashboardChartController::class, "barChartLead"])->name('dashboard.get.bar.chart.lead');

	Route::post('/dashboard-order-report-ajax', [DashboardReportController::class, "dashboardOrderReport"])->name('dashboard.order.report.ajax');
	Route::post('/dashboard-sale-per-report-ajax', [DashboardReportController::class, "dashboardSalePerReport"])->name('dashboard.sale.per.report.ajax');
	Route::post('/dashboard-sale-executive-report-ajax', [DashboardReportController::class, "dashboardSaleExecutiveReport"])->name('dashboard.sale.executive.report.ajax');
	Route::post('/dashboard-sale-overview-per-entity-ajax', [DashboardReportController::class, "dashboardSalesOverviewPerEntity"])->name('dashboard.sale.overview.per.entity.ajax');
	Route::post('/dashboard-sale-overview-ajax', [DashboardReportController::class, "dashboardSalesOverviewajax"])->name('dashboard.sale.overview.ajax');

	/// START USER GENERAL FUNCTIONS

	Route::get('/users-search-state', [UsersController::class, "searchState"])->name('users.search.state');
	Route::get('/users-search-city', [UsersController::class, "searchCity"])->name('users.search.city');
	Route::get('/users-search-company', [UsersController::class, "searchCompany"])->name('users.search.company');
	Route::get('/users-search-saleperson-type', [UsersController::class, "searchSalePersonType"])->name('users.search.saleperson.type');
	Route::get('/users-search-purchaseperson-type', [UsersController::class, "searchPurchasePersonType"])->name('users.search.purcheperson.type');
	Route::get('/users-state-cities', [UsersController::class, "stateCities"])->name('users.state.cities');
	Route::get('/users-reporting-manager-sales', [UsersController::class, "salesReportingManager"])->name('users.reporting.manager');
	Route::get('/users-reporting-manager-purchase', [UsersController::class, "purchaseReportingManager"])->name('users.reporting.manager.purchase');
	Route::get('/users-search-state-cities', [UsersController::class, "searchStateCities"])->name('users.search.state.cities');
	Route::post('/user-phone-number-check', [UsersController::class, "checkUserPhoneNumberAndEmail"])->name('user.phone.number.check');

	// AXONE WORK START
	Route::get('/users-search-service-executive-type', [UsersController::class, "searchServiceExecutiveType"])->name('users.search.service.executive.type');
	Route::get('/users-search-service-executive-reporting-manager', [UsersController::class, "searchServiceExecutiveReportingManager"])->name('users.search.service.executive.reporting.manager');
	// AXONE WORK END

	Route::post('/users-save', [UsersController::class, "save"])->name('users.save');
	Route::get('/users-detail', [UsersController::class, "detail"])->name('users.detail');

	/// END USER GENERAL FUNCTIONS

	/// START USERS - ADMIN

	Route::get('/users-admin', [UsersAdminController::class, "index"])->name('users.admin');
	Route::post('/users-admin-ajax', [UsersAdminController::class, "ajax"])->name('users.admin.ajax');
	Route::get('/users-admin-export', [UsersAdminController::class, "export"])->name('users.admin.export');

	/// END USERS - ADMIN

	/// START USERS - COMPANY ADMIN

	Route::get('/users-company-admin', [UsersCompanyAdminController::class, "index"])->name('users.company.admin');
	Route::post('/users-company-admin-ajax', [UsersCompanyAdminController::class, "ajax"])->name('users.company.admin.ajax');
	Route::get('/users-company-admin-export', [UsersCompanyAdminController::class, "export"])->name('users.company.admin.export');

	/// END USERS - COMPANY ADMIN

	/// START USERS - SALE PERSON

	Route::get('/users-sale-person', [UsersSalePersonController::class, "index"])->name('users.sale.person');
	Route::post('/users-sale-person-ajax', [UsersSalePersonController::class, "ajax"])->name('users.sale.person.ajax');
	Route::get('/users-sale-person-export', [UsersSalePersonController::class, "export"])->name('users.sale.person.export');

	/// END USERS - SALE PERSON

	/// START USERS - PURCHAE PERSON

	Route::get('/users-purchase-person', [UsersPurchasePersonController::class, "index"])->name('users.purchase.person');
	Route::post('/users-purchase-person-ajax', [UsersPurchasePersonController::class, "ajax"])->name('users.purchase.person.ajax');
	Route::get('/users-purchase-person-export', [UsersPurchasePersonController::class, "export"])->name('users.purchase.person.export');

	/// END USERS - PURCHAE PERSON

	/// START USERS - ACCOUNT

	Route::get('/users-account', [UsersAccountController::class, "index"])->name('users.account');
	Route::post('/users-account-ajax', [UsersAccountController::class, "ajax"])->name('users.account.ajax');
	Route::get('/users-account-export', [UsersAccountController::class, "export"])->name('users.account.export');

	/// END USERS - ACCOUNT

	// Users - Dispatcher User

	Route::get('/users-dispatcher', [UsersDispatcherController::class, "index"])->name('users.dispatcher');
	Route::post('/users-dispatcher-ajax', [UsersDispatcherController::class, "ajax"])->name('users.dispatcher.ajax');
	Route::get('/users-dispatcher-export', [UsersDispatcherController::class, "export"])->name('users.dispatcher.export');

	// Users - Dispatcher User

	// Users - Production User

	Route::get('/users-production', [UsersProductionController::class, "index"])->name('users.production');
	Route::post('/users-production-ajax', [UsersProductionController::class, "ajax"])->name('users.production.ajax');
	Route::get('/users-production-export', [UsersProductionController::class, "export"])->name('users.production.export');

	// Users - Production User

	// START USERS - MARKETING
	Route::get('/users-marketing', [UsersMarketingController::class, "index"])->name('users.marketing');
	Route::post('/users-marketing-ajax', [UsersMarketingController::class, "ajax"])->name('users.marketing.ajax');
	Route::get('/users-marketing-export', [UsersMarketingController::class, "export"])->name('users.marketing.export');

	// END  USERS -  MARKETING

	// START USERS - MARKETING - DISPATCHER
	Route::get('/users-marketing-dispatcher', [UsersMarketingDispatcherController::class, "index"])->name('users.marketing.dispatcher');
	Route::post('/users-marketing-dispatcher-ajax', [UsersMarketingDispatcherController::class, "ajax"])->name('users.marketing.dispatcher.ajax');
	Route::get('/users-marketing-dispatcher-export', [UsersMarketingDispatcherController::class, "export"])->name('users.marketing.dispatcher.export');

	// END  USERS -  MARKETING - DISPATCHER

	// START USERS - THIRD PARTY
	Route::get('/users-thirdparty', [UsersThirdPartyController::class, "index"])->name('users.thirdparty');
	Route::post('/users-thirdparty-ajax', [UsersThirdPartyController::class, "ajax"])->name('users.thirdparty.ajax');
	Route::get('/users-thirdparty-export', [UsersThirdPartyController::class, "export"])->name('users.thirdparty.export');

	// END  USERS -  THIRD PARTY

	// START USERS - TALE SALE
	Route::get('/users-tele-sale', [UsersTeleSaleController::class, "index"])->name('users.tele.sale');
	Route::post('/users-tele-sale-ajax', [UsersTeleSaleController::class, "ajax"])->name('users.tele.sale.ajax');
	Route::get('/users-tele-sale-export', [UsersTeleSaleController::class, "export"])->name('users.tele.sale.export');
	// END  USERS -  TALE SALE

	Route::get('/get-user-update', [UsersUpdateController::class, "detail"])->name('users.update.detail');
	Route::post('/get-user-save-update', [UsersUpdateController::class, "save"])->name('users.update.save');
	Route::get('/get-user-update-seen', [UsersUpdateController::class, "updateSeen"])->name('users.update.seen');

	/// Channel Partners

	Route::post('/channel-partners-ajax', [ChannelPartnersController::class, "ajax"])->name('channel.partners.ajax');
	Route::post('/channel-partners-discount-ajax', [ChannelPartnersController::class, "discountAjax"])->name('channel.partners.discount.ajax');
	Route::post('/channel-partners-type-discount-ajax', [ChannelPartnersController::class, "cptDiscountAjax"])->name('channel.partners.type.discount.ajax');
	Route::get('/channel-partners-discount-search-product-group', [ChannelPartnersController::class, "searchProductGroup"])->name('channel.partners.discount.search.product.group');

	Route::post('/channel-partners-discount-save', [ChannelPartnersController::class, "discountSave"])->name('channel.partners.discount.save');

	Route::post('/channel-partners-discount-save-all', [ChannelPartnersController::class, "discountSaveAll"])->name('channel.partners.discount.save.all');
	Route::post('/channel-partners-discount-cpt-save-all', [ChannelPartnersController::class, "discountCPTSaveAll"])->name('channel.partners.discount.cpt.save.all');

	Route::get('/channel-partners-stockist', [ChannelPartnersController::class, "stockist"])->name('channel.partners.stockist');
	Route::get('/channel-partners-adm', [ChannelPartnersController::class, "adm"])->name('channel.partners.adm');
	Route::get('/channel-partners-apm', [ChannelPartnersController::class, "apm"])->name('channel.partners.apm');
	Route::get('/channel-partners-ad', [ChannelPartnersController::class, "ad"])->name('channel.partners.ad');
	Route::get('/channel-partners-retailer', [ChannelPartnersController::class, "retailer"])->name('channel.partners.retailer');
	Route::get('/channel-partners-request', [ChannelPartnersController::class, "request"])->name('channel.partners.request');

	Route::get('/channel-partners-search-state', [ChannelPartnersController::class, "searchState"])->name('channel.partners.search.state');
	Route::get('/channel-partners-search-city', [ChannelPartnersController::class, "searchCity"])->name('channel.partners.search.city');

	Route::get('/channel-partners-search-reporting-manager', [ChannelPartnersController::class, "reportingManager"])->name('channel.partners.search.reporting.manager');
	Route::get('/channel-partners-search-sale-person', [ChannelPartnersController::class, "salePerson"])->name('channel.partners.search.sale.person');

	Route::get('/channel-partners-city-detail', [ChannelPartnersController::class, "cityDetail"])->name('channel.partners.city.detail');

	Route::post('/channel-partners-save', [ChannelPartnersController::class, "save"])->name('channel.partners.save');
	Route::get('/channel-partners-detail', [ChannelPartnersController::class, "detail"])->name('channel.partners.detail');
	Route::get('/channel-partners-export', [ChannelPartnersController::class, "export"])->name('channel.partners.export');

	Route::get('/channel-partners-stockist-view', [ChannelPartnersViewController::class, "stockist"])->name('channel.partners.stockist.view');
	Route::get('/channel-partners-adm-view', [ChannelPartnersViewController::class, "adm"])->name('channel.partners.adm.view');
	Route::get('/channel-partners-apm-view', [ChannelPartnersViewController::class, "apm"])->name('channel.partners.apm.view');
	Route::get('/channel-partners-ad-view', [ChannelPartnersViewController::class, "ad"])->name('channel.partners.ad.view');
	Route::get('/channel-partners-retailer-view', [ChannelPartnersViewController::class, "retailer"])->name('channel.partners.retailer.view');

	Route::post('/channel-partners-ajax-view', [ChannelPartnersViewController::class, "ajax"])->name('channel.partners.ajax.view');

	Route::get('/channel-partners-detail-view', [ChannelPartnersViewController::class, "detail"])->name('channel.partners.detail.view');
	Route::get('/channel-partners-export-view', [ChannelPartnersViewController::class, "export"])->name('channel.partners.export.view');

	Route::get('/channel-partners-afm', [ChannelPartnersController::class, "afm"])->name('channel.partners.afm');
	Route::get('/channel-partners-afm-view', [ChannelPartnersViewController::class, "afm"])->name('channel.partners.afm.view');


	/// Channel Partners

	/// START NEW CHANNEL PARTNERS ROUTES

	Route::get('/new-channel-partners', [NewChannelPartnersController::class, "index"])->name('new.channel.partners.index');
	Route::post('/new-channel-partners-save', [NewChannelPartnersController::class, "save"])->name('new.channel.partners.save');
	// Route::get('/new-channel-partners-table', [NewChannelPartnersController::class, "table"])->name('new.channel.partners.table');
	Route::get('/new-channel-partners-get-detail', [NewChannelPartnersController::class, "getDetail"])->name('new.channel.partners.get.detail');
	Route::get('/new-channel-partners-export', [NewChannelPartnersController::class, "export"])->name('new.channel.partners.export');
	Route::get('/new-channel-partners-discount-search-product-group', [NewChannelPartnersController::class, "searchProductGroup"])->name('new.channel.partners.discount.search.product.group');
	Route::post('/new-channel-partners-discount-ajax', [NewChannelPartnersController::class, "discountAjax"])->name('new.channel.partners.discount.ajax');
	Route::post('/new-channel-partners-discount-cpt-save-all', [NewChannelPartnersController::class, "discountCPTSaveAll"])->name('new.channel.partners.discount.cpt.save.all');
	Route::post('/new-channel-partners-type-discount-ajax', [NewChannelPartnersController::class, "cptDiscountAjax"])->name('new.channel.partners.type.discount.ajax');
	Route::post('/new-channel-partners-list-ajax', [NewChannelPartnersController::class, "getListAjax"])->name('new.channel.partners.detail.list.ajax');
	Route::get('/new-channel-partners-search-sale-person', [NewChannelPartnersController::class, "salePerson"])->name('new.channel.partners.search.sale.person');
	Route::get('/new-channel-partners-detail', [NewChannelPartnersController::class, "detail"])->name('new.channel.partners.detail');


	/// END NEW CHANNEL PARTNERS ROUTES

	/// START CHANNEL PARTNERS REPORTS

	Route::get('/channel-partners-reports', [ChannelPartnersReportsController::class, "index"])->name('channel.partners.reports');
	Route::get('/channel-partners-reports-search-sale-person', [ChannelPartnersReportsController::class, "searchSalePerson"])->name('channel.partners.reports.search.sale.person');
	Route::post('/channel-partners-reports-list-type', [ChannelPartnersReportsController::class, "typeList"])->name('channel.partners.reports.list.type');
	Route::post('/channel-partners-reports-list', [ChannelPartnersReportsController::class, "list"])->name('channel.partners.reports.list');

	/// END CHANNEL PARTNERS REPORTS

	/// START ADD ORDER

	Route::get('/order-add', [OrderController::class, "add"])->name('order.add');
	Route::get('/order-pdf', [OrderController::class, "testPDF"])->name('order.pdf');
	Route::post('/order-calculation', [OrderController::class, "calculation"])->name('order.calculation');
	// Route::get('/order-search-city', [OrderController::class, "searchCity"])->name('order.search.city');
	Route::get('/order-search-channel-partner', [OrderController::class, "searchChannelPartner"])->name('order.search.channel.partner');
	Route::get('/order-channel-partner-detail', [OrderController::class, "channelPartnerDetail"])->name('order.channel.partner.detail');
	Route::get('/order-search-product', [OrderController::class, "searchProduct"])->name('order.search.product');
	Route::get('/order-product-detail', [OrderController::class, "productDetail"])->name('order.product.detail');
	Route::post('/orders-save', [OrderController::class, "save"])->name('order.save');
	Route::get('/orders-cancel', [OrderController::class, "cancel"])->name('order.cancel');
	Route::post('/orders-created-save', [OrderController::class, "createdSave"])->name('order.created.save');

	/// END ADD ORDER

	// ORDERS
	Route::get('/orders', [OrderController::class, "index"])->name('orders');
	Route::post('/orders-ajax', [OrderController::class, "ajax"])->name('orders.ajax');
	Route::get('/orders-detail', [OrderController::class, "detail"])->name('order.detail');
	Route::get('/order-invoice-list', [OrderController::class, "invoiceList"])->name('orders.invoice.list');
	Route::post('/order-invoice-ajax', [OrderController::class, "invoiceListAjax"])->name('orders.invoice.list.ajax');
	Route::get('/orders-invoice-detail', [OrderController::class, "invoiceDetail"])->name('orders.invoice.detail');
	Route::post('/order-export', [OrderController::class, "export"])->name('order.export');

	// END ORDERS

	/// START SUB ORDERS
	Route::get('/sub-orders-all', [OrderSubController::class, "all"])->name('orders.sub.all');
	Route::get('/sub-orders-asm', [OrderSubController::class, "asm"])->name('orders.sub.asm');
	Route::get('/sub-orders-adm', [OrderSubController::class, "adm"])->name('orders.sub.adm');
	Route::get('/sub-orders-apm', [OrderSubController::class, "apm"])->name('orders.sub.apm');
	Route::get('/sub-orders-ad', [OrderSubController::class, "ad"])->name('orders.sub.ad');
	Route::get('/sub-orders-retailer', [OrderSubController::class, "retailer"])->name('orders.sub.retailer');
	Route::get('/sub-orders-afm', [OrderSubController::class, "afm"])->name('orders.sub.afm');
	Route::get('/search-channel-partenertype', [OrderSubController::class, "searchChannelPartenerType"])->name('search.channel.partenertype');

	Route::post('/sub-orders-ajax', [OrderSubController::class, "ajax"])->name('orders.sub.ajax');
	Route::get('/sub-orders-detail', [OrderSubController::class, "detail"])->name('order.sub.detail');
	Route::get('/sub-order-invoice-list', [OrderSubController::class, "invoiceList"])->name('order.sub.invoice');
	Route::post('/sub-order-invoice-list-ajax', [OrderSubController::class, "invoiceListAjax"])->name('order.sub.invoice.ajax');
	Route::get('/sub-order-invoice-detail', [OrderSubController::class, "invoiceDetail"])->name('order.sub.invoice.detail');

	/// END SUB ORDERS

	///START SALES ORDER

	Route::get('/orders-sales', [OrderSalesController::class, "index"])->name('orders.sales');
	Route::post('/orders-sales-ajax', [OrderSalesController::class, "ajax"])->name('orders.sales.ajax');
	Route::get('/orders-sales-detail', [OrderSalesController::class, "detail"])->name('orders.sales.detail');
	Route::get('/orders-cancel-detail', [OrderSalesController::class, "cancelDetail"])->name('orders.sales.cancel.detail');
	Route::get('/orders-partial-cancel', [OrderSalesController::class, "cancel"])->name('order.partial.cancel');

	Route::post('/orders-sales-invoice-calculation', [OrderSalesController::class, "calculation"])->name('orders.sales.invoice.calculation');
	Route::post('/orders-sales-invoice-save', [OrderSalesController::class, "invoiceSave"])->name('orders.sales.invoice.save');
	Route::get('/orders-sales-invoice-list', [OrderSalesController::class, "invoiceList"])->name('orders.sales.invoice.list');
	Route::post('/orders-sales-invoice-list-ajax', [OrderSalesController::class, "invoiceListAjax"])->name('orders.sales.invoice.list.ajax');
	Route::get('/orders-sales-invoice-detail', [OrderSalesController::class, "invoiceDetail"])->name('orders.sales.invoice.detail');
	Route::post('/orders-sales-invoice-file-update', [OrderSalesController::class, "updateFile"])->name('orders.sales.invoice.file.update');

	///END SALES ORDER

	/// START INVOICE

	Route::get('/invoice-list', [InvoiceController::class, "index"])->name('invoice.list');
	Route::post('/invoice-list-ajax', [InvoiceController::class, "ajax"])->name('invoice.list.ajax');
	Route::get('/invoice-detail', [InvoiceController::class, "detail"])->name('invoice.detail');

	/// END INVOICE

	/// START INVOICE MANAGEMENT

	Route::get('/invoice-raised', [InvoiceManagementController::class, "raised"])->name('invoice.raised');
	Route::post('/invoice-raised-ajax', [InvoiceManagementController::class, "raisedAjax"])->name('invoice.raised.ajax');
	Route::post('/invoice-raised-invoice-calculation', [InvoiceManagementController::class, "calculation"])->name('invoice.raised.invoice.calculation');

	//Route::get('/invoice-mark-as-packed', [InvoiceManagementController::class, "markAsPacked"])->name('invoice.markaspacked');
	Route::post('/invoice-mark-as-packed', [InvoiceManagementController::class, "markAsPacked"])->name('invoice.markaspacked');

	Route::get('/invoice-packed', [InvoiceManagementController::class, "packed"])->name('invoice.packed');
	Route::post('/invoice-packed-ajax', [InvoiceManagementController::class, "packedAjax"])->name('invoice.packed.ajax');
	Route::get('/invoice-search-courier', [InvoiceManagementController::class, "searchCourier"])->name('invoice.search.courier');
	Route::post('/invoice-mark-as-dispatch', [InvoiceManagementController::class, "markAsDispatch"])->name('invoice.markasdispatch');

	Route::get('/invoice-dispatched', [InvoiceManagementController::class, "dispatched"])->name('invoice.dispatched');
	Route::post('/invoice-dispatched-ajax', [InvoiceManagementController::class, "dispatchedAjax"])->name('invoice.dispatched.ajax');

	Route::get('/invoice-mark-as-recieved', [InvoiceManagementController::class, "markAsRecieved"])->name('invoice.markasrecieved');

	Route::get('/invoice-recieved', [InvoiceManagementController::class, "recieved"])->name('invoice.recieved');
	Route::post('/invoice-recieved-ajax', [InvoiceManagementController::class, "recievedAjax"])->name('invoice.recieved.ajax');

	Route::get('/invoice-cancelled', [InvoiceManagementController::class, "cancelled"])->name('invoice.cancelled');
	Route::post('/invoice-cancelled-ajax', [InvoiceManagementController::class, "cancelledAjax"])->name('invoice.cancelled.ajax');

	Route::get('/invoice-detail-for-dispatcher', [InvoiceManagementController::class, "detail"])->name('invoice.detail.dispatcher');

	/// END INVOICE MANAGEMENT

	/// START ARCHITECT

	//Route::get('/architects-non-prime', [ArchitectsController::class, "nonPrime"])->name('architects.non.prime');
	Route::get('/architects', [ArchitectsController::class, "prime"])->name('architects.prime');
	Route::post('/architects-ajax', [ArchitectsController::class, "ajax"])->name('architects.ajax');

	Route::get('/architects-search-sale-person', [ArchitectsController::class, "searchSalePerson"])->name('architects.search.sale.person');
	Route::post('/architect-save', [ArchitectsController::class, "save"])->name('architect.save');
	Route::post('/architect-change-category', [ArchitectsController::class, "saveCategory"])->name('architect.change.category');

	Route::get('/architect-detail', [ArchitectsController::class, "detail"])->name('architect.detail');
	Route::get('/architect-search-user', [ArchitectsController::class, "searchUser"])->name('architect.search.user');

	Route::get('/architects-export', [ArchitectsController::class, "export"])->name('architects.export');
	Route::post('/architect-point-log', [ArchitectsController::class, "pointLog"])->name('architect.point.log');
	Route::post('/architect-inquiry-log', [ArchitectsController::class, "inquiryLog"])->name('architect.inquiry.log');

	/// END ARCHITECT


	//// NEW ARCHITECTS ROUTES
	Route::get('/new-architects', [NewArchitectsController::class, "index"])->name('new.architects.index');
	Route::get('/new-architects-table', [NewArchitectsController::class, "table"])->name('new.architects.table');
	Route::post('/new-architects-ajax', [NewArchitectsController::class, "ajax"])->name('new.architects.ajax');
	Route::post('/new-architects-save', [NewArchitectsController::class, "save"])->name('new.architects.save');
	Route::post('/new-architects-edit-save', [NewArchitectsController::class, "saveEditArchitect"])->name('new.architects.edit.save');
	Route::get('/new-architects-detail', [NewArchitectsController::class, "detail"])->name('new.architects.detail');
	Route::get('/new-architects-detail-list', [NewArchitectsController::class, "getList"])->name('new.architects.detail.list');
	Route::post('/new-architects-detail-list-ajax', [NewArchitectsController::class, "getListAjax"])->name('new.architects.detail.list.ajax');
	Route::get('/new-architects-export', [NewArchitectsController::class, "export"])->name('new.architects.export');
	Route::get('/new-architects-search-sale-person', [NewArchitectsController::class, "searchSalePerson"])->name('new.architects.search.sale.person');
	Route::post('/new-architects-change-category', [NewArchitectsController::class, "saveCategory"])->name('new.architects.change.category');
	Route::get('/new-architects-search-user', [NewArchitectsController::class, "searchUser"])->name('new.architects.search.user');
	Route::post('/new-architects-inquiry-log', [NewArchitectsController::class, "inquiryLog"])->name('new.architects.inquiry.log');
	Route::post('/new-architects-point-log', [NewArchitectsController::class, "pointLog"])->name('new.architects.point.log');
	Route::get('/new-architects-get-detail', [NewArchitectsController::class, "getDetail"])->name('new.architects.get.detail');
	Route::get('/new-architects-search-source-type', [NewArchitectsController::class, "searchSourceType"])->name('new.architects.search.source.type');
	Route::get('/new-architects-search-source', [NewArchitectsController::class, "searchSource"])->name('new.architects.search.source');
	Route::get('/new-architects-search-status', [NewArchitectsController::class, "searchStatus"])->name('new.architects.search.status');
	Route::get('/new-architects-add-default-contact', [NewArchitectsController::class, "addContactArcClient"])->name('new.architects.add.default.contact');
	Route::get('/get-architect-user', [NewArchitectsController::class, "usergetArchitect"])->name('get.architect.user');
	//// NEW ARCHITECTS ROUTES

	/// START ELECTRICIANS

	//Route::get('/electricians-non-prime', [ElectriciansController::class, "nonPrime"])->name('electricians.non.prime');
	Route::get('/electricians', [ElectriciansController::class, "prime"])->name('electricians.prime');
	Route::post('/electricians-ajax', [ElectriciansController::class, "ajax"])->name('electricians.ajax');
	Route::get('/electricians-search-sale-person', [ElectriciansController::class, "searchSalePerson"])->name('electricians.search.sale.person');
	Route::post('/electrician-save', [ElectriciansController::class, "save"])->name('electrician.save');
	Route::get('/electrician-detail', [ElectriciansController::class, "detail"])->name('electrician.detail');
	Route::get('/electricians-export', [ElectriciansController::class, "export"])->name('electricians.export');
	Route::post('/electricians-point-log', [ElectriciansController::class, "pointLog"])->name('electricians.point.log');
	Route::post('/electricians-inquiry-log', [ElectriciansController::class, "inquiryLog"])->name('electricians.inquiry.log');

	/// END ELECTRICIANS

	/// NEW ELECTRICIAN ROUTES
	Route::get('/get-electrician-user', [NewElectricianController::class, "usergetElectrician"])->name('get.electricians.user');
	Route::get('/new-electricians', [NewElectricianController::class, "index"])->name('new.electricians.index');
	Route::get('/new-electricians-table', [NewElectricianController::class, "table"])->name('new.electricians.table');
	Route::post('/new-electricians-ajax', [NewElectricianController::class, "ajax"])->name('new.electricians.ajax');
	Route::get('/new-electricians-search-sale-person', [NewElectricianController::class, "searchSalePerson"])->name('new.electricians.search.sale.person');
	Route::post('/new-electricians-save', [NewElectricianController::class, "save"])->name('new.electricians.save');
	Route::get('/new-electricians-detail', [NewElectricianController::class, "detail"])->name('new.electricians.detail');
	Route::get('/new-electricians-export', [NewElectricianController::class, "export"])->name('new.electricians.export');
	Route::post('/new-electricians-point-log', [NewElectricianController::class, "pointLog"])->name('new.electricians.point.log');
	Route::post('/new-electricians-inquiry-log', [NewElectricianController::class, "inquiryLog"])->name('new.electricians.inquiry.log');
	Route::get('/new-electricians-get-detail', [NewElectricianController::class, "getDetail"])->name('new.electricians.get.detail');
	Route::get('/new-electricians-detail-list', [NewElectricianController::class, "getList"])->name('new.electricians.detail.list');
	Route::post('/new-electricians-detail-list-ajax', [NewElectricianController::class, "getListAjax"])->name('new.electricians.detail.list.ajax');
	Route::get('/new-electricians-search-status', [NewElectricianController::class, "searchStatus"])->name('new.electricians.search.status');
	Route::get('/new-electricians-add-default-contact', [NewElectricianController::class, "addContactEleClient"])->name('new.electricians.add.default.contact');
	/// NEW ELECTRICIAN ROUTES


	/// USER ACTION ROUTES START
	Route::get('/user-action-search-contact-tag', [UserContactController::class, "searchTag"])->name('user.action.search.contact.tag');
	Route::post('/user-action-contact-save', [UserContactController::class, "save"])->name('user.action.contact.save');
	Route::get('/user-action-contact-detail', [UserContactController::class, "detail"])->name('user.action.contact.detail');

	Route::post('/user-action-file-save', [UserFileController::class, "save"])->name('user.action.file.save');
	Route::get('/user-action-file-delete', [UserFileController::class, "delete"])->name('user.action.file.delete');

	Route::post('/user-action-update-save', [UserNoteController::class, "save"])->name('user.action.update.save');

	Route::get('/user-action-search-call-type', [UserCallController::class, "searchCallType"])->name('user.action.search.call.type');
	Route::get('/user-action-search-contact', [UserCallController::class, "searchContact"])->name('user.action.search.contact');
	Route::post('/user-action-call-save', [UserCallController::class, "save"])->name('user.action.call.save');
	Route::get('/user-action-call-detail', [UserCallController::class, "detail"])->name('user.action.call.detail');
	Route::get('/user-action-search-call-outcome-type', [UserCallController::class, "searchCallOutcomeType"])->name('user.action.search.call.outcome.type');

	Route::get('/user-action-search-task-assign-to', [UserTaskController::class, "searchAssignedTo"])->name('user.action.search.task.assign');
	Route::post('/user-action-task-save', [UserTaskController::class, "save"])->name('user.action.task.save');
	Route::get('/user-action-task-detail', [UserTaskController::class, "detail"])->name('user.action.task.detail');
	Route::get('/user-action-search-task-outcome-type', [UserTaskController::class, "searchTaskOutcomeType"])->name('user.action.search.task.outcome.type');

	Route::get('/user-action-search-meeting-title', [UserMeetingController::class, "searchTitle"])->name('user.action.search.meeting.title');
	Route::get('/user-action-search-meeting-type', [UserMeetingController::class, "searchMeetingType"])->name('user.action.search.meeting.type');
	Route::get('/user-action-search-meeting-participants', [UserMeetingController::class, "searchParticipants"])->name('user.action.search.meeting.participants');
	Route::post('/user-action-meeting-save', [UserMeetingController::class, "save"])->name('user.action.meeting.save');
	Route::get('/user-action-meeting-detail', [UserMeetingController::class, "detail"])->name('user.action.meeting.detail');
	Route::get('/user-action-search-meeting-outcome-type', [UserMeetingController::class, "searchMeetingOutcomeType"])->name('user.action.search.meeting.outcome.type');

	Route::get('/user-action-open-action-all', [UserAllDetailController::class, "allOpenAction"])->name('user.action.open.action.all');
	Route::get('/user-action-close-action-all', [UserAllDetailController::class, "allCloseAction"])->name('user.action.close.action.all');
	Route::get('/user-action-contact-all', [UserAllDetailController::class, "allContact"])->name('user.action.contact.all');
	Route::get('/user-action-files-all', [UserAllDetailController::class, "allFiles"])->name('user.action.files.all');
	Route::get('/user-action-notes-all', [UserAllDetailController::class, "allUpdates"])->name('user.action.notes.all');

	Route::get('/search-reminder-time-slot', [CommanUserDetailController::class, "searchReminderTimeSlot"])->name('search.reminder.time.slot');
	Route::get('/search-user-tag', [CommanUserDetailController::class, "searchUserTag"])->name('search.user.tag');
	Route::POST('/save-user-detail', [CommanUserDetailController::class, "saveUserDetail"])->name('save.user.detail');
	Route::post('/user-view-lead-data', [CommanUserDetailController::class, "viewLeadData"])->name('user.view.lead.data');
	Route::post('/user-view-deal-data', [CommanUserDetailController::class, "viewDealData"])->name('user.view.deal.data');
	Route::post('/user-view-channel-partner-deal-ajax', [CommanUserDetailController::class, "viewChannelPartnerDealAjax"])->name('user.view.channel.partner.deal.ajax');
	Route::get('/won_deal_bill_pending_channelpartner_export', function (Request $request) {
		$user_id = $request->user_id;
		return Excel::download(new ChannelPartnerBillPendingDealListExport($user_id), 'Bill Pending.xlsx');
	})->name('won.deal.bill.pending.channelpartner.export');
	Route::post('/user-status-change', [CommanUserDetailController::class, "userStatusChange"])->name('user.status.change');
	/// USER ACTION ROUTES END

	/// USER FILTER START
	Route::get('/user-filter-search-condition', [UserFilterController::class, "searchAdvanceFilterCondition"])->name('user.filter.search.condition');
	Route::get('/user-filter-search-value', [UserFilterController::class, "searchFilterValue"])->name('user.filter.search.value');
	/// USER FILTER END



	/// START MOVE ASSIGNEE

	Route::get('/database-master-move-assignee', [DatabaseMasterController::class, "index"])->name('database.master.move.assignee');
	Route::get('/database-master-move-assignee-search-assigned-user', [DatabaseMasterController::class, "searchAssignedUser"])->name('database.master.move.assignee.search.assigned.user');
	Route::post('/database-master-move-assignee-save', [DatabaseMasterController::class, "save"])->name('database.master.move.assignee.save');

	/// END MOVE ASSIGNEE

	/// START DEBUG LOG

	Route::get('/debug-log', [DebugLogController::class, "index"])->name('debug.log');
	Route::post('/debug-log-ajax', [DebugLogController::class, "ajax"])->name('debug.log.ajax');

	/// END DEBUG LOG

	/// START MAIN MASTER
	Route::get('/main-master', [MasterMainController::class, "index"])->name('main.master');
	Route::post('/main-master-ajax', [MasterMainController::class, "ajax"])->name('main.master.ajax');
	Route::post('/main-master-save', [MasterMainController::class, "save"])->name('main.master.save');
	Route::get('/main-master-detail', [MasterMainController::class, "detail"])->name('main.master.detail');

	/// END MAIN MASTER

	/// START Data Master
	Route::get('/data-master', [MasterDataController::class, "index"])->name('data.master');
	Route::post('/data-master-ajax', [MasterDataController::class, "ajax"])->name('data.master.ajax');
	Route::post('/data-master-save', [MasterDataController::class, "save"])->name('data.master.save');
	Route::get('/data-master-detail', [MasterDataController::class, "detail"])->name('data.master.detail');
	Route::get('/data-main-master-search', [MasterDataController::class, "searchMainMaster"])->name('data.main.master.search');

	/// END Data Master

	// START ROLL MASTER
	Route::get('/roll-master', [MasterRollController::class, "index"])->name('roll.master');
	Route::post('/roll-master-ajax', [MasterRollController::class, "ajax"])->name('roll.master.ajax');
	Route::get('/roll-master-detail', [MasterRollController::class, "detail"])->name('roll.master.detail');
	Route::post('/roll-master-save', [MasterRollController::class, "save"])->name('roll.master.save');
	// END ROLL MASTER

	/// START Country List
	Route::get('/master-location-country', [MasterLocationCountryController::class, "index"])->name('countrylist');
	Route::post('/master-location-country-ajax', [MasterLocationCountryController::class, "ajax"])->name('countrylist.ajax');
	/// END Country List

	/// START State List
	Route::get('/master-location-state', [MasterLocationStateController::class, "index"])->name('statelist');
	Route::post('/master-location-state-ajax', [MasterLocationStateController::class, "ajax"])->name('statelist.ajax');
	/// END State List

	/// START CITY LIST
	Route::get('/master-location-city', [MasterLocationCityController::class, "index"])->name('citylist');
	Route::get('/master-location-city-search-state', [MasterLocationCityController::class, "searchState"])->name('citylist.search.state');
	Route::post('/master-location-city-ajax', [MasterLocationCityController::class, "ajax"])->name('citylist.ajax');
	Route::get('/master-location-city-search-country', [MasterLocationCityController::class, "searchCountry"])->name('citylist.search.country');
	Route::post('/master-location-city-save', [MasterLocationCityController::class, "save"])->name('citylist.save');
	Route::get('/master-location-city-detail', [MasterLocationCityController::class, "detail"])->name('citylist.detail');

	/// END CITY LIST

	/// START Sales Hierarchy
	Route::get('/sales-hierarchy', [MasterSalesHierarchyController::class, "index"])->name('sales.hierarchy');
	Route::get('/sales-hierarchy-search', [MasterSalesHierarchyController::class, "search"])->name('sales.hierarchy.search');
	Route::post('/sales-hierarchy-ajax', [MasterSalesHierarchyController::class, "ajax"])->name('sales.hierarchy.ajax');
	Route::post('/sales-hierarchy-save', [MasterSalesHierarchyController::class, "saveProcess"])->name('sales.hierarchy.save');

	Route::get('/sales-hierarchy-detail', [MasterSalesHierarchyController::class, "detail"])->name('sales.hierarchy.detail');
	Route::get('/sales-hierarchy-delete', [MasterSalesHierarchyController::class, "delete"])->name('sales.hierarchy.delete');

	/// END Sales Hierarchy

	/// START Sales Hierarchy
	Route::get('/purchase-hierarchy', [MasterPurchaseHierarchyController::class, "index"])->name('purchase.hierarchy');
	Route::get('/purchase-hierarchy-search', [MasterPurchaseHierarchyController::class, "search"])->name('purchase.hierarchy.search');
	Route::post('/purchase-hierarchy-ajax', [MasterPurchaseHierarchyController::class, "ajax"])->name('purchase.hierarchy.ajax');
	Route::post('/purchase-hierarchy-save', [MasterPurchaseHierarchyController::class, "saveProcess"])->name('purchase.hierarchy.save');

	Route::get('/purchase-hierarchy-detail', [MasterPurchaseHierarchyController::class, "detail"])->name('purchase.hierarchy.detail');
	Route::get('/purchase-hierarchy-delete', [MasterPurchaseHierarchyController::class, "delete"])->name('purchase.hierarchy.delete');

	/// END Sales Hierarchy

	/// START Companies
	Route::get('/companies', [MasterCompanyController::class, "index"])->name('companies');
	Route::post('/companies-ajax', [MasterCompanyController::class, "ajax"])->name('companies.ajax');
	Route::post('/company-save', [MasterCompanyController::class, "save"])->name('company.save');
	Route::get('/company-detail', [MasterCompanyController::class, "detail"])->name('company.detail');
	Route::get('/companies-search-state', [MasterCompanyController::class, "searchState"])->name('companies.search.state');
	Route::get('/companies-search-city', [MasterCompanyController::class, "searchCity"])->name('companies.search.city');

	/// END Companies

	/// START PARAMETER
	Route::get('/parameter', [MasterParameterController::class, "index"])->name('parameter');
	Route::post('/parameter-ajax', [MasterParameterController::class, "ajax"])->name('parameter.ajax');
	Route::get('/parameter-detail', [MasterParameterController::class, "detail"])->name('parameter.detail');
	Route::post('/parameter-save', [MasterParameterController::class, "save"])->name('parameter.save');
	/// END PARAMETER

	/// START Incentive
	Route::get('/incentive-sale-person', [IncentiveController::class, "salePerson"])->name('incentive.sale.person');
	Route::get('/incentive-channel-partner', [IncentiveController::class, "channelPartner"])->name('incentive.channel.partner');

	Route::post('/incentive-ajax', [IncentiveController::class, "ajax"])->name('incentive.ajax');
	Route::post('/incentive-save', [IncentiveController::class, "save"])->name('incentive.save');
	Route::get('/incentive-detail', [IncentiveController::class, "detail"])->name('incentive.detail');

	/// END Incentive

	Route::get('/exhibition', [MasterExhibitionController::class, "index"])->name('exhibition');
	Route::post('/exhibition-ajax', [MasterExhibitionController::class, "ajax"])->name('exhibition.ajax');
	Route::get('/exhibition-detail', [MasterExhibitionController::class, "detail"])->name('exhibition.detail');
	Route::post('/exhibition-save', [MasterExhibitionController::class, "save"])->name('exhibition.save');
	Route::get('/search-sales-person', [MasterExhibitionController::class, "searchSalesPerson"])->name('exhibition.search.sales');

	/// START Data Master
	Route::get('/product-inventory', [ProductInventoryController::class, "index"])->name('product.inventory');
	Route::post('/product-inventory-ajax', [ProductInventoryController::class, "ajax"])->name('product.inventory.ajax');
	Route::post('/product-inventory-save', [ProductInventoryController::class, "save"])->name('product.inventory.save');
	Route::get('/product-inventory-detail', [ProductInventoryController::class, "detail"])->name('product.inventory.detail');
	Route::get('/product-inventory-search-brand', [ProductInventoryController::class, "searchBrand"])->name('product.inventory.search.brand');
	Route::get('/product-inventory-search-code', [ProductInventoryController::class, "searchCode"])->name('product.inventory.search.code');

	Route::post('/product-inventory-discount-ajax', [ProductInventoryController::class, "discountAjax"])->name('product.inventory.discount.ajax');

	Route::post('/product-inventory-discount-save', [ProductInventoryController::class, "discountSave"])->name('product.inventory.discount.save');
	Route::post('/product-inventory-discount-save-all', [ProductInventoryController::class, "discountSaveAll"])->name('product.inventory.discount.save.all');

	Route::get('/product-inventory-view', [ProductInventoryViewController::class, "index"])->name('product.inventory.view');
	Route::post('/product-inventory-ajax-view', [ProductInventoryViewController::class, "ajax"])->name('product.inventory.ajax.view');

	Route::get('/product-inventory-stock', [ProductInventoryStockController::class, "index"])->name('product.inventory.stock');
	Route::post('/product-inventory-ajax-stock', [ProductInventoryStockController::class, "ajax"])->name('product.inventory.ajax.stock');
	Route::get('/product-inventory-detail-stock', [ProductInventoryStockController::class, "detail"])->name('product.inventory.detail.stock');
	Route::post('/product-inventory-save-stock', [ProductInventoryStockController::class, "save"])->name('product.inventory.save.stock');

	Route::get('/product-inventory-search-channel-partner', [ProductInventoryController::class, "searchChannelPartner"])->name('product.inventory.search.channel.partner');
	Route::get('/product-inventory-search-group', [ProductInventoryController::class, "searchProductGroup"])->name('product.inventory.search.group');
	Route::get('/product-inventory-search-product-inventory', [ProductInventoryController::class, "searchProductInventory"])->name('product.inventory.search');
	Route::post('/product-inventory-report-generate', [ProductInventoryController::class, "reportGenerate"])->name('product.inventory.report.generate');

	/// END Data Master

	/// START Product Log

	Route::get('/product-log', [ProductLogController::class, "index"])->name('product.log');
	Route::post('/product-log-ajax', [ProductLogController::class, "ajax"])->name('product.log.ajax');
	Route::get('/product-log-search-product', [ProductLogController::class, "searchProduct"])->name('product.log.search.product');

	/// END Product Log

	/// START Product Group
	Route::get('/product-group', [ProductGroupController::class, "index"])->name('product.group');
	Route::get('/product-group-search-brand', [ProductGroupController::class, "searchBrand"])->name('product.group.search.brand');
	Route::post('/product-group-ajax', [ProductGroupController::class, "ajax"])->name('product.group.ajax');
	Route::post('/product-group-save', [ProductGroupController::class, "save"])->name('product.group.save');
	Route::get('/product-group-detail', [ProductGroupController::class, "detail"])->name('product.group.detail');
	Route::get('/product-group-delete', [ProductGroupController::class, "delete"])->name('product.group.delete');

	/// END Product Group

	/// START CRM

	/// START GIFT CATEGORY
	Route::get('/gift-category', [CRMGiftCategoryController::class, "index"])->name('gift.category');
	Route::post('/gift-category-ajax', [CRMGiftCategoryController::class, "ajax"])->name('gift.category.ajax');
	Route::get('/gift-category-detail', [CRMGiftCategoryController::class, "detail"])->name('gift.category.detail');
	Route::post('/gift-category-save', [CRMGiftCategoryController::class, "save"])->name('gift.category.save');

	/// END GIFT CATEGORY

	/// START GIFT PRODUCTS
	Route::get('/gift-products', [CRMGiftProductController::class, "index"])->name('gift.products');
	Route::post('/gift-products-ajax', [CRMGiftProductController::class, "ajax"])->name('gift.products.ajax');
	Route::get('/gift-product-category', [CRMGiftProductController::class, "category"])->name('gift.product.category');
	Route::get('/gift-product-detail', [CRMGiftProductController::class, "detail"])->name('gift.product.detail');
	Route::post('/gift-product-save', [CRMGiftProductController::class, "save"])->name('gift.product.save');

	/// END GIFT PRODUCTS
	/// START HELP DOCUMENT
	Route::get('/crm-help-document', [CRMHelpDocumentController::class, "index"])->name('crm.help.document');
	Route::post('/crm-help-document-ajax', [CRMHelpDocumentController::class, "ajax"])->name('crm.help.document.ajax');
	Route::get('/crm-help-document-detail', [CRMHelpDocumentController::class, "detail"])->name('crm.help.document.detail');
	Route::post('/crm-help-document-save', [CRMHelpDocumentController::class, "save"])->name('crm.help.document.save');
	///  END HELP DOCUMENT

	/// START GIFT PRODUCTS FOR USER
	Route::get('/crm-user-gift-products', [CRMUserGiftProductController::class, "index"])->name('architect.gift.products');
	Route::get('/crm-user-products-detail', [CRMUserGiftProductController::class, "detail"])->name('architect.gift.product.detail');

	Route::get('/crm-user-gift-products-cash', [CRMUserGiftProductController::class, "cash"])->name('architect.gift.products.cash');

	Route::get('/crm-user-gift-products-cart-count', [CRMUserOrderController::class, "getCartCount"])->name('architect.gift.products.cart.count');
	Route::get('/crm-user-gift-products-cart-set', [CRMUserOrderController::class, "setCart"])->name('architect.gift.products.cart.set');
	Route::get('/crm-user-gift-products-cart-remove', [CRMUserOrderController::class, "removeFromCart"])->name('architect.gift.products.cart.remove');

	Route::get('/crm-user-gift-products-cart', [CRMUserOrderController::class, "cart"])->name('architect.gift.products.cart');
	Route::get('/crm-user-gift-products-cart-detail', [CRMUserOrderController::class, "cartDetail"])->name('architect.gift.products.cart.detail');

	Route::post('/crm-user-gift-products-preview-order', [CRMUserOrderController::class, "previewOrder"])->name('architect.gift.products.preview.order');
	Route::post('/crm-user-gift-products-place-order', [CRMUserOrderController::class, "placeOrder"])->name('architect.gift.products.place.order');

	/// END GIFT PRODUCTS FOR USER

	/// START ARCHITECT HELP DOCUMENT
	Route::get('/crm-user-help-document', [CRMUserHelpDocumentController::class, "index"])->name('architect.help.document');
	/// END ARCHITECT HELP DOCUMENT

	/// START ARCHITECT LOG
	Route::get('/crm-user-log', [CRMUserLogController::class, "index"])->name('architect.log');
	Route::post('/crm-user-log-ajax', [CRMUserLogController::class, "ajax"])->name('architect.log.ajax');
	/// END ARCHITECT LOG

	/// START ARCHITECT ORDERS
	Route::get('/crm-user-orders', [CRMUserOrderController::class, "index"])->name('architect.orders');
	Route::post('/architech-orders-ajax', [CRMUserOrderController::class, "ajax"])->name('architect.orders.ajax');
	Route::get('/architech-order-detail', [CRMUserOrderController::class, "detail"])->name('architect.order.detail');
	/// END ARCHITECT ORDERS

	/// START CRM USER SAVE QUERY
	Route::post('/crm-user-order-send-query', [CRMUserRaiseQueryController::class, "send"])->name('crm.user.send.query');
	Route::get('/crm-user-order-query-detail', [CRMUserRaiseQueryController::class, "detail"])->name('crm.user.order.query.detail');
	Route::post('/crm-user-order-query-conversion-save', [CRMUserRaiseQueryController::class, "save"])->name('crm.user.order.query.conversion.save');
	/// END CRM USER SAVE QUERY

	/// START GIFT PRODUCT ORDERS
	Route::get('/gift-product-orders', [CRMGiftProductOrderController::class, "index"])->name('gift.product.orders');
	Route::post('/gift-product-orders-ajax', [CRMGiftProductOrderController::class, "ajax"])->name('gift.product.orders.ajax');
	Route::get('/gift-product-orders-detail', [CRMGiftProductOrderController::class, "detail"])->name('gift.product.order.detail');
	Route::post('/gift-product-orders-log-ajax', [CRMGiftProductOrderController::class, "logAjax"])->name('gift.product.orders.log.ajax');
	Route::post('/gift-product-order-mark-as-dispatch', [CRMGiftProductOrderController::class, "markAsDispatch"])->name('gift.product.order.markasdispatch');
	Route::get('/gift-product-order-mark-as-accept', [CRMGiftProductOrderController::class, "markAsAccept"])->name('gift.product.order.markasaccept');
	Route::get('/gift-product-order-mark-as-reject', [CRMGiftProductOrderController::class, "markAsReject"])->name('gift.product.order.markasreject');
	Route::get('/gift-product-order-mark-as-deliever', [CRMGiftProductOrderController::class, "markAsDeliever"])->name('gift.product.order.markasdeliever');
	Route::get('/gift-product-order-mark-as-recieve', [CRMGiftProductOrderController::class, "markAsRecieve"])->name('gift.product.order.markasrecieve');
	Route::post('/gift-product-order-save-bank-detail', [CRMGiftProductOrderController::class, "saveBankDetail"])->name('gift.product.order.savebankbetail');

	/// END GIFT PRODUCT ORDERS

	/// START GIFT PRODUCT ORDERS

	Route::get('/gift-product-order-query', [CRMGiftProductOrderQueryController::class, "index"])->name('gift.product.orders.query');
	Route::post('/gift-product-order-query-ajax', [CRMGiftProductOrderQueryController::class, "ajax"])->name('gift.product.orders.query.ajax');
	Route::get('/gift-product-order-query-detail', [CRMGiftProductOrderQueryController::class, "detail"])->name('gift.product.orders.query.detail');
	Route::post('/gift-product-order-query-save', [CRMGiftProductOrderQueryController::class, "save"])->name('gift.product.orders.query.save');
	Route::get('/gift-product-order-query-close', [CRMGiftProductOrderQueryController::class, "close"])->name('gift.product.orders.query.close');

	/// END GIFT PRODUCT ORDERS

	Route::get('/crm-account-table', [LeadAccountController::class, "index"])->name('crm.account.table');
	Route::post('/crm-account-table-ajax', [LeadAccountController::class, "ajax"])->name('crm.account.table.ajax');
	Route::get('/crm-account-detail', [LeadAccountController::class, "detail"])->name('crm.account.detail');
	Route::get('/crm-account-list', [LeadAccountController::class, "getList"])->name('crm.account.list');
	Route::get('/crm-account-detail-view', [LeadAccountController::class, "getDeatailView"])->name('crm.lead.account.detail.view');
	Route::post('/crm-account-list-ajax', [LeadAccountController::class, "getListAjax"])->name('crm.lead.account.list.ajax');

	/// CRM LEAD

	Route::get('/crm-lead-my-action', [LeadActionController::class, "index"])->name('crm.lead.myaction');
	Route::post('/crm-lead-action-call-ajax', [LeadActionController::class, "TodayMyActionAjax"])->name('crm.lead.myaction.call.ajax');
	Route::post('/crm-lead-action-call-previous-ajax', [LeadActionController::class, "PreviousMyActionAjax"])->name('crm.lead.myaction.call.previous.ajax');
	Route::post('/crm-lead-action-today-close-call-ajax', [LeadActionController::class, "CloseMyActionAjax"])->name('crm.lead.myaction.today.close.call.ajax');

	Route::get('/crm-lead-team-action', [LeadTeamActionController::class, "index"])->name('crm.lead.team.action');
	Route::post('/crm-lead-today-team-action-ajax', [LeadTeamActionController::class, "TodayTeamActionAjax"])->name('crm.lead.today.team.action.ajax');
	Route::post('/crm-lead-previous-team-action-ajax', [LeadTeamActionController::class, "PreviousTeamActionAjax"])->name('crm.lead.previous-team.action.ajax');
	Route::post('/crm-lead-team-action-close-ajax', [LeadTeamActionController::class, "CloseTeamActionAjax"])->name('crm.lead.team.action.close.ajax');
	Route::get('/crm-lead-team-action-search-employee', [LeadTeamActionController::class, "SearchTeamEmployee"])->name('crm.lead.team.action.search.employee');

	Route::get('/crm-lead-account-contact-table', [LeadAccountContactController::class, "index"])->name('crm.lead.account.contact.table');
	Route::get('/crm-account-contact-detail-view', [LeadAccountContactController::class, "getDeatailView"])->name('crm.lead.account.contact.detail.view');
	Route::get('/crm-account-contact-list', [LeadAccountContactController::class, "getList"])->name('crm.lead.account.contact.list');
	Route::get('/crm-lead-account-contact-table-detail', [LeadAccountContactController::class, "table"])->name('crm.lead.account.contact.table.detail');
	Route::post('/crm-lead-account-contact-table-ajax', [LeadAccountContactController::class, "ajax"])->name('crm.lead.account.contact.table.ajax');

	// Route::get('/crm-inquiry-convert-to-lead', [LeadDataSyncController::class, "convertInquiryToLead"])->name('crm.inquiry.convert.to.lead');
	// Route::get('/delete-lead-threw-inquiry-trans', [LeadDataSyncController::class, "deleteLeadThrewInquiryTrans"])->name('delete.lead.threw.inquiry.trans');
	// Route::get('/update-inquiry-threw-lead-data', [LeadDataSyncController::class, "updateOldLeadData"])->name('update.inquiry.threw.lead.data');
	// Route::get('/update-inquiry-threw-lead-stage-site', [LeadDataSyncController::class, "updatestageofsitedata"])->name('update.inquiry.threw.lead.stage.site');
	// Route::get('/crm-inquiry-bill-transfer-to-lead', [LeadDataSyncController::class, "inquiryBillTransferLeadAndDeal"])->name('crm.inquiry.transfer.to.lead.deal.bill');
	// Route::get('/crm-lead-otherdata-remove', [LeadDataSyncController::class, "deleteLeadOtherData"])->name('crm.lead.otherdata.remove');

	// Route::get('/crm-inquiry-quot-amount-transfer-quotation-table', [LeadDataSyncController::class, "leadAmountToQuotation"])->name('crm.inquiry.quot.amount.transfer.quotation.table');
	// Route::get('/crm-inquiry-quot-auto-tick', [LeadDataSyncController::class, "lastQuotationAutoTick"])->name('crm.inquiry.quot.auto.tick');

	Route::get('/crm-lead-table', [LeadController::class, "table"])->name('crm.lead.table');
	Route::post('/crm-lead-table-ajax', [LeadController::class, "tableAjax"])->name('crm.lead.table.ajax');
	Route::get('/crm-deal-table', [LeadController::class, "tableDeal"])->name('crm.deal.table');

	Route::get('/crm-lead', [LeadController::class, "index"])->name('crm.lead');
	Route::get('/crm-deal', [LeadController::class, "indexDeal"])->name('crm.deal');

	Route::post('/crm-lead-save', [LeadController::class, "save"])->name('crm.lead.save');
	Route::get('/crm-lead-updatedetail', [LeadController::class, "updateDetail"])->name('crm.lead.updatedetail');
	Route::get('/crm-lead-search-site-stage', [LeadController::class, "searchSiteStage"])->name('crm.lead.search.site.stage');
	Route::get('/crm-lead-search-site-type', [LeadController::class, "searchSiteType"])->name('crm.lead.search.site.type');
	Route::get('/crm-lead-search-bhk', [LeadController::class, "searchBHK"])->name('crm.lead.search.bhk');
	Route::get('/crm-lead-search-want-to-cover', [LeadController::class, "searchWantToCover"])->name('crm.lead.search.want.to.cover');
	Route::get('/crm-lead-search-source-type', [LeadController::class, "searchSourceType"])->name('crm.lead.search.source.type');
	Route::get('/crm-lead-search-source', [LeadController::class, "searchSource"])->name('crm.lead.search.source');
	Route::get('/crm-lead-search-status', [LeadController::class, "searchStatus"])->name('crm.lead.search.status');
	Route::get('/crm-lead-search-sub-status', [LeadController::class, "searchSubStatus"])->name('crm.lead.search.sub.status');
	Route::get('/crm-lead-search-competitors', [LeadController::class, "searchCompetitors"])->name('crm.lead.search.competitors');
	Route::get('/crm-lead-search-assigned-user', [LeadController::class, "searchAssignedUser"])->name('crm.lead.search.assigned.user');
	Route::get('/crm-lead-search-advance-filter-view', [LeadController::class, "searchAdvanceFilterView"])->name('crm.lead.search.advance.filter.view');
	Route::post('/crm-lead-save-advance-filter', [LeadController::class, "saveAdvanceFilter"])->name('crm.lead.save.advance.filter');
	Route::post('/crm-lead-advance-filter-detail', [LeadController::class, "getDetailAdvanceFilter"])->name('crm.lead.advance.filter.detail');
	Route::get('/crm-lead-search-filter-value', [LeadController::class, "searchFilterValue"])->name('crm.lead.search.filter.value');
	Route::get('/crm-lead-search-filter-condition', [LeadController::class, "searchAdvanceFilterCondition"])->name('crm.lead.search.filter.condition');
	Route::get('/crm-lead-view-selected-filter', [LeadController::class, "ViewSelectedFilter"])->name('crm.lead.view.selected.filter');
	Route::get('/crm-lead-advance-filter-delete', [LeadController::class, "AdvanceFilterDelete"])->name('crm.lead.advance.filter.delete');
	Route::get('/crm-lead-search-status-action', [LeadController::class, "searchStatusInAction"])->name('crm.lead.search.status.action');
	Route::post('/crm-lead-change-final-quotation', [LeadController::class, "changeFinalQuotation"])->name('crm.lead.change.final.quotation');
	Route::get('/crm-lead-search-reminder-time-slot', [LeadController::class, "searchReminderTimeSlot"])->name('crm.lead.search.reminder.time.slot');
	Route::get('/crm-lead-refresh-status', [LeadController::class, "refreshStatus"])->name('crm.lead.refresh.status');
	Route::post('/crm-lead-phone-number-check', [LeadController::class, "checkPhoneNumber"])->name('crm.lead.phone.number.check');
	Route::get('/crm-lead-search-lead-and-deal-tag', [LeadController::class, "searchLeadAndDealTag"])->name('crm.lead.search.lead.and.deal.tag');
	Route::get('/crm-lead-search-question', [LeadController::class, "searchQuestion"])->name('crm.lead.search.question');
	Route::post('/save-lead-status-answer', [LeadController::class, "saveLeadStatusAnswer"])->name('save.lead.status.answer');
	Route::get('/lead-channel-partner-detail', [LeadController::class, "channelPartnerdetail"])->name('lead.channel.partner.detail');
	Route::get('/lead-architect-detail', [LeadController::class, "architectdetail"])->name('lead.architect.detail');
	Route::get('/lead-electrician-detail', [LeadController::class, "electriciandetail"])->name('lead.electrician.detail');
	Route::get('/lead-search-channel-partner', [LeadController::class, "searchChannelpartner"])->name('lead.search.channel.partner');

	Route::get('/crm-lead-detail', [LeadController::class, "getDeatail"])->name('crm.lead.detail');
	Route::post('/crm-lead-list-ajax', [LeadController::class, "getListAjax"])->name('crm.lead.list.ajax');
	Route::post('/crm-lead-list-amount-summary', [LeadController::class, "getLeadAmountSummary"])->name('crm.lead.list.amount.summary');
	Route::get('/crm-lead-edit-detail', [LeadController::class, "editDetail"])->name('crm.lead.edit.detail');
	Route::get('/crm-lead-contact-all', [LeadController::class, "allContact"])->name('crm.lead.contact.all');
	Route::get('/crm-lead-file-all', [LeadController::class, "allFiles"])->name('crm.lead.file.all');
	Route::get('/crm-lead-update-all', [LeadController::class, "allUpdates"])->name('crm.lead.update.all');
	Route::get('/crm-lead-open-action-all', [LeadController::class, "allOpenAction"])->name('crm.lead.open.action.all');
	Route::get('/crm-lead-close-action-all', [LeadController::class, "allCloseAction"])->name('crm.lead.close.action.all');
	Route::get('/crm-lead-status-change', [LeadController::class, "changeStatus"])->name('crm.lead.status.change');
	Route::post('/crm-lead-status-save', [LeadController::class, "saveStatus"])->name('crm.lead.status.save');
	Route::post('/crm-filter-view-as-default-save', [LeadController::class, "saveViewAsDefault"])->name('crm.filter.view.as.default.save');
	Route::post('/crm-lead-view-log', [LeadController::class, "ViewLeadLog"])->name('crm.lead.view.log');
	Route::get('/crm-lead-get-reward-bill-status', [LeadController::class, "getRewardBillStatus"])->name('crm.lead.get.reward.bill.status');

	Route::get('/crm-lead-search-contact-tag', [LeadContactController::class, "searchTag"])->name('crm.lead.search.contact.tag');
	Route::post('/crm-lead-contact-save', [LeadContactController::class, "save"])->name('crm.lead.contact.save');
	Route::get('/crm-lead-contact-detail', [LeadContactController::class, "detail"])->name('crm.lead.contact.detail');

	Route::post('/crm-lead-point-ajax', [LeadPointController::class, "pointAjax"])->name('crm.lead.point.ajax');
	Route::post('/crm-lead-save-billing-amount', [LeadPointController::class, "saveBillingAmount"])->name('crm.lead.save.billing.amount');
	Route::get('/crm-lead-point-query-question-answer', [LeadPointController::class, "hodQueryQuestion"])->name('crm.lead.point.query.question.answer');
	Route::get('/crm-lead-point-query-question', [LeadPointController::class, "pointQueryQuestion"])->name('crm.lead.point.query.question');
	Route::post('/lead-answer-save', [LeadPointController::class, "saveLeadAnswer"])->name('lead.answer.save');
	Route::post('/point-hod-approve', [LeadPointController::class, "hodApproved"])->name('point.hod.approve');

	// 2023-03-27
	Route::post('/crm-account-contact-save', [LeadAccountContactController::class, "save"])->name('crm.account.contact.save');
	Route::get('/crm-account-contact-detail', [LeadAccountContactController::class, "detail"])->name('crm.account.contact.detail');

	Route::post('/crm-lead-file-save', [LeadFileController::class, "save"])->name('crm.lead.file.save');
	Route::get('/crm-lead-file-delete', [LeadFileController::class, "delete"])->name('crm.lead.file.delete');
	Route::get('/crm-lead-search-file-tag', [LeadFileController::class, "searchTag"])->name('crm.lead.search.file.tag');
	Route::post('/crm-lead-file-status-change', [LeadFileController::class, "statusChange"])->name('crm.lead.file.status.change');
	Route::post('/crm-lead-update-save', [LeadUpdateController::class, "save"])->name('crm.lead.update.save');

	Route::get('/crm-lead-search-call-type', [LeadCallController::class, "searchCallType"])->name('crm.lead.search.call.type');
	Route::get('/crm-lead-search-contact', [LeadCallController::class, "searchContact"])->name('crm.lead.search.contact');
	Route::post('/crm-lead-call-save', [LeadCallController::class, "save"])->name('crm.lead.call.save');
	Route::get('/crm-lead-call-detail', [LeadCallController::class, "detail"])->name('crm.lead.call.detail');
	Route::get('/crm-lead-search-call-outcome-type', [LeadCallController::class, "searchCallOutcomeType"])->name('crm.lead.search.call.outcome.type');
	Route::get('/crm-lead-search-additional-info', [LeadCallController::class, "searchAdditionalInfo"])->name('crm.lead.search.additional.info');
	Route::get('/crm-lead-additional-info-detail', [LeadCallController::class, "getAdditionalInfoDetail"])->name('crm.lead.additional.info.detail');
	Route::get('/crm-lead-call-outcome-type-detail', [LeadCallController::class, "getCallOutcomeTypeDetail"])->name('crm.lead.call.outcome.type.detail');
	Route::get('/crm-lead-search-call-assign-to', [LeadCallController::class, "searchAssignedTo"])->name('crm.lead.search.call.assign');
	Route::get('/crm-lead-auto-call-detail', [LeadCallController::class, 'getCallDetail'])->name('crm.lead.auto.call.detail');
	Route::get('/crm-lead-auto-task-and-call-list', [LeadCallController::class, 'getTaskAndCallList'])->name('crm.lead.auto.task.and.call.list');

	Route::get('/crm-lead-search-task-assign-to', [LeadTaskController::class, "searchAssignedTo"])->name('crm.lead.search.task.assign');
	Route::post('/crm-lead-task-save', [LeadTaskController::class, "save"])->name('crm.lead.task.save');
	Route::get('/crm-lead-task-detail', [LeadTaskController::class, "detail"])->name('crm.lead.task.detail');
	Route::post('/crm-lead-quotation-save', [LeadQuotationController::class, "save"])->name('crm.lead.quotation.save');
	Route::get('/crm-lead-search-task-outcome-type', [LeadTaskController::class, "searchTaskOutcomeType"])->name('crm.lead.search.task.outcome.type');
	Route::get('/crm-lead-auto-task-detail', [LeadTaskController::class, 'getTaskDetail'])->name('crm.lead.auto.task.detail');


	Route::get('/crm-lead-search-meeting-title', [LeadMeetingController::class, "searchTitle"])->name('crm.lead.search.meeting.title');
	Route::get('/crm-lead-search-meeting-type', [LeadMeetingController::class, "searchMeetingType"])->name('crm.lead.search.meeting.type');
	Route::get('/crm-lead-search-meeting-participants', [LeadMeetingController::class, "searchParticipants"])->name('crm.lead.search.meeting.participants');
	Route::post('/crm-lead-meeting-save', [LeadMeetingController::class, "save"])->name('crm.lead.meeting.save');
	Route::get('/crm-lead-meeting-detail', [LeadMeetingController::class, "detail"])->name('crm.lead.meeting.detail');
	Route::get('/crm-lead-search-meeting-outcome-type', [LeadMeetingController::class, "searchMeetingOutcomeType"])->name('crm.lead.search.meeting.outcome.type');
	Route::post('/crm-lead-find-meeting-times', [LeadMeetingController::class, "findMeetingTimes"])->name('crm.lead.find.meeting.times');

	Route::get('/crm-setting', [CRMSettingController::class, "index"])->name('crm.setting');
	Route::post('/crm-setting-stage-of-site-ajax', [CRMSettingController::class, "ajaxStageOfSite"])->name('crm.setting.stage.site');
	Route::get('/crm-setting-stage-of-site-detail', [CRMSettingController::class, "deailStageOfSite"])->name('crm.setting.stage.site.detail');
	Route::post('/crm-setting-stage-of-site-save', [CRMSettingController::class, "saveStageOfSite"])->name('crm.setting.stage.site.save');

	Route::post('/crm-setting-site-type-ajax', [CRMSettingController::class, "ajaxSiteType"])->name('crm.setting.site.type');
	Route::get('/crm-setting-site-type-detail', [CRMSettingController::class, "deailSiteType"])->name('crm.setting.site.type.detail');
	Route::post('/crm-setting-site-type-save', [CRMSettingController::class, "saveSiteType"])->name('crm.setting.site.type.save');

	Route::post('/crm-setting-bhk-ajax', [CRMSettingController::class, "ajaxBHK"])->name('crm.setting.bhk');
	Route::get('/crm-setting-bhk-detail', [CRMSettingController::class, "deailBHK"])->name('crm.setting.bhk.detail');
	Route::post('/crm-setting-bhk-save', [CRMSettingController::class, "saveBHK"])->name('crm.setting.bhk.save');

	Route::post('/crm-setting-want-to-cover-ajax', [CRMSettingController::class, "ajaxWantToCover"])->name('crm.setting.cover');
	Route::get('/crm-setting-want-to-cover-detail', [CRMSettingController::class, "deailWantToCover"])->name('crm.setting.cover.detail');
	Route::post('/crm-setting-want-to-cover-save', [CRMSettingController::class, "saveWantToCover"])->name('crm.setting.cover.save');

	Route::post('/crm-setting-source-type-ajax', [CRMSettingController::class, "ajaxSouceType"])->name('crm.setting.source.type');
	Route::get('/crm-setting-source-type-detail', [CRMSettingController::class, "deailSouceType"])->name('crm.setting.source.type.detail');
	Route::post('/crm-setting-source-type-save', [CRMSettingController::class, "saveSouceType"])->name('crm.setting.source.type.save');

	Route::get('/crm-setting-source-search', [CRMSettingController::class, "searchSourceType"])->name('crm.setting.source.type.search');
	Route::post('/crm-setting-source-ajax', [CRMSettingController::class, "ajaxSource"])->name('crm.setting.source');
	Route::get('/crm-setting-source-detail', [CRMSettingController::class, "deailSource"])->name('crm.setting.source.detail');
	Route::post('/crm-setting-source-save', [CRMSettingController::class, "saveSource"])->name('crm.setting.source.save');

	Route::post('/crm-setting-competitors-ajax', [CRMSettingController::class, "ajaxCompetitors"])->name('crm.setting.competitors');
	Route::get('/crm-setting-competitors-detail', [CRMSettingController::class, "deailCompetitors"])->name('crm.setting.competitors.detail');
	Route::post('/crm-setting-competitors-save', [CRMSettingController::class, "saveCompetitors"])->name('crm.setting.competitors.save');

	Route::get('/crm-setting-status-search', [CRMSettingController::class, "searchStatus"])->name('crm.setting.status.search');
	Route::post('/crm-setting-sub-status-ajax', [CRMSettingController::class, "ajaxSubStatus"])->name('crm.setting.sub.status.ajax');
	Route::get('/crm-setting-sub-status-detail', [CRMSettingController::class, "deailSubStatus"])->name('crm.setting.sub.status.detail');
	Route::post('/crm-setting-sub-status-save', [CRMSettingController::class, "saveSubStatus"])->name('crm.setting.sub.status.save');

	Route::post('/crm-setting-contact-tag-ajax', [CRMSettingController::class, "ajaxContactTag"])->name('crm.setting.contact.tag');
	Route::get('/crm-setting-contact-tag-detail', [CRMSettingController::class, "deailContactTag"])->name('crm.setting.contact.tag.detail');
	Route::post('/crm-setting-contact-tag-save', [CRMSettingController::class, "saveContactTag"])->name('crm.setting.contact.tag.save');

	Route::post('/crm-setting-file-tag-ajax', [CRMSettingController::class, "ajaxFileTag"])->name('crm.setting.file.tag');
	Route::get('/crm-setting-file-tag-detail', [CRMSettingController::class, "deailFileTag"])->name('crm.setting.file.tag.detail');
	Route::post('/crm-setting-file-tag-save', [CRMSettingController::class, "saveFileTag"])->name('crm.setting.file.tag.save');

	Route::post('/crm-setting-meeting-title-ajax', [CRMSettingController::class, "ajaxMeetingTitle"])->name('crm.setting.meeting.title');
	Route::get('/crm-setting-meeting-title-detail', [CRMSettingController::class, "deailMeetingTitle"])->name('crm.setting.meeting.title.detail');
	Route::post('/crm-setting-meeting-title-save', [CRMSettingController::class, "saveMeetingTitle"])->name('crm.setting.meeting.title.save');

	Route::post('/crm-setting-schedule-type-ajax', [CRMSettingController::class, "ajaxScheduleType"])->name('crm.setting.schedule.type');
	Route::get('/crm-setting-schedule-type-detail', [CRMSettingController::class, "deailScheduleType"])->name('crm.setting.schedule.type.detail');
	Route::post('/crm-setting-schedule-type-save', [CRMSettingController::class, "saveScheduleType"])->name('crm.setting.schedule.type.save');

	Route::post('/crm-setting-schedule-type-meeting-ajax', [CRMSettingController::class, "ajaxScheduleMeetingType"])->name('crm.setting.schedule.meeting.type');
	Route::get('/crm-setting-schedule-type-meeting-detail', [CRMSettingController::class, "deailScheduleMeetingType"])->name('crm.setting.schedule.type.meeting.detail');
	Route::post('/crm-setting-schedule-type-meeting-save', [CRMSettingController::class, "saveScheduleMeetingType"])->name('crm.setting.schedule.type.meeting.save');


	Route::post('/crm-setting-call-outcome-type-ajax', [CRMSettingController::class, "ajaxCallOutcomeType"])->name('crm.setting.call.outcome.type.ajax');
	Route::post('/crm-setting-call-outcome-type-detail', [CRMSettingController::class, "saveCallOutcomeType"])->name('crm.setting.call.outcome.type.save');
	Route::get('/crm-setting-call-outcome-type-save', [CRMSettingController::class, "detailCallOutcomeType"])->name('crm.setting.call.outcome.type.detail');

	Route::post('/crm-setting-meeting-outcome-type-ajax', [CRMSettingController::class, "ajaxMeetingOutcomeType"])->name('crm.setting.meeting.outcome.type.ajax');
	Route::post('/crm-setting-meeting-outcome-type-detail', [CRMSettingController::class, "saveMeetingOutcomeType"])->name('crm.setting.meeting.outcome.type.save');
	Route::get('/crm-setting-meeting-outcome-type-save', [CRMSettingController::class, "detailMeetingOutcomeType"])->name('crm.setting.meeting.outcome.type.detail');

	Route::post('/crm-setting-task-outcome-type-ajax', [CRMSettingController::class, "ajaxTaskOutcomeType"])->name('crm.setting.task.outcome.type.ajax');
	Route::post('/crm-setting-task-outcome-type-detail', [CRMSettingController::class, "saveTaskOutcomeType"])->name('crm.setting.task.outcome.type.save');
	Route::get('/crm-setting-task-outcome-type-save', [CRMSettingController::class, "detailTaskOutcomeType"])->name('crm.setting.task.outcome.type.detail');

	Route::post('/crm-setting-lead-deal-tag-master-save', [CRMSettingController::class, "saveLeadDealTag"])->name('crm.setting.lead.deal.tag.master.save');
	Route::post('/crm-setting-lead-deal-tag-master-ajax', [CRMSettingController::class, "ajaxLeadDealTag"])->name('crm.setting.lead.deal.tag.master.ajax');
	Route::get('/crm-setting-lead-deal-tag-master-detail', [CRMSettingController::class, "detailLeadDealTag"])->name('crm.setting.lead.deal.tag.master.detail');

	Route::post('/crm-setting-user-tag-master-save', [CRMSettingController::class, "saveUserTag"])->name('crm.setting.user.tag.master.save');
	Route::post('/crm-setting-user-tag-master-ajax', [CRMSettingController::class, "ajaxUserTag"])->name('crm.setting.user.tag.master.ajax');
	Route::get('/crm-setting-user-tag-master-detail', [CRMSettingController::class, "detailUserTag"])->name('crm.setting.user.tag.master.detail');

	Route::post('/crm-setting-call-additional-info-ajax', [CRMSettingController::class, "ajaxCallAdditionalInfo"])->name('crm.setting.call.additional.info.ajax');
	Route::post('/crm-setting-call-additional-info-save', [CRMSettingController::class, "saveCallAdditionalInfo"])->name('crm.setting.call.additional.info.save');
	Route::get('/crm-setting-call-additional-info-detail', [CRMSettingController::class, "detailCallAdditionalInfo"])->name('crm.setting.call.additional.info.detail');

	/// CRM LEAD

	/// START INQUIRY QUESTION

	Route::get('/inquiry-question', [CRMInquiryQuestionController::class, "index"])->name('inquery.question');
	Route::post('/inquiry-question-save', [CRMInquiryQuestionController::class, "saveQuestion"])->name('inquiry.question.save');
	Route::post('/inquiry-question-ajax', [CRMInquiryQuestionController::class, "ajax"])->name('inquiry.question.ajax');
	Route::get('/inquiry-question-detail', [CRMInquiryQuestionController::class, "detail"])->name('inquiry.question.detail');
	Route::get('/inquiry-question-delete', [CRMInquiryQuestionController::class, "delete"])->name('inquiry.question.delete');
	Route::get('/inquiry-question-order-change', [CRMInquiryQuestionController::class, "orderChange"])->name('inquiry.question.order.change');
	Route::get('/inquiry-question-depended-question', [CRMInquiryQuestionController::class, "dependedQuestion"])->name('inquiry.question.depended.question');
	Route::get('/inquiry-question-depended-question-answer', [CRMInquiryQuestionController::class, "dependedQuestionAnswer"])->name('inquiry.question.depended.question.answer');

	/// END INQUIRY QUESTION

	Route::get('/inquiry-exhibition', [CRMInquiryExhibitionController::class, "index"])->name('inquiry.exhibition');
	Route::post('/inquiry-exhibition-ajax', [CRMInquiryExhibitionController::class, "ajax"])->name('inquiry.exhibition.ajax');
	Route::get('/inquiry-exhibition-detail', [CRMInquiryExhibitionController::class, "detail"])->name('inquiry.exhibition.detail');
	Route::post('/inquiry-exhibition-save', [CRMInquiryExhibitionController::class, "save"])->name('inquiry.exhibition.save');
	Route::get('/inquiry-search-exhibition-filter', [CRMInquiryExhibitionController::class, "searchExhibition"])->name('inquiry.search.exhibition.filter');
	Route::get('/inquiry-search-user-type-filter', [CRMInquiryExhibitionController::class, "searchUserType"])->name('inquiry.search.exhibition.user.type.filter');
	Route::get('/inquiry-search-inquiry-convert-filter', [CRMInquiryExhibitionController::class, "searchInquiryConverted"])->name('inquiry.search.exhibition.inquiry.convert.filter');
	Route::get('/inquiry-exhibition-report-download-filter', [CRMInquiryExhibitionController::class, "download"])->name('inquiry.exhibition.report.download.filter');
	Route::get('/inquiry-exhibition-search-assigned-user', [CRMInquiryExhibitionController::class, "searchAssignedUser"])->name('inquiry.exhibition..search.assigned.user');
	Route::get('/inquiry-exhibition-detail-2', [CRMInquiryExhibitionController::class, "detail2"])->name('inquiry.exhibition.detail2');
	Route::post('/inquiry-exhibition-update-save', [CRMInquiryExhibitionController::class, "saveUpdate"])->name('inquiry.exhibition.update.save');
	Route::get('/inquiry-exhibition-convert-to-user', [CRMInquiryExhibitionController::class, "converToUser"])->name('inquiry.exhibition.convert.user');

	/// START INQUIRY

	Route::get('/inquiry', [CRMInquiryController::class, "index"])->name('inquiry');
	Route::post('/inquiry-save', [CRMInquiryController::class, "saveInquiry"])->name('inquiry.save');
	Route::post('/inquiry-phone-number-check', [CRMInquiryController::class, "checkPhoneNumber"])->name('inquiry.phone');
	Route::post('/inquiry-ajax', [CRMInquiryController::class, "ajax"])->name('inquiry.ajax');

	Route::get('/pending-inquiry', [CRMInquiryController::class, "pendingRequest"])->name('inquiry.pending');
	Route::post('/pending-inquiry-ajax', [CRMInquiryController::class, "pendingRequestAjax"])->name('inquiry.pending.ajax');
	Route::post('/pending-inquiry-accept-reject', [CRMInquiryController::class, "acceptReject"])->name('inquiry.pending.accept.reject');

	Route::get('/inquiry-detail', [CRMInquiryController::class, "detail"])->name('inquiry.detail');
	Route::get('/inquiry-search-user', [CRMInquiryController::class, "searchUser"])->name('inquiry.search.user');
	Route::get('/inquiry-search-exhibition', [CRMInquiryController::class, "searchExhibition"])->name('inquiry.search.exhibition');
	Route::get('/inquiry-search-architect', [CRMInquiryController::class, "searchArchitect"])->name('inquiry.search.architect');
	Route::get('/inquiry-search-electrician', [CRMInquiryController::class, "searchElectrician"])->name('inquiry.search.electrician');
	Route::get('/inquiry-search-channel-partner', [CRMInquiryController::class, "searchChannelPartner"])->name('inquiry.search.channelpartner');

	Route::get('/inquiry-questions', [CRMInquiryController::class, "inquiryQuestions"])->name('inquiry.questions');
	Route::post('/inquiry-answer-save', [CRMInquiryController::class, "saveInquiryAnswer"])->name('inquiry.answer.save');
	Route::get('/inquiry-assigned-user', [CRMInquiryController::class, "assignedUser"])->name('inquiry.assigned.user');
	Route::get('/inquiry-search-assigned-user', [CRMInquiryController::class, "searchAssignedUser"])->name('inquiry.search.assigned.user');

	Route::post('/inquiry-assigned-to-save', [CRMInquiryController::class, "saveAssignedTo"])->name('inquiry.assignedto.save');

	Route::post('/inquiry-quotation-save', [CRMInquiryController::class, "saveQuotation"])->name('inquiry.quotation.save');
	Route::post('/inquiry-billing-save', [CRMInquiryController::class, "saveBilling"])->name('inquiry.billing.save');

	Route::post('/inquiry-closing-datetime-to-save', [CRMInquiryController::class, "saveClosingDateTime"])->name('inquiry.closing.datetime.save');

	Route::post('/inquiry-follow-up-datetime-to-save', [CRMInquiryController::class, "saveFollowUpDateTime"])->name('inquiry.followup.datetime.save');
	Route::get('/inquiry-stage-of-site-to-save', [CRMInquiryController::class, "saveStageOfSite"])->name('inquiry.stageofsite.save');
	Route::get('/inquiry-followup-type-save', [CRMInquiryController::class, "saveFollowupType"])->name('inquiry.followuptype.save');
	Route::post('/inquiry-update-save', [CRMInquiryController::class, "saveUpdate"])->name('inquiry.update.save');
	Route::get('/inquiry-update-seen', [CRMInquiryController::class, "updateSeen"])->name('inquiry.update.seen');

	Route::get('/inquiry-search-mention-users', [CRMInquiryController::class, "searchMentionUsers"])->name('inquiry.search.mention.users');
	Route::get('/inquiry-delete-invoice', [CRMInquiryController::class, "deleteInvoice"])->name('inquiry.delete.invoice');
	Route::post('/inquiry-point-log', [CRMInquiryController::class, "pointLog"])->name('inquiry.point.log');
	Route::get('/inquiry-prediection-sure-notsure', [CRMInquiryController::class, "moveTosureNosure"])->name('inquiry.sure.notsure');
	Route::get('/inquiry-tm', [CRMInquiryController::class, "moveToTM"])->name('inquiry.tm');
	Route::post('/inquiry-tm-save', [CRMInquiryController::class, "saveTMUpdate"])->name('inquiry.tm.save');

	/// END INQUIRY

	/// START MOVE ASSIGNEE

	Route::get('/inquiry-move-assignee', [CRMInquiryMoveAssigneeController::class, "index"])->name('inquiry.move.assignee');

	// Route::get('/inquiry-move-assignee-search-assigned-user', [CRMInquiryMoveAssigneeController::class, "searchAssignedUser"])->name('inquiry.move.assignee.search.assigned.user');
	Route::post('/inquiry-move-assignee-save', [CRMInquiryMoveAssigneeController::class, "save"])->name('inquiry.move.assignee.save');

	/// END MOVE ASSIGNEE

	/// START INQUIRY REPORTS

	Route::get('/inquiry-reports', [CRMInquiryReportsController::class, "index"])->name('inquiry.reports');
	Route::get('/inquiry-reports-search-sale-person', [CRMInquiryReportsController::class, "searchSalePerson"])->name('inquiry.reports.search.sale.person');
	Route::get('/inquiry-reports-search-source', [CRMInquiryReportsController::class, "searchSource"])->name('inquiry.reports.search.source');
	Route::get('/inquiry-reports-download', [CRMInquiryReportsController::class, "download"])->name('inquiry.reports.download');

	Route::post('/inquiry-reports-sales-person', [CRMInquiryReportsController::class, "getSalesPersonReport"])->name('inquiry.reports.sale.person');
	Route::post('/inquiry-reports-source-types', [CRMInquiryReportsController::class, "getSourceTypesReport"])->name('inquiry.reports.source.type');
	Route::post('/inquiry-reports-source', [CRMInquiryReportsController::class, "getSourceReport"])->name('inquiry.reports.source');
	Route::post('/inquiry-reports-list', [CRMInquiryReportsController::class, "inquiryList"])->name('inquiry.reports.list');

	/// END INQUIRY REPORTS

	/// START INQUIRY REVERSE REPORTS

	Route::get('/inquiry-reports-reverse', [CRMInquiryReportsReverseController::class, "index"])->name('inquiry.reports.reverse');
	Route::post('/inquiry-reports-reverse-sales-person', [CRMInquiryReportsReverseController::class, "getSalesPersonReport"])->name('inquiry.reports.reverse.sale.person');

	Route::get('/inquiry-reports-reverse-download', [CRMInquiryReportsReverseController::class, "download"])->name('inquiry.reports.reverse.download');

	/// END INQUIRY REVERSE REPORTS

	/// START INQUIRY PREDICATION REPORTS

	Route::get('/inquiry-reports-predication', [CRMInquiryReportsPredicationController::class, "index"])->name('inquiry.reports.predication');
	Route::post('/inquiry-reports-predication-sales-person', [CRMInquiryReportsPredicationController::class, "getSalesPersonReport"])->name('inquiry.reports.predication.sale.person');
	Route::get('/inquiry-reports-predication-download', [CRMInquiryReportsPredicationController::class, "download"])->name('inquiry.reports.predication.download');
	Route::get('/inquiry-predication-search-sale-person', [CRMInquiryReportsPredicationController::class, "searchSalePerson"])->name('inquiry.predication.search.sale.person');
	Route::post('/inquiry-report-predications-list', [CRMInquiryReportsPredicationController::class, "inquiryList"])->name('inquiry.reports.predication.list');

	/// END INQUIRY PREDICATION REPORTS

	/// END CRM

	/// START USER NOTIFICATION
	Route::get('/get-user-notification-badge', [NotificationController::class, "getBadge"])->name('notification.badge');
	Route::get('/get-user-notification-content', [NotificationController::class, "getContent"])->name('notification.content');

	Route::get('/read-notification', [NotificationController::class, "read"])->name('notification.read');
	Route::get('/unread-notification', [NotificationController::class, "unread"])->name('notification.unread');

	Route::get('/favourite-notification', [NotificationController::class, "favourite"])->name('notification.favourite');
	Route::get('/remove-favourite-notification', [NotificationController::class, "removeFromFavourite"])->name('notification.favourite.remove');
	/// END USER NOTIFICATION

	/// START MOVE ASSIGNEE

	Route::get('/move-assignee', [MoveAssigneeController::class, "index"])->name('move.assignee');

	Route::get('/move-assignee-search-assigned-user', [MoveAssigneeController::class, "searchAssignedUser"])->name('move.assignee.search.assigned.user');
	Route::post('/move-assignee-save', [MoveAssigneeController::class, "save"])->name('move.assignee.save');
	Route::post('/move-assignee-inquiry-ajax', [MoveAssigneeController::class, "ajaxInquiry"])->name('move.assignee.inquiry.ajax');

	/// END MOVE ASSIGNEE

	// START MOVE STATUS
	Route::get('/move-status', [MoveStatusController::class, "index"])->name('move.status');
	Route::get('/move-status-search-assigned-user', [MoveStatusController::class, "searchAssignedUser"])->name('move.status.search.assigned.user');
	Route::post('/move-status-inquiry-ajax', [MoveStatusController::class, "ajaxInquiry"])->name('move.status.inquiry.ajax');
	Route::post('/move-status-save', [MoveStatusController::class, "save"])->name('move.status.save');
	// END MOVE STATUS

	///MARKEING MATERIAL

	Route::get('/marketing-product-inventory', [MarketingProductInventoryController::class, "index"])->name('marketing.product.inventory');
	Route::post('/marketing-product-inventory-ajax', [MarketingProductInventoryController::class, "ajax"])->name('marketing.product.inventory.ajax');

	Route::post('/marketing-product-inventory-save', [MarketingProductInventoryController::class, "save"])->name('marketing.product.inventory.save');
	Route::get('/marketing-product-inventory-detail', [MarketingProductInventoryController::class, "detail"])->name('marketing.product.inventory.detail');
	Route::get('/marketing-product-inventory-search-group', [MarketingProductInventoryController::class, "searchGroup"])->name('marketing.product.inventory.search.group');
	Route::get('/marketing-product-inventory-search-code', [MarketingProductInventoryController::class, "searchCode"])->name('marketing.product.inventory.search.code');

	Route::get('/marketing-product-log', [MarketingProductLogController::class, "index"])->name('marketing.product.log');
	Route::post('/marketing-product-log-ajax', [MarketingProductLogController::class, "ajax"])->name('marketing.product.log.ajax');
	Route::get('/marketing-product-log-search-product', [MarketingProductLogController::class, "searchProduct"])->name('marketing.product.log.search.product');

	/// START ADD ORDER

	Route::get('/marketing-request-add', [MarketingOrderController::class, "add"])->name('marketing.order.add');
	Route::get('/marketing-request-pdf', [MarketingOrderController::class, "testPDF"])->name('marketing.order.pdf');
	Route::post('/marketing-request-calculation', [MarketingOrderController::class, "calculation"])->name('marketing.order.calculation');
	// Route::get('/order-search-city', [OrderController::class, "searchCity"])->name('order.search.city');
	Route::get('/marketing-request-search-channel-partner', [MarketingOrderController::class, "searchChannelPartner"])->name('marketing.order.search.channel.partner');
	Route::get('/marketing-request-channel-partner-detail', [MarketingOrderController::class, "channelPartnerDetail"])->name('marketing.order.channel.partner.detail');
	Route::get('/marketing-request-search-product', [MarketingOrderController::class, "searchProduct"])->name('marketing.order.search.product');
	Route::get('/marketing-request-product-detail', [MarketingOrderController::class, "productDetail"])->name('marketing.order.product.detail');
	Route::post('/marketing-request-save', [MarketingOrderController::class, "save"])->name('marketing.order.save');
	Route::get('/marketing-request-cancel', [MarketingOrderController::class, "cancel"])->name('marketing.order.cancel');

	/// END ADD ORDER

	// MARKETING ORDER
	Route::get('/marketing-request', [MarketingOrderController::class, "index"])->name('marketing.orders');
	Route::post('/marketing-request-ajax', [MarketingOrderController::class, "ajax"])->name('marketing.orders.ajax');
	Route::get('/marketing-request-detail', [MarketingOrderController::class, "detail"])->name('marketing.order.detail');
	// END MARKETING ORDEan

	// MARKETING REQUEST SALES
	Route::get('/marketing-request-sales', [MarketingOrderSalesController::class, "index"])->name('marketing.orders.sales');
	Route::post('/marketing-request-sales-ajax', [MarketingOrderSalesController::class, "ajax"])->name('marketing.orders.sales.ajax');
	Route::get('/marketing-request-sales-detail', [MarketingOrderSalesController::class, "detail"])->name('marketing.orders.sales.detail');

	Route::post('/marketing-orders-sales-invoice-calculation', [MarketingOrderSalesController::class, "calculation"])->name('marketing.orders.sales.invoice.calculation');

	Route::post('/marketing-orders-sales-invoice-save', [MarketingOrderSalesController::class, "invoiceSave"])->name('marketing.orders.sales.invoice.save');

	Route::get('/marketing-request-delivery-challan', [MarketingOrderDeliveryChallanController::class, "index"])->name('marketing.orders.delivery.challan');
	Route::post('/marketing-request-delivery-challan-ajax', [MarketingOrderDeliveryChallanController::class, "ajax"])->name('marketing.orders.delivery.challan.ajax');

	//Route::get('/marketing-request-delivery-challan-detail', [MarketingOrderDeliveryChallanController::class, "detail"])->name('marketing.orders.delivery.challan.detail');
	// END MARKETING REQUEST SALES

	Route::get('/marketing-request-sales-2', [MarketingOrderSalesController::class, "index2"])->name('marketing.orders.sales2');
	Route::post('/marketing-request-sales-2-ajax', [MarketingOrderSalesController::class, "ajax2"])->name('marketing.orders.sales2.ajax');
	Route::get('/marketing-request-sales-2-detail', [MarketingOrderSalesController::class, "detail2"])->name('marketing.orders.sales2.detail');
	Route::post('/marketing-orders-sales-2-invoice-save', [MarketingOrderSalesController::class, "invoiceSave2"])->name('marketing.orders.sales2.invoice.save');

	Route::get('/marketing-request-rejected', [MarketingOrderSalesController::class, "index3"])->name('marketing.orders.rejected');
	Route::post('/marketing-request-rejected-ajax', [MarketingOrderSalesController::class, "ajax3"])->name('marketing.orders.rejected.ajax');
	Route::get('/marketing-request-rejected-detail', [MarketingOrderSalesController::class, "detail3"])->name('marketing.orders.rejected.detail');

	//// START MARKETING REUQST CHALLAN MANAGEMENT

	Route::get('/marketing-request-delivery-challan-raised', [MarketingDeliveryChallanManagementController::class, "raised"])->name('marketing.orders.delivery.challan.raised');
	Route::post('/marketing-request-delivery-challan-raised-ajax', [MarketingDeliveryChallanManagementController::class, "raisedAjax"])->name('marketing.orders.delivery.challan.raised.ajax');

	Route::get('/marketing-request-delivery-challan-mark-as-packed', [MarketingDeliveryChallanManagementController::class, "markAsPacked"])->name('marketing.orders.delivery.challan.markaspacked');

	Route::get('/marketing-request-delivery-challan-packed', [MarketingDeliveryChallanManagementController::class, "packed"])->name('marketing.orders.delivery.challan.packed');
	Route::post('/marketing-request-delivery-challan-packed-ajax', [MarketingDeliveryChallanManagementController::class, "packedAjax"])->name('marketing.orders.delivery.challan.packed.ajax');
	Route::get('/marketing-request-delivery-challan-search-courier', [MarketingDeliveryChallanManagementController::class, "searchCourier"])->name('marketing.orders.delivery.challan.search.courier');
	Route::post('/marketing-request-delivery-challan-mark-as-dispatch', [MarketingDeliveryChallanManagementController::class, "markAsDispatch"])->name('marketing.orders.delivery.challan.markasdispatch');

	Route::get('/marketing-request-is-stock-available', [MarketingDeliveryChallanManagementController::class, "isAvailableStock"])->name('marketing.request.is.stock.available');
	Route::get('/marketing-request-delivery-challan-dispatched', [MarketingDeliveryChallanManagementController::class, "dispatched"])->name('marketing.orders.delivery.challan.dispatched');
	Route::post('/marketing-request-delivery-challan-dispatched-ajax', [MarketingDeliveryChallanManagementController::class, "dispatchedAjax"])->name('marketing.orders.delivery.challan.dispatched.ajax');

	Route::get('/marketing-request-delivery-challan-detail', [MarketingDeliveryChallanManagementController::class, "detail"])->name('marketing.orders.delivery.challan.detail');

	//// END MARKETING REUQST CHALLAN MANAGEMENT

	/// INQUIRY ADD POINT
	Route::get('/reward-point-add', [CRMRewardPointController::class, "index"])->name('reward.point.add');
	Route::get('/reward-point-search-user', [CRMRewardPointController::class, "searchUser"])->name('reward.search.user');
	Route::get('/reward-point-search-inquery', [CRMRewardPointController::class, "searchInquiry"])->name('reward.search.inquiry');
	Route::post('/reward-point-add-process', [CRMRewardPointController::class, "addProcess"])->name('reward.add.process');
	/// END INQUIERY POINT

	/////////////////////////////////////////////////////// START AXONE DEVELOPMENT ///////////////////////////////////////////////////////

	/// START APP USER MASTER FORMS
	Route::get('/quot-app-user-master', [QuotAppUserMasterController::class, "index"])->name('quot.app.user.master');
	Route::post('/quot-app-user-master-ajax', [QuotAppUserMasterController::class, "ajax"])->name('quot.app.user.master.ajax');
	/// END APP USER MASTER FORMS

	/// START QUOTATION COMPANY MASTER FORMS
	Route::get('/quot-company-master', [QuotCompanyMasterController::class, "index"])->name('quot.company.master');
	Route::post('/quot-company-master-ajax', [QuotCompanyMasterController::class, "ajax"])->name('quot.company.master.ajax');
	Route::post('/quot-company-master-save', [QuotCompanyMasterController::class, "save"])->name('quot.company.master.save');
	Route::get('/quot-company-master-detail', [QuotCompanyMasterController::class, "detail"])->name('quot.company.master.detail');
	Route::get('/quot-company-master-delete', [QuotCompanyMasterController::class, "delete"])->name('quot.company.master.delete');
	Route::get('/quot-company-export', [QuotCompanyMasterController::class, "export"])->name('quot.company.export');
	/// END QUOTATION COMPANY MASTER FORMS

	/// START QUOTATION ITEM CATEGORY MASTER FORMS
	Route::get('/quot-itemcategory-master', [QuotItemCategoryMasterController::class, "index"])->name('quot.itemcategory.master');
	Route::post('/quot-itemcategory-master-ajax', [QuotItemCategoryMasterController::class, "ajax"])->name('quot.itemcategory.master.ajax');
	Route::post('/quot-itemcategory-master-save', [QuotItemCategoryMasterController::class, "save"])->name('quot.itemcategory.master.save');
	Route::get('/quot-itemcategory-master-detail', [QuotItemCategoryMasterController::class, "detail"])->name('quot.itemcategory.master.detail');
	Route::get('/quot-itemcategory-master-delete', [QuotItemCategoryMasterController::class, "delete"])->name('quot.itemcategory.master.delete');
	Route::get('/quot-itemcategory-search-category-type', [QuotItemCategoryMasterController::class, "searchCategoryType"])->name('quot.itemcategory.search.category.type');
	Route::get('/quot-category-export', [QuotItemCategoryMasterController::class, "export"])->name('quot.category.export');
	/// END QUOTATION ITEM CATEGORY MASTER FORMS

	/// START QUOTATION ITEM GROUP MASTER FORMS
	Route::get('/quot-itemgroup-master', [QuotItemGroupMasterController::class, "index"])->name('quot.itemgroup.master');
	Route::post('/quot-itemgroup-master-ajax', [QuotItemGroupMasterController::class, "ajax"])->name('quot.itemgroup.master.ajax');
	Route::post('/quot-itemgroup-master-save', [QuotItemGroupMasterController::class, "save"])->name('quot.itemgroup.master.save');
	Route::get('/quot-itemgroup-master-detail', [QuotItemGroupMasterController::class, "detail"])->name('quot.itemgroup.master.detail');
	Route::get('/quot-itemgroup-master-delete', [QuotItemGroupMasterController::class, "delete"])->name('quot.itemgroup.master.delete');
	Route::get('/quot-itemgroup-export', [QuotItemGroupMasterController::class, "export"])->name('quot.itemgroup.export');
	/// END QUOTATION ITEM GROUP MASTER FORMS

	/// START QUOTATION ITEM SUB GROUP MASTER FORMS
	Route::get('/quot-itemsubgroup-master', [QuotItemSubGroupMasterController::class, "index"])->name('quot.itemsubgroup.master');
	Route::post('/quot-itemsubgroup-master-ajax', [QuotItemSubGroupMasterController::class, "ajax"])->name('quot.itemsubgroup.master.ajax');
	Route::post('/quot-itemsubgroup-master-save', [QuotItemSubGroupMasterController::class, "save"])->name('quot.itemsubgroup.master.save');
	Route::get('/quot-itemsubgroup-master-detail', [QuotItemSubGroupMasterController::class, "detail"])->name('quot.itemsubgroup.master.detail');
	Route::get('/quot-itemsubgroup-search-company', [QuotItemSubGroupMasterController::class, "searchCompany"])->name('quot.itemsubgroup.search.company');
	Route::get('/quot-itemsubgroup-search-group', [QuotItemSubGroupMasterController::class, "searchGroup"])->name('quot.itemsubgroup.search.group');
	Route::get('/quot-itemsubgroup-master-delete', [QuotItemSubGroupMasterController::class, "delete"])->name('quot.itemsubgroup.master.delete');
	Route::get('/quot-itemsubgroup-search-manager', [QuotItemSubGroupMasterController::class, "searchManager"])->name('quot.itemsubgroup.search.manager');
	Route::get('/quot-itemsubgroup-export', [QuotItemSubGroupMasterController::class, "export"])->name('quot.itemsubgroup.export');
	/// END QUOTATION ITEM SUB GROUP MASTER FORMS

	/// START QUOTATION ITEM MASTER FORMS
	Route::get('/quot-item-master', [QuotItemMasterController::class, "index"])->name('quot.item.master');
	Route::post('/quot-item-master-ajax', [QuotItemMasterController::class, "ajax"])->name('quot.item.master.ajax');
	Route::post('/quot-item-master-save', [QuotItemMasterController::class, "save"])->name('quot.item.master.save');
	Route::get('/quot-item-master-detail', [QuotItemMasterController::class, "detail"])->name('quot.item.master.detail');
	Route::get('/quot-item-search-company', [QuotItemMasterController::class, "searchCategory"])->name('quot.item.search.category');
	Route::post('/quot-item-master-upload-image-additional-info', [QuotItemMasterController::class, "uploadImageAdditionalInfo"])->name('quot.item.master.upload-image-additional-info');
	Route::get('/quot-item-master-delete', [QuotItemMasterController::class, "delete"])->name('quot.item.master.delete');
	Route::get('/quot-item-master-export', [QuotItemMasterController::class, "export"])->name('quot.item.master.export');
	/// END QUOTATION ITEM MASTER FORMS

	/// START QUOTATION ITEM PRICE MASTER FORMS
	Route::get('/quot-itemprice-master', [QuotItemPriceMasterController::class, "index"])->name('quot.itemprice.master');
	Route::post('/quot-itemprice-master-ajax', [QuotItemPriceMasterController::class, "ajax"])->name('quot.itemprice.master.ajax');
	Route::post('/quot-itemprice-master-save', [QuotItemPriceMasterController::class, "save"])->name('quot.itemprice.master.save');
	Route::get('/quot-itemprice-master-detail', [QuotItemPriceMasterController::class, "detail"])->name('quot.itemprice.master.detail');
	Route::get('/quot-itemprice-search-category-type', [QuotItemPriceMasterController::class, "searchCategoryType"])->name('quot.itemprice.search.category.type');
	Route::get('/quot-itemprice-search-multi-flow', [QuotItemPriceMasterController::class, "searchMultiFlow"])->name('quot.itemprice.search.multi.flow');
	Route::get('/quot-itemprice-search-company', [QuotItemPriceMasterController::class, "searchCompany"])->name('quot.itemprice.search.company');
	Route::get('/quot-itemprice-search-itemgroup', [QuotItemPriceMasterController::class, "searchItemGroup"])->name('quot.itemprice.search.itemgroup');
	Route::get('/quot-itemprice-search-itemsubgroup', [QuotItemPriceMasterController::class, "searchItemSubGroup"])->name('quot.itemprice.search.itemsubgroup');
	Route::get('/quot-itemprice-search-item', [QuotItemPriceMasterController::class, "searchItem"])->name('quot.itemprice.search.item');
	Route::get('/quot-itemprice-master-delete', [QuotItemPriceMasterController::class, "delete"])->name('quot.itemprice.master.delete');
	Route::get('/quot-itemprice-master-export', [QuotItemPriceMasterController::class, "export"])->name('quot.itemprice.master.export');
	Route::post('/quot-itemprice-master-update-price-excel', [QuotItemPriceMasterController::class, "updatePriceExcel"])->name('quot.itemprice.master.update.price.excel');
	Route::post('/quot-itemprice-master-price-update-ajax', [QuotItemPriceMasterController::class, "ajaxItemPriceUpdate"])->name('quot.itemprice.master.price.update.ajax');
	Route::post('/quot-itemprice-master-flow-update-ajax', [QuotItemPriceMasterController::class, "ajaxItemFlowUpdate"])->name('quot.itemprice.master.flow.update.ajax');
	Route::post('/quot-itemprice-master-filtered-price-save', [QuotItemPriceMasterController::class, "saveFilteredPrice"])->name('quot.itemprice.master.filtered.price.save');
	Route::post('/quot-itemprice-master-filtered-flow-save', [QuotItemPriceMasterController::class, "saveFilteredFlow"])->name('quot.itemprice.master.filtered.flow.save');
	Route::post('/quot-itemprice-master-saveall-price-save', [QuotItemPriceMasterController::class, "saveAllPrice"])->name('quot.itemprice.master.saveall.price.save');
	Route::post('/quot-itemprice-master-saveall-flow-save', [QuotItemPriceMasterController::class, "saveAllFlow"])->name('quot.itemprice.master.saveall.flow.save');
	/// END QUOTATION ITEMPRICE MASTER FORMS

	/// START QUOTATION LIST FORMS

	Route::get('/quot-master', [QuotationMasterController::class, "index"])->name('quot.master');
	Route::post('/quot-master-ajax', [QuotationMasterController::class, "ajax"])->name('quot.master.ajax');
	Route::post('/quot-master-quotationversiondetail-first', [QuotationMasterController::class, "versiondetailf"])->name('quot.master.quotationversiondetail.first');
	Route::post('/quot-master-quotationversiondetail-second', [QuotationMasterController::class, "versiondetails"])->name('quot.master.quotationversiondetail.second');
	Route::post('/quot-master-quotationversiondetail-third', [QuotationMasterController::class, "versiondetailt"])->name('quot.master.quotationversiondetail.third');
	Route::get('/quot-master-itemwiseprint-download', [QuotationMasterController::class, "PostItemWiseDownloadPrint"])->name('quot.master.itemwiseprint.download');

	Route::get('/quot-itemquotedetail', [QuotationDetailMasterController::class, "index"])->name('quot.itemquotedetail');
	Route::post('/quot-itemquotedetail-data', [QuotationDetailMasterController::class, "ajax"])->name('quot.itemquotedetail.data');
	Route::get('/quot-summary-data', [QuotationDetailMasterController::class, "quotationSummaryData"])->name('quot.summary.data');

	Route::post('/quot-boarddetail-data', [QuotationMasterController::class, "quot_board_detail"])->name('quot.boarddetail.data');
	Route::post('/quot-board-error-detail-data', [QuotationMasterController::class, "quot_board_error_detail"])->name('quot.board.error.detail.data');
	Route::get('/quot-board-error-delete', [QuotationMasterController::class, "delete_board_error"])->name('quot.board.error.delete');
	Route::get('/quot-board-delete', [QuotationMasterController::class, "delete_quot_board"])->name('quot.board.delete');
	Route::get('/quot-search-plate-data', [QuotationMasterController::class, "searchItemSubGroupPlate"])->name('quot.search.plate.data');
	Route::get('/quot-search-accessories-data', [QuotationMasterController::class, "searchItemSubGroupAccessories"])->name('quot.search.accessories.data');
	Route::get('/quot-search-whitelion-data', [QuotationMasterController::class, "searchItemSubGroupWhitelion"])->name('quot.search.whitelion.data');
	Route::get('/quot-search-item-brand-data', [QuotationMasterController::class, "searchItemBrandForDiscount"])->name('quot.search.item.brand.data');
	Route::get('/quot-search-item-data', [QuotationMasterController::class, "searchItemForDiscount"])->name('quot.search.item.data');
	Route::post('/quot-history-ajax-data', [QuotationMasterController::class, "QuotHistoryDataajax"])->name('quot.history.ajax.data');
	Route::post('/quot-show-range-data-ajax', [QuotationMasterController::class, "show_selected_range"])->name('quot.show.range.data.ajax');
	Route::post('/quot-range-change-ajax', [QuotationMasterController::class, "quot_range_change"])->name('quot.range.change.ajax');
	Route::get('/quot-search-boarditem-ajax', [QuotationMasterController::class, "quot_Search_BoardItem"])->name('quot.search.boarditem.ajax');
	Route::get('/quot-quot-item-discountdetail-data', [QuotationMasterController::class, "add_discount_model"])->name('quot.quot.item.discountdetail.data');
	Route::post('/quot-quot-new-board-item-save', [QuotationMasterController::class, "newBoardItemSave"])->name('quot.quot.new.board.item.save.data');
	Route::get('/quot-status-change-ajax', [QuotationMasterController::class, "changeQuotationStatus"])->name('quot.status.change.ajax');
	Route::get('/quot-search-subgroup-discount-change', [QuotationMasterController::class, "searchSubGroupForUpdateDiscount"])->name('quot.search.subgroup.discount.change');
	Route::get('/quot-doard-status-data', [QuotationMasterController::class, "change_board_satus"])->name('quot.doard.status.data');

	Route::get('/quot-quot-item-discount-save', [QuotationMasterController::class, "discountitemSave"])->name('quot.quot.item.discount.save');
	Route::get('/quot-quot-item-discount-brandwise-save', [QuotationMasterController::class, "discountBrandWiseSave"])->name('quot.quot.item.discount.brandwise.save');
	Route::get('/quot-quot-item-newqty-save', [QuotationMasterController::class, "newqtySave"])->name('quot.quot.item.newqty.save');

	Route::get('/quot-search-board-addons', [QuotationMasterController::class, "searchBoardAddons"])->name('quot.search.board.addons'); // new update
	Route::get('/quot-get-item-price', [QuotationMasterController::class, "getItemPriceOnChange"])->name('quot.get.item.price'); // new update
	Route::post('/quot-save-board-addon', [QuotationMasterController::class, "BoardAddonsSave"])->name('quot.save.board.addon'); // new update

	Route::get('/quot-get-brand-list', [QuotationMasterController::class, "GetBrandList"])->name('quot.get.brand.list'); // new update
	Route::post('/quot-discount-approved-or-reject', [QuotationMasterController::class, "SaveDiscountApprovedOrReject"])->name('quot.discount.approved.or.reject'); // new update

	// START QUOT UPDATE
	Route::get('/quotation-convertation', [QuotationConvertationController::class, "index"])->name('quotation.convertation');
	Route::post('/PostQuotItemList', [QuotationConvertationController::class, 'PostQuotItemList']);
	Route::post('/PostQuotRoomList', [QuotationConvertationController::class, 'PostQuotRoomList'])->name('quotation.room.list');
	Route::post('/PostQuotRoomWiseBoardList', [QuotationConvertationController::class, 'PostQuotRoomWiseBoardList'])->name('quotation.room.wise.board.list');
	Route::post('/PostQuotRoomNBoardSave', [QuotationConvertationController::class, 'PostQuotRoomNBoardSave'])->name('quotation.room.and.board.save');

	Route::get('/board-detail', [QuotationConvertationController::class, "boardDetil"])->name('board.detail');
	Route::get('/quot-convertion-search-plate', [QuotationConvertationController::class, "searchPlate"])->name('quot.convertion.search.plate');
	Route::get('/quot-convertion-search-accessories', [QuotationConvertationController::class, "searchAccessories"])->name('quot.convertion.search.accessories');
	Route::get('/quot-convertion-search-whitelion-model', [QuotationConvertationController::class, "searchWhitelionModel"])->name('quot.convertion.search.whitelion.model');
	Route::get('/quot-convertion-search-addon', [QuotationConvertationController::class, "searchAddon"])->name('quot.convertion.search.addon');
	// END QUOT UPDATE

	/// END QUOTATION LIST FORMS

	// START QUOT APP MASTER FORMS
	Route::get('/quot-app-master', [QuotAppMasterController::class, "index"])->name('quot.app.master');
	Route::post('/quot-app-master-ajax', [QuotAppMasterController::class, "ajax"])->name('quot.app.master.ajax');
	Route::post('/quot-app-master-save', [QuotAppMasterController::class, "save"])->name('quot.app.master.save');
	Route::get('/quot-app-master-detail', [QuotAppMasterController::class, "detail"])->name('quot.app.master.detail');
	Route::get('/quot-app-master-delete', [QuotAppMasterController::class, "delete"])->name('quot.app.master.delete');
	Route::post('/quot-app-send-notification', [QuotAppMasterController::class, "sendNotificationFirebase"])->name('quot.app.send.notification');

	Route::post('/cmimport', [QuotAppMasterController::class, 'cmimport'])->name('cmimport');
	Route::get('/getlogs', [QuotAppMasterController::class, 'getlogs'])->name('getlogs');
	Route::get('/cmtruncatedata', [QuotAppMasterController::class, 'cmtruncatedata'])->name('cmtruncatedata');
	// END QUOT APP MASTER FORMS

	/// START TARGET ACHIEVEMENT FORMS
	Route::get('/target-achievement', [TargetAchievementController::class, "index"])->name('target.achievement');
	Route::post('/target-achievement-ajax', [TargetAchievementController::class, "ajax"])->name('target-achievement.ajax');
	Route::post('/target-achievement-save', [TargetAchievementController::class, "save"])->name('target.achievement.save');
	Route::get('/target-achievement-detail', [TargetAchievementController::class, "detail"])->name('target.achievement.detail');
	Route::get('/search-financial-year-ajax', [TargetAchievementController::class, "searchFinancialYear"])->name('search.financial.year.ajax');
	Route::get('/currunt-financial-year-ajax', [TargetAchievementController::class, "curruntFinancialYear"])->name('currunt.financial.year.ajax');
	Route::post('/search-joining-date-ajax', [TargetAchievementController::class, "searchJoiningDate"])->name('search.joining.date.ajax');
	Route::get('/search-target.view.type', [TargetAchievementController::class, "searchTargetViewType"])->name('search.target.view.type');
	Route::get('/search-salesperson-ajax', [TargetAchievementController::class, "searchSalesUser"])->name('search.salesperson.ajax');
	/// END TARGET ACHIEVEMENT FORMS

	/// START TARGET ACHIEVEMENT VIEW FORMS
	Route::get('/search-financial-year-target-view', [TargetViewController::class, "searchTVFinancialYear"])->name('search.financial.year.target.view');
	Route::get('/search-sales-user-target-view', [TargetViewController::class, "searchTVSalesUaer"])->name('search.sales.user.target.view');
	Route::post('/target-view-data', [TargetViewController::class, "targetViewData"])->name('target.view.data');
	Route::post('/target-view-freez-save', [TargetViewController::class, "saveTargetFreez"])->name('target.view.freez.save');
	/// END TARGET ACHIEVEMENT VIEW FORMS

	/// START FINANCIAL YEAR FORMS
	Route::post('/financial-year-ajax', [YearMasterController::class, "ajax"])->name('financial.year.ajax');
	Route::post('/financial-year-save', [YearMasterController::class, "save"])->name('financial.year.save');
	Route::get('/financial-year-detail', [YearMasterController::class, "detail"])->name('financial.year.detail');
	Route::get('/financial-year-delete', [YearMasterController::class, "delete"])->name('financial.year.delete');
	/// END FINANCIAL YEAR FORMS

	/// START QUOT TYPE FORMS
	Route::post('/quot-type-ajax', [QuotTypeMasterController::class, "ajax"])->name('quot.type.ajax');
	Route::post('/quot-type-save', [QuotTypeMasterController::class, "save"])->name('quot.type.save');
	Route::get('/quot-type-detail', [QuotTypeMasterController::class, "detail"])->name('quot.type.detail');
	Route::get('/quot-type-delete', [QuotTypeMasterController::class, "delete"])->name('quot.type.delete');
	/// END QUOT TYPE FORMS

	/// START WARRANTY REGISTRATION FORMS
	Route::get('/warranty-registration-master', [WarrantyManagementController::class, "index"])->name('warranty.registration.master');
	Route::post('/warranty-registration-ajax', [WarrantyManagementController::class, "ajax"])->name('warranty.registration.ajax');
	Route::get('/warranty-registration-detail', [WarrantyManagementController::class, "detail"])->name('warranty.registration.detail');
	Route::post('/warranty-registration-save', [WarrantyManagementController::class, "save"])->name('warranty.registration.save');
	/// END WARRANTY REGISTRATION FORMS

	/// START SERVICE PRODUCT TAG MASTER FORMS
	Route::get('/service-product-tag-master', [ProductTagMasterContoller::class, "index"])->name('service.product.tag.master');
	Route::post('/service-product-tag-master-save', [ProductTagMasterContoller::class, "save"])->name('service.product.tag.master.save');
	Route::post('/service-product-tag-master-ajax', [ProductTagMasterContoller::class, "ajax"])->name('service.product.tag.master.ajax');
	Route::get('/service-product-tag-master-delete', [ProductTagMasterContoller::class, "delete"])->name('service.product.tag.master.delete');
	Route::get('/service-product-tag-master-detail', [ProductTagMasterContoller::class, "detail"])->name('service.product.tag.master.detail');
	/// END SERVICE PRODUCT TAG MASTER FORMS

	/// START DISCOUNT FLOW MASTER FORMS
	Route::get('/quot-discount-master', [QuotDiscountMasterController::class, "index"])->name('quot.discount.master');
	Route::get('/quot-discount-user-type-search', [QuotDiscountMasterController::class, "searchUserType"])->name('quot.discount.user.type.search');
	Route::get('/quot-discount-user-search', [QuotDiscountMasterController::class, "searchUser"])->name('quot.discount.user.search');
	Route::post('/quot-discount-save', [QuotDiscountMasterController::class, "saveProcess"])->name('quot.discount.save');
	Route::post('/quot-discount-ajax', [QuotDiscountMasterController::class, "ajax"])->name('quot.discount.ajax');
	Route::get('/quot-discount-delete', [QuotDiscountMasterController::class, "delete"])->name('quot.discount.delete');
	Route::get('/quot-discount-detail', [QuotDiscountMasterController::class, "detail"])->name('quot.discount.detail');
	/// END DISCOUNT FLOW MASTER FORMS

	/// START SERVICE WAREHOUSE MASTER FORMS
	Route::get('/service-warehouse-master', [WarehouseMasterContoller::class, "index"])->name('service.warehouse.master');
	Route::post('/service-warehouse-master-save', [WarehouseMasterContoller::class, "save"])->name('service.warehouse.master.save');
	Route::post('/service-warehouse-master-ajax', [WarehouseMasterContoller::class, "ajax"])->name('service.warehouse.master.ajax');
	Route::get('/service-warehouse-master-delete', [WarehouseMasterContoller::class, "delete"])->name('service.warehouse.master.delete');
	Route::get('/service-warehouse-master-detail', [WarehouseMasterContoller::class, "detail"])->name('service.warehouse.master.detail');
	Route::get('/service-warehouse-master-export', [WarehouseMasterContoller::class, "export"])->name('service.warehouse.master.export');

	Route::get('/warehouse-search-city', [WarehouseMasterContoller::class, "searchCity"])->name('service.warehouse.search.city');
	Route::get('/warehouse-search-state', [WarehouseMasterContoller::class, "searchState"])->name('service.warehouse.search.state');
	Route::get('/warehouse-search-country', [WarehouseMasterContoller::class, "searchCountry"])->name('service.warehouse.search.country');
	/// END SERVICE WAREHOUSE MASTER FORMS

	// START USERS - SERVICE EXECUTIVE
	Route::get('/users-service-executive', [UsersServiceExecutiveController::class, "index"])->name('users.service.executive');
	Route::post('/users-service-executive-ajax', [UsersServiceExecutiveController::class, "ajax"])->name('users.service.executive.ajax');
	Route::get('/users-service-executive-export', [UsersServiceExecutiveController::class, "export"])->name('users.service.executive.export');
	// END  USERS -  SERVICE EXECUTIVE

	// START USERS - RECEPTION
	Route::get('/users-reception', [UsersReceptionController::class, "index"])->name('users.reception');
	Route::post('/users-reception-ajax', [UsersReceptionController::class, "ajax"])->name('users.reception.ajax');
	Route::get('/users-reception-export', [UsersReceptionController::class, "export"])->name('users.reception.export');
	// END  USERS - RECEPTION

	// START USERS - CRE
	Route::get('/users-cre', [CreUsersController::class, "index"])->name('users.cre');
	Route::post('/users-cre-ajax', [CreUsersController::class, "ajax"])->name('users.cre.ajax');
	// END  USERS - CRE

	// START SERVICE HIERARCHY
	Route::get('/service-hierarchy', [MasterServiceHierarchyController::class, "index"])->name('service.hierarchy');
	Route::get('/service-hierarchy-search', [MasterServiceHierarchyController::class, "search"])->name('service.hierarchy.search');
	Route::post('/service-hierarchy-ajax', [MasterServiceHierarchyController::class, "ajax"])->name('service.hierarchy.ajax');
	Route::post('/service-hierarchy-save', [MasterServiceHierarchyController::class, "saveProcess"])->name('service.hierarchy.save');

	Route::get('/service-hierarchy-detail', [MasterServiceHierarchyController::class, "detail"])->name('service.hierarchy.detail');
	Route::get('/service-hierarchy-delete', [MasterServiceHierarchyController::class, "delete"])->name('service.hierarchy.delete');
	// END  SERVICE HIERARCHY

	// START WHATSAPP API
	Route::get('/search-whatsapp-template', [WhatsappApiContoller::class, "getMessageTemplate"])->name('search.whatsapp.template');
	Route::post('/send-whatsapp-template-message', [WhatsappApiContoller::class, "sendTemplateMessage"])->name('send.whatsapp.template.message');
	// END WHATSAPP API

	// Route::post('/master-search-ajax', [MasterSearchController::class, "ajax"])->name('master.search.ajax');
	Route::post('/master-search-sales-user-ajax', [MasterSearchController::class, "SalesUserAjax"])->name('master.search.sales.user.ajax');
	Route::post('/master-search-arc-ajax', [MasterSearchController::class, "ArchitectAjax"])->name('master.search.arc.ajax');
	Route::post('/master-search-ele-ajax', [MasterSearchController::class, "ElectricianAjax"])->name('master.search.ele.ajax');
	Route::post('/master-search-lead-ajax', [MasterSearchController::class, "LeadAjax"])->name('master.search.lead.ajax');
	Route::post('/master-search-deal-ajax', [MasterSearchController::class, "DealAjax"])->name('master.search.deal.ajax');

	// WAERHOSE SHOW AND SAVE
	Route::get('wearhouse', [WarehouseController::class, 'index'])->name('wearhouse');
	Route::post('wearhouse-ajax', [WarehouseController::class, 'ajax'])->name('wearhouse.ajax');
	Route::post('wearhouse-save', [WarehouseController::class, 'save'])->name('wearhouse.save');
	Route::get('wearhouse-detail', [WarehouseController::class, 'detail'])->name('wearhouse.detail');
	// WAERHOUSE END

	// REWARD PROGRAM START
	Route::get('/reward', [RewardController::class, 'index'])->name('reward');
	Route::post('/reward-list-ajax', [RewardController::class, 'getListAjax'])->name('reward.list.ajax');
	Route::get('/reward-detail', [RewardController::class, 'getDetail'])->name('reward.detail');
	Route::get('/reward-task-detail', [RewardController::class, 'getTaskDetail'])->name('reward.task.detail');
	Route::get('/reward-call-detail', [RewardController::class, 'getCallDetail'])->name('reward.call.detail');
	Route::get('/reward-company-admin-all-task', [RewardController::class, 'getCompanyAdminAllTask'])->name('reward.company.admin.all.task');
	// REWARD PROGRAM END

	/// START REVIEW MASTER FORMS
	Route::get('/review-master', [ReviewController::class, "index"])->name('review.master');
	Route::post('/review-master-ajax', [ReviewController::class, "ajax"])->name('review.master.ajax');
	Route::post('/review-master-save', [ReviewController::class, "save"])->name('review.master.save');
	Route::get('/review-master-detail', [ReviewController::class, "detail"])->name('review.master.detail');
	/// END REVIEW MASTER FORMS

	/// START NOTIFICATION MASTER FORMS
	Route::get('/notification-master', [PromotionalNotification::class, "index"])->name('notification.master');
	Route::post('/notification-master-ajax', [PromotionalNotification::class, "ajax"])->name('notification.master.ajax');
	Route::post('/notification-master-save', [PromotionalNotification::class, "save"])->name('notification.master.save');
	Route::get('/notification-master-detail', [PromotionalNotification::class, "detail"])->name('notification.master.detail');
	Route::get('/search-notification-usertype', [PromotionalNotification::class, "searchUserType"])->name('search.notification.usertype');
	/// END NOTIFICATION MASTER FORMS

	/// START BANNER MASTER FORMS
	Route::get('/banner-master', [BannerController::class, "index"])->name('banner.master');
	Route::post('/banner-master-ajax', [BannerController::class, "ajax"])->name('banner.master.ajax');
	Route::post('/banner-master-save', [BannerController::class, "save"])->name('banner.master.save');
	Route::get('/banner-master-detail', [BannerController::class, "detail"])->name('banner.master.detail');
	/// END BANNER MASTER FORMS

	/// START DEVICE BINDING MASTER FORMS
	Route::get('/device-binding-master', [DeviceBindingController::class, "index"])->name('device.binding.master');
	Route::post('/device-binding-master-ajax', [DeviceBindingController::class, "ajax"])->name('device.binding.master.ajax');
	Route::post('/device-binding-master-save', [DeviceBindingController::class, "save"])->name('device.binding.master.save');
	Route::get('/device-binding-master-detail', [DeviceBindingController::class, "detail"])->name('device.binding.master.detail');
	Route::get('/device-binding-search-user', [DeviceBindingController::class, "searchUser"])->name('device.binding.search.user');
	/// END DEVICE BINDING MASTER FORMS

	/// START MOVE ASSIGNEE
	Route::get('/new-move-assignee', [NewMoveAssigneeController::class, "index"])->name('new.move.assignee');
	Route::get('/new-move-assignee-salese-user', [NewMoveAssigneeController::class, "searchAssignedUser"])->name('new.search.from.assignee.user');
	Route::post('/new-move-assignee-ajax', [NewMoveAssigneeController::class, "ajax"])->name('new.move.assignee.ajax');
	Route::get('/new-move-assignee-status', [NewMoveAssigneeController::class, "status"])->name('new.move.assignee.status');
	Route::post('/new-move-assignee-save', [NewMoveAssigneeController::class, "save"])->name('new.move.assignee.save');
	/// END MOVE ASSIGNEE

	/// START QUOTATION COMPANY MASTER FORMS
	Route::get('/crm-lead-report', [LeadWorkReportController::class, "index"])->name('crm.lead.report');
	Route::post('/crm-lead-report-ajax', [LeadWorkReportController::class, "ajax"])->name('crm.lead.report.ajax');
	Route::post('/crm-lead-report-export', [LeadWorkReportController::class, "export"])->name('crm.lead.report.export');
	Route::get('/report-export-search-user', [LeadWorkReportController::class, "searchUser"])->name('report.export.search.user');
	/// END QUOTATION COMPANY MASTER FORMS

	/// START QUOTATION COMPANY MASTER FORMS
	Route::get('/crm-marketing-lead', [MarketingLeadController::class, "index"])->name('crm.marketing.lead');
	Route::post('/crm-marketing-lead-ajax', [MarketingLeadController::class, "ajax"])->name('crm.marketing.lead.ajax');
	Route::post('/crm-marketing-lead-save', [MarketingLeadController::class, "save"])->name('crm.marketing.lead.save');
	// Route::post('/crm-marketing-lead-export', [MarketingLeadController::class, "export"])->name('crm.marketing.lead.export');
	/// END QUOTATION COMPANY MASTER FORMS

	/// START QUOTATION COMPANY MASTER FORMS
	Route::get('/crm-marketing-lead-report', [MarketingLeadReportController::class, "index"])->name('crm.marketing.lead.report');
	Route::post('/crm-marketing-lead-report-ajax', [MarketingLeadReportController::class, "ajax"])->name('crm.marketing.lead.report.ajax');

	/// END QUOTATION COMPANY MASTER FORMS


	// START INVENTORY SYNC
	Route::get('/inventory-sync', [InventorySyncController::class, "index"])->name('inventory.sync');
	Route::post('/inventory-sync-ajax', [InventorySyncController::class, "ajax"])->name('inventory.sync.ajax');
	Route::post('/inventory-sync-save', [InventorySyncController::class, "save"])->name('inventory.sync.save');
	Route::get('/inventory-sync-search-quot-item', [InventorySyncController::class, "searchQuotItem"])->name('inventory.sync.search.quot.item');
	Route::get('/inventory-sync-search-so-item', [InventorySyncController::class, "searchSoItem"])->name('inventory.sync.search.so.item');
	// END INVENTORY SYNC


	/// START ADD BILL

	Route::get('/bill-add', [BillController::class, "add"])->name('bill.add');
	Route::get('/bill-pdf', [BillController::class, "testPDF"])->name('bill.pdf');
	Route::post('/bill-calculation', [BillController::class, "calculation"])->name('bill.calculation');
	// Route::get('/bill-search-city', [BillController::class, "searchCity"])->name('bill.search.city');
	Route::get('/bill-search-channel-partner', [BillController::class, "searchChannelPartner"])->name('bill.search.channel.partner');
	Route::get('/bill-channel-partner-detail', [BillController::class, "channelPartnerDetail"])->name('bill.channel.partner.detail');
	Route::get('/bill-search-product', [BillController::class, "searchProduct"])->name('bill.search.product');
	Route::get('/bill-product-detail', [BillController::class, "productDetail"])->name('bill.product.detail');
	Route::post('/bill-save', [BillController::class, "save"])->name('bill.save');
	Route::get('/bill-cancel', [BillController::class, "cancel"])->name('bill.cancel');
	Route::post('/bill-created-save', [BillController::class, "createdSave"])->name('bill.created.save');

	/// END ADD BILL

	// BILL
	Route::get('/bill', [BillController::class, "index"])->name('bill');
	Route::post('/bill-ajax', [BillController::class, "ajax"])->name('bill.ajax');
	Route::get('/bill-detail', [BillController::class, "detail"])->name('bill.detail');
	Route::get('/bill-invoice-list', [BillController::class, "invoiceList"])->name('bill.invoice.list');
	Route::post('/bill-invoice-ajax', [BillController::class, "invoiceListAjax"])->name('bill.invoice.list.ajax');
	Route::get('/bill-invoice-detail', [BillController::class, "invoiceDetail"])->name('bill.invoice.detail');
	Route::post('/bill-export', [BillController::class, "export"])->name('bill.export');

	// END BILL

	/////////////////////////////////////////////////////// END AXONE DEVELOPMENT ///////////////////////////////////////////////////////

});
