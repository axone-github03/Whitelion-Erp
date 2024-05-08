<?php

namespace App\Http\Controllers\API\Target;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\Wlmst_target;
use App\Models\Invoice;
use App\Models\Wlmst_targetdetail;
use App\Models\Wlmst_financialyear;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

// use Illuminate\Http\Request;

class TargetViewApiController extends Controller
{

	public function target_dashboard(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'type' => ['required'], // MONTHLY-QUATERLY
			'page_type' => ['required'], // 
			// 'sales_user_id' => ['required'], // Pass Sales User ID
		]);

		if ($validator->fails()) {
			$response = errorRes("Please Check Perameater And Value");
			$response['data'] = $validator->errors();
		} else {
			// if (isSalePerson() == 1) {
				$response = array();
			if (isSalePerson() == 1) {

				$view_type = $request->type;
				$page_type = $request->page_type;

				// $sales_user_id = $request->sales_user_id;
				$sales_user_id = Auth::user()->id; // Live

				date_default_timezone_set("Asia/Kolkata");
				if (date('m') > 3) {
					$financialyear = date('Y') . "-" . (date('Y') + 1);
				} else {
					$financialyear = (date('Y') - 1) . "-" . date('Y');
				}
				$response['financial_year'] = $financialyear;
				$Tv_finYear = Wlmst_financialyear::query();
				$Tv_finYear->select('*');
				$Tv_finYear->where('wlmst_financialyear.name', $financialyear);
				$Tv_finYear = $Tv_finYear->first();

				if ($Tv_finYear != 'null') {

					$financialYearId = $Tv_finYear->id;

					try {

						$childSalePersonsIds = getChildSalePersonsIds($sales_user_id);

						$allSalesUserIds = array_unique($childSalePersonsIds);
						$allSalesUserIds = array_values($allSalesUserIds);

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
						$QueryTarget->where('wlmst_target.employeee_id', $sales_user_id);
						$QueryTarget->where('wlmst_target.finyear_id', $financialYearId);
						$QueryTarget = $QueryTarget->first();

						if ($QueryTarget != 'null') {

							if ($view_type == 'MONTHLY') {
								$TargetDetail = Wlmst_targetdetail::query();
								$TargetDetail->select('*');
								$TargetDetail->where('wlmst_targetdetail.target_id', $QueryTarget->id);
								if ($page_type == 'DETAIL') {
									$TargetDetail->where('wlmst_targetdetail.month_number', date('m'));
								}
								$TargetDetail->orderBy('wlmst_targetdetail.id', 'ASC');

								$TargetDetail = $TargetDetail->get();

								$monthly_data_list = array();
								foreach ($TargetDetail as $key => $value) {

									$startDate = getDateFromFinancialYear($QueryTarget->financial_year)['start'];
									$endDate = getDateFromFinancialYear($QueryTarget->financial_year)['end'];

									$monthly_data['t_id'] = $value->target_id;
									$monthly_data['td_id'] = $value->id;
									$monthly_data['month_number'] = $value->month_number;
									$monthly_data['name'] = ucwords(strtolower($value->month_name));
									$monthly_data['target_amount'] = (int)$value->target_amount;

									// ORDERED AMOUNT CALCULATION
									DB::enableQueryLog();
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

									if ($orderAmount != null) {
										$ordered_amt = ($value->isfreez == 1) ? number_format(floatval($value->freez_amount), 2, '.', '') : number_format(floatval($orderAmount->amount), 2, '.', '');
										$ordered_per = ($value->isfreez == 1) ? getpercentage($value->target_amount, $value->freez_amount) : getpercentage($value->target_amount, $orderAmount->amount);
									} else {
										$ordered_amt = ($value->isfreez == 1) ? number_format(floatval($value->freez_amount), 2, '.', '') : 0;
										$ordered_per = ($value->isfreez == 1) ? getpercentage($value->target_amount, $value->freez_amount) : 0;
									}
									$monthly_data['ordered_amt'] = (int)$ordered_amt;
									$monthly_data['ordered_per'] = (int)floatval($ordered_per);

									// DISPATCHED AMOUNT CALCULATION
									$orderDispatchAmount = Invoice::query();
									$orderDispatchAmount->select(DB::raw('MONTH(orders.created_at) as month'));
									$orderDispatchAmount->selectRaw('SUM(invoice.total_mrp_minus_disocunt) as amount');
									$orderDispatchAmount->leftJoin('orders', 'orders.id', '=', 'invoice.order_id');
									$orderDispatchAmount->whereIn('invoice.status', array(2, 3));
									$orderDispatchAmount->where('orders.created_at', '>=', $startDate);
									$orderDispatchAmount->where('orders.created_at', '<=', $endDate);
									$orderDispatchAmount->whereMonth('orders.created_at', $value->month_number);
									$orderDispatchAmount->whereIn('orders.user_id', $allSalesUserIds);
									$orderDispatchAmount->groupby('month');
									$orderDispatchAmount = $orderDispatchAmount->first();

									if ($orderDispatchAmount != null) {
										$dispatched_amt = number_format(floatval($orderDispatchAmount->amount), 2, '.', '');
										$dispatched_per = getpercentage($value->target_amount, $orderDispatchAmount->amount);
									} else {
										$dispatched_amt = 0;
										$dispatched_per = 0;
									}
									$monthly_data['dispatched_amt'] = (int)floatval($dispatched_amt);
									$monthly_data['dispatched_per'] = (int)floatval($dispatched_per);

									array_push($monthly_data_list, $monthly_data);
								}
								$response = successRes("success");
								$response['data'] = $monthly_data_list;
							} elseif ($view_type == 'QUATERLY') {
								/*$TargetDetail = Wlmst_targetdetail::query();
							$TargetDetail->select('quater');
							// $TargetDetail->selectRaw('SUM(CASE WHEN wlmst_targetdetail.isfreez = 1 THEN wlmst_targetdetail.freez_amount ELSE wlmst_targetdetail.target_amount END) AS new_amt');
							$TargetDetail->selectRaw('SUM(wlmst_targetdetail.target_amount) as target_amount');
							$TargetDetail->where('wlmst_targetdetail.target_id', $QueryTarget->id);
							if ($page_type == 'DETAIL') {
								$TargetDetail->where('wlmst_targetdetail.quater', getQuaterFromMonth(date('m')));
							}
							$TargetDetail->groupBy('wlmst_targetdetail.quater');

							$TargetDetail = $TargetDetail->get();

							$monthly_data_list = array();
							foreach ($TargetDetail as $key => $value) {

								$startDate = getDatesFromQuarter($value->quater, $QueryTarget->financial_year)['start'];
								$endDate = getDatesFromQuarter($value->quater, $QueryTarget->financial_year)['end'];


								$monthly_data['quater_number'] = $value->quater;
								$monthly_data['name'] = 'Q' . $value->quater;
								$monthly_data['target_amount'] = floatval($value->target_amount);
								// $monthly_data['target_amount_new'] = floatval($value->new_amt);
								// $monthly_data['start'] = getDatesFromQuarter($value->quater, $QueryTarget->financial_year);
								// $monthly_data['end'] = getDatesFromQuarter($value->quater, $QueryTarget->financial_year);

								// ORDERED AMOUNT CALCULATION

								$orderAmount = Order::query();
								$orderAmount->selectRaw('SUM(orders.total_mrp_minus_disocunt) as amount');
								$orderAmount->where('orders.status', '!=', 4);
								$orderAmount->where('orders.created_at', '>=', $startDate);
								$orderAmount->where('orders.created_at', '<=', $endDate);
								$orderAmount->whereIn('orders.user_id', $allSalesUserIds);
								$orderAmount = $orderAmount->first();

								if ($orderAmount != null) {
									$ordered_amt = number_format(floatval($orderAmount->amount), 2, '.', '');
									$ordered_per = getpercentage($value->target_amount, $orderAmount->amount);
								} else {
									$ordered_amt = 0;
									$ordered_per = 0;
								}
								$monthly_data['ordered_amt'] = floatval($ordered_amt);
								$monthly_data['ordered_per'] = floatval($ordered_per);

								// DISPATCHED AMOUNT CALCULATION
								$orderDispatchAmount = Invoice::query();
								$orderDispatchAmount->selectRaw('SUM(invoice.total_mrp_minus_disocunt) as amount');
								$orderDispatchAmount->leftJoin('orders', 'orders.id', '=', 'invoice.order_id');
								$orderDispatchAmount->whereIn('invoice.status', array(2, 3));
								$orderDispatchAmount->where('orders.created_at', '>=', $startDate);
								$orderDispatchAmount->where('orders.created_at', '<=', $endDate);
								$orderDispatchAmount->whereIn('orders.user_id', $allSalesUserIds);
								$orderDispatchAmount = $orderDispatchAmount->first();

								if ($orderDispatchAmount != null) {
									$dispatched_amt = number_format(floatval($orderDispatchAmount->amount), 2, '.', '');
									$dispatched_per = getpercentage($value->target_amount, $orderDispatchAmount->amount);
								} else {
									$dispatched_amt = 0;
									$dispatched_per = 0;
								}
								$monthly_data['dispatched_amt'] = floatval($dispatched_amt);
								$monthly_data['dispatched_per'] = floatval($dispatched_per);


								array_push($monthly_data_list, $monthly_data);
							}

							$response = successRes("success");
							$response['data'] = $monthly_data_list;*/


								$TargetDetail = Wlmst_targetdetail::query();
								$TargetDetail->select('*');
								$TargetDetail->where('wlmst_targetdetail.target_id', $QueryTarget->id);
								$TargetDetail->orderBy('wlmst_targetdetail.id', 'ASC');

								$TargetDetail = $TargetDetail->get();

								$monthly_data_list = array();
								$target_amount_new = 0;
								$ordered_amt_new = 0;
								$dispatched_amt_new = 0;
								foreach ($TargetDetail as $key => $value) {

									$startDate = getDateFromFinancialYear($QueryTarget->financial_year)['start'];
									$endDate = getDateFromFinancialYear($QueryTarget->financial_year)['end'];



									// ORDERED AMOUNT CALCULATION
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

									if ($orderAmount != null) {
										$ordered_amt = ($value->isfreez == 1) ? number_format(floatval($value->freez_amount), 2, '.', '') : number_format(floatval($orderAmount->amount), 2, '.', '');
										$ordered_per = ($value->isfreez == 1) ? getpercentage($value->target_amount, $value->freez_amount) : getpercentage($value->target_amount, $orderAmount->amount);
									} else {
										$ordered_amt = ($value->isfreez == 1) ? number_format(floatval($value->freez_amount), 2, '.', '') : 0;
										$ordered_per = ($value->isfreez == 1) ? getpercentage($value->target_amount, $value->freez_amount) : 0;
									}



									// DISPATCHED AMOUNT CALCULATION
									$orderDispatchAmount = Invoice::query();
									$orderDispatchAmount->select(DB::raw('MONTH(orders.created_at) as month'));
									$orderDispatchAmount->selectRaw('SUM(invoice.total_mrp_minus_disocunt) as amount');
									$orderDispatchAmount->leftJoin('orders', 'orders.id', '=', 'invoice.order_id');
									$orderDispatchAmount->whereIn('invoice.status', array(2, 3));
									$orderDispatchAmount->where('orders.created_at', '>=', $startDate);
									$orderDispatchAmount->where('orders.created_at', '<=', $endDate);
									$orderDispatchAmount->whereMonth('orders.created_at', $value->month_number);
									$orderDispatchAmount->whereIn('orders.user_id', $allSalesUserIds);
									$orderDispatchAmount->groupby('month');
									$orderDispatchAmount = $orderDispatchAmount->first();

									if ($orderDispatchAmount != null) {
										$dispatched_amt = number_format(floatval($orderDispatchAmount->amount), 2, '.', '');
										$dispatched_per = getpercentage($value->target_amount, $orderDispatchAmount->amount);
									} else {
										$dispatched_amt = 0;
										$dispatched_per = 0;
									}


									// $target_q1 += $value->target_amount;
									// $achieve_q1 += floatval($achived_amt);
									$target_amount_new += floatval($value->target_amount);
									$ordered_amt_new += floatval($ordered_amt);
									$dispatched_amt_new += floatval($dispatched_amt);

									if ($key == 2) {
										$monthly_data['quater_number'] = 1;
										$monthly_data['name'] = 'Q1';
										$monthly_data['target_amount'] = (int)floatval(number_format(floatval($target_amount_new), 2, '.', ''));
										$monthly_data['ordered_amt'] = (int)floatval(number_format(floatval($ordered_amt_new), 2, '.', ''));
										$monthly_data['ordered_per'] = (int)floatval(number_format(getpercentage($target_amount_new, $ordered_amt_new), 2, '.', ''));
										$monthly_data['dispatched_amt'] = (int)floatval(number_format(floatval($dispatched_amt_new), 2, '.', ''));
										$monthly_data['dispatched_per'] = (int)floatval(getpercentage($target_amount_new, $dispatched_amt_new));

										$target_amount_new = 0;
										$ordered_amt_new = 0;
										$dispatched_amt_new = 0;

										if ($page_type == 'DETAIL') {
											if (getQuaterFromMonth(date('m')) == 1) {
												array_push($monthly_data_list, $monthly_data);
											}
										} else {
											array_push($monthly_data_list, $monthly_data);
										}
									}
									if ($key == 5) {
										$monthly_data['quater_number'] = 2;
										$monthly_data['name'] = 'Q2';
										$monthly_data['target_amount'] = (int)floatval(number_format(floatval($target_amount_new), 2, '.', ''));
										$monthly_data['ordered_amt'] = (int)floatval(number_format(floatval($ordered_amt_new), 2, '.', ''));
										$monthly_data['ordered_per'] = (int)floatval(number_format(getpercentage($target_amount_new, $ordered_amt_new), 2, '.', ''));
										$monthly_data['dispatched_amt'] = (int)floatval(number_format(floatval($dispatched_amt_new), 2, '.', ''));
										$monthly_data['dispatched_per'] = (int)floatval(getpercentage($target_amount_new, $dispatched_amt_new));

										$target_amount_new = 0;
										$ordered_amt_new = 0;
										$dispatched_amt_new = 0;

										if ($page_type == 'DETAIL') {
											if (getQuaterFromMonth(date('m')) == 2) {
												array_push($monthly_data_list, $monthly_data);
											}
										} else {
											array_push($monthly_data_list, $monthly_data);
										}
									}
									if ($key == 8) {
										$monthly_data['quater_number'] = 3;
										$monthly_data['name'] = 'Q3';
										$monthly_data['target_amount'] = (int)floatval(number_format(floatval($target_amount_new), 2, '.', ''));
										$monthly_data['ordered_amt'] = (int)floatval(number_format(floatval($ordered_amt_new), 2, '.', ''));
										$monthly_data['ordered_per'] = (int)floatval(number_format(getpercentage($target_amount_new, $ordered_amt_new), 2, '.', ''));
										$monthly_data['dispatched_amt'] = (int)floatval(number_format(floatval($dispatched_amt_new), 2, '.', ''));
										$monthly_data['dispatched_per'] = (int)floatval(getpercentage($target_amount_new, $dispatched_amt_new));

										$target_amount_new = 0;
										$ordered_amt_new = 0;
										$dispatched_amt_new = 0;

										if ($page_type == 'DETAIL') {
											if (getQuaterFromMonth(date('m')) == 3) {
												array_push($monthly_data_list, $monthly_data);
											}
										} else {
											array_push($monthly_data_list, $monthly_data);
										}
									}
									if ($key == 11) {
										$monthly_data['quater_number'] = 4;
										$monthly_data['name'] = 'Q4';
										$monthly_data['target_amount'] = (int)floatval(number_format(floatval($target_amount_new), 2, '.', ''));
										$monthly_data['ordered_amt'] = (int)floatval(number_format(floatval($ordered_amt_new), 2, '.', ''));
										$monthly_data['ordered_per'] = (int)floatval(number_format(getpercentage($target_amount_new, $ordered_amt_new), 2, '.', ''));
										$monthly_data['dispatched_amt'] = (int)floatval(number_format(floatval($dispatched_amt_new), 2, '.', ''));
										$monthly_data['dispatched_per'] = (int)floatval(getpercentage($target_amount_new, $dispatched_amt_new));

										$target_amount_new = 0;
										$ordered_amt_new = 0;
										$dispatched_amt_new = 0;

										if ($page_type == 'DETAIL') {
											if (getQuaterFromMonth(date('m')) == 4) {
												array_push($monthly_data_list, $monthly_data);
											}
										} else {
											array_push($monthly_data_list, $monthly_data);
										}
									}
								}
								$response = successRes("success");
								$response['data'] = $monthly_data_list;
							} else {
								$response = errorRes("Invelid Type");
							}
						} else {
							$response = errorRes("your target not declared");
						}
					} catch (QueryException $ex) {
						$response = errorRes("please contact to admin");
						$response['data'] = $ex;
					}
				} else {
					$response = errorRes("please contat to admin");
				}
			} else {
				$response = errorRes("Invelid User");
			}
		}

		return response()->json($response, $response['status_code'])->header('Content-Type', 'application/json');
	}
}
