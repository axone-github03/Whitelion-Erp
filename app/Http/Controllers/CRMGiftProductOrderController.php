<?php

namespace App\Http\Controllers;

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
use App\Http\Controllers\Whatsapp\WhatsappApiContoller;

class CRMGiftProductOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $tabCanAccessBy = [0, 1, 2, 6, 9, 10, 13];
            if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
                return redirect()->route('dashboard');
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $data = [];
        $data['title'] = 'Orders';
        $data['type'] = isset($request->type) ? $request->type : 202;
        return view('crm/gift_product_orders', compact('data'));
    }

    public function ajax(Request $request)
    {
        $isSalePerson = isSalePerson();
        $isPurchasePerson = isPurchasePerson();

        if ($isSalePerson == 1) {
            $childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
        }

        $searchColumns = ['gift_product_orders.id', 'gift_product_orders.total_point_value', 'reporting_sale.first_name', 'reporting_sale.last_name', 'users.first_name', 'users.last_name'];

        $sortingColumns = [
            0 => 'gift_product_orders.id',
            1 => 'gift_product_orders.created_at',
            2 => 'gift_product_orders.user_id',
            3 => 'gift_product_orders.total_point_value',
            5 => 'gift_product_orders.dispatch_detail',
            6 => 'gift_product_orders.total_cashback',
            7 => 'gift_product_orders.total_cash',
            8 => 'gift_product_orders.status',
        ];

        $selectColumns = ['gift_product_orders.id', 'gift_product_orders.created_at', 'gift_product_orders.created_at', 'gift_product_orders.total_point_value', 'gift_product_orders.cash_point_value', 'gift_product_orders.status', 'gift_product_orders.cash_status', 'gift_product_orders.cashback_status', 'gift_product_orders.track_id', 'gift_product_orders.dispatch_detail', 'users.first_name', 'users.last_name', 'users.type', 'users.id as user_id', 'gift_product_orders.total_cashback', 'gift_product_orders.total_cash', 'reporting_sale.first_name as reporting_sale_first_name', 'reporting_sale.last_name as reporting_sale_last_name'];
        if ($request->type == 202) {
            $selectColumns[] = 'architect.sale_person_id';
        } elseif ($request->type == 302) {
            $selectColumns[] = 'electrician.sale_person_id';
        }

        $query = GiftProductOrder::query();
        $query->leftJoin('users', 'users.id', '=', 'gift_product_orders.user_id');

        if ($isSalePerson == 1) {
            if ($request->type == 202) {
                $query->leftJoin('architect', 'architect.user_id', '=', 'gift_product_orders.user_id');
                //$query->leftJoin('users as reporting_sale', 'reporting_sale.id', '=', 'architect.sale_person_id');
                $query->whereIn('architect.sale_person_id', $childSalePersonsIds);
            } elseif ($request->type == 302) {
                $query->leftJoin('electrician', 'electrician.user_id', '=', 'gift_product_orders.user_id');
                //$query->leftJoin('users as reporting_sale', 'reporting_sale.id', '=', 'electrician.sale_person_id');
                $query->whereIn('electrician.sale_person_id', $childSalePersonsIds);
            }
        } elseif ($isPurchasePerson == 1) {
            $query->where('gift_product_orders.status', 1);
        }

        $query->where('users.type', $request->type);
        //$query->where('gift_product_orders.user_id', Auth::user()->id);
        $recordsTotal = $query->count();
        $recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.
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
        $query->limit($request->length);
        $query->offset($request->start);
        $query->orderBy($sortingColumns[$request['order'][0]['column']], $request['order'][0]['dir']);

        $isFilterApply = 0;
        $search_value = '';

        if (isset($request['search']['value'])) {
            $isFilterApply = 1;
            $search_value = $request['search']['value'];
            $query->where(function ($query) use ($search_value, $searchColumns) {
                for ($i = 0; $i < count($searchColumns); $i++) {
                    if ($i == 0) {
                        $query->where($searchColumns[$i], 'like', '%' . $search_value . '%');
                    } else {
                        $query->orWhere($searchColumns[$i], 'like', '%' . $search_value . '%');
                    }
                }
            });
        }

        $data = $query->get();

        $data = json_decode(json_encode($data), true);
        if ($isFilterApply == 1) {
            $recordsFiltered = count($data);
        }

        // echo '<pre>';
        // print_r($data);
        // die;
        $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
        $viewData = [];

        foreach ($data as $key => $value) {
            $is_verify = '<i class="bx bxs-phone bx-tada text-danger font-size-16 me-1" style="text-decoration: underline;"></i>';
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
                    $is_verify = '<i class="bx bxs-badge-check bx-tada text-success font-size-16 me-1" ></i>';
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
                    $is_verify = '<i class="bx bxs-badge-check bx-tada text-success font-size-16 me-1" ></i>';
                }
            }
            
            if (isTaleSalesUser() == 1) {
                $orderID = $data[$key]['id'];
                $viewData[$key] = [];
                $viewData[$key]['id'] = "<a href='javascript:void(0)' onclick='getGiftOrderLog(" . $value['id'] . ")' > #" . highlightString($value['id'],$search_value) . '</a>';
                $viewData[$key]['created_at'] = highlightString(convertDateTime($value['created_at']),$search_value);

                if ($value['type'] == 201 || $value['type'] == 202) {
                    $routeArchitects = route('new.architects.index') . '?id=' . $value['user_id'];
                    $viewData[$key]['name'] = "<div class='d-flex align-items-center'>".$is_verify."<a target='_blank' href='" . $routeArchitects . "' >" . highlightString($value['first_name'] . ' ' . $value['last_name'],$search_value) . '</a></div>';
                } elseif ($value['type'] == 301 || $value['type'] == 302) {
                    $routeElectrician = route('new.electricians.index') . '?id=' . $value['user_id'];
                    $viewData[$key]['name'] = "<div class='d-flex align-items-center'>".$is_verify."<a target='_blank' href='" . $routeElectrician . "' >" . highlightString($value['first_name'] . ' ' . $value['last_name'],$search_value) . '</a></div>';
                }
                $viewData[$key]['assign_to'] = highlightString($value['reporting_sale_first_name'] . ' ' . $value['reporting_sale_last_name'],$search_value);
                $viewData[$key]['total_point_value'] = '' . highlightString((int) $value['total_point_value'],$search_value);
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
                    $gift_name .= $obj_order_items['name'] . ' </br>';
                }

                if ($value['cash_point_value'] != 0 && count($GiftProductOrderItem) != 0) {
                    $gift_name .= '';
                } elseif ($value['cash_point_value'] != 0) {
                    $gift_name .= '-';
                }
				if ($value['status'] == 1) {
                    if ($gift_name == '-') {
                        $viewData[$key]['gift'] = '<a href="javascript:void(0)" class="edit-name">' . $gift_name . '</a>';
                    } else {
                        $viewData[$key]['gift'] = '<a href="javascript:void(0)" class="badge badge-pill badge-soft-success edit-name"  data-bs-toggle="tooltip" title="" data-bs-original-title="Approved">' . $gift_name . '</a>';
                    }
                } elseif ($value['status'] == 2) {
                    if ($gift_name == '-') {
                        $viewData[$key]['gift'] = '<a href="javascript:void(0)" class="edit-name">' . $gift_name . '</a>';
                    } else {
                        $viewData[$key]['gift'] = '<a href="javascript:void(0)" class="badge badge-pill badge-soft-warning edit-name"  data-bs-toggle="tooltip" title="" data-bs-original-title="Dispatched">' . $gift_name . '</a>';
                    }
                } elseif ($value['status'] == 3) {
                    if ($gift_name == '-') {
                        $viewData[$key]['gift'] = '<a href="javascript:void(0)" class="edit-name">' . $gift_name . '</a>';
                    } else {
                        $viewData[$key]['gift'] = '<a href="javascript:void(0)" class="badge badge-pill badge-soft-danger edit-name"  data-bs-toggle="tooltip" title="" data-bs-original-title="Rejected">' . $gift_name . '</a>';
                    }
                } elseif ($value['status'] == 4) {
                    if ($gift_name == '-') {
                        $viewData[$key]['gift'] = '<a href="javascript:void(0)" class="edit-name">' . $gift_name . '</a>';
                    } else {
                        $viewData[$key]['gift'] = '<a href="javascript:void(0)" class="badge badge-pill badge-soft-info edit-name"   data-bs-toggle="tooltip" title="" data-bs-original-title="Delivered">' . $gift_name . '</a>';
                    }
                } elseif ($value['status'] == 5) {
                    if ($gift_name == '-') {
                        $viewData[$key]['gift'] = '<a href="javascript:void(0)" class="edit-name">' . $gift_name . '</a>';
                    } else {
                        $viewData[$key]['gift'] = '<a href="javascript:void(0)" class="badge badge-pill br-color edit-name"  data-bs-toggle="tooltip" title="" data-bs-original-title="Recieved">' . $gift_name . '</a>';
                    }
                } else {
                    $viewData[$key]['gift'] = '<a href="javascript:void(0)" class="edit-name">' . $gift_name . '</a>';
                }

                $viewData[$key]['total_cash'] = '';
                $viewData[$key]['total_cashback'] = '';
                // if ($value['cash_status'] == 1 && $isSalePerson == 0) {
                if ($value['cash_status'] == 0) {
                    if ($value['total_cash'] == 0) {
                        $viewData[$key]['total_cash'] = '<a href="javascript:void(0)" class="edit-name">-</a>';
                    } else {
                        $viewData[$key]['total_cash'] = '<a href="javascript:void(0)" class="badge badge-pill y-color edit-name" data-bs-toggle="tooltip" title="" data-bs-original-title="New Request">' . (int) $value['total_cash'] . '</a>';
                    }
                } elseif ($value['cash_status'] == 0) {
                    if ($value['total_cash'] == 0) {
                        $viewData[$key]['total_cash'] = '<a href="javascript:void(0)" class="edit-name">-</a>';
                    } else {
                        $viewData[$key]['total_cash'] = '<a href="javascript:void(0)" class="badge badge-pill y-color edit-name" data-bs-toggle="tooltip" title="" data-bs-original-title="New Request">' . (int) $value['total_cash'] . '</a>';
                    }
                } elseif ($value['cash_status'] == 1) {
                    if ($value['total_cash'] == 0) {
                        $viewData[$key]['total_cash'] = '<a href="javascript:void(0)" class="edit-name">-</a>';
                    } else {
                        $viewData[$key]['total_cash'] = '<a href="javascript:void(0)" class="badge badge-pill badge-soft-success edit-name" data-bs-toggle="tooltip" title="" data-bs-original-title="Approved">' . (int) $value['total_cash'] . '</a>';
                    }
                } elseif ($value['cash_status'] == 2) {
                    if ($value['total_cash'] == 0) {
                        $viewData[$key]['total_cash'] = '<a href="javascript:void(0)" class="edit-name">-</a>';
                    } else {
                        $viewData[$key]['total_cash'] = '<a href="javascript:void(0)" class="badge badge-pill badge-soft-warning edit-name" data-bs-toggle="tooltip" title="" data-bs-original-title="Dispatched">' . (int) $value['total_cash'] . '</a>';
                    }
                } elseif ($value['cash_status'] == 3) {
                    if ($value['total_cash'] == 0) {
                        $viewData[$key]['total_cash'] = '<a href="javascript:void(0)" class="edit-name">-</a>';
                    } else {
                        $viewData[$key]['total_cash'] = '<a href="javascript:void(0)" class="badge badge-pill badge-soft-danger edit-name"  data-bs-toggle="tooltip" title="" data-bs-original-title="Rejected">' . (int) $value['total_cash'] . '</a>';
                    }
                } elseif ($value['cash_status'] == 4) {
                    if ($value['total_cash'] == 0) {
                        $viewData[$key]['total_cash'] = '<a href="javascript:void(0)" class="edit-name">-</a>';
                    } else {
                        $viewData[$key]['total_cash'] = '<a href="javascript:void(0)" class="badge badge-pill badge-soft-info edit-name" data-bs-toggle="tooltip" title="" data-bs-original-title="Delivered">' . (int) $value['total_cash'] . '</a>';
                    }
                } elseif ($value['cash_status'] == 5) {
                    if ($value['total_cash'] == 0) {
                        $viewData[$key]['total_cash'] = '<a href="javascript:void(0)" class="edit-name">-</a>';
                    } else {
                        $viewData[$key]['total_cash'] = '<a href="javascript:void(0)" class="badge badge-pill br-color edit-name"  data-bs-toggle="tooltip" title="" data-bs-original-title="Recieved">' . (int) $value['total_cash'] . '</a>';
                    }
                }


                if ($value['cashback_status'] == 0) {
                    if ($value['total_cashback'] == 0) {
                        $viewData[$key]['total_cashback'] = '<a href="javascript:void(0)" class="edit-name">-</a>';
                    } else {
                        $viewData[$key]['total_cashback'] = '<a href="javascript:void(0)" class="badge badge-pill y-color edit-name" data-bs-toggle="tooltip" title="" data-bs-original-title="New Request">' . (int) $value['total_cashback'] . '</a>';
                    }
                } elseif ($value['cashback_status'] == 0) {
                    if ($value['total_cashback'] == 0) {
                        $viewData[$key]['total_cashback'] = '<a href="javascript:void(0)" class="edit-name">-</a>';
                    } else {
                        $viewData[$key]['total_cashback'] = '<a href="javascript:void(0)" class="badge badge-pill y-color edit-name" data-bs-toggle="tooltip" title="" data-bs-original-title="New Request">' . (int) $value['total_cashback'] . '</a>';
                    }
                } elseif ($value['cashback_status'] == 1) {
                    if ($value['total_cashback'] == 0) {
                        $viewData[$key]['total_cashback'] = '<a href="javascript:void(0)" class="edit-name">-</a>';
                    } else {
                        $viewData[$key]['total_cashback'] = '<a href="javascript:void(0)" class="badge badge-pill badge-soft-success edit-name"data-bs-toggle="tooltip" title="" data-bs-original-title="Approved">' . (int) $value['total_cashback'] . '</a>';
                    }
                } elseif ($value['cashback_status'] == 2) {
                    if ($value['total_cashback'] == 0) {
                        $viewData[$key]['total_cashback'] = '<a href="javascript:void(0)" class="edit-name">-</a>';
                    } else {
                        $viewData[$key]['total_cashback'] = '<a href="javascript:void(0)" class="badge badge-pill badge-soft-warning edit-name" data-bs-toggle="tooltip" title="" data-bs-original-title="Dispatched">' . (int) $value['total_cashback'] . '</a>';
                    }
                } elseif ($value['cashback_status'] == 3) {
                    if ($value['total_cashback'] == 0) {
                        $viewData[$key]['total_cashback'] = '<a href="javascript:void(0)" class="edit-name">-</a>';
                    } else {
                        $viewData[$key]['total_cashback'] = '<a href="javascript:void(0)" class="badge badge-pill badge-soft-danger edit-name"  data-bs-toggle="tooltip" title="" data-bs-original-title="Rejected">' . (int) $value['total_cashback'] . '</a>';
                    }
                } elseif ($value['cashback_status'] == 4) {
                    if ($value['total_cashback'] == 0) {
                        $viewData[$key]['total_cashback'] = '<a href="javascript:void(0)" class="edit-name">-</a>';
                    } else {
                        $viewData[$key]['total_cashback'] = '<a href="javascript:void(0)" class="badge badge-pill badge-soft-info edit-name" data-bs-toggle="tooltip" title="" data-bs-original-title="Delivered">' . (int) $value['total_cashback'] . '</a>';
                    }
                } elseif ($value['cashback_status'] == 5) {
                    if ($value['total_cashback'] == 0) {
                        $viewData[$key]['total_cashback'] = '<a href="javascript:void(0)" class="edit-name">-</a>';
                    } else {
                        $viewData[$key]['total_cashback'] = '<a href="javascript:void(0)" class="badge badge-pill br-color edit-name"  data-bs-toggle="tooltip" title="" data-bs-original-title="Recieved">' . (int) $value['total_cashback'] . '</a>';
                    }
                }

                $viewData[$key]['total_amount'] = (int) $value['total_cashback'] + (int) $value['total_cash'];
                $uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';
                $uiAction .= '<li class="list-inline-item px-2">';
                $uiAction .= '<a onclick="ViewOrder(\'' . $orderID . '\')" href="javascript: void(0);" title="View"><i class="mdi mdi-eye"></i></a>';
                $uiAction .= '</li>';
                $uiAction .= '</ul>';
                $viewData[$key]['action'] = $uiAction;
            } else {
                $orderID = $data[$key]['id'];
                $viewData[$key] = [];
                $viewData[$key]['id'] = "<a href='javascript:void(0)' onclick='getGiftOrderLog(" . $value['id'] . ")' > #" . highlightString($value['id'],$search_value) . '</a>';
                $viewData[$key]['assign_to'] = highlightString($value['reporting_sale_first_name'] . ' ' . $value['reporting_sale_last_name'],$search_value);
                $viewData[$key]['total_point_value'] = '' . highlightString((int) $value['total_point_value'],$search_value);
                $viewData[$key]['total_amount'] = highlightString((int) $value['total_cashback'] + (int) $value['total_cash'],$search_value);
                $viewData[$key]['created_at'] = highlightString(convertDateTime($value['created_at']),$search_value);

                if ($value['type'] == 201 || $value['type'] == 202) {
                    $routeArchitects = route('new.architects.index') . '?id=' . $value['user_id'];
                    $viewData[$key]['name'] = "<div class='d-flex align-items-center'>".$is_verify."<a target='_blank' href='" . $routeArchitects . "' >" . highlightString($value['first_name'] . ' ' . $value['last_name'],$search_value) . '</a></div>';
                } elseif ($value['type'] == 301 || $value['type'] == 302) {
                    $routeElectrician = route('new.electricians.index') . '?id=' . $value['user_id'];
                    $viewData[$key]['name'] = "<div class='d-flex align-items-center'>".$is_verify."<a target='_blank' href='" . $routeElectrician . "' >" . highlightString($value['first_name'] . ' ' . $value['last_name'],$search_value) . '</a></div>';
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
                    $gift_name .= $obj_order_items['name'] . ' </br>';
                }

                if ($value['cash_point_value'] != 0 && count($GiftProductOrderItem) != 0) {
                    $gift_name .= '';
                } elseif ($value['cash_point_value'] != 0) {
                    $gift_name .= '-';
                }

                if ($value['status'] == 1 && $isSalePerson == 0) {
                    if ($gift_name == '-') {
                        $viewData[$key]['gift'] = '<a href="javascript:void(0)" class="edit-name">' . $gift_name . '</a>';
                    } else {
                        $viewData[$key]['gift'] = '<a href="javascript:void(0)" class="badge badge-pill badge-soft-success edit-name" onclick="doMarkAsDispatch(\'' . $value['id'] . '\', \'gift\')" data-bs-toggle="tooltip" title="" data-bs-original-title="Approved">' . $gift_name . '</a>';
                    }
                } elseif ($value['status'] == 2) {
                    if ($gift_name == '-') {
                        $viewData[$key]['gift'] = '<a href="javascript:void(0)" class="edit-name">' . $gift_name . '</a>';
                    } else {
                        $viewData[$key]['gift'] = '<a href="javascript:void(0)" class="badge badge-pill badge-soft-warning edit-name" onclick="doMarkAsDeliever(\'' . $value['id'] . '\', \'gift\')" data-bs-toggle="tooltip" title="" data-bs-original-title="Dispatched">' . $gift_name . '</a>';
                    }
                } elseif ($value['status'] == 3) {
                    if ($gift_name == '-') {
                        $viewData[$key]['gift'] = '<a href="javascript:void(0)" class="edit-name">' . $gift_name . '</a>';
                    } else {
                        $viewData[$key]['gift'] = '<a href="javascript:void(0)" class="badge badge-pill badge-soft-danger edit-name"  data-bs-toggle="tooltip" title="" data-bs-original-title="Rejected">' . $gift_name . '</a>';
                    }
                } elseif ($value['status'] == 4) {
                    if ($gift_name == '-') {
                        $viewData[$key]['gift'] = '<a href="javascript:void(0)" class="edit-name">' . $gift_name . '</a>';
                    } else {
                        $viewData[$key]['gift'] = '<a href="javascript:void(0)" class="badge badge-pill badge-soft-info edit-name" onclick="doMarkAsRecieve(\'' . $value['id'] . '\', \'gift\')"  data-bs-toggle="tooltip" title="" data-bs-original-title="Delivered">' . $gift_name . '</a>';
                    }
                } elseif ($value['status'] == 5) {
                    if ($gift_name == '-') {
                        $viewData[$key]['gift'] = '<a href="javascript:void(0)" class="edit-name">' . $gift_name . '</a>';
                    } else {
                        $viewData[$key]['gift'] = '<a href="javascript:void(0)" class="badge badge-pill br-color edit-name"  data-bs-toggle="tooltip" title="" data-bs-original-title="Recieved">' . $gift_name . '</a>';
                    }
                } else {
                    $viewData[$key]['gift'] = '<a href="javascript:void(0)" class="edit-name">' . $gift_name . '</a>';
                }

                if ($value['status'] == 0 && $isAdminOrCompanyAdmin == 1) {
                    if ($gift_name == '-') {
                        $viewData[$key]['gift'] = '<a href="javascript:void(0)" class="edit-name">' . $gift_name . '</a>';
                    } else {
                        $viewData[$key]['gift'] = '<a href="javascript:void(0)" class="badge badge-pill y-color edit-name" onclick="showAcceptAndRejectModal(\'' . $value['id'] . '\', \'gift\')"  data-bs-toggle="tooltip" title="" data-bs-original-title="New Request">' . $gift_name . '</a>';
                    }
                }

                $viewData[$key]['total_cash'] = '';
                $viewData[$key]['total_cashback'] = '';
                // if ($value['cash_status'] == 1 && $isSalePerson == 0) {
                if ($value['cash_status'] == 0 && $isAdminOrCompanyAdmin == 1) {
                    if ($value['total_cash'] == 0) {
                        $viewData[$key]['total_cash'] = '<a href="javascript:void(0)" class="edit-name">-</a>';
                    } else {
                        $viewData[$key]['total_cash'] = '<a href="javascript:void(0)" class="badge badge-pill y-color edit-name" onclick="showAcceptAndRejectModal(\'' . $value['id'] . '\', \'cash\')"  data-bs-toggle="tooltip" title="" data-bs-original-title="New Request">' . (int) $value['total_cash'] . '</a>';
                    }
                } elseif ($value['cash_status'] == 0 && $isAdminOrCompanyAdmin == 0) {
                    if ($value['total_cash'] == 0) {
                        $viewData[$key]['total_cash'] = '<a href="javascript:void(0)" class="edit-name">-</a>';
                    } else {
                        $viewData[$key]['total_cash'] = '<a href="javascript:void(0)" class="badge badge-pill y-color edit-name" data-bs-toggle="tooltip" title="" data-bs-original-title="New Request">' . (int) $value['total_cash'] . '</a>';
                    }
                } elseif ($value['cash_status'] == 1 && $isSalePerson == 0) {
                    if ($value['total_cash'] == 0) {
                        $viewData[$key]['total_cash'] = '<a href="javascript:void(0)" class="edit-name">-</a>';
                    } else {
                        $viewData[$key]['total_cash'] = '<a href="javascript:void(0)" class="badge badge-pill badge-soft-success edit-name" onclick="doMarkAsDispatch(\'' . $value['id'] . '\', \'cash\')" data-bs-toggle="tooltip" title="" data-bs-original-title="Approved">' . (int) $value['total_cash'] . '</a>';
                    }
                } elseif ($value['cash_status'] == 2) {
                    if ($value['total_cash'] == 0) {
                        $viewData[$key]['total_cash'] = '<a href="javascript:void(0)" class="edit-name">-</a>';
                    } else {
                        $viewData[$key]['total_cash'] = '<a href="javascript:void(0)" class="badge badge-pill badge-soft-warning edit-name" onclick="doMarkAsDeliever(\'' . $value['id'] . '\', \'cash\')"  data-bs-toggle="tooltip" title="" data-bs-original-title="Dispatched">' . (int) $value['total_cash'] . '</a>';
                    }
                } elseif ($value['cash_status'] == 3) {
                    if ($value['total_cash'] == 0) {
                        $viewData[$key]['total_cash'] = '<a href="javascript:void(0)" class="edit-name">-</a>';
                    } else {
                        $viewData[$key]['total_cash'] = '<a href="javascript:void(0)" class="badge badge-pill badge-soft-danger edit-name"  data-bs-toggle="tooltip" title="" data-bs-original-title="Rejected">' . (int) $value['total_cash'] . '</a>';
                    }
                } elseif ($value['cash_status'] == 4) {
                    if ($value['total_cash'] == 0) {
                        $viewData[$key]['total_cash'] = '<a href="javascript:void(0)" class="edit-name">-</a>';
                    } else {
                        $viewData[$key]['total_cash'] = '<a href="javascript:void(0)" class="badge badge-pill badge-soft-info edit-name" onclick="doMarkAsRecieve(\'' . $value['id'] . '\', \'cash\')"  data-bs-toggle="tooltip" title="" data-bs-original-title="Delivered">' . (int) $value['total_cash'] . '</a>';
                    }
                } elseif ($value['cash_status'] == 5) {
                    if ($value['total_cash'] == 0) {
                        $viewData[$key]['total_cash'] = '<a href="javascript:void(0)" class="edit-name">-</a>';
                    } else {
                        $viewData[$key]['total_cash'] = '<a href="javascript:void(0)" class="badge badge-pill br-color edit-name"  data-bs-toggle="tooltip" title="" data-bs-original-title="Recieved">' . (int) $value['total_cash'] . '</a>';
                    }
                }

                // if ($value['cash_status'] == 0 && $isAdminOrCompanyAdmin == 1) {
                // 	if ($value['total_cash'] == 0) {
                // 		$viewData[$key]['total_cash'] = '<a href="javascript:void(0)" class="badge badge-pill br-color edit-name" data-bs-toggle="tooltip" title="" data-bs-original-title="Recieved">' . (int) $value['total_cash'] . '</a>';
                // 	} else {
                // 		$viewData[$key]['total_cash'] = '<a href="javascript:void(0)" class="badge badge-pill y-color edit-name" onclick="showAcceptAndRejectModal(\'' . $value['id'] . '\', \'cash\')"  data-bs-toggle="tooltip" title="" data-bs-original-title="New Request">' . (int) $value['total_cash'] . '</a>';
                // 	}
                // } else {
                // 	$viewData[$key]['total_cash'] = '<a href="javascript:void(0)" class="badge badge-pill y-color edit-name"  data-bs-toggle="tooltip" title="" data-bs-original-title="New Request">' . (int) $value['total_cash'] . '</a>';
                // }

                if ($value['cashback_status'] == 0 && $isAdminOrCompanyAdmin == 1) {
                    if ($value['total_cashback'] == 0) {
                        $viewData[$key]['total_cashback'] = '<a href="javascript:void(0)" class="edit-name">-</a>';
                    } else {
                        $viewData[$key]['total_cashback'] = '<a href="javascript:void(0)" class="badge badge-pill y-color edit-name" onclick="showAcceptAndRejectModal(\'' . $value['id'] . '\', \'cashback\')"  data-bs-toggle="tooltip" title="" data-bs-original-title="New Request">' . (int) $value['total_cashback'] . '</a>';
                    }
                } elseif ($value['cashback_status'] == 0 && $isAdminOrCompanyAdmin == 0) {
                    if ($value['total_cashback'] == 0) {
                        $viewData[$key]['total_cashback'] = '<a href="javascript:void(0)" class="edit-name">-</a>';
                    } else {
                        $viewData[$key]['total_cashback'] = '<a href="javascript:void(0)" class="badge badge-pill y-color edit-name" data-bs-toggle="tooltip" title="" data-bs-original-title="New Request">' . (int) $value['total_cashback'] . '</a>';
                    }
                } elseif ($value['cashback_status'] == 1 && $isSalePerson == 0) {
                    if ($value['total_cashback'] == 0) {
                        $viewData[$key]['total_cashback'] = '<a href="javascript:void(0)" class="edit-name">-</a>';
                    } else {
                        $viewData[$key]['total_cashback'] = '<a href="javascript:void(0)" class="badge badge-pill badge-soft-success edit-name" onclick="doMarkAsDispatch(\'' . $value['id'] . '\', \'cashback\')" data-bs-toggle="tooltip" title="" data-bs-original-title="Approved">' . (int) $value['total_cashback'] . '</a>';
                    }
                } elseif ($value['cashback_status'] == 2) {
                    if ($value['total_cashback'] == 0) {
                        $viewData[$key]['total_cashback'] = '<a href="javascript:void(0)" class="edit-name">-</a>';
                    } else {
                        $viewData[$key]['total_cashback'] = '<a href="javascript:void(0)" class="badge badge-pill badge-soft-warning edit-name" onclick="doMarkAsDeliever(\'' . $value['id'] . '\', \'cashback\')"  data-bs-toggle="tooltip" title="" data-bs-original-title="Dispatched">' . (int) $value['total_cashback'] . '</a>';
                    }
                } elseif ($value['cashback_status'] == 3) {
                    if ($value['total_cashback'] == 0) {
                        $viewData[$key]['total_cashback'] = '<a href="javascript:void(0)" class="edit-name">-</a>';
                    } else {
                        $viewData[$key]['total_cashback'] = '<a href="javascript:void(0)" class="badge badge-pill badge-soft-danger edit-name"  data-bs-toggle="tooltip" title="" data-bs-original-title="Rejected">' . (int) $value['total_cashback'] . '</a>';
                    }
                } elseif ($value['cashback_status'] == 4) {
                    if ($value['total_cashback'] == 0) {
                        $viewData[$key]['total_cashback'] = '<a href="javascript:void(0)" class="edit-name">-</a>';
                    } else {
                        $viewData[$key]['total_cashback'] = '<a href="javascript:void(0)" class="badge badge-pill badge-soft-info edit-name" onclick="doMarkAsRecieve(\'' . $value['id'] . '\', \'cashback\')"  data-bs-toggle="tooltip" title="" data-bs-original-title="Delivered">' . (int) $value['total_cashback'] . '</a>';
                    }
                } elseif ($value['cashback_status'] == 5) {
                    if ($value['total_cashback'] == 0) {
                        $viewData[$key]['total_cashback'] = '<a href="javascript:void(0)" class="edit-name">-</a>';
                    } else {
                        $viewData[$key]['total_cashback'] = '<a href="javascript:void(0)" class="badge badge-pill br-color edit-name"  data-bs-toggle="tooltip" title="" data-bs-original-title="Recieved">' . (int) $value['total_cashback'] . '</a>';
                    }
                }

                // if ($value['cashback_status'] == 0 && $isAdminOrCompanyAdmin == 1) {
                // 	if ($value['total_cashback'] == 0) {
                // 		$viewData[$key]['total_cashback'] = '<a href="javascript:void(0)" class="badge badge-pill br-color edit-name" data-bs-toggle="tooltip" title="" data-bs-original-title="Recieved">' . (int) $value['total_cashback'] . '</a>';
                // 	} else {
                // 		$viewData[$key]['total_cashback'] = '<a href="javascript:void(0)" class="badge badge-pill y-color edit-name" onclick="showAcceptAndRejectModal(\'' . $value['id'] . '\', \'cashback\')"  data-bs-toggle="tooltip" title="" data-bs-original-title="New Request">' . (int) $value['total_cashback'] . '</a>';
                // 	}
                // } else {
                // 	$viewData[$key]['total_cashback'] = '<a href="javascript:void(0)" class="badge badge-pill y-color edit-name" data-bs-toggle="tooltip" title="" data-bs-original-title="New Request">' . (int) $value['total_cashback'] . '</a>';
                // }

                // $viewData[$key]['action_mark_dispatch'] = $action_mark_dispatch;
                $uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';
                $uiAction .= '<li class="list-inline-item px-2">';
                $uiAction .= '<a onclick="ViewOrder(\'' . $orderID . '\')" href="javascript: void(0);" title="View"><i class="mdi mdi-eye"></i></a>';
                $uiAction .= '</li>';
                $uiAction .= '</ul>';
                $viewData[$key]['action'] = $uiAction;
            }
        }

        $query = GiftProductOrder::query();
        $query->leftJoin('users', 'users.id', '=', 'gift_product_orders.user_id');

        if ($isSalePerson == 1) {
            if ($request->type == 202) {
                $query->leftJoin('architect', 'architect.user_id', '=', 'gift_product_orders.user_id');
                //$query->leftJoin('users as reporting_sale', 'reporting_sale.id', '=', 'architect.sale_person_id');
                $query->whereIn('architect.sale_person_id', $childSalePersonsIds);
            } elseif ($request->type == 302) {
                $query->leftJoin('electrician', 'electrician.user_id', '=', 'gift_product_orders.user_id');
                //$query->leftJoin('users as reporting_sale', 'reporting_sale.id', '=', 'electrician.sale_person_id');
                $query->whereIn('electrician.sale_person_id', $childSalePersonsIds);
            }
        } elseif ($isPurchasePerson == 1) {
            $query->where('gift_product_orders.status', 1);
        }

        $query->where('users.type', $request->type);
        $query->where('gift_product_orders.status', '!=', 3);
        //$query->where('gift_product_orders.user_id', Auth::user()->id);
        $totalCash = $query->sum('total_cash');

        $query = GiftProductOrder::query();
        $query->leftJoin('users', 'users.id', '=', 'gift_product_orders.user_id');

        if ($isSalePerson == 1) {
            if ($request->type == 202) {
                $query->leftJoin('architect', 'architect.user_id', '=', 'gift_product_orders.user_id');
                //$query->leftJoin('users as reporting_sale', 'reporting_sale.id', '=', 'architect.sale_person_id');
                $query->whereIn('architect.sale_person_id', $childSalePersonsIds);
            } elseif ($request->type == 302) {
                $query->leftJoin('electrician', 'electrician.user_id', '=', 'gift_product_orders.user_id');
                //$query->leftJoin('users as reporting_sale', 'reporting_sale.id', '=', 'electrician.sale_person_id');
                $query->whereIn('electrician.sale_person_id', $childSalePersonsIds);
            }
        } elseif ($isPurchasePerson == 1) {
            $query->where('gift_product_orders.status', 1);
        }

        $query->where('users.type', $request->type);
        $query->where('gift_product_orders.status', '!=', 3);
        //$query->where('gift_product_orders.user_id', Auth::user()->id);
        $totalCashback = $query->sum('total_cashback');

        $total = $totalCashback + $totalCash;

        $query = GiftProductOrder::query();
        $query->leftJoin('users', 'users.id', '=', 'gift_product_orders.user_id');

        if ($isSalePerson == 1) {
            if ($request->type == 202) {
                $query->leftJoin('architect', 'architect.user_id', '=', 'gift_product_orders.user_id');
                //$query->leftJoin('users as reporting_sale', 'reporting_sale.id', '=', 'architect.sale_person_id');
                $query->whereIn('architect.sale_person_id', $childSalePersonsIds);
            } elseif ($request->type == 302) {
                $query->leftJoin('electrician', 'electrician.user_id', '=', 'gift_product_orders.user_id');
                //$query->leftJoin('users as reporting_sale', 'reporting_sale.id', '=', 'electrician.sale_person_id');
                $query->whereIn('electrician.sale_person_id', $childSalePersonsIds);
            }
        } elseif ($isPurchasePerson == 1) {
            $query->where('gift_product_orders.status', 1);
        }
        $query->where('users.type', $request->type);
        $query->where('gift_product_orders.status', '!=', 3);
        //$query->where('gift_product_orders.user_id', Auth::user()->id);
        $totalPrice = $query->sum('total_item_value');

        $jsonData = [
            'draw' => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            'recordsTotal' => intval($recordsTotal), // total number of records
            'recordsFiltered' => intval($recordsFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
            'data' => $viewData, // total data array
            'overview' => ['total' => $total, 'total_cash' => $totalCash, 'total_cashback' => $totalCashback, 'total_price' => $totalPrice], // total data array
        ];
        return $jsonData;
    }

    function logAjax(Request $request)
    {
        $isSalePerson = isSalePerson();
        // if ($isSalePerson == 1) {
        // 	$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
        // }

        $searchColumns = ['crm_log.id', 'crm_log.name', 'crm_log.description', 'crm_log.created_at'];

        $sortingColumns = [
            0 => 'crm_log.name',
            1 => 'crm_log.description',
            2 => 'crm_log.created_at',
        ];

        $selectColumns = ['crm_log.id', 'crm_log.name', 'crm_log.description', 'crm_log.created_at'];

        $recordsTotal = 0;

        if ($request->order_id != 0) {
            $query = CRMLog::query();
            $query->where('order_id', $request->order_id);
            $recordsTotal = $query->count();
        }

        $recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.
        $data = [];
        $isFilterApply = 0;
        if ($request->order_id != 0) {
            $query = CRMLog::query();
            //$query->where('order_id', $request->order_id);
            $query->where('order_id', $request->order_id);
            //$query->where('gift_product_orders.user_id', Auth::user()->id);
            $query->select($selectColumns);
            $query->limit($request->length);
            $query->offset($request->start);
            // $query->orderBy($sortingColumns[$request['order'][0]['column']], $request['order'][0]['dir']);
            if (isset($request['search']['value'])) {
                $isFilterApply = 1;
                $search_value = $request['search']['value'];
                $query->where(function ($query) use ($search_value, $searchColumns) {
                    for ($i = 0; $i < count($searchColumns); $i++) {
                        if ($i == 0) {
                            $query->where($searchColumns[$i], 'like', '%' . $search_value . '%');
                        } else {
                            $query->orWhere($searchColumns[$i], 'like', '%' . $search_value . '%');
                        }
                    }
                });
            }
            $data = $query->get();
            $data = json_decode(json_encode($data), true);
            foreach ($data as $key => $value) {
                $data[$key]['created_at'] = convertDateTime($value['created_at']);
            }
        }

        if ($isFilterApply == 1) {
            $recordsFiltered = count($data);
        }

        $jsonData = [
            'draw' => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            'recordsTotal' => intval($recordsTotal), // total number of records
            'recordsFiltered' => intval($recordsFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
            'data' => $data, // total data array
        ];
        return $jsonData;
    }

    function markAsAccept(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => ['required'],
        ]);

        if ($validator->fails()) {
            $response = [];
            $response['status'] = 0;
            $response['msg'] = 'The request could not be understood by the server due to malformed syntax';
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors();

            return response()->json($response)->header('Content-Type', 'application/json');
        } else {
            $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
            if ($isAdminOrCompanyAdmin == 1 || iscreUser() == 1) {
                $GiftProductOrder = GiftProductOrder::find($request->id);
                if ($GiftProductOrder) {
                    if ($request->type == 'gift') {
                        $GiftProductOrder->status = 1;
                    } elseif ($request->type == 'cash') {
                        $GiftProductOrder->cash_status = 1;
                    } elseif ($request->type == 'cashback') {
                        $GiftProductOrder->cashback_status = 1;
                    }
                    $GiftProductOrder->save();
                    $response = successRes('Successfully updated status');
                    $User = User::find($GiftProductOrder->user_id);
                    if ($User) {
                        if ($User->fcm_token != '') {
                            $mobileNotificationTitle = 'Order Update';
                            $mobileNotificationMessage = 'Your Order #' . $GiftProductOrder->id . ' Status Update to PLACED / ON REVIEW To ACCEPTED';
                            sendNotificationTOAndroid($mobileNotificationTitle, $mobileNotificationMessage, [$User->fcm_token], 'Gift Order', $GiftProductOrder);
                        }

                        $debugLog = [];
                        $debugLog['for_user_id'] = $User->id;
                        $debugLog['name'] = 'order-status-accept';
                        $debugLog['points'] = 0;
                        $debugLog['order_id'] = $GiftProductOrder->id;
                        $debugLog['description'] = 'Your Order #' . $GiftProductOrder->id . ' Status Update to PLACED / ON REVIEW To ACCEPTED';
                        $debugLog['type'] = '';
                        saveCRMUserLog($debugLog);
                    }
                } else {
                    $response = errorRes('Something went wrong');
                }
            }
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function markAsReject(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => ['required'],
        ]);

        if ($validator->fails()) {
            $response = [];
            $response['status'] = 0;
            $response['msg'] = 'The request could not be understood by the server due to malformed syntax';
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors();

            return response()->json($response)->header('Content-Type', 'application/json');
        } else {
            $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
            if ($isAdminOrCompanyAdmin == 1 || iscreUser() == 1) {
                if ($request->type == 'gift') {
                    $GiftProductOrder = GiftProductOrder::find($request->id);
                    if ($GiftProductOrder && $GiftProductOrder->status != 3) {
                        $GiftProductOrder->status = 3;
                        $GiftProductOrder->save();
                        $response = successRes('Successfully updated status');
                        $User = User::find($GiftProductOrder->user_id);
                        if ($User) {
                            if ($User->type == 201 || $User->type == 202) {
                                $Architect = Architect::where('user_id', $User->id)->first();
                                if ($Architect) {
                                    $Architect->total_point_used = $Architect->total_point_used - $GiftProductOrder->total_point_value;
                                    $Architect->total_point_current = $Architect->total_point_current + $GiftProductOrder->total_point_value;
                                    $Architect->save();

                                    $debugLog = [];
                                    $debugLog['for_user_id'] = $User->id;
                                    $debugLog['name'] = 'point-back';
                                    $debugLog['points'] = $GiftProductOrder->total_point_value;
                                    $debugLog['order_id'] = $GiftProductOrder->id;
                                    $debugLog['description'] = $GiftProductOrder->total_point_value . ' Point back from order #' . $GiftProductOrder->id;
                                    $debugLog['type'] = '';
                                    saveCRMUserLog($debugLog);
                                }
                            } elseif ($User->type == 301 || $User->type == 302) {
                                $Electrician = Electrician::where('user_id', $User->id)->first();
                                if ($Electrician) {
                                    $Electrician->total_point_used = $Electrician->total_point_used - $GiftProductOrder->total_point_value;
                                    $Electrician->total_point_current = $Electrician->total_point_current + $GiftProductOrder->total_point_value;
                                    $Electrician->save();

                                    $debugLog = [];
                                    $debugLog['for_user_id'] = $User->id;
                                    $debugLog['name'] = 'point-back';
                                    $debugLog['points'] = $GiftProductOrder->total_point_value;
                                    $debugLog['order_id'] = $GiftProductOrder->id;
                                    $debugLog['description'] = $GiftProductOrder->total_point_value . ' Point back from order #' . $GiftProductOrder->id;
                                    $debugLog['type'] = '';
                                    saveCRMUserLog($debugLog);
                                }
                            }

                            if ($User->fcm_token != '') {
                                $mobileNotificationTitle = 'Order Update';
                                $mobileNotificationMessage = 'Your Order #' . $GiftProductOrder->id . ' Status Update to PLACED / ON REVIEW To REJECTED';
                                sendNotificationTOAndroid($mobileNotificationTitle, $mobileNotificationMessage, [$User->fcm_token], 'Gift Order', $GiftProductOrder);
                            }
                        }
                        $response = successRes('Successfully rejected order');
                    } else {
                        $response = errorRes('Something went wrong');
                    }
                } elseif ($request->type == 'cash') {
                    $GiftProductOrder = GiftProductOrder::find($request->id);
                    if ($GiftProductOrder && $GiftProductOrder->cash_status != 3) {
                        $GiftProductOrder->cash_status = 3;
                        $GiftProductOrder->save();
                        $response = successRes('Successfully updated status');
                        $User = User::find($GiftProductOrder->user_id);
                        if ($User) {
                            if ($User->type == 201 || $User->type == 202) {
                                $Architect = Architect::where('user_id', $User->id)->first();
                                if ($Architect) {
                                    $Architect->total_point_used = $Architect->total_point_used - $GiftProductOrder->total_point_value;
                                    $Architect->total_point_current = $Architect->total_point_current + $GiftProductOrder->total_point_value;
                                    $Architect->save();

                                    $debugLog = [];
                                    $debugLog['for_user_id'] = $User->id;
                                    $debugLog['name'] = 'point-back';
                                    $debugLog['points'] = $GiftProductOrder->total_point_value;
                                    $debugLog['order_id'] = $GiftProductOrder->id;
                                    $debugLog['description'] = $GiftProductOrder->total_point_value . ' Point back from order #' . $GiftProductOrder->id;
                                    $debugLog['type'] = '';
                                    saveCRMUserLog($debugLog);
                                }
                            } elseif ($User->type == 301 || $User->type == 302) {
                                $Electrician = Electrician::where('user_id', $User->id)->first();
                                if ($Electrician) {
                                    $Electrician->total_point_used = $Electrician->total_point_used - $GiftProductOrder->total_point_value;
                                    $Electrician->total_point_current = $Electrician->total_point_current + $GiftProductOrder->total_point_value;
                                    $Electrician->save();

                                    $debugLog = [];
                                    $debugLog['for_user_id'] = $User->id;
                                    $debugLog['name'] = 'point-back';
                                    $debugLog['points'] = $GiftProductOrder->total_point_value;
                                    $debugLog['order_id'] = $GiftProductOrder->id;
                                    $debugLog['description'] = $GiftProductOrder->total_point_value . ' Point back from order #' . $GiftProductOrder->id;
                                    $debugLog['type'] = '';
                                    saveCRMUserLog($debugLog);
                                }
                            }

                            if ($User->fcm_token != '') {
                                $mobileNotificationTitle = 'Order Update';
                                $mobileNotificationMessage = 'Your Order #' . $GiftProductOrder->id . ' Status Update to PLACED / ON REVIEW To REJECTED';
                                sendNotificationTOAndroid($mobileNotificationTitle, $mobileNotificationMessage, [$User->fcm_token], 'Gift Order', $GiftProductOrder);
                            }
                        }
                        $response = successRes('Successfully rejected order');
                    } else {
                        $response = errorRes('Something went wrong');
                    }
                } elseif ($request->type == 'cashback') {
                    $GiftProductOrder = GiftProductOrder::find($request->id);
                    if ($GiftProductOrder && $GiftProductOrder->cashback_status != 3) {
                        $GiftProductOrder->cashback_status = 3;
                        $GiftProductOrder->save();
                        $response = successRes('Successfully updated status');
                        $User = User::find($GiftProductOrder->user_id);
                        if ($User) {
                            if ($User->type == 201 || $User->type == 202) {
                                $Architect = Architect::where('user_id', $User->id)->first();
                                if ($Architect) {
                                    $Architect->total_point_used = $Architect->total_point_used - $GiftProductOrder->total_point_value;
                                    $Architect->total_point_current = $Architect->total_point_current + $GiftProductOrder->total_point_value;
                                    $Architect->save();

                                    $debugLog = [];
                                    $debugLog['for_user_id'] = $User->id;
                                    $debugLog['name'] = 'point-back';
                                    $debugLog['points'] = $GiftProductOrder->total_point_value;
                                    $debugLog['order_id'] = $GiftProductOrder->id;
                                    $debugLog['description'] = $GiftProductOrder->total_point_value . ' Point back from order #' . $GiftProductOrder->id;
                                    $debugLog['type'] = '';
                                    saveCRMUserLog($debugLog);
                                }
                            } elseif ($User->type == 301 || $User->type == 302) {
                                $Electrician = Electrician::where('user_id', $User->id)->first();
                                if ($Electrician) {
                                    $Electrician->total_point_used = $Electrician->total_point_used - $GiftProductOrder->total_point_value;
                                    $Electrician->total_point_current = $Electrician->total_point_current + $GiftProductOrder->total_point_value;
                                    $Electrician->save();

                                    $debugLog = [];
                                    $debugLog['for_user_id'] = $User->id;
                                    $debugLog['name'] = 'point-back';
                                    $debugLog['points'] = $GiftProductOrder->total_point_value;
                                    $debugLog['order_id'] = $GiftProductOrder->id;
                                    $debugLog['description'] = $GiftProductOrder->total_point_value . ' Point back from order #' . $GiftProductOrder->id;
                                    $debugLog['type'] = '';
                                    saveCRMUserLog($debugLog);
                                }
                            }

                            if ($User->fcm_token != '') {
                                $mobileNotificationTitle = 'Order Update';
                                $mobileNotificationMessage = 'Your Order #' . $GiftProductOrder->id . ' Status Update to PLACED / ON REVIEW To REJECTED';
                                sendNotificationTOAndroid($mobileNotificationTitle, $mobileNotificationMessage, [$User->fcm_token], 'Gift Order', $GiftProductOrder);
                            }
                        }
                        $response = successRes('Successfully rejected order');
                    } else {
                        $response = errorRes('Something went wrong');
                    }
                }
            }
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function markAsDeliever(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => ['required'],
        ]);

        if ($validator->fails()) {
            $response = [];
            $response['status'] = 0;
            $response['msg'] = 'The request could not be understood by the server due to malformed syntax';
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors();

            return response()->json($response)->header('Content-Type', 'application/json');
        } else {
            $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
            if ($isAdminOrCompanyAdmin == 1 || iscreUser() == 1) {
                $GiftProductOrder = GiftProductOrder::find($request->id);
                if ($request->type == 'gift') {
                    if ($GiftProductOrder && $GiftProductOrder->status != 4) {
                        $GiftProductOrder->status = 4;
                        $GiftProductOrder->save();
                        $response = successRes('Successfully updated status');
                        $User = User::find($GiftProductOrder->user_id);
                        if ($User) {
                            if ($User->fcm_token != '') {
                                $mobileNotificationTitle = 'Order Update';
                                $mobileNotificationMessage = 'Your Order #' . $GiftProductOrder->id . ' Status Update to DISPATCHED To DELIEVERED';
                                sendNotificationTOAndroid($mobileNotificationTitle, $mobileNotificationMessage, [$User->fcm_token], 'Gift Order', $GiftProductOrder);
                            }

                            $debugLog = [];
                            $debugLog['for_user_id'] = $User->id;
                            $debugLog['name'] = 'order-status-deliever';
                            $debugLog['points'] = 0;
                            $debugLog['order_id'] = $GiftProductOrder->id;
                            $debugLog['description'] = 'Your Order #' . $GiftProductOrder->id . ' Status Update to DISPATCHED To DELIEVERED';
                            $debugLog['type'] = '';
                            saveCRMUserLog($debugLog);
                        }
                    } else {
                        $response = errorRes('Something went wrong');
                    }
                } elseif ($request->type == 'cash') {
                    if ($GiftProductOrder && $GiftProductOrder->cash_status != 4) {
                        $GiftProductOrder->cash_status = 4;
                        $GiftProductOrder->save();
                        $response = successRes('Successfully updated status');
                        $User = User::find($GiftProductOrder->user_id);
                        if ($User) {
                            if ($User->fcm_token != '') {
                                $mobileNotificationTitle = 'Order Update';
                                $mobileNotificationMessage = 'Your Order #' . $GiftProductOrder->id . ' Status Update to DISPATCHED To DELIEVERED';
                                sendNotificationTOAndroid($mobileNotificationTitle, $mobileNotificationMessage, [$User->fcm_token], 'Gift Order', $GiftProductOrder);
                            }

                            $debugLog = [];
                            $debugLog['for_user_id'] = $User->id;
                            $debugLog['name'] = 'order-status-deliever';
                            $debugLog['points'] = 0;
                            $debugLog['order_id'] = $GiftProductOrder->id;
                            $debugLog['description'] = 'Your Order #' . $GiftProductOrder->id . ' Status Update to DISPATCHED To DELIEVERED';
                            $debugLog['type'] = '';
                            saveCRMUserLog($debugLog);
                        }
                    } else {
                        $response = errorRes('Something went wrong');
                    }
                } elseif ($request->type == 'cashback') {
                    if ($GiftProductOrder && $GiftProductOrder->cashback_status != 4) {
                        $GiftProductOrder->cashback_status = 4;
                        $GiftProductOrder->save();
                        $response = successRes('Successfully updated status');
                        $User = User::find($GiftProductOrder->user_id);
                        if ($User) {
                            if ($User->fcm_token != '') {
                                $mobileNotificationTitle = 'Order Update';
                                $mobileNotificationMessage = 'Your Order #' . $GiftProductOrder->id . ' Status Update to DISPATCHED To DELIEVERED';
                                sendNotificationTOAndroid($mobileNotificationTitle, $mobileNotificationMessage, [$User->fcm_token], 'Gift Order', $GiftProductOrder);
                            }

                            $debugLog = [];
                            $debugLog['for_user_id'] = $User->id;
                            $debugLog['name'] = 'order-status-deliever';
                            $debugLog['points'] = 0;
                            $debugLog['order_id'] = $GiftProductOrder->id;
                            $debugLog['description'] = 'Your Order #' . $GiftProductOrder->id . ' Status Update to DISPATCHED To DELIEVERED';
                            $debugLog['type'] = '';
                            saveCRMUserLog($debugLog);
                        }
                    } else {
                        $response = errorRes('Something went wrong');
                    }
                }
                $response = successRes('Successfully rejected order');
            }
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function markAsRecieve(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => ['required'],
        ]);

        if ($validator->fails()) {
            $response = [];
            $response['status'] = 0;
            $response['msg'] = 'The request could not be understood by the server due to malformed syntax';
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors();

            return response()->json($response)->header('Content-Type', 'application/json');
        } else {
            $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
            if ($isAdminOrCompanyAdmin == 1 || iscreUser() == 1) {
                if ($request->type == 'gift') {
                    $GiftProductOrder = GiftProductOrder::find($request->id);
                    if ($GiftProductOrder && $GiftProductOrder->status != 5) {
                        $GiftProductOrder->status = 5;
                        $GiftProductOrder->save();
                        $response = successRes('Successfully updated status');
                        $User = User::find($GiftProductOrder->user_id);
                        if ($User) {
                            if ($User->fcm_token != '') {
                                $mobileNotificationTitle = 'Order Update';
                                $mobileNotificationMessage = 'Your Order ' . $GiftProductOrder->id . ' Status Update to DELIEVERED To RECEIVED';
                                sendNotificationTOAndroid($mobileNotificationTitle, $mobileNotificationMessage, [$User->fcm_token], 'Gift Order', $GiftProductOrder);
                            }

                            $debugLog = [];
                            $debugLog['for_user_id'] = $User->id;
                            $debugLog['name'] = 'order-status-recieve';
                            $debugLog['points'] = 0;
                            $debugLog['order_id'] = $GiftProductOrder->id;
                            $debugLog['description'] = 'Your Order #' . $GiftProductOrder->id . ' Status Update to DELIEVERED To RECEIVED';
                            $debugLog['type'] = '';
                            saveCRMUserLog($debugLog);

                            $configrationForNotify = configrationForNotify();
                            $params = [];
                            $params['bcc_email'] = ['poonam@whitelion.in'];
                            $params['from_email'] = $configrationForNotify['from_email'];
                            $params['from_name'] = $configrationForNotify['from_name'];
                            $params['user_name'] = $User->first_name . ' ' . $User->last_name;
                            $params['to_email'] = $User->email;
                            $params['user_mobile'] = $User->phone_number;
                            // $params['to_name'] = $configrationForNotify['to_name'];
                            // $params['query'] = $GiftProductOrderQuery;
                            $params['subject'] = 'Important notice regarding claim reward';

                            Mail::send('emails.gift_order_received', ['params' => $params], function ($m) use ($params) {
                                // SEND MAIL
                                $m->from($params['from_email'], $params['from_name'])->subject($params['subject']);
                                $m->bcc($params['bcc_email']);
                                $m->to($params['to_email'], $params['user_name'])->subject($params['subject']);
                            });

                            //TEMPLATE ARCHITECT & ELECTRICIAN REQUEST TO CLAIM GIFT
                            try {
                                $whatsapp_controller = new WhatsappApiContoller();
                                $perameater_request = new Request();
                                $perameater_request['q_whatsapp_massage_mobileno'] = $User->phone_number;
					            $perameater_request['q_whatsapp_massage_template'] = 'electrician_reward_claim_request_close';
					            $perameater_request['q_whatsapp_massage_attechment'] = '';
					            $perameater_request['q_broadcast_name'] = $User->first_name . ' ' . $User->last_name;
					            $perameater_request['q_whatsapp_massage_parameters'] = array();
                                $wp_response = $whatsapp_controller->sendTemplateMessage($perameater_request);
                                $response['whatsapp'] = $wp_response;
                            } catch (Exception $e) {
                                $response['whatsapp'] = $e->getMessage();
                            }
                        }
                    } else {
                        $response = errorRes('Something went wrong');
                    }
                } elseif ($request->type == 'cash') {
                    $GiftProductOrder = GiftProductOrder::find($request->id);
                    if ($GiftProductOrder && $GiftProductOrder->cash_status != 5) {
                        $GiftProductOrder->cash_status = 5;
                        $GiftProductOrder->save();
                        $response = successRes('Successfully updated status');
                        $User = User::find($GiftProductOrder->user_id);
                        if ($User) {
                            if ($User->fcm_token != '') {
                                $mobileNotificationTitle = 'Order Update';
                                $mobileNotificationMessage = 'Your Order ' . $GiftProductOrder->id . ' Status Update to DELIEVERED To RECEIVED';
                                sendNotificationTOAndroid($mobileNotificationTitle, $mobileNotificationMessage, [$User->fcm_token], 'Gift Order', $GiftProductOrder);
                            }

                            $debugLog = [];
                            $debugLog['for_user_id'] = $User->id;
                            $debugLog['name'] = 'order-status-recieve';
                            $debugLog['points'] = 0;
                            $debugLog['order_id'] = $GiftProductOrder->id;
                            $debugLog['description'] = 'Your Order #' . $GiftProductOrder->id . ' Status Update to DELIEVERED To RECEIVED';
                            $debugLog['type'] = '';
                            saveCRMUserLog($debugLog);

                            $configrationForNotify = configrationForNotify();
                            $params = [];
                            $params['bcc_email'] = ['poonam@whitelion.in'];
                            $params['from_email'] = $configrationForNotify['from_email'];
                            $params['from_name'] = $configrationForNotify['from_name'];
                            $params['user_name'] = $User->first_name . ' ' . $User->last_name;
                            $params['to_email'] = $User->email;
                            $params['user_mobile'] = $User->phone_number;
                            // $params['to_name'] = $configrationForNotify['to_name'];
                            // $params['query'] = $GiftProductOrderQuery;
                            $params['subject'] = 'Important notice regarding claim reward';

                            Mail::send('emails.gift_order_received', ['params' => $params], function ($m) use ($params) {
                                // SEND MAIL
                                $m->from($params['from_email'], $params['from_name'])->subject($params['subject']);
                                $m->bcc($params['bcc_email']);
                                $m->to($params['to_email'], $params['user_name'])->subject($params['subject']);
                            });

                            //TEMPLATE ARCHITECT & ELECTRICIAN REQUEST TO CLAIM GIFT
                            try {
                                $whatsapp_controller = new WhatsappApiContoller();
                                $perameater_request = new Request();
                                $perameater_request['q_whatsapp_massage_mobileno'] = $User->phone_number;
					            $perameater_request['q_whatsapp_massage_template'] = 'electrician_reward_claim_request_close';
					            $perameater_request['q_whatsapp_massage_attechment'] = '';
					            $perameater_request['q_broadcast_name'] = $User->first_name . ' ' . $User->last_name;
					            $perameater_request['q_whatsapp_massage_parameters'] = array();
                                $wp_response = $whatsapp_controller->sendTemplateMessage($perameater_request);
                                $response['whatsapp'] = $wp_response;
                            } catch (Exception $e) {
                                $response['whatsapp'] = $e->getMessage();
                            }
                        }
                    } else {
                        $response = errorRes('Something went wrong');
                    }
                } elseif ($request->type == 'cashback') {
                    $GiftProductOrder = GiftProductOrder::find($request->id);
                    if ($GiftProductOrder && $GiftProductOrder->cashback_status != 5) {
                        $GiftProductOrder->cashback_status = 5;
                        $GiftProductOrder->save();
                        $response = successRes('Successfully updated status');
                        $User = User::find($GiftProductOrder->user_id);
                        if ($User) {
                            if ($User->fcm_token != '') {
                                $mobileNotificationTitle = 'Order Update';
                                $mobileNotificationMessage = 'Your Order ' . $GiftProductOrder->id . ' Status Update to DELIEVERED To RECEIVED';
                                sendNotificationTOAndroid($mobileNotificationTitle, $mobileNotificationMessage, [$User->fcm_token], 'Gift Order', $GiftProductOrder);
                            }

                            $debugLog = [];
                            $debugLog['for_user_id'] = $User->id;
                            $debugLog['name'] = 'order-status-recieve';
                            $debugLog['points'] = 0;
                            $debugLog['order_id'] = $GiftProductOrder->id;
                            $debugLog['description'] = 'Your Order #' . $GiftProductOrder->id . ' Status Update to DELIEVERED To RECEIVED';
                            $debugLog['type'] = '';
                            saveCRMUserLog($debugLog);

                            $configrationForNotify = configrationForNotify();
                            $params = [];
                            $params['bcc_email'] = ['poonam@whitelion.in'];
                            $params['from_email'] = $configrationForNotify['from_email'];
                            $params['from_name'] = $configrationForNotify['from_name'];
                            $params['user_name'] = $User->first_name . ' ' . $User->last_name;
                            $params['to_email'] = $User->email;
                            $params['user_mobile'] = $User->phone_number;
                            // $params['to_name'] = $configrationForNotify['to_name'];
                            // $params['query'] = $GiftProductOrderQuery;
                            $params['subject'] = 'Important notice regarding claim reward';

                            Mail::send('emails.gift_order_received', ['params' => $params], function ($m) use ($params) {
                                // SEND MAIL
                                $m->from($params['from_email'], $params['from_name'])->subject($params['subject']);
                                $m->bcc($params['bcc_email']);
                                $m->to($params['to_email'], $params['user_name'])->subject($params['subject']);
                            });

                            //TEMPLATE ARCHITECT & ELECTRICIAN REQUEST TO CLAIM GIFT
                            try {
                                $whatsapp_controller = new WhatsappApiContoller();
                                $perameater_request = new Request();
                                $perameater_request['q_whatsapp_massage_mobileno'] = $User->phone_number;
					            $perameater_request['q_whatsapp_massage_template'] = 'electrician_reward_claim_request_close';
					            $perameater_request['q_whatsapp_massage_attechment'] = '';
					            $perameater_request['q_broadcast_name'] = $User->first_name . ' ' . $User->last_name;
					            $perameater_request['q_whatsapp_massage_parameters'] = array();
                                $wp_response = $whatsapp_controller->sendTemplateMessage($perameater_request);
                                $response['whatsapp'] = $wp_response;
                            } catch (Exception $e) {
                                $response['whatsapp'] = $e->getMessage();
                            }
                        }
                    } else {
                        $response = errorRes('Something went wrong');
                    }
                }
            }
            $response = successRes('Successfully recieved order');
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function detail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => ['required'],
        ]);

        if ($validator->fails()) {
            $response = [];
            $response['status'] = 0;
            $response['msg'] = 'The request could not be understood by the server due to malformed syntax';
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors();
            return response()->json($response)->header('Content-Type', 'application/json');
        } else {
            $selectColumns = ['gift_product_orders.id', 'gift_product_orders.created_at', 'gift_product_orders.created_at', 'gift_product_orders.total_point_value', 'gift_product_orders.status', 'gift_product_orders.d_address_line1', 'gift_product_orders.d_address_line2', 'gift_product_orders.d_pincode', 'gift_product_orders.d_country_id', 'gift_product_orders.d_state_id', 'gift_product_orders.d_city_id', 'gift_product_orders.user_id', 'users.first_name', 'users.last_name', 'users.phone_number', 'users.dialing_code', 'users.email', 'users.type', 'gift_product_orders.total_cashback', 'gift_product_orders.total_cash', 'gift_product_orders.payment_mode', 'gift_product_orders.bank_detail_ifsc', 'gift_product_orders.bank_detail_account', 'gift_product_orders.bank_detail_upi', 'gift_product_orders.cash_point_value', 'gift_product_orders.product_point_value'];

            $GiftProductOrder = GiftProductOrder::query();
            $GiftProductOrder->select($selectColumns);
            $GiftProductOrder->leftJoin('users', 'users.id', '=', 'gift_product_orders.user_id');

            $GiftProductOrder->where('gift_product_orders.id', $request->id);
            $GiftProductOrder->limit(1);
            $GiftProductOrder = $GiftProductOrder->first();
            if ($GiftProductOrder) {
                $isSalePerson = isSalePerson();

                if ($isSalePerson == 1) {
                    $childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
                }

                if ($isSalePerson == 1) {
                    if ($GiftProductOrder->type == 202) {
                        //user_id
                        $Architect = Architect::where('user_id', $GiftProductOrder->user_id)->first();

                        if (!in_array($Architect->sale_person_id, $childSalePersonsIds)) {
                            $response = errorRes('Invalid access');
                            return response()->json($response)->header('Content-Type', 'application/json');
                        }
                    } elseif ($GiftProductOrder->type == 302) {
                        $Electrician = Electrician::where('user_id', $GiftProductOrder->user_id)->first();
                        if (!in_array($Electrician->sale_person_id, $childSalePersonsIds)) {
                            $response = errorRes('Invalid access');
                            return response()->json($response)->header('Content-Type', 'application/json');
                        }
                    }
                }

                $GiftProductOrderItem = GiftProductOrderItem::query();
                $GiftProductOrderItem->select('gift_products.name', 'gift_products.image', 'gift_product_order_items.qty', 'gift_product_order_items.total_point_value', 'gift_product_order_items.point_value');
                $GiftProductOrderItem->leftJoin('gift_products', 'gift_products.id', '=', 'gift_product_order_items.gift_product_id');
                $GiftProductOrderItem->where('gift_product_order_id', $GiftProductOrder->id);
                $GiftProductOrderItem->orderBy('gift_product_order_items.id', 'desc');
                $GiftProductOrderItem = $GiftProductOrderItem->get();

                $GiftProductOrderItem = json_decode(json_encode($GiftProductOrderItem), true);
                foreach ($GiftProductOrderItem as $key => $value) {
                    $GiftProductOrderItem[$key]['info'] = [];
                    $GiftProductOrderItem[$key]['info']['name'] = $value['name'];
                    $GiftProductOrderItem[$key]['info']['image'] = getSpaceFilePath($value['image']);
                }
                $GiftProductOrder['items'] = $GiftProductOrderItem;
                $response = successRes('Order detail');
                $data = [];
                $data['preview'] = 0;
                $data['order'] = json_decode(json_encode($GiftProductOrder), true);

                $data['order']['total_cashback_value'] = $data['order']['total_cashback'];

                //$data['total_cash_value'] = $data['order']['total_cash'];
                $data['total_cash_pv'] = $data['order']['cash_point_value'];
                $data['order']['total_product_pv'] = $data['order']['product_point_value'];
                $data['total_cash'] = $data['order']['total_cash'];
                // $data['order']['total_cashback_value'] = $data['order']['total_cashback'];
                // $data['order']['total_cash_value'] = $data['order']['total_cash'];

                $data['name'] = $data['order']['first_name'] . ' ' . $data['order']['last_name'];
                $data['email'] = $data['order']['email'];
                $data['phone_number'] = $data['order']['dialing_code'] . ' ' . $data['order']['phone_number'];

                $data['d_country'] = getCountryName($data['order']['d_country_id']);
                $data['d_state'] = getStateName($data['order']['d_state_id']);
                $data['d_city'] = getCityName($data['order']['d_city_id']);
                $data['d_pincode'] = $data['order']['d_pincode'];
                $data['d_address_line1'] = $data['order']['d_address_line1'];
                $data['d_address_line2'] = $data['order']['d_address_line2'];

                $response = successRes('Order Previw');
                $response['data'] = $data;
                $response['preview'] = view('crm/architect/orders_preview', compact('data'))->render();
            } else {
                $response = errorRes('Invalid order id');
            }
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function markAsDispatch(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => ['required'],
        ]);
        if ($validator->fails()) {
            $response = [];
            $response['status'] = 0;
            $response['msg'] = 'The request could not be understood by the server due to malformed syntax';
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors();

            return response()->json($response)->header('Content-Type', 'application/json');
        } else {
            $GiftProductOrder = GiftProductOrder::find($request->order_id);

            if ($GiftProductOrder) {
                if ($request->order_type == 'gift') {
                    $validator = Validator::make($request->all(), [
                        'order_track_id' => ['required'],
                        'order_courier_service_id' => ['required'],
                        'order_dispatch_detail' => ['required'],
                    ]);
                    if ($validator->fails()) {
                        $response = [];
                        $response['status'] = 0;
                        $response['msg'] = 'The request could not be understood by the server due to malformed syntax';
                        $response['statuscode'] = 400;
                        $response['data'] = $validator->errors();

                        return response()->json($response)->header('Content-Type', 'application/json');
                    } else {
                        if ($GiftProductOrder->status == 1) {
                            $uploadedFile1 = '';
                            if ($request->hasFile('order_dispatch_detail')) {
                                $folderPathofFile = '/s/gift-order-dispatch-detail';
                                if (!is_dir(public_path($folderPathofFile))) {
                                    mkdir(public_path($folderPathofFile));
                                }

                                $folderPathofFile = '/s/gift-order-dispatch-detail/' . date('Y');

                                if (!is_dir(public_path($folderPathofFile))) {
                                    mkdir(public_path($folderPathofFile));
                                }

                                $folderPathofFile = '/s/gift-order-dispatch-detail/' . date('Y') . '/' . date('m');
                                if (!is_dir(public_path($folderPathofFile))) {
                                    mkdir(public_path($folderPathofFile));
                                }

                                $fileObject1 = $request->file('order_dispatch_detail');
                                $extension = $fileObject1->getClientOriginalExtension();
                                $fileTypes = acceptFileTypes('gift.order.dispatch.detail', 'server');
                                if (in_array(strtolower($extension), $fileTypes)) {
                                    $fileName1 = time() . mt_rand(10000, 99999) . '.' . $extension;

                                    $destinationPath = public_path($folderPathofFile);
                                    $fileObject1->move($destinationPath, $fileName1);

                                    if (File::exists(public_path($folderPathofFile . '/' . $fileName1))) {
                                        $uploadedFile1 = $folderPathofFile . '/' . $fileName1;

                                        //START UPLOAD FILE ON SPACES

                                        $spaceUploadResponse = uploadFileOnSpaces(public_path($uploadedFile1), $uploadedFile1);
                                        if ($spaceUploadResponse != 1) {
                                            $uploadedFile1 = '';
                                        } else {
                                            unlink(public_path($uploadedFile1));
                                        }
                                        //END UPLOAD FILE ON SPACES
                                    }
                                }
                            }
                            $GiftProductOrder->track_id = $request->order_track_id;
                            $GiftProductOrder->courier_service_id = $request->order_courier_service_id;
                            $GiftProductOrder->dispatch_detail = $uploadedFile1;
                            $GiftProductOrder->status = 2;
                            $GiftProductOrder->save();
                        } else {
                            $response = errorRes('Order already Gift dispateched');
                        }
                    }
                } elseif ($request->order_type == 'cash') {
                    $validator = Validator::make($request->all(), [
                        'order_transaction_id' => ['required'],
                        'order_dispatch_detail' => ['required'],
                    ]);
                    if ($validator->fails()) {
                        $response = [];
                        $response['status'] = 0;
                        $response['msg'] = 'The request could not be understood by the server due to malformed syntax';
                        $response['statuscode'] = 400;
                        $response['data'] = $validator->errors();

                        return response()->json($response)->header('Content-Type', 'application/json');
                    } else {
                        if ($GiftProductOrder->cash_status == 1) {
                            $uploadedFile1 = '';
                            if ($request->hasFile('order_dispatch_detail')) {
                                $folderPathofFile = '/s/gift-order-dispatch-detail';
                                if (!is_dir(public_path($folderPathofFile))) {
                                    mkdir(public_path($folderPathofFile));
                                }

                                $folderPathofFile = '/s/gift-order-dispatch-detail/' . date('Y');

                                if (!is_dir(public_path($folderPathofFile))) {
                                    mkdir(public_path($folderPathofFile));
                                }

                                $folderPathofFile = '/s/gift-order-dispatch-detail/' . date('Y') . '/' . date('m');
                                if (!is_dir(public_path($folderPathofFile))) {
                                    mkdir(public_path($folderPathofFile));
                                }

                                $fileObject1 = $request->file('order_dispatch_detail');
                                $extension = $fileObject1->getClientOriginalExtension();
                                $fileTypes = acceptFileTypes('gift.order.dispatch.detail', 'server');
                                if (in_array(strtolower($extension), $fileTypes)) {
                                    $fileName1 = time() . mt_rand(10000, 99999) . '.' . $extension;

                                    $destinationPath = public_path($folderPathofFile);
                                    $fileObject1->move($destinationPath, $fileName1);

                                    if (File::exists(public_path($folderPathofFile . '/' . $fileName1))) {
                                        $uploadedFile1 = $folderPathofFile . '/' . $fileName1;

                                        //START UPLOAD FILE ON SPACES

                                        $spaceUploadResponse = uploadFileOnSpaces(public_path($uploadedFile1), $uploadedFile1);
                                        if ($spaceUploadResponse != 1) {
                                            $uploadedFile1 = '';
                                        } else {
                                            unlink(public_path($uploadedFile1));
                                        }
                                        //END UPLOAD FILE ON SPACES
                                    }
                                }
                            }
                            $GiftProductOrder->cash_transaction_id = $request->order_transaction_id;
                            $GiftProductOrder->cash_document = $uploadedFile1;
                            $GiftProductOrder->cash_status = 2;
                            $GiftProductOrder->save();
                        } else {
                            $response = errorRes('Order already Cash dispateched');
                        }
                    }
                } elseif ($request->order_type == 'cashback') {
                    $validator = Validator::make($request->all(), [
                        'order_transaction_id' => ['required'],
                        'order_dispatch_detail' => ['required'],
                    ]);
                    if ($validator->fails()) {
                        $response = [];
                        $response['status'] = 0;
                        $response['msg'] = 'The request could not be understood by the server due to malformed syntax';
                        $response['statuscode'] = 400;
                        $response['data'] = $validator->errors();

                        return response()->json($response)->header('Content-Type', 'application/json');
                    } else {
                        if ($GiftProductOrder->cashback_status == 1) {
                            $uploadedFile1 = '';

                            if ($request->hasFile('order_dispatch_detail')) {
                                $folderPathofFile = '/s/gift-order-dispatch-detail';
                                if (!is_dir(public_path($folderPathofFile))) {
                                    mkdir(public_path($folderPathofFile));
                                }

                                $folderPathofFile = '/s/gift-order-dispatch-detail/' . date('Y');

                                if (!is_dir(public_path($folderPathofFile))) {
                                    mkdir(public_path($folderPathofFile));
                                }

                                $folderPathofFile = '/s/gift-order-dispatch-detail/' . date('Y') . '/' . date('m');
                                if (!is_dir(public_path($folderPathofFile))) {
                                    mkdir(public_path($folderPathofFile));
                                }

                                $fileObject1 = $request->file('order_dispatch_detail');
                                $extension = $fileObject1->getClientOriginalExtension();
                                $fileTypes = acceptFileTypes('gift.order.dispatch.detail', 'server');
                                if (in_array(strtolower($extension), $fileTypes)) {
                                    $fileName1 = time() . mt_rand(10000, 99999) . '.' . $extension;

                                    $destinationPath = public_path($folderPathofFile);
                                    $fileObject1->move($destinationPath, $fileName1);

                                    if (File::exists(public_path($folderPathofFile . '/' . $fileName1))) {
                                        $uploadedFile1 = $folderPathofFile . '/' . $fileName1;

                                        //START UPLOAD FILE ON SPACES

                                        $spaceUploadResponse = uploadFileOnSpaces(public_path($uploadedFile1), $uploadedFile1);
                                        if ($spaceUploadResponse != 1) {
                                            $uploadedFile1 = '';
                                        } else {
                                            unlink(public_path($uploadedFile1));
                                        }
                                        //END UPLOAD FILE ON SPACES
                                    }
                                }
                            }

                            $GiftProductOrder->cashback_transaction_id = $request->order_transaction_id;
                            $GiftProductOrder->cashback_document = $uploadedFile1;
                            $GiftProductOrder->cashback_status = 2;
                            $GiftProductOrder->save();
                        } else {
                            $response = errorRes('Order already CashBack dispateched');
                        }
                    }
                }

                $response = successRes('Successfully cashback status updated to dispateched');
                $User = User::find($GiftProductOrder->user_id);
                if ($User->fcm_token != '') {
                    $mobileNotificationTitle = 'Order Update';
                    $mobileNotificationMessage = 'Your Order #' . $GiftProductOrder->id . ' Status CashBack Update to ACCEPTED To DISPATCHED';
                    sendNotificationTOAndroid($mobileNotificationTitle, $mobileNotificationMessage, [$User->fcm_token], 'Gift Order', $GiftProductOrder);
                }

                if ($User->type == 201 || $User->type == 202) {
                    $SalesPerson = Architect::where('user_id', $User->id)->first();
                    $SalesPerson = User::find($SalesPerson->sale_person_id);
                } elseif ($User->type == 301 || $User->type == 302) {
                    $SalesPerson = Electrician::where('user_id', $User->id)->first();
                    $SalesPerson = User::find($SalesPerson->sale_person_id);
                }

                $configrationForNotify = configrationForNotify();
                $params = [];
                $params['from_name'] = $configrationForNotify['from_name'];
                $params['from_email'] = $configrationForNotify['from_email'];
                $params['to_name'] = $configrationForNotify['to_name'];
                $params['user_name'] = $User->first_name . ' ' . $User->last_name;
                $params['subject'] = ' ';
                // $params['subject'] = "Order #" . $GiftProductOrder->id . " dispatched";
                $params['order_id'] = $GiftProductOrder->id;

                $courierService = '';

                if ($GiftProductOrder->courier_service_id != 0) {
                    $DataMaster = DataMaster::select('name')->find($GiftProductOrder->courier_service_id);

                    if ($DataMaster) {
                        $courierService = $DataMaster->name;
                    }
                }

                $params['courier_service_name'] = $courierService;
                $params['track_id'] = $GiftProductOrder->track_id;
                $params['cash'] = $GiftProductOrder->total_cash + $GiftProductOrder->total_cashback;
                $params['bcc_email'] = $User->email;
                if ($SalesPerson) {
                    $params['bcc_email'] = [$SalesPerson->email, 'poonam@whitelion.in'];
                }
                $params['to_email'] = $User->email;

                $GiftProductOrderItem = GiftProductOrderItem::query();
                $GiftProductOrderItem->select('gift_products.name', 'gift_products.image', 'gift_product_order_items.qty', 'gift_product_order_items.total_point_value', 'gift_product_order_items.point_value');
                $GiftProductOrderItem->leftJoin('gift_products', 'gift_products.id', '=', 'gift_product_order_items.gift_product_id');
                $GiftProductOrderItem->where('gift_product_order_id', $GiftProductOrder->id);
                $GiftProductOrderItem->orderBy('gift_product_order_items.id', 'desc');
                $GiftProductOrderItem = $GiftProductOrderItem->get();
                $params['items'] = json_decode(json_encode($GiftProductOrderItem), true);

                // if (Config::get('app.env') == "local") {

                // 	$params['to_email'] = $configrationForNotify['test_email'];
                // 	$params['bcc_email'] = $configrationForNotify['test_email_bcc'];
                // }

                //TEMPLATE 11
                Mail::send('emails.gift_order_dispatched', ['params' => $params], function ($m) use ($params) {
                    $m->from($params['from_email'], $params['from_name']);
                    $m->bcc($params['bcc_email']);
                    $m->to($params['to_email'], $params['to_name'])->subject($params['subject']);
                });
                // } else {

                // 	$response = errorRes("Invalid attached dispatch file");
                // }

                //TEMPLATE ARCHITECT & ELECTRICIAN DISPATCH OF GIFT PRODUCT
                try {
                    $whatsapp_controller = new WhatsappApiContoller();
                    $perameater_request = new Request();

                    
                    $user_type_lable = '';
                    if ($User->type == 201 || $User->type == 202) {
                        //ARCHITECT
                        $user_type_lable = 'Architect';
                        $perameater_request['q_whatsapp_massage_template'] = 'architect_dispatch_gift_order';
                    } elseif ($User->type == 301 || $User->type == 302) {
                        //ELECTRICIAN
                        $user_type_lable = 'Electrician';
                        $perameater_request['q_whatsapp_massage_template'] = 'electrician_dispatch_gift_order';
                    }
                    
                    $perameater_request['q_whatsapp_massage_mobileno'] = $User->phone_number;
                    $perameater_request['q_whatsapp_massage_attechment'] = '';
                    $perameater_request['q_broadcast_name'] = $User->first_name . ' ' . $User->last_name . '-' . $user_type_lable;
                    $perameater_request['q_whatsapp_massage_parameters'] = array(
                        'data[0]' => $User->first_name . ' ' . $User->last_name,
						'data[1]' => $params['track_id'],
						'data[2]' => $params['courier_service_name'],
						'data[3]' => $params['order_id']
                    );
                    
                    $wp_response = $whatsapp_controller->sendTemplateMessage($perameater_request);
                    $response['whatsapp'] = $wp_response;
                } catch (Exception $e) {
                    $response['whatsapp'] = $e->getMessage();
                }
            } else {
                $response = errorRes('Invalid order id');
            }
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function saveBankDetail(Request $request)
    {
        $GiftProductOrder = GiftProductOrder::find($request->order_hidden_id);
        if ($request->order_hidden_type == 1) {
            $GiftProductOrder->bank_detail_account = $request->account_number;
            $GiftProductOrder->bank_detail_ifsc = $request->ifsc_number;
            $GiftProductOrder->payment_mode = $request->order_hidden_type;
        } elseif ($request->order_hidden_type == 2) {
            $GiftProductOrder->bank_detail_upi = $request->upi_id;
            $GiftProductOrder->payment_mode = $request->order_hidden_type;
        }
        $GiftProductOrder->save();

        $response = successRes('Successfully saved Bank Detail');

        return response()->json($response)->header('Content-Type', 'application/json');
    }
}
