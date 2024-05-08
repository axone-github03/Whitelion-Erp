<?php
namespace App\Http\Controllers;

use App\Models\ChannelPartner;
use App\Models\Company;
use App\Models\User;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

//use Session;

class ChannelPartnersReportsController extends Controller {

	public function __construct() {

		$this->middleware(function ($request, $next) {

			$tabCanAccessBy = array(0, 1, 2, 13);

			if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
				return redirect()->route('dashboard');

			}

			return $next($request);

		});

	}

	public function index(Request $request) {

		$data = array();
		$data['title'] = "Reports - Channel Partners";
		return view('channel_partners/reports', compact('data'));

	}
	public function searchSalePerson(Request $request) {

		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		$isSalePerson = isSalePerson();

		if ($isSalePerson == 1) {
			$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);

		}

		$User = $UserResponse = array();
		$q = $request->q;
		$User = User::select('users.id', 'users.first_name', 'users.last_name', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"));

		if ($isAdminOrCompanyAdmin == 1) {

			$User->whereIn('users.type', array(0, 1, 2));

		} else if ($isSalePerson == 1) {
			$User->where('users.type', 2);
			$User->whereIn('id', $childSalePersonsIds);
		}
		$User->where(function ($query) use ($q) {
			$query->where('users.first_name', 'like', '%' . $q . '%');
			$query->orWhere('users.last_name', 'like', '%' . $q . '%');
		});
		$User->where('users.status', 1);
		$User->limit(5);
		$User = $User->get();

		if (count($User) > 0) {
			foreach ($User as $User_key => $User_value) {
				$UserResponse[$User_key]['id'] = $User_value['id'];
				$UserResponse[$User_key]['text'] = $User_value['full_name'];
			}
		}

		$UserKey = count($UserResponse);
		$UserResponse[$UserKey]['id'] = 0;
		$UserResponse[$UserKey]['text'] = "All";
		$response = array();
		$response['results'] = $UserResponse;
		$response['pagination']['more'] = false;
		return response()->json($response)->header('Content-Type', 'application/json');

	}

	public function typeList(Request $request) {

		$isSalePerson = isSalePerson();
		if ($isSalePerson == 1) {
			$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
		}

		$startDate = date('Y-m-d 00:00:00', strtotime($request->start_date));
		$startDate = date('Y-m-d H:i:s', strtotime($startDate . " -5 hours"));
		$startDate = date('Y-m-d H:i:s', strtotime($startDate . " -30 minutes"));

		$endDate = date('Y-m-d 23:59:59', strtotime($request->end_date));
		$endDate = date('Y-m-d H:i:s', strtotime($endDate . " -5 hours"));
		$endDate = date('Y-m-d H:i:s', strtotime($endDate . " -30 minutes"));

		$query = ChannelPartner::query();
		$query->select('channel_partner.type', DB::raw('count(*) as channel_partner_count'), );
		$query->groupBy('channel_partner.type');
		$query->where('channel_partner.created_at', '>=', $startDate);
		$query->where('channel_partner.created_at', '<=', $endDate);
		if ($isSalePerson == 1) {

			$query->where(function ($query2) use ($childSalePersonsIds) {
				foreach ($childSalePersonsIds as $key => $value) {
					if ($key == 0) {
						$query2->whereRaw('FIND_IN_SET("' . $value . '",channel_partner.sale_persons)>0');
					} else {
						$query2->orWhereRaw('FIND_IN_SET("' . $value . '",channel_partner.sale_persons)>0');
					}
				}
			});

		}

		if ($request->sales_user_id != 0) {

			$query->whereRaw('FIND_IN_SET("' . $request->sales_user_id . '",channel_partner.sale_persons)>0');

		}

		$data = $query->get();

		$channelTypeCount = array();

		foreach ($data as $key => $value) {
			$channelTypeCount[$value->type] = $value->channel_partner_count;

		}

		$tableResponse = "";

		$totalchannelPartnersCount = 0;

		$channelPartners = getChannelPartners();
		foreach ($channelPartners as $key => $value) {

			$channelParnterCountSi = isset($channelTypeCount[$value['id']]) ? $channelTypeCount[$value['id']] : 0;
			$tableResponse .= "<tr>";
			$tableResponse .= "<td>";
			$tableResponse .= $value['short_name'];
			$tableResponse .= "</td>";
			$tableResponse .= "<td>";
			$tableResponse .= "" . $channelParnterCountSi;
			$tableResponse .= "</td>";
			$tableResponse .= "</tr>";
			$totalchannelPartnersCount = $totalchannelPartnersCount + $channelParnterCountSi;

		}

		$tableResponse .= "<tr>";
		$tableResponse .= "<td>";
		$tableResponse .= "TOTAL";
		$tableResponse .= "</td>";
		$tableResponse .= "<td>";
		$tableResponse .= "" . $totalchannelPartnersCount;
		$tableResponse .= "</td>";
		$tableResponse .= "</tr>";

		$response = successRes("tyoe reports");
		$response['view'] = $tableResponse;
		$response['data'] = $data;

		return response()->json($response)->header('Content-Type', 'application/json');

	}

	public function list(Request $request) {

		$startDate = date('Y-m-d 00:00:00', strtotime($request->start_date));
		$startDate = date('Y-m-d H:i:s', strtotime($startDate . " -5 hours"));
		$startDate = date('Y-m-d H:i:s', strtotime($startDate . " -30 minutes"));

		$endDate = date('Y-m-d 23:59:59', strtotime($request->end_date));
		$endDate = date('Y-m-d H:i:s', strtotime($endDate . " -5 hours"));
		$endDate = date('Y-m-d H:i:s', strtotime($endDate . " -30 minutes"));

		$isSalePerson = isSalePerson();
		if ($isSalePerson == 1) {

			$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);

		}

		$searchColumns = array(

			0 => 'users.id',
			1 => 'users.first_name',
			2 => 'users.last_name',
			3 => 'users.email',
			4 => 'users.phone_number',
			5 => 'channel_partner.gst_number',
			6 => 'channel_partner.firm_name',
		);

		$selectColumns = array(
			0 => 'users.id',
			1 => 'users.first_name',
			2 => 'users.email',
			3 => 'users.last_active_date_time',
			4 => 'users.last_login_date_time',
			5 => 'users.status',
			5 => 'users.last_name',
			6 => 'users.type',
			7 => 'users.created_at',
			8 => 'users.status',
			9 => 'users.phone_number',
			10 => 'channel_partner.gst_number',
			11 => 'channel_partner.reporting_manager_id',
			12 => 'channel_partner.reporting_company_id',
			13 => 'channel_partner.sale_persons',
			14 => 'channel_partner.firm_name',

		);

		$sortColumns = array(
			0 => 'users.id',
			1 => 'users.first_name',
			2 => 'users.email',
			3 => 'channel_partner.reporting_company_id',
			4 => 'channel_partner.sale_persons',
			5 => 'users.last_active_date_time',

		);

		$query = DB::table('users');
		$query->leftJoin('channel_partner', 'channel_partner.id', '=', 'users.reference_id');
		$query->whereIn('users.type', array(101, 102, 103, 104, 105));
		$query->where('channel_partner.created_at', '>=', $startDate);
		$query->where('channel_partner.created_at', '<=', $endDate);
		if ($request->sales_user_id != 0) {

			$query->whereRaw('FIND_IN_SET("' . $request->sales_user_id . '",channel_partner.sale_persons)>0');

		}

		if ($isSalePerson == 1) {

			$query->where(function ($query2) use ($childSalePersonsIds) {
				foreach ($childSalePersonsIds as $key => $value) {
					if ($key == 0) {
						$query2->whereRaw('FIND_IN_SET("' . $value . '",channel_partner.sale_persons)>0');
					} else {
						$query2->orWhereRaw('FIND_IN_SET("' . $value . '",channel_partner.sale_persons)>0');
					}
				}
			});

		}

		$recordsTotal = $query->count();
		$recordsFiltered = $recordsTotal;

		$query = DB::table('users');
		$query->select($selectColumns);
		$query->leftJoin('channel_partner', 'channel_partner.id', '=', 'users.reference_id');

		if ($isSalePerson == 1) {

			$query->where(function ($query2) use ($childSalePersonsIds) {
				foreach ($childSalePersonsIds as $key => $value) {
					if ($key == 0) {
						$query2->whereRaw('FIND_IN_SET("' . $value . '",channel_partner.sale_persons)>0');
					} else {
						$query2->orWhereRaw('FIND_IN_SET("' . $value . '",channel_partner.sale_persons)>0');
					}
				}
			});

		}
		$query->whereIn('users.type', array(101, 102, 103, 104, 105));

		$query->where('channel_partner.created_at', '>=', $startDate);
		$query->where('channel_partner.created_at', '<=', $endDate);
		if ($request->sales_user_id != 0) {

			$query->whereRaw('FIND_IN_SET("' . $request->sales_user_id . '",channel_partner.sale_persons)>0');

		}

		$query->limit($request->length);
		$query->offset($request->start);
		$query->orderBy($sortColumns[$request['order'][0]['column']], $request['order'][0]['dir']);
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

		$data = json_decode(json_encode($data), true);

		if ($isFilterApply == 1) {
			$recordsFiltered = count($data);
		}

		foreach ($data as $key => $value) {

			$data[$key]['id'] = '<div class="avatar-xs"><span class="avatar-title rounded-circle">' . $data[$key]['id'] . '</span></div>';

			$data[$key]['name'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">' . highlightString($value['firm_name'],$search_value) . '</a></h5>
             <p class="text-muted mb-0">' . highlightString(getUserTypeName($value['type']),$search_value) . '</p>';

			if ($data[$key]['created_at'] == $data[$key]['last_active_date_time']) {

				$data[$key]['last_active_date_time'] = "-";
				$data[$key]['last_login_date_time'] = "-";

			} else {

				$data[$key]['last_active_date_time'] = convertDateTime($value['last_active_date_time']);
				$data[$key]['last_login_date_time'] = convertDateTime($value['last_login_date_time']);

			}

			$data[$key]['active_login'] = '<p class="text-muted mb-0">' . $data[$key]['last_active_date_time'] . '</p>
             <p class="text-muted mb-0">' . $data[$key]['last_login_date_time'] . '</p>';

			$data[$key]['status'] = getUserStatusLable($value['status']);
			$data[$key]['email'] = '<p class="text-muted mb-0">' . highlightString($value['email'],$search_value) . '</p>
             <p class="text-muted mb-0">' . highlightString($value['phone_number'],$search_value) . '</p><p class="text-muted mb-0">' . highlightString(($value['gst_number']),$search_value) . '</p>';

			$invoiceFrom = "";

			if ($value['reporting_manager_id'] != 0) {

				$ChannelPartner = ChannelPartner::select('firm_name', 'type');
				$ChannelPartner->where('user_id', $value['reporting_manager_id']);
				$ChannelPartner = $ChannelPartner->first();
				if ($ChannelPartner) {

					$invoiceFrom = '<p class="text-muted mb-0">' . highlightString($ChannelPartner->firm_name,$search_value) . '</p>
             <p class="text-muted mb-0">' . highlightString(getUserTypeName($ChannelPartner->type),$search_value) . '</p>';

				}

			} else {

				$Company = array();
				$Company = Company::select('id', 'name');
				$Company->where('id', $value['reporting_company_id']);
				$Company = $Company->first();
				if ($Company) {
					$invoiceFrom = '<p class="text-muted mb-0">' . highlightString($Company->name,$search_value) . '</p>';

				}

			}

			$data[$key]['invoice_from'] = $invoiceFrom;

			$salePersons = DB::table('sale_person');
			$salePersons->select('users.id as id', DB::raw('CONCAT(first_name," ", last_name) AS text'));
			$salePersons->leftJoin('users', 'sale_person.user_id', '=', 'users.id');
			$salePersons->whereIn('users.id', explode(",", $value['sale_persons']));
			$salePersons = $salePersons->get();

			$StrsalePersons = "";
			foreach ($salePersons as $keySP => $valueSP) {

				$StrsalePersons .= '<p class="text-muted mb-0">' . highlightString($valueSP->text,$search_value) . '</p>';

			}

			$data[$key]['sale_persons'] = $StrsalePersons;

		}

		$jsonData = array(
			"draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
			"recordsTotal" => intval($recordsTotal), // total number of records
			"recordsFiltered" => intval($recordsFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
			"data" => $data, // total data array

		);
		return $jsonData;
	}
}