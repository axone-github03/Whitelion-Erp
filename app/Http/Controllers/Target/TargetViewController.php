<?php

namespace App\Http\Controllers\Target;

use App\Http\Controllers\Controller;

use App\Models\Wlmst_target;
use App\Models\Wlmst_targetdetail;
use App\Models\Wlmst_financialyear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

//use Session;

class TargetViewController extends Controller
{

	public function __construct()
	{

		$this->middleware(function ($request, $next) {
			return $next($request);
		});
	}

	public function searchTVFinancialYear(Request $request)
	{
		$Tv_finYear = Wlmst_target::query();
		$Tv_finYear->select('wlmst_target.finyear_id as id', 'wlmst_financialyear.name as text');
		$Tv_finYear->leftJoin('wlmst_financialyear', 'wlmst_target.finyear_id', '=', 'wlmst_financialyear.id');
		$Tv_finYear->where('wlmst_target.employeee_id', $request->target_customer);
		$Tv_finYear->where('wlmst_financialyear.name', 'like', '%' . $request->q . '%');
		$Tv_finYear->groupBy('wlmst_target.finyear_id', 'wlmst_financialyear.name');
		$Tv_finYear->limit(5);
		$Tv_finYear = $Tv_finYear->get();
		$response = array();
		$response['results'] = $Tv_finYear;
		$response['pagination']['more'] = false;

		return response()->json($response)->header('Content-Type', 'application/json');
	}
	public function searchTVSalesUaer(Request $request)
	{
		$chiledSalePersonId = getChildSalePersonsIds(Auth::user()->id);
		$Tv_sales_person = Wlmst_target::query();
		$Tv_sales_person->select('wlmst_target.finyear_id', 'wlmst_financialyear.name as fy_name', 'wlmst_target.employeee_id as id', DB::raw('CONCAT(users.first_name," ", users.last_name) AS text'));
		$Tv_sales_person->leftJoin('users', 'wlmst_target.employeee_id', '=', 'users.id');
		$Tv_sales_person->leftJoin('wlmst_financialyear', 'wlmst_target.finyear_id', '=', 'wlmst_financialyear.id');
		if(Auth::user()->type == 2)
		{
			$Tv_sales_person->leftJoin('sale_person', 'wlmst_target.employeee_id', '=', 'sale_person.user_id');
			$Tv_sales_person->whereIn('sale_person.user_id', $chiledSalePersonId);
		}
        $Tv_sales_person->where('users.status', 1);
		$Tv_sales_person->distinct('wlmst_target.employeee_id');
		$Tv_sales_person->groupBy(['users.first_name', 'users.last_name', 'wlmst_financialyear.name', 'wlmst_target.finyear_id','wlmst_target.employeee_id']);
		
		if(isset($request->q)){
			$searchKeyword = $request->q;
			$Tv_sales_person->where(function ($query) use ($searchKeyword) {
				$query->orWhere('users.first_name', 'like', '%' . $searchKeyword . '%');
				$query->orWhere('users.last_name', 'like', '%' . $searchKeyword . '%');
				$query->orWhereRaw('CONCAT(users.first_name," ",users.last_name)' . ' like ? ', ["%" . $searchKeyword . "%"]);
			});
		}
		
		$Tv_sales_person_list = array();
		
		foreach ($Tv_sales_person->distinct()->get() as $key => $value) {

			date_default_timezone_set("Asia/Kolkata");
			if (date('m') > 6) {
				$financialyear = date('Y') . "-" . (date('Y') + 1);
			} else {
				$financialyear = (date('Y') - 1) . "-" . date('Y');
			}

			$Tv_finYear = Wlmst_financialyear::query();
			$Tv_finYear->select('*');
			$Tv_finYear->where('wlmst_financialyear.name', $financialyear);
			$Tv_finYear = $Tv_finYear->first();

			if ($Tv_finYear != 'null') {
				$Tv_sales_person_new = Wlmst_target::query();
				$Tv_sales_person_new->select('wlmst_target.finyear_id', 'wlmst_financialyear.name as fy_name', 'wlmst_target.employeee_id as emp_id', DB::raw('CONCAT(users.first_name," ", users.last_name) AS text'));
				$Tv_sales_person_new->leftJoin('users', 'wlmst_target.employeee_id', '=', 'users.id');
				$Tv_sales_person_new->leftJoin('wlmst_financialyear', 'wlmst_target.finyear_id', '=', 'wlmst_financialyear.id');
				$Tv_sales_person_new->where('wlmst_target.finyear_id', $Tv_finYear->id);
				$Tv_sales_person_new->where('wlmst_target.employeee_id', $value['id']);
				$Tv_sales_person_new->groupBy(['users.first_name', 'users.last_name', 'wlmst_financialyear.name', 'wlmst_target.finyear_id','wlmst_target.employeee_id']);
				$new_sales_pe = $Tv_sales_person_new->first();

				if ($new_sales_pe != 'null') {

					$new_list['id'] =  $value['id'];
					$new_list['text'] = $value['text'];
					$new_list['fynancial_year'] = $Tv_finYear->id;
					$new_list['fynancial_year_name'] = $Tv_finYear->name;
				} else {
					$new_list['id'] = $value['id'];
					$new_list['text'] = $value['text'];
					$new_list['fynancial_year'] = $value['finyear_id'];
					$new_list['fynancial_year_name'] = $value['fy_name'];
				}
			} else {
				$new_list['id'] = $value['id'];
				$new_list['text'] = $value['text'];
				$new_list['fynancial_year'] = $value['finyear_id'];
				$new_list['fynancial_year_name'] = $value['fy_name'];
			}

			array_push($Tv_sales_person_list, $new_list);
		}
		$response = array();
		$response['status'] = 1;
		$response['msg'] = 'success';
		// $response['data'] = collect($Tv_sales_person_list)->unique();
		$response['data'] = $Tv_sales_person_list;
		// $response['data'] = unique_multidim_array($Tv_sales_person_list,'id');

		// return response()->json($response)->header('Content-Type', 'application/json');
		// return successRes("Successfully saved target");
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function targetViewDetail(Request $request)
	{

		$columns = array(
			'wlmst_target.id',
			'wlmst_target.employeee_id',
			'wlmst_target.finyear_id',
			'wlmst_target.minachivement',
			'wlmst_target.total_target',
			'wlmst_target.distribute_type',
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
		$TargetData['monthly_target'] = $TargetDetail;


		if ($QueryTarget) {

			$response = successRes("Successfully get Target data");
			$response['data'] = $TargetData;
		} else {
			$response = errorRes("Invalid id");
		}
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function targetViewData(Request $request)
	{
		$salesUserIds = $request->sales_user_id;

		$startDate = date('Y-m-d 00:00:00', strtotime($request->start_date));
		// $startDate = date('Y-m-d H:i:s', strtotime($startDate . " -5 hours"));
		// $startDate = date('Y-m-d H:i:s', strtotime($startDate . " -30 minutes"));

		$endDate = date('Y-m-d 00:00:00', strtotime($request->end_date));
		// $endDate = date('Y-m-d H:i:s', strtotime($endDate . " -5 hours"));
		// $endDate = date('Y-m-d H:i:s', strtotime($endDate . " -30 minutes"));

		$financialYearId = $request->financial_year_id;
		$dataViewType = $request->data_view_type;

		DB::enableQueryLog();



		$columns = array(
			'wlmst_target.id',
			'wlmst_target.employeee_id',
			'wlmst_target.finyear_id',
			'wlmst_target.minachivement',
			'wlmst_target.total_target',
			'wlmst_target.distribute_type',
			'wlmst_target.created_at',
			'wlmst_financialyear.name as financial_year',
		);

		$QueryTarget = Wlmst_target::query();
		$QueryTarget->select($columns);
		$QueryTarget->selectRaw("CONCAT(users.first_name,' ', users.last_name) AS sales_person_name");
		$QueryTarget->leftJoin('users', 'users.id', '=', 'wlmst_target.employeee_id');
		$QueryTarget->leftJoin('wlmst_financialyear', 'wlmst_financialyear.id', '=', 'wlmst_target.finyear_id');
		$QueryTarget->where('wlmst_target.employeee_id', $salesUserIds);
		$QueryTarget->where('wlmst_target.finyear_id', $financialYearId);
		$QueryTarget = $QueryTarget->first();

		$monthly_data = '';
		$quterly_data = '';
		$target_q1 = 0;
		$achieve_q1 = 0;

		if ($QueryTarget != null) {

			$TargetDetail = Wlmst_targetdetail::query();
			$TargetDetail->select('*');
			$TargetDetail->where('wlmst_targetdetail.target_id', $QueryTarget->id);
			$TargetDetail->orderBy('wlmst_targetdetail.id', 'ASC');
			$TargetDetail = $TargetDetail->get();

			$monthly_data = '';
			$quterly_data = '';
			$yearly_data = '';
			$year_total_target = 0;
			$year_total_achieved = 0;
			$target_q1 = 0;
			$achieve_q1 = 0;

			foreach ($TargetDetail as $key => $value) {
				$childSalePersonsIds = getChildSalePersonsIds($salesUserIds);

				$allSalesUserIds = array_unique($childSalePersonsIds);
				$allSalesUserIds = array_values($allSalesUserIds);

				switch ($dataViewType) {
					case 1:
						$orderAmount = Order::query();
						$orderAmount->select(DB::raw('MONTH(orders.created_at) as month'));
						$orderAmount->selectRaw('SUM(orders.total_mrp_minus_disocunt) as amount');
						$orderAmount->where('orders.status', '!=', 4);
						$orderAmount->where('orders.created_at', '>=', $startDate);
						$orderAmount->where('orders.created_at', '<=', $endDate);
						$orderAmount->whereMonth('orders.created_at', $value->month_number);
						$orderAmount->whereIn('orders.user_id', $allSalesUserIds);
						$orderAmount->groupby('month');
						$orderAmount = $orderAmount->first();
						break;

					case 2:
						$orderAmount = Invoice::query();
						$orderAmount->select(DB::raw('MONTH(orders.created_at) as month'));
						$orderAmount->selectRaw('SUM(invoice.total_mrp_minus_disocunt) as amount');
						$orderAmount->leftJoin('orders', 'orders.id', '=', 'invoice.order_id');
						$orderAmount->whereIn('invoice.status', array(2, 3));
						$orderAmount->where('orders.created_at', '>=', $startDate);
						$orderAmount->where('orders.created_at', '<=', $endDate);
						$orderAmount->whereMonth('orders.created_at', $value->month_number);
						$orderAmount->whereIn('orders.user_id', $allSalesUserIds);
						$orderAmount->groupby('month');
						$orderAmount = $orderAmount->first();
						break;

					case 3:
						$orderAmount = Order::query();
						$orderAmount->select(DB::raw('MONTH(orders.created_at) as month'));
						$orderAmount->selectRaw('SUM(orders.total_mrp_minus_disocunt) as amount');
						$orderAmount->where('orders.status', '!=', 4);
						$orderAmount->where('orders.created_at', '>=', $startDate);
						$orderAmount->where('orders.created_at', '<=', $endDate);
						$orderAmount->whereMonth('orders.created_at', $value->month_number);
						$orderAmount->whereIn('orders.user_id', $allSalesUserIds);
						$orderAmount->groupby('month');
						$orderAmount = $orderAmount->first();
						break;
				}

				// $monthly_data['t_id'] = $value->target_id;
				// $monthly_data['td_id'] = $value->id;
				// $monthly_data['month_number'] = $value->month_number;
				// $monthly_data['month_name'] = ucwords(strtolower($value->month_name));
				// $monthly_data['target_amount'] = $value->target_amount;
				// $monthly_data['freez_amount'] = $value->freez_amount;
				// $monthly_data['freez_date'] = $value->freez_date;
				// $monthly_data['isfreez'] = $value->isfreez;


				$monthly_data .= '<div class="row mb-2">';
				$monthly_data .= '<div class="col-2 p-1 align-self-center text-left">';
				$monthly_data .= '<label class="form-label text-dark">' . ucwords(strtolower($value->month_name)) . '</label>';
				$monthly_data .= '</div>';
				$monthly_data .= '<div class="col-3 p-1">';
				$monthly_data .= '<input type="text" class="form-control monthlytarget" id="' . $value->month_number . '_target" placeholder="₹" value="' . (int)$value->target_amount . '" name="' . $value->month_number . '_target" readonly>';
				$monthly_data .= '</div>';
				$monthly_data .= '<div class="col-3 p-1 amtfreezdiv">';

				if ($orderAmount != null) {
					if ($dataViewType == 1) {
						$achived_amt = number_format(floatval($orderAmount->amount), 2, '.', '');
						$achived_per = getpercentage($value->target_amount, $orderAmount->amount);
					} elseif ($dataViewType == 2) {
						$achived_amt = number_format(floatval($orderAmount->amount), 2, '.', '');
						$achived_per = getpercentage($value->target_amount, $orderAmount->amount);
					} elseif ($dataViewType == 3) {
						$achived_amt = ($value->isfreez == 1) ? number_format(floatval($value->freez_amount), 2, '.', '') : number_format(floatval($orderAmount->amount), 2, '.', '');
						$achived_per = ($value->isfreez == 1) ? getpercentage($value->target_amount, $value->freez_amount) : getpercentage($value->target_amount, $orderAmount->amount);
					}
				} else {
					if ($dataViewType == 3) {
						$achived_amt = ($value->isfreez == 1) ? number_format(floatval($value->freez_amount), 2, '.', '') : 0;
						$achived_per = ($value->isfreez == 1) ? getpercentage($value->target_amount, $value->freez_amount) : 0;
					} else {
						$achived_amt = 0;
						$achived_per = 0;
					}
					// $achived_amt = ($value->isfreez == 1) ? number_format(floatval($value->freez_amount), 2,'.', '') : 0;
					// $achived_per = ($value->isfreez == 1) ? getpercentage($value->target_amount, $value->freez_amount) : 0;
				}


				
				$isfreezvisible = ($dataViewType != 3) ? 'style="display:none;"' : '';
				$btncolour = ($value->isfreez == 1) ? 'danger' : 'primary';
				$btntext = ($value->isfreez == 1) ? 'Un Freeze' : 'Freeze';
				$btntextsize = '';

				if(Auth::user()->type == 0 || Auth::user()->type == 1)
				{
					$isachivededit = ($value->isfreez == 1) ? 'readonly' : ($dataViewType != 3) ? 'readonly' : '';

					$monthly_data .= '<input type="text" class="form-control monthly_achivement freez_amount" onkeypress="return isNumber(event); monthlyachieved" id="' . $value->month_number . '_achieved_amt" placeholder="₹" value="' . (int)$achived_amt . '" name="' . $value->month_number . '_achieved_amt" ' . $isachivededit . '>';

					$monthly_data .= '</div>';
					$monthly_data .= '<div class="col-2 p-1">';
					$monthly_data .= '<input type="text" class="form-control monthlyper" id="' . $value->month_number . '_achieved_per" placeholder="%" value="' . (int)$achived_per . '" name="' . $value->month_number . '_achieved_per" readonly>';

					$monthly_data .= '</div>';
					$monthly_data .= '<div class="col-2 p-1">';

					$monthly_data .= '<button onclick="saveTargetFreez(\'' . $value->id . '\',\'' . $value->month_number . '\')" data-isfreeze="' . $value->isfreez . '" class="btn btn-' . $btncolour . ' waves-effect waves-light ' . $btntextsize . ' freezbutton" id="' . $value->month_number . '_btn_freeze" ' . $isfreezvisible . '>' . $btntext . '</button>';
				}
				else{
					$isachivededit = ($value->isfreez == 1) ? 'readonly' : ($dataViewType != 3) ? 'readonly' : 'readonly';

					$monthly_data .= '<input type="text" class="form-control monthly_achivement freez_amount" onkeypress="return isNumber(event); monthlyachieved" id="' . $value->month_number . '_achieved_amt" placeholder="₹" value="' . (int)$achived_amt . '" name="' . $value->month_number . '_achieved_amt" ' . $isachivededit . '>';

					$monthly_data .= '</div>';
					$monthly_data .= '<div class="col-2 p-1">';
					$monthly_data .= '<input type="text" class="form-control monthlyper" id="' . $value->month_number . '_achieved_per" placeholder="%" value="' . (int)$achived_per . '" name="' . $value->month_number . '_achieved_per" readonly>';

					$monthly_data .= '</div>';
					$monthly_data .= '<div class="col-2 p-1">';
				}

				$monthly_data .= '</div>';
				$monthly_data .= '</div>';

				$target_q1 += $value->target_amount;
				$achieve_q1 += floatval($achived_amt);
				if ($key == 2) {
					$quterly_data .= '<div class="row mb-2 text-center">';
					$quterly_data .= '<div class="col-2 p-1 align-self-center">';
					$quterly_data .= '<label class="form-label text-dark">Q1</label>';
					$quterly_data .= '</div>';
					$quterly_data .= '<div class="col-3 p-1">';
					$quterly_data .= '<input type="text" class="form-control" placeholder="₹" value="' . (int)$target_q1 . '" readonly>';
					$quterly_data .= '</div>';
					$quterly_data .= '<div class="col-3 p-1">';
					$quterly_data .= '<input type="text" class="form-control" value="' . (int)$achieve_q1 . '" placeholder="₹" readonly>';
					$quterly_data .= '</div>';
					$quterly_data .= '<div class="col-2 p-1">';
					$quterly_data .= '<input type="text" class="form-control" value="' . (int)getpercentage($target_q1, $achieve_q1) . '" placeholder="%" readonly>';
					$quterly_data .= '</div>';
					$quterly_data .= '</div>';

					if((int)$target_q1 > 0){
						$year_total_target += (int)$target_q1;
						$year_total_achieved += (int)$achieve_q1;
					}

					$target_q1 = 0;
					$achieve_q1 = 0;
				}
				if ($key == 5) {
					$quterly_data .= '<div class="row mb-2 text-center">';
					$quterly_data .= '<div class="col-2 p-1 align-self-center">';
					$quterly_data .= '<label class="form-label text-dark">Q2</label>';
					$quterly_data .= '</div>';
					$quterly_data .= '<div class="col-3 p-1">';
					$quterly_data .= '<input type="text" class="form-control" placeholder="₹" value="' . (int)$target_q1 . '" readonly>';
					$quterly_data .= '</div>';
					$quterly_data .= '<div class="col-3 p-1">';
					$quterly_data .= '<input type="text" class="form-control" value="' . (int)$achieve_q1 . '" placeholder="₹" readonly>';
					$quterly_data .= '</div>';
					$quterly_data .= '<div class="col-2 p-1">';
					$quterly_data .= '<input type="text" class="form-control" value="' . (int)getpercentage($target_q1, $achieve_q1) . '" placeholder="%" readonly>';
					$quterly_data .= '</div>';
					$quterly_data .= '</div>';

					if((int)$target_q1 > 0){
						$year_total_target += (int)$target_q1;
						$year_total_achieved += (int)$achieve_q1;
					}

					$target_q1 = 0;
					$achieve_q1 = 0;
				}
				if ($key == 8) {
					$quterly_data .= '<div class="row mb-2 text-center">';
					$quterly_data .= '<div class="col-2 p-1 align-self-center">';
					$quterly_data .= '<label class="form-label text-dark">Q3</label>';
					$quterly_data .= '</div>';
					$quterly_data .= '<div class="col-3 p-1">';
					$quterly_data .= '<input type="text" class="form-control" placeholder="₹" value="' . (int)$target_q1 . '" readonly>';
					$quterly_data .= '</div>';
					$quterly_data .= '<div class="col-3 p-1">';
					$quterly_data .= '<input type="text" class="form-control" value="' . (int)$achieve_q1 . '" placeholder="₹" readonly>';
					$quterly_data .= '</div>';
					$quterly_data .= '<div class="col-2 p-1">';
					$quterly_data .= '<input type="text" class="form-control" value="' . (int)getpercentage($target_q1, $achieve_q1) . '" placeholder="%" readonly>';
					$quterly_data .= '</div>';
					$quterly_data .= '</div>';

					if((int)$target_q1 > 0){
						$year_total_target += (int)$target_q1;
						$year_total_achieved += (int)$achieve_q1;
					}

					$target_q1 = 0;
					$achieve_q1 = 0;
				}
				if ($key == 11) {
					$quterly_data .= '<div class="row mb-2 text-center">';
					$quterly_data .= '<div class="col-2 p-1 align-self-center">';
					$quterly_data .= '<label class="form-label text-dark">Q4</label>';
					$quterly_data .= '</div>';
					$quterly_data .= '<div class="col-3 p-1">';
					$quterly_data .= '<input type="text" class="form-control" placeholder="₹" value="' . (int)$target_q1 . '" readonly>';
					$quterly_data .= '</div>';
					$quterly_data .= '<div class="col-3 p-1">';
					$quterly_data .= '<input type="text" class="form-control" value="' . (int)$achieve_q1 . '" placeholder="₹" readonly>';
					$quterly_data .= '</div>';
					$quterly_data .= '<div class="col-2 p-1">';
					$quterly_data .= '<input type="text" class="form-control" value="' . (int)getpercentage($target_q1, $achieve_q1) . '" placeholder="%" readonly>';
					$quterly_data .= '</div>';
					$quterly_data .= '</div>';

					if((int)$target_q1 > 0){
						$year_total_target += (int)$target_q1;
						$year_total_achieved += (int)$achieve_q1;
					}

					$target_q1 = 0;
					$achieve_q1 = 0;
				}


			}

			$yearly_data .= '<div class="row mb-2 text-center">';
			$yearly_data .= '<div class="col-2 p-1 align-self-center">';
			$yearly_data .= '<label class="form-label text-dark">'.$QueryTarget->financial_year.'</label>';
			$yearly_data .= '</div>';
			$yearly_data .= '<div class="col-3 p-1">';
			$yearly_data .= '<input type="text" class="form-control" placeholder="₹" value="' . (int)$year_total_target . '" readonly>';
			$yearly_data .= '</div>';
			$yearly_data .= '<div class="col-3 p-1">';
			$yearly_data .= '<input type="text" class="form-control" value="' . (int)$year_total_achieved . '" placeholder="₹" readonly>';
			$yearly_data .= '</div>';
			$yearly_data .= '<div class="col-2 p-1">';
			$yearly_data .= '<input type="text" class="form-control" value="' . (int)getpercentage($year_total_target, $year_total_achieved) . '" placeholder="%" readonly>';
			$yearly_data .= '</div>';
			$yearly_data .= '</div>';
			$response = successRes("Target View Success");
		} else {
			$response = errorRes("current year data not add, so try with another year");
			$response['data'] = 'This User Id : ' . $salesUserIds . ' Financial year Id : ' . $financialYearId . ' Not Exist On Target';
		}


		$response['target'] = $QueryTarget;
		$response['monthly_detail'] = $monthly_data;
		$response['quterly_detail'] = $quterly_data;
		$response['yearly_detail'] = $yearly_data;
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function saveTargetFreez(Request $request)
	{

		$validator = Validator::make($request->all(), [
			'target_detail_id' => ['required'],
			'freez_amount' => ['required'],
			'type' => ['required'],
		]);
		if ($validator->fails()) {

			$response = array();
			$response['status'] = 0;
			$response['msg'] = "The request could not be understood by the server due to malformed syntax";
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();

			return response()->json($response)->header('Content-Type', 'application/json');
		} else {

			if ($request->type == 'FREEZE') {
				$isfreeze = '1';
			} elseif ($request->type == 'UNFREEZE') {
				$isfreeze = '0';
			}
			date_default_timezone_set("Asia/Kolkata"); //India Time (GMT +5:30)
			$date_save = date('y-m-d');

			$TargetDetail = Wlmst_targetdetail::find($request->target_detail_id);
			$TargetDetail->freez_amount = $request->freez_amount;
			$TargetDetail->freez_date = $date_save;
			$TargetDetail->freezby = Auth::user()->id;
			$TargetDetail->freezip = $request->ip();
			$TargetDetail->isfreez = $isfreeze;
			$TargetDetail->updateby = Auth::user()->id;
			$TargetDetail->updateip = $request->ip();
			$TargetDetail->save();

			if ($request->type == 'FREEZE') {
				$response = successRes("Target Freezed Succesfully");
			} elseif ($request->type == 'UNFREEZE') {
				$response = successRes("Target Un Freezed Succesfully");
			}
			return response()->json($response)->header('Content-Type', 'application/json');
		}
	}
}
