<?php

namespace App\Http\Controllers;

use App\Models\CityList;
use App\Models\CountryList;
use App\Models\DataMaster;
use App\Models\MainMaster;
use App\Models\StateList;
use App\Models\LeadFile;
use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\LeadAccount;
use App\Models\LeadContact;
use App\Models\LeadAccountContact;
use App\Models\AccountContactTimeline;
use Illuminate\Support\Facades\Auth;



class GeneralController extends Controller
{

	public function __construct()
	{
	}

	public function searchCountry(Request $request)
	{

		$searchKeyword = isset($request->q) ? $request->q : "in";
		$id = isset($request->id) ? $request->id : 0;

		$CountryList = CountryList::select('id', 'name as text');
		if ($id != 0) {
			$CountryList->where('id', $id);
			$CountryList->limit(1);
		} else {
			$CountryList->where('name', 'like', "%" . $searchKeyword . "%");
			$CountryList->limit(5);
		}

		$CountryList = $CountryList->get();
		$response = array();
		$response['results'] = $CountryList;
		$response['pagination']['more'] = false;
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function searchCity(Request $request)
	{

		$searchKeyword = isset($request->q) ? $request->q : "sur";

		$CityList = CityList::select('id', 'name as text');
		$CityList->where('name', 'like', "%" . $searchKeyword . "%");
		$CityList->where('status', 1);
		$CityList->limit(5);
		$CityList = $CityList->get();
		$response = array();
		$response['results'] = $CityList;
		$response['pagination']['more'] = false;
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function searchCityStateCountry(Request $request)
	{

		$searchKeyword = isset($request->q) ? $request->q : "sur";

		$CityList = CityList::select('city_list.id', 'city_list.name as city_list_name', 'state_list.name as state_list_name');
		$CityList->leftJoin('state_list', 'state_list.id', '=', 'city_list.state_id');
		$CityList->where('city_list.name', 'like', "%" . $searchKeyword . "%");
		$CityList->where('city_list.status', 1);
		$CityList->limit(5);
		$CityList = $CityList->get();
		foreach ($CityList as $key => $value) {
			$CityList[$key]['text'] = $value['city_list_name'] . ", " . $value['state_list_name'] . ", India";
		}


		$response = array();
		$response['results'] = $CityList;
		$response['pagination']['more'] = false;
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function searchStateFromCountry(Request $request)
	{

		$searchKeyword = isset($request->q) ? $request->q : "guj";
		$countryId = isset($request->country_id) ? $request->country_id : "";
		$id = isset($request->id) ? $request->id : 0;
		$StateList = array();

		if ($countryId != "") {

			$StateList = StateList::select('id', 'name as text');
			$StateList->where('name', 'like', "%" . $searchKeyword . "%");
			$StateList->where('country_id', $request->country_id);
			$StateList->limit(5);
			$StateList = $StateList->get();
		} else if ($id != 0) {

			$StateList = StateList::select('id', 'name as text');
			$StateList->where('id', $id);
			$StateList->limit(1);
			$StateList = $StateList->get();
		}

		$response = array();
		$response['results'] = $StateList;
		$response['pagination']['more'] = false;
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function searchCityFromState(Request $request)
	{

		$searchKeyword = isset($request->q) ? $request->q : "sur";
		$stateId = isset($request->state_id) ? $request->state_id : "";
		$id = isset($request->id) ? $request->id : 0;
		$CityList = array();

		if ($stateId != "") {

			$CityList = CityList::select('id', 'name as text');
			$CityList->where('state_id', $request->state_id);
			$CityList->where('name', 'like', "%" . $searchKeyword . "%");
			$CityList->where('status', 1);
			$CityList->limit(5);
			$CityList = $CityList->get();
		} else if ($id != 0) {
			$CityList = CityList::select('id', 'name as text');
			$CityList->where('id', $id);
			$CityList->limit(1);
			$CityList = $CityList->get();
		}

		$response = array();
		$response['results'] = $CityList;
		$response['pagination']['more'] = false;
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	function searchCourier(Request $request)
	{

		$DataMaster = array();

		$MainMaster = MainMaster::select('id')->where('code', 'COURIER_SERVICE')->first();
		if ($MainMaster) {

			$DataMaster = array();
			$DataMaster = DataMaster::select('id', 'name as text');
			$DataMaster->where('main_master_id', $MainMaster->id);
			$DataMaster->where('name', 'like', "%" . $request->q . "%");
			$DataMaster->limit(5);
			$DataMaster = $DataMaster->get();
		}

		$response = array();
		$response['results'] = $DataMaster;
		$response['pagination']['more'] = false;
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	function notificationScheduler(Request $request)
	{
	}

	function LeadAccountSave(Request $request)
	{
		$leads = Lead::query()->where('is_deal', 1)->get();
		$leadData = [];


		foreach ($leads as $lead) {
			$contacts = LeadContact::query()->where('lead_id', $lead->id)->where('status', 1)->get()->toArray();
			$leadData[] = [
				'lead' => array_merge($lead->toArray(), ['main_contact_id' => $contacts])
			];
		}
		foreach ($leadData as $value) {
			$leadAccount = LeadAccount::query()->where('phone_number', $value['lead']['phone_number'])->first();
			if ($leadAccount) {
				if ($leadAccount->phone_number != null && $leadAccount->phone_number != 0 && $leadAccount->phone_number != '') {
					$leadAccountIds = explode(',', $leadAccount->lead_ids);
					if (!in_array($leadAccount->id, $leadAccountIds)) {
						$leadAccountIds[] = $leadAccount->id;
						$leadAccount->lead_ids = implode(',', $leadAccountIds);
					}
					$leadAccount->updateby = Auth::user()->id;
					$leadAccount->updateip = $request->ip();
					$leadAccount->save();

					if ($leadAccount) {
						$TimeLine = new AccountContactTimeline();
						$TimeLine->transaction_id = $leadAccount->id;
						$TimeLine->transaction_type = 'Account';
						$TimeLine->description = 'New Lead Added: #' . $leadAccount->lead_ids;
						$TimeLine->remark = 'Regenerated';
						$TimeLine->source = 'WEB';
						$TimeLine->entryby = Auth::user()->id;
						$TimeLine->entryip = $request->ip();
						$TimeLine->save();
					}


					if ($leadAccount) {
						foreach ($value['lead']['main_contact_id'] as $Cvalue) {
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

									$LeadAccountContact->updateby = Auth::user()->id;
									$LeadAccountContact->updateip = $request->ip();
									$LeadAccountContact->save();

									if ($LeadAccountContact) {
										$TimeLine = new AccountContactTimeline();
										$TimeLine->transaction_id = $LeadAccountContact->id;
										$TimeLine->transaction_type = 'Contact';
										$TimeLine->description = 'New Account Added: #' . $leadAccount->id;
										$TimeLine->remark = 'Regenerated';
										$TimeLine->source = 'WEB';
										$TimeLine->entryby = Auth::user()->id;
										$TimeLine->entryip = $request->ip();
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
									$LeadAccountNewContact->entryby = Auth::user()->id;
									$LeadAccountNewContact->entryip = $request->ip();
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
											$TimeLine->remark = 'Regenerated';
											$TimeLine->source = 'WEB';
											$TimeLine->entryby = Auth::user()->id;
											$TimeLine->entryip = $request->ip();
											$TimeLine->save();
										}
									}
								}
							}
						}
					}
				}
			} else {
				if ($value['lead']['phone_number'] != null && $value['lead']['phone_number'] != 0 && $value['lead']['phone_number'] != '') {
					$leadAccountNew = new LeadAccount();
					$leadAccountNew->first_name = $value['lead']['first_name'];
					$leadAccountNew->last_name = $value['lead']['last_name'];
					$leadAccountNew->full_name = $value['lead']['first_name'] . ' ' . $value['lead']['last_name'];
					$leadAccountNew->email = $value['lead']['email'];
					$leadAccountNew->phone_number = $value['lead']['phone_number'];
					$leadAccountNew->address_line_1 = $value['lead']['addressline1'];
					$leadAccountNew->address_line_2 = $value['lead']['addressline2'];
					$leadAccountNew->pincode = $value['lead']['pincode'];
					$leadAccountNew->area = $value['lead']['area'];
					$leadAccountNew->country_id = $value['lead']['id'];
					$leadAccountNew->state_id = $value['lead']['id'];
					$leadAccountNew->city_id = $value['lead']['city_id'];
					$leadAccountNew->lead_ids = $value['lead']['id'];
					$leadAccountNew->status = $value['lead']['status'];
					$leadAccountNew->remark = '';
					$leadAccountNew->entryby = Auth::user()->id;
					$leadAccountNew->entryip = $request->ip();
					$leadAccountNew->save();
					if ($leadAccountNew) {
						$contacts = [
							['description' => 'New Account Created from #' . $value['lead']['id'] .  'This Deal'],
							['description' => 'New Lead Added: #' . $value['lead']['id']],
						];
						foreach ($contacts as $contact) {
							$TimeLine = new AccountContactTimeline();
							$TimeLine->transaction_id = $leadAccountNew->id;
							$TimeLine->transaction_type = 'Account';
							$TimeLine->description = $contact['description'];
							$TimeLine->remark = 'Regenerated';
							$TimeLine->source = 'WEB';
							$TimeLine->entryby = Auth::user()->id;
							$TimeLine->entryip = $request->ip();
							$TimeLine->save();
						}
					}


					if ($leadAccountNew) {
						foreach ($value['lead']['main_contact_id'] as $Cvalue) {
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

									$LeadAccountContact->updateby = Auth::user()->id;
									$LeadAccountContact->updateip = $request->ip();
									$LeadAccountContact->save();
									if ($LeadAccountContact) {
										$TimeLine = new AccountContactTimeline();
										$TimeLine->transaction_id = $LeadAccountContact->id;
										$TimeLine->transaction_type = 'Contact';
										$TimeLine->description = 'New Account Added: #' . $leadAccountNew->id;
										$TimeLine->remark = 'Regenerated';
										$TimeLine->source = 'WEB';
										$TimeLine->entryby = Auth::user()->id;
										$TimeLine->entryip = $request->ip();
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
									$LeadAccountNewContact->entryby = Auth::user()->id;
									$LeadAccountNewContact->entryip = $request->ip();
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
											$TimeLine->remark = 'Regenerated';
											$TimeLine->source = 'WEB';
											$TimeLine->entryby = Auth::user()->id;
											$TimeLine->entryip = $request->ip();
											$TimeLine->save();
										}
									}
								}
							}
						}
					}
				}
			}
		}
		$response = successRes('Save Data');
		return $response;
	}
}
