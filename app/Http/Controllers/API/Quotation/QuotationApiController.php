<?php

namespace App\Http\Controllers\API\Quotation;

use App\Models\DebugLog;
use App\Models\WlmstItem;
use App\Models\Wlmst_Client;
// use PDF;
// use Dompdf\Dompdf;
use App\Models\WlmstCompany;
use Illuminate\Http\Request;
use App\Models\Wlmst_ItemGroup;
use App\Models\Wlmst_ItemPrice;
// use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Wltrn_Quotation;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\WlmstItemCategory;
use App\Models\WlmstItemSubgroup;
use Illuminate\Support\Facades\DB;
use App\Models\Wlmst_QuotationType;
use App\Http\Controllers\Controller;
use App\Models\Wlmst_NameSuggestion;
use App\Models\Wlmst_QuotationError;
use App\Models\LeadContact;
use App\Models\QuotRequest;
use App\Models\User;
use App\Models\Lead;
use App\Models\ChannelPartner;
use App\Models\LeadSource;
use App\Models\SalePerson;
use App\Models\CityList;
use App\Models\Wltrn_QuotItemdetail;
use Illuminate\Support\Facades\File;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Quotation\QuotationMasterController;
use App\Http\Controllers\CRM\Lead\LeadQuotationController;
use GrahamCampbell\ResultType\Success;
use Illuminate\Http\Response;

// use Illuminate\Http\Request;
class QuotationApiController extends Controller
{
    public function PostSuggestionList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => ['required'],
        ]);

        if ($validator->fails()) {
            $status = 0;
            $msg = 'Please Check Perameater And Value';
            $status_code = 400;
            $data = $validator->errors();
        } else {
            $searchColumns = ['wlmst_name_suggestions.name'];

            $columns = ['wlmst_name_suggestions.id', 'wlmst_name_suggestions.type', 'wlmst_name_suggestions.name'];

            $status = 0;
            $status_code = http_response_code();
            $msg = 'api';
            $data = null;

            try {
                $status = 1;
                $msg = 'Name Suggestion List Success';
                $query = Wlmst_NameSuggestion::query();
                $query->select($columns);
                $query->where('wlmst_name_suggestions.type', $request->type);
                $query->where('wlmst_name_suggestions.isactive', '1');
                // $query->limit(10);

                if (isset($request->q)) {
                    $search_value = $request->q;
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
            } catch (QueryException $ex) {
                $response = [];
                $status = 0;
                $msg = 'Please Contact To Admin';
                $data = $ex;
            }
        }

        $response['status'] = $status;
        $response['status_code'] = $status_code;
        $response['msg'] = $msg;
        $response['data'] = $data;
        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }

    public function PostQuotCompanyList(Request $request)
    {
        // if (getCheckAppVersion($request->app_source, $request->app_version)) {
        $searchColumns = [
            0 => 'wlmst_companies.companyname',
        ];

        $columns = [
            0 => 'wlmst_companies.id',
            1 => 'wlmst_companies.companyname',
            2 => 'wlmst_companies.shortname',
            3 => 'wlmst_companies.isactive',
        ];

        $response = [];
        $status = 0;
        $status_code = http_response_code();
        $msg = 'api';
        $data = null;

        try {
            $response = [];
            $status = 1;
            $msg = 'Quotation Item Company List Success';
            $query = WlmstCompany::query();
            $query->select($columns);
            $query->where('isactive', '1');

            if (isset($request->q)) {
                $search_value = $request->q;
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

            $data = $query->paginate(20);
        } catch (QueryException $ex) {
            $response = [];
            $status = 0;
            $msg = 'Please Contact To Admin';
            $data = $ex;
        }

        $response['status'] = $status;
        $response['status_code'] = $status_code;
        $response['msg'] = $msg;
        $response['data'] = $data;
        // } else {
        // 	$response = quoterrorRes(2, 402, "Please Update Your App");
        // }
        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }

    public function PostQuotCategoryList(Request $request)
    {
        // if (getCheckAppVersion($request->app_source, $request->app_version)) {
        $searchColumns = [
            0 => 'wlmst_item_categories.itemcategoryname',
        ];

        $columns = ['wlmst_item_categories.id', 'wlmst_item_categories.itemcategoryname', 'wlmst_item_categories.itemcategoryname as name', 'wlmst_item_categories.shortname', 'wlmst_item_categories.isactive', 'wlmst_item_categories.display_group'];

        $response = [];
        $status = 0;
        $status_code = http_response_code();
        $msg = 'api';
        $data = null;

        try {
            $response = [];
            $status = 1;
            $msg = 'Quotation Item Category List Success';
            $query = WlmstItemCategory::query();
            $query->select($columns);
            $query->where('isactive', '1');
            $query->where('id', '<>', '13');
            if (isset($request->display_group)) {
                if (isset($request->is_bulkquot) && $request->is_bulkquot == 1) {
                    $query->whereIn('display_group', [$request->display_group, 0]);
                } else {
                    $query->where('display_group', $request->display_group);
                }
            }
            if (isset($request->type)) {
                $query->whereRaw('find_in_set(' . $request->type . ',cat_type)');
            }

            if (isset($request->q)) {
                $search_value = $request->q;
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

            $data = $query->paginate(20);
        } catch (QueryException $ex) {
            $response = [];
            $status = 0;
            $msg = 'Please Contact To Admin';
            $data = $ex;
        }

        $response['status'] = $status;
        $response['status_code'] = $status_code;
        $response['msg'] = $msg;
        $response['data'] = $data;
        // } else {
        // 	$response = quoterrorRes(2, 402, "Please Update Your App");
        // }

        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }

    public function PostQuotGroupList(Request $request)
    {
        // if (getCheckAppVersion($request->app_source, $request->app_version)) {
        DB::enableQueryLog();
        $searchColumns = [
            0 => 'wlmst_item_groups.itemgroupname',
        ];

        $columns = ['wlmst_item_groups.id', 'wlmst_item_groups.itemgroupname', 'wlmst_item_groups.itemgroupname as name', 'wlmst_item_groups.shortname', 'wlmst_item_groups.isactive'];

        $response = [];
        $status = 0;
        $status_code = http_response_code();
        $msg = 'api';
        $data = null;

        try {
            $response = [];
            $status = 1;
            $msg = 'Quotation Item Group List Success';
            $query = Wlmst_ItemGroup::query();
            $query->select($columns);
            $query->where('app_isactive', '1');
            $query->orderBy('sequence');

            if (isset($request->q)) {
                $search_value = $request->q;
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

            $data = $query->paginate(20);
        } catch (QueryException $ex) {
            $response = [];
            $status = 0;
            $msg = 'Please Contact To Admin';
            $data = $ex;
        }

        $response['status'] = $status;
        $response['status_code'] = $status_code;
        $response['msg'] = $msg;
        $response['data'] = $data;
        // $response['query'] = DB::getQueryLog();
        // } else {
        // 	$response = quoterrorRes(2, 402, "Please Update Your App");
        // }

        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }

    public function PostQuotSubQuotGroupList(Request $request)
    {
        // if (getCheckAppVersion($request->app_source, $request->app_version)) {
        DB::enableQueryLog();
        $searchColumns = [
            0 => 'wlmst_item_subgroups.itemsubgroupname',
        ];

        $columns = ['wlmst_item_subgroups.id', 'wlmst_item_subgroups.itemsubgroupname', 'wlmst_item_subgroups.itemsubgroupname as name', 'wlmst_item_subgroups.shortname', 'wlmst_item_subgroups.isactive', 'wlmst_item_subgroups.company_id', 'wlmst_item_subgroups.itemgroup_id', 'wlmst_item_subgroups.image'];

        $response = [];
        $status = 0;
        $status_code = http_response_code();
        $msg = 'api';
        $data = null;

        try {
            $response = [];
            $status = 1;
            $msg = 'Quotation Item SubGroup List Success';
            $query = WlmstItemSubgroup::with([
                'company' => function ($query) {
                    $query->select('id', 'companyname');
                },
                'itemgroup' => function ($query) {
                    $query->select('id', 'itemgroupname');
                },
            ]);
            // if (strpos(strtolower($value->itemsubgroupname), "white") == true) {
            // 	$data_plate[$key]['image'] = "https://whitelion.sgp1.digitaloceanspaces.com/erp.whitelion.in/quotation/item/168440428710446.png";
            // } else {
            // 	$data_plate[$key]['image'] = "https://whitelion.sgp1.digitaloceanspaces.com/erp.whitelion.in/quotation/item/168440423561646.png";
            // }
            $query->select($columns);
            $query->where('wlmst_item_subgroups.isactive', '1');
            if (isset($request->company_id)) {
                // $query->where('wlmst_item_subgroups.company_id', $request->company_id);
                // $query->whereIn('wlmst_item_subgroups.company_id', [$request->company_id]);
                // $query->whereRaw('FIND_IN_SET(' . $request->company_id . ',wlmst_item_subgroups.company_id)');
                $query->whereRaw('find_in_set(' . $request->company_id . ',wlmst_item_subgroups.company_id)');
                // $query->where(DB::raw("find_in_set('$request->company_id', 'wlmst_item_subgroups.company_id')"));
            }
            if (isset($request->itemgroup_id)) {
                $query->where('wlmst_item_subgroups.itemgroup_id', $request->itemgroup_id);
            }

            if (isset($request->q)) {
                $search_value = $request->q;
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

            $data = $query->paginate(20);
        } catch (QueryException $ex) {
            $response = [];
            $status = 0;
            $msg = 'Please Contact To Admin';
            $data = $ex;
        }

        $response['status'] = $status;
        $response['status_code'] = $status_code;
        $response['msg'] = $msg;
        $response['data'] = $data;
        // $response['query'] = DB::getQueryLog();

        // } else {
        // 	$response = quoterrorRes(2, 402, "Please Update Your App");
        // }

        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }

    public function PostQuotItemList(Request $request)
    {
        // if (getCheckAppVersion($request->app_source, $request->app_version)) {
        DB::enableQueryLog();
        $searchColumns = [
            0 => 'wlmst_items.itemname',
            1 => 'wlmst_item_categories.itemcategoryname',
        ];

        $columns = [
            'wlmst_item_prices.id as priceid',
            'wlmst_items.id as itemid',
            'wlmst_items.itemname',
            'wlmst_items.app_display_name',
            'wlmst_items.module',
            'wlmst_items.max_module',
            'wlmst_items.is_special',
            'wlmst_items.additional_remark',
            'wlmst_items.remark',
            'wlmst_items.itemcategory_id',
            'wlmst_item_categories.itemcategoryname',
            'wlmst_item_prices.mrp',
            'wlmst_item_prices.discount',
            'wlmst_item_prices.company_id',
            'wlmst_item_prices.item_type',
            'wlmst_companies.companyname',
            'wlmst_item_prices.itemgroup_id',
            'wlmst_item_groups.itemgroupname',
            'wlmst_item_prices.itemsubgroup_id',
            'wlmst_item_subgroups.itemsubgroupname',
            // 'wlmst_items.image',
            'wlmst_item_prices.image',
            'wlmst_item_prices.code',
            'wlmst_item_prices.code AS product_code',
            'wlmst_items.itemname AS product_code_name',
        ];

        $response = [];
        $status = 0;
        $status_code = http_response_code();
        $msg = 'api';
        $data = null;

        try {
            $response = [];
            $status = 1;
            $msg = 'Quotation Item List Success';

            // $request
            $query = WlmstItem::query();
            $query->select($columns);

            $query->leftJoin('wlmst_item_categories', 'wlmst_items.itemcategory_id', '=', 'wlmst_item_categories.id');
            $query->leftJoin('wlmst_item_prices', 'wlmst_items.id', '=', 'wlmst_item_prices.item_id');
            $query->leftJoin('wlmst_companies', 'wlmst_companies.id', '=', 'wlmst_item_prices.company_id');
            $query->leftJoin('wlmst_item_groups', 'wlmst_item_groups.id', '=', 'wlmst_item_prices.itemgroup_id');
            $query->leftJoin('wlmst_item_subgroups', 'wlmst_item_subgroups.id', '=', 'wlmst_item_prices.itemsubgroup_id');

            $query->where('wlmst_items.isactive', 1);
            $query->where('wlmst_item_prices.isactive', 1);
            $query->orderBy('wlmst_items.app_sequence', 'ASC');

            if (isset($request->quot_id) || isset($request->quotgroup_id) || isset($request->room_no) || isset($request->board_no)) {
                $Quot_Id = $request->quot_id;
                $Quotgroup_Id = $request->quotgroup_id;
                $Room_No = $request->room_no;
                $Board_No = $request->board_no;

                $query->selectRaw('case when wltrn_quot_itemdetails.qty is null then 0 else wltrn_quot_itemdetails.qty end as qty');
                $query->selectRaw('case when wltrn_quot_itemdetails.qty is null then wlmst_item_prices.mrp else wltrn_quot_itemdetails.rate end as mrp');
                $query->selectRaw('case when wltrn_quot_itemdetails.qty is null then 0 else wltrn_quot_itemdetails.net_amount end as net_amount');

                $query->leftJoin('wltrn_quot_itemdetails', function ($query) use ($Quot_Id, $Quotgroup_Id, $Room_No, $Board_No) {
                    $query->where('wltrn_quot_itemdetails.quot_id', '=', $Quot_Id);
                    $query->where('wltrn_quot_itemdetails.quotgroup_id', '=', $Quotgroup_Id);
                    $query->where('wltrn_quot_itemdetails.room_no', '=', $Room_No);
                    $query->where('wltrn_quot_itemdetails.board_no', '=', $Board_No);
                    // $query->on('wltrn_quot_itemdetails.company_id', '=', 'wlmst_item_prices.company_id');
                    // $query->on('wltrn_quot_itemdetails.itemgroup_id', '=', 'wlmst_item_prices.itemgroup_id');
                    // $query->on('wltrn_quot_itemdetails.itemsubgroup_id', '=', 'wlmst_item_prices.itemsubgroup_id');
                    $query->on('wltrn_quot_itemdetails.item_id', '=', 'wlmst_item_prices.item_id');
                });
            } else {
                $query->addSelect(DB::raw("'0' as qty"));
            }

            if (isset($request->range_subgroup)) {
                $Range_Subgroup = explode(',', $request->range_subgroup);
                // $Range_group = explode(',', $request->range_group);
                $query->where(function ($query) use ($Range_Subgroup) {
                    for ($i = 0; $i < count($Range_Subgroup); $i++) {
                        $range_group_id = WlmstItemSubgroup::find($Range_Subgroup[$i])->itemgroup_id;
                        // $range_group_id = $Range_group[$i];
                        $range_subgroup_id = $Range_Subgroup[$i];
                        if ($i == 0) {
                            $query->where(function ($query) use ($range_group_id, $range_subgroup_id) {
                                $query->whereIn('wlmst_item_prices.itemgroup_id', [$range_group_id])->whereIn('wlmst_item_prices.itemsubgroup_id', [$range_subgroup_id]);
                            });
                        } else {
                            $query->orWhere(function ($query) use ($range_group_id, $range_subgroup_id) {
                                $query->whereIn('wlmst_item_prices.itemgroup_id', [$range_group_id])->whereIn('wlmst_item_prices.itemsubgroup_id', [$range_subgroup_id]);
                            });
                        }
                    }
                });
            }

            if (isset($request->company_id)) {
                $query->where('wlmst_item_prices.company_id', $request->company_id);
            }

            if (isset($request->itemgroup_id)) {
                $query->where('wlmst_item_prices.itemgroup_id', $request->itemgroup_id);
            }

            if (isset($request->itemsubgroup_id)) {
                $query->whereIn('wlmst_item_prices.itemsubgroup_id', explode(',', $request->itemsubgroup_id));
            }

            if (isset($request->itemcategory_id)) {
                // $query->where('wlmst_items.itemcategory_id', $request->itemcategory_id);
                $query->whereRaw('find_in_set(' . $request->itemcategory_id . ',wlmst_items.itemcategory_id)');
            }
            if (isset($request->type)) {
                $query->whereRaw("find_in_set('" . $request->type . "',wlmst_item_prices.item_type)");
            }

            if (isset($request->q)) {
                $search_value = $request->q;
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

            if (isset($request->pagination)) {
                $data = $query->get();
            } else {
                $data = $query->paginate(20);
            }
            foreach ($data as $key => $value) {
                if ($value->image == null) {
                    $data[$key]['image'] = 'http://axoneerp.whitelion.in/assets/images/logo.svg';
                } else {
                    $data[$key]['image'] = getSpaceFilePath($value->image);
                }
            }
            // $data = $query->get();
        } catch (QueryException $ex) {
            $response = [];
            $status = 0;
            $msg = $query;
            $data = $ex;
        }

        $response['status'] = $status;
        $response['status_code'] = $status_code;
        $response['msg'] = $msg;
        // $response['query'] = DB::getQueryLog();
        $response['data'] = $data;
        // } else {
        // 	$response = quoterrorRes(2, 402, "Please Update Your App");
        // }

        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }

    public function PostQuotTypeList(Request $request)
    {
        // if (getCheckAppVersion($request->app_source, $request->app_version)) {
        $searchColumns = [
            0 => 'wlmst_quotation_type.name',
        ];

        $columns = [
            0 => 'wlmst_quotation_type.id',
            1 => 'wlmst_quotation_type.name',
        ];

        $response = [];
        $status = 0;
        $status_code = http_response_code();
        $msg = 'api';
        $data = null;

        try {
            $response = [];
            $status = 1;
            $msg = 'Quotation Type List Success';
            $query = Wlmst_QuotationType::query();
            $query->where('isactive', 1);
            $query->select($columns);

            if (isset($request->q)) {
                $search_value = $request->q;
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
        } catch (QueryException $ex) {
            $response = [];
            $status = 0;
            $msg = 'Please Contact To Admin';
            $data = $ex;
        }

        $response['status'] = $status;
        $response['status_code'] = $status_code;
        $response['msg'] = $msg;
        $response['data'] = $data;
        // } else {
        // 	$response = quoterrorRes(2, 402, "Please Update Your App");
        // }
        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }

    public function GetPlatSizeList(Request $request)
    {
        // if (getCheckAppVersion($request->app_source, $request->app_version)) {
        DB::enableQueryLog();
        $searchColumns = [
            0 => 'wlmst_items.itemname',
            1 => 'wlmst_item_categories.itemcategoryname',
        ];

        $columns = [
            'wlmst_item_prices.id as priceid',
            'wlmst_items.id as itemid',
            'wlmst_items.itemname',
            'wlmst_items.app_display_name',
            'wlmst_items.module',
            'wlmst_items.is_special',
            'wlmst_items.additional_remark',
            'wlmst_items.remark',
            'wlmst_items.itemcategory_id',
            'wlmst_item_categories.itemcategoryname',
            'wlmst_item_prices.mrp',
            'wlmst_item_prices.company_id',
            'wlmst_companies.companyname',
            'wlmst_item_prices.itemgroup_id',
            'wlmst_item_prices.discount',
            'wlmst_item_groups.itemgroupname',
            'wlmst_item_prices.itemsubgroup_id',
            'wlmst_item_subgroups.itemsubgroupname',
            // 'wlmst_items.image',
            'wlmst_item_prices.image',
            'wlmst_item_prices.code',
            'wlmst_item_prices.code AS product_code_name',
            'wlmst_items.max_module',
        ];

        $response = [];
        $status = 0;
        $status_code = http_response_code();
        $msg = 'api';
        $data = null;

        try {
            $response = [];
            $status = 1;
            $msg = 'Quotation Item List Success';

            // ------------- PLATE LIST QUERY START -------------
            $query = WlmstItem::query();
            $query->select($columns);

            $query->leftJoin('wlmst_item_categories', 'wlmst_items.itemcategory_id', '=', 'wlmst_item_categories.id');
            $query->leftJoin('wlmst_item_prices', 'wlmst_items.id', '=', 'wlmst_item_prices.item_id');
            $query->leftJoin('wlmst_companies', 'wlmst_companies.id', '=', 'wlmst_item_prices.company_id');
            $query->leftJoin('wlmst_item_groups', 'wlmst_item_groups.id', '=', 'wlmst_item_prices.itemgroup_id');
            $query->leftJoin('wlmst_item_subgroups', 'wlmst_item_subgroups.id', '=', 'wlmst_item_prices.itemsubgroup_id');
            $query->where('wlmst_items.itemcategory_id', '13');
            $query->where('wlmst_items.isactive', 1);
            $query->where('wlmst_item_prices.isactive', 1);
            $query->orderBy('wlmst_items.app_sequence', 'ASC');

            if (isset($request->quot_id) || isset($request->quotgroup_id) || isset($request->room_no) || isset($request->board_no)) {
                $Quot_Id = $request->quot_id;
                $Quotgroup_Id = $request->quotgroup_id;
                $Room_No = $request->room_no;
                $Board_No = $request->board_no;

                $query->selectRaw('case when wltrn_quot_itemdetails.qty is null then 0 else wltrn_quot_itemdetails.qty end as qty');
                $query->selectRaw('case when wltrn_quot_itemdetails.qty is null then wlmst_item_prices.mrp else wltrn_quot_itemdetails.rate end as mrp');
                $query->selectRaw('case when wltrn_quot_itemdetails.qty is null then 0 else wltrn_quot_itemdetails.net_amount end as net_amount');

                $query->leftJoin('wltrn_quot_itemdetails', function ($query) use ($Quot_Id, $Quotgroup_Id, $Room_No, $Board_No) {
                    $query->where('wltrn_quot_itemdetails.quot_id', '=', $Quot_Id);
                    $query->where('wltrn_quot_itemdetails.quotgroup_id', '=', $Quotgroup_Id);
                    $query->where('wltrn_quot_itemdetails.room_no', '=', $Room_No);
                    $query->where('wltrn_quot_itemdetails.board_no', '=', $Board_No);
                    $query->on('wltrn_quot_itemdetails.item_id', '=', 'wlmst_item_prices.item_id');
                });
            } else {
                $query->addSelect(DB::raw("'0' as qty"));
            }

            if (isset($request->type)) {
                $query->whereRaw("find_in_set('" . $request->type . "',wlmst_item_prices.item_type)");
            }

            if (isset($request->range_subgroup)) {
                $Range_Subgroup = explode(',', $request->range_subgroup);
                // $Range_group = explode(',', $request->range_group);
                $query->where(function ($query) use ($Range_Subgroup) {
                    for ($i = 0; $i < count($Range_Subgroup); $i++) {
                        $range_group_id = WlmstItemSubgroup::find($Range_Subgroup[$i])->itemgroup_id;
                        // $range_group_id = $Range_group[$i];
                        $range_subgroup_id = $Range_Subgroup[$i];
                        if ($i == 0) {
                            $query->where(function ($query) use ($range_group_id, $range_subgroup_id) {
                                $query->whereIn('wlmst_item_prices.itemgroup_id', [$range_group_id])->whereIn('wlmst_item_prices.itemsubgroup_id', [$range_subgroup_id]);
                            });
                        } else {
                            $query->orWhere(function ($query) use ($range_group_id, $range_subgroup_id) {
                                $query->whereIn('wlmst_item_prices.itemgroup_id', [$range_group_id])->whereIn('wlmst_item_prices.itemsubgroup_id', [$range_subgroup_id]);
                            });
                        }
                    }
                });
            }

            if (isset($request->company_id)) {
                $query->where('wlmst_item_prices.company_id', $request->company_id);
            }

            if (isset($request->itemsubgroup_id)) {
                $query->whereIn('wlmst_item_prices.itemsubgroup_id', explode(',', $request->itemsubgroup_id));
            }

            if (isset($request->itemcategory_id)) {
                // $query->where('wlmst_items.itemcategory_id', $request->itemcategory_id);
                $query->whereRaw('find_in_set(' . $request->itemcategory_id . ',wlmst_items.itemcategory_id)');
            }

            if (isset($request->q)) {
                $search_value = $request->q;
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
            $query->orderBy('module');

            $data_plate = $query->get();
            foreach ($data_plate as $key => $value) {
                if ($value->itemcategory_id == 13) {
                    if (strpos(strtolower($value->itemsubgroupname), 'white') == true) {
                        $data_plate[$key]['image'] = 'https://whitelion.sgp1.digitaloceanspaces.com/erp.whitelion.in/quotation/item/168440428710446.png';
                    } else {
                        $data_plate[$key]['image'] = 'https://whitelion.sgp1.digitaloceanspaces.com/erp.whitelion.in/quotation/item/168440423561646.png';
                    }
                } else {
                    if ($value->image == null) {
                        $data_cat_1[$key]['image'] = 'http://axoneerp.whitelion.in/assets/images/logo.svg';
                    } else {
                        $data_cat_1[$key]['image'] = getSpaceFilePath($value->image);
                    }
                }
            }
            // ------------- PLATE LIST QUERY END -------------
            // ------------- CATEGORY DISPLAY 1 LIST QUERY START -------------
            $cat_1columns = [
                0 => 'wlmst_item_categories.id',
                1 => 'wlmst_item_categories.itemcategoryname',
                2 => 'wlmst_item_categories.shortname',
                3 => 'wlmst_item_categories.isactive',
                4 => 'wlmst_item_categories.display_group',
            ];
            $cat1query = WlmstItemCategory::query();
            $cat1query->select($cat_1columns);
            $cat1query->where('isactive', '1');
            $cat1query->where('display_group', '1');
            $cat1query->orderBy('wlmst_item_categories.app_sequence', 'ASC');

            $data_cat_1 = $cat1query->get();

            // ------------- CATEGORY DISPLAY 1 LIST QUERY END -------------

            // ------------- CATEGORY DISPLAY 2 LIST QUERY START -------------
            $cat_2columns = [
                0 => 'wlmst_item_categories.id',
                1 => 'wlmst_item_categories.itemcategoryname',
                2 => 'wlmst_item_categories.shortname',
                3 => 'wlmst_item_categories.isactive',
                4 => 'wlmst_item_categories.display_group',
            ];
            $cat2query = WlmstItemCategory::query();
            $cat2query->select($cat_2columns);
            $cat2query->where('isactive', '1');
            $cat2query->where('display_group', '2');
            if (isset($request->type)) {
                $cat2query->whereRaw('find_in_set(? , cat_type)', [$request->type]);
            }
            $cat2query->orderBy('wlmst_item_categories.app_sequence', 'ASC');

            $data_cat_2 = $cat2query->get();
            // ------------- CATEGORY DISPLAY 2 LIST QUERY END -------------

            // ------------- BOARD ADD ON LIST QUERY START -------------

            $board_addon_query = WlmstItem::query();
            $board_addon_query->select($columns);

            $board_addon_query->leftJoin('wlmst_item_categories', 'wlmst_items.itemcategory_id', '=', 'wlmst_item_categories.id');
            $board_addon_query->leftJoin('wlmst_item_prices', 'wlmst_items.id', '=', 'wlmst_item_prices.item_id');
            $board_addon_query->leftJoin('wlmst_companies', 'wlmst_companies.id', '=', 'wlmst_item_prices.company_id');
            $board_addon_query->leftJoin('wlmst_item_groups', 'wlmst_item_groups.id', '=', 'wlmst_item_prices.itemgroup_id');
            $board_addon_query->leftJoin('wlmst_item_subgroups', 'wlmst_item_subgroups.id', '=', 'wlmst_item_prices.itemsubgroup_id');
            $board_addon_query->where('wlmst_items.isactive', 1);
            $board_addon_query->where('wlmst_item_prices.isactive', 1);

            // $board_addon_query->where('wlmst_items.itemcategory_id', '6');
            $board_addon_query->whereRaw("find_in_set('6',wlmst_items.itemcategory_id)");

            if (isset($request->quot_id) || isset($request->quotgroup_id) || isset($request->room_no) || isset($request->board_no)) {
                $Quot_Id = $request->quot_id;
                $Quotgroup_Id = $request->quotgroup_id;
                $Room_No = $request->room_no;
                $Board_No = $request->board_no;

                $board_addon_query->selectRaw('case when wltrn_quot_itemdetails.qty is null then 0 else wltrn_quot_itemdetails.qty end as qty');
                $board_addon_query->selectRaw('case when wltrn_quot_itemdetails.qty is null then wlmst_item_prices.mrp else wltrn_quot_itemdetails.rate end as mrp');
                $board_addon_query->selectRaw('case when wltrn_quot_itemdetails.qty is null then 0 else wltrn_quot_itemdetails.net_amount end as net_amount');

                $board_addon_query->leftJoin('wltrn_quot_itemdetails', function ($board_addon_query) use ($Quot_Id, $Quotgroup_Id, $Room_No, $Board_No) {
                    $board_addon_query->where('wltrn_quot_itemdetails.quot_id', '=', $Quot_Id);
                    $board_addon_query->where('wltrn_quot_itemdetails.quotgroup_id', '=', $Quotgroup_Id);
                    $board_addon_query->where('wltrn_quot_itemdetails.room_no', '=', $Room_No);
                    $board_addon_query->where('wltrn_quot_itemdetails.board_no', '=', $Board_No);
                    // $query->on('wltrn_quot_itemdetails.company_id', '=', 'wlmst_item_prices.company_id');
                    // $query->on('wltrn_quot_itemdetails.itemgroup_id', '=', 'wlmst_item_prices.itemgroup_id');
                    // $query->on('wltrn_quot_itemdetails.itemsubgroup_id', '=', 'wlmst_item_prices.itemsubgroup_id');
                    $board_addon_query->on('wltrn_quot_itemdetails.item_id', '=', 'wlmst_item_prices.item_id');
                });
            } else {
                $board_addon_query->addSelect(DB::raw("'0' as qty"));
            }

            $data_board_addon = $board_addon_query->get();
            foreach ($data_board_addon as $key => $value) {
                if ($value->image == null) {
                    $data_board_addon[$key]['image'] = 'http://axoneerp.whitelion.in/assets/images/logo.svg';
                } else {
                    $data_board_addon[$key]['image'] = getSpaceFilePath($value->image);
                }
            }
            // ------------- BOARD ADD ON LIST QUERY END -------------

            // ------------- GROUP1 DATA LIST QUERY START -------------
            $perameater_request = new Request();
            if (isset($request->quot_id) || isset($request->quotgroup_id) || isset($request->room_no) || isset($request->board_no)) {
                $Quot_Id = $request->quot_id;
                $Quotgroup_Id = $request->quotgroup_id;
                $Room_No = $request->room_no;
                $Board_No = $request->board_no;
                $perameater_request['quot_id'] = $Quot_Id;
                $perameater_request['quotgroup_id'] = $Quotgroup_Id;
                $perameater_request['room_no'] = $Room_No;
                $perameater_request['board_no'] = $Board_No;
            }
            if (isset($request->range_subgroup)) {
                $perameater_request['range_subgroup'] = $request->range_subgroup;
            }

            $perameater_request['itemcategory_id'] = $data_cat_1[0]['id'];
            $perameater_request['item_type'] = $request->type;
            $perameater_request['type'] = $request->type;
            $perameater_request['app_source'] = $request->app_source;
            $perameater_request['app_version'] = $request->app_version;
            $perameater_request['pagination'] = 1;
            $data_cat_1_list = $this->PostQuotItemList($perameater_request);

            $perameater_request['itemcategory_id'] = $data_cat_2[0]['id'];
            if ($request->type == 'QUARTZ') {
                if (isset($request->itemsubgroup_id)) {
                    $perameater_request['itemsubgroup_id'] = $request->itemsubgroup_id;
                } else {
                    $perameater_quotquartz_request = new Request();
                    $perameater_quotquartz_request['app_source'] = $request->app_source;
                    $perameater_quotquartz_request['app_version'] = $request->app_version;
                    $perameater_request['itemsubgroup_id'] = $this->PostQuotQuartzColour($perameater_quotquartz_request)->original['data'][0]['itemsubgroup_id'];
                }
            }
            $data_cat_2_list = $this->PostQuotItemList($perameater_request);

            // ------------- GROUP1 DATA LIST QUERY END -------------

            $response['data'] = $data_plate;
            $response['data1'] = $data_cat_1;
            $response['data2'] = $data_cat_2;
            $response['data3'] = $data_cat_1_list->original['data'];
            $response['data4'] = $data_cat_2_list->original['data'];
            $response['data5'] = $data_board_addon;
        } catch (QueryException $ex) {
            $response = [];
            $status = 0;
            $msg = $query;
            $response['data'] = $ex;
        }

        $response['status'] = $status;
        $response['status_code'] = $status_code;
        $response['msg'] = $msg;
        // $response['query'] = DB::getQueryLog();

        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }

    public function PostQuotClientSave(Request $request)
    {
        // if (getCheckAppVersion($request->app_source, $request->app_version)) {
        $res_status = '';
        $res_status_code = '';
        $res_message = '';
        $res_data = '';
        $response = [];

        $validator = Validator::make($request->all(), [
            'name' => ['required'],
            'mobile' => ['required'],
            'address' => ['required'],
        ]);

        if ($validator->fails()) {
            $res_status = 0;
            $res_message = 'Please Check Perameater And Value';
            $res_status_code = 400;
            $res_data = $validator->errors();
        } else {
            $alreadyName = Wlmst_Client::query();

            if ($request->client_id != 0) {
                $alreadyName->where('name', $request->name);
                $alreadyName->where('id', '!=', $request->client_id);
            } else {
                $alreadyName->where('name', $request->name);
            }

            $alreadyName = $alreadyName->first();

            $alreadyMobile = Wlmst_Client::query();

            if ($request->client_id != 0) {
                $alreadyMobile->where('mobile', $request->mobile);
                $alreadyMobile->where('id', '!=', $request->client_id);
            } else {
                $alreadyMobile->where('mobile', $request->mobile);
            }

            $alreadyMobile = $alreadyMobile->first();

            if ($alreadyName) {
                $res_status = 0;
                $res_status_code = 400;
                $res_message = 'already name exits, Try with another name';
            } elseif ($alreadyMobile) {
                $res_status = 0;
                $res_status_code = 400;
                $res_message = 'already mobile exits, Try with another mobile';
            } else {
                if ($request->client_id != 0) {
                    $ClientMaster = Wlmst_Client::find($request->client_id);
                    $ClientMaster->updateby = Auth::user()->id; //Live
                    $ClientMaster->updateip = $request->ip();
                } else {
                    $ClientMaster = new Wlmst_Client();
                    $ClientMaster->entryby = Auth::user()->id; //Live
                    $ClientMaster->entryip = $request->ip();
                }

                $ClientMaster->name = $request->name;
                $ClientMaster->email = $request->email;
                $ClientMaster->mobile = $request->mobile;
                $ClientMaster->address = $request->address;
                $ClientMaster->remark = '0';

                $ClientMaster->save();
                if ($ClientMaster) {
                    if ($request->client_id != 0) {
                        $res_status = 1;
                        $res_status_code = 200;
                        $res_message = 'Successfully saved client';

                        $DebugLog = new DebugLog();
                        $DebugLog->user_id = Auth::user()->id;
                        $DebugLog->name = 'quot-client-master-edit';
                        $DebugLog->description = 'client master #' . $ClientMaster->id . '(' . $ClientMaster->name . ')' . ' has been updated';
                        $DebugLog->save();
                    } else {
                        $res_status = 1;
                        $res_status_code = 200;
                        $res_message = 'Successfully added client';

                        $DebugLog = new DebugLog();
                        $DebugLog->user_id = Auth::user()->id;
                        $DebugLog->name = 'quot-client-master-add';
                        $DebugLog->description = 'client master #' . $ClientMaster->id . '(' . $ClientMaster->name . ') has been added';
                        $DebugLog->save();
                    }
                }
            }
        }

        $response['status'] = $res_status;
        $response['status_code'] = $res_status_code;
        $response['msg'] = $res_message;
        $response['data'] = $res_data;
        // } else {
        // 	$response = quoterrorRes(2, 402, "Please Update Your App");
        // }

        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }

    public function PostQuotClientList(Request $request)
    {
        // if (getCheckAppVersion($request->app_source, $request->app_version)) {
        $searchColumns = [
            0 => 'wlmst_client.name',
            1 => 'wlmst_client.mobile',
        ];

        $columns = [
            0 => 'wlmst_client.id',
            1 => 'wlmst_client.name',
            2 => 'wlmst_client.email',
            3 => 'wlmst_client.mobile',
            4 => 'wlmst_client.address',
        ];

        $response = [];
        $status = 0;
        $status_code = http_response_code();
        $msg = 'api';
        $data = null;

        try {
            $response = [];
            $status = 1;
            $status_code = 200;
            $msg = 'Client List Success';
            $query = Wlmst_Client::query();
            $query->select($columns);
            $query->where('isactive', '1');
            $query->limit(15);

            if (isset($request->id)) {
                $query->where('id', $request->id);
            }

            if (isset($request->q)) {
                $search_value = $request->q;
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

            // $data = $query->paginate(20);
            $data = $query->get();
        } catch (QueryException $ex) {
            $response = [];
            $status = 0;
            $status_code = 400;
            $msg = 'Please Contact To Admin';
            $data = $ex;
        }

        $response['status'] = $status;
        $response['status_code'] = $status_code;
        $response['msg'] = $msg;
        $response['data'] = $data;
        // } else {
        // 	$response = quoterrorRes(2, 402, "Please Update Your App");
        // }

        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }

    public function PostQuotationList(Request $request)
    {
        // if (getCheckAppVersion($request->app_source, $request->app_version)) {
        DB::enableQueryLog();

        // if (getCheckAppVersion($request->app_source, $request->app_version)) {

        $searchColumns = [
            0 => 'wltrn_quotation.customer_name',
            1 => 'wltrn_quotation.customer_contact_no',
            2 => 'CONCAT(leads.first_name," ",leads.last_name)',
        ];

        $columns = [
            'wltrn_quotation.id',
            'wltrn_quotation.quotgroup_id',
            'wltrn_quotation.yy',
            'wltrn_quotation.mm',
            'wltrn_quotation.quottype_id',
            'quot_type.name AS quot_type',
            'wltrn_quotation.quotno',
            'wltrn_quotation.quot_no_str',
            'wltrn_quotation.customer_id',
            'wltrn_quotation.customer_contact_no',

            'wltrn_quotation.architech_id',
            'arc_user.first_name AS architect_first_name',
            'arc_user.phone_number AS architect_mobile',
            'wltrn_quotation.electrician_id',
            'ele_user.first_name AS electrician_first_name',
            'ele_user.phone_number AS electrician_mobile',
            'wltrn_quotation.salesexecutive_id',
            'sales_user.first_name AS sales_first_name',
            'sales_user.phone_number AS sales_mobile',
            'wltrn_quotation.channelpartner_id',
            'chann_user.first_name AS channelpartener_first_name',
            'chann_user.phone_number AS channelpartener_mobile',

            'wltrn_quotation.inquiry_id',
            'wltrn_quotation.site_name',
            'wltrn_quotation.siteaddress',

            'wltrn_quotation.site_state_id',
            'state_list.name AS state',
            'wltrn_quotation.site_country_id',
            'country_list.name AS country',
            'wltrn_quotation.site_city_id',
            'city_list.name AS city',

            'wltrn_quotation.status',
            'wltrn_quotation.additional_remark',
            'wltrn_quotation.quot_date',
            'wltrn_quotation.quotationsource',
            'wltrn_quotation.default_range',
            'wltrn_quotation.created_at',
            'wltrn_quotation.entryby',
        ];

        $response = [];
        $status = 0;
        $status_code = http_response_code();
        $msg = 'api';
        $data = null;

        try {
            $response = [];
            $status = 1;
            $msg = 'Quotation List Success';
            $query = Wltrn_Quotation::query();
            $query->select($columns);
            $query->selectRaw('case when wltrn_quotation.inquiry_id != 0 then CONCAT(leads.first_name," ",leads.last_name) else wltrn_quotation.customer_name end as customer_name');
            $query->leftJoin('users AS arc_user', 'arc_user.id', '=', 'wltrn_quotation.architech_id');
            $query->leftJoin('users AS ele_user', 'ele_user.id', '=', 'wltrn_quotation.electrician_id');
            $query->leftJoin('users AS sales_user', 'sales_user.id', '=', 'wltrn_quotation.salesexecutive_id');
            $query->leftJoin('users AS chann_user', 'chann_user.id', '=', 'wltrn_quotation.channelpartner_id');
            $query->leftJoin('state_list', 'state_list.id', '=', 'wltrn_quotation.site_state_id');
            $query->leftJoin('city_list', 'city_list.id', '=', 'wltrn_quotation.site_country_id');
            $query->leftJoin('country_list', 'country_list.id', '=', 'wltrn_quotation.site_country_id');
            $query->leftJoin('leads', 'leads.id', '=', 'wltrn_quotation.inquiry_id');
            $query->leftJoin('wlmst_quotation_type as quot_type', 'quot_type.id', '=', 'wltrn_quotation.quottype_id');
            $query->where('quot_no_str', '1.1');

            if (isAdminOrCompanyAdmin() != 1) {
                //Live
                $query->where('wltrn_quotation.entryby', Auth::user()->id);
            }

            $query->addSelect(DB::raw("'0' as range_company"));

            if (isset($request->q)) {
                $search_value = $request->q;
                $query->where(function ($query) use ($search_value, $searchColumns) {
                    for ($i = 0; $i < count($searchColumns); $i++) {
                        if ($i == 0) {
                            // $query->where($searchColumns[$i], 'like', "%" . $search_value . "%");
                            $query->WhereRaw($searchColumns[$i] . ' like ? ', ['%' . $search_value . '%']);
                        } else {
                            // $query->orWhere($searchColumns[$i], 'like', "%" . $search_value . "%");
                            $query->orWhereRaw($searchColumns[$i] . ' like ? ', ['%' . $search_value . '%']);
                        }
                    }
                });
            }
            $quotlist = $query->get();
            $quot_array = [];
            foreach ($quotlist as $key => $value) {
                $quot_f_array = [];

                if ($quotlist[$key]['default_range'] != '' || $quotlist[$key]['default_range'] != null) {
                    $Range_Subgroup = explode(',', $quotlist[$key]['default_range']);
                    // $Range_Subgroup = $quotlist[$key]['default_range'];
                    $range_group = '';
                    $range_company = '';
                    for ($i = 0; $i < count($Range_Subgroup); $i++) {
                        $range_group .= WlmstItemSubgroup::find($Range_Subgroup[$i])->itemgroup_id . ',';
                        $range_company .= WlmstItemSubgroup::find($Range_Subgroup[$i])->company_id . ',';
                    }
                    $range_group_f = substr($range_group, 0, -1);
                    // $range_company_f = substr($range_company,0,-1);
                    $range_company_f = explode(',', $range_company)[0];
                } else {
                    $range_group_f = '';
                    $range_company_f = '';
                }

                $quot_f_array = $value;
                // $quot_f_array['created_at'] = convertDateTime($value['created_at']);
                $quot_f_array['created_at1'] = convertOrderDateTime($value['created_at'], 'date') . ' at ' . convertOrderDateTime($value['created_at'], 'time');
                $quot_f_array['status_lable'] = $value['status'] == 1 ? 'Sent Request' : getQuotationMasterStatusLableText($value['status']);
                $quot_f_array['range_group'] = $range_group_f;
                $quot_f_array['range_company'] = $range_company_f;
                array_push($quot_array, $quot_f_array);
            }

            // $data = $query->paginate(20);
            $data = $quot_array;
        } catch (QueryException $ex) {
            $response = [];
            $status = 0;
            $msg = 'Please Contact To Admin';
            $data = $ex;
        }

        $response['status'] = $status;
        $response['status_code'] = $status_code;
        $response['msg'] = $msg;
        $response['data'] = $data;
        // $response['query'] = DB::getQueryLog();
        // } else {
        // 	$response = quoterrorRes(2, 402, "Please Update Your App");
        // }

        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }

    public function PostQuotationhistoryList(Request $request)
    {
        // if (getCheckAppVersion($request->app_source, $request->app_version)) {
        DB::enableQueryLog();
        $searchColumns = ['wltrn_quotation.customer_name', 'wltrn_quotation.customer_contact_no', 'CONCAT(leads.first_name," ",leads.last_name)'];

        $columns = [
            'wltrn_quotation.id',
            'wltrn_quotation.quotgroup_id',
            'wltrn_quotation.yy',
            'wltrn_quotation.mm',
            'wltrn_quotation.quottype_id',
            'quot_type.name AS quot_type',
            'wltrn_quotation.quotno',
            'wltrn_quotation.quot_no_str',
            'wltrn_quotation.customer_id',
            'wltrn_quotation.customer_contact_no',

            'wltrn_quotation.architech_id',
            'arc_user.first_name AS architect_first_name',
            'arc_user.phone_number AS architect_mobile',
            'wltrn_quotation.electrician_id',
            'ele_user.first_name AS electrician_first_name',
            'ele_user.phone_number AS electrician_mobile',
            'wltrn_quotation.salesexecutive_id',
            'sales_user.first_name AS sales_first_name',
            'sales_user.phone_number AS sales_mobile',
            'wltrn_quotation.channelpartner_id',
            'chann_user.first_name AS channelpartener_first_name',
            'chann_user.phone_number AS channelpartener_mobile',

            'wltrn_quotation.inquiry_id',
            'wltrn_quotation.site_name',
            'wltrn_quotation.siteaddress',

            'wltrn_quotation.site_state_id',
            'state_list.name AS state',
            'wltrn_quotation.site_country_id',
            'country_list.name AS country',
            'wltrn_quotation.site_city_id',
            'city_list.name AS city',

            'wltrn_quotation.status',
            'wltrn_quotation.additional_remark',
            'wltrn_quotation.quot_date',
            'wltrn_quotation.quotationsource',
            'wltrn_quotation.default_range',

            'source.first_name AS source_first_name',
            'wltrn_quotation.quotationsource AS source',

            'wltrn_quotation.created_at',
            'wltrn_quotation.entryby',
        ];

        $response = [];
        $status = 0;
        $status_code = http_response_code();
        $msg = 'api';
        $data = null;

        try {
            $response = [];
            $status = 1;
            $msg = 'Quotation List Success';
            $query = Wltrn_Quotation::query();
            $query->select($columns);
            $query->selectRaw('case when wltrn_quotation.inquiry_id != 0 then CONCAT(leads.first_name," ",leads.last_name) else wltrn_quotation.customer_name end as customer_name');
            $query->leftJoin('users AS arc_user', 'arc_user.id', '=', 'wltrn_quotation.architech_id');
            $query->leftJoin('users AS ele_user', 'ele_user.id', '=', 'wltrn_quotation.electrician_id');
            $query->leftJoin('users AS sales_user', 'sales_user.id', '=', 'wltrn_quotation.salesexecutive_id');
            $query->leftJoin('users AS source', 'source.id', '=', 'wltrn_quotation.quotationsource');
            $query->leftJoin('users AS chann_user', 'chann_user.id', '=', 'wltrn_quotation.channelpartner_id');
            $query->leftJoin('state_list', 'state_list.id', '=', 'wltrn_quotation.site_state_id');
            $query->leftJoin('city_list', 'city_list.id', '=', 'wltrn_quotation.site_country_id');
            $query->leftJoin('country_list', 'country_list.id', '=', 'wltrn_quotation.site_country_id');
            $query->leftJoin('leads', 'leads.id', '=', 'wltrn_quotation.inquiry_id');
            $query->leftJoin('wlmst_quotation_type as quot_type', 'quot_type.id', '=', 'wltrn_quotation.quottype_id');
            if ($request->quotgroup_id != 0) {
                $query->where('wltrn_quotation.quotgroup_id', $request->quotgroup_id);
            }

            if (isset($request->q)) {
                $search_value = $request->q;
                $query->where(function ($query) use ($search_value, $searchColumns) {
                    for ($i = 0; $i < count($searchColumns); $i++) {
                        if ($i == 0) {
                            // $query->where($searchColumns[$i], 'like', "%" . $search_value . "%");
                            $query->WhereRaw($searchColumns[$i] . ' like ? ', ['%' . $search_value . '%']);
                        } else {
                            // $query->orWhere($searchColumns[$i], 'like', "%" . $search_value . "%");
                            $query->orWhereRaw($searchColumns[$i] . ' like ? ', ['%' . $search_value . '%']);
                        }
                    }
                });
            }

            $data_quotation = $query->get();

            $data_response = [];
            foreach ($data_quotation as $key => $value) {
                $quot_f_array = [];

                if ($value->default_range != '' || $value->default_range != null) {
                    $Range_Subgroup = explode(',', $value->default_range);
                    foreach ($Range_Subgroup as $key => $subgroup_value) {
                        $range_subgroup_detail = WlmstItemSubgroup::find($subgroup_value);
                        $range_group_new[$subgroup_value] = $range_subgroup_detail;
                        $range_group = [];
                        array_push($range_group, $range_group_new);
                    }
                    $range_group_f = $range_group[0];
                    // $range_group_f = array_map($range_group);
                } else {
                    // $range_group_f = 0;
                    $range_group_f = json_decode('""');
                }
                $quot_f_array = $value;
                $quot_f_array['created_at1'] = convertOrderDateTime($value['created_at'], 'date') . ' at ' . convertOrderDateTime($value['created_at'], 'time');
                $quot_f_array['status_lable'] = $value['status'] == 1 ? 'Sent Request' : getQuotationMasterStatusLableText($value['status']);
                $quot_f_array['default_range_value'] = $range_group_f;

                array_push($data_response, $quot_f_array);
            }
            $data = $data_response;
        } catch (QueryException $ex) {
            $response = [];
            $status = 0;
            $msg = 'Please Contact To Admin';
            $data = $ex;
        }

        $response['status'] = $status;
        $response['status_code'] = $status_code;
        $response['msg'] = $msg;
        $response['data'] = $data;
        // $response['query'] = DB::getQueryLog();
        // } else {
        // 	$response = quoterrorRes(2, 402, "Please Update Your App");
        // }

        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }

    public function PostQuotationhistoryListLeadWise(Request $request)
    {
        // if (getCheckAppVersion($request->app_source, $request->app_version)) {
        DB::enableQueryLog();
        $searchColumns = ['wltrn_quotation.customer_name', 'wltrn_quotation.customer_contact_no', 'CONCAT(leads.first_name," ",leads.last_name)'];

        $columns = [
            'wltrn_quotation.id',
            'wltrn_quotation.quotgroup_id',
            'wltrn_quotation.yy',
            'wltrn_quotation.mm',
            'wltrn_quotation.quottype_id',
            'quot_type.name AS quot_type',
            'wltrn_quotation.quotno',
            'wltrn_quotation.quot_no_str',
            'wltrn_quotation.customer_id',
            'wltrn_quotation.customer_contact_no',

            'wltrn_quotation.architech_id',
            'arc_user.first_name AS architect_first_name',
            'arc_user.phone_number AS architect_mobile',
            'wltrn_quotation.electrician_id',
            'ele_user.first_name AS electrician_first_name',
            'ele_user.phone_number AS electrician_mobile',
            'wltrn_quotation.salesexecutive_id',
            'sales_user.first_name AS sales_first_name',
            'sales_user.phone_number AS sales_mobile',
            'wltrn_quotation.channelpartner_id',
            'chann_user.first_name AS channelpartener_first_name',
            'chann_user.phone_number AS channelpartener_mobile',

            'wltrn_quotation.inquiry_id',
            'wltrn_quotation.site_name',
            'wltrn_quotation.siteaddress',

            'wltrn_quotation.site_state_id',
            'state_list.name AS state',
            'wltrn_quotation.site_country_id',
            'country_list.name AS country',
            'wltrn_quotation.site_city_id',
            'city_list.name AS city',

            'wltrn_quotation.status',
            'wltrn_quotation.additional_remark',
            'wltrn_quotation.quot_date',
            'wltrn_quotation.quotationsource',
            'wltrn_quotation.default_range',

            'source.first_name AS source_first_name',
            'wltrn_quotation.quotationsource AS source',

            'wltrn_quotation.created_at',
            'wltrn_quotation.entryby',

            'wltrn_quotation.isfinal',
            'wltrn_quotation.quot_whitelion_amount',
            'wltrn_quotation.quot_billing_amount',
            'wltrn_quotation.quot_other_amount',
            'wltrn_quotation.quot_total_amount',

            'leads.id as lead_id',
            'leads.first_name as lead_first_name',
            'leads.last_name as lead_last_name',
            'leads.is_deal as lead_is_deal',
        ];

        $response = [];
        $status = 0;
        $status_code = http_response_code();
        $msg = 'api';
        $data = null;

        try {
            $response = [];
            $status = 1;
            $msg = 'Quotation List Success';
            $query = Wltrn_Quotation::query();
            $query->select($columns);
            $query->selectRaw('case when wltrn_quotation.inquiry_id != 0 then CONCAT(leads.first_name," ",leads.last_name) else wltrn_quotation.customer_name end as customer_name');
            $query->leftJoin('users AS arc_user', 'arc_user.id', '=', 'wltrn_quotation.architech_id');
            $query->leftJoin('users AS ele_user', 'ele_user.id', '=', 'wltrn_quotation.electrician_id');
            $query->leftJoin('users AS sales_user', 'sales_user.id', '=', 'wltrn_quotation.salesexecutive_id');
            $query->leftJoin('users AS source', 'source.id', '=', 'wltrn_quotation.quotationsource');
            $query->leftJoin('users AS chann_user', 'chann_user.id', '=', 'wltrn_quotation.channelpartner_id');
            $query->leftJoin('state_list', 'state_list.id', '=', 'wltrn_quotation.site_state_id');
            $query->leftJoin('leads', 'leads.id', '=', 'wltrn_quotation.inquiry_id');
            $query->leftJoin('city_list', 'city_list.id', '=', 'wltrn_quotation.site_country_id');
            $query->leftJoin('country_list', 'country_list.id', '=', 'wltrn_quotation.site_country_id');
            $query->leftJoin('wlmst_quotation_type as quot_type', 'quot_type.id', '=', 'wltrn_quotation.quottype_id');
            if ($request->inquiry_id != 0) {
                $query->where('wltrn_quotation.inquiry_id', $request->inquiry_id);
            }

            if (isset($request->q)) {
                $search_value = $request->q;
                $query->where(function ($query) use ($search_value, $searchColumns) {
                    for ($i = 0; $i < count($searchColumns); $i++) {
                        if ($i == 0) {
                            $query->WhereRaw($searchColumns[$i] . ' like ? ', ['%' . $search_value . '%']);
                        } else {
                            $query->orWhereRaw($searchColumns[$i] . ' like ? ', ['%' . $search_value . '%']);
                        }
                    }
                });
            }

            $data_quotation = $query->get();

            $data_response = [];
            foreach ($data_quotation as $mainkey => $value) {
                $quot_f_array = array();
                $range_group = array();

                if ($value['default_range'] != '' || $value['default_range'] != null) {
                    $Range_Subgroup = explode(',', $value['default_range']);
                    $range_group_detail_new = [];
                    foreach ($Range_Subgroup as $sukey => $subgroup_value) {
                        $range_group_detail_new[$subgroup_value] = WlmstItemSubgroup::find($subgroup_value);
                    }
                    $range_group = $range_group_detail_new;
                } else {
                    $range_group = json_decode('""');
                }
                $quot_f_array = $value;
                $quot_f_array['created_at1'] = convertOrderDateTime($value['created_at'], 'date') . ' at ' . convertOrderDateTime($value['created_at'], 'time');
                $quot_f_array['status_lable'] = $value['status'] == 1 ? 'Sent Request' : getQuotationMasterStatusLableText($value['status']);
                $quot_f_array['default_range_value'] = $range_group;

                array_push($data_response, $quot_f_array);
            }
            $data = $data_response;
        } catch (QueryException $ex) {
            $response = [];
            $status = 0;
            $msg = 'Please Contact To Admin';
            $data = $ex;
        }

        $response['status'] = $status;
        $response['status_code'] = $status_code;
        $response['msg'] = $msg;
        $response['data'] = $data;
        // $response['query'] = DB::getQueryLog();
        // } else {
        // 	$response = quoterrorRes(2, 402, "Please Update Your App");
        // }

        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }

    public function PostQuotBasicDetaiSave(Request $request)
    {
        // if (getCheckAppVersion($request->app_source, $request->app_version)) {
        DB::enableQueryLog();
        $res_status = '';
        $res_status_code = '';
        $res_message = '';
        $res_data = '';
        $response = [];

        $validator = Validator::make($request->all(), [
            'quot_id' => ['required'],
            'quotgroup_id' => ['required'],
            'siteaddress' => ['required'],
            'site_country_id' => ['required'],
            'site_state_id' => ['required'],
            'site_city_id' => ['required'],
            'quottype_id' => ['required'],
        ]);

        if ($validator->fails()) {
            $res_status = 0;
            $res_message = 'Please Check Perameater And Value';
            $res_status_code = 400;
            $res_data = $validator->errors();
        } else {
            $BoardSaveLog = [];
            $BoardSaveLog['quot_id'] = 0;
            $BoardSaveLog['quotgroup_id'] = 0;
            $BoardSaveLog['room_no'] = 0;
            $BoardSaveLog['board_no'] = 0;
            $BoardSaveLog['description'] = json_encode($request->all());
            $BoardSaveLog['source'] = $request->app_source;
            $BoardSaveLog['entryip'] = $request->ip();
            saveBoardSaveLog($BoardSaveLog);

            $isAvailableInquiryId = 0;
            if (isset($request->inquiry_id) && $request->inquiry_id != null) {
                $isAvailableInquiryId = $request->inquiry_id;
            }

            if ($request->quot_id != 0) {
                $QuotMaster = Wltrn_Quotation::find($request->quot_id);
                $QuotMaster->updateby = Auth::user()->id; //Live
                $QuotMaster->updateip = $request->ip();
            } else {
                $QuotMaster = new Wltrn_Quotation();
                $QuotMaster->entryby = Auth::user()->id; //Live
                $QuotMaster->entryip = $request->ip();

                $isQuotation = Wltrn_Quotation::query();
                $isQuotation->where('wltrn_quotation.inquiry_id', $isAvailableInquiryId);
                $isQuotation->orderBy('wltrn_quotation.id', 'desc');
                $isQuotation = $isQuotation->first();
                if ($isQuotation) {
                    $new_quot_no_str = Wltrn_Quotation::selectRaw('max(wltrn_quotation.quot_no_str + 1) as newversion')
                        ->where('quotgroup_id', $isQuotation->quotgroup_id)
                        ->first();
                    // $new_quot_no_str = Wltrn_Quotation::selectRaw('max(wltrn_quotation.quot_no_str + 0.1) as newversion')->where('quotgroup_id', $isQuotation->quotgroup_id)->first();
                    $QuotMaster->quotgroup_id = $isQuotation->quotgroup_id;
                    $QuotMaster->yy = substr(date('Y'), -2);
                    $QuotMaster->mm = date('m');
                    $QuotMaster->quotno = $isQuotation->quotno;
                    [$major, $minor] = explode('.', $new_quot_no_str->newversion);
                    $QuotMaster->quot_no_str = $major . '.01';
                    // $QuotMaster->quot_no_str = $new_quot_no_str->newversion;
                    $QuotMaster->quot_date = date('Y-m-d');
                } else {
                    $QuotMaster->quotgroup_id = Wltrn_Quotation::max('quotgroup_id') + 1;
                    $QuotMaster->yy = substr(date('Y'), -2);
                    $QuotMaster->mm = date('m');
                    $QuotMaster->quotno = Wltrn_Quotation::max('quotno') + 1;
                    $QuotMaster->quot_no_str = '1.01';
                    $QuotMaster->quot_date = date('Y-m-d');
                }
            }

            $QuotMaster->quottype_id = $request->quottype_id;
            $CustomerDetail = Lead::find($isAvailableInquiryId);
            $QuotMaster->customer_id = $request->customer_id;
            if($CustomerDetail){
                $QuotMaster->customer_name = $CustomerDetail->first_name .' '.$CustomerDetail->last_name;
                $QuotMaster->customer_contact_no = substr($CustomerDetail->phone_number, -10);
            }else{
                $QuotMaster->customer_name = '';
                $QuotMaster->customer_contact_no = 00;
            }

            $QuotMaster->architech_id = $request->architech_id;
            $QuotMaster->electrician_id = $request->electrician_id;
            if (isSalePerson() == 1) {
                //Live
                $QuotMaster->salesexecutive_id = Auth::user()->id;
            } else {
                $QuotMaster->salesexecutive_id = $request->salesexecutive_id;
            }
            $QuotMaster->channelpartner_id = $request->channelpartner_id;

            $QuotMaster->other_name = $request->other_name;
            $QuotMaster->other_mobile_no = $request->other_mobile_no;

            $QuotMaster->site_name = '-';
            $QuotMaster->siteaddress = $request->siteaddress;

            $QuotMaster->site_city_id = $request->site_city_id;
            $city_data = CityList::find($request->site_city_id);
            $QuotMaster->site_country_id = $city_data->country_id;
            $QuotMaster->site_state_id = $city_data->state_id;

            $QuotMaster->site_pincode = $request->site_pincode;

            $QuotMaster->inquiry_id = $isAvailableInquiryId;

            // $QuotMaster->inquiry_id = $request->inquiry_id;
            // $QuotMaster->inquiry_id = 0;
            $QuotMaster->additional_remark = $request->additional_remark;

            $QuotMaster->quotationsource = $request->quotationsource;

            $QuotMaster->save();
            if ($QuotMaster) {
                $columns = [
                    'wltrn_quotation.id',
                    'wltrn_quotation.quotgroup_id',
                    'wltrn_quotation.yy',
                    'wltrn_quotation.mm',
                    'wltrn_quotation.quottype_id',
                    'quot_type.name AS quot_type',
                    'wltrn_quotation.quotno',
                    'wltrn_quotation.quot_no_str',
                    'wltrn_quotation.customer_id',
                    'wltrn_quotation.customer_name',
                    'wltrn_quotation.customer_contact_no',

                    'wltrn_quotation.architech_id',
                    'arc_user.first_name AS architect_first_name',
                    'arc_user.phone_number AS architect_mobile',
                    'wltrn_quotation.electrician_id',
                    'ele_user.first_name AS electrician_first_name',
                    'ele_user.phone_number AS electrician_mobile',
                    'wltrn_quotation.salesexecutive_id',
                    'sales_user.first_name AS sales_first_name',
                    'sales_user.phone_number AS sales_mobile',
                    'wltrn_quotation.channelpartner_id',
                    'chann_user.first_name AS channelpartener_first_name',
                    'chann_user.phone_number AS channelpartener_mobile',

                    'wltrn_quotation.inquiry_id',
                    'wltrn_quotation.site_name',
                    'wltrn_quotation.siteaddress',

                    'wltrn_quotation.site_state_id',
                    'state_list.name AS state',
                    'wltrn_quotation.site_country_id',
                    'country_list.name AS country',
                    'wltrn_quotation.site_city_id',
                    'city_list.name AS city',
                    'wltrn_quotation.site_pincode AS pincode',

                    'wltrn_quotation.additional_remark',
                    'wltrn_quotation.quot_date',

                    'source.first_name AS source_first_name',
                    'wltrn_quotation.quotationsource AS source',

                    'wltrn_quotation.default_range',
                    'wltrn_quotation.created_at',
                    'wltrn_quotation.entryby',
                ];
                $query_quot = Wltrn_Quotation::query();
                $query_quot->select($columns);
                $query_quot->leftJoin('users AS arc_user', 'arc_user.id', '=', 'wltrn_quotation.architech_id');
                $query_quot->leftJoin('users AS ele_user', 'ele_user.id', '=', 'wltrn_quotation.electrician_id');
                $query_quot->leftJoin('users AS sales_user', 'sales_user.id', '=', 'wltrn_quotation.salesexecutive_id');
                $query_quot->leftJoin('users AS source', 'source.id', '=', 'wltrn_quotation.quotationsource');
                $query_quot->leftJoin('users AS chann_user', 'chann_user.id', '=', 'wltrn_quotation.channelpartner_id');
                $query_quot->leftJoin('state_list', 'state_list.id', '=', 'wltrn_quotation.site_state_id');
                $query_quot->leftJoin('city_list', 'city_list.id', '=', 'wltrn_quotation.site_country_id');
                $query_quot->leftJoin('country_list', 'country_list.id', '=', 'wltrn_quotation.site_country_id');
                $query_quot->leftJoin('wlmst_quotation_type as quot_type', 'quot_type.id', '=', 'wltrn_quotation.quottype_id');
                $query_quot->where('wltrn_quotation.id', $QuotMaster->id);

                if ($request->quot_id != 0) {
                    $res_status = 1;
                    $res_status_code = 200;
                    $res_message = 'Successfully Saved Quotation Basic Detail';

                    $data_response = [];
                    foreach ($query_quot->get() as $key => $value) {
                        $quot_f_array = [];

                        if ($value->default_range != '' || $value->default_range != null) {
                            $Range_Subgroup = explode(',', $value->default_range);
                            foreach ($Range_Subgroup as $key => $subgroup_value) {
                                $range_subgroup_detail = WlmstItemSubgroup::find($subgroup_value);
                                $range_group_new[$subgroup_value] = $range_subgroup_detail;
                                $range_group = [];
                                array_push($range_group, $range_group_new);
                            }
                            $range_group_f = $range_group[0];
                        } else {
                            $range_group_f = json_decode('""');
                        }
                        $quot_f_array = $value;
                        $quot_f_array['source_type_lable'] = $this->getArchitectsSourceTypesValue($value->source);
                        $quot_f_array['default_range_value'] = $range_group_f;

                        array_push($data_response, $quot_f_array);
                    }
                    $res_data = $data_response[0];

                    $DebugLog = new DebugLog();
                    $DebugLog->user_id = 1;
                    $DebugLog->name = 'quot-quot-master-basicdetail-edit';
                    $DebugLog->description = 'Quotation master Basic Detail #' . $QuotMaster->id . '(' . $QuotMaster->id . ')' . ' has been updated';
                    $DebugLog->save();
                } else {
                    $res_status = 1;
                    $res_status_code = 200;
                    $res_message = 'Successfully Added Quotation Basic Detail';

                    // if ($isAvailableInquiryId != 0) {
                    // 	Lead::query()->where('id', $isAvailableInquiryId)->update(['is_deal' => 1]);
                    // }

                    $quot_array = [];
                    foreach ($query_quot->get() as $key => $value) {
                        $quot_f_array = $value;
                        $quot_f_array['default_range_value'] = json_decode('""');
                        // $quot_f_array['default_range_value'] = ;
                        $quot_f_array['source_type_lable'] = $this->getArchitectsSourceTypesValue($value->source);
                        array_push($quot_array, $quot_f_array);
                    }
                    $res_data = $quot_array[0];

                    $DebugLog = new DebugLog();
                    $DebugLog->user_id = 1;
                    $DebugLog->name = 'quot-quot-master-basicdetail-add';
                    $DebugLog->description = 'Quotation master Basic Detail #' . $QuotMaster->id . '(' . $QuotMaster->id . ') has been added';
                    $DebugLog->save();
                }
            }
        }

        $response['status'] = $res_status;
        $response['status_code'] = $res_status_code;
        $response['msg'] = $res_message;
        $response['data'] = $res_data;
        // $response['query'] = DB::getQueryLog();
        // } else {
        // 	$response = quoterrorRes(2, 402, "Please Update Your App");
        // }

        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }

    function getArchitectsSourceTypesValue($HelpDocumentStatus)
    {
        $HelpDocumentStatus = (int) $HelpDocumentStatus;
        if ($HelpDocumentStatus == 50) {
            $HelpDocumentStatus = 'None';
        } elseif ($HelpDocumentStatus == 301) {
            $HelpDocumentStatus = 'Electrician(Non Prime)';
        } elseif ($HelpDocumentStatus == 302) {
            $HelpDocumentStatus = 'Electrician(Prime)';
        } elseif ($HelpDocumentStatus == 101) {
            $HelpDocumentStatus = 'ASM';
        } elseif ($HelpDocumentStatus == 102) {
            $HelpDocumentStatus = 'ADM';
        } elseif ($HelpDocumentStatus == 103) {
            $HelpDocumentStatus = 'APM';
        } elseif ($HelpDocumentStatus == 104) {
            $HelpDocumentStatus = 'AD';
        } elseif ($HelpDocumentStatus == 51) {
            $HelpDocumentStatus = 'Retailer';
        } elseif ($HelpDocumentStatus == 52) {
            $HelpDocumentStatus = 'Whitelion HO';
        } elseif ($HelpDocumentStatus == 53) {
            $HelpDocumentStatus = 'Cold call';
        } elseif ($HelpDocumentStatus == 54) {
            $HelpDocumentStatus = 'Marketing activities';
        } elseif ($HelpDocumentStatus == 55) {
            $HelpDocumentStatus = 'Other';
        } elseif ($HelpDocumentStatus == 56) {
            $HelpDocumentStatus = 'Existing Client';
        }
        return $HelpDocumentStatus;
    }

    public function PostQuotRoomNBoardSave(Request $request)
    {
        DB::enableQueryLog();
        $res_status = '';
        $res_status_code = '';
        $res_message = '';
        $res_data = [];
        $response = [];
        $request_data = $request->input();
        $validator = Validator::make($request->input(), [
            'room' => ['required'],
            // 'item' => ['required']
        ]);

        if ($validator->fails()) {
            $res_status = 0;
            $res_message = 'Please Check Perameater And Value';
            $res_status_code = 400;
            $res_data = $validator->errors(); 
        } else {
            /* ROOM ARRAY KEY VALUE SEY HERE */
            $quot_itemdetail_id = $request['room'][0]['id'];
            $quot_id = $request['room'][0]['quot_id'];
            $quotgroup_id = $request['room'][0]['quotgroup_id'];
            $room_no = $request['room'][0]['room_no'];
            $room_adon = $request['room'][0]['room_adon'];
            $room_name = $request['room'][0]['room_name'];
            $board_no = $request['room'][0]['board_no'];
            $board_name = $request['room'][0]['board_name'];
            $board_size = $request['room'][0]['board_size'];
            $board_item_id = $request['room'][0]['board_item_id'];
            $board_item_price_id = $request['room'][0]['board_item_price_id'];
            $itemdescription = $request['room'][0]['itemdescription'];
            $default_range = $request['room'][0]['default_range'];
            $room_range = $request['room'][0]['room_range'];
            $board_range = $request['room'][0]['board_range'];
            $board_image = $request['room'][0]['board_image'];

            /* ------------------- END ------------------- */
            if ($quot_itemdetail_id != 0) {
                $QuotMaster = Wltrn_Quotation::find($quot_id);
                $QuotMaster->default_range = $default_range;
                $QuotMaster->save();

                if($QuotMaster->quottype_id == 5) {
                    $DeleteOldEntry = Wltrn_QuotItemdetail::where([['wltrn_quot_itemdetails.quot_id', $quot_id], ['wltrn_quot_itemdetails.quotgroup_id', $quotgroup_id], ['wltrn_quot_itemdetails.room_no', $room_no]])->delete();
                    $board_no = Wltrn_QuotItemdetail::where([['quot_id', $quot_id], ['quotgroup_id', $quotgroup_id], ['room_no', $room_no]])->max('board_no') + 1;
                }else{
                    $DeleteOldEntry = Wltrn_QuotItemdetail::where([['wltrn_quot_itemdetails.quot_id', $quot_id], ['wltrn_quot_itemdetails.quotgroup_id', $quotgroup_id], ['wltrn_quot_itemdetails.room_no', $room_no], ['wltrn_quot_itemdetails.board_no', $board_no]])->delete();
                }

                if ($board_image != '') {
                    $folderPathofFile = '/quotation/board';
                    $fileObject1 = base64_decode($board_image);
                    $extension = '.png';
                    $fileName1 = uniqid() . '_' . $quot_id . '_' . $quotgroup_id . '_' . $room_no . '_' . $board_no . $extension;
                    $destinationPath = public_path($folderPathofFile);

                    if (!File::exists($destinationPath)) {
                        File::makeDirectory($destinationPath);
                    }

                    file_put_contents($destinationPath . '/' . $fileName1, $fileObject1);

                    if (File::exists(public_path($folderPathofFile . '/' . $fileName1))) {
                        $Image_Path = $folderPathofFile . '/' . $fileName1;
                        //START UPLOAD FILE ON SPACES
                        $spaceUploadResponse = uploadFileOnSpaces(public_path($Image_Path), $Image_Path); //Live

                        if ($spaceUploadResponse != 1) {
                            $Image_Path = '';
                        } else {
                            unlink(public_path($Image_Path));
                        }
                        //END UPLOAD FILE ON SPACES
                    } else {
                        $Image_Path = '/assets/images/logo.png';
                    }
                } else {
                    $Image_Path = '/assets/images/logo.png';
                }

                $BoardSaveLog = [];
                $BoardSaveLog['quot_id'] = $quot_id;
                $BoardSaveLog['quotgroup_id'] = $quotgroup_id;
                $BoardSaveLog['room_no'] = $room_no;
                $BoardSaveLog['board_no'] = $board_no;
                // $BoardSaveLog['description'] = json_encode($request_data);
                $BoardSaveLog['description'] = ' ';
                $BoardSaveLog['source'] = $request->app_source;
                $BoardSaveLog['entryip'] = $request->ip();
                saveBoardSaveLog($BoardSaveLog);

                if (count($request_data['item']) >= 1) {
                    foreach ($request_data['item'] as $value) {
                        if (intval($value['qty']) != 0) {
                            $QuotationMaster = Wltrn_Quotation::find($quot_id);
                            $ItemMaster = WlmstItem::find($value['itemid']);
                            $SubTotal = floatval($value['mrp']) * floatval($value['qty']);
                            $Discount_Amount = (floatval($SubTotal) * floatval($value['discount'])) / 100;
                            $GrossAmount = floatval($SubTotal) - floatval($Discount_Amount);
                            if ($QuotationMaster->site_state_id == '9' /*IS GUJARAT*/) {
                                /* CGST CALCULATION */
                                $CGST_Per = $ItemMaster->cgst_per;
                                $CGST_Amount = (floatval($GrossAmount) * floatval($ItemMaster->cgst_per)) / 100;
                                /* SGST CALCULATION */
                                $SGST_Per = $ItemMaster->sgst_per;
                                $SGST_Amount = (floatval($GrossAmount) * floatval($ItemMaster->sgst_per)) / 100;
                                /* IGST CALCULATION */
                                $IGST_Per = '0.00';
                                $IGST_Amount = '0.00';

                                /* NET AMOUNT CALCULATION */
                                $NetTotalAmount = floatval($GrossAmount) + floatval($CGST_Amount) + floatval($SGST_Amount);
                                /* ROUND_UP AMOUNT CALCULATION */
                                $RoundUpAmount = floatval($NetTotalAmount) - floatval(round($NetTotalAmount));
                                /* NET FINAL AMOUNT CALCULATION */
                                $NetAmount = round($NetTotalAmount);
                            } else {
                                /* CGST CALCULATION */
                                $CGST_Per = '0';
                                $CGST_Amount = '0.00';
                                /* SGST CALCULATION */
                                $SGST_Per = '0';
                                $SGST_Amount = '0.00';
                                /* IGST CALCULATION */
                                $IGST_Per = $ItemMaster->igst_per;
                                $IGST_Amount = (floatval($GrossAmount) * floatval($ItemMaster->igst_per)) / 100;

                                /* NET AMOUNT CALCULATION */
                                $NetTotalAmount = floatval($GrossAmount) + floatval($IGST_Amount);
                                /* ROUND_UP AMOUNT CALCULATION */
                                $RoundUpAmount = floatval($NetTotalAmount) - floatval(round($NetTotalAmount));
                                /* NET FINAL AMOUNT CALCULATION */
                                $NetAmount = round($NetTotalAmount);
                            }

                            $qry_add_quot_item = new Wltrn_QuotItemdetail();
                            $qry_add_quot_item->quot_id = $quot_id;
                            $qry_add_quot_item->quotgroup_id = $quotgroup_id;
                            $qry_add_quot_item->room_no = $room_no;
                            $qry_add_quot_item->room_name = $room_name;
                            $qry_add_quot_item->board_no = $board_no;
                            $qry_add_quot_item->board_name = $board_name;
                            $qry_add_quot_item->board_size = $board_size;
                            $qry_add_quot_item->board_item_id = $board_item_id;
                            $qry_add_quot_item->board_item_price_id = $board_item_price_id;
                            if ($Image_Path != '') {
                                $qry_add_quot_item->board_image = $Image_Path;
                            }
                            $qry_add_quot_item->itemdescription = $itemdescription;
                            $qry_add_quot_item->item_id = $value['itemid'];
                            $qry_add_quot_item->item_price_id = $value['priceid'];
                            $qry_add_quot_item->company_id = $value['company_id'];
                            $qry_add_quot_item->itemgroup_id = $value['itemgroup_id'];
                            $qry_add_quot_item->itemsubgroup_id = $value['itemsubgroup_id'];
                            $qry_add_quot_item->itemcategory_id = $value['itemcategory_id'];
                            $qry_add_quot_item->itemcode = $value['code'];
                            $qry_add_quot_item->qty = $value['qty'];
                            $qry_add_quot_item->rate = $value['mrp'];

                            // $qry_add_quot_item->sequence_no = $value['sequence_no'];

                            $qry_add_quot_item->discper = $value['discount'];
                            $qry_add_quot_item->discamount = $Discount_Amount;

                            $qry_add_quot_item->grossamount = $GrossAmount;
                            $qry_add_quot_item->taxableamount = $GrossAmount;
                            $qry_add_quot_item->igst_per = $IGST_Per;
                            $qry_add_quot_item->igst_amount = $IGST_Amount;
                            $qry_add_quot_item->cgst_per = $CGST_Per;
                            $qry_add_quot_item->cgst_amount = $CGST_Amount;
                            $qry_add_quot_item->sgst_per = $SGST_Per;
                            $qry_add_quot_item->sgst_amount = $SGST_Amount;
                            $qry_add_quot_item->roundup_amount = $RoundUpAmount;
                            $qry_add_quot_item->net_amount = $NetAmount;
                            $qry_add_quot_item->item_type = $value['item_type'];
                            $qry_add_quot_item->room_range = $room_range;
                            $qry_add_quot_item->board_range = $board_range;
                            $qry_add_quot_item->entryby = Auth::user()->id; //Live
                            $qry_add_quot_item->entryip = $request->ip();
                            $qry_add_quot_item->save();
                        }
                    }
                    $res_message = 'Successfully Saved Quotation Item Detail';
                } else {
                    // $res_message = "item array empty";
                    $res_message = 'Successfully Saved Quotation Item Detail';
                }

                $res_status = 1;
                $res_status_code = 200;
                $res_data = $this->ShowRoomNBoardList($quot_id, $quotgroup_id, 0, 1);
                // $res_data = array();

                $DebugLog = new DebugLog();
                $DebugLog->user_id = 1;
                $DebugLog->name = 'quot-quot-master-basicdetail-edit';
                $DebugLog->description = "Quotation Item Detail has been Updated (#Quote ID = " . $quot_id . ") (#Quote Group ID = " . $quotgroup_id . ") (#Room No = " . $room_no . ") (#Board No = " . $board_no . ')';
                $DebugLog->save();
            } else {
                $QuotMaster = Wltrn_Quotation::find($quot_id);
                $QuotMaster->default_range = $default_range;
                $QuotMaster->save();

                if ($room_adon == '0') {
                    $BoardNo = Wltrn_QuotItemdetail::where([['quot_id', $quot_id], ['quotgroup_id', $quotgroup_id], ['room_no', $room_no]])->max('board_no') + 1;
                } else {
                    $BoardNo = 0;
                }

                if ($board_image != '') {
                    $folderPathofFile = '/quotation/board';
                    $fileObject1 = base64_decode($board_image);
                    $extension = '.png';
                    $fileName1 = uniqid() . '_' . $quot_id . '_' . $quotgroup_id . '_' . $room_no . '_' . $BoardNo . $extension;
                    $destinationPath = public_path($folderPathofFile);

                    if (!File::exists($destinationPath)) {
                        File::makeDirectory($destinationPath);
                    }

                    file_put_contents($destinationPath . '/' . $fileName1, $fileObject1);

                    if (File::exists(public_path($folderPathofFile . '/' . $fileName1))) {
                        $Image_Path = $folderPathofFile . '/' . $fileName1;
                        //START UPLOAD FILE ON SPACES
                        $spaceUploadResponse = uploadFileOnSpaces(public_path($Image_Path), $Image_Path); //Live

                        if ($spaceUploadResponse != 1) {
                            $Image_Path = '';
                        } else {
                            unlink(public_path($Image_Path));
                        }
                        //END UPLOAD FILE ON SPACES
                    } else {
                        $Image_Path = 'aa';
                    }
                } else {
                    $Image_Path = '/assets/images/logo.png';
                }

                $BoardSaveLog = [];
                $BoardSaveLog['quot_id'] = $quot_id;
                $BoardSaveLog['quotgroup_id'] = $quotgroup_id;
                $BoardSaveLog['room_no'] = $room_no;
                $BoardSaveLog['board_no'] = $BoardNo;
                // $BoardSaveLog['description'] = json_encode($request_data);
                $BoardSaveLog['description'] = ' ';
                $BoardSaveLog['source'] = $request->app_source;
                $BoardSaveLog['entryip'] = $request->ip();
                saveBoardSaveLog($BoardSaveLog);
                if (count($request_data['item']) >= 1) {
                    foreach ($request_data['item'] as $value) {
                        if (intval($value['qty']) != 0) {
                            $QuotationMaster = Wltrn_Quotation::find($quot_id);
                            $ItemMaster = WlmstItem::find($value['itemid']);
                            $SubTotal = floatval($value['mrp']) * floatval($value['qty']);
                            $Discount_Amount = (floatval($SubTotal) * floatval($value['discount'])) / 100;
                            $GrossAmount = floatval($SubTotal) - floatval($Discount_Amount);
                            if ($QuotationMaster->site_state_id == '9' /*IS GUJARAT*/) {
                                /* CGST CALCULATION */
                                $CGST_Per = $ItemMaster->cgst_per;
                                $CGST_Amount = (floatval($GrossAmount) * floatval($ItemMaster->cgst_per)) / 100;
                                /* SGST CALCULATION */
                                $SGST_Per = $ItemMaster->sgst_per;
                                $SGST_Amount = (floatval($GrossAmount) * floatval($ItemMaster->sgst_per)) / 100;
                                /* IGST CALCULATION */
                                $IGST_Per = '0.00';
                                $IGST_Amount = '0.00';

                                /* NET AMOUNT CALCULATION */
                                $NetTotalAmount = floatval($GrossAmount) + floatval($CGST_Amount) + floatval($SGST_Amount);
                                /* ROUND_UP AMOUNT CALCULATION */
                                $RoundUpAmount = floatval($NetTotalAmount) - floatval(round($NetTotalAmount));
                                /* NET FINAL AMOUNT CALCULATION */
                                $NetAmount = round($NetTotalAmount);
                            } else {
                                /* CGST CALCULATION */
                                $CGST_Per = '0';
                                $CGST_Amount = '0.00';
                                /* SGST CALCULATION */
                                $SGST_Per = '0';
                                $SGST_Amount = '0.00';
                                /* IGST CALCULATION */
                                $IGST_Per = $ItemMaster->igst_per;
                                $IGST_Amount = (floatval($GrossAmount) * floatval($ItemMaster->igst_per)) / 100;

                                /* NET AMOUNT CALCULATION */
                                $NetTotalAmount = floatval($GrossAmount) + floatval($IGST_Amount);
                                /* ROUND_UP AMOUNT CALCULATION */
                                $RoundUpAmount = floatval($NetTotalAmount) - floatval(round($NetTotalAmount));
                                /* NET FINAL AMOUNT CALCULATION */
                                $NetAmount = round($NetTotalAmount);
                            }

                            $qry_add_quot_item = new Wltrn_QuotItemdetail();
                            $qry_add_quot_item->quot_id = $quot_id;
                            $qry_add_quot_item->quotgroup_id = $quotgroup_id;
                            $qry_add_quot_item->room_no = $room_no;
                            $qry_add_quot_item->room_name = $room_name;
                            $qry_add_quot_item->board_no = $BoardNo;
                            $qry_add_quot_item->board_name = $board_name;
                            $qry_add_quot_item->board_size = $board_size;
                            $qry_add_quot_item->board_item_id = $board_item_id;
                            $qry_add_quot_item->board_item_price_id = $board_item_price_id;
                            if ($Image_Path != '') {
                                $qry_add_quot_item->board_image = $Image_Path;
                            }
                            $qry_add_quot_item->itemdescription = $itemdescription;
                            $qry_add_quot_item->item_id = $value['itemid'];
                            $qry_add_quot_item->item_price_id = $value['priceid'];
                            $qry_add_quot_item->company_id = $value['company_id'];
                            $qry_add_quot_item->itemgroup_id = $value['itemgroup_id'];
                            $qry_add_quot_item->itemsubgroup_id = $value['itemsubgroup_id'];
                            $qry_add_quot_item->itemgroup_id = $value['itemgroup_id'];
                            $qry_add_quot_item->itemcode = $value['code'];
                            $qry_add_quot_item->qty = $value['qty'];
                            $qry_add_quot_item->rate = $value['mrp'];

                            $qry_add_quot_item->discper = $value['discount'];
                            $qry_add_quot_item->discamount = $Discount_Amount;

                            $qry_add_quot_item->grossamount = $GrossAmount;
                            $qry_add_quot_item->taxableamount = $GrossAmount;
                            $qry_add_quot_item->igst_per = $IGST_Per;
                            $qry_add_quot_item->igst_amount = $IGST_Amount;
                            $qry_add_quot_item->cgst_per = $CGST_Per;
                            $qry_add_quot_item->cgst_amount = $CGST_Amount;
                            $qry_add_quot_item->sgst_per = $SGST_Per;
                            $qry_add_quot_item->sgst_amount = $SGST_Amount;
                            $qry_add_quot_item->roundup_amount = $RoundUpAmount;
                            $qry_add_quot_item->net_amount = $NetAmount;
                            $qry_add_quot_item->item_type = $value['item_type'];
                            $qry_add_quot_item->room_range = $room_range;
                            $qry_add_quot_item->board_range = $board_range;
                            $qry_add_quot_item->entryby = Auth::user()->id; //Live
                            $qry_add_quot_item->entryip = $request->ip();
                            $qry_add_quot_item->save();
                        }
                    }
                    $res_message = 'Successfully Saved Quotation Item Detail';
                } else {
                    $res_message = 'Successfully Saved Quotation Item Detail';
                }

                $res_status = 1;
                $res_status_code = 200;
                $res_data = $this->ShowRoomNBoardList($quot_id, $quotgroup_id, 0, 1);
                // $res_data = array();

                $DebugLog = new DebugLog();
                $DebugLog->user_id = Auth::user()->id; //Live;
                $DebugLog->name = 'quot-quot-itemdetail-add';
                $DebugLog->description =
                    "Quotation Item Detail has been added
     (#Quote ID = " .
                    $quot_id .
                    ")
     (#Quote Group ID = " .
                    $quotgroup_id .
                    ")
     (#Room No = " .
                    $room_no .
                    ")
     (#Board No = " .
                    $BoardNo .
                    ')';
                $DebugLog->save();
            }
        }

        $response['status'] = $res_status;
        $response['status_code'] = $res_status_code;
        $response['msg'] = $res_message;
        $response['data'] = $res_data;
        // $response['query'] = DB::getQueryLog();

        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }

    public function PostQuotRoomList(Request $request)
    {
        // if (getCheckAppVersion($request->app_source, $request->app_version)) {
        $searchColumns = [
            0 => 'wltrn_quot_itemdetails.room_name',
        ];

        $room_columns = [
        'wltrn_quot_itemdetails.room_no', 
        'wltrn_quot_itemdetails.room_name', 
        'wltrn_quot_itemdetails.room_range', 
        'wltrn_quot_itemdetails.quot_id', 
        'wltrn_quot_itemdetails.quotgroup_id',
        'wltrn_quot_itemdetails.isactiveroom'];

        $response = [];
        $status = 0;
        $status_code = http_response_code();
        $msg = 'api';
        $data = null;

        try {
            $response = [];
            $status = 1;
            $msg = 'Quotation Room List Success';

            $room_query = Wltrn_QuotItemdetail::query();
            $room_query->select($room_columns);
            $room_query->where([['wltrn_quot_itemdetails.quot_id', $request->quot_id], ['wltrn_quot_itemdetails.quotgroup_id', $request->quotgroup_id]]);
            $room_query->groupBy($room_columns);
            $room_query->orderBy('wltrn_quot_itemdetails.room_no', 'ASC');
            // $room_query->groupBy(['wltrn_quot_itemdetails.room_no']);

            if (isset($request->q)) {
                $search_value = $request->q;
                $room_query->where(function ($query) use ($search_value, $searchColumns) {
                    for ($i = 0; $i < count($searchColumns); $i++) {
                        if ($i == 0) {
                            $query->where($searchColumns[$i], 'like', '%' . $search_value . '%');
                        } else {
                            $query->orWhere($searchColumns[$i], 'like', '%' . $search_value . '%');
                        }
                    }
                });
            }

            $roomlist = $room_query->get();
            $quot_array = [];
            foreach ($roomlist as $key => $value) {
                $quot_f_array = [];

                if ($value['room_range'] != '' || $value['room_range'] != null) {
                    $Range_Subgroup = explode(',', $value['room_range']);
                    // $Range_Subgroup = $quotlist[$key]['default_range'];
                    $range_group = '';
                    $range_company = '';
                    for ($i = 0; $i < count($Range_Subgroup); $i++) {
                        $range_group .= WlmstItemSubgroup::find($Range_Subgroup[$i])->itemgroup_id . ',';
                        $range_company .= WlmstItemSubgroup::find($Range_Subgroup[$i])->company_id . ',';
                    }
                    $range_group_f = substr($range_group, 0, -1);
                    // $range_company_f = substr($range_company,0,-1);
                    $range_company_f = explode(',', $range_company)[0];
                } else {
                    $range_group_f = '';
                    $range_company_f = '';
                }

                $room_addon_query = WlmstItem::query();
                $room_addon_query->select('wlmst_items.id');
                $room_addon_query->leftJoin('wlmst_item_categories', 'wlmst_items.itemcategory_id', '=', 'wlmst_item_categories.id');
                $room_addon_query->leftJoin('wlmst_item_prices', 'wlmst_items.id', '=', 'wlmst_item_prices.item_id');
                $room_addon_query->leftJoin('wlmst_companies', 'wlmst_companies.id', '=', 'wlmst_item_prices.company_id');
                $room_addon_query->leftJoin('wlmst_item_groups', 'wlmst_item_groups.id', '=', 'wlmst_item_prices.itemgroup_id');
                $room_addon_query->leftJoin('wlmst_item_subgroups', 'wlmst_item_subgroups.id', '=', 'wlmst_item_prices.itemsubgroup_id');
                $room_addon_query->whereRaw("find_in_set('4',wlmst_items.itemcategory_id)");
                $data_room_addon = $room_addon_query->get();

                $room_addon_qry = Wltrn_QuotItemdetail::query();
                $room_addon_qry->select('wlmst_item_prices.id as priceid', 'wlmst_items.id as itemid', 'wlmst_items.itemname', 'wlmst_items.app_display_name', 'wlmst_items.module', 'wlmst_items.max_module', 'wlmst_items.is_special', 'wlmst_items.additional_remark', 'wlmst_items.remark', 'wlmst_items.itemcategory_id', 'wlmst_item_categories.itemcategoryname', 'wlmst_item_prices.mrp', 'wlmst_item_prices.discount', 'wlmst_item_prices.company_id', 'wlmst_item_prices.item_type', 'wlmst_companies.companyname', 'wlmst_item_prices.itemgroup_id', 'wlmst_item_groups.itemgroupname', 'wlmst_item_prices.itemsubgroup_id', 'wlmst_item_subgroups.itemsubgroupname', 'wlmst_items.image', 'wlmst_item_prices.code', 'wlmst_item_prices.code AS product_code', 'wlmst_items.itemname AS product_code_name', DB::raw('CONCAT(wlmst_item_prices.code," - ", wltrn_quot_itemdetails.qty) AS name'));
                $room_addon_qry->leftJoin('wlmst_items', 'wltrn_quot_itemdetails.item_id', '=', 'wlmst_items.id');
                $room_addon_qry->leftJoin('wlmst_item_prices', 'wltrn_quot_itemdetails.item_price_id', '=', 'wlmst_item_prices.id');
                $room_addon_qry->leftJoin('wlmst_item_categories', 'wltrn_quot_itemdetails.itemcategory_id', '=', 'wlmst_item_categories.id');
                $room_addon_qry->leftJoin('wlmst_companies', 'wltrn_quot_itemdetails.company_id', '=', 'wlmst_companies.id');
                $room_addon_qry->leftJoin('wlmst_item_groups', 'wltrn_quot_itemdetails.itemgroup_id', '=', 'wlmst_item_groups.id');
                $room_addon_qry->leftJoin('wlmst_item_subgroups', 'wltrn_quot_itemdetails.itemsubgroup_id', '=', 'wlmst_item_subgroups.id');
                // $room_items_qry->whereNotIn('wltrn_quot_itemdetails.item_id', ['wltrn_quot_itemdetails.room_item_id']);
                // $room_addon_qry->where('wltrn_quot_itemdetails.item_id', '<>', $room_value->room_item_id);
                $room_addon_qry->whereIn('wltrn_quot_itemdetails.item_id', $data_room_addon);
                $room_addon_qry->where([['wltrn_quot_itemdetails.quot_id', $request->quot_id], ['wltrn_quot_itemdetails.quotgroup_id', $request->quotgroup_id], ['wltrn_quot_itemdetails.room_no', $value['room_no']], ['wltrn_quot_itemdetails.board_no', 0]]);

                $quot_f_array = $value;
                $quot_f_array['range_group'] = $range_group_f;
                $quot_f_array['range_company'] = $range_company_f;
                $quot_f_array['room_addon'] = $room_addon_qry->get();
                array_push($quot_array, $quot_f_array);
            }

            // $data = $query->paginate(20);

            $data = $quot_array;
        } catch (QueryException $ex) {
            $response = [];
            $status = 0;
            $msg = 'Please Contact To Admin';
            $data = $ex;
        }

        $response['status'] = $status;
        $response['status_code'] = $status_code;
        $response['msg'] = $msg;
        // $response['query'] = DB::getQueryLog();
        $response['data'] = $data;
        // } else {
        // 	$response = quoterrorRes(2, 402, "Please Update Your App");
        // }

        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }

    public function PostQuotQuartzColour(Request $request)
    {
        // if (getCheckAppVersion($request->app_source, $request->app_version)) {
        $searchColumns = [
            0 => 'wlmst_item_subgroups.itemsubgroupname',
        ];

        $columns = [
            0 => 'wlmst_item_subgroups.id AS itemsubgroup_id',
            1 => 'wlmst_item_subgroups.itemsubgroupname',
            2 => 'wlmst_item_subgroups.company_id',
            3 => 'wlmst_companies.companyname',
            4 => 'wlmst_item_subgroups.itemgroup_id',
            5 => 'wlmst_item_groups.itemgroupname',
            6 => 'wlmst_item_subgroups.shortname',
            7 => 'wlmst_item_subgroups.remark',
        ];

        $response = [];
        $status = 0;
        $status_code = http_response_code();
        $msg = 'api';
        $data = null;

        try {
            $response = [];
            $status = 1;
            $msg = 'Quotation Quartz Colours List Success';

            $quartz_query = WlmstItemSubgroup::query();
            $quartz_query->select($columns);
            $quartz_query->leftJoin('wlmst_companies', 'wlmst_companies.id', '=', 'wlmst_item_subgroups.company_id');
            $quartz_query->leftJoin('wlmst_item_groups', 'wlmst_item_groups.id', '=', 'wlmst_item_subgroups.itemgroup_id');
            $quartz_query->where('wlmst_item_subgroups.itemsubgroupname', 'like', '%Quartz%');
            $quartz_query->where('wlmst_item_subgroups.isactive', '1');
            $quartz_query->where('wlmst_item_subgroups.itemgroup_id', '1');

            if (isset($request->q)) {
                $search_value = $request->q;
                $quartz_query->where(function ($query) use ($search_value, $searchColumns) {
                    for ($i = 0; $i < count($searchColumns); $i++) {
                        if ($i == 0) {
                            $query->where($searchColumns[$i], 'like', '%' . $search_value . '%');
                        } else {
                            $query->orWhere($searchColumns[$i], 'like', '%' . $search_value . '%');
                        }
                    }
                });
            }

            $data = $quartz_query->get();
        } catch (QueryException $ex) {
            $response = [];
            $status = 0;
            $msg = 'Please Contact To Admin';
            $data = $ex;
        }

        $response['status'] = $status;
        $response['status_code'] = $status_code;
        $response['msg'] = $msg;
        $response['data'] = $data;
        // } else {
        // 	$response = quoterrorRes(2, 402, "Please Update Your App");
        // }

        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }

    public function PostQuotRoomWiseBoardList(Request $request)
    {
        DB::enableQueryLog();
        $columns = ['wltrn_quot_itemdetails.isactiveboard', 'wltrn_quot_itemdetails.board_image', 'wltrn_quot_itemdetails.quot_id', 'wltrn_quot_itemdetails.quotgroup_id', 'wltrn_quot_itemdetails.srno', 'wltrn_quot_itemdetails.isactiveboard', 'wltrn_quot_itemdetails.room_no', 'wltrn_quot_itemdetails.board_item_id', 'wltrn_quot_itemdetails.item_type', 'wltrn_quot_itemdetails.board_no', 'wltrn_quot_itemdetails.board_name'];

        $response = [];
        $status = 0;
        $status_code = http_response_code();
        $msg = 'api';
        $data = null;

        try {
            $response = [];
            $status = 1;
            $msg = 'Quotation Room List Success';

            $board_query = Wltrn_QuotItemdetail::query();
            $board_query->select($columns);
            $board_query->leftJoin('wlmst_items', 'wltrn_quot_itemdetails.board_item_id', '=', 'wlmst_items.id');
            $board_query->where([['wltrn_quot_itemdetails.quot_id', $request->quot_id], ['wltrn_quot_itemdetails.quotgroup_id', $request->quotgroup_id], ['wltrn_quot_itemdetails.room_no', $request->room_id]]);
            // $board_query->where('wltrn_quot_itemdetails.board_no', '!=', 0);
            $board_query->orderBy('wltrn_quot_itemdetails.board_no', 'asc');
            // $board_query->groupBy(['wltrn_quot_itemdetails.board_no']);
            $board_query->groupBy($columns);

            // $data = $board_query->get();
            $boardlist = $board_query->get();
            $quot_array = [];
            foreach ($boardlist as $key => $board_value) {
                $quot_f_array = [];

                $board_range_query = Wltrn_QuotItemdetail::query();
                $board_range_query->select('wltrn_quot_itemdetails.board_range');
                $board_range_query->where([['wltrn_quot_itemdetails.quot_id', $request->quot_id], ['wltrn_quot_itemdetails.quotgroup_id', $request->quotgroup_id], ['wltrn_quot_itemdetails.room_no', $request->room_id], ['wltrn_quot_itemdetails.board_no', $board_value->board_no]]);
                $board_range_query = $board_range_query->first();

                if ($board_range_query->board_range != '' || $board_range_query->board_range != null) {
                    $Range_Subgroup = explode(',', $board_range_query->board_range);
                    $range_group = '';
                    $range_company = '';
                    for ($i = 0; $i < count($Range_Subgroup); $i++) {
                        $range_group .= WlmstItemSubgroup::find($Range_Subgroup[$i])->itemgroup_id . ',';
                        $range_company .= WlmstItemSubgroup::find($Range_Subgroup[$i])->company_id . ',';
                    }
                    $range_group_f = substr($range_group, 0, -1);
                    // $range_company_f = substr($range_company,0,-1);
                    $range_company_f = explode(',', $range_company)[0];
                } else {
                    $range_group_f = ' ';
                    $range_company_f = ' ';
                }

                $quot_f_array = $board_value;
                if ($board_value->board_no == 0) {
                    $Board_Name = 'Room Addon';
                } else {
                    $Board_Name = $board_value->board_name;
                }
                $quot_f_array['board_name'] = $Board_Name; //Live
                $quot_f_array['image'] = getSpaceFilePath($board_value->board_image); //Live
                $quot_f_array['range_group'] = $range_group_f;
                $quot_f_array['range_company'] = $range_company_f;

                $board_items_column = ['wlmst_item_prices.id as priceid', 'wlmst_items.id as itemid', 'wlmst_items.itemname', 'wlmst_items.app_display_name', 'wlmst_item_prices.company_id', 'wlmst_companies.companyname', 'wlmst_items.itemcategory_id', 'wlmst_item_categories.itemcategoryname', 'wlmst_item_prices.itemgroup_id', 'wlmst_item_groups.itemgroupname', 'wlmst_item_prices.itemsubgroup_id', 'wlmst_item_subgroups.itemsubgroupname', 'wlmst_items.module', 'wltrn_quot_itemdetails.qty', 'wlmst_items.max_module', 'wlmst_items.is_special', 'wlmst_items.additional_remark', 'wltrn_quot_itemdetails.discper as discount', 'wlmst_items.remark', 'wlmst_item_prices.mrp', 'wlmst_item_prices.image', 'wlmst_item_prices.code', 'wlmst_item_prices.code AS product_code_name'];
                $board_items_qry = Wltrn_QuotItemdetail::query();
                $board_items_qry->select($board_items_column);
                $board_items_qry->leftJoin('wlmst_items', 'wltrn_quot_itemdetails.item_id', '=', 'wlmst_items.id');
                $board_items_qry->leftJoin('wlmst_item_prices', 'wltrn_quot_itemdetails.item_price_id', '=', 'wlmst_item_prices.id');
                $board_items_qry->leftJoin('wlmst_item_categories', 'wltrn_quot_itemdetails.itemcategory_id', '=', 'wlmst_item_categories.id');
                $board_items_qry->leftJoin('wlmst_companies', 'wltrn_quot_itemdetails.company_id', '=', 'wlmst_companies.id');
                $board_items_qry->leftJoin('wlmst_item_groups', 'wltrn_quot_itemdetails.itemgroup_id', '=', 'wlmst_item_groups.id');
                $board_items_qry->leftJoin('wlmst_item_subgroups', 'wltrn_quot_itemdetails.itemsubgroup_id', '=', 'wlmst_item_subgroups.id');
                // $board_items_qry->whereNotIn('wltrn_quot_itemdetails.item_id', ['wltrn_quot_itemdetails.board_item_id']);
                $board_items_qry->where('wltrn_quot_itemdetails.item_id', '<>', $board_value->board_item_id);
                $board_items_qry->where([['wltrn_quot_itemdetails.quot_id', $request->quot_id], ['wltrn_quot_itemdetails.quotgroup_id', $request->quotgroup_id], ['wltrn_quot_itemdetails.room_no', $request->room_id], ['wltrn_quot_itemdetails.board_no', $board_value->board_no]]);
                // TODO IF I WANT PLATE SHOW IN FIRST
                // $board_items_qry->orderByRaw('IF(wltrn_quot_itemdetails.item_id = wltrn_quot_itemdetails.board_item_id, 0,1)');
                $range_group_new = [];
                $range_group = [];
                $item_name = '';
                foreach ($board_items_qry->get() as $key => $value) {
                    if ($value->image == null) {
                        $value['image'] = 'http://axoneerp.whitelion.in/assets/images/logo.svg';
                    } else {
                        $value['image'] = getSpaceFilePath($value->image);
                    }
                    // if($value->itemid != $board_value->board_item_id){
                    $value['is_addons'] = $value->itemcategory_id == 6 ? 1 : 0;
                    // $range_group_new = array();
                    if ($value->itemcategory_id == 6) {
                        $item_name .= $value->itemname . ',';
                    }
                    $range_group_new[$value->priceid] = $value;
                    $range_group = [];
                    array_push($range_group, $range_group_new);

                    // $range_group = array();
                    // }
                }
                $range_group_new = [];

                $board_addon_query = WlmstItem::query();
                $board_addon_query->select('wlmst_items.id');
                $board_addon_query->leftJoin('wlmst_item_categories', 'wlmst_items.itemcategory_id', '=', 'wlmst_item_categories.id');
                $board_addon_query->leftJoin('wlmst_item_prices', 'wlmst_items.id', '=', 'wlmst_item_prices.item_id');
                $board_addon_query->leftJoin('wlmst_companies', 'wlmst_companies.id', '=', 'wlmst_item_prices.company_id');
                $board_addon_query->leftJoin('wlmst_item_groups', 'wlmst_item_groups.id', '=', 'wlmst_item_prices.itemgroup_id');
                $board_addon_query->leftJoin('wlmst_item_subgroups', 'wlmst_item_subgroups.id', '=', 'wlmst_item_prices.itemsubgroup_id');
                if ($board_value->board_no == 0) {
                    $board_addon_query->whereRaw("find_in_set('4',wlmst_items.itemcategory_id)");
                } else {
                    $board_addon_query->whereRaw("find_in_set('6',wlmst_items.itemcategory_id)");
                }
                $data_board_addon = $board_addon_query->get();

                $board_addon_qry = Wltrn_QuotItemdetail::query();
                $board_addon_qry->select(DB::raw('CONCAT(wlmst_item_prices.code," - ", wltrn_quot_itemdetails.qty) AS name'));
                $board_addon_qry->leftJoin('wlmst_items', 'wltrn_quot_itemdetails.item_id', '=', 'wlmst_items.id');
                $board_addon_qry->leftJoin('wlmst_item_prices', 'wltrn_quot_itemdetails.item_price_id', '=', 'wlmst_item_prices.id');
                $board_addon_qry->leftJoin('wlmst_item_categories', 'wltrn_quot_itemdetails.itemcategory_id', '=', 'wlmst_item_categories.id');
                $board_addon_qry->leftJoin('wlmst_companies', 'wltrn_quot_itemdetails.company_id', '=', 'wlmst_companies.id');
                $board_addon_qry->leftJoin('wlmst_item_groups', 'wltrn_quot_itemdetails.itemgroup_id', '=', 'wlmst_item_groups.id');
                $board_addon_qry->leftJoin('wlmst_item_subgroups', 'wltrn_quot_itemdetails.itemsubgroup_id', '=', 'wlmst_item_subgroups.id');
                // $board_items_qry->whereNotIn('wltrn_quot_itemdetails.item_id', ['wltrn_quot_itemdetails.board_item_id']);
                // $board_addon_qry->where('wltrn_quot_itemdetails.item_id', '<>', $board_value->board_item_id);
                $board_addon_qry->whereIn('wltrn_quot_itemdetails.item_id', $data_board_addon);
                $board_addon_qry->where([['wltrn_quot_itemdetails.quot_id', $request->quot_id], ['wltrn_quot_itemdetails.quotgroup_id', $request->quotgroup_id], ['wltrn_quot_itemdetails.room_no', $request->room_id], ['wltrn_quot_itemdetails.board_no', $board_value->board_no]]);

                $quot_f_array['itemname'] = rtrim($item_name, ',');
                if (!empty($range_group)) {
                    $quot_f_array['board_item'] = json_encode($range_group[0]);
                } else {
                    $quot_f_array['board_item'] = 'null';
                }
                $quot_f_array['board_addon'] = $board_addon_qry->get();

                array_push($quot_array, $quot_f_array);
            }

            $data['data'] = $quot_array;

            $room_addon_arr = [];
            $room_addon_column = ['wlmst_items.id as itemid', 'wlmst_items.itemname'];
            $room_addon_qry = Wltrn_QuotItemdetail::query();
            $room_addon_qry->select($room_addon_column);
            $room_addon_qry->leftJoin('wlmst_items', 'wltrn_quot_itemdetails.item_id', '=', 'wlmst_items.id');
            // $room_addon_qry->where('wltrn_quot_itemdetails.item_id', '<>', $board_value->board_item_id);
            $room_addon_qry->where([['wltrn_quot_itemdetails.quot_id', $request->quot_id], ['wltrn_quot_itemdetails.quotgroup_id', $request->quotgroup_id], ['wltrn_quot_itemdetails.room_no', $request->room_id], ['wltrn_quot_itemdetails.board_no', 0]]);
            $room_addon_name = '';
            foreach ($room_addon_qry->get() as $key => $value) {
                $room_addon_name .= $value->itemname . ',';
            }
            $room_addon_arr['text'] = rtrim($room_addon_name, ',');

            $data['room_addon'] = [$room_addon_arr];
        } catch (QueryException $ex) {
            $response = [];
            $status = 0;
            $msg = 'Please Contact To Admin';
            $data = $ex;
        }

        $response['status'] = $status;
        $response['status_code'] = $status_code;

        $response['msg'] = $msg;
        $response['data'] = $data;
        // $response['query'] = DB::getQueryLog();
        // } else {
        // 	$response = quoterrorRes(2, 402, "Please Update Your App");
        // }

        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }

    public function PostSentQuotation(Request $request)
    {
        $request_data = $request->input();
        if(getCityName(Auth::user()->city_id) == 'Surat'){
            $validator = Validator::make($request->all(), [
                'quot_id' => ['required'],
                'quot_group_id' => ['required'],
                'discount' => ['required'],
            ]);
        }else{
            $validator = Validator::make($request->all(), [
                'quot_id' => ['required'],
            ]);
        }

        if ($validator->fails()) {
            $response = errorRes($validator->errors()->first());
            $response['data'] = $validator->errors();
            
        } else {
            // if(getCityName(Auth::user()->city_id) == 'Surat' || getCityName(Auth::user()->city_id) == 'Pune'){
                
                $quot_id = $request_data['quot_id'];
                $quot_group_id = $request_data['quot_group_id'];
                $lstDiscount = $request_data['discount'];
                $isSalePerson = isSalePerson();
    
                $group_id = 0;
                if (count($lstDiscount) >= 1) {
                    // $response = successRes();
                    // $response['count'] = count($lstDiscount);
                    try {
                        $max_group_id = DB::table('quotation_request')->max('group_id');
                        if ($max_group_id) {
                            $group_id = $max_group_id + 1;
                        } else {
                            $group_id = 1;
                        }
                        $isQuotationConfirm = 0;
                        $Manager_assign = 0;
                        $Channel_partner_assign = 0;
                        $Hod_assign = 0;
                        foreach ($lstDiscount as $dis_key => $dis_value) {
                            $QuotItemDetailArr = Wltrn_QuotItemdetail::select('*')->where([['wltrn_quot_itemdetails.quot_id', $quot_id], ['wltrn_quot_itemdetails.quotgroup_id', $quot_group_id], ['wltrn_quot_itemdetails.itemsubgroup_id', $dis_value['itemsubgroup_id']]]);
    
                            $isApply = 0;
                            $AssignTo = 1;
                            $selectSubgroupColumns = array(
                                'wlmst_item_subgroups.id',
                                'wlmst_item_subgroups.maxdisc',
                                'wlmst_item_subgroups.itemgroup_id',
                                'wlmst_item_subgroups.channel_partner_maxdisc',
                                'wlmst_item_subgroups.manager_ids',
                                'wlmst_item_subgroups.manager_maxdisc',
                                'wlmst_item_subgroups.company_admin_maxdisc',
                                'wlmst_item_subgroups.company_id'
                            );
                            $subgroupDetail = WlmstItemSubgroup::select($selectSubgroupColumns)->where('wlmst_item_subgroups.id',$dis_value['itemsubgroup_id'])->first();
                            if($subgroupDetail){
                                if($subgroupDetail->itemgroup_id == 1 || $subgroupDetail->itemgroup_id == 3){
                                    if ($dis_value['discount'] <= $subgroupDetail->maxdisc) {
                                        $isApply = 1;
                                    } elseif ($dis_value['discount'] > $subgroupDetail->maxdisc && $dis_value['discount'] <= $subgroupDetail->manager_maxdisc){
                                        if (in_array(Auth::user()->id, explode(',', $subgroupDetail['manager_ids']))) {
                                            $isApply = 1;
                                        } else {
                                            $AssignTo = 1;
                                            $isApply = 0;
                                            try {
                                                $Deal_id = Wltrn_Quotation::find($quot_id)->inquiry_id;
                                                $lead = Lead::find($Deal_id)->assigned_to;
                                                $parentSalesIds = getParentSalePersonsIds($lead);
                                                foreach (explode(',', $subgroupDetail['manager_ids']) as $key => $value) {
                                                    if (in_array($value, $parentSalesIds)) {
                                                        $AssignTo = $value;
                                                        $Manager_assign = $value;
                                                        
                                                    }
                                                }
                                            } catch (QueryException $ex) {
                                                
                                            }
                                        }
                                    }else{
                                        $isApply = 0;
                                        $AssignTo = 1;
                                        $Hod_assign = 1;
                                    }
                                }else{
                                    if ($dis_value['discount'] <= $subgroupDetail->maxdisc) {
                                        $isApply = 1;
                                    } elseif ($dis_value['discount'] > $subgroupDetail->maxdisc && $dis_value['discount'] <= $subgroupDetail->channel_partner_maxdisc){
                                        $ch_type_arr = ['user-101', 'user-102', 'user-103', 'user-104', 'user-105'];
                                        $isApply = 0;
                                        $AssignTo = 1;
                                        $Deal_id = Wltrn_Quotation::find($quot_id)->inquiry_id;
                                        if ($Deal_id) {
                                            $Lead_ch_id = LeadSource::select('source')->where('lead_id', $Deal_id)->whereIn('source_type', $ch_type_arr)->orderBy('id', 'DESC')->first();
                                            if ($Lead_ch_id) {
                                                $AssignTo = $Lead_ch_id->source;
                                                $Channel_partner_assign = $Lead_ch_id->source;
                                                        
                                            } else {
                                                $AssignTo = 1;
                                            }
                                        }
                                    }else{
                                        $isApply = 0;
                                        $AssignTo = 1;
                                        $Hod_assign = 1;
                                    }
                                }
    
        
                                if ($isApply == 1) {
                                    foreach ($QuotItemDetailArr->get() as $key => $value) {
                                        $QuotItemDetail = Wltrn_QuotItemdetail::find($value['id']);
        
                                        $totalamt = floatval($QuotItemDetail->qty) * floatval($QuotItemDetail->rate);
                                        $dis_amt = (floatval($totalamt) * floatval($dis_value['discount'])) / 100;
                                        $new_grossamount = floatval($totalamt) - floatval($dis_amt);
                                        $new_taxableamount = floatval($totalamt) - floatval($dis_amt);
        
                                        $new_igst_amount = (floatval($new_taxableamount) * floatval($QuotItemDetail->igst_per)) / 100;
                                        $new_cgst_amount = (floatval($new_taxableamount) * floatval($QuotItemDetail->cgst_per)) / 100;
                                        $new_sgst_amount = (floatval($new_taxableamount) * floatval($QuotItemDetail->sgst_per)) / 100;
        
                                        /* NET AMOUNT CALCULATION */
                                        $NetTotalAmount = floatval($new_taxableamount) + floatval($new_igst_amount) + floatval($new_cgst_amount) + floatval($new_sgst_amount);
                                        /* ROUND_UP AMOUNT CALCULATION */
                                        $RoundUpAmount = floatval($NetTotalAmount) - floatval(round($NetTotalAmount));
                                        /* NET FINAL AMOUNT CALCULATION */
                                        $new_net_amount = round($NetTotalAmount);
        
                                        $QuotItemDetail->discper = $dis_value['discount'];
                                        $QuotItemDetail->discamount = $dis_amt;
                                        $QuotItemDetail->grossamount = $new_grossamount;
                                        $QuotItemDetail->taxableamount = $new_taxableamount;
                                        $QuotItemDetail->igst_amount = $new_igst_amount;
                                        $QuotItemDetail->cgst_amount = $new_cgst_amount;
                                        $QuotItemDetail->sgst_amount = $new_sgst_amount;
                                        $QuotItemDetail->roundup_amount = $RoundUpAmount;
                                        $QuotItemDetail->net_amount = $new_net_amount;
        
                                        $QuotItemDetail->save();
                                    }
                                    $isQuotationConfirm = $isQuotationConfirm + 1;
                                } else {
                                    $Quot_Request = new QuotRequest();
                                    $Quot_Request->group_id = $group_id;
                                    $Quot_Request->quot_id = $quot_id;
                                    $Quot_Request->quotgroup_id = $quot_group_id;
                                    $Quot_Request->subgroup_id = $dis_value['itemsubgroup_id'];
                                    $Quot_Request->discount = $dis_value['discount'];
                                    $Quot_Request->deal_id = Wltrn_Quotation::find($quot_id)->inquiry_id;
                                    $Quot_Request->title = 'Discount Approvel';
                                    $Quot_Request->type = 'DISCOUNT';
                                    $Quot_Request->assign_to = $AssignTo;
                                    $Quot_Request->status = 0;
                                    $Quot_Request->entryby = Auth::user()->id;
                                    $Quot_Request->entryip = $request->ip();
                                    $Quot_Request->save();
                                }
                            }else{
                                $response = errorRes('invalid brand');
                            }
                        }
    
    
                        if (count($lstDiscount) == $isQuotationConfirm) {
    
                            $QuotMaster = Wltrn_Quotation::find($quot_id);
                            $QuotMaster->updateby = Auth::user()->id;
                            $QuotMaster->updateip = $request->ip();
                            // Wltrn_Quotation::where('quotgroup_id', $quot_group_id)->update(['isfinal' => 0]);
                            Wltrn_Quotation::where('inquiry_id', $QuotMaster->inquiry_id)->update(['isfinal' => 0]);
                            $QuotMaster->status = 3;
                            $QuotMaster->isfinal = 1;
                            $QuotMaster->save();
    
                            if ($QuotMaster) {
                                $res_data = $QuotMaster;
                                if ($QuotMaster->inquiry_id != null || $QuotMaster->inquiry_id != '' || (int) $QuotMaster->inquiry_id != 0) {
                                    $Lead = Lead::find($QuotMaster->inquiry_id);
                                    // if ($Lead->is_deal == 0) {
                                        $LeadQuotationController = new LeadQuotationController();
                                        $Lead->is_deal = 1;
                                        $Lead->status = 100;
                                        $Lead->account_user_id = $LeadQuotationController->accountCreate($Lead,strval($request->ip()),Auth::user()->id,$request->app_source);
                                        $Lead->save();
    
                                        $timeline = [];
                                        $timeline['lead_id'] = $Lead->id;
                                        $timeline['type'] = 'convert-to-deal';
                                        $timeline['reffrance_id'] = $Lead->id;
                                        $timeline['description'] = 'Quatation upload - convert to deal';
                                        $timeline['source'] = $request->app_source;
                                        saveLeadTimeline($timeline);
    
                                        $lead_msg = $Lead;
                                    // } else {
                                    //     $lead_msg = $Lead;
                                    // }
                                } else {
                                    $lead_msg = 'inquiry id nulll in quotation';
                                }
    
                                $DebugLog = new DebugLog();
                                $DebugLog->user_id = 1;
                                $DebugLog->name = 'quotation-sent';
                                $DebugLog->description = 'Quotation #' . $QuotMaster->id . ' has been sent Successfully';
                                $DebugLog->save();
                            }
                            $response = successRes("✅ \nDiscount Updated");
                            $response['data'] = $res_data;
                            $response['lead_msg'] = $lead_msg;
                        } else {
                            $QuotMaster = Wltrn_Quotation::find($quot_id);
                            $QuotMaster->updateby = Auth::user()->id; //Live
                            $QuotMaster->updateip = $request->ip();
                            $QuotMaster->status = '5';
                            $QuotMaster->save();
    
                            Wltrn_Quotation::where('quotgroup_id', $quot_group_id)->update(['isfinal' => 0]);
    
                            $response = successRes("✅ \nOver limit discount entered, \nDiscount approval request submited successfully");

                            if($QuotMaster){
                                $Lead = Lead::find($QuotMaster->inquiry_id);
                                $notificationUserids = getParentSalePersonsIds($Lead->assigned_to);
					            $notificationUserids[] = $Lead->assigned_to;
					            $UsersNotificationTokens = UsersNotificationTokens($notificationUserids);

                                sendNotificationTOAndroid("Discount request", "Discount change request generated for deal no #".$Lead->id, $UsersNotificationTokens, "LEAD", $Lead, '');
                            }
                        }
                    } catch (QueryException $ex) {
                        $response = errorRes($ex->getMessage());
                    }
                }
            
        }

        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }

    public function PostCopyRoomNBoard(Request $request)
    {
        // if (getCheckAppVersion($request->app_source, $request->app_version)) {
        $res_status = '';
        $res_status_code = http_response_code();
        $res_message = '';
        $res_data = '';
        $response = [];
        DB::enableQueryLog();

        $validator = Validator::make($request->all(), [
            'quot_id' => ['required'],
            'quotgroup_id' => ['required'],
            'room_no' => ['required'],
        ]);

        if ($validator->fails()) {
            $res_status = 0;
            $res_message = 'Please Check Perameater And Value';
            $res_data = $validator->errors();
        } else {
            $BoardSaveLog = [];
            $BoardSaveLog['quot_id'] = 0;
            $BoardSaveLog['quotgroup_id'] = 0;
            $BoardSaveLog['room_no'] = 0;
            $BoardSaveLog['board_no'] = 0;
            $BoardSaveLog['description'] = 'ROOM AND BOARD COPY : ' . json_encode($request->all());
            $BoardSaveLog['source'] = $request->app_source;
            $BoardSaveLog['entryip'] = $request->ip();
            saveBoardSaveLog($BoardSaveLog);
            try {
                if ($request->board_no != 0) {
                    $BoardNo = Wltrn_QuotItemdetail::where([['quot_id', $request->quot_id], ['quotgroup_id', $request->quotgroup_id], ['room_no', $request->room_no]])->max('board_no') + 1;

                    $board_detail_query = Wltrn_QuotItemdetail::where([['wltrn_quot_itemdetails.quot_id', $request->quot_id], ['wltrn_quot_itemdetails.quotgroup_id', $request->quotgroup_id], ['wltrn_quot_itemdetails.room_no', $request->room_no], ['wltrn_quot_itemdetails.board_no', $request->board_no]])->get();

                    foreach ($board_detail_query as $key => $value) {
                        try {
                            $board_replicate_unit = Wltrn_QuotItemdetail::find($value->id);
                            $query_board_copy = $board_replicate_unit->replicate();
                            $query_board_copy->board_no = $BoardNo;
                            $query_board_copy->board_name = $request->name;
                            $query_board_copy->copyfromboard_no = $request->board_no;
                            $query_board_copy->save();
                            $res_message = 'Board copy successfully';
                        } catch (QueryException $ex) {
                            $response = [];
                            $res_status = 0;
                            $res_message = 'Please Contact To Admin';
                            $res_data = $ex;
                        }
                    }
                } else {
                    $RoomNo = Wltrn_QuotItemdetail::where([['quot_id', $request->quot_id], ['quotgroup_id', $request->quotgroup_id]])->max('room_no') + 1;

                    $board_detail_query = Wltrn_QuotItemdetail::where([['wltrn_quot_itemdetails.quot_id', $request->quot_id], ['wltrn_quot_itemdetails.quotgroup_id', $request->quotgroup_id], ['wltrn_quot_itemdetails.room_no', $request->room_no]])->get();

                    foreach ($board_detail_query as $key => $value) {
                        try {
                            $board_replicate_unit = Wltrn_QuotItemdetail::find($value->id);
                            $query_board_copy = $board_replicate_unit->replicate();
                            $query_board_copy->room_no = $RoomNo;
                            $query_board_copy->room_name = $request->name;
                            $query_board_copy->copyfromroom_no = $request->room_no;
                            $query_board_copy->save();
                            $res_message = 'Room copy successfully';
                        } catch (QueryException $ex) {
                            $response = [];
                            $res_status = 0;
                            $res_message = 'Please Contact To Admin';
                            $res_data = $ex;
                        }
                    }
                }
                // $data = $this->ShowRoomNBoardList($request->quot_id, $request->quotgroup_id, 0, $request->room_id);
                $data = [];

                $res_status = 1;

                $res_data = $data;
            } catch (QueryException $ex) {
                $response = [];
                $res_status = 0;
                $res_message = 'Please Contact To Admin';
                $res_data = $ex;
            }
        }

        // if ($query_copy) {
        // 	$res_status = 1;
        // 	$res_status_code = 200;
        // 	$res_message = "Successfully Copy Row";
        // 	$res_data = $query_copy;

        // 	// $DebugLog = new DebugLog();
        // 	// $DebugLog->user_id = 1;
        // 	// $DebugLog->name = "quotation-sent";
        // 	// $DebugLog->description = "Quotation #" . $query_copy->id . "(" . $query->query_copy . ")" . " has been sent Successfully";
        // 	// $DebugLog->save();
        // }

        $response['status'] = $res_status;
        $response['status_code'] = $res_status_code;
        $response['msg'] = $res_message;
        $response['data'] = $res_data;
        // $response['query'] = DB::getQueryLog();
        // } else {
        // 	$response = quoterrorRes(2, 402, "Please Update Your App");
        // }

        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }

    public function PostCopyFullQuotation12(Request $request)
    {
        // if (getCheckAppVersion($request->app_source, $request->app_version)) {
        $res_status = '';
        $res_status_code = http_response_code();
        $res_message = '';
        $res_data = '';
        $res_value1 = '';
        $res_value2 = '';
        $response = [];

        $validator = Validator::make($request->all(), [
            'quot_id' => ['required'],
            'quotgroup_id' => ['required'],
        ]);

        if ($validator->fails()) {
            $res_status = 0;
            $res_message = 'Please Check Perameater And Value';
            $res_data = $validator->errors();
        } else {
            try {
                $quot_id = $request->quot_id;
                $quotgroup_id = $request->quotgroup_id;
                $entryip = $request->ip();
                $entryby = Auth::user()->id; //Live

                $new_quot = $this->PostCopyFullQuotationVersion($quot_id, $quotgroup_id, $entryip, $entryby);

                $json = json_decode($new_quot, true);

                $res_status = 1;
                $res_message = 'Quotation copy successfully';
                $res_data = $json;
                // $res_value1 = $json[0]['status'];
                // $res_value2 = $json->status;
            } catch (QueryException $ex) {
                $response = [];
                $res_status = 0;
                $res_message = 'Please Contact To Admin';
                $res_data = $ex;
            }
        }

        // if ($query_copy) {
        // 	$res_status = 1;
        // 	$res_status_code = 200;
        // 	$res_message = "Successfully Copy Row";
        // 	$res_data = $query_copy;

        // 	// $DebugLog = new DebugLog();
        // 	// $DebugLog->user_id = 1;
        // 	// $DebugLog->name = "quotation-sent";
        // 	// $DebugLog->description = "Quotation #" . $query_copy->id . "(" . $query->query_copy . ")" . " has been sent Successfully";
        // 	// $DebugLog->save();
        // }

        $response['status'] = $res_status;
        $response['status_code'] = $res_status_code;
        $response['msg'] = $res_message;
        $response['data'] = $res_data;
        $response['res_value1'] = $res_value1;
        $response['res_value2'] = $res_value2;
        // } else {
        // 	$response = quoterrorRes(2, 402, "Please Update Your App");
        // }

        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }

    public function PostChangeRoomNBoardRange(Request $request)
    {
        // if (getCheckAppVersion($request->app_source, $request->app_version)) {
        $res_status = '';
        $res_status_code = http_response_code();
        $res_message = '';
        $res_data = '';
        $response = [];

        $validator = Validator::make($request->all(), [
            'quot_id' => ['required'],
            'quotgroup_id' => ['required'],
            'range' => ['required'],
            'room_no' => ['required'],
            'board_no' => ['required'],
        ]);

        if ($validator->fails()) {
            $res_status = 0;
            $res_message = 'Please Check Perameater And Value';
            $res_data = $validator->errors();
        } else {
            // $BoardSaveLog = array();
            // 	$BoardSaveLog['quot_id'] = $request->quot_id;
            // 	$BoardSaveLog['quotgroup_id'] = $request->quotgroup_id;
            // 	$BoardSaveLog['room_no'] = $request->room_no;
            // 	$BoardSaveLog['board_no'] = $request->board_no;
            // 	$BoardSaveLog['description'] = $request->range;
            // 	$BoardSaveLog['source'] = $request->app_source;
            // 	$BoardSaveLog['entryip'] = $request->ip();
            // 	saveBoardSaveLog($BoardSaveLog);

            try {
                $Range_Subgroup = explode(',', $request->range);
                foreach ($Range_Subgroup as $rage_key => $range_value) {
                    $range_group = WlmstItemSubgroup::find($range_value)->itemgroup_id;
                    $range_subgroup = $range_value;
                    $range_company = WlmstItemSubgroup::find($range_value)->company_id;

                    if ($request->board_no != 0) {
                        /* CHNAGE BOARD RANGE */
                        $board_detail_query = Wltrn_QuotItemdetail::where([
                            ['wltrn_quot_itemdetails.quot_id', $request->quot_id],
                            ['wltrn_quot_itemdetails.quotgroup_id', $request->quotgroup_id],
                            ['wltrn_quot_itemdetails.room_no', $request->room_no],
                            ['wltrn_quot_itemdetails.board_no', $request->board_no],
                            ['wltrn_quot_itemdetails.itemgroup_id', $range_group],
                            // ['wltrn_quot_itemdetails.itemsubgroup_id', '<>', '64']
                        ])->get();
                    } elseif ($request->board_no == 'FULL') {
                        /* CHNAGE FULL QUOTATION RANGE */
                        $board_detail_query = Wltrn_QuotItemdetail::where([
                            ['wltrn_quot_itemdetails.quot_id', $request->quot_id],
                            ['wltrn_quot_itemdetails.quotgroup_id', $request->quotgroup_id],
                            ['wltrn_quot_itemdetails.itemgroup_id', $range_group],
                            // ['wltrn_quot_itemdetails.itemsubgroup_id', '<>', '64']
                        ])->get();
                        $query_default_range_update = Wltrn_Quotation::find($request->quot_id);
                        $query_default_range_update->default_range = $request->range;
                        $query_default_range_update->updateby = Auth::user()->id; //Live
                        $query_default_range_update->updateip = $request->ip();
                        $query_default_range_update->save();
                    } elseif ($request->board_no == 0) {
                        /* CHNAGE ROOM RANGE */
                        $board_detail_query = Wltrn_QuotItemdetail::where([
                            ['wltrn_quot_itemdetails.quot_id', $request->quot_id],
                            ['wltrn_quot_itemdetails.quotgroup_id', $request->quotgroup_id],
                            ['wltrn_quot_itemdetails.room_no', $request->room_no],
                            ['wltrn_quot_itemdetails.itemgroup_id', $range_group],
                            // ['wltrn_quot_itemdetails.itemsubgroup_id', '<>', '64']
                        ])->get();
                    }

                    foreach ($board_detail_query as $key => $value) {
                        $board_item_change = Wltrn_QuotItemdetail::find($value->id);
                        if ($request->board_no != 0) {
                            /* CHNAGE BOARD RANGE */
                            $board_item_change->board_range = $request->range;

                        } elseif ($request->board_no == 'FULL') {
                            /* CHNAGE FULL QUOTATION RANGE */
                            $board_item_change->board_range = $request->range;
                            $board_item_change->room_range = $request->range;

                        } elseif ($request->board_no == 0) {
                            /* CHNAGE ROOM RANGE */
                            $board_item_change->board_range = $request->range;
                            $board_item_change->room_range = $request->range;
                        }
                        $board_item_change->save();

                        if ($value->itemsubgroup_id == 62 || $value->itemsubgroup_id == 63 || $value->itemsubgroup_id == 64) {
                        } else {
                            $item_price_detail = Wlmst_ItemPrice::where([['wlmst_item_prices.company_id', $range_company], ['wlmst_item_prices.itemgroup_id', $range_group], ['wlmst_item_prices.itemsubgroup_id', $range_subgroup], ['wlmst_item_prices.item_id', $value->item_id]])->first();

                            if ($item_price_detail) {
                                $QuotationMaster = Wltrn_Quotation::find($request->quot_id);
                                $ItemMaster = WlmstItem::find($item_price_detail->item_id);

                                $SubTotal = round(floatval($item_price_detail->mrp) * floatval($value->qty));
                                // $SubTotal = floatval($item_price_detail->mrp) * floatval($value->qty);
                                $Discount_Amount = (floatval($SubTotal) * floatval($item_price_detail->discount)) / 100;
                                $GrossAmount = floatval($SubTotal) - floatval($Discount_Amount);

                                if ($QuotationMaster->site_state_id == '9' /*IS GUJARAT*/) {
                                    /* CGST CALCULATION */
                                    $CGST_Per = $ItemMaster->cgst_per;
                                    $CGST_Amount = (floatval($GrossAmount) * floatval($ItemMaster->cgst_per)) / 100;
                                    /* SGST CALCULATION */
                                    $SGST_Per = $ItemMaster->sgst_per;
                                    $SGST_Amount = (floatval($GrossAmount) * floatval($ItemMaster->sgst_per)) / 100;
                                    /* IGST CALCULATION */
                                    $IGST_Per = '0.00';
                                    $IGST_Amount = '0.00';

                                    /* NET AMOUNT CALCULATION */
                                    $NetTotalAmount = floatval($GrossAmount) + floatval($CGST_Amount) + floatval($SGST_Amount);
                                    /* ROUND_UP AMOUNT CALCULATION */
                                    $RoundUpAmount = floatval($NetTotalAmount) - floatval(round($NetTotalAmount));
                                    /* NET FINAL AMOUNT CALCULATION */
                                    $NeteAmount = round($NetTotalAmount);
                                } else {
                                    /* CGST CALCULATION */
                                    $CGST_Per = '0';
                                    $CGST_Amount = '0.00';
                                    /* SGST CALCULATION */
                                    $SGST_Per = '0';
                                    $SGST_Amount = '0.00';
                                    /* IGST CALCULATION */
                                    $IGST_Per = $ItemMaster->igst_per;
                                    $IGST_Amount = (floatval($GrossAmount) * floatval($ItemMaster->igst_per)) / 100;

                                    /* NET AMOUNT CALCULATION */
                                    $NetTotalAmount = floatval($GrossAmount) + floatval($IGST_Amount);
                                    /* ROUND_UP AMOUNT CALCULATION */
                                    $RoundUpAmount = floatval($NetTotalAmount) - floatval(round($NetTotalAmount));
                                    /* NET FINAL AMOUNT CALCULATION */
                                    $NeteAmount = round($NetTotalAmount);
                                }

                                $board_item_change = Wltrn_QuotItemdetail::find($value->id);
                                $board_item_change->company_id = $item_price_detail->company_id;
                                $board_item_change->itemgroup_id = $item_price_detail->itemgroup_id;
                                $board_item_change->itemsubgroup_id = $item_price_detail->itemsubgroup_id;
                                $board_item_change->itemcategory_id = $ItemMaster->itemcategory_id;
                                $board_item_change->item_id = $item_price_detail->item_id;
                                $board_item_change->itemcode = $item_price_detail->code;
                                $board_item_change->rate = $item_price_detail->mrp;

                                // $board_item_change->discper = $value->discper;
                                // $board_item_change->discamount = $value->discamount;
                                $board_item_change->grossamount = $GrossAmount;
                                // $board_item_change->addamount = $value->addamount;
                                // $board_item_change->lessamount = $value->lessamount;
                                $board_item_change->taxableamount = $GrossAmount;

                                $board_item_change->igst_per = $IGST_Per;
                                $board_item_change->igst_amount = $IGST_Amount;

                                $board_item_change->cgst_per = $CGST_Per;
                                $board_item_change->cgst_amount = $CGST_Amount;

                                $board_item_change->sgst_per = $SGST_Per;
                                $board_item_change->sgst_amount = $SGST_Amount;

                                $board_item_change->roundup_amount = $RoundUpAmount;
                                $board_item_change->net_amount = $NeteAmount;

                                $board_item_change->item_price_id = $item_price_detail->id;

                                // if ($request->board_no != 0) {
                                // 	/* CHNAGE BOARD RANGE */
                                // 	$QuotBoardItemUpdate = Wltrn_QuotItemdetail::where([
                                // 		['wltrn_quot_itemdetails.quot_id', $board_item_change->quot_id],
                                // 		['wltrn_quot_itemdetails.quotgroup_id', $board_item_change->quotgroup_id],
                                // 		['wltrn_quot_itemdetails.room_no', $board_item_change->room_no],
                                // 		['wltrn_quot_itemdetails.board_no', $board_item_change->board_no],
                                // 	]);
                                // 	$QuotBoardItemUpdate->update(['board_range' => $request->range]);

                                // 	$board_item_change->board_range = $request->range;
                                // } else if ($request->board_no == 'FULL') {
                                // 	/* CHNAGE FULL QUOTATION RANGE */
                                // 	$QuotBoardItemUpdate = Wltrn_QuotItemdetail::where([
                                // 		['wltrn_quot_itemdetails.quot_id', $board_item_change->quot_id],
                                // 		['wltrn_quot_itemdetails.quotgroup_id', $board_item_change->quotgroup_id]
                                // 	]);
                                // 	$QuotBoardItemUpdate->update(['board_range' => $request->range,'room_range' => $request->range]);

                                // 	$board_item_change->board_range = $request->range;
                                // 	$board_item_change->room_range = $request->range;
                                // } else if ($request->board_no == 0) {
                                // 	/* CHNAGE ROOM RANGE */
                                // 	$QuotBoardItemUpdate = Wltrn_QuotItemdetail::where([
                                // 		['wltrn_quot_itemdetails.quot_id', $board_item_change->quot_id],
                                // 		['wltrn_quot_itemdetails.quotgroup_id', $board_item_change->quotgroup_id],
                                // 		['wltrn_quot_itemdetails.room_no', $board_item_change->room_no],
                                // 	]);
                                // 	$QuotBoardItemUpdate->update(['board_range' => $request->range,'room_range' => $request->range]);
                                // 	$board_item_change->board_range = $request->range;
                                // 	$board_item_change->room_range = $request->range;
                                // }

                                if ($item_price_detail->itemgroup_id == '4') {
                                    $QuotBoardItemUpdate = Wltrn_QuotItemdetail::where([['wltrn_quot_itemdetails.quot_id', $board_item_change->quot_id], ['wltrn_quot_itemdetails.quotgroup_id', $board_item_change->quotgroup_id], ['wltrn_quot_itemdetails.room_no', $board_item_change->room_no], ['wltrn_quot_itemdetails.board_no', $board_item_change->board_no]]);
                                    $QuotBoardItemUpdate->update(['board_item_id' => $item_price_detail->item_id, 'board_item_price_id' => $item_price_detail->id, 'board_size' => $ItemMaster->module]);

                                    $board_item_change->board_item_id = $item_price_detail->item_id;
                                    $board_item_change->board_item_price_id = $item_price_detail->id;
                                }
                                $board_item_change->updateby = Auth::user()->id; //Live
                                $board_item_change->updateip = $request->ip();
                                $board_item_change->save();
                            } else {
                                $board_item_change = Wltrn_QuotItemdetail::find($value->id);
                                $board_item_change->company_id = '0';
                                $board_item_change->itemgroup_id = '0';
                                $board_item_change->itemsubgroup_id = '0';
                                $board_item_change->itemcategory_id = '0';
                                $board_item_change->itemcode = '0';
                                $board_item_change->rate = '0';

                                $board_item_change->grossamount = '0';
                                $board_item_change->taxableamount = '0';
                                $board_item_change->igst_per = '0';
                                $board_item_change->igst_amount = '0';
                                $board_item_change->cgst_per = '0';
                                $board_item_change->cgst_amount = '0';
                                $board_item_change->sgst_per = '0';
                                $board_item_change->sgst_amount = '0';
                                $board_item_change->net_amount = '0';
                                $board_item_change->item_price_id = '0';

                                if ($request->board_no != 0) {
                                    /* CHNAGE BOARD RANGE */
                                    $board_item_change->board_range = $request->range;
                                } elseif ($request->board_no == 'FULL') {
                                    /* CHNAGE FULL QUOTATION RANGE */
                                    $board_item_change->board_range = $request->range;
                                    $board_item_change->room_range = $request->range;
                                } elseif ($request->board_no == 0) {
                                    /* CHNAGE ROOM RANGE */
                                    $board_item_change->board_range = $request->range;
                                    $board_item_change->room_range = $request->range;
                                }
                                $board_item_change->status = '2';
                                $board_item_change->updateby = Auth::user()->id; //Live

                                $board_item_change->updateip = $request->ip();
                                $board_item_change->save();

                                $error_query = new Wlmst_QuotationError();
                                $error_query->quot_id = $request->quot_id;
                                $error_query->quotgroup_id = $request->quotgroup_id;
                                $error_query->quotitemdetail_id = $value->id;
                                $error_query->srno = $value->srno;
                                $error_query->floorno = $value->floor_no;
                                $error_query->roomno = $value->room_no;
                                $error_query->boardno = $value->board_no;

                                $error_query->old_company_id = $value->company_id;
                                $error_query->old_itemgroup_id = $value->itemgroup_id;
                                $error_query->old_itemsubgroup_id = $value->itemsubgroup_id;
                                $error_query->old_itemcategory_id = $value->itemcategory_id;
                                $error_query->old_item_id = $value->item_id;
                                $error_query->old_itemcode = $value->itemcode;
                                $error_query->old_item_price_id = $value->item_price_id;

                                $error_query->new_company_id = $range_company;
                                $error_query->new_itemgroup_id = $range_group;
                                $error_query->new_itemsubgroup_id = $range_subgroup;
                                $error_query->new_itemcategory_id = '0';
                                $error_query->new_item_id = '0';
                                $error_query->new_itemcode = '0';
                                $error_query->new_item_price_id = '0';
                                $error_query->description = 'In This Range Some Product Mismatch';
                                $error_query->status = '400';
                                $error_query->entryby = Auth::user()->id; //Live

                                $error_query->entryip = $request->ip();
                                $error_query->save();
                            }
                        }
                    }
                }

                $chk_error_columns = [
                    'wlmst_quotation_errors.quot_id',
                    'wlmst_quotation_errors.quotgroup_id',
                    'wlmst_quotation_errors.quotitemdetail_id',
                    'wlmst_quotation_errors.srno',
                    'wlmst_quotation_errors.floorno',
                    'wlmst_quotation_errors.roomno',
                    'wlmst_quotation_errors.old_company_id',
                    'wlmst_quotation_errors.old_itemgroup_id',
                    'wlmst_quotation_errors.old_itemsubgroup_id',
                    'wlmst_quotation_errors.old_itemcategory_id',
                    'wlmst_quotation_errors.old_item_id',
                    'wlmst_quotation_errors.old_itemcode',
                    'wlmst_quotation_errors.old_item_price_id',
                    'wlmst_quotation_errors.old_range',
                    'wlmst_quotation_errors.new_range',
                    'wlmst_quotation_errors.new_company_id',
                    'wlmst_quotation_errors.new_itemgroup_id',
                    'wlmst_quotation_errors.new_itemsubgroup_id',
                    'wlmst_quotation_errors.new_itemcategory_id',
                    'wlmst_quotation_errors.new_item_id',
                    'wlmst_quotation_errors.new_itemcode',
                    'wlmst_quotation_errors.new_item_price_id',
                    'wlmst_quotation_errors.description',
                    'wlmst_quotation_errors.status',
                    'wlmst_quotation_errors.boardno',
                ];

                $error_data_query = Wlmst_QuotationError::query();
                $error_data_query->select($chk_error_columns);
                // $error_data_query->leftJoin('country_list', 'country_list.id', '=', 'wltrn_quotation.site_country_id');

                if ($request->board_no != 0) {
                    /* CHNAGE BOARD RANGE */
                    $error_data_query->where([['wlmst_quotation_errors.quot_id', $request->quot_id], ['wlmst_quotation_errors.quotgroup_id', $request->quotgroup_id], ['wlmst_quotation_errors.roomno', $request->room_no], ['wlmst_quotation_errors.boardno', $request->board_no], ['wlmst_quotation_errors.status', '400']]);
                } elseif ($request->board_no == 'FULL') {
                    /* CHNAGE FULL QUOTATION RANGE */
                    $error_data_query->where([['wlmst_quotation_errors.quot_id', $request->quot_id], ['wlmst_quotation_errors.quotgroup_id', $request->quotgroup_id], ['wlmst_quotation_errors.status', '400']]);
                } elseif ($request->board_no == 0) {
                    /* CHNAGE ROOM RANGE */
                    $error_data_query->where([['wlmst_quotation_errors.quot_id', $request->quot_id], ['wlmst_quotation_errors.quotgroup_id', $request->quotgroup_id], ['wlmst_quotation_errors.roomno', $request->room_no], ['wlmst_quotation_errors.status', '400']]);
                }
                $error_data = $error_data_query->get();
                if (count($error_data) >= 1) {
                    $QuotMaster = Wltrn_Quotation::find($request->quot_id);
                    $QuotMaster->updateby = Auth::user()->id; //Live
                    $QuotMaster->updateip = $request->ip();
                    $QuotMaster->status = '6';
                    $QuotMaster->save();

                    $res_status = 1;
                    $res_message = 'In This Range Change Time Some Product Mismatch';
                } else {
                    $res_status = 1;
                    $res_message = 'Range Change successfully';
                }

                $res_data = $error_data;
            } catch (QueryException $ex) {
                $response = [];
                $res_status = 0;
                $res_message = 'Please Contact To Admin';
                $res_data = $ex;
            }
        }

        $response['status'] = $res_status;
        $response['status_code'] = $res_status_code;
        $response['msg'] = $res_message;
        $response['data'] = $res_data;
        // } else {
        // 	$response = quoterrorRes(2, 402, "Please Update Your App");
        // }
        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }

    public function PostRoomNBoardRename(Request $request)
    {
        // if (getCheckAppVersion($request->app_source, $request->app_version)) {
        $res_status = '';
        $res_status_code = '';
        $res_message = '';

        $validator = Validator::make($request->input(), [
            'title' => ['required'],
            'quot_id' => ['required'],
            'quotgroup_id' => ['required'],
            'room_no' => ['required'],
        ]);

        if ($validator->fails()) {
            $res_status = 0;
            $res_message = 'please check perameater and value';
            $res_status_code = 400;
        } else {
            $Title = $request->title;
            $Name = $request->name;
            $quot_id = $request->quot_id;
            $quotgroup_id = $request->quotgroup_id;

            if ($Title == 'ROOM') {
                if (isset($request->room_no)) {
                    $room_no = $request->room_no;
                    $QuotItemdetail = Wltrn_QuotItemdetail::where([['wltrn_quot_itemdetails.quot_id', $quot_id], ['wltrn_quot_itemdetails.quotgroup_id', $quotgroup_id], ['wltrn_quot_itemdetails.room_no', $room_no]]);
                    $QuotItemdetail->update(['room_name' => $Name, 'updateby' => Auth::user()->id, 'updateip' => $request->ip()]); //Live

                    $DebugLog = new DebugLog();
                    $DebugLog->user_id = 1;
                    $DebugLog->name = 'quotation-sent';
                    $DebugLog->description =
                        "Quotation Item Detail Room Name has been Updated
      (#Quote ID = " .
                        $quot_id .
                        ")
      (#Quote Group ID = " .
                        $quotgroup_id .
                        ")
      (#Room No = " .
                        $room_no .
                        ')';
                    $DebugLog->save();

                    $res_status = 1;
                    $res_status_code = 200;
                    $res_message = 'successfully rename room name';
                } else {
                    $res_status = 0;
                    $res_message = 'please check perameater and value';
                    $res_status_code = 400;
                }
            } elseif ($Title == 'BOARD') {
                if (isset($request->board_no)) {
                    $board_no = $request->board_no;
                    $room_no = $request->room_no;
                    $QuotItemdetail = Wltrn_QuotItemdetail::query();
                    $QuotItemdetail->where('wltrn_quot_itemdetails.quot_id', '=', $quot_id);
                    $QuotItemdetail->where('wltrn_quot_itemdetails.quotgroup_id', '=', $quotgroup_id);
                    $QuotItemdetail->where('wltrn_quot_itemdetails.room_no', '=', $room_no);
                    $QuotItemdetail->where('wltrn_quot_itemdetails.board_no', '=', $board_no);
                    $QuotItemdetail->update(['board_name' => $Name, 'updateby' => Auth::user()->id, 'updateip' => $request->ip()]);

                    $DebugLog = new DebugLog();
                    $DebugLog->user_id = 1;
                    $DebugLog->name = 'quotation-sent';
                    $DebugLog->description =
                        "Quotation Item Detail Board Name has been Updated
      (#Quote ID = " .
                        $quot_id .
                        ")
      (#Quote Group ID = " .
                        $quotgroup_id .
                        ")
      (#Room No = " .
                        $room_no .
                        ")
      (#Board No = " .
                        $board_no .
                        ')';

                    $DebugLog->save();

                    $res_status = 1;
                    $res_status_code = 200;
                    $res_message = 'successfully rename board name';
                } else {
                    $res_status = 0;
                    $res_message = 'please check perameater and value';
                    $res_status_code = 400;
                }
            } else {
                $res_status = 0;
                $res_message = 'please check perameater and value';
                $res_status_code = 400;
            }
        }

        $response['status'] = $res_status;
        $response['status_code'] = $res_status_code;
        $response['msg'] = $res_message;
        // } else {
        // 	$response = quoterrorRes(2, 402, "Please Update Your App");
        // }
        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }

    public function PostRoomNBoardStatus(Request $request)
    {
        // if (getCheckAppVersion($request->app_source, $request->app_version)) {
        $res_status = '';
        $res_status_code = '';
        $res_message = '';

        $validator = Validator::make($request->input(), [
            'title' => ['required'],
            'quot_id' => ['required'],
            'quotgroup_id' => ['required'],
            'room_no' => ['required'],
        ]);

        if ($validator->fails()) {
            $res_status = 0;
            $res_message = 'please check perameater and value';
            $res_status_code = 400;
        } else {
            $Title = $request->title;
            $Status = $request->status;
            $quot_id = $request->quot_id;
            $quotgroup_id = $request->quotgroup_id;

            if ($Title == 'ROOM') {
                if (isset($request->room_no)) {
                    $room_no = $request->room_no;
                    $QuotItemdetail = Wltrn_QuotItemdetail::where([['wltrn_quot_itemdetails.quot_id', $quot_id], ['wltrn_quot_itemdetails.quotgroup_id', $quotgroup_id], ['wltrn_quot_itemdetails.room_no', $room_no]]);
                    $QuotItemdetail->update(['isactiveroom' => $Status, 'isactiveboard' => $Status, 'updateby' => Auth::user()->id, 'updateip' => $request->ip()]); //Live

                    $DebugLog = new DebugLog();
                    $DebugLog->user_id = 1;
                    $DebugLog->name = 'quotation-sent';
                    $DebugLog->description = "Quotation Item Detail Room Status has been Updated (#Quote ID = " .$quot_id .") (#Quote Group ID = " .$quotgroup_id .") (#Room No = " .$room_no .')';
                    $DebugLog->save();

                    $res_status = 1;
                    $res_status_code = 200;
                    $res_message = 'successfully status change';
                } else {
                    $res_status = 0;
                    $res_message = 'please check room number';
                    $res_status_code = 400;
                }
            } elseif ($Title == 'BOARD') {
                if (isset($request->board_no)) {
                    $room_no = $request->room_no;
                    $board_no = $request->board_no;
                    $QuotItemdetail = Wltrn_QuotItemdetail::where([['wltrn_quot_itemdetails.quot_id', $quot_id], ['wltrn_quot_itemdetails.quotgroup_id', $quotgroup_id], ['wltrn_quot_itemdetails.room_no', $room_no], ['wltrn_quot_itemdetails.board_no', $board_no]]);
                    $QuotItemdetail->update(['isactiveboard' => $Status, 'updateby' => Auth::user()->id, 'updateip' => $request->ip()]); //Live

                    $DebugLog = new DebugLog();
                    $DebugLog->user_id = 1;
                    $DebugLog->name = 'quotation-sent';
                    $DebugLog->description =
                        "Quotation Item Detail Board Status has been Updated
      (#Quote ID = " .
                        $quot_id .
                        ")
      (#Quote Group ID = " .
                        $quotgroup_id .
                        ")
      (#Board No = " .
                        $board_no .
                        ')';
                    $DebugLog->save();

                    $res_status = 1;
                    $res_status_code = 200;
                    $res_message = 'successfully status change';
                } else {
                    $res_status = 0;
                    $res_message = 'please check board number';
                    $res_status_code = 400;
                }
            } else {
                $res_status = 0;
                $res_message = 'please check perameater and value';
                $res_status_code = 400;
            }
        }

        $response['status'] = $res_status;
        $response['status_code'] = $res_status_code;
        $response['msg'] = $res_message;
        // } else {
        // 	$response = quoterrorRes(2, 402, "Please Update Your App");
        // }
        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }

    public function ShowRoomNBoardList($quot_id, $quotgroup_id, $quot_srno, $quot_room_id)
    {
        /* *********************************************************************************** ROOM LIST *********************************************************************************** */
        $room_columns = [
            0 => 'wltrn_quot_itemdetails.room_no',
            1 => 'wltrn_quot_itemdetails.room_name',
            2 => 'wltrn_quot_itemdetails.room_range',
            3 => 'wltrn_quot_itemdetails.quot_id',
            4 => 'wltrn_quot_itemdetails.quotgroup_id',
            5 => 'wltrn_quot_itemdetails.srno',
            6 => 'wltrn_quot_itemdetails.isactiveroom',
        ];
        $room_query = Wltrn_QuotItemdetail::query();
        $room_query->select($room_columns);
        $room_query->where([['wltrn_quot_itemdetails.quot_id', $quot_id], ['wltrn_quot_itemdetails.quotgroup_id', $quotgroup_id]]);
        // $room_query->groupBy(['wltrn_quot_itemdetails.room_no']);
        $room_query->groupBy($room_columns);
        $room_query->orderBy('wltrn_quot_itemdetails.room_no', 'ASC');

        $roomlist = $room_query->get();
        $quot_room_array = [];
        foreach ($roomlist as $key => $value) {
            $quot_f_array = [];

            if ($roomlist[$key]['room_range'] != '' || $roomlist[$key]['room_range'] != null) {
                $Range_Subgroup = explode(',', $roomlist[$key]['room_range']);
                // $Range_Subgroup = $quotlist[$key]['default_range'];
                $range_group = '';
                $range_company = '';
                for ($i = 0; $i < count($Range_Subgroup); $i++) {
                    $range_group .= WlmstItemSubgroup::find($Range_Subgroup[$i])->itemgroup_id . ',';
                    $range_company .= WlmstItemSubgroup::find($Range_Subgroup[$i])->company_id . ',';
                }
                $range_group_f = substr($range_group, 0, -1);
                // $range_company_f = substr($range_company,0,-1);
                $range_company_f = explode(',', $range_company)[0];
            } else {
                $range_group_f = '';
                $range_company_f = '';
            }

            $quot_f_array = $value;
            $quot_f_array['range_group'] = $range_group_f;
            $quot_f_array['range_company'] = $range_company_f;
            array_push($quot_room_array, $quot_f_array);
        }
        /* *********************************************************************************** BOARD LIST *********************************************************************************** */
        $board_columns = [
            0 => 'wltrn_quot_itemdetails.id',
            1 => 'wltrn_quot_itemdetails.board_no',
            2 => 'wltrn_quot_itemdetails.board_name',
            3 => 'wltrn_quot_itemdetails.isactiveboard',
            5 => 'wltrn_quot_itemdetails.board_range',
            6 => 'wlmst_items.itemname',
            7 => 'wltrn_quot_itemdetails.board_image',
            8 => 'wltrn_quot_itemdetails.quot_id',
            9 => 'wltrn_quot_itemdetails.quotgroup_id',
            10 => 'wltrn_quot_itemdetails.srno',
            11 => 'wltrn_quot_itemdetails.isactiveboard',
            12 => 'wltrn_quot_itemdetails.room_no',
        ];
        $board_query = Wltrn_QuotItemdetail::query();
        $board_query->select($board_columns);
        $board_query->leftJoin('wlmst_items', 'wltrn_quot_itemdetails.board_item_id', '=', 'wlmst_items.id');
        $board_query->where([['wltrn_quot_itemdetails.quot_id', $quot_id], ['wltrn_quot_itemdetails.quotgroup_id', $quotgroup_id], ['wltrn_quot_itemdetails.room_no', $quot_room_id]]);
        $board_query->where('wltrn_quot_itemdetails.board_no', '!=', 0);
        // $board_query->groupBy(['wltrn_quot_itemdetails.board_no']);
        $board_query->groupBy($board_columns);

        // $data = $board_query->get();
        $boardlist = $board_query->get();
        $board_array = [];
        foreach ($boardlist as $key => $value) {
            $quot_f_array = [];

            if ($boardlist[$key]['board_range'] != '' || $boardlist[$key]['board_range'] != null) {
                $Range_Subgroup = explode(',', $boardlist[$key]['board_range']);
                $range_group = '';
                $range_company = '';
                for ($i = 0; $i < count($Range_Subgroup); $i++) {
                    $range_group .= WlmstItemSubgroup::find($Range_Subgroup[$i])->itemgroup_id . ',';
                    $range_company .= WlmstItemSubgroup::find($Range_Subgroup[$i])->company_id . ',';
                }
                $range_group_f = substr($range_group, 0, -1);
                // $range_company_f = substr($range_company,0,-1);
                $range_company_f = explode(',', $range_company)[0];
            } else {
                $range_group_f = '';
                $range_company_f = '';
            }

            $quot_f_array = $value;
            $quot_f_array['image'] = getSpaceFilePath($value->board_image);
            // $quot_f_array['image'] = "http://103.218.110.153:623/whitelion_new/public" . $value->board_image;
            $quot_f_array['range_group'] = $range_group_f;
            $quot_f_array['range_company'] = $range_company_f;
            array_push($board_array, $quot_f_array);
        }
        $res_data = ['room' => $quot_room_array, 'board' => $board_array];
        return $res_data; // it will return room & board list
    }

    public function PostCopyFullQuotation(Request $request)
    {
        // if (getCheckAppVersion($request->app_source, $request->app_version)) {
        $response = [];
        $status = 0;
        $status_code = http_response_code();
        $msg = 'api';
        $data = null;

        try {
            $quot_id = $request->quot_id;
            $quotgroup_id = $request->quotgroup_id;
            $entryip = $request->ip();
            $entryby = Auth::user()->id; //Live

            $new_quot_no_str = Wltrn_Quotation::selectRaw('max(wltrn_quotation.quot_no_str + 0.01) as newversion')
                ->where('quotgroup_id', $quotgroup_id)
                ->first();

            $quot_replicate_unit = Wltrn_Quotation::find($quot_id);
            if ($quot_replicate_unit->quottype_id == 4) {
                $response = successRes("This is manual quotation so you Can't copy this quotation");
                return response()
                    ->json($response)
                    ->header('Content-Type', 'application/json');
            } elseif ($quot_replicate_unit->default_range == '' || $quot_replicate_unit->default_range == null) {
                $response = successRes("Range not selected in this quotation, so you can't copy it!");
                return response()
                    ->json($response)
                    ->header('Content-Type', 'application/json');
            }
            if ((int) $quot_replicate_unit->status == 1 || (int) $quot_replicate_unit->status == 2 || (int) $quot_replicate_unit->status == 3 || (int) $quot_replicate_unit->status == 5) {
                $query_quot_copy = $quot_replicate_unit->replicate();
                $query_quot_copy->quot_no_str = $new_quot_no_str->newversion;
                $query_quot_copy->quot_date = date('Y-m-d');
                $query_quot_copy->created_at = date('Y-m-d h:i:s');
                $query_quot_copy->isfinal = 0;
                $query_quot_copy->status = 0;
                $query_quot_copy->entryby = $entryby;
                $query_quot_copy->entryip = $entryip;
                $query_quot_copy->updateby = $entryby;
                $query_quot_copy->updateip = $entryip;
                $query_quot_copy->copyfromquotation_id = $quot_id;
                $query_quot_copy->save();

                $board_detail_query = Wltrn_QuotItemdetail::where([['wltrn_quot_itemdetails.quot_id', $quot_id], ['wltrn_quot_itemdetails.quotgroup_id', $quotgroup_id]])->get();

                foreach ($board_detail_query as $key => $value) {
                    $board_replicate_unit = Wltrn_QuotItemdetail::find($value->id);
                    $query_board_copy = $board_replicate_unit->replicate();
                    $query_board_copy->quot_id = $query_quot_copy->id;
                    $query_board_copy->entryby = $entryby;
                    $query_board_copy->entryip = $entryip;
                    $query_board_copy->updateby = $entryby;
                    $query_board_copy->updateip = $entryip;
                    $query_board_copy->save();
                }
                $response = [];
                $status = 1;
                $msg = 'Quotation Copy Successfully';
                $data = $query_quot_copy;
            } else {
                $response = [];
                $status = 1;
                $msg = 'This Quotation Is Already ' . getQuotationMasterStatusLableText($quot_replicate_unit->status);
                $data = '';
            }
        } catch (QueryException $ex) {
            $response = [];
            $status = 0;
            $msg = 'Please Contact To Admin';
            $data = $ex;
        }

        $response['status'] = $status;
        $response['status_code'] = $status_code;
        $response['msg'] = $msg;
        $response['data'] = $data;
        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }

    // //NEW UPDATE START
    public function PostDownloadPrint(Request $request)
    {
        $pdf_filter_array = json_decode($request->visible_array, true);
        $visible_array = $pdf_filter_array[0];

        $objQuotation = Wltrn_Quotation::find($request->quot_id);
        if((int)$objQuotation->quottype_id == 4){
            $response['data']['size'] = 0;
            $response['data']['url'] = getSpaceFilePath($objQuotation->quotation_file);
            $response['data']['view'] = '';
            return response()->json($response)->header('Content-Type', 'application/json');
        }

        $old_QuotItemdetail = Wltrn_QuotItemdetail::where([['wltrn_quot_itemdetails.quot_id', $request->quot_id], ['wltrn_quot_itemdetails.quotgroup_id', $request->quotgroup_id], ['wltrn_quot_itemdetails.isactiveroom', 1], ['wltrn_quot_itemdetails.isactiveboard', 1]]);
        $old_QuotItemdetail->update(['is_appendix' => 0]);

        $appendix_columns = ['wlmst_items.additional_info', 'wltrn_quot_itemdetails.item_id'];

        $appendix_query = Wltrn_QuotItemdetail::query();
        $appendix_query->select($appendix_columns);
        $appendix_query->leftJoin('wlmst_items', 'wlmst_items.id', '=', 'wltrn_quot_itemdetails.item_id');
        $appendix_query->groupBy($appendix_columns);
        $appendix_query->where([['wltrn_quot_itemdetails.quot_id', $request->quot_id], ['wltrn_quot_itemdetails.quotgroup_id', $request->quotgroup_id], ['wltrn_quot_itemdetails.isactiveroom', 1], ['wltrn_quot_itemdetails.isactiveboard', 1]]);
        $appendix_query = $appendix_query->get();

        foreach ($appendix_query as $key => $appendix_value) {
            if ($appendix_value->additional_info != null) {
                $appendix_count = Wltrn_QuotItemdetail::where([['wltrn_quot_itemdetails.quot_id', $request->quot_id], ['wltrn_quot_itemdetails.quotgroup_id', $request->quotgroup_id], ['wltrn_quot_itemdetails.isactiveroom', 1], ['wltrn_quot_itemdetails.isactiveboard', 1]])->max('is_appendix') + 1;

                $QuotItemdetail = Wltrn_QuotItemdetail::where([['wltrn_quot_itemdetails.quot_id', $request->quot_id], ['wltrn_quot_itemdetails.quotgroup_id', $request->quotgroup_id], ['wltrn_quot_itemdetails.item_id', $appendix_value->item_id], ['wltrn_quot_itemdetails.isactiveroom', 1], ['wltrn_quot_itemdetails.isactiveboard', 1]]);
                $QuotItemdetail->update(['is_appendix' => $appendix_count]);
            }
        }

        $lst_appendix_columns = ['wlmst_items.additional_info', 'wltrn_quot_itemdetails.item_id', 'wltrn_quot_itemdetails.is_appendix'];

        $lst_appendix_query = Wltrn_QuotItemdetail::query();
        $lst_appendix_query->select($lst_appendix_columns);
        $lst_appendix_query->leftJoin('wlmst_items', 'wlmst_items.id', '=', 'wltrn_quot_itemdetails.item_id');
        $lst_appendix_query->groupBy($lst_appendix_columns);
        $lst_appendix_query->where([['wltrn_quot_itemdetails.quot_id', $request->quot_id], ['wltrn_quot_itemdetails.quotgroup_id', $request->quotgroup_id], ['wltrn_quot_itemdetails.is_appendix', '!=', '0'], ['wltrn_quot_itemdetails.isactiveroom', 1], ['wltrn_quot_itemdetails.isactiveboard', 1]]);
        $lst_appendix_query = $lst_appendix_query->get();
        $data['lstappendix'] = $lst_appendix_query;
        $data['appendix_count'] = (int) count($lst_appendix_query);

        $columns = [
            'wltrn_quotation.id',
            'wltrn_quotation.site_name',
            'wltrn_quotation.customer_name',
            'wltrn_quotation.customer_contact_no',
            'wltrn_quotation.siteaddress',
            'wltrn_quotation.site_city_id',
            'wltrn_quotation.inquiry_id',
            'city_list.name AS city_name',
            'wltrn_quotation.site_state_id',
            'state_list.name AS state_name',
            'wltrn_quotation.site_pincode',
            'wltrn_quotation.yy',
            'wltrn_quotation.mm',
            'wltrn_quotation.quotno',
            'wltrn_quotation.quot_no_str',
            'wltrn_quotation.quottype_id',
            'quot_type.name AS quot_type',
            'wltrn_quotation.customer_id',
            'wlmst_client.email',
            'wltrn_quotation.architech_id',
            'wltrn_quotation.electrician_id',
            'wltrn_quotation.salesexecutive_id',
            'wltrn_quotation.channelpartner_id',
            'channel_partner.first_name as channel_partner_first_name',
            'channel_partner.last_name as channel_partner_last_name',
            'channel_partner.email as channel_partner_email',
            'channel_partner.phone_number as channel_partner_mobile_number',
            'consultant.first_name as consultant_first_name',
            'consultant.last_name as consultant_last_name',
            'consultant.phone_number as consultant_phone_number',
            'consultant.email as consultant_email',
        ];

        $QuotationBasic = Wltrn_Quotation::query();
        $QuotationBasic->select($columns);
        $QuotationBasic->selectRaw('DATE_FORMAT(wltrn_quotation.quot_date,"%d-%m-%Y") as quot_date');
        $QuotationBasic->leftJoin('city_list', 'city_list.id', '=', 'wltrn_quotation.site_city_id');
        $QuotationBasic->leftJoin('state_list', 'state_list.id', '=', 'wltrn_quotation.site_state_id');
        $QuotationBasic->leftJoin('wlmst_quotation_type as quot_type', 'quot_type.id', '=', 'wltrn_quotation.quottype_id');
        $QuotationBasic->leftJoin('wlmst_client as wlmst_client', 'wlmst_client.id', '=', 'wltrn_quotation.customer_id');
        $QuotationBasic->leftJoin('users as channel_partner', 'channel_partner.id', '=', 'wltrn_quotation.channelpartner_id');
        $QuotationBasic->leftJoin('users as consultant', 'consultant.id', '=', 'wltrn_quotation.entryby');
        $QuotationBasic->where('wltrn_quotation.id', $request->quot_id);

        $Quot_Basic_Detail = $QuotationBasic->first();

        if ($Quot_Basic_Detail) {
            $LeadDetail = Lead::find($Quot_Basic_Detail['inquiry_id']);
            $SiteAddress = '';
            $CustomerName = '';
            if ($LeadDetail) {

                $ch_type_arr = ['user-101', 'user-102', 'user-103', 'user-104', 'user-105'];
                $Lead_source = LeadSource::select('users.first_name','users.last_name','users.email','users.phone_number');
                $Lead_source->leftJoin('users', 'users.id', '=', 'lead_sources.source');
                $Lead_source->where('lead_sources.lead_id', $LeadDetail->id);
                $Lead_source->where('lead_sources.source','!=', '');
                $Lead_source->whereIn('lead_sources.source_type', $ch_type_arr);
                $Lead_source = $Lead_source->orderBy('lead_sources.id', 'DESC')->first();
                if($Lead_source){
                    $Quot_Basic_Detail['channel_partner_first_name'] = $Lead_source->first_name;
                    $Quot_Basic_Detail['channel_partner_last_name'] = $Lead_source->last_name;
                    $Quot_Basic_Detail['channel_partner_email'] = $Lead_source->email;
                    $Quot_Basic_Detail['channel_partner_mobile_number'] = $Lead_source->phone_number;
                }
                
                $CustomerName = $LeadDetail->first_name . ' ' . $LeadDetail->last_name;
                if ($LeadDetail->house_no != '' || $LeadDetail->house_no != null) {
                    $SiteAddress .= $LeadDetail->house_no;
                }
                if ($LeadDetail->addressline1 != '' || $LeadDetail->addressline1 != null) {
                    $SiteAddress .= ', ' . $LeadDetail->addressline1;
                }
                if ($LeadDetail->addressline2 != '' || $LeadDetail->addressline2 != null) {
                    $SiteAddress .= ', ' . $LeadDetail->addressline2;
                }
                if ($LeadDetail->area != '' || $LeadDetail->area != null) {
                    $SiteAddress .= ', ' . $LeadDetail->area;
                }
                if ($LeadDetail->city_id != '' || $LeadDetail->city_id != null || $LeadDetail->city_id != 0) {
                    $CityName = CityList::select('city_list.id', 'city_list.name as city_list_name', 'state_list.name as state_list_name');
                    $CityName->leftJoin('state_list', 'state_list.id', '=', 'city_list.state_id');
                    $CityName->where('city_list.id', $LeadDetail->city_id);
                    $CityName = $CityName->first();
                    if ($CityName) {
                        $SiteAddress .= ', ' . $CityName->city_list_name . ', ' . $CityName->state_list_name;
                    }
                }
                if ($LeadDetail->pincode != '' || $LeadDetail->pincode != null || $LeadDetail->pincode != null) {
                    $SiteAddress .= ', ' . $LeadDetail->pincode;
                }
            } else {
                $CustomerName = $Quot_Basic_Detail['customer_name'];
                if ($Quot_Basic_Detail['siteaddress'] != '' || $Quot_Basic_Detail['siteaddress'] != null) {
                    $SiteAddress .= $Quot_Basic_Detail['siteaddress'];
                }
                if ($Quot_Basic_Detail['city_name'] != '' || $Quot_Basic_Detail['city_name'] != null) {
                    $SiteAddress .= ', ' . $Quot_Basic_Detail['city_name'];
                }
                if ($Quot_Basic_Detail['state_name'] != '' || $Quot_Basic_Detail['state_name'] != null) {
                    $SiteAddress .= ', ' . $Quot_Basic_Detail['state_name'];
                }
                if ($Quot_Basic_Detail['site_pincode'] != '' || $Quot_Basic_Detail['site_pincode'] != null) {
                    $SiteAddress .= ', ' . $Quot_Basic_Detail['site_pincode'];
                }
            }

            if ($Quot_Basic_Detail['quottype_id'] == 1) {
                $SiteVisitWith = 'Client';
                if ($Quot_Basic_Detail['architech_id'] != '0') {
                    $SiteVisitWith .= ', Architect';
                }

                if ($Quot_Basic_Detail['electrician_id'] != '0') {
                    $SiteVisitWith .= ',</br>Electrician';
                }
            } else {
                $SiteVisitWith = '-';
            }
            // BOARD WISE ROOM LIST START

            $room_column = ['wltrn_quot_itemdetails.room_no', 'wltrn_quot_itemdetails.room_name'];
            $room_query = Wltrn_QuotItemdetail::query();
            $room_query->select($room_column);
            $room_query->selectRaw('SUM(wltrn_quot_itemdetails.net_amount) as room_amount');
            $room_query->where([
                ['wltrn_quot_itemdetails.quot_id', $request->quot_id],
                ['wltrn_quot_itemdetails.quotgroup_id', $request->quotgroup_id],
                ['wltrn_quot_itemdetails.isactiveroom', 1],
                ['wltrn_quot_itemdetails.isactiveboard', 1],
                // ['wltrn_quot_itemdetails.itemsubgroup_id', '<>', '64']
            ]);
            $room_query->groupBy($room_column);
            $room_data = $room_query->get();

            $product_detailed_summary_visible = $visible_array['product_detailed_summary_visible'];
            $product_detailed_gst_visible = $visible_array['product_detailed_gst_visible'];
            $product_detailed_discount_visible = $visible_array['product_detailed_discount_visible'];
            $product_detailed_rate_total_visible = $visible_array['product_detailed_rate_total_visible'];

            if ($product_detailed_summary_visible == 1) {
                $arr_room = [];
                foreach ($room_data as $key => $room_value) {
                    $room_detail['room_name'] = $room_value->room_name;
                    $room_detail['room_amount'] = round($room_value->room_amount);
                    // $room_detail['room_amount'] = number_format(round($room_value->room_amount), 2, '.', '');
                    $board_column = ['wltrn_quot_itemdetails.quot_id', 'wltrn_quot_itemdetails.quotgroup_id', 'wltrn_quot_itemdetails.room_no', 'wltrn_quot_itemdetails.board_no', 'wltrn_quot_itemdetails.board_name', 'wltrn_quot_itemdetails.board_item_id', 'wltrn_quot_itemdetails.board_image'];

                    $board_query = Wltrn_QuotItemdetail::query();
                    $board_query->select($board_column);
                    $board_query->selectRaw('SUM(wltrn_quot_itemdetails.net_amount) as board_net_amount');
                    $board_query->where([
                        ['wltrn_quot_itemdetails.quot_id', $request->quot_id],
                        ['wltrn_quot_itemdetails.quotgroup_id', $request->quotgroup_id],
                        ['wltrn_quot_itemdetails.room_no', $room_value->room_no],
                        ['wltrn_quot_itemdetails.isactiveroom', 1],
                        ['wltrn_quot_itemdetails.isactiveboard', 1],
                        // ['wltrn_quot_itemdetails.itemsubgroup_id', '<>', '64']
                    ]);
                    // $board_query->groupBy(['wltrn_quot_itemdetails.board_no']);
                    $board_query->groupBy($board_column);
                    $board_data = $board_query->get();
                    $arr_board = [];
                    foreach ($board_data as $key => $board_value) {
                        // $arr_board_detail['board_image'] = strval($board_value->board_image);
                        $arr_board_detail['board_image'] = strval(getSpaceFilePath($board_value->board_image));
                        $arr_board_detail['board_name'] = $board_value->board_name;
                        $arr_board_detail['board_price'] = round($board_value->board_net_amount);
                        // $arr_board_detail['board_price'] = number_format(round($board_value->board_net_amount), 2, '.', '');
                        $board_item_column = [
                            'wltrn_quot_itemdetails.qty',
                            'wltrn_quot_itemdetails.rate',
                            'wltrn_quot_itemdetails.discper',
                            'wltrn_quot_itemdetails.grossamount as taxableamount',
                            // 'wltrn_quot_itemdetails.taxableamount',
                            'wltrn_quot_itemdetails.net_amount',
                            'wltrn_quot_itemdetails.is_appendix',
                            'wlmst_items.itemname',
                            'wlmst_items.igst_per',
                            'wlmst_item_groups.sequence',
                            'wlmst_item_prices.code',
                            'wlmst_item_prices.image as addons_image',
                        ];

                        $board_item_query = Wltrn_QuotItemdetail::query();
                        $board_item_query->select($board_item_column);
                        $board_item_query->leftJoin('wlmst_items', 'wlmst_items.id', '=', 'wltrn_quot_itemdetails.item_id');
                        $board_item_query->leftJoin('wlmst_item_groups', 'wlmst_item_groups.id', '=', 'wltrn_quot_itemdetails.itemgroup_id');
                        $board_item_query->leftJoin('wlmst_item_prices', 'wlmst_item_prices.id', '=', 'wltrn_quot_itemdetails.item_price_id');

                        $board_item_query->where([['wltrn_quot_itemdetails.quot_id', $request->quot_id], ['wltrn_quot_itemdetails.quotgroup_id', $request->quotgroup_id], ['wltrn_quot_itemdetails.room_no', $board_value->room_no], ['wltrn_quot_itemdetails.board_no', $board_value->board_no], ['wltrn_quot_itemdetails.isactiveroom', 1], ['wltrn_quot_itemdetails.isactiveboard', 1]]);
                        // $board_item_query->orderBy('wltrn_quot_itemdetails.itemsubgroup_id', 'ASC');
                        $board_item_query->orderBy('wlmst_item_groups.sequence', 'ASC');

                        $arr_board_detail['board_item_count'] = count($board_item_query->get());
                        $arr_board_detail['board_item'] = $board_item_query->get();
                        array_push($arr_board, $arr_board_detail);
                    }

                    $room_detail['board'] = $arr_board;
                    array_push($arr_room, $room_detail);
                }
            } else {
                $arr_room = 0;
            }
            $data['basic_detail'] = $Quot_Basic_Detail;
            $data['basic_detail']['site_visit_with'] = $SiteVisitWith;
            $data['basic_detail']['customer_name'] = $CustomerName;
            $data['basic_detail']['final_site_address'] = $SiteAddress;
            $data['room'] = $arr_room;
            // BOARD WISE ROOM LIST END

            ///////////////////////////////   START ROOM AND AREA WISE SUMMARY  ////////////////////////////////////////////
            $area_page_visible = $visible_array['area_page_visible'];
            $area_summary_visible = $visible_array['area_summary_visible'];
            $product_summary_visible = $visible_array['product_summary_visible'];

            // ROOM WISE SUMMARY START
            if ($area_page_visible == 1) {
                $columns_area_summary = [
                    0 => 'wltrn_quot_itemdetails.room_no',
                    1 => 'wltrn_quot_itemdetails.room_name',
                ];

                $QuotationAreaSummary = Wltrn_QuotItemdetail::query();
                $QuotationAreaSummary->select($columns_area_summary);
                $QuotationAreaSummary->selectRaw('SUM(wltrn_quot_itemdetails.net_amount) as room_net_amount');
                $QuotationAreaSummary->where([['wltrn_quot_itemdetails.quot_id', $request->quot_id], ['wltrn_quot_itemdetails.quotgroup_id', $request->quotgroup_id], ['wltrn_quot_itemdetails.isactiveroom', 1], ['wltrn_quot_itemdetails.isactiveboard', 1]]);
                $QuotationAreaSummary->groupBy($columns_area_summary);

                $listAreaSummary = [];
                $area_summary_total_other_amount = 0;
                $area_summary_total_whitelion_amount = 0;
                $area_summary_total_final_amount = 0;
                foreach ($QuotationAreaSummary->get() as $key => $area_summary) {
                    $listAreaSm['room_no'] = $area_summary->room_no;
                    $listAreaSm['room_name'] = $area_summary->room_name;

                    $whitelion_net_amount = Wltrn_QuotItemdetail::where('room_no', $area_summary->room_no);
                    $whitelion_net_amount = $whitelion_net_amount->where('quot_id', $request->quot_id);
                    $whitelion_net_amount = $whitelion_net_amount->where('quotgroup_id', $request->quotgroup_id);
                    $whitelion_net_amount = $whitelion_net_amount->where('isactiveroom', 1);
                    $whitelion_net_amount = $whitelion_net_amount->where('isactiveboard', 1);
                    $whitelion_net_amount = $whitelion_net_amount->whereIn('itemgroup_id', [1, 3])->sum('net_amount');

                    $other_net_amount = Wltrn_QuotItemdetail::where('room_no', $area_summary->room_no);
                    $other_net_amount = $other_net_amount->where('quot_id', $request->quot_id);
                    $other_net_amount = $other_net_amount->where('quotgroup_id', $request->quotgroup_id);
                    $other_net_amount = $other_net_amount->where('isactiveroom', 1);
                    $other_net_amount = $other_net_amount->where('isactiveboard', 1);
                    $other_net_amount = $other_net_amount->whereIn('itemgroup_id', [2, 4])->sum('net_amount');

                    $total_net_amount = $area_summary->room_net_amount;
                    $area_summary_total_other_amount += $other_net_amount;
                    $area_summary_total_whitelion_amount += $whitelion_net_amount;
                    $area_summary_total_final_amount += $total_net_amount;
                    $listAreaSm['room_total_whitelion_net_amount'] = round($whitelion_net_amount);
                    $listAreaSm['room_total_other_net_amount'] = round($other_net_amount);
                    $listAreaSm['room_total_net_amount'] = round($total_net_amount);
                    array_push($listAreaSummary, $listAreaSm);
                }
            } else {
                $listAreaSummary = 0;
                $area_summary_total_other_amount = 0;
                $area_summary_total_whitelion_amount = 0;
                $area_summary_total_final_amount = 0;
            }
            $data['area_summary']['area_list'] = $listAreaSummary;
            $data['area_summary']['area_summary_total_other_amount'] = round($area_summary_total_other_amount);
            $data['area_summary']['area_summary_total_whitelion_amount'] = round($area_summary_total_whitelion_amount);
            $data['area_summary']['area_summary_total_final_amount'] = round($area_summary_total_final_amount);
            // ROOM WISE SUMMARY END

            // START PRODUCT WISE EXCEL SUMMARY
            if ($product_summary_visible == 1) {
                $columns_area_summary = [
                    0 => 'wltrn_quot_itemdetails.room_no',
                    1 => 'wltrn_quot_itemdetails.room_name',
                ];

                $QuotationAreaSummary = Wltrn_QuotItemdetail::query();
                $QuotationAreaSummary->select($columns_area_summary);
                $QuotationAreaSummary->selectRaw('SUM(wlmst_item_details.touch_on_off * wltrn_quot_itemdetails.qty) as touch_on_off');
                $QuotationAreaSummary->selectRaw('SUM(wlmst_item_details.touch_fan_regulator * wltrn_quot_itemdetails.qty) as touch_fan_regulator');
                $QuotationAreaSummary->selectRaw('SUM(wlmst_item_details.wl_plug * wltrn_quot_itemdetails.qty) as wl_plug');
                $QuotationAreaSummary->selectRaw('SUM(wlmst_item_details.special * wltrn_quot_itemdetails.qty) as special');
                $QuotationAreaSummary->selectRaw('SUM(wlmst_item_details.wl_accessories * wltrn_quot_itemdetails.qty) as wl_accessories');
                // $QuotationAreaSummary->selectRaw('SUM(wlmst_item_details.rc2 * wltrn_quot_itemdetails.qty) as rc2');
                $QuotationAreaSummary->selectRaw('SUM(wlmst_item_details.normal_switch * wltrn_quot_itemdetails.qty) as normal_switch');
                $QuotationAreaSummary->selectRaw('SUM(wlmst_item_details.normal_fan_regulator * wltrn_quot_itemdetails.qty) as normal_fan_regulator');
                $QuotationAreaSummary->selectRaw('SUM(wlmst_item_details.other_plug * wltrn_quot_itemdetails.qty) as other_plug');
                $QuotationAreaSummary->selectRaw('SUM(wlmst_item_details.other * wltrn_quot_itemdetails.qty) as other');
                $QuotationAreaSummary->leftJoin('wlmst_item_details', 'wlmst_item_details.item_id', '=', 'wltrn_quot_itemdetails.item_id');
                $QuotationAreaSummary->where([['wltrn_quot_itemdetails.quot_id', $request->quot_id], ['wltrn_quot_itemdetails.quotgroup_id', $request->quotgroup_id], ['wltrn_quot_itemdetails.isactiveroom', 1], ['wltrn_quot_itemdetails.isactiveboard', 1]]);
                $QuotationAreaSummary->groupBy($columns_area_summary);
                $listProductExcelSummary = $QuotationAreaSummary->get();
            } else {
                $listProductExcelSummary = 0;
            }

            $data['area_summary']['item_excel_summary'] = $listProductExcelSummary;
            // END PRODUCT WISE EXCEL SUMMARY

            ///////////////////////////////   END ROOM AND AREA WISE SUMMARY  ////////////////////////////////////////////

            ///////////////////////////////   START ROOM AND AREA WISE DETAILED SUMMARY  ////////////////////////////////////////////

            // PREFIX = rds
            $area_detailed_summary_visible = $visible_array['area_detailed_summary_visible'];
            $area_detailed_gst_visible = $visible_array['area_detailed_gst_visible'];
            $area_detailed_discount_visible = $visible_array['area_detailed_discount_visible'];
            $area_detailed_rate_total_visible = $visible_array['area_detailed_rate_total_visible'];
            if ($area_detailed_summary_visible == 1) {
                $rds_room_column = ['wltrn_quot_itemdetails.room_no', 'wltrn_quot_itemdetails.room_name'];
                $rds_room_query = Wltrn_QuotItemdetail::query();
                $rds_room_query->select($rds_room_column);
                $rds_room_query->selectRaw('SUM(wltrn_quot_itemdetails.rate) as rds_total_rate');
                $rds_room_query->selectRaw('SUM(wltrn_quot_itemdetails.grossamount) as rds_total_grossamount');
                // $rds_room_query->selectRaw('SUM(wltrn_quot_itemdetails.taxableamount) as rds_total_grossamount');
                $rds_room_query->selectRaw('SUM(wltrn_quot_itemdetails.net_amount) as room_amount');
                $rds_room_query->selectRaw('SUM(wltrn_quot_itemdetails.cgst_amount) as rds_total_cgst_amount');
                $rds_room_query->selectRaw('SUM(wltrn_quot_itemdetails.sgst_amount) as rds_total_sgst_amount');

                $rds_room_query->where([
                    ['wltrn_quot_itemdetails.quot_id', $request->quot_id],
                    ['wltrn_quot_itemdetails.quotgroup_id', $request->quotgroup_id],
                    ['wltrn_quot_itemdetails.isactiveroom', 1],
                    ['wltrn_quot_itemdetails.isactiveboard', 1],
                    // ['wltrn_quot_itemdetails.itemsubgroup_id', '<>', '64']
                ]);
                $rds_room_query->groupBy($rds_room_column);
                $rds_room_data = $rds_room_query->get();

                $rds_room_arr = [];
                foreach ($rds_room_data as $key => $rds_room_summary) {
                    $rds_room_detail['rds_room_name'] = $rds_room_summary->room_name;
                    $rds_room_detail['rds_room_total_rate'] = round($rds_room_summary->rds_total_rate);
                    $rds_room_detail['rds_room_total_grossamount'] = round($rds_room_summary->rds_total_grossamount);
                    $rds_room_detail['rds_room_total_gst'] = round($rds_room_summary->rds_total_cgst_amount + $rds_room_summary->rds_total_sgst_amount);
                    $rds_room_detail['rds_room_total_netamount'] = round($rds_room_summary->room_amount);
                    $rds_item_column = ['wltrn_quot_itemdetails.discper', 'wltrn_quot_itemdetails.rate', 'wltrn_quot_itemdetails.is_appendix', 'wlmst_items.itemname', 'wlmst_items.igst_per', 'wltrn_quot_itemdetails.item_price_id', 'wlmst_item_groups.sequence', 'wlmst_item_prices.code'];

                    $rds_item_query = Wltrn_QuotItemdetail::query();
                    $rds_item_query->select($rds_item_column);
                    $rds_item_query->selectRaw('SUM(wltrn_quot_itemdetails.qty) as qty');
                    $rds_item_query->selectRaw('SUM(wltrn_quot_itemdetails.discamount) as discamount');
                    // $rds_item_query->selectRaw('SUM(wltrn_quot_itemdetails.taxableamount) as grossamount');
                    $rds_item_query->selectRaw('SUM(wltrn_quot_itemdetails.grossamount) as grossamount');
                    $rds_item_query->selectRaw('SUM(wltrn_quot_itemdetails.net_amount) as net_amount');
                    $rds_item_query->leftJoin('wlmst_items', 'wlmst_items.id', '=', 'wltrn_quot_itemdetails.item_id');
                    $rds_item_query->leftJoin('wlmst_item_groups', 'wlmst_item_groups.id', '=', 'wltrn_quot_itemdetails.itemgroup_id');
                    $rds_item_query->leftJoin('wlmst_item_prices', 'wlmst_item_prices.id', '=', 'wltrn_quot_itemdetails.item_price_id');

                    $rds_item_query->where([['wltrn_quot_itemdetails.quot_id', $request->quot_id], ['wltrn_quot_itemdetails.quotgroup_id', $request->quotgroup_id], ['wltrn_quot_itemdetails.room_no', $rds_room_summary->room_no], ['wltrn_quot_itemdetails.isactiveroom', 1], ['wltrn_quot_itemdetails.isactiveboard', 1]]);
                    $rds_item_query->groupby($rds_item_column);
                    $rds_item_query->orderBy('wlmst_item_groups.sequence', 'ASC');
                    $rds_room_detail['rds_room_item'] = $rds_item_query->get();
                    array_push($rds_room_arr, $rds_room_detail);
                }
            } else {
                $rds_room_arr = 0;
            }
            $data['rds_room_summary'] = $rds_room_arr;

            ///////////////////////////////   END ROOM AND AREA WISE DETAILED SUMMARY  ////////////////////////////////////////////

            ///////////////////////////////   START WHITELION AND OTHERS PRODUCT WISE DETAILED SUMMARY  ////////////////////////////////////////////

            // PREFIX = whitelion
            $wlt_and_others_detailed_summary_visible = $visible_array['wlt_and_others_detailed_summary_visible'];
            $wlt_and_others_detailed_gst_visible = $visible_array['wlt_and_others_detailed_gst_visible'];
            $wlt_and_others_detailed_discount_visible = $visible_array['wlt_and_others_detailed_discount_visible'];
            $wlt_and_others_detailed_rate_total_visible = $visible_array['wlt_and_others_detailed_rate_total_visible'];
            if ($wlt_and_others_detailed_summary_visible == 1) {
                $whitelion_company_column = ['wltrn_quot_itemdetails.discper', 'wltrn_quot_itemdetails.rate', 'wltrn_quot_itemdetails.is_appendix', 'wlmst_items.itemname', 'wlmst_items.igst_per', 'wltrn_quot_itemdetails.item_price_id', 'wlmst_item_groups.sequence', 'wlmst_item_prices.code'];

                $whitelion_company_query = Wltrn_QuotItemdetail::query();
                $whitelion_company_query->select($whitelion_company_column);
                $whitelion_company_query->selectRaw('SUM(wltrn_quot_itemdetails.qty) as whitelion_qty');
                $whitelion_company_query->selectRaw('SUM(wltrn_quot_itemdetails.discamount) as whitelion_discount_amount');
                // $whitelion_company_query->selectRaw('SUM(wltrn_quot_itemdetails.taxableamount) as whitelion_grossamount');
                $whitelion_company_query->selectRaw('SUM(wltrn_quot_itemdetails.grossamount) as whitelion_grossamount');
                $whitelion_company_query->selectRaw('SUM(wltrn_quot_itemdetails.cgst_amount) as whitelion_cgst_amount');
                $whitelion_company_query->selectRaw('SUM(wltrn_quot_itemdetails.sgst_amount) as whitelion_sgst_amount');
                $whitelion_company_query->selectRaw('SUM(wltrn_quot_itemdetails.net_amount) as whitelion_net_amount');
                $whitelion_company_query->leftJoin('wlmst_items', 'wlmst_items.id', '=', 'wltrn_quot_itemdetails.item_id');
                $whitelion_company_query->leftJoin('wlmst_item_groups', 'wlmst_item_groups.id', '=', 'wltrn_quot_itemdetails.itemgroup_id');
                $whitelion_company_query->leftJoin('wlmst_item_prices', 'wlmst_item_prices.id', '=', 'wltrn_quot_itemdetails.item_price_id');

                $whitelion_company_query->where([['wltrn_quot_itemdetails.quot_id', $request->quot_id], ['wltrn_quot_itemdetails.quotgroup_id', $request->quotgroup_id], ['wltrn_quot_itemdetails.isactiveroom', 1], ['wltrn_quot_itemdetails.isactiveboard', 1]]);
                $whitelion_company_query->whereIn('wltrn_quot_itemdetails.itemgroup_id', [1, 3]);
                $whitelion_company_query->groupby($whitelion_company_column);
                $whitelion_company_query->orderBy('wlmst_item_groups.sequence', 'ASC');
                $whitelion_company_data = $whitelion_company_query->get();

                $whitelion_company_total_cgst = 0;
                $whitelion_company_total_sgst = 0;
                $whitelion_company_total_grossamount = 0;
                $whitelion_company_total_netamount = 0;

                $whitelion_company_arr = [];
                $new_whitelion_subgroup = '';
                $old_whitelion_subgroup = '';
                foreach ($whitelion_company_data as $key => $whitelion_company_summary) {
                    $item_price = Wlmst_ItemPrice::find($whitelion_company_summary->item_price_id);
                    $item_subgroup = WlmstItemSubgroup::find($item_price->itemsubgroup_id);
                    $new_whitelion_subgroup = $item_subgroup->itemsubgroupname;
                    $whitelion_company_summary['subgroupname'] = '';
                    if ($new_whitelion_subgroup != $old_whitelion_subgroup) {
                        $whitelion_company_summary['subgroupname'] = $new_whitelion_subgroup;
                    }

                    $whitelion_company_total_cgst += $whitelion_company_summary->whitelion_cgst_amount;
                    $whitelion_company_total_sgst += $whitelion_company_summary->whitelion_sgst_amount;
                    $whitelion_company_total_grossamount += $whitelion_company_summary->whitelion_grossamount;
                    $whitelion_company_total_netamount += $whitelion_company_summary->whitelion_net_amount;
                    array_push($whitelion_company_arr, $whitelion_company_summary);

                    $old_whitelion_subgroup = $new_whitelion_subgroup;
                }

                // PREFIX = others
                $others_company_column = ['wltrn_quot_itemdetails.discper', 'wltrn_quot_itemdetails.rate', 'wltrn_quot_itemdetails.is_appendix', 'wlmst_items.itemname', 'wlmst_items.igst_per', 'wltrn_quot_itemdetails.item_price_id', 'wlmst_item_groups.sequence', 'wlmst_item_prices.code'];
                $others_company_query = Wltrn_QuotItemdetail::query();
                $others_company_query->select($others_company_column);
                $others_company_query->selectRaw('SUM(wltrn_quot_itemdetails.qty) as others_qty');
                $others_company_query->selectRaw('SUM(wltrn_quot_itemdetails.discamount) as others_discount_amount');
                // $others_company_query->selectRaw('SUM(wltrn_quot_itemdetails.taxableamount) as others_grossamount');
                $others_company_query->selectRaw('SUM(wltrn_quot_itemdetails.grossamount) as others_grossamount');
                $others_company_query->selectRaw('SUM(wltrn_quot_itemdetails.cgst_amount) as others_cgst_amount');
                $others_company_query->selectRaw('SUM(wltrn_quot_itemdetails.sgst_amount) as others_sgst_amount');
                $others_company_query->selectRaw('SUM(wltrn_quot_itemdetails.net_amount) as others_net_amount');
                $others_company_query->leftJoin('wlmst_items', 'wlmst_items.id', '=', 'wltrn_quot_itemdetails.item_id');
                $others_company_query->leftJoin('wlmst_item_groups', 'wlmst_item_groups.id', '=', 'wltrn_quot_itemdetails.itemgroup_id');
                $others_company_query->leftJoin('wlmst_item_prices', 'wlmst_item_prices.id', '=', 'wltrn_quot_itemdetails.item_price_id');

                $others_company_query->where([['wltrn_quot_itemdetails.quot_id', $request->quot_id], ['wltrn_quot_itemdetails.quotgroup_id', $request->quotgroup_id], ['wltrn_quot_itemdetails.isactiveroom', 1], ['wltrn_quot_itemdetails.isactiveboard', 1]]);
                $others_company_query->whereIn('wltrn_quot_itemdetails.itemgroup_id', [2, 4]);
                $others_company_query->groupby($others_company_column);
                $others_company_query->orderBy('wlmst_item_groups.sequence', 'ASC');

                $others_company_data = $others_company_query->get();

                $others_company_total_cgst = 0;
                $others_company_total_sgst = 0;
                $others_company_total_grossamount = 0;
                $others_company_total_netamount = 0;
                $others_company_arr = [];
                $new_other_subgroup = '';
                $old_other_subgroup = '';
                foreach ($others_company_data as $key => $others_company_summary) {
                    $item_price = Wlmst_ItemPrice::find($others_company_summary->item_price_id);
                    $item_subgroup = WlmstItemSubgroup::find($item_price->itemsubgroup_id);
                    $new_other_subgroup = $item_subgroup->itemsubgroupname;
                    $others_company_summary['subgroupname'] = '';
                    if ($new_other_subgroup != $old_other_subgroup) {
                        $others_company_summary['subgroupname'] = $new_other_subgroup;
                    }

                    $others_company_total_cgst += $others_company_summary->others_cgst_amount;
                    $others_company_total_sgst += $others_company_summary->others_sgst_amount;
                    $others_company_total_grossamount += $others_company_summary->others_grossamount;
                    $others_company_total_netamount += $others_company_summary->others_net_amount;
                    array_push($others_company_arr, $others_company_summary);

                    $old_other_subgroup = $new_other_subgroup;
                }
            } else {
                // whitelion
                $whitelion_company_total_cgst = 0;
                $whitelion_company_total_sgst = 0;
                $whitelion_company_total_grossamount = 0;
                $whitelion_company_total_netamount = 0;
                $whitelion_company_arr = 0;

                // others
                $others_company_total_cgst = 0;
                $others_company_total_sgst = 0;
                $others_company_total_grossamount = 0;
                $others_company_total_netamount = 0;
                $others_company_arr = 0;
            }

            $data['whitelion_product_summary']['whitelion_company_total_cgst'] = $whitelion_company_total_cgst;
            $data['whitelion_product_summary']['whitelion_company_total_sgst'] = $whitelion_company_total_sgst;
            $data['whitelion_product_summary']['whitelion_company_total_grossamount'] = $whitelion_company_total_grossamount;
            $data['whitelion_product_summary']['whitelion_company_total_netamount'] = $whitelion_company_total_netamount;
            $data['whitelion_product_summary']['whitelion_items'] = $whitelion_company_arr;

            $data['others_product_summary']['others_company_total_cgst'] = $others_company_total_cgst;
            $data['others_product_summary']['others_company_total_sgst'] = $others_company_total_sgst;
            $data['others_product_summary']['others_company_total_grossamount'] = $others_company_total_grossamount;
            $data['others_product_summary']['others_company_total_netamount'] = $others_company_total_netamount;
            $data['others_product_summary']['others_company_item'] = $others_company_arr;

            ///////////////////////////////   END WHITELION AND OTHERS PRODUCT WISE DETAILED SUMMARY  ////////////////////////////////////////////
            $data['pdf_permission'] = $visible_array;

            // $response = $data;
            // $pdf = Pdf::loadView('quotation/master/quotation/quotpdf', compact('data'));
            // $view = view('quotation/master/quotation/quotpdf', compact('data'))->render();
            // $pdf = app('dompdf.wrapper');
            // $pdf->setPaper('a4', 'portrait');
            // $pdf->getDomPDF()->set_option("isHtml5ParserEnabled", true);
            // $pdf->getDomPDF()->set_option("isRemoteEnabled", true);
            // $pdf->getDomPDF()->set_option("enable_php", true);
            // $pdf->loadHTML($view);
            // return $pdf->download($Quot_Basic_Detail->site_name . '_quotation.pdf');

            // $view2 = view('quotation/master/quotation/quotpdf', compact('data'));
            // $view2 = response($view2, Response::HTTP_OK)->header('Content-Type', 'text/html');

            $view = view('quotation/master/quotation/quotpdf', compact('data'))->render();
            $pdf = app('dompdf.wrapper');
            $pdf->getDomPDF()->set_option('isHtml5ParserEnabled', true);
            $pdf->getDomPDF()->set_option('isRemoteEnabled', true);
            $pdf->getDomPDF()->set_option('enable_php', true);
            $pdf->loadHTML($view);
            $filePath = public_path('quotation/document.pdf');
            $pdf->save($filePath);

            $response = successRes('Success');
            $fileSize = filesize($filePath);
            $response['data']['size'] = $fileSize;
            $response['data']['url'] = url('quotation/document.pdf');
            $response['data']['view'] = $view;

            // $response['data']['view2'] = $view2;
        } else {
            $response = errorRes('Invalid Quotation Number');
        }
        // $data['data'] = $visible_array;
        // $response = $data;

        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }
    // NEW UPDATE END

    public function PostCheckVersion(Request $request)
    {
        // if (getCheckAppVersion($request->app_source, $request->app_version)) {
        $response = quotsuccessRes(1, 200, 'App Already Updated');
        // } else {
        // 	$response = quoterrorRes(2, 402, "Please Update Your App");
        // }
        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }

    public function searchChannelPartner(Request $request)
    {
        $isSalePerson = isSalePerson();
        $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
        $isRequest = 0;
        if ($isAdminOrCompanyAdmin == 1 && isset($request->is_request) && $request->is_request == 1) {
            $isRequest = 1;
        }

        if ($isSalePerson == 1) {
            $childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
        }

        $isTaleSalesUser = isTaleSalesUser();

        if ($isTaleSalesUser == 1) {
            $TeleSalesCity = TeleSalesCity(Auth::user()->id);
        }

        if (isset($request['search']['value'])) {
            $query = DB::table('users');
            $query->select('users.id');
            $query->where('users.type', 2);

            $query->where(function ($query2) use ($request) {
                $query2->where('users.first_name', 'like', '%' . $request['search']['value'] . '%');
                $query2->orWhere('users.last_name', 'like', '%' . $request['search']['value'] . '%');
            });

            $searchSalesPerson = $query->get();
            $searchSalesPersonIds = [];
            foreach ($searchSalesPerson as $keyS => $valueS) {
                $searchSalesPersonIds[] = $valueS->id;
            }
        }

        $searchColumns = [
            0 => 'users.id',
            1 => 'users.phone_number',
            2 => 'channel_partner.firm_name',
        ];

        $query = DB::table('users');
        $query->select('users.id', 'channel_partner.firm_name as text', 'users.phone_number', 'users.type');
        $query->leftJoin('channel_partner', 'channel_partner.id', '=', 'users.reference_id');
        $query->leftJoin('channel_partner as reporting_channel_partner', 'channel_partner.reporting_manager_id', '=', 'reporting_channel_partner.user_id');
        $query->whereIn('users.type', [101, 102, 103, 104, 105]);
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
        } elseif ($isTaleSalesUser == 1) {
            $query->whereIn('users.city_id', $TeleSalesCity);
        }

        if ($isRequest == 1) {
            $query->where('users.status', 2);
        }
        $query->orderBy('users.id', 'desc');

        if (isset($request['search']['value'])) {
            $search_value = $request['search']['value'];
            $query->where(function ($query) use ($search_value, $searchColumns, $searchSalesPersonIds) {
                $hasSalesPerson = 0;

                if (count($searchSalesPersonIds) > 0) {
                    $hasSalesPerson = 1;

                    $query->where(function ($query2) use ($searchSalesPersonIds) {
                        foreach ($searchSalesPersonIds as $keyS => $valueS) {
                            if ($keyS == 0) {
                                $query2->whereRaw('FIND_IN_SET("' . $valueS . '",channel_partner.sale_persons)>0');
                            } else {
                                $query2->orWhereRaw('FIND_IN_SET("' . $valueS . '",channel_partner.sale_persons)>0');
                            }
                        }
                    });
                }

                for ($i = 0; $i < count($searchColumns); $i++) {
                    if ($i == 0) {
                        if ($hasSalesPerson == 0) {
                            $query->where($searchColumns[$i], 'like', '%' . $search_value . '%');
                        } else {
                            $query->orWhere($searchColumns[$i], 'like', '%' . $search_value . '%');
                        }
                    } else {
                        $query->orWhere($searchColumns[$i], 'like', '%' . $search_value . '%');
                    }
                }
            });
        }
        $data = $query->get();

        $newdata = [];
        foreach ($data as $key => $value) {
            $d1['id'] = $value->id;
            $d1['text'] = getUserTypeName($value->type) . ' - ' . $value->text;
            $d1['phone_number'] = $value->phone_number;
            array_push($newdata, $d1);
        }
        $data = json_decode(json_encode($newdata), true);

        $response = successRes('Channel Parnter list');
        $response['data'] = $data;
        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }

    
    public function quotationRequestBrandWiseStatusList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'quot_id' => ['required'],
        ]);

        if ($validator->fails()) {
            $response = errorRes($validator->errors()->first());
            $response['data'] = $validator->errors();
        } else {
            $data = QuotRequest::select('quotation_request.*', 'wlmst_item_subgroups.itemsubgroupname', 'users.type as user_type');
            $data->selectRaw('CONCAT(users.first_name," ",users.last_name) AS user_name');
            $data->leftJoin('users', 'users.id', '=', 'quotation_request.assign_to');
            $data->leftJoin('wlmst_item_subgroups', 'wlmst_item_subgroups.id', '=', 'quotation_request.subgroup_id');
            $data->where('quotation_request.quot_id', $request->quot_id);
            $data->where('quotation_request.type', 'DISCOUNT');
            $data = $data->get();
            foreach ($data as $key => $value) {
                $user_type_lable = getUserTypeMainLabel($value['user_type']);
                if (isset(getChannelPartners()[$value['user_type']]['short_name'])) {
                    $objChannelPartner = ChannelPartner::selectRaw('firm_name AS user_name')->where('user_id', $value['assign_to'])->first();
                    $data[$key]['user_name'] = $objChannelPartner->user_name;
                }
                $data[$key]['user_type_lable'] = $user_type_lable;

                if ($value['status'] == 0) {
                    $data[$key]['status_name'] = 'Pending';
                } elseif ($value['status'] == 1) {
                    $data[$key]['status_name'] = 'Approved';
                } elseif ($value['status'] == 2) {
                    $data[$key]['status_name'] = 'Reject';
                } else {
                    $data[$key]['status_name'] = 'Unknown';
                }
            }
            $response = successRes();
            $response['data'] = $data;
        }

        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }

    public function discountBrandWiseSave(Request $request)
    {
        $request_data = $request->input();
        $validator = Validator::make($request->all(), [
            'quot_id' => ['required'],
            'quot_group_id' => ['required'],
            'discount' => ['required'],
        ]);

        if ($validator->fails()) {
            $response = [];
            $response['status'] = 0;
            $response['msg'] = $validator->errors()->first();
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors();
        } else {
            $quot_id = $request_data['quot_id'];
            $quot_group_id = $request_data['quot_group_id'];
            $lstDiscount = $request_data['discount'];

            $group_id = 0;
            if (count($lstDiscount) >= 1) {
                try {
                    $max_group_id = DB::table('quotation_request')->max('group_id');
                    if ($max_group_id) {
                        $group_id = $max_group_id + 1;
                    } else {
                        $group_id = 1;
                    }
                    $SalePersonManager = '';
                    $channelpartner = '';
                    $obgQuotation = Wltrn_Quotation::find($quot_id);
                    $objLead = Lead::find($obgQuotation->inquiry_id);
                    foreach ($lstDiscount as $dis_key => $dis_value) {
                        $QuotItemDetailArr = Wltrn_QuotItemdetail::select('*')->where([['wltrn_quot_itemdetails.quot_id', $quot_id], ['wltrn_quot_itemdetails.quotgroup_id', $quot_group_id], ['wltrn_quot_itemdetails.itemsubgroup_id', $dis_value['id']]]);

                        $isApply = 0;
                        $AssignTo = 0;
                        $selectSubgroupColumns = array(
                            'wlmst_item_subgroups.id',
                            'wlmst_item_subgroups.maxdisc',
                            'wlmst_item_subgroups.itemgroup_id',
                            'wlmst_item_subgroups.channel_partner_maxdisc',
                            'wlmst_item_subgroups.manager_ids',
                            'wlmst_item_subgroups.manager_maxdisc',
                            'wlmst_item_subgroups.company_admin_maxdisc',
                            'wlmst_item_subgroups.company_id'
                        );
                        $subgroupDetail = WlmstItemSubgroup::select($selectSubgroupColumns)->where('wlmst_item_subgroups.id',$dis_value['id'])->first();
                        if($subgroupDetail){
                            
                            if($subgroupDetail->itemgroup_id == 1 || $subgroupDetail->itemgroup_id == 3){
                                if ($dis_value['discount'] <= $subgroupDetail->maxdisc) {
                                    $isApply = 1;
                                } elseif ($dis_value['discount'] > $subgroupDetail->maxdisc && $dis_value['discount'] <= $subgroupDetail->manager_maxdisc){
                                    $login_user_id = Auth::user()->id;
                                    $isApply = in_array($login_user_id, explode(',', $subgroupDetail['manager_ids'])) ? 1 : 0;
                                    $SalePersonManager = SalePerson::select('reporting_manager_id')->where('user_id', $objLead->assigned_to)->first();
                                    if($SalePersonManager){
                                        $SalePersonManager = $SalePersonManager->reporting_manager_id;
                                    }else{
                                        $SalePersonManager = 1;
                                    }
                                    $AssignTo = $isApply == 0 ? $SalePersonManager : 1;
                                }else{
                                    $isApply = isCompanyAdmin();
                                    $AssignTo = ($isApply == 0) ? 1 : 0;
                                }
                            }else{
                                if ($dis_value['discount'] <= $subgroupDetail->maxdisc) {
                                    $isApply = 1;
                                } elseif ($dis_value['discount'] > $subgroupDetail->maxdisc && $dis_value['discount'] <= $subgroupDetail->channel_partner_maxdisc){
                                    $ch_type_arr = [101, 102, 103, 104, 105];
                                    $login_user_type = Auth::user()->type;
                                    $isApply = in_array($login_user_type, $ch_type_arr) ? 1 : 0;
                                    if ($isApply == 0) {
                                        $Deal_id = Wltrn_Quotation::find($quot_id)->inquiry_id;
                                        if ($Deal_id) {
                                            $Lead_ch_id = LeadContact::selectRaw("SUBSTRING_INDEX(type_detail, '-', -1) AS Ch_id")
                                                ->where('lead_id', $Deal_id)
                                                ->whereIn('type', $ch_type_arr)
                                                ->where('status', 1)
                                                ->first();
                                            if ($Lead_ch_id) {
                                                $AssignTo = $Lead_ch_id->Ch_id;
                                            } else {
                                                $AssignTo = 1;
                                            }
                                        }
                                    }
                                    $channelpartner = $AssignTo;
                                }else{
                                    $isApply = isCompanyAdmin();
                                    $AssignTo = ($isApply == 0) ? 1 : 0;
                                }
                            }

                            // if ($dis_value['discount'] <= $subgroupDetail->maxdisc) {
                            //     $isApply = 1;
                            // } elseif ($dis_value['discount'] <= $subgroupDetail->channel_partner_maxdisc) {
                            //     $ch_type_arr = [101, 102, 103, 104, 105];
                            //     $login_user_type = Auth::user()->type;
                            //     $isApply = in_array($login_user_type, $ch_type_arr) ? 1 : 0;
                            //     if ($isApply == 0) {
                            //         $Deal_id = Wltrn_Quotation::find($quot_id)->inquiry_id;
                            //         if ($Deal_id) {
                            //             $Lead_ch_id = LeadContact::selectRaw("SUBSTRING_INDEX('type_detail', '-', -1) AS Ch_id")
                            //                 ->where('lead_id', $Deal_id)
                            //                 ->whereIn('type', $ch_type_arr)
                            //                 ->where('status', 1)
                            //                 ->first();
                            //             if ($Lead_ch_id) {
                            //                 $AssignTo = $Lead_ch_id;
                            //             } else {
                            //                 $AssignTo = 1;
                            //             }
                            //         }
                            //     }
                            // } elseif ($dis_value['discount'] <= $subgroupDetail->manager_maxdisc) {
                            //     $login_user_id = Auth::user()->id;
                            //     $isApply = in_array($login_user_id, explode(',', $subgroupDetail['manager_ids'])) ? 1 : 0;
                            //     $AssignTo = $isApply == 0 ? Auth::user()->id : 0;
                            // } elseif ($dis_value['discount'] <= $subgroupDetail->company_admin_maxdisc) {
                            //     $isApply = isCompanyAdmin();
                            //     $AssignTo = $isApply == 0 ? 12 : 0;
                            // }
    
                            if ($isApply == 1) {
                                foreach ($QuotItemDetailArr->get() as $key => $value) {
                                    $QuotItemDetail = Wltrn_QuotItemdetail::find($value['id']);
    
                                    $totalamt = floatval($QuotItemDetail->qty) * floatval($QuotItemDetail->rate);
                                    $dis_amt = (floatval($totalamt) * floatval($dis_value['discount'])) / 100;
                                    $new_grossamount = floatval($totalamt) - floatval($dis_amt);
                                    $new_taxableamount = floatval($totalamt) - floatval($dis_amt);
    
                                    $new_igst_amount = (floatval($new_taxableamount) * floatval($QuotItemDetail->igst_per)) / 100;
                                    $new_cgst_amount = (floatval($new_taxableamount) * floatval($QuotItemDetail->cgst_per)) / 100;
                                    $new_sgst_amount = (floatval($new_taxableamount) * floatval($QuotItemDetail->sgst_per)) / 100;
    
                                    /* NET AMOUNT CALCULATION */
                                    $NetTotalAmount = floatval($new_taxableamount) + floatval($new_igst_amount) + floatval($new_cgst_amount) + floatval($new_sgst_amount);
                                    /* ROUND_UP AMOUNT CALCULATION */
                                    $RoundUpAmount = floatval($NetTotalAmount) - floatval(round($NetTotalAmount));
                                    /* NET FINAL AMOUNT CALCULATION */
                                    $new_net_amount = round($NetTotalAmount);
    
                                    $QuotItemDetail->discper = $dis_value['discount'];
                                    $QuotItemDetail->discamount = $dis_amt;
                                    $QuotItemDetail->grossamount = $new_grossamount;
                                    $QuotItemDetail->taxableamount = $new_taxableamount;
                                    $QuotItemDetail->igst_amount = $new_igst_amount;
                                    $QuotItemDetail->cgst_amount = $new_cgst_amount;
                                    $QuotItemDetail->sgst_amount = $new_sgst_amount;
                                    $QuotItemDetail->roundup_amount = $RoundUpAmount;
                                    $QuotItemDetail->net_amount = $new_net_amount;
    
                                    $QuotItemDetail->save();
                                }
                                $response = successRes('Discount Updated ✅');
                            } else {
                                $Quot_Request = new QuotRequest();
                                $Quot_Request->group_id = $group_id;
                                $Quot_Request->quot_id = $quot_id;
                                $Quot_Request->quotgroup_id = $quot_group_id;
                                $Quot_Request->subgroup_id = $dis_value['id'];
                                $Quot_Request->discount = $dis_value['discount'];
                                $Quot_Request->deal_id = Wltrn_Quotation::find($quot_id)->inquiry_id;
                                $Quot_Request->title = 'Discount Approvel';
                                $Quot_Request->type = 'DISCOUNT';
                                $Quot_Request->assign_to = $AssignTo;
                                $Quot_Request->status = 0;
                                $Quot_Request->entryby = Auth::user()->id;
                                $Quot_Request->entryip = $request->ip();
                                $Quot_Request->save();
    
                                $response = successRes();
                                // $response['data'] =  $Deal_id;
                            }
                        }else{
                            
                            $response = errorRes('invalid brand');
                        }
                    }
                    $response['channelpartner'] = $channelpartner;
                    $response['SalePersonManager'] = $SalePersonManager;
                    
                } catch (QueryException $ex) {
                    $response = errorRes($ex->getMessage());
                }
            }
            return response()
                ->json($response)
                ->header('Content-Type', 'application/json');
        }
    }

    public function PostQuotDetailItemList(Request $request) {
        
        $validator = Validator::make($request->all(), [
            'quot_id' => ['required'],
            'quotgroup_id' => ['required'],
        ]);

        if ($validator->fails()) {
            $response = errorRes($validator->errors()->first(), 400);
			$response['data'] = $validator->errors();
            return response()->json($response)->header('Content-Type', 'application/json');
        } else {
            $data = array();

            $DefaultRange = Wltrn_Quotation::select('default_range');
            $DefaultRange->where('id', $request->quot_id);
            $DefaultRange->where('quotgroup_id', $request->quotgroup_id);
            $DefaultRange = $DefaultRange->first();

            $room = array();
            $room['id'] = "1";
            $room['room_no'] = "0";
            $room['room_name'] = "Bulk Room";
            $room['quot_id'] = $request->quot_id;
            $room['quotgroup_id'] = $request->quotgroup_id;
            $room['board_no'] = "0";
            $room['board_size'] = "0";
            $room['board_item_id'] = "0";
            $room['board_item_price_id'] = "0";
            $room['board_item'] = "0";
            $room['board_name'] = "Bulk Board";
            $room['image'] = "";
            $room['item_type'] = "";
            $room['itemdescription'] = "";
            if($DefaultRange) {
                $room['default_range'] = $DefaultRange->default_range;
                $room['room_range'] = $DefaultRange->default_range;
                $room['board_range'] = $DefaultRange->default_range;
            } else {
                $room['default_range'] = "";
                $room['room_range'] = "";
                $room['board_range'] = "";
            }
            $room['room_adon'] = "0";
            $room['board_image'] = "";
            $room['app_source'] = $request->app_source;
            $room['app_version'] = $request->app_version;
            
            $data['room'] = $room;

            $columns = [
                'wlmst_item_prices.id as priceid',
                'wlmst_items.id as itemid',
                'wlmst_items.itemname',
                'wlmst_items.app_display_name',
                'wlmst_items.module',
                'wlmst_items.max_module',
                'wlmst_items.is_special',
                'wlmst_items.additional_remark',
                'wlmst_items.remark',
                'wlmst_items.itemcategory_id',
                'wlmst_item_categories.itemcategoryname',
                'wlmst_item_prices.discount',
                'wlmst_item_prices.company_id',
                'wlmst_item_prices.item_type',
                'wlmst_companies.companyname',
                'wlmst_item_prices.itemgroup_id',
                'wlmst_item_groups.itemgroupname',
                'wlmst_item_prices.itemsubgroup_id',
                'wlmst_item_subgroups.itemsubgroupname',
                'wlmst_item_prices.image',
                'wlmst_item_prices.code',
                'wlmst_item_prices.code AS product_code',
                'wlmst_items.itemname AS product_code_name',
                'wltrn_quot_itemdetails.qty',
                'wltrn_quot_itemdetails.rate as mrp',
                'wltrn_quot_itemdetails.net_amount',
            ];

            $query = Wltrn_QuotItemdetail::query();
            $query->select($columns);

            $query->leftJoin('wlmst_item_prices', 'wlmst_item_prices.id', '=', 'wltrn_quot_itemdetails.item_price_id');
            $query->leftJoin('wlmst_items', 'wlmst_items.id', '=', 'wltrn_quot_itemdetails.item_id');
            $query->leftJoin('wlmst_item_categories', 'wlmst_item_categories.id', '=', 'wlmst_items.itemcategory_id');
            // $query->leftJoin('wlmst_item_prices', 'wlmst_items.id', '=', 'wlmst_item_prices.item_id');
            $query->leftJoin('wlmst_companies', 'wlmst_companies.id', '=', 'wlmst_item_prices.company_id');
            $query->leftJoin('wlmst_item_groups', 'wlmst_item_groups.id', '=', 'wltrn_quot_itemdetails.itemgroup_id');
            $query->leftJoin('wlmst_item_subgroups', 'wlmst_item_subgroups.id', '=', 'wltrn_quot_itemdetails.itemsubgroup_id');

            $query->where('wltrn_quot_itemdetails.quot_id', '=', $request->quot_id);
            $query->where('wltrn_quot_itemdetails.quotgroup_id', '=', $request->quotgroup_id);
            $query->where('wlmst_items.isactive', 1);
            $query->where('wlmst_item_prices.isactive', 1);
            $query->orderBy('wlmst_items.app_sequence', 'ASC');

            $query = $query->get();
            $data['item'] = $query;


            $response = successRes();
            $response['data'] = $data;
            return response()->json($response)->header('Content-Type', 'application/json');
        }
        
    }
}
