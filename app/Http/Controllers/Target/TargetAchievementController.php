<?php

namespace App\Http\Controllers\Target;

use App\Http\Controllers\Controller;

use App\Models\Wlmst_target;
use App\Models\Wlmst_targetdetail;
use App\Models\Wlmst_financialyear;
use App\Models\User;
use App\Models\SalePerson;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use stdClass;
//use Session;

class TargetAchievementController extends Controller
{

	public function __construct()
	{

		$this->middleware(function ($request, $next) {

			$tabCanAccessBy = array(0, 1, 2, 3);

			if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
				return redirect()->route('dashboard');
			}
			return $next($request);
		});
	}


	public function searchTargetViewType(Request $request)
	{
		$searchKeyword = $request->q;

		$Month_list = Wlmst_targetdetail::query();
		$Month_list->select('month_number as id', 'month_name AS text');
		$Month_list->orderBy('wlmst_targetdetail.month_number', 'ASC');
		$Month_list->where('wlmst_targetdetail.month_name', 'like', '%' . $searchKeyword . '%');
		$Month_list->groupBy(['month_number', 'month_name']);
		$Month_list_new = array();

		foreach ($Month_list->get() as $key => $value) {
			if ($key == 0) {
				$listMonth1['id'] = 0;
				$listMonth1['text'] = 'FULL YEAR';
				$listMonth['id'] = $value['id'];
				$listMonth['text'] = $value['text'];
				array_push($Month_list_new, $listMonth1);
				array_push($Month_list_new, $listMonth);
			} else {
				$listMonth['id'] = $value['id'];
				$listMonth['text'] = $value['text'];
				array_push($Month_list_new, $listMonth);
			}
		}

		$response = array();
		$response['results'] = $Month_list_new;
		$response['pagination']['more'] = false;

		return response()->json($response)->header('Content-Type', 'application/json');
	}
	public function searchSalesUser(Request $request)
	{
		$searchKeyword = $request->q;
		// $SalePerson = User::query();
		$isAdmin = isAdminOrCompanyAdmin();
		$isAccountUser = isAccountUser();
		$isSaleUser = isSalePerson();
		if($isAdmin == 1 || $isAccountUser == 1)
		{
			$ChildSalePerson = SalePerson::query();

			$ChildSalePerson->leftJoin('users', 'users.id' , '=', 'sale_person.user_id');
			$ChildSalePerson->select('users.id as id', DB::raw('CONCAT(first_name," ", last_name) AS text'));
			$ChildSalePerson->where('users.status', 1);
			$ChildSalePerson->where(function ($query) use ($searchKeyword) {
				$query->orWhere('users.first_name', 'like', '%' . $searchKeyword . '%');
				$query->orWhere('users.last_name', 'like', '%' . $searchKeyword . '%');
				$query->orWhereRaw('CONCAT(users.first_name," ",users.last_name)' . ' like ? ', ["%" . $searchKeyword . "%"]);
			});
		}
		else if($isSaleUser == 1)
		{
			$chiledSalePersonId = getChildSalePersonsIds(Auth::user()->id);
			$ChildSalePerson = User::query();
			
			$ChildSalePerson->leftJoin('sale_person', 'sale_person.user_id', '=', 'users.id');
			$ChildSalePerson->select('users.id as id', DB::raw('CONCAT(first_name," ", last_name) AS text'));
			$ChildSalePerson->where('users.status', 1);
			$ChildSalePerson->whereIn('sale_person.reporting_manager_id', $chiledSalePersonId);
			$ChildSalePerson->where(function ($query) use ($searchKeyword) {
				$query->orWhere('users.first_name', 'like', '%' . $searchKeyword . '%');
				$query->orWhere('users.last_name', 'like', '%' . $searchKeyword . '%');
				$query->orWhereRaw('CONCAT(users.first_name," ",users.last_name)' . ' like ? ', ["%" . $searchKeyword . "%"]);
			});
		}

		
		$ChildSalePerson->limit(10);	
		$ChildSalePerson = $ChildSalePerson->get();
		
		$response = array();
		$response['results'] = $ChildSalePerson;
		$response['pagination']['more'] = false;

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function searchJoiningDate(Request $request)
	{
		$employee_id = $request->employee_id;
		$SalePerson = Wlmst_target::query();
		$SalePerson->where('wlmst_target.employeee_id', $employee_id);
		$SalePerson = $SalePerson->first();
		if ($SalePerson) {
			$response = successRes("Successfully get user data");
			$response['data'] = $SalePerson;
		} else {
			$response = errorRes("Invalid id");
		}
		return response()->json($response)->header('Content-Type', 'application/json');
	}
	public function searchFinancialYear(Request $request)
	{
		$FYList = array();
		$FYList = Wlmst_financialyear::select('id', 'name as text');
		// $GroupList->where('company_id', $request->company_id);
		$FYList->where('name', 'like', "%" . $request->q . "%");
		$FYList->limit(5);
		$FYList = $FYList->get();

		$response = array();
		$response['results'] = $FYList;
		$response['pagination']['more'] = false;
		
		if (date('m') > 3) {
			$financialyear = date('Y') . "-" . (date('Y') + 1);
		} else {
			$financialyear = (date('Y') - 1) . "-" . date('Y');
		}

		$currunt_finYear = Wlmst_financialyear::query();
		$currunt_finYear->select('*');
		$currunt_finYear->where('wlmst_financialyear.name', $financialyear);
		$currunt_finYear = $currunt_finYear->first();
		if ($currunt_finYear) {
			$response['currunt_fy_id'] = $currunt_finYear->id;
			$response['currunt_fy_name'] = $currunt_finYear->name;
		}else{
			$response['currunt_fy_id'] = 0;
			$response['currunt_fy_name'] = 0;
		}
		return response()->json($response)->header('Content-Type', 'application/json');
	}
	public function curruntFinancialYear(Request $request)
	{
			
		if (date('m') > 3) {
			$financialyear = date('Y') . "-" . (date('Y') + 1);
		} else {
			$financialyear = (date('Y') - 1) . "-" . date('Y');
		}

		$currunt_finYear = Wlmst_financialyear::query();
		$currunt_finYear->select('*');
		$currunt_finYear->where('wlmst_financialyear.name', $financialyear);
		$currunt_finYear = $currunt_finYear->first();
		if ($currunt_finYear) {
			$response['currunt_fy_id'] = $currunt_finYear->id;
			$response['currunt_fy_name'] = $currunt_finYear->name;
		}else{
			$response['currunt_fy_id'] = 0;
			$response['currunt_fy_name'] = 0;
		}
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function index()
	{
		$data = array();
		$data['title'] = "Target Achievement";
		return view('target/targetachievement', compact('data'));
	}

	function ajax(Request $request)
	{
		//DB::enableQueryLog();

		$searchColumns = array(
			0 => 'users.first_name',
			1 => 'users.last_name',
			2 => "CONCAT(users.first_name,' ',users.last_name)",
		);

		$columns = array(
			'wlmst_target.id',
			'wlmst_target.employeee_id',
			'wlmst_target.finyear_id',
			'wlmst_target.minachivement',
			'wlmst_target.total_target',
			'wlmst_target.distribute_type',
			'wlmst_target.created_at',
			'wlmst_target.updated_at',
			'wlmst_financialyear.name as financial_year',
			'users.first_name',
			'users.status as user_status'
		);

		 // when there is no search parameter then total number rows = total number filtered rows.

		$isSalePerson = isSalePerson();
		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		$isAccountUser = isAccountUser();
		if ($isSalePerson == 1) {
			$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
		}
		$query = Wlmst_target::query();
		$query->select($columns);
		$query->selectRaw("CONCAT(users.first_name,' ', users.last_name) AS sales_person_name");
		// $query->leftJoin('users', 'users.id', '=', 'wlmst_target.employeee_id');
		$query->leftJoin('users', 'users.id', '=', 'wlmst_target.employeee_id');
		$query->leftJoin('wlmst_financialyear', 'wlmst_financialyear.id', '=', 'wlmst_target.finyear_id');
        $query->where('users.status', 1);
		if (isset($request->financial_year)) {
			$query->where('wlmst_target.finyear_id', $request->financial_year);
		}
		
		if($isSalePerson == 1)
		{
			$query->leftJoin('sale_person', 'sale_person.user_id', '=', 'users.id');
			$query->whereIn('sale_person.user_id', $childSalePersonsIds);
		}
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
						$query->whereRaw($searchColumns[$i] . ' like ? ', [$search_value]);
						$query->orWhereRaw($searchColumns[$i] . ' like ? ', ["%" . $search_value . "%"]);
					} else {

						$query->orWhereRaw($searchColumns[$i] . ' like ? ', [$search_value]);
						$query->orWhereRaw($searchColumns[$i] . ' like ? ', ["%" . $search_value . "%"]);
					}
				}
			});
		}


		$data = $query->get();

		if(Auth::user()->id == 2)
		{
			$recordsTotal = count($data);
		}else
		{
			$recordsTotal = Wlmst_target::query();
			$recordsTotal->leftJoin('users', 'users.id', '=', 'wlmst_target.employeee_id');
			if (isset($request->financial_year)) {
				$recordsTotal->where('wlmst_target.finyear_id', $request->financial_year);
			}
			
			if($isSalePerson == 1)
			{
				$recordsTotal->leftJoin('sale_person', 'sale_person.user_id', '=', 'users.id');

				$recordsTotal->whereIn('sale_person.reporting_manager_id', $childSalePersonsIds);
			}
			$recordsTotal = $recordsTotal->count();
		}
		$recordsFiltered = $recordsTotal;

		// echo "<pre>";
		// print_r(DB::getQueryLog());
		// die;

		$data = json_decode(json_encode($data), true);

		if ($isFilterApply == 1) {
			$recordsFiltered = count($data);
		}

		foreach ($data as $key => $value) {
			$user_status = $value['user_status'];
			$user_status_lable = getUserStatusLable($value['user_status']);

			$data[$key]['name'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" onclick="targetView(\'' . $value['id'] . '\',\'' . $value['employeee_id'] . '\',\'' . $value['finyear_id'] . '\',\'' . $value['financial_year'] . '\')" >' .  highlightString($data[$key]['sales_person_name'],$search_value) . '</span></a> ' . $user_status_lable . '</h5>
            <p class="text-muted mb-0">' . $data[$key]['financial_year'] . '</p>';

			$total_target = 0;
			if ($request->view_type == 0 || $request->view_type == '') {
				$total_target = (int)$data[$key]['total_target'];
				$data[$key]['total_target'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">RS. ' . (int)$data[$key]['total_target'] . '</a></h5>';
			} else {
				$montly_target = Wlmst_targetdetail::query();
				$montly_target->select('target_amount');
				$montly_target->where('wlmst_targetdetail.target_id', $value['id']);
				$montly_target->where('wlmst_targetdetail.month_number', $request->view_type);
				$montly_target = $montly_target->first();
				
				if($montly_target){
					$total_target = (int)$montly_target->target_amount;
					$data[$key]['total_target'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">RS. ' . (int)$montly_target->target_amount . '</a></h5>';
				} else {
					$data[$key]['total_target'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">RS. 0</a></h5>';
				}
			}


			if ($request->view_type == 0 || $request->view_type == '') {
				$start_year = explode("-", $value['financial_year'])[0];
				$end_year = explode("-", $value['financial_year'])[1];
				$start_date = '01-04-' . $start_year;
				$end_date = '31-03-' . $end_year;

				$startDate = date('Y-m-d 00:00:00', strtotime($start_date));

				$endDate = date('Y-m-d 00:00:00', strtotime($end_date));
			} else {

				$startDate = getDatesFromMonth($request->view_type, $value['financial_year'])['start'];

				$endDate = getDatesFromMonth($request->view_type, $value['financial_year'])['end'];
			}

			$childSalePersonsIdsinv = getChildSalePersonsIds($value['employeee_id']);


			$allSalesUserIds = array_unique($childSalePersonsIdsinv);
			$allSalesUserIds = array_values($allSalesUserIds);

			$orderAmount = Invoice::query();
			$orderAmount->selectRaw('SUM(invoice.total_mrp_minus_disocunt) as amount');
			$orderAmount->leftJoin('orders', 'orders.id', '=', 'invoice.order_id');
			$orderAmount->whereIn('invoice.status', array(2, 3));
			$orderAmount->where('orders.created_at', '>=', $startDate);
			$orderAmount->where('orders.created_at', '<=', $endDate);
			$orderAmount->whereIn('orders.user_id', $allSalesUserIds);
			$orderAmount = $orderAmount->first();


			if ($orderAmount != null) {
				$achieved_amt = ($orderAmount->amount == '') ? '00.00' : $orderAmount->amount;
				$achieved_per = getpercentage($total_target, $achieved_amt);
			} else {
				$achieved_amt = 0;
				$achieved_per = 0;
			}

			if ($achieved_per < $value['minachivement']) {
				$achievecolour = 'text-danger';
			} elseif ($achieved_per < 100.00 && $achieved_per >= $value['minachivement']) {
				$achievecolour = 'text-primary';
			} elseif ($achieved_per >= 100) {
				$achievecolour = 'text-success';
			} else {
				$achievecolour = 'text-dark';
			}

			$data[$key]['achived_target'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="' . $achievecolour . '">RS. ' . (int)$achieved_amt . '</a></h5>';
			$data[$key]['achived_per'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="' . $achievecolour . '">' . (int)$achieved_per . '%</a></h5>';

			$uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';

			$uiAction .= '<li class="list-inline-item px-2">';
			$uiAction .= '<a data-html="true" data-bs-toggle="tooltip" href="javascript: void(0);" id="tootltip_' . $value['id'] . '"
			created_at = "' . convertDateTime($value['created_at']) . '"
			updated_at = "' . convertDateTime($value['updated_at']) . '"
			title="Created Date & Time : ' . convertDateTime($value['created_at']) . '"
			><i class="bx bx-calendar"></i></a>';
			$uiAction .= '</li>';

			if ($user_status == 1  && $isAdminOrCompanyAdmin == 1 || $isAccountUser == 1) {
				$uiAction .= '<li class="list-inline-item px-2">';
				$uiAction .= '<a onclick="editView(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Edit"><i class="bx bx-edit-alt"></i></a>';
				$uiAction .= '</li>';
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

	public function save(Request $request)
	{

		$validator = Validator::make($request->all(), [
			'q_employee_id' => ['required'],
			'q_fy_id' => ['required'],
			'q_min_achievement' => ['required'],
			'q_total_target' => ['required'],
			'distribute_type_value' => ['required'],
		]);
		if ($validator->fails()) {

			$response = array();
			$response['status'] = 0;
			$response['msg'] = "The request could not be understood by the server due to malformed syntax";
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();

			return response()->json($response)->header('Content-Type', 'application/json');
		} else {
			$Total_Target = number_format(floatval($request->q_total_target), 2, '.', '');
			$SumOfAllMonth = number_format(floatval($request->q_april_target) +
				floatval($request->q_may_target) +
				floatval($request->q_june_target) +
				floatval($request->q_july_target) +
				floatval($request->q_august_target) +
				floatval($request->q_september_target) +
				floatval($request->q_october_target) +
				floatval($request->q_november_target) +
				floatval($request->q_december_target) +
				floatval($request->q_january_target) +
				floatval($request->q_february_target) +
				floatval($request->q_march_target), 2, '.', '');

			$alreadyYear = Wlmst_target::query();

			if ($request->q_target_achievement_id > 0) {

				$alreadyYear->where('finyear_id', $request->q_fy_id);
				$alreadyYear->where('employeee_id', $request->q_employee_id);
				$alreadyYear->where('id', '!=', $request->q_target_achievement_id);
			} else {
				$alreadyYear->where('finyear_id', $request->q_fy_id);
				$alreadyYear->where('employeee_id', $request->q_employee_id);
			}

			$alreadyYear = $alreadyYear->first();

			if ($alreadyYear) {
				$response = errorRes("already exits this year target, Try with another year");
			} elseif ($Total_Target != $SumOfAllMonth) {
				$response = errorRes("all month target not match with total target");
			} else {

				if ($request->q_target_achievement_id > 0) {
					$Target = Wlmst_target::find($request->q_target_achievement_id);
					$Target->updateby = Auth::user()->id;
					$Target->updateip = $request->ip();
				} else {
					$Target = new Wlmst_target();
					$Target->entryby = Auth::user()->id;
					$Target->entryip = $request->ip();
				}

				$Target->employeee_id = $request->q_employee_id;
				$Target->joining_date = $request->q_joining_date;
				$Target->finyear_id = $request->q_fy_id;
				$Target->minachivement = $request->q_min_achievement;
				$Target->total_target = $request->q_total_target;
				$Target->distribute_type = $request->distribute_type_value;
				$Target->incremental_per = $request->q_incremental_per == null ? 0 : $request->q_incremental_per;
				$Target->source = 'WEB';
				$Target->save();

				//APRIL
				if ($request->april_td_id > 0) {
					$TargetDetail = Wlmst_targetdetail::find($request->april_td_id);
					$TargetDetail->updateby = Auth::user()->id;
					$TargetDetail->updateip = $request->ip();
				} else {
					$TargetDetail = new Wlmst_targetdetail();
					$TargetDetail->entryby = Auth::user()->id;
					$TargetDetail->entryip = $request->ip();
				}
				$TargetDetail->target_id = $Target->id;
				$TargetDetail->quater = getQuaterFromMonth(4);
				$TargetDetail->month_number = '4';
				$TargetDetail->month_name = 'APRIL';
				$TargetDetail->target_amount = $request->q_april_target;
				$TargetDetail->source = 'WEB';
				$TargetDetail->save();
				//MAY
				if ($request->may_td_id > 0) {
					$TargetDetail = Wlmst_targetdetail::find($request->may_td_id);
					$TargetDetail->updateby = Auth::user()->id;
					$TargetDetail->updateip = $request->ip();
				} else {
					$TargetDetail = new Wlmst_targetdetail();
					$TargetDetail->entryby = Auth::user()->id;
					$TargetDetail->entryip = $request->ip();
				}
				$TargetDetail->target_id = $Target->id;
				$TargetDetail->month_number = '5';
				$TargetDetail->quater = getQuaterFromMonth(5);
				$TargetDetail->month_name = 'MAY';
				$TargetDetail->target_amount = $request->q_may_target;
				$TargetDetail->source = 'WEB';
				$TargetDetail->save();
				//JUNE
				if ($request->june_td_id > 0) {
					$TargetDetail = Wlmst_targetdetail::find($request->june_td_id);
					$TargetDetail->updateby = Auth::user()->id;
					$TargetDetail->updateip = $request->ip();
				} else {
					$TargetDetail = new Wlmst_targetdetail();
					$TargetDetail->entryby = Auth::user()->id;
					$TargetDetail->entryip = $request->ip();
				}
				$TargetDetail->target_id = $Target->id;
				$TargetDetail->quater = getQuaterFromMonth(6);
				$TargetDetail->month_number = '6';
				$TargetDetail->month_name = 'JUNE';
				$TargetDetail->target_amount = $request->q_june_target;
				$TargetDetail->source = 'WEB';
				$TargetDetail->save();
				//JULY
				if ($request->july_td_id > 0) {
					$TargetDetail = Wlmst_targetdetail::find($request->july_td_id);
					$TargetDetail->updateby = Auth::user()->id;
					$TargetDetail->updateip = $request->ip();
				} else {
					$TargetDetail = new Wlmst_targetdetail();
					$TargetDetail->entryby = Auth::user()->id;
					$TargetDetail->entryip = $request->ip();
				}
				$TargetDetail->target_id = $Target->id;
				$TargetDetail->quater = getQuaterFromMonth(7);
				$TargetDetail->month_number = '7';
				$TargetDetail->month_name = 'JULY';
				$TargetDetail->target_amount = $request->q_july_target;
				$TargetDetail->source = 'WEB';
				$TargetDetail->save();
				//AUGUST
				if ($request->august_td_id > 0) {
					$TargetDetail = Wlmst_targetdetail::find($request->august_td_id);
					$TargetDetail->updateby = Auth::user()->id;
					$TargetDetail->updateip = $request->ip();
				} else {
					$TargetDetail = new Wlmst_targetdetail();
					$TargetDetail->entryby = Auth::user()->id;
					$TargetDetail->entryip = $request->ip();
				}
				$TargetDetail->target_id = $Target->id;
				$TargetDetail->quater = getQuaterFromMonth(8);
				$TargetDetail->month_number = '8';
				$TargetDetail->month_name = 'AUGUST';
				$TargetDetail->target_amount = $request->q_august_target;
				$TargetDetail->source = 'WEB';
				$TargetDetail->save();
				//SEPTEMBER
				if ($request->september_td_id > 0) {
					$TargetDetail = Wlmst_targetdetail::find($request->september_td_id);
					$TargetDetail->updateby = Auth::user()->id;
					$TargetDetail->updateip = $request->ip();
				} else {
					$TargetDetail = new Wlmst_targetdetail();
					$TargetDetail->entryby = Auth::user()->id;
					$TargetDetail->entryip = $request->ip();
				}
				$TargetDetail->target_id = $Target->id;
				$TargetDetail->quater = getQuaterFromMonth(9);
				$TargetDetail->month_number = '9';
				$TargetDetail->month_name = 'SEPTEMBER';
				$TargetDetail->target_amount = $request->q_september_target;
				$TargetDetail->source = 'WEB';
				$TargetDetail->save();
				//OCTOMBER
				if ($request->octomber_td_id > 0) {
					$TargetDetail = Wlmst_targetdetail::find($request->octomber_td_id);
					$TargetDetail->updateby = Auth::user()->id;
					$TargetDetail->updateip = $request->ip();
				} else {
					$TargetDetail = new Wlmst_targetdetail();
					$TargetDetail->entryby = Auth::user()->id;
					$TargetDetail->entryip = $request->ip();
				}
				$TargetDetail->target_id = $Target->id;
				$TargetDetail->quater = getQuaterFromMonth(10);
				$TargetDetail->month_number = '10';
				$TargetDetail->month_name = 'OCTOMBER';
				$TargetDetail->target_amount = $request->q_october_target;
				$TargetDetail->source = 'WEB';
				$TargetDetail->save();
				//NOVEMBER
				if ($request->november_td_id > 0) {
					$TargetDetail = Wlmst_targetdetail::find($request->november_td_id);
					$TargetDetail->updateby = Auth::user()->id;
					$TargetDetail->updateip = $request->ip();
				} else {
					$TargetDetail = new Wlmst_targetdetail();
					$TargetDetail->entryby = Auth::user()->id;
					$TargetDetail->entryip = $request->ip();
				}
				$TargetDetail->target_id = $Target->id;
				$TargetDetail->quater = getQuaterFromMonth(11);
				$TargetDetail->month_number = '11';
				$TargetDetail->month_name = 'NOVEMBER';
				$TargetDetail->target_amount = $request->q_november_target;
				$TargetDetail->source = 'WEB';
				$TargetDetail->save();
				//DECEMBER
				if ($request->december_td_id > 0) {
					$TargetDetail = Wlmst_targetdetail::find($request->december_td_id);
					$TargetDetail->updateby = Auth::user()->id;
					$TargetDetail->updateip = $request->ip();
				} else {
					$TargetDetail = new Wlmst_targetdetail();
					$TargetDetail->entryby = Auth::user()->id;
					$TargetDetail->entryip = $request->ip();
				}
				$TargetDetail->target_id = $Target->id;
				$TargetDetail->quater = getQuaterFromMonth(12);
				$TargetDetail->month_number = '12';
				$TargetDetail->month_name = 'DECEMBER';
				$TargetDetail->target_amount = $request->q_december_target;
				$TargetDetail->source = 'WEB';
				$TargetDetail->save();
				//JANUARY
				if ($request->january_td_id > 0) {
					$TargetDetail = Wlmst_targetdetail::find($request->january_td_id);
					$TargetDetail->updateby = Auth::user()->id;
					$TargetDetail->updateip = $request->ip();
				} else {
					$TargetDetail = new Wlmst_targetdetail();
					$TargetDetail->entryby = Auth::user()->id;
					$TargetDetail->entryip = $request->ip();
				}
				$TargetDetail->target_id = $Target->id;
				$TargetDetail->quater = getQuaterFromMonth(1);
				$TargetDetail->month_number = '1';
				$TargetDetail->month_name = 'JANUARY';
				$TargetDetail->target_amount = $request->q_january_target;
				$TargetDetail->source = 'WEB';
				$TargetDetail->save();
				//FEBRUARY
				if ($request->february_td_id > 0) {
					$TargetDetail = Wlmst_targetdetail::find($request->february_td_id);
					$TargetDetail->updateby = Auth::user()->id;
					$TargetDetail->updateip = $request->ip();
				} else {
					$TargetDetail = new Wlmst_targetdetail();
					$TargetDetail->entryby = Auth::user()->id;
					$TargetDetail->entryip = $request->ip();
				}
				$TargetDetail->target_id = $Target->id;
				$TargetDetail->quater = getQuaterFromMonth(2);
				$TargetDetail->month_number = '2';
				$TargetDetail->month_name = 'FEBRUARY';
				$TargetDetail->target_amount = $request->q_february_target;
				$TargetDetail->source = 'WEB';
				$TargetDetail->save();
				//MARCH
				if ($request->march_td_id > 0) {
					$TargetDetail = Wlmst_targetdetail::find($request->march_td_id);
					$TargetDetail->updateby = Auth::user()->id;
					$TargetDetail->updateip = $request->ip();
				} else {
					$TargetDetail = new Wlmst_targetdetail();
					$TargetDetail->entryby = Auth::user()->id;
					$TargetDetail->entryip = $request->ip();
				}
				$TargetDetail->target_id = $Target->id;
				$TargetDetail->quater = getQuaterFromMonth(3);
				$TargetDetail->month_number = '3';
				$TargetDetail->month_name = 'MARCH';
				$TargetDetail->target_amount = $request->q_march_target;
				$TargetDetail->source = 'WEB';
				$TargetDetail->save();

				if ($Target) {

					if ($request->q_target_achievement_id != 0) {
						$response = successRes("Successfully saved target");

						$debugLog = array();
						$debugLog['name'] = "target-master-edit";
						$debugLog['description'] = "target master #" . $Target->id . " has been updated ";
						saveDebugLog($debugLog);
					} else {
						$response = successRes("Successfully added target");

						$debugLog = array();
						$debugLog['name'] = "target-master-add";
						$debugLog['description'] = "target master #" . $Target->id . " has been added ";
						saveDebugLog($debugLog);
					}
				}
			}

			return response()->json($response)->header('Content-Type', 'application/json');
		}
	}

	public function detail(Request $request)
	{

		$columns = array(
			'wlmst_target.id',
			'wlmst_target.employeee_id',
			'wlmst_target.joining_date',
			'wlmst_target.finyear_id',
			'wlmst_target.minachivement',
			'wlmst_target.total_target',
			'wlmst_target.distribute_type',
			'wlmst_target.incremental_per',
			'wlmst_target.created_at',
			'wlmst_financialyear.name as financial_year',
		);

		$QueryTarget = Wlmst_target::query();
		$QueryTarget->select($columns);
		$QueryTarget->selectRaw("CONCAT(users.first_name,' ', users.last_name) AS sales_person_name");
		$QueryTarget->leftJoin('users', 'users.id', '=', 'wlmst_target.employeee_id');
		$QueryTarget->leftJoin('wlmst_financialyear', 'wlmst_financialyear.id', '=', 'wlmst_target.finyear_id');
		$QueryTarget->where('wlmst_target.id', $request->id);
		$QueryTarget = $QueryTarget->first();

		$TargetDetail = Wlmst_targetdetail::query();
		$TargetDetail->select('*');
		$TargetDetail->where('wlmst_targetdetail.target_id', $QueryTarget->id);
		$TargetDetail->orderBy('wlmst_targetdetail.month_number', 'ASC');

		$TargetDetail = $TargetDetail->get();

		$TargetData['basic'] = $QueryTarget;
		$TargetData['detail'] = $TargetDetail;


		if ($QueryTarget) {

			$response = successRes("Successfully get Target data");
			$response['data'] = $TargetData;
		} else {
			$response = errorRes("Invalid id");
		}
		return response()->json($response)->header('Content-Type', 'application/json');
	}
}
