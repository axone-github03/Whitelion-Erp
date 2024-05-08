<?php

namespace App\Http\Controllers\CRM\Accounts;

use App\Http\Controllers\Controller;
use App\Models\CityList;
use App\Models\CRMSettingStageOfSite;
use App\Models\Lead;
use App\Models\LeadAccountContact;
use App\Models\LeadContact;
use App\Models\User;
use App\Models\StateList;
use App\Models\CountryList;
use App\Models\CRMSettingSubStatus;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeadAccountController extends Controller {

	public function __construct() {

		$this->middleware(function ($request, $next) {

			$tabCanAccessBy = array(0, 1, 2, 9);

			if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
				return redirect()->route('dashboard');
			}

			return $next($request);
		});
	}

	public function index(Request $request) {

		$data = array();
		$data['title'] = "Account";
		return view('crm/account/account', compact('data'));
	}

	public function detail(Request $request) {

		$data = array();
		$data['title'] = "Account";
		$data['id'] = isset($request->id) ? $request->id : 0;
		$data['is_account'] = 1;
		return view('crm/account/account_detail', compact('data'));
	}

	public function ajax(Request $request) {
		$searchColumns = array(

			0 => 'users.id',
			1 => 'users.first_name',
			2 => 'users.last_name',
			3 => 'users.email',
			4 => 'users.phone_number',
		);

		$columns = array(
			0 => 'users.id',
			1 => 'users.first_name',
			2 => 'users.email',
			3 => 'users.status',
			4 => 'users.last_name',
			5 => 'users.type',
			6 => 'users.created_at',
			7 => 'users.status',
			8 => 'users.phone_number',

		);

		$recordsTotal = DB::table('users')->where('no_of_deal', '>', 0)->count();
		$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.
		$query = DB::table('users');
		$query->where('no_of_deal', '>', 0);
		$query->select($columns);
		$query->limit($request->length);
		$query->offset($request->start);
		$query->orderBy($columns[$request['order'][0]['column']], $request['order'][0]['dir']);
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

		$data = $query->get();
		// echo "<pre>";
		// print_r(DB::getQueryLog());
		// die;

		$data = json_decode(json_encode($data), true);

		if ($isFilterApply == 1) {
			$recordsFiltered = count($data);
		}

		$routeAccountDetail = route('crm.account.detail');

		foreach ($data as $key => $value) {

			$data[$key]['id'] = '<div class="avatar-xs"><span class="avatar-title rounded-circle">' . $data[$key]['id'] . '</span></div>';

			$data[$key]['name'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">' . highlightString($value['first_name'] . " " . $value['last_name'],$search_value) . '</a></h5>
             <p class="text-muted mb-0">' . highlightString(getUserTypeName($value['type']),$search_value) . '</p>';

			// if ($data[$key]['created_at'] == $data[$key]['last_active_date_time']) {

			// 	$data[$key]['last_active_date_time'] = "-";
			// 	$data[$key]['last_login_date_time'] = "-";

			// } else {

			// 	$data[$key]['last_active_date_time'] = convertDateTime($value['last_active_date_time']);
			// 	$data[$key]['last_login_date_time'] = convertDateTime($value['last_login_date_time']);

			// }

			$data[$key]['status'] = getUserStatusLable($value['status']);
			$data[$key]['email'] = '<p class="text-muted mb-0">' . highlightString($value['email'],$search_value) . '</p>
             <p class="text-muted mb-0">' . highlightString($value['phone_number'],$search_value) . '</p>';

			$uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';

			$uiAction .= '<li class="list-inline-item px-2">';
			$uiAction .= '<a target="_blank" href="' . $routeAccountDetail . '?id=' . $value['id'] . '" title="Edit"><i class="bx bx-list-ul"></i></a>';
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

	function getList(Request $request) {

		$pageNo = isset($request->page_no) ? $request->page_no : 1;
		$Users = User::query();
		if (isset($request->search)) {
            if ($request->search != "") {
                $search = $request->search;
                $Users->where(function ($query) use ($search) {
                    $query->where('users.id', 'like', '%' . $search . '%');
                    $query->orWhere('users.first_name', 'like', '%' . $search . '%');
                    $query->orWhere('users.last_name', 'like', '%' . $search . '%');
                });
            }
        }
		$Users->where('no_of_deal', '>', 0);
		$Users->orderBy('users.id', 'desc');

		$Users = $Users->get();
		$Users = json_encode($Users);
		$Users = json_decode($Users, true);
		$lastPageLeadId = 0;
		$FirstPageLeadId = 0;
		$LeadR = array_reverse($Users);
		if (count($LeadR) > 0) {
			$FirstPageLeadId = $Users[0]['id'];
			$lastPageLeadId = $Users[0]['id'];
		}

		$data = array();
		$data['users'] = $Users;
		$response = successRes("Get List");
		$response['view'] = view('crm/account/account_list', compact('data'))->render();
		$response['lastPageLeadId'] = $lastPageLeadId;
		$response['FirstPageLeadId'] = $FirstPageLeadId;
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	function getDeatailView(Request $request) {

		$response = successRes("");

		$User = User::find($request->id);
		$data = array();

		if ($User) {

			$User = json_encode($User);
			$User = json_decode($User, true);

			$data['user'] = $User;


			$data['user']['H_No'] = "-";
			$data['user']['Area'] = "-";

			$CityList = CityList::select('city_list.id', 'city_list.name as city_list_name');
			$CityList->where('city_list.id', $data['user']['city_id']);
			$CityList = $CityList->first();
			if ($CityList) {
				$CityList = json_encode($CityList);
				$CityList = json_decode($CityList, true);
				$data['user']['city'] = $CityList['city_list_name'];
			}

			$StateList = StateList::select('state_list.id', 'state_list.name as state_list_name');
			$StateList->where('state_list.id', $data['user']['state_id']);
			$StateList = $StateList->first();
			if ($StateList) {
				$StateList = json_encode($StateList);
				$StateList = json_decode($StateList, true);
				$data['user']['state'] = $StateList['state_list_name'];
			}

			$CountryList = CountryList::select('country_list.id', 'country_list.name as country_list_name');
			$CountryList->where('country_list.id', $data['user']['country_id']);
			$CountryList = $CountryList->first();
			if ($CountryList) {
				$CountryList = json_encode($CountryList);
				$CountryList = json_decode($CountryList, true);
				$data['user']['country'] = $CountryList['country_list_name'];
			}

			$LeadContact = LeadContact::query();
			$LeadContact->select('crm_setting_contact_tag.name as tag_name', 'lead_contacts.*');
			$LeadContact->leftJoin('crm_setting_contact_tag', 'crm_setting_contact_tag.id', '=', 'lead_contacts.contact_tag_id');
			$LeadContact->where('lead_contacts.lead_id', $data['user']['id']);
			$LeadContact->orderBy('lead_contacts.id', 'desc');
			$LeadContact = $LeadContact->get();
			$LeadContact = json_encode($LeadContact);
			$LeadContact = json_decode($LeadContact, true);

			$data['contacts'] = $LeadContact;

			$Lead = Lead::query();
			$Lead->where('leads.account_user_id', $data['user']['id']);
			$Lead->where('leads.is_deal', 1);
			$Lead->orderBy('leads.id', 'desc');
			$Lead = $Lead->get();
			$Lead = json_encode($Lead);
			$Lead = json_decode($Lead, true);

			$LeadStatus = getLeadStatus();

			foreach ($Lead as $key => $value) {

				$lead_closing_date_time = $Lead[$key]['closing_date_time'];
				$lead_closing_date_time = date('Y-m-d H:i:s', strtotime($lead_closing_date_time . " +5 hours"));
				$lead_closing_date_time = date('Y-m-d H:i:s', strtotime($lead_closing_date_time . " +30 minutes"));
				$Lead[$key]['closing_date_time'] = date('Y-m-d', strtotime($lead_closing_date_time));

				if ($Lead[$key]['site_stage'] != 0) {

					$CRMSettingStageOfSite = CRMSettingStageOfSite::select('id', 'name as text');
					// $CRMSettingStageOfSite->where('crm_setting_stage_of_site.status', 1);
					$CRMSettingStageOfSite->where('crm_setting_stage_of_site.id', $Lead[$key]['site_stage']);
					$CRMSettingStageOfSite = $CRMSettingStageOfSite->first();
					if ($CRMSettingStageOfSite) {

						$Lead[$key]['site_stage'] = $CRMSettingStageOfSite->text;
					}
				} else {
					$Lead[$key]['site_stage'] = "";
				}
				$Lead[$key]['status'] = $LeadStatus[$Lead[$key]['status']]['name'];

				$sub_status = CRMSettingSubStatus::find($Lead[$key]['sub_status']);
				if($sub_status)
				{
					$Lead[$key]['sub_status'] = $sub_status['name'];
				}
				else{
					$Lead[$key]['sub_status'] = "-";
				}

				
				$Lead[$key]['url'] = route('crm.deal') . "?id=" . $Lead[$key]['id'];
			}

			$LeadContact = LeadAccountContact::query();
			$LeadContact->select('crm_setting_contact_tag.name as tag_name', 'lead_account_contacts.*');
			$LeadContact->leftJoin('crm_setting_contact_tag', 'crm_setting_contact_tag.id', '=', 'lead_account_contacts.contact_tag_id');
			$LeadContact->where('lead_account_contacts.user_id', $data['user']['id']);
			$LeadContact->orderBy('lead_account_contacts.id', 'desc');
			$LeadContact = $LeadContact->get();
			$LeadContact = json_encode($LeadContact);
			$LeadContact = json_decode($LeadContact, true);

			$data['deals'] = $Lead;
			$data['contacts'] = $LeadContact;

			$response['view'] = view('crm/account/account_detail_view', compact('data'))->render();
		} else {
			$response = errorRes("Something went wrong");
		}

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function getListAjax(Request $request)
    {

        $isSalePerson = isSalePerson();
        $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
        $source_type = getInquirySourceTypes();
        if ($isSalePerson == 1) {
            $parentSalesUsers = getParentSalePersonsIds(Auth::user()->id);
            $childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
        }

        $selectColumns = array(
            'users.id',
            'users.first_name',
            'users.last_name',
        );


        $searchColumns = array(
            0 => 'users.id',
            1 => 'users.first_name',
            2 => 'users.last_name',

        );

        // RECORDSTOTAL START
        $query = User::query();
		$query->where('no_of_deal', '>', 0);
        $recordsTotal = $query->count();
        $recordsFiltered = $recordsTotal;
        // RECORDSTOTAL END
		
		
        // RECORDSFILTERED START
        $query = User::query();
		$query->where('no_of_deal', '>', 0);
        $query->select($selectColumns);
               
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
        // RECORDSFILTERED START


        $Account = User::query();
		$Account->where('no_of_deal', '>', 0);
        $Account->orderBy('users.id', 'DESC');
        $Account->limit($request->length);
        $Account->offset($request->start);

		$search_value = '';
        if (isset($request['search']['value'])) {
            $isFilterApply = 1;
            $search_value = $request['search']['value'];
            $Account->where(function ($query) use ($search_value, $searchColumns) {
                for ($i = 0; $i < count($searchColumns); $i++) {

                    if ($i == 0) {
                        $query->where($searchColumns[$i], 'like', "%" . $search_value . "%");
                    } else {

                        $query->orWhere($searchColumns[$i], 'like', "%" . $search_value . "%");
                    }
                }
            });
        }

        $Account_data = $Account->get();

        if ($Account->count() >= 1) {
            $FirstPageLeadId = $Account_data[0]['id'];
        } else {
            $FirstPageLeadId = 0;
        }
        $data = json_decode(json_encode($Account_data), true);

        $viewData = array();
        foreach ($data as $key => $value) {
            $view = "";
            $view = '<li class="lead_li" id="lead_' . $value['id'] . '" onclick="getDataDetail(' . $value['id'] . ')" style="list-style: none;">';
            $view .= '<a href="javascript: void(0);">';
            $view .= '<div class="d-flex">';
            $view .= '<div class="flex-grow-1 overflow-hidden">';
            $view .= '<h5 class="text-truncate font-size-14 mb-1">#' . highlightString($value['id'],$search_value) . '</h5>';
            $view .= '<p class="text-truncate mb-0">' . highlightString($value['first_name'] . '  ' . $value['last_name'],$search_value) . '</p>';
            $view .= '</div>';
            // $view .= '<div class="d-flex justify-content-end font-size-16">';
            // $view .= '<span class="badge badge-pill badge badge-soft-info font-size-11" style="height: fit-content;" id="' . $value['id'] . '_lead_list_status">' . $LeadStatus[$value['status']]['name'] . '</span>';

            // $view .= '</div>';
            $view .= '</div>';
            $view .= '</a>';
            $view .= '</li>';

            $viewData[$key] = array();
            $viewData[$key]['view'] = $view;
        }

        $jsonData = array(
            "draw" => intval($request['draw']),
            // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($recordsTotal),
            // total number of records
            "recordsFiltered" => intval($recordsFiltered),
            // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $viewData,
            // total data array
            "dataed" => $data,
            // total data array
            "FirstPageLeadId" => $FirstPageLeadId,

        );
        return $jsonData;
    }
}
