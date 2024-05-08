<?php

namespace App\Http\Controllers;

use App\Models\Architect;
use App\Models\CityList;
use App\Models\CRMHelpDocument;
use App\Models\CRMLog;
use App\Models\StateList;
use App\Models\DebugLog;
use App\Models\Electrician;
use App\Models\GiftProduct;
use App\Models\GiftProductOrder;
use App\Models\Inquiry;
use App\Models\InquiryLog;
use App\Models\InquiryQuestionAnswer;
use App\Models\GiftProductOrderItem;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ChannelPartner;
// use Excel;
use App\Models\InquiryQuestionOption;
use App\Models\InquiryUpdate;
use App\Models\InvoiceItem;
use App\Models\ProductInventory;
use App\Models\User;

use Illuminate\Support\Facades\Auth;

//use Session;

class VersionUpdateController extends Controller
{

	public function __construct()
	{

		$this->middleware(function ($request, $next) {

			$tabCanAccessBy = array(0, 1);
			ini_set('memory_limit', '-1');

			if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
				return redirect()->route('dashboard');
			}

			return $next($request);
		});
	}

	public function index()
	{

		// echo Config::get('app.env');
		// die;
		//$this->joiningBonus();
		//$this->updateExistingInquiry();

		//$this->importMondayDotCom();
		//$this->updateCountUpdate();

		//$this->updateAnswerDateTime();

		//$this->recalculculateArchitectInquiry();
		// die;
		//$this->uploadDataOnDigitalOcean();

		//$this->changeFileName1();
		//$this->changeFileName2();
		//$this->changeFileName3();
		//$this->changeFileName4();

		//$this->changeFileName5();
		//$this->setPrimeNoNPrimeDate();.
		//$this->setPrivilegeUserType();
		// DONE

		//$this->recalculculateCRM();
		//$this->recalculculateCRM2();
		//$this->updateStageDateTime();

		//$this->updateInvoiceQty();

		//$this->recalculateTotalInquiry();
		//$this->updateCreatedBy();
		//$this->updateMaterailSendDateTime();
		//$this->giftProductItemOrderUpdate();
		//$this->partialOrderCancel();
		//$this->exportChannelPartner();

		$this->updateCIty();
		//$this->accountCreate();
		echo "DONE";
	}

	function updateCIty()
	{

		$file = fopen("region_name.csv", "r");

		while ($row = fgetcsv($file)) {




			$smallCity = strtolower($row[4]);
			$smallState = strtolower($row[3]);

			$stateList = StateList::whereRaw('LOWER(name)=?', [$smallState])->first();
			if ($stateList) {
			} else {

				$StateList = new StateList();
				$StateList->country_id = 1;
				$StateList->name = $row[3];
				$StateList->save();
			}
		}

		fclose($file);

		$file = fopen("region_name.csv", "r");

		while ($row = fgetcsv($file)) {




			$smallCity = strtolower($row[4]);
			$smallState = strtolower($row[3]);

			$stateList = StateList::whereRaw('LOWER(name)=?', [$smallState])->first();
			if ($stateList) {

				$CityList = CityList::whereRaw('LOWER(name)=?', [$smallCity])->first();
				if ($CityList) {
				} else {

					$CityList = new CityList();
					$CityList->name = $row[4];
					$CityList->country_id = 1;
					$CityList->state_id = $stateList->id;
					$CityList->save();
				}

				// echo '<br>';
				// print_r($row);
			} else {
				echo "State Not : " . $smallState;
			}
		}

		fclose($file);
	}


	function accountCreate()
	{

		//CONVERTED TO DEAL
		$Inquiry = Inquiry::whereIn('status', [5, 6, 7, 8, 13, 9, 14, 12, 10])->get();
		$Inquiry = json_decode(json_encode($Inquiry), true);
		foreach ($Inquiry as $key => $value) {

			$user = User::where('phone_number', $value['phone_number'])->first();
			if ($user) {
				echo $user->id . " - " . $user['type'];
				echo '<br>';
			}
		}
	}



	function exportChannelPartner()
	{

		$ChannelPartner = ChannelPartner::where('sale_persons', 'like', '%,%')->get();
		$ChannelPartner = json_decode(json_encode($ChannelPartner), true);

		echo "UserId,Firm Name,Sales Person Id, Sales Persons";
		echo '<br>';

		foreach ($ChannelPartner as $key => $value) {


			$sale_persons = explode(",", $value['sale_persons']);
			$Users = array();
			foreach ($sale_persons as $keyU => $valueU) {
				$User = User::find($valueU);
				$Users[] = $User->first_name . " " . $User->last_name;
			}
			$Users = implode("|", $Users);



			echo $value['user_id'] . "," . $value['firm_name'] . "," . $value['sale_persons'] . "," . $Users;
			echo '<br>';
		}
	}

	function partialOrderCancel()
	{

		// $Invoices = Invoice::select('id')->get();
		// $Invoices = json_decode(json_encode($Invoices), true);

		// foreach ($Invoices as $key => $value) {

		// 	$Invoice = Invoice::find($value['id']);

		// 	$InvoiceItem = InvoiceItem::where('invoice_id', $value['id'])->get();
		// 	$total_qty = 0;
		// 	foreach ($InvoiceItem as $key1 => $value1) {
		// 		$total_qty = $total_qty + $value1->qty;
		// 	}
		// 	$Invoice->total_qty = $total_qty;
		// 	$Invoice->save();
		// }



		$OrderList = Order::select('id')->get();
		$OrderList = json_decode(json_encode($OrderList), true);



		foreach ($OrderList as $key => $value) {

			$Order = Order::find($value['id']);
			$Invoice = Invoice::where('order_id', $Order->id)->whereIn('status', [2, 3])->get();


			$dispatched_total_payable = 0;
			foreach ($Invoice as $key1 => $value1) {
				$dispatched_total_payable = $dispatched_total_payable + $value1->total_payable;
			}
			$Order->dispatched_total_payable = $dispatched_total_payable;





			$dispatched_total_qty = 0;

			foreach ($Invoice as $key1 => $value1) {

				$InvoiceItem = InvoiceItem::where('invoice_id', $value1->id)->get();
				foreach ($InvoiceItem as $key2 => $value2) {
					$dispatched_total_qty = $dispatched_total_qty + $value2->qty;
				}
			}

			$Order->dispatched_total_qty = $dispatched_total_qty;



			if ($Order->status == 4) {
				$Order->cancelled_total_qty = $Order->total_qty;
				$Order->is_cancelled = 1;

				$OrederItems = OrderItem::where('order_id', $Order->id)->get();
				foreach ($OrederItems as $key3 => $value3) {
					$OrderItem = OrderItem::find($value3->id);
					$OrderItem->cancelled_qty = $OrderItem->qty;
					$OrderItem->save();
				}
			} else {

				$Order->is_cancelled = 0;
			}

			$Order->save();
			$actual_total_mrp_minus_disocunt = 0;
			if ($Order->status != 4 && $Order->is_cancelled == 1) {

				$Invoices = Invoice::where('order_id', $Order->id)->where('is_cancelled', 0)->get();
				$actual_total_mrp_minus_disocunt = 0;

				foreach ($Invoices as $key => $value) {
					$actual_total_mrp_minus_disocunt = $actual_total_mrp_minus_disocunt + $value['total_mrp_minus_disocunt'];
				}

				$Order->actual_total_mrp_minus_disocunt = $actual_total_mrp_minus_disocunt;
				$Order->save();
			} else if ($Order->status == 4) {

				$Order->actual_total_mrp_minus_disocunt = 0;
				$Order->save();
			} else {
				$Order->actual_total_mrp_minus_disocunt = $Order->total_mrp_minus_disocunt;
				$Order->save();
			}
		}


		// / Invoice total qty == DONE
		// dispatched_total_payable --- DONE
		// dispatched_total_qty --- DONE
		// cancelled_total_qty -- DONE
		// is_cancelled  --- DONE

		// OrederItems: dispatched_qty
		// OrederItems: cancelled_qty -- DONE

		// $OrederItems = OrderItem::select('id', 'order_id')->get();
		// $OrederItems = json_decode(json_encode($OrederItems), true);

		// foreach ($OrederItems  as $key => $value) {

		// 	$OrederItem = OrderItem::find($value['id']);
		// 	$Invoices = Invoice::where('order_id', $value['order_id'])->whereIn('status', [2, 3])->get();
		// 	$dispatched_qty = 0;

		// 	foreach ($Invoices as $key3 => $value3) {
		// 		$InvoiceItems = InvoiceItem::where('invoice_id', $value3->id)->where('order_item_id', $value['id'])->get();
		// 		foreach ($InvoiceItems as $key4 => $value4) {

		// 			$dispatched_qty = $dispatched_qty + $value4->qty;
		// 		}
		// 	}


		// 	$OrederItem->dispatched_qty = $dispatched_qty;
		// 	$OrederItem->save();
		// }
	}

	function giftProductItemOrderUpdate()
	{
		$GiftProductOrders = GiftProductOrder::get();

		foreach ($GiftProductOrders as $key => $value) {

			$GiftProductOrder = GiftProductOrder::find($value->id);
			$giftProductItems = GiftProductOrderItem::where('gift_product_order_id', $value->id)->get();
			$totalItemValue = 0;

			foreach ($giftProductItems as $key2 => $value2) {

				$GiftProduct = GiftProduct::find($value2->gift_product_id);
				if ($GiftProduct) {
					$GiftProductOrderItem = GiftProductOrderItem::find($value2->id);
					$GiftProductOrderItem->item_value = $GiftProduct->price;
					$GiftProductOrderItem->total_item_value =  $GiftProduct->price * $GiftProductOrderItem->qty;
					$GiftProductOrderItem->save();
					$totalItemValue = $totalItemValue + $GiftProductOrderItem->total_item_value;
				}
			}

			$GiftProductOrder->total_item_value = $totalItemValue;
			$GiftProductOrder->save();
		}
	}

	function updateMaterailSendDateTime()
	{

		$Inquires = Inquiry::select('id', 'created_at')->get();
		foreach ($Inquires as $key => $value) {

			$InquiryLog = InquiryLog::where('inquiry_id', $value->id);
			$InquiryLog->where(function ($query) {

				$query->where('description', 'like', '%to Material Sent');
				$query->orWhere('description', 'like', '%to Direct Material Sent');
			});

			$InquiryLog = $InquiryLog->orderBy('id', 'desc')->first();
			if ($InquiryLog) {

				$inquiry = Inquiry::select('id')->find($value->id);
				$inquiry->material_sent_date_time = $InquiryLog->created_at;
				$inquiry->save();
			}
		}
	}

	function updateCreatedBy()
	{

		$DebugLog = DebugLog::whereIn('name', array('user-add', 'architect-add', 'electrician-add', 'channel-partner-add'))->get();

		$createdByUsers = array();

		foreach ($DebugLog as $DK => $DV) {

			$stringExplodeByHash = explode("#", $DV->description);

			if (count($stringExplodeByHash) > 1) {

				$stringExplodeByHash2 = explode("(", $stringExplodeByHash[1]);
				$userId = $stringExplodeByHash2[0];
				$user = User::find($userId);
				$user->created_by = $DV->user_id;
				$user->save();
			}
		}
	}

	function recalculateTotalInquiry()
	{

		$Architects = Architect::select('user_id')->get();
		foreach ($Architects as $key => $value) {

			architectInquiryCalculation($value->user_id);
		}

		$Electricians = Electrician::select('user_id')->get();
		foreach ($Electricians as $key => $value) {

			elecricianInquiryCalculation($value->user_id);
		}
	}

	function updateInvoiceQty()
	{

		$InvoiceItems = InvoiceItem::select('id', 'qty')->get();
		foreach ($InvoiceItems as $key => $valu) {
			$InvoiceItem = InvoiceItem::find($valu->id);
			$InvoiceItem->pending_packed_qty = $valu->qty;
			$InvoiceItem->save();
		}
		echo "InvoiceItem";
	}

	function setPrivilegeUserType()
	{

		$Users = User::select('id', 'privilege')->get();
		foreach ($Users as $keyU => $valueU) {

			$User = User::select('id', 'privilege')->find($valueU->id);
			$PrivilegeJSON = array();
			$PrivilegeJSON['dashboard'] = 1;
			$User->privilege = json_encode($PrivilegeJSON);
			$User->save();
		}
	}

	function setPrimeNoNPrimeDate()
	{

		$Architects = Architect::get();
		foreach ($Architects as $key => $value) {

			$Architect = Architect::where('id', $value->id)->first();
			if ($Architect) {

				$Architect->prime_nonprime_date = $Architect->created_at;
				$Architect->save();
			}
		}
	}

	function uploadDataOnDigitalOcean()
	{
		ini_set('memory_limit', '-1');

		//$allFiles = $this->get_all_directory_and_files('s/architect');
		//$allFiles = $this->get_all_directory_and_files('s/crm-help-document');
		//$allFiles = $this->get_all_directory_and_files('s/dispatch-detail');
		//$allFiles = $this->get_all_directory_and_files('s/eway-bill');
		//$allFiles = $this->get_all_directory_and_files('s/gift-order-dispatch-detail');
		//$allFiles = $this->get_all_directory_and_files('s/gift-product');
		//$allFiles = $this->get_all_directory_and_files('s/invoice');
		//$allFiles = $this->get_all_directory_and_files('s/product');
		//$allFiles = $this->get_all_directory_and_files('s/question-attachment');
		// print_r("okay");
		// print_r($allFiles);
		echo "DONE";
		die;
	}

	function changeFileName1()
	{

		$data = CRMHelpDocument::get();
		foreach ($data as $key => $value) {

			$CRMHelpDocument = CRMHelpDocument::find($value->id);
			if ($CRMHelpDocument->file_name != "") {

				$CRMHelpDocument->file_name = "/s/crm-help-document/" . $CRMHelpDocument->file_name;
				$CRMHelpDocument->save();
			}
		}
	}

	function changeFileName2()
	{

		$data = GiftProduct::get();
		foreach ($data as $key => $value) {

			$object = GiftProduct::find($value->id);
			if ($object->image != "") {

				$object->image = "/s/gift-product/" . $object->image;
				if ($object->image2 != "") {
					$filePieces = explode(",", $object->image2);
					$image2 = array();
					foreach ($filePieces as $f => $fv) {

						if ($fv != "") {
							$image2[] = "/s/gift-product/" . $fv;
						}
					}
					$image2 = implode(",", $image2);
					$object->image2 = $image2;
					$object->save();
				}

				$object->save();
			}
		}
	}

	function changeFileName3()
	{

		$data = Inquiry::get();
		foreach ($data as $key => $value) {

			$object = Inquiry::find($value->id);
			if ($object->billing_invoice != "") {

				if ($object->billing_invoice != "") {
					$filePieces = explode(",", $object->billing_invoice);
					$image2 = array();
					foreach ($filePieces as $f => $fv) {

						if ($fv != "") {
							$image2[] = "/" . $fv;
						}
					}
					$image2 = implode(",", $image2);
					$object->billing_invoice = $image2;
				}
			}

			if ($object->quotation != "") {
				$object->quotation = "/" . $object->quotation;
			}

			$object->save();
		}
	}

	function changeFileName4()
	{

		$data = InquiryQuestionAnswer::whereIn('question_type', [7, 2])->get();
		foreach ($data as $key => $value) {

			$object = InquiryQuestionAnswer::find($value->id);
			if ($object->answer != "") {

				$filePieces = explode(",", $object->answer);
				$image2 = array();
				foreach ($filePieces as $f => $fv) {

					if ($fv != "") {
						$image2[] = "/" . $fv;
					}
				}
				$image2 = implode(",", $image2);
				$object->answer = $image2;

				$object->save();
			}
		}
	}

	function changeFileName5()
	{

		$data = ProductInventory::orderBy('id', 'asc')->get();
		$getSpaceFilePath = getSpaceFilePath("");
		foreach ($data as $key => $value) {

			$object = ProductInventory::find($value->id);
			if ($object->image != "") {

				$imageName = $object->image;

				$imageName = str_replace($getSpaceFilePath, "", $imageName);
				$imagesPieces = explode("/", $imageName);
				$imageName = end($imagesPieces);

				$object->image = "/s/product/" . $imageName;
				$object->thumb = "/s/product/thumb-" . $imageName;
				$object->save();
			}
		}
	}

	function get_all_directory_and_files($dir)
	{

		$dh = new DirectoryIterator($dir);
		// Dirctary object
		foreach ($dh as $item) {
			if (!$item->isDot()) {
				if ($item->isDir()) {
					$this->get_all_directory_and_files("$dir/$item");
				} else {

					$lastfileName = $dir . "/" . $item->getFilename();
					uploadFileOnSpaces(public_path($lastfileName), $lastfileName);

					echo $lastfileName;
					echo "<br>";
				}
			}
		}
	}

	public function recalculculateArchitectInquiry()
	{

		$Architects = Architect::get();
		foreach ($Architects as $key => $value) {

			$this->architectInquiryCalculation($value->user_id);
		}
	}

	public function recalculculateCRM()
	{

		//CRMLog::query()->where('is_manually', 0)->delete();
		CRMLog::query()->delete();
		$Architects = Architect::where('converted_prime', 1)->get();

		foreach ($Architects as $key => $value) {

			$Architect = Architect::where('id', $value->id)->first();
			if ($Architect) {
				$pointValue = 50;
				$Architect->total_point = $pointValue;
				$Architect->total_inquiry = 0;
				$Architect->total_point_current = $pointValue;
				$Architect->save();

				$debugLog = array();
				$debugLog['architect_user_id'] = $Architect->user_id;
				$debugLog['name'] = "point-gain";
				$debugLog['points'] = $pointValue;
				$debugLog['inquiry_id'] = 0;
				$debugLog['description'] = $pointValue . " Point gained joining bonus ";

				$DebugLog = new CRMLog();
				$DebugLog->user_id = 1;
				$DebugLog->for_user_id = $debugLog['architect_user_id'];
				$DebugLog->points = $debugLog['points'];
				$DebugLog->inquiry_id = $debugLog['inquiry_id'];
				$DebugLog->name = $debugLog['name'];
				$DebugLog->description = $debugLog['description'];
				$DebugLog->created_at = $Architect->created_at;
				$DebugLog->save();
			}
		}

		Inquiry::query()->update(['is_point_calculated' => 0]);
		
		$Electricians = Electrician::all();
		foreach ($Electricians as $key => $value) {

			$Electrician = Electrician::where('id', $value->id)->first();
			if ($Electrician) {
				$pointValue = 0;
				$Electrician->total_point = $pointValue;
				$Electrician->total_inquiry = 0;
				$Electrician->total_point_current = $pointValue;
				$Electrician->save();
			}
		}
	}

	function recalculculateCRM2()
	{

		$GiftProductOrder = GiftProductOrder::all();

		foreach ($GiftProductOrder as $key => $value) {

			$User = User::find($value->user_id);

			if ($User) {

				if ($User->type == 201 || $User->type == 202) {

					$Architect = Architect::where('user_id', $value->user_id)->first();

					if ($Architect) {

						$Architect->total_point_used = $Architect->total_point_used + $value->total_point_value;
						$Architect->total_point_current = $Architect->total_point_current - $value->total_point_value;
						$Architect->save();

						$debugLog = array();
						$debugLog['for_user_id'] = $value->user_id;
						$debugLog['name'] = "point-redeem";
						$debugLog['points'] = $value->total_point_value;
						$debugLog['description'] = $value->total_point_value . " Point redeem from order #" . $value->id;
						$debugLog['inquiry_id'] = 0;
						$debugLog['order_id'] = $value->id;

						$DebugLog = new CRMLog();
						$DebugLog->user_id = $value->user_id;
						$DebugLog->for_user_id = $debugLog['for_user_id'];
						$DebugLog->points = $debugLog['points'];
						$DebugLog->inquiry_id = $debugLog['inquiry_id'];
						$DebugLog->name = $debugLog['name'];
						$DebugLog->order_id = $debugLog['order_id'];
						$DebugLog->description = $debugLog['description'];
						$DebugLog->created_at = date('Y-m-d H:i:s');
						$DebugLog->save();
					}
				}
				if ($User->type == 301 || $User->type == 302) {

					$Electrician = Electrician::where('user_id', $value->user_id)->first();
					if ($Electrician) {

						$Electrician->total_point_used = $Electrician->total_point_used + $value->total_point_value;
						$Electrician->total_point_current = $Electrician->total_point_current - $value->total_point_value;
						$Electrician->save();
						$debugLog = array();
						$debugLog['for_user_id'] = $value->user_id;
						$debugLog['name'] = "point-redeem";
						$debugLog['points'] = $value->total_point_value;
						$debugLog['description'] = $value->total_point_value . " Point redeem from order #" . $value->id;
						$debugLog['inquiry_id'] = 0;
						$debugLog['order_id'] = $value->id;

						$DebugLog = new CRMLog();
						$DebugLog->user_id = $value->user_id;
						$DebugLog->for_user_id = $debugLog['for_user_id'];
						$DebugLog->points = $debugLog['points'];
						$DebugLog->inquiry_id = $debugLog['inquiry_id'];
						$DebugLog->order_id = $debugLog['order_id'];
						$DebugLog->name = $debugLog['name'];
						$DebugLog->description = $debugLog['description'];
						$DebugLog->created_at = date('Y-m-d H:i:s');
						$DebugLog->save();
					}
				}
			}
		}

		// CRMLog::query()->where('is_manually', 0)->delete();

	}

	public function updateStageDateTime()
	{

		$Inquires = Inquiry::select('id', 'created_at')->get();
		foreach ($Inquires as $key => $value) {

			$InquiryLog = InquiryLog::where('inquiry_id', $value->id)->where('name', 'stage-of-site')->orderBy('id', 'desc')->first();
			if ($InquiryLog) {

				$inquiry = Inquiry::select('id')->find($value->id);
				$inquiry->stage_of_site_date_time = $InquiryLog->created_at;
				$inquiry->save();
			} else {

				$inquiry = Inquiry::select('id')->find($value->id);
				$inquiry->stage_of_site_date_time = $value->created_at;
				$inquiry->save();
			}
		}
	}

	public function updateAnswerDateTime()
	{

		$Inquires = Inquiry::select('id', 'created_at')->get();
		foreach ($Inquires as $key => $value) {

			$InquiryLog = InquiryLog::where('inquiry_id', $value->id)->where('name', 'answer')->orderBy('id', 'desc')->first();
			if ($InquiryLog) {

				$inquiry = Inquiry::select('id')->find($value->id);
				$inquiry->answer_date_time = $InquiryLog->created_at;
				$inquiry->save();
			} else {

				$inquiry = Inquiry::select('id')->find($value->id);
				$inquiry->answer_date_time = $value->created_at;
				$inquiry->save();
			}
		}
	}

	public function updateCountUpdate()
	{

		$Inquires = Inquiry::select('id')->get();
		foreach ($Inquires as $key => $value) {

			$InquiryUpdateCount = InquiryUpdate::where('inquiry_id', $value->id)->count();

			$InquiryLatestUpdate = InquiryUpdate::select('created_at')->where('inquiry_id', $value->id)->orderBy('id', 'desc')->first();

			if ($InquiryLatestUpdate) {
				$latestUpdate = date('Y-m-d H:i:s', strtotime($InquiryLatestUpdate->created_at));
			}

			$inquiry = Inquiry::select('id')->find($value->id);
			if ($inquiry && $InquiryUpdateCount > 0) {
				$inquiry->update_count = $InquiryUpdateCount;
				$inquiry->last_update = $latestUpdate;
				$inquiry->save();
			}
		}
	}

	public function importMondayDotCom()
	{

		// print_r("okay");
		// die;

		$InquiryQuestionsStageOfStateOption = InquiryQuestionOption::select('id', 'option')->where('inquiry_question_id', 7)->orderBy('id', 'asc')->get();

		$excelFile = public_path('monday/Jodhpur_1653455160.xlsx');
		$sheet = Excel::toArray([], $excelFile);
		$InquiryStatusList = getInquiryStatus();

		$monthArray = array();
		$firstMonthInPHP = "01-01-2021";
		for ($i = 1; $i <= 12; $i++) {

			$monthArray[$i] = date('F', strtotime($firstMonthInPHP));
			$firstMonthInPHP = date('Y-m-d H:i:s', strtotime($firstMonthInPHP . " +1 months"));
		}

		foreach ($sheet as $sheetKey => $sheetValue) {

			if ($sheetKey == 0) {

				// continue;

				foreach ($sheetValue as $dataKey => $dataValue) {

					if ($dataKey > 2) {

						// print_r($dataValue);
						// die;

						$inquiryObject = array();
						$inquiryObject['quotation_amount'] = "";

						$createdDate = date('Y-m-d H:i:s', strtotime($dataValue[1]));

						$createdDate = date('Y-m-d H:i:s', strtotime($createdDate . " -5 hours"));
						$createdDate = date('Y-m-d H:i:s', strtotime($createdDate . " -30 minutes"));

						$inquiryStatus = trim(strtolower($dataValue[8]));
						$inquiryObject['status'] = 1;
						foreach ($InquiryStatusList as $ik => $vk) {
							if (strtolower($vk['name']) == $inquiryStatus) {
								$inquiryObject['status'] = $vk['id'];
								break;
							}
						}

						$inquiryName = $dataValue[0];
						$inquiryNamePieces = explode(" ", $inquiryName);

						$inquiryObject['first_name'] = $inquiryNamePieces[0];
						unset($inquiryNamePieces[0]);

						$inquiryNamePieces = array_values($inquiryNamePieces);
						$inquiryObject['last_name'] = implode(" ", $inquiryNamePieces);
						$inquiryObject['phone_number'] = $dataValue[2];
						$inquiryObject['pincode'] = "000000";
						$inquiryObject['city_id'] = 838;
						$cityName = strtolower($dataValue[14]);
						//$City = CityList::whereRaw('LOWER(name)', $cityName)->first();
						$City = CityList::whereRaw('LOWER(name)="' . $cityName . '"')->first();
						if ($City) {

							$inquiryObject['city_id'] = $City->id;
						}

						$inquiryAddress = $dataValue[6];
						$inquiryAddressPieces = explode(",", $inquiryAddress);

						$inquiryHouseNo = isset($inquiryAddressPieces[0]) ? $inquiryAddressPieces[0] : '';
						$inquirySocietyName = isset($inquiryAddressPieces[1]) ? $inquiryAddressPieces[1] : '';
						if (isset($inquiryAddressPieces[0])) {
							unset($inquiryAddressPieces[0]);
						}

						if (isset($inquiryAddressPieces[1])) {
							unset($inquiryAddressPieces[1]);
						}

						$inquiryAddressPieces = array_values($inquiryAddressPieces);
						$inquiryObject['house_no'] = $inquiryHouseNo;
						$inquiryObject['society_name'] = $inquirySocietyName;
						$inquiryObject['area'] = implode(" ", $inquiryAddressPieces);

						$inquiryAssingedToPhoneNumber = $dataValue[4];
						$inquiryAssingedToPhoneNumber = str_replace(" ", "", $inquiryAssingedToPhoneNumber);

						$inquiryObject['assigned_to'] = 3;
						// Rajesh Oja
						$inquiryAssingedTo = User::select('id')->where('phone_number', $inquiryAssingedToPhoneNumber)->whereIn('type', [0, 1, 2])->first();

						if ($inquiryAssingedTo) {
							$inquiryObject['assigned_to'] = $inquiryAssingedTo->id;
						}

						$sourceType = $dataValue[13];
						$inqurySourceTypes = getInquirySourceTypes();

						$inquirySourceType = "text-5";

						$source = $dataValue[11];
						$inquiryObject['source_type_value'] = $source;

						$source = $dataValue[11];

						$stageOfSite = $dataValue[10];

						$inquiryStageOfSite = "";

						foreach ($InquiryQuestionsStageOfStateOption as $keyOption => $valueOption) {

							if (trim(strtolower($valueOption->option)) == trim(strtolower($stageOfSite))) {
								$inquiryStageOfSite = $valueOption->option;
							}
						}

						$inquiryObject['stage_of_site'] = $inquiryStageOfSite;
						$inquiryObject['stage_of_site2'] = $dataValue[10];

						if (isset($dataValue[17]) && $dataValue[17] != "") {

							$inquiryObject['quotation_amount'] = $dataValue[17];
						}

						$Inquiry = Inquiry::select('id', 'assigned_to')->where('monday_dot_com_id', $dataValue[16])->first();
						if ($Inquiry) {

							// print_r("<pre>");
							// print_r($inquiryObject);
							// die;

							// $Inquiry = new Inquiry();
							// $Inquiry->status = $inquiryObject['status'];
							$Inquiry->user_id = $inquiryObject['assigned_to'];
							$Inquiry->assigned_to = $inquiryObject['assigned_to'];
							// $Inquiry->first_name = $inquiryObject['first_name'];
							// $Inquiry->last_name = $inquiryObject['last_name'];
							// $Inquiry->phone_number = $inquiryObject['phone_number'];
							// $Inquiry->pincode = $inquiryObject['pincode'];
							// $Inquiry->city_id = $inquiryObject['city_id'];
							// $Inquiry->house_no = $inquiryObject['house_no'];
							// $Inquiry->society_name = $inquiryObject['society_name'];
							// $Inquiry->area = $inquiryObject['area'];
							// $Inquiry->source_type_lable = "Other";
							// $Inquiry->source_type = $inquirySourceType;
							// $Inquiry->source_type_value = $inquiryObject['source_type_value'];
							// $Inquiry->architect = 0;
							// $Inquiry->electrician = 0;
							// $Inquiry->stage_of_site = $inquiryObject['stage_of_site'];
							// $Inquiry->site_photos = "";
							// $Inquiry->required_for_property = "";
							// $Inquiry->changes_of_closing_order = "";
							// $Inquiry->follow_up_type = "Call";
							// $Inquiry->follow_up_date_time = date('2022-02-26 10:00:00');
							// $Inquiry->monday_dot_com_id = $dataValue[16];
							// $Inquiry->created_at = $createdDate;
							// $Inquiry->quotation_amount = $inquiryObject['quotation_amount'];
							$Inquiry->save();
						}
					}
				}
			} else if ($sheetKey == 1) {
				continue;

				foreach ($sheetValue as $dataKey => $dataValue) {

					$monday_dot_com_id = $dataValue[0];
					$Inquiry = Inquiry::select('id', 'assigned_to')->where('monday_dot_com_id', $monday_dot_com_id)->first();
					if ($Inquiry) {

						$createdDate2 = explode(" ", $dataValue[5]);
						$createdDate3 = explode("/", $createdDate2[0]);
						$monthNumber = 0;
						foreach ($monthArray as $key => $value) {
							if ($value == $createdDate3[1]) {
								$monthNumber = $key;
							}
						}

						$createdDate = $createdDate3[2] . "-" . $monthNumber . "-" . $createdDate3[0] . " " . $createdDate2[2] . " " . $createdDate2[3];
						$createdDate = date('Y-m-d H:i:s', strtotime($createdDate));

						$createdDate = date('Y-m-d H:i:s', strtotime($createdDate . " -5 hours"));
						$createdDate = date('Y-m-d H:i:s', strtotime($createdDate . " -30 minutes"));

						$InquiryUpdate = new InquiryUpdate();
						$InquiryUpdate->message = $dataValue[4] . " : " . $dataValue[6];
						$InquiryUpdate->user_id = $Inquiry->assigned_to;
						$InquiryUpdate->inquiry_id = $Inquiry->id;
						$InquiryUpdate->created_at = $createdDate;
						// echo '<pre>';
						// print_r($InquiryUpdate);
						// die;
						$InquiryUpdate->save();
					}
				}
			}
		}

		echo "DONE->importMondayDotCom";
	}

	public function updateExistingInquiry()
	{

		$Inquirys = Inquiry::all();

		foreach ($Inquirys as $key => $value) {

			$Inquiry = Inquiry::where('id', $value->id)->first();
			if ($Inquiry) {

				$architect = $Inquiry->architect;
				$electrician = $Inquiry->electrician;

				if ($architect == 0) {

					if ($Inquiry['source_type'] == "user-201" || $Inquiry['source_type'] == "user-202") {

						$architect = $Inquiry['source_type_value'];
						$Inquiry->architect = $architect;
						//$Inquiry->save();

					} else {

						if ($Inquiry['architect_phone_number'] != "") {

							$User = User::select('id')->whereIn('type', [201, 202])->where('phone_number', $Inquiry['architect_phone_number'])->first();
							if ($User) {

								$architect = $User->id;
								$Inquiry->architect = $architect;
								//$Inquiry->save();

							}
						}
					}
				}

				if ($electrician == 0) {

					if ($Inquiry['source_type'] == "user-301" || $Inquiry['source_type'] == "user-302") {

						$electrician = $Inquiry['source_type_value'];
						$Inquiry->electrician = $electrician;
						$Inquiry->save();
					} else {

						if ($Inquiry['electrician_phone_number'] != "") {

							$User = User::select('id')->whereIn('type', [301, 302])->where('phone_number', $Inquiry['electrician_phone_number'])->first();
							if ($User) {

								$electrician = $User->id;
								$Inquiry->electrician = $electrician;
								$Inquiry->save();
							}
						}
					}
				}
			}
		}
	}

	public function joiningBonus()
	{

		$Architects = Architect::where('type', 202)->where('total_point', 0)->get();
		foreach ($Architects as $key => $value) {

			$Architect = Architect::where('id', $value->id)->first();
			if ($Architect) {
				$pointValue = 50;
				$Architect->total_point = $Architect->total_point + $pointValue;
				$Architect->total_point_current = $Architect->total_point_current + $pointValue;
				$Architect->save();
				$debugLog = array();
				$debugLog['for_user_id'] = $Architect->user_id;
				$debugLog['name'] = "point-gain";
				$debugLog['description'] = $pointValue . " Point gained joining bonus ";
				$debugLog['type'] = '';
				saveCRMUserLog($debugLog);
			}
		}

		echo "DONE->joiningBonus";
	}
}
