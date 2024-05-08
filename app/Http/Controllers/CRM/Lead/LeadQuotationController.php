<?php

namespace App\Http\Controllers\CRM\Lead;

use App\Http\Controllers\Controller;
use App\Models\CityList;
use App\Models\Lead;
use App\Models\LeadAccountContact;
use App\Models\LeadContact;
use App\Models\Wltrn_Quotation;
use App\Models\LeadFile;
use App\Models\LeadAccount;
use App\Models\AccountContactTimeline;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class LeadQuotationController extends Controller
{

	public function __construct()
	{

		$this->middleware(function ($request, $next) {

			$tabCanAccessBy = array(0, 1, 2, 6,9, 11, 13, 101, 102, 103, 104, 105, 202, 302);

			if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
				return redirect()->route('dashboard');
			}

			return $next($request);
		});
	}

	public function save(Request $request)
	{

		$rules = array();
		$rules['lead_quotation_lead_id'] = 'required';
		$rules['lead_quotation_file'] = 'required';
		$rules['lead_quotation'] = 'required';

		$customMessage = array();
		$customMessage['lead_quotation_lead_id.required'] = "Invalid parameters";

		$validator = Validator::make($request->all(), $rules, $customMessage);

		if ($validator->fails()) {

			$response = errorRes("The request could not be understood by the server due to malformed syntax");
			$response['data'] = $validator->errors();
		} else {

			$uploadedFile1 = array();
			$fileSize = 0;
			$print_count = 0;
			if ($request->hasFile('lead_quotation_file')) {

				$folderPathofFile = '/s/lead-files/';
                if (!is_dir(public_path($folderPathofFile))) {
                    mkdir(public_path($folderPathofFile));
                }

                $folderPathofFile = '/s/lead-files/' . date('Y');

                if (!is_dir(public_path($folderPathofFile))) {
                    mkdir(public_path($folderPathofFile));
                }

                $folderPathofFile = '/s/lead-files/' . date('Y') . "/" . date('m');
                if (!is_dir(public_path($folderPathofFile))) {
                    mkdir(public_path($folderPathofFile));
                }


                foreach ($request->file('lead_quotation_file') as $key => $file) {

					$fileObject1 = $request->file('lead_quotation_file')[$key];
					$extension = $fileObject1->getClientOriginalExtension();
					$fileName1 = time() . mt_rand(10000, 99999) . '.' . $extension;
					$destinationPath = public_path($folderPathofFile);
					$fileObject1->move($destinationPath, $fileName1);
					if (File::exists(public_path($folderPathofFile . "/" . $fileName1))) {
						$fileName1 = $folderPathofFile . "/" . $fileName1;
						$fileSize = filesize(public_path($fileName1));
						$spaceUploadResponse = uploadFileOnSpaces(public_path($fileName1), $fileName1);
						if ($spaceUploadResponse != 1) {
							$fileName1 = "";
						} else {
							$uploadedFile1[] = $fileName1;
							unlink(public_path($fileName1));
						}
					}

					$print_count++; 
                }
			}

			if ($uploadedFile1 != []) {
				$isQuotation = Wltrn_Quotation::query();
            	$isQuotation->where('wltrn_quotation.inquiry_id', $request->lead_quotation_lead_id);
            	$isQuotation->orderBy('wltrn_quotation.id', 'desc');
            	$isQuotation = $isQuotation->first();
            	$QuotMaster = new Wltrn_Quotation();
            	if ($isQuotation) {
            	    $new_quot_no_str = Wltrn_Quotation::selectRaw('max(wltrn_quotation.quot_no_str + 1) as newversion')
            	        ->where('quotgroup_id', $isQuotation->quotgroup_id)
            	        ->first();
            	    $QuotMaster->quotgroup_id = $isQuotation->quotgroup_id;
            	    $QuotMaster->yy = substr(date('Y'), -2);
            	    $QuotMaster->mm = date('m');
            	    $QuotMaster->quotno = $isQuotation->quotno;
            	    [$major, $minor] = explode('.', $new_quot_no_str->newversion);
            	    $QuotMaster->quot_no_str = $major . '.01';
            	    $QuotMaster->quot_date = date('Y-m-d');
            	} else {
            	    $QuotMaster->quotgroup_id = Wltrn_Quotation::max('quotgroup_id') + 1;
            	    $QuotMaster->yy = substr(date('Y'), -2);
            	    $QuotMaster->mm = date('m');
            	    $QuotMaster->quotno = Wltrn_Quotation::max('quotno') + 1;
            	    $QuotMaster->quot_no_str = '1.01';
            	    $QuotMaster->quot_date = date('Y-m-d');
            	}

				$QuotMaster->quottype_id = 4;
				$QuotMaster->quotation_file = implode(",", $uploadedFile1);
				$QuotMaster->print_count = $print_count;
				$QuotMaster->status = 3;
				$QuotMaster->inquiry_id = $request->lead_quotation_lead_id;
				$QuotMaster->net_amount = $request->lead_quotation;
				$QuotMaster->quot_total_amount = $request->lead_quotation;
				$QuotMaster->entryby = Auth::user()->id; //Live
				$QuotMaster->entryip = $request->ip();
				$QuotMaster->save();

				if($QuotMaster){
					Wltrn_Quotation::where('inquiry_id', $QuotMaster->inquiry_id)->update(['isfinal' => 0]);
                	$QuotMaster->isfinal = 1;
					$QuotMaster->save();

					$timeline = array();
                    $timeline['lead_id'] = $QuotMaster->inquiry_id;
                    $timeline['type'] = "lead-update";
                    $timeline['reffrance_id'] = $QuotMaster->inquiry_id;
                    $timeline['description'] = "Add Manual Quotation File (".$QuotMaster->print_count.") In Lead #" . $QuotMaster->inquiry_id . " Amount is : ".$QuotMaster->net_amount;
                    $timeline['source'] = "WEB";
                    saveLeadTimeline($timeline);

					// $Lead_quotation_count = Wltrn_Quotation::query()->where('inquiry_id', $request->lead_quotation_lead_id)->count();
					// if($Lead_quotation_count == 0){
						$Lead = Lead::find($request->lead_quotation_lead_id);

						$LeadFile = new LeadFile();
						$LeadFile->uploaded_by = Auth::user()->id;
						$LeadFile->file_size = $fileSize;
						$LeadFile->name = $QuotMaster->quotation_file;
						$LeadFile->lead_id = $Lead->id;
						$LeadFile->file_tag_id = 2;
						$LeadFile->save();
	
						if ($Lead->is_deal == 0) {
	
							$Lead->is_deal = 1;
							$Lead->status = 101;
							$Lead->account_user_id = $this->accountCreate($Lead,strval($request->ip()),Auth::user()->id,'WEB');
							
	
							$timeline = array();
							$timeline['lead_id'] = $Lead->id;
							$timeline['type'] = "convert-to-deal";
							$timeline['reffrance_id'] = $LeadFile->id;
							$timeline['description'] = "Quatation upload - convert to deal";
							$timeline['source'] = "WEB";
							saveLeadTimeline($timeline);
						}
	
						$Lead->save();
					// }
					
					$response = successRes("Successfully saved lead quotation");
					$response['id'] = $request->lead_quotation_lead_id;
				}
			} else {
				$response = errorRes("Something went wrong");
			}
		}

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	function accountCreate($lead,$ip = '',$entryby = 0,$source = '')
	{
		$Account_id = 0;
		$leadAccount = LeadAccount::query()->where('phone_number', $lead->phone_number)->first();
		if ($leadAccount) {
			$Account_id = $leadAccount->id;
			if ($leadAccount->phone_number != null && $leadAccount->phone_number != 0 && $leadAccount->phone_number != '') {
				$leadAccountIds = explode(',', $leadAccount->lead_ids);
				if (!in_array($leadAccount->id, $leadAccountIds)) {
					$leadAccountIds[] = $leadAccount->id;
					$leadAccount->lead_ids = implode(',', $leadAccountIds);
				}
				$leadAccount->updateby = $entryby;
				$leadAccount->updateip = $ip;
				$leadAccount->save();
				if ($leadAccount) {
					$TimeLine = new AccountContactTimeline();
					$TimeLine->transaction_id = $leadAccount->id;
					$TimeLine->transaction_type = 'Account';
					$TimeLine->description = 'New Lead Added: #' . $leadAccount->lead_ids;
					$TimeLine->remark = '';
					$TimeLine->source = 'WEB';
					$TimeLine->entryby = $entryby;
					$TimeLine->entryip = $ip;
					$TimeLine->save();
				}
				if ($leadAccount) {
					$leadContacts = LeadContact::query()->where('lead_id', $lead->id)->where('status', 1)->get();
					foreach ($leadContacts as $Cvalue) {
						if ($Cvalue['phone_number'] != '' && $Cvalue['phone_number'] != 0) {
							$LeadAccountContact = LeadAccountContact::query()->where('phone_number', $Cvalue['phone_number'])->first();
							if ($LeadAccountContact) {
								$leadAccountIds = explode(',', $LeadAccountContact->lead_account_ids);
								if (!in_array($leadAccount->id, $leadAccountIds)) {
									$leadAccountIds[] = $leadAccount->id;
									$LeadAccountContact->lead_account_ids = implode(',', $leadAccountIds);
								}
								$leadContactIds = explode(',', $LeadAccountContact->lead_contact_id);
								if (!in_array($Cvalue['id'], $leadContactIds)) {
									$leadContactIds[] = $Cvalue['id'];
									$LeadAccountContact->lead_contact_id = implode(',', $leadContactIds);
								}
								$leadContactTagIds = explode(',', $LeadAccountContact->lead_contact_tag_id);
								if (!in_array($Cvalue['contact_tag_id'], $leadContactTagIds)) {
									$leadContactTagIds[] = $Cvalue['contact_tag_id'];
									$LeadAccountContact->lead_contact_tag_id = implode(',', $leadContactTagIds);
								}
								$LeadAccountContact->updateby = $entryby;
								$LeadAccountContact->updateip = $ip;
								$LeadAccountContact->save();
								if ($LeadAccountContact) {
									$TimeLine = new AccountContactTimeline();
									$TimeLine->transaction_id = $LeadAccountContact->id;
									$TimeLine->transaction_type = 'Contact';
									$TimeLine->description = 'New Account Added: #' . $leadAccount->id;
									$TimeLine->remark = 'convert-to-deal';
									$TimeLine->source = 'WEB';
									$TimeLine->entryby = $entryby;
									$TimeLine->entryip = $ip;
									$TimeLine->save();
								}
							} else {
								$LeadAccountNewContact = new LeadAccountContact();
								$nameParts = explode(' ', $Cvalue['first_name']);
								$LeadAccountNewContact->first_name = $nameParts[0];
								$LeadAccountNewContact->last_name = isset($nameParts[1]) ? implode(' ', array_slice($nameParts, 1)) : '';
								$LeadAccountNewContact->full_name = $Cvalue['first_name'];
								$LeadAccountNewContact->email = $Cvalue['email'];
								$LeadAccountNewContact->phone_number = $Cvalue['phone_number'];
								$LeadAccountNewContact->alt_phone_number = $Cvalue['alernate_phone_number'];
								$LeadAccountNewContact->lead_account_ids = $leadAccount->id;
								$LeadAccountNewContact->lead_contact_id = $Cvalue['id'];
								$LeadAccountNewContact->lead_contact_tag_id = $Cvalue['contact_tag_id'];
								$LeadAccountNewContact->status = $Cvalue['status'];
								$LeadAccountNewContact->remark = '';
								$LeadAccountNewContact->entryby = $entryby;
								$LeadAccountNewContact->entryip = $ip;
								$LeadAccountNewContact->save();
								if ($LeadAccountNewContact) {
									$contacts = [
										['description' => 'New Contact Created by #' . $LeadAccountNewContact->id],
										['description' => 'New Account Added: #' . $leadAccount->id],
									];
									foreach ($contacts as $contact) {
										$TimeLine = new AccountContactTimeline();
										$TimeLine->transaction_id = $LeadAccountNewContact->id;
										$TimeLine->transaction_type = 'Contact';
										$TimeLine->description = 'New Account Added: #' . $leadAccount->id;
										$TimeLine->remark = 'convert-to-deal';
										$TimeLine->source = 'WEB';
										$TimeLine->entryby = $entryby;
										$TimeLine->entryip = $ip;
										$TimeLine->save();
									}
								}
							}
						}
					}
				}
			}
		} else {
			if ($lead->phone_number != null && $lead->phone_number != 0 && $lead->phone_number != '') {
				$leadAccountNew = new LeadAccount();
				$leadAccountNew->first_name = $lead->first_name;
				$leadAccountNew->last_name = $lead->last_name;
				$leadAccountNew->full_name = $lead->first_name . ' ' . $lead->last_name;
				$leadAccountNew->email = $lead->email;
				$leadAccountNew->phone_number = $lead->phone_number;
				$leadAccountNew->address_line_1 = $lead->addressline1;
				$leadAccountNew->address_line_2 = $lead->addressline2;
				$leadAccountNew->pincode = $lead->pincode;
				$leadAccountNew->area = $lead->area;
				$leadAccountNew->country_id = $lead->id;
				$leadAccountNew->state_id = $lead->id;
				$leadAccountNew->city_id = $lead->city_id;
				$leadAccountNew->lead_ids = $lead->id;
				$leadAccountNew->status = $lead->status;
				$leadAccountNew->remark = '';
				$leadAccountNew->entryby = $entryby;
				$leadAccountNew->entryip = $ip;
				$leadAccountNew->save();
				if ($leadAccountNew) {
					$Account_id = $leadAccountNew->id;
					$contacts = [
						['description' => 'New Account Created from #' . $lead->id .  'This Deal'],
						['description' => 'New Lead Added: #' . $lead->id],
					];
					foreach ($contacts as $contact) {
						$TimeLine = new AccountContactTimeline();
						$TimeLine->transaction_id = $leadAccountNew->id;
						$TimeLine->transaction_type = 'Account';
						$TimeLine->description = $contact['description'];
						$TimeLine->remark = 'convert-to-deal';
						$TimeLine->source = 'WEB';
						$TimeLine->entryby = $entryby;
						$TimeLine->entryip = $ip;
						$TimeLine->save();
					}
				}
				if ($leadAccountNew) {
					$leadContacts = LeadContact::query()->where('lead_id', $lead->id)->where('status', 1)->get();
					foreach ($leadContacts as $Cvalue) {
						if ($Cvalue['phone_number'] != '' && $Cvalue['phone_number'] != 0) {
							$LeadAccountContact = LeadAccountContact::query()->where('phone_number', $Cvalue['phone_number'])->first();
							if ($LeadAccountContact) {
								$leadAccountIds = explode(',', $LeadAccountContact->lead_account_ids);
								if (!in_array($leadAccountNew->id, $leadAccountIds)) {
									$leadAccountIds[] = $leadAccountNew->id;
									$LeadAccountContact->lead_account_ids = implode(',', $leadAccountIds);
								}
								$leadContactIds = explode(',', $LeadAccountContact->lead_contact_id);
								if (!in_array($Cvalue['id'], $leadContactIds)) {
									$leadContactIds[] = $Cvalue['id'];
									$LeadAccountContact->lead_contact_id = implode(',', $leadContactIds);
								}
								$leadContactTagIds = explode(',', $LeadAccountContact->lead_contact_tag_id);
								if (!in_array($Cvalue['contact_tag_id'], $leadContactTagIds)) {
									$leadContactTagIds[] = $Cvalue['contact_tag_id'];
									$LeadAccountContact->lead_contact_tag_id = implode(',', $leadContactTagIds);
								}
								$LeadAccountContact->updateby = $entryby;
								$LeadAccountContact->updateip = $ip;
								$LeadAccountContact->save();
								if ($LeadAccountContact) {
									$TimeLine = new AccountContactTimeline();
									$TimeLine->transaction_id = $LeadAccountContact->id;
									$TimeLine->transaction_type = 'Contact';
									$TimeLine->description = 'New Account Added: #' . $leadAccountNew->id;
									$TimeLine->remark = 'convert-to-deal';
									$TimeLine->source = 'WEB';
									$TimeLine->entryby = $entryby;
									$TimeLine->entryip = $ip;
									$TimeLine->save();
								}
							} else {
								$LeadAccountNewContact = new LeadAccountContact();
								$nameParts = explode(' ', $Cvalue['first_name']);
								$LeadAccountNewContact->first_name = $nameParts[0];
								$LeadAccountNewContact->last_name = isset($nameParts[1]) ? implode(' ', array_slice($nameParts, 1)) : '';
								$LeadAccountNewContact->full_name = $Cvalue['first_name'];
								$LeadAccountNewContact->email = $Cvalue['email'];
								$LeadAccountNewContact->phone_number = $Cvalue['phone_number'];
								$LeadAccountNewContact->alt_phone_number = $Cvalue['alernate_phone_number'];
								$LeadAccountNewContact->lead_account_ids = $leadAccountNew->id;
								$LeadAccountNewContact->lead_contact_id = $Cvalue['id'];
								$LeadAccountNewContact->lead_contact_tag_id = $Cvalue['contact_tag_id'];
								$LeadAccountNewContact->status = $Cvalue['status'];
								$LeadAccountNewContact->remark = '';
								$LeadAccountNewContact->entryby = $entryby;
								$LeadAccountNewContact->entryip = $ip;
								$LeadAccountNewContact->save();
								if ($LeadAccountNewContact) {
									$contacts = [
										['description' => 'New Contact Created by #' . $LeadAccountNewContact->id],
										['description' => 'New Account Added: #' . $leadAccountNew->id],
									];
									foreach ($contacts as $contact) {
										$TimeLine = new AccountContactTimeline();
										$TimeLine->transaction_id = $LeadAccountNewContact->id;
										$TimeLine->transaction_type = 'Contact';
										$TimeLine->description = $contact['description'];
										$TimeLine->remark = 'convert-to-deal';
										$TimeLine->source = 'WEB';
										$TimeLine->entryby = $entryby;
										$TimeLine->entryip = $ip;
										$TimeLine->save();
									}
								}
							}
						}
					}
				}
			}
		}

		return $Account_id;
	}
}