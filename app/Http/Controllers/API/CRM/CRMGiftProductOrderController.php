<?php

namespace App\Http\Controllers\API\CRM;

use App\Models\Architect;
use App\Models\Electrician;
use App\Models\GiftProductOrder;
use App\Models\GiftProductOrderItem;
use App\Models\Lead;
use App\Models\GiftProduct;
use App\Models\User;
use App\Models\DataMaster;
use App\Models\CRMLog;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Config;
use Mail;
use DB;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Whatsapp\WhatsappApiContoller;

class CRMGiftProductOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $tabCanAccessBy = [0, 1, 2, 6, 9, 10, 13];
            if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
                $response = errorRes("Invalid access", 401);
				return response()->json($response, $response['status_code'])->header('Content-Type', 'application/json');
            }
            return $next($request);
        });
    }


    public function ajax(Request $request)
    {
        $isSalePerson = isSalePerson();
        $isPurchasePerson = isPurchasePerson();

        if ($isSalePerson == 1) {
            $childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
        }

        $searchColumns = [
            'gift_product_orders.id',
            'gift_product_orders.total_point_value', 
            'reporting_sale.first_name', 
            'reporting_sale.last_name', 
            'users.first_name', 
            'users.last_name',
            "CONCAT(users.first_name,' ',users.last_name)",
            "CONCAT(reporting_sale.first_name,' ',reporting_sale.last_name)"
        ];


        $selectColumns = ['gift_product_orders.id', 'gift_product_orders.created_at', 'gift_product_orders.created_at', 'gift_product_orders.total_point_value', 'gift_product_orders.cash_point_value', 'gift_product_orders.status', 'gift_product_orders.cash_status', 'gift_product_orders.cashback_status', 'gift_product_orders.track_id', 'gift_product_orders.dispatch_detail', 'users.first_name', 'users.last_name', 'users.type', 'users.id as user_id', 'gift_product_orders.total_cashback', 'gift_product_orders.total_cash', 'reporting_sale.first_name as reporting_sale_first_name', 'reporting_sale.last_name as reporting_sale_last_name'];
        if ($request->type == 202) {
            $selectColumns[] = 'architect.sale_person_id';
        } elseif ($request->type == 302) {
            $selectColumns[] = 'electrician.sale_person_id';
        }

        
        $query = GiftProductOrder::query();
        $query->leftJoin('users', 'users.id', '=', 'gift_product_orders.user_id');
        $query->where('users.type', $request->type);

        if ($request->type == 202) {
            $query->leftJoin('architect', 'architect.user_id', '=', 'gift_product_orders.user_id');
            $query->leftJoin('users as reporting_sale', 'reporting_sale.id', '=', 'architect.sale_person_id');
        } elseif ($request->type == 302) {
            $query->leftJoin('electrician', 'electrician.user_id', '=', 'gift_product_orders.user_id');
            $query->leftJoin('users as reporting_sale', 'reporting_sale.id', '=', 'electrician.sale_person_id');
        }

        if ($isSalePerson == 1) {
            if ($request->type == 202) {
                // $query->leftJoin('architect', 'architect.user_id', '=', 'gift_product_orders.user_id');
                // $query->leftJoin('users as reporting_sale', 'reporting_sale.id', '=', 'architect.sale_person_id');
                $query->whereIn('architect.sale_person_id', $childSalePersonsIds);
            } elseif ($request->type == 302) {
                // $query->leftJoin('electrician', 'electrician.user_id', '=', 'gift_product_orders.user_id');
                // $query->leftJoin('users as reporting_sale', 'reporting_sale.id', '=', 'electrician.sale_person_id');
                $query->whereIn('electrician.sale_person_id', $childSalePersonsIds);
            }
        } elseif ($isPurchasePerson == 1) {
            $query->where('gift_product_orders.status', 1);
        }

        //$query->where('gift_product_orders.user_id', Auth::user()->id);
        $query->select($selectColumns);

        $query->orderBy('gift_product_orders.id', 'desc');
        $search_value = '';

        if (isset($request->q)) {
            $isFilterApply = 1;
            $search_value = $request->q;
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

        $data = json_decode(json_encode($data), true);

        $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
        $viewData = [];

        foreach ($data as $key => $value) {
            $is_verify = 0;
            if ($value['type'] == 201 || $value['type'] == 202) {
                $deal_count = Lead::query();
                $deal_count->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                $deal_count->where('leads.status', 103);
                $deal_count->whereIn('leads.status',array(0,1,3));
                $deal_count->where(function ($deal_count) use ($value) {
                    $deal_count->orwhere('leads.architect', $value['user_id']);
                    $deal_count->orwhere('lead_sources.source', $value['user_id']);
                });
                $deal_count = $deal_count->count();
                if($deal_count > 0){
                    $is_verify = 1;
                }
            }elseif ($value['type'] == 301 || $value['type'] == 302) {
                $deal_count = Lead::query();
                $deal_count->leftJoin('lead_sources', 'lead_sources.lead_id', '=', 'leads.id');
                $deal_count->where('leads.status', 103);
                $deal_count->whereIn('leads.status',array(0,1,3));
                $deal_count->where(function ($deal_count) use ($value) {
                    $deal_count->orwhere('leads.electrician', $value['user_id']);
                    $deal_count->orwhere('lead_sources.source', $value['user_id']);
                });
                $deal_count = $deal_count->count();
                if($deal_count > 0){
                    $is_verify = 1;
                }
            }
            
            $viewData[$key] = $value;
            $viewData[$key]['id'] = $value['id'];
            $viewData[$key]['assign_to'] = $value['reporting_sale_first_name'] . ' ' . $value['reporting_sale_last_name'];
            $viewData[$key]['total_point_value'] = (int)$value['total_point_value'];
            $viewData[$key]['total_amount'] = ((int) $value['total_cashback'] + (int) $value['total_cash']);
            $viewData[$key]['created_at'] = convertDateTime($value['created_at']);
            $viewData[$key]['is_verify'] = $is_verify;

            if ($value['type'] == 201 || $value['type'] == 202) {
                $viewData[$key]['name'] = $value['first_name'] . ' ' . $value['last_name'];
            } elseif ($value['type'] == 301 || $value['type'] == 302) {
                $viewData[$key]['name'] = $value['first_name'] . ' ' . $value['last_name'];
            }

            $viewData[$key]['gift'] = '';

            $GiftProductOrderItem = GiftProductOrderItem::query();
            $GiftProductOrderItem->select('gift_products.name');
            $GiftProductOrderItem->leftJoin('gift_products', 'gift_products.id', '=', 'gift_product_order_items.gift_product_id');
            $GiftProductOrderItem->where('gift_product_order_id', $value['id']);
            $GiftProductOrderItem->orderBy('gift_product_order_items.id', 'desc');
            $GiftProductOrderItem = $GiftProductOrderItem->get();

            $GiftProductOrderItem = json_decode(json_encode($GiftProductOrderItem), true);
            $gift_name = '';
            foreach ($GiftProductOrderItem as $order_items_key => $obj_order_items) {
                $gift_name .= $obj_order_items['name'] ;
            }

            if ($value['cash_point_value'] != 0 && count($GiftProductOrderItem) != 0) {
                $gift_name .= '';
            } elseif ($value['cash_point_value'] != 0) {
                $gift_name .= '-';
            }
            
            $viewData[$key]['gift'] = $gift_name;

            $viewData[$key]['total_cash'] = '';
            if ($value['total_cash'] == 0) {
                $viewData[$key]['total_cash'] = '-';
            } else {
                $viewData[$key]['total_cash'] = (int) $value['total_cash'];
            }
            
            $viewData[$key]['total_cashback'] = '';
            if ($value['total_cashback'] == 0) {
                $viewData[$key]['total_cashback'] = '-';
            } else {
                $viewData[$key]['total_cashback'] = (int) $value['total_cashback'];
            }
                
        }

        $data = json_decode(json_encode($data), true);

		$response = successRes("Order List");
		$response['data'] = $viewData;
		return response()->json($response, $response['status_code'])->header('Content-Type', 'application/json');
    }


}
